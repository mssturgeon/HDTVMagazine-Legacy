<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	$debug = isset($_GET['debug']);

	function getByPGMasterID($masterid) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_pg = "SELECT * FROM pg_main WHERE masterid = '$masterid'";
		$res_pg = mQuery($sql_pg);
		if (mysql_num_rows($res_pg) != 0) return mysql_fetch_assoc($res_pg);

		# Not found ... try to get dynamically
		$pg_request_url = "http://ah.pricegrabber.com/search_xml.php?pid=718&key=7e084a24802&version=2.14&upc=1&spec=2&offers=1&masterid=$masterid";
		$contents = file_get_contents($pg_request_url);
		$xml = new SimpleXMLElement( $contents );

		$pg_main = array();
		$product = $xml->product;
		$pg_main['url'] = $product->url;
		$pg_main['masterid'] = $product->masterid;
		$pg_main['title'] = $product->title;
		$pg_main['image_small'] = $product->image_small;
		$pg_main['image_medium'] = $product->image_medium;
		$pg_main['image_large'] = $product->image_large;
		$pg_main['image_160'] = $product->image_160;
#		$pg_main['reative_rank'] = $product->reative_rank;
		$pg_main['manufacturer'] = $product->manufacturer;
		$pg_main['partnum'] = $product->partnum;
		$pg_main['upc'] = $product->upc;
