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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 293 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 293
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Analysis of DTV Content Protection Rulings and Agreements&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/02/analysis-of-dtv-content-protection-rulings-and-agreements.php&amp;title=Analysis of DTV Content Protection Rulings and Agreements">
		<span style="display:none">The following article originally appeared in HDTVetc magazine in their April 2004 issue. For your convenience, and to facilitate the understanding of the reading of this complex subject, please refer to page the end of the article where you will find a graphical representation of the application of the DTV Rulings and Agreements.

I would like to indicate that the content has an historical value dated back to April 2004, but is still very close to the current situation, except for some events that have taken place after the article was published. One of which was the over-turning of the &quot;Broadcast Flag&quot; FCC mandate, where the court ruled that the FCC had  over-stepped its authority.

This subject has been covered by the press and other magazines over time in bits and pieces.  In order to provide you with a complete perspective, I have prepared this simplified analysis of the approved FCC rulings, the areas not ruled on yet, and how their integration could affect you as an HDTV adopter.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 293";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Analysis of DTV Content Protection Rulings and Agreements" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Analysis of DTV Content Protection Rulings and Agreements" />
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
	<title>HDTV Magazine - Analysis of DTV Content Protection Rulings and Agreements</title>
	<meta name="keywords" content="content protection, broadcast flag, broadcast content, dvi hdmi, component analog, content, digital, broadcast, protection, flag, fcc, cable, analog, copy, equipment, could, dtv, connections, res, ieee, agreement, new, hdtv, dvi, resolution" />
	<meta name="description" content="The following article originally appeared in HDTVetc magazine in their April 2004 issue. For your convenience, and to facilitate the understanding of the reading of this complex subject, please refer to page the end of the article where you will find a graphical representation of the application of the DTV Rulings and Agreements.

I would like to indicate that the content has an historical value dated back to April 2004, but is still very close to the current situation, except for some events that have taken place after the article was published. One of which was the over-turning of the &quot;Broadcast Flag&quot; FCC mandate, where the court ruled that the FCC had  over-stepped its authority.

This subject has been covered by the press and other magazines over time in bits and pieces.  In order to provide you with a complete perspective, I have prepared this simplified analysis of the approved FCC rulings, the areas not ruled on yet, and how their integration could affect you as an HDTV adopter." />
	<meta name="title" content="Analysis of DTV Content Protection Rulings and Agreements" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/02/analysis-of-dtv-content-protection-rulings-and-agreements.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=293', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/02/analysis-of-dtv-content-protection-rulings-and-agreements.php">Analysis of DTV Content Protection Rulings and Agreements</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February  9, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p class="editorial">The following article originally appeared in HDTVetc magazine in their April 2004 issue. For your convenience, and to facilitate the understanding of the reading of this complex subject, please refer to the end of the article where you will find a graphical representation of the application of the DTV Rulings and Agreements.<br />
<br />
I would like to indicate that the content has an historical value dated back to April 2004, but is still very close to the current situation, except for some events that have taken place after the article was published. One of which was the over-turning of the "Broadcast Flag" FCC mandate, where the court ruled that the FCC had  over-stepped its authority.</p>

<p><br />
This subject has been covered by the press and other magazines over time in bits and pieces.  In order to provide you with a complete perspective, I have prepared this simplified analysis of the approved FCC rulings, the areas not ruled on yet, and how their integration could affect you as an HDTV adopter.</p>

<p><br />
<h2>'Plug-and-Play' Cable Agreement (Applicable to Satellite)</h2><br />
In issue 3 of the HDTVetc magazine, I wrote an article about integrated tuners, which briefly touched the subject of content protection within the 'Plug-and-play' cable agreement approved by the FCC. This was from the perspective of hardware functionality and cost/benefit analysis of integrated vs. separate HD tuning devices, for satellite, cable, or over-the-air reception.</p>

<p>In this article, I will go deeper into the subject of content protection to analyze the combined effect of the 'Plug-and-play' cable agreement, together with the 'Broadcast Flag' ruling, and the 'analog hole' issue, not ruled yet.  I will explore the ramifications when interacting with each other when viewing and recording HD content.  I will also analyze the areas of the rulings that seem not specific or not clear enough.  </p>

<p>As stated on the previous article about the subject, in December 2002, an announcement was made of an agreement between the consumer-electronics and cable television industries regarding digital cable interoperability as follows: </p>

