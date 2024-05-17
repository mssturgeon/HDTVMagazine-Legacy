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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3232 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3232
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-fall-2009-tv-schedule-podcast-389.php&amp;title=HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389">
		<span style="display:none">It's that time of year again.  The weather begins to cool, leaves start to change colors, Football is back and we get a bunch of new HDTV shows.  When we were kids we looked forward to Christmas and the first day of summer.  As adults, we look forward to the fall more than anything else.  Some of our favorite shows are coming back with new episodes and we get the opportunity to fill the DVR with brand new series to see what sticks.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=34&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3232";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389</title>
	<meta name="keywords" content="human target, los angeles, certain age, ncis los, returning shows, shows, new, show, cbs, men, life, fox, ncis, abc, series, nbc, picks, back, fall, —, guide, tnt, drama, family, certain" />
	<meta name="description" content="It's that time of year again.  The weather begins to cool, leaves start to change colors, Football is back and we get a bunch of new HDTV shows.  When we were kids we looked forward to Christmas and the first day of summer.  As adults, we look forward to the fall more than anything else.  Some of our favorite shows are coming back with new episodes and we get the opportunity to fill the DVR with brand new series to see what sticks.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=34&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-fall-2009-tv-schedule-podcast-389.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3232', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-fall-2009-tv-schedule-podcast-389.php">HDTV and Home Theater Podcast - Fall 2009 TV Schedule: Podcast #389</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September  4, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=381&category=Sports">Sports</a></b>
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
<h4><strong>Fall 2009 TV Schedule</strong></h4>
<p>It&#8217;s that time of year again.  The weather begins to cool, leaves start to change colors, Football is back and we get a bunch of new HDTV shows.  When we were kids we looked forward to Christmas and the first day of summer.  As adults, we look forward to the fall more than anything else.  Some of our favorite shows are coming back with new episodes and we get the opportunity to fill the DVR with brand new series to see what sticks.</p>
<p><a id="r_em" title="TV Guide" href="http://www.tvguide.com/" target="_blank">TV Guide</a> has a great graph of the <a id="qo.y" title="fall lineup" href="http://www.tvguide.com/special/fall-preview/fall-schedule.aspx" target="_blank">fall lineup</a> for the 5 major networks: ABC, NBC, CBS, FOX and the CW.  TNT has actually come on pretty strong in the last few seasons with shows like Saving Grace, The Closer, Raising the Bar, Leverage and now Dark Blue.  We&#8217;d like to see TV Guide expand their list to 6 next year.  Of course HBO always has compelling content as well.</p>
<p><a id="n16x" title="The TV Remote" href="http://www.thetvremote.com/" target="_blank">The TV Remote</a> has a nice listing of the <a id="s6qt" title="premier dates" href="http://www.thetvremote.com/2009-fall-premiere-schedule/" target="_blank">premier dates</a> for all the new and returning shows.  Check it out; there&#8217;s way too much for us to cover on there.  You&#8217;ll notice that some premiers are scheduled to start as early as next Tuesday, Sept. 8.  Most of them, however, kick off in a couple/few weeks.</p>
<p>A few new show summaries, most taken from TV Guide.com.  This is not an exhaustive list, but rather a few of the shows we thought were noteworthy.</p>
<p><strong>ABC<br />
</strong></p>
<ul>
<li><strong>Shark Tank</strong> &#8211; <em>Survivor</em> creator Mark Burnett offers this recession-ready reality show about everyday people pitching their best ideas to captains of industry. Who, it turns out, can be kind of mean.</li>
<li><strong>The Forgotten</strong> &#8211; This Jerry Bruckheimer-produced drama centers on a group of amateur detectives led by a former cop (Christian Slater) whose daughter has disappeared. They try to crack murder cases involving unidentified victims — the people everyone else has forgotten. Can it prove more memorable than Slater&#8217;s <em>My Own Worst Enemy</em>?</li>
<li><strong>Modern Family</strong> &#8211; Ed O&#8217;Neill (<em>Married&#8230; with Children</em>), Julie Bowen (<em>Ed</em>) and Jesse Tyler Ferguson (<em>The Class</em>) headline a top-notch cast delivering a fresh take on the multigenerational family comedy. Our favorite moment from the pilot is scored by the <em>Lion King</em> theme.</li>
<li><strong>FlashForward</strong> &#8211; The world&#8217;s population sees into the future when everyone blacks out for two minutes and 17 seconds at the same time. The clairvoyant episode staggers the minds of all, as in many instances their futures are not what they expected&#8212;and some, it seems, have no future at all. While many recount their experiences on a worldwide Web site, others seek to circumvent their fates; and some, like FBI agent Mark Benford, seek to learn what caused the mass blackout.</li>
</ul>
<p><strong>CBS<br />
</strong></p>
<ul>
<li><strong>Accidentally on Purpose</strong> &#8211; Jenna Elfman (Dharma &amp; Greg) is a film critic with blockbuster news — she&#8217;s pregnant from a one-night stand with a younger dude (Jon Foster, Life As We Know It). Will this waylay a budding romance with boss Grant Show? What happens next is all very <em>Knocked Up</em>.</li>
<li><strong>NCIS: Los Angeles </strong>- Chris O&#8217;Donnell and LL Cool J front this spin-off of the hit CBS procedural, playing Special Agents assigned to the high-tech Office of Special Projects. Academy Award winner Linda Hunt plays their &#8220;Q&#8221;/gadget master, while Rocky Carroll&#8217;s Leon Vance will appear on both <em>NCIS</em> shows.</li>
<li><strong>Three Rivers</strong> &#8211; Alex O&#8217;Loughlin (<em>Moonlight</em>), Katherine Moennig (<em>The L Word</em>) and Daniel Henney (<em>X-Men Origins: Wolverine</em>) populate a team of transplant doctors. Each transplant story is told from three perspectives — those of donor, recipient, and doctors.</li>
</ul>
<p><strong>CW<br />
</strong></p>
<ul>
<li><strong>Melrose Place</strong> &#8211; The CW&#8217;s remake of the soapy &#8217;90s melodrama features many familiar archetypes: the brooding bad boy, the nice couple, and the powerful bitch, to name three. The new show departs from its source material with a mystery storyline concerning a dead body that appears, <em>Sunset Boulevard</em>-style, in the apartment complex&#8217;s pool. Vets Laura Leighton and Thomas Calabro co-star.</li>
<li><strong>The Beautiful Life: TBL</strong> &#8211; Executive produced by Ashton Kutcher, this drama follows two young models (Sara Paxton and <em>High School Musical</em>&#8217;s Corbin Bleu) as they are swept up in the fashion business. There to guide them (or complicate matters) are the modeling agency&#8217;s boss (Elle MacPherson) and a model with a few years under her belt (<em>The O.C.</em>&#8217;s Mischa Barton).</li>
</ul>
<p><strong>FOX<br />
</strong></p>
<ul>
<li><strong>Brothers</strong> &#8211; Retired NFL star Michael Strahan plays&#8230; a retired NFL star who is summoned back home to visit his ailing dad (<em>Rocky</em>&#8217;s Carl Weathers). While there, he trades barbs with his paraplegic brother (Darryl &#8220;Chill&#8221; Mitchell) and gets duped by their mom (CCH Pounder).</li>
<li><strong>The Cleveland Show</strong> &#8211; This <em>Family Guy</em> spin-off ships Cleveland Brown off from Quahog to sunny California. Along the way, he makes a (permanent) pit stop in his hometown, fictional Stoolbend, Va., where he rekindles a romance with his high school girlfriend. Series creator Seth Macfarlane, Mike Henry, Sanaa Lathan and Kevin Michael Richardson provide voices.</li>
<li><strong>Past Life</strong> &#8211; <em>Fox will debut</em> <em> midseason &#8211; </em>Have you ever experienced déjà vu or met someone you thought seemed familiar? Do you believe in karma, fate or love at first sight? From writer David Hudgins (“Friday Night Lights”), and inspired by the book “The Reincarnationist” by M.J. Rose, comes PAST LIFE, a new drama series about an unlikely pair of past-life detectives who investigate whether what is happening to you today is the result of who you were before.</li>
<li><strong>Human Target</strong> <em>- will replace</em> Glee <em>midseason &#8211; </em>A full-throttle, action-packed thrill ride based on the popular DC Comics graphic novel and starring Mark Valley (Fringe), Chi McBride (Pushing Daisies) and Academy Award nominee Jackie Earle Haley (Watchmen), the series follows Christopher Chance (Valley), a unique private contractor who will stop at nothing even if it means becoming a human target to keep his clients alive.</li>
</ul>
<p><strong>NBC<br />
</strong></p>
<ul>
<li><strong>Trauma</strong> &#8211; Anastasia Griffith (<em>Damages</em>) and Derek Luke (<em>Antwone Fisher</em>) are among a team of EMTs who must confront the astoundingly traumatic moments of a trauma and then quickly treat the injured, often just minutes after tragedy strikes. Much stuff blows up.</li>
<li><strong>Mercy</strong> &#8211; Doctor shows are old hat. This year is about nurses. (See also <em>HawthoRNe</em> and <em>Nurse Jackie</em>.) Taylor Schilling and Jaime Lee Kirchner play the nurses who actually run their hospital, and Michelle Trachtenberg is the new kid who learns the harsh realities of medicine.</li>
<li><strong>Jay Leno Show</strong> &#8211; A comedic entertainment show led by the former &#8216;Tonight Show&#8217; host and featuring topical humor, celebrity guests and correspondents<strong>.</strong></li>
<li><strong>Community</strong> &#8211; <em>The Soup</em>&#8217;s Joel McHale stars as an ethically challenged attorney who is forced to go back to college — community college. There, he meets a ragtag bunch of misfit toys, including an understated Chevy Chase, Gillian Jacobs and <em>Mad Men</em>&#8217;s Alison Brie (&#8221;Hell&#8217;s bells, Trudy!&#8221;) who all yearn for some higher learning, despite their obvious deficiencies out in the real world.</li>
</ul>
<p><strong>TNT<br />
</strong></p>
<ul>
<li><strong>Men of a Certain Age</strong> &#8211; Starring Emmy Award winners Ray Romano (Everybody Loves Raymond) and Andre Braugher (Homicide: Life on the Street) and Golden Globe winner Scott Bakula (Quantum Leap), Men of a Certain Age is a new original series that takes a wry look at the bond between three men in their 40s who are lifelong best friends.</li>
</ul>
<p><strong>Braden&#8217;s Picks, New Shows</strong></p>
<ul>
<li>NCIS: Los Angeles (CBS)</li>
<li>Human Target (FOX)</li>
<li>Community (NBC)</li>
<li>Men of a Certain Age (TNT)</li>
<li>Possible: Trauma, Mercy, Three Rivers</li>
</ul>
<p><strong>Ara&#8217;s Picks, New Shows</strong></p>
<ul>
<li>NCIS: Los Angeles (CBS)</li>
<li>FlashForward (ABC), Shark Tank (Kind of) Too bad its still in SD. Shame on ABC!</li>
</ul>
<p><strong>Braden&#8217;s Picks, Returning Shows</strong></p>
<ul>
<li>ABC: Castle</li>
<li>CBS: NCIS, The Mentalist, Numbers</li>
<li>FOX: 24</li>
<li>NBC: Chuck, The Office, Southland</li>
<li>TNT: Dark Blue, Raising the Bar, HawthoRNe*</li>
</ul>
<p><strong>Ara&#8217;s Picks, Returning Shows</strong></p>
<ul>
<li>ABC: Lost</li>
<li>CBS: How I Met Your Mother, Two and a Half Men, NCIS, Survivor</li>
<li>FOX: 24, House, Bones, Fringe, Hells Kitchen, American Idol</li>
<li>NBC: Chuck, 30 Rock</li>
</ul>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-09-04.mp3">Download Episode #389</a></p>
  <a rel="nofollow" href="http://feeds.wordpress.com/1.0/gocomments/htguys.wordpress.com/34/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/comments/htguys.wordpress.com/34/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/godelicious/htguys.wordpress.com/34/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/delicious/htguys.wordpress.com/34/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/gostumble/htguys.wordpress.com/34/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/stumble/htguys.wordpress.com/34/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/godigg/htguys.wordpress.com/34/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/digg/htguys.wordpress.com/34/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/goreddit/htguys.wordpress.com/34/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/reddit/htguys.wordpress.com/34/" /></a> <img alt="" border="0" src="http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&blog=8935650&post=34&subd=htguys&ref=&feed=1" /></div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September  4, 2009 12:04 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3232)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3232)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-fall-2009-tv-schedule-podcast-389.php" type="text/javascript" charset="utf-8"></script>
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
