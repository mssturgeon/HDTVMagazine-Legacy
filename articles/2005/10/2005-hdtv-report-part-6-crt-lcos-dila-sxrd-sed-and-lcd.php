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
		AND e.entry_id = 236";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 236 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 236 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 236";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-6-crt-lcos-dila-sxrd-sed-and-lcd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 236";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD</title>
	<meta name="keywords" content="ansi lumens, direct view, hdmi hdcp, integrated atsc, qam cablecard, ttm, integrated, crt, lcd, series, line, models, atsc, rptv, lumens, ansi, hdcp, hdmi, qam, ces, rptvs, tuners, view, direct, dvi" />
	<meta name="description" content="This part 6 details display monitors and integrated TVs using the technologies of CRT, Liquid Crystal On Silicon (LCoS), JVC's D-ILA, Sony's SXRD, Toshiba/Canon SED, and LCD projection displays. All Direct-view, Rear Projection (RPTV), and Front Projection (FPTV) technologies are included.

The parts covering DLP (FPTV and RPTV), LCD-TV panels, and PDP Plasma panel displays will be released shortly.

Please keep in mind that the MSRP and TTM (time to market) information was supplied as of 1Q05 for the CES 2005 report and, as is usual with CE, the prices and availability change upon product release, as well as street sale prices usually differ from the MSRP data used throughout the report." />
	<meta name="title" content="2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-6-crt-lcos-dila-sxrd-sed-and-lcd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This part 6 details display monitors and integrated TVs using the technologies of CRT, Liquid Crystal On Silicon (LCoS), JVC's D-ILA, Sony's SXRD, Toshiba/Canon SED, and LCD projection displays. All Direct-view, Rear Projection (RPTV), and Front Projection (FPTV) technologies are included.

The parts covering DLP (FPTV and RPTV), LCD-TV panels, and PDP Plasma panel displays will be released shortly.

Please keep in mind that the MSRP and TTM (time to market) information was supplied as of 1Q05 for the CES 2005 report and, as is usual with CE, the prices and availability change upon product release, as well as street sale prices usually differ from the MSRP data used throughout the report." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=236', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-6-crt-lcos-dila-sxrd-sed-and-lcd.php">2005 HDTV Report, Part 6: CRT, LCoS, D-ILA, SXRD, SED, and LCD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 15, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p><i>Note: DLP, LCD-TV and PDP Plasma panels are not included in this section</i></p>

<p><br />
<h2>Akai</h2><br />
<u>CES 2005</u><br />
<u>CRT RPTVs Integrated</u><br />
TTM Apr 05, HD component, ATSC tuner but no cable tuner<br />
42'"	PT421QHD	$1000<br />
47"	PT471QHD	$1100<br />
52"	PT521QHD	$1200</p>

<p><br />
<h2>Barco</h2><br />
Oct 04 <br />
<u>LCD FPTV</u><br />
IConH600	1920x1080</p>

<p><br />
<h2>Brillian</h2><br />
<u>CES 2005</u><br />
65"	BR6580 	1080p UltraContrast Gen II LCoS, 2000:1 CR, offered also in 720p, DVI, VGA, HD component, upgradeable software</p>

<p><br />
<h2>Bravo 3M</h2><br />
<u>LCD FPTV</u><br />
S10 LCD projector, pedestal design, 1200 ANSI, whisper mode, 2000 hour lamp life, vertical keystone correction, built-in speaker.</p>

<p><br />
<h2>Canon</h2><br />
<u>LCD FPTVs</u><br />
LV-S3		SVGA (800x600), 4.9 lbs, silent mode 32dB, 1250 ANSI lumens, 1.2X Canon Optical Zoom lens for up to 100" screen from 10.4 feet, ideal for small spaces, progressive scan conversion.</p>

<p>Oct 04<br />
<u>LCoS FPTV</u><br />
LCoS projector (Aspectual Illumination System - AISYS)<br />
Realis SX50	$4000, TTM Nov 04, 1400x1050 SXGA, 2500 ANSI lumens, 1000:1 CR, 8.6 pounds, 1.7 optical zoom lens, 100-inch image from 9.8 feet away, converts 480/575 to 1050 progressive, DVI/HDCP, component, VGA for PC connectivity. </p>

<p>Dec 04<br />
<u>LCD FPTVs</u><br />
LV-7565	$8000, TTM Nov 04, 5100 ANSI, 1000:1 CR, LV-NI01 Network Imager <br />
LV-7565F	$7500, TTM Nov 04, same as above without 1.3x zoom lens</p>

<p><br />
<h2>Crystal View</h2><br />
Dec 04<br />
<u>FPTV</u><br />
CV-1	9-inch CRTs</p>

<p><br />
<h2>Daewoo</h2><br />
Apr 04<br />
<u>CRT RPTVs</u><br />
NTSC tuner, TTM Apr 04, 400:1 CR, 1080i, 600 cd/m2, 5 band-equalizer<br />
47"	DSJ-4710CRA		$1300<br />
55"	DSJ-5510CRA		$1600</p>

<p><u>LCD RPTVs monitors</u><br />
Two piece cabinets, 720p, 5-band equalizer<br />
50"	DJS-5020LN		$4000, TTM 3Q04<br />
60"	DSJ-6000LN		$4500, TTM  current</p>

