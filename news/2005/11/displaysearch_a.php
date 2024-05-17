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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 262 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 262
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=DisplaySearch Announces Q3\'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2005/11/displaysearch-announces-q305-oled-yy-growth-shipments-up-144-revenues-up-49.php&amp;title=DisplaySearch Announces Q3'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%">
		<span style="display:none">AUSTIN, TEXAS, November 30, 2005—DisplaySearch, an NPD Group Company and the worldwide leader in display market research and consulting, indicated in its latest Quarterly OLED Shipment and Forecast Report that OLED shipments grew to 16.7M and revenues hit $130.9M in Q3'05. Organic Light Emitting Diodes (OLED) displays compete with LCDs in small/medium applications such as mobile phone main displays and subdisplays, MP3s and automotive consoles. OLED shipments increased 144% Y/Y due to 35% growth in MP3 player sales, and 93% growth in industrial displays. All of the growth is in PMOLEDs using small molecule material; AMOLED producers—Sanyo Kodak, AUO and Sony—shipped less than 50K displays in Q3’05. P-OLED material panel producers continued to struggle with less than a 3% share of total shipments.

</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 262";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DisplaySearch Announces Q3\'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DisplaySearch Announces Q3\'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%" />
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
	<title>HDTV Magazine - DisplaySearch Announces Q3'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%</title>
	<meta name="keywords" content="text decoration, npd group, font size, arial helvetica, geneva sans, displaysearch, oled, font, shipments, growth, color, npd, revenue, displays, text, decoration, top, group, information, verdana, arial, size, geneva, forecast, sans" />
	<meta name="description" content="AUSTIN, TEXAS, November 30, 2005—DisplaySearch, an NPD Group Company and the worldwide leader in display market research and consulting, indicated in its latest Quarterly OLED Shipment and Forecast Report that OLED shipments grew to 16.7M and revenues hit $130.9M in Q3'05. Organic Light Emitting Diodes (OLED) displays compete with LCDs in small/medium applications such as mobile phone main displays and subdisplays, MP3s and automotive consoles. OLED shipments increased 144% Y/Y due to 35% growth in MP3 player sales, and 93% growth in industrial displays. All of the growth is in PMOLEDs using small molecule material; AMOLED producers—Sanyo Kodak, AUO and Sony—shipped less than 50K displays in Q3’05. P-OLED material panel producers continued to struggle with less than a 3% share of total shipments.

" />
	<meta name="title" content="DisplaySearch Announces Q3'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2005/11/displaysearch-announces-q305-oled-yy-growth-shipments-up-144-revenues-up-49.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=262', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/11/displaysearch-announces-q305-oled-yy-growth-shipments-up-144-revenues-up-49.php">DisplaySearch Announces Q3'05 OLED Y/Y Growth: Shipments Up 144%, Revenues Up 49%</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>November 30, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p><HTML><HEAD><TITLE>DisplaySearch: Press Release</TITLE><br />
