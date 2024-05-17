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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 667 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 667
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2007 HDTV Technology Review, Part 1: Introduction & TOC&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/08/2007-hdtv-technology-review-part-1-introduction-toc.php&amp;title=2007 HDTV Technology Review, Part 1: Introduction &amp; TOC">
		<span style="display:none">As with every year, this report reviews the state of HDTV technology for consumers, its implementation, government affairs, and the industry behind it.

This year, in addition to this report, I have produced a more comprehensive Industry Edition distributed by Display Search (almost 600 pages).  On this Consumer Edition, I concentrate most of the material on what the readers of the HDTV Magazine usually devote more interest to: HDTV hardware, especially TVs.

Additionally, to round up the presentation, I provide a brief review of the main subjects related to DTV.  However, due to space considerations, the full coverage of those subjects, such as digital connectivity, multi-channel audio for HD, content protection, satellite/cable/broadcast, IPTV, DTV implementation, etc. will be released in separate editions.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 667";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2007 HDTV Technology Review, Part 1: Introduction & TOC" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2007 HDTV Technology Review, Part 1: Introduction & TOC" />
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
	<title>HDTV Magazine - 2007 HDTV Technology Review, Part 1: Introduction & TOC</title>
	<meta name="keywords" content="def dvd, hdtv technology, blu ray, technology review, toshiba players, ces, hdtv, products, toshiba, dtv, dvd, chapter, digital, report, sony, industry, samsung, technology, technologies, hitachi, implementation, def, review, cable, jvc" />
	<meta name="description" content="As with every year, this report reviews the state of HDTV technology for consumers, its implementation, government affairs, and the industry behind it.

This year, in addition to this report, I have produced a more comprehensive Industry Edition distributed by Display Search (almost 600 pages).  On this Consumer Edition, I concentrate most of the material on what the readers of the HDTV Magazine usually devote more interest to: HDTV hardware, especially TVs.

Additionally, to round up the presentation, I provide a brief review of the main subjects related to DTV.  However, due to space considerations, the full coverage of those subjects, such as digital connectivity, multi-channel audio for HD, content protection, satellite/cable/broadcast, IPTV, DTV implementation, etc. will be released in separate editions." />
	<meta name="title" content="2007 HDTV Technology Review, Part 1: Introduction &amp; TOC" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/08/2007-hdtv-technology-review-part-1-introduction-toc.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=667', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/08/2007-hdtv-technology-review-part-1-introduction-toc.php">2007 HDTV Technology Review, Part 1: Introduction & TOC</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>August  9, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=288&category=Broadcast">Broadcast</a></b>, <b><a href="/category.php?id=328&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=331&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>, <b><a href="/category.php?id=363&category=Fiber/IPTV HDTV">Fiber/IPTV HDTV</a></b>
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
				<p class="editorial">This is the first in a series of articles taken from the <b>HDTV Technology Review 2007</b> by Rodolfo La Maestra, published in May 2007. If you are interested in purchasing the full version of this report, it is currently available for purchase from our <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.</p>

<p>HDTV Technology Review 2007</p>

<p>By Rodolfo La Maestra<br />
May 2007</p>

<p><b>Introduction</b></p>

<p>As with every year, this report reviews the state of HDTV technology for consumers, its implementation, government affairs, and the industry behind it.</p>

<p>This year, in addition to this report, I have produced a more comprehensive Industry Edition distributed by Display Search (almost 600 pages).  On this Consumer Edition, I concentrate most of the material on what the readers of the HDTV Magazine usually devote more interest to: HDTV hardware, especially TVs.</p>

<p>Additionally, to round up the presentation, I provide a brief review of the main subjects related to DTV.  However, due to space considerations, the full coverage of those subjects, such as digital connectivity, multi-channel audio for HD, content protection, satellite/cable/broadcast, IPTV, DTV implementation, etc. will be released in separate editions.</p>

<p>The review includes future products and technologies announced as of May 2007, with information supplied directly by manufacturers, or gathered at HDTV conferences, CEDIA, NAB, CEATEC, and the International CES (Consumer Electronics Show), where the industry also introduce innovations, prototypes, and technology statements.</p>

<p>If you are looking for a previously released product or technology that is not mentioned in this report, please consult the other annual reports available at no cost published by the HDTV Magazine:</p>

<p><a href="/reports/hdtv-technology-review.php">http://www.hdtvmagazine.com/reports/hdtv-technology-review.php</a></p>

<p>These reports also provide a historical background of government mandates, industry agreements, satellite/cable plans, and descriptions of the technologies introduced during the covered year, including some previously reported products<br />
<img src="/images/articles/hdtvtr2007/image001.jpg" align=left />that are current to facilitate reading and comparison analysis within the same document.</p>

