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
		AND e.entry_id = 909";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 909 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 909 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 909";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 909";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LCD Specs Playing with Your Eyes" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LCD Specs Playing with Your Eyes" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LCD Specs Playing with Your Eyes" />
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
	<title>HDTV Magazine - LCD Specs Playing with Your Eyes</title>
	<meta name="keywords" content="response time, frame rate, liquid crystal, crystal pixel, next frame, frame, time, frames, response, display, pixel, lcd, rate, image, faster, hold, motion, fps, video, black, light, speed, could, driving, liquid" />
	<meta name="description" content="A recent question by one of our HDTV Magazine readers regarding LCD was: What does 4ms really mean? And how would that reconcile with the fact that a typical 60Hz frame rate would display each frame every 16ms? (8ms on a 120Hz display).

To accurately respond to these questions would take more than just a few sentences. The answers are not black and white, as I explain in the following article.

There seems to be a contradiction in the way these specs are expressed or interpreted for LCDs, and they are actually two different subjects that interact for the objective of display quality..." />
	<meta name="title" content="LCD Specs Playing with Your Eyes" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LCD Specs Playing with Your Eyes" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="A recent question by one of our HDTV Magazine readers regarding LCD was: What does 4ms really mean? And how would that reconcile with the fact that a typical 60Hz frame rate would display each frame every 16ms? (8ms on a 120Hz display).

To accurately respond to these questions would take more than just a few sentences. The answers are not black and white, as I explain in the following article.

