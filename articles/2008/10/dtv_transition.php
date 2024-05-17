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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1531 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1531
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php&amp;title=DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households">
		<span style="display:none">Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.

Over recent years some of the figures tossed by...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1531";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</title>
	<meta name="keywords" content="million million, dtv transition, million dtvs, analog tvs, per household, million, digital, dtv, tvs, analog, dtvs, cable, households, transition, cea, year, sets, number, ratio, household, years, still, sold, per, stb" />
	<meta name="description" content="Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.

Over recent years some of the figures tossed by..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1531', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 27, 2008</b>
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
<li><a href="/articles/2008/11/dtv_transition_can_you_help_part_4_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_6_subsidy_settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>TVs vs. Households</b></p> <p>Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.</p> <h2>Number of TVs vs. Households</h2> <p>Over recent years some of the figures tossed by the press mixed and loosely compared the number of households with the number of TV sets in the US, and the number of cable/satellite household subscribers with the number of analog, digital or HD set-top-boxes, for example.</p> <p>From that mix, deceptive percentages and ratios were derived and presented in articles made to sensationalize preconceived opinions for a journalistic profit, and many of those authors were not even related to the DTV industry.</p> <p>My objective is to help the public with factual and accurate information, and to provide an analysis without an agenda of profitability from the situation at hand, so let us get to work.</p> <p>Up until 2007 the Consumer Electronics Association (CEA) used an average ratio of 2.6 TV sets per household (289 million active/inactive TVs of any kind installed into 111 million US households). Now the average ratio is 3.1 and is calculated as 346 million active/inactive TVs within 112.8 million households.</p> <p>After discussing the subject with the CEA's Senior Director of Market Research, he confirmed my assumption that the increased ratio responds to consumers purchasing newer DTVs to experience digital, but not to immediately replace any old TV, which might still perform well for some secondary service in the home.</p> <p>This particular situation has the effect of making the overall inventory of TVs increase more than the simple effect of replacing analog by digital (289 to 346 million), and making the ratio higher when relative to an almost unchanged number of households (111 to 112.8 million).</p> <p>However, of those 346 million about 300 million TVs are actually active (in use), which actually makes the average ratio of "active" TVs per household to 2.6. In my discussion with the CEA we agreed that although the other 46 million inactive TVs might still be functional, they have been moved to other non-living areas of the house, such as the attic.</p> <p>According to information obtained by CEA, the Senior Director of Market Research of the CEA said, 13% of the sampled households reported to have 5 TVs in the house, and 35% of the sampled households reported to have 4 TVs. When those groups joined the remaining 52% of households of the sampling, the average of TVs per household is 3.1 on the most recent year of the research.</p> <p>Does the US need those 346 million TVs to be digital for the February 17, 2009 deadline to be met? No.</p> <p>Those TVs do not have to be all digital by then, and the reality is that they could not all be replaced for digital sets in such a short time either, and probably they will continue performing an analog service in a digital world for years to come.</p> <p>A total of 81.1 million digital TVs were sold between 1998, when the DTV transition started, and December 2007. It is projected that approximately a cumulative 116 million DTVs in total will be sold by the February 17, 2009 deadline.</p> <p>This means that the US would still have about 230 million analog TV sets (346-116=230) by the February 2007 deadline. How is this mix going to affect the public receiving the variety of TV services within the US, such as broadcast, cable, satellite, IPTV, etc?</p> <h2>The Set-Top-Box Comes to Help </h2> <p>After the deadline, any analog TV would require a set-top-box (STB) to tune to a digital signal whether they are connected to an antenna, cable, satellite, etc. Let us analyze how that would happen.</p> <p>Small dish satellite (DirecTV and Dish Network) is already 100% digital, and each satellite subscriber household must have at least one satellite digital STB already installed. Satellite subscribers are automatically ready for the digital transition regardless of the TV they have, and although HD is not a requirement for the digital service, the digital STB has to be HD capable to receive HD channels from the satellite provider.</p> <p>Some cable companies converted all of their subscribers to digital by replacing analog STBs by digital STBs when needed, and migrating their service to support only digital tiers. The complete switch to digital and the upfront investment in digital STBs can be an incentive to some companies when the bandwidth required by discontinued analog channels could be released and used for more digital channels or for more efficiently managed services that could generate more revenue in the long run.</p> <p>Other cable companies are gradually converting subscribers to digital tiers while temporarily continuing with analog tiers, under the flexibility given by the FCC to cable companies, by which they have until 2012 to switch to full digital.</p> <p>The flexibility also benefits many millions of analog subscribers that have their analog TVs directly connected to the wall-coax to tune basic analog channels without having to lease a cable set-top-box. </p> <p>In other words, regardless of whether the STBs are cable, satellite, or over-the-air, they would allow millions of analog TVs to still perform at their level of resolution (480i) regardless of the digital signal tuned by the STB.</p> <p>The vast majority of the remaining 230 million TVs mentioned earlier would probably not need to be replaced by DTVs any time soon as long as STBs are used, or analog tiers are continued by some cable companies until 2012.</p> <p>Although many integrated DTVs have internal digital QAM cable tuners the cable subscriber may still require a cable STB for certain services. The integrated tuners might even have Cable CARDs but the tuners are only uni-directional and do not support the cable supplied EPG (Electronic Program Guide), VOD (Video on Demand), and Impulse PPV (Pay-per-view) bi-directional cable features, so a cable STB is needed.</p> <p>This can be viewed as a duplicated investment for many DTV owners, who paid once for the integrated ATSC/cable tuners mandated by the FCC within the purchased DTV, and paid again when leasing the cable STB with bi-directional capabilities to support the features above.</p> <p>In other words, if the cable subscriber a) owns a DTV with QAM digital cable tuning capabilities w/o CableCARD and wants to tune to premium channels, or b) owns a DTV with a CableCARD for premium channels, but wants bi-directional services (VOD, etc), a digital cable STB will still be needed, which is now mandated by the FCC to have a CableCARD within it.</p> <h2>Number of DTVs Sold Since 1998</h2> <p>Although this article is not about reconciling numbers or counting beans for journalism purposes, the information below helps provide a sky-high perspective of where we are with the DTV transition.</p> <p>On each of my HDTV annual reports I analyze the progress of the DTV installed base by technology (plasma, LCD, DLP, etc), and provide a projection for the next several years. Below is a yearly summary of the DTVs sold to dealers since the beginning of the DTV transition. The first table immediately below shows figures sourced from the CEA as of July 31, 2008:</p> <table class="type1b"><tr><td class="type1b_header">Year</p></td> <td class="type1b_header">DTV Sets</td></tr> <tr> <td valign="top" width="57">2011</td> <td valign="top" width="580">40.8 million (cumulative end of year 2011, 228.7 million)</td></tr> <tr> <td valign="top" width="57">2010</td> <td valign="top" width="580">38.4 million</td></tr> <tr> <td valign="top" width="57">2009</td> <td valign="top" width="580">35.8 projected million</td></tr> <tr> <td valign="top" width="57">2008</td> <td valign="top" width="580">32.6 estimated million (cumulative end of year 2008, 113.7 million)</td></tr> <tr> <td valign="top" width="57">2007</td> <td valign="top" width="580">26.4 million (cumulative end of year 2007, 81.1 million)</td></tr> <tr> <td valign="top" width="57">2006</td> <td valign="top" width="580">23.5 million (cumulative end of year 2006, 54.7 million)</td></tr> <tr> <td valign="top" width="57">2005</td> <td valign="top" width="580">11.4 million</td></tr> <tr> <td valign="top" width="57">2004</td> <td valign="top" width="580">8 million</td></tr> <tr> <td valign="top" width="57">2003</td> <td valign="top" width="580">5.5 million</td></tr> <tr> <td valign="top" width="57">2002</td> <td valign="top" width="580">4.1 million</td></tr> <tr> <td valign="top" width="57">2001</td> <td valign="top" width="580">1.5 million</td></tr> <tr> <td valign="top" width="57">2000</td> <td valign="top" width="580">0.6 million</td></tr> <tr> <td valign="top" width="57">1999</td> <td valign="top" width="580">0.1 million</td></tr> <tr> <td valign="top" width="57">1998</td> <td valign="top" width="580">0.0 million</td></tr></tbody></table> <p>As you see from the above, a total of 81.1 million DTVs were confirmed as actually sold between 1998 and December 2007. Those 81.1 million DTVs are expected and are capable of replacing and performing the job as part of the whole inventory of 346 million TV sets available in the whole US.</p> <p>In summary, the installed base grew beyond a replacement purpose and has now a higher ratio (3.1) of TVs per household, the 81.1 million purchased DTVs are not necessarily replacing analog sets that will be inactive, but they make a household now DTV capable with at least one digital set.</p> <p>So it is obvious that quite a few more years will be needed before all legacy analog TVs can be actually replaced by digital sets, but the transition does not expect that all the TVs have to be replaced to be able to switch the analog NTSC system to digital, regardless of the date.</p> <p>For a long time to come there will be a mix of new digital TVs and old analog TVs that be eventually replaced, or in many cases not ever replaced, depending on their purpose and if they still perform well as display devices for the needed image.</p> <p>To have an estimate of an "until today" (July 2008) DTV-sold figure, one can take half of the 2008 projection above (16 million from the full year's 32.6 million) and add it to the 81.1 million figure of 1998-2007, making the total for the period 1998-1H08 to be about 97.1 million DTVs (81.1 + 16).</p> <p>However, it is customary to use actual annual figures only when the period is completed to have the opportunity to confirm or to revise the estimate for that year. The actual figure for 2008 would be available sometime in mid-2009.</p> <p>In the past, even actual figures published of earlier years, not just the estimate of the previous year, were subjected to further revision by the CEA to refine the count based on improved feedback from the CE industry. My annual reports include the CEA adjustments when they are made available, usually a few months after my reports are published for the year.</p> <p>The most accurate figures are in my 2007 report. The CEA figures mentioned in the table above are very similar to the ones I published in my 2007 report (below) with the information available at that time:</p> <table class="type1b"><tr><td class="type1b_header">Year</td> <td class="type1b_header">DTV sets (as I reported in the <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">2007 HDTV Review</a>)</td></tr> <tr> <td valign="top" width="63">98-01</td> <td valign="top" width="516">1.4 million (revised now to 2.2 million by CEA in 2008 as above)</td></tr> <tr> <td valign="top" width="66">2002</td> <td valign="top" width="516">4.1 million (match, but also reported as 2.5 million in earlier reports)</td></tr> <tr> <td valign="top" width="68">2003</td> <td valign="top" width="516">5.5 million (match, but also reported as 4.1 million in earlier reports)</td></tr> <tr> <td valign="top" width="70">2004</td> <td valign="top" width="516">8.0 million (match, revised from 8.2 million reported in 2006)</td></tr> <tr> <td valign="top" width="71">2005</td> <td valign="top" width="516">11.3 million (revised to 11.4 million in 2008)</td></tr> <tr> <td valign="top" width="72">2006</td> <td valign="top" width="516">23.9 million (confirmed in 2008 as 23.5 million actual)</td></tr> <tr> <td valign="top" width="73">2007</td> <td valign="top" width="516">29.2 million (confirmed in 2008 as 26.4 million actual)</td></tr> <tr> <td valign="top" width="74">Total</td> <td valign="top" width="516"><b>83.4 million (Nov 98 - Dec 2007)</b> as I reported in 2007.<br><br>Confirmed now as <b>81.1</b> million actual in 2008 (in CEA table above)</td></tr></tbody></table> <p>In other words, a negative adjustment of about -2.3 million DTVs (from 83.4 to 81.1 million) was made for the 1998-2007 period, which is about 2.7% reduction over the figures provided on the 2007 report, mainly due to revising projected/estimated figures by actual figures, as it happens every year.</p> <h2>The Projections, and the Expected TV Replacement Behavior</h2> <p>Looking ahead over 3 years, CEA's estimate of 228.7 million DTVs sold by 2011 would still be short of replacing all the TVs in the US. If the 40 million per year rate for the single year 2011 projected by the CEA were consistently maintained for the years beyond 2011, an additional period of 7 years (counting from mid-2008) would be needed for the full replacement of analog TVs on the entire population (by 2014).</p> <p>But again, because many of the current analog TVs might still be useful for their purpose (video games, pre-recorded movies, external STB, etc) there should not be a rush in declaring them obsolete just because of a DTV transition deadline or because they are not digital.</p> <p>Additionally, the 346 million DTVs of today are a moving target inventory, when consumers buy new DTVs not necessarily to replace analog TVs and declare them inactive. The total number will grow and the ratio per household will grow as well perhaps for a few more years until a majority of households find no need to keep so many active and inactive sets in the home, regardless how many DTVs they buy.</p> <p>In retrospect, all B&amp;W TVs did not need to be replaced in a rush when color television arrived decades ago. Back then it was due to backward compatibility of the color system to a B&amp;W TV by design; now, it is thanks to a STB tuner/converter of digital-to-analog. Even today, some households might still have some of those B&amp;W sets around for some basic purpose, if they still work.</p> <p>People would naturally replace analog TVs as needed. Current DTVs would also be replaced for newer models as needed, and those replaced DTVs would probably be moved to other rooms in the house to perform other secondary tasks, replacing analog TVs that are doing that task, gradually shifting out old analog sets as new DTV sets come into the households.</p> <h2>Who Owns the 81.1 Million DTVs?</h2> <p>One item that I regularly analyze in my annual reviews is that the 81.1 million DTVs sold over the first 10 years of the DTV transition is not necessarily equivalent to a 1-to-1 installed ratio in a similar number of households (having one DTV at each home).</p> <p>Many early-adopters that bought DTVs since 1998 most likely have already purchased their second and even third DTV set for their homes during these first ten years of transition, making the actual number of households having DTV much smaller than the 81.1 million DTVs sold.</p> <p>The purchase pattern of early-adopters is usually driven by the satisfaction of experiencing technology challenges and innovations, having cost as a secondary factor, if at all. This is not the pattern of the budget oriented consumer electronics market, and certainly not the red-tag-sale weekend-ad consumers looking for the best deal at the right time for their pockets.</p> <p>By the end of 2007, Gary Shapiro, CEA president and CEO, declared, "I am proud to announce our nation has hit this digital milestone. With 50 percent of U.S. homes able to experience the reality of digital television, we have crossed a critical threshold."</p> <p>Although the statement from the CEA was an estimate, it seems to be quite accurate. 50% of 112 million households in the US = 56 million households. It is consistent with my analysis above of not following a 1-to-1 ratio for the first wave of DTV consumers.</p> <p>The estimate means that the 81.1 million DTVs sold are actually installed into 56 million households; many of those are early-adopters, making the sold-DTV average ratio as 1.5 DTVs per household.</p> <p>It is likely that the households of early-adopters that purchased most of the 81.1 million of DTVs until 2007 are the main reason of the 5 TV per household ratio mentioned earlier (13% of the sampling).</p> <h2>Final Thoughts on the DTV Adoption</h2> <p>In summary, the DTV market conditions and the ratio of DTV per household will gradually increase over the next few years because:</p> <p>a) DTV prices go further down and attract lower income groups, becoming more accessible to the remaining 56 million households,</p> <p>b) More households continue to acquire multiple DTVs without immediately disposing of current analog TVs,</p> <p>c) The total number of TVs in the US increases further beyond the actually used (active),</p> <p>d) That number is also a moving target that one should not necessarily expect to be fully replaced (such as the inactive 46 million out of the 346 in 2007 mentioned above),</p> <p>e) The analog shut-off will be a strong motivator when the event actually happens as planned in February 2009, and </p> <p>f) Blu-ray increases its footprint for the enjoyment of higher quality pre-recorded HD media, while consumers strive for larger 1080p screens, making the acquisition of an HDTV more appealing when able to realize the full potential of both technologies without having to resort to DVD upconversion/video processing. Blu-ray also brings a great opportunity to finally get "the real picture".</p> <p>Stay tuned for the next part (4) in this series, dedicated to Integrated DTVs.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 27, 2008  9:58 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1531)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1531)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php" type="text/javascript" charset="utf-8"></script>
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
