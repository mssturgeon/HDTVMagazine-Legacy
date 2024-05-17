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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 558 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 558
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=LG BH100 Universal HD DVD/Blu-ray Player&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/reviews/2007/03/lg-bh100-universal-hd-dvdbluray-player.php&amp;title=LG BH100 Universal HD DVD/Blu-ray Player">
		<span style="display:none">One of the biggest complaints with the Blu-ray and HD DVD disc format war is the need to buy separate players for each format. If HD and film is your passion you have a difficult choice because not all movies will be released in both formats. A large portion of the potential &quot;high definition disc&quot; consumers are waiting for one of two things before investing their hard-earned dollars in another format: 1) a clear winner in the format war, or 2) a universal player.  When LG unveiled their universal player at the 2007 Consumer Electronics Show (CES)...</span></a>
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

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 558";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG BH100 Universal HD DVD/Blu-ray Player" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG BH100 Universal HD DVD/Blu-ray Player" />
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
	<title>HDTV Magazine - LG BH100 Universal HD DVD/Blu-ray Player</title>
	<meta name="keywords" content="via hdmi, blu ray, analog video, full meal, meal deal, dvd, video, performance, audio, disc, player, via, hdmi, features, analog, support, application, blu, ray, toshiba, players, deal, both, full, well" />
	<meta name="description" content="One of the biggest complaints with the Blu-ray and HD DVD disc format war is the need to buy separate players for each format. If HD and film is your passion you have a difficult choice because not all movies will be released in both formats. A large portion of the potential &quot;high definition disc&quot; consumers are waiting for one of two things before investing their hard-earned dollars in another format: 1) a clear winner in the format war, or 2) a universal player.  When LG unveiled their universal player at the 2007 Consumer Electronics Show (CES)..." />
	<meta name="title" content="LG BH100 Universal HD DVD/Blu-ray Player" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/reviews/2007/03/lg-bh100-universal-hd-dvdbluray-player.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=558', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/03/lg-bh100-universal-hd-dvdbluray-player.php">LG BH100 Universal HD DVD/Blu-ray Player</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>March  8, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=281&category=Blu-ray Players">Blu-ray Players</a></b>, <b><a href="/category.php?id=360&category=HD DVD Players">HD DVD Players</a></b>
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
				<p><img src="/images/products/lg-bh100.jpg" alt="LG BH100" /><br /></p>

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid">$1,199.00</td>
<td class="greygrid"><a href="/equipment/model.php?man=LG%20Electronics&model=BH100">$1,074.99</a></td>
<td class="greygrid"><a href="http://www.amazon.com/gp/redirect.html?ie=UTF8&location=http%3A%2F%2Fwww.amazon.com%2FLG-BH100-Blu-Ray-disc-player%2Fdp%2FB000NNK9LY%3Fie%3DUTF8%26s%3Delectronics%26qid%3D1173364182%26sr%3D8-1&tag=hdtvmagazine-20&linkCode=ur2&camp=1789&creative=9325">$1,199.00</a></td></tr>
</table>
<br />
Serial# 701KVDT070385<br />
Warranty: 1 year parts, 90 days labor<br /><br /><br /><b>Summary: Some are going to love it and for others it will not be enough</b><br /><br />One of the biggest complaints with the Blu-ray and HD DVD disc format war is the need to buy separate players for each format. If HD and film is your passion you have a difficult choice because not all movies will be released in both formats. A large portion of the potential "high definition disc" consumers are waiting for one of two things before investing their hard-earned dollars in another format: 1) a clear winner in the format war, or 2) a universal player.  When LG unveiled their universal player at the 2007 Consumer Electronics Show (CES) it was an immediate marketing hit that was tempered by some excluded features and also a past history for not meeting video standards. A player was sent to me for testing by <a href="/cgi-bin/ntlinktrack.cgi?http://www.customht.net">Custom HT</a> for 1 week. This allowed just enough time to check operational and main performance issues. Let's see how well LG faired.

<p><strong>Features</strong></p>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://us.lge.com/download/product/file/1000002028/BH100.pdf">LG BH100 Brochure</a></p>

<p>The unit has all the standard connection types. Like others, you do have analog video support up to 1080i for either HD disc format.</p>

<p><strong>Noteworthy Features</strong></p>

