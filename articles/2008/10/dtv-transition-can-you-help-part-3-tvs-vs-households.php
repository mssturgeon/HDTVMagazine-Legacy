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
		AND e.entry_id = 1531";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1531 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1531 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1531";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1531";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</title>
	<meta name="keywords" content="million million, dtv transition, million dtvs, analog tvs, per household, million, digital, dtv, tvs, analog, dtvs, cable, households, transition, cea, year, sets, number, ratio, household, years, still, sold, per, stb" />
	<meta name="description" content="Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.

Over recent years some of the figures tossed by..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.

Over recent years some of the figures tossed by..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1531', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 27, 2008</b>
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
<li><a href="/articles/2008/11/dtv_transition_can_you_help_part_4_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_6_subsidy_settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>TVs vs. Households</b></p> <p>Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.</p> <h2>Number of TVs vs. Households</h2> <p>Over recent years some of the figures tossed by the press mixed and loosely compared the number of households with the number of TV sets in the US, and the number of cable/satellite household subscribers with the number of analog, digital or HD set-top-boxes, for example.</p> <p>From that mix, deceptive percentages and ratios were derived and presented in articles made to sensationalize preconceived opinions for a journalistic profit, and many of those authors were not even related to the DTV industry.</p> <p>My objective is to help the public with factual and accurate information, and to provide an analysis without an agenda of profitability from the situation at hand, so let us get to work.</p> <p>Up until 2007 the Consumer Electronics Association (CEA) used an average ratio of 2.6 TV sets per household (289 million active/inactive TVs of any kind installed into 111 million US households). Now the average ratio is 3.1 and is calculated as 346 million active/inactive TVs within 112.8 million households.</p> <p>After discussing the subject with the CEA's Senior Director of Market Research, he confirmed my assumption that the increased ratio responds to consumers purchasing newer DTVs to experience digital, but not to immediately replace any old TV, which might still perform well for some secondary service in the home.</p> <p>This particular situation has the effect of making the overall inventory of TVs increase more than the simple effect of replacing analog by digital (289 to 346 million), and making the ratio higher when relative to an almost unchanged number of households (111 to 112.8 million).</p> <p>However, of those 346 million about 300 million TVs are actually active (in use), which actually makes the average ratio of "active" TVs per household to 2.6. In my discussion with the CEA we agreed that although the other 46 million inactive TVs might still be functional, they have been moved to other non-living areas of the house, such as the attic.</p> <p>According to information obtained by CEA, the Senior Director of Market Research of the CEA said, 13% of the sampled households reported to have 5 TVs in the house, and 35% of the sampled households reported to have 4 TVs. When those groups joined the remaining 52% of households of the sampling, the average of TVs per household is 3.1 on the most recent year of the research.</p> <p>Does the US need those 346 million TVs to be digital for the February 17, 2009 deadline to be met? No.</p> <p>Those TVs do not have to be all digital by then, and the reality is that they could not all be replaced for digital sets in such a short time either, and probably they will continue performing an analog service in a digital world for years to come.</p> <p>A total of 81.1 million digital TVs were sold between 1998, when the DTV transition started, and December 2007. It is projected that approximately a cumulative 116 million DTVs in total will be sold by the February 17, 2009 deadline.</p> <p>This means that the US would still have about 230 million analog TV sets (346-116=230) by the February 2007 deadline. How is this mix going to affect the public receiving the variety of TV services within the US, such as broadcast, cable, satellite, IPTV, etc?</p> <h2>The Set-Top-Box Comes to Help </h2> <p>After the deadline, any analog TV would require a set-top-box (STB) to tune to a digital signal whether they are connected to an antenna, cable, satellite, etc. Let us analyze how that would happen.</p> <p>Small dish satellite (DirecTV and Dish Network) is already 100% digital, and each satellite subscriber household must have at least one satellite digital STB already installed. Satellite subscribers are automatically ready for the digital transition regardless of the TV they have, and although HD is not a requirement for the digital service, the digital STB has to be HD capable to receive HD channels from the satellite provider.</p> <p>Some cable companies converted all of their subscribers to digital by replacing analog STBs by digital STBs when needed, and migrating their service to support only digital tiers. The complete switch to digital and the upfront investment in digital STBs can be an incentive to some companies when the bandwidth required by discontinued analog channels could be released and used for more digital channels or for more efficiently managed services that could generate more revenue in the long run.</p> <p>Other cable companies are gradually converting subscribers to digital tiers while temporarily continuing with analog tiers, under the flexibility given by the FCC to cable companies, by which they have until 2012 to switch to full digital.</p> <p>The flexibility also benefits many millions of analog subscribers that have their analog TVs directly connected to the wall-coax to tune basic analog channels without having to lease a cable set-top-box. </p> <p>In other words, regardless of whether the STBs are cable, satellite, or over-the-air, they would allow millions of analog TVs to still perform at their level of resolution (480i) regardless of the digital signal tuned by the STB.</p> <p>The vast majority of the remaining 230 million TVs mentioned earlier would probably not need to be replaced by DTVs any time soon as long as STBs are used, or analog tiers are continued by some cable companies until 2012.</p> <p>Although many integrated DTVs have internal digital QAM cable tuners the cable subscriber may still require a cable STB for certain services. The integrated tuners might even have Cable CARDs but the tuners are only uni-directional and do not support the cable supplied EPG (Electronic Program Guide), VOD (Video on Demand), and Impulse PPV (Pay-per-view) bi-directional cable features, so a cable STB is needed.</p> <p>This can be viewed as a duplicated investment for many DTV owners, who paid once for the integrated ATSC/cable tuners mandated by the FCC within the purchased DTV, and paid again when leasing the cable STB with bi-directional capabilities to support the features above.</p> <p>In other words, if the cable subscriber a) owns a DTV with QAM digital cable tuning capabilities w/o CableCARD and wants to tune to premium channels, or b) owns a DTV with a CableCARD for premium channels, but wants bi-directional services (VOD, etc), a digital cable STB will still be needed, which is now mandated by the FCC to have a CableCARD within it.</p> <h2>Number of DTVs Sold Since 1998</h2> <p>Although this article is not about reconciling numbers or counting beans for journalism purposes, the information below helps provide a sky-high perspective of where we are with the DTV transition.</p> <p>On each of my HDTV annual reports I analyze the progress of the DTV installed base by technology (plasma, LCD, DLP, etc), and provide a projection for the next several years. Below is a yearly summary of the DTVs sold to dealers since the beginning of the DTV transition. The first table immediately below shows figures sourced from the CEA as of July 31, 2008:</p> <table class="type1b"><tr><td class="type1b_header">Year</p></td> <td class="type1b_header">DTV Sets</td></tr> <tr> <td valign="top" width="57">2011</td> <td valign="top" width="580">40.8 million (cumulative end of year 2011, 228.7 million)</td></tr> <tr> <td valign="top" width="57">2010</td> <td valign="top" width="580">38.4 million</td></tr> <tr> <td valign="top" width="57">2009</td> <td valign="top" width="580">35.8 projected million</td></tr> <tr> <td valign="top" width="57">2008</td> <td valign="top" width="580">32.6 estimated million (cumulative end of year 2008, 113.7 million)</td></tr> <tr> <td valign="top" width="57">2007</td> <td valign="top" width="580">26.4 million (cumulative end of year 2007, 81.1 million)</td></tr> <tr> <td valign="top" width="57">2006</td> <td valign="top" width="580">23.5 million (cumulative end of year 2006, 54.7 million)</td></tr> <tr> <td valign="top" width="57">2005</td> <td valign="top" width="580">11.4 million</td></tr> <tr> <td valign="top" width="57">2004</td> <td valign="top" width="580">8 million</td></tr> <tr> <td valign="top" width="57">2003</td> <td valign="top" width="580">5.5 million</td></tr> <tr> <td valign="top" width="57">2002</td> <td valign="top" width="580">4.1 million</td></tr> <tr> <td valign="top" width="57">2001</td> <td valign="top" width="580">1.5 million</td></tr> <tr> <td valign="top" width="57">2000</td> <td valign="top" width="580">0.6 million</td></tr> <tr> <td valign="top" width="57">1999</td> <td valign="top" width="580">0.1 million</td></tr> <tr> <td valign="top" width="57">1998</td> <td valign="top" width="580">0.0 million</td></tr></tbody></table> <p>As you see from the above, a total of 81.1 million DTVs were confirmed as actually sold between 1998 and December 2007. Those 81.1 million DTVs are expected and are capable of replacing and performing the job as part of the whole inventory of 346 million TV sets available in the whole US.</p> <p>In summary, the installed base grew beyond a replacement purpose and has now a higher ratio (3.1) of TVs per household, the 81.1 million purchased DTVs are not necessarily replacing analog sets that will be inactive, but they make a household now DTV capable with at least one digital set.</p> <p>So it is obvious that quite a few more years will be needed before all legacy analog TVs can be actually replaced by digital sets, but the transition does not expect that all the TVs have to be replaced to be able to switch the analog NTSC system to digital, regardless of the date.</p> <p>For a long time to come there will be a mix of new digital TVs and old analog TVs that be eventually replaced, or in many cases not ever replaced, depending on their purpose and if they still perform well as display devices for the needed image.</p> <p>To have an estimate of an "until today" (July 2008) DTV-sold figure, one can take half of the 2008 projection above (16 million from the full year's 32.6 million) and add it to the 81.1 million figure of 1998-2007, making the total for the period 1998-1H08 to be about 97.1 million DTVs (81.1 + 16).</p> <p>However, it is customary to use actual annual figures only when the period is completed to have the opportunity to confirm or to revise the estimate for that year. The actual figure for 2008 would be available sometime in mid-2009.</p> <p>In the past, even actual figures published of earlier years, not just the estimate of the previous year, were subjected to further revision by the CEA to refine the count based on improved feedback from the CE industry. My annual reports include the CEA adjustments when they are made available, usually a few months after my reports are published for the year.</p> <p>The most accurate figures are in my 2007 report. The CEA figures mentioned in the table above are very similar to the ones I published in my 2007 report (below) with the information available at that time:</p> <table class="type1b"><tr><td class="type1b_header">Year</td> <td class="type1b_header">DTV sets (as I reported in the <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">2007 HDTV Review</a>)</td></tr> <tr> <td valign="top" width="63">98-01</td> <td valign="top" width="516">1.4 million (revised now to 2.2 million by CEA in 2008 as above)</td></tr> <tr> <td valign="top" width="66">2002</td> <td valign="top" width="516">4.1 million (match, but also reported as 2.5 million in earlier reports)</td></tr> <tr> <td valign="top" width="68">2003</td> <td valign="top" width="516">5.5 million (match, but also reported as 4.1 million in earlier reports)</td></tr> <tr> <td valign="top" width="70">2004</td> <td valign="top" width="516">8.0 million (match, revised from 8.2 million reported in 2006)</td></tr> <tr> <td valign="top" width="71">2005</td> <td valign="top" width="516">11.3 million (revised to 11.4 million in 2008)</td></tr> <tr> <td valign="top" width="72">2006</td> <td valign="top" width="516">23.9 million (confirmed in 2008 as 23.5 million actual)</td></tr> <tr> <td valign="top" width="73">2007</td> <td valign="top" width="516">29.2 million (confirmed in 2008 as 26.4 million actual)</td></tr> <tr> <td valign="top" width="74">Total</td> <td valign="top" width="516"><b>83.4 million (Nov 98 - Dec 2007)</b> as I reported in 2007.<br><br>Confirmed now as <b>81.1</b> million actual in 2008 (in CEA table above)</td></tr></tbody></table> <p>In other words, a negative adjustment of about -2.3 million DTVs (from 83.4 to 81.1 million) was made for the 1998-2007 period, which is about 2.7% reduction over the figures provided on the 2007 report, mainly due to revising projected/estimated figures by actual figures, as it happens every year.</p> <h2>The Projections, and the Expected TV Replacement Behavior</h2> <p>Looking ahead over 3 years, CEA's estimate of 228.7 million DTVs sold by 2011 would still be short of replacing all the TVs in the US. If the 40 million per year rate for the single year 2011 projected by the CEA were consistently maintained for the years beyond 2011, an additional period of 7 years (counting from mid-2008) would be needed for the full replacement of analog TVs on the entire population (by 2014).</p> <p>But again, because many of the current analog TVs might still be useful for their purpose (video games, pre-recorded movies, external STB, etc) there should not be a rush in declaring them obsolete just because of a DTV transition deadline or because they are not digital.</p> <p>Additionally, the 346 million DTVs of today are a moving target inventory, when consumers buy new DTVs not necessarily to replace analog TVs and declare them inactive. The total number will grow and the ratio per household will grow as well perhaps for a few more years until a majority of households find no need to keep so many active and inactive sets in the home, regardless how many DTVs they buy.</p> <p>In retrospect, all B&amp;W TVs did not need to be replaced in a rush when color television arrived decades ago. Back then it was due to backward compatibility of the color system to a B&amp;W TV by design; now, it is thanks to a STB tuner/converter of digital-to-analog. Even today, some households might still have some of those B&amp;W sets around for some basic purpose, if they still work.</p> <p>People would naturally replace analog TVs as needed. Current DTVs would also be replaced for newer models as needed, and those replaced DTVs would probably be moved to other rooms in the house to perform other secondary tasks, replacing analog TVs that are doing that task, gradually shifting out old analog sets as new DTV sets come into the households.</p> <h2>Who Owns the 81.1 Million DTVs?</h2> <p>One item that I regularly analyze in my annual reviews is that the 81.1 million DTVs sold over the first 10 years of the DTV transition is not necessarily equivalent to a 1-to-1 installed ratio in a similar number of households (having one DTV at each home).</p> <p>Many early-adopters that bought DTVs since 1998 most likely have already purchased their second and even third DTV set for their homes during these first ten years of transition, making the actual number of households having DTV much smaller than the 81.1 million DTVs sold.</p> <p>The purchase pattern of early-adopters is usually driven by the satisfaction of experiencing technology challenges and innovations, having cost as a secondary factor, if at all. This is not the pattern of the budget oriented consumer electronics market, and certainly not the red-tag-sale weekend-ad consumers looking for the best deal at the right time for their pockets.</p> <p>By the end of 2007, Gary Shapiro, CEA president and CEO, declared, "I am proud to announce our nation has hit this digital milestone. With 50 percent of U.S. homes able to experience the reality of digital television, we have crossed a critical threshold."</p> <p>Although the statement from the CEA was an estimate, it seems to be quite accurate. 50% of 112 million households in the US = 56 million households. It is consistent with my analysis above of not following a 1-to-1 ratio for the first wave of DTV consumers.</p> <p>The estimate means that the 81.1 million DTVs sold are actually installed into 56 million households; many of those are early-adopters, making the sold-DTV average ratio as 1.5 DTVs per household.</p> <p>It is likely that the households of early-adopters that purchased most of the 81.1 million of DTVs until 2007 are the main reason of the 5 TV per household ratio mentioned earlier (13% of the sampling).</p> <h2>Final Thoughts on the DTV Adoption</h2> <p>In summary, the DTV market conditions and the ratio of DTV per household will gradually increase over the next few years because:</p> <p>a) DTV prices go further down and attract lower income groups, becoming more accessible to the remaining 56 million households,</p> <p>b) More households continue to acquire multiple DTVs without immediately disposing of current analog TVs,</p> <p>c) The total number of TVs in the US increases further beyond the actually used (active),</p> <p>d) That number is also a moving target that one should not necessarily expect to be fully replaced (such as the inactive 46 million out of the 346 in 2007 mentioned above),</p> <p>e) The analog shut-off will be a strong motivator when the event actually happens as planned in February 2009, and </p> <p>f) Blu-ray increases its footprint for the enjoyment of higher quality pre-recorded HD media, while consumers strive for larger 1080p screens, making the acquisition of an HDTV more appealing when able to realize the full potential of both technologies without having to resort to DVD upconversion/video processing. Blu-ray also brings a great opportunity to finally get "the real picture".</p> <p>Stay tuned for the next part (4) in this series, dedicated to Integrated DTVs.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 27, 2008  9:58 AM</b>
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
			<?=getComments(1531)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1531)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php" type="text/javascript" charset="utf-8"></script>
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