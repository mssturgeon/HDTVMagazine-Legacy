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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 390 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 390
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php&amp;title=Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First">
		<span style="display:none">Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &amp;quot;RD-A1&amp;quot; can record and store up to 130 hours of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 390";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First" />
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
	<title>HDTV Magazine - Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First</title>
	<meta name="keywords" content="hard disk, high definition, output line, audio output, video mode, dvd, video, high, mode, digital, output, hard, content, disk, recording, may, definition, line, discs, recorder, toshiba, playback, programs, speed, support" />
	<meta name="description" content="Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &amp;quot;RD-A1&amp;quot; can record and store up to 130 hours of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc." />
	<meta name="title" content="Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=390', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php">Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 22, 2006</b>
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
				<center><p align="center"><strong><font color="#996633" size="4">Integration of 1-Terabyte<sup>2</sup> Hard Disk with HD DVD Recordable Drive Opens way to Recording and Archiving of High-Definition Video</font></strong></p>
</center>

<p><img src="/images/articles/RD-A1.jpg" alt="RD-A1" align="left"><br />
<p>TOKYO-Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &quot;RD-A1&quot; can record and store up to 130 hours<sup>3</sup> of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc. In addition to superb image and sound recording and playback, the new recorder also offers an extensive range of advanced functions made possible by the versatility of HD DVD, including optimized navigation and menu displays. The RD-A1 is scheduled for roll out in the Japanese market from July 14. </p>

<p>The RD-A1 is the first video recorder to support recording and playback of content in the HD DVD format<sup>4</sup>, the next generation of DVD format defined and approved by the DVD Forum. The recorder combines support for recording of full HD broadcasts with high capacity recording to HD DVD-R discs: up to 115 minutes<sup>3</sup> of HD content to a 15-gigabyte (GB) single-layer HD DVD-R disc, and up to 230 minutes<sup>3</sup> to a 30GB dual-layer HD DVD-R disc, allowing viewers to make HD DVD-R libraries of their favorite TV programs, whether dramas, movies or sport. Ease of use is also enhanced by the ability to record two TV programs, one digital HD and one analog, to the hard disk, simultaneously.</p>

<p>In addition to HD DVD, the RD-A1 also supports playback from and recording to conventional DVD-RAM/-RW/-R discs, giving users complete access to content recorded and saved in standard DVD. It also offers simplified transfer of DVD disc content to higher capacity HD DVD discs.</p>

<p>Another key feature among the many supported by the RD-A1 is support for 1080p output via HDMI, allowing viewing of &quot;full HD&quot; progressive scan video signals<sup>5</sup>. Up-conversion<sup>6</sup>of standard DVD to 1080p resolution output also enhances the enjoyment of current DVD software and recorded programs. Video and audio output is further enhanced by the design of the RD-A1's chassis, which isolates the player from vibration and optimizes the performance of its high-grade parts and components.</p>

<p>RD-A1 takes full advantage of the advanced functionality<sup>7</sup> offered by the versatility of the HD DVD format, which far surpasses standard DVD in its extensive support for &quot;pop-up menus&quot; and advanced features such as Picture in Picture (PIP) with moving picture functions.</p>

<p><strong><font color="#996600" size="+1">Background</font></strong><br />
<p>Toshiba launched &quot;RD-2000,&quot; the world's first digital video recorder integrating a hard disk and DVD recorder in the Japanese market in 2001. RD-2000 introduced the world to a new way of viewing TV programs, &quot;First record to hard disk, select and archive to DVD,&quot; and inspired a new market for &quot;Hard Disk &amp; DVD&quot; where Toshiba still provides leadership and drives growth. Now, as HDTV broadcasting expands its services and service area, in readiness for the 2011 phase out of analog broadcasting in Japan, demand is growing for an &quot;Hard Disk &amp; DVD&quot; solution that can handle high definition image quality and its larger data capacities. Toshiba delivers the clear answer with the RD-A1. The new recorder is the first HD DVD recorder, and combines it with a 1TB hard disk, and with it Toshiba leads the industry and supporting for HD DVD playbacks and recorders to be the first manufacture to bring the product in the market. Toshiba will enhance its line-up of HD DVD products by producing the products which integrates the next generation of DVD in the marketplace.</p></p>

