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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3241 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3241
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php&amp;title=NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs">
		<span style="display:none">NETGEAR launched the newest Internet-connected set-top box in its popular &quot;Digital Entertainer&quot; product family. The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.

In addition to personal media collections and YouTube, consumers can now...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3241";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" />
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
	<title>HDTV Magazine - NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</title>
	<meta name="keywords" content="digital entertainer, entertainer live, digital media, internet video, netgear inc, digital, netgear, internet, live, entertainer, home, videos, video, media, network, products, movies, product, content, usb, storage, service, consumers, may, devices" />
	<meta name="description" content="NETGEAR launched the newest Internet-connected set-top box in its popular &quot;Digital Entertainer&quot; product family. The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.

In addition to personal media collections and YouTube, consumers can now..." />
	<meta name="title" content="NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3241', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php">NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>
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
				<p class="prtitle">NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</p>

<center><i>Plays Movies, Videos, Music and Photos from USB Drives, Computers and Network Attached Storage; Accesses YouTube, Roxio CinemaNow On-demand Movies, Internet Videos, Hulu, Netflix and More</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/2009-09-08_netgear_digital_entertainer_live.jpg" alt="Netgear Digital Entertainer Live" class="keyimg" /><strong>SAN JOSE, Calif., Sept. 8 /PRNewswire-FirstCall/ -- </strong>NETGEAR , Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, today launched the newest Internet-connected set-top box in its popular "Digital Entertainer" product family. The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.</p>

<p>In addition to personal media collections and YouTube, consumers can now easily browse, download and play newly released pay-per-view movies from Roxio CinemaNow(TM). They can also view videos on their TV from a wide variety of Internet sources, such as Hulu, Netflix and CBS, through free software trials and optional subscriptions. The Digital Entertainer Live incorporates all of these functions into a single compact player, an advantage for cluttered home entertainment cabinets.</p>

<p>"People are amassing a huge amount of their own downloaded and personal digital music, photos, and videos, as well as consuming more and more Internet video," said Phillip Pyo, NETGEAR's director of product marketing for connected home entertainment products. "According to comScore's Video Metrix service, between January 2007 and July 2009, there was a 331 percent jump in the number of minutes of video watched per average viewer per month. It went from 2 hours, 31 minutes to 8 hours, 20 minutes."</p>

<p>He added, "The vast majority of people are still watching these videos on small computer screens, so it's logical to assume that the amount of time spent will continue to increase as devices such as the Digital Entertainer Live make it even easier to watch online video on HDTVs. NETGEAR is thrilled to offer an affordable, content-rich, and easy-to-set-up and -use solution that bridges this gap and enables people to fully enjoy their home media collections and online video from popular Internet sites on the best screen in their home -- their big-screen TVs."</p>

<p><br />
<strong>The Digital Entertainer Live -- Product Features</strong></p>

<p>The Digital Entertainer Live is a compact, "plug in and go" home media player with a simple remote control that enables consumers to easily access their digital movies, videos, music and photos directly from their USB storage devices and watch them on their TV. Users need only plug a USB hard drive containing digital media content into one of two USB 2.0 ports on the Digital Entertainer Live and connect the Digital Entertainer Live to their HDTV using an HDMI or composite cable. The Digital Entertainer Live also features regular RCA jacks for connecting to older analog TVs.</p>

<p>Furthermore, with its integrated network port, the Digital Entertainer Live easily makes an Ethernet wired connection to the Internet and the home network, enabling access to digital media content stored on computers and network storage devices in the home network, as well as Internet content over the web. If consumers do not have an Ethernet connection available near their TV, they can use the optional Digital Entertainer Live Wireless USB Adapter (EVAW111) that connects the Digital Entertainer Live to the Internet and the home network via Wi-Fi . Alternatively, they can use existing electrical power outlets and a powerline device, such as NETGEAR's Home Theater Internet Connection Kit (XAVB1004), to connect the Digital Entertainer Live to the Internet and the home network.</p>

<p>By connecting the Digital Entertainer Live to a broadband Internet connection, consumers enjoy the full YouTube experience -- searching, browsing and watching millions of videos with access to subscriptions, playlists, country selections, categories and channels -- all without the need for a computer. Instead of huddling around a small computer screen to watch the latest funny video, consumers can now show it on their TV for everyone to enjoy.</p>

<p>To widen the search for Internet video content, the Digital Entertainer Live is shipped with a built-in Internet video search engine that can locate videos on the entire worldwide web. The Digital Entertainer Live performs dynamic keyword searches of more than a hundred thousand websites for Internet videos without needing a computer. The search feature yields dynamic results with each letter inputted and automatically categorizes popular subjects into easy-to-find folders.</p>

