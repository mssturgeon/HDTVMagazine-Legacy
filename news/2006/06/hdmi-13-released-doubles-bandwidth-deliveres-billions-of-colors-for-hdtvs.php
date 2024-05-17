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
		AND e.entry_id = 392";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 392 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 392 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 392";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/06/hdmi-13-released-doubles-bandwidth-deliveres-billions-of-colors-for-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 392";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs" />
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
	<title>HDTV Magazine - HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs</title>
	<meta name="keywords" content="high definition, hdmi specification, licensing llc, consumer electronics, hdmi licensing, hdmi, audio, digital, high, color, devices, definition, interface, colors, video, consumer, new, specification, licensing, electronics, standard, llc, anticipated, image, display" />
	<meta name="description" content="HDMI Founder companies (Hitachi, Ltd., Matsushita Electric Industrial Co., Ltd. (Panasonic), Royal Philips Electronics, Silicon Image, Inc., Sony Corp., Thomson, Inc. and Toshiba Corp.) today released a major enhancement of the High-Definition Multimedia Interface&amp;trade; (HDMI&amp;trade;) specification, the de facto standard digital interface for high definition consumer electronics. HDMI 1.3 will enable the next generation of HDTVs, PCs and DVD players to transmit and display content in billions of colors with unprecedented vividness and accuracy." />
	<meta name="title" content="HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/06/hdmi-13-released-doubles-bandwidth-deliveres-billions-of-colors-for-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="HDMI Founder companies (Hitachi, Ltd., Matsushita Electric Industrial Co., Ltd. (Panasonic), Royal Philips Electronics, Silicon Image, Inc., Sony Corp., Thomson, Inc. and Toshiba Corp.) today released a major enhancement of the High-Definition Multimedia Interface&amp;trade; (HDMI&amp;trade;) specification, the de facto standard digital interface for high definition consumer electronics. HDMI 1.3 will enable the next generation of HDTVs, PCs and DVD players to transmit and display content in billions of colors with unprecedented vividness and accuracy." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=392', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/06/hdmi-13-released-doubles-bandwidth-deliveres-billions-of-colors-for-hdtvs.php">HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 23, 2006</b>
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
				<div align="center">
<p><br />
<strong>HDMI 1.3 DOUBLES BANDWIDTH, DELIVERS BILLIONS OF COLORS FOR HDTVs</strong></p>
<p>
<strong>High-Definition Multimedia Interface Also Adds Newest Digital Audio Formats, Mini Connector and Lip Sync</strong></p>
<p align="left">SUNNYVALE, Calif., June 22, 2006 &mdash; The seven HDMI Founder companies (Hitachi, Ltd., Matsushita Electric Industrial Co., Ltd. (Panasonic), Royal Philips Electronics, Silicon Image, Inc., Sony Corp., Thomson, Inc. and Toshiba Corp.) today released a major enhancement of the High-Definition Multimedia Interface&trade; (HDMI&trade;) specification, the de facto standard digital interface for high definition consumer electronics. HDMI 1.3 will enable the next generation of HDTVs, PCs and DVD players to transmit and display content in billions of colors with unprecedented vividness and accuracy.
<p align="left">The HDMI 1.3 specification more than doubles HDMI's bandwidth and adds support for Deep Color technology, a broader color space, new digital audio formats, automatic audio/video synching capability ("lip sync"), and an optional smaller connector for use with personal photo and video devices. The update reflects the determination of the HDMI founders to ensure HDMI continues evolving ahead of future consumer demands.</p>
<p align="left">The update arrives at a time of strong momentum for the HDMI standard. HDMI Licensing, LLC today announced that more than 400 makers of consumer electronics and PC products worldwide have adopted HDMI. Market researcher In-Stat expects 60 million devices featuring HDMI to ship in 2006.</p>
<p align="left">"PLAYSTATION&reg;3 will be the most advanced computer platform for enjoying a wide range of entertainment content, including the latest games and HD movies, in the home," said Ken Kutaragi, president and group CEO of Sony Computer Entertainment, Inc. "By introducing the next-generation HDMI 1.3 technology, with its high speed and deep color capabilities, PS3 will push the boundaries of audiovisual quality to the next level of more natural and smoother expression on the latest large flat panel displays."</p>
<p align="left">"HDMI is an established cornerstone for the whole High Definition TV industry and Philips is extremely pleased to see such significant improvements for picture and sound quality with this new version," said Johan van de Ven, CTO and Senior Vice President of  Philips Consumer Electronics. "We look forward to continuing to work with other HDMI Founder companies to extend the scope of HDMI across new devices and applications, while remaining entirely committed to ensuring full backward compatibility with existing products."</p>
<p align="left">With the adoption of Deep Color and the xvYCC color space, HDMI 1.3 removes the previous interface-related restrictions on color selection. The interface will no longer be a constraining pipe that forces all content to fit within a limited set of colors, unlike all previous video interfaces.</p>
<p align="left"><strong>New HDMI 1.3 capabilities include:</strong></p>

