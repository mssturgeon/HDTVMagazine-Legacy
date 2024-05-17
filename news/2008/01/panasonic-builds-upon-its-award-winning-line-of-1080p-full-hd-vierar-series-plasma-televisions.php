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
		AND e.entry_id = 871";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 871 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 871 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 871";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/panasonic-builds-upon-its-award-winning-line-of-1080p-full-hd-vierar-series-plasma-televisions.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 871";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions" />
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
	<title>HDTV Magazine - Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions</title>
	<meta name="keywords" content="viera link, inch class, game mode, card slot, native contrast, viera, panasonic, native, game, link, inch, mode, plasma, class, contrast, ratio, card, memory, slot, hdmi, resolution, connections, line, tbd, new" />
	<meta name="description" content="Panasonic Corporation of North America, the principal U.S. subsidiary of Matsushita Electric Industrial Co., Ltd. (NYSE:MC) , the industry leader in high definition and Plasma TV, today introduced its 2008 line of Plasma full HD televisions. The VIERA 2008 full HD Plasma series introduces a new screen size - a 46-inch(1) class display, complimenting the Panasonic Plasma family of televisions, further strengthening Panasonic's award winning Plasma line-up, which also includes televisions in the 42-inch class, 50-inch class and 58-inch class size. This year, Panasonic 's VIERA Plasma 1080p series feature a new panel with increased contrast ratio and an improved anti-reflective screen, a Game Mode, VIERA Link(TM), increased luminous efficiency, lead free panels and 100,000 hours to half brightness. Completing the 2008 Plasma HD line-up are two 720p Plasmas in 42-inch class and 50-inch class screen sizes. The entire VIERA line of HDTVs features a new aesthetic look.

Year after year, Panasonic has brought..." />
	<meta name="title" content="Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/panasonic-builds-upon-its-award-winning-line-of-1080p-full-hd-vierar-series-plasma-televisions.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic Corporation of North America, the principal U.S. subsidiary of Matsushita Electric Industrial Co., Ltd. (NYSE:MC) , the industry leader in high definition and Plasma TV, today introduced its 2008 line of Plasma full HD televisions. The VIERA 2008 full HD Plasma series introduces a new screen size - a 46-inch(1) class display, complimenting the Panasonic Plasma family of televisions, further strengthening Panasonic's award winning Plasma line-up, which also includes televisions in the 42-inch class, 50-inch class and 58-inch class size. This year, Panasonic 's VIERA Plasma 1080p series feature a new panel with increased contrast ratio and an improved anti-reflective screen, a Game Mode, VIERA Link(TM), increased luminous efficiency, lead free panels and 100,000 hours to half brightness. Completing the 2008 Plasma HD line-up are two 720p Plasmas in 42-inch class and 50-inch class screen sizes. The entire VIERA line of HDTVs features a new aesthetic look.

Year after year, Panasonic has brought..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=871', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/panasonic-builds-upon-its-award-winning-line-of-1080p-full-hd-vierar-series-plasma-televisions.php">Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions</a></td>
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
				<p class="prtitle">Panasonic Builds Upon Its Award Winning Line Of 1080p Full HD Viera(R) Series Plasma Televisions</p>

<center><i>2008 Plasmas Feature New Screen Size, New Panel, Improved Picture Quality, Increased Production, Continuation of Unique Concierge Program To Reinforce Industry Leadership and Commitment to Excellence</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 6 /PRNewswire/</B> -- Panasonic Corporation of North America, the principal U.S. subsidiary of Matsushita Electric Industrial Co., Ltd. (NYSE:MC) , the industry leader in high definition and Plasma TV, today introduced its 2008 line of Plasma full HD televisions. The VIERA 2008 full HD Plasma series introduces a new screen size - a 46-inch(1) class display, complimenting the Panasonic Plasma family of televisions, further strengthening Panasonic's award winning Plasma line-up, which also includes televisions in the 42-inch class, 50-inch class and 58-inch class size. This year, Panasonic 's VIERA Plasma 1080p series feature a new panel with increased contrast ratio and an improved anti-reflective screen, a Game Mode, VIERA Link(TM), increased luminous efficiency, lead free panels and 100,000 hours to half brightness. Completing the 2008 Plasma HD line-up are two 720p Plasmas in 42-inch class and 50-inch class screen sizes. The entire VIERA line of HDTVs features a new aesthetic look.</p>

