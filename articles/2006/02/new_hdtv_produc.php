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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 289 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 289
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HDTV Products - Looking Into the Future&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/02/hdtv-products-looking-into-the-future.php&amp;title=HDTV Products - Looking Into the Future">
		<span style="display:none">The following article originally appeared in HDTVetc magazine in their August 2004 issue. It contains product information that is likewise, dated to mid-2004. The products in this article were &quot;New&quot; when originally published and should obviously not be considered as such when reading today. Although this article has some historical value, the primary value is the analysis to reach a forecasted vision of future market conditions (which eventually came to pass). This assisted many consumers in making more informed purchasing decisions. Reading the Analysis and Conclusions section is almost like time-travel: The historic vision has now transformed itself into current events and conditions ... mostly (we are still waiting for some of them to happen).</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 289";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Products - Looking Into the Future" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Products - Looking Into the Future" />
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
	<title>HDTV Magazine - HDTV Products - Looking Into the Future</title>
	<meta name="keywords" content="atsc qam, qam cablecard, hdmi hdcp, cablecard tuners, direct view, ttm, integrated, new, models, series, hdmi, dlp, lcd, line, cablecard, qam, atsc, tuners, sets, products, monitors, ces, crt, rptv, ieee" />
	<meta name="description" content="The following article originally appeared in HDTVetc magazine in their August 2004 issue. It contains product information that is likewise, dated to mid-2004. The products in this article were &quot;New&quot; when originally published and should obviously not be considered as such when reading today. Although this article has some historical value, the primary value is the analysis to reach a forecasted vision of future market conditions (which eventually came to pass). This assisted many consumers in making more informed purchasing decisions. Reading the Analysis and Conclusions section is almost like time-travel: The historic vision has now transformed itself into current events and conditions ... mostly (we are still waiting for some of them to happen)." />
	<meta name="title" content="HDTV Products - Looking Into the Future" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/02/hdtv-products-looking-into-the-future.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=289', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/02/hdtv-products-looking-into-the-future.php">HDTV Products - Looking Into the Future</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February  3, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p><script type="text/javascript"><br />
	function shid(id) {<br />
		var oDiv = document.getElementById(id);<br />
		d = oDiv.style.display;<br />
		if (d == 'none') {<br />
			oDiv.style.display = 'inline';<br />
		} else {<br />
			oDiv.style.display = 'none';<br />
		}<br />
	}<br />
</script><br />
<blockquote>The following article originally appeared in HDTVetc magazine in their August 2004 issue. It contains product information that is likewise, dated to mid-2004. The products in this article were "New" when originally published and should obviously not be considered as such when reading today. Although this article has some historical value, the primary value is the analysis to reach a forecasted vision of future market conditions (which eventually came to pass). This assisted many consumers in making more informed purchasing decisions. Reading the Analysis and Conclusions section is almost like time-travel: The historic vision has now transformed itself into current events and conditions ... mostly (we are still waiting for some of them to happen). Enjoy the reading.</blockquote></p>

<p><br />
How do you know what HDTV products are aligned for future release to make an informed selection today?  Similar to when you are about to spend $3000 on any other product for your house.  How do you know if your selection would not to be replaced next month by a product introduced on trade shows?  Last year shows.  Would you expect to see near future products at your corner store when the retailer is actually trying to sell the current line and liquidating the line before that?  </p>

<p>Often, not even the store manager could be informed enough to help you with those questions, and the information is most probably limited to only the lines the store sells, and not looking ahead far enough so your purchase would not to be obsolete the minute it arrives to your door step.  Some sales personnel would not even bother to read basic video subscriptions, or to consult free information of the Internet, to serve you better.  Therefore, you are in your own to been able to anticipate, what to do?         </p>

<p>Consumer electronics is always very dynamic.  I have seen some manufacturers release self-replacing HDTV lines three times within the year, sometimes with minor improvements, other times with radical redesigns to include features you might have interest to wait for, if you knew they were coming next quarter.</p>

<p>The International Consumers Electronic Show (CES) held every January at Las Vegas is considered as one of the most important yearly consumer electronic events, if not the most.  Manufacturers have the opportunity to show their new products, prototypes, mockups, and to provide technology statements.  Many of those products would become available later on the year, or the following, some will later be released with different features or functionality than when shown, some will never make it to the retailing floors.</p>

<p>Every year I prepare a comprehensive review of future H/DTV products a month after CES; approximately 100 pages of documentation containing hundreds of new products with detailed specifications and company/technology trends.  If you need that kind of detail, the complete version could help you and is available in the Internet's HDTV Magazine, <a href="http://www.hdtvmagazine.com/store/ces-2005.php">http://www.hdtvmagazine.com/store/ces-2005.php</a>.  The material also serves as backbone for the printable summary version that appears on the pages of this magazine (HDTVetc) as "The Current State of HD Technology" (Spring 2004 issue for CES 2004). </p>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://www.cedia.net/">CEDIA EXPO</a> (Custom Electronic Design and Installation Association) is another show usually held in September that manufacturers also use as an opportunity to show new products that are coming down the line, those products might be found displayed again four months later at CES.  <a href="/cgi-bin/ntlinktrack.cgi?http://www.CESweb.org/">CES International</a> has grown from 200 exhibitors/17,500 attendees in 1967 to 2,500 exhibitors/129,000 attendees in 2004, and is now over six times the size of CEDIA EXPO (with over 20,000 attendees in 2003).  </p>

