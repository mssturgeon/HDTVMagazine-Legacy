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
		AND e.entry_id = 1735";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1735 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1735 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1735";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/06/vizio-americas-1-hdtv-company-delivers-new-high-performance-tv-collection.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1735";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO America\'s #1 HDTV Company Delivers NEW High Performance TV Collection" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO America\'s #1 HDTV Company Delivers NEW High Performance TV Collection" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO America\'s #1 HDTV Company Delivers NEW High Performance TV Collection" />
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
	<title>HDTV Magazine - VIZIO America's #1 HDTV Company Delivers NEW High Performance TV Collection</title>
	<meta name="keywords" content="srs trusurround, lcd hdtv, july srs, hdtv srs, stylish finish, hdtv, lcd, srs, trusurround, full, models, new, xvt, series, truvolume, java, july, stylish, performance, value, technology, energy, high, finish, audio" />
	<meta name="description" content="VIZIO, America's #1 HDTV and Consumer Electronics Company, has unveiled its 2009 TV product lineup, with 31 new models in three product groups. With a comprehensive range that includes cutting-edge Smart Dimming(TM) backlight TruLED(TM) LCD technology, high-style/high performance products, and the best everyday value HDTVs, VIZIO's products are energy efficient, with each earning and exceeding Energy Star 3.0 qualification. Product releases are scheduled throughout the year, with numerous products now in-store and due to hit shelves soon.

This year..." />
	<meta name="title" content="VIZIO America's #1 HDTV Company Delivers NEW High Performance TV Collection" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO America's #1 HDTV Company Delivers NEW High Performance TV Collection" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/06/vizio-americas-1-hdtv-company-delivers-new-high-performance-tv-collection.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="VIZIO, America's #1 HDTV and Consumer Electronics Company, has unveiled its 2009 TV product lineup, with 31 new models in three product groups. With a comprehensive range that includes cutting-edge Smart Dimming(TM) backlight TruLED(TM) LCD technology, high-style/high performance products, and the best everyday value HDTVs, VIZIO's products are energy efficient, with each earning and exceeding Energy Star 3.0 qualification. Product releases are scheduled throughout the year, with numerous products now in-store and due to hit shelves soon.

This year..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1735', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/06/vizio-americas-1-hdtv-company-delivers-new-high-performance-tv-collection.php">VIZIO America's #1 HDTV Company Delivers NEW High Performance TV Collection</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June  8, 2009</b>
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
				<p class="prtitle">VIZIO America's #1 HDTV Company Delivers NEW High Performance TV Collection</p>

<center><i>- Number One shipper of Flat Panel HDTVs in USA unveils wide range of high-performance, high-value televisions

<p>- Flagship Extreme VIZIO Technology, XVT(TM) Series advances television's state of the art with NEW TruLED(TM) and 240Hz SPS(TM) (scenes per second), and Thin Line(TM) 120Hz models. (New XVT Models are in 55 - 32-inch screen sizes)</p>

<p>- JAVA(TM) designer collection within the NEW 'M' Series combines high style with Full HD 1080P resolution and 120Hz with Smooth Motion(TM) LCD HDTVs (New M Series Models are in 47 - 32-inch screen sizes)</p>

<p>- 'E' Series provide energy efficient EcoHD(TM) (20% below Energy Star 3.0), essential performance and exceptional value in small to mid screen sizes (New E Series Models are in 42 - 19-inch sizes)</p>

<p>- All models are energy efficient, meeting and exceeding Energy Star 3.0 requirements</i></center><br /><br />
<br /></p>

<p><br />
<B>IRVINE, Calif., June 8 /PRNewswire/ </B>-- VIZIO, America's #1 HDTV and Consumer Electronics Company, has unveiled its 2009 TV product lineup, with 31 new models in three product groups. With a comprehensive range that includes cutting-edge Smart Dimming(TM) backlight TruLED(TM) LCD technology, high-style/high performance products, and the best everyday value HDTVs, VIZIO's products are energy efficient, with each earning and exceeding Energy Star 3.0 qualification. Product releases are scheduled throughout the year, with numerous products now in-store and due to hit shelves soon.</p>

<p>"VIZIO's 2009 line of LCD HDTVs advances our mission to deliver the most advanced video and audio technologies to our customers with unequalled value and style," says Laynie Newsome, VIZIO Co-Founder and VP Sales and Marketing Communications. "Our customers have come to expect superior performance and design from VIZIO, and this new line has unprecedented value in every screen size."</p>

