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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1538 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1538
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php&amp;title=HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?">
		<span style="display:none">As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.  But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.  Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween.</span></a>
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

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1538";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?</title>
	<meta name="keywords" content="review high, technical review, blu ray, high def, def digest, buy, high, video, def, technical, digest, blu, ray, review, surround, content, able, movies, usb, should, neville, mpeg, even, uncompressed, pcm" />
	<meta name="description" content="As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.  But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.  Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween." />
	<meta name="title" content="HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1538', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php">HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October 30, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=417&category=High Definition Production">High Definition Production</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-31.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.&nbsp; But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.&nbsp; Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween.<br>
<br><strong>Top Ten Blu-ray movies for Halloween</strong><br>
<br><strong>10. Pan's Labyrinth (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000WSLAUO" id="ehh7">Buy now</a>)</strong><br>
Following a bloody civil war, young Ofelia enters a world of
unimaginable cruelty when she moves in with her new stepfather, a
tyrannical military officer. Armed with only her imagination, Ofelia
discovers a mysterious labyrinth and meets a faun who sets her on a
path to saving herself and her ailing mother. But soon, the lines
between fantasy and reality begin to blur, and before Ofelia can turn
back, she finds herself at the center of a ferocious battle between
good and evil.<br>
<ul>

<li>1080p VC-1 video</li>
<li>DTS HD Master Audio 7.1 surround track</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1181/panslabyrinth.html" id="bn2e">review</a> at High Def Digest<br>
</li></ul><strong>9. Zodiac: Director's Cut (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001HUHBAE" id="gr-q">Buy now</a>)</strong><br>
Closer in spirit to a police procedural than a gory serial-killer flick, David Fincher's <em>Zodiac</em>
provides a sleek, armrest-gripping re-invention of the crime film. It
surveys the investigation of the Zodiac killings that terrorized the
San Francisco Bay area in the late -60-early -70s; Zodiac not only
killed people, but cultivated a Jack the Ripper aura by sending icky
letters to the newspapers and daring readers to solve coded messages.<br>

