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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3206 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3206
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php&amp;title=New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September">
		<span style="display:none">Sony Computer Entertainment Inc. (SCE) today unveiled the new PlayStation 3 (CECH-2000A) (body color: charcoal black) computer entertainment system, featuring an extremely streamlined form factor with a 120GB Hard Disk Drive (HDD). The new PlayStation 3 (PS3 ) system will become available in stores from...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3206";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" />
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
	<title>HDTV Magazine - New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September</title>
	<meta name="keywords" content="computer entertainment, sony computer, vertical stand, entertainment inc, system software, playstation, system, new, entertainment, computer, sony, vertical, users, inc, stand, software, available, cech, network, further, content, bravia, games, power, hdmi" />
	<meta name="description" content="Sony Computer Entertainment Inc. (SCE) today unveiled the new PlayStation 3 (CECH-2000A) (body color: charcoal black) computer entertainment system, featuring an extremely streamlined form factor with a 120GB Hard Disk Drive (HDD). The new PlayStation 3 (PS3 ) system will become available in stores from..." />
	<meta name="title" content="New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3206', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php">New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 18, 2009</b>
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
				<p class="prtitle">New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September</p>

<center><i>Lower Price to Further Accelerate Expansion of the PlayStation(R)3 Platform Along with Extensive Software Title Line-up for Upcoming Holiday Season</center></i><br />
<br />

<p><strong>TOKYO, Aug. 18 /PRNewswire/</strong> -- Sony Computer Entertainment Inc. (SCE) today unveiled the new PlayStation 3 (CECH-2000A) (body color: charcoal black) computer entertainment system, featuring an extremely streamlined form factor with a 120GB Hard Disk Drive (HDD). The new PlayStation 3 (PS3 ) system will become available in stores from September 1, 2009, in North America, Europe/ PAL territories and Asian countries and regions at a very attractive recommended retail price (RRP) of US$299 and euro 299, respectively. The system will become available in Japan on September 3, 2009, at a RRP of 29,980 yen (including tax). With the introduction of the new PS3 system, SCE will also reduce the price of the current PS3 with 80GB HDD to a RRP of US$299 from August 18 and euro 299 from August 19. Also in North America, the price of PS3 with 160GB HDD will be reduced to a RRP of US$399 from August 18. By launching a vast library of exciting and attractive software titles for PS3 this holiday season and offering customers a line-up of hardware models and pricing to match their preference, SCE will build on the momentum and further accelerate the expansion of the PS3 platform.</p>

<p>The internal design architecture of the new PS3 system, from the main semiconductors and power supply unit to the cooling mechanism, has been completely redesigned, achieving a much slimmer and lighter body. Compared to the very first PS3 model with 60GB HDD, the internal volume as well as its thickness and weight are trimmed down to approximately two-thirds. Furthermore, power consumption is also cut to two-thirds, helping to reduce fan noise. While inheriting the sleek curved body design of the original model, the form factor of the new PS3 system features a new meticulous design with textured surface finish, giving an all new impression and a casual look. With the compact body and casual appearance, the newly introduced model will appeal to a wider audience who are looking to buy the best entertainment system for their home.</p>

<p>Concurrently with the release of the new PS3 system, SCE will modify the PS3 brand name from "PLAYSTATION 3" to "PlayStation 3", and introduce a new "PS3" logo, which is engraved on the surface of the new PS3 system. By unifying under the familiar "PlayStation " name, which represents the entire PlayStation family, PS3 together with PlayStation 2 and PSP (PlayStation Portable) will further expand the PlayStation business, and will continue to enhance the entertainment experience along with the ever-growing PlayStation Network.</p>

<p>The new PS3 continues to offer the cutting-edge features and functions of the current models, such as the ability to enjoy high-definition Blu-ray disc (BD) movies and games, as well as various content and services downloadable through the network. The new PS3's storage size has increased from 80GB to 120GB, and with the extra capacity users will be able to store more games, music, photos, videos as well as various content and services available through PlayStation Network. Having more than 27 million registered accounts around the world, PlayStation Network offers more than 15,000 pieces of digital content, ranging from game titles, trailers, and demos to more than 15,000 movies and TV shows via PlayStation Store(*1). PlayStation Network members can also download free applications, such as PlayStation Home, a ground-breaking 3D social gaming community available on PS3 that allows users to interact, communicate and share gaming experiences, as well as Life with PlayStation, which offers users various news and information on a TV monitor in the living room by connecting the PS3 to the network.</p>

<p>Since the launch of PS3 in November 2006, the number of BD-based titles has reached more than 1,000 titles and downloadable PS3 games to 1,400(*2) titles worldwide, with the support from a broad range of third party game developers and publishers. In addition to this extensive software title line-up, exciting and attractive new titles are to be released from SCE Worldwide Studios, including Uncharted 2: Among Thieves, EyePet, Ratchet & Clank Future: A Crack in Time, Heavy Rain, God of War 3, MAG, ModNation Racer, Gran Turismo 5 and more.</p>

