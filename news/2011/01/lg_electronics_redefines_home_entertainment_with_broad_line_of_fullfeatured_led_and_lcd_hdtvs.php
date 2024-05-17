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
		AND e.entry_id = 4127";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4127 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4127 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4127";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/lg-electronics-redefines-home-entertainment-with-broad-line-of-fullfeatured-led-and-lcd-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4127";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs" />
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
	<title>HDTV Magazine - LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs</title>
	<meta name="keywords" content="inch class, class inch, inch diagonal, energy saving, class sizes, inch, class, diagonal, led, series, full, –, energy, features, home, hdtv, technology, saving, smarttv, consumers, sizes, electronics, smart, thx, certification" />
	<meta name="description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- &lt;/strong&gt;LG Electronics today introduced its 2011 LED and LCD HDTVs – led by advanced new display technology, versatile new 3D viewing options and internet-connected &quot;Smart TV&quot; capabilities – at the International Consumer Electronics Show (Booth #8205).

The LG LED and LCD HDTV 2011 lineup combines slim depth and bezel design with enhanced connectivity and abundant content options. Leading the way to the ultimate home entertainment experience, the LW9500 and LW7700 sets are LG's first NANO FULL LED 3D-ready models available in the United States. Setting a new standard in 3D comfort, the LW6500 and LW5600 sets will be LG's first models available..." />
	<meta name="title" content="LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/lg-electronics-redefines-home-entertainment-with-broad-line-of-fullfeatured-led-and-lcd-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- &lt;/strong&gt;LG Electronics today introduced its 2011 LED and LCD HDTVs – led by advanced new display technology, versatile new 3D viewing options and internet-connected &quot;Smart TV&quot; capabilities – at the International Consumer Electronics Show (Booth #8205).

The LG LED and LCD HDTV 2011 lineup combines slim depth and bezel design with enhanced connectivity and abundant content options. Leading the way to the ultimate home entertainment experience, the LW9500 and LW7700 sets are LG's first NANO FULL LED 3D-ready models available in the United States. Setting a new standard in 3D comfort, the LW6500 and LW5600 sets will be LG's first models available..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4127', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/lg-electronics-redefines-home-entertainment-with-broad-line-of-fullfeatured-led-and-lcd-hdtvs.php">LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">LG Electronics Redefines Home Entertainment with Broad Line of Full-Featured LED and LCD HDTVs</p>

