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
		AND e.entry_id = 4143";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4143 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4143 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4143";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/new-sony-bluray-players-and-home-theater-systems-deliver-connected-entertainment-experience.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4143";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience" />
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
	<title>HDTV Magazine - New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience</title>
	<meta name="keywords" content="blu ray, ray disc, home theater, internet video, iphone ipod, blu, ray, video, disc, sony, home, playback, new, music, theater, internet, audio, model, remote, bdp, access, usb, bravia, available, entertainment" />
	<meta name="description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- (CES Booth #14200) -- &lt;/strong&gt;Sony today introduced its new Blu-ray Disc&amp;trade; line featuring access to online entertainment, Wi-Fi&amp;reg; Internet connectivity, Blu-ray 3D&amp;trade; playback, and compatibility with Sony's new HomeShare&amp;trade; Network speakers for wireless multi-room audio streaming throughout the home. 

The full HD 1080p new Blu-ray Disc&amp;trade; line includes..." />
	<meta name="title" content="New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/new-sony-bluray-players-and-home-theater-systems-deliver-connected-entertainment-experience.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- (CES Booth #14200) -- &lt;/strong&gt;Sony today introduced its new Blu-ray Disc&amp;trade; line featuring access to online entertainment, Wi-Fi&amp;reg; Internet connectivity, Blu-ray 3D&amp;trade; playback, and compatibility with Sony's new HomeShare&amp;trade; Network speakers for wireless multi-room audio streaming throughout the home. 

The full HD 1080p new Blu-ray Disc&amp;trade; line includes..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4143', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/new-sony-bluray-players-and-home-theater-systems-deliver-connected-entertainment-experience.php">New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>, <b><a href="/category.php?id=342&category=Wireless HDMI/HDTV">Wireless HDMI/HDTV</a></b>
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
				<p class="prtitle">New Sony Blu-ray Players and Home Theater Systems Deliver Connected Entertainment Experience</p>

