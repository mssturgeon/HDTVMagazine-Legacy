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
		AND e.entry_id = 3580";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3580 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3580 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3580";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/03/acer-extends-leadership-in-3d-space-with-nvidia-3d-visionready-projectors.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3580";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors" />
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
	<title>HDTV Magazine - Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors</title>
	<meta name="keywords" content="new acer, acer projectors, new projectors, video projectors, vision ready, acer, projectors, video, projector, new, technology, vision, nvidia, customers, images, lamp, –, experience, home, ready, content, even, color, high, visuals" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/acer-h5360-3d-projector.jpg&quot; alt=&quot;Acer H5360 3D Projector&quot; height=&quot;47&quot; width=&quot;72&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Acer America today extends its leadership in delivering excellent products that take advantage of consumers' growing demand for 3D imagery with two new NVIDIA 3D Vision-Ready video projectors.

The three-dimensional experience is made possible by..." />
	<meta name="title" content="Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/03/acer-extends-leadership-in-3d-space-with-nvidia-3d-visionready-projectors.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/acer-h5360-3d-projector.jpg&quot; alt=&quot;Acer H5360 3D Projector&quot; height=&quot;47&quot; width=&quot;72&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Acer America today extends its leadership in delivering excellent products that take advantage of consumers' growing demand for 3D imagery with two new NVIDIA 3D Vision-Ready video projectors.

The three-dimensional experience is made possible by..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3580', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/03/acer-extends-leadership-in-3d-space-with-nvidia-3d-visionready-projectors.php">Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>March  2, 2010</b>
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
				<p class="prtitle"> Acer Extends Leadership in 3D Space With NVIDIA 3D Vision-Ready Projectors</p>

<center><i>Two new projectors ready to deliver stereoscopic 3D quality video and images; 720p HD-ready model ideal for immersive home theater experience</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/acer-h5360-3d-projector.jpg" alt="Acer H5360 3D Projector" height="95" width="144" class="keyimg"><strong>SAN JOSE, Calif.--(BUSINESS WIRE)--</strong>Acer America today extends its leadership in delivering excellent products that take advantage of consumers' growing demand for 3D imagery with two new NVIDIA 3D Vision-Ready video projectors.</p>

<p>The three-dimensional experience is made possible by a combination of the projectors' DLP projection capabilities, high refresh rates and NVIDIA 3D Vision technology. As a result, the flat surface of any wall can be transformed into a 3D screen.</p>

<p>"The new Acer video projectors provide incredibly compelling and realistic 3D video and images that make customers feel like they are part of the experience," said Irene Chan, senior product marketing manager for peripherals, Acer America. "With the Acer projectors, consumers can enjoy existing 2D content as if it were developed in 3D for a more immersive entertainment and learning experience – whether it's a fictional journey, a scientific exploration of the universe or a tour of ancient archaeological sites. Of course, customers will thoroughly enjoy the superior visuals projected from these new models even while watching traditional 2D content."</p>

<p><br />
<strong>Acer H5360 – Brilliant 3D Visuals for Home Theater Entertainment</strong></p>

<p>Home theater enthusiasts will enjoy video, game content, photos and more in an incredible new level of realism and video immersion using the new Acer H5360 projector. Delivering HD-ready 720p (1,280x720) resolution, the Acer H5360 boasts the latest technology for a truly unsurpassed video projection experience. The advanced lamp technology with illumination of up to 2500 ANSI lumens paired with the high 3200:1 contrast ratio also heightens the color and clarity of the images. Also – it displays images in native 16:9, so customers can view high-definition digital content without image distortion that arises from incompatible aspect ratios. The projector also has a 50-120Hz vertical refresh rate.</p>

<p>The Acer H5360 projector has an HDMI&trade; port that provides a seamless connection to the latest digital sources ensuring exceptional high-definition viewing and audio from Blu-ray Disc&trade; high definition technology as well as DVDs. So even when it's not paired with NVIDIA 3D Vision, customers get to enjoy incredibly realistic 2D images that are crisp and vibrant.</p>

<p>Along with its HDMI port, the projector has other ports that ensure the projector can connect to a wide range of video sources; it has three RCA jacks, component video, S-video mini DIN, 2.5mm audio mini-jack, 15-pin D-Sub for a PC analog signal.</p>

<p>The Acer H5360 projector is available now for U.S. customers at leading retailers for a Manufacturer's Suggested Retail Price (MSRP) of $699.00.</p>

<p><br />
<strong>Acer X1261 Projector – Excellent 3D Performance for Home, Work, School</strong></p>

<p>The Acer X1261 projector is ideal for home, business and classroom environments, providing rich visuals at an excellent value. Delivering bright colors and crisp images, the Acer X1261 projector features advanced lamp technology with illumination of up to 2500 ANSI lumens, a high 3700:1 contrast ratio and a vertical refresh rate of 50-120Hz. Its native XGA resolution and 4:3 aspect ratio are ready for presentations, photos, multimedia, and more. The projector can also be adjusted to a 16:9 aspect ratio for video content such as that from Blu-ray Disc&trade; and DVD. The Acer X1261 projector can connect to a variety of video input sources through its range of ports; it has composite video, component video, S-video mini DIN, and a stereo mini Jack.</p>