<ul>
	<li><div align="justify"><strong>Higher speed:</strong>  HDMI 1.3 increases its single-link bandwidth from 165MHz (4.95 gigabits per second) to 340 MHz (10.2 Gbps) to support the demands of future high definition display devices, such as higher resolutions, Deep Color and high frame rates. In addition, built into the HDMI 1.3 specification is the technical foundation that will let future versions of HDMI reach significantly higher speeds.</div></li>
	<li><div align="justify"><strong>Deep color:</strong> HDMI 1.3 supports 30-bit, 36-bit and 48-bit (RGB or YCbCr) color depths, up from the 24-bit depths in previous versions of the HDMI specification.</div>
		<ul>
			<li><div align="justify">Lets HDTVs and other displays go from millions of colors to billions of colors</div></li>
			<li><div align="justify">Eliminates on-screen color banding, for smooth tonal transitions and subtle gradations between colors</div></li>
			<li><div align="justify">Enables increased contrast ratio</div></li>
			<li><div align="justify">Can represent many times more shades of gray between black and white. At 30-bit pixel depth, four times more shades of gray would be the minimum, and the typical improvement would be eight times or more</div></li>
		</ul>
	</li>
	<li><div align="justify"><strong>Broader color space:</strong>  HDMI 1.3 removes virtually all limits on color selection.</div>
		<ul>
			<li><div align="justify">Next-generation "xvYCC" color space supports 1.8 times as many colors as existing HDTV signals</div></li>
			<li><div align="justify">Lets HDTVs display colors more accurately</div></li>
			<li><div align="justify">Enables displays with more natural and vivid colors</div></li>
		</ul>
	</li>
	<li><div align="justify"><strong>New mini connector:</strong> With small portable devices such as HD camcorders and still cameras demanding seamless connectivity to HDTVs, HDMI 1.3 offers a new, smaller form factor connector option.</div></li>
	<li><div align="justify"><strong>Lip Sync:</strong> Because consumer electronics devices are using increasingly complex digital signal processing to enhance the clarity and detail of the content, synchronization of video and audio in user devices has become a greater challenge and could potentially require complex end-user adjustments. HDMI 1.3 incorporates an automatic audio/video synching capability that allows devices to perform this synchronization automatically with accuracy.</div></li>
	<li><div align="justify"><strong>New lossless audio formats:</strong>  In addition to HDMI's current ability to support high-bandwidth uncompressed digital audio and currently-available compressed formats (such as Dolby&reg; Digital and DTS), HDMI 1.3 adds additional support for new, lossless compressed digital audio formats Dolby&reg; TrueHD and DTS-HD Master Audio&trade;.</div></li>
</ul>

