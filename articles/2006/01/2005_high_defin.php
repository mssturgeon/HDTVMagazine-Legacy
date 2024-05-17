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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Thomas Fletcher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Thomas Fletcher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 307 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 307
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 High Definition: A Year in Review&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/01/2005-high-definition-a-year-in-review.php&amp;title=2005 High Definition: A Year in Review">
		<span style="display:none">In this article, the first in a three-part series, Tom Fletcher gives us a comprehensive overview of &quot;HDTV in 2005&quot;. It places on the calendar those events which have left an historical echo in our national transition to superior television. Highlighting this progressive report on the health of HDTV are new HD channel launches, increased HD movie production, 1080p TV's, new HD cameras (both consumer and professional), and the arrival of HD console gaming (Xbox). The year-end numbers for Digital Cinema and HDTV penetration are sure to impress those who still think that HDTV is slow to catch on. A must-read for those with a careful eye on the HDTV phenomena.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 307";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 High Definition: A Year in Review" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 High Definition: A Year in Review" />
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
	<title>HDTV Magazine - 2005 High Definition: A Year in Review</title>
	<meta name="keywords" content="high definition, digital cinema, fletcher chicago, using sony, digital projectors, link, digital, hdtv, sony, camera, first, film, definition, production, high, year, cameras, announces, cinema, television, series, networks, fletcher, using, projectors" />
	<meta name="description" content="In this article, the first in a three-part series, Tom Fletcher gives us a comprehensive overview of &quot;HDTV in 2005&quot;. It places on the calendar those events which have left an historical echo in our national transition to superior television. Highlighting this progressive report on the health of HDTV are new HD channel launches, increased HD movie production, 1080p TV's, new HD cameras (both consumer and professional), and the arrival of HD console gaming (Xbox). The year-end numbers for Digital Cinema and HDTV penetration are sure to impress those who still think that HDTV is slow to catch on. A must-read for those with a careful eye on the HDTV phenomena." />
	<meta name="title" content="2005 High Definition: A Year in Review" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/01/2005-high-definition-a-year-in-review.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=307', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/01/2005-high-definition-a-year-in-review.php">2005 High Definition: A Year in Review</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Thomas Fletcher</b> on <b>January 31, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=328&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=274&category=Gaming">Gaming</a></b>, <b><a href="/category.php?id=515&category=HD DVD">HD DVD</a></b>, <b><a href="/category.php?id=283&category=HD Video Production">HD Video Production</a></b>
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
				<blockquote>In this article, the first in a three-part series, Tom Fletcher gives us a comprehensive overview of "HDTV in 2005". It places on the calendar those events which have left an historical echo in our national transition to superior television. Highlighting this progressive report on the health of HDTV are new HD channel launches, increased HD movie production, 1080p TV's, new HD cameras (both consumer and professional), and the arrival of HD console gaming (Xbox). The year-end numbers for Digital Cinema and HDTV penetration are sure to impress those who still think that HDTV is slow to catch on. A must-read for those with a careful eye on the HDTV phenomena.<br>
<br>
The other parts in the series are:<br>
Part 2: <a href="/articles/2006/02/hd_feature_film.php">HD Feature Films Shot or Released in 2005</a><br>
Part 3: <a href="/articles/2006/02/2005_hd_acquisi.php">2005 HD Acquisition Television Show Productions</a></blockquote>
<br>
<br>
<center><b>Fletcher Chicago's<br>
2005 High Definition: A Year in Review</b><br>
Compiled by Thomas Fletcher<br>
http://www.fletch.com/feArticles.asp?NextAction=Article&AID=263
</center>

<p><br />
<b>January</b></p>

<p>ESPN launched its second High Definition network, ESPN2HD. <a href="http://broadcastengineering.com/newsletters/sports/20050114/">Link</a></p>

