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
		AND e.entry_id = 724";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 724 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 724 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 724";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/10/iptv-part-3-the-methods-and-a-working-technology.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 724";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download IPTV Part 3 - The Methods and a Working Technology" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="IPTV Part 3 - The Methods and a Working Technology" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="IPTV Part 3 - The Methods and a Working Technology" />
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
	<title>HDTV Magazine - IPTV Part 3 - The Methods and a Working Technology</title>
	<meta name="keywords" content="iptv part, real time, video codec, data bit, video output, iptv, video, matrixstream, mpeg, stb, part, viewing, vod, content, output, company, imx, internet, using, service, channel, hdtv, speed, support, providers" />
	<meta name="description" content="When using standard MPEG-2 compression, an HD channel requires about 19 Mbps of bandwidth to be transmitted. If using DSL or cable modem, although it is considered hi-speed for typical Internet services, it currently has the capacity to transmit only a small fraction of what raw HDTV requires.

Even when re-compressing the 19 Mbps with more efficient compression algorithms like MPEG-4 or VC1, and even when using additional transmission-saving techniques to fit HDTV content into those typical hi-speed Internet services, the approach would be a challenge, not to mention that after making use of this bandwidth for HDTV there will be little or no headroom left for Internet downloads of music, files, photos, etc.

Additionally, it becomes less feasible to..." />
	<meta name="title" content="IPTV Part 3 - The Methods and a Working Technology" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="IPTV Part 3 - The Methods and a Working Technology" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/10/iptv-part-3-the-methods-and-a-working-technology.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="When using standard MPEG-2 compression, an HD channel requires about 19 Mbps of bandwidth to be transmitted. If using DSL or cable modem, although it is considered hi-speed for typical Internet services, it currently has the capacity to transmit only a small fraction of what raw HDTV requires.

Even when re-compressing the 19 Mbps with more efficient compression algorithms like MPEG-4 or VC1, and even when using additional transmission-saving techniques to fit HDTV content into those typical hi-speed Internet services, the approach would be a challenge, not to mention that after making use of this bandwidth for HDTV there will be little or no headroom left for Internet downloads of music, files, photos, etc.

Additionally, it becomes less feasible to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=724', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/10/iptv-part-3-the-methods-and-a-working-technology.php">IPTV Part 3 - The Methods and a Working Technology</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October  2, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV Part 1 - Read the Fine Print</a></li>
<li><a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_5_-_additional_implementations.php">IPTV Part 5 - Additional Implementations</a></li>
<li><a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></li>
</ul></div>
<br />

<p><B>Different Methods of HDTV Over IP</B></p>

<p>When using standard MPEG-2 compression, an HD channel requires about 19 Mbps of bandwidth to be transmitted. If using DSL or cable modem, although it is considered hi-speed for typical Internet services, it currently has the capacity to transmit only a small fraction of what raw HDTV requires.</p>

<p>Even when re-compressing the 19 Mbps with more efficient compression algorithms like MPEG-4 or VC1, and even when using additional transmission-saving techniques to fit HDTV content into those typical hi-speed Internet services, the approach would be a challenge, not to mention that after making use of this bandwidth for HDTV there will be little or no headroom left for Internet downloads of music, files, photos, etc.</p>

<p>Additionally, it becomes less feasible to consider accommodating additional parallel real-time HD channels to satisfy the individual viewing needs of a typical home with several TVs.</p>

<p>Some IPTV advertising campaigns say, "We could download any HD program of your choice into the DVR for later viewing", the download could happen while you sleep so the downloading speed would not need to be as fast as the viewing speed from the DVR.</p>

<p>Others say, "We could send the program for real-time viewing" which generally means one selection from a group of options, like a VOD service.</p>

<p>Other IPTV service providers claim to have found a way to send several HD feeds throughout various rooms in the home.</p>

<p>Others advertise having hundreds of channels on the line up, but fail to elaborate on the viewing restrictions, compression artifacts, freeze ups, single TV per home limitations, etc.</p>

<p>IPTV service providers like AT&T and Verizon consider themselves to have an advantage over cable companies because their IPTV services only require just enough bandwidth to send the selected channel.</p>

<p>This means they do not need to send out all the 150 parallel channels like cable and satellite. More specifically, the channel tuning selection is not done the traditional way as with terrestrial, cable, etc, where a viewer chooses from a wide selection of parallel channels arriving to the STB and selects only one from the multi-channel stream.</p>

