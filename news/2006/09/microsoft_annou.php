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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 453 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 453
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Microsoft Announces Xbox HD DVD Player Availability for U.S.&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php&amp;title=Microsoft Announces Xbox HD DVD Player Availability for U.S.">
		<span style="display:none">Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &amp;#8364;199.99/&amp;#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with ...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 453";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Microsoft Announces Xbox HD DVD Player Availability for U.S." height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Microsoft Announces Xbox HD DVD Player Availability for U.S." />
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
	<title>HDTV Magazine - Microsoft Announces Xbox HD DVD Player Availability for U.S.</title>
	<meta name="keywords" content="xbox live, game studios, microsoft game, interactive entertainment, dvd player, xbox, game, new, microsoft, games, studios, entertainment, dvd, live, windows, player, next, interactive, titles, available, gamers, online, world, first, title" />
	<meta name="description" content="Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &amp;#8364;199.99/&amp;#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with ..." />
	<meta name="title" content="Microsoft Announces Xbox HD DVD Player Availability for U.S." />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=453', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php">Microsoft Announces Xbox HD DVD Player Availability for U.S.</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 27, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="editorial"><img src="/images/bulletins/xbox360-hd-dvd.gif" alt="Xbox 360 HD DVD" align="left">Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &#8364;199.99/&#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with both the Universal Pictures blockbuster Peter Jackson's King Kong on HD DVD (for a limited time) and the Xbox 360 Universal Media Remote.<br clear="all"></p>

<p>Here is the full Newsflash from Xbox.com:</p>

<p class="prtitle">Xbox 360 Welcomes New Worlds of Entertainment</p>

<p>Barcelona, Spain-On the shores of one of the world's most artistic and progressive cities, Microsoft Corp. today thrilled attendees at its annual X06 event by inviting everyone to experience the next generation now on the Xbox 360&trade; system. The announcements and hands-on gameplay experiences highlight how the world's greatest game creators are pushing the boundaries of what is possible in high definition and online storytelling. Highlights of the announcements included the following:</p>

<p>    * A landmark partnership between Academy Award-winning writer, director, and producer Peter Jackson, Academy Award-winning screenwriter Fran Walsh, and Microsoft Game Studios will create two new interactive entertainment series exclusively for Xbox 360 and Xbox Live&reg;. The first will be a collaborative effort with Bungie Studios to co-create the next great chapter in the Halo&reg; universe. The second will be an entirely original property targeted at bringing new audiences into the captivating world of interactive entertainment. In addition, Microsoft Game Studios will partner with Jackson and Walsh to establish Wingnut Interactive, a studio dedicated to the creation of world-class interactive entertainment.<br />
    * Halo Warsis an all-new real-time strategy game based on the legendary Halo universe and designed exclusively for Xbox 360 by Ensemble Studios, creators of the Age of Empires&reg; franchise.<br />
    * Rockstar and Take-Two will provide Xbox 360 gamers with exclusive access to two epic  downloadable episodes of Grand Theft Auto IV via Xbox Live, each with hours of new gameplay content, and available only on Xbox 360 just months after the release of the title.<br />
    * Ubisoft confirmed that the next Splinter Cell title, the installment after Tom Clancy's Splinter Cell&reg; Double Agent&trade;, will be exclusive to Xbox 360, a testament to the ability of the powerful next-gen game console to deliver experiences no other console can match.<br />
    * 2K Games confirmed that BioShock , a first person shooter that will revolutionize the genre and forever change the expectations of gamers, will be released exclusively on Xbox 360 and Microsoft&reg;Windows&reg; next spring.<br />
    * Project Gotham Racing&reg; 4, was unveiled, the latest addition to the best-selling franchise, made exclusively for Xbox 360 by Bizarre Creations. PGR4 promises to continue the series' pedigree of innovation by introducing exciting new experiences to racing fans worldwide.<br />
    * The beloved Banjo-Kazooie&reg; franchise will breathe new, high-definition life exclusively on Xbox 360, from famed developer Rare Ltd. Beloved characters Banjo, Kazooie and Gruntilda-among other fan favorites-will new next-gen visuals and presentation as well as their sharp wit and hilarious sense of humor.<br />
    * Microsoft Game Studios will release its highly-anticipated new MMO game, Marvel Universe Online for both Xbox 360 and the Windows Vista&trade; operating system. MUO was developed by industry luminaries Cryptic Studios, creators of the smash hits City of Heroes and City of Villains.<br />
    * Expect two new additions to Xbox Live Arcade: The FPS that pioneered the network-gaming era, DOOM&reg;, from acclaimed developer id Software and Activision, is available now on Xbox Live Marketplace. The game includes the original four-episode single-player game, four-player split screen action, both co-op and deathmatch, and four player co-op and deathmatch via Xbox Live. Coming soon to Xbox Live Arcade is Sensible World of Soccer from Codemasters. Based on a classic Amiga title from 1994, Sensible World of Soccer will let gamers choose between the original graphics or an updated, high-resolution look and feel-while still capturing the original game's wide world of football.<br />
    * Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &#8364;199.99/&#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with both the Universal Pictures blockbuster Peter Jackson's King Kong on HD DVD (for a limited time) and the Xbox 360 Universal Media Remote.</p>

