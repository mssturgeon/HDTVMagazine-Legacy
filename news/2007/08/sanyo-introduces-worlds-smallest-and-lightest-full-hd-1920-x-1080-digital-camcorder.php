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
		AND e.entry_id = 687";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 687 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 687 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 687";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 687";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sanyo Introduces World\'s Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sanyo Introduces World\'s Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sanyo Introduces World\'s Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" />
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
	<title>HDTV Magazine - Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder</title>
	<meta name="keywords" content="high definition, docking station, still images, smallest lightest, memory card, sanyo, video, digital, full, xacti, image, high, still, images, new, definition, recording, station, function, camera, camcorder, display, shooting, lens, card" />
	<meta name="description" content="SANYO, a world leading digital camera manufacturer, introduces the Xacti HD1000, the world's smallest and lightest full HD digital camcorder*1. The sleek and simple-to-use device records video in Full HD (1920 x 1080 pixels) and also takes 4-megapixel digital still images. The HD1000 utilizes the advanced MPEG-4 AVC/H.246 video format and features a 10x optical HD lens and a large 2.7-inch widescreen display.

The SANYO Xacti HD1000 will be available in the U.S.A. in September, 2007, and has an MSRP of $799.99*2.

The new SANYO Xacti HD1000 weighs only 9.5 ounces and has a total volume of only 16.6 cubic inches, making it the world's smallest and lightest Full HD recording (1920 horizontal and 1080 vertical pixels) digital camcorder*1. It incorporates advanced MPEG-4 AVC/H.264 video compression, enabling up to..." />
	<meta name="title" content="Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="SANYO, a world leading digital camera manufacturer, introduces the Xacti HD1000, the world's smallest and lightest full HD digital camcorder*1. The sleek and simple-to-use device records video in Full HD (1920 x 1080 pixels) and also takes 4-megapixel digital still images. The HD1000 utilizes the advanced MPEG-4 AVC/H.246 video format and features a 10x optical HD lens and a large 2.7-inch widescreen display.

The SANYO Xacti HD1000 will be available in the U.S.A. in September, 2007, and has an MSRP of $799.99*2.

The new SANYO Xacti HD1000 weighs only 9.5 ounces and has a total volume of only 16.6 cubic inches, making it the world's smallest and lightest Full HD recording (1920 horizontal and 1080 vertical pixels) digital camcorder*1. It incorporates advanced MPEG-4 AVC/H.264 video compression, enabling up to..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=687', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php">Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 30, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Sanyo Introduces World's Smallest and Lightest Full HD (1920 X 1080) Digital Camcorder</p>

<center><i>Xacti HD1000 Features Advanced MPEG-4 AVC/H.264 Video Format, Improved Ergonomics, and Convenient New 'Xacti Library' File Saving & TV Playback Solution</i></center><br />
<br />

<p><img src="/images/products/sanyo-xacti-hd1000.jpg" alt="Sanyo Xacti HD1000" align="left" /><B>CHATSWORTH, Calif., Aug. 30 /PRNewswire-FirstCall/</B> -- SANYO, a world leading digital camera manufacturer, introduces the Xacti HD1000, the world's smallest and lightest full HD digital camcorder*1. The sleek and simple-to-use device records video in Full HD (1920 x 1080 pixels) and also takes 4-megapixel digital still images. The HD1000 utilizes the advanced MPEG-4 AVC/H.246 video format and features a 10x optical HD lens and a large 2.7-inch widescreen display.</p>

<p>The SANYO Xacti HD1000 will be available in the U.S.A. in September, 2007, and has an MSRP of $799.99*2.</p>

<p>"The SANYO Xacti line has gained a reputation for excellence and innovation," said John Lamb, SANYO's Senior Marketing Manager for the Xacti camcorder series. "The new Xacti HD1000 continues our pioneering approach by combining full 1080i HD video recorded in the advanced AVC/H.264 standard with a massive 2.7-inch widescreen display and a simple hardware and software interface for file saving and TV playback."</p>

<p>SMALLEST, LIGHTEST, FULL HD CAMCORDER</p>

<p>The new SANYO Xacti HD1000 weighs only 9.5 ounces and has a total volume of only 16.6 cubic inches, making it the world's smallest and lightest Full HD recording (1920 horizontal and 1080 vertical pixels) digital camcorder*1. It incorporates advanced MPEG-4 AVC/H.264 video compression, enabling up to approximately 85 minutes of Full HD (1920 x 1080) video recording or up to five hours, 14 minutes of TV-quality (640 x 480) video recording onto an 8GB SDHC Memory Card (memory card sold separately).</p>

<p>SANYO developed a new high-speed image processing engine to handle the high capacity demands of Full HD data. This engine utilizes SANYO's proprietary codec, enabling Full HD compatibility with the AVC/H.264 format, and makes it possible to do with one simple yet powerful chip the complex processing which previously required two separate chips. The required high compression ratio is gracefully achieved due to optimization of the image process algorithm actuator, while consuming a mere 4.2 watts of power.</p>

