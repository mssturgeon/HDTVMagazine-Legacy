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
		AND e.entry_id = 4951";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4951 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4951 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4951";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-4-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4951";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
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
	<title>HDTV Magazine - Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</title>
	<meta name="keywords" content="ultra hdtv, color space, front right, hdtv ultra, naming conventions, dci, ultra, part, front, image, left, right, top, hdtv, centre, audio, living, bits, per, khz, back, resolution, required, itu, bit" />
	<meta name="description" content="Part 3 of this series addressed the overall issues of naming conventions defined by the various organizations (DCI, ITU, EBU and CEA) regarding 4K and Ultra-HDTV.

This part 4 covers the specifics of the DCI (Digital Cinema Initiatives) and ITU (International Telecommunications Union) naming conventions and standards.

Part 5 will cover..." />
	<meta name="title" content="Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-4-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Part 3 of this series addressed the overall issues of naming conventions defined by the various organizations (DCI, ITU, EBU and CEA) regarding 4K and Ultra-HDTV.

This part 4 covers the specifics of the DCI (Digital Cinema Initiatives) and ITU (International Telecommunications Union) naming conventions and standards.

Part 5 will cover..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4951', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-4-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php">Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December 21, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=369&category=4K (Ultra HD)">4K (Ultra HD)</a></b>
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
				<div class="editorial">
	This is one of a series of articles. If you are interested in other articles in this series, they are as follows:<br><ul>
		<li><a href="/articles/2012/10/living-with-4k-getting-the-beautiful-monster-part-1.php" target="_blank">Living with 4K: Getting the Beautiful Monster (Part 1)</a></li>
		<li><a href="/articles/2012/10/living-with-4k-4k-content-when-part-2.php" target="_blank">Living with 4K: 4K Content, when? (Part 2)</a></li>
		<li><a href="/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" target="_blank">Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</a></li>
		<li><a href="/articles/2012/12/living-with-4k-part-4-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" target="_blank">Living with 4K (Part 4) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</a></li>
		<li><a href="/articles/2012/12/living-with-4k-part-5-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" target="_blank">Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</a></li>
		<li><a href="/articles/2012/12/living-with-4k-part-6-which-4k-sony-dci-4k-and-ultrahd-capable.php" target="_blank">Living with 4K (Part 6) - Which 4K? Sony - DCI 4K and Ultra-HD Capable</a></li>
	</ul>
