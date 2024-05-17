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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 882 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 882
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2008/01/sharp-demonstrates-lcd-display-and-video-prowess-at-ces-with-enhanced-tv-features-and-groundbreaking-new-technologies.php&amp;title=Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies">
		<span style="display:none">Sharp continues to change the LCD industry with one-of-a-kind innovations, increased performance and dazzling new designs. Reinforcing its commitment to the display industry at CES 2008, Sharp is demonstrating several revolutionary products and technologies that help to make the LCD TV both a design statement and the leading technology for home entertainment.

Sharp is taking LCD technology to new levels and responding to consumer demand for stylish, unobtrusive home theater products. For the first time in the U.S., Sharp is showcasing...</span></a>
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

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 882";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies" />
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
	<title>HDTV Magazine - Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies</title>
	<meta name="keywords" content="blu ray, aquos net, aquos lcd, high definition, lcd tvs, aquos, sharp, lcd, new, available, video, screen, room, system, hdmi, well, provides, models, features, blu, ray, products, television, inch, panel" />
	<meta name="description" content="Sharp continues to change the LCD industry with one-of-a-kind innovations, increased performance and dazzling new designs. Reinforcing its commitment to the display industry at CES 2008, Sharp is demonstrating several revolutionary products and technologies that help to make the LCD TV both a design statement and the leading technology for home entertainment.

Sharp is taking LCD technology to new levels and responding to consumer demand for stylish, unobtrusive home theater products. For the first time in the U.S., Sharp is showcasing..." />
	<meta name="title" content="Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2008/01/sharp-demonstrates-lcd-display-and-video-prowess-at-ces-with-enhanced-tv-features-and-groundbreaking-new-technologies.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=882', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/sharp-demonstrates-lcd-display-and-video-prowess-at-ces-with-enhanced-tv-features-and-groundbreaking-new-technologies.php">Sharp Demonstrates LCD Display and Video Prowess at CES with Enhanced TV Features and Groundbreaking New Technologies</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Sharp Demonstrates Enhanced LCD TV Features and Groundbreaking New Technologies</p>

<center><i>New 1080p Full HD AQUOS&reg; LCD TV Models with Web-based Capabilities and Enhanced Blu-ray Player Demonstrate Sharp's Display Technology Commitment</i></center><br />
<br />

<p>2008 International CES<br />
BOOTH #11024</p>

<p><B>LAS VEGAS--(BUSINESS WIRE)</B>--Sharp continues to change the LCD industry with one-of-a-kind innovations, increased performance and dazzling new designs. Reinforcing its commitment to the display industry at CES 2008, Sharp is demonstrating several revolutionary products and technologies that help to make the LCD TV both a design statement and the leading technology for home entertainment.</p>

<p>"Sharp continues to improve upon LCD technology with groundbreaking products that enhance our entertainment experience," said Bob Scaglione, senior vice president and group manager, Product and Marketing Group, Sharp Electronics Corporation. "LCD remains the best flat-panel technology, and we are demonstrating LCD technology innovations that are available to consumers now, and a sneak peak at the exciting next generation of LCD TVs."</p>

<p>Sharp is taking LCD technology to new levels and responding to consumer demand for stylish, unobtrusive home theater products. For the first time in the U.S., Sharp is showcasing a line of large screen next generation LCD TVs. These prototype LCD TVs bring together Sharp's unique LCD technologies nurtured over long years of experience in this field to achieve unprecedented levels of performance and design with a thickness of just 2cm (main display section; 2.9 cm at the thickest part) for a 52" screen size model, a bright room contrast ratio of 3,000:1 and a power consumption level that is half that of today's LCD TV's.</p>

<p>Literally changing the way consumers interact with their television, Sharp introduces a technology called AQUOS Net that brings Internet content and remote diagnostics to AQUOS LCD TVs at the touch of a button. To connect the AQUOS to an internet source in another room utilizing existing electrical wiring, Sharp debuts several PLC (Powerline Communication) adapters. These PLC adapters allow consumers to connect multiple devices, such as TVs, set-top boxes, gaming consoles, PCs, and routers, using Sharp's PLC adapters wherever there are power outlets in the home.</p>

