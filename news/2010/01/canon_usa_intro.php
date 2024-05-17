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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3464 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3464
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php&amp;title=Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User">
		<span style="display:none">Canon U.S.A., Inc, a leader in digital imaging technology, today announced an exciting new line of nine VIXIA High Definition flash memory camcorders. The 2010 high-definition lineup includes Canon’s flagship VIXIA HF S-series, the compact VIXIA HF M-series and a new entry-level VIXIA HF R-series. Some new features enhancing Canon’s 2010 lineup include...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3464";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" />
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
	<title>HDTV Magazine - Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User</title>
	<meta name="keywords" content="flash memory, memory camcorders, estimated retail, new vixia, retail price, vixia, video, canon, new, memory, camcorders, flash, series, canon’s, recording, image, advanced, touch, definition, available, feature, retail, respectively, full, estimated" />
	<meta name="description" content="Canon U.S.A., Inc, a leader in digital imaging technology, today announced an exciting new line of nine VIXIA High Definition flash memory camcorders. The 2010 high-definition lineup includes Canon’s flagship VIXIA HF S-series, the compact VIXIA HF M-series and a new entry-level VIXIA HF R-series. Some new features enhancing Canon’s 2010 lineup include..." />
	<meta name="title" content="Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3464', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php">Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=343&category=HD Camcorders & Cameras">HD Camcorders & Cameras</a></b>
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
				<p class="prtitle">Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User</p>

<center><i>The New VIXIA Line Offers Advanced Touch Screen and Tracking Technologies Putting Ease-of-Use at Your Fingertips</center></i><br />
<br />

<p><strong>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)--</strong>Canon U.S.A., Inc, a leader in digital imaging technology, today announced an exciting new line of nine VIXIA High Definition flash memory camcorders. The 2010 high-definition lineup includes Canon’s flagship VIXIA HF S-series, the compact VIXIA HF M-series and a new entry-level VIXIA HF R-series. Some new features enhancing Canon’s 2010 lineup include a new Touch Panel LCD with an advanced tracking feature helping keep any subject - such as people, pets, or cars - in focus and properly exposed, even in a busy scene. Canon’s new VIXIA lineup also includes an enhancement to its image stabilization system and an all-new HD-to-SD Downconversion feature allowing video to be easily uploaded to the web or burned onto DVDs. Select 2010 VIXIA camcorders are compatible with Eye-fi SD Memory Cards, allowing for wireless uploading of video content to a computer or favorite video sharing site via the Eye-fi card’s wireless capabilities.</p>

<p>“Canon’s new 2010 VIXIA Flash Memory camcorders deliver superior high-definition image quality in a compact, lightweight design and offer a host of new features to make capturing and sharing video easier than ever before," said Yuichi Ishizuka, senior vice president and general manager, Consumer Imaging Group, Canon U.S.A.</p>

<p>All of the new 2010 VIXIA High Definition camcorders retain Canon’s proprietary imaging technologies – a Genuine Canon HD Video Lens, HD CMOS Image Sensor and DIGIC DV III Image Processor. The Canon Full HD CMOS Image Sensor and DIGIC DV III Image Processor have been further improved to reduce noise under low-light conditions and enhanced to deliver more faithful reproduction of purple and blue tones – for both video and photos. All of these proprietary technologies combine together to produce Full HD video that is stunningly lifelike with astonishing detail and clarity.</p>

