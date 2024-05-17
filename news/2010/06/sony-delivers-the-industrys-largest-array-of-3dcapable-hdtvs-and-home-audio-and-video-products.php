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
		AND e.entry_id = 3782";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3782 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3782 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3782";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/06/sony-delivers-the-industrys-largest-array-of-3dcapable-hdtvs-and-home-audio-and-video-products.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3782";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony Delivers the Industry\'s Largest Array of 3D-Capable HDTVs and Home Audio and Video Products" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony Delivers the Industry\'s Largest Array of 3D-Capable HDTVs and Home Audio and Video Products" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony Delivers the Industry\'s Largest Array of 3D-Capable HDTVs and Home Audio and Video Products" />
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
	<title>HDTV Magazine - Sony Delivers the Industry's Largest Array of 3D-Capable HDTVs and Home Audio and Video Products</title>
	<meta name="keywords" content="blu ray, bravia xbr, ray disc, home entertainment, led backlight, sony, bravia, blu, ray, home, xbr, models, hdtvs, video, series, capable, inch, integrated, products, full, kdl, players, theater, audio, update" />
	<meta name="description" content="Sony today announced that its 3D-capable BRAVIA&amp;reg; HDTVs are now available for pre-sale at Sony Style stores and that its new integrated Blu-ray 3D(TM) devices will hit retail shelves beginning this July. Additionally, the company released a free firmware update that activates Blu-ray 3D capability for previously announced Blu-ray Disc models including the BDP-S470 and BDP-S570 players and the BDV-E570 and BDV-E770W home theater systems.

Sony now offers consumers..." />
	<meta name="title" content="Sony Delivers the Industry's Largest Array of 3D-Capable HDTVs and Home Audio and Video Products" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony Delivers the Industry's Largest Array of 3D-Capable HDTVs and Home Audio and Video Products" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/06/sony-delivers-the-industrys-largest-array-of-3dcapable-hdtvs-and-home-audio-and-video-products.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sony today announced that its 3D-capable BRAVIA&amp;reg; HDTVs are now available for pre-sale at Sony Style stores and that its new integrated Blu-ray 3D(TM) devices will hit retail shelves beginning this July. Additionally, the company released a free firmware update that activates Blu-ray 3D capability for previously announced Blu-ray Disc models including the BDP-S470 and BDP-S570 players and the BDV-E570 and BDV-E770W home theater systems.

Sony now offers consumers..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3782', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/06/sony-delivers-the-industrys-largest-array-of-3dcapable-hdtvs-and-home-audio-and-video-products.php">Sony Delivers the Industry's Largest Array of 3D-Capable HDTVs and Home Audio and Video Products</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June  9, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>
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
				<p class="prtitle">Sony Delivers the Industry's Largest Array of 3D-Capable HDTVs and Home Audio and Video Products</p>

<center><i>Pre-sales Begin for BRAVIA 3D HDTVs; 3D Upgrade For Earlier 2010 Blu-ray Models</center></i><br />
<br />

<p><strong>SAN DIEGO, June 9 /PRNewswire/ -- </strong>Sony today announced that its 3D-capable BRAVIA&reg; HDTVs are now available for pre-sale at Sony Style stores and that its new integrated Blu-ray 3D(TM) devices will hit retail shelves beginning this July. Additionally, the company released a free firmware update that activates Blu-ray 3D capability for previously announced Blu-ray Disc models including the BDP-S470 and BDP-S570 players and the BDV-E570 and BDV-E770W home theater systems.</p>

<p>Sony now offers consumers the most diverse line of 3D-capable home entertainment products including 19 BRAVIA&reg; HDTVs, Blu-ray Disc players and theater systems, and audio/video components that offer various levels of performance and features.</p>

<p>Sony will support the 3D launch with an integrated marketing campaign featuring NFL quarterback Peyton Manning of the Indianapolis Colts, and Grammy&reg; and Emmy&reg; award-winning Jive/Sony Music artist Justin Timberlake, aiming to deliver consumer education and eliminate 3D confusion.</p>

<p>"3D is revolutionizing the entertainment industry and only Sony is involved in every stage of the ecosystem," said Chris Fawcett, vice president of Sony's television business. "Leveraging deep 3D expertise from the company's theatrical and professional groups, Sony products are optimized to offer the best possible 3D home entertainment experience."</p>

<p><br />
<strong>BRAVIA 3D HDTVs</strong></p>

<p>Focusing on that high quality experience, Sony's 3D-capable BRAVIA HDTVs incorporate a frame sequential display with active-shutter glasses that work together with Sony's proprietary high frame rate technology reproducing smooth, full high-definition 3D images.</p>

<p>The line-up includes the 3D-integrated BRAVIA XBR-LX900 HDTV, which features a built-in 3D sync transmitter and two pair of active shutter glasses and the 3D ready BRAVIA XBR-HX909 and KDL-HX800 series 3D ready models which offer the option of adding the 3D sync transmitter and glasses at an additional cost.</p>

<p>The line features screen sizes including 40, 46, 52, 55, and 60-inches and ranges in price from around $2,100 (KDL-40HX800) to about $5,000 (XBR-60LX900).</p>

<p>Consumers who purchase and register one of the new 3D BRAVIA models will receive a copy of Sony Pictures Home Entertainment's Blu-ray 3D(TM) title Cloudy With a Chance of Meatballs as well as Blu-ray 3D title Deep Sea. The sets will also include a PlayStation&reg; Network voucher enabling 3D BRAVIA purchasers to download stereoscopic 3D gaming experiences on the PlayStation3 (PS3(TM)) System (sold separately). The titles include PAIN (partial game) and MotorStorm&reg;: Pacific Rift (demo) and full game downloads of WipEout&reg; HD and Super StarDust(TM) HD.</p>

