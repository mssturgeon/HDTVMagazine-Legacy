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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 2925 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 2925
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=LG Electronics Showcases New Products with \'Unparalleled Performance by Design\' for Holiday Season&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php&amp;title=LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season">
		<span style="display:none">Led by the new SL80 and SL90 HDTVs with their seamless design and ultra-thin bezel, LG Electronics highlighted its latest collection of home entertainment, home appliance and mobile communications products at the company's 2009 holiday preview event.

During its annual Summer Line Show today LG showcased...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 2925";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics Showcases New Products with \'Unparalleled Performance by Design\' for Holiday Season" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics Showcases New Products with \'Unparalleled Performance by Design\' for Holiday Season" />
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
	<title>HDTV Magazine - LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season</title>
	<meta name="keywords" content="class inch, inch class, inch diagonal, home entertainment, blu ray, inch, home, consumers, access, new, features, performance, entertainment, led, offers, class, electronics, full, hdtv, lcd, technology, design, mobile, picture, system" />
	<meta name="description" content="Led by the new SL80 and SL90 HDTVs with their seamless design and ultra-thin bezel, LG Electronics highlighted its latest collection of home entertainment, home appliance and mobile communications products at the company's 2009 holiday preview event.

During its annual Summer Line Show today LG showcased..." />
	<meta name="title" content="LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=2925', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php">LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 30, 2009</b>
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
				<p class="prtitle">LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season</p>

<center><i>First 'Seamless' LCD HDTVs Unveiled</center></i><br />
<br />

<p><strong>NEW YORK, July 30 /PRNewswire/ -- </strong>Led by the new SL80 and SL90 HDTVs with their seamless design and ultra-thin bezel, LG Electronics highlighted its latest collection of home entertainment, home appliance and mobile communications products at the company's 2009 holiday preview event.</p>

<p>During its annual Summer Line Show today - at New York City's newly renovated Alice Tully Hall in Lincoln Center for the Performing Arts - LG showcased its products' "unparalleled performance by design" and latest technological innovations.</p>

<p>"Across our brand portfolio, LG products combine innovative features, intuitive functionality, exceptional performance and stylish design to provide consumers with something better than the ordinary home entertainment experience," said Michael Ahn, president and CEO, LG Electronics North America. "Our featured Summer Line Show products, from our 4-Door French-Door Refrigerator with automatic open/close drawers, to our mobile phone with touch screens, and our advanced new SL80/90 HDTVs, demonstrate how together innovation and design produce exceptional performance."</p>

<p>LG Electronics continues to expand the content-on-demand options for consumers by announcing a new alliance with VUDU, building on its collaborations with Netflix, CinemaNow, Yahoo! and YouTube. Through this new alliance, LG is giving consumers access to an extensive online library of high-definition movie titles. (See separate release for additional details)</p>

<p><br />
<strong>Home Entertainment</strong></p>

<p>Leading the charge with HDTV innovations, such as extensive broadband capabilities and enhanced picture quality, LG Electronics is further broadening its home entertainment product family with the unveiling of the SL80 and SL90 series of LCD HDTVs.</p>

<p>Now, there's an LCD HDTV that will truly turn heads whether the screen is on or off. The SL80's advanced technology and seamless edge-to-edge panel - over an ultra-slim bezel - establish a new benchmark in the possibilities of LCD technology. Available beginning in August, the SL80 is designed to deliver exceptional picture quality, sporting a 150,000:1 contrast ratio for greater color detail and deeper blacks. It also offers TruMotion 240Hz to handle fast-moving images at lightning speed.</p>

<p>Expanding its stylish line of seamless HDTVs, LG also unveiled LED versions, the SL90 series, which will be available later this year. At only 1.15 inches thick the ultra-slim models add LED technology for the ultimate in picture quality and further energy savings.</p>

<p>LG home entertainment products featured at the summer line show include:</p>

