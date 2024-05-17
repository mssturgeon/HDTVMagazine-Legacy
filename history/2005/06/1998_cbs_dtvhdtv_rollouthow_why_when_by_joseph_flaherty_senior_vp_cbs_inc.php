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
		AND e.entry_id = 130";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 130 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 130 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 130";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1998_cbs_dtvhdtv_rollouthow_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (5) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 130";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc." height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc." />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc." />
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
	<title>HDTV Magazine - 1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc.</title>
	<meta name="keywords" content="per second, pixels per, hdtv format, per frame, dtv receivers, hdtv, format, per, dtv, digital, quality, formats, frame, cbs, display, pixels, second, receivers, programs, today, sdtv, flaherty, system, equipment, transition" />
	<meta name="description" content="By Joseph Flaherty April 6, 1998 Snell &amp; Wilcox says it all! The Dinosaurs are gone! Adapt or Die! Unlike the dinosaurs, CBS doesn't live in the past, and won't become extinct! As you have just heard from Mike Jordan..." />
	<meta name="title" content="1998 - CBS DTV/HDTV Rollout--How, Why, &amp; When by Joseph Flaherty, Senior vp, CBS. Inc." />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1998 - CBS DTV/HDTV Rollout--How, Why, &amp; When by Joseph Flaherty, Senior vp, CBS. Inc." />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1998_cbs_dtvhdtv_rollouthow_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="By Joseph Flaherty April 6, 1998 Snell &amp; Wilcox says it all! The Dinosaurs are gone! Adapt or Die! Unlike the dinosaurs, CBS doesn't live in the past, and won't become extinct! As you have just heard from Mike Jordan..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=130', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1998_cbs_dtvhdtv_rollouthow_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php">1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc.</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 26, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p>By Joseph Flaherty<br />
April 6, 1998</p>

<p><strong>Snell & Wilcox says it all! The Dinosaurs are gone! Adapt or Die!</strong></p>

<p>Unlike the dinosaurs, CBS doesn't live in the past, and won't become extinct! As you have just heard from Mike Jordan and Bill Korn, CBS will make the digital transition, will do it on the FCC schedule, and will ensure that American viewers will see full HDTV, starting this Fall with a primetime HDTV schedule in the 1080I full HDTV format.</p>

<p>Your network has been our industry's leader in recognizing the vital importance of the digital transition to broadcasting's future. As far back as 1988, CBS set the DTV goals for the FCC mandated transition to digital transmission.</p>

<p>They were, and are:</p>

<p>- To ensure that terrestrial broadcasters will be able to deliver a fully competitive digital TV and HDTV service; </p>

<p>- To provide sufficient VHF and UHF spectrum for terrestrial broadcasters to effect the transition to digital transmission, replicating their present coverage area; - To preserve the value of existing TV receivers, and thus the existing TV audience, during the transition to digital TV and HDTV;</p>

<p>- To provide technical headroom to ensure future competitive parity for broadcasting as digital technologies improve.</p>

<p>We believe that these goals have been met in the ATSC digital television standard. The range of digital formats from SDTV to full HDTV at 1080 lines, 1920 pixels/line, interlace and progressively scanned will permit broadcasters to compete with all comers, now and in the future.</p>

<p>The digital channels have been assigned using the last VHF and UHF spectrum available for terrestrial broadcasting. Any lack of use of these channels will result in their loss and assignment to other services. DTV is "now or never" for broadcasters!</p>

<p>The NTSC audience will be served on the existing analog charnel and on the new digital receivers, thus, maintaining our audiences throughout the transition period.</p>

<p>The broadcast of 1080 HDTV programs will ensure that the 1080 HDTV decoders are integrated into the DTV receivers. <br />
The ATSC standard has the headroom to accommodate improvements in compression and transmission technologies that will keep terrestrial broadcasting in the competitive race for 21st century viewers. For example, full 1080 line progressive scan live pictures transmitted at 60 frames-per-second in our 6 MHz channel will be practical before the DTV transition period is over. steps in this direction are to be seen on the floor of this convention today.</p>

<p>As the DTV services are launched, we believe that the most common DTV formats will be 1080I & P for HDTV, and 480I for SDTV.</p>

<p>As you know, the 1080 format is transmitted in the interlace mode for electronically generated programs and in the progressive mode for film programs. In the live mode, the 1080 format provides 1.421,000 displayable pixels-per-frame, taking account of Kell factor losses.</p>

<p>The 1080P mode for film programs provides 1,866,000 displayable pixels per frame now.</p>

<p>The 720P HDTV format provides 829,000 displayable pixels per frame, or 592,000 fewer pixels-per-frame than 1080I, and 1,037,000 fewer pixels-per-frame than 1080P. In short, the 1080 HDTV format is simply the best quality HDTV format by a large margin.</p>

<p>Both 480I & P are Standard Definition (SDTV) systems. The 460I format has 236,000 displayable pixels-per-frame, and the 480P format has 304,000 displayable pixels-per-frame.</p>

<p>Naturally, these digital SDTV formats are still better than NTSC, which in digital terms, would have just 147,862 pixels-per-frame, or just 10t of the 1080I format.</p>

<p>The reason that the 480I SDTV format will be widely used is simply that all the TV stations in the country are already fully equipped with 4801, 60 fields-per-second equipment today. A move to 480P throughout their plants would require significant reinvestment - an investment offering only a marginal quality improvement over a 480I system.</p>

<p>Further, for multiplex broadcasting purposes, a 480I, 60 fields-Per-Second system, requiring about 5 Mb/s in transmission, supports an additional multiplex channel over a 480P, 60 frame-per-second system which requires about 8 mb/sec.</p>

