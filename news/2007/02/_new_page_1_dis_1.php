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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 547 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 547
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Top HDTV Sellers&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2007/02/top-hdtv-sellers.php&amp;title=Top HDTV Sellers">
		<span style="display:none">AUSTIN, TEXAS, February 13, 2007-DisplaySearch, the worldwide leader in display market research and consulting, released Q4'06 TV shipments and revenues by technology, brand, region, size and resolution for 53 different TV brands as part of its    Quarterly Global TV Shipment and Forecast Report.

Global TV unit shipments grew 26% Q/Q in seasonally strong Q4 while declining 1% Y/Y to 57.6M. Due to a strong shift towards flat panel TVs, average TV prices fell just 1% Q/Q but increased 16% Y/Y to $534. LCD and PDP TV prices fell 21% and 29% Y/Y, respectively, which increased flat panel TV demand boosting the flat panel unit share from 19% in Q4'05 to 38% in Q4'06 and the flat panel revenue share from 57% in Q4'05 to 73% in Q4'06. As a result, TV revenues reached a new record high of $30.8B, up 24% Q/Q and 15% Y/Y.

By region, Europe enjoyed the fastest sequential growth, up... </span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 547";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Top HDTV Sellers" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Top HDTV Sellers" />
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
	<title>HDTV Magazine - Top HDTV Sellers</title>
	<meta name="keywords" content="north america, lcd tvs, revenue share, share followed, revenue basis, share, revenue, lcd, tvs, samsung, revenues, unit, growth, market, sony, while, north, plasma, america, shipments, year, basis, lge, philips, remained" />
	<meta name="description" content="AUSTIN, TEXAS, February 13, 2007-DisplaySearch, the worldwide leader in display market research and consulting, released Q4'06 TV shipments and revenues by technology, brand, region, size and resolution for 53 different TV brands as part of its    Quarterly Global TV Shipment and Forecast Report.

Global TV unit shipments grew 26% Q/Q in seasonally strong Q4 while declining 1% Y/Y to 57.6M. Due to a strong shift towards flat panel TVs, average TV prices fell just 1% Q/Q but increased 16% Y/Y to $534. LCD and PDP TV prices fell 21% and 29% Y/Y, respectively, which increased flat panel TV demand boosting the flat panel unit share from 19% in Q4'05 to 38% in Q4'06 and the flat panel revenue share from 57% in Q4'05 to 73% in Q4'06. As a result, TV revenues reached a new record high of $30.8B, up 24% Q/Q and 15% Y/Y.

By region, Europe enjoyed the fastest sequential growth, up... " />
	<meta name="title" content="Top HDTV Sellers" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2007/02/top-hdtv-sellers.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=547', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/02/top-hdtv-sellers.php">Top HDTV Sellers</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>February 16, 2007</b>
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
				<p><html></p>

<p><head><br />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><br />
<title>New Page 1</title><br />
</head></p>

<p><body></p>

