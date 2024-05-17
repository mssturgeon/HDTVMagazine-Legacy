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
		AND e.entry_id = 4665";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4665 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4665 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4665";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/02/canon-usa-introduces-the-realis-wux5000-and-wux5000-d-its-brightest-installation-lcos-projectors-to-date-for-the-professional-av-market.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4665";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market" />
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
	<title>HDTV Magazine - Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market</title>
	<meta name="keywords" content="realis wux, installation lcos, lcos projectors, wux wux, canon realis, canon, wux, realis, installation, projector, lcos, projectors, display, control, new, lens, high, users, products, color, resolution, lenses, business, convenience, professional" />
	<meta name="description" content="Canon U.S.A., Inc., a leader in digital imaging solutions, has introduced its brightest-ever REALiS Installation LCOS Projectors, which are designed to provide exceptional display performance for a wide range of professional AV markets. Incorporating multiple features for convenient installation and maintenance, the new REALiS WUX5000 and WUX5000 D Installation LCOS Projectors utilize Canon's unique fourth-generation AISYS (Aspectual Illumination System) optical technology to maximize the display capabilities of their advanced LCOS (Liquid Crystal on Silicon) imaging panels. This combination enables the projectors to deliver higher-than-HD-resolution (1920 x 1200) widescreen video and still images with an aspect ratio of 16:10 and a high brightness level of 5000 lumens. The 5000 lumen brightness level improves display quality in long throw venues or rooms with relatively high ambient light levels, thereby increasing the versatility of the new projectors for a wider variety of viewing environments.

The new Canon REALiS WUX5000 and REALiS WUX5000 D LCOS Projectors expand Canon's line of high-performance products for the installation market. Additional Canon projectors in this category include..." />
	<meta name="title" content="Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/02/canon-usa-introduces-the-realis-wux5000-and-wux5000-d-its-brightest-installation-lcos-projectors-to-date-for-the-professional-av-market.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Canon U.S.A., Inc., a leader in digital imaging solutions, has introduced its brightest-ever REALiS Installation LCOS Projectors, which are designed to provide exceptional display performance for a wide range of professional AV markets. Incorporating multiple features for convenient installation and maintenance, the new REALiS WUX5000 and WUX5000 D Installation LCOS Projectors utilize Canon's unique fourth-generation AISYS (Aspectual Illumination System) optical technology to maximize the display capabilities of their advanced LCOS (Liquid Crystal on Silicon) imaging panels. This combination enables the projectors to deliver higher-than-HD-resolution (1920 x 1200) widescreen video and still images with an aspect ratio of 16:10 and a high brightness level of 5000 lumens. The 5000 lumen brightness level improves display quality in long throw venues or rooms with relatively high ambient light levels, thereby increasing the versatility of the new projectors for a wider variety of viewing environments.

The new Canon REALiS WUX5000 and REALiS WUX5000 D LCOS Projectors expand Canon's line of high-performance products for the installation market. Additional Canon projectors in this category include..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4665', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/02/canon-usa-introduces-the-realis-wux5000-and-wux5000-d-its-brightest-installation-lcos-projectors-to-date-for-the-professional-av-market.php">Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>February  1, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=495&category=Front Projection">Front Projection</a></b>
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
				<p class="prtitle">Canon U.S.A. Introduces the REALiS WUX5000 and WUX5000 D, its Brightest Installation LCOS Projectors to Date for the Professional AV Market</p>

<center><i>Convenient Design, 5000-Lumen Brightness, Unique Canon LCOS Display Engine, and a Choice of Canon Projection Lenses Combine For Seamless, Higher-Than-HD Resolution Image Quality</center></i><br />
<br />

<p><strong>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)--</strong>Canon U.S.A., Inc., a leader in digital imaging solutions, has introduced its brightest-ever REALiS Installation LCOS Projectors, which are designed to provide exceptional display performance for a wide range of professional AV markets. Incorporating multiple features for convenient installation and maintenance, the new REALiS WUX5000 and WUX5000 D Installation LCOS Projectors utilize Canon's unique fourth-generation AISYS (Aspectual Illumination System) optical technology to maximize the display capabilities of their advanced LCOS (Liquid Crystal on Silicon) imaging panels. This combination enables the projectors to deliver higher-than-HD-resolution (1920 x 1200) widescreen video and still images with an aspect ratio of 16:10 and a high brightness level of 5000 lumens. The 5000 lumen brightness level improves display quality in long throw venues or rooms with relatively high ambient light levels, thereby increasing the versatility of the new projectors for a wider variety of viewing environments.</p>

<p>The new Canon REALiS WUX5000 and REALiS WUX5000 D LCOS Projectors expand Canon's line of high-performance products for the installation market. Additional Canon projectors in this category include the REALiS WUX4000, REALiS WUX4000 D, and the LV-7590.</p>

<p>"The REALiS WUX5000 series is ideal for end-users in such markets as education, business, and government, as well as for professionals in photo studios, film and TV production facilities, theme parks, and museums," noted Yuichi Ishizuka, executive vice president and general manager, Imaging Technologies and Communications Group, Canon U.S.A. "The introduction of these new models firmly plants Canon within many professional markets and we will continue to produce high-quality products that users demand."</p>

