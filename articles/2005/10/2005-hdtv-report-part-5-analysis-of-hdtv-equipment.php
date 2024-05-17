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
		AND e.entry_id = 235";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 235 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 235 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 235";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-5-analysis-of-hdtv-equipment.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 235";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 5: Analysis of H/DTV Equipment" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 5: Analysis of H/DTV Equipment" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2005 HDTV Report, Part 5: Analysis of H/DTV Equipment" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 5: Analysis of H/DTV Equipment</title>
	<meta name="keywords" content="qam cable, lcd panels, announced ces, qam cablecard, price range, integrated, new, models, announced, lcd, ces, panels, technology, cable, sets, cost, features, chips, larger, range, lcos, qam, unidirectional, price, tuner" />
	<meta name="description" content="Panel technology and microchip-based displays have taken a larger position in the HDTV market.  The price reduction experienced on LCD and Plasma panels is remarkable compared to the year before.  DLP and LCD are the main technologies now used for RPTV's, although CRT is not yet withdrawing from the market.  Many major companies are still introducing new lines of RPTV and Direct-view models based on CRT technology, which continues to offer the best value for a good quality display known for its excellent rendition of black, provided space and weight are not a constraint.  Additionally, with the new introduction of slim tubes using 33% less depth, direct-view sets might create a shift on the market share for second/third room applications, where small LCD panels were starting to be adopted in 2003/4.  The LCoS technology is showing some successes but also some disappointments, there is a parallel effect of companies switching in and out of the LCoS technology depending of the manufacturer or chip-company. LCD panels are becoming larger and larger from one year to another; last year's 40-inch screens introduction seemed a big step forward, a step into the domain of plasmas; this year the competition for even larger panels is heating up.  The larger LCD TV panels are now at 1920x1080 resolution on the 45+ sizes, while in plasmas the 1080p resolution is seen on much larger sizes.  CES 2005 showed a larger volume of integrated sets to meet the FCC mandate and deadlines, however, there are still at least two issues to be resolved: a) the cost of integration and HD-STB's is still too high, and b) integrated QAM CableCARD tuners are being implemented with only unidirectional limited functionality. The integration extra cost was expected to come down, and is gradually happening, but is not yet to the level it should be.  Consumers purchasing a HDTV might not be aware of the actual cost of integration they are subjected to endure.  Are you one of those?" />
	<meta name="title" content="2005 HDTV Report, Part 5: Analysis of H/DTV Equipment" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2005 HDTV Report, Part 5: Analysis of H/DTV Equipment" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-5-analysis-of-hdtv-equipment.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panel technology and microchip-based displays have taken a larger position in the HDTV market.  The price reduction experienced on LCD and Plasma panels is remarkable compared to the year before.  DLP and LCD are the main technologies now used for RPTV's, although CRT is not yet withdrawing from the market.  Many major companies are still introducing new lines of RPTV and Direct-view models based on CRT technology, which continues to offer the best value for a good quality display known for its excellent rendition of black, provided space and weight are not a constraint.  Additionally, with the new introduction of slim tubes using 33% less depth, direct-view sets might create a shift on the market share for second/third room applications, where small LCD panels were starting to be adopted in 2003/4.  The LCoS technology is showing some successes but also some disappointments, there is a parallel effect of companies switching in and out of the LCoS technology depending of the manufacturer or chip-company. LCD panels are becoming larger and larger from one year to another; last year's 40-inch screens introduction seemed a big step forward, a step into the domain of plasmas; this year the competition for even larger panels is heating up.  The larger LCD TV panels are now at 1920x1080 resolution on the 45+ sizes, while in plasmas the 1080p resolution is seen on much larger sizes.  CES 2005 showed a larger volume of integrated sets to meet the FCC mandate and deadlines, however, there are still at least two issues to be resolved: a) the cost of integration and HD-STB's is still too high, and b) integrated QAM CableCARD tuners are being implemented with only unidirectional limited functionality. The integration extra cost was expected to come down, and is gradually happening, but is not yet to the level it should be.  Consumers purchasing a HDTV might not be aware of the actual cost of integration they are subjected to endure.  Are you one of those?" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=235', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-5-analysis-of-hdtv-equipment.php">2005 HDTV Report, Part 5: Analysis of H/DTV Equipment</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 14, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p>Panel technology and microchip-based displays have taken a larger position in the HDTV market.  It was remarkable the price reduction experienced on LCD and Plasma panels compared to the year before.  DLP and LCD are the main technologies now used for RPTVs, although CRT is not yet withdrawing from the market.</p>

