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
		AND e.entry_id = 4580";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4580 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4580 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4580";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-508-home-theater-projector-buying-guide.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4580";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide</title>
	<meta name="keywords" content="street price, contrast ratio, home cinema, jvc dla, home theater, price, contrast, street, projector, ratio, see, home, epson, model, full, sxrd, technology, screen, performance, color, our, dla, ila, cinema, jvc" />
	<meta name="description" content="This is our first time doing it, and we&amp;acirc;€™ll admit, it presents some unique challenges, but we did it anyways. This is our first ever HT Guys Projector Buyer&amp;acirc;€™s Guide. Of course no projector is complete on its own, it still needs a screen, and the screen plays a large role in how the projector will perform, but the same could be said of receivers and speakers as well. Instead of lumping them into cost categories, we just picked our fave 6 (couldn&amp;#039;t use that other number due to trademark infringements)." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-508-home-theater-projector-buying-guide.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This is our first time doing it, and we&amp;acirc;€™ll admit, it presents some unique challenges, but we did it anyways. This is our first ever HT Guys Projector Buyer&amp;acirc;€™s Guide. Of course no projector is complete on its own, it still needs a screen, and the screen plays a large role in how the projector will perform, but the same could be said of receivers and speakers as well. Instead of lumping them into cost categories, we just picked our fave 6 (couldn&amp;#039;t use that other number due to trademark infringements)." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4580', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-508-home-theater-projector-buying-guide.php">HDTV and Home Theater Podcast - Podcast #508: Home Theater Projector Buying Guide</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>December  8, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=496&category=Front Projection">Front Projection</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>Home Theater Projector Buyerâ€™s Guide</h3>
<p>This is our first time doing it, and weâ€™ll admit, it presents some unique challenges, but we did it anyways. This is our first ever HT Guys Projector Buyerâ€™s Guide. Of course no projector is complete on its own, it still needs a screen, and the screen plays a large role in how the projector will perform, but the same could be said of receivers and speakers as well. Instead of lumping them into cost categories, we just picked our fave 6 (couldn&#8217;t use that other number due to trademark infringements).</p>
<p><a href="http://www.htguys.com/shop?id=B002W7CW32">Panasonic PT-AE4000U (Street price: $2039)</a><br />
The Panasonic PT-AE line of projectors has long been considered by enthusiasts and owners alike as the gold standard in price vs. performance. The Panasonic PT-AE4000U continues that tradition beautifully. It is an all-around stellar performer with a great price point at just over $2000.</p>
<ul>
<li>LCD Projector</li>
<li>New Red-Rich Lamp</li>
<li>Full HD Optimized Optical System</li>
<li>Pure Contrast Plate Delivers 100,000:1 Contrast</li>
<li>Pure Color Filter Pro for Rich Vibrant Colors</li>
<li>16-Bit Digital Processing &#8211; Faithfully reproduces even subtle hues and brightness variations.</li>
</ul>
<p>See also: <a href="http://www.htguys.com/shop?id=B005SVMPQ8">Panasonic PT-AE7000U (Street price: $3025)</a></p>
<ul>
<li>Better features, better performance (400,000:1 contrast ratio, etc.), 3D</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B004DR2QJE">Epson Home Cinema 8700 UB (Street price: $2140)</a><br />
Epson pushes the envelope with dark, inky blacks at a price mere mortals can afford with the Home Cinema 8700 UB. It will impress you across the board with picture quality and performance, but what really gets you are the black levels. When you canâ€™t tell where the screen starts and the bezel ends, life is good.</p>
<ul>
<li>Deep, dark blacks; crystal-clear detail &#8211; 1080p D7 chip with C2Fine technology</li>
<li>3LCD, 3-chip technology and a contrast ratio up to 200,000:1</li>
<li>State-of-the-art Fujinon lens with a 2.1x zoom ratio</li>
<li>Built-in Silicon Optix HQV Reon-VX processor</li>
</ul>
<p>See also: <a href="http://www.htguys.com/shop?id=B0044UHJWY">Epson Home Cinema 8350 (Street price: $1099)</a></p>
<ul>
<li>Lower cost, great projector but not quite the same black levels</li>
</ul>
<p>See also: <a href="http://www.epson.com/cgi-bin/Store/jsp/Product.do?sku=V11H398020">Epson Home Cinema 5010 (MSRP: $2999)</a></p>
<ul>
<li>3D model, if you want 3D, this is a beast, and a great deal for under $3K</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B004EHLS4W">JVC DLA-X3 (Street price: $3599)</a><br />
If you want top of the line without spending lottery-winner money, check out the JVC DLA-X3. It is the entry level D-ILA projector in the almost current lineup, so you can pick it up for a good price. The new model, the <a href="http://procision.jvc.com/product.jsp?modelId=MODL028963&amp;pathId=140&amp;page=10">DLA-X30BU</a>, looks even more impressive and has a reduced MSRP, but we had trouble actually tracking one down. That should change soon.</p>
<ul>
<li>Remarkable 50,000:1 Native Contrast Ratio</li>
<li>3D Enabled Viewing with 3-chip 0.7-inch 1920&#215;1080 D-ILA devices</li>
<li>Supports Frame Sequential 3D, side-by-side 3D and top-bottom 3D methods</li>
<li>Upgraded 24p capable 120Hz Clear Motion Drive</li>
<li>No Special Screen Needed for 3D Playback</li>
</ul>
<p>See also: <a href="http://procision.jvc.com/product.jsp?modelId=MODL028965&amp;pathId=140&amp;page=10">JVC DLA-X90RBU</a></p>
<ul>
<li>This is the lottery-winner version, an impressive animal, it weighs in at MSRP $11,999</li>
</ul>
<p>See also: <a href="http://www.htguys.com/shop?id=B004RXW824">JVC DLA-HD250 (Street price: $2649)</a></p>
<ul>
<li>Consumer version of the X series, most of the great performance with less of the investment. Â Also not a 3D model.</li>
<li>Full HD D-ILA front projector with 25,000:1 native contrast ratio, three D-ILA devices, 2x motorized zoom lens with motorized focus, HQV Reon-VX video processor, on-screen gamma control, flexible set-up and more</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B005FZZQZY">Sony VPL-HW30ES (Street price: $3169)</a><br />
Ever since we snuck into a secret back room at CES a few years ago to see one of Sonyâ€™s early 1080p SXRD projectors, weâ€™ve been hooked. They just seem to do everything well, not just well, but really, really well. The picture looks so natural itâ€™s uncanny. You will be the envy of all your friends with this model from Sony. And when it comes down to it, isn&#8217;t that what weâ€™re all looking for?</p>
<ul>
<li>1300 ANSI Lumens Brightness</li>
<li>70,000:1 Dynamic Contrast Ratio</li>
<li>240Hz panel drive improves 3D picture</li>
<li>Whisper-quiet fan: Only 22db emitted</li>
<li>Convert standard 2D HD content to 3D</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B0044UF0PM">Mitsubishi HC4000 (Street price: $1085)</a><br />
This is our value model on the list at a price just a hair over $1000. It really comes down to splitting hairs between this one and the similarly priced Epson 8350 mentioned above. In general, the Mitsubishi has a slight edge on color, but the Epson is very smooth and more flexible on placement options. The Mitsubishi would probably just barely beat the Epson in terms of overall video quality, but that assumes you can get it installed just right to get that performance out of it.</p>
<ul>
<li>Native 1080p, HDMI connectivity, Manual Focus &amp; Zoom Lens (Zoom Ratio 1.5:1)</li>
<li>DLP DarkChip3</li>
<li>DDP3021 Full 10-bit panel driver built in for smooth expression of dark gradations</li>
<li>Contrast Ratio: 4,000:1</li>
<li>Color Wheel: 6-segment (RGBRGB),</li>
<li>Brightness/Lumens: 1300 ANSI Lumens,</li>
<li>Decibels: 31dBA (standard mode)</li>
</ul>
<p>See also: <a href="http://www.htguys.com/shop?id=B005E9VV0A">Mitsubishi HC9000D (Street price: $5005)</a></p>
<ul>
<li>A big step up in features, quality and price. Â Also adds 3D.</li>
<li>Silicon Optix ReonVX Processor Next-gen 10-bit chip for fast processing, HQV noise reduction, high quality picture</li>
<li>3D in Full 1080P SXRD technology for amazing contrast, color accuracy &amp; 3D compatibility, maximum input resolution 1920&#215;1200 (scaled to 1920&#215;1080)</li>
<li>1.8X Power Zoom Lens</li>
<li>SXRD technology for high 150,000 contrast ratio and expansive color pallet</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B0034IS0A2">LG Electronics CF181D (Street price: $2029)</a><br />
This might be the one real curve ball on the list. Â LG isnâ€™t a brand that one would expect to find among recommendations for home theater projectors, but the SXRD model stands here on reputation alone. For full disclosure, weâ€™ve never seen the projector first hand. But we have a lot of experience with SXRD technology and it has never let us down. Couple that with the overwhelming number of positive reviews for this unit, and we had to put it on the list. Our goal for the near term is to find a way to see it for ourselves. Â If you have it, we want to hear about it.</p>
<ul>
<li>Delivers very high brightness (1,800 ANSI Lumens)</li>
<li>LCOS; 0.61&#8243; SXRD (120Hz Full HD resolution)</li>
<li>Contrast of 35,000:1</li>
</ul>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2011-12-09.mp3">Download Episode #508</a></p>
<p>&nbsp;</p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>December  8, 2011 10:12 PM</b>
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
			<?=getComments(4580)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4580)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-508-home-theater-projector-buying-guide.php" type="text/javascript" charset="utf-8"></script>
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