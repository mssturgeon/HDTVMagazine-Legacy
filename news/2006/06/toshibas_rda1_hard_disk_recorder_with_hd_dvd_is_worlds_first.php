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
		AND e.entry_id = 390";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 390 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 390 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 390";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 390";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First" />
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
	<title>HDTV Magazine - Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First</title>
	<meta name="keywords" content="hard disk, high definition, output line, audio output, video mode, dvd, video, high, mode, digital, output, hard, content, disk, recording, may, definition, line, discs, recorder, toshiba, playback, programs, speed, support" />
	<meta name="description" content="Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &amp;quot;RD-A1&amp;quot; can record and store up to 130 hours of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc." />
	<meta name="title" content="Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &amp;quot;RD-A1&amp;quot; can record and store up to 130 hours of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=390', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php">Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 22, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<center><p align="center"><strong><font color="#996633" size="4">Integration of 1-Terabyte<sup>2</sup> Hard Disk with HD DVD Recordable Drive Opens way to Recording and Archiving of High-Definition Video</font></strong></p>
</center>

<p><img src="/images/articles/RD-A1.jpg" alt="RD-A1" align="left"><br />
<p>TOKYO-Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &quot;RD-A1&quot; can record and store up to 130 hours<sup>3</sup> of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc. In addition to superb image and sound recording and playback, the new recorder also offers an extensive range of advanced functions made possible by the versatility of HD DVD, including optimized navigation and menu displays. The RD-A1 is scheduled for roll out in the Japanese market from July 14. </p>

<p>The RD-A1 is the first video recorder to support recording and playback of content in the HD DVD format<sup>4</sup>, the next generation of DVD format defined and approved by the DVD Forum. The recorder combines support for recording of full HD broadcasts with high capacity recording to HD DVD-R discs: up to 115 minutes<sup>3</sup> of HD content to a 15-gigabyte (GB) single-layer HD DVD-R disc, and up to 230 minutes<sup>3</sup> to a 30GB dual-layer HD DVD-R disc, allowing viewers to make HD DVD-R libraries of their favorite TV programs, whether dramas, movies or sport. Ease of use is also enhanced by the ability to record two TV programs, one digital HD and one analog, to the hard disk, simultaneously.</p>

<p>In addition to HD DVD, the RD-A1 also supports playback from and recording to conventional DVD-RAM/-RW/-R discs, giving users complete access to content recorded and saved in standard DVD. It also offers simplified transfer of DVD disc content to higher capacity HD DVD discs.</p>

<p>Another key feature among the many supported by the RD-A1 is support for 1080p output via HDMI, allowing viewing of &quot;full HD&quot; progressive scan video signals<sup>5</sup>. Up-conversion<sup>6</sup>of standard DVD to 1080p resolution output also enhances the enjoyment of current DVD software and recorded programs. Video and audio output is further enhanced by the design of the RD-A1's chassis, which isolates the player from vibration and optimizes the performance of its high-grade parts and components.</p>

<p>RD-A1 takes full advantage of the advanced functionality<sup>7</sup> offered by the versatility of the HD DVD format, which far surpasses standard DVD in its extensive support for &quot;pop-up menus&quot; and advanced features such as Picture in Picture (PIP) with moving picture functions.</p>

<p><strong><font color="#996600" size="+1">Background</font></strong><br />
<p>Toshiba launched &quot;RD-2000,&quot; the world's first digital video recorder integrating a hard disk and DVD recorder in the Japanese market in 2001. RD-2000 introduced the world to a new way of viewing TV programs, &quot;First record to hard disk, select and archive to DVD,&quot; and inspired a new market for &quot;Hard Disk &amp; DVD&quot; where Toshiba still provides leadership and drives growth. Now, as HDTV broadcasting expands its services and service area, in readiness for the 2011 phase out of analog broadcasting in Japan, demand is growing for an &quot;Hard Disk &amp; DVD&quot; solution that can handle high definition image quality and its larger data capacities. Toshiba delivers the clear answer with the RD-A1. The new recorder is the first HD DVD recorder, and combines it with a 1TB hard disk, and with it Toshiba leads the industry and supporting for HD DVD playbacks and recorders to be the first manufacture to bring the product in the market. Toshiba will enhance its line-up of HD DVD products by producing the products which integrates the next generation of DVD in the marketplace.</p></p>

