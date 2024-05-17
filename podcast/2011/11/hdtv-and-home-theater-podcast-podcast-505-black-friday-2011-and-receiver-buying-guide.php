<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	require(BASE_DIR .'/includes/lib_amazon.php');
	require(BASE_DIR .'/includes/lib_pg.php');

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-type: text/plain');

	function getReviewHeader($asin, $amazon_tracking_id, $pg_url, $pg_price) {
		global $admindata, $debug;

		if ($asin != '') $row_amazon = getByASIN($asin);
		if ($row_amazon != '') {
			if ($debug) echo "amazon_tracking_id: $amazon_tracking_id<br />";
			if ($debug) echo "admin_data: {$admindata['amazon_associates_id']}<br />";
			$az_url = str_replace($admindata['amazon_associates_id'], $amazon_tracking_id, $row_amazon['DetailPageURL']);
			$az_image = ($row_amazon['MediumImageURL'] == '') ? '' : '<img src="'. $row_amazon['MediumImageURL'] .'" alt="'. $row_amazon['Title'] .'" height="'. $row_amazon['MediumImageHeight'] .'" width="'. $row_amazon['MediumImageWidth'] .'"/>';
			$az_product = $row_amazon['Manufacturer'] .' '. $row_amazon['Model'];
			$az_list = ($row_amazon['ListPriceFormatted'] == '') ? 'N/A' : $row_amazon['ListPriceFormatted'];
			$az_price = $row_amazon['LowestNewPriceFormatted'];
			$az_price = ($az_price == 'Too low to display') ? 'Unknown' : $az_price;

			if ($row_amazon['ProductGroup'] == 'DVD') {
				$releaseDate = date('M j, Y', strtotime($row_amazon['ReleaseDate']));
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<h2>{$row_amazon['Title']}</h2>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<b>Studio:</b> {$row_amazon['Studio']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
							<b>Release Date:</b> $releaseDate<br />
							<b>Aspect Ratio:</b> {$row_amazon['AspectRatio']}<br />
							<b>Running Time:</b> {$row_amazon['RunningTime']} minutes<br />
						</div>
					<span class="corners-bottom"><span></span></span></div>
EOT;
			} else {
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<h2>{$row_amazon['Title']}</h2>
							<b>Manufacturer:</b> {$row_amazon['Manufacturer']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
						</div>
					<span class="corners-bottom"><span></span></span></div>
EOT;
			}

			return $review_header;
		}
	}

	# Get auxiliary information
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 4568";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get Pricegrabber info
	if (intval($row_aux['pg_masterid']) > 0) $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword='. urlencode($row_pg['title']) .'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}

	# Get primary category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4568 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4568 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4568";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Pricegrabber info
	if (intval($row_aux['pg_masterid']) > 0) $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword='. urlencode($row_pg['title']) .'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}
*/

	# Get Comments
	if ($row_aux['topic_replies'] > 0) {
#		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
#		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a></li>';
		$comments = '<img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a>';
	} else {
#		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
#		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a></li>';
		$comments = '<img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a>';
	}

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta_medium_type = 'blog';

	# Determine og type
	$og_type = 'article'; // Default
	if ($category_id == 295) $og_type = 'movie'; // Blu-ray
	if ($category_id == 296) $og_type = 'game'; // PlayStation 3 (PS3) & Xbox 360

	# Set image
	$link_rel_image_src = $row_aux['image_src'];
	if ($link_rel_image_src == '') $link_rel_image_src = $row_amazon['SmallImageURL'];

	$h_buttons = <<<EOT
<div align="right">
	<span style="float:left; padding-top:10px;">$comments</span>
	<span class='st_fblike_hcount' ></span>
	<span class='st_plusone_hcount' ></span>
	<span class='st_twitter_hcount' displayText='Tweet'></span>
	<span class='st_sharethis_hcount' displayText='ShareThis'></span>