<center>
<table cellSpacing="0" cellPadding="0" width="100%" border="0" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table1">
	<tr>
		<td bgColor="#ffffff" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
		<table cellSpacing="0" cellPadding="0" width="100%" border="0" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table2">
			<tr>
				<td width="538" bgColor="#ffffff" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
				<h1><font face="Arial" size="4">DisplaySearch Reports Samsung #1 
				in 2006 TV Revenues, Sony #1 in LCD TV Revenues, LCDs Overtake 
				Plasma at 40&quot;-44&quot; in Q4'06 </font></h1>
				<p>AUSTIN, TEXAS, February 13, 2007-DisplaySearch, the worldwide 
				leader in display market research and consulting, released Q4'06 
				TV shipments and revenues by technology, brand, region, size and 
				resolution for 53 different TV brands as part of its <em>
				<a title="http://now.eloqua.com/er.asp?s=488&amp;lid=29&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39" style="color: 006FA2; text-decoration: none" href="http://now.eloqua.com/er.asp?s=488&lid=29&elq=A302150CE4FE411992A3D3EDD4F8AE39">
				<strong title="http://now.eloqua.com/er.asp?s=488&amp;lid=29&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39">
				Quarterly Global TV Shipment and Forecast Report</strong></a></em>.
				</p>
				<p>Global TV unit shipments grew 26% Q/Q in seasonally strong Q4 
				while declining 1% Y/Y to 57.6M. Due to a strong shift towards 
				flat panel TVs, average TV prices fell just 1% Q/Q but increased 
				16% Y/Y to $534. LCD and PDP TV prices fell 21% and 29% Y/Y, 
				respectively, which increased flat panel TV demand boosting the 
				flat panel unit share from 19% in Q4'05 to 38% in Q4'06 and the 
				flat panel revenue share from 57% in Q4'05 to 73% in Q4'06. As a 
				result, TV revenues reached a new record high of $30.8B, up 24% 
				Q/Q and 15% Y/Y. </p>
				<p>By region, Europe enjoyed the fastest sequential growth, up 
				67% in shipments and 57% in revenues, after channel inventories 
				were depleted in Q3'06. North America earned the highest Y/Y 
				unit growth triggered by excessive Q3'06 sell-in leading to 
				rapid price declines in large-sized flat panel TVs. China earned 
				the highest Y/Y revenue growth. </p>
				<p><strong>LCD TV</strong>s were the only technology to enjoy 
				Y/Y revenue growth in Q4'06, up 72% as 117% unit growth more 
				than offset the 21% ASP declines. LCD TV shipments reached a 
				record high 18.6M units and a 32% share of the Q4'06 TV market. 
				For the year, LCD TVs rose 119% to 46.4M units and a 24% share, 
				up from 11% in 2005. On a revenue basis, LCD TVs led with a 49% 
				share, up from 32% in 2005. LCD TVs gained significant share in 
				every region vs. Q3'06, earned more than a 50% share for the 
				first time in Europe, and overtook CRT TVs in North America. 
				Europe was the top region for LCD TVs, holding a 39% share. By 
				size category, LCD TVs overtook PDP TVs at 40-44" for the first 
				time in Q4'06, holding a 52% to 45% advantage and leading in 
				China, Europe Japan and North America. Larger sizes continued to 
				gain share with 30" and larger LCD TVs accounting for 61% of 
				Q4'06 LCD TV volumes, up from 43% in Q4'05. The 40"+ share 
				reached 17%, up dramatically from just 6% in Q4'05. 1080p LCD 
				TVs rose from a 4% unit share in Q3'06 to a 7% unit share in 
				Q4'06, earning a 9% share in North America and a 15% share in 
				Japan as it resonated with consumers in those regions. 1080p LCD 
				TVs overtook 720p LCD TVs on a unit basis at 46-47" in Q4'06 and 
				reached a 30% revenue share at 40-44". By brand as shown in 
				Table&nbsp;1, Sony reclaimed the top position on a revenue basis in 
				Q4'06, holding a 17.4% to 15.0% advantage over Samsung on the 
				strength of its large-size and 1080p position. Sony widened its 
				revenue share advantage over Samsung at 40-44" by accounting for 
				more than a 50% share at 40-42" 1080p. While Samsung fell to #2 
				in LCD TV revenues, Sharp, Philips and LGE remained #3 - #5 with 
				only Philips gaining share. For the year, Sony led in LCD TV 
				revenues for the first time with a 16% share followed by Samsung 
				at 15% and Sharp at 11.5%. 2006 was the first year Sharp had not 
				been #1 in LCD TV revenues. On a unit basis in Q4'06, Samsung 
				remained #1 with a 14% share followed by Philips at 13%, Sony at 
				12%, Sharp at 10% and LGE at 7%. For the year, Samsung led with 
				a 13.4% share followed by Philips at 13.0%, Sony at 11.6%, Sharp 
				at 11.3% and LGE at 7%. </p>
				<p align="center"><strong>Table 1: LCD TV Brand Revenue Share 
				and Growth </strong></p>
				<div align="center">
					<table cellSpacing="0" cellPadding="0" width="80%" border="1" frame="below" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table3">
						<tr bgColor="#333333">
							<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<div align="center">
