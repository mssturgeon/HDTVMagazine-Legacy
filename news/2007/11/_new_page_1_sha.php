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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 774 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 774
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php&amp;title=Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel">
		<span style="display:none">AUSTIN, TEXAS, November 1, 2007--The North American TV brand sell-in rankings were shaken up again in Q3'07 with Sharp on top in LCD TVs for the first time since Q1'05. As shown in Table 1, Sharp led the North American LCD TV market with an 11.3% share, rising from #3 in Q2'07, on 65% Q/Q and 88% Y/Y growth. Its strong growth can be attributed to:</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 774";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" />
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
	<title>HDTV Magazine - Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel</title>
	<meta name="keywords" content="unit share, north american, flat panel, lcd tvs, table preliminary, growth, lcd, share, sharp, volume, unit, panel, north, samsung, tvs, led, total, plasma, rose, panasonic, sony, market, table, vizio, american" />
	<meta name="description" content="AUSTIN, TEXAS, November 1, 2007--The North American TV brand sell-in rankings were shaken up again in Q3'07 with Sharp on top in LCD TVs for the first time since Q1'05. As shown in Table 1, Sharp led the North American LCD TV market with an 11.3% share, rising from #3 in Q2'07, on 65% Q/Q and 88% Y/Y growth. Its strong growth can be attributed to:" />
	<meta name="title" content="Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=774', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php">Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>November  1, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=371&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=367&category=LCD HDTVs">LCD HDTVs</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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

<div>
	<strong><font color="#000000" size="2">Sharp and Samsung Reclaim Top 
	Positions in LCD and Flat Panel TVs According to DisplaySearch; Vizio Falls 
	to #2 in Each Category</font></strong></div>
<div>
	&nbsp;</div>
