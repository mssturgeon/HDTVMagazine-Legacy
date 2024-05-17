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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 447 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 447
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword= Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php&amp;title= Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA">
		<span style="display:none">&lt;img src=&quot;/images/bulletins/sharp-aquos-lc-52d62u.jpg&quot; alt=&quot;Sharp AQUOS LC-52D62U&quot; align=&quot;left&quot;&gt;Sharp is rounding out the company's line of &quot;full HD&quot; home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's &quot;full HD&quot; product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 447";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download  Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content=" Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA" />
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
	<title>HDTV Magazine -  Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA</title>
	<meta name="keywords" content="home theater, high definition, front projector, contrast ratio, currently available, sharp, home, high, aquos, available, theater, models, projector, definition, screen, system, inch, room, model, front, lcd, dlp, full, contrast, technology" />
	<meta name="description" content="&lt;img src=&quot;/images/bulletins/sharp-aquos-lc-52d62u.jpg&quot; alt=&quot;Sharp AQUOS LC-52D62U&quot; align=&quot;left&quot;&gt;Sharp is rounding out the company's line of &quot;full HD&quot; home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's &quot;full HD&quot; product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature..." />
	<meta name="title" content=" Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=447', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php"> Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 14, 2006</b>
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
				<p class="prtitle">Sharp Showcases High-Definition Dominance with a "Full HD" Product Lineup at CEDIA</p>

<p><b>DENVER--(BUSINESS WIRE) - Sept. 14, 2006</b> - New High-Definition AQUOS LCD TVs With Advanced Specifications and Competitive Pricing Plus a 1080p DLP Front Projector Headline Sharp's Elegant HD Home Entertainment Product Line 	</p>

<p><img src="/images/bulletins/sharp-aquos-lc-52d62u.jpg" alt="Sharp AQUOS LC-52D62U" align="left">Sharp is rounding out the company's line of "full HD" home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's "full HD" product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature 1080p resolution, dramatically enhanced contrast ratios and pixel response times that are among the fastest in the industry.</p>

<p>"High-definition is the future of entertainment, and we are proud to be bringing to market a complete line of 'full HD' display products that will enhance consumers' home entertainment experience," said Bob Scaglione, senior vice president and group manager, product and marketing Group, Sharp. "Sharp is committed to leading the high-definition products industry, and our CEDIA booth reflects that commitment."</p>

<p>With the addition of the AQUOS D62U line, Sharp offers full HD 1080p models in six screen sizes (37", 42", 46", 52", 57" and 65") - more than any other manufacturer. The company has unsurpassed LCD screen manufacturing capability, highlighted by its recently-opened state-of-the-art Generation 8 factory, Kameyama No. 2, which focuses solely on the creation of large-screen units. Kameyama No. 2 is the world's first and only 8th-generation LCD facility and will enable Sharp to build the most advanced flat-panel televisions in the world, and also help to meet the growing demand for competitively-priced large-screen high-definition LCD TVs in the U.S.</p>

<p>In addition to LCD TV, Sharp is further broadening its television line with an extensive line of high-definition front projectors that feature the award-winning Texas Instruments DLP technology. "Sharp's 'full HD' expertise extends beyond the flat-panel display category, and we are especially excited about the availability of our new 1080p flagship product, the XV-Z20000, which will change the front projection landscape," Scaglione continued.</p>

<p>Demonstrating the company's complete home theater prowess, Sharp's CEDIA 2006 booth will also feature a technology demonstration of the BD-MPC10, a multi-faceted Home Theater Studio System with Blu-Ray functionality and Time Domain Speaker Technology. The system consists of an AV receiver powered by Sharp's 1-Bit digital amplification operating at 11.2MHz sampling rate, a special pair of tower speakers with built-in Time Domain Speaker Technology, and a Blu-Ray high-definition player. Time Domain Speaker Technology produces a coherent and low distortion sound wave that results in true, crystal-clear sound. The BD-MPC10 also features a listener correction EQ system by Audyssey that adjusts sound for inconsistent room acoustics. Through an interface with the AQUOS Familink system, viewers can control the complete system with the push of a single button on an AQUOS TV's remote control.</p>

<p>For detailed information on Sharp's new products, please see the individual product announcements and fact sheets available for media.</p>

<p>AQUOS Widescreen 1080p Models</p>

<p>Sharp is expanding its wide range of AQUOS models with the introduction of three large-screen 1080p HDTV AQUOS Liquid Crystal Televisions, available in 42-, 46- and 52-inch screen sizes. The models produced at the new 8th-generation Kameyama plant (46- and 52-inch) will feature the latest version of Sharp's proprietary Advanced Super View panel for extraordinary LCD performance. This panel technology enables an incredible contrast ratio for deep blacks and crisp picture quality; enhanced Quick Shoot video circuitry for faster pixel response time; and wider viewing angles, so users can view the television from virtually anywhere in the room. The 46- and 52-inch models will also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, all units in this series include dual HDMI inputs, both of which are compatible with 1080p signals from Blu-ray devices. All three models will be available in October, the LC-42D62U for a Manufacturer's Suggested Retail Price (MSRP) of $2,499.99, the LC-46D62U for an MSRP of $3,499.99, and the LC-52D62U for an MSRP of $4,799.99.</p>