<p>Year after year, Panasonic has brought new picture improving technologies to flat panels and 2008's increased line-up continues that trend. With the digital transition on the horizon and an increased awareness among consumers about high definition, consumers now demand blacker blacks, faster response time and the billions of colors that Panasonic VIERA full HD Plasmas deliver.</p>

<p>"Panasonic's superior picture quality, connectivity and service continues to distinguish the 2008 line, which we have re-branded VIERA. VIERA means a new visual era, and our 2008 line of HD Plasmas is committed to providing the consumer with televisions that do indeed usher in a new era of television. We firmly believe that the television is the center of the living room with an emphasis on family time spent experiencing entertainment together via a variety of audio and visual products," said Dennis Eppel, Vice President, Panasonic Display Products Company. "Not only have our Plasma displays received critical acclaim from consumer and trade publications, but Panasonic has maintained the market lead for the majority of the last three years. Panasonic has never rested on its laurels and the 2008 VIERA line continues that philosophy. And, all of our Plasmas continue to include an SD Memory Card slot, a unique feature that we have found to be a differentiator."</p>

<p>The VIERA PZ80 series introduces the new 46-inch class display -- the TH-46PZ80 -- to the 2008 line, that includes three screen sizes, all with 1080p resolution. The 42 inch class TH-42PZ80 and the 50-inch class TH-50PZ80 complete the PZ80 series and feature Game Mode, VIERA Link, an SD Memory Card slot, HDMI connections and an improved native contrast ratio of 20,000:1. . The Game Mode minimizes the time lag when displaying game images on the Plasma screen. The Mode synchronizes the response of the game image to the player's operation, thereby producing an extremely clear image with no motion artifacts. VIERA Link allows the consumer to operate all VIERA Link equipped components with a singe remote.</p>

<p>The VIERA PZ85 series includes three screen sizes, all with 1080p resolution - the 42-inch class TH-42PZ85, the 46-inch class TH-46PZ85 and the 50-inch class TH-50PZ85. These full HD Plasma televisions feature an improved native contrast ratio of 30,000:1; Game Mode; VIERA Link; an SD memory Card slot and a PC input. In addition, the speakers are now hidden in the front.</p>

<p>The VIERA PZ800 series of 1080p Plasma televisions includes four screen sizes, the 42-inch class TH-42PZ800, the 46-inch class TH-46PZ800, the 50-inch class TH-50PZ800 and the 58-inch class TH-58PZ800. The PZ800 series meets the THX(TM) Certified Display specifications, signifying the highest standards of performance and quality. The PZ800 Plasma televisions feature an improved native contrast ratio of 30,000:1; Game Mode; VIERA Link; a PC Input, four HDMI connections and an all new one sheet of glass design concept.</p>

<p>While Panasonic's focus for 2008 is on Full High Definition 1080p Plasma displays, there are two 720p High Definition Plasmas in the 2008 line-up. The VIERA 42-inch class TH-42PX80 and VIERA 50-inch class TH-50PX80 feature an improved Native contrast resolution of 15,000:1; Game Mode; an SD Memory Card Slot, VIERA Link(TM), which allows the consumer to operate multiple VIERA Link equipped components with one remote, and three HDMI connections to insure the highest audio/video quality</p>

<p>To emphasize its commitment to excellence in both technology and customer service, Panasonic's unique VIERA Concierge program continues to be a success. "Panasonic is very proud of the Concierge Program's acceptance and success," said Eppel. "Panasonic is intensely focused on its commitment to excellence in customer service. The Concierge program is another differentiator in the market, providing the customer with 'peace-of-mind' services. Furthermore, our continuing commitment to Plasma technology is evidenced in the construction of Panasonic's fifth Plasma panel factory in Amagasaki. Expected to be completed in 2009, the new factory will have a production capacity of 1,000,000 sets a month."</p>

