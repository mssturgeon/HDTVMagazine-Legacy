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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Chris Russell'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Chris Russell" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 812 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 812
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Wired vs. Wireless Multimedia Connectivity&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php&amp;title=Wired vs. Wireless Multimedia Connectivity">
		<span style="display:none">The advent of high definition content with Blu-Ray and HD DVD, HD broadcast and stunning real time HD console gaming continue to drive the bandwidth requirements at a dramatic pace. The most recent specifications for supporting current and future HD content requires a bandwidth as high as 10.8Gbps (in the case of Display Port) and 10.2Gbps (in the case of HDMI). The first part of this article examines the driving forces behind the requirement for ever-increasing bandwidth. The second part of the article compares the bandwidth available with wired solutions and various wireless bands including UWB and 60GHz bands.

The key factors driving the requirement for increased bandwidth are...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 812";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Wired vs. Wireless Multimedia Connectivity" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Wired vs. Wireless Multimedia Connectivity" />
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
	<title>HDTV Magazine - Wired vs. Wireless Multimedia Connectivity</title>
	<meta name="keywords" content="data rate, data rates, high definition, effective data, ghz band, data, rate, bandwidth, content, high, rates, ghz, band, required, wireless, colour, copper, gbps, scheme, available, used, increasing, bits, viterbi, signal" />
	<meta name="description" content="The advent of high definition content with Blu-Ray and HD DVD, HD broadcast and stunning real time HD console gaming continue to drive the bandwidth requirements at a dramatic pace. The most recent specifications for supporting current and future HD content requires a bandwidth as high as 10.8Gbps (in the case of Display Port) and 10.2Gbps (in the case of HDMI). The first part of this article examines the driving forces behind the requirement for ever-increasing bandwidth. The second part of the article compares the bandwidth available with wired solutions and various wireless bands including UWB and 60GHz bands.

