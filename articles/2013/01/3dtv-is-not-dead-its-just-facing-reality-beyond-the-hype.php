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
		AND e.entry_id = 5014";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5014 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5014 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5014";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2013/01/3dtv-is-not-dead-its-just-facing-reality-beyond-the-hype.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5014";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3DTV is Not Dead, It\'s Just Facing Reality Beyond the Hype" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3DTV is Not Dead, It\'s Just Facing Reality Beyond the Hype" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3DTV is Not Dead, It\'s Just Facing Reality Beyond the Hype" />
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
	<title>HDTV Magazine - 3DTV is Not Dead, It's Just Facing Reality Beyond the Hype</title>
	<meta name="keywords" content="auto stereoscopic, tile format, depth map, video frame, stereoscopic dtvs, video, format, image, hdtv, frame, compatible, stereoscopic, tile, dtv, left, resolution, auto, been, data, content, map, depth, right, broadcasting, pixels" />
	<meta name="description" content="Contrary to what many at the press have been preaching since 3DTV was introduced in 2010, 3D is still alive and active in the industry, and many consumers still want to experience 3D at home.

What it should be dead is the approach of inflated advertising and improper reporting of 3DTV as a whole new television set or system that replaces what you have, although it appears that the market and the industry have finally adapted to the idea of considering 3D as what it should have been considered since day one..." />
	<meta name="title" content="3DTV is Not Dead, It's Just Facing Reality Beyond the Hype" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3DTV is Not Dead, It's Just Facing Reality Beyond the Hype" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2013/01/3dtv-is-not-dead-its-just-facing-reality-beyond-the-hype.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Contrary to what many at the press have been preaching since 3DTV was introduced in 2010, 3D is still alive and active in the industry, and many consumers still want to experience 3D at home.

