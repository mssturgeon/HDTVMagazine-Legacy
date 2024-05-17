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
		AND e.entry_id = 1662";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Doug Brott" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1662 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Doug Brott'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Doug Brott" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1662 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1662";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2009/01/the-directv-plus-high-definition-dvr-year-two.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1662";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The DIRECTV Plus High Definition DVR &ndash; Year Two" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The DIRECTV Plus High Definition DVR &ndash; Year Two" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The DIRECTV Plus High Definition DVR &ndash; Year Two" />
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
	<title>HDTV Magazine - The DIRECTV Plus High Definition DVR &ndash; Year Two</title>
	<meta name="keywords" content="directv plus, high definition, year directv, enthusiast community, second year, directv, dvr, plus, year, new, users, available, features, user, content, definition, added, satellite, channels, experience, high, software, receiver, enthusiast, second" />
	<meta name="description" content="After a jam packed year, the DIRECTV Plus High Definition DVR has become the flagship receiver for a company that has become one of the leaders in high-definition programming. The enthusiast community is stronger than ever, fusing innovation and collaboration in ways that have never been done before. Sit back while we reflect on the things that have made this trip something to remember.

Joining the HR20-700 and the HR20-100 this year are the..." />
	<meta name="title" content="The DIRECTV Plus High Definition DVR &amp;ndash; Year Two" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The DIRECTV Plus High Definition DVR &amp;ndash; Year Two" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2009/01/the-directv-plus-high-definition-dvr-year-two.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="After a jam packed year, the DIRECTV Plus High Definition DVR has become the flagship receiver for a company that has become one of the leaders in high-definition programming. The enthusiast community is stronger than ever, fusing innovation and collaboration in ways that have never been done before. Sit back while we reflect on the things that have made this trip something to remember.

Joining the HR20-700 and the HR20-100 this year are the..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1662', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2009/01/the-directv-plus-high-definition-dvr-year-two.php">The DIRECTV Plus High Definition DVR &ndash; Year Two</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Doug Brott</b> on <b>January 12, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=300&category=Cable, Satellite & Fiber">Cable, Satellite & Fiber</a></b>
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
				<p class="editorial">This article is a collective effort of Doug Brott and Stuart Sweet.</p>
