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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3582 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3582
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Introducing TiVo(R) Premiere, the One Box to Rule Them All&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2010/03/introducing-tivor-premiere-the-one-box-to-rule-them-all.php&amp;title=Introducing TiVo(R) Premiere, the One Box to Rule Them All">
		<span style="display:none">&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/tivo-premiere.jpg&quot; alt=&quot;TiVo Premiere&quot; height=&quot;68&quot; width=&quot;150&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;TiVo Inc. (NASDAQ: TIVO), a leader in advanced television services, including digital video recorders (DVRs), announced today the launch of TiVo&amp;reg; Premiere and TiVo&amp;reg; Premiere XL boxes. TiVo Premiere elegantly combines access to cable programming, movies, web videos, and music all in one box at a truly affordable price. TiVo Premiere is now the only way to unlock the real value of the HD television set, a box that is more powerful, compact, and energy efficient than previous generations. If the DVR changed your life, TiVo Premiere will change it again.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3582";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Introducing TiVo(R) Premiere, the One Box to Rule Them All" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Introducing TiVo(R) Premiere, the One Box to Rule Them All" />
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
	<title>HDTV Magazine - Introducing TiVo(R) Premiere, the One Box to Rule Them All</title>
	<meta name="keywords" content="tivo premiere, tivo inc, premiere box, looking statements, hours standard, tivo, box, premiere, cable, television, digital, content, new, experience, video, hours, remote, statements, our, programming, broadband, entertainment, service, looking, want" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/tivo-premiere.jpg&quot; alt=&quot;TiVo Premiere&quot; height=&quot;68&quot; width=&quot;150&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;TiVo Inc. (NASDAQ: TIVO), a leader in advanced television services, including digital video recorders (DVRs), announced today the launch of TiVo&amp;reg; Premiere and TiVo&amp;reg; Premiere XL boxes. TiVo Premiere elegantly combines access to cable programming, movies, web videos, and music all in one box at a truly affordable price. TiVo Premiere is now the only way to unlock the real value of the HD television set, a box that is more powerful, compact, and energy efficient than previous generations. If the DVR changed your life, TiVo Premiere will change it again." />
	<meta name="title" content="Introducing TiVo(R) Premiere, the One Box to Rule Them All" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2010/03/introducing-tivor-premiere-the-one-box-to-rule-them-all.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3582', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/03/introducing-tivor-premiere-the-one-box-to-rule-them-all.php">Introducing TiVo(R) Premiere, the One Box to Rule Them All</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>March  3, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Introducing TiVo(R) Premiere, the One Box to Rule Them All</p>

<center><i>New Box Offers a Stunning New HD Experience That Blows Away Any Other Cable Set-Top Box</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/tivo-premiere.jpg" alt="TiVo Premiere" height="136" width="300" class="keyimg"><strong>ALVISO, CA -- (Marketwire) -- 03/03/2010 -- </strong>TiVo Inc. (NASDAQ: TIVO), a leader in advanced television services, including digital video recorders (DVRs), announced today the launch of TiVo&reg; Premiere and TiVo&reg; Premiere XL boxes. TiVo Premiere elegantly combines access to cable programming, movies, web videos, and music all in one box at a truly affordable price. TiVo Premiere is now the only way to unlock the real value of the HD television set, a box that is more powerful, compact, and energy efficient than previous generations. If the DVR changed your life, TiVo Premiere will change it again.</p>

<p>"It has never been this easy to get all your entertainment in one place, on the big screen, in HD, right at your fingertips. And the beauty of TiVo Premiere is that finding what to watch is just as fun as the watching the TV shows themselves. We accomplished it by using pictures and graphics to make the whole television guide experience come alive in a way that it never has before," said TiVo's President and CEO Tom Rogers. "TiVo Premiere is your new cable box, it's your movie box, it's your web box, and music box; it's the one box that can give you access to almost anything you want, whenever you want it. We've taken millions of pieces of content and organized it for you in a way that makes so much sense you'll wonder how you ever lived without it."</p>

<p><br />
<strong>Why Premiere Is Revolutionary:</strong></p>

<p>It's the One Box: TiVo Premiere is your new cable box, movie box, web box, and music box; it's the one box that gives you access to everything you want to get on your television and all with one remote. It's a true one stop shop for entertainment.*</p>

<p>Broadband and Broadcast Integration: Premiere's search functionality offers users the ability to find programs from many sources -- whether it's a digital premium channel from cable, a funny outtake from YouTube or a movie from broadband sources such as Netflix, Amazon Video On Demand, or Blockbuster On Demand, plus options from the Web all brought together into one unified and simple experience.*</p>

