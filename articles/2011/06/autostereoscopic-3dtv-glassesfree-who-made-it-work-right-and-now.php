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
		AND e.entry_id = 4371";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4371 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4371 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4371";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-who-made-it-work-right-and-now.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4371";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now" />
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
	<title>HDTV Magazine - Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now</title>
	<meta name="keywords" content="auto stereoscopic, glasses free, viewing zones, stereoscopic dtv, sony toshiba, philips, stereoscopic, dfusion, technology, auto, dtv, company, viewing, video, demoed, panel, stephen, free, years, made, image, product, dfmax, business, toshiba" />
	<meta name="description" content="As introduced on my previous installments on the subject, 3DFusion demoed a 42” auto-stereoscopic 3DTV panel (AS-3DTV) at CES 2011 that to my eyes was the best of its kind at the show and the best of what I have seen so far in glasses-free 3D panels, including Sony’s and Toshiba’s prototypes of the same technology demoed at CES 2011, and including other screens shown at the 2010 Display Taiwan Show I attended in Taipei a few months ago.

This article describes how this company managed to create this product, and the product is not only a 3DTV. The next article covers how the product actually works.

The primary reason 3DFusion’s panel stands out of the crowd is due to..." />
	<meta name="title" content="Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-who-made-it-work-right-and-now.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As introduced on my previous installments on the subject, 3DFusion demoed a 42” auto-stereoscopic 3DTV panel (AS-3DTV) at CES 2011 that to my eyes was the best of its kind at the show and the best of what I have seen so far in glasses-free 3D panels, including Sony’s and Toshiba’s prototypes of the same technology demoed at CES 2011, and including other screens shown at the 2010 Display Taiwan Show I attended in Taipei a few months ago.

This article describes how this company managed to create this product, and the product is not only a 3DTV. The next article covers how the product actually works.