<p>The newest lines of large-screen AQUOS HDTVs feature enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs. The company has unsurpassed LCD screen manufacturing at its new state-of-the-art 8th generation factory in Kameyama, Japan, where an entire production line focuses on the creation of large-screen units, ensuring that Sharp maintains their top position in the growing market for larger screen size LCD-TV's. This manufacturing superiority helps Sharp offer one of the most comprehensive flat-panel LCD TV selections, with more than 50 models in screen sizes ranging from 15- to 108-inches.</p>

<p>The new "Special Edition" SE94 AQUOS Series with AQUOS Net capability is headlining the company's wide breadth of TV offerings, which also includes a myriad of styles of AQUOS LCD-TV's, ranging from new 720p AQUOS models in 32- and 37-inch screen size classes, through 65-inch 1080p Full HD screen class sizes, as well as a series specifically intended for gamers. Furthering the company's digital home theater dominance, Sharp's CES 2008 booth will introduce its second-generation AQUOS Blu-ray Disc&trade; player, the BD-HP50U, with BD-ROM Profile 1.1 support. The BD-HP50U player includes several innovative features that turn the player into an all-in-one entertainment center. Through the HDMI cable connection, users can experience AQUOS Link, which enables integrated and seamless operation between the Blu-ray player and AQUOS LCD TV.</p>

<p>The booth is also packed with advanced digital technologies that complement the flat-panel TV revolution and exceptional new digital technologies. In addition to AQUOS and Blu-ray Disk players, highlights include new versions of the stylish docking systems made for iPod&reg; that allow the user to charge and play music directly from any iPod as well as an innovative 1-Bit, 2.1-channel audio home theater rack system. For detailed information on Sharp's new products, please see the individual product announcements and fact sheets.</p>

<p><br />
<B>AQUOS Net</B></p>

<p>AQUOS Net is a revolutionary service that will provide customized Web-based content as well as real-time customer support directly on the AQUOS television. AQUOS Net is a gateway, available through the Ethernet jack on the television that can connect to several unique services. Using this service, viewers can potentially configure "widgets" to check, for example, the local weather forecast, get stock quotes or even follow their favorite comic strip, while watching television, all directly from the TV's remote control. Through AQUOS Net, consumers will also have access to unparalleled customer support for their television, including the ability to have dedicated AQUOS Advantage advisors connect remotely to their TV to assist in adjusting the TV's settings and optimizing picture quality for the best viewing experience. This interactive tool, known as AQUOS Advantage Live, is easily accessible from the AQUOS Net home page. In addition, AQUOS Net provides consumers with instant access to important information about their television. Consumers can find FAQ's that explain HDMI&trade; connections, HDTV and other TV technology-related features, as well as view the user manual directly on the AQUOS LCD-TV. A "What's New" page will also be available, outlining exciting new products and news from Sharp.</p>

<p><br />
<B>AQUOS Widescreen 1080p HDTV Series (models LC-65SE94U, LC-52SE94U and LC-46SE94U)</B></p>

<p>Sharp's new elegantly styled "Special Edition" SE94 widescreen AQUOS LCD-TV series, available in 65-, 52- and 46-inch screen class sizes, combines a best-in-class picture quality with a new and distinguished "Cornerstone" design and "AQUOS Net" capability. The latest version of Sharp's proprietary Advanced Super View (ASV) panel provides an incredible Dynamic Contrast Ratio of 27,000:1, for exceptional detail in both light and dark scenes. Sharp's proprietary Fine Motion Advanced technology offers 120Hz frame rate conversion, doubling the traditional 60Hz driving speed, for even finer video performance. Sharp's new proprietary 10-bit ASV panel provides more accurate color and the industry-leading 4ms response time ensures flowing motion in fast-action scenes. These new models also incorporate Sharp's proprietary 5-wavelength backlight system that provides deeper, more vibrant reds and truer greens than previously possible. With viewing angles of 176 degrees, consumers can experience the superior picture quality from virtually anywhere in the room. Through the unit's Ethernet jack, users have access to AQUOS Net, a revolutionary service providing customized Web-based content as well as real-time customer support. The "Special Edition" AQUOS series features Sharp's proprietary 5-wavelength backlight system that provides an enhanced color spectrum, producing more vivid, deeper reds and greens than previously possible. Additionally, all the units include 3 HDMI&trade; (version 1.3 with x.v.Color and 24fps compatibility) inputs as well as dual HD component terminals, all of which are compatible with 1080p signals. The unit also houses an RS-232C port for custom installations and a dedicated PC input. These newly redesigned slim-line models are available in a stunning, new textured finish with eye-catching corner accents and detachable bottom speakers to match modern home décors, and include a detachable table stand. The LC-65SE94U and the LC-52SE94U will be available in January for Manufacturer's Suggested Retail Prices (MSRP) of $10,999.99 and $4,199.99 respectively. The LC-46SE94U will be available in February for an MSRP of $3,199.99.</p>

