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
		AND e.entry_id = 3388";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3388 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3388 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3388";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-2-the-quality-factors-of-hdtv-content-distribution.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3388";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution" />
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
	<title>HDTV Magazine - High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution</title>
	<meta name="keywords" content="sub channels, content distribution, sub channel, mobile dtv, per second, content, dtv, quality, digital, channel, mobile, analog, channels, using, sub, frame, broadcast, per, mhz, compression, bandwidth, system, image, distribution, fields" />
	<meta name="description" content="High quality HDTV can be broadcasted using the 6 MHz channel-slot of one analog NTSC channel, but with over 9 times its resolution quality. The NTSC image is made of 480ix450 viewable picture elements per video frame composed of two interlaced fields and delivered at a rate of 60 fields (30 frames) per second. 

Most HD broadcast compressed with MPEG-2 is transmitted as interlaced 1080i with 2 million+ pixels per video frame (1080x1920 pixels), each frame is composed of two interlaced 540-line fields delivered at the rate of 60 fields per second (30 frames). 

Some HD broadcasts use..." />
	<meta name="title" content="High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-2-the-quality-factors-of-hdtv-content-distribution.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="High quality HDTV can be broadcasted using the 6 MHz channel-slot of one analog NTSC channel, but with over 9 times its resolution quality. The NTSC image is made of 480ix450 viewable picture elements per video frame composed of two interlaced fields and delivered at a rate of 60 fields (30 frames) per second. 

Most HD broadcast compressed with MPEG-2 is transmitted as interlaced 1080i with 2 million+ pixels per video frame (1080x1920 pixels), each frame is composed of two interlaced 540-line fields delivered at the rate of 60 fields per second (30 frames). 

