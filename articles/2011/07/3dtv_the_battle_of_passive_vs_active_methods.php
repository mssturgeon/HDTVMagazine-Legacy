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
		AND e.entry_id = 4421";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4421 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4421 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4421";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4421";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3DTV - The Battle of Passive vs. Active Methods" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3DTV - The Battle of Passive vs. Active Methods" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3DTV - The Battle of Passive vs. Active Methods" />
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
	<title>HDTV Magazine - 3DTV - The Battle of Passive vs. Active Methods</title>
	<meta name="keywords" content="image quality, per eye, half resolution, passive glasses, full resolution, lines, image, eye, resolution, dtv, quality, glasses, pixels, viewing, passive, images, panel, dtvs, technology, shown, display, may, show, rather, right" />
	<meta name="description" content="As you may be aware there is a battle of concepts going on in the 3DTV market for home panels. The price of the glasses has been one main difference for consumers looking for low cost solutions for 3D group viewing, but that is only part of the story.

Manufacturers of active-shutter 3DTVs claim their panels display the full resolution of the original images stored on 3D Blu-rays per eye, and they do, but their glasses are relatively expensive and fragile, and the TV/glasses style of operation was reported to create visual discomfort to some viewers.

Manufacturers of passive polarized 3DTVs claim..." />
	<meta name="title" content="3DTV - The Battle of Passive vs. Active Methods" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3DTV - The Battle of Passive vs. Active Methods" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As you may be aware there is a battle of concepts going on in the 3DTV market for home panels. The price of the glasses has been one main difference for consumers looking for low cost solutions for 3D group viewing, but that is only part of the story.

Manufacturers of active-shutter 3DTVs claim their panels display the full resolution of the original images stored on 3D Blu-rays per eye, and they do, but their glasses are relatively expensive and fragile, and the TV/glasses style of operation was reported to create visual discomfort to some viewers.

