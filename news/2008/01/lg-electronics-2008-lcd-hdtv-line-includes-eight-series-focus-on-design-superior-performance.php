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
		AND e.entry_id = 856";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 856 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 856 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 856";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/lg-electronics-2008-lcd-hdtv-line-includes-eight-series-focus-on-design-superior-performance.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 856";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics\' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics\' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics\' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance" />
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
	<title>HDTV Magazine - LG Electronics' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance</title>
	<meta name="keywords" content="lcd hdtv, hdmi deep, deep color, voice technology, clear voice, lcd, hdtv, technology, color, inch, features, series, simplink, electronics, design, system, line, speaker, hdmi, invisible, connectivity, deep, voice, led, include" />
	<meta name="description" content="LG Electronics today unveiled its stylish 2008 LCD HDTV line -- eight series, 24 models, 17 with &quot;Full HD&quot; 1080p display capability -- with screen sizes ranging from 19- to 52-inches. The line features multiple design and technological innovations, including a stunningly designed 1.7-inch thin LCD, a unique wireless HDTV and a model with LED backlighting technology.

A new invisible speaker system tuned by..." />
	<meta name="title" content="LG Electronics' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/lg-electronics-2008-lcd-hdtv-line-includes-eight-series-focus-on-design-superior-performance.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LG Electronics today unveiled its stylish 2008 LCD HDTV line -- eight series, 24 models, 17 with &quot;Full HD&quot; 1080p display capability -- with screen sizes ranging from 19- to 52-inches. The line features multiple design and technological innovations, including a stunningly designed 1.7-inch thin LCD, a unique wireless HDTV and a model with LED backlighting technology.

A new invisible speaker system tuned by..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=856', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/lg-electronics-2008-lcd-hdtv-line-includes-eight-series-focus-on-design-superior-performance.php">LG Electronics' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  6, 2008</b>
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
				<p class="prtitle">LG Electronics' 2008 LCD HDTV Line Includes Eight Series, Focus on Design, Superior Performance</p>

<center><i>1.7-inch 'Super Slim,' Wireless and LED Backlight Models Lead LG Innovations</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 6 /PRNewswire/</B> -- LG Electronics today unveiled its stylish 2008 LCD HDTV line -- eight series, 24 models, 17 with "Full HD" 1080p display capability -- with screen sizes ranging from 19- to 52-inches. The line features multiple design and technological innovations, including a stunningly designed 1.7-inch thin LCD, a unique wireless HDTV and a model with LED backlighting technology.</p>

<p>The new LCD HDTVs combine eye-catching design with enhanced features to deliver superior picture quality and enhanced functionality. With a striking, slim silhouette, red-colored back and round aperture with LED light, the flagship 1.7-inch thin LGX Super Slim is the epitome of elegance. Styling transcends the line, as series including the LG70, LG71 and LG75, include a unique teardrop design, red color accenting and a "high-gloss" black finish.</p>

<p>A new invisible speaker system tuned by renowned audio expert, Mr. Mark Levinson, further accentuates the beauty of the LCD HDTV line. This unique system incorporates speaker actuators around the perimeter of the entire bezel, eliminating traditional speaker drivers and associated grills. This not only allows for a sleek, finished look, but also offers a wider "sweet spot" by creating a virtual "wall" of sound. What's more, LG's new "Clear Voice" technology automatically enhances the sound frequency range of the dialogue even when background noise swells.</p>

<p>But beautiful design is only part of the story. The new LG LCD line also delivers on picture performance. Other advanced features, including TruMotion 120Hz technology, wireless connectivity and Image Science Foundation custom calibration certification (ISFccc) provide more options for consumers seeking premium LCD HDTV performance (Please see separate release on picture quality). LG's 2008 LCD line is on display at the 2008 International CES(R) (Booth #8214, Central Hall, Las Vegas Convention Center).</p>