<ul><li>LHX Slim Wireless LED Backlight HDTV (Class Size: 55-inch*): LG's LHX offers superior picture quality with an elegant ultra-slim design - less than one-inch thick at its thinnest point. The HDTV uses a full array of LED backlights, which employ local dimming techniques for precise picture control, resulting in deeper blacks, wide color gamut and smooth motion, achieving 240Hz performance for more natural picture clarity. In addition, the LHX offers the convenience and flexibility of wireless transmission, allowing source components to be placed up to 30 feet away yet still providing uncompressed, Full HD 1080p picture signal transmission.</li><li>LH90 LED Backlight HDTV (Class Sizes: 55-, 47-, and 42-inch*): As the first LCD to receive THX Display Certification in the U.S. market, LG's LH90 series of LCD HDTVs employs TruMotion 240Hz technology, allowing for a more satisfying viewing experience while delivering images that move more naturally. The LH90 LCD HDTV series also features LED backlighting, which employs local dimming techniques for precise picture control.</li><li>LH50 Full HD 1080p LCD HDTV with NetCast(TM) Entertainment Access (Class Sizes: 47-, and 42-inch*): Using LG's first-ever HDTV with Ethernet connectivity, consumers can access even more content and video on their big screen TV without the need for a computer. This connectivity offers access to Netflix Instant Streaming, Yahoo! Widgets, YouTube, VUDU**, and access to music and photos stored on a home PC, so consumers have a choice when it comes to entertainment on a single device.</li><li>PS80 Full HD 1080p Plasma HDTV with NetCast(TM) Entertainment Access (Class Sizes: 60- and 50-inch*): LG's first plasma HDTV with Ethernet connectivity allows consumers to access even more content and video on their big screen TV without the need for a computer. This connectivity offers access to Netflix Instant Streaming, Yahoo! Widgets, YouTube**, and can also access music and photos stored on a home PC.  The PS80 also features THX Display Certification.</li><li>LG BD370 Network Blu-ray Disc Player: The BD370 offers consumers broadband connectivity and advanced audio capabilities with audio format decoding, such as Dolby TrueHD/Digital Plus and DTS-HD for a crisper, clearer auditory experience. Other features include Full HD 1080p Blu-ray disc playback with BD-Live and BonusView, and NetCast(TM) Entertainment Access, which includes Netflix instant streaming, instant access to the latest movie titles from CinemaNow, and a world of entertainment options with YouTube access**.</li><li>LG BD390 Network Blu-ray Disc Player: The BD390 boasts all the same core functionalities of the BD370 model but takes performance and connectivity a step further, featuring integrated wireless home networking for easy connection to the home network, and 1GB of built-in memory, offering consumers a simpler option for enjoying BD-Live content from their favorite Blu-ray movies without the need for a flash drive. Discrete 7.1 channel audio outputs offer exceptional connectivity and performance.</li><li>LHB953 Blu-ray Home Theater System: The perfect complement to any movie enthusiast's home entertainment system, the LHB953 Network Blu-ray Home Theater System is equipped with a 5.1 surround sound system and LG's Netcast(TM) Entertainment Access, which offers consumers multiple content-on-demand options including instant streaming from Netflix, YouTube(TM) and LG's latest content provider, Pandora  Internet radio**, right out of the box. For those that enjoy pure high definition radio, the LHB953 combines Pure HD audio performance with Dolby Digital Plus, Dolby True HD and dts-HD Advanced Digital Audio Out for an uncompressed next-generation audio experience for your home theater.</li><li>LHB977 Blu-ray Home Theater System: The LHB977 includes similar features to the LHB953, including NetCast Entertainment Access, but also features two HDMI inputs for exceptional connectivity to high-def, sources such as cable boxes and game systems, two tallboy speakers, and two stylish satellite speakers that offer superior audio performance and complement any home theater.</li><li>M237WD: The M237WD offers consumers flexibility as it doubles as a monitor and a full 1080p HDTV. It delivers premium picture performance for viewing videos or using graphic-intensive applications with its 30,000:1 contrast ratio. Its two HDMI inputs make it a multimedia centerpiece, allowing consumers to easily and conveniently connect to other digital devices. A remote control completes the package.</li><li>W2286L LED Monitor: LG's newest LCD monitor features an LED backlit display for advanced performance with a stylish, color-infused design. With 'Full HD' 1080p resolution and a 2,000,000:1 digital fine contrast ratio, the W2286L sets a new standard for desktop monitors.</li><li>N2R1 Network Attached Storage: LG's N2R1 is the first-ever Network Attached Storage with a built in DVD burner. Compatible with Windows, Linux and Mac operating systems, the NAS unit also offers advanced data management. With its simple and clean design, the N2R1 is perfect for consumers looking for the one destination to access and back all their data files.</li></ul>

