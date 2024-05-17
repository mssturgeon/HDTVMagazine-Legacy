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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3540 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3540
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-2.php&amp;title=3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)">
		<span style="display:none">As I mentioned in part one, many experts in the HD industry, such as Mr. Gary Shapiro, president and CEO of the Consumer Electronics Association, compared the 3D CES movement of 2010 to when HD was introduced in 1998. 

Several factors make the two efforts similar: they are both based on digital technology, use the same digital distribution channels as 2D HDTV, use similar digital displays (with upgraded features for 3D), and both are as complex regarding having multiple formats, standards, conversions, compressions, connectivity requirements, etc. 

Unfortunately, due to that complexity, consumer confusion is also as high, if not higher. If local consumer electronics stores for years had difficulty learning HD right and to explain it correctly to consumers (and many have not reached that point yet even after a decade), I anticipate that 3D will be worse, and misinformation will fill consumers heads again. 

One could say that standards and compatibility should help … keep reading.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3540";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)" />
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
	<title>HDTV Magazine - 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)</title>
	<meta name="keywords" content="decade ago, set top, frame compatible, ces –, top boxes, light, glasses, hdmi, ces, could, image, resolution, decade, video, part, frame, brain, formats, format, same, digital, home, ago, quality, set" />
	<meta name="description" content="As I mentioned in part one, many experts in the HD industry, such as Mr. Gary Shapiro, president and CEO of the Consumer Electronics Association, compared the 3D CES movement of 2010 to when HD was introduced in 1998. 

Several factors make the two efforts similar: they are both based on digital technology, use the same digital distribution channels as 2D HDTV, use similar digital displays (with upgraded features for 3D), and both are as complex regarding having multiple formats, standards, conversions, compressions, connectivity requirements, etc. 

Unfortunately, due to that complexity, consumer confusion is also as high, if not higher. If local consumer electronics stores for years had difficulty learning HD right and to explain it correctly to consumers (and many have not reached that point yet even after a decade), I anticipate that 3D will be worse, and misinformation will fill consumers heads again. 