<p><br />
<h2>Epson</h2><br />
<u>CES 2005</u><br />
<u>LCD RPTVs</u><br />
Living Station line<br />
Similar specs than previous P1 line but now integrated<br />
ATSC/NTSC (no cable) tuners, 1280x720, DVI, component, RGB<br />
47"	LS47P2	$2900 <br />
57"	LS57P2	$3400<br />
Check complete details of the P1 line in 2004 report</p>

<p>57"		1080p prototype version, $4000 estimate, TTM 2006, will be released in a different size than the 57" shown as prototype</p>

<table><tr><td>
<img src="/articles/images/mt/image033.jpg" alt="1080p Prototype" align="left">
</td><td>
<img src="/articles/images/mt/image034.jpg" alt="1080p Prototype" align="right">
</td></tr></table>

<p><u>FPTVs</u><br />
Powerlite line<br />
Cinema 200+		succeeds 200, 1280x720, 1500 lumens (from 1300), 1000: 1 CR (from 800:1), no DIV or HDMI, RGB VGA, YPbPr, D$ for Japan, USB<br />
Cinema 500		continues from last year, check details on 2004 report  <br />
Home 10+		succeeds 10, 800:1 CR (from 700), 1200 lumens, 854x480</p>

<p><br />
<h2>Faroudja</h2><br />
May 04<br />
<u>D-ILA FPTVs</u><br />
Anamorphic lenses, and DVI inputs, paired with either DVP1010 or DVP1050 ($10000) digital video processors<br />
FDP-DILA3 3x0.7 inch chips, 1400x1050 at 800 lumens output<br />
FDP-DILA2 3x0.9 inch chips, 1365x1024 at 100 lumens output</p>

<p><u>CES 2005</u><br />
<u>D-ILA FPTVs</u><br />
FDP-DILA4		3 chips 1400x788, 2.1 to 2.6 manual zoom, multi-scan up to 120KHz, DVP1010 or DVP1510 processor option, with DVI/HDCP, YPbPr, RGB BNC or DB15 on video processor, DCDi</p>

<p>DILA-1080pHD	$40000, 3 chips 1920x1080, 1.8 to 2.35 manual zoom, 2000:1 CR, DVI/HDCP, option of DVP1080 ($6500 TTM Feb 05) or DVP1510 digital video processors, DCDi<br />
  </p>

<h2>Fujitsu</h2>
Nov 04
<u>LCD FPTV</u>
LPF-D711	$25000, 1080p, three 1.3-inches 16x9 LCDs, Epson's technology, Advanced Video Movement II (AVM-II) digital video processor, selector unit LPF-QSD1WB with HDMI, DVI-D, component, digital and analog RGB, RS-232, 3300:1 CR, 1200 ANSI lumens, images from 36 inches to 25 feet diagonally, 3 year warranty on projector, 2000 hour warranty on bulb

<p><br />
<h2>Hitachi</h2><br />
Apr 04<br />
<u>LCD RPTV line</u><br />
Virtual HD 1080p video processor, DVI, 26 point video processing, automatic 1080i 3:2 film correction, PCMCIA slot for photo memory card, TTM Apr 04<br />
60" 60V500 $4500<br />
50" 50V500 $3800</p>

<p>Jun 04 (company announcement of 2004/5 models)</p>

<p>Hitachi concentrated its DTV efforts in LCD (RPTV and direct-view) and CRT RPTV for 2004/5 models, which includes the new "CineForm" design series of fully integrated/CableCARD sets, expected by year-end 2004.  The company has switched from 3 to 21 integrated models transitioning to CableCARD tuner integration, using Hitachi's VirtualHD 1080p upconversion video processing on all models. </p>

<p><u>Eleven new Ultravision CineForm models (below)</u><br />
All integrated with ATSC/QAM CableCARD/dual NTSC tuners, Virtual HD 1080p video processor, identical vertical/horizontal look within the line, reduced height, two stage light engine, new dual-focus, advertising to start in Sep 04 </p>

<p><u>LCD RPTVs Integrated </u><br />
<u>Ultravision VS810 Series</u><br />
For open distribution, two HDMI/HDCP, 40 watt speaker system<br />
50"	50VS810	$4000, TTM 3Q04<br />
60"	60VS810	$4700, TTM 3Q04<br />
70"	70VS810	$7000, TTM 4Q04<br />
<u>Ultravision Director's VX915 Series</u><br />
For A/V retail stores, adds to above dual two-way 1394/DTCP, high-gloss cabinet w/black trim, deep-black anti-reflective shield, learning A/V Net III remote, TTM 4Q04<br />
50"	50VX915	$4700<br />
60"	60VX915	$5500<br />
70"	70VX915	$7500 </p>

<p><u>Non-CineForm LCD Integrated RPTV</u><br />
Fully integrated ATSC/QAM digital CableCARD tuning capability, TTM 3Q04<br />
<u>V710 (entry) Line</u><br />
720p, Virtual HD 1080p processing, HDMI, USB, 40watt 3-way speaker system<br />
42"	42V710	$2800<br />
50"	50V710	$3300<br />
60"	60V710	$4000<br />
<u>V715 (step-up) Line</u><br />
Titanium silver finish<br />
50"	50V715	$3300<br />
60"	60V715	$4000</p>