<p><br />
<B>AQUOS Widescreen 1080p HDTV LC-52D74U</B></p>

<p>This new 52-inch Full HD1080p HDTV AQUOS LCD TV, features an incredible 18,000:1 Dynamic Contrast Ratio, for deep blacks and crisp picture quality; enhanced Quick Shoot video circuitry for faster pixel response time of 4 ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. Through the unit's Ethernet jack, users have access to AQUOS Net, a revolutionary service providing customized Web-based content as well as real-time customer support. Using this service, viewers can create and configure "widgets" to check the local weather forecast or get stock quotes while watching their favorite television show. Through AQUOS Net, consumers will also have access to unparalleled customer support for their television, including the ability to have dedicated Sharp advisors connect remotely to their TV to assist in adjusting the TV's settings and optimizing picture quality for the best viewing experience. The LC-52D74U also includes Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, the model includes three HDMI&trade; (version 1.3 with x.v.Color and 24fps compatibility) inputs as well as two HD component terminals, all of which are compatible with 1080p signals from Blu-ray Disc&trade; and other new devices, in addition to RS-232C for custom installations and a PC input so the TV can be used as a PC monitor. This model features full HD 1080p (1920 x 1080) resolution for an unparalleled high-definition experience, and is available in a stunning new "Cornerstone" design with a slim-line casing and tight-fit stealth speakers. The LC-52D74U will be available in April for an MSRP of $3,599.99.</p>

<p><br />
<B>108-inch Screen Size Class High-Definition LCD</B></p>

<p>Sharp is showcasing its 108-inch LCD, a Full HD 1080p LCD, which measures 93.9-inches (W) by 52.9-inches (H) in size, and features Sharp's Advanced Super View LCD Panel manufactured at Sharp's Kameyama Plant No. 2, the first facility in the world to produce panels from eighth-generation glass substrates. The success of this development means that it is now possible to produce LCD TVs in all sizes from 13-inches to the super-large-size class.</p>

<p><br />
<B>AQUOS Blu-ray Disc&trade; Player BD-HP50U</B></p>

<p>The slim-profile AQUOS&reg; Blu-ray Disc&trade; player, model BD-HP50U, features full digital 1080p video output, that when combined with an AQUOS HDTV Liquid Crystal Television, allows consumers to view the latest high-definition films as well as enhanced definition titles in stunning high-definition quality. The BD-HP50U supports BD-ROM Profile 1.1, or BD Live, the latest version of the Blu-ray Disc format. BD-ROM Profile 1.1 provides secondary audio and video decoders for Picture in Picture (PIP) capability, as well as a USB terminal for a local storage device. Interactive features of the BD-HP50U allow consumers to view additional content available on Blu-ray titles -- including movie trailers, special subtitles, and director commentary -- without stopping the movie. Reflecting Sharp's strategy of creating great products from great devices, the key components of the AQUOS Blu-ray player were all built by Sharp, including the Blu-ray laser, the pick-up beam, and the driver unit. Through the HDMI&trade; 1.3 input, users can experience AQUOS LINK&trade;, which enables integrated and seamless operation between the Blu-ray disc player and AQUOS LCD TV. With the AQUOS LINK feature, HDMI-connected products can be controlled through the AQUOS screen with a single TV remote. The unit also outputs 1920 x 1080 24 fps (frames per second) high-definition video and features a Quick Start feature that allows the user to begin enjoying gorgeous Blu-ray video with a touch of a button in less than 10 seconds*. The player also offers multi-channel audio output via HDMI connection by decoding Dolby&reg; TrueHD and DTS HD. It also decodes Dolby&reg; Digital Plus, providing optimum surround sound to an appropriately equipped receiver. The BD-HP50U provides an RS-232C port for advanced control and is compatible with a wide variety of formats including BD-ROM/RE/R, DVD Video, DVD-RW/R, DVD+RW/R, and Audio CDs. The BD-HP50U will be available in Spring 2008 for an MSRP of $799.99.</p>

