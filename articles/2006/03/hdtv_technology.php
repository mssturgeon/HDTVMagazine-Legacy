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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 354 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 354
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HDTV Technology Review, Part 1: Introduction&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/03/hdtv-technology-review-part-1-introduction.php&amp;title=HDTV Technology Review, Part 1: Introduction">
		<span style="display:none">As with every year, this report reviews the state of HDTV technology and the industry behind it. The information is up-to-date as of March 2006 and includes future products announced at January's International CES (Consumer Electronics Show). Most publications only show current DTV products with few specifications. They exclude equipment expected in the medium-term future, and they do not analyze the market to guide the reader in making the right choice. Hundreds of products are included in this report, with specifications and features intended to facilitate comparisons with other models, brands, and technologies.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 354";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Technology Review, Part 1: Introduction" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Technology Review, Part 1: Introduction" />
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
	<title>HDTV Magazine - HDTV Technology Review, Part 1: Introduction</title>
	<meta name="keywords" content="def dvd, technology review, hdtv technology, content protection, blu ray, dtv, ces, report, hdmi, digital, products, industry, dvd, hdtv, equipment, video, technology, audio, cable, def, connectivity, update, sii, year, future" />
	<meta name="description" content="As with every year, this report reviews the state of HDTV technology and the industry behind it. The information is up-to-date as of March 2006 and includes future products announced at January's International CES (Consumer Electronics Show). Most publications only show current DTV products with few specifications. They exclude equipment expected in the medium-term future, and they do not analyze the market to guide the reader in making the right choice. Hundreds of products are included in this report, with specifications and features intended to facilitate comparisons with other models, brands, and technologies." />
	<meta name="title" content="HDTV Technology Review, Part 1: Introduction" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/03/hdtv-technology-review-part-1-introduction.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=354', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/03/hdtv-technology-review-part-1-introduction.php">HDTV Technology Review, Part 1: Introduction</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>March 27, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>
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
				<blockquote>This is the first in an upcoming series of articles from the <b>HDTV Technology Review 2006 Report</b> by Rodolfo La Maestra, published in March 2006. If you are interested in purchasing the full version of this report, it is currently available for purchase from our <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.</blockquote>

<p class="big-first">As with every year, this report reviews the state of H/DTV technology and the industry behind it. The information is up-to-date as of March 2006 and includes future products announced at January's International CES (Consumer Electronics Show).</p>

<p>If you are looking for a particular piece of equipment or technology that is not mentioned in this report, please consult earlier CES reports. These reports include previously released equipment that may still be available to consumers. Previous CES reports are available on the HDTV Magazine website at the following address: <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">www.hdtvmagazine.com/reports/hdtv-technology-review.php</a>.</p>

<p>These reports also provide the historical background of government mandates, industry agreements, satellite/cable plans, definitions, and descriptions of some technologies introduced at that opportunity. Together with this 2006 report, readers are able to use this series of CES reports to better understand the past, present and future of recently released products and technologies.<br />
<img src="/images/articles/HDTVTR2006/image002.jpg" align=left></p>

<p>When applicable, I will provide a brief summary to give an historical perspective of a given subject so that the reader can be familiar with the background before getting into the detail of this year.</p>

<p>During the year, prior to CES, many announcements are made and industry events occur (such as dealer meetings, or conferences like CEDIA every September) which manufacturers leverage to introduce new products.</p>

<p>For these cases, I will mention the month of product introduction to provide a perspective of its maturity in the market. In addition,<br />
products announced at CES 2006 that are planned for release throughout 2006/7 are highlighted within each manufacturer grouping so the reader can have a view of the future.</p>

<p>Most publications only show current DTV products with few specifications. They exclude equipment expected in the medium-term future, and they do not analyze the market to guide the reader in making the right choice. Hundreds of products are included in this report, with specifications and features intended to facilitate comparisons with other models, brands, and technologies.</p>

<p>Many people attend CES to plan future purchases and start saving for products that could be released months or years later; or rather decide to buy now a current product because CES helped confirm that it might not be worth the wait.</p>

<p>In this report, I highlight industry trends, the adoption/abandoning of H/DTV technologies, the remarkable increase in number and variety of flat panel displays, the growth of LCoS, the 1080p Holy Grail, the Hi-def DVD format war, ED in all its variances (SED, NED, OLED, FED), etc.</p>

<p>This report assumes that the reader has a basic understanding of H/DTV. The technical information provided might seem overwhelming to readers<br />
that feel the need to understand the basics first. If you would like to understand the basics of H/DTV, you may want to consult the Glossary and some of my tutorial articles at the HDTV Magazine website: <a href="http://www.hdtvmagazine.com/">www.hdtvmagazine.com</a>.</p>

<p><img src="/images/articles/HDTVTR2006/image004.jpg"></p>