<p>Many major companies introduced new lines of RPTV and Direct-view models based on CRT technology, which continues to offer the best value for a good quality display known for its excellent rendition of black, provided space and weight are not a constraint.  Additionally, with the new introduction of slim tubes using 33% less depth, direct-view sets might create a shift on the market share for second/third room applications, where small LCD panels were starting to be adopted in 2003/4.</p>

<p>The LCoS technology is showing some successes but also some disappointments.  There is a parallel effect of companies switching in and out of the LCoS technology depending of the manufacturer, or chip-company.  Some justified by the difficulty of manufacturing the chips, some for the limited availability of them, some due to company direction.</p>

<p>For example, Intel announced at CES 2004 their entry to the LCoS chip manufacturing, then they announced in August a delay in the delivery of LCoS chips for projection TVs, the chip could not be made available by the end of 2004 as planned; but later, the company announced the cancellation of the overall effort.  Its competitor, Advanced Micro Devices Inc., is still on course manufacturing chips as planned.  The LCoS versions of Sony (SXRD) and JVC (D-ILA) chips and HDTVs continue firm with the technology, while other companies such as Sears became interested in LCoS and introduced at least one set, although it is uncertain if the product would actually be released as planned.</p>

<p>LCD panels are becoming larger and larger from one year to another; last year's 40-inch screens introduction seemed a big step forward, a step into the domain of plasmas; this year the competition for even larger panels is heating up.  The plasmas and LCD panels announced at CES 2005 overlap in the 37 to mid-50-inches range; the new large LCD TV panels generally cost more than similar size plasmas, twice as much in many cases, but prices are coming down fast for both types of panels.</p>

<p>The larger LCD TV panels are now at 1920x1080 resolution on the 45+ sizes, while in plasmas the 1080p resolution is seen on much larger sizes.  One example of LCD TV panel is the Sharp's 1080p 45" LCD TV panel (AQUOS models 45GD4U and 45GD6U) available since fall of 2004 at an original price of $10,000 MSRP (but seen at $8000 range on the street); other panels in the range of 55, 57, and 65 inches were announced at CES by several manufacturers.</p>

<p>Some DLP lines of front projectors that were typically in the range of $10,000 to $13,000 over the last 3 years (such as the FPTV competition of Yamaha, Sharp, Marantz, etc) are being replaced by updated models but at about the same price range.  New features and better technology and chips on the new models seem to justify upholding of the price range.</p>

<p>A similar effect has been noticed with some companies that are introducing new models at a about the same prices than the sets they replace, if not higher.  The new sets are suited with newer or better features or chips (such as HD2, HD3, HD2+ TI's DMD chips), or/and add mandated built-in ATSC/QAM cable tuners, which generally increases the TV price between $400-$1000.  Tuner integration is starting to show some signs of cost reduction, manufacturers that charged an extra $700 for the integrated HD tuner on their 2003/4 models, are now charging $500 for their 2005/6 models and in some cases even less.</p>

<p>CES 2005 showed a larger volume of integrated sets to meet the FCC mandate and deadlines.  However, there are still at least two issues to be resolved: a) the cost of integration and HD-STBs is still too high, and b) integrated QAM CableCARD tuners are being implemented with only unidirectional limited functionality.  Unfortunately, when having the tuner inside the HDTV, physical tuner upgrades/replacements would not be as easy as a STB.  However, if they were designed properly, they should be able to receive firmware upgrades downloaded from the service provider, as STBs do.</p>

