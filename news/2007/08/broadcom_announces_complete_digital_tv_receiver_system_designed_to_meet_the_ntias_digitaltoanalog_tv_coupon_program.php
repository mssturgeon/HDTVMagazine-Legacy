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
		AND e.entry_id = 673";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 673 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 673 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 673";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/08/broadcom-announces-complete-digital-tv-receiver-system-designed-to-meet-the-ntias-digitaltoanalog-tv-coupon-program.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 673";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA\'s Digital-to-Analog TV Coupon Program" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA\'s Digital-to-Analog TV Coupon Program" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA\'s Digital-to-Analog TV Coupon Program" />
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
	<title>HDTV Magazine - Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program</title>
	<meta name="keywords" content="digital analog, converter boxes, our products, digital television, our ability, broadcom, our, digital, analog, products, communications, bcm, converter, television, receiver, ntia, atsc, program, boxes, system, video, broadband, statements, ability, product" />
	<meta name="description" content="Broadcom Corporation (NASDAQ:BRCM) , a global leader in semiconductors for wired and wireless communications, today announced a complete digital television receiver system targeted at the National Telecommunications and Information Administration's (NTIA's) digital-to-analog converter box program. The program, which is part of a Federal Communications Commission (FCC) initiative, includes a budget of $1.5 billion that will be used to assist U.S. households in making an affordable transition from existing analog televisions to digital by providing coupons to households to defray the cost of digital TV converter boxes. Broadcom has introduced a turnkey digital television-on-chip (TVoC) and associated software to enable these digital-to-analog converter boxes, extending the lives of analog-only TVs.

As written in the Department of Commerce's Federal Register..." />
	<meta name="title" content="Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/08/broadcom-announces-complete-digital-tv-receiver-system-designed-to-meet-the-ntias-digitaltoanalog-tv-coupon-program.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Broadcom Corporation (NASDAQ:BRCM) , a global leader in semiconductors for wired and wireless communications, today announced a complete digital television receiver system targeted at the National Telecommunications and Information Administration's (NTIA's) digital-to-analog converter box program. The program, which is part of a Federal Communications Commission (FCC) initiative, includes a budget of $1.5 billion that will be used to assist U.S. households in making an affordable transition from existing analog televisions to digital by providing coupons to households to defray the cost of digital TV converter boxes. Broadcom has introduced a turnkey digital television-on-chip (TVoC) and associated software to enable these digital-to-analog converter boxes, extending the lives of analog-only TVs.

As written in the Department of Commerce's Federal Register..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=673', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/08/broadcom-announces-complete-digital-tv-receiver-system-designed-to-meet-the-ntias-digitaltoanalog-tv-coupon-program.php">Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 20, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=268&category=Politics & Policy">Politics & Policy</a></b>
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
				<p class="prtitle">Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program</p>

<center><i>New Digital Television-on-Chip Enables OEMs to Deliver NTIA Coupon-Eligible Converter Boxes that Extend the Lives of Millions of Analog-Only Televisions in the U.S.</i></center><br />
<br />

<p><B>IRVINE, Calif., Aug. 20 /PRNewswire-FirstCall/</B> -- Broadcom Corporation (NASDAQ:BRCM) , a global leader in semiconductors for wired and wireless communications, today announced a complete digital television receiver system targeted at the National Telecommunications and Information Administration's (NTIA's) digital-to-analog converter box program. The program, which is part of a Federal Communications Commission (FCC) initiative, includes a budget of $1.5 billion that will be used to assist U.S. households in making an affordable transition from existing analog televisions to digital by providing coupons to households to defray the cost of digital TV converter boxes. Broadcom has introduced a turnkey digital television-on-chip (TVoC) and associated software to enable these digital-to-analog converter boxes, extending the lives of analog-only TVs.</p>

<p>As written in the Department of Commerce's Federal Register, dated March 15, 2007, the Digital Television Transition and Public Safety Act of 2005 (the Act) directs the FCC to require full-power television stations to cease analog broadcasting and to only broadcast digital transmissions after February 17, 2009. As a result, televisions that receive over-the-air broadcasts through rabbit ear antennas will no longer work after February 17, 2009. At that point, the analog television spectrum will be freed up for public safety initiatives and will be auctioned off by the U.S. government for such applications as wireless networking.</p>

<p>For those consumers who want to continue receiving broadcast programming over-the-air using analog-only televisions not connected to cable or satellite service, a small digital-to-analog converter box will be required. These converter boxes are expected to be available in early 2008, at which time the NTIA has been authorized to create a digital-to-analog converter box assistance program for eligible households. As a result, the NTIA will provide two $40 discount coupons per household that consumers can redeem directly from retailers for these NTIA-qualified digital converter boxes. There is a total of 33.5 million coupons budgeted for the program, which begins January 1, 2008 and concludes on March 31, 2009.</p>