<ul>
<li>1080p VC-1 video</li>
<li>Dolby Digital 5.1 surround track</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1636/zodiac_nl.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>8. Underworld</strong><strong> (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000TGJ80I" id="gr-q">Buy now</a>)<br>
</strong>In the Underworld, Vampires are a secret clan of modern aristocratic
sophisticates whose mortal enemies are the Lycans (werewolves), a
shrewd gang of street thugs who prowl the city's underbelly. Noone
knows the origin of their bitter blood feud, but the balance of power
between them turns even bloodier when a beautiful young Vampire warrior
and a newly-turned Lycan with a mysterious past fall in love. Kate
Beckinsale and Scott Speedman star in this modern-day, action-packed
tale of ruthless intrigue and forbidden passion ­ all set against the
dazzling backdrop of a timeless, Gothic metropolis.<br>

<ul>
<li>1080p AVC MPEG-4 video</li>
<li>uncompressed PCM 5.1 surround track<br>
</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/996/underworld.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>7. The Orphanage </strong><strong>(<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
In
Newline's The Orphanage a woman discovers dark secrets hidden
within her cherished childhood home.&nbsp; The supernatural drama is the
feature film debut of acclaimed young Spanish director
Juan Antonio Bayona. A superbly atmospheric and emotionally powerful
tale of love, loss and guilt.&nbsp; There are a few gory make-up effects,
but Bayona mostly preys on our fear of the unknown to craft a
first-rate fright fest.<br>

<ul>
<li>1080p VC-1 video</li>
<li>DTS HD Lossless Master Audio 7.1 (Spanish)</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/446/orphanage.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>6. I Am Legend</strong><strong> (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
Robert Neville is a brilliant scientist, but even he could not contain
the terrible virus that was unstoppable, incurable, and man-made.
Somehow immune, Neville is now the last human survivor in what is left
of New York City and maybe the world. For three years, Neville has
faithfully sent out daily radio messages, desperate to find any other
survivors who might be out there. But he is not alone. Mutant victims
of the plague, The Infected, lurk in the shadows...watching
Neville's every move...waiting for him to make a fatal mistake.
Perhaps mankind's last, best hope, Neville is driven by only one
remaining mission: to find a way to reverse the effects of the virus
using his own immune blood. But he knows he is outnumbered...and
quickly running out of time.<br>

<ul>
<li>1080p VC-1 video</li>
<li>Dolby TrueHD 5.1 Surround</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1336/iamlegend.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>5. The Descent (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
On an annual extreme outdoor adventure, six women meet in a remote part
of the Appalachians to explore a cave hidden deep in the woods. Far
below the surface of the earth, disaster strikes when a rock fall
blocks their exit and there's no way out. The women push on, praying
for another exit, but there is something else lurking under the earth.
The friends are now prey, forced to unleash their most primal instincts
in an all-out war against an unspeakable horror - one that attacks
without warning, again and again and again.<br>
<ul>
<li>1080p AVC MPEG-4 video</li>

<li>uncompressed PCM 6.1 surround mix</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/463/descent.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>4. Disturbia</strong><strong> (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000RO6K80" id="ew9t">Buy Now</a>)</strong><br>
After his father’s accidental death, Kale remains
withdrawn and troubled. When he lashes out at a well-intentioned but
insensitive teacher, he finds himself under a court-ordered house
arrest. His mother continues to cope, working extra shifts to support
herself and her son, as she tries in vain to understand the changes in
his personality. His interests turn outside the windows of his suburban
home toward those of his neighbors, including a mutual attraction to
the new girl next door. Together, they begin to suspect
that another neighbor is a serial killer. Are their suspicions merely
the product of Kale’s cabin fever and vivid imagination? Or have they
unwittingly stumbled across a crime that could cost them their lives?<br>
<ul>
<li>1080p AVC MPEG-4 video</li>

<li>DTS 6.1 Surround-ES and Dolby Digital 5.1 Surround EX</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/939/disturbia.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>3. Monster House</strong><strong>  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000IFRT38" id="ew9t">Buy Now</a>)</strong><br>
Even for a 12-year old, D.J. Walters has a particularly overactive
imagination. He is convinced that his haggard and crabby neighbor
Horace Nebbercracker, who terrorizes all the neighborhood kids, is
responsible for Mrs. Nebbercracker's mysterious disappearance. Any toy
that touches Nebbercracker's property, promptly disappears, swallowed
up by the cavernous house in which Horace lives. D.J. has seen it with
his own eyes! But no one believes him, not even his best friend,
Chowder. What everyone does not know is D.J. is not imagining things.
Everything he's seen is absolutely true and it's about to get much
worse than anything D.J could have imagined.<br>
<ul>
<li>1080p MPEG-2 video</li>

<li>uncompressed PCM 5.1 surround
  mix</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/186/monsterhouse.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>2. Sweeney Todd: The Demon Barber of Fleet Street  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B001BN1ZHW" id="ew9t">Buy Now</a>)</strong><br>
After years of rumors, it turns out that Tim Burton was the perfect visionary to film <em>Sweeney Todd: The Demon Barber of Fleet Street</em>,
Stephen Sondheim's Broadway masterpiece, and the result is a macabre
and moving musical movie as enthralling as anything Burton has ever
done. The show's mix of gothic horror, Grand Guignol, <em>very</em> dark
humor, and witty and beautiful music never was the stuff of traditional
musical comedy, but it's a powerful work, and perhaps the richest of
the late 20th century.<br>

<ul>
<li>1080p VC-1 video</li>
<li>lossless Dolby TrueHD 5.1</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1603/sweeneytodd2007.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>1. The Shining </strong><strong>  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000UJ48WC" id="ew9t">Buy Now</a>)</strong><br>
?Heeeeere?s Johnny!? In a macabre masterpiece adapted from Stephen
King?s novel, Jack Nicholson falls prey to forces haunting a snowbound
mountain resort with a macabre history. Kubrick's <em>The Shining</em> gets under your skin and chills your bones; it stays with you, inhabits you, haunts you. And there's no place to hide.

<ul>
<li>1080p VC-1 video</li>
<li>uncompressed PCM 5.1 Surround mix<br>
</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/325/shining1980.html" id="bn2e">review</a> at High Def Digest</li></ul><br>
<br><strong>Is Blu Ray a Temporary Platform?</strong>
<div><br>
<strong></strong></div>
<div> We received an email from Brad in Festus MO with a link to a CNET
article that suggests that the Playstation 4 will not have a Blu-ray
drive (<a href="http://news.cnet.com/8301-13506_3-10042820-17.html" target="_blank" title="Why the Playstation 4 won't have Blu-ray">Why the Playstation 4 won't have Blu-ray</a>).
One reason for this assertion is that technology is moving so fast that
there isn't enough time for Blu Ray to take a strong hold before a
better technology makes Blu-ray obsolete. So for today we would like to
discuss this interesting idea.</div>

<div>&nbsp;</div>
<div><strong>Reasons for Blu-ray's Demise:</strong></div>
<div>
<ol>
<li>More
convenient to download HD content then go out and buy or rent. Its safe
to say that in the future we will have more bandwidth than we have now.
Its not unrealistic that we will be able to download HDX quality movies
in less than 30 minutes. We certainly will be able to start watching
within five minutes.</li>
<li>There will be a storage breakthrough that
will give us 25 or 50GB on a USB stick. Just two years ago no one would
have believed that you can store 8 GB on a USB key. Today you can buy a <a href="http://www.htguys.com/shop.php?id=B000TXEE14" target="_blank" title="8GB USB Stick for less than $25">8GB USB Stick for less than $25</a>!
Soon we will have USB 3.0 that not only increases the capacity but also
the data rate. USB 3.0 will have a 4.8 Gbps data rate so copying files
to the drive will not take forever.</li>
<li>Portability. With HD movies
on a stick you will be able to take you movies on the go. We predict
that there will be mobile entertainment systems that will be able to
receive the USB stick and play the contents. Likewise we feel that
future iPods will store HD versions of movies and be able to down
convert on the fly so that legacy devices with A/V inputs will still be
usable.&nbsp;</li>
<li>Studio Support - This is the most pie in the sky!
Studios will realize that doing away with all the packaging will
greatly increase their profit and they will fully support downloadable
content with no restrictions. They will also have two types of content,
free with ads included, and no ads but you have to pay for it.</li>
<li>Interactive
Content - BD live can still work in this scenario. There is no reason
why computers or other players can't access the Internet and provide a
dynamic experience.</li></ol>

<div>&nbsp;</div>
<div><strong>Our hope for the future:</strong></div>
<div>We'd
like to see a HTPC that is more like a DVR. It should be able to
download content but also have a tuner built in. Recorded and
downloaded movies should be transportable to a portable device in full
HD. In effect, the portable device should act like a VHS cassette tape.
If I have rights to the content I should be able to connect it to a
friends device and play&nbsp;the&nbsp;content. For universal access the device
should be able to output AV through RCA cables for playback on older
legacy type of devices. This ends up bringing the
Video&nbsp;Cassette&nbsp;Recorder into the 21st century. DVRs are great, but its
too hard to take your recorded content with you!</div>
<div>&nbsp;</div>
<div>&nbsp;</div></div><br>
<br><br>

				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October 30, 2008 11:38 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1538)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1538)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php" type="text/javascript" charset="utf-8"></script>
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
