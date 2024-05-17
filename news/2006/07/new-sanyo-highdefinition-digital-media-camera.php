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
		AND e.entry_id = 403";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 403 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 403 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 403";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/07/new-sanyo-highdefinition-digital-media-camera.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 403";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Sanyo High-Definition Digital Media Camera" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Sanyo High-Definition Digital Media Camera" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New Sanyo High-Definition Digital Media Camera" />
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
	<title>HDTV Magazine - New Sanyo High-Definition Digital Media Camera</title>
	<meta name="keywords" content="high definition, sanyo xacti, still images, memory card, digital media, video, sanyo, definition, high, mode, digital, still, camera, recording, images, media, xacti, new, card, shooting, features, memory, players, display, lcd" />
	<meta name="description" content="A video recording mode optimized for the video iPod&amp;reg;, 16:9 widescreen still picture mode and easy, convenient in-camera video editing. The SANYO Xacti HD1a features an ergonomic, one-handed operation. It can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. in September 2006 at a very competitive $699.99** MSRP." />
	<meta name="title" content="New Sanyo High-Definition Digital Media Camera" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New Sanyo High-Definition Digital Media Camera" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/07/new-sanyo-highdefinition-digital-media-camera.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="A video recording mode optimized for the video iPod&amp;reg;, 16:9 widescreen still picture mode and easy, convenient in-camera video editing. The SANYO Xacti HD1a features an ergonomic, one-handed operation. It can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. in September 2006 at a very competitive $699.99** MSRP." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=403', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/07/new-sanyo-highdefinition-digital-media-camera.php">New Sanyo High-Definition Digital Media Camera</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 25, 2006</b>
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
				<p class="editorial">This press release is actually over a week old, but due to the website difficulties we were experiencing at the time, it was never published. Hopefully it is still news to some of you, although it is not necessarily a "bulletin" anymore. &raquo; Shane</p>

<center><b>New SANYO High-Definition Digital Media Camera Features Optimized Recording for Portable Video Players and 16:9 Still Mode Ideal for Widescreen Viewing</b></center>

<center><i>SANYO Xacti HD1a Offers In-Camera Editing, 2.2-inch LCD Display and $699.99 MSRP</i></center>

<p><img src="/images/bulletins/Xacti-HD1a.jpg" alt="Xacti HD1a" align="left">CHATSWORTH, Calif., July 13 /PRNewswire-FirstCall/ -- A video recording mode optimized for the video iPod&reg; and other portable video players tops the advanced feature set of the new SANYO Xacti HD1a high-definition compact digital media camera. Following quickly on the strong sales and critical success of the SANYO Xacti HD1 introduced in January 2006, the new high-definition HD1a also adds such compelling features as a selectable 16:9 widescreen still picture mode and easy, convenient in-camera video editing.</p>

<p>The SANYO Xacti HD1a retains its title as the world's smallest and lightest high-definition digital media camera*. Featuring ergonomic, one-handed operation, the camera can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. from SANYO, the world's leading manufacturer of digital cameras and components, in September 2006 at a very competitive $699.99** MSRP.</p>

<p>"The remarkable success of our precedent-setting Xacti HD1, along with user feedback, prompted SANYO to incorporate several new key features into the new HD1a," said John Lamb, Senior Marketing Manager, for SANYO Fisher Company's Audio Video Division. "Our inclusion of a 30 frames-per-second 320 x 240 pixel video recording mode -- optimized for viewing MPEG-4 video clips on a video iPod or other portable video player -- reflects a shift by consumers towards on-the-go viewing."</p>

<p><b>Video recording optimized for personal media players</b><br />
The HD1a's new "Web-SHQ" recording mode is designed specifically to capture video destined for the video iPod and other popular MPEG-4 capable personal media players. For optimal playback on such devices, video is captured at a resolution of 320 x 240 pixels and a smooth and natural 30 frames per second.</p>

<p><b>16:9 still shooting</b><br />
An all-new 16:9 shooting mode allows users to capture 3.8 megapixel stills in the same widescreen format as their high-definition videos for eye-catching viewing on a 16:9 television screen.</p>

<p><b>Convenient in-camera video editing</b><br />
The HD1a features enhanced video editing functions, enabling quick A>B deletions and easy combining of video clips. In-camera editing makes it easy to remove unwanted material and helps conserve memory card space.</p>

<p><b>High-definition engine</b><br />
The SANYO Xacti HD1a is powered by high-precision LSI (large-scale integration) circuitry for advanced, high-definition image processing. This powerful "high-definition engine" quickly executes a vast number of calculations and enables the HD1a to realize image processing functions such as high-definition 720p processing, real-time MPEG-4 compression and noise reduction.</p>

<p><b>2.2-inch LCD display</b><br />
The HD1a features a large Sanyo-developed 2.2-inch LCD display with 210,000 total pixels for exceptional viewability. The display flips out from the camera and rotates up to 285 degrees on axis for taking great video or still images even in difficult locations.</p>