<p>"Xbox 360 is changing the way developers are telling stories today-from the industry's most beloved franchises to exciting new properties," said Peter Moore, corporate vice president of the Interactive Entertainment Business in the Entertainment and Devices Division at Microsoft. "We are inspiring the imaginations of the entertainment industry's best and most creative talent to take their franchises in exciting directions, while also spinning new tales for everyone."</p>

<p>"We are incredibly pleased to be here in Barcelona to talk about the next chapter of Xbox&reg; and how Xbox 360 continues to deliver on the promise and potential of the next generation," said Chris Lewis, regional vice president of the Home and Entertainment Division, Europe, Middle East, and Africa (EMEA), at Microsoft. "As we prepare to launch some of our biggest global titles and best regional content, both right now and well into the future, consumers will have much to choose from with a system designed for high-definition and online entertainment. We are the only next-generation experience that seamlessly connects players to their games, friends, and entertainment content."</p>

<p><b>Xbox 360 Expands Popular Franchises</b><br />
Xbox 360 continues to expand the interactive entertainment landscape, enabling industry superstars to take beloved franchises in exciting new directions. Microsoft Game Studios announced its partnership with famed movie director Peter Jackson to herald in a new age of interactive entertainment that can only be realized through Xbox 360 and Xbox Live. The goal of this partnership is to not only create new stories, but to redefine the way they are told. The first part of this long-term relationship is for Jackson, his partner Fran Walsh, and their team to bring to life two new interactive entertainment experiences exclusively for Xbox 360 and Xbox Live. The first project is to co-write, co-design, and co-produce a completely new and original chapter in the Halo universe in collaboration with Bungie Studios. The second project is an entirely original property from the team in New Zealand that will not only bring a whole new interactive story to life, but will also captivate new audiences that have yet to discover the power of interactive entertainment.</p>

<p>Additionally, in collaboration with Microsoft Game Studios, Jackson and Walsh are creating Wingnut Interactive. The two companies will create a world-class interactive entertainment studio that fuses the strength of Microsoft's technology and interactive entertainment experience with the creative and imaginative excellence of the Wingnut team.</p>

<p>"Microsoft has built an amazing living canvas with Xbox 360 and Xbox Live, which allows the storytellers of our time to express themselves in a new medium. They have fundamentally changed how people think about games," Jackson said. "My vision, together with Microsoft Game Studios, is to push the boundaries of game development and the future of interactive entertainment. From a movie-maker's point of view, it is clear to me that the Xbox 360 platform is the stage where storytellers can work their craft in the same way they do today with movies and books, but taking it further with interactivity."</p>

<p>In addition to demonstrating its leadership in the next-generation of games through exclusive alliances, Microsoft Game Studios announced Halo Wars, an all-new RTS game based on the legendary Halo universe and built exclusively for Xbox 360 by Ensemble Studios, the creators of the Age of Empires franchise. Halo Wars places the player in command of human UNSC armies as they deploy for mankind's first deadly encounter with the enemy forces of the Covenant.</p>

<p>In addition, Microsoft Game Studios provided first details surrounding Project Gotham Racing 4, the latest addition to the premiere racing franchise from Bizarre Creations, and the reunion of Banjo, Kazooie, and Gruntilda in an all-new addition to the Banjo-Kazooie franchise from industry veterans Rare.</p>

