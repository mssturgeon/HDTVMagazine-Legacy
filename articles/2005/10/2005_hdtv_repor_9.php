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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 224 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 224
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 10: HDTV Tuners & Tuning DVR\'s&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-10-hdtv-tuners-tuning-dvrs.php&amp;title=2005 HDTV Report, Part 10: HDTV Tuners &amp; Tuning DVR's">
		<span style="display:none">In May 2004, at the National Cable and Communications Association (NCTA) Motorola and Scientific Atlanta announced their new HD cable boxes with DVR and VOD capability.  The Explorer 8300 multi-room cable DVR from Scientific Atlanta would have the capability of connecting with up to three non-DVR STBs using existing home wiring and provide image control (FF, RW, etc) from all the STBs and IPG, VOD, and PPV content.

Motorola unveiled their...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 224";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 10: HDTV Tuners & Tuning DVR\'s" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 10: HDTV Tuners & Tuning DVR\'s" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 10: HDTV Tuners & Tuning DVR's</title>
	<meta name="keywords" content="ces report, digital audio, dvi hdcp, component rgb, atsc ntsc, atsc, ces, dvr, ttm, component, digital, dvi, tuner, cable, stbs, tuners, hdd, stb, audio, directv, dvd, model, outputs, report, unit" />
	<meta name="description" content="In May 2004, at the National Cable and Communications Association (NCTA) Motorola and Scientific Atlanta announced their new HD cable boxes with DVR and VOD capability.  The Explorer 8300 multi-room cable DVR from Scientific Atlanta would have the capability of connecting with up to three non-DVR STBs using existing home wiring and provide image control (FF, RW, etc) from all the STBs and IPG, VOD, and PPV content.

Motorola unveiled their..." />
	<meta name="title" content="2005 HDTV Report, Part 10: HDTV Tuners &amp; Tuning DVR's" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-10-hdtv-tuners-tuning-dvrs.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=224', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-10-hdtv-tuners-tuning-dvrs.php">2005 HDTV Report, Part 10: HDTV Tuners & Tuning DVR's</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 19, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p>In May 2004, at the National Cable and Communications Association (NCTA) Motorola and Scientific Atlanta announced their new HD cable boxes with DVR and VOD capability.  The Explorer 8300 multi-room cable DVR from Scientific Atlanta would have the capability of connecting with up to three non-DVR STBs using existing home wiring and provide image control (FF, RW, etc) from all the STBs and IPG, VOD, and PPV content.</p>

<p>Motorola unveiled their DCT6412 HD cable box/DVR/modem for a network environment, using IP-over-coaxial developed by Entropic.  The DVR system could stream out up to four HD recorded programs simultaneously and control recording functions from other rooms.  Similar features have started to be downloaded as software upgrades on the DCT6208.</p>

<p>In August 2004, LG announced that the company expects DTV ATSC STBs that would down-convert the digital signal and connect via RF or base to analog TVs to be retailing between $50 and $70 by 2008, starting to be under $100 in late 2005.  The company also estimates the existence of about 80 million analog TVs that tune to broadcast via antenna, to those the STBs above would offer the option to continue using their analog TVs, which is expected to generate enough demand for the volume to bring the price down to the expected price, although lower licensing fees are also a contributor to the lower price.</p>

<p>In Oct 04, Zenith demo their E-VSB (Enhanced VSB) for reception with multiple moving echoes (car/bus), and MPEG-4, suited better than MPEG-2.</p>

<p>In December 2004, IMS Forecast issued a projection of 90 million HD-STBs to be shipped worldwide by 2009, doubling the 2004 expectation.  The region of Asia Pacific is expected to supply about 40 percent of that volume, mainly due to China's aggressive migration to digital transmission in such a large population.</p>

<blockquote>Note: Satellite services companies offer packages that bundle programming services with HD-STBs or even H/DTV sets, packages are not covered in this report.</blockquote>