<p>In other words, people attending CES might expect to see "all" of the products that will be introduced in the near future.  Some attendees come back from the show and start saving for their targeted product, or decide to buy another one that is already available because CES helped them confirm that it might not be worth the waiting.  However, those that follow the industry closely might already know that many major companies introduce full lines of new products after the first quarter, products that were not even hinted at CEDIA or CES.  By incorporating to the CES review those "during the year" announcements, one can be in a better position to compare the products introduced by company A at CES in January, against products introduced by company B a few months later after CES (due to company's marketing yearly cycles), and have then a more complete view of the 2004/5 lines.  </p>

<p>This article covers the new introductions/updates made between January and August (after CES, but before CEDIA) from several major DTV manufacturers such as Hitachi, Mitsubishi, Thomson, Toshiba, Samsung, and Sony.  Prices are MSRP (I took the liberty to round the 999s to the next dollar to facilitate reading).  Product availability is stated as "Time to Market" (TTM), reported in press releases by the manufacturer. </p>

<p>I hope the information would be useful for your buying/upgrade/planning decisions.  To help you put all this information in perspective I also include a brief analysis and conclusions section at the end.</p>

<blockquote>I would like to remind you that although some of the following products are still current, the announcements are not.</blockquote>

<p><br />
<strong><a href="javascript:shid('Hitachi')">Hitachi</a></strong><br />
<div id="Hitachi" style="display:none"></p>

<p>New York, June 04</p>

<p>Hitachi is concentrating its DTV efforts in LCD (RPTV and direct-view) and CRT RPTV for 2004/5 models, which includes the new "CineForm" design series of fully integrated/CableCARD sets, expected by year-end.  The company has switched from 3 to 21 integrated models transitioning to CableCARD tuner integration, using Hitachi's VirtualHD 1080p upconversion video processing on all models. </p>

<p><u>Eleven new Ultravision CineForm models (below)</u><br />
All integrated with ATSC/QAM CableCARD/dual NTSC tuners, Virtual HD 1080p video processor, identical vertical/horizontal look within the line, reduced height, two stage light engine, new dual-focus, advertising to start in Sep 04 </p>

<p><u>LCD RPTVs Integrated</u><br />
<u>Ultravision VS810 Series</u><br />
For open distribution, two HDMI/HDCP, 40 watt speaker system<br />
50"	50VS810	$4000, TTM 3Q04<br />
60"	60VS810	$4700, TTM 3Q04<br />
70"	70VS810	$7000, TTM 4Q04<br />
<u>Ultravision Director's VX915 Series</u><br />
For A/V retail stores, adds to above dual two-way 1394/DTCP, high-gloss cabinet w/black trim, deep-black anti-reflective shield, learning A/V Net III remote, TTM 4Q04<br />
50"	50VX915	$4700<br />
60"	60VX915	$5500<br />
70"	70VX915	$7500 </p>

<p><u>LCD Flat-panel TV Direct-view</u><br />
32" 	32HDL51	$TBA, TTM 4Q04, 768p, two IEEE-1394, two HDMI</p>

<p><u>Plasma Panels</u><br />
<u>Ultravision HDT51 Series Integrated</u><br />
TTM 3Q04, CineForm cosmetics, dual HDMI, dual 1394, Quick Start Seamless ATSC/NTSC/QAM CableCARD tuners, Virtual HD 1080p processing, USB, inputs/outputs housed on Hitachi's AV Center connected via single wire to panel and controlled with IR from screen for hide away installations<br />
42"	42HDT51	$6000, 1024x1024, AliS technology<br />
<img src="/images/articles/image001.jpg"><br />
55"	55HDT51	$10000, WXGA 1366x768</p>

<p><u>Ultravision HDX61 Director's Series Integrated</u><br />
Same features as HDT51 line plus enhanced industrial design w/high gloss, black trim, high-contrast deep black shield, two year warranty, TTM 3Q04<br />
42"	42HDX61	$7000<br />
55"	55HDX61	$11000</p>

<p><u>Non-CineForm LCD Integrated RPTV</u><br />
Fully integrated ATSC/QAM digital CableCARD tuning capability, TTM 3Q04<br />
<u>V710 (entry) Line</u><br />
720p, Virtual HD 1080p processing, HDMI, USB, 40watt 3-way speaker system<br />
42"	47V710	$2800<br />
50"	50V710	$3300<br />
60"	60V710	$4000<br />
<u>V715 (step-up) Line</u><br />
Titanium silver finish<br />
50"	50V715	$3300<br />
60"	60V715	$4000</p>

<p><u>Plasma EDTV Monitor</u><br />
42"	42EDT41	$4300, Virtual HD 1080p processing, 480p, DVI/HDCP, NTSC tuner, DVI/HDCP, TTM 2Q04<br />
<u>Plasma Professional Panel</u><br />
42"	CMP420V	$3500, DVI, 853x480, V1 black frame version, V2 silver frame </p>

<p><u>CRT RPTVs</u><br />
<u>Series F510 Monitor Line</u><br />
TTM 3Q04, HDMI, Virtual HD 1080p processing, 1080i/540p<br />
46"	46F510	$1500<br />
51"	51F510	$1700<br />
57"	57F510	$2000<br />
<u>Series F710 Integrated</u><br />
TTM 3Q04, adds to above integrated w/ATSC and QAM CableCARD tuners<br />
65"	65F710	$3000<br />
<u>Series S715 Ultravision Integrated Line</u><br />
TTM 3Q04, adds to above five element lens system, USB, 40 watt speaker system<br />
51"	51S715	$2200<br />
57"	57S715	$2500</p>

<p><u>LCD FPTV Ultra-vision</u><br />
PJTX100	$4000, TTM 2Q04, 16:9 LCD for screen sizes between 30" and 300", 1200 ANSI, 1200:1 CR, 1280X720, 1.6:1 zoom, horizontal/vertical lens shift, DVI/HDCP</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Mitsubishi')">Mitsubishi</a></strong><br />
<div id="Mitsubishi" style="display:none"><br />
April 2004</p>

<p>The company announced the addition of six DLP high-definition sets between 52" and 62" to be available in the period Jul-Sep 04, and one 82" integrated LCoS micro display (Alpha's flagship RPTV).  The sets are integrated with ATSC/QAM CableCARD unidirectional tuners, and have HDMI, and IEEE-1394 digital connections.  These features are also included on most of the other newer sets of the 2004/5 line.  In total, the company is adding 18 new integrated ATSC/cable-ready models. </p>

<p>According to the product development director, Mitsubishi designed a proprietary light engine using the 0.85-inch DMD chip on their new DLP additions, a return from their original 65" DLP introduction in 2000 (MSRP $15,000 at that time), the company said.  Diamond models will be distributed by A/V specialists, Medallion models by major accounts.</p>

<p><u>525 DLP Series Integrated</u><br />
TTM Jul/Aug 04, NetCommand 4.0 networking with Learning Media Command, "AMVP2" motion video processing<br />
52"	WD-52525	$4200<br />
<img src="/images/articles/image002.jpg"><br />
62"	WD-62525	$5000</p>

<p><u>Medallion 725 DLP Series Integrated</u><br />
TTM Aug/Sep 04, same as 525 features plus TV Guide Onscreen IPG, Anti-Glare Diamond Shield<br />
52"	WD-52725	$4500<br />
62"	WD-62725	$5300</p>

<p><u>Diamond 825 DLP Series Integrated</u><br />
TTM Aug 04, 120GB HDD DVR for 12 hours HD, 72 hours SD, MPEG SD encoder, Gemstar guide, subscription free<br />
<img src="/images/articles/image003.jpg"><br />
52"	WD-52825	$5500<br />
62"	WD-62825	$6300 </p>

<p><u>LCoS RPTV</u><br />
82"	Alpha 925	$21000, TTM Oct 04, 1920x1080 pixels resolution, 120GB DVR for 12 hours HD, 72 hours SD, MPEG SD encoder, diffusion screen, two-way speakers, this new unit has now an internal DVR, but still costing $21000 as last year's model Alpha WL-82913</p>

<p><u>New Lines (five models) of LCD Flat-panel TVs</u><br />
40 Series<br />
1280x768, PC compatibility, upgradeable, AMVP<br />
22"	LT-2240	$3000, TTM Jun 04<br />
30"	LT-3040	$5000, TTM Apr 04</p>

<p><u>Medallion LCD Flat-panel Monitor</u><br />
1280x768, TTM May 04, black bezel cosmetics, Color View picture control<br />
<img src="/images/articles/image004.jpg"><br />
30"	LT-3050	$5300<br />
 <br />
<u>Diamond Series LCD Integrated TVs</u><br />
ATSC/NTSC/QAM CableCARD tuners, IEEE-1394, HDMI/HDCP, 120GB HDD DVR for 12 hours HD, 72 hours SD, TV Guide Onscreen IPG, MPEG SD encoders, Net Command 4.0 <br />
<img src="/images/articles/image005.jpg"><br />
42"	LT-4260	$14000, 768x1365, TTM Oct 04, uses 20 fluorescent lamps<br />
55"	LT-5560	$TBA, 1080x1920, TTM TBA, uses 28 fluorescent lamps<br />
 <br />
<u>Plasmas Medallion Series</u> TTM Oct 04<br />
42"	PD-4245	$5000, 480x852 EDTV, MonitorLink DVI, speakers, stand<br />
50"	PD-5050	$8500, 768x1365 HD monitor, HDMI <br />
<img src="/images/articles/image006.jpg"><br />
61"	PD-6150	$18000, 768x1365 HD monitor, HDMI, improved brightness and contrast</p>

<p><u>CRT RPTVs</u><br />
<u>315 Series</u>, upgradeable monitors, DVI/HDCP<br />
42"	WT-42315	$1600, TTM Apr 04<br />
48"	WS-48315	$1800, TTM May 04<br />
55"	WS-55315	$2200, TTM Mar 04<br />
65"	WS-65351	$2700, TTM Apr 04</p>

<p><u>Eight CRT RPTV Fully Integrated Models</u><br />
ATSC/QAM CableCARD tuners, AMVP2 processing, IEEE-1394, Net-Command 4.0 system control, HDMI/HDCP <br />
<u>515 Series</u><br />
48"	WS-48515	$2300, TTM Jul 04<br />
55"	WS-55515	$2700, TTM Jul 04<br />
65"	WS-65515	$3200, TTM Apr 04</p>

<p><u>Medallion 615 Series</u>, TTM Aug 04<br />
55"	WS-55615	$3000<br />
65"	WS-65615	$3500<br />
73"	WS-73615	$5300<br />
<u>Diamond 815 Series</u>, TTM Aug 04<br />
<img src="/images/articles/image007.jpg"><br />
55"	WS-55815	$4500<br />
65"	WS-65815	$5500, 9-inch CRTs</p>

<p><u>New HDTV Receiver/Controller</u><br />
<img src="/images/articles/image008.gif"><br />
HD-6000	TTM later 04 at selected retailers, HD 120GB PVR (personal video recorder), up to 12 hours of HD recording, and 72 hours of non-HD, subcription free, MPEG SD encoder, AMVP2(TM) Mitsubishi's second-generation Advanced Multimedia Video Processor.</p>

<p>ATSC/QAM CableCARD/NTSC tuners, NetCommand(R) 4.0 system control, PerfectColor(TM) 6-way color adjustment, TV Guide On Screen(R) electronic program guide, seven inputs including one HDMI, and three component video inputs.  Outputs include one HDMI, and one component video.  Two FireWire(R) (IEEE 1394) digital home-networking ports.</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Samsung')">Samsung</a></strong><br />
<div id="Samsung" style="display:none"><br />
New York, June 2004</p>

<p>At CES 2004 Samsung informally projected that in July 2004 they would make available to the consumer a 1080p DLP RPTV line implementing TI's new xHD3 Digital Micromirror Device (DMD), followed by a 1080p DLP FPTV in November.  The line for 1080P RPTV was said to include sizes of 50", 56", and 61", at a projected range of $4000 to $6000.  In June, the company formally confirmed the initial release of the 1080p RPTV line for later this fall to be made available to select A/V retailers, using a new design of their light engine (fifth-generation).</p>

<p><u>97 Series Line</u><br />
61"	HL-P6197W	$6500, TTM Nov 04, 3000:1 CR, seven-segment color wheel, integrated ATSC/QAM unidirectional CableCARD tuners, to be distributed through select A/V retailers.  The $6000 projected MSRP upper range (for the larger set), provided in January at CES, was close to June's confirmation.  </p>

<p>In June's announcement no confirmation was issued about the release of the other smaller screens of the line, however, in September, just after this article was prepared, Samsung announced that they would show at CEDIA (September 04) the 56-inch model of this xHD3 1080p line:</p>

<p>56"	HL-P5697W	MSRP expected to be between $5500/$6000, TTM 1Q05, 3000:1 CR, pedestal base design, integrated with ATSC/NTSC/QAM Cable CARD tuners. </p>

<p>Samsung announced the release of nine fully integrated/CableCARD-ready rear-projection HDTV sets, six transitioned as HDTV monitors, three integrated models expected for release between September and November.  Other DLP models will be transitioned to become integrated w/CableCARD to comply with the FCC mandate with the objective of eventually discontinue the manufacturing of HD DLP monitors.</p>

<p>Integrated CableCARD models transitioning from monitor versions will be identified with a number 7 on the last digit of the model number (instead of the 3 of the monitor version), the new 56" HL-P5663W DLP RPTV monitor expected for July will transition to HL-P5667W as the CableCARD integrated version, within a year time-frame.  At CES 2004, the company anticipated that integrated versions would be priced $500 above the monitor versions below, expected for later in 2004.</p>

<p><u>63 Series DLP Monitors</u><br />
Will be distributed by national accounts, TTM Jun/Jul 04, 0.55" HD3 DMD chip with third-generation light engine, 1500:1 CR, improved brightness over 2003's HD2 models, one DVI and one HDMI inputs, two component video inputs, <br />
<img src="/images/articles/image009.jpg"><br />
46"	HL-P4663W	$3300<br />
50"	HL-P5063W	$3700<br />
56"	HL-P5663W	$4200<br />
61"	HL-P6163W	$4700<br />
The 56" is for regional accounts, but will also be available to national accounts by special order only</p>

<p><u>70 Series DLP Monitors</u><br />
Will be distributed by the PRO group and select A/V specialty retailers, 0.85" HD2+ DMD chip with fourth-generation light engine, claimed to have increased switching speed, reduced pivot point and higher reflective surface area for improved contrast (2500:1 CR) and brightness, improved optics and screen designs <br />
46"	$4000<br />
56"	$4500</p>

<p><u>Monitor Pedestal DLP Models</u><br />
TTM Jul 04, HD2+ DMD chip, fourth-generation light engine, HDMI, DVI, PC inputs, component video inputs</p>

<p><img src="/images/articles/image010.jpg"><br />
50"	HL-P5085W	$4300, distribution by national accounts<br />
56"	HL-P5685W	$5000, distribution by regional accounts </p>

<p><u>CRT RPTVs Monitors</u><br />
TTM Apr 04, HDMI/HDCP, two component inputs<br />
42"	HC-P4252W	$1200, tabletop<br />
47"	HC-P4752W	$1300, tabletop<br />
52"	HC-P5252W	$1500, floor-standing</p>

<p>At CES 2004, Samsung announced that it would drop the 55" and 65" CRT RPTV monitor sets to focus on micro-display technologies, and is actually happening.  The company also indicated that it plans to transition the above CRT RPTV monitors to fully integrated CableCARD sets later in the year, a difficult endeavor when adding a relatively expensive HD tuner to the low cost of CRT models, according to Samsung.  One integrated (transitioned) model mentioned at CES was:</p>

<p>52"	HC-P5256W	$2,200, TTM later 04, integrated w/ATSC/QAM CableCARD, DNIe, HDMI (note the difference of $700 of the MSRP of 52" monitor vs. the estimated integrated at CES time; hopefully the difference "might" be lower at actual release time)</p>

