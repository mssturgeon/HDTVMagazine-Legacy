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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3312 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3312
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Portable TV and the Haier HLT717&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php&amp;title=Portable TV and the Haier HLT717">
		<span style="display:none">This story actually starts with a DVD Audio player! DVD Audio is a defunct HD audio format from 2001 (along with SACD) that brings the master recording to your home. Unfortunately the DVD forum and mastering houses failed in execution of this new standard making many of the titles auto play for multi-channel only, not stereo; an irritating premise for a 2 channel audiophile minimalist requiring a video monitor to navigate the menus to the stereo tracks. Indeed, DVD Audio listening time was few and far between due to this hassle.

What my DVD Audio world needed was an inexpensive, small LCD display with quick and convenient disconnects...</span></a>
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

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3312";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Portable TV and the Haier HLT717" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Portable TV and the Haier HLT717" />
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
	<title>HDTV Magazine - Portable TV and the Haier HLT717</title>
	<meta name="keywords" content="dvd audio, mmc card, usb input, dtv reception, dtv tuner, antenna, portable, dtv, cable, reception, haier, input, power, audio, products, dvd, usb, small, channel, card, video, digital, tuner, analog, ntsc" />
	<meta name="description" content="This story actually starts with a DVD Audio player! DVD Audio is a defunct HD audio format from 2001 (along with SACD) that brings the master recording to your home. Unfortunately the DVD forum and mastering houses failed in execution of this new standard making many of the titles auto play for multi-channel only, not stereo; an irritating premise for a 2 channel audiophile minimalist requiring a video monitor to navigate the menus to the stereo tracks. Indeed, DVD Audio listening time was few and far between due to this hassle.

What my DVD Audio world needed was an inexpensive, small LCD display with quick and convenient disconnects..." />
	<meta name="title" content="Portable TV and the Haier HLT717" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3312', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php">Portable TV and the Haier HLT717</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>October  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=277&category=HDTV Displays">HDTV Displays</a></b>
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
				<p>This story actually starts with a DVD Audio player! DVD Audio is a defunct HD audio format from 2001 (along with SACD) that brings the master recording to your home. Unfortunately the DVD forum and mastering houses failed in execution of this new standard making many of the titles auto play for multi-channel only, not stereo; an irritating premise for a 2 channel audiophile minimalist requiring a video monitor to navigate the menus to the stereo tracks. Indeed, DVD Audio listening time was few and far between due to this hassle.</p>

<p>What my DVD Audio world needed was an inexpensive, small LCD display with quick and convenient disconnects, but in 2002 that was a tough nut to crack. LCD has come a long way since then so I decided to see what a local brick and mortar store might have to offer in 2009. My natural preference was a display with full HD A/V inputs. The smallest size available was a 15" 720p Dynex over at Best Buy. This tempted me into a custom application in the form of a wall mount and holes in the wall for cabling yet also created a hassle factor of pulling the AC plug and video cable during listening. That led me to the portable TV category.</p>

<p><br />
<strong>General Features of Portable TV</strong></p>

<p>Like so many things related to marketing I can't help but wonder why this category remained portable TV rather than changed to portable DTV. This category label is bound to bite a used electronics purchaser who could unwittingly end up with an NTSC-only product.</p>

<p>There are a number of products available. The common feature set is a DTV tuner, standard RF antenna connector, remote (very small card type), mini 3.5mm jacks for A/V input and headphones, battery pack and AC wall wart power supply/charger with some including a car charging adapter. Typical battery life is 1.5 hours. Most provide a telescoping antenna attached directly to the RF jack with a handful providing a small stick antenna on a magnetic base with an RF cable to the RF jack. Some include a USB and/or card reader slot for PC pictures, music and video. The old analog NTSC standard is supported by many. Some serve double duty as fully featured digital photo frames.</p>