<p>The group of reports can be used as a research tool for past, present, and future products and technologies, and to analyze the evolution of HDTV.</p>

<p>When applicable, a brief background is provided to give an historical perspective of a given subject before getting into the detail of the current year.</p>

<p>Products are mentioned highlighting the month of their introduction and future availability to provide a perspective of their maturity in the market.  CEDIA and CES announcements are highlighted within each manufacturer.</p>

<p>Most publications show current DTV products with basic specifications and do not analyze the market to guide consumers to help them make the right choice for their needs.  Hundreds of manufacturers and products are included in this report, with detailed specifications and features to facilitate comparisons with other models, brands, and technologies.</p>

<p><img src="/images/articles/hdtvtr2007/image002.jpg" align=right></p>

<p>However, this report is not only about products and technologies, there is abundant coverage in sections dedicated to standards, connectivity, government, IPTV, etc. that provide a broad picture of the history and implementation of HDTV beyond a TV set, unlike any other publication or book.</p>

<p>From the consumer point of view, many attend CES to plan future purchases and maybe start saving for products that could be released months or years later.  Some decide to rather buy now a current product because CES helped confirm that it might not be worth the wait.  This report helps consumers making those choices because of its full coverage of the subject.</p>

<p><img src="/images/articles/hdtvtr2007/image003.gif" align=left><br />
The report also highlights industry trends, the adoption (or abandoning) of H/DTV technologies, the remarkable growth of flat panel displays, the endurance of continued LCoS support, the 1080p Holy Grail, the CinemaScope implementations with new 1080p projectors and anamorphic lenses, the Hi-Def DVD format war, the oversize panel competition, 3-D, and the ED display technology in all its varieties (SED, NED, OLED, FED).</p>

<p>This report assumes that the reader has a basic understanding of H/DTV.  Certain technical information might seem overwhelming to readers that feel the need to understand the basics first.  The Glossary at the end of the report and tutorial articles at the HDTV Magazine are recommended:</p>

<p><a href="/articles/articles-author.php?id=16">http://www.hdtvmagazine.com/articles/articles-author.php?id=16</a></p>

<p>All types of H/DTVs and technologies are covered in this report: RPTV (rear projection TV), FP (front projectors), Direct-view (CRTs, CRPs, etc), Plasmas (PDP), DLP (Digital Light Processing), LCD (Liquid Crystal Display), LCoS (Liquid Crystal on Silicon, including JVC's D-ILA and Sony's SXRD), and the EDs displays mentioned above.</p>

<p><img src="/images/articles/hdtvtr2007/image004.jpg" align=right />This report also reviews DTV related equipment such as Hi-Def DVD for playback and recording, HD tuning set-top-boxes (STB) for small-dish satellite, digital cable, and over-the-air (OTA) w/antenna reception, HD DVRs (Digital Video Recorders), the implementation of digital video connectivity, etc.</p>

<p>The information about models, prices, and specifications has been researched and confirmed with product demonstrations, lab reviews, press releases, technical material, and manufacturer interviews at CES and other conferences.  Prices are quoted as MSRP and rounded to facilitate reading and quick comparisons; when unknown, TBA or TBD is generally used.  Product availability is stated as TTM (Time to Market).</p>

<p>As the industry grows in complexity, variety, and number of HD products, the effort to research, review, analyze, and compare products, added to a full H/DTV coverage at CES and other HDTV events, and issue final projections, is becoming an overwhelming task for one person year after year.</p>

<p><img src="/images/articles/hdtvtr2007/image005.jpg" align=left>In the past, many people referred to this effort as a &quot;CES report&quot;.  The truth is: <u>CES is important but is just one piece </u>of the industry perspective offered in this document.  Although I know in advance the technologies and products expected to appear at CES, the show permits me to eyewitness them and talk to the engineers that participated in their development.  No press release or magazine's new-product page can provide such transparent review, a unique and complete picture of the H/DTV industry.</p>

<p>Additionally, the effort for preserving a broad scope facilitates the linking of all the HD areas and allows for a deeper analysis and a wider perspective across manufacturers, technologies, and the industry in general.<br />
<br clear=all /><br />
Although considerable effort was made to consolidate and verify the correctness of <img src="/images/articles/hdtvtr2007/image006.jpg" align=left />the data included in the report, I cannot assume responsibility for omissions or errors.</p>

<p>Should you have any comments or questions, please contact me at:</p>

<p><a href="/about/contact.php?name=lamaestra">http://www.hdtvmagazine.com/about/contact.php?name=lamaestra</a></p>

<p>Thank you for your continued support and interest in my work.<br clear=all /></p>