<p>Browse Baby Browse: Premiere is at its best when you want to browse, discover and explore the almost infinite array of TV options. Every show, from every source, is organized by category or topic. With a full-screen menu that includes a rich display of movie posters &amp; TV show logos for exploring, it's simple to stumble upon interesting things to watch or record. Plus, consumers have never before had the option to browse by specific movie collections on their TV. For example, browse thru Oscar&reg; Award winning movies or Emmy&reg; Award winning television shows with one simple click of the remote.</p>

<p>Six-Degrees of Separation: Premiere will settle debates over "where have I seen that actor before?" far faster than a trip to the laptop ever could. Just click on a show or an actor and consumers will be able to view entire entertainment resumes and cast lists, quickly and easily. Before you know it you'll find associations from one favorite actor to another that you never dreamed existed, fueling the discovery process even more and likely getting you hooked in the process.</p>

<p>Easy to Use HD Layout: We've kept the easy navigation, but significantly cut the number of screens you have to visit by utilizing the extra room high-definition affords. The experience was built for HD television sets and by creating a two-column and three-column layouts we reduce the number of screens, helping you to get to what you want more quickly.</p>

<p>Set-top box built on Adobe&reg; Flash&reg; Platform for the Digital Home: Premiere is built on Adobe Flash software, a pathway to future user-generated experiences and development opportunities, which have not been available before on a set-top box. Premiere is built on multi-core architecture that greatly facilitates future development of third party provided applications.</p>

<p>We Listened: By popular demand Premiere includes an on-screen disk space meter that shows how much room is left to record, a built in 30 second scan, and a new video window that lets you watch your favorite show while navigating the TiVo menus.</p>

<p>Room to Record: The TiVo Premiere box has up to 45 hours of HD storage space or up to 400 hours of standard definition programming capacity. And for those who want even more options the TiVo Premiere XL box holds up to 150 hours of high-definition or up to 1350 hours of standard definition programming.</p>

<p>Even More Content: A brand new relationship with Pandora means in the coming months, Premiere as well as TiVo Series3&trade;, HD DVR, and Series2 customers will be able to listen to their personalized Pandora radio stations on the best speakers in the house. A new relationship with FrameChannel brings the best of the web directly to the TV offering users access to nearly 1,000 content widgets of personal and commercial content, ranging from Tweets, photos and status updates to news, weather, sports scores and stock quotes. FrameChannel acts as a personalized channel on your television, transforming your TV into a constant stream of real-time news and information with the content that matters to you most.</p>

<p>Speaking about its strategic marketing alliance with TiVo that was announced last year, Mike Vitelli, President of the Americas for Best Buy, said, "This is an important step toward achieving our shared vision to transform the digital home entertainment experience and redefine customer service. Through TiVo we can continue to strengthen relationships and interact with our valued customers even after they leave our stores, which is invaluable in a rapidly evolving digital media environment. Cable companies do a great job of connecting customers to content, but TiVo takes the complete digital experience to the next level."</p>

<p>"We are proud to be leading the cable industry in the adoption of this groundbreaking approach to advanced television. TiVo will bring a whole new way for our subscribers to experience television with TiVo's DVR and broadband television offerings," said RCN President &amp; CEO Peter Aquino. "I'm very pleased to report that based on our field test results, we will begin to roll out TiVo as our primary advanced box in all of our markets, one by one, in the second quarter of this year."</p>

<p><img src="http://www.hdtvmagazine.us/news/images/TiVo-Qwerty-Remote.jpg" alt="TiVo Qwerty Remote" height="249" width="288" style="float:right">Coming in May, a new TiVo Wireless N adapter, that allows for faster downloads and faster streaming of content, at speeds that are especially helpful for multi-room viewing (MRV) transfers. Coming later this year, consumers will also have access to a groundbreaking, slide-out QWERTY TiVo remote. The compact peanut shaped remote gives a faster experience, especially when utilizing text entry. With Easy plug n' play installation, extended range utilizing Bluetooth and backlit programmable buttons the remote will quickly become a must have accessory.</p>

<p><br />
<strong>TiVo Premiere Box Specifications:</strong></p>