<p><i>"The agreement is part of a broad 'memorandum of understanding' between the two industries that is intended to lead to a 'plug-and-play' standard that was needed to link digital cable equipment and services with consumer electronics devices.  Once the Federal Communications Commission approves the agreement, it is expected to help speed the adoption of HDTV.</i></p>

<p><i>The memorandum, along with a letter to FCC chairman Michael Powell, was signed by 12 consumer electronics companies and seven major cable multiple system operators (MSO) representing more than 75 percent of all cable subscribers.  The memorandum is a package of voluntary commitments, specifications and proposals for rules covering digital television (DTV) cable hardware compatibility and content protection, and the FFC is expected to approve the recommendations".</i></p>

<p>The plan includes the phased-in use of two digital interface connectors on new digital cable-ready TVs and/or cable set-top converter boxes.  </p>

<p>Those are: a) IEEE1394 'FireWire' connections with Digital Transmission Content Protection (DTCP) for recordable and networkable compressed video streams, and b) the non-recordable DVI/HDMI with High-bandwidth Digital Content Protection (HDCP) connections on digital televisions and cable set-top boxes.</p>

<p>The agreement prohibited Multi-channel Video Programming Distributors (MVPDs), mainly cable but also applicable to satellite providers, who use STBs with both 'FireWire' and DVI/HDMI connectors to switch the outputs in order to restrict lawful recording ('selectable output control').  The agreement also included encoding rules to 'copy freely, once, or never', depending on the content, modeled from the Digital Millennium Copyright Act.  The CEA announced in September 2003:</p>

<p><i>"Digital cable ready HDTV owners will be provided with a secure CableCARD to be inserted into the digital receiver in order to comply with varying degrees of content copy protection levels and prevent theft of cable service.  For instance, at least one copy of a digital channel sold by monthly subscription (e.g. basic and HBO) may be made for private and personal use, whereas premium pay-per-view and video-on-demand programs may be marked as copy never (originally as copy once).  Free over-the-air broadcast signals may be copied freely, and may not be reduced in resolution ("down-res'd") when output from unprotected high definition analog ports."</i></p>

<p><i>"Significantly, legacy DTV set owners also are protected by this agreement, which bans the use of "selectable-output-controls," which would have enabled content providers to control content delivery to households from the head end.  Without the plug-and-play agreement's encoding rules, consumers who purchased introductory HDTV sets not equipped with copyprotection-designed digital outputs could be disenfranchised and altogether denied HDTV services and programming.  This agreement ensures that today's DTV products will not be made obsolete in the course of a transformation to nationwide digital video delivery over cable.  <u>But selectable output controls may some day in the future be used.</u>" (Underline added).</i></p>

<p>I would like to mention that even though the spirit of the agreement was protective of the investment made by millions of early adopters of HD equipment with just analog connections, some loose ends were left open for further resolution (read as 'further negotiation with the MPAA').  </p>

<p>One loose end is the underlined statement above (<i>But selectable output controls may some day in the future be used);</i> another loose end is the possibility of 'down-res' non-broadcast content over legacy component analog connections (the FCC prohibited 'down-res' but <u>only</u> for broadcast content).</p>

<p>As approved almost a year later, the ruling set the following deadlines: Starting April 1, 2004, cable operators must supply upon request HD-STBs with functional IEEE1394 'Firewire' connectors, and by July 1, 2005, all HD-STBs would also require a Digital Visual Interface (DVI) or High Definition Multimedia Interface (HDMI), both protected with High-bandwidth Digital Content Protection (HDCP). </p>

<p>These digital connections would permit protected HD viewing (DVI and HDMI with HDCP) and recording (IEEE1394 with DTCP), depending upon the copy protection rules applied to such content (copy freely, once, or never).</p>

<p>The 'Plug-and-play' cable agreement decisions (copy freely, once, never; and 'no-down-res' of broadcast content) were also applied to satellite service providers (which are MVPDs as well).  At that time, they said they were not a party on this FCC decision, and declared that it was not the end of the process. </p>

<p><br />
<h2>'Broadcast Flag'</h2><br />
Not as glorious as the flag we all defend and love. </p>

