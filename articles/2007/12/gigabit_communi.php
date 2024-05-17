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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'RedMere/Molex'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="RedMere/Molex" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 813 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 813
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php&amp;title=Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue">
		<span style="display:none">Multi-gigabit communications present many challenges to cable manufacturers. How can bandwidths higher than 10Gbps required by new standards such as HDMI™ and DisplayPort be achieved over low cost cables? What are the core technical problems with achieving these high data rates and what technologies can be used to address them? How can manufacturers achieve solutions which are less dependent on copper pricing? How can reliability issues be resolved without the need to use thicker cables? Cable manufacturing techniques have evolved to try to meet the challenge, but semiconductor solutions are emerging as promising alternatives and can be expected to play a significant role in solving these issues.

This article explains the physical problems faced by cable manufacturers, in particular...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 813";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" />
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
	<title>HDTV Magazine - Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue</title>
	<meta name="keywords" content="pair skew, intra pair, high frequency, cable manufacturers, gbps data, cable, data, skew, figure, eye, equalization, cables, technology, redmere, gbps, meter, adaptive, pair, manufacturers, high, problems, signal, frequency, cost, end" />
	<meta name="description" content="Multi-gigabit communications present many challenges to cable manufacturers. How can bandwidths higher than 10Gbps required by new standards such as HDMI™ and DisplayPort be achieved over low cost cables? What are the core technical problems with achieving these high data rates and what technologies can be used to address them? How can manufacturers achieve solutions which are less dependent on copper pricing? How can reliability issues be resolved without the need to use thicker cables? Cable manufacturing techniques have evolved to try to meet the challenge, but semiconductor solutions are emerging as promising alternatives and can be expected to play a significant role in solving these issues.