What it should be dead is the approach of inflated advertising and improper reporting of 3DTV as a whole new television set or system that replaces what you have, although it appears that the market and the industry have finally adapted to the idea of considering 3D as what it should have been considered since day one..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5014', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2013/01/3dtv-is-not-dead-its-just-facing-reality-beyond-the-hype.php">3DTV is Not Dead, It's Just Facing Reality Beyond the Hype</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 27, 2013</b>
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
				<h2>How European’s Sisvel Technology compares with the new US broadcast 3D system</h2> <p>Contrary to what many at the press have been preaching since 3DTV was introduced in 2010, 3D is still alive and active in the industry, and many consumers still want to experience 3D at home.  <p>What it should be dead is the approach of inflated advertising and improper reporting of 3DTV as a whole new television set or system that replaces what you have, although it appears that the market and the industry have finally adapted to the idea of considering 3D as what it should have been considered since day one: just a “feature”, one more feature of a good HDTV, to seldom enjoy a 3D movie or sport, then, when the 3D program is over, continue with everyday’s HD viewing. For this reason it is a must to have 3D transmissions backward compatible with HDTV transmissions. <p>As expected, CES 2013 showed many demonstrations of Ultra HDTV LED and OLED (even in Ultra HDTV resolution, such as Sony’s and Panasonic’s 56” OLED Ultra HDTV prototypes), and there were also 3D demos of the same Ultra HDTV and OLED panels, not to mention the huge 3D wall at the entrance of LG’s booth. Additionally, Stream TV Networks, Hisense, Toshiba and others demo their 1080p and Ultra HDTV auto-stereoscopic (no-glasses) 3D panels as well.  <p>From the point of view of display availability and pre-recorded/theatrical content there has been 3D progress during 2012, and there has been also progress in the work toward <a href="http://www.atsc.org/cms/standards/A104-Part-2-2012.pdf">new ATSC standard</a> for US’s terrestrial broadcasting of 3D, such as the A/104 <a href="http://www.atsc.org/cms/index.php/standards/standards/305-a104-atsc-3d-tv-terrestrial-broadcasting">Service Compatible Hybrid Coding 3DTV (SCHC)</a> standard approved in December 26, 2012 by the ATSC:<a href="http://www.hdtvmagazine.com/testWindowsLiveWriter/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image002_2.gif"><img style="border-right-width: 0px; margin: 10px 20px 0px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" border="0" alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image002_thumb.gif" width="614" height="364"></a>  <p>“<i>3D-TV broadcasting service composed of two or more compressed video images, where at least one of them is the legacy 2D-TV image having the same resolution as the production resolution. </i><i>Elements of Service Compatible Hybrid Coded 3D-TV (SCHC) include Stereoscopic 3D video, audio signals, and ancillary data. Stereoscopic 3D video basically consists of a left view and a right view. In SCHC, left and right views are independently transmitted as separate video elementary streams, one of which is a base view video and the other of which is an additional video. Ancillary data can be caption information, program/channel signaling data, etc. Caption information is transmitted along with the video signal of a bit stream, while signaling data is transmitted via multiplexing….The compression format of the base view video shall conform to MPEG-2 video Main Profile @ High Level [7] while the compression format of the additional view video shall conform to AVC/H.264 Main Profile @ Level 4.0 or High Profile @ Level 4.0 [8].”</i>  <p>Is there any other format that would perform the double function of 2D and 3D without impacting image quality on either and still fit within the allotted channel space?  <p>Europe has been in those shoes recently, and the answer is yes.&nbsp; In the US we do not often see what other continents are doing regarding 3D broadcasting, but Europe and Asia showed an accelerated implementation and public acceptance of 3D compared to the US.  <p>A service compatible format <a href="http://www.broadbandtvnews.com/2010/12/08/piedmont-launches-terrestrial-3d-tv/">announced</a> in December 2010 was launched in the Italian Piedmont region and was backward compatible with 2D HDTV sets using a technique known as 3D Tile Format, which integrates two 720p frames within a single 1080p frame, one 720p image can be tuned by legacy HDTVs for 2D display, the other 720p image is split in 3 tiles when delivered and is reconstructed by a compatible decoder for 3D to be displayed in a 3DTV.  <p><img style="margin: 10px 20px 10px 0px; float: left" title="Frame Compatible Tile Format" border="0" alt="Frame Compatible Tile Format" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image010_3.gif" width="520" height="221">Over the past few years a company named Sisvel Technology (<a href="http://www.sisveltechnology.com">www.sisveltechnology.com</a>) has been actively working and promoting their “<a href="http://www.sisveltechnology.com/3D_tile_format.asp">3D Tile</a>” broadcasting system that claims to offer a one-for-all solution for broadcasting 3D in HD while delivering the image in a way that is also backward compatible with existing HDTVs.  <p>More recently the company made the 3D Tile format also compatible with near future glasses-free auto-stereoscopic 3DTVs, sharing the same transmission bandwidth of an HD channel, the format was named “3DZ Tile Format”, developed in partnership with Triaxes Vision, a Glasses Free 3D Specialist (<a href="http://www.triaxes.com">www.triaxes.com</a>).  <p>In <a href="http://www.hdtvmagazine.com/articles/2011/03/3dtv-overtheair-broadcasting-in-a-bind.php">past articles</a> I mentioned the European 3D broadcasting efforts. I met Sisvel in the past, and again now at CES 2013. We discussed the new 3DZ feature of enabling their transmission for auto-stereoscopic 3DTVs without degrading the current quality of the Tile format by using the remaining space of the 1920x1080 video frame (about 10% or 200,000+ pixels, more below). Currently the Tile format is being used in some areas of Europe.  <p>As with over-the-air broadcasting, distributing 3D over cable, satellite, and IPTV entails higher bandwidth requirements to been able to send two images (left and right eye) using the current HDTV channel space.&nbsp; At the same time the signal is made backward compatible with current 2D HDTVs to avoid having to distribute yet another channel with the HD version of the 3D content. Cable and satellite services in the US are using <a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">the top-down or side-by-side</a> half-resolution frame-compatible formats. <img style="border-right-width: 0px; margin: 20px 20px 0px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Side-by-Side 3D Format" border="0" alt="Side-by-Side 3D Format" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image004_3.gif" width="376" height="212"> <p>New compression algorithms such MPEG-4 allow for the larger 3D data stream to fit into the 6MHz allotted bandwidth space of a current HDTV over-the-air terrestrial transmission that uses MPEG-2, the current compression used for DTV over-the-air broadcasting in the US. The more efficient MPEG-4 can cut in a half the required space and promises an even better image quality.</p> <p>Sisvel Technology uses MPEG-4 compression to deliver 3D with 1280x720p resolution per eye to new 3DTV sets, it is also backward compatible with legacy HDTVs, and is now also ready for future auto-stereoscopic 3DTVs by also transmitting the extra 3D depth-map. </p><p>Auto-stereoscopic 3DTVs use the left eye image and the depth map to create several 3D viewing cones perceived as 3D by several simultaneous viewers (or by a single viewer moving in front of the screen).&nbsp; Auto-stereoscopic systems by <a href="http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-one-companys-picture-perfect-solution-how-does-it-work.php">3DFusion</a>, Dimenco, and Stream TV Networks were demo at trade shows, the last one a technology that will be implemented soon by Hisense in their auto-stereoscopic (glasses free) 2K and 4K 3DTV LCD/LED panels, also demo at CES 2013.  <p><img style="border-right-width: 0px; margin: 5px 15px 0px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Top-and -Bottom 3D Format" border="0" alt="Top-and -Bottom 3D Format" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image006_3.gif" width="373" height="219"><img style="border-right-width: 0px; margin: 5px 20px 5px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Sisvel Technology's 3D Tile Format" border="0" alt="Sisvel Technology's 3D Tile Format" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image008_3.gif" width="373" height="217">Sisvel Technology indicated that although their <a href="http://www.sisveltechnology.com/files/3Dresolutionevaluation.pdf">Tile format</a> is currently based on a 1920x1080 video frame at 50/60fps, it can be adapted to US’s 60i interlaced transmissions as well.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <p>3D content captured as 1080px2 images in 3D the Tile format would be distributed as 720px2 in 3D using a 1080p video frame.  <p>A 3DTV would use both 720p left/right images to display 3D, a legacy HDTV would use the left eye’s 720p image of that 3D transmission to display it as HD (upscaled to 1080p on a 1080p HDTV).&nbsp; An auto-stereoscopic 3DTV would use the left 720p image and the depth map included in the same video frame to display a glasses-free 3D image, all using the existing single HD channel of the broadcaster, cable, or satellite company.  <p>However, a <a href="http://www.sisveltechnology.it/media/files/3dtv/3DTV-Frame-slicing-format.pdf">firmware upgrade</a> to current MPEG-4 set-top-boxes (or a new set-top-box) would be required to decode and extract the signal needed by the particular TV.<img style="border-right-width: 0px; margin: 15px 20px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Sisvel Technology's 3DZ Tile Format Utilization" border="0" alt="Sisvel Technology's 3DZ Tile Format Utilization" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image012_3.gif" width="887" height="219">  <p>By delivering the 3D depth-map data into the video frame, rather than having the receiver set-top-box create the depth map on-the-fly from the transmitted left and right images, Sisvel Technology’s method shifts this complex function to the broadcaster’s higher processing power, making the approach relatively less costly than putting the burden in millions of receiving set-top-boxes.  <p>Below is another graph of the Tile format with the depth-map data. Note the 720p left image, and the right image split in 3 pieces, both fitted into the 1080p video frame, and the depth-map data for auto-stereoscopic 3DTVs occupying the bottom right corner of the 1080p video frame.<img style="border-right-width: 0px; margin: 10px 20px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Sisvel Technology's 3D Tile Format transforming into 3DZ Tile Format" border="0" alt="Sisvel Technology's 3D Tile Format transforming into 3DZ Tile Format" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image015_thumb.gif" width="885" height="315">  <p>Of the total 2,073,600 (1920x1080) active pixels of the 1080p video frame, the left/right images of the 3DZ Tile format occupies 1280x720p each (totaling 1,843,200 pixels both together), leaving 230,400 pixels available to store the depth-map (black and white image).  <p>Although the system can still claim HD image quality on the 2D/3D images by using the 720p format, if the original 2D/3D signal was captured as 1920x1080 its higher resolution would have to be downscaled to 1280x720 for transmission, which penalizes the spatial resolution by over 55% (2,073K pixels reduced to 921K pixels) regardless of the frame rate.  <p>I personally may not mind that loss of resolution when viewing an occasional 3D program, and considering that the number of 3D programs are expected to continue to be less numerous than HD programs, I may also<img style="border-right-width: 0px; margin: 10px 20px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Sample of images arranged within a 1080p frame with the 3DZ Tile Format" border="0" hspace="12" alt="Sample of images arranged within a 1080p frame with the 3DZ Tile Format" align="left" src="http://www.hdtvmagazine.us/articles/images/3DTVisnotdeadisjustfacingrealitybeyondth_F7AC/clip_image014_3.jpg" width="330" height="186"> accept viewing a 1080p 3D program in downscaled 720p 2D in a 1080p HDTV if the 2D version is not available, but I would not accept all 1080p HD content to be downscaled to 720p if the Tile format would have been always applied to all content at all times. <p>The 3D Tile system is sufficiently intelligent and versatile to switch to a non-Tiled 1080p HD transmission of film sources that originated from 1080p transfers, properly flagged for the decoder/display to perform the automatic switch of formats on the fly; the transmitted resolution of the image is returned to the full HD definition (1080p HD) because in this case there is no need to share the pixels of a 1080p HD frame with a second view (as it would be needed when the program transmission are in 3D). <p>Although720p is still considered HD quality and is ideal for fast content due to its fast 60p frame rate, such as ESPN sports, I still prefer the higher spatial resolution of 1080i/p for non-sports content because of its 1920 horizontal pixels (vs. just 1280 of the 720p format) to enjoy the increased pixel detail, especially noticeable in larger screens, which have been increasingly adopted among consumers and will continue to be in the near future, however, 1080i sources are known to be prone to interlaced artifacts on fast moving content.&nbsp; 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 27, 2013  7:48 AM</b>
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
			<?=getComments(5014)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5014)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2013/01/3dtv-is-not-dead-its-just-facing-reality-beyond-the-hype.php" type="text/javascript" charset="utf-8"></script>
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