<p>In November 2003, to limit the indiscriminate redistribution of digital broadcast content, the FCC approved the 'Broadcast Flag' anti-piracy order.  A digital code embedded into a digital broadcasting stream would signal DTV reception equipment to activate the redistribution limit.  The mandate will take effect in July 1, 2005.  </p>

<p>The FCC allowed broadcasters to decide whether or not to include the flag with specific types of programming, but declined to prohibit the use of the flag with regard to certain types of programming, such as news or public affairs, an issue that consumers and free-speech advocacy groups had demanded not to restrict.</p>

<p>Two of the five commissioners disagreed with the section that dealt with restricting also news programs, and content with expired copyrights, which would affect the sharing of such video clips over the Internet. </p>

<p>This regulation excludes digital devices not built with internal digital tuners, such as existing digital VCRs, DVD players, personal computers, etc.  According to the FCC ruling, all existing equipment incapable of reading the broadcast flag, such as televisions, VCRs, DVD players, will remain fully functional.</p>

<p>The new rules still allow consumers to make digital copies of broadcast HD content; they are intended to prevent only the mass distribution over the Internet, and to encourage availability of 'high value content' on broadcast television by discouraging its migration to more secure platforms such as cable and satellite TV service, according to the FCC.</p>

<p>In the words of the FCC ruling documents, "The broadcast flag protects consumers use and enjoyment of broadcast video programming.  The flag does not restrict copying in any way".</p>

<p>A demodulator (within equipment capable of tuning DTV) that complies with the flag mandate could still send the tuned signal to the analog component outputs of the device, but only to those digital outputs that meet with a copy-protection technology approved by the FCC (possibly 5c). </p>

<p>The FCC still needs to go thru the process of approving those future copy-protection broadcast-flag technologies; several are already pre-approved, including 5c.  Companies that are part of the Broadcast Protection Discussion Group developed those technologies.  Vendors of a particular content protection or recording technology need to be certified by the FCC in that such technology is an appropriate tool to give effect to the broadcast flag. <br />
  <br />
In another statement of the FCC documents it is indicated: "We concur and find that redistribution control is a more appropriate form of content protection for digital broadcast television than copy restrictions.  This determination is in keeping with our earlier decision to prohibit copy restrictions on unencrypted digital broadcast television when retransmitted on MVPD systems."</p>

<p>Following are some interesting fragments of comments made in the proceedings of this ruling:<br />
"If first run DTV broadcast content were freely available over the Internet, then secondary, international and web cast markets could be threatened"; </p>

<p>"MPAA cautions that if current trends in compression efficiency, storage capacity and broadband speed persist, then in a few years it will take less time to download a high definition movie than to watch it";</p>

<p>"Critics suggest that this threat is overstated and that limits to existing broadband capacity will prevent widespread Internet retransmission of high definition digital content for the immediate future.  One estimate indicates that it could take as much as four days to upload a one hour HDTV broadcast program to the Internet at standard consumer broadband speeds".</p>

<p><br />
<h2>Other Technology Alternatives to the Flag?</h2><br />
Some sources in the ruling suggested that the flag could be easily circumvented by using digital to analog converters and that there is concern that the presence of component analog outputs on ATSC tuners would provide a weakness point for the protection offered by flag recognition technology, because it would remove the content protection ('analog hole'). </p>

<p>Non-compliant legacy devices will output content without recognizing the flag, critics say "non-compliant hardware or software demodulators could be produced with relative ease by individuals with some degree of technical sophistication."  </p>

<p>Several technological options, such as watermarks and forensic fingerprinting, were discussed with the inter-industry Analog Reconversion Discussion Group ("ARDG").  Digimarc, Macrovision, and Philips supported the use of watermarking to secure DTV broadcast content.  Philips also argued, "an encryption regime should be considered critically because it could potentially limit the playback functionality of legacy recording equipment."  </p>

<p>Other sources on the FCC proceedings recognized that encryption would not resolve the 'analog hole' problem, and commented that the watermarks mechanism was considered a more complete solution than the ATSC flag since watermarks are embedded within content and can survive digital and analog processing as well as format conversion, which makes it suitable for redistribution control purposes and also to address the analog hole.  Watermarks could even be used as a complementary method, in addition to the broadcast flag, to solve the issue of component analog outputs.</p>

