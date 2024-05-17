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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3474 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3474
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2010/01/lg-electronics-redefines-home-entertainment-expectations-with-broad-line-of-stunning-slim-led-and-lcd-hdtvs.php&amp;title=LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs">
		<span style="display:none">INFINIA, an innovative new family of LED LCD HDTVs from LG Electronics that delivers &quot;freedom through infinite possibilities,&quot; highlights the company's 2010 lineup of LED LCD HDTVs introduced here today at the International Consumer Electronics Show (Booth #8205).

LG INFINIA HDTVs (the LE9500, LE8500 and LE7500 series) combine a slim design and thin bezel with enhanced connectivity and abundant content options. Leading the way to the ultimate home entertainment experience, the 55- and 47-inch class* LE9500 sets will be LG's first 3D-ready models available in the United States.

INFINIA is the flagship of...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3474";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs" />
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
	<title>HDTV Magazine - LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs</title>
	<meta name="keywords" content="inch class, class inch, inch diagonal, class sizes, led lcd, inch, class, diagonal, led, series, technology, sizes, lcd, full, home, entertainment, consumers, slim, hdtvs, picture, electronics, access, wireless, hdtv, options" />
	<meta name="description" content="INFINIA, an innovative new family of LED LCD HDTVs from LG Electronics that delivers &quot;freedom through infinite possibilities,&quot; highlights the company's 2010 lineup of LED LCD HDTVs introduced here today at the International Consumer Electronics Show (Booth #8205).

LG INFINIA HDTVs (the LE9500, LE8500 and LE7500 series) combine a slim design and thin bezel with enhanced connectivity and abundant content options. Leading the way to the ultimate home entertainment experience, the 55- and 47-inch class* LE9500 sets will be LG's first 3D-ready models available in the United States.

INFINIA is the flagship of..." />
	<meta name="title" content="LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2010/01/lg-electronics-redefines-home-entertainment-expectations-with-broad-line-of-stunning-slim-led-and-lcd-hdtvs.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3474', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/lg-electronics-redefines-home-entertainment-expectations-with-broad-line-of-stunning-slim-led-and-lcd-hdtvs.php">LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  6, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>, <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">LG Electronics Redefines Home Entertainment Expectations With Broad Line of Stunning, Slim LED and LCD HDTVs</p>

<center><i>New LED Technology Combined with Local Dimming Enhances Picture Quality, Black Levels and Utilizes Less Power</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 6 /PRNewswire/ -- </strong>INFINIA, an innovative new family of LED LCD HDTVs from LG Electronics that delivers "freedom through infinite possibilities," highlights the company's 2010 lineup of LED LCD HDTVs introduced here today at the International Consumer Electronics Show (Booth #8205).</p>

<p>LG INFINIA HDTVs (the LE9500, LE8500 and LE7500 series) combine a slim design and thin bezel with enhanced connectivity and abundant content options. Leading the way to the ultimate home entertainment experience, the 55- and 47-inch class* LE9500 sets will be LG's first 3D-ready models available in the United States.</p>

<p>INFINIA is the flagship of LG's 41-model LED LCD HDTV line - six new series of LED LCD HDTVs and five new series of LCD HDTVs. Leading these introductions are two new LED technologies - Full LED Slim and LED Plus - that provide cutting-edge picture quality. The unique backlight structure on its Full LED Slim models (LE9500 and LE8500) allows for the INFINIA line's ultra-slim depth without sacrificing picture quality. Together, these features provide consumers with infinite possibilities in home entertainment.</p>

<p>"We're removing barriers to entertainment with very slim LED LCD TVs that couple wireless connectivity with the most access to online content," said Peter Reiner, senior vice president, marketing, LG Electronics USA. "With seamless connectivity and limitless content, LG INFINIA is resetting the standards for design and entertainment as LED LCD TVs are expected to grow to more than 20 percent of the market this year."</p>

<p>"Consumers will no longer have to compromise on picture quality in order to enjoy an ultra-slim design. Together this new Full LED Slim technology and our wireless connectivity options allow consumers to 'live borderless' with the ultimate in content access and convenient installation," Reiner added.</p>

<p>LG's new Full LED Slim technology elevates picture quality with a slim LED structure that supports detailed local dimming of up to 240 addressable segments (on the 55-inch class LE9500), resulting in an HDTV that provides the deeper black levels and uniform picture quality which typically could not be achieved on an ultra-thin set.</p>

<p>The LE9500 series cabinet depth is only .92 inches with a bezel width of only 8.5mm. LG's LED Plus technology (available on the LE7500 and LE5500 series), also improves picture quality and energy efficiency by adding a basic local dimming capability of up to 16 addressable segments.</p>

