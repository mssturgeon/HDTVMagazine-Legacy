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
		AND e.entry_id = 3865";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3865 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3865 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3865";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/07/panasonic-unveils-the-worlds-first-3d-consumer-camcorder-complete-with-a-3d-conversion-lens.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3865";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Unveils the World\'s First 3D Consumer Camcorder, Complete With a 3D Conversion Lens" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Unveils the World\'s First 3D Consumer Camcorder, Complete With a 3D Conversion Lens" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Unveils the World\'s First 3D Consumer Camcorder, Complete With a 3D Conversion Lens" />
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
	<title>HDTV Magazine - Panasonic Unveils the World's First 3D Consumer Camcorder, Complete With a 3D Conversion Lens</title>
	<meta name="keywords" content="conversion lens, hdc sdt, panasonic hdc, panasonic sdt, lens attached, panasonic, sdt, lens, camcorder, images, recorded, conversion, consumer, video, hdc, shooting, full, recording, system, content, side, available, features, home, high" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/panasonic-hdc-sdt750.jpg&quot; alt=&quot;Panasonic HDC-SDT750&quot; height=&quot;115&quot; width=&quot;144&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;Panasonic today announced the launch of the Panasonic HDC-SDT750, the world's first consumer 3D camcorder, which includes a 3D conversion lens&lt;sup&gt;1&lt;/sup&gt; that enables the camcorder to shoot powerful and true-to-life 3D video content. The Panasonic SDT750 is a user-friendly consumer 3D camcorder that makes experiencing 3D at home easy and affordable&lt;sup&gt;2&lt;/sup&gt;. In addition to shooting 3D, the SDT750 can record full 1080p High Definition (HD) in AVCHD, when the 3D conversion lens is unattached, and includes powerful features such as a 3MOS system, a Leica Dicomar lens and a 12x optical zoom.

To shoot 3D video with the Panasonic HDC-SDT750 camcorder, the user needs to attach..." />
	<meta name="title" content="Panasonic Unveils the World's First 3D Consumer Camcorder, Complete With a 3D Conversion Lens" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Unveils the World's First 3D Consumer Camcorder, Complete With a 3D Conversion Lens" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/07/panasonic-unveils-the-worlds-first-3d-consumer-camcorder-complete-with-a-3d-conversion-lens.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/panasonic-hdc-sdt750.jpg&quot; alt=&quot;Panasonic HDC-SDT750&quot; height=&quot;115&quot; width=&quot;144&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;Panasonic today announced the launch of the Panasonic HDC-SDT750, the world's first consumer 3D camcorder, which includes a 3D conversion lens&lt;sup&gt;1&lt;/sup&gt; that enables the camcorder to shoot powerful and true-to-life 3D video content. The Panasonic SDT750 is a user-friendly consumer 3D camcorder that makes experiencing 3D at home easy and affordable&lt;sup&gt;2&lt;/sup&gt;. In addition to shooting 3D, the SDT750 can record full 1080p High Definition (HD) in AVCHD, when the 3D conversion lens is unattached, and includes powerful features such as a 3MOS system, a Leica Dicomar lens and a 12x optical zoom.

To shoot 3D video with the Panasonic HDC-SDT750 camcorder, the user needs to attach..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3865', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/07/panasonic-unveils-the-worlds-first-3d-consumer-camcorder-complete-with-a-3d-conversion-lens.php">Panasonic Unveils the World's First 3D Consumer Camcorder, Complete With a 3D Conversion Lens</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 27, 2010</b>
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
				<p class="prtitle">Panasonic Unveils the World's First 3D Consumer Camcorder, Complete With a 3D Conversion Lens<sup>1</sup></p>

