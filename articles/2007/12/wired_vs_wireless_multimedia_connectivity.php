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
		AND e.entry_id = 812";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Chris Russell" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 812 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Chris Russell'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Chris Russell" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 812 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 812";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 812";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Wired vs. Wireless Multimedia Connectivity" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Wired vs. Wireless Multimedia Connectivity" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Wired vs. Wireless Multimedia Connectivity" />
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
	<title>HDTV Magazine - Wired vs. Wireless Multimedia Connectivity</title>
	<meta name="keywords" content="data rate, data rates, high definition, effective data, ghz band, data, rate, bandwidth, content, high, rates, ghz, band, required, wireless, colour, copper, gbps, scheme, available, used, increasing, bits, viterbi, signal" />
	<meta name="description" content="The advent of high definition content with Blu-Ray and HD DVD, HD broadcast and stunning real time HD console gaming continue to drive the bandwidth requirements at a dramatic pace. The most recent specifications for supporting current and future HD content requires a bandwidth as high as 10.8Gbps (in the case of Display Port) and 10.2Gbps (in the case of HDMI). The first part of this article examines the driving forces behind the requirement for ever-increasing bandwidth. The second part of the article compares the bandwidth available with wired solutions and various wireless bands including UWB and 60GHz bands.

The key factors driving the requirement for increased bandwidth are..." />
	<meta name="title" content="Wired vs. Wireless Multimedia Connectivity" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Wired vs. Wireless Multimedia Connectivity" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The advent of high definition content with Blu-Ray and HD DVD, HD broadcast and stunning real time HD console gaming continue to drive the bandwidth requirements at a dramatic pace. The most recent specifications for supporting current and future HD content requires a bandwidth as high as 10.8Gbps (in the case of Display Port) and 10.2Gbps (in the case of HDMI). The first part of this article examines the driving forces behind the requirement for ever-increasing bandwidth. The second part of the article compares the bandwidth available with wired solutions and various wireless bands including UWB and 60GHz bands.

