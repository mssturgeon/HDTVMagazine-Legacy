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
		AND e.entry_id = 854";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 854 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 854 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 854";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/vizio-introduces-new-generation-plasma-tvs-including-full-1080p-hd-models-and-silicon-optix-hqv-processing.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 854";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing" />
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
	<title>HDTV Magazine - VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing</title>
	<meta name="keywords" content="high definition, silicon optix, home theatre, picture picture, contrast ratio, plasma, new, high, definition, models, full, hqv, including, hdmi, performance, processing, picture, video, optix, silicon, sound, include, home, system, offer" />
	<meta name="description" content="VIZIO, America's Fastest Growing Flat Panel HDTV Company, today introduced seven new plasma High-Definition Televisions with advanced processing power and the latest technological advancements. Offerings will include the feature rich VP504F-50&quot; Full High Definition, 1080p Plasma Display featuring Silicon Optix HQV Processing and the all new VP605F-60&quot; Full high-Definition 10800p display. In addition, VIZIO will release the all new VIZIO VP series of products including the VP322-32&quot; Plasma ($689), VP422-42&quot; Plasma ($999), VP423-42&quot; Plasma ($999), and VP503-50&quot; Plasma ($1399) all with High Definition 720p technology. VIZIO also reintroduces the VIZIO Jive VP500 and new VP501 All-in-One home theatre solution which include 50&quot; Plasma technology alongside a full Dolby Digital 5.1 surround sound system, now also featuring..." />
	<meta name="title" content="VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/vizio-introduces-new-generation-plasma-tvs-including-full-1080p-hd-models-and-silicon-optix-hqv-processing.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="VIZIO, America's Fastest Growing Flat Panel HDTV Company, today introduced seven new plasma High-Definition Televisions with advanced processing power and the latest technological advancements. Offerings will include the feature rich VP504F-50&quot; Full High Definition, 1080p Plasma Display featuring Silicon Optix HQV Processing and the all new VP605F-60&quot; Full high-Definition 10800p display. In addition, VIZIO will release the all new VIZIO VP series of products including the VP322-32&quot; Plasma ($689), VP422-42&quot; Plasma ($999), VP423-42&quot; Plasma ($999), and VP503-50&quot; Plasma ($1399) all with High Definition 720p technology. VIZIO also reintroduces the VIZIO Jive VP500 and new VP501 All-in-One home theatre solution which include 50&quot; Plasma technology alongside a full Dolby Digital 5.1 surround sound system, now also featuring..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=854', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/vizio-introduces-new-generation-plasma-tvs-including-full-1080p-hd-models-and-silicon-optix-hqv-processing.php">VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing</a></td>
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
				<p class="prtitle">VIZIO Introduces New Generation Plasma TVs Including Full 1080p HD Models and Silicon Optix HQV Processing</p>

<p><B>- VIZIO continues heritage of high-value, low cost plasma HDTVs with eight new models, offering increased contrast ratio and longer life</p>

<p>- Exciting performance and price breakthroughs in 50" and 60" Full 1080p HDTV models</p>

<p>- An all-in-one solution with plasma flat panel display and complete 5.1- surround sound system</p>

<p>- Four other models, ranging from 32" to 50", offer astonishing prices for native 720p plasma HDTVs</p>

<p>- VIZIO's 1080p 50" model is the world's first plasma TV to offer Silicon Optix REON HQV processing for best of breed video performance<br />
</B><br />
<br /></p>

<p><B>IRVINE, Calif., Jan. 6 /PRNewswire/</B> -- VIZIO, America's Fastest Growing Flat Panel HDTV Company, today introduced seven new plasma High-Definition Televisions with advanced processing power and the latest technological advancements. Offerings will include the feature rich VP504F-50" Full High Definition, 1080p Plasma Display featuring Silicon Optix HQV Processing and the all new VP605F-60" Full high-Definition 10800p display. In addition, VIZIO will release the all new VIZIO VP series of products including the VP322-32" Plasma ($689), VP422-42" Plasma ($999), VP423-42" Plasma ($999), and VP503-50" Plasma ($1399) all with High Definition 720p technology. VIZIO also reintroduces the VIZIO Jive VP500 and new VP501 All-in-One home theatre solution which include 50" Plasma technology alongside a full Dolby Digital 5.1 surround sound system, now also featuring a move to 1080p resolution.</p>