<p><u>CRT RPTVs</u><br />
<u>Series F510 Monitor Line</u><br />
TTM 3Q04, HDMI, Virtual HD 1080p processing, 1080i/540p<br />
46"	46F510	$1500<br />
51"	51F510	$1700<br />
57"	57F510	$2000<br />
<u>Series F710 Integrated</u><br />
TTM 3Q04, adds to above integrated w/ATSC and QAM CableCARD tuners<br />
65"	65F710	$3000<br />
<u>Series S715 Ultravision Integrated Line</u><br />
TTM 3Q04, adds to above five element lens system, USB, 40 watt speaker system<br />
51"	51S715	$2200<br />
57"	57S715	$2500</p>

<p><u>LCD FPTV Ultra-vision</u><br />
PJTX100	$4000, TTM 2Q04, 16:9 LCD for screen sizes between 30" and 300", 1200 ANSI, 1200:1 CR, 1280X720, 1.6:1 zoom, horizontal/vertical lens shift, DVI/HDCP</p>

<p><br />
<h2>InFocus</h2><br />
Sep 04<br />
<u>LCD FPTV</u><br />
ScreenPlay 5000, 3-chip, $2000, 1280x720, DVI/HDCP, component</p>

<p><u>LCD RPTV</u><br />
ScreenPlay, ultra-thin cabinet under 7 inches, dual integrated HDTV tuners, uni-directional Cable CARD, HDMI/HDCP, IEEE1394<br />
50"	SP50rp10	$8000 <br />
61"	SP61rp10	$10000<br />
70"			TTM early 2005</p>

<p><br />
<h2>JVC</h2><br />
<u>D-ILA FPTVs</u><br />
SX-21<br />
HX-1</p>

<p>DLA-QX-1 (QXGA) 2048x1536, 7000 ANSI lumens, CR > 1000:1, 1080i/24p/fp input capability, 12-bit gamma, 10-bit color processing, horizontal/vertical lens shift, optional anamorphic lens system, interchangeable lens system, HD-SDI input, DVI-D dual link option w/HDCP, for digital cinema screens <30 feet, home theater screens 12-20 feet, telecine transfer labs, YPbPr, RGBHV VGA, supports vertical Sync frequencies of 48Hz - 120 Hz (1080p/24/30/60i) and horizontal 31kHz -135kHz </p>

<p>DLA-HX2U	1400x788, 1500:1 CR, DIST, professional product</p>

<p>HD-2K, three D-ILA devices, 1920x1080 advanced technology, 2000:1 CR, 2-piece design, lower cost longer life lamp than Sony SXRD (one tenth), 13 element projection lens, outboard Faroudja 1080p signal processor via DVI-D, 13 pounds, TTM Summer 04<br />
 <br />
HD-4K projector comparable to 35mm quality, 4096x2160, 8.8 million pixels, 100000 hours lifetime in display modulators.</p>

<p><u>D-ILA RPTV line</u><br />
TTM Oct 04, integrated w/QAM CableCARD, and 1394 ports.<br />
52"	HD-52Z795	$5000<br />
61"	HD-61Z795	$</p>

<p><u>CES 2005</u><br />
<u>D-ILA 720p RPTV integrated line</u><br />
ATSC/QAM Cable CARD tuners, dual IEEE1394 ports<br />
52" 56" and 61" are offered in black or silver cabinet option as line 786<br />
52"	HD-52G886	$3300, TTM Jun 05<br />
56"	HD-56G886	$3700, TTM Jul 05 <br />
61"	HD-61Z886	$4000, TTM Mar 05<br />
70"	HD-70G886	$7000, TTM Mar 05, black cabinet cosmetic, memory card</p>

<p>A 70" RPTV was introduced as a D-ILA 1920x1080p integrated set; the picture was one of the best of the show:</p>

<p><u>D-ILA  1080p RPTV integrated line</u><br />
ATSC/QAM Cable CARD tuners, dual IEEE1394 inputs, dual HDMI inputs, no DVI<br />
61"	HD61FH96	$6000, TTM Sep 05<br />
70" 	HD70FH96	$9000, TTM Sep 05, memory card slot</p>

<table>
<tr><td align="center">
	<img src="/articles/images/mt/image035.jpg" alt="1080p Prototype">
</td><td align="center">
	<img src="/articles/images/mt/image036.jpg" alt="JVC DILA 70 inch 1080p RPTV HD70FH96">
</td></tr>
<tr><td>&nbsp;</td><td align="center">
	JVC DILA 70" 1080p RPTV HD70FH96
</td></tr></table>

<h2>LG</h2>
<u>CES 2005</u>
<u>LCoS RPTV line</u>
XG engine, integrated ATSC/NTSC/QAM Cable CARD tuners, 1920x1080p, $TBA, HDMI/HDCP, IEEE1394, 3500:1 CR, 
62"	62SL1D 	$TBA, TTM TBA
71"	71SL1D	$TBA, TTM Jul 05