<p><br />
<h2>Dish Network</h2><br />
The following two models (811 and 921) are listed as they appeared on the CES 2004 report, although the 921 is now discontinued:</p>

<p><u>DISH811</u><br />
<img src="/articles/images/mt/image073.gif" alt="Dish811" align="left"><i>$400, TTM Dec 03, ATSC OTA/satellite tuners (one each) DVI/HDCP, NO 1394, component out, NO RGB out, replaces Dish 6000, NO PVR, tuner module included inside the unit, same selectable outputs of model 921, converts formats to any output, 2 days of electronic program guide, optical digital audio out.</i><br />
<br clear="all"><br />
<u>PVR 921</u><br />
<img src="/articles/images/mt/image074.gif" alt="PVR 921" align="left"><i>(Innovations CES 2003 best of show winner), $1,000, original TTM was for 2Q03 (actual TTM was Dec 03), although the unit has been announced as ready to release for almost 2 years. HD-PVR with a 250 GB HDD, up to 180 hrs SD, up to 25 hrs HD, one DVI-I/HDCP, two 1394/DTCP to be enabled via future software upgrade, dual satellite tuners, ATSC OTA tuner built in, one component out, 2 USB ports for future use (such as remote keyboard), records DD when available and over the air digital broadcasts, headphone and USB jacks in front panel, records up to two programs in the PVR simultaneously (HD or any) while capable to play another HD program stored in the PVR (or from the 3rd HD tuner).</i></p>

<p><i>Nine days electronic program guide, optical digital audio out, 30-second skip for commercials, four fast-forward and fast-rewind speeds, picture-in-picture, multi-device remote control, selectable output from the menu for 480p/720p/1080i, stores signal in original resolution.  Some users reported that the HDD is always turning.  Beta testing reports are available on the Web.</i></p>

<p>The PVR921 was actually introduced by mid 04, is now discontinued, and it is currently been offered for $549.</p>

<p><u>CES 2005</u></p>

<p>Dish Player DVR942 ($700 + $50 dish, TTM Feb 05), 250GB DVR for up to 25hrs HD, or 180 hrs SD, dual tuner satellite receiver with 2 TV outputs for multi-room viewing, up to 9 days EPG, records DD, ATSC tuner and records OTA, caller ID with history, 2 USB ports for future use, optical audio out, DVI/HDCP, component YPbPr, planned to be offered also for lease with a $250 initiation fee (and the subscriber is expected to return the box at the end of the service).  </p>

<p>A couple of portable DVRs were also displayed at CES 2005 that store content transferred from the DVR942, once stored into the portable unit the content can not be outputted (other than playing back or erasing it, by the subscriber).  Screen sizes are 2.2, 4, and 7-inch screens, capacity of 20GB and 40GB, some accept Compact Flash cards, have IEEE1394, and USB 2.0 to receive the content.</p>

<p><br />
<h2>DirecTV</h2><br />
The units shown below were part of Hughes inventory before they were acquired, and still current, the text is sourced from the CES 2004 report:</p>

<p><u>Hughes</u><br />
<img src="/articles/images/mt/image075.jpg" alt="HTL-HD" align="left"><i>HTL-HD	$500, TTM Nov 03 (unit apparently offered for $99 temporarily by DirecTV), ATSC and DirecTV tuners, similar to HD300 from Sony and 3200A from LG, but IR remote, DVI, component, optical digital audio (no coaxial), VGA D-sub 15 in, switch in back for DVI/VGA, DVI cable, 720p/1080i switch (front button on box)</i><br />
<em><br clear="all"></em><br />
<img src="/articles/images/mt/image076.jpg" alt="HD-DVR250" align="left"><i>New HD-DVR250	$1,000, TTM Apr 04, w/HD Tivo, Best of Innovations CES 2004. 2 ATSC + 2 DirecTV tuners (E* 921 HD-STB has only 3 tuners in total), HDMI/HDCP, component, 2 sat RF inputs, digital audio Toslink, 2 USB ports (for future use), 1 RF antenna that splits internally to two ATSC tuners, 250 GB DVR for up 30 hrs of HD recording or up to 200 hrs of SD recording, built-in fan, S-video out.</i></p>

