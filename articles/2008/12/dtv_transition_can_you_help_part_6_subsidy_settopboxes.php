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
		AND e.entry_id = 1590";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1590 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1590 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1590";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-6-subsidy-settopboxes.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1590";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</title>
	<meta name="keywords" content="dtv transition, coupon program, converter boxes, cable satellite, digital analog, million, coupons, dtv, households, digital, analog, converter, tvs, program, transition, coupon, ota, could, part, help, cable, number, viewers, boxes, dtvs" />
	<meta name="description" content="On July 2006, based on an estimate of the number of households who rely solely on OTA television broadcasts, the U.S. Commerce Department proposed coupons for an estimated 21 million U.S. households to aid the purchase of converter boxes,

Congress passed a law providing an initial $990 million dollars within a $1.5 billion program to subsidize the purchase of converter boxes" />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-6-subsidy-settopboxes.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="On July 2006, based on an estimate of the number of households who rely solely on OTA television broadcasts, the U.S. Commerce Department proposed coupons for an estimated 21 million U.S. households to aid the purchase of converter boxes,

Congress passed a law providing an initial $990 million dollars within a $1.5 billion program to subsidize the purchase of converter boxes" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1590', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-6-subsidy-settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December 31, 2008</b>
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
<li><a href="/articles/2008/11/dtv_transition_can_you_help_part_4_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
</ul></div>
<br />
<p align="center"><b>Part 6 - Subsidy Set-Top-Boxes</b>
<h2>Why a Government Subsidy for DTV?</h2> <p>As you may know by now, due to the analog to digital switch on February 17, 2009 a viewer of over-the-air (OTA) analog broadcast would need a digital tuner converter set-top-box (STB) between the antenna and the analog TV to view digital TV, or alternatively would need to pay for a subscription service, such as cable, satellite, FiOS, etc.  <p>Although the DTV transition started 10 years ago and analog and digital broadcasts were transmitting in parallel to give ample time for viewers to experience and even fully switch to digital, the analog TV shut-off of February 2009 might still affect millions of over-the-air households that did not timely buy new integrated DTVs or digital tuners for their analog TVs.  <p>To facilitate the DTV adoption and to reduce the impact on broadcast viewers, the US Government approved a special budget with a <a href="https://www.dtv2009.gov/">coupon program</a> to help analog TV viewers purchase digital-to-analog STB converters. The budget also included a program to educate the public about DTV. The coupon program offers up to two $40 coupons applicable to two STBs per household. There are limitations on these coupons related to date of request, redemption period, coupon applicability, etc.  <p>Both programs have been in operation since early 2008, and were designed to make the DTV transition smoother to the public, the industries, and Government itself.  <p>At the end of <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">part 1 (Transition, Reception, and Help)</a> I included several links with information for readers that would help make the DTV transition as smooth as possible. Toward the end of <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php">part 5</a> I discuss the alternatives and the connectivity requirements for all types of viewers, services, and TVs.  <p>This part (6) complements part 5 and specifically addresses <a href="https://www.dtv2009.gov/">coupon-program STBs</a> needed by over-the-air viewers for their analog TVs, but also needed by cable/satellite/Telco subscribers that for economic or convenience reasons have secondary analog TVs connected to a separate antenna, rather than to the subscription service to which the main TV sets are usually connected.  <h2>The Budget for the DTV Converter Box Subsidy</h2> <p>On July 2006, based on an estimate of the number of households who rely solely on OTA television broadcasts, the U.S. Commerce Department proposed coupons for an estimated 21 million U.S. households to aid the purchase of converter boxes,  <p>Congress passed a law providing an initial $990 million dollars within a $1.5 billion program to subsidize the purchase of converter boxes (the Senate proposed 3 billion, the House proposed $1 billion). The remaining $510 million was to be released by Congress to help TV households with only over-the-air antennas receiving analog NTSC.  <p>Upon considering the proposal, the government requested public comment. Some suggested limiting the coupons to low-income families living below the poverty level, which was not implemented.  <p>Under the plan any consumer that needs economic support to acquire a <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">Digital-to-Analog converter box</a> could request up to two $40 coupons. Originally, converter boxes were estimated to be about $60 each and the consumer would pay for the difference, today the converter STBs are available between $40 and $80. Two coupons cannot be applied to one box, and a coupon cannot be used for other types of tuners (full HDTV, cable, etc).  <p>It was assumed that this would not affect consumers who own integrated digital televisions, or subscribe to satellite services or digital cable services because the service provider would supply the necessary STB.  <p>However, as mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">part 1 (Transition, Reception, and Help)</a> of this series, many millions of cable/satellite/Telco subscribers tuning OTA with an antenna on secondary TVs would be affected as well, which could impact the number and availability of coupons, and the budget. Conversely having many OTA viewers switching to subscription services rather than requesting their coupons could help reduce the overall need of economic support from the Government budget.  <p>Consumers that could demonstrate eligibility were to apply between Jan 1, 2008 and March 31, 2009. 22.25 million coupons were to be made available to all U.S. households in the first batch, then, additional 11.25 million coupons were to be made available only to OTA households.  <p>The National Association of Broadcasters (NAB) estimated that 73 million TVs were not connected to cable or satellite, the Government Accounting Office (GAO) estimated that number as 44 million TVs, other organizations provided different estimates as well. The CEA estimated the OTA broadcast population as 15 million households.  <p>As detailed in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">part 3 (TVs vs. Households)</a>, 15 million households having an average ratio of 3.1 TVs per household (about 45 million TVs) is a number of TVs that is not that far from the 44 million estimated by the GAO.  <p>However, as I mentioned above and illustrated in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">part 1 (Transition, Reception, and Help)</a>, many satellite/cable subscribers having secondary analog TVs connected to an antenna could add considerably to the number of impacted households beyond the 15 million. The question is how many households are in that situation and what would they rather do for February 17, 2009? They may decide to use their existing subscription services also for their secondary TVs, but if that means paying for additional subscription STBs such option may not be as economical as free broadcast.  <p>The plan was for the <a href="http://www.ntia.doc.gov/">National Telecommunications &amp; Information Administration</a> (NTIA), responsible for the distribution of the $40 coupons, to have the system operational by January 2008.  <p>Some basic rules were initially established in March 2007 for the coupon program:  <ul> <li>22+ million coupons were to be made available from the first $990 million part of the fund.  <li>Starting January 1, 2008, coupons can be requested via toll-free phone number, Web site, fax or postal mail, until March 31, 2009.  <li>Converter boxes could cost between $50 and $70 each.  <li>NTIA can request Congress for the remaining funds for an additional 11+ million coupons if needed, but they are reserved for households that self-certify that only receive TV over the air (no cable or satellite).  <li>No income limit is required for coupons.  <li>Coupons could not be used for equipment exceeding the services of just digital broadcast tuning (DVR or DVD recorder for example). Features include EPG, software upgrades, antenna inputs, and video outputs.  <li>Coupons expire within 90 days of receipt, releasing the money for further coupons (and disallowing a household for more requests than the permitted two). </li></ul> <p>In August 2007 the NTIA approved a $120 million contract for IBM to run the Digital TV coupon program, which includes coupon distribution, consumer education, and the reimbursement to retailers when receiving the coupons from consumers.  <h5></h5> <h2>DTV Education Campaign</h2> <p>In February 2007, the FCC requested from Congress $1.5 million in its 2008 budget for a DTV education campaign, which includes producing PSA's, Web material, publications, participation in forums, work with the NAB and the Association of Public Television Stations to air the PSA's, distributing the information to low-income and minority consumers, translating it into Spanish, Chinese, Korean and Vietnamese, educating the children (for them to educate their parents), etc.  <p>According to Broadcast &amp; Cable Magazine, in February 2008 President Bush, under some criticism, proposed another $20 million within the fiscal budget for 2008 for the Federal Communications Commission to educate consumers on the transition to Digital TV.  <p>Tony Wilhelm, NTIA consumer education and public information director on the TV converter coupon program, commented that in addition to the Government funding, millions of dollars worth of advertising, support, and airtime are being contributed by the industries.  <h2>Low-Power/Translator Stations</h2> <p>Low-Power/Translator Stations are permitted to continue transmitting in analog after the February 17 switch over to digital. The DTV-to-analog converter boxes are not mandated to pass-through analog signals nor are they permitted to have NTSC tuners for analog reception.  <p>Under that situation some consumers getting converters not featuring analog-pass-through may not been able to view the low-power channels unless they connect the antenna also to the analog TV for it to tune the analog low power channels.  <p>In order to facilitate the migration to digital, as reported by Broadcasting &amp; Cable, the Senate approved the release of the "left over" funds for the DTV transition with an amendment to the Deficit Reduction Act for the DTV-to-analog converter box program to help assist senior citizens minorities and rural viewers in preparing for and making the transition, and the Commerce Committee agreed to allow the Government to make $65 million dollars available to help low-power broadcasters make the switch to digital by February 17, 2009, the date required for full-power stations, rather than having them wait until the planned October 2010 to get the funds.  <h2>The Converter STB - A Bit of History</h2> <h5></h5> <p><u>The Prototype </u></p> <p>In July 05, the Association for Maximum Service Television Stations (MSTV) and the NAB announced a program to develop a prototype of a terrestrial digital converter box (TDCB) to convert broadcasters' ATSC VSB digital transmissions and MPEG coding to the NTSC format. The following features were listed as the original design goals for the TDCB:  <ul> <li>Inexpensive, does not compromise over-the-air performance;  <li>Processes all ATSC video formats;  <li>Delivers video and stereo audio to NTSC receivers on either TV Ch. 3 or 4, along with a base-band composite video output with stereo audio;  <li>Must have robust front-end performance, including multi-path &amp; overload immunity;  <li>Small and lightweight;  <li>Easy to install and operate;  <li>Transparent to the user;  <li>Be PSIP-compliant and have a friendly menu guide;  <li>Comply with closed captioning, EAS, and the required parental controls;  <li>Include a detachable antenna and a smart external antenna interface;  <li>Be operable by remote control.  <li>Responses are due by noon on July 22, 2005.  <li>A working prototype expected by the end of the 2005.</li></ul> <p>Note that the video outputs on the converter STB above are only 480i over RF or composite video, which means that although the STB must be able to tune to SD, ED and HD digital channels it would "only" output them at NTSC analog quality.  <p>In other words, the converter would not be able to used for full HD functionality if connected to an HDTV monitor, a feature that all HD-STBs offer (in addition to analog outputs), but costing 3 times as much.  <p><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; margin: 0px 5px 5px 0px; border-right-width: 0px" height="284" alt="analog-to-digital set-top-box" hspace="12" src="http://www.hdtvmagazine.com/images/articles/19dc50640d15_291E/clip_image002.jpg" width="376" align="left" border="0">In September 2005, LG, parent company of Zenith, chip maker Zoran, Motorola, and Thomson successfully demonstrated on Capitol Hill prototypes of digital-to analog (D-to-A) technology connected to small indoor antennas and with side-by-side screens of analog and digital reception, including multicast channels.  <p>The LG demo was of a fifth-generation reception technology that handled multipath interference well at locations previous generations did not perform well.  <p>LG showed a prototype of a finished product measuring 6.5-by-1.5-by-4.3 inch, weighting under 2 pounds, and using the 5G-plus technology above. In 2005, LG anticipated that the D-to-A converter could retail for $50 by 2008 assuming millions of units could be ordered if a hard-date is set by Congress (which later in 2006 was extended to February 17, 2009). The $50 estimate in 2005 is actually happening as planned in 2008.  <p>Consumers can now choose from over a <a href="https://www.ntiadtv.gov/cecb_list.cfm">hundred models</a> manufactured by dozens of companies.  <p><u>Maximum Power Consumption</u></p> <p>In October 2006, a proposal was made to the Environmental Protection Agency (EPA) and the National Telecommunication and Information Administration by the CEA, the NAB, the Consumer Electronics Retailers' Coalition, the Association for Maximum Service Television, and the Natural Resources Defense Council to establish a maximum power usage level on next-generation digital-to-analog converter boxes.  <p>The proposal was in response to an EPA request for the development of an Energy Star program for digital-to-analog converter boxes, and for the adoption of the following requirements:  <ul> <li>Consumption of no more than 8 watts of power in "on" state;  <li>Consumption of no more than 1 watt of power in "sleep" state;  <li>The STB shall meet the auto power down requirements to automatically switch from the "on" state to the "sleep" state after a period of time without user input;  <li>The factory must enable the default setting for the auto power down as four hours, and must remain unaltered unless the user chooses so;  <li>The STB may allow the current program to complete before switching to the Sleep state.</li></ul> <p>The CEA suggested that the sleep state measurements follow the industry standards CEA-2022 and CEA 2013-A.  <p><u>Actual Converter Boxes First Shown at CES 2007</u></p> <p><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; margin: 0px 5px 5px 0px; border-right-width: 0px" height="197" alt="analog-to-digital set-top-box" hspace="12" src="http://www.hdtvmagazine.com/images/articles/19dc50640d15_291E/clip_image004.jpg" width="387" align="left" border="0">At CES 2007 an LG's OTA terrestrial STB was unveiled. The STB was suited with an MPEG-2 ATSC tuner, Dolby 2 channel, Energy Star Compliance, 480i out only via composite, RF channel 3 or 4, stereo L/R audio. The company advanced that their STB would be sold for $60 at retail in early 2008.  <p>One of the key issues was to make sure later-generation chips are included in these STBs. Greg Zancewicz, Microtune product marketing manager, said his company would like to see Government start a certification process where DTV sets and set-tops could be labeled "A/74-compliant."  <p>Texas Instruments introduced the TVP9007 Converter Box to support the DTV transition with an ATSC to NTSC converted processor with integrated 8-VSB/QAM demodulator and HDTV processor.</p> <p>In March 2007, Samsung announced their plans for a D/A TV converter to be offered in time with the NTIA coupon program.  <h2>How Many Coupons could actually be needed? </h2> <p><b></b> <p>The subject can be analyzed from several perspectives.  <p>One simple perspective (mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">Part 1</a> and above) is that ALL of the 15 million over-the-air (OTA) TV households would NOT have integrated-DTVs and would ALL request two coupon-program converter STBs for two analog TVs. 30 million $40 coupons for 30 million analog TVs would add to $1.2 billion; that alone is 80% of the total $1.5 billion budget.  <p>From another OTA viewer perspective, the households in need could be 2 or 3 times larger than the 15 million when counting cable/satellite subscriber households that use an antenna for secondary TVs.  <p>Since cable/satellite subscriber households were allowed to request coupons within the first batch, the second batch of the budget reserved for OTA-only viewers might be insufficient if a large number of the 15-million-household-group delays their coupon requests and the first batch was largely redeemed.  <h2>DTVs and Coupons</h2> <p>From the DTV installed base perspective, between 1998 and 2008 113.7 million DTVs were sold, including 32.6 million projected for 2008 to be confirmed in 2009. 113.7 million DTVs are about 1/3 of the total inventory of (any kind of) TVs in the whole US (346 million).  <p>As discussed in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">part 3 (TVs vs. Households)</a>, those DTVs are not actually installed 1-to-1 in a similar number of (113.7 million) households. Almost a year ago the CEA estimated 50% of US households were digital TV ready, or 56 million households based on the total 112.8 million households.  <p>Considering that early adopters purchased most of the DTVs over the 10 years of the DTV transition and those may already have 2 or 3 DTV sets at home, my estimate is a bit lower than CEA's 50%. I estimate the DTV household footprint to be around 40%, or 45 million homes, housing the 113.7 million DTVs.  <p>Nielsen looks at it from another perspective. In December 2008 it was reported a Nielsen study that concluded that the number of households "having and viewing HDTV" is in the 23% range as of November 2008, doubling the 10% reported in July 2007.  <p>Another study estimated the number of households "having HDTV" at around 30% mainly in the areas on Boston, Washington DC and New York, while Detroit had about 21% of HDTV households.  <p>A distinction needs to be made between "having a DTV" vs. "having an HDTV" vs. "having an HDTV 'and' viewing HD content with it". While those surveys convey a perspective of adoption of HDTV and HD content, an HD level of quality is not required for DTV or for a digital content; it has been optional since the DTV transition started in 1998. Not long ago many plasmas were just ED, 480p resolution quality.  <p>To be " ... able to experience the reality of digital television", as expressed by the CEA (toward the end of <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">Part 3 (TVs vs. Households)</a>), a) an integrated DTV does not have to be of HD resolution quality (it would sufficient for it to be 480i SD, or 480p ED quality), and b) a household does not have to receive digital content at HD quality to "experience digital television", and be ready for the transition.  <p>An "integrated" DTV of any resolution could also mean one less coupon needed by the public, which is the point of this section.  <p>Although a minority of the DTVs sold since 2003 are tuner-less monitors, most are integrated with OTA digital tuners, regardless of whether they can display a tuned HD image at its full resolution or not.  <p>Many of the 113.7 million DTVs sold since 1998 could have been installed in many of the 15 million OTA households and in many of the cable/satellite subscriber homes in need for OTA tuners for their secondary TVs.  <p>Theoretically, that should lower the need for coupon-program converter STBs, which reportedly is one of the reasons the FCC moved the mandate of integrated digital tuners on every DTV forward.  <p>However, as I analyzed in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">part 3 (TVs vs. Households)</a>, many of the 113.7 million DTVs did not actually replace disabled old analog TVs in a household, but rather added to the number of TVs within the same household, increasing the 2007 ratio from 2.6 to 3.1 TVs per household in 2008. Which brings the next perspective.  <p>Since the majority of over-the-air households that already have DTV would still need digital-to-analog converter boxes for the active analog TVs connected to an antenna in secondary rooms, the DTV installed base of 113.7 million DTVs could not be considered as a full factor of reduction of the need of subsidized digital-to-analog converters for either primary or secondary applications.  <p>Exactly how many of those 113.7 million DTVs would have reduced the need of coupon-program STBs for OTA households (primary and secondary) is uncertain until that specific research is made; however, surveying HDTVs and HD content viewing does not convey how the DTV installed base could affect the coupon-program estimates.  <h2>Recent Status of the Coupon Requests</h2> <p><b></b> <p>On a recent report, Meredith Baker, acting administrator of the Commerce Department's National Telecommunications &amp; Information Administration, declared that 62% of OTA households have requested government coupons to help pay for the price of subsidy converters.  <p>Ms. Baker also said that about 17 million households requested 33.5 million coupons of which 13.5 million have been redeemed.  <p>The same report indicated "Of the nation's 210 television markets, NTIA said that in 172 more than 50% of the over-the-air households have applied for coupons and 45 markets have a 75% or greater participation rate.'  <p>On a more recent update, according to USA Today December 26, 2008, "so far, about 22 million households have requested more than 41 million coupons, the NTIA says. Only about 14 million have been redeemed."  <p>On December 30, 2008, just 4 days after the USA Today report, the Washington Post reported (from the same NTIA) 44 million coupons requested, of which 18 million were redeemed.  <p>Judging by the number of requested coupons it seems that the total budgeted by the program has been reached already. If all of those are actually redeemed within their 90-day period it could mean that no more funds could be available for more coupons, even for OTA-only viewers.  <p><b></b> <p>One source related to the coupon program indicated: "If you applied for just one coupon and it expires before you use it, then you may apply for a second coupon."  <p>The problem that I see is that coupons might not be available by the time you decide to apply, either because all the requested coupons are already redeemed or because those that are not yet redeemed are holding the access to newer coupons until they expire.  <p>Another related source indicated: "Over 8 million of the requested coupons for digital converter boxes have expired and cannot be used again." In theory, those unused coupons should release their committed funds so the program can approve further coupon requests.  <h2>Running Out of Funds </h2> <p>Based on the information discussed above in the article it seems that the coupon program may run out of funds and not have enough coupons for all, which could affect many OTA TV viewers that may be waiting until the last minute to join the digital transition, unaware that their delay for the switch to digital would be more difficult when not finding coupons available later on.  <p>According to Broadcasting and Cable, just a few weeks ago (in October 2008), the FCC told Congress that the DTV-to-analog converter box coupon program could "run out of money before it runs out of requests for the $40 coupons, suggesting the calculations of the government agency responsible for administering the program may be off."  <p>From the same report, in letters to House Energy &amp; Commerce Committee Chairman John Dingell (D-MI) and Telecommunications &amp; Internet Subcommittee Chairman Ed Markey (D-MA), FCC's chairman Martin says says he is "increasingly concerned about the funding of the program" overseen by the National Telecommunications and Information Administration (NTIA), who also said the program might run out of funds when requesting funds for the coupon processing job.  <p>Reportedly, Martin suggested NTIA could have underestimated the number of coupons it will need, which was based on data from Nielsen that there were only 13.7 thousand OTA households in Wilmington, N.C., where analog TV was shut-off on September 8, and, Martin said, as of September 30 19.1 thousand requests from Wilmington houses identified themselves as over-the-air-only. Extrapolating that to the rest of the country, he said, instead of 14 million OTA households, as has been projected by NTIA, there could be 19.5 million.  <p>From the same report, the "NTIA also predicted that the current 49% redemption rate for coupons would remain steady, even as requests for coupons rose toward the February 17, 2009 transition date. But Martin says recent trends suggest that rate will rise. Add to that the addition of nursing homes and post office boxes to the rolls of eligible households, and "it is difficult to predict whether the converter box program is adequately funded," wrote Martin."  <p>In other words, estimating a more accurate number of OTA viewers is showing the actual complexity when including a) OTA viewers into cable/satellite subscriber households, b) those that claim to be OTA-only when they are not, and c) those viewers that do not actually need/redeem the requested coupons (which distorts the requirements). The combination of those factors affects the funding and the timing for the DTV transition. Since we are at just a couple of months from the deadline my suggestion is if you really need the coupons do not delay your request.  <p>This concludes this series of "DTV Transition - Can YOU Help?" articles.  <p>I hope the content was informative and useful for you, and I also hope you become part of the solution by helping others with it, which was the primary purpose of these articles.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December 31, 2008 11:30 AM</b>
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
			<?=getComments(1590)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1590)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/12/dtv-transition-can-you-help-part-6-subsidy-settopboxes.php" type="text/javascript" charset="utf-8"></script>
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