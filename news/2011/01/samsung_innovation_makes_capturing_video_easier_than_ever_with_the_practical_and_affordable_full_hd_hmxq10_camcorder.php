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
		AND e.entry_id = 4119";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4119 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4119 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4119";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/samsung-innovation-makes-capturing-video-easier-than-ever-with-the-practical-and-affordable-full-hd-hmxq10-camcorder.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4119";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder" />
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
	<title>HDTV Magazine - Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder</title>
	<meta name="keywords" content="switch grip, easy operation, samsung electronics, smart access, easier ever, hmx, samsung, camcorder, easy, video, recording, lcd, grip, smart, digital, technology, users, switch, makes, record, best, operation, features, intuitive, access" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/samsung-hmx-q10-camcorder.jpg&quot; alt=&quot;Samsung HMX-Q10 Camcorder&quot; height=&quot;48&quot; width=&quot;72&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;Samsung Electronics Co., Ltd, a global leader in digital media and digital convergence technologies, today announced the launch of the latest model in its camcorder portfolio, the HMX-Q10. The HMX-Q10 has been designed to highlight Samsung's full HD camcorder technology with a BSI CMOS sensor, and makes recording movies easier than ever before, using ground-breaking technology and smart features such as its innovative Switch Grip technology. As a new addition to the Samsung line-up for 2011, the HMX-Q10's Easy Operation and Smart Access user interface (UI) makes Samsung's innovations in video capture more accessible than ever, allowing everyone to record excellent quality video.

Samsung has created the HMX-Q10 to be a compact, practical and easy-to-use full HD camcorder that combines the best in Samsung innovation with incredibly simple functions, all available..." />
	<meta name="title" content="Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/samsung-innovation-makes-capturing-video-easier-than-ever-with-the-practical-and-affordable-full-hd-hmxq10-camcorder.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/samsung-hmx-q10-camcorder.jpg&quot; alt=&quot;Samsung HMX-Q10 Camcorder&quot; height=&quot;48&quot; width=&quot;72&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;Samsung Electronics Co., Ltd, a global leader in digital media and digital convergence technologies, today announced the launch of the latest model in its camcorder portfolio, the HMX-Q10. The HMX-Q10 has been designed to highlight Samsung's full HD camcorder technology with a BSI CMOS sensor, and makes recording movies easier than ever before, using ground-breaking technology and smart features such as its innovative Switch Grip technology. As a new addition to the Samsung line-up for 2011, the HMX-Q10's Easy Operation and Smart Access user interface (UI) makes Samsung's innovations in video capture more accessible than ever, allowing everyone to record excellent quality video.

Samsung has created the HMX-Q10 to be a compact, practical and easy-to-use full HD camcorder that combines the best in Samsung innovation with incredibly simple functions, all available..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4119', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/samsung-innovation-makes-capturing-video-easier-than-ever-with-the-practical-and-affordable-full-hd-hmxq10-camcorder.php">Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  3, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=343&category=HD Camcorders & Cameras">HD Camcorders & Cameras</a></b>
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
				<p class="prtitle">Samsung Innovation Makes Capturing Video Easier Than Ever with the Practical and Affordable Full HD HMX-Q10 Camcorder</p>

<center><i>Samsung innovation creates simple and intuitive controls for effortless recording</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/samsung-hmx-q10-camcorder.jpg" alt="Samsung HMX-Q10 Camcorder" height="96" width="144" class="keyimg"><strong>LAS VEGAS--(BUSINESS WIRE)--</strong>Samsung Electronics Co., Ltd, a global leader in digital media and digital convergence technologies, today announced the launch of the latest model in its camcorder portfolio, the HMX-Q10. The HMX-Q10 has been designed to highlight Samsung's full HD camcorder technology with a BSI CMOS sensor, and makes recording movies easier than ever before, using ground-breaking technology and smart features such as its innovative Switch Grip technology. As a new addition to the Samsung line-up for 2011, the HMX-Q10's Easy Operation and Smart Access user interface (UI) makes Samsung's innovations in video capture more accessible than ever, allowing everyone to record excellent quality video.</p>

<p>Samsung has created the HMX-Q10 to be a compact, practical and easy-to-use full HD camcorder that combines the best in Samsung innovation with incredibly simple functions, all available at an affordable price. The HMX-Q10 combines the world's first Switch Grip with Easy Operation via an LCD screen. The Switch Grip means that no matter how the camcorder is held, the G-magnetic sensor inside the HMX-Q10 recognizes the angle and turns the LCD display so it's easy to see, and controls recording easily from any angle. Easy Operation means that users can control detailed processes, turn the camcorder on and off and operate the playback function easily through the LCD screen, so controlling the device is intuitive and fuss-free, and viewing footage is as easy as capturing it. The HMX-Q10 also features a prominently placed record button that makes capturing video extremely simple, and straightforward. The slim and durable body also means that the HMX-Q10 is compact and light enough to be taken anywhere, and the product is so versatile it can also double as a 4.9 Megapixel (MP) still photo camera.</p>

