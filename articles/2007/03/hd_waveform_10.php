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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 565 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 565
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=HD Waveform 10 - Dynamic Iris and Gamma&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php&amp;title=HD Waveform 10 - Dynamic Iris and Gamma">
		<span style="display:none">Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature degrades overall image performance, why it sells product and why, for some technologies, it may be needed to be competitive.

&lt;B&gt;Iris&lt;/B&gt;
One way to improve dynamic range and measured contrast ratio is to employ an iris. An iris typically decreases...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 565";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HD Waveform 10 - Dynamic Iris and Gamma" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HD Waveform 10 - Dynamic Iris and Gamma" />
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
	<title>HDTV Magazine - HD Waveform 10 - Dynamic Iris and Gamma</title>
	<meta name="keywords" content="dynamic range, light output, contrast ratio, peak white, gamma ire, gamma, iris, ire, dynamic, image, black, light, response, video, contrast, output, system, does, peak, range, pattern, ratio, using, imaging, full" />
	<meta name="description" content="Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature degrades overall image performance, why it sells product and why, for some technologies, it may be needed to be competitive.

&lt;B&gt;Iris&lt;/B&gt;
One way to improve dynamic range and measured contrast ratio is to employ an iris. An iris typically decreases..." />
	<meta name="title" content="HD Waveform 10 - Dynamic Iris and Gamma" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=565', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php">HD Waveform 10 - Dynamic Iris and Gamma</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>March 26, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p class="editorial">"HD Waveform" is a series of articles published over the past few years and originally made available only to subscribers of HDTV Magazine. It is authored by Richard Fisher and was born out of 20+ years in the industry and discussion among HDTV Magazine membership concerning faithful reporting. HD Waveform goes into great detail, providing conclusions based in science and is therefore suitable for both consumers and professionals alike. In many cases these conclusions will seem at odds with what many are hearing "on the street" and from marketers. Waveform is for those who care about quality, performance and reasonable scientific conclusions. Feel free to read the rest of <a href="/forum/viewforum.php?f=103">The HD Waveform Series</a>, as originally published.</p>

<p>Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature does not meet video standards, why it sells product and why, for some technologies, it may be needed to be competitive.</p>

<p>To shorten the article some terms have embedded links, blue, for a full definition of the term.</p>

<p><B>Iris</B><br />
One way to improve dynamic range and measured contrast ratio is to employ an iris. <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=86">An iris</a> typically decreases the light going to the panels or imager, or can also be on the output side reducing the light entering the lens after the imager. A manual iris allows the end user to employ a true brightness adjustment for the best blacks in their application. Reducing light output to the lens also improves the intrafield contrast ratio or actual available dynamic range when you have a mix of bright and dark within the same frame. Many higher end front projectors employ a manual iris. The disadvantage is that no matter where you set it, you have increased or decreased the light output linearly; Blacks may look blacker but peak white also drops in output as well. What if we had an iris that could change its setting on the fly based on image content such that it closes up during dark scenes for better blacks and opens up during bright scenes for peak light output, thereby creating a wider light output capability? Hook up a motor to a high speed iris mechanism and viola, you have an auto iris. While a good start, that alone cannot change the natural dynamic range of the technology. It only changes light output and one has to be very careful of when and how fast it changes or the viewer will catch this process in motion which is often times referred to as breathing.</p>

<p><B>Gamma</B><br />
What if you could change brightness and contrast levels, gamma, on the fly and better yet do it at multiple specific points in the video signal based on image content? Viola, you have the ability to create the perception of more dynamic range. <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Gamma</a> is the difference in response between two levels and for video we use the industry standard of 2.2. Video gamma is also a non linear response determined with an exponential equation rather than simple multiplication. A video signal is broken up into IRE levels where 0 IRE is peak black and 100 IRE is peak white. In terms of overall gamma, if it is less than 2.2 the image becomes flat, dull or washed-out and if it is more than 2.2 the image becomes dynamic, bright or aggressive. When manipulating gamma you have two brick walls, peak white and peak black which you cannot go beyond. As an example that means you cannot increase the gamma response from 60-100 IRE without decreasing the gamma from 0-59 IRE. You cannot increase the gamma from 40-70 IRE without decreasing gamma from 0-39 IRE and/or from 71-100 IRE. You have to rob Paul to pay Peter; there is no other way when playing the gamma game on the input signal or you will induce clipping errors that will be quite visible. It is possible to overcome this brick wall of a video signal by changing the gamma response at the imaging device but then you face the brick wall of what the device is capable of and that is typically maxed out anyway when using a <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">D65 color temperature</a> as a reference. This is a rare occurrence and bears little merit for discussion</p>

