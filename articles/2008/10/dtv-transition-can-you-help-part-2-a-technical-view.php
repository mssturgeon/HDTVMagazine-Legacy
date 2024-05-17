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
		AND e.entry_id = 1526";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1526 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1526 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1526";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-2-a-technical-view.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1526";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 2) - A Technical View" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 2) - A Technical View" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Transition - Can YOU Help? (Part 2) - A Technical View" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 2) - A Technical View</title>
	<meta name="keywords" content="dtv transition, sub channels, transition help, help part, horizontal pixels, dtv, digital, channels, channel, quality, analog, image, content, resolution, hdtv, pixels, help, same, broadcast, could, mhz, transition, sub, part, horizontal" />
	<meta name="description" content="Part 2 is dedicated to some technical aspects and benefits brought by the DTV implementation. 

DTV includes HDTV and SDTV, HDTV is a major improvement having 9 times the image quality of analog just in resolution terms, and SD is efficient enough to be able to broadcast 4-6 SD channels over the same bandwidth reserved for one HD channel (or one analog channel) in areas where that line up is needed. 

DTV also allows for the simultaneous broadcasting of both HD and SD, whereby SD uses part of the bandwidth required for HD on the same channel slot, which could be a good benefit, but could possibly harm the quality of the parallel HD program if overused. DTV also has..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 2) - A Technical View" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Transition - Can YOU Help? (Part 2) - A Technical View" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-2-a-technical-view.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Part 2 is dedicated to some technical aspects and benefits brought by the DTV implementation. 

DTV includes HDTV and SDTV, HDTV is a major improvement having 9 times the image quality of analog just in resolution terms, and SD is efficient enough to be able to broadcast 4-6 SD channels over the same bandwidth reserved for one HD channel (or one analog channel) in areas where that line up is needed. 