<p>The HMX-Q10 sets the tone for the Samsung 2011 camcorder range, and raises the bar for affordable and easy-to-use camcorders built on the latest cutting-edge technology. The camcorder is the product of years of Samsung expertise in digital imaging, and is based on detailed customer insight research, revealing that simplicity and affordability are a key priority for an increasing number of camcorder users.</p>

<p>"The launch of the HMX-Q10 is particularly important for us as it is one of the best demonstrations of Samsung's continuing innovation, providing high performing features and design in a model that makes the best camcorder experience available to all our customers," said Mr. Hyunho Chung, Executive Vice President and Head of the Digital Imaging Business, Samsung Electronics. "For example, we have developed the world's first Switch Grip technology so that the HMX-Q10 adapts to the consumer's lifestyle and setting – not the other way around. In addition, Easy Operation via the LCD and Smart Access UI takes the confusion out of recording, so that you can instantly record brilliant movies from the very first time you pick up the camcorder without the need for an instruction manual."</p>

<p><br />
<strong>The world's first Switch Grip (either-handed grip)</strong></p>

<p>As a brand-new addition to the Samsung camcorder portfolio, the HMX-Q10 is built around the needs of the consumer who wants to capture their movies without fuss or complication. Samsung has developed the world's first Switch Grip technology, which ensures that when filming, the LCD screen adapts to how the camcorder body is being held. Whether you are left- or right-handed, shooting in difficult positions or combining recording film with other activities, you can always maintain perfect control over your video. The body of the HMX-Q10 has also been streamlined and made more compact so it can easily be carried anywhere.</p>

<p><br />
<strong>Intuitive Easy Operation for ease of use</strong></p>

<p>The HMX-Q10 includes a brand new Easy Operation and Smart Access UI that makes using the camcorder easier to use than ever before. Featuring an intuitive and clearly visible record button, the HMX-Q10 offers the best and most convenient operating system on the market, as it eliminates the need to have many separate buttons and keys to control the camcorder. Easy Operation via the 2.7" wide LCD screen allows the user to perform a variety of functions, including a pause function allowing you to stop and work out the best angle for your recording experience, the ability to turn the camcorder on and off, and playback mode so users can immediately review their footage. The camcorder's intuitive Smart Access UI feature grants users the opportunity to operate the camcorder's advanced functions and manage detailed processes with ease.</p>

<p><br />
<strong>The best in high-quality video</strong></p>

<p>The Samsung HMX-Q10 is based on quality components and functions to help consumers capture brilliant, clear videos in astonishing quality, effortlessly and regardless of the situation. The HMX-Q10 captures video in 1920x1080/60i full HD, and includes an OIS (Optical Image Stabilization) Duo system to compensate for hand-shaking better than ever before, creating a stable, clear video every time – even when walking or moving around. The HMX-Q10's features also include a 5MP BSI CMOS sensor, which records with twice the sensitivity of normal CMOS sensors, dramatically reducing noise and distortion while also enhancing recording quality in low-light conditions, so great quality video can be captured in any situation.</p>

<p>The HMX-Q10 also features an upgraded version of Samsung's Smart Auto scene recognition technology, which analyzes key elements of the composition of the footage such as brightness, motion, color and subject and then selects the most appropriate settings to produce the best results possible. For the HMX-Q10, Samsung Record Pause technology has also been introduced, allowing the user to take brief pauses in filming before re-starting, so they don't need to merge files when finished. This allows for easier editing and makes the HMX-Q10 perfect for filming at sports events or parties. In addition to great movie functions, the HMX-Q10 can also capture 4.9MP still photos, giving users the ability to capture brilliant snapshots without the need to carry a camera. With the intuitive LCD touch screen, powered by the new Smart Access UI, users can record and review their videos easily and enjoyably.</p>

<p>Users can also choose to adopt more manual control. With the HMX-Q10's Easy Manual Mode setting, users can access and calibrate the entire breadth of easy-to-use manual features (White Balance, Exposure Values, Backlighting, Self Timer, and C.Nite) through the intuitive interface. The new Art Film function also means that video can be captured in more creative ways, using special effects such as Time Lapse and Black &amp; White, making video recording fun and easy.</p>

