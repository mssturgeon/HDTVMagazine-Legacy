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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1607 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1607
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=VIZIO Expands Flagship XVT Series of HDTVs&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php&amp;title=VIZIO Expands Flagship XVT Series of HDTVs">
		<span style="display:none">VIZIO, America's HDTV and Consumer Electronics Company, unveils several new models to the XVT Series expanding the performance line with five NEW models. The most dramatic technology introduction is the use of LED panels featuring 240Hz with Scanning Backlight and VIZIO's Smooth Motion II(TM) technology in their largest model the 55&quot; VF551XVT. LED backlight technology provides better picture quality with 1,000,000:1 Mega Dynamic Contrast with local dimming. LED also provides faster response times and higher color saturation so you achieve a better picture. It is also environmentally responsible because it is mercury free. The VIZIO VF551XVT also has 5 HDMI Inputs and a speaker bar designed into the TV so you achieve more robust full body sound from a flat panel TV with SRS TruSurround HD(TM), and TruVolume(TM).

They are also expanding their collection of 42&quot; and 47&quot; XVTs to include...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1607";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Expands Flagship XVT Series of HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Expands Flagship XVT Series of HDTVs" />
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
	<title>HDTV Magazine - VIZIO Expands Flagship XVT Series of HDTVs</title>
	<meta name="keywords" content="srs trusurround, mega dynamic, xvt series, refresh rate, hdmi inputs, xvt, new, srs, contrast, models, trusurround, truvolume, dynamic, led, mega, model, series, performance, technology, hdmi, rate, lcd, audio, sound, inputs" />
	<meta name="description" content="VIZIO, America's HDTV and Consumer Electronics Company, unveils several new models to the XVT Series expanding the performance line with five NEW models. The most dramatic technology introduction is the use of LED panels featuring 240Hz with Scanning Backlight and VIZIO's Smooth Motion II(TM) technology in their largest model the 55&quot; VF551XVT. LED backlight technology provides better picture quality with 1,000,000:1 Mega Dynamic Contrast with local dimming. LED also provides faster response times and higher color saturation so you achieve a better picture. It is also environmentally responsible because it is mercury free. The VIZIO VF551XVT also has 5 HDMI Inputs and a speaker bar designed into the TV so you achieve more robust full body sound from a flat panel TV with SRS TruSurround HD(TM), and TruVolume(TM).

They are also expanding their collection of 42&quot; and 47&quot; XVTs to include..." />
	<meta name="title" content="VIZIO Expands Flagship XVT Series of HDTVs" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1607', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php">VIZIO Expands Flagship XVT Series of HDTVs</a></td>
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
				<p class="prtitle">VIZIO Expands Flagship XVT Series of HDTVs</p>

<center><i>- New arsenal includes 240Hz models from 42" thru 55", a 55" LED HDTV and Slim 120Hz 32" and 37" LCD HDTVs

<p>- New 55" 240Hz is VIZIO's First LED LCD HDTV featuring 1,000,000:1 Mega Dynamic Contrast for exceptional contrast along with 240Hz Refresh Rate with Scanning Backlight and VIZIO Smooth Motion II(TM) technology</p>

<p>- New 42" and 47" models feature 240Hz Refresh Rate with Scanning Backlight and VIZIO Smooth Motion II(TM) technology to deliver the most incredibly smooth picture and rich detail</p>

<p>- All new models include NEW VIZIO Multi-Media feature with USB input compatible to play MPEG-2, H.264 & WMV9 Video, JPEG Photos and MP3 Music from Thumb Drive or FAT32 Hard Drive, they also come with 1080P content loaded onto a 1GB USB Drive so you can enjoy 1080P video right out of the box!</p>