<p><u>CRT Direct-view Integrated Models</u> (eight)<br />
Built-in ATSC tuners in four screen sizes, in-the-clear QAM digital cable tuner (omit CableCARD), DynaFlat picture tube, DVI/HDCP, accept 1080i/720p to display as native 1080i <br />
30" in 16x9 AR, three models priced between $1000-$1200<br />
26" in 16x9 AR, two models priced at $700 each<br />
32" in 4x3 AR, two models at $1000 each<br />
27" in 4x3 AR, $700</p>

<p><u>LCD Flat Panel Monitors</u><br />
46"	LT-P468W	$10,000, TTM Jul 04, 1080p (1080x1920), 12-milisecond response-time, 800:1 CR, 500 candelas brightness, the model was originally gross estimated at CES 2004 as $20,000.</p>

<p>There were no comments issued from the company representatives regarding the status of the 57" version anticipated at CES 2004 (57" LTP578W, $TBA, TTM Jun 04, 1080x1920, 1000:1 CR, 600 cd/m2, DNIe, HDMI, DVI), hopefully they are on track.</p>

<p><u>Reduction of price on HLN models</u>:<br />
In March 2004 Samsung announced a $500 reduction of prices on the earlier 2003 DLP rear-projection HDTV lineup, the 43" entry model HLN4365W is now $3200 MSRP, the 50" HLN5065W is now $3700 MSRP, and the 61" HLN617W is now $4700 MSRP. </p>