<p><strong><font color="#996600" size="+1">Key Features of the New Recorder</font></strong><br />
<p><strong><font color="#996600">1. </font></strong><font color="#996600"><strong>Record digital broadcasts to hard disk and HD DVD-R</strong></font> Integrated digital tuners cover the full range of HD broadcasting sources &mdash;mdash; terrestrial, broadcast satellite (BS) and communications satellite 110&deg; (CS) broadcasts &mdash;mdash; while another dedicated tuner handles analog broadcasts. The RD-A1 can record two broadcasts at once, one digital broadcast, one analog. The hard disk drive's terabyte capacity allows it record and playback 130 hours<sup>3</sup> of HD broadcasts, and viewers are then free to select and archive their favorites to HD DVD discs. The RD-A1 supports two HD DVD-R capacities, a single-layer 15GB disc that can record up to 115 minutes<sup>3</sup> of HD broadcasts, and a dual-layer 30GB disc that doubles that performance to 230-minutes<sup>3</sup>, allowing viewers to build libraries of their favorite programs. The new recorder also supports recording of digital high-definition TV programs on conventional DVD-RAM/-RW/-R discs at standard definition image quality.<sup>8</sup></p></p>

<p><strong><font color="#996600">2. Playback of high definition content and support for advanced content features</font></strong><sup>7</sup> The RD-A1, like the HD DVD players Toshiba has already launched, can play back HD DVD content software, and also supports the enhanced functionality and diverse features that content providers can build into their package software &mdash;mdash; a major step forward from standard DVD players. While specifics depend on the title, typical features include the convenience of a &quot;pop-up menu&quot; that displays menu choices or movie chapters while a movie plays, allowing viewers to search for desired functions or use the chapter guide to jump to a particular scene. The new product also supports PIP with video, a feature that allows, for example, comments by the director or actors to be superimposed over a movie while it is playing. The commentators can literally point to the material they are discussing. &nbsp;Audio output is as rich as video playback, as RD-A1 supports next generation surround sound formats, such as Dolby Digital Plus, Dolby TrueHD and DTS-HD, L-PCM 5.1ch, the same formats as Toshiba's first HD DVD players. Analog 5.1ch output integrated into the RD-A1 allows consumers to enjoy surround sound simply by connecting the player to an AV amplifier with analog input.

<p><strong><font color="#996600">3. Support for RD engine for HD DVD</font></strong> Toshiba has upgraded its successful &quot;RD Engine HD&quot; to provide dedicated support for HD DVD format. Upgrades include a graphic user interface with letter-box display compatibility and Toshiba proprietary multi-function recording software. RD Engine HD allows viewers to edit recorded high-definition programs on a frame basis, and transfer the edited video to an HD DVD disc. A useful function is high-speed write of DVD video sources to hard disks and high speed dubbing<sup>9</sup> of that video to an HD DVD-R, allowing viewers to combine programs from multiple discs on a single disc. This compacting of video libraries is done without any loss of picture or sound quality. 

<p><strong><font color="#996600">4. Digital high definition picture of 1080p in HDMI output</font></strong> Support for the up-conversion to 1080p output is achieved<sup>10</sup> through implementation of the newest high performance scaler from Anchor Bay Technologies Inc. This converts and plays back 1080i HD content as 1080p full HD output<sup>11</sup>. Furthermore, besides the HD DVD software and DVD software, it is also possible to playback past recorded DVD by up-converting them to 1080p output<sup>12</sup>. 
<p><strong><font color="#996600">5. Body and parts designed for high definition picture quality and high quality sound </font></strong> The RD-A1's design is optimized for high quality video and audio output by a special dual-layer body featuring a 1-millimeter main case and a metal sub frame. The recorder stands on special aluminum pillars designed to damp vibration and enhance high sound quality. The same attention to detail carries through to chief components. The RD-A1 is the first recorder to adopt a high-speed, high-performance 297MHz/14bit video encoder, making it possible to deliver HD quality via analog output through the D terminal and component terminal. High grade parts typically found in high-end audio products are also used in the RD-A1. 

<p><strong><font color="#996600">6. Internet connectivity via &quot;Net de Navi<sup>&reg;</sup>&quot;software, recommendation service, and DLNA guideline</strong></font><sup>13</sup> The versatility of the RD-A1 is significantly enhanced by its Internet connectivity via &quot;Net de Navi<sup>&reg;</sup>&quot; software. Once in a network the recorder can be programmed remotely, via e-mail or the on-line iEPG, an electronic TV program timetable service. LAN connectivity allows configuration of a home network&nbsp; with Toshiba's series of digital high-definition LCD TVs in the &quot;REGZA Z1000&quot; series and with the &quot;Qosmio G30&quot; AV notebook PC, which supports DLNA guideline, and allows users to playback the recorded titles on different devices on the network<sup>14</sup>. 

<p>1 As of June 2006, as a digital video recorder with HD DVD.</p>

