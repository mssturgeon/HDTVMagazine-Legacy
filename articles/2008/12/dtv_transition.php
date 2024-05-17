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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1566 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1566
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-5-was-tuner-integration-timed-right.php&amp;title=DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?">
		<span style="display:none">This part in the series discusses the implementation of tuner integration, its timing within the DTV Transition, and its impact on existing and future DTVs. I will talk about over-the-air (OTA) and cable reception, integrated tuners and set-top-boxes (STBs), and satellite STBs.

This is an analysis of the facts related to tuner integration as implemented over the past 6 years within the 10-year DTV transition, and how those facts affect consumers depending on the service they use (broadcast, cable, satellite, or Telco).

At closing I will also identify...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1566";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</title>
	<meta name="keywords" content="directional cable, dtv transition, tuner integration, integrated dtv, integrated dtvs, dtv, cable, tuner, integrated, tuners, stb, dtvs, digital, stbs, analog, directional, satellite, integration, ota, transition, need, part, broadcast, cost, could" />
	<meta name="description" content="This part in the series discusses the implementation of tuner integration, its timing within the DTV Transition, and its impact on existing and future DTVs. I will talk about over-the-air (OTA) and cable reception, integrated tuners and set-top-boxes (STBs), and satellite STBs.

This is an analysis of the facts related to tuner integration as implemented over the past 6 years within the 10-year DTV transition, and how those facts affect consumers depending on the service they use (broadcast, cable, satellite, or Telco).

