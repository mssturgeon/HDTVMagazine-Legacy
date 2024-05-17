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
		AND e.entry_id = 1652";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1652 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1652 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1652";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/nvidia-announces-3d-visionthe-worlds-first-highdefinition-3d-stereo-solution-for-the-home.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1652";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NVIDIA Announces 3D Vision-The World\'s First High-Definition 3D Stereo Solution for the Home" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NVIDIA Announces 3D Vision-The World\'s First High-Definition 3D Stereo Solution for the Home" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="NVIDIA Announces 3D Vision-The World\'s First High-Definition 3D Stereo Solution for the Home" />
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
	<title>HDTV Magazine - NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home</title>
	<meta name="keywords" content="vice president, nvidia physx, nvidia corporation, visual computing, solution home, nvidia, vision, games, stereoscopic, geforce, game, gaming, new, president, glasses, high, stereo, home, world, vice, solution, technology, consumer, our, time" />
	<meta name="description" content="NVIDIA Corporation, in conjunction with the world's leading content developers, display manufacturers, and PC OEMs and system builders, is pleased to announce NVIDIA(R) 3D Vision(TM) for GeForce(R), the world's first high-definition 3D stereo solution for the home.

Forming the foundation for a new consumer 3D stereo ecosystem for gaming and home entertainment PCs, 3D Vision is a combination of high-tech wireless glasses, a high-power IR emitter and advanced software that automatically transforms..." />
	<meta name="title" content="NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/nvidia-announces-3d-visionthe-worlds-first-highdefinition-3d-stereo-solution-for-the-home.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="NVIDIA Corporation, in conjunction with the world's leading content developers, display manufacturers, and PC OEMs and system builders, is pleased to announce NVIDIA(R) 3D Vision(TM) for GeForce(R), the world's first high-definition 3D stereo solution for the home.

Forming the foundation for a new consumer 3D stereo ecosystem for gaming and home entertainment PCs, 3D Vision is a combination of high-tech wireless glasses, a high-power IR emitter and advanced software that automatically transforms..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1652', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/nvidia-announces-3d-visionthe-worlds-first-highdefinition-3d-stereo-solution-for-the-home.php">NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  8, 2009</b>
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
				<p class="prtitle">NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home</p>

<center><i>NVIDIA 3D Vision for GeForce Brings New Dimension to Photos, Videos and Games</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 8 /PRNewswire-FirstCall/ -- CONSUMER ELECTRONICS SHOW (CES) 2009</B> -- NVIDIA Corporation, in conjunction with the world's leading content developers, display manufacturers, and PC OEMs and system builders, is pleased to announce NVIDIA(R) 3D Vision(TM) for GeForce(R), the world's first high-definition 3D stereo solution for the home.</p>

<p>Forming the foundation for a new consumer 3D stereo ecosystem for gaming and home entertainment PCs, 3D Vision is a combination of high-tech wireless glasses, a high-power IR emitter and advanced software that automatically transforms hundreds of PC games into full stereoscopic 3D experiences. Designed to work with the new pure Samsung(R) and ViewSonic(R) 120 Hz LCD monitors, Mitsubishi(R) DLP(R) HDTVs, and the DepthQ HD 3D Projector by Lightspeed Design, Inc, 3D Vision unlocks crystal-clear, flicker-free 3D stereo imagery perfect for driving new experiences in 3D gaming, 3D movies, and 3D photography.</p>

<p>"Along with gaming innovations in Microsoft Windows and DirectX, NVIDIA 3D Vision proves there's never been a better time to be a PC gamer," said Corey Rosemond, group marketing manager, Windows Gaming. "By including support for previously released and upcoming Games for Windows and Games for Windows -- LIVE titles, PC gamers can expect a new level of immersion in full stereoscopic 3D, and enjoy broad support for the hottest games."</p>

<p>Powered by NVIDIA GeForce GPUs, the number one choice of gamers worldwide, 3D Vision is the world's highest quality stereoscopic 3D consumer solution, consisting of:</p>

<p>-- High-Tech, Wireless Active Shutter Glasses</p>

<p>-- Designed with top-of-the-line optics to deliver 2X the resolution per eye and ultra-wide viewing angles versus passive glasses. Comfortable to wear and modeled after modern sunglasses, offering a stylish and lightweight alternative to traditional 3D glasses. Fully untethered solution, offering free range of motion and up to 20 feet of wireless 3D viewing.</p>

<p>-- USB-based, High Power IR Emitter</p>