<div>
	<span class="style1"><font color="#000000" size="2"><strong>AUSTIN, TEXAS, 
	November 1, 2007--</strong>The North American TV brand sell-in rankings were 
	shaken up again in Q3'07 with Sharp on top in LCD TVs for the first time 
	since Q1'05. As shown in Table 1, Sharp led the North American LCD TV market 
	with an 11.3% share, rising from #3 in Q2'07, on 65% Q/Q and 88% Y/Y growth. 
	Its strong growth can be attributed to </font></span>
	<ul class="style1">
		<li><font color="#000000" size="2">Rapidly growing internal panel 
		capacity--Sharp had the fastest sequential TFT LCD supply growth of any 
		panel supplier, up 36% Q/Q, as it continues to ramp its 8G fab. </font>
		</li>
		<li><font color="#000000" size="2">Growing LCD TV focus--Sharp's 
		worldwide LCD TV panel shipments rose from 56% to 64% of its total 
		large-area TFT LCD volume, taking share from notebook PCs. </font></li>
		<li><font color="#000000" size="2">Significant emphasis on smaller sizes 
		where demand is strong and supply is tight--Sharp's &lt;32&quot; volume rose 77% 
		Q/Q, rising to 40% of its Q3'07 volume. Sharp was #1 in 19&quot; and 26&quot; and 
		also led at 52&quot;. </font></li>
		<li><font color="#000000" size="2">Increased emphasis on the North 
		American LCD TV market--North America rose from 24% to 34% of Sharp's 
		worldwide Q3'07 LCD TV volume. </font></li>
	</ul>
	<p class="style1"><strong><font color="#000000" size="2">Table 1: 
	Preliminary Q2'07 - Q3'07 North American LCD TV Unit Share and Growth</font></strong></p>
	<table border="1" cellpadding="0" cellspacing="0" width="403" id="table1">
		<tr align="center" bgcolor="#333333">
			<td class="style1" valign="bottom" width="73"><b>
			<font color="#000000" size="2">
			<p align="center">Rank</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73"><b>
			<font color="#000000" size="2">Brand</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q2'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q3'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q/Q<br>
			Growth</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Y/Y<br>
			Growth</font></b></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">1</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Sharp</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">65%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">88%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">2</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Vizio</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">12.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.9%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">19%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">334%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">3</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Samsung</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">33%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">79%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">4</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Sony</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">6.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">108%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">84%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">5</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Funai</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.9%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">8.1%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">39%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">20%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="73">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Other</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">53.5%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">49.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">24%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">73%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Total</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">34%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">82%</font></td>
		</tr>
	</table>
	<p class="style1"><font color="#000000" size="2">Other LCD TV highlights 
	include</font></p>
	<ul class="style1">
		<li><font color="#000000" size="2">Vizio fell to #2 despite 334% Y/Y 
		growth; it had the slowest Q/Q growth of the top five. Its slower Q3'07 
		sequential growth can be explained by the less seasonal nature of its 
		primary sales channel: the warehouse club channel. Nonetheless, it was 
		#1 in 32&quot; and larger volume and in LCD HDTV volume. It also led the 32&quot;, 
		37&quot; and 42&quot; markets. </font></li>
		<li><font color="#000000" size="2">Samsung fell from #2 to #3 despite 
		33% Q/Q and 79% Y/Y growth. It was #2 in 40&quot;+ volume. </font></li>
		<li><font color="#000000" size="2">Sony had the fastest Q/Q growth of 
		the top five brands up 108% Q/Q and 84% Y/Y, as it rolled out a number 
		of compelling new products later this year than last year, while also 
		targeting mass merchants like Wal-Mart through the new M series. As a 
		result, Sony's unit ranking rose from #7 to #4, and it jumped from #3 to 
		#1 in revenues. Sony had the highest focus on 40&quot; and larger LCD TVs 
		which accounted for 67% of its volume. It led at 40- 42&quot; and 46-47&quot;.
		</font></li>
		<li><font color="#000000" size="2">Funai fell from #4 to #5 in units and 
		led at 15&quot; and 20&quot;. </font></li>
		<li><font color="#000000" size="2">Preliminary totals for LCD TVs rose 
		34% Q/Q and 82% Y/Y to a new record high of 6.6M units. LCD TVs rose to 
		88% of flat panel TV volume vs. 78% in Q3'06. </font></li>
	</ul>
	<p class="style1"><font color="#000000" size="2">In plasma TVs, Panasonic 
	continued to lead, as shown in Table 2. Panasonic earned a 30% share on 11% 
	Q/Q growth. However, on a Y/Y basis, Panasonic's volume was down 28% after 
	it shipped an excessive number of plasma TVs into North America a year ago 
	when the European market stalled after the World Cup. With Panasonic down, 
	the total plasma TV market was down 17% Y/Y to a preliminary total of 866K 
	units. Other highlights include</font></p>
	<ul class="style1">
		<li><font color="#000000" size="2">Samsung and LGE each gained share on 
		significant growth at larger sizes and also enjoyed success with new 
		1080p products.&nbsp; </font></li>
		<li><font color="#000000" size="2">Vizio fell from #4 to #7 as it exited 
		the 42&quot; plasma market to focus on 42&quot; LCD. </font></li>
		<li><font color="#000000" size="2">Hitachi enjoyed the fastest Q/Q 
		growth on more than a 100% increase in 50&quot;, rising from #6 to #4. </font>
		</li>
		<li><font color="#000000" size="2">Panasonic led at 42&quot;, 50&quot; and 55-59&quot; 
		and in all 1080p products. LGE led at 60&quot;+. </font></li>
	</ul>
	<p class="style1"><strong><font color="#000000" size="2">Table 2: 
	Preliminary Q2'07 - Q3'07 North American Plasma TV Unit Share and Growth</font></strong></p>
	<table border="1" cellpadding="0" cellspacing="0" width="409" id="table2">
		<tr align="center" bgcolor="#333333">
			<td class="style1" valign="bottom" width="77"><b>
			<font color="#000000" size="2">
			<p align="center">Rank</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77"><b>
			<font color="#000000" size="2">Brand</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q2'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><b><font color="#000000" size="2">Q3'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q/Q<br>
			Growth</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Y/Y<br>
			Growth</font></b></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">1</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Panasonic</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">32.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">30.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-28%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">2</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Samsung</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">15.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">19.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">53%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">5%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">3</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">LGE</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.6%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">13.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">56%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">23%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">4</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Hitachi </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.1% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.6% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">64% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">28% </font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">5</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Philips </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.3% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.4% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-3% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-41% </font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="77">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Other </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">24.5% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">19.5% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-4% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-33% </font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Total</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">21%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-17%</font></td>
		</tr>
	</table>
	<p class="style1"><font color="#000000" size="2">Based on the total LCD and 
	plasma volume, the flat panel TV rankings are shown in Table 3. As 
	indicated, Samsung overtook Vizio to earn the #1 position due to strong 
	growth in both LCD and plasma TVs. Sharp, Sony and Funai remained at #3 - #5 
	with each gaining share. </font></p>
	<p class="style1"><strong><font color="#000000" size="2">Table 3: 
	Preliminary Q2'07 - Q3'07 North American Flat Panel TV Unit Share and Growth</font></strong></p>
	<table border="1" cellpadding="0" cellspacing="0" width="397" id="table3">
		<tr align="center" bgcolor="#333333">
			<td class="style1" valign="bottom" width="71"><b>
			<font color="#000000" size="2">
			<p align="center">Rank</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71"><b>
			<font color="#000000" size="2">Brand</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q2'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q3'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q/Q<br>
			Growth</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Y/Y<br>
			Growth</font></b></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">1</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Samsung</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11.4%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">37%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">57%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">2</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Vizio</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">12.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">12%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">297%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">3</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Sharp</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">8.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">65%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">88%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">4</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Sony</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">5.5%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">8.6%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">108%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">84%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">5</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Funai</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">6.9%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">39%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">16%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="71">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Other</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">56.1%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">52.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">24%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">44%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Total</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">33%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">60%</font></td>
		</tr>
	</table>
	<p class="style1"><font color="#000000" size="2">DisplaySearch's TV market 
	intelligence including panel and TV shipments, TV shipments by region by 
	brand by size for nearly 60 brands, rolling 16-quarter forecasts, TV 
	cost/price forecasts and design wins can be found in its </font>
	<a rel="nofollow" target="_blank" href="http://mail.hdtvmagazine.com/Redirect/www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-2318CD58/displaysearch/hs.xsl/quarterly_global_tv_shipment_and_forecast_report.asp">
	<em><font color="#000000" size="2">Quarterly Global TV Shipment and Forecast 
	Report</font></em></a><font color="#000000" size="2">. For more information 
	on this report, please contact Arie Braun at (512) 687-1505 or </font>
	<a rel="nofollow" target="_blank" href="mailto:arie@displaysearch.com">
	<font color="#000000" size="2">arie@displaysearch.com</font></a><font color="#000000" size="2">.
	</font></p>
	<p class="style1"><font color="#000000" size="2">DisplaySearch is also 
	holding a webinar on the Black Friday retail results. For more information, 
	please </font>
	<a rel="nofollow" target="_blank" href="http://mail.hdtvmagazine.com/Redirect/guest.cvent.com/EVENTS/Info/Summary.aspx?e=1d7b8086-6323-48dc-9656-b04f8d6348d3">
	<font color="#000000" size="2">follow this link</font></a><font color="#000000" size="2">.
	</font></div>

<p></body></p>

<p></html><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>November  1, 2007 11:42 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(774)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 774)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php" type="text/javascript" charset="utf-8"></script>
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