<p>
Rodolfo La Maestra<br />
HDTV  Technology  Consulting<br />
Senior Technical Director<br />
<img border=0 width=163 height=20 src="/images/articles/hdtvtr2007/image007.jpg" alt="HDTV Magazine - Your Guide to High Definition Television">
</p>

<p><a href="http://www.hdtvmagazine.com/">http://www.hdtvmagazine.com/</a></p>

<p><br />
<B>Table of Contents</B></p>

<p><B>Chapter 1 - H/DTV Implementation</B><br />
<span style="margin-left:20px"></span>The Updated Transition Plan (2006 and 2007)<br />
<span style="margin-left:40px"></span>Approved New DTV Deadline<br />
<span style="margin-left:40px"></span>Approved Public Education / Emergency Program<br />
<span style="margin-left:40px"></span>Cable DTV Downconversion<br />
<span style="margin-left:40px"></span>Approved DTV Converter Box Budget for Subsidy<br />
<span style="margin-left:40px"></span>Converter Box Subsidy Program Updates<br />
<span style="margin-left:20px"></span>Integrated Tuner Mandate Update<br />
<span style="margin-left:40px"></span>Tuner-less DTVs<br />
<span style="margin-left:20px"></span>Analysis and Projections For 2007/8/9</p>

<p><B>Chapter 2 - DTV Standards</B><br />
<span style="margin-left:20px"></span>A-VSB - Advanced-Vestigial Side-Band<br />
<span style="margin-left:20px"></span>x.v.YCC<br />
<span style="margin-left:20px"></span>LG's MPH (Mobile-Pedestrian-Handheld)</p>

<p><B>Chapter 3 - Satellite, Cable, Broadcasting</B><br />
<B>Satellite</B><br />
<span style="margin-left:20px"></span>DirecTV<br />
<span style="margin-left:20px"></span>Dish Network (EchoStar)<br />
<span style="margin-left:40px"></span>Microsoft Partnership<br />
<B>Cable</B><br />
<span style="margin-left:40px"></span>DOCSIS 3.0<br />
<span style="margin-left:40px"></span>CES 2007<br />
<span style="margin-left:40px"></span>The CableCARD Implementation - Current Situation<br />
<span style="margin-left:40px"></span>CableCARD Implementation Analysis<br />
<span style="margin-left:40px"></span>Multi-Stream CableCARD<br />
<span style="margin-left:40px"></span>Cable STB Integration Ban<br />
<span style="margin-left:40px"></span>Congress Support for DCAS<br />
<span style="margin-left:40px"></span>FCC Approved DCAS - Some Integrated Ban Waivers<br />
<span style="margin-left:40px"></span>Switched Digital Video (SDV)<br />
<B>Broadcasting</B><br />
<span style="margin-left:40px"></span>3-in-One TV Tuner<br />
<span style="margin-left:40px"></span>Down-Conversion Proposal<br />
<span style="margin-left:40px"></span>Do You Know Where Your Recording is Tonight?<br />
<span style="margin-left:40px"></span>Broadcasting Industry Preparing for 1080p Production</p>

<p><B>Chapter 4 - Internet Protocol TV (IPTV)</B><br />
<span style="margin-left:20px"></span>Introduction<br />
<span style="margin-left:20px"></span>What Does IPTV Mean to You?<br />
<span style="margin-left:20px"></span>Different Methods of HDTV Over IP<br />
<span style="margin-left:20px"></span>Current/Planned IPTV Market Solutions</p>

<p><B>Chapter 5 - CRT, SED, OLED, FED, NED, 3DTV</B><br />
<B>The Technologies</B><br />
<span style="margin-left:20px"></span>LCD vs. CRT in Europe<br />
<span style="margin-left:20px"></span>3DTV<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:20px"></span>SED<br />
<span style="margin-left:20px"></span>Applied Nanotech<br />
<B>Display Manufacturers</B><br />
<span style="margin-left:20px"></span>Canon<br />
<span style="margin-left:40px"></span>SED<br />
<span style="margin-left:20px"></span>GTT<br />
<span style="margin-left:20px"></span>JVC<br />
<span style="margin-left:20px"></span>Hitachi<br />
<span style="margin-left:20px"></span>LG<br />
<span style="margin-left:20px"></span>Mitsubishi<br />
<span style="margin-left:40px"></span>OLED<br />
<span style="margin-left:20px"></span>Motorola<br />
<span style="margin-left:40px"></span>NED<br />
<span style="margin-left:20px"></span>Philips<br />
<span style="margin-left:20px"></span>RCA<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:40px"></span>OLED<br />
<span style="margin-left:40px"></span>FED<br />
<span style="margin-left:40px"></span>CRT<br />
<span style="margin-left:20px"></span>Sony<br />
<span style="margin-left:40px"></span>OLED<br />
<span style="margin-left:20px"></span>Thomson (RCA)<br />
<span style="margin-left:20px"></span>Toshiba<br />
<span style="margin-left:40px"></span>SED line</p>

