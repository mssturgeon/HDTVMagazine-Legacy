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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1338 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1338
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=A Comparison of Movie Download Services&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/04/a-comparison-of-movie-download-services.php&amp;title=A Comparison of Movie Download Services">
		<span style="display:none">Over the past two years, we have seen a number of video download services hit the market from major players like Microsoft, Apple, Amazon and Netflix. There have also been some new companies entering this category, such as XStreamHD and VUDU. Some are available only via a software client, meaning a PC (or Mac) would be required to enjoy them, while others work with dedicated hardware connected to your TV. They vary in quality, selection, delivery methods and cost and this article will hit the highlights of what each of the major players are offering their would-be customers in this burgeoning market.

Let's set the stage. This article covers products and services that provide movie downloads via the internet. Specifically...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1338";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download A Comparison of Movie Download Services" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="A Comparison of Movie Download Services" />
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
	<title>HDTV Magazine - A Comparison of Movie Download Services</title>
	<meta name="keywords" content="high definition, amazon unbox, video quality, start watching, set top, video, movie, movies, content, service, apple, quality, services, available, download, rent, vudu, could, high, amazon, quite, see, purchase, definition, well" />
	<meta name="description" content="Over the past two years, we have seen a number of video download services hit the market from major players like Microsoft, Apple, Amazon and Netflix. There have also been some new companies entering this category, such as XStreamHD and VUDU. Some are available only via a software client, meaning a PC (or Mac) would be required to enjoy them, while others work with dedicated hardware connected to your TV. They vary in quality, selection, delivery methods and cost and this article will hit the highlights of what each of the major players are offering their would-be customers in this burgeoning market.

