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
		AND e.entry_id = 620";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 620 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 620 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 620";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/06/cinemascope-hdht-part-3-screens-and-aspect-ratios.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 620";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios" />
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
	<title>HDTV Magazine - CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</title>
	<meta name="keywords" content="cinemascope system, aspect ratio, anamorphic lens, cinemascope hdht, aspect ratios, cinemascope, screen, image, system, wider, viewing, aspect, resolution, using, ratio, same, theater, bars, projector, content, movies, home, scaler, might, chip" />
	<meta name="description" content="If your HDTV has a fixed frame, such as an LCD/plasma panel or a rear-projection TV, and you want to see the CinemaScope&amp;trade; image at its intended aspect ratio, there is not much that can be done about the bars at the top and bottom of the screen. As a result, the bars use part of the valuable vertical resolution of the 16:9 TV or the projector's chip. To make things worse some technologies, such as LCD projection, show the black bars as dark gray, distracting the viewing of the actual image.

The HD chip of a front projector is fixed to the resolution of its design (720px1280 or 1080px1920 pixels). Many projectors today are chip-based DLP, LCD, and LCoS technologies at 720p resolution. More recently, several affordable 1080p projectors were introduced to the market." />
	<meta name="title" content="CinemaScope&amp;trade; HDHT - Part 3 - Screens and Aspect Ratios" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="CinemaScope&amp;trade; HDHT - Part 3 - Screens and Aspect Ratios" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/06/cinemascope-hdht-part-3-screens-and-aspect-ratios.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="If your HDTV has a fixed frame, such as an LCD/plasma panel or a rear-projection TV, and you want to see the CinemaScope&amp;trade; image at its intended aspect ratio, there is not much that can be done about the bars at the top and bottom of the screen. As a result, the bars use part of the valuable vertical resolution of the 16:9 TV or the projector's chip. To make things worse some technologies, such as LCD projection, show the black bars as dark gray, distracting the viewing of the actual image.

The HD chip of a front projector is fixed to the resolution of its design (720px1280 or 1080px1920 pixels). Many projectors today are chip-based DLP, LCD, and LCoS technologies at 720p resolution. More recently, several affordable 1080p projectors were introduced to the market." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=620', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/06/cinemascope-hdht-part-3-screens-and-aspect-ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>June 27, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<div class="editorial">The following article is the latest in the CinemaScope&trade; series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php">CinemaScope&#8482; HDHT - Part 1 - The Concept</a></li>
<li><a href="/articles/2007/03/cinemascope_hdht_-_part_2.php">CinemaScope&#8482; HDHT - Part 2</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
I use the word CinemaScope&trade; to convey the idea of any content with an aspect ratio that is wider than 1.78:1 (16:9 HDTV), which would be displayed with black top/bottom bars on any HDTV.

<p>If your HDTV has a fixed frame, such as an LCD/plasma panel or a rear-projection TV, and you want to see the CinemaScope&trade; image at its intended aspect ratio, there is not much that can be done about the bars at the top and bottom of the screen. As a result, the bars use part of the valuable vertical resolution of the 16:9 TV or the projector's chip. To make things worse some technologies, such as LCD projection, show the black bars as dark gray, distracting the viewing of the actual image.</p>

<p>The HD chip of a front projector is fixed to the resolution of its design (720px1280 or 1080px1920 pixels). Many projectors today are chip-based DLP, LCD, and LCoS technologies at 720p resolution. More recently, several affordable 1080p projectors were introduced to the market.</p>

<p>Regardless of the projector's resolution, there will be a loss of about 30% of the vertical resolution (measured top to bottom) when displaying the blacks bars of a wider CinemaScope&trade; letterboxed movie without using a CinemaScope&trade; system.</p>

<p>However, something can be done with front projectors to use the full resolution of the chip when displaying CinemaScope&trade; images in wider screens; A CinemaScope&trade; system with anamorphic lens, capable scaler, and a 2.35:1 screen can be implemented.</p>

