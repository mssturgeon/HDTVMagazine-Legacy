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
		AND e.entry_id = 3943";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3943 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3943 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3943";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3943";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)" />
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
	<title>HDTV Magazine - Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)</title>
	<meta name="keywords" content="auto stereoscopic, shutter glasses, per eye, blu ray, perceived depth, image, resolution, glasses, depth, stereoscopic, display, viewing, quality, eye, images, dtv, auto, view, design, part, perceived, screen, because, shutter, same" />
	<meta name="description" content="The HDTV industry currently has consumer 3D panels and projectors that require 3D active shutter or passive glasses. 3D depth may be attractive but in one way or another they all sacrifice original resolution, luminance or image quality with new artifacts for the sake of displaying a 3D image.

Even 3D Blu-ray displayed with active shutter glasses looses luminance because only one eye is seeing the corresponding image at the time, in addition to the darkness created by the 3D glasses.

One recent review of a Panasonic plasma 3DTV..." />
	<meta name="title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The HDTV industry currently has consumer 3D panels and projectors that require 3D active shutter or passive glasses. 3D depth may be attractive but in one way or another they all sacrifice original resolution, luminance or image quality with new artifacts for the sake of displaying a 3D image.

Even 3D Blu-ray displayed with active shutter glasses looses luminance because only one eye is seeing the corresponding image at the time, in addition to the darkness created by the 3D glasses.

