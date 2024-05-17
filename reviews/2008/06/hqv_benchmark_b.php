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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1406 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1406
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HQV Benchmark Blu-ray, DVD and HD DVD&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php&amp;title=HQV Benchmark Blu-ray, DVD and HD DVD">
		<span style="display:none">HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, &lt;a href=&quot;http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789&quot;&gt;Scaler&lt;/a&gt;. 

The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of the tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to disc players...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1406";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HQV Benchmark Blu-ray, DVD and HD DVD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HQV Benchmark Blu-ray, DVD and HD DVD" />
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
	<title>HDTV Magazine - HQV Benchmark Blu-ray, DVD and HD DVD</title>
	<meta name="keywords" content="hqv benchmark, blu ray, dvd player, external scaler, digital video, test, dvd, player, video, tests, content, display, testing, response, hqv, benchmark, pixel, blu, ray, vertical, horizontal, same, pattern, detail, disc" />
	<meta name="description" content="HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, &lt;a href=&quot;http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789&quot;&gt;Scaler&lt;/a&gt;. 

The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of the tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to disc players..." />
	<meta name="title" content="HQV Benchmark Blu-ray, DVD and HD DVD" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1406', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php">HQV Benchmark Blu-ray, DVD and HD DVD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>June 19, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=318&category=HDTV Accessories">HDTV Accessories</a></b>
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
				<p><br clear="left"/><b>Product line up and pricing</b></p>

<ul><li>DVD: HQV Benchmark Version 1.4 NTSC $20</li>
<li>DVD: HQV Benchmark Version 1.4a PAL $20 (not reviewed)</li>
<li>Blu-ray: HQV Benchmark Version 1.0 $20</li>
<li>HD DVD: HQV Benchmark Version 1.0 $10</li></ul>

<ul><li>Bundle 1: Blu-ray, DVD (NTSC) $30</li>
<li>Bundle 2: HD DVD, DVD (NTSC) $25</li>
<li>Bundle 3: Blu-ray, DVD (PAL) $30 (not reviewed)</li>
<li>Bundle 4: HD DVD, DVD (PAL) $25 (not reviewed)</li></ul>

<p><b>Summary: </b>A very useful testing regimen if you understand the limitations<br /></p>

<p>Have you ever wondered why special features on DVD don't look as good as the movie you watched? Maybe you've wondered why some of your DVDs don't look as good as others? Or why Blu-ray Hollywood movies appear to have more detail than concerts or documentaries? HQV Benchmark has a testing regimen to help you figure all of that out!</p>

<p>HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, <a target="_blank"href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789">Scaler</a>. </p>

<p>The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of its tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to your overall experience.</p>

<p><br />
<b>Testing, Scoring, Education</b></p>

<p>The test material concentrates mostly on deinterlacing and scaling. Silicon Optix provides a downloadable PDF file of the test regimen which includes how to score each test along with detailed explanations. While the guide provides comparison images, the resolution is not high enough for many of the tests to assist you in fully appreciating what to look for.</p>

<p><a target="_blank" href="http://www.hqv.com/contentEngine/dspDocumentDownload.cfm?PCVID=6557af58-7e90-e2a3-bea3-f6ec25bf8781">HQV Benchmark DVD Testing and Scoring Guide</a><br />
<a target="_blank" href="http://www.hqv.com/contentEngine/dspDocumentDownload.cfm?PCVID=6557b0fd-7e90-e2a3-bdde-f1edd6040515">HQV Benchmark Blu-ray and HD DVD Testing and Scoring Guide</a></p>

<p>Many of the DVD test results and all of the Blu-ray and HD DVD test results rely on perception requiring proper calibration of the display for valid results and is noted by * in the title. Neither disc provides a full suite of calibration test patterns. Digital Video Essentials is recommended and available in Blu-ray and DVD. There is also an HD DVD and DVD combo available while supplies last. If your system has been ISF calibrated, you are ready for testing. If not, <a target="_blank" href="http://www.isfforum.com/Find-a-Calibrator/ISF-Forum-Calibrators.html">check the ISF Forum</a> for a professional ISF calibrator in your area.</p>

<p><br />
<h2>HQV Benchmark DVD</h2></p>

