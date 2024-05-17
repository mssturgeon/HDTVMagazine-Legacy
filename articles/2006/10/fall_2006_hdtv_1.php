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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 465 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 465
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Fall 2006 HDTV Study Results&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php&amp;title=Fall 2006 HDTV Study Results">
		<span style="display:none">The results are in. From &lt;strong&gt;August 22nd, 2006&lt;/strong&gt; through &lt;strong&gt;October 1st, 2006&lt;/strong&gt;, HDTV Magazine conducted and sponsored The Fall 2006 HDTV Study. This article focuses on several key questions related to HDTV technology and how the results came out. The charts and data below reflect the preferences, buying habits, and general demographics of &lt;strong&gt;1281&lt;/strong&gt; study respondents. These respondents took the survey either as a subscriber to our services, or by navigating from a link featured on our home page. As such, the audience for this survey are primarily either owners of HDTVs or those in the immediate market (within 3 months of purchase) for an HDTV.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 465";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Fall 2006 HDTV Study Results" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Fall 2006 HDTV Study Results" />
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
	<title>HDTV Magazine - Fall 2006 HDTV Study Results</title>
	<meta name="keywords" content="next months, blu ray, intend purchase, dvd blu, home entertainment, respondents, hdtv, next, question, months, most, dvd, purchase, people, own, blu, ray, survey, intend, either, popular, surprise, home, important, programming" />
	<meta name="description" content="The results are in. From &lt;strong&gt;August 22nd, 2006&lt;/strong&gt; through &lt;strong&gt;October 1st, 2006&lt;/strong&gt;, HDTV Magazine conducted and sponsored The Fall 2006 HDTV Study. This article focuses on several key questions related to HDTV technology and how the results came out. The charts and data below reflect the preferences, buying habits, and general demographics of &lt;strong&gt;1281&lt;/strong&gt; study respondents. These respondents took the survey either as a subscriber to our services, or by navigating from a link featured on our home page. As such, the audience for this survey are primarily either owners of HDTVs or those in the immediate market (within 3 months of purchase) for an HDTV." />
	<meta name="title" content="Fall 2006 HDTV Study Results" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=465', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php">Fall 2006 HDTV Study Results</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 27, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=20&category=General Interest">General Interest</a></b>
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
				The results are in. From <strong>August 22nd, 2006</strong> through <strong>October 1st, 2006</strong>, HDTV Magazine conducted and sponsored The Fall 2006 HDTV Study. This article focuses on several key questions related to HDTV technology and how the results came out. The charts and data below reflect the preferences, buying habits, and general demographics of <strong>1281</strong> study respondents. These respondents took the survey either as a subscriber to our services, or by navigating from a link featured on our home page. As such, the audience for this survey are primarily either owners of HDTVs or those in the immediate market (within 3 months of purchase) for an HDTV. If you would like to see specific combinations of this data, or if you have ideas for questions or topics we should cover on future studies, please <a href="/help/feedback.php">let us know</a>.

<div align="center"><div class="alertbox" style="vertical-align:middle"><img src="/images/logos/internet-podcasting_75x40.gif" alt="Podcast" align="left" style="border:1px solid #D5CA8B; padding:0">Tune in to hear us talk about the survey on <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/">The HDTV Podcast</a> with Ara Derderian and Braden Russell. [ <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/archive/2006/October272006.html">Permanent Link</a> ]<br clear="all" /></div></div>

Below is a list of the questions covered in this article:

<ul>
<li><a href="#1">How many HDTVs do you own?</a></li>
<li><a href="#2">What type(s) of HDTVs do you own?</a></li>
<li><a href="#3">What is the size of your largest HDTV?</a></li>
<li><a href="#4">What type(s) of content do you watch most?</a></li>
<li><a href="#5">How do you receive your HD programming?</a></li>
<li><a href="#6">Are you able to receive digital programming, either over-the-air or via your current cable/satellite provider?</a></li>
<li><a href="#7">Are you able to receive HDTV programming, either over-the-air or via your current cable/satellite provider?</a></li>
<li><a href="#8">HD DVD or Blu-ray?</a></li>
<li><a href="#9">In deciding between HD DVD and Blu-ray, what is the most important factor?</a></li>
<li><a href="#10">Which is more important to you?</a></li>
<li><a href="#11">As it relates to high definition, for which of the following areas do you have an interest?</a></li>
<li><a href="#12">Approximately how much do you have invested in your home entertainment system (components, cables, media, etc)? (US Dollars)</a></li>
<li><a href="#13">Which of the following home entertainment system components and services do you own and/or subscribe to?</a></li>
<li><a href="#14">Do you intend to purchase an HDTV within the next 6 months?</a></li>
<li><a href="#15">If you intend to purchase an HDTV within the next six months, which type of HDTV are you most likely to purchase?</a></li>
<li><a href="#16">Are you planning on buying a Sony Playstation 3 (PS3) within the next 6 months? (assuming they stick to the 11/17 release date)</a></li>
<li><a href="#17">Assuming neither technology pulls ahead, are you planning on purchasing a next-gen DVD player within the next 6 months?</a></li>
<li><a href="#18">Which other home entertainment components and services are you planning to purchase in the next 6 months?</a></li>
<li><a href="#19">Other Comments</a></li>
</ul>