<p>Announced today is the Broadcom(R) BCM3543 ATSC (Advanced Television Systems Committee) receiver designed to receive ATSC high definition television (HDTV) broadcasts and convert them to NTSC (National Television Systems Committee) signals. Utilizing 65 nanometer process technology, the BCM3543 is a highly integrated, low power digital TVoC receiver that provides superior ATSC signal reception targeted at the NTIA's coupon-eligible "analog switch-off" converter box program. The BCM3543 features on-chip support to convert all ATSC standard and high definition inputs to 480i output formats for display on analog TVs.</p>

<p>"Broadcom is very excited to support the transition from analog-to-digital broadcasting by enabling our OEM partners to produce and quickly deliver digital-to-analog converter boxes as part of the NTIA coupon program," said Dan Marotta, Senior Vice President and General Manager of Broadcom's Broadband Communications Business Group. "With the introduction of the BCM3543 ATSC receiver, Broadcom continues its leadership in the digital television market and demonstrates its ability to efficiently integrate a wide variety of technologies required to meet the demands of the market."</p>

<p><br />
<B>Product Information</B></p>

<p>The BCM3543 converts ATSC signals to NTSC for displaying on analog TVs and features key system functionality that includes Channel 3 or 4 radio frequency modulated output, EIA/CEA-909 smart antenna support, and keypad and remote control support. This extensive level of support reduces the complexity and system cost associated with the design of NTIA-eligible converter boxes. The BCM3543 is also supported by an extensive hardware and software reference design that simplifies and minimizes the design and development process for Broadcom's OEM partners.</p>

<p>Compatible with an ATSC antenna design available exclusively from Broadcom, the BCM3543's antenna provides a high performance indoor solution that only requires a single cable interface, eliminating difficult and costly outdoor installations. The antenna control is seamlessly integrated into the BCM3543's user interface eliminating the need for consumers to have to manually adjust the antenna position in order to receive optimal signal reception.</p>

<p> The key features of the BCM3543 ATSC receiver include:</p>

<p> -- An integrated 8/16-VSB digital terrestrial receiver<br />
 -- An EIA/CEA 909-compliant smart antenna controller<br />
 -- An ATSC-compliant, all-format MP@HL MPEG-2 high definition video<br />
 decoder<br />
 -- A Channel 3-4 modulator<br />
 -- A Dolby(R) Digital and MPEG audio decoder<br />
 -- High quality video scaling<br />
 -- A picture enhancement processor<br />
 -- A 200 MHz MIPS32(R) CPU<br />
 -- A 32-bit 200 MHz DDR-DRAM controller</p>

<p><br />
<B>65 Nanometer Process Technology</B></p>

<p>The 65 nanometer (nm) process is the most advanced lithographic node for manufacturing semiconductors in large volumes today and provides significant benefits over 90 nm and 130 nm processes by enabling lower power consumption, smaller size and higher levels of integration. For Broadcom, the move to 65 nanometer process technology is changing the competitive landscape because of the breadth and depth of the communications intellectual property the company possesses. Without a broad portfolio of market-leading solutions to integrate, competitors are not able to take full advantage of the benefits that these next-generation processes provide. Broadcom's vast communications intellectual property for transporting voice, video and data at home, work and on-the-go are helping to offer customers and service providers with a truly seamless communications experience for end users worldwide.</p>

<p><br />
<B>Availability and Pricing</B></p>

<p>The 65 nanometer BCM3543 ATSC receiver is now sampling to early access customers. Pricing is available upon request.</p>

<p><br />
<B>About Broadcom's Broadband Communications Group</B></p>

<p>Broadcom offers manufacturers a range of broadband communications and consumer electronics SoC's that enable voice, video and data services over residential wired and wireless networks. These highly integrated silicon solutions continue to enable the most advanced system solutions on the market, which include digital cable, satellite and IP set-top boxes and media servers, broadband modems and residential gateways, high definition and digital televisions, HD DVD and Blu-ray Disc(TM) players, DVD recorders and personal video recorders.</p>

<p><br />
<B>About Broadcom</B></p>

<p>Broadcom Corporation is a major technology innovator and global leader in semiconductors for wired and wireless communications. Broadcom products enable the delivery of voice, video, data and multimedia to and throughout the home, the office and the mobile environment. We provide the industry's broadest portfolio of state-of-the-art, system-on-a-chip and software solutions to manufacturers of computing and networking equipment, digital entertainment and broadband access products, and mobile devices. These solutions support our core mission: Connecting everything(R).</p>

<p>Broadcom is one of the world's largest fabless semiconductor companies, with 2006 revenue of $3.67 billion, and holds over 2,200 U.S. and 900 foreign patents, more than 6,600 additional pending patent applications, and one of the broadest intellectual property portfolios addressing both wired and wireless transmission of voice, video and data. Broadcom is headquartered in Irvine, Calif., and has offices and research facilities in North America, Asia and Europe. Broadcom may be contacted at +1.949.926.5900 or at http://www.broadcom.com/.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995:</p>

