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
		AND e.entry_id = 466";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 466 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 466 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 466";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/10/first-products-with-hdmi-13-features-hit-the-market.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 466";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download First Products with HDMI 1.3 Features Hit the Market" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="First Products with HDMI 1.3 Features Hit the Market" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="First Products with HDMI 1.3 Features Hit the Market" />
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
	<title>HDTV Magazine - First Products with HDMI 1.3 Features Hit the Market</title>
	<meta name="keywords" content="hdmi licensing, licensing llc, high definition, silicon image, hdmi specification, hdmi, audio, bit, color, digital, licensing, dolby, llc, consumer, high, anticipated, image, definition, silicon, market, video, deep, technology, support, specification" />
	<meta name="description" content="HDMI Licensing, LLC, the agent responsible for licensing the High-Definition Multimedia Interface&amp;trade; (HDMI&amp;trade;) specification, next week will kick off a series of briefings and technology demonstrations for media in Asia, the United States and Europe, previewing key technologies enabled by HDMI 1.3.

The demonstrations will preview high-definition (HD) video and audio technology that will begin hitting the consumer market in November and continue rolling out in 2007. According to announcements by manufacturers, among the first consumer products with HDMI 1.3 features to reach the market will be the PLAYSTATION&amp;reg;3 (PS3) from Sony Computer Entertainment Inc. in November, the HD-XA2 HD DVD player from Toshiba America Consumer Products, LLC in December, and the EMP-TW1000, a 3LCD 1080p projector from Epson in December." />
	<meta name="title" content="First Products with HDMI 1.3 Features Hit the Market" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="First Products with HDMI 1.3 Features Hit the Market" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/10/first-products-with-hdmi-13-features-hit-the-market.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="HDMI Licensing, LLC, the agent responsible for licensing the High-Definition Multimedia Interface&amp;trade; (HDMI&amp;trade;) specification, next week will kick off a series of briefings and technology demonstrations for media in Asia, the United States and Europe, previewing key technologies enabled by HDMI 1.3.

The demonstrations will preview high-definition (HD) video and audio technology that will begin hitting the consumer market in November and continue rolling out in 2007. According to announcements by manufacturers, among the first consumer products with HDMI 1.3 features to reach the market will be the PLAYSTATION&amp;reg;3 (PS3) from Sony Computer Entertainment Inc. in November, the HD-XA2 HD DVD player from Toshiba America Consumer Products, LLC in December, and the EMP-TW1000, a 3LCD 1080p projector from Epson in December." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=466', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/10/first-products-with-hdmi-13-features-hit-the-market.php">First Products with HDMI 1.3 Features Hit the Market</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 27, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=272&category=Technology">Technology</a></b>
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
				<p class="prtitle">HDMI LICENSING LAUNCHES HDMI 1.3 WORLD TOUR, AS FIRST PRODUCTS WITH HDMI 1.3 FEATURES HIT THE MARKET</p>

<p><b>SUNNYVALE, Calif., Oct. 26, 2006</b> - HDMI Licensing, LLC, the agent responsible for licensing the High-Definition Multimedia Interface&trade; (HDMI&trade;) specification, next week will kick off a series of briefings and technology demonstrations for media in Asia, the United States and Europe, previewing key technologies enabled by HDMI 1.3.</p>

<p>The demonstrations will preview high-definition (HD) video and audio technology that will begin hitting the consumer market in November and continue rolling out in 2007. According to announcements by manufacturers, among the first consumer products with HDMI 1.3 features to reach the market will be the PLAYSTATION&reg;3 (PS3) from Sony Computer Entertainment Inc. in November, the HD-XA2 HD DVD player from Toshiba America Consumer Products, LLC in December, and the EMP-TW1000, a 3LCD 1080p projector from Epson in December.</p>

<p>"Reports from manufacturers indicate that most Blu-ray Disc and HD DVD players, and a substantial proportion of conventional DVD players, will include HDMI 1.3 capabilities in 2007," said Leslie Chard, president of HDMI Licensing, LLC. "During the first half of 2007 we expect to see HDTVs with HDMI 1.3 functionality, allowing them to display Deep ColorTM content. We also expect the introduction during 2007 of HDMI 1.3 technology for PCs, audio-visual receivers and a range of other source and display devices."</p>