<p><br />
<strong>Mobile Communications</strong></p>

<p>With the latest in touch screen technology, advanced music, Bluetooth 2.0, solar power technology, and full line of QWERTY devices for every carrier, LG Mobile Phones continue to bring style, function, and performance to the wireless industry, meeting the demands of today's mobile consumer.</p>

<p>Mobile phones and Bluetooth accessories featured at the summer line show include:</p>

<ul><li>XENON(TM): The LG XENON(TM) is the model of mobile innovation with a large touch screen, enhanced flash user interface that makes menus, shortcuts, and contacts available right at your fingertips.  The two mega pixel camera and slide-out QWERTY keyboard allows users to quickly send text messages and emails.</li><li>NEON(TM): The LG NEON(TM) is a compact and colorful phone that lets users easily stay connected to their social circle with its external touch screen, slide-out full QWERTY keyboard and two mega-pixel camera.</li><li>Glance(TM) : Style-oriented consumers will respond to the sophisticated design and ultra-slim profile of the new LG Glance, which boasts an elegant, woven metal back plate, easy-to-read two-inch display, enhanced Bluetooth capability, one-touch speakerphone, speaker-independent voice command and a 1.3 mega pixel camera with picture messaging.</li><li>enV3: Text messaging fanatics can't wait to get their thumbs on the new LG enV3, a slim and feature-packed handset with a compact QWERTY keyboard, 2.6-inch internal screen, three mega-pixel camera and camcorder, Bluetooth stereo capability, a music player, and favorites key that enables users to connect with their ten most frequent contacts in a flash.</li><li>enV TOUCH: The new LG enV TOUCH combines undeniable style with an unrivaled multimedia experience by offering features such as its three-inch external touch screen, full-size QWERTY keyboard and 3.2 mega pixel camera with built-in flash, Dolby  Mobile sound, and document reader.</li><li>LX370: LG LX370 is an easy-to-use vertical slider, providing users with a sleek powerful device including a 2.0 mega pixel camera, Stereo Bluetooth, MP3 player with microSD memory card slot and more.</li><li>Rumor2 (new colors): Hot on the heels of the fast-selling LG Rumor, LG proudly presents the LG Rumor2, boasting features to delight both tech-oriented and image-conscious consumers, such as the 4-line QWERTY keypad, eye-popping screen resolution (QVGA 240x320), MP3 music player, 1.3 mega pixel camera and removable backplates for customization.</li></ul>

<p><br />
<strong>Home Appliances</strong></p>

<p>With industry-leading features, powerful performance and sleek, contemporary styling, LG offers a full suite of premium kitchen and laundry appliances, providing consumers something better throughout the home, helping make everyday household tasks such as cooking and laundry a more enjoyable experience.</p>

<p>LG home appliance products featured at the Summer Line Show, include:</p>

