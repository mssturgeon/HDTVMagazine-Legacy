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
		AND e.entry_id = 3464";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3464 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3464 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3464";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3464";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" />
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
	<title>HDTV Magazine - Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User</title>
	<meta name="keywords" content="flash memory, memory camcorders, estimated retail, new vixia, retail price, vixia, video, canon, new, memory, camcorders, flash, series, canon’s, recording, image, advanced, touch, definition, available, feature, retail, respectively, full, estimated" />
	<meta name="description" content="Canon U.S.A., Inc, a leader in digital imaging technology, today announced an exciting new line of nine VIXIA High Definition flash memory camcorders. The 2010 high-definition lineup includes Canon’s flagship VIXIA HF S-series, the compact VIXIA HF M-series and a new entry-level VIXIA HF R-series. Some new features enhancing Canon’s 2010 lineup include..." />
	<meta name="title" content="Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Canon U.S.A., Inc, a leader in digital imaging technology, today announced an exciting new line of nine VIXIA High Definition flash memory camcorders. The 2010 high-definition lineup includes Canon’s flagship VIXIA HF S-series, the compact VIXIA HF M-series and a new entry-level VIXIA HF R-series. Some new features enhancing Canon’s 2010 lineup include..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3464', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php">Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2010</b>
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
				<p class="prtitle">Canon U.S.A. Introduces a Powerful New VIXIA Lineup to Meet the Needs of Every User</p>

<center><i>The New VIXIA Line Offers Advanced Touch Screen and Tracking Technologies Putting Ease-of-Use at Your Fingertips</center></i><br />
<br />

<p><strong>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)--</strong>Canon U.S.A., Inc, a leader in digital imaging technology, today announced an exciting new line of nine VIXIA High Definition flash memory camcorders. The 2010 high-definition lineup includes Canon’s flagship VIXIA HF S-series, the compact VIXIA HF M-series and a new entry-level VIXIA HF R-series. Some new features enhancing Canon’s 2010 lineup include a new Touch Panel LCD with an advanced tracking feature helping keep any subject - such as people, pets, or cars - in focus and properly exposed, even in a busy scene. Canon’s new VIXIA lineup also includes an enhancement to its image stabilization system and an all-new HD-to-SD Downconversion feature allowing video to be easily uploaded to the web or burned onto DVDs. Select 2010 VIXIA camcorders are compatible with Eye-fi SD Memory Cards, allowing for wireless uploading of video content to a computer or favorite video sharing site via the Eye-fi card’s wireless capabilities.</p>

<p>“Canon’s new 2010 VIXIA Flash Memory camcorders deliver superior high-definition image quality in a compact, lightweight design and offer a host of new features to make capturing and sharing video easier than ever before," said Yuichi Ishizuka, senior vice president and general manager, Consumer Imaging Group, Canon U.S.A.</p>

<p>All of the new 2010 VIXIA High Definition camcorders retain Canon’s proprietary imaging technologies – a Genuine Canon HD Video Lens, HD CMOS Image Sensor and DIGIC DV III Image Processor. The Canon Full HD CMOS Image Sensor and DIGIC DV III Image Processor have been further improved to reduce noise under low-light conditions and enhanced to deliver more faithful reproduction of purple and blue tones – for both video and photos. All of these proprietary technologies combine together to produce Full HD video that is stunningly lifelike with astonishing detail and clarity.</p>

<p><br />
<strong>New Advanced Features:</strong><br />
<ul><li><strong>Smart Auto</strong>: The Smart Auto mode makes shooting great video even easier by utilizing Canon’s DIGIC DV III Image Processor to intelligently detect and analyze brightness, color, distance and movement and automatically select the best setting for the scene being recorded.</li><li><strong>Touch &amp; Track</strong>: Canon’s new Touch &amp; Track technology enables users to select a subject on the Touch Panel LCD that the camcorder will then recognize and track. This sophisticated technology recognizes faces, objects, even animals, ensuring that your subject will always be in focus and properly exposed.</li><li><strong>Relay Recording</strong>: Relay Recording allows users to capture uninterrupted video when the primary recording media is full. The camcorder will continue to record a scene by switching from one memory source to the other as it fills up, so that you won’t miss a moment of action.</li><li><strong>Powered IS</strong>: In addition to Canon’s Dynamic SuperRange Optical Image Stabilization, Powered IS provides an even higher level of compensation for subtle hand movement at the telephoto end of the zoom range. This new enhancement can be engaged by pressing the Powered IS button on the LCD panel.</li><li><strong>HD-to-SD Downconversion</strong>: A new HD-to-SD Downconversion feature enables users to convert recorded high-definition video to standard-definition files while preserving the original HD video. These standard-definition files make it even more convenient to share video online or create a DVD.</li><li><strong>Advanced Video Snapshot</strong>: Advanced Video Snapshot mode has been upgraded to provide the flexibility of capturing 2, 4, or 8 second video clips while recording or during playback.</li></ul></p>

