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
		AND e.entry_id = 3945";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3945 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3945 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3945";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3945";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)" />
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
	<title>HDTV Magazine - DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)</title>
	<meta name="keywords" content="audio canal, aac audio, mbps video, technical aspects, dtv system, digital, canal, analog, video, argentina, part, seg, stb, signal, channel, system, audio, pal, dtv, mobile, isdb, lcd, mbps, world, both" />
	<meta name="description" content="Part 1 offered an overall view. Part 2 covered an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years, regarding how the standard was selected. This Part 3 covers how the system is being implemented, and Part 4 will cover the technical aspects of the system, a better choice for Argentina and several other countries than the US system, although the selection was not technically guided.

Mr. Victor Acuña..." />
	<meta name="title" content="DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Part 1 offered an overall view. Part 2 covered an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years, regarding how the standard was selected. This Part 3 covers how the system is being implemented, and Part 4 will cover the technical aspects of the system, a better choice for Argentina and several other countries than the US system, although the selection was not technically guided.

Mr. Victor Acuña..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3945', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php">DTV Around the World - How ISDB-T is being Implemented as DTV in Argentina (Part 3)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September 16, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=328&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
				<div class="editorial">This article is the third in the "DTV around the World" series. Other articles in this series are:<br> <ul> <li><a href="/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php" target="_blank">Part 1: The US DTV System, Not so Popular Around the World</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php" target="_blank">Part 2: Why Argentina selected ISDB-T after testing US&rsquo;s ATSC</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php" target="_blank">Part 4: Argentina&rsquo;s DTV System &ndash; Technical Aspects</a> </li></ul></div><p>This series of articles is about how terrestrial broadcast <a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29">digital TV</a> is being implemented around the world. <p>Part 1 offered an overall view. Part 2 covered an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years, regarding how the standard was selected. This Part 3 covers how the system is being implemented, and Part 4 will cover the technical aspects of the system, a better choice for Argentina and several other countries than the US system, although the selection was not technically guided. <p><b></b> <p><b>Mr. Victor Acuña, please indicate your role on the implementation of digital TV in Argentina.</b> <p>I am an electronics engineer. I am working in the Committee of Electronics &amp; Telecommunications Technologies in the Argentine Centre of Engineers (CAI) as specialist in Digital Terrestrial Television. I am also a professor in the Institute of Superior Education in Broadcasting (ISER) and in the Universidad Abierta Interamericana (UAI). <p>The challenge to go to digital TV with a very new system gives me the chance to work with the state of the art technology. <p><span class="caption left" style="width:332px"><img alt="Testing the Transport Stream with a PC, while viewing the HD signal with an LCD HDTV; below the LCD is the STB (black box) and over the STB is the little 1-seg screen for mobile/portable. The signal on the oscilloscope is the HD output from the STB." src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldHowISDBTisbeingImplemen_B79/clip_image002_e643a934-6e54-40d9-aed8-e7f938c293cc.jpg" width="330" height="247"><br />Testing the Transport Stream with a PC, while viewing the HD signal with an LCD HDTV; below the LCD is the STB (black box) and over the STB is the little 1-seg screen for mobile/portable. The signal on the oscilloscope is the HD output from the STB.</span> <p><b>Please describe the actual implementation of ISDB-T in Argentina </b> <p>Here in Buenos Aires “Canal 7”, the public state channel in digital channel 23 (527MHz), is the first that is transmitting <u>since late April</u> with a 10KW NEC transmitter and a 150-meter tower; it covers the whole city up to about 30 Kilometers. <p>I am testing the transmission Transport Stream (TS) and it has now (now, because the test signals change all the time) 4 signals on air having the following video payload: one HD using 8.8Mbps for video, two SD using 3Mbps each for video, and the 1-seg using 340Kbps. The total channel data rate is 18.3Mbps as whole TS. <p><span class="caption right" style="width:194px"><img alt="Edificio Ministerio de Salud. Building in downtown, where the transmitters and antennas were installed (the same place than the analog). The antenna for analog is the top post in the tower, the digital is in the bottom (the photo is previous to installing the digital panels)." align="left" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldHowISDBTisbeingImplemen_B79/clip_image005_e1ea68fe-a710-44fa-81f0-94dee4058081.jpg" width="192" height="298"><br />Edificio Ministerio de Salud. Building in downtown, where the transmitters and antennas were installed (the same place than the analog). The antenna for analog is the top post in the tower, the digital is in the bottom (the photo is previous to installing the digital panels).</span>Only one private station “Canal 9”, in digital channel 43 (647MHz), is doing similar transmission tests since January, with a 500W Harris transmitter and a 50-meter tower, the coverage is very small with a radial of 3 km. It has 3 signals in the air, one HD sub-channel with 12Mbps for video, one SD sub-channel with 3.6Mbps for video, and the 1-seg portable/mobile with 320Kbps for video, with a total data rate of 18.2Mbps. <p>The total data rate is obtained from the complete Transport Stream emitted; it includes the audio data, ancillary data, null packets, PAT identification, etc. For example, for Canal 9 the TS is as follows: <br clear="both" /><table class="simple" align="center"><tbody><tr class="header"><td width="39">&nbsp;</td><td width="75">Hex PID</td><td width="102">Stream Type</td><td width="79">Kbps</td><td width="66">Percent</td><td width="113">Service Name</td></tr><tr><td width="39">0</td><td width="75">0x0000</td><td width="102">PAT</td><td width="79">15.83</td><td width="66">0.09</td><td width="113">N/A</td></tr><tr><td width="39">16</td><td width="75">0x0010</td><td width="102">NIT</td><td width="79">1.58</td><td width="66">0.01</td><td width="113">N/A</td></tr><tr><td width="39">17</td><td width="75">0x0011</td><td width="102">SDT</td><td width="79">0.79</td><td width="66">0</td><td width="113">N/A</td></tr><tr><td width="39">20</td><td width="75">0x0014</td><td width="102">TOT</td><td width="79">0.32</td><td width="66">0</td><td width="113">N/A</td></tr><tr><td width="39">36</td><td width="75">0x0024</td><td width="102">?</td><td width="79">1.58</td><td width="66">0.01</td><td width="113">N/A</td></tr><tr><td width="39">4096</td><td width="75">0x1000</td><td width="102">PMT</td><td width="79">15.83</td><td width="66">0.09</td><td width="113">Canal 9 - HD</td></tr><tr><td width="39">4097</td><td width="75">0x1001</td><td width="102">H.264 Video</td><td width="79">12556.97</td><td width="66">68.68</td><td width="113">Canal 9 - HD</td></tr><tr><td width="39">4098</td><td width="75">0x1002</td><td width="102">PCR</td><td width="79">41.67</td><td width="66">0.23</td><td width="113">N/A</td></tr><tr><td width="39">4099</td><td width="75">0x1003</td><td width="102">AAC Audio</td><td width="79">218.99</td><td width="66">1.2</td><td width="113">Canal 9 - HD</td></tr><tr><td width="39">4100</td><td width="75">0x1004</td><td width="102">AAC Audio</td><td width="79">211.51</td><td width="66">1.16</td><td width="113">Canal 9 - HD</td></tr><tr><td width="39">4101</td><td width="75">0x1005</td><td width="102">MPEG Audio</td><td width="79">114.9</td><td width="66">0.63</td><td width="113">Canal 9 - HD</td></tr><tr><td width="39">4112</td><td width="75">0x1010</td><td width="102">PMT</td><td width="79">15.83</td><td width="66">0.09</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4113</td><td width="75">0x1011</td><td width="102">H.264 Video</td><td width="79">3686.49</td><td width="66">20.16</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4115</td><td width="75">0x1013</td><td width="102">AAC Audio</td><td width="79">216</td><td width="66">1.18</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4116</td><td width="75">0x1014</td><td width="102">AAC Audio</td><td width="79">215.97</td><td width="66">1.18</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4117</td><td width="75">0x1015</td><td width="102">MPEG Audio</td><td width="79">114.9</td><td width="66">0.63</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4123</td><td width="75">0x101B</td><td width="102">Private PES</td><td width="79">37.6</td><td width="66">0.21</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4124</td><td width="75">0x101C</td><td width="102">Private PES</td><td width="79">2.15</td><td width="66">0.01</td><td width="113">Canal 9 - SD</td></tr><tr><td width="39">4129</td><td width="75">0x1021</td><td width="102">H.264 Video</td><td width="79">316.64</td><td width="66">1.73</td><td width="113">Canal 9 - Mobile</td></tr><tr><td width="39">4131</td><td width="75">0x1023</td><td width="102">AAC Audio</td><td width="79">70.71</td><td width="66">0.39</td><td width="113">Canal 9 - Mobile</td></tr><tr><td width="39">8139</td><td width="75">0x1FCB</td><td width="102">PMT</td><td width="79">15.81</td><td width="66">0.09</td><td width="113">Canal 9 - Mobile</td></tr><tr><td width="39">8191</td><td width="75">0x1FFF</td><td width="102">Null Packets</td><td width="79">411.86</td><td width="66">2.25</td><td width="113">N/A</td></tr><tr><td width="39">&nbsp;</td><td width="75">&nbsp;</td><td width="102">Total:</td><td width="79">18283.93</td><td width="66">100.02</td><td width="113">&nbsp;</td></tr></tbody></table> <p><span class="caption left" style="width:408px"><img alt="Digital channel 7 racks with a pair of NEC MX-1500 multiplexers in the middle, and the MPEG-4 encoders in the right side, a pair of NEC VC-7010 1-seg at the top, monitors and patcher test in the middle, and a pair of NEC VC-7301 SD/HD" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldHowISDBTisbeingImplemen_B79/clip_image008_6b6214be-b963-4653-aa52-538338b5caa0.jpg" width="406" height="304"><br />Digital channel 7 racks with a pair of NEC MX-1500 multiplexers in the middle, and the MPEG-4 encoders in the right side, a pair of NEC VC-7010 1-seg at the top, monitors and patcher test in the middle, and a pair of NEC VC-7301 SD/HD</span>In both mentioned channels, the SD and the 1-seg transmission replicate the content of the analog channel, but for HD the content is a documental video clip in an endless loop. <p>"Canal 7” was committed to transmit the FIFA World Cup in HD, but after this happens I don’t know what will be broadcasted in HD.  <p><p><b>Regarding the digital panels that have been selling for several years already in Argentina (mainly LCD), are there any compatibility issues of their digital tuning capabilities with the newly selected digital broadcast standard? Do those panels have also the required tuners to support the selected format?, or would they require external digital tuners for the newly selected format?</b> <p>All the analog TV sets sold in Argentina can work in both standards PAL-N and NTSC (they are “binorm”, as we call). Last years’ analog TV sets are “trinorm”, PAL-N, PAL-M, and NTSC. <p>The analog standard we have (PAL-N) is 50 Hz with 625 interlaced lines of vertical resolution (567 active). The SDTV digital signal (the basic digital format) is in 576i/50, which is taken from the station’s actual analog signal, but the TVs (CRT and LCD) can work in 60 Hz as well.  <p>If a digital broadcaster transmits in SD in 50 Hz the STB outputs a PAL-N analog signal, likewise, if a broadcaster transmits digital signals in 60 Hz the receiver system can work with it. In this case, the STB outputs an <a href="http://www.hdtvmagazine.com/glossary.php#NTSC">NTSC</a> signal for the analog TVs (or PAL-M 480i60 similar to NTSC; PAL-N is 567i50), selectable from the STB menu.  <p>The digital screens sold over recent years (mainly LCD, some Plasma) don't have an ISDB-T digital TV tuner, which means those TVs will need a separate STB to tune to the digital terrestrial broadcasts. Some manufacturers are announcing near future models with ISDB-T tuners. One important thing is that some old LCDs don't support HDTV scanned in 50 Hz and they unfortunately will not work with a 1080i/50 HD broadcast signal as is being transmitted now. <p>Just a few TVs with digital tuners are being announced recently, many are LCDs with LED technology. <p><span class="caption left" style="width:305px"><img alt="Close-view of front panel of one of the digital transmitters." src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldHowISDBTisbeingImplemen_B79/clip_image011_c6b36d16-cc93-4c9c-bf5c-eaf714296781.jpg" width="303" height="338"><br />Close-view of front panel of one of the digital transmitters.</span>The business of high-definition television will mobilize an estimated 300 million dollars in Argentina. This corresponds to the total investment required to set the standard operational, including TV operators, technology providers and manufacturers of television sets. <p><b></b> <p><b>Would the basic converter box also be capable to output HD to an HD monitor? Or would only output downconverted analog quality from a tuned HD signal, and only be useful for legacy analog TVs (like it happened in the US)?</b> <p>The politicians, with a social view, want more SD channels, the TV owners and content producers want HD, this is a dilemma for the public TV. But when the private stations come to air, HD sure will be the starlet.  <p>From what I understand, all the STBs that will be offered will have both SD / HD outputs. The STBs that I am testing are coming from Brazil and have all the outputs. <p>According to the specifications published by government these STBs for free distribution will have both outputs, 1) analog video in a RCA jack with PAL-N or NTSC for SD (or a downconverted HD version) to connect to legacy analog TV sets, and 2) a <a href="http://www.hdtvmagazine.com/glossary.php#Component+Video">component-analog</a> (Y, Pb, Pr) or component-digital (Y, Cb, Cr) video switchable output with 3 RCA connectors, as well as an <a href="http://www.hdtvmagazine.com/glossary.php#HDMI">HDMI</a> connector, both to output SD or HD to LCD HDTVs. <p>The STBs also have an USB input to connect a flash drive to see photos and videos directly to the TV, and an Ethernet connector to access to a LAN. In the future this would be the return channel for interactivity, for which they have a Ginga NCL middleware. <p>Additionally, there are low-cost versions of STBs primarily for mobile use (with 12Volts supply) that can be used in cars or portable displays, and there are Dongles for Portable Computers with models that cover Full-seg and 1-seg. <p>In summary, there are two types of digital STB receivers: the full-seg STB that can receive all the signals, and the one-seg to receive only the mobile-portable signal. <p>---------------------<b></b> <p>Thank you Victor for collaborating with this series of articles about DTV around the world. The next article (part 4) will cover the technical aspects of ISDB-T, stay tuned.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September 16, 2010  7:53 AM</b>
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
			<?=getComments(3945)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3945)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php" type="text/javascript" charset="utf-8"></script>
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