<p>Apple's Steve Jobs declares 2005 as "The Year of HD Video Editing." High Definition editing reaches all the way down to the consumer with released of iMovie HD supporting the HDV 720p and 1080i formats. <a href="http://broadcastengineering.com/newsletters/bth/20050116/">Link</a></p>

<p>For the first time in the history of the Sundance Film Festival more films are projected using digital projection than conventional 35mm. One film, <i>"Rize"</i>, was delivered via the internet. <a href="http://www.wired.com/news/culture/0,1284,66380,00.html">Link</a></p>

<p>In town to shoot a Nike spot. Spike Lee's production company, 40 Acres and a Mule is the first tenant for Fletcher's new Production Offices. <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=239">Link</a></p>

<p><br />
<b>February</b><br />
FOX's Super Bowl broadcast sees a 150% increase in the number of HD <i>finished</i> commercials over the previous year (up from 10 HD spots to 25). 87% of viewers said that watching commercials in HD increased their enjoyment of the spot. <a href="http://www.inhd.com/press/pressDetail.jsp?pressId=37">Link</a></p>

<p>HD Super Bowl spots that originated electronically included Heineken's <i>"Beer Run"</i> directed by David Fincher using a Viper, and General Motors/Cadillac <i>"Elope"</i> directed by Antony Hoffman mixing VariCam and Sony HDV. <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=236">Link</a></p>

<p>"Superman Returns" beings production in Australia. It is the first feature film to use Panavision's new single, imager HD digital cinematography camera Genesis. Director Bryan Singer mixes Sony's HDW-F950 for underwater cinematography. <a href="http://www.panavision.com.au/News/Genesis_on_Superman.htm">Link</a></p>

<p>Canadians are purchasing HDTV sets faster the Americans. 16% of Canadian households compared with 10% of their American counterparts. <a href="http://www.ottawabusinessjournal.com/306401552757091.php">Link</a></p>

<p>The BBC starts principal photography of Charles Dickens' Bleak House, its first period drama shot on HD. Throughout the 22-week shoot, DP Kieran McGuigan utilizes two modified HDW-750P cameras via ARRI Media. <a href="http://www.sonybiz.net/b2b/sony-business-uk/14625-sony-prestigious-bbc-drama-series-bleak-house-shooting-hdcam-hdcam-latest-news.html">Link</a></p>

<p><br />
<b>March</b><br />
<i>"Collateral"</i> is the first digital feature film (approx. 80% shot with Viper and F900) to be honored for Outstanding Cinematography. Wins British Academy Award (BAFTA) for Best Cinematography and was also nominated by American Society of Cinematographers (ASC). <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=238">Link</a></p>

<p>CBS triples the number of March Madness basketball games to be broadcast in HDTV. <a href="http://www.harris.com/view_pressrelease.asp?act=lookup&pr_id=1548">Link</a></p>

<p>Ireland announces intention to be first country to go all-digital in theaters. <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=241&PG=True">Link</a></p>

<p><br />
<b>April</b><br />
Canon's anamorphic converter makes its debut with Spain's production company MediaPro shooting the feature "Salvador". <a href="http://www.canon-europe.com/TV-Products/News/anamorphic_converter_story.asp?ComponentID=320038&SourcePageID=33108">Link</a></p>

<p>"Sin City" opens. Shot using Sony's F950/HDCAM SR/Fujinon E-Series, director Robert Rodriquez's utilizes his local Austin, Texas post house, 501 Post, to conform and color correct via Quantel's eQ 4:4:4. <a href="http://www.quantel.com/site/en.nsf/HTML/81B65448592CD8048025709A00375D9C">Link</a></p>