<p>When using IPTV, you select and request delivery of the specific program to your STB from the line up. Such delivery could be viewed in real-time or be downloaded to a DVR for later viewing, depending of the service and installed hardware.</p>

<p>IPTV is being implemented in different flavors, and part of the reason for the variation is that while some neighborhoods have very limited Internet speed, others have been provisioned with very fast fiber optic networks.</p>

<p><br />
<B>Current/Planned IPTV Market Solutions</B></p>

<p>A couple of years ago I was contacted by a company that developed an HD-IPTV system. Using this solution, consumers would purchase client STBs for PCs and stand-alone TVs and receive IPTV content even at 1080p quality, as claimed by the company.</p>

<p>MatrixStream is the name of that company, founded in 1999 and headquartered in Vancouver, British Columbia. I included their launched HD 1080p server/client IPTV products on last year's annual HDTV Technology report (March 2006, 2006 HDTV Technology Report).</p>

<p>The interesting part was that the company also offered hardware to enable "anyone" to become a content distributor via IP from a server to clients in a network.</p>

<p>The company adopted MPEG-4 Part 10/H.264 compression which halves the stream requirements to transmit via IP for typical HDTV content compressed with MGEG-2. MatrixStream also implemented techniques to optimize the transport over the Internet by using proprietary buffering and error-correction features that compensate for Internet bottlenecks.</p>

<p>MatrixStream claims they can transmit a DVD-quality TV signal requiring only 1.5 Mbps and a high-definition 1080p signal requiring only 2.5 Mbps, both within the limitations of typical DSL and cable-modem hi-speed connections.</p>

<p><br />
<B>MatrixStream IPTV Technologies</B></p>

<p>Over a year ago the company introduced the world's first VOD and IPTV HD-STB using H.264 AVC (advance video codec) adapted for 1080p, with 80GB of HDD, to receive SD and HD IPTV signals over broadband.</p>

<p>The STB allows high bandwidth users (1.5 Mbps+) to view videos in real time via live streaming. For users with lower bandwidth, the IMX Set Top Box (STB) has the proprietary video preload feature that preloads videos to the STB cache prior to viewing.</p>

<p><img src="/images/products/matrixstream.jpg" alt="Matrixstream IMX 1020HD IPTV HD STB" align="right" /><u>IMX 1020HD IPTV HD STB</u><br />
Available for trials since Jan 06, TTM 1Q06, supports HD 720P, 1080i and 1080P formats, H.264/MPEG 4 Part 10, streaming video, download and push VOD, 1080p over HDMI, component analog able to output 1080i subjected to downrez if the content protection requires it, HDCP over HDMI is activated depending on the content provider contract and STB (the boxes are offered world wide so it varies by location).</p>

<p>According to the company, the IPTV signal will always be protected by encryption. It is up to the service provider to decide if they want to turn HDCP on or off depending on the contract agreement for content they have made for IPTV delivery. Usually a customer will get the set-top box from the service provider directly, however, some service providers might choose to provide it over retail, i.e.: Best Buy.</p>

<p><u>IMX 1000 IPTV STB</u><br />
Designed to support Windows Media/VC-1 video codec, fully supports push VOD, download VOD, and streaming VOD in Windows Media format over the Internet.</p>

<p><u>IMX 1100 PC Player</u><br />
Available since Jan 06 , originally from the movie99.tv website. It offered over 300 free channels from around the world and 150 free DVD and HD quality movie clips.</p>

<p><br />
<u>Deployment Diagram</u><br />
The following is an example of how an IMX 1000 STB is deployed in a VOD environment. Each STB is connected to a TV through standard RCA output, S-Video output, component video output, or DVI output. Each STB fully supports Dolby Digital 5.1 Surround Sound via the optical PCM output.</p>

<p>Source: <a href="/cgi-bin/ntlinktrack.cgi?http://www.matrixstream.com/" target="_blank">MatrixStream</a></p>

<p><img src="/images/articles/matrixstream-vod-solution.jpg" alt="MatrixStream VOD Solution" /></p>

<p>According to the company, "MatrixStream's solution is generally marketed to broadband providers seeking an opportunity to increase ROI by deploying video over their networks. On the back-end, broadband providers have access to one of the most cost-effective, scaleable VOD systems available, complete with billing, management, subscriber management, channel management, and digital rights management. MatrixStream's solution supports industry standard video codecs like MPEG4, VC-1, and H.264 and is capable of supporting all future video codecs".</p>

