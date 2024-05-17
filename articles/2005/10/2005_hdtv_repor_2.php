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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 234 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 234
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-4-satellite-cable-broadcasting.php&amp;title=2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting">
		<span style="display:none">DirecTV, Dish Network, and Cable are constantly pursuing an increase of their HDTV line up to gain market share.  The launching of more satellites and the &quot;upgrade&quot; to MPEG-4 AVC compression provide the satellite companies an opportunity for such gain; the sale of VOOM to Dish Network creates more forces on that competition; what an HDTV consumer is supposed to expect next with these compression plans when is known that true 1080i resolution is not currently being delivered?  OTA broadcasting is increasing their SD multi-casting efforts, and their HDTV is gradually being subjected to the pressure of offering more quantity rather than quality.  Although the HDTV offerings are on the increase and 1080p displays are starting to appear, it seems contradictory that the delivering of true HDTV is being subjected to quality degradation and could become truncated to just DVD quality at its best.  Should we let that happen?</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 234";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting</title>
	<meta name="keywords" content="dish network, cable vision, dtv stations, broadcast flag, ces report, cable, mpeg, channels, voom, stbs, services, subscribers, dish, stations, million, local, directv, satellite, broadcast, dtv, service, network, usdtv, announced, new" />
	<meta name="description" content="DirecTV, Dish Network, and Cable are constantly pursuing an increase of their HDTV line up to gain market share.  The launching of more satellites and the &quot;upgrade&quot; to MPEG-4 AVC compression provide the satellite companies an opportunity for such gain; the sale of VOOM to Dish Network creates more forces on that competition; what an HDTV consumer is supposed to expect next with these compression plans when is known that true 1080i resolution is not currently being delivered?  OTA broadcasting is increasing their SD multi-casting efforts, and their HDTV is gradually being subjected to the pressure of offering more quantity rather than quality.  Although the HDTV offerings are on the increase and 1080p displays are starting to appear, it seems contradictory that the delivering of true HDTV is being subjected to quality degradation and could become truncated to just DVD quality at its best.  Should we let that happen?" />
	<meta name="title" content="2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-4-satellite-cable-broadcasting.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=234', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-4-satellite-cable-broadcasting.php">2005 HDTV Report, Part 4: Satellite, Cable, Broadcasting</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 14, 2005</b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p><br />
<h2>DirecTV</h2><br />
On May 2004, DirecTV named Thomson as principal supplier of their satellite STBs, for at least half of its needs.  The agreement was expected to close in 2Q04 provided it meets with regulatory conditions.  Under the five-year's supply agreement Thomson would acquire the STB manufacturing assets of Hughes Network Systems, a unit of DirecTV.  Thomson would manufacture DirecTV STBs and DVR receivers.</p>

<p>On Sep 2004, DirecTV announced their plan to launch fourth generation satellites to expand HD and interactive services.  The first two new Ka-band satellites, the Spaceway 1 and 2, will be launched in 2Q2005 and programming will be offered by the middle of the year including local HD to most of the US to a capacity of 500 channels, and expanding SD services.  The launching will enable the offering of local HD channels initially to 12 markets.</p>

<p>The next two Ka-band satellites DirecTV 10 and 11 will launch early in 2007 and will expand the capacity to over 1000 additional local HD channels and more than 150 national HD channels, among other offerings to consumers with a single small dish.</p>

<p>DirecTV also mentioned their plan to implement MPEG-4 AVC in late 2005, and possibly require a new dish capable to receive signals with 5 LNBs.  No confirmation was provided regarding how the upgrade path to existing customers will be carried out.</p>

<p>DirecTV provides satellite services to over 13 million customers, and is 34% owned by Fox Entertainment Group.<br />
 <br />
On Oct 2004, along the lines of its planned expansion, DirecTV told the FCC that any dual carriage requirement that would require delivery of both broadcast digital and analog signals would reduce the number of markets that it could provide local TV service, violate the Constitution, and create more burden on DBS than on cable services due to the limited capacity.</p>

<p>In January 2005 DirecTV further confirmed the satellites launching and HD plan above, and announced new interactive services with mix regular channels with enhanced features such as six channels simultaneous viewing, new DVR's and service, and the introduction of a Home Media Center later in 2005 (although not HD level).</p>

