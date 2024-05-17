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
		AND e.entry_id = 3830";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3830 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3830 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3830";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/07/sony-elevates-the-standard-for-3d-home-theater-with-new-es-av-receivers-and-bluray-3d-player.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3830";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player" />
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
	<title>HDTV Magazine - Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player</title>
	<meta name="keywords" content="iphone ipod, multi room, ipod touch, blu ray, audio video, control, audio, sony, video, hdmi, str, available, ipod, via, channel, remote, iphone, home, room, app, blu, ray, network, second, touch" />
	<meta name="description" content="Driving the innovation of 3D, Sony today took another step to strengthen its industry leading 3D home product line with new Elevated Standard (ES) AV receivers including the STR-DA5600ES, STRA-DA4600ES, and STR-DA3600ES and a Blu-ray 3D(TM) player, the BDP-S1700ES.

In addition to Sony's latest 3D capable BRAVIA&amp;reg; HDTVs..." />
	<meta name="title" content="Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/07/sony-elevates-the-standard-for-3d-home-theater-with-new-es-av-receivers-and-bluray-3d-player.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Driving the innovation of 3D, Sony today took another step to strengthen its industry leading 3D home product line with new Elevated Standard (ES) AV receivers including the STR-DA5600ES, STRA-DA4600ES, and STR-DA3600ES and a Blu-ray 3D(TM) player, the BDP-S1700ES.

In addition to Sony's latest 3D capable BRAVIA&amp;reg; HDTVs..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3830', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/07/sony-elevates-the-standard-for-3d-home-theater-with-new-es-av-receivers-and-bluray-3d-player.php">Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July  6, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Sony Elevates the Standard for 3D Home Theater With New ES AV Receivers and Blu-ray 3D Player</p>

<center><i>Company Supports Specialty AV Channel With Focused Distribution Strategy</center></i><br />
<br />

<p><strong>SAN DIEGO, July 6 /PRNewswire/ -- </strong>Driving the innovation of 3D, Sony today took another step to strengthen its industry leading 3D home product line with new Elevated Standard (ES) AV receivers including the STR-DA5600ES, STRA-DA4600ES, and STR-DA3600ES and a Blu-ray 3D(TM) player, the BDP-S1700ES.</p>

<p>In addition to Sony's latest 3D capable BRAVIA&reg; HDTVs, the new ES components represent the best audio and video quality Sony has to offer. Designed with custom installers in mind, ES models integrate seamlessly with multi-room, third-party control systems.</p>

<p>"By focusing on seamless integration with custom home theater control partners, Sony is working to deliver products that support the custom and specialty retail business like no other manufacturer," said Brian Siegel, vice president of Sony's home audio and video business. "Because Sony is involved in every stage of the 3D ecosystem, specialty dealers and installers can rest assured that our AV components leverage that deep expertise to deliver the most technically advanced experience possible."</p>

<p>Focusing on the needs of custom home theater installers, Sony's ES line represents an enthusiast-level AV experience featuring some of the company's most advanced technology. The value of the products is best demonstrated by dealers and integrators qualified to engage an advanced consumer audience. To that end, the company is focusing its distribution strategy to include only specialty AV retailers and custom installers. ES products will no longer be offered for sale online or through telesales.</p>

<p><br />
<strong>AV Receivers with Custom Control</strong></p>

<p>Sony's 2010 ES AV receivers feature IR input jacks and two-way serial control. They also offer control over IP which allows installers to easily integrate the receivers into the leading home automation systems including Control4&reg;, Crestron, AMX&reg;, Savant, Ultimate Remote Control, RTI, and others. Sony has worked closely with leading control companies and is a Control4 Certified Partner, an AMX Device Discovery Partner, Crestron Integration Partner, and a Savant Excellence in Audio and Video Partner.</p>

<p>The new models offer 3D pass-through and flexible multi-room features. They can connect to a home broadband network through an Ethernet port offering easy access to digital photos, music and videos from Digital Life Network Alliance (DLNA&reg;) sources. They also function as an Ethernet hub featuring four ports that connect other network devices.</p>

<p>Additionally, the Ethernet connection provides access to both Shoutcast&reg; Internet Radio and Rhapsody&reg; Music Service, and offers easy access to firmware upgrades via the Internet. The STR-DA5600ES model also functions as a DLNA Live Audio Server that can stream music to other DLNA clients.</p>

<p>Users with an iPhone&reg; or iPod&reg; touch device can control the models using a free app that transforms your mobile device into a full ES receiver remote. The app features zone control, full GUI menu, and Sony's Quick Click remote function which allows users to control source components (such as Blu-Ray player, set-top box) in the main home theater room to be controlled from a second zone.</p>

