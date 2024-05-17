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
		AND e.entry_id = 3243";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3243 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3243 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3243";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/09/sound-goes-live-with-new-harman-kardon-audiovideo-receivers-bringing-hometheater-setups-to-life.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3243";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sound Goes Live With New Harman Kardon&reg; Audio/Video Receivers Bringing Home-Theater Setups to Life" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sound Goes Live With New Harman Kardon&reg; Audio/Video Receivers Bringing Home-Theater Setups to Life" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sound Goes Live With New Harman Kardon&reg; Audio/Video Receivers Bringing Home-Theater Setups to Life" />
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
	<title>HDTV Magazine - Sound Goes Live With New Harman Kardon&reg; Audio/Video Receivers Bringing Home-Theater Setups to Life</title>
	<meta name="keywords" content="harman kardon, harman international, avr avr, audio video, component video, harman, avr, video, audio, dts, kardon, sound, international, inputs, high, hdmi, dolby, receivers, home, outputs, trademark, rear, music, ipod, iphone" />
	<meta name="description" content="Harman Kardon, a division of Harman International Industries, Incorporated (&lt;a target=&quot;_blank&quot; href=&quot;http://www.harman.com/&quot;&gt;www.harman.com&lt;/a&gt;), today introduced three new audio/video receivers that bring the best sound and visual experience to the home. Each receiver takes advantage of the latest sound and video technologies - such as the latest high-bit-rate formats, including Dolby® TrueHD and DTS-HD Master Audio&amp;trade; delivered via Blu-ray Disc&amp;trade; and the HD video content stored on iPod and iPhone products - and incorporates them into a beautifully designed, easy-to-use system. The AVR 1600, AVR 2600 and AVR 3600 are all capable of generating multichannel surround sound, allowing users to hear the crisp detail of a soft guitar stroke and the exciting explosions coming from different directions in the latest action movie. Since Harman Kardon's introduction of the world's first high-fidelity receiver in 1954, the company continues to utilize the latest in Harman's proprietary technology and elegant high style to produce audio systems that do not sacrifice quality or ease of use.

Harman Kardon's new line of receivers..." />
	<meta name="title" content="Sound Goes Live With New Harman Kardon&amp;reg; Audio/Video Receivers Bringing Home-Theater Setups to Life" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sound Goes Live With New Harman Kardon&amp;reg; Audio/Video Receivers Bringing Home-Theater Setups to Life" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/09/sound-goes-live-with-new-harman-kardon-audiovideo-receivers-bringing-hometheater-setups-to-life.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Harman Kardon, a division of Harman International Industries, Incorporated (&lt;a target=&quot;_blank&quot; href=&quot;http://www.harman.com/&quot;&gt;www.harman.com&lt;/a&gt;), today introduced three new audio/video receivers that bring the best sound and visual experience to the home. Each receiver takes advantage of the latest sound and video technologies - such as the latest high-bit-rate formats, including Dolby® TrueHD and DTS-HD Master Audio&amp;trade; delivered via Blu-ray Disc&amp;trade; and the HD video content stored on iPod and iPhone products - and incorporates them into a beautifully designed, easy-to-use system. The AVR 1600, AVR 2600 and AVR 3600 are all capable of generating multichannel surround sound, allowing users to hear the crisp detail of a soft guitar stroke and the exciting explosions coming from different directions in the latest action movie. Since Harman Kardon's introduction of the world's first high-fidelity receiver in 1954, the company continues to utilize the latest in Harman's proprietary technology and elegant high style to produce audio systems that do not sacrifice quality or ease of use.

Harman Kardon's new line of receivers..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3243', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/09/sound-goes-live-with-new-harman-kardon-audiovideo-receivers-bringing-hometheater-setups-to-life.php">Sound Goes Live With New Harman Kardon&reg; Audio/Video Receivers Bringing Home-Theater Setups to Life</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  9, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Sound Goes Live With New Harman Kardon&reg; Audio/Video Receivers Bringing Home-Theater Setups to Life</p>

<center><i>Over Half of U.S. Households Have HDTVs; Harman International Provides Them With HD Sound</center></i><br />
<br />

