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
		AND e.entry_id = 1633";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1633 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1633 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1633";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-line-of-high-definition-camcorders-including-model-with-worlds-highest-total-pixel-count.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1633";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Expands Line of High Definition Camcorders, Including Model With World\'s Highest Total Pixel Count*" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Expands Line of High Definition Camcorders, Including Model With World\'s Highest Total Pixel Count*" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Expands Line of High Definition Camcorders, Including Model With World\'s Highest Total Pixel Count*" />
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
	<title>HDTV Magazine - Panasonic Expands Line of High Definition Camcorders, Including Model With World's Highest Total Pixel Count*</title>
	<meta name="keywords" content="high definition, memory card, built memory, still images, models feature, hdc, panasonic, memory, models, camcorder, high, camcorders, recording, feature, new, definition, image, video, card, images, users, mode, shooting, built, sdhc" />
	<meta name="description" content="Panasonic today expanded its 2009 High Definition (HD) camcorder line with six new full-HD models, ranging from introductory to semi-professional. Three new models, the HDC-HS300, HDC-TM300 and HDC-HS250, are more advanced and feature a newly developed 3MOS chip system, while the three other introductory models, the HDC-HS20, HDC-TM20 and HDC-SD20, offer features ideal for first-time users wanting simple but powerful video capabilities. All six High Definition models feature..." />
	<meta name="title" content="Panasonic Expands Line of High Definition Camcorders, Including Model With World's Highest Total Pixel Count*" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Expands Line of High Definition Camcorders, Including Model With World's Highest Total Pixel Count*" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-line-of-high-definition-camcorders-including-model-with-worlds-highest-total-pixel-count.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic today expanded its 2009 High Definition (HD) camcorder line with six new full-HD models, ranging from introductory to semi-professional. Three new models, the HDC-HS300, HDC-TM300 and HDC-HS250, are more advanced and feature a newly developed 3MOS chip system, while the three other introductory models, the HDC-HS20, HDC-TM20 and HDC-SD20, offer features ideal for first-time users wanting simple but powerful video capabilities. All six High Definition models feature..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1633', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-line-of-high-definition-camcorders-including-model-with-worlds-highest-total-pixel-count.php">Panasonic Expands Line of High Definition Camcorders, Including Model With World's Highest Total Pixel Count*</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=343&category=HD Camcorders & Cameras">HD Camcorders & Cameras</a></b>
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
				<p class="prtitle">Panasonic Expands Line of High Definition Camcorders, Including Model With World's Highest Total Pixel Count*</p>

<center><i>Panasonic Offers HD Camcorders for Experienced and New Users</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/</B> -- Panasonic today expanded its 2009 High Definition (HD) camcorder line with six new full-HD models, ranging from introductory to semi-professional. Three new models, the HDC-HS300, HDC-TM300 and HDC-HS250, are more advanced and feature a newly developed 3MOS chip system, while the three other introductory models, the HDC-HS20, HDC-TM20 and HDC-SD20, offer features ideal for first-time users wanting simple but powerful video capabilities. All six High Definition models feature Panasonic's first camcorder touch-screen that allows icons displayed on the LCD to be easily operated by touching them with a fingertip.</p>

<p>Panasonic's new 3MOS system, available in the HDC-HS300, HDC-TM300 and HDC-HS250, assures high image quality for both motion and still images by using three full-HD MOS sensors to provide the world's highest total pixel count of 9,150,000 pixels (3,050,000 pixels x 3)*. These three semi-professional camcorders are equipped with a newly developed, large-aperture Leica Dicomar lens (filter diameter: 43 mm) and a 1/4.1-inch image sensor. They also record still images with a 10.6-megapixel resolution and feature an 8.3-megapixel resolution for still images when simultaneously recording with video. The HDC-HS300, HDC-TM300 and HDC-HS250 have a luminance of 1.6 lx, allowing them to shoot video in very low-light conditions.</p>