<p>The STR-DA5600ES and the STR-DA4600ES offer the ability to distribute audio and video to multiple rooms via the second HDMI output and video to a second zone via CAT5 output. They can both also up-scale all sources to 1080p in the main zone and 1080i in a second zone via the Faroudja chip&reg; ensuring a consistent, sharp picture. The connected video zones feature a high-grade, icon-driven menu system that allows for simple device and content navigation.</p>

<p>All three models feature Digital Cinema Auto Calibration (DCAC) with Automatic Phase Matching (APM). Without changing the front reference speakers, APM corrects for phase differentiation between front, center, and surround speakers, ensuring an ideal sound stage regardless of speaker type. The STR-DA5600ES also offers DCAC EX which includes speaker relocation, not only adjusting for the distance but also the angle of the listening position to create an optimal listening environment.</p>

<p>The receivers also feature Sony's innovative new HD Digital Cinema Sound&reg; that was developed together with Sony Pictures Entertainment. Incorporating high-mounted front speaker placement, HD DCS was designed to transform and replicate both the sound quality and acoustics of a movie theater in the home environment creating an unmatched home theater experience.</p>

<p>All of the models support 1080/24p video signals and x.v.Color(TM) and Deep Color(TM) video codecs, as well as all advanced audio formats (Dolby&reg; Digital Plus, Dolby&reg; TrueHD, dts&reg; Master HD, dts&reg; ES, Dolby&reg; Digital EX, Dolby&reg; Pro Logic IIx and 8 channel Linear PCM). They also feature audio return channel allowing network content originating from the television to be played through the receiver without additional cables.</p>

<p>The models feature Sony's Digital Media Port(TM) for simple connection to an iPod and other digital music players, and are both XM&reg; and Sirius&reg; ready.</p>

<p>The 7.1 channel network multi-room STR-DA5600ES AV receiver features a 130-watt power amplifier x7 (8 ohms, 20 Hz - 20 kHz, .09 percent THD) With six HDMI 1.4 inputs (five rear, one front) and two switched or simultaneous HDMI outputs, the model will be available this September for about $2,000.</p>

<p>The 120-watt x7 (8 ohms, 20 Hz - 20 kHz, .09 percent THD) STR-DA4600ES features four HDMI outputs and two switched or simultaneous HDMI outputs. It will be available this August for about $1,500.</p>

<p>The 100-watt x7 (8 ohms, 20 Hz - 20 kHz, .09 percent THD) STR-DA3500ES features four HDMI output and will be available in August for about $1,100.</p>

<p>The company also added the 110-watt STR-DN2010 network 3D capable model to its receiver line up. The model will be available in August for about $800.</p>

<p><br />
<strong>ES Blu-ray 3D Player with iPhone/iPod Control</strong></p>

<p>Available next month for about $400, Sony's BDP-S1700ES Blu-ray 3D player features an IR input on the back panel or easy integration with control systems. It also offers built in Wi-Fi (802.11n) for easy access to Sony's BRAVIA Internet Video platform and Sony's new premium video service, Qriocity.</p>

<p>Also, users with an iPhone or iPod touch device can control the player using the free "BD Remote" app available from the Apple App store. The app, which will also be available soon for Android(TM) devices, allows the hand-held to function as a remote control and displays Blu-ray Disc details such as jacket artwork, actor, and production information as well as search for related video clips online.</p>

<p><br />
<strong>ES Quality</strong></p>

<p>All Sony ES products are supported by a five-year limited manufacturer warranty, a ninety-day advanced exchange program (2-3 day expedited replacement unit), and a dedicated installer support line to San Diego-based CEDIA trained product experts.</p>

<p>The models will be available through authorized Sony ES dealers and custom installers across the country. For further information on the products, please visit <a target="_blank" href="http://www.sony.com/es/">www.sony.com/es</a>.</p>

<p><br />
<strong>  Model Specifications:</strong></p>

<p>  STR-DA5600ES 7.1 Channel Network Multi-room AV Receiver<br />
  Available in September for about $2,000<br />
  --  130 watt  x7 @ 8-ohms, 1kHz, .09% THD<br />
  --  DCAC EX Speaker Auto-Calibration with Speaker Relocation (distance and<br />
      degree) and Automatic Phase Matching (APM)<br />
  --  3D pass-through<br />
  --  Integrated four port Ethernet switch<br />
  --  Second zone CAT5e output<br />
  --  DLNA Client and Live Audio Server<br />
  --  iPhone/iPod Touch remote control application<br />
  --  Quick Click feature for multi-room source control (with iPhone/iPod<br />
      touch app)<br />
  --  On-screen graphical user interface with overlay (main and second<br />
      zones)<br />
  --  H.A.T.S. clock synchronization for HDMI and DSD<br />
  --  Standby pass-through of audio and video via HDMI<br />
  --  Control system integration via control over IP, RS232 or IR<br />
  --  PC set up manager<br />
  --  Audio Return Channel for TV audio over a single HDMI cable<br />
  --  Shoutcast&reg; Internet Radio and Rhapsody&reg; Music Service<br />
  --  Dolby&reg; TrueHD and dts&reg; HD Enhanced audio codecs</p>