<ul><li>Ultra-Capacity 4-Door French-Door Refrigerator (model LMX28987ST): LG's French-door refrigeration line now features an ultra-capacity 27.5 cubic foot 4-door model with an exclusive automatic open/close bottom freezer drawer option. At the press of a button, the drawers automatically open, making it easier for consumers to access food or unload groceries. The drawer also has an assisted open/close function that allows consumers to slightly push or pull on the drawer to experience the automatic open/close feature.</li><li>Gas Cooktops (models LCG3091ST and LCG3691ST): LG's new high-performance gas cooktops, available in 30- and 36-inch sizes, offer professional-grade features, such as a range in power from 5,000 to 19,000 BTUs, and feature professional-grade knobs and three heavy-duty continuous grates, covering each of the five burners for a professional look. Its contemporary stainless steel styling package includes LED lights that indicate when a burner is in use or may still be hot to the touch. Controls are placed at the front of the cooktop for easier access when the cooktop is in use.</li><li>Slide-In Electric Range (model LSE3094): LG's first slide-in electric range features elegant styling with its stainless steel frames and its rich set of features, such as its 5.4 cubic foot capacity - among the largest available in the slide-in category, and separate baking drawer. Dual convection fans with three different settings evenly disperse heat throughout the oven for faster pre-heating and more uniform baking, and a gliding and rotating rack provides added convenience for home chefs.</li><li>Steam Dishwasher (model LDF9932ST): LG's newest Steam Dishwasher offers sleek styling with its stainless steel finish and LCD display integrated into the top of the door. Features include the highly efficient TrueSteam(TM) technology, which offers the option of adding steam to any wash cycle and a SenseClean(TM) washing system that automatically measures the turbidity of the water and adjusts wash time during the first rinse in order to optimize water usage. Consumers will also enjoy energy-saving features such as a half-load cleaning option and a hybrid condensing drying system, which allows for faster drying, reduced spotting and superior energy efficiency.</li><li>Graphite Steel Laundry System with Washing Motions (models WM2701HV and DLEX2701/DLGX2702): Until now, washers only used one cleansing motion - tumbling - to clean clothes. LG's new motion technology introduces four "dance-like" washing motions - rolling, stepping, swinging and scrubbing - using LG's Direct Drive motor to increase efficiency and reduce noise and vibration. These innovative washing motions care for clothes while saving time, water and energy. LG's TrueBalance(TM) anti-vibration system also helps offset unbalanced loads in the washer drum, allowing for quieter overall operation.</li><li>LED Steam Laundry Pair in Riviera Blue Designer Finish (model WM2801 and DLEX2801/DLGX2802): One of the largest capacity front-load steam laundry pairs in the industry, at 4.5 cubic feet, with a new eye-pleasing LED display, the Riviera Blue with LED is the latest version of the Steam Laundry Pair. It comes with a LED display, which takes the guesswork out of cycle selection, LG's TrueSteam(TM) technology, including the exclusive Allergiene(TM) cycle, designed to reduce common allergens such as dust mites and pet dander on fabrics, and like all LG steam laundry systems, it is Energy-Star rated.</li></ul>

<p><br />
For additional information and images on all of LG's product offerings, please visit: <a target="_blank" href="http://www.pimsmultimedia.com/LGSLS2009">www.pimsmultimedia.com/LGSLS2009</a></p>

<p><br />
<strong>About LG Electronics USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances and business solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.LGusa.com/">www.LGusa.com</a>.</p>

<p>  * 55LHX 55-inch class/54.6-inch diagonal<br />
  * 55LH90 55-inch class/54.6-inch diagonal<br />
  * 47LH90 47-inch class/47.0-inch diagonal<br />
  * 42LH90 42-inch class/42.0-inch diagonal<br />
  * 47LH50 47-inch class/47.0-inch diagonal<br />
  * 42LH50 42-inch class/42.0-inch diagonal<br />
  * 60PS80 60-inch class/59.5-inch diagonal<br />
  * 50PS80 50-inch class/50.0-inch diagonal</p>

<p>  ** Internet connection and subscriptions required and sold separately.</p>

<p>Source: LG Electronics USA, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 30, 2009  7:41 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(2925)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 2925)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php" type="text/javascript" charset="utf-8"></script>
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