<p><i>Pause live TV up to 30 minutes, DirecTV advanced program guide w/14 in advance (most PPV 24hrs in advance), multiple screen formats (standard, letterbox, panorama).</i></p>

<p><i>Selectable output for 480i/p, 720p, or 1080i (reportedly via soft button).  Functionality to been able to pause, instant replay, rewind live TV and fast forward and playback recorded programs in normal speed, slow motion or frame by frame.</i></p>

<p><i>Can record two different programs from either DirecTV, ATSC or one from each, as well as watch a pre-recorded program at the same time.  It records one HD program while watching another (requires connection of two satellite inputs from a triple LNB dish antenna).  Simultaneous SD and HD output not specified (and assumed as NO).  According to DirecTV, there are NO plans for a future 1394 output.</i></p>

<p><u>CES 2005</u><br />
DirecTV is working on a new Home Media Center DVR that will connect to clients around a house network, the center is initially for SD services only (demo at CES) but it is expected that a similar unit with HD capabilities will follow in late 2005, most possibly in 2006.  Such unit would probably be the one to replace the current 250-DVR when switching to MPEG-4 later in 2005.</p>

<p><br />
<h2>HP</h2><br />
Digital Entertainment Center (DEC) STB with MS Media Center Edition 2005 for photos, music, video, FM tuner, HDTV tuner, and PVR, all in one box</p>

<p>Model z540	$1500, TTM Oct 04, single analog TV tuner, 160 GB HDD<br />
Model z545-b	$2000, two tuners, 200GB HDD, removable 160GB Personal Media Drive via USB2 to transport multimedia file anywhere and expandability</p>

<p>Extender unit		$300, to make a regular TV a Media Center client by pulling content that resides in the DEC center via a wireless or Ethernet cable connection, with a maximum of five clients.</p>

<p><br />
<h2>JVC</h2><br />
As appeared on the CES 2004 report:<br />
<i><br />
<img src="/articles/images/mt/image077.jpg" alt="TU-DVR921RU" align="left">TU-DVR921RU	$1,000, TTM Dec 03<br />
JVC unit that pairs the Dish Network 921, 250 GB HDD, Dish Wire 1394 A/V connectors to use with select products, ATSC and NTSC tuners, DVI/HDCP, dual E* tuner 480i/p/720p/1080i outputs, DVR capacity for up to 180 hrs of SD or 25 hrs of HD, or a combination of both.</i></p>

<p>The sibling PVR921 unit from Dish Network was actually introduced by mid 04, and is now discontinued. <br />
<br clear="all"><br />
<u>CES 2005</u><br />
TUDP811 - Current model, check details on 2004 report</p>

<p>TUDVR942 - Sibling of the DVR942 DVR Dish Network STB mentioned above</p>

<p><br />
<h2>LG / Zenith</h2><br />
Jun 04<br />
LST-3410A DVR - update in price $650 (from $1000), discontinued units are offered at Best Buy $550 (Dec 04).  </p>

<p>The following is the detail of the available units from when the units when introduced (as appeared in the CES 2004 report):</p>

<p>LG LST-3410A<br />
<img src="/articles/images/mt/image079.jpg" alt="LG LST-3410A" align="left"><img src="/articles/images/mt/image078.jpg" alt="LG LST-3410A" align="left"><i>(Previously announced as Zenith HD-PVR330), $1000, TTM Feb 04 (originally Nov 03), ATSC, QAM tuners, PVR 120GB, 8 hrs HD recording, DD recording, DVI/HDCP, RGB, component, <u>1394 2-way</u>, GemStar EPG, No CableCARD for scrambled cable channels</i></p>