One recent review of a Panasonic plasma 3DTV..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3943', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">Auto-stereoscopic 3DTV (3D Without Glasses) - Going Backwards in Image Quality for the sake of Depth? (Part 2)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September  7, 2010</b>
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
				<div class="editorial">This article is the second in the "Auto-stereoscopic 3DTV (3D Without Glasses)" series. Other articles in this series are as follows:<br> <ul> <li><a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php" target="_blank">Part 1: Display Taiwan 2010 Hinted: “Sooner than you think”</a> </li><li><a href="/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php" target="_blank">Part 3: What else to lose for Stereoscopic 3D?</a></li><!--li><a href="" target="_blank">Part 4: </a> </li--></ul></div><h2>First, a 3D Reality Check is in Order. </h2> <p>The HDTV industry currently has consumer 3D panels and projectors that require 3D active shutter or passive glasses. 3D depth may be attractive but in one way or another they all sacrifice original resolution, luminance or image quality with new artifacts for the sake of displaying a 3D image.  <p>Even 3D Blu-ray displayed with active shutter glasses looses luminance because only one eye is seeing the corresponding image at the time, in addition to the darkness created by the 3D glasses.  <p>One recent review of a Panasonic plasma 3DTV prized the quality of the set but indicated that in 3D mode the contrast was raised by the TV to 100%, which is usually not recommended because of the risk of burn-in produced by the prolonged display of some static images, such as long sessions of widescreen <span class="caption left"><img title="AUOs 65&quot; passive polarized LCD" hspace="12" alt="AUOs 65&quot; passive polarized LCD" src="http://www.hdtvmagazine.us/articles/images/clip_image002.jpg" width="392" height="417"><br  />AUOs 65&quot; passive polarized LCD</span> movies with black bars at the top and bottom. LCDs are brighter by design but although they do not run the same risks as plasmas they still show 3D images noticeably darker than 2D.  <p>3DTV is an electronic illusion of depth and the brain has to work overtime to make sense of it when the eyes actually focus on a flat surface. Some viewers love it, but others experience headaches and fatigue, even during just a few minutes of viewing.  <p>Regardless how well the brain adapts to the 3DTV illusion, planes of image depth are limited and unnaturally depicted when displayed on a 3DTV (especially in on-the-fly 2D to 3D conversions made by the TV itself), compared to the natural human vision capability of viewing real life objects (details further below). However, the 3D experience is well received by many.  <p>After enjoying HD and Blu-ray at home in large screens for several years, with increasingly improved high quality image, brightness, resolution, contrast ratio, etc., 3D faces a challenge: how to still entice viewers with image depth when almost all of the technology advances reached by 2D HD over 10 years are sacrificed in one way or another, and especially noticeable on an increasing market of large screen sizes of 1080p HD sets that merited their purchase for the appreciation of the high quality images reached by HD, especially Blu-ray.  <p>Fortunately 3D is a feature that a viewer can turn on when needed on their new HDTV and the set can still be used for 2D viewing with all the technological advances in image quality, and the delta difference in price for such 3D features is reasonable.  <p><b></b> <h2>What Technological Advances?</h2> <p>Home video panels took years to reach the Holy Grail 1080p HD level, and they are now available even on some smaller TV sizes. Displaying 3D splits that Holy Grail resolution in half on passive glasses designed to be able to serve each eye with a different image simultaneously. However, as mentioned in Part 1 and covered down below, further resolution splits occur on some multiple-zones auto-stereoscopic 3D designs. The more viewing zones in the TV design the less resolution per eye.  <p>Typical LCD refresh rates of 60Hz, which together with slow pixel speed has been long blamed as the cause of LCD blurriness, took years before speeds improved to 120Hz, 240Hz, and now 480Hz and higher for 2D viewing. Now those speeds have to be shared per eye when displaying 3D. Little press coverage is seen about going backwards in that area of image reproduction. Some new 3D panels (i.e. JVC) show per eye images at 60Hz as they did several years ago in 2D. Is LCD blurriness not important anymore now that the image is in 3D? Is it because the 3D glasses darken so much the viewing of an already dimmed image that motion artifacts cannot be perceived as they were perceived in 2D?  <p>Years of technological advances in luminance capabilities made images brighter and with higher contrast-ratios, from bright whites to absolute black Kuro quality. Those advances have been compromised by either 3D method when serving each eye, either because there are less pixels of image resolution providing image brightness per eye on the 3D passive polarized glasses or auto-stereoscope no-glasses designs, or because the view is blocked by darkening the LCD shutter glasses alternately one eye at the time on the 3D active shutter glasses design; not to mention the drop in luminance caused by the 3D glasses themselves.  <p>Local theaters have seen their standard of brightness for 2D movies measured in foot-lamberts drop to a dim one third when showing a 3D feature. Some have even compromised 2D projection quality when they use the same 3D silver screen that is specially designed to retain circular polarization to allow the use of low-cost passive glasses. 3D projection solutions at home face similar challenges of compromised light output. Many projectors were already low in light output for 2D. Some home theaters have to consider two screens, a regular for 2D and a silver 3D screen polarized for passive glasses, however, some shutter glasses solutions synchronized to a projector feature allow the use of the 2D screen for 3D.  <p>Regarding auto-stereoscopic panels, when selecting a large screen 3DTV with lenticular of parallax barrier designs you have to decide in advance what will be a better choice for you in the long term, a) have a multiple-view zones 3DTV for several people, each zone with lower resolution (in a constant basis), or b) have higher resolution images serving only one person with two viewpoints, one per eye. <span class="caption right"><img title="clip_image005" hspace="12" alt="CHAMTRON's Lenticular Auto-Stereoscopic" src="http://www.hdtvmagazine.us/articles/images/clip_image005.jpg" width="353" height="252"><br />CHAMTRON's Lenticular Auto-Stereoscopic</span> As mentioned in part 1, AUO is working in an intelligent 3DTV set that would provide flexibility in that area, otherwise, after choosing a set of fixed zones there is no coming back if you choose the (a) design and eventually prefer (b) to have more resolution with less views for the TV to be viewed just by yourself. The reverse is also true. With time it is expected that more technological developments will be introduced by companies to improve this situation, as AUO is doing now.  <p><h2 style="clear:both">Auto-stereoscopic, an Image Resolution Challenge </h2> <p>As mentioned briefly in part 1, one issue of no-glasses auto-stereoscopic 3D panels is that in order to allow several viewing angles for multiple viewers the typical 1920x1080 resolution of the screen has to be divided among the number of viewing zones so each can receive the intended 3D effect with his/her image pair.  <p><img style="margin:5px" title="clip_image008" hspace="12" alt="clip_image008" align="left" src="http://www.hdtvmagazine.us/articles/images/clip_image008.jpg" width="353" height="266">Such design could be acceptable for advertising a product in an airport while people walk and view the image casually shifting to other sweet viewing spots, or for a game-replay screen in a large stadium viewed from very far away from various angles, because showing quality resolution is not the primary objective, the large group 3D viewing is. However, such design may not be of acceptable quality for the viewing of a movie at home considering the quality experience Blu-ray brought to the home as 2D, and now as 3D with active-shutter glasses.  <p>The following excerpts from the research: <a href="http://www.dur.ac.uk/n.s.holliman/Presentations/3dv3-0.pdf"><i>3D Display Systems</i></a>, <i>Dr Nick Holliman, Department of Computer Science, University of Durham, Science Laboratories, South Road, Durham, DH1 3LE, February 2, 2005, </i>highlight factors to consider when designing or choosing an auto-stereoscopic display with multiple-view capabilities.  <p>The research shows how a 2D panel capable of 1280h x 1024v of spatial resolution would display multiple view images at reduced resolution:  <p>426h x 341v pixels on each of 9 views, or  <p>640h x 1024v pixels on each of 2 views.  <p>The research also highlights the ability of the human vision to resolve many planes (~240) of perceived depth (stereoscopic voxels) when viewing real life volumetric images, and compares such ability to the relatively limited capability of 3D displays of reproducing voxels of perceived depth (only 31 voxels for twin view for one person; 20 for 9 view designs), all measured within a 1-meter range at the average adult eye separation of 65mm.  <p><i>Page 38: “… a pair of corresponding pixels in the left and right images represent a volume of perceived depth, we will call this a stereoscopic voxel…” “The perceived voxels are arranged in planes from in front <img style="margin:5px" title="clip_image010" hspace="12" alt="clip_image010" align="right" src="http://www.hdtvmagazine.us/articles/images/clip_image010.jpg" width="376" height="280"> to behind the display as they recede from the viewer the cells increase in depth”.</i>  <p><i>Page 40: “….the eye is much better at perceiving depth than the best display is at reproducing it with a minimum detectable depth difference of 0.84mm and an equivalent stereoscopic resolution of 240 planes of depth in the working range +/- 100mm. This difference suggests significant improvements are still possible to the depth reproduction characteristics of stereoscopic displays.”</i>  <p style="clear:both"><img style="margin:5px" title="clip_image012" alt="clip_image012" align="left" src="http://www.hdtvmagazine.us/articles/images/clip_image012.gif" width="444" height="258">  <p style="clear:both">Page 38: <i>“Having stereo 3D does not replace the need for high spatial resolution and anyone used to 1280x1024 monoscopic displays will notice the step down when dividing these pixels between two or nine views. A 3D display can often look better than a monoscopic display with the same resolution as a single view on the 3D display because the brain integrates the information received from the two views into a single image.” </i> <p>Page 42: “<i>Stereoscopic images do not provide the same stimulus to the eyes as the natural world and the implications of this affects 3D display design and use. In particular while the eyes verge to fixate different depths in a stereo image the eye’s accommodation must keep the image plane, rather than the fixation point, in focus. This places measurable limits on how much perceived depth is comfortable to view on a particular 3D display. As well as the stereoscopic depth cue the brain uses many 2D depth cues to help it understand depth information in a scene. Therefore the first aim for a 3D display design needs to be to keep the same basic image quality as a 2D display including values of brightness, contrast, spatial resolution and viewing freedom</i>.”  <p>Another summarized source on this particular subject from the same author is: <a href="http://www.dur.ac.uk/n.s.holliman/Presentations/DTI7NickHollimanATL.pdf">3D Displays, A practical technology</a>  <p>Stay tuned for part 3.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September  7, 2010  8:05 AM</b>
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
			<?=getComments(3943)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3943)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php" type="text/javascript" charset="utf-8"></script>
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