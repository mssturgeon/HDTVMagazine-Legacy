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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, viglink_suppress, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 4074";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];
	# I was going to use this and discovered that I could turn off insertion separately from affiliation
	#$viglink_suppress = ($row_aux['viglink_suppress'] == 1) ? 'nolinks' : '';

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4074 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4074 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4074";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4074";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3D World 2010 Conference in NYC" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3D World 2010 Conference in NYC" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3D World 2010 Conference in NYC" />
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
	<title>HDTV Magazine - 3D World 2010 Conference in NYC</title>
	<meta name="keywords" content="aspect ratio, world conference, cinemascope aspect, passive glasses, blu ray, aspect, ratio, content, cinemascope, panel, conference, passive, avatar, shown, screen, image, glasses, world, same, lcd, show, theater, sports, movie, used" />
	<meta name="description" content="The 3D World 2010 Conference took place on October 13-14, 2010 in New York City as part of the Content and Communications World Conference (CCW).   I attended this conference also last year when it was an HD conference with 3D tracks and 3D exhibitors within CCW.  I decided to return this year to see the growth of the 3D industry within the event, and to have the opportunity of meeting again with 3D content producers, video editors, and professional 3D equipment manufacturers that make 3D possible at the local theater and now in the home.

Last year..." />
	<meta name="title" content="3D World 2010 Conference in NYC" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3D World 2010 Conference in NYC" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The 3D World 2010 Conference took place on October 13-14, 2010 in New York City as part of the Content and Communications World Conference (CCW).   I attended this conference also last year when it was an HD conference with 3D tracks and 3D exhibitors within CCW.  I decided to return this year to see the growth of the 3D industry within the event, and to have the opportunity of meeting again with 3D content producers, video editors, and professional 3D equipment manufacturers that make 3D possible at the local theater and now in the home.