<p>In June 2006, the HDMI Founders announced the HDMI 1.3 specification, the most significant upgrade yet in the interface that has become the de facto standard interface for high-definition devices. HDMI 1.3 more than doubles HDMI's bandwidth and adds support for Deep Color technology, a broader color space, new digital audio formats, automatic audio/video synching capability ("lip sync"), and an optional smaller connector for use with portable devices such as digital still cameras and camcorders.</p>

<p>HDMI specifications include both mandatory and optional components. As a result, HDMI Licensing encourages consumers to look for the functionality they want the device to support (Deep Color, specific audio formats, etc.), referring to the manufacturer's product information.</p>

<p>The HDMI 1.3 World Tour will offer the first glimpse of key HDMI 1.3 technologies, including Deep Color and support for new lossless audio formats.</p>

<p><b>Silicon Image Demonstrates Deep Color</b><br />
The HDMI 1.3 World Tour includes one of the first opportunities to see Deep Color. HDMI 1.3 supports 10-bit, 12-bit and 16-bit (RGB or YCbCr) color depths, up from the 8-bit depths in previous versions of the HDMI specification. Silicon Image, Inc., the parent corporation of HDMI Licensing, LLC, will demonstrate new 10-bit color technology side-by-side with existing 8-bit color technology. Silicon Image is currently shipping the VastLane SiI9133 and VastLane SiI9134, the industry's first HDMI 1.3 receiver and transmitter semiconductors used in HDTVs and DVD players.</p>

<p>(Note: HDMI Licensing previously used an alternative naming scheme referring to 10-bit, 12-bit and 16-bit color as 30-bit, 36-bit and 48-bit color, reflecting the bit depth of all three colors (RGB or YCbCr) combined.)</p>

<p>Many filmmakers today digitally record and process motion pictures at greater color depths than consumer home theater equipment has been able to reproduce. Movie studios have had to reduce the color depth of their films for home distribution in order for them to play on consumer equipment. However, the advent of 10-bit digital displays and HDMI 1.3 paves the way for player devices and media that can deliver digital movie and game content in nearly lossless visual form, providing consumers with a level of visual acuity and realism never before available in their homes.</p>

<p>Benefits of Deep Color support include:<br />
<ul><li>Allows HDTVs and other displays go from millions of colors to billions of colors</li><li>Eliminates on-screen color banding, for smooth tonal transitions and subtle gradations between colors</li><li>Enables increased contrast ratio</li><li>Can represent many times more shades between any two colors, and many times more shades of gray between black and white. At 10-bit color depth, four times more shades would be the minimum, and the typical improvement would be eight times or more.</li></ul></p>

<p><b>Dolby Demonstrates HDMI 1.3's Advanced Audio Capabilities</b><br />
The HDMI 1.3 World Tour will offer an inside look at the most advanced audio technologies available for next-generation devices, including high-definition optical disc players, future streaming media devices and audio-video receivers. Extending beyond HDMI's current support for high-bandwidth uncompressed digital audio and lossy compressed formats (such as Dolby&reg; Digital and DTS), HDMI 1.3 adds support for new, lossless compressed digital audio formats Dolby&reg; TrueHD and DTS-HD Master Audio&trade;.</p>

<p>Dolby Laboratories, Inc., will demonstrate Dolby TrueHD and Dolby Digital Plus using a state-of-the-art audio system during the HDMI 1.3 World Tour. Dolby TrueHD is 100% lossless audio and can deliver playback audio performance that is identical to uncompressed PCM at one-half to one-third the bit-rate. The resulting bit-rate savings allows content providers to offer consumers better video performance, additional soundtracks and more features on a disc without compromising audio quality. With Dolby TrueHD, the listener can enjoy a playback experience that is bit-for-bit identical with a studio master or live concert hall recording.</p>