<p>-- Transmits data directly to active shutter glasses within a 20 foot radius and contains an easy to use real-time 3D adjustment dial.</p>

<p>-- Maximum Display Flexibility</p>

<p>-- Designed for pure ViewSonic and Samsung 120 Hz LCD monitors, Mitsubishi DLP 1080p HDTVs, and DepthQ HD 3D projectors, unlocking crystal- clear, flicker-free stereoscopic 3D gaming for multiple viewing solutions.</p>

<p>-- Out of the Box Game Compatibility</p>

<p>-- Advanced NVIDIA software automatically converts over 300 games to work in 3D stereo out of the box, without the need for special game patches. In addition, NVIDIA's "The Way It's Meant to Be Played" program ensures that future games will support 3D Vision. 3D Vision is also the only stereoscopic 3D gaming solution to fully support NVIDIA SLI(R), NVIDIA PhysX(TM), and Microsoft(R) DirectX(R) 10 technologies.</p>

<p>-- Extended Usability On a Single Charge</p>

<p>-- A single charge using a standard USB cable enables over 40 hours of continuous 3D stereoscopic gaming. Intelligent circuit design built into the glasses automatically shuts the glasses off after 10 minutes of inactivity to preserve battery life.</p>

<p>-- Support for 3D Stereo Photography and Movies</p>

<p>-- Includes a free 3D Vision viewer which allows consumers to take in-game screenshots and view them in 3D stereo, or import and view stereoscopic pictures and movies from a variety of different capture sources and online web photo galleries.</p>

<p>"For gamers, 3D Vision for GeForce represents a whole different way of experiencing the game, and for developers, it unlocks the potential of making the game literally pop off the screen," said Ujesh Desai, vice president of GeForce desktop business at NVIDIA. "From games to movies to photography, 3D Vision delivers a truly immersive awesome 3D experience."</p>

<p>3D Vision for GeForce is available starting today from leading U.S. e-tailers including www.compusa.com, www.tigerdirect.com, www.microcenter.com; as well as direct from www.nvidia.com for a suggested MSRP of $199 USD. Worldwide availability will be announced later, in the first quarter.</p>

<p><br />
<B>About NVIDIA</B></p>

<p>NVIDIA (NASDAQ:NVDA) is the world leader in visual computing technologies and the inventor of the GPU, a high-performance processor which generates breathtaking, interactive graphics on workstations, personal computers, game consoles, and mobile devices. NVIDIA serves the entertainment and consumer market with its GeForce(R) products, the professional design and visualization market with its Quadro(R) products, and the high-performance computing market with its Tesla(TM) products. NVIDIA is headquartered in Santa Clara, Calif. and has offices throughout Asia, Europe, and the Americas. For more information, visit www.nvidia.com .</p>

<p>Certain statements in this press release including, but not limited to, statements as to: the benefits, features, impact, and capabilities of NVIDIA 3D Vision, NVIDIA GeForce GPUs, and NVIDIA PhysX technology; and the impact of stereoscopic on video games; are forward-looking statements that are subject to risks and uncertainties that could cause results to be materially different than expectations. Important factors that could cause actual results to differ materially include: development of more efficient or faster technology; adoption of the CPU for parallel processing; design, manufacturing or software defects; the impact of technological development and competition; changes in consumer preferences and demands; customer adoption of different standards or our competitor's products; changes in industry standards and interfaces; unexpected loss of performance of our products or technologies when integrated into systems as well as other factors detailed from time to time in the reports NVIDIA files with the Securities and Exchange Commission including its Form 10-Q for the fiscal period ended October 26, 2008. Copies of reports filed with the SEC are posted on our website and are available from NVIDIA without charge. These forward-looking statements are not guarantees of future performance and speak only as of the date hereof, and, except as required by law, NVIDIA disclaims any obligation to update these forward-looking statements to reflect future events or circumstances.</p>

<p>Copyright (C) 2008 NVIDIA Corporation. All rights reserved. NVIDIA, PhysX, GeForce, Quadro, Tesla, and CUDA, are registered trademarks and/or trademarks of NVIDIA Corporation in the United States and other countries. All other company and/or product names may be trade names, trademarks and/or registered trademarks of the respective owners with which they are associated. Features, pricing, availability, and specifications are subject to change without notice.</p>

<p>What the Industry is Saying:</p>

<p>"NVIDIA 3D Vision has the potential to take PC gaming to a whole new level by bringing an unprecedented level of immersion to titles," said Christian Svensson, Corporate Officer/Vice-President - Strategic Planning & Business Development at Capcom. "When playing Dark Void with NVIDIA 3D Vision glasses, it really feels like you're inside the game as never before."</p>