#		$pg_main['price'] = $product->masterid;
		$pg_main['price_formatted'] = $product->price;
		$pg_main['sellers'] = $product->num_sellers[0];
		$pg_main['rating'] = $product->rating;
		$pg_main['num_reviews'] = $product->num_reviews;

		# Update pg_main
		$sql = "REPLACE INTO pg_main (". join(", ", array_keys($pg_main)) .", date_updated) VALUES ('". join("', '", array_values($pg_main)) ."', NOW())";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Now that we KNOW it's in the database ...
		$res_pg = mQuery($sql_pg);
		return mysql_fetch_assoc($res_pg);
	}

	function getByASIN($asin) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_amazon = "SELECT * FROM az_main m, az_attributes a WHERE m.ASIN = '$asin' AND m.ASIN = a.ASIN";
		$res_amazon = mQuery($sql_amazon);
		if (mysql_num_rows($res_amazon) != 0) return mysql_fetch_assoc($res_amazon);

		# Not found ... try to get dynamically
		$parameters = "AWSAccessKeyId={$admindata['amazon_access_key']}".
		"&AssociateTag={$admindata['amazon_associates_id']}".
		"&ItemId=$asin".
		"&Operation=ItemLookup".
		"&ResponseGroup=ItemAttributes,OfferSummary,Images".
		"&Service=AWSECommerceService".
		"&Timestamp=". gmdate("Y-m-d\TH:i:s\Z") .
		"&Version=2009-11-01";
		$parameters = str_replace(array(':',','), array('%3A','%2C'), $parameters);

		$signature = base64_encode(hash_hmac("sha256", "GET\nwebservices.amazon.com\n/onca/xml\n$parameters", $admindata['amazon_secret_access_key'], true));
		$signature = str_replace(array('+','='), array('%2B','%3D'), $signature);
		$signed_request = "http://webservices.amazon.com/onca/xml?{$parameters}&Signature=$signature";

		$contents = file_get_contents($signed_request);
		$xml = new SimpleXMLElement( $contents );

		# Verify a successful request
		if (is_object($xml->OperationRequest->Errors->Error)) {
			foreach($xml->OperationRequest->Errors->Error as $error) {
				$err = "Error {$error->Code}: (line ". __LINE__ .") $error->Message\n";
				mail("shane@hdtvmagazine.com", "ERROR: "& $_SERVER['SCRIPT_URL'], $err);
				return '';
			}
		}

		$az_main = array();
		$az_attributes = array();
		$item = $xml->Items->Item;
		$az_main['ASIN'] = $item->ASIN;
		$az_main['DetailPageURL'] = $item->DetailPageURL;

		if (is_object($item->ItemAttributes)) { # Update az_item_attributes
			$az_attributes['ASIN'] = $item->ASIN;
			$az_attributes['Brand'] = $item->ItemAttributes->Brand;
			$az_attributes['EAN'] = $item->ItemAttributes->EAN;
			$az_main['Weight'] = $item->ItemAttributes->ItemDimensions->Weight;
#			$az_main['Label'] = $item->ItemAttributes->Label;
			$az_main['ListPrice'] = $item->ItemAttributes->ListPrice->Amount;
			$az_main['ListPriceFormatted'] = $item->ItemAttributes->ListPrice->FormattedPrice;
			$az_attributes['Manufacturer'] = $item->ItemAttributes->Manufacturer;
			$az_attributes['Model'] = $item->ItemAttributes->Model;
			$az_attributes['MPN'] = $item->ItemAttributes->MPN;
			$az_attributes['ProductGroup'] = $item->ItemAttributes->ProductGroup;
#			$az_main['ProductTypeName'] = $item->ItemAttributes->ProductTypeName;
			$az_attributes['Publisher'] = $item->ItemAttributes->Publisher;
			$az_attributes['Studio'] = $item->ItemAttributes->Studio;
			$az_attributes['Title'] = $item->ItemAttributes->Title;
			$az_attributes['UPC'] = $item->ItemAttributes->UPC;
		}

		if (is_object($item->OfferSummary)) { # Update az_main (might want to put into a separate table)
			$az_main['TotalNew'] = $item->OfferSummary->TotalNew;
			if ($az_main['TotalNew'] > 0) {
				$az_main['LowestNewPrice'] = $item->OfferSummary->LowestNewPrice->Amount;
				$az_main['LowestNewPriceFormatted'] = $item->OfferSummary->LowestNewPrice->FormattedPrice;
			}
			$az_main['TotalUsed'] = $item->OfferSummary->TotalUsed;
			if ($az_main['TotalUsed'] > 0) {
				$az_main['LowestUsedPrice'] = $item->OfferSummary->LowestUsedPrice->Amount;
				$az_main['LowestUsedPriceFormatted'] = $item->OfferSummary->LowestUsedPrice->FormattedPrice;
			}
			$az_main['TotalRefurbished'] = $item->OfferSummary->TotalRefurbished;
			if ($az_main['TotalRefurbished'] > 0) {
				$az_main['LowestRefurbishedPrice'] = $item->OfferSummary->LowestRefurbishedPrice->Amount;
				$az_main['LowestRefurbishedPriceFormatted'] = $item->OfferSummary->LowestRefurbishedPrice->FormattedPrice;
			}
		}

		if (is_object($item->SmallImage)) { # Update az_main (might want to put images in separate table)
			$az_main['SmallImageURL'] = $item->SmallImage->URL;
			$az_main['SmallImageHeight'] = $item->SmallImage->Height;
			$az_main['SmallImageWidth'] = $item->SmallImage->Width;
		}
		if (is_object($item->MediumImage)) { # Update az_main (might want to put images in separate table)
			$az_main['MediumImageURL'] = $item->MediumImage->URL;
			$az_main['MediumImageHeight'] = $item->MediumImage->Height;
			$az_main['MediumImageWidth'] = $item->MediumImage->Width;
		}

		# Update az_main
		$sql = "REPLACE INTO az_main (". join(", ", array_keys($az_main)) .", date_updated) VALUES ('". join("', '", array_values($az_main)) ."', NOW())";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Update az_attributes
		$sql = "REPLACE INTO az_attributes (". join(", ", array_keys($az_attributes)) .") VALUES ('". join("', '", array_values($az_attributes)) ."')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Now that we KNOW it's in the database ...
		$res_amazon = mQuery($sql_amazon);
		return mysql_fetch_assoc($res_amazon);
	}

	# Get author information
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1539 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1539
		AND a.topic_id = t.topic_id";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Comments
	if ($row_aux['topic_replies'] > 0) {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a></li>';
	} else {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a></li>';
	}

	# Get Pricegrabber info
	if ($row_aux['pg_masterid'] != '') $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}

	# Get Amazon info
	if ($row_aux['ASIN'] != '') $row_amazon = getByASIN($row_aux['ASIN']);
	if ($row_amazon != '') {
		$az_url = str_replace($admindata['amazon_associates_id'], $amazon_tracking_id, $row_amazon['DetailPageURL']);
		$az_image = '<img src="'. $row_amazon['MediumImageURL'] .'" alt="'. $row_amazon['Title'] .'" style="keyimg" height="'. $row_amazon['MediumImageHeight'] .'" width="'. $row_amazon['MediumImageWidth'] .'"/>';
		$review_header = <<<EOT
<table align="center" class="greygrid"><!--tr>
	<td style="font-weight:bold; text-align:center;" colspan="4">{$row_amazon['Title']}</td></tr>
<tr-->
	<td class="greygrid">&nbsp;</td>
	<td class="greygrid"><b>List</b></td>
	<td class="greygrid"><b>Street</b></td>
	<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
	<td class="greygrid"><b>Current Pricing</b></td>
	<td class="greygrid">{$row_amazon['ListPriceFormatted']}</td>
	<td class="greygrid"><a href="$pg_url" target="_blank">$pg_price</a></td>
	<td class="greygrid"><a href="$az_url" target="_blank">{$row_amazon['LowestNewPriceFormatted']}</a></td>
</tr></table>
EOT;
	}

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta_medium_type = 'blog';
	$link_rel_image_src = $row_amazon['SmallImageURL'];
	$v_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_vertical">
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php&amp;title=DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration">
		<span style="display:none">This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  &lt;p&gt;As mentioned in &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php&quot;&gt;part 3&lt;/a&gt; of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  &lt;p&gt;Would DTV tuner integration address this situation?...</span></a>
	</li>