<p>"MatrixStream's IPTV solution is automatically programmed with features currently available on DVDs, including interactive menus, subtitles, multiple audio tracks and video chapters."</p>

<ul><li>High Definition Video Support - MatrixStream's video viewing clients display DVD and HD videos.</li><li>Video Content Security - MatrixStream offers extensive security measures, including built-in Microsoft DRM (Digital Rights Management) support with the option to add any 3rd party DRM system. Dynamic watermarking management technology is also utilized to protect content from piracy.</li><li>Advanced Video Codec Support - MatrixStream fully supports multiple industry standard advance codecs such as H.264, MPEG 4 Part 10, and VC-1. MatrixStream designed its IPTV system to support all existing and future subsequent video codecs.</li><li>Dynamic Advertising Module - MatrixStream allows IPTV operators/broadband providers complete control over dynamic ad insertion, targeting viewers based on personal preferences and interests.</li><li>Flexible Viewing Options - MatrixStream supports video viewing on both PC and the IP STB clients. STB clients can be remotely upgraded with new releases and additional options. MatrixStream viewing clients can also be ported to third party platforms and OEM STBs.</li></ul>

<p><u>Video Formats</u><br />
NTSC/PAL composite<br />
NTSC/PAL s-video<br />
Analog YPbPr / RGB<br />
150 MHz YCbCr / RGB digital video output interface<br />
- 8-bit 4:2:2 YCbCr data<br />
- 16-bit 4:2:2 YCbCr data<br />
- 24-bit 4:4:4 YCbCr data<br />
- 24-bit RGB data (888)<br />
- BT.601, BT.656, or VIP 2.0, "video valid" output signal<br />
- Master or slave timing</p>

<p><u>Resolution / Frame Refresh Rates</u><br />
704/720 x 480i 30Hz<br />
704/720 x 480p 60Hz<br />
704/720 x 576i 25Hz<br />
704/720 x 576p 50Hz<br />
1280x720p 50/60Hz<br />
1366x768p 50/60Hz<br />
1024x1024p 50/60Hz<br />
1920 x 1080i 25/30Hz<br />
1920 x 1080p 50/60Hz</p>

<p><u>Audio Formats</u><br />
- 16-bit linear PCM with HDCD support<br />
- MPEG-1 and MPEG-2 Layers I, II and III (MP3) 2.0<br />
- MPEG-2 BC multi-channel Layers I, II and III 5.1<br />
- MPEG-2 and MPEG-4 AAC-LC 2.0<br />
- MPEG-2 and MPEG-4 HE-AAC 2.0<br />
- MPEG-4 SBAC 2.0<br />
- Dolby Digital 5.1<br />
- DTS 5.1</p>

<p><u>Back panel connections</u><br />
- WMA9@L3 2.0, WMA9 Lossless 2.0, WMA9 Pro@M2 5.1, LAN: 10/100 Base-T, RJ 45<br />
- Connectivity: Two USB 2.0 ports<br />
- Video: HDMI/DVI, S-Video, RCA composite, Y/Pb/Pr<br />
- Audio: S/PDIF, Left/Right channel audio output</p>

<p><img src="/images/articles/matrixstream-player-requirements.jpg" alt="MatrixStream Player Requirements" /></p>

<p>In September 2006, MatrixStream released a new HD IPTV package featuring video on demand (VOD), an IPTV basic IMX500 middleware server, an IMX 2410 XMS streaming server, and an IMX 4010 video encoder, capable of handling up to 500 concurrent users, <$70,000, XMS streaming technology, H.264 compliant, fully integrated, end-to-end solution, including billing management, subscriber management, channel management and digital rights management.</p>

<p>According to MatrixStream, the package can be implemented in a very short time and with minimum cost of deployment. The system performs over any broadband network with no Quality of Service (QoS) requirements.</p>

<p>In the next article, I will analyze several of these services and highlight the pros and cons.</p>

<p>Next Article: <a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October  2, 2007  7:51 AM</b>
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
			<?=getComments(724)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 724)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/10/iptv-part-3-the-methods-and-a-working-technology.php" type="text/javascript" charset="utf-8"></script>
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