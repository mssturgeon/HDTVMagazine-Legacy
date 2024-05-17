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
		AND e.entry_id = 628";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 628 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 628 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 628";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2007/07/benq-w10000-1080p-dlp-front-projector.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 628";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download BenQ W10000 1080p DLP Front Projector" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="BenQ W10000 1080p DLP Front Projector" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="BenQ W10000 1080p DLP Front Projector" />
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
	<title>HDTV Magazine - BenQ W10000 1080p DLP Front Projector</title>
	<meta name="keywords" content="pixel mapping, color decoding, light output, color space, color enhancement, color, pixel, screen, response, light, using, projector, calibration, output, green, isf, benq, black, menu, performance, lamp, most, decoding, feature, real" />
	<meta name="description" content="1080p DLP front projection is slowly starting to dribble into the market but pricing has been high. BenQ is providing the W10000 for the street price of just under $6000, providing a full 1920x1080 DLP Dark Chip3 DMD far closer in price range with other recently released 1080p technology at $5k and below.

As noted, this is a full 1920x1080 chip and does not use wobulation. The advantage here is the potential for a pixel perfect response. Bear in mind this also means pixels are going to be more visible but whether or not that is a problem is a matter of viewing distance. This is a single chip display utilizing an 8 segment color wheel..." />
	<meta name="title" content="BenQ W10000 1080p DLP Front Projector" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="BenQ W10000 1080p DLP Front Projector" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2007/07/benq-w10000-1080p-dlp-front-projector.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="1080p DLP front projection is slowly starting to dribble into the market but pricing has been high. BenQ is providing the W10000 for the street price of just under $6000, providing a full 1920x1080 DLP Dark Chip3 DMD far closer in price range with other recently released 1080p technology at $5k and below.

As noted, this is a full 1920x1080 chip and does not use wobulation. The advantage here is the potential for a pixel perfect response. Bear in mind this also means pixels are going to be more visible but whether or not that is a problem is a matter of viewing distance. This is a single chip display utilizing an 8 segment color wheel..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=628', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/07/benq-w10000-1080p-dlp-front-projector.php">BenQ W10000 1080p DLP Front Projector</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>July  5, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=284&category=HDTV Projectors">HDTV Projectors</a></b>
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
				<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.benq.com/products/Projector/?product=921"><img src="/images/products/benq-w10000.jpg" alt="BenQ W10000" /></a><br /></p>

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid">$9,999.00</td>
<td class="greygrid"><a target="_blank" href="/equipment/model.php?man=BenQ&model=W10000">$5,999.00</a></td>
<td class="greygrid">N/A</td></tr>
</table>
<br />
Serial #PDXB600243TVO<br />
Warranty: 3 years parts and labor<br />
<br />
<B>Summary: Pixel perfect 1080p24 for the videophile performance enthusiast but there's a catch</B><br />
<br />
<p class="editorial">Review Note: Two different firmware versions of the product were reviewed and tested. This review reflects the current version and differences between them are noted when applicable. To update a projector to the current firmware requires an exchange by BenQ. The new version replaces USER 2 and USER 3 presets with ISF Day and ISF Night on the remote and is noted in some portions of the technical section. After the final conclusion, you will find a quick synopsis of the main differences between the two versions.</p>

<p>1080p DLP front projection is slowly starting to dribble into the market but pricing has been high. BenQ is providing the W10000 for the street price of just under $6000, providing a full 1920x1080 DLP Dark Chip3 DMD far closer in price range with other recently released 1080p technology at $5k and below.</p>

<p>As noted, this is a full 1920x1080 chip and does not use wobulation. The advantage here is the potential for a pixel perfect response. Bear in mind this also means pixels are going to be more visible but whether or not that is a problem is a matter of viewing distance. This is a single chip display utilizing an 8 segment color wheel, which also means rainbows are possible. The "rainbow effect", as its known, is a byproduct of using a color wheel to rapidly flash alternating red, green, and blue picture elements to the screen. For a small segment of the population, this can create a visible "rainbow" artifact when projected content features bright objects on a dark background (movie credits, for example). While greatly reduced over the years by adding more sections and increasing rotational speed, they are still perceptible by some viewers.</p>