&nbsp;</div>
							</td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">
							Q3'06 </font></strong></td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">
							Q4'06 </font></strong></td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">Q/Q 
							Growth </font></strong></td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Sony</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">15.1%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">17.4% </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">67%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Samsung </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">15.5%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">15.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">41%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Sharp </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">11.5%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">11.2%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">41%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap height="16" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Philips </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">10.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">10.5%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">53%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">LGE </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">6.7%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">6.4%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">38%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Other</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">41.2%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">39.5%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">39%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Total</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">100.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">100.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">45%</td>
						</tr>
					</table>
				</div>
				<p><strong>Plasma TV</strong> revenues were up 7% Q/Q, but fell 
				4% Y/Y to $5.0B, the first quarter plasma TV revenues have 
				declined Y/Y due to loss of share to LCDs at 40-44&quot; and rapid 
				price erosion. Plasma TV ASPs fell 20% Q/Q and 29% Y/Y to $1643. 
				Plasma TVs overtook microdisplay RPTVs for the first time at 
				50&quot;+ in Q4'06 with a narrow 42.5% to 42.3% advantage, led the 
				50-54&quot; TV market with a 55% share, grew 34% Q/Q and 35% Y/Y to a 
				record 3.1M TVs, and grew the 50&quot;+ share of total plasma TV 
				shipments from 13% in Q4'05 to 23% in Q4'06. However, this 
				growth was insufficient to offset the aggressive price declines. 
				For the year, plasma TV shipments rose 57% to 9.2M units, while 
				plasma TV revenues grew 22% to $18.5B on a 22% decline in ASPs. 
				In Q4'06, 37&quot; plasma TVs had the fastest Q/Q growth on strong 
				demand in Japan and Europe, while 60&quot;+ plasma TVs had the 
				highest Y/Y growth on strong North American demand. While only 
				North America experienced plasma TV unit growth in Q3'06, all 
				regions experienced Q/Q growth in Q4'06; however, China was down 
				Y/Y due to lack of emphasis by the domestic Chinese brands and 
				China's emphasis on smaller sizes. North America remained the 
				top region for plasma with a 38% share and accounted for 63% of 
				all plasma TV shipments at 50&quot;+. Panasonic remained the dominant 
				brand in Q4'06 with a 32% unit and 33% revenue share. On both 
				unit and revenue shares, LGE remained #2 followed by Samsung, 
				Philips, Hitachi and Pioneer. For the year, Panasonic led with a 
				29% revenue share followed by LGE at 16%, Samsung at 14%, 
				Philips at 10%, Hitachi at 8% and Pioneer at 7%. </p>
				<p><strong>Microdisplay(MD) RPTV </strong>unit shipments rose 
				17% Q/Q while falling 9% Y/Y to 862K units. MD RPTVs grew their 
				share of the 55&quot;+ TV market from 70% in Q3'06 to 77% in Q4'06. 
				For the year, MD RPTVs were up 13% Y/Y to 2.8M. While unit 
				growth was healthy in 2006, revenues declined. In Q4'06, MD RPTV 
				revenues were flat Q/Q at $1.5B and were down 34% Y/Y while ASPs 
				were down 15% Q/Q and 28% Y/Y to $1757 despite a shift in size 
				and resolution mix towards larger sizes and 1080p. For the year, 
				MD RPTV revenues were down 7% on an 18% decline in ASP. In 
				Q4'06, 59% of MD RPTV revenues were at 1080p vs. just 25% in 
				Q4'05 and 58% of MD RPTV revenues were at 55&quot;+ vs. 41% a year 
				earlier. By MD RPTV technology, DLP continued to lead in both 
				units and revenues and held a 43% share for the year. LCOS 
				matched the 3LCD revenue share in Q4'06 as 3LCD unit shipments 
				were down 34% Y/Y. North America continued to dominate the MD 
				RPTV market earning an 89% revenue share, down from 93% in Q3'06 
				as Europe and ROW gained share. By brand, Sony widened its 
				revenue share advantage, earning a 42% share in Q4'06 followed 
				by Samsung at 25% and Mitsubishi at 11%. For the year, Sony led 
				with a 40% share followed by Samsung at 22% and Mitsubishi at 
				12%. </p>
				<p><strong>CRT TV</strong> shipments rose 11% Q/Q while 
				declining 24% Y/Y to 34.9M. CRT revenues were up 6% Q/Q while 
				declining 23% Y/Y on a 4% Q/Q ASP decrease and 1% Y/Y ASP 
				increase. For the year, CRTs were down 16% to 130M with revenues 
				down 15% to $26.5B. The CRT unit share fell from 83% to 69% 
				while revenues fell from 39% to 26%. The digital share of the 
				CRT TV market rose from 12% in Q3'06 to 13% in Q4'06 while the 
				HD share remained flat at 2%. TTE maintained the top position on 
				a unit basis with a 12% share followed by LGE and Samsung at 10% 
				and also maintained the top position on a revenue basis with a 
				12% share followed by Samsung at 11%. TTE also held the top unit 
				and revenue positions for all of 2006. </p>
				<p><strong>The HDTV</strong> share of all TVs sold in Q4'06 
				reached 35% in Q4'06, up from 27% in Q3'06. On a revenue basis, 
				the HDTV share rose from 70% in Q3'06 to 75% in Q4'06. Samsung 
				remained the top HD brand, although it lost share to Sony, which 
				narrowed the gap from over 4 share points to under 2. </p>
				<p>The <strong>1080p</strong> share rose from 1.6% in Q3'06 to 
				2.9% in Q4'06 on a unit basis and from 8% to 13% on a revenue 
				basis. Sony led in 1080p revenues with a 34.5% share, more than 
				twice that of its closest competitor. </p>
				<p>The <strong>digital</strong> share rose from 40% in Q3'06 to 
				48% in Q4'06 led by Japan at 87% and North America at 73%. The 
				average TV size reached 26.5&quot;, up from 24.8&quot; in Q4'05 led by LCD 
				TVs which have grown from 26&quot; to 30&quot; over that period. By 
				region, North America had the largest average size of 29.7&quot;, 
				which was 1-7&quot; larger than other regions. North America 
				accounted for 75% of the 50&quot;+ market and 45% of the 40&quot;+ market.
				</p>
				<p>In terms of total TV brand share worldwide, Samsung led the 
				global TV market in Q4'06 on a unit basis for the second 
				consecutive quarter and on a revenue basis for the fourth 
				consecutive quarter. As shown in Table 2, Samsung enjoyed 31% 
				Q/Q growth to lead the Q4'06 market with an 11.6% share. It was 
				also #1 for all of 2006, the only supplier to earn more than a 
				10% share at 10.6%. LGE and TTE remained #2 and #3 while losing 
				share; Philips and Sony remained #4 and #5 while gaining share. 
				On a revenue basis, as shown in Table 3, Samsung remained #1 but 
				lost share after six consecutive quarters of share growth. 
				Samsung remained #1 in Europe and #2 in ROW and lost the top 
				position in North America to Sony, which enjoyed the fastest 
				revenue growth due to share gains in 1080p TVs. Panasonic fell 
				from #3 to #5, so LGE and Philips each moved up a position. </p>
				<p align="center"><strong>Table 2: TV Unit Share by Brand
				</strong></p>
				<div align="center">
					<table cellSpacing="0" cellPadding="0" width="80%" border="1" frame="below" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table4">
						<tr bgColor="#333333">
							<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<div align="center">