<p>The test material is a mix of 4:3 and 16:9 original aspect ratio (OAR) content, which will require you to manually change the aspect because there are no flags to trigger the auto 4:3/16:9 aspect feature that some players support. If the auto aspect does not provide a manual feature then you are stuck with 4:3, which will not be correct for some of the 16:9 tests. The introduction and test material does not provide reference imaging quality for showing off the DVD format at its best.</p>

<p><b>Color Bars (4:3)</b><br />
This is a resolution test that is part of a multiple test pattern. This is not only one of the more useful patterns on the disc, but contains other tests such as color decoding at different saturation levels and luminance (video) levels. I recommend testing in both 16:9 and 4:3 aspects. Above the middle is a resolution response test for both luminance and chroma. Going left to right they are numbered as 4, 3, 2 and 1 with 1 representing 720 pixels horizontally. The catch is that this is a 4:4:4 encoded pattern which means the chroma has the same response as luminance. The consumer DVD standard (and HDTV) uses 4:2:0 encoding which cuts the chroma response to half of luminance to conserve bandwidth and storage space. The chroma response in block 1 serves no purpose since it cannot be properly reproduced so disregard those results. This pattern is part and parcel of confirming proper calibration prior to testing.</p>

<p><b>Jaggie 1 (16:9)</b><br />
A single bar is constantly rotated inside a circle testing the high detail b/w video or luminance portion of an image. </p>

<p><b>Jaggie 2 (16:9)</b><br />
Three bars move back and forth in a narrow arc, covering 50-20 degrees, inside a circle testing the high detail b/w video or luminance portion of an image.</p>

<p><b>Flag (4:3)</b><br />
A final jaggie test of both color and luminance using the common and notoriously difficult American flag waving in the wind.</p>

<p><b>Detail (16:9)</b><br />
The disc narrative and guide both stress that this material should "exhibit fine detail resulting in a crisp realistic image" and other similar statements. This material will never have the response one would expect or can get viewing properly captured and mastered DVD video. Page 10 of the guide compares two images that hardly look different for a test score high of 10 and 0, and in this case resembles reasonable expectations for this test.</p>

<p><b>Noise (4:3)</b><br />
If the TV or player does not have a noise reduction (NR) feature, skip this test; it is not about players. MPEG NR targets compression noise, is processed differently and should not be used for this test. A series of 12 still images are provided and most target the blue color channel. Two of those images never showed any noise and one was marginal. These images are great examples of a noisy analog cable service or a satellite / cable set top box delivered via channel 3 or 4 to your TV. This test is all about the NTSC broadcast television system, analog cable, analog RF tuners and the RF noise that can easily come from them. While having little to do with players, they are useful for DVD recorders and broadcast NTSC. For high fidelity with DVD, NR on your DVD player should be turned off. For recorders this feature may make some or all of your noisy channels more palatable but in most cases the setting will also apply to DVDs, in which case it should be turned off.</p>

<p>One point missing from the guide; the first step is to view the content with NR turned off and that includes the TV if available. Look through or ignore the noise and recognize the detail that is present. Now turn on NR on the player and see how much noise is removed along with any loss in detail. You can also reverse the test, turn the NR off on the player and turn it on for your TV if available. Some NR circuits offer different range levels in which case test all of them and determine which setting provides the best balance of detail versus noise suppression.</p>

<p><b>Motion Adaptive Noise (16:9 and 4:3)</b><br />
A 16:9 image of a roller coaster and a 4:3 image of a boat going down a river are provided. While an NR circuit can successfully navigate the prior noise test of still images, the addition of motion will show any artifacts created by the process and may help identify what kind of NR the video processor is using. Follow the same procedure for testing as previously described, following the guide for evaluation. The roller coaster is also a convenient test for LCD pixel speed provided you turn off NR on the player and display. </p>

<p><b>Film Detail (4:3)</b><br />
In the guide, this test is called 3:2 Detection, which is a far better description of the test result. A familiar movie scene is provided of an F-1 car passing by empty bleachers testing the standard 3:2 cadence required with 24fps (frames per second) content. </p>

<p><b>Assorted Cadences (16:9)</b><br />
A test clip is provided in 8 different cadences. This is the most brutal part of the testing regimen that few players or displays will pass. External scalers should pass most if not all of the following tests:</p>