One could say that standards and compatibility should help … keep reading." />
	<meta name="title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-2.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3540', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-2.php">3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 2)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 16, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<div class="editorial">The following article is the latest in the "3D TV at CES 2010 – Was it Actually Like HD a Decade Ago?" series. Other articles in this series are as follows:<br> <ul> <li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_1.php" target="_blank">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 1)</a></li><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_3.php" target="_blank">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 3)</a></li><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_4.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 4)</a></li></ul></div><p><h2><b>Basic Similarities</b> </h2> <p>As I mentioned in part one, many experts in the HD industry, such as Mr. Gary Shapiro, president and CEO of the Consumer Electronics Association, compared the 3D <a href="http://www.cesweb.org/">CES</a> movement of 2010 to when HD was introduced in 1998.  <p>Several factors make the two efforts similar: they are both based on digital technology, use the same digital distribution channels as 2D HDTV, use similar digital displays (with upgraded features for 3D), and both are as complex regarding having multiple formats, standards, conversions, compressions, connectivity requirements, etc.  <p>Unfortunately, due to that complexity, consumer confusion is also as high, if not higher. If local consumer electronics stores for years had difficulty learning HD right and to explain it correctly to consumers (and many have not reached that point yet even after a decade), I anticipate that 3D will be worse, and misinformation will fill consumers heads again.  <p>One could say that standards and compatibility should help … keep reading.  <p><b></b> <h2><b>How Many 3D Formats/Structures? So Many? Again?</b> </h2> <p>Many may remember the 18 DTV formats of the ATSC standard Table 3 when approved in the mid 90s. Three simple options for signal resolution (480, 720, and 1080) mushroomed into 18 format possibilities when considering progressive/interlace, frame rates, aspect ratios, and horizontal resolution.  <p>3D has its own basket of multiple formats and structures as DTV did, also compounded when considering the 50Hz subset of formats (as DTV’s Table 3). Just recently, Steve Venuti from HDMI presented the following list of 3D structures at the <a href="http://www.3dathome.org/default.aspx">3D@Home Consortium</a> meeting I attended at <a href="http://www.cesweb.org/">CES 2010</a>:  <ul> <li>Full side-by-side  <li>Half side-by-side  <li>Frame packing  <li>Field alternative  <li>Line alternative  <li>Left + Depth  <li>Left + Depth + Gfx + Gfx Depth </li></ul> <p>Apparently, we may be heading toward a similar approach of “standardizing” the concept of “here is the pile, choose the format of preference”, as DTV did for content providers and TVs. Just as ESPN chose 720p and CBS chose 1080i, 3DTVs had their own choice of resolution as well (see the 3D section in my previously posted <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">HD World Conference</a> wrap-up).  <p><b></b> <h2><b>Many 3D Formats/Structures? Solution: Convert, Convert, Convert</b> </h2> <p>3D for the home uses passive or active shutter glasses. The old anaglyph color 3D glasses method is not covered in this series, but CES 2010 also showed several 3D demonstrations without using glasses, designed either for commercial purposes (like Thomson) or as a company’s technology statement.  <p>Although it would be ideal that eventually 3D would be glasses-free, the auto-stereoscope technology does not yet produce an image that could compete with the quality of the two methods above, and its 3D effect varies with viewer location, if it offers more than one. Therefore, this method is not covered in this series.  <p>As with DTV, tuners and set-top-boxes would have to be built to be compatible with any of the formats that can be fed to them, which would add complexity and cost; a set-top-box would have to convert the tuned format to a selectable output format, and the TV would convert the format to its own “native” format to show the image, if different.  <p>In other words, in addition to the DTV type of conversions, such as digital compression, resolution, and frame rate, there will be conversions of 3D signal structures to display devices capabilities.  <p>From the world of digital audio/video, although many generalize that bits are bits, we know that original signal quality suffers with conversions and compressions, and that brings the next point.  <p>In the next article I will analyze a possible scenario of a signal path of a 3D frame-compatible format such as the one satellite and cable companies are planning to distribute, a top/bottom format for example.  <p><b></b> <h2><b>Back to the Main Subject of this Article</b> </h2> <p>I admit that my initial reaction before CES was the same as Mr. Shapiro (3D seems similar to HD a decade ago). However, when looking beyond the expectations, the industry fervor for new sales, and the technology demos at CES, 3D and HD are not as similar as they appear in how they were introduced regarding content, distribution, and technology advances after a decade of digital video.  <p>In the first installment of this series, I mentioned five differentiators, which I briefly highlight below for your convenience:  <p><b>1) A variety of 3D content is expected from various sources since the introduction, </b>such as 3D Blu-ray pre-recorded media, 3D satellite, 3D cable, and possibly soon some 3D terrestrial broadcasts….  <p><b>2) Cable, satellite, and terrestrial broadcast plan to distribute 3D, but using frame-compatible lower resolution formats. </b>3D distributed content would have inferior resolution compared to what most 3DTVs would be capable of displaying, such as the active-shutter 3DTVs. This is the exact opposite of what HD experienced when implemented in 1998….<b></b>  <p><b>3) A widely adopted digital interface (</b><a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_a_digital_interface_solution.php"><b>HDMI</b></a><b>) has been well established in the industry</b>, as opposed to the first 4 years of HD with only component analog connectivity and no content protection….  <p><b>4) Regarding 3D interoperability protocols, version 1.4 of HDMI </b>introduced in mid 2009 already has 3D protocols for interoperability between 3D devices….<b> </b> <p><b>5) Firmware upgrades can be applied to earlier HDMI versions to implement 3D protocols in legacy devices….</b>  <p>Consult the previous article (<a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_1.php" target="_blank">3D TV at CES 2010 - Part I</a>) for details on the factors above.  <p>In this part II, I include four more factors (although there are more), as follows:  <p><b>6) Cable and satellite are planning to reuse existing STBs for 3D</b> and apply firmware upgrades for the 3D protocols needed for frame-compatible signal formats so 3D can be transported/distributed using the current bandwidth of HD. Earlier HDMI versions can also be enabled for 3D, as it was covered in point 5.  <p>The display device has to be 3D capable, even if you use two projectors. If the 3D signal is transported as frame-compatible format and the HDMI protocol upgrades were applied to the connected legacy equipment, the current HDMI wiring could also be reused for 3D if it was working with 1080p HD.  <p>This is not only favorable for millions of legacy set-top-boxes, but also for many in-wall legacy HDMI installations that could be reused for 3D without incurring any reinstallation/rewiring labor.  <p>It should be mentioned that while a cable set-top-box could be updated with firmware upgrades from the cable company, an A/V receiver connected in the 3D HDMI signal path could still be a problem for certain installations if it cannot transfer the 3D protocols to the 3D display device.  <p>In such a case, a 3D compliant HDMI splitter device capable of simultaneously sending the input HDMI signal to 2 or more outputs may be needed, one HDMI output from the splitter goes to the receiver for audio, and the other HDMI output goes directly to the 3D-capable TV for 3D video.  <p>Conversely, when HDTV was implemented, new HD set-top-boxes and internal DTV tuners had to be manufactured for satellite, broadcast, and cable. Satellite services required new dishes, analog component or DVI/HDMI wiring was required to transport the higher bandwidth of DTV between set-top-boxes and TVs (legacy S-video and composite cables commonly used at that time could not do that).  <p>In other words, 3D could reuse more from HDTV, than HDTV reused a decade ago from the technologies available then.  <p><b></b> <p><b>7) All major display manufacturers already implemented many digital technology advances in their HDTVs. </b> <p>DTV technology advances can be used as a platform to grow 3D, such as increasing the frame rate, the efficiency of the video processing, etc. on every display technology (LCD, plasma, DLP, LCoS, front projection, rear projection, etc).  <p>Such advances were not available when HD started in 1998; there were no digital TVs in the market to grow from.  <p>Compared to today’s video processing advances, first generation HDTVs included line-doublers of inferior video processing performance. The advances made for 3D capabilities on new TVs should also produce better 2D images on the same displays for HD viewing.  <p><b></b> <p><b>8) Many 3D HDTVs have included the ability to convert a 2D source image “on-the-fly” to display it as 3D. </b> <p>Many in the industry see this factor as a key facilitator for the early adoption of 3D by consumers; a facilitator considered even larger than early HDTVs upscaling regular DVDs.  <p>However, the quality of the conversion, if substandard, could negatively affect the adoption of 3D.  <p>In a later article, I will discuss performance details of this 2D-to-3D “on-the-fly” conversion as I have seen in the latest 3D conferences, including CES 2010.  <p><b></b> <p><b>9) Viewing 3D with glasses reminds me of a decade ago, but for opposing reasons.</b>  <p>When HDTV arrived in 1998, the quality of the picture was so good compared to analog NTSC that I ordered another set of prescription glasses to make sure I was able to appreciate every bit of image quality.  <p>However, 3D feels like wearing “dark-glasses” in a bat-cave-home-theater. Home theaters usually control the external light so it does not get into the room and the lumens produced by the projector are maximized when reaching the screen. Light is a precious asset in a home theater.  <p>The concept of increasingly pursuing a brighter image applies to all display technologies. The high light output of LCD is an example, which impressed many people at showrooms. Brighter LCD screens sold many more TVs than better quality images of plasmas, even when consumers were told how to identify the factors that make a plasma image better than LCD.  <p>In other words, light output was, and still is an attractive feature and consumers continue to buy new LCDs by a large margin. The effort made by the industry over the last decade to squeeze more luminance out of light engines and increase contrast-ratio so DTVs can produce an image with more impact (mainly in showrooms), brings me to this question: Are we then going backwards in that respect, wearing “dark-glasses” for the sake of 3D depth?  <p>Several industry experts provided a range of estimates and actual measurements of the amount of light that is lost as perceived by the eyes when the display device switches to 3D, by polarizing filters, by ZScreens, by the 3D glasses, etc.  <p>Depending on the 3D installation (<a href="http://www.barco.com/projection_systems/downloads/Barco_stereoscopic_proj.pdf">Barco 3D projectors for example</a>), type of 3D glasses, and without any screen gain (silver or white), the amount of light that ultimately reaches the eyes was measured to drop from 100% to a 3D level of 59% (loss of 41%, dual LCD projectors with internal polarization and passive 3D glasses), with a worst case scenario of 12% (88% loss, single projector running in active stereo mode with external alternating polarizing ZScreen and passive 3D glasses).  <p>Other 3D implementations measured in between those percentages depending on the type of projector, single or pair, active shutter glasses, polarization methods, etc.  <p>Is that dark enough for you? Maybe not if the content is so immersive, such as Avatar, that makes you forget about the possibility of a 100% light output, that you would not have the chance to see at that very moment in a 2D version of the same content with a 2D screen, no filters, same projector, no glasses, etc.  <p>However, what about having that chance at home, where you may make a comparison if you have the setup? Would the comparison frustrate you, or you would still accept 3D dark as it is, a reasonable compromise for the sake of depth?  <p>Of course, anyone expected to make money from 3D at your home would avoid discussing the subject in detail, or would say that the 3D glasses would also reduce the perception of ambient light surrounding the darker image, keeping a proportion of overall darkness perception of image/room. However, the light loss attributed to the 3D glasses is only a small part of the total loss of brightness (-16% on the above two applications by Barco).  <p>The feeling of going backwards may be worse if reflecting over a decade of improved HD image quality, increasingly brighter and contrasted, and been told that, additionally 3D at home will be distributed with only half of the resolution of a 2D image per eye, knowing that the original is sitting in some place at 100% resolution and is used by 3D Blu-ray.  <p><b></b> <h2><b>Is that What the Brain Thinks when Viewing 3D?</b> </h2> <p>The human brain dedicates 25% of its power to vision, but I am not sure it can do miracles with so much loss in resolution and light, although <a href="http://www.quantel.com/repository/files/whitepapers_s3dmarch19th.pdf">some say it could, like Quantel</a>, without being very specific about it.  <p>I recommend for you to see the <a href="http://www.charlierose.com/view/interview/10727">second episode of the Brain series by Charlie Rose</a> where many interesting aspects about how the brain works with vision are explained, such as the limited information the eyes “choose” to register, having more clarity at their center (acuity) and less detail on the perimeter, unlike two video cameras evenly registering all details about the same objects.  <p>The retina captures millions of pieces of information (part of the same brain but located in the back of the eye for anatomical reasons), but compresses them 100:1 when transported over the optical nerve. Acuity, selective detail, compression… Maybe having half-resolution and low light is the least of the problems. Sounds like HDTV compressed with MPEG-2 over coax to your home.  <p>Compound that scenario with the fact that when viewing 3D video the eyes are not actually registering information from the same set of real objects seen by the cameras, but from a manmade representation of how they should be perceived with depth by the brain.  <p>Considering the restrictions of light and resolution of 3D video images, it seems 3D viewing could be made pleasant by an increased brain activity that adapts to all those physical and technological limitations. What would then be the threshold on those factors, under which the brain could no longer be pleased (fooled) with manmade 3D? Do you think the 3D industry is not exploiting those factors by assuming the average person cannot see or hear a certain level of detail?  <p>In other words, would a frame-compatible half resolution image from ESPN DirecTV seen at 12% of light be judged “by the brain” as so dramatically lower in quality than full frame images seen at 100% of light (if that would be possible)? <a href="http://www.panasonic.com/3d/explore-the-technology.aspx">Panasonic</a> and 3D Blu-ray claim so; does the brain think the same?  <p>This subject is being debated by science and video technologists while James Cameron provided a great opportunity with his Avatar creation simulating 3D reality to motivate a more interesting analysis of all of the above.  <p>As some claimed at CES (of course the RealD glasses manufacturer was one), TVs are being designed to boost the light output when detecting an incoming 3D image to partially compensate for the darkening effect of wearing the “dark-glasses”. The TV manufacturers I met with did not confirm such claim, but the 3D images I have viewed from all the technologies still do not compete with the brighter punch of the 2D HD version, regardless how much my brain could have tried to compensate, maybe I was tired after so many meetings.  <p>In part 3, I will expand the subject of 3D further. Stay tuned. You may remove your 3D glasses now.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 16, 2010  6:05 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3540)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3540)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-2.php" type="text/javascript" charset="utf-8"></script>
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