<p><i>LG LST-3510A (previously announced as Zenith HDX330), $500, TTM 4Q03, ATSC/QAM/NTSC tuners, DVD player, 3:2 pull-down, 5.1 DD audio, simultaneous HD and SD outputs, DVI/HDCP, RGB, component, DVD upconversion to 1080i over DVI, selectable 1080i, 720p, 480i/p outputs, line-doubler.  Originally excluded IEEE1394 (when from Zenith), LG version excluded IEEE1394 as well.</i></p>

<p><u>CES 2005</u><br />
LST-4200A	ATSC/NTSC/QAM unscrambled tuners, DVI/HDCP, PSIP, simultaneous 480i output, component, RGB</p>

<p><br />
<h2>Mitsubishi</h2><br />
Apr 04 (company announcement of 2004/5 models)</p>

<p><u>New HDTV Receiver/Controller</u><br />
<img src="/articles/images/mt/image080.gif" alt="Mitsubishi HD-6000"><br />
HD-6000	TTM later 04 at selected retailers, HD 120GB PVR (personal video recorder), up to 12 hours of HD recording, and 72 hours of non-HD, subscription free, MPEG SD encoder, AMVP2(TM) Mitsubishi's second-generation Advanced Multimedia Video Processor.</p>

<p>ATSC/QAM CableCARD/NTSC tuners, NetCommand(R) 4.0 system control, PerfectColor(TM) 6-way color adjustment, TV Guide On Screen(R) electronic program guide, seven inputs including one HDMI, and three component video inputs.  Outputs include one HDMI, and one component video.  Two FireWire(R) IEEE 1394 digital home-networking ports.  </p>

<p>The analysis I provided in the CES 2004 report (below) for the predecessor unit of the above model (the HD-5000 Network Controller - Promise set top for $1700) still applies to the newer model regarding digital connectivity to legacy TVs, the text was as follows:</p>

<p><img src="/articles/images/mt/image081.jpg" alt="HD-5000 Network Controller" align="left"><i>This unit (HD-5000 Network Controller) facilitates early HDTV monitors with home networking capability and digital recorders connectivity even though their TVs lack digital interface connections, however, it does not provide those early HDTV monitors with the full digital connectivity available on newer DVI HDTVs, as follows.</i></p>

<p><i>The controller does NOT have a DVI input, which means that the solution offered by this unit as the promise for a " customer who wants all of the features and convenience of a top-of-the-line integrated HDTV" (as stated on the web-site), would not actually be met for DirecTV subscribers.</i></p>

<p><i>A subscriber of DirecTV, which STBs only use a DVI output as digital connection, who is also the owner of an earlier non-DVI HDTV model, could not use this Promise set-top-box to get the full benefit of the meaning of the "digital-connectivity promise", when the controller is not able to receive the digital signal of the DVI output of the DirecTV STB, so it can be send to the HDTV for viewing when the content is protected with HDCP.</i></p>

<p><i>This leaves the traditional component analog connection as the only choice available for this owner of a $1700 promise box.  Such viewer of protected content could be subjected to possible copyright HD viewing restrictions when the content is sent via the component analog connection, if/when those restrictions are implemented.</i></p>

<p><br />
<h2>Motorola</h2><br />
For current models of the 6200 QAM Cable STBs family and the BMC9012/22 Media Centers STBs, please consult details and photographs included in the CES 2004 report.</p>

<p><u>CES 2005</u><br />
The company showed the following HD units:</p>

<p>Model 6412 </p>

<p>VOOM 550 and 580 models include below with Voom STBs</p>

<p>MOXI BMC9022 server, dual tuner networked 160GB DVR (records two cable programs, watch one of them), Docsis modem for interactivity and VOD, integrated DVD/CD player, NO ATSC tuner</p>

<p>MOXI MATE (client box for the above) suited with only L/R/V and RF output connections for remote TVs</p>