<p>Among the features and benefits of Dolby TrueHD technology:<br />
<ul><li>100% lossless audio, which is capable of delivering high-definition sound that is identical to that of a studio master</li><li>Support for eight channels of playback in HD DVD and Blu-ray formats, which delivers full-range high-definition audio to complement the latest high-definition video</li><li>Advanced data rate support for up to 18 Mbps at 24 bit-rate 192kHz sampling frequency, which offers more realistic sounds and greater dynamic punch, significantly improving the way listeners hear music and dialogue. It also delivers greater frequency response and dynamic range for a better entertainment experience.</li></ul></p>

<p>Dolby Digital Plus is Dolby's next-generation home entertainment audio technology designed for high-definition programming and media. Dolby Digital Plus on next-generation optical media is defined by transcendent fidelity and support for up to 7.1 channels of surround sound, to deliver a rich immersive sound field with deep natural bass performance and enhanced dialogue performance.</p>

<p><b>About HDMI</b><br />
HDMI is the first and only consumer electronics industry-supported, uncompressed, all-digital audio/video interface. By delivering crystal-clear, all-digital audio and video via a single cable, HDMI dramatically simplifies cabling and helps provide consumers with the highest-quality home theater experience. HDMI provides an interface between any audio/video source, such as a set-top box, DVD player, or A/V receiver and an audio and/or video monitor, such as a digital television (DTV), over a single cable.</p>

<p><b>About HDMI Licensing, LLC</b><br />
HDMI Licensing, LLC, a wholly-owned subsidiary of Silicon Image, Inc., is the agent responsible for licensing the HDMI specification, promoting the HDMI standard and providing education on the benefits of HDMI to retailers and consumers. The HDMI specification was developed by Hitachi, Matsushita (Panasonic), Philips, Silicon Image, Sony, Thomson and Toshiba as the digital interface standard for the consumer electronics market. The HDMI specification combines uncompressed high-definition video and multi-channel audio in a single digital interface to provide crystal-clear digital quality over a single cable. For more information about HDMI, please visit www.hdmi.org.</p>

<p><b>Forward-looking Statements</b><br />
This news release contains forward-looking information within the meaning of federal securities regulations. These forward-looking statements include statements related to the anticipated features, benefits, capabilities, implementation and performance of the HDMI standard and the HDMI 1.3 specification, the anticipated timing and benefits of the HDMI 1.3 World Tour, the anticipated development of, availability, market growth and consumer demand for HDMI 1.3 equipped products, and the role of HDMI Licensing, LLC and Silicon Image, Inc. in promoting and meeting such anticipated market growth and consumer demand. These forward-looking statements involve risks and uncertainties, including those described from time to time in the Securities and Exchange Commission (SEC) filings of Silicon Image, Inc., the parent corporation of HDMI Licensing, LLC, that could cause the actual results to differ materially from those anticipated by these forward-looking statements. In particular, the anticipated features, benefits, capabilities, implementation and performance of the HDMI standard and the HDMI 1.3 specification, the anticipated timing and benefits of the HDMI 1.3 World Tour, the anticipated development of, availability, market growth and consumer demand for HDMI 1.3 equipped products, and the role of HDMI Licensing, LLC and Silicon Image, Inc. in promoting and meeting such anticipated market growth and consumer demand, may differ materially from what is currently anticipated. In addition, see the Risk Factors section of the most recent Form 10-K or Form 10-Q filed by Silicon Image with the SEC. Silicon Image assumes no obligation to update any forward-looking information contained in this press release.</p>

<p>###</p>

<p>HDMI&trade;, High-Definition Multimedia Interface&trade; and Deep ColorTM are trademarks or registered trademarks of HDMI Licensing, LLC in the United States and other countries, and are used under license from HDMI Licensing, LLC. All other trademarks and registered trademarks are the property of their respective owners.</p>

<p><br />
<b>Media Contacts:</b><br />
Kasey Holman<br />
Media Relations - HDMI Licensing, LLC<br />
Phone: 408-616-4192<br />
kholman@hdmi.org</p>

<p>Paul Sherer<br />
Ogilvy Public Relations Worldwide<br />
Phone: 415-677-2715<br />
paul.sherer@ogilvypr.com</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 27, 2006 12:29 PM</b>
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
			<?=getComments(466)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 466)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/10/first-products-with-hdmi-13-features-hit-the-market.php" type="text/javascript" charset="utf-8"></script>
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