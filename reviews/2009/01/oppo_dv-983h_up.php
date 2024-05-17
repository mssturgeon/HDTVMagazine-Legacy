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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1626 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1626
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=OPPO DV-983H Upconverting DVD Player - On the Test Bench&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/reviews/2009/01/oppo-dv983h-upconverting-dvd-player-on-the-test-bench.php&amp;title=OPPO DV-983H Upconverting DVD Player - On the Test Bench">
		<span style="display:none">The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection since the means to see it would infer a means to steal it. At this time the Panasonic PTAE-1000U has been kept in the stable just for the purpose of using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, preventing me from verifiying the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and are noted. All tests were performed using Digital Video Essentials as the source material.</span></a>
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

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1626";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download OPPO DV-983H Upconverting DVD Player - On the Test Bench" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="OPPO DV-983H Upconverting DVD Player - On the Test Bench" />
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
	<title>HDTV Magazine - OPPO DV-983H Upconverting DVD Player - On the Test Bench</title>
	<meta name="keywords" content="dvd audio, frequency response, hqv benchmark, waveform monitoring, sonic signature, player, test, audio, video, oppo, pass, response, dvd, color, disc, toshiba, analog, frequency, jvc, performance, better, audiophile, being, sacd, detail" />
	<meta name="description" content="The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection since the means to see it would infer a means to steal it. At this time the Panasonic PTAE-1000U has been kept in the stable just for the purpose of using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, preventing me from verifiying the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and are noted. All tests were performed using Digital Video Essentials as the source material." />
	<meta name="title" content="OPPO DV-983H Upconverting DVD Player - On the Test Bench" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/reviews/2009/01/oppo-dv983h-upconverting-dvd-player-on-the-test-bench.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1626', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2009/01/oppo-dv983h-upconverting-dvd-player-on-the-test-bench.php">OPPO DV-983H Upconverting DVD Player - On the Test Bench</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>January  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=279&category=Upconverting DVD Players">Upconverting DVD Players</a></b>
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
				<p class="editorial">This portion of the review details how the OPPO DV-983H performed on the test bench. Please read the <a href="/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php">OPPO DV-983H Review Essentials</a>, if you have not already.  <h2>On the Test Bench </h2> <p>The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection since the means to see it would infer a means to steal it. At this time the <a href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000U</a> has been kept in the stable just for the purpose of using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, preventing me from verifiying the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and are noted. All tests were performed using Digital Video Essentials as the source material.  <p>Comparison Players<br><a href="http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php">Toshiba HD-A35</a><br><a href="http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php">OPPO DV-981HD</a>  <h2>Digital Video Essentials</h2> <p><b>Video Levels</b>  <p>Whether by visual calibration or waveform monitoring, the player output 0IRE and 100IRE at the correct 16/235 levels.  <p><b>Color Decoding</b>  <p>Whether by visual calibration or waveform monitoring, the player output correct color decoding at HD scan rates, 720p, 1080i and 1080p (does not include 480p).  <p><b>Horizontal Frequency Response Luminance</b>  <p>As noted, waveform monitoring response was useless for this test. Visually the player passed the continuous frequency burst test quite well for luminance. For the low frequency pattern there is banding for the highest frequency burst. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly and this player is no exception. This pattern always has banding so the best I can state on this is high, medium or a low contrast response with high being the best and low being the worst. For the OPPO DV-983H a high contrast response was the norm; equaling our current reference, the Toshiba HD-A35.  <p><b>Vertical Frequency Response Luminance</b>  <p>Vertical frequency response was excellent in 720p, 1080i and 1080p.  <p><b>Frequency Response Color</b>  <p>While some banding is normal, the DV-983H showed a much higher level of banding than any other player reviewed so far. The contrast levels also dropped for the right one third of the response, the higher frequencies. The Toshiba HD-A35 remains a reference for this test.  <p><b>CUE, Chroma Upsampling Error</b>  <p>This causes a vertical breakup of color detail in the vertical plane, typically expressed in reds but can show up for other colors, and is related to the player using only one MPEG decoding method rather than both interlace and progressive and applying the correct version to the native source on the disc. The DV-983H passed.  <h2><a href="http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php">HQV Benchmark DVD</a></h2> <p>Color Bars (4:3) PASS<br>Color Bars (16:9) PASS  <p>Jaggie 1 (16:9) PASS  <p>Jaggie 2 (16:9) FAIL  <p>Flag (4:3) PASS  <p>Detail (16:9) PASS  <p>Noise (4:3) NA*  <p>Motion Adaptive Noise (16:9) NA*<br>Motion Adaptive Noise (4:3) NA*  <p>*uses block noise reduction related to MPEG compression  <p>Film Detail (4:3) PASS  <p>Assorted Cadences (16:9) <br>2-2 30fps film PASS<br>2-2-2-4 DVCAM FAIL<br>2-3-3-2 DVCAM PASS<br>3-2-3-2-2 VARI SPEED Broadcast PASS<br>5-5 Anime PASS<br>6-4 Anime PASS<br>8-7 Anime PASS<br>3-2 24fps film PASS  <p>Mixed 3:2 with titles (4:3) PASS  <h2>ABT Test Disc</h2> <p>You get a free ABT test disc to show off the capability of your player and of course the DV-983H passes all of the tests. Consider this a mini review. Similar to the HQV Benchmark DVD it, contains similar test material for jaggies, cadence and titles. I tested this disc on the OPPO DV-981HD and the Toshiba HD-A35. The OPPO DV-981HD passed many of the tests but it uses intelligent deinterlacing. The Toshiba failed horribly and uses dumb deinterlacing which so far infers the disc being played lacks progressive flags, which a dumb deinterlacing design depends upon. The ABT disc image quality and some of the tests like jaggies are better implemented and delivered than the HQV Benchmark.  <p><b>Scaling</b>  <p>I tested the OPPO at 1080p, 1080i and 720p feeding a 1080p DLP front projector that supports 1:1 pixel mapping with other scan rates. As expected the edges were soft which is a byproduct of the scaling process for nearly any manufacturer, although pixel-mapped 1080p output into a native 1080p will provide the sharpest response. Color bar patterns showed the typical dark edging where the different colors meet.  <p>Moving on to test images from DVE at 1080p I was greeted with an overall good response. The unit is not perfect and the DV-981HD and Toshiba HD-A35 have an edge. As noticed for the color burst response test this player is lacking by comparison. Overall either of the other players mentioned faired better with the Toshiba remaining a reference. This loss of detail was most evident in the restaurant sequence during the table view. The young man has a desert plate with a garnish of cut strawberries. The Toshiba provided better detail and nuance with the DV-983H blurring that. The DV-981HD didn't fair as well but outperformed the DV-983H.  <p>The next disc up was Star Wars Episode II, a disc I have tested to no end over the last year with numerous products. As with the above, the response was good but lacked that edge of color detail that the Toshiba can deliver.  <p>I typically don't bring up some DVDs I have burned from a variety of old VCR tapes and laserdisc titles. Eventually one of these will fall into a player and the OPPO was no exception. I bring them up because the OPPO would lose cadence lock if a pause, FF or RW function was used causing a choppy, strobing effect. Going in and out of the modes might get it to lock again. I have never had this problem with any other player.  <p><b>Scaling Special Features and Oddball Cadences</b>  <p>Typically I have two paragraphs describing limitations with such content but it is exactly in this area that the DV-983HD shines. What other players trip up on, this one will pass without a hitch. It passed all but one of the HQV Benchmark cadence tests! If you are a DVD collector looking for the best overall response with ANY content the DV-983HD has you covered. Just like an external scaler the OPPO delivers the goods yet for detail an external scaler has an edge much like the Toshiba HD-A35.  <p><b>Aspect Ratio Control</b>  <p>The DV-983HD provides an auto 16:9/4:3 switching mode so the player maintains correct aspect with special features or 4:3 movies, black side bars. OPPO calls this 16:9 Wide/Auto and is found in the setup menu.  <p>For the DVD collector with 4:3 letterboxed content this player performs quite well when expanding such content to fill out your screen. Again the player is directly competing with an external scaler in this capability.  <p><b>Additional Video Features</b>  <p><u>Y/C Delay</u><br>With a calibration disc you test for this error on your display and correct it.  <p><u>CUE Correction<br></u>Although the player passes the CUE test there is also ICP, Interlaced Chroma Problem. From the manual, "ICP is caused by encoding interlaced video so you may encounter it on some DVDs". Beyond the specific CUE patterns on the ABT test disc I did not see this feature change anything and it would seem leaving it in automatic will give you the best results.  <p><u>Video Mode</u><br>If you are playing back a PAL encoded DVD you can select the front end MPEG video decoder, Video 1, or use Video 2, the Precision Scaling and RightRate video processing technologies of the Anchor Bay processor. This feature was not tested.  <p><u>Color Space</u><br>Auto is based on information from your display during the HDMI handshake. You can force YCbCr 4:4:4 color space (480i), RGB Video Level (HDMI) and RGB PC Level (DVI). Useful if your display EDID is incorrect or missing in action for automated settings.  <p><b>Audio Performance</b>  <p>The OPPO provides full bit stream or multichannel PCM support for digital audio connections. Being a simple matter of set-and-forget, there was nothing to test. On the other hand it does provide 8-channel analog audio with 24/192 D/A converters and supports SACD and DVD Audio. I tested the PCM stream using a Denon AVR3808Ci A/V receiver which provides 24/192 DA conversion for the outputs. In this mode I was able to duplicate all DVD Audio formats out to 24/192. When an SACD is played the Denon indicates 88.2 kHz. Whether in multichannel or stereo mode SACD lacked in clarity not only from being down converted* to 88.2 kHz but also conversion from DSD to PCM. Ultimately, getting the full potential of SACD performance is an audiophile concern and I suggest an audiophile stand alone player. I know that is not an easy or inexpensive product to find. It is unfortunate but in the end maintaining a pure unconverted signal for SACD from source to decoded analog output, whether that be stereo or multichannel analog or digital, is a huge challenge for the end user on a budget.  <p>The player has earned some kudos as an audiophile product so I am going into more depth for this aspect of performance. Testing of the analog outputs takes us to my 2-channel system which is composed of custom and modified products designed and setup for the ultimate expression of a neutral audio signature. The only connections for either player during testing was the power into a PS Audio Power Plant 300 and audio connections to the preamp. The reference point is my modified JVC XLV720 for DVD Audio. The core of this test is simplistic D/A conversion performance of the analog outputs and therefore limited to 24/192 DVD Audio in stereo mode only.  <p>Make sure you turn on Audio Only mode as it makes a huge difference in the overall delivery. Note that when turning it on there is a delay before the feature is implemented and shows up on the display. Turning the video back on is instant. Unfortunately you can't use this feature with a movie so you will be taking this performance hit if you are using the analog outputs. Turning off the display provides marginal improvement. The player does have an OFF selection for the display turning it on when a feature or operation has been accessed and back to off automatically after a short period.  <p>Sonic Signature: The signature is robust and thick; all sonics have a heavier sound than normal as if the harmonics below the primary tone are being accentuated, best described as more of everything. Many a listener will be drawn into this signature because on the surface more is always perceived as better. This sonic signature covers up the nuances of tone and harmonics that are otherwise heard with the JVC. The sonic signature can complement a system that is the opposite; thin and strident. This also tilted the comparison because everything was perceived as louder than the JVC. On one test I used a higher volume setting for the JVC to counteract this anomaly yet that just made the JVC sound even better! Ultimately, while perceptually and euphonically pleasing, the DV-983H was not accurate.  <p>Sound Stage: With the video circuits turned on the soundstage is significantly pulled to the center. Even with the video circuits turned off the soundstage was far narrower than the JVC. Overall the playback had that in-your-face character. Most noticeable was a lack of space and depth. The OPPO delivered a disconnected <em>you are here and the music is there</em> experience rather than the commingling <em>we are one experience</em> of the JVC. The OPPO sounded contained; the JVC was limitless without boundaries enveloping the listener in a perceptual surround experience.  <p>Considering the price of the player, top notch audiophile analog performance is not a reasonable expectation. If you are an audiophile seeking audiophile stereo or multichannel analog performance outputs you will have to look elsewhere. Most will find the analog output satisfying and experience the improvement of comparing CD to SACD or DVD Audio. A dedicated audiophile CD player could sound better overall! In the end the DV-983H provides a good entry level audio signature that is to be credited for a smooth response while not doing anything grossly wrong or irritating causing listener fatigue. On the other hand it does provide multichannel PCM streams via HDMI for a capable receiver and this is where some form of audiophile nirvana can be found with DVD Audio discs.  <p><b>Bench Testing Perspective and Subjective Experience</b>  <p>Overall the DV-983H performed quite well for all test patterns but one, the Chroma Burst. With subjective viewing of actual images the OPPO DV-981HD is one hair better and the Toshiba HD-A35 was five hairs better providing a more refined and detailed presentation. While visible on my downstairs reference system at 3 screen heights, there was no real difference on the upstairs casual system at 4-5 screen heights. Bottom line is you need a very refined system used at its maximum potential to have any concern over this. More importantly the strong suit of the DV-983H is not ultimate performance with the main feature but how it handles everything else you can throw at it!  <p>Most will find the player pleasing for audio while opening the door to the world of HD audio in the form of DVD Audio and SACD. Bear in mind that the digital connection with DVD Audio is your best route to sonic nirvana.  <p><b>References</b>  <p>*<a href="http://www.smr-home-theatre.org/surround2002/technology/page_07.shtml">Poking a round hole in a square wave</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>January  8, 2009 11:37 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1626)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 1626)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/01/oppo-dv983h-upconverting-dvd-player-on-the-test-bench.php" type="text/javascript" charset="utf-8"></script>
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
