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
		AND e.entry_id = 3486";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3486 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3486 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3486";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/01/sony-makes-3d-come-to-life-at-home-with-first-3d-capable-bravia-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3486";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs" />
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
	<title>HDTV Magazine - Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs</title>
	<meta name="keywords" content="bravia internet, diagonally kdl, inch class, bravia kdl, class inches, bravia, kdl, video, internet, series, sony, inch, models, sizes, full, inches, music, led, include, sony’s, new, diagonally, engine, class, xbr" />
	<meta name="description" content="From black-and-white, to color, to digital, Sony Electronics is once again setting the new standard for how televisions look and perform.  Today, the company introduced its 2010 BRAVIA® LCD HDTV line featuring its first 3D HDTVs, a new innovative and stylish Monolithic Design Concept, and LED backlighting.

The new Sony televisions are..." />
	<meta name="title" content="Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/01/sony-makes-3d-come-to-life-at-home-with-first-3d-capable-bravia-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="From black-and-white, to color, to digital, Sony Electronics is once again setting the new standard for how televisions look and perform.  Today, the company introduced its 2010 BRAVIA® LCD HDTV line featuring its first 3D HDTVs, a new innovative and stylish Monolithic Design Concept, and LED backlighting.

The new Sony televisions are..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3486', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/sony-makes-3d-come-to-life-at-home-with-first-3d-capable-bravia-hdtvs.php">Sony Makes 3D Come to Life at Home with First 3D Capable Bravia HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  6, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">SONY MAKES 3D COME TO LIFE AT HOME WITH FIRST 3D CAPABLE BRAVIA HDTVs</p>
	
<center><i>Monolithic Design Concept Changes Viewing Environment Merging Style and Function</center></i><br />
<br />