<p><strong><font color="#996600" size="+1">Key Features of the New Recorder</font></strong><br />
<p><strong><font color="#996600">1. </font></strong><font color="#996600"><strong>Record digital broadcasts to hard disk and HD DVD-R</strong></font> Integrated digital tuners cover the full range of HD broadcasting sources &mdash;mdash; terrestrial, broadcast satellite (BS) and communications satellite 110&deg; (CS) broadcasts &mdash;mdash; while another dedicated tuner handles analog broadcasts. The RD-A1 can record two broadcasts at once, one digital broadcast, one analog. The hard disk drive's terabyte capacity allows it record and playback 130 hours<sup>3</sup> of HD broadcasts, and viewers are then free to select and archive their favorites to HD DVD discs. The RD-A1 supports two HD DVD-R capacities, a single-layer 15GB disc that can record up to 115 minutes<sup>3</sup> of HD broadcasts, and a dual-layer 30GB disc that doubles that performance to 230-minutes<sup>3</sup>, allowing viewers to build libraries of their favorite programs. The new recorder also supports recording of digital high-definition TV programs on conventional DVD-RAM/-RW/-R discs at standard definition image quality.<sup>8</sup></p></p>

<p><strong><font color="#996600">2. Playback of high definition content and support for advanced content features</font></strong><sup>7</sup> The RD-A1, like the HD DVD players Toshiba has already launched, can play back HD DVD content software, and also supports the enhanced functionality and diverse features that content providers can build into their package software &mdash;mdash; a major step forward from standard DVD players. While specifics depend on the title, typical features include the convenience of a &quot;pop-up menu&quot; that displays menu choices or movie chapters while a movie plays, allowing viewers to search for desired functions or use the chapter guide to jump to a particular scene. The new product also supports PIP with video, a feature that allows, for example, comments by the director or actors to be superimposed over a movie while it is playing. The commentators can literally point to the material they are discussing. &nbsp;Audio output is as rich as video playback, as RD-A1 supports next generation surround sound formats, such as Dolby Digital Plus, Dolby TrueHD and DTS-HD, L-PCM 5.1ch, the same formats as Toshiba's first HD DVD players. Analog 5.1ch output integrated into the RD-A1 allows consumers to enjoy surround sound simply by connecting the player to an AV amplifier with analog input.

<p><strong><font color="#996600">3. Support for RD engine for HD DVD</font></strong> Toshiba has upgraded its successful &quot;RD Engine HD&quot; to provide dedicated support for HD DVD format. Upgrades include a graphic user interface with letter-box display compatibility and Toshiba proprietary multi-function recording software. RD Engine HD allows viewers to edit recorded high-definition programs on a frame basis, and transfer the edited video to an HD DVD disc. A useful function is high-speed write of DVD video sources to hard disks and high speed dubbing<sup>9</sup> of that video to an HD DVD-R, allowing viewers to combine programs from multiple discs on a single disc. This compacting of video libraries is done without any loss of picture or sound quality. 

<p><strong><font color="#996600">4. Digital high definition picture of 1080p in HDMI output</font></strong> Support for the up-conversion to 1080p output is achieved<sup>10</sup> through implementation of the newest high performance scaler from Anchor Bay Technologies Inc. This converts and plays back 1080i HD content as 1080p full HD output<sup>11</sup>. Furthermore, besides the HD DVD software and DVD software, it is also possible to playback past recorded DVD by up-converting them to 1080p output<sup>12</sup>. 
<p><strong><font color="#996600">5. Body and parts designed for high definition picture quality and high quality sound </font></strong> The RD-A1's design is optimized for high quality video and audio output by a special dual-layer body featuring a 1-millimeter main case and a metal sub frame. The recorder stands on special aluminum pillars designed to damp vibration and enhance high sound quality. The same attention to detail carries through to chief components. The RD-A1 is the first recorder to adopt a high-speed, high-performance 297MHz/14bit video encoder, making it possible to deliver HD quality via analog output through the D terminal and component terminal. High grade parts typically found in high-end audio products are also used in the RD-A1. 

<p><strong><font color="#996600">6. Internet connectivity via &quot;Net de Navi<sup>&reg;</sup>&quot;software, recommendation service, and DLNA guideline</strong></font><sup>13</sup> The versatility of the RD-A1 is significantly enhanced by its Internet connectivity via &quot;Net de Navi<sup>&reg;</sup>&quot; software. Once in a network the recorder can be programmed remotely, via e-mail or the on-line iEPG, an electronic TV program timetable service. LAN connectivity allows configuration of a home network&nbsp; with Toshiba's series of digital high-definition LCD TVs in the &quot;REGZA Z1000&quot; series and with the &quot;Qosmio G30&quot; AV notebook PC, which supports DLNA guideline, and allows users to playback the recorded titles on different devices on the network<sup>14</sup>. 

<p>1 As of June 2006, as a digital video recorder with HD DVD.</p>

