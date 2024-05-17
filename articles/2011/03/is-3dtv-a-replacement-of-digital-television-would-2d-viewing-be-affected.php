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
		AND e.entry_id = 4230";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4230 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4230 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4230";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/03/is-3dtv-a-replacement-of-digital-television-would-2d-viewing-be-affected.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4230";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?" />
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
	<title>HDTV Magazine - Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?</title>
	<meta name="keywords" content="video processing, auto stereoscopic, home theater, high quality, dual purpose, viewing, dtv, quality, image, screen, should, feature, may, video, images, processing, display, using, glasses, hdtv, most, eye, stretch, content, theater" />
	<meta name="description" content="I would like to start with a statement that I customarily make about 3DTV.  3DTV should not be regarded as a new TV system intended to replace the current digital H/DTV system, but should rather be considered as just one more advanced feature to occasionally view 3D content on an HDTV.

The amount of 3D content is expected to improve with time.  The effects of prolonged 3D viewing may soon be confirmed by appropriate research.  The existing over-the-air, cable, satellite, and IPTV transmission infrastructure, bandwidth, and equipment are being adapted to distribute 3DTV, although with certain limitations compared to the 3D quality of Blu-ray, such as half resolution per eye using frame compatible 3D formats (such as side-by-side or top-bottom 3D structures for the left/right images to share the same video frame), relatively high digital compression, lower transfer speed rate, and reduced audio quality using lossy codecs rather than the high quality lossless codecs of Blu-ray (such as DTS Master Audio).

The TV models featuring 3D capabilities released during 2010 are..." />
	<meta name="title" content="Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/03/is-3dtv-a-replacement-of-digital-television-would-2d-viewing-be-affected.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I would like to start with a statement that I customarily make about 3DTV.  3DTV should not be regarded as a new TV system intended to replace the current digital H/DTV system, but should rather be considered as just one more advanced feature to occasionally view 3D content on an HDTV.

The amount of 3D content is expected to improve with time.  The effects of prolonged 3D viewing may soon be confirmed by appropriate research.  The existing over-the-air, cable, satellite, and IPTV transmission infrastructure, bandwidth, and equipment are being adapted to distribute 3DTV, although with certain limitations compared to the 3D quality of Blu-ray, such as half resolution per eye using frame compatible 3D formats (such as side-by-side or top-bottom 3D structures for the left/right images to share the same video frame), relatively high digital compression, lower transfer speed rate, and reduced audio quality using lossy codecs rather than the high quality lossless codecs of Blu-ray (such as DTS Master Audio).