<p>NAB HD Product Launches<br />
- Pace Advantage 4:4:4 Camera with the style, form, fit and feel of a film body. <a href="http://www.fletch.com/pace.htm">Link</a><br />
- Sony introduces 1080/60p cameras. <a href="http://news.sel.sony.com/pressrelease/5774">Link</a><br />
- Quantel launches "Pay as you Go HD" to ease transition to HD finishing. <a href="http://www.digitalvideoediting.com/articles/viewarticle.jsp?id=34659">Link</a><br />
- Panasonic introduces HVX-200 P2 Camera (Ships Dec 29, 2005) <a href="http://www.cameraguild.com/index.html?technology/05-05a.html~top.main_hp">Link</a><br />
- Venom Flash Pack - Small Portable 4:4:4 Recorder <a href="http://www.mediaworkstation.com/articles/viewarticle.jsp?id=30380">Link</a><br />
- Arri &amp; Kodak Infrared-Based dust &amp; scratch removal system for ARRISCAN. <a href="http://www.arri.com/news/nab_2004/arriscan.htm#kodak">Link</a></p>

<p>Mark Cuban's HDNet signed Steven Soderbergh to direct six films using high definition which will be released "day and date" in theaters, on HDTV (HDNET) and in DVDs all on the same day. This is the first series of films with simultaneous releases in movie history. <a href="http://www.dcinematoday.com/dc/pr.aspx?newsID=246">Link</a></p>

<p>Stratton Camera becomes Fletcher rental agent for HD cameras in Detroit. <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=242">Link</a></p>

<p><br />
<b>May</b></p>

<p>Star Wars III <i>"Revenge of the Sith"</i> Episode III opens. The film was shot using "Sony's F950/HDCAM SR/Fujinon E-Series lenses via Plus 8 Digital and posted on Quantel iQ. <a href="http://www.studiodaily.com/filmandvideo/searchlist/4413.html">Link</a></p>

<p>Advances in film stocks and post production processes spark increase in Super 16mm for High Definition television show production. <a href="http://www.kodak.com/US/en/motion/16mm/why/filmMaker/hdtv.jhtml?id=0.1.4.3.8&lc=en">Link</a></p>

<p>Dalsa opens Digital Cinema Center in Los Angeles to showcase their 4K Origin camera to Hollywood production community. <a href="http://www.studiodaily.com/filmandvideo/tools/gear/4406.html">Link</a></p>

<p>Scripps Networks announces HGTV and Food Networks will begin broadcast in HDTV in January 2006. Scripps will double the hours of HD production it originally planned, with more than 1,000 hours scheduled for 2006. <a href="http://www.broadcastingcable.com/article/CA528601.html?display=Breaking+News&referral=supp">Link</a></p>

<p><br />
<b>June</b><br />
At Cine Gear Expo in Los Angeles, ASC Digital Master class conducts a side-by-side comparison of the Arri 435 film camera, Dalsa Origin, Panavision Genesis and the Arri D20. The event was organized by Bill Bennett ASC and lit by Russell Carpenter ASC with digital engineering provided by Marinder Snie. <a href="http://www.showreel.org/memberarea/article.php?32">Link</a></p>

<p>Michael Mann begins principal photography on "Miami Vice". Over the course of June to December in locations throughout Florida, South American and the Dominican Republic, the feature mixes Viper, F900, F950 and 35mm film cameras. <a href="http://www.imdb.com/title/tt0430357/technical">Link</a></p>

<p>Fletcher Chicago launches "Rent to Own" program to help customers transition into High Definition production and post production. 50% of the total spent on all rentals can be applied toward the purchase of Sony and/or Panasonic cameras, VTRs and/or CRT monitors. <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=247">Link </a></p>

<p>Soho Images, the London based film and Digital Intermediate lab takes delivery the first ARRISCAN now capable of scanning a 6K image from a 35mm negative. <a href="http://www.arri.com/news/newsletter/articles/09082005/capitalfx.htm">Link</a></p>