</div><p>Part 3 of this series addressed the overall issues of naming conventions defined by the various organizations (DCI, ITU, EBU and CEA) regarding 4K and Ultra-HDTV. <p>This part 4 covers the specifics of the DCI (Digital Cinema Initiatives) and ITU (International Telecommunications Union) naming conventions and standards. <p>Part 5 will cover European Broadcasting Union (EBU) and the Consumer Electronics Association (CEA) naming conventions and standards, wrap the subject, and provide a historic perspective of similar naming decisions made by the CEA with DTV. <p><b><u></u></b> <p><b>Digital Cinema Initiatives (</b><a target="_blank" href="http://en.wikipedia.org/wiki/Digital_cinema"><b>DCI</b></a><b>) 4K standard:</b> <p>And I quote from the standard document: <p><i>"A joint project of seven motion picture studios (Disney, Fox, MGM, Paramount, Sony Pictures Entertainment, Universal, and Warner Bros.), DCI published their specification in 2005....The primary purpose of DCI is to establish uniform specifications for Digital Cinema.."</i> <p><i>"The Digital Cinema system shall have the capability to present a theatrical experience that is better than what one could achieve now with a traditional 35mm Answer Print."</i> <p><i>"The specification calls for picture encoding using the ISO/IEC 15444-1 "</i><a target="_blank" href="http://en.wikipedia.org/wiki/JPEG2000"><i>JPEG2000</i></a><i>" (.jp2) standard and use of the </i><a target="_blank" href="http://en.wikipedia.org/wiki/CIE_1931_color_space"><i>CIE XYZ</i></a><i> color space at 12 bits per component encoded with a 2.6 </i><a target="_blank" href="http://en.wikipedia.org/wiki/Gamma_correction"><i>gamma</i></a><i> applied at projection, and audio using the </i><a target="_blank" href="http://en.wikipedia.org/wiki/Broadcast_Wave_Format"><i>"Broadcast Wave" (.wav)</i></a><i> format at 24 bits and 48 kHz or 96 kHz sampling, controlled by an </i><a target="_blank" href="http://en.wikipedia.org/wiki/XML"><i>XML</i></a><i>-format Composition Playlist, into an </i><a target="_blank" href="http://en.wikipedia.org/wiki/MXF"><i>MXF</i></a><i>-compliant file at a maximum data rate of 250 Mbit/s." </i> <p><b>DCI Formats:</b> 2K and 4K. K means 1024 pixels of horizontal resolution.<b></b> <p><b>2K Format:</b> 2048x1080<b></b> <p><b>4K Format:</b> 4096x2160<b></b> <p><b>Aspect Ratio:</b> 1.89 (17:9)<b></b> <p><b>Color Space</b>: 12-bit each per <a target="_blank" href="http://en.wikipedia.org/wiki/CIE_1931_color_space">X'Y'Z'</a> channel using the most significant bits of 16-bit words (filled with 4 zeros)  <p><b>Audio</b>: capacity for up to 16 channels of uncompressed audio at 24 (AES3) bits at 48 kHz or 96 kHz sampling rates, with subtitles. The presentation is required to provide, at a minimum, a 5.1 audio format, (Left, Center, Right, Low Frequency Effects, Left Surround and Right Surround). <p><b>Frame Speed<u>:</u></b> 2K: could be 24fps or 48fps (12-bit DCDM), 4K could be 24fps only<b><u></u></b> <p><b>Spatial Resolution Conversion (as per DCI standard</b>): <i>"The projector is required to display either a native resolution of 4096x2160 or 2048x1080. If the projector's native resolution is 4096x2160, and the incoming spatial resolution of the content is 2048x1080, then the projection system is required to perform the up-conversion of 2048x1080 content to 4096x2160. All spatial conversions are required to be done at an exact ratio of 2:1 in each axis, i.e., a projector with a horizontal pixel count of slightly higher than the image container is required to not convert the projected image beyond the image container to fill the array, nor is an image to be converted to something less than the 4096x2160 or 2048x1080 image container size.</i> <p><i>Should electronic image resizing or scaling be used to support a constant height projection or constant width projection theater environment, then it is required that the image resizing or scaling does not introduce visible image artifacts</i>." <p><br /><br /><p><a target="_blank" href="http://www.itu.int/net/pressoffice/press_releases/2012/31.aspx"><b>Ultra-HDTV as defined</b></a><b> by the ITU (International Telecommunication Union):</b> <p>As per ITU-R BT.2020 UHDTV <a target="_blank" href="http://www.itu.int/dms_pubrec/itu-r/rec/bt/R-REC-BT.2020-0-201208-I!!PDF-E.pdf">parameters document </a>(August 2012)  <p><b>Picture aspect ratio:</b> 16x9 <p><b>Quantization Levels:</b> 10 and 12 bit per component <p><b>Colorimetry:</b> <a target="_blank" href="http://en.wikipedia.org/wiki/Rec._2020">Rec.2020</a> <p><b>Frame frequencies (Hz):</b> 24, 30, 60, and (updated to) 120 fps, and also 25 and 50 for other systems/countries. <p><b>Scan mode:</b> progressive <p><b>Resolutions:</b> 3840x2160 and 7680x4320, also <a target="_blank" href="http://www.itu.int/net/pressoffice/press_releases/2012/31.aspx#.UK_kmtuF9Y4">called as 4K and 8K by ITU</a>: <p>“<i>The first level of UHDTV picture levels has the equivalent of about 8 megapixels (3840 x 2160 image system), and the next level comes with the equivalent of about 32 megapixels (7680 x 4320 image system).&nbsp; As a shorthand way of describing them, they are sometimes called the ‘4K’ and ‘8K’ UHDTV systems.”</i> <p><b>Recommended diagonal minimum size of screens:</b> 1.5 meters and larger screens (LSDI) of large venue presentations to "<i>provide viewers with an increased sense of “being there” and increased sense of realness"</i> and "<i>higher spatial/temporal resolution, wider colour gamut, wider dynamic range, etc."</i> <p><b>Recommended viewing distance for 8K:</b> 0.75 H&nbsp; <p><a target="_blank" href="http://en.wikipedia.org/wiki/File:CIExy1931_Rec_2020_and_Rec_709.svg"><img style="float:none" alt="" src="http://www.hdtvmagazine.us/articles/images/5434d869a5fb_F2DB/clip_image002_b34b9f5c-d626-4770-8f2a-35924ae98dff.gif" width="300" height="340"></a> <p>Diagram of the <a target="_blank" href="http://en.wikipedia.org/wiki/CIE_1931_color_space">CIE 1931 color space</a> that shows the <a target="_blank" href="http://en.wikipedia.org/wiki/Rec._2020">Rec. 2020</a> (UHDTV) <a target="_blank" href="http://en.wikipedia.org/wiki/Color_space">color space</a> in the outer triangle and <a target="_blank" href="http://en.wikipedia.org/wiki/Rec._709">Rec. 709</a> (HDTV) color space in the inner triangle. Both Rec. 2020 and Rec. 709 use <a target="_blank" href="http://en.wikipedia.org/wiki/Illuminant_D65">Illuminant D65</a> for the <a target="_blank" href="http://en.wikipedia.org/wiki/White_point">white point</a>. <p><b>Ultra-HDTV Audio as per ITU: </b> <p>22.2 multichannel sound with audio sampling frequency of 48 kHz, and 96 kHz, which can optionally be applied, with a bit depth of 16 bits, 20 bits or 24 bits per audio sample. <p><u>Audio channels:</u> <p>1&nbsp;&nbsp; FL Front left <p>2&nbsp;&nbsp; FR Front right <p>3&nbsp;&nbsp; FC Front centre <p>4&nbsp;&nbsp; LFE1 LFE-1 <p>5&nbsp;&nbsp; BL Back left <p>6&nbsp;&nbsp; BR Back right <p>7&nbsp;&nbsp; FLc Front left centre <p>8&nbsp;&nbsp; FRc Front right centre <p>9&nbsp;&nbsp; BC Back centre <p>10 LFE2 LFE-2 <p>11 SiL Side left <p>12 SiR Side right <p>13 TpFL Top front left <p>14 TpFR Top front right <p>15 TpFC Top front centre <p>16 TpC Top centre <p>17 TpBL Top back left <p>18 TpBR Top back right <p>19 TpSiL Top side left <p>20 TpSiR Top side right <p>21 TpBC Top back centre <p>22 BtFC Bottom front centre <p>23 BtFL Bottom front left <p>24 BtFR Bottom front right <p><b></b> <p>Stay tuned for part 5 of this “Living with 4K” series for the European Broadcasting Union (EBU) and the Consumer Electronics Association (CEA) naming conventions and standards for U-HDTV and U-HD.  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December 21, 2012  7:24 AM</b>
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
			<?=getComments(4951)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4951)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-4-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" type="text/javascript" charset="utf-8"></script>
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