<p>All types of H/DTVs and technologies are covered in this report: RPTV (rear projection TV), FP (front projectors), Direct-view (CRTs, CRPs, etc), Plasmas (PDP), DLP (Digital Light Processing), LCD (Liquid Crystal Display), LCoS (Liquid Crystal on Silicon, including JVC's D-ILA and Sony's SXRD), and SED, OLED, FED, NED, etc.</p>

<p>This report also reviews DTV related equipment such as Hi-def DVD for playback and recording, HD tuning set-top-boxes (STB) for small-dish satellite, digital cable, and over-the-air (OTA) w/antenna reception, HD DVRs (Digital Video Recorders), and the implementation of digital video connectivity (DVI, HDMI and IEEE-1394 Firewire).</p>

<p>As always, this report makes more emphasis on H/DTV displays over 40" diagonal (except for a smaller few mentioned in the CRT and LCD-TV groups to round up the introduction of a new line). It excludes computer related HD-tuner cards, computer Hard Disk Drives (HDD) for HD video storage (a computer DVR), C-Band (big dish) satellite equipment, and some after-market modifications to HD-Set Top Boxes (HD-STBs) for DBS small-dish satellite HD recording (<a href="http://www.169time.com/">www.169time.com</a>).</p>

<p>All the information about models, prices, and specifications has been researched and confirmed with product demonstrations, lab tests, industry press releases, technical material, and my manufacturer interviews at CES. Prices are consistently shown as MSRP (rounding the 999s to the next dollar to make for easier reading). Product availability is stated as TTM (Time to Market) or TBA (when unknown).</p>

<p>As the industry grows in complexity, variety, and number of products, the effort to research, analyze, review products, and wrap with a full H/DTV coverage at CES with my projections is becoming an overwhelming task year after year.</p>

<p>Most people refer to this effort as a &quot;CES report&quot;. The truth is: <u>CES is just one piece</u> of the industry perspective offered in this document. In most cases, I already know what will be appearing at CES beforehand because I follow the industry on a daily basis. CES permits me to see (and in some cases test) those ground breaking H/DTV products and talk to the engineers that participated in their creation. No press release can provide that, and you have it here.</p>

<p>Nevertheless, I still make the effort because maintaining the broad scope allows me to link all the pieces together, which facilitates a deeper analysis from a wider perspective across manufacturers, technologies, and the industry in general.</p>

<p>The readership benefits from a unique report, a historic and summarized statement of a year of the H/DTV industry, which also becomes a research tool, rather than just showing the photographs of selected new products with modest background information, as most publications do, when they do.</p>

<p>Although considerable effort was made to consolidate and verify the correctness of all the complex data included in the report, I cannot assume responsibility for omissions or errors.</p>

<table><tr><td style="vertical-align:top">
	Any information you might want to contribute to correct or enhance the usefulness of this report would be certainly welcomed. Should you have any comments or questions, please feel free to contact me at <a href="mailto:rodolfo@hdtvmagazine.com">rodolfo@hdtvmagazine.com</a>.

<p>	Thank you for your continued support and interest in my work.<br />
<br><br />
<br><br />
	Rodolfo La Maestra<br />
</td><td style="text-align:center"><img src="/images/articles/HDTVTR2006/image006.jpg"><br />
	Your HDTV Magazine at CES 2006<br />
	<i>Shane, Dale, and Rodolfo</i><br />
</td></tr></table></p>

<p><br />
We have a lot to cover this year, now reaching 207 pages, so let us time travel to the future of H/DTV.</p>