<p>Customers who want an excellent home video projector for 3D video, games, photos and multimedia yet need to stay within a certain price range will appreciate the performance and value delivered by the Acer X1261 projector. It is available now for U.S. customers at leading retailers for an MSRP of $579.00.</p>

<p><br />
<strong>State-of-the-Art Technology from NVIDIA&reg; 3D Vision&trade;</strong></p>

<p>Both new Acer projectors – the Acer H5360 and Acer X1261 – deliver an incredibly realistic 3D viewing experience when combined with NVIDIA&reg; 3D Vision&trade; technology, which transforms traditional 2D images into stunning 3D. NVIDIA &reg; 3D Vision&trade; is a combination of an NVIDIA 3D Vision compatible computer and graphics card, and 3D Vision Kit that includes wireless active-shutter glasses and advanced software that can transform hundreds of PC games into an exceptional 3D experience. The lightweight glasses, which can be worn over regular eyeglasses, can provide up to 40 hours of 3D entertainment on a single charge. For more information on NVIDIA 3D Vision technology, please visit <a target="_blank" href="http://www.nvidia.com/object/3D_Vision_Main.html/">http://www.nvidia.com/object/3D_Vision_Main.html</a> .(2)</p>

<p><br />
<strong>Projectors Have First-Rate Features for Improved Visual Experience at Home</strong></p>

<p>Both new projectors display images with more natural and balanced shades, and truly lifelike tones, thanks to Acer ColorBoost II+ featuring an optimized 3X color wheel design, a powerful image processor and an advanced lamp waveform to improve color performance. Further contributing to the enhanced images, the ColorSafe and DLP&reg; technology ensure picture integrity even with prolonged use, making the projectors virtually immune to color decay. The visuals are complemented by ample audio with an internal speaker on the new projectors with 2-watt output. External speakers can be connected.</p>

<p>Up to 4000 hours of lamp life on the two new Acer projectors reduces lamp replacement costs and ensures long-term dependable projector use.(3) The extended lifespan and consistent image quality lower the total cost of ownership and translate to big savings. Further savings are ensured by the innovative DLP chip, enabling a filter-free design for lower maintenance and operating costs.</p>

<p>Designed to be used in a variety of locations, the new projectors will deliver superior visuals at home, the office or in a classroom. Thanks to the innovative wall-color compensation capabilities, the projectors display clearly even on colored surfaces, so they do not need to be used with a screen. Acer projectors correct vertical distortions of up to 40°, so the audience sees a rectangular image rather than one with a wider top/bottom, even if the projector is placed above or below the screen. Plus, both models can be ceiling mounted and used with the included remote control. Customers can easily change the lamp module even when the projector is ceiling-mounted thanks to the Acer Top-loading Lamp design.</p>

<p><br />
<strong>Easy-to-Use and Eco-Friendly</strong></p>

<p>To improve ease-of-use, Acer projectors are equipped with Empowering Technology, a suite of tools designed to simplify access to simple setup, viewing and timer utilities, making customers more productive. The Acer eView Management capabilities let customers quickly and easily adjust the projector settings to suit any environment and any type of content. Eight factory or user-defined presets instantly configure color, brightness and contrast to deliver best-quality images.</p>

<p>The new Acer projectors employ an environmentally friendly management solution – Acer EcoProjection – which reduces standby power consumption by up to 50 percent after five minutes of being idle. Additionally, it delivers 20 percent additional power savings and automatically performs a safety shutdown if it does not receive input after a time interval that can be set by the customer. Also included in the Acer EcoProjection suite is Acer ePower Management, a tool that lets customers create customized power-saving configurations.</p>

<p>The new Acer H5360 and Acer X1261 video-projectors are backed with a one year warranty on the DLP chip and 90 day warranty on the lamp.</p>

<p><br />
<strong>About Acer</strong></p>

<p>Since its founding in 1976, Acer has achieved the goal of breaking the barriers between people and technology. Globally, Acer ranks No. 2 for total PCs and notebooks(1). A profitable and sustainable Channel Business Model is instrumental to the company's continuing growth, while its multi-brand approach effectively integrates Acer, Gateway, Packard Bell, and eMachines brands in worldwide markets. Acer strives to design environmentally friendly products and establish a green supply chain through collaboration with suppliers. Acer is proud to be a Worldwide Partner of the Olympic Movement in staging the Vancouver 2010 Olympic Winter and London 2012 Olympic Games. The Acer Group employs 7,000 people worldwide. Estimated revenue for 2009 is US$17.9 billion. See <a target="_blank" href="http://www.acer-group.com/">www.acer-group.com</a> for more information.</p>

<p>&copy; 2009 Acer Inc. All rights reserved. Acer and the Acer logo are registered trademarks of Acer Inc. Other trademarks, registered trademarks, and/or service marks, indicated or otherwise, are the property of their respective owners.</p>

<p>1 Source: Gartner data, FY 2009</p>

<p>2 Source: NVIDIA Corporation</p>

<p>3 Lamp-life depends on a variety of factors including brightness and can be maximized via Acer EcoProjection Technology, which allows the user to customize power-savings configuration.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>March  2, 2010 10:43 AM</b>
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
			<?=getComments(3580)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3580)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/03/acer-extends-leadership-in-3d-space-with-nvidia-3d-visionready-projectors.php" type="text/javascript" charset="utf-8"></script>
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