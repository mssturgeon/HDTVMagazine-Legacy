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
		AND e.entry_id = 530";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 530 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 530 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 530";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 530";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CinemaScope&#8482; HDHT - Part I - The Concept" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CinemaScope&#8482; HDHT - Part I - The Concept" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="CinemaScope&#8482; HDHT - Part I - The Concept" />
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
	<title>HDTV Magazine - CinemaScope&#8482; HDHT - Part I - The Concept</title>
	<meta name="keywords" content="wide screen, black bars, cinemascope hdht, aspect ratio, screen movies, image, cinemascope, screen, wide, bars, black, hdtv, consumer, part, resolution, hdht, aspect, project, ratio, articles, movie, could, still, viewing, theater" />
	<meta name="description" content="Anyone can relate to how CinemaScope brings the movie experience to the eyes, some of us can even remember the Cinerama experience over 40 years ago, almost everyone is familiarized with the term and meaning of HDTV, and with HT (Home Theater).  So I created the term &quot;CinemaScope HDHT&quot; because it is actually blending the 3 concepts and technologies into one, and this series of articles is about implementing the 3 concepts, at your home, finally with consumer products.

For some people the choice of 16:9 (aspect ratio of 1.78:1 for HDTV) has been..." />
	<meta name="title" content="CinemaScope&amp;#8482; HDHT - Part I - The Concept" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="CinemaScope&amp;#8482; HDHT - Part I - The Concept" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Anyone can relate to how CinemaScope brings the movie experience to the eyes, some of us can even remember the Cinerama experience over 40 years ago, almost everyone is familiarized with the term and meaning of HDTV, and with HT (Home Theater).  So I created the term &quot;CinemaScope HDHT&quot; because it is actually blending the 3 concepts and technologies into one, and this series of articles is about implementing the 3 concepts, at your home, finally with consumer products.

For some people the choice of 16:9 (aspect ratio of 1.78:1 for HDTV) has been..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=530', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php">CinemaScope&#8482; HDHT - Part I - The Concept</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 25, 2007</b>
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
<li><a href="/articles/2007/03/cinemascope_hdht_-_part_2.php">CinemaScope&#8482; HDHT - Part 2</a></li>
<li><a href="/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
Anyone can relate to how CinemaScope brings the movie experience to the eyes, some of us can even remember the Cinerama experience over 40 years ago, almost everyone is familiarized with the term and meaning of HDTV, and with HT (Home Theater).  So I created the term "CinemaScope HDHT" because it is actually blending the 3 concepts and technologies into one, and this series of articles is about implementing the 3 concepts, at your home, finally with consumer products.

<p>For some people the choice of 16:9 (aspect ratio of 1.78:1 for HDTV) has been cut short as aspect ratio and not wide enough to convey the typical feeling of wide-screen movies.  If the movie itself has been filmed in a wider aspect ratio, like most movies do at 2.35:1, it means that the 16:9 HDTV set would have to show the movie image with top/bottom black bars to compensate for its wider geometry.  </p>

<p>It also means that the image would occupy a smaller space within the 16:9 cabinet frame of the TV, giving the impression of missing something above and below.  </p>

<p>Actually, those black bars use part of (waste) the precious resolution of the media and of the TV (panel, chip, etc).  The movie image in between the bars ends up not as resolved as the Hi Def DVD media and the HDTV equipment make you believe they could be on their advertising schemes.  </p>

<p>That 1080p holy-grail TV you eagerly bought would actually end up using about 70% of its capable resolution for the actual 2.35:1 image, because the black bars use the other 30% of the available resolution for just black lines.  </p>

<p>As to the media side, the Hi Def DVD disc containing a wide-screen 2.35:1 movie would actually store those black bars in the disc above and below the image, wasting 30% of real state occupied by the video, and the player would play them back through your TV/projector as if they were part of the movie.</p>

<p>Black bars, top and bottom, a wide-screen image in a wide-screen TV and still bars, hmmm...</p>

<p>16:9 HDTV was supposed to be the way of the future, a wide-screen solution wider than the legacy 4:3 NTSC system, but wait, let us look at the other side.  We still tune to lots of programming recorded in 4:3, that when displayed on a 16:9 HDTV would have two pillar bars at each side to compensate for the different geometry of that "squarish" 4:3 image.</p>

<p>Although one can still stretch the 4:3 image to fill the 16:9 frame of the TV, some viewers dislike faces that become too fat, heads or feet cut by the image expansion, game scores that are usually down below in the image on an ESPN sport program become out of the viewing area due to the expansion, etc.  </p>

<p>Some TVs have scrolling capabilities to let you move up/down edges of the viewing area to bring back pieces of the image, like bringing up the server player on a tennis baseline at the bottom of the screen, but most sets do not have such capability, they only have a couple of image stretching modes, non-standard modes that the TV designer expects you to like, if you know what I mean. </p>

<p>Black bars again, now with pillars on the side, not much we could do about legacy but, hmmm... </p>

<p>People thought that we were getting rid of black bars when switching from 4:3 TVs to 16:9 HDTVs, especially to view wide-screen movies, but unfortunately is not quite that way.  </p>