<p><B>Black is an Illusion</B><br />
When it comes to imaging, black is the absence of light and therefore has perceptual qualities related to optical illusion. We can take a display with poor blacks and with the right pattern, such as a full field 0 IRE pattern (black), make that aspect quite prominent. We can also improve our perception of that level of black by simply introducing a 50 IRE window in the middle of the 0 IRE full raster pattern. I can make the black seem even blacker by increasing that window to 100IRE providing the greatest amount of light difference. By providing that comparison to your eyes the black appears blacker, you perceive a greater dynamic range, but it is an optical illusion! Technically, measurement would show the far more likely result that light from the window is leaking into the black area which not only reduces your black level but can also hide subtle levels of black. This is called intrafield contrast ratio putting yet another spin on the optical illusion of black!</p>

<p><B>Putting All 3 Together</B><br />
The ultimate goal for this system is to create a perceptual increase in dynamic image. The best explanation uses two image extremes. Using a dark scene with no real peak white, the iris closes up improving real black level. The gamma is then expanded from say 0-50 IRE reducing the gamma from 50-100IRE. This will create a more dynamic presentation for the dark image and blacks will be perceived as even blacker since those areas that do have light have been increased, creating a greater dynamic difference; the optical illusion of black. Using a bright scene with no real peak black the iris opens up improving real light output. The gamma is then expanded from say 50-100 IRE reducing the gamma from 0-50 IRE. This will also create a more dynamic presentation and blacks will be perceived as even blacker since those areas that do not have light have been decreased creating a greater dynamic difference; the optical illusion of black. In no way does this simplistic example represent the far more complex nature of this system and its implementation, but hopefully you have an elementary understanding of how this is done and why it can make a mess of things.</p>

<p><B>Objective Measurements</B><br />
Does it work? Why of course! It does create the desired perception for the viewer of better dynamic range and allows the manufacturer to claim greater contrast ratios on their specs. Close up the iris for the peak black measurement and open it up for peak white measurement and you can only get a larger number than one without an iris. From a current Panasonic PTAE1000 review after ISF calibration:</p>

<p>With the Dynamic Iris OFF at 96 lamp hours I obtained 367fl at 100IRE and .522fl for 0IRE yielding a contrast ratio of 703:1.</p>

<p>With Dynamic Iris ON I obtained 715fl at 100IRE and .503fl for 0IRE yielding a contrast ratio of 1421:1.</p>

<p>If we go check the specs at Projector Central Panasonic is claiming a contrast ratio of 11000! This is defined as full on, full off. Historically this is defined as the projector using a 100IRE window or raster with all three colors maxed out for the 100IRE reading, not even remotely close to D65, nor really viewable, and then the projector turned off! Obviously that has nothing to do with actual imaging and is a flawed test, yet they are doing it. Nearly all of the manufacturers are following this testing procedure to remain on equal footing in the market. The human eye is capable of about 800:1 for any given scene whether on screen or in real life. Does a real contrast ratio of 11000:1 have any value? The point is this is nothing but specification marketing shenanigans that tell the consumer or imaging professional little about actual performance.</p>

<p>Getting back to the magic trick of a Dynamic iris, the ON number is very impressive in the real world of peak white and black contrast ratios! This test implies nearly a doubling in contrast ratio yet this is not the perceptual experience you will have. All an iris can change is the light output to the imager, not the native dynamic range of the technology. What really puts this in perspective is switching the iris on and off with paused images. With the right image you can see a difference in light output yet with most the difference is quite subtle. Ultimately the biggest difference you see with this test is a change of the gamma response within the image rather than simple light output.</p>

<p>If the end user does not know what the image should look like, no references, they likely will not detect that it is wrong either hence the acceptance and popularity in the mass market. No matter what, it will never provide an accurate image due to the artifacts of an incorrect gamma response. The first two gamma plots of a window and full field pattern represent the calibrated response with iris turned off as the light output changes from 0-100 IRE in 10 IRE steps. The dotted line presents the desired response curve so please ignore the average gamma calculation.</p>

<p>Full Field Pattern / Window Pattern<br />
<img src="/images/articles/waveform/patterns.jpg" alt="Full Field Pattern / Window Pattern" /></p>

<p>Window Pattern<br />
<img src="/images/articles/waveform/irisOFFwindow.jpg" alt="Window Pattern" /></p>

<p>Full Field Pattern<br />
<img src="/images/articles/waveform/irisOFFfull.jpg" alt="Full Field Pattern" /></p>