<center><i>New LED Technology, Combined with 3D, SmartTV and Enhanced Picture Quality, Delivers Uncompromised Options for Home Theater Enthusiasts</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- </strong>LG Electronics today introduced its 2011 LED and LCD HDTVs – led by advanced new display technology, versatile new 3D viewing options and internet-connected "Smart TV" capabilities – at the International Consumer Electronics Show (Booth #8205).</p>

<p>The LG LED and LCD HDTV 2011 lineup combines slim depth and bezel design with enhanced connectivity and abundant content options. Leading the way to the ultimate home entertainment experience, the LW9500 and LW7700 sets are LG's first NANO FULL LED 3D-ready models available in the United States. Setting a new standard in 3D comfort, the LW6500 and LW5600 sets will be LG's first models available for home entertainment consumers containing polarized LG Cinema 3D technology. LG's Cinema 3D offers a theater-like experience at home with lightweight glasses, crisp images and clear wide-angle viewing.</p>

<p>INFINIA is the flagship of LG's 21-model LED HDTV* line – including nine new series of LED HDTVs and four new series of LCD HDTVs. Leading these introductions is the NANO FULL LED series that pushes the boundaries of viewing pleasure with NANO Lighting Technology, which produces bright, clear and smooth picture. An extremely thin film printed with a proprietary light dispersion pattern and combined with a full array of LEDs disperses light more evenly across the screen, creating pictures that are brighter and more uniform than conventional edge-lit LED sets. What's more, an Anti-Reflection Panel on the screen minimizes reflection from external light sources, including sunlight, to provide a crystal clear picture.</p>

<p>The LW9500 and LW7700 were recognized with CES 2011 Innovations Awards, including the "Best of Innovations" distinction in the Video Displays category for the LW9500.</p>

<p><br />
<strong>Setting Premier Expectations</strong></p>

<p>Broadening consumer entertainment options, LG's latest series of HDTVs gives consumers superior picture quality, advanced energy saving options and flexible access to content-on-demand. LG's LED HDTVs challenge consumers' current perceptions of home entertainment by illustrating what's possible with superior display technology.</p>

<p>LG's NANO FULL LED and Full LED Slim series (models LW9500, LW7700 and LZ9700) are expected to achieve THX 2D and 3D Display Certification* – the industry standard for having the correct gamma, luminance, and color temperature. To earn THX 3D display certification, these models passed more than 400 laboratory tests evaluating left and right eye images for color accuracy, cross-talk, viewing angles and video processing performance. In addition to THX 3D Display Certification, this series had to pass THX certification for their superior picture quality in 2D, which must be achieved before passing THX 3D Display Certification. THX Certification ensures that consumers bring home an uncompromised HD experience with picture quality the way the director intended.</p>

<p>NANO FULL LED and FULL LED Slim 3D works with the use of active shutter glasses and an RF emitter built into the television.</p>

<p><br />
<strong>Connectivity</strong></p>

<p>Nine out of 13 of the new LED LCD series boast a connectivity package with a variety of entertainment options, including the brand new LG SmartTV*. LG SmartTV is an easy way to access limitless content, thousands of movies, customizable apps, videos and browse the Web, all organized in a simple to use interface. And when consumers can just point and choose selections with LG's unique motion-controlled Magic Motion Remote control, it's even simpler.</p>

<p>LG also has incorporated the Digital Living Network Alliance (DLNA) technology across all SmartTV-enabled HDTVs. DLNA allows consumers to access content stored on other DLNA-certified devices within the home, such as computers or an LG NAS device, making content options almost limitless.</p>

<p>Providing easy options for connecting to the Internet, in addition to the wired Ethernet jack, all LG SmartTV-enabled sets can integrate into a wireless home network by using a USB wireless broadband adaptor (included). All LG SmartTV models also support multi-media playback from a connected USB device including photos (JPEG), music (MP3) and video (DivX HD).</p>

<p><br />
<strong>Energy Savings</strong></p>

<p>Understanding consumers' desire for products that reduce their household energy costs, most of LG's LED HDTVs have a variety of energy-saving features, such as Intelligent Sensor, to automatically calibrate and optimize brightness, contrast, white balance and color, based on the ambient light in the room, saving on energy output under most circumstances. Additionally, ISFccc calibration options allow consumers to work with a professional to set "day" and "night" levels for optimal viewing and brightness levels. All of LG's 2011 LED series also qualify for ENERGY STAR&reg; certification.</p>

<p>In total, LG unveiled 13 new series of LED and LCD HDTV models for consumers – creating a robust HDTV line up of advanced picture quality, wireless technology and diverse screen sizes. Full details on the series are below:</p>

<p>INFINIA LZ9700 (72-inch class size*) – LG's largest consumer TV, Full HD 1080p 3D-enabled HDTV features Full LED Slim technology with Local Dimming, TruMotion 480Hz and THX 3D and 2D Display Certification (pending). Also includes LG SmartTV with Magic Remote, DLNA, Wi-Fi capability (adaptor included) and Smart Energy Saving features.</p>

<p>INFINIA LW9500 Series (55- and 60-inch class sizes*) – Full HD 1080p 3D-enabled HDTV features LG's thinnest design with ultra-slim bezel, NANO FULL LED technology, TruMotion 480Hz and THX 3D and 2D Display Certification (pending). Also includes LG SmartTV with Magic Remote, DLNA, Wi-Fi capability (adaptor included) and Smart Energy Saving features.</p>

<p>INFINIA LW7700 Series (47- and 55-inch class sizes*) – Full HD 1080p 3D-enabled HDTV features NANO FULL LED technology, TruMotion 240Hz and THX 3D and 2D Display Certification (pending). Also includes LG SmartTV with Magic Remote, DLNA, Wi-Fi capability (adaptor included) and Smart Energy Saving features.</p>

<p>INFINIA LW6500 Series (47-, 55- and 65-inch class sizes*) – Provides consumers with Full HD 1080p, LG Cinema 3D, TruMotion 240Hz, and LED Plus technology with local dimming capability. With LG SmartTV, the Magic Remote and Wi-Fi capability (adaptor included), content is easier to access than ever before. LG Cinema 3D uses polarized, lightweight glasses. Four pairs of glasses are included with each set.</p>

<p>INFINIA LW5600 Series (47- and 55-inch class sizes*) – Provides consumers with Full HD 1080p, LG Cinema, TruMotion 120Hz, and LED Plus technology with local dimming capability. With LG SmartTV, the Magic Remote and Wi-Fi capability (adaptor included), content is easier to access than ever before. LG Cinema 3D uses polarized, lightweight glasses. Four pairs of glasses are included with each set.</p>

<p>INFINIA LV5500 Series (42-, 47-, and 55-inch class sizes*) – Full HD 1080p HDTV series includes LED lighting and TruMotion 120Hz. Also includes LG SmartTV, DLNA, Wi-Fi capability (adaptor included) and Smart Energy Saving features.</p>

<p>LV3700 Series (42-, 47-, and 55-inch class sizes*) – Full HD 1080p HDTV series includes LED lighting, LG SmartTV, DLNA, Wi-Fi capability (adaptor included) and Smart Energy Saving features.</p>

<p>LV3500 Series (37- and 42-inch class sizes*) – Mid-sized Full HD 1080p HDTV series includes LED lighting and Smart Energy Saving features.</p>

<p>LV2500 Series (22-, 26- and 32-inch class sizes*) – Provides a smaller screen size for consumers in a 720p HD model with LED lighting and Smart Energy Saving features.</p>

<p>LK530 Series (42-, 47- and 55-inch class sizes*) – A Full HD 1080p HDTV, this LCD HDTV series boasts TruMotion 120Hz performance for reduced motion blur. Other features include: LG SmartTV, DLNA, Wi-Fi capability (adaptor included) and Smart Energy Saving features.</p>

<p>LK520 Series (42-, 47- and 55-inch class sizes*) – A Full HD 1080p HDTV, this LCD HDTV series boasts TruMotion 120Hz performance for reduced motion blur and Smart Energy Saving features.</p>

<p>LK450 Series (32-, 37- and 42-inch class sizes*) – A Full HD 1080p HDTV in a variety of sizes to fit almost any room in the home and features Smart Energy Saving features.</p>

<p>LK330 Series (32-inch class size*) – Provides a smaller screen size for consumers in a 720p HD model and features Smart Energy Saving features.</p>

<p>With class sizes ranging from 22- to 72-inches, LG's LCD HDTV models provide a variety of flat panel options for any room in the home – all built with LG's four core technologies:</p>

<p>    Picture Wizard: Provides consumers with an easy-to-use seven-step calibration process that allows them to change picture settings without hiring an expert.<br />
    Intelligent Sensor: Automatically calibrates and optimizes brightness, contrast, white balance and color, based on the brightness and color temperature of lighting in the room – thereby saving on energy output in most circumstances.<br />
    Clear Voice II: An enhancement to Clear Voice, this feature customizes volume settings by 12 distinct voice zoom levels, helping ensure consumers don't miss a single line of dialogue during action sequences.<br />
    AV Mode II: Includes three AV modes preset to optimize picture and sound settings based on Cinema, Sports or Game content, which can be easily set with the remote control. </p>

<p><br />
For more information and product images, please visit LG's online press kit at <a target="_blank" href="http://www.lgnewsroom.com/CES2011/">www.lgnewsroom.com/CES2011</a>.</p>

<p>* Designs, features and specifications subject to change without notice.</p>

<p>* 72LZ9700 and 55LW9500 are confirmed. THX certification for LW7700 is pending final testing and approval by THX Ltd.</p>

<p>* LG LED TVs are LCD TVs with LED backlighting.</p>

<p>*Internet connection &amp; subscriptions required and sold separately. The Magic Motion Remote does not come equipped with all LG SmartTV enabled TVs and separate purchase may be required.</p>

<p>*For a small percentage of the population, the viewing of stereoscopic 3D video may cause discomfort such as dizziness or nausea. If you experience any of these symptoms, discontinue using the 3D functionality and contact your health care provider.  3D glasses required and sold separately.</p>

<p>*72LZ9700 72-inch class/72.0-inch diagonal<br />
*60LW9500 60-inch class/59.8-inch diagonal<br />
*55LW9500 55-inch class/54.6-inch diagonal<br />
*55LW7700 55-inch class/54.6-inch diagonal<br />
*47LW7700 47-inch class/47.0-inch diagonal<br />
*65LW6500 65-inch class/64.7-inch diagonal<br />
*55LW6500 55-inch class/54.6-inch diagonal<br />
*47LW6500 47-inch class/47.0-inch diagonal<br />
*55LW5600 55-inch class/54.6-inch diagonal<br />
*47LW5600 47-inch class/47.0-inch diagonal<br />
*55LV5500 55-inch class/54.6-inch diagonal<br />
*47LV5500 47-inch class/47.0-inch diagonal<br />
*42LV5500 42-inch class/42.0-inch diagonal<br />
*55LV3700 55-inch class/54.6-inch diagonal<br />
*47LV3700 47-inch class/47.0-inch diagonal<br />
*42LV3700 42-inch class/42.0-inch diagonal<br />
*42LV3500 42-inch class/42.0-inch diagonal<br />
*37LV3500 37-inch class/37.0-inch diagonal<br />
*32LV2500 32-inch class/31.5-inch diagonal<br />
*26LV2500 26-inch class/26.0-inch diagonal<br />
*22LV2500 22-inch class/21.6-inch diagonal<br />
*55LK530 55-inch class/54.6-inch diagonal<br />
*47LK530 47-inch class/47.0-inch diagonal<br />
*42LK530 42-inch class/42.0-inch diagonal<br />
*55LK520 55-inch class/54.6-inch diagonal<br />
*47LK520 47-inch class/47.0-inch diagonal<br />
*42LK520 42-inch class/42.0-inch diagonal<br />
*42LK450 42-inch class/42.0-inch diagonal<br />
*37LK450 37-inch class/37.0-inch diagonal<br />
*32LK450 32-inch class/31.5-inch diagonal<br />
*32LK330 32-inch class/31.5-inch diagonal</p>

<p><br />
<strong>About LG Electronics, Inc.</strong></p>

<p>LG Electronics, Inc. (KSE: 066570.KS) is a global leader and technology innovator in consumer electronics, mobile communications and home appliances, employing more than 80,000 people working in over 115 operations around the world. With 2009 global sales of 55.5 trillion Korean won (USD 43.4 billion), LG comprises four business units – Home Entertainment, Mobile Communications, Home Appliance, and Air Conditioning &amp; Energy Solutions. LG is one of the world's leading producers of flat panel TVs, audio and video products, mobile handsets, air conditioners and washing machines. LG has signed a long-term agreement to become both a Global Partner and a Technology Partner of Formula 1&trade;. As part of this top-level association, LG acquires exclusive designations and marketing rights as the official consumer electronics, mobile phone and data processor of this global sporting event. For more information, please visit <a target="_blank" href="http://www.lg.com/">www.lg.com</a>.</p>

<p><br />
<strong>About LG Electronics USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances, commercial displays, air conditioning systems and solar energy solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.lg.com/">www.lg.com</a>.</p>

<p>SOURCE LG Electronics USA Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2011  9:19 PM</b>
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
			<?=getComments(4127)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4127)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/lg-electronics-redefines-home-entertainment-with-broad-line-of-fullfeatured-led-and-lcd-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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