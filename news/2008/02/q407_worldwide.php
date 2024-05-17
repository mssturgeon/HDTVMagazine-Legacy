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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1266 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1266
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Q4\'07 Worldwide LCD TV Shipments Surpass CRTs&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php&amp;title=Q4'07 Worldwide LCD TV Shipments Surpass CRTs">
		<span style="display:none">AUSTIN, TEXAS, February 19, 2008-&lt;/b&gt;DisplaySearch, the worldwide leader in display market research and consulting, reported in its latest &lt;i&gt;Quarterly Global TV Shipment and Forecast Report&lt;/i&gt; that global TV shipments grew 21% Q/Q and 5% Y/Y to 60.8 million units, which brought 2007 total shipments to almost 200 million units worldwide. For the full year in 2007, TV revenues exceeded $100 billion for the first time, with Q4'07 revenues climbing 10% Y/Y and 26% Q/Q to a record $32.9 billion.  &lt;p&gt;Also of note, DisplaySearch reported that LCD TV shipments worldwide overtook CRT TV shipments... </span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1266";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Q4\'07 Worldwide LCD TV Shipments Surpass CRTs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Q4\'07 Worldwide LCD TV Shipments Surpass CRTs" />
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
	<title>HDTV Magazine - Q4'07 Worldwide LCD TV Shipments Surpass CRTs</title>
	<meta name="keywords" content="north america, screen sizes, revenue share, unit basis, revenue basis, share, lcd, shipments, revenue, growth, brand, america, pdp, samsung, unit, total, basis, sony, north, units, market, displaysearch, crt, top, screen" />
	<meta name="description" content="AUSTIN, TEXAS, February 19, 2008-&lt;/b&gt;DisplaySearch, the worldwide leader in display market research and consulting, reported in its latest &lt;i&gt;Quarterly Global TV Shipment and Forecast Report&lt;/i&gt; that global TV shipments grew 21% Q/Q and 5% Y/Y to 60.8 million units, which brought 2007 total shipments to almost 200 million units worldwide. For the full year in 2007, TV revenues exceeded $100 billion for the first time, with Q4'07 revenues climbing 10% Y/Y and 26% Q/Q to a record $32.9 billion.  &lt;p&gt;Also of note, DisplaySearch reported that LCD TV shipments worldwide overtook CRT TV shipments... " />
	<meta name="title" content="Q4'07 Worldwide LCD TV Shipments Surpass CRTs" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1266', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php">Q4'07 Worldwide LCD TV Shipments Surpass CRTs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>February 19, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=267&category=Marketplace">Marketplace</a></b>
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
				<p></p>
