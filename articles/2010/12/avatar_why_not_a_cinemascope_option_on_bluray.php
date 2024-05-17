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
		AND e.entry_id = 4104";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4104 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4104 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4104";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/12/avatar-why-not-a-cinemascope-option-on-bluray.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4104";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Avatar - Why not a CinemaScope Option on Blu-ray?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Avatar - Why not a CinemaScope Option on Blu-ray?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Avatar - Why not a CinemaScope Option on Blu-ray?" />
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
	<title>HDTV Magazine - Avatar - Why not a CinemaScope Option on Blu-ray?</title>
	<meta name="keywords" content="aspect ratio, blu ray, local theater, ratio movie, cinemascope home, cinemascope, image, movie, theater, ratio, aspect, screen, theaters, home, avatar, blu, ray, extraction, same, shown, version, local, frame, wider, disc" />
	<meta name="description" content="This is a follow up article to the previous 3D World 2010 Conference in NYC article, in which I highlighted the subject of the CinemaScope presentation for Avatar. I will cover the subject in more detail here.

As mentioned in the previous article, the Avatar 3D trailer was shown in the same CinemaScope widescreen aspect ratio as the original 3D movie in the local theater. IMAX cinemas showed the movie at a more squarish aspect ratio. The demo at 3D World was done with a Sony 4K projector with polarizing filters and a 16:9 screen showing a CinemaScope 3D image that was cropped with top/bottom black bars and displayed as dual 1080p interleaved images viewed with RealD polarized glasses.

As usual for 3D, the 3D image was low in luminance, but the image quality was acceptable considering that the projector was very far away from the screen. Although James Cameron likes the CinemaScope aspect ratio..." />
	<meta name="title" content="Avatar - Why not a CinemaScope Option on Blu-ray?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Avatar - Why not a CinemaScope Option on Blu-ray?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/12/avatar-why-not-a-cinemascope-option-on-bluray.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This is a follow up article to the previous 3D World 2010 Conference in NYC article, in which I highlighted the subject of the CinemaScope presentation for Avatar. I will cover the subject in more detail here.

As mentioned in the previous article, the Avatar 3D trailer was shown in the same CinemaScope widescreen aspect ratio as the original 3D movie in the local theater. IMAX cinemas showed the movie at a more squarish aspect ratio. The demo at 3D World was done with a Sony 4K projector with polarizing filters and a 16:9 screen showing a CinemaScope 3D image that was cropped with top/bottom black bars and displayed as dual 1080p interleaved images viewed with RealD polarized glasses.

