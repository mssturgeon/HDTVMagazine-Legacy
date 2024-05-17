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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 557 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 557
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=CinemaScope&#8482; HDHT - Part 2&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php&amp;title=CinemaScope&amp;#8482; HDHT - Part 2">
		<span style="display:none">As I mentioned on the first article, I decided to launch this CinemaScope&amp;trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&amp;trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.

Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 557";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CinemaScope&#8482; HDHT - Part 2" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CinemaScope&#8482; HDHT - Part 2" />
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
	<title>HDTV Magazine - CinemaScope&#8482; HDHT - Part 2</title>
	<meta name="keywords" content="aspect ratio, anamorphic lens, home theater, wide screen, top bottom, screen, cinemascope, projector, image, lens, system, resolution, aspect, content, could, masking, project, anamorphic, ratio, installation, home, movie, theater, bars, part" />
	<meta name="description" content="As I mentioned on the first article, I decided to launch this CinemaScope&amp;trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&amp;trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.

Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell..." />
	<meta name="title" content="CinemaScope&amp;#8482; HDHT - Part 2" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=557', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php">CinemaScope&#8482; HDHT - Part 2</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>March  6, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<div class="editorial">The following article is the latest in the CinemaScope&trade; series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php">CinemaScope&#8482; HDHT - Part 1 - The Concept</a></li>
<li><a href="/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
First of all, forgive me for not been able to produce these articles as often as I had planned, I am very occupied with the annual report about HDTV Technology that I produce every year (for 5 years already) at this time and that has priority, but I will try to keep the momentum of these articles as warm as possible.

<p><br />
<B>The CinemaScope&trade; Project</B></p>

<p>As I mentioned on the first article, I decided to launch this CinemaScope&trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.</p>

<p>Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell that the CinemaScope&trade; experience is breathtaking. Building this system was a challenge, but it was worth every penny and every minute invested on it.</p>

<p>The project is not about beautifying a home theater, it is about what is possible with quality electronics and optics to optimize the viewing of wide-screen content and to recreate the CinemaScope&trade; feeling of the classical theater.</p>

<p>It was also about simulating the steps a regular consumer would have to follow to make a similar project for their homes. Regardless if I was capable to install, connect, calibrate, etc., I decided to hire out the necessary labor for each task, rather than shaving costs by doing the job myself. The value of the project to readers was for me to do exactly what a regular consumer would have to do. Due to the fact that I intended to live with the system after it was finished, I designed the system and selected all the components myself. A consumer without such knowledge should expect the dealer/installer of the system to take that role.</p>

<p><br />
<img src="/images/articles/hdht-projector.jpg" alt="Optoma Projector" align="left" /><B>The Projector Took the Driver Seat</B></p>

<p>As you might know already, front-projector manufacturers are doing partnerships with manufacturers of other CinemaScope&trade; components, such as anamorphic lens, transports, plates, etc. They have improved their compatibility, and they are easier to install because they are made with specifications to fit with each other. They are sold by projector manufacturers as CinemaScope&trade; system packages, which make the whole project considerably smoother and cheaper to the consumer, particularly in labor costs. Not to mention the risks one would run by choosing components based on their individual merits that might end up not fitting as smoothly with each other.</p>

<p>This year we will see a growing number of manufacturers offering CinemaScope&trade; lens/transport package solutions bundled with projectors, and offering the lens/plate/transport in a separate package deal for those consumers that have a projector already.</p>

<p><br />
<B>Other Aspect Ratios Displayed on a 2.35:1 Screen</B></p>

<p>As I mentioned in the first article, the CinemaScope&trade; approach I am covering on these articles is not about simply zooming and/or masking 2.35:1 images on a screen. Is about implementing a 2.35:1 screen for predominantly 2.35:1 viewing while maximizing the capabilities of the projector's chip-resolution and light-output. That requires more than just a 2.35:1 screen, it requires as a vertical stretch capable scaler, anamorphic lens, lens transport, transport plate, etc.</p>

<p>Images on aspect ratios that are less wide (16:9, 4:3, for example) would have to be displayed without the anamorphic lens in front of the projector lens, and with side pillars on the wider 2.35:1 screen, but aligned with the top/bottom edges of the screen (reason by which it is called "constant height"). That is, if the original aspect ratio of the incoming image needs not to be altered. Additionally, pillar side-bar masking might be necessary for some viewers that can not tolerate "projected" black pillar bars, which are not as black as good masking material.</p>