<p>"More and more, we are moving towards a High Definition lifestyle, and Panasonic's camcorders are no exception," said Dennis Eppel, Vice President, Network Business Group, Panasonic Consumer Electronics Company. "With the introduction of six new models, we are answering a consumer demand to record videos in High Definition quality that are ready for viewing on High Definition TVs. We also recognize that not all users are the same - so we are introducing HD models appropriate for both advanced and new users alike."</p>

<p>All the semi-professional camcorder models feature more advanced Intelligent Auto (iA) technologies, making them intuitive to use to help users more easily create high-quality video. Panasonic's iA features include:</p>

<p>--  Shooting Mode: Chooses the most suitable shooting mode based on the subject.</p>

<p>--  Auto Focus (AF) Tracking: Once the user touches a selected subject on the touch-screen -- the subject can be at the center of the screen or at an edge -- and locks the focus on the subject, AF Tracking keeps the subject in focus even if it moves or turns to one side. This records the subject with the optimal AF/AE (auto focus/auto exposure) settings.</p>

<p>--  Scene Detection for Motion and Still Images: The iA automatically selects the most suitable settings from Portrait, Scenery, Macro, Night Portrait, and Night Scenery modes.</p>

<p>--  Optical Image Stabilization (O.I.S): Gyrosensors detect hand-shake and a lens unit shifts to correctly align the optical axis, so that images are sharp and help reduce blur. The corrections happen at a remarkable rate of 4,000 times per second. Users can capture clear, sharp images even when shooting long-distance zoom shots at times when hand-shake is typically a big concern. These models also have two O.I.S. modes designed exclusively for still-image shooting.</p>

<p>The HS300 and HS250 both feature an internal 120 GB** Hard Disk Drive (HDD), which can store up to 50 hours of recording (in HE mode). The HS20 has an 80 GB HDD and can record 33 hours and 20 minutes in HE mode. These camcorders can easily copy recorded video from the SDHC/SD Memory Card to the hard disk, or vice versa.</p>

<p>New to Panasonic's camcorder line are two twin memory models, the HDC-TM300 -- part of the semi-professional line and HDC-TM20, one of the introductory models. A "twin memory" camcorder means the model can record to the built-in memory or an SDHC/SD Memory Card (optional), and feature a Relay Recording function. With this feature, when the built-in memory becomes full, the camcorder switches the recording media to the SDHC/SD Memory Card to provide uninterrupted recording***. Also, camcorders with a built-in memory versus an HDD are lighter, making them more portable. The HDC-TM300 has a 32 GB built-in memory, allowing for 12 hours of recording (in HE mode), while the HDC-TM20 comes with a 16 GB built-in memory for 6 hours of recording (in HE mode).</p>

<p>The HS300/TM300 models come equipped with a manual ring, an electronic viewfinder and a microphone terminal -- all ideal for semiprofessionals looking for more expressive video shooting. These models allow delicate and precise fingertip control of the zoom, focus, aperture, shutter speed and white balance. The HDC-HS250 was designed to be a compact HD camcorder and is virtually identical to the HS300/TM300 models in camera performance, but without the manual functions and electronic viewfinder, thus providing the same high image quality, but in a smaller body for those who want high-quality shooting in a portable body.</p>

<p><br />
<B>New Introductory High Definition Camcorders HDC-HS20/TM20/SD20</B></p>

<p>The HDC-HS20, HDC-TM20 and HDC-SD20 are Panasonic's introductory HD camcorder models, recommended for new or intermediate users wanting an easy-to-use camcorder that still produces beautiful and vivid video worthy of sharing with friends and family. All three models feature a 16x optical zoom and a Leica Dicomar lens, but differ in recording media and thus vary slightly in overall size and weight.</p>

<p>The HDC-HS20 is a hybrid model capable of recording onto its internal 80 GB hard disk drive or an SDHC/SD Memory Card (optional accessory). The HDC-TM20 is a twin memory model that can record onto its built-in 16 GB memory or an SDHC/SD Memory Card (optional accessory), and comes in three body colors: black, silver and red. The HDC-SD20 uses an SDHC/SD Memory Card as a recording media and thus is extremely compact and lightweight. All three models also feature AF Tracking.</p>