<p>All statements included or incorporated by reference in this release, other than statements or characterizations of historical fact, are forward- looking statements. These forward-looking statements are based on our current expectations, estimates and projections about our industry and business, management's beliefs, and certain assumptions made by us, all of which are subject to change. Forward-looking statements can often be identified by words such as "anticipates," "expects," "intends," "plans," "predicts," "believes," "seeks," "estimates," "may," "will," "should," "would," "could," "potential," "continue," "ongoing," similar expressions, and variations or negatives of these words. These forward-looking statements are not guarantees of future results and are subject to risks, uncertainties and assumptions that could cause our actual results to differ materially and adversely from those expressed in any forward-looking statement.</p>

<p>Important factors that may cause such a difference for Broadcom in connection with BCM3543 digital TV receiver products include, but are not limited to, general economic and political conditions and specific conditions in the markets we address, including the volatility in the technology sector and semiconductor industry, trends in the broadband communications markets in various geographic regions, including seasonality in sales of consumer products into which our products are incorporated, and possible disruption in commercial activities related to terrorist activity or armed conflict in the United States and other locations; the rate at which our present and future customers and end-users adopt Broadcom's technologies and products in the markets for digital television applications; delays in the adoption and acceptance of industry standards in those markets; the timing, rescheduling or cancellation of significant customer orders and our ability, as well as the ability of our customers, to manage inventory; the gain or loss of a key customer, design win or order; our ability to scale our operations in response to changes in demand for our existing products and services or demand for new products requested by our customers; our ability to specify, develop or acquire, complete, introduce, market and transition to volume production new products and technologies in a cost- effective and timely manner; intellectual property disputes and customer indemnification claims and other types of litigation risk; the quality of our products and any remediation costs; changes in our product or customer mix; the volume of our product sales and pricing concessions on volume sales; the effectiveness of our expense and product cost control and reduction efforts; our ability to timely and accurately predict market requirements and evolving industry standards and to identify opportunities in new markets; problems or delays that we may face in shifting our products to smaller geometry process technologies and in achieving higher levels of design integration; our ability to retain, recruit and hire key executives, technical personnel and other employees in the positions and numbers, with the experience and capabilities, and at the compensation levels needed to implement our business and product plans; the risks and uncertainties associated with our international operations; competitive pressures and other factors such as the qualification, availability and pricing of competing products and technologies and the resulting effects on sales and pricing of our products; the timing of customer-industry qualification and certification of our products and the risks of non-qualification or non-certification; the availability and pricing of third party semiconductor foundry, assembly and test capacity and raw materials; fluctuations in the manufacturing yields of our third party semiconductor foundries and other problems or delays in the fabrication, assembly, testing or delivery of our products; the risks of producing products with new suppliers and at new fabrication and assembly facilities; the effects of natural disasters, public health emergencies, international conflicts and other events beyond our control; the level of orders received that can be shipped in a fiscal quarter; and other factors.</p>

<p>Our Annual Report on Form 10-K, subsequent Quarterly Reports on Form 10-Q, recent Current Reports on Form 8-K, and other Securities and Exchange Commission filings discuss the foregoing risks as well as other important risk factors that could contribute to such differences or otherwise affect our business, results of operations and financial condition. The forward-looking statements in this release speak only as of this date. We undertake no obligation to revise or update publicly any forward-looking statement for any reason.</p>

<p>Broadcom(R), the pulse logo, Connecting everything(R) and the Connecting everything logo are among the trademarks of Broadcom Corporation and/or its affiliates in the United States, certain other countries and/or the EU. Dolby(R) is a trademark of Dolby Laboratories Licensing Corporation. MIPS32(R) is a trademark of MIPS Technology, Inc. Blu-ray Disc(TM) is a trademark of the Blu-ray Disc Association. Any other trademarks or trade names mentioned are the property of their respective owners.</p>

<p> Broadcom Trade Press Contact<br />
 Laura Brandlin<br />
 Senior Director, Marketing Communications<br />
 949-926-5108<br />
 lbrandlin@broadcom.com</p>

<p> Broadcom Investor Relations Contact<br />
 T. Peter Andrew<br />
 Vice President, Corporate Communications<br />
 949-926-5663<br />
 andrewtp@broadcom.com</p>

<p> Broadcom Technical Contact<br />
 Charlie Lou<br />
 Product Marketing Manager, Digital TV<br />
 949-926-8508<br />
 clou@broadcom.com</p>

<p>Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20060609/BROADCOMLOGO<br />
AP Archive: http://photoarchive.ap.org/<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: Broadcom Corporation; BRCM Broadband</p>

<p>CONTACT: Trade Press, Laura Brandlin, Senior Director, Marketing<br />
Communications, +1-949-926-5108, lbrandlin@broadcom.com, or Investor<br />
Relations, T. Peter Andrew, Vice President, Corporate Communications,<br />
+1-949-926-5663, andrewtp@broadcom.com, or Technical Contact, Charlie Lou,<br />
Product Marketing Manager, Digital TV, +1-949-926-8508, clou@broadcom.com, all<br />
of Broadcom</p>

<p>Web site: http://www.broadcom.com/</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 20, 2007  6:19 AM</b>
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
			<?=getComments(673)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 673)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/broadcom-announces-complete-digital-tv-receiver-system-designed-to-meet-the-ntias-digitaltoanalog-tv-coupon-program.php" type="text/javascript" charset="utf-8"></script>
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