</div>
EOT;
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2011/11/hdtv-and-home-theater-podcast-podcast-505-black-friday-2011-and-receiver-buying-guide.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			break;
		case 6: # Test
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$viglink_source = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$author_headshot = '';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($user->data['is_registered']) {
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
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4568";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide" />
	<meta property="og:audio:artist" content="Ara Derderian &amp; Braden Russell" />
	<meta property="og:audio:album" content="The HDTV and Home Theater Podcast" />
	<meta property="og:audio:type" content="audio/mpeg" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			$itunes_chicklet = BASE_IMG_HOST .'/images/chicklet-itunes.gif';

			# Need a better check here if we add other podcasts
//			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
//			$xml = new SimpleXMLElement( $contents );

			$h_buttons = <<<EOT
<div id="dd_right">
	<span style="float:left; padding-top:10px;">$comments</span>
	<span class='st_fblike_hcount' ></span>
	<span class='st_plusone_hcount' ></span>
	<span class='st_twitter_hcount' displayText='Tweet'></span>
	<span class='st_sharethis_hcount' displayText='ShareThis'></span>
	<span>
		<span style="line-height:16px;vertical-align:middle;">{$xml->feed->entry['circulation']}
			<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=1826&RD_PARM1=http%3A%2F%2Fphobos.apple.com%2FWebObjects%2FMZStore.woa%2Fwa%2FviewPodcast%3Fid%3D73799860" target="_blank"
				><img src="$itunes_chicklet" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="top" height="15" width="80"></a>
		</span>
	</span>
</div>
EOT;

			break;
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($user->data['is_registered']) {
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide</title>
	<meta name="keywords" content="blu ray, lcd hdtv, hdmi inputs, street price, hdtv model, hdtv, receiver, hdmi, price, blu, ray, one, model, channel, lcd, inputs, network, street, volume, player, support, video, audio, features, watt" />
	<meta name="description" content="Its that time of year again where the HT Guys get to spend your money. We start of our buying guides with receivers. BTW, this guide is not necessarily about getting the latest product. Its about getting a good product at a great price so you may see some of last year&amp;acirc;€™s gear on the list. All these receivers are readily available online or at a big box store. This year there are some fantastic deals to be had! Enjoy!" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2011/11/hdtv-and-home-theater-podcast-podcast-505-black-friday-2011-and-receiver-buying-guide.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Its that time of year again where the HT Guys get to spend your money. We start of our buying guides with receivers. BTW, this guide is not necessarily about getting the latest product. Its about getting a good product at a great price so you may see some of last year&amp;acirc;€™s gear on the list. All these receivers are readily available online or at a big box store. This year there are some fantastic deals to be had! Enjoy!" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4568', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2011/11/hdtv-and-home-theater-podcast-podcast-505-black-friday-2011-and-receiver-buying-guide.php">HDTV and Home Theater Podcast - Podcast #505: Black Friday 2011 and Receiver Buying Guide</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>November 17, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=438&category=Deals & Discounts">Deals & Discounts</a></b>
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2">
								<?=$h_buttons?>
							</td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<h2>Today&#8217;s Show:</h2>
<h3>Black Friday 2011</h3>
<p><strong>Best Buy:</strong><br />
<a href="http://www.blackfriday.info/item/66748">Samsung 32&#8243; Class 720p LCD HDTV</a> &#8211; $277.99<br />
<a href="http://www.blackfriday.info/item/66747">Panasonic 46&#8243; Class 720p 600Hz Plasma Smart HDTV</a> &#8211; $399.99<br />
Sharp 42&#8243; Class 1080p LCD HDTV &#8211; $199.99 *<br />
<a href="http://www.blackfriday.info/item/66877">Sharp 60&#8243; Class 1080p 120Hz LCD HDTV</a> &#8211; $799.99<br />
<a href="http://www.blackfriday.info/item/66880">Sony 55&#8243; Class 1080p 120Hz LED Smart 3D HDTV w/1000-Watt Home Theater System</a> &#8211; $1,698.99<br />
<a href="http://www.blackfriday.info/item/66835">Sony BDPS185 Blu-ray Disc Player</a> &#8211; $79.99<br />
<a href="http://www.blackfriday.info/item/66744">Samsung Blu-ray Disc Player</a> &#8211; $99.99<br />
<a href="http://www.blackfriday.info/item/66842">Pioneer 625-Watt Receiver</a> &#8211; $199.99 Model 821K<br />
<a href="http://www.blackfriday.info/item/66771">Logitech Harmony 650 Advanced Remote</a> &#8211; $39.99<br />
All kinds of DVDs and Blu-rays</p>
<p><strong>Target:</strong><br />
<a href="http://www.blackfriday.info/item/67337">Samsung 32&#8243; 60Hz 720p LCD HDTV</a> &#8211; $277.99<br />
Westinghouse 46&#8243; 60Hz 1080p LCD HDTV &#8211; $298.00<br />
Element 40&#8243; 60Hz 1080p LCD HDTV &#8211; $265.00 *<br />
Sony WiFi Enabled 3D Blu-ray Player &#8211; $109.99<br />
All Kinds of DVDs and Blu-ray movies $2 &#8211; $13 New titles too!</p>
<p><strong>K-Mart:</strong><br />
<a href="http://www.blackfriday.info/item/62793">Seiki 40&#8243; 1080p LCD HDTV</a> &#8211; $299.99 *<br />
<a href="http://www.blackfriday.info/item/63609">Hitachi 32&#8243; 720p LCD HDTV (Model # LE32H405)</a> &#8211; $299.99 *<br />
<a href="http://www.blackfriday.info/item/62794">LG 47&#8243; 1080p, 120Hz, LED HDTV (Model #47LV4400)</a> &#8211; $679.99 *<br />
<a href="http://www.blackfriday.info/item/63608">Panasonic 50&#8243; 720p 600Hz Plasma HDTV (Model # TCP5032C)</a> &#8211; $499.99 *<br />
Sony Wi-Fi-Ready Blu-ray Disc Player &#8211; $79.99<br />
All kinds of DVDs and Blu-ray $3 &#8211; $13</p>
<p><strong>Sears:</strong><br />
<a href="http://www.blackfriday.info/item/72061">Panasonic 42&#8243; 1080p LED Smart HDTV (Model #TCL42E3)</a> &#8211; $599.99 *<br />
<a href="http://www.blackfriday.info/item/72097">Panasonic 55&#8243; 1080p 600Hz 3D Plasma Smart HDTV (Model # TC-P55ST30)</a> &#8211; $1,099.99<br />
<a href="http://www.blackfriday.info/item/72095">Sharp 70&#8243; 1080p 120Hz LED HDTV (Model # LC70LE632U)</a> &#8211; $1,999.99<br />
<a href="http://www.blackfriday.info/item/72007">Sharp 60&#8243; 1080p 120Hz LED HDTV (Model # LC60LE6300U)</a> &#8211; $999.99 *<br />
<a href="http://www.blackfriday.info/item/72077">Samsung BD-D5700 Blu-ray Disc Player w/Built-In Wi-Fi</a> &#8211; $99.99 *<br />
<a href="http://www.blackfriday.info/item/72078">Sony BDP-S380 Wi-Fi Ready Blu-ray Disc Player</a> &#8211; $79.99 *<br />
<a href="http://www.blackfriday.info/item/72093">Panasonic 5.1 Channel 1000 Watt 3D Blu-ray Home Theater System</a> &#8211; $299.99<br />
<a href="http://www.blackfriday.info/item/72090">Samsung HT-D5300 5.1 Channel 1000 Watt 3D Blu-ray Home Theater System</a> &#8211; $259.99 *</p>
<p>&nbsp;</p>
<h3>Receiver Buying Guide</h3>
<p>Its that time of year again where the HT Guys get to spend your money. We start of our buying guides with receivers. BTW, this guide is not necessarily about getting the latest product. Its about getting a good product at a great price so you may see some of last yearâ€™s gear on the list. All these receivers are readily available online or at a big box store. This year there are some fantastic deals to be had! Enjoy!</p>
<h4>Less than $500</h4>
<p><a href="http://www.htguys.com/shop?id=B004QR56SE">Yamaha RX-V671BL AV Receiver (Street Price $499.95)</a><br />
OK so at the time of this writing this receiver barely makes the price cut off. We used to pay well over a $1000 for a receiver like this when we first got into the game. This bad boy has 6 HDMI inputs, supports next generation audio, supports 7.1, and an iOS app for remote control. There are many more features but we donâ€™t have time to get into them all. This is perfect for someone just starting out or someone ready to move up. The 671 is priced for higher end of the entry market but can compete with receivers in the mid tier.</p>
<p><a href="http://www.htguys.com/shop?id=B004U403WM">Denon AVR-1612 5.1 Channel A/V Home Theater Receiver (Street Price $349)</a><br />
We are big fans of Denon and are happy to have such a capable receiver at this price. The 1612 is a 5.1 system that supports HDMI 1.4a. So if 3D is important to you you might want to consider this model. The 1612 has four HDMI inputs, iPod Connectivity and support for Audyssey MultEQ for optimal sound.</p>
<h4>$500 &#8211; $1000</h4>
<p><a href="http://www.htguys.com/shop?id=B005E1HWN8">Pioneer Elite Vsx-53 Av Network Receiver &#8211; 7.1 Channel 3D (Street Price $765)</a><br />
You canâ€™t go wrong with a Pioneer Elite especially at this price! The VSX-53 is an Airplay capable receiver that supports HDMI 1.4a which means that its 3D capable. There are 7 HDMI inputs and two outputs, iPod support, iOS Air Jam app for control of the unit, and its network capable just to name a few if its features. As far as video processing goes, the VSX-53 scales both analog and digital signals up to 1080p/24fps with the Marvell QdeoTM Video Scaler.</p>
<p><a href="http://www.htguys.com/shop?id=B005EVGS2E">Harman Kardon AVR 3650 7.1-Channel, 110-Watt Audio/Video Receiver with HDMI v.1.4a, 3-D, Deep Color and Audio Return Channel (Street Price $999)</a><br />
Making this category by $1 the HK 3650 is a tough competitor in this price range. The unit has 6 3D capable HDMI inputs and one output, network connectivity, iPod Support, and Dolby Volume.</p>
<p><a href="http://www.htguys.com/shop?id=B00505F01E">Onkyo TX-NR809 THX Certified 7.2-Channel Network A/V Receiver (Street Price $689)</a><br />
Each year we have at least one Onkyo on the list. Onkyo typically has great value for some higher end features. The 809 continues in that tradition with 8 HDMI Inputs and 2 Outputs with support for 3D and Audio Return Channel, remote apps for both Android and iOS, support for both Dolby Volume and Audyssey Dynamic Volume, THX Select 2 Plus, and so many more! This is truly a bang for the buck receiver!</p>
<h4>Greater than $1000</h4>
<p><a href="http://www.htguys.com/shop?id=B003R7KMRY">Marantz SR7005 Audio Video Receiver (Street Price $1699)</a><br />
Besides having a cool design that would look great in any one&#8217;s equipment rack, the Marantz SR705 is loaded with features:</p>
<ul>
<li>Ver.1.4 3D ready 6 HDMI inputs(including 1 front input) 2 selectable outputs</li>
<li>USB input for music playback with iPhone/iPod and USB memory</li>
<li>Bluetooth capability with option RX10</li>
<li>Network player function for music/photo streaming, Compatible with Windows7,</li>
<li>Audyssey Dynamic Volume, Dyamic EQ, DSX and MultEQ XT room acoustic calibration</li>
<li>Airplay Compatibility</li>
</ul>
<p><a href="http://www.integrahometheater.com/model.cfm?m=DTR-80.2&amp;class=Receiver&amp;p=i">Integra DTR 80.2 9.2 Channel Network Receiver (Street Price $2800)</a><br />
When you are ready for some serious AV processing power look no further than the 80.2. Again this unit is loaded with features including Dolby Volume, 8 HDMI inputs capabale of 3D, Network capability, front USB port with iPod support, DLNA certified, Audyssey Dynamic Volume and HDMI Video Upscaling to 1080p/24 with HQV Reon-VX. The DTR 80.2 also has plenty of features for the custom integration crowd including:</p>
<ul>
<li>Bi-Directional Ethernet and RS232 Port for Control</li>
<li>Powered Zone 2/3/4 and Zone 2/3 Pre Outs for Distributed Audio Playback in Multiple Rooms</li>
<li>Zone 2 and Zone 3 Subwoofer Outputs</li>
<li>Zone 2 Monitor Output (Component and Composite Video)</li>
<li>Independent Zone 2 and Zone 3 Bass/Treble/Balance Controls</li>
<li>Max &amp; Power On Volume Settings for Main Zone &amp; Zone 2/Zone 3</li>
<li>Dealer Settings Memory Store &amp; Recall with Lock/Unlock</li>
<li>Permanently Stored Settings</li>
<li>2 IR Inputs and 1 Output</li>
<li>3 Programmable 12V Triggers (with Adjustable Delay)</li>
<li>Optional Rack Mount Kit Available (IRK-180-4)</li>
<li>RIHD (Remote Interactive over HDMI) for System Control*</li>
</ul>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2011-11-18.mp3">Download Episode #505</a></p>
<p>&nbsp;</p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>November 17, 2011 11:47 PM</b>
					<span style="float:right">
						<span class='st_email' ></span>
						<span class='st_digg' ></span>
						<span class='st_facebook' ></span>
						<span class='st_twitter' ></span>
						<span class='st_sharethis' ></span>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(4568)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4568)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
					<?=stripslashes($author_bio)?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
		</td><td id="right">
			<div align="center">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div><br />

			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div><br />

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<?=getBoxDiscussions()?>

			<div align="right">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2011/11/hdtv-and-home-theater-podcast-podcast-505-black-friday-2011-and-receiver-buying-guide.php" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript">var switchTo5x=true;</script>
	<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
	<script type="text/javascript">
		stLight.options({
			publisher:'3da06545-0753-46cb-8739-3ffcef208c1f',
			embeds:'true',
			theme:'2'
		});
	</script>
</div></body>
</html>