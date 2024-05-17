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
		AND e.entry_id = 4950";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4950 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4950 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4950";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4950";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
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
	<title>HDTV Magazine - Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</title>
	<meta name="keywords" content="ultra hdtv, aspect ratio, digital cinema, horizontal resolution, vertical resolution, hdtv, ultra, resolution, dci, part, horizontal, digital, aspect, cinema, content, called, ratio, term, naming, pixels, living, series, using, actually, sony" />
	<meta name="description" content="I know, I know, another naming convention mess, and the new Ultra-HD term recently assigned by the Consumer Electronics Association (CEA) still does not cleanly address the naming issue it intended to resolve for the new high resolution panels, which were widely referred as 4K since 2011 by their manufacturers and by the press.

The short answer is..." />
	<meta name="title" content="Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I know, I know, another naming convention mess, and the new Ultra-HD term recently assigned by the Consumer Electronics Association (CEA) still does not cleanly address the naming issue it intended to resolve for the new high resolution panels, which were widely referred as 4K since 2011 by their manufacturers and by the press.

The short answer is..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4950', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php">Living with 4K (Part 3) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December 20, 2012</b>
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
</div><p>The short answer is:  <p><b>All of the above with a Sony Digital Cinema (DCI) 4K projector. </b> <p><b>New Ultra-HDTV panels are not Digital Cinema 4K resolution and would not need to be for the viewing of Ultra-HDTV future television/pre-recorded media.</b> <p>I know, I know, another naming convention mess, and the new Ultra-HD term recently assigned by the Consumer Electronics Association (CEA) still does not cleanly address the naming issue it intended to resolve for the new high resolution panels, which were widely referred as 4K since 2011 by their manufacturers and by the press. <p><b></b> <p><b>What is 4K? (Depends on who is using the term) </b> <p>The term 4K actually comes from <a target="_blank" href="http://en.wikipedia.org/wiki/Digital_Cinema_Initiatives">Digital Cinema Initiatives</a> , LLC (DCI), seven major studios that standardized and defined a digital image container with a constant horizontal resolution of 4096 pixels (4K= 4 x 1K [1024 pixels in binary]) used for commercial theaters (details in the next part 4 of this series). <p>But the 4K naming convention has also been casually used by many in the TV industry for the past couple of years to describe U-HDTV displays that actually have only 3840 pixels horizontally. <p>To be exact, the use of the 4K term may be inappropriate considering that the displays do not have even 4000 pixels horizontally nor they are actually in 4K aspect ratio (17:9), and those panels are already selling under 4K marketing and press. <p>Should the industry stop using the term 4K for those TV displays? Is this an issue of “exactitude”, or is a marketing need for a more sensational name than just 4K to attract people with big wallets? <p><b></b> <p><b>Horizontal or Vertical Resolution for U-HDTV?</b> <p>With U-HDTV this is the first time TV resolutions are loosely quoted using the rough horizontal resolution (4K or 8K) rather than the exact vertical resolution such as 480i/p, 720p, 1080i/p. <p>Is it because introducing them as 4K or 8K was considered to impress better than saying 2160 and 4320? But now realized that actually 4K is not enough to impress a buyer for a $20-25000 purchase? <p>HDTV 1080i/p was never quoted as “2K Digital Cinema” even when its 1920 viewable horizontal pixels were close in number to the 2048 of 2K. So why starting the use of “Digital Cinema K” for U-HDTV? <p><img style="float:none;" alt="" src="http://www.hdtvmagazine.us/articles/images/4056e7e0500b_E0A0/clip_image002_df7a3b50-7f8b-448c-b0c5-ccb3ced34247.gif" width="624" height="343"><br />Source: Sony <p>For simplicity and to concentrate in the specific matter of this series I will not cover how different aspect ratios of recorded 4K content within an image container with a constant horizontal resolution of 4096 (x 2160) would result in a variety of vertical resolutions for the content itself (horizontal video lines) depending upon the chosen aspect ratio of the content (1.33:1, 1.78:1, or 2.40:1). <p>This is similar to a 2.35:1 Cinemascope Flat movie letterboxed within a 16:9 1920x1080p HD space; it has a lower vertical resolution for the actual CinemaScope image (using 800+ video lines out of the available 1080) and is sandwiched between top/bottom black bars that use the rest of the 1080 space. <p>Similarly, a flat 1.85:1 movie will use some video lines for the movie image and some video lines for black bars within the 1920x1080 space. Content recorded and projected with anamorphic lens stretches vertically the content to maximize vertical resolution, and that application is beyond the scope of this subject. <p>Again, I will concentrate in the typical 2160 vertical resolution implemented by most consumer panels and projectors for 4K and U-HDTV. <p><b></b> <p><b>Using the 4K DCI Term Correctly</b> <p>Over the past few years Sony and other manufacturers of commercial theater projectors such as NEC, Christie, etc. correctly use the 4K term because they are capable to display true 4K images with their 4K projector chips, implementing SXRD (Sony’s proprietary naming for LCoS-Liquid Crystal on Silicone technology) or DLP (Digital Light Processing from Texas Instruments). <p>For consumers Sony introduced this year the first 4K VPL-VW1000ES home cinema projector ($25,000 MSRP) with 4096 horizontal pixels (x 2160), a true DCI 4K chip with 17:9 aspect ratio. <p>No 4K content is yet available as pre-recorded media for consumers to buy. I am upscaling 1080p content to 4K with the projector and applying its Reality Creation feature that enhances the upscaled 8-million-pixel image with an appearance and detail that has been nothing less than stunning. <p><img style="float:none;" alt="" src="http://www.hdtvmagazine.us/articles/images/4056e7e0500b_E0A0/clip_image004_f2d8c8f4-5e41-4920-8ae1-148bddeeb25e.gif" width="624" height="347"><br />Source: Sony <p><b></b> <p><b>Using your U-HDTV panel for television</b> <p>New higher resolution LCD panels claim to have 4K capabilities while only having 3840 (x2160) horizontal pixels, should they be called 4K? Would 4K be an attractive name for the marketing of these expensive panels? Would consumers need a true 4K 17:9 aspect ratio panel with 4096x2160 of resolution for the purpose of viewing 16:9 television/pre-recorded media with 3840x2160 resolution? <p>As a consumer you may rather have a U-HDTV panel with a resolution and aspect ratio that is in tune with current HDTV considering that you would be displaying upconverted legacy HDTV content and Blu-ray pre-recorded media with 16:9 aspect ratio for several years, even after U-HDTV broadcasting and U-HDTV Blu-ray format are implemented (details in part 2 of this “Living with 4K” series), which most probably be in 16:9 aspect ratio as well (not in the 4096x2160 of 4K Cinema DCI with 17:9 aspect ratio).  <p><img style="float:none;" alt="" src="http://www.hdtvmagazine.us/articles/images/4056e7e0500b_E0A0/clip_image006_90a6653e-dc59-4067-a905-9029ee18da85.gif" width="624" height="351"><br />Source: <i>Sony</i><i></i></p> <p>So, does it make sense that Ultra-HDTV continues using a 4K term inherited from Digital Cinema, even as rough horizontal resolution, when they actually have a different aspect ratio and different horizontal resolution? <p><b></b> <p><b>How Ultra-HDTV is named now?</b> <p>In the following parts of this “Living with 4K” series I will detail the various naming conventions and standards regarding Ultra-HDTV and 4K DCI, but to give you the heads up, here are some highlights and a summary table:  <p>1) The Ultra-HDTV standard <a target="_blank" href="http://www.youtube.com/watch?v=LAVTX4S7vCw&amp;feature=youtu.be">defined by the EBU</a> (European Broadcast Union) has 2 levels of high resolution: level 1 with 3840x2160 pixels (called 4K), and level 2 with 7680x4320 pixels (called 8K or Super Hi-Vision - SHV). <p>2) The International Telecommunication Union (<a target="_blank" href="http://www.itu.int/net/pressoffice/press_releases/2012/31.aspx">ITU</a>) and the <a target="_blank" href="https://www.smpte.org/">SMPTE</a> (Society of Motion Picture and Television Engineers) also refer to the same two levels of Ultra-HDTV, <a target="_blank" href="http://www.itu.int/net/pressoffice/press_releases/2012/31.aspx#.UK_kmtuF9Y4">defining them</a> as lower level U-HD1 and upper level U-HD2, and also making reference to 4K and 8K resolutions.  <p>3) The Digital Cinema Initiatives (<a target="_blank" href="http://en.wikipedia.org/wiki/Digital_cinema">DCI</a>) defines 2K as 2048x1080 and 4K as 4096x2160, in addition to defining several other specifications for frame rates, bit depth, color space, etc., “for the purpose of digital commercial cinema”. <p>4) And just recently the Consumers Electronics Association (CEA) <a target="_blank" href="http://www.twice.com/articletype/news/ultra-hd-now-4k&rsquo;s-official-ce-industry-name/103664">named the 3840x2160</a> pixel displays as: U-HD (Ultra-HD) rather than 4K. For over a year they have been called 4K displays, and they are still being advertised and covered by the press that way. <p>Note that the CEA did not specifically use the established U-HD1 term (level 1/lower of Ultra-HDTV), but rather used “Ultra-HD” resembling the broader “Ultra-HDTV” standard that actually engulfs two levels, the “4K and “8K” levels. <h2>Summary of 4K/U-HD/U-HDTV naming conventions (and HDTV)</h2><table class="simple"><tbody>
<tr><td><b>Defining Organization</b></td><td><b>Image format (HxV)</b></td><td><b>Viewable Pixel count per video frame</b></td><td><b>Scanning</b></td><td><b>Naming Convention Assigned to it</b></td><td><b>Relevant to "K" (1K=1024) resolution the format is Actually</b></td></tr>
<tr><td rowspan="2">ATSC Table 3</td><td>1280 × 720</td><td>921,600</td><td>Progressive</td><td rowspan="2">HDTV</td><td>1.25K [1280/1024]</td></tr>
<tr><td>1920 × 1080</td><td>2,073,600</td><td>Interlaced/ Progressive</td><td>1.87K [1920/1024]</td></tr>
<tr><td rowspan="2">Digital Cinema Initiatives (DCI)</td><td>2048 × 1080</td><td>2,211,840</td><td>Progressive</td><td>Correctly named 2K</td><td><b>True 2K</b></td></tr>
<tr><td>4096 × 2160</td><td>8,847,360</td><td>Progressive</td><td>Correctly named 4K</td><td><b>True 4K</b></td></tr>
<tr><td rowspan="2">European Broadcasting Union (EBU)</td><td>3840 × 2160</td><td>8,294,400</td><td>Progressive</td><td>UHDTV Lower Layer UHD-1, Also called 4K</td><td>3.75K [3840/1024]</td></tr>
<tr><td>7680 × 4320</td><td>33,177,600</td><td>Progressive</td><td>UHDTV Upper Layer UHD-2 - Also called 8K or Super Hi-Vision (SHV)</td><td>7.5K  [7680/1024]</td></tr>
<tr><td rowspan="2">International Communication Union (ITU)</td><td>3840 × 2160</td><td>8,294,400</td><td>Progressive</td><td>UHDTV level 1, Also called 4K</td><td>3.75K [3840/1024]</td></tr>
<tr><td>7680 × 4320</td><td>33,177,600</td><td>Progressive</td><td>UHDTV level 2, Also called 8K</td><td>7.5K  [7680/1024]</td></tr>
<tr><td>Consumer Electronics Association (CEA)</td><td>3840 x 2160</td><td>8,294,400</td><td>Progressive</td><td>Newly named U(ltra) HD, Initially called 4K</td><td>3.75K [3840/1024]</td></tr>
</tbody></table> <p>The following parts 4 and 5 of this “Living with 4K” series will provide details of the naming conventions as defined by the various organizations (ITU, EBU, CEA, and DCI). Part 5 wraps the subject of naming conventions and provides a historic perspective of similar naming decisions taken by the CEA with DTV.  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December 20, 2012  7:07 AM</b>
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
			<?=getComments(4950)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4950)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" type="text/javascript" charset="utf-8"></script>
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