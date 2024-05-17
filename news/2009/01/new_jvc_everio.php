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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1661 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1661
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php&amp;title=New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need">
		<span style="display:none">JVC today announced a new line of Everio camcorders that offers innovations in video sharing, dual storage memory models and hi-def camcorders no larger than their diminutive standard definition counterparts.

For 2009, what the Everio line offers is choice - in storage medium, color and sharing. The line includes hard disk drive (HDD) models, memory camcorders, and for the first time dual memory models, including standard and high definition dual...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1661";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" />
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
	<title>HDTV Magazine - New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need</title>
	<meta name="keywords" content="avg mbps, rec modes, min min, data battery, battery charger, min, jvc, everio, memory, models, recording, card, dual, camcorders, trademarks, video, battery, avg, new, youtube, mbps, definition, line, model, camcorder" />
	<meta name="description" content="JVC today announced a new line of Everio camcorders that offers innovations in video sharing, dual storage memory models and hi-def camcorders no larger than their diminutive standard definition counterparts.

For 2009, what the Everio line offers is choice - in storage medium, color and sharing. The line includes hard disk drive (HDD) models, memory camcorders, and for the first time dual memory models, including standard and high definition dual..." />
	<meta name="title" content="New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1661', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php">New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2009</b>
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
				<p class="prtitle">New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need</p>

<center><i>2009 line includes palm-sized new high definition models, hard disk and memory recording, more color choices and new sharing options.</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 8 /PRNewswire/</B> -- JVC today announced a new line of Everio camcorders that offers innovations in video sharing, dual storage memory models and hi-def camcorders no larger than their diminutive standard definition counterparts.</p>

<p>For 2009, what the Everio line offers is choice - in storage medium, color and sharing. The line includes hard disk drive (HDD) models, memory camcorders, and for the first time dual memory models, including standard and high definition dual SD memory card camcorders, the world's first dual card slot models. There's also a standard definition model that combines SD card recording with internal flash memory.</p>

<p>For sharing, all 2009 JVC Everios make it easy to watch videos on an iPod(R) or iPhone(R) by exporting videos to the user's iTunes(R) library with the new One Touch Export function provided by bundled software for Windows(R). This joins the One Touch Upload function for uploading to YouTube(TM), for 2009 a feature available on all Everio camcorders, and One Touch DVD Creation function for easy archiving and sharing by disc, a long-time Everio feature.</p>

<p>All 2009 Everio camcorders are extremely compact, with the high definition models for the first time the same size as standard definition Everios that use the same media. In addition, more models offer color options for 2009, and the choice of colors has expanded to six.</p>

<p>The 2009 JVC Everio line includes three HD Everio models that all record 1920 x 1080 full HD video. The GZ-HD300, with 60GB hard drive, and the GZ-HD320, with 120GB hard drive, offer HDD storage for extra-long recording times. The GZ-HM200 offers dual SD card recording, with continuous recording from one card to the next, and is one of the smallest and lightest full HD camcorders available.</p>

<p>In standard definition, there are three new Everio G series models - the 60GB GZ-MG630, the 80GB GZ-MG670 and the 120GB GZ-MG680. The Everio S series of memory camcorders includes two models - the GZ-MS120, with dual SD card slots, and the GZ-MS130, with an SD card slot and 16GB of internal flash memory. All dual memory models offer continuous recording.</p>

<p><br />
<B>Availability and pricing:</B><br />
<pre><br />
  Model              Available                    National Ad Value<br />
  GZ-MS120           February                     $299.95<br />
  GZ-MS130           February                     $349.95<br />
  GZ-MG630           January                      $429.95<br />
  GZ-MG670           January                      $479.95<br />
  GZ-MG680           February                     $549.95<br />
  GZ-HM200           March                        $579.95<br />
  GZ-HD300           February                     $699.95<br />
  GZ-HD320           February                     $799.95</pre></p>

<p><br />
<B>About JVC Company of America</B></p>

<p>JVC Company of America, headquartered in Wayne, New Jersey, is a division of JVC Americas Corp., a wholly-owned subsidiary of Victor Company of Japan Ltd., and a holding company for JVC companies located in North and South America. JVC distributes a complete line of video and audio equipment, including high definition displays, camcorders, DVD players and recorders, home and portable audio equipment, headphones, mobile entertainment products and recording media. For further product information, visit JVC's Web site at http://www.jvc.com/ or call 800-526-5308.</p>

<p>Trademarks</p>

<p>- YouTube and the YouTube logo are trademarks and/or registered trademarks of YouTube LLC. This product's YouTube(TM) upload functionality is included under license from YouTube LLC. The presence of YouTube(TM) upload functionality in this product is not an endorsement or recommendation of the product by YouTube LLC.</p>

<p>- Microsoft(R) and Windows(R) are either registered trademarks or trademarks of Microsoft Corporation in the United States and/or other countries.</p>

<p>- Apple, Apple logo, Macintosh, Mac OS, QuickTime iMovie, Final Cut Pro, iTunes, iPod, and iPhone are trademarks of Apple Inc. registered in the United States and other countries.</p>

<p>- "AVCHD" and the "AVCHD" logo are trademarks of Panasonic Corporation and Sony Corporation.</p>

<p>- The microSD and microSDHC logos are trademarks of the SD Card Association.</p>

<p>- All brand names are trademarks, registered trademarks, or trade names of their respective holders.</p>

<p>JVC Everio Camcorders - 2009:</p>

<p>Approx. recording times for each recording mode and number of storable still images</p>

<p>  [HD Everio] HD Hard Disk Camcorder GZ-HD320, GZ-HD300<br />
<pre><br />
  4 Rec Modes:             Built-in HDD             microSDHC<br />
  UXP, XP, SP, EP          120GB model 60GB model   8GB          4GB</p>

