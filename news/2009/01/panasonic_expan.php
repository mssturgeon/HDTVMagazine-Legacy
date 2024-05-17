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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1631 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1631
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-its-award-winning-line-of-vierar-hdtvs.php&amp;title=Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs">
		<span style="display:none">Panasonic Corporation of North America (NYSE:PC) , the industry leader in high definition Plasma and LCD televisions, introduced its expanded line of VIERA HDTVs at the 2009 Consumer Electronics Show. Building upon its award winning portfolio of high definition televisions, Panasonic will broaden its offering in 2009 by introducing new screen sizes in both Plasma and LCD lines with a new 54-inch class plasma and 19-inch class LCD. In addition, VIERA will extend its unique technology features, such as the acclaimed VIERA CAST(R) web menu, to additional model lines.

VIERA CAST, originally launched in 2008, will broaden its entertainment offering with the addition of...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1631";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs" />
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
	<title>HDTV Magazine - Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs</title>
	<meta name="keywords" content="viera link, contrast ratio, black panel, viera image, image viewer, viera, resolution, link, panel, inch, viewer, contrast, ratio, panasonic, image, black, series, hdmi, field, drive, sub, infinite, inputs, native, class" />
	<meta name="description" content="Panasonic Corporation of North America (NYSE:PC) , the industry leader in high definition Plasma and LCD televisions, introduced its expanded line of VIERA HDTVs at the 2009 Consumer Electronics Show. Building upon its award winning portfolio of high definition televisions, Panasonic will broaden its offering in 2009 by introducing new screen sizes in both Plasma and LCD lines with a new 54-inch class plasma and 19-inch class LCD. In addition, VIERA will extend its unique technology features, such as the acclaimed VIERA CAST(R) web menu, to additional model lines.

VIERA CAST, originally launched in 2008, will broaden its entertainment offering with the addition of..." />
	<meta name="title" content="Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-its-award-winning-line-of-vierar-hdtvs.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1631', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-its-award-winning-line-of-vierar-hdtvs.php">Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs</a></td>
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
				<p class="prtitle">Panasonic Expands Its Award Winning Line of VIERA(R) HDTVs</p>

<center><i>Enhanced Features and Screen Sizes, Brighter Panels, Deeper Blacks And Full-Time 1080 TV Lines of Motion Picture Resolution Highlight 2009 VIERA Plasma and LCD HDTV Product Lines</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/ </B>-- Panasonic Corporation of North America (NYSE:PC) , the industry leader in high definition Plasma and LCD televisions, introduced its expanded line of VIERA HDTVs at the 2009 Consumer Electronics Show. Building upon its award winning portfolio of high definition televisions, Panasonic will broaden its offering in 2009 by introducing new screen sizes in both Plasma and LCD lines with a new 54-inch class plasma and 19-inch class LCD. In addition, VIERA will extend its unique technology features, such as the acclaimed VIERA CAST(R) web menu, to additional model lines.</p>

<p>VIERA CAST, originally launched in 2008, will broaden its entertainment offering with the addition of Amazon's VOD streaming video service. Panasonic also extended the prestigious THX(R) Certified Display to three product lines. With a continuing emphasis on improving the television's performance and reducing the impact on the planet's carbon footprint, Panasonic's 2009 line of VIERA HDTVs have improved their energy efficiency vs. last year's models, while also improving the overall picture performance of the HDTVs.</p>

<p>Year after year, Panasonic has been dedicated to bringing new picture improving technologies to HDTVs and 2009's line-up continues that trend. With the digital transition on the horizon and an increased awareness among consumers of high definition, consumers now demand blacker blacks, faster response times and the billions of colors that Panasonic VIERA HDTVs deliver.</p>

