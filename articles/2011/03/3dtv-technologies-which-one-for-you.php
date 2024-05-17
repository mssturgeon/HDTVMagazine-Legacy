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
		AND e.entry_id = 4216";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4216 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4216 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4216";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/03/3dtv-technologies-which-one-for-you.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4216";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3DTV Technologies &ndash; Which one for you?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3DTV Technologies &ndash; Which one for you?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3DTV Technologies &ndash; Which one for you?" />
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
	<title>HDTV Magazine - 3DTV Technologies &ndash; Which one for you?</title>
	<meta name="keywords" content="active shutter, shutter glasses, auto stereoscopic, display ces, half resolution, glasses, may, issues, viewing, technology, shutter, active, display, resolution, passive, dtv, cost, stereoscopic, auto, lcd, technologies, ces, time, option, adapt" />
	<meta name="description" content="As mentioned on the previous article, 3DTV is being implemented by different technologies.  According to some the approach causes unnecessary confusion, asserting that the various technologies may eliminate themselves if consumers decide they’d rather not spend on 3D when not knowing what to buy.

I see the choices of technologies as solutions to various issues and preferences people naturally have.  A consumer may not..." />
	<meta name="title" content="3DTV Technologies &amp;ndash; Which one for you?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3DTV Technologies &amp;ndash; Which one for you?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/03/3dtv-technologies-which-one-for-you.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As mentioned on the previous article, 3DTV is being implemented by different technologies.  According to some the approach causes unnecessary confusion, asserting that the various technologies may eliminate themselves if consumers decide they’d rather not spend on 3D when not knowing what to buy.

