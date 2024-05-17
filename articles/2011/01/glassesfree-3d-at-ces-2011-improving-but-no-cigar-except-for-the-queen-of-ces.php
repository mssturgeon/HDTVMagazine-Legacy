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
		AND e.entry_id = 4168";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4168 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4168 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4168";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4168";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES" />
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
	<title>HDTV Magazine - Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES</title>
	<meta name="keywords" content="glasses free, auto stereoscopic, shutter glasses, ality digital, passive polarized, glasses, dfusion, ”, viewing, resolution, free, technology, quality, ces, lcd, display, panel, passive, time, dtv, image, content, stereoscopic, auto, side" />
	<meta name="description" content="Most journalists covered CES’s Smart TV announcements, new panel sizes and models, with backlight LEDs here, there, and everywhere, dozens of tablets, etc. but provide no coverage on the technical detail and depth of what is really difficult to create in the 3D world, and most say will not be ready for many years: auto-stereoscopic (glasses-free) 3D.

So which was the best looking glasses-free large screen 3DTV? Who was the 3D-glasses-free queen of CES? Not Sony’s 56” 4K, or 46” 2K LCD panel prototypes shown with floor platforms that wisely limited the viewing area/angles. Not the 65” and 56” LCD models from Toshiba shown with fixed feet marks on the floor (so you better not move) in a tunnel-type booth that also precluded angled views other than straight to the set. Not even LG. The best looking large screen 3D image without using 3D glasses to my eyes was..." />
	<meta name="title" content="Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Most journalists covered CES’s Smart TV announcements, new panel sizes and models, with backlight LEDs here, there, and everywhere, dozens of tablets, etc. but provide no coverage on the technical detail and depth of what is really difficult to create in the 3D world, and most say will not be ready for many years: auto-stereoscopic (glasses-free) 3D.