<p>2 1TB is 1,000GB (Gigabyte), calculated on the basis of 1GB=1 billion bytes.<br />
 <br />
3 Recording of digital terrestrial broadcasts at approx.17Mbps in TS mode.<br />
 <br />
4 Playback and recording in MPEG4 AVC and VC1 is not available for HD-DVD-R.<br />
 <br />
5 An HDTV or HD display equipped with D3/D4 input or HDCP capable HDMI input is required for high-definition viewing.</p>

<p>6 Since up-conversion is from standard definition video, image quality may not match that of an original high-definition source. <br />
 <br />
7 1) Some advanced functions may not be available, depending on HD DVD content specifications.<br />
 <br />
 2) HD DVD player support for versatile functions is based on software instructions integrated with the content. Such instruction may need to updated, via downloads from the Internet. Functions and operation, including the display, sound effect and icons, may differ with content. Consult the manufacturer's customer service or the user manual for more details.<br />
 <br />
 3) Please check content specifications of the content since some contents may not apply to the up-date information or may require a broadband Internet connection. </p>

<p>8 VR-mode recording is available in CPRM discs. Support for cartridge in DVD-RAM. Also, support for VR-mode recording in DVD-R DL.<br />
 <br />
9 No support for copy-once recording. Limited to titles recorded in VR mode, or titles recorded in the Toshiba RD series products supporting re-writing.<br />
 <br />
10 In order to enjoy viewing 1080p output signals, a cable and a TV or display that support the 1080p signal format is required. </p>

<p>11 Some content may not up-convert. </p>

<p>12 Since up-conversion is from standard definition video, image quality may not match that of an original high-definition source.</p>

<p>13 DLNA (Digital Living Network Alliance) is a organization that supports standardization of home LAN.&nbsp; Supports only the digital media server.</p>

<p>14 Copy free, limited to titles in VR mode. </p>

<p><font color="#996600"><strong><font size="+1">Key Specifications</font></strong></font> 
<table class="type1b" cellpadding="0" cellspacing="0"><tr><td class="type1b_header" nowrap >Model name</td><td class="grid">RD-A1</td></tr><tr><td class="type1b_header" nowrap >Hard Disk&#12288;</td><td class="grid">Built in Hard DiskHD DVD-Video
Twin Format Discs (Dual-layer, single-sided, HD DVD-Video + DVD-Video), HD DVD-R (HDVR mode), HD DVD-R DL (HDVR mode), DVD-R (Video mode; VR mode), DVD-R DL (Video mode; VR mode), DVD-RAM (VR mode), DVD-RW (Video mode; VR mode), DVD-Video Music CD CD-R, CD-RW (CD-DA), <table> <tr> <td nowrap valign="top">*</td> <td>Some content and discs may not be compatible. Depending on recording mode and condition, some discs may not playback. DVD-RW Ver.1.0 is not supported.</td> </tr> </table></td></tr><tr><td class="type1b_header" nowrap >Recordable media <p></p>
</td><td class="grid" class="grid"> <p>Built in Hard Disk (TS mode; VR mode) HD DVD-R (HDVR mode), HD DVD-R DL (HDVR mode), DVD-R (Video mode; VR mode), for General/1X-16X SPEED (recording speed 8X), DVD-R DL (Video mode; VR mode), for General/4X SPEED (recording speed 4X), DVD-RAM (VR mode), 2X-5X SPEED (Applied cartridges), (recording speed 2X), DVD-RW (Video mode; VR mode), 1X-6X SPEED (recording speed 4X), *Some discs may not be compatible, or may not record.</td></tr><tr><td class="type1b_header" nowrap >Video recording format&#12288;</td><td class="grid">MPEG 2&#12288;</td></tr><tr><td class="type1b_header" nowrap >Audio recording format</td><td class="grid"><p>Dolby Digital (2ch), L-PCM (2ch), AAC (5.1ch),</p></td></tr><tr><td class="type1b_header" nowrap >Audio output</td><td class="grid">Dolby Digital (5.1ch)
Dolby Digital Plus (5.1ch), Dolby TrueHD (2ch), DTS-HD (5.1ch) L-PCM (5.1ch) *Output format depends on content.</td></tr><tr><td class="type1b_header" nowrap >Video DAC</td> <td class="grid">14bit, 297MHz</td></tr><tr><td class="type1b_header" nowrap >Audio DAC&#12288;</td> <td class="grid">192kHz, 24bit</td></tr><tr><td class="type1b_header" nowrap >Channel&#12288;</td><td class="grid">Digital terrestrial broadcast (000 - 999ch), CATV path through,BS digital (000 - 999ch), 100 degree CS digital broadcast (000 - 999ch), Analog terrestrial broadcast VHF (1 - 12ch), UHF (13 - 62ch), CATV (C13 - C63ch),
</td></tr><tr><td class="type1b_header" nowrap >Input</td><td class="grid">S1 video input line 3 (rear 2, front 1), video input line 3 (rear 2, front 1), 2ch analog audio input line 3 (rear 2, front 1), D1 video input line 1, DV input line 1 (front)</td></tr><tr><td class="type1b_header" nowrap >Output&#12288;</td><td class="grid">D1/D2/D3/D4 video output line 1, component video output line 1 (Y, CB, CR), S1 video output line 3, video output line 3, 5.1ch surround-sound analog audio output line 1, 2ch analog audio output line 3, Coaxial digital audio output line 1, Optical digital audio output line 1, HDMI output line 1, i.LINK line 2 (D-VHS dubbing)</td></tr><tr><td class="type1b_header" nowrap >Antenna terminal</td> <td class="grid">Digital terrestrial in-output, BS/100degree CS digital in-output, VHF/UHF in-output</td></tr><tr><td class="type1b_header" nowrap >Other terminals&#12288;</td> <td class="grid">LAN terminal, Sky Perfect continuous terminal, phone circuit terminal, Extension terminal line 3 (front, 5V 500mA)</td></tr><tr><td class="type1b_header" nowrap >Power consumption&#12288;</td><td class="grid">133W (BS antenna supply:144W), Stand by mode 6.5W (power-save:4.0W),</td></tr><tr><td class="type1b_header" nowrap ><p>Dimensions</p></td><td class="grid">Width 457 mm &times; Height 159 mm &times; Depth 408 mm</td></tr><tr><td class="type1b_header" nowrap >Weight&#12288;</td><td class="grid">15.2 kg&#12288;</td></tr><tr><td class="type1b_header" nowrap >Accessories&#12288;</td><td class="grid">Remote control, battery for remote control (AAA cell battery x 2),power cable, coaxial cable, video/audio connecting cord, user manual, B-CAS card, modular splitter, phone cable</td></tr></table>

