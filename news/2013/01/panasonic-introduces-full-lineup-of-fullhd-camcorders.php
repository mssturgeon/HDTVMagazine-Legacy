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
		AND e.entry_id = 4986";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4986 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4986 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4986";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2013/01/panasonic-introduces-full-lineup-of-fullhd-camcorders.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4986";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Introduces Full Lineup of Full-HD Camcorders" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Introduces Full Lineup of Full-HD Camcorders" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Introduces Full Lineup of Full-HD Camcorders" />
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
	<title>HDTV Magazine - Panasonic Introduces Full Lineup of Full-HD Camcorders</title>
	<meta name="keywords" content="megapixel still, lighting situations, dim lighting, highlight playback, bsi sensor, images, panasonic, full, shooting, recording, new, oom, image, high, wide, functions, sensor, quality, intelligent, camcorders, easy, megapixel, home, even, playback" />
	<meta name="description" content="Panasonic announces five new Full-HD camcorders to expand its product lineup for 2013.  The new lineup includes the HC-X920, HC-V720, HC-V520, HC-V210 and HC-V110. The high-end HC-X920 model is equipped with the New 3MOS System PRO with BSI (Backside Illumination) Sensor to enhance the three MOS sensors for excellent shooting performance even in dim lighting situations. The use of the BSI Sensor reduces noise to 50% of previous models. The X920 captures and records momentous scenes in high-definition in any environment, including the bright outdoors to dimly lit indoors.

The X920,V720 and V520 all feature the new..." />
	<meta name="title" content="Panasonic Introduces Full Lineup of Full-HD Camcorders" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Introduces Full Lineup of Full-HD Camcorders" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2013/01/panasonic-introduces-full-lineup-of-fullhd-camcorders.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic announces five new Full-HD camcorders to expand its product lineup for 2013.  The new lineup includes the HC-X920, HC-V720, HC-V520, HC-V210 and HC-V110. The high-end HC-X920 model is equipped with the New 3MOS System PRO with BSI (Backside Illumination) Sensor to enhance the three MOS sensors for excellent shooting performance even in dim lighting situations. The use of the BSI Sensor reduces noise to 50% of previous models. The X920 captures and records momentous scenes in high-definition in any environment, including the bright outdoors to dimly lit indoors.

The X920,V720 and V520 all feature the new..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4986', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2013/01/panasonic-introduces-full-lineup-of-fullhd-camcorders.php">Panasonic Introduces Full Lineup of Full-HD Camcorders</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 10, 2013</b>
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
				<p class="prtitle">Panasonic Introduces Full Lineup of Full-HD Camcorders</p>

<center><i>HC-X920 with 3 MOS System PRO with BSI Sensor Achieves New Level of High-Sensitivity Image Quality</center></i><br />
<br />

<p>HC-X920, HC-V720, HC-V520 with Built-in Wi-Fi Functions Allow Easy Image Sharing</p>

<p><strong>LAS VEGAS, Jan. 7, 2013 /PRNewswire/</strong> -- Panasonic announces five new Full-HD camcorders to expand its product lineup for 2013.  The new lineup includes the HC-X920, HC-V720, HC-V520, HC-V210 and HC-V110. The high-end HC-X920 model is equipped with the New 3MOS System PRO with BSI (Backside Illumination) Sensor to enhance the three MOS sensors for excellent shooting performance even in dim lighting situations. The use of the BSI Sensor reduces noise to 50% of previous models. The X920 captures and records momentous scenes in high-definition in any environment, including the bright outdoors to dimly lit indoors.</p>

<p>The X920,V720 and V520 all feature the new Level Shot function, which automatically adjusts tilting images, in addition to 5-Axis Correction HYBRID O.I.S., which thoroughly suppresses blurring. The camcorders also offer Built-in Wi-Fi functions that allow live view control from a smartphone and enable Real Time Broadcasting to family and friends who live far away. These models allow users to shoot, play and share images more easily and conveniently.  All of the new models also offer new levels of performance and function. Panasonic's wide lineup of camcorders meets the diverse lifestyles of consumers. The cutting-edge technologies incorporated in Panasonic camcorders add to the fun of capturing, viewing, archiving and sharing images.</p>

