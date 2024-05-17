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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3414 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3414
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2010 International Consumers Electronics Show (CES) - New York Press Preview&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php&amp;title=2010 International Consumers Electronics Show (CES) - New York Press Preview">
		<span style="display:none">This event by the Consumers Electronics Association was held on November 10 in New York City to show to the press a preview of the new products and planned events that will take place at the 2010 International CES in Las Vegas on January 7- 10, 2010.

Steve Koenig, CEA’s Director of Industry Analysis, and Shawn DuBravac, CFA, CEA’s Chief Economist and Director of Research disclosed their analysis of the holiday outlook for consumer electronics sales and technology trends for CES 2010, introduced some new hot products, and announced the best of Innovation Honorees, mentioned in a link below. Several exhibitors with tabletop displays introduced new products in advance of their official debuts at the actual CES in January.

The expectation for the holidays is...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3414";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2010 International Consumers Electronics Show (CES) - New York Press Preview" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2010 International Consumers Electronics Show (CES) - New York Press Preview" />
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
	<title>HDTV Magazine - 2010 International Consumers Electronics Show (CES) - New York Press Preview</title>
	<meta name="keywords" content="blu ray, new york, actual ces, ces show, took place, ces, new, show, hdtv, press, products, expect, content, cea, standard, player, electronics, million, jvc, ”, projector, article, blu, ray, technology" />
	<meta name="description" content="This event by the Consumers Electronics Association was held on November 10 in New York City to show to the press a preview of the new products and planned events that will take place at the 2010 International CES in Las Vegas on January 7- 10, 2010.

Steve Koenig, CEA’s Director of Industry Analysis, and Shawn DuBravac, CFA, CEA’s Chief Economist and Director of Research disclosed their analysis of the holiday outlook for consumer electronics sales and technology trends for CES 2010, introduced some new hot products, and announced the best of Innovation Honorees, mentioned in a link below. Several exhibitors with tabletop displays introduced new products in advance of their official debuts at the actual CES in January.