<p><B>Chapter 6 - Digital Light Processing (DLP)</B><br />
<span style="margin-left:20px"></span>Texas Instruments<br />
<span style="margin-left:40px"></span>CES 2007<br />
<span style="margin-left:40px"></span>TVP9010 HDTV Processor<br />
<span style="margin-left:40px"></span>TVP9007 Converter Box<br />
<span style="margin-left:20px"></span>Akai<br />
<span style="margin-left:20px"></span>Barco<br />
<span style="margin-left:20px"></span>BenQ<br />
<span style="margin-left:20px"></span>Christie<br />
<span style="margin-left:20px"></span>Digital Projection International<br />
<span style="margin-left:20px"></span>Dwin<br />
<span style="margin-left:20px"></span>Hitachi<br />
<span style="margin-left:20px"></span>HP<br />
<span style="margin-left:20px"></span>InFocus<br />
<span style="margin-left:20px"></span>LG<br />
<span style="margin-left:20px"></span>Marantz<br />
<span style="margin-left:20px"></span>Mitsubishi<br />
<span style="margin-left:20px"></span>NEC<br />
<span style="margin-left:20px"></span>Nuvision<br />
<span style="margin-left:20px"></span>Optoma<br />
<span style="margin-left:20px"></span>Panasonic<br />
<span style="margin-left:20px"></span>Philips<br />
<span style="margin-left:20px"></span>Projectiondesign<br />
<span style="margin-left:20px"></span>Radio Shack<br />
<span style="margin-left:20px"></span>RCA (see Thomson)<br />
<span style="margin-left:20px"></span>Runco<br />
<span style="margin-left:20px"></span>SAGEM<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:20px"></span>Sanyo<br />
<span style="margin-left:20px"></span>Sharp<br />
<span style="margin-left:20px"></span>SIM2 USA<br />
<span style="margin-left:20px"></span>Thomson<br />
<span style="margin-left:20px"></span>Toshiba<br />
<span style="margin-left:20px"></span>Vidikron<br />
<span style="margin-left:20px"></span>Viewsonic<br />
<span style="margin-left:20px"></span>Vivitek<br />
<span style="margin-left:20px"></span>Yamaha<br />
<span style="margin-left:20px"></span>Zenith</p>

<p><B>Chapter 7 - Liquid Crystal on Silicon (LCoS)</B><br />
<span style="margin-left:20px"></span>Canon<br />
<span style="margin-left:20px"></span>Cinetron<br />
<span style="margin-left:20px"></span>DreamVison<br />
<span style="margin-left:20px"></span>ELCOS<br />
<span style="margin-left:20px"></span>Faroudja<br />
<span style="margin-left:20px"></span>Hitachi<br />
<span style="margin-left:20px"></span>JVC (D-ILA)<br />
<span style="margin-left:20px"></span>LG<br />
<span style="margin-left:20px"></span>Meridian-Faroudja<br />
<span style="margin-left:20px"></span>MicroDisplay Corporation<br />
<span style="margin-left:20px"></span>OMT<br />
<span style="margin-left:20px"></span>SONY (SXRD)<br />
<span style="margin-left:20px"></span>Syntax/Brillian</p>

<p><B>Chapter 8 - LCD Projection (FP and RPTV)</B><br />
<span style="margin-left:20px"></span>Canon<br />
<span style="margin-left:20px"></span>Epson<br />
<span style="margin-left:20px"></span>Hitachi<br />
<span style="margin-left:20px"></span>Mitsubishi<br />
<span style="margin-left:20px"></span>Panasonic<br />
<span style="margin-left:20px"></span>Sanyo<br />
<span style="margin-left:20px"></span>Sony<br />
<span style="margin-left:20px"></span>Toshiba<br />
<span style="margin-left:20px"></span>Viewsonic</p>