<p>The LE9500, LE8500 and LE7500 were all recognized with CES 2010 Innovations Awards, including the "Best of Innovations" distinction in the Online Audio/Video Content category for the LE9500.</p>

<p><br />
<strong>Shattering Expectations</strong></p>

<p>Broadening consumer entertainment options, LG's latest series of HDTVs affords consumers superior picture quality, advanced energy saving options and flexible access to content-on-demand. LG's LED LCD HDTVs challenge consumers' current perceptions of home entertainment by illustrating what's possible with superior display technology.</p>

<p>LG's Full LED Slim series (models LE9500 and LE8500) for example, join an elite group of LED LCD HDTVs that have achieved THX Display Certification* - the industry standard for having the correct gamma, luminance, and color temperature. This certification demonstrates that select series of LG HDTVs can recreate the cinema experience at home, making the picture resemble movie theatre quality. To date, LG is the only manufacturer who has attained this designation for LCD TV in the U.S. market. LG is also the first manufacturer to include the "THX Bright Room" setting on its LED LCD HDTVs. This new feature to the THX certification program optimizes the contrast, gamma and other settings for watching movies in rooms with a lot of ambient light.</p>

<p>LG's exclusive Full LED Slim technology includes detailed local dimming capability, but also enables the LE9500 and LE8500 to achieve a slim depth usually limited to conventional edge-lit models. This unique technology makes it possible for these two models to achieve the picture quality worthy of THX Display Certification and helps minimize the front bezel of the TV. This works with the single, edge-to-edge panel of glass to create a design, perfect for any home environment. Boasting a thin bezel of only 8.5mm, the LE9500 brings advanced technology into the home without being obtrusive. Available in 55-and 47-inch class sizes*, this series also incorporates TruMotion 480Hz for reduced motion blur during fast moving action sequences.</p>

<p><br />
<strong>Connectivity</strong></p>

<p>LG's full line of LED LCD HDTVs ? series LE9500, LE8500, LE7500, LE5500 and LE5400 (in screen sizes 32-inch class and above) ? boast a connectivity package with a variety of entertainment options, including NetCastEntertainment Access(TM). With NetCast, consumers can access the following content sites for an almost endless array of entertainment options**:</p>

<p>  --  Skype(TM):  Newly added in 2010, this allows consumers to make free<br />
      video and voice calls over the Internet to family members and friends<br />
      (separate camera and other equipment needed).<br />
  --  Netflix(TM): Updated with Netflix 2.0, consumers can stream thousands<br />
      of movies without a PC.<br />
  --  VUDU(TM): Allows consumers to instantly buy or rent from an extensive<br />
      library of movies and TV titles, including a catalog of more than<br />
      3,000 high-definition movies - with no monthly fees or additional<br />
      hardware.<br />
  --  YouTube(TM): Offers the ability to instantly stream millions of Web<br />
      videos directly from the Internet (without a personal computer).<br />
  --  Napster(TM): Now Napster subscribers can enjoy unlimited on-demand<br />
      streaming music from millions of songs on their NetCast TV.<br />
  --  Yahoo! Widgets(TM): Enables access to various applications called TV<br />
      Widgets that allow viewers to interact with popular Internet services<br />
      and online media through applications specifically tailored to the<br />
      needs of the watcher, such as up-to-the minute Yahoo! News, Weather<br />
      and Finance, and new widgets, including CBS, Showtime and CNBC.</p>

<p></p>

<p>LG also has incorporated the Digital Living Network Alliance (DLNA) technology across the full line of LED models. DLNA allows consumers to access content stored on other DLNA-certified devices within the home, such as computers, making content options almost limitless.</p>

<p>Providing easy options for connecting to the Internet, in addition to the wired Ethernet jack, all NetCast-enabled sets can integrate into a wireless home network by using a USB wireless broadband adaptor (sold separately). All models with NetCast also support multi-media playback from a connected USB device including photos (JPEG), music (MP3) and video (DivX HD).</p>

<p>For greater convenience and flexibility in setup and installation, all HDTV series with NetCast also offer wireless Full HD 1080p wireless transmission from a "Wireless Media Hub" from up to 98 feet. Connecting source components, such as Blu-ray players, cable or satellite boxes and video games to the media hub enables transmission to a compact receiver adaptor, which attaches to the back of the TV, hidden from view. This eliminates the need for individual components to be connected directly to the TV, making for a clean and easy installation and removal of the unsightly wires (Media Hub and receiver adaptor sold separately as a package).</p>

<p><br />
<strong>Controls</strong></p>

<p>LG's LE9500 incorporates a unique "Magic Wand" remote system that provides an immersive interaction with the set. This "Magic" user interface brings together menus, component controls and even embedded games, which can be accessed using a simple remote that combines minimal buttons and gestures to control the on-screen activity, mirroring a "Wii-like" experience.</p>