<p><br />
<b>July</b><br />
The Digital Signage Business, which involves sending out content to HD displays located in shopping malls and other public areas, is growing 40% annually with revenues at $1.4 billion. July featured two big deals. Thomson purchases Premiere Retail Networks for $284 million; during the same week, 3M purchases Mercury Online Solutions. Falling HD display prices are enabling retailers like Wal-Mart and Kroger to add multiple plasma and LCD displays at their 23,000 and 2,500 stores, respectively. <a href="http://www.broadcastingcable.com/index.asp?layout=nocclamp&doc_id=1340005570&cache=FALSE&preview=TRUE#NEWS2">Link</a></p>

<p>Consumer HDV Cameras arrive. Sony releases a sub $2000 consumer HDV 1080i camera - HDR-HC1. This is pretty impressive when you consider the price of Sony's previous five generations of HD camera systems. <a href="http://www.camcorderinfo.com/content/Sony-HCR-HC1-Review.htm">Link</a></p>

<p>Digital Cinema Initiatives (DCI) announces final overall system requirements and specifications. Agreement gives manufacturers of digital projectors and theater equipment one universal standard to create the next generation of cinemas. <a href="http://www.dcimovies.com/press/07-27-05.tt2">Link</a></p>

<p><br />
<b>August</b><br />
<i>"The Late Show with David Letterman"</i> goes on the air in HDTV August 29. <i>"The Tonight Show with Jay Leno"</i> has been available in HD since 1999 and <i>"Late Night with Conan O'Brien"</i> joined the HD club last spring. <a href="http://www.avsforum.com/avs-vb/showthread.php?t=568867&page=1&pp=30">Link</a></p>

<p>European broadcaster Sky reveals HDTV launch lineup which is scheduled to begin April 2006 will feature Sky Sports, Sky Box Office, two Sky movie channels and simulcast HD versions of Sky One and Artsworld. MTV, Discovery, History Channel and National Geographic's networks are also added later in 2005. <a href="http://www.digitalspy.co.uk/article/ds23757.html">Link</a></p>

<p>DVD format war inevitable. The peace talks broke off between to the two rivals High Definition DVDs format HD-DVD and Blu-ray. The two technology camps have held weeks of negotiations in an attempt to unify their formats, but negotiations fell through as neither side yielded. <a href="http://broadcastengineering.com/newsletters/bth/20050828/#war">Link</a></p>

<p>Fox Networks Group announced plans to offer a high-definition feed National Geographic Channel. NGC HD will launch in January 2006. For the past year, Nat Geo has been producing its original series in high definition in anticipation of the rollout.<a href="http://www.mediaweek.com/mw/news/cabletv/article_display.jsp?vnu_content_id=1001010946">Link</a></p>

<p>In response to the growing number of networks launching HD channels, HD EXPO, known for its VariCamp Workshops, announces a HD Broadcast Workshop covering news, magazine, and sports - ENG and EFP camera workshop. <a href="http://www.hdexpo.net/workshops/broadcast.html">Link</a></p>

<p><br />
<b>September</b><br />
With the HD transition finally beginning in earnest in Europe, this year's International Broadcasting Convention (IBC) hosted a number of HD focused events. Keynote speaker<b>, </b>David Hill, chairman of Fox Sports Inc. and president of the DirecTV Entertainment Group, ends HD address by commenting that "the future... is 3D". <a href="http://www.videsignline.com/products/170703551">Link</a></p>

<p>IBC HD Product Highlights<br />
- Thomson's Infinity 2/3" camcorder records to Iomega disks &amp; CompactFlash. <a href="http://www.thomsongrassvalley.com/news/2005/20050909-Infinity_Digital_Media_Camcorder.html">Link</a><br />
- Sony XDCAM HD 1/2" camcorder switchable between  18, 25 and 35 Mbps. <a href="http://www.studiodaily.com/main/news/5515.html">Link</a><br />
- Quantel Pablo Color Correction - faster than real time HD, 2K &amp; 4K workflow. <a href="http://www.uemedia.net/CPC/digitalcinemamag/article_13922.shtml">Link</a><br />
- Reference-grade, color-calibrated, full HD resolution LCD monitors from Cine-tal and e-Cinema. <a href="http://www.cine-tal.com/cine-tal.com/products/cinemage_overview.htm">Link</a></p>