<p>DTV has been riddled with over the air reception problems since inception and is the most glaring problem with these products per customer reviews. Based on the use and expectation of performance of portable TV products of yesteryear, these are bound to disappoint. Our old NTSC analog system was far more robust because it was far more forgiving. Multipath problems and signals buried in noise were still useful especially on little screen sizes, creating nothing but momentary visual blips of noise and even under severe conditions at least you could hear the sound. Analog beats digital hands down as an emergency service for the public. These same problems wreak havoc on digital because blips in the stream of data kill picture and sound and if reception is too poor then you get nothing at all. Like Murphy's Law, these reception blips will happen during a climatic event in your program raising your blood pressure. While much has been done on the receiver end and many local broadcasters are still updating or modifying their transmitter and/or antenna locations, vast improvement can only come with major changes in the system covered by colleague Ed Milbourn in his article <a href="/columns/2009/03/eds_view_hdtv_broadcast_wish_list.php">HDTV Broadcast Wish List</a>. Indeed, the portable TV category has a new competitor; the <a href="/forum/viewtopic.php?t=11488">ATSC Mobile/Handheld</a> standard being developed around cell phone products. A number of portable TV products noted that they are designed for stationary use only, not mobile.</p>

<p>Knowing all this I found it ironic that current portable TV products still use outdated antenna technology in the form of a multi-directional telescoping antenna or the similar stick antenna. This was one product line where I fully expected to see <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=10289">Smart Antenna</a> technology implemented, if not for the benefit of the consumer and their own brand name, then to at least reduce product returns. Bottom line is your portable TV won't seem so portable and convenient if you have to haul a separate and much larger antenna design along with it. Numerous reviews pointed out this need for a better and more directional antenna design and the reception improvements gleaned by providing one.</p>

<p>This DTV reception problem is very disconcerting related to local news and announcements during emergencies. This is not the analog TV experience of yesteryear! If buying this product for that purpose you should test your reception right away. For now and the near future, many of us will get better results with an old fashioned analog AM and FM radio that can run off of batteries for these events.</p>

<p><br />
<strong>Available Products</strong></p>

<p>The following list of portable TV products provides general information only to help get you started. All are 7-inch 16:9 480x243 screens except for one 10-inch as noted. Price ranges from $90-$140 except for that 10-inch with an MSRP of $200. Some USB inputs may be mini and/or require a cable or adapting USB mini/USB cable. Technical details, specifications and a descriptive owner's manual can be difficult to find. I highly recommend you check reviews and manufacturer websites when looking for specific features.</p>

<ul><li>CTA TV-P7 - SD/MS/MMC card reader</li><li>Eviant T7-01</li><li>Envizen EF70701 - USB input, SD/MS/MMC card reader</li><li>Envizen EF71001 10" Digital Photo Frame 800x480 - No A/V input or battery power, USB input, SD/MS/MMC card reader</li><li>Axion AXN-8701</li><li>Viore PLC7V95 - USB input, SD/MS/MMC card reader</li><li>iView 780PTV - USB input, SD/MS/MMC card reader</li><li>Tivax HiRez7 - USB input, SD/MS/MMC card reader, can use standard batteries in a pinch</li><li>Digital Prism 7" LCD TV</li></ul>

<p><br />
<h2>Review: Haier HLT717</h2></p>

<p><strong>Features</strong></p>

<ul><li>7-inch LCD screen, 480x243</li><li>ATSC DTV tuner, Analog Cable tuner, Digital Clear QAM cable tuner</li><li>ATSC DTV Electronic Programming Guide</li><li>RCA composite video and stereo audio connectors</li><li>Manual 4:3 or 16:9 aspect ratio control</li><li>3.5mm headphone jack</li><li>Remote</li><li>Tripod Mount</li><li>Main power switch</li><li>Sleep timer</li><li>Folding kick stand</li><li>Standard RF antenna connector</li><li>Telescopic antenna with snap in storage slot on TV (must be disconnected)</li><li>OEM rechargeable battery pack</li><li>AC wall wart power supply and charger</li></ul>

<p>I eventually came across this portable TV from Haier conveniently down the street at my local Target for $129. It was small enough to sit on one of my shelves, had RCA A/V inputs for composite video and ran on a battery along with a main power switch to completely kill it. Beyond my DVD Audio application it has a DTV tuner, AC power supply/charger, remote, optional antenna, standard RF coax input and headphone jack.</p>

