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
		AND e.entry_id = 3733";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3733 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3733 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3733";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-kramer-proscale-vp729-presentation-switcherscaler-june-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3733";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)</title>
	<meta name="keywords" content="analog video, presentation switcher, video signals, digital audio, hdmi output, video, hdmi, audio, output, kramer, image, menu, signals, analog, signal, ’ll, input, ’s, inputs, resolution, buttons, vga, screen, switching, digital" />
	<meta name="description" content="Kramer’s ProScale VP-729 switcher/scaler packs a bunch of features into an affordable and practical package…and it supports HDMI, too." />
	<meta name="title" content="HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-kramer-proscale-vp729-presentation-switcherscaler-june-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Kramer’s ProScale VP-729 switcher/scaler packs a bunch of features into an affordable and practical package…and it supports HDMI, too." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3733', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-kramer-proscale-vp729-presentation-switcherscaler-june-2009.php">HDTV Expert - Product Review: Kramer ProScale VP-729 Presentation Switcher/Scaler (June 2009)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April  8, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=304&category=General Interest">General Interest</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>It wasn’t all that many years ago that the idea of a seamless presentation switcher was nothing more than fantasy. Back in the late 1990s, the farthest anyone had come with switching and mixing video signals was to combine the functions of basic line doublers and quadruplers with a couple of frame buffers, resulting in a product with five- and six-figure price tags.</p>