<p><br />
<strong>HC-X920</strong><br />
Ultrafine Image Quality<br />
- 1080 TV Lines Horizontal Resolution<br />
- 20.4-Megapixel Still Picture Recording</p>

<p>Assist Functions for Professional Shooting<br />
- Manual Ring satisfies the needs of high-end amateur photographers wanting full control of camcorder operation. The large manual ring allows intuitive operation of zoom, focus, iris, shutter speed and white balance.<br />
- Level Gauge to detect the horizontal/vertical angle of view with its internal level working with an acceleration sensor. This function corrects even slight tilting that is difficult to see with the naked eye.<br />
- Electronic Viewfinder (EVF) is anextendable 263,424-dot equivalent viewfinder is easy to use and convenient for focusing and other adjustments. The EVF makes it easy to see the shooting scene under bright sunlight when the LCD might be difficult to view.</p>

<p>Built-in Wi-Fi Functionality<br />
- Built-in Wi-Fi function enables Real Time Broadcasting of important events via Ustream, while recording images in Full-HD. This lets users deliver live images to people with Internet access around the world. There is also a function to send e-mail to notify the start of streaming to preregistered e-mail addresses.<br />
- By using the dedicated Panasonic Image App, users can remotely check audio recording, zoom In/Out and shutter ON/OFF, while viewing the live image on a smartphone or tablet. Highlight Playback of recorded images is also possible with a smartphone or tablet. Favorite scenes can be shared on SNS with simple drag-and-drop operation.<br />
- Home Monitoring lets users check on pets or family at home from the outside. They can even talk to their pets or family while viewing them on the screen, and record images remotely from a smartphone.<br />
- DLNA / Wi-Fi Compatible* via an access point at Home that can be used to transfer data wirelessly to the TV or a tablet in the living room, kitchen or other rooms in the home and play back images on the large-screen TV.</p>

<p>* DLNA wireless playback availability depends on the Recording Mode.</p>

<p>Dynamic Wide 29.8mm to 25x Zoom Shooting with Tilt and Blur Correction<br />
- The wide shooting range expands the breadth of photographic expression, extending from 29.8mm wide angle to Intelligent 25x zooming. This makes it possible to capture everything from panoramic scenery to telephoto images for obtaining dynamic images of subjects at a distance.</p>

<p>Other Beneficial Features<br />
- 1,152,000-dot 3.5-inch Touch LCD<br />
- Full HD 3D Recording with Optional 3D Conversion Lens VW-CLT2<br />
- 5.1ch Surround, Zoom and Focus Microphone and New Wind Noise Canceller<br />
- Plug and Copy to External HDD</p>

<p><br />
<strong>HC-V720</strong><br />
New High Sensitivity Sensor Records Clear, Beautiful Images Even in Dim Lighting Situations. Versatile Camcorder with Wi-Fi Functions for Easy Image Sharing.</p>

<p>High-Quality Images Even in Dim Lighting Situations</p>

<p>- The combination of the newly developed New High Sensitivity Sensor and the Crystal Engine PRO, with its superb noise reduction performance, records bright, high-quality, and low-noise images in both bright and dim lighting situations. Beautiful images can be recorded under a variety of situations, including indoor shooting and night scenery shooting.<br />
- Horizontal resolution of 1080 TV lines* makes it possible to shoot high-resolution Full-HD images that express even extremely small details.<br />
- 20.4-megapixel Still Picture Recordingwith high resolution<br />
- Dynamic Wide 28 mm to 50x Zoom Shooting with Tilt and Blur Correction<br />
- Built-in Wi-Fi Functions for Extra Convenience and Shooting, Viewing and   Sharing of Images<br />
- Real Time Broadcasting with Full-HD Recording<br />
- NFC (Near Field Communication) and Remote Shooting with Smartphone<br />
- Highlight Playback and Easy Share<br />
- Home Monitoring and Remote Video Recording<br />
- DLNA / Wi-Fi Compatible</p>

<p>* According to the JEITA ITE chart and CIPA (for digital cameras) ISO 1233 resolution chart.</p>

<p>Other Beneficial Features<br />
- 3.0-inch Touch Wide LCD<br />
- 5.1ch Surround, Zoom and Focus Microphone and New Wind Noise Canceller<br />
- 2D to 3D Conversion Function<br />
- Plug and Copy to External HDD</p>