<p><br />
<strong>Energy Savings</strong></p>

<p>Understanding consumers' desire for products that reduce their household energy costs, most of LG's LED and CCFL HDTVs have a variety of energy-saving features, such as Intelligent Sensor, to automatically calibrate and optimize brightness, contrast, white balance and color, based on the ambient light in the room, saving on energy output under most circumstances. Additionally, ISFccc calibration options allow consumers to work with a professional to set "day" and "night" levels for optimal viewing and brightness levels. All of LG's 2010 LED LCD series also qualify for ENERGY STAR® 4.0 certification.</p>

<p>In total, LG unveiled six new series of LED LCD HDTV models for consumers - creating a robust HDTV line up of advanced picture quality, wireless technology and diverse screen sizes. Full details on the series are below:</p>

<p>INFINIA LE9500 Series (47-, and 55-inch class sizes*) - Full HD 1080p HDTV features uni-layer design with ultra-slim (8.5MM) bezel, Full LED Slim technology with Local Dimming, TruMotion 480Hz and THX Display Certification. Also includes NetCast Entertainment Access, DLNA, wireless broadband ready capabilities, wireless HD ready and 2 USB ports.</p>

<p>INFINIA LE8500 Series (47-, and 55-inch class sizes*) - Includes all the same features of the LE9500 with the exception of the 3D capability, 8.5mm bezel and the Magic Wand remote control. It also features TruMotion 240Hz technology.</p>

<p>INFINIA LE7500 Series (42-, 47-, and 55-inch class sizes*) - Provides consumers with a Full HD 1080p experience, TruMotion 120Hz, and LED Plus technology for local dimming capability. With NetCast Entertainment Access, Wireless HD Technology content is easier to access than ever before without the fuss of too many cords.</p>

<p>LE5500 Series (22-, 26-, 42-, 47-, 55-inch class sizes*) - Includes TruMotion 120Hz, LED Plus Technology, and NetCast Entertainment Access. (Except 22- and 26-inch class sizes).</p>

<p>LE5400 Series (32-, 42-, 47, 55, 60-inch class sizes*) - Full HD 1080p HDTV series includes LED lighting, TruMotion 120Hz, NetCast Entertainment Access, Wi-Fi ready and two USB ports.</p>

<p>LE5300 Series (19-, 22-, 26-, 32-, 37-inch class sizes*) - Brings the slim profile of LED technology to smaller screen sizes. Thirty-two and 37-inch class sizes are Full HD 1080p and feature TruMotion 120Hz technology, while the 19-, 22- and 26-inch class sizes provide a 720p HD picture.</p>

<p><br />
<strong>Broad LCD HDTV Lines</strong></p>

<p>Expanding its line of LCD HDTV options, LG also introduced five new series of CCFL LCD HDTVs, which bring consumers a variety of technology, design, and energy-saving options. These series include:</p>

<p>LD650 Series (47- and 55-inch class sizes*) - A Full HD 1080p HDTV, this LCD HDTV series boasts TruMotion 240Hz performance for reduced motion blur. Other features include: NetCast Entertainment Access, USB Video DLNA, and Wireless-ready technology.</p>

<p>LD550Series (32-, 42-, 46-, 52-, and 60-inch class sizes*) - Also a Full HD 1080p series, these HDTVs are equipped with TruMotion 120Hz. Other features include: NetCast Entertainment Access, USB Video, DLNA, and Wireless-ready technology.</p>

<p>LD520 Series (32-, 42-, 47- and 55-inch class sizes*) - Combines Full HD 1080p with TruMotion 120Hz performance with a variety of screen sizes.</p>

<p>LD450 Series (32-, 37-, 42-, 47-inch class sizes*) - A Full HD 1080p HDTV in a variety of sizes to fit almost any room in the home.</p>

<p>LD350 Series (19-, 22-, 26-, and 32-inch class sizes*) - Provides smaller screen sizes for consumers in a 720p HD model.</p>

<p>With class sizes ranging from 19- to 60-inches, LG's LCD HDTV models provide a variety of flat panel options for any room in the home - most built with LG's four core technologies:</p>

<p>  --  Picture Wizard: Provides consumers with an easy-to-use seven-step<br />
      calibration process that allows them to change picture settings<br />
      without hiring an expert.<br />
  --  Intelligent Sensor: Automatically calibrates and optimizes brightness,<br />
      contrast, white balance and color, based on the brightness and color<br />
      temperature of lighting in the room - thereby saving on energy output<br />
      in most circumstances. (Excluding LD350 and LD450 series and the 19,<br />
      22, 26-inch class LE5300 models)<br />
  --  Clear Voice II: An enhancement to Clear Voice, this feature customizes<br />
      volume settings by 12 distinct voice zoom levels without diminishing<br />
      other surrounding sounds, helping ensure consumers don't miss a single<br />
      line of dialogue during action sequences.<br />
  --  AV Mode II: Includes three AV modes preset to optimize picture and<br />
      sound settings based on Cinema, Sports or Game content, which can be<br />
      easily set with the remote control.</p>