<p>The approach could mean that the overall area occupied by a smaller 16:9 image within the 2.35:1 screen could end up unacceptably small to some viewers, compared to a 16:9 screen installation for predominantly 16:9 TV viewing occupying the same width of the 2.35:1 screen approach. Not to mention the further reduced visual impact of 4:3 images that would look even smaller when implemented in constant height 2.35:1 screen.</p>

<p><br />
<B>Limited Wall Width</B></p>

<p>With that in mind, whether the screen is mounted on a wall or coming from the ceiling, if the front viewing area is limited in width, a 2.35:1 screen could be seen as a step backwards regarding overall image size impact for viewers that seldom watch 2.35:1 content. Therefore, one has to decide very carefully what is the primary purpose of the home theater. If the purpose is to mainly watch TV in 4:3 or 16:9 formats and occasionally view a 2.35:1 movie, then the CinemaScope&trade; concept with 2.35:1 screens might not be as appealing as it would be to a 2.35:1 wide-screen movie fan that would not watch TV or smaller aspect ratios as often, or at all, on the home theater.</p>

<p><br />
<img src="/images/articles/hdht-screen.jpg" alt="HDHT Screen" align="right" /><B>Butchering Aspect Ratio</B></p>

<p>Many movie directors do not take lightly when anyone geometrically alters the original aspect ratio (OAR) of their piece of art to fit the image into a display device with different aspect ratio. Aspect ratio is part of the artistic creation and should be respected as is. Some HD movie channels alter (butcher) the original aspect ratio of wide-screen movies to make them fit within the 16:9 frame so they are displayed without letterbox bars. The content distributor thinks that people do not like to see bars, and actually many consumers purchased 16:9 screens under the impression that they would finally get rid of the hatred black bars on their 4:3 sets when viewing 16:9 content. The content distributors were not to far off in that thinking process "for the general mass of TV viewers", but they ignored the OAR loving public.</p>

<p>2.35:1 wide-screen movie content displayed on a 16:9 screen would display with a similar letterbox bar effect of the 16:9 content on the old 4:3 TV set. The approach wastes about 30% of the vertical resolution of the display device used for projecting just black dots, and the projected dots are generally not absolute black when they hit the screen. So how could we use that wasted vertical resolution capability for an image that rather needs it horizontally because is proportionally wider?</p>

<p><br />
<B>Putting All the Resolution Pixels to Good Use</B></p>

<p>A CinemaScope&trade; system could put those vertical pixels to work electronically with a capable scaler, it would stretch the image vertically, everyone would look thin, it would be ideal that someone later in the system path makes them normal again. This is where the anamorphic lens fits into place in the system. When placed in front of the projector lens it produces a similar stretch but now in the horizontal direction, and with optics. Both steps are needed, making first the image taller electronically with a scaler and later wider optically with the anamorphic lens. The combined effect of both actions produce an image that is larger but proportional to the original image when it was sandwiched between the black bars, maintaining the exact same aspect ratio as the original 2.35:1 source.</p>

<p><br />
<B>How Could We Use the System?</B></p>

<p>Many HD movie channels and even broadcast HD movies distribute 2.35:1 movies as they are, with top/bottom bars sandwiched within the 16:9 aspect ratio of the HD signal to maintain the original geometry of the content. Such wide-screen content could certainly display well in a CinemaScope&trade; capable home theater, the scaler and the anamorphic lens would do their job with that content as well, in other words, this is not about wide-screen [HiDef]DVDs pre-recorded movies only.</p>

<p><br />
<B>Does Resolution Matter in CinemaScope?</B></p>

<p>Having a 1080p projector rather than a lower resolution projector always helps on larger screens, but a 720p projector could also be used to implement CinemaScope&trade;. However, a 720p image barely has 1 million pixels of spatial resolution (720x1280= 921,600 pixels) displayed on each frame of content, while a 1080p projector more than doubles that (1080x1920=2,073,600 pixels). Regardless of the frame rate and the original resolution of the content, the above will be the spatial resolution outputted by the projector's chip.</p>

<p>If the primary purpose of a HT is to show CinemaScope&trade; movies, the spatial resolution factor is more important than the classical benefits of the faster temporal resolution of the 720p format, 60fps frame rate are well suited to fast sports, such as ESPN HD, but the horizontal line has only 1280 pixels, rather than 1920.</p>

<p>A non-1080p resolution projector could still do a decent job as long as the increased width of the horizontally larger screen (for an image that is horizontally expanded by the anamorphic lens) is not pushed beyond reasonable limits. Going too large might negatively affect the image quality for the particular viewing distance.</p>