<p>2-2 30fps film<br />
2-2-2-4 DVCAM<br />
2-3-3-2 DVCAM<br />
3-2-3-2-2 VARI SPEED Broadcast<br />
5-5 Anime<br />
6-4 Anime<br />
8-7 Anime<br />
3-2 24fps film</p>

<p><b>Mixed 3:2 with titles (4:3)</b><br />
4:3 images at 24fps are provided with the same kind of 30fps-based titles you would get from your broadcaster notifying you of weather alerts or emergencies. This is also delivered in the form of end credits at the end of a TV show. This applies far more to broadcast TV and your display rather than players.</p>

<p><br />
<b>HQV Benchmark DVD on Your Player</b></p>

<p>Just because a player fails some or all of these tests does not mean it will generate the same errors when playing a Hollywood movie on your player, nor would passing some of these tests qualify as high fidelity performance. None of the tests even relate to how the vast majority of movies are captured, processed and mastered for DVD along with how your player is designed to reproduce them. Missing from this disc is the same test material processed and mastered just like Hollywood does. Without such a reference point the person doing the evaluation may have unrealistic expectations of how well the test material should perform.</p>

<p>While it can be argued on the surface that some of these tests should apply to a player, as a reviewer I find myself in a catch 22. Test material from Avia, Video Essentials, Sound and Vision, Digital Video Essentials and nearly all popular movies look decent to fantastic although that same player fails all or some of the HQV Benchmark material. How can that be? And as reviewer, how do I report a passing or failing grade?</p>

<p>The key is understanding why an inexpensive DVD player can get decent results with no-name video processing. This is achieved during mastering by including progressive flags in the data directly from the mastering studio telling the video processor in the player how to take the interlaced fields and put them together for a proper 480p presentation. This is an extremely intelligent way to deliver a high fidelity performance envelope on the cheap! With a native 480p 16:9 display, typically CRT only, and a properly designed 480p player, you are in videophile nirvana due to this free ride but this is an HDTV world and most displays these days require the 480p free ride gets scaled to one of the HD scan rates, 720p, 1080i or 1080p. Proper deinterlacing is the crux and scaling is far easier so this free ride can provide decent to high quality performance beyond 480p depending on the design goals! There is a catch; no flags, no free ride and with incorrect flags, the free ride could turn bumpy with errors/artifacts.</p>

<p>The HQV Benchmark DVD technical twist is that the material is encoded as raw 480i, no progressive flags for dumb scaling, leaving the player entirely on its own to figure out how to deinterlace the content. The bottom line is that the vast majority of players are going to fail many of the tests that relate to DVD content. While the movie is bound to have these progressive flags, that may not be the case for special features. This has improved over the years, but for a movie buff and/or DVD collector much of a library is going to contain such content. On top of that, many a collection will have 4:3 letterboxed releases along with the oddball cadences that come with low volume or low budget productions, cult classics, anime and TV shows on DVD. Some folks desire a player or external scaler that can get the most out of such content. HQV Benchmark is exactly what the doctor ordered for reviewers and videophiles alike who are looking for a simple straight forward battery of tests to quickly determine performance with such content. The only test missing is for 4:3 letterboxed material. </p>

<p><br />
<h2>HQV Benchmark Blu-ray / HD DVD</h2></p>

<p>The introductory scenes of 16:9 video content are quite short, although in the other chapters about testing there is material of greater length. This content is worthy of overall image quality evaluation, but it does appear slightly soft in detail and flat in dynamic range compared to other reference material. Both discs provide the test materials in three different play formats; Play Loop, Play All Tests (manual advance) and Select Individual Tests. The first two automated play formats unfortunately skip one or more tests (noted in the test pattern breakdown) so if you want to view them all then choose Select Individual Tests.</p>

<p><b>HD Color Bars</b><br />
This pattern is only available under Select Individual Tests and appears to be derived from a non HD source and scaled to 1080i based on the color pixel errors at the edges where two colors meet. Useful for checking / confirming luminance and color levels prior to testing.</p>