This article explains the physical problems faced by cable manufacturers, in particular..." />
	<meta name="title" content="Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=813', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php">Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>RedMere/Molex</b> on <b>December 19, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p>Multi-gigabit communications present many challenges to cable manufacturers. How can bandwidths higher than 10Gbps required by new standards such as HDMI&trade; and DisplayPort be achieved over low cost cables? What are the core technical problems with achieving these high data rates and what technologies can be used to address them? How can manufacturers achieve solutions which are less dependent on copper pricing? How can reliability issues be resolved without the need to use thicker cables? Cable manufacturing techniques have evolved to try to meet the challenge, but semiconductor solutions are emerging as promising alternatives and can be expected to play a significant role in solving these issues.  <p>This article explains the physical problems faced by cable manufacturers, in particular <i>differential skew</i> and <i>limited bandwidth</i>. The origins and implications for both problems are explained using eye diagrams. The article presents a silicon solution for these problems, discussing the challenges associated with circuits which automatically de-skew and the requirement for equalization to address the limited bandwidth problem. Data recovery is shown to be dramatically improved when adaptive de-skew and equalization is applied.  <p>Cost-effective silicon embedded in a cable bulk-head, combined with low-cost manufacturing techniques (high AWG cables, etc), provide cable manufacturers with a competitive solution in terms of performance and cost.  <p>Cable Manufacturers need to embrace semiconductor technology. Embedded silicon can answer the commercial and technical issues facing the industry today.  <p><b>The Cable Problem</b>  <p><b></b> <p>The main challenge for cable manufacturers is to solve the issue of propagation delay difference and high frequency suppression problems associated with the eternal need for increased data-rates. Propagation delay difference varies with cable length and dielectric constant and causes increased common-mode noise (cross-talk, EMI) and reduced transmission margin. High frequency suppression is a function of conductive loss (skin-effect and shield current) and causes increased rise times and reduced amplitude on the transmitted signal.  <p>To combat these effects, manufacturers need to optimize the selection of cable type (moving away from STP to TWINAX or SCTC), dielectric performance (use mechanical foaming) and conductive material (use solid and not stranded material and move to low AWGs). These solutions are expensive and bring other challenges to the cable (bulkiness, weight, rigidness, solder-cracking in connector, etc). An alternative approach is to consider cost-effective embedded semiconductor solutions such as RedMere's MagnifEye&trade; Repeater, MagnifEye&trade; Switch and Cable MagnifEye&trade; solutions which solve these <i>intra-pair skew</i> and <i>high frequency attenuation</i> problems for different cable applications<i>,</i> allowing cable manufacturers to work with thin low-cost cables such as 36 AWG.  <h4><br>Intra-pair Skew</h4> <p>Intra-pair skew exists in all systems where differential signals are transmitted. It is caused by differences in transit times or electrical path lengths for the positive and negative parts of a differential signal. These transit time differences ("skews") or electrical path length differences are caused by tolerances in the cable manufacturing process. The phenomenon is not well known because twisted pair have only recently been used for Gigabit data rates. At these data rates cables of three meters and beyond have differential skew times that are significant portions of the data bit times.  <p>To see where this "skew" time might come from we first note that signal propagation velocity along a twisted pair is approximately 0.71 times the speed of light which translates to approximately 47ps per cm. Thus for a ten meter cable the total delay is 47ns. Therefore a path length difference of just 1% within a ten meter cable causes an intra-pair skew of 470ps. We will show that this level of skew is disastrous in the context of 300-600ps bit-times. Figure 1 illustrates how a tiny change in the cable wrapping leads to a change in cable length, which then results in intra-pair skew.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image002.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="78" alt="clip_image002" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image002_thumb.jpg" width="556" border="0"></a></p> <p>Figure 1. Manufacturing quality affects path length in a twisted pair.  <p>It is also important to note that even if the manufacturing process produces perfectly matched lengths, this does not guarantee zero skew. Cables and PCB materials can have non-uniform dielectric constants due to variation in thickness and material properties. This results in variation in propagation velocity, which also changes the effective path length. The skew problems discussed can be exacerbated by bending or compression of the cable, effects which are almost guaranteed in many application environments.  <p>We have characterized hundreds of cables and found a wide variation in intra-pair skew ranging from 40ps on shorter cables up to 520ps on some 20 meter examples. Samples of cable measurements we have made are shown in Figure 2.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image004.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="276" alt="clip_image004" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image004_thumb.gif" width="390" border="0"></a><br>Figure 2. Measured intra-pair skew</p> <p>The impact of skew for a TV receiver is to directly reduce the timing budget available to the data recovery circuit to extract the data. Figure 3 below shows the impact of 155ps of skew on a 3.4Gbps data eye through three meters of Twinax cable. This is the HDMI&trade; specification limit for the skew at the receiver end of a cable.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image006.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="261" alt="clip_image006" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image006_thumb.jpg" width="557" border="0"></a></p> <p>Figure 3. Eye diagram at 3.4Gbps showing zero skew (top plot) and eye diagram with 155ps of intra-pair skew (lower plot).  <p>The top plot corresponds to a cable with zero skew and shows significant eye opening. This opening allows the data recovery block in the receiver sample the data over a 300ps window to decide whether a '1' or '0' is present. The lower plot shows that the addition of the 155ps of skew has reduced this valid data window to approximately 150ps thus making it virtually impossible for the data recovery block in the receiver.  <h5>Solution for Intra-pair Skew Problem</h5> <p>Redmere has patented a unique <i>adaptive de-skewing</i> technology to tackle the skew problem. This technology sits at the front end of the receiver chip and re-aligns the positive and negative portions of the differential signals. The re-alignment is done automatically using a combination of deep oversampling of the data bits and custom DSP. The result of this de-skewing block is seen below in Figure 4 where the top poor eye is reopened resulting in the data eye shown in the lower plot.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image008.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="274" alt="clip_image008" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image008_thumb.jpg" width="557" border="0"></a></p> <p>Figure 4. Eye diagram at 3.4Gbps showing degradation due to 155ps of skew (top plot) and improved eye diagram after processing by REDMERE's active de-skew circuitry (lower plot).  <h4>Inter Symbol Interference (ISI) or High frequency Attenuation</h4> <p>Because cables have multiple parallel lines there will invariably be some series inductance and parallel capacitance. These parasitic elements will filter the high frequency components of the signal. A measure of this effect is seen below in Figure 5. Here we see signal attenuation versus frequency for three and six meter cables.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image010.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="315" alt="clip_image010" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image010_thumb.gif" width="494" border="0"></a></p> <p>Figure 5. Signal attenuation versus frequency for three and six meter Twinax cables.  <p>When a signal is filtered by the cable, the data pulses of different lengths are shortened or lengthened and this degrades the data eye. This time domain degradation is seen in the eye diagrams of Figure 6 where we see the impact of passing data through a three meter Twinax cable (Top graph) and then further degradation when data is passed through six meters of Twinax cable.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image012.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="200" alt="clip_image012" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image012_thumb.jpg" width="557" border="0"></a></p> <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image014.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="204" alt="clip_image014" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image014_thumb.jpg" width="557" border="0"></a></p> <p>Figure 6. 3.4Gbps eye diagram at the end of three and six meters of Twinax cable.  <p>Cable equalizers compensate for the high frequency loss by applying gain to the high frequency components of the received signal. This process is seen in Figure 7 where the cascade of the cable transfer function and the equalizer transfer function produce a unity gain or all pass transfer function.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/image.png"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="159" alt="image" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/image_thumb.png" width="577" border="0"></a> </p> <p>Figure 7. Cascading of cable transfer function with an equalizer transfer function produces unity gain over all frequencies.  <p>If appropriate equalization is applied to the three meter data eye seen in Figure 6 then the resultant improved eye diagram is as can be seen in Figure 8.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image029.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="216" alt="clip_image029" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image029_thumb.jpg" width="558" border="0"></a></p> <p>Figure 8. 3.4Gbps data with appropriate equalization showing improved eye.  <p>If the same <i>fixed equalization</i> is applied at the end of a six meter cable then the result is a poor eye as shown in Figure 9. Thus the six meter cable is under-equalized and the level of eye closure here may well cause bit errors.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image031.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="217" alt="clip_image031" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image031_thumb.jpg" width="558" border="0"></a></p> <p>Figure 9. Poor 3.4Gbps data eye resulting from <i>fixed equalization</i> scheme applied to six meter cable.  <p>For this reason, <i>fixed equalization</i> is not generally a quality solution and there is a requirement for tuning the equalization as the cable length changes. A simple implementation of this tuning process is referred to as <i>programmable equalization</i>. In this case, tuning of the equalizer chip is achieved by setting or resetting external pins. These pins enable the selection of different transfer functions for the equalizer. This process may work in certain situations with external test equipment selecting the correct settings on the chip, but the ideal solution is where the chip tunes the equalizer parameters itself. This is referred to as <i>adaptive equalization</i>. <i>Adaptive equalization</i> changes the transfer function of the equalizer to automatically cancel the attenuation caused by the cable.  <p>It is also worth noting that some receiver chips with fixed equalization claim that they a suited for particular length cables. This claim, while partially true, ignores the variation associated with different cable technologies. This variation is clear from Figure 10 which shows different levels of attenuation found in 11 different five meter cables from different manufacturers.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image033.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="398" alt="clip_image033" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image033_thumb.gif" width="579" border="0"></a></p> <p>Figure 10 Attenuation versus frequency measured in 11 cables from different manufacturers.  <p><b>MagnifEye</b><b><sup>TM</sup></b><b> Technology from Redmere </b> <p>Our solution to the cable limitations is to do both <i>adaptive de-skewing </i>and<i> adaptive equalization </i>in tandem, i.e. using our patented MagnifEye<sup>TM</sup> technology. This block sits at the front end of our HDMI products. MagnifEye<sup>TM</sup> technology very effectively tunes the receiver to the specific cable connected. This gives the optimal reception of HDMI signals across longer cables and improves operating margins on shorter ones.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image035.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="187" alt="clip_image035" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image035_thumb.gif" width="383" border="0"></a></p> <p>Figure 11 MagnifEye<sup>TM</sup> Technology Block Diagram  <h4></h4> <p>The following section shows the impact of using both <i>adaptive de-skewing </i>and<i> adaptive equalization</i>. It is also clear from the following sequence that both are necessary.  <h4>Importance of Equalization and De-skew</h4> <p>The first scope shot (Figure 12) shows a closed eye when 2.275Gbps data is passed though a 15 meter cable with 300ps of skew.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image037.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="247" alt="clip_image037" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image037_thumb.jpg" width="352" border="0"></a></p> <p>Figure 12 2.275Gbps Data at the end of 15 meters of cable with 300ps of skew.  <p>Clearly there is no chance of recovering this data in this raw state. A standard analog front end would add equalization at this stage to improve the eye. The result of this can be seen below in Figure 13.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image039.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="247" alt="clip_image039" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image039_thumb.jpg" width="352" border="0"></a></p> <p>Figure 13 2.275Gbps Data at the end of 15 meters of cable with 300ps of skew with adaptive equalization applied  <p>This signal has a wider eye opening but it is clear from the relative eye closure that the subsequent data recovery system would result in many bit errors. Standard front-ends available today are doomed to failure when required to deal with 300ps of skew as equalization is the only tool on offer. Fortunately MagnifEye<sup>TM</sup> technology has another weapon in its arsenal; it also applies <i>adaptive de-skewing </i>to the same data, the result of which is shown in Figure 14.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image041.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="247" alt="clip_image041" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image041_thumb.jpg" width="352" border="0"></a></p> <p>Figure 14 2.275Gbps Data at the end of 15 meters of cable with 300ps of skew with <i>adaptive equalization</i> and Magnifye<sup>TM </sup>'s <i>adaptive de-skewing.</i>  <p>Now the data has clear open eyes and is perfectly conditioned for the data recovery block.  <h4>Summary</h4> <p>This article has shown the challenges in sending up to 3.4Gbps through several meters of cable. Two of the key cable challenges, namely "differential skew" and "Inter symbol Interference" have been introduced and their impact on cable performance demonstrated. Both these problems reduce the valid data eye, but on cheaper cables the eye is completely closed and data is rendered unrecoverable. One solution to these problems is to use more expensive cable technology which will typically result in a thicker and less flexible cable. An alternative is to consider embedded silicon combined with lower cost bulk cable.  <p>Redmere's patented MagnifEye<sup>TM</sup> technology is such a solution, combining circuit solutions for each of the problems into one elegant core applicable to a variety of cable applications. Whether the application is a multi-port cable repeater, externally powered cable or a cable powered off internal power, MagnifEye&trade; provides optimal signal integrity for lowest cost cables. With MagnifEye<sup>TM </sup>technology, cable manufacturers can deliver cable assemblies which meet the data requirements of today's market in a cost-effective manner.  <p><b>About the authors</b>:  <p>Dr. John Horan (<a href="mailto:john.horan@redmere.com">john.horan@redmere.com</a>), Chief Technology Officer and a Co-Founder of Redmere Technology. Previously he was an IC Architect with the Wireline Communications Division of Ceva Inc., based in Cork, Ireland. He has authored numerous technical papers and has had 6 US patents issued, with others pending.  <p>Atsuhito Noda (<a href="mailto:Atsuhito.Noda@molex.co.jp">Atsuhito.Noda@molex.co.jp</a>) is Director of New Technology Development for Molex's Global Micro Product Division and is based in Japan. He previously worked in Connector Engineering for 27 years and has 45 patents.  <p>Deirdre Mathelin (<a href="mailto:deirdre.mathelin@redmere.com">deirdre.mathelin@redmere.com</a>) is Product Manager for RedMere's HDMI&trade; semiconductor products and is based in Paris, Francea. She has 21 years semiconductor experience, having previously worked in semiconductor design for Infineon, ST Microelectronics and Ceva.  <p>David McGowan (<a href="mailto:david.mcgowan@redmere.com">david.mcgowan@redmere.com</a>), Applications Manager, based in Cork, Ireland, is responsible for RedMere's high-speed interface semiconductor application development. Prior to RedMere, David worked for Panasonic TV Group, Apple Computer and Ceva.  <p><b>About RedMere Technology</b>  <p>Headquartered in Balbriggan, Ireland, RedMere is an innovator in driving architecture and semiconductor solutions for high speed multimedia interconnect applications for consumer electronics and personal computing markets. For more information, please visit <a href="http://www.redmere.com/">www.redmere.com</a>.  <p><b>About Molex Incorporated</b>  <p>Molex Incorporated is a 69-year-old global manufacturer of electronic, electrical and fiber optic interconnection systems. Based in Lisle, Illinois, USA, the company operates 54 manufacturing facilities in 19 countries. The Molex website is <a href="http://www.molex.com">www.molex.com</a>.  <p><b></b> <p><b></b> <p><b>Contact Details:</b></p>
RedMere Technology Ltd.,<br />
2B Fingal Bay Business Park,<br />
Balbriggan,<br />
Co. Dublin,<br />
Ireland<br />
Tel: +353 1 841 0920<br />
Fax: +353 1 690 4196<br />
<a href="http://www.redmere.com">www.redmere.com</a><br />
</p><p>
Molex Japan Co. Ltd.,<br />
1-5-4 Fukamihigashi,<br />
Yamato,<br />
Kanagawa,<br />
242-8585 Japan<br />
Tel: +81 46 261 4500<br />
Fax: +81 46 264 1470<br />
<a href="http://www.molex.com">www.molex.com</a><br />
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>RedMere/Molex</b>, <b>December 19, 2007  7:20 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(813)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('RedMere/Molex', 813)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About RedMere/Molex</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php" type="text/javascript" charset="utf-8"></script>
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
