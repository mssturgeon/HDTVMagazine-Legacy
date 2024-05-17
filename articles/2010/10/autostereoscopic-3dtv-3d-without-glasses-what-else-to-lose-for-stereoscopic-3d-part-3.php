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
		AND e.entry_id = 4003";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4003 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4003 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4003";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4003";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)" />
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
	<title>HDTV Magazine - Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)</title>
	<meta name="keywords" content="image quality, shutter glasses, active shutter, frame compatible, image pair, image, glasses, quality, images, dtv, resolution, pixels, frame, may, original, format, lines, eye, lcd, part, passive, content, side, active, could" />
	<meta name="description" content="As mentioned in part 2, the glasses-required 3DTVs show an image that has a significant loss of resolution/luminance compared to the original 3D image recorded by the 3D camera-pair, and compared to its 2D version, but many viewers still like the 3D experience, and for many that is all that counts when deciding for a TV with a 3D feature, and if you are one of those it may be better for you to skip this part of the series, as the Spanish culture says: “Ojos que no ven corazon que no siente”.

Although auto-stereoscopic 3DTV removes the glasses from the equation, the technology has its own set of issues..." />
	<meta name="title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As mentioned in part 2, the glasses-required 3DTVs show an image that has a significant loss of resolution/luminance compared to the original 3D image recorded by the 3D camera-pair, and compared to its 2D version, but many viewers still like the 3D experience, and for many that is all that counts when deciding for a TV with a 3D feature, and if you are one of those it may be better for you to skip this part of the series, as the Spanish culture says: “Ojos que no ven corazon que no siente”.

