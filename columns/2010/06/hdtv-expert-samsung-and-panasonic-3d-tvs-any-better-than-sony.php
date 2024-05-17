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
		AND e.entry_id = 3816";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3816 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3816 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3816";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-samsung-and-panasonic-3d-tvs-any-better-than-sony.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3816";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?" />
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
	<title>HDTV Magazine - HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?</title>
	<meta name="keywords" content="figure shows, ghost images, figure figure, glasses tilted, tilted degrees, figure, glasses, screen, shows, degrees, crosstalk, images, samsung, lcd, –, ’s, tilted, angle, polarizers, tvs, view, panasonic, ghost, image, seen" />
	<meta name="description" content="After my off-axis viewing tests on Sony's 3D TVs last week, I figured it was only fair to see just how well the competition performed in the same test." />
	<meta name="title" content="HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-samsung-and-panasonic-3d-tvs-any-better-than-sony.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="After my off-axis viewing tests on Sony's 3D TVs last week, I figured it was only fair to see just how well the competition performed in the same test." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3816', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-samsung-and-panasonic-3d-tvs-any-better-than-sony.php">HDTV Expert - Samsung and Panasonic 3D TVs: Any better than Sony?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>June 28, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>
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
          <p>This past Sunday, I packed up my Sanyo Xacti pistol camera and headed over to a nearby Best Buy store. My goal was to re-run the same off-axis viewing tests that I conducted on a Sony Bravia 3D LCD TV at the CEA Line Shows. Except this time around, my guinea pigs would be Samsung and Panasonic products.</p>
<p>I picked a normal exposure for the correct (level) view and didn’t change it as I rotated the camera and glasses around. This was done so you could see any change in screen brightness.</p>
<p>First up was a Samsung 55-inch LED model. I settled in the comfy chair, pulled out the lone pair of active shutter glasses, and picked a few scenes from Monsters vs. Aliens.</p>
<p>Figure 1 shows a close-up view of the screen through the right eye lens, with the glasses positioned at the correct angle to the screen. No ghost images (crosstalk) were spotted and picture quality was high.</p>
<div id="attachment_618" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-Horizontal-MR.jpg"><img class="size-full wp-image-618" title="Samsung - Horizontal MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-Horizontal-MR.jpg" alt="" width="600" height="423" /></a><p class="wp-caption-text">Figure 1</p></div>
<p>The next image shows the view with the glasses tilted about 30 degrees to the left. No objectionable ghosting here, either, although this particular scene is of a TV weatherman on a ‘flat’ picture tube – not much 3D going on here.</p>
<div id="attachment_619" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-30-degrees-left-MR.jpg"><img class="size-full wp-image-619" title="Samsung - 30 degrees left MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-30-degrees-left-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 2</p></div>
<p>Figure 3 shows the view with the glasses tilted about 60 degrees to the left. The image is noticeably darker now, as the polarizers in the glasses are starting to cancel out the polarized light from the LCD TV screen.</p>
<div id="attachment_620" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-60-Degrees-left-MR.jpg"><img class="size-full wp-image-620" title="Samsung - 60 Degrees left MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-60-Degrees-left-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 3</p></div>
<p>Figure 4 shows – nothing! The glasses are tilted about 80 degrees to the left and the ‘twist’ of polarized light from the LCD screen is canceled out by the polarizing angle of the 3D glasses. Not surprising, considering that two polarizers are being used in the 3D glasses.</p>
<div id="attachment_621" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-80-Degrees-Left-MR.jpg"><img class="size-full wp-image-621" title="Samsung - 80 Degrees Left MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-80-Degrees-Left-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 4</p></div>
<p>These tests don’t mean the Samsung glasses are completely free from ghost images when tilted. Figures 5a and 5b show two different views with the glasses tilted at about 45 degrees to either side, and you can see crosstalk in both images.</p>
<div id="attachment_622" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-crosstalk-45-degrees-left-MR.jpg"><img class="size-full wp-image-622" title="Samsung crosstalk 45 degrees left MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-crosstalk-45-degrees-left-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 5a</p></div>
<div id="attachment_623" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-Crosstalk-45-degrees-right-MR.jpg"><img class="size-full wp-image-623" title="Samsung Crosstalk 45 degrees right MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Samsung-Crosstalk-45-degrees-right-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 5b</p></div>
<p>On to Panasonic! Figure 6 shows the 50-inch plasma screen head-on, as seen through the right lens.</p>
<div id="attachment_624" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-50VT20-FS-MR.jpg"><img class="size-full wp-image-624" title="Panasonic 50VT20 FS MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-50VT20-FS-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 6</p></div>
<p>The next figure shows the same screen with a tilt of about 45 degrees. Picture brightness has dropped a little, but there is no ghosting evident in the image.</p>
<div id="attachment_625" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-45-degrees-left-MR.jpg"><img class="size-full wp-image-625" title="Panasonic 45 degrees left MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-45-degrees-left-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 7</p></div>
<p>Figure 8 shows the screen as seen at a nearly vertical angle, about 80 degrees. Image brightness is still good and there is only a hint of ghosting to be seen (look around St. Peter’s dome). Figure 9 shows the screen 90 degrees to horizontal and it’s still largely free of crosstalk.</p>
<div id="attachment_626" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-80-degrees-left-MR.jpg"><img class="size-full wp-image-626" title="Panasonic 80 degrees left MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-80-degrees-left-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 8</p></div>
<div id="attachment_627" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-90-degrees-r-MR.jpg"><img class="size-full wp-image-627" title="Panasonic 90 degrees r MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Panasonic-90-degrees-r-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Figure 9</p></div>
<p>From these tests. it should be pretty clear that plasma has a big advantage over LCD technology for viewing 3D, and that’s because plasma TVs don’t use polarizers as part of their imaging process. (Anti-glare glass is used, but doesn’t seem to have an adverse effect on 3D viewing angles.)</p>
<p>In contrast, it’s a tricky proposition to pair up polarized glasses with a polarized TV screen, as we’re just seen with Samsung and Sony LCD TVs. Your head really needs to be level to avoid seeing any ghost images.</p>
<p>It appears that the crosstalk problem is worse on Sony’s 3D LCD TVs because they’re only using one polarizer per glass lens (that’s the consensus educated guess). That decision results in images that are brighter, but are ridden with crosstalk – even when the glasses are positioned level to the screen. So there’s no allowance for head tilt  – even slight amounts – with Sony’s approach.</p>
<p>By using two polarizers per lens, Samsung cuts down crosstalk more thoroughly, just at the cost of screen brightness. But you can tilt your head at a greater angle and not be distracted by crosstalk through the glasses.</p>
<p>Panasonic is also using dual polarizers and their images were about as bright as Samsung’s, but nearly free of ghost images when viewed at any angle. If and when OLED-based 3D TVs make it to market, you can expect to see that same level of performance.</p>
<p>So…now you know!</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>June 28, 2010  7:00 AM</b>
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
			<?=getComments(3816)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3816)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-samsung-and-panasonic-3d-tvs-any-better-than-sony.php" type="text/javascript" charset="utf-8"></script>
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