<p><br />
  STR-DA4600ES 7.1 Channel Network Multi-room AV Receiver<br />
  Available in August for about $1,500<br />
  --  120 watt  x7 @ 8-ohms, 1kHz, .09% THD<br />
  --  3D pass-through<br />
  --  Integrated four port Ethernet switch<br />
  --  Second zone CAT5e output<br />
  --  DLNA Client<br />
  --  iPhone/iPod Touch remote control application<br />
  --  Quick Click feature for multi-room source control (with iPhone/iPod<br />
      touch app)<br />
  --  On-screen graphical user interface with overlay (main and second<br />
      zones)<br />
  --  Standby pass-through of audio and video via HDMI<br />
  --  Control system integration via control over IP, RS232 or IR<br />
  --  PC set up manager<br />
  --  Audio Return Channel for TV audio over a single HDMI cable<br />
  --  Shoutcast&reg; Internet Radio and Rhapsody&reg; Music Service<br />
  --  Dolby TrueHD and dts HD Enhanced audio codecs</p>

<p><br />
  STR-DA3600ES 7.1 Channel Network Multi-room AV Receiver<br />
  Available in August for about $1,100<br />
  --  100 watt  x7  @ 8-ohms, 1kHz, .09% THD<br />
  --  3D pass-through<br />
  --  Integrated four port Ethernet switch<br />
  --  DLNA Client<br />
  --  iPhone/iPod Touch remote control application<br />
  --  Quick Click feature for multi-room source control (with iPhone/iPod<br />
      touch app)<br />
  --  On-screen graphical user interface with overlay (main and second<br />
      zones)<br />
  --  Standby pass-through of audio and video via HDMI connected devices<br />
  --  Control system integration via control over IP, RS232 or IR<br />
  --  PC set up manager<br />
  --  Audio Return Channel for TV audio over a single HDMI cable<br />
  --  Shoutcast&reg; Internet Radio and Rhapsody&reg; Music Service<br />
  --  Dolby TrueHD and dts HD Enhanced audio codecs</p>

<p><br />
  STR-DN2010 7.1 Channel Network AV Receiver<br />
  Available in August for about $800<br />
  --  110 watt  x7  @ 8-ohms, 1kHz, 1% THD<br />
  --  3D pass-through<br />
  --  Integrated four port Ethernet switch<br />
  --  DLNA Client<br />
  --  iPhone/iPod touch remote control app<br />
  --  On-screen graphical user interface<br />
  --  Wireless second zone audio (S-AIR(TM) multi-room capability)<br />
  --  Standby pass-through of audio and video via HDMI connected devices<br />
  --  Audio Return Channel for TV audio over a single HDMI cable<br />
  --  Shoutcast&reg; Internet Radio and Rhapsody&reg; Music Service<br />
  --  Dolby TrueHD and dts HD Enhanced audio codecs</p>

<p><br />
  BDP-S1700ES Blu-ray 3D Player<br />
  Available in August for $400<br />
  --  Full HD 1080p single-disc Blu-ray Disc, DVD, CD, SA-CD player<br />
  --  Blu-ray 3D capable<br />
  --  BRAVIA Internet Video and Sony's Qriocity<br />
  --  IR input jack on rear panel for easy control system integration<br />
  --  Built-in Wi-Fi  Wireless (802.11n) with Wi-Fi Protected Setup (WPS)<br />
  --  Entertainment Database Browser with Gracenote(TM) technology<br />
  --  BD Remote (iPhone/iPod touch and Android remote control - free app.)<br />
  --  Photo/music/video playback via USB and DLNA<br />
  --  DVD upscaling to 1080p with Precision Cinema HD Upscaling<br />
  --  Dolby TrueHD and dts-HD Master Audio(TM) decoding<br />
  --  Built-in 1GB Memory</p>

<p>Source: Sony Electronics</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July  6, 2010  7:45 PM</b>
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
			<?=getComments(3830)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3830)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/07/sony-elevates-the-standard-for-3d-home-theater-with-new-es-av-receivers-and-bluray-3d-player.php" type="text/javascript" charset="utf-8"></script>
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