<p><u>Plasma Panels</u><br />
At the time (August) this article was prepared, Samsung was expected to announce the future introduction of plasma panels at CEDIA (Sep 04), such as:</p>

<p>37"	HP-P3761 monitor (dual NTSC tuners only), 1000:1 CR, 1000 cd/m2 brightness, <br />
42"	HP-P4261 monitor (dual NTSC tuners only), 900:1 CR, 1366x768, 1000 cd/m2 brightness <br />
55"	HP-P5581 integrated 55 inches with ATSC/QAM CableCARD tuners, 3000:1 CR, 1000 cd/m2 brightness, DVI/HDCP, MSRP TBA</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('SONY')">SONY</a></strong><br />
<div id="SONY" style="display:none"><br />
February/June/August 2004</p>

<p>On February 2004, Sony introduced twelve HDTV integrated models with ATSC/NTSC/QAM CableCARD unidirectional tuners with HDMI/HDCP digital connectivity and two HD-STBs with DVR for QAM Cable CARD tuning.  Of the twelve models, six are LCD Grand Wega RPTVs, four CRT Direct-View sets, and two CRT-based RPTV sets, as follows:</p>

<p><u>LCD Grand Wega Integrated RPTVs</u><br />
Two new series (WF and XS) were added to the entry-level (WE) and high-end (XBR) Series, and two new models were added within the WE Series, TTM Sep 04, 16:9 AR, Sony LCD Optical Engine video processing</p>