The key factors driving the requirement for increased bandwidth are..." />
	<meta name="title" content="Wired vs. Wireless Multimedia Connectivity" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=812', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php">Wired vs. Wireless Multimedia Connectivity</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Chris Russell</b> on <b>December 11, 2007</b>
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
				<p>This article examines the facts in the debate surrounding Wired versus Wireless Multimedia Connectivity.</p> <p><br><b>1. Introduction.</b></p> <p>The methods used to deploy High Definition video content are analysed in detail to determine the likely outcome in the ongoing Wireless Versus Wired debate.</p> <p>The article examines the outlook for wireless versus wired technologies from the perspective of achievable data rate, security, compression, interoperability and picture quality.</p> <p>The advent of high definition content with Blu-Ray and HD DVD, HD broadcast and stunning real time HD console gaming continue to drive the bandwidth requirements at a dramatic pace. The most recent specifications for supporting current and future HD content requires a bandwidth as high as 10.8Gbps (in the case of Display Port) and 10.2Gbps (in the case of HDMI). The first part of this article examines the driving forces behind the requirement for ever-increasing bandwidth. The second part of the article compares the bandwidth available with wired solutions and various wireless bands including UWB and 60GHz bands.</p> <p><br><b>2. Key Factors Driving Bandwidth Requirement.</b></p> <p>The key factors driving the requirement for increased bandwidth are:</p> <p>1) Increasing screen resolution and frame rates<br>2) Richer colour support<br>3) The continued requirement for the transmission of protected content in an uncompressed format; and<br>4) The migration of equipment such as HDTV's and consoles to display in progressive scan mode.</p> <p>2.1. Screen Resolution:<br>The HDTV market will evolve rapidly beyond the 720p and 1080p formats supported today to 1440p and beyond in the not-too-distant future. The requirement to drive even larger screen sizes is driven by the availability of display technology in the PC space. Current state of the art display technology is at WQXGA with 2560×1600 resolution. VESA has already defined a WQUXGA standard supporting resolutions of 3840 x 2400. Increasing screen resolution will continue to drive bandwidth requirements into the future. As end-users become aware of the dramatic visual difference experienced with progressive scan they will demand this as standard. Progressive scan doubles the requirement for channel bandwidth.</p> <p>2.2. Increased Frame rate<br>Current frame rates are 25, 50 and 60 Hz. This has been driven historically by the TV broadcast market. The main driver for increased frame rate comes from the console and PC gaming market where the HD content is being created on the fly. The benefit of moving from 60Hz to 120Hz frame rate whilst increasing screen resolution and colour depth has a direct impact on game play. Increasing frame rate allows the game player to drive faster without loosing frames or scene quality. New processor architectures such as the IBM's Cell are now fast enough to perform the required geometry and rendering calculations required to refresh the scenery at 120Hz. This should evolve as a standard feature as the developer community authors the required content to showcase the new levels of detail possible.</p> <p>2.3. Colour Depth.<br>Improvements in display technology combined with the constant demand for improving the end-user experience has driven the requirement to increase the number of colours displayed. 24 bit colour allows 8 bits to render the individual RGB components on an LCD panel. By increasing the number of colours to 36 or 48 bits there are now 12 or 16 bits available to render individual RGB components. This enables a dramatic increase in the quality level achieved for many typical movie or game scenes. Using 12 bits to display a clear blue sky allows a more realistic picture to be rendered, with over 2000 levels of blue available. The same scene rendered with 8 bits of blue allows only 256 blue levels which will cause colour banding. Visually the eye can detect the different shades of blue. With 36 or 48 bit colour depth the scene appears as a continuous range of blue which is much more pleasing to the eye. This greater colour depth also has a significant impact on the contrast ratio. Hollywood is already using 36bit colour and providing support for this in consumer electronics equipment allows this to be experienced in the home.</p> <p>2.4. Security &amp; Compression.<br>High Definition content is stored and broadcast in compressed format and decompressed by the DVD player or STB. When the outputs from these devices were Composite, S Video or SCART there weren't many concerns about security because there was no access externally to the native digital content that could be used for illegal duplication. The fact that the connection between the DVD, STB and HDTV is now digital raises security concerns as this digital content is available externally. This led the industry to introduce content protection for digital content, and movies are now encrypted using the HDCP copy protection scheme. However, the second piece of the puzzle in content protection is that if the content is transmitted in compressed format, there is an increased risk of it being captured and decrypted off line for subsequent distribution. Compression makes life easier for the pirate industry. Therefore Hollywood has a requirement that the digital content when exposed in this manner is in an uncompressed format. The sheer size of the content makes it impractical to store for offline decryption. For example a 2 hour 1080p movie would require 3 Terabits of exceptionally high-speed storage. The use of an uncompressed format clearly has a direct impact on the required data rate.</p> <p><br><b>3. Effect on Bandwidth Requirements.</b></p> <p>The overall effect of increasing screen resolution, frame rate, colour depth, the lack of compression and the drive to progressive scan as a standard feature is to drive the required bandwidth for connectivity to new levels. The latest HDMI specification, Version 1.3, has increased the link bandwidth from the previous 4.95Gbps to 10.2Gbps and the VESA Display Port 1.0 has chosen 10.8Gbps for the data rate required to satisfy the increasing need for greater bandwidth. These two standards will provide sufficient bandwidth for the next few years but will continue to be upgraded to cope with the requirements for further bandwidth.</p> <p><br><b>4. Copper Bandwidth.</b></p> <p>Data rates for copper cable links have increased dramatically over the years from very low data rates to the sophisticated SerDes implementations running into the multiple Gigabits per second. In some specific applications copper is likely to replace multi-Gig optical links. To achieve these high data rates with copper two main approaches have been taken to date. Industry bodies such as HDMI and VESA's DisplayPort use Single Data Rate (SDR) transmission with multiple data pairs in a single low-cost cable. Data rates of 10.2Gbps and 10.8Gbps are achieved with HDMI and Display Port respectively. Other copper-based standards such as Infiniband have taken this a step further with the use of Double Data Rate (DDR) and Quad Data Rate (QDR) communications to scale up the effective data rates for each pair within the cable. Fig. 1 below shows the achievable data rates for 1, 4 and 12 lane Infiniband when SRD, DDR and QDR techniques are used.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig1.gif"><br>Fig.1 Data rates achievable with SDR, QDR and QDR Techniques<br></center> <p></p> <p>This performance is achieved with the use of an NRZ encoding scheme used in conjunction with QDR and DDR techniques. These effective data rates can be increased by a factor of two and even further with multi-level signalling schemes such as PAM4 or PAM12. Instead of the transmitted data simply being a one or a zero, multiple signal levels are allowed. The effective data rates are thus scaled up again by a factor of 4 or 12 from the rates in Figure 1.</p> <p>Almost all of the copper transmission schemes discussed are capable of comfortably and cost-effectively delivering the bandwidth necessary to support high-definition video distribution.</p> <p><br><b>5. Wireless Bandwidth:</b></p> <p>Looking at the wireless world, three wireless bands are examined to determine available data rates for HD content distribution. These are 5GHz, UWB and 60GHz bands. The attainable data rates for these bands is dependent on numerous variables including:<br><br> <ul> <li>Signal to noise ratio <li>Modulation scheme <li>Viterbi encoding rate</li></ul> <p></p> <p>The signal to noise ratio is dependent on the background noise level and the allowable signal power transmitted within the frequency band in question. The modulation scheme determines the number of bits per Hz achievable in the band and the Viterbi encoding rate is a measure of the amount of redundant data required to enable the receiver to correct transmission errors. The lower the signal to noise level in a given band the more redundant data is required to achieve error correction.</p> <p>To calculate the throughput in a given band the available spectral bandwidth (allocated by FCC, EU etc) is multiplied by the number of bits per Hz transmitted. This gives a theoretical data rate in the absence of noise. This effective data rate is reduced to account for the Viterbi encoding scheme required for error correction.</p> <p>A 64 bit Quadrature Amplitude Modulation (QAM64) is used in the three cases examined. This enables 6 bits per Hz to be achieved. This is the maximum possible order QAM practical in these bands.</p> <p>This data rate is reduced by the Viterbi encoding rate. For example with a 1/3 Viterbi rate encoder the effective data rate is reduced by a factor of 3 with the additional of 66% redundant data to the transmission. As the signal to noise ratio increases the order of the Viterbi encoder is reduced. In the 60GHz band greater transmit power is available so a 3/4 rate Viterbi scheme is possible. The amount of redundant data required for error correction is reduced to 25% resulting in a reduction of the effective data rate by 25%. This calculation yields the maximum data rate possible assuming no other transmitter is in the vicinity. The detailed computations for each band are discussed below.</p> <p><br><b>5.1. 5GHz Band:</b></p> <p>This band has a high allocated transmit power because of the relatively low 20MHz bandwidth allocated to it as shown in Fig 1.</p> <p>The signal to noise ratio is high and a QAM64 is used for transmission. The data stream is processed using a 1/3 rate Viterbi encoding scheme for error correcting.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig2.gif"><br>Fig 1: Allocated Spectrum at 5GHz<br></center> <p></p> <p>QAM64 allows the simultaneous transmission of 6 bits per Hz so the total theoretical bandwidth is calculated as follows.</p> <p> <center>Bandwidth available 20MHz x 6 X 1/3 = 40Mbps</center> <p></p> <p>This assumes one transmit receive path. In the likely event there are multiple transmit receivers in operation this will be reduced by the number of channels in operation. This is achieved with time or frequency multiplexing. Two channels in operation will result of a halving to 20Mbps being available for each channel.</p> <p>This band clearly can not play a major role in the transmission of high definition video content given it's extremely limited channel bandwidth.</p> <p><br><b>5.2. Ultra Wide Band:</b></p> <p>UWB has a very low transmit power allocation due to the wide 1.3GHz spectrum allocated, and a 64 bit QAM scheme is possible as in the narrower 5GHz band above so 6 bits per Hz are transmitted. The carrier frequency can be between 5 to 10GHz as shown in Fig 2 below.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig3.gif"><br>Fig 2: Allocated Spectrum for Ultra Wide Band<br></center> <p></p> <p>A 1/3 rate Viterbi encoding scheme is required and the total theoretical bandwidth is thus:</p> <p> <center>1.3GHz X 6 X 1/3 = 2.6Gbps</center> <p></p> <p>Again this is for the case where one channel is in operation. The data rate is reduced significantly when multiple channels are in operation as would be the case if this technology was widely used.</p> <p>However even at 2.6Gbps data rate there is insufficient bandwidth to distribute an uncompressed 1080p movie with 24 bit colour.</p> <p><br><b>5.3. 60GHz Band</b></p> <p>The 60GHz band allows for a relatively high power transmission given the relatively uncluttered spectrum at this frequency. The allocated spectrum is 1.6GHz as shown in Fig 3. The field strength attenuation with distance from the antenna is exceptionally high at this frequency so directional antennas are required to ensure the maximum signal strength is directed at the receiver.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig4.gif"><br>Fig 4: Allocated Spectrum for 60GHz Band<br></center> <p></p> <p>This high signal strength enables the use of a 3/4 rate Viterbi encoding scheme and a QAM64 modulation scheme is used to achieve the effective data rate as follows.</p> <p> <center>1.6GHz X 6 X 3/4 = 7.2Gbps</center> <p></p> <p>The use of the directional antennas should allow multiple channels to be active without reducing the available bandwidth as in UWB. Each channel is spatially separated so the need for time or frequency multiplexing is no longer required.</p> <p>This clearly holds more promise in high definition video applications than UWB as the raw bandwidth is significantly higher. However, significant investment will be required by the semiconductor industry to bring the low-cost, low-power CMOS technologies to market which enable these solutions.</p> <p>There are disadvantages in that the antennas need to be aligned so the transmitter knows the location of the receiver. But at the 7.2Gbps maximum achievable bandwidth this delivers, it cannot compete with copper to keep up with the increasing demands from end-users and content developers. The bandwidth may be suitable to transmit lower resolutions of video with reduced colour depths and refresh rates, or video streams which compress the content. However, one significant application for the 60GHz band is to use it in a "Kiosk" mode where the end-user can purchase content in a store and download it extremely rapidly into a portable media player.</p> <p>"Nevertheless, the clear mobility advantages offered by wireless for the low end of high-definition media connectivity has generated a lot of interest, with multiple wireless platforms jostling for position. Despite the physical data-rate limits of the channel as discussed above, Wireless HD solutions abound. In pursuing this nirvana, a variety of schemes are in use, such as only supporting lower screen resolutions, colour depths and refresh rates, or the use of interlaced as opposed to progressive scan. Compression (to varying degrees) has also been used, in conflict with copy protection requirements and raising the issue of interoperability given the wide variety of codecs available for compression and decompression. In some wireless schemes, while high-resolution video is transmitted, the paired receivers ignore channel loss and only receive lower resolutions."</p> <p><br><b>6. Summary</b></p> <p>It is clear from the analysis of copper and wireless technologies that there is simply nothing to compete with the cost-effectiveness and raw data rates achievable with copper technologies. Wireless cannot be beaten for flexibility and mobility, but not where full rate, content-protected, uncompromised visual quality is a requirement - here copper has no match.</p> <p>Industry standards such as HDMI and Display Port are achieving the data rates required for the foreseeable future. The technologies used in these copper-based standards have significant room for further enhancement as can be seen from the examples of PAM encoding, DDR and QDR technologies already deployed in other areas. Wireless on the other hand will be close to the technology limit at 60GHz and is coming up short on the data rate and cost requirements for HD content distribution</p> <p><br><b>About the Author - Chris Russell</b></p> <p>Chris Russell is Director of Business Development at RedMere Technology based in Dublin, Ireland. He was previously Vice President of Sales and Marketing at OMI a successful Irish semiconductor equipment start-up acquired by a major US player. Prior to that, he was Director of Worldwide Business Development at Parthus Technologies. He spent ten years overseas including six years in Silicon Valley in various senior sales and marketing positions at Chips and Technologies, National Semiconductor and Cirrus Logic. He was also involved in a successful start up 3D graphics company that was subsequently acquired by Cirrus Logic. Chris has a primary qualification in Electronic Engineering from University College Dublin.</p> <p>Chris Russell [chris.russell@redmere.com]</p> <p>RedMere Technology is a privately-held fabless semiconductor company providing highly innovative communications solutions for mainstream consumer multimedia, PC and storage markets.</p> <p>RedMere's unique <a href="/cgi-bin.cgi?http://www.redmere.com/content/view/39/65/">MagnifEye&amp;trade;</a> signal processing technology introduces a step-change in the performance of high definition media and storage connectivity solutions, targeting applications such as High-definition TVs, Multimedia PCs, High-resolution Monitors, Personal Video Recorders and AV Receivers.</p> <p><a href="/cgi-bin.cgi?http://www.redmere.com/content/view/39/65/">MagnifEye&amp;trade;</a> offers RedMere's customers greater flexibility in system design, reducing deployment costs and increasing reliability of multimedia applications across the industry</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Chris Russell</b>, <b>December 11, 2007  9:19 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(812)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Chris Russell', 812)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Chris Russell</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php" type="text/javascript" charset="utf-8"></script>
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