<p><b>HD Noise</b><br />
Provides two images, a flower and sailing boat, which have motion components as well as noise. The Blu-ray version skips the sailing boat when choosing Play Loop. You may be hard pressed to even use this test since a noise reduction feature is not common for HD disc players or displays when viewing HD scan rates. Please check the DVD version of this test for additional details. </p>

<p><b>Video Resolution Loss</b><br />
A SMPTE RP-133 1920X1080 <a target="_blank" href="http://www.isfforum.com/viewtopic.php?t=82">1:1 pixel map</a> of luminance, b/w video, has a 360 degree constantly rotating bar. The results as described in the guide require a 1920x1080 pixel matrix to test the internal scaler along with proper aspect setting of the display for 1:1 pixel mapping best confirmed prior to testing with the same pattern from Digital Video Essentials. This pattern has a black and white video level error and pixel mapping error. Black is about 10% above peak black and white is about 10% below peak white; this is not critical to the test. The pixel map is correct for the vertical plane, 1080 pixels, but incorrect for the horizontal plane, 1920; this is not critical for this test of 1080 vertical resolution but the 1920 burst for horizontal resolution will not show up appearing as a gray box. As a 1:1 pixel map pattern, the horizontal response should be ignored. This test applies to other pixel matrixes and 1080i content but you will likely have problems with the vertical 1080 burst and the response may vary in the five different areas of the image. The rotating bar will tell you if the 3:2 cadence is being properly detected and should show smooth motion as it spins.</p>

<p><b>Diagonal Filtering Jaggies Test</b><br />
Provides the same two tests as the DVD. The first is the single bar constantly rotating and the second is where three bars move back and forth in a narrow arc. The Guide calls this Video Reconstruction Tests, does not document the second test and both disc versions require you choose Select Individual Tests for the second jaggie test to be viewed.</p>

<p><b>Film Resolution Loss</b><br />
A SMPTE RP-133 1920X1080 <a target="_blank" href="http://www.isfforum.com/viewtopic.php?t=82">1:1 pixel mapped</a> test pattern is panned back and forth along with a horizontal pan of a football stadium. The Blu-ray version skips the stadium when choosing Play Loop. The test uses the same pattern as Video Resolution Loss containing the same errors and has the same requirements for testing. Unlike the previous test, this one can show horizontal response errors due to motion. As before, the 1920 box will be gray. Included in this pattern are video level boxes with percentage of modulation and you can see that the one pixel space between the two digits is missing implying that frequency response has been slightly extended. This means that the 1920, 960 and 480 boxes actually have slightly higher pixel counts which is why it does not pixel map and probably why the 1920 response is missing. This test still applies but due to this error it clearly is not the ultimate test it could be and who knows if a scaler might perform better with a true pixel map expecting 1920x1080. That said, the guide and disc only discuss errors in the vertical response. </p>

<p>The stadium test follows but lacks the snap of detail expected from quality capturing and 1:1 pixel mapping. It only pans right to left and one display that was tested with the SMPTE pattern had less horizontal response artifacts in that direction over the other. It's a shame the stadium was not panned in both directions like the SMPTE pattern. When the 50 yard line hits about the center of the screen there is a subtle pixel response change and a change of light output level in some areas of the scene. Two displays failed the SMPTE pattern test in clearly different ways for either the horizontal or vertical planes. I found this test one of the most difficult to analyze. </p>

<p>With either display the horizontal plane detail would drop in and out at what appeared to be the same points. The display that passed the prior test for vertical response simply had more detail while in motion and at the final still point of a few seconds. While the other display faired far better with the prior test of horizontal response, both appeared to respond equally. With a casual viewing you may be hard pressed to detect any appreciable difference in the stadium pan. If you concentrate on the horizontal response you will miss the point of this test. Concentrate on the vertical response or horizontal lines in the bleachers, light towers, field and elsewhere in the image. Those are the trees you are looking for in this forest and the horizontal response of vertical lines seems best left ignored.</p>

<p><br />
<b>HQV Benchmark Blu-ray / HD DVD on Your Player</b></p>

<p>As with the DVD version, missing from these discs is the same test material processed and mastered just like Hollywood does. A 1080p24 reference point with a proper 1080p24 display would show the viewer what correct performance looks like assisting the 1080i evaluation. It would be interesting to see how a player handles converting a Hollywood 1080p24 version of these specific test materials to the other scan rates. </p>