<p align="left">Products implementing the new HDMI specification will continue to be backward compatible with earlier HDMI products.</p>
<p align="left">"The dramatic increase in maximum speed achieved in HDMI 1.3 will enable HDMI to stay far ahead of the bandwidth demands of future high definition source and display devices," said Leslie Chard, president of HDMI Licensing, LLC. "As the de facto standard digital interface for the high definition and consumer electronics markets, HDMI is implementing the most innovative technologies today to fulfill the demands of tomorrow's consumers."</p>
<p align="left">The latest HDMI specification can be downloaded at no cost by visiting www.hdmi.org.</p>
<p align="left"><strong>About HDMI</strong><br />
HDMI is the first and only consumer electronics industry-supported, uncompressed, all-digital audio/video interface. By delivering crystal-clear, all-digital audio and video via a single cable, HDMI dramatically simplifies cabling and helps provide consumers with the highest-quality home theater experience. HDMI provides an interface between any audio/video source, such as a set-top box, DVD player, or A/V receiver and an audio and/or video monitor, such as a digital television (DTV), over a single cable.</p>
<p align="left"><strong>About HDMI Licensing, LLC</strong><br />
HDMI Licensing, LLC, a wholly owned subsidiary of Silicon Image, Inc., is the agent responsible for licensing the HDMI specification, promoting the HDMI standard and providing education on the benefits of HDMI to retailers and consumers. The HDMI specification was developed by Hitachi, Matsushita (Panasonic), Philips, Silicon Image, Sony, Thomson and Toshiba as the digital interface standard for the consumer electronics market. The HDMI specification combines uncompressed high-definition video and multi-channel audio in a single digital interface to provide crystal-clear digital quality over a single cable. For more information about HDMI, please visit www.hdmi.org</p>
<p align="left"><strong>Forward-looking Statements</strong><br />
This news release contains forward-looking information within the meaning of federal securities regulations. These forward-looking statements include statements related to the anticipated growth, market, acceptance and consumer demand for HDMI 1.3 and high definition source and display devices, the anticipated volume of shipments of HDMI devices in 2006, and the benefits, capabilities, performance, evolution, design and implementation of HDMI 1.3, the HDMI standard, and products implementing HDMI. These forward-looking statements involve risks and uncertainties, including those described from time to time in the Securities and Exchange Commission (SEC) filings of Silicon Image, Inc., the parent corporation of HDMI Licensing, LLC, that could cause the actual results to differ materially from those anticipated by these forward-looking statements. In particular, the anticipated growth, market, acceptance and consumer demand for HDMI 1.3 and high definition source and display devices, the anticipated volume of shipments of HDMI devices in 2006, and the benefits, capabilities, performance, evolution, design and implementation of HDMI 1.3, the HDMI standard, and products implementing HDMI, may differ materially from what is currently anticipated. In addition, see the Risk Factors section of the most recent Form 10-K or Form 10-Q filed by Silicon Image with the SEC. Silicon Image assumes no obligation to update any forward-looking information contained in this press release.</p>
<p align="center">###</p>
<p align="left">HDMI&trade; and High-Definition Multimedia Interface are trademarks of HDMI Licensing, LLC in the United States and other countries. All other trademarks and registered trademarks are the property of their respective owners. PLAYSTATION is a registered trademark of Sony Computer Entertainment, Inc.<br /></p>
</div>
<br />
<br />
<strong>Media Contacts:</strong><br />
Kasey Holman<br />
Media Relations - HDMI Licensing, LLC <br />
Phone: 408-616-4192<br />
<a href="mailto:kholman@hdmi.org">kholman@hdmi.org</a><br />
<br />
Paul Sherer<br />
Ogilvy Public Relations Worldwide <br />
Phone: 415-677-2715<br />
<a href="mailto:paul.sherer@ogilvypr.com">paul.sherer@ogilvypr.com</a><br />
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 23, 2006  4:58 AM</b>
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
			<?=getComments(392)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 392)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/06/hdmi-13-released-doubles-bandwidth-deliveres-billions-of-colors-for-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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