<p><br />
<h2>Dish Network</h2><br />
On November 2004, Echo Star Communications praised Congress on passing the Satellite Home Viewer Extension and Reauthorization Act of 2004, which would allow consumers to receive distant HDTV network channels if the local broadcasters do not comply in their timely delivery of their HDTV signal at full power to viewers or if those viewers can not receive OTA from the local affiliates.</p>

<p>On the other hand, the company also expressed disappointment when the bill imposed a 3-year wait period to provide distant network services to viewers; EchoStar declared that it was singled out regarding channel positioning, giving only 1.5 years to resolve the dual dish issue, which now affects the subscribers of 38 of 150 markets that receive the local stations of their area.  Dual dish setup is allowed if the local stations are grouped in one dish, or if one dish delivers all the analog channels and the other all the digital channels, but this is not the case of EchoStar.  EchoStar has about 10.4 million subscribers.</p>

<p>As part of the approved Act mentioned above, it was also approved that satellite operators would have up to five years the right to offer the four major networks (NY to LA) to subscribers that do not have those channels available via OTA service in their area, or to "sell" that service to those subscribers that want it, even without authorization from the local affiliates.</p>

<p>Regarding future services, Dish Network disclosed in November 2004 that within one year the company plans to start the transition from their current MPEG-2 compression to MPEG-4, which would allow for more channels (regular and HD).  When considering the large task for that upgrade the starting could be delayed for later in 2006.  The upgrade would require replacement of current MPEG-2 HD-STBs, incompatible with MPEG-4, the new STBs would handle MPEG-4 and decode MPEG-2 signals.  The transition could take 4 years to complete, starting with the existing HD subscribers, during that time there will be dual services of MPEG-2 and MPEG-4, and was anticipated that there will be no cost to customers with older boxes.</p>

<p>Dish Network commented at CES 2005 that they have not yet decided how the transition would be done but they anticipate that they might first offer new MPEG-4 capable HD-STBs to HD subscribers that want the newer MPEG-4 channels, then they might offer box replacement to the current HD group of subscribers that are only staying with the current HD channels, and maybe later with the SD customers, if the SD service they receive would also be switched to MPEG-4, an issue that is not yet decided.  No additional details were provided of how the transition will be performed or STBs exchanged, but it was already recognized that the upgrade to MPEG-4 could not be done by firmware, card replacement, or user replaceable parts.  None of the STBs currently installed support MPEG-4; not even the soon to be introduced (mid 2005) HD-STB DVR supports MPEG-4.</p>

<p>As a comparison, DirecTV is going thru a similar situation, and Voom current subscribers have STBs that can be upgradeable to MPEG-4 by inserting a new card on the side of the 550 STB, and later be firmware upgradeable with dish downloads.</p>

<p><br />
<h2>Voom</h2><br />
As of May 2004, Cable Vision Systems reported 8000 VOOM activated customers (in about six months of operation, launched in October 15, 2003), with quarterly net revenue of $1 million by March 31 2004, generated by the hardware sales.</p>

<p>A number of problems were reported with VOOM Motorola DSR-550 STB, such as DVI problems sending as 480i a 720p signal, no closed caption, no over-the-air channel scanning, no channel delete for mapped channels that are out of reception range, etc.  The company was gradually fixing those problems during 2004.</p>

<p>On November 2004, a report was issued by Fulcrum Global Partners showing that VOOM had reached 25,000 subscribers; the goal is to reach 200,000 to start making some profit, at which level each subscriber would be valued as $1,500.  The recommendation was to sell or shutter VOOM to stop the losses, a loss that is estimated as $130 million in just the past two quarters.  Cable Vision showed their support to VOOM, but also commented on plans to spin off VOOM with Rainbow Media programming assets, although by December Cable Vision decided to suspend the spin off.</p>

<p>On November 2004, Rainbow Media Enterprises announced a large expansion of services by March 2005.  The current 39 HD channels will increase to over 70, and add almost 200 SD channels.  To that end, VOOM will use 16 transponders on the SES Americom AMC-6 satellite (called Rainbow 2).  VOOM will implement Harmonic's MPEG-4 encoding on both satellites (Rainbow 1 already in operation and Rainbow 2) during 2005.</p>