<p><u>CRT super-thin direct-view</u><br />
30"	integrated ATSC/NTSC tuners, 1920x1080i native resolution, HDMI/HDCP, HD component input</p>

<p><u>3-D RPTVs</u><br />
<img src="/articles/images/mt/image037.jpg" alt="3-D HDTV RPTV" align="right"><br />
LG displayed an interesting pair of 60-inches RPTVs showing 3-D images using a Stereoscopic Projection System of video games.  For proper viewing, it was necessary to wear the typical 3-D glasses.  The pair of display RPTVs were mainly to make a technology statement, but it also might become a near future product line of LG if the market demands it.  According to LG, the system is applicable to 3D Game, 3D CAD, 3D Simulator, etc. at an optimum 3D view distance of 2.5 m (but more than 1m) using inputs as Normal DirecX-Based 3D Games and 3D Video.  </p>

<p><br />
<h2>Mitsubishi</h2><br />
April 2004 (company announcement of 2004/5 models)</p>

<p><u>LCoS RPTV</u><br />
82"	Alpha 925	$21000, TTM Oct 04, 1920x1080 pixels resolution, 120GB DVR for 12 hours HD, 72 hours SD, MPEG SD encoder, diffusion screen, two-way speakers, this new unit has now an internal DVR, but still costing $21000 as last year's model Alpha WL-82913</p>

<p><u>Diamond Series LCD Integrated TVs</u><br />
ATSC/NTSC/QAM CableCARD tuners, IEEE-1394, HDMI/HDCP, 120GB HDD DVR for 12 hours HD, 72 hours SD, TV Guide Onscreen IPG, MPEG SD encoders, Net Command 4.0 </p>

<p><img src="/articles/images/mt/image038.jpg" alt="Diamond Series LCD Integrated TVs"><br />
42"	LT-4260	$14000, 768x1365, TTM Oct 04, uses 20 fluorescent lamps<br />
55"	LT-5560	$TBA, 1080x1920, TTM TBA, uses 28 fluorescent lamps</p>

<p><u>CRT RPTVs</u><br />
<u>315 Series</u>, upgradeable monitors, DVI/HDCP<br />
42"	WT-42315	$1600, TTM Apr 04<br />
48"	WS-48315	$1800, TTM May 04<br />
55"	WS-55315	$2200, TTM Mar 04<br />
65"	WS-65315	$2700, TTM Apr 04</p>

<p><u>Eight CRT RPTV Fully Integrated Models</u><br />
ATSC/QAM CableCARD tuners, AMVP2 processing, IEEE-1394, Net-Command 4.0 system control, HDMI/HDCP <br />
<u>515 Series</u><br />
48"	WS-48515	$2300, TTM Jul 04<br />
55"	WS-55515	$2700, TTM Jul 04<br />
65"	WS-65515	$3200, TTM Apr 04</p>

<p><u>Medallion 615 Series</u>, TTM Aug 04<br />
55"	WS-55615	$3000<br />
65"	WS-65615	$3500<br />
73"	WS-73615	$5300<br />
<u>Diamond 815 Series</u>, TTM Aug 04<br />
<img src="/articles/images/mt/image039.jpg" alt="Diamond 815 Series"><br />
55"	WS-55815	$4500<br />
65"	WS-65815	$5500, 9-inch CRTs</p>

<p><br />
<h2>Moxell</h2><br />
<u>CES 2005</u><br />
First line of direct-view CRT TVs (ten) <br />
<u>Proview CRT</u><br />
13", 20", 24", and 27" analog models from $90 to $400 in Mar/Apr 05<br />
27"	TI-627		$450, TTM May 05, HDTV monitor<br />
30"	TI-630		$650, TTM Jun 05, HDTV monitor<br />
32"	TI-632		$700, TTM Jul 05, HDTV monitor</p>

<p><br />
<h2>Panasonic</h2><br />
2004 lines (D64 and 54 RPTVs, L14 and LCX64 LCD RPTVs) are still current; check details and original MSRP prices on the CES 2004 report </p>

<p><u>LCD FPTV</u><br />
PT-AE500, 1280x720 LCD panels, 10 bit digital processing and gamma correction, three layer RGB structure for 2.76 million pixels, 850 ANSI lumens, 1300:1 CR, 100" screen size from 10 feet distance.</p>

<p>Oct 04 (announced at CEATEC)<br />
<u>LCD FPTV</u><br />
PT-AE7000U-EC	$3000, TTM Oct 04, 2000:1 CR, 2x optical lens, 1000 lumens, 480i/p, 720p, 1080i, 10-bit gamma correction, Smooth Screen Technology for film appearance<br />
<img src="/articles/images/mt/image040.gif" alt="LCD FPTV"></p>

<p>Nov 04<br />
Panasonic confirmed their decision to discontinue CRT RPTV production by March 2005, although direct-view continues.</p>

<p><u>CES 2005</u><br />
<u>LCD RPTVs</u><br />
Integrated with ATSC/NTSC/QAM Cable CARD tuners, Photo Viewer w/SD slot, RGB PC input, HDMI/HDCP<br />
LCX85 Series<br />
61"	PT-61LCX85	1080p, 2000:1 CR<br />
<img src="/articles/images/mt/image041.jpg" alt="LCX85 Series"></p>