<p><br />
<B>Superior Performance Video & Audio</B></p>

<p>This year's Extreme VIZIO Technology XVT(TM) series again advances VIZIO performance with a Full HD 1080p lineup featuring both 120Hz with Thin Line(TM) stylish designs and 240Hz SPS models using Smooth Motion(TM) technology, with the most sophisticated models incorporating TruLED and Smart Dimming(TM) backlight technology. In all models, VIZIO has integrated USB video inputs that are enhanced and offer high quality 1080p video playback, along with Mega Dynamic Contrast Ratio(TM) of up to 2,000,000:1 makes for incredible black levels and almost three-dimensional imagery, they also include advanced user interface control with picture in picture and picture on picture functionality. VIZIO XVT models all include a universal learning backlit remote control.</p>

<p>All models have superior audio, thanks to SRS Labs Technology with TruSurround HD(TM), which enhances clarity and produces surround sound without external speakers, and TruVolume(TM), which eliminates annoying volume fluctuations when switching channels or when commercials come on and off.</p>

<p><B>New "XVT" Series Models and Features</B><br />
<pre><br />
  Model                 Description<br />
  SV320XVT - Thin Line  32" 1080p 120Hz Full HD LCD HDTV<br />
  SV370XVT - Thin Line  37" 1080p 120Hz Full HD LCD HDTV<br />
  SV421XVT              42" 1080p 240Hz SPS Full HD LCD HDTV<br />
  SV471XVT              47" 1080p 240Hz SPS Full HD LCD HDTV<br />
  VF550XVT              55" 1080p 120Hz Full HD LCD HDTV<br />
  VF551XVT              55" 1080p 240Hz SPS w/Smart Dimming Backlight TruLED</p>

<p>  HDMI<br />
  Inputs   Audio                            In-store    MSRP<br />
  3        SRS TruSurround HD & TruVolume   September   $749.99<br />
  3        SRS TruSurround HD & TruVolume   September   $849.99<br />
  4        SRS TruSurround HD & TruVolume   July        $1,199.99<br />
  4        SRS TruSurround HD & TruVolume   July        $1,499.99<br />
  5        SRS TruSurround HD & TruVolume   Now         $1,999.99<br />
  5        SRS TruSurround HD & TruVolume   September   $2,199.99<br />
</pre></p>

<p><br />
<B>Full HD in New Colors and Styles</B></p>

<p>VIZIO's designer lines combine 1080p Full HD performance with distinctive looking designs such as the VL series brushed JAVA(TM) color treatment, and VT series TVs that resemble luxurious wood picture frames to bring a stylish touch to consumers' viewing environments. Select models feature a 120Hz refresh rate with Smooth Motion technology, as well as VIZIO's new USB Multi-Media Feature that can display MPEG-2, H.264 and WMV9 video, JPEG photos, and MP3 music from a thumb drive or FAT32 hard drive. Many models have VIZIO's new "Pause Live TV" feature, which allows viewers to pause their programs without the need for a separate cable or satellite box. VIZIO's Mega Dynamic Contrast Ratio(TM) of up to 50,000:1 increases contrast ratio and picture quality, providing incredibly deep blacks. Each model also has multiple HDMI inputs and many include a side panel HDMI Game or Camera Input Port.</p>

<p><br />
<B>New "M" Series Models and Features</B><br />
<pre><br />
  Model                              Description<br />
  VA22LF                             22"   1080p Full HD LCD HDTV<br />
  VX240M                             24"   1080p Full HD LCD HDTV<br />
  VL260M with JAVA Stylish Finish    26"   1080p Full HD LCD HDTV<br />
  VL320M with JAVA Stylish Finish    32"   1080p Full HD LCD HDTV<br />
  VA320M                             32"   1080p Full HD LCD HDTV<br />
  VOJ320F                            32"   1080p Full HD LCD HDTV<br />
  VO37LF                             37"   1080p Full HD LCD HDTV<br />
  VO370M                             37"   1080p Full HD LCD HDTV<br />
  VL370M with JAVA Stylish Finish    37"   1080p Full HD LCD HDTV<br />
  VL420M with JAVA Stylish Finish    42"   1080p Full HD LCD HDTV<br />
  VA370M                             37"   1080p Full HD LCD HDTV<br />
  VT420M with JAVA Stylish Finish    42"   1080p 120Hz Full HD LCD HDTV<br />
  VT470M with JAVA Stylish Finish    47"   1080p 120Hz Full HD LCD HDTV<br />
  SV420M                             42"   1080p 120Hz Full HD LCD HDTV<br />
  SV470M                             47"   1080p 120Hz Full HD LCD HDTV<br />
  VL470M with JAVA Stylish Finish    47"   1080p 120Hz Full HD LCD HDTV<br />
  VF550M                             55"   1080p 120Hz Full HD LCD HDTV</p>