<p><br />
<strong>Specifications</strong><br />
<table class="bwtablemarginb" cellspacing="0"><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">&nbsp;</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black"><b>HMX-Q10</b></td></tr><tr><td><b>Storage media</b></td><td>SDHC/SD Card</td></tr><tr><td>&nbsp;</td><td>1/4.1"(effec.1/5.8")</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">5MP (2.07M effective pixels)</td></tr><tr><td style="border-bottom:1px solid black"><b>Image Stabilization</b></td><td style="border-bottom:1px solid black">OIS Duo</td></tr><tr><td style="border-bottom:1px solid black"><b>Sensor</b></td><td style="border-bottom:1px solid black">5MP BSI CMOS sensor</td></tr><tr><td style="border-bottom:1px solid black"><b>Lens</b></td><td style="border-bottom:1px solid black">Schneider-Kreuznach Varioplan-HD F1.8 10x optical zoom lens with OIS 2.75mm ~ 27.5mm focal length</td></tr><tr><td style="border-bottom:1px solid black"><b>Size</b></td><td style="border-bottom:1px solid black">43.7 x 53.3 x 119.4 mm</td></tr><tr><td><b>Additional Features</b></td><td>2.7” touch panel LCD</td></tr><tr><td>&nbsp;</td><td>Flip Shooting (Switch Grip Control)</td></tr><tr><td>&nbsp;</td><td>Switch/both Handed Grip</td></tr><tr><td>&nbsp;</td><td>Face detection</td></tr><tr><td>&nbsp;</td><td>Smart Record Pause</td></tr><tr><td>&nbsp;</td><td>Ultra Compact and Full HD Recording</td></tr><tr><td>&nbsp;</td><td>Built-in USB/PC S/W</td></tr><tr><td>&nbsp;</td><td>Auto Focus</td></tr><tr><td>&nbsp;</td><td>Face detect (up to 6 persons)</td></tr><tr><td>&nbsp;</td><td>Smart Auto</td></tr><tr><td>&nbsp;</td><td>HD Time Lapse recording</td></tr><tr><td>&nbsp;</td><td>Intelli-Studio 2.0</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">Tripod screw</td></tr><tr><td><b>Display</b></td><td>2.7” touch LCD</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">2D S-motion Graphic User Interface</td></tr><tr><td><b>Video Recording</b></td><td>HD resolution:</td></tr><tr><td>&nbsp;</td><td>1920 x 1080 60i</td></tr><tr><td>&nbsp;</td><td>1280 x720 60p</td></tr><tr><td>&nbsp;</td><td>SD resolution:</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">720 x 480 60i</td></tr><tr><td style="border-bottom:1px solid black"><b>Still Image Capture</b></td><td style="border-bottom:1px solid black">4.9MP (2MP)</td></tr><tr><td><b>Device Connectivity</b></td><td>USB interface (including USB charging)</td></tr><tr><td>&nbsp;</td><td>Composite interface (output only)</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">HDMI interface</td></tr><tr><td><b>Battery type</b></td><td>BP125A (1250mAh)</td></tr><tr><td></td><td></td><td>&nbsp;</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">110min (1h50m) max.</td></tr><tr><td><b>Recording time (max.)</b></td><td>SD</td></tr><tr><td>&nbsp;</td><td>SF: 4h10m</td></tr><tr><td>&nbsp;</td><td>F: 5h</td></tr><tr><td>&nbsp;</td><td>N: 6h30m</td></tr><tr><td>&nbsp;</td><td></td><td></td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>HD</td></tr><tr><td>&nbsp;</td><td>SF: 17h30m</td></tr><tr><td>&nbsp;</td><td>F: 23h10m</td></tr><tr><td style="border-bottom:1px solid black">&nbsp;</td><td style="border-bottom:1px solid black">N: 34h</td></tr><tr><td style="border-bottom:1px solid black"><b>Price</b></td><td style="border-bottom:1px solid black">$299</td></tr><tr><td style="border-bottom:1px solid black"><b>Availability</b></td><td style="border-bottom:1px solid black">Feb. 2011</td></tr></table></p>

<p><strong>About Samsung Electronics Co., Ltd.</strong></p>

<p>Samsung Electronics Co., Ltd. is a global leader in semiconductor, telecommunication, digital media and digital convergence technologies with 2009 consolidated sales of US$116.8 billion. Employing approximately 174,000 people in 193 offices across 66 countries, the company consists of eight independently operated business units: Visual Display, Mobile Communications, Telecommunication Systems, Digital Appliances, IT Solutions, Digital Imaging, Semiconductor and LCD. Recognized as one of the fastest growing global brands, Samsung Electronics is a leading producer of digital TVs, memory chips, mobile phones and TFT-LCDs. For more information, please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  3, 2011  9:26 PM</b>
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
			<?=getComments(4119)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4119)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/samsung-innovation-makes-capturing-video-easier-than-ever-with-the-practical-and-affordable-full-hd-hmxq10-camcorder.php" type="text/javascript" charset="utf-8"></script>
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