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
		AND e.entry_id = 1690";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1690 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1690 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1690";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-on-the-test-bench.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1690";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Mitsubishi LT-46148 LCD HDTV - On the Test Bench" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Mitsubishi LT-46148 LCD HDTV - On the Test Bench" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Mitsubishi LT-46148 LCD HDTV - On the Test Bench" />
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
	<title>HDTV Magazine - Mitsubishi LT-46148 LCD HDTV - On the Test Bench</title>
	<meta name="keywords" content="color space, ire ire, light output, color decoding, color temp, color, response, video, ire, display, black, space, using, light, output, pass, test, back, set, fail, any, hdmi, while, pixel, error" />
	<meta name="description" content="This portion of the review details how the Mitsubishi LT-46148 LCD HDTV performed on the test bench. Please read the Mitsubishi LT-46148 LCD HDTV Review Essentials, if you have not already. 

RGB 0-255 can't pass below black because black is 0. Unfortunately the same goes for YPbPr 16-235 yet with that video signal black is 16 so it could if designed to do so. Bottom line, no below black signals will pass. While not a severe error, it is one that Videophiles should take note of since some small portion of consumer video content will exceed 16 or 235 by a few notches and an ISF calibration would setup a display to account for that anomaly. This one..." />
	<meta name="title" content="Mitsubishi LT-46148 LCD HDTV - On the Test Bench" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Mitsubishi LT-46148 LCD HDTV - On the Test Bench" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-on-the-test-bench.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This portion of the review details how the Mitsubishi LT-46148 LCD HDTV performed on the test bench. Please read the Mitsubishi LT-46148 LCD HDTV Review Essentials, if you have not already. 

RGB 0-255 can't pass below black because black is 0. Unfortunately the same goes for YPbPr 16-235 yet with that video signal black is 16 so it could if designed to do so. Bottom line, no below black signals will pass. While not a severe error, it is one that Videophiles should take note of since some small portion of consumer video content will exceed 16 or 235 by a few notches and an ISF calibration would setup a display to account for that anomaly. This one..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1690', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-on-the-test-bench.php">Mitsubishi LT-46148 LCD HDTV - On the Test Bench</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>March 19, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=277&category=HDTV Displays">HDTV Displays</a></b>
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
				<p class="editorial">This portion of the review details how the Mitsubishi LT-46148 LCD HDTV performed on the test bench. Please read the <a href="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi_lt46148_lcd_hdtv_review_essentials.php" target="_blank">Mitsubishi LT-46148 LCD HDTV Review Essentials</a>, if you have not already.</p>

<h2>Below Black Video</h2>

<p>RGB 0-255 can't pass below black because black is 0. Unfortunately the same goes for YPbPr 16-235 yet with that video signal black is 16 so it could if designed to do so. Bottom line, no below black signals will pass. While not a severe error, it is one that Videophiles should take note of since some small portion of consumer video content will exceed 16 or 235 by a few notches and an ISF calibration would setup a display to account for that anomaly. This one can't for black.</p>