<p>In 2008, Panasonic offered one VIERA HDTV series that had innovative internet enabled VIERA CAST feature. In 2009, it will be extended to three series - The Z1 series, the V10 series and the G10 series. In addition to the continuation of such favorite entertainment sites as Google's YouTube(TM), Picasa(TM) Web Album, Bloomberg and weather service; a consumer will now have an access to HD movie rental via Amazon Video-on-Demand. Panasonic has also improved VIERA CAST interface by implementing quick keyword input (like a cell phone) to help retrieve favorite content faster and easier. Panasonic HDTVs VIERA Link - a technology that utilizes HDMI-CEC (Consumer Electronics Control) and allows a consumer to operate all VIERA Link(TM) compatible A/V components using only the TV's remote control and helpful on-screen menus. In addition to operating a VIERA HDTV, video source (Blu-ray and DVD player) and home theater receiver; VIERA Link permits operation of a network camera, ideal for monitoring of a home/nursery*. A VIERA Link capable Network Camera BL-C210A can be connected to a VIERA HDTV and controlled via VIERA Link remote. Users can then watch their child, pet and/or property by installing the camera and networking to the VIERA HDTV via Ethernet cable. The BL-C210A will be available in the United States in the summer of 2009.</p>

<p>As in 2008, all Panasonic VIERA HDTVs feature VIERA Image Viewer(TM) a function for easy viewing of digital still photos and in some models, the ability to play back AVCHD video recorded on SD card.</p>

<p>For 2009, VIERA Plasma HDTVs are rated, as in 2008, to have a lifespan of 100,000 hours. This is more than 30 years of viewing when watched for 8 hours a day, before the TV reaches half brightness. To further improve picture quality, VIERA HDTVs feature some breakthrough consumer innovations like 600Hz Sub-field Drive and Infinite Black panel with improved contrast ratios. In 2008, Panasonic offered VIERA HDTV models that were THX certified and in 2009, the prestigious certification will be extended to 8 VIERA models (The V10 series and the G10 series). THX Ltd. works closely with Panasonic from initial product design phase to the final product rollout in the market. THX certification ensures that each display can present all HD and standard definition content to the maximum resolution with accurate color and luminance levels.</p>

<p>Panasonic's commitment to improving the environment was confirmed this year as all of its 2009 VIERA HDTVs qualified for Energy Star certification. As in the past, VIERA Plasma HDTVs are lead and mercury free.</p>

<p>"Panasonic's superior quality, connectivity and service continues to distinguish the VIERA line," said Bob Perry, Executive Vice President, Panasonic Consumer Electronics Company. "Not only have our VIERA HDTVs received critical acclaim from consumer and trade publications, but Panasonic has maintained the market lead for the majority of the last three years. Panasonic has never rested on its laurels and the 2009 VIERA line continues that thinking. This year Panasonic has created the 'Neo-PDP' line with a brighter panel, double luminance efficiency, deeper blacks with improved contrast ratio and 1080 TV lines of Moving Picture Resolution. Our corporate philosophy of Ideas for Life is borne out by our attention to the consumer- to provide the ultimate entertainment experience in an easy to use format. That is why we have expanded our VIERA CAST feature and continued to improve the functionality of VIERA Link."</p>

<h2>PANASONIC VIERA Plasma HDTV</h2>

<p><B>Z1 Series</B></p>

