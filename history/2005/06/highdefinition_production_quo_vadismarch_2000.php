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
		AND e.entry_id = 74";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 74 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 74 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 74";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/highdefinition_production_quo_vadismarch_2000.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 74";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000" />
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
	<title>HDTV Magazine - HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000</title>
	<meta name="keywords" content="high definition, program production, hdtv program, per second, hours per, hdtv, production, definition, high, quality, format, television, today, program, programs, per, broadcast, cbs, film, system, electronic, digital, international, hours, been" />
	<meta name="description" content="HDTV is always the best, not the second best, not the third best, and not the previous best. Today, the best is 1080/1920 at an aspect ratio of 16:9, interlace and progressively scanned. Today, this 1080 line digital wide screen, high definition format has escalated &quot;Broadcast Quality&quot; to a plateau never before imagined.
" />
	<meta name="title" content="HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/highdefinition_production_quo_vadismarch_2000.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="HDTV is always the best, not the second best, not the third best, and not the previous best. Today, the best is 1080/1920 at an aspect ratio of 16:9, interlace and progressively scanned. Today, this 1080 line digital wide screen, high definition format has escalated &quot;Broadcast Quality&quot; to a plateau never before imagined.
" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=74', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/highdefinition_production_quo_vadismarch_2000.php">HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 12, 2005</b>
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
				<p>Another in this series of addresses from Dr. Joseph Flaherty, CBS. <br />
An Address Given to the CANADIAN SATELLITE USERS ASSOCIATION CONFERENCE<br />
TORONTO, CANADA<br />
MARCH 23, 2000 </p>

<p>HIGH-DEFINITION PRODUCTION Quo Vadis</p>

<p>Over the years, many systems have been called High Definition Television' As far back as 1935, in a report on the emergence of television, David Sarnoff, then President of RCA said:</p>

<p>"Public interest in television continues unabated since ... RCA stated that it was diligently exploring the development of television. Our laboratory efforts have been guided by the principle that the commercial application of such a service could be achieved only through a system of high-definition television."</p>

<p>Thus, in 1935 it was 343 lines, in prewar England it became 405 lines, by the 1939 New York World's Fair it was 441 lines, 525 line NTSC was introduced as a "high definition color television system", and in latter day Europe HDTV became 625 lines.</p>

<p>In short HDTV has always been, and will always be, the best quality achievable with a given state-of-the-art. HDTV is always the best, not the second best, not the third best, and not the previous best. Today, the best is 1080/1920 at an aspect ratio of 16:9, interlace and progressively scanned. Today, this 1080 line digital wide screen, high definition format has escalated "Broadcast Quality" to a plateau never before imagined.</p>

<p>The constant search for higher quality television has been endless, and there has never been a significant quality improvement in television technology that has not become a part of everyday American life. HDTV is just the latest such "must have" technology.</p>

<p>While engineers love to agonize over the relative benefits of interlace and progressive scanning and over the relative importance of spatial and temporal resolution, there is no question whatsoever that 1080/1920P is vastly superior to any other high definition system by at least a million pixels-per-frame. Moreover, 1080/1920p is with us today in 24, 25, and 30 frames-per-second with 50 and 60 frames-per-second about two years away. In fact, as you already know, all programs produced on film are always broadcast in the progressive format today.</p>

<p>Anyone in this room that believes, or wants to believe, that the public won't ever want wide screen digital HDTV when it's offered are taking a "bet-the-business" gamble.</p>

<p>Beginning last year, the new U.S. program season saw the massive launch of digital HDTV programming throughout the country. This, coupled with the rapid rollout of DTV stations that now cover over 58% of television homes, able to reach over 100 million people, forms a solid base for the DTV/HDTV transition in America.</p>

<p>The latest report from the FCC shows that:</p>

<p>• 410 stations have applied for a permit to construct digital television facilities.<br />
• 306 stations have been granted permits,<br />
• 119 stations are already broadcasting DTV and HDTV,<br />
* 25 television markets have two or more DTV/HDTV stations on-the-air,</p>

<p>In markets, ranked lower than 30, the digital transition continues with 21 stations now on-the-air with more to come.</p>

<p>As the digital transition takes place, there will be a host of transmission formats available to broadcasters, DTH and cable operators from the inferior VHS format through 480 I&P, 720P, to full 1080 I&P HDTV. While transmission systems have severe bandwidth restrictions that limit the ultimate quality today, HDTV program production has far more bandwidth flexible and important program production requires the highest possible quality. Thus, the production and distribution of programs is a wholly different matter from the multiple transmission and delivery systems employed by the multiple distribution media in important prime HDTV production it is critical to capture, record, post produce, and finish productions in the highest possible quality to protect the finished product quality and to protect the archive value for future broadcast and for both domestic and international syndication sales. Programs may be downconverted to all the lesser transmission formats, but they must be mastered in full HDTV to remain competitive in the domestic and world program markets.</p>

<p>As to the all-important HDTV programs, last September CBS began transmitting 14 1/2 hours-per-week of prime time programming in 1080 I&P high definition along with "specials", movies, and major sporting events, including I 8 hours of the US Open Tennis Championship at Forest Hills.</p>

<p>Today, CBS broadcasts 15 hours-per-week of prime time programming, covering 17 individual programs and CBS will broadcast the NCAA Final Four" basketball championships in 1080 HDTV on April I to 3, and 10 1/2 hours of the Master's golf tournament April 6 to 9, live from Augusta, Georgia.</p>

<p>Most importantly all of these high definition programs are sponsored and paid for by advertisers!</p>

