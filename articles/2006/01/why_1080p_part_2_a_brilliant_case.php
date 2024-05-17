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
		AND e.entry_id = 297";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 297 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 297 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 297";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/01/why-1080p-part-2-a-brilliant-case.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 297";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Why 1080p? - Part 2 - A Brillian(t) Case" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Why 1080p? - Part 2 - A Brillian(t) Case" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Why 1080p? - Part 2 - A Brillian(t) Case" />
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
	<title>HDTV Magazine - Why 1080p? - Part 2 - A Brillian(t) Case</title>
	<meta name="keywords" content="motion adaptive, video processing, pixel pixel, pixel motion, adaptive deinterlacing, brillian, set, video, pixel, fps, fields, sources, even, frames, image, deinterlacing, motion, odd, inputs, generation, quality, lines, adaptive, material, chip" />
	<meta name="description" content="Following with the subject of 1080p, this is the second part of the series of articles about the technology.  Today we will look behind the curtain of how Brillian had implemented their 1080p magic into their recently released LCoS rear projection set." />
	<meta name="title" content="Why 1080p? - Part 2 - A Brillian(t) Case" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Why 1080p? - Part 2 - A Brillian(t) Case" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/01/why-1080p-part-2-a-brilliant-case.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Following with the subject of 1080p, this is the second part of the series of articles about the technology.  Today we will look behind the curtain of how Brillian had implemented their 1080p magic into their recently released LCoS rear projection set." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=297', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/01/why-1080p-part-2-a-brilliant-case.php">Why 1080p? - Part 2 - A Brillian(t) Case</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 23, 2006</b>
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
				<p>Following with the subject of 1080p, this is the second part of the series of articles about the technology.  Today we will look behind the curtain of how Brillian had implemented their 1080p magic into their recently released LCoS rear projection set.</p>

<p>The company recently introduced their 65" 6580iFB 1080p LCoS set, which was slated to become available in 4Q05 and was the only size Brillian was planning to carry in 2005.  Brillian indicated that the video processing was implemented to get to the viewer all the resolution the 1920x1080 chip can promise, even with non-1080p sources.</p>

<p>During July/August of 2005, we held several technical exchanges with Vincent Sollitto (President and CEO), Hope Frank (Vice President of Marketing), and their technical team, continued with some meetings at the HDTV Display Search Conference held in Beverly Hills in late August, and culminated in January 2006 with a visit to their suite at CES to discuss with their engineers.   </p>

<p>Although I have seen the RPTV myself in several opportunities, the following material should not be misinterpreted as my endorsement of the product, or a technical confirmation of some of the statements provided by Brillian.  </p>

<p>The material might be more productive if the reader first becomes familiarized with the basic HDTV concepts of interlace and progressive I covered on other articles and the HDTV Glossary of this magazine; otherwise the information below could be a bit more technical than a casual reader might be comfortable with.  However, the subjects are covered with a tutorial approach, and are intended to help any reader to get acquainted with the concepts surrounding 1080p.   <br />
 <br />
<strong>Upconversion to 1080p</strong><br />
 <br />
This 1080p set displays images at 120 fps; in Brillian's opinion the image quality obtained at that frame rate is much better than just 60 fps, which is typically what most other 1080p sets do.  The video processor does not perform motion adaptation when jumping the frame rate from 60 to 120 fps; Brillian considers it unnecessary.</p>

<p><u>480i (NTSC) Inputs</u>:  Brillian uses pixel-by-pixel motion adaptive deinterlacers with 3:2 cadence detection and compensation combined with advanced low angle interpolation to produce a 720x480p image.  According to Brillian, this conversion process is as good as any in the industry today.  </p>

<p>Brillian then uses the highest quality scaling filters to upscale the image to 1440x1080, preserving the aspect ratio and converting from rectangular to square pixels.  If the user chooses one of the non-standard aspect ratios, the conversion will change to compensate.  For example, widescreen content viewed in the widescreen aspect ratio will be scaled horizontally to 1920, performing a one third stretch and converting from rectangular to square pixels.<br />
 <br />