<center><i>Models Offer Advanced Features Including Internet Video Streaming, Wi-Fi, Wireless Multi-room Audio, and Blu-ray 3D Compatibility</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- (CES Booth #14200) -- </strong>Sony today introduced its new Blu-ray Disc&trade; line featuring access to online entertainment, Wi-Fi&reg; Internet connectivity, Blu-ray 3D&trade; playback, and compatibility with Sony's new HomeShare&trade; Network speakers for wireless multi-room audio streaming throughout the home. </p>

<p>The full HD 1080p new Blu-ray Disc&trade; line includes four stand alone models (BDP-S780, BDP-S580, BDP-S480, and BDP-S380) and three integrated home theater systems (BDV-E780W, BDV-E580, and BDV-E280), as well as a 5.1 channel home theater audio system (HT-SS380).  All of the models play back DVD, CD and SACD discs in addition to Blu-ray Discs.</p>

<p>The company also introduced its first portable Blu-ray Disc player, BDP-SX1000, which features a 10.1-inch WSVGA screen.</p>

<p>When connected to a broadband Internet network, all models offer access to over 40 free and premium movies, video, and music services through the BRAVIA Internet Video platform including Sony's Video On Demand and Music Unlimited powered by Qriocity&trade;, as well as Netflix&trade;, Pandora&reg;, HuluPlus&trade;, Amazon Video on Demand, YouTube&trade;, Slacker&reg; Internet Radio, Crackle and Blip.tv.</p>

<p>Video On Demand powered by Qriocity is a premium video streaming service with instant access to Hollywood blockbusters, and Music Unlimited powered by Qriocity is a cloud-based, streaming music service which gives music lovers access at anytime to a constantly expanding catalog of over six million global music tracks from major labels.</p>

<p>"Leveraging our strengths in hardware and content, only Sony can bring consumers devices that deliver the very best of entertainment," said Brian Siegel, vice president of Sony Electronics' home audio and video business.  "Our new Blu-ray Disc devices not only playback high-quality 1080p 2D and 3D content from Blu-ray, but they offer access to the widest array of online video and audio entertainment from across the Internet."</p>

<p>The line is also compatible with Sony's new HomeShare wireless audio products such as the new SA-NS400 Wi-Fi network speaker allowing users to easily stream audio from the devices through the home wirelessly via Wi-Fi.  With Sony's unique PartyStreaming feature, you can share music throughout the home without wires and complicated set up.</p>

<p><br />
<strong>Blu-ray Disc Players</strong></p>

<p>Sony's BDP-S780 Blu-ray Disc player is Blu-ray 3D capable and compatible with DVDs, CDs, and SACDs.  The model features built in Wi-Fi wireless (802.11n) with Wi-Fi Protected Setup (WPS) as well as an Ethernet jack for easy access to BRAVIA Internet Video and BD-LIVE&trade;.</p>

<p>Compatible with Digital Living Network Alliance (DLNA&reg;) devices, the model serves as a DLNA client and allows you to wirelessly share digital photos, music, and video media from your PC using your home network.  It also includes a front USB input for convenient access to photo, music, and video playback.  The model is also compatible with Sony's new HomeShare Wi-Fi Network Speakers.</p>

<p>The player also has Skype&trade; embedded.  When connecting a USB TV camera, purchased separately, users can enjoy free widescreen Skype-to-Skype video calls with friends and family from the comfort of their living rooms.</p>

<p>The model also features an Entertainment Database Browser, using Gracenote&trade; technologies, that allows users to browse details like actor and production information from a Blu-ray disc and access related content found on BRAVIA Internet Video content.</p>

<p>Users with an iPhone&reg;, iPod touch&reg;, or Android&trade; mobile device can control Sony's entire Blu-ray line-up using a free application that can be downloaded from the app stores.  The updated Sony "Media Remote" app allows the mobile device to function as a remote control that includes the ability to access a Blu-ray Disc's details such as jacket artwork, actor, and production information as well as search for additional video clips online.  With an Android phone, consumers can also use the app to control their Sony Blu-ray and BRAVIA products with their voice.  The app is also compatible with select new BRAVIA HDTVs announced today.</p>

<p>With the press of a button, the BDP-S780 can up convert 2D content to simulate 3D and improves standard definition and web video quality with Sony's IP Content Noise Reduction technology and Precision Cinema HD Upscaling technologies.</p>

<p>It also includes Super Bit Mapping, Smoothing, and Chroma Processing technologies which deliver smoother color gradation when connected via HDMI.</p>

<p>Building on industry leading fast load times, the model features an improved start up and disc loading performance.  When the quick start feature is turned on, the start-up time is quicker and disc loading is faster than previous models.</p>

<p>The BDP-S780 will be available in April for about $250.</p>

<p>Also new to Sony's Blu-ray Disc player line is the BDP-S580 Blu-ray Disc player featuring built in Wi-Fi (802.11n) with WPS.  Other features found on this model include:</p>

<p>    Full HD 1080p playback<br />
    Blu-ray 3D, DVD, CD and SACD compatibility<br />
    IP Content Noise Reduction<br />
    BRAVIA Internet Video and BD-LIVE functionality<br />
    Entertainment Database Browser with Gracenote technology<br />
    DLNA Client and photo, music, and video playback<br />
    Photo, music, and video playback via front USB<br />
    HomeShare Wi-Fi Network Speaker compatible<br />
    "Media Remote" (iPhone/iPod touch/Android BD Remote Control application)<br />
    Quick start and loading<br />
    Available in March for about $200</p>

<p><br />
Rounding out the line, other new Blu-ray Disc players include:</p>

<p>BDP-S480 Blu-ray Disc Player</p>

<p>    Full HD 1080p playback<br />
    Blu-ray 3D, DVD, CD, and SACD playback<br />
    Wi-Fi-ready (USB wireless LAN adapter sold separately)<br />
    BRAVIA Internet Video and BD-LIVE functionality<br />
    Entertainment Database Browser with Gracenote technology<br />
    DLNA Client and photo, music, and video playback<br />
    Photo, music, and video playback via front USB<br />
    HomeShare Wi-Fi Network Speaker compatible<br />
    "Media Remote" (iPhone/iPod touch/Android BD Remote Control application)<br />
    Quick start and loading<br />
    Available in March for about $180</p>

<p><br />
BDP-S380 Blu-ray Disc Player</p>

<p>    Full HD 1080p playback<br />
    DVD, CD, and SACD playback<br />
    Wi-Fi-ready (USB wireless LAN adapter sold separately)<br />
    BRAVIA Internet Video and BD-LIVE functionality<br />
    Entertainment Database Browser with Gracenote technology<br />
    Photo, music, and video playback via front USB<br />
    "Media Remote" (iPhone/iPod touch/Android BD Remote Control application)<br />
    Quick start and loading<br />
    Available in February for about $150</p>

<p></p>

<p><strong>Blu-ray Disc Home Theater Systems</strong></p>

<p>Sony's new Blu-ray Disc home theater line feature three models that are Blu-ray 3D capable and all offer access to Sony's BRAVIA Internet Video and BD-LIVE&trade; technology.</p>

<p>The flagship BDV-E780W is a full HD 1080p 5.1 channel 1000 watt Blu-ray Disc home theater system that features built-in Wi-Fi (802.11n), wireless rear speakers, 2-way front speakers that can deliver a wider audio sweet spot ideal for family viewing and an improved subwoofer.</p>

<p>The model also features a universal remote, as well as the "Media Remote" control app available for iPhone/iPod touch and Android devices.</p>

<p>It includes Sony's Entertainment Database Browser with Gracenote&reg; technology to access disc cover art, actor, and production information, as well as IP Content Noise Reduction technology and Precision Cinema HD Upscaling to improve standard definition and web video quality.</p>

<p>Serving as a DLNA client, the model is compatible with DLNA devices and includes a front USB input for photo, music, and video playback and is compatible with Sony's new HomeShare Wi-Fi Network Speakers.</p>

<p>The model also comes with an iPhone/iPod dock and offers two HDMI inputs and will be available in May for about $600.</p>

<p>Other new Blu-ray Disc home theater systems include:</p>

<p><br />
<strong>BDV-E580 Blu-ray Disc Home Theater System</strong></p>

<p>Available in March for about $500</p>

<p>    1000 watt, 5.1 channel home theater system<br />
    Blu-ray 3D, DVD, CD, and SACD playback<br />
    Two HDMI inputs<br />
    Integrated Wi-Fi (802.11n) with WPS<br />
    BRAVIA Internet Video<br />
    Entertainment Database Browser with Gracenote technology<br />
    USB and DLNA Client and photo, music, and video playback<br />
    HomeShare Wi-Fi Network Speaker compatible<br />
    "Media Remote" (iPhone/iPod touch/Android BD Remote Control application)<br />
    iPhone/iPod touch dock included<br />
    Quick start and load</p>

<p></p>

<p><strong>BDV-E280 Blu-ray Disc Home Theater System</strong></p>

<p>Available in April for about $400</p>

<p>    1000 watt, 5.1 channel home theater system<br />
    Blu-ray 3D, DVD, CD, and SACD playback<br />
    Two HDMI inputs<br />
    Wi-Fi-ready (USB wireless LAN adapter sold separately)<br />
    BRAVIA Internet Video<br />
    iPhone/iPod touch dock included<br />
    Quick start and load</p>

<p></p>

<p><strong>Portable Blu-ray Disc Player</strong></p>

<p>Sony's new BDP-SX1000 is the company's first portable Blu-ray Disc player.  The 10.1-inch wide, WSVGA model plays back both Blu-ray and DVD discs, as well as USB media.</p>

<p>It features 180-degree swivel display, five hour battery life from the built-in, rechargeable battery and offers built-in speakers and a headphone output.</p>

<p>The model will be available this spring for about $300.</p>

<p><br />
<strong>Home Theater Audio System</strong></p>

<p>Sony also announced the HT-SS380 5.1 channel home theater audio system with Blu-ray Disc player matching design.  The 1000 watt model includes three HDMI inputs and one output featuring an HDMI repeater (3 inputs/1 output), stand-by pass through, 3D pass through, and audio return channel allowing the television to send audio signals back to the unit for playback.</p>

<p>The models ship with an iPhone/iPod dock included for playback of audio, video, and photos.</p>

<p>The HT-SS380 will be available this spring for about $350.</p>

<p>All new Blu-ray Disc players and home theater systems will be available at Sony Style stores, online at <a target="_blank" href="http://www.sonystyle.com/">www.sonystyle.com</a>, at military base exchanges, and at authorized retailers nationwide. </p>

<p>SOURCE Sony Electronics Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2011 10:06 PM</b>
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
			<?=getComments(4143)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4143)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/new-sony-bluray-players-and-home-theater-systems-deliver-connected-entertainment-experience.php" type="text/javascript" charset="utf-8"></script>
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