<p>In the long term, VOOM announced their plans to launch 5 Ka-Band high power satellites to increase its channel capacity to over 5000 HD channels in spot beam and half-CONUS beam modes, which would make VOOM able to provide direct broadcast services across the nation.</p>

<p>Approximately 3 years would be needed for Lockheed Martin to manufacture and launch the first of those satellites (estimated life of 15 years), which will be positioned at 62, 71, 77, 119, and 129 degrees.  According to VOOM, all the subscribers already have on their STB the capability to decode MPEG-4, and the company is committed to supply over 400 channels by the end of 2005.</p>

<p>On January 2005, the company decided to spin off VOOM and either close or sell VOOM.  Later, Dish Network bought VOOM with Cablevision's Rainbow 1 satellite, the ground facilities, and certain other assets for $200 Million.</p>

<p>The acquisition of the Rainbow 1 satellite at 61.5 degrees includes the rights to 11 DBS frequencies of 13, 12 can be operated in "spot beam" mode.  How this would affect the expansion plans, MPEG-4 upgrades, the subscribers, and the awaited 580 DVR server/network remains to be seen.  Dish Network was going in the same upgrade direction, with the difference that VOOM STBs were already MPEG-4 upgradeable with the satellite card for MPEG-4 and future firmware upgrades.</p>

<p><br />
<h2>Cable</h2><br />
On September 2004, the National Cable & Telecommunications Association (NCTA) declared that there are now 177 markets (out of 210) where consumers can receive HD services, 100 of them are Designated Market Areas.  There are now 454 local digital broadcast stations carried by cable systems (from 304 in December 2003).  90 million TV households (out of the 108) can be served with HD packages, 28% increase over the 70 million of December 2003.</p>

<p>Almost 2 years ago, Samsung announced at CES 2003 that they were the first company to make an agreement with CableLabs for a two-way bi-directional Cable-CARD tuner implementation.  No products were released yet with such capability.  Most recently in November 2004 CableLabs announced, again, that Samsung was the first consumer electronics manufacturer to sign a license for bi-directional Cable Card product capability to implement Cable Lab's Open Cable Application Platform (OCAP) middleware on their DTVs and HD-STBs, which would enable them to have IPG and VOD premium services.</p>

<p>For over the last two years, QAM CableCARD tuners are only unidirectional, lacking the interactive ability for VOD, impulse PPV, and cable-provided EPG.</p>

<p>However, on January 2005, Samsung made an agreement with three MSOs that serve over 20 million subscribers, Time Warner Cable, Brighthouse Cable, and Charter Cable.  Under the terms, there will be an implementation of bi-directional OCAP software on Cable tuners, with a middle-ware specification designed with a universal interface.  OCAP is part of an industry agreement that is now two years old and was formally known as PHILA (POD Host Interface Licensing Agreement).  Later, with the arrival of the CableCARD concept, the name was changed to CHILA (the front 'C' from CableCARD).</p>

<p>The National Cable & Telecommunications Association declared on November 2004 their concern with the large troubleshooting effort that is being made to repair integrated plug-and-play HDTVs as follows: "Armies of cable engineering personnel, from field technicians to corporate engineers, have continued to spend time at consumer homes".  Reportedly, the problems arise when the card is installed on weak connector pins that are bent due to a soldering-temperature error.</p>

<p>The NAB protested to the FCC for the ban imposed on the deployment of HD-STBs if not having CableCARD after July 2006.  The ban is intended to reduce manufacturing costs with the higher demand and availability of a larger number of compliant STBs, when requiring that cable companies use CableCARDs in their own STBs.  In a recent report of December 2004, it was disclosed that approximately 10,000 CableCARDs were deployed in over 140 devices from 11 manufacturers, although those have only CableCARD unidirectional capabilities.</p>

<p>The FCC is "monitoring and encouraging" the negotiations and progress made on the bi-directional digital cable services, the talks were held in closed doors and no dateline has been issued yet, the group that participates on those negotiations now has grown to about 90 interested parties, including the MPAA, NAB, and computer companies for hardware and software, which makes the agreements more complicated and slower to be reached.</p>