<p><br />
<strong>Common Features</strong><br />
<ul><li>White cabinet finish</li><li>One HDMI, BNC RGBHV, component RCA, S-video and composite input</li><li>Accepts 480i, 480p, 720p, 1080i, 1080p and that new magic number, 1080p 24 frame</li><li>Serial RS232 port for external control of the projector</li><li>12V trigger output<br />
</li><li>Fairly good size remote that was comfortable in the hand, easy to use and includes an orange backlighting button providing great clarity for reading buttons in the dark</li><li>Aspect controls work with all scan rates providing 4:3 and LETTERBOX screen modes, a non-linear stretch for 4:3 content to fill out your screen called WIDE, ANAMORPHIC to fill out your screen with 720p or SD 16:9 sources</li><li>3 color space presets and 2 grayscale presets</li><li>first version: 3 user presets</li><li>second version: 1 user preset, ISF Day and ISF Night presets</li><li>PIP/POP feature limited to 480i with HD sources only. Does not support two HD sources or two 480i sources</li><li>10 bit digital video processing</li></ul></p>

<p><br />
<B>Not-So-Common Features</B><br />
<ul><li>Remote controlled variable iris (not auto), vertical lens shift, 1.15x zoom and focus<br />
</li><li>BNC for component or RGBHV sharing the BNC connectors</li><li>REAL aspect control for 1X1 pixel mapping of 1080i/p, 720p and 480i/p video sources to the center of the screen or 1080 sources full screen with no overscan</li><li>Color management</li><li>Color decoder adjustments labeled as Enhanced Adjustments</li><li>Auto Phase and frequency adjust or manual</li><li>Internal Patterns: black and white box window, grid</li><li>0 or 7.5 IRE black level offset</li><li>Panamorph Lens compatible supporting anamorphic 2.35 aspect screen applications - not tested</li><li>Two lamp power settings</li></ul></p>

<p><br />
<strong>Missing Features</strong><br />
When you press the power button on the remote or the projector, you will be asked again if you really want to turn it off. If you accidentally proceed you are not given any grace period to change your mind. The projector will have to complete the power down cycle before it will turn on again.</p>

<p><br />
<strong>Out-of-the-box Performance</strong><br />
Using pattern generators and calibration discs, I went through the various combinations of controls and features to find a good looking color temperature and adjusted controls for a proper video setup. I used the projector in this fashion for about 2 weeks. Overall things looked great, but after getting over the initial impact of a lively and highly detailed image, color errors were making themselves more and more visible. The color was, by experience, a bit over the top in brilliance and flesh tones clearly were magenta in hue. Overall the color just felt wrong although easily pleasing to most. This came out in a PC game I was playing at the time because yellow was simply the incorrect hue in some images yet seemed correct in others. Time to see what is going on!</p>

<p><br />
<B>Calibration and Performance</B><br />
Calibration reporting follows a system developed by the ISF Forum called the ISF Display Chart. Some headings provide an embedded link to the ISF Forum for a complete description of the calibration parameter. The key consideration for this part of the review is to ascertain if the necessary controls and features exist to calibrate the display to video standards and/or compensate for source errors. In some cases, a control or feature may not be present even though the product meets that particular qualification for performance.</p>

<p>Pictures are taken with a digital camera, which has limitations of its own inducing artifacts that are not there. The main purpose of the pictures is to provide a reference for the review, regardless of quality, and provide a fair impression of actual performance as compared to other pictures to which they may be compared.</p>

<p><br />
<strong>Optics</strong><br />
I did find an edge to edge focus error on about the last 250 pixels on the left side only, with the rest of the screen spot-on. I was able to achieve an acceptable focus when adjusting for an even response but was surprised by this non-linear error considering the minimal 1.15 zoom feature. This focus error was unseen with video but was quite evident with PC graphics as it relates to fonts, characters and icons. Chromatic RGB convergence errors were minimal with half a pixel of blue on the right and 1 pixel of blue on the left. The chromatic convergence error was invisible at the seating position of 2.8 screen heights.</p>

<p>The focus uniformity problem was brought to the attention of BenQ and the projector was exchanged. The replacement had near perfect focus uniformity with an even response left to right. Extreme left and right were ever so slightly de-focused but even with a PC source, this was a case of splitting hairs.</p>

<p>Back to the zoom for a moment; the 1.15 range is quite small indeed and could be a limitation for some applications. I therefore suggest that you see this as more of a tweak adjustment to set size and overscan for your application, rather than a means for convenient placement or use in a zoomed 2.35 aspect application. If you are considering the W10000 as a replacement projector, then check the online owners manual for screen size and installation logistics to make sure it can fit wherever your present mount is.</p>

<p><br />
<B>Pixel Visibility</B><br />
The definition of pixel visibility has two forms. One is the ability to see the individual pixel response on your screen and is related only to your ability to perceive the full resolution; this is good. The other is your ability see individual pixels on the screen related to the fill factor provided by the technology and that is what we are measuring here.</p>

