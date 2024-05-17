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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3387 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3387
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php&amp;title=High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services">
		<span style="display:none">This series of three articles analyzes the current and future market for HD, the methods of distribution and the capabilities of the digital technology to distribute HD content to meet consumer expectations. This technology must also support other quantity oriented businesses and services that can potentially degrade the original HD vision and affect those that invested in HDTV equipment under the reasonable expectation of viewing uncompromised HD quality, not just digital. This is a dilemma of quantity vs. quality when DTV permits the implementation of both, sharing the same bandwidth. 

HD content is defined in this article as...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3387";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" />
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
	<title>HDTV Magazine - High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services</title>
	<meta name="keywords" content="million million, content distribution, internet hdtv, cable satellite, cumulative end, content, million, cable, distribution, internet, digital, iptv, hdtv, video, quality, service, broadcast, dtv, satellite, analog, tvs, network, channels, resolution, services" />
	<meta name="description" content="This series of three articles analyzes the current and future market for HD, the methods of distribution and the capabilities of the digital technology to distribute HD content to meet consumer expectations. This technology must also support other quantity oriented businesses and services that can potentially degrade the original HD vision and affect those that invested in HDTV equipment under the reasonable expectation of viewing uncompromised HD quality, not just digital. This is a dilemma of quantity vs. quality when DTV permits the implementation of both, sharing the same bandwidth. 

