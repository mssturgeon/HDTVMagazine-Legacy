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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 461 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 461
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=EA Announces 30 Games in Development for PLAYSTATION 3&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php&amp;title=EA Announces 30 Games in Development for PLAYSTATION 3">
		<span style="display:none">Electronic Arts (NASDAQ:ERTS) today announced more than 30 games in development for the PLAYSTATION&amp;reg;3 computer entertainment system. When the system launches in November, EA will deliver some of the world's most popular game franchises including Madden NFL 07, Tiger Woods PGA TOUR&amp;reg; 07 and Need for Speed&amp;trade; Carbon. EA will release eight to ten games on the PLAYSTATION&amp;reg;3 by late March including EA SPORTS&amp;trade; Fight Night Round 3 and Def Jam: ICON&amp;trade;.

Paul Lee, President of EA Studios commented on the launch of the PLAYSTATION&amp;reg;3...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 461";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download EA Announces 30 Games in Development for PLAYSTATION 3" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="EA Announces 30 Games in Development for PLAYSTATION 3" />
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
	<title>HDTV Magazine - EA Announces 30 Games in Development for PLAYSTATION 3</title>
	<meta name="keywords" content="pga tour, need speed, power playstation, medal honor, electronic arts, playstation, new, game, games, power, speed, gameplay, experience, world, gamers, unique, pga, need, tour, system, def, electronic, arts, next, jam" />
	<meta name="description" content="Electronic Arts (NASDAQ:ERTS) today announced more than 30 games in development for the PLAYSTATION&amp;reg;3 computer entertainment system. When the system launches in November, EA will deliver some of the world's most popular game franchises including Madden NFL 07, Tiger Woods PGA TOUR&amp;reg; 07 and Need for Speed&amp;trade; Carbon. EA will release eight to ten games on the PLAYSTATION&amp;reg;3 by late March including EA SPORTS&amp;trade; Fight Night Round 3 and Def Jam: ICON&amp;trade;.

Paul Lee, President of EA Studios commented on the launch of the PLAYSTATION&amp;reg;3..." />
	<meta name="title" content="EA Announces 30 Games in Development for PLAYSTATION 3" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=461', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php">EA Announces 30 Games in Development for PLAYSTATION 3</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 20, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=275&category=Gaming">Gaming</a></b>
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
				<p class="prtitle">EA Announces 30 Games in Development for PLAYSTATION 3</p>

<p><em><center>Launch Titles Include Madden NFL 07, Need for Speed Carbon and Tiger Woods PGA TOUR 07</center></em></p>

<p><img src="/images/logos/ea.jpg" alt="EA" align="left"><b>REDWOOD CITY, Calif.--(BUSINESS WIRE)--</b> Electronic Arts (NASDAQ:ERTS) today announced more than 30 games in development for the PLAYSTATION&reg;3 computer entertainment system. When the system launches in November, EA will deliver some of the world's most popular game franchises including Madden NFL 07, Tiger Woods PGA TOUR&reg; 07 and Need for Speed&trade; Carbon. EA will release eight to ten games on the PLAYSTATION&reg;3 by late March including EA SPORTS&trade; Fight Night Round 3 and Def Jam: ICON&trade;.</p>

<p>Paul Lee, President of EA Studios commented on the launch of the PLAYSTATION&reg;3, "Each game has been custom designed to leverage the hardware power of the PlayStation 3 and serve as a launch pad for EA's next generation of HD gaming. This is only the beginning. In the months and years to come, developers will take greater advantage of the PlayStation 3's cell processors and blu-ray storage capacity to create games of stunning depth and texture."</p>

<p>Frank Gibeau, EA Executive Vice President of North America Publishing noted, "This is a very exciting time for gamers. EA's games on the PlayStation 3 will help propel HD forward. Over the course of the next 18 months, EA will roll out groundbreaking new original properties and spectacular new versions of perennial hits that will further maximize the power of the PlayStation 3's unique cell processor and outstanding blu-ray disk capacity."</p>

<p>To date, the complete list of EA games for PLAYSTATION&reg;3 includes: (listed alphabetically)</p>

<p><b>ARMY OF TWO&trade;</b></p>

<p>Delivering a groundbreaking 3rd person co-op shooter unparalleled in the action genre, EA Montreal's ARMY OF TWO focuses on gameplay centered around TWO man missions, TWO man strategies, TWO man tactics and a TWO man advantage. Taking advantage of the PLAYSTATION&reg;3 cell processor and multi-threading technology, as well as the SIXAXIS&trade; wireless controller, the EA Montreal team is creating an entirely new next-gen gameplay experience offering gamers shooting and play mechanics never before possible on the current generation of consoles. ARMY OF TWO will throw gamers into hot spots ripped from current day headlines where they will utilize unique TWO man strategies and tactics while seamlessly transitioning between playing with intelligent Partner AI and a live player.</p>

