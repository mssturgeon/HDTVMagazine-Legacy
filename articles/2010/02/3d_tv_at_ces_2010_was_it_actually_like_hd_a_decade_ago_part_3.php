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
		AND e.entry_id = 3563";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3563 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3563 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3563";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-3.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3563";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)" />
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
	<title>HDTV Magazine - 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)</title>
	<meta name="keywords" content="blu ray, frame compatible, set top, top box, frame rate, frame, dtv, image, hdmi, images, resolution, fps, top, signal, full, blu, ray, video, compatible, display, set, bandwidth, eye, format, box" />
	<meta name="description" content="As mentioned in part 2: In addition to the DTV type of conversions, such as digital compression, resolution and frame rate, 3D will subject the signal to conversions of structures/formats to match source devices with display devices capabilities. 

From the world of digital audio/video, although many mistakenly generalize that bits are bits, we know that original signal quality suffers with conversions, which is what we will discuss in this part 3 of this series. 

As mentioned earlier, 3D for the home is been implemented either with..." />
	<meta name="title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-3.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As mentioned in part 2: In addition to the DTV type of conversions, such as digital compression, resolution and frame rate, 3D will subject the signal to conversions of structures/formats to match source devices with display devices capabilities. 

From the world of digital audio/video, although many mistakenly generalize that bits are bits, we know that original signal quality suffers with conversions, which is what we will discuss in this part 3 of this series. 