<p>Capable of displaying many different kinds of high-resolution video and computer signals with highly accurate color, superior display of movement, crisp contrast, and sharper blacks, the REALiS WUX5000 Installation LCOS Projector features a built-in Canon Color-Correction IC (integrated circuit) and 3D color LUT (look-up table) for six-axis color management. Users can adjust specific colors without affecting the display of neighboring colors. A dynamic gamma feature can also optimize gradation reproduction and contrast, which can be beneficial when displaying movies with low-contrast scenes. A Memory Color Correction feature enables users to save their preferred color settings. User-determined display settings for the REALiS WUX5000 Installation LCOS Projector also include Presentation, Vivid Photo, and Photo/sRGB.</p>

<p>The REALiS WUX5000 D Installation LCOS Projector has all the features of the WUX5000 model plus a DICOM&reg; Simulation mode designed to reproduce medical images such as X-Rays, CAT scans and MRI images with superb 21-step grayscale gradation.</p>

<p><br />
<strong>Optional Projection Lenses</strong></p>

<p>Further contributing to the display excellence – and installation convenience – of the new Canon REALiS WUX5000 and WUX5000 D Installation LCOS Projectors are the three optional high-resolution interchangeable lenses available for the units. These lenses – including a 1.5x Zoom, 0.8x Ultra Wide Angle single focal length, and 1.7x Long Focus Zoom – incorporate advanced low-distortion and high-resolution optical technologies originally developed for Canon's camera lenses. Engineered to minimize brightness loss, each lens is the same physical length, due to the use of advanced Canon aspherical elements. This uniformity of length can simplify shipment of assembled projector/lens units for rental/staging companies, and avoid lens-overhang issues when mounted inside projector-lift systems.</p>

<p><br />
<strong>Motorized Lens Shift and Installation Convenience</strong></p>

<p>Additional installation convenience is provided by the Canon REALiS WUX5000 and WUX5000 D Installation LCOS Projectors' motorized Lens Shift feature. This enables installers to overcome moderate projector-placement obstacles by shifting the lens itself to adjust the vertical and horizontal position of the projected image. (This is done using the projector's control panel or remote control.) The projector can be ceiling-mounted with a plate or extension pole using either Canon's mounting bracket or products from independent ceiling-mount companies. When used in multi-projector configurations, the REALiS WUX5000 and WUX5000 D can also be stacked or installed side by side.</p>

<p>The projector's lamp and air filter assemblies can be expediently replaced from the back and side of the projector respectively, and its optical filter element can be replaced from the top of the unit for added convenience and economy.</p>

<p><br />
<strong>Multimedia Device Compatibility</strong></p>

<p>Industry-standard digital and analog terminals and connectors ensure the new Canon REALiS WUX5000 and WUX5000 D Installation LCOS Projectors' compatibility with a wide range of video and computer devices. Signal inputs include HDMI Version 1.3, DVI-D and analog PC terminals, two audio inputs, and one audio output for external amplification and speakers. A built-in five-watt speaker is also included. A network-ready RJ-45 port allows for authorized users to manage and control multiple projectors from any PC on the network. An RS-232 serial connection allows for monitoring the projector locally through a third-party control system. A hand-held remote control enables users to control up to four REALiS WUX5000 and WUX5000 D Installation LCOS Projectors individually or simultaneously. Lens Shift and Gamma Control buttons are also included on the remote control.</p>

<p><br />
<strong>Price and Availability</strong></p>

<p>The Canon REALiS WUX5000 and WUX5000 D Installation LCOS Projector have suggested list prices of $8,999 and $9,999 respectively. The new products are scheduled to be available through authorized Canon dealers in March, 2012.</p>

<p>The REALiS WUX5000 and WUX5000 D Installation LCOS Projectors are also backed by Canon USA's Three-Year Limited Warranty and exclusive Projector Protection Program ("Triple P"). Triple P is a FREE service program that provides a loaner projector of equal or greater value in the event that a qualifying unit is in need of repair. Triple P is available on all Canon projector models during the Three-Year Canon USA Limited Warranty period.</p>

<p>For more information please visit <a target="_blank" href="http://www.usa.canon.com/projectors/">www.usa.canon.com/projectors</a>.</p>

<p><br />
<strong>About Canon U.S.A., Inc.</strong></p>

<p>Canon U.S.A., Inc., is a leading provider of consumer, business-to-business, and industrial digital imaging solutions. With more than $45 billion in global revenue, its parent company, Canon Inc. (NYSE:CAJ), ranks fourth overall in patent holdings in the U.S. in 2010† and is one of Fortune Magazine's World's Most Admired Companies in 2011. Canon U.S.A. is committed to the highest levels of customer satisfaction and loyalty, providing 100 percent U.S.-based consumer service and support for all of the products it distributes. Canon U.S.A. is dedicated to its Kyosei philosophy of social and environmental responsibility. To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting <a target="_blank" href="http://www.usa.canon.com/rss/">www.usa.canon.com/rss</a>.</p>

<p><em>†Based on weekly patent counts issued by United States Patent and Trademark Office.</p>

<p>All referenced product names, and other marks, are trademarks of their respective owners.</p>

<p>DICOM is the registered trademark of the National Electrical Manufacturers Association for its standards publications relating to digital communications of medical information.</p>

<p>Availability, prices, and specifications of all products are subject to change without notice. Actual prices are set by individual dealers and may vary.</em></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>February  1, 2012  7:19 PM</b>
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
			<?=getComments(4665)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4665)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/02/canon-usa-introduces-the-realis-wux5000-and-wux5000-d-its-brightest-installation-lcos-projectors-to-date-for-the-professional-av-market.php" type="text/javascript" charset="utf-8"></script>
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