<p>Motorola informally declared that they have discontinued the ATSC tuner HDT100, the ATSC/QAM tuner HDT300, and the 160GB DVR ATSC/QAM tuner, all three HD-STBs the company introduced at CES 2004 one year ago.  Apparently, there was no market interest for those products.  Details and photographs can be obtained at the CES 2004 report.</p>

<p><br />
<h2>Norcent</h2><br />
ZAT-500HD	HD-STB w/ATSC OTA tuner, shown as prototype at CES 2005, TTM N/A, $ N/A</p>

<p><br />
<h2>RCA</h2><br />
Current models as detailed on the CES 2004 report:</p>

<p><i>ATSC10	$549, ATSC only, TTM 1Q03, RGBHV on 15-pin D-sub, HD component out, <u>DVI/HDCP, NO 1394</u>, coaxial and TosLink DD audio out, output resolution switchable to 1080i and 720p</p>

<p>ATSC11	$449, TTM summer 03, ATSC tuner only, no NTSC tuner <br />
ATSC21	$499,TTM summer 03 (was still unreleased by Nov 03), ATSC/NTSC tuners, $50 extra for NTSC tuner over the model ATSC11<br />
As per Press Release May 22, 03, both units above were reported to have DVI, 1080i/720p/480p/I output, simultaneous 480i, audio optical/coaxial outputs, component, RGB 15 pin D-sub adapter (unconfirmed)</p>

<p>DVR10 PVR	$449, TTM Summer 03, 80GB HDD, enough for 9 hrs of HDTV recording or 40 hrs SD, when connected to any two-way IEEE 1394 device such as the new line of RCA and RCA Scenium HDTV Sets, the RCA DVR10 can record and store HDTV.</p>

<p>Dish Network HD satellite STB<br />
HD6000	$492, TTM current, component, RGB, optical digital audio</p>

<p>(Announced at CEDIA Sep 03)<br />
DTC-210 	$600 (offered for preorder at $529), TTM 1Q04, DirecTV and ATSC tuner, DVI/HDCP, multiple output formats 1080i, 720p, 480p/i, component and 15 pin D-sub, simultaneous 480i, coaxial/optical DD audio outputs, integrated electronic guide</i><br />
<img src="/articles/images/mt/image082.jpg" alt="DTC-210" align="left"><img src="/articles/images/mt/image083.jpg" alt="DTC-210" align="left"><br clear=all></p>

<p><br />
<h2>Samsung</h2><br />
Mar 04<br />
SIR-TS360 DirecTV HD receiver, ATSC/NTSC tuners, 1080i/720p/480p/I selectable outputs, simultaneous 480i/HD outputs</p>

<p>SIR-T351 	HDTV tuner<br />
<img src="/articles/images/mt/image084.jpg" alt="SIR-T351" align="left">Jul 04 (2004 Samsung Line Show), $350, TTM Aug 04 ATSC/QAM cable on-the-clear, 1080i/720p/480p/I selectable outputs, simultaneous 480i/HD outputs, DVI, component, optical/coax dig audio connections</p>

<p>SIR-S4080R	DirecTV HD-STB tuner DVR, 80GB HDD, TTM Aug 04, 70 hrs of recording (SD), record two shows at the same time or watch one while recording another, pause live TV up to 30 minutes, up to 14 day advance program guide, optical digital audio out, dual USB 1.1</p>

<p>On June 2004, Samsung communicated their plan for their STBs to comply with the FCC mandate of Broadcast Flag.  The company informed that the FCC ruling would affect devices sold after July 1, 2005 as well as previous models, for which an upgrade will be needed, otherwise "Failure to upgrade your receiver with the broadcast flag standard may prevent you from fully experiencing DTV since you will not be able too receive protected content and may interfere with unprotected content as well; if the Broadcast Flag is broadcast the Samsung set top box could turn off and cycle on-off and nothing will be displayed until the upgrade is performed. If you are a DirecTV customer the upgrade will be handled automatically by DirecTV through your Satellite connection". </p>

