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
		AND e.entry_id = 557";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 557 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 557 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 557";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 557";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CinemaScope&#8482; HDHT - Part 2" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CinemaScope&#8482; HDHT - Part 2" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="CinemaScope&#8482; HDHT - Part 2" />
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
	<title>HDTV Magazine - CinemaScope&#8482; HDHT - Part 2</title>
	<meta name="keywords" content="aspect ratio, anamorphic lens, home theater, wide screen, top bottom, screen, cinemascope, projector, image, lens, system, resolution, aspect, content, could, masking, project, anamorphic, ratio, installation, home, movie, theater, bars, part" />
	<meta name="description" content="As I mentioned on the first article, I decided to launch this CinemaScope&amp;trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&amp;trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.

Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell..." />
	<meta name="title" content="CinemaScope&amp;#8482; HDHT - Part 2" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="CinemaScope&amp;#8482; HDHT - Part 2" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As I mentioned on the first article, I decided to launch this CinemaScope&amp;trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&amp;trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.

Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=557', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php">CinemaScope&#8482; HDHT - Part 2</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>March  6, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
<li><a href="/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
First of all, forgive me for not been able to produce these articles as often as I had planned, I am very occupied with the annual report about HDTV Technology that I produce every year (for 5 years already) at this time and that has priority, but I will try to keep the momentum of these articles as warm as possible.

<p><br />
<B>The CinemaScope&trade; Project</B></p>

<p>As I mentioned on the first article, I decided to launch this CinemaScope&trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.</p>

<p>Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell that the CinemaScope&trade; experience is breathtaking. Building this system was a challenge, but it was worth every penny and every minute invested on it.</p>

<p>The project is not about beautifying a home theater, it is about what is possible with quality electronics and optics to optimize the viewing of wide-screen content and to recreate the CinemaScope&trade; feeling of the classical theater.</p>

<p>It was also about simulating the steps a regular consumer would have to follow to make a similar project for their homes. Regardless if I was capable to install, connect, calibrate, etc., I decided to hire out the necessary labor for each task, rather than shaving costs by doing the job myself. The value of the project to readers was for me to do exactly what a regular consumer would have to do. Due to the fact that I intended to live with the system after it was finished, I designed the system and selected all the components myself. A consumer without such knowledge should expect the dealer/installer of the system to take that role.</p>

<p><br />
<img src="/images/articles/hdht-projector.jpg" alt="Optoma Projector" align="left" /><B>The Projector Took the Driver Seat</B></p>

<p>As you might know already, front-projector manufacturers are doing partnerships with manufacturers of other CinemaScope&trade; components, such as anamorphic lens, transports, plates, etc. They have improved their compatibility, and they are easier to install because they are made with specifications to fit with each other. They are sold by projector manufacturers as CinemaScope&trade; system packages, which make the whole project considerably smoother and cheaper to the consumer, particularly in labor costs. Not to mention the risks one would run by choosing components based on their individual merits that might end up not fitting as smoothly with each other.</p>

<p>This year we will see a growing number of manufacturers offering CinemaScope&trade; lens/transport package solutions bundled with projectors, and offering the lens/plate/transport in a separate package deal for those consumers that have a projector already.</p>

<p><br />
<B>Other Aspect Ratios Displayed on a 2.35:1 Screen</B></p>

<p>As I mentioned in the first article, the CinemaScope&trade; approach I am covering on these articles is not about simply zooming and/or masking 2.35:1 images on a screen. Is about implementing a 2.35:1 screen for predominantly 2.35:1 viewing while maximizing the capabilities of the projector's chip-resolution and light-output. That requires more than just a 2.35:1 screen, it requires as a vertical stretch capable scaler, anamorphic lens, lens transport, transport plate, etc.</p>

<p>Images on aspect ratios that are less wide (16:9, 4:3, for example) would have to be displayed without the anamorphic lens in front of the projector lens, and with side pillars on the wider 2.35:1 screen, but aligned with the top/bottom edges of the screen (reason by which it is called "constant height"). That is, if the original aspect ratio of the incoming image needs not to be altered. Additionally, pillar side-bar masking might be necessary for some viewers that can not tolerate "projected" black pillar bars, which are not as black as good masking material.</p>

<p>The approach could mean that the overall area occupied by a smaller 16:9 image within the 2.35:1 screen could end up unacceptably small to some viewers, compared to a 16:9 screen installation for predominantly 16:9 TV viewing occupying the same width of the 2.35:1 screen approach. Not to mention the further reduced visual impact of 4:3 images that would look even smaller when implemented in constant height 2.35:1 screen.</p>

