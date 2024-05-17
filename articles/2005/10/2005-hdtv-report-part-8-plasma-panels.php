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
		AND e.entry_id = 219";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 219 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 219 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 219";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-8-plasma-panels.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 219";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 8: Plasma Panels" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 8: Plasma Panels" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2005 HDTV Report, Part 8: Plasma Panels" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 8: Plasma Panels</title>
	<meta name="keywords" content="dvi hdcp, atsc ntsc, ttm apr, ttm current, hdcp component, ttm, dvi, hdcp, component, ntsc, tuners, plasma, atsc, ces, rgb, hdmi, series, integrated, brightness, sep, monitor, edtv, qam, panels, pdp" />
	<meta name="description" content="This is a section dedicated to plasmas.  In 2005, panel prices are coming down at a faster rate relative to other types of displays, and plasma panels will be more common at larger sizes, such as 70+ and 80-inches plasmas, and even an oversized 102&quot; model, expected within two years. LCD panels are joining the 40&quot; plus domain of the plasmas, with 40 to 65 inches from many manufacturers. CES unveiled a good number of these oversized panels.  Samsung introduced large plasmas up to 80 inches (HPR8072, $39,000 MSRP, 1920x1080p) and a 102&quot; prototype model announced as the largest TV in the world (Z102, 1920x1080p, TTM two years, $80,000-$90,000 estimated MSRP).  LG unveiled their 71&quot; plasma model MW-71PY10, planned for Feb/Mar 2005, $75,000, 1920x1080p; was still unavailable in September but some sites can BO for &quot;just&quot; $29K, quite a drop in price.  LG's technical team supporting this plasma assured the panel will accept 1080p when is released in the US (have to see to believe that claim), the panel was announced at CES as to be manufactured in limited numbers (5000) and to be initially distributed in Chicago, Los Angeles, and New York; according to LG, there was a 3-month waiting list already in January. The same model is also made in gold finish and paired
with a gold finished audio system, the system is offered in Korea for $100,000.  Are you ready to invest your retirement fund in one of these?" />
	<meta name="title" content="2005 HDTV Report, Part 8: Plasma Panels" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2005 HDTV Report, Part 8: Plasma Panels" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-8-plasma-panels.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This is a section dedicated to plasmas.  In 2005, panel prices are coming down at a faster rate relative to other types of displays, and plasma panels will be more common at larger sizes, such as 70+ and 80-inches plasmas, and even an oversized 102&quot; model, expected within two years. LCD panels are joining the 40&quot; plus domain of the plasmas, with 40 to 65 inches from many manufacturers. CES unveiled a good number of these oversized panels.  Samsung introduced large plasmas up to 80 inches (HPR8072, $39,000 MSRP, 1920x1080p) and a 102&quot; prototype model announced as the largest TV in the world (Z102, 1920x1080p, TTM two years, $80,000-$90,000 estimated MSRP).  LG unveiled their 71&quot; plasma model MW-71PY10, planned for Feb/Mar 2005, $75,000, 1920x1080p; was still unavailable in September but some sites can BO for &quot;just&quot; $29K, quite a drop in price.  LG's technical team supporting this plasma assured the panel will accept 1080p when is released in the US (have to see to believe that claim), the panel was announced at CES as to be manufactured in limited numbers (5000) and to be initially distributed in Chicago, Los Angeles, and New York; according to LG, there was a 3-month waiting list already in January. The same model is also made in gold finish and paired
with a gold finished audio system, the system is offered in Korea for $100,000.  Are you ready to invest your retirement fund in one of these?" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=219', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-8-plasma-panels.php">2005 HDTV Report, Part 8: Plasma Panels</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 17, 2005</b>
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

<h2>Akay</h2>
TTM Feb 05, component, DVI/HDCP, monitors
42"	PDP420QHD	$2800, 1024x1024
50"	PDP500QDH	$4500, 1366x768

<p><u>CES 2005</u><br />
TTM Feb 05<br />
42"	EDTV	$1200<br />
50"</p>