<p><br />
<B>VIZIO VP605F 60" 1080P and VP504F 1080p with SILICON OPTIX HQV (Hollywood Quality Video) Processing</B></p>

<p>VIZIO jumps into Full High-Definition 1080p performance with a bang in 2008, launching an all-new series of ultra high-performance Plasma technology displays. While the new VIZIO VP605F boasts 60" of large screen high definition 1080p performance to capture the imagination of even the most discerning of consumers, the 50" VIZIO VP504F packs an enhanced punch with integrated Silicon Optix's REON HQV chip, ensuring the sharpest and most detailed image possible. Silicon Optix HQV's advanced noise reduction removes noise and artifacts caused by signal compression from cable and satellite providers. Since the HQV's REON chip can process two full channels of HD or SD channels, this allows users to achieve full resolution with picture-in-picture images.</p>

<p>With advanced technology built into both models, both Standard Definition (SD) and High Definition (HD) sources will bring out even the finest details. VIZIO's new 1080p plasma HDTV's, the VP504F and VP605F are compatible with all of today's popular input resolutions [1080p, 1080i, 720p, 480p and 480i] and use an integrated, DTV-compliant HD/QAM tuner so users can enjoy high definition and regular television programs with or without paid high definition service.</p>

<p>Both models are significantly brighter than previous VIZIO plasma models boasting an amazing contrast ratio up to 30,000:1 and include four HDMI v1.3 inputs, two of which are available as a side access HD Game port. Independent RGB adjustments allow users to fine-tune the sets' color settings plus a new remote control makes it even easier to operate TV and other system component functions.</p>

<p>Additional feature sets include true four-field motion adaptive de- interlace, 10-bit diagonal interpolator which removes jagged or stair step artifacts from de-interlaced video sources and true 10-bit processing which output 4:4:4 color processing which renders more than one billion colors.</p>

<p>VIZIO sets itself apart from the crowd in 2008 by being the first manufacturer to include a 6' HDMI cable within the carton in lieu of commonly inserted composite video and audio cables. HDMI allows the highest level of High Definition television video and audio to be transmitted through a single cable. VIZIO wants customers to experience the truest HD quality experience with their new VIZIO plasma display as well as a new side access HD Game port including 2 HDMI v1.3 inputs.</p>

<p>The new VIZIO VP504F and VIZIO VP605F are expected to launch in June 2008 with estimated selling prices of $1699 and $2899.</p>

<p><br />
<B>VIZIO VP500 and VP501</B></p>

<p>In 2007, VIZIO introduced the VIZIO Jive JV50P All-in-One home theatre solution which included a 50" Plasma High-Definition display and Dolby Digital 5.1 surround sound. VIZIO was the first TV manufacturer to offer this complete Home Theater solution and has improved its performance and capability.</p>

<p>For 2008, the VIZIO VP500 and new VIZIO VP501 will share honors in the growing popularity of home theatre enthusiast market in the All-in-One solution category. While the VP500 will retain its 50" Plasma technology and 720p resolution, its newest sibling will step it up a notch with Full High Definition 1080p performance. Each model will offer Picture-in-Picture, Picture-on-Picture, three HDMI, two component video, two composite and one RF input.</p>

<p>What makes the VIZIO VP500 system so unique however is the Dolby Digital 5.1 surround-sound system. Working in concert with integrated front, left /right speakers and center channel are two rear channel (left and right) speakers attached to a subwoofer. The subwoofer attaches wirelessly through 2.4GHz transmission to the VP500 system, completing the home theatre experience and eliminating wire clutter commonly experienced with other home theatre systems. The VIZIO Jive generates more than ample sound, even for the discerning listener pumping 560-watts total peak power (70-watts RMS) of high quality digital sound to maximize your VIZIO High Definition television experience.</p>

<p>In 2008, VIZIO will now include its all new, prized brushed aluminum- trimmed Learning remote control (VUR8). This remote control compliments the elegance of the VP500 and VP501 design with full-featured functionality including Picture-in-Picture controls.</p>