<h2>1:1 Pixel Mapping (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=82">Definition</a>)</h2>

<p>The display has an aspect called Full Native for any 1920x1080 video signal, 1080i and 1080p 24, 30 or 60 frame and passed luminance correctly. Using the SMPTE RP133 pattern from DVE the 1920 and 960 boxes clearly showed a blue shift along with the 1080 box showing the same although less in level. This is HDMI YPbPr so the display has a component processing chroma error even though it passed the luminance correctly. Using a similar pattern (horizontal only) from the Accupel generator using DVI RGB there was no chroma error. Using that same pattern output as component YPbPr 1080i there was no chroma error but the 1920 response was lower in contrast level.</p>

<h2>Over Scan</h2>

<p>Using the Full Native aspect ratio there was 0 over scan. If the digital input you are using is labeled as PC this will activate a different set of aspect ratios, one being 1:1 pixel mapped centered output for scan rates below 1920x1080 with 0 overscan. Per the owners manual it supports 9 different pixel matrices. In testing with a Sencore VP403 set for PC/Mac 640x480 and 1024x768 was not pixel mapped. I also tried the HDTV section at 480p and was greeted with a correct 16:9 aspect ratio. All three of these displayed quite well without any ringing in any of the bursts although they were not pixel mapped. Only the HDTV 720p, 720x1280, pixel mapped as a centered output.</p>

<h2>Color Resolution</h2>

<p>Using HDMI via the Accupel Burst pattern encoded as 4:4:4 color at 720p with the display input label set for PC provided the correct response. Setting the input label for another input type takes it out of 4:4:4 color mode. Using the same pattern at 1080I the response was OK. As resolution increased color saturation decreased. 120 lines was well saturated, 240 showed a drop, 480 was greatly reduced, 960 was barely noticed and of course 1920 was missing in action as it should be rather than creating a chroma error since HDTV is limited to 960 or half of luminance.</p>

<p>Using component via the Accupel Burst pattern encoded as 4:4:4 color at 1080i the response varied. The 480 line response was actually more saturated than either the 240 or 960 line response. The 960 line response faired better in saturation than HDMI. The 1920 line response was completely saturated, an error, but video YPbPr is limited to 960 lines so this should not be a problem.</p>

<p>Either response is a case of choose your poison since either input type comes with errors. My preference would be HDMI.</p>

<h2>DVI RGB versus HDMI YPbPr Video Levels</h2>

<p>Typically any DVI input will be setup for RGB at the source and this display is designed specifically for DVI PC video levels, 0-255 when ever any digital RGB video signal is used. Conversely an HDMI source is typically digital YPbPr and uses consumer video levels 16-235. According to the manual you must name the HDMI input you are using for your computer, PC, &quot;It is important to use the name PC so that the TV can process the video signal correctly&quot;. This will change the formatting or aspect ratio options along with color resolution and response but it will not adjust the video levels back and forth from consumer video 16-235 to PC video 0-255. What this means is any digital RGB source (DVI) you want to use has to be set up for PC levels 0-255 and any digital YPbPr source (HDMI) has to be setup for 16-235 to get the right video level results on screen.</p>

<p>Component YPbPr yielded the correct response.</p>

<h2>Calibration, Test Results and Factory Settings </h2>

<p>The factory service menu is extremely limited from the controls offered to how they affect the image. The bad news is this display does not favor ISF display calibration one bit. The display offers three factory presets that change overall response, BRILLIANT, BRIGHT and NATURAL along with two color temperature settings of HIGH and LOW. The good news is selecting NATURAL and LOW yields some very good results. While not accurate these settings provide a decent response envelope as you will see. BRILLIANT, as with all displays, represents the out of box <em>sales mode</em> settings from the manufacturer to compete against others and induce your purchase. This is the setting you will likely encounter at the retailer when shopping.</p>

<p>BRILLIANT pumps up the gamma and expands the color space to its maximum. BRIGHT pumps up the gamma providing correct color space. Only NATURAL provides nearly correct gamma and color space.</p>

<h2>Gamma (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Definition</a>) </h2>

<p>The gamma response charts consist of a green line representing the target gamma of 2.2 and a red line representing the response of the display. The average gamma figure only has value when the lines match; otherwise your calibrator will look at the individual steps to identify and correct the problem.</p>

<p>Gamma for BRILLIANT and HIGH color temp<br />
<img title="clip_image001[23]" style="border-right: 0px; border-top: 0px; display: inline; border-left: 0px; border-bottom: 0px" height="434" alt="clip_image001[23]" src="http://www.hdtvmagazine.com/reviews/clip_image00123.jpg" width="465" border="0" /> <br clear="both"/>
0 IRE - NA <br />
10 IRE - 3.2 <br />
20 IRE - 3.3 <br />
30 IRE - 3.3 <br />
40 IRE - 2.5 <br />
50 IRE - 2.0 <br />
60 IRE - 2.2 <br />
70 IRE - 1.5 <br />
80 IRE - 1.3 <br />
90 IRE - 1.2 <br />
100 IRE - end</p>

<p>To provide artificially dynamic images the BRILLIANT gamma is setup to expand the black and the natural consequence is the crushing of white. This leads to video processing artifacts related to pixilation along with a flat dynamic response from 70 to 100 IRE as noted in the review.</p>

<p>Gamma for NATURAL and LOW color temp<br />
<a href="http://www.hdtvmagazine.com/reviews/clip_image0016.jpg"><img title="clip_image001[6]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="434" alt="clip_image001[6]" src="http://www.hdtvmagazine.com/reviews/clip_image0016.jpg" width="465" border="0" /></a> <br clear="both"/>
0 IRE - NA <br />
10 IRE - 2.2 <br />
20 IRE - 3.0 <br />
30 IRE - 2.9 <br />
40 IRE - 2.4 <br />
50 IRE - 2.2 <br />
60 IRE - 2.2 <br />
70 IRE - 2.1 <br />
80 IRE - 2.2 <br />
90 IRE - 2.2 <br />
100 IRE - end</p>

<p>While still climbing strongly out of black at the 20 and 30 IRE points the rest of the response is text book.</p>

<p>Another aspect of linear light output and gamma is how a display deals with full field rasters; when the screen is all white at various light output or IRE levels. Like lamp based front or rear projection displays LCD technology has no issue with this and maintains the same response regardless of IRE levels or the amount of screen area provided for reproduction. This is one area of performance where plasma technology suffers dramatically.</p>

<h2>Color Temperature and Tracking (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">Definition</a>) </h2>

<p>A raw 6500 Kelvin response chart may look nice but it does not reflect a specific color. Delta C is provided instead which shows how far off from D65 the response is. The target is less than 1. Less than .5 error is considered quite good approaching a reference response. RGB response charts are included providing a much better understanding of response errors. In a perfect D65 world all three colors would be flat creating a single line response at 100% providing a flawless color temperature and tracking response.</p>

<p>Delta C for BRILLIANT and HIGH color temp<br />
<img title="clip_image001[9]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="434" alt="clip_image001[9]" src="http://www.hdtvmagazine.com/reviews/clip_image0019.jpg" width="465" border="0" /><br clear="both"/></p>

<p>RGB for BRILLIANT and HIGH color temp<br />
<img title="clip_image001[11]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="434" alt="clip_image001[11]" src="http://www.hdtvmagazine.com/reviews/clip_image00111.jpg" width="465" border="0" /><br clear="both"/></p>

<p>As expected the grayscale response is heavily shifted towards blue which also significantly shifts all color information towards blue.</p>

<p>Delta C for NATURAL and LOW color temp<br />
<img title="clip_image001[13]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="434" alt="clip_image001[13]" src="http://www.hdtvmagazine.com/reviews/clip_image00113.jpg" width="465" border="0" /><br clear="both"/></p>

<p>RGB for NATURAL and LOW color temp<br />
<img title="clip_image001[15]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="434" alt="clip_image001[15]" src="http://www.hdtvmagazine.com/reviews/clip_image00115.jpg" width="465" border="0" /><br clear="both"/></p>

<p>For grayscale calibration the product &quot;appears&quot; to be limited to RGB gain controls only for peak white and does not include necessary controls for peak black. The obvious problem is the sloped red response. Trying to calibrate the product I found out that the gain controls do not function as a calibrator would expect. I tried to flatten out the red but all the gain control does is move that response up or down as is in its entirety. In the world of grayscale calibration we would not call this a gain control yet as an engineer, using the word gain is correct in the generic sense of any electronic adjustment. The controls we are looking for are in there somewhere but Mitsubishi, as usual for many years now, has decided to lock them out. The response is what it is and ISF calibration can't do anything for this aspect of performance. In the end all a calibrator can do is move the Delta C error somewhere else; in this case to the lower IRE response. The human eye is more sensitive to color at peak black rather than peak white so ultimately it is set for the best overall response. That is not a passing grade though because the response you see did not happen by accident, it is calibrated, and the only reason to leave it with these errors is that a correct response reduces light output. The only good news is perceptually it's not bad and the errors fall in the neighborhood of ignorance is bliss; without prior experience viewing accuracy it is highly unlikely you would know it is off. An example is the gray mobius bar of the PS3; I know it's gray (at night anyway) because that is how I have observed it on the reference system and on the Mitsubishi it is clearly tinted blue.</p>

<h2>Color Decoding (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=78">Definition</a>)</h2>

<p>Color encoding and decoding for real images creates a complex array of phase angles which can interact. It is possible to have correct color space and incorrect color decoding. Decoding is tested using patterns that provide complex phase angles. For this test I use the Sencore VP403 Color Decode, SMPTE Color Bars and the Accupel 100% and 75% Color Decoder patterns.</p>

<p>The display does not provide any red, green or blue gating feature to properly test this response. Gating via turning down both of the other colors, in effect turning them off, is 100% inconvenient due to the extreme length of time it takes to 0 out these settings yet I did take the undue amount of time required to do this. Overall decoding via HDMI is quite good using the NATURAL setting if not spot on except for magenta.</p>

<p>Via component YPbPr the errors increase slightly. HDMI has the better response.</p>

<h2>Color Space (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=81">Definition</a>) </h2>

<p>Once color decoding is established then comes color space. There are various types of color space in the world with the American SMPTE C and European EBU being very similar and specified for standard definition mastering applications and broadcast studio monitoring. The new kid on the block is BT709 for HDTV which slightly expands the color space from standard definition. As to which one you should use has been debated heavily. For reviews I will be using HDTV BT709 color space. If the product provides color space management this also infers that you can calibrate for SMPTE-C or EBU if you desire unless stated otherwise.</p>

<p>HDTV BT709 Color Space for BRILLIANT and HIGH color temp<br />
<img title="clip_image001[17]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="378" alt="clip_image001[17]" src="http://www.hdtvmagazine.com/reviews/clip_image00117.jpg" width="457" border="0" /><br clear="both"/></p>

<p>HDTV BT709 Color Space for NATURAL and LOW color temp<br />
<img title="clip_image001[21]" style="border-top-width: 0px; display: inline; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="378" alt="clip_image001[21]" src="http://www.hdtvmagazine.com/reviews/clip_image00121.jpg" width="457" border="0" /><br clear="both"/></p>
<p>Color space varied little between component YPbPr and HDMI. The display has nothing to offer for those seeking SMPTE-C color space or accurate BT709 color space.</p>

<h2>Perfect Color</h2>

<p>This feature wreaks far more havoc with the color response rather than fixing anything. The feature affects both color space and color decoding which ultimately is an error as these are two separate functions. As an example turning down red, blue, magenta and yellow moved the color closer to the color space target yet it also decreased the gain or output of that color wreaking havoc on color decoding. Green and cyan on the other hand moved outward away from their targets when turned down also wreaking havoc on the color decoder. The feature is setup as a gain control for color decoding yet only the primaries, red, blue and green, should have a gain control. Why manufacturers apply this feature to the secondaries of yellow, cyan and magenta is a mystery since these are color decoding phase adjustments, not gain. While Mitsubishi also provides a Perfect Tint feature it is not included with this model to test the positive or negative. Even if included, Perfect Tint suffers from the same problem as Perfect Color; all six colors are adjustable for phase yet red, green and blue are not phase adjustments. In the end I see little benefit of the feature based on the positive color space and color decoding results provided by Mitsubishi. I recommend leaving this in the factory setting of 31 for all colors and living with the magenta color space / color decoding error.</p>

<h2>Y/C and RGB Color Timing (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=84">Definition</a>)</h2>

<p>Via HDMI there is a 1 pixel dark line where magenta meets green and cyan in the magenta box. Via component there is a 1 pixel line, even darker, in the magenta box where it meets green on the left of green and a 1 pixel brighter line where magenta meets green on the right in both blocks creating a 2 pixel wide error. While not an accurate response for either video connection I have seen worse. HDMI has the better response.</p>

<h2>Edge Enhancement (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=79">Definition</a>)</h2>

<p>Out of the box it was quite evident and healthy and could only be removed with the sharpness set at 0. As sharpness was decreased to remove this artifact another artifact began to appear, a slightly darker grey band about 5 pixels in size on either side of single pixel lines at 1080p and visible even at 6 screen heights. This was difficult to pick up on with actual video images but video with the right content would make this artifact visible. Getting rid of the highly visible artifact of white outlines in exchange of the gray artifact was clearly the better choice! This response was equal for both HDMI and component YPbPr.</p>

<h2>Multi-source Ready (<a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=83">Definition</a>)</h2>

<p>The controls for each input are memorized and allow independent adjustment. For each input you have a separate brightness, contrast and color temperature memory for each picture mode. While it appears to meet the requirements of this capability the customer controls and factory service menu controls are very limited in what they can correct. As an example, changing the video levels of the Accupel generator from DVI-PC to DVI-video creates an expected black and white level error. While I could compensate for the black level I could not increase the contrast for the lower peak white level of the signal and this also created a color space error. An end user is best served by using sources that provide correct video levels and response for either RGB or YPbPr video to begin with or have their own controls to compensate which is far easier to acquire these days than ever before.</p>

<h2>Back Lighting and Day / Night Settings (<a href="http://www.isfforum.com/Display-Criteria/Display-Technology/Lamp-&amp;-Backlight.html">Definition</a>)</h2>

<p>While the display offers a back light control it did not react as expected. This came up in testing because I noticed that when left on a black raster the light output would automatically decrease slightly. Going to a 10 IRE window or bringing up the menu brought the light output back up. Due to this anomaly I made sure that profiling would always start with a 100 IRE window so when it starts out at 0 IRE peak black would resemble actual viewing. When doing the contrast ratio measurement I measured .010fl when light output dropped, brought up one of the video adjustments to bring it back up and as soon as that disappeared took the measurement yielding .045fl. Not sure what this reflects in the design. The display never appeared to be pumping / changing light output with video content and I am hard pressed to think of any video content that could trigger this response as it takes very little, the 10 IRE window, to bring it back up.</p>

<p>With the back light control set at maximum I measured 109.7fl and set at minimum measured 21.6fl. That is an extreme drop compared to other panels I have seen so I decided to do some profiling with a back light setting of 0, 31 and 63. Oddly enough at the 0 setting I had the best gamma response from top to bottom, nearly text book. As bias lighting is increased the display pumps up the gamma in the 10-40 IRE region.</p>

<p>Ultimately Mitsubishi's back lighting design does not appear to reduce the light output of the back lights! Black remained the same level of .045fl whether it was set at 0 or 63; this also means contrast ratio changes from a high of 2438 to a low of 480. This was quite a shocker based on other displays and was self evident with a 0 IRE raster because it did not get blacker. What this means is turning off your lights for that movie theater experience and turning down bias lighting to create deep dark blacks for outer space flicks will not be happening with this product.</p>

<p>In my experience any feature that really reduces light output from a lamp does so linearly for any video level, 0-100 IRE; it reduces peak white and peak black. Putting it all together it appears that Mitsubishi is not changing the light output of the back lights and uses video processing to adjust contrast and brightness instead. They have done a good job of implementing this process as video levels remain constant regardless of where you set the back light control and the response is less light output. I did not detect any difference in video performance.</p>

<p>As noted, other LCD products with back lighting don't have nearly the range of this display. In practice I found the Mitsubishi design will do a better job in being able to match your ambient lighting. In a pitch black room and the back lighting control set to minimum I experienced a good contrast ratio without feeling my eyes were getting scorched. Black is a perceptual illusion and that illusion was in full play provided there was video on the screen. The only time I ever noted that black wasn't CRT inky black was only during all black rasters, a rare experience with actual video. Turning on the lights at night I increased back lighting to 31 creating the same perceptual experience and during the day I turned it up all the way creating yet again the same perceptual experience. In both of those scenarios black was black. In the end, kudos to Mitsubishi because this design covered not just two different levels of room ambient light but all three which is quite extreme while maintaining a great video response regardless of back light setting! Such displays are rare.</p>

<h2>Contrast Ratio</h2>

<p>With bias lighting set at the maximum and using a Minolta LS100 I measured .045fl for peak black and 109.7fl for peak white yielding an impressive contrast ratio of 2438. This particular aspect of current state of the art LCD technology stood out in spades when compared to other display technologies.</p>

<h2>Uniformity</h2>

<p>This is the first time I am reporting on this aspect of performance because this is the first display to clearly have a visible problem anybody would see. I did not perform a full spec screen uniformity measurement. The overall screen area measures about .045fl. With a 0 IRE black raster I had a spot of light in each corner measuring about .1fl that spread out in size yet also diminished in light output extinguishing itself within about 4-5 inches into the screen diagonally. Along with that was an extended patch of light in the lower left side of the screen coming from the lower left corner measuring about .06fl. There were other patchy areas of light throughout the screen area that were only visible if staring at a 0 IRE raster. The only errors evident when viewing actual video content were the corners and that was quite rare.</p>

<p>While the uniformity error was rarely visible there are other displays that perform better in this regard</p>

<h2>HQV Benchmark Standard Definition via the Panasonic DVD-RP91 (<a href="http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php">LINK</a>) </h2>

<p>Color Bars (4:3) PASS<br />
Color Bars (16:9) PASS</p>

<p>Jaggie 1 (16:9) FAIL</p>

<p>Jaggie 2 (16:9) FAIL</p>

<p>Flag (4:3) FAIL</p>

<p>Detail (16:9) FAIL</p>

<p>Noise (4:3) PASS</p>

<p>Motion Adaptive Noise (16:9) PASS<br />
Motion Adaptive Noise (4:3) PASS <br />
An interesting point is that the display failed this test for HQV Benchmark Blu-ray (next section). The birds in the sailing boat scene are moving quite a bit faster than the roller coaster. Another point is HQV mentions the roller coaster as a good test for LCD pixel speed yet I think that is based on past LCD technology rather than current technology. This display did not have any problem with that motion!</p>

<p>Film Detail (4:3) FAIL</p>

<p>Assorted Cadences (16:9)<br />
2-2 30fps film - FAIL <br />
2-2-2-4 DVCAM - FAIL <br />
2-3-3-2 DVCAM - FAIL <br />
3-2-3-2-2 VARI SPEED Broadcast - FAIL <br />
5-5 Anime - FAIL <br />
6-4 Anime - FAIL <br />
8-7 Anime - FAIL <br />
3-2 24fps film - PASS</p>

<p>Mixed 3:2 with titles (4:3) PASS</p>

<p><strong>Conclusion</strong> <br />
I also ran the battery of Accupel patterns via 480i and 480p via HDMI and component. All video response results were worse in every category. With the HQV tests most notable was a very clear edge enhancement ghost for all vertical lines (horizontal edge enhancement) appearing very similar to NTSC RF ghosting from an outside antenna suffering from multipath. From experience I did not expect much from 480i but was surprised that 480p suffered as much as it did. I also ran the HQV battery of tests in 480p and was shocked to find the Mixed 3:2 With Titles test that passed now failing or other tests that failed having an even worse response and it was not the DVD player. Only the Film Detail test was improved. During my time with the display I changed the TIVO Series 3 cable box to native output for a few days. The next day my son asked why the picture looked so bad while viewing an SD channel. Eventually I switched the output to 1080i only. This display performs best with external scaling of SD sources to 720p, 1080i or 1080p. An upconverting DVD player is highly recommended!</p>

<h2>HQV Benchmark Blu-ray, Tested HDMI, 1080i via the Sony PS3 (<a href="http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php">LINK</a>) </h2>

<p>HD Noise Test A - PASS<br />
HD Noise Test B - FAIL <br />
For either test the noise feature was set on high since that was the only setting providing a substantial improvement worthy of the effort. It gets a PASS on the flower for reducing noise while preserving detail. It gets a FAIL on the sail boat due to a slight trailing blur of the birds that fly through. This was not an artifact of LCD technology but an artifact of the scaler and what would appear to be temporal noise filtering. Using the high setting for noise reduction is a double edged sword creating this artifact yet a lower setting will not provide any substantial noise reduction improvement.</p>

<p>Video Resolution Loss - PASS</p>

<p>Jaggies A - PASS</p>

<p>Jaggies B - PASS</p>

<p>Film Resolution Loss A Vertical - FAIL / PASS<br />
The scaler gets a PASS for providing a clearly defined 1080 line response. It gets a FAIL for an edging artifact on either side of the 1080 box appearing as 2-3 pixels wide of random noise on the left and 1-2 pixels wide of the same on the right. The black edging artifact appeared on the top corner and center 1080 boxes. For the bottom corners this artifact was displayed as white instead. Oddly enough when bringing up the menu of the display these artifacts disappeared providing a PASS response.</p>

<p>Film Resolution Loss A Horizontal - PASS</p>

<p>Film Resolution Loss B - PASS</p>

<p><strong>Conclusion</strong> <br />
Overall the internal scaler did good job of passing these tests. I don't place much emphasis on the noise test since in most cases this should be turned off yet if this is a feature you seek you know the pitfalls of using it. While the scaler did fail the Film Resolution Loss A test none of those artifacts showed up with the video content or the Film Resolution Loss B test of the stadium pan.</p>

<h2>Motion Blur and Smooth 120Hz LCD Processing</h2>

<p>For a deeper understanding please read <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=10552">HD Waveform: Motion Blur and 120 Hz LCD Processing</a>.</p>

<p>In the menu under GLOBAL you can select SMOOTH 120 DEMO and the display will provide two split screen examples of the artifacts the feature will remove. The first is a panning image of a woman seated in front of a PC along with horizontally scrolling text. The text for the unprocessed side appears to show an example of frame rate motion blur related to object speed along with detail motion blur of the woman and her clothing. The other is a high speed motorcycle. Although highly instructive of what Smooth 120 Hz Processing is supposed to eliminate neither represented real world experience. One example is HQV Benchmark DVD provides a text test and it was as clear as any other display technology.</p>

<p>Mitsubishi 120 Hz processing fixes all of these problems yet as noted in the review it also creates an entirely artificial response making natural imaging and natural motion appear computer generated; it is not high fidelity, being faithful to the original. Perceptually many viewers are likely to approve just as they approve of a <em>sales mode</em> response; it can easily be perceived as better yet it clearly is not high fidelity. The processing was not perfect either intermittently losing frame lock on a regular basis allowing motion artifacts for a brief moment. Nearly all of our viewing of this product occurred with this feature turned off.</p>

<p>After more than two months of viewing with 120 Hz processing turned off I can count on only one hand how many times such artifacts were clearly evident and unique to LCD display technology under a casual viewing mind set. There are numerous facets involved such as viewing distance, contrast ratio (turning down the back lighting on the Mitsubishi can reduce the effect), frame rate, cinematography and especially what elements of the image you happen to be focused upon when viewing. With that said such artifacts may have appeared far more often but my focus was else where in the image. Neither my wife nor son ever complained about motion artifacts.</p>

<h2>Final Notes</h2>

<p>One of the most interesting developments from Mitsubishi since about 2004 is the inclusion of ISF text in the service menu. At first blush a performance user and ISF calibrator will think this infers full bore ISF calibration capability only to find the controls have little to no effect or are setup in such a way that you can't get the correct results.</p>

<p>There are a number of operational quirks. While you get the best results using the PC setting, 720p will be pixel mapped to the center which most users will not want. It won't accept 1080i in this mode either and while 1080p60 would work fine that won't do a thing for broadcast content from cable or satellite sources which are limited to 1080i output. 1080p24 doesn't pixel map so you lose out on the purist approach as well in this mode. On the surface it would appear one answer is to simply change the input label to take it out of PC mode for 1080p24 and deal with the color response loss along with the luminance and chroma errors but that trade off comes with a nasty catch 22. When you change labels the controls are all reset so using that approach comes with realignment of the controls every time you change the label.</p>

<p>The Plush 1080p internal scaler has an intermittent problem with vertical combing of luminance which was highly difficult to nail down. The HQV DVD Film Detail test made this artifact easy to reproduce on demand. I can tell you it happens far more often using the STANDARD aspect ratio (which corrupts 1:1 pixel mapping by introducing slight over scan) and any SD content whether native SD or SD converted by the broadcaster for their native HD broadcast. The STANDARD aspect ratio provided is required due to the common VIR signal used with NTSC, standard definition broadcast, that appears as white dashes across the very top of SD content that 0 over scan allows you to see when using the NATIVE aspect ratio. Within 1-2 days my wife complained about these dashes!</p>

<p>PERFECT COLOR has little to do with correcting color space and is not intuitive in that regard. Color decoding was fairly good so again not much to offer there either.</p>

<p>While uniformity was poor it was not something that showed up regularly and requires just the right kind of content to make itself clearly visible.</p>

<h2>Performance Conclusion</h2>

<p>This is not a videophile product for numerous reasons as shown in the testing. The display is severely limited for an ISF calibration yet ISF calibration is not just about display calibration but also system calibration and source confirmation. An external scaler set for 1080p could be added, with the display input set for PC mode to improve performance and correct the errors that remain in which case a full blown ISF calibration would be performed/required. That said, it was shocking how well it does perform with the factory calibration!</p>

<h2>Best Performance Settings</h2>

<p>A first for one of our reviews and provided since ISF display calibration has little value.</p>

<p>Picture Mode - NATURAL<br />
Back Lighting - set for your viewing environment (we used full output, 61) <br />
Video Noise - OFF <br />
Color Temp - LOW <br />
Sharpness - 00 <br />
Tint - 31 (factory) <br />
Color - 31 (factory) <br />
Brightness - 31 (factory) <br />
Contrast - 63 (factory)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>March 19, 2009  9:06 AM</b>
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
			<?=getComments(1690)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 1690)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-on-the-test-bench.php" type="text/javascript" charset="utf-8"></script>
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