<p><br />
<B>AQUOS HDTV D64U Series (models LC-37D64U and LC-32D64U)</B></p>

<p>Sharp has added 32- and 37-inch screen size classes to its "slim-line" D64U Series of AQUOS Full HD 1080p HDTVs, which already includes 42-, 46-, 52- and 65-inch screen size classes. The new 37- and 32-inch screen class sizes feature the slim-line design with 25% thinner depth than previous models and a thin, lightweight bezel that provides a significantly smaller footprint, establishing a new design standard for LCD TVs. With this space-saving design, consumers now have unprecedented placement flexibility, allowing a larger screen size to fit in a smaller area. It has full 1080p (1920 x 1080) resolution and the latest generation of Sharp's proprietary Advanced Super View LCD panel for an unparalleled high-definition viewing experience. The ASV panel enables an impressive Dynamic Contrast Ratio of 10,000:1 and an unparalleled response time of 6ms for stunning picture quality even on fast-moving action scenes. The D64U line also features a 4-wavelength backlight system, providing deeper, more vibrant reds than previously possible, as well as impressive 176 degree viewing angles, enabling the consumer to view the TVs from virtually anywhere in the room. Rounding out the enhanced list of specs is the D64U's array of inputs, including two HDMI inputs (version 1.3) and two HD component video inputs, all 1080p compatible, an RS-232C input for control, and a PC input so the TV can be used as a PC monitor, saving space and reducing clutter. The series is also Energy Star-compliant, with very low power consumption. The LC-37D64U will be available in February for an MSRP of $1,599.99 and the LC-32D64U will be available in this month for an MSRP of $1,299.99.</p>

<p><br />
<B>AQUOS Widescreen 720p HDTV Series (models LC-37D44U and LC-32D44U)</B></p>

<p>The new widescreen 37- and 32-inch HDTV AQUOS D44 Liquid Crystal Televisions further bolster Sharp's unmatched selection of sophisticated designs and its superior-performing LCD TVs. Sharp's proprietary Advanced Super View LCD panel enables a Dynamic contrast ratio of 7500:1, enhanced Quick Shoot video circuitry for fast pixel response time (6ms) and wide viewing angles (176 degrees), so users can view the television from almost anywhere in the room. The newly-designed series features a subtle silver accent on the front of the panel with a high-gloss black finish that enhances the décor of any family room, living room or den, and tight-fit stealth speakers for placement flexibility. With 1366 x 768 resolution, a true 16:9 aspect ratio, and built-in ATSC/QAM/NTSC tuners, consumers can enjoy the latest HDTV programming, and the addition of a PC input makes the panel multifunctional for any room. The LC-37D44U will be available in February for an MSRP of $1,299.99 and the LC-32D44U will be available in January for an MSRP of $999.99.</p>

<p><br />
<B>AQUOS HDTV Gaming LCD TV (models LC-32GP3U-R, LC-32GP3U-W, LC-32GP3U-B)</B></p>