The TV models featuring 3D capabilities released during 2010 are..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4230', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/03/is-3dtv-a-replacement-of-digital-television-would-2d-viewing-be-affected.php">Is 3DTV a Replacement of Digital Television? Would 2D Viewing be affected?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>March 11, 2011</b>
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
				<p>I would like to start with a statement that I customarily make about 3DTV. 3DTV should not be regarded as a new TV system intended to replace the current digital H/DTV system, but should rather be considered as just one more advanced feature to occasionally view 3D content on an HDTV. <p><span class="caption right" style="width:219px"><img alt="Samsung Largest 3D LCD Panel at CES 2011" src="http://www.hdtvmagazine.us/articles/images/238aaa0914c4_14CB8/clip_image002_297b7d38-3da2-4f46-b4f9-413342f6ea9f.jpg" width="217" height="234"><br />Samsung Largest 3D LCD Panel at CES 2011</span>The amount of 3D content is expected to improve with time. The effects of prolonged 3D viewing may soon be confirmed by appropriate research. The existing over-the-air, cable, satellite, and IPTV transmission infrastructure, bandwidth, and equipment <a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php">are being adapted</a> to distribute 3DTV, although with certain limitations compared to the 3D quality of Blu-ray, such as half resolution per eye using <a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">frame compatible 3D formats</a> (such as side-by-side or top-bottom 3D structures for the left/right images to share the same video frame), relatively high digital compression, lower transfer speed rate, and reduced audio quality using lossy codecs rather than the high quality lossless codecs of Blu-ray (such as DTS Master Audio). <p>The TV models featuring 3D capabilities released during 2010 are also high quality HDTV models of the upper lines, capable of producing excellent HDTV images. However, and this is the main message of this article, the quality of the 2D image for the viewing of traditional HDTV should not be compromised by having the extra feature of displaying 3D, and you should verify that. <h2>3DTVs may affect 2D Viewing </h2> <p>Any HDTV set that adds special components to show 3D images, such as extra LCD/film/glass/lenticular layers, additional image processing on passive technologies to polarize left/right images, or image processing on <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">auto-stereoscopic technologies</a> to produce multiple views for a group of viewers, etc., should be carefully designed for the dual functionality without affecting the quality of the 2D image, which is what most people would often view on a daily basis. <p>On the other hand, if an auto-stereoscopic 3DTV is primarily designed to display 3D for digital signage purposes, not for a home display for example, its cost/benefit should be evaluated with the perspective of that service for the particular application, which is rather unusual for most consumers, generally looking for a good quality 3DTV within the price range of HDTVs. <p>Generally the 3D feature of active-shutter-glasses 3DTVs does not interfere with the screen materials or the video processing when performing as a 2D display device. When working as a 3D display the active-shutter 3D glasses are switching the corresponding left/right images alternatively to both eyes in sync with the timing of the display. <p><span class="caption left" style="width:298px"><img alt="Samsung RX 3D glasses Solution"  src="http://www.hdtvmagazine.us/articles/images/238aaa0914c4_14CB8/clip_image005_0cb1efa5-4a85-4aab-a55b-c86a8aeb24c6.jpg" width="296" height="223"><br />Samsung RX 3D glasses Solution</span>A <a href="http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php">passive 3DTV LCD</a> viewed with polarized glasses (or even an auto-stereoscopic 3DTV) can implement the 3D feature with additional video processing and an extra layer of a pattern retarder film/glass/additional LCD panel/lenticular screen to be capable of directing images to either eye using low cost polarized glasses (or no-glasses in the auto-stereoscopic technology).  <p><span class="caption right" style="width:276px"><img alt="LG Display - Defending Passive 3D LCD at CES 2011. But not a word was said about half-resolution"  src="http://www.hdtvmagazine.us/articles/images/238aaa0914c4_14CB8/clip_image007_fa8f5194-980d-43f3-a775-bfa545990b1d.jpg" width="274" height="225"><br />LG Display - Defending Passive 3D LCD at CES 2011<br />But not a word was said about half-resolution</span>One side effect of that design is that the perceived resolution from a passive display is halved per eye when displaying 3D, or even lower resolutions per eye on many auto-stereoscopic panels to deliver images to multiple viewers sharing the fixed total resolution of the panel. <p>Another side effect is that, by having an extra screen layer/material and the added video processing used for 3D that may not be fully defeated out of the way for 2D viewing, the set could potentially compromise the <a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-2.php">quality of HDTV viewing</a>, and that should be evaluated before buying a 3DTV set. Most consumers perform subjective viewing at the retail store but usually that is not enough to determine if the quality of the displayed 2D image has been compromised. Product reviews by reputable publications could be valuable to the consumer for that purpose.  <h2>Additional Factors concerning Projection Systems</h2> <p>The 2D performance issue mentioned above is in addition to the factors associated with 3DTV technologies mentioned in my other articles, but having 3D functionality could also interfere with other factors in home theater projection systems. <p>For example, 3D projectors using passive technology typically require 3D silver screens to retain the polarity of the projected light and reflect it back to the viewer wearing polarized glasses to let the video lines that are intended for one eye be seen only by that eye.  <p>Due to the material used on 3D silver screens they are generally not recommended to be used also for quality 2D viewing, so a 3D home theater installation may end up requiring two screens, one for 2D HDTV, and another for 3D (a silver screen), the one in front should be retractable, certainly increasing the cost for having the 3D feature in a projection system that normally needs just one screen that could be fixed to the wall (relatively lower price). <p>One screen manufacturer (Stewart) introduced a dual purpose screen (<a href="http://www.stewartfilmscreen.com/residential/materials/3d/silver5D_residential.html">2D and 3D</a>), but its performance for frequent projection of quality 2D may be unacceptable for your application of sporadic 3D viewing using the same screen. However, if the purpose of the home-theater is to predominantly view 3D content and infrequently view 2D, which would be an unusual investment due to the limited 3D content available and its growth rate, a dual purpose screen that is a good 3D performer may be justified.  <p>Regarding other home theater projection features, over the past few years, manufacturers of HD home theater projectors implemented video processing features to vertically stretch an image for <a href="http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php">CinemaScope viewing</a> and using anamorphic lenses optically stretch the image horizontally to restore the geometry of the wider CinemaScope image, the result: a very large and wide image without any black bars just like the local theater. Since 2006 this feature has been increasingly implemented by all major manufacturers in most high quality projectors. <p>Ironically, the more modern 3D versions of most of those projectors are now incapable of performing the CinemaScope stretch when viewing 3D content. If the viewer has already installed a wide 2.35:1 CinemaScope screen and an anamorphic lenses system their full potential could only be used for 2D viewing but not when viewing 3D, which is what 3D may benefit most from, a wider image. <p>JVC claimed that in order to perform both video processing features simultaneously (3D, and the vertical stretch for CinemaScope) their projector would have been priced considerably higher and the company preferred not to offer the feature. The alternative: for the consumer to also buy an external scaler, such as Lumagen or others for several thousand dollars, to perform the image vertical stretch outside the projector, or purchase a 3D Blu-ray player with the vertical stretch feature, which is rather unusual, but less expensive than a scaler used for just that function. <p>In summary, although many cost/performance issues and factors of 3DTV are mainly related to 3D viewing, and considering that most viewing will be 2D until abundant 3D content becomes available, consumers should make sure that the quality of 2D images using a 3DTV are not compromised by added screen materials and video processing that may not be fully defeated for 2D viewing. Additionally, in order to realize the full potential, and to evaluate the cost benefit of the dual purpose system, all of the hidden costs and factors of concern must be timely disclosed.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>March 11, 2011  7:44 AM</b>
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
			<?=getComments(4230)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4230)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/03/is-3dtv-a-replacement-of-digital-television-would-2d-viewing-be-affected.php" type="text/javascript" charset="utf-8"></script>
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