<p>NEW ERGONOMIC DESIGN</p>

<p>Designed for easy, one-handed operation with all key conveniently functions thumb-operable, the HD1000 is comfortable to hold, even for extended periods. The device is the first to incorporate ergonomic results based on collaborative research between SANYO and Japan's Chiba University in regard to optimizing the lens-to-grip angle to minimize strain on the muscle groups used while holding and recording. Testing focused on muscle responses in six places on the arm, as well as surveys asking for individual assessments and evaluations. As a result of these tests and responses, it was discovered that a lens-to-grip angle of 105 degree, the angle subsequently used on the HD1000, was optimal.</p>

<p>As part of its stylish and thoughtful new design, the HD1000 also adds a distinctly useful feature frequently requested by users of previous Xacti camcorders -- the ability to have the lens level with the ground when the device is used with a tripod.</p>

<p>FULL 1080i HD SENSOR</p>

<p>Incorporating the latest high-definition CMOS sensor, the SANYO Xacti HD1000 camcorder captures full 1080i high-definition video (1920x1080) at 60 frames-per-second. Designed to record the rich and vibrant colors of real life, the HD1000 also captures the subtle tones to provide a natural-looking result. The HD1000's CMOS sensor provides the quick responsiveness needed to capture fast moving subjects and SANYO's noise reduction technology helps obtain the cleanest signal from each pixel.</p>

<p>NEW "XACTI LIBRARY" FUNCTION FOR EASY FILE SAVING AND PLAYBACK*3</p>

<p>The large data size of Full HD movies, along with demanding file saving and playback processing usually requires special hardware and/or burning video to a disc. The HD1000's new "Xacti Library" feature makes it exceptionally easy to save video and image files by simply connecting the included USB cable*4 from the docking station to an external hard disk drive*5 (hard drive not included). After connecting the docking station to an external hard drive and to a television (via an HDMI cable), the user places the camera into the docking station and -- using the menu that appears on the television screen -- can search through the thumbnails and choose either to play the file or save it to the external hard disk.</p>

<p>10X OPTICAL HD LENS</p>

<p>At the front of the HD1000 is a commanding 10x all-glass HD lens. The HD1000's fast f/1.8-2.5 lens is capable of allowing almost four times more light through to assist in lower light venues. Consisting of eight groups and eleven total lenses with a built-in neutral density filter, the HD1000's lens provides a spectacular field-of-view with a 38-380 mm range (35 mm equivalent). Combined with the 10x digital zoom, the HD1000 provides up to 100x total zooming capability.</p>

<p>LARGE 2.7 INCH WIDESCREEN DISPLAY</p>

<p>The HD1000 features a large 2.7 inch widescreen Liquid Crystal Display (LCD). The display flips out from the camera and rotates up to 285 degrees on axis, allowing you to take great video or still images even from difficult-to-view positions, which is especially useful when shooting in large crowds or in small rooms.</p>

<p>FOUR MEGAPIXEL DIGITAL IMAGES</p>

<p>The Xacti HD1000 enables simultaneous shooting of 4-megapixel still images and HD movie clips, with a simple press of the shutter button during the recording of a video clip. Users need never miss another precious photo opportunity. (Depending on the mode used to take still images, simultaneous video clip shooting may be interrupted. While shooting video clips, using the digital image stabilizer may change the angle of view for still images.)</p>

<p>HDMI HIGH-DEFINITION OUTPUT</p>

<p>It's easy to view and share high-definition video on your HD television with the HD1000. Using the HDMI (High-Definition Multimedia Interface) terminal built into the base station, just one cable connects your camcorder to your TV for a totally digital output. HDMI carries both the video and audio signals in digital form for the highest quality playback.</p>

<p>RECORDS TO CONVENIENT SD/SDHC MEMORY CARD</p>

<p>The SANYO Xacti HD1000 records high-definition and photos directly to a standard SD or SDHC Memory Card. In fact, the HD1000 is capable of recording up to 85 minutes of 1080i high-definition video on a single 8GB card (sold separately). The SD Memory Card's compact size and weight makes it ideal for the diminutive HD1000. The minimal power requirements of the SD card also contribute to longer recording and playback times. When connected to the computer via the USB cable, the HD1000 acts as a standard card reader. Transferring images and videos to your computer has never been easier.</p>

<p>IMAGE STABILIZATION</p>

<p>High-definition can't hide shaky or erratic camera movement. That's why SANYO's HD1000 comes with a sophisticated image stabilizer for both stills and video. This handy feature operates in both wide-angle and telephoto modes, giving every shot a solid, professional-looking feel. For video-compatible anti-shake correction, SANYO further developed the digital stabilizer based on previous proprietary image stabilizer technology, and advanced the technology to be more accurate in its correction, increasing the image area detection function. Also, SANYO newly developed its proprietary "Superposition function" for higher still image quality. This function allows for clear pictures of the subject even when moving or rotation occurs.</p>

<p>AUTOMATIC "FACE CHASER" FUNCTION FOR STILL IMAGES</p>