<p>Microsoft and Ubisoft announced that the next Splinter Cell title will be created exclusively for Xbox 360. Based on the increasingly proven potential of the Xbox 360 hardware and its online promise, Ubisoft confirmed that Xbox 360 will be the exclusive platform for the next iteration of its massively popular and influential espionage franchise. Through the power of Xbox Live, the series that revolutionized online cooperative and competitive gameplay promises to transform and modernize online gaming once again.</p>

<p>New details regarding the epic, exclusive episodic content for the upcoming and highly-anticipated Grand Theft Auto IV from Rockstar and Take-Two were also revealed; Rockstar Games will offer two downloadable episodes, each with hours of new gameplay, extending the experience of what already promises to be an immense game. Both chapters will be exclusive and available only to Xbox 360 gamers via Xbox Live. Grand Theft Auto IV will be available to Xbox 360 gamers on its first day of availability: October 16, 2007, in North America and October 19, 2007, in Europe.</p>

<p><b>Xbox Live Announcements</b><br />
Xbox Live Arcade made a surprise announcement today, unveiling one of the greatest games of the 3-D era: DOOM is now available for download for only 800 Microsoft points. The game brings legendary DOOM mayhem to gamers, who for the first time ever can relive the classic demon-blasting frag fest in both single-player and two-to-four player co-op and deathmatch modes over Xbox Live. Also a new, multi-title relationship with Codemasters was announced, with the first title being the classic, fast-action soccer game, Sensible World of Soccer.</p>

<p>Xbox Live is a thriving online game community, connecting more than 3 million members across nearly 25 countries to enjoy hundreds of social games, as well as on-demand game demos, Xbox Live Arcade games, music, and movie content. With more than 10 million downloads to date and nearly 100 independent, classic, and original development titles available by next summer, Xbox Live Arcade is a fast-growing phenomenon.</p>

<p><b>Jump Into HD DVD Affordably</b><br />
At X06, exciting details about the much-anticipated Xbox 360 HD DVD Player were also revealed. Available in mid-November, 2006 in North America for $199.99 (ESRP), in the U.K., France, and Germany for &#8364;199.99 (&#163;129.99) (ESRP), and other territories in 2007, the Xbox 360 HD DVD Player comes with the Universal Pictures blockbuster film Peter Jackson's King Kong on HD DVD (for a limited time) and the Xbox 360 Universal Media Remote. Users can just add the Xbox 360 HD DVD Player to their Xbox 360 to create the ultimate home-theater experience.</p>

<p>"The Xbox 360 HD DVD Player is the best high-definition movie experience and value on the market," Moore said. "The reviews, the word of mouth, and the consumer response have all been crystal clear-HD DVD is the format of choice. We're not forcing movie technology on game players, but are instead letting them choose how to personalize their experiences. If they want HD DVD, there's no better value out there."</p>

<p>The Xbox 360 HD DVD Player offers up to six times higher resolution than DVD, and as part of the fall 2006 console update all Xbox 360 consoles will have the ability to output native resolution 1080p games and movies. Users can enjoy blockbuster HD DVD releases, with more than 150 titles available by the holidays from major movie studios including Paramount Pictures, StudioCanal, Universal Studios, New Line Entertainment, HBO, and Warner Bros. Entertainment Inc.</p>

<p><b>The Most Anticipated Titles</b><br />
In addition to taking popular franchises in new directions, developers are finding new life through Xbox 360 with exciting new content, and are delivering many of the industry's most praised and anticipated titles.</p>

<p>Arguably the most anticipated title of 2006, Gears of War&reg; from Epic Games and Microsoft Game Studios is a third-person tactical action/horror game available exclusively on Xbox 360.Gears of War will be the only game to blend a deep and disturbing story of human survival against hordes of nightmarish creatures with a next-generation tactical combat system and unsurpassed visuals and special effects. Gears of War, which will be available Nov. 12, 2006, in the U.S. and Nov. 17, 2006, in Europe, has garnered numerous industry awards and accolades including the Game Critics Awards Best Console and Best Action Game of E3 2006, IGN's E3 2005 Best Xbox 360 Game, and GameSpot's E3 2006 People's Choice Award.</p>

