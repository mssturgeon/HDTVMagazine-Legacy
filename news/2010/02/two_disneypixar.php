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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3562 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3562
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php&amp;title=Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp;amp; Toy Story 2">
		<span style="display:none">This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.

For the first time ever, these two groundbreaking films will be presented...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3562";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2" />
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
	<title>HDTV Magazine - Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2</title>
	<meta name="keywords" content="toy story, blu ray, combo pack, pre book, walt disney, toy, story, pixar, disney, dvd, feature, combo, english, blu, ray, dolby, buzz, pack, spanish, french, pre, entertainment, book, time, may" />
	<meta name="description" content="This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.

For the first time ever, these two groundbreaking films will be presented..." />
	<meta name="title" content="Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp;amp; Toy Story 2" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3562', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php">Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>February 23, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before</p>

<center><i>TOY STORY &amp; TOY STORY 2 Blasting Off For the First Time Ever in High Definition On Blu-ray(TM) + DVD Combo Pack March 23, 2010 Special Edition DVDs Available May 11, 2010</center></i><br />
<br />

<p><strong>BURBANK, Calif., Feb. 23 /PRNewswire/ -- </strong>This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.</p>

<p>For the first time ever, these two groundbreaking films will be presented with eye-popping 1080p high definition picture and 5.1 DTS-HD Master Audio sound, along with hours of innovative new bonus features. Also included are exclusive sneak peeks at Disney-Pixar's Toy Story 3, hitting U.S. theaters on June 18, 2010.</p>