Let's set the stage. This article covers products and services that provide movie downloads via the internet. Specifically..." />
	<meta name="title" content="A Comparison of Movie Download Services" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/04/a-comparison-of-movie-download-services.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1338', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/04/a-comparison-of-movie-download-services.php">A Comparison of Movie Download Services</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>April 17, 2008</b>
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
				<p>So I've had this article ready to go about 3 or 4 times now, going back to the beginning of December. The Movie Download space is changing so much these days I've had to &quot;stop the presses&quot; as it were and adjust this article several times. I think it has finally settled down now, so I am pushing this out to you before someone else decides to make an announcement.</p>  <p>Over the past two years, we have seen a number of video download services hit the market from major players like Microsoft, Apple, Amazon and Netflix. There have also been some new companies entering this category, such as XStreamHD and VUDU. Some are available only via a software client, meaning a PC (or Mac) would be required to enjoy them, while others work with dedicated hardware connected to your TV. They vary in quality, selection, delivery methods and cost and this article will hit the highlights of what each of the major players are offering their would-be customers in this burgeoning market.</p>  <h2>Common Ground</h2>  <p>Let's set the stage. <em>This article covers products and services that provide movie downloads via the internet. </em>Specifically, here are the requirements we put in place for being included in this comparison:</p>  <ul>   <li>The service must deliver video via the internet. We will not cover or compare services like Video On Demand (VOD) or Pay Per View (PPV), as these both utilize a satellite or cable network delivery. </li>    <li>The service must also contain full-length motion pictures. We will not cover services like the Sony PlayStation Store or MyTVPal, which only carry trailers. </li>    <li>Lastly, the movie must be downloadable and not limited to streaming. Like me, you are probably an HD Enthusiast, and quite likely know that you cannot get a high quality movie experience if your limited to streaming it over your 1 Mbps internet connection. </li> </ul>  <p>Regardless of the provider, there are several aspects of downloadable video that are standard. These relate mostly to Hollywood studio stipulations and vary only where noted throughout the article:</p>  <ul>   <li>Each of these services is only available in the United States </li>    <li>Each of these services offers similar sound standards and performance. More and more of it is standardizing on Dolby Digital. Please check individual titles for supported audio. </li>    <li>You have a 30 day window in which to start watching videos rented through these services. Once you start watching, you have 24 hours to finish the video. </li>    <li>For those services that permit video purchasing (as opposed to just rental), you can generally re-download titles if they are lost or deleted. </li>    <li>The &quot;digital catalog&quot; available to these services seems to be about the same. All services offer the same 5,000-6,000 movies from all major studios. </li> </ul>  <p>Also, to set the stage for several comments below, you should know the equipment used in my setup to download and view these videos:</p>  <ul>   <li>Samsung 46&quot; 1920x1080p TV </li>    <li>Viewing distance: approx. 8 ft </li>    <li>All devices connect to TV via HDMI through Gefen 8x1 Switch </li>    <li>Internet service: AT&amp;T DSL at 6 Mbps advertised, 4 Mbps actual </li> </ul>  <p>So let's dive in... (in alphabetical order)</p>  <h2>Amazon Unbox</h2>  <p><a href="http://www.amazon.com/b?%5Fencoding=UTF8&amp;node=16386761&amp;tag=hdtvmagazine-20&amp;linkCode=ur2&amp;camp=1789&amp;creative=9325" target="_blank"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; margin: 0px 0px 5px 5px; border-right-width: 0px" height="40" alt="Amazon Unbox" src="http://www.hdtvmagazine.com/images/mt/AComparisonofvideodownloadservices_DE48/image.png" width="153" align="right" border="0" /></a> <a href="http://www.amazon.com/b?%5Fencoding=UTF8&amp;node=16386761&amp;tag=hdtvmagazine-20&amp;linkCode=ur2&amp;camp=1789&amp;creative=9325" target="_blank">Amazon Unbox</a> launched in September 2006. You have two options to enjoy this service: 1) Via PC through their Unbox Video Player, or 2) Via TiVo Series 2 or 3. A quick search on Amazon's site shows that they have over 1000 TV series available and over 6,000 movie titles available. Amazon Unbox does permit purchases in addition to rentals. Movie rentals are $1 - $4 and purchases are $10 - $15 for most titles. Amazon also offers a &quot;TV Pass&quot; on some TV series, allowing you to subscribe to an entire season at a reduced cost. None of the Amazon Unbox content is available in high definition, however. </p>  <p>According to the <a href="http://unbox.typepad.com/amazon_unbox/2007/04/unbox_video_qua.html" target="_blank">Amazon Unbox Blog</a>, there are slight differences depending on whether you download to PC or to TiVo. For PC video, it is encoded using Windows Media Video 9 Advanced Profile and for TiVo it is MPEG-2. Video downloaded to PC has a bitrate of 2500 Kbps average, 6000 Kbps peak and video downloaded to TiVo has a bitrate of 2800 Kbps average, 6600 Kbps peak. Audio bitrates are the same at 192 Kbps average, 1000 Kbps peak.</p>  <p>In my experience with this service, it could be better. I tested out the TiVo method of downloading as that provided the better quality. I was surprised that the interface wasn't more intuitive, given TiVo's track record. I could not immediately locate how to access Amazon Unbox from the TiVo, nor could I find where the movie I purchased was downloaded. But they do have instructions both on the Amazon web site and the TiVo web site that explain these quite well.</p>  <p>From the time of purchase, it took about 5 minutes for the movie to show up in my &quot;Now Playing&quot; list. Once it showed up, I had to wait for enough of it to download before I could start watching it. This was slow as well, only downloading about 1 minute of the movie each minute ... so about real-time. I had to wait until it had about 15 minutes queued up before I could start watching. Combined with the initial 5 minute wait, a total wait time of about 20 minutes to begin watching.</p>  <p>The video appeared a bit jerky (even more so than I'm sure the director intended). There was also obvious color banding, especially in the blacks. There was a general lack of detail in most scenes and blurriness in several. I would objectively classify it visually as sub-DVD quality.</p>  <p><strong>What to improve</strong>: Usability, video quality, and time from buy-to-play all need improving. They need to add HD support and improve encoding quality. And the usability could stand to be improved a bit to make it more intuitive and easier to buy/find movies. It would also be nice if the movie was available sooner after purchase. 20 minutes is quite a while to wait unless you plan ahead. </p>  <p><strong>Overall Grade: C</strong> (<a href="#detail">see detail below</a>)</p>  <h2>Apple TV</h2>  <p><a href="http://www.apple.com/appletv/" target="_blank"><img style="margin: 0px 0px 5px 5px" height="46" alt="Apple TV" src="http://www.hdtvmagazine.com/images/mt/AComparisonofvideodownloadservices_DE48/image_3.png" width="153" align="right" border="0" /></a>I first looked at <a href="http://www.apple.com/appletv/">Apple TV</a> 6 months ago for the purposes of evaluating it as a movie player, and was fairly disappointed. At the time, there was no high definition content and you had to use iTunes on a PC or Mac to purchase and download your content, then either stream it or sync it to the Apple TV. Also, their user interface was rather unimpressive, quite unlike Apple, and did not lend itself well to video storage and content discovery.</p>  <p>Apple announced at Macworld in January that an update would be coming for the Apple TV product and for iTunes that would allow for movie rentals from all major studios as well as support for high definition. Well, the Apple TV update has finally arrived and I must say: I am impressed. The changes made in this latest update are monumental.</p>  <p>The Apple TV unit is a set top box that connects to your TV via HDMI or component video. It can output high definition at up to 1080p/24 fps, but the <a href="http://www.apple.com/appletv/specs.html" target="_blank">tech specs according to Apple</a> limit the source video resolution to 720p/24. With this latest update from Apple, you can now rent movies directly from the Apple TV itself ... no syncing with a computer is required. In fact, it doesn't appear as though you can rent HD via iTunes at all, it's only available through the Apple TV unit.</p>  <p>The interface is much-improved, allowing you to now select from among several relevant top-level categories that contain exactly what you might expect: &quot;My Movies&quot;, &quot;Rented Movies&quot;, &quot;All HD&quot;, &quot;Search&quot;, etc. Included on each movie detail page is also a list of what other users rented who also rented that particular title, but missing is the ability to see other titles in the library by diving into the actor or director from each title. So the content discovery features could be expanded greatly. </p>  <p>According to <a href="http://www.appletvjunkie.com/" target="_blank">AppleTVJunkie.com</a>, Apple TV currently has 694 titles available for rent, with 200 available in HD. In addition, they carry over 200 different TV series (by my count). The content quality was quite mixed. I tested out some TV episodes of &quot;The Unit&quot; in SD, and they were sub-DVD quality. For SD movies, I tested out &quot;Shooter&quot;, which had quite good video quality, equivalent to DVD. For HD content, I tested out &quot;Transformers&quot;, and it was excellent. It did take quite a while to download though. Once it reached 7% downloaded (about 15 minutes in), it let me start playing the film. The video quality was much better than DVD, although not quite as good as HD DVD and/or Blu-ray.</p>  <p>Apple recently reduced the price of its Apple TV boxes to $229 for the 40 GB model and $329 for the 160 GB model. The content for purchase (own) ranges from $1.99 for TV episodes (in SD) to between $9.99 and $14.99 for newly released movies. Content for rent is $2.99 for library titles and $3.99 for new releases. For high definition, it's $3.99 for library titles and $4.99 for new releases.</p>  <p></p>  <p><strong>What to improve</strong>: Apple has done quite well across the board with this latest update. I would like to see native 1080p transfers as well as enhanced audio support. Their HD selection is quite good, but their SD selection is a bit less than most other services. The usability is as one would expect from Apple, very refined. It could use a few improvements in diving down into different areas when looking for movies to rent/buy though, like clicking on actors or directors to see other works they've done. With a 15 minutes wait time, it's approaching acceptable, but it would be better if it was a bit quicker. The cost is also a little higher than other services for equivalent quality, which is surprising considering what Apple has done for music downloads.</p>  <p><strong>Overall Grade: B+</strong> (<a href="#detail">see detail below</a>)</p>  <h2>DishONLINE</h2>  <p><a href="http://www.dishnetwork.com/content/whats_on_dish/dish_on_demand/dishonline/index.shtml" target="_blank"><img style="margin: 0px 0px 5px 5px" height="46" alt="DishONLINE" src="http://www.hdtvmagazine.com/images/mt/AComparisonofvideodownloadservices_DE48/image_4.png" width="153" align="right" border="0" /></a> No, this is not a satellite-delivered VOD service. The DishONLINE service is relatively new, available to those who own their ViP 622 and ViP 722 DVRs. These units come with an ethernet port on the back which, when connected, provides access to downloadable movies. </p>  <p>The interface is OK. It's easy to browse through the movies and sort by various criteria. However, the download time is abysmal. It took me 1 hour and 20 minutes to get enough downloaded to be able to start watching, which was about 59% of the movie. Their pricing is also quite a bit higher than equivalent services at $4.99 for new releases and $2.99 for library/catalog content. There is not an option to purchase movies, only rent. No HD content is available yet.</p>  <p><strong>What to improve</strong>: Obviously, they need to add HD content. Their interface and UI is acceptable, but waiting over an hour to watch a move is a killer. Their selection is bare-bones, so that needs some beefing up. Lastly, their pricing is way to high for the level of quality. You can rent high definition content from other service for the same price or less.</p>  <p><strong>Overall Grade: D</strong> (<a href="#detail">see detail below</a>)</p>  <h2>Microsoft Xbox 360</h2>  <p><a href="http://www.xbox.com/en-US/live/marketplace/moviestv/default.htm" target="_blank"><img style="margin: 0px 0px 5px 5px" height="42" alt="Xbox Live Marketplace" src="http://www.hdtvmagazine.com/images/mt/AComparisonofvideodownloadservices_DE48/image_5.png" width="153" align="right" border="0" /></a> Through the Microsoft Xbox 360 console, you can connect to the <a href="http://www.xbox.com/en-US/live/marketplace/moviestv/default.htm" target="_blank">Xbox Live Marketplace</a> and purchase TV shows or rent movies and watch them on whatever TV you have your console connected to. This service launched in November 2006 and since then they have accumulated a library of almost 300 TV series across 26 different networks and over 300 movies. All of their movie content is available in 480p, and a quick spot check shows that approximately half of the movie content is available in 720p HD as well. Movie prices range from $3 - $6 for rental only. They do not currently have movies available for purchase. </p>  <p>Their interface is fairly easy to use, with many ways to search for the program you want. Content from the marketplace does have the advantage of being able to start watching it before it is completely downloaded, but this doesn't seem to help much with HD content. I tried downloading &quot;Transformers&quot; and gave up 1 hour into it after only 9% was downloaded. </p>  <p>The video quality is on-par with the HD video from Apple TV, so in this respect, they are one of the best options available ... especially if you already have an Xbox. Audio also is DD 5.1, which is on par with most other services in this category.</p>  <p><strong>What to improve</strong>: This was one of the first movie download services to launch, and they launched with HD content from the start. But now, their 720p offerings are matched or surpassed by other providers ... so they need to improve this if they want to remain competitive. Their pricing also is a bit higher than Apple TV for equivalent video quality.</p>  <p><strong>Overall Grade: B-</strong> (<a href="#detail">see detail below</a>)</p>  <h2>VUDU</h2>  <p><a href="http://www.vudu.com/" target="_blank"><img style="margin: 0px 0px 5px 5px" height="62" alt="VUDU" src="http://www.hdtvmagazine.com/images/mt/AComparisonofvideodownloadservices_DE48/image1.png" width="153" align="right" border="0" /></a> </p>  <p>In September 2007, there was a new entry into the movie download market: <a href="http://www.vudu.com/" target="_blank">VUDU</a>. Like the Apple TV service, VUDU is accessed using a set-top box (STB) that connects directly to your television. VUDU has movies available for both purchase and rental. The set-top box is capable of outputting video at up to 1080p/24, and they now have over 100 movies available in high definition out of their library of over 6,000 movies. The set top box is $295, which allows you to store roughly 50 HD movies and unlimited rentals. Rentals are reasonably priced from $0.99 up to $5.99 for high definition new releases. Content for purchase (own) ranges from $4.99 up to $24.99 for high definition. </p>  <p>Whether you rent or buy, you may be able to watch the movie immediately if your internet connection is fast enough because VUDU utilizes peer-to-peer (P2P) technology to distribute movie and TV content. With P2P, instead of downloading from a central server, the box downloads segments of the movie from other VUDU boxes connected to the internet.</p>  <p>The main benefit of P2P distribution is in its distributed delivery of your movie. You are not relying on a single server (or group of servers) to have enough horsepower to deliver your movie along with hundreds or thousands of other simultaneous customers. This means that VUDU can scale quite well to much larger audiences without significant investments on the delivery side (servers, bandwidth, etc.), making it theoretically more future-proof than a client-server system.</p>  <p>Also with VUDU, every single movie on their &quot;system&quot; has a starter stub stored on your box already, roughly the first 30 seconds of every movie. When you buy (or rent) a movie, that starter stub begins playing while the box connects to dozens of other VUDU boxes on the internet to download the subsequent segments of the movie.</p>  <p>The video quality was excellent, even for SD fare. All of their content is encoded at 24 fps. SD video is 480p/24 and encoded with H.264 Main Profile while all HD content is 1080p/24 encoded with H.264 High Profile. In all honesty, when I first began testing the unit with SD programming back in November, it was not obvious to me that what I was watching <strong>wasn't</strong> HD. I've looked at all the major players in the movie download market, and the quality they are getting with their SD video is unsurpassed. The quality of their HD content rivals that of packaged media, although I'm sure it would not hold up to a side-by-side test.</p>  <p>Their user interface is flawless. This system is so easy to use, I can put the remote in just about anyone's hand and they won't have a single question about what to do next. As advertised, their content begins playing immediately. Even for HD content, all that is needed is a 4 Mbps connection to be able to watch HD content instantly. If you'd like to give your ISP a test drive and see if you'd be able to watch instantly, VUDU has a <a href="http://speedtest.vudu.com/cdn1/" target="_blank">speed test</a> that will rate your connection throughput.</p>  <p>The other thing VUDU does very well is discovery. Their interface allows for you to navigate through and dig deeper into movies from lists of actors and directors associated with each movie, as well as &quot;Similar Movies&quot; by genre. VUDU almost makes it too easy to find something to watch or add to your Wish List.</p>  <p><strong>Where to improve</strong>: Honestly, the only area where they could stand to improve is price. They charge about the same as Xbox Live Marketplace for HD content, and are providing 1080p instead of 720p, but Apple is now only charging $4.99 for HD rentals. Granted, Apple's service is only 720p, but I don't know that anyone would find that the 1080p content is worth the 20% premium. Your call.</p>  <p><strong>Overall Grade: A</strong> (<a href="#detail">see detail below</a>)</p> <a name="details"></a>  <h2>Comparison Tables</h2>  <p><strong>Grades</strong></p>  <p>In the table below, I've graded each service on the following criteria:</p>  <ul>   <li><strong>Usability</strong>: How intuitive is the user interface, how easy is it to purchase movies and how quickly can you start watching </li>    <li><strong>Audio/Video</strong>: General audio/video quality </li>    <li><strong>Selection</strong>: Available titles, HD support, rent vs. buy </li>    <li><strong>Cost</strong>: How do the rental/purchase costs compare with traditional rentals and purchases, or with similar providers </li> </ul>  <table class="type1b" cellspacing="0" cellpadding="2" width="520" border="0"><tbody>     <tr>       <td class="type1b_header" nowrap="nowrap" width="117">Service</td>        <td class="type1b_header" nowrap="nowrap" width="75">Usability</td>        <td class="type1b_header" nowrap="nowrap" width="104">Audio/Video</td>        <td class="type1b_header" nowrap="nowrap" width="75">Selection</td>        <td class="type1b_header" nowrap="nowrap" width="70">Cost</td>        <td class="type1b_header" nowrap="nowrap" width="77"><strong>Overall</strong></td>     </tr>      <tr>       <td class="grid" width="117">Amazon Unbox</td>        <td class="grid" width="75">B</td>        <td class="grid" width="104">D</td>        <td class="grid" width="75">C</td>        <td class="grid" width="70">A</td>        <td class="grid" width="77"><strong>C</strong></td>     </tr>      <tr>       <td class="grid" width="117">Apple TV</td>        <td class="grid" width="75">A-</td>        <td class="grid" width="104">B</td>        <td class="grid" width="75">B</td>        <td class="grid" width="70">B</td>        <td class="grid" width="77"><strong>B+</strong></td>     </tr>      <tr>       <td class="grid" width="117">Dish</td>        <td class="grid" width="75">B</td>        <td class="grid" width="104">C</td>        <td class="grid" width="75">F</td>        <td class="grid" width="70">F</td>        <td class="grid" width="77"><strong>D</strong></td>     </tr>      <tr>       <td class="grid" width="117">Microsoft Xbox</td>        <td class="grid" width="75">B</td>        <td class="grid" width="104">B</td>        <td class="grid" width="75">C</td>        <td class="grid" width="70">C</td>        <td class="grid" width="77"><strong>B-</strong></td>     </tr>      <tr>       <td class="grid" width="117">VUDU</td>        <td class="grid" width="75">A</td>        <td class="grid" width="104">A</td>        <td class="grid" width="75">A</td>        <td class="grid" width="70">A-</td>        <td class="grid" width="77"><strong>A</strong></td>     </tr>   </tbody></table>  <p><strong>Overview Comparison</strong>:</p>  <table class="type1b" cellspacing="0" cellpadding="2" width="714" border="0"><tbody>     <tr>       <td class="type1b_header" nowrap="nowrap">Service</td>        <td class="type1b_header" nowrap="nowrap">PC / STB</td>        <td class="type1b_header" nowrap="nowrap">Resolution(s)</td>        <td class="type1b_header" nowrap="nowrap">Selection <sup>2</sup></td>        <td class="type1b_header" nowrap="nowrap">Cost</td>     </tr>      <tr>       <td class="grid" nowrap="nowrap">Amazon Unbox</td>        <td class="grid" nowrap="nowrap">PC or STB <sup>1</sup></td>        <td class="grid">480p</td>        <td class="grid">6,000 movies, 1000 TV series</td>        <td class="grid">Rent: $0.99 - $3.99          <br />Buy: $9.99 - $14.99</td>     </tr>      <tr>       <td class="grid">Apple TV</td>        <td class="grid">STB</td>        <td class="grid" nowrap="nowrap">480p, 720p</td>        <td class="grid">~700 movies (200 HD), ~200 TV series</td>        <td class="grid" nowrap="nowrap">Rent: $2.99 - $4.99          <br />Buy: $9.99 - $14.99</td>     </tr>      <tr>       <td class="grid">Dish</td>        <td class="grid">STB</td>        <td class="grid">480p</td>        <td class="grid">300 movies, no HD content</td>        <td class="grid">Rent: $2.99 - $4.99</td>     </tr>      <tr>       <td class="grid" nowrap="nowrap">Microsoft Xbox</td>        <td class="grid">STB</td>        <td class="grid" nowrap="nowrap">480p, 720p</td>        <td class="grid">300 movies (~150 HD), 300 TV series</td>        <td class="grid">Rent: $3 - $6</td>     </tr>      <tr>       <td class="grid">VUDU</td>        <td class="grid">STB</td>        <td class="grid">480p, 1080p</td>        <td class="grid">6,000 movies (100+ HD), ~50 TV series</td>        <td nowrap="nowrap">Rent: $0.99 - $5.99          <br />Buy: $4.99 - $24.99</td>     </tr>   </tbody></table>  <p>1 - Amazon Unbox is available through TiVo Series 2 or 3 set-top boxes</p>  <p>2 - Selection is approximate and rounded</p>  <h2>Those That Failed</h2>  <p><strong>Moviebeam</strong> - This service announced last fall that is was closing down. I am mentioning it here so that you know what the differences are between this failed venture and what others are trying to do. The Moviebeam service was similar to each of these in that it was a hardware solution to video downloading. The set top box was available for $200-$250 and movies could be rented for $2 - $4. Where it differed from those mentioned here is that it used traditional over-the-air broadcast to distribute its movies. Because of this, it could not guarantee delivery into every home. It was also limited by the fact that it could only offer about 200 movies at a time. </p>  <p><strong>Wal-Mart</strong> - Wal-mart also tried their hand at providing video download services, announcing the service in February, 2007. They closed their doors shortly before Christmas last year. Many have offered reasons for this failure: Inexperience in operating an online video store, fear that video download would poach DVD sales in-store, and a customer demographics that simply couldn't grasp the concept, etc. It remains to be seen whether the services mentioned in this comparison will suffer the same fate.</p>  <h2>Future Offerings</h2>  <p><strong>DirecTV on Demand</strong> - DirecTV is currently previewing their On Demand service as a &quot;Beta&quot;. That is why this is under Future Offerings instead of being part of the comparison above. In order to access this service, you will need an internet connection, HD DVR (HR20 or HR21 model), HD Access and DVR service. We'll have to see how it all works out, but for now they are indicating that there will be no extra charge for On Demand service for DirecTV customers.</p>  <p><strong>LG &amp; Netflix</strong> - At CES 2008, Netflix and LG announced a partnership in which LG will be manufacturing an STB that can be used with Netflix's <a href="http://www.netflix.com/BrowseSelection?sgid=gev" target="_blank">Watch Instantly</a> service. It is unknown whether this will be a mediocre-quality streaming services or a higher-quality download service. But definitely a partnership we will be keeping our eye on when it debuts this summer.</p>  <p><strong>Pioneer SyncTV</strong> - Announced in November 2007, SyncTV is a product of the advanced research labs of Pioneer Electronics. This service is still in &quot;beta&quot;, so it cannot be accessed publicly as yet. From their &quot;About&quot; page:</p>  <p>&quot;SyncTV gives you home-theater quality TV shows on an unlimited download basis, so now you can download whole seasons of TV shows and watch them when you want. Completely based on open-standards, the SyncTV service works on Windows PCs, Macs and Linux PCs, and in the future it will also work on TVs and portable players.</p>  <p>Where possible, SyncTV will provide HD programming across the different channels. SyncTV will also have programming available in discrete 5.1 Dolby Digital Plus, giving you the full home theater experience.&quot;</p>  <p>It remains to be seen how much of this quality comes through, and there is no mention of the price of services offered.</p>  <p><strong>Sony Playstation Store</strong> - Sony has been hinting that video downloads are coming for the Playstation 3 for over a year. In February, Phil Harrison of Sony indicated that it would be here &quot;very shortly&quot;, but we have yet to see any details on when that will be.</p>  <p><strong>XStreamHD</strong> - One of the most promising technologies shown at CES this year was XStreamHD. This is a new hybrid satellite/internet delivery service that is touting Full 1080p, DTS-HD movie downloads capable of bitrates up to 100 Mbps. There has been no announcement as yet on service or movie pricing, but the hardware required for this service has been priced at $399. The service is set to launch in Q4 this year.</p>  <h2>Conclusion</h2>  <p>In just over a year, we've seen a tremendous amount of online video flood the marketplace with varying degrees of quality and pricing, but we have yet to see one that has the ultimate combination of content choice, quality (high definition) video, usability and reasonable price. I think these services certainly could replace the current DVD library in many homes today, but with respect to high definition DVD, it's not there yet. Depending on how quickly these services can expand their HD offerings, it could be a viable replacement at some point later this year or early next year. Even if you are not interested in giving up your physical media collection, any of these services provide convenient ways to rent movies.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>April 17, 2008  7:53 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1338)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1338)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/04/a-comparison-of-movie-download-services.php" type="text/javascript" charset="utf-8"></script>
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