<p><br />
<strong>HC-V520</strong><br />
Super-Powerful Optical 50x/Intelligent 80x Zoom in a Compact Body.</p>

<p>Built-in Wi-Fi Functions Allow Highlight Playback and Image Sharing the Easy Way.<br />
- Equipped with Intelligent 80x Zoom/Optical 50x Zoom, these models can capture even the images of craters on the moon in Full-HD image quality. The V520 Series lets users easily shoot distant subjects that are too far away to capture clearly with conventional zoom functions.<br />
- 5-Axis HYBRID O.I.S. + and Level Shot<br />
- Built-in Wi-Fi Functions for Extra Convenience and Shooting, Viewing and Sharing of Images<br />
- Real Time Broadcasting<br />
- NFC (Near Field Communication) and Remote Shooting with Smartphone<br />
- Highlight Playback and Easy Share<br />
- Home Monitoring and Remote Video Recording<br />
- DLNA / Wi-Fi Compatible*<br />
2.25-Megapixel BSI Sensor with 10-Megapixel Still Images</p>

<p>* DLNA wireless playback availability depends on the Recording Mode.</p>

<p>Other Beneficial Features<br />
- 3.0-inch Touch Wide LCD<br />
- New Wind Noise Canceller<br />
- 2D to 3D Conversion Function<br />
- Plug and Copy to External HDD</p>

<p><br />
<strong>HC-V210</strong><br />
Light Weight and Compact Size for Extra Portability, and a 135-Minute Battery* for Extended Shooting. Full-HD Camcorder with a Wealth of Convenient Functions Including Intelligent 72x Zoom and POWER O.I.S.</p>

<p>* 135 minutes of recording time with a fully charged battery (HG mode) in NTSC areas.</p>

<p>Intelligent 72x Zoom with POWER O.I.S.<br />
The HC-V210/V210M enables ultra-telephoto Intelligent 72x zoom shooting with a compact body. These models are also equipped with POWER O.I.S. that eliminates blurring without any degradation in image quality. As a result, the original image quality captured by the lens remains intact, all the way from wide-angle to powerful zoom.<br />
- 32.3mm wide-angle lens will fit an expansive landscape or a large group of people into the frame. This lens boasts F1.8 brightness to allow shooting in dim lighting situations.<br />
- Full-HD 1920 x 1080/60p Recording and 10-Megapixel Still Pictures with<br />
2.20-Megapixel BSI Sensor</p>

<p>Other Beneficial Features<br />
- Highlight Playback<br />
- Eye-Fi Compatible<br />
- Plug and Copy to External HDD</p>

<p><br />
<strong>HC – V110</strong><br />
Full-HD Recording and Intelligent 72x Zoom Compact Camcorders Suitable for Easy Recording of High-Quality Images.</p>

<p>- Intelligent 72x Zoom<br />
-  Full-HD Recording and 8.9-Megapixel Still Pictures with 2.07-Megapixel BSI Sensor<br />
- Image stabilizer to suppress blurring</p>

<p>About Panasonic Consumer Marketing Company of North America<br />
Based in Secaucus, N.J., Panasonic Consumer Marketing Company of North America, a Division of Panasonic Corporation of North America, the principal North American Subsidiary of Panasonic Corporation (NYSE: PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations, offers a wide-range of consumer solutions in the U.S. and Canada.  The Company's portfolio of innovative consumer products ranges from VIERA Full HD 3D Televisions, Blu-ray players, LUMIX Digital Cameras, Camcorders, Home Audio, Cordless Phones, Home Appliances, Wellness and Personal Care products and more.</p>

<p>Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. In the 2012 Interbrand Annual Best Global Green Brands ranking, the Panasonic brand jumped four spots to number six: http://www.interbrand.com/en/best-global-brands/Best-Global-Green-Brands/2012-Report.aspx.  Follow Panasonic on Twitter @PanasonicUSA, and additional company information for media is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>SOURCE Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 10, 2013  4:28 PM</b>
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
			<?=getComments(4986)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4986)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2013/01/panasonic-introduces-full-lineup-of-fullhd-camcorders.php" type="text/javascript" charset="utf-8"></script>
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