Manufacturers of passive polarized 3DTVs claim..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4421', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php">3DTV - The Battle of Passive vs. Active Methods</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>July  5, 2011</b>
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
				<p>As you may be aware there is a battle of concepts going on in the 3DTV market for home panels. The price of the glasses has been one main difference for consumers looking for low cost solutions for 3D group viewing, but that is only part of the story.  <p>Manufacturers of active-shutter 3DTVs claim their panels display the full resolution of the original images stored on 3D Blu-rays per eye, and they do, but their glasses are relatively expensive and fragile, and the TV/glasses style of operation was reported to create visual discomfort to some viewers.  <p>Manufacturers of passive polarized 3DTVs claim their glasses are low cost, that the image is similar to what viewers experienced at some local theaters, and that viewers generally not notice the half resolution per eye (540 lines out of 1080 per eye).  <p>The latter has been a recent issue of the 3DTV concept battle, an image resolution quality battle, and frankly, retail stores give little advice to consumers regarding that subject, probably because the sales staff itself does not know the subject as they should, and typically they just want to sell a TV as quickly as possible and move on to the next customer. And that is at the local store; imagine the advice that can be provided on most Internet 3DTV purchases: zero.  <p><img src="http://www.hdtvmagazine.us/articles/images/d94ccd3c0d72_13EB6/clip_image002_853d5c0c-47d0-427e-9688-45b91f975ad4.jpg" width="534" height="252">  <p style="clear: both">Source: LG Display  <p>Although the passive technology shows only 540 lines out of the 1080 lines of resolution per eye the big fuss is that the two half-resolution 3D images are shown to each corresponding eye simultaneously, reason by which 3DTV manufactures claim that their final image is <a href="http://www.ultimateavmag.com/content/closer-look-active-vs-passive-3d-flat-panels">a full 1080</a> image, for the brain to work the mess out.  <p><b></b> <p><b>Quality Matters</b>  <p>Since HDTV was introduced in 1998, HD reached a quality plateau of 1080x1920 image resolution, with optimized colors, contrast, brightness, speed, etc. now some in the industry are short-cutting <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">all those advancements</a> and expect for your brain to fill the gaps when showing 3D, although with no price discount for such product, and the “3D feature” often triggers a sale by itself, go figure.  <p>3D should actually be about two images of the reached plateau of HD quality. There should not be a step backwards on image quality to show depth.  <p>Image quality should always be a priority, and active-shutter glasses offers the better image quality at home today, but if the active-shutter technology causes discomfort to a given viewer, the <a href="http://www.hdtvmagazine.com/articles/2011/03/3dtv-technologies-which-one-for-you.php">passive-glasses method could allow</a> that viewer to enjoy 3D at <a href="http://www.hdtvmagazine.com/articles/2011/03/3dtv-are-competing-technologies-necessary-including-autostereoscopic.php">half-resolution as a compromise</a>, but the compromise has to be told when purchasing the set. At the end it is a consumer’s decision, and hopefully the selection will be supported by a well informed comparison of 3D technologies, rather than just by how much the glasses cost.  <p>Is that happening? Certainly not. The 3DTV market is just going thru the initial WOW! phase, like HDTV did in 1998.  <p>However, this is not all, now there are a couple of new twists on display technology by some passive-glasses 3DTVs, I mention one below.  <p><b></b> <p><b>LG’s claims all the Pixels are shown</b>  <p><img align="left" src="http://www.hdtvmagazine.us/articles/images/d94ccd3c0d72_13EB6/clip_image004_c02499ff-2ba0-4085-ade3-97ca39d616b0.jpg" width="196" height="289">LG claims that their passive-glasses 3DTV actually displays all the 1080 lines per eye as extracted from the 3D Blu-ray disc; no pixels from the source are missed. While it may be true that all the pixels are shown by the panel in some place, although not simultaneously, LG stops short in not publicly describing what pixel information is shown in which TV lines at which time, and more importantly, in revealing the benefits of that method toward image quality, rather than counting lines and pixels for a marketing edge.  <p>Although <a href="http://www.lg.com/us/tv-audio-video/televisions/LG-led-tv-55LW6500.jsp">LG’s new passive 3DTVs</a> are said to actually extract the whole resolution of the two images stored in the 3D Blu-ray disc, the issue is “how” the 3DTV <a href="http://www.ultimateavmag.com/content/passive-3d-resolution-update">displays such full resolution</a> to both eyes simultaneously, considering that there are not enough pixels in the panel for the simultaneous display of the two 1080p images polarized for each eye, and knowing that the panel is physically constrained with a <a href="http://www.lgdisplay.com/">film-patterned-retarder</a> to split the two images with interleaved non-interchangeable 540-fixed-lines that are polarized so line 1 is fixed to display its pixels only to the left eye, line 2 only for the right, line 3 for the left, and so on, so low cost polarized glasses can be used.  The article from another magazine mentioned above under "<a href="http://www.ultimateavmag.com/content/passive-3d-resolution-update">displays such full resolution</a>" published wrong information regarding the explanation that right eye's even lines show on odd TV lines, the right eye's even lines actually show on the even TV lines during the first 120Hz cycle.  I will cover the complete detail of how this technology works on my next articles about this subject. <p><a href="http://www.lg.com/us/tv-audio-video/discoverlgtvs/cinema3d/index.jsp">LG Electronics</a> and LG Display did not provide technical documentation about the subject but I discussed some technical details with company representatives. <img style="display: inline; float: right" align="right" src="http://www.hdtvmagazine.us/articles/images/d94ccd3c0d72_13EB6/clip_image005_24b083d6-e1c8-4f95-9e22-63320304647d.jpg" width="160" height="163">  <p>In one sentence: the TV uses the faster speed of the panel to show 540 lines, and quickly after it shows the remaining 540 lines of the original image for the same eye, and here is the kick: it does it by “overlapping the pixel detail that was just shown, in addition to interleaving lines, writing over the same pixels new information that belongs to other parts of the image”. <p>During the first 120Hz cycle the panel shows the (half-resolution) left image on the TV’s 540-odd-lines while also showing the (half-resolution) right image on the TV’s 540-even-lines. On the next 120Hz cycle the panel displays the lines that were ignored from the disc, but since there are no more TV lines available in the 1080p panel to show the disc’s missed lines, it does it by overlapping the lines just shown on the first cycle. <p>Could the 3DTV claim that its shows the whole 1080-lines of each image stored in the 3D Blu-ray disc? In theory yes, but the overlapping approach and the human persistence of vision could negatively interact to rather subtract from an image quality compared to just showing 540 clean lines per eye as other passive-glasses 3DTVs do. Casual viewing did not reveal that to my eyes but my brain not only helps me put together a 3D image as the passive technology expects me to do, it makes me worry about the pixel overlaps of the whole image, so lab tests using calibration patterns would be needed to make image quality conclusions. <p>Imagine this analogy. How would you feel if the local photo store sells you a print of a large photo of your kids under the understanding that 4 million pixels are in the print and when you get home your neighbor photo geek tells you that it looks as only 2 million are at the front. You look at it again, turned around and find the other two million pixels printed at the back of the photograph. Furthermore, how would you feel if the pixels are rather printed overlapping the first two million? Quite a cocktail indeed, but the photo store was right: all the pixels are there. <p><b>LG’s 3DTV may be what you need </b> <p>However, the <a href="http://www.lg.com/us/tv-audio-video/discoverlgtvs/cinema3d/index.jsp">LG’s 3DTV</a> may be exactly what you need. Let your eyes be the judge. <p>Some advice. When you view the set at the store see if you notice the horizontal black lines of the film-patterned-retarder that separates each line of the two 540-lines images. Ironically, the effect is clearly noticeable on LG’s advertising white screen at the start of the 3D demo. Perhaps on regular content material your viewing would not be affected to be a concern, or you may already view TV from far away for the lines to be noticed, and I mean far away. <p>Also notice the degradation of the 3D effect and the ghosting when viewing closer than 6 feet from the screen, whereby the image for the left eye is visually separated from the image for the right eye, rather than both cleanly merged as 3D. Maybe your viewing positions at home would never be that close and that would not be a factor of concern, think about kids and other viewers gathering around the TV and viewing from the floor close to the screen when the seating area has been fully occupied. <p>Also take note that, although LG Electronics claims that their new 3DTVs can be viewed <a href="http://www.lg.com/us/tv-audio-video/discoverlgtvs/cinema3d/index.jsp">at increased angles</a>, there is a limitation of view angles typical of LCD technology that degrades not only color, contrast, brightness and overall picture quality, but also affects the 3D effect. My informal viewing did not convince me of that wide-angle ability. But hopefully your application may never be affected by this factor if your viewing position is already straight to the image, rather than looking up to the panel that is mounted high above a fireplace mantel in a fixed vertical position, or installed on a corner, which would require excessive viewing angles. <p>My <a href="/articles/2011/07/displaying-3dtv-images-what-is-wrong-with-this-picture.php">next article</a> covers how 3DTVs show 3D images, and the next one after that covers more detail of LG’s technique. Stay tuned.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>July  5, 2011  7:45 AM</b>
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
			<?=getComments(4421)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4421)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php" type="text/javascript" charset="utf-8"></script>
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