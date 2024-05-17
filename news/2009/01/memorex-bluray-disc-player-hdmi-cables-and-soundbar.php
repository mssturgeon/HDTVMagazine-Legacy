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
		AND e.entry_id = 1608";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1608 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1608 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1608";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/memorex-bluray-disc-player-hdmi-cables-and-soundbar.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1608";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar" />
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
	<title>HDTV Magazine - Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar</title>
	<meta name="keywords" content="blu ray, audio video, ray disc, home theatre, retailers starting, memorex, audio, video, cables, home, digital, dvd, cable, experience, high, player, hdmi, blu, ray, soundbar, sound, theatre, disc, retailers, ipod" />
	<meta name="description" content="Recreate the cinema movie experience right in your home with easy-to-use and affordable Memorex home theatre products. Memorex, a portfolio brand of Imation Corp. (NYSE: IMN), today introduced the powerful SimpleSurround HDMI DVD SoundBar with iPod&amp;reg; dock, the feature-packed MVBD-2520 Blu-ray Disc player, and high performance audio/video cables including High Definition Multimedia Interface (HDMI) cables. The latest in the Memorex line of home theatre components and accessories represent the perfect blend of quality and value, and offer consumers the exciting opportunity to experience high-definition (HD) movies in their homes without breaking the bank.

The following home theatre components and accessories will be on display..." />
	<meta name="title" content="Memorex&amp;reg; Blu-ray Disc Player, HDMI Cables and Soundbar" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Memorex&amp;reg; Blu-ray Disc Player, HDMI Cables and Soundbar" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/memorex-bluray-disc-player-hdmi-cables-and-soundbar.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Recreate the cinema movie experience right in your home with easy-to-use and affordable Memorex home theatre products. Memorex, a portfolio brand of Imation Corp. (NYSE: IMN), today introduced the powerful SimpleSurround HDMI DVD SoundBar with iPod&amp;reg; dock, the feature-packed MVBD-2520 Blu-ray Disc player, and high performance audio/video cables including High Definition Multimedia Interface (HDMI) cables. The latest in the Memorex line of home theatre components and accessories represent the perfect blend of quality and value, and offer consumers the exciting opportunity to experience high-definition (HD) movies in their homes without breaking the bank.

The following home theatre components and accessories will be on display..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1608', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/memorex-bluray-disc-player-hdmi-cables-and-soundbar.php">Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Memorex&reg; Home Theatre Electronics, Components Bring the High-Definition Movie Experience Home at Affordable Prices</p>

<center><i>Powerful soundbar, feature-packed Blu-ray Disc player, and top-performing HDMI cables manufactured to meet highest standards, yet priced for outstanding value</i></center><br />
<br />

<p><br />
<B>LAS VEGAS--(BUSINESS WIRE)</B>--Recreate the cinema movie experience right in your home with easy-to-use and affordable Memorex home theatre products. Memorex, a portfolio brand of Imation Corp. (NYSE: IMN), today introduced the powerful SimpleSurround HDMI DVD SoundBar with iPod&reg; dock, the feature-packed MVBD-2520 Blu-ray Disc player, and high performance audio/video cables including High Definition Multimedia Interface (HDMI) cables. The latest in the Memorex line of home theatre components and accessories represent the perfect blend of quality and value, and offer consumers the exciting opportunity to experience high-definition (HD) movies in their homes without breaking the bank.</p>

<p>"Home theatre systems are natural focal points for family gatherings, but until recently, assembling a high-definition entertainment system has been prohibitively expensive," said Jessica Walton, Memorex global brand director, Imation Corp. "Memorex's latest line of audio and video electronics and cables represent a new paradigm shift in the affordability of home entertainment systems. With our products, assembling friends and family together to share and experience the magnificent, vivid images and rich surround sounds of cinematic movies in the home is finally within reach of the average consumer."</p>

<p>The following home theatre components and accessories will be on display at the Pepcom&reg; Digital Experience event from 7 to 10 p.m. tonight at The Mirage.</p>

<p><br />
<B>Memorex SimpleSurround SoundBar with iPod Dock</B></p>