<p>The new VIZIO VP500 is expected to arrive in stores June 2008 with an estimated selling price of $1799. Pricing and availability on the new VP501 has not been set.</p>

<p><br />
<B>VIZIO VP322, VIZIO VP422, VIZIO VP423, VIZIO VP503</B></p>

<p>Rounding out VIZIO's family of plasma displays are the VP322, VP422, and VP423 all offering stunning 720p performance, rich deep black levels, outstanding color rendering and the latest connectivity options including HDMI version 1.3 inputs. All of VIZIO's high performance plasma's offer extremely fluid and uninterrupted motion, a significant advantage over LCD flat panels. Plasma TVs continue to be a leading choice for watching sporting events and action-packed movies.</p>

<p>The VP324 is a 32-inch set with 1024 X 720 resolution for consumers that would like the picture qualities of plasma but in a small cabinet size. The set's exceptional 15,000:1 contrast ratio ensures images have rich, deep blacks and brilliant colors providing a cost effective solution for displaying HD broadcast content and playing HD-DVD and Blu-Ray discs, which can be connected to one of the three HDMI video inputs.</p>

<p>The VP422/VP423 are VIZIO's 42-inch Plasma models with 1024 x 768 resolution, 20:000:1 contrast ratio and two HDMI inputs. Two 42" models, the VP422 will sell in discount retailers such as Wal-Mart and K-Mart and the VP423 will head for the shelves at club retailers such as Costco and Sam's Club, as well as traditional consumer electronics retailers like Circuit City and Sears. Also providing two HDMI inputs, the VP503 is a 50-inch plasma set that delivers a native resolution of 1365 x 768, is compatible with 1080p content, and displays a bright, rich image due to its 30,000:1 contrast ratio.</p>

<p>"Great looking plasma HDTVs including Full 1080p HD models are now attainable for everyone with our newest plasma line," says Laynie Newsome vice president sales for VIZIO, Inc. "We have successfully brought the best plasma technologies such as Silicon Optix's REON HQV video processing and features to high value flat panel TVs. We continue to find new ways to integrate the most- desired features and technologies, while keeping our products at the most reasonable prices in the industry."</p>

<p>The new VIZIO VP324, VP422, VP423, and VP503 are expected in May or June 2008 with estimated selling prices of $689, $999, $999 and $1399 respectively.</p>

<p>VIZIO will be displaying many of these models along with several other 2008 product introductions at their suite in the Wynn Hotel during CES.</p>

<p><br />
<B>About VIZIO</B></p>

<p>VIZIO, Inc. "Where Vision Meets Value," headquartered in Irvine, California, is America's Fastest Growing Flat Panel HDTV Company. The VIZIO brand has been seen and heard on TV and radio, including NBC's Today Show, ABC's Good Morning America and Live with Regis and Kelly, won numerous awards from leading publications including Good Housekeeping's Best Big-Screens, CNET's Top 10 Holiday Gifts, PC World's Best Buy, Sound & Vision's Editors Choice, Home Theater Magazine's Rave Award, PC Magazine's Editors Choice, AVRev.com's #1 Product We Love the Best and The Perfect Vision's Products of the Year. VIZIO is bringing vision to the consumer electronics market through practical innovation. VIZIO products offer customers advanced technologies at the most affordable value. Products include the VIZIO, Maximvs and Gallevia lines of Plasma and LCD HDTVs. Many of these products can be found at BJ's Wholesale, Circuit City, Costco Wholesale, Sam's Club, Sears, Wal-Mart, and other retailers nationwide along with authorized online partners. For more information, please call 888-VIZIOCE or visit on the web at www.VIZIO.com.</p>

<p>The V, VIZIO, Gallevia, Maximvs, Where Vision Meets Value names, phase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>Source: VIZIO, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2008 11:23 AM</b>
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
			<?=getComments(854)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 854)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/vizio-introduces-new-generation-plasma-tvs-including-full-1080p-hd-models-and-silicon-optix-hqv-processing.php" type="text/javascript" charset="utf-8"></script>
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