<p><br />
<B>About Panasonic Consumer Electronics Company</B></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), a market and technology leader in High Definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Matsushita Electric Industrial Co. Ltd. (NYSE:MC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations. Panasonic's exclusive Panasonic Plasma Concierge customer support program (888-972-6276) is administered through its Virginia-based Call Center, recognized as a Certified "Center of Excellence" by the Center for Customer-Driven Quality(TM) at Purdue University. Information about Panasonic products is available at www.panasonic.com. Additional company information for journalists is available at www.panasonic.com/pressroom.</p>

<pre>   Model          Features                             Availability     SRP*
   TH-50PZ800     1080p resolution, one sheet           Spring, 2008    TBD
                  of glass design; native
                  contrast ratio  30,000:1;
                  THX; 24p native reproduction;
                  Game Mode; SD Memory Card slot;
                  four HDMI connections;
                  PC-Input; VIERA Link

<p>   TH-46PZ800     1080p resolution, one sheet           Spring, 2008     TBD<br />
                  of glass design; native<br />
                  contrast ratio  30,000:1; THX;<br />
                  24p native reproduction; Game<br />
                  Mode; SD Memory Card slot; four<br />
                  HDMI connections; PC-Input;<br />
                  VIERA Link</p>

<p>   TH-42PZ800     1080p resolution, one sheet of        Spring 2008      TBD<br />
                  glass design; native contrast<br />
                  ratio  30,000:1; THX; 24p native<br />
                  reproduction; Game Mode; SD<br />
                  Memory Card slot; four HDMI<br />
                  connections; PC-Input; VIERA<br />
                  Link</p>

<p>   TH-50PZ85      1080p resolution; Native contrast     Spring 2008      TBD<br />
                  ratio 30,000:1; 24p native<br />
                  reproduction; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections;<br />
                  PC Input; VIERA Link</p>

<p>   TH-46PZ85      1080p resolution; Native contrast     Spring 2008      TBD<br />
                  ratio 30,000:1; 24p native<br />
                  reproduction; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections;<br />
                  PC Input; VIERA Link</p>

<p>   TH-42PZ85      1080p resolution; Native contrast     Spring 2008      TBD<br />
                  ratio 30,000:1; one sheet of glass<br />
                  design; 24p native reproduction;<br />
                  Game Mode; SD Memory Card slot;<br />
                  three HDMI connections; PC Input;<br />
                  VIERA Link</p>

<p>   TH-50PZ80      1080p resolution; native contrast     Spring 2008      TBD<br />
                  ratio 20,000:1; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections:<br />
                  VIERA Link</p>

<p>   TH-46PZ80      1080p resolution; native contrast     Spring, 2008     TBD<br />
                  ratio 20,000:1; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections;<br />
                  VIERA Link</p>

<p>   TH-42PZ80      1080p resolution; native contrast     Spring, 2008     TBD<br />
                  ratio 20,000:1; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections;<br />
                  VIERA Link</p>

<p>   TH-50PX80      720p resolution; native contrast      Spring, 2008     TBD<br />
                  ratio 15,000:1; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections;<br />
                  VIERA Link</p>

<p>   TH-42PX80      720p resolution; native contrast      Spring, 2008     TBD<br />
                  ratio 15,000:1; Game Mode; SD Memory<br />
                  Card slot; three HDMI connections;<br />
                  VIERA Link</p>

<p>  (1). Measured diagonally<br />
</pre></p>

<p> (1). Measured diagonally</p>

<p>(2). Digital Cinema Initiatives, LLC (DCI) was crated in March, 2002 and is a joint venture of Disney, Fox, Paramount, Sony Pictures Entertainment, Universal and Warner Bros. Studios. DCI's primary purpose is to establish and document voluntary specifications for an open architecture for digital cinema that ensures a uniform and high level of technical performance, reliability and quality control.</p>

<p>*Suggested retail price. All prices are in U.S. dollars.</p>

<p>Specifications are subject to change without notice.</p>

<p>Source: Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2008 11:39 PM</b>
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
			<?=getComments(871)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 871)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/panasonic-builds-upon-its-award-winning-line-of-1080p-full-hd-vierar-series-plasma-televisions.php" type="text/javascript" charset="utf-8"></script>
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