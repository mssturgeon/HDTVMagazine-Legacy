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
		AND e.entry_id = 4984";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4984 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4984 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4984";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-stunning-design-innovations-and-cuttingedge-features-on-viera-2013-ledlcd-models.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4984";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&reg; 2013 LED/LCD Models" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&reg; 2013 LED/LCD Models" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&reg; 2013 LED/LCD Models" />
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
	<title>HDTV Magazine - Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&reg; 2013 LED/LCD Models</title>
	<meta name="keywords" content="inch class, led lcd, backlight scanning, class model, home screen, series, panasonic, viera, inch, class, led, lcd, features, home, screen, models, design, resolution, quality, picture, technology, content, swipe, hdmi, scanning" />
	<meta name="description" content="Panasonic, an industry and technology leader in High Definition and Smart TV technology, unveiled the new 2013 lineup of VIERA LED/LCD HDTVs that redefine the home entertainment experience and add simplicity and luxury to the home.  The 2013 VIERA LED/LCD product line achieves the ultimate in advanced design with outstanding picture quality, easy operation and enhanced connectivity and personalization options. For 2013, all Panasonic VIERA LCD models are LED.

The 2013 Smart VIERA LED/LCD line-up features..." />
	<meta name="title" content="Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&amp;reg; 2013 LED/LCD Models" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&amp;reg; 2013 LED/LCD Models" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-stunning-design-innovations-and-cuttingedge-features-on-viera-2013-ledlcd-models.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic, an industry and technology leader in High Definition and Smart TV technology, unveiled the new 2013 lineup of VIERA LED/LCD HDTVs that redefine the home entertainment experience and add simplicity and luxury to the home.  The 2013 VIERA LED/LCD product line achieves the ultimate in advanced design with outstanding picture quality, easy operation and enhanced connectivity and personalization options. For 2013, all Panasonic VIERA LCD models are LED.

The 2013 Smart VIERA LED/LCD line-up features..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4984', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-stunning-design-innovations-and-cuttingedge-features-on-viera-2013-ledlcd-models.php">Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&reg; 2013 LED/LCD Models</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 10, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">Panasonic Unveils Stunning Design Innovations And Cutting-Edge Features On VIERA&reg; 2013 LED/LCD Models</p>

<center><i>All-LED Line-up Features Expanded VIERA Connect&trade;, Ultimate Picture Quality, Increased Connectivity and Personalization Options, Cutting-Edge Design</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 7, 2013 /PRNewswire/</strong> -- Panasonic, an industry and technology leader in High Definition and Smart TV technology, unveiled the new 2013 lineup of VIERA LED/LCD HDTVs that redefine the home entertainment experience and add simplicity and luxury to the home.  The 2013 VIERA LED/LCD product line achieves the ultimate in advanced design with outstanding picture quality, easy operation and enhanced connectivity and personalization options. For 2013, all Panasonic VIERA LCD models are LED.</p>

<p>"Panasonic's new LED/LCD models transform the home, creating not only the ultimate viewing experience, but the finest in design innovation, allowing the television to become the centerpiece of the home" said Henry Hauser, Vice President, Merchandising Group, Panasonic Consumer Marketing Company of North America. "We're committed to pushing the envelope in terms of form and functionality while maintaining our strong commitment to producing the most environmentally-friendly products."</p>

<p><br />
<strong>The 2013 Smart VIERA LED/LCD line-up features:</strong></p>

<p><strong>My Home Screen</strong> – a personalization function that allows each user in the home to create their own personal home screen giving them quick access to their favorite content. (2013 VIERA WT60, DT60, ET60, and E60 Series)</p>

<p><strong>Swipe &amp; Share 2.0</strong> – a connectivity enhancement that transforms the TV into a hub for streaming and sharing photo and video content seamlessly with Smartphone and Tablet devices.  Through Panasonic's proprietary VIERA Connect&trade; platform, users can transfer personal photos and videos from their Android or iOS devices directly to the large screen with a simple swipe of the finger and transfer them back to their smart devices the same way.  Swipe &amp; Share also enables sharing of user-generated photos and videos that are on the large screen with other Android or iOS devices. (2013 VIERA&reg; WT60, DT60, ET60, and E60 Series)</p>

<p><strong>Voice Guidance</strong> – an accessibility function that uses text to speech functions to verbalize text content as it appears on your TV.  (2013 VIERA WT60, DT60, ET60, and E60 Series)</p>

<p><strong>Voice Interaction</strong> -- by simply saying a key word into the Smart VIERA Touch Pad Controller or Smartphone (VIERA Remote 2 App must be installed), the search result is displayed on the VIERA HDTV screen and also verbally read out. (2013 VIERA WT60 and DT60 Series)</p>

<p>Representing the latest engineering technologies, the 2013 lineup achieves the ultimate in modern design. Panasonic's enhanced IPS Panel technology delivers a wide viewing angle with almost no picture degradation.  Twice backlight scanning motion and improved contrast ratio with finer local dimming control contribute to crisp and superior picture quality with improved color accuracy.  4200 Backlight Scanning technologyreduces motion blur and loss of detail in fast-moving images, along with 1080p resolution and dot noise reduction, each of the 16 models feature unmatched, vivid and true-to-life picture quality.</p>

<p>Committed to producing products that are mindful of the environment, Panasonic's new and improved high efficient LED/LCD panels reduce power consumption up to 15%. Panasonic's new LED/LCD models are Energy Star 6.0 certified. (except the VIERA 50-inch class and 39-inch class B6 Series models).</p>

<p><br />
<strong>WT60 Series</strong></p>

<p>The WT60 series is the FULL HD 3D, IPS LED LCD flagship series and is available in two screen sizes - the TC-L47WT60, 47-inch class and the TC-L55WT60, 55-inch class.</p>

