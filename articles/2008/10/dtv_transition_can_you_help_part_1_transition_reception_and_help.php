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
		AND e.entry_id = 1522";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1522 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1522 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1522";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-1-transition-reception-and-help.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1522";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help" />
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
	<title>HDTV Magazine - DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help</title>
	<meta name="keywords" content="dtv transition, cable satellite, million households, transition help, help part, dtv, digital, transition, antenna, analog, might, cable, those, households, help, million, tvs, broadcast, satellite, public, air, part, people, could, fcc" />
	<meta name="description" content="The purpose of this series of articles is to help the public and the industry with the Digital Television (DTV) transition, and to motivate you to help others.

Due to the imminent DTV transition deadline of February 17, 2009, and because the subject is complicated to many in the public, it deserves to be explained in detail, so I will cover the topic in several articles within the series of &quot;DTV Transition - Can YOU Help?&quot;

If you are interested in a more in-depth analysis of the evolution of the DTV implementation..." />
	<meta name="title" content="DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-1-transition-reception-and-help.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The purpose of this series of articles is to help the public and the industry with the Digital Television (DTV) transition, and to motivate you to help others.

Due to the imminent DTV transition deadline of February 17, 2009, and because the subject is complicated to many in the public, it deserves to be explained in detail, so I will cover the topic in several articles within the series of &quot;DTV Transition - Can YOU Help?&quot;