<p>At 2.8 screen heights none could be found with HD material or from the PC. Even when setting up the projector to fill out my 2.35 aspect screen, none where there. Testing reveals that at about 2 screen heights, pixels begin to become noticeable, so eagle eyed viewers should be safe at 2.5.</p>

<p><br />
<B>1:1 Pixel Mapping (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=82">Definition</a>)</B><br />
The luminance (black and white signal) and chrominance (color signal) frequency response pattern goes out to the full 1920 lines, but it should be noted that 960 lines is the limit of chroma or color detail for the current HDTV system. The only source that can provide 1920 chroma detail is a PC. The left side of the block is 960 line response and the right side 1920 line response.</p>

<p><br />
<B>Component 1080i / HDMI 1080i</B><br />
<img alt="Pixel Mapping" src="/images/reviews/w10000/pixelmapping.jpg" /></p>

<p>The W10000 provided a reference response for this test for both analog component video and HDMI, maintaining similar levels of output for black and white along with color regardless of two-pixel or single-pixel response. If you were here with me you would note a pixel perfect response that can't quite be captured with a camera. While the image obscures the red single pixel response in real life, they are just as bright as the two pixel response. Observed during this test was the perception of the black lines being ever so slightly wider than the white ones, but that was due to the fill factor between pixels adding to the size of black pixels.</p>

<p>The BenQ also has frequency and phase adjustment, either auto or manual, for the analog video inputs for 1:1 pixel mapping. Whether you choose auto or manual adjustment, you need a 1080 pattern that provides a single pixel response like the burst pattern I use to set it properly. On that note I found the auto adjust just as precise as doing it manually. If not set properly, you lose the pixel mapping and get a banding artifact covered in the next section.</p>

<p><br />
<b>Overscan</b><br />
With 1080p and 720p using the REAL aspect ratio setting the projector delivered a pixel perfect 0% overscan. REAL won't fill out your screen for scan rates other than 1080i/1080p and when using the ANAMORPHIC setting instead to fill out your screen the internal scaler does increase overscan to 4.5%. While that seems high, in reality that is not excessive for the average display, but being a front projector I was rather surprised since most have 0-1% overscan under similar circumstances.</p>

<p>If the above is confusing, you are in good company since the centered pixel mapped output of the REAL setting is unique for a consumer display. For the video perfectionist, the REAL aspect ratio setting offers 1:1 pixel mapping. This feature is great for seeing both 1080p and 720p as a straight shot from your source without scaling. What that means is 1080p and 1080i will be full screen but 720p will only occupy the center of the screen using only the 1280X720 pixels within the 1920x1080 panel. Naturally this means the image size decreases as the pixel matrix decreases. Ultimately this makes sense because if you increase the size of the image you also increase the size of the pixels and at some point they will become quite clear. You are then seeing the technology as well as the image, and that is an artifact. With 480p or SD signals this has been mapped as 4:3 640x480 rather than the 16:9 DVD standard of 720x480 which means you lose your 1:1 pixel mapping for 16:9 DVD but retain it for 4:3. It would have been nice to have both options available considering the videophile performance this feature is intended to provide.</p>

<p>Back to ANAMORPHIC. Naturally the projector provides the ability to watch any input scan rate with the screen filled out using ANAMORPHIC, with the penalty of a 4.5% overscan. The W10000 provides a unique opportunity for me to show you just how destructive it is to veer away from 1:1 pixel mapping using 720p as the example. I have included a 720p burst from the Sencore VP403 comparing REAL, 1:1 pixel mapping, with ANAMORPHIC.</p>

<p>TOP: 720p 1:1 pixel mapping, BOTTOM 720p scaled to 1080p<br />
<img alt="720p Scaling" src="/images/reviews/w10000/720pScaling.jpg" /></p>

<p>This was not the best camera shot, and pixel detail is blurred creating the misperception of thick white lines and thinner black ones. Nonetheless, a comparison makes it quite clear how the 1280 response of 720p is riddled with artifacts when remapped to 1920. The perfect transition of one pixel off and one pixel on creating a nice hard edge is replaced by a transition of in-between pixels; intermediate pixels in between peak white and black. Note that numerous white lines in the scaled image for 1280 don't even reach peak white, another clear artifact, and one of the lines in the 640 section is not the same size, being off by one pixel with a different luminance response compared to the lines on either side. At the viewing position where you are seeing the forest rather then the trees, this shows up as banding. All of these errors combined clearly degrade detail response and have the same affect on vertical pixel mapping and detail for the same reasons.</p>

<p>An example of banding is shown and explained in the <a href="/forum/viewtopic.php?t=5217"> Zenith DVD DVB318 review</a> in our HD Library. Scroll down to the high frequency burst image from the DVE calibration DVD; note the text above and the related waveform images.</p>

<p><br />
<b>Special Note About Aspect Ratio Formatting and 1:1 Pixel Mapping</b><br />
The W10000 is unique in how it handles these two concerns. Most front projectors provide the conventional 16:9 aspect setting relying on scan rate to determine when 1:1 pixel mapping should be used and do not apply excessive overscan with non-native scan rates; in this case anything that is not a 1080 scan rate. For the W10000 you have a catch 22 because you only get 1:1 pixel mapping using the REAL aspect setting but that prevents other scan rates from filling out the screen. While you can use ANAMORPHIC to fill out the screen when you return to a 1080 scan rate the screen will still be filled out; will you remember to switch back to REAL with 1080 content for the best performance? This is a real problem for most performance installers and their clients who seek simplicity and ease of use. For many applications, the solution is as simple as leaving the W10000 in REAL mode, setup up all your sources to output 1080 only and use aspect ratio formatting from those sources instead of the projector. Most clients will find this acceptable and watch the occasional content that gets geometrically distorted, such as special features on an SD DVD. Some clients won't accept that and the typical solution is an external scaler. There are myriad ways to overcome this and the key point here was to make the potential buyer aware of this.</p>

<p>I love the REAL aspect feature and BenQ should keep that feature intact! If BenQ would replace ANAMORPHIC with the conventional method employed by other manufacturers for a16:9 aspect ratio, it would be a great step forward for future products.</p>

<p><br />
<B>Gamma (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Definition</a>)</B><br />
The gamma response charts consist of a green line representing the target gamma of 2.2 and a red line representing the response of the display. The average gamma figure only has value when the lines match; otherwise your calibrator will look at the individual steps to identify and correct the problem.</p>

<p>Gamma Pre-calibration<br />
<img alt="Gamma Pre-calibration" src="/images/reviews/w10000/preGamma.jpg" /></p>

<p>Gamma Post-calibration<br />
<img alt="Gamma Post-calibration" src="/images/reviews/w10000/postGamma.jpg" /></p>

<p>Out of the box, the W10000 doesn't do anything wacky with gamma to sell it, as shown by the near-perfect similar response.</p>

<p><br />
<B>Color Temperature and Tracking (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">Definition</a>)</B><br />
A raw 6500 Kelvin response chart may look nice but it does not reflect a specific color. Delta C is provided instead which shows how far off from D65 the response is. The target is less than 1. Less than .5 error is considered quite good approaching a reference response. RGB response charts are included providing a much better understanding of response errors. In a perfect D65 world all three colors would be flat, creating a single line response at 100% for a flawless color temperature and tracking response.</p>

<p>Delta C Pre-calibration<br />
<img alt="Delta C Pre-calibration" src="/images/reviews/w10000/predelta.jpg" /></p>

<p>D65 RGB Chart Pre-calibration<br />
<img alt="D65 RGB Chart Pre-calibration" src="/images/reviews/w10000/preRGB.jpg" /></p>

<p>Delta C Post-calibration<br />
<img alt="Delta C Post-calibration" src="/images/reviews/w10000/postdelta.jpg" /></p>

<p>D65 RGB Chart Post-calibration<br />
<img alt="D65 RGB Chart Post-calibration" src="/images/reviews/w10000/postRGB.jpg" /></p>

<p>Out of the box, pre-calibration, the W10000 has a Delta C error of 2.5 to 5.5 and the RGB chart shows this error is driven towards cyan by increasing both green and blue output over red. Red is the Achilles Heel of all arc lamp based displays as it is the primary with the least amount of light output. All display products have a weak primary so no big deal. What this means for an arc lamp light source is to increase light output for sales and marketing you turn up green and blue since they have more light output to offer which ends up pushing the color temperature towards cyan. This creates a fairly common marginal error for such products and correcting it had marginal impact on light output.</p>

<p>For post-calibration the W10000 provides an excellent response for a consumer display pushing a near reference response except for the bottom end of black exceeding a .5 Delta C error. The RGB chart mirrors this excellent response and subjective viewing showed no obvious errors in the blacks. Errors below one can be difficult to perceive for the layman but is likely to be seen by a colorist professional. If you are doing professional work, this could cause an ever so slight color error that one might be tempted to compensate for when compared to the reference.</p>

<p><br />
<B>Color Decoding (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=78">Definition</a>)</B><br />
Color encoding and decoding for real images creates a complex array of phase angles which can interact. It is possible to have correct color space and incorrect color decoding. Decoding is tested using patterns that provide complex phase angles. For this test, I use the Sencore VP403 Color Decode, SMPTE Color Bars and the Accupel 100% and 75% Color Decoder patterns.</p>

<p>In the service menu for calibration, the BenQ does have blue channel isolation for setting color and tint but does not provide any means to isolate the red and green channels to professionally check color decoding.</p>

<p>This area of response is directly related to the Color Enhancement and, oddly enough, the 3D Color Management sections of the customer menu. Color Enhancement is one of those goofy color processes similar to the Mitsubishi Perfect Color that consumers perceive as useful and calibrators find totally annoying because neither one correctly address color decoding alignments, and using them can easily cause a lot more harm than good. The short version of the harm for the W10000 is that using these adjustments only affects unique phase angles for the color. Where you should have the same color and intensity of yellow within different sections of a color decoding pattern you would see a different response solely due to what other colors are next to it, the complex phase angle of color decoding. For the W10000, I calibrated two different ways; first using Color Enhancement and 3D Color Management to improve the color decoder; and second, ignoring it completely using only 3D color management. Both calibrations produced comparable results. This test became necessary because when calibrating in the service menu, the Color Enhancement controls do not appear and all you have to work with is 3D Color Management. While 3D Color management should only address color space it includes a saturation adjustment that only affects color decoding.</p>

<p>In the end I simply felt better about color decoding when the Color Enhancement controls are available. With HDMI and 1080p capability being the cat's meow, I calibrated HDMI using the customer menu and the analog video component input using the service menu.</p>

<p><br />
<B>Color Space (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=81">Definition</a>)</B><br />
Once color decoding is established, we then address color space. There are various types of color space in the world with the American SMPTE C and European EBU being very similar and specified for standard definition mastering applications and broadcast studio monitoring. The new kid on the block is BT709 for HDTV which slightly expands the color space from standard definition. As to which one you should use, or that I as a reviewer should reference, has been debated heavily. For reviews I will be using HDTV BT709 color space. If the product provides color space management this also infers that you can calibrate for SMPTE-C or EBU if you desire unless stated otherwise.</p>

<p>HDTV BT709 Color Space Pre-calibration<br />
<img alt="HDTV BT709 Color Space Pre-calibration" src="/images/reviews/w10000/precolor.jpg" /></p>

<p>HDTV BT709 Color Space Post-calibration<br />
<img alt="HDTV BT709 Color Space Post-calibration" src="/images/reviews/w10000/postcolor.jpg" /></p>

<p>The W10000 response was a bit surprising for this alignment. Ultimately the color segments on the color wheel are outside of all industry standard color spaces and it is color management that brings them back in, on target. The color wheel creates a green primary way above the green target and some manufacturers leave it that way along with red or blue to differentiate their final image from competitors in the market place. Out of the box, the green veers towards yellow as well as pushing towards the edge of the chart. Using 3D Color Management I was unable to get the green on target. Looking at blue you can see it is slightly off and the controls allowed a precision alignment all around the target but not on target. Because the green can't reach its target, that has an effect on the secondary of cyan, between green and blue, as that is a byproduct of color decoding based on the primaries. I contacted BenQ about this mystery with green and was told that any change to this response will have to wait for the next generation product. Considering the level of performance provided so far, this was very unfortunate as the product is capable and would have allowed the end user to calibrate for any of the industry standard color spaces used around the world.</p>

<p>As with the wacky color processing used by Color Enhancement, the 3D Color Management provided a similar trait by providing color decoder saturation controls for the secondaries which plays absolutely no role in color decoding since their saturation point is a byproduct of proper color decoding for the primaries.</p>

<p><br />
<B>Y/C and RGB Color Timing (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=84">Definition</a>)</B><br />
It can be difficult to define the source of errors related to this element of performance. What we are looking for is a precision alignment of luminance and the color signal as well the individual red, green and blue channels within that color signal. When this is not set properly, edges form between color blocks reducing color definition and creating an artifact with the right images. For this test I use the Accupel color decoding pattern. Images do not reflect accurate color but you can still identify the primary or secondary colors.</p>

<p><img alt=" Y/C and RGB Color Timing" src="/images/reviews/w10000/YCtiming.jpg" /></p>

<p>While the above image appears perfect, note that magenta in the 1st, 3rd and 4th blocks has a one-pixel error when it transitions with green and cyan, creating a 1 pixel darker line very evident in the 1st block. While not reference, this near perfect response was excellent compared to other consumer displays.</p>

<p><br />
<B>Edge Enhancement (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=79">Definition</a>)</B><br />
Out of the box, the display had the notorious outlining of edges, but this was eliminated using the customer controls.</p>

<p><br />
<B>Multi-source Ready (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=83">Definition</a>)</B><br />
The W10000 is limited by having only three presets available, and does not provide different data tables based on scan rate. In most applications this is not going to be very relevant for a product like this but due to the well-aligned nature of the internal scaler, three would cover most applications. While this holds true for the first version, it does not for the second because the ISF Day and Night settings cannot be remembered in the customer menu, only in the service menu. So the second version requires a calibrator to implement these two presets, or service menu access, which consumers are not supposed to have.</p>

<p><br />
<B>Contrast Ratio</B><br />
This measurement is provided only for the purpose of comparison to my other reviews in order to illustrate true contrast ratios using a D65 calibrated color temperature and a 100IRE and 0IRE window pattern. This is a simple measurement of a 0IRE raster and 100IRE window after calibration. The probe is pointed towards the projector and moved towards it until .5fl has been obtained with a 0 IRE raster. Being able to obtain this contrast ratio on your screen will depend on how much of the light reflected off the screen gets reflected back to the screen by your room. This simple measurement does not account for the light in bright areas contaminating the black areas due to projector design and/or technology, which is called intrafield contrast ratio.</p>

<p>With the manual iris wide open, and using a calibrated D65 light output at 168 lamp hours, I obtained 501fl at 100IRE and .447fl for 0IRE yielding a contrast ratio of 1120:1.</p>

<p><br />
<b>Manual Iris</b><br />
The unit provides a manual iris adjustment which provides two benefits. First and foremost, the ability to tune the light output for your application for the best balance of black and peak white. While I could close it up for near CRT black, peak light output suffered. This is a feature I love but it should be noted that an iris cannot improve overall dynamic range and you will get whatever natural dynamic range the technology offers, so consider this more of a brightness or back light feature to match the ambient light in the viewing environment. The other iris benefit is one of intra field contrast ratio. This relates to what happens to the light as it passed through the optics. Optics are not 100% efficient and cause some of that light to scatter, which then enters areas that may be darker. This is where the black and white checker board pattern comes in to play by measuring the light output from the black and white boxes yielding a real world contrast ratio. By turning the iris up so the picture gets darker, less light will enter the optics and that in turn reduces light scatter improving intra field contrast ratios. Taking this measurement is difficult, but I did note a slight improvement by doing so, which was to be expected. In my application I used the iris for better blacks with video content and opened it up all the way for graphics such as gaming and the computer.</p>

<p><br />
<B>Light Output</B><br />
A light meter was not available to provide an accurate or meaningful number. Based on experience, this projector when calibrated had healthy light output similar to most DLPs. 16:9 on my native 2.35 screen works out to 102" using a 1.4 gain and it looked fantastic. Increasing image size to fill out my 2.35 aspect screen yields a 135" 16:9 screen and that still had plenty of life. Gee, and that was with the lamp in low power mode!</p>

<p><br />
<B>Day and Night Settings and ISF CCC</B><br />
Over the last couple of years this has become a new feature in the control menu or on the remote for some displays that changes calibration settings based on ambient room light at the push of a button. Unfortunately, many displays cannot do that and maintain relative accuracy. The W10000 is a surprising exception due to the manual iris and lamp power settings that are part of the user memory function for the ISF Day and Night presets. The difference in light output is significant enough that this projector could be implemented in a dark/medium room or medium/bright room. If this is an application you are seeking for this product, I highly recommend you work with a professional to select the proper screen size and gain along with a calibration of the presets for optimal results. There are only a handful of products that can do dark/bright applications accurately.</p>

<p>The new firmware version supports the ISF CCC interface for calibration and alignment representing the ISF Day and Night preset modes. In the past this feature required an interface for a PC to access it, but the W10000 breaks new ground by allowing access via the service menu which is a huge plus since most calibrators have not had enough demand to justify the additional expense for the interface. Many calibrators have also recommended against using the feature because in past implementations all customer controls were blocked. The W10000 is ground breaking yet again for the ISF CCC system by allowing the user to make adjustments to these presets. Bear in mind doing so does not allow you to change the memory settings and your changes are only active during that particular viewing session which are reset to the reference values input by your calibrator once you cycle the power. This is a huge plus since any change you would want would also be related to the content you are viewing at that time.</p>

<p><br />
<B>Lens Shift (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=87">Definition</a>)</B><br />
The W10000 offers quite a bit of range. The alignment is done via your remote and provides a precision adjustment for this feature. An installer will want to know where center is to reduce optical artifacts if you don't need the feature. This was not available for the first version but is available via the service menu only in the second version with the ISF Day/Night modes.</p>

<p><br />
<B>Noise</B><br />
This was an extremely quiet projector if not the quietest so far for me. Increasing <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=87">lamp power</a> will also increase fan speed but even that remained at a very acceptable level. Throughout testing, the lamp was set for normal.</p>

<p><br />
<B>Light Leakage / Stray Light</B><br />
Due to my application, there was a circular strip of light appearing about 2 feet to the right of the screen. This will not be a big deal though for most users with a dark colored room. Rather than light leakage though, this comes from the reflective finished ring part of the lens housing and could easily be related to excessive lens shift in my application. This light would have to be a byproduct of flares within the optics since the image is clearly not striking this surface. If this is a problem, a strip of black cloth or felt applied to the inside of the ring should resolve it.</p>

<p><br />
<b>DLP Rainbow Artifacts</b><br />
While I saw less of them than I did with my Samsung SPH710, and far less than my Samsung HLP5063, they nonetheless showed up on occasion. This artifact is something I have grown used to and making them show up is a function of image contrast ratio, lots of black with some peak white, and eye movement, meaning your eyes scanning the image up and down or side to side. You cannot see rainbows if you look at the same place on the screen. Note that this artifact is rarely seen by most viewers but there are some folks sensitive to it, which not only see this with DLP but CRT rear projection as well! Screen gain plays a huge role in how visible it can be!</p>

<p><br />
<B>Maintenance</B><br />
Some DLP products have filters while others do not. The reason to have one is because dust builds up on the fan blades and cooling areas reduce cooling efficiency. The engineers at BenQ decided the W10000 should have one. The filter needs to be cleaned/replaced every 1000 hours and under the SETUP menu you will find a counter under DUST FILTER to keep track of this as well as a manual counter reset after you clean it.</p>

<p>Whether it is the lamp or filter that needs access, BenQ has given some thought into making that convenient for you. Both are accessed via the side panels allowing you to leave the projector on the mount, a huge plus. The manual covers this, yet I found myself struggling because neither of them swings out to release them as the manual infers. For the filter there are two plastic locking tabs that you push in then slide the panel towards the bottom maybe a quarter inch and it will release. For the lamp you loosen two screws (with retainers so they don't fall out) and slide it also about a quarter inch towards the bottom and it will release. To be clear, the bottom infers the end with the feet which when mounted on the ceiling would be the top. Now that I know how to do this, it is relatively easy! Mounts tend to be sensitive and enough force is required that you will likely have to center the image once you are done.</p>

<p><br />
<B>Problems</B><br />
About the second week of use, the lamp would strobe or flash intermittently creating a pulsating change in light output. During the review this projector was used in the upright position. This was cured by switching the lamp power to high and back to low. This occurred over a one week period and then stopped acting up. The pulsating appears to be occurring at some time period within the life cycle of the lamp. BenQ suggested sending out another projector for testing but it also suffered the same problem. Based on the three projectors I have worked with I have seen it at about 100 hours and then one time again at about 400. Visibility is directly related to what you are watching. Any still images make it obvious but content with motion and action sequences easily hide it and in the beginning as I was trying to determine what was going on it would also correct itself given enough time.</p>

<p>For the hands on videophile who has no problem driving their equipment using customer controls and menus, it is nothing more than a momentary irritation over a number of days requiring a few keystrokes on the remote. It did not happen everyday but came and went with a mind of its own presenting itself at power-on only; once remedied, the lamp remained stable. It does not appear to have any effect on lamp life. On the flip side if you have a videophile client who is technology challenged and catches this problem, that could be trouble and no amount of reassurance concerning its temporary effect may suffice. That said, the client with the ISF calibration never did notice or call about this problem and his was ceiling mounted so position appears to have no role.</p>

<p>As of the publishing date, I have received no further comment from BenQ on this matter. If they do respond, the article will be updated to reflect that response.</p>

<p><br />
<B>Subjective Viewing Results</B><br />
After calibration, I was greeted with a response I have become accustomed to. When products do well at imaging science they do great with video content. I found all errors resolved and was left with great state of the art imaging revealing of every detail and nuance. The W10000 is very capable at fully expressing the art of film making and television. PC games and graphics were rendered flawlessly.</p>

<p><br />
<B>Conclusion</B><br />
It is clear that BenQ was seeking a videophile imaging science performance pedigree. For mastering and other professional applications the W10000 simply falls short and it is indeed unfortunate that it does not meet imaging science in one area that simply should not even be a problem for such a product; color space. Indeed, considering everything else this projector gets right, it is perplexing how this could have been missed in the final design and production of the product. I would also prefer Color Enhancement be aligned and calibrated at the factory providing at least a reference point for correct color decoding and let the end user play artist if they prefer.</p>

<p><br />
<B>Putting It in Perspective</B><br />
As noted in the subjective viewing results, this machine appears to do everything right with my areas of complaint related far more to specsmanship rather than visual experience. That green primary error hangs like a sore thumb on an otherwise blemish free delivery. The reality is most consumers would be unaware and many a videophile would let this one slide considering the rest of the package. I have enjoyed this projector immensely but ultimately the green error prevents it from being a long term product in my performance world; I want the correct colors.</p>

<p>It doesn't work conveniently for my zoom 2.35 screen application due to the zoom limitations. I am forced to physically move it farther away or closer to the screen. Due to that same limitation I recommend you measure the throw distance if working with a small room or if you plan to install it on an existing ceiling mount; it may not work out or you will have to move your mount. Like most projectors you need to get it perfectly centered left to right since using any keystone correction destroys the all important 1:1 pixel mapping; this feature is disregarded altogether for that reason.</p>

<p>While 1:1 pixel mapping using the REAL aspect is a novel performance feature, most are going to want their images full screen. But if you want the projector to do this, you are going to be forced into some unnecessary overscan using the internal scaler. An external scaler solves that and also adds $1-2K minimum to your budget. Many performance systems already have an external scaler, so this may be a moot point for those upgrading. Another solution is to set your source for one scan rate and have it do the scaling to 1080i/p, but you could be faced with a similar overscan issue. The best solution would have been 0 or near 0 over scan for all content not in 1080. Considering the fact that most front projection systems will have 1-2% overscan applied by the installer to cover up imperfections in installation or the source, I fail to find any valid reason for excessively over scanning content that is not 1080.</p>

<p>I have yet to review reflective LCD technology, so I have no comment other than to say that Greg Rogers did a review on the Sony Pearl VPLVW50 for Widescreen Review and in the technical portion there were some response similarities to our currently reviewed Panasonic PTAE1000U. Both have a reputation based on the dynamic iris technique. At this level of performance, the JVC D-ILA series is another one that should be on your list for consideration.</p>

<p><br />
<B>Final Conclusion</B><br />
DLP technology has come a long way and remains a performance leader with the sharpest image along with some of the best natural dynamic range out there. It can at least do film black and film dynamics without any auto iris or gamma tricks. For the W10000 the manual iris is imaging science frosting on this delicious cake. You can feed it 1080p at 24, 30 or 60 frames acquiring nearly all the benefits of imaging science in all it's glory for $6000, and do as good as, if not clearly better than, your local cinemaplex (excluding IMAX theaters of course.)</p>

<p><br />
<b>W10000 #2</b><br />
Serial#: PVY1700093TVO</p>

<p>Due to the focus uniformity, green primary errors and lamp problem, BenQ sent out a replacement. During the production run, BenQ rearranged a few things on this projector creating two different animals when it comes to calibration. The following additions reflect only those elements that are different between the two units.</p>

<p>For calibration, the ISF CCC system adds 7 gamma settings and separate gamma controls for red, green and blue. The gamma setting preset by BenQ is just fine. Otherwise the ISF CCC system is a duplicate of the user menu minus the Color Enhancement menu. As far as calibration goes there was not any real difference for the end result; both ended up with the same level of performance.</p>

<p>The new version has increased the on screen display graphics to double the size. For the end user this was a good move as the original menu pixel mapped to 1920x1080 providing very small fonts. This was most noted in trying to read the incoming scan rate display at the bottom right corner. The down-side is that the menu now is so large that it covers up areas in the test patterns. This can be worked around but the first version was more convenient for calibrating. This version also allows service menu access unlike the first, and in there I found factory resets for zoom, focus, iris and most importantly lens shift, so it can be returned to center.</p>

<p>The second product also went through a phase of the lamp pulsating light output.</p>

<p><br />
<b>W10000 #3</b><br />
This was a clients unit mounted on the ceiling, so I don't have the serial number, but it had the same problem with pulsating light output.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>July  5, 2007  8:28 AM</b>
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
			<?=getComments(628)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 628)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/07/benq-w10000-1080p-dlp-front-projector.php" type="text/javascript" charset="utf-8"></script>
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