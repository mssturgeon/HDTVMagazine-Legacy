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
		AND e.entry_id = 3903";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3903 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3903 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3903";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/08/fujifilm-introduces-the-worlds-first-3d-digital-camera-that-can-capture-high-definition-3d-movies.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3903";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download FUJIFILM Introduces the World\'s First 3D Digital Camera That Can Capture High Definition 3D Movies" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="FUJIFILM Introduces the World\'s First 3D Digital Camera That Can Capture High Definition 3D Movies" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="FUJIFILM Introduces the World\'s First 3D Digital Camera That Can Capture High Definition 3D Movies" />
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
	<title>HDTV Magazine - FUJIFILM Introduces the World's First 3D Digital Camera That Can Capture High Definition 3D Movies</title>
	<meta name="keywords" content="finepix real, digital camera, left right, high quality, camera automatically, fujifilm, real, camera, finepix, digital, movies, high, photos, images, photo, capture, shooting, mode, quality, easy, right, imaging, still, hdmi, advanced" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/fujifilm-finepix-real-3d-w3.jpg&quot; alt=&quot;Fujifilm FinePix REAL 3D W3&quot; height=&quot;99&quot; width=&quot;144&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;VALHALLA, N.Y.--(BUSINESS WIRE)--FUJIFILM North America Corporation today announced a new addition to its FinePix REAL 3D system – the FinePix REAL 3D W3 digital camera. The FinePix REAL 3D W3 steps it up from its predecessor with the ability to shoot high-resolution 3D photos and movies in 3D HD at 720p¹, with the help of a new RP (Real Photo) Processor. It also sports a new Mini HDMI port² for easy playback on most 3D television systems and an Autostereoscopic 3D Widescreen 3.5&quot; LCD. All components make for stunning 3D still photos and movies that can be viewed and enjoyed through an easy connection between the FinePix REAL 3D W3 and their 3D TV³, or printed in stunning 3D quality.

The slim FinePix REAL 3D W3 digital camera..." />
	<meta name="title" content="FUJIFILM Introduces the World's First 3D Digital Camera That Can Capture High Definition 3D Movies" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="FUJIFILM Introduces the World's First 3D Digital Camera That Can Capture High Definition 3D Movies" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/08/fujifilm-introduces-the-worlds-first-3d-digital-camera-that-can-capture-high-definition-3d-movies.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/fujifilm-finepix-real-3d-w3.jpg&quot; alt=&quot;Fujifilm FinePix REAL 3D W3&quot; height=&quot;99&quot; width=&quot;144&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;VALHALLA, N.Y.--(BUSINESS WIRE)--FUJIFILM North America Corporation today announced a new addition to its FinePix REAL 3D system – the FinePix REAL 3D W3 digital camera. The FinePix REAL 3D W3 steps it up from its predecessor with the ability to shoot high-resolution 3D photos and movies in 3D HD at 720p¹, with the help of a new RP (Real Photo) Processor. It also sports a new Mini HDMI port² for easy playback on most 3D television systems and an Autostereoscopic 3D Widescreen 3.5&quot; LCD. All components make for stunning 3D still photos and movies that can be viewed and enjoyed through an easy connection between the FinePix REAL 3D W3 and their 3D TV³, or printed in stunning 3D quality.

The slim FinePix REAL 3D W3 digital camera..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3903', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/08/fujifilm-introduces-the-worlds-first-3d-digital-camera-that-can-capture-high-definition-3d-movies.php">FUJIFILM Introduces the World's First 3D Digital Camera That Can Capture High Definition 3D Movies</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 17, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=343&category=HD Camcorders & Cameras">HD Camcorders & Cameras</a></b>
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
				<p class="prtitle"> FUJIFILM Introduces the World's First 3D Digital Camera That Can Capture High Definition 3D Movies</p>

<center><i>FinePix REAL 3D W3 Digital Camera Captures Stills and Movies in HD in True 3D;</center></i><br />
with Easy Playback on 3D HD TV Systems<br />

<p><img src="http://www.hdtvmagazine.us/news/images/fujifilm-finepix-real-3d-w3.jpg" alt="Fujifilm FinePix REAL 3D W3" height="99" width="144" class="keyimg">VALHALLA, N.Y.--(BUSINESS WIRE)--FUJIFILM North America Corporation today announced a new addition to its FinePix REAL 3D system – the FinePix REAL 3D W3 digital camera. The FinePix REAL 3D W3 steps it up from its predecessor with the ability to shoot high-resolution 3D photos and movies in 3D HD at 720p¹, with the help of a new RP (Real Photo) Processor. It also sports a new Mini HDMI port² for easy playback on most 3D television systems and an Autostereoscopic 3D Widescreen 3.5" LCD. All components make for stunning 3D still photos and movies that can be viewed and enjoyed through an easy connection between the FinePix REAL 3D W3 and their 3D TV³, or printed in stunning 3D quality.</p>

