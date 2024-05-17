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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 687 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 687
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Sanyo Introduces World\'s Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php&amp;title=Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder">
		<span style="display:none">SANYO, a world leading digital camera manufacturer, introduces the Xacti HD1000, the world's smallest and lightest full HD digital camcorder*1. The sleek and simple-to-use device records video in Full HD (1920 x 1080 pixels) and also takes 4-megapixel digital still images. The HD1000 utilizes the advanced MPEG-4 AVC/H.246 video format and features a 10x optical HD lens and a large 2.7-inch widescreen display.

The SANYO Xacti HD1000 will be available in the U.S.A. in September, 2007, and has an MSRP of $799.99*2.

The new SANYO Xacti HD1000 weighs only 9.5 ounces and has a total volume of only 16.6 cubic inches, making it the world's smallest and lightest Full HD recording (1920 horizontal and 1080 vertical pixels) digital camcorder*1. It incorporates advanced MPEG-4 AVC/H.264 video compression, enabling up to...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 687";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sanyo Introduces World\'s Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sanyo Introduces World\'s Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" />
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
	<title>HDTV Magazine - Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder</title>
	<meta name="keywords" content="high definition, docking station, still images, smallest lightest, memory card, sanyo, video, digital, full, xacti, image, high, still, images, new, definition, recording, station, function, camera, camcorder, display, shooting, lens, card" />
	<meta name="description" content="SANYO, a world leading digital camera manufacturer, introduces the Xacti HD1000, the world's smallest and lightest full HD digital camcorder*1. The sleek and simple-to-use device records video in Full HD (1920 x 1080 pixels) and also takes 4-megapixel digital still images. The HD1000 utilizes the advanced MPEG-4 AVC/H.246 video format and features a 10x optical HD lens and a large 2.7-inch widescreen display.

The SANYO Xacti HD1000 will be available in the U.S.A. in September, 2007, and has an MSRP of $799.99*2.

The new SANYO Xacti HD1000 weighs only 9.5 ounces and has a total volume of only 16.6 cubic inches, making it the world's smallest and lightest Full HD recording (1920 horizontal and 1080 vertical pixels) digital camcorder*1. It incorporates advanced MPEG-4 AVC/H.264 video compression, enabling up to..." />
	<meta name="title" content="Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=687', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php">Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 30, 2007</b>
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
				<p class="prtitle">Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder</p>

<center><i>Xacti HD1000 Features Advanced MPEG-4 AVC/H.264 Video Format, Improved Ergonomics, and Convenient New 'Xacti Library' File Saving & TV Playback Solution</i></center><br />
<br />

<p><img src="/images/products/sanyo-xacti-hd1000.jpg" alt="Sanyo Xacti HD1000" align="left" /><B>CHATSWORTH, Calif., Aug. 30 /PRNewswire-FirstCall/</B> -- SANYO, a world leading digital camera manufacturer, introduces the Xacti HD1000, the world's smallest and lightest full HD digital camcorder*1. The sleek and simple-to-use device records video in Full HD (1920 x 1080 pixels) and also takes 4-megapixel digital still images. The HD1000 utilizes the advanced MPEG-4 AVC/H.246 video format and features a 10x optical HD lens and a large 2.7-inch widescreen display.</p>

<p>The SANYO Xacti HD1000 will be available in the U.S.A. in September, 2007, and has an MSRP of $799.99*2.</p>

<p>"The SANYO Xacti line has gained a reputation for excellence and innovation," said John Lamb, SANYO's Senior Marketing Manager for the Xacti camcorder series. "The new Xacti HD1000 continues our pioneering approach by combining full 1080i HD video recorded in the advanced AVC/H.264 standard with a massive 2.7-inch widescreen display and a simple hardware and software interface for file saving and TV playback."</p>

<p>SMALLEST, LIGHTEST, FULL HD CAMCORDER</p>

<p>The new SANYO Xacti HD1000 weighs only 9.5 ounces and has a total volume of only 16.6 cubic inches, making it the world's smallest and lightest Full HD recording (1920 horizontal and 1080 vertical pixels) digital camcorder*1. It incorporates advanced MPEG-4 AVC/H.264 video compression, enabling up to approximately 85 minutes of Full HD (1920 x 1080) video recording or up to five hours, 14 minutes of TV-quality (640 x 480) video recording onto an 8GB SDHC Memory Card (memory card sold separately).</p>

<p>SANYO developed a new high-speed image processing engine to handle the high capacity demands of Full HD data. This engine utilizes SANYO's proprietary codec, enabling Full HD compatibility with the AVC/H.264 format, and makes it possible to do with one simple yet powerful chip the complex processing which previously required two separate chips. The required high compression ratio is gracefully achieved due to optimization of the image process algorithm actuator, while consuming a mere 4.2 watts of power.</p>

<p>NEW ERGONOMIC DESIGN</p>