<p>All Panasonic's 2009 High Definition camcorder models enable the transfer of recorded data to an Apple Macintosh computer so that recorded video clips can be edited in HD image quality using iMovie software. Other new features of all six High Definition camcorders include:</p>

<p>--  Highlight Playback -- This new feature is designed for users who want to view recorded images as quickly as possible. It detects zooming, panning, scene changes, increases and decreases in sound level, faces and more in recorded images as "highlights" by using Panasonic's Intelligent Index System.</p>

<p>--  Auto Power LCD -- Automatically adjusts the brightness of the LCD screen according to the shooting conditions.  In indoor dark places, it reduces the LCD brightness to 1/3 the normal level to conserve energy. While in well-lit outdoor locations, the LCD is twice as bright as normal level to make the image on the LCD easier to see in the bright surroundings.  Manual adjustments are also possible if the user desires.</p>

<p>--  1.9-sec**** Quick Power On -- Helps Catch Sudden Shooting Opportunities - When the camcorder is switched on, it is in recording standby and ready to shoot in just 1.9 seconds. This quick power-on time allows the camcorder to stay in standby mode, thus saving energy. When added to the Quick Start mode, which allows the camcorder to start up in just 0.6 seconds, this is an extremely fast camcorder.</p>

<p>--  5-Microphone, 5.1-Channel Surround Sound System -- The camcorders feature a 5.1-channel surround sound system with 5 microphones. When the recording is played on a 5.1-channel home cinema system, viewers are surrounded by clear, detailed sound that makes them feel as if they are right in the middle of the action. A Zoom Mic function links the microphone's action to the camera's action.</p>

<p>--  Networking -- The camcorder is compatible with Panasonic's VIERA Link. Connect it to a VIERA HDTV via an HDMI mini cable, and operate the camcorder using the TV remote control and following on-screen prompts. This adds extra ease and convenience to the fun of viewing full-HD videos.</p>

<p>--  SD Networking with VIERA and Blu-ray Disc(TM) Player -- The VIERA Image Viewer Function lets the user view recorded images on the large screen of a Panasonic VIERA Series Plasma or LCD ***** HDTV by simply inserting the SD Memory Card into the SD Memory Card Slot on the TV. This allows instant playback of video clips recorded in the AVCHD format in full-HD quality. Similarly, video recordings can be easily played by using a Panasonic Blu-ray Disc(TM) Player******.</p>

<p>Suggested retail prices for all camcorders are as follows: $1,399.95 for the HDC-HS300; $1,299.95 for the HDC-TM300; $999.95 for the HDC-HS250; $799.95 for the HDC-HS20; $649.95 for the HDC-TM20; $599.95 for the HDC-SD20. All models come in black and will be available in April 2009.</p>

<p>* For home-use camcorders, as of January 7, 2009. Effective motion image pixels: 6,210,000 (2,070,000 pixels x 3).</p>

<p>** 1 GB = 1,073,741,824 bytes. Usable capacity will be less.</p>

<p>*** The SDHC/SD Memory Card must have memory space available that is larger than the data in the built-in memory to be merged.</p>

<p>**** Recording onto an SDHC/SD Memory Card or built-in memory only. In the HS300/TM300/HS250, the time may become longer than 1.9 seconds in the still-image.</p>

<p>*****P54Z1/P65V10/P58V10/P54V10/P50V10/P50G15/P46G15/P42G15/P54G10/P50G10/ P46G10/P42G10.</p>

<p>****** BD80/BD60/BD70V.</p>

<p><br />
<B>About Panasonic Consumer Electronics Company</B></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Panasonic Corporation. (NYSE:PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations. Panasonic has a full suite of easy-to-use camcorders, including High Definition models that produce rich, vivid videos. Additional company information for journalists is available at www.panasonic.com/pressroom.</p>

<p>Source: Panasonic </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  4:58 PM</b>
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
			<?=getComments(1633)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1633)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/panasonic-expands-line-of-high-definition-camcorders-including-model-with-worlds-highest-total-pixel-count.php" type="text/javascript" charset="utf-8"></script>
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