<br />
<h2><a name="1">How many HDTVs do you own?</a></h2>
<div style="float:left"><object id="30925705" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30925705%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=200">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30925705%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>Even though the audience for this study are primarily HDTV owners, it is pleasing to see that more than one third (37%) of respondents indicated that they had more than one HDTV, and about one in eight (13%) indicated they had 3 or more. <b>(1247 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="2">What type(s) of HDTVs do you own?</a></h2>
<div style="float:left"><object id="30926348" width="400" height="200" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926348%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926348%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="200"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This was one of the surprises of the survey. I would have expected LCD and Plasma technologies to be much closer, but nearly twice as many people had LCD televisions as did Plasma. It was also very interesting to see that LCD was ahead of both rear-projection and direct-view CRT displays. One thing I would like to know: How did 7 people have SED displays? They're not even on the market yet. <b>(1138 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="3">What is the size of your largest HDTV?</a></h2>
<div style="float:left"><object id="30926420" width="400" height="200" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926420%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=200&chartWidth=450">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926420%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=200&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="200"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The choices for this questions probably could have been better arranged to more accurately isolate the popular sizes of televisions. As they are, they range from 25 to 70 inches, in 5 inch increments. The most popular sizes are in the 46" - 50" and the 30" - 35" range. <b>(1148 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="4">What type(s) of content do you watch most?</a></h2>
<div style="float:left"><object id="30928488" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928488%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928488%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>Surprise #2 on the survey: Network primetime television is watched more than Sports, which contradicts several recent surveys. Although to be fair, this is perhaps a seasonal question, and Sports may indeed be more popular at other times of the year ... we shall see.</p>
<p>This question will be expanded on future surveys. We thought we hit the highlights here, but nearly 10% selected "Other" and provided answers other than what we suggested. <b>(1110 respondents)</b><br clear="all" /></p><p>Some of the most popular "Other" answers included:
<ul>
<li>All of the above</li>
<li>Anything HD</li>
<li>Education</li>
<li>News</li>
<li>Science</li>
<li>and Xbox</li>
</ul></p>