<p><br />
<h2>AKIRA</h2><br />
May 04<br />
63"	HPT-630A	$TBA, 1000:1 CR, 1000 cd/m2 brightness, user selectable color temp from 3200 to 9300 degrees Kelvin, dual NTSC/ATSC tuner, DVI-I/HDCP, network Ethernet connection</p>

<p><br />
<h2>Audiovox</h2><br />
Mar 04 <br />
First plasma panels under the Acoustic Research brand name for specialty retailers</p>

<p>42"	AR4200	EDTV, TTM 2Q04, $4500, 8521x480, one NTSC tuner, DVI/HDCP, component<br />
50"	AR5000	HDTV, TTM 2Q04, $7600, 1366x768, dual NTSC tuners, DVI/HDCP, component  </p>

<p><br />
<h2>Crystal View</h2><br />
Dec 04<br />
50"	CVP-50	1100:1 CR</p>

<p><br />
<h2>Dell</h2><br />
Oct 04<br />
New models, dual NTSC tuners, PIP, 20-watt audio, DVI/HDCP <br />
42"	W4200HD	$3500, TTM Nov 04, HDTV, ATSC tuner, 1024x768, 450 nits, 2700:1 CR<br />
42"	W4200ED	$2300, TTM Nov 04, EDTV, 852x480, 420 nits, 2300:1 CR</p>

<p><br />
<h2>Dwin</h2><br />
Sep 04 (CEDIA announcement)<br />
<u>PlasmaImage HD series</u><br />
Two component design with TranScanner control box/processor/scaler, 12-bit color processing, DVI/HDCP connection to the panel, two DVI/HDCP inputs, two RGB, two component, screen saver for burn-in prevention<br />
42"	HD-142	$9000, 1024x768</p>

<p>Two larger models below with 1365x768, TTM current<br />
50"	HD-150	$11000<br />
61"	HD-161	$20000</p>

<p><u>CES 2005</u><br />
Plasma image POR Series<br />
DVI-D/HDCP, RGBHV, RGB/PC, up to 9000 feet<br />
42"	PRO-142	1024x768<br />
50"	PRO-150	1365x768<br />
61"	PRO-161	1365x768</p>

<p><u>New Video Processor</u><br />
Duo Vision HD		Dual-display system to supply DVI/HDCP digital connectivity to TransVision 3 720P DLP projector and PlasmaImage series plasma panels from one central location simultaneously from up to 100 feet away using Dwin DVI Cable Extender ($245).  The Duo Vision is suited with two DVI/HDCP and two component inputs and packages are priced from $18000.  It can only be used to Dwin displays designed for this interface.</p>

<p><br />
<h2>Faroudja</h2><br />
<u>CES 2005</u><br />
42"	FPP-42HD30	$N/A (special order 3-4 weeks), 1024x768<br />
50"	FPP-50HD30	$13000, TTM Jan 05, 1365x768<br />
61"	FPP-61HD30	$22000, TTM now, 1365x768</p>

<p>Video processors for panels above:<br />
DVP1010 and DVD1510 (included in the DILA FPTVs and processors sections)</p>

<p><br />
<h2>Fujitsu General</h2><br />
Apr 04 <br />
The company has cut the prices of five of their monitors<br />
50"	P50XHA30WS from $11,000 to $9,000<br />
55"	P55XHA30WS from $15,000 to $12,000</p>

<p>The other Plasmavision  Slimscreen models remain unchanged, among which:<br />
42"	P42VHA30WS	$5000<br />
42"	P42HHA30WS	$7000<br />
63"	P63XHA30WS	$25000</p>

<p>New 2004 models for Commercial market:<br />
42"	P42HCA30WH	$7000<br />
42"	P42VCA30WH	$5000<br />
50"	P50XCA30WH	$11000</p>