<p><B>Chapter 9 - Plasma Panels</B><br />
<span style="margin-left:20px"></span>Audiovox<br />
<span style="margin-left:20px"></span>Cinemateq<br />
<span style="margin-left:20px"></span>Daewoo<br />
<span style="margin-left:20px"></span>Dell<br />
<span style="margin-left:20px"></span>Dwin<br />
<span style="margin-left:20px"></span>Fujitsu / Hitachi<br />
<span style="margin-left:20px"></span>HISENSE<br />
<span style="margin-left:20px"></span>Hitachi/ Fujitsu<br />
<span style="margin-left:20px"></span>HP<br />
<span style="margin-left:20px"></span>LG<br />
<span style="margin-left:20px"></span>Marantz<br />
<span style="margin-left:20px"></span>Maxent<br />
<span style="margin-left:20px"></span>Mitsubishi<br />
<span style="margin-left:20px"></span>NEC<br />
<span style="margin-left:20px"></span>NIKADA<br />
<span style="margin-left:20px"></span>Norcent<br />
<span style="margin-left:20px"></span>Panasonic<br />
<span style="margin-left:20px"></span>Philips<br />
<span style="margin-left:20px"></span>Pioneer<br />
<span style="margin-left:20px"></span>Runco<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:20px"></span>Thomson<br />
<span style="margin-left:20px"></span>Toshiba<br />
<span style="margin-left:20px"></span>Vidikron<br />
<span style="margin-left:20px"></span>VIZIO Inc</p>

<p><B>Chapter 10 - LCD Panels</B><br />
<span style="margin-left:20px"></span>Akira<br />
<span style="margin-left:20px"></span>Asus<br />
<span style="margin-left:20px"></span>BenQ<br />
<span style="margin-left:20px"></span>Hisense<br />
<span style="margin-left:20px"></span>Hitachi<br />
<span style="margin-left:20px"></span>HP<br />
<span style="margin-left:20px"></span>H&B<br />
<span style="margin-left:20px"></span>Humax<br />
<span style="margin-left:20px"></span>JVC<br />
<span style="margin-left:20px"></span>LG<br />
<span style="margin-left:20px"></span>Maxent<br />
<span style="margin-left:20px"></span>Mitsubishi<br />
<span style="margin-left:20px"></span>NIKADA<br />
<span style="margin-left:20px"></span>Norcent<br />
<span style="margin-left:20px"></span>NUVISION<br />
<span style="margin-left:20px"></span>Panasonic<br />
<span style="margin-left:20px"></span>Philips<br />
<span style="margin-left:20px"></span>Prima<br />
<span style="margin-left:20px"></span>Proton<br />
<span style="margin-left:20px"></span>Proview<br />
<span style="margin-left:20px"></span>Runco<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:20px"></span>Sanyo<br />
<span style="margin-left:20px"></span>Sharp<br />
<span style="margin-left:20px"></span>Sony<br />
<span style="margin-left:20px"></span>Syntax/Brillian<br />
<span style="margin-left:20px"></span>Thomson (RCA)<br />
<span style="margin-left:20px"></span>Toshiba<br />
<span style="margin-left:20px"></span>VIZIO Inc<br />
<span style="margin-left:20px"></span>Vidikron<br />
<span style="margin-left:20px"></span>Viewsonic<br />
<span style="margin-left:20px"></span>Westinghouse</p>