<p><br />
<strong>VIXIA HF S-series:</strong></p>

<p>The Canon VIXIA HF S21*/**, VIXIA HF S20*/** and VIXIA HF S200*/** Flash Memory camcorders are Canon’s premiere camcorders with professional and easy-to-use features to allow anyone to capture outstanding HD video quality. The VIXIA HF S-series comes equipped with varying levels of internal flash memory and all feature two SD card slots for maximum storage capacity and easy video transfer. The VIXIA HF S21 and VIXIA HF S20 camcorders incorporate 64GB and 32GB of internal flash memory, respectively, and the VIXIA HF S200 records video directly to removable SD memory cards. Recording Full 1920 x 1080 HD video, these camcorders feature a Genuine Canon 10x HD Video Lens and a Canon 1/2.6-inch, 8.59-megapixel Full HD CMOS Image Sensor for stunning video and outstanding photos up to 8.0 megapixels. All three models in the VIXIA HF S-series include Canon’s new 3.5-inch High Resolution (922,000-dot) Touch Panel LCD screen for a large, bright display and easy menu navigation, including Touch &amp; Track technology. All of the models in this series also feature Canon’s Smart Auto, Relay Recording, Powered IS, HD-to-SD Downconversion, and Advanced Video Snapshot.</p>

<p>In addition, the VIXIA HF S-Series includes a host of professional features such as a built-in LANC terminal, and Native 24p (AVCHD) recording. For shooting outside on a sunny day, the VIXIA HF S21 includes a viewfinder which offers a reliable viewing environment when shooting in bright outdoor conditions. The VIXIA HF S21, VIXIA HF S20 and VIXIA HF S200 Flash Memory camcorders are scheduled to be available in April, and will have an estimated retail price of $1399.99, $1099.99 and $999.99 respectively.</p>

<p><br />
<strong>VIXIA HF M-series:</strong></p>

<p>The Canon VIXIA HF M31*/**, VIXIA HF M30*/** and VIXIA HF M300*/** Flash Memory camcorders offer consumers stunning HD video in an ultra-sleek, compact and lightweight body. The VIXIA HF M31 and VIXIA HF M30 incorporate 32GB and 8GB of internal flash memory, respectively, and the VIXIA HF M300 records video directly to an SD memory card. Recording Full 1920 x 1080 HD video, these camcorders include a Genuine Canon 15x HD Video Lens, a 2.7-inch Touch Panel LCD with Touch &amp; Track technology, Smart Auto, Powered IS and Advanced Video Snapshot. In addition, the VIXIA HF M31 and VIXIA HF M30 models both include Canon’s Relay Recording and HD-to-SD Downconversion. The VIXIA HF M31, VIXIA HF M30 and VIXIA HF M300 Flash Memory camcorders are scheduled to be available in April for an estimated retail price of $799.99, $699.99 and $679.99 respectively.</p>

<p><br />
<strong>VIXIA HF R-series</strong></p>