<p>MTV announces MHD to begin broadcasts in January 2006. The channel will showcase content from various MTV Networks channels. Expected shows include <i>"MTV Unplugged"</i> and <i>"VH-1 Storytellers"</i>. MHD will also have some original content including HD music videos. Sees HD concerts as the next big thing. <a href="http://www.digitalspy.co.uk/article/ds24412.html">Link</a></p>

<p>Bob Primes ASC and Rick Maguire, ASC are first DP's to utilize Panavision's Genesis next generation HD camera system for a prime-time drama - <i>"NightStalker"</i>. <a href="http://www.uemedia.net/CPC/cinematographer/article_14209.shtml">Link</a></p>

<p><br />
<b>October</b><br />
"Saturday Night Live" goes HD and letterboxes standard definition broadcast. <a href="http://www.avsforum.com/avs-vb/showthread.php?postid=5540666">Link</a></p>

<p>Curtis Clark ASC, working with director Eric Steinman on <i>"Sing It"</i>, for LG Electronics, becomes the first DP to use the ARRIFLEX D-20 on a commercial. Clark comments the camera doesn't come from a video lineage. It's designed as a digital motion-picture camera. <a href="http://www.icommag.com/november-2005/november-page-2b.html">Link</a></p>

<p>Sony announces it is restructuring 75% of product to be High Definition by March 2008 - up from 35% in 2005. The HD focus is to be an overarching strategy bringing Sony's 4K digital cinema projectors to consumers, mating them with Blu-ray disks, with the Playstation 3, and with specialized content from Sony Pictures Entertainment. <a href="http://www.broadcastingcable.com/index.asp?layout=nocclamp&doc_id=1340005828&cache=FALSE&preview=TRUE">Link</a></p>

<p>World Series in HDTV with 28 cameras! While that is not necessarily noteworthy in of itself in 2005. What is newsworthy, according to FOX Sports Vice President of Field Operations Jerry Steinberg is HDTV has come of age. What was a science project has become television. <a href="http://broadcastengineering.com/newsletters/sports/20051028/World-Series-Fox-20051028/">Link</a>  <i>Chicago won in 4 games.</i></p>

<p>Paramount, which was among the earliest studios to announce HD-DVD support, said it now intends to release films in both formats, making it the first movie studio to do so. <a href="http://www.twice.com/article/CA6262984.html?display=Breaking+News">Link</a></p>

<p><b>November</b><br />
Disney and REAL D launch new digital 3D projection system in 85 theaters with release of <i>"Chicken Little"</i>. System uses a single projector displaying 144 frames per second alternating left and right eye images. Results reported in December: 3D screens generated nearly 3 times the revenue of the average 2D screen. <a href="http://www.reald.com/news_chicken_little.asp">Link</a></p>

<p>The HDTV launch of <i>"Good Morning America"</i> marks the first time ever that regularly scheduled commercial network news program will be air in HD. <a href="http://www.tvpredictions.com/gmahdtv110305.htm">Link</a></p>

<p>The BBC announces trial HD broadcasts set for 2006. "From colour and widescreen to digital radio and television, the BBC has always been at the forefront of innovations in broadcasting," said director of television, Jana Bennett. "Our promise to our license payers is to give them the highest quality television, so the time is right for the BBC to get involved in high definition. <a href="http://www.digitalspy.co.uk/article/ds26019.html">Link</a></p>

<p>HDTV-3D that does not require glasses introduced. <a href="http://www.hdtvmagazine.com/articles/2005/11/eds_view_-_hd3.php">Link</a> </p>

<p>Video games HDTV compatible. Microsoft's Xbox 360 beats Sony to market. <a href="http://www.crutchfieldadvisor.com/ISEO-rgbtcspd/reviews/20030930/TV-video_game_tvs.html">Link</a></p>