<p>For some Home Theater enthusiasts, retaining that resolution and viewing 2.35:1 movies in 2.35:1 screens is sufficient to pursue a full CinemaScope&trade; system.</p>

<p><img src="/images/articles/aspect-comparison.jpg" alt="Aspect Ratio Comparison" /></p>

<p><b>CinemaScope&trade; Using a 16:9 Screen</b></p>

<p>As mentioned before, a 2.35:1 screen would be ideal for showing a 2.35:1 CinemaScope&trade; movie, and, when displaying 16:9 images, the CinemaScope&trade; system would maintain the same "Constant Height" using the same 2.35:1 screen.</p>

<p>However, what happens if one has an existing 16:9 screen and wants to use it also to display wider content of 2.35:1 aspect ratio? Although these articles are not intended to cover this particular scenario in depth, allow me to offer some brief comments about this subject.</p>

<p>While it is possible to view wider CinemaScope&trade; content on a 16:9 projection screen (using or not a CinemaScope&trade; scaler and anamorphic lens) we know that the image width will be limited by the width of the 16:9 projection screen, which would make the 2.35:1 CinemaScope&trade; image shorter with top/bottom black bars, and the objects on it appear relatively smaller than 16:9 material displayed on the same 16:9 screen.</p>

<p>A CinemaScope&trade; system, regardless of the aspect ratio of the screen, requires a scaler adding approximately 30% interpolated horizontal lines stretching the image vertically to use the full 720 or 1080 vertical resolution of the projector's chip (making objects look thinner and taller), then the anamorphic lens would stretch the image horizontally to restore its original geometry.</p>

<p>Some home theater enthusiasts migrating to a 2.35:1 screen installation (and those not migrating as well) might find it tempting to start using a CinemaScope&trade; system with an existing 16:9 screen under the theory that maximizing the projector's vertical resolution with the scaler would provide a better image than the original at the same size.</p>

<p>The assumption of "more lines/pixels should render a better image" might sound tempting on the surface, but those are not pixels of original resolution, those are "scaler magic". What happens when your magician is not perfect?</p>

<p>Depending on the CinemaScope&trade; system's electronic and optical quality, the manipulation on the four axes of the image could negatively affect the quality of the original image that had fewer pixels but were pixel-perfect-fit from source to display.</p>

<p>In other words, it might be better to display the image as it is, and accept that a 16:9 screen will not accommodate for a wider image, which is actually the main objective of a CinemaScope&trade; system; displaying a wide-image wider without making it shorter.</p>

<p>Additionally, the approach of using a 16:9 screen for CinemaScope&trade; images is not considered "Constant Height" until a 2.35:1 screen is used to complete the CinemaScope&trade; system, and that is where the net gain in benefits is to be found.</p>

<p>Although it is a matter of personal choice to start with these components of the CinemaScope&trade; system using an existing 16:9 screen and get the 2.35:1 screen later, one should be concerned with ruining a pixel perfect image just by assuming that 30% more interpolated lines to use the projector chip's full resolution would be an improvement on the same size of image.</p>

<p>If a CinemaScope&trade; system would not be used for a 16:9 screen, why would Home Theater people use a 16:9 screen for images of wider aspect ratios? Many Home Theater rooms have limited left/right space and have installed 16:9 screens to maximize the overall image size of more frequent viewing of 16:9 and 4:3 content. Those people do not mind occasionally viewing a 2.35:1 image relatively smaller than 16:9 content, more on this later.</p>

<p><br />
<b>The Wider Formats</b></p>

<p>Many modern movies are filmed/transferred as 2.35, 1.85, 1.78, 2.40, 2.39, etc. According to some publications that show aspect ratio information, such as Widescreen Review Magazine, in a recent 5 month period 50-60% of the reviewed movies were at or wider than 2.35:1, but mostly 2.35:1.</p>

<p>Most of the newer Hi-Def DVDs are being released at 2.35:1 and wider. Blu-ray in December 2006 introduced five 2.35:1 movies vs. three of smaller aspect ratios, in January introduced six vs. three, and in February introduced six vs. two. The HD DVD format on the same period was about half and half.</p>