<p><u>New models on the WE Series</u><br />
42"	KDF-42WE655	$2800<br />
50"	KDF-50WE655	$3000<br />
<u>New WF Series</u><br />
55"	KDF-55WF655	$3700<br />
60"	KDF-60WF655	$4000<br />
<u>New XS Series</u><br />
55"	KDF-55XS955		$4000<br />
60"	KDF-60XS955		$4400</p>

<p><u>Direct-View Integrated CRT Tubes</u><br />
Trinitron Wega tubes, SuperFine Pitch CRT technology, Wega engine processing<br />
34"	KD-34XBR960		$2200, TTM Jun 04<br />
34"	KD-34SX955		$2000, TTM Aug 04<br />
36"	KD-36SX955		$1900, 4:3 AR, TTM Oct 04<br />
30"	KD-30SX955		$1400, 16:9 AR, TTM Aug 04</p>

<p><u>CRT-based RPTVs Integrated</u><br />
TTM Sep 04, WEGA engine, Direct Digital, DRCM (Digital Reality Creation MultiFunction), Multi-Image Driver (MID-X) circuitry<br />
51"	KDP-51WS655	$2100<br />
57"	KDP-57WS655	$2400</p>

<p><u>Flat Panel Monitors</u><br />
Large Scale Integrated (LSI) circuitry to improve response rates in LCD and contrast in plasma model<br />
23"	KLV-23M1	$2300, LCD TV, 1366x768<br />
32"	KLV-32M1	$4500, LCD TV, 1366x768<br />
42"	KE-42M1	$5000, TTM Jun 04, 480p EDTV</p>

<p><u>Hi-Scan Series FD Trinitron WEGA Monitors</u><br />
Solid silver tone, rounded corner cabinetry, dual component inputs, HDMI/HDCP<br />
27"	KV-27HS420	$750<br />
30"	KV-30HS420	$1000<br />
32"	KV-32HS420	$1000, 4:3 AR<br />
34"	KV-34HS420	$N/A, 16:9 AR<br />
36"	KV-36HS420	$N/A </p>

