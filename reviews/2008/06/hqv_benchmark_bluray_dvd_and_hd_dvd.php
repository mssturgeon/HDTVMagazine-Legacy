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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 1406";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1406 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1406 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1406";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1406";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HQV Benchmark Blu-ray, DVD and HD DVD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HQV Benchmark Blu-ray, DVD and HD DVD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HQV Benchmark Blu-ray, DVD and HD DVD" />
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
	<title>HDTV Magazine - HQV Benchmark Blu-ray, DVD and HD DVD</title>
	<meta name="keywords" content="hqv benchmark, blu ray, dvd player, external scaler, digital video, test, dvd, player, video, tests, content, display, testing, response, hqv, benchmark, pixel, blu, ray, vertical, horizontal, same, pattern, detail, disc" />
	<meta name="description" content="HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, &lt;a href=&quot;http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789&quot;&gt;Scaler&lt;/a&gt;. 

The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of the tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to disc players..." />
	<meta name="title" content="HQV Benchmark Blu-ray, DVD and HD DVD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HQV Benchmark Blu-ray, DVD and HD DVD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, &lt;a href=&quot;http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789&quot;&gt;Scaler&lt;/a&gt;. 

The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of the tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to disc players..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1406', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php">HQV Benchmark Blu-ray, DVD and HD DVD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>June 19, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=318&category=HDTV Accessories">HDTV Accessories</a></b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><br clear="left"/><b>Product line up and pricing</b></p>

<ul><li>DVD: HQV Benchmark Version 1.4 NTSC $20</li>
<li>DVD: HQV Benchmark Version 1.4a PAL $20 (not reviewed)</li>
<li>Blu-ray: HQV Benchmark Version 1.0 $20</li>
<li>HD DVD: HQV Benchmark Version 1.0 $10</li></ul>

<ul><li>Bundle 1: Blu-ray, DVD (NTSC) $30</li>
<li>Bundle 2: HD DVD, DVD (NTSC) $25</li>
<li>Bundle 3: Blu-ray, DVD (PAL) $30 (not reviewed)</li>
<li>Bundle 4: HD DVD, DVD (PAL) $25 (not reviewed)</li></ul>

<p><b>Summary: </b>A very useful testing regimen if you understand the limitations<br /></p>

<p>Have you ever wondered why special features on DVD don't look as good as the movie you watched? Maybe you've wondered why some of your DVDs don't look as good as others? Or why Blu-ray Hollywood movies appear to have more detail than concerts or documentaries? HQV Benchmark has a testing regimen to help you figure all of that out!</p>

<p>HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, <a target="_blank"href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789">Scaler</a>. </p>

<p>The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of its tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to your overall experience.</p>

<p><br />
<b>Testing, Scoring, Education</b></p>

<p>The test material concentrates mostly on deinterlacing and scaling. Silicon Optix provides a downloadable PDF file of the test regimen which includes how to score each test along with detailed explanations. While the guide provides comparison images, the resolution is not high enough for many of the tests to assist you in fully appreciating what to look for.</p>

<p><a target="_blank" href="http://www.hqv.com/contentEngine/dspDocumentDownload.cfm?PCVID=6557af58-7e90-e2a3-bea3-f6ec25bf8781">HQV Benchmark DVD Testing and Scoring Guide</a><br />
<a target="_blank" href="http://www.hqv.com/contentEngine/dspDocumentDownload.cfm?PCVID=6557b0fd-7e90-e2a3-bdde-f1edd6040515">HQV Benchmark Blu-ray and HD DVD Testing and Scoring Guide</a></p>

<p>Many of the DVD test results and all of the Blu-ray and HD DVD test results rely on perception requiring proper calibration of the display for valid results and is noted by * in the title. Neither disc provides a full suite of calibration test patterns. Digital Video Essentials is recommended and available in Blu-ray and DVD. There is also an HD DVD and DVD combo available while supplies last. If your system has been ISF calibrated, you are ready for testing. If not, <a target="_blank" href="http://www.isfforum.com/Find-a-Calibrator/ISF-Forum-Calibrators.html">check the ISF Forum</a> for a professional ISF calibrator in your area.</p>

<p><br />
<h2>HQV Benchmark DVD</h2></p>

