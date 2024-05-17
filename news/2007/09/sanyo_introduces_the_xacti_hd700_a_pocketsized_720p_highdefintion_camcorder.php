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
		AND e.entry_id = 733";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 733 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 733 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 733";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/09/sanyo-introduces-the-xacti-hd700-a-pocketsized-720p-highdefintion-camcorder.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 733";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder" />
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
	<title>HDTV Magazine - SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder</title>
	<meta name="keywords" content="high definition, sanyo xacti, memory card, pocket sized, sanyo fisher, sanyo, video, high, xacti, camcorder, easy, digital, photos, still, new, images, definition, movies, camera, image, design, easily, card, print, elements" />
	<meta name="description" content="SANYO, a world leading digital camera manufacturer, debuts the Xacti HD700, a pocket-sized, 720p high-definition digital camcorder. The HD700 is a high-performance camcorder capable of recording both high-definition video and 7.1-megapixel photos easily to an SD or SDHC memory card (memory card sold separately). Featuring an ultra-compact, ergonomic shape, the HD700 is specifically designed for publishing high quality video and high resolution still photos in formats ideally suited for Web-use or sharing. Fun and easy to use anytime inspiration strikes, the HD700 is truly an advanced communication tool for the emerging information age.

The SANYO Xacti HD700 will be available in" />
	<meta name="title" content="SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/09/sanyo-introduces-the-xacti-hd700-a-pocketsized-720p-highdefintion-camcorder.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="SANYO, a world leading digital camera manufacturer, debuts the Xacti HD700, a pocket-sized, 720p high-definition digital camcorder. The HD700 is a high-performance camcorder capable of recording both high-definition video and 7.1-megapixel photos easily to an SD or SDHC memory card (memory card sold separately). Featuring an ultra-compact, ergonomic shape, the HD700 is specifically designed for publishing high quality video and high resolution still photos in formats ideally suited for Web-use or sharing. Fun and easy to use anytime inspiration strikes, the HD700 is truly an advanced communication tool for the emerging information age.

The SANYO Xacti HD700 will be available in" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=733', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/09/sanyo-introduces-the-xacti-hd700-a-pocketsized-720p-highdefintion-camcorder.php">SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 26, 2007</b>
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
				<p class="prtitle">SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder Designed for Fun, Everyday Use</p>

<center><i>Stylish, Easy-To-Use Camcorder Shoots 720p Video and 7.1 Megapixel Photos</i></center><br />
<br />

<p><img src="/images/products/sanyo-xacti-hd700.jpg" alt="Sanyo Xacti HD700" align="left" /><B>CHATSWORTH, Calif., Sept. 26 /PRNewswire-FirstCall/</B> -- SANYO, a world leading digital camera manufacturer, debuts the Xacti HD700, a pocket-sized, 720p high-definition digital camcorder. The HD700 is a high-performance camcorder capable of recording both high-definition video and 7.1-megapixel photos easily to an SD or SDHC memory card (memory card sold separately). Featuring an ultra-compact, ergonomic shape, the HD700 is specifically designed for publishing high quality video and high resolution still photos in formats ideally suited for Web-use or sharing. Fun and easy to use anytime inspiration strikes, the HD700 is truly an advanced communication tool for the emerging information age.</p>

<p>The SANYO Xacti HD700 will be available in Silver, Red and a gorgeous Brown color in the U.S.A. at the end of October, 2007, and has an MSRP of $599.99(1).</p>

<p>"The new SANYO Xacti HD700 is our best everyday-use camcorder ever," said John Lamb, SANYO's Senior Marketing Manager for the Xacti line. "At 16% smaller and 10% lighter than our popular HD2, the HD700 combines a host of new features in a sleek, affordable and easy-to-use camcorder."</p>

<p><br />
<B>HIGH-QUALITY VIDEO, EASY WEB USE</B></p>

<p>The HD700 uses the same new optical processing engine found in the recently announced VPC-HD1000, which is unique in its ability to process images quickly with lower power consumption. In addition to utilizing SANYO's proprietary H.264 engine, the camera only needs 4.0 watts of power, made possible by combining two engine chips into one miniaturized fast-processing image engine.</p>

<p>Video is recorded in the advanced MPEG-4 AVC/H.264 format encoding technology. This advanced compression delivers exceptional video clarity and detail while maintaining the smallest file size possible.</p>

<p>Also, in addition to the reduction in data capacity needed to store the images and video clips, photos or movies are easily copied and loaded on a personal computer or uploaded and shared on the Internet for online use on blogs, video-sharing websites and personal websites.</p>

<p><br />
<B>7.1-MEGAPIXEL STILL IMAGES</B></p>

<p>The HD700 captures 7.1-megapixel still photos and provides excellent low-light performance all the way to the ISO 3200 level of sensitivity.</p>

<p><br />
<B>AUTOMATIC "FACE CHASER" FUNCTION FOR STILL PHOTOS</B></p>

<p>The HD700 includes the new "Face Chaser" function that automatically detects and isolates faces to assist the camera's exposure and auto-focus. The HD700 is capable of detecting up to 12 independent faces at a time.</p>

<p><br />
<B>5X OPTICAL ZOOM</B></p>