<p><br />
<h2>Broadcasting</h2><br />
At NAB (April 04) Dolby Digital Plus (DD+) was announced for broadcasters to transmit 5.1 at 50% (192kBs) data rate of regular DD (384kBs).  There will be a need to address backward compatibility issues with consumer's existing equipment, one idea was to make available conversion devices from DD+ to DD to permit consumer equipment to read as DD.</p>

<p>Edward Fritts, president of the National Association of broadcasters (NAB) declared (Oct 2004) that OTA broadcasting needs to be protected for emergency information to reach consumers, as follows "There are 73 million television sets in use in America connected neither to cable nor satellite, 45 million of which are in homes that rely exclusively on local, 'over-the-air' stations as their sole source of television," Fritts added in his letter:  "These stations provide more than just entertainment; as hurricane-ravaged Florida residents can attest, they provide lifesaving information to communities in crisis".<br />
	<br />
By August 2004, the FCC reported that has already approved 13 different "digital output technologies and recording methods" to implement the Broadcast Flag order.  A complete detail of the subject of Broadcast Flag was provided in the CES 2004 report and in an article I wrote for the HDTVetc magazine (issue # 6) about content protection of H/DTV in general.  A summary of what is the Broadcast Flag and a description of the approved technologies is included on the section of Content Protection towards the end of this 2005 report.</p>

<p>The ATSC approved on November 10 a new ATSC standard A76 "Programming Metadata Communication Protocol" known as PMCP, to help broadcasters generate PSIP.  On December 2004, it was reported that the FCC has ordered all US terrestrial DTV stations to include PSIP in their broadcasts with a deadline of February 1, 2005.</p>

<p>According to the National Association of Broadcasters, by December 2004, 1356 DTV stations were on air in 211 markets serving 99.95 percent of US households.  About 90% of 106 million households are in markets where 5 or more DTV stations are available, and 71% with 8 or more stations.</p>

<p>As of January 2005, 1676 stations (97%) have been granted a DTV construction permit or license, 1481 stations are already in the air with DTV broadcasting.</p>

<p><br />
<h2>USDTV</h2><br />
On April 20, 2004 the USDTV subscription service for H/DTV multi-channel broadcast thru a VHF/UHF regular antenna was announced; consumers would purchase a USDTV-Ready STB (sold a Wal-Mart stores and some electronic chains) to tune to 12 popular cable channels in addition to OTA H/DTV channels.  Wegener Unity 4600 receivers will be located at USDTV broadcast stations, which receive the satellite USDTV channels and broadcast the content to terrestrial signal subscribers.</p>

<p>On July 2004, USDTV announced a partnership with LG to suit their USDTV STBs with 5th generation 8-VSB and ATSC tuners, which would be benefited by the ability of the chip to enhance OTA reception in difficult areas.  The USDTV service operates from Salt Lake City, Albuquerque, and Las Vegas, and costs less than $20 per month.  The service offers OTA SD and HD of popular networks, by using spectrum that some partners do not use.</p>

<p>On my visit to their booth at CES, I had the opportunity to discuss about their service and hardware.  The company indicated that in order to have enough bandwidth for their services they take any unused pieces of other channel's bandwidth to find room for what they need to broadcast.</p>

<p>USDTV offers several options for their services as follows: a) a $20 initiation fee and $ 20 monthly x 12 months, the subscriber would have own the box at the end of the period, b) $20 x month plus $200 purchased box, or c) $200 purchased box with no service, but with the box a customer would be able to tune to ATSC HD over the air programming, relatively cheap considering the cost of the other ATSC tuner HD STBs that usually carry a minimum $350 MSRP.  One thing that should be noted is the absence of IEEE1394 outputs on their HD-STB, meaning there is no D-VHS HD recording possible from what the box tunes, a feature that all of the other OTA HD-STBs have.  It was discussed the possibility of future features, like a DVR, among others plans, but no details were provided to be disclosed as press announcements.</p>

<p>Be sure that you read the next article in the series: <a href="http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_repor_3.php">Analysis of H/DTV Equipment</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 14, 2005 12:43 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(234)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 234)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-4-satellite-cable-broadcasting.php" type="text/javascript" charset="utf-8"></script>
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
