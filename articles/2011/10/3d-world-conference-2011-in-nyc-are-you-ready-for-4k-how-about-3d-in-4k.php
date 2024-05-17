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
		AND e.entry_id = 4551";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4551 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4551 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4551";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/10/3d-world-conference-2011-in-nyc-are-you-ready-for-4k-how-about-3d-in-4k.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4551";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?" />
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
	<title>HDTV Magazine - 3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?</title>
	<meta name="keywords" content="video frame, auto stereoscopic, per eye, video processing, blu ray, resolution, video, image, content, hdtv, sony, quality, projector, panel, using, new, projectors, jvc, same, pixels, panels, conference, frame, technology, display" />
	<meta name="description" content="Last week I attended the 3D World conference at the Content and Communications World (CCW) 2011 in NYC. Although my main interest has been the professional aspects of the 3D industry from content acquisition to display devices I always find interesting other digital TV subjects and initiatives in conference tracks and discussion panels, this time 4K was very notorious. As I did in 3D World 2009 and in 2010 I flew in and out of NYC the same day so I had to make my conference day as effective as possible, this was a long day indeed.

HDTV Magazine is..." />
	<meta name="title" content="3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/10/3d-world-conference-2011-in-nyc-are-you-ready-for-4k-how-about-3d-in-4k.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Last week I attended the 3D World conference at the Content and Communications World (CCW) 2011 in NYC. Although my main interest has been the professional aspects of the 3D industry from content acquisition to display devices I always find interesting other digital TV subjects and initiatives in conference tracks and discussion panels, this time 4K was very notorious. As I did in 3D World 2009 and in 2010 I flew in and out of NYC the same day so I had to make my conference day as effective as possible, this was a long day indeed.