<p>The HD700 digital media camera features a 5x all-glass optical zoom with a bright maximum aperture of f/3.5. Consisting of nine groups and twelve total lenses (3 aspheric elements, 5 aspheric surfaces), the HD700's lens provides a fantastic field-of-view with a 38-190 mm range (35 mm equivalent). Combined with the 12x digital zoom, the HD700 provides up to 60x zooming capability.</p>

<p><br />
<B>LARGE 2.7 INCH WIDESCREEN DISPLAY</B></p>

<p>The Xacti HD700 features a large 2.7 inch Liquid Crystal Display (LCD). The bright, widescreen display flips out from the camera and rotates up to 285 degrees on an axis that allows you to take great video or still images from otherwise-difficult-to-view positions, especially useful when shooting in large crowds or in small rooms.</p>

<p><br />
<B>MAC OS COMPATIBLE</B></p>

<p>The HD700 is designed to work with Apple's full complement of video editing applications including the new iMovie 08. Easily edit your movies and add them to your website or YouTube for sharing or create versions for iPod, iPhone or the Apple TV.</p>

<p><br />
<B>ADOBE(R) PREMIERE(R) ELEMENTS 3.0 INCLUDED</B></p>

<p>For Windows users, the SANYO Xacti HD700 includes the powerful award-winning Adobe Premiere Elements 3.0 video editing software. Adobe Premiere Elements 3.0 software makes creating and sharing impressive home videos a snap. Burn your footage to DVD in two simple steps, complete with a DVD menu and scene index, or easily assemble your movie by rearranging clips with drag-and-drop simplicity. And share your movies on DVD, the web, mobile phones, and virtually anywhere else.</p>

<p><br />
<B>CONVENIENT SD/SDHC MEMORY CARD STORAGE</B></p>

<p>The SANYO Xacti HD700 records high-definition and photos directly to a standard SD or SDHC Memory Card. The camcorder can record up to 2 hour and 46 minutes of 720p high-definition video on a single 8GB card (sold separately).</p>

<p><br />
<B>RANDOM ACCESS</B></p>

<p>Each video is recorded as an individual MPEG-4 and each still as a JPEG so you can have true random access allowing you to review a specific image or video quickly and easily, without waiting for tape rewinding or fast forwarding.</p>

<p><br />
<B>ERGOMOMIC DESIGN</B></p>

<p>The Xacti series has become known for its ergonomic design and small, easy-to-use file sizes. The HD700 is also designed for convenient, one-handed use and adopts the new 105 degree angle design that recent research has proven to be less tiring to hold and shoot than typical camcorders. Easy-to-hold and easy-to-shoot, the HD700 raises the bar in compact camcorder design.</p>

<p><br />
<B>ADDITIONAL SANYO XACTI HD700 FEATURES:</B></p>

<p> -- HDMI High-Definition Output (with included Docking Station)<br />
 -- Digital Image Stabilization<br />
 -- Superfast Startup (Record in as little as 1.3 seconds).<br />
 -- Playback directly onto a HD or standard TV screen.<br />
 -- Easy connection to VCR or DVD recorder.<br />
 -- Expand the fun of viewing home movies and photos on television with<br />
 'Xacti Library' for easy playback and saving of files.<br />
 -- Equipped with 'SIMPLE' mode for even beginners to create high quality,<br />
 beautiful high definition movies.<br />
 -- 'Talking Navigation' informs user of current state/setting<br />
 -- 'Super Macro' allows close-ups of 1 cm<br />
 -- In-Camera Editing<br />
 -- Equipped with 'Wind Noise Reduction' reducing wind noise from the<br />
 microphone when recording movies<br />
 -- Compatible with Exif Print(2) and PRINT Image Matching III(3) for<br />
 printing<br />
 -- Print pictures without a computer using PictBridge<br />
 -- Innovative Docking Station Included<br />
 -- Remote Control Included</p>

<p><br />
<B>About SANYO</B></p>

<p>SANYO Electric Co., Ltd. is a multi-billion-dollar global leader in providing solutions for the environment, energy and for lifestyle applications based on its Brand Vision 'Think GAIA'. SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.), based in Chatsworth, California, markets mobile phones, digital projectors, digital still cameras, digital media camcorders, home appliances, security video equipment, audio systems, portable and mobile electronics and HD televisions.</p>

<p>For more information and additional specifications, please visit http://www.sanyodigital.com/. For downloadable hi-res product images, go to http://www.sanyodigital.com/ and click on "Dealer Images".</p>

<p>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</p>

<p> (1) Manufacturer's Suggested Retail Price. Pricing subject to change at<br />
 any time. Actual prices are determined by individual dealers and may<br />
 vary.<br />
 (2) Exif Ver2.2 is an industry standard established for file formatting of<br />
 digital cameras<br />
 (3) When using PRINT Image Matching compatible printer</p>

<p>Source: SANYO Fisher Company</p>

<p>CONTACT: Michael R. Harris of Harris Public Relations, +1-805-418-7198,<br />
hpr1@earthlink.net, for SANYO Fisher Company</p>

<p>Web site: http://www.sanyo.com/</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 26, 2007  5:35 AM</b>
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
			<?=getComments(733)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 733)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/sanyo-introduces-the-xacti-hd700-a-pocketsized-720p-highdefintion-camcorder.php" type="text/javascript" charset="utf-8"></script>
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