<center><i>New Panasonic HDC-SDT750 Shoots 3D Video Ready to Play Back on 3D-Capable Televisions, Perfect for Creating a 3D Entertainment Ecosystem at Home</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/panasonic-hdc-sdt750.jpg" alt="Panasonic HDC-SDT750" height="115" width="144" class="keyimg"><strong>SECAUCUS, N.J., July 27 /PRNewswire-FirstCall/ -- </strong>Panasonic today announced the launch of the Panasonic HDC-SDT750, the world's first consumer 3D camcorder, which includes a 3D conversion lens<sup>1</sup> that enables the camcorder to shoot powerful and true-to-life 3D video content. The Panasonic SDT750 is a user-friendly consumer 3D camcorder that makes experiencing 3D at home easy and affordable<sup>2</sup>. In addition to shooting 3D, the SDT750 can record full 1080p High Definition (HD) in AVCHD, when the 3D conversion lens is unattached, and includes powerful features such as a 3MOS system, a Leica Dicomar lens and a 12x optical zoom.</p>

<p>"As a result of research conducted through Panasonic Hollywood Laboratory, Panasonic developed a professional 3D system camera and successfully brought high-quality Full HD 3D images to the home for viewing on Panasonic VIERA Full HD 3D televisions. But now, Panasonic has taken it one step further and developed the world's first consumer 3D camcorder, the HDC-SDT750 - creating a 3D ecosystem available for consumers in the home," said Chris Rice, Senior Product Manager, Imaging, Panasonic Consumer Electronics Company. "Incorporating Panasonic's professional broadcast technology and bringing it to an easy-to-use consumer model, the SDT750 makes high-quality 3D video content a reality in the home."</p>

<p>To shoot 3D video with the Panasonic HDC-SDT750 camcorder, the user needs to attach the 3D conversion lens that comes included, to record dynamic images. The specially-designed 3D conversion lens records right-eye and left-eye images simultaneously through its two lenses, thus resulting in video that can be viewed in 3D. The right and left images (each with 960 x 1080 pixels) that enter through the lenses are recorded using the side-by-side method.</p>

<p>The Panasonic SDT750 features a Time Lapse Recording feature, which plays a scene such as a sunset or a blooming flower at an accelerated speed, similar to a fast-forward. By setting the recording interval to 1 second, 10 seconds, 30 seconds, 1 minute or 2 minutes, the user can view an otherwise long recording in a reduced time period. For example, when a scene is recorded at the 1-second interval setting, a 10-minute sunset scene can be played back in approximately 10 seconds, making the slow change in the subject appear as if it were taking place in a very short time. This interval recording feature is also available when the 3D conversion lens is attached to the SDT750.</p>

<p>Panasonic offers a 5.1-channel audio recording sound system that uses five microphones, so when voices are recorded from the front, right, left and back are played on a 5.1-channel home cinema system, users are surrounded by clear, detailed sound that makes them feel as if they are right in the middle of the action. The Focus Microphone function, which has been made possible by five highly-directional microphones, picks up the sounds from sources in the area in front of the camcorder, regardless of whether the lens is focusing on a near or distant object. It also allows footage recorded in 3D to be enjoyed with lifelike and dynamic sounds.</p>

<p>The high-sensitivity 3MOS System has 7.59 million effective motion image pixels (2.53 megapixels x 3), so this advanced image sensor separates the light received through the lens into the three primary colors - red, green and blue - and processes each color independently. As a result, the Panasonic SDT750 produces beautiful images with rich color quality, detail and gradation. Adding to the quality, the SDT750 also features a large-diameter (46mm) F1.5<sup>3</sup> Leica Dicomar lens and Crystal Engine PRO, a high-speed processing unit - both components which contribute to the effectiveness of the camcorder's light gathering, increased sensitivity, and reduced noise when shooting, even in dim lighting.</p>

<p>Users can play back 3D videos recorded on the Panasonic HDC-SDT750 on 3D-capable televisions, such as Panasonic VIERA&reg; Full HD 3D televisions, including the TC-P50VT25, TC-P54VT25, TC-P58VT25, TC-P65VT25 and the TC-P50VT20 models. Playback using a VIERA TV is done by connecting the 3D camcorder to the television using an HDMI cable. In addition, it is also possible to play 3D images recorded on SD Memory Cards by using an AVCHD compatible player,<sup>4</sup> such as a Panasonic 3D Blu-ray Disc player - the DMP-BDT350 or DMP-BDT300 models are currently available. When watching 3D content recorded by the SDT750 on any of the Panasonic Full HD 3D VIERA televisions, users can view the true-to-life content and the VIERA television will automatically engage the side-by-side method for smooth viewing of 3D content - no change of settings necessary.</p>