<p><br />
<B>Limited Wall Width</B></p>

<p>With that in mind, whether the screen is mounted on a wall or coming from the ceiling, if the front viewing area is limited in width, a 2.35:1 screen could be seen as a step backwards regarding overall image size impact for viewers that seldom watch 2.35:1 content. Therefore, one has to decide very carefully what is the primary purpose of the home theater. If the purpose is to mainly watch TV in 4:3 or 16:9 formats and occasionally view a 2.35:1 movie, then the CinemaScope&trade; concept with 2.35:1 screens might not be as appealing as it would be to a 2.35:1 wide-screen movie fan that would not watch TV or smaller aspect ratios as often, or at all, on the home theater.</p>

<p><br />
<img src="/images/articles/hdht-screen.jpg" alt="HDHT Screen" align="right" /><B>Butchering Aspect Ratio</B></p>

<p>Many movie directors do not take lightly when anyone geometrically alters the original aspect ratio (OAR) of their piece of art to fit the image into a display device with different aspect ratio. Aspect ratio is part of the artistic creation and should be respected as is. Some HD movie channels alter (butcher) the original aspect ratio of wide-screen movies to make them fit within the 16:9 frame so they are displayed without letterbox bars. The content distributor thinks that people do not like to see bars, and actually many consumers purchased 16:9 screens under the impression that they would finally get rid of the hatred black bars on their 4:3 sets when viewing 16:9 content. The content distributors were not to far off in that thinking process "for the general mass of TV viewers", but they ignored the OAR loving public.</p>

<p>2.35:1 wide-screen movie content displayed on a 16:9 screen would display with a similar letterbox bar effect of the 16:9 content on the old 4:3 TV set. The approach wastes about 30% of the vertical resolution of the display device used for projecting just black dots, and the projected dots are generally not absolute black when they hit the screen. So how could we use that wasted vertical resolution capability for an image that rather needs it horizontally because is proportionally wider?</p>

<p><br />
<B>Putting All the Resolution Pixels to Good Use</B></p>

<p>A CinemaScope&trade; system could put those vertical pixels to work electronically with a capable scaler, it would stretch the image vertically, everyone would look thin, it would be ideal that someone later in the system path makes them normal again. This is where the anamorphic lens fits into place in the system. When placed in front of the projector lens it produces a similar stretch but now in the horizontal direction, and with optics. Both steps are needed, making first the image taller electronically with a scaler and later wider optically with the anamorphic lens. The combined effect of both actions produce an image that is larger but proportional to the original image when it was sandwiched between the black bars, maintaining the exact same aspect ratio as the original 2.35:1 source.</p>

<p><br />
<B>How Could We Use the System?</B></p>

<p>Many HD movie channels and even broadcast HD movies distribute 2.35:1 movies as they are, with top/bottom bars sandwiched within the 16:9 aspect ratio of the HD signal to maintain the original geometry of the content. Such wide-screen content could certainly display well in a CinemaScope&trade; capable home theater, the scaler and the anamorphic lens would do their job with that content as well, in other words, this is not about wide-screen [HiDef]DVDs pre-recorded movies only.</p>

<p><br />
<B>Does Resolution Matter in CinemaScope?</B></p>

<p>Having a 1080p projector rather than a lower resolution projector always helps on larger screens, but a 720p projector could also be used to implement CinemaScope&trade;. However, a 720p image barely has 1 million pixels of spatial resolution (720x1280= 921,600 pixels) displayed on each frame of content, while a 1080p projector more than doubles that (1080x1920=2,073,600 pixels). Regardless of the frame rate and the original resolution of the content, the above will be the spatial resolution outputted by the projector's chip.</p>

<p>If the primary purpose of a HT is to show CinemaScope&trade; movies, the spatial resolution factor is more important than the classical benefits of the faster temporal resolution of the 720p format, 60fps frame rate are well suited to fast sports, such as ESPN HD, but the horizontal line has only 1280 pixels, rather than 1920.</p>

<p>A non-1080p resolution projector could still do a decent job as long as the increased width of the horizontally larger screen (for an image that is horizontally expanded by the anamorphic lens) is not pushed beyond reasonable limits. Going too large might negatively affect the image quality for the particular viewing distance.</p>