<p>The test material is a mix of 4:3 and 16:9 original aspect ratio (OAR) content, which will require you to manually change the aspect because there are no flags to trigger the auto 4:3/16:9 aspect feature that some players support. If the auto aspect does not provide a manual feature then you are stuck with 4:3, which will not be correct for some of the 16:9 tests. The introduction and test material does not provide reference imaging quality for showing off the DVD format at its best.</p>

<p><b>Color Bars (4:3)</b><br />
This is a resolution test that is part of a multiple test pattern. This is not only one of the more useful patterns on the disc, but contains other tests such as color decoding at different saturation levels and luminance (video) levels. I recommend testing in both 16:9 and 4:3 aspects. Above the middle is a resolution response test for both luminance and chroma. Going left to right they are numbered as 4, 3, 2 and 1 with 1 representing 720 pixels horizontally. The catch is that this is a 4:4:4 encoded pattern which means the chroma has the same response as luminance. The consumer DVD standard (and HDTV) uses 4:2:0 encoding which cuts the chroma response to half of luminance to conserve bandwidth and storage space. The chroma response in block 1 serves no purpose since it cannot be properly reproduced so disregard those results. This pattern is part and parcel of confirming proper calibration prior to testing.</p>

<p><b>Jaggie 1 (16:9)</b><br />
A single bar is constantly rotated inside a circle testing the high detail b/w video or luminance portion of an image. </p>

<p><b>Jaggie 2 (16:9)</b><br />
Three bars move back and forth in a narrow arc, covering 50-20 degrees, inside a circle testing the high detail b/w video or luminance portion of an image.</p>

<p><b>Flag (4:3)</b><br />
A final jaggie test of both color and luminance using the common and notoriously difficult American flag waving in the wind.</p>

<p><b>Detail (16:9)</b><br />
The disc narrative and guide both stress that this material should "exhibit fine detail resulting in a crisp realistic image" and other similar statements. This material will never have the response one would expect or can get viewing properly captured and mastered DVD video. Page 10 of the guide compares two images that hardly look different for a test score high of 10 and 0, and in this case resembles reasonable expectations for this test.</p>

<p><b>Noise (4:3)</b><br />
If the TV or player does not have a noise reduction (NR) feature, skip this test; it is not about players. MPEG NR targets compression noise, is processed differently and should not be used for this test. A series of 12 still images are provided and most target the blue color channel. Two of those images never showed any noise and one was marginal. These images are great examples of a noisy analog cable service or a satellite / cable set top box delivered via channel 3 or 4 to your TV. This test is all about the NTSC broadcast television system, analog cable, analog RF tuners and the RF noise that can easily come from them. While having little to do with players, they are useful for DVD recorders and broadcast NTSC. For high fidelity with DVD, NR on your DVD player should be turned off. For recorders this feature may make some or all of your noisy channels more palatable but in most cases the setting will also apply to DVDs, in which case it should be turned off.</p>

<p>One point missing from the guide; the first step is to view the content with NR turned off and that includes the TV if available. Look through or ignore the noise and recognize the detail that is present. Now turn on NR on the player and see how much noise is removed along with any loss in detail. You can also reverse the test, turn the NR off on the player and turn it on for your TV if available. Some NR circuits offer different range levels in which case test all of them and determine which setting provides the best balance of detail versus noise suppression.</p>

<p><b>Motion Adaptive Noise (16:9 and 4:3)</b><br />
A 16:9 image of a roller coaster and a 4:3 image of a boat going down a river are provided. While an NR circuit can successfully navigate the prior noise test of still images, the addition of motion will show any artifacts created by the process and may help identify what kind of NR the video processor is using. Follow the same procedure for testing as previously described, following the guide for evaluation. The roller coaster is also a convenient test for LCD pixel speed provided you turn off NR on the player and display. </p>

<p><b>Film Detail (4:3)</b><br />
In the guide, this test is called 3:2 Detection, which is a far better description of the test result. A familiar movie scene is provided of an F-1 car passing by empty bleachers testing the standard 3:2 cadence required with 24fps (frames per second) content. </p>

<p><b>Assorted Cadences (16:9)</b><br />
A test clip is provided in 8 different cadences. This is the most brutal part of the testing regimen that few players or displays will pass. External scalers should pass most if not all of the following tests:</p>