<p>Some specific comments on alternate mechanisms stated "Digimarc and Macrovision assert that the implementation costs for watermarking are similar to the costs associated with a flag regime, and that watermarks can be made backwards-compatible with legacy devices.  In a similar vein, Philips believes that a specific type of watermarking technology known as fingerprinting may evolve into an appropriate mechanism to address both redistribution control and analog hole concerns."</p>

<p>Even when considering the robust security generally associated with encryption technologies (as stated in the proceedings), it was anticipated that the implementation costs and delays would make it a less desirable content protection system for DTV broadcasts than the ATSC flag.</p>

<p>The proceedings viewed the obsolescence of legacy equipment as particularly burdensome on consumers, and there was no enough evidence to support that "the security benefits gained from encryption outweigh the costs that would be levied on consumers." </p>

<p>In another comment from the proceedings, it was expressed that "given the anticipated growth in DTV equipment sales over the next few years, we conclude that the development time needed for an encryption system would exacerbate the existing legacy problem and frustrate early adopters.  As such, we decline to adopt encryption at the source as a content protection mechanism for DTV broadcasts." </p>

<p>Regarding watermarks and fingerprinting it was resolved that "as new content protection technologies develop, watermarking and fingerprinting may emerge as useful tools to protect DTV broadcasts.  At this time, however, the record reflects that these technologies are insufficiently mature for implementation."  </p>

<p><br />
<h2>'Down-res'</h2><br />
The 'Down-res' nightmare is already six years old, and is still haunting us, what is 'Down-res'?  </p>

<p>Imagine tuning to an HD signal (1080i or 720p resolution) and not been able to view/record it as true HD, but only as an SD downgraded version of it (480i resolution).  This is not due to DTV equipment technical limitations, which we all know there are from the camera to the home, it is intentionally imposed by the content provider, reducing it to 16% of the 1080i original version (1080ix1920 = 2,073,600 down converted to 480ix704 = 337,920, both at 30 frames per second rate).  </p>