<p>Flagship DLP 1080p Home Theater Front Projector (model XV-Z20000):</p>

<p>Sharp brings home theater to the forefront with the new XV-Z20000, utilizing a single .95" DMD chip from Texas Instruments. This groundbreaking 1080p projector has a native resolution of "full HD" 1920 x 1080 for true 16:9 widescreen movie viewing, producing clear vivid images. The XV-Z20000 transforms any room into a high-tech home theater, using Sharp's CV-IC III Video Scaling Circuitry. DVI/HDCP (High Bandwidth Digital Content Protection) and two HDMI terminals ensure a secure digital connection with all high definition set top boxes. The XV-Z20000 will be available in October for an MSRP of $11,999.99.</p>

<p>AQUOS Widescreen HDTV Series (models LC-37D90U and LC-32D50U)</p>

<p>The D90U and D50U Widescreen Series of HDTV AQUOS Liquid Crystal Televisions, available in 37- and 32-inch screen sizes, feature Sharp's proprietary multi-pixel technology for extraordinary LCD performance. This technology enables contrast ratio of 1200:1, enhanced Quick Shoot video circuitry for 6 ms pixel response time and wide viewing angles (176 degrees), so users can view the television from virtually anywhere in the room. The 37-inch model features full 1080p (1920 x 1080) HDTV resolution, providing consumers with an unparalleled high-definition experience, and also includes Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than previously possible. The LC-32D50U has stellar 1366 x 768 resolution for viewing HD programming. Additionally, both units in this series include dual HDMI, HD component, DVI-I for PC compatibility and the DTVLink advanced digital interface. These elegantly-styled models are available in a titanium finish with detachable bottom speakers (model LC-37D90U) or fixed bottom speakers (model LC-32D50U). The LC-37D90U and LC-32D50U are currently available for MSRPs of $2,999.99 and $1,799.99, respectively.</p>

<p>AQUOS D40U Series (models LC-37D40U, LC-32D40U and LC-26D40U)</p>

<p>Widescreen 37-, 32- and 26-inch HDTV AQUOS D40U Liquid Crystal Televisions further bolster Sharp's unmatched selection of sophisticated designs and its superior-performing LCD TVs. This series features a contrast ratio of 1200:1, enhanced Quick Shoot video circuitry for 6 ms pixel response time and wide viewing angles (176 degrees), so users can view the television from almost anywhere in the room. This series features an elegant piano black finish with fixed bottom speakers and a detachable table stand for wall-mounting flexibility. With 1366 x 768 resolution and built-in ATSC/QAM/NTSC tuners, consumers can enjoy the latest HDTV programming. Models LC-37D40U, LC-32D40U and LC-26D40U are currently available for MSRPs of $2,299.99, $1,599.99 and $1,099.99, respectively.</p>

<p>AQUOS LC-20D30U</p>

<p>This 20-inch AQUOS is the only model in Sharp's extensive LCD TV lineup to offer true 16:9 widescreen aspect ratio and 1366 x 768 resolution for 720p HDTV compatibility in a 20-inch screen size. Perfect for a smaller or secondary television-watching space, the widescreen format allows movies to be seen in the aspect ratio originally intended by the director. The LC-20D30U provides outstanding picture quality with 800:1 contrast ratio, and multiple placement options with 170-degree viewing angles. In addition, the LC-20D30U includes HD compatibility, HDMI and PC compatibility, and features a black matte finish with black side accents, bottom speakers and a removable table stand, for a variety of placement options. Model LC-20D30U is currently available for an MSRP of $899.99.</p>

<p>AQUOS S5U Series (models LC-20S5U and LC-15S5U)</p>

<p>These 15- and 20-inch 4:3 AQUOS models are Enhanced Definition LCD TVs that feature 480p compatibility for viewing progressive DVDs. These models have fixed bottom speakers and a silver finish with black side trim that complements any decor. The S5U series includes Optical Picture Control (OPC) for automatic brightness adjustment to accommodate a room's lighting conditions; NTSC, PAL and SECAM video playback capability; HD compatible input; and a high brightness level. These models are currently available for MSRPs of $749.99 and $549.99, respectively.</p>

<p>Home Theater DLP Front Projector (model XV-Z3000):</p>