<p>There are many more areas of discussion in the resolution subject, such as the ability to handle 24fps 1080p content originating from film stored in HD DVD and Blu-ray. There are projectors that are capable to accept 1080p at 24fps and can display it at multiples of that frame rate, without doing 2:3 pull-down to go to the 60i interlaced world to later de-interlace it to 60p. One should always avoid any unnecessary video processing and conversions to let the scaler to do its CinemaScope&trade; vertical stretch with a signal as clean as possible to avoid compound artifacts.</p>

<p><br />
<B>2.35:1 Screens, How People Use Them </B></p>

<p>1080p projectors are now available in good variety, as well as good quality anamorphic lenses/transports; scalers with vertical stretch ability for constant height installation are also available, all relatively affordable. 2.35:1 movie fans might see an opportunity to jump out of the 16:9 HD aspect ratio bandwagon and into the 2.35:1 CinemaScope&trade; style, to watch their movies on a screen with the same aspect ratio as the movie.</p>

<p>However, installing a 2.35:1 screen is only part of the solution, as we discussed earlier, a scaler has to stretch the image vertically to get rid of the top/bottom black bars and let the projector's chip use its full vertical resolution (1080 or 720), which means more projected light from 30% more pixels, then the anamorphic lens stretches the image horizontally.</p>

<p>Not doing the full combination of the steps above, and performing only manual adjustments like zooming, refocusing, masking, to push the top/bottom letterbox bars out of the 2.35:1 screen frame, even if they are covered with masking material, potentially looses precious projector chip resolution and light output. That approach is used by many home theater enthusiasts and is not within the scope of these articles. There is a cost/benefit on each solution, each person has to decide what is the best solution for their particular installation, quality, flexibility, easy of operation, and pocket. In theory they both need a capable projector and a 2.35:1 screen to start with, those two pieces could be reused when moving from one approach to the other.</p>

<p><br />
<img src="/images/articles/hdht-construction.jpg" alt="Construction" align="left" /><B>The Perfect Storm Team</B></p>

<p>I would like to thank all the companies that participated on this CinemaScope&trade; project for their collaboration and personal efforts:</p>

<p>Wing Chung, Director of Project Engineering, Optoma (DLP projector/scaler)<br />
Shawn Kelly, President Panamorph (anamorphic lens, mounting plate)<br />
Bob Yellin, President Z-bott LLC (anamorphic lens transport)<br />
Don Newquist, Principal HTIQ (masking/curtains HT system)<br />
Mark Haflich, Owner Soundworks (projector, screen, etc. dealer/installer)<br />
Chuck Williams, ISF calibrator<br />
Jose Granados, Owner Granados Co., (installation masking/curtains rail system)</p>

<p>When we started a couple of months ago, some of the products were still in a prototype stage or just released, and the installation and calibration efforts required creativity, breaking new ground to make things work. But we all capitalized from this learning experience in one way or another.</p>

<p>Regarding the installation in particular, I would like to thank Mark Haflich, owner of Soundworks in Kensington MD, for his valuable support and the professional dedication of his installation crew, who worked several days until very late and with whom I had the pleasure to share many inventive moments, as well as the nightmares, and the satisfaction of seeing some pieces fit under the powers of creativity, often without the benefit of instruction manuals or suitable installation hardware.</p>

<p>Shawn (Panamorph) and Wing (Optoma) have both been excellent team experts that facilitated their insider knowledge for the optimal harmonization of products, even those that their companies did not have in production yet.</p>

<p>I am still working with the final HT phases with Don (htiq, home theater electric masking/curtains, etc), regarding the pillar masking and motorized curtains for 4:3 and 16:9 viewing in the constant height system. He has been an excellent collaborator in making my curtain/masking system design possible. His expert advice in that area of the project has been very valuable.</p>

<p>It is important to note that I am not doing curtains and masking just to beautify the HT installation, but to primarily improve the perception of the image by the eyes. Image quality was and still is one of the main drivers of this project.</p>

<p><br />
<B>What is Next</B></p>

<p>In the next installments I will cover each of the areas about the projector, lens, transport, plate, procurement, installation, calibration, masking system, etc. CinemaScope&trade; at the consumer's home is warming up very quickly in 2007. It is possible, affordable, and breathtaking, and we could do a great service to our readers by providing them with a real case scenario to successfully repeat at their home.</p>

<p>Stay tuned for Part 3</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>March  6, 2007 11:52 AM</b>
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
			<?=getComments(557)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 557)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/cinemascope-hdht-part-2.php" type="text/javascript" charset="utf-8"></script>
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