<p>This assumes (the dream) that the full potential of the HD interlaced 1080i standard is/could be actually recorded with capable cameras and transmitted as 1080ix1920, and can actually be displayed at your home as 1080ix1920.  There are in the market a few high-quality (expensive) displays that can resolve the full 1080i resolution (9" CRTs, and some fixed pixel displays), but most consumer displays do not.  However, even with such limitations, 480i is still a severe downgrade that can be easily visualized when compared to 1080i, especially in the large screens of most HDTVs of today.  </p>

<p>In other words, if you invest on an HDTV and an HD-STB without protected digital connections you run the risk of eventually not been able to view some HD protected content as true HD when using their component analog connections.</p>

<p>Before getting deeper into the subject, a little bit of history could be useful.  Since the first introduction of HD-STBs in 1998/9, it was known that satellite service providers have the capability to deactivate from their end the analog component connections ('selectable output controls') of the HD-STB, or to reduce the resolution ('down-res') on such connection, when/if the owner of the content requires the MVPD to protect the program. </p>

<p>Dish Network was known to oppose to such practice and hoped never to have to use the option; as a contrast, DirecTV never opposed to it and tested HDCP as a copy protection method over a DVI digital connection over a year ago; that gave us a warning sign.</p>

<p>Judging by DirecTV's continuous resistance to the installation of IEEE1394 outputs on their HD-STBs, so subscribers cannot archive/record HD content on D-VHS tape (and no networking in HD), it would not be a revelation if DirecTV would actually end up implementing 'down-res' on non-broadcast content over analog connections (see below their request of February 13, 2004).     </p>

<p>Even the first DirecTV HD-STB introduced six years ago (the respected RCA DTC-100) with only component analog outputs, highlighted in the user manual, and in the packaging box, that some HD programming might not be viewed with this HD receiver, the actual disclosure follows:</p>

<p><i>"Due to copyright restrictions, you may not be able to record or view some high definition programs in high definition format using this product.  To view this type of programming in standard definition format, you must also connect the Audio/Video jacks to the monitor."</i></p>

<p>The 'down-res' objective was implemented as 'selectable output control', which forced the viewer to switch to the regular TV inputs to at least view 'something'.  Newer STBs are designed to perform the 'down-res' over the same component analog connection, no need to switch TV inputs; either way, the resolution of the viewing is degraded to SD.  <br />
 <br />
Although the industry started in 2003 to include digital connections (DVI, HDMI) in a large number of HD equipment, the vast majority of the 9 million HDTV sets and STBs sold until today still have only component analog connections.</p>

<p>As stated before, the FCC already ruled by prohibiting MVPDs to 'down-res' to protect early adopters in the 'plug-and-play' cable agreement, which also prohibits the use of 'selectable output controls'.  However, the 'down-res' prohibition was for broadcast content only, as follows:</p>

<p>"<u>Down-resolution</u> - Down-resolution (reducing the resolution of high-definition programming to standard-definition) is prohibited for broadcast programming by all MVPDs; the FCC said that down-resolution of non-broadcast programming will be addressed in the Further Notice.  In the interim, MVPDs intending to use down-resolution for non-broadcast programming are required to notify the FCC at least 30 days in advance."<br />
 <br />
In February 13, 2004, DirecTV requested to the FCC to allow them to 'down-res' certain non-broadcast programs over the component analog connections of their HD-STBs, anticipating that some movie studios will not make available to them some of the more popular movies if using unprotected outputs.   </p>

<p>The Consumer Electronics Association declared that they want the FCC to keep the 'plug and play' rule of 'no down-res' as introduced, although what DirecTV is requesting is for non-broadcast content, the 'analog hole', which was not yet addressed by the FCC.</p>

<p>Due to remaining negotiations with the MPAA regarding the protection of their content over analog connections ('analog hole'), and also due to DirecTV's recent request for 'down-res', millions of early adopters are haunted again with the possibility of restricting HD viewing on legacy equipment with analog connections. </p>

<p>The FCC, the CEA, the MPAA, and the MVPDs, would have to employ all of their negotiation skills to solve this matter to the satisfaction of everyone, not to mention the right of a consumer to watch a paid HD movie/premium channel in its full resolution.  </p>

<p><br />
<h2>Some analysis; and some loose ends</h2><br />
The term "indiscriminate redistribution" used in the content-protection order of the Broadcast Flag is to prohibit Internet redistribution to stop piracy (if anything could actually ever stop piracy).</p>

<p>New HD PVR devices record content for time shifting.  Some soon to be released PVRs later this year from VOOM and others, also perform as home servers that store HD content able to be distributed to other network devices throughout the house. </p>

<p>The new ruling is not specific enough about how a proposed content protection technology (including those the FCC did not approve yet) would allow HD home networking functionality for personal use, when the concept is very similar to the redistribution over the Internet (network), which flag prohibits sending protected content to even another device of your own for personal use.</p>

<p>The protection system includes combined technologies (approved and to be approved) that could have the potential of also restricting personal archival in D-VHS tape of a content already stored on a (fully) compliant digital HD-PVR (used for time-shifting purposes) if the content is flagged as copy-once.  The system might consider the D-VHS taping as a second-generation copy, although the first generation in the PVR is not actually a permanent storage device copy for archival purposes.  </p>

<p>Hopefully this would not happen if the technical implementation of the new system would be intelligent enough to allow D-VHS archiving while auto-erasing the source program stored on the PVR, so only one copy would exist.</p>

<p>Equipment purchased before July 2005 escapes the 'Broadcast-Flag' ruling; that might set in motion a large number of purchases to occur before that date, but there is a risk for that equipment to end up having limited functionality when matted with compliant devices under the new protection technologies.</p>

<p>A flag-compliant DVD recording device might make a DVD recording that might not be playable on existing non-compliant DVD players, which could not decrypt the copy-protected signal.  This could make an existing legacy DVD player limited from its original functionality, an issue that some groups indicate it could unfairly force consumers to buy new DVD players, although the FCC expressively states that "will not require consumers to purchase any new equipment". </p>

<p>It is also undefined how MVPDs, such as cable and satellite TV operators, who retransmit DTV over-the-air broadcasts, would actually be allowed/required to encrypt the digital content to maintain the flag's intent.</p>

<p>The FCC ruling gives them "the latitude to implement the flag as appropriate for their distribution platforms, whether it be through direct pass-through or by effectuating the flag's intent through their own conditional access system", for which the FCC "is seeking further comment from MVPDs" and also states "MVPDs may not assert greater redistribution control protection for digital broadcast content than that which the broadcaster has selected.  In the case of content which a broadcaster has not marked with the flag, MVPDs must deliver that content to subscribers in a manner that reflects and gives effect to its unflagged status."</p>

<p>Some cable MVPDs were considering encrypting the entire basic tier to effectuate the flag's intent of the HD broadcast channels of the tier; imagine the implications to cable subscribers that currently do not use/need a separate STB for decrypting a basic tier (as with on-the-clear QAM cable tuners into integrated HDTVs).  </p>

<p>When we have a better definition of the combined effect of the content protection technologies (with the ones to be proposed, courtesy of the MPAA), we might be able to confirm how the full extent of the content protection system would actually work with the mix of new and legacy devices.  Although, in "spirit", the following was disclosed so far:</p>

<p>From the 'Plug-and-play' cable agreement: "<u>Approval of New Connectors and Content Protection Technologies</u> - The DFAST license anticipates FCC appellate oversight in cases of dispute over CableLabs determinations regarding the use of new connectors and content protection technologies".</p>

<p>From the 'Broadcast Flag' ruling: " ...  the FCC established an interim policy that allows proponents of a particular content protection or recording technology to certify to the FCC that such technology is an appropriate tool to give effect to the broadcast flag, subject to public notice and objection.  The FCC's interim certification decisions will be guided by a series of objective criteria aimed at promoting innovation in content protection technology". <br />
	</p>

<h2>What could you do?</h2>
In addition to contact your representatives, the FCC, and your MVPD, to let them know that you want your HDTV investment and viewing/recording rights protected, there are several things you could do to make your transition to HDTV as safe as possible considering the circumstances. 

<p>Given that copy-protection of HD premium content, and prohibited digital redistribution of broadcast content, are both here to stay, to be on the safe side buy equipment that can handle DVI or HDMI digital connections.  Make sure the digital connection is HDCP compliant, in addition to having the typical component analog connections (RGBHV, as 5 BNC or 15 pin D-sub VGA; or YPbPr wide-bandwidth 3 connectors, etc).</p>

<p>HDTVs that have more than one DVI/HDMI input would provide better flexibility to connect multiple HD components suited with such outputs (HD-STB, DVD player upconverting to HD, HD scaler, etc.).  The insufficiency of DVI/HDMI inputs in TVs is starting to become a problem this year; one option is to install a DVI/HDMI switcher; make sure any equipment you buy that uses DVI or HDMI is HDCP compliant, including the switcher.      </p>

<p>Buy HD tuning equipment also suited with IEEE1394 (FireWire) connections to facilitate HD networking and HD recording on D-VHS tape of content permitted to be copied (copy freely, copy once).  IEEE1394 connections should also be present on an integrated HDTV that has a built-in HD tuner, make sure the connection is bi-directional, and not only a digital iLink input to connect digital camcorders.  </p>

<p>Avoid buying an integrated HDTV set that does not have IEEE1394 to send the tuned signal out for HD recording, which also provides the choice to connect an external PVR (like the tuneless RCA DVR10 $450) to time shift the program tuned by the integrated HDTV.</p>

<p>Regarding the availability of equipment suited with IEEE1394, the market offers the following:</p>

<p>a) Cable has been mandated to provide that connection by April 2004 when/if the customer requests an HD-STB suited with IEEE1394, try better leasing the box so the evolution on content protection technologies (and equipment failure) would not impact your pocket, just say 'send me another box', which also would facilitate the later DVI/HDMI upgrade from the MVPD for 2005, at their cost; </p>