<ul>
<li>1080p24 support!</li>
<li>The remote looked nice and felt natural in my hand, although it does not have a back lit feature for the darkened home theater. </li>
<li>The player color theme is black with a black brushed aluminum top and front panel with glossy plastic side panels. </li>
<li>There are five buttons on the top panel for common commands such as power, play and tray open/close that won't be available in a typical rack of equipment. Those same buttons are on the remote so access is not actually required. That does not change the fact that you would have to have the remote in hand to perform those functions when you walk up to the player and for some that may be an aggravation so plan accordingly. </li>
<li>Speaking of looks the marketing division did a great job on form and fashion for this box. If the player is placed in an open environment it provides a look of elegance and charm to your decor.</li>
</ul>

<p><strong>Features Missing</strong></p>

<ul>
<li>The unit does not carry an HD DVD logo because it does not fully support the format. This is obvious when you put an HD DVD in because rather than get the standard FBI warning and MPAA screens you will get a screen telling you to check the jacket instead and then the movie begins to play. You will never see a disc menu and this also means you will not have access to any special features on the disc or the new HD DVD feature of related materials from the internet. The only HD DVD support provided is playback of the movie itself and nothing more.</li>

<p><li>Unlike most upconverting players that allow 480p, it will not allow anything greater than 480i via analog component video for SD DVD. </li></p>

<p><li>There is no aspect ratio feature for SD DVD letterboxed movies.</li></p>

<p><li>There are no video adjustments to correct for internal or external errors.</li></p>

<p><li>While I did not check the manual, I was unable to find a feature or command in the setup menu to check or upgrade the firmware via the ethernet connection. The connection is labeled for service only, yet by hooking up a remote Wireless G adapter, the player did trigger a communications sequence as if it was an Ethernet port.</li><br />
</ul></p>

<p><strong>HD Disc Testing Notification</strong></p>

<p>At this time there are no commercially available calibration discs for these formats, although DVE has an HD DVD version due for release soon. Without one, it is impossible to provide objective results or know in absolute terms the performance level of the product. The following subjective HD disc observations were made viewing selected scenes from <i>X-Men: The Last Stand</i> on Blu-ray and <i>King Kong</i> on HD DVD via a calibrated system and direct comparisons to the Sony PS3 and Toshiba HD-A1 players. Bear in mind that beyond the disc technology and size of bit stream, both formats use the same video and audio codecs so however Blu-ray responds, so should HD DVD and vice versa.</p>

<p><strong>HD Audio</strong></p>

<p>The desired approach is either PCM multichannel via HDMI or the future capability of HD audio bitstreams via HDMI directly to your A/V receiver coming this year, just as we do now with SD audio.</p>

<p>The only way to hear any of the HD audio codecs is via the 5.1 analog audio outputs. This capability and performance was not tested, although from experience it will not differ greatly from other products from which you can choose. Adjustments are minimal, covering only speaker size and quantity. While the spec sheet claims PCM support, it is not referring to HD audio PCM multichannel via HDMI. I suspect the PCM relates to standard stereo 24/96 that can be sent over SD digital audio connections although I did not have time to test that. Stereo 24/96 PCM was supported via HDMI as well as the standard DTS and Dolby Digital SD codecs.</p>

<p><strong>Analog Video Connection</strong></p>

<p>To my surprise, the player limits SD DVD to 480i allowing 480p, 720p and 1080i for HD disc content only. That said, SD DVD at 480i met video standards and the rest appeared to meet video standards with HD disc. Why 480p is not allowed for SD DVD is curious. Limiting SD DVD to 480i could make or break the use of this player since most displays don't do very well upconverting that scan rate, hence the preference for 480p at minimum to give SD DVD performance playback a fighting chance. 480i analog video is a difficult scan rate to objectively evaluate without a native 480i display or scope. The key spec error here was resolution as both the color and luminance bursts showed a prominent roll off in response which softens the image reducing detail. With the test material of real video content the image was clearly off from the reference yet on the other hand there were no obvious artifacts to complain about which is the key concern for most. In today's world, native 480i analog video has little relevance to inspire me to fully test this feature to determine if the errors were from the scaler of the display or from the player. With HD disc the player appeared to perform just as well as the Toshiba set for a 1080i output.</p>