Some HD broadcasts use..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3388', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-2-the-quality-factors-of-hdtv-content-distribution.php">High Definition Content Distribution in the US (Part 2) - The Quality Factors of HDTV Content Distribution</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November 24, 2009</b>
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
				<p> <h2>Expectation for Quality </h2> <p>High quality <a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HDTV</a><b> </b>can be broadcasted using the 6 MHz channel-slot of one analog <a href="http://www.hdtvmagazine.com/glossary.php#NTSC">NTSC</a> channel, but with over 9 times its resolution quality. The NTSC image is made of 480ix450 viewable picture elements per video frame composed of two interlaced fields and delivered at a rate of 60 fields (30 frames) per second.  <p>Most HD broadcast compressed with MPEG-2 is transmitted as <a href="http://www.hdtvmagazine.com/glossary.php">interlaced 1080i</a> with 2 million+ pixels per video frame (1080x1920 pixels), each frame is composed of two interlaced 540-line fields delivered at the rate of 60 fields per second (30 frames).  <p>Some HD broadcasts use the other HD <a href="http://www.hdtvmagazine.com/glossary.php#720p">720p</a> progressive format which has about 1 million viewable pixels per video frame (720x1280 pixels), delivered as 60 frames per second using MPEG-2 digital compression.  <p>Although the 720p progressive HD frame has only half of the spatial pixel resolution of the 1080i frame the format’s higher frame speed makes it better suitable for sports than any interlaced format, which is prone to artifacts that result from interleaving fields of fast moving content.  <p><b></b> <p><b></b> <h2>Potential Quality Concerns </h2> <p><b></b> <p><u>Over Compression</u></p> <p>Digital compression (<a href="http://www.hdtvmagazine.com/glossary.php#MPEG-2">MPEG-2</a> for DTV) allows for a digital signal to fit into a smaller space for recording or transmission purposes. The saved space could be used for other sub-channels or services.  <p>Digital compression can be applied in a way that was not possible with analog NTSC, and improved algorithms could make it more efficient so more content can be transmitted using the same bandwidth. However, the flexibility provides an opportunity for exploiting a quantity model rather than pursuing HD quality, which affects the supply and demand of HD content distribution.  <p>Cable/satellite/Telco companies are known to apply over-compression and bit starving techniques to maximize their limited bandwidth so they can offer a greater number of HD channels to remain competitive.  <p>Subscribers using small screens and/or viewing from far away may not notice image degradation as opposed to consumers that are increasingly upgrading to larger and higher resolution screens to been able to sit closer to the screen and increase the angle the view beyond <a href="http://www.hdtvmagazine.com/glossary.php#THX">THX</a> standards and stimulate the peripheral vision to be better immersed in a movie.  <p><u>Selected Output Control</u></p> <p>Since 2002 the Federal Communications Commission (FCC) has <a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">prohibited the use</a> of selected output controls (SOC) designed to restrict or <a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">down-res</a> the quality of an HD signal when using the analog outputs of tuning devices (but not affecting the digital outputs, such as <a href="http://www.hdtvmagazine.com/glossary.php#DVI+%28Digital+Visual+Interface%29">DVI</a> or <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php">HDMI</a>, because they are protected with High Definition Content Protection (HDCP).  <p>The Motion Picture Association of America (MPAA) returned to the FCC in May 2008 for a waiver <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_blu-ray_only_if_your_hdtv_permits_it.php">to permit SOC</a> for a new model of HD content distribution for VoD (Video-on-Demand) HD movies just after their theater release and before they become available in Blu-ray.  <p>The waiver was justified over the concern of the possibility that the content could be copied and distributed illegally thru piracy, and therefore negatively affect the subsequent packaged media business (DVD and Blu-ray sales) and PPV (Pay-Per-View), but it was giving the MPAA too much leeway in its implementation of SOC, affecting many consumers with non-compliant HDTVs which could be left in the dark.  <p>SOC counts on <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php">HDMI</a> working well and too much trust is put on the assumption that protected digital connections were implemented properly on consumer electronics, which is <a href="http://www.cepro.com/article/hdmi_or_component_integrators_weigh_in">not the case</a>. As of January 2009 the approval for the waiver has not been granted.  <p>Although movie studios did not implement it yet, Blu-ray disc content is protected with a feature to reduce <a href="/articles/2009/11/who_needs_content_protection.php">HD 1080i/p quality to SD</a> when using the analog outputs of the player.  <p>In a similar effort, in February 13, 2004, DirecTV requested the FCC to allow 'down-res' of certain non-broadcast programs over the component analog connections of their HD-STBs, anticipating that some movie studios will not make available to them some of the more popular movies if using unprotected analog outputs.  <p>Similarly, in November 2003, to limit the indiscriminate redistribution of digital broadcast content, the FCC approved the '<a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">Broadcast Flag</a>' anti-piracy order; a digital code embedded into a digital broadcasting stream that would signal DTV reception equipment to activate the redistribution limit. The order was to take effect in July 1, 2005 but the court ruled that the FCC had over-stepped its authority.  <p>In summary, artificial degradation to protect HD content has been pursued and requested several times, and its implementation affects consumers and the HD content distribution market.  <p><b></b> <p><u>Multi-casting Standard Definition Sub-channels</u></p> <h4></h4> <p>The 6 MHz broadcast channel slot used by 1 channel in the analog system has enough capacity to simultaneously broadcast 4-6 DTV <a href="http://www.hdtvmagazine.com/glossary.php#SDTV+%28Standard+Definition+TV%29">standard definition</a> (SD) sub-channels to meet the requirements of certain demographic areas, ethnic channels, weather, children, etc., and also multiply the advertising revenue of broadcasters.</p> <h4></h4> <p>In the analog world, those 4-6 sub-channels would have required 6MHz each and may not have existed due to TV spectrum limitations. A single HD channel typically requires the entire 6 MHz bandwidth for itself at a 19.4 Mbps bit rate to deliver a quality image using MPEG-2 compression.</p> <h4></h4> <p>Many TV stations in the US, such as PBS, are implementing a combination of HD and SD channels sharing the same 6MHz bandwidth. Based on my experience since 1998, when the bit rate of the HD sub-channel is considerably reduced by multi-casting parallel SD sub-channels the quality of the HD sub-channel deteriorates beyond acceptance, especially when fast content is viewed on large screens.</p> <p><b></b> <p><u>DTV Mobile and Handheld Devices</u></p> <p>In June 2007, the <a href="http://www.hdtvmagazine.com/glossary.php#ATSC">ATSC</a> received proposals regarding a DTV mobile standard to be approved in 2009. Several conditions were established, which include but are not limited to, protecting legacy receivers and existing services, be compatible with the current ATSC DTV system, and operate in the same RF channel without penalizing legacy equipment.  <p><a href="http://www.hdtvmagazine.com/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Samsung initially proposed their A-VSB</a> system but later supported the approved-candidate ATSC-Mobile/Handheld (M/H) standard based on the MPH (Mobile Pedestrian Handheld) mobile DTV transmission system jointly developed by LG Electronics and Harris Broadcast.  <p>The Mobile DTV system of transmission shares the 6MHz assigned to a terrestrial DTV channel and, like other similar mobile digital transmission systems, can generate new business opportunities for broadcasters.  <p>However, the bits needed for the digital mobile transmission are subtracted from the bandwidth needed by the HD image (about 20% or more for A-VSB) potentially degrading the HD image quality, specially if an SD sub-channel is also multi-cast in parallel (3 transmissions: HD, mobile M/H, and SD).  <p>The Mobile DTV standard was finally <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">approved</a> by the ATSC on October 15, 2009.  <p><b></b> <p><u>Datacasting</u></p> <p>DTV is capable of <a href="http://en.wikipedia.org/wiki/Datacasting">datacasting</a>, sharing the bandwidth typically allocated to one HDTV channel. Datacasting provides broadcasters the opportunity of new business models with the implementation of DTV, not possible with traditional analog television.  <p>The final part 3 covers HD content in pre-recorded media, and conclusions.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November 24, 2009  9:31 AM</b>
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
			<?=getComments(3388)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3388)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-2-the-quality-factors-of-hdtv-content-distribution.php" type="text/javascript" charset="utf-8"></script>
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