<p><b>AUSTIN, TEXAS, February 19, 2008-</b>DisplaySearch, the worldwide leader in display market research and consulting, reported in its latest <i>Quarterly Global TV Shipment and Forecast Report</i> that global TV shipments grew 21% Q/Q and 5% Y/Y to 60.8 million units, which brought 2007 total shipments to almost 200 million units worldwide. For the full year in 2007, TV revenues exceeded $100 billion for the first time, with Q4'07 revenues climbing 10% Y/Y and 26% Q/Q to a record $32.9 billion. 
<p>Also of note, DisplaySearch reported that LCD TV shipments worldwide overtook CRT TV shipments for the first time, after rising 56% Y/Y to a record of more than 28.5 million units or 47% of the world TV market. The strong LCD TV share gains can be attributed to 
<ul>
<li><b>Share gains in all regions-</b>LCD unit share improved in every region worldwide, including Europe, which had the strongest growth of the quarter. LCD penetration was highest in developed regions, reaching 86% in Japan, 84% in Western Europe and 78% in North America. But the strongest unit growth for LCD was in developing regions, such as Latin America, Asia Pacific, and Middle East &amp; Africa, which combined rose 106% Y/Y, where penetration is low and the opportunity is substantial. 
<li><b>Natural replacement for CRT-</b>LCD is the only other technology that extends down in screen size to less than 20", which makes it a natural replacement to CRT TVs, as consumers upgrade and CRT TV tube capacity shrinks. Current plasma (PDP) TV technology extends down to 32", but the CRT market is largely below this size, and many regions of the world have limited acceptance of 40"+ screen sizes. As shown in Figure 1, CRT has fallen from 77% of global TV shipments in Q1'06 to 46% in Q4'07, even with LCD prices at a 224% ASP premium for 32" and smaller screen sizes.</li></ul>
<p><b></b>&nbsp; <p><b>Figure 1: Worldwide CRT vs. LCD TV Unit Share</b> 
<p><img style="margin: 0px 0px 5px 5px" height="305" alt="clip_image002" src="http://www.hdtvmagazine.com/images/test/DisplaySearchReportsQ407WorldwideLCDTVSh_ADC1/clip_image002.gif" width="431" border="0"><b></b> 
<ul>
<li><b>Share gains against RPTV and PDP at 40"+-</b>Despite the natural replacement of the CRT TV market, LCD has also made strong share gains against plasma and RPTV technologies with new larger LCD panel fabs optimized to produce larger screen sizes more cost effectively. LCD share of 40"+ TV's has grown from 44% to 65% Y/Y on a unit basis while PDP TV has fallen from 40% to 31% and RPTV is down from 16% to 3%.<b></b></li></ul>
<h2>Results by Technology</h2>
<p><b>LCD TV</b> shipments rose 41% Q/Q and 56% Y/Y to 28.5M units in Q4'07, taking a 47% share of total TV shipments during the quarter, surpassing the 46% share for CRT TV. This brings the 2007 total LCD TV shipments to 79.3M units, a 73% increase from 2006. On a revenue basis, LCD TV grew 34% Y/Y and 31% Q/Q to $22.8B-accumulating almost $68B total in 2007, a 40% boost Y/Y. 
<ul>
<li>LCD led at 15-19", 22-24", 30-34", 35-39", 40-44" and 45-49" screen sizes as the average LCD TV screen size climbed above 32" for the first time in Q4'07. The 40"+<b> size share of the </b>LCD market expanded from 17% to 25% Y/Y on a unit basis and 33% to 44% on a revenue basis. 1080p resolutions also enjoyed strong growth, rising 71% Q/Q and 286% Y/Y to climb to 17% of all LCD TV shipments and 57% of 40"+ units since overtaking HD and lower resolutions in Q3'07. 
<li>Western Europe regained the share lead as the top region for LCD TV shipments, rising from 28% to 32%, overtaking North America which fell to 31% from 33%. 
<li>On a brand share basis, Sony overtook Samsung for the #1 revenue share in LCD TV at 19.5%, the first time since Q1'07 at #1, but Samsung remained #1 on a unit basis. Sony had the strongest Q/Q revenue growth of the top 5 and outpaced total LCD Q/Q revenue growth 2:1 as shown in Table 1. Sony also led in North America and Latin America on a revenue basis, while Samsung was the top brand in European regions as well as Asia Pacific and Middle East &amp; Africa. Sharp led in Japan while Hisense was #1 in China.</li></ul>
<p><b>Table 1: LCD Brand Revenue Share and Growth</b> 
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Sony</td>
<td class="grid" nowrap>
15.9%</td>
<td class="grid" nowrap>
19.5%</td>
<td class="grid" nowrap>
61%</td>
<td class="grid" nowrap>
41%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
18.7%</td>
<td class="grid" nowrap>
19.3%</td>
<td class="grid" nowrap>
35%</td>
<td class="grid" nowrap>
67%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
Philips</td>
<td class="grid" nowrap>
9.7%</td>
<td class="grid" nowrap>
10.1%</td>
<td class="grid" nowrap>
37%</td>
<td class="grid" nowrap>
23%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Sharp</td>
<td class="grid" nowrap>
12.5%</td>
<td class="grid" nowrap>
10.1%</td>
<td class="grid" nowrap>
6%</td>
<td class="grid" nowrap>
21%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
7.8%</td>
<td class="grid" nowrap>
7.7%</td>
<td class="grid" nowrap>
30%</td>
<td class="grid" nowrap>
54%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
35.4%</td>
<td class="grid" nowrap>
33.3%</td>
<td class="grid" nowrap>
24%</td>
<td class="grid" nowrap>
20%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>31%</b></td>
<td class="grid" nowrap>
<b>34%</b></td></tr></tbody></table>
<p>&nbsp; <p><b>PDP TV</b> shipments were up a more modest 29% Y/Y compared to LCD TV, but exhibited the strongest Q/Q growth of any technology at 43% to 4M units in Q4'07. This brings 2007 total shipments to 11.3M units, 22% higher than 2006. On a revenue basis, PDP TV growth was not as robust with 28% Q/Q growth but a 3% Y/Y decline in Q4'07 to $4.8B. 
<ul>
<li>The biggest area of growth for PDP TV has been at smaller screen sizes with the &lt;42" market rising from 8% to 13% Q/Q of plasma shipments and both 32" and 37" volume up over 90% as LCD price reductions slow at 32" and 37" on supply constraints, boosting interest in PDP TV at these sizes. 1080p share of PDP TV shipments rose from less than 1% during Q1'07 to more than 12% during Q4'07, helping to slow share gains by LCD at competing screen sizes. PDP TV held a significant share lead at 50-54", but LCD has started eating into that lead. 
<li>North America continued to be the top region for PDP shipments, but lost share to Europe, mostly at 40-44" screen sizes but also at 55"+. 
<li>By brand on a revenue basis, as shown in Table 2, Panasonic enjoyed healthy share growth from 33% in Q3'07 to 40% in Q4'07 with more than twice the Q/Q revenue gains than #2 Samsung, which had the best Y/Y growth as Panasonic had an extremely strong Q4 a year ago on large price drops. Panasonic topped PDP TV shipments in Japan, North America, Western and Eastern Europe and China while #3 LGE led in Asia Pacific, Latin America, and Middle East &amp; Africa.<b></b></li></ul>
<p><b></b>
<p><b></b>
<p><b></b>
<p><b></b>
<p><b></b>
<p><b>Table 2: PDP TV Brand Revenue Share and Growth</b> 
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Panasonic</td>
<td class="grid" nowrap>
33.0%</td>
<td class="grid" nowrap>
39.6%</td>
<td class="grid" nowrap>
53%</td>
<td class="grid" nowrap>
16%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
21.7%</td>
<td class="grid" nowrap>
20.3%</td>
<td class="grid" nowrap>
20%</td>
<td class="grid" nowrap>
38%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
16.1%</td>
<td class="grid" nowrap>
15.0%</td>
<td class="grid" nowrap>
19%</td>
<td class="grid" nowrap>
-12%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Hitachi</td>
<td class="grid" nowrap>
7.8%</td>
<td class="grid" nowrap>
6.6%</td>
<td class="grid" nowrap>
9%</td>
<td class="grid" nowrap>
-18%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
Pioneer</td>
<td class="grid" nowrap>
7.5%</td>
<td class="grid" nowrap>
6.3%</td>
<td class="grid" nowrap>
8%</td>
<td class="grid" nowrap>
-27%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
13.8%</td>
<td class="grid" nowrap>
12.2%</td>
<td class="grid" nowrap>
13%</td>
<td class="grid" nowrap>
-42%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>28%</b></td>
<td class="grid" nowrap>
<b>-3%</b></td></tr></tbody></table>
<p><b>Microdisplay (MD) RPTV </b>shipments started out the year down, and the decline accelerated throughout the year with Q4'07 falling 60% Y/Y and 6% Q/Q to 348K units, given a 2007 year end total of 1.6M units. MD RPTVs suffered from stiff competition in North America from both LCD and PDP TVs, as well as a lack of regional diversification as 92% of worldwide shipments were made in North America during 2007. </p>
<p>Because of substantial price advantages over PDP and LCD TVs, MD RPTV continues to lead at 55-59" and 60"+ screen sizes. The 1080p share of MD RPTV shipments grew from 74% to 84% Q/Q, with DLP accounting for a dominant share of the RPTV market at 60%, up from 42% a year earlier. 
<p>Samsung remained at the top of MD RPTV revenue share rankings in Q4'07 worldwide at 33.7% with Mitsubishi overtaking Sony for #2 at 30.6%, as Sony announced their exit from the category in Q1'08. 
<h2>Results by TV Brand</h2>
<p><b>Samsung</b> led on a unit basis for the sixth consecutive quarter, picking up a point of share to 15% on the second strongest quarterly growth among the top five and the greatest Y/Y growth, as shown in Table 3. Samsung also had the top revenue brand share for the eighth straight quarter, even stronger than their unit share due to a higher blended average price, rising to 18.6% in Q4'07. Samsung's broad support of many technologies allows them to reach a wide addressable market. Samsung was #2 in LCD, PDP and CRT TVs and was #1 in MD RPTV. Samsung also led in Europe, the strongest growing region for the quarter, as well as in Asia Pacific and Middle East &amp; Africa. 
<p><b>Sony</b> was #2 in TV revenues, rising from 11.7% to 14.4% in Q4'07, doing so with a stronger focus on LCD TV than any other top five brand with 94% of its units shipped by that technology. Sony had the strongest Q/Q TV revenue growth among the top five, with the biggest revenue gains coming from Western Europe. Sony rose to #3 on a unit basis, rising to 8% unit share and overtaking Philips. Sony was also the category leader in LCD TV on a revenue basis and rose to #1 on a unit basis in North America for the first time. 
<p><b>LGE</b> was the #3 brand in TV revenues at 9.4%, unchanged from Q3'07, and improved its #2 unit share position with a half point rise to 11.7%. LGE is a strong competitor in developed regions and was the unit leader in the developing markets of Latin America and Middle East &amp; Africa, while ranking #2 behind Samsung in Asia Pacific. LGE had the top CRT TV unit and revenue share worldwide, still an important category in developing markets. 
<p><b>Table 3: Total TV Brand Unit Share and Growth</b><b> </b>
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
13.9%</td>
<td class="grid" nowrap>
15.0%</td>
<td class="grid" nowrap>
30%</td>
<td class="grid" nowrap>
37%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
11.3%</td>
<td class="grid" nowrap>
11.7%</td>
<td class="grid" nowrap>
25%</td>
<td class="grid" nowrap>
32%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
Sony</td>
<td class="grid" nowrap>
6.2%</td>
<td class="grid" nowrap>
8.0%</td>
<td class="grid" nowrap>
56%</td>
<td class="grid" nowrap>
20%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Philips</td>
<td class="grid" nowrap>
7.0%</td>
<td class="grid" nowrap>
7.4%</td>
<td class="grid" nowrap>
28%</td>
<td class="grid" nowrap>
0%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
TCL</td>
<td class="grid" nowrap>
5.7%</td>
<td class="grid" nowrap>
5.9%</td>
<td class="grid" nowrap>
25%</td>
<td class="grid" nowrap>
-24%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
56.1%</td>
<td class="grid" nowrap>
52.0%</td>
<td class="grid" nowrap>
12%</td>
<td class="grid" nowrap>
-2%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>21%</b></td>
<td class="grid" nowrap>
<b>5%</b></td></tr></tbody></table>
<p><strong></strong>&nbsp; <p><b>Table 4: Total TV Brand Revenue Share and Growth</b> 
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
18.3%</td>
<td class="grid" nowrap>
18.6%</td>
<td class="grid" nowrap>
29%</td>
<td class="grid" nowrap>
40%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
Sony</td>
<td class="grid" nowrap>
11.7%</td>
<td class="grid" nowrap>
14.4%</td>
<td class="grid" nowrap>
55%</td>
<td class="grid" nowrap>
17%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
9.4%</td>
<td class="grid" nowrap>
9.4%</td>
<td class="grid" nowrap>
26%</td>
<td class="grid" nowrap>
25%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Panasonic</td>
<td class="grid" nowrap>
7.1%</td>
<td class="grid" nowrap>
8.4%</td>
<td class="grid" nowrap>
48%</td>
<td class="grid" nowrap>
13%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
Philips</td>
<td class="grid" nowrap>
8.0%</td>
<td class="grid" nowrap>
8.3%</td>
<td class="grid" nowrap>
31%</td>
<td class="grid" nowrap>
4%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
45.5%</td>
<td class="grid" nowrap>
40.9%</td>
<td class="grid" nowrap>
13%</td>
<td class="grid" nowrap>
-3%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>26%</b></td>
<td class="grid" nowrap>
<b>10%</b></td></tr></tbody></table>
<p>&nbsp; <p>Beginning with the Q1'08 report, DisplaySearch's methodology has been improved by providing brand-level ASPs in North America, which has increased revenue and revenue-based market share accuracy. 
<p>See the #1 North America LCD and Plasma TV brands speak at the <b><i>DisplaySearch US FPD Conference</i></b> to be held March 10-13 in San Diego, California. To view the full agenda and register, visit <a href="http://www.displaysearch.com/usfpd2008">www.displaysearch.com/usfpd2008</a>. 
<p>DisplaySearch's TV market intelligence including panel and TV shipments, TV shipments by region by brand by size for nearly 60 brands, rolling 16-quarter forecasts, TV cost/price forecasts and design wins can be found in its <i>Quarterly Global TV Shipment and Forecast Report</i>. For more information on this report, please contact Arie Braun at (512) 687-1505 or arie@displaysearch.com. 
<h2>About DisplaySearch</h2>
<p>DisplaySearch, an NPD Group company, has a core team of 57 employees located in Europe, North America and Asia who produce a valued suite of FPD-related market forecasts, technology assessments, surveys, studies and analyses. The company also organizes influential events worldwide. Headquartered in Austin, Texas, DisplaySearch has regional operations in Chicago, Houston, Kyoto, London, San Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is on the web at <a href="http://www.displaysearch.com">www.displaysearch.com</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>February 19, 2008  8:15 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1266)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 1266)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php" type="text/javascript" charset="utf-8"></script>
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