<p><u>LCX65 Series</u><br />
44"	PT-44LCX65<br />
52"	PT-52LCX65<br />
61"	PT-61LCX65</p>

<p><br />
<h2>Philips</h2><br />
Jun 04<br />
<u>LCoS RPTVs</u><br />
New 3rd generation, Matchline and Epic series in 55" and 62" sizes. <br />
<u>Matchline</u><br />
55"	55PL9774	$4300, TTM current<br />
62"	62PL9774	$N/A, TTM Jun 04<br />
<u>Epic line</u><br />
55"	55PL9524	$3800, TTM current<br />
62"	62PL9524	$4200, TTM Jun 04</p>

<p>Oct 04<br />
<u>Cineos series</u><br />
Integrated CableCARD/ATSC tuners<br />
44"	44PL9523	$2300, black and black/silver cabinets<br />
55"	55PL9223	$2500<br />
55"	55PL9524	$3000<br />
55"	55PL9774	$3000<br />
62"	62PL9524	$3300<br />
62"	62PL9774	$3500</p>

<p>In Oct 04, Philips announced that it has decided to discontinue their LCoS business with engines and RPTV, the company indicated that it had invested approximately $200 million in LCoS, the RPTV market was too small, and is not willing to increase the investment for the company to compete with these products.  Operations were planned to stop on November 19, 2004.  The company also announced a retail price reduction of $500 on existing LCoS sets.</p>

<p><u>CRT RPTVs</u><br />
Three models:<br />
51"	51pp9920	$2100<br />
55"	55pp9920	$2300<br />
60"	60pp9920	$2600</p>

<p><br />
<h2>Runco</h2><br />
<u>FPTVs</u><br />
<u>Vision Line</u><br />
Model 60	$15000, 1400x768, D-ILA<br />
Model 100	$25000, 1366x768, LCD, 70 foot-lamberts</p>

<p>Sep 04 (CEDIA introduction)<br />
Cinewide motorized anamorphic lens for 2.35:1 aspect ratio, the technology expands 1.78:1 to 2.35:1</p>

<p><br />
<h2>Samsung</h2><br />
Jun 2004 (company announcement of 2004/5 models)</p>

<p><u>CRT RPTVs Monitors</u><br />
TTM Apr 04, HDMI/HDCP, two component inputs<br />
42"	HC-P4252W	$1200, tabletop<br />
47"	HC-P4752W	$1300, tabletop<br />
52"	HC-P5252W	$1500, floor-standing</p>

<p>A year ago at CES 2004, Samsung announced that it would drop the 55" and 65" CRT RPTV monitor sets to focus on micro-display technologies, and actually happened in 2004.  The company also indicated that it plans to transition the above CRT RPTV monitors to fully integrated CableCARD sets later in the year, a difficult endeavor when adding a relatively expensive HD tuner to the low cost of CRT models, according to Samsung.  One integrated (transitioned) model mentioned at CES 2004 was:</p>

<p>52"	HC-P5256W	$2,200, TTM later 04, integrated w/ATSC/QAM CableCARD, DNIe, HDMI (note the estimated difference of $700 of the MSRP of 52" monitor vs. the estimated integrated at CES 2004 time).</p>

<p><u>CRT Direct-view Integrated Models</u> (eight)<br />
Built-in ATSC tuners in four screen sizes, in-the-clear QAM digital cable tuner (omit CableCARD), DynaFlat picture tube, DVI/HDCP, accept 1080i/720p to display as native 1080i <br />
30" in 16x9 AR, three models priced between $1000-$1200<br />
26" in 16x9 AR, two models priced at $700 each<br />
32" in 4x3 AR, two models at $1000 each<br />
27" in 4x3 AR, $700</p>

<p><u>CES 2005</u><br />
Samsung has shown a new technology to reduce by 30% the depth of a direct-view CRT tube.  The technology is called "SlimFit" and will be used on a new line of sets:</p>

<table><tr><td align="center">
<img src="/articles/images/mt/image042.jpg" alt="Dynaflat">
</td><td align="center">
<img src="/articles/images/mt/image043.jpg" alt="Dynaflat">
</td></tr></table>

<p><u>DynaFlat SlimFit CRT direct-view TV Series</u> 30"	TX-R3079WH	$1300, TTM Mar 05, 15.5 inches deep, integrated ATSC tuner, HDMI, 2 HD/DVD component inputs</p>

<p><u>CRT RPTVs new line</u><br />
TTM 2Q05, floating screen cabinet, integrated ATSC tuner, excludes QAM Cable CARD slot<br />
43"	$1300<br />
47"</p>

<p><br />
<h2>Sanyo</h2><br />
Oct 04<br />
<u>LCD FPTV</u><br />
PLV-HD10	1920x1080, 5500 ANSI lumens, 1000:1 CR</p>

<table><tr><td align="center">
<img src="/articles/images/mt/image044.jpg" alt="Sanyo PLV-HD10 FPTV"><br>
Sanyo PLV-HD10 FPTV
</td></tr></table>

<p>PLV-WF10	1366x768, 3000 ANSI lumens, 900:1 CR</p>