<pre>
Other features of the new PS3 include:

<p>  - PS3 system software update version 3.00</p>

<p>    Concurrently with the release of new PS3, system software will be<br />
    upgraded to version 3.00 on September 1.  The update adds various user-<br />
    friendly features such as the "What's New" screen, where users can<br />
    quickly browse the new items available in PlayStation Store as well as<br />
    their recently played games directly on the XMB(TM) (XrossMediaBar),<br />
    with short cuts to each piece of content.  PS3 will evolve continuously<br />
    with the system software updates, further improving the operability and<br />
    enhancing the user experience available through the network.  PS3 owners<br />
    will be able to enjoy new features by simply updating the PS3 system<br />
    software to version 3.00 via the "System Update" function on the<br />
    XMB(*3).</p>

<p>  - BRAVIA(R) Sync(TM) Feature</p>

<p>    The new PS3 system is also equipped with the BRAVIA(R) Sync(TM) feature.<br />
    By connecting the new PS3 system and a BRAVIA TV with the HDMI cable,<br />
    users are able to directly operate the XMB on PS3 using the TV remote<br />
    control.  Other functions include "System Standby" that will<br />
    automatically turn off the PS3 system when the BRAVIA TV is turned<br />
    off(*4).</p>

<p>  - "Vertical Stand" for new PS3  (CECH-2000 series)</p>

<p>    By utilizing the separately sold "Vertical Stand", users will be able to<br />
    set the new PS3 in vertical position(*5), making it easier to place the<br />
    PS3 system anywhere at home.  The vertical stand will become available<br />
    in Japan on September 3, 2009, at a RRP of 2,000 yen (including tax) and<br />
    in North America at US$24(*6).</p>

<p>  - Removal of "Install Other OS" feature</p>

<p>    The new PS3 system will focus on delivering games and other<br />
    entertainment content, and users will not be able to install other<br />
    Operating Systems to the new PS3 system.<br />
</pre></p>

<p>Along with a vast line-up of attractive and exciting entertainment content with the new PS3 system, SCE will continue to further expand the PS3 platform and create a new world of computer entertainment.<br />
<pre><br />
  *1    Number as of end July 2009.  Content within PlayStation Store will<br />
        differ by region, please refer to the official PlayStation.com site<br />
        for further details.<br />
  *2    Includes PS one(R) classics and free of charge content (downloadable<br />
        demos).<br />
  *3    Users will need to connect their PS3 to the network to use the<br />
        function.<br />
  *4    Users will need to use BRAVIA TV that supports the BRAVIA Sync<br />
        feature.  For further information about BRAVIA Sync, please refer to<br />
        the official Sony site in each region.<br />
  *5    Users will need to use the separately sold "Vertical Stand" to set<br />
        the new PS3 in vertical position.<br />
  *6    Release date of the vertical stand for North America will be<br />
        announced when available.  Release date and price of the vertical<br />
        stand for Europe/ PAL territories and Asian countries and regions<br />
        will be announced when available.</p>

<p><br />
  Product Outline<br />
  PlayStation(R)3 (CECH-2000A)</p>

<p>   Product name                    PlayStation(R)3</p>

<p><br />
   Product code                    CECH-2000A (Charcoal Black)</p>

<p><br />
   CPU                             Cell Broadband Engine(TM)</p>

<p><br />
   GPU                             RSX(R)</p>

<p><br />
   Audio output                    LPCM 7.1ch, Dolby Digital, Dolby Digital<br />
                                    Plus, Dolby TrueHD, DTS, DTS-HD, AAC.</p>

<p><br />
   Memory                          256MB XDR Main RAM, 256MB GDDR3 VRAM</p>

<p><br />
   Hard disk      2.5" Serial ATA  120GB(*1)</p>

<p><br />
   Inputs/        Hi-Speed USB<br />
   Outputs(*2)    (USB 2.0)        2</p>

<p><br />
   Networking                      Ethernet (10BASE-T, 100BASE-TX,<br />
                                    1000BASE-T) x 1</p>

<p>                                   IEEE 802.11 b/g</p>

<p>                                   Bluetooth(R) 2.0 (EDR)</p>

<p><br />
   Controller                      Wireless Controller (Bluetooth(R))</p>

<p><br />
   AV output      Resolution       1080p, 1080i, 720p, 480p, 480i (for PAL<br />
                                    576p, 576i)</p>

<p>                  HDMI OUT<br />
                   connector(*3)   1</p>

<p>                  AV MULTI OUT<br />
                   connector       1</p>