<p>Sep 04<br />
<u>PlasmaVision monitors</u><br />
AVM II processor, DVI-D/HDCP, RS-232, built-in stereo amp, TTM was TBA in fall, $ was TBA in fall, MSRP prices reflect recent reductions <br />
42"	P42VHA40US $4000, TTM now, 852x480 EDTV, 852x480<br />
42"	P42HHA40US $5500, TTM now, 1024x1024 HDTV, 1000:1 CR<br />
50"	P50XHA40US	$7500, TTM now, 1366x768, 3000:1 CR<br />
55"	P55XHA40US	$10000, TTM Jan 05,1366x768, 900:1 CR<br />
63'	P63XHA40US	$18000, TTM 2Q05, 1366x768, 3000:1 CR, HDMI, 160 degrees viewing angle</p>

<p><u>Monitors</u><br />
42" PDM-4210<br />
55" PDM-5520<br />
63"</p>

<p><br />
<h2>Hitachi</h2><br />
June 2004 (company announcement of 2004/5 models)</p>

<p><u>Ultravision HDT51 Series Integrated</u><br />
TTM 3Q04, CineForm cosmetics, dual HDMI, dual 1394, Quick Start Seamless ATSC/NTSC/QAM CableCARD tuners, Virtual HD 1080p processing, USB, inputs/outputs housed on Hitachi's AV Center connected via single wire to panel and controlled with IR from screen for hide away installations<br />
42"	42HDT51	$6000, 1024x1024, AliS technology<br />
<img src="/articles/images/mt/image061.jpg" alt=""><br />
55"	55HDT51	$10000, WXGA 1366x768</p>

<p><u>Ultravision HDX61 Director's Series Integrated</u><br />
Same features as HDT51 line plus enhanced industrial design w/high gloss, black trim, high-contrast deep black shield, two year warranty, TTM 3Q04<br />
42"	42HDX61	$7000<br />
55"	55HDX61	$11000</p>

<p><u>Plasma EDTV Monitor</u><br />
42"	42EDT41	$4300, Virtual HD 1080p processing, 480p, DVI/HDCP, NTSC tuner, DVI/HDCP, TTM 2Q04<br />
<u>Plasma Professional Panel</u><br />
42"	CMP420V	$3500, DVI, 853x480, V1 black frame version, V2 silver frame </p>

<p><br />
<h2>HP</h2><br />
Sep 04<br />
Piano black, less 4 inches deep, VFS, DCDi, 3:2 and 2:2 pull-down conversion for film, dual integrated NTSC tuners, PIP, DVI-D/HDCP, component <br />
42"	PE4240N	$3000, EDTV (852x480), optional wall bracket/attachable speakers/subwoofer<br />
42"	PL4245N	$5000, 1024x768, 3000:1 CR  </p>

<p><br />
<h2>HUMAX</h2><br />
Mar 04 <br />
First plasma<br />
42"	$4500, 1024x1024, ATSC tuner </p>

<p><br />
<h2>JVC</h2><br />
<u>CES 2005</u><br />
Two model new line<br />
XGA resolution upconverted to 770p using DIST, ATSC/QAM Cable CARD tuners (within the included outboard media box), HDMI, dual IEEE1394, dual HD component<br />
42"<br />
50"</p>

<p><br />
<h2>LG</h2><br />
Jul 04<br />
71" 	MW-71PY10	$N/A, TTM fall 2004, monitor, see below confirmed availability at CES</p>

<p>On Sep 04 at CEDIA LG announced the availability by fall of a new line of panels with  integrated QAM/ATSC w/Cable CARD tuners, 5th generation 8VSB for improved multipath performance, "Double Life" technology which extends to 60000 hours the life of their plasma displays, DVI/HDCP, HDMI, 1394, RS-232, PC connection, PIP/POP/split-zoom and twin picture, 1000cd/m2 brightness, 3000:1 CR, four exclusive image -sticking prevention options, XD engine, Gemstar TV Guide IPG.<br />
42"	DU-42PY10	$6000 (also announced as $5500)<br />
50"	DU-50PY10	$7000 (also announced as $6500)<br />
60"	DU-60PY10	$17000 (also announced as $15000)</p>