<u>1080i Inputs</u>:  As many current 1080p HDTV manufacturers do, Brillian treats 1920x1080i video as 1920x540p frames.  According to Brillian, to differentiate its set from the competition and ensure the highest quality 1080p image is presented; Brillian uses a proprietary set of sophisticated scaling filters to vertically scale the 1920x540 fields to 1920x1080.  </p>

<p>As the next generation of image processors become more mature, the next generation 1080p units will incorporate hardware to perform the same high quality pixel-by-pixel motion adaptive deinterlacing on 1080i inputs Brillian currently only uses on 480i inputs.  Brillian stated: "Our next generation of products with pixel-by-pixel motion adaptive deinterlacing of 1080i sources will be brought to market when they are mature and don't cause more issues than they solve."</p>

<p><u>Progressive Inputs</u>:  Brillian accepts the standard 480p and 720p video formats as well as a multitude of PC formats such as VGA, SVGA, XGA, SXGA, and 1080p.  Brillian uses the highest quality scaling filters to convert these images to the 1920x1080 panels with options to preserve the aspect ratio or fill the screen.<br />
 <br />
A note on scaling filters:  Brillian does not use simple interpolation to scale the incoming data to fill its panels.  Interpolation, even the more advanced techniques, can cause loss of detail and in general have uncontrolled effects on the images.  Brillian uses up to 320 tap FIR filters to perform the image resolution conversion.  The use of FIR filters allow for control of the resulting image sharpness, which Brillian provides as its Picture Filter Modes.  Additionally, these scalers are multiregional, allowing for non-linear scaling to execute Brillian's Extended aspect ratios.<br />
 </p>

<p><strong>Deinterlacing Implementation</strong></p>

<p>Brillian does not add special artificial frames not intended by the material authors, however unless the image already comes externally as 60 fps, the set would have no other choice than to create 60 progressive frames from the provided 60 interlaced fields using pixel by pixel motion adaptive deinterlacing (480i).  </p>

<p>Further, if the original material was 24fps from film, then the 60 interlaced fields need to be converted to 60 progressive frames using inverse 3:2 pull-down.  Given such video processing, I questioned if the pixel-by-pixel motion adaptive deinterlacing is also used for the added frames, in addition to the motion adaptation used for joining the fields.</p>

<p> 	They clarified that in their view 1080i deinterlacing is really no different than 480i deinterlacing and follows the same rules or patterns.  Standard video sources (those recorded interlaced) are handled by combining each field with the previous taking into account motion to prevent combing or blurring effects.  </p>

<p>If the 60Hz interlaced source has the following fields A, B, C, D, E, then the process produces progressive frames 1-4 which are 1 (a combination of fields A and B), 2 (a combination of fields B and C), 3 (a combination of fields C and D), 4 (a combination of fields D and E) and so on.  </p>

<p>In some sense, blending these fields together does produce images unique from the original material but motion adaptive deinterlacing should further reduce the artifacts generated by the process.  By how much and if it will be noticeable at all will highly depend on the content.  The result is something close to what would be viewed on a phosphor based monitor where only the lines contained in each of the fields are actively driven and decay while the other lines are driven on the next field.<br />
 <br />
Film sources at 24Hz have progressive frames A, B, C, D.  These sources are converted to 60Hz interlaced formats (like 480i and 1080i) by showing half the lines (odd) of A, then the other half of the lines (even) of A, then the first half of the lines (odd) again of A, then half the lines (even) of B are shown, followed by the other half of the lines (odd) of B, etc.  So the 60Hz fields sequence is A odd, A even, A odd, B even, B odd, C even, C odd, C even, D odd, D even.  </p>

<p>According to Brillian, the proper way to deinterlace this content is to merge the even and odd lines of A to form one progressive scan frame and show it once for each original interlaced field or 3 times for A, C and correspondingly 2 times for B, D.  The de-interlaced 60Hz outcome results in the original film frames being shown A, A, A, B, B, C, C, C, D, D.  </p>