HDTV Magazine is..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4551', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/10/3d-world-conference-2011-in-nyc-are-you-ready-for-4k-how-about-3d-in-4k.php">3D World Conference 2011 in NYC: Are you ready for 4K? How about 3D in 4K?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 24, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=369&category=4K (Ultra HD)">4K (Ultra HD)</a></b>
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
				<p><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image002_13c9c82b-cfa4-4a9c-89da-e34ebbc531e3.jpg" width="310" height="328"><p>Last week I attended the <a href="http://www.ccwexpo.com/3dworld.asp">3D World conference</a> at the Content and Communications World (<a href="http://www.ccwexpo.com/">CCW</a>) 2011 in NYC. Although my main interest has been the professional aspects of the 3D industry from content acquisition to display devices I always find interesting other digital TV subjects and initiatives in conference tracks and discussion panels, this time 4K was very notorious. As I did in <a href="http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php">3D World 2009</a> and <a href="http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php">in 2010</a> I flew in and out of NYC the same day so I had to make my conference day as effective as possible, this was a long day indeed. <p>HDTV Magazine is a sponsor of this conference and I wanted to be present. I did not plan to write about the event, but that changed when I noticed the 4K coverage. So what was the 3D World about this year? It was very obvious that the industry is looking again to sell more technology to consumers after <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php">13 years of HDTV</a>, about 6 years of <a href="http://www.hdtvmagazine.com/articles/2006/01/why-1080p.php">1080p</a>, 5 of Blu-ray, and 2 of <a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php">3DTV</a>. For them it was time for another pocket shocker, actually the pocket shocker never stopped. <h2>Who needs 4K?</h2> <p>One thing is having James Cameron looking for better quality image on filming his new Avatar 2, more resolution such as 4K3D, and faster shooting speed to smooth out the frame rates of 3D imaging, such as the 48 frames-per-second, 60 fps, or even higher he commented recently, rather than the typical 24fps. <p>Another very different thing is applying the same approach for the “in-between” steps in the food chain of equipment and transmission after the camera/production point all the way to the display device at home, including broadcasting, pre-recorded media, <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi-a-digital-interface-solution.php">HDMI</a> standards, <a href="http://www.hdtvmagazine.com/articles/2006/08/hdmi-part-8-cables-for-13.php">wiring</a>, A/V equipment, enhanced compression algorithms, and the TV itself. Such idea naturally motivates a “not-again” natural reaction from consumers. <p>In that regard the “Demystifying 4K Resolution: From Scene to Seen”<a href="http://www.ccwexpo.com/sessions_byday.asp#2784"> panel</a> commented that using 4K cameras for creating content not necessarily expects 4K distribution and display, but has the benefit of making a more detailed original of the content, compared to creating it with HDTV cameras, for various future uses. <p>On his presentation of “<a href="http://pro.sony.com/bbsc/video/related-most_popular/video-cinealta_hdworld2010_4k_roadmap/">4K/2K End-to-End - The Sony 4K Roadmap</a>” <a href="http://www.ccwexpo.com/speaker_bios.asp?ID=3179">Hugo Gaggioni</a>, Chief Technology Officer for the Broadcast and Production Systems Division of Sony Electronics, indicated that a 4K CCD <div class="caption right"><img alt="" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image004_40fe2f07-c50b-45b7-9763-2919557cec4b.jpg" width="429" height="239"></div>camera with 26.5 million RGB pixels (4096x2160x3 RGB colors) is not easy to do, although Sony worked on a Q67 model that allowed the image detail to be recorded in 17.7 million RGB pixels rendering about 67% of the colorimetry of the original image (the combination of 100% green resolution, and 50% of the red and blue), as opposed to just 33% of other inferior technologies such as the filtering of the Bayer Pattern with just 8.8 million pixels (a graphical comparison is made at about 7 minutes into <a href="http://pro.sony.com/bbsc/video/related-most_popular/video-cinealta_hdworld2010_4k_roadmap/">his</a> video presentation). The panel added that 4K content converted to HD would show a better image on an HDTV than original HDTV content.  <p><div class="caption left" style="width:365px;"><img alt="Sony 3D Camera Rig" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image006_c8eca62c-a68c-4c59-8d89-328473d71492.jpg" width="363" height="360"><br />Sony 3D Camera Rig</div>The moderator of the same panel, <a href="http://www.ccwexpo.com/speaker_bios.asp?ID=3170">Mark Schubin</a>, commented that people think that 4K would increase considerably the requirements of distribution bandwidth but the overhead was actually estimated in just another 5% over the HDTV version (I believe he was referring to even using the same MPEG-2 compression standard rather than switching to a more efficient MPEG-4 for 4K transmission, although no technical explanation was offered). <p>Although not exactly the same spatial resolution of the video frame, 2K digital cinema resolution for theaters (2048x1080) is close to HDTV resolution (1920x1080), and 4K is often misinterpreted as the double of 2K resolution by many that just look at the doubled number. <p>Actually the doubling in the 4K nomenclature is referring to the gross number of pixels in the horizontal axis of the video frame (4096), the total resolution of a 4K video frame is about 4 times the HDTV resolution because it doubles the spatial resolution of the video frame in both directions, 2x horizontally (from 2048 to 4096) and 2x vertically (from 1080 to 2160). <p>The conference discussed the subject of 4K on several panels and tracks, and of course also 3D from image acquisition to displays. One track was dedicated to analyze the consequences of <a href="http://www.ccwexpo.com/sessions_byday.asp#2781">bad and good 3D</a>, and how bad 3D could harm the 3D industry and the public appreciation and acceptance of 3D in general if the only 3D they have seen is a bad 3D movie (Clash of the Titans comes to mind), or seen a movie that exploits rapid or excessive fluctuations of negative to/from positive parallax (objects seen behind the screen plane switching to in-front, and with excessive depth) which may produce visual discomfort to some, a condition that well made 3D should not cause to most viewers. <p><div class="caption right" style="width:269px"><img alt="Sony 3D Camera Rig From Back" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image009_daba7dc8-0d41-46b7-890b-7fe18e4293f2.jpg" width="267" height="356"><br />Sony 3D Camera Rig From Back</div>So what is next? Several initiatives for image quality improvement were presented. For example, at front of the video food chain (movies/cameras/production) there will be faster frame rates, 4K, and continuing with 3D (in 4K as well), and at the back of the video food chain for your home a similar movement toward quality that can also be used to upscale HD to 4K (newly announced 4K projectors, some 3DTV 4K panels, and some auto-stereoscopic 3DTV 4K panels as well). <p>Depending on the video processing capabilities of the new 4K consumer projectors this may bring new opportunities to bring to market good quality 4K scalers, the same way they did for 1080p 5 years ago, and likewise it would require that the 4K projector is capable to accept 4K content as input, a feature the new Sony projector has but not the JVC (mentioned below). I am making this comment because some of the first impressions of the Sony’s HD-to-4K upscaling processing of the new projector at CEDIA 2011 were not as good as expected, although true 4K content displayed well, more tests are required to elaborate further, and the projector is not even out yet.  <p>How to deal with the in-between steps of the 4K chain is still fuzzy, but it was clear that in order to start creating high quality 4K content, or to display upscaled HD in 4K displays, there is no immediate need to have every step of the content distribution chain capable of 4K/3D4K, although 4K Blu-ray is already in the works and we already know that the ATSC is already working in a higher quality TV broadcast standard, which also includes 3D and other features of quality. <p>Meanwhile manufacturers of displays are salivating again while anticipating more sales of new toys, and many home-theater aficionados are salivating as well, especially the ones with large <a href="http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php">CinemaScope</a> screens looking for high quality imaging. <p>Just recently a couple of companies announced 4K projectors for consumers at CEDIA 2011, <a href="http://newsroom.jvc.com/2011/09/new-jvc-home-theater-projectors-display-images-with-4k-precision/">four from JVC</a> ($8K to $12K, available in November mentioned further below, with 3840x2160 resolution, which is not actually 4K by the book but is exactly 4 times of 16x9 HDTV 1920x1080), and <a href="http://www.hdtvmagazine.com/news/2011/09/sony-introduces-the-worlds-first-4k-projector-designed-for-highend-home-theater-installations.php">one from Sony</a>, the VPL-VW1000ES &lt;$25K, 4096x2160 (true Digital Cinema Initiative 4K resolution), shipping in December thru professional installers. <p><div class="caption left" style="width:376px;"><img alt="Lee Turvey - District Sales Manager of Quantel" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image011_37f074df-edbc-4031-a374-e41f87926105.jpg" width="374" height="282"><br />Lee Turvey - District Sales Manager of Quantel</div>Both projector lines are capable of handling CinemaScope vertical stretch video processing for those using anamorphic lens, and also have memories to perform CinemaScope zoom/focus/adjustments for constant-height screen systems without requiring anamorphic lens to get rid of black bars. As opposed to the Sony, the new JVC 4K projectors display 3D as 1080p per eye, not upscaled to 4K per eye, why? They have no-4K chip inside, read further down.  <p>The 4K consumer grade projectors are offered at relatively reasonable prices considering the very high pricing of some <a href="http://pro.sony.com/bbsc/ssr/cat-projectors/cat-ultrahires/">current Sony 4K projectors</a>, or even earlier 4K projectors for movie theater applications, such as the Sony’s SRX-R110 introduced in 2005 and shown again at CES 2007 ($80K+$15K lens, shown in page 101 of the <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology Review</a> consumer edition - free pdf, or in page 169 of the <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">industry edition</a> book), if I recall correctly only the lamp for that projector cost about $3K.  <p>Another example is Sony’s <a href="http://hometheaterreview.com/sony-srx-r220-4k-professional-cinealta-front-projector-reviewed/">SRX-R220</a> for about $220K including lens, or the <a href="http://pro.sony.com/bbsc/ssr/cat-projectors/cat-digitalcinema/product-SRXR320P/">SRXR320p</a> for digital cinemas, or JVC’s old DLA-SH4K and <a href="http://www.engadget.com/2009/09/10/jvc-puts-4k-dla-rs4000-projector-in-your-home-for-just-175-000/">DLA RS-4000</a> 4K projector for $175K. <p>However, regarding JVC’s new 4K projectors the devil is in the detail, the projector from JVC does not accept 4K resolution as input, and uses an “e-Shift” display method that claims to render a 4K “precision” in the displayed image without actually using a full resolution 4K DiLA chip, and I quote from JVC’s press release <i>“available in the DLA-X90R, DLA-X70R, DLA-RS65 and DLA-RS55.&nbsp; Using e-Shift, 2D HD content is upconverted and scaled to a 4K signal (3840 x 2160) and the e-Shift technology displays it at full 4K precision.&nbsp; Compared to a Full HD (1920 x 1080) image, that’s twice the horizontal and vertical resolution and four times the number of pixels, or over 8 megapixels.&nbsp; The result is a stunningly detailed image with minimal aliasing artifacts found in standard HD displays.”</i> <p>This immediately triggered memories of Texas Instruments’ “wobulated” DLP chips that in 2004 claimed to render images with full 1920 pixels of horizontal resolution while actually using a 960 pixel/mirrors array in the chip, implemented in rear-projection TVs such as Mitsubishi (page 49 of the <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2005.pdf">2005 HDTV Technology Review</a>, free as well). The JVC uses an electronic/optical approach but for similar purposes. <p> <a href="http://www.hdtvmagazine.com/news/2011/09/sony-introduces-the-worlds-first-4k-projector-designed-for-highend-home-theater-installations.php">The new Sony VPL-VW1000ES 4K projector</a> claims that HDTV sources and Blu-ray content would be scaled up to 4K, which means 75% of every video frame would be pixel-interpolated, a scaling creation of its video processor using the other 25% of the image as a base (the original pixels in each video f<div class="caption left" style="width:375px;"><img alt="JVC demo of 3D passive LCD panel with their 3D camcorder" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image018_cb49bd9e-bdb9-489f-a986-3977d462d018.jpg" width="373" height="262"><br />JVC demo of 3D passive LCD panel with their 3D camcorder</div>rame), but I assume it will also calculate interpolation with motion detection based on previous and next video frames in the moving content. Hopefully it would be a good video processor, Sony claims it is, and within a $25K projector such video processor should better be. It claims that is also capable of CinemaScope vertical stretch for constant height systems, hopefully the stretch can be made also during 3D video processing, a feature previous Sony and JVC 3D projectors did not have. The image quality may be reviewed by reputable lab tests in early 2012 but as I mentioned before some of the initial viewings of HD-to-4K upscaling were not as great as 4K native was. The projector accepts 4K resolution as input. <h2>4K on 3D?</h2> <p><div class="caption right" style="width:416px;"><img alt="JVC demo of professional Real Time 2D - 3D Conversion" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image021_1b23d695-6086-4c5d-ba27-0ee7c3d96d6a.jpg" width="414" height="264"><br />JVC demo of professional Real Time 2D - 3D Conversion</div>Add to the 4K HD scenario the possibility of 3D in 4K, and the possibility of using 4K with 3D passive technology to show true 1080p per eye using low cost polarized glasses (rather than the current 540-lines per eye of Vizio, LG and Toshiba panels), not to mention the possibility of a stunning 4K image quality per eye in 3D in an active-shutter projection system. Additionally, the possibility of sufficient video processing to also vertically stretch CinemaScope movies for the anamorphic lenses to optically expand the image horizontally, and I will write articles seating in my home-theater from that moment on with a “do not disturb” sign at the door, if I make the time to write. <p>In January at CES 2011 Toshiba and Sony also showed <a href="http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php">4K prototype</a> panels for auto-stereoscopic (no-glasses) 3D as well, not that the 3D effect was great but the panel resolution showed the companies direction toward 4K also for HDTV images. <p><div class="caption left" style="width:232px;"><img alt="JVC's 3D consumer camcorder" src="http://www.hdtvmagazine.us/articles/images/4ebef9bd8e34_119B5/clip_image023_a9c4f9dd-a87b-4643-bb7e-b5115d4bf7e9.jpg" width="230" height="338"><br />JVC's 3D consumer camcorder</div>Neither panel was shown at this CCW/ 3D World conference, and Stephen Blumenthal from <a href="http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-who-made-it-work-right-and-now.php">3DFusion</a> said to me before the conference they could not attend either to show their auto-stereoscopic (no-glasses) 3D panel, but I expect to see more no-glasses large screen panels, and 4K, again at CES 2012. <p>3DFusion has now the auto-stereoscopic panel available on the street and as expected is offered at an initial relatively high price tag ($10K) but it clearly shows that glasses-free 3DTV is available now, not in 10 years as many claimed.  <p><a href="http://www.hdtvmagazine.com/news/2011/09/look-out-3d-glasses-here-comes-3dfusions-no-glasses-3dtv.php">3DFusion</a>’s 42" Auto-Stereoscopic-Display with PC, media player, software, and remote control is already available and all are included in the $10K. The same applies to the ZL2 55” panel for no-glasses 3D and 4K from Toshiba (<a href="http://www.hdguru3d.com/index.php?option=com_content&amp;view=article&amp;id=1589:toshibas-giant-3d-tv&amp;catid=35:hdguru3d-news&amp;Itemid=59">recently announced</a> as a product for $11K+). <p>The feature of higher resolution renders more detailed images and allows for closer viewing distances, which increases the lateral angle of view, which excites wider areas of the peripheral vision immersing the viewer further into a movie, much beyond the 30%+ THX or SMPTE standards for 1080p. <p>This immersion factor is especially important for 3D content, which cannot convey the same cinema depth effect on small TV screens if viewed from afar. In other words, 3D is better in large screens, and 4K facilitates more quality in large screens, the union of both is positive for an overall 3D experience. <p>If you are reading this magazine you are not a regular consumer looking for a small CRT for your family room, but many are just coming out of recent technology upgrades such as HDTV panels, Blu-ray, 3D etc. So here is the question, are you ready for 4K? About for 3D 4K? Do you have a screen that would allow you to notice the increase in 4K image quality even when you cannot see now the individual pixels of 1080p from your current viewing distance? Perhaps this is the time for your dream home-theater.</p></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 24, 2011  8:21 AM</b>
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
			<?=getComments(4551)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4551)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/10/3d-world-conference-2011-in-nyc-are-you-ready-for-4k-how-about-3d-in-4k.php" type="text/javascript" charset="utf-8"></script>
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