&nbsp;</div>
							</td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">
							Q3'06 </font></strong></td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">
							Q4'06 </font></strong></td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">Q/Q 
							Growth </font></strong></td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Samsung</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">11.2%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">11.6% </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">31%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">LGE</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">9.9%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">9.4%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">20%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">TTE </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">9.7%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">8.8%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">14%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap height="16" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Philips </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">6.3%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">7.2%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">43%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Sony </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">5.7%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">7.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">54%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Other</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">57.1%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">56.1%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">24%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Total</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">100.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">100.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">26%</td>
						</tr>
					</table>
				</div>
				<p align="center"><strong>Table 3: TV Revenue Share by Brand
				</strong></p>
				<div align="center">
					<table cellSpacing="0" cellPadding="0" width="80%" border="1" frame="below" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table5">
						<tr bgColor="#333333">
							<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<div align="center">
&nbsp;</div>
							</td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">
							Q3'06 </font></strong></td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">
							Q4'06 </font></strong></td>
							<td noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center"><strong><font color="#ffffff">Q/Q 
							Growth </font></strong></td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Samsung</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">14.7%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">14.4% </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">22%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Sony</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">10.3%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">12.7</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">53%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">LGE </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">8.4%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">8.2%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">22%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap height="16" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Philips </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">7.7%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">8.2%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">33%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Panasonic </td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">9.3%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">8.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">8%</td>
						</tr>
						<tr>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Other</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">49.6%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">48.4%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">21%</td>
						</tr>
						<tr bgColor="#cccccc">
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">Total</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">100.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">100.0%</td>
							<td vAlign="bottom" noWrap style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
							<p align="center">24%</td>
						</tr>
					</table>
				</div>
				<p>For more information on the dynamic TV market and TV supply 
				chain, attend DisplaySearch's
				<a title="http://now.eloqua.com/er.asp?s=488&amp;lid=34&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39" style="color: 006FA2; text-decoration: none" href="http://now.eloqua.com/er.asp?s=488&lid=34&elq=A302150CE4FE411992A3D3EDD4F8AE39">
				9th Annual US FPD Conference</a> from March 6-8 in San Diego 
				which will feature speakers from leading TV brands, TV 
				retailers, TV panel suppliers, TV market and financial analysts 
				and TV electronics manufacturers. More information can be found 
				at
				<a title="http://now.eloqua.com/er.asp?s=488&amp;lid=34&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39" style="color: 006FA2; text-decoration: none" href="http://now.eloqua.com/er.asp?s=488&lid=34&elq=A302150CE4FE411992A3D3EDD4F8AE39">
				http://www.displaysearch.com/usfpd2007</a>. </p>
				<p>DisplaySearch's TV market intelligence including panel and TV 
				shipments, TV shipments by region by brand by size, rolling 
				16-quarter forecasts, TV cost/price forecasts and design wins 
				can be found in its <em>
				<a title="http://now.eloqua.com/er.asp?s=488&amp;lid=29&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39" style="color: 006FA2; text-decoration: none" href="http://now.eloqua.com/er.asp?s=488&lid=29&elq=A302150CE4FE411992A3D3EDD4F8AE39">
				<strong title="http://now.eloqua.com/er.asp?s=488&amp;lid=29&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39">
				Quarterly Global TV Shipment and Forecast Report</strong></a></em>. 
				For more information on this report, please contact Carolyn Lowe 
				at (512) 459-3126, x104 or
				<a title="mailto:carolyn_lowe@displaysearch.com" style="color: 006FA2; text-decoration: none" href="mailto:carolyn_lowe@displaysearch.com">
				carolyn_lowe@displaysearch.com</a>. </p>
				<p><b>About DisplaySearch</b></p>
				DisplaySearch, an NPD Group company, has a core team of 45 
				employees located in North America and Asia who produce a valued 
				suite of FPD-related market forecasts, technology assessments, 
				surveys, studies and analyses. The company also organizes 
				influential events worldwide. Headquartered in Austin, Texas, 
				DisplaySearch has regional operations in Chicago, Houston, 
				Kyoto, San Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, 
				and the company is on the web at
				<a title="http://now.eloqua.com/er.asp?s=488&amp;lid=23&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39" style="color: 006FA2; text-decoration: none" href="http://now.eloqua.com/er.asp?s=488&lid=23&elq=A302150CE4FE411992A3D3EDD4F8AE39">
				www.displaysearch.com</a>.
				<p>&nbsp;</p>
				<p><b>About The NPD Group Inc.</b></p>
				<p>The NPD Group is the leading provider of reliable and 
				comprehensive consumer and retail information for a wide range 
				of industries. Today, more than 1,600 manufacturers, retailers, 
				and service companies rely on NPD to help them drive critical 
				business decisions at the global, national, and local market 
				levels. NPD helps our clients to identify new business 
				opportunities and guide product development, marketing, sales, 
				merchandising, and other functions. Information is available for 
				the following industry sectors: automotive, beauty, commercial 
				technology, consumer technology, entertainment, fashion, food 
				and beverage, foodservice, home, office supplies, software, 
				sports, toys, and wireless. For more information, visit
				<a title="http://now.eloqua.com/er.asp?s=488&amp;lid=11&amp;elq=A302150CE4FE411992A3D3EDD4F8AE39
http://www.npd.com/" style="color: 006FA2; text-decoration: none" href="http://now.eloqua.com/er.asp?s=488&lid=11&elq=A302150CE4FE411992A3D3EDD4F8AE39">
				www.npd.com</a>. <br>
&nbsp;</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
</center>

<p></body></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>February 16, 2007  1:22 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(547)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 547)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/02/top-hdtv-sellers.php" type="text/javascript" charset="utf-8"></script>
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
