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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1640 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1640
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/01/sharp-demonstrates-continued-leadership-in-lcd-innovation-and-introduces-cuttingedge-video-and-audio-technologies-at-ces-2009.php&amp;title=Sharp&amp;reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009">
		<span style="display:none">At CES 2009, Sharp is showcasing several revolutionary products and technologies that demonstrate the company's drive to innovate and improve the LCD industry with increased performance, the widest array of screen size offerings, improvements in energy efficiency and dazzling new designs. While maintaining its commitment to the LCD industry, Sharp is also incorporating its leading display technologies into the video industry with new Blu-ray introductions and an array of...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1640";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009" />
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
	<title>HDTV Magazine - Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009</title>
	<meta name="keywords" content="blu ray, home theater, aquos lcd, high definition, ray player, sharp, new, aquos, lcd, sound, home, blu, ray, audio, models, theater, high, player, video, digital, available, diagonal, hdmi, screen, quality" />
	<meta name="description" content="At CES 2009, Sharp is showcasing several revolutionary products and technologies that demonstrate the company's drive to innovate and improve the LCD industry with increased performance, the widest array of screen size offerings, improvements in energy efficiency and dazzling new designs. While maintaining its commitment to the LCD industry, Sharp is also incorporating its leading display technologies into the video industry with new Blu-ray introductions and an array of..." />
	<meta name="title" content="Sharp&amp;reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/01/sharp-demonstrates-continued-leadership-in-lcd-innovation-and-introduces-cuttingedge-video-and-audio-technologies-at-ces-2009.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1640', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/sharp-demonstrates-continued-leadership-in-lcd-innovation-and-introduces-cuttingedge-video-and-audio-technologies-at-ces-2009.php">Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
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
				<p class="prtitle">Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009</p>

<center><i>Next-generation AQUOS&reg; LCD TV with built-in Blu-ray&trade; player, new 120 Hz offerings, new Blu-ray products highlight Sharp's CES lineup</i></center><br />
<br />

<p><br />
<B>LAS VEGAS--(BUSINESS WIRE)</B>--At CES 2009, Sharp is showcasing several revolutionary products and technologies that demonstrate the company's drive to innovate and improve the LCD industry with increased performance, the widest array of screen size offerings, improvements in energy efficiency and dazzling new designs. While maintaining its commitment to the LCD industry, Sharp is also incorporating its leading display technologies into the video industry with new Blu-ray introductions and an array of powerful and stylish new audio products.</p>

<p>"As a leading presence in the LCD industry, Sharp continues to dedicate considerable resources and steadfast commitment to improving and furthering LCD technology, giving consumers one-of-a-kind product enhancements that improve their lifestyles," said Bob Scaglione, senior vice president and group manager, Product and Marketing Group, Sharp Electronics Corporation. "As in years past, Sharp continues to show why we are an LCD leader. But we're also introducing several new products that complete the home theater experience, to give consumers both the highest image quality available and powerful cinema-like sound."</p>

<p>Demonstrating the company's complete digital home theater prowess, Sharp is introducing the world's first LCD TV with a built-in Blu-ray player, for a convenient all-in-one home theater solution. Sharp is also expanding its 120 Hz offerings and debuting a brand new screen size class -- an 82-inch class LCD display that expands the company's extensive range of flat-panel Liquid Crystal Display (LCD) screen sizes. The booth is packed with an array of styles and sizes of AQUOS LCD TVs, with full-HD 1080p models in seven screen size classes as well as the availability of 120Hz in seven new AQUOS TVs. The newest lines of large-screen AQUOS HDTVs feature enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs.</p>

<p>In addition to two new advanced Blu-ray players featuring BD-ROM Profile 2.0 for a multitude of interactive features through an Internet connection, Sharp is introducing several audio products that incorporate superior sound technology. A new audio home theater system with built-in Blu-ray as well as the company's first Sound Bar products, offer consumers improved sound to match the high-quality images on an AQUOS LCD TV and low-profile designs to complement modern home decors. To further complement the flat-panel TV revolution and exceptional new digital technologies, other highlights include new versions of the stylish docking systems made for iPod&reg; that allow the user to charge and play music directly from any iPod as well as a new affordable 1080p home theater projector. For detailed information on Sharp's new products, please see the individual product announcements.</p>

<p><br />
<B>AQUOS HDTV BD Series (models LC-52BD80U, LC-46BD80U, LC-42BD80U, LC-37BD60U and LC-32BD60U)</B></p>

