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
		AND e.entry_id = 1539";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1539 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1539 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1539";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1539";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</title>
	<meta name="keywords" content="dtv transition, tuner integration, transition help, help part, tuner mandate, tuner, dtv, digital, integrated, dtvs, tuners, sets, inches, analog, part, mandate, fcc, transition, july, help, manufacturers, integration, less, cable, stb" />
	<meta name="description" content="This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  &lt;p&gt;As mentioned in &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php&quot;&gt;part 3&lt;/a&gt; of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  &lt;p&gt;Would DTV tuner integration address this situation?..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  &lt;p&gt;As mentioned in &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php&quot;&gt;part 3&lt;/a&gt; of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  &lt;p&gt;Would DTV tuner integration address this situation?..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1539', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November  3, 2008</b>
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
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_2_a_technical_view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></li>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_3_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_6_subsidy_settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>Part 4 - DTV Tuner Integration</b>
<p>This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  <p>As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">part 3</a> of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  <p>Would DTV tuner integration address this situation? Yes, for those that purchased an integrated DTV, but the rest would have to use a set-top-box tuner/converter to receive a digital signal, and in both cases an antenna pointing to the digital stations would be needed.  <p>The idea is simple, a digital tuner could be within the DTV, or could be within a separate set-top-box (STB). The STB could be used for an analog TV but also for a digital TV that lacks a tuner (a monitor manufactured before the FCC's tuner integration mandate).  <p>However, a digital monitor that does not have tuning capabilities can only give its full potential when connected to a digital HD-STB that is capable to tune and send to the DTV the SD/ED/HD program at its full resolution, a feature that is not possible with a government coupon-program tuner/converter.  <p>Analog TVs can only display at 480i resolution. A government-coupon-program converter would tune to digital channels but would downconvert their resolution to 480i so the analog TV could display them. Analog TVs do not need to be replaced if that level of image quality is satisfactory enough for the viewer.  <p>Coupon-program converters do not output digital HD signals as fully capable HD-STBs do. While it is possible to use these coupon-converters to feed analog 480i to even a tuner-less monitor DTV, the DTV resolution capabilities would be under utilized when a high-resolution image is tuned and the coupon-program converter box reduces its quality to just 480i.  <p>An integrated DTV tuner would perform a similar function as a separate HD-STB regarding tuning HD, but it has the virtue of not having a separate box around the TV and also save the cost/inconvenience of additional wiring to the TV.  <p>The trade-off? If the internal tuner fails, performs badly, or becomes obsolete, the integrated DTV itself is subjected to the inconvenience/service, rather than just a box that can be independently serviced or replaced while the TV can still be used with another STB or for other purposes.  <p><b></b> <h2>Original Integrated Tuner Mandate </h2> <p>In 2002, the FCC issued a mandate for over-the-air tuners to be gradually included into every DTV manufactured after that date. On-the-clear QAM cable tuners (for unscrambled content) were also included into DTVs as part of an industry agreement made around that time.  <p>Beware of some recent erroneous and misleading claims of uninformed journalists, such as: "<i>many of these DTVs lack ATSC tuners; all DTVs weren't mandated to include tuners until last March.</i>"  <p>The claim is "just" 5 years off.  <p>Actually <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">integrated DTVs</a> were gradually introduced with 2003 models following a five-year phased-in plan, which originally was issued as follows:  <ul> <li>50 percent of sets measuring 36 inches and larger by July 1, 2004; 100 percent by July 1, 2005.  <li>50 percent of sets measuring 25 inches to 35 inches were to add DTV tuners by July 1, 2005; 100 percent by July 1, 2006.  <li>The rest were to conform by July 1, 2007. </li></ul> <p>In 2005, that plan was modified with stricter deadlines and screen sizes.  <h4></h4> <h2>Updated Integrated Tuner Mandate </h2> <p>In mid 2005, the FCC made a tentative decision to change the deadline, and requested feedback from manufacturers, broadcasters, and industry trade groups.  <p>The decision was to accelerate the deadline of tuner integration for TVs under 25-inches to make them compliant 6 months earlier than planned, advancing the date from July 1, 2007 to December 31, 2006. The decision also included smaller sets under 13 inches, not included within the original mandate.  <p>Advancing the deadline was also viewed as a way to reduce the number of D/A converters that would be needed in the subsidy program when analog signals stop broadcasting.  <p>On their response, the CEA requested to eliminate the July 1, 2005 deadline that required manufacturers to make half of the 25/35-inches sets capable of receiving digital. The argument from manufacturers was that consumers would end up buying the cheaper analog sets, and retailers were less willing to order the more expensive digital sets. The FCC rejected that request.  <p>Walt Disney, the NAB, and the Association for Maximum Service Television (MSTV) urged the FCC to adopt the advanced deadline of December 31<sup>st</sup>, 2006, while the CEA, the CERC (Consumer Electronics Retailers' Association), Sharp Electronics, and Philips Electronics North America opposed to it, and claimed not having enough time to manufacture those DTVs by the end of 2006.  <p>The CEA and CERC issued the following statements: "<a name="OLE_LINK2"></a><a name="OLE_LINK1">the FCC should refrain from making any rulings regarding the inclusion of digital tuners in new </a>receivers with screen sizes less than 13 inches until manufacturers, retailers and the commission adequately are able to examine the impact of the small chassis products that currently are subject to the commission's tuner requirements." They opposed accelerating the timetable claiming that no evidence justified the change.  <p>Other comments from the CEA regarding the effect of accelerating the mandate on manufacturing and consumers were as follows:  <p>"Some manufacturers could opt to market monitor-only models that remove both digital and analog tuners, or stop manufacturing certain sets altogether. For smaller sets, 13 to 26 inches, the requirement would double the development costs for manufacturers, as well as double the price of a typical 13-inch television to consumers," the CEA said, and added: "If the product is rejected by lower income and other consumers because the price exceeds their budget, it will not be carried by retailers and, eventually, not produced by manufacturers."  <p>The CEA also said "the unfortunate result of accelerating the tuner mandate deadlines for all sets would be to decrease the number of DTV tuners in the marketplace, which clearly does not serve the transition."  <p>Finally, in November 2005, the FCC voted for setting the new date as March 1, 2007 for all sizes including those smaller than 13 inches, which received the support from the NAB taking into consideration how important they are in times of emergency and are commonly used without STBs.  <p>The FCC revised the deadline dates for DTVs to have integrated tuners as follows:</p> <table cellspacing="0" cellpadding="2" width="669" border="0"> <tbody> <tr> <td valign="top" width="139">Upon approval </td> <td valign="top" width="528">&gt;=36 inches </td></tr> <tr> <td valign="top" width="140">By March 1, 06 for </td> <td valign="top" width="528">&gt;= 25 inches (was July 1, 06 on the original plan) </td></tr> <tr> <td valign="top" width="141">By March 1, 07 for </td> <td valign="top" width="528">&gt;= 13 inches (was July 1, 07, and was agreed for March 07 although the FCC proposed it for December 31, 06) </td></tr> <tr> <td valign="top" width="141">By March 1, 07 for </td> <td valign="top" width="528">&lt; 13 inches (was not required before) </td></tr></tbody></table> <p>The mandate does not apply to other small screen video capable devices that do not receive analog OTA broadcasting, even when they might be used to watch TV shows, such as PDAs, mobile phones, iPODs, etc., but it applies to other non-screen devices that have analog tuners to perform their purpose, such as VCRs and DVD recorders.  <p>11.8 million DTVs were produced between 1998 and 2003 and most are tuner-less monitors that need an external STB tuner to view digital TV. Most of those DTVs also do not have DVI/HDMI digital connections with HDCP content protection; they only include component analog connections for HD.  <p>The lack of protected digital connectivity could render <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_blu-ray_only_if_your_hdtv_permits_it.php">them incapable</a> of eventually receiving protected premium content from an external STB when connected with analog component cables, which is another wrinkle of the DTV transition that hit hard on the efforts early-adopters did to help establish DTV in the US.  <p>The majority of the remaining 69.3 million DTVs that were sold between 2004 and 2007 have an integrated tuner to comply with the mandate, although many of that period were still permitted to be tuner-less depending on their size. A large part of these sets may also have QAM cable TV tuners for unscrambled content, and many of those also have CableCARDS for premium content, although with only unidirectional capabilities.  <p><b></b> <p><b></b> <h2>Integrated Tuner Mandate Enforcement </h2> <p>In June 2007 Regent USA (Maxent) and Syntax-Brillian (Olevia) were issued "apparent liability for forfeiture" notices by the FCC for allegedly failing to comply with the ATSC tuner mandate on the DTVs they manufactured, imported or shipped, and the FCC added, under "willful and repeated violations".  <h5></h5> <p>Regent was fined $63,650 for importing or shipping 1,182 non-compliant DTV tuners. Syntax-Brillian was fined $2,899,575, for 22,069 DTVs imported or shipped within the statute of limitations, and the FCC commented "We believe that the proposed forfeiture reflects the gravity of Syntax-Brillian's apparent violations, the company's ability to pay, and the need to deter Syntax-Brillian and other companies from future violations of the act and the rules".  <h2>Tuner-less DTVs Even Under the Mandate</h2> <p>In 2007, Toshiba introduced new lines of HD flat-panels, rear-projection, and direct-view sets that excluded the mandated over-the-air digital tuner by not including the analog tuner on the sets, which then qualified them as valid "monitors."  <p>Toshiba was not alone in cutting down on tuning components. CableCARD-less TV lines started to appear from various manufacturers that found no merit in integrating a unidirectional cable tuner with CableCARDs into DTVs while the cable industry was moving toward a bi-directional OCAP solution.  <p>Toshiba announced the 2007 monitors to cost $300 less than comparable "integrated" TVs with mandated tuners.  <p>In perspective, when integrated DTVs were introduced in 2003 the difference between a monitor DTV and an integrated version of the same TV <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">was $704 more on average</a>. Since 2003, millions of consumers have paid for integrated tuners they do not use because most subscribe to satellite or cable.  <p>Additionally, consumers could not know of the extra cost of the unneeded tuner because monitor-only versions of their integrated sets where no longer produced.  <p>"This is all the video display a consumer needs if they get programming from a cable or satellite TV box," Toshiba said.  <p>The next part (5) in this series will be dedicated to "<a href="/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">Was Tuner Integration Timed Right?</a>" </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November  3, 2008  9:42 AM</b>
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
			<?=getComments(1539)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1539)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/11/dtv-transition-can-you-help-part-4-dtv-tuner-integration.php" type="text/javascript" charset="utf-8"></script>
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