<p>There are many more areas of discussion in the resolution subject, such as the ability to handle 24fps 1080p content originating from film stored in HD DVD and Blu-ray. There are projectors that are capable to accept 1080p at 24fps and can display it at multiples of that frame rate, without doing 2:3 pull-down to go to the 60i interlaced world to later de-interlace it to 60p. One should always avoid any unnecessary video processing and conversions to let the scaler to do its CinemaScope&trade; vertical stretch with a signal as clean as possible to avoid compound artifacts.</p>

<p><br />
<B>2.35:1 Screens, How People Use Them </B></p>

<p>1080p projectors are now available in good variety, as well as good quality anamorphic lenses/transports; scalers with vertical stretch ability for constant height installation are also available, all relatively affordable. 2.35:1 movie fans might see an opportunity to jump out of the 16:9 HD aspect ratio bandwagon and into the 2.35:1 CinemaScope&trade; style, to watch their movies on a screen with the same aspect ratio as the movie.</p>

<p>However, installing a 2.35:1 screen is only part of the solution, as we discussed earlier, a scaler has to stretch the image vertically to get rid of the top/bottom black bars and let the projector's chip use its full vertical resolution (1080 or 720), which means more projected light from 30% more pixels, then the anamorphic lens stretches the image horizontally.</p>

<p>Not doing the full combination of the steps above, and performing only manual adjustments like zooming, refocusing, masking, to push the top/bottom letterbox bars out of the 2.35:1 screen frame, even if they are covered with masking material, potentially looses precious projector chip resolution and light output. That approach is used by many home theater enthusiasts and is not within the scope of these articles. There is a cost/benefit on each solution, each person has to decide what is the best solution for their particular installation, quality, flexibility, easy of operation, and pocket. In theory they both need a capable projector and a 2.35:1 screen to start with, those two pieces could be reused when moving from one approach to the other.</p>

<p><br />
<img src="/images/articles/hdht-construction.jpg" alt="Construction" align="left" /><B>The Perfect Storm Team</B></p>

<p>I would like to thank all the companies that participated on this CinemaScope&trade; project for their collaboration and personal efforts:</p>

<p>Wing Chung, Director of Project Engineering, Optoma (DLP projector/scaler)<br />
Shawn Kelly, President Panamorph (anamorphic lens, mounting plate)<br />
Bob Yellin, President Z-bott LLC (anamorphic lens transport)<br />
Don Newquist, Principal HTIQ (masking/curtains HT system)<br />
Mark Haflich, Owner Soundworks (projector, screen, etc. dealer/installer)<br />
Chuck Williams, ISF calibrator<br />
Jose Granados, Owner Granados Co., (installation masking/curtains rail system)</p>

<p>When we started a couple of months ago, some of the products were still in a prototype stage or just released, and the installation and calibration efforts required creativity, breaking new ground to make things work. But we all capitalized from this learning experience in one way or another.</p>

<p>Regarding the installation in particular, I would like to thank Mark Haflich, owner of Soundworks in Kensington MD, for his valuable support and the professional dedication of his installation crew, who worked several days until very late and with whom I had the pleasure to share many inventive moments, as well as the nightmares, and the satisfaction of seeing some pieces fit under the powers of creativity, often without the benefit of instruction manuals or suitable installation hardware.</p>

<p>Shawn (Panamorph) and Wing (Optoma) have both been excellent team experts that facilitated their insider knowledge for the optimal harmonization of products, even those that their companies did not have in production yet.</p>

<p>I am still working with the final HT phases with Don (htiq, home theater electric masking/curtains, etc), regarding the pillar masking and motorized curtains for 4:3 and 16:9 viewing in the constant height system. He has been an excellent collaborator in making my curtain/masking system design possible. His expert advice in that area of the project has been very valuable.</p>

<p>It is important to note that I am not doing curtains and masking just to beautify the HT installation, but to primarily improve the perception of the image by the eyes. Image quality was and still is one of the main drivers of this project.</p>

<p><br />
<B>What is Next</B></p>

<p>In the next installments I will cover each of the areas about the projector, lens, transport, plate, procurement, installation, calibration, masking system, etc. CinemaScope&trade; at the consumer's home is warming up very quickly in 2007. It is possible, affordable, and breathtaking, and we could do a great service to our readers by providing them with a real case scenario to successfully repeat at their home.</p>

<p>Stay tuned for Part 3</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>March  6, 2007 11:52 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(557)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 557)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php" type="text/javascript" charset="utf-8"></script>
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