<p><br />
<strong>New Advanced Features:</strong><br />
<ul><li><strong>Smart Auto</strong>: The Smart Auto mode makes shooting great video even easier by utilizing Canon’s DIGIC DV III Image Processor to intelligently detect and analyze brightness, color, distance and movement and automatically select the best setting for the scene being recorded.</li><li><strong>Touch &amp; Track</strong>: Canon’s new Touch &amp; Track technology enables users to select a subject on the Touch Panel LCD that the camcorder will then recognize and track. This sophisticated technology recognizes faces, objects, even animals, ensuring that your subject will always be in focus and properly exposed.</li><li><strong>Relay Recording</strong>: Relay Recording allows users to capture uninterrupted video when the primary recording media is full. The camcorder will continue to record a scene by switching from one memory source to the other as it fills up, so that you won’t miss a moment of action.</li><li><strong>Powered IS</strong>: In addition to Canon’s Dynamic SuperRange Optical Image Stabilization, Powered IS provides an even higher level of compensation for subtle hand movement at the telephoto end of the zoom range. This new enhancement can be engaged by pressing the Powered IS button on the LCD panel.</li><li><strong>HD-to-SD Downconversion</strong>: A new HD-to-SD Downconversion feature enables users to convert recorded high-definition video to standard-definition files while preserving the original HD video. These standard-definition files make it even more convenient to share video online or create a DVD.</li><li><strong>Advanced Video Snapshot</strong>: Advanced Video Snapshot mode has been upgraded to provide the flexibility of capturing 2, 4, or 8 second video clips while recording or during playback.</li></ul></p>

<p><br />
<strong>VIXIA HF S-series:</strong></p>

<p>The Canon VIXIA HF S21*/**, VIXIA HF S20*/** and VIXIA HF S200*/** Flash Memory camcorders are Canon’s premiere camcorders with professional and easy-to-use features to allow anyone to capture outstanding HD video quality. The VIXIA HF S-series comes equipped with varying levels of internal flash memory and all feature two SD card slots for maximum storage capacity and easy video transfer. The VIXIA HF S21 and VIXIA HF S20 camcorders incorporate 64GB and 32GB of internal flash memory, respectively, and the VIXIA HF S200 records video directly to removable SD memory cards. Recording Full 1920 x 1080 HD video, these camcorders feature a Genuine Canon 10x HD Video Lens and a Canon 1/2.6-inch, 8.59-megapixel Full HD CMOS Image Sensor for stunning video and outstanding photos up to 8.0 megapixels. All three models in the VIXIA HF S-series include Canon’s new 3.5-inch High Resolution (922,000-dot) Touch Panel LCD screen for a large, bright display and easy menu navigation, including Touch &amp; Track technology. All of the models in this series also feature Canon’s Smart Auto, Relay Recording, Powered IS, HD-to-SD Downconversion, and Advanced Video Snapshot.</p>

<p>In addition, the VIXIA HF S-Series includes a host of professional features such as a built-in LANC terminal, and Native 24p (AVCHD) recording. For shooting outside on a sunny day, the VIXIA HF S21 includes a viewfinder which offers a reliable viewing environment when shooting in bright outdoor conditions. The VIXIA HF S21, VIXIA HF S20 and VIXIA HF S200 Flash Memory camcorders are scheduled to be available in April, and will have an estimated retail price of $1399.99, $1099.99 and $999.99 respectively.</p>

<p><br />
<strong>VIXIA HF M-series:</strong></p>

<p>The Canon VIXIA HF M31*/**, VIXIA HF M30*/** and VIXIA HF M300*/** Flash Memory camcorders offer consumers stunning HD video in an ultra-sleek, compact and lightweight body. The VIXIA HF M31 and VIXIA HF M30 incorporate 32GB and 8GB of internal flash memory, respectively, and the VIXIA HF M300 records video directly to an SD memory card. Recording Full 1920 x 1080 HD video, these camcorders include a Genuine Canon 15x HD Video Lens, a 2.7-inch Touch Panel LCD with Touch &amp; Track technology, Smart Auto, Powered IS and Advanced Video Snapshot. In addition, the VIXIA HF M31 and VIXIA HF M30 models both include Canon’s Relay Recording and HD-to-SD Downconversion. The VIXIA HF M31, VIXIA HF M30 and VIXIA HF M300 Flash Memory camcorders are scheduled to be available in April for an estimated retail price of $799.99, $699.99 and $679.99 respectively.</p>

<p><br />
<strong>VIXIA HF R-series</strong></p>