<p><strong>LAS VEGAS (CES BOOTH #14200), Jan. 6, 2010</strong> – From black-and-white, to color, to digital, Sony Electronics is once again setting the new standard for how televisions look and perform.  Today, the company introduced its 2010 BRAVIA® LCD HDTV line featuring its first 3D HDTVs, a new innovative and stylish Monolithic Design Concept, and LED backlighting.</p>

<p>The new Sony televisions are another example of how Sony is providing consumers with networked products that enhance the home entertainment experience by offering built-in Wi-Fi® (802.11) for easy access to BRAVIA Internet video, BRAVIA Internet Widgets and personal content through Digital Living Network Alliance (DLNA) certified™ home networks.</p>

<p>The line is made up of 38 models ranging in screen sizes from 60 to 22-inches.  The LX900 series offers integrated 3D functionality with Sony’s 3D active shutter glasses and built-in 3D transmitter, while the HX900 and HX800-series are 3D capable using Sony 3D active shutter glasses and 3D transmitter (each sold separately).  The 3D models incorporate a frame sequential display and active-shutter glasses that work together with Sony's proprietary high frame rate technology reproducing full high-definition 3D images.</p>

<p>“Sony’s 3D HDTVs leverage the breadth and depth of the company’s expertise to create an entirely unique experience at home that draws you closer to entertainment than ever before,” said Jeff Goldstein, vice president for Sony’s television business.  “Sony will continue to own the living room by delivering TVs that work synergistically; both in function and design.”</p>

<p>Models in the LX900, HX900, NX800, and NX700 series feature Sony’s all new Monolithic Design.  The design concept aims to change the entire TV viewing environment by delivering the most innovative, stylish, and high-quality experience possible.</p>

<p>With quality materials forming a simple, flush viewing surface with crisp edges and a smooth border, the models blend into the home’s interior and allow viewers to focus completely on beautiful on-screen images without distraction from unnecessary decorative elements.  Also, a unique six degree upward tilt option offers a more natural, comfortable viewing experience.</p>

<p>Central to the slim profile Monolithic Design is Sony’s Edge LED backlight.  Designed to deliver deep blacks and brilliant whites, the design allows for a slim form factor and an outstanding contrast ratio helping the new models impress not only when they are displaying beautiful images, but also when turned off as they blend into the home’s décor.</p>

<p> <br />
<strong>BRAVIA XBR-LX900 Series 3D HDTV</strong></p>

<p>Available this summer, the BRAVIA XBR-LX900 series features integrated 3D functionality and screen sizes including 60 (XBR-60LX900), 52 (XBR-52LX900), 46 (XBR-46LX900), and 40-inch (XBR-40LX900).</p>

<p>The full HD 1080p (1920 x 1080) models feature Edge LED backlight, Sony’s new Monolithic Design and Motionflow™ PRO 240Hz motion compensation technology, which helps produces smooth images in fast moving content such as sport and action movies.  Sony’s 240Hz technology also reduces the mixing of images of 3D content assigned to the left and right eyes, while the BRAVIA Engine™ 3 full digital video processor uses a collection of enhanced algorithms to significantly reduce noise, enhance overall image detail, and optimize contrast so every scene produces sharp, vibrant, life-like images.</p>

<p>The LX900 models also feature Sony’s new OptiContrast panel.  Designed with a clear surface treatment and a resin sheet sandwiched between the LCD display panel and glass plate, the OptiContrast panel minimizes the reflection and refraction of external and internal light producing deeper images with superior black levels even in bright rooms.</p>

<p>The models include integrated Wi-Fi for an easy connection to broadband home networks.  Once connected, users can access thousands of streaming movies, videos, music and more from Netflix, Amazon Video on Demand, YouTube™, Slacker® Internet Radio, Pandora®, NPR, Sony Pictures, Sony Music, and over 25 total providers through the Sony BRAVIA Internet Video platform.</p>

<p>Also, with the touch of a button, users can access the latest in news, weather, USA Today sports, Yahoo Finance, Twitter, Flickr photos, and FrameChannel through small applications called BRAVIA Internet Widgets.  The widgets can be uniquely positioned anywhere on the TV screen for a custom viewing experience.</p>

<p>The models also feature playback of personal content including digital pictures, video, and music through USB and DLNA® certified network connections.</p>

<p>Another new feature is Sony’s Intelligent Presence Sensor with face detection.  The sensor detects if you've stepped away from the TV or are not watching the screen and automatically dims the backlight.  After an extended period, the TV will turn off if no one has re-entered the viewing area.  Additionally, the Intelligent Presence Sensor’s newly added Position Control feature detects a user’s viewing position to deliver optimized video/sound balance, while the Distance Alert feature helps to keep small children at an eye-friendly distance.</p>

<p>The models also offer Sony’s BRAVIA Sync™ for easy operation with other BRAVIA Sync devices such as AV receivers and Blu-ray Disc™ players, and TVGuide® on-screen channel guide.</p>

<p> <br />
<strong>BRAVIA XBR-HX900 Series 3D Ready HDTV</strong></p>

<p>The XBR-HX900 series 3D ready (with the addition of Sony active shutter glasses and transmitter, both sold separately) full HD (1920 x 1080p) models feature Intelligent Dynamic LED backlight and Sony’s new Monolithic Design.</p>

<p>The model’s full-array LED backlighting improves contrast and dynamic range by local dimming that controls the LED backlight level by area so that detail is maintained in the dark areas, while other areas are driven near peak brightness.  The technology reduces unnecessary light emission resulting in true and deep blacks compared to conventional LED backlit models.</p>

<p>The models also feature Sony’s new ambient sensor that automatically optimizes the TV’s color and brightness according to the room environment and lighting conditions for optimized settings.</p>

<p>Featuring screen sizes including 52 (XBR-52HX900) and 46-inches (XBR-46HX900), the model offers four HDMI 1.4 inputs, one component input, one composite input, one component/composite selectable inputs, and a PC input (HD15) with PC/TV picture-in-picture.</p>

<p>The models also feature:<br />
<ul><li>Monolithic Design</li><li>Motionflow PRO 240Hz motion compensation technology</li><li>OptiContrast</li><li>USB Wireless-LAN adapter for easy wireless network connection (sold separately)</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3 with Intelligent Image Enhancer</li><li>USB and DLNA photo/music/video playback</li></ul></p>

<p><br />
<strong>BRAVIA KDL-HX800 Series 3D Ready HDTV</strong></p>

<p>Also 3D ready (with the addition of Sony active shutter glasses and transmitter, sold separately), the BRAVIA KDL-HX800 series will be available this summer in screen sizes including 55-inch class (54.6-inches measured diagonally) (KDL-55HX800), 46 (KDL-46HX800) and 40-inch (KDL-40HX800).</p>

<p>The full HD 1080p (1920 x 1080) models utilize a Dynamic Edge LED backlight with local dimming for improved contrast and dynamic range.</p>

<p>Other features include:<br />
<ul><li>Motionflow PRO 240Hz Technology for Smooth Motion</li><li>Ambient sensor</li><li>USB Wireless-LAN adapter for easy wireless network connection (sold separately)</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3</li><li>USB and DLNA photo/music/video playback</li></ul></p>

<p> </p>

<p>Sony also introduced several other new BRAVIA models with various features and screen sizes.  They include:</p>

<p><br />
<strong>BRAVIA KDL-NX800 series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) Edge LED backlit LCD</li><li>Monolithic Design</li><li>Motionflow 240Hz Technology for Smooth Motion</li><li>Integrated Wi-Fi wireless network capabilities (802.11)</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3</li><li>USB and DLNA photo/music/video playback</li><li>Available in March</li><li>Screen sizes include 60 (KDL-60NX800: $4,00), 52 (KDL-52NX800: $3,400) and 46-inch (KDL-46NX800: $2,800)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-NX700 Series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) Edge LED backlit LCD</li><li>Monolithic Design</li><li>Motionflow 120Hz Technology for Smooth Motion</li><li>Integrated Wi-Fi wireless network capabilities (802.11N)</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3</li><li>USB and DLNA photo/music/video playback</li><li>Available in March</li><li>Screen sizes include 46 (KDL-46NX700: $2,600) and 40-inch (KDL-40NX700: $2,100)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-EX700 Series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) Edge LED backlit LCD</li><li>Presence Sensor, Ambient sensor</li><li>Motionflow 120Hz Technology for Smooth Motion</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3</li><li>USB and DLNA photo/music/video playback</li><li>Available in March</li><li>Screen sizes include 60 (KDL-60EX700: $3,900), 52 (KDL-52EX700: $2,800), 46 (KDL-46EX700: $2,200), 40-inch (KDL-40EX700: $1,700), and 32-inch class (31.5-inches measure diagonally) (KDL-32EX700: $1,100)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-EX600 Series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) Edge LED backlit LCD</li><li>Ambient sensor</li><li>BRAVIA Engine 2</li><li>BRAVIA Sync</li><li>USB photo/music/video playback</li><li>Available in March</li><li>Screen sizes include 46 (KDL-46EX600: $1,900), 40 (KDL-40EX600: $1,400) and 32-inch class (31.5-inches measure diagonally) (KDL-32EX600: $800)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-EX500 Series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) CCFL backlit LCD</li><li>Motionflow 120Hz Technology for Smooth Motion</li><li>Ambient sensor</li><li>BRAVIA Engine 2</li><li>BRAVIA Sync</li><li>USB photo/music/video playback</li><li>Available in February</li><li>Screen sizes include 60 (KDL-60EX500: $3,300), 55-inch class (54.6-inches measured diagonally) (KDL-55EX500: $2,400), 46 (KDL-46EX500: $1,600), 40 (KDL-40EX500: $1,100), and 32-inch class (31.5-inches measure diagonally) (KDL-32EX500: $800)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-EX40B Series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) CCFL backlit LCD</li><li>Integrated Blu-ray Disc player</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3</li><li>Ambient sensor</li><li>Ethernet input</li><li>USB and DLNA photo/music/video playback</li><li>Available in May</li><li>Screen sizes include 40 (KDL-40EX40B: $1,000) and 32-inch class (31.5-inches measure diagonally) (KDL-32EX40B: $800)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-EX400 Series</strong><br />
<ul><li>Full HD 1080p (1920 x 1080) CCFL backlit LCD</li><li>Ambient sensor</li><li>BRAVIA Engine 2</li><li>BRAVIA Sync</li><li>USB photo/music/video playback</li><li>Available in February</li><li>Screen sizes include 46 (KDL-46EX400: $1,200), 40 (KDL-40EX400: $800), and 32-inch class (31.5-inches measure diagonally)  (KDL-32EX400: $600)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-EX308 Series</strong><br />
<ul><li>720p (1366 x 768) CCFL backlit LCD</li><li>Wi-Fi with USB adapter (included)</li><li>BRAVIA Internet Video and BRAVIA Internet Widgets</li><li>BRAVIA Engine 3</li><li>BRAVIA Sync</li><li>USB and DLNA photo/music/video playback</li><li>Available in March</li><li>Screen sizes include 32-inch class (31.5-inches measure diagonally)  (KDL-32EX308: $530) and 22-inch class (21.6 inches measured diagonally) (KDL-22EX308: $380)</li></ul></p>

<p><br />
<strong>BRAVIA KDL-BX300 Series</strong><br />
<ul><li>720p (1366 x 768) CCFL backlit LCD</li><li>BRAVIA Engine 2</li><li>BRAVIA Sync</li><li>Available in March</li><li>Screen sizes include 32-inch class (31.5-inches measure diagonally)  (KDL-32BX300: $500) and 22-inch class (21.6 inches measured diagonally)  (KDL-22BX300: $350)</li></ul></p>

<p>For further details and pre-orders, please visit  <a target="_blank" href="http://www.sony.com/bravia/">www.sony.com/bravia</a> or Sony Style retail stores across the country.</p>

<p>To learn about the 3D world created by Sony, please visit <a target="_blank" href="http://www.sony.net/united/3D/">www.sony.net/united/3D</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2010  7:20 PM</b>
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
			<?=getComments(3486)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3486)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/sony-makes-3d-come-to-life-at-home-with-first-3d-capable-bravia-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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