<p>From leading U.K. based developer Rare and Microsoft Game Studios comes Viva Piñata&trade;, an original game concept and the latest innovative gaming experience for gamers of all ages and types. Viva Piñata invites gamers to create an immersive world where living piñatas inhabit an ever-changing environment. Viva Pinata, which has won several industry awards including Best Graphics from Nick Jr. Magazine and IGN's Runner-Up for Best Strategy Game of E3 2006, is scheduled to be available this holiday.</p>

<p>Underlining the Xbox 360 platform strength, Microsoft and Ubisoft today confirmed that Assassin's Creed, the eagerly anticipated action title during the crusades, is also coming to Xbox 360 on the same day and date as the game's release on other platforms. Assassin's Creed is a next-generation action-adventure/stealth title from the highly talented and critically acclaimed team that brought gamers Prince of Persia: The Sands of Time . Assassin's Creed will place gamers in the role of a ruthless and skilled assassin as he silently stalks his victims.</p>

<p>Last week at the Tokyo Game Show in Japan, Lost Odyssey wowed audiences with its incredible graphic style and epic storyline. From famed Japanese developer Hironobu Sakaguchi, Lost Odyssey will be shipped in Japan in 2007, and in the U.S. and Europe at a later date.</p>

<p>Few games have generated more interest than BioShock in the past year, and Microsoft confirmed today that the highly anticipated first-person shooter will be exclusive to Xbox 360 and Windows when it launches in spring 2007.</p>

<p><b>More Exciting Titles</b><br />
Looking into the coming year, X06 showcased a montage video trailer demonstrating the dazzling power and versatility of the Xbox 360 platform, promising gamers a choice of titles and genres coming this spring season, including the Microsoft Game Studios titles Crackdown&trade;, Too Human, Mass Effect&trade;, and Forza Motorsport&trade; 2. In addition to these titles from Microsoft Game Studios, games from today's leading publishers round out what is already a robust library of offerings for the Xbox 360 platform, including John Woo Presents Stranglehold, Lost Planet, and Pro Evolution Soccer 6.</p>

<p>Microsoft underscored the continued momentum behind Xbox 360 with more than 5 million consoles sold since launch, the fastest console launch ever. Microsoft remains on track to deliver 10 million consoles worldwide, with a library of 160 games, by the end of the year. The Xbox Live community continues to grow and is on track to double in size to 6 million gamers by June 2007. Those numbers are supported by the most impressive number of all: Xbox 360 is now available in more than 30 countries since the console launched last November. By the end of this year, Xbox 360 will launch in even more countries, including South Africa and in Europe where plans are set to distribute the console in Slovakia, the Czech Republic, Hungary, and Poland.</p>

<p><b>Games for Windows</b><br />
Microsoft also provided attendees with an update on Games for Windows and announced several exciting new Games for Windows titles, including Bioshock and Marvel Universe Online. Starting this September with LEGO&reg; Star Wars&reg; II: The Original Trilogy from LucasArts and Company of Heroes from THQ, games will carry the Games for Windows branding after meeting a set of technical guidelines designed to provide consumers with a consistent, reliable gaming experience on Windows XP and Windows Vista. The guidelines include easier game installation, improved reliability, and support for key Windows Vista features such as the Games Explorer and Parental Controls. They will also support wide-screen gaming, launch from within Windows Media&reg; Center, be compatible with 64-bit consumer versions of Windows, and will support the Xbox 360 Controller for Windows (for games that enable gamepads).</p>

<p>Attendees were given a glimpse of the exciting upcoming Games for Windows titles including Hellgate: London, Rail Simulator, and Age of Conan: Hyborian Adventures, as well as Microsoft Game Studios titles Age of Empires III: The WarChiefs, Alan Wake, Zoo Tycoon&reg; 2: Marine Mania&reg;, Flight Simulator X, Shadowrun&trade;, Halo&reg; 2 for Windows Vista, and the newly named Marvel Universe Online, a massively multiplayer online game for Xbox 360 and Windows Vista from Cryptic Studios, creators of the hit City of Heroes franchise.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 27, 2006 11:51 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(453)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 453)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php" type="text/javascript" charset="utf-8"></script>
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