<p><strong>DVI Connection</strong></p>

<p>From a performance standpoint what we really want is HDMI YPbPr because that is what is on the disc. For DVI applications this signal has to be converted to RGB, hence the potential for errors with video standards. The product failed video standards for RGB DVI. While I was able to compensate for this by turning down the contrast and increasing the brightness using a DVE calibration disc for reference, I could not address an obvious clipping at peak white which was missing some steps, nor could I address a color error. The fact that the player had no video adjustments did not help and could be problematic since it will not match products that do provide correct video standards for consumer video DVI. Color is slightly off in this mode with some red push. I did do a performance check with SD DVD, HD DVD and Blu-ray noting nothing unusual in a general sense but that does not change the fact that analog video or HDMI looked better, as they should since they get it right. This type of error would also require just the right scene to show it so I look forward to the DVE HD DVD for just this reason. Since I don't have that, it is reasonable to assume based on the HDMI and analog video tests that this error is carried to the HD disc side as well.</p>

<p><strong>HDMI Connection</strong></p>

<p>The product did meet video standards with the HDMI YPbPr connection using the DVE disc. The color response was ever so slightly off, so while it is not a reference for studio mastering, it was clearly close enough for consumer performance applications and most would be hard pressed to detect an error with a reference. The top of the image showed 5 pixels cropped. One point to cover is 1080p support. In the setup menu your choice is 1080p, no frame rates. The LG output 24 frames providing no means to change it. During the menu and movie the display remained in 1080p24 mode. Per LG, the product supports both 24 and 30 frame output depending on the source as contained on the disc. Since my displays accept 1080p24 I had no way to confirm that nor compatibility with 1080p60 based displays.</p>

<p><strong>SD DVD Performance via HDMI</strong></p>

<p>In the past LG has had some major problems with their upconverting players and I am pleased to say that this one did a great job via HDMI. That said, it is odd that SD DVD is limited to 1080i and you are taking a performance hit due to that with a 1080p capable display. The Oppo DV981 (currently under review) had the edge for those seeking SD DVD performance at close viewing distances with 1080p capable displays.</p>

<p><strong>HD DVD and Blu-ray Performance via HDMI</strong></p>

<p>Both were simply spectacular using a 1080p24 output to a 1080p24 capable display. With a properly designed player, correct video setup and 1X1 pixel mapping with a quality display you have an untouched hi fidelity signal from the disc bit stream to the final imager of your display and spectacular is to be expected. Fascinating was a direct comparison to a Sony PS3 set for 1080p60 yielding comparable results taking me to the land of nitpicky to resolve a difference with the LG getting a slight edge on detail performance and overall clarity. Clearly the Sony is processing the original 1080p24 bitstream for 2/3 film pulldown for a 1080p60 output.</p>

<p><strong>Conclusion</strong></p>

<p>For performance enthusiasts using current technology the "full meal deal" is true 1080p24 output directly from the disc bit stream for HD disc that adheres to video standards, 1080p24 or 1080p60 for SD DVD upconversion, and HD audio multichannel PCM supporting all HD audio codecs via HDMI or an HD audio bit stream via HDMI for a receiver equipped with the HD audio codecs.</p>

<p>The LG appears to do video imaging science with all three disc formats via HDMI which is a significant achievement for LG. Unfortunately the LG will not be providing the "full meal deal" on a number of levels and for some that will be a deal breaker. SD DVD upconversion is limited to 1080i, lacks HD audio bitstream or multichannel PCM support via HDMI and you can only play HD DVD movie content, no special features. With analog component video, you have the SD DVD limitation of 480i. With DVI you have a potential compatibility problem with other DVI sources and other video errors that cannot be corrected.</p>

<p><strong>Putting it in Perspective</strong></p>

<p>The following nearly two plus pages would not be required if the LG had delivered the performance "full meal deal". The perspective would be quite simple. If you can live without the HD DVD special features, buy it! But the LG didn't and this causes the reviewer and potential buyer to spend time evaluating applications where the product can do well. These days that has become so much more complex due to the of myriad scan rates, connection types, differing formats and features directly compared to the performance level desired by reader.</p>