As usual for 3D, the 3D image was low in luminance, but the image quality was acceptable considering that the projector was very far away from the screen. Although James Cameron likes the CinemaScope aspect ratio..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4104', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/12/avatar-why-not-a-cinemascope-option-on-bluray.php">Avatar - Why not a CinemaScope Option on Blu-ray?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December 23, 2010</b>
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
				<p><a href="http://www.amazon.com/gp/product/B002VPE1B6?ie=UTF8&tag=hdtvmagazine-20&linkCode=as2&camp=1789&creative=390957&creativeASIN=B002VPE1B6"><img style="float:right; padding:5px" align="right" border="0" src="http://ecx.images-amazon.com/images/I/512Kb3ix8VL._SL160_.jpg"></a><img src="http://www.assoc-amazon.com/e/ir?t=hdtvmagazine-20&l=as2&o=1&a=B002VPE1B6" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />This is a follow up article to the previous 3D World 2010 Conference in NYC article, in which I highlighted the subject of the CinemaScope presentation for Avatar. I will cover the subject in more detail here. <p><b></b> <h2>The Avatar 3D Trailer in CinemaScope on a 16:9 screen?</h2> <p><img class="keyimg" align="left" src="http://www.hdtvmagazine.us/articles/images/d1364380dfd8_12A24/clip_image004_2d14fe97-d937-4d5b-b8a5-93acf2813ff3.jpg" width="307" height="409">As mentioned in the previous article, the Avatar 3D trailer was shown in the same CinemaScope widescreen aspect ratio as the original 3D movie in the local theater. IMAX cinemas showed the movie at a more squarish aspect ratio. The demo at 3D World was done with a Sony 4K projector with polarizing filters and a 16:9 screen showing a CinemaScope 3D image that was cropped with top/bottom black bars and displayed as dual 1080p interleaved images viewed with RealD polarized glasses. <p>As usual for 3D, the 3D image was low in luminance, but the image quality was acceptable considering that the projector was very far away from the screen. Although James Cameron likes the CinemaScope aspect ratio (and used it on many of his movies, such as the guy with machine-gun looking at you with the red eyes) he <a href="http://entertainment.ca.msn.com/movies/features/article.aspx?cp-documentid=23942281">prefers</a> the 16:9 aspect ratio for Avatar, which is the original aspect ratio the movie was filmed. I personally prefer the CinemaScope aspect ratio, especially on modern action/epic movies, including Avatar, so I welcomed the aspect ratio of the demo. <p>The interesting part is that this 3D World Conference presentation of a 16:9 original-aspect-ratio movie, using a 16:9 screen, a 16:9 4K Sony projector, released as 16:9 in Blu-ray 2D and DVD discs (and soon a 3D disc in the same aspect ratio), was rather shown in CinemaScope. <p>This contradicts the director’s approach of always showing the maximum image width available on the theater’s screen, which in the case of a CinemaScope theater a vertical top/bottom cropping would necessary, losing some height, but on a 16:9 or IMAX screen the whole height of the original image can be shown, like it should have been the case on this trailer demo with the 16:9 screen. <p>After the conference I received and explanation: the pressure of a crazy schedule and last minute arrival to the conference did not give the person sufficient time to check the aspect ratio of the 3D Avatar trailer before it was actually shown to the audience. I sympathized with the pressure, I was in a similar situation; I traveled for 12 hours roundtrip for a 7 hour conference day. <p>However, the event touched a nerve in me that was latent. When the Blu-ray disc was released, why weren’t consumers offered an option to buy a CinemaScope version as well?  <h2>Which should be the Correct Aspect Ratio for the Avatar’s Blu-ray?</h2> <p><img class="keyimg" align="right" src="http://www.hdtvmagazine.us/articles/images/d1364380dfd8_12A24/clip_image006_4f9d1b26-7dd9-4ffd-bf4e-e90406d5cb98.jpg" width="274" height="406">The answer may be “the same aspect ratio of the movie as officially projected at the local theater so consumers can reproduce at home the same viewing experience the director wanted to show <b>at that theater</b>”. <p><a href="http://www.collider.com/2010/03/24/james-cameron-interview-avatar-blu-ray-also-talks-titanic-3d-and-avatar-2/">According to James Cameron</a>: <i>“We finished the picture in 16×9 and then we vertically extracted the cinemascope when we were mastering the film for theatrical release“. </i> <p>During the panel discussion at 3D World, and considering the technical image innovation in 3D of Avatar historically, I opened the discussion (or the can of worms I should say) of why the Blu-ray transfer did not also offer the CinemaScope aspect ratio, if the version was already extracted by the director for the theaters, <a href="http://www.avsforum.com/avs-vb/showthread.php?t=1240365">so consumers</a> can choose the disc format of the aspect ratio of the movie they have seen at the theater, or the one they prefer. <p>Compared to the 16:9 format of the released Blu-ray disc, the CinemaScope display aspect ratio at the theater provided a wider visual impact relative to its shorter height. Reportedly, the film was <a href="http://bluray.highdefdigest.com/news/show/Industry_Trends/3D/Drew_Taylor/Avatar/James_Cameron/Jon_Landau/20th_Century_Fox/High-Def_Digest_Talks_With_Avatar_Producer_Jon_Landau/4591">shot as 16:9</a> to offer a taller image at IMAX theaters and at home’s HDTVs, which are now at over 50% of US households; besides, black bars on letterboxed movies are not very popular and usually misunderstood by most.  <p>Based on the comments that the 16:9 camera shots were also correctly composed for CinemaScope viewing, which means the CinemaScope image was a subset of the 16:9 image, I experimented with doing the image extraction myself from the 16:9 Blu-ray disc using my CinemaScope home theater; unfortunately the resulting image was not the same as seen at the theater. Why not? Keep reading. <p><b></b> <h2>Reproducing James Cameron’s Extraction at Home </h2> <p>As I mentioned before, reportedly, the composition of the camera shots was also made with a Cinemascope mindset. The CinemaScope extraction cropped some of the top/bottom of the original 16:9 image to fit the shorter CinemaScope frame. The excluded content was expected not to affect the story-telling as it was meant to be told, so CinemaScope movie theaters could maximize their wider screen with that version of the movie. The same lateral content is said to be present on both versions all the way to the edges of their video frame.  <p><img class="keyimg" align="left" src="http://www.hdtvmagazine.us/articles/images/d1364380dfd8_12A24/clip_image008_d897ded0-be5a-434a-9be4-6443b73e71c8.jpg" width="410" height="308"> <p><a href="http://bluray.highdefdigest.com/2915/avatar.html">As with others</a>, I preferred the CinemaScope view because it produced a better immersion into the movie and plot with the many spacious Pandora shots of Avatar, rather than viewing a taller but relatively narrower 16:9 image, regardless of size. But I understand that many viewers may like to be overwhelmed with the huge and squarish IMAX, and at home many may prefer to fill the whole HDTV screen even when the geometry of the image is different than what they viewed at the CinemaScope local theater.  <p>Reportedly, the idea of an extracted widescreen version was to avoid displaying a relatively smaller 16:9 image with side pillar-boxes on wider CinemaScope theaters, which is implicitly admitting that the visual impact of a wider CinemaScope image was a better choice for viewer immersion into the movie. <p>The visual impact of the wider CinemaScope aspect ratio can be appreciated when viewing the image from an appropriate distance to obtain the wider angle of vision. Not from the last row in a long theater, which would reduce the width of the angle of vision, nor from the first row in a short theater (or IMAX theater), which would make the angle of vision wider but would also entail frequent head/eyes movement when attempting to view the image in full on a huge screen. The idea is to fill the extreme left/right of a person’s lateral vision with comfortable viewing for a more immersive experience without making the image too tall, even when the eye is not capable of discerning the fine detail in the lateral extremes of the image when focusing on the center of the image. <p>If the filmed shots where actually composed for the dual purpose as declared, so objects and people would fit proportionally well within the spacious video 16:9 frame (also for IMAX) and also composed well for a CinemaScope extraction (whereby objects and people would still fit proportionally well without looking cramped or over zoomed in the less spacious height of the CinemaScope frame) the extraction at home should have shown a similar image as at the local theater, and it did not. <p>Although not declared by James Cameron, reports indicated that the extraction dynamically paned the content vertically so foreheads and other scenes would not be excessively cropped in the CinemaScope version, as opposed to planning and composing all the shots with a shorter CinemaScope frame perspective for those type of theaters, so objects, people, and surroundings are in proportional harmony with each other and breathe well within the shorter space of the CinemaScope image frame. <p>The 16:9 format choice on the Blu-ray disc was to be expected considering that more than half of the US population is reported to have 16:9 HDTVs, unfortunately that decision left out those that prefer to view CinemaScope movies at home as they were shown at the theaters, regardless if they use TVs or home theaters. <p>As it happened to me, a growing audience of CinemaScope home-theater enthusiasts <a href="http://www.avsforum.com/avs-vb/showthread.php?t=1240365">are unable</a> to reproduce the same Avatar image they viewed at the CinemaScope local theater using their projectors and anamorphic lenses to extract the CinemaScope version from the 16:9 disc because the vertical extraction and cropping they can apply at their home theater is from the center of the video frame equally to all the scenes throughout the whole movie, rather than dynamically scanned as the scene dictates, as done by the director for the version he created for the CinemaScope theaters. <p>The result: excessive forehead cropping on many Navi scenes was immediately apparent with close-ups that appeared over-zoomed within a shorter frame composition that seemed cramped within the less spacious vertical surrounding to the characters, and the recording logs appearing on the upper/lower part of the image where not fully visible, although the subtitles translating the Navi language were unusually high (close to the vertical center of the image) rather than the typical lower area of the image. <p>While the extraction I did was viewable, I was not comfortable with it so I switched to view the movie as a smaller 16:9 image with pillar-boxes (within the wider CinemaScope screen) which reduced the lateral impact and the overall immersion into the movie, and it was not a matter of sitting closer to the screen to compensate for the reduced angle of view. Reportedly, that is exactly what the director wanted to avoid in CinemaScope theaters so he did the extraction. <p>The adoption of CinemaScope home theaters have been growing considerably in the past decade. Most projector manufacturers embraced anamorphic lenses for CinemaScope home theaters and are also introducing their 3D projector versions. That market, and the market of viewers that prefer the black bars on their TVs because they know that the resulting image, although smaller, would be exactly the same as they have seen at the local theater, was obviously ignored in the Blu-ray decision of releasing only one format of a movie that was shown in several formats at movie theaters. <h2>Final Thoughts</h2> <p>Is this different than many movies that are seen in widescreen at the local theater and are displayed “modified to fit your TV” at home? Or different than HBO’s style of changing the image’s aspect ratio of a movie to avoid showing black bars on an HDTV? Or different than consumers zooming non-16:9 content to fill their HDTV screen? <p>Not by much, in all of those cases (as with Avatar) preference is given to fully filling a TV screen, rather than to respect those that want to view at home the aspect ratio of the movie as seen at CinemaScope local theaters.  <p><a href="http://www.amazon.com/s/ref=nb_sb_ss_i_0_6?url=search-alias%3Daps&amp;field-keywords=avatar&amp;sprefix=avatar">Several pre-recorded versions</a> of Avatar are gradually being released, in Blu-ray 2D and DVD, with additional features, more minutes of movie, in <a href="http://www.amazon.com/Avatar-Version-Blu-ray-Sam-Worthington/dp/B003EYVXVY/ref=sr_1_7?ie=UTF8&amp;qid=1290719276&amp;sr=8-7">Blu-ray 3D</a> (but first only available when buying a specific television brand, so most people must wait), in Extended/Collectors/Original Theatrical/Combos/with BD-Live versions of 1, 2, 3 discs packages, etc. Why not releasing the version of the movie shown at CinemaScope movie theaters? <p>I upgraded my projector and lenses several times within a couple of years in pursuit of quality, and I was considering upgrading my home theater again, now to 3D, with the same CinemaScope screen and anamorphic lens system, which are compatible with active-shutter systems, but quite frankly this matter does not inspire me. Content may be the king but also a killer of the hardware market. <p>Thank you 3D World Conference for giving me the opportunity and motivation for bringing this subject to the surface. Hopefully someone with the power to make it happen will listen.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December 23, 2010  7:49 AM</b>
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
			<?=getComments(4104)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4104)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/12/avatar-why-not-a-cinemascope-option-on-bluray.php" type="text/javascript" charset="utf-8"></script>
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