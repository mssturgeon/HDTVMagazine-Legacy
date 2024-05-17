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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1435 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1435
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It.&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/06/high-definition-movies-before-they-hit-bluray-only-if-your-hdtv-permits-it.php&amp;title=High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It.">
		<span style="display:none">How would you like to view an HD movie at home via Video-On-Demand (VOD) just a few weeks after its theatrical release, even before it's released on DVD and Blu-ray? Maybe your HDTV connections would not let you do it, despite the fact that you paid for the movie.

On May 9, 2008, the MPAA issued a petition to the FCC for a waiver of the SOC feature...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1435";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It." height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It." />
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
	<title>HDTV Magazine - High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It.</title>
	<meta name="keywords" content="component analog, content protection, analog connection, blu ray, digital connections, hdmi, content, analog, digital, hdtv, connection, component, connections, could, soc, cable, million, video, might, protection, hdtvs, protected, fcc, vod, those" />
	<meta name="description" content="How would you like to view an HD movie at home via Video-On-Demand (VOD) just a few weeks after its theatrical release, even before it's released on DVD and Blu-ray? Maybe your HDTV connections would not let you do it, despite the fact that you paid for the movie.

On May 9, 2008, the MPAA issued a petition to the FCC for a waiver of the SOC feature..." />
	<meta name="title" content="High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It." />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/06/high-definition-movies-before-they-hit-bluray-only-if-your-hdtv-permits-it.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1435', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/06/high-definition-movies-before-they-hit-bluray-only-if-your-hdtv-permits-it.php">High Definition Movies Before They Hit Blu-ray? Only if Your HDTV Permits It.</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>June 17, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=331&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>, <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>, <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p>How would you like to view an HD movie at home via Video-On-Demand (VOD) just a few weeks after its theatrical release, even before it's released on DVD and Blu-ray? Maybe your HDTV connections would not let you do it, despite the fact that you paid for the movie.  <p><b></b> <p><b></b> <h2>The FCC, the MPAA, and your HDTV</h2> <p>Through the years I have been warning the public about the risk of using an analog connection for an HDTV, because it might eventually face a limitation in the quality of the displayed image (or show no image at all) if the content provider decides to put into effect the full content protection scheme intended for analog and digital connections, courtesy of the MPAA (Motion Picture Association of America).  <p>Under such a scheme, the protection on the content could trigger a feature on the cable/satellite HD set-top-box (HD-STB) designed to only send the HD image to its protected digital outputs, and disable or downgrade the image sent to the component analog outputs. This feature is known as "Selectable-Output-Controls (SOC)", and is how the MPAA wishes to implement content protection.  <p>If your HDTV is connected with component analog wires, you might either see a blank screen, or an image that could only be about 16% of the original resolution of the protected image. If you have digital connections <b>that work well</b> you might be one of the lucky ones. Otherwise, <b>no HD movie on your HDTV</b>.  <p>The "Broadcast Flag" initiative followed a similar objective with premium broadcast content, for a similar content protection concern. The "Flag" embedded into the content was proposed by the FCC in November 2003 to limit the indiscriminate redistribution of digital broadcast content, so premium content would not end up in the hands of pirates.  <p>Consult the details in the <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">2005, 2006</a>, and <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">2007 (Industry Edition)</a> HDTV Technology Review books, and this <a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">Analysis of DTV Content Protection</a> article.  <p>Along the same lines, on December 2002, under the "Plug-and-Play" cable agreement made between the consumer-electronics and cable television industries, the FCC approved a "memorandum of understanding" to implement several features in cable HD-STBs and integrated digital TVs.  <p>Some of those features were:  <p>a) Cable CARDS,  <p>b) The addition of IEEE 1394 "FireWire" digital connections to allow viewers to externally record/network compressed HD (protected by DTCP, Digital Transmission Content Protection),  <p>c) The addition of DVI (Digital Video Interface) or HDMI (High-Definition Multimedia Interface) digital connections with HDCP (High-bandwidth Digital Content Protection) for the secure viewing of uncompressed HD video on HDTVs.  <h2>Get to the Point</h2> <p>The main point of this article is that on the agreement, the FCC also prohibited in 2002 the operation of the SOC feature included within HD-STBs. That gave some relief to millions of HDTV early adopters owning sets designed with only component analog inputs, but the FCC left the SOC implementation decision pending until "further notice".  <p>Back then I highlighted part of the text of the "memorandum of understanding" as announced by the CEA in 2002, as follows: "<i>But Selectable Output Controls may some day in the future be used</i>".  <p>I warned consumers about the loose end on the agreement, and anticipated that the MPAA will come back for the "unfinished business".  <p><b></b> <h2>It is now Happening, and in a Rush </h2> <p><b></b> <p>6 years later SOC came back to haunt HDTV again.</p> <p>On May 9, 2008, the MPAA issued a <a href="http://www.hdtvmagazine.com/downloads/mpaa-petition.pdf">petition to the FCC for a waiver of the SOC feature</a> to be permitted, but only to protect new "compelling" content they have in the plans.</p> <p>The MPAA said they plan to make available through MVPDs (Multi-channel Video Programming Distributors, such as cable/satellite/telephone/IPTV) new movies just after their theater release so consumers can view them at home with VOD (Video on Demand) <b>before they are sold on DVD or Blu-ray</b>.  <p>The waiver was justified over the concern of the possibility that the content could be copied and distributed illegally through piracy, which could negatively affect the subsequent packaged media business (DVD and Blu-ray sales) and PPV (Pay-Per-View). But it would give the MPAA too much leeway in its implementation of SOC and many consumers with non-compliant HDTVs could be left in the dark, literally.  <p>The FCC is currently reviewing the matter to make a decision, with no deadline set. On June 5<sup>th</sup>, the FCC requested comments from the industry and the public before a June 25, 2008 deadline. The comments can be filed as follows:  <p><a href="http://www.fcc.gov/Daily_Releases/Daily_Business/2008/db0605/DA-08-1081A1.pdf">http://www.fcc.gov/Daily_Releases/Daily_Business/2008/db0605/DA-08-1081A1.pdf</a>  <h2>A Potential Problem </h2> <p>SOC counts on HDMI working well. Too much trust is put on the assumption that protected digital connections were implemented properly on consumer electronics, and that is not the case.  <p>From installers to reviewers to owners, the general understanding is that for over 5 years already, HDMI cannot yet be trusted to be as reliable as component analog, reason by which many installers declared on a recent survey (<a href="http://www.cepro.com/article/hdmi_or_component_integrators_weigh_in">HDMI or Component - Integrators Weigh In</a>) that they would rather install component analog wires.  <p>Although the justification for the request to the FCC seems valid considering the compelling content, the opportunity of obtaining the waiver might start something broader. SOC could later be applied to other HD content, and make all displays with component analog connections (and those with HDMI problematic connections) useless for the HD viewing of other HD content, not just this premium VOD the MPAA is about to implement.  <p>On the 15-page MPAA petition a couple of statements indicated that the waiver for SOC should not affect any other channel currently received by the MVPD subscriber, but politics and lobbies could eventually bring another SOC waiver request for premium channels riding over this SOC waiver for premium VOD, if granted. Add to that situation the issue of problematic HDMI connections and it could become a checkmate for many.  <p>Additionally, although the petition mentions that the VOD service is also applicable to integrated HDTVs (because of their internal CableCARD tuners), it errors on the fact that all those millions of HDTVs cannot actually receive bi-directional VOD services because their internal CableCARD cable tuners are just unidirectional, and a HD-STB would be required (more on this later).  <h2>Need More Background?</h2> <p>I have written in the past about the limitations that content protection could impose to lawful HD viewing, for which consumers bought expensive HDTV sets.  <p>The subject has become so convoluted that is now usually a chapter on each of my annual <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology books</a> since 2005.  <p>On the <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">2007 Industry Edition version</a>, the subject is summarized on chapter 26 (pages 511-529), but it has been also covered on these two articles:  <p><a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">Analysis of DTV Content Protection</a>  <p><a href="http://www.hdtvmagazine.com/articles/2006/02/is_hdtv_complex_enough.php">HDTV Complexity</a>  <h3></h3> <h2>Who could be affected? - The Full Current HDTV Footprint</h2> <p>CEA's president and CEO Gary Shapiro said in December 2007:  <blockquote> <p>"With 50% of U.S. homes able to experience the reality of digital television, we have crossed a critical threshold. 2008 will continue to demonstrate the growth and success of DTV, with nearly 32 million units forecasted to ship."</p></blockquote> <p>The CEA reported in 2007 that the US has about 289 million people/TVs in 111 million households, a ratio of 2.6 TVs per household. 83 million HDTVs were sold between 1998 and 2007 (of which 29.2 million were reported as projected for 2007).  <p>We know that 11 million of those 83 million HDTVs have only component analog inputs (sold to early adopters between 1998 and 2003); the remaining 72 million (sold between 2004 and 2007) <b>should</b> have DVI or HDMI digital inputs (in addition to component analog connections), most with HDCP content protection.  <p>A large number of those 72 million HDTVs might have integrated cable tuners with CableCARDs to comply with the FCC integrated tuner mandate, but those tuners only have unidirectional capabilities, they are not suited for VOD and impulse-PPV.  <p>Which means that an HD-STB from the cable company would still be required for those services (and for the VOD premium services the MPAA is requesting the SOC waiver). An HD-STB would require a protected DVI/HDMI connection to the HDTV. For this SOC model to work the HDMI connection should be bulletproof, and is not.  <p>The market has still not introduced any integrated HDTV with bi-directional cable capabilities. Such a feature would make the HDTV capable of VOD by itself, and not need the HD-STB or HDMI for that purpose.  <p>Recently, "Tru2way" efforts between the cable industry and some TV manufacturers such as Sony, Samsung and Panasonic to make integrated HDTVs with bi-directional cable features were announced. But most of those sets are a year or more away from becoming available in volume, and during that time another 30 million + integrated HDTVs with just unidirectional CableCARD cable tuners would be dumped to the US market on a yearly basis.  <p>By the end of 2008, the full 115 million HDTV owners (115 = 83 million sold 1998-2007, + 32 million estimated by CEA for 2008) could be subjected to connectivity issues with SOC-protected content from HD-STBs if using component analog connections, or unreliable HDMI connections; although most could be lucky and not have any problems if the equipment connected is compatible and the installation performs well.  <h2>The Connectivity Choices</h2> <p>An HDTV has two ways to receive HD video from an external device (such as the typical cable/satellite/over-the-air HD-STB, or a Blu-ray player).  <img style="margin: 0px 0px 5px 5px" alt="Component Analog" src="http://www.hdtvmagazine.com/images/mt/8ad07be07cdf_DAA5/image.png" align="right" border="0"><h3><i>Component Analog:</i> </h3> <p>One way is by using the legacy 3-component analog wire connection, typically known as YPbPr, or componentized in the form of analog RGB, VGA, or 15-pin D-sub; capable of up to 1080i HD resolution (the 1080p resolution was not approved within the component analog standard).  <p>The analog component connection carries video only; digital audio is carried separately using legacy optical/coaxial connections and support lossy multi-channel audio, such as Dolby Digital or DTS legacy audio formats.  <br clear="all"/><img style="margin: 0px 0px 5px 5px" alt="Digital DVI or HDMI" src="http://www.hdtvmagazine.com/images/mt/8ad07be07cdf_DAA5/image_3.png" align="right" border="0"><h3><i>Digital DVI or HDMI:</i> </h3> <p>The other way to connect to a display device to view HD is using a digital connection that carries uncompressed HD video and multi-channel digital audio in a single HDMI wire (or a DVI cable that carries only the video). HDMI supports 1080p since version 1.0, but HD-STBs output 1080i not p. HDMI can also carry multi-channel PCM audio.  <p>While beyond the scope of this article (because HDTV has only Dolby Digital 5.1 as audio standard), when the HD source is a Blu-ray player, a connection with HDMI version 1.3 can also carry lossless hi-bit streaming audio for the new Dolby True HD, DTS HD and Master Audio, and Dolby Digital Plus codecs. Consult details about HDMI on <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php">this series of 10 HDMI articles</a> and <a href="http://www.hdtvmagazine.com/articles/2007/08/hi-def_dvd_-_audio_streaming_over_hdmi.php">Audio with HDMI</a>.  <p>Digital content over HDMI is generally protected with HDCP to deter the unlawful copy and distribution of digital content with a quality similar to the original.  <p><a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29</a>  <h3><i>The Content Protection Effect on the Analog Connection:</i> </h3> <p>One main issue is that the analog connection is not content protected. If a movie is protected the digital protection is not carried over the analog connection when the video is converted from digital to analog, and anyone with the means could re-digitize the content from the analog connection, and copy and distribute it illegally in digital form (this is known as the "analog hole").  <p>For that reason, if the content is protected and SOC is implemented it could direct the HD-STB to disable the output completely, or to "downrez" the quality of the image over the analog connection, lowering it to 16% (only 704x480, SD quality) of the original resolution (1920x1080i, HD quality).  <p>If you knew that your HDTV system could be subjected to these limitations due to connectivity and content protection you might have preferred to postpone your HDTV purchase and keep the old NTSC analog TV to down-convert HD to SD. <p>However, this is not only about SOC affecting component analog connections.  <h2>It Might Happen to an HDTV near You </h2> <p>A large number of HDTV consumers could be affected.  <p>For example,  <p>a) Approximately 11 million HDTVs acquired by early adopters between 1998 and 2003 only had analog connections,  <p>b) Millions of PC Home Theaters still have monitors connected with analog cables, and do not have DVI/HDMI connections,  <p>c) 72 million of post-2003 DVI/HDMI capable HDTVs, many of which are actually not using the digital connections, for various reasons.  <h2>Connect with Component Analog an HDMI capable TV?</h2> <p>Why would someone connect an HDTV with analog connections when the TV is already suited with HDMI or DVI digital connections?  <p>Although many could have used component analog due to ignorance of the subject of this article, or due to the cost of some HDMI cables, etc, many others found the hard way that HDMI connections are not <b>consistently</b> as good as advertised, and stayed away from HDMI.  <p>Even after 5 years of the HDMI implementation, many cable and satellite HD-STBs, Blu-ray players, video-scalers, video-processors, HD switchers, A/V receivers used for HD switching, and even the cables themselves (due to length or construction quality), might not handle HDMI and HDCP consistently well, even with high quality equipment.  <p>The handshake required by the HDMI connection between source and receiver is in some cases fragile enough to fail, even with a direct connection to the display device. Add more pieces of equipment to the HDMI path, such as HDMI switchers and A/V receivers with HDMI inputs/outputs to centralize the audio/video controls, and compatibility and signal sensitivity problems increase.  <p>Although some problems could be solved by firmware upgrades from manufacturers, others cannot if they are hardware related, such as the implementation of a substandard HDMI chip for its application, a cable that is too long or poorly constructed, an HDMI chip that does not conform to the standards, etc.  <p>On those events, the image might suddenly be lost, or might come on and off intermittently, displaying blue screens or black screens, when switching channels, when changing images with different resolutions, or even in the middle of a movie.  <p>Some front projectors might lock-up their operation forcing a full shutdown and reboot, which require cool-down and warm-up mandatory time to complete both cycles properly.  <p>Under such risk, having a parallel component analog connection provides peace-of-mind for uninterrupted viewing. I recommend being on the safe side and plan for dual connectivity with quality component analog and HDMI wiring, especially for in-wall installations (trying the connections out of the wall first). I would not hire a professional in-wall installer that does not welcome dual connectivity if you want to pay for it.  <p>There is the general believe that a digital connection is a better connection from a digital source to a digital display, because the signal is not subjected to unneeded digital-to-analog conversions and video processing in the communication path between the source and the display device.  <p>Interestingly enough, in many cases, the analog component legacy connection may supply an image that might look better than the digital connection. Before you assume that the digital connection is better, I recommend trying the component analog connection, you might be surprised. The display device must be calibrated to its best for each input/connection before performing any comparison.  <p>A professional ISF (Imaging Science Foundation) calibration for every input could cost several hundred dollars, but you could try doing the basic video adjustments yourself with a calibration DVD / Blu-ray/ HD DVD disc to set the TV inputs the best you can do, and then test the connections to see which one you like best.  <p>Eventually you might still decide to calibrate the set professionally. If you do, you will have only lost the price of the calibration disc, and your time, but gained good knowledge of the subject by doing it yourself.  <p><b></b> <p><b></b> <h2>So Some of Your Connectivity Options Are </h2> <p><b></b> <p>A) Use a component analog connection for unprotected content if the HDMI is unreliable, or if it simply looks worse.  <p>B) Use an HDMI digital connection for protected content even if may look worse than component analog, because if it works, you would at least have an HD image to view.  <p><b></b> <p>C) If the HDMI connection is unreliable, try shortening the HDMI path between the HD-STB and the HDTV, avoiding other electronic equipment in between, maybe that solves the problem and becomes a permanent reliable connection for the B case above.  <p><b></b> <p>D) Having both connections operating in parallel allows you to conveniently alternate them based on content, image quality, and connectivity problems.  <p>However, if the HDMI is not working consistently well, the content is protected, and SOC in the HD-STB has been implemented, there is not much else you can do to be able to view the HD content you paid for in HD, so you would be restricted to SD, and that is what the FCC should know from you. The current conditions of HDMI in the market should play a very important role in the approval (or rejection) of any SOC feature.  <p><b></b> <h2>Conclusion </h2> <p>While content is important for the art and artists, it is also important for the movie industry, for the electronic industry, for the video service distribution industry, and for the consumers. But if consumers cannot properly view the content with the devices the electronic industry made available to them for exactly that purpose, content providers might suffer another type of loss other than piracy: consumers might not buy the content, and we all loose.  <p>It is possible to identify the weak pieces within the content-provider-to-consumer chain so they can be improved and reduce the potential loss to all. The problem is doing it timely and responsibly.  <p>Since consumers pay for the content and for the electronic devices to view it, they have the right to demand to the FCC, and those industries, the need for harmony among software and hardware devices to produce content and electronics that work well together, and evolve well, as opposed to being developed and implemented in independent parallel paths and timings to satisfy their individual objectives, and hope they will fit down the line.  <p>As with HDTVs, connectivity, and content protection, cars with square wheels are not useful for their primary purpose. One expects that both manufacturers work in harmony because wheels and cars are entities that need each other to be a product worth buying, and if the car cannot be sold, its security system (SOC) is also useless.  <p>Therefore, in consideration to:  <p>a) The component analog legacy of 11 million early adopters that helped establish HDTV on the first 5 years,  <p>b) The HDMI implementation with reliability/compatibility problems,  <p>c) The 72 million (and growing) DVI/HDMI capable HDTVs that would still require HD-STBs and HDMI for VOD SOC protected content, and  <p>d) The unavailability of integrated Cable-CARD HDTVs with bi-directional capabilities for VOD/impulse PPV within the TV (not needing HDMI),  <p>Any ruling insensitive to those factors could certainly exacerbate an inter-industry situation that is already out of harmony in timing, standards, and implemented features, which has affected HDTV consumers since 1998.  <p><b>Mistakes were made</b>, let us learn from them and not perpetuate the pain of the ones footing the bill: The Consumers.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>June 17, 2008  4:32 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1435)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1435)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/06/high-definition-movies-before-they-hit-bluray-only-if-your-hdtv-permits-it.php" type="text/javascript" charset="utf-8"></script>
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