The key factors driving the requirement for increased bandwidth are..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=812', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php">Wired vs. Wireless Multimedia Connectivity</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Chris Russell</b> on <b>December 11, 2007</b>
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
				<p>This article examines the facts in the debate surrounding Wired versus Wireless Multimedia Connectivity.</p> <p><br><b>1. Introduction.</b></p> <p>The methods used to deploy High Definition video content are analysed in detail to determine the likely outcome in the ongoing Wireless Versus Wired debate.</p> <p>The article examines the outlook for wireless versus wired technologies from the perspective of achievable data rate, security, compression, interoperability and picture quality.</p> <p>The advent of high definition content with Blu-Ray and HD DVD, HD broadcast and stunning real time HD console gaming continue to drive the bandwidth requirements at a dramatic pace. The most recent specifications for supporting current and future HD content requires a bandwidth as high as 10.8Gbps (in the case of Display Port) and 10.2Gbps (in the case of HDMI). The first part of this article examines the driving forces behind the requirement for ever-increasing bandwidth. The second part of the article compares the bandwidth available with wired solutions and various wireless bands including UWB and 60GHz bands.</p> <p><br><b>2. Key Factors Driving Bandwidth Requirement.</b></p> <p>The key factors driving the requirement for increased bandwidth are:</p> <p>1) Increasing screen resolution and frame rates<br>2) Richer colour support<br>3) The continued requirement for the transmission of protected content in an uncompressed format; and<br>4) The migration of equipment such as HDTV's and consoles to display in progressive scan mode.</p> <p>2.1. Screen Resolution:<br>The HDTV market will evolve rapidly beyond the 720p and 1080p formats supported today to 1440p and beyond in the not-too-distant future. The requirement to drive even larger screen sizes is driven by the availability of display technology in the PC space. Current state of the art display technology is at WQXGA with 2560×1600 resolution. VESA has already defined a WQUXGA standard supporting resolutions of 3840 x 2400. Increasing screen resolution will continue to drive bandwidth requirements into the future. As end-users become aware of the dramatic visual difference experienced with progressive scan they will demand this as standard. Progressive scan doubles the requirement for channel bandwidth.</p> <p>2.2. Increased Frame rate<br>Current frame rates are 25, 50 and 60 Hz. This has been driven historically by the TV broadcast market. The main driver for increased frame rate comes from the console and PC gaming market where the HD content is being created on the fly. The benefit of moving from 60Hz to 120Hz frame rate whilst increasing screen resolution and colour depth has a direct impact on game play. Increasing frame rate allows the game player to drive faster without loosing frames or scene quality. New processor architectures such as the IBM's Cell are now fast enough to perform the required geometry and rendering calculations required to refresh the scenery at 120Hz. This should evolve as a standard feature as the developer community authors the required content to showcase the new levels of detail possible.</p> <p>2.3. Colour Depth.<br>Improvements in display technology combined with the constant demand for improving the end-user experience has driven the requirement to increase the number of colours displayed. 24 bit colour allows 8 bits to render the individual RGB components on an LCD panel. By increasing the number of colours to 36 or 48 bits there are now 12 or 16 bits available to render individual RGB components. This enables a dramatic increase in the quality level achieved for many typical movie or game scenes. Using 12 bits to display a clear blue sky allows a more realistic picture to be rendered, with over 2000 levels of blue available. The same scene rendered with 8 bits of blue allows only 256 blue levels which will cause colour banding. Visually the eye can detect the different shades of blue. With 36 or 48 bit colour depth the scene appears as a continuous range of blue which is much more pleasing to the eye. This greater colour depth also has a significant impact on the contrast ratio. Hollywood is already using 36bit colour and providing support for this in consumer electronics equipment allows this to be experienced in the home.</p> <p>2.4. Security &amp; Compression.<br>High Definition content is stored and broadcast in compressed format and decompressed by the DVD player or STB. When the outputs from these devices were Composite, S Video or SCART there weren't many concerns about security because there was no access externally to the native digital content that could be used for illegal duplication. The fact that the connection between the DVD, STB and HDTV is now digital raises security concerns as this digital content is available externally. This led the industry to introduce content protection for digital content, and movies are now encrypted using the HDCP copy protection scheme. However, the second piece of the puzzle in content protection is that if the content is transmitted in compressed format, there is an increased risk of it being captured and decrypted off line for subsequent distribution. Compression makes life easier for the pirate industry. Therefore Hollywood has a requirement that the digital content when exposed in this manner is in an uncompressed format. The sheer size of the content makes it impractical to store for offline decryption. For example a 2 hour 1080p movie would require 3 Terabits of exceptionally high-speed storage. The use of an uncompressed format clearly has a direct impact on the required data rate.</p> <p><br><b>3. Effect on Bandwidth Requirements.</b></p> <p>The overall effect of increasing screen resolution, frame rate, colour depth, the lack of compression and the drive to progressive scan as a standard feature is to drive the required bandwidth for connectivity to new levels. The latest HDMI specification, Version 1.3, has increased the link bandwidth from the previous 4.95Gbps to 10.2Gbps and the VESA Display Port 1.0 has chosen 10.8Gbps for the data rate required to satisfy the increasing need for greater bandwidth. These two standards will provide sufficient bandwidth for the next few years but will continue to be upgraded to cope with the requirements for further bandwidth.</p> <p><br><b>4. Copper Bandwidth.</b></p> <p>Data rates for copper cable links have increased dramatically over the years from very low data rates to the sophisticated SerDes implementations running into the multiple Gigabits per second. In some specific applications copper is likely to replace multi-Gig optical links. To achieve these high data rates with copper two main approaches have been taken to date. Industry bodies such as HDMI and VESA's DisplayPort use Single Data Rate (SDR) transmission with multiple data pairs in a single low-cost cable. Data rates of 10.2Gbps and 10.8Gbps are achieved with HDMI and Display Port respectively. Other copper-based standards such as Infiniband have taken this a step further with the use of Double Data Rate (DDR) and Quad Data Rate (QDR) communications to scale up the effective data rates for each pair within the cable. Fig. 1 below shows the achievable data rates for 1, 4 and 12 lane Infiniband when SRD, DDR and QDR techniques are used.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig1.gif"><br>Fig.1 Data rates achievable with SDR, QDR and QDR Techniques<br></center> <p></p> <p>This performance is achieved with the use of an NRZ encoding scheme used in conjunction with QDR and DDR techniques. These effective data rates can be increased by a factor of two and even further with multi-level signalling schemes such as PAM4 or PAM12. Instead of the transmitted data simply being a one or a zero, multiple signal levels are allowed. The effective data rates are thus scaled up again by a factor of 4 or 12 from the rates in Figure 1.</p> <p>Almost all of the copper transmission schemes discussed are capable of comfortably and cost-effectively delivering the bandwidth necessary to support high-definition video distribution.</p> <p><br><b>5. Wireless Bandwidth:</b></p> <p>Looking at the wireless world, three wireless bands are examined to determine available data rates for HD content distribution. These are 5GHz, UWB and 60GHz bands. The attainable data rates for these bands is dependent on numerous variables including:<br><br> <ul> <li>Signal to noise ratio <li>Modulation scheme <li>Viterbi encoding rate</li></ul> <p></p> <p>The signal to noise ratio is dependent on the background noise level and the allowable signal power transmitted within the frequency band in question. The modulation scheme determines the number of bits per Hz achievable in the band and the Viterbi encoding rate is a measure of the amount of redundant data required to enable the receiver to correct transmission errors. The lower the signal to noise level in a given band the more redundant data is required to achieve error correction.</p> <p>To calculate the throughput in a given band the available spectral bandwidth (allocated by FCC, EU etc) is multiplied by the number of bits per Hz transmitted. This gives a theoretical data rate in the absence of noise. This effective data rate is reduced to account for the Viterbi encoding scheme required for error correction.</p> <p>A 64 bit Quadrature Amplitude Modulation (QAM64) is used in the three cases examined. This enables 6 bits per Hz to be achieved. This is the maximum possible order QAM practical in these bands.</p> <p>This data rate is reduced by the Viterbi encoding rate. For example with a 1/3 Viterbi rate encoder the effective data rate is reduced by a factor of 3 with the additional of 66% redundant data to the transmission. As the signal to noise ratio increases the order of the Viterbi encoder is reduced. In the 60GHz band greater transmit power is available so a 3/4 rate Viterbi scheme is possible. The amount of redundant data required for error correction is reduced to 25% resulting in a reduction of the effective data rate by 25%. This calculation yields the maximum data rate possible assuming no other transmitter is in the vicinity. The detailed computations for each band are discussed below.</p> <p><br><b>5.1. 5GHz Band:</b></p> <p>This band has a high allocated transmit power because of the relatively low 20MHz bandwidth allocated to it as shown in Fig 1.</p> <p>The signal to noise ratio is high and a QAM64 is used for transmission. The data stream is processed using a 1/3 rate Viterbi encoding scheme for error correcting.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig2.gif"><br>Fig 1: Allocated Spectrum at 5GHz<br></center> <p></p> <p>QAM64 allows the simultaneous transmission of 6 bits per Hz so the total theoretical bandwidth is calculated as follows.</p> <p> <center>Bandwidth available 20MHz x 6 X 1/3 = 40Mbps</center> <p></p> <p>This assumes one transmit receive path. In the likely event there are multiple transmit receivers in operation this will be reduced by the number of channels in operation. This is achieved with time or frequency multiplexing. Two channels in operation will result of a halving to 20Mbps being available for each channel.</p> <p>This band clearly can not play a major role in the transmission of high definition video content given it's extremely limited channel bandwidth.</p> <p><br><b>5.2. Ultra Wide Band:</b></p> <p>UWB has a very low transmit power allocation due to the wide 1.3GHz spectrum allocated, and a 64 bit QAM scheme is possible as in the narrower 5GHz band above so 6 bits per Hz are transmitted. The carrier frequency can be between 5 to 10GHz as shown in Fig 2 below.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig3.gif"><br>Fig 2: Allocated Spectrum for Ultra Wide Band<br></center> <p></p> <p>A 1/3 rate Viterbi encoding scheme is required and the total theoretical bandwidth is thus:</p> <p> <center>1.3GHz X 6 X 1/3 = 2.6Gbps</center> <p></p> <p>Again this is for the case where one channel is in operation. The data rate is reduced significantly when multiple channels are in operation as would be the case if this technology was widely used.</p> <p>However even at 2.6Gbps data rate there is insufficient bandwidth to distribute an uncompressed 1080p movie with 24 bit colour.</p> <p><br><b>5.3. 60GHz Band</b></p> <p>The 60GHz band allows for a relatively high power transmission given the relatively uncluttered spectrum at this frequency. The allocated spectrum is 1.6GHz as shown in Fig 3. The field strength attenuation with distance from the antenna is exceptionally high at this frequency so directional antennas are required to ensure the maximum signal strength is directed at the receiver.</p> <p> <center><br><img alt="" src="/images/articles/redmere/fig4.gif"><br>Fig 4: Allocated Spectrum for 60GHz Band<br></center> <p></p> <p>This high signal strength enables the use of a 3/4 rate Viterbi encoding scheme and a QAM64 modulation scheme is used to achieve the effective data rate as follows.</p> <p> <center>1.6GHz X 6 X 3/4 = 7.2Gbps</center> <p></p> <p>The use of the directional antennas should allow multiple channels to be active without reducing the available bandwidth as in UWB. Each channel is spatially separated so the need for time or frequency multiplexing is no longer required.</p> <p>This clearly holds more promise in high definition video applications than UWB as the raw bandwidth is significantly higher. However, significant investment will be required by the semiconductor industry to bring the low-cost, low-power CMOS technologies to market which enable these solutions.</p> <p>There are disadvantages in that the antennas need to be aligned so the transmitter knows the location of the receiver. But at the 7.2Gbps maximum achievable bandwidth this delivers, it cannot compete with copper to keep up with the increasing demands from end-users and content developers. The bandwidth may be suitable to transmit lower resolutions of video with reduced colour depths and refresh rates, or video streams which compress the content. However, one significant application for the 60GHz band is to use it in a "Kiosk" mode where the end-user can purchase content in a store and download it extremely rapidly into a portable media player.</p> <p>"Nevertheless, the clear mobility advantages offered by wireless for the low end of high-definition media connectivity has generated a lot of interest, with multiple wireless platforms jostling for position. Despite the physical data-rate limits of the channel as discussed above, Wireless HD solutions abound. In pursuing this nirvana, a variety of schemes are in use, such as only supporting lower screen resolutions, colour depths and refresh rates, or the use of interlaced as opposed to progressive scan. Compression (to varying degrees) has also been used, in conflict with copy protection requirements and raising the issue of interoperability given the wide variety of codecs available for compression and decompression. In some wireless schemes, while high-resolution video is transmitted, the paired receivers ignore channel loss and only receive lower resolutions."</p> <p><br><b>6. Summary</b></p> <p>It is clear from the analysis of copper and wireless technologies that there is simply nothing to compete with the cost-effectiveness and raw data rates achievable with copper technologies. Wireless cannot be beaten for flexibility and mobility, but not where full rate, content-protected, uncompromised visual quality is a requirement - here copper has no match.</p> <p>Industry standards such as HDMI and Display Port are achieving the data rates required for the foreseeable future. The technologies used in these copper-based standards have significant room for further enhancement as can be seen from the examples of PAM encoding, DDR and QDR technologies already deployed in other areas. Wireless on the other hand will be close to the technology limit at 60GHz and is coming up short on the data rate and cost requirements for HD content distribution</p> <p><br><b>About the Author - Chris Russell</b></p> <p>Chris Russell is Director of Business Development at RedMere Technology based in Dublin, Ireland. He was previously Vice President of Sales and Marketing at OMI a successful Irish semiconductor equipment start-up acquired by a major US player. Prior to that, he was Director of Worldwide Business Development at Parthus Technologies. He spent ten years overseas including six years in Silicon Valley in various senior sales and marketing positions at Chips and Technologies, National Semiconductor and Cirrus Logic. He was also involved in a successful start up 3D graphics company that was subsequently acquired by Cirrus Logic. Chris has a primary qualification in Electronic Engineering from University College Dublin.</p> <p>Chris Russell [chris.russell@redmere.com]</p> <p>RedMere Technology is a privately-held fabless semiconductor company providing highly innovative communications solutions for mainstream consumer multimedia, PC and storage markets.</p> <p>RedMere's unique <a href="/cgi-bin.cgi?http://www.redmere.com/content/view/39/65/">MagnifEye&amp;trade;</a> signal processing technology introduces a step-change in the performance of high definition media and storage connectivity solutions, targeting applications such as High-definition TVs, Multimedia PCs, High-resolution Monitors, Personal Video Recorders and AV Receivers.</p> <p><a href="/cgi-bin.cgi?http://www.redmere.com/content/view/39/65/">MagnifEye&amp;trade;</a> offers RedMere's customers greater flexibility in system design, reducing deployment costs and increasing reliability of multimedia applications across the industry</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Chris Russell</b>, <b>December 11, 2007  9:19 AM</b>
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
			<?=getComments(812)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Chris Russell', 812)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Chris Russell</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/12/wired-vs-wireless-multimedia-connectivity.php" type="text/javascript" charset="utf-8"></script>
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