Last year..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4074', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important nolinks"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare nolinks" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php">3D World 2010 Conference in NYC</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November 25, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><a href="http://ccwexpo.com/3dworld.asp">The 3D World 2010 Conference</a> took place on October 13-14, 2010 in New York City as part of the <a href="http://ccwexpo.com/">Content and Communications World Conference (CCW)</a>. I attended this conference also last year when it was an <a href="http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php">HD conference</a> with 3D tracks and 3D exhibitors within CCW. I decided to return this year to see the growth of the 3D industry within the event, and to have the opportunity of meeting again with 3D content producers, video editors, and professional 3D equipment manufacturers that make 3D possible at the local theater and now in the home. <p>Last year the Canadian company <a href="http://www.miranda.com/vid.php?i=videos&amp;video=MP3D&amp;KeepThis=true&amp;TB_iframe=true&amp;height=380&amp;width=640">Miranda Technologies</a> made an excellent presentation of “3D / Stereoscopic TV - A Basic Tutorial”, covering the 3D subject from the camera to the display device at home. Many attendees appreciated that track in 2009 and Miranda made an even better presentation this year. I congratulated Michael Proulx, CTO of Miranda Technologies, who made the presentation. <p><span class="caption left" style="width:295px"><img alt="SONY Dual 3D Camera Rig" src="http://www.hdtvmagazine.us/articles/images/3fb4f1bf5355_8D2/clip_image002_a470433e-3df6-4b13-b1ec-9f4bced4206e.jpg" width="293" height="391"><br />SONY Dual 3D Camera Rig</span>The tracks at the CCW conference were mainly dedicated to the professional industry in 3D, HD, IPTV, 4K, satellite, media production, etc. This year I focused my visit on 3D tracks/exhibitors. There were presentations that highlighted how much more difficult it is to capture 3D content with camera angles that differ from those used for 2D-HD, to make dual-view convergence decisions mixing various content depths overlapped by logos, game scores, and graphics, and avoid extreme visual situations that may produce viewing discomfort if exaggerated or done wrong. Interesting panel discussions took place regarding the pros and cons of using the same feed of content acquired for 3D also for a 2D HD broadcast feed, rather than doubling up cameras and crews. There were also panel discussions about 2D content converted to 3D, as a studio work or done on-the-fly by some of the new 3D HDTVs, such as Samsung.  <p>I met Quantel’s staff again, this time for a demonstration of how they <a href="http://www.quantel.com/page.php?u=b6b1c07d0faf7f15e8a56feb8acb3d29">use their 3D equipment</a> to process and improve 3D images. Later, Danny Peters, Director of Creative Services – North America of Quantel joined a presentation with Craig Price, Stereo DI Editor to discuss how 3D has changed the workflow for filmmakers in production and post.  <p><img style="float:right" src="http://www.hdtvmagazine.us/articles/images/3fb4f1bf5355_8D2/clip_image005_00abd3cf-5788-4007-a483-a78b644c3414.jpg" width="288" height="270">I also attended a track about “3D Sports, strategies for implementation and growth”. Among the panel of experts was Kenneth Aagaard, EVP operations, Engineering and Production Services of CBS Sports and <a href="http://3alitydigital.com/2010/03/steve-schklair/">Steve Schklair, CEO</a>, <a href="http://3alitydigital.com/">3ality Digital</a> Systems. 3D clips from various sports, and even an Avatar 3D trailer, were shown to talk about <a href="http://broadcastengineering.com/podcast/training-3-d-crews-reaches-critical-level-20100928/">the details in producing 3D content</a> (podcast interview courtesy of Broadcast Engineering). <p><b></b> <h2>The Avatar 3D Trailer “in CinemaScope on a 16:9 screen”?</h2> <p>The Avatar 3D trailer was shown on the same CinemaScope widescreen aspect ratio I have seen the original 3D movie in the local theater. IMAX cinemas showed the movie at a more squarish aspect ratio. The demo at 3D World was done with a Sony 4K projector with polarizing filters and a 16:9 screen showing a CinemaScope 3D image that was cropped with top/bottom black bars and displayed as dual 1080p interleaved images viewed with RealD polarized glasses. <p>As usual for 3D, the 3D image was low in luminance, but the image quality was acceptable considering that the projector was very far away from the screen. Although James Cameron likes the CinemaScope aspect ratio (and used it on many of his movies) he <a href="http://entertainment.ca.msn.com/movies/features/article.aspx?cp-documentid=23942281">prefers</a> the 16:9 aspect ratio for Avatar, which is the original aspect ratio of the movie. I personally prefer the CinemaScope aspect ratio, especially on modern action/epic movies, including Avatar, so I welcomed the aspect ratio of the demo. <p>The interesting part is that this 3D World Conference presentation of a 16:9 original-aspect-ratio movie, using a 16:9 screen, a 16:9 4K Sony projector, released as 16:9 in Blu-ray 2D and DVD discs (and soon a 3D disc in the same aspect ratio), was rather shown in CinemaScope, which contradicts the director’s approach of always show the movie maximizing image height but without sacrificing the width available on the screen, justifying the vertical top/bottom cropping in wider CinemaScope screens, but not on a 16:9 screen. <p>I got some comments after the conference that explained that the pressure of a crazy schedule and last minute conference arrival did not provide sufficient time to check the aspect ratio of the 3D Avatar trailer before it was actually shown. I sympathized with the pressure, I was in a similar situation; I traveled for 12 hours roundtrip for a 7 hour conference day. But I was actually more concern with why consumers were not offered an option on aspect ratios as well.  <p>Compared to the 16:9 format of the released Blu-ray disc, the CinemaScope display aspect ratio at the theater provided a wider visual impact relative to its shorter height. Reportedly, the film was <a href="http://bluray.highdefdigest.com/news/show/Industry_Trends/3D/Drew_Taylor/Avatar/James_Cameron/Jon_Landau/20th_Century_Fox/High-Def_Digest_Talks_With_Avatar_Producer_Jon_Landau/4591">shot as 16:9</a> to offer a taller image at IMAX, and home’s HDTVs, which are now at over 50% of US households (and black bars on letterboxed movies are not cheered by most). <a href="http://www.collider.com/2010/03/24/james-cameron-interview-avatar-blu-ray-also-talks-titanic-3d-and-avatar-2/">According to James Cameron</a>: <i>“We finished the picture in 16×9 and then we vertically extracted the cinemascope when we were mastering the film for theatrical release“. </i>I tried to do the extraction from the 16:9 disc myself at <a href="http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php">my</a> CinemaScope home theater but the resulting image was not the same as the theater (I will cover this subject in my next article).<b> </b> <p>As I mentioned above, the CinemaScope aspect ratio was not available as an option on the released 2D Blu-ray Avatar 16:9 video transfer, nor will be available on the 3D version when released with the Panasonic panel’s package. During the panel discussion at 3D World, and considering the technical image innovation in 3D of Avatar historically, I opened the discussion (or the can of worms I should say) of why the Blu-ray transfer did not also offer the CinemaScope aspect ratio, if the version was already extracted by the director for the theaters, <a href="http://www.avsforum.com/avs-vb/showthread.php?t=1240365">so consumers</a> can choose the disc format of the aspect ratio of the movie they have seen at the theater, or the one they prefer. <p>The Avatar trailer shown at this 3D World conference confirmed once again that 3D content shows better on very large projection screens and the CinemaScope aspect ratio increases the effect of 3D even further with the wide visual impact. <h2>3D Sports Demos</h2> <p>The 3D sports demos were shown using the same setup mentioned above for Avatar; a Sony 4K projector, 16:9 screen, RealD passive glasses, etc. <p>Unfortunately, I detected some image artifacts in a few 3D sports trailers, particularly the US Tennis Open 3D demo of CBS, which showed some lag and video artifacts in some areas of fast moving images, such as the tennis ball breaking up its fast trajectory more than it normally happens in HD. I brought the subject up with the technical staff and they admitted noticing the same, but they could not confirm if the problem was in the 3D content, the frame-rate/resolution, or the 3D display setup itself. <p>Would a 2D version of the same content have shown better? Perhaps, thinking that all of the 4K projector’s pixels could have been used for the single 2D image, rather than splitting the pixels among the two interleaved views of 3D for both eyes; or thinking that a faster frame rate could have been put to work to smooth the motion with frame interpolation for that particular content, a smoothing approach that film lovers typically would not want applied to the trademark flickering of 24fps of film to avoid making it look like video. <p>Most of the 3D content/viewing experience on the large screen at this show was of good quality, but I was surprised that some trailers like the US Open were chosen by a panel that may have intended to show how good 3D could be on large screens in the home. <p><b></b> <h2>Passive 3D LCDs spotted at the Show</h2> <p><span class="caption left" style="width:299px"><img alt="Figure 1 - 3D LCD Passive panel from JVC" src="http://www.hdtvmagazine.us/articles/images/3fb4f1bf5355_8D2/clip_image007_9c572497-e8d4-4c60-be1d-3d9b2664a843.jpg" width="297" height="228"><br />Figure 1 - 3D LCD Passive panel from JVC</span>As I mentioned on other 3D articles, the bulk of the consumer implementations of 3D panels are based on active shutter glasses designs, either on LCDs or plasmas, showing frame sequential images alternatively to each eye. 3D panels using the passive glasses design are not common; one main reason is that polarizing the screen to use less costly passive glasses makes the panel more expensive. <p>Other than the professional LCD panel from JVC currently available (shown on Figure 1) and the Hyundai, AUO, and the Vizio LCD panels introduced at some shows (but not yet available) no other companies announced large size panels using passive glasses designs. <br clear="both"><span class="caption right" style="width:289px"><img alt="Figure 2 - 3D LCD Passive Panel from LG" src="http://www.hdtvmagazine.us/articles/images/3fb4f1bf5355_8D2/clip_image014_6bfb25df-6d0f-4e25-85b5-710abe0c4093.jpg" width="287" height="213"><br />Figure 2 - 3D LCD Passive Panel from LG</span>However, at this 3D World show I spotted an LCD panel made by LG of approximately 50 diagonal inches that used passive glasses (shown in Figure 2). The LCD panel was not at an LG booth but was used by an exhibitor to demo their products. The exhibitor believed LG has those models for markets outside the US for now. We may see more of these passive LCD 3D panels from other companies at CES 2011 in January. <span class="caption left" style="width:327px"><img alt="JVC's booth - Real time 2D to 3D conversion" src="http://www.hdtvmagazine.us/articles/images/3fb4f1bf5355_8D2/clip_image011_b689112e-537d-4d8a-94dd-05340e6306cc.jpg" width="325" height="224"><br />JVC's booth - Real time 2D to 3D conversion</span>The passive 3D panel I saw at this 3D World show appears to be the same that was <a href="http://www.engadget.com/2010/04/01/lg-announces-ld950-passive-shutter-3dtv-for-uk-market/">quoted for the UK</a> market in April 2010 (no price or time availability was announced then), and later <a href="http://www.dealerscope.com/article/lg-launched-its-first-3d-lcd-led-tvs-commercial-market/1?sponsor=newsletter/today#utm_source=today&amp;utm_medium=enewsletter_headline&amp;utm_campaign=2010-11-16">introduced by LG</a> on the week of November 16 as a 47”panel for the hotel market, which maybe be their best method against “loosing” many $150-active-shutter-glasses from guests that forget to return them or brake them, and rather put the 3D investment on the screen itself not on the 3D glasses. <p>My next conferences are the <a href="http://www.ce.org/events/default.asp?siteUrl=%20http://speaker.ce.org/index.cfm?do=cus.meeting|meetingID=1180|style=0|meetingContentType=descriptionFull">CEA Press-preview</a> on November 10<sup>th</sup> in NYC of the <a href="http://www.cesweb.org/">CES 2011 January show</a> in Las Vegas, which I plan to attend as well, the <a href="http://www.gvexpo.com/conference/grid_sessions.php">Government Video Expo 2010</a> in Washington DC (Nov 30 – Dec 2), and the 3D workshop “From <a href="http://insightmedia.info/emailblasts/2010-11-12_3dworkshop.php">the Content Creation to Living Room Display</a>” presented by <a href="http://www.insightmedia.info/training/index.php">Insight Media University</a> (in Columbia University in NYC) on December 2nd. Until the next time.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November 25, 2010  8:50 PM</b>
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
			<?=getComments(4074)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4074)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php" type="text/javascript" charset="utf-8"></script>
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