<p><u>CES 2005</u><br />
<u>Plasma Integrated panels</u><br />
TTM Mar 04, integrated with ATSC/NTSC/QAM Cable CARD tuners, 160GB DVR, EPG, XG engine, four burn-in prevention processes, 1366x768, IEEE1394, HDMI/HDCP<br />
50"	50PY2DR	$9000  <br />
60"	60PY2DR	$12000 (also reported as $16000) <br />
60"	60PY2D	TTM Apr 05</p>

<p><u>Other Integrated plasmas</u><br />
ATSC/NTSC/QAM CableCARD tuners, 5000:1 CR<br />
42"	DU-42PX12X	TTM Jan 05, 1024x768, DVI/HDCP </p>

<p>ATSC/NTSC/QAM CableCARD tuners, 5000:1 CR, HDMI/HDCP, IEEE1394, LG's XD Engine<br />
42"	42PX4D	TTM Mar 05, 1024x768<br />
50"	50PX4D	TTM Apr 05, 1366x768            <br />
<img src="/articles/images/mt/image062.jpg" alt="" align="right"></p>

<p>42"	42PX5D	TTM Apr 05, 1024x768<br />
50"	50PX5D	TTM Apr 05, 1366x768</p>

<p><u>Oversized Monitor</u><br />
71"	MW-71PY10	$75000, Feb/Mar 05, 1920x1080p, integrated ATSC/NTSC/QAM Cable CARD tuners, 800 cd/m2 brightness, 1200:1 CR, DCDi, HDMI, DVI, component, 3-months waiting list, 5000 units production, limited distribution to New York, Chicago, and Los Angeles. A Gold package version with audio system available for the Korean market only, for $100000.   </p>

<p>Apparently, the 76" introduced at CES 2004 one year ago was dropped from the plans of actual production.</p>

<p><br />
<h2>Luce/Epoq</h2><br />
Apr 04<br />
<u>HDTV panels</u><br />
TTM current, all integrated with ATSC/NTSC tuners<br />
42"	$6500<br />
50"	$11000<br />
63"	$23000</p>

<p><u>EDTVs</u><br />
42"	$6,000<br />
50"	$9,000</p>

<p>Oct 04<br />
42"	MU-42PZ90XC  monitor</p>

<p>Sep 04 (CEDIA announcement)<br />
<u>Integrated HDTVs</u><br />
With ATSC/NTSC tuners, DCDi, DVI/HDCP, component, VGA<br />
42"	STV-42A2	$4300, 1024x768, 3000:1 CR, 1000 cd/m2 brightness<br />
50"	HTV-50A2	$11000, 1365x768, 3000:1 CR, 1000 cd/m2 brightness<br />
63"	HTV-63A2	$23000, 1365x768, 850 cd/m2 brightness, 950:1 CR</p>

<p><u>Integrated EDTVs</u> (NTSC tuners only)<br />
42"	STV-42A0	$4000, 853x480, 1500:1 CR, one tuner<br />
42"	STV-42A2	$4300, 853x480, 3000:1 CR, 1000 cd/m2 brightness, dual NTSC tuners</p>

<p><u>HDTV Monitors Renaissance Series</u><br />
43"	HVM-42A3	$6000, 1024x768<br />
50"	HVM-50S2	$9000, 1366x768</p>

<p><br />
<h2>Marantz</h2><br />
Sep 04 (CEDIA announcement)<br />
Black bezel, HDMI, monitor only, TTM Sep 04<br />
42"	PD 4230	$6000, EDTV<br />
42"	PD4250	$9000<br />
50"	PD5050	$11000</p>

<p><br />
<h2>Maxx Products</h2><br />
TTM current, component, DVI, D-Sub<br />
TruVision 42		$4500, 852x480, 3000:1 CR<br />
TruVision 42HD	$6500, 1366x768, 3000:1 CR<br />
TruVision 50HD	$7000, 1366x768, 3000:1 CR</p>

<p><br />
<h2>Mitsubishi</h2><br />
April 2004 (company announcement of 2004/5 models)</p>

