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
		AND e.entry_id = 3355";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3355 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3355 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3355";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/10/texas-instruments-announces-new-low-power-ip-camera-reference-design-providing-h264-main-profile-1080p-at-30-frames-per-second-and-30-performance-boost.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3355";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost" />
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
	<title>HDTV Magazine - Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost</title>
	<meta name="keywords" content="reference design, camera reference, texas instruments, main profile, application software, design, reference, video, camera, customers, fps, new, instruments, processing, texas, power, software, ipnc, includes, support, complete, performance, isp, system, encryption" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/2009-10-29_texas_instruments.jpg&quot; alt=&quot;Texas Instruments&quot; height=&quot;34&quot; width=&quot;81&quot; class=&quot;keyimg&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Texas Instruments Incorporated (TI) (NYSE:TXN) today announced a new Internet Protocol (IP) camera reference design that provides low power, high definition (HD) video processing for the video surveillance market. The DM368IPNC-MT5 IP camera reference design with H.264 main profile 1080p at 30 frames per second (fps) offers industry-leading compression in a full HD solution, with the complete camera utilizing only three Watts. Based on a new DaVinci(TM) video processor, the IP camera reference design provides 30 percent more host processing performance over the previous generation. This overall performance boost allows the camera to support 720p at 60 fps for multiple video formats including H.264 and MPEG-4, as well as MJPEG at five Megapixels (MP) at 15 fps. It also supports multi-streaming video processing at various frame rates (&lt;a target=&quot;_blank&quot; href=&quot;http://www.ti.com/1080p-ipcamrd-prprod/&quot;&gt;www.ti.com/1080p-ipcamrd-prprod&lt;/a&gt;).

Additionally, the IP camera reference design includes..." />
	<meta name="title" content="Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/10/texas-instruments-announces-new-low-power-ip-camera-reference-design-providing-h264-main-profile-1080p-at-30-frames-per-second-and-30-performance-boost.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/2009-10-29_texas_instruments.jpg&quot; alt=&quot;Texas Instruments&quot; height=&quot;34&quot; width=&quot;81&quot; class=&quot;keyimg&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Texas Instruments Incorporated (TI) (NYSE:TXN) today announced a new Internet Protocol (IP) camera reference design that provides low power, high definition (HD) video processing for the video surveillance market. The DM368IPNC-MT5 IP camera reference design with H.264 main profile 1080p at 30 frames per second (fps) offers industry-leading compression in a full HD solution, with the complete camera utilizing only three Watts. Based on a new DaVinci(TM) video processor, the IP camera reference design provides 30 percent more host processing performance over the previous generation. This overall performance boost allows the camera to support 720p at 60 fps for multiple video formats including H.264 and MPEG-4, as well as MJPEG at five Megapixels (MP) at 15 fps. It also supports multi-streaming video processing at various frame rates (&lt;a target=&quot;_blank&quot; href=&quot;http://www.ti.com/1080p-ipcamrd-prprod/&quot;&gt;www.ti.com/1080p-ipcamrd-prprod&lt;/a&gt;).

Additionally, the IP camera reference design includes..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3355', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/10/texas-instruments-announces-new-low-power-ip-camera-reference-design-providing-h264-main-profile-1080p-at-30-frames-per-second-and-30-performance-boost.php">Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 29, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=286&category=High Definition Production">High Definition Production</a></b>, <b><a href="/category.php?id=272&category=Technology">Technology</a></b>
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
				<p class="prtitle">Texas Instruments announces new low power IP camera reference design providing H.264 main profile 1080p at 30 frames per second and 30% performance boost</p>

<center><i>New reference design provides full HD video with advanced software for Image Signal Processing tuning and encryption</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/2009-10-29_texas_instruments.jpg" alt="Texas Instruments" height="67" width="192" class="keyimg"><strong>HOUSTON, Oct. 29 /PRNewswire/ -- </strong>Texas Instruments Incorporated (TI) (NYSE:TXN) today announced a new Internet Protocol (IP) camera reference design that provides low power, high definition (HD) video processing for the video surveillance market. The DM368IPNC-MT5 IP camera reference design with H.264 main profile 1080p at 30 frames per second (fps) offers industry-leading compression in a full HD solution, with the complete camera utilizing only three Watts. Based on a new DaVinci(TM) video processor, the IP camera reference design provides 30 percent more host processing performance over the previous generation. This overall performance boost allows the camera to support 720p at 60 fps for multiple video formats including H.264 and MPEG-4, as well as MJPEG at five Megapixels (MP) at 15 fps. It also supports multi-streaming video processing at various frame rates (<a target="_blank" href="http://www.ti.com/1080p-ipcamrd-prprod/">www.ti.com/1080p-ipcamrd-prprod</a>).</p>

<p>Additionally, the IP camera reference design includes a complete Linux application software package to help customers differentiate their end camera. This application software package includes an Image Signal Processing (ISP) Tuning Tool 1.0, a hardware-accelerated AES encryption module and support for the Physical Security Interoperability Alliance standard (PSIA, <a target="_blank" href="http://www.psialliance.org/">www.psialliance.org</a>) for easier adoption and deployment. When combined, this enables customers to quickly create cost-sensitive products with full HD video, such as IP cameras or IP modules for closed-circuit TV cameras, in greatly reduced development time.</p>

<p>DM368IPNC-MT5 IP camera reference design key features and benefits:<code><br />
  --  Flexibility to support a variety of video formats, including:<br />
      --  H.264 main profile 1080p at 30 fps or 720p at 60 fps<br />
      --  MPEG-4 up to 720p at 60 fps<br />
      --  MJPEG at 5 MP at 15 fps<br />
  --  New DaVinci video processor provides 30 percent more ARM host processing performance over previous generations, offering more<br />
      headroom for differentiation and video analytics.<br />
  --  Power-efficient reference design utilizes only three Watts and<br />
      includes a TI Power over Ethernet (PoE) solution, the TPS23753.<br />
  --  On-chip, fifth generation of TI's ISP solution eliminates the need for<br />
      an external ISP or the purchase of expensive optics to achieve<br />
      high-quality images.  Also, customers do not have to create their own<br />
      custom algorithms, which are time and resource intensive.<br />
  --  Software implementations on the ISP provide video stabilization, face<br />
      detection, noise filtering, auto white balance, auto focus, auto<br />
      exposure and edge enhancement, as well as other video processing<br />
      features for quality enhancements and image improvements.<br />
  --  The reference design includes a full complement of analog ICs, ranging<br />
      from power management to interface devices from TI, providing a<br />
      complete system solution to reduce development time.</p>

<p>  --  Developed with and available from Appro Photoelectron Inc.</p>

<p>  Reference design includes complete Linux application software:<br />
</code></p>

<p>With the purchase of the IP camera reference design, customers will have access to a complete, royalty-free Linux application software package that includes:<code></p>

<p>  --  ISP Tuning Tool 1.0, which can be utilized to tune the existing sensor<br />
      that is included with the reference design or with customers' own<br />
      developed sensor board.<br />
  --  PSIA 1.0 support that guarantees connectivity with PSIA-compliant<br />
      systems, thereby providing easier implementation and plug-n-play<br />
      connectivity.<br />
  --  AES encryption offers greater protection and privacy for video<br />
      streams, enhancing the security of the users' end system.  Customers<br />
      also benefit from a more efficient system as the encryption occurs on<br />
      a separate hardware accelerator.<br />
  --  Global dynamic range enhancement (GDRE) allows users to bring out<br />
      details in the shadows of the video without washing out the<br />
      highlights, a critical feature for video surveillance.  The GDRE also<br />
      compensates for the reduced dynamic range often found in lower cost<br />
      CMOS sensors, allowing customers to develop a more cost-effective<br />
      system.</p>

<p>  --  Linux source code for customer-specific customization and<br />
      differentiation.<br />
</code></p>

<p><br />
<strong>Pricing and availability:</strong></p>

<p>Order entry is currently open for the DM368IPNC-MT5 IP camera reference design, which is available for USD $995 from Appro Photoelectron Inc. at <a target="_blank" href="http://www.ti.com/1080p-ipcamrd-prprod/">www.ti.com/1080p-ipcamrd-prprod</a>.</p>

<p>See a demonstration of the DM368IPNC-MT5 IP camera reference design at the Global Sourcing Conference on Public Safety and Security (CPSE, <a target="_blank" href="http://www.cpse.com.cn/en//">http://www.cpse.com.cn/en/</a>), November 1 - 4. The conference is at the Shenzhen Convention and Exhibition Center (Fuhua 3rd Road, Futian Central District, Shenzhen, China), and the TI booth can be found in Hall 1, Overseas Area A, B218 - B223.</p>

<p>Find out more about the DM368IPNC-MT5 IP camera reference design and TI's video capabilities:</p>

<p>  --  IP camera reference design, DM368IPNC-MT5:<br />
      <a target="_blank" href="http://www.ti.com/1080p-ipcamrd-prprod/">www.ti.com/1080p-ipcamrd-prprod</a></p>

<p>  --  TI E2E Community and support: <a target="_blank" href="http://www.ti.com/1080p-ipcamrd-prsupport/">www.ti.com/1080p-ipcamrd-prsupport</a></p>

<p><br />
<strong>About Texas Instruments</strong></p>

<p>Texas Instruments (NYSE:TXN) helps customers solve problems and develop new electronics that make the world smarter, healthier, safer, greener and more fun. A global semiconductor company, TI innovates through design, sales and manufacturing operations in more than 30 countries. For more information, go to <a target="_blank" href="http://www.ti.com/">www.ti.com</a>.</p>

<p><br />
<strong>Trademarks</strong></p>

<p>DaVinci is a trademark of Texas Instruments. All trademarks are the property of their respective owners.</p>

<p>Source: Texas Instruments Incorporated</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 29, 2009  6:00 AM</b>
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
			<?=getComments(3355)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3355)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/10/texas-instruments-announces-new-low-power-ip-camera-reference-design-providing-h264-main-profile-1080p-at-30-frames-per-second-and-30-performance-boost.php" type="text/javascript" charset="utf-8"></script>
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