<p><B>Chapter 11 - HDTV Tuners / DVRs</B><br />
<span style="margin-left:20px"></span>AMD<br />
<span style="margin-left:40px"></span>AverMedia<br />
<span style="margin-left:40px"></span>AVerTVHD MCE A180<br />
<span style="margin-left:40px"></span>AVerTVHD Hybrid Express card<br />
<span style="margin-left:40px"></span>AVerMedia AVerTVHD Combo PCI-E<br />
<span style="margin-left:40px"></span>AVerMedia AVerTVHD Hybrid USB<br />
<span style="margin-left:20px"></span>AutummWave<br />
<span style="margin-left:20px"></span>AV Toolbox<br />
<span style="margin-left:20px"></span>CodexNovus<br />
<span style="margin-left:20px"></span>Contemporary Research<br />
<span style="margin-left:20px"></span>Digeo<br />
<span style="margin-left:20px"></span>Digital Stream<br />
<span style="margin-left:20px"></span>DIRECTV<br />
<span style="margin-left:40px"></span>CES 2007<br />
<span style="margin-left:40px"></span>Sat-Go portable receiver<br />
<span style="margin-left:20px"></span>Dish Network<br />
<span style="margin-left:40px"></span>2006 line of MPEG-2/MPEG-4 models<br />
<span style="margin-left:40px"></span>CES 2007<br />
<span style="margin-left:20px"></span>Ezneo<br />
<span style="margin-left:20px"></span>Gefen<br />
<span style="margin-left:20px"></span>Hisense<br />
<span style="margin-left:20px"></span>HP<br />
<span style="margin-left:20px"></span>Humax<br />
<span style="margin-left:20px"></span>JVC<br />
<span style="margin-left:20px"></span>Key Digital<br />
<span style="margin-left:20px"></span>LG<br />
<span style="margin-left:20px"></span>MatrixStream IPTV Technologies<br />
<span style="margin-left:40px"></span>Deployment Diagram<br />
<span style="margin-left:20px"></span>MicroTune<br />
<span style="margin-left:20px"></span>MIT (Micro Image Technology)<br />
<span style="margin-left:20px"></span>Mitsubishi<br />
<span style="margin-left:20px"></span>Motorola<br />
<span style="margin-left:40px"></span>CES 2007<br />
<span style="margin-left:20px"></span>MovieBeam<br />
<span style="margin-left:20px"></span>Moxi<br />
<span style="margin-left:20px"></span>MyDTV45.com<br />
<span style="margin-left:20px"></span>Netgear<br />
<span style="margin-left:20px"></span>Onair Solution<br />
<span style="margin-left:20px"></span>Pace<br />
<span style="margin-left:20px"></span>PrimeDTV<br />
<span style="margin-left:20px"></span>Pro-Brand<br />
<span style="margin-left:20px"></span>PX Digital Multimedia<br />
<span style="margin-left:20px"></span>RCA<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:20px"></span>Scientific Atlanta<br />
<span style="margin-left:40px"></span>CES 2007<br />
<span style="margin-left:20px"></span>Sharp<br />
<span style="margin-left:20px"></span>Sony<br />
<span style="margin-left:20px"></span>Sylvania<br />
<span style="margin-left:20px"></span>TEAC<br />
<span style="margin-left:20px"></span>Thomson<br />
<span style="margin-left:40px"></span>Cable STB DCI9000<br />
<span style="margin-left:40px"></span>Satellite STB<br />
<span style="margin-left:40px"></span>IP DBI200<br />
<span style="margin-left:40px"></span>Triple Play Services over Multiple Network Types<br />
<span style="margin-left:20px"></span>Tivo<br />
<span style="margin-left:20px"></span>USDTV<br />
<span style="margin-left:20px"></span>UTStarcom<br />
<span style="margin-left:20px"></span>V,Inc<br />
<span style="margin-left:20px"></span>Viewsonic<br />
<span style="margin-left:20px"></span>Vizio<br />
<span style="margin-left:20px"></span>Voom<br />
<span style="margin-left:20px"></span>Winegard<br />
<span style="margin-left:20px"></span>Xceive<br />
<span style="margin-left:20px"></span>Zenith<br />
<span style="margin-left:20px"></span>Zoran</p>

<p><B>Chapter 12 - High Definition DVD</B><br />
<B>HD DVD and Blu-ray Formats</B><br />
<span style="margin-left:20px"></span>Background<br />
<span style="margin-left:20px"></span>Formats Reconciliation<br />
<span style="margin-left:20px"></span>Associations of the Formats<br />
<span style="margin-left:20px"></span>Formats - Companies Support</p>

<p><span style="margin-left:20px"></span>Universal Player<br />
<span style="margin-left:40px"></span>Samsung<br />
<span style="margin-left:40px"></span>LG<br />
<span style="margin-left:40px"></span>The Universal Confusion<br />
<span style="margin-left:40px"></span>We Do it For the Consumer</p>

<p><span style="margin-left:20px"></span>Player Interactivity<br />
<span style="margin-left:20px"></span>Content Protection for Hi Def DVD<br />
<span style="margin-left:20px"></span>AACS Down-Res<br />
<span style="margin-left:20px"></span>PVP-OPM<br />
<span style="margin-left:20px"></span>Gaming<br />
<span style="margin-left:40px"></span>How Much does the PS3 Actually Cost?<br />
<span style="margin-left:40px"></span>Cables for PS3<br />
<span style="margin-left:40px"></span>Console Comparative Speculations<br />
<span style="margin-left:40px"></span>Xbox 360<br />
<span style="margin-left:20px"></span>Hi-Def DVD Formats Specifications</p>

<p><span style="margin-left:20px"></span>Computing - Hi-Def Laptops<br />
<span style="margin-left:40px"></span>Toshiba<br />
<span style="margin-left:40px"></span>Dell<br />
<span style="margin-left:40px"></span>Fujitsu<br />
<span style="margin-left:40px"></span>HP<br />
<span style="margin-left:40px"></span>Samsung<br />
<span style="margin-left:40px"></span>Sony</p>

<p><span style="margin-left:20px"></span>Computer Drives<br />
<span style="margin-left:40px"></span>BenQ<br />
<span style="margin-left:40px"></span>Dell<br />
<span style="margin-left:40px"></span>NEC<br />
<span style="margin-left:40px"></span>Pioneer<br />
<span style="margin-left:40px"></span>Sony<br />
<span style="margin-left:40px"></span>Toshiba</p>