<p><u>Plasmas Medallion Series</u><br />
TTM Oct 04<br />
42"	PD-4245	$5000, 480x852 EDTV, MonitorLink DVI, speakers, stand<br />
50"	PD-5050	$8500, 768x1365 HD monitor, HDMI <br />
<img src="/articles/images/mt/image063.jpg" alt=""><br />
61"	PD-6150	$18000, 768x1365 HD monitor, HDMI, improved brightness and contrast</p>

<p><br />
<h2>Motorola</h2><br />
Feb 04<br />
TTM middle 2004<br />
42"	PG-H42	$6000, 1024x768, 3000:1 CR<br />
42"	PD-S42	$3500, 852x480 EDTV, 1000:1 CR</p>

<p><br />
<h2>Moxell</h2><br />
<u>CES 2005</u><br />
<u>Proview line</u><br />
Faroudja video processing, HDMI inputs, some w/integrated ATSC tuner<br />
42"	MH-422HU	$3500, TTM Jan 05, HDTV, monitor only<br />
42"	MH-422SU	$2200, TTM now, EDTV, monitor only<br />
46"	MH-462SU	$3200, TTM now, EDTV, monitor only <br />
46"	MH-463SU	$3500, Apr 05, EDTV 852x480<br />
46"	MH-463HU	$4500, May 05, HDTV 1024x 768<br />
50"	GP-650D	$6200 (also reported as $5200), Feb 05, 1366x768, monitor only</p>

<p><br />
<h2>NEC</h2><br />
Sep 04 (CEDIA announcement)<br />
Four plasma monitor panels are available now, DVI/HDCP, RGBHV, and HD component: <br />
42"	42VR5		$3000, EDTV 853x480<br />
42"	42XR3		$5800, HDTV 1024x768<br />
50"	50XR4		$8000, 1365x768<br />
61"	61XR3		$15000, 1365x768</p>

<p><br />
<h2>Optoma</h2><br />
<u>CES 2005</u><br />
50"	SVP5F		$6000, TTM Jan 05, 1366x768, 400 Nits of brightness, 3000:1 CR, component, DVI/HDCP, 15-pin D-sub RGB</p>

<p><br />
<h2>Panasonic</h2><br />
Sep 04<br />
<u>ONYX XVS series</u><br />
Next generation plasma products<br />
Two piece configuration w/Media Box outboard, ATSC/NTSC/QAM CableCARD tuners, two component video, HDMI/HDCP, PC input, 16-watt detachable speakers, PCMCIA flash memory cards.<br />
42"	TH-42XVS30V		$7500, TTM Oct, 1024x768<br />
50"	TH-50XVX30V		$9500, TTM Oct, 1366x768  <br />
<img src="/articles/images/mt/image064.gif" alt="TH-65XVSS30V"><br />
65"	TH-65XVSS30V	$20000, TTM Nov 04, 1366x768  </p>

<p>Panasonic has cut the prices of the following integrated plasmas as of Sep 04:<br />
37"	TH-37PX25U  		$4000<br />
42"	TH-42PX25U 		$5500<br />
50"	TH-50PX25U 		$7500</p>

<p><u>EDTVs</u><br />
37"	TH-37PD25U 		down to $2500<br />
42"	TH-42PD25U 		down to $3000</p>

<p>According to Panasonic's marketing information, the company has 23 % of plasma market in all channels (CEDIA Sep 04)</p>

<p>Oct 04<br />
<u>Viera</u><br />
65"	TH-65DX300A		$23000 in Japan, 3000:1 CR, 1366x768, 60000 hrs life</p>

<p><u>CES 2005</u><br />
Introduced six new plasma panels<br />
ATSC/NTSC/QAM CableCARD tuners, EPG, PCMCIA and SD memory card slots, HDMI/HDCP, 3000:1 CR, 8.6 million colors, sub-pixel control increases horizontal resolution by 30% over previous models</p>

<p><u>PX500 Series</u><br />
TTM Jun 05<br />
42"	TH-42PX500U <br />
<img src="/articles/images/mt/image065.gif" alt="TH-50PX500U"><br />
50"	TH-50PX500U	</p>

