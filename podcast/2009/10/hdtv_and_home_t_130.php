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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3299 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3299
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php&amp;title=HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote">
		<span style="display:none">Not content with what the HT Guys have declared the best universal remote on the market, Logitech continues to put out new Harmony Universal Remotes.  Of the two most recent models on the market, the Harmony 700 and the Harmony 900, we've had a chance to look at the 700.  It is the less expensive of the two, and can be found in retail stores and online for an MSRP of $150 US.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=51&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;</span></a>
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

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3299";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote</title>
	<meta name="keywords" content="lcd hdtv, home theater, panasonic viera, hdtv panasonic, harmony universal, harmony, hdtv, panasonic, inch, remote, get, lcd, tvs, buttons, theater, top, series, samsung, home, remotes, viera, our, universal, everything, hard" />
	<meta name="description" content="Not content with what the HT Guys have declared the best universal remote on the market, Logitech continues to put out new Harmony Universal Remotes.  Of the two most recent models on the market, the Harmony 700 and the Harmony 900, we've had a chance to look at the 700.  It is the less expensive of the two, and can be found in retail stores and online for an MSRP of $150 US.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=51&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3299', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php">HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October  1, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=314&category=General Interest">General Interest</a></b>
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
				<div class='snap_preview'><br /><h2>Today&#8217;s Show:</h2>