At closing I will also identify..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-5-was-tuner-integration-timed-right.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1566', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-5-was-tuner-integration-timed-right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December 18, 2008</b>
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
<ul><li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception and Help</a></li>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></li>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul><p>Please note that we have included references to external websites within this article. These are included for research and information purposes only and should not be interpreted as an endorsement of their products and/or services.</p></div>
<br />
<p align="center"><b>Part 5 - Was Tuner Integration Timed Right?</b> <p>This part in the series discusses the implementation of tuner integration, its timing within the DTV Transition, and its impact on existing and future DTVs. I will talk about over-the-air (OTA) and cable reception, integrated tuners and set-top-boxes (STBs), and satellite STBs.  <p>Let me be clear on one point right up front: This article is neither an endorsement nor a criticism of a mandate that is now history. This is an analysis of the facts related to tuner integration as implemented over the past 6 years within the 10-year DTV transition, and how those facts affect consumers depending on the service they use (broadcast, cable, satellite, or Telco).  <p>At closing I will also identify the available options of DTVs and STBs so you can decide what is best for your particular tuning situation. Hopefully you could help others with this as well, as that is the primary purpose of this series of articles.  <h2>Why Integrated Tuners?</h2> <p>Since its inception in the mid 1900s, TV traditionally performed a tune-and-display role in a world of broadcast-only tuning. In time, cable and satellite came along and brought an alternative to broadcast content distribution, but they required a different tuner.  <p>This gave birth to the STB approach, a tuner outside the TV. Later, analog cable tuners were incorporated into cable-ready analog TVs to tune to unscrambled content.  <p>When premium content (e.g. HBO) arrived, in order to protect the investment and the effort of creating the content, service providers implemented security controls under a pay distribution model to unscramble premium content, which required an STB even when the analog TV was cable-ready.  <p>When turning the page from analog to digital, the video content distribution model grew with more features but also with more complexity for equipment and connectivity, with CableCARDs, digital/analog conversions, image resolution controls, integrated digital DVRs, selectable output controls for content protection, digital audio and video connections, etc.  <p>The complexity certainly affected user friendliness in millions of households that were already accustomed to live with blinking 12:00 VCRs. Incidentally, JVC (the creator of VHS) just announced the end of the manufacturing of single VCRs units.  <p><a name="OLE_LINK1"></a> <p>The idea of integrating tuners into DTVs responds to the same tune-and-display concept of decades of broadcast TV, however, the timing of applying the same concept to DTV is a subject that looks simpler than it is.  <p>Factors like cost, maturity, reliability, upgradeability, and serviceability of a digital tuner were not at their prime in 2002, enough to discourage integration at that time.  <p>However, the alternative of not-integrating then and wait for the best timing of the combination of all of those factors might not have helped the transition and could potentially expand the risk beyond the effort of coupon-program converters to help 15 million households with analog TVs not go dark on February 2009.  <p>Why beyond? Because not integrating could have instead meant having 100+ million <b>tuner-less DTV monitors</b> installed in more than <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">56 million households</a> (about 50% of US households) by the transition deadline, and many millions of those risking going dark if used for broadcast and have no tuner.  <p>Although they could be connected to HD-STBs to avoid going dark, HD-STBs are <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">3 times more expensive</a> than <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program digital-to-analog converters</a>, and are not subsidized by the Government coupon-program as converters for analog TVs are.  <p>I will mention a few more factors surrounding this matter later in this article.  <h2>Integrating Cable</h2> <p>As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">part 4</a>, integrated DTVs have been gradually manufactured with internal over-the-air (OTA) tuners to comply with the FCC's mandate proposed in 2002.  <p>At that time an agreement was made with the cable industry for DTVs to also include a QAM digital cable tuner for on-the-clear unscrambled programming; some DTVs also included a CableCARD slot for the QAM cable tuner to be able to tune scrambled premium programming (e.g. HBO) without using a cable set-top-box (STB).  <p>While digital cable STBs are bi-directional to permit Video-on-Demand (VOD), Impulse Pay-Per-View (PPV), and cable supplied Electronic Program Guide (EPG), the cable tuners integrated within DTVs are only uni-directional and cannot perform those services.  <p>More details about cable integration are included further below.  <h2>(Dis)Integrating Satellite </h2> <p>Although digital cable tuners were integrated into DTVs, small-dish-satellite tuners were not, except for two Thomson/RCA CRT DTVs with DirecTV tuners manufactured in the late 90s, a 38" Direct-view and a 61" rear-projection set.  <p>Our own <a href="http://www.hdtvmagazine.com/about/contact.php?name=milbourn">Edward Milbourn</a>, a Thomson/RCA manager at that time, participated in the introduction of those and in the creation of the DTC-100, the first DirecTV HD-STB.  <p>The DTC-100 was one of the most reliable satellite HD-STBs ever created. It was a workhorse that even received occasional firmware upgrades though the dish to improve its functionality and performance, a normal feature now, but visualize that almost a decade ago. I still keep my DTC-100 as a symbol of the beginnings of small-satellite HDTV.  <p>In Ed's words "the DTC-100 satellite HD-STB, the 61" DTV, and the 38" DTV, in that order, were introduced over a six month period of time because of the time required to obtain DirecTV certification (the hardest part of the project)."  <p>There are no plans disclosed to the public to integrate satellite tuners within DTVs, so get accustomed to having a perennial HD-STB solution, HD wires, A/V rack space, the need for managing multiple HD inputs, eventual selectable output controls for protected content when connected with component analog connections (a problem for DTV early adopters), and the mood of HDCP, DVI and HDMI in some equipment.  <p>The same picture applies to digital cable STBs now, but help is on its way with Tru2way DTV integration, more on that later.  <h2>Integrate All Tuners?</h2> <p>Traditionally, cable STBs do not include satellite tuners and vice versa, but that should not surprise anyone, they are in direct competition for subscribers.  <p>Cable-company supplied STBs usually do not include over-the-air tuners for free broadcast TV, although some cable DVRs and combo STBs for PC networking have OTA tuners.  <p>Satellite HD-STBs from Dish Network, such as the ViP722, include OTA tuners (analog and digital), however, while DirecTV used to include OTA tuners on their HD-STBs, the <a href="http://www.solidsignal.com/prod_display.asp?PROD=H21" target="_blank">most recent DirecTV STB models</a> do not, which can be solved by adding <a href="http://www.solidsignal.com/prod_display.asp?PROD=AM21">another STB </a>between the antenna and the satellite tuner. This STB provides the missing broadcast tuning functionality.  <p><a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">Over-the-air STBs</a> for broadcast do not include any other tuners.  <p>Telecom companies and <a href="http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV (Internet Protocol Television)</a> are also competing for TV subscribers and use STBs.  <p>In other words, no STB has multiple tuners for all possible services into a single cabinet, nor is it viewed that a DTV should have all of them integrated, because consumers usually select only one of those services to receive TV (broadcast, cable, satellite, or Telco), and considering the price of tuners, why would a consumer be interested in paying for all of those tuners within a DTV or within a universal STB?  <p>Therefore, a consumer should expect to have multiple STBs on the audio/video rack if all of those services are wanted, even when having a DTV that is already tuner-integrated with OTA and cable.  <p>The cost of internal tuner parts installed into a DTV should be lower than the price of an STB because a DTV does not need the STB cabinet, front panel, buttons, rear connections, power supply, remote control, STB assembly labor, etc., but the reality is that after 10 years of DTV transition, the price consumers pay for tuner integration has not come down as low as one might expect (details later in the article).  <p>Every service provider company has introductory packages for new subscribers, but individually purchased HD-STBs/DVRs for <a href="http://www.solidsignal.com/prod_display.asp?prod=TCD653080">cable</a>, <a href="http://www.solidsignal.com/cat_display.asp?main_cat=02&amp;CAT=DIRECTV%20Receivers">DirecTV</a>, <a href="http://www.solidsignal.com/cat_display.asp?main_cat=02&amp;CAT=DISH%20Network%20Receivers">Dish Network</a>, and <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">OTA</a> are not as low as it should be expected.  <p><b></b> <h2>The Benefit of Monitors </h2> <p>Before the integrated-tuner idea was proposed by the FCC in 2002, digital televisions manufactured since 1998 were monitors without digital tuning capabilities.  <p>The monitor DTVs need an HD-STB to tune over-the-air broadcast when connected to a UHF/VHF antenna. Later, HD-STBs for satellite reception (DirecTV and Dish Network) and for digital cable were introduced. The HD-STBs require component analog or DVI/HDMI wires for HD signals.  <p>The monitor approach provided several benefits to consumers, especially early in the transition, when DTVs and STBs were very pricey and immature. It was common to recommend a separation between the STB and the DTV monitor, at least until tuners cost less and become more reliable to be part of an expensive TV set.  <p>In perspective, a 1999 Pioneer Elite OTA tuner ($3000) was connected to a 64" CRT rear-projection HDTV Pioneer Elite monitor (close to $10,000). Should the two have been integrated back then a $13,000 300-pound DTV might have forced a costly home service if the internal tuner failed.  <p>I recall my comment on my 2003 <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology Review</a> "Over the last five years we have experienced all kinds of early adoption let downs regarding HD-STBs. Noisy fans, hot units, slow/unfriendly menus, weird software behavior, frozen units, dead units, killed units on firmware upgrades, etc."  <p>During the first couple of years of the integration mandate, the cost of integrated DTVs was considerably higher than the monitor versions. Not only were the added tuner-parts expensive and low in volume, but there were risk factors about tuner's performance, serviceability, failure, obsolescence, etc., whereas a troubled tuner in a separate HD-STB would not compromise and inconvenience the larger DTV investment, in fact it could be as easy as asking the service provider for a new upgraded unit at no cost.  <p><b></b> <h2>An Integrated DTV Looking for an HD-STB </h2> <p>Regarding tuner's performance and obsolescence, in a recent <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=34793">thread</a> (29 Oct 2008 01:19 pm) in response to <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">Part 3 </a>of this series of articles a reader was surprised when a low-cost coupon-program tuner was sensitive enough to pick up 40+ DTV stations, better than the integrated tuner of his high quality new Sony DTV using the same over-the-air antenna.  <p>Enticed by the experience, he was looking for an HD-STB that would be as sensitive as the coupon-program-tuner, and use the HD-STB to do the tuning for his Sony DTV, rather than having the DTV do the tuning.  <p>Ironically, adding an STB to an integrated DTV defeats the basic concept of integration, but if having a separate box and cabling is not an issue it could be worth a try. Modularity generally offers better flexibility, upgradeability, serviceability, and replace-ability.  <p>Although new generations of tuners are expected to perform better, it is a mixed bag considering the numerous choices, prices, and quality among dozens of STB and DTV manufacturers.  <h2>Cable Impact on Integrated DTVs </h2> <p><b></b> <p>Since the mandate, consumers have purchased 107.4 million DTVs (from 2003 to 4Q08, <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">refer to Part 3 of this series</a>). Although some of those are monitors, most are integrated DTVs.  <p>As mentioned earlier, because QAM cable tuners in integrated DTVs were only implemented with uni-directional capabilities, millions of DTV owners had to lease or purchase a cable HD-STB for bi-directional functionality, an additional expense to their "tuner-ready" integrated DTV.  <p>Although tuner integration is also expected to eventually support bi-directional cable tuners within DTVs, it has only reached a mid-point solution.  <p>Panasonic <a href="http://www2.panasonic.com/webapp/wcs/stores/servlet/prModelDetail?storeId=11301&amp;catalogId=13251&amp;itemId=304735&amp;modelNo=Content10152008035707436&amp;surfModel=Content10152008035707436">just announced</a> the introduction by fall 2008 of a couple of integrated DTV models with bi-directional cable tuners (facilitated by a platform named <a href="http://www.tru2way.com/">tru2way</a>), but the industry keeps implementing millions of uni-directional cable tuners within most cable-ready DTVs, and the impact to consumers will grow until all models from all DTV manufacturers are tru2way capable.  <p>Visualize this analogy for a minute: imagine that since 2002 people can only buy cars having a forward-only transmission (uni-directional tuners in DTVs) that cannot be modified or upgraded for reverse. Conversely, imagine if rent-a-cars were built capable to drive in both forward and reverse (bi-directional cable features in STBs). The days you need to park in reverse you better lease a rent-a-car (cable STB) and leave your car (integrated DTV tuner) in the garage.  <p>Here is a short quiz: As a car (DTV) owner, what would be your overall cost to be able to drive in both directions?  <p>How long would you think the car sale industry could have lasted under those rules?  <p>When the tuner integration mandate idea started in 2002 the expectation was that it might take another couple of years for bi-directionally to be into DTV integrated cable tuners, so it was agreed to start installing uni-directional cable tuners to move on. We are almost in 2009 and are still waiting.  <p>Assuming it could take another couple of years before we see tru2way fully deployed by all in the industry, another<a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php"> 74.2 million DTVs</a> are projected to be in the hands of consumers in 2009 and 2010, their cable tuners would be only uni-directional.  <p>Adding 74.2 million DTVs to the 107.4 million DTVs that were already sold until 2008 makes a total of 181.6 million integrated DTVs estimated by the end of 2010, which is about 52% of the existing 346 million TVs in the whole US (analog and digital). Over the past 5 years I predicted the above scenario in every <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology annual report</a>.  <p>However, the alternative of waiting until a bi-directional solution was created, embraced by the industry, and integrated in volume was discouraged because it was apparently less favorable to the DTV transition and the consumer. Some related factors are mentioned below.  <h2>Was Tuner Integration Timed Right?</h2> <p>Again, this article is neither an endorsement nor a criticism of a mandate that is now history, but rather it is an analysis after 6 years of facts.  <p>In theory, the concept of tuner integration should have spared a viewer of DTV broadcast from the need to purchase an external OTA digital STB, but because only a minority tunes to OTA broadcast, it meant that a majority of DTV owners still needed to purchase or lease an STB (satellite, bi-directional cable, FiOS, etc).  <p>The driver of the tuning consumer decision was the preference of the content distribution service, but what if the cost factor of monitor vs. integrated was included in the analysis?  <p>When the mandate was issued, integrated DTVs cost consumers <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">$704 more </a>on average compared to their tuner-less monitor versions (2003/2004 models). Over the past few years several manufactures (Westinghouse, Hitachi, etc.) introduced a few compliant monitors (because they also lacked analog tuners), but most if not all 2008 integrated DTVs from most manufacturers have no monitor versions of the same set, therefore I cannot make an industry wide comparison study as I did in 2003.  <p>However, in 2007, one major manufacturer announced monitors that cost $300 less than their similar integrated versions, and in September 2008 at CEDIA, Bob Perry, Senior VP of Panasonic, said that the new "tru2way" bi-directional cable integrated DTVs (mentioned earlier for fall 2008) will be priced $300 more than their non-tru2way counterparts. This only compares integrated uni-directional to integrated bi-directional cable DTVs. The price difference to tuner-less monitors should obviously be higher, if available.  <p>In summary, even after 6 years of the issued mandate, the price to consumers for tuner integration is not as negligible as OTA/cable analog tuners were for NTSC color television. Some say that is the price of innovation.  <p>On the other hand, if the integration mandate/cable agreement would have been implemented only when matured tuners reached a negligible cost to consumers, the delay could have affected other factors, and could have potentially damaged the success and the timing of the overall transition.  <p>Leaving aside how you receive content and which are your personal preferences, how could you evaluate if the 2002 decision was timed right for the overall public?  <p>Many issues merge together to make the decision more complex than it looks on the surface. I will mention just a few factors to consider:  <p>a) If waiting too long for integration, the fast grown DTV installed base (107.4 million until 2008) could have been made of 100% tuner-less monitors, one third of the US TV inventory (346 million DTVs). Some may consider that enough reason not to wait,  <p>b) From another view, the above could have been a positive cost factor for consumers if the monitors were installed in households that did not need broadcast tuners (the vast majority). The larger that group, the lower the overall cost, regardless who pays for it,  <p>c) The high cost of OTA tuners back in 2002. The uncertainty of when economies of scale will kick-in for reliable tuners in large volume,  <p>d) The need for sufficient volume of matured and reliable tuners for the millions of DTVs produced since 2003,  <p>e) The risk of waiting for tuner maturity, upgradeability, proper functionality, and reliability, to provide enough confidence for the tuner to be a part of a large DTV investment,  <p>f) The lack of cable industry readiness within the DTV transition in regard to hardware, software, content protection, CableCARD, uni/bi-directionality,  <p>g) The timing of designing and implementing bi-directional hardware/software solutions for cable STBs and integrated tuners should have been earlier on the DTV development phase (pre-1998), rather than waiting a few years after 1998 to show interest in HDTV, and wait until the integrated-tuner mandate to just implement a half-way uni-directional cable solution.  <p>h) Work on content protection and digital connectivity could have started much earlier in the pre-DTV transition phase. Both affect HD premium content distribution for cable and satellite subscribers, not to mention 11.8 million HDTVs of early adopters with component analog connections (pre DVI/HDMI).  <p>i) The over-the-air tuner mandate was for 100% of the DTVs; only <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1.php">15 million households</a> are estimated to receive broadcast, which is about 13.3% of the total 112.8 households in the US,  <p>j) The remaining 86.7% of households subscribe to satellite/cable/Telco. As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">Part 3 of this series of articles</a> a few of those may have secondary DTVs that are connected to antennas, for which an integrated OTA tuner could be useful. On the other hand, a lower cost monitor and an HD-STB could have made the overall cost of ownership lower, even today,  <p>k) The 86.7% of satellite/cable/Telco subscriber household footprint is large enough (97.8 million households) to justify offering a monitor option, letting consumers decide what to buy depending on tuning needs,  <p>l) The content distribution model in the 21<sup>st</sup> century is more competitive (cable, satellite, broadcast, FiOS, etc.) compared to the broadcast-only NTSC analog beginnings in the mid 1900s, which required an OTA tuner for all TVs,  <p>m) If tuner integration were decided to be optional, having monitors and integrated models for similar DTVs would have added complexity to the manufacturing, inventory management, distribution, dealer showroom, sale, service, warranty, and parts.  <h2>Solving your Specific Tuning </h2> <p>a) <u>If you are a satellite (or Telco TV) subscriber</u> and:  <p>a.1) Tune local channels with the STB: buy a monitor if available, there is no need for the integrated OTA tuner within the DTV.  <p>a.2) Tune local channels with an OTA antenna: buy an integrated DTV and use its internal OTA tuner, or buy a DTV monitor with an <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">over-the-air HD-STB</a> (not a <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program converter STB</a>, which is only 480i analog/SD resolution quality).  <p>In both cases the STB has to be connected to the DTV with component or DVI/HDMI digital video connections to view HD.  <p>b) <u>If you view broadcast TV</u> and:  <p>b.1) Are not ready to buy a DTV: you can still use a current analog TV with a <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program converter</a>. <a href="https://www.dtv2009.gov/">Request the $40 coupon</a> and pay for the difference, if any. Connect the converter to your analog TV using the RF, composite, or S-video connections, which are limited to the TV's 480i resolution. There is no need for the higher quality component or DVI/HDMI digital video cables.  <p>If you need more than two coupon-program converters for additional analog TVs you would have to pay the full price of each extra <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program converter</a>, there is no government subsidy for extra converters beyond the allowed two.  <p>b.2) Are ready and want to view DTV: buy an integrated DTV, it should have the needed OTA digital (and analog) tuner, otherwise, if the DTV is not integrated, you need to purchase an <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">over-the-air HD-STB</a> because the DTV is a tuner-less monitor: <p>b.2.1) Manufactured before the mandated deadline for the screen size (mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">Part 4</a>), or  <p>b.2.2) Was recently manufactured but is one of the monitor exceptions mentioned earlier in this article.  <p>c) <u>If you are a cable subscriber</u> and:  <p>c.1) Do not need DTV quality for your image requirements: you can keep using your existing analog TV. If the cable service is offered only in digital lease or buy a digital cable STB. If the cable company still sends the analog feed connect the TV directly to the wall RF coax plate to tune to basic programming, or lease an analog cable STB if you want premium services. Refer to (a) for local channels.  <p>c.2) Need to view DTV but do not need bi-directional cable functionality:  <p>c.2.1) Buy a cable-ready integrated DTV with a QAM on-the-clear tuner for non-premium channels; connect the coax wire directly to the DTV.  <p>c.2.2) Buy a cable-ready integrated DTV with CableCARD for premium channels; connect the coax wire directly to the DTV.  <p>c.2.3) Buy a DTV monitor and connect to a digital cable STB (even when you do not need bi-directional cable functionality). The STB has to be connected to the DTV with component or DVI/HDMI digital video connections to view HD.  <p>Refer to (a) for local channels.  <p>c.3) Need to view DTV, want bi-directional cable functionality but cannot wait for a tru2way integrated DTV: buy a monitor DTV (or integrated DTV if none is available) and lease an HD-STB/DVR from the cable company, or purchase a <a href="http://www.solidsignal.com/prod_display.asp?prod=TCD653080">TIVO DVR</a>. Refer to (a) for local channels.  <p>c.4) Need to view DTV, want bi-directional cable functionality and a tru2way DTV is available: buy it, it should have all you need for bi-directional cable functionality without resorting to digital cable STBs and extra wiring. Refer to (a) for local channels.  <p>Stay tuned for the next part (6) in this series, dealing with Subsidy Set-top-boxes</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December 18, 2008  9:07 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1566)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1566)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-5-was-tuner-integration-timed-right.php" type="text/javascript" charset="utf-8"></script>
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