<p>The Canon VIXIA HF R11*/**, VIXIA HF R10*/** and VIXIA HF R100*/** Flash Memory camcorders are perfect for the budget-conscious consumer who wants Full 1920 x 1080 HD video. The VIXIA HF R11 and VIXIA HF R10 models incorporate 32GB and 8GB of internal flash memory, respectively, and the VIXIA HF R10 records directly to an SD memory card. All three models also include a Genuine Canon 20x HD Video Lens, Dynamic IS, Smart Auto and Advanced Video Snapshot. Both the VIXIA HF R11 and VIXIA HF R10 feature Canon’s Relay Recording and HD-to-SD Downconversion. Additionally the VIXIA HF R10 will be available in three stylish colors, black, red, and silver. The VIXIA HF R31, VIXIA HF R30 and VIXIA HF R300 Flash Memory camcorders will be available in March for an estimated retail price of $699.99, $549.99 and $499.99 respectively.</p>

<p><br />
<strong>FS series:</strong></p>

<p>In addition to the new VIXIA High Definition lineup, Canon is also introducing two standard-definition camcorders, the FS31*/**, and FS300*/** Flash Memory camcorders. The Canon FS31 model records to 16GB of internal flash memory, while the FS300 records video directly to an SD memory card. Wrapped in a small and attractive package, the FS-series offers 41x Advanced Zoom to help capture great video even at extreme telephoto distances, as well as Dynamic IS. In addition, the Canon FS300 will be available in three fashionable colors, silver, red, and blue. The Canon FS31 and FS300 Flash Memory camcorders are available in March for an estimated retail price of $349.99 and $299.99 respectively.</p>

<p><br />
<strong>New Optional Camcorder Accessories</strong></p>

<p>The new Canon WP-V2 Waterproof Case allows you to capture exciting HD footage underwater, up to depths of 130 feet, with any of the VIXIA HF M-series Flash Memory camcorders. The ultimate camcorder accessory for underwater enthusiasts, this compact and lightweight housing seals the camcorder, allowing easy on-camera operation and control. The Canon WP-V2 Waterproof Case will be available in April for an estimated retail price of $599.</p>

<p>Also new from Canon is the SM-V1 5.1-Channel Surround Microphone for the ultimate home theater experience. This new microphone is compatible with the VIXIA HF S-series and VIXIA HF M-series, allowing you to capture lifelike sound from all directions. The Canon SM-V1 5.1-Channel Surround Microphone will be available in April for an estimated retail price of $250.</p>

<p><br />
<strong>About Canon U.S.A., Inc.</strong></p>

<p>Canon U.S.A., Inc., is a leading provider of consumer, business-to-business, and industrial digital imaging solutions. Its parent company, Canon Inc. (NYSE:CAJ), a top patent holder of technology, ranked third overall in the U.S. in 2008†, with global revenues of US $45 billion, is listed as number four in the computer industry on Fortune Magazine's World’s Most Admired Companies 2009 list, and is on the 2009 BusinessWeek list of "100 Best Global Brands." Canon U.S.A. is committed to the highest levels of customer satisfaction and loyalty, providing 100 percent U.S.-based consumer service and support for all of the products it distributes. At Canon, we care because caring is essential to living together in harmony. Founded upon a corporate philosophy of Kyosei – "all people, regardless of race, religion or culture, harmoniously living and working together into the future" – Canon U.S.A. supports a number of social, youth, educational and other programs, including environmental and recycling initiatives. Additional information about these programs can be found at <a target="_blank" href="http://www.usa.canon.com/kyosei/">www.usa.canon.com/kyosei</a>. To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting <a target="_blank" href="http://www.usa.canon.com/rss/">www.usa.canon.com/rss</a>.</p>

<p>†Based on weekly patent counts issued by United States Patent and Trademark Office.</p>

<p>* This device has not been authorized as required by the rules of the Federal Communications Commission. This device is not, and may not be offered for sale or lease, or sold or leased, until authorization is obtained.</p>

<p>** A Product Report required by 21 C.F.R. § 1002.10 has not been submitted to the United States Food and Drug Administration for this product. This product is not, and may not be, offered for sale or lease, or sold or leased, until the required report has been submitted.</p>

<p>Prices, specifications and availability are subject to change without notice. Prices are estimated retail prices. Actual selling prices are set by dealers and may vary.</p>

<p>All referenced product names, and other marks, are trademarks of their respective owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2010 10:18 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3464)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3464)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php" type="text/javascript" charset="utf-8"></script>
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
