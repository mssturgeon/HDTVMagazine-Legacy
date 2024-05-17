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
		AND e.entry_id = 4242";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4242 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4242 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4242";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-product-review-samsung-pn50c8000-3d-plasma-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4242";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV</title>
	<meta name="keywords" content="movie mode, blu ray, color temperature, plasma tvs, cell light, mode, color, –, plasma, ’s, contrast, movie, brightness, tvs, image, frame, noise, samsung, low, while, cell, black, side, blue, calibration" />
	<meta name="description" content="While LCD TVs dominate the marketplace, they’re not the best way to show 3D on a direct view TV – plasma is. And this review will show you why I think so." />
	<meta name="title" content="HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-product-review-samsung-pn50c8000-3d-plasma-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="While LCD TVs dominate the marketplace, they’re not the best way to show 3D on a direct view TV – plasma is. And this review will show you why I think so." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4242', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-product-review-samsung-pn50c8000-3d-plasma-tv.php">HDTV Expert - Product Review: Samsung PN50C8000 3D Plasma TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March  8, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=511&category=LCD HDTVs">LCD HDTVs</a></b>, <b><a href="/category.php?id=308&category=Marketplace">Marketplace</a></b>, <b><a href="/category.php?id=506&category=Plasma HDTVs">Plasma HDTVs</a></b>
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
				<div class="art-PostContent"><p>Back in October, I had some time to test drive Samsung’s UN46C7000 3D LCD TV. Although it had many strong points, I’m just not a big fan of 3D over LCD, mostly because of the black level and viewing angle issues.</p>