<p>You'll leave your friends and family speechless when they experience the dramatic and powerful sounds playing from your Memorex SimpleSurround HDMI DVD SoundBar, a beautifully-designed and sleek all-in-one solution for your home audio/video needs, complete with a slot-loading HDMI DVD player and iPod dock at an outstanding value of less than $200. The SimpleSurround SoundBar is powerful enough to deliver the immersive sounds of the movie theatre right into your living or home entertainment room, and is simple to install compared to surround sound systems comprised of several speaker units and subwoofers that often require professional installations.</p>

<p>Perfect for the living room or bedroom, the SimpleSurround Soundbar is compact and sleek, reducing the visual clutter of having multiple speakers and an iPod dock as part of your home theatre set-up. Featuring true versatility, this Memorex HDMI DVD Soundbar with iPod dock not only lets you charge your iPod and play music directly from your MP3 player, but also offers FM radio with station presets, DVD playback, and a USB/SD/MMC card slot, a perfect feature for memory keepers looking to share digital photos and home videos on television displays.</p>

<p>Housed in a sleek black, compact design, the 2.1 channel SoundBar incorporates innovative audio technology from SRS Labs that creates a drastically fuller and more natural sound experience than other 2.1 channel sound systems. Featured SRS Labs technology includes Dialog Clarity, which makes speech and center dialogue more clear and crisp during playback; TruBass which enhances bass performance using psychoacoustic techniques that selectively boost a series of low bass harmonics to restore the perception of low frequency tones; and TruSurround XT, a sophisticated algorithm that enables a surround sound multi-channel immersive audio experience over two channels.</p>

<p>The SimpleSurround Soundbar is "Made for iPod" certified and comes preloaded with five relaxing and soothing ambient sounds.</p>

<p>Technical features include 2.1 channel stereo sound with integrated amplifier and built-in SRS TruBass, Dialog Clarity, TruSurround XT technologies. Playable media includes DVD, DVD-R, DVD-R/RW, DV+R/RW, CD, CD-R/RW, SVCD/VCD, WMV and MP3. Connectivity includes HDMI output, component/S-video composite output, USB/SD/MMC Card slot, and auxiliary input to connect other digital audio devices. It also features bass and treble control, reverse polarity LCD display, and full function remote control.</p>

<p>The Memorex SoundBar is available in black and will be shipping to retailers starting in April for an SRP of $199.99.</p>

<p><br />
<B>Memorex MVBD-2520 Blu-ray Disc Player (Profile 2.0 /BD-Live)</B></p>

<p>Experience the richer colors and finer details of HD images, and enjoy the crisp, vibrant multi-channel audio content of the Blu-ray Disc format with this sleek, elegant Blu-ray player. In addition to Blu-ray Disc playback, the Memorex MVBD-2520 features advanced offerings like Profile 2.0 or BD-Live for internet access via an Ethernet port. This dramatically enhances a movie viewer's experience with downloadable extra features, online bonus content and the ability to download firmware updates. In addition, the Memorex MVBD 2520 has the capability to convert standard-definition material to high-definition quality at 480p, 720p, 1080i and 1080p display resolutions. The Memorex MVBD 2520 Blu-ray Disc player is packed with advanced features and functionality, yet is offered at less than $200, representing an outstanding value.</p>

<p>Technical features include video resolution of full HD 1080p and video frame rates of 24p and 60p. Audio playback features include Dolby Digital, Dolby TrueHD, Dolby Digital Plus, DTS, DTS-HD High Resolution Audio decoding and bit stream output, as well as Master Audio bit stream output. The player is compatible with a wide range of audio and video formats including Blu-ray Disc (AV format), DVD-Video, MPEG-2, MPEG-4 AVC (H.264), VC-1, VCD, SVCD, JPEG, BD-ROM,DVD-ROM, DVD-R/-RW, DVD+R/+RW, CD-ROM, and CD-R/-RW, as well as WMA and MP3 files. This player also supports SD Card and USB 2.0.</p>

<p>The Memorex MVBD-2520 Blu-ray Disc Player will be shipping to retailers starting in early summer 2009 for suggested retail price (SRP) of $199.99.</p>

<p><br />
<B>Audio/Video Cables</B></p>