<p>Instructions were provided to upgrade the following models: PRL-3100, SIR-T151, SIR-T165, SIR-TS160 (without DIRECTV activation), which could be obtained from Samsung's Website at <a href="/ntlinktrack.cgi?http://www.samsungusa.com/broadcastflag">www.samsungusa.com/broadcastflag</a></p>

<p><u>CES 2005</u><br />
Samsung showed several models for OTA, Cable, and DirecTV:</p>

<p>DirecTV model that DirecTV distributes:<br />
H10	HD/SD DirecTV / ATSC tuners, HDMI, component, optical/coax dig outputs</p>

<p>Open Cable HD STB with OCAP<br />
DCB-A800C	TTM 2Q05, ATSC/QAM Cable CARD tuners (dual each), OCAP 1.0 middleware, DVI, component, RGB, digital audio optical, 10/100Base T Ethernet, IEEE1394</p>

<p>Home AV Server Multi- Room Network<br />
160GB HDD DVR, ATSC/QAM tuners, DVD player, Internet access, content sharing with clients, DVI, component, RGB, optical/coaxial digital audio connections, USB 1.1, 10/100 Base T Ethernet, V.90 PSTN Modem, Ucentric middleware</p>

<p>MovieBeam Terrestrial VOD Movie Service and STB<br />
MTR-1120U		ATSC & NTSC tuners, VOD service receiver, storage capacity up to 100 movies, 10 movies updated every week, 160 GB HDD, CAS: Nagravision, digital audio optical out, USB 1.1, V.90 PSTN modem, service of VOD only in 3 cities by Buena Vista</p>

<p><br />
<h2>Scientific Atlanta</h2><br />
The following are the current models as detailed in the CES 2004 report:</p>

<p><img src="/articles/images/mt/image085.jpg" alt="Explorer 3250HD" align="left"><i>3250HD	$500 (as of Sep 03), rented by cable company, TTM 4Q02, DVI (was not activated as of Sep 03), <u>1394 optional</u>, component out, RGB adapter, selectable video resolution, USB port, AR control, coaxial digital audio out</i><br />
<br clear="all"><br />
<img src="/articles/images/mt/image086.jpg" alt="Explorer 3270HD" align="left"><i>3270HD	$500, TTM fall 03, 3rd generation STB, 64 and 256 QAM with a single tuner, two 1394, component and DVI 1.0 included, initially available at Best Buy, 720p/1080i, also by Cox cable, simultaneous HD with 480i for VCR, shows guide while smaller scaled window of current program could still show small letters, zoom and stretch functions from unit and remote.  Sale version of the 3250D.</i><br />
<br clear="all"><br />
<img src="/articles/images/mt/image087.jpg" alt="Explorer 8000HD" align="left"><i>Explorer 8000HD	Home Entertainment Center. Initially sold directly to cable operators, later available to retail distribution, dual 1394, PVR with several HDD options, DVI, component out, RGB adapter, selectable video resolution, optical digital audio out, USB port, AR control, in June 03 the unit was being tested by Cox, 1394 initially one way only, a firmware needs to be delivered to activate the STBs that have 1394 connections, voice over IP cable modem to facilitate voice/data/video.</i></p>

<p><i>Explorer 8300		TTM 3Q04, Multi-room system, mock-up shown at CES 2004, built upon the 8000HD model, PVR with USB for external additions of HDDs, up to 3 client STBs could be connected coaxially to this server, the clients could also be any older cable STB that the company could recycle back as a slave unit of the server (like the model 2100), each client could control one independent DVR session, and watch a different program with full forward, pause, etc. controls.</i></p>