<p>After a jam packed year, the DIRECTV Plus High Definition DVR has become the flagship receiver for a company that has become one of the leaders in high-definition programming. The enthusiast community is stronger than ever, fusing innovation and collaboration in ways that have never been done before. Sit back while we reflect on the things that have made this trip something to remember.</p>
<p>Joining the HR20-700 and the HR20-100 this year are the HR21-100, HR21-200, and HR21-700, as well as the new HR22-100 with 100 hours of recording capability. The new HR21 models sport a sleek black exterior and function in much the same way as the HR20 models. While the HR21 series lacks the HR20's built-in ATSC tuner, DIRECTV moved aggressively to cover the nation with local HD channels, and provided the AM21 antenna module for those still awaiting satellite coverage. Professional users also welcomed the HR21-Pro.</p><p>The DIRECTV Plus HD DVR also added another member of the family in its second year: the R22 Standard Definition DVR, designed for markets where standard-definition local channels were encoded in the superior MPEG-4 format. The R22 DVR has all the features of its HD brethren, except of course true HD. However, it can output EDTV (480p) through component or HDMI, and can use all the other advanced functions of the Plus HD DVR, including media sharing and On Demand. By using the more mature software and hardware of the HR21 series, the R22 was able to hit the streets quickly in response to expansion into new markets.</p><h2>Consumer Electronics Show</h2> <p>The 2008 Consumer Electronics show in Las Vegas brought more news of the DIRECTV Plus HD DVR product line. DIRECTV hosted its own enclosed "experience" room, along with silver-coated satellite dishes, nearly 100 large flat screen TVs, and a simulated "install experience" covered through a window crafted to look like a TV screen. Of course, this was all icing on the cake for the news of their changes to the Plus HD DVR series.</p><p>The AM21 off-air tuner was revealed, and with the AM21 there was truly no difference in capabilities between any member of the Plus HD DVR line. A short few months later, the AM21 became available to the public.</p><p>DIRECTV also showed the Samsung-built HR21Pro. Designed for rack mounting with a larger hard drive and more ventilation, the HR21Pro was designed with installers in mind and allowed for Optical HDMI (OWLink was on site with a solution) and serial IR control. The HR21Pro became available to the public shortly after the close of the Consumer Electronics Show.</p><p>Lastly, they showed a beta of DIRECTV2PC, which will allow PC-based playback of DIRECTV Plus HD DVR content and is in limited testing among the enthusiast community.</p><h2>The Beat Goes On</h2> <p>There were 10 national software releases for the DIRECTV Plus HD DVR in its second year, all delivered via satellite, and included everything from new features to bug fixes. This number was fewer than half of the 23 updates required in the HR20's first year.</p><p>DIRECTV also began utilizing the enthusiast community at a much more frequent rate as the Cutting Edge (CE) program (at <a id="eesw" title="DBSTalk.com" href="http://www.dbstalk.com/" target="_blank">DBSTalk.com</a>) became the go-to place each week for enthusiasts to contribute to DIRECTV's success. Never before had real customers become so visible. The collaboration between DIRECTV and its customers enabled average people to download pre-release firmware and kick the tires before the firmware reaches the general public. In this way, new features or changes can be eased into the software rather than making one big monster change that would otherwise be pushed to all DIRECTV Plus HD DVR customers at the same time. Cutting Edgers, or "CE'ers" as we call ourselves, test new features and check national release candidates, helping DIRECTV to bring the best product to its millions of subscribers.</p><p>The CE program has grown up over that past year as well. The process has become a nearly weekly event involving the distribution of information, the collection of data and a visit to the Chat Room each time new software is available for the DIRECTV Plus HD DVR. Each week, there are Chat Room regulars that help point new folks in the right direction. While this process has more or less grown as a grass roots effort, it has proven to be an effective means of getting to the goal of a perfect receiver.</p><p>The CE collaboration made a quantum leap when DIRECTV gave testers the ability to send detailed logs and information over their network line. This gave their engineers information to more quickly identify problems and, as a result, more quickly provide a solution. The network issue reporting system is completely "opt-in" and does not interfere with the regular user's experience. No other system like this has ever been implemented on a set-top box, and is to us clear evidence of DIRECTV's commitment to the Cutting Edge program.</p><p>Some of the notable features added to the DIRECTV Plus HD DVR this year are:</p><ul> <li>Added Resume/Start Over options for playback of recordings</li> <li>Added Delete button to My Playlist and To Do List</li><li>DIRECTV on Demand</li><li>Hide SD channels if duplicate HD channel exists</li><li>Triple Tap Lookup for DIRECTV on Demand</li><li>30 Second Skip</li><li>MediaShare</li><li>Boolean Search</li><li>Shortcut for Closed Caption On/Off</li><li>Pass-through (Original Format) Option</li><li>GameSearch</li><li>Triple Tap for Search</li><li>Network Issue Reporting</li></ul> <h2>Simply the Best</h2> <p>One of the most exciting features to come to the DIRECTV Plus HD DVR in the second year isn't even built into the box. The addition of new high-definition channels has brought new meaning to having high-definition television. In September, 2007, DIRECTV moved its new satellite (DIRECTV10) into position and lit up 30 new HD channels overnight including SciFi, CNN, and The History Channel. With that one change, the DIRECTV Plus HD DVR all of a sudden became the receiver of choice. In addition, HD local channels are now available in over 115 different markets available. This encompasses over 85% of all US households. With another satellite (DIRECTV11) now online and yet another satellite (DIRECTV12) going up this year, DIRECTV is positioned to provide 200 national HD channels when it's all said and done. The DIRECTV Plus HD DVR will provide access to current and future high-definition content.</p><p>Another highly anticipated feature added in the past year is DIRECTV on Demand. By simply connecting the DIRECTV Plus HD DVR to a high-speed home network connection, users can download movies with the push of a button. The programs available include television shows, documentaries, music videos and much more. Recently, DIRECTV began charging for some of the content, but much of it is free and all of it is available to 24 hours a day.</p><h2>Catering to the Enthusiast</h2> <p>In a nod to power users, DIRECTV implemented three incredibly powerful search improvements in the DVR's second year:</p><ul> <li>For sports fans, GameSearch will automatically seek out rebroadcasts of games that are blacked out on a particular channel. This feature is automatic and is proving to be a real crowd-pleaser.</li> <li>Triple Tap, a suggestion from the HR20/HR21 Wish List, allows users to bypass the "grid" system for choosing letters and numbers, and lets them instead use their remote controls as they would use cell phone keypads. Pushing "2" once for "A" and twice for "B" has been a major improvement to the search experience for advanced users.</li><li>The most powerful search option available, Boolean Search, lets users create complex search using Boolean operators (AALL, NNOT, AANY) and limitations on content (TTITLE, NNAME, CCHAN) for the ultimate in flexibility. For example, it's now possible to pick only "Law and Order" episodes on USA that feature Chris Noth with a single query.</li></ul>The <b>original format</b> option was added, allowing the purest possible viewing experience for the user by sending picture and sound directly to the television and AV receiver with no postprocessing. DIRECTV's HD DVR is unique in the Satellite industry as the only receiver to offer this option.</li> <h2>User Experience Improvements: DIRECTV reaches out</h2> <p>This past year also saw sweeping improvements to the user interface. DBSTalk users had debated since the introduction of the first non-TiVo DVR as to which user interface was better. Through a series of guided discussions, DIRECTV engineers embarked on a complete revamp of the user interface. The Menu button now brought up the same options regardless of where it was used, and the Yellow button, formerly used in an inconsistent way, now brought up context-sensitive options for every screen. Among these was a welcome improvement: a way to turn closed captioning on or off in three pushes instead of nineteen. While access to the to do list now took 5 pushes instead of two, the improvements to other options eclipsed this for many users.</p><p>Updated splash screens gave users a better experience during reboots with fully-rendered logos. This minor change, aimed at pleasing CE'ers, sometimes preceded messages telling regular users what had changed.</p><p>Throughout the second year of the DIRECTV Plus HD DVR, DBSTalk members tested new technologies and new software. One of the most interesting was the Media Share PC software which allowed PCs to stream programs from HD DVRs, with all the same controls as the user would have at the television. Unlike other products that simply capture the output from the TV and re-encode it, Media Share PC is DIRECTV's first step toward true multi-room viewing. The programming on the PC can be completely different from what the user on the TV sees, and is transferred to the PC at full resolution, making this the first HD multi-room viewing solution from a major provider.</p><p>While the requirements for the client PC are steep by 2008 standards, advances in technology will bring this solution closer to mass availability quickly. For now, most PCs capable of playing Blu-ray discs should be able to share media. Using industry-standard security measures, users cannot save streamed content or move it out of the local network, ensuring that DIRECTV can honor its agreements with content providers.</p><h2>Until Next Time</h2> <p>The enthusiast community at DBSTalk.com has become an everyday part of the improvements to the DIRECTV Plus HD DVR. The CE program, started by Earl Bonovich, has proven that customers and corporations can work together to make a product even better. A number of exciting features have been added in the past year with even more exciting features in the works for the next year. The addition of significant HD content in the past year will be followed by the addition of even more HD content in the coming year, and the DIRECTV Plus HD DVR series will be the receiver that will make it all available.</p><p>It has been our pleasure to be part of the enthusiast community and part of the improvement process. Year three promises to be the best yet!</p><p><i>Doug Brott, Super Moderator, DBSTalk.com<br></i><i>Stuart Sweet, Super Moderator, DBSTalk.com</i></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Doug Brott</b>, <b>January 12, 2009  8:11 AM</b>
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
			<?=getComments(1662)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Doug Brott', 1662)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Doug Brott</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2009/01/the-directv-plus-high-definition-dvr-year-two.php" type="text/javascript" charset="utf-8"></script>
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