<p>The integration extra cost was expected to come down, and is gradually happening, but as indicated above is not yet to the level it should be.  Consumers purchasing a HDTV might not be aware of the actual cost of integration they are subjected to endure.  Soon, there will not be monitor-only versions to facilitate some comparisons, such as lower-cost monitors compared against identical integrated versions.  Subscribers of satellite services would have no option than to pay for an integrated set with tuners he/she would not need.  All those issues still exist throughout the last few years.</p>

<p>Regarding the issue of QAM Cable CARD with only unidirectional features, the recently announced 2005/6 models, and most probably the not yet announced 2006 models from some companies, will not come with bi-directional features.  Samsung seems as the only company that made an agreement for bi-directional features (recently with CableLabs), but their 2005 models were announced as only unidirectional.</p>

<p>In other words, consumers might need to wait for at least another year or two before seeing bidirectional features in future QAM Cable CARD integrated HDTVs, which makes for a total waiting of 3 to 4 years from the initial cable plug-and-play agreement approved by the FCC, if not more.</p>

<p>The consequence of such waiting is that many more millions of integrated TVs would be sold over the next two years suited with just unidirectional features, probably in the range of 30+ million judging by the trend of sales (in 2004 alone the yearly sales jumped to 7 million sets from 3 million in 2003).  The cumulative total of QAM CableCARD integrated sets sold by the end of 2006 could be close to 40 million sets.</p>

<p>Unfortunately, the FCC approved plug-and-play agreement has stretched its plan for unidirectional-soon-to-be-bidirectional longer than expected.  Many of those 30+ million consumers will be footing the cost of an early replacement of an otherwise good integrated HDTV w/unidirectional QAM Cable tuner, to get a bi-directional version, or by having to lease the duplicated Cable HD-STBs for the bi-directional services. Perhaps you might be one of those consumers.<br />
  <br />
CES showed a large adoption of DVI and HDMI connections in HDTV displays, HD-STBs, and DVD players with upconversion to HD.  Such connection is for the transmission of protected digital HD uncompressed video.  HDMI will provide also multi-channel digital audio over the same cable.  Check the Digital Connectivity section for details on this area, the industry is moving consistently in that direction.</p>

<p>The support for IEE1394 on integrated tuner equipment has increased.  Such feature allows for HD networking and external HD recording to DVRs and D-VHS.  Make sure the integrated set you want has 'activated' IEEE-1394 two-way connections.  Even when present, they might only work with the manufacturer's proprietary implementation of it, incompatible to other brands and models, such as the case of the Toshiba's new stand-alone DVR (Symbio) designed to work only when paired to certain new Toshiba's integrated TVs.  Check the HD-DVRs sections.</p>

<p>New products are beginning to show support for the Broadcast Flag content protection, as mandated by the FCC.  There is a section at the end of the report that covers that subject, or read my article regarding DTV content protection regulations, on issue # 4 of the HDTVetc magazine.</p>

<p>When looking at the following listings of equipment, remember that the date that appears a head of each grouping (like Oct 04) indicates product announcement/introduction, and provides a perspective of the maturity of a product that facilitates comparisons within the same manufacturer, or other manufacturers.  The date also helps anticipate that new products could be announced shortly in 2005, following the manufacturer's annual cycle, when out of the CES timing.  A good number of manufacturer groupings will also have a CES 2005 subheading usually located towards the end of each group to include all the information announced/disclosed at CES 2005.</p>

<p>Regarding acronyms and terms, I include at the end of the report a Glossary of Terms related to H/DTV and Home Theater technology.  I use the term TTM to indicate Time To Market (product reaching the stores); TBA as To Be Announced (used for time or price); '1394' or 'IEEE1394' (FireWire connection) interchangeably; 'CR' for Contrast Ratio (check the glossary for the definition); and 'component' for component video inputs/outputs to carry HD analog signals between pieces of HD equipment, mostly known as 3-wire YPbPr but could be VGA RGB or BNC.</p>

<p>Be sure that you read the next article in the series: <a href="http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_repor_4.php">CRT, LCoS, D-ILA, SXRD, SED, and LCD</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 14, 2005  1:45 PM</b>
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
			<?=getComments(235)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 235)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-5-analysis-of-hdtv-equipment.php" type="text/javascript" charset="utf-8"></script>
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