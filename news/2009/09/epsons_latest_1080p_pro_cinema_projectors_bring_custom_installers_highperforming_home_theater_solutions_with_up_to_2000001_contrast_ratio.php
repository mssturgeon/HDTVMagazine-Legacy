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
		AND e.entry_id = 3240";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3240 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3240 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3240";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/09/epsons-latest-1080p-pro-cinema-projectors-bring-custom-installers-highperforming-home-theater-solutions-with-up-to-2000001-contrast-ratio.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3240";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Epson\'s Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Epson\'s Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Epson\'s Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio" />
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
	<title>HDTV Magazine - Epson's Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio</title>
	<meta name="keywords" content="pro cinema, light output, epson america, image quality, contrast ratio, epson, cinema, pro, color, technology, quality, high, light, home, image, projectors, features, output, installers, lcd, lamp, contrast, custom, america, video" />
	<meta name="description" content="CEDIA Expo 2009, Booth 3353 -- Epson America today announced two native 1080p 3LCD(TM) home theater projectors designed for custom installers and home theater buffs, the PowerLite Pro Cinema 9100 and 9500 UB. These projectors feature the latest 3LCD chips with D7 technology for amazing color and detail, and significantly higher contrast ratios - the Pro Cinema 9100 achieves a 36,000:1 dynamic contrast ratio and the Pro Cinema 9500 UB attains an unprecedented 200,000:1 in its class (i). With professional-level color tools including ISF calibration and color isolation, the Pro Cinema 9100 and 9500 UB offer professional installers full-featured solutions.

Available for..." />
	<meta name="title" content="Epson's Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Epson's Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/09/epsons-latest-1080p-pro-cinema-projectors-bring-custom-installers-highperforming-home-theater-solutions-with-up-to-2000001-contrast-ratio.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="CEDIA Expo 2009, Booth 3353 -- Epson America today announced two native 1080p 3LCD(TM) home theater projectors designed for custom installers and home theater buffs, the PowerLite Pro Cinema 9100 and 9500 UB. These projectors feature the latest 3LCD chips with D7 technology for amazing color and detail, and significantly higher contrast ratios - the Pro Cinema 9100 achieves a 36,000:1 dynamic contrast ratio and the Pro Cinema 9500 UB attains an unprecedented 200,000:1 in its class (i). With professional-level color tools including ISF calibration and color isolation, the Pro Cinema 9100 and 9500 UB offer professional installers full-featured solutions.

Available for..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3240', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/09/epsons-latest-1080p-pro-cinema-projectors-bring-custom-installers-highperforming-home-theater-solutions-with-up-to-2000001-contrast-ratio.php">Epson's Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Epson's Latest 1080p Pro Cinema Projectors Bring Custom Installers High-Performing Home Theater Solutions with up to 200,000:1 Contrast Ratio</p>

<center><i>PowerLite Pro Cinema 9100 and 9500 UB Offer New Levels of Image Quality, Detail and Performance</center></i><br />
<br />

<p><strong>ATLANTA, Sept. 8 /PRNewswire-FirstCall/ -- </strong>CEDIA Expo 2009, Booth 3353 -- Epson America today announced two native 1080p 3LCD(TM) home theater projectors designed for custom installers and home theater buffs, the PowerLite Pro Cinema 9100 and 9500 UB. These projectors feature the latest 3LCD chips with D7 technology for amazing color and detail, and significantly higher contrast ratios - the Pro Cinema 9100 achieves a 36,000:1 dynamic contrast ratio and the Pro Cinema 9500 UB attains an unprecedented 200,000:1 in its class (i). With professional-level color tools including ISF calibration and color isolation, the Pro Cinema 9100 and 9500 UB offer professional installers full-featured solutions.</p>

<p>Available for $2,599 and sub-$4,000 respectively, the Pro Cinema 9100 and 9500 UB offer state-of-the-art image quality and performance in each of their respective categories with enhanced color reproduction capabilities and 3LCD technology to deliver bright and natural color, crisp image detail and reliability. As Epson's flagship home theater model, the Pro Cinema 9500 UB brings several technology enhancements to the market, including a new dual-layered auto-iris to control light reduction rates, Super-resolution(TM) technology for enhanced picture quality and improved FineFrame(TM) technology.</p>

<p>"Epson understands the needs of the custom installation channel and is committed to providing high-quality products that meet the needs of dealers in today's competitive market," said Marge Ang, senior product manager, Epson America. "These latest Pro Cinema projectors have been packed with a range of value-add features and technology refinements that allow custom installers to build a customized high-performance, home entertainment system for their discerning customers."</p>

<p><br />
<strong>Epson Pro Cinema 9100</strong></p>

<p>The Pro Cinema 9100 offers a high-value, customizable home entertainment experience. The projector includes ISF Day and Night modes for switching between picture modes and lamp output, as well as Epson's Color Isolation system for fine-tuning of color saturation and hue without the need for blue and red optical filters, making set-up and calibration quick and simple. With a contrast ratio of up to 36,000:1 and brightness of 1,800 lumens color and white light output (ii), this projector offers amazing big-screen image quality. Housed in a stylish black and silver design, the Pro Cinema 9100 features an exclusive Dynamic Iris system which contributes to the improvement in contrast. It controls light on a frame-by-frame basis at up to 60 times per second, making it ideal for fast-action movies with frequent scene changes.</p>