<p>Just like DVD, we have the same dilemma of how a player can fail these tests yet show not one sign of trouble with actual movies or calibration and test discs like Digital Video Essentials. The answer is the same; HD disc movies, for the most part, are not mastered as 1080i, they are mastered as 1080p24 and the player is designed to work with that when deriving other output scan rates. </p>

<p>Like DVD, special features still receive little to no attention and can vary from 480p all the way through 1080p24. I have even seen 480p 4:3 letterboxed content. Even though Blu-ray is an HD 16:9 destined format you just don't know what you are going to get. It doesn't end there; feature material that was captured as native 1080i is mastered as is. Most of those titles are concert, documentary features and television programming along with some indie movies with the common thread being that they were captured with HD 1080i based cameras. If the content is original 24 frame film then telecine mastering is performed at 1080p24 and that represents the majority of the Hollywood catalog. </p>

<p>Testing HD disc players is not nearly as straight forward as it was for DVD. Getting acceptable imaging out of lower resolutions (such as standard definition) is far more difficult. With DVD content you can run into some oddball content that will create very noticeable artifacts far more easily detected, even at far viewing distances. The nine cadence tests for DVD are a prime example and are not part of the HD version. With that in mind, the most critical HQV test for any HD disc viewer is proper cadence detection of the rotating bar of the Video Resolution Loss test which must pass with smooth motion and should be tested at 720p, 1080i and 1080p depending on what HD scan rates your display will accept. You may find that only 1080i provides the correct response. As for the other tests of 1080 horizontal lines testing vertical response, failure hardly means your image will stink but it does mean that 1080i content will have a loss in horizontal line detail. More than likely the failure will be due to vertical filtering which requires a deeper understanding of burst testing, what is going on and how it affects specific elements of an image. I refer you to <a target="_blank" href="http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_vertical_and_horizontal_filtering.php">HD Waveform - Vertical and Horizontal Filtering</a> for detailed information.</p>

<p>For those with a display that only accepts a 1080p60 or 1080i output you can test the player's ability to convert Hollywood 1080p24 to either of those scan rates by using the SMTPE RP-133 from Digital Video Essentials for both Blu-ray and HD DVD. In this case the pattern is properly pixel mapped in both horizontal and vertical planes but it is a still pattern and cannot test for cadence or loss of resolution due to motion. For either scan rate setting you are looking for single pixel lines in the vertical 1080 box response and horizontal 1920 box response.</p>

<p><br />
<b>Vertical Filtering - Finding the Right Recipe</b></p>

<p>If you want every last shred of detail with 1080i content then vertical filtering is unacceptable. The easiest route to overcoming that is no recipe at all, which requires a player to properly deinterlace 1080i and convert it to 1080p60 or 1080p24 for a proper response along with advanced features that would allow you to output native 1080p24 content untouched. Most 1080p displays these days provide 1:1 pixel mapping at 1080p60 and many include 1080p24 as well. More importantly, the player must do this automatically providing a convenient hands-off and worry free approach for the user. While such a player would provide the ultimate keep it simple solution for any viewer, finding one that has been designed much less reviewed for this attribute may be far more difficult. For current Blu-ray players the right recipe of display and player can achieve the exact same results and external scaling provides yet another solution.</p>

<p>The discs were tested with a Sony PS3 and Toshiba HD-A35. The PS3 will not do a thing with native 1080i content except pass it along as is; testing 1080i conversion of the player to 1080p was impossible. With a full featured 1080p display the PS3 defaults to native output of the source. I did find it curious that the HQV Blu-ray menus are native 1080p24. The Toshiba on the other hand does not support native output and follows what you have set it for; 720p60, 1080p60 or 1080p24. Set for 720p or 1080p60 the Toshiba failed to pass the vertical 1080 box. Set for 1080p24 the Toshiba completely wiped out on cadence with every one of the tests creating a strobing effect at all times. Both players passed the Digital Video Essentials SMPTE RP-133 test at 1080i and 1080p60. A <a  target="_blank" href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000</a> and <a target="_blank" href="http://www.hdtvmagazine.com/reviews/2007/07/benq_w10000_1080p_dlp_front_projector.php">BenQ W10000</a> front projector were also tested. The Panasonic passed with flying colors for the most part while the BenQ failed due to vertical filtering.</p>