<p>Picture quality for all the HDTV and SDTV formats is also greatly affected by the smoothness of the motion portrayal, that is, by the number of pictures transmitted-per-second. All American TV systems have always operated at 60 pictures per second to obtain smooth motion portrayal. A system operated at 30 frames-per-second to conserve transmission bit rate will exhibit poor motion portrayal as a function of The broadcast of 1080 HDTV programs will ensure that the 1080 HDTV decoders are integrated into the DTV receivers. <br />
the movement in the scene. Slow frame rates, such as the 24 frame-per-second film rate exhibit judder, strobing, and other artifacts. It's the reason the wheels go backwards in film and objects move across the screen in jerks. It is unlikely that 30 frame motion portrayal will be acceptable to the viewing audience or to our commercial clients for "live", or electronically produced programming.</p>

<p>Thus, CBS will use the 1080I & P HDTV format at 60 pictures-per-second because:</p>

<p>- It is the highest quality HDTV Format and puts us in the best competitive position with DBS and cable program distributors who have already announced the adoption of the 1080I & P format for their HDTV programs.</p>

<p>- The broadcast of 1080 HDTV programs will ensure that the 1080 HDTV decoders are integrated into the DTV receivers. Broadcasting has always depended on all the facilities needed to receive broadcast signals being within the receiver itself. DBS and cable have supplied, and always can supply, set top boxes to decode whatever they wish to sell. without HDTV decoders in the new DTV receivers, broadcasters would be locked out of competing in the HDTV marketplace,</p>

<p>- 1080I equipment is the least expensive HD equipment because it is already in its third and fourth generation and well down the price erosion curve. Among other things, this is due to the widespread use of 1000 plus line interlace formats worldwide. There are no progressive scan HDTV systems in-use, or planned, anywhere in the World outside of the U.S.</p>

<p>- The 1080I format is the "Common Image Format" standard established by the International Telecommunications Union (ITU) for international high definition production and program exchange.</p>

<p>All digital TV and HDTV receivers are being built to receive and decode all the ATSC formats but to display only the 1080I and 480 I & P formats. No receivers are being built to display the 720P format due to the excessive cost of the high frequency scanning system and to the need to switch horizontal scanning within the receiver. 720P signals will be down converted to 480I or P or upconverted to 1080I with the quality losses attendant thereto.</p>

<p>The information now available indicates that:</p>

<p>-CBS, NBC, HBO, MSG, Warner Bros., PBS, DirectTV, and the<br />
Discovery Channel, will use the 1080I & P HDTV format,</p>

<p>-ABC plans to use the 720P HDTV format,</p>

<p>-Fox plans to use only the 480P SDTV format,</p>

<p>-TCI/Microsoft plans to use the Microsoft 480P IIHD-011 SDTV format with a 720P & 1080I & P "pass through" as a premium, higher cost, option.</p>

<p>This broadcaster format list is attached as Figure 1.</p>

<p>At the opening of the convention the scorecard of DTV formats being planned, or offered, by major equipment manufacturers is shown in Figure 2. Note the dominance of 1080I equipment and the near absence of yet-to-be-designed 720P equipment. The main application of 720P is presently found only in format converters.</p>

<p>In the process of picking your DTV broadcasting format, beware of format demonstrations. Most are severely flawed and proponents speak highly of their product on the carton. An accurate comparison of the native quality of several formats is very difficult to produce and display. Such a comparison starts with the scenic elements, matched angles of view, the quality of the lenses, the pre-filtering and enhancement in the cameras, the filtering and response of the tape machines, and most importantly the aperture response of the display devices electronically and their resolution in actual light output.</p>

<p>This last element, the display device, is the biggest problem in critical comparison tests. Viewing HDTV today is a bit like Mark Twain's comment that "Wagner's music is better than it sounds". Today, HDTV is better than it looks! The display devices are the limiting quality factor. While improvements are being made by the month, as of today, no display achieves the full quality potential of America's HDTV system.</p>

<p>This is as it should be! The new wide screen HDTV system needs to be the platform that provides the headroom and challenge for further near term development.</p>

<p>The full potential of any new standard should never be fully encompassed by the existing state-of-the-art, nor should it be so futuristic as to not have its potential achievable in a foreseeable time. NTSC was well beyond the quality of the 1950s color displays, and America's HDTV standard is beyond the quality of today's HD displays. But, unless HD is transmitted, display improvements will not be made.</p>

<p>"Good enough" is no longer "perfect", and may become wholly unsatisfactory </p>

<p>Finally, all roads lead to the home! Digital TV will be a success, or failure, based on consumer reaction, and this, in turn, depends on the quality and quantity of DTV and HDTV programming and on the design, availability, and cost of the DTV receivers.</p>

<p>The latest information we have from the consumer equipment suppliers is shown in Figure 3. As noted before, column 6 - Native Display Format - indicates plans to display only 1080I and 480 I & P formats, converting 720P to one of these display formats.</p>

<p>1080I and 480 I & P will be the dominant DTV formats, and by November 1, 1999 at least 33 DTV stations are due to be on-air, reaching 53% of U.S. households!</p>

<p>So, as we evaluate DTV and HDTV and plan for their implementation, we must bear in mind that today's "standard of service" enjoyed by our viewers will not be their "level of expectation" tomorrow. "Good enough" is no longer "perfect", and may become wholly unsatisfactory.</p>

<p>"Quality is a moving target, both in programs and in technology. Our judgments as to the future must not be based on today's performance, nor on minor improvements thereto.:</p>

<p>____________________________________________________</p>

<p>About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em></p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 26, 2005  1:22 PM</b>
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
			<?=getComments(130)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 130)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1998_cbs_dtvhdtv_rollouthow_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php" type="text/javascript" charset="utf-8"></script>
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