<table class="bare" cellpadding=0 cellspacing=0 style="table-layout:fixed;overflow:hidden">
<col width=400>
<col width=30>
</td></tr>
<tr><td colspan=2 style="text-align:center;font-weight:bold;font-size:12pt">Table of Contents</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>H/DTV Highlights</b>  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">7</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>H/DTV Implementation</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Background Summary (1998-2004)  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  . . . . . . .</div></td><td style="text-align:right">22</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;The Updated Plan (2005 and Later) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">23</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;New Deadline, STB Subsidy, Public Education Program</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TV Statistics</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Converter Boxes for the Transition</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Integrated Tuner Mandate Update</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;H/DTV Programming</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DTV Market Penetration</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Analysis, Projections</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>DTV Standards - Update</b>  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">30</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Enhanced AC-3 Audio Standard</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;ACAP Standard</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Enhanced-VSB (E-VSB) Transmission Mode</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;High-Definition Audio-Video Network Alliance (HANA)</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>Satellite, Cable, Broadcasting</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Satellite</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DIRECTV . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">32</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Planned Upgrade</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2005/6 Update</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dish Network  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">33</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Planned Upgrade</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Voom</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2005/6 Update</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">34</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Managing the Progress to Bidirectional</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Microsoft and CableCARDs?</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Unidirectional CableCARD in a Bidirectional Cable World</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iDCR and DCAS</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OCAP</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OCAP Implementations</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ETV</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Broadcasting  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">37</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Must-Carry Multicasting Channels</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;USDTV . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">37</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>H/DTV Displays (*)</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;CRT, SED, OLED, FED, NED  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">39</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Digital Light Processing (DLP)  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">45</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Liquid Crystal on Silicon (LCoS)  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">64</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;LCD Projection (FP and RPTV)  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">70</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Plasma Panels . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">73</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;LCD-TV Panels . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">83</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;(*) Includes Direct-view, Panels, Front (FP) and Rear (RPTV) Projection</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>1080p into HDTV Displays</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;1080p Implementation  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">95</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;1080p by Brillian . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .</div></td><td style="text-align:right">97</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Upconversion to 1080p</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Deinterlacing Implementation</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1080p Acceptance</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Upgradeability</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Integrated Tuners, FireWire, ISF</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Brillian Moving Forward</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>Other HDTV Equipment</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDTV STBs (Tuners / DVRs) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">103</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Hi-def DVD (HD DVD and Blu-ray) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">112</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Background</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Formats Reconciliation</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Universal Player</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Interactivity</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Content Protection</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AACS Down-Res</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PVP-OPM</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gaming</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Format Specifications</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Computing</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PC Applications for BD</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Discs</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BD Discs</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Recording media for HD DVD and Blu-ray</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BD-ROM Pre-recorded Media</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Studio Announcements</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Launching Announcements</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HD DVD Launch</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Blu-ray Launch</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HD DVD Equipment</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Blu-ray Equipment</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Analysis  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">128</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Choosing the Player</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cutting out Early Adopters, Possible Consequences</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1080P and Hi-def DVD</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A Different View of Hi-def DVD Booths at CES</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Taiwan's Forward Versatile Disc (FVD) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">131</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HD Signal Processors</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Video Processing Engines  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">132</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Processors Using HD Video Processing Engines  . . . . </div></td><td style="text-align:right">136</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDTV Video Cameras  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">139</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>Digital Connectivity - Tutorial</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;DVI . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">144</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;IEEE1394  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">146</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">147</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>Digital Connectivity Implementation</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI Connectivity (2004-2005) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">148</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2004</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2005</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PanelILink Cinema (PLC) Partners Program</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI Connectivity Update (2005 - 2006)  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">149</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HDMI Licensing, v1.2 Specification</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HDMI First Receiver SiI 9033 with v1.2 Specification</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HDMI Receiver IC Support for 1080p</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HDMI SiI 1930 and 1390 Transmitters</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HDMI SiI 9023 Receiver</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SiI 4726 Processor</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HDMI SiI 9020 Transmitter for HD Cameras</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Simplay Labs Introduction</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SiI 8200 Video Processor</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iTMDS Internal Link Technology and SiI 7170 Transmitter</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;and 7171 Receiver</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI Version 1.3  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">153</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI Industry Adoption  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">153</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI in 1080p Equipment . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">154</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HDMI Multi-channel Audio  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">155</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Other Digital Connectivity - Update . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">156</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IEEE1394 over Coaxial</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IEEE Approves Initial 802.11n Spec</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IBM Developed Wireless HD Chip</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UWB</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>Multi-channel Audio for HD</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Hi-bit Dolby Digital Formats - Connectivity . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">158</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Single-Cable Digital Connection</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Multichannel Analog Connection</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/PDIF Connection</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dolby TrueHD and Dolby Digital Plus in A/V Receivers</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Legacy Discrete Surround Audio Formats for Hi Def DVD . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">162</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Hi-bit Surround Audio Formats - Summary . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">163</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dolby Digital Plus</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DTS-HD (DTS++ and DTS-HD Master Audio)</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dolby TrueHD</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Hi-bit Audio Application to Hi-def DVD Formats 164</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Analysis  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">165</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>HD Content Protection</b></td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;DTV Content Protection Rulings and Agreements . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">167</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Plug-and-Play Cable Agreement</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Broadcast Flag</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other Technology Alternatives to the Flag</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Down-res</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Analysis - Some Loose Ends</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;What Could You Do?</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;Graphical Representation of Rulings and Agreements  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">177</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;HD Content Protection - Update  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">178</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AACS</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AACS Down-res Approved</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PVP-OPM</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Broadcast Flag</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>Glossary of H/DTV Terms</b> . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">182</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap">&nbsp;</td></tr>
<tr><td><div style="overflow:hidden;white-space:nowrap"><b>About the Author</b>  . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </div></td><td style="text-align:right">207</td></tr>
</table>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>March 27, 2006 12:51 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(354)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 354)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/03/hdtv-technology-review-part-1-introduction.php" type="text/javascript" charset="utf-8"></script>
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