<p>                  Digital out<br />
                   (optical)<br />
                   connector       1</p>

<p><br />
   BD/DVD/CD      Maximum read     BD x 2 (BD-ROM)<br />
    drive (read    rate            DVD x 8 (DVD-ROM)<br />
    only)                          CD x 24 (CD-ROM)</p>

<p><br />
   Power                           AC 220 - 240, 50/60Hz(*4)</p>

<p><br />
   Power consumption               Approx. 250W</p>

<p><br />
   External dimensions             Approx. 290 x 65 x 290 mm (width x height<br />
   (excluding maximum projecting    x length)<br />
    part)</p>

<p><br />
   Mass                            Approx. 3.2kg</p>

<p><br />
   Included (*5)                   PlayStation(R)3 system x 1<br />
                                   Wireless Controller (DUALSHOCK(R)3) x 1<br />
                                   AC power cord x 1<br />
                                   AV cable x 1<br />
                                   USB cable x 1</p>

<p><br />
  *1    Hard disk capacity calculated using base 10 mathematics (1 GB =<br />
        1,000,000,000 bytes). System software versions 1.10 and later<br />
        calculate capacity using binary mathematics (1 GB = 1,073,741,824<br />
        bytes), which will display lower capacity and free space. A portion<br />
        of hard disk capacity is reserved for system administration, which<br />
        varies depending upon system software version, and is not available<br />
        for use.<br />
  *2    Usability of all connected devices is not guaranteed.<br />
  *3    "Deep Colour" and "x.v.Colour (xvYCC)" defined by HDMI ver.1.3a are<br />
        supported.<br />
  *4    Power changes depending on countries or regions.<br />
  *5    For certain regions, Euro-AV cable will be included.<br />
  Note: This product is not compatible with PlayStation(R)2 games.</p>

<p>  New Logo<br />
  "PS3"(TM)<br />
  PlayStation 3</p>

<p><br />
  Vertical Stand (CECH-ZS1)</p>

<p>  Product name              Vertical Stand</p>

<p>  Product code              CECH-ZS1</p>

<p>  Included                  Vertical Stand (CECH-ZS1) x 1</p>

<p>  External dimension        Approx. 88 mm x 18 mm x 260 mm (width x height<br />
                             x length)</p>

<p>  Mass                      Approx. 115g</p>

<p>  Supports                  CECH-2000 series</p>

<p>  *    The "Vertical Stand" is for the new PS3 system (CECH-2000 series) and<br />
       cannot be used on the current model.<br />
</pre></p>

<p><br />
<strong>About Sony Computer Entertainment Inc.</strong></p>

<p>Recognized as the global leader and company responsible for the progression of consumer-based computer entertainment, Sony Computer Entertainment Inc. (SCEI) manufacturers, distributes and markets the PlayStation game console, the PlayStation 2 computer entertainment system, the PSP (PlayStation Portable) handheld entertainment system and the PlayStation 3 (PS3 ) system. PlayStation has revolutionized home entertainment by introducing advanced 3D graphic processing, and PlayStation 2 further enhances the PlayStation legacy as the core of home networked entertainment. PSP is an innovative handheld entertainment system that allows users to enjoy 3D games, with high-quality full-motion video, and high-fidelity stereo audio. PS3 is an advanced computer system, incorporating the state-of-the-art Cell processor with super computer like power. SCEI, along with its subsidiary divisions Sony Computer Entertainment America Inc., Sony Computer Entertainment Europe Ltd., and Sony Computer Entertainment Korea Inc. develops, publishes, markets and distributes software, and manages the third party licensing programs for these platforms in the respective markets worldwide. Headquartered in Tokyo, Japan, Sony Computer Entertainment Inc. is an independent business unit of the Sony Group.</p>

<p>  Dolby is a trademark of Dolby Laboratories.<br />
  DTS is a trademark of Digital Theater Systems, Inc.<br />
  HDMI, HDMI logo and High Definition Multimedia Interface are trademarks of<br />
  HDMI Licensing LLC.<br />
  Blu-ray Disc is a trademark.<br />
  The Bluetooth word mark is a registered trademark owned by Bluetooth SIG,<br />
  Inc. and any use of such marks by Sony Computer Entertainment Inc. is<br />
  under license.<br />
  PlayStation, PLAYSTATION, PS3, RSX, DUALSHOCK and GRAN TURISMO are<br />
  registered trademarks of Sony Computer Entertainment Inc.  Cell Broadband<br />
  Engine is a trademark of Sony Computer Entertainment Inc. All other<br />
  trademarks are property of their respective owners.</p>

<p>Source: Sony Computer Entertainment America</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 18, 2009  8:15 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3206)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3206)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php" type="text/javascript" charset="utf-8"></script>
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