<br />
<h2><a name="5">How do you receive your HD programming?</a></h2>
<div style="float:left"><object id="30928311" width="250" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928311%26width%3D250%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928311%26width%3D250%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The only surprise with this question was how equally weighted all three options seemed to be. This question allowed multiple answers, and each option was selected by about 47% of respondents. <b>(1112 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="6">Are you able to receive digital programming, either over-the-air or via your current cable/satellite provider?</a></h2>
<div style="float:left"><object id="31136450" width="125" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136450%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=125">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136450%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>No surprise here, given that the audience was generally already familiar with HDTV. <b>(1111 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="7">Are you able to receive HDTV programming, either over-the-air or via your current cable/satellite provider?</a></h2>
<div style="float:left"><object id="31136460" width="125" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136460%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=125">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136460%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>No surprise here either. <b>(1111 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="8">HD DVD or Blu-ray?</a></h2>
<div style="float:left"><object id="31218392" width="400" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218392%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionText&chartHeight=125&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218392%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionText&chartHeight=125&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The first of several next-gen DVD questions. HD DVD does seem to be favored by a small margin (~4%), but one should keep in mind the timing of this survey: When this survey was conducted, Blu-ray was having significant (Samsung) player problems and the picture quality of the existing titles was questionable. Having said that, the interesting part about this question is that the vast majority are content to wait until there is a clear winner, rather than enjoying another form of high definition while they can. And given the investment each is making in their respective formats, it is quite possible that IF there is ever a clear winner ... it will probably not be apparent for at least 6 months to a year. <b>(1206 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="9">In deciding between HD DVD and Blu-ray, what is the most important factor?</a></h2>
<div style="float:left"><object id="31220019" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31220019%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31220019%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The results to this question came out about where we expected. Picture Quality being the clear most important factor, with Price and Titles both about equal. Most could apparently care less about the Brand or Capacity of the disc, although both of those do factor into the Picture Quality to some extent.</p><p>There was also an open-ended component to this question, where respondents could specify an "Other" important factor. 131 people specified "Other", so perhaps this questions option should be expanded on future surveys as well. <b>(1170 respondents)</b><br clear="all" /></p><p>Some of the most popular "Other" answers are listed below ... my favorite is the last one, which was mentioned several times:
<ul>
<li>All of the above</li>
<li>Backward Compatibility</li>
<li>Clear Leader/Winner</li>
<li>Combo Player</li>
<li>Playstation 3</li>
<li>Recordability</li>
<li>Not Sony</li>
</ul></p>

<br />
<h2><a name="10">Which is more important to you?</a></h2>
<div style="float:left"><object id="30926686" width="200" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926686%26width%3D200%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=450">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926686%26width%3D200%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>It should be no surprise that people preferred Quality to Quantity 3 to 1. This gap will widen considerably as more content makes the move to HD. In the next year we will see more content in the areas of Gaming, Satellite, and Packaged Media (HD DVD &amp; Blu-ray). There will also be SOME content coming available via Internet download ... but don't expect this to be a huge market for more than 12 months out. <b>(1205 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="11">As it relates to high definition, for which of the following areas do you have an interest?</a></h2>
<div style="float:left"><object id="30678608" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678608%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678608%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This question was primarily geared to help us determine which portions of our website should be improved or expanded to continue retaining the interest of our visitors. The answers here are about where I expect them to be, with the possible exception being Sports. I would have predicted that Sports would have been up there with Movies. This question will have an "Other" option on future surveys, as I'm sure there were other interest categories that would have been specified. <b>(1206 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="12">Approximately how much do you have invested in your home entertainment system (components, cables, media, etc)? (US Dollars)</a></h2>
<div style="float:left"><object id="30678643" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678643%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678643%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This is included mainly for your own comparison. There are not any juicy conclusions with this data ... so enjoy. <b>(1195 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="13">Which of the following home entertainment system components and services do you own and/or subscribe to?</a></h2>
<div style="float:left"><object id="30678656" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678656%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678656%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=250&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="250"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>It was a bit of a surprise to have an "audio" option lead on an HDTV-related study, but I suppose high-end audio goes hand-in-hand with high-end video. <b>(1187 respondents)</b><br clear="all" />Some other interesting comparisons:
<ul>
<li>CRT's are still more prevalent than flat-panel technology ... this will change</li>
<li>Cable &amp; Satellite are about even</li>
<li>More people own HDPC/Media Center's than own Xbox 360's</li>
<li>4x as many people own HD DVD players vs. Blu-ray</li>
<li>27 people own PS3's ????</li>
</ul></p>

<br />
<h2><a name="14">Do you intend to purchase an HDTV within the next 6 months?</a></h2>
<div style="float:left"><object id="31218024" width="125" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218024%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=125">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218024%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>1 in 4 intend to buy an HDTV within the next 6 months. This figure is included primarily to support the next chart. It is rather interesting that this works out to 305 respondents (24% of 1190), yet 670 answered the next question. I thought the next question was dependent upon a "Yes" answer to this one, but apparently it was not. <b>(1190 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="15">If you intend to purchase an HDTV within the next six months, which type of HDTV are you most likely to purchase?</a></h2>
<div style="float:left"><object id="31217160" width="400" height="200" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31217160%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31217160%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="200"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This is the second question on the survey that indicated LCD was more popular than Plasma. More than twice as many people intend to purchase an LCD television that do Plasma. And LCoS appears to be poised to overtake Plasma in the near future, aided no doubt by the new SXRD line from Sony. It is also interesting to note that there are still people who intend to purchase CRT televisions ... even though most manufacturs have gotten out of the CRT lines. <b>(670 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="16">Are you planning on buying a Sony Playstation 3 (PS3) within the next 6 months? (assuming they stick to the 11/17 release date)</a></h2>
<div style="float:left"><object id="31221468" width="425" height="100" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31221468%26width%3D425%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=425">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31221468%26width%3D425%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=600"
		FlashVars=""
		quality="high"
		width="600"
		height="100"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div><br clear="all" />
<p>Only one in 7 intend to purchase the PS3 when it comes out next month. Perhaps not a surprise given that the average age of respondents is not typical of the gaming generation. <b>(1183 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="17">Assuming neither technology pulls ahead, are you planning on purchasing a next-gen DVD player within the next 6 months?</a></h2>
<div style="float:left"><object id="31224566" width="175" height="100" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31224566%26width%3D175%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=175">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31224566%26width%3D175%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="100"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>Like the other HD DVD/Blu-ray above, this also indicates that most people are willing to wait it out rather than make another format mistake. HD DVD does again have a slight preference over Blu-ray, but it is negligable on a respondent pool of this size. <b>(1186 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="18">Which other home entertainment components and services are you planning to purchase in the next 6 months?</a></h2>
<div style="float:left"><object id="30678790" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678790%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678790%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This question clearly needs expanded to other options, given that over a third of all respondents provided an "Other" answer. Of those that selected something other than "Other", HD TiVo/DVR was the clear leader. <b>(616 respondents)</b><br clear="all" /></p><p>Some of the more popular "Other" entries:
<ul>
<li>DirecTV's new HD DVR</li>
<li>DirecTV's new MPEG-4 receiver</li>
<li>FiOS/Fiber Optic service</li>
<li>None</li>
<li>PS3</li>
</ul>
</p>


<h2><a name="19">Other Comments</a></h2>
<p>On the survey, we had an open-ended question: "What else is on your mind?". These will be collected and published in a subsequent article. We had 442 people take the time to submit their comments to us, and we'd like to take the time to digest each of these and respond to as many as possibly. So watch for that article at a later date. <b>(442 respondents)</b><br clear="all" /></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 27, 2006 11:09 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(465)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 465)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php" type="text/javascript" charset="utf-8"></script>
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