<!link rel="stylesheet" href="/displaysearch/site/scripts/fonts.css" type="text/css"><br />
<style type="text/css"><br />
	BODY			{ margin: 0px 0px; font: 12px Arial, Helvetica, Verdana, </p>

<p>Geneva, sans-serif; }<br />
	TABLE, TD		{ font: 10px Verdana, Arial, Helvetica, Geneva, sans-serif; </p>

<p>word-spacing: 1px; color: #606060;}	/*line-height: 25px; letter-spacing: 5px;*/<br />
	.title_head		{ font-weight: bold; font-size: 18px; text-indent: 38px; }<br />
	.title 			{ font-weight: bold; font-size: 12px;  }	/* color: </p>

<p>#720517; */<br />
	a:link			{ color:006FA2; text-decoration:none; }<br />
	a:visited		{ color:006FA2; text-decoration:none; }<br />
	a:hover			{ color:006FA2; text-decoration:underline; /* font-weight : </p>

<p>bold; #720517*/ }<br />
	a.news			{ font-family: Verdana, Arial, Helvetica, Geneva, </p>

<p>sans-serif; font-size:10px; color:606060; text-decoration:none; }/*, 006699*/<br />
	a.news:hover	{ font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; </p>

<p>font-size:10px; color:006FA2; text-decoration:underline;}<br />
.style1 {color: #FFFFFF}<br />
</style></p>

<p></HEAD><br />
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LEFTMARGIN="0" RIGHTMARGIN="0" TOPMARGIN="0" </p>

<p>MARGINWIDTH="0" MARGINHEIGHT="0"></p>

<center>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td colspan="3" BGCOLOR="#333333">&nbsp;<br><br></td></tr>
<tr><td BGCOLOR="#333333" width="50%">&nbsp;</td>
	<td><table border="0" cellpadding="0" cellspacing="0" width="718">
		<tr><td 
background="http://www.displaysearch.com/press/pr_email/v4/press-head-1.gif"
width="582" class=title_head>	
				Press Release
				&nbsp;
			</td>
			<td width="136"><a href="http://www.displaysearch.com/"><img 
	src="http://www.displaysearch.com/press/pr_email/v4/press-head-logoNPD.gif" 
			alt="DisplaySearch" width="136" height="101" border="0"></a></td>
		</tr>
		</table>
	</td>
	<td BGCOLOR="#333333" width="50%">&nbsp;</td>
	</tr>
<tr><td BGCOLOR="#333333">&nbsp;</td>
	<td bgcolor="#FFFFFF">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr><td width="100" bgcolor="#f9c252">&nbsp;</td>
			<td width="10">&nbsp;</td>
			<td><br>
<!-- ---- Begin Content ---- -->			

<p>Wednesday, November 30, 2005<br />
<p><span class=title>DisplaySearch Announces Q3'05 OLED Y/Y Growth: Shipments Up 144%, </p>

<p>Revenues Up 49% </span></p>

<p>AUSTIN, TEXAS, November 30, 2005-DisplaySearch, an NPD Group Company and the worldwide 

<p>leader in display market research and consulting, indicated in its latest <B><I><a </p>

<p>href="http://www.displaysearch.com/products/?pn=qoled">Quarterly OLED Shipment and Forecast </p>

<p>Report</B></I></a> that OLED shipments grew to 16.7M and revenues hit $130.9M in Q3'05. </p>

<p>Organic Light Emitting Diodes (OLED) displays compete with LCDs in small/medium applications </p>

<p>such as mobile phone main displays and subdisplays, MP3s and automotive consoles. OLED </p>

<p>shipments increased 144% Y/Y due to 35% growth in MP3 player sales, and 93% growth in </p>

<p>industrial displays.  All of the growth is in PMOLEDs using small molecule material;  AMOLED </p>

<p>producers-Sanyo Kodak, AUO and Sony-shipped less than 50K displays in Q3'05. P-OLED material </p>

<p>panel producers continued to struggle with less than a 3% share of total shipments.</p></p>

<p>Samsung SDI led in units and revenue in Q3'05 with slightly over 4.4M displays and $37.1M 

<p>in revenue. RiTdisplay was #2 in both shipments and revenue. TDK experienced the strongest </p>

<p>growth of the top five, with shipments up 138% Q/Q to 1.2M and revenue up 81% Q/Q to $7.6M. </p>

<p>The revenue results for the top five vendors in Q3'05 are shown below:</p></p>

<p></p>

<p></p>

<p><span class=title>Table 1: Top Five OLED Manufacturers' Q3'05 Revenue and Growth (US$ 

<p>Millions)</span></p><br />
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" </p>

<p><br />
  <tr  bgcolor="#333333"><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Rank</font></strong></td><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Manufacturer</font></strong></td><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Revenue</font></strong></td><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Q/Q</font></strong></td><br />
<td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Y/Y</font></strong></td><br />
  </tr><br />
  <tr ><br />
    <td width="25%"><p align="center">1 </p></td><br />
    <td width="25%"><p align="center">Samsung,SDI </p></td><br />
    <td width="25%"><p align="center">$37.1 </p></td><br />
    <td width="25%"><p align="center">0% </p></td><br />
    <td width="25%"><p align="center">1% </p></td><br />
  </tr><br />
  <tr  bgcolor="#CCCCCC"><br />
    <td><p align="center">2 </p></td><br />
    <td width="25%"><p align="center">RiTdisplay </p></td><br />
    <td width="25%"><p align="center">$26.8 </p></td><br />
    <td width="25%"><p align="center">-5% </p></td><br />
    <td width="25%"><p align="center">77% </p></td><br />
  </tr><br />
  <tr ><br />
    <td width="25%"><p align="center">3 </p></td><br />
    <td width="25%"><p align="center">Pioneer </p></td><br />
    <td width="25%"><p align="center">$17.9 </p></td><br />
    <td width="25%"><p align="center">-9% </p></td><br />
    <td width="25%"><p align="center">9% </p></td><br />
  </tr><br />
  <tr  bgcolor="#CCCCCC"><br />
    <td width="25%"><p align="center">4 </p></td><br />
    <td width="25%"><p align="center">Univision </p></td><br />
    <td width="25%"><p align="center">$15.0 </p></td><br />
    <td width="25%"><p align="center">6% </p></td><br />
    <td width="25%"><p align="center">311% </p></td><br />
  </tr><br />
  <tr ><br />
    <td width="25%"><p align="center">5 </p></td><br />
    <td width="25%"><p align="center">TDK </p></td><br />
    <td width="25%"><p align="center">$7.6 </p></td><br />
    <td width="25%"><p align="center">81% </p></td><br />
    <td width="25%"><p align="center">321% </p></td><br />
  </tr><br />
  <tr bgcolor="#CCCCCC" ><br />
    <td width="25%"><p align="center"> </p></td><br />
    <td width="25%"><p align="center">Others </p></td><br />
    <td width="25%"><p align="center">$26.4 </p></td><br />
    <td width="25%"><p align="center">17%  </p></td><br />
    <td width="25%"><p align="center">19%  </p></td><br />
  </tr><br />
  <tr><br />
    <td width="25%"><p align="center"> </p></td><br />
    <td width="20%"><p align="center">Total </p></td><br />
    <td width="20%"><p align="center">$130.9 </p></td><br />
    <td width="22%"><p align="center">4% </p></td><br />
    <td width="22%"><p align="center">49% </p></td><br />
  </tr><br />
</table></p>

<p>The top five applications for OLED displays in shipments and growth are shown in the 

<p>following table:</p></p>

<p><span class=title>Table 1: Top Five Q3'05 OLED Applications: Shipments (000) and Growth 

<p></span></p><br />
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" </p>

<p>rules="all" frame="below"><br />
  <tr  bgcolor="#333333"><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Rank</font></strong></td><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Application</font></strong></td><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Shipments</font></strong></td><br />
    <td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Q/Q</font></strong></td><br />
<td width="25%" align="center" class=table_title><strong><font </p>

<p>color="#FFFFFF">Y/Y</font></strong></td><br />
  </tr><br />
  <tr ><br />
    <td width="25%"><p align="center">1 </p></td><br />
    <td width="25%"><p align="center">MP3</p></td><br />
    <td width="25%"><p align="center">8,265.0 </p></td><br />
    <td width="25%"><p align="center">35% </p></td><br />
    <td width="25%"><p align="center">2721% </p></td><br />
  </tr><br />
  <tr  bgcolor="#CCCCCC"><br />
    <td><p align="center">2 </p></td><br />
    <td width="25%"><p align="center">Subdisplay </p></td><br />
    <td width="25%"><p align="center">7,660.0 </p></td><br />
    <td width="25%"><p align="center">8% </p></td><br />
    <td width="25%"><p align="center">30% </p></td><br />
  </tr><br />
  <tr ><br />
    <td width="25%"><p align="center">3 </p></td><br />
    <td width="25%"><p align="center">Car Audio </p></td><br />
    <td width="25%"><p align="center">431.0 </p></td><br />
    <td width="25%"><p align="center">-5% </p></td><br />
    <td width="25%"><p align="center">53% </p></td><br />
  </tr><br />
  <tr  bgcolor="#CCCCCC"><br />
    <td width="25%"><p align="center">4 </p></td><br />
    <td width="25%"><p align="center">Auto Console </p></td><br />
    <td width="25%"><p align="center">132.0 </p></td><br />
    <td width="25%"><p align="center">4% </p></td><br />
    <td width="25%"><p align="center">71% </p></td><br />
  </tr><br />
  <tr ><br />
    <td width="25%"><p align="center">5 </p></td><br />
    <td width="25%"><p align="center">Main Display </p></td><br />
    <td width="25%"><p align="center">128.5 </p></td><br />
    <td width="25%"><p align="center">-61% </p></td><br />
    <td width="25%"><p align="center">7% </p></td><br />
  </tr><br />
  <tr bgcolor="#CCCCCC" ><br />
    <td width="25%"><p align="center"> </p></td><br />
    <td width="25%"><p align="center">Others </p></td><br />
    <td width="25%"><p align="center">125.5 </p></td><br />
    <td width="25%"><p align="center">77%  </p></td><br />
    <td width="25%"><p align="center">-25%  </p></td><br />
  </tr><br />
  <tr><br />
    <td width="25%"><p align="center"> </p></td><br />
    <td width="20%"><p align="center">Total </p></td><br />
    <td width="20%"><p align="center">16,742.0 </p></td><br />
    <td width="22%"><p align="center">18% </p></td><br />
    <td width="22%"><p align="center">144% </p></td><br />
  </tr><br />
</table></p>

<p><B><I><a href="http://www.displaysearch.com/products/?pn=qoled">The Quarterly OLED 

<p>Shipment and Forecast Report</B></I></a> includes shipments, revenues, and ASPs by </p>

<p>application, material type, driver technology and supplier. <B><I><a </p>

<p>href="http://www.displaysearch.com/products/?pn=qoled">The Quarterly OLED Shipment and </p>

<p>Forecast Report</B></I></a> also shows capacity plans by supplier and has a comprehensive </p>

<p>supply/demand forecast. This report is delivered in PowerPoint and includes Excel pivot </p>

<p>tables. Please contact Arie Braun at DisplaySearch at 512-459-3126 or <a </p>

<p>href="mailto:arie@displaysearch.com">arie@displaysearch.com</a> for further information.</p></p>

<p><br />
<p><B>Register for US FPD by December 31 and save!</B>  DisplaySearch will also hold its </p>

<p><B><I><a href="http://www.displaysearch.com/usfpd2006">8th Annual DisplaySearch US FPD </p>

<p>Conference</a></I></B> at the beautiful Loews Coronado in San Diego on March 21-23, 2006.  </p>

<p>Save $200 by signing up as an attendee by December 31,2005.  Sponsorships and exhibits are </p>

<p>also available now!  Please visit the official conference website at <a </p>

<p>href="http://www.displaysearch.com/usfpd2006">www.displaysearch.com/usfpd2006</a> or contact </p>

<p>Kendra Smith at 512-459-3126 x107 or <a </p>

<p>href="mailto:kendra@displaysearch.com">kendra@displaysearch.com.</a></p></p>

<p><B>About DisplaySearch</B><br />
<p>DisplaySearch, an NPD Group company, has a core team of 33 employees located in North </p>

<p>America and Asia who produce a valued suite of market forecasts, technology assessments, </p>

<p>surveys, studies and analyses. The company also organizes influential events worldwide. </p>

<p>Headquartered in Austin, Texas, DisplaySearch has regional operations in Chicago, Hong Kong, </p>

<p>Houston, Kyoto, San Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is </p>

<p>on the web at <a href="http://www.displaysearch.com">www.displaysearch.com.</a></p></p>

<p><B>About The NPD Group Inc.</B><br />
<p>Since 1967, The NPD Group has provided reliable and comprehensive consumer and retail </p>

<p>information for a wide range of industries. Today more than 1,400 manufacturers and </p>

<p>retailers rely on NPD to help them better understand their customers, product categories, </p>

<p>distribution channels and competition in order to help guide their business. Information </p>

<p>from The NPD Group is available for the following major vertical sectors: apparel, </p>

<p>appliances, automotive, beauty, consumer electronics, food and beverage, foodservice, </p>

<p>footwear, home improvement, housewares, imaging, information technology, music, software, </p>

<p>travel, toys, video games, and wireless. For more information, visit <a </p>

<p>href="http://www.npd.com">www.npd.com.</a></p></p>

<p></p>

<p>			<br />
<!-- ---- End Content ---- -->			<br />
				<br><br><br />
			</td><br />
			<td width="70">&nbsp;</td><br />
		</tr><br />
		</table><br />
	</td><td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
<tr><td BGCOLOR="#333333">&nbsp;</td><br />
	<td><a href="http://www.displasearch.com/"><img <br />
	src="http://www.displaysearch.com/press/pr_email/press-foot.gif" <br />
	alt="" width="718" height="48" border="0"></a></td><br />
	<td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
<tr><td BGCOLOR="#333333">&nbsp;</td><br />
	<td BGCOLOR="#333333" align="right"><br />
		<font size="-2" color="#666666"><br />
		<a </p>

<p>href="mailto:listserve@displaysearch.com?subject=unsubscribe">unsubscribe</a><br><br />
		&copy; 2005 <a href="http://www.displaysearch.com/"><font </p>

<p>color="#777777">DisplaySearch</font></a><br />
		&bull; Site by <a href="http://www.gogocreative.com/"<br />
		onMouseOver="self.status='Gogo Creative'; return true"><font </p>

<p>color="#777777">GoGo Creative</font></a><br />
		</font><br />
		<BR><BR><BR><BR><BR><BR><br />
	</td><br />
	<td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
</table><br />
</center><br />
<br><br><br></p>

<p></BODY><br />
</HTML></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>November 30, 2005 11:26 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(262)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 262)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/11/displaysearch-announces-q305-oled-yy-growth-shipments-up-144-revenues-up-49.php" type="text/javascript" charset="utf-8"></script>
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