The expectation for the holidays is..." />
	<meta name="title" content="2010 International Consumers Electronics Show (CES) - New York Press Preview" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3414', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php">2010 International Consumers Electronics Show (CES) - New York Press Preview</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December  8, 2009</b>
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
				<p>This event by the Consumers Electronics Association was held on November 10 in New York City to show to the press a preview of the new products and planned events that will take place at the 2010 International CES in Las Vegas on January 7- 10, 2010. <p>Although most of the products shown at this show were not relevant to HDTV, it was interesting to see that some excellent products that were relevant, such as the LCoS JVC projector (top of the line model DLA-HD990, $10K MSRP), got an Innovation Honoree award. <p>Steve Koenig, CEA’s Director of Industry Analysis, and Shawn DuBravac, CFA, CEA’s Chief Economist and Director of Research disclosed their analysis of the holiday outlook for consumer electronics sales and technology trends for CES 2010, introduced some new hot products, and announced the best of Innovation Honorees, mentioned in a link below. Several exhibitors with tabletop displays introduced new products in advance of their official debuts at the actual CES in January. <p>The expectation for the holidays is that based on the pooled consumers (n=344 US adults) they will cut back in expenditures in 2009 compared to 2008 under the reason of earning less money, with a -3% change of total holiday dollar spending compared to 2008. 60% of CE insiders believe that the # 1 challenge is “<i>People not wanting to spend as much as they have in the past”</i> in electronics retailers this holiday season, while the 2<sup>nd</sup> biggest challenge is “<i>Less people coming into the store.</i>” <p>Based on a random national sample of 1,002 U.S. adults: A +8% of allocation of money has been projected for 2009 CE gifts compared to 2008. The notebook/laptop PC item still is the # 1 product on the “CE gift wish list for adults” (same as 2008). The TV was 2<sup>nd</sup> on the list in 2008 but the portable MP3/digital media player took its place in 2009, and a flat panel TVs is 3<sup>rd</sup>in 2009. Kindle/E-reader, iPhone, and Blu-Ray players are new in the “CE gift wish list for adults” in 2009, appearing as sixth, seventh, and eight places. <p>Bundling of products is growing for the 2009 holiday such as TVs bundled with a home theater system or a Blu-ray player. A # 1 trend to watch at CES 2010 is “<i>Beyond HD: Tomorrow’s TV Experience Connected displays deliver a new interactive environment with access to content, movies, music, widgets and more. 3-D TV goes mainstream.”</i> according to the CEA. <p>Over 4 million 3D TVs were forecasted to be shipped in 2010, increasing 1 million per year on the forecast up to 2013 (with 7+ million units), while 2009 registered 2+ million, and 2008 0.5 million. Seventeen percent of adults reported to have seen 3D at the theaters in the last 12 months. <p>CEA said, “The first CES took place in New York City in June of 1967, with 250 exhibitors and 17,500 attendees. Since then, the International CES has grown more than eight-fold.” CEA added at the show, “The 2010 CES continues to gain momentum, with strong sales and a record number of more than 330 new exhibitors. We are updating our projections for the 2010 show based on momentum in exhibit sales and pre-registration numbers. We expect the 2010 CES to draw more than 110,000 attendees from around the world and to feature more than 2,500 exhibitors.” <p><u>Product Debut at CES (source CEA)</u> <ul> <li>1970 Videocassette Recorder (VCR)  <li>1974 Laserdisc Player  <li>1981 Camcorder  <li>1981 Compact Disc Player  <li>1990 Digital Audio Technology  <li>1991 Compact Disc - Interactive  <li>1994 Digital Satellite System (DSS)  <li>1996 Digital Versatile Disc (DVD)  <li>1998 High Definition Television (HDTV)  <li>1999 Hard-disc VCR (PVR)  <li>2000 Satellite Radio  <li>2001 Microsoft Xbox  <li>2001 Plasma TV  <li>2002 Home Media Server  <li>2003 Blu-ray DVD  <li>2003 HDTV DVR  <li>2004 HD Radio  <li>2005 IPTV  <li>2007 New convergence of content and technology 2008 OLED TV  <li>2009 3D HDTV </li></ul> <p>This Pre-show event in NY of the actual 2010 CES in Vegas was a 22hr long day for me. The train from DC relaxed the effort and facilitated time for writing. When I was drafting this article in the train back to DC, I was also drafting four other articles that were published before this one. A few days later, I returned to this draft and noticed that other publications covered this event quite well already. Therefore, I decided to redraft, offer the links to those sources, and rather complete this article with my view about the interesting things I expect to see at CES 2010 regarding HD and 3D, not mentioned in detail on other publications.  <p>Some of the articles that covered the CES 2010 press show are as follows: <p><a href="http://www.displaydaily.com/">http://www.displaydaily.com/</a> <p><a href="http://www.dealerscope.com/article/a-roundup-news-announced-ces-press-preview-event-new-york-tuesday/1?sponsor=newsletter/today">http://www.dealerscope.com/article/a-roundup-news-announced-ces-press-preview-event-new-york-tuesday/1?sponsor=newsletter/today</a> <p><a href="http://cesweb.org/news/upToTheMinute/111109.asp?edm=uttm111009#3530">http://cesweb.org/news/upToTheMinute/111109.asp?edm=uttm111009#3530</a> <p><a href="http://www.twice.com/article/388448-CEA_Unveils_Best_Of_Innovations_Award_Winners.php?nid=2402&amp;source=link&amp;rid=5380669">http://www.twice.com/article/388448-CEA_Unveils_Best_Of_Innovations_Award_Winners.php?nid=2402&amp;source=link&amp;rid=5380669</a> <p><a href="http://www.twice.com/article/388428-CEA_Highlights_CES_10_Features_Changes.php?nid=2402&amp;source=title&amp;rid=5380669">http://www.twice.com/article/388428-CEA_Highlights_CES_10_Features_Changes.php?nid=2402&amp;source=title&amp;rid=5380669</a> <p><a href="http://www.dealerscope.com/slideshow/highlights-from-ces-unveiled?sponsor=newsletter/today#0">http://www.dealerscope.com/slideshow/highlights-from-ces-unveiled?sponsor=newsletter/today#0</a> <p>CES Innovations Honorees: <p><a href="http://www.cesweb.org/awards/innovations/2010honorees.asp?category=931350">http://www.cesweb.org/awards/innovations/2010honorees.asp?category=931350</a> <p>Although the actual CES show is from the seventh to the 10<sup>th</sup>of January, the <a href="http://www.cesweb.org/press/events/default.asp">fifth and the sixth of January are reserved for pre-show events for the press</a>, which I usually attend as well. The sixth is a day when many important companies such a Panasonic, LG, Sharp, Pioneer, etc. each offer a consolidated hour to the press to unveil the products they will introduce during the following four days when CES opens. Although it is a busy full day for the press with back-to-back meetings, the primer usually helps me to be more efficient at the booths and meetings during the rest of the show. <p>As I mentioned above, the press pre-CES show of November 10 announced the top-of-the-line JVC projector to receive the Innovations Honoree award in the video products group, here are some details on the projector: <p><a href="http://admin.virtualpressoffice.com/Presenter?urlId=1&amp;deliveryid=1258031094453">http://admin.virtualpressoffice.com/Presenter?urlId=1&amp;deliveryid=1258031094453</a> <p>Company representatives at the show said JVC would also demo their 4K projector at CES in tandem with another 4K projector for a 3D presentation. JVC declared no plans for 3D 1080p consumer projectors, a statement issued also at the recent <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">HD World Conference</a> I attended on October 15 in NY City. Perhaps CES 2010 will give a surprise announcement.  <p>Although neither Panasonic nor Sony made announcements at this press-pre-CES show, I expect that Sony will demo their 4K projectors, as they did before. Many LCD/plasma panel manufacturers are also expected to have their 3D demos with stereoscopic passive glasses such as JVC and Hyundai, with active shutter glasses such as Panasonic and several others, and with auto-stereoscopic capabilities with no glasses. I hope that I may be able to be lucky enough to hold the correct viewer sweet spot among the large CES crowd trying to do the same long enough so I can analyze the picture, a sweet spot that is usually required by that technology to obtain the full 3D effect.  <p>In other words, I expect that this CES could be similar to what 1998/9 was for HDTV when I purchased my first HDTV, but now for 3D HD. Considering that a broadly-adopted standard has not been established yet, I expect more confusion about the introduction of 3D than when HDTV was introduced in 1998, which had the ATSC standard of 1995 as a base.  A 3D standard for media, distribution and display is needed before 3D consumer electronics equipment and content are introduced in volume to the public.<p>I hope that the standard expected by the end of this year will soon enough align manufacturers, content distributors, and content creators, so consumers will not have to suffer another format war struggle, and pay for a wrong choice, again. <p>This time a format war in 3D may mean much more that choosing the correct player or selecting the correct 3D media/service provider. This time adopting wrong too early could be as bad as paying high dollar for a 3D-HDTV that implements <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">a 3D display standard</a> with limited 3D capabilities.  <p>Considering that the standard completion for 3D Blu-ray is so imminent, I expect that Panasonic would introduce a ready-for-retail 3D Blu-ray player with full 1080p dual HD images together with their new 3D plasma panel, as promised to be available in 2010. <p>I also expect to see demos of 8K and 16K by some manufacturers. Some market research companies recently estimated that millions of households would embrace the Ultra-HDTV format within the next few years, with a rather aggressive adoption over the next ten years. In my opinion, we just came out of the DTV transition in June 2009, about 50% of households are estimated to have HDTV, and the industry expects consumers to switch again to another technology, including 3D, when their new HDTVs are still smelling of brand new electronics in their homes?  <p>I estimate exactly 15,356,798 homes in 5 years with 16K 3D and a growth of 53.73% by 2025, if you know what I mean. What a crystal ball some people have indeed. My position is that it is too early to issue such defined projections when we are just getting a handle on HDTV after a recent DTV transition that “motivated” many people to invest in a new DTV, and many did even when not needing to replace their perfectly functional analog televisions.  <p>It will be interesting to witness any 3D announcements from the content distribution providers, such as satellite, cable, FiOS, Internet, and broadcast, as well as their short-term plans for 3D content, set-top-boxes, etc., and <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">using which transmission methods and image resolution for stereoscopic 3D HDTV to the home. </a> <p>We shall meet again soon, hopefully right after CES 2010 a few weeks from now. Stay tuned.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December  8, 2009  9:22 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3414)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3414)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php" type="text/javascript" charset="utf-8"></script>
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