<p>2-2 30fps film<br />
2-2-2-4 DVCAM<br />
2-3-3-2 DVCAM<br />
3-2-3-2-2 VARI SPEED Broadcast<br />
5-5 Anime<br />
6-4 Anime<br />
8-7 Anime<br />
3-2 24fps film</p>

<p><b>Mixed 3:2 with titles (4:3)</b><br />
4:3 images at 24fps are provided with the same kind of 30fps-based titles you would get from your broadcaster notifying you of weather alerts or emergencies. This is also delivered in the form of end credits at the end of a TV show. This applies far more to broadcast TV and your display rather than players.</p>

<p><br />
<b>HQV Benchmark DVD on Your Player</b></p>

<p>Just because a player fails some or all of these tests does not mean it will generate the same errors when playing a Hollywood movie on your player, nor would passing some of these tests qualify as high fidelity performance. None of the tests even relate to how the vast majority of movies are captured, processed and mastered for DVD along with how your player is designed to reproduce them. Missing from this disc is the same test material processed and mastered just like Hollywood does. Without such a reference point the person doing the evaluation may have unrealistic expectations of how well the test material should perform.</p>

<p>While it can be argued on the surface that some of these tests should apply to a player, as a reviewer I find myself in a catch 22. Test material from Avia, Video Essentials, Sound and Vision, Digital Video Essentials and nearly all popular movies look decent to fantastic although that same player fails all or some of the HQV Benchmark material. How can that be? And as reviewer, how do I report a passing or failing grade?</p>

<p>The key is understanding why an inexpensive DVD player can get decent results with no-name video processing. This is achieved during mastering by including progressive flags in the data directly from the mastering studio telling the video processor in the player how to take the interlaced fields and put them together for a proper 480p presentation. This is an extremely intelligent way to deliver a high fidelity performance envelope on the cheap! With a native 480p 16:9 display, typically CRT only, and a properly designed 480p player, you are in videophile nirvana due to this free ride but this is an HDTV world and most displays these days require the 480p free ride gets scaled to one of the HD scan rates, 720p, 1080i or 1080p. Proper deinterlacing is the crux and scaling is far easier so this free ride can provide decent to high quality performance beyond 480p depending on the design goals! There is a catch; no flags, no free ride and with incorrect flags, the free ride could turn bumpy with errors/artifacts.</p>

<p>The HQV Benchmark DVD technical twist is that the material is encoded as raw 480i, no progressive flags for dumb scaling, leaving the player entirely on its own to figure out how to deinterlace the content. The bottom line is that the vast majority of players are going to fail many of the tests that relate to DVD content. While the movie is bound to have these progressive flags, that may not be the case for special features. This has improved over the years, but for a movie buff and/or DVD collector much of a library is going to contain such content. On top of that, many a collection will have 4:3 letterboxed releases along with the oddball cadences that come with low volume or low budget productions, cult classics, anime and TV shows on DVD. Some folks desire a player or external scaler that can get the most out of such content. HQV Benchmark is exactly what the doctor ordered for reviewers and videophiles alike who are looking for a simple straight forward battery of tests to quickly determine performance with such content. The only test missing is for 4:3 letterboxed material. </p>

<p><br />
<h2>HQV Benchmark Blu-ray / HD DVD</h2></p>

<p>The introductory scenes of 16:9 video content are quite short, although in the other chapters about testing there is material of greater length. This content is worthy of overall image quality evaluation, but it does appear slightly soft in detail and flat in dynamic range compared to other reference material. Both discs provide the test materials in three different play formats; Play Loop, Play All Tests (manual advance) and Select Individual Tests. The first two automated play formats unfortunately skip one or more tests (noted in the test pattern breakdown) so if you want to view them all then choose Select Individual Tests.</p>

<p><b>HD Color Bars</b><br />
This pattern is only available under Select Individual Tests and appears to be derived from a non HD source and scaled to 1080i based on the color pixel errors at the edges where two colors meet. Useful for checking / confirming luminance and color levels prior to testing.</p>