<p><u>PX50 Series</u><br />
37"	TH-37PX50U	TTM Mar05<br />
42"	TH-42PX50U	TTM Apr 05<br />
50"	TH-50PX50U	TTM Apr 05</p>

<p><u>PD50 Series</u><br />
42"	TH-42PD50U	TTM Mar 05 </p>

<p>And an oversized 65" panel<br />
65"	TH-65PHD7UY	$16000</p>

<p><br />
<h2>Philips</h2><br />
Jun 04<br />
<u>Ambilight series</u><br />
42"	42PF9976	$5500<br />
42"	42PF9966	$6500, TTM Jul 04<br />
50"	50PF9966	$8800, TTM Jul 04</p>

<p><br />
<h2>Pioneer</h2><br />
Jun 04<br />
Six new plasmas to be introduced, two are with digital cable ready (DCR), Pure Vision line, Pure Drive technology for converting analog sources to digital, QAM integrated tuner, Passport on-screen EPG, two HDMI/HDCP, two 1394, $ N/A, TTM Sep 04:<br />
43"	PDP-4345HD XGA 1024x768<br />
50"	PDP-5045HD 1280x768</p>

<p>Elite versions in the same sizes were announced but not introduced. </p>

<p>Sep 04<br />
<u>Pure Vision Elite</u><br />
<u>Integrated panels</u><br />
XGA, TTM Nov 04, HDMI/HDCP, ATSC/dual NTSC, QAM CableCARD tuners, Pioneer's Passport Echo EPG <br />
43"	PRO-920HD	$11500 (also announced as 10500)<br />
50"	PRO-1120HD	$15500 (also announced as 13500)</p>

<p><u>Monitors</u><br />
TTM Nov 04, two HDMI/HDCP<br />
43"	PRO-810HD	$9000<br />
50"	PRO-1010HD	$13000 (also announced as 12000)<br />
61"	PRO-1410HD	$20560 in Japan, 1365x768, 135 pounds, Advanced Pure Cinema for film-based content that employs 3:3 film detection at 72 Hz, Pure Color Filter II to improve color performance</p>

<p>Oct 04<br />
43"	PDP-434CMX<br />
50"	PDP-504CMX<br />
61"	PDP-614CMX</p>

<p><u>CES 2005</u><br />
<u>50HD Series</u><br />
Two piece design, ATSC/NTSC/QAM CableCARD tuners, HDMI<br />
43"	PDP-4350HD	$7000, TTM May 05, 1024x768, 1100 cd/m2 brightness<br />
50"	PDP-5050HD	$9000, TTM Jun 05, 1280x768, 1000 cd/m2 brightness</p>

<p><u>A5HD Series</u><br />
One-piece chassis integration, ATSC/NTSC/QAM CableCARD tuners<br />
43"	PDP-43A5HD	$TBA, TTM May 05, 1024x768, 1100 cd/m2 brightness<br />
50"	PDP-50A5HD	$TBA, TTM May 05, 1280x768, 1000 cd/m2 brightness, HDMI</p>

<p><u>EDTV monitor</u><br />
42"	PDP-4200ED	$2500, 853x480, DVI/HDCP</p>

<p><br />
<h2>Planar</h2><br />
May 04<br />
(Model included because it was missing on my 2004 report)<br />
42"	PDP42HD under $4000, 1024x768, 800:1 CR, 750 cd/m2 brightness, comp, DVI, RGB 15 pin, TTM now</p>

<p><br />
<h2>Plastract</h2><br />
In Mar 04, the company introduced a new plasma panel that permits the viewing of 4:3 images using a retractable top to avoid the use of black bars when detecting the incoming signal, www.noblackbars.com  <br />
42"	$TBA, TTM Apr 04, 1366x768 to 1366x1366</p>

<p><br />
<h2>Polaroid</h2><br />
42"	PLA-4260	$3300, TTM Jan 05, 1024x768, DVI/HDCP, component</p>

<p><br />
<h2>Revox</h2><br />
32"	E1032		$8000, TTM current, 1024x852, component BNC, DVI<br />
42"	E1042		$10000, TTM current, 1024x1024, component BNC, DVI<br />
50"	E1050		$14000, TTM current, 1280x768, component BNC, DVI</p>