HD content is defined in this article as..." />
	<meta name="title" content="High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3387', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php">High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November 23, 2009</b>
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
				<h2>Introduction</h2> <p>This series of three articles analyzes the current and future market for HD, the methods of distribution and the capabilities of the digital technology to distribute HD content to meet consumer expectations. This technology must also support other quantity-oriented businesses and services that can potentially degrade the original HD vision and affect those that invested in <a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HDTV</a> equipment under the reasonable expectation of viewing uncompromised HD quality, not just digital. This is a dilemma of quantity vs. quality when DTV permits the implementation of both, sharing the same bandwidth.  <p><a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HD</a> content is defined in this article as video originally recorded by HD video cameras or transferred from a film source at <a href="http://www.hdtvmagazine.com/glossary.php#1080i">1080i/p</a> or <a href="http://www.hdtvmagazine.com/glossary.php#720p">720p</a> resolution. The distribution of HD is subjected to the demand for that level of quality, the preservation of quality throughout the distribution channels, the competition among HD content providers and a reasonable cost/benefit to consumers, among many other factors.  <p> <h2>Market for HD Content</h2> <p>To view HD content at its full resolution a DTV needs to be capable of displaying 1080i/p or 720p without downgrading the original resolution of the image.  <p>According to Consumer Electronics Association (CEA)’s 2008 estimates, a third of the 346 million TVs in the US are DTVs that were sold between 1998 and 2008 (<a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">113.7 million)</a>. Most of those sets are HD capable, but many are of only SD/ED quality (480i/p).  <p>The other two thirds of TVs (about 230 million) are analog TVs that would require a set-top-box from a cable/satellite/Telco service provider or a broadcast DTV tuner to convert digital broadcast to analog due to the analog broadcast <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">discontinuation of June 12, 2009</a>.  <p>The 230 million TVs should not be considered a market for HD content until they <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">are replaced by HDTVs</a>. At a <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">selling pace of 35 million</a> DTVs per year a full replacement could not happen earlier than 2014. However, most consumers replace TVs when and if becomes necessary so it could take much longer for all TVs to be replaced, which affects the HD content distribution market.  <p>According to the CEA and the <a href="http://i.ncta.com/ncta_com/PDFs/NCTA_Annual_Report_05.16.08.pdf">NCTA</a> (2008 data), the 346 million analog/digital TVs are installed in 112.8 million US households, 65 million households <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">subscribe</a> to <a href="http://i.ncta.com/ncta_com/PDFs/NCTA_Annual_Report_05.16.08.pdf">cable</a>, 32.8 million to satellite and Telco, and the remaining 15 million receive broadcast with an over-the-air antenna.  <p>HD content is being distributed by all of the methods above. The Federal Communications Commission (FCC) allowed cable companies to offer subscribers an analog feed or to offer set-top-boxes for analog subscribers to tune to digital tier channels if the cable company prefers to fully switch to digital. Satellite services (small dish DirecTV/Dish Network) have been digital since they started, and offered HD content since 1999.  <p>Only those cable/satellite/Telco subscribers with an HDTV and an HDTV set-top-box can view HD at its full resolution. Viewing HD content at lower resolution should not be part of the HD content distribution market.  <p>A Nielsen research estimated that 23% of households <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">“have an HDTV and view HD</a>” while the <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">CEA estimated </a>months earlier that 50% of households are “able to experience the reality of digital television", which is not the same.  <p>Based on my analysis of the DTV industry since the transition started in 1998 I estimate that approximately 50 million households (44% of 112.8 million) may have at least one HDTV set, potentially qualifying for receiving distributed HD content even when not “viewing” HD today.  <p>Lower resolution TV channels gradually migrate to an HD format and motivate subscribers to upgrade analog/digital services to HD tiers and buy/lease HD-DVR equipment. Table 1 below shows a yearly summary of DTVs sold (and to be sold) to dealers since the beginning of the DTV transition (source: CEA, July 31, 2008):  <table border="1" cellspacing="0" cellpadding="0"> <tbody> <tr> <td width="59"> <h5>Year</h5></td> <td width="514"> <h5>DTV sets sold to dealers</h5></td></tr> <tr> <td valign="top" width="59"> <p>2011 </p></td> <td valign="top" width="514"> <p>40.8 million (cumulative end of year 2011, 228.7 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2010 </p></td> <td valign="top" width="514"> <p>38.4 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2009 </p></td> <td valign="top" width="514"> <p>35.8 projected million</p></td></tr> <tr> <td valign="top" width="59"> <p>2008 </p></td> <td valign="top" width="514"> <p>32.6 estimated million (cumulative end of year 2008, 113.7 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2007 </p></td> <td valign="top" width="514"> <p>26.4 million (cumulative end of year 2007, 81.1 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2006 </p></td> <td valign="top" width="514"> <p>23.5 million (cumulative end of year 2006, 54.7 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2005 </p></td> <td valign="top" width="514"> <p>11.4 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2004 </p></td> <td valign="top" width="514"> <p>8 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2003 </p></td> <td valign="top" width="514"> <p>5.5 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2002 </p></td> <td valign="top" width="514"> <p>4.1 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2001 </p></td> <td valign="top" width="514"> <p>1.5 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2000 </p></td> <td valign="top" width="514"> <p>0.6 million</p></td></tr> <tr> <td valign="top" width="59"> <p>1999 </p></td> <td valign="top" width="514"> <p>0.1 million</p></td></tr> <tr> <td valign="top" width="59"> <p>1998 </p></td> <td valign="top" width="514"> <p>0.0 million (the DTV transition started in November 1998)</p></td></tr></tbody></table> <h4></h4> <h2>HDTV Distribution Services</h2> <p><b></b> <p><u>Broadcast, Cable and Satellite</u></p> <p>Since its inception in the mid 1900s, TV traditionally performed a tune-and-display role in a world of broadcast-only tuning. In time, cable and satellite offered an alternative to broadcast content distribution.  <p>When premium content (e.g. HBO) arrived, in order to protect the investment and the effort of creating the content, service providers implemented security controls under a pay distribution model, which required the use of a STB to unscramble premium content, even if the analog TV might have been cable-ready.  <p>When turning the page from analog to digital, the video content distribution model grew with more features but also with <a href="http://www.hdtvmagazine.com/articles/2006/02/is_hdtv_complex_enough.php">more complexity</a> for equipment and connectivity, with CableCARDs, digital/analog conversions, image resolution controls, integrated digital DVRs, selectable output controls for <a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">content protection</a>, <a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php">digital audio</a> and <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php">video connections</a>, etc.  <p>While HDTV is part of that digital distribution model, HD was declared optional in the broadcast DTV system mandated by the US Government. That can potentially affect the market for HD content distribution.  <p><a href="http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">Integrated DTVs</a> have been gradually manufactured with internal over-the-air (OTA) tuners to comply with the FCC’s mandate proposed in 2002. Also in 2002 an agreement was made with the cable industry for DTVs to also integrate a QAM digital cable tuner for in-the-clear unscrambled programming.  <p>Some DTVs also included a CableCARD slot for the integrated <a href="http://www.hdtvmagazine.com/glossary.php#QAM+%28digital+cable+tuners%29">QAM</a> cable tuner to unscramble premium programming (e.g. HBO) without an STB. While digital cable STBs are bi-directional and support Video-on-Demand (VoD), Impulse Pay-Per-View (PPV), and cable supplied Electronic Program Guide (EPG), the cable tuners <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">integrated within</a> DTVs since 2003 are only uni-directional and cannot perform that STB functionality.  <p>Bi-directional integrated HDTVs with cable tuners (<a href="http://www.tru2way.com/">Tru2way</a>) were introduced in 2009 to support VoD and Impulse PPV without an STB, in addition to other two-way features.  <p><b></b> <p>Since 1998 DirecTV and Dish Network distributed satellite HD content with <a href="http://www.hdtvmagazine.com/glossary.php#MPEG-2">MPEG-2 </a>compression, including HD networks for their local markets. With the launching of additional satellites and a migration to more efficient MPEG-4 compression (which was claimed to be about 50% more efficient than MPEG-2) the satellite services <a href="http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php">increased the number</a> of HD channels (100+).  <p>Digital cable companies have also experienced considerable HD content growth over the past few years. However, the number of HD channels traditional cable companies offer has been generally lower than satellite due to the limited bandwidth of their coaxial network compared to adding dozens of transponders on new <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2007-satellite-cable-broadcast.pdf">satellite birds</a> sent to orbit, and implementing more efficient compression algorithms.  <p>Some service providers advertised their HD content as “1000+ program choices” (i.e. VoD movies) to fool subscribers as having more (24hr) HD channels than the competition. <p><u>Internet TV</u>  <p>Although Internet TV and IPTV share the basic concept of using Internet Protocol as a method of transmission for delivery of content, they should not be mixed as many casual journalists do.  <p>Shane kindly offered his contribution about this type of delivery:  <p>The term "Internet TV", or in this case "Internet HDTV", has become the commonly accepted term for referring to video content delivered via the internet. While the more technical readers may be aware that the internet is an IP network, Internet HDTV is not the same as IPTV.  <p>The primary differentiator between the two is that IPTV is delivered to the home over a "private" network. The programming provider controls delivery directly to the home (e.g. AT&amp;T's U-Verse). By comparison, "Internet HDTV" is delivered to the home over a "public" network, namely The Internet.  <p>Within the Internet HDTV category, video can be delivered in a number of ways:<br>- Streaming: YouTube, Hulu, Netflix<br>- Download (Progressive download): Apple TV, Microsoft Xbox Live, Video podcasts<br>- Peer-to-peer (P2P): VUDU  <p>For the consumer, the primary benefit of IPTV over Internet HDTV is quality. Since the content provider controls the signal and the network all the way to the home, it has complete control over the quality of the image.  <p>Cable or Satellite service, as well as closed-IPTV-networks, are services that are much more expensive to implement and maintain than using a basic Internet connection to view IP video from the open Internet, live or download.&nbsp; <p>On either service, if the content quality exceeds the transmission capacity (bandwidth) of the ISP service for live TV viewing, the option could be to wait for a download to be ready for later viewing, and that could be possible even with a dial-up connection over standard telephone wiring. <p><u>Internet Protocol TV (IPTV) </u> <p><a href="http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV </a>manages TV signals stored as digital files that can be distributed within packets using Internet Protocol (IP) within dedicated networks to the TVs at home.  <p>As mentioned above, although it uses a similar concept of Internet Protocol data packets, IPTV should not be confused with the transmission of video over the open internet, which in some cases is also (advertised as) HD quality, such as Hulu. <p>As opposed to unidirectional terrestrial DTV broadcasters, IPTV maintains a two-way communication with subscribers, which can generate new revenues from the same content by expanding the distribution to destinations other than regular antennas. <p>With new handheld/mobile devices implementing the recently approved DTV mobile broadcast standard it could be possible to broadcast/datacast unidirectional content to a portable device while it maintains a line-back communication with the broadcast provider using its non-DTV capabilities, such as the internet access offered by the cell phone service, closing the loop. <p>Some IPTV video companies deliver movies, TV shows and sports to subscriber’s PCs or handsets using the true IPTV concept. Others (miss)use the term IPTV to describe delivery service of triple-way telephone, high-speed Internet, and TV channels over a private hybrid network made of coax and fiber-optic cable, offering <a href="http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">most of the video</a> service over coax QAM cable, rather than as IP packets. Such service certainly does not follow the “pull” model mentioned below. <p>Some IPTV services supply only over-compressed live VoD, others only allow downloads for later viewing due to limited bandwidth for live HD, or offer real-time HDTV but with content that is severely re-compressed to fit bandwidth limitations, which is typically restricted and unable to meet the requirements of a live feed of “quality” HD. <p>Some service providers limit the number of simultaneous HD programs that can be viewed in a multi-TV household, or <a href="http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php">reduce the resolution</a> of simultaneous HD streams for secondary TVs when delivered in parallel to one active HD stream that is viewed on the main HDTV. Some systems delay the start of additional VOD movie requests when one is already active in the household. <p>A benefit of IPTV is that the infrastructure and system needs just enough bandwidth to deliver the selected (“pull”) content chosen by the viewer, as opposed to the traditional content distribution systems, where dozens of parallel HD channels are simultaneously delivered for the viewer to select one at the <a href="http://www.hdtvmagazine.com/glossary.php#STB">HD-STB</a> point. <p>In such case, an IPTV Telco may not need to upgrade the distribution infrastructure to add broader content variety to subscribers because an IPTV channel line-up could flexibly grow at the head-end independently of the distribution model. <p>In a similar manner, some cable companies are implementing Switched Digital Video (SDV) to deliver only the channel selected by the viewer (“pull concept”), facilitating channel line up growth at the head-end. <p>Although some companies <a href="http://www.hdtvmagazine.com/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">had trouble</a> on their implementations, IPTV is growing in Europe, Asia, <a href="http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">and the US</a>. <p>IPTV performance, such as image quality and channel change speed, should be monitored using QoS (Quality of Service) and QoE (Quality of Experience) techniques; the latter requires the viewer’s participation.  <p>Other IP methods of HD content distribution are the P2P (peer-to-peer) file sharing, and the BitTorrent protocol, capable of distributing HD content among a large number of viewers which PCs share the effort of parallel delivery without overloading the distribution capacity of the hardware/software infrastructure at the source. <p>Part 2 of this series analyzes the quality factors of HDTV content distribution.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November 23, 2009  9:27 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3387)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3387)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php" type="text/javascript" charset="utf-8"></script>
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