<p> LG'S NEW LCDS: OPULENT DESIGN, DISTINCTIVE COLOR ACCENT<br />
 * LG75 LCD HDTV LED Backlit LCD (Size: 47-inch) -- A CES 2008 "Innovations<br />
 Honoree," the LG75 is a "Full HD" 1080p LCD HDTV combining LED<br />
 backlighting technology, enhanced functionality and elegant design. The<br />
 LED backlight is partitioned into 128 light-emitting diodes, enabling<br />
 local dimming to provide quick response to changing images. The local<br />
 dimming ability of the LED backlight can make it more energy efficient<br />
 too. When combined with LG's TruMotion 120Hz technology, consumers will<br />
 enjoy one of the highest picture qualities available in the market<br />
 today. Key features include:<br />
 -- 1,000,000:1 Dynamic Contrast Ratio<br />
 -- TruMotion 120Hz technology<br />
 -- Invisible Speaker System<br />
 -- Intelligent Sensor for automatic optimization of brightness and<br />
 color<br />
 -- 24p TruCinema<br />
 -- ISFccc Calibration Ready<br />
 -- Content-specific AV modes that automatically tailor settings for<br />
 movies, sports or games<br />
 -- Four HDMI with 1.3 Deep Color<br />
 -- USB 2.0 jack for viewing JPEG photos or listening to MP3 music files.<br />
 -- Clear Voice technology<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment</p>

<p> * LG71 Wireless LCD HDTV Series (Sizes: 47- and 52-inch) -- A CES 2008<br />
 "Innovations Honoree," the stylish LG71 wireless "Full HD" 1080p LCD<br />
 HDTV features LG's "teardrop" design and is perfect for consumers who<br />
 are looking to eliminate unsightly wires and external components. Its<br />
 integrated 802.11n Wireless System allows a clean wall installation<br />
 without down-converting signals. The unit comes with a separate<br />
 wireless receiver with a 50-foot radius (approx.), allowing other<br />
 devices to be placed nearby, hidden in a cabinet or entertainment<br />
 console. The full-featured receiver includes four HDMI 1.3 with Deep<br />
 Color inputs for cable, satellite, DVD, HDTV and other sources. Key<br />
 features include:<br />
 -- 20,000:1 Dynamic Contrast Ratio<br />
 -- TruMotion 120Hz technology<br />
 -- 802.11n Wireless System<br />
 -- Invisible Speaker System<br />
 -- Intelligent Sensor for automatic optimization of brightness and<br />
 color<br />
 -- 24p TruCinema<br />
 -- ISFccc Calibration Ready<br />
 -- Four HDMI 1.3 with Deep Color<br />
 -- USB 2.0 jack for viewing JPEG photos or listening to MP3 music files<br />
 -- Clear Voice technology<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment</p>

<p> * LG70 LCD HDTV Series (Sizes: 32-, 42-, 47- and 52-inch) -- The LG70<br />
 series' elegant "teardrop" design is accentuated with clean lines, LG's<br />
 invisible speaker system, soft touch controls and distinctive red color<br />
 accents to create a truly premium HDTV. The LG70 series has an ultra-<br />
 high 20,000:1 contrast ratio, and "Full HD" 1080p resolution with<br />
 TruMotion 120Hz technology. It also features LG's Intelligent Sensor<br />
 which automatically adjusts picture settings according to room lighting.<br />
 Key features include:<br />
 -- 20,000:1 Dynamic Contrast Ratio<br />
 -- TruMotion 120Hz technology<br />
 -- Invisible Speaker System<br />
 -- Intelligent Sensor for automatic optimization of brightness and<br />
 color<br />
 -- 24p TruCinema<br />
 -- ISFccc Calibration Ready<br />
 -- Four HDMI 1.3 with Deep Color<br />
 -- USB 2.0 jack for viewing JPEG photos or listening to MP3 music files.<br />
 -- Clear Voice technology<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment</p>

<p> * LGX LCD HDTV Super Slim (Size: 42-inch) -- A CES 2008 "Innovations<br />
 Honoree," the stunning 1.7-inch thin LGX LCD HDTV Super Slim is ideal<br />
 for the modern living environment. Its striking, slim silhouette, red-<br />
 colored back and round aperture with LED light address the growing<br />
 demand for a superior viewing experience that complements today's<br />
 elegant home styles. Key features include:<br />
 -- 15:000:1 Dynamic Contrast Ratio<br />
 -- TruMotion 120Hz technology<br />
 -- Invisible Speaker System<br />
 -- Intelligent Sensor for automatic optimization of brightness and<br />
 color<br />
 -- 24p TruCinema<br />
 -- ISFccc Calibration Ready<br />
 -- Four HDMI 1.3 with Deep Color<br />
 -- USB 2.0 jack for viewing JPEG photos or listening to MP3 music files.<br />
 -- Clear Voice technology to enhance dialogue when background noise<br />
 swells<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment</p>