<p>It would be interesting to also analyze the timing and implementation tendency of wider formats for filmmaking, to determine if there is a gradual shift toward the use of wider formats in a 16:9 HDTV era.</p>

<p>Regarding regular DVD releases, during 2 weeks in December 2006, 15 titles were 2.35:1 and wider, three were listed as TBD, and nine titles were not wider than 2.35:1.</p>

<p>Historically, movie theater film shifted to wider aspect ratios when NTSC TV was introduced over 50 years ago with a similar 4:3 aspect ratio, to offer a theatrical format that could not be viewed on a TV at home, and keep the public interested in attending the local movie theater.</p>

<p>Now that TV is 16:9, Hollywood might be inclined to use wider than HD formats more frequently to attract the public to the local movie theater with widescreen movies viewed without black bars. The movie-making industry must be maintained alive and the art supported, regardless of what TV system is in place. Everyone needs this art in one way or another.</p>

<p>Some directors select the 16:9 aspect ratio so their movies can be viewed on an HDTV at home exactly the same way as they are viewed in the theater, with no black-bars.</p>

<p>This series of articles is not intended to cover the aspect ratio differences and their implementation in filmmaking. For that subject, I recommend reading some articles published over the years by the Widescreen Review Magazine.</p>

<p>If CinemaScope&trade; movies are increasingly released, it will be beneficial to have a home theater system capable of maximizing the resolution of 16:9 projectors for CinemaScope&trade; viewing by using anamorphic lens and enabled scalers.</p>

<p><br />
<b>The Viewing Position Using a CinemaScope&trade; System with 2.35:1 Screens</b></p>

<p>When using a 2.35:1 screen with a CinemaScope&trade; system implementing "Constant Height", one should determine sound and viewing positions based on 16:9 viewing. The scaler would make 2.35:1 viewing maintain the same image/screen height of a 16:9 image. The anamorphic lens expands the image laterally to cover the entire width of the 2.35:1 screen, increasing the angle of view and the cinematic impact.</p>

<p>There is no need to change viewing positions or audio sweet spots when changing aspect ratios, and no need to adjust the zoom, focus, etc. The system should be seamless. Some systems even operate by having the scaler auto-detect the aspect ratio of the content, switch the aspect ratio automatically, and issue control commands to automatically move the anamorphic lens/sled in/out of the path of the projector, move screen masks to adapt to the image, roll curtains, etc.</p>

<p>As a viewer, you just select the content and let the system work for you.</p>

<p><br />
<b>The Viewing Position Using a 16:9 Screen Also for CinemaScope&trade; Viewing</b></p>

<p>On the other hand, when using a 16:9 screen and no system, there is always the alternative of doing nothing, waste some of the projector's resolution and light output to display 30% of black bars, and learn to live with a shorter 2.35:1 image on a 16:9 screen. Such approach might prompt you to move the viewing position closer to the screen to adjust to the smaller 2.35:1 image to receive a cinematic impact.</p>

<p>While the lateral angle of vision did not increase, having a shorter image makes objects within it proportionally smaller than the same objects within a taller 16:9 image that uses the full area of the screen, both having the same width. For that reason, when switching back to view larger 16:9 images you might want to move the seating position further from the screen.</p>

<p>Switching viewing positions to adjust to the aspect ratio of the image could not only be inconvenient, but could also potentially affect the sweet spot of multi-channel audio when it was calibrated for one viewing position, while is unacceptable for the other.</p>

<p>It is impractical to change the speaker's location to adjust the multi-channel audio sweet spot as you switch viewing-positions, and it is certainly an impossible option if the speakers are mounted in the walls/ceilings.</p>

<p>Stay tuned for the next article in this CinemaScope&trade; HDHT series.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>June 27, 2007  3:23 AM</b>
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
			<?=getComments(620)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 620)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/06/cinemascope-hdht-part-3-screens-and-aspect-ratios.php" type="text/javascript" charset="utf-8"></script>
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