<p>My first amusing test was checking over-the-air (OTA) DTV reception; amusing because my location is awful and the optional antenna for portable use is an omni directional telescoping antenna. I went outside on my deck and as expected the Haier failed to find anything worthy. During the scan it was able to detect the two VHF channels and none of the UHF. Next step was using my Silver Sensor UHF antenna. Unfortunately the positioning mechanism for the telescopic antenna was a tight fellow and I was unable to unscrew it by the hex nut connector alone without a wrench so I did it the way I put it on; grabbed the stiff antenna along with the nut to loosen it. This is not something you easily pop on and off. With the Silver Sensor connected the Haier detected all the DTV stations but only one UHF channel would pass muster making it into memory. Outdoors the remote sensitivity was quite poor requiring it be within about 2 feet of the display to respond reliably. Outside the Haier stayed locked to this one channel but moving the whole mess into the house on the other side of a window, a four foot difference, made reception unstable and sensitive to my physical location nearby. While a Smart Antenna design would probably do little for my location it would be far better than the telescoping omni-directional rod antenna. The product does not offer a signal analysis interface in the menu to help you with a marginal reception problem.</p>

<p>Moving on to the right antenna for my location the Haier performed just as well as my DTV tuner and handled one fringe station better. If you select over the air you are stuck with DTV reception. When selecting cable you engage an old NTSC cable tuner along with a Cable Clear QAM tuner. Channel auto-programming went quite fast compared to other products tested and tuning was also faster than expected when surfing through all the digital QAM channels. Best news is the Haier utilizes two memory slots, one for DTV and one for cable, and you can change from one tuning system to the other by simply changing the reception mode in the menu. This is a great feature if the Haier is going to serve a dual role in your home with cable service and portable DTV outdoors. If you still have some local VHF stations transmitting in NTSC you should be able to pick them up using the cable tuning mode but there is nothing this TV can do with UHF except DTV. Another great feature is the ability to directly tune the transmitting channel number; if received and properly captured it will go into memory automatically. This means you can select that station and move your antenna around to see if you can capture the signal even if it was missed on the auto scan. It is not convenient that the antenna has to be removed and snapped in place for on board storage.</p>

<p>Portable TV is not about video performance. If you are sensitive to lip sync this TV may irritate you as it appears no audio delay was included in the design. Every channel had the problem to the same degree. Overall color balance and factory settings looked fine. At this size the pixel matrix is limited and while finding that spec is like pulling nails it appears all the 7 inch panels revolve around 480x243. You have to hit 10 inches to get an ED, Extended Definition, pixel matrix of 800x480 and that size is very rare in this category. It appeared anything that could earn a 720p rating came at a significantly larger size along with a power cord only such as that 15" Dynex at Best Buy. Nonetheless the limited 7" display had enough legibility for text from DTV or a DVD player. Text font types in DVD menus though could be troublesome at times. The resolution limit of the panel creates artifacts. With HD you can get moire artifacts depending on content. The vertical resolution of 243 lines affects both SD and HD content creating an artifact of fine horizontal lines through out the screen triggered by vertical pans somewhat similar to interlaced analog TV. Angled views had far more affect on black level and light output rather than discoloring the image, a plus.</p>

<p>While I read numerous complaints about tinny sounding audio these users clearly have unreasonable expectations; there isn't much to be done about that at this small size and I found the itty bitty speakers to sound just as I would have expected.</p>

<p><br />
<strong>Haier Conclusion</strong></p>

<p>If you are looking for a small DTV for a small application the Haier just may have you covered due to the cable system capabilities and the RCA input jacks. If on satellite you can pick up your VHF channel 3 or 4 NTSC RF feed by switching to cable mode or grab some RCA cables and use the A/V input.</p>

<p>As a portable TV, keep your expectations realistic and be prepared to try an antenna that provides some margin of directional ability to overcome multi-path. The proper antenna for your location is the key to stable DTV reception. While there are clearly distance limitations with a small antenna you could also be close to the towers yet <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=6914">swamped with multi-path</a> preventing reception. Some larger yet still plausible alternatives are the <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=3378">Silver Sensor</a> or <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=4289">DB2</a>.</p>

<p><br />
<strong>So How About that DVD Audio Player?</strong></p>

<p>The Haier worked great for this application. The RCA A/V inputs created my quick disconnect and universal convenience. The size was just right and the display fit quite nicely between the shelves in an open space in front of the PS3. LCD displays are RF noise makers and as a minimalist audiophile the main power switch allowed me to leave the video cable attached while completely killing operation of the product right down to the standby power supply and micro awaiting a power command to turn on. Font legibility was on the edge but good enough to navigate the menus.</p>

<p>I would have loved to try out that 10 inch Envizen Digital Photo Frame due to the ED resolution but it had no A/V input and required an AC power cord...</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>October  8, 2009  9:39 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3312)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 3312)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php" type="text/javascript" charset="utf-8"></script>
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