<p><b>HD Noise</b><br />
Provides two images, a flower and sailing boat, which have motion components as well as noise. The Blu-ray version skips the sailing boat when choosing Play Loop. You may be hard pressed to even use this test since a noise reduction feature is not common for HD disc players or displays when viewing HD scan rates. Please check the DVD version of this test for additional details. </p>

<p><b>Video Resolution Loss</b><br />
A SMPTE RP-133 1920X1080 <a target="_blank" href="http://www.isfforum.com/viewtopic.php?t=82">1:1 pixel map</a> of luminance, b/w video, has a 360 degree constantly rotating bar. The results as described in the guide require a 1920x1080 pixel matrix to test the internal scaler along with proper aspect setting of the display for 1:1 pixel mapping best confirmed prior to testing with the same pattern from Digital Video Essentials. This pattern has a black and white video level error and pixel mapping error. Black is about 10% above peak black and white is about 10% below peak white; this is not critical to the test. The pixel map is correct for the vertical plane, 1080 pixels, but incorrect for the horizontal plane, 1920; this is not critical for this test of 1080 vertical resolution but the 1920 burst for horizontal resolution will not show up appearing as a gray box. As a 1:1 pixel map pattern, the horizontal response should be ignored. This test applies to other pixel matrixes and 1080i content but you will likely have problems with the vertical 1080 burst and the response may vary in the five different areas of the image. The rotating bar will tell you if the 3:2 cadence is being properly detected and should show smooth motion as it spins.</p>

<p><b>Diagonal Filtering Jaggies Test</b><br />
Provides the same two tests as the DVD. The first is the single bar constantly rotating and the second is where three bars move back and forth in a narrow arc. The Guide calls this Video Reconstruction Tests, does not document the second test and both disc versions require you choose Select Individual Tests for the second jaggie test to be viewed.</p>

<p><b>Film Resolution Loss</b><br />
A SMPTE RP-133 1920X1080 <a target="_blank" href="http://www.isfforum.com/viewtopic.php?t=82">1:1 pixel mapped</a> test pattern is panned back and forth along with a horizontal pan of a football stadium. The Blu-ray version skips the stadium when choosing Play Loop. The test uses the same pattern as Video Resolution Loss containing the same errors and has the same requirements for testing. Unlike the previous test, this one can show horizontal response errors due to motion. As before, the 1920 box will be gray. Included in this pattern are video level boxes with percentage of modulation and you can see that the one pixel space between the two digits is missing implying that frequency response has been slightly extended. This means that the 1920, 960 and 480 boxes actually have slightly higher pixel counts which is why it does not pixel map and probably why the 1920 response is missing. This test still applies but due to this error it clearly is not the ultimate test it could be and who knows if a scaler might perform better with a true pixel map expecting 1920x1080. That said, the guide and disc only discuss errors in the vertical response. </p>

<p>The stadium test follows but lacks the snap of detail expected from quality capturing and 1:1 pixel mapping. It only pans right to left and one display that was tested with the SMPTE pattern had less horizontal response artifacts in that direction over the other. It's a shame the stadium was not panned in both directions like the SMPTE pattern. When the 50 yard line hits about the center of the screen there is a subtle pixel response change and a change of light output level in some areas of the scene. Two displays failed the SMPTE pattern test in clearly different ways for either the horizontal or vertical planes. I found this test one of the most difficult to analyze. </p>

<p>With either display the horizontal plane detail would drop in and out at what appeared to be the same points. The display that passed the prior test for vertical response simply had more detail while in motion and at the final still point of a few seconds. While the other display faired far better with the prior test of horizontal response, both appeared to respond equally. With a casual viewing you may be hard pressed to detect any appreciable difference in the stadium pan. If you concentrate on the horizontal response you will miss the point of this test. Concentrate on the vertical response or horizontal lines in the bleachers, light towers, field and elsewhere in the image. Those are the trees you are looking for in this forest and the horizontal response of vertical lines seems best left ignored.</p>

<p><br />
<b>HQV Benchmark Blu-ray / HD DVD on Your Player</b></p>

<p>As with the DVD version, missing from these discs is the same test material processed and mastered just like Hollywood does. A 1080p24 reference point with a proper 1080p24 display would show the viewer what correct performance looks like assisting the 1080i evaluation. It would be interesting to see how a player handles converting a Hollywood 1080p24 version of these specific test materials to the other scan rates. </p>

