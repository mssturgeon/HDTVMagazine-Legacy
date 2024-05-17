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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 3669";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3669 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3669 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3669";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/04/sanyo-introduces-new-wide-xga-highbrightness-high-performance-projectors-with-a-variety-of-functions.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3669";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions" />
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
	<title>HDTV Magazine - SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions</title>
	<meta name="keywords" content="wide xga, picture picture, north america, ansi lumens, sanyo north, sanyo, lens, video, projectors, plc, new, wide, picture, brightness, xga, high, display, ansi, ratio, screen, projection, resolution, north, presentation, image" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/sanyo-plc-wm5500.jpg&quot; alt=&quot;SANYO PLC-WM5500&quot; height=&quot;152&quot; width=&quot;140&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;SANYO North America Corporation (SANYO), subsidiary of SANYO Electric Co., Ltd., one of the world's largest manufacturers of LCD and DLP projectors, announces the introduction of four new high performance Wide XGA format projectors to the US market. Targeting a wide range of environments, including large conference rooms, lecture halls and digital signage applications, the PLC-WM5500 (with standard zoom lens:LNS-S20), and PLC-WM5500L (with optional lens) have a very high brightness of 5,500 ANSI lumens, and the PLC-WM 4500 (with standard zoom lens:LNS-S20), and PLC-WM4500L (with optional lens) have brightness of 4,500 ANSI lumens. Each projector features two new modes that enable the simultaneous presentation of two images. All four projectors use Wide XGA panels in 16:10 widescreen aspect ratio, with 1280 x 800 resolution, allowing for the display of significantly more information than is possible with 4:3 XGA panels and 1024 x 768 resolution. The PLC-WM5500 [$5,645] and PLC-WM4500L [$4,095] have scheduled availability of July 2010, the PLC-WM4500 [$4,545] August 2010 and the PLC-WM5500L [$4,995] is expected in June 2010.

The new projectors will be..." />
	<meta name="title" content="SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/04/sanyo-introduces-new-wide-xga-highbrightness-high-performance-projectors-with-a-variety-of-functions.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/sanyo-plc-wm5500.jpg&quot; alt=&quot;SANYO PLC-WM5500&quot; height=&quot;152&quot; width=&quot;140&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;SANYO North America Corporation (SANYO), subsidiary of SANYO Electric Co., Ltd., one of the world's largest manufacturers of LCD and DLP projectors, announces the introduction of four new high performance Wide XGA format projectors to the US market. Targeting a wide range of environments, including large conference rooms, lecture halls and digital signage applications, the PLC-WM5500 (with standard zoom lens:LNS-S20), and PLC-WM5500L (with optional lens) have a very high brightness of 5,500 ANSI lumens, and the PLC-WM 4500 (with standard zoom lens:LNS-S20), and PLC-WM4500L (with optional lens) have brightness of 4,500 ANSI lumens. Each projector features two new modes that enable the simultaneous presentation of two images. All four projectors use Wide XGA panels in 16:10 widescreen aspect ratio, with 1280 x 800 resolution, allowing for the display of significantly more information than is possible with 4:3 XGA panels and 1024 x 768 resolution. The PLC-WM5500 [$5,645] and PLC-WM4500L [$4,095] have scheduled availability of July 2010, the PLC-WM4500 [$4,545] August 2010 and the PLC-WM5500L [$4,995] is expected in June 2010.

The new projectors will be..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3669', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/04/sanyo-introduces-new-wide-xga-highbrightness-high-performance-projectors-with-a-variety-of-functions.php">SANYO Introduces New Wide XGA High-Brightness High Performance Projectors with a Variety of Functions</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>April 20, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=495&category=Front Projection">Front Projection</a></b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p class="prtitle">SANYO INTRODUCES NEW WIDE XGA HIGH-BRIGHTNESS HIGH PERFORMANCE PROJECTORS WITH A VARIETY OF FUNCTIONS</p>

<center><i>High resolution Wide XGA projectors have the highest brightness in their class at up to 5,500 ANSI lumens<br /><br />Picture-in-Picture and Picture-by-Picture modes enable simultaneous presentation of two images<br /><br />Ideal for large conference rooms and lecture halls</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/sanyo-plc-wm5500.jpg" alt="SANYO PLC-WM5500" height="304" width="280" class="keyimg"><strong>CHATSWORTH, CA.— April 20, 2010</strong> – SANYO North America Corporation (SANYO), subsidiary of SANYO Electric Co., Ltd., one of the world's largest manufacturers of LCD and DLP projectors, announces the introduction of four new high performance Wide XGA format projectors to the US market. Targeting a wide range of environments, including large conference rooms, lecture halls and digital signage applications, the PLC-WM5500 (with standard zoom lens:LNS-S20), and PLC-WM5500L (with optional lens) have a very high brightness of 5,500 ANSI lumens, and the PLC-WM 4500 (with standard zoom lens:LNS-S20), and PLC-WM4500L (with optional lens) have brightness of 4,500 ANSI lumens. Each projector features two new modes that enable the simultaneous presentation of two images. All four projectors use Wide XGA panels in 16:10 widescreen aspect ratio, with 1280 x 800 resolution, allowing for the display of significantly more information than is possible with 4:3 XGA panels and 1024 x 768 resolution. The PLC-WM5500 [$5,645] and PLC-WM4500L [$4,095] have scheduled availability of July 2010, the PLC-WM4500 [$4,545] August 2010 and the PLC-WM5500L [$4,995] is expected in June 2010.</p>

<p>The new projectors will be displayed at InfoComm 2010 in Las Vegas, Nevada, from June 9 to June 11, 2010. July 2010, the PLC-WM4500 [$4,545] August 2010 and the PLC-WM5500L [$4,995] is expected in June 2010.</p>