<p><br />
<strong>BRAVIA XBR-LX900 Series Integrated 3D HDTVs</strong></p>

<p>The full HD 1080p (1920 x 1080) BRAVIA XBR-LX900 series features integrated 3D functionality and includes the 60-inch XBR-60LX900 for about $5,000 and the 52-inch XBR-52LX900 for about $4,000.</p>

<p>The Monolithic Design, Edge LED backlight models also feature integrated Wi-Fi&reg; (802.11n) for an easy connection to broadband home networks to access Sony's BRAVIA Internet Video Platform and BRAVIA Internet Widgets, as well as Sony's new premium video service, Qriocity.</p>

<p><br />
<strong>BRAVIA XBR-HX909 Series 3D Ready HDTVs</strong></p>

<p>The XBR-HX909 series is 3D ready with the addition of Sony active shutter glasses (about $150 per pair) and sync transmitter (about $50), both sold separately. The models feature full HD (1920 x 1080p) and Sony's Intelligent Dynamic full array LED backlight. The Monolithic Design concept models include the 52-inch XBR-52HX909 for about $4,000 and the 46-inch XBR-46HX909 for about $3,500.</p>

<p>Sony's Intelligent Dynamic LED backlighting improves contrast and dynamic range by local dimming that controls the LED backlight level by area so that detail is maintained in the dark areas, while other areas are driven near peak brightness. The models also include an RS-232c I/0 terminal for custom installation applications.</p>

<p><br />
<strong>BRAVIA KDL-HX800 Series 3D Ready HDTVs</strong></p>

<p>Also 3D ready (with the addition of Sony active shutter glasses and sync transmitter, sold separately), the BRAVIA KDL-HX800 series features full HD 1080p (1920 x 1080) and utilizes a Dynamic edge LED backlight with local dimming for improved contrast and dynamic range. The series includes the 55-inch class (54.6-inches measured diagonally) KDL-55HX800 for about $3,400, the 46-inch KDL-46HX800 for about $2,700, and the 40-inch KDL-40HX800 for about $2,100.</p>

<p><br />
<strong>Blu-ray 3D Players</strong></p>

<p>Available in July for about $300, Sony's Blu-ray 3D BDP-S770 model offers built-in Wi-Fi (802.11n) for easy access to Sony's BRAVIA Internet Video platform and Qriocity.</p>

<p>Additionally, Sony's BDP-S470 and BDP-S570 Blu-ray Disc players can be upgraded to play Blu-ray 3D with a free online firmware update that is now available. The update also adds Digital Living Network Alliance (DLNA&reg;) compatibility to the models.</p>

<p><br />
<strong>Blu-ray 3D Home Theater Systems</strong></p>

<p>Also available this July for about $800, Sony's new full HD 1080p 5.1 channel Blu-ray 3D-capable home theater system (model BDV-HZ970W) features wireless capabilities via the included USB wireless LAN adapter (802.11n) allowing for easy access to the BRAVIA Internet Video platform and Qriocity.</p>

<p>The model also offers two HDMI inputs with 3D pass-through, HDMI repeater function, and a universal remote.</p>

<p>Sony's BDV-E570 and BDV-E770W Blu-ray theater systems can now also be upgraded to Blu-ray 3D with the free firmware update. The update also adds DLNA compatibility to the units.</p>

<p>Unique to all Sony Blu-ray Disc players, users with an iPhone&reg; or iPod&reg; touch device can control the players using a free app called "BD Remote" which can be downloaded from the Apple App store. The app, will also be available soon for Android(TM) devices, allows the device to function as a remote control that includes the ability to access a Blu-ray Disc's details such as jacket artwork, actor, and production information as well as search for additional video clips online.</p>

<p><br />
<strong>3D-Capable Home Audio Components</strong></p>

<p>Sony also recently announced 3D-capable home audio products including the STR-DN1010 audio/video receiver, the HT-CT350 and HT-CT150 3.1 channel sound bars, and the HT-SF470 5.1 channel home theater system. The models offer consumers flexible solutions to round out the Sony 3D experience and meet the demands of 3D home entertainment.</p>

<p>Additionally, Sony will add 3D capability to the previously announced STR-DH810 and STR-DH710 AV receivers through a firmware update later this month.</p>

<p><br />
<strong>Integrated Marketing Campaign</strong></p>

<p>Sony's 3D advertising will start airing this week on national TV networks, accompanied by cinema, radio, print and digital ads throughout the year. Owing to the importance of clear consumer education on 3D, Sony will also spread the word about the new BRAVIA 3D TVs through dedicated training events, displays at authorized Sony retailers, social networks, on SonyStyle.com, through Sony Style stores, and via e-mail, direct mail, and free standing inserts.</p>

<p>The campaign was developed with support of Sony Electronics' advertising agency 180 Los Angeles.</p>

<p>Specifications and images for all 3D products can be found at <a target="_blank" href="http://www.sony.com/news/">www.sony.com/news</a>.</p>

<p>For further details and pre-orders, please visit <a target="_blank" href="http://www.sony.com/bravia/">www.sony.com/bravia</a> or Sony Style retail stores across the country. These products and others can be found at Sony authorized retailers across the country.</p>

<p>To learn about the 3D world created by Sony, please visit <a target="_blank" href="http://www.sony.net/united/3D/">www.sony.net/united/3D</a>.</p>

<p>Source: Sony Electronics Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June  9, 2010  2:52 PM</b>
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
			<?=getComments(3782)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3782)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/06/sony-delivers-the-industrys-largest-array-of-3dcapable-hdtvs-and-home-audio-and-video-products.php" type="text/javascript" charset="utf-8"></script>
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