<h3>Smaller HDTVs Selling like Hotcakes</h3>
<p>We came across an article at twice.com called <a id="e6td" title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.twice.com/article/355641-Ratio_Of_Small_TVs_To_Large_Shifts_To_3_2.php" target="_blank">Ratio Of Small TVs To Large Shifts To 3:2</a>.  The article points to research from Retrevo Pulse that found smaller TVs, those up to 37 inches, are selling at a 3:2 ratio compared with larger sets in the 37 to 50 inch size range.  This is up from a 1:1 ratio one year ago.</p>
<p>The research analyst cited three potential reasons for this change:</p>
<ul>
<li>The completion of the digital TV transition on June 12 was a motivating factor in a new TV purchases by a wider population segment.</li>
</ul>
<ul>
<li>More households are now adding multiple HDTV sets for various rooms in the house.</li>
</ul>
<ul>
<li>More HDTV programming through terrestrial broadcasts, cable, satellite and Internet TV is now available, stoking consumer demand for sets on which to view it.</li>
</ul>
<p>In other words (or our words):</p>
<ul>
<li>People buying TVs now will only buy digital because it&#8217;s just plain silly to buy analog and it&#8217;s tough to find a digital TV that isn&#8217;t high definition anymore.  Most TV purchases are smaller TVs, so it stands to reason that the number of smaller HDTVs sold would increase.</li>
<li>People already own their big Family Room/Home Theater HDTV.  That was the first one they bought.  Now they&#8217;re adding TVs for bedrooms, bonus rooms, offices, kitchens, etc.</li>
<li>Perhaps consumers are finally getting over the &#8220;there&#8217;s nothing on in HDTV&#8221; or &#8220;everything I like it still only available in standard definition&#8221; hurdle.  Could it be true?  We certainly hope so.</li>
</ul>
<p>So on the surface, the trend makes perfect sense, but we decided to put it to the test.  As with everything else consumer and buying related, we turn to <a id="h_jx" title="Amazon.com" href="http://www.amazon.com/?%5Fencoding=UTF8&amp;tag=hdtvandhometh-20" target="_blank">Amazon.com</a> to be our barometer.</p>
<p><strong>Top 10 TVs at Amazon.com:</strong></p>
<ol>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001UAB40E" target="_blank">Panasonic VIERA G10 Series TC-P46G10 46-Inch 1080p Plasma HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001T9N0EO" target="_blank">Sony BRAVIA V-Series KDL-46V5100 46-Inch 1080p 120Hz LCD HDTV, Black</a> by Sony</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001VKY7WU" target="_blank">Samsung LN52B750 52-Inch 1080p 240Hz LCD HDTV with Charcoal Grey Touch of Color</a> by Samsung</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001U3Y8LS" target="_blank">Samsung LN26B360 26-Inch 720p LCD HDTV</a> by Samsung</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001UE6M9S" target="_blank">Panasonic VIERA C12 Series TC-L32C12 32-Inch 720p LCD HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001U3Y8LI" target="_blank">Samsung LN22B360 22-Inch 720p LCD HDTV</a> by Samsung</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001V5J7OI" target="_blank">LG 32LH30 32-Inch 1080p LCD HDTV, Gloss Black</a> by LG</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001U3YIM2" target="_blank">Panasonic VIERA S1 Series TC-L37S1 37-Inch 1080p LCD HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001SE4YQS" target="_blank">Panasonic VIERA X1 Series TC-P42X1 42-Inch 720p Plasma HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001UE6MA2" target="_blank">Panasonic VIERA X1 Series TC-L26X1 26-Inch 720p LCD HDTV</a> by Panasonic</li>
</ol>
<p><strong>Observations:</strong></p>
<ul>
<li>Amazon&#8217;s top 10 bestselling TVs represent the exact 3:2 found in the research, but the top 3 TVs in the list all fall in the large size range.</li>
<li>There are 8 LCDs on the list and 2 plasmas</li>
<li>Samsung used to dominate the top 10, now Panasonic is the big player with 5 of the top 10 including the #1 set.</li>
<li>The first set 60 inches or larger in the full top 100 list appears at #57.  it is a plasma: <a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B002IK8H0A" target="_blank">Panasonic VIERA S1 Series TC-P65S1 65-Inch 1080p Plasma HDTV, Black</a> by Panasonic</li>
<li>There are no rear projection sets in the top 100</li>
</ul>
<h3>Harmony 700 Universal Remote</h3>
<p>Not content with what the HT Guys have declared the best universal remote on the market, <a href="http://www.logitech.com/" target="_blank">Logitech</a> continues to put out new Harmony Universal Remotes.  Of the two most recent models on the market, the <a id="et85" title="Harmony 700" href="http://www.logitech.com/index.cfm/remotes/universal_remotes/devices/6063&amp;cl=us,en" target="_blank">Harmony 700</a> and the <a id="b.vz" title="Harmony 900" href="http://www.logitech.com/index.cfm/remotes/universal_remotes/devices/5874&amp;cl=us,en" target="_blank">Harmony 900</a>, we&#8217;ve had a chance to look at the 700.  It is the less expensive of the two, and can be found in retail stores and online for an MSRP of $150 US (<a title="Buy Now" href="http://www.htguys.com/shop?id=B002IC0YLS" target="_blank">Buy Now</a>).</p>
<p><strong>Setup</strong></p>
<p>The manual tells you to reserve 45 minutes to get the remote set up.  If you&#8217;re a first-timer, that might be about right.  You need to make sure you know the model numbers for all your home theater equipment and also set aside a few minutes to get familiar with the programming software.</p>
<p>For us, and likely for those who&#8217;ve owned a Harmony before, it&#8217;s much closer to 15 than 45 minutes.  As is customary, we started from scratch, but there&#8217;s really no difference in how most of the Harmony remotes are programmed.  There are only subtle differences on what buttons it has, how many soft buttons are available, etc.  It took about 15 minutes to program 700 even when you consider the time it took to include all our customizations.</p>
<p>In addition to the remote, the box includes a USB cable for programming which also doubles as a recharging cord when plugged into the included wall adapter.  So yes, this Harmony is also a rechargeable model, but it&#8217;s slightly different.  For this one Logitech chose to include 2 NiMH rechargeable AA&#8217;s.  We found the charging cord to be a bit short, but since you can get a week&#8217;s worth of use out of one charge, there&#8217;s no need to keep it constantly plugged in.</p>
<p>It would seem that one benefit of the AA form factor is that if the batteries die, you can simply swap them out for some standard AA batteries you have lying around until you can get a pair of new rechargeables.  We didn&#8217;t test this theory, though, since the manual warns of a risk of explosion should you replace the batteries with an incorrect type.  Sounds like an episode of MacGyver in the making.</p>
<p><strong>Design</strong></p>
<p>The 700 is a replacement for the trusty 880 many of us had grown to love.  But admit it, it needed a face lift and the 700 provides just that.  It will drop you to four soft buttons from eight on the 880, but it provides more hard buttons that are laid out much better and a much easier to get to.  It also adds three hard buttons for the most common activities, Watch TV, Watch a Movie and Listen to Music.  Of course you can always override those to do whatever you want.</p>
<p><strong>Use</strong></p>
<p>The Harmony line of remotes is award winning and a lock for the Home Theater no-brainer award, in our opinion.  The 700 continues in that tradition.  Setup is as simple as you can get.  Then you get one click to turn everything on and setup right to do whatever you want in your home theater.  Every button on the remote does exactly what you&#8217;d expect it to without having to switch between devices, then one click to turn everything off.</p>
<p>All the buttons light up, so if you&#8217;re watching in the dark, a simple shake of the remote lets you see everything perfectly.</p>
<p><strong>Other stuff</strong></p>
<p>So the 700 falls right in the middle of the Harmony lineup.  Entry level is the 510 for $100 MSRP.  You then step up to the 700 for rechargeable batteries, a color screen and a slightly more elegant aesthetic.  From there you can move up to the Harmony One for $250.  It gives you a color touchscreen with more soft buttons and a charging cradle.  And then up to the cream of the crop in hard button remotes, the 900 for $400.  It looks just like the One, but includes built-in RF.</p>
<p>It&#8217;s worth noting that we use Harmony remotes almost exclusively in our homes and consider the Harmony One to be the de-facto standard in how a home theater remote should be built.  Harmony also offers a complete touch screen model, the ultra-sexy 1100 for $400 MSRP.  But we tend to prefer hard button remotes for their ease of use and simplicity.</p>
<p>In our food analogy from <a id="qun-" title="Episode #372" href="http://www.htguys.com/podcasts/2009/5/7/harmony-remote-round-up-podcast-372.html" target="_blank">Episode #372</a>, the 880 came in as a nice steak dinner.  Enough to get dressed up, but a great deal at the same time.  You&#8217;ll brag about what you got, and how little you paid for it.  The 700 fits right there as well, only this time you&#8217;re going out to the newest steakhouse in town.</p>
<p><strong>Conclusion</strong></p>
<p>As with any Harmony remote, the 700 is an excellent choice to control your home theater.  It&#8217;s new, sexy and incredibly easy to use.  While overall it doesn&#8217;t represent a huge departure from the 880, it does offer some nice usability upgrades.  For the coolness factor, the One with its touchscreen is still where it&#8217;s at, but for those who want the best of both worlds, a great, easy to use remote that costs a little less, the 700 is ideal.  If you haven&#8217;t tried a Harmony universal remote yet, you owe it to yourself.</p>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-10-02.mp3">Download Episode #393</a></p>
  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October  1, 2009 11:29 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3299)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3299)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php" type="text/javascript" charset="utf-8"></script>
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