<p>It is difficult to recommend this player with a DVI input application due to the obvious errors and those that cannot be corrected. The clipping of peak white on this player will require a solution from LG if they even want to address it in a world that has gone HDMI as the 1080p performance standard. While it could arguably work, considering there is no other all in one box alternative at this time, there are other options to consider that will be revealed as I continue. If you have a DVI input then 1080p24 and 1080p60 is likely not even a concern in which case any of the lower budget alternatives will suffice.</p>

<p>HD audio via digital bitstream or PCM multichannel is preferred over an analog multichannel input and player D/A conversion. That said, you can get all the benefits of HD audio minus the sonic signature of your analog multichannel input; most will have a sonic signature degrading sound quality. Nonetheless, that is a huge step in the right audio direction with clear sonic benefits over SD DVD. Like all performance issues only you can draw the line and choose chocolate or vanilla HD audio or even wait for strawberry, HD audio bitstream sources and A/V receivers with HD audio codecs.</p>

<p>My upstairs application represents the mass market. It is a family multimedia room using a native 720p display that performs quite well with either HDMI or analog video at about 5 screen heights with a PC stereo audio system for sound. SD DVD via any of the Oppo products creates a perception of near HD video quality. The LG dovetails into this application with ease via HDMI and the only loss I incur is the lack of special features with HD DVD. In this application the LG could replace the Oppo for SD DVD which would be a good thing considering the limited space I have. Is it worth it? If you can see a difference between SD DVD and HD content then it certainly has value and the LG is one choice for getting both HD disc formats to your screen plus good SD DVD upconversion. Via analog component is another matter for SD DVD and in that case the Oppo would have to stay which, for some will defeat the purpose of having it all in one box.</p>

<p>Over a year ago my downstairs application was a native 480p/1080i CRT RP limited to analog video only, representing the legacy market of older HDTV displays, with a variable viewing distance range of 3-5 screen heights. The LG dovetails into this application with ease as well. SD DVD video is another matter since the player is stuck at 480i in this application. For the most part, that is of no concern as SD DVD playback over component was likely resolved years ago using either a good 480p DVD player or external scaling. If not you can pick up good 480p DVD players for under $80. I also have the room for another box so no matter.</p>

<p>Currently the downstairs application is native 720p or 1080p front projection on a 10 foot wide 2.35 aspect screen at 2.8 screen heights using anamorphic zoom representing the high performance market. Top notch video performance is required in this system due to the close viewing distance making artifacts as plain as day. Performance audio is desired. There are three Blu-ray players currently on the market providing the "full meal deal" for about $1000. For this application the LG falls short. Such a system starts at about $10,000 for everything so what's another $1000 for the "full meal deal" using separate HD DVD and Blu-ray players? Surely one of those is going to get SD DVD upconversion right with a 1080p output as well. The more you are spending on the system the more sense separate players make!</p>

<p>The above comment dwells in an HD DVD "full meal deal" fantasy though because there is none. HD DVD has not received the manufacturer support that Blu-ray did from numerous companies providing choice in the market place. At this time the LG is the only HD DVD player providing a 1080p24 output yet lacks digital HD audio support and HD DVD special features. There is the Xbox 360 doing 1080p60 via analog video with no HD audio support whatsoever or SD upconversion. We have 1st generation Toshiba players with both digital and analog HD audio support limited to 1080i for SD or HD and now 2nd generation Toshiba players with both digital and analog HD audio support limited to 1080p60. There is no HD DVD "full meal deal" out there regardless of price! While the rumor mill claims the new Toshiba HDXA2 will get 1080p24 support via firmware upgrade, Toshiba has yet to provide an official press release confirming this, nor provide any official comment to repeated inquiries by Senior Technical Director of HDTV Magazine, Rodolfo La Maestra, during the 2007 CES and again after CES.</p>

<p>Let us not forget though that the difference between my Sony PS3 at 1080p60 versus the LG at 1080p24 was marginal enough that the 2nd generation Toshiba 1080p60 player remains a viable option considering you will get the HD audio and HD DVD features which in my opinion gets you the closest to the "full meal deal". That conclusion is based on proper 60 frame conversion just like the Sony did! Unfortunately, current HD DVD players leave the performance enthusiast with only pros and cons options.</p>