<p>Academy Award&reg;-winner John Lasseter (1996, Special Achievement Award, Toy Story), directs these two action-packed, laugh-filled animated favorites featuring the superstar voice talents of Tom Hanks (Angels &amp; Demons) and Tim Allen (The Santa Clause), along with Don Rickles (Casino), Annie Potts (Ghostbusters), Wallace Shawn (The Princess Bride) and, of course, Pixar favorite John Ratzenberger (TV's "Cheers"). Together, Toy Story and Toy Story 2 launched the Disney-Pixar label and sent audiences to the infinity of family entertainment fun and beyond...as they're sure to do once again on Blu-ray(TM).</p>

<p>TOY STORY Blu-ray(TM) + DVD Combo Pack All-New Bonus Features<br />
<ul><li>The Story: An Exclusive Sneak Peek at Toy Story 3</li><li>Buzz Lightyear Mission Logs:</li><li>Episode One: Blast Off - Buzz reports back about his adventure to the International Space Station.</li><li>Paths to Pixar: Artists - In this series of shorts, Pixar artists talk about their career path and share advice to aspiring filmmakers and animators.</li><li>Studio Stories: A series of amusing anecdotal shorts about life at Pixar.</li><li>"John's Car" recounts how Toy Story director John Lasseter refused to stop driving his beat-up car even after the film's success.</li><li>"Baby AJ" tells the hilarious story of how important Halloween is to the Pixar team and how one Pixar employee dressed up as the oversized baby from the short Tin Toy to win a prize.</li><li>"Scooter Races" takes the audience on a rousing scooter race around the studio with John Lasseter and other employees.</li><li>Buzz Takes Manhattan - Spectacular footage from Buzz Lightyear's premiere as a Macy's Thanksgiving Day Parade balloon.</li><li>Black Friday: The Toy Story You Never Saw - The Toy Story filmmakers discuss the early cut of the film that very nearly shut down production entirely.</li></ul></p>

<p>TOY STORY 2 Blu-ray(TM) + DVD Combo Pack All-New Bonus Features<br />
<ul><li>Characters: An Exclusive Sneak Peek At Toy Story 3</li><li>Director Commentary</li><li>Buzz Lightyear Mission Logs:</li><li>Episode Two: International Space Station - Buzz reports back about his adventure to the International Space Station.</li><li>Paths to Pixar: Technical Artists - In this series of shorts, Pixar artists talk about their career path and share advice to aspiring filmmakers and animators.</li><li>Studio Stories: A series of amusing anecdotal shorts about life at Pixar.</li><li>"Toy Story 2 Sleep Deprivation Lab" gives audiences an inside peek into the intense time pressures under which the Toy Story 2 editors found themselves.</li><li>"Studio Stories: Pinocchio" shows an impromptu competition between the animators to decorate their workspace by throwing toys into the ceiling.</li><li>"Studio Stories: The Movie Vanishes" tells how a mischievous technical error almost erased all of Toy Story 2 from the computer system.</li><li>Pixar's Zoetrope - A look at the creation of the live-action zoetrope that Pixar created to capture the principles of animation in a live sculpture.</li><li>Celebrating our Friend Joe Ranft - A tribute to beloved Disney and Pixar story man Joe Ranft, regarded as one of the industry's most gifted story artists.</li></ul></p>

<p><br />
<strong>Toy Story Synopsis:</strong></p>

<p>Toy Story, the first full-length computer-animated feature film, is "a wonder to behold." - People Magazine. Experience a hilarious fantasy about the lives toys lead when they're left alone. Woody (voiced by Tom Hanks), an old-fashioned cowboy doll, is Andy's favorite. But when Andy gets Buzz Lightyear (voiced by Tim Allen) for his birthday, the flashy new space hero takes Andy's room by storm! Their rivalry leaves them lost with a toy's worst nightmare -- Sid, the toy-torturing boy next door. Woody and Buzz must work together to escape, realizing along the way that they've got a friend ... in each other!</p>

<p><br />
<strong>Toy Story 2 Synopsis:</strong></p>

<p>A Golden Globe&reg; Award winner for Best Motion Picture - Comedy Or Musical, Toy Story 2 has become a favorite all across the world, garnering praise from fans and critics everywhere. It's "an instant classic," raved New York Magazine. While Andy is away at summer camp, Woody is toynapped by Al McWhiggin, a greedy collector who needs Andy's favorite toy to complete his "Woody's Roundup" collection. Together with Jessie (voiced by Joan Cusack), Bullseye, and the Prospector, Woody is on his way to a museum where he'll spend the rest of his life behind glass. It's up to Buzz, Mr. Potato Head, Hamm, Rex, and Slinky Dog to rescue their friend and remind him what being a toy is all about.</p>

<p>The Toy Story and Toy Story 2 Blu-ray(TM) + DVD Combo Packs are priced at an SRP of $39.99. The Special Edition DVDs are priced at an SRP of $29.99.</p>

<pre>
  TOY STORY BD &amp; DVD COMBO PACK PRODUCT DETAILS:

<p>  STREET DATE:     March 23, 2010<br />
  ---------------------------------------<br />
  Direct pre-book:     1/26/10<br />
  Distributor pre-book:    2/09/10<br />
  Suggested Retail Price:  $39.99<br />
  Feature run time:    Approximately 81 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1.78:1<br />
  Video:       1080p, Widescreen<br />
  Sound:       5.1 DTS-HD ES, English 2.0 DTS-HD,       English DVS 2.0 Dolby; French 5.1 Dolby       EX; Spanish 5:1 Dolby EX<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY 2 BD &amp; DVD COMBO PACK PRODUCT DETAILS:<br />
  STREET DATE:     March 23, 2010<br />
  ---------------------------------------<br />
  Direct pre-book:     1/26/10<br />
  Distributor pre-book:    2/09/10<br />
  Suggested Retail Price:  $39.99<br />
  Feature run time:    Approximately 92 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    2.35:1<br />
  Video:       1080p, Widescreen<br />
  Sound:       5.1 DTS-HD ES, English 2.0 DTS-HD,       English DVS 2.0 Dolby; French 5.1 Dolby       EX; Spanish 5:1 Dolby EX<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY SPECIAL EDITION DVD PRODUCT DETAILS:<br />
  STREET DATE:     May 11, 2010<br />
  -------------------------------------<br />
  Direct pre-book:     3/16/10<br />
  Distributor pre-book:    3/30/10<br />
  Suggested Retail Price:  $29.99<br />
  Feature run time:    Approximately 81 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1:78:1<br />
  Sound:       5.1 EX and 2.0 Dolby Digital English,       French (Canadian only), Dedicated       Spanish Language SKU<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY 2 SPECIAL EDITION DVD PRODUCT DETAILS:<br />
  STREET DATE:     May 11, 2010<br />
  -------------------------------------<br />
  Direct pre-book:     3/16/10<br />
  Distributor pre-book:    3/30/10<br />
  Suggested Retail Price:  $29.99<br />
  Feature run time:    Approximately 92 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1:78:1<br />
  Sound:       5.1 EX and 2.0 Dolby Digital English,       French (Canadian only), Dedicated       Spanish Language SKU<br />
  Subtitles:       English SDH, French, Spanish<br />
</pre></p>

<p><br />
<strong>About Disney's Combo Pack:</strong></p>

<p>To provide consumers with unprecedented quality, value and portability of their favorite Disney movies, in 2008 WDSHE pioneered the Combo Pack - a Blu-ray Disc(TM) plus a DVD and a DisneyFile Digital Copy of the film in a single package. Current Disney-branded titles available as Combo Packs include High School Musical 3, Beverly Hills Chihuahua, Bolt, Bedtime Stories, Race To Witch Mountain, Jonas Brothers and Hannah Montana The Movie.</p>

<p>Walt Disney Studios Home Entertainment, a recognized leader in the home entertainment industry, is the marketing, sales and distribution company for Walt Disney, Touchstone, Hollywood Pictures, Miramax and Buena Vista product, which includes DVD, Blu-ray Disc(TM) and electronic distribution. Walt Disney Studios Home Entertainment is a division of The Walt Disney Studios</p>

<p>These press materials are available in electronic form at <a target="_blank" href="http://www.WDSHEpublicity.com/">www.WDSHEpublicity.com</a>.</p>

<p>&copy;Walt Disney Studios Home Entertainment, Inc.</p>

<p>Source: Walt Disney Studios Home Entertainment(C)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>February 23, 2010  6:11 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3562)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3562)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php" type="text/javascript" charset="utf-8"></script>
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