<p>  HDMI<br />
  Inputs  Audio                             In-store    MSRP<br />
  2       SRS TruSurround XT                Now         $349.99<br />
  2       SRS TruSurround HD                July        $349.99<br />
  2       SRS TruSurround HD                June        $479.99<br />
  3       SRS TruSurround HD                June        $649.99<br />
  4       SRS TruSurround XT                June        $649.99<br />
  2       SRS TruSurround XT                Now         $599.99<br />
  3       SRS TruSurround XT                Now         $729.99<br />
  3       SRS TruSurround XT                Now         $799.99<br />
  3       SRS TruSurround HD                July        $799.99<br />
  4       SRS TruSurround HD                July        $899.99<br />
  3       SRS TruSurround XT                September   $799.99<br />
  4       SRS TruSurround HD & TruVolume    July        $999.99<br />
  4       SRS TruSurround HD & TruVolume    July        $1,299.99<br />
  4       SRS TruSurround HD & TruVolume    June        $999.99<br />
  4       SRS TruSurround HD & TruVolume    Now         $1,299.99<br />
  4       SRS TruSurround HD                July        $1,299.99<br />
  5       SRS TruSurround HD & TruVolume    July        $1,799.99<br />
</pre></p>

<p><br />
<B>HDTV Value in Every Size</B></p>

<p>With screen sizes ranging from 19" to 32", VIZIO's "E" series HDTVs provide incredible value in small to midsized packages with stylish white or black piano colored bezels. Many of these sets offer Full HD 1080p performance, and all utilize SRS Labs' TruSurround technology to provide superior audio. Some models feature VIZIO's EcoHD(TM) technology, which lowers energy consumption as much as 20% below Energy Star 3.0 standards.</p>

<p><br />
<B>New "E" Series Models and Features</B><br />
<pre><br />
                                 HDMI<br />
  Model    Description           Inputs Audio               In-store MSRP<br />
  VA19L    19" 720p LCD HDTV     2      SRS TruSurround XT  Now      $249.99<br />
  VX200E   20" 720p LCD HDTV     2      SRS TruSurround HD  July     $299.99<br />
  VA220E   22" 720p LCD HDTV     2      SRS TruSurround XT  Now      $399.99<br />
  VA26L    26" 720p LCD HDTV     2      SRS TruSurround XT  Now      $449.99<br />
  VECO320L 32" 720p LCD HDTV     2      SRS TruSurround XT  Now      $499.99<br />
  VA320E   32" 720p LCD HDTV     3      SRS TruSurround HD  Now      $499.99<br />
  VO320E   32" 720p LCD HDTV     2      SRS TruSurround HD  Now      $499.99<br />
  VO420E   42" 1080p Full HD LCD 3      SRS TruSurround HD  Now      $849.99<br />
</pre></p>

<p><br />
<B>About VIZIO</B></p>

<p>VIZIO, Inc., "Where Vision Meets Value," headquartered in Irvine, California, is America's HDTV Company and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead major categories in U.S. TV sales. Since 2007 VIZIO HDTV shipments remain in the TOP ranks in the U.S. and are again #1 in Q1, 2009 with over 20% market share. VIZIO is committed to bringing feature-rich flat panel televisions to market at a value through practical innovation. VIZIO offers a broad range of award winning Plasma and LCD HDTVs including the new XVT series. VIZIO's products are found at Costco Wholesale, Sam's Club, Sears, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Good Housekeeping's Best Big-Screens, CNET's Top 10 Holiday Gifts and PC World's Best Buy among others. For more information, please call 888-VIZIOCE or visit on the web at www.VIZIO.com.</p>

<p>The V, VIZIO, XVT, TruLED, 240Hz SPS, Thin Line, Smooth Motion, JAVA, Where Vision Meets Value names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>Source: VIZIO, Inc. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June  8, 2009  5:23 AM</b>
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
			<?=getComments(1735)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1735)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/06/vizio-americas-1-hdtv-company-delivers-new-high-performance-tv-collection.php" type="text/javascript" charset="utf-8"></script>
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