<p><u>CES 2005</u><br />
Scientific Atlanta showed a new DVR model (# pending) expected for end-2005 that is able to write HD into a Hi Def DVD recorder incorporated into the unit.  </p>

<p>The HD-STB is a QAM Cable DVR and the DVD media <u>in HD is stored as a file copy format, the Hi Def DVD disc is playable only on the recording STB (or another STB of the same model)</u>, the DVD unit also records DVD-R/-RW that could be playable on other DVD players if CPRM permits it.  The STB will be made available to Cable companies only.</p>

<p>The model will have IEEE1394, 160GB DVR, records 8.5 GB on dual layer DVD, and will be a server piece connected to a home network via coax to other existing Scientific Atlanta STBs.  The company is still working in incorporating a Cable CARD slot into the HD-STB, which was missing at the CES demo.</p>

<p><br />
<h2>Sharp</h2><br />
<u>CES 2005</u><br />
Sharp showed two DVD-RW/-R HDD recorders<br />
DV-HRD200		400GB HDD for up to 34 hours of HD (or 390 hours of SD), HDD/DVD two way dubbing, DVD-RW/-R recording, iLink interface, triple digital tuner (terrestrial, BS and CS110) for Japan domestic DTV, enhanced DVD playback, compatibility with DVD+-RW and +-R</p>

<p>DV-HRD20		250GB HDD for up to 21 hours of HD (or 314 hours of SD), DVD-RW/-R recording, HDDD-DVD 2 way dubbing, iLink, triple digital tuners (terrestrial, BS/CS110) for Japan domestic DTV, enhanced DVD playback, compatibility with DVD+-RW and +-R</p>

<p><br />
<h2>Sony</h2><br />
Sony still mentioned the SAT-HD300 HD-STB at CES 2005.  Details included in the CES 2004 report. </p>

<p>In 2004, the company announced the following 2004/5 models:</p>

<p><u>HD QAM Cable STBs with DVR</u><br />
On Sony's press release of February 04, the company announced the future introduction (by fall 04) of two new Cable HD-STBs with DVR capabilities, featuring ATSC/NTSC/QAM CableCARD tuners implementing Sony Passage integrated decryption technology.  The boxes were said to be suited with HDMI/HDCP, Gemstar integrated EPG, component output, flexible AR settings, DD 5.1 w/optical audio out, USB data ports, and memory stick for JPEG and MPEG1, as follows:</p>

<p>DHG-HDD100 $700, TTM fall 04, 120GB HDD, 120 hours SD, 12 hours HD <br />
DHG-HDD200 $800, TTM fall 04, 250GB HDD, 200 hours SD, 25 hours HD</p>

<p>However, later, in August 04, Sony issued a different press release announcing the future introduction of other models, as follows:</p>

<p>DHG-HDD250 $800, TTM fall 04, 250GB HDD, 20 hours of HD recording<br />
DHG-HDD500 $1000, TTM fall 04, 500GB HDD (two 250GB HDDs), 60 hours of HD </p>

<p>At CES 2005, Sony has confirmed that they decided to replace the two original models (100 and 200) even before they were expected to appear in fall 04. </p>

<p>It is important to note that these HD-STBs have a connectivity limitation: they lack IEEE1394 Firewire&trade; input/outputs.  This means that a tuned/stored HD content would not be able to be output to a D-VHS recorder for HD tape archival, nor it could be part of digital networking of compressed HD video with other devices or displays.  </p>

<p>Additionally, such limitation does not comply with a specification requiring 1394 digital connectivity on Cable HD-STBs established in the plug-and-play agreement made by the Cable and Consumer Electronics industry, and approved by the FCC.  If you are interested in more details, this subject was covered on my article "HDTV Integrated Tuners and You" that appeared on the second issue of the HDTVetc magazine.</p>

<p><br />
<h2>Thomson</h2><br />
Jan05<br />
The company announced a new HD STB that will be released early 2005 for $300 to send HD video (MPEG-2, MPEG-4, XviD, WMV9) from a PC running XP or Windows 2000 (Mac OS X for 2Q05) to a TV using wireless technology, the STB is the Acoustic Research Digital Media Bridge receiver.  It is capable of also send pictures and audio (MP3, WMA, WAV) to units that support Universal Plug and Play (UPnP) in the wireless network.  The system can also send CinemaView movies (5000 in inventory) downloaded from the Internet service.  Content can be send from up to 3 PCs with Implicit Networks server software to a HDTV within the network.  The receiver has DVI.</p>

<p><br />
<h2>USDTV</h2><br />
OTA STB for their broadcast service and ATSC, component, optical audio out, USB, NO IEEE1394, TTM now, $200, or lease option of $20 initial fee and $20 x 12 months, and the STB is yours after that.  Plans for future DVR are being discussed.</p>

<p><br />
<h2>Viewsonic</h2><br />
On March 2004 Viewsonic announced the April availability of their new NextVision HD10 HDTV OTA and NTSC tuner, with component outputs and aspect ratio control, outputs 480i/0, 720p and 1080i, MSRP $400.</p>

<p>On September 2004 Viewsonic announced the model HD12 HD-STB, TTM Oct 04, $400, OTA ATSC/NTSC tuners, DVI/HDCP, HD component, VGA RGB.</p>

<p><br />
<h2>V, Inc</h2><br />
Current model.  As appeared in the CES 2004 report:<br />
<i><br />
Bravo HD1	$350, TTM Feb 04, ATSC/NTSC tuners, scale to 480p/720p/1080i over component output, component, digital audio coax, titanium finish, <u>NO DVI, NO 1394, NO VGA 15 D-sub outputs</u>, aspect ratio control (4:3, 1:6 letterbox, 16:9 full), simultaneous SD and HD outputs<br />
</i></p>

<p><br />
<h2>Voom</h2></p>

<p>Regarding HD-STBs, Voom still have their Motorola 550 and has not yet released the 580, both included on the CES 2004 report.</p>

<p>On a 2004 review of their STB, it was noted that if the service is discontinued the OTA tuner will no longer function.  Changing channels was very slow (7 seconds to lock into video/audio).  Voom is working on 4 to 5 seconds, still about twice DirecTV and E*.  Movies wider than 16x9 are panned and scanned to fill a 16x9 screen; VOOM is reexamining the policy.  VOOM does not have yet quality standards for its movie transfers.</p>

<p>Additional detail about the 580 model is below:</p>

<p>Developed by Motorola, channel oriented recording DVR with clients, TTM Mar 05 but without network capabilities until later, which will be upgrade it by software download, $N/A (although a year ago the company estimated the price to be competitive with the other DVR STBs), 250GB of HDD, client STBs will not be available immediately upon its release but later in summer of 2005, current Model 550 HD-STBs could perform as clients and will be able to see the menu of the 580 DVR server, upgrade path for existing customers is being discussed but no commitments were made. </p>

<p>MPEG-4 upgrade will be performed at field by inserting custom available modules (MPEG-4 card on the side door) when available.  Future improvements (MPEG-4 algorithm enhancements) can be downloaded via satellite.</p>

<p>The 580 has DVI and component YPbPr video HD outs, supports 1080i/720p/480p/480i resolution formats, tunes HD OTA with internal ATSC tuner, and has optical digital audio out, similar to the model 550, neither unit, as well as the thin future network client boxes, will have IEEE1394 outputs for D-VHS tape archival recording (at the CES 2004 Voom declared that the 580 would have IEEE1394).  </p>

<p>For more details regarding how the plans of these units evolved within the last year please consult the section of HD-STBs for VOOM in page 81 of the CES 2004 report.</p>

<p>The 550 originally offered as $750 when released in 4Q03 is now offered for $499 and includes the ATSC antenna/dish and installation.</p>

<p>Be sure that you read the next article in the series: High Definition DVD (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 19, 2005  5:22 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(224)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 224)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-10-hdtv-tuners-tuning-dvrs.php" type="text/javascript" charset="utf-8"></script>
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