<p><br />
<b>December</b><br />
Fletcher Chicago adds film cameras to rental inventory investing over $1.5 million in Arricam LT, Arri 435 Xtreme, Arri 235, Zeiss' Master Primes, and Angenieux Zooms. Stan Glapa, veteran Midwest camera rental specialist, tapped to run division along with 20 year camera and optical technician, Al Collins. <a href="http://www.reelchicago.com/story.cfm?storyID=1097">Link</a></p>

<p>One of the missing tools for HDTV sports production (especially golf) has been a practical wireless HD camera system. ABC Sports successfully uses the LinkHD and Thomson LDK6000 on Monday Night Football. <a href="http://broadcastengineering.com/newsletters/sports/20051223/AVS-LinkHD-Thomson-20051223/">Link</a></p>

<p>History Channel announces HD service coming in 2006 <a href="http://www.digitalspy.co.uk/broadcasting/hdtv/">Link</a></p>

<p>Panasonic ships the first HVX-200 P2 camera December 29th. This was one of the most eagerly awaited HD products of the year since it records the full DVCPRO 100 format at a cost of under $6,000. It combines multiple HD and SD formats, recording modes and variable frames rates, and the vast benefits of P2 solid state memory recording in a rugged, compact design. <a href="http://www.tvtechnology.com/dailynews/one.php?id=3522">Link</a></p>

<p>Digital Cinema Announcements in December<br />
- Technicolor partners with major studios to install digital projectors into theaters, charging the studios what they would ordinarily have to pay for a film print (a "virtual print fee") until the projectors are paid off. In December adds Twentieth Century Fox along with previous agreements with "DreamWorks SKG, Sony Pictures Entertainment, Universal Pictures, and Warner Bros. Initial rollout to be 5000 DCI compliant screens over next few years primarily using Sony's 4K SXRD projectors and Grass Valley servers. <a href="http://www.digitalcinemareport.com/news/thomsontwentieth.html">Link</a></p>

<p>- Carmike Cinemas, the nation's third-largest chain, said it would convert 2,300 auditoriums by October 2007 using Christie Digital projectors. <a href="http://www.dcinematoday.com/dc/pr.aspx?newsID=369">Link</a></p>

<p>- Mark Cuban's, independent film friendly, Landmark Cinema announces plans to outfit 85 with Sony 4K SXRD projectors. <a href="http://www.indiewire.com/biz/biz_050316land.html">Link</a></p>

<p>Both the US House and Senate agree to set hard cut off date for analog television - February 17, 2009 (after Super Bowl and before March Madness Basketball). Includes $1.5 billion for a "digital-to-analog converter box program" to allow viewers without DTV receivers to obtain up to two, $40 converter-box coupons. <a href="http://www.tvtechnology.com/features/news/n_2009_squeak-01.11.06.shtml">Link</a></p>

<p>DG Systems' merger with FastChannel creates the first HD advertising distribution network. <a href="http://www.broadcastbuyer.tv/publish/article_6483.shtml">Link</a></p>

<p><b>2005 Year End Numbers:</b></p>

<p>276 Digital Cinema installations worldwide. <a href="http://www.technicolor.com/Cultures/En-Us/Locations/North+America/USA/CABurbank/BurbankDigitalCinema/TheatreDirectory.htm">Link</a></p>

<p>An estimated 8.9 million units of HDTV were sold in 2005. The Consumer Electronics Association estimates the total HDTV households at the end of 2005 to be at 20 million. <a href="http://www.fletch.com/feArticles.asp?NextAction=Article&AID=251">Link</a><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Thomas Fletcher</b>, <b>January 31, 2006  2:00 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(307)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Thomas Fletcher', 307)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Thomas Fletcher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/01/2005-high-definition-a-year-in-review.php" type="text/javascript" charset="utf-8"></script>
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