<p>2 1TB is 1,000GB (Gigabyte), calculated on the basis of 1GB=1 billion bytes.<br />
 <br />
3 Recording of digital terrestrial broadcasts at approx.17Mbps in TS mode.<br />
 <br />
4 Playback and recording in MPEG4 AVC and VC1 is not available for HD-DVD-R.<br />
 <br />
5 An HDTV or HD display equipped with D3/D4 input or HDCP capable HDMI input is required for high-definition viewing.</p>

<p>6 Since up-conversion is from standard definition video, image quality may not match that of an original high-definition source. <br />
 <br />
7 1) Some advanced functions may not be available, depending on HD DVD content specifications.<br />
 <br />
 2) HD DVD player support for versatile functions is based on software instructions integrated with the content. Such instruction may need to updated, via downloads from the Internet. Functions and operation, including the display, sound effect and icons, may differ with content. Consult the manufacturer's customer service or the user manual for more details.<br />
 <br />
 3) Please check content specifications of the content since some contents may not apply to the up-date information or may require a broadband Internet connection. </p>

<p>8 VR-mode recording is available in CPRM discs. Support for cartridge in DVD-RAM. Also, support for VR-mode recording in DVD-R DL.<br />
 <br />
9 No support for copy-once recording. Limited to titles recorded in VR mode, or titles recorded in the Toshiba RD series products supporting re-writing.<br />
 <br />
10 In order to enjoy viewing 1080p output signals, a cable and a TV or display that support the 1080p signal format is required. </p>

<p>11 Some content may not up-convert. </p>

<p>12 Since up-conversion is from standard definition video, image quality may not match that of an original high-definition source.</p>

<p>13 DLNA (Digital Living Network Alliance) is a organization that supports standardization of home LAN.&nbsp; Supports only the digital media server.</p>

<p>14 Copy free, limited to titles in VR mode. </p>

<p><font color="#996600"><strong><font size="+1">Key Specifications</font></strong></font> 
<table class="type1b" cellpadding="0" cellspacing="0"><tr><td class="type1b_header" nowrap >Model name</td><td class="grid">RD-A1</td></tr><tr><td class="type1b_header" nowrap >Hard Disk&#12288;</td><td class="grid">Built in Hard DiskHD DVD-Video
Twin Format Discs (Dual-layer, single-sided, HD DVD-Video + DVD-Video), HD DVD-R (HDVR mode), HD DVD-R DL (HDVR mode), DVD-R (Video mode; VR mode), DVD-R DL (Video mode; VR mode), DVD-RAM (VR mode), DVD-RW (Video mode; VR mode), DVD-Video Music CD CD-R, CD-RW (CD-DA), <table> <tr> <td nowrap valign="top">*</td> <td>Some content and discs may not be compatible. Depending on recording mode and condition, some discs may not playback. DVD-RW Ver.1.0 is not supported.</td> </tr> </table></td></tr><tr><td class="type1b_header" nowrap >Recordable media <p></p>
</td><td class="grid" class="grid"> <p>Built in Hard Disk (TS mode; VR mode) HD DVD-R (HDVR mode), HD DVD-R DL (HDVR mode), DVD-R (Video mode; VR mode), for General/1X-16X SPEED (recording speed 8X), DVD-R DL (Video mode; VR mode), for General/4X SPEED (recording speed 4X), DVD-RAM (VR mode), 2X-5X SPEED (Applied cartridges), (recording speed 2X), DVD-RW (Video mode; VR mode), 1X-6X SPEED (recording speed 4X), *Some discs may not be compatible, or may not record.</td></tr><tr><td class="type1b_header" nowrap >Video recording format&#12288;</td><td class="grid">MPEG 2&#12288;</td></tr><tr><td class="type1b_header" nowrap >Audio recording format</td><td class="grid"><p>Dolby Digital (2ch), L-PCM (2ch), AAC (5.1ch),</p></td></tr><tr><td class="type1b_header" nowrap >Audio output</td><td class="grid">Dolby Digital (5.1ch)
Dolby Digital Plus (5.1ch), Dolby TrueHD (2ch), DTS-HD (5.1ch) L-PCM (5.1ch) *Output format depends on content.</td></tr><tr><td class="type1b_header" nowrap >Video DAC</td> <td class="grid">14bit, 297MHz</td></tr><tr><td class="type1b_header" nowrap >Audio DAC&#12288;</td> <td class="grid">192kHz, 24bit</td></tr><tr><td class="type1b_header" nowrap >Channel&#12288;</td><td class="grid">Digital terrestrial broadcast (000 - 999ch), CATV path through,BS digital (000 - 999ch), 100 degree CS digital broadcast (000 - 999ch), Analog terrestrial broadcast VHF (1 - 12ch), UHF (13 - 62ch), CATV (C13 - C63ch),
</td></tr><tr><td class="type1b_header" nowrap >Input</td><td class="grid">S1 video input line 3 (rear 2, front 1), video input line 3 (rear 2, front 1), 2ch analog audio input line 3 (rear 2, front 1), D1 video input line 1, DV input line 1 (front)</td></tr><tr><td class="type1b_header" nowrap >Output&#12288;</td><td class="grid">D1/D2/D3/D4 video output line 1, component video output line 1 (Y, CB, CR), S1 video output line 3, video output line 3, 5.1ch surround-sound analog audio output line 1, 2ch analog audio output line 3, Coaxial digital audio output line 1, Optical digital audio output line 1, HDMI output line 1, i.LINK line 2 (D-VHS dubbing)</td></tr><tr><td class="type1b_header" nowrap >Antenna terminal</td> <td class="grid">Digital terrestrial in-output, BS/100degree CS digital in-output, VHF/UHF in-output</td></tr><tr><td class="type1b_header" nowrap >Other terminals&#12288;</td> <td class="grid">LAN terminal, Sky Perfect continuous terminal, phone circuit terminal, Extension terminal line 3 (front, 5V 500mA)</td></tr><tr><td class="type1b_header" nowrap >Power consumption&#12288;</td><td class="grid">133W (BS antenna supply:144W), Stand by mode 6.5W (power-save:4.0W),</td></tr><tr><td class="type1b_header" nowrap ><p>Dimensions</p></td><td class="grid">Width 457 mm &times; Height 159 mm &times; Depth 408 mm</td></tr><tr><td class="type1b_header" nowrap >Weight&#12288;</td><td class="grid">15.2 kg&#12288;</td></tr><tr><td class="type1b_header" nowrap >Accessories&#12288;</td><td class="grid">Remote control, battery for remote control (AAA cell battery x 2),power cable, coaxial cable, video/audio connecting cord, user manual, B-CAS card, modular splitter, phone cable</td></tr></table>