<p>As side note, you may have noticed that these two plots are not exactly the same and that, too, is an error and an unexpected error at that because as a lamp based display there is no reason for the light output to change. This error is directly related to the design of the product and it appears gamma shifting is still taking place for whatever reason. This was also reflected in the calibration of this projector. It was a moving target rarely providing an identical response when retested for the same parameter. The forthcoming review covers this in depth.</p>

<p>The two gamma plots that follow are with the iris on showing the system manipulating gamma based on image content. The dotted line presents the desired response curve.</p>

<p>Window Pattern<br />
<img src="/images/articles/waveform/irisONwindow.jpg" alt="Window Pattern" /></p>

<p>Full Field Pattern<br />
<img src="/images/articles/waveform/irisONfull.jpg" alt="Full Field Pattern" /></p>

<p>The goal of this article has been achieved. Testing clearly shows the system in action creating a non-linear response and also changing that response based on image content. What these tests do not reveal is where the system would be manipulating gamma within a variety of far more complex real world images. Unfortunately such depth is beyond the time and resources of this reviewer as well as article length so instead I provide my subjective observations.</p>

<p>When first implemented these systems clearly showed problems either from breathing of the iris or poorly implemented gamma manipulation. While products employing these systems for 2007 have greatly advanced eliminating obvious errors, you can't rob Paul to pay Peter without that image appearing flat in some aspect when it should not. If you have a reference of what the image should look like you will also recognize that such systems clearly change the response; it does not look the same. For the Panasonic, the net effect on the image was subtle for the most part providing a difference that perceptually did appear to improve dynamic range. Testing with common video sources there were no obvious image artifacts to be seen tipping its hand to the viewer but those sources also allow far more leniency with errors. That was not the case with a PC source which required I turn off the auto iris to get a proper image with some content due to a flattening of the upper IRE response. No matter what you may perceive using these products they will not do imaging science with the system turned on; the image is artificial and does not represent the original; it does not meet video industry standards. Someone who masters video or is involved with video setup for mass distribution should not be using a display with such a system that does not allow it to be turned off.</p>

<p><B>Turning it Off</B><br />
If the system has been employed simply to improve sales and marketing specs then turning it off is of no concern. As a system, in most cases you cannot turn off just one or the other feature but the <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/">ISF community</a> has figured out a way on some displays to turn off the gamma manipulation and manually adjust the iris for better blacks for a particular application. Some projectors employ a manual iris for that very purpose which comes with the side benefit of better intrafield contrast ratios but that does little to change the natural dynamic range of the imaging technology taking me to the next point.</p>

<p>It could be argued that transmissive LCD needs this process to create a competitive dynamic image and there may be others. I have yet to review any of the reflective LCD products yet based on what I am reading from other reviewers some versions appear to have similar properties. The problem here is natural dynamic range of the technology without any slight of hand. Transmissive LCD clearly suffers from this dilemma and if using CRT as our reference none of the microdisplay technologies qualify. On the other hand if we use film as our reference point for black current DLP microdisplay technology since 2006 has come quite close to that level of response with no gimmicks required.</p>

<p>A point of contention with this system occurs when making direct comparisons of performance. If I am building a DLP projector that meets video standards and setup a demo comparing it to transmissive LCD the main comparison is going to be how my projector looks without such a system and how the LCD looks with it turned off providing a direct comparison of the natural dynamic range of both technologies based on a calibrated response that meets video standards. Many have claimed such comparisons to be unfair yet scientifically speaking it is not only fair but required to compare apples to apples. Naturally the LCD will suffer. If the LCD is allowed to use the system then ultimately you will get two different images with the LCD potentially perceived as more dynamic and if both are pleasing to the eye the only argument left is accuracy, video standards and your perception. Choose!</p>

<p><B>In Perspective</B><br />
If you are looking for performance that meets video imaging standards the measurements, observations and science conclusively show that the use of a dynamic iris and gamma manipulation is clearly not the path of high fidelity imaging.</p>

<p>These kinds of imaging antics and tricks have been going on for decades and are nearly always related to a performance flaw of the technology, implementation and design or cost cutting. Using such tricks to yield better marketing and sales specs for a technology that performs adequately to begin with is old hat as well. Whether or not you want a product that forces or needs such tricks is your call.</p>

<p><B>Links</B><br />
<a href="/forum/viewtopic.php?t=3787">Contrast Ratio</a></p>

<p><a href="/forum/viewforum.php?f=103">Waveform Series</a><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>March 26, 2007 11:28 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(565)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 565)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php" type="text/javascript" charset="utf-8"></script>
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