<p>"Trying to describe how cool NVIDIA's 3D Vision is with words is like trying to draw Angelina Jolie on an Etch-A-Sketch. This is something you need to see with your own eyes to believe. 3D Vision literally takes gaming into the third dimension - something a lot of companies have aspired to over the years with little success," said Kelt Reeves, president of Falcon Northwest. "It took NVIDIA's full grasp of the gaming ecosystem, from developers to hardware and software solutions to produce the best consumer 3D experience I've ever seen. You've got to try this!"</p>

<p>"NVIDIA GeForce graphics processor technology is fundamentally changing the way our customers use their computers. By allowing real-time conversion of existing PC games into stereoscopic 3D and opening up new markets for 3D movies and pictures, 3D Vision will revolutionize how users interact with visual computing applications and is forming the foundation for a new consumer stereoscopic 3D ecosystem" said Chris Ward, president, of Lightspeed Design, Inc. "The DepthQ 3D HD Projector is the perfect application for a home theater room, allowing GeForce owners the ability to view games in stereoscopic 3D on Da-Lite projection screens up to nine feet wide."</p>

<p>"We're ecstatic to be able to bring the new NVIDIA 3D Vision technology to our customers," said Wallace Santos, president of Maingear Computers. "3D Vision really brings a whole new perspective to gamers, and is something that we will be recommending to anyone purchasing a MAINGEAR system equipped with NVIDIA graphics. With 3D Vision, NVIDIA continues to push the technology boundaries of what other companies can only dream about achieving."</p>

<p>"3D in the home is now an exciting reality, and Mitsubishi is proud to be partnered with NVIDIA to bring this solution to consumers," said Frank DeMartin, vice president, marketing, Mitsubishi Digital Electronics America. "By combining the Mitsubishi 60", 65" and 73" large screen home theater televisions, with more color than typical flat panel HDTVs, and the amazing visual computing power of GeForce graphics processors, consumers now have the premier platform for immersive home entertainment when they add 3D Vision to their computer."</p>

<p>"We believe 3D Vision will be a milestone for PC games and massively multiplayer online games in particular and enable players to enter a much more immersive game world", said Jerry Mao, vice president of object software, developer of Metal Knight Zero. "Compared with other MMOGs, Metal Knight Zero will benefit more from 3D Vision and provide players with a realistic game environment especially combined with NVIDIA PhysX technology which is already integrated into the game."</p>

<p>"We are excited to introduce the world's first pure 3D Vision-Ready 120 Hz desktop LCD 22" monitor for sale, delivering amazing 3D performance, picture quality, and response time" said R.A. Atanus, vice president of product marketing at Samsung Electronics. "The Samsung SyncMaster 2233RZ is the ultimate 3D widescreen gaming LCD monitor, and when paired with 3D Vision, gamers can instantly play hundreds of PC games into stereoscopic 3D."</p>

<p>"We are impressed by NVIDIA 3D Vision's amazing effects, which recreates visual effects like 3D theaters," said Chenglong Xu, vice technical director of TENCENT. "NVIDIA has enabled us to have this feature in our games, and we believe that players can enjoy a more realistic and innovative 3D experience powered by NVIDIA 3D Vision."</p>

<p>"There is no doubt that stereoscopic 3D is a very interesting step in the realm of 3D technology and it really does offer some impressive enhancements to PC games," said Ubisoft EMEA's Peter Hammer. "From the moment you put on NVIDIA 3D Vision glasses the visuals come to life!"</p>

<p>"NVIDIA GPUs and GeForce 3D Vision are helping drive new standards in dimensionalized viewing capabilities in desktop displays," said Jeff Volpe, vice president and general manager, ViewSonic North America. "NVIDIA 3D Vision and the ViewSonic FuHzion(TM) VX2265wm display will provide game enthusiasts with realistic depth, intense motion, rich graphics and detailed images that literally leap off the screen."<br />
Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20090108/CLTH926<br />
http://www.newscom.com/cgi-bin/prnh/20020613/NVDALOGO<br />
AP Archive: http://photoarchive.ap.org/<br />
AP PhotoExpress Network: PRN13<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: NVIDIA Corporation </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  8, 2009  6:45 AM</b>
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
			<?=getComments(1652)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1652)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/nvidia-announces-3d-visionthe-worlds-first-highdefinition-3d-stereo-solution-for-the-home.php" type="text/javascript" charset="utf-8"></script>
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