<p>Sharp introduces the world's first high-definition AQUOS LCD TV Series with built-in Blu-ray DiscTM player, setting a new standard for home entertainment. The new 1080p AQUOS LCD / Blu-ray player combination product offers the convenience of an all-in-one home theater solution with a new Superlucent Advanced Super View (ASV) panel for a dramatically bright and crisp picture. The Superlucent ASV panel applies an ultra-smooth finish that minimizes gloss while intensifying panel brightness and contrast. A new AQUOS Pure Mode enables convenient optimized viewing of Blu-ray titles. Available in 52- (52-1/32" diagonal), 46- (45-63/64" diagonal), 42- (42-1/64" diagonal), 37- (37" diagonal) and 32-inch (31-35/64" diagonal) screen size classes, these models offer a slim frame with a new elegant "AQUOS Blue" design that includes a subtle blue accent at the bottom of the frame. The units also include a swivel stand for viewing convenience. The LC-52BD80U, LC-46BD80U and LC-42BD80U include Fine Motion Enhanced technology for 120 Hz Frame Rate Conversion, a 10-bit panel for Deep Color compatibility and a special "dejudder" feature that reduces background motion noise. All five models offer fast pixel response time and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. Additionally, all units in this series include a multi-slot for BD, DVD and CD content. A single step operation feature turns on the TV and activates play when a BD disc is inserted. The series offers extensive HDMI inputs, with four on the LC-52BD80U, LC-46BD80U and LC-42BD80U and three on the LC-37BD60U and LC-32BD60U. The LC-52BD80U and LC-46BD80U will be available in February; pricing is TBD. The LC-42BD80U, LC-37BD60U and LC-32BD60U will available in January; pricing is TBD.</p>

<p><br />
<B>AQUOS HDTV E Series (models LC-65E77U, LC-52E77U, LC-46E77U, LC-40E77U, LC-40E67U, LC-32E67U)</B></p>

<p>The new widescreen series of Full HD1080p HDTV AQUOS LCD TVs, including the E77U models and the E67U models, rounds out a lineup of new Full HD 1080p LCD TVs that bring enhanced picture quality to the forefront. The E Series features the E77U models in 65- (64 33/64" diagonal), 52- (52 1/32" diagonal), 46- (45 63/64" diagonal) and 40-inch (TBD diagonal) screen class sizes (LC-65E77U, LC-52E77U, LC-46E77U and LC-40E77U respectively), and the E67U models in 40- (TBD diagonal) and 32-inch (31 35/64" diagonal) screen class sizes (LC-40E67U and LC-32E67U respectively). The E77 models feature Sharp's new 10-bit Superlucent Advanced Super View (ASV) panel for a dramatically bright and crisp picture with reduced haze and reflectivity. These models also include Fine Motion Enhanced technology for 120 Hz Frame Rate Conversion and a special dejudder feature that helps eliminate background motion artifacts on Blu-ray movies, fast pixel response time of 4 ms, and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. Additionally, these units include five HDMI inputs and two HD component terminals, all of which are compatible with 1080p signals from Blu-ray and other new devices, in addition to RS-232C for custom installations and a dedicated PC input. The new 1080p E67 models incorporate Sharp's Superlucent ASV technology, providing the most detailed Full HD 1080p picture possible. Additionally, these models come fully equipped with four HDMI inputs with 24p input capability and two component video inputs, all 1080p compatible. The LC-32E67U adds Sharp's Vyper Drive technology for video game enthusiasts. This feature reduces the lag time between the game console input and the TV display to imperceptible levels. The full E series is compliant with the most recent Energy Star&reg; standards and also offers energy saving through Sharp's OPC function, to automatically adjust the unit's brightness based on the lighting of the room. The models offer a new design, with a black cabinet that gives way to a soft gold hue that accents the bottom of the E77 series frame, and a subtle copper hue at the bottom of the E67 cabinet. The LC-65E77U will be available in June for an MSRP of $4,499.99. The LC-52E77U, LC-46E77U and LC-32E67U will be available in February for MSRPs of $2,399.99, $2,099.99 and $899.99 respectively. The LC-40E77U and LC-40E67U will be available in March for MSRPs of $1,399.99 and $1,199.99.</p>

<p><br />
<B>82-inch Screen Size Class High-Definition LCD</B></p>

<p>Sharp announces the world's first 82-inch class Full-HD 1080p LCD Monitor, expanding the company's extensive range of flat-panel Liquid Crystal Display (LCD) screen sizes. The LC-82MX1U was designed for the true home theater enthusiast and features the next generation of Sharp's proprietary 10-bit Advanced Super View (ASV) / Black TFT Panel, which provides deep blacks and crisp picture quality. This new size category was developed at Sharp's 8th generation Kameyama Plant No. 2, to fill the space between the 65- (64-17/32" diagonal) and 108-inch (107.5" diagonal) screen classes. The LC-82MX1U features Fine Motion Enhanced technology for 120Hz Frame Rate Conversion and a fast pixel response time of 4ms, ensuring smooth, flowing motion in fast-action scenes and an overall immersive experience. With Sharp's 10-bit ASV / Black TFT Panel, the display offers Spectral Contrast Engine XD (Extreme Dark), providing high Dynamic Contrast for deep blacks and crisp picture quality. This large-screen model also includes an array of connection options, including HDMI&trade; and DVI-I connectors, for greater connectivity with a wide variety of equipment and devices.</p>