<p>The SDT750 comes with HD Writer AE 2.6T PC editing software, which allows users to easily edit recorded 3D images, and save them onto PCs or Blu-ray/DVD discs. HD Writer AE 2.6T features a "Smart Wizard" that starts as soon as the SDT750 is connected to the USB port of the computer, which gives simple on-screen guidance. HD Writer AE 2.6T also enables easy uploading and sharing online without the need for any cumbersome processes, so that even an inexperienced user can post video clips on the web. When uploading 3D images from a PC onto the web, they must first be converted into 2D images.</p>

<p>Even without the 3D conversion lens attached, the Panasonic SDT750 is an innovative and high-performance Full HD camcorder that is equipped with a wide range of sophisticated functions, including the 3MOS System, which features improved noise reduction (NR) technologies, and a wide-variety of manual adjustments controlled by a manual ring for easy, creative shooting. The manual ring provides convenient, fingertip control of the focus, zoom, exposure (iris), shutter speed and white balance settings. Using the ring is extremely intuitive, comfortable, and user-friendly. Only the white balance setting is available when the 3D conversion lens is attached. The SDT750 can shoot 1,080/60p recording (Full-HD 1,920 x 1,080, 60 progressive recording) and produces rich expressive images, with no detail loss and flickering.</p>

<p>Panasonic's Intelligent Auto (iA) function makes the SDT750 extremely easy to use. When the 3D lens is not attached and iA is engaged, the SDT750 automatically selects the most suitable shooting mode with the press of a button. While shooting HD video, the Panasonic SDT750's iA function offers the following six functions: Face Recognition, the new HYBRID O.I.S., AF/AE Tracking, Intelligent Scene Selector, Face Detection and Intelligent Contrast Control. HYBRID O.I.S., a new feature, provides extremely accurate hand-shake correction with its four-axis blur detection, resulting in steady images while zooming or shooting on the move.</p>

<p>Other features of the Panasonic HDC-SDT750 include:<ul><li>Auto Power LCD automatically adjusts the brightness of the screen according to the shooting environment for comfortable use in a variety of different lighting situations.</li><li>Large 3.0" touch-screen LCD allows icons to be easily operated by touching them with a fingertip. On the LCD, recorded 3D images are displayed only as the 2D images that were recorded with the left lens.</li><li>Eco Mode automatically turns off the power when the camcorder is not operated for five minutes, reducing wasteful energy use and saving battery power.</li><li>Pre-Rec allows for the camcorder to continuously record three seconds of content into internal memory. Then, when the record button is pressed, the three seconds immediately prior will have been recorded.</li></ul></p>

<p>The Panasonic HDC-SDT750 will be available in October 2010 with a suggested retail price of $1,399.95. For more information on Panasonic camcorders, please visit: <a target="_blank" href="http://www.panasonic.com/dvc/">www.panasonic.com/dvc</a>.</p>

<p><sup>1</sup> As a consumer camcorder with 3D conversion lens for the AVCHD standard (as of July 27, 2010).</p>

<p><sup>2</sup> A TV that is capable of side-by-side method 3D playback, 3D Eyewear, and HDMI cable connection are required to play the recorded 3D images.</p>

<p><sup>3</sup> F3.2 when the 3D conversion lens is attached.</p>

<p><sup>4</sup> If the player/recorder is incompatible with 3D, the 3D mode must be set on the TV manually</p>

<p>* Design and specifications are subject to change without notice.</p>

<p><br />
<strong>About Panasonic Consumer Electronics Company</strong></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Panasonic Corporation (NYSE:PC) and the hub of Panasonic's U.S. marketing, sales, service and R&amp;D operations. Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. Company information for journalists is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>Source: Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 27, 2010  7:57 PM</b>
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
			<?=getComments(3865)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3865)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/07/panasonic-unveils-the-worlds-first-3d-consumer-camcorder-complete-with-a-3d-conversion-lens.php" type="text/javascript" charset="utf-8"></script>
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