<p>The above perspective dwells in performance and two machines running about $1000 each so what about those simply looking for better at an affordable price? The LG still has some stiff competition. The Sony PS3 tops out at $600, has full Blu-ray support and in March will be upgraded to 1080p24 output and SD DVD upconversion per press releases. Currently the PS3 is the least expensive Blu-ray player available and based on testing so far that upgrade will make the PS3 the least expensive route to a reference Blu-ray player as well while providing so much more as a gaming system and media center. Mate that with a 1st or 2nd generation Toshiba player at about $500. Then there is the Xbox 360 for $500-600 providing the same gaming and media center advantages as a PS3. For $1000-1200 you have both HD disc and HD console gaming formats covered comparable to the hit and miss of the LG at a similar price range with arguably far more benefits. A PS3 and any of the Toshiba players will get you more for your buck as well, again at about the same price. That leads me to speculating on the Toshiba firmware upgrade. If that should follow through then you are looking at a PS3 for about $600 and the Toshiba for $1000 which would give you the HD disc "full meal deal" for only $400 more than the LG! There was also a press release just prior to publishing from Sony that mid 2007 they will be releasing the BDPS300 Blu-ray player providing the "full meal deal" for $600 breaking the $1000 price point for a conventional performance player. If you are game console adverse or wondering how a PS3 is going to fit into a rack or stack of equipment, another $600 option form Sony is on the horizon.</p>

<p>This new ability to upgrade firmware to fix problems or add features for consumer products has both pros and cons. Historically, firmware upgrades have addressed compatibility problems, not performance or features. This is a new world for consumer entertainment products where many folks are bound to make a purchase based on future promises related to features and performance. I cannot stress enough that if you do so, make sure such promises have been made with a public press release and not the rumor mill. A firmware upgrade related to performance or features can make the conclusion of this review inaccurate, such as providing SD DVD 1080p60 output via HDMI, 480p output via analog video, fix the DVI setup or make HD audio via HDMI suddenly work. All of those together would make the BH100 a hit for any application where the owner is willing to forgo the HD DVD special features. That is the pro side of the equation. The con side of the equation, and what concerns me and the crew of performance enthusiasts out there, is manufacturers using this ability as a substitute for spending the time upfront to release a product that does video standards with all intended features already there. Using the LG as an example, if a firmware upgrade can correct video problems and add missing features later then why wasn't that done prior to release? Personally, if it would have supported HD audio via HDMI now I think I would have bought one! The reason I am making such a deal over this is because we have already had this experience with 1st generation Toshiba HD DVD players and now the PS3. Toshiba never provided a public press release of promised upgrades and fixes yet had all the motivation required to do so at the time in this format war, which they did.  That should not be mistaken as something they will voluntarily do on future product and note the DVI RGB still has a black level error that you have to compensate for and this is not expected to ever be fixed. Why PS3 owners who bought in November and December of 2006 have to wait until March 2007 for 1080p24 support and SD DVD upconversion is curious but in this case Sony did provide a public press release. Due to that the PS3 review has been put on hold as both of those features will expand the application of the product.</p>

<p><strong>Final</strong></p>

<p>The LG is pretty and sleek providing better performance than expected in some areas while being limited in others. Products like that still have an application and for the LG BH100 there are a number it can fill. Is HD disc 1080p24 video your priority? LG delivers. Looking for better performance in a convenient and space saving one box solution for three disc players? LG has that sexy box for you. Want both Blu-ray and HD DVD capability for less? LG provides an option. The one application it can't fill is the HD and SD disc total performance package in one box and those seeking it are going to have to wait or give up on the one box solution by choosing other products and alternatives.</p>

<p><strong>1 Week Disclaimer</strong></p>

<p>I had the BH100 for one very busy week only. With other reviews I get 2-3 months to actually live with a product which can easily change a short term conclusion and application perspectives.</p>

<p><strong>Links</strong></p>

<p><a href="/forum/viewtopic.php?t=6873">Toshiba HD-XA2 - will it or will it not support 1080p24?</a></p>

<p><a href="/forum/viewtopic.php?t=6868">What is your "High Definition DVD" position?</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>March  8, 2007  8:24 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(558)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 558)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/03/lg-bh100-universal-hd-dvdbluray-player.php" type="text/javascript" charset="utf-8"></script>
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