<p>Sharp has introduced a new Full HD 1080p AQUOS LCD gaming model series, the LC-32GP3U, crafted specifically for video game enthusiasts, available in a 32-inch screen class size. The 32-inch set has a striking new design, featuring a thin, "slim-line" body that provides a significantly smaller footprint than previous models, as well as a unique swivel stand for ultimate viewing and gaming flexibility. To complement any living room or game room, this AQUOS model is available in three glossy-finish colors - black, white and a unique wine dark red. In addition to the new style, the GP3U models also offers the same special features that enhance the game-playing experience as the previous gaming models, including a "game mode" which optimizes the picture quality for game-playing, and a custom-designed remote control that allows the user to quickly "jump" into the game mode, and access the side-placed terminals for easy connections to video games. The game mode provides a "Vyper Drive" engine, which reduces lag time between the game console and the TV to be virtually imperceptible. The panel boasts an incredible 10,000:1 Dynamic contrast ratio, for deep blacks and crisp picture quality; enhanced Fine Motion video circuitry for faster pixel response time of 6 ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. It also includes Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. With three HDMI&trade; v1.3 inputs (with one on the side) as well as two HD component terminals (one on the side), the LC-32GP3U models are compatible with 1080p signals from the latest video game devices, as well as supports 1920 x 1080 24 fps (frames per second) high definition video, the highest resolution possible via an HDMI&trade; connection. The LC-32GP3U-R, LC-32GP3U-W and LC-32GP3U-B are available now for an MSRP of $1,599.99.</p>

<p><br />
<B>1-Bit 2.1 Channel Audio Home Theater Rack System</B></p>

<p>Satisfying both video and audio enthusiasts, Sharp's new 2.1 Channel 1-Bit&trade; AQUOS&reg; audio home theater rack system delivers hi-fidelity sound while providing housing for a large screen AQUOS TV (up to 52-inches in size). The new AN-ACS1U rack system joins the Sharp suite of AQUOS brand products, including the extensive line of LCD TVs and, most recently, Blu-ray Disc players. With a high-gloss piano-black finish to match the style of AQUOS TVs and room-filling sound from a built-in 1-Bit amplifier, the new AN-ACS1U packs a powerful punch (150W power) for superior sound and offers limitless design and décor options that complement any room of the home. The rack system also incorporates technology from Audistry&trade; by Dolby&trade; for an enhanced personal listening experience. For seamless connection between the parts, the rack system provides 2 HDMI-CEC (consumer electronics control) inputs. Sharp's built-in AQUOS LINK&trade; feature allows users to seamlessly control HDMI-connected products via an AQUOS screen using a single remote control. The AN-ACS1U rack system incorporates a sophisticated speaker system that provides a wide listening area for a fuller sound throughout the home. The AN-ACS1U will be available in April for an MSRP of $1,699.99.</p>

<p><br />
<B>Music System for iPod&reg;</B></p>

<p>The newly designed Sharp music systems for iPod feature a sleek, triangular shape that can be carried from room to room and allows for placement flexibility, fitting nicely on small shelves and in tight corners. These docking systems have an iPod terminal that allows the user to charge and play music directly from any iPod. The portable lightweight units feature full range bass reflex speakers with HDSS (high-definition sound standard) sound technology. The new stereo systems will be available in white (DK-AP2) and black (DK-AP2BK) and come with a thin card-style remote control that allows the user to control the iPod dock system functions in addition to the connected iPod. The units can support MP3 music players as well as stream video files from iPod video via the video out jack to the television. The systems run on batteries so music lovers can listen to tunes anywhere. The DK-AP2 and DK-AP2BK are available now for an MSRP of $129.99.</p>

<p>For more information on Sharp's full line of products, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07430, or call 800-BE-SHARP. For online product information, visit Sharp's Web site at sharpusa.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&reg; Microwave Drawers&reg;, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com.</p>

<p>* Quick Start time may vary depending on movie content, type of video connection, and type of monitor being used</p>

<p>AQUOS is a registered trademark of Sharp Corporation</p>

<p>HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks of HDMI Licensing, LLC.</p>

<p>iPod is a trademark of Apple.</p>

<p>All other trademarks are the property of their respective owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2008  7:44 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(882)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 882)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/sharp-demonstrates-lcd-display-and-video-prowess-at-ces-with-enhanced-tv-features-and-groundbreaking-new-technologies.php" type="text/javascript" charset="utf-8"></script>
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