<p>Black bars, on the four sides, and sometimes all at the same time within the same program, are here to stay for the long journey of our new system of 16:9 HDTVs, whether you have a TV or a panel, because all HDTV cabinets are cut as 16:9.  There has to be black bars because the physical aspect ratio of the TV cabinet cannot change to adapt to the aspect ratio of the media.  </p>

<p>So you think, there it goes my CinemaScope dream, not quite.</p>

<p>If you are looking for a front projection solution for your HT wide-screen movies and for your HDTV viewing, there is something you could now do for the wide-screen dream on CinemaScope.  </p>

<p><img src="/images/articles/cinemascope.jpg" alt="CinemaScope" align="left" />Using the concept of "constant height", which is maintaining the same image height of the screen for both 16:9 and 2.35:1 material, an electronic /optical solution can display a wider image by making wider the sides of your viewing area, just like some elegant local theaters still do when the main feature is about to start and roll the large curtains laterally to let open a much wider screen.  Get the popcorn now.    </p>

<p>That is exactly the subject of these articles, how to implement your own "CinemaScope HDHT (High Definition Home Theater)" for wide-screen movies in a HDTV HT projection environment.</p>

<p>Remember those emotions flowing when viewing that large wide-screen image at the local theater, and you were fully immersed in the action?  An image that excited the very wide angles of your horizontal vision in a way that no NTSC and no HDTV and no Hi def DVD could do, regardless of the resolution they might have, this about the effect of increasing aspect ratio, and increasing the angle of view, not just resolution.</p>

<p>CinemaScope HDHT certainly benefits with more resolution, but just having 720p, 1080i, or even 1080p would not do, they can only show more pixels, or show them faster to be more suitable to fast action on the image, but the geometric difference of most movies vs. the smaller aspect ratio of TV, NTSC or HDTV, can not be fully addressed by resolution increases alone.  Although some people use their existing projection systems with zoom and masking to adapt to those differences, the resolution lines are still lost to black lines, behind the masks, but lost. </p>

<p>Over the last few years a few companies have been working overtime to bring wide-screen CinemaScope movie viewing to consumers at home, and brought to market several technologies together, electronic and optical, and now relatively affordable to regular consumers looking for their own HT projection environments now that 1080p projectors have become so affordable.</p>

<p>However, there are still some areas that a designer/installer has to carefully address to make sure the pieces of a system fit with each other, because a wide-screen anamorphic lens manufacturer needs to work with the projector manufacturer, and both need a scaler manufacturer with a suitable product, and a lens transport manufacturer, installation hardware manufacturer, etc.  </p>

<p>Many of these companies are working hard to offer consumer-transparent harmonic multi-product solutions, and some projector manufacturers are even taking over the responsibility to deliver all the pieces manufactured by other vendors specially tailored to their projectors, but it is always a good idea to hire a local installer that knows about how to install a CinemaScope concept.  </p>

<p>One can always add to the project the non-electronics part of making that CinemaScope home-theater looking like a real theater, with columns, dark ceilings, seats, etc., and that is not within the scope of these articles.  The audio part is not covered, and is assumed the consumer would already have a multi-channel audio system.</p>

<p>Up until recently the coordination efforts of fitting the products was left to the creativity of individual home theater enthusiasts, people that had to perform various tailored solutions to make things work, some not designed to work together, no instructions in most cases, some were personal inventions of manually made anamorphic lens and other parts built for the pieces of the HT to fit well with the rest.  </p>

<p>Some magazines take one area, and entice you to immerse your efforts for a full project without necessarily telling the whole story, weak areas that could go wrong, and affect the rest of the project, and most generally the overall budget.</p>

<p>Advertising is easy, reaching the final objective and investing your own money in a real project and technical solution is not, and the regular consumer should be made aware of the roadblocks and the collateral expenses that a dream CinemaScope project could entail.  </p>

<p>No magazine covers the whole story, and you should be prepared before you start signing the first check.  One thing is the theory, another very different thing is actually doing it, and so I decided to be a Guinea Pig and invest my own money on this concept, and help our readers with the articles.</p>

<p>I decided to dedicate a new room for this experience and technical challenge to see what is possible for a regular consumer with current electronics and optics.  This is not about building and beatifying a HT with seats, columns, popcorn machines, curtains, star ceiling, that is easy, many companies do such projects, this is about how to get the CinemaScope video solution to work, you add your own audio.  </p>

<p>Over the last couple of months I contacted several manufacturers to work together with them, and get their latest products, some of them were prototypes.  I agreed with them that I would write these articles to tell the true story to the consumer and that they will collaborate in documenting the effort.  </p>

<p>I also contacted local dealers, installers, an ISF calibrator, etc. exactly the same way any consumer would have to do, this was not about using my experience and capabilities of doing the project, this was about doing the steps that a regular consumer would have to do, and paying the bills any consumer would have to pay, no discounts, no breaks, no freebies.  Just like other magazines do, don't they?  This is the only way these articles could be of value to you the consumer.</p>

<p>Stay tuned for Part II, and welcome to the beauty of CinemaScope HDHT.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 25, 2007  4:30 AM</b>
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
			<?=getComments(530)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 530)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php" type="text/javascript" charset="utf-8"></script>
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