<p>Therefore, 60Hz is always derived without adding unique frames.  Certain frames are repeated for film sources, but they are not altered just repeated.  This ensures that the Brillian image quality remains as the author intended, versus trying to combine the fields from two separate frames of film material, which would create unintended blurry images.</p>

<p>The 1080p set does not do 3:3 video processing to display 72 frames from 24fps sources, but rather upconverts the 24 to 60 fps (Pioneer Elite plasmas are known to have the 72fps capability, more suitable for displaying film based content)</p>

<p><br />
<strong>1080p Acceptance</strong></p>

<p>Brillian reassured that their 1080p set is capable to accept an external 1080p signal on its digital (DVI) input, as 24, 30, or 60 fps.  The set's hardware can support 1920x1080p 24Hz and 30Hz ATSC standards.  This includes the transmission of the video data to the display section without altering the resolution of the 1920x1080p image.  </p>

<p>An accepted 60fps 1080p signal is passed to the display as is without video processing, however, 24fps and 30fps DVI inputs are currently frame rate converted to 60fps using a video buffer with some loss of temporal/spatial resolution pixels due to video processing (about 30%).  Future software upgrades may overcome this performance degradation.  As these sources become readily available, Brillian's software can be upgraded to take full advantage of this hardware path (more on it further down).</p>

<p>The TV's hardware can support 1920x1080p at 24Hz and 30Hz on the VGA and High Definition Component inputs.  However, 60Hz 1920x1080p analog sources will be too fast for the system.  The A/D converter itself is only 140MHz, so the VGA 148.5MHz standard will not run cleanly.  All the circuitry past the A/D converter is fast enough for 1080p 60Hz at 148.5MHz, up to and including the display's pixel matrix.</p>

<p>If the source of the material supports the CEA standard timings for 1080p at 24Hz or 30Hz, the set will be able to display this format.  However, since analog sources are not data enabled like DVI/HDMI, the source needs to provide the correct timing formats or else the data will not be detected and displayed properly.  </p>

<p>It is important to note that although I am very specific on quoting some limitations on the way this set accepts 1080p (because readers looking for that feature deserve honest detail), the fact that the set actually accepts 1080p is putting this set in a very unique class of only a couple of first generation RPTV sets available today.  Brillian has made the effort to provide 1080p inputs on this first generation and that has an important future-proof value that most other manufacturers could not match on their recently released 1080p sets, although some have already announced their plans to provide such feature in the near future. </p>

<p><br />
<strong>Upgradeability</strong></p>

<p>As these 24Hz and 30Hz 1080p sources become more prevalent the Brillian software may need to be updated to support all the nuances of the video timing, but the hardware platform is in place.</p>

<p>Brillian's current thinking is that there are so few devices providing material at this resolution and rate today that it is difficult to predict if they become more common and if the external sources will continue to conform to the standards.  Given this, Brillian said that software updates are available.  </p>

<p>When inquiring about Brillian's plans of software upgradeability for TVs that were purchased with the current software, and how they could investment-protect consumers who buy the first generation 1080p model, the response was: "Brillian provides the new firmware on its website for home service technicians and home installers to access and install for customers who require the upgraded features.  The User's Manuals are also available to support the new firmware on the same web site."</p>

<p>Brillian is working on the next generation video processing for 1080i deinterlacing to 1080p; the company indicated that they have no details as to how future hardware/software solutions for this feature would be implemented in current models, "if" it can be implemented as an upgrade.  <br />
 	</p>

<p><strong>Integrated Tuners, FireWire, ISF, etc.</strong></p>

<p>Although the following items are not necessarily related to the 1080p subject, consumers interested on this 1080p set might want to know how certain features are implemented. </p>

<p>Regarding tuning and connectivity capabilities, Brillian's 1080p set was suited with simple ATSC and Cable QAM on-the-clear tuners to meet basic tuning capabilities.  The CableCARD option was not pursued after an initial effort when finding out of the need to redo both tuner and Card to suit them for bidirectional capabilities, when implemented at a later time.<br />
 <br />