<p>Just like DVD, we have the same dilemma of how a player can fail these tests yet show not one sign of trouble with actual movies or calibration and test discs like Digital Video Essentials. The answer is the same; HD disc movies, for the most part, are not mastered as 1080i, they are mastered as 1080p24 and the player is designed to work with that when deriving other output scan rates. </p>

<p>Like DVD, special features still receive little to no attention and can vary from 480p all the way through 1080p24. I have even seen 480p 4:3 letterboxed content. Even though Blu-ray is an HD 16:9 destined format you just don't know what you are going to get. It doesn't end there; feature material that was captured as native 1080i is mastered as is. Most of those titles are concert, documentary features and television programming along with some indie movies with the common thread being that they were captured with HD 1080i based cameras. If the content is original 24 frame film then telecine mastering is performed at 1080p24 and that represents the majority of the Hollywood catalog. </p>

<p>Testing HD disc players is not nearly as straight forward as it was for DVD. Getting acceptable imaging out of lower resolutions (such as standard definition) is far more difficult. With DVD content you can run into some oddball content that will create very noticeable artifacts far more easily detected, even at far viewing distances. The nine cadence tests for DVD are a prime example and are not part of the HD version. With that in mind, the most critical HQV test for any HD disc viewer is proper cadence detection of the rotating bar of the Video Resolution Loss test which must pass with smooth motion and should be tested at 720p, 1080i and 1080p depending on what HD scan rates your display will accept. You may find that only 1080i provides the correct response. As for the other tests of 1080 horizontal lines testing vertical response, failure hardly means your image will stink but it does mean that 1080i content will have a loss in horizontal line detail. More than likely the failure will be due to vertical filtering which requires a deeper understanding of burst testing, what is going on and how it affects specific elements of an image. I refer you to <a target="_blank" href="http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_vertical_and_horizontal_filtering.php">HD Waveform - Vertical and Horizontal Filtering</a> for detailed information.</p>

<p>For those with a display that only accepts a 1080p60 or 1080i output you can test the player's ability to convert Hollywood 1080p24 to either of those scan rates by using the SMTPE RP-133 from Digital Video Essentials for both Blu-ray and HD DVD. In this case the pattern is properly pixel mapped in both horizontal and vertical planes but it is a still pattern and cannot test for cadence or loss of resolution due to motion. For either scan rate setting you are looking for single pixel lines in the vertical 1080 box response and horizontal 1920 box response.</p>

<p><br />
<b>Vertical Filtering - Finding the Right Recipe</b></p>

<p>If you want every last shred of detail with 1080i content then vertical filtering is unacceptable. The easiest route to overcoming that is no recipe at all, which requires a player to properly deinterlace 1080i and convert it to 1080p60 or 1080p24 for a proper response along with advanced features that would allow you to output native 1080p24 content untouched. Most 1080p displays these days provide 1:1 pixel mapping at 1080p60 and many include 1080p24 as well. More importantly, the player must do this automatically providing a convenient hands-off and worry free approach for the user. While such a player would provide the ultimate keep it simple solution for any viewer, finding one that has been designed much less reviewed for this attribute may be far more difficult. For current Blu-ray players the right recipe of display and player can achieve the exact same results and external scaling provides yet another solution.</p>

<p>The discs were tested with a Sony PS3 and Toshiba HD-A35. The PS3 will not do a thing with native 1080i content except pass it along as is; testing 1080i conversion of the player to 1080p was impossible. With a full featured 1080p display the PS3 defaults to native output of the source. I did find it curious that the HQV Blu-ray menus are native 1080p24. The Toshiba on the other hand does not support native output and follows what you have set it for; 720p60, 1080p60 or 1080p24. Set for 720p or 1080p60 the Toshiba failed to pass the vertical 1080 box. Set for 1080p24 the Toshiba completely wiped out on cadence with every one of the tests creating a strobing effect at all times. Both players passed the Digital Video Essentials SMPTE RP-133 test at 1080i and 1080p60. A <a  target="_blank" href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000</a> and <a target="_blank" href="http://www.hdtvmagazine.com/reviews/2007/07/benq_w10000_1080p_dlp_front_projector.php">BenQ W10000</a> front projector were also tested. The Panasonic passed with flying colors for the most part while the BenQ failed due to vertical filtering.</p>