<p>b) Some Over-the-air ATSC HD-STBs have IEEE1394; some also include an internal PVR for time shifting; some have also a QAM cable tuner; </p>

<p>c) Dish Network has IEEE1394 jacks on their recently introduced 921 PVR (although its activation was announced for later in the year); it also has an ATSC over the air tuner; JVC has a sibling of this unit; when 1394 is activated this unit will have all the connections, at $1,000.</p>

<p>d) VOOM satellite will have IEEE1394 on their new Motorola 580 HD-STB expected for mid 2004; this is a PVR server centerpiece of a home-network with thin clients; the company 'might' offer an upgrade path for the owners of their original 550 model (which does not have, nor will be upgraded for, IEEE1394).  The thin clients will also have IEEE1394, but being a network, the system of content protection (including the "Broadcast Flag") technologies might eventually impose limitations on distributing the digital signal.  It is too early to know 'exactly' what would/not work, not because of VOOM; VOOM would have to implement the rules as they come.  </p>

<p>e) DirecTV has been avoiding the offering of IEEE1394 since DVI was introduced a few years ago; the new STBs announced for 2004 still omit such connection; however, if you already are a DirecTV subscriber, and HD tape recording is a requirement, you might want to opt for an aftermarket modification such as http://169time.com/, which would enable 'some' satellite HD-STBs by adding IEEE1394 outputs (the modification costs more than the STB, but it might be worth for you).  </p>