<p><br />
<B>AQUOS Blu-ray Disc&trade; Player BD-HP22U</B></p>

<p>The slim-profile AQUOS&reg; Blu-ray Disc&trade; player, model BD-HP22U, provides Full HD 1080p digital output that when paired with an AQUOS LCD TV allows consumers to appreciate the superior quality of high-definition audio and video content. The Blu-ray player supports BD-ROM Profile 2.0, also known as BD-Live, which provides a multitude of interactive features through an Internet connection. Users can download and stream bonus content such as additional scenes, shorts, trailers and multi-player interactive games, providing the consumer full access to content otherwise unattainable. In addition to the BD-Live upgrade, the BD-HP22U incorporates several added features that help to improve the home theater experience, including advanced audio decoding, a high quality picture with AQUOS Pure Mode, and lower power consumption in both Power On mode and in Standby mode (19W power consumption). With AQUOS Pure mode, the new Blu-ray Disc players connect to a Sharp AQUOS LCD TV with AQUOS Link function via an HDMI&reg; cable to deliver video content with unparalleled clear contrast and details. Sharp engineers designed this mode so that the BD player recognizes the connection to the AQUOS TV, in turn producing the best picture possible. The BD-HP22U enables superior video quality with state-of-the-art HDMI 1.3 digital output with x.v. color, and 1920 x 1080 video at 24 frames per second output, matching the playback of the original film. For added convenience, the BD players include Sharp's proprietary Quick Start feature for quick disc loading. The player outputs the most advanced lossless surround-sound formats including Dolby&reg; TrueHD and DTS HD Master Audio via HDMI digital output. The player also decodes Dolby Digital Plus, which provides optimum surround sound for appropriately equipped receivers. The BD-HP22U is compatible with a wide variety of formats including BD-ROM/RE/R, DVD Video, DVD-RW/R, DVD+RW/R, and Audio CDs. It also includes 2GB of USB memory and includes a jpeg viewer. The BD-HP22U will be available in May for an MSRP of $299.99.</p>

<p><br />
<B>AQUOS BD Audio Home Theater Systems (models BD-MPC40 and BC-MPC30)</B></p>

<p>Sharp introduces a compact full audio/video home theater system, designed to satisfy both audio and video enthusiasts. Joining the Sharp suite of AQUOS-brand products, including the extensive line of LCD TVs and, most recently, Blu-ray players, the new AQUOS BD-MPC40 and BD-MPC30 include the main unit, housing a Blu-ray player and amplifier, as well as five speakers and a subwoofer. The systems pack a powerful punch (720W power) and provide room-filling 5.1-channel surround sound. A high-gloss piano-black finish on the main unit matches the styling of AQUOS TVs, accompanied by black wooden cabinet speakers with the BD-MPC40, and black synthetic finish speakers with the BD-MPC30, allowing for limitless design and décor options that complement any room of the home. The home theater systems incorporate Dolby TrueHD and DTS-HD Master Audio surround sound capabilities for a true theater sound experience. The main unit includes a Blu-ray player for full high-definition 1080p/24 Hz output, creating a true cinematic experience in the comfort of the living room. The player supports BD-ROM Profile 2.0, also know as BD-Live, which provides a multitude of interactive features through an Ethernet jack-enabled Internet connection. Users can download and stream bonus content such as additional scenes, shorts, trailers and multi-player interactive games, providing the consumer full access to content otherwise unattainable. Additionally, the Blu-ray player features AQUOS Pure Mode, which automatically senses the aspect ratio of the Blu-ray title being played and optimizes the TV's view mode for the best possible HD picture possible. The systems come with an HDMI&reg; 1.3 digital output, allowing Blu-ray discs to be viewed in complete digital 1080p/24Hz high-definition. Providing outstanding versatility, the Blu-ray player is compatible with a wide variety of formats including BD-ROM/RE/R, DVD Video, DVD-RW/R, DVD+RW/R, and Audio CDs. It is also compatible with standard DVDs and is capable of upscaling them via HDMI to 1080p, improving the picture performance of an existing DVD library. The BD-MPC40 and BD-MPC30 will be available this spring for an MSRP of $799.99.</p>

<p><br />
<B>2.1 Sound Bar Home Theater Systems (models HT-SB300 and HT-SB200)</B></p>