<p>I figured plasma should be a better match to 3D, seeing as that it had the widest viewing angle, bright and contrasty colors, and no issues with the physics of light waves (when you watch 3D on an LCD TV, you are looking through as many as four different polarizers).</p>
<p>Samsung’s <strong>PN50C8000 ($2,599 list)</strong> showed up in early January for a round of testing and is one of the company’s top-line 3D plasma TVs. I paired it with the <strong>BD-C6800</strong> Blu-ray player <strong>(MSRP $250)</strong>, along with a copy of <em>How to Train Your Dragon</em> in 3D. (Thank God, as I was burned out after watching <em>Monsters vs. Aliens</em> umpteen-million times!)</p>
<p> </p>
<p><a rel="attachment wp-att-1118" href="http://www.hdtvexpert.com/?attachment_id=1118"><img class="aligncenter size-full wp-image-1118" title="Samsung-PN50C80001 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-PN50C80001-MR1.jpg" alt="" width="600" height="395" /></a></p>
<p><strong>OUT OF THE BOX</strong></p>
<p>The PN50C8000 is remarkably similar to the UN46C7000 in design, except that it gets a much sturdier base. Once again, the finish around the bezel and on the stand is a shiny silvery color, while I still prefer darker bezels that are less distracting.</p>
<p>I will say that this is extremely lightweight plasma, tipping the scales at 63 pounds with the stand. It wasn’t that long ago that 50-inch plasmas weighed over 100 pounds, and that was without a stand! 63 pounds is <span style="text-decoration: underline;">well</span> inside LCD TV country, so if you were hesitant to buy a plasma TV because of its weight, let that put your concerns to rest.</p>
<p>Samsung has provided plenty of input connections on this TV. There are four HDMI inputs, all of them version 1.4a compatible. Input #1 also supports connections to a personal computer, while Input #2 is the audio return channel (ARC) connection for an external AV receiver.</p>
<p>There’s also a single analog component video (YPbPr) connection, the ‘Y’ (luminance) connection of which doubles as a composite video jack. Unlike the UN46C7000, the PN50C8000 uses full-sized RCA jacks for these two inputs. But there’s a catch – you need to chase down small-diameter RCA connectors to use these connections as they are so close to the rear wall of the plasma TV. The provided F-style RF connector is the normal, threaded type, so leave your adapters at home. There’s enough space around it to screw in a normal F plug.</p>
<p> </p>
<div id="attachment_1112" class="wp-caption aligncenter" style="width: 609px"><a rel="attachment wp-att-1112" href="http://www.hdtvexpert.com/?attachment_id=1112"><img class="size-full wp-image-1112" title="Samsung PN50C8000 Rear" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-PN50C8000-Rear.jpg" alt="" width="599" height="418" /></a><p class="wp-caption-text">Here's what the back end of the PN50C8000 looks like. All of the connectors are on the right side.</p></div>
<p> </p>
<p>All of the HDMI connections support CEC, so when you turn on your Blu-ray player, the TV also powers up and switches to that input automatically. Samsung’s also included a Toslink output jack so you can feed digital audio from TV programs to your AV receiver, but you’ll need to come up with the cable. HDMI input #2 will also provide an audio return path to your receiver.</p>
<p><strong>MENUS AND ADJUSTMENTS</strong></p>
<p>Menu adjustments are very similar to those on the UN46C700, so I’ve retained those descriptions from my earlier review of the UN46C7000.</p>
<p>Samsung’s menus are easy to navigate.  There are six image presets, labeled Dynamic, Standard, Relax, Movie, ISF Day, and ISF Night. Stay away from Dynamic mode, as the pictures are extremely bright and over-enhanced. Standard, Natural, and Movie modes all work well for everyday viewing, but if you are into calibration, you’ll need to use Movie mode. You’ll also find it to be one of the brighter modes. ISF Day and Night modes can’t be adjusted by the average user; only a calibrator can tweak those.</p>
<p>You can select from four different color temperature settings, five different aspect ratio settings, and a host of ‘green’ energy setting modes called Eco Solution. Seeing that this is a plasma TV, you can also adjust cell brightness (separate from black level and contrast) at levels from 0 to 20. Cell brightness has to do with how hard the plasma pixel are driven, and you will see a big change in overall brightness playing with this control. (I set it at 15.)</p>
<p>There is also a screen protection sub-menu that activates pixel orbiting at preset intervals. Or, you can turn on a scrolling feature to rid the screen of any ‘stuck’ images. (It’s just like an electronic Sham-Wow!)</p>
<p>There are other image ‘enhancements’ that Samsung has included, including three different black levels, three settings for dynamic contrast, and a shadow detail enhance/reduce adjustment. My advice is to leave them all off, particularly Black Tone and Auto Contrast.  Generally, these settings mess up gamma performance, and if you are into quality pictures, that’s a must to avoid.</p>
<p>For calibrators, there are two Expert Patterns (grayscale and color) for basic brightness, contrast, saturation, and hue calibrations. You can also select red, green, and blue-only modes, as well as Auto, Native, and Custom color spaces. The Custom mode lets you define your own x,y coordinates for primaries.</p>
<p>For color temperature calibration, Samsung provides two-point and ten-point RGB gain and offset adjustments. The theory is to do most of the calibration in two-point mode, then go back through a multi-step grayscale in ten-point mode for fine-tuning. Other adjustments include Flesh Tone enhance (leave it off), xvYCC mode (leave it off as well, no one currently supports extended color in packaged content), and the usual edge enhancement (peaking) stuff. As I’ve said before, HDTV doesn’t need enhancement!</p>
<p>There are a couple of noise filters that have some effect on image quality. The MPEG noise filter attempts to use low-pass filtering to get rid of mosquito noise and macroblock (excessive compression) artifacts. Be warned that low-pass filtering softens high-frequency image detail, so go easy on these controls. For HD programs, you probably won’t need them, unless you happen to be one of those unfortunate subscribers to U-Verse (720p and 1080i HDTV @ 5 Mb/s looks pretty awful).</p>
<p>Samsung’s Auto Motion Plus corrects for 24-frame judder by pulling the frame rate up to multiples of 60 Hz. In the case of the PN50C8000, the corrected frame rate is probably close to 240 Hz, the same speed at which it operates in 3D mode. What this actually does to images is to make filmed content look like it is live, or shot at video rates.</p>
<p>The result is a very smooth presentation, free of flicker and judder, but it just doesn’t look the same as a movie. The motivation behind Auto Motion Plus (and every other TV manufacturers implementation of it) is to get rid of motion blur and smearing, something that all LCD TVs suffer from to various degrees. Try it – you may like it, you may hate it.</p>
<p>I’d be remiss here in not discussing any of the connected Samsung apps, which let you stream movies and TV shows directly from YouTube, Netflix, and Hulu Plus. While this is a handy feature, don’t expect picture quality to come anywhere close to that of a Blu-ray disc, or even an HDTV channel. Watching Netflix movies over the Internet is more akin to looking at VHS tapes, or composite video from DVDs – the resolution just isn’t there. So use these features more for their convenience than their quality. ESPN updates are also accessible, and if you are addicted to ‘tweeting’ and ‘friending,’ you also have one-touch access to Twitter and Facebook.</p>
<p><strong>3D MENUS</strong></p>
<p>Samsung 3D TVs automatically recognize the HDMI v1.4a frame packing format. This format, which delivers movies at 1920x1080p @24 Hz resolution, is so unique that if you start playing a 3D Blu-ray disc, the PN50C8000 will automatically switch into 3D mode – no further adjustments required.</p>
<p>The two frame-compatible 3D formats (1080i side-by-side and 720p top+bottom) require some help from you to be shown correctly. Once you’ve established that you are indeed seeing the unprocessed 720p or 1080i 3D program from your content provider, go into the PN50C8000’s 3D menu and turn 3D mode ON.</p>
<p>Your will then be presented with a menu of 3D frame compatible formats to choose from, including side by side (1080i) and top &amp; bottom (720p), plus other formats. Aside from frame packing and side-by-side/top &amp; bottom, you are most likely to run into the checkerboard format when playing back 3D games and other non-standard media.</p>
<p>Samsung also has 2D to 3D conversion algorithm built-in to all of their 3D TVs. My advice is not to try and add synthetic 3D effects to everyday TV shows and movies, but stick with content that has been specifically formatted for 3D.</p>
<p><strong>ON THE TEST BENCH</strong></p>
<p>Unlike the UN46C7000 and its persistent auto-dimming feature, I was able to tune up the PN50C8000 quite nicely with basic menu adjustments, plus some assistance from the 10-point white balance menu. I used AccuPel HDG4000 test patterns and ColorFacts 7.5 to perform the measurements.</p>
<p>After my best calibration, I measured brightness in Movie mode at 80 nits (23.4 foot-Lamberts). That number didn’t vary by much, ranging from 73 nits in Relax mode to 83 nits in Movie mode with the Cell Light setting at maximum (20). Why so low?</p>
<p> </p>
<div id="attachment_1113" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1113" href="http://www.hdtvexpert.com/?attachment_id=1113"><img class="size-full wp-image-1113" title="Samsung PN50C8000 Gamma MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-PN50C8000-Gamma-MR.jpg" alt="" width="600" height="324" /></a><p class="wp-caption-text">After calibration, the PN50C8000 produced this beautiful 2.3 gamma curve.</p></div>
<p> </p>
<p>Apparently this plasma TV employs a front-surface vertical polarizing filter to improve black levels and cancel out reflections. It’s an old trick – Pioneer KURO plasma TVs also used it – but it reduces the vertical viewing angle. You can verify this by walking right up to the TV and looking down at the screen; a you get closer, you’ll see image brightness drop off dramatically.</p>
<p>That additional polarizer (or patterned glass filter) reduces overall brightness, too. While 80 nits is plenty at night, it’s a little dim when viewing under high ambient lighting. But there’s only so far you can push image brightness on this TV.</p>
<p>Fortunately, image contrast doesn’t suffer from the additional filtering. ANSI (average) contrast measured 815:1 in Movie mode with cell light set at 15. Boosting cell light ‘to the max’ at 20 kicked that number up to 913:1. Peak contrast in normal cell mode was 939:1, while with maximum cell lighting, it was just shy of 1000:1 (991:1). Black levels measurements were impressive at .09 nits in Movie mode – that’s deep, bro.</p>
<p>White balance uniformity was outstanding. I measured a maximum color temperature shift of 215 degrees Kelvin across a full white field, which is reference monitor performance.  The PN50C8000 also tracks a rock-steady color of gray, varying by just 245 degrees from 20 to 100 IRE.</p>
<p> </p>
<div id="attachment_1114" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1114" href="http://www.hdtvexpert.com/?attachment_id=1114"><img class="size-full wp-image-1114" title="Samsung PN50C8000 Temperature Histogram MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-PN50C8000-Temperature-Histogram-MR.jpg" alt="" width="600" height="325" /></a><p class="wp-caption-text">Color temperature tracking on the PN50C8000 is rock steady.</p></div>
<p> </p>
<p>Gamma performance is also noteworthy. After some tune-up (and disabling auto contrast and black tone), I was able to come up with an almost-perfect 2.3 gamma curve, which emulates the classic CRT gamma response and provides great low-level shadow detail, except for some pulse-width modulation noise.</p>
<p>The RGB histogram shows why. Red, green, and blue track each other very closely from 20 IRE on up to full white, with most of the variation coming in the blue channel. I’ve seen this erratic blue tracking in Panasonic plasma TVs as well and it’s not anything you can correct easily – outboard color gamut and gamma correction hardware and software would run about $6,000, so don’t lose any sleep over it!</p>
<p>Like most plasma TVs, the PN50C8000 has two much cyan in its green phosphors, pulling the color space towards blue for a brighter image. The yellow and blue coordinates are on the money, while cyan is shifted too much towards blue (predictably) and red is a bit over-saturated when compared to the BT.709 standard gamut for HDTV signals.</p>
<p> </p>
<div id="attachment_1115" class="wp-caption aligncenter" style="width: 582px"><a rel="attachment wp-att-1115" href="http://www.hdtvexpert.com/?attachment_id=1115"><img class="size-full wp-image-1115" title="Samsung PN50C8000 CIE Chart" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-PN50C8000-CIE-Chart.jpg" alt="" width="572" height="642" /></a><p class="wp-caption-text">Hre's how the PN50C8000's color gamut compares to the BT.709 HDTV color space (dark outline).</p></div>
<p><strong>IMAGE QUALITY</strong></p>
<p>It doesn’t matter whether you are watching 2D or 3D programming, you will find the pictures this TV produces very pleasing to the eye with excellent color shading and contrast. Those attributes come in real handy when viewing 3D content, especially if you are sitting off-axis. Interestingly, my calibration of the PN50C8000 was the brightest, not to mention very accurate. So I didn’t need to switch out of Movie mode to kick some more photons to the 3D glasses.</p>
<p>Watching <em>How to Train Your Dragon</em> in 3D is a real treat. I thought this was the best 3D movie of 2010, and it was evident that a lot of care went into designing and executing the 3D effects. The flying sequences are just amazing, particularly when Hiccup and Toothless the dragon are swooping and skimming above the ocean, dodging and twisting through rock formations and around cliffs.</p>
<p>In fact, I think it actually looked better on this TV than in the theater (Sony SXRD 4K projector and RealD glasses). Just for fun, I set the TV up in the concessions lobby at the Ambler Theater’s annual Oscars Party (Dragon was nominated for best animated feature and best score, two awards it should have walked away with IMHO) for the 400+ attendees to test-drive. Most of them were predictably wowed by the flying sequences in 3D.</p>
<p>As I mentioned in my review of the UN46C7000, the 3D experience using frame compatible formats isn’t quite as impactful as a 3D Blu-ray disc. The latter format has more detail, more contrast punch, and is just a lot more satisfying to watch. Because the two frame-compatible formats are half-resolution, 3D coverage of sports and other programming – even movies – leaves a bit to be desired.</p>
<p>That unusual low gray noise I mentioned appears to be sub-field sampling noise. It’s evident when playing Blu-ray movies in low-level scenes and on  occasions it can be pretty distracting. Panasonic and LG plasma TVs also exhibit this pulse-width modulation (PWM) noise to varying degrees, but I didn’t notice it as quickly as I did on the PN50C8000. Apparently operating in 1080p/24 mode seems to aggravate it; I didn’t notice it much at all while watching prime time programs in the 720p and 1080i formats. If you spot it, make sure the sharpness control is set to near zero and experiment with the MPEG noise reduction, as that can help minimize this artifact.</p>
<p>My only other negative comment is that you will sometimes notice ghost images on the PN50C8000 after even short periods of operation. It doesn’t matter what brightness level you are running, or even if the display is calibrated – the ghost images still appear when you are showing a dark gray to 50% white screen.  What I’m seeing is not burn-in, as you can turn off the TV, turn it back on, play back different content, and observe an entirely different ghost image.</p>
<p>What I would suggest is to ‘wear in’ the TV when you first get it out of the box – leave a full white test pattern on screen for 200 hours, or use the internal scrolling pattern for the same length of time. That will ‘settle down’ the blue phosphors (which naturally age the fastest) and any subsequent calibration should hold nicely for a long time.</p>
<p><strong>CONCLUSION</strong></p>
<p>Samsung’s PN50C8000 is definitely on the cutting edge of plasma TV design. The performance of this TV (aside from the low-level PWM noise) approaches Pioneer’s late, lamented KURO sets. It is a strong performer with excellent color quality, grayscale shading, and color temperature tracking. You’ll have plenty of contrast and deep, rich black levels to enjoy, even if the overall brightness is on the low side for a consumer TV product.</p>
<p>As far as plasma goes, there’s simply nothing better for viewing 3D – no off-axis contrast flattening or color shift, no crosstalk (common on LCD TVs), and if your head isn’t perfectly level, don’t worry – you won’t see any double images. From my perspective, 3D on a plasma TV comes closest to watching 3D on a DLP Cinema projector of any home theater experience so far.</p>
<p>Full specifications and other product information are available here – <a href="http://www.samsung.com/us/video/tvs/PN50C8000YFXZA" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','www.samsung.com']);">http://www.samsung.com/us/video/tvs/PN50C8000YFXZA</a></p>
<p>Current MAP on this TV is $2,299 as of March 8, 2011.</p>
<p><strong>Power consumption tests</strong> – Over an 8-hour period, the PN50C8000 consumed an average of 205 watts while in Movie mode with full-screen HD and SD content. Cell light was set to 15 and peak brightness was 85 nits.</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March  8, 2011  8:08 AM</b>
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
			<?=getComments(4242)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4242)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-product-review-samsung-pn50c8000-3d-plasma-tv.php" type="text/javascript" charset="utf-8"></script>
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