<p>NBC, HBO, Madison Square Garden, Warner Bros-, DirecTV, The Discovery channel, and Capitol Broadcasting are transmitting over 120 hours-per-week in the 1080 I&P HDTV format. Of this amount of HD programming, Madison Square Garden has produced and distributed 40 hours-per-month of world class basketball and hockey this season, and another 5 to 20 hours of 1080 high definition will be added to their schedule each month with the broadcast of major league baseball games this summer.</p>

<p>Today, the 1080 I&P format is rapidly becoming the unarguable HDTV program production format worldwide, and it is the only HDTV program production and international exchange standard approved by the International Telecommunications Union in its ITU-R Recommendation BT-709-3. This ITU Recommendation is based on the "Common Image Format" or CEF of 1920 samples-per-line at an aspect ratio of 16:9 with 1080 lines-per-progressively scanned at 24, 25, and 30 frames-per-second and both interlace, and progressively scanned at 50, and 60 pictures-per-second. The 1080 line production important as- it-makes-possible the electronic production of film style programs for both TV and for the cinema. Heretofore, electronic production did not meet the quality requirements of 35mm film production, and the 1920/108OP/24-frame Common Image Format has erased this quality limitation for the electronic production of film programs. In fact, 80% of the recent Star-Wars production "Episode I - The Phantom Menace' was produced electronically with the electronic camera photography done using the 1920/1080 CIF format. The Lucas organization has now announced that much of the next Star Wars production of "Episode 11 will be produced electronically with the camera photography using the 1920/108OP/24-film CIF format. With the 1920/1080124 I&P format available, why would anyone produce electronic film any other way?</p>

<p>In addition to CBS, NBC, HBO, Madison Square Garden, Warner Bros., PBS, DirecTV, The Discovery Channel, and Capitol Broadcasting using the CIF 1920/1080 HDTV format, the Asian-Pacific Broadcast Union (ABU) has adopted the 1080 CIF format as its unique HDTV program production and international exchange standard for use throughout the Asia-Pacific region.</p>

<p>Thus, after 50 years of a TV technological "Tower of Babel", the world has seen the emergence of a single worldwide HDTV program production and international exchange standard.</p>

<p>There is finally a way to move and sell HD programs and sporting events around the world in a single image format, and the world's major programmers are adopting it.</p>

<p>As high definition program schedules expand in broadcasting, in DTH and cable systems, it is important to remember that in the competition for viewers, wide screen, high definition programs will be just a "channel click" away from all lesser quality offerings, including those broadcast on both sides of the US/Canadian border. The competition will be horrific!</p>

<p>Lest the need for full HDTV in the prime program production is questioned, Hollywood has already set the pace worldwide. 80% of U.S. primetime television product is, and has been, produced in HD for over 40 years, namely 35mm film. Hollywood TV product dominates the world program market, its market share is growing, and its product is high definition. Today, much of this 35mm film product is being converted to the 1080 CIF HDTV format and broadcast as 1080P.</p>

<p>With, its high definition TV product, the growth in total revenues returned to the major U.S. production studios from 1987 through 1997 has increased over three times to a total of US $32 billion annually. While revenues from the theatrical distribution of movies have increased modestly, the revenues derived from the electronic distribution media of cable, DTH, home video, and television broadcasting, have increased 350 percent.</p>

<p>The demand for programs is a worldwide phenomenon, and today, some 40 percent of the total U.S. studio revenues are derived from the export of programs, and these exports, with the electronic media providing most of the growth, continue to increase at an annual rate of 17 percent.</p>

<p>Programmers who wish to maintain and increase their share of the domestic and international program markets will be forced to produce in HDTV, and the worldwide 1080 HD Common Image Format will dominate high definition program production and exchange in all the TV markets.</p>

<p>As you plan your way into HDTV and into the landscape of 21st century television, it is vital to understand that 1080 I&P, wide screen, high definition is not just pretty pictures for today's small screen TV sets. Rather, it is a wholly new digital platform that will support the larger and vastly improved displays already in development for near term commercialization.</p>

<p>However, viewing HDTV on present high definition displays is a bit like Mark Twain's comment that "Wagner's music is better than it sounds". Today HDTV is better than it looks! The display devices are the limiting quality factor, the low pass filter as it were. As of now, no display has achieved the full quality potential of the 1920/1080 CIF HDTV system. In making HD system decisions, beware of today's high definition system demonstrations Usually, the viewer is testing the limited display devices and not the HDTV systems themselves.</p>

<p>Yet this development is as it should be! The full potential of any new standard should never be fully encompassed by the existing state-of-the-art, nor should it be so futuristic as to not have its potential achievable in a foreseeable time. 'The 1080 CIF HDTV standard is beyond the present quality of displays, but not beyond the scope of rapid display development. Displays are getting better and cheaper - not poorer and more expensive!</p>

<p>Be forewarned! Full quality displays will rapidly improve and will continue to widen the quality gap between real 1080 HDTV and all lesser formats. Interim "good enough" system decisions are a "pay me now and pay me later" investment!</p>

<p>Finally, as you evaluate tomorrow's TV and HDTV and plan for its implementation, bear in mind that today's standard of service enjoyed by the viewer will not be his level of expectation tomorrow. Good enough is no longer perfect, and may become wholly unsatisfactory. Quality is a moving target, both in programs and in technology. Judgments as to future changes must not be based on today's performance or on minor improvements thereto.</p>

<p>Change is irresistible, as Victor Hugo noted when he wrote:</p>

<p>"An invasion of armies can be resisted; but not an idea whose time has come."</p>

<p>________________________________________________<br />
About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em><br />
 <br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 12, 2005 11:28 PM</b>
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
			<?=getComments(74)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 74)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/highdefinition_production_quo_vadismarch_2000.php" type="text/javascript" charset="utf-8"></script>
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