<p><br />
<table><tr><td valign="top" nowrap>&#12539;</td><td>RD-A1 supports AACS (Advanced Access Content System), the next generation content protection &nbsp;system.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>An HDTV or HD display equipped with D3/D4 input, HDCP capable HDMI input, or component video input is required for high-definition viewing. Other TVs or displays can display content, but not in high definition. Also, some content may not playback or playback in lower resolution on equipment with D3/D4 and component video output.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>HDMI and High-Definition Multimedia Interface are trademarks of HDMI Licensing, L.L.C.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>HD DVD and DVD are trademarks of the DVD Format/Logo Licensing Corporation.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Dolby and Dolby Digital are registered trademarks of Dolby Laboratories.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>DTS is a registered trademark of DTS, Inc.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>i.LINK is a registered trademark of Sony Corporation.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Other company names and products names are the registered trademarks of each company.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Service via the Internet may subject to temporary cessation or termination without notice.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Some discs may not playback or record.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Some discs may be incompatible with playback and/or record.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Recording of audio and video is for personal use and unauthorized usage is prohibited under the copyright laws.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>While Toshiba has made every effort at the time of publication to ensure the accuracy of the information provided herein, product specifications are subject to change without notice.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Not all discs and output connectors are compatible and no guarantee of performance is made hereby.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Actual writing speed may decrease from the actual speed.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>This digital AV product integrates diverse software. The hard disk and HD DVD drive are connected via an ATAPI interface, a PC-based connection standard, and both the hardware and software are managed via operating system software, like a PC.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>While designed to be robust, the hard disk contains moving parts, and may be damaged if proper conditions of use are not observed. If a part of the hard disk platter becomes damaged, programs recorded on that part may exhibit pixelation or black noise when played back. If you notice such noise or problem, you will have to replace the hard disk, at cost.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>We do not recommend using the hard disk for long term storage of programs. Transfer important programs that you want to save to a recordable DVD disc. Recordable DVD discs are also susceptible to damage if not handled and stored carefully, and as a result some of all of the programs stored on them may become unplayable. Reduce these risks by using high quality DVD recordable discs and checking their playability from time to time. Recovery of programs deleted from the hard disk is not guaranteed.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Hard disk capacity is calculated on the basis of 1TB=1,000GB, and 1GB =1-billion bytes.</td></tr></table></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 22, 2006  5:30 AM</b>
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
			<?=getComments(390)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 390)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/06/toshibas-rda1-hard-disk-recorder-with-hd-dvd-is-worlds-first.php" type="text/javascript" charset="utf-8"></script>
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