<p>Memorex enters the audio and video (A/V) cable market with six offerings made of the highest quality materials. Memorex A/V cables have been designed and engineered to ensure they consistently transmit clean error-free signals for the best imaging and sound quality. Memorex cables feature state-of-the-art manufacturing techniques and high performance materials including superior cable shielding, a reliable soldering process and armor-locked technology to improve strain relief to protect and preserve audio and video signals. Affordably priced, Memorex cables are ideal for the value-conscious movie enthusiast.</p>

<p>Leading the Memorex cable lineup is its High-Definition Audio Video Cable. Consumers can experience the sharpest picture, deepest color, crystal-clear sounds and smoothest video possible with new HDMI-Certified cables from Memorex. Memorex HDMI cables have been manufactured under very precise standards using the highest quality materials to transport uncompressed audio and video signals from HD sources like Blu-ray players, HD digital satellite receivers, and PlayStation&reg; 3 consoles at ultra-fast speeds of up to 20GB per second via a single cable. Memorex HDMI cables have been constructed and designed for optimum signal transfer and high performance featuring 24k gold plated connectors and oxygen-free copper coating. These cables have undergone rigorous testing to ensure they can deliver the most crystal clear signals possible. They are offered in 4 feet, 8 feet and 16 feet to accommodate any home theatre configuration, and will begin shipping to retailers starting in spring 2009 for an SRP of $34.99, $39.99 and $59.99, respectively.</p>

<p>Other cables from the Memorex family include:</p>

<p>    * Component Video Cables for clear, vibrant images and clearer sound from video sources including DVD players, satellite receivers, cable boxes and digital video recorders. The 8-foot Memorex Component Video Cable will begin shipping to retailers starting in spring 2009 for an SRP of $34.99.<br />
    * Composite Video Coax Digital Cables are designed to minimize signal distortion. This is the perfect conduit to carry low frequency analog video signals of up to 4.2MHz, and digital audio signals up to 24.576MHz from your camcorder, VCR, DVD player and cable/satellite box to your television display or monitor. The 8-foot Memorex Composite Video Coax Digital Cable will begin shipping to retailers starting in spring 2009 for an SRP of $29.99.<br />
    * Digital Optical Audio Cables offer smoother, more detailed and undistorted sounds from audio/video components. These digital optical fiber cables address the corruption of timing or "jitter" which causes sound distortion. Offered in 8-feet, the Memorex Digital Optical Audio Cable will begin shipping to retailers starting in spring 2009 for an SRP of $35.99.<br />
    * Stereo Audio Cables deliver clear, high-quality sound from audio components. High-quality metals such as long grain copper conductors enable accurate, low distortion analog audio signal transmission. The 8-foot Memorex Stereo Audio Cable will begin shipping to retailers starting in spring 2009 for an SRP of $29.99.<br />
    * Subwoofer Cables allow you to experience floor-shaking bass and the ultimate realism in sound from your home theatre audio system. The cable uses two identical solid 1.25 percent silver conductors for positive and negative charges, eliminating any strand-interaction distortion. The cables also feature silver-plated copper (SPC) conductors which create an "artificial edge" to enhance the articulation and intelligibility of subwoofer or low frequency sounds. Memorex Subwoofer Cables come with chassis ground connections to prevent hum, and are offered in 12 feet. They will begin shipping to retailers starting in spring 2009 for an SRP of $44.99.</p>

<p><br />
<B>About Memorex</B></p>

<p>Memorex is one of the most trusted and recognized consumer brands in modern marketing history. A portfolio brand of Imation Corp. (NYSE: IMN), Memorex is the North American market share leader in optical media and media accessories at retail and one of the best known names in the consumer electronics industry. Memorex reaches into millions of homes with home audio and video products, MP3 players, digital picture frames, iPod&reg; accessories, and LCD televisions that are stylish and simple in form and function. For more information about Memorex, please visit www.memorex.com.</p>

<p>Memorex, the Memorex logo, and Imation are trademarks of Imation Corp. and its subsidiaries. All other trademarks are property of their respective owners. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  7:31 AM</b>
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
			<?=getComments(1608)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1608)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/memorex-bluray-disc-player-hdmi-cables-and-soundbar.php" type="text/javascript" charset="utf-8"></script>
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