The 1080p set does not have 1394 connections even though the hardware can support it from a design standpoint.  Brillian considered that the integrated basic tuners are not usually what customers of this type of TV use for HD reception, they typically use a Cable or OTA STB, which should have 1394 outputs to facilitate HD external recording (on D-VHS for example), in addition to possibly have integrated HD-DVR capabilities for time-shifting purposes.  The inclusion of 1394 interfaces on the second-generation sets will depend on market demand.<br />
 <br />
Brillian also showed at their CES suite a demo of a technology demonstration of a prototype 65" 1080p set that was actually a monitor configuration with a variety of external video processors showing how each performed 1080i to p deinterlacing.  This concept will offer videophiles the ability to have a true video system of components as audio does today.  Brillian also provided some insight into the performance achievable in future models, they also declared to be happy with the performance of the Silicon Optix chip.</p>

<p>The model that is in production has the ability to perform a wide variety of ISF calibration functions from the user menu (which could also be locked out to avoid accidental changes); there is no need to go to the service menu for the access to that functionality (as with other manufacturers, if they do provide access at all).  Some adjustments include selection of color palette (e.g. PC levels at 0-255 gray shades and TV levels at 16-235), 3 color-temperatures (normal 8500 Kelvin, cool 13000, warm 6500) that are also adjustable, sharpness filters, picture modes for each input, etc.  </p>

<p>All typical menu settings such as contrast, brightness, etc., are set at halfway levels out of the box, as opposed to what many competitors do, usually cranking up the contrast and other settings to impress favorably on fluorescent lighted retail floors; many uninformed consumers continue using those settings at home, not obtaining the best image the set could provide at the home environment.<br />
 <br />
It also features a 200-page user manual I have not seen yet but quoted of exceptional clarity.  Upon purchasing this TV, an ISF (Imaging Science Foundation) technician visit is also included to perform calibration service for two inputs, which typically could run in the range of $300-$500 if hired separately; such feature is certainly an innovation among the competition, and shows that Brillian strives to produce the best quality image the TV could offer to a consumer. </p>

<p><br />
<strong>Brillian Moving Forward</strong></p>

<p>According to Brillian, their sets distinguish themselves from other LCoS 1080p manufacturers in the way they employ an analog drive scheme with their pixel array, giving a much better result with less noise and contouring artifacts than the other digital implementations, such as JVC's DILA.  It's method of uniformity compensation is also unique and ensures even color rendering across the screen in solid images.<br />
  <br />
In the words of Brillian: "Pixelworks has been a good partner.  They have provided us a quality chip-set and base design kit.  Brillian's engineers have invested 2 years to customize the design to extract the distinguishing performance from the system."  Today they have a very capable system, which Brillian said is getting good reviews including Best HDTV of 2005 from several industry experts.  <br />
 <br />
Moving forward to next generation designs, Pixelworks, along with all of the major video processor chip designers offer, will offer new chip sets to support the all-important pixel-by-pixel motion adaptive deinterlacing of 1080i sources.  Brillian continues to evaluate these chip sets, as well as those from other companies, to insure best in-class performance is delivered.  <br />
 <br />
Silicon Optix is one such company under evaluation.  Their market buzz and pixel-by-pixel motion adaptive noise reduction makes Silicon Optix a player to be closely watched, Brillian said.  I have watched them and they have certainly progressed quite well judging by the manufacturers adopting their video processing technology since they introduced to the public their Realta chip at CES 2005, read the details at my HDTV Technology and CES 2005 report available at the pages of this HDTV Magazine.</p>

<p><br />
Make sure you read the next article of this 1080p series, coming soon.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 23, 2006  7:00 AM</b>
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
			<?=getComments(297)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 297)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/01/why-1080p-part-2-a-brilliant-case.php" type="text/javascript" charset="utf-8"></script>
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