<p>The Canon VIXIA HF R11*/**, VIXIA HF R10*/** and VIXIA HF R100*/** Flash Memory camcorders are perfect for the budget-conscious consumer who wants Full 1920 x 1080 HD video. The VIXIA HF R11 and VIXIA HF R10 models incorporate 32GB and 8GB of internal flash memory, respectively, and the VIXIA HF R10 records directly to an SD memory card. All three models also include a Genuine Canon 20x HD Video Lens, Dynamic IS, Smart Auto and Advanced Video Snapshot. Both the VIXIA HF R11 and VIXIA HF R10 feature Canon’s Relay Recording and HD-to-SD Downconversion. Additionally the VIXIA HF R10 will be available in three stylish colors, black, red, and silver. The VIXIA HF R31, VIXIA HF R30 and VIXIA HF R300 Flash Memory camcorders will be available in March for an estimated retail price of $699.99, $549.99 and $499.99 respectively.</p>

<p><br />
<strong>FS series:</strong></p>

<p>In addition to the new VIXIA High Definition lineup, Canon is also introducing two standard-definition camcorders, the FS31*/**, and FS300*/** Flash Memory camcorders. The Canon FS31 model records to 16GB of internal flash memory, while the FS300 records video directly to an SD memory card. Wrapped in a small and attractive package, the FS-series offers 41x Advanced Zoom to help capture great video even at extreme telephoto distances, as well as Dynamic IS. In addition, the Canon FS300 will be available in three fashionable colors, silver, red, and blue. The Canon FS31 and FS300 Flash Memory camcorders are available in March for an estimated retail price of $349.99 and $299.99 respectively.</p>

<p><br />
<strong>New Optional Camcorder Accessories</strong></p>

<p>The new Canon WP-V2 Waterproof Case allows you to capture exciting HD footage underwater, up to depths of 130 feet, with any of the VIXIA HF M-series Flash Memory camcorders. The ultimate camcorder accessory for underwater enthusiasts, this compact and lightweight housing seals the camcorder, allowing easy on-camera operation and control. The Canon WP-V2 Waterproof Case will be available in April for an estimated retail price of $599.</p>

<p>Also new from Canon is the SM-V1 5.1-Channel Surround Microphone for the ultimate home theater experience. This new microphone is compatible with the VIXIA HF S-series and VIXIA HF M-series, allowing you to capture lifelike sound from all directions. The Canon SM-V1 5.1-Channel Surround Microphone will be available in April for an estimated retail price of $250.</p>

<p><br />
<strong>About Canon U.S.A., Inc.</strong></p>

<p>Canon U.S.A., Inc., is a leading provider of consumer, business-to-business, and industrial digital imaging solutions. Its parent company, Canon Inc. (NYSE:CAJ), a top patent holder of technology, ranked third overall in the U.S. in 2008†, with global revenues of US $45 billion, is listed as number four in the computer industry on Fortune Magazine's World’s Most Admired Companies 2009 list, and is on the 2009 BusinessWeek list of "100 Best Global Brands." Canon U.S.A. is committed to the highest levels of customer satisfaction and loyalty, providing 100 percent U.S.-based consumer service and support for all of the products it distributes. At Canon, we care because caring is essential to living together in harmony. Founded upon a corporate philosophy of Kyosei – "all people, regardless of race, religion or culture, harmoniously living and working together into the future" – Canon U.S.A. supports a number of social, youth, educational and other programs, including environmental and recycling initiatives. Additional information about these programs can be found at <a target="_blank" href="http://www.usa.canon.com/kyosei/">www.usa.canon.com/kyosei</a>. To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting <a target="_blank" href="http://www.usa.canon.com/rss/">www.usa.canon.com/rss</a>.</p>

<p>†Based on weekly patent counts issued by United States Patent and Trademark Office.</p>

<p>* This device has not been authorized as required by the rules of the Federal Communications Commission. This device is not, and may not be offered for sale or lease, or sold or leased, until authorization is obtained.</p>

<p>** A Product Report required by 21 C.F.R. § 1002.10 has not been submitted to the United States Food and Drug Administration for this product. This product is not, and may not be, offered for sale or lease, or sold or leased, until the required report has been submitted.</p>

<p>Prices, specifications and availability are subject to change without notice. Prices are estimated retail prices. Actual selling prices are set by dealers and may vary.</p>

<p>All referenced product names, and other marks, are trademarks of their respective owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2010 10:18 AM</b>
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
			<?=getComments(3464)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3464)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/canon-usa-introduces-a-powerful-new-vixia-lineup-to-meet-the-needs-of-every-user.php" type="text/javascript" charset="utf-8"></script>
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