<p>  Video UXP  Avg. 24 Mbps  11 hr       5 hr 30 min  40 min       20 min<br />
        XP   Avg. 17 Mbps  15 hr       7 hr 30 min  1 hr         30 min<br />
        SP   Avg. 12 Mbps  21 hr       10 hr        1 hr 28 min  44 min<br />
        EP   Avg. 5  Mbps  50 hr       25 hr        3 hr 20 min  1 hr 40 min<br />
  Stills                   9999</p>

<p>  [HD Everio] HD Memory Camcorder (Dual SD Slot) GZ-HM200</p>

<p>                          Dual SDHC<br />
  Rec Modes               32GB + 32 GB 16GB + 16GB  8GB + 8GB   4GB + 4GB</p>

<p>  Video UXP Avg. 24 Mbps  5 hr 20 min  2 hr 40 min  1 hr 20 min 40 min<br />
        XP  Avg. 17 Mbps  8 hr         4 hr         2 hr        1 hr<br />
        SP  Avg. 12 Mbps  11 hr 44 min 5 hr 52 min  2 hr 56 min 1 hr 28 min<br />
        EP   Avg. 5 Mbps  26 hr 40 min 13 hr 20 min 6 hr 40 min 3 hr 20 min<br />
  Stills                  9999</p>

<p>  [Everio G] Hard Disk Camcorder GZ-MG680, GZ-MG670, GZ-MG630</p>

<p>              Built-in HDD                           microSDHC<br />
  Rec Modes   120GB model  80GB model   60GB model   8GB         4GB</p>

<p>  Video Ultra 28 hr 40 min 19 hr        14 hr 20 min 1 hr 54 min 57 min<br />
        Fine  42 hr 40 min 28 hr 20 min 21 hr 20 min 2 hr 50 min 1 hr 25 min<br />
        Norm  56 hr 20 min 37 hr 40 min 28 hr 10 min 3 hr 46 min 1 hr 53 min<br />
        Eco   150 hr       100 hr       75 hr        9 hr 56 min 4 hr 58 min<br />
  Stills      9999</p>

<p>  [Everio S] Memory Camcorder (Dual SD Slot) GZ-MS120</p>

<p>  Rec Modes     When using 2 SDHC Cards<br />
                32GB + 32GB     16GB + 16GB     8GB + 8GB     4GB + 4GB</p>

<p>  Video Ultra   15 hr           7 hr 30 min     3 hr 40 min   2 hr<br />
        Fine    22 hr 40 min    11 hr 20 min    5 hr 40 min   2 hr 40 min<br />
        Norm    30 hr           15 hr           7 hr 30 min   3 hr 40 min<br />
        Eco     80 hr           40 hr           19 hr 50 min  10 hr<br />
  Stills        9999</p>

<p>  [Everio S] Memory Camcorder (Single SD Slot + Internal Memory) GZ-MS130</p>

<p>               When using an SDHC Card and internal 16GB memory<br />
  Rec Modes    32GB + 16GB    16GB + 16GB   8GB + 16GB    4GB + 16GB</p>

<p>  Video  Ultra 11 hr 15 min   7 hr 30 min   5 hr 35 min   4 hr 45 min<br />
         Fine  17 hr          11 hr 20 min  8 hr 30 min   7 hr<br />
         Norm  22 hr 30 min   15 hr         11 hr 15 min  9 hr 20 min<br />
         Eco   60 hr          40 hr         29 hr 55 min  25 hr<br />
  Stills Fine  9999<br />
</pre><br />
  Note:</p>

<p>Recording time figures are estimations. There may be cases in which actual recording time becomes shorter depending on the type of content being recorded.</p>

<p>For all models, SD/microSD card is not supplied.</p>

<p>To record video, an SDHC/microSDHC card with Class 4 or higher performance is required. For UXP mode, please use class 6 or higher. SD/microSD memory cards (256MB to 2GB) and SDHC memory cards (4GB to 32GB)/microSDHC memory cards (4GB and 8GB) have been tested for the following brands: Panasonic, Toshiba, SanDisk, and ATP. Note that using other media may result in recording failure or data loss.</p>

<p>  Key optional accessories for Everio<br />
  DVD Burner<br />
  --  CU-VD50 SHARE STATION Direct DVD Burner/Player<br />
  --  CU-VD3 SHARE STATION Direct DVD Burner<br />
  Data Battery and Charger<br />
  --  BN-VF808 Data Battery (730mAh)<br />
  --  BN-VF815 Data Battery (1460mAh)<br />
  --  BN-VF823 Data Battery (2190mAh)<br />
  --  AA-VF8 Battery Charger<br />
  --  VU-VT8K Battery Charger Kit (incl. BN-VF808 Data Battery and AA-VF8<br />
      Battery Charger)<br />
  Conversion Lens and Filter<br />
  --  GL-V0730 Wide-Angle Conversion Lens<br />
  --  GL-AT30 Telephoto Conversion Lens<br />
  --  GL-A30CPK Filter Kit</p>

<p><br />
<pre><br />
  Contacts:<br />
  Chelsea Vander Groef                          JVC News Hotline<br />
  JVC Company of America                        1-877-NEWS-JVC<br />
  973-317-5000, X5312                           JVCPR@PlannedTVArts.com<br />
  cvandergroef@jvc.com<br />
</pre></p>

<p>NOTE TO EDITORS: An electronic version of this release and high-resolution<br />
images are available at: http://www.jvc.com/ces. For images, please click<br />
here.</p>

<p>First Call Analyst:<br />
FCMN Contact:<br />
http://www.jvc.com/press/Vegas09/index.jsp</p>

<p>Source: JVC Company of America </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2009  8:28 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1661)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1661)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php" type="text/javascript" charset="utf-8"></script>
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