<ul><li>TiVo Series4&trade; architecture</li><li>Supports digital cable, high-definition digital cable, antenna (ATSC) and Verizon FiOS</li><li>Outputs: HDMI, Component video, Composite video, Optical audio, Analog audio</li><li>Video output modes include: 480i, 480p, 720p, 1080i, 1080p</li><li>Inputs: CableCARD&trade; support, Cable coax, Antenna coax, Ethernet</li><li>Ethernet connection, USB 2.0 ports (2), E-SATA support for external storage</li><li>TiVo Wireless N and G Network Adapter support</li><li>ENERGY STAR&reg; certified</li><li>320 Gigabytes</li><li>Records up to 45 hours of HD programming or up to 400 hours of standard-definition</li></ul>

<p><strong>TiVo Premiere XL Box Specifications (all specs not listed are the same as above unless noted)</strong></p>

<ul><li>One Terabyte storage</li><li>Records up to 150 hours of HD programming or up to 1350 hours of standard-definition</li><li>Backlit, programmable, and learning remote</li><li>THX&reg;certified, ensuring optimal audio and video reproduction and enables seamless integration with other THX components</li><li>TiVo Premiere XL box is the first HD product to feature THX&reg; Optimizer&trade;, a video calibration tool that lets users fine tune color, black levels and other settings to improve picture quality. Hailed by critics for its ease-of-use, the exclusive THX Optimizer for TiVo Premiere XL box is found in the My Shows menu of the TiVo service. A pair of THX Optimizer Blue Glasses, designed for adjusting Color and Tint settings, is included with the owner's manual.</li></ul>

<p>TiVo Premiere and TiVo Premiere XL boxes will be available in retail nationwide in early April. They are also available for pre-order today at tivo.com for $299.99 and $499.99 respectively.</p>

<p>*TiVo service is required and sold separately. Cable and Netflix subscriptions required for cable and Netflix programming, respectively and are not included in TiVo service subscription. Additional fees may apply for other broadband content. Broadband programming sources subject to availability and may change without notice.</p>

<p><br />
<strong>About TiVo Inc.</strong></p>

<p>Founded in 1997, TiVo Inc. (NASDAQ: TIVO) developed the first commercially available digital video recorder (DVR). TiVo offers the TiVo service and TiVo DVRs directly to consumers online at <a target="_blank" href="http://www.tivo.com/">www.tivo.com</a> and through third-party retailers. TiVo also distributes its technology and services through solutions tailored for cable, satellite, and broadcasting companies. Since its founding, TiVo has evolved into the ultimate single solution media center by combining its patented DVR technologies and universal cable box capabilities with the ability to aggregate, search, and deliver millions of pieces of broadband, cable, and broadcast content directly to the television. An economical, one-stop-shop for in-home entertainment, TiVo's intuitive functionality and ease of use puts viewers in control by enabling them to effortlessly navigate the best digital entertainment content available through one box, with one remote, and one user interface, delivering the most dynamic user experience on the market today. TiVo also continues to weave itself into the fabric of the media industry by providing interactive advertising solutions and audience research and measurement ratings services to the television industry. <a target="_blank" href="http://www.tivo.com/">www.tivo.com</a></p>

<p>TiVo, and the TiVo Logo are registered trademarks of TiVo Inc. and its subsidiaries worldwide. (c) 2010 TiVo Inc. All other trademarks are property of their respective owners. All rights reserved</p>

<p>This release contains forward-looking statements within the meaning of the Private Securities Litigation Reform Act of 1995. These statements relate to, among other things, the future retail availability of TiVo Premiere box, TiVo Wireless N Adapter, and TiVo slide-out QWERTY remote control as well as future applications from Pandora Internet Radio, FrameChannel, and future user-generated development opportunities. Forward-looking statements generally can be identified by the use of forward-looking terminology such as, "believe," "expect," "may," "will," "intend," "estimate," "continue," or similar expressions or the negative of those terms or expressions. Such statements involve risks and uncertainties, which could cause actual results to vary materially from those expressed in or indicated by the forward-looking statements. Factors that may cause actual results to differ materially include delays in development, competitive service offerings and lack of market acceptance, as well as the other potential factors described under "Risk Factors" in the Company's public reports filed with the Securities and Exchange Commission, including the Company's Annual Report on Form 10-K for the fiscal year ended January 31, 2009, Quarterly reports on Form 10-Q since then, and Current Reports on Form 8-K. The Company cautions you not to place undue reliance on forward-looking statements, which reflect an analysis only and speak only as of the date hereof. TiVo disclaims any obligation to update these forward-looking statements.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>March  3, 2010  2:32 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3582)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3582)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/03/introducing-tivor-premiere-the-one-box-to-rule-them-all.php" type="text/javascript" charset="utf-8"></script>
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