<p><b>10x optical zoom</b><br />
The HD1a features a generous and highly efficient 10x optical zoom lens. Built from 12 elements designed in 9 groups and with a built-in neutral density filter, the 10x zoom lens has a maximum aperture of f/3.5 in both wide and telephoto angles, allowing for clear images in lower light situations. Combined with a 10x digital zoom capability, the HD1a is capable of a total 100x zoom.</p>

<p><b>Digital video and stills, all in one</b><br />
Like the Xacti HD1 and all previous SANYO Xacti digital media cameras, the HD1a can record still images in addition to video clips. Utilizing the 5.36 megapixel (total) CCD, the HD1a captures beautiful 5.1 megapixel still images which are recorded directly onto a standard SD memory card. The camera can record both 5.1 megapixel still images and high-definition (1280 x 720-pixel) digital video at the same time with a simple press of the shutter button during the shooting of a video clip.***</p>

<p><b>21 minutes of HD video per Gigabyte of memory</b><br />
The HD1a can record up to 21 minutes of 720p HD video per Gigabyte on a standard SD or SDHC memory card. That's up to 42 minutes on a 2-Gigabyte card or 84 minutes on the soon to be released 4-Gigabyte SDHC card (cards sold separately). Alternatively, HD1 users can select to record in Standard Definition mode (640 x 480 pixels at 30fps progressive) for up to one hour per Gigabyte of recording capacity. Users can quickly switch between high-definition and standard-definition recording modes by simply pressing the "HD/Norm" button located beneath the LCD display.</p>

<p><b>Ergonomic and lightweight</b><br />
Designed for convenient, one-handed operation and one-thumb control of most key functions, the truly ergonomic SANYO HD1a is ready to use whenever inspiration strikes, at home or away. Lightweight at only 8.3 ounces (including battery and a standard SD memory card), the HD1a measures 3.1" W x 4.7" H x 1.4" D.</p>

<p><b>Key SANYO Xacti HD1a features include:</b><br />
- 320 x 240 "Web-SHQ" video mode; optimal for playback on many personal media players<br />
- 16:9 widescreen MPEG-4 video (HD-SHQ / HD-HQ modes)<br />
- 16:9 widescreen digital still mode for stunning widescreen TV playback<br />
- Highly efficient 10x optical zoom<br />
- 2.2" rotating LCD display<br />
- CD-quality AAC-LC (MPEG-4 Audio) stereo recording<br />
- Enhanced video editing functions for quick A>B deletions and easy combining of clips<br />
- 60 fps Fluid Motion Recording (640 x 480 TV-HR Mode)<br />
- Rapid 5.1 megapixel sequential still shooting<br />
- Pop-up flash with double the brightness of conventional models<br />
- Anti-shake digital image stabilizer<br />
- Talking navigation guide for first-time users<br />
- Super-fast 1.7-second camera startup<br />
- Versatile manual mode enables advanced-control shooting<br />
- Super Macro shooting down to 1 cm (W) / 1 m (T)<br />
- Self timer (2 seconds / 10 seconds)<br />
- Voice recorder function: over 33 hours recording time with optional 2 GB SD Memory Card<br />
- Red-eye reduction mode<br />
- Multifunction docking station<br />
- High-capacity SANYO rechargeable Lithium-ion battery<br />
- Remote control included<br />
- Exif Print and Print Image Matching III<br />
- PictBridge-capable for PC-Free printing with PictBridge-compatible printers</p>

<p>SANYO Electric Co., Ltd. (Nasdaq: SANYY) is a $23 billion manufacturer and distributor of consumer and commercial electronics, including multimedia and telecommunication products. Based in Chatsworth, California, SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.) markets digital cameras, PCS phones, audio systems, portable and mobile electronics, televisions, DVD players, dictation devices, home appliances, LCD projectors, security video equipment and air conditioning systems.</p>

<p>For more information and additional specifications, please visit <a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyodigital.com/">www.sanyodigital.com</a>. Visit <a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyodigital.com/">www.sanyodigital.com</a> and click on "HD1a" > "Dealer Images" for downloadable hi-res product images.</p>

<center><i>###</i></center>
<i>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</i>

<p>*   Among commercially available high-definition media cameras, as of July 13, 2006.<br />
**  Estimated Selling Price: Actual prices set by dealer are subject to change.<br />
*** Depending on the mode used to take still images, simultaneous video clip shooting may be interrupted.</p>

<p><u><b>Sanyo Press Release:</b></u><br />
<a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyo.com/aboutsanyo/press_releases_detail.cfm?id=171">http://www.sanyo.com/aboutsanyo/press_releases_detail.cfm?id=171</a></p>

<p><u><b>HD1a Product Page:</b></u><br />
<a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyodigital.com/video_cameras/HD1A/index.html">http://www.sanyodigital.com/video_cameras/HD1A/index.html</a></p>

<p><u><b>Editorial Contact:</b></u><br />
Michael R. Harris, Harris Public Relations<br />
for SANYO Fisher Company<br />
Tel: (714)966-0258, E-mail: hpr1@earthlink.net</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 25, 2006  4:26 AM</b>
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
			<?=getComments(403)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 403)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/07/new-sanyo-highdefinition-digital-media-camera.php" type="text/javascript" charset="utf-8"></script>
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