If you are interested in a more in-depth analysis of the evolution of the DTV implementation..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1522', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-1-transition-reception-and-help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception, and Help</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 20, 2008</b>
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
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_2_a_technical_view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></li>
<li><a href="/articles/2008/10/dtv_transition_can_you_help_part_3_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/11/dtv_transition_can_you_help_part_4_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_can_you_help_part_6_subsidy_settopboxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>Part 1 - Transition, Reception, and Help </b> <p>The purpose of this series of articles is to help the public and the industry with the Digital Television (DTV) transition, and to motivate you to help others.  <p><a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29" target="_blank">DTV includes HDTV</a>; when there is a need to be more HDTV specific I will state it that way.  <p>Due to the imminent DTV transition deadline of February 17, 2009, and because the subject is complicated to many in the public, it deserves to be explained in detail, so I will cover the topic in several articles within the series of "DTV Transition - Can YOU Help?"  <p>If you are interested in a more in-depth analysis of the evolution of the DTV implementation please consult my annual HDTV Technology Review Reports.  <p>The HDTV Magazine publishes and distributes the <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php" target="_blank">Consumer Editions</a> (2003, 2004, 2005, 2006, and 2007, with 354 pages), now free.  <p>A more comprehensive 560-page <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp" target="_blank">2007 Industry Edition</a> is published and distributed by <a href="http://www.displaysearch.com/cps/rde/xchg/displaysearch/hs.xsl/index.asp" target="_blank">Display Search</a>.  <h2>How Can You Be Part of the Solution?</h2> <p>There are enough factors to be concerned about with this transition, especially in the cases of retirement homes and low-income people, but little can be gained when some just concentrate on enlarging the concern, rather than investing the effort in helping the public.  <p>Those that have some DTV knowledge or have been through the DTV experience already, and more specially, those in journalism with the power of the pen, must do the effort to be part of the solution and educate the uninformed or unprepared by giving them useful information, guidelines, web sites (some included at the end), and ideas to make their transition as smooth as possible, especially with the public sectors mentioned above.  <p>However, if you are knowledgeable about DTV, be sensitive with the way you provide the help. Without intending to, you might overwhelm some people with technical specs and data and produce a natural rejection to a mystified DTV instead. Start by asking those you want to help the following question:  <p>"How much do you need to know and need me to tell you so you can make your DTV decisions?"  <p>I have been doing the above for 10 years already, and I invite you to do your part, hopefully these articles would be useful for that purpose. Note that this public service should not end on February 17, 2009; we should continue helping others even after the transition deadline has passed.  <h2>DTV Transition - What Is It?</h2> <p>The DTV transition started in November 1998 when broadcasters started to use the parallel 6MHz channels that were approved by the FCC (Federal Communications Commission) to broadcast their over-the-air (OTA) content as analog and digital simultaneously. At that time, the first DTV sets and stand-alone STB (set-top-box) tuners became commercially available, and the public was able to tune to some limited broadcast DTV.  <p>Originally, the DTV transition was supposed to be completed by January 2007, the time when analog broadcast would stop.  <p>But on February 2006 the US President approved a 2-year extension of the DTV transition deadline, which postponed the target date to February 17, 2009. The approval also included the budget for the last phase of the transition.  <p>The budget included the federal government's Digital TV coupon program, by which a subsidy for two $40 coupons per household was approved for those households that need to buy OTA converter STBs, so they can continue using their analog TVs for digital broadcasts. A separate article about this subject is coming later in the series.  <p>The approved budget also included a fund to expand public education about DTV and a fund to run the federal government's Digital TV coupon program mentioned above.  <p>Between November 1998 and February 2006 (when the extension of 2 years was approved), a considerable effort was made to implement DTV by equipment manufacturers, broadcast stations, cable and satellite companies, content providers, etc. All invested heavily on the DTV technology during those first 7 years.  <p>The public also invested dearly by purchasing about 30 million DTVs in that first period. During the first couple of years early-DTV-adopters paid very high prices compared to today, which facilitated companies to reinvest in further research and development, to bring DTV prices down, and manufacture better equipment in larger quantity.  <p>As an example, a 42-inch plasma cost about $12,000 in 1999; now a better one cost about 10% of that. Some technologies disappeared (such as the old and reliable CRT), some DTV manufacturers dropped some TV technologies to concentrate on others, and LCD/plasma panels became economically accessible to many and took over a large part of the current market.  <h2>The DTV Deadline and People</h2> <p>In May 2008, the press announced a Nielsen report indicating that "78 percent of U.S. households are 'completely ready' for the switchover, 9.4 percent are 'not ready', and 12.6 percent are only 'partially ready".  <p>While I agree with some in the press saying that the deadline will arrive and many people would still say that they do not know what to do and might keep claiming that the government and the industry were not as informative and organized as they could be, it is also true that many from the public are not actively helping themselves or others as much as they should, not showing proper interest, not asking or offering help, or postponing actions up to the last moment.  <p>Many consumers might be waiting for the price of those dreamed DTVs to be lower to match their purchasing threshold, or in hope that the analog broadcast may not actually be shut-off as planned.  <p>Regardless of their situation and reasons, those that are DTV savvy should find a manner to help those in need, by offering solutions and ideas, rather than sitting on the sidelines once their own DTV case is solved.  <p>Survey takers from whom many journalists obtain data for their articles could use the precious moment of contacting thousands of people not only to obtain the data, but also to properly inform those lost in the DTV transition, guide them to the right sources, and tell them what they can do for their situation. That would certainly be a better public service than just counting the beans.  <h2><b>Over-The-Air Reception - How Large is the Group?</b></h2> <p>The Consumer Electronics Association (CEA), the National Association of Broadcasters (NAB), the GAO office, and others, keep refining different estimates of how the public receives television. The average of those estimates for over-the-air broadcast TV is about <b>15 million</b> out of 112.8 million households on the U.S.  <p>The household and TV inventory count for the transition and for the full replacement of analog TVs, is explained in detail in a later part of this series.  <p>The 15-million figure is also supported by <a href="http://i.ncta.com/ncta_com/PDFs/NCTA_Annual_Report_05.16.08.pdf" target="_blank">this report</a> (page 13) from the National Cable Television Association (NCTA). Of the 112.8 million households, 65 million households subscribe to cable, and the rest (112.8 - 15 - 65 = 32.8 million) subscribe to satellite, FiOS, IPTV, etc.  <p>The 15-million households are the ones facing the main impact of the <i>broadcast</i> DTV transition because that is the only manner they receive TV <i>on all the TVs in the home</i>. Assuming the existing antenna is appropriate for the digital reception, this group has two alternatives in continuing to use their over-the-air antenna:  <p>a) Change the primary and secondary TVs for digital integrated sets, or  <p>b) Get a maximum of two $40 subsidy-coupons to buy two $40-$60 tuner-converters. Use the converters to avoid replacing two of the existing analog TVs. The other TVs in the home would need additional tuner-converters that are not subsidized by the government (limit of two).  <p>The other alternative for this group is to switch to subscription TV services (cable/satellite), but more on that later.  <p>But that is not a complete picture of the impact: cable/satellite subscribers could be impacted as well. Subscribers to cable/satellite/FiOS may use a STB for the main TVs, but this group of 97.8 million households (112.8-15) may still use an over-the-air antenna to tune to free TV on secondary TVs in the house, although the CEA often says that those are usually connected to video game consoles and VCRs.  <p>These 97.8-million households might have another alternative for their secondary TVs (in addition to a and b above), as follows:  <p>Request an additional cable/satellite STB instead of using the antenna for each secondary TV. The government coupon program does not apply for this type of STB. The subscriber STB has to be leased or purchased by the consumer.  <p>For both groups above, cable services might mean having the subscriber to pay additional digital tier fees, not just for the cable STB lease. If the cable company already switched all the subscribers from analog to digital, it would require the subscriber to pay for the digital tier plus the lease of digital STBs.  <p>I will cover this subject in more detail in another part of this series, but the FCC gave cable companies the flexibility to do the complete switch to digital until 2012, which would allow subscribers of some cable companies to continue receiving the analog feed, if offered.  <p>Those subscribers might not even need to lease an analog STB if the TVs is/are connected directly from the wall coax for the basic channels, and the cost of that service could just be the basic analog tier only. When the company decides to switch to digital, a digital tier plus a digital STB lease for each analog TV would have to be paid.  <p>That is one of the side effects the DTV transition might inflict on those that might not be prepared for it, especially low-income and retirement-home people who could be economically forced beyond their means when having to switch from free TV to subscription TV services.  <p>If you can help any of these people you might also be indirectly doing a service to his/her friends or relatives that may follow the same approach due to similar conditions.  <p>Another issue that I cover below in more detail is that many people might find difficult to tune digital channels that were viewed normally as analog using the same antenna. This could be due to particular circumstances of their location, broadcast station, line of sight, or installation (more on it below), and only installing a government coupon program STB will not solve that, a better antenna might be needed.  <p>So in summary, a) the OTA population that could be affected by the DTV transition is actually beyond the 15 million OTA-only households, b) a coupon program STB might look as a zero-expenditure solution but it could still require a better antenna for which there is no subsidy, and c) switching to subscription services might be a guaranteed solution but might be prohibitive to many.  <h2>Have You Tried to Receive Over-The-Air DTV?</h2> <p>Over the past 10 years there were many successful and also many complicated cases of people trying to tune digital via an antenna. Some people may be fine with their current antenna; some might just need a better antenna, or an outdoor antenna, or just reorient the same antenna (manually or with a rotor).  <p>Many millions of TV viewers might have experienced a similar situation decades ago when they gave up their rabbit-ears indoor antenna and installed an outdoor antenna to improve the reception of the whole house, and possibly added a rotor as well.  <p>This antenna improvement issue has been around for decades and should not surprise people. Some in the industry were advertising "digital" antennas for DTV, implying that all the current antennas used for analog would not work for digital signals, which is not true. However, it has always been the case that some antennas are better than others.  <p>If the public (and the press) concentrates on the complicated cases and make that negative image the focal point of the DTV transition, regardless of the deadline, we would not be able to move forward, we have to let people see the big picture.  <p>For example, in February and April 2008, Centris (<a href="http://www.centris.com/" target="_blank">www.centris.com</a>) released a couple of reports. They found serious gaps in the digital TV signal coverage across the country. Half of the over-the-air viewers, they said, might be in a challenging reception situation and be limited to tune only four or fewer broadcast TV stations with a small/medium omni-directional rooftop antenna or an indoor antenna, and added that in some cases an antenna upgrade might be necessary.  <p>"40 million households are currently receiving over-the-air analog signals in the U.S., reflecting a combined total of as many as 117 million sets that are unconnected from cable or satellite video networks", they said.  <p>The 40 million households include their estimate of about 17 million households that are over-the-air-only (as opposed to the 15-million average from other sources mentioned before). Centris calls those <i>primary</i> households.  <p>The remaining 23 million households (to make the 40) are called <i>secondary</i> and, consistent with my independent analysis above, those households that are connected to cable/satellite may still use an over-the-air antenna to watch TV on the additional sets in the home, perhaps to avoid paying for additional STBs for those.  <p>Additionally, the reports identified that about 54% of those 17 million over-the-air-only <i>primary</i> households might have difficulty using their existing antenna to receive digital signals. Over 9 million households might have difficulty from only the <i>primary</i> group.  <p>The tuning difficulty and the antenna upgrade should also apply to the cable/satellite households using antenna for additional TVs in the home (<i>secondary</i> homes).  <p>Regarding the geographic areas of concern, according to Centris, the most affected cities are:  <ul> <li>New York  <li>Boston (Manchester)  <li>Philadelphia  <li>Los Angeles  <li>Washington, DC (Hagerstown)  <li>Seattle-Tacoma  <li>San Francisco-Oakland-San Jose  <li>Minneapolis-St. Paul  <li>Atlanta  <li>Cleveland-Akron (Canton) </li></ul> <p>Hopefully those "challenging" cases would be limited to a minority of households.  <p>In my discussions with Centris the company claims that the public is not being made aware by the Government education program that the transition to digital may require a possible antenna upgrade, in addition to the converter STB for analog TVs.  <p>Another point made by Centris was that by motivating consumers to buy Digital TVs and converter STBs that might not work as expected under the existing antenna conditions, is like a trial and error approach, and could also impact retailers and manufacturers due to equipment returns.  <p>I understand that the Centris reports were made to bring to light some problem areas of the DTV transition, including public education, it is always good to identify areas of improvement, I just wish that the press-releases from Centris would have been a bit more positive in recognizing also the hard effort the industry, government, consumers, content distributors, content providers, etc. have made over 10 years, which although is not perfect, it was able to make this transition as good as we have it today; not to mention appreciating the decades of vast engineering effort invested to "create" DTV.  <h2>An Early Switch to Digital - The Wilmington Case</h2> <p>The FCC performed a planned early switch to digital in September in Wilmington, NC. The CEA offered converter boxes to a number of long-term-care people on that area.  <p>This was an opportunity to evaluate with an actual case the tuning problem reported by Centris among other transition issues, and was also useful to obtain information about lessons to be learned for the imminent whole-US switch.  <p>The Washington Post stated that Wilmington is the 135th-largest television market in the United States, with about 180,000 television households across five counties, according to Nielsen. About 7 percent of those households (approximately 14.000) rely on analog broadcasts, compared with a national average of about 13 percent, the other 93 percent of Wilmingtons' households subscribe to cable or TV service, the report said (and I add, one more public source of information slightly differing from the 15 million average number from major sources mentioned above). <p>The report also added, "A dozen calls to local nursing homes and retirement communities revealed that even those who typically rely most on over-the-air signals -- senior citizens -- have nothing to worry about".  <p>The Wilmington switch to digital was considered successful. Only a relatively small number of households called for help, mainly related to rescanning digital channels on the converter box, antenna re-orientation, or technical issues.  <p>According to Broadcasting &amp; Cable, the first day of the analog shut-off (Monday September 8<sup>th</sup>), FCC officials said they had received 797 calls from viewers to its hotline from Wilmington, N.C., some of whom were not aware that the switch had taken place and others who were having trouble hooking up converters. The following day the number of calls lowered to 424, of those, 23 residents were not aware of the switch. In summary ""one-half of one percent of area homes" called for help.  <p>FCC's Chairman Kevin Martin said that one lesson learned from this early implementation is that more information regarding converter box set up should be disclosed in the consumer education efforts.  <p>According to CedMagazine, FCC's Commissioner Michael Copps, based on the Wilmington's experience, detailed a proposal to help prevent problems for the larger shut-off effort for the rest of the country planned for February 17, 2009, as follows:  <ul> <li>Conduct additional field-testing  <li>Dedicate a special FCC team to the needs of at-risk communities  <li>Ramp up the FCC Call Center  <li>Prepare comprehensive DTV contingency plans  <li>Create an online DTV Consumer Forum  <li>Educate consumers on DTV trouble-shooting, including antenna issues and the need to "re-scan" converter boxes and sets  <li>Ensure that broadcasters meet their construction deadlines  <li>Encourage the rapid deployment of small, battery-powered DTV sets  <li>Find a way to broadcast an analog message to consumers following the transition. </li></ul> <h4></h4> <h2>A DTV Tour of Duty</h2> <p>In August 2008, the FCC implemented a nationwide program to educate the public about the DTV transition to be carried out before the February deadline, by which the FCC chairman Kevin Martin and his fellow commissioners would visit key US markets.  <p>According to TWICE, the tour is to "prepare consumers for the transition, we have identified television markets in which the largest number of viewers will have to take action to be prepared for the transition six months from now," stated Martin. "This unprecedented nationwide tour by the entire Commission is part of our commitment to prepare and educate consumers about the digital television transition."  <p>Periodic updates were to be issued to monitor the program while helping local broadcasters, community leaders, and other stakeholders to prepare for the digital transition. Additionally, coordination with NAB was made for some broadcaster's temporary participation to "soft tests" (temporary turn off of their analog signals) to ascertain consumer readiness.<b></b>  <h2>Are Broadcasting Stations Ready? </h2> <p><b></b> <p>Broadcast &amp; Cable's news on August 2008 said that a new FCC report disclosed that 97% of broadcasters are either on the air with their digital signal at full power or will be by Feb. 17, 2009.  <p>56% (1002 stations) have fully constructed facilities ready for the transition, 41% of the stations (716) said they are not done with construction but expect to be ready at full power by February 17<sup>th</sup>. 502 of those said they were making good progress, other 234 cited special circumstances, including 10 that had to coordinate with Mexico, five that had Fish and Wildlife clearance issues.  <p>Regarding extensions, according to the Washington Post, the National Association of Broadcasters said that some of its member companies voluntarily agreed to extend until March 4, 2009 (2 weeks after the shut-off) the availability of local analog broadcast signals to cable, satellite, and telecom TV operators, among those, ABC, NBC, ION Media Networks, Univision, Telemundo, Gannett Broadcasting, and Hearst-Argyle Television were mentioned.  <h2>My Tuning</h2> <p>In my particular case I have been tuning HDTV since 1998 digital stations located in the Washington, Virginia, and Maryland areas.  <p>I used the same Channel Master antenna I had installed in the attic for analog reception in the early nineties. The digital signal had to go through the roof's layers and through two leafy tree lines. Even with those obstacles, I was able to tune stations from as far as Baltimore MD, which is 75 miles north of my home in northern VA.  <p>While the analog versions displayed images with snow, bleeding colors, and much inferior quality, the digital versions tuned and displayed well, although with occasional image freezes, sometimes too many for prolonged viewing.  <p>To be honest, the typical metropolitan stations within normal reach showed very sharp in digital, making analog unattractive to the point of ignoring the channel on the favorite list.  <p>For those distant out-of-state channels, I admit that I was pushing the tuning envelope, but I was testing everything out there, and I was surprised that I could even get a digital image from so far away, not to mention such quality image.  <p>However, although the digital image quality was superior, the interruptions in the digital feed of stations that were so far away made the analog version more valuable, even with snow on the image, when viewing content that required continuity, such as a final match on any sport, not to mention how important that could be during an emergency broadcast.  <p>I had a choice of analog or digital at that time, after Feb 17, 2009 I will not have that choice anymore.  <p>Over the past 10 years of HDTV I had experimented with antenna, cable, and satellite.  <p>In terms of image quality, the over-the-air HD channels broadcasted at their full bandwidth using the entire 6 MHz at 19.4 Mbps of bit rate, when available, showed noticeable better than the same versions distributed by satellite.  <p>Satellite typically applies additional compression to maximize their use of their limited total service bandwidth shared with many channels, reportedly reducing the 19.4 Mbps broadcast quality by approximately 40% of its bit-rate at times, which typically causes loss of detail, macro-blocking, and image freezes on some fast action content.  <p>Depending on the cable company, the over-the-air image quality is also often superior to cable for similar reasons; cable companies also have limited bandwidth to share with many channels. In other words, over-the-air HD broadcast at full bandwidth/bit rate offers the best image, and is also free.  <h2>What Can You Do for the Transition?</h2> <p>I offered some options, ideas, and solutions earlier in the article, and I will also do the same on the next parts as well, but ultimately, if you are in a difficult tuning location, the option of switching to cable or satellite (or FiOS) might be better than investing on an antenna solution, and certainly better than viewing no TV at all. For those that can afford subscriber services it could also be an opportunity to receive many additional channels that are not available with an antenna.  <p>If the rabbit-ear antenna approach does not work for you, the option of subscriber services might be preferable and practical, and maybe even economically better, when compared to the up-front investment of possibly a full set of outdoor antenna components that might be required if your tuning situation is complicated, such as an high quality outdoor antenna, amplifier, rotor, tall mast, mounting kit, wiring, grounding, installation labor, etc.  <p>One must consider that the cable/satellite service option can also be a concern if you are very sensitive with the quality of HD images and excessive compression might impact that.  <p>The other way around could also be true if the outdoor antenna solution for your case is simple and inexpensive, while the option of satellite/cable installation might be economically unattractive for your needs considering the total package of one-time installation fee plus the monthly fee for a prolonged period of service.  <p>Hopefully a local antenna expert could also evaluate if the antenna components would work for your particular location before installing the antenna system, which facilitates the comparison with the cost of subscriber services before any installation dollar is spent.  <p>You might be lucky, have the rabbit-ear antennas work well on all of your TVs, and the limited broadcast channel line-up be enough for your needs. That scenario would cost you a) zero for your existing digital TVs, b) almost nothing for two coupon converter STBs for two analog TVs, plus c) the full cost of additional converter STBs for other analog TVs you might have (beyond the 2 supported by Government program coupons).  <h4>Some Links to Find Help and Help Others</h4> <ul> <li><a href="http://www.hdtvmagazine.com/" target="_blank">HDTV Magazine</a>, of course (www.hdtvmagazine.com) - Read our articles, contact us with questions, and participate in the <a href="http://www.hdtvmagazine.com/forum" target="_blank">HDTV Forum</a>.  <li>Official DTV Transition site (<a href="http://www.dtvtransition.org/" target="_blank">http://www.dtvtransition.org/</a>)  <li>Information on the DTV Coupon Program (<a href="http://www.dtv2009.gov/" target="_blank">http://www.dtv2009.gov/</a>) - In Spanish: (<a href="http://www.dtv2009.gov/es/" target="_blank">http://www.dtv2009.gov/es/</a>)  <li>The FCC's DTV page (<a href="http://www.dtv.gov/" target="_blank">http://www.dtv.gov/</a>) - In Spanish (<a href="http://www.dtv.gov/spanish/" target="_blank">http://www.dtv.gov/spanish/</a>)  <li>DTV 101: A Consumer's Guide to Digital Television (<a href="http://www.youtube.com/watch?v=re_zirX84xI" target="_blank">http://www.youtube.com/watch?v=re_zirX84xI</a>)  <li>What is the Digital TV Transition? (<a href="http://www.youtube.com/watch?v=wIIjT3xotHU" target="_blank">http://www.youtube.com/watch?v=wIIjT3xotHU</a>)  <li>How do I hook-up a Digital to Analog TV Converter? (<a href="http://www.youtube.com/watch?v=bTevFtRyA88&amp;feature=related" target="_blank">http://www.youtube.com/watch?v=bTevFtRyA88&amp;feature=related</a>)  <li>CEA Digital Tips (<a href="http://www.digitaltips.org/" target="_blank">http://www.digitaltips.org/</a>)  <li>CEA CE Know-How (<a href="http://www.cyberscholar.com/ceknowhow/digitalTelevision.cfm" target="_blank">http://www.cyberscholar.com/ceknowhow/digitalTelevision.cfm</a>)  <li>Digital To Analog Converter - How to do it (For the hearing impaired) (<a href="http://www.youtube.com/watch?v=SmUPLF2H4w0&amp;feature=related" target="_blank">http://www.youtube.com/watch?v=SmUPLF2H4w0&amp;feature=related</a>)</li></ul> <p>In the <a href="/articles/2008/10/dtv_transition_-_can_you_help_part_2.php">next article in the series</a>, I will cover a brief list of technical benefits of DTV, which by itself gives most people a good reason for its implementation to replace analog TV.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 20, 2008 11:25 AM</b>
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
			<?=getComments(1522)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1522)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-1-transition-reception-and-help.php" type="text/javascript" charset="utf-8"></script>
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