<p><strong>NORTHRIDGE, Calif.--(BUSINESS WIRE)--</strong>Harman Kardon, a division of Harman International Industries, Incorporated (<a target="_blank" href="http://www.harman.com/">www.harman.com</a>), today introduced three new audio/video receivers that bring the best sound and visual experience to the home. Each receiver takes advantage of the latest sound and video technologies - such as the latest high-bit-rate formats, including Dolby® TrueHD and DTS-HD Master Audio&trade; delivered via Blu-ray Disc&trade; and the HD video content stored on iPod and iPhone products - and incorporates them into a beautifully designed, easy-to-use system. The AVR 1600, AVR 2600 and AVR 3600 are all capable of generating multichannel surround sound, allowing users to hear the crisp detail of a soft guitar stroke and the exciting explosions coming from different directions in the latest action movie. Since Harman Kardon's introduction of the world's first high-fidelity receiver in 1954, the company continues to utilize the latest in Harman's proprietary technology and elegant high style to produce audio systems that do not sacrifice quality or ease of use.</p>

<p>Harman Kardon's new line of receivers makes its debut at a time when more than half of U.S. households own a high-definition TV, according to the Cable & Telecommunications Association for Marketing. However, solely owning a high-definition TV without the proper receiver or sound equipment means that these households can only take partial advantage of the technology being offered through their TV systems. Audio/video receivers not only help integrate the best sound possible coming out of your TV, but they also help integrate playback of other popular devices.</p>

<p>The AVR 3600 is the ultimate home entertainment receiver for entertainment fanatics looking to get the most out of all of their devices at home. It comes equipped with Harman Kardon's The Bridge III, a universal dock for the iPod and iPhone that connects with a single cable and lets you navigate music and video collections with the unit's high-resolution on-screen menus. The AVR 3600 is one of the few receivers to play back HD videos from iPod or iPhone products. Non-HD videos from the iPhone are automatically optimized by the AVR 3600 to bring the video quality to the HD standard, 1080p.</p>

<p>Given the importance of integration of multiple entertainment devices, both the AVR 3600 and AVR 2600 feature Dolby Volume to maintain consistent and clear sound volume when switching between multiple sources, such as from DVR to TV to music, one TV channel to another or even a TV program to an overly loud commercial. Harman Kardon® receivers have earned worldwide accolades for their uncompromising sound quality. From entry-level to advanced models, the multichannel receivers are held to the brand's high-performance standards. With a few simple steps, the AVRs adjust sound to exact room setups. With Harman Kardon's proprietary EzSet/EQ&trade; system, the user plugs in an included microphone and follows full-color on-screen instructions (text-only in the AVR1600).</p>

<p>"Novice home-theater installers shouldn't fret. Harman International brought sound to the first movie theaters and it also introduced many of the technologies associated with the world's best amplifiers. Simplifying the household setup has become a big part of that process," said Christopher M. Dragon, director of consumer and field marketing, Harman International. "Our team of engineers continues to deliver the ultimate home-listening experience that's easy to set up."</p>

<p>Each receiver helps music and movie libraries sound their best by providing power that ranges between 50 and 80 watts for each of the seven channels. Like all Harman Kardon products, the AVR 1600, AVR 2600 and AVR 3600 are designed to match the entire Harman home-theater lineup; they will be available in September 2009 for a suggested retail price ranging from $599.95 for the AVR 1600, $799.95 for the AVR 2600 and $1,199.95 for the AVR 3600.</p>

<p>All three Harman Kardon AVRs offer a superior audio and video experience. The entire line is capable of Logic 7®, 5.1 and 7.1 for music and cinema. Below is a breakdown of each model's features:</p>

<p>The Harman Kardon AVR 3600 (SRP $1,199.95) includes all of the AVR 1600 and AVR 2600 features plus the following:</p>

<p>    * The Bridge III, included for charging, control and audio/HD video playback from compatible iPod and iPhone products<br />
    * Additional Custom Features: Full multiroom audio system with Zone II remote included, preamplifier outputs for all channels and A-BUS/READY&trade;</p>

<p>The Harman Kardon AVR 2600 (SRP $799.95) includes all of the AVR 1600 features plus the following:</p>