<p>Sharp's new 2.1-channel sound bars, models HT-SB300 and HT-SB200, offer enhanced audio technology and easy setup for a dynamic home theater experience. The models feature a slim profile design, enclosing the main left and right speaker drivers as well as the sub woofer in one thin, wall-mountable sound bar. The bar can also be placed on a TV stand (32-inch LCD and up), with included mounting plates to straddle the base of an LCD TV. The 34-watt sound bar combines advanced HDSS (high-definition sound standard) sound technology and SRS WOW HD Sound for a more natural listening experience and a deeper, more natural bass response. Both models feature adjustable Bass, Treble and Sub level options for a unique surround sound effect without rear channel speakers. The HT-SB300 adds digital audio decoding with DTS, Dolby&reg; Digital and Dolby&reg; Pro logic II decoder, as well as Dolby&reg; Virtual Speaker to effectively simulate 5.1 channel surround sound. For flexibility and simple setup, both sound bars feature dual audio inputs, right and left RCA jacks and a 3.5mm sub mini jack, which allows for a second audio source such as an MP3 player. The HT-SB300 adds a subwoofer output jack as well. Both models also include a slim remote control with Sharp AQUOS LCD TV control, a programmed equalizer, and an auto On/Off function. The HT-SB300 will be available in April for an MSRP of $299.99; the HT-SB200 will be available in January for an MSRP of $249.99.</p>

<p><br />
<B>1080p DLP&reg; Home Theater Front Projector (model XV-Z15000)</B></p>

<p>Sharp brings home theater to the forefront with a new 1080p DLP home theater front projector that represents an outstanding value in the home theater market. The XV-Z15000 projector bundles an unprecedented 30,000:1 dynamic contrast ratio and high brightness in a price-competitive model, recreating a true cinematic experience. Featuring a sleek high-gloss black design, the XV-Z15000 utilizes a single 1080p DLP 0.65" DMD chip from Texas Instruments (TI) to create one of the most spectacular images available in projection today. The true widescreen 16:9 aspect ratio projector provides cinema-quality images so consumers can enjoy movies in their original widescreen format, from the comfort of their own home. A 24 Hz film mode delivers the accurate representation of the film's originally intended frame rate, giving consumers a more realistic viewing experience. A high brightness level of 1600 ANSI lumens delivers a magnificent and crystal-clear picture and a 6 Segment 6-Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction for an uninterrupted image. A powered iris switchover function gives the consumer enhanced control over brightness and contrast settings with the touch of a button on the remote control, providing flexibility in varying home theater environments with different lighting situations. For easier installations, the model includes two HDMI&trade; terminals (version 1.3 with x.v.Color) to ensure a secure digital connection with all high definition set top boxes or Blu-ray players and an RS-232C input for custom installations. The XV-Z15000 will be available in March for an MSRP of $2,999.99.</p>

<p><br />
<B>Music System for iPod&reg; (Model DK-AP7N)</B></p>

<p>This small, yet powerful, 2.1 channel audio system features an ultra-portable design that folds closed for safe keeping when on the go. The single system houses all the necessary components for an enjoyable listening experience, including the main drivers and subwoofer. With five hours of battery operation and an AC adapter and soft carry bag included, the DK-AP7N is truly a portable solution to enjoying high-quality audio from any location. The iPod terminal allows the user to charge and play music directly from any iPod through the unit's full range bass reflex speakers with HDSS (high-definition sound standard) sound technology. For optimum sound quality, the unit offers Esound, a digital signal processing technology that improves the quality of compressed digital music. By enhancing the sound frequency and increasing the sound pressure, ESound mode corrects deterioration to the sound quality that plagues most compressed music. The DK-AP7N also includes a video output so that when connected to a TV, users can enjoy their favorite iPod videos on a larger screen. The portable The DK-AP7N will be available in March for an MSRP of $129.99.</p>

<p>For more information on Sharp's full line of products, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07495-1163, or call 800-BE-SHARP. For online product information, visit Sharp's Web site at sharpusa.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&reg; Microwave Drawer&reg; appliances, Plasmacluster&reg; air purifiers, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com</p>

<p>* Quick Start time may vary depending on movie content, type of video connection, and type of monitor being used</p>

<p>AQUOS is a registered trademark of Sharp Corporation</p>

<p>HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks of HDMI Licensing, LLC.</p>

<p>DLP is a trademark of Texas Instruments.</p>

<p>iPod is a trademark of Apple.</p>

<p>Energy Star is a trademark of the EPA.</p>

<p>All other trademarks are the property of their respective owners. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  5:27 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1640)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1640)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/sharp-demonstrates-continued-leadership-in-lcd-innovation-and-introduces-cuttingedge-video-and-audio-technologies-at-ces-2009.php" type="text/javascript" charset="utf-8"></script>
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
