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
		AND e.entry_id = 3930";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3930 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3930 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3930";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3930";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &ldquo;Sooner than you think&rdquo; (Part 1)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &ldquo;Sooner than you think&rdquo; (Part 1)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &ldquo;Sooner than you think&rdquo; (Part 1)" />
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
	<title>HDTV Magazine - Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &ldquo;Sooner than you think&rdquo; (Part 1)</title>
	<meta name="keywords" content="auto stereoscopic, display taiwan, image quality, viewing zones, active shutter, glasses, resolution, image, viewing, panel, stereoscopic, auto, taiwan, dtv, display, ”, quality, viewers, auo, technology, show, part, lcd, viewer, home" />
	<meta name="description" content="Display Taiwan 2010 took place recently in Taipei, China. The event entailed a very busy couple of days for me. The reason why I traveled so far from Washington D.C. was because I wanted to witness firsthand what Taiwan was actually doing in the area of large screens 3D auto-stereoscopic (no-glasses 3D). I saw prototypes, commercially available displays, and new developments of advanced technology that made the trip and effort worthwhile." />
	<meta name="title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &amp;ldquo;Sooner than you think&amp;rdquo; (Part 1)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &amp;ldquo;Sooner than you think&amp;rdquo; (Part 1)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Display Taiwan 2010 took place recently in Taipei, China. The event entailed a very busy couple of days for me. The reason why I traveled so far from Washington D.C. was because I wanted to witness firsthand what Taiwan was actually doing in the area of large screens 3D auto-stereoscopic (no-glasses 3D). I saw prototypes, commercially available displays, and new developments of advanced technology that made the trip and effort worthwhile." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3930', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">Auto-stereoscopic 3DTV (3D Without Glasses) - Display Taiwan 2010 Hinted: &ldquo;Sooner than you think&rdquo; (Part 1)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September  1, 2010</b>
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
				<div class="editorial">This article is the first in the "Auto-stereoscopic 3DTV (3D Without Glasses)" series. Other articles in this series are as follows:<br> <ul> <li><a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php" target="_blank">Part 2: Going Backwards in Image Quality for the sake of Depth?</a> </li><li><a href="/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php" target="_blank">Part 3: What else to lose for Stereoscopic 3D?</a></li><!--li><a href="" target="_blank">Part 4: </a> </li--></ul></div><a href="http://www.displaytaiwan.com/">Display Taiwan 2010</a> took place recently in Taipei, China. The event entailed a very busy couple of days for me. The reason why I traveled so far from Washington D.C. was because I wanted to witness firsthand what Taiwan was actually doing in the area of large screens 3D auto-stereoscopic (no-glasses 3D). I saw prototypes, commercially available displays, and new developments of advanced technology that made the trip and effort worthwhile.<br /><div align="center"><img src="http://www.hdtvmagazine.us/articles/images/DSC01041_600.jpg" /></div><br /><h2>Display Taiwan Show</h2> <p>The event was not only about 3D, and I quote from the show itself “<i>Display Taiwan 2010, world's leading trade fair for flat panel display and technology, opens its doors concurrently with Photonics Festival in Taiwan during June 9<sup>th</sup> to 11<sup>th</sup> as the only innovation platform of its kind. </i><a href="http://download.taipeitradeshows.com.tw/2010/display/download/report2010.htm"><i>Over 200 exhibitors amounting to about 550 booths</i></a><i> will again place Taiwan in the pivotal position of FPD industry.”</i> <p><i>“This year's Display Taiwan includes some exciting and significant progress in display imaging and some innovative products. Touch panel and E-paper, with an explosive growth in 2010, … and other key highlights exhibits include 3D, OLED, large-sized displays, and materials and components of the related areas.</i>” <p>“<i>Taiwan is already the main source of worldwide flat panel displays, and is also the crucial market supplier for manufacturing equipment, components, and materials for the worldwide display industry. Display Taiwan 2010, as a channel to connect panel players all over the world, promises to carry out the mission to consolidate Taiwan's top position in global FPD industry</i>.” <p>I visited all the exhibitors, as I do with all the electronics shows. Although this was a smaller venue than CES, which typically requires miles of walking over six days and dozens of meetings, Display Taiwan 2010 was a good opportunity to actually see how technologically advanced Taiwan is in the electronics business of 3D, and more especially auto-stereoscopic 3D.  <p><b></b> <h2>Show and Tell</h2> <p>Several companies showed their advanced 3D products. Some demonstrations were the typical 3DTV implementations with 3D glasses; others were with no-glasses (auto-stereoscopic) lenticular-based screens, with no-glasses glass-based screens suited with fixed parallax barriers for 2, 4, 6 and 8 viewing zones (Unique Instruments Co., Ltd.), 3D panels for the home and for the commercial market, no-glasses multiple projector solutions and LCD screens showing a 3D image within a 2D image (both from the Industrial Technology Research Institute of Hsinchu, Taiwan), and 3D solutions from Chunghwa Picture Tubes, Ltd of Taoyuan, Taiwan, and Champtron Co., Ltd., to mention a few. <p>2010 started with a big push for 3D from electronic manufacturers and content producers/distributors alike, capitalizing from the “Avatar” local theater success and other 3D movies seen by the general public. The common denominator of 3D for the home, as overwhelmingly shown at CES in January, is a “glasses-required” LCD or plasma for 3D viewing, most 3DTVs using active shutter designs, but also some 3DTVs using passive polarized glasses solution (i.e. <a href="http://www.hdtvmagazine.com/articles/2009/10/hd-world-conference-in-ny-3d-ip-online-video-and-mobile-dtv.php">JVC, Hyundai</a>, <a href="http://blogs.consumerreports.org/electronics/2010/06/vizio-polarized-3dtv-expensive-shutter-glasses-2011.html">Vizio</a> and AUO further below)  <p>CES 2010 also showed 3D projectors such as the dual light engine of the new $10K LG DiLA projector, the dual 4K projectors from Sony and JVC, the 3D DLP projectors (i.e. Optoma, although not HD yet), and a couple of technology statements of auto-stereoscopic designs for large audiences in commercial applications, where image quality at high resolution is not the primary objective. <p>However, this show allowed me to view various no-glasses demonstrations of better quality than the ones shown at CES in January. It also allowed me to hold meetings with the 3DTV engineers of small and large companies that designed the products, and to have an open discussion analyzing the technical aspects and limitations of their auto-stereoscopic innovations. <h2>3D without glasses seems a great idea, but look closely</h2> <p>One known criticism from consumers about the 3D that was introduced for the home is the requirement of the glasses and the lack of light in the image. Many look forward to the day when 3DTVs would not use 3D glasses and assume that will restore the light as well. <p>As with the glasses-required 3DTVs the non-glasses solutions bring their own compromises and constraints, some coincident such as the effective reduction in display resolution, some different such as the requirement of viewing from a sweet spot to appreciate the 3D effect, but people still look forward for a near future auto-stereoscopic 3D and <a href="http://gl.ict.usc.edu/Research/3DDisplay/">holography technology</a> at home.  <p>The auto-stereoscopic version of 3D has been prototyped by many companies and universities for many years experimenting with the technology, some with final products, especially small screens. <p>Display Taiwan 2010 showed auto-stereoscopic 3D products in several screen sizes giving evidence that the concept of 3D without glasses in the home is progressing faster than many think, and suggesting that auto-stereoscopic 3D may be available to consumers sooner than the typical 10+ years estimated by many in the press. <p>But first some reality checks are in order. <p>Auto-stereoscopic displays use a variety of methods to deliver 3D images to one or more viewers without using 3D glasses, from lenticular screens with fixed viewing zones that require the viewers to seat in specific sweet spots (and better not move their heads), to parallax-barrier panels designed for a fixed audience of one or more viewers that let their eyes see the specific pixels to portray depth. Many prefer the latter over the lenticular designs, but viewers better stay on the sweet spots as well. Some implemented eye/head tracking technologies for the TV to detect viewer’s movement and still offer acceptable 3D viewing when the eyes move away from the sweet spot. <p>Additionally, a 3D panel, like any other panel, has a limited number of resolution pixels, and one common issue of auto-stereoscopic 3D TVs designed for multiple views is that each view/image shares part of the panel’s total resolution. Two eyes require 2 views/images; more eyes from more viewing locations/people require the display of more views/images. The panel can be a 1080p high resolution panel, but each 3D image could show a YouTube type of resolution quality.  <p>On a panel designed for one single viewer the resolution of each of the two views (one image per eye) could drop to one half, sharing the panel’s resolution, ideal for non-group applications such as a laptop, personal player, phone, etc. However, if the panel was designed to serve a predetermined number of view zones, the resolution per image could drop considerably as the number of view zones increases, dividing the panel resolution horizontally and vertically to display the same image with depth to each and all viewing zones. <p>Having a lower number of pixels illuminating each view lowers the image quality and the brightness at each of the viewing positions, even when actually only one person is viewing from one of the viewing positions; part 2 covers more detail on the subject. However: <p><b></b> <h2>Display Taiwan gave hope </h2> <p>For starters I will give you a teaser. How would you feel about viewing a movie on a large auto-stereoscopic 3DTV screen without using 3D glasses and the TV is smart enough to a) automatically know how many people are viewing the 3D image, and b) dynamically adjust the parallax barriers that separate the various viewing zones/eyes, to c) maximize the resolution of the image shown to each of the 3D viewing zones? <p>In other words, imagine you are alone viewing 3D without glasses receiving the full resolution of the panel (half of the panel pixel resolution for each eye), now your wife steps into the room and starts viewing with you, the TV “notices” the second viewer and, given that the panel resolution has a limited number of pixels, automatically adjusts the way it distributes resolution for you and your wife to receive the best shared resolution for each 3D image. She leaves the room for some popcorn; the TV detects again a single viewer, and adjusts the resolution higher to your image-pair knowing that you are the only one viewing. <p>Does an invention like that get your attention? No glasses smart 3DTV? Not enough? How about a 3840-pixel (double of the typical 1920 HD) resolution so the auto-stereoscopic 3D panel shows each eye’s image at full HD without wearing 3D glasses to the single viewer?  <p>This same company said they are also working on that product and demoed their ultra-wide 58” 21x9 LED widescreen 120Hz panel to show 2.35:1 movies with no black bars (2560x1080). Which company?  <p><a href="http://auo.com/auoDEV/pressroom.php?sec=newsReleases&amp;intTempId=1&amp;intNewsId=802&amp;ls=en">AU Optronics Corporation</a> (AUO) of Hsinchu Science Park in Taiwan, R.O.C. <h2>AU Optronics Corporation </h2> <p>I met with the technical staff from this company. My last meeting was with Mr. Frank Ko Ph.D. Senior Associate Vice President &amp; GM Television Business Group.  <p>At the opening of the show Mr. Ko accepted a “Gold Panel Award” on behalf of his company for their 65” 3DTV (below). After our first few exchanges at the meeting he certainly did not appear to be the typical VP that needs to consult often with technical support to respond with knowledgeable depth, or one that postpones the responses for after the meeting with “we will get back to you”. That made my job easier and the exchanges technically richer and I thank him for that. <p>Mr. Rahul Chopra, Director of the Editorial Department of EFY Enterprises Pvt Ltd in New Delhi, India (<a href="http://www.efyindia.com">www.efyindia.com</a>), a group that handles over 20 publications/events/portals about electronics and computers, was interested in the subject of the meeting and asked us to be part of it, and said jokingly, to receive a “crash course” in 3D. <br /><div align="center"><img src="http://www.hdtvmagazine.us/articles/images/DSC01031_600.png" /></div><p><a href="http://auo.com/auoDEV/pressroom.php?sec=newsReleases&amp;intTempId=1&amp;intNewsId=802&amp;ls=en"><br clear="all" />AUO demoed their 65” LCD/LED</a> 3DTV with pattern retarder for polarized 3D glasses. The set was the largest commercialized 3DTV panel available in June’s Display Taiwan’s show, however, just this week of August Samsung also announced their 65” LCD 3DTV, but it uses active shutter glasses that are sold in a 3D Starter Kit promotion where you get two pairs of 3D active glasses. Samsung currently has three different types of 3D active shutter glasses available, including battery operated, rechargeable, and a version for kids. <p>The 65” AUO LCD set operates with two fields of 540-line interleaved vertical resolution lines of 1920 horizontal pixels to show the two 3D images simultaneously with polarization, that the glasses separate to each eye. This is a similar concept than the professional model from JVC introduced last year with circular polarization. AUO recommends the passive polarized glasses LCD/LED version for the home.  <br /><div align="center"><img src="http://www.hdtvmagazine.us/articles/images/DSC01010_600.png" /></div><br clear="all" />The company also demoed their 65” auto-stereoscopic 3D glasses-free lenticular lens LCD version designed with eight fixed 3D viewpoints having 650 nits of brightness. The resolution each eye would see in 3D is 1/8 of the total 1920x1080 panel resolution (480x540, lower than DVD quality 720h x 480v). The 3DTV showed the same 3D image to the other zones/angles. AUO said that this lenticular model was made for commercial implementation purposes. <p>Other exhibitors also demonstrated auto-stereoscopic displays capable of showing multiple views 3D. One TV with parallax barriers was said to be capable not only to show multiple angles but also to receive images from different 3D angles recorded from multiple 3D cameras to show a different 3D perspective as the viewer changes the position, similar to a <a href="http://gl.ict.usc.edu/Research/3DDisplay">holographic effect</a> but more limited due to using a flat screen rather than volume image technology. <p>As expected, a large crowd was surrounding the 3D sets. The 3DTVs were shown a bit higher than normal so people from the back could also appreciate the auto-stereoscopic image. Typically, increasing the viewing angle of LCD affects image quality, but Mr. Ko commented that the quality on their 3DTV image could still be high with viewing angles up to 45 degrees, and a contrast-ratio in the order of 16,000:1 (measured as full white/full black). AUO said that is working in maintaining high contrast-ratio for viewing angles beyond the 45 degrees. <p>To properly view the demo of the LCD 3DTV with passive glasses there was no other choice than to wait for the 3D glasses of the booth to be available, and I did, but frankly I was not looking for demos of passive/active glasses 3DTVs, CES showed enough of that technology from many manufacturers, I was actually looking for a high quality no-glasses 3D demo of a large image for multiple viewers. Other than the 65” lenticular panel, this event showed smaller versions of the auto-stereoscopic concept with parallax barrier panel designs.  <p>AUO also has a 240Hz panel module that operates in 3D with active-shutter glasses; the company does not produce a plasma 3D. <p><b></b> <h2>AUO’s Smart 3DTV that knows when you are Alone?</h2> <p>As I mentioned on the teaser, the interesting part of AUO’s design is that the 3DTV can detect the number of viewers looking at the image and improve the image quality. This barrier adjustment technology should not be confused with the eye/head tracking technology that follows viewer movement to still offer a good 3D image. <p>If only one person is viewing 3D without glasses the TV would electronically adjust the 3D barriers to deliver the maximum resolution quality of the panel to the viewer (1080 lines of 1920 pixels, 960 per eye). If another person joins the viewing, the 3DTV would adjust the images to share the panel resolution among viewers, the question was how much the resolution is adjusted? <p>One may expect that the automatic share will cut the resolution to another half for each viewer. I tossed such number to AUO, but their response was “not necessarily cutting it to 50%”. <p>In AUO words “the resolution doesn't necessarily drop by half when there's a new member joining as audience. We are working on developing better performance than that. AUO is working continuously on delivering the maximum resolution quality of the panel to viewers.” <p>How can two viewers see a resolution higher than half of what one viewer sees when alone? Are both viewers then allowed to see part of the same pixel grids from different angles and still perceive the correct 3D depth? Because this new technology is in development stage, AUO could not disclose specific numbers on image resolution for multiple viewers, but I requested the information and will share it with readers when available.  <p>Other than the concept discussed at my meeting (and later email exchanges) no announcements were made by AUO regarding time to market of the technology, targeted product, or estimated pricing, although I believe the 65” panel was mentioned. Press-releases about this development were not issued at or after the conference. <p><b></b> <h2>Final Thoughts</h2> <p>Would this technological advance help auto-stereoscopic 3D to soon be the Holy Grail of 3D viewing at home? That is too early to tell at this time, but viewers are not as easy with 3D glasses for traditional home viewing as they are at the local theater with a 3D movie. <p>The auto-detect feature to adjust parallax barriers electronically for auto-stereoscopic viewing is a step in the right direction in dealing with the compromises of resolution and brightness of all the 3D methods, not to mention the 3D-glasses factors of perceived darkness, restricted personal interaction on group viewing, isolation from surrounding environment, individual and group cost of glasses, fragility with children, lack of flexibility for adding more viewers when not having enough glasses, limited compatibility of glasses with other manufacturer’s 3DTVs, restricting the TV upgrade path to same brand, etc.  <p>Display Taiwan 2010 was a good forum to research and analyze this area of the 3D subject, and I appreciate the efforts of the companies working in newer technologies to improve the quality of 3D images so they can be seen the most natural way, as we see life, without 3D glasses. <p>Next in this 3D series:  <a href="/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-going-backwards-in-image-quality-for-the-sake-of-depth-part-2.php">Part 2 – Going backwards in image quality for the sake of depth?</a> <p>Part 3 - “<i>Passive 3D interleaved LCD cutting 1/2 of the original resolution? Would you accept actually loosing 3/4s when viewing some frame-compatible cable/satellite 3D content</i>?” <p>Stay tuned<i>.</i>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September  1, 2010  8:40 AM</b>
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
			<?=getComments(3930)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3930)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php" type="text/javascript" charset="utf-8"></script>
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