</ul></div>
EOT;
	$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
</ul></div>
EOT;

	switch (1) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			break;
		case 6: # Test
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$author_headshot = '';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			$meta_medium_type = 'news';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1539";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			$itunes_chicklet = BASE_IMG_HOST .'/images/chicklet-itunes.gif';
			# Need a better check here if we add other podcasts
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

			$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
	<li class="li_horizontal">
		<span style="line-height:16px;vertical-align:middle;">{$xml->feed->entry['circulation']}
			<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=1826&RD_PARM1=http%3A%2F%2Fphobos.apple.com%2FWebObjects%2FMZStore.woa%2Fwa%2FviewPodcast%3Fid%3D73799860" target="_blank"
				><img src="$itunes_chicklet" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="top" height="15" width="80"></a>
		</span>
	</li>
</ul></div>
EOT;

			break;
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</title>
	<meta name="keywords" content="dtv transition, tuner integration, transition help, help part, tuner mandate, tuner, dtv, digital, integrated, dtvs, tuners, sets, inches, analog, part, mandate, fcc, transition, july, help, manufacturers, integration, less, cable, stb" />
	<meta name="description" content="This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  &lt;p&gt;As mentioned in &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php&quot;&gt;part 3&lt;/a&gt; of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  &lt;p&gt;Would DTV tuner integration address this situation?..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
		}

		function tweetMemeButton() {
			if (document.getElementById("tm_li")) {
				var iframeCode = '';
				iframeCode += '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(document.URL) +'&amp;style=normal&amp;source=SEOmofo&amp;service=bit.ly" scrolling="no" frameborder="0" width="50" height="61">';
				document.getElementById("tm_li").innerHTML = iframeCode;
			}
		}
		function getTMButton(url, style, source, service) {
			if (style == 'compact') {w = 70;h = 20;} else {w = 50;h = 61;}
			return '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(url) +'&amp;style='+ style +'&amp;source='+ source +'&amp;service='+ service +'" scrolling="no" frameborder="0" width="'+ w +'" height="'+ h +'">';
		}
	</script>
	<style>
		#dd_right {float:right;padding:2px;text-align:right;}
		#dd_right ul {padding:0;margin:0;}
		#dd_right ul li {list-style-image:none;list-style-position:outside;padding:4px;margin:0;outline:0 none;background-color:transparent;border:0 none;list-style-type:none;background-image:none;}
		#dd_right .li_horizontal {align:right;display:inline;float:left;font-weight:bold;margin-top:2px;padding:0 10px}
		#dd_right .li_vertical {display:block;list-style-type:none;}