So which was the best looking glasses-free large screen 3DTV? Who was the 3D-glasses-free queen of CES? Not Sony’s 56” 4K, or 46” 2K LCD panel prototypes shown with floor platforms that wisely limited the viewing area/angles. Not the 65” and 56” LCD models from Toshiba shown with fixed feet marks on the floor (so you better not move) in a tunnel-type booth that also precluded angled views other than straight to the set. Not even LG. The best looking large screen 3D image without using 3D glasses to my eyes was..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4168', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php">Glasses-Free 3D at CES 2011 - Improving, but no Cigar, Except for the Queen of CES</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 13, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<p>Another CES. Walking miles to attend to 50+ meetings, and hundreds of HD/3D-related visits. <p>I also listened to my beloved hi-end audio at the Venetian demoed with $25,000 vinyl turntables and tube amps, doing their pleasant analog sound miracle in a world of digital-everything. This is the way I started in the early 60s, until transistors and the “ones-and-zeros” transformed the audio and video industry, to say the least.  <p>Interestingly enough, the crowd in the high-end audio gatherings is around my age, retirement age (certainly not the MP3 age), and claim to have golden ears that can still perfectly hear the highest sound frequencies just as they did in their twenties.  <p><span class="caption left" style="width:350px"><img title="LG Display - in support for passive glasses (discont. active-shutter glasses)" alt="LG Display - in support for passive glasses (discont. active-shutter glasses)" src="http://www.hdtvmagazine.us/articles/images/e2533f4f609b_109EB/clip_image002_1eaf2f77-7d5d-4e1b-a6dc-54cd2561fba4.jpg" width="348" height="449"><br />LG Display - in support for passive glasses (discont. active-shutter glasses)</span>CES showed many TVs with 3D and Smart internet/streaming capabilities, including refrigerators (as if anyone needs to browse during the 10 seconds of grabbing a coke), most 3DTVs with active-shutter glasses technology, some 3DTVs with passive polarized passive glasses as I <a href="http://www.hdtvmagazine.com/articles/2010/11/3d-world-2010-conference-in-nyc.php">predicted</a>, like the large LCD panel introduced by Vizio. <p>Although the Vizio passive 3D panel had OK image quality for straight-to-the-screen viewing, its quality degraded considerably when viewed from lateral angles beyond 30 degrees off center, which of course affected the 3D effect as well. What is the plus then? Polarized 3D glasses are relatively cheap compared to active-shutter glasses and some manufacturers think that will help sell more 3DTVs.  <p>In a private press presentation LG Display (not LG Electronics) announced a company decision to switch fully from producing LCD 3D panels for active-shutter glasses to panels for passive polarized glasses, based on a number of health issues documented by their professional/academic research, although wisely omitting a little detail from their presentation: the 50% resolution loss that is characteristic of the passive polarized method. <p>Most journalists covered CES’s Smart TV announcements, new panel sizes and models, with backlight LEDs here, there, and everywhere, dozens of tablets, etc. but provide no coverage on the technical detail and depth of what is really difficult to create in the 3D world, and most say will not be ready for many years: auto-stereoscopic (glasses-free) 3D. <p>So which was the best looking glasses-free large screen 3DTV? Who was the 3D-glasses-free queen of CES? Not Sony’s 56” 4K, or 46” 2K LCD panel prototypes shown with floor platforms that wisely limited the viewing area/angles. Not the 65” and 56” LCD models from Toshiba shown with fixed feet marks on the floor (so you better not move) in a tunnel-type booth that also precluded angled views other than straight to the set. Not even LG. The best looking large screen 3D image without using 3D glasses to my eyes was the one demoed by <a href="http://www.3dfusion.com/">3DFusion</a> in a private viewing at the Stratosphere hotel in Vegas. I dedicated an unusual couple of hours viewing different 3D content and talking to the <a href="http://www.3dfusion.com/team.html">people</a> responsible for this effort (photo). <p><span class="caption left" style="width:404px"><img title="Ilya Sorokin (right), CEO, Stephen K. Blumenthal (center), President, Mark Hooper (left), Chief Scientist, and Alex Braurman  (not in the picture), VP of Business Development" alt="Ilya Sorokin (right), CEO, Stephen K. Blumenthal (center), President, Mark Hooper (left), Chief Scientist, and Alex Braurman  (not in the picture), VP of Business Development" src="http://www.hdtvmagazine.us/articles/images/e2533f4f609b_109EB/clip_image005_5ba318e0-9e47-466a-950d-c4c909857d5f.jpg" width="402" height="199"><br />Ilya Sorokin (right), CEO, Stephen K. Blumenthal (center), President, Mark Hooper (left), Chief Scientist, and Alex Braurman  (not in the picture), VP of Business Development</span>The majority of the 3DFusion technical 3D ASD team is based in Netherlands, Europe, and the company has a corporate office in NYC. When attending CES I generally avoid going to meetings in hotels outside the Las Vegas Convention Center because of the excessive time it takes to reach them (and return to the show), but I made an exception in this case because I knew that what I was about to witness was worth the effort. But I didn’t go alone. While attending a Consortium meeting of industry members at CES, I ran into <a href="http://www.insightmedia.info/aboutus.php?team">Chris Chinnock</a>, Founder and President of <a href="http://www.insightmedia.info/">Insight Media</a> and a key facilitator of the <a href="http://www.3dathome.org/">3D@Home Consortium</a>, and extended a courtesy invitation to him to attend the demo with me.. <p>In November, when I attended the <a href="http://insightmedia.info/emailblasts/2010-11-12_3dworkshop.php">3D University</a> workshop in NYC, Stephen Blumenthal from 3DFusion invited me to view their auto-stereoscopic (glasses free) 3DTVs (22”, 42", 55", and a 128" video wall of 9-LCD tiled panels) in their Wall Street corporate office but my return flight to Washington D.C. did not allow for sufficient time for the demo, so we arranged a private visit at CES, and I am very glad I did. <p>I will cover further this 3DFusion development and their technology in an follow up article but I just wanted to, once again, defuse the negative press claiming that the technology for auto-stereoscopic 3D will take 5 to 10 years to be available. <p>In June 2010 I travelled to Taipei (<a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">Display Taiwan 2010 show</a>) to witness their technical advances in glasses-free 3D, especially AUO’s developments, and the perception was that the technology is coming much sooner than many think. <p>I want to be absolutely clear on the following: I am a supporter of quality imaging, and recognize that 3D is (ab)using the technology plateau the <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">industry has reached</a> after 10 years of HDTV with 1080p and Blu-ray to fit the 3D concept into it, at a price of lower luminance, <a href="http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php">lower resolution</a>, 3D glasses, <a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">over compressed transmission</a>, lower contrast, unsaturated colors, all for the sake of depth, while not telling the whole story to consumers. <p>I consider the 3D technology to be at its beginning phase, and for it to be just a feature within a good quality HDTV for those that may want to occasionally watch a 3D movie, and hope that the 3D transmission and display technology will improve with time so the image quality of 3D could eventually be as good as, or better than, the quality reached by HD and no less than what the 3D cameras recorded at the source. However, I also consider that regular consumers are not as concerned about high image quality as they are about accessing appealing content with online convenience, lower price, and features like Smart TV connected to the Internet for browsing and Netflix without waiting for a disc, Skype, etc. <p>In other words, the setback that 3D entails to the reached plateau of quality HD image and adequate infrastructure may not be of concern to the average consumer that drives huge volume sales and seems to be content with an over-compressed, low-resolution YouTube video on a large 1080p quality panel if the content is of interest, unfortunately for those that strive for quality. <p>While it is true that glasses-free 3D demos over the past couple of years have shown viewing limitations in resolution, size, and viewing zones for a low number of viewers, the fact is that 3DFusion has done a considerable effort on their software engine to process in real time a 3D source to soften, and most of the time completely eliminate, the typical visual breaks viewers experience when moving between viewing zones to change their sitting position or when moving their heads, which is known to disturb the 3D effect. <p><span class="caption right" style="width:314px"><img title="3DFusion - 3D glasses-free LCD 42” panel" alt="3DFusion - 3D glasses-free LCD 42” panel" src="http://www.hdtvmagazine.us/articles/images/e2533f4f609b_109EB/clip_image008_c1dc1b17-83cf-474e-8ecf-6552865d48a4.jpg" width="312" height="215"><br />3DFusion - 3D glasses-free LCD 42” panel</span>In the case of the 42” 3D LCD shown by 3DFusion at CES 2011, the visual breaks between viewing zones were almost imperceptible and I was able to change positions freely in front of the 3DTV panel maintaining a uniform 3D viewing even at wide lateral angles, a typical weakness of LCD panels even with HD. Although CES did not show the variety of 3D without glasses TVs I was hoping to see, I was glad to see Sony’s and Toshiba’s demos, but they did not compare to the viewing-zones-change ability of the 3DFusion demo. <p>The 56” Sony 4K set (which was a demo of future technology, no specs, no timeline, no pricing) showed a crisp 3D trailer of racing cars but the viewing was visibly disturbed when changing the viewing position or moving the head (the 46” 2K LCD set degraded the experience even further). The Toshiba’s glasses-free pair of 3DTVs, announced to be available by year-end for an undisclosed price and specs, also offered a 3D viewing experience that could not compete with the quality of the 3DFusion demo. <p>Granted, these demos were prototypes that are expected to improve when their final products are out, but that was the case with 3DFusion as well and the difference was noticeable. How the Sony’s and Toshiba’s 3DTVs would have performed if using the 3DFusion proprietary software to soften the viewing zone breaks? I would assume much better but that is a conjecture, and I’d rather concentrate on facts.  <p>The 3DFusion panel (based on off-the-shelf LCD), the lenticular screen, and the proprietary software were all designed to show 9 zone views of approximately 900x500 pixels of effective resolution per view zone (eye) using a sub-pixel sharing method to offer the best resolution while maximizing the use of the total 1920x1080 pixel resolution of the panel. These numbers will continue to improve according to 3DFusion management (a 47” panel with 1500+ lines (rows) of vertical of resolution is in the works, 1.5x 1080p HD resolution, with 27 views). <p>Regarding original resolution of the 3D source image, it must be highlighted that a passive polarized 3DTV with glasses shows 1920x540 per eye of the original resolution of 1920x1080 of Blu-ray 3D/3D camera recording, but only shows 960x540 per eye of that original resolution (one quarter of it) when displaying 3D content that was broadcasted <a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">side-by-side</a>, regardless of the magic interpolation of created pixels the TV can do to complete the displayed image. <p>Side-by-side is one common 3D format/structure used by DirecTV and Cable with some 3D content, and eventually most probably be used by over-the-air 3D broadcast to maximize the 6 MHz bandwidth limitation of the channel allocation. <p>In other words, the number of pixels of “original resolution” each eye receives on the 3DFusion demo without 3D glasses (900x500) is similar to what each eye receives of the same “original resolution of 1920x1080” from a side-by-side 3D broadcast when displayed on a passive glasses 3DTV (960x540). Would you prefer to view the same original pixels with or without glasses? <p>Stephen Blumenthal declared that “although 3DFusion is definitely interested in manufacturing our own Perfect Picture 3DTV complete ASD platform, from camera capture to 3D display. Not just the display, but the unified 3DFmax meta data based complete television platform and content tools” the company is now making available a proprietary real-time processing software solution that manufacturers can license to facilitate their implementation of better quality glasses-free 3D. Such a processing feature can be incorporated into Blu-ray players, external boxes, or the display itself to reach the same goal. Although the technology can be applied to plasma panels, the company feels that the results will not be as good because of the lower fill rate of plasma displaying 3D images.  <p>Although no price was officially established, informal discussions pointed to a starting $8,000 range until sufficient production volume can be leveraged. When judging an $8,000 price on a 2011 world of $2,000-$3,000 3D active/passive displays that require glasses, one may react negatively to the choice, but it is important to remember the high prices <a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php">during the introduction of HDTV</a> in 1998, with first generation CRT RPTVs in the range of $5,000-$9,000 (56” Toshiba to 64” Pioneer Elite), over-the-air HDTV tuners as high as $3,000 (Pioneer external unit) just to tune ATSC, and 42” plasma panels around the $10,000 range (Fujitsu for example) that were not even in HD resolution. <p>I remember that in detail because I have been involved with HDTV since the 80s and purchased my early-adopter HDTV and tuners as soon as they became available, certainly at prohibitive prices but worth every penny when considering the importance of that first experience of a technology that took so much effort and time to develop by the US television industry. <p>This phase of glasses-free 3D should be evaluated under the same perspective until economies-of-scale kick-in for the glasses-free 3D business model, while the 3D auto-stereoscopic technology improves rapidly on a daily basis. <p>I close with a quote 3DFusion received from a renowned 3D industry leader at a November&nbsp; 12<sup>th</sup> 2010 demonstration of 3DFusion's 42" glasses-free Auto Stereoscopic Display at the <a href="http://3alitydigital.com/">3ality Digital</a> Studio in LA, where 3ality Digital CEO’s <a href="http://3alitydigital.com/2010/04/steve-schklair-2/">Steve Schklair</a> offered the following thoughts on the 3DFusion 3DTV ASD platform:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <p><i>"…this is the best autostereo display I've seen to date"…"For certain commercial applications, the 3D Fusion ASD is ready now, and this technology for the home is a lot closer than I previously thought. Their live 3D camera capture / ASD display, complete with on-the –fly depth adjustments, clearly demonstrates proof of concept.&nbsp; It works.</i>"&nbsp; <p>Stephen Blumenthal of 3DFusion added:&nbsp; "<i>Steve Schklair 's 3ality Digital is a key innovator and leader in 3D Camera&nbsp; imaging,&nbsp; We are interested in exploring with 3ality Digital the possibility of developing new 3D shot strategies designed to&nbsp; support our 3DFMax 3D depth optimization and forward pop.</i>"and he later commented “<i>I believe that 3DFusion is a 3DTV television technology revolution</i>”.&nbsp; <p>Stay tuned for more coverage about this 3D auto-stereoscopic effort of 3DFusion and 3D in general at CES 2011.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 13, 2011  8:37 PM</b>
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
			<?=getComments(4168)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4168)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php" type="text/javascript" charset="utf-8"></script>
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