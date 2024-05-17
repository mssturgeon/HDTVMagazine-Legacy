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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 385 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 385
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=High-Def DVD Part II - Taiwan Challenger&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/05/highdef-dvd-part-ii-taiwan-challenger.php&amp;title=High-Def DVD Part II - Taiwan Challenger">
		<span style="display:none">Taiwan's Forward Versatile Disc (FVD)

Over the past couple of years, I have written on these reports (as well as in the pages of DVDetc and HDTVetc Magazines) about the four Hi-def DVD formats in the China/Taiwan market, three from China (EVD, HVD, and HDV), and one from Taiwan (FVD).

On this opportunity, I met at CES 2006 with Mr. Job Liu, Managing Director of POSO (Power Source Group Limited), who was representing the FVD format at CES. Mr. Liu introduced also Margaret Fan, General Manager of Idar Electronics Co., a company involved with the FVD players and format.

They showed the player, the movies, ...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 385";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download High-Def DVD Part II - Taiwan Challenger" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="High-Def DVD Part II - Taiwan Challenger" />
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
	<title>HDTV Magazine - High-Def DVD Part II - Taiwan Challenger</title>
	<meta name="keywords" content="def dvd, red laser, blue laser, dvd forum, fvd format, fvd, dvd, taiwan, laser, format, players, player, def, movies, disc, part, red, content, video, discs, blue, article, itri, series, ces" />
	<meta name="description" content="Taiwan's Forward Versatile Disc (FVD)

Over the past couple of years, I have written on these reports (as well as in the pages of DVDetc and HDTVetc Magazines) about the four Hi-def DVD formats in the China/Taiwan market, three from China (EVD, HVD, and HDV), and one from Taiwan (FVD).

On this opportunity, I met at CES 2006 with Mr. Job Liu, Managing Director of POSO (Power Source Group Limited), who was representing the FVD format at CES. Mr. Liu introduced also Margaret Fan, General Manager of Idar Electronics Co., a company involved with the FVD players and format.

They showed the player, the movies, ..." />
	<meta name="title" content="High-Def DVD Part II - Taiwan Challenger" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/05/highdef-dvd-part-ii-taiwan-challenger.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=385', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/05/highdef-dvd-part-ii-taiwan-challenger.php">High-Def DVD Part II - Taiwan Challenger</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>May 30, 2006</b>
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
				<blockquote>This article is the second in a series.<br>
<br>
Other articles in this series:<br>
Part 1: <a href="/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php">Hi-Def DVD? - Blue laser? Well, what else is out there?</a></blockquote>
<br>
<br>
This article is a small excerpt on the full coverage of Hi-Def DVD in a section included within my "State of HDTV Technology, 2006 Review". The article continues with the subject of Part I "Well, what else is out there?" covering another HD red laser format, this one coming from Taiwan.

<p><br />
<center><b>Taiwan's Forward Versatile Disc (FVD)</b></center></p>

<p>Over the past couple of years, I have written on these reports (as well as in the pages of DVDetc and HDTVetc Magazines) about the four Hi-def DVD formats in the China/Taiwan market, three from China (EVD, HVD, and HDV), and one from Taiwan (FVD).<img src="/images/articles/fvd-player.jpg" alt="FVD player" align="right"></p>

<p>On this opportunity, I met at CES 2006 with Mr. Job Liu, Managing Director of POSO (Power Source Group Limited), who was representing the FVD format at CES. Mr. Liu introduced also Margaret Fan, General Manager of Idar Electronics Co., a company involved with the FVD players and format.</p>

<p>They showed the player, the movies, the FVD format efforts, and we discussed about specifications and technical capabilities of the format, discs, and players. At the end of our long meeting, I was offered if I wanted to take the player with me after the show. It took me by surprise, I declined politely, but I certainly accepted an FVD disc demo as my after show teaser.</p>

<p>FVD discs and players are already available for sale. The player MSRP is $250, and FVD movies were quoted as about $6 per disc, although I have not seen an official price list as I did with Chinese EVD companies the year before.</p>

<p>FVD is a red laser solution that supports FVD-video and WMV-9 HD video codecs, and WMA, LPCM and ITRI-Audio codecs. The disc can store 135 minutes of HD full-length movies in 720p/24/30 (SL), or in 1080i60/p24 (DL, or 3 hrs TL), in addition to 720x480 and 320x240 regular video resolution at 60i.</p>

<p>The FVD player is suited with DVI/HDMI and component analog connections, optical and coax for 5.1 or 2-channel audio, peak bit rate 15Mbps. According to the company at CES the player is able to output 1080i over component analog because the format uses its own content protection system (ITRI-AES, Innovative Technologies Research Institute - Advanced Encryption Standard).</p>