<p> * LG60 LCD HDTV Series (Sizes: 32-, 37-, 42-, 47- and 52-inch) -- LG's<br />
 thin LG60 LCD HDTV Series has all the features and functionalities of<br />
 the LGX LCD HDTV Super Slim model (see above).</p>

<p> * LG50 LCD HDTV Series (Sizes: 37-, 42-, 47- and 52-inch) -- The LG50<br />
 series' slim profile, black high-gloss finish and invisible speaker<br />
 system create a clean, high quality look with performance to match.<br />
 These "Full HD" 1080p sets feature LG's proprietary Intelligent Sensor,<br />
 which automatically adjusts the picture to ensure that its quality<br />
 excels in all viewing conditions, and an expert mode for custom picture<br />
 calibration. Other features include:<br />
 -- 15,000:1 Dynamic Contrast Ratio<br />
 -- Intelligent Sensor for automatic optimization of brightness and<br />
 color<br />
 -- 24p TruCinema<br />
 -- ISFccc Calibration Ready<br />
 -- Three HDMI 1.3 with Deep Color<br />
 -- USB 2.0 jack for viewing JPEG photos or listening to MP3 music files.<br />
 -- Clear Voice technology to enhance dialogue when background noise<br />
 swells<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment</p>

<p> * LG40 LCD HDTV (Size: 32-inch) -- The LG40's stylish design, color accent<br />
 and built-in DVD player makes it the ideal HDTV for secondary rooms such<br />
 as bedrooms and home offices. Balanced on a curved pedestal, the 32-<br />
 inch LCD features a side vacuum-loading DVD player. Its red back accent<br />
 is visible from the front creating an elegant and refined look. Other<br />
 features include:<br />
 -- 12:000:1 Dynamic Contrast Ratio<br />
 -- Three HDMI 1.3 with Deep Color<br />
 -- Content-specific AV modes that automatically tailor settings for<br />
 movies, sports or games<br />
 -- Clear Voice technology to enhance dialogue when background noise<br />
 swells<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment</p>

<p> * LG30 LCD HDTV Series (Sizes: 19-, 22-, 26-, 32-, 37- and 42-inch) -- The<br />
 LG30 series is LG's 720p LCD HDTV line. It offers consumers a wide<br />
 range of screen sizes and features invisible speakers that enhance its<br />
 sleek, modern look. Other features include:<br />
 -- Content-specific AV modes that automatically tailor settings for<br />
 movies, sports or games<br />
 -- Three HDMI 1.3 with Deep Color (26-inch and larger sets only)<br />
 -- Clear Voice technology to enhance dialogue when background noise<br />
 swells<br />
 -- LG SimpLink(TM) connectivity to control other LG SimpLink-compatible<br />
 equipment (26-inch and larger sets only)</p>

<p><br />
<B>About LG Electronics USA, Inc.</B></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a wide range of consumer electronics (digital display and digital media) products, mobile phones and digital appliances under LG's "Life's Good" marketing theme. For more information, please visit www.LGusa.com.</p>

<p><br />
<B>About LG Electronics, Inc.</B></p>

<p>LG Electronics, Inc. is a global leader and technology innovator in consumer electronics, mobile communications and home appliances, employing more than 82,000 people working in over 110 operations including 81 subsidiaries around the world. Comprising four business units -- Digital Displays, Digital Media, Mobile Communications and Digital Appliances, with 2006 global sales of U.S. $38.5 billion -- LG Electronics is the world's largest producer of CDMA handsets, air conditioners, optical storage products and DVD players. For more information, please visit www.lge.com.</p>

<p>Source: LG Electronics USA, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2008 11:34 AM</b>
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
			<?=getComments(856)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 856)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/lg-electronics-2008-lcd-hdtv-line-includes-eight-series-focus-on-design-superior-performance.php" type="text/javascript" charset="utf-8"></script>
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