<p><br />
<h2>Runco</h2><br />
<u>CES 2005</u><br />
<u>Cinema Wall Monitor panels</u><br />
Vivix Processing<br />
42"	CW-42HD	1024x768, high alt to 9000 feet, DVI/HDCP, 1000:1 CR, RGB<br />
42"	CW-42i	853x480, high alt to 9000 feet, DVI/HDCP, 1000:1 CR, RGB<br />
43"	CW-43MC	1024x768, DVI/HDCP, RGB, 1000:1 CR, controller included<br />
50"	CW-50mc	1280x768, DVI/HDCP, controller, 1000:1 CR, RGB<br />
50"	CW-50xa	1365x768, DVI/HDCP, high alt to 9000 feet, RGB<br />
61"	CW-61		1366x768, DVI/HDCP, high alt to 9000 feet, 1000:1 CR, RGB</p>

<p><br />
<h2>Samsung</h2><br />
Feb 04<br />
42"	SPP4231	$4300, TTM Apr 04, 852x480, 3000:1 CR<br />
42"	SPP4251	$5000, TTM Apr 04<br />
42"	HPN4259	$6500, TTM Apr 04, 1024x768<br />
50"	HPP5061	$9000, TTM summer 04</p>

<p>Oct 04<br />
42"	PPM42SQ3	$N/A<br />
50"			$9000, TTM fall 04, 802.11a wireless connection up to 30 feet to STB, NTSC tuner in the STB<br />
63"	PPM63WQ3	$N/A<br />
80"			Showed again as a prototype, introduced at CES 2004</p>