<p>The flagship VIERA plasma in 2009 is the Z1 series, with a revolutionary one inch thin panel design and Wireless HD connectivity to deliver the ultimate sleek, uncluttered HDTV viewing experience. Also included on the VIERA Z1 is VIERA CAST web menu with the new streaming HD movie rental capability via Amazon Video-on-Demand. The Neo PDP design of the VIERA Z1 produces a brighter picture, deeper blacks, improved native contrast ratio (40,000:1) and Full-Time 1080 TV lines of motion resolution. Other Z1 model features include; 1080p resolution, a THX Certified Display; an Infinite Black panel; 600Hz Sub-field Drive; and VIERA Link, and VIERA Image Viewer for playing back digital still images and AVCHD videos recorded on SD Memory Cards. The Z1 series will be available in the summer of 2009 in the new TC-54Z1, 54-inch class screen size (54" measured diagonally).</p>

<p><br />
<B>V10 Series</B></p>

<p>The VIERA V10 series are slim, 2-inch thin plasma HDTVs with built-in tuners. The V10 series offer such cutting edge innovations as Digital Cinema Color(TM) which helps to deliver all movie-essential colors, full THX Display certification, and the VIERA CAST web menu with Amazon Video on Demand services. The Neo PDP design of the V10 series features 1080p resolution; deeper blacks, improved native contrast ratio of 40,000:1; Full-Time 1080 TV lines of motion resolution; an Infinite Black panel; 600Hz Sub-field Drive; VIERA Link, and VIERA Image Viewer for playing back digital still images and AVCHD videos recorded on SD Memory Cards. The V10 series is available in a 65-inch class (64.8" measured diagonally), 58-inch class (58" measured diagonally), 54-inch class (54" measured diagonally), and a 50-inch class (49.9" measured diagonally) screen size. The 50-inch model with one-sheet-of-glass design will be available in May 2009 and the remaining models will be available in the summer of 2009.</p>

<p><br />
<B>G10 Series</B></p>

<p>The VIERA G10 Plasma series include features like the VIERA CAST web menu, a THX certified Display, and VIERA Image Viewer for playing back digital still images and AVCHD videos recorded on SD Memory Cards. The NEO PDP design of the G10 series also offers 1080p resolution, deeper blacks, improved native contrast ratio of 40,000:1, Full-Time 1080 TV lines of motion resolution, an Infinite Black panel, 600Hz Sub-field Drive, as well as Game Mode and VIERA Link. The G10 series will be available in a 54-inch class (54" measured diagonally), 50-inch class (49.9" measured diagonally), 46-inch class (46" measured diagonally), and 42-inch class (41.6" measured diagonally) screen size. The 42-inch, 46-inch and 50-inch HDTVs will be available in March 2009, while the 54-inch model will be available in May 2009.</p>

<p><br />
<B>S1 Series</B></p>

<p>The VIERA S1 Plasma series offers a broad range of HDTVs which also represent the first VIERA plasma series to feature the new NEO PDP designs. Key features of the S1 series include Full-Time 1080 TV lines of motion resolution, which eliminate traditional HDTV motion blur. The S1 series also feature THX certified displays together with VIERA Image Viewer. Other features include 1080p resolution; a native contrast ratio of 40,000:1; an Infinite Black panel; 600Hz Sub-field Drive; and a Game Mode. The S1 series will compliment the V10 65" screen size with its own 65-inch class (64.7" measured diagonally) product. Other S1 sizes include a 58-inch class (58" measured diagonally), a 54-inch class (54" measured diagonally), a 50-inch class (49.9" measured diagonally), a 46-inch class (46" measured diagonally), and a 42-inch class (41.6" measured diagonally). The 42-inch, 46-inch and 50-inch HDTVs will be available in March 2009 while the 54-inch will be available in the summer of 2009.</p>

<p><br />
<B>X1 Series</B></p>

<p>Panasonic's focus in 2009 continues to be on high definition 1080p HDTVs but the VIERA X1 series represent a line of 720p plasma HDTVs which help Panasonic deliver on its promise to satisfy consumer demands for differing HDTV resolution options. The X1 VIERA HDTVs offer stunning picture performance with a 600Hz Sub-field Drive that delivers razor-sharp motion focus, VIERA Image Viewer for sharing digital photos with friends and family, and VIERA Link control of all compatible A/V home entertainment components via a single remote. The X1 series also feature improved native contrast ratio of 30,000:1, an Infinite Black panel, and a Game Mode. The VIERA X1 plasmas are available in a 50-inch class (49.9" measured diagonally) and 42-inch class (41.6" measured diagonally).</p>

<h2>Panasonic VIERA LCD HDTV</h2>

<p>In 2009, Panasonic will continue to demonstrate its strong and growing commitment to the LCD marketplace by more than doubling the number of its 2009 VIERA LCD model offerings (12 models, up from 5 in 2008), introducing a new screen size (the 19-inch class X1 series), and launching an innovative, new iPod/HDTV entertainment solution.</p>

<p><br />
<B>G1 Series</B></p>

<p>Panasonic's top-of-the-line LCD is the VIERA G1 series featuring the 1080p TC-L37G1, 37-inch class (37" measured diagonally) TV and the 720p TC-L32G1, 32-inch class (31.5" measured diagonally) model. Both TVs offer 120Hz Motion Picture Pro3 technology that ensures crisp, focused images for sports, dramatic action, and all other fast-moving scenes; an IPS Alpha Display Panel that deliver a 178 degree wide viewing angle and bright, clear images from any location in the room; and VIERA Image Viewer that provides the consumer with an easy way to view and share their digital photos via the TV's built-in SD card reader. The G1 LCD's also feature VIERA Link(TM); a PC input; Game mode, a 20:000:1 contrast ratio; 3 HDMI inputs, and a swivel base and narrow bezel design. The G1 series will be available in April 2009.</p>

<p><br />
<B>S1 Series</B></p>

<p>The VIERA LCD S1 series, available in March 2009, features two screen sizes with 1080p resolution - the 32-inch class (31.5" measured diagonally) and 37-inch class (37" measured diagonally) models. These two televisions feature Motion Focus technology; IPS Alpha Panel with 178% viewing angle; the VIERA Image Viewer function; VIERA Link; 15,000:1 native contrast ratio; PC Input, 3 HDMI inputs and Game mode.</p>

<p><br />
<B>X1 Series</B></p>

<p>Panasonic's 720p VIERA X1 LCD series introduces both an iPod entertainment Kit and the new 19-inch class (19" measured diagonally) screen size to the LCD family. There are three more screen sizes in this line - 26-inch class (26" measured diagonally), 32-inch class (31.5" measured diagonally) and 37-inch class (37" measured diagonally). The iPod(R) entertainment kit allows consumer to enjoy their iPod music and videos on the X1 series' high definition screen and provides the unique convenience of controlling the playback of iPod content using only the VIERA's remote control. Other X1 features includes VIERA Image Viewer; VIERA Link(TM); a PC input; a Game mode; a 12000:1 contrast ratio, and 3 HDMI inputs on the 32" and 37" screen sizes. The 37-inch and 32-inch will be available in March 2009, 26-inch in April and the 19-inch in August.</p>

<p><br />
<B>About Panasonic Consumer Electronics Company</B></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), a market and technology leader in High Definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Panasonic Corporation (NYSE:PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations. Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. Information about Panasonic products is available at www.panasonic.com. Additional company information for journalists is available at www.panasonic.com/pressroom.</p>

<pre>  Model      Features                         Availability

<p>  TC-P54Z1   Neo PDP; 1080p Resolution;       Summer, 2009<br />
             Wireless HD (TM)Connectivity;<br />
             thin 1" Design Panel and Tuner<br />
             Box configuration; VIERA<br />
             CAST(TM); THX(R) Certified<br />
             Display; 600Hz Sub-field<br />
             Drive; VIERA Image<br />
             Viewer(TM) with AVCHD<br />
             Playback; VIERA Link(TM);<br />
             Contrast Ratio: Native<br />
             40,000:1/Infinite Black<br />
             Panel; 24P Cinematic<br />
             Playback, 4 HDMI inputs,<br />
             Swivel Base<br />
  TC-P65V10  Neo PDP; 1080p Resolution;       August, 2009<br />
             Digital Cinema Color;<br />
             THX(R) Certified Display;<br />
             VIERA CAST(TM); VIERA Image<br />
             Viewer(TM)  with AVCHD<br />
             Playback; VIERA Link(TM);<br />
             600Hz Sub-field drive;<br />
             24P Cinematic Playback, 4<br />
             HDMI inputs; Contrast<br />
             Ratio: Native 40,000:1<br />
             Infinite Black Panel<br />
  TC-P58V10  Neo PDP; 1080p Resolution;       August, 2009<br />
             Digital Cinema Color;<br />
             THX(R) Certified Display;<br />
             VIERA CAST(TM); VIERA Image<br />
             Viewer(TM)  with AVCHD<br />
             Playback; VIERA Link(TM);<br />
             600 Hz Sub-field drive;<br />
             24P Cinematic Playback, 4<br />
             HDMI inputs; Contrast<br />
             Ratio: Native<br />
             40,000:1/Infinite Black<br />
             Panel<br />
  TC-P54V10  Neo PDP; 1080p Resolution;       June, 2009<br />
             Digital Cinema Color;<br />
             THX(R) Certified Display;<br />
             VIERA CAST(TM); VIERA Image<br />
             Viewer(TM)  with AVCHD<br />
             Playback; VIERA Link(TM);<br />
             600Hz Sub-field Drive;<br />
             24P Cinematic Playback, 4<br />
             HDMI inputs; Contrast<br />
             Ratio: Native 40,000:1/<br />
             Infinite Black Panel<br />
  TC-P50V10  Neo PDP; 1080p Resolution;       June, 2009<br />
             Digital Cinema Color;<br />
             THX(R) Certified Display;<br />
             VIERA CAST(TM); VIERA Image<br />
             Viewer(TM)  with AVCHD<br />
             Playback; VIERA Link(TM);<br />
             600Hz Sub-field Drive;<br />
             24P Cinematic Playback, 4<br />
             HDMI inputs; Contrast<br />
             Ratio: Native 40,000:1<br />
             Dynamic 1,000,000:1/Infinite<br />
             Black Panel, 2" Design<br />
             with Single-Sheet-of-Glass,<br />
             Swivel Base.<br />
  TC-P54G10  Neo PDP; 1080p Resolution;       May, 2009<br />
             VIERA CAST(TM); VIERA Link(TM);<br />
             THX(R) Certified Display;<br />
             VIERA Image Viewer(TM) with<br />
             AVCHD Playback; 600Hz<br />
             Sub-field Drive; Game<br />
             mode; 3 HDMI inputs,<br />
             Contrast Ratio: Native<br />
             40,000:1/Infinite Black<br />
             Panel<br />
  TC-P50G10  Neo PDP; 1080p Resolution;       March, 2009<br />
             VIERA CAST(TM); VIERA Link(TM);<br />
             THX(R) Certified Display;<br />
             VIERA Image Viewer(TM) with<br />
             AVCHD Playback; 600Hz<br />
             Sub-field Drive; Game<br />
             mode; 3 HDMI inputs,<br />
             Contrast Ratio: Native<br />
             40,000:1/Infinite Black<br />
             Panel<br />
  TC-P46G10  Neo PDP; 1080p Resolution;       March, 2009<br />
             VIERA CAST(TM); VIERA Link(TM);<br />
             THX(R) Certified Display;<br />
             VIERA Image Viewer(TM) with<br />
             AVCHD Playback; 600Hz<br />
             Sub-field Drive; Game<br />
             mode; 3 HDMI inputs,<br />
             Contrast Ratio: Native<br />
             40,000:1/Infinite Black<br />
             Panel<br />
  TC-P42G10  Neo PDP; 1080p Resolution;       March, 2009<br />
             VIERA CAST(TM); VIERA Link(TM);<br />
             THX(R) Certified Display;<br />
             VIERA Image Viewer(TM) with<br />
             AVCHD Playback; 600Hz<br />
             Sub-field Drive; Game<br />
             mode; 3 HDMI inputs,<br />
             Contrast Ratio: Native<br />
             40,000:1/Infinite Black<br />
             Panel<br />
  TC-P65S1   Neo PDP; 1080p Resolution;       August, 2009<br />
             Full-Time 1080 lines<br />
             Moving Picture Resolution;<br />
             VIERA Image Viewer(TM);<br />
             VIERA Link(TM); 600Hz<br />
             Sub-field Drive; AR<br />
             filter; Game mode; 3 HDMI<br />
             inputs, Contrast Ratio:<br />
             Native 40,000:1/Infinite<br />
             Black Panel<br />
  TC-P58S1   Neo PDP; 1080p Resolution;       August, 2009<br />
             Full-Time 1080 lines<br />
             Moving Picture<br />
             Resolution; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             600Hz Sub-field Drive; AR<br />
             filter; Game mode; 3 HDMI<br />
             inputs, Contrast Ratio:<br />
             Native 40,000:1/Infinite<br />
             Black Panel<br />
  TC-P54S1   Neo PDP; 1080p Resolution;       May, 2009<br />
             Full-Time 1080 lines<br />
             Moving Picture<br />
             Resolution; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             600Hz Sub-field Drive; AR<br />
             filter; Game mode; 3 HDMI<br />
             inputs, Contrast Ratio:<br />
             Native 40,000:1/Infinite<br />
             Black Panel<br />
  TC-P50S1   Neo PDP; 1080p Resolution;       March, 2009<br />
             Full-Time 1080 lines<br />
             Moving Picture<br />
             Resolution; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             600Hz Sub-field Drive; AR<br />
             filter; Game mode; 3 HDMI<br />
             inputs; Contrast Ratio:<br />
             Native 40,000:1/Infinite<br />
             Black Panel<br />
  TC-P46S1   Neo PDP; 1080p Resolution;       March, 2009<br />
             Full-Time 1080 lines<br />
             Moving Picture<br />
             Resolution; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             600Hz Sub-field Drive; AR<br />
             filter; Game mode; 3 HDMI<br />
             inputs; Contrast Ratio:<br />
             Native 40,000:1/Infinite<br />
             Black Panel<br />
  TC-P42S1   Neo PDP; 1080p Resolution;       March, 2009<br />
             Full-Time 1080 lines<br />
             Moving Picture<br />
             Resolution; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             600Hz Sub-field Drive; AR<br />
             filter; Game mode; 3 HDMI<br />
             inputs, Contrast Ratio:<br />
             Native 40,000:1/Infinite<br />
             Black Panel<br />
  TC-P50X1   720p Resolution; Over 900        February, 2009<br />
             Lines of Moving<br />
             Resolution, 600Hz<br />
             Sub-field Drive; VIERA<br />
             Image Viewer(TM); VIERA<br />
             Link(TM); AR Filter; Game<br />
             Mode; 3 HDMI inputs;<br />
             Contrast Ratio Native<br />
             30,000:1/Infinite Black<br />
             Panel<br />
  TC-P42X1   720p Resolution; Over 900        February, 2009<br />
             Lines of Moving<br />
             Resolution, 600Hz<br />
             Sub-field Drive; VIERA<br />
             Image Viewer(TM); VIERA<br />
             Link(TM); AR Filter; Game<br />
             Mode; 3 HDMI inputs;<br />
             Contrast Ratio Native<br />
             30,000:1/Infinite Black<br />
             Panel<br />
  TC-54PS14  Neo PDP; 1080p Resolution;<br />
             600Hz Sub-field Drive;<br />
             Contrast Ratio Native<br />
             30,000:1/Infinite Black<br />
             Panel; Over 900 lines of<br />
             Moving  Resolution; VIERA<br />
             Link(TM)z Sub-field Drive;<br />
             SD Photo Viewer; VIERA<br />
             Link;<br />
  TC-50PS14  Neo PDP; 1080p Resolution;<br />
             600Hz Sub-field Drive;<br />
             Contrast Ratio Native<br />
             30,000:1/Infinite Black<br />
             Panel; Over 900 lines of<br />
             Moving  Resolution; VIERA<br />
             Link(TM)z Sub-field Drive;<br />
             SD Photo Viewer; VIERA<br />
             Link;<br />
  TC-42PS14  Neo PDP; 1080p Resolution;<br />
             600Hz Sub-field Drive;<br />
             Contrast Ratio Native<br />
             30,000:1/Infinite Black<br />
             Panel; Over 900 lines of<br />
             Moving  Resolution; VIERA<br />
             Link(TM)z Sub-field Drive;<br />
             SD Photo Viewer; VIERA<br />
             Link;<br />
  TC-50PX14  720p Resolution; 600Hz<br />
             Sub-field Drive; VIERA<br />
             Image Viewer(TM); VIERA<br />
             Link(TM); Over 900 lines of<br />
             Moving Resolution;<br />
             Contrast Ratio Native<br />
             15,000:1/Infinite Black<br />
             Panel<br />
  TC-42PX14  720p Resolution; 600Hz<br />
             Sub-field Drive; VIERA<br />
             Image Viewer(TM); VIERA<br />
             Link(TM); Over 900 lines of<br />
             Moving Resolution;<br />
             Contrast Ratio Native<br />
             15,000:1/Infinite Black<br />
             Panel<br />
  TC-L37G1   1080p Resolution; 120Hz          May, 2009<br />
             Motion Picture Pro3,<br />
             VIERA Image Viewer(TM);<br />
             VIERA Link(TM); Contrast<br />
             Ratio 20,000:1; IPS Alpha<br />
             Panel with 178 degree viewing<br />
             angle; PC input; Game<br />
             Mode; 3 HDMI inputs,<br />
             Swivel Base; Narrow Bezel<br />
             Design, Fine Black Panel<br />
  TC-L32G1   1080p Resolution; 120Hz          May, 2009<br />
             Motion Picture Pro3,<br />
             VIERA Image Viewer(TM);<br />
             VIERA Link(TM); Contrast<br />
             Ratio 20,000:1; IPS Alpha<br />
             Panel with 178 degree viewing<br />
             angle; PC input; Game<br />
             Mode; 3 HDMI inputs;<br />
             Swivel Base; Narrow Bezel<br />
             Design, Fine Black Panel<br />
  TC-L37S1   1080p Resolution; Motion         March, 2009<br />
             Focus technology;<br />
             Intelligent Scene<br />
             Controller, VIERA Image<br />
             Viewer(TM); VIERA<br />
             Link(TM);Contrast Ratio:<br />
             15,000:1 ; IPS Alpha<br />
             Panel with 178 degree viewing<br />
             angle; PC Input; Game<br />
             Mode; 3 HDMI inputs, Fine<br />
             Black Panel<br />
  TC-L32S1   1080p Resolution; Motion         March, 2009<br />
             Focus technology;<br />
             Intelligent Scene<br />
             Controller, VIERA Image<br />
             Viewer(TM); VIERA<br />
             Link(TM);Contrast Ratio:<br />
             15,000:1 ; IPS Alpha<br />
             Panel with 178 degree viewing<br />
             angle; PC Input; Game<br />
             Mode; 3 HDMI inputs, Fine<br />
             Black Panel<br />
  TC-L37X1   720p Resolution; iPod            March, 2009<br />
             Entertainment Kit;<br />
             Intelligent Scene<br />
             Controller; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             Contrast Ratio 12,000:1;<br />
             PC input; Game Mode; 3<br />
             HDMI inputs, Fine Black<br />
             Panel<br />
  TC-L32X1   720p Resolution; iPod            March, 2009<br />
             Entertainment Kit;<br />
             Intelligent Scene<br />
             Controller; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             Contrast Ratio 12,000:1;<br />
             PC input; Game Mode; 3<br />
             HDMI inputs, Fine Black<br />
             Panel<br />
  TC-L26X1   720p Resolution; iPod            May, 2009<br />
             Entertainment Kit;<br />
             Intelligent Scene<br />
             Controller; VIERA Image<br />
             Viewer(TM); VIERA Link(TM);<br />
             Contrast Ratio 12,000:1;<br />
             PC input; Game Mode; 2<br />
             HDMI inputs, Fine Black<br />
             Panel<br />
  TC-L19X1   720p Resolution; iPod            August, 2009<br />
             Entertainment Kit;<br />
             Intelligent Scene<br />
             Controller; VIERA Image<br />
             Viewer(TM); VIERA Link(TM); PC<br />
             input; Game Mode; 2 HDMI<br />
             inputs, Fine Black Panel<br />
  TC-32LX14  720p Resolution;<br />
             Intelligent Scene<br />
             Controller; VIERA Image<br />
             Viewer(TM),; VIERA Link(TM);<br />
             Contrast Ratio 10,000:1;<br />
             Fine Black Panel<br />
  TC-26LX14  720p Resolution, VIERA<br />
             Image Viewer(TM); VIERA<br />
             Link(TM); Contrast Ratio<br />
             10,000:1; Fine Black<br />
             Panel</pre></p>

<p><br />
* Network camera function only available with VIERA G10 and above models, with specified Panasonic H.264 network camera (available summer 2009).</p>

<p>  --  THX and the THX logo are trademarks of THX Ltd. Which may be<br />
      registered in some jurisdictions.<br />
  --  The WirelessHD and WiHD logo are trademarks of WirelessHD, LLC.<br />
  --  iPod is a trademark of Apple Inc., registered in the U.S. and other<br />
      countries.<br />
  --  HDMI, and the HDMI logo and High-Definition Multimedia Interface are<br />
      trademarks or registered trademarks of HDMI Licensing LLC.<br />
  --  All other trademarks are property of the respective owners.</p>

<p>Source: Panasonic </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  4:51 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1631)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1631)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-its-award-winning-line-of-vierar-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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