<p>Two new operating modes enable these projectors to simultaneously display two video sources. The Picture-by-Picture Mode allows the display of two images in fixed screen locations of equal size. The Picture-in-Picture Mode displays an inset image within the full-frame image, with size and position of the smaller image specified by the operator. In either mode, for example, video material can be displayed adjacent to an accompanying slide show, or live video conferencing can be displayed alongside a video presentation viewed by all conference participants. By eliminating the cost and need to install a second projector, these features make these new projectors ideal for video conferencing and other applications where the display of two images is required.</p>

<p>"These wide format projectors set a new performance standard for brightness and resolution in their price class," states Sam Malik, Vice President and General Manager of the Presentation Technologies Group in SANYO North America's Consumer Solutions Division. "With their wide aspect ratio and new functions like our Picture-in-Picture and Picture-by-Picture modes, they provide users with not only a larger display palette, but the ability to simultaneously display in a more than one format. Further, their high brightness levels mean they can be used a wide variety of ambient lighting conditions and environments, including very large spaces."</p>

<p>Exceptional brightness of 5,500 ANSI lumens is achieved through a newly developed optical engine and new cooling technologies. With optical efficiency 40% greater than conventional projectors, SANYO's New Optical Engine uses inorganic liquid crystal panels, which are combined with new heat dissipation and cooling technologies that minimize the effects of heat on the optical components - resulting in higher reliability and durability. Maintenance costs are kept low through SANYO's Active Maintenance Filter (AMF) system, which extends the replacement interval of its filter cartridge to 10,000 hours. Rather than individual filters, each projector uses an AMF cartridge that contains a filter roll equivalent to 10 filters, advancing automatically as needed without requiring user attention.</p>

<p>Recognizing the increased usage of the HDMI interface in commercial applications, each model provides an HDMI 1.3 input in addition to DVI-D, composite video, RGB D-sub 15, VIDEO/Y-Pb/Cb- Pr/Cr (both BNC and RCA) and S-video inputs. A Power Vertical/Horizontal Lens Shift control facilitates image alignment to the screen without moving the projector, and SANYO's Lens Centering Design simplifies installation. The WM5500 and WM4500 models have remote controllable Power Zoom and Focus, and the WM5500 models also have a Corner Keystone Correction function to eliminate geometric distortion resulting from projector placement at an angle to the screen. A mechanical shutter assembly provides a convenient method of stopping light projection on the screen. When required, the projectors can display an image on floors or ceilings through a vertical 360-degree tilt angle projection capability.</p>

<p>Each of these projectors conforms with the EuP-Directive Lot 6 for reduction of energy consumption and the EU RoHS Directive for reducing the use of hazardous materials in electronic products.</p>

<p><br />
<strong>SPECIFICATIONS</strong></p>

<p>PLC-WM5500<br />
(power-driven zoom lens) and PLC-WM5500L (optional lens)<br />
Resolution: 1280 x 800 (Wide XGA)	<br />
Aspect Ratio: 16:10	<br />
Brightness: 5,500 ANSI lumens	<br />
Contrast Ratio: 800:1<br />
Uniformity: 90%	<br />
Projection Lamp:	330W	<br />
Screen Size: 40"- 400" projection distance varies based on lens<br />
Input Terminals: DVI-D, HDMI 1.3 Deep Color compatible, Video (x3): RGB D-sub 15-pin (x2), RGBHV/Y/VIDEO, Pb/Cb,Pr/Cr (BNC x3) and (RCA x3), S-video<br />
Control: Wired remote mini jack, wired LAN RJ45, control port D-sub 9<br />
Dimensions: 19.27 (W) x 6.46 (H) x 14.6 (D) inches<br />
Weight: 21.3lbs (with standard lens), 19.6lbs (without lens)	</p>

<p>PLC-WM4500<br />
(power-driven zoom lens) and PLC-WM4500L (optional lens)<br />
Resolution: 1280 x 800 (Wide XGA)<br />
Aspect Ratio: 16:10<br />
Brightness:4,500 ANSI lumens<br />
Contrast Ratio: 800:1<br />
Uniformity: 90%<br />
Projection Lamp: 275W<br />
Screen Size: 40"- 400" projection distance varies based on lens<br />
Input Terminals: DVI-D, HDMI 1.3 Deep Color compatible, Video (x3): RGB D-sub 15-pin (x2), RGBHV/Y/VIDEO, Pb/Cb,Pr/Cr (BNC x3) and (RCA x3), S-video<br />
Control: Wired remote mini jack, wired LAN RJ45, control port D-sub 9<br />
Dimensions: 19.27 (W) x 6.46 (H) x 14.6 (D) inches<br />
Weight: TBD (with standard lens), TBD (without lens)</p>

<p><br />
<strong>About SANYO</strong></p>

<p>SANYO Electric Co., Ltd. is a global company providing solutions for environment, energy and lifestyle applications. The Presentation Technology Group, part of the SANYO North America Corporation Consumer Solutions Division (SANYO North America is a subsidiary of SANYO Electric Co., Ltd.) is based in Chatsworth, California, and is a service and sales division that markets digital projectors, digital still cameras, digital media camcorders, home appliances, security video equipment, audio systems, portable and mobile electronics and HD televisions. For more information on SANYO, please visit http://us.sanyo.com.</p>

<p>Source: SANYO North America Corp.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>April 20, 2010  7:10 PM</b>
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
			<?=getComments(3669)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3669)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/04/sanyo-introduces-new-wide-xga-highbrightness-high-performance-projectors-with-a-variety-of-functions.php" type="text/javascript" charset="utf-8"></script>
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