<p>Designed for easy, one-handed operation with all key conveniently functions thumb-operable, the HD1000 is comfortable to hold, even for extended periods. The device is the first to incorporate ergonomic results based on collaborative research between SANYO and Japan's Chiba University in regard to optimizing the lens-to-grip angle to minimize strain on the muscle groups used while holding and recording. Testing focused on muscle responses in six places on the arm, as well as surveys asking for individual assessments and evaluations. As a result of these tests and responses, it was discovered that a lens-to-grip angle of 105 degree, the angle subsequently used on the HD1000, was optimal.</p>

<p>As part of its stylish and thoughtful new design, the HD1000 also adds a distinctly useful feature frequently requested by users of previous Xacti camcorders -- the ability to have the lens level with the ground when the device is used with a tripod.</p>

<p>FULL 1080i HD SENSOR</p>

<p>Incorporating the latest high-definition CMOS sensor, the SANYO Xacti HD1000 camcorder captures full 1080i high-definition video (1920x1080) at 60 frames-per-second. Designed to record the rich and vibrant colors of real life, the HD1000 also captures the subtle tones to provide a natural-looking result. The HD1000's CMOS sensor provides the quick responsiveness needed to capture fast moving subjects and SANYO's noise reduction technology helps obtain the cleanest signal from each pixel.</p>

<p>NEW "XACTI LIBRARY" FUNCTION FOR EASY FILE SAVING AND PLAYBACK*3</p>

<p>The large data size of Full HD movies, along with demanding file saving and playback processing usually requires special hardware and/or burning video to a disc. The HD1000's new "Xacti Library" feature makes it exceptionally easy to save video and image files by simply connecting the included USB cable*4 from the docking station to an external hard disk drive*5 (hard drive not included). After connecting the docking station to an external hard drive and to a television (via an HDMI cable), the user places the camera into the docking station and -- using the menu that appears on the television screen -- can search through the thumbnails and choose either to play the file or save it to the external hard disk.</p>

<p>10X OPTICAL HD LENS</p>

<p>At the front of the HD1000 is a commanding 10x all-glass HD lens. The HD1000's fast f/1.8-2.5 lens is capable of allowing almost four times more light through to assist in lower light venues. Consisting of eight groups and eleven total lenses with a built-in neutral density filter, the HD1000's lens provides a spectacular field-of-view with a 38-380 mm range (35 mm equivalent). Combined with the 10x digital zoom, the HD1000 provides up to 100x total zooming capability.</p>

<p>LARGE 2.7 INCH WIDESCREEN DISPLAY</p>

<p>The HD1000 features a large 2.7 inch widescreen Liquid Crystal Display (LCD). The display flips out from the camera and rotates up to 285 degrees on axis, allowing you to take great video or still images even from difficult-to-view positions, which is especially useful when shooting in large crowds or in small rooms.</p>

<p>FOUR MEGAPIXEL DIGITAL IMAGES</p>

<p>The Xacti HD1000 enables simultaneous shooting of 4-megapixel still images and HD movie clips, with a simple press of the shutter button during the recording of a video clip. Users need never miss another precious photo opportunity. (Depending on the mode used to take still images, simultaneous video clip shooting may be interrupted. While shooting video clips, using the digital image stabilizer may change the angle of view for still images.)</p>

<p>HDMI HIGH-DEFINITION OUTPUT</p>

<p>It's easy to view and share high-definition video on your HD television with the HD1000. Using the HDMI (High-Definition Multimedia Interface) terminal built into the base station, just one cable connects your camcorder to your TV for a totally digital output. HDMI carries both the video and audio signals in digital form for the highest quality playback.</p>

<p>RECORDS TO CONVENIENT SD/SDHC MEMORY CARD</p>

<p>The SANYO Xacti HD1000 records high-definition and photos directly to a standard SD or SDHC Memory Card. In fact, the HD1000 is capable of recording up to 85 minutes of 1080i high-definition video on a single 8GB card (sold separately). The SD Memory Card's compact size and weight makes it ideal for the diminutive HD1000. The minimal power requirements of the SD card also contribute to longer recording and playback times. When connected to the computer via the USB cable, the HD1000 acts as a standard card reader. Transferring images and videos to your computer has never been easier.</p>

<p>IMAGE STABILIZATION</p>

<p>High-definition can't hide shaky or erratic camera movement. That's why SANYO's HD1000 comes with a sophisticated image stabilizer for both stills and video. This handy feature operates in both wide-angle and telephoto modes, giving every shot a solid, professional-looking feel. For video-compatible anti-shake correction, SANYO further developed the digital stabilizer based on previous proprietary image stabilizer technology, and advanced the technology to be more accurate in its correction, increasing the image area detection function. Also, SANYO newly developed its proprietary "Superposition function" for higher still image quality. This function allows for clear pictures of the subject even when moving or rotation occurs.</p>

<p>AUTOMATIC "FACE CHASER" FUNCTION FOR STILL IMAGES</p>