<p><u>LCoS RPTV (Sony's SXRD Technology)</u><br />
To pair their current QUALIA FPTV projector, Sony was expected to introduce an SXRD integrated RPTV soon.  After this article was prepared, Sony actually unveiled such set at CEDIA (September 04).  It is a 16:9 model KDS-70XBR100 within the XBR line (not the hi-end QUALIA line), native resolution of 1920x1080, 70 inches, NTSC/ATSC/QAM CableCARD tuners, 200-watt cooled lamp for 3000:1 CR, WEGA Engine System, HD component inputs, HDMI/HDCP, IEEE1394 (iLink), expected MSRP $10000, TTM early 05. </p>

<p><u>HD QAM Cable STBs</u><br />
On Sony's press release of February 04, the company announced the future introduction (by fall 04) of two new Cable HD-STBs with DVR capabilities, featuring ATSC/NTSC/QAM CableCARD tuners implementing Sony Passage integrated decryption technology.  The boxes were said to be suited with HDMI/HDCP, Gemstar integrated EPG, component output, flexible AR settings, DD 5.1 w/optical audio out, USB data ports, and memory stick for JPEG and MPEG1, as follows:</p>

<p>DHG-HDD100	$700, TTM fall 04, 120GB HDD, 120 hours SD, 12 hours HD <br />
DHG-HDD200	$800, TTM fall 04, 250GB HDD, 200 hours SD, 25 hours HD</p>

<p>However, later, in August 04, Sony issued a different press release announcing the future introduction of other models, as follows:</p>

<p>DHG-HDD250 $800, TTM fall 04, 250GB HDD, 20 hours of HD recording<br />
DHG-HDD500 $1000, TTM fall 04, 500GB HDD (two 250GB HDDs), 60 hours of HD </p>

<p>Although both units above (250 and 500) have similar tuning and connectivity features as the models previously announced in February (100 and 200), the new units were announced with different model numbers, prices, HDD capacity, and recording time. </p>

<p>Sony had not referred to the first models on the second press release of August to clarify if this new pair is a replacement or an addition to those; apparently, Sony has decided to replace the two original models (100 and 200) even before they were expected to appear in fall 04. </p>

<p>It is important to note that these HD-STBs have a connectivity limitation: they lack IEEE1394 Firewire &trade; input/outputs.  This means that a tuned/stored HD content would not be able to be output to a D-VHS recorder for HD tape archival, nor it could be part of digital networking of compressed HD video with other devices or displays.  </p>

<p>Additionally, such limitation does not comply with a specification requiring 1394 digital connectivity on Cable HD-STBs established in the plug-and-play agreement made by the Cable and Consumer Electronics industry, and approved by the FCC.  If you are interested in more details, this subject was covered on my article "HDTV Integrated Tuners and You" that appeared on the second issue of this magazine.</p>

<p>(Announced later on June 2004)<br />
<u>Plasma XS Series Integrated</u><br />
Digital Cable Ready, third-generation WEGA engine image processing, TTM Aug 04<br />
37"	KDE-37XS955		$5500, 1024x1024<br />
42"	KDE-42XS955		$7000, 1024x1024<br />
50"	KDE-50XS955		$9000, 1366x768</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Thomson')">Thomson</a></strong><br />
<div id="Thomson" style="display:none"><br />
New York, May 2004</p>

<p>Thomson join venture with China's CTL (TTE) starting in July 04 will produce for the US market eleven fully integrated ATSC and Digital Cable Ready models with QAM CableCARD unidirectional (seven RCA Scenium DLP models, four RCA CRT RPTV models), with HDMI, and with component inputs.  The new sets are said to recognize the Broadcast Flag.  According to TTE, the company will become the largest company in the world for color TV products; selling 18 million sets annually (with a 22 million production capacity), representing 11% globally.  </p>

<p><u>"Profile" Scenium DLP Integrated</u><br />
Ultra-thin two new models, ATSC/QAM CableCARD tuners, TTM Sep 04, 6.85 inches deep and less than 103 pounds make them suitable for wall hanging, hanging bracket sold separately, dual IEEE-1394 two-way/DTCP, TV Guide Onscreen EPG and Internet browser, will detect broadcast flag, HD2 chip, HDMI/HDCP, dual component HD inputs<br />
50"	HD50THW263		$9000<br />
61"	HD61THW263		$10000<br />
Thomson did not update/confirm the status of the future 70" DLP mural-sized set announced in January at CES (HD70THW263), expected for early 2005, hopefully they on track.<br />
<u>Other Scenium DLP Integrated</u><br />
Five new sets, 16 inches cabinet depth, under 100 pounds, HDMI/HDCP, ATSC/QAM w/CableCARD/NTSC tuners, 160 degree viewing angle, component, HD2 chip, controls DVR functions of optional DVR2080<br />
<u>165 Series</u><br />
IEEE-1394, Internet browser, TV Guide EPG, TTM fall 04<br />
44"	HD44LPW165	$3700 <br />
<u>163 Series</u><br />
IEEE-1394, Internet browser, TV Guide EPG, TTM Aug 04<br />
50"	HD50LPW163	$4000 <br />
61"	HD61LPW163	$4600</p>

<p><u>162 Series</u><br />
ATSC/QAM cable in-the-clear tuners, excludes: TV Guide EPG, Internet browser, and IEEE-1394 interface.  TTM Jul 04<br />
50"	HD50LPW162	$3800<br />
61"	HD61LPW162	$4400</p>

<p><u>CRT RPTV Integrated</u><br />
Four new sets w/ATSC and QAM cable tuners, HDMI/HDCP, component, TTM fall 04<br />
52" 	HD52W55	$1900<br />
52"	HD52W56	$2000<br />
<u>58 group</u><br />
Subwoofer output, protective screen shield, SRS Focus<br />
52"	HD52W58	$2300<br />
56"	HD56W58	$2500</p>

<p><u>Current 42 Series DLP Integrated</u> (continues in the line up)<br />
ATSC/QAM cable in-the-clear tuners, includes EPG, Internet browser, HDMI, and IEEE-1394<br />
50"	HD50LPW42	$3800<br />
61"	HD61LPW42	$4300</p>

<p><u>CRT RPTV Monitors</u> (carried over)<br />
DVI/HDCP <br />
52"	D52W15	$1500<br />
52"	D52W20	$1700<br />
56"	D56W20	$2000<br />
61"	D61W20	$2200</p>

<p><u>Plasma Flat Panels</u><br />
Thomson discontinued the plasma line; their supplier (NEC) was acquired by Pioneer.  Thomson will concentrate on DLP and LCD.</p>

<p><u>LCD TV</u><br />
<u>RCA Line Monitor</u><br />
26"	LCDX2620W	$2600, TTM Jun 04, 1280x768, open distribution, DVI/HDCP, component, RGB, NTSC tuner, 600:1 CR, 500 cd/m2 brightness<br />
<u>RCA Scenium Line</u><br />
NTSC tuning monitors, DVI/HDCP, component, RGB, 500:1 CR, 500 cd/m2 brightness, 170 degrees of vertical/horizontal viewing<br />
27"	LCDX2722W	$2800, TTM Jun 04, 1280x720<br />
32"	LCDX3022W	$3800, TTM Jul 04, 1280x768</p>

<p><u>Two new RCA Scenium HD DVRs</u><br />
Interface w/new integrated TV sets via IEEE-1394 connections; recognize DTCP and Broadcast Flag<br />
DVR2160	$550, 80 hours of SD, 18 hours of HD recording<br />
DVR2080	$450, 40 hours of SD, 9 hours of HD recording</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Toshiba')">Toshiba</a></strong><br />
<div id="Toshiba" style="display:none"><br />
May 2004 (Austin Texas) </p>

<p>Toshiba announced its 2004-05 television line to dealers.  The new line is mainly oriented to fixed-pixel digital display technologies, such as direct-view LCD TV, plasma, Digital Light Processing (DLP) rear-projection integrated sets and monitors, in addition to CRT-based rear-projection and direct-view products.</p>

<p>Additionally, Toshiba introduced an HD 160GB digital video recorder (Symbio 160HD4, $500) to connect via IEEE-1394 with their integrated sets.  The programming circuitry is based in part on Gemstar's TV Guide Onscreen interactive program guide, and since is built within Toshiba's fully integrated HDTV sets it makes the Symbio compatible only with those sets.</p>

<p>In January 2004 (CES), Toshiba announced their decision of discontinuing the LCoS line, which is now replaced by their support to DLP.  In May, Toshiba formally announced the addition of 10 RPTV DLP sets and monitors implementing the pairing of the HD2+ chip of Texas Instruments (0.8-inch Digital Micromirror Device - DMD) with the Toshiba Advanced Light Engine (TALEN).  The company's two-year goal is to position their DLP products within the top-three DLP TV manufacturers, by using advance optics together with the HD2+ DMD chip, as opposed to the HD3 chip-based sets of the competition.</p>

<p>New models of fully integrated HDTV sets include ATSC/QAM tuners with CableCARD slots for unidirectional digital cable ready capability, and TV Guide Onscreen interactive program guides.  Most sets also include both HDMI/HDCP and IEEE-1394/DTCP digital interfaces. </p>

<p><u>DLP RPTVs</u><br />
HD2+ chip, Magic Square Algorithm for smooth color gradation, Dynamic Contrast Enhancer for higher contrast, color purity, and saturation, Super Real Transient and Small Signal Sharpness for sharp transitions from dark to light, Color Transient Improver and Color Detail Enhancer, flat-panel plasma displays type of look, silver cosmetics and thin cabinets, and under-screen glass component cabinets.</p>

<p><u>TheaterWide Series DLP RPTVs</u><br />
Silver cabinets with a gray bezel<br />
<u>HM84 Tabletop Monitors</u><br />
TTM Jul 04<br />
46"	46HM84 	$3000<br />
52"	52HM84 	$3500<br />
62"	62HM84 	$4000</p>

<p><u>HM94 Tabletop Integrated</u><br />
ATSC/QAM CableCARD tuners, IEEE-1394, DTVLink, HDMI, TTM Sep 04<br />
46"	46HM94	$3400<br />
52"	52HM94	$3900<br />
62"	62HM94	$4400</p>

<p><u>Cinema Series DLP RPTVs</u><br />
Same as TheaterWide Series plus cabinetry with black bezel accents, virtual Dolby surround sound, 6-item A/V illuminated remote<br />
<u>HMX84 monitors</u><br />
52"	52HMX84	$3800, TTM Aug 04<br />
62"	62HMX84 	$4300, TTM Sep 04<br />
<u>HMX94 Integrated</u><br />
Two HDMI, TTM Oct 04<br />
52"	52HMX94	$4200<br />
62"	62HMX94	$4700</p>

<p><u>CRT RPTVs</u><br />
Analog and 4:3 aspect ratio sets are now discontinued <br />
<u>TheaterWide Monitors</u><br />
46"	46H84		$1400, Jun 04, tabletop<br />
51"	51H84		$1700, May 04<br />
57"	57H84		$1900, May 04<br />
65"	65H84		$2200, Jun 04</p>

<p><u>TheaterWide Integrated</u><br />
QAM CableCard/ATSC tuners, IEEE-1394, TV Guide On-screen interface <br />
51"	51H94		$2100, Jul 04<br />
57"	57H94		$2300, Sep 04</p>

<p><u>Cinema Series Integrated</u><br />
QAM CableCard/ATSC tuners, IEEE-1394, TV Guide On-Screen interface<br />
51"	51HX94	$2400, Aug 04<br />
57"	57HX94	$2600, Sep 04<br />
65"	65HX94	$2900, Oct 04</p>

<p><u>Flat-panel Displays</u></p>

<p><u>LCD Monitors</u><br />
DVI or HDMI, Cable clear DNR+ video noise reduction circuitry<br />
<u>EDTV 4:3 LCD Monitors</u><br />
500:1 CR, component input, 480x640<br />
14"	14DL74	$500, Jun 04<br />
20"	20DL74	$1000, Jun 04<br />
<u>TheaterWide 16:9 LCD Monitors</u><br />
DVI/HDCP<br />
23"	23HL84	$1800, Aug 04, 1280x768, 500:1 CR<br />
23"	23HLV84	$2000, Aug 04, LCD TV/DVD combi-unit, 1280x768, 500:1 CR<br />
26"	26HL84	$2500, Jun 04, 1366x768<br />
32"	32HL84	$3500, May 04, 1366x768</p>

<p><u>TheaterWide Plasma Monitors</u><br />
42"	42HP84	$5500, Sep 04, 1024x768<br />
50"	50HP84	$TBD (estimated at $7500), Oct 04, 1024x768 </p>

<p><u>Cinema Series Flat-panel Monitors</u><br />
New double-baffle design<br />
32"	32HLX84	$4000, Oct 04, LCD monitor, 1366x768, 800:1 CR<br />
42"	42HPX84	$6000, Sep 04, plasma display monitor, 1024x768 </p>

<p><u>Direct-view CRT Monitors</u><br />
<u>TheaterWide Line</u><br />
HDMI<br />
26"	26HF84	$700, Aug 04<br />
30"	30HF84	$900, Jul 04<br />
34"	34HF84	$1400, Jun 04<br />
<u>Cinema Series Line</u><br />
30"	30HFX84	$1000, Aug 04<br />
34"	34HFX84	$1600, Jul 04</p>

<p>Toshiba anticipates a demand for direct-view digital televisions and will keep producing complete lines of analog CRT direct-view models (curved and flat-faced).</p>

<p><u>DVD Recorders</u> (I am adding this non-DTV information due to the HD upconversion feature of the last unit)</p>

<p>In regards to DVD recording, Toshiba announced several new products, single-deck, DVD/VHS, DVD/DVR, DVD/TiVo, and TiVo/DVR combined with DVD-RW/-R recording.  TiVo units use the Series 2 platform with networking upgrade capability and free basic service, and come with 120GB ($600, Aug 04) and 160GB ($700, Aug 04) capacity.  They are suited with a new Easy Navi menu system (simplifying the archiving of camcorder tapes to disc), and with IR blaster capability for automatic recording from separate connected components.</p>

<p>D-R3	 	$350, TTM Sep 04, single-deck DVD recorder w/DVD-RAM/-R<br />
D-VR3		$500, TTM Jul 04, combination DVD-RAM/-RW/-R and VCR dual-deck recorder with bi-directional dubbing</p>

<p>One unit in particular features an interesting capability related to HD, is a DVD recorder with DVR and HD upconversion:<br />
 <br />
RDXS53	$700, TTM Oct 04, combination DVD-RAM/-R DVD recorder, 160GB hard drive recorder, TV Guide On-screen program guide, HDMI output, 720p/1080i upconversion over HDMI</p>

<p>In addition, Toshiba will introduce a full line of DVD players (single-drive, dual-deck and changer configurations) with HDMI.</p>

<p><br />
</div></p>

<p><br />
<strong>Analysis, conclusions</strong></p>

<p>It was noticed an increased support for microchip-based RPTV displays, mainly using LCD and DLP technology.  Some TV manufacturing companies are switching in and out of the LCoS technology due to difficulty/availability of chips, or due to company direction.  Additionally, Intel has announced in August their delay in the delivery of LCoS chips for projection TVs, not to be by year-end as planned; while its competitor, Advanced Micro Devices Inc., is still on course manufacturing chips as planned.</p>

<p>Some large LCD TV panels are now exceeding the 40" mark, a size long considered the domain of plasmas in the flat-panel market.  Although with a handful of new large LCD panels, there is now an overlap in the 37 to mid-40-inches range covered by both technologies, however, new large LCD TV panels generally retail for at least twice the cost of similar size plasmas.  It must be noted that LCD TV panels have reached 1080x1920 resolution on the 45+ size, a resolution that plasma will have in much larger panels, but does not have in the sizes of the overlapping range.  One LCD TV panel is the Sharp's 1080p 45" LCD TV panel (AQUOS models 45GD4U and 45GD6U) expected to ship in the fall of 2004 at $10000 MSRP.</p>

<p>The DTV industry is gradually moving from the manufacturing of CRT-based displays to support other technologies, however, most major companies are still introducing new lines of RPTV and Direct-view models based on CRT technology, which continue to offer the best value for a good quality display (and the rendition of black), if space and weight are not a constraint.  </p>

<p>As to be expected, there is a noticeable boost in the manufacturing volume of integrated sets to meet the FCC mandate and deadlines.  However, if you look carefully across 2004/5 TV lines, you will find that there is still a substantial additional cost charged to consumers for integrated OTA ATSC and QAM CableCARD tuners, the latter with only unidirectional cable limited functionality.  </p>

<p>While the boost in the release of new integrated lines and models is seen in the manufacturing, the actual sales to the final customer on this initial period of large availability of integrated models is not showing a similar boost.  In other words, some companies that still offer monitor-only versions experienced more sales of monitors than integrated models.  That phenomenon is expected to change, not because people prefer (or prefer to pay extra for) integrated tuners, but because most monitors will gradually disappear, as mandated by the FCC.</p>

<p>Customarily, manufacturers reduce the price of models that are about to be replaced, like Samsung, which in March 2004 had reduced by $500 its previous DLP RPTV HLN line, replaced by the 2004 HLP line.  However, many companies are not actually reducing prices when replacing their HDTV current models with newer models, which could be a way to accelerate the HDTV adoption by passing to consumers the savings gained from higher volume and more efficient manufacturing.</p>

<p>Instead, many companies are introducing new models at similar prices than the sets they replace, and in many cases even higher.  Some justify it by the implementation of newer upscale features/technology (like HD2 improved by HD3 and later by HD2+ TI's DMD chips).  Others justify it by the inclusion of the mandated built-in ATSC/QAM cable tuners, jacking up the TV price by $400-$1000 depending of the display technology and the manufacturer.  Although is starting to look better for consumers, "some" manufacturers that added $700 for the HD tuner integration of their 2003 models now charge $500.</p>

<p>Uninformed consumers looking for near future HDTV purchases would most probably not notice the actual dollar impact of integration to their pockets, because they eventually would not find monitor-only versions available to be able to compare their lower cost against their integrated versions, as they could now.  In other words, imagine being forced to buy a new refrigerator when just need to replace the kitchen counter top, because "now it comes with it", everyone sells them together, and the refrigerator is made by the counter top manufacturer.   </p>

<p>It was noticeable the large adoption of DVI and HDMI connectors in HDTV displays (also on HD-STBs and DVD players with upconversion to HD) for the transmission of protected digital HD uncompressed video.  If you have the choice go with HDMI.  Additionally, in most cases, the number of inputs in HDTV displays is still not enough to accept multiple DVI/HDMI suited equipment, which might force you to pay for a DVI/HDMI switcher (not coming cheap).  A/V receivers and pre/pro equipment with multiple DVI/HDMI input/outputs are still absent.  On the plus side, the industry is moving consistently in that direction.   </p>

<p>It was also noticed an increased support to suit integrated HDTV sets and HD-STBs (except DirecTV) with two-way IEEE1394 inputs/outputs for HD compressed video connectivity, which allow for HD networking and external HD recording to DVRs and D-VHS.  However, if you read the specifications on the announcements carefully, you might notice that a great number of integrated HDTVs do not mention the inclusion of IEEE-1394 connectors.  It could be an omission on the early announcement of the specifications, but it could also be an omission of the connection itself.  </p>

<p>My best suggestion to you: before committing yourself to wait for a future model, confirm the features you need if they are not fully spelled out on the announcements, and, in the case of 1394, do not buy an integrated HDTV without 'activated' IEEE-1394 two-way connections.  Even when the 1394 connection is present, it might only work with the manufacturer's proprietary implementation of it, incompatible to other brands and models, such as the case of the Toshiba's new stand-alone DVR (Symbio) that works only when paired to certain new Toshiba's integrated TVs.  </p>

<p>A handful of integrated HDTVs are now suited with internal DVR capabilities, but even when the unit can record internally a program for time-shifting, if you are also interested in archiving to D-VHS, make sure the HDTV has IEEE-1394 connections to permit for that.  </p>

<p>Some Broadcast Flag compliant products are starting to appear, earlier than mandated by the FCC.  The rush for a purchase of a compliant product, rather than a legacy product that does not recognize the Broadcast Flag, might not necessarily be of your best interest (for more in the subject check my article regarding DTV content protection regulations, on issue # 4 of this magazine).</p>

<p>Regarding the expectations for the arrival of some innovating products promised at CES:</p>

<p>The oversized 1080x1920 plasmas (LG 71" Nov 04, LG 76" Jan 05, and Samsung 70" HPP7071 4Q04),<br />
The 57" LTP578W LCD 1080x1920 TV (Samsung, Jun 04, later updated to 4Q04),<br />
The Blu-ray Hi-def DVD player/recorders (Samsung mid 04, LG 3Q04), <br />
The 1080p xHD3 DLP RPTVs (Samsung mid/late 04), and <br />
Voom's network HD-STBs (Motorola 580 DVR server/clients, mid 04)</p>

<p>Although they were originally expected by mid/late 2004, the products are still unreleased as of this writing (August).  Perhaps, by the time you read this, some of those products might start to appear, but if not, it would give you more time for saving for their purchase; there is always a positive side on everything.</p>

<p>On another positive side, the H/DTV industry is moving much faster than when the first sets appeared in 1998, this is very good to consumers and manufacturers alike, we have more technologies, better products, and a larger variety to choose from, the dream of HDTV is finally becoming a reality.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February  3, 2006  5:44 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(289)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 289)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/hdtv-products-looking-into-the-future.php" type="text/javascript" charset="utf-8"></script>
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