<p>"Last year, Fujifilm introduced the first real, complete solution for 3D digital photography, once again showing our commitment to the evolution of imaging technology," said Go Miyazaki, division president, Imaging and Electronic Imaging Divisions, FUJIFILM North America Corporation. "With the new FinePix REAL 3D W3 digital camera, we have made 3D imaging more accessible to all consumers, enabling them to shoot in 3D HD and making it easy to view their photos and movies on most 3D TV systems."</p>

<p>The slim FinePix REAL 3D W3 digital camera with a black matte finish is ergonomically designed and measures 21.0 mm at its thinnest point and weighs 8.5 ounces with battery and memory card. The durable stainless steel construction ensures that the optical axis is balanced ever so precisely resulting in accurate 3D images. The W3 also has a horizontal lens cover with wave detail, which serves as the on/off switch.</p>

<p><br />
<strong>FinePix REAL 3D Technology</strong></p>

<p>Fujifilm's FinePix REAL 3D technology captures true 3D thanks to its use of twin 10 MegaPixel CCD sensors and dual FUJINON 3x optical zoom lenses (35-105mm) that are spaced 75 mm apart to create realistic images that are similar to how human eyes see them. The synchronized control of the twin CCD sensors releases the left and right shutters at the same time. This technology produces a synchronized image with a natural sense of depth, and allows easy capture of 3D movies and photos in HD, and also provides the beneficial unique 2D Advanced Shooting modes.</p>

<p><br />
<strong>3DHD Capture and RP (Real Photo) Processor 3D HD</strong></p>

<p>The 3D and 2D Auto functions let anyone take high-definition, high-resolution movies and photos effortlessly and audio is captured in stereo to preserve its original impact. Photo data captured by the dual lens CCD system is processed by the newly developed RP (Real Photo) Processor 3D HD which merges the left and right images into a single image. This processor is also the power behind 3D Auto – the function that lets even first-time users take stunning 3DHD quality photos.</p>

<p><br />
<strong>Autostereoscopic (3D) LCD</strong></p>

<p>The FinePix REAL 3D W3 also comes equipped with a 3.5" high resolution Autostereoscopic 3D Widescreen LCD with 1150K resolution that displays high contrast images and movies that can be viewed in 3D without the need of 3D glasses. This new lenticular system uses rows of convex lenses that create a binocular parallax effect to produce a realistic 3D image with less cross-talk and flicker. 3D images can be shown in High Luminosity Mode that both display images 1.5 times brighter, and color reproduced 1.8 times deeper4, allowing for clear, distinct and vivid images.</p>

<p><br />
<strong>3D HD Movies and Photo Playback</strong></p>

<p>Now it's easier than ever to enjoy high quality 3D HD movies and still photos in your home, because the FinePix REAL 3D W3 comes equipped with a Mini HDMI 1.4 port. This allows the user to quickly and easily connect the camera with an HDMI cable (not included) to enjoy high quality movies at 720p and HD photos captured at 1920x1080 pixels (or higher) on most 3D HD TV systems.</p>

<p><br />
<strong>Advanced 3D/2D Modes</strong></p>

<p>The FinePix REAL 3D W3 includes advanced 3D and 2D capture modes. With Individual Shutter 3D Shooting, advanced users will enjoy the ability to take two shots of the same subject from different positions, and then the camera will automatically merge and save the captured images as a single, enhanced 3D photo. Photos of distant subjects like mountains and skyscrapers look amazing in enhanced 3D, while close-up subjects like flowers come to life in natural 3D. With Interval 3D shooting, you can shoot hyper (wide distance between capture of left and right data) for capturing 3D effect in far away subjects.</p>

<p>The Advanced 2D Modes also allow for independent use of the dual CCD sensors and FUJINON lenses in the FinePix REAL 3D W3. Just as if shooting with two digital cameras, the user can choose different zoom ranges and color settings for each image, capturing both at the same time. With Tele/Wide Simultaneous Shooting, you can zoom in on your subject while also taking a wide-angle shot of the same scene. With Two-Color Simultaneous Shooting, at one press of the shutter, you can take photos of the same scene with a different color tonality. Set one lens system to vivid colors and the other to vintage black and white, or capture the scene in both standard and black and white. Dual-Sensitivity Simultaneous Shooting allows the user to capture high and standard sensitivity simultaneously. For example, you can take panned shots of a moving subject at the exact same instant with different degrees of background motion blur. In dark scenes, you can prioritize blur reduction for one shot, and image quality for the other.</p>

<p>The FinePix REAL 3D W3 also comes with a variety of additional functions:</p>