<p><span style="margin-left:40px"></span>CES 2007 and Late 2006 Computer Equipment Introductions.<br />
<span style="margin-left:60px"></span>Hitachi<br />
<span style="margin-left:60px"></span>HP<br />
<span style="margin-left:60px"></span>LaCie<br />
<span style="margin-left:60px"></span>LG<br />
<span style="margin-left:60px"></span>Niveus Media<br />
<span style="margin-left:60px"></span>Pioneer<br />
<span style="margin-left:60px"></span>Samsung<br />
<span style="margin-left:60px"></span>Sony<br />
<span style="margin-left:60px"></span>Toshiba</p>

<p><span style="margin-left:20px"></span>Parts and Chips<br />
<span style="margin-left:20px"></span>PC applications for BD<br />
<span style="margin-left:20px"></span>Discs<br />
<span style="margin-left:40px"></span>NME<br />
<span style="margin-left:40px"></span>Multi-layer Dual Optical Disc<br />
<span style="margin-left:40px"></span>Toshiba's Triple-layer Hybrid TWIN Disc Format<br />
<span style="margin-left:20px"></span>Recording Media for HD DVD and Blu-ray<br />
<span style="margin-left:40px"></span>Sony<br />
<span style="margin-left:40px"></span>TDK<br />
<span style="margin-left:20px"></span>HD DVD ROM<br />
<span style="margin-left:20px"></span>BD Discs<br />
<span style="margin-left:20px"></span>BD-ROM Pre-recorded Media<br />
<span style="margin-left:40px"></span>Studio Announcements<br />
<span style="margin-left:40px"></span>Format Launching<br />
<span style="margin-left:20px"></span>HD DVD Launch<br />
<span style="margin-left:20px"></span>Blu-ray Launch<br />
<span style="margin-left:40px"></span>Industry and Content Support - CES 2007<br />
<span style="margin-left:20px"></span>Blu-ray Presentation<br />
<span style="margin-left:20px"></span>HD DVD Presentation<br />
<span style="margin-left:20px"></span>Warner's Total Hi Def Disc (Dual Blue Laser Formats)<br />
<span style="margin-left:20px"></span>Format Market Penetration - CES 2007<br />
<span style="margin-left:20px"></span>Titles and Blu-ray Player Sales<br />
<span style="margin-left:20px"></span>Film Grain Added to HD DVD</p>

<p><span style="margin-left:20px"></span><B>HD DVD Players/Recorders</B><br />
<span style="margin-left:40px"></span>Alco<br />
<span style="margin-left:40px"></span>LG<br />
<span style="margin-left:40px"></span>Onkyo<br />
<span style="margin-left:40px"></span>RCA<br />
<span style="margin-left:40px"></span>Sanyo<br />
<span style="margin-left:40px"></span>Shinco Electronics<br />
<span style="margin-left:40px"></span>Toshiba<br />
<span style="margin-left:40px"></span>The Actual Value of the Player<br />
<span style="margin-left:40px"></span>Current Toshiba Players:<br />
<span style="margin-left:40px"></span>1080p Video Processing in Toshiba Players<br />
<span style="margin-left:40px"></span>1080p 24fps Output in Toshiba Players<br />
<span style="margin-left:40px"></span>Toshiba Plans for 1080p 24fps<br />
<span style="margin-left:40px"></span>New Toshiba Players<br />
<span style="margin-left:20px"></span><B>Blu-ray Players / Recorders</B><br />
<span style="margin-left:40px"></span>Hitachi<br />
<span style="margin-left:40px"></span>JVC<br />
<span style="margin-left:40px"></span>LiteOn<br />
<span style="margin-left:40px"></span>LG<br />
<span style="margin-left:40px"></span>Mitsubishi<br />
<span style="margin-left:40px"></span>Panasonic<br />
<span style="margin-left:40px"></span>Philips<br />
<span style="margin-left:40px"></span>Pioneer<br />
<span style="margin-left:40px"></span>Samsung<br />
<span style="margin-left:40px"></span>Sharp<br />
<span style="margin-left:40px"></span>Sony</p>

<p><span style="margin-left:20px"></span>Formats Implementation Issues<br />
<span style="margin-left:40px"></span>Blu-ray<br />
<span style="margin-left:40px"></span>HD DVD</p>

<p><span style="margin-left:20px"></span>Analysis for the Hi-Def DVD Adopter<br />
<span style="margin-left:40px"></span>Balancing Features<br />
<span style="margin-left:40px"></span>1080p Outputs<br />
<span style="margin-left:40px"></span>1080p24fps for Film Content Playback<br />
<span style="margin-left:40px"></span>The Format Choice - Survey Question<br />
<span style="margin-left:40px"></span>Audio Claims<br />
<span style="margin-left:40px"></span>Image Constraint Token (ICT) - Early Adopter Impact<br />
<span style="margin-left:40px"></span>ICT Token - An Issue Anytime any Place<br />
<span style="margin-left:40px"></span>ICT Token - Industry Impact<br />
<span style="margin-left:40px"></span>Hybrid Discs<br />
<span style="margin-left:40px"></span>Partial Implementation of Features</p>