<p>The HD1000 includes a new "Face Chaser" function that automatically detects and isolates faces to assist the camera's exposure and auto-focus. The HD1000 is capable of detecting up to 12 independent faces at a time.</p>

<p>MANUAL CONTROLS</p>

<p>The HD1000 features versatile manual controls for advanced shooting. The following settings can be manually adjusted according to the shooting situation: Manual focus adjustment (16 settings); aperture adjustment (6 stops); exposure compensation (1.8 EV, 0.3 EV steps); shutter speed (13 settings); and image-quality adjustment (for sharpness and color saturation).</p>

<p> ADDITIONAL HD1000 FEATURES:</p>

<p> -- Random Access: Each video is recorded as an individual MPEG-4 and each<br />
 still as a JPEG so you can have true random access allowing you to<br />
 review a specific image or video quickly and easily, without waiting<br />
 for tape rewinding or fast forwarding.</p>

<p> -- Easy Camera to PC Connection: One of the more frustrating aspects of<br />
 working with any digital media camera is juggling all the wires and<br />
 connections necessary when you want to use it with external components<br />
 for viewing or to download files. SANYO's HD1000 streamlines the whole<br />
 process with an innovative docking station that provides an instant<br />
 HDMI, component, composite or S-video connection to a TV and a USB<br />
 connection for a PC. The HD1000 even recharges its internal battery<br />
 when nested in the docking station.</p>

<p> -- Super fast Startup: With its tapeless design, the HD1000 eliminates the<br />
 need to queue up a tape deck or get a DVD or hard drive spinning,<br />
 allowing the HD1000 to begin shooting in as little as two seconds! When<br />
 the HD1000 is powered on, closing the LCD display puts the HD1000 in<br />
 standby mode. Simply open the display and the HD1000 automatically<br />
 powers up and can begin immediately recording in as little as two<br />
 seconds.</p>

<p> -- Equipped with 'SIMPLE' mode so even beginners can create high quality,<br />
 beautiful high definition movies</p>

<p> -- Defaults for automatic settings can be accessed quickly with the<br />
 "Full-Auto" button</p>

<p> -- Adopts newly developed 2.7 inch 230,000 pixel, widescreen TFT-LCD<br />
 monitor designed for viewing HD footage.</p>

<p> -- Bundled with "Nero 7 Essentials" for playback and "Ulead DVD<br />
 MovieFactory 5 SE" for editing.</p>

<p> -- Newly developed and long-lasting 1900 mAh Lithium-ion battery (DB-L50)</p>

<p> -- Allows use of external accessories such as an external strobe, video<br />
 light, microphone, etc.</p>

<p> -- Optional adapter lenses for increased telephoto or wide-angle<br />
 conversion</p>

<p> -- Six selectable video resolution modes and eight selectable still photo<br />
 resolutions</p>

<p> -- Continuous Still Image Shooting function - 7 frames per second*6</p>

<p> -- Able to take still pictures while in the middle of Full HD movie<br />
 recording*7</p>

<p> -- 9-image quick display function</p>

<p> -- In-camera editing</p>

<p> -- 48 kHz, 16-bit, 2-channel sound</p>

<p> -- Headphone Jack</p>

<p><br />
 About SANYO</p>

<p>SANYO Electric Co., Ltd. is a multi-billion-dollar global leader in providing solutions for the environment, energy and for lifestyle applications based on its Brand Vision 'Think GAIA'. SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.), based in Chatsworth, California, markets mobile phones, digital projectors, digital still cameras, digital media camcorders, home appliances, security video equipment, audio systems, portable and mobile electronics and HD televisions.</p>

<p>For more information and additional specifications, please visit http://www.sanyodigital.com/. For downloadable hi-res product images, go to http://www.sanyodigital.com/ and click on "Dealer Images".</p>

<p>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</p>

<p> *1 For consumer-use Full HD digital movie cameras, size is by volume (as<br />
 of August 30, 2007)<br />
 *2 Manufacturer's Suggested Retail Price. Pricing subject to change at any<br />
 time. Actual prices are determined by individual dealers and may<br />
 vary.<br />
 *3 Via docking station<br />
 *4 Uses a special cable (included), HDMI cable sold separately<br />
 *5 SANYO does not guarantee that the docking station will be compatible<br />
 with all external drives.<br />
 *6 Recorded as 4.0 Mega-pixels<br />
 *7 Recorded as 2.0 Mega-pixels</p>

<p>Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20070830/LATH034<br />
AP Archive: http://photoarchive.ap.org/<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: SANYO Fisher Company</p>

<p>CONTACT: Michael R. Harris of Harris Public Relations, +1-714-966-0258,<br />
hpr1@earthlink.net, for SANYO Fisher Company</p>

<p>Web site: http://www.sanyo.com/</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 30, 2007  7:28 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(687)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 687)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php" type="text/javascript" charset="utf-8"></script>
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