<p>    * Audio DSP Section: Dolby Volume<br />
    * Video DSP Section: Faroudja® "Torino" video scaler/enhancer, upconverts to composite from component video or HDMI&trade;, upconverts to 720P, 1080i through component or up to 1080p through HDMI&trade;. Menu system rendered directly in HD up to 1080p<br />
    * HDMI Inputs/Outputs: Four inputs and one output, assignable component video ports<br />
    * Audio Inputs: iPod charging, control via AVR menus and playback of audio or HD and SD video via The Bridge III (not included), SIRIUS Satellite Radio®-ready<br />
    * Audio Outputs: Configurable back amps for 7.1 or Zone II output<br />
    * General Features: Remote In/Out, MR remote In, one switched AC outlet</p>

<p>The Harman Kardon AVR 1600 (SRP $599.95) includes the following:</p>

<p>    * Amplifier: Output power - 50 watts per channel (65 watts for the AVR 2600 and 80 watts for the AVR 3600), 20Hz-20kHz bandwidth, high current capability and all-discrete amplifier circuitry<br />
    * Audio DSP Section: Dolby Digital Plus, Dolby TrueHD, Dolby Digital, Dolby PLIIx, DTS®, DTS-HD&trade; High Resolution Audio, DTS-HD Master Audio, DTS-ES® Discrete, DTS NEO:6, DTS 96/24 and DTS 96 kHz/7.1Ch., Logic 7, 5.1 and 7.1 for cinema and music, 2 Ch. downmix<br />
    * Video DSP Section: Cross-conversion via component to HDMI (all input to HDMI for the AVR 2600 and AVR 3600)<br />
    * Audio Inputs: 6/8 channel direct, portable music player via 3.5 mini jack, five analog L/R (rear) and one analog L/R (front) and S/P-DIF inputs<br />
    * Audio Outputs: Three analog out L/R (rear), pre-outs and headphone<br />
    * Video Inputs: Three rear and one front composite video, two rear high-bandwidth component video (configurable)<br />
    * Video Outputs: One composite video output for front and rear, and one for receiver (VID1)<br />
    * HDMI Inputs/Outputs: Three inputs and one output, assignable component video ports<br />
    * Digital Inputs/Outputs: Two coax and optical rear inputs, one coax and optical front input, one coax rear output<br />
    * General Features: USB port for firmware/DSP updates and upgrades, compressed music enhancer and EzSet/EQ</p>

<p><br />
<strong>Harman International</strong></p>

<p>Harman Kardon is a unit of Harman International Industries, Incorporated (<a target="_blank" href="http://www.harman.com/">www.harman.com</a>). Harman International designs, manufactures and markets a wide range of audio and infotainment products for the automotive, consumer and professional markets. Harman International maintains a strong presence in the Americas, Europe and Asia, and employs more than 11,000 people worldwide. The Harman International family of brands includes AKG®, Audioaccess®, Becker®, BSS®, Crown®, dbx®, DigiTech®, Harman Kardon®, Infinity®, JBL®, Lexicon®, Mark Levinson®, Revel®, QNX®, Soundcraft® and Studer®. Harman International's stock is traded on the New York Stock Exchange under the symbol (NYSE:HAR).</p>

<p>Harman Kardon, AKG, Audioaccess, Becker, BSS, Crown, dbx, DigiTech, Infinity, JBL, Lexicon, Mark Levinson, Revel, QNX, Soundcraft and Studer are trademarks of Harman International Industries, Incorporated, registered in the United States and/or other countries.</p>

<p>EzSet/EQ is a trademark of Harman International Industries, Incorporated.</p>

<p>Blu-ray Disc is a trademark of the Blu-ray Disc Association.</p>

<p>Dolby is a registered trademark of Dolby Laboratories.</p>

<p>DTS and DTS Neo:6 are registered trademarks of DTS, Inc.</p>

<p>DTS-HD, DTS-HD Master Audio and DTS 96/24 are trademarks of DTS, Inc.</p>

<p>Faroudja is a registered trademark of Genesis Microchip Inc.</p>

<p>HDMI is a trademark of HDMI Licensing LLC.</p>

<p>iPod is a trademark of Apple, Inc., registered in the U.S. and other countries. iPhone is a trademark of Apple, Inc.</p>

<p>SIRIUS Satellite Radio is a registered mark of SIRIUS XM Radio Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  9, 2009  9:06 AM</b>
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
			<?=getComments(3243)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3243)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/09/sound-goes-live-with-new-harman-kardon-audiovideo-receivers-bringing-hometheater-setups-to-life.php" type="text/javascript" charset="utf-8"></script>
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