<p><b>Burnout&trade; 5</b></p>

<p>Burnout 5 harnesses the power of the PLAYSTATION&reg;3 to give players license to wreak havoc in Paradise City, the ultimate seamless racing battleground. Every inch of the world in Burnout 5 is built to deliver heart-stopping Burnout-style crashes and spectacular gameplay</p>

<p><b>Battlefield: Bad Company&trade;</b></p>

<p>Built from the ground-up using the bleeding-edge Frostbite&trade; game engine, Battlefield: Bad Company drops PLAYSTATION&reg;3 gamers behind enemy lines with a squad of renegade soldiers who risk it all on a personal quest for gold and revenge. Featuring a deep, cinematic single-player experience loaded with adventure and dark humor, the game delivers the series' trademark sandbox gameplay in a universe where nearly everything is destructible. Battlefield: Bad Company also will feature a full suite of the franchise's trademark multiplayer options with deep gameplay designed to take full advantage of the game's massively destructible environments.</p>

<p><b>Def Jam: ICON&trade;</b></p>

<p>Infusing hip-hop music, culture and lifestyle into the gameplay, EA Chicago and urban lifestyle powerhouse Def Jam Interactive, continue to push the boundaries of game development bringing unique and innovative content to the next generation of gaming. In Def Jam: ICON, EA Chicago is changing the way fighting games are played. With the power of the PLAYSTATION&reg;3 Cell Processor, Def Jam: ICON features the most lifelike characters seen on any platform as well as a living breathing environment that animates and pulsates to the beat of the music. Not only is the environment reaching the next generation of art but each piece of the environment moves individually to the music being played during the fight. As the environment gets destroyed, the characters and the pieces of environment are animated with real world physics driven by the cell processor. The level of interaction with the environment, the smooth and fluid character animations, and the environmental detail and physics are all possible with the power of the PLAYSTATION&reg;3.</p>

<p><img src="/images/bulletins/fight-night-round-3.jpg" alt="Fight Night Round 3" align="left"><b>Fight Night Round 3</b></p>

<p>EA SPORTS Fight Night Round 3 for the PLAYSTATION&reg;3 will be shipping on December 12 to retail stores nationwide. Featuring exclusive content including a comprehensive ESPN Integration package and a new first person mode called Get in the Ring, the PLAYSTATION&reg;3 version continues to innovate on the hit franchise. Get in the Ring mode allows gamers to experience the fight through the eyes of the boxer. For the first time ever, gamers will truly experience the sensation of the sport with visual and audio effects like ear ringing, restricted vision, flashes of bright light, color shifts and blur effects that simulate the sense and feeling of getting punched. Imagine trying to recover from Ali's lighting fast jabs when you can barely make out his glove through a blinding barrage of flashes and blur. Furthermore, the boxers are more lifelike than ever with everything from the boxer's sweat and skin to the appearance of their muscles and veins all adding to the realistic gameplay experience. Tapping into the power of the PLAYSTATION&reg;3, the EA Canada development team has come up with new ways to make the boxers look more photo realistic than ever before, like seeing the reflection of the venue walls in the sweat sheen.<br clear="all" /></p>

<p><b>Madden NFL 07</b></p>

<p>With unparalleled next-generation power, new gang-tackling physics, and jaw-dropping graphics, Madden NFL 07 for the PLAYSTATION&reg;3 delivers a previously unimaginable experience that blurs the line between gaming and reality. The brand new SIXAXIS motion sensor controller puts complete command of your players at your fingertips like never before by allowing you to throw the perfect block or deliver punishing defensive hits.</p>

<p><b>Medal of Honor Airborne&trade;</b></p>

<p>Medal of Honor Airborne is the newest installment from EA's critically-acclaimed Medal of Honor&trade; franchise which was credited with pioneering the First-Person Shooter (FPS) WWII genre when it debuted in 1999. While building on the key tenets of the franchise including historical accuracy and authenticity, Medal of Honor Airborne is set to redefine the series by introducing players to an entirely new way of experiencing a WWII FPS - namely the fully interactive Airborne experience. Players will step into the boots of Boyd Travers, Private First Class of the 82nd Airborne Division and engage in battles throughout Europe. From rocky beginnings in Sicily to war-winning triumphs in Germany, each mission begins with an intense and fully interactive airdrop which leverages the unique SIXAXIS PLAYSTATION&reg;3 controller to give players complete and precise control of how, where, and when they land behind enemy lines. In this free roaming FPS environment, the path a player chooses will dramatically change the way each mission plays out. Medal of Honor Airborne will also feature exceptionally photo-realistic characters, adding to the intensity of the cinematic, story-driven game.</p>

<p><b>NBA STREET</b></p>