I see the choices of technologies as solutions to various issues and preferences people naturally have.  A consumer may not..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4216', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/03/3dtv-technologies-which-one-for-you.php">3DTV Technologies &ndash; Which one for you?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>March  9, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<p>As mentioned on the previous article, 3DTV is being implemented by different technologies. According to <a href="http://hdtvprofessor.com/HDTVAlmanac/?p=1395">some</a> the approach causes unnecessary confusion, asserting that the various technologies may eliminate themselves if consumers decide they’d rather not spend on 3D when not knowing what to buy. <p>I see the choices of technologies as solutions to various issues and preferences people naturally have. A consumer may not adapt to a given technology but may adapt to another that offers the chance to enjoy 3DTV. Simultaneous technology offerings facilitate that scenario. <p>On a similar vein, look at the variety of HDTV display technologies offered in parallel over the past decade, such as DLP, LCD, LCD with LED, plasma, LCoS, OLED, and the left behind CRT. Their parallelism facilitated the best selection for the particular viewing environments and user requirements at different price ranges. <p>How does this apply to 3DTVs?  <p><b></b> <p><b>Health/technology Issues</b> <p><span class="caption left" style="width:314px"><img alt="LG Display at CES 2011" src="http://www.hdtvmagazine.us/articles/images/d332c2f70ba7_13660/clip_image002_77d3c60b-01a0-4285-8e11-c8d5d8d77304.jpg" width="312" height="483"><br />LG Display at CES 2011</span>Some viewers experience eye fatigue or discomfort produced by the 3D glasses, crosstalk (image overlapping), flicker (blinking effect), dizziness, nausea, and/or photosensitive seizures. Some issues are produced by poorly made 3D content. Other issues are produced by active-shutter glasses 3D technology. Some consumers “have to have” the full resolution per eye of active-shutter glasses technology and prefer to adapt to its issues. Some would not adapt to the issues and are forced to give up the full resolution solution.  <p>Others noticed that passive polarized glasses eliminated the issues they had with active-shutter glasses and chose the passive technology because it was more important to them to solve the viewing issues, although admitting that the <a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">half-resolution 3D</a> of passive LCD panels is a disadvantage they can live with. Others cannot stand seeing the line structure of half-resolution 3D images on passive LCD panels and would rather run the risk of occasionally experiencing some of the issues of active-shutter glasses. <p><span class="caption right" style="width:325px"><img alt="LG Display at CES 2011" src="http://www.hdtvmagazine.us/articles/images/d332c2f70ba7_13660/clip_image005_9f5e68b6-0a2f-4231-9267-56e434d7c2b0.jpg" width="323" height="451"><br />LG Display at CES 2011</span>Others cannot adapt to the issues of any of the 3D glasses technologies and prefer the no-glasses <a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">auto-stereoscopic 3D</a> technology accepting its higher cost and viewing zones constraints. A darker image when viewed with 3D glasses for long periods may induce viewing strain that some people cannot tolerate, so they may have to buy a brighter LCD panel with local dimming even when they consider plasma to produce a better image. <p>Others would never trade the quality of a plasma panel when viewing 2D 90% of the time just for a brighter LCD to view 3D 10% of the time. LG Display <a href="http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php">claimed</a> a number of issues that prompted their decision of halting their production of active-shutter glasses 3DTVs to instead manufacture 3DTV panels with pattern retarders and polarized glasses. <p><b></b> <p><span class="caption left" style="width:185px"><img alt="LG Display at CES 2011 - Discontinuing Active Shutter Glasses Technology" src="http://www.hdtvmagazine.us/articles/images/d332c2f70ba7_13660/clip_image008_3527f192-f76e-4b59-8675-7f07bf6e9bf9.jpg" width="183" height="314"><br />LG Display at CES 2011 - Discontinuing Active Shutter Glasses Technology</span><b>Viewing style/User Requirement Reasons</b> <p>Some are reluctant to wear 3D glasses because of their need of 3D group-viewing or to avoid the feeling of isolation from other viewers and from the room, so auto-stereoscopic maybe their only option. Some need to view casually while moving around and do not want to remove/replace the glasses to been able to perform activities they currently do while viewing HDTV for which they do not need glasses. An older person does not want to risk an accident because the dark glasses can make the person unaware of a toy on the floor, for example. <p>The cost of group viewing at large gatherings at $150 per active-shutter glasses may be prohibitive compared to the cost of a large auto-stereoscopic panel that anyone can see without 3D glasses, or compared to the cost of a passive panel/front projector with polarized (low-cost) glasses, however, the additional cost of a silver screen for a passive projector solution could render that option unreasonable, not to mention having to have 2 screens, one for 3D, another one for 2D.  <p>The risk of damaging or losing expensive active-shutter glasses during large gatherings or while being mishandled by children makes the extra cost of the auto-stereoscopic alternative a relatively better investment when no glasses are needed, and therefore never at risk. The lower cost of polarized glasses combined with a passive panel/projector maybe a better option for that case if the half-resolution of 3D would not be a quality concern for the occasional 3D viewing. <p><span class="caption left" style="width:410px"><img alt="LG Display CES 2011 Supports Polarized Glasses" src="http://www.hdtvmagazine.us/articles/images/d332c2f70ba7_13660/clip_image012_77943ec5-fc36-44c0-a66b-a105b331af41.jpg" width="408" height="486"><br />LG Display CES 2011 Supports Polarized Glasses</span>However, that may not be an acceptable option for a videophile in pursuit of image quality, even for an occasional viewing of 3D. Another videophile wants to have the setup exclusively dedicated for 3D viewing, and the half-resolution of passive technology is not acceptable, neither auto-stereoscopic method may be considered until 4K/higher resolution panels would allow 1080p resolution per viewing zone/eye for all viewers. This means that the active-shutter glasses technology at full 1080p resolution per eye may be the most quality-appropriate option for that videophile at the present time. However, if the viewer has sensitivity issues with active-shutter glasses, the viewing of 3D at full resolution may not be possible today and some tradeoffs may have to be accepted. <p>In other words, having <a href="/articles/2011/03/3dtv-are-competing-technologies-necessary-including-autostereoscopic.php">more than one technology</a> increases the possibilities of adopting 3D rather than the opposite.  <p><span class="caption right" style="width:261px"><img alt="LD Display CES 2011 - Press Meeting Announcing Discontinuation of Active-Shutter 3D Technology" src="http://www.hdtvmagazine.us/articles/images/d332c2f70ba7_13660/clip_image014_e7d04d88-0e14-48a9-b7aa-e19ba7a6e08b.jpg" width="259" height="145"><br />LD Display CES 2011 - Press Meeting Announcing Discontinuation of Active-Shutter 3D Technology</span><b>Are you part of the 20% or the 80%? </b> <p>Several sources claimed that 5 to 20% of viewers may have health issues with 3D. Some issues were actually identified as visual limitations of the person and were discovered as a consequence of their exposure to 3D, similarly to some visual conditions found on children that may not have been noticed otherwise. <p>The percentages above originate from various formal and informal sources and they seem to not only include those affected by the use of active-shutter glasses and other 3D technology factors, but also include people that experienced visual discomfort at the local theater using passive glasses, such as fatigue and headaches, when rapidly trying to adjust to excessive variations of positive and negative depth present in some poorly made 3D content during an extended period of time, and issue that would affect the viewing of that content using any 3DTV technology, whether is viewed with 3D glasses or not (auto-stereoscopic). <p>To address that issue <a href="/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php">3DFusion has developed</a> a proprietary “knob type” of depth control that allows the depth of any 3D content to be adjusted in real-time at any point in the creation/distribution process from the camera to the display device, including giving the viewer the power to adjust the 3D depth of the content to suit his/her comfort level and diminish the effect that may produce fatigue and visual discomfort. <p>Some negative publicity suggested that 3DTV should not move forward and manufacturers should not be pushing 3DTV, or should not make some type of 3DTVs (such as LG Display decided to do with active-shutter glasses technology, as mentioned above). <p>I recently attended the <a href="http://www.3dathome.org/">3D@Home Consortium</a> meeting at CES 2011. The Consortium reported that a dedicated group was assigned the task of <a href="/columns/2010/06/hdtv-almanac-is-3dtv-bad-for-you.php">evaluating the health</a> issues of 3D. The group is headed by an industry professional judged by the Consortium as with ample experience in this subject. <p>On the other hand, considering the overwhelming success of some 3D movies at local theaters such as Avatar, the health concerns appear to affect a small fraction of the public. Even if the 20% estimate is confirmed by appropriate research, a manufacturer may see no reason to stop making a product that 80% of consumers can still use and the other 20% is not forced to buy, and if they do buy they still have a top-of-the-line TV set that is an excellent display device for 2D HDTV, and have the choice not to use the 3D feature (as often or ever; the consumer is always in control of his/her 3D factors of concern). <p>As with all products some consumers may have limitations regarding some of their features. Proper guidelines should be provided to warn/inform consumers of known issues before they open the box, which is what <a href="http://www.samsung.com/au/tv/warning.html">some companies</a> have actually done with 3DTVs, although seemingly protecting themselves from possible legal ramifications. <p>Next article: <a href="/articles/2011/03/is-3dtv-a-replacement-of-digital-television-would-2d-viewing-be-affected.php">Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?</a>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>March  9, 2011  7:05 AM</b>
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
			<?=getComments(4216)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4216)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/03/3dtv-technologies-which-one-for-you.php" type="text/javascript" charset="utf-8"></script>
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