<p>The HD1000 includes a new "Face Chaser" function that automatically detects and isolates faces to assist the camera's exposure and auto-focus. The HD1000 is capable of detecting up to 12 independent faces at a time.</p>

<p>MANUAL CONTROLS</p>

<p>The HD1000 features versatile manual controls for advanced shooting. The following settings can be manually adjusted according to the shooting situation: Manual focus adjustment (16 settings); aperture adjustment (6 stops); exposure compensation (1.8 EV, 0.3 EV steps); shutter speed (13 settings); and image-quality adjustment (for sharpness and color saturation).</p>

<p> ADDITIONAL HD1000 FEATURES:</p>

<p> -- Random Access: Each video is recorded as an individual MPEG-4 and each<br />
 still as a JPEG so you can have true random access allowing you to<br />
 review a specific image or video quickly and easily, without waiting<br />
 for tape rewinding or fast forwarding.</p>

<p> -- Easy Camera to PC Connection: One of the more frustrating aspects of<br />
 working with any digital media camera is juggling all the wires and<br />
 connections necessary when you want to use it with external components<br />
 for viewing or to download files. SANYO's HD1000 streamlines the whole<br />
 process with an innovative docking station that provides an instant<br />
 HDMI, component, composite or S-video connection to a TV and a USB<br />
 connection for a PC. The HD1000 even recharges its internal battery<br />
 when nested in the docking station.</p>

<p> -- Super fast Startup: With its tapeless design, the HD1000 eliminates the<br />
 need to queue up a tape deck or get a DVD or hard drive spinning,<br />
 allowing the HD1000 to begin shooting in as little as two seconds! When<br />
 the HD1000 is powered on, closing the LCD display puts the HD1000 in<br />
 standby mode. Simply open the display and the HD1000 automatically<br />
 powers up and can begin immediately recording in as little as two<br />
 seconds.</p>

<p> -- Equipped with 'SIMPLE' mode so even beginners can create high quality,<br />
 beautiful high definition movies</p>

<p> -- Defaults for automatic settings can be accessed quickly with the<br />
 "Full-Auto" button</p>

<p> -- Adopts newly developed 2.7 inch 230,000 pixel, widescreen TFT-LCD<br />
 monitor designed for viewing HD footage.</p>

<p> -- Bundled with "Nero 7 Essentials" for playback and "Ulead DVD<br />
 MovieFactory 5 SE" for editing.</p>

<p> -- Newly developed and long-lasting 1900 mAh Lithium-ion battery (DB-L50)</p>

<p> -- Allows use of external accessories such as an external strobe, video<br />
 light, microphone, etc.</p>

<p> -- Optional adapter lenses for increased telephoto or wide-angle<br />
 conversion</p>

<p> -- Six selectable video resolution modes and eight selectable still photo<br />
 resolutions</p>

<p> -- Continuous Still Image Shooting function - 7 frames per second*6</p>

<p> -- Able to take still pictures while in the middle of Full HD movie<br />
 recording*7</p>

<p> -- 9-image quick display function</p>

<p> -- In-camera editing</p>

<p> -- 48 kHz, 16-bit, 2-channel sound</p>

<p> -- Headphone Jack</p>

<p><br />
 About SANYO</p>

<p>SANYO Electric Co., Ltd. is a multi-billion-dollar global leader in providing solutions for the environment, energy and for lifestyle applications based on its Brand Vision 'Think GAIA'. SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.), based in Chatsworth, California, markets mobile phones, digital projectors, digital still cameras, digital media camcorders, home appliances, security video equipment, audio systems, portable and mobile electronics and HD televisions.</p>

<p>For more information and additional specifications, please visit http://www.sanyodigital.com/. For downloadable hi-res product images, go to http://www.sanyodigital.com/ and click on "Dealer Images".</p>

<p>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</p>

<p> *1 For consumer-use Full HD digital movie cameras, size is by volume (as<br />
 of August 30, 2007)<br />
 *2 Manufacturer's Suggested Retail Price. Pricing subject to change at any<br />
 time. Actual prices are determined by individual dealers and may<br />
 vary.<br />
 *3 Via docking station<br />
 *4 Uses a special cable (included), HDMI cable sold separately<br />
 *5 SANYO does not guarantee that the docking station will be compatible<br />
 with all external drives.<br />
 *6 Recorded as 4.0 Mega-pixels<br />
 *7 Recorded as 2.0 Mega-pixels</p>

<p>Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20070830/LATH034<br />
AP Archive: http://photoarchive.ap.org/<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: SANYO Fisher Company</p>

<p>CONTACT: Michael R. Harris of Harris Public Relations, +1-714-966-0258,<br />
hpr1@earthlink.net, for SANYO Fisher Company</p>

<p>Web site: http://www.sanyo.com/</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 30, 2007  7:28 AM</b>
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
			<?=getComments(687)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 687)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/sanyo-introduces-worlds-smallest-and-lightest-full-hd-1920-x-1080-digital-camcorder.php" type="text/javascript" charset="utf-8"></script>
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