<p><br />
<strong>Epson Pro Cinema 9500 UB</strong></p>

<p>The flagship Pro Cinema 9500 UB offers custom installers the ultimate in image quality, combining Epson's C2Fine and UltraBlack(TM) technology with 3LCD and D7 technology to produce superb black levels and vivid images. The projector also features a dual-layered auto iris to control light reduction rates more effectively, contributing to its astounding contrast ratio - up to 200,000:1. With brightness of 1,600 lumens color and white light output, the projector delivers deeper blacks and crystal clear detail.</p>

<p>Adding to its high-quality performance, the Pro Cinema 9500 UB features Epson's improved FineFrame technology for smoother frame interpolation, sharper video quality and the elimination of judder for viewing sports and fast-action movie scenes, and playing video games. Epson has also added new Super-resolution technology to sharpen blurry images or low-resolution pictures. This new technology is ideal for high-definition content, addressing broadcast signal noise reduction or improving the quality of poor DVD transfers.</p>

<p>The Pro Cinema 9500 UB also features new real color reproduction capabilities for more natural and precise colors, while Epson's 12-bit 3LCD driver technology and a built-in Silicon Optix HQV Reon-VX video processor and PW390 scaler ensure a smoother picture. It also includes pre-set color space selection to set color gamut according to geography and source material, a feature typically found only on high-end high-definition broadcast monitors. And, with an optional external anamorphic lens (available from Epson) and "vertical stretch" picture mode, the Pro Cinema 9500 UB enables anamorphic viewing without an external processor for a full theatrical image - projecting true 2.35:1 and 2.40:1 ultra widescreen images without the black bars above and below the picture.</p>

<p>Shared Features of the Pro Cinema Line - Epson's Pro Cinema projectors share a range of value-add features designed to further enhance performance, image quality and total cost of ownership, including:</p>

<ul><li>3LCD Technology: Delivers bright and natural color, amazing detail and road-tested reliability; uses an advanced, 3-chip optical engine for full-time color without the possibility of color break-up.</li><li>Cinema Filter: Delivers larger color space for improved color fidelity.</li><li>Fujinon OptiCinema(TM) Multi-Lens Optics System: Projects clean, precise edges with consistent image quality across the entire screen.</li><li>E-TORL  (Epson Twin Optics Reflection Lamp): Provides optimum light uniformity and increased light output for screen sizes larger than ten feet with exclusive 200 watt high efficiency design; uses less energy for up to 4,000 hours of lamp life(iii); both models include a spare lamp.</li><li>ISF Certification:  Allows installers and calibrators to fine-tune picture quality and match output with front projection screens.</li><li>Input Selections: Features two HDMI 1.3a inputs with Deep Color support, high definition component video input, S-video input, composite video input, and VGA-type RGB input (D-sub 15).</li><li>Installation Options: Includes ceiling mount, reversible front panel Epson logo for various mounting positions, rear panel cable cover to hide wires and cable hook to ensure connections remain secure.</li></ul>

<p><br />
<strong>Availability and Support</strong></p>

<p>Available in October and November respectively, the Pro Cinema 9100 and 9500 UB can be purchased through authorized Epson projector dealers and select retail outlets. Both models come with Epson's industry leading service and support, including a three-year limited warranty with toll-free access to Epson's PrivateLine(SM) priority technical support, 90-day limited lamp warranty, and free two-business day exchange with Extra Care(SM) Home Service.</p>

<p><br />
<strong>About Epson America Inc.</strong></p>

<p>Epson America Inc. is the U.S. affiliate of Japan-based Seiko Epson Corporation (SEC) and is a leading provider of digital imaging products that exceed the vision of its customers. The company's extensive range of printers, 3LCD projectors and small- and medium-sized LCDs are renowned for their superior quality, functionality, compactness, and energy efficiency. The Seiko-Epson organization is proud of its ongoing contributions to the global environment and was recently added to the Dow Jones Sustainability World Index, an indicator for leading companies in economic, environmental and social criteria.</p>

<p>Note: Epson, C2Fine, E-TORL are registered trademarks of Seiko Epson Corporation. PowerLite and PrivateLine are registered trademarks, FineFrame, OptiCinema and UltraBlack are trademarks, and Extra Care is a service mark of Epson America Inc. All other product and brand names are trademarks and/or registered trademarks of their respective companies. Epson disclaims any and all rights in these marks.</p>

<p>  (i)   Home entertainment projectors under $4,000.<br />
  (ii)  Light output varies depending on modes (color and white light<br />
        output).  White light output measured using ISO 21118 standard.<br />
  (iii) Lamp life will vary depending upon mode selected, environmental<br />
        conditions and usage.  Lamp brightness decreases over time.</p>

<p>Source: Epson America Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  8, 2009  7:16 AM</b>
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
			<?=getComments(3240)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3240)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/09/epsons-latest-1080p-pro-cinema-projectors-bring-custom-installers-highperforming-home-theater-solutions-with-up-to-2000001-contrast-ratio.php" type="text/javascript" charset="utf-8"></script>
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