There seems to be a contradiction in the way these specs are expressed or interpreted for LCDs, and they are actually two different subjects that interact for the objective of display quality..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=909', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php">LCD Specs Playing with Your Eyes</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 21, 2008</b>
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
				<p>A recent question by one of our HDTV Magazine readers regarding LCD was: What does 4ms really mean? And how would that reconcile with the fact that a typical 60Hz frame rate would display each frame every 16ms? (8ms on a 120Hz display).  <p>To accurately respond to these questions would take more than just a few sentences. Although the answers in the following article are factual, they require comprehensiveness to cover the subject well. <p>There seems to be a contradiction in the way these specs are expressed or interpreted for LCDs, and they are actually two different subjects that interact for the objective of display quality.  <h2>Liquid Crystal Pixel Response Time</h2> <p>The short answer to the 4ms part of the question is:  <p>The timing is the response time in ms that a single liquid crystal pixel of a display device takes to twist and turn itself to change from white to white passing thru black first. Manufacturers also declare the spec from gray to gray, from black to black, to express the same thing, and could be shorten with "G-G or g-g".  <p>In other words, the timing for a liquid crystal pixel to restore itself and be ready to display the next image without showing any residue of the previous image displayed by that pixel (otherwise artifacts could occur).  <p>In theory, the faster a liquid crystal pixel can restore itself, the better potential the display device would have to display more frames per second (faster frame refresh rates), for whatever purpose.  <p>Some manufacturers express the spec as the number of ms the liquid crystal pixel takes to reach only up the halfway point (ie. measuring from white to black, before converting to white again). If my memory serves me correctly, several years ago Sony specified some SXRDs LCoS (Liquid Crystal on Silicon) sets with about 2.5ms for that half cycle when introduced, which in theory would mean 5ms for the full cycle.  <p>Some consumers do not take the time to verify how the spec was measured, and many do not even know or care for it. Sony probably viewed the opportunity of showing a small number on the spec as a market edge at a time when LCD was notoriously slow in the 16-20 ms range. Blurriness was (and still is) a negative factor in LCD technology.  <p>In my exchanges about the subject with Brian Berkeley, Vice President, LCD Business Technology Development Group, Samsung Electronics, the concept was further clarified:  <p><em>"G-G response time can be reduced (a good thing) by application of response time compensation (RTC) circuits, which compare gray levels of the previous and current frames to determine a boost value.<br><br>This boost, or overshoot, causes the LC molecules to switch to the proper position more quickly. Samsung's version of RTC is called dynamic capacitance compensation, or DCC. For an LCD-TV to be free of motion blur, it is a requirement that the g-g response time be small enough."</em></p> <p><i></i> <p><i></i> <h2>Frame Refresh Time</h2> <p>The 16 ms timing mentioned in the question from this reader pertains to another specification. The spec is the timing the display device takes before showing the next full frame at 60 frames per second (1 second/60 frames= 0.016), regardless of image resolution (480p, 1080p or 720p).  <p>Note that I am not saying "the timing that takes a full frame to be displayed", which is a different concept.  <p>The frame refresh rate is a specification of the incoming signal but is also of a display device, which could have by design the capability to show 60 frames per second of incoming video at faster speeds if necessary, such as 120Hz or 180Hz. What kind of content is displayed on those extra frames is another subject, more on that later.  <p>Although that spec is a timing of the video engine, not the liquid crystal pixel, the overall performance of the set could be affected by the interaction of both. For example, the response time of each pixel could be insufficient when is much slower (taking longer to restore itself) than the speed required by the frame rate of the display. In simple terms, having a slow 20ms pixel response time on a 120Hz display (that would require at least 8ms of pixel speed) would render an inferior image.  <p>In other words, the set intends to display video frames at a speed that the pixels response time cannot catch up with, affecting the pixels readiness for the next frame of video, and show blurriness, lag, etc. which is a common problem on poorly designed LCD panels.  <p>Although an incoming signal could be interlaced at 60i (equivalent to 30 fps), LCD fixed-pixel-panels de-interlace the "i" to "p" and show progressive frames as 60p frames of 1080 or 720 horizontal lines, all at once.  <h2>The Sample-and-Hold Style of Operation</h2> <p>Even when reducing the pixel response time to shorter intervals, such as 2ms or 4ms, the typical LCD style of operation is still constrained with other limitations that would not allow to profit from the shorter pixel intervals as expected to reduce blurriness.  <p>Typical LCD panels show each frame in a "sample-and-hold" manner, by which all the pixels on each frame are kept lighted for the whole duration of the cycle for that frame rate (16ms for 60fps), and at the very end of that cycle the crystals twist and turn in a rush to be ready and adapt to the next frame, which also brings full light with it. That is done even when the pixel could have restored itself faster at its own independent response time of 2, 4, 6, 8 ms depending on the design.  <p>In other words, the blurriness is not always caused because the liquid crystal pixel response time is slow, but because there might not be a break of light in between frames.  <p>Note that here you can capitalize from the comment I made earlier about frame refresh time ('Note that I am not saying "the timing that takes a full frame to be displayed", which is a different concept.')  <p>As follows: The frame could be displayed really fast within the 16 ms period between frames, and still have the time to DISAPPEAR out of sight to show black or something else to interrupt light, rather than sit there fully lighted and on hold until is time for the next frame.  <p>An important factor is that, to perceive continuous motion, the human eye expects the image frames to have breaks in between and interrupt the constant supply of light, so it might not be misinterpreted as increased blurriness.  <p>The sample-and-hold approach for each frame does not provide for that break, and when the next frame needs to be shown the twist-and-turn of the liquid crystal pixels is done so quickly that is not seen as a light interruption.  <p>While discussing this subject with Brian Berkeley, he confirmed the following:  <p><em>"Hold type driving is used in LCD-TVs and in other matrix-addressed displays. Compare this type of driving with CRT displays, which are impulsively driven. In an impulsively driven display, the image is present only for a short period of time. By contrast, in a hold-type driven display like an LCD, the image is held throughout the entire frame period until next frame is written.<br><br>Hold-type driving is good for eliminating flicker and for getting maximum light output, but there is a drawback to hold-type driving for moving pictures. If an image is moving at a rate of, say, a few pixels per frame, then there is effectively a positional error in that the image position is correct for only a fraction of the frame.<br><br>To measure motion picture response time of an LCD panel, there is a metric called "MPRT", which literally stands for motion picture response time. MPRT is useful for comparing different LCD-TVs, but it is not so useful for comparing LCDs to other technologies, such as plasma displays, projection displays, or CRTs. As in g-g time, it is also good to have a lower MPRT score.<br><br>Important note: Having very small gray-to-gray response time does not solve this problem. Even if the g-g response time is 0, MPRT can still be too large to have blur-free images. Again, this is due to hold-type driving mentioned above. A low g-g LC response time is necessary, but not sufficient, for achieving a low MPRT score."</em></p> <p><i></i> <p>Another factor is the flicker-fusion threshold of our vision, which should be lower than the frame refresh rate of the display to perceive motion without flicker. That varies with each person, and is something similar to the rainbow effect of color wheels on 1-chip DLP engine designs, which most people do not notice, but some do.  <h2>Faster Frame Rates</h2> <p>If a display's frame rate is faster than the incoming signal, it has the potential to smooth out the motion and improve the presentation of images. For example, 24fps video that originated from film content will show flicker if displayed at that speed, some displays can accept that 24fps frame rate and display the frame cadence at exact multiples of it to eliminate the flicker and to give smoothness to fast action images, without doing 2:3 pull-down to convert the film frame rate to the more typical 60fps of video.  <p>Check the glossary for details on that subject:  <p><a href="http://www.hdtvmagazine.com/glossary.php#2-3+Pulldown+%28also+mentioned+as+3-2+pulldown%29">http://www.hdtvmagazine.com/glossary.php#2-3+Pulldown+%28also+mentioned+as+3-2+pulldown%29</a>  <p>Some displays multiply the frame rate x2, x3, etc. to display the 24fps content at 48Hz (front projectors), 72Hz (Pioneer Elite plasmas), 96Hz (front projectors), and even at 120 Hz (some projectors, LCDs), creating or adding new frames that were not present in the incoming signal.  <h2>Interpolate New frames</h2> <p>To impart smoothness to the fast movement of the objects shown by the image, some sets insert new frames employing interpolation techniques that calculate its pixels anticipating the future direction of the content motion by looking at the next frame/s in advance, rather than just repeating the same exact frame.  <p>Such technique claims to improve the presentation of fast images, but I add, provided that the video processing is of sufficient quality to avoid adding artifacts that might render a cadence of images that is poorer than just repeating the same frame.  <p>Such repetition would be more faithful to film content, as the local theater projector do with film, showing the 24 film frames per second at 48 frames speed by opening the projector's shooter twice for each celluloid frame that passes in front of the projector lens.  <h2>The 120 Hz Competition</h2> <p>Having a spec of 120Hz on a LCD display does not necessarily mean that the set is doing pixel-by-pixel motion adaptive interpolation of added calculated frames as mentioned above, it depends on the design, and it does not necessarily mean that it accepts 24fps film sources frame rate to display them at 5 times that speed, whereby 1 incoming frame is repeated 4 times before displaying the next frame.  <p>Some might use the faster 120Hz display speed to give smoothness to the 60Hz video source presentation, and convert 24Hz sources to 60Hz with 2:3 pull-down, and then double the frame rate to 120Hz of the display by repeating twice the frame (rather than the 4 repeated frames technique above). Because of the conversions, there could be a lot of video artifacts compared to straight frame repetition.  <p>Some LCDs are inserting black or darker (same image at lower light) frames within the 120fps display cadence of an incoming 60fps image. Some can do both (black/darker frames, or image interpolated frames) at the option of the viewer and selectable from the video menu of the set.  <p>Some can show black intervals between the actual 60fps frames without having a 120Hz speed rate design, such as just interrupting the light source in between frames, rather than adding black frames that need to be displayed at a faster frame rate.  <p>The insertion of black (or darker) frames or light interruptions between actual frames is viewed by many manufacturers as a good method for the human vision to better perceive motion from LCD with less motion blur, having a clean separation of video frames with no (or reduced) light source in between, rather than the sample-and-hold approach mentioned before.  <p>120Hz is becoming the new buzzword for LCD panels to give consumers the idea of "my panel is faster and is therefore better than the competition", but many fall short in describing how that speed is used. The image cadence could be faster but at the price of interpolation artifacts from a sub-standard video processor, to keep the panel price down, which also attracts consumers.  <p>Manufacturers like JVC, Toshiba, Sharp, Philips, Samsung, Hitachi, and LG have implemented proprietary technologies such as Clear Motion Drive II, ClearFrame, <a href="http://www.tacp.toshiba.com/televisions/lcd/glossary.asp?fid=2088">http://www.tacp.toshiba.com/televisions/lcd/glossary.asp?fid=2088</a>, FineMotion Advanced, and Pixel Plus (from the first four manufacturers above respectively), to address the sample-and-hold issues, improve motion smoothing, and reduce the perceived motion blur. They employ methods of adding black frames, interpolated frames, darker frames, backlight flashing, at 60, 120 or 180 Hz frame rates.  <p>Brian Berkeley, further adds to this concept:  <p><em>"g-g and MPRT scores are being confused with one another. There is nothing particularly great about a g-g response time of 8ms; 4ms g-g is more typical these days. On the other hand, an MPRT score of 8ms is about state-of-the-art for mass production LCD-TVs these days.<br><br>So how to overcome the problem of hold-type driving? By using 120Hz driving with frame interpolation, instead of 60Hz driving, this effectively cuts the hold time in half. If the g-g response time is low enough, the MPRT score will also be cut in half.<br><br>Another technique is to use black insertion, which causes the LCD to mimic a CRT's impulsively driven response. But the problem of applying such kind of impulsive driving technique to LCDs is that (just like CRTs) flicker can occur, and there is light loss.<br><br>With this as background, yes, a 120Hz set must have g-g response times below 8ms."</em> <h2>Mine is a Super Fast Pixel</h2> <p>Why pursuing a shorter 2-4-6 ms pixel response time when it would not solve the blurriness problem by itself? Most LCD panels are designed with frame rates of 60fps, and now with 120 fps to address the problem, and those theoretically would need respectively 16 or 8 ms pixel response time.  <p>Maybe those pixel-fast panels were preparing for soon to be implemented technology of faster frame rates, which would put to use such fast speed at the pixel level. Maybe they should have both been implemented together to claim that the blurriness problem in LCD was properly addressed as a package.  <p>In the middle of those question marks there is an issue of market competition and "be first in technology" by a manufacturer, and how that played a role in convincing consumers that 2-4-6 ms response time was actually needed in a world of mostly 60Hz panels, and even now with 120Hz panels.  <p>Brian Berkeley responded to me with one final comment about that issue:  <p><em>"Yes, 120Hz driving needs faster g-g response time. The calculation of required g-g isn't trivial; it depends on several factors. For example, for a 120Hz panel, it isn't quite as simple as saying the g-g response must be within 1/120Hz or 8.33ms.<br><br>A g-g response below that number is required. Also, g-g is just one number, but how it gets measured is important: Does g-g response time mean average g-g over all possible transitions, does it mean the max value of all possible transitions, is it measured at 10%-90% or some other transition %, and so on.<br><br>This said, no I don't see any side benefit to having faster transition time if the frame rate doesn't require it. Better to focus on other design points. I would agree that 2ms g-g is over-designed for current generation sets, especially if 60Hz."</em> <p>The question is then: how much lower is low enough (lower than 8ms for 120 Hz panels) to make sure the g-g as measured is sufficiently low (fast) for the requirement of the panel frame rate? Considering that the g-g measurement standard is not typically disclosed on a g-g spec other than saying "x ms", and even if it would be, I doubt most people would know what to do with it.  <p>By design the g-g should be low enough, but are you the type of consumer that believes that manufacturers do not cut corners believing you won't notice? Or one that rather go a bit lower than 8ms g-g, just to be on the safe side, maybe down to 4ms, if that is a safe zone for your pocket.  <p>Would you buy a Ferrari with 55 Mph rated tires, or rather with 200 Mph rated tires?</p> <p>Even when law limits the 55 Mph speed, you might feel better because one day such high performance on that piece of the car "might be needed".</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 21, 2008  6:16 PM</b>
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
			<?=getComments(909)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 909)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php" type="text/javascript" charset="utf-8"></script>
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