Although auto-stereoscopic 3DTV removes the glasses from the equation, the technology has its own set of issues..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4003', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">Auto-stereoscopic 3DTV (3D Without Glasses) - What else to lose for Stereoscopic 3D? (Part 3)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 11, 2010</b>
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
				<div class="editorial">This article is the third in the "Auto-stereoscopic 3DTV (3D Without Glasses)" series. Other articles in this series are as follows:<br> <ul> <li><a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php" target="_blank">Part 1: Display Taiwan 2010 Hinted: “Sooner than you think”</a> </li><li><a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php" target="_blank">Part 2: Going Backwards in Image Quality for the sake of Depth?</a> </li><!--li><a href="" target="_blank">Part 4: </a> </li--></ul></div><p>As mentioned in <a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">part 2</a>, the glasses-required 3DTVs show an image that has a significant loss of resolution/luminance compared to the original 3D image recorded by the 3D camera-pair, and compared to its 2D version, but many viewers still like the 3D experience, and for many that is all that counts when deciding for a TV with a 3D feature, and if you are one of those it may be better for you to skip this part of the series, as the Spanish culture says: “Ojos que no ven corazon que no siente”. <p>Although auto-stereoscopic 3DTV removes the glasses from the equation, the technology has its own set of issues. People are mislead by the assumption that 3D glasses are the only problem and not having them would help resolve the image issues, when auto-stereoscopic actually adds new ones, such as sweet spot viewing, or a further reduction of resolution and luminance because the TV set is designed to be viewed by several people without 3D glasses (as covered in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">parts 1</a> and <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">2</a>). <p><span class="caption left" style="width:463px"><img alt="Multiple projector system for Auto-stereoscopic 3D (No-glasses 3D) shown at Display Taiwan in June 2010 by the Industrial Technology Research Institute of Hsinchu, Taiwan" src="http://www.hdtvmagazine.us/articles/images/ecf255151f1a_13340/clip_image003_7d6ea7a2-9284-4229-b0dd-560bb2f1c96a.jpg" width="461" height="345"><br />Multiple projector system for Auto-stereoscopic 3D (No-glasses 3D) shown at Display Taiwan in June 2010 by the Industrial Technology Research Institute of Hsinchu, Taiwan</span>Consumers that appreciate image quality may be concerned with reduced resolution and lower brightness when viewing 3D, or feel like going backwards on many 2D technological advances when viewing 3D images as I mentioned in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">part 2</a>. If you are one of those you may continue reading and perhaps find a logical explanation to what you are experiencing in 3D, or ignored about 3D, otherwise turn on your home theater and enjoy your 3D movie, the experience that many care about, not the technology. <h2>Perceived Light and Image Resolution in LCD passive 3DTVs</h2> <p>3DTVs that use passive polarized glasses, like the LCD panel introduced by JVC a few months ago, the Vizio LCD planned for 2011, or the AUO 65” LCD mentioned in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">part 1</a>, have the capability to display 1080 lines of vertical resolution (each with 1920 horizontal pixels per line) when showing 2D images, but when they display dual 3D images they interleave two images of just 540 lines of 1920 pixels each, one directed to each eye (x-pol concept).  <p>Each interleaved image is seen by only one eye because the lines of that image are polarized by the LCD panel and detected by the 3D glasses. One eye only sees the odd 540 lines while the other eye only sees the even 540 lines. The panel displays both images at once. Each image uses half of the resolution of the LCD panel compared to using the whole set of lines when displaying a 2D version of the same content. In summary, there is a pixel count loss of 50% per eye, which means also 50% less of brightness per eye, compared to the original image-pair recorded by the 3D camera. <p>According to some experts, although there is a loss in resolution, since both eyes are viewing both half-images at the same time with the passive polarized 3D glasses the brain is capable of merging the lines well, perceive depth, and still observe “acceptable” image quality. <p>Conversely, 3DTVs designed for active shutter glasses show each 3D image with the whole line structure (i.e. progressive 1080x1920) in alternate fashion at a faster frame rate, whereby each eye sees only the full video frame intended for that eye, while the other eye sees darkness when light is blocked by the active shutter glasses. <p>Each video frame does not loose resolution compared to the original left/right image recorded by the cameras, but because each full image is only seen by one eye at the time there is a reduction of the total perceived brightness compared to what could otherwise be seen if both eyes would see both full images at the same time, like real life, not alternately. Additionally, due to image switching in the shutter glasses approach some people claim to notice objectionable flicker. <p>And the typical question is: which is better? a) The passive method with two simultaneous half-images or b) the active method with two alternated full-images. <p>As you may know, both types of 3D glasses are different in what they do and also in price. If you have a family of 4, would you rather pay a few dollars for 4 passive glasses or about $600 for 4 active-shutter glasses? <p>Various industry opinions about the subjects of total perceived brightness and pixel detail of the passive polarized system versus the active-shutter glasses system (such as Panasonic plasmas, Samsung LCDs and plasmas, and Sony LCDs), are interpreted and defended differently depending on whom you ask, typically getting a “mine is better” response without getting into details. <p>In addition, either method contributes to darkness caused by the 3D glasses themselves, produces a sense of viewer isolation from the surrounding environment and a sense of separation from the other viewers, not to mention the requirements of active-shutter glasses (batteries, fragility, high cost per unit and per group, incompatible with other manufacturer 3DTVs, etc.). For some people those factors could make the 3D proposition not as attractive as traditional HDTV was to the same consumers, not even as an add-on feature for occasional viewing of 3D content. Ironically, other consumers cannot wait to get their hands on one of the new 3DTVs. <p>The above statements describe in very simple terms both 3D alternatives (passive and active) without getting into details of brightness loss by the brand of 3D glasses, of image quality perception affected by the light surrounding the frame of the glasses, of wide viewing angles on LCD that usually affect image quality and would also impact the 3D effect, as opposed to plasma, to mention a few. <p>By now you may realize that the 3DTV technology introduced in 2010 is much more complicated than the HDTV technology introduced in 1998, which was very <a href="/articles/2006/02/is-hdtv-complex-enough.php">complicated</a> compared to analog TV back then.  <p>Front projection systems also have their own set of factors affecting resolution, brightness, and image quality. As mentioned in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">part 2</a>, the passive 3D method requires a different screen that receives and transfers the polarization effect to each eye thru the passive glasses. The 3D screen should not be used for quality 2D viewing. Active-shutter glasses projection solutions could use the same 2D screen to view 3D because the method does not use polarization but rather uses frame-switching. <p><a href="http://www.hdtvmagazine.com/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_1.php">These articles</a> could give you an idea of the complexity of 3DTV from source to display and how that compares to the other major effort the TV industry experienced over the past 10 years: HDTV.  <p>So far one may conclude that regardless of the method the 3DTV itself is not doing a good job in displaying the quality of the original 3D pair of images. Wait until you read further down what cable, satellite, broadcast, and IPTV are doing to get the 3D content to your home using their existing HD infrastructure and what your passive LCD 3DTV could do with that. But first: <p><b></b> <h2>3D Pre-recorded Media - Impact of 3D in A/V System</h2> <p>The 3D Blu-ray standard has been approved and it uses full-video-frames per eye, matching the frame-switching active-shutter system. 3DBD discs and players are being produced following the standard, and because the disc contains the full video frame pair you can expect the image to be of better quality than the cable and satellite distribution methods at half-frames. As it could be expected there may still be differences in the construction and the playing quality of some players. Some internet forums and reputable equipment reviews can provide good input for your selection process. <p>The signal distribution within the A/V system should be 3D capable/compatible, such as the equipment in the middle of the path of the 3D video signal between the 3D set-top-box/player and the 3DTV (namely A/V receivers, switchers, wiring, etc). Some current components may be firmware upgradeable, but others may need to be replaced to meet the higher bandwidth of 3D, 3D protocol handling, 3D format recognition (with HDMI 1.4 or 1.3 upgraded), etc. <p>One option is to make a direct video connection between the 3D set-top-box/player and the 3DTV, and let the existing system components handle just the audio part. <p>But unless the set-top-box/player has a dual HDMI output you will not be able to send to the A/V receiver the latest lossless multi-channel audio tracks (DTS Master Audio and Dolby True HD) for decoding because they do not get transported over the typical coax/Toslink digital audio connections, so you would have to settle for legacy DTS (up to 1.5 Mbps) and Dolby-Digital (up to 640 kbps), which still sound better than typical DVD soundtracks using the same lossy audio formats (at less resolution).  <h2>Distributed 3D image - Where did the Original get lost?</h2> <p>I covered this subject briefly in <a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-2.php">this article</a>, but I include a summary below to facilitate reading. <p>The 3D image-pair as recorded by the 3D camera demands more bandwidth than 2D for transmission. <p>Content distributors such as satellite, cable, broadcast, and IPTV have chosen to use their existing bandwidth capacity used for a 2D HDTV channel to also distribute 3D. In order to do that a sampling/filtering approach was adopted to reduce the pixel count in the 3D image pair. Such reduction is in addition to the MPEG compression typically applied to digitally transmitted content such as HDTV, which is generally known to be over-compressed for quality imaging. After the sampling/filtering, the 3D image-pair is packed within a single video frame for transmission (frame-compatible 3D structure). <p>In other words, the 3D image reaching your home is only a fraction of its original resolution. A consumer has no choice about the quality of the distribution part. Hopefully, more bandwidth may eventually be allocated to a 3D program, but bandwidth is a limited and expensive asset in the content provider industry. <p>I was reading the other day an article (and smiling to the text) from a respected colleague from another magazine; he commented that, to him, the original 3D images in <a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HD</a> (of <a href="http://www.hdtvmagazine.com/glossary.php#1080i">1080i</a>/<a href="http://www.hdtvmagazine.com/glossary.php#1080p">p</a>) ended up looking like <a href="http://www.hdtvmagazine.com/glossary.php#480i">480i</a> SD quality (<a href="http://www.hdtvmagazine.com/glossary.php#SDTV+%28Standard+Definition+TV%29">Standard Definition Digital TV</a>) when displayed by the 3DTV he was reviewing. I understand and share the criticism.  <p>Could the 3D image-pair still be considered HD at such reduced resolution? To my eyes, it could be better, but it is clear that the first implementations of 3D for the home are not about image quality, but rather about “get something on the street now because selling HDTV is already old business”. The industry expects that the brain of a consumer would fill all the pixel detail that was intentionally removed so the current HDTV system and infrastructure could be reused, and hopes consumers would be enticed enough with the 3D experience and not think about how the original 3D image actually was, and how it got to the home. <p>What can one do about the cable/satellite distribution part of 3D? One can start by selecting a good quality 3DTV, choose a content provider that cares for image quality, and hope the 3D image would show better on the 3DTV than a competitor. <p>If the provider gives the opportunity of trial offers without charging for set-top-box returns/installation fees perhaps you may want to experiment with several providers of the 3D channels you are interested in. Some internet forums can provide owner/viewer feedback that can help you make a better choice. <p>In numerical terms, content distributors such as cable/satellite are sampling the original 3D content to send you frame-compatible images with only 50% of the original pixels captured by a 3D camera (50% of the two images of 1080 lines of 1920 pixels). <p>Some content distributors use the top-bottom format (two images of 540 lines of 1920 pixels) losing 50% of the vertical resolution, others use the side-by-side format (two images of 1080 lines of 960 pixels) losing 50% of the horizontal resolution. <p>The viewer does not have a choice of the 3D format chosen by cable/satellite/network (i.e., 3D ESPN) for content distribution. Additionally, satellite and cable may apply a different MPEG compression than a competitor for transmission, which may further affect the perceived image quality, as it is today for HD. <h2>Displaying the Frame-compatible 3D image</h2> <p>When your 3DTV receives and displays that image, depending on the design and the limitations of the 3DTV, the image quality may deteriorate even further. When the 3D-glasses get in the middle, your eyes and <a href="http://www.charlierose.com/view/interview/10727">brain</a> have to work overtime to compensate (<a href="http://www.quantel.com/repository/files/whitepapers_s3dmarch19th.pdf">Quantel paper, page 7</a>) for the loss of original detail and perceive the intended depth even when shown on a flat surface, with the limited pixel information that is left. <p>A new 3DTV faces quite a challenge in displaying a “reasonable” but relatively imperfect image and expect that, because is in 3D, a consumer could be enticed to pay more for that TV. <p>Would the electronic processing and brain miracle actually make the human vision feel the 3DTV images look real? NO, but the general public seem to accept the visual illusion quite well, at least initially as a novelty. As I covered in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">part 2</a>, the human vision is capable of perceiving almost 10 times the voxel detail (planes of depth from front to back) any TV display can produce today, but the 3D effect on a 3DTV is still entertaining to many.  <p><b></b> <h2>Which Frame-compatible 3D Format do you prefer?</h2> <p>Some say that the 3D frame-compatible image sampled/filtered by cable/satellite (and over-the-air broadcast eventually) is of acceptable quality because the brain compensates for its deficiencies. But which one is better, top-bottom or side-by-side? <p>Some prefer the “top-bottom” format because the 1920 pixels of horizontal resolution provide more detail left-to-right, to which the peripheral vision capabilities of the eye are more sensitive to (compared to its limited vertical capabilities). <p>Others prefer the “side-by-side” format for having all the 1080 lines of the video frame, although the horizontal pixels of resolution are halved to just 960. <p>Some support the preference depending if the source signal is 720p or 1080p/i, or on the type of content itself, such as fast action sports or relatively slow image movement in a film movie, as it was the case with HDTV as well. <p>But what happens when either frame-compatible format gets to your 3DTV? What happens when the 3DTV is designed to display 3D images operating in a format that is NOT how the signal was sampled and sent? <p>Regardless if the 3D images were horizontally or vertically sampled for transmission, active shutter glasses LCDs/plasmas would upconvert the two half images of either frame-compatible format to two full frames, and use the time-sequential method to display them in alternate frames at a faster rate as described above. <p>The TV completes the incoming pixels of the half images with other 50% interpolated pixels to make full frames of 1920x1080 per eye. In summary, the 540 lines of the top-bottom format are upconverted to 1080, or the 960 pixels of the side-by-side format are upconverted to 1920. Quite a creation of electronically invented pixels indeed, but the pixels of the frames of either format are completed on the active-shutter glasses system. <p>But what about passive polarized glasses LCD 3DTVs displaying 3D images as 540 interleaved lines from top to bottom? What do those TVs do with either transmitted frame-compatible format?  <p><b></b> <h2>Passive LCD only shows 1/2 of the original resolution? Wait - Would you accept 1/4?</h2> <p>One issue not seen openly discussed is the potential for higher loss on resolution when using a passive polarized glasses LCD 3DTV commonly operating with the top-bottom 540-interleaved-lines mode of operation (x-pol, such as JVC, Vizio, AUO), compared to using the active-shutter-glasses display method. <p>I brought the subject up to Mr. Ko, Ph.D. Senior Associate Vice President &amp; GM Television Business Group of <a href="http://auo.com/auoDEV/pressroom.php?sec=newsReleases&amp;intTempId=1&amp;intNewsId=802&amp;ls=en">AU Optronics Corporation</a> (AUO) of Hsinchu Science Park in Taiwan, R.O.C. while discussing AUO’s 65” 3D interleaved LCD design when I visited Display Taiwan 2010 in June (mentioned in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">part 1</a>). Mr. Ko realized and acknowledged the impact of a side-by-side signal displayed on AUO’s TV. <p>I also discussed the subject with Mr. <a href="http://www.insightmedia.info/aboutus.php?team">Chris Chinnock</a> president and founder of <a href="http://www.insightmedia.info/aboutus.php">Insight Media</a> and a BOD member of the <a href="http://www.3dathome.org/default.aspx">3D@Home consortium</a>. He understood my analysis and concern and added that the final quality depended on the type of sampling/filtering used to create the frame-compatible images.  <p>An incoming 3D image that is vertically compressed with the top-bottom 3D structure should be able to be displayed with virtually no resolution impact by a passive LCD 3DTV because the TV operates similarly (as mentioned further above). The incoming 3D format: two original 1920x1080-line images sampled/filtered down as two 1920x540-line half images, both sandwiched within a 1920x1080-line video frame, will be displayed as two 540-lines interleaved fields, each line with its 1920 pixels. <p><img alt="" src="http://www.hdtvmagazine.us/articles/images/ecf255151f1a_13340/clip_image005_a5d4bdde-8a94-414e-adf7-5b07abe4242e.jpg" width="624" height="770"><br clear="both" /><p>However, what happens if the incoming 3D image has a side-by-side 3D structure such as many programs transmitted by cable, broadcast, and satellite? The incoming 3D format: two original 1920x1080-line images sampled/filtered down to make two 960x1080-line half images, both sandwiched within the 1920x1080-line video frame, would be displayed as: <p>The 960 horizontal pixels of each line will be completed with another 960 pixels interpolated (created) by the TV and displayed as a complete 1920-pixels line. I should mention that such video processing is not an “exact restoration of the original 1920 pixels as recorded by the camera” because those pixels (50% of them) were already lost in the transmission. I often use the example of “enhancing” a large photo with Photoshop and manually add 50% of new pixels the way we think they fit better, then do the same type of “enhancing” work on other 59 photos, and flip them quickly in a second to depict the motion video images do, hoping that the 50% of added pixels would not ruin the final product. But that is not the worst part.  <p>What happens with the 1080-lines of the incoming (left or right) image if the LCD 3DTV can only show 540?  <p>You guessed right: The TV does further sampling, the 1080 lines would have to be downscaled by the TV to just 540 to match the way it operates to display each half-image to the corresponding eye, cutting the original vertical resolution to a half. <p>So what is the total effect when a side-by-side 3D content is displayed on a passive 3D LCD working with a top-bottom x-pol method? 75% of loss due to sampling in both directions.  <p>In other words, 50% of the original horizontal information was already lost when it was scaled down by the cable/satellite provider (from 1920 reduced to 960 pixels to make the side-by-side image-pair), and another 50% of the vertical detail is lost when the TV scales down the vertical axis of the incoming image (from 1080 reduced to 540 lines) to be compatible with the way the TV operates when displaying 3D. <p>A total loss of 75% of the original resolution quality recorded by the dual cameras, courtesy of the content distributor and the passive LCD 3DTV combined. Would you expect NOT to see any artifacts or softness on such 3D image, especially on a large screen at the correct viewing distance? You may have a great brain. <p><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/ecf255151f1a_13340/clip_image007_38595094-4ee1-4dd9-8d73-7096279c9732.jpg" width="624" height="755"><br clear="both" /> <p> <p><b>Final Thoughts</b> <p>No matter which 3D transport method is displayed on which 3DTV type, there will be a loss of resolution or/and brightness compared to 2D, and the awareness of the loss is subjected to the viewer’s perception, his/her personal ability to detect it, the proper screen size/viewing distance relationship, and the relative value the viewer gives to a 3D experience vs. image quality. <p>As mentioned earlier, a loss in image quality also applies to 3D Blu-ray when the fully resolved 1080x1920 image-pair in the disc is displayed on a 3DTV with active-shutter glasses in alternate frames, whereby each eye receives only one full-resolution image at the time while the other eye will see darkness, affecting the total perceived brightness. Not to mention the expected loss in image quality when the 3DBD disc is displayed on a polarized LCD 3DTV, where the vertical resolution of the 3D image- pair is downconverted to half to match the TV’s operation of 540-line interleaved half frames. <p>While 3D-at-home is an interesting feature of new 3DTVs, the 3D image has suffered the loss of many technological advances HDTV has reached over the past decade. Those that appreciate image quality may deem unacceptable the compromises of current 3D as implemented, and rather prefer a 2D version of the same movie without electronic depth. <p><span class="caption left" style="width:438px"><img alt="No-glasses glass-based screens suited with fixed parallax barriers for 2, 4, 6 and 8 viewing zones by Unique Instruments Co., Ltd., Taiwan, shown at the recent Display Taiwan 2010 show" align="left" src="http://www.hdtvmagazine.us/articles/images/ecf255151f1a_13340/clip_image009_5c9250e9-2478-41d5-ac03-70d0d70436b1.jpg" width="436" height="327"><br />No-glasses glass-based screens suited with fixed parallax barriers for 2, 4, 6 and 8 viewing zones by Unique Instruments Co., Ltd., Taiwan, shown at the recent Display Taiwan 2010 show</span>The good news is that 3D is just “one more feature” into a good quality 2D HDTV mainly used for HD viewing. Eventually panels and projectors may have higher resolution and more light output to compensate for some of today’s limitations of 3DTVs, but since that higher performance could also be used in the same HDTV to display 2D, the 2D image could be so stunning in super-resolution, contrast and brightness compared to today’s sets that we may be back to square-one again when comparing it to 3D using the same advanced TV. <p>How auto-stereoscopic 3D fits into this picture? Not wearing 3D glasses theoretically improves the total perceived light output of the image and for some that is the major road block today, but auto-stereoscopic 3D has its own number of image issues with either lenticular and parallax barrier designs, and is not yet comparable to the image quality of 2D Blu-ray on large screen home viewing. However, as I said in <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">part 1</a> the industry is showing promising advances in that area. <p>But let us be clear, while I am not against 3D as an extra feature and the 3D-for-the-home of today is not as perfect as it could be, it is still a big step forward from the old 3D-anaglyph-color-glasses approach of the past. The industry is moving fast every day to make it even better; many consumers like to have 3D at home, and most are not image quality nerds<b>. </b> <p>Some say that there is no assurance that 3D will become a mass home format in which volume would bring prices down and 3D content would increase availability. But the question actually should be: Does 3D really have to be a mass consumer format in order to survive as an extra feature in new TVs to occasionally enjoy compelling 3D content at home if the delta price difference is reasonable? <p>Stay tuned for part 4.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 11, 2010  7:58 AM</b>
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
			<?=getComments(4003)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4003)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php" type="text/javascript" charset="utf-8"></script>
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