<p>The combination of the Sony PS3 and the Panasonic PTAE-1000 provides optimal results. Since the Sony defaults to native output of disc content and the Panasonic passes 1080i testing along with native 1080p60 (1080p 30 frame source) or 1080p24 support you are getting full automation for the best results. Either the internal scaler of the display is deinterlacing and scaling or is getting a direct feed bypassing it providing a 1:1 pixel map instead. </p>

<p>The Toshiba HD DVD player on the other hand does not have a native mode and simply does what you tell it to. If you know the HD DVD disc has native 1080i content then even with a display like the Panasonic you will have to change the output scan rate to match the disc source; same goes with the Toshiba feeding an external scaler. While lacking automation you can get optimal results manually.</p>

<p>The BenQ is a stellar performer and nearly a reference for video standards when pixel mapped at 1080p60 or 1080p24 yet 1080i is its Achilles performance Heal. Neither player tested provides a direct solution alone. One solution requires an external scaler with advanced features that will de-interlace and scale 1080i while allowing a 1080p60, 1080p30 or 1080p24 bypass. Mated with the PS3 you would achieve simple automation.</p>

<p>Untouched native 1080p24 content from player to a 1080p24 display is quite easy to acquire and will satisfy most users. In the end it all comes down to you, your system, how you use it (viewing distance) and the importance you place on some or all of the content you are viewing. Ultimately HQV Benchmark is limited in its ability to answer all of these questions if you want the most out of every bit of content you might be feeding your system since it is limited to 1080i testing only. </p>

<p><br />
<b>HQV Benchmark on Your Display</b></p>

<p>A properly designed 480i DVD player can be used to evaluate the internal scaler of a display or external scaler using the analog component inputs. Digital video, HDMI/DVI, is typically limited to 480p but if your display accepts 480i and your player can provide it without artifacts then that would be a valid test. Simply set the output of the player to 480i. Testing S-video and composite video connections is a bit more dicey because that requires proper down conversion so while likely not the ultimate reference test signal from your player there are still things that can be learned. For cable and satellite boxes using the analog component input of your display, these tests have direct value if you are setting the box to native so NTSC is output as 480i. Since you can't test the box it won't help you determine if the box or your display is doing a better job. Unfortunately, what this disc can't test is your NTSC TV tuner. Keep in mind during your testing how some displays apply, lock or limit different video processing features based on the input type. </p>

<p>A properly designed Blu-ray or HD DVD player can be used to evaluate how the internal scaler of a display or external scaler handles 1080i content using the analog component inputs or HDMI/DVI (with the player set for 1080i output). </p>

<p><b>Conclusion</b></p>

<p>While the some of the tests represent a small portion of the available catalog, the DVD version helps those seeking videophile nirvana with any and all DVD content on the planet via a player or external scaler. All of the tests are useful for determining how your display or external scaler handles broadcast NTSC video. For player evaluation, it should be considered a secondary test to the primary test of other discs that do follow Hollywood mastering representing the vast majority of what you would rent or purchase. The Blu-ray and HD DVD version provides a battery of tests to evaluate how your display or an external scaler handles HDTV 1080i content from Blu-ray disc or broadcast HDTV. If Blu-ray Hollywood movies are your main concern then just like DVD it should be considered a secondary test to the primary test of other discs that provide a native 1080p24 response representing the majority of Hollywood features. Either version of HQV Benchmark is unique in what it brings to the videophile table for display and player evaluation. With those limitations understood, the HQV Benchmark series is a unique evaluation tool that deserves a place in the videophiles toolbox.</p>

<p><b>Test Results of Past Products </b></p>

<p><a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33688#33688">Panasonic PT-AE1000U LCD Front Projector </a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33689#33689">BenQ W10000 DLP Front Projector</a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33709#33709">Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33710#33710">OPPO DV-981HD Upconverting SD DVD Player</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>June 19, 2008 12:24 PM</b>
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
			<?=getComments(1406)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 1406)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/06/hqv-benchmark-bluray-dvd-and-hd-dvd.php" type="text/javascript" charset="utf-8"></script>
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