<p><br />
<table><tr><td valign="top" nowrap>&#12539;</td><td>RD-A1 supports AACS (Advanced Access Content System), the next generation content protection &nbsp;system.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>An HDTV or HD display equipped with D3/D4 input, HDCP capable HDMI input, or component video input is required for high-definition viewing. Other TVs or displays can display content, but not in high definition. Also, some content may not playback or playback in lower resolution on equipment with D3/D4 and component video output.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>HDMI and High-Definition Multimedia Interface are trademarks of HDMI Licensing, L.L.C.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>HD DVD and DVD are trademarks of the DVD Format/Logo Licensing Corporation.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Dolby and Dolby Digital are registered trademarks of Dolby Laboratories.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>DTS is a registered trademark of DTS, Inc.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>i.LINK is a registered trademark of Sony Corporation.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Other company names and products names are the registered trademarks of each company.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Service via the Internet may subject to temporary cessation or termination without notice.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Some discs may not playback or record.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Some discs may be incompatible with playback and/or record.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Recording of audio and video is for personal use and unauthorized usage is prohibited under the copyright laws.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>While Toshiba has made every effort at the time of publication to ensure the accuracy of the information provided herein, product specifications are subject to change without notice.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Not all discs and output connectors are compatible and no guarantee of performance is made hereby.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Actual writing speed may decrease from the actual speed.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>This digital AV product integrates diverse software. The hard disk and HD DVD drive are connected via an ATAPI interface, a PC-based connection standard, and both the hardware and software are managed via operating system software, like a PC.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>While designed to be robust, the hard disk contains moving parts, and may be damaged if proper conditions of use are not observed. If a part of the hard disk platter becomes damaged, programs recorded on that part may exhibit pixelation or black noise when played back. If you notice such noise or problem, you will have to replace the hard disk, at cost.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>We do not recommend using the hard disk for long term storage of programs. Transfer important programs that you want to save to a recordable DVD disc. Recordable DVD discs are also susceptible to damage if not handled and stored carefully, and as a result some of all of the programs stored on them may become unplayable. Reduce these risks by using high quality DVD recordable discs and checking their playability from time to time. Recovery of programs deleted from the hard disk is not guaranteed.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Hard disk capacity is calculated on the basis of 1TB=1,000GB, and 1GB =1-billion bytes.</td></tr></table></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 22, 2006  5:30 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(390)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 390)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php" type="text/javascript" charset="utf-8"></script>
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