<p>There is a PC playback software version (Super FVD, in beta now) that allows the use of existing DVD-ROM drives for HD FVD playback of movies without any additional hardware. There is no need for Microsoft's Media Center in the PC because as mentioned above the format uses its own content protection system, no DRM.</p>

<p><img width=254 height=141 src="/images/articles/fvd-discs.jpg" alt="FVD Discs" align="left">There are 29 companies and ITRI in the AOSRA (Taiwan Advanced Optical Storage Research Alliance), an organization founded in January 2002.</p>

<p>There are several FVD disc producers like RiTek Corporation, Prodisc Technology Inc., U-Tech Media Corporation, Leaddata, Infodisc, Giga Storage, Optodisc, Nan-Ya, and CMC Magnetics Co. (their discs are pictured above on the left). Players are being manufactured by TATUNG, BenQ, LITE-ON, Actima, Mustek, PROTOP, Arima, MSI, QSINC, Ultima, and A-DATA. Chip-set manufacturers include VOS, ALI, MTK, SUNPLUS, and CHEERTEK. Video software content includes Newsoft, Deltamac, and Cine-Asia Entertainment.</p>

<p><img src="/images/articles/sniper.jpg" alt="Sniper" align="right"></p>

<p><img src="/images/articles/fvd-press.jpg" alt="FVD Press" align="left">In addition to the existing titles, and WM9, fourteen FVD films were planned to be released soon: Air Panic, Avalanche, City of Fear, Death Train, Edges of the Lord, The Order, Us Seals, The Confession, Earthquake, Fire, Volcano, A Wobot Christmas, The Opponent, Diary of a City Priest, Buried Lies, Combustion, Malie.</p>

<p><span stype="float:right"><table><tr><td align="center"><br />
Idar Electronics Co. below<br><img src="/images/articles/idar-electronics-co.jpg" alt="Idar Electronics Co." align="left"><br />
</td></tr></table></span><br />
FVD was introduced on April 5, 2004 in Taipei Taiwan. The first FVD players were made available in May 2005 for $175 with 10 free movies in Taiwan.<br />
<br clear=all><br />
With a sales promotion in Europe and the US, the global volume was estimated to reach 100,000 players in 2005, 3 million in 2006, and 5 million in 2007. Later, Taiwan's Kolin offered in November 2005 an initial sales promotion period for their first KVD-1080 player with an HDMI cable included, and three 1080i FVD movie discs, all for $240.</p>

<p>Content is available mainly from independent studios but the alliance is doing efforts to expand to 100 the initial offering of titles by including other major studios.</p>

<table border=1 cellpadding=0 width="94%"
 style='border:outset #CCCCCC 1.0pt'>
 <tr>
  <td width="99%" colspan=5 style='width:99.28%;border:inset #CCCCCC 1.0pt;
  background:#000066;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>Comparison of
  Formats</span></strong>
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <p class=MsoNormal style='line-height:22.0pt'><span style='font-family:s\04E9&#2;;
  color:black'>&nbsp;
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>DVD</span></strong>
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>FVD</span></strong>
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>HD DVD</span></strong>
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>BD</span></strong>
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span style='
  color:white'>Leading Organization</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span
  style='color:black'>DVD Forum
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='color:black'>Taiwan</span><span
  style='color:black'>'s  </span><span
  style='font-family:Arial;color:black'>AOSRA
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span
  style='color:black'>DVD Forum
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span
  style='color:black'>Blu-ray Association
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span style='
  color:white'>Physical Capacity (single side)</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 4.7GB (DL) 8.5GB
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 5.4/6GB (DL) 9.8/11GB<br>
  (TL) 15GB
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 15GB
  <span style='
  color:black'>(DL) 30GB
  <span style='
  color:black'>(TL) 45GB
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 25GB
  <span style='
  color:black'>(DL) 50GB
  <span style='
  color:black'>(4L) 100GB(TDK)
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span style='
  color:white'>Laser</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Red Laser (650nm)
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Red Laser (650nm)
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Blue Laser (405nm)
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Blue Laser (405nm)
  </td>
 </tr>
 <tr style='height:37.35pt'>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <strong><span style='
  color:white'>Resolution</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>720x480i60
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>1280x720p24<br>
  1920x1080i60
  <span style='
  color:black'>1920x1080p24
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>1280x720p<br>
  1920x1080i/p
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>1280x720p<br>
  1920x1080i60
  <span style='color:black'>1920x1080p24
  </td>
 </tr>
</table>

<p><i><span style='text-align:center"'>Chart sourced from FDV with my additions/corrections</span></i></p>

<p>Stay tuned to my next article on this series, Hi-Def DVD Part III, the Blue battle.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>May 30, 2006 10:31 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(385)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 385)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/highdef-dvd-part-ii-taiwan-challenger.php" type="text/javascript" charset="utf-8"></script>
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