<p>The Digital Entertainer Live also supports pay-per-view movies on-demand from Roxio CinemaNow, where users can buy or rent a range of newly released movies as soon as they are available on DVD and watch them in minutes. Consumers no longer have to wait for the mail or drive to the nearest rental store. They simply browse all the movies on their TV and download them to a USB storage device using their Digital Entertainer Live and Roxio CinemaNow account.</p>

<p>Additionally, the Digital Entertainer Live includes a free trial of VuNow, which provides access to hundreds of other Internet videos, live Internet TV and live Internet radio streamed from popular sites from around the world, such as Bloomberg, CNN Video, C-SPAN, ESPN, Germany's 2DF, Al Jazeera, BBC Worldwide, China's CCTV, Germany's DWTV, Euronews, EuroSport, France 24, France's Orange Sport, Germany's RTL, and Sky News. The Digital Entertainer Live also comes with a free trial of PlayOn software. By running this optional software on a computer also connected to the Internet and home network, users enjoy hit TV shows and movies from popular Internet video services such as Hulu, Netflix, Amazon Video On Demand, BBC iPlayer, CBS, NFL, the Australian Broadcasting Corp. and more, wherever the service is normally available via the Internet.</p>

<p>"There is an ever-increasing amount of digital media -- TV episodes, movies, photos and music -- being stored on computers and other devices throughout the home, as well as on the Internet," said Jayant Dasari, broadband and television infrastructure and services research analyst at Parks Associates. "In fact, some people have even maxed out their personal computers with media, requiring external storage, such as one that connects via USB. Due to this increase in distributed digital media content, consumers are looking for ways to enjoy their digital media and online videos in one place. Internet-connected set-top boxes are one solution that enable the entire family to benefit from viewing the broadest spectrum of digital content on their HDTVs from the comfort of their living rooms."</p>

<p><br />
<strong>Pricing and Availability</strong></p>

<p>Backed by a one-year warranty and 24/7 technical support, the NETGEAR Digital Entertainer Live (EVA2000) is available in the U.S. through leading retailers, e-commerce sites and value-added resellers at an MSRP of $149.99. The Digital Entertainer Live Wireless USB Adapter (EVAW111) has an MSRP of $39.99. Worldwide availability of the Digital Entertainer Live is planned for the coming months.</p>

<p>Product details, instructional videos and other information are at <a target="_blank" href="http://www.delive.netgear.com/">www.delive.netgear.com</a>.</p>

<p>Product photos can be downloaded from: <a target="_blank" href="http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA2000.aspx">www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA2000.aspx</a></p>

<p><br />
<strong>About NETGEAR, Inc.</strong></p>

<p>NETGEAR (NASDAQGM: NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of Small- to Medium-sized Businesses (SMBs) and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in over 29,000 retail locations around the globe, and via more than 41,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR partner. More information is available at <a target="_blank" href="http://www.netgear.com/">www.netgear.com</a> or by calling (408) 907-8000. Follow NETGEAR at <a target="_blank" href="http://twitter.com/NETGEAR/">twitter.com/NETGEAR</a> and <a target="_blank" href="http://www.facebook.com/netgear">www.facebook.com/netgear</a>.</p>

<p>2009 NETGEAR, Inc. NETGEAR and the NETGEAR logo are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>Wi-Fi is a trademark of the Wi-Fi Alliance. Any other trademarks or trade names mentioned are the property of their respective owners.</p>

<p>VuNow service will be free for trial period and thereafter subject to paid annual subscriptions. The term of the trial period may be found at the Digital Entertainer Live product site located within <a target="_blank" href="http://www.netgear.com/vunow">www.netgear.com/vunow</a>. The VuNow service will be subject to acceptance of the terms of the VuNow service and license agreement.</p>

<p>PlayOn service will be free for a trial period and thereafter offered with a special discount with Digital Entertainer Live purchase. The term of the trial period may be found at the Digital Entertainer Live product site located within <a target="_blank" href="http://www.netgear.com/playon">www.netgear.com/playon</a>. Support of online sites subject to PlayOn terms and conditions.</p>

<p>CinemaNow, Hulu and Netflix are only available in the United States. Netflix support requires an existing subscription to the Netflix service.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning NETGEAR's business and the expected performance characteristics, specifications, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 35 through 49, in the Company's quarterly report on Form 10-Q for the fiscal second quarter ended June 28, 2009, filed with the Securities and Exchange Commission on August 6, 2009. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>

<p>Source: NETGEAR, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  8, 2009  9:14 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3241)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3241)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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