<p>- New Ultra Slim (2.5" depth) 32" & 37" 120Hz models provide exceptional picture and sound performance with 50,000:1 Mega Dynamic Contrast Ratio, and SRS TruVolume and TruSurround HD</i></center><br /><br />
<br /></p>

<p><br />
<B>LAS VEGAS and IRVINE, Calif., Jan. 7 /PRNewswire/ -- (CES 2009)</B> -- VIZIO, America's HDTV and Consumer Electronics Company, unveils several new models to the XVT Series expanding the performance line with five NEW models. The most dramatic technology introduction is the use of LED panels featuring 240Hz with Scanning Backlight and VIZIO's Smooth Motion II(TM) technology in their largest model the 55" VF551XVT. LED backlight technology provides better picture quality with 1,000,000:1 Mega Dynamic Contrast with local dimming. LED also provides faster response times and higher color saturation so you achieve a better picture. It is also environmentally responsible because it is mercury free. The VIZIO VF551XVT also has 5 HDMI Inputs and a speaker bar designed into the TV so you achieve more robust full body sound from a flat panel TV with SRS TruSurround HD(TM), and TruVolume(TM).</p>

<p>They are also expanding their collection of 42" and 47" XVTs to include 240Hz with scanning backlight using VIZIO's Smooth Motion II(TM) technology and Mega Dynamic Contrast ratio of 50,000:1. The 42" SV421XVT and 47" SV471XVT are capable of outstanding realism and are close to eliminating motion blur-once and for all. All new XVT models integrate the latest SRS sound technologies like SRS TruSurround HD(TM), and SRS TruVolume(TM), for a complete all-in-one home theater solution.</p>

<p>"VIZIO is committed to providing the absolute latest advanced technologies to consumers at a value. Consumers expect great performance and style from VIZIO" says Laynie Newsome, VIZIO Co-Founder and VP Sales and Marketing Communications. "You will see this demonstrated in the many new products we are introducing at CES, our prestigious XVT Series exemplifies what we believe is the BEST performing leading edge HDTV technology for consumers."</p>

<p><br />
<B>The Best in Video Performance</B></p>

<p>Launching two compelling new technologies, the expanded XVT Series offers more advanced features than any previous VIZIO product line with a complete line of Full HD 1080p performance coupled with both 120Hz and 240Hz with Scanning Backlight.</p>

<p>Using advanced LED backlight control local dimming, the VF551XVT is capable of delivering the brightest highlights and the deepest blacks. Coupled with VIZIO's Mega Dynamic Contrast Ratio(TM) these sets can provide a contrast ratio that rivals the performance of the best plasma displays. The use of local dimming to increase overall contrast ratio and picture quality, especially in brightly lit rooms, allows these LED models to exhibit deep, three-dimensional-like images.</p>

<p>VIZIO's Smooth Motion II(TM) technology steps up the game with 240Hz Refresh Rate in the SV421XVT and SV471XVT for unbelievably flawless video -- frame after frame by eliminating motion blur from fast-action movie sequences.</p>

<p>Every LED and LCD model in the series is outfitted with the Advanced Glare Polarizer (AGP) and Enhanced In-Plane Switching (IPS). AGP provides up to 60% better contrast ratio in bright room environments. Earlier Anti-Glare screens tended to create an overall haze on the TV image; however, VIZIO's solution counteracts the effects of ambient light, while keeping images bright and crystal clear. By reducing the reflections that are produced by ambient light, the overall picture contrast can be preserved. IPS produces truer colors at wider viewing angles ensuring vivid and clear pictures even when sitting off-axis.</p>

<p>VIZIO's advanced HD/SD noise reduction removes noise and artifacts caused by signal compression from cable and satellite providers. Each model is outfitted with multiple HDMI inputs for maximum connectivity with DVD/Blu-ray players, digital cable and satellite set-top boxes, gaming consoles, and HD camcorders. All XVT series sets have a Game Port on the side of the set with inputs (including HDMI) for fast, easy connection of other devices.</p>

<p><br />
<B>Sound Advancements</B></p>

<p>VIZIO's XVT Series is not just about superior video features and technologies. Every model is equipped with audio enhancement technologies from SRS Labs to ensure the best possible audio performance from built-in speakers. The 55" VF551XVT and VF550XVT1A take audio performance to a new level with the integration of a Sound Bar that delivers 30W of power (15W x2-speakers), enhanced by SRS TruSurround HD(TM) and TruVolume HD(TM). All the other new XVT models (SV320XVT, SV370XVT, SV422XVT, SV472XVT) also take advantage of SRS TruSurround and TruVolume.</p>

<p>SRS TruSurround HD(TM) makes it possible to enjoy realistic surround sound without running cables or adding additional speakers. Providing a suite of processes, TruSurround HD uses the improved SRS TruBass(TM) to optimize the low frequency performance of the VIZIO Sound Bar for cleaner bass tones. SRS Dialog Enhancement ensures crisp and intelligible vocals, while Definition Control delivers maximum high-frequency realism and clarity.</p>

<p>SRS' latest innovation, TruVolume HD(TM) allows customers to set their volume level at a pre-defined position. Regardless of the content, be it a quiet movie passage or a loud commercial, there are no annoying volume fluctuations. SRS uses Intelligent Multi-band Monitoring and Analysis to establish a consistent volume while delivering a more natural listening experience. There is no pumping, breathing, or clipping artifacts that is commonly associated with traditional Automatic Gain Controls (AGCs). The circuit works flawlessly with mono, stereo and even multi-channel sources.</p>

<p><br />
<B>Thoughtfully Earth-friendly</B></p>

<p>VIZIO is committed to making more eco-friendly products and designs. To that end, the VF551XVT use LED panels rather than CCFL panels. LED panels do not use mercury, and are highly efficient requiring significantly less power.</p>

<p>Also being introduced in these same models is Dynamic Power Control (DCP), which lowers power consumption by up to 15% more than Energy Star 3.0 requirements without sacrificing picture quality. DCP analyzes image data to optimize the amount of backlight produced, minimizing image distortion. According to CNET tests, TVs that meet Energy Star 3.0 requirements can save up to $100 a year, so it is possible to save even more using VIZIO's latest products with DCP.</p>

<p>VIZIO guarantees zero bright pixel defects for the duration of the limited one-year warranty on all their products. Additionally, they provide free one-year on-site service and lifetime technical support.</p>

<p><br />
<B>XVT Models and Features</B></p>

<p>  Model: SV320XVT *NEW*<br />
  Size: 32" 120Hz FULL HD 1080P LCD HDTV<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 120 Hz<br />
  HDMI inputs: 3<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Fall 2009<br />
  Price: $749.99</p>

<p>  Model: SV370XVT *NEW*<br />
  Size: 37" 120Hz FULL HD 1080P LCD HDTV<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 120 Hz<br />
  HDMI inputs: 3<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Fall 2009<br />
  Price: $999.99</p>

<p>  Model: SV421XVT *NEW*<br />
  Size: 42" LCD<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 240 Hz<br />
  HDMI inputs: 4<br />
  Game Port: Yes<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Summer 2009<br />
  Price: $1099.99</p>

<p>  Model: SV471XVT *NEW*<br />
  Size: 47" LCD<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 240 Hz<br />
  HDMI inputs: 4<br />
  Game Port: Yes<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Summer 2009<br />
  Price: $1399.99</p>

<p>  Model: VF551XVT1A *NEW*<br />
  Size: 55" LED<br />
  Contrast: 1,000,000:1 Mega Dynamic Contrast Ratio<br />
  Refresh Rate: 120 Hz<br />
  HDMI inputs: 5<br />
  Game Port: Yes<br />
  Audio Enhancements: Integrated Sound Bar, SRS TruSurround HD & TruVolume<br />
  Availability: Summer 2009<br />
  Price: $1999.99</p>

<p></p>

<p>The new models can be seen at VIZIO's private presentations at the WYNN Hotel's La Tache Ballrooms during the International CES in Las Vegas from January 7-10, 2009.</p>

<p><br />
<B>About VIZIO</B></p>

<p>VIZIO, Inc. "Where Vision Meets Value," headquartered in Irvine, California, is America's HDTV Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead major categories in U.S. TV sales. VIZIO is committed to bringing feature-rich flat panel televisions to market at a value through practical innovation. VIZIO offers a broad range of award winning Plasma and LCD HDTVs including the new XVT series. VIZIO's products are found at Costco Wholesale, Sam's</p>

<p>Club, Sears, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Good Housekeeping's Best Big-Screens, CNET's Top 10 Holiday Gifts, PC World's Best Buy among others. For more information, please call 888-VIZIOCE or visit on the web at www.VIZIO.com.</p>

<p>The V, VIZIO, Where Vision Meets Value names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>Source: VIZIO, Inc. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  7:13 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1607)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1607)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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