<p></p>

<p>For more information and product images, please visit LG's online press kit at <a target="_blank" href="http://www.lgusa.com/cespressroom/">www.lgusa.com/cespressroom</a>.</p>

<p><br />
<strong>About LG Electronics USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances and business solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.LGusa.com/">www.LGusa.com</a>.</p>

<p><br />
<strong>About LG Electronics, Inc.</strong></p>

<p>LG Electronics, Inc. is a global leader and technology innovator in consumer electronics, mobile communications and home appliances, employing more than 84,000 people working in 115 operations including 84 subsidiaries around the world. With 2008 global sales of $44.7 billion, LG comprises of five business units - Home Entertainment, Mobile Communications, Home Appliance, Air Conditioning and Business Solutions. LG is one of the world's leading producers of flat panel TVs, audio and video products, mobile handsets, air conditioners and washing machines. LG has signed a long-term agreement to become both A Global Partner of Formula 1(TM) and A Technology Partner of Formula 1(TM). As part of this top-level association, LG acquires exclusive designations and marketing rights as the official consumer electronics, mobile phone and data processor of this global sporting event. For more information, please visit <a target="_blank" href="http://www.lge.com/">www.lge.com</a>.</p>

<p>  **Internet connection and subscriptions required and sold separately.</p>

<p></p>

<p>  *55LE9500 55-inch class/54.6-inch diagonal<br />
  *47LE9500 47-inch class/47.0-inch diagonal<br />
  *55LE8500 55-inch class/54.6-inch diagonal<br />
  *47LE8500 47-inch class/47.0-inch diagonal<br />
  *55LE7500 55-inch class/54.6-inch diagonal<br />
  *47LE7500 47-inch class/47.0-inch diagonal<br />
  *42LE750042-inch class/42.0-inch diagonal<br />
  *55LE5500 55-inch class/54.6-inch diagonal<br />
  *47LE5500 47-inch class/47.0-inch diagonal<br />
  *42LE5500 42-inch class/42.0-inch diagonal<br />
  *60LE540060-inch class/59.5-inch diagonal<br />
  *55LE5400 55-inch class/54.6-inch diagonal<br />
  *47LE5400 47-inch class/47.0-inch diagonal<br />
  *42LE5400 42-inch class/42.0-inch diagonal<br />
  *32LE5400 32-inch class/31.5-inch diagonal<br />
  *37LE5300 37-inch class/37.0-inch diagonal<br />
  *32LE5300 32-inch class/31.5-inch diagonal<br />
  *26LE5300 26-inch class/26.0-inch diagonal<br />
  *22LE5300 22-inch class/21.6-inch diagonal<br />
  *19LE5300 19-inch class/18.5-inch diagonal<br />
  *55LD65055-inch class/54.6-inch diagonal<br />
  *47LD65047-inch class/47.0-inch diagonal<br />
  *60LD55060-inch class/59.5-inch diagonal<br />
  *52LD550 52-inch class/52.0-inch diagonal<br />
  *46LD550 46-inch class/45.9-inch diagonal<br />
  *42LD55042-inch class/42.0-inch diagonal<br />
  *32LD550 32-inch class/31.5-inch diagonal<br />
  *55LD520 55-inch class/54.6-inch diagonal<br />
  *47LD520 47-inch class/47.0-inch diagonal<br />
  *42LD520 42-inch class/42.0-inch diagonal<br />
  *32LD520 32-inch class/31.5-inch diagonal<br />
  *47LD540 47-inch class/47.0-inch diagonal<br />
  *42LD540 42-inch class/42.0-inch diagonal<br />
  *37LD540 37-inch class/37.0-inch diagonal<br />
  *32LD540 32-inch class/31.5-inch diagonal<br />
  *32LD350 32-inch class/31.5-inch diagonal<br />
  *26LD350 26-inch class/26.0-inch diagonal<br />
  *22LD350 22-inch class/21.6-inch diagonal<br />
  *19LD350 19-inch class/18.5-inch diagonal</p>

<p>  * Specifications subject to change without notice.<br />
  * THX certification is pending final testing and approval by THX Ltd.</p>

<p>Source: LG Electronics USA, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2010  9:22 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3474)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3474)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/lg-electronics-redefines-home-entertainment-expectations-with-broad-line-of-stunning-slim-led-and-lcd-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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