<p>The 4th chapter of the NBA STREET series returns with a brand new game engine only possible with the power of the PLAYSTATION&reg;3. The multi-platinum franchise is once again raising the bar with cutting edge graphics and innovative gameplay that puts the ball directly in your hands. With a new animation engine and control system, NBA STREET allows users to create tricks-on-the-fly for the first time in a basketball game. The best of the best in the NBA are rendered with meticulous detail, making true athlete fidelity a reality. NBA Stars will play in new authentic environments that are equally detailed with 360 degrees of view, making it possible to get up close and personal with every move and moment.</p>

<p><b>Need for Speed&trade; Carbon</b></p>

<p>Need for Speed Carbon and the PLAYSTATION&reg;3 introduce the world to a whole new way to play Need for Speed. The battle for Palmont City starts in the streets, but is ultimately won in the canyons as Need for Speed Carbon immerses you in the world's most dangerous and adrenaline-filled forms of street racing. The combination of the classic Need for Speed controls with the new, unique motion sensitive controller of the PLAYSTATION&reg;3 takes the gameplay to a level previously not possible. The player will instantly recognize and feel the physics differences between the 50 plus Muscle, Exotic, and Tuner cars, as they use their crew to win Canyon races, customize their cars using Autosculpt&trade;, and battle to take control of the streets of Palmont. EA is leveraging the high capacity and throughput of the Blu-ray disk to store and stream our highly complex world; something that was becoming increasingly difficult to do on other forms of media.</p>

<p><b>SKATE</b></p>

<p>With innovative controls that take advantage of the PLAYSTATION&reg;3 hardware and the dual analog sticks, SKATE offers a unique and authentic next-gen skateboard videogame experience unparalleled in the skate videogame genre. Featuring physics driven animations made only possible by the power of the PLAYSTATION&reg;3, gamers will have a unique experience every time they pick up the controller since no two tricks will ever be the same. The amount of information the game is able to take from the unique flickit analog controls and interpret it through the physics engine could never be done on a current generation console system. SKATE on PLAYSTATION&reg;3 has the ability to simulate real world physics versus canned animations. From a fully procedural trick engine to the way cloth moves on the skaters, all movement is physically simulated and dynamic offering gamers a skating game that is the closest thing to skateboarding without actually putting their feet on a board.</p>

<p><b>Tiger Woods PGA TOUR&reg; 07</b></p>

<p>Tiger Woods PGA TOUR 07 allows you to compete for The FedExCup, the new PGA TOUR&reg; championship playoff system, against some of the world's best golfers. New golfers in the game include Michael Campbell, Ian Poulter, and Annika Sorenstam. With the new True Aiming system, survey the course layout and weigh the risks of each shot before swinging away using the refined dual analog stick swing system. Develop your drive, chip shots, and putting skills in the new Practice Facility or take on a friend in new mini games including Capture the Flag, Twenty One or Target-to-Target, before unleashing yourself on the PGA TOUR. Tiger Woods PGA TOUR 07 on PLAYSTATION&reg;3 takes full advantage of the new motion-sensor controller for a greater degree of ball spin direction and speed control. Gamers tilt the controller in the direction they wish the ball to spin, the longer the tilt in the direction, the faster the ball will turn for more action on the course and around the green.</p>

<p>To download screenshots from any of these games, please visit info.ea.com.</p>

<p><b>About Electronic Arts</b></p>

<p>Electronic Arts Inc. (EA), headquartered in Redwood City, California, is the world's leading interactive entertainment software company. Founded in 1982, the company develops, publishes, and distributes interactive software worldwide for videogame systems, personal computers and the Internet. Electronic Arts markets its products under four brand names: EA SPORTSTM, EATM, EA SPORTS BIGTM and POGOTM. In fiscal 2006, EA posted revenue of $2.95 billion and had 27 titles that sold more than one million copies. EA's homepage and online game site is www.ea.com. More information about EA's products and full text of press releases can be found on the Internet at http://info.ea.com.</p>

<p>Electronic Arts, EA, EA SPORTS, EA SPORTS BIG, POGO, Need for Speed, AutoSculpt, Army of TWO, Burnout, Medal of Honor Airborne and Battlefield: Bad Company are trademarks or registered trademarks of Electronic Arts Inc. in the U.S and/or other countries. Medal of Honor is a trademark or registered trademark of Electronic Arts Inc. in the U.S. and/or other countries for computer and video game products. Def Jam&reg;, Def Jam Icon&trade;, and all associated trademarks and logos are used under license from DJR Holdings, LLC and Simcoh, LLC. John Madden, NFL, Tiger Woods, PGA TOUR and NBA are trademarks of their respective owners and used with permission. "PLAYSTATION" is a registered trademark of Sony Computer Entertainment Inc. All other trademarks are the property of their respective owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 20, 2006  5:46 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(461)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 461)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php" type="text/javascript" charset="utf-8"></script>
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