<p><u>Integrated panels</u> (55" w/DTV tuners)<br />
37"	HPP3761	$4000, dual NTSC tuners, 1000:1 CR, 1000 cd/m2 brightness<br />
42"	HPP4261	$5500, dual NTSC tuners, 900:1 CR, 1366x768, 1000 cd/m2<br />
55"	HPP5581	$10000, QAM CableCARD and ATSC tuners, panel from Fujitsu/Hitachi factory, TTM Oct 04, 3000:1 CR, 1000 cd/m2 brightness, DVI/HDCP <br />
63"</p>

<p>Samsung abandoned their efforts for ultra-wide band UWB technology and rather supported 802.11a to connect wirelessly plasma panels with STBs. </p>

<p>Dec 2004<br />
Samsung announced their development of a 102" plasma display with 1920x1080 pixels of resolution, 1000 candelas of brightness, and 2000:1 CR.  The company declared that they are planning to invest about 30 billion won on the initial production of panels of 80 and 102 inches, expected by 1H05. </p>

<p><u>CES 2005</u><br />
The company showed their large plasma displays in the 80 and 102 inches range:</p>

<p>80" 	HPR8072	$39000  (also quoted informally as $50000 at the CES booth), TTM May 05, 1920x1080p, 68.7 billion color display capability, 12-bit video processing, integrated w/ATSC and QAM Cable CARD tuners, DNIe, 1500 cd/m2 brightness, 5000:1 CR, DNIe, Anynet chip for home networking. </p>

<p>102"	Z-102		$80000/$90000 unofficial MSRP estimate, shown as the largest plasma in existence, available in about two years from CES 2005, 1920x1080p</p>

<table><tr><td align="center">
<img src="/articles/images/mt/image066.jpg" alt="Samsung 80 inch Plasma HPR8072">
</td><td align="center">
<img src="/articles/images/mt/image067.jpg" alt="Samsung 102 inch Plasma Z102">
</td></tr><tr><td align="center">
Samsung 80" Plasma HPR8072
</td><td align="center">
Samsung 102" Plasma Z102
</td></tr></table>

<p>And a 50" panel:<br />
50"	HPR5072	$7000, TTM Apr 05, 175 degree viewing</p>

<p><br />
<h2>Sanyo</h2><br />
<u>CES 2005</u><br />
42"	PDP-42H2A	1024x1024 monitor, viewing angle 175", DVI/HDCP, component, RGB VGA D-sub 15 pin</p>

<p><br />
<h2>Sony</h2><br />
Jun 04 (company announcement of 2004/5 models)</p>

<p><u>Plasma XS Series Integrated</u><br />
Digital Cable Ready, third-generation WEGA engine image processing, TTM Aug 04<br />
37"	KDE-37XS955		$5500, 1024x1024<br />
42"	KDE-42XS955		$7000, 1024x1024<br />
50"	KDE-50XS955		$9000, 1366x768, integrated with QAM Unidirectional CableCARD/ATSC/NTSC tuners, swivel stand included, 100Watt digital amp and 50W subwoofer</p>

<p><u>Flat Panel Monitor</u><br />
42"	KE-42M1	$5000, TTM Jun 04, 480p EDTV, LSI for contrast improvement</p>

<p><br />
<h2>Thomson</h2><br />
May 04 (company announcement of 2004/5 models)</p>

<p><u>Plasma Flat Panels</u><br />
Thomson discontinued the plasma line; their supplier (NEC) was acquired by Pioneer.  Thomson will concentrate on DLP and LCD.</p>

<p><br />
<h2>Toshiba</h2><br />
Jun 04 (company announcement of 2004/5 models)</p>

<p><u>Cinema series monitor</u><br />
42"	42HPX84	$6000, TTM Sep 04, HDMI, 1024x768</p>

<p><u>TheaterWide series monitors</u><br />
42"	42HP84	$5500, TTM Sep 04, 1024x768<br />
50"	50HP84	$7500, TTM Oct 04, 1024x768</p>

<p><br />
<h2>V, Inc</h2><br />
42"	Vizio P2	$3000, TTM current, 480px852, DVI-D, RGB 15-pin, component<br />
42"	P42		$2000, TTM current, 480px852, DVI/HDCP, component, RGB <br />
42"	Vizio P42HD	$2500, TTM current, 1024x768, DVI/HDCP, component, RGB<br />
46"	Vizio P4	$3800, TTM current, 480px852, DVI-D, RGB 15-pin, component<br />
46"	P46		$2500, TTM current, 480px852, DVI-D, component, RGB 15-pin</p>

<p><br />
<h2>Vidrikon</h2><br />
Sep 04<br />
<u>Plasmaview monitors</u><br />
42"	VP-42		$4000, 853x480 EDTV, BNC, compon., DVI/HDCP, RGB 15-pin<br />
42"	VP-42HD	$4000, 1024x768, RGB BNC & 15 pin, component, DVI/HDCP <br />
50"	VP-50		$10000, 1365x768, C BNC & 15 pin, component, DVI/HDCP<br />
60"	VP-60		originally $20000, 1365x768, 3:2 pull-down, anti-burn circuitry</p>

<p><br />
<h2>Viewsonic</h2><br />
Jun 04 (InfoComm)<br />
42"	VPW4255	$5,500, 1024x1024, 1000:1 CR, 1100 nits, DVI/HDCP, TTM Jul 2004, 160 degrees of viewing angle, RGB, monitor</p>

<p>Sep 04 (CEDIA)<br />
55"	VPW5500	$10000, TTM Sep 04, 1366x768, 160 degrees viewing angle</p>

<p>Oct 04<br />
42"	VPW450HD	AliS, NTSC tuner</p>

<p>CES 2005<br />
42"	VPW4200	$3000, TTM Jan 05, 852x480, DVI/HDCP, component, RGB VGA</p>

<p><br />
<h2>Yamaha</h2><br />
Sep 04 (CEDIA)<br />
55"	PDM-5520	TTM Dec 04, 1366x768, 1000cd/m2 brightness, Fujitsu's e-AliS technology, 1000:1 CR</p>

<p>Be sure that you read the next article in the series: LCD TV Panels (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 17, 2005  5:23 PM</b>
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
			<?=getComments(219)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 219)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-8-plasma-panels.php" type="text/javascript" charset="utf-8"></script>
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