As mentioned earlier, 3D for the home is been implemented either with..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3563', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-3.php">3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 3)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 24, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<div class="editorial">The following article is the latest in the "3D TV at CES 2010 – Was it Actually Like HD a Decade Ago?" series. Other articles in this series are as follows:<br> <ul> <li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_1.php" target="_blank">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 1)</a></li><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_2.php" target="_blank">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 2)</a></li><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_4.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 4)</a></li></ul></div> <p>As mentioned in <a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_2.php" target="_blank">part 2</a>: In addition to the DTV type of conversions, such as digital compression, resolution and frame rate, 3D will subject the signal to conversions of structures/formats to match source devices with display devices capabilities.  <p>From the world of digital audio/video, although many mistakenly generalize that bits are bits, we know that original signal quality suffers with conversions, which is what we will discuss in this part 3 of this series.  <p>As mentioned earlier, 3D for the home is been implemented either with passive or active (shutter) glasses; as I mentioned in previous parts of this series, the old anaglyph color 3D glasses and the no-glasses auto-stereoscopic methods will not be covered.  <p>I provide below a sample scenario of a signal path of a 3D frame-compatible format distributed by satellite and cable service providers, up to the point where a 3DTV displays it. <b></b> <p><b>The Original 3D Image Pair</b>  <p>The original 3D image pair is made of two full frames recorded by two 1080p cameras, with 1080 lines x 1920 pixels of horizontal resolution for each image. The capture and editing process is also performed over the full resolution of the images.  <p>Due to the bandwidth limitations of satellite and cable services (and terrestrial broadcast), the 3D image-pair is down-converted to a frame-compatible format (such as the top/bottom format for example) to be able to use the current HD distribution infrastructure, as follows.  <p><b>The Distribution Format/Structure</b>  <p>The structure of the top/bottom 3D frame-compatible format (also referred to as over/under) is made of two images that share the same video frame. In order to do that, the resolution of the original images is reduced to half of the video lines on the vertical resolution axis (540 lines x 1920 pixels image for the left eye + 540x1920 image for the right eye = 1080x1920 of the typical video frame for HD).  <p>In other words, half of the quality of the original pair of 3D images is discarded even before is compressed (with MPEG-2, MPEG-4, etc) for distribution by satellite and cable (and planned by terrestrial broadcast).  <p>If another 3D frame-compatible structure is used by the service provider, such as the side-by-side, the video frame is still made of two half-resolution images, but split over the horizontal resolution axis, having 1080 lines x 960 pixels each, both totaling 1080x1920.  <p><b></b> <p><b>Set-top-Boxes </b> <p>A 3D enabled set-top-box would be able to tune the 3D frame-compatible signal as distributed by satellite and cable (and terrestrial broadcast), uncompress the signal (MPEG-2, MPEG-4, etc., not the 3D structure), perform the necessary video processing, and send it uncompressed to its HDMI output.  <p>The 3D signal may be maintained at the same frame-compatible structure, or converted to another 3D structure depending on the functionality of the box. This is similar to what a typical HD set-top-box does today with 720P or 1080i output settings to better match the resolution of the display device, but in the 3D case is about the structure of the image.  <p>The frame rate of the 3D structure/format could be 2x1080p24fps for film content or 2x1080i60 fps (fields) for video content. Most probably, the frame-compatible 3D signal would be output in the same format as the input within the set-top-box to let the 3DTV up-convert the signal to full frame dual 3D images to display them in sync with active shutter glasses.  <p>Upconverting the dual half-images to dual full-images of 2x1080p60 within the set-top-box would double the resolution and the connection to the 3DTV would require the higher bandwidth of HDMI version 1.3/1.4 (10.2 Gbps) to transport the larger signal, in addition to the upgrade to handle the full frame 3D protocols (covered in earlier parts).  <p>Requiring such high bandwidth between the set-top-box and the 3DTV would not provide a benefit unless the set-top-box does a better job on the up-conversion than the 3DTV could, a similar situation than resolution conversions of HD set-top-boxes and HDTVs today.  <p><b></b> <p><b>The 3DTV</b>  <p>Most 3DTV panels introduced at CES 2010 are designed to display alternate full frames at higher frame rates synchronized with active shutter glasses, displaying one full frame per eye at the given time, such as the Panasonic Plasmas, Sony’s LCDs, etc. The 3D Blu-ray format for pre-recorded movies follows that standard as well.  <p>A 3DTV should detect various 3D formats at its HDMI input, such as the frame-compatible pair of images of this example. The 3DTV will create two full 1080p frames of 2 million pixels each, one for each eye, by interpolating another 1 million pixels on each image, in other words, it creates 540x1920 pixels not present in the original image.  <p>The TV in sync with the active shutter glasses alternately displays the resulting image-pair of 2 million pixels each, one eye at the time.  <p>In the case of a 3D Blu-ray image-pair of full 1080 frames, the player sends the images to the 3DTV as they are, which displays them without conversions or pixel interpolation (covered further below).  <p>Almost the opposite scenario would happen if a 3D Blu-ray player sends a full 3D pair of 1080p images to a display device that is designed to display only frame compatible half-resolution 3D images for viewing with passive polarized glasses (such as the JVC 3D LCD). In this case, the original resolution of the 3D Blu-ray images would be cut in a half for each eye due to the TVs limitations.  <p>I must mention that although the industry is still working in standards, formats, and structures, some of these formats/structures were already approved as mandatory and optional depending on the device (set-top-box, 3D Blu-ray player for pre-recorded content, or display device).  <p>Now here is the quiz, how many signal conversions took place throughout the image chain from camera to display?  <p><b>3D Blu-ray </b> <p><u>HDMI Version for 3D?</u>  <p>New 3D players and 3D TVs are expected to be HDMI 1.4 compliant, however, even the bandwidth of HDMI version 1.3 (10.2 Gbps) would be sufficient for transporting 3D at 2x1080p60, provided the connected equipment has the proper 3D language/protocols to interoperate properly.&nbsp; <p>All pre-1.3 HDMI spec versions handle 4.95 Gbps of bandwidth, which should also be sufficient to transport (2x) 1080p24fps for 3D film sources, or (2x) 1080i60fps for interlaced 3D video sources.  <p>However, a 3D Blu-ray player that offers functionality to raise the frame rate and outputs (2x) 1080p60fps (progressive frames), would require the 10.2 Gbps bandwidth of the HDMI 1.3 or 1.4 versions to transport that higher resolution signal, in addition to the language/protocol as mentioned above.<p><u>Frame Rate - Deinterlacing (Assumptions)</u>  <p>It is assumed that all 3DTVs will accept 1080p24fps per eye from film sources.  <p>However, to accommodate for 3D displays that cannot accept 24fps frame rate (but may accept 60i/p fields/frames per second), a 3D Blu-ray player may offer the option of converting the 24fps to 60i, which involves a 2:3 pull-down conversion (for the 24 frames to become 30, equal to 60 interlaced fields).  <p>The player may offer also a frame rate upconversion to 60fps progressive frames, but that would require the full bandwidth of 10.2 Gbps of HDMI 1.3/4.&nbsp; However, there is no need to waste/require more bandwidth in the equipment/chips (not the HDMI spec, which has it), if the display is capable to accept 24fps per eye.  <p>A similar feature is usually offered by many 2D Blu-ray players to deinterlace 1080i-to-1080p60, for which the 4.95 Gbps of pre-v1.3 HDMI bandwidth is sufficient because 2D is made of one single image (1080p60 fps), not two as 3D.  <p><u>The Players (Assumptions)</u>  <p>We will have to see the actual functionality of the new 3D Blu-ray players from Sony and Panasonic when they become available in spring 2010.&nbsp; <p>One unique feature of the Panasonic player is that it has two HDMI outputs, which are particularly useful to connect one output to a non-3D compliant A/V receiver for audio, and the other HDMI output to the 3DTV for 3D video.  <p>Sony's does not mention 3D parameters on the video menu settings in the user’s manual published on their <a href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&amp;storeId=10151&amp;langId=-1&amp;productId=8198552921666077660">website</a>, but indicates that a firmware upgrade for 3D will be available in July 2010.  <p>Panasonic did not respond yet about the specific technical capabilities of their future player.  <p>The next part 4 will provide a view of what consumers could do when considering upgrading to 3DTV. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 24, 2010  9:11 AM</b>
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
			<?=getComments(3563)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3563)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-3.php" type="text/javascript" charset="utf-8"></script>
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