<p>The combination of the Sony PS3 and the Panasonic PTAE-1000 provides optimal results. Since the Sony defaults to native output of disc content and the Panasonic passes 1080i testing along with native 1080p60 (1080p 30 frame source) or 1080p24 support you are getting full automation for the best results. Either the internal scaler of the display is deinterlacing and scaling or is getting a direct feed bypassing it providing a 1:1 pixel map instead. </p>

<p>The Toshiba HD DVD player on the other hand does not have a native mode and simply does what you tell it to. If you know the HD DVD disc has native 1080i content then even with a display like the Panasonic you will have to change the output scan rate to match the disc source; same goes with the Toshiba feeding an external scaler. While lacking automation you can get optimal results manually.</p>

<p>The BenQ is a stellar performer and nearly a reference for video standards when pixel mapped at 1080p60 or 1080p24 yet 1080i is its Achilles performance Heal. Neither player tested provides a direct solution alone. One solution requires an external scaler with advanced features that will de-interlace and scale 1080i while allowing a 1080p60, 1080p30 or 1080p24 bypass. Mated with the PS3 you would achieve simple automation.</p>

<p>Untouched native 1080p24 content from player to a 1080p24 display is quite easy to acquire and will satisfy most users. In the end it all comes down to you, your system, how you use it (viewing distance) and the importance you place on some or all of the content you are viewing. Ultimately HQV Benchmark is limited in its ability to answer all of these questions if you want the most out of every bit of content you might be feeding your system since it is limited to 1080i testing only. </p>

<p><br />
<b>HQV Benchmark on Your Display</b></p>

<p>A properly designed 480i DVD player can be used to evaluate the internal scaler of a display or external scaler using the analog component inputs. Digital video, HDMI/DVI, is typically limited to 480p but if your display accepts 480i and your player can provide it without artifacts then that would be a valid test. Simply set the output of the player to 480i. Testing S-video and composite video connections is a bit more dicey because that requires proper down conversion so while likely not the ultimate reference test signal from your player there are still things that can be learned. For cable and satellite boxes using the analog component input of your display, these tests have direct value if you are setting the box to native so NTSC is output as 480i. Since you can't test the box it won't help you determine if the box or your display is doing a better job. Unfortunately, what this disc can't test is your NTSC TV tuner. Keep in mind during your testing how some displays apply, lock or limit different video processing features based on the input type. </p>

<p>A properly designed Blu-ray or HD DVD player can be used to evaluate how the internal scaler of a display or external scaler handles 1080i content using the analog component inputs or HDMI/DVI (with the player set for 1080i output). </p>

<p><b>Conclusion</b></p>

<p>While the some of the tests represent a small portion of the available catalog, the DVD version helps those seeking videophile nirvana with any and all DVD content on the planet via a player or external scaler. All of the tests are useful for determining how your display or external scaler handles broadcast NTSC video. For player evaluation, it should be considered a secondary test to the primary test of other discs that do follow Hollywood mastering representing the vast majority of what you would rent or purchase. The Blu-ray and HD DVD version provides a battery of tests to evaluate how your display or an external scaler handles HDTV 1080i content from Blu-ray disc or broadcast HDTV. If Blu-ray Hollywood movies are your main concern then just like DVD it should be considered a secondary test to the primary test of other discs that provide a native 1080p24 response representing the majority of Hollywood features. Either version of HQV Benchmark is unique in what it brings to the videophile table for display and player evaluation. With those limitations understood, the HQV Benchmark series is a unique evaluation tool that deserves a place in the videophiles toolbox.</p>

<p><b>Test Results of Past Products </b></p>

<p><a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33688#33688">Panasonic PT-AE1000U LCD Front Projector </a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33689#33689">BenQ W10000 DLP Front Projector</a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33709#33709">Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33710#33710">OPPO DV-981HD Upconverting SD DVD Player</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>June 19, 2008 12:24 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1406)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 1406)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php" type="text/javascript" charset="utf-8"></script>
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