DTV also allows for the simultaneous broadcasting of both HD and SD, whereby SD uses part of the bandwidth required for HD on the same channel slot, which could be a good benefit, but could possibly harm the quality of the parallel HD program if overused. DTV also has..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1526', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-2-a-technical-view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 23, 2008</b>
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
				<div class="editorial">The following article is the latest in the "DTV Transition - Can YOU Help?" series. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_1_transition_reception_and_help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception and Help</a></li>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_3_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/11/dtv_transition_can_you_help_part_4_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_6_subsidy_settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>Part 2 - A Technical View</b>  <p>Part 2 is dedicated to some technical aspects and benefits brought by the DTV implementation.  <p>DTV includes HDTV and SDTV, HDTV is a major improvement having 9 times the image quality of analog just in resolution terms, and SD is efficient enough to be able to broadcast 4-6 SD channels over the same bandwidth reserved for one HD channel (or one analog channel) in areas where that line up is needed.  <p>DTV also allows for the simultaneous broadcasting of both HD and SD, whereby SD uses part of the bandwidth required for HD on the same channel slot, which could be a good benefit, but could possibly harm the quality of the parallel HD program if overused. DTV has also the potential of <a href="http://en.wikipedia.org/wiki/Datacasting" target="_blank">datacasting</a>.  <p>When writing articles about DTV, many journalists focus on the politics, the government issues, the anxiety of possible failures, delays, weakness points, etc. I focus on working for the DTV system to be successful, for which I am asking your help in this series of articles.  <p>The technical and quality benefits of DTV are plenty, even if the DTV implementation is further delayed or needs another budget boost. After more than 20 years of effort, HDTV is here to stay and we have to make the DTV transition be successful.  <p>Sister technologies such as communications, music, photo, etc. have already migrated to digital. The technical world keeps moving in the direction of ones and zeros, and television is no exception.  <p>Sometimes the concept of "ones and zeros" facilitates the opportunity of over-compressing a digital signal that might be already penalized by the limited sampling of an infinite analog original of images we see and sounds we hear in real life, an analog world.  <p>DTV is no exception for that either. As with other digital-everything experiences, quantity models driven by moneymaking temptations might impact the quality of media, and you can help: demand quality be preserved!  <p>If you are not technically oriented, ignore the numbers in this article and scroll through the concepts to familiarize yourself with some benefits of DTV over analog TV. If you need more clarity and detail on some HDTV terminology please consult the <a href="http://www.hdtvmagazine.com/glossary.php" target="_blank">HDTV Glossary</a>.  <h2>Is DTV So Different?</h2> <p>You thought the current NTSC analog TV was fine, so why "fix" it?  <p>What about if DTV offers you an HDTV image that is 9+ times the resolution of your current analog NTSC TV?  <p>The NTSC image resolution is made of 480 viewable lines of 450 horizontal pixels each. HDTV is as high as 1080x1920. Do the math. Which one should look better?  <p>You thought DVD was even better than NTSC analog TV. What about an HD image that is 6 times the resolution of DVD?  <p>The DVD format is 480x720. HDTV is as high as 1080x1920. Do the math. Which one should look better?  <p>Is there anything better than broadcast 1080i HDTV? What about Blu-ray pre-recorded HD discs with 1080x1920 <b>progressive</b> at 24 frames per second for film based content. Play those on a large screen at home, and it would be hard to return to the local theater, unless you are missing the popcorn and soda on the floor.  <p>All of the above brought to you courtesy of the hard HDTV effort of the past 20+ years.  <p>How can someone disregard these technology advances? Perhaps by not being able to detect the differences in quality at the home environment. How could you appreciate those differences?  <p>Anyone should be able to appreciate the HD picture quality improvements on a larger TV screen viewed at the appropriate distance, but not if using the 13-inch TV in the kitchen viewed from 20 feet away. Even VHS might look the same to anyone under those conditions.  <p>That is probably the reason why many people with small screens, or viewing from too far away, might wonder: why H/DTV? I see no difference!  <p>Or why Blu-ray? DVD looks the same to me! Check out the <a href="http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buyers_guide_part_1.php" target="_blank">2008 HDTV Buyers Guide</a>, which should help you with that subject.  <p>The market of larger screens with stunning image quality has motivated more people to migrate to bigger-and-better models, especially panels (ie flat screens), and they will eventually begin to appreciate the differences of image quality when viewing HD after switching from a regular channel. After that, there is no turning back; your favorite list of channels from the remote will be mostly HD.  <p>TV viewers that are less concerned about picture quality than they are about more free over-the-air channels can also benefit from DTV, due to the larger number and variety of channels DTV offers with the SD sub-channel capability.  <h2>The Digital Opportunity for Quantity vs. Quality</h2> <p>The current analog television uses quite a bit of bandwidth from a reserved spectrum of airwaves that are set aside for a limited number of TV channels in increments of 6 MHz. In general, DTV can efficiently maximize the use of that TV spectrum, to have more and better quality digital channels and services.  <p>Additionally, when analog TV is fully replaced by digital, part of the spectrum will be returned to the FCC, and the communications industry could use that spectrum to further benefit consumers with other communications business and technologies.  <p>Let us browse over a few DTV technical features that would benefit you and the industry:  <ul> <li><b>Multiple Standard Definition digital sub-channels<br></b> A single 6 MHz slot used for 1 <b>analog</b> channel has enough capacity to simultaneously support the broadcast of 4-6 standard definition (SD) <b>digital </b>sub-channels, to meet the requirements of certain demographic areas, ethnic channels, weather, children, etc., not to mention multiply the advertising revenue of broadcasters as well. In the analog world, those 4-6 sub-channels would have required 6 MHz <b>each</b>.</li> <li><b>High quality HDTV </b>can be broadcasted using the same 6 MHz channel-slot of one analog NTSC channel, and raise up to 9 times the resolution quality. The NTSC image is made of 480ix450 viewable picture elements per video frame (216,000). Each video frame is actually made by two half frames (interlaced fields) of 240 lines each (240x450 pixels on each field).<br><br>Most HD broadcast is also transmitted as <a href="http://www.hdtvmagazine.com/glossary.php">interlaced 1080i</a>, but with 2 million+ pixels x video frame (1080x1920 pixels), made by two half frames (fields) of 540 horizontal lines each (540x1920 pixels in each filed).</li> <li><b>Progressive video broadcast, unique to DTV</b> <br>A few HDTV networks broadcast HD using a progressive digital video format <a href="http://www.hdtvmagazine.com/glossary.php">720p</a>, as opposed to interlaced 1080i HD.<br><br>If one were to compare 720p vs. 1080i by the number of pixels that are spatially perceived by the eye when viewing one full video frame, 720p has half the spatial-resolution of the 1080i field-pair.<br><br>720x1280= 921K pixels on one 720p video frame compared to 1080x1920= 2+ million pixels on the 1080i frame.<br>The battle of opinion about which of the two formats (1080i or 720p) is better will never end, and it depends on the type of content, and the limitations of the display device.<br><br>For example, if your display device were a 720p panel limited to 1280 horizontal pixels, watching 720p content would be a perfect match, but you would never be able to see the full 1920 horizontal pixels of a 1080i image when you change to tune a 1080i channel (if all those pixels were actually recorded in the original content).<br><br>The same applied to legacy direct-view or projection CRTs due to their typical limited horizontal resolution. Beyond the interlaced vs. progressive image motion virtues, the 720p format itself has 33% less horizontal resolution compared to 1080i (1920-1280=640, 640 is 33% less than 1920).<br><br>The 720p progressive format does not have the problem of the interlace artifacts of 1080i. Each frame of 720p lines contains the complete detail of a full image frozen in time by the camera, and the frames are displayed at a higher speed (60 <u>frames</u>-per-second, rather than 30 frames of 1080i displayed as 60 <u>fields</u>-per-second). Progressive is better for fast content, such as a sports program on a dedicated sports network (eg. ESPN-HD).<br><br>However, let us look at the viewing experience in the home switching channels. If a mixed-content network decides for 720p transmission in a constant basis (such as ABC), the progressive format might be beneficial when broadcasting a sports program (faster frames could be more important than more detailed lines on that fast content), but movies and documentaries on that channel would also be broadcasted at the limited horizontal resolution of 720p (1280), when a 1080i channel could make the image of that content look more spatially detailed with the full 1920 horizontal pixels.<br><br>In general, a broadcast format that is good for you, for the type of content you watch, and for your display resolution, might not be the situation of your neighbor. It depends on the content you and he/she watch more often.<br><br>People do not choose a TV set based on the resolution of the broadcast channel they watch more often, but if so, since only a few networks use 720p, and the TV market is gradually moving to a larger variety of 1080p resolution TVs for even medium size screens, the option of 1080x1920 displays becomes more beneficial as time progresses, especially with the Blu-ray media using all that resolution.<br><br>I personally use my projection home theater for mostly movies on a 135-inch CinemaScope screen. The 33% horizontal resolution gain of the 1920 horizontal pixels of 1080i is more evident on it, and even when an original 720p program is converted to 1080p for display, the lower spatial resolution of the original 720p signal is evident on the conversion to 1080p, because pixels that never existed in the incoming image cannot be invented by video processing to look the same as if they were original, especially during motion, and more obvious when displayed on a very large screen.<br><br>I must add that there are two other HD progressive formats within the 18 formats of DTV, and that is 1080p at 24 and 30 frames per second. Broadcasters are not currently using these formats to transmit HD to homes but some satellite companies are moving in that direction. The 1080p24 format is suitable to 24-frames movie film content when transferred into a pre-recorded media such as Blu-ray. For more detail check the <a href="http://www.hdtvmagazine.com/glossary.php" target="_blank">HDTV Glossary</a> or the <a href="http://www.hdtvmagazine.com/articles/2006/01/why_1080p.php" target="_blank">1080p articles</a> I wrote about the subject.</li> <li><b>Digital allows for a combination of HD and SD sub-channels</b> sharing the same 6 MHz channel allocation. When HD is compressed with MPEG-2 at 19.4 Mbps it typically needs the whole 6 MHz bandwidth of the channel for itself.<br><br>Even then, some rapid content such as strobe flashing, sudden flames, or waterfalls, could show with some artifacts. Sharing the 6MHz channel-slot space with other content would cause the 19.4 Mbps bit rate of the HD content to drop, and the HD image quality to suffer.<br><br>It is important to emphasize that HD quality is generally the main reason most consumers have when purchasing a large screen HDTV, to view large stunning HD images, not just digital anything.<br><br>However, even when I am not in favor of subtracting from the quality of an HD image to give space to additional SD sub-channels, sometimes it might be necessary, and the point here is that the digital technology allows it, analog did not.<br><br>PBS is an example. Although their HD sub-channel shows the impact of the reduced bit-rate, PBS made the decision to simultaneously broadcast other parallel SD channels for family, children, etc. as a public service.<br><br>In perspective, the analog alternative would have used 6 MHz <u>for each</u> sub-channel, which means that those channels might have never existed in the analog world due to the limited space in the TV airwaves spectrum.</li> <li><b>Digital compression (MPEG-2 for DTV)</b> allows for a digital signal to fit into a smaller space for recording or transmission purposes. The saved space could be used for other sub-channels or services. Compression can be flexible in a way that was not possible with analog NTSC.<br><br>Compression can also evolve with improved algorithms over time to be more efficient and allow the transport of more content over the same bandwidth, as satellite and cable did for years.<br><br>These subscriber services are now switching HD to MPEG-4 compression technology, which is more efficient than MPEG-2 (about 50%), however, the compression standard selected for broadcast terrestrial DTV was MPEG-2, not MPEG-4, and millions of integrated DTVs and tuning STBs since 1998 were designed to handle MPEG-2 compression, not MPEG-4.</li> <li><b>Mobile and portable devices</b> would eventually receive digital TV when (and if) implementing some recent technological advances, such as <a href="http://www.hdtvmagazine.com/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Samsung's A-VSB</a>. The A-VSB system of transmission shares the same 6MHz assigned to the terrestrial DTV channel without interfering with terrestrial receivers.<br><br>A-VSB and other similar mobile digital transmission systems proposed for standard approval can generate new business opportunities for broadcasters. However, the bits needed for the digital mobile service need to be taken from the same bandwidth used for a good quality HD image (approximately 20% or more).<br><br>As with the case of implementing multiple SD sub-channels, the quality of HD could deteriorate if its bit rate is penalized. The opportunities that digital brings could be very tempting for quantity business models, so consumers should speak up if they have interest in quality, especially when HD content shows obvious image deterioration.<br><br>However, the point here is that the digital system is capable to offer parallel services sharing the same bandwidth.</li> <li><b>Dolby Digital multi-channel digital audio</b> was selected as the audio standard of DTV. The audio format is lossy compressed with 5.1 discrete channels (stereo L/R front, stereo L/R surround, and center channels) at their full 20Hz-20KHz frequency response range, and with a separate .1 LFE (low-frequency-effects) channel for a subwoofer.<br><br>Dolby Digital 5.1 is a considerable improvement compared to legacy stereo Left/Right and even so compared to the legacy 4-channel surround Dolby Pro-Logic.<br><br>By design, Dolby Pro-Logic has the surround and center channels matrix encoded into the two L/R channels, they are not discrete (not matrixed) channels as the Dolby Digital standard used on DTV.<br><br>Additionally, legacy Dolby Pro-Logic has no separate LFE subwoofer channel, and the surround channel is only monaural (not L/R stereo) and with a reduced frequency response, although is reproduced over two side/rear speakers (making you believe that are different channels).</li> <li><b>Digital can also facilitate the implementation of data casting</b> and of two-way digital interactive services that bring the opportunity of new business models to broadcasters. These services were not possible with uni-directional analog broadcast sending signals as out-only from the broadcast antenna.<br><br>As with the implementations of SD sub-channels and mobile digital transmission, this feature would also have to share the same 6 MHz allocation that otherwise could be used for a good quality HD channel.<br><br>How can you help? Monitor the quality of what you like to watch, let the broadcaster know your opinion, and educate others as well.</li></ul> <p>A non-technical benefit for DTV is that billions of dollars will become available from the auctioning of the spectrum of those 6 MHz parallel channels returned to the FCC after the switch to digital broadcasting.</p> <p>In addition to the potential of having that spectrum facilitate the implementation and modernization of other communication technologies that could benefit consumers, the proceeds of the auction would help pay for the digital-to-analog converter-box program, for programs to help first-responders, and for deficit reduction. </p> <p>Congress already set the auction of the spectrum in the 700 MHz band (January 2008). Since reportedly no bids met the FCC's $1.3 billion first auction's reserve price, the FCC was planning to run simultaneous auctions, "the first would be for the whole D-block at a lower reserve price of $750 million, while the second would be for 58 regional licenses, used for either LTE or mobile WiMAX" according to CedMagazine.  <p>In the <a href="/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">next article in the series</a>, I will cover the subject of DTV market conditions for the transition, the number of DTVs vs. household's coverage for the DTV Transition deadline, and a projection for the full replacement of analog TVs in the US.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 23, 2008  9:18 AM</b>
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
			<?=getComments(1526)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1526)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-2-a-technical-view.php" type="text/javascript" charset="utf-8"></script>
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