<p> The WT60 is equipped with 1080p resolution and a wide viewing angle .  The 2D-3D conversion, transforms 2D content to 3D Images in real-time, and local dimming and Clear Panel Pro further enhance picture quality.  The models feature three HDMI ports, allowing for 3D video and audio features.  In addition, the panel's backlight scanning utilizes 4200 BLS technology, which provides a clearer picture to enjoy fast-moving scenes.  The WT60 also features Panasonic's first-ever built in camera for enhanced connectivity with VIERA Connect&trade; apps and technologies.</p>

<p>The WT60 Series features a minimalist approach to aesthetics that is encompassed by a clear pedestal with an elegant metal bezel and is equipped with VIERA Connect, My Home Screen, Swipe and Share 2.0, and Voice Interaction / Guidance. The WT60 Series also features ISFccc Calibration Mode with Advanced Calibration. Calibrators adjust the detailed picture setting with the calibration software (CALMAN&trade; ) provided by SpectraCal Inc.</p>

<p><br />
<strong>DT60 Series</strong></p>

<p>The DT60 series includes two screen sizes – the TC-L55DT60, 55-inch class and TC-L60DT60, 60-inch class.</p>

<p>The series achieves the ultimate in advanced design with a stunning display.  The DT60 series produces a clear viewing experience at virtually any angle with 1080p FULL HD resolution and a 178 degree wide viewing angle.  The DT60 Series is equipped with enhanced connectivity features, including VIERA Connect, My Home Screen, Swipe and Share 2.0, Voice Interaction /Recognition, with three HDMI and three USB ports, and a Dual Core with Hexa Processing Engine.  In addition, the 55-inch class model's backlight scanning utilizes up to 1920 BLS technology while the 60-inch class model utilizes up to 1200 BLS.</p>

<p><br />
<strong>ET60 Series</strong></p>

<p>The TC-L50ET60, 50-inch class and TC-L55ET60, 55-inch class comprise the ET60 series of FULL HD 3D IPS LED LCD TVs.</p>

<p>The ET60 models include 1080p FULL HD resolution and 2D-3D conversion for pristine picture quality.  The ET60 series features VIERA ConnectTM, My Home Screen, built In Wireless LAN, Swipe and Share 2.0, and Voice Guidance.  The series includes 3 HDMI connections and 2 USB ports. In addition, ET60 Series' backlight scanning utilizes up to 720 BLS.</p>

<p><br />
<strong>E60 Series</strong></p>

<p>The TC-L42E60, 42-inch class; TC-L50E60, 50-inch class; TC-L58E60, 58-inch class and the TC-L65E60, 65-inch class comprise the E60 series of FULL HD 3D LED LCD TVs.</p>

<p>The E60 models include 1080p FULL HD resolution and dot noise reduction for the ultimate viewing experience.  The E60 series features 3 HDMI and 2 USB ports.  With VIERA Connect, My Home Screen, built in Wireless LAN, Voice Guidance and Swipe and Share 2.0, the new line makes it more convenient than ever for users to access and navigate content.  In addition, E60 Series' backlight scanning utilizes up to 240 BLB.</p>

<p><strong>EM60 Series</strong></p>

<p>The EM60 series provides the consumer with 1080p resolution, slim LED and narrow bezel design. There are two screen sizes in the EM60 series – the TC-L39EM60, 39-inch class and TC-L50EM60, 50-inch class. All offer media player, with two HDMI connections and one USB port. Dot noise reduction technology enhances image quality of content.  In addition, EM60 Series' backlight scanning utilizes up to 240 BLB.</p>

<p><br />
<strong>XM6 Series</strong></p>

<p>The TC-L32XM6, 32-inch class model features 720p resolution with two HDMI connections with one USB port.  The XM6 series reflect Panasonic's minimalistic design with a slim bezel.  Dot noise reduction technology enhances image quality of content.</p>

<p><br />
<strong>B6 Series</strong></p>

<p>The TC-L32B6, 32-inch class model features 720p resolution, and the TC-L39B6, 39-inch class model and TC-L50B6, 50-inch class model feature 1080p resolution.  The entire B6 series is equipped with two HDMI connections with one USB port. Dot noise reduction technology enhances image quality of content.</p>

<p>The Panasonic 2013 VIERA&reg; LED LCD HDTVs will be available in the spring of 2013.  They will be on display at the Panasonic booth #9406 at the 2013 International CES at the Las Vegas Convention Center from January 8, 2013 through January 11, 2013.  For more information including technical specifications please visit <a target="_blank" href="http://www.Panasonic.com/">www.Panasonic.com</a>.</p>

<p><br />
<strong>About Panasonic Consumer Marketing Company of North America</strong></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Marketing Company of North America, a Division of Panasonic Corporation of North America, the principal North American Subsidiary of Panasonic Corporation (NYSE: PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations, offers a wide-range of consumer solutions in the U.S. and Canada. The Company's portfolio of innovative consumer products ranges from VIERA Full HD 3D Televisions, Blu-ray players, LUMIX Digital Cameras, Camcorders, Home Audio, Cordless Phones, Home Appliances, Wellness and Personal Care products and more.</p>

<p>Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. In the 2012 Interbrand Annual Best Global Green Brands ranking, the Panasonic brand jumped four spots to number six: http://www.interbrand.com/en/best-global-brands/Best-Global-Green-Brands/2012-Report.aspx. Follow Panasonic on Twitter @panasonicusa, and additional company information for media is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>SOURCE Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 10, 2013  3:58 PM</b>
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
			<?=getComments(4984)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4984)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-stunning-design-innovations-and-cuttingedge-features-on-viera-2013-ledlcd-models.php" type="text/javascript" charset="utf-8"></script>
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