<p><u>LCD RPTV</u><br />
Oct 04 (CEATEC introduction)<br />
55"	LP-55WR1	$6000, TTM in Japan Dec 04, in US by 2005, 1280x720 </p>

<p><u>LCD FPTV</u><br />
Oct 04 (CEATEC introduction)<br />
LP-23	$3100, TTM Oct 04 in Japan, 2000:1 CR, 1280x720, up to 200 inches image</p>

<p><u>CES 2005</u><br />
<u>LCD FPTVs</u><br />
PLC-EF60	1400x1050, 5800 ANSI lumens, 1300: 1 CR<br />
PLC-XF60	1024x768, 6500 ANSI lumens, 1300:1 CR<br />
PLC-XP56/L	1024x768, 5000 ANSI lumens, 1200:1 CR<br />
PLC-XP51/L	1024x768, 4000 ANSI lumens, 1000:1 CR<br />
PLC-XU51	1024x768, 2000 ANSI lumens, 450:1 CR, ultra portable<br />
PLC-XU56	1024x768, 2500 ANSI lumens, 450:1 CR, ultra portable<br />
PLC-XU47	1024x768, 2000 ANSI lumens, 450:1 CR, ultra portable<br />
PLC-XU41	1024x768, 1500 ANSI lumens, 450:1 CR, ultra portable<br />
PLC-SU51	800x600, 2000 ANSI lumens, 450:1 CR, ultra portable<br />
PLC-SW35	800x600, 1500 ANSI lumens, 350:1 CR, ultra portable </p>

<p><br />
<h2>Sears</h2><br />
Sep 04<br />
<u>First LCoS own brand (Veos)</u><br />
65"	720p monitor, Brillian chip, $8000, 2000:1 CR, DVI, optional expansion port for ATSC tuner, QAM cable tuner w/Cable CARD, and 1394 memory card devices  </p>

<p><br />
<h2>Sony</h2><br />
February/June/August 2004 (company announcements of 2004/5 models)</p>

<p>In February 2004, Sony introduced twelve HDTV integrated models with ATSC/NTSC/QAM CableCARD unidirectional tuners with HDMI/HDCP digital connectivity and two HD-STBs with DVR for QAM Cable CARD tuning.  Of the twelve models, six are LCD Grand Wega RPTVs, four CRT Direct-View sets, and two CRT-based RPTV sets, as follows:</p>

<p><u>LCD Grand Wega Integrated RPTVs</u><br />
Two new series (WF and XS) were added to the entry-level (WE) and high-end (XBR) Series, and two new models were added within the WE Series, TTM Sep 04, 16:9 AR, Sony LCD Optical Engine video processing</p>

<p><u>New models on the WE Series</u><br />
42"	KDF-42WE655	$2800<br />
50"	KDF-50WE655	$3000<br />
<u>New WF Series</u><br />
55"	KDF-55WF655	$3700<br />
60"	KDF-60WF655	$4000<br />
<u>New XS Series</u><br />
55"	KDF-55XS955		$4000<br />
60"	KDF-60XS955		$4400</p>

<p><u>Direct-View Integrated CRT Tubes</u><br />
Trinitron Wega tubes, SuperFine Pitch CRT technology, Wega engine processing<br />
34"	KD-34XBR960		$2200, TTM Jun 04<br />
34"	KD-34SX955		$2000, TTM Aug 04<br />
36"	KD-36SX955		$1900, 4:3 AR, TTM Oct 04<br />
30"	KD-30SX955		$1400, 16:9 AR, TTM Aug 04</p>

<p><u>CRT-based RPTVs Integrated</u><br />
TTM Sep 04, WEGA engine, Direct Digital, DRCM (Digital Reality Creation MultiFunction), Multi-Image Driver (MID-X) circuitry<br />
51"	KDP-51WS655	$2100<br />
57"	KDP-57WS655	$2400</p>

<p><u>Hi-Scan Series FD Trinitron WEGA Monitors</u><br />
Solid silver tone, rounded corner cabinetry, dual component inputs, HDMI/HDCP<br />
27"	KV-27HS420	$750<br />
30"	KV-30HS420	$1000<br />
32"	KV-32HS420	$1000, 4:3 AR<br />
34"	KV-34HS420	$N/A, 16:9 AR<br />
36"	KV-36HS420	$N/A</p>