<p><span style="margin-left:20px"></span>A Different View of Hi-Def DVD Booths at CES</p>

<p><B>Asia's Hi-Def DVD Challengers</B><br />
<span style="margin-left:20px"></span>China's EVD, HVD, and HDV<br />
<span style="margin-left:20px"></span>Taiwan's Forward Versatile Disc (FVD)<br />
<span style="margin-left:20px"></span>Versatile Multi-layer Disc - VMD Format</p>

<p><B>Chapter 13 - HD Video Processors</B><br />
<span style="margin-left:20px"></span>Engines for Video Processors<br />
<span style="margin-left:40px"></span>ABT<br />
<span style="margin-left:40px"></span>Gennum Corporation<br />
<span style="margin-left:40px"></span>Silicon Optix<br />
<span style="margin-left:60px"></span>Algolith<br />
<span style="margin-left:60px"></span>BenQ<br />
<span style="margin-left:60px"></span>Calibre<br />
<span style="margin-left:60px"></span>Cinetron<br />
<span style="margin-left:60px"></span>Denon<br />
<span style="margin-left:60px"></span>Digital Projection<br />
<span style="margin-left:60px"></span>Epson<br />
<span style="margin-left:60px"></span>JVC<br />
<span style="margin-left:60px"></span>Mitsubishi<br />
<span style="margin-left:60px"></span>NEC<br />
<span style="margin-left:60px"></span>Proview<br />
<span style="margin-left:60px"></span>Syntax<br />
<span style="margin-left:60px"></span>Toshiba<br />
<span style="margin-left:60px"></span>Yamaha<br />
<span style="margin-left:60px"></span>GEO chip<br />
<span style="margin-left:20px"></span>Video Processors<br />
<span style="margin-left:40px"></span>Algolith<br />
<span style="margin-left:40px"></span>Calibre<br />
<span style="margin-left:40px"></span>Digital Projection<br />
<span style="margin-left:40px"></span>DVDO<br />
<span style="margin-left:40px"></span>Faroudja<br />
<span style="margin-left:40px"></span>Gefen<br />
<span style="margin-left:40px"></span>Lumagen<br />
<span style="margin-left:40px"></span>NEC<br />
<span style="margin-left:40px"></span>Pixel Magic (Crystalio II)</p>

<p><B>Chapter 14 - HD Video Cameras</B><br />
<span style="margin-left:20px"></span>HDV Format<br />
<span style="margin-left:20px"></span>AVCHD Format<br />
<span style="margin-left:20px"></span>Ambarella<br />
<span style="margin-left:20px"></span>Canon<br />
<span style="margin-left:40px"></span>Professional Products<br />
<span style="margin-left:40px"></span>Consumer Camcorders<br />
<span style="margin-left:20px"></span>Hitachi<br />
<span style="margin-left:20px"></span>JVC<br />
<span style="margin-left:40px"></span>Consumer Products - CES 2007<br />
<span style="margin-left:40px"></span>Professional Products<br />
<span style="margin-left:20px"></span>Panasonic<br />
<span style="margin-left:40px"></span>Panasonic Broadcast<br />
<span style="margin-left:40px"></span>Consumer Products<br />
<span style="margin-left:20px"></span>Sanyo<br />
<span style="margin-left:20px"></span>Samsung<br />
<span style="margin-left:20px"></span>Sony<br />
<span style="margin-left:40px"></span>Sony High Performance Broadcast<br />
<span style="margin-left:40px"></span>Consumer Products</p>

<p><B>Chapter 15 - Screens and HT Equipment</B><br />
<span style="margin-left:20px"></span>Digital Innovations<br />
<span style="margin-left:20px"></span>Dnp<br />
<span style="margin-left:20px"></span>D-Box<br />
<span style="margin-left:20px"></span>Hillcrest Labs<br />
<span style="margin-left:20px"></span>Jasco GE<br />
<span style="margin-left:20px"></span>Lightscope<br />
<span style="margin-left:20px"></span>Optoma<br />
<span style="margin-left:20px"></span>Panamorph<br />
<span style="margin-left:20px"></span>Planar Systems<br />
<span style="margin-left:20px"></span>Sima<br />
<span style="margin-left:20px"></span>Stewart<br />
<span style="margin-left:20px"></span>VUTEC</p>

<p><B>Glossary of H/DTV Terms</B></p>

<p><B>About the Author</B></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>August  9, 2007  1:02 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(667)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 667)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/08/2007-hdtv-technology-review-part-1-introduction-toc.php" type="text/javascript" charset="utf-8"></script>
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