<p>Sharp's portable DLP front projector, the SharpVision XV-Z3000, is a 720p high-definition home entertainment solution that instantly transforms any room into a high-tech home theater. This widescreen, portable projector can be carried throughout a home or to a friend's home to create an instant home theater for watching TV, viewing DVDs or playing computer games on a big screen. The XV-Z3000 features brightness (1200 ANSI Lumens) and contrast levels (6500:1) superior to those available in current front projectors, so consumers can enjoy excellent picture quality in almost any lighting conditions. Additionally, a dual-iris system adjusts image brightness to show full detail and enhances contrast ratio to compensate for varied lighting environments. The low fan noise of 30 dBA (in economy mode) ensures that the viewer won't miss a minute of the film's dialogue and special effects. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost, a 12 volt trigger and an HDMI interface. The XV-Z3000 is currently available for an MSRP of $3,499.99.</p>

<p>Home Theater DLP Front Projector (model DT-500):</p>

<p>The DT-500 high-definition DLP front projector is a stylish, feature-packed projector that is ideal for a dedicated home theater or any viewing room. This portable unit can be moved easily from room to room, for an instant home theater anywhere. Utilizing DLP technology from Texas Instruments, and with a resolution of 1280 x 768, the DT-500 produces a 4000:1 contrast ratio and a brightness rating of 1200 ANSI lumens, delivering one of the best pictures available in consumer home theater today. Weighing just 8.6 pounds, consumers can carry the projector to any room of the house to watch TV, DVD movies or play computer games on a big screen and then store the entire system in a cabinet to save space. A powered optical iris system instantly changes brightness and contrast settings with the push of a button to allow the greatest flexibility for varying home theater environments. Home theater convenience is further enhanced with easy installation and whisper-quiet operation. A 6 Segment 5 X Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction, resulting in an uninterrupted, detailed picture. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost and an HDMI interface. The DT-500 is currently available for an MSRP of $3,299.99.</p>

<p>Portable DLP Front Projector (model DT-100):</p>

<p>Weighing just over eight and a half pounds, this portable DLP front projector can be moved easily from room to room, for an instant big-screen theater anywhere in the home. Using DLP technology from Texas Instruments, this stylish, feature-packed projector is ideal for consumers to watch TV, DVDs or play computer games on a full-size screen and then pack it all up and put it away, saving space and avoiding clutter. The DT-100 provides consumers with a compact, lightweight product that will easily fit on a shelf, cabinet or small side table. The projector is EDTV (enhanced definition television), with a resolution of 854 x 480 that is high-definition compatible. Upgraded features include an extremely high contrast ratio of 2500:1 as well as 1000 ANSI Lumen brightness for brilliant clarity and a superior image. The low fan noise of 30 dBA (in economy mode) ensures that a film's dialogue and special effects are the only sounds that movie-watching guests will hear. The projector is outfitted with a 6 Segment 5 X Speed color wheel that minimizes "color breaking" and provides high quality images with accurate color reproduction. The DT-100 is currently available for an MSRP of $1,299.99.</p>

<p>Widescreen Liquid Crystal Television/DVD Combos (models LC-26DV20U and LC-20DV20U):</p>

<p>The Widescreen LC-26DV20U and LC-20DV20U LCD TVs provide a slim, versatile, all-in-one television and video solution. Both units feature HDMI and HD component inputs for high-definition compatibility when connected to a separate set-top box. A built-in progressive-scan DVD Player loads discs into the TV from the side, keeping the sleek appearance of the unit and creating a complete home theater solution. The DV20U Series provides a high contrast ratio (800:1) and wide viewing angles (170 degrees). The 26-inch model is an HDTV and includes built-in NTSC/ATSC/QAM tuners. The 20-inch model is an HDTV Monitor offering HD compatibility and PC connectivity. These televisions are silver and feature bottom-placed speakers that will complement any decor. The LC-26DV20U and LC-20DV20U are currently available for MSRPs of $1,049.99 and $899.99 respectively.</p>

<p>For more information on Sharp's full line of Liquid Crystal Televisions, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07430, or call 800-BE-SHARP. For online product information, visit sharpusa.com.</p>

<p>Sharp Electronics Corporation is the Mahwah, N.J.-based marketing and sales subsidiary of Japan's Sharp Corporation, a worldwide developer of the core technologies that are integral to shaping the next generation of home entertainment products, appliances, networked, multifunctional office solutions, solar energy and mobile communication and information tools. Leading brands include AQUOS(R) Liquid Crystal Televisions, 1-Bit(TM) digital audio products, SharpVision(R) projection products, Carousel(R) microwaves, IMAGER(TM) digital multifunctional systems, and Notevision(R) multimedia projectors. Sharp Electronics Corporation employs approximately 2,000 people throughout the U.S. supporting more than 50 product lines.</p>

<p>*Sharp won a 2004 Technology & Engineering Emmy(R) for Award for Development of Direct View Liquid Crystal Display Screens. Use of the trademarks and service marks of the National Television Academy, including the mark Emmy(R), requires the prior express written permission of the National Television Academy.</p>

<p>**Cable system must deliver HDTV programming. Consumers should check with their local cable company to determine available HDTV channels.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 14, 2006  7:24 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(447)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 447)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php" type="text/javascript" charset="utf-8"></script>
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