/*		#dd_right img {border:none !important;}*/
		a.stbar.chicklet img {border:0;height:16px;width:16px;margin-right:3px;vertical-align:middle;}
		a.stbar.chicklet {height:16px;line-height:16px;}
	</style>
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1539', 340, 125);">Add ASIN</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November  3, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=328&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2"><?=$h_buttons?></td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=$v_buttons?>
				<?=$review_header?>
				<?=$az_image?>
				<div class="editorial">The following article is the latest in the "DTV Transition - Can YOU Help?" series. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_1_transition_reception_and_help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception and Help</a></li>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_2_a_technical_view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></li>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_3_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_6_subsidy_settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>Part 4 - DTV Tuner Integration</b>
<p>This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  <p>As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">part 3</a> of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  <p>Would DTV tuner integration address this situation? Yes, for those that purchased an integrated DTV, but the rest would have to use a set-top-box tuner/converter to receive a digital signal, and in both cases an antenna pointing to the digital stations would be needed.  <p>The idea is simple, a digital tuner could be within the DTV, or could be within a separate set-top-box (STB). The STB could be used for an analog TV but also for a digital TV that lacks a tuner (a monitor manufactured before the FCC's tuner integration mandate).  <p>However, a digital monitor that does not have tuning capabilities can only give its full potential when connected to a digital HD-STB that is capable to tune and send to the DTV the SD/ED/HD program at its full resolution, a feature that is not possible with a government coupon-program tuner/converter.  <p>Analog TVs can only display at 480i resolution. A government-coupon-program converter would tune to digital channels but would downconvert their resolution to 480i so the analog TV could display them. Analog TVs do not need to be replaced if that level of image quality is satisfactory enough for the viewer.  <p>Coupon-program converters do not output digital HD signals as fully capable HD-STBs do. While it is possible to use these coupon-converters to feed analog 480i to even a tuner-less monitor DTV, the DTV resolution capabilities would be under utilized when a high-resolution image is tuned and the coupon-program converter box reduces its quality to just 480i.  <p>An integrated DTV tuner would perform a similar function as a separate HD-STB regarding tuning HD, but it has the virtue of not having a separate box around the TV and also save the cost/inconvenience of additional wiring to the TV.  <p>The trade-off? If the internal tuner fails, performs badly, or becomes obsolete, the integrated DTV itself is subjected to the inconvenience/service, rather than just a box that can be independently serviced or replaced while the TV can still be used with another STB or for other purposes.  <p><b></b> <h2>Original Integrated Tuner Mandate </h2> <p>In 2002, the FCC issued a mandate for over-the-air tuners to be gradually included into every DTV manufactured after that date. On-the-clear QAM cable tuners (for unscrambled content) were also included into DTVs as part of an industry agreement made around that time.  <p>Beware of some recent erroneous and misleading claims of uninformed journalists, such as: "<i>many of these DTVs lack ATSC tuners; all DTVs weren't mandated to include tuners until last March.</i>"  <p>The claim is "just" 5 years off.  <p>Actually <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">integrated DTVs</a> were gradually introduced with 2003 models following a five-year phased-in plan, which originally was issued as follows:  <ul> <li>50 percent of sets measuring 36 inches and larger by July 1, 2004; 100 percent by July 1, 2005.  <li>50 percent of sets measuring 25 inches to 35 inches were to add DTV tuners by July 1, 2005; 100 percent by July 1, 2006.  <li>The rest were to conform by July 1, 2007. </li></ul> <p>In 2005, that plan was modified with stricter deadlines and screen sizes.  <h4></h4> <h2>Updated Integrated Tuner Mandate </h2> <p>In mid 2005, the FCC made a tentative decision to change the deadline, and requested feedback from manufacturers, broadcasters, and industry trade groups.  <p>The decision was to accelerate the deadline of tuner integration for TVs under 25-inches to make them compliant 6 months earlier than planned, advancing the date from July 1, 2007 to December 31, 2006. The decision also included smaller sets under 13 inches, not included within the original mandate.  <p>Advancing the deadline was also viewed as a way to reduce the number of D/A converters that would be needed in the subsidy program when analog signals stop broadcasting.  <p>On their response, the CEA requested to eliminate the July 1, 2005 deadline that required manufacturers to make half of the 25/35-inches sets capable of receiving digital. The argument from manufacturers was that consumers would end up buying the cheaper analog sets, and retailers were less willing to order the more expensive digital sets. The FCC rejected that request.  <p>Walt Disney, the NAB, and the Association for Maximum Service Television (MSTV) urged the FCC to adopt the advanced deadline of December 31<sup>st</sup>, 2006, while the CEA, the CERC (Consumer Electronics Retailers' Association), Sharp Electronics, and Philips Electronics North America opposed to it, and claimed not having enough time to manufacture those DTVs by the end of 2006.  <p>The CEA and CERC issued the following statements: "<a name="OLE_LINK2"></a><a name="OLE_LINK1">the FCC should refrain from making any rulings regarding the inclusion of digital tuners in new </a>receivers with screen sizes less than 13 inches until manufacturers, retailers and the commission adequately are able to examine the impact of the small chassis products that currently are subject to the commission's tuner requirements." They opposed accelerating the timetable claiming that no evidence justified the change.  <p>Other comments from the CEA regarding the effect of accelerating the mandate on manufacturing and consumers were as follows:  <p>"Some manufacturers could opt to market monitor-only models that remove both digital and analog tuners, or stop manufacturing certain sets altogether. For smaller sets, 13 to 26 inches, the requirement would double the development costs for manufacturers, as well as double the price of a typical 13-inch television to consumers," the CEA said, and added: "If the product is rejected by lower income and other consumers because the price exceeds their budget, it will not be carried by retailers and, eventually, not produced by manufacturers."  <p>The CEA also said "the unfortunate result of accelerating the tuner mandate deadlines for all sets would be to decrease the number of DTV tuners in the marketplace, which clearly does not serve the transition."  <p>Finally, in November 2005, the FCC voted for setting the new date as March 1, 2007 for all sizes including those smaller than 13 inches, which received the support from the NAB taking into consideration how important they are in times of emergency and are commonly used without STBs.  <p>The FCC revised the deadline dates for DTVs to have integrated tuners as follows:</p> <table cellspacing="0" cellpadding="2" width="669" border="0"> <tbody> <tr> <td valign="top" width="139">Upon approval </td> <td valign="top" width="528">&gt;=36 inches </td></tr> <tr> <td valign="top" width="140">By March 1, 06 for </td> <td valign="top" width="528">&gt;= 25 inches (was July 1, 06 on the original plan) </td></tr> <tr> <td valign="top" width="141">By March 1, 07 for </td> <td valign="top" width="528">&gt;= 13 inches (was July 1, 07, and was agreed for March 07 although the FCC proposed it for December 31, 06) </td></tr> <tr> <td valign="top" width="141">By March 1, 07 for </td> <td valign="top" width="528">&lt; 13 inches (was not required before) </td></tr></tbody></table> <p>The mandate does not apply to other small screen video capable devices that do not receive analog OTA broadcasting, even when they might be used to watch TV shows, such as PDAs, mobile phones, iPODs, etc., but it applies to other non-screen devices that have analog tuners to perform their purpose, such as VCRs and DVD recorders.  <p>11.8 million DTVs were produced between 1998 and 2003 and most are tuner-less monitors that need an external STB tuner to view digital TV. Most of those DTVs also do not have DVI/HDMI digital connections with HDCP content protection; they only include component analog connections for HD.  <p>The lack of protected digital connectivity could render <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_blu-ray_only_if_your_hdtv_permits_it.php">them incapable</a> of eventually receiving protected premium content from an external STB when connected with analog component cables, which is another wrinkle of the DTV transition that hit hard on the efforts early-adopters did to help establish DTV in the US.  <p>The majority of the remaining 69.3 million DTVs that were sold between 2004 and 2007 have an integrated tuner to comply with the mandate, although many of that period were still permitted to be tuner-less depending on their size. A large part of these sets may also have QAM cable TV tuners for unscrambled content, and many of those also have CableCARDS for premium content, although with only unidirectional capabilities.  <p><b></b> <p><b></b> <h2>Integrated Tuner Mandate Enforcement </h2> <p>In June 2007 Regent USA (Maxent) and Syntax-Brillian (Olevia) were issued "apparent liability for forfeiture" notices by the FCC for allegedly failing to comply with the ATSC tuner mandate on the DTVs they manufactured, imported or shipped, and the FCC added, under "willful and repeated violations".  <h5></h5> <p>Regent was fined $63,650 for importing or shipping 1,182 non-compliant DTV tuners. Syntax-Brillian was fined $2,899,575, for 22,069 DTVs imported or shipped within the statute of limitations, and the FCC commented "We believe that the proposed forfeiture reflects the gravity of Syntax-Brillian's apparent violations, the company's ability to pay, and the need to deter Syntax-Brillian and other companies from future violations of the act and the rules".  <h2>Tuner-less DTVs Even Under the Mandate</h2> <p>In 2007, Toshiba introduced new lines of HD flat-panels, rear-projection, and direct-view sets that excluded the mandated over-the-air digital tuner by not including the analog tuner on the sets, which then qualified them as valid "monitors."  <p>Toshiba was not alone in cutting down on tuning components. CableCARD-less TV lines started to appear from various manufacturers that found no merit in integrating a unidirectional cable tuner with CableCARDs into DTVs while the cable industry was moving toward a bi-directional OCAP solution.  <p>Toshiba announced the 2007 monitors to cost $300 less than comparable "integrated" TVs with mandated tuners.  <p>In perspective, when integrated DTVs were introduced in 2003 the difference between a monitor DTV and an integrated version of the same TV <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">was $704 more on average</a>. Since 2003, millions of consumers have paid for integrated tuners they do not use because most subscribe to satellite or cable.  <p>Additionally, consumers could not know of the extra cost of the unneeded tuner because monitor-only versions of their integrated sets where no longer produced.  <p>"This is all the video display a consumer needs if they get programming from a cable or satellite TV box," Toshiba said.  <p>The next part (5) in this series will be dedicated to "<a href="/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">Was Tuner Integration Timed Right?</a>" </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November  3, 2008  9:42 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1539)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1539)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
					<?=stripslashes($author['bio_short'])?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
		</td><td id="right">
			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<?=getBoxDiscussions()?>
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php" type="text/javascript" charset="utf-8"></script>
	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;post_services=email%2Ctwitter%2Cdigg%2Cfacebook%2Cmyspace%2Csms%2Cdelicious%2Cstumbleupon%2Cgoogle_bmarks%2Clinkedin%2Cwindows_live%2Creddit%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine%2Cxanga&amp;linkfg=%23003F87&amp;button=false"></script>
	<script type="text/javascript">
		var shared_object = SHARETHIS.addEntry({title: document.title,url: document.location.href});

		shared_object.attachButton(document.getElementById("ck_sharethis"));
		shared_object.attachChicklet("email", document.getElementById("ck_email"));
		shared_object.attachChicklet("facebook", document.getElementById("ck_facebook"));
		shared_object.attachChicklet("twitter", document.getElementById("ck_twitter"));
	</script>
</div></body>
</html>