<p>    * <strong>Switch 2D/3D Button</strong>: Switch between capturing a subject in 2D or 3D instantly with the touch of a button and effortlessly capture movies and pictures for every kind of scene.<br />
    * <strong>One Touch Movie Mode</strong>: No need to fumble to find the movie mode, as the button is located right on the back of the camera, instantly changing from still to movie mode, making it convenient for taking HD quality movies in either 3D or 2D.<br />
    * <strong>Auto and Manual Parallax Control</strong>: The camera automatically adjusts parallax in 3D Auto mode for an optimal sense of depth. Manual mode is also possible by operating the Parallax Control lever on the top of the camera to easily fine-tune the sense of depth and alignment of the images. Both can be used to eliminate "ghosting" and increase or decrease the 3D effect.<br />
    * <strong>MyFinePix Studio 3D Movie and Photo Editor</strong>: Manually edit captured 3D images effortlessly with MyFinePix Studio software that's included with the camera. Separate a 3D still photo (MPO file) into left/right still image data, and recombine left/right still photos into 3D photos (MPO file). MyFinePix Studio also lets you freely cut and splice 3D movies.<br />
    * <strong>Manual Shooting Functions</strong>: Choose "M" (Manual) mode for the freedom to set shutter speed and aperture. Get instant control of depth of field in "A" (Aperture Priority) Mode. With the built-in "P" (Program) Mode, the camera automatically sets the shutter speed and aperture value.<br />
    * <strong>Scene Positions</strong>: Select from among the 13 Scene Positions (SP) that matches the subject and situation. The camera automatically optimizes camera settings for some of the most common shooting environments, making it easy to get great 3D photos in virtually any shooting situation.<br />
    * <strong>Super Intelligent Flash</strong>: This advanced flash system automatically adjusts flash output and timing to capture subjects and background in bright, natural detail over the full flash range. No more flash washout even when taking ultra close-up shots.</p>

<p><br />
<strong>3D Print Solution</strong></p>

<p>Getting high quality 3D prints is as easy as uploading your images to Fujifilm's SeeHere.com a photo printing, gifting, and sharing website, and having them delivered right to your door. Fujifilm has integrated high precision prints with lenticular technology, resulting in stunning high quality 3D images with tremendous depth and detail. 3D prints priced at $6.99 for a 5"x7" print.</p>

<p>The FinePix REAL 3D W3 digital camera will be available in early September 2010 at a retail price of $499.95.</p>

<p>*1 As a digital camera equipped with 2 CCDs, according to Fujifilm research in August 2010.</p>

<p>*2 HDMI 1.4-3D compliant, HDMI mini-terminal.</p>

<p>*3 HDMI cable with high speed type required.</p>

<p>*4 Compared to previous model.</p>

<p>NOTE: For FinePix REAL 3D W3 digital camera specs please go to: <a target="_blank" href="http://www.fujifilm.com/products/digital_cameras/">http://www.fujifilm.com/products/digital_cameras/</a></p>

<p><br />
<strong>About Fujifilm</strong></p>

<p>FUJIFILM North America Corporation, a marketing subsidiary of FUJIFILM Corporation, consists of four operating divisions and two subsidiary companies. The Imaging Division sells consumer and commercial photographic products and services including film, one-time-use cameras, online photo services and fulfillment, digital printing equipment and service. The Electronic Imaging Division markets consumer digital cameras. The Motion Picture Division provides motion picture film and the Graphic Systems Division supplies products and services to the printing industry. FUJIFILM Optical Devices U.S.A., Inc. is a provider of binoculars, and optical lenses for the closed circuit television, videography, cinematography, broadcast, and industrial markets. FUJIFILM Canada Inc. markets a range of Fujifilm products and services. For more information, please visit <a target="_blank" href="http://www.fujifilm.com/northamerica/">www.fujifilm.com/northamerica</a>, or go to <a target="_blank" href="http://www.twitter.com/fujifilmus/">www.twitter.com/fujifilmus</a> to follow Fujifilm on Twitter. To receive news and information direct from Fujifilm via RSS, subscribe at <a target="_blank" href="http://www.fujifilmusa.com/rss/">www.fujifilmusa.com/rss</a>.</p>

<p>FUJIFILM Holdings Corporation, Tokyo, Japan, brings continuous innovation and leading-edge products to a broad spectrum of industries, including electronic imaging, digital printing equipment, medical systems, life sciences, graphic arts, flat panel display materials, and office products, based on a vast portfolio of digital, optical, fine chemical and thin film coating technologies. The company was among the top 20 companies around the world granted U.S. patents in 2009, and in the year ended March 31, 2010, had global revenues of $23.5 billion*. Fujifilm is committed to environmental stewardship and good corporate citizenship. For more information, please visit <a target="_blank" href="http://www.fujifilmholdings.com/">www.fujifilmholdings.com</a>.</p>

<p>* At an exchange rate of 93 yen to the dollar.</p>

<p>All product and company names herein may be trademarks of their registered owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 17, 2010  7:08 PM</b>
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
			<?=getComments(3903)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3903)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/08/fujifilm-introduces-the-worlds-first-3d-digital-camera-that-can-capture-high-definition-3d-movies.php" type="text/javascript" charset="utf-8"></script>
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