The primary reason 3DFusion’s panel stands out of the crowd is due to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4371', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-who-made-it-work-right-and-now.php">Auto-Stereoscopic 3DTV (Glasses-Free) - Who Made it Work Right - and Now</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>June  6, 2011</b>
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
				<p> <span class="caption left" style="width:314px"><img alt="3DFusion - 3D glasses-free LCD 42” panel" src="http://www.hdtvmagazine.us/articles/images/cedf5172f6e4_E836/clip_image002_ad9a5ee9-6808-4705-a48f-742c95c7a094.jpg" width="312" height="215"><br />3DFusion - 3D glasses-free LCD 42” panel</span>As introduced on my previous <a href="http://www.hdtvmagazine.com/articles/2011/03/glassesfree-autostereoscopic-3dtv-when.php">installments</a> on the subject, <a href="http://www.3dfusion.com/">3DFusion</a> <a href="http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php">demoed a 42”</a> auto-stereoscopic 3DTV panel (AS-3DTV) at CES 2011 that to my eyes was the best of its kind at the show and the best of what I have seen so far in glasses-free 3D panels, including Sony’s and Toshiba’s prototypes of the same technology demoed at CES 2011, and including other screens shown at the 2010 Display Taiwan Show I attended in Taipei a few months ago. </p> <p>This article describes how this company managed to create this product, and the product is not only a 3DTV. <a href="/articles/2011/06/autostereoscopic-3dtv-glassesfree-one-companys-picture-perfect-solution-how-does-it-work.php">The next article covers</a> how the product actually works. <p><b></b> <p><b></b> <p><b>The Perceived Difference</b> <p>The primary reason 3DFusion’s panel stands out of the crowd is due to the company’s ability to implement a <a href="http://www.3dfusion.com/3d-content-player.html">smart software engine</a> that softens and often makes imperceptible the image breaks between viewing zones, typically noticed as a weakness of other auto-stereoscopic displays when the viewer changes position or moves the head. <p><img style="background-image: none; border-bottom: 0px; border-left: 0px; padding-left: 0px; padding-right: 0px; display: inline; float: right; border-top: 0px; border-right: 0px; padding-top: 0px" title="clip_image005" border="0" hspace="12" alt="clip_image005" align="right" src="http://www.hdtvmagazine.us/articles/images/cedf5172f6e4_E836/clip_image005_c5915baf-baeb-4082-b8df-1514a5d09ac7.jpg" width="298" height="186">You may have witnessed the demos from Sony, Toshiba and the others in the recent months, shown with 2 or 3 pairs of feet marked on the floor for the viewers to stand on exactly those positions, and better not move or the 3D is gone. <p>With 3DFusion I was free to walk in front of the panel side-to-side, as with any other regular TV panel, without drastically loosing the 3D effect.  <p>As mentioned, this is a common issue seen on many auto-stereoscopic prototypes, the 3D viewing effect is easily disrupted and the 2D viewing between 3D viewing zones is not clear either. This is one of the main reasons the auto-stereoscopic technology is usually criticized by many that have only seen early generation or poorly made prototypes. <p>So the press uses their expert crystal ball that knows it all, and estimates for the technology to take 10 years to reach the market, perhaps betting on the Mayas to be correct so no one would be around anymore to say they were wrong about AS-3DTV. But <a href="http://www.hdtvmagazine.com/columns/2011/05/hdtv-almanac-sid-2011-it-begins.php">recently several sources said</a> 7, and 5, and 3 years. About <a href="http://www.hdtvmagazine.com/articles/2011/03/glassesfree-autostereoscopic-3dtv-when.php">now</a>?  <p><a href="http://www.3dfusion.com/3d-display.html">The 42” LCD</a> demoed by 3DFusion had a <a href="http://www.3dfusion.com/glasses-free-3d-optics.html">lenticular screen</a> under a glass layer that protected its delicate surface. A proprietary process in manufacturing attaches the lens for 3D viewing zones to the lenticular screen. Their panel showed at CES 2011 and easily surpassed the quality demoed by Sony and Toshiba prototypes on the same technology. <p>Sony’s AS-3DTV panels were demoed as a company statement of future technology and direction. No specs other than total panel resolution were disclosed, no indication of possible number of viewers other than assuming the number from the feet marked at the floor, no estimated resolution per view-zone/eye was disclosed either, no time-line, no estimated price, in other words it could have been a prototype of a UFO that may never fly in the real world.  <p>Toshiba demoed their AS-3DTVs as a technology statement as well, the company said the sets would be improved when commercially available soon, by the end of the year they said at CES 2011, although others thought that most likely would be in 2012 (if one can believe the PR of these companies), but as with Sony, details and specs were not provided other than panel size and resolution.  <p>However, the quality of their image and viewing experience made me conclude that although some major companies have made positive progress on this technology year to year, I would not want to have any of the sets demoed by Toshiba or Sony using floor space at my home even if they were free and available tomorrow. <p><b></b> <p><b>How the 3DFusion product started?</b> <p>The concept was originally developed by <a href="http://www.usa.philips.com/">Royal Philips</a>. Philips worked on it for about 9 years and invested approximately half billion dollars, however, the Philips’ auto-stereoscopic project suffered from ghosting, double image and sweet spot issues.  <p>In 2009, around the beginning of the economic meltdown, about 17 incubators were closed and Philips decided to shut down their TV manufacturing plants for 3DTVs for the consumer market. At the end, the effort resulted in an incomplete package design suffering from the same eye strain issues as other auto-stereoscopic 3DTVs. <p>According to 3DFusion, the Philips technology was first made available to <a href="http://www.dimenco.eu/display-technology/">Dimenco</a>, a company made of a group of former Philips 3DSolutions employees who after Philips shut down the incubators were transferred from their other Philips assignments and formed a 3D company to promote the Philips 3D technology under a Philips consulting agreement.<p>Philips supported them and they were hired to manage inquires regarding Philips 3D. Led by <a href="http://en.wikipedia.org/wiki/Martin_Tobias">Martin Tobias</a>, they have forged a solid 3D business obtaining a license from Philips for a range of 3D IP.  <p>3DFusion added <i>“As 3DFusion has licensed the most extensive IP package from Philips and filed our own IP, as the "icing on the cake", we consider them as a sister company.&nbsp; They are doing a good job developing hardware and we are in negotiations with them for a strategic partnership to develop new products.” </i> <p>Another factor for the decision of shutting down the project was that Philips did not have an appropriate distribution channel for those products. 3DFusion took over the effort and built upon it, inventing the 3DFMax image optimization technology. Because the original Philips product had a lot of image shifting the 3DFMax required considerable software work to smooth those transitions. <p>Unlike other TV companies that are involved in the TV business for decades for similar endeavors, 3DFusion developed their 3D solution after working on the 3DTV glasses-free design for only 3 years, one should not be surprised, 3DFusion’s president Stephen Blumenthal, the father of 3DFMax, has been a stereoscopic 3D video microscopy consultant for 30 years. <p>After filing their own exclusive 3D auto-stereoscopic intellectual property (IP), building over the Philips design which was turned over to 3DFusion in 2007, the company entered into a 9-month licensing negotiation, and in May 2010 3DFusion obtained world-wide rights to all Philips 3DTV IP. <p>This technology package, coupled with 3DFusion’s know how, trade secrets, and patent pending IP, provided the 3DFMax foundation for their exclusive auto-stereoscopic “picture perfect” (as Stephen calls it), adjustable depth 3DTV solution. <p><b></b> <p><b>The Whole R&amp;D department: Two guys from NY</b> <p><b></b> <p>While many other TV manufacturers have a large number of staff with decades of TV background in <span class="caption left" style="width:404px"><img alt="Ilya Sorokin (right), CEO, Stephen K. Blumenthal (center), President, Mark Hooper (left), Chief Scientist, and Alex Braurman  (not in the picture), VP of Business Development" src="http://www.hdtvmagazine.us/articles/images/cedf5172f6e4_E836/clip_image007_a5c29865-6c5e-4082-93c5-f0791393237e.jpg" width="402" height="199"><br />Ilya Sorokin (right), CEO, Stephen K. Blumenthal (center), President, Mark Hooper (left), Chief Scientist, and Alex Braurman  (not in the picture), VP of Business Development</span>their R&amp;D departments, 3DFusion counts with just Ilya Sorokin and Stephen Blumenthal, the only ones responsible of the post Philips developments. Stephen comes from a TV electronics video tech industry for over 30 years, and Ilya, the CEO, brought strong business finance background to the company.  <p>As mentioned, Stephen has 3D auto-stereoscopic video background, pioneering with Leica Microsystems of Buffalo, NY two major breakthroughs in the advancement of 3D stereoscopic video microscopy. <p>One was a “picture perfect” 3D video stereo microscope. The other was a 3D optical mechanical shutter device which converted a mono path, 1200x compound optical microscope into a 3D stereoscopic, dual path, 1,200x video microscope, advancing stereoscopic imaging from 250x to 1,200x for viewing live optical specimens, a development credited by Leica as being of historical significance. <p>In the words of Stephen, “<i>My nuts and bolts background provided me with the right skill sets to be able to address and solve the Philips 3D auto-stereoscopic problems. Coupled with Ilya’s unique knowledge base, the two of us did it without a major R&amp;D team. It took two guys from NY to show how to fix Philips’ auto-stereoscopic 3DTV technology”.</i> <p><i></i> <p>“<i>Ilya and I started the company, and as the founders we are delighted at the fruits of our labor, and believe that this technology’s platform will stimulate a technical transformation in a large number of industries. There is the potential for the results of these advancements to impact every major video imaging application</i>”. <p><i>“3DFusion is looking to create master licensing agreements with strategic partners in some of the market verticals for which our 3DFMax 3DTV system is ready for distribution”.</i> <p>How the technology works? Stay tuned for the next article. 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>June  6, 2011  7:34 AM</b>
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
			<?=getComments(4371)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4371)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-who-made-it-work-right-and-now.php" type="text/javascript" charset="utf-8"></script>
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