<p>As a DirecTV subscriber, if you just want to time shift HD content (no need for recording and networking), the newer DirecTV PVR/Tivo model HD-DVR250 from Hughes ($1,000), announced to become available April 2004, might be all you need.</p>

<p>In general, as with any first release model, especially HD-STBs, it is a good idea to research on Internet forums (i.e., <a href="/cgi-bin/ntlinktrack.cgi?http://www.avsforum.com/">www.avsforum.com</a>) and reviews to make sure there are no serious problems with the unit of your interest.  A good number of STBs from MVPDs receive gradual online firmware upgrades to correct reported problems, and improve the STB operation and performance, free.</p>

<p>For a more complete analysis of the subject of digital connectivity you could benefit by reading an updated version as of CES 2004 Report (Jan 04):</p>

<p><a href="http://www.hdtvmagazine.com/store/ces-2005.php">http://www.hdtvmagazine.com/store/ces-2005.php</a></p>

<p>It will provide you with a complete research of HDTV products for 2004/5, and sufficient specifications to help you determine the best fit for your needs.      </p>

<p><br />
There are other factors to consider:</p>

<p>1) Some people say, 'Buy nothing and wait'.  I say, 'you have only one life, if you are ready and could afford the cost, the sooner you enjoy HD the better you and your family would feel'.<br />
  <br />
2) Other people say, 'Buy before the broadcast flag deadline of July 2005' so the equipment would not have the internal hw/sw design to read the flag.  I say 'maybe'; I would not rush to buy for that reason if not ready; the rules and restrictions of the complete system are not 100% clear yet, the FCC is still working on wrapping some of them, like the analog hole down-res/new proposed technology.  Since you still have over a year to make your decision, monitor the events to fit your purchase to the equipment choices that give you the best features according to your needs.</p>

<p>3) After six years of HD on the air the HD industry is still subjected to the powerful funding and lobbying of Hollywood (MPAA) pulling the strings by controlling content; our government seems to keep trying to be reasonable with everyone on the rulings, but there still a lack of a well organized plan with clear parameters for all to follow.</p>

<p>4) The rules and standards are gradually being introduced while the DTV train rolls on the tracks; some of those standards are replacing themselves on each stop of the trip (i.e., component analog/1394/DVI/HDMI, and DTCP/HDCP).  It certainly feels as if the DTV train departed from the Union Station as an incomplete entity with some wheels that are square and wagons that are not well connected, but since the maintenance crew is tailoring and finishing the construction while rolling, we gained confidence that it will not derail, not by now.</p>

<p>Another example of self-replacing/parallel HD standards is cable QAM tuners (on-the-clear/unidirectional/bi-directional), with no card/POD/now CableCARD, and the High Definition DVD format/Codec war (Blu-ray, HD-DVD, EVD, WM9, MPEG-2, MPEG-4, etc).</p>

<p>However, even when the DTV moving train has inefficient behavior, DTV had an unprecedented growth this year, and is certainly here to stay.  Adopt HDTV with an intelligent purchase when ready to enjoy HD, not reacting to the pressure of implementation deadlines, standards, rulings, or the corner store inventory, neither by waiting until the dust settles down.  The dust never settles down in consumer electronics.  <br />
 <br />
After six years from introduction, HDTVs are now very affordable, and if you already have one, keep in mind that this technology upgrades itself so fast that by the time some issues are fully resolved, such as connectivity, recording, copy-protection, HD-DVD, etc., you might be tempted to replace it for a newer set with much better resolution, functionality, features, and technical capabilities, and hang it on the wall, at a very reasonable price.            </p>

<p><img src="/images/articles/page13.jpg" alt=""></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February  9, 2006  7:00 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(293)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 293)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/analysis-of-dtv-content-protection-rulings-and-agreements.php" type="text/javascript" charset="utf-8"></script>
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