<p><u>LCoS RPTV (Sony's SXRD Technology)</u></p>

<p><img src="/articles/images/mt/image045.jpg" alt="SONY 70 SXRD LCoS 1080p RPTV KDS-70Q006" align="right"><br />
To pair their current QUALIA FPTV projector, Sony unveiled a 16:9 model KDS-70Q006 for $13000 (previously called KDS-70XBR100 within the XBR line, for $10000), native resolution of 1920x1080, 70 inches, NTSC/ATSC/QAM CableCARD tuners, 200-watt cooled lamp for 3000:1 CR, WEGA Engine System, HD component inputs, HDMI/HDCP, IEEE1394 (iLink), TTM Jan 05. </p>

<p>At CES 2005 the model above was shown with identical characteristics than the XBR set at CEDIA, the set was introduced now as part of the QUALIA hi-end line, and its price increased to $13,000.  The TV does NOT accept 1080p externally.</p>

<p><u>LCD FPTVs</u><br />
<u>Superlite line</u><br />
VPL-ES1, SVGA, 1000 ANSI, $1,300, HDTV capability<br />
Oct 04<br />
Cineza VPL-HS51	$3500, TTM Oct 04, 1280x720, 6000:1 CR <br />
<img src="/articles/images/mt/image046.jpg" alt="Cineza VPL-HS51"></p>

<p><u>4K SXRD</u><br />
Sep 04<br />
4K projector SRX-R110 (introduced at Digital Cinema Laboratory in Hollywood, CA), resolution of 4096x2160, compatible with 2K projectors of 1920x1080, judged as with a picture quality of at least 35 mm, 10000 ANSI lumens, 3000:1 CR (expected at 2000:1 in the production units), expected in theaters by 2005, dual-screen mode for the projection of dual 1920x1080 images, and quad-mode for four 1920x1080 images, $80000, $15000 extra for lens, TTM Jan 05, suitable for up to 40 feet wide screens.</p>

<p>SRX-R105	$60000, 5000 lumens, suitable for up to 25 feet screens.</p>

<p><u>Sony Black Screen</u><br />
Sony introduced their new screen designed to reflect only red, green, and blue wavelengths with a 2.1 gain, absorbing all ambient light in the room.  The screen measures 80 inches and will be sold for about $2000, TTM next summer.  The screen was shown at CES 2005 mating Sony's Cineza VPL-HS51 LCD projector.  The very large room they used for this screen was shared with all their other TVs, cameras, and equipment which required of sufficient light to been able to see all the components and read their specifications.  Even with such lighting conditions the screen was still able to perform acceptably, according to Sony it is designed to perform properly with daylight.</p>

<p><br />
<h2>Syntax</h2><br />
<img src="/articles/images/mt/image047.jpg" alt="LCoS RPTV - Olevia" align="left"><br />
<u>CES 2005</u><br />
LCoS RPTV<br />
Olevia line</p>

<p>50"	LCT50HV	$2100, TTM Dec 04, 1388x780 3-panel RGB, 1000:1 CR, 1000 Nits of brightness, 3:2 pulldown, wide 170/170 viewing angle, NTSC tuner, DVI/HDCP, YCbCr, YPbPr, VGA RGB for PC</p>

<p>61"	$TBA, TTM end 05, ATSC tuner, 1920x1080p, NO 1080p input </p>

<p><br clear="all"><br />
<h2>Thomson</h2><br />
May 04 (company announcement of 2004/5 models)</p>

<p>Thomson join venture with China's CTL (TTE) starting in July 04 will produce for the US market eleven fully integrated ATSC and Digital Cable Ready models with QAM CableCARD unidirectional (seven RCA Scenium DLP models, four RCA CRT RPTV models), with HDMI, and with component inputs.  The new sets are said to recognize the Broadcast Flag.  According to TTE, the company will become the largest company in the world for color TV products; selling 18 million sets annually (with a 22 million production capacity), representing 11% globally.  </p>

<p><u>CRT RPTV Integrated</u><br />
Four new sets w/ATSC and QAM cable tuners, HDMI/HDCP, component, TTM fall 04<br />
52" 	HD52W55	$1900<br />
52"	HD52W56	$2000<br />
<u>58 group</u><br />
Subwoofer output, protective screen shield, SRS Focus<br />
52"	HD52W58	$2300<br />
56"	HD56W58	$2500</p>

<p><u>Current 42 Series DLP Integrated</u> (continues in the line up)<br />
ATSC/QAM cable in-the-clear tuners, includes EPG, Internet browser, HDMI, and IEEE-1394<br />
50"	HD50LPW42	$3800<br />
61"	HD61LPW42	$4300</p>

<p><u>CRT RPTV Monitors</u> (carried over)<br />
DVI/HDCP <br />
52"	D52W15	$1500<br />
52"	D52W20	$1700<br />
56"	D56W20	$2000<br />
61"	D61W20	$2200</p>

<p><u>CES 2005</u><br />
<u>Introduced 10 RCA CRT RPTV models in five Series</u><br />
Integrated ATSC tuners, $ 1100 for the 52"</p>

<p><u>59 Series</u><br />
52"	HD52W59</p>

<p><u>64 Series</u><br />
52"	HD52W64</p>

<p><u>65 Series</u><br />
56"	HD56W65</p>

<p><u>66 and 68 series below with DVI and subwoofer</u><br />
<u>66 Series</u><br />
52"	HD52W66<br />
56"	HD56W66<br />
61"	HD61W66</p>

<p><u>68 Series</u><br />
52"	HD52W68<br />
56"	HD56W68</p>

<p><u>Introduced seven new direct-view CRTs integrated</u><br />
ATSC tuner, displays 480i images (SDTV) in 4:3 AR:<br />
27"	$269, entry level<br />
32" 	$<$400</p>

<p><u>CRT HDTVs direct-view 4:3 monitors</u><br />
27"<br />
32"</p>

<p><br />
<h2>Toshiba</h2><br />
May 04 (company announcement of 2004/5 models)</p>

<p>Toshiba announced its 2004-05 television line to dealers.  The new line is mainly oriented to fixed-pixel digital display technologies, such as direct-view LCD TV, plasma, Digital Light Processing (DLP) rear-projection integrated sets and monitors, in addition to CRT-based rear-projection and direct-view products.</p>

<p>In January 2004 (CES), Toshiba announced their decision of discontinuing the LCoS line, which is now replaced by their support to DLP.</p>

<p><u>CRT RPTVs</u><br />
Analog and 4:3 aspect ratio sets are now discontinued <br />
<u>TheaterWide Monitors</u><br />
46"	46H84		$1400, Jun 04, tabletop<br />
51"	51H84		$1700, May 04<br />
57"	57H84		$1900, May 04<br />
65"	65H84		$2200, Jun 04</p>

<p><u>TheaterWide Integrated</u><br />
QAM CableCard/ATSC tuners, IEEE-1394, TV Guide On-screen interface <br />
51"	51H94		$2100, Jul 04<br />
57"	57H94		$2300, Sep 04</p>

<p><u>Cinema Series Integrated</u><br />
QAM CableCard/ATSC tuners, IEEE-1394, TV Guide On-Screen interface<br />
51"	51HX94	$2400, Aug 04<br />
57"	57HX94	$2600, Sep 04<br />
65"	65HX94	$2900, Oct 04</p>

<p><u>Direct-view CRT Monitors</u><br />
<u>TheaterWide Line</u><br />
HDMI<br />
26"	26HF84	$700, Aug 04<br />
30"	30HF84	$900, Jul 04<br />
34"	34HF84	$1400, Jun 04<br />
<u>Cinema Series Line</u><br />
30"	30HFX84	$1000, Aug 04<br />
34"	34HFX84	$1600, Jul 04</p>

<p>At the time of the May 04 announcement of new 2004/5 lines Toshiba anticipated a demand for direct-view digital televisions and will keep producing complete lines of analog CRT direct-view models (curved and flat-faced).</p>

<p>Sep 04<br />
<u>SED</u><br />
Toshiba and Canon have been working together since 1999 in a join venture for the development of SED panels, expected to be 55 inches and above.  Flat panel TV with SED (surface-conduction electron-emitter display) technology is said to be similar than CRT beam-emitting technology to obtain comparable clear images but with a flat panel.  SED handles fast images without jagged edges and consumes one-third the electric current needed by plasma.  Although the first SED televisions could be available in 2005, full production is expected in 2006.</p>

<p>SED is formed by two glass plates with vacuum in between, one mounted with electron emitters and pixels similar in number to those of a CRT electron gun, and another glass plate coated with a fluorescent substance.  The technology has a very narrow slit (several nanometers wide) made from ultrafine-particle film; reaction to voltage produces a tunneling effect and the emission of electrons, which are accelerated by the voltage applied between the glass plates and collide with the fluorescent-coated glass plate, which emits light.  </p>

<p>SED has a wide angle of viewing, similar to CRT.  Larger screens can be manufactured increasing the number of electron emitters to match the required number of pixels.  SEDs do not need electronic-beam deflection.  Wall-mounted large-screen TV displays can be made with only a few centimeters thick.</p>

<p><u>CES 2005</u><br />
<u>SED</u><br />
SED products are expected to perform with 1 millisecond response time and 8600:1 CR, and are planned to be offered in late 2005/early 2006 at a price range of LCD-TV panels of equivalent size, starting with 50" model.  The company expects SED panels to challenge the flat-panel market currently dominated by plasmas and LCD-TVs. </p>

<p>36"	1280x720, flat panel with CRT performance (demo)<br />
50"	1920x1080, TTM late 05/early 06, $ TBA</p>

<p><u>CRT direct-view</u><br />
Super-thin models will be introduced in 2005, with 30% less depth,</p>

<p><u>CRT Direct-view TryPlay widescreen line</u><br />
26"	$700, TTM Mar 05<br />
30"	$1000, TTM Mar 05<br />
30"	$1400, TTM Jun 05, integrated with ATSC/QAM tuners</p>

<p><br />
<h2>Vidrikon</h2><br />
Jun 04 (Home Entertainment Expo)</p>

<p><u>FPTVs</u> (in addition to the models 20 and 40 DLP FPTVs already introduced)<br />
Model 60 D-ILA	$13000, 3-panel, 1400x768 16:9 native, 1000 ANSI, 800:1 CR, TTM late summer 2004</p>

<p>Sep 04 (CEDIA introduction)<br />
Model 80 D-ILA	$30,000, 1920x1280, external processing, 1050 ANSI lumens, 2100:1 CR, DVI/HDCP, component</p>

<p><br />
<h2>Yamaha</h2><br />
<u>LCD FPTV</u><br />
LPX-510	$5500, TTM Sep 04, 3x 0.7-inch LCD panels of 1280x720, DCDi, 10-bit D/A converter, 1200:1 CR, 1000 brightness with 200 watt UHP lamp (3000 hours expected life), HD component, HDMI/HDCP, RS-232</p>

<p>Be sure that you read the next article in the series: DLP RPTV's & FPTV Projectors (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 15, 2005  8:14 AM</b>
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
			<?=getComments(236)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 236)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-6-crt-lcos-dila-sxrd-sed-and-lcd.php" type="text/javascript" charset="utf-8"></script>
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