<p>But Moore’s Law prevailed, as it always does. Today, it’s possible to buy a presentation switcher for less than $2,000 that works better than those early line-doubling models. That’s good news for anyone who has a modest AV facility, but wants to switch between video and computer sources as smoothly and elegantly as staging companies do.</p>
<p>Kramer Electronics, one of the fastest-growing companies in the Pro AV marketplace, specializes in feature-rich but affordable video and audio interfaces. Their earlier efforts at presentation switching have been met with favorable reviews by a myriad of end-users. It was only a matter of time before a product like the VP-729 made its curtain call, combining HQV-quality video signal processing with Ethernet connectivity and a very attractive price – just $1,595 MSRP.</p>
<h1>
</h1><p></p><div id="attachment_434" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-15.jpg"><img class="size-full wp-image-434" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-15.jpg" alt="" width="600" height="111" /></a><p class="wp-caption-text">Figure 1. Front view of Kramer's ProScale VP-729 presentation switcher. </p></div>
<p>OUT OF THE BOX</p>
<p>The VP-729 is surprisingly compact, measuring just 1RU in height. It’s finished in the usual Kramer blue-gray, sporting nine input selection buttons, three additional function buttons, and eight smaller buttons for accessing the menu and other functions, including navigation. A separate power switch is on the far left, along with an IR control sensor.</p>
<p>There are nine inputs on the rear of the VP-729, four of which can be configured to accommodate multiple analog video formats. In addition, there’s a USB 2.0 jack on the front panel that does double duty as a JPEG still image reader and a port for uploading firmware updates.</p>
<p>Each of the four analog video inputs consists of three RCA jacks and will accept composite, S-video, and component (YPbPr) video signals up to a maximum resolution of 1920×1080p 50/60. Note that you’ll need a special adapter cable to connect S-video to the VP-729. Kramer hasn’t included DIN-style S-video jacks on this switcher, but given how few people use that signal format anymore, it may be a non-issue.</p>
<p>The next two inputs are standard 15-pin VGA jacks, labeled “UXGA 1&amp;2” on the front and back panels. These connectors will accept just about any RGBS/RGBHV signal format all the way to 1920×1200 resolution with a 60Hz frame rate. You can also create a custom configuration in the Advanced menu to work with even higher image resolutions.</p>
<p>The last two inputs are HDMI 1.3 types. Like more and more companies in the pro AV channel, Kramer has opted to replace DVI connections with HDMI, ostensibly because they take up less room, and can also carry digital audio – a real handy thing to have in a switcher. The connectors are fully HDCP-compliant, which might throw up a red flag in terms of being able to switch sources smoothly. (Not a problem, as you’ll see shortly.)</p>
<h1>
</h1><p></p><div id="attachment_435" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-25.jpg"><img class="size-full wp-image-435" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-25.jpg" alt="" width="600" height="105" /></a><p class="wp-caption-text">Figure 2. You’ll have enough connectors for just about any conference room or classroom installation.</p></div>
<p>Kramer has provided two video outputs. The first is another HDMI 1.3 jack, while the second is a 15-pin VGA connector. (You can drive both at the same time.) The VGA jack can work as a conventional RGBHV connection, or be configured to transport YPbPr signals on three of its pins.</p>
<p>There are several ways you can have audio follow video around during switching. Separate stereo RCA jack are provided for each of the four analog video inputs, while a pair of 1/8” mini phone jacks are used to interface PC audio. Embedded audio through the HDMI jacks moves around just as easily, and you can enable/disable the embedded audio stream from the menu.</p>
<p>For audio output, Kramer has included one additional pair of RCA jacks for an analog connection, plus a coaxial SPDIF output. And of course, the HDMI output jack also carries switched audio from any source. The connector complement is topped off with a standard DB9 RS232 port for remote control, plus an Ethernet jack for TCP/IP operation.</p>
<p>REMOTE AND MENUS</p>
<p>The supplied remote control was too busy for me with 30 buttons of similar size and color. (The Power and Menu buttons are red; all others are white.) But the upside is that you’ll have direct access to any input and generally fast navigation when making adjustments.</p>
<p>In addition to discrete up/down/left/right buttons for navigation, you’ll also find eight buttons at the bottom of the remote for designating the picture-in-picture (PiP) source signal. I would have left these behind a cover – it’s not likely that the settings will be changed all that frequently.</p>
<p>Additional buttons operate the switcher’s Freeze Image mode, let you switch to a blank screen, capture a JPEG image to be used as a screen-saver or boot-up screen, save and recall image settings, and mute audio. You can also push and hold the RESET button to restore the VP-729 back to its default output resolution of 1024×768 (XGA), just in case you accidentally configure a non-supported output signal. (Like that’s never happened before, right?)</p>
<p>When it comes to menus and adjustments, you’ll be in hog heaven. Kramer has included just about every adjustment you could imagine, taking full advantage of the IDT HQV video processor. Not only does that mean top-notch de-interlacing and 3:2 motion correction, but it also places image warping and rotation tweaks at your fingertips. These are extremely handy settings when you are mounting a projector off-center or at a severe angle to the screen.</p>
<p>The Input menu lets you configure the four universal video jack sets to accept composite, S-video, or component signals. You can also set the video standard (NTSC, NTSC 4.43, PAL, SECAM or Auto modes), fine-tune the horizontal and vertical image position for RGB signals, and play with frequency and phase to clean up clock errors. There’s also an Auto Image button for fast setup.</p>
<p>The Picture menu is where you’ll make basic image adjustments, along with five steps of output gamma, film/video mode (for detection of 2:2 and 3:2 frame cadences), and three kinds of noise reduction – temporal, mosquito, and block. Surprisingly, these adjustments are grayed-out when viewing content through an HDMI connection, which is where they’d be most needed, as mosquito and block noise are the results of digital image compression.</p>
<p>Kramer has also provided multiple steps of detail, luma transition, and chroma transition enhancement. I’d suggest staying away from these tweaks completely, except with low-resolution composite video such as those you’d see from ½” and ¾” videotape formats. Otherwise, you’ll find up with some weird ringing and edge artifacts around higher-resolution video signals. (Repeat to yourself – HDTV does NOT need edge enhancement…)</p>
<p>The Output menu is where you’ll configure the VGA and HDMI output ports. For your convenience, Kramer has provided 28 pre-programmed settings that start at 640×480 (VGA) and top out at 1680×1050 (UXGA+). Among those choices, you’ll also find eight standard component/HDMI video formats, including 1080p/60, or you can simply set the native HDMI input format to be the output format. (According to Kramer’s technical staff, the VP-729 can actually scale all input signals up to 1920×1200 (WUXGA) resolution, using the Custom menu settings.)</p>
<p>The HDMI output connector can be toggled to operate in full HDMI mode with embedded audio, or in basic DVI mode (video only). Five different aspect ratios are also at your fingertips, including Standard, Letterbox, Anamorphic (stretch), Virtual Wide, and Native (pass-through). A Custom option is also included for your imagination.</p>
<p>The Output menu also gives you access to some of the goodies packed within the HQV processor, including the ability to pan and zoom images horizontally and vertically, or to digitally zoom the entire image from 100 to 450%.</p>
<p>There’s also a Picture In Picture menu where you define PiP mode (overlay, side-by-side, or split screen), choose the Pip source and window size, set the horizontal and vertical position of the PiP window, and turn on or off a colored frame around the window’s edge, with red, green, or blue being the choices.</p>
<p>In the Audio menu, you can toggle between analog and SPDIF (digital) audio inputs and fiddle with input and output volume, bass, treble, balance, and loudness. Kramer has thoughtfully included a user-programmable digital audio delay line, which will help clear up lip-sync errors on large flat panel HDTVs or even fix a problem with digital TV broadcasts. The maximum delay is 340 milliseconds, or you can simply leave it set to Dynamic, which corrects automatically for the video processing chain inside the VP729.</p>
<p>Other menus include Geometry, where you can go crazy with image warping and keystone correction settings; Setup, where you can define and save image profiles in a maximum of eight memory locations, plus lock in frame rates, and Info, where you’ll see a static display of input and output signal information and firmware versions.</p>
<p>Hidden in the Setup menu is the previously mentioned ADVANCED sub-menu. This menu lets you download and store a custom logo from a USB drive, capture a displayed image to internal memory for use as a screen saver or boot-up screen, lock the front panel buttons or save your locked configuration, and define the FREEZE button function to operate alone, or pair it with the audio muting function.</p>
<p>This is also the place to input your own timing rates and create a custom output resolution. Caution – you’ll need to know several image parameters to do so without screwing things up. Otherwise, just stick with the factory definitions.</p>
<p>IN OPERATION</p>
<p>I decided to test the VP-729 with Pioneer’s PRO-111FD 50-inch plasma TV, connecting composite, component, and HDMI outputs from Aurora Multimedia’s V-Tune Pro HDTV tuner. I also hooked up component video signals from an AccuPel HDG-2000 test pattern generator and an Extron VTG-300 pattern generator. A second HDMI signal came from Toshiba’s HD-A2 HD DVD player. (Both HDMI inputs to the VP-729 carried embedded digital audio.)</p>
<p>The VP-729 recognizes input signals very quickly, especially HDMI sources. I selected 1080p/60 output resolution through the HDMI output to drive the Pioneer, after applying a software/firmware update from Kramer’s tech wizards Chris and Tom Kopin. This update from a USB flash drive ensured embedded HDMI audio was always recognized and transported smoothly through the switcher.</p>
<p>In my tests, all analog video sources switched between themselves with a smooth fade-down/fade-up sequence. Kramer calls this process Fade-Thru-Black™ switching, and it works by muting the input audio, then fading the selected video/PC signal to black. Next, a sync/audio switch is made, with the new video/PC source fading up. Audio follows shortly afterwards.</p>
<p>It took two seconds to make a complete analog video transition, with audio active in about three seconds. Switching from analog video to an HDMI source took slightly longer for video, but audio isn’t restored in this mode until nearly five seconds have gone by.</p>
<p>Unlike analog video sources, HDMI signals do not fade up. Instead, they “cut,” which may be a limitation of dealing with HDCP-compliant signals. Switching from analog video to an RGB signal also results in the latter “cutting” onto the screen, not a smooth fade up.</p>
<p>HDMI/HDMI transitions were as fast as analog video, with audio recovering after four seconds. The slowest transition was from analog component to HDMI video. It took about 2.5 seconds for the video to switch and nearly six seconds to hear audio.</p>
<p>During my tests, I lost the HDMI signal from the HD-A2 player completely after about 15 minutes. The player was looping one of the Realta HQV test patterns when I lost sync, and it could only be restored by powering down both the VP-729 and the HD-A2, then re-booting everything. The culprit might have been an older version of HDMI running on the HD-A2, which only has 1080i/30 output capability.</p>
<p>Video image quality was excellent with all inputs. The VP-729 passed both the Video Resolution and Film Resolution loss tests from the Realta HD DVD test disc with flying colors, along with the 3:2 sequence, rotating bars, mixed film and video titles, and variable cadences from the standard Realta HQV DVD.</p>
<p>The K-Storm scaler handles standard-definition video with ease. Expect some softness from sources like composite and S-video, which you can sharpen up using a variety of detail, luma, and chroma edge enhancements. But leave these off when working with HD video signals, which should not need any enhancement.</p>
<p>I’d like to see Kramer open up access to the three noise-reduction processors when switching HDMI signals. Mosquito and block noise artifacts are digital in origin and always the result of excessive video compression, something that digital video often suffers from when it originates from terrestrial, cable, or satellite broadcast systems.</p>
<p>CONCLUSIONS</p>
<p>Kramer’s VP-729 is a winner. It’s just the ticket for affordable seamless switching and scaling. Given HDMI’s inexorable creep into the pro AV market (whether you want it or not), it’s good to see manufacturers responding quickly with compatible interfaces. And a pro install these days is likely to include a few consumer signal sources, like set-top boxes and Blu-ray and upscaling DVD players.</p>
<p>I’m not sure what caused the signal dropout from my HD DVD player, but Kramer has been pretty good about diagnosing these glitches and promptly issuing firmware updates. I’d suggest checking to see if you have the latest firmware before you purchase one of these. If not, the updates are easy enough to load from USB flash drives.</p>
<p><strong>Kramer Electronics</strong></p>
<p><strong>ProScale VP-729 Presentation Switcher/Scaler</strong></p>
<p><strong>MSRP: $1,595</strong></p>
<p><strong>Specifications:</strong></p>
<p>Dimensions: 19” W x 9.3” D x 1RU</p>
<p>Weight: 6.6 lbs</p>
<p>Video Inputs: 4x C/YC/YPbPr universal, 2x 15p VGA, 2x HDMI 1.3</p>
<p>Video Outputs: 1x HDMI 1.3, 1x 15p VGA</p>
<p>Audio Inputs: 4x RCA Stereo, 2x 1/8” mini, 2x HDMI</p>
<p>Audio Outputs: 1x Stereo RCA, 1x coaxial SPDIF, HDMI</p>
<p>Control: DB9 RS232, Ethernet</p>
<p>Supported input resolutions: VGA-UXGA+, WXGA, 480i/p, 576i/p, 720p, 1080i, and custom</p>
<p>Output resolutions: VGA-UXGA+, WXGA, WUXGA (1920×1200), 480p, 576p, 720p 50/60, 1080i 50/60, 1080p 50/60, custom</p>
<p><strong>Available from:</strong></p>
<p>Kramer Electronics USA</p>
<p>96 Route 173 West, Suite 1<br />
Hampton, NJ 08827</p>
<p>(888) 275-6311</p>
<p>www.kramerus.com</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April  8, 2010  1:05 PM</b>
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
			<?=getComments(3733)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3733)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-kramer-proscale-vp729-presentation-switcherscaler-june-2009.php" type="text/javascript" charset="utf-8"></script>
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