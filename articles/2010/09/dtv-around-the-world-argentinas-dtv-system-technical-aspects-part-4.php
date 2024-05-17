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
		AND e.entry_id = 3984";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3984 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3984 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3984";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3984";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Around the World - Argentina&rsquo;s DTV System &ndash; Technical Aspects (Part 4)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Around the World - Argentina&rsquo;s DTV System &ndash; Technical Aspects (Part 4)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Around the World - Argentina&rsquo;s DTV System &ndash; Technical Aspects (Part 4)" />
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
	<title>HDTV Magazine - DTV Around the World - Argentina&rsquo;s DTV System &ndash; Technical Aspects (Part 4)</title>
	<meta name="keywords" content="uses mpeg, dtv world, dtv system, being implemented, mpeg avc, aac, system, dtv, audio, part, argentina, mpeg, standard, video, segments, digital, series, dolby, quality, channel, world, technical, portable, high, used" />
	<meta name="description" content="In part 1, I offered an overall view. Part 2  covered an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years regarding how the standard was selected. Part 3  covered how the system is being implemented. This part 4 covers the technical aspects of that system, a better choice for Argentina and several other countries than the US system, although the selection was not technically guided." />
	<meta name="title" content="DTV Around the World - Argentina&amp;rsquo;s DTV System &amp;ndash; Technical Aspects (Part 4)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Around the World - Argentina&amp;rsquo;s DTV System &amp;ndash; Technical Aspects (Part 4)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In part 1, I offered an overall view. Part 2  covered an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years regarding how the standard was selected. Part 3  covered how the system is being implemented. This part 4 covers the technical aspects of that system, a better choice for Argentina and several other countries than the US system, although the selection was not technically guided." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3984', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php">DTV Around the World - Argentina&rsquo;s DTV System &ndash; Technical Aspects (Part 4)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September 23, 2010</b>
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
				<div class="editorial">This article is the fourth in the "DTV around the World" series. Other articles in this series are:<br> <ul> <li><a href="/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php" target="_blank">Part 1: The US DTV System, Not so Popular Around the World</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php" target="_blank">Part 2: Why Argentina selected ISDB-T after testing US&rsquo;s ATSC</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php" target="_blank">Part 3: How ISDB-T is being Implemented as DTV in Argentina</a> </li></ul></div><p>This series of articles is about how terrestrial broadcast <a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29">digital TV</a> is being implemented around the world. <p>In <a href="http://www.hdtvmagazine.com/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php" target="_blank">part 1</a>, I offered an overall view. <a href="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php" target="_blank">Part 2</a> covered an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years regarding how the standard was selected. <a href="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php" target="_blank">Part 3</a> covered how the system is being implemented. This part 4 covers the technical aspects of that system, a better choice for Argentina and several other countries than the US system, although the selection was not technically guided.  <p><img align="left" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldArgentinasDTVSystemTech_A337/clip_image002_0ed617dd-bac9-4a7c-91ad-fe6749a62f54.jpg" width="212" height="271"><p><b>Mr. Victor Acuña, please indicate your role on the implementation of digital TV in Argentina.</b> <p>I am an electronics engineer. I am working in the Committee of Electronics &amp; Telecommunications Technologies in the Argentine Centre of Engineers (CAI) as specialist in Digital Terrestrial Television. I am also a professor in the Institute of Superior Education in Broadcasting (ISER) and in the Universidad Abierta Interamericana (UAI). <p>The challenge to go to digital TV with a very new system, gives me the chance to work with the state of the art technology. <p><b>Please describe the technical video and audio characteristics of the selected standard for SD and HD, as well as the compression requirements and bandwidth utilization, including the mobile DTV component.</b> <p>This standard takes advantage from the most modern compression techniques; it uses MPEG4/AVC (H.264) for video coding and High Efficiency – Advanced Audio Codec (HE-AAC) for audio coding.  <p>The modulation scheme is made in hierarchical and segmented Orthogonal Frequency-Division Multiplexing (OFDM), which offers the possibility to transport different signals with different robustness (Multiprogramming scheme).  <p><span class="caption left" style="width:375px"><img alt="The racks for the Digital Terrestrial Television in the Technical Operations Master room. Some engineers listening to the details of the operation." align="left" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldArgentinasDTVSystemTech_A337/clip_image004_746187d7-f3da-41e7-b005-6d2d055e8fa1.gif" width="373" height="304"><br />The racks for the Digital Terrestrial Television in the Technical Operations Master room. Some engineers listening to the details of the operation.</span>It processes Error Correction by Time Interleaving and Frequency Interleaving. It has the possibility to send datacasting by a data carrousel, and has interactive TV middleware using a declarative language Ginga-NCL and a procedural language Ginga-J. <p>As the quality requested is the inverse to the coverage (because the guard interval, FEC spaces, and other parameters needed to obtain robustness, decrease the payload bit-rate usable by the video), there will be a need to select the right parameters to find the best compromise. This system has many options. <p>This standard transmits video in MPEG-4 AVC HP@L4 (Advanced Video Coding, High Profile, Level 4) for fixed (or mobile) reception, and in MPEG-4 AVC BP@L1.3 (AVC, Base Profile, Level 1.3) for Portable reception. <p><b></b> <p><b>Could you please indicate the resolutions this system works with?</b> <p>The resolutions can be: <p>Fixed: <ul> <li>SD 720x480i (60) <li>SD 720x480p (60) <li>SD 720x576i (50) <li>SD 720x576p (50) <li>HD 1280x720p (50 or 60) <li>HD 1920x1080i (50 or 60)</li></ul> <p>Portable: <ul> <li>SQVGA (160x120 or 160x90) (15, 25 or 30) <li>QVGA (320x240 or 320x180) (15, 25 or 30) <li>CIF (352x288) (15, 25 or 30)</li></ul> <p><b>What is the audio capability of this standard?</b> <p>It can transmit mono, stereo or multichannel, typically as 5.1 audio channels.  <p>In Stereo it uses: MPEG-4 AAC@L2 or MPEG-4 HE-AAC v1@L2.  <p>In Multi Channel 5.1 it uses: MPEG-4 AAC@L4 or MPEG-4 HE-AAC v1@L4.  <p>In Portable service it uses: MPEG-4 HE-AAC v2@L2, only in stereo audio or 2 mono channels. <p>The AAC-LC (low complexity) is very good for high bitrates and high quality, consider this as the “normal AAC”. HE-AAC is better for low bitrates. <p>In comparison with the Dolby AC3 standardized in the ATSC A/52, the AAC was evaluated to be the most data-efficient codec. In the compression level HE-AAC doubles the payload of the AC3. For example an AC3 @ 448kbps sounds like an HE-AAC @ 210kbps. <p>But, in the control level the Dolby AC3 allows the setting of up to 28 metadata parameters, while the AAC standardized in the ABNT-NBR 15602-2 allows only 3. Dolby has developed the Dolby Pulse, an AAC with powerful metadata, which in my opinion is the next step, as Dolby upgrades the future implementations in the audio services for broadcasting. <p><b>Please describe how the bandwidth of the allotted channel is used </b> <p>The broadcast transmission format in Argentina is interlaced 50fps all the time, but this standard can also work in 60fps if needed.  <p>The segmented OFDM modulation scheme format divides the allotted 6 MHz channel in 14 segments, one segment is used as separation from adjacent channels (half in each end of the 6MHz), and the remaining 13 segments are for video/audio signals.  <p>The segment located in the center of the 6MHz channel is used for portable/mobile transmission; it is called one-segment (1-seg). Its capacity depends of various parameters which detail is long to explain here, but the typical usage gives up to 430 kbps using QPSK as a typical modulation scheme. With a 320x240 screen, it is ideal for portable devices (i.e., cellular phones). In our signal tests, it was able to be received indoor, inside the city, without any problems. <p>The remaining 12 segments can be used in different configurations, conditioned by the quantity of signals one wants to put in the air. With 64QAM we can have about 1.5 Mbps per segment; an HD 1080i/50 signal would need 8 segments for a 12Mbps transmission with subjectively good quality.  <p><span class="caption right" style="width:427px"><img alt="Technical Operations Master console adapted for the DTT." align="left" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldArgentinasDTVSystemTech_A337/clip_image007_179e62df-42d9-47df-aee9-6ea419f8691f.gif" width="425" height="320"><br />Technical Operations Master console adapted for the DTT.</span>For SD, in 576i/50, 3 segments give a quality similar to analog TV. A good proportion is 9 (or 8) segments for HD, 3 (or 4) for SD, and 1 for mobile. If you want to add datacasting this proportion is lowered. <p>If you don’t need HD, the 12 segments can give 4 good SD signals (or 6 of lower quality); this multi-program transmission option is not allowed for private stations in Brazil. It is reserved only for the federal government TV channels. I suspect that the same will happen in Argentina when the system is set operational, differently than what Japan accomplished, where all configurations are allowed. <p>With the datacasting possibility you can send such information over a TV channel. We are doing an academic R&amp;D over the Ginga language middleware, developed in Brazil, to obtain a useful interactivity model. For now only a few STBs will have such feature to interact, in a first step, with data stored in the STB, but in the future full interactivity will be fulfilled, when a return channel is defined.  <p><b></b> <p><b>Is there anything else that you want to add about this DTV effort in Argentina?</b><b></b> <p>The change will be very important. So far the system has been implemented primarily by government efforts but in the future all the players need to cooperate. Our hard work in accomplishing this effort not only creates enormous possibilities for new jobs but also benefits the country. The most important thing ahead is educating the public to show what DTV is and how it can change their lives.  <p>Now the home experience will change with the use of these new HDTV Sets including their use as computer displays, and the possibility to see high quality movies with Blu-ray players. <p>Rodolfo comments: <i>which at 1080i60 for pre-recorded video content or 1080p24 for film based movies internally converted to output as 1080i60, the players and the discs should be able to be viewed on Argentina’s DTVs because they can sync to 50 or 60 Hz (although the terrestrial broadcast standard is 1080i50.</i> <p>Of course the audio side of the TV home system needs to be improved in the same way, and Rodolfo adds: <i>audio/video receivers normally decoding Dolby Digital and DTS would have to also decode Argentina’s chosen audio standard of MPEG-4 / AAC of DTV broadcast.</i> <p>My personal target is now the control of the audio levels in digital content, and to make a recommendation for the establishment of an important loudness standard. <p><b>-------------------------------------------------------- </b> <p>Thanks Victor for your collaboration on this series of articles and I wish your testing and implementation to go as smooth as you have planned. <p>This concludes this part 4 of this series about DTV around the world. Perhaps in the near future I will produce additional articles in this series as the implementation of DTV around the world develops. Until next time.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September 23, 2010  9:35 AM</b>
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
			<?=getComments(3984)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3984)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php" type="text/javascript" charset="utf-8"></script>
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