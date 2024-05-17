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
		AND e.entry_id = 1348";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1348 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1348 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1348";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/04/iomega-announces-new-media-xporter-drive-for-use-with-todays-popular-game-consoles.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1348";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Iomega Announces New Media Xporter Drive for Use With Today\'s Popular Game Consoles" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Iomega Announces New Media Xporter Drive for Use With Today\'s Popular Game Consoles" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Iomega Announces New Media Xporter Drive for Use With Today\'s Popular Game Consoles" />
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
	<title>HDTV Magazine - Iomega Announces New Media Xporter Drive for Use With Today's Popular Game Consoles</title>
	<meta name="keywords" content="media xporter, xporter drive, iomega corporation, game consoles, iomega media, iomega, media, drive, xporter, game, xbox, usb, new, consoles, playstation, video, corporation, product, storage, today, photos, formats, console, music, family" />
	<meta name="description" content="Iomega Corporation (NYSE:IOM) , a global leader in data protection and security, today announced the new Iomega(R) Media Xporter(TM) Drive, a game-oriented portable hard drive that provides cross-platform media storage for Xbox(TM) 360 and PlayStation(R) 3 consoles, making it easier than ever to utilize today's popular game consoles and high definition televisions and other large screen TVs to share photos, videos, and music collections with family and friends.

The wallet-sized USB-powered 160GB* Media Xporter Drive utilizes the USB 2.0 ports on the Xbox 360 or the PlayStation 3 to..." />
	<meta name="title" content="Iomega Announces New Media Xporter Drive for Use With Today's Popular Game Consoles" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Iomega Announces New Media Xporter Drive for Use With Today's Popular Game Consoles" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/04/iomega-announces-new-media-xporter-drive-for-use-with-todays-popular-game-consoles.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Iomega Corporation (NYSE:IOM) , a global leader in data protection and security, today announced the new Iomega(R) Media Xporter(TM) Drive, a game-oriented portable hard drive that provides cross-platform media storage for Xbox(TM) 360 and PlayStation(R) 3 consoles, making it easier than ever to utilize today's popular game consoles and high definition televisions and other large screen TVs to share photos, videos, and music collections with family and friends.

The wallet-sized USB-powered 160GB* Media Xporter Drive utilizes the USB 2.0 ports on the Xbox 360 or the PlayStation 3 to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1348', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/04/iomega-announces-new-media-xporter-drive-for-use-with-todays-popular-game-consoles.php">Iomega Announces New Media Xporter Drive for Use With Today's Popular Game Consoles</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>April 17, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=275&category=Gaming">Gaming</a></b>
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
				<p class="prtitle">Iomega Announces New Media Xporter Drive for Use With Today's Popular Game Consoles</p>

<center><i>Innovative Cross-Platform Portable Storage Device Makes It Easy to Enjoy Digital Video, Images and Music in the Living Room Without a PC</i></center><br />
<br />

<p><B>SAN DIEGO, April 17 /PRNewswire-FirstCall/</B> -- Iomega Corporation (NYSE:IOM) , a global leader in data protection and security, today announced the new Iomega(R) Media Xporter(TM) Drive, a game-oriented portable hard drive that provides cross-platform media storage for Xbox(TM) 360 and PlayStation(R) 3 consoles, making it easier than ever to utilize today's popular game consoles and high definition televisions and other large screen TVs to share photos, videos, and music collections with family and friends.</p>

<p>The wallet-sized USB-powered 160GB* Media Xporter Drive utilizes the USB 2.0 ports on the Xbox 360 or the PlayStation 3 to give gaming enthusiasts a convenient new way to enjoy music, digital movies, and photos through the family game console, without the need of a PC or digital media adapter. The new Iomega Media Xporter Drive has been tested for compatibility with the Xbox 360 and the PlayStation 3 and like other Iomega products is a complete storage solution that includes value-added software for converting file formats not natively supported by the game consoles.</p>

<p><br />
<B>About the Iomega Media Xporter Drive</B></p>

<p>Because the current Xbox 360 and PlayStation 3 consoles support HDMI connections and high-definition television resolutions of up to 1080p, photos and videos stored on the Iomega Media Xporter Drive (and displayed through either game console) take on the breathtaking sharpness and clarity of today's most advanced HDTV's.</p>

<p>"The new Iomega Media Xporter Drive extends the value of today's most popular game consoles that are already evolving into home entertainment centers," said Ralf San Jose, global product manager for HDD Products, Iomega Corporation. "For the home-gamer enthusiast, the Media Xporter Drive provides an easy-to-use portal for 160GB of family photos, music files and videos to move beyond the computer room and into the family room for the enjoyment of everyone."</p>

<p>Designed for plug-and-play operation and easy portability, Iomega's new Media Xporter Drive features a rugged 2.5 inch portable hard drive that requires no external power supply -- just connect its USB 2.0 "Y" cable to two available USB 2.0 ports on the Xbox 360 or PlayStation 3. The Media Xporter drive natively supports PlayStation 3 and Xbox 360 compatible formats such as MP3, MPEG-4 and JPEG, and it also comes with video conversion software to convert additional video file formats into compatible game console formats. Prism Video Converter MX software can be downloaded free of cost to convert input files in formats such as QuickTime, AVI, VOB and others, into MPEG-4 files that are playable by the game consoles.</p>

<p>"The new Media Xporter Drive is a total solution from Iomega that includes software to convert most kinds of video formats for use with an Xbox 360 or PlayStation 3," continued San Jose. "That means a game console can run an entire media library, be the family video and photo viewer, not to mention run favorite music playlists. The Iomega Media Xporter Drive makes home entertainment accessible and portable in a way that's fresh and relevant for today's home gamer."</p>

<p>As an added plus, users of the new Media Xporter Drive can not only take their media files wherever they need them, but also use the Media Xporter Drive as a backup solution for 160GB of media files stored on virtually any computer with a USB port. Iomega's new Media Xporter Drive can carry up to 640,000 photos, over 2,900 hours of music or 240 hours of video.**</p>

<p><br />
<B>About the PlayStation 3 and Xbox 360 Game Consoles</B></p>

<p>Microsoft's Xbox 360(TM) game console, released worldwide in late 2005, supports HD video output of 720p, 1080i or 1080p with the Xbox 360(TM) Component HD AV cable (or HDMI cable for HDMI-equipped Xbox 360 units). It has 3 USB 2.0 ports which can be used with the Iomega Media Xporter Drive.</p>

<p>Sony's PlayStation 3 game console, released in Japan and North America in late 2006, supports video of 1080p, 1080i, 720p, or 480p and 480i either through the built-in HDMI connector or through Sony's AV Multi cable for component connections. Most models come with 4 USB 2.0 ports (except for the 40GB model which comes with 2 USB 2.0 ports), any of which can be used with the Iomega Media Xporter Drive.</p>

<p><br />
<B>Compatibility</B></p>

<p>Designed for use with Xbox(TM) 360 and PlayStation(R) 3 game consoles, the Iomega(R) Media Xporter Drive USB 2.0, 160GB is also compatible with Apple computers (Mac(R) OS X 10.1 and above) and with Microsoft(R) Windows 2000 Professional or above, including Windows Vista.</p>

<p><br />
<B>Availability and Price</B></p>

<p>The Iomega(R) Media Xporter Drive USB 2.0, 160GB is now available in the U.S. for $119.95 (U.S. suggested retail) and in Europe for euro 99.</p>

<p><br />
<B>About Iomega</B></p>

<p>Iomega Corporation, headquartered in San Diego, is a worldwide leader in innovative storage and network security solutions for small and mid-sized businesses, consumers and others. The Company has sold more than 400 million digital storage drives and disks since its inception in 1980. Today, Iomega's product portfolio includes industry leading network attached storage products, external hard drives, and our award-winning removable storage technology, the REV(R) Backup Drive. OfficeScreen(R), Iomega's managed security services, available in the U.S. and select markets in Europe, provides enterprise quality perimeter security and secure remote network access for SMBs, which help protect small enterprises from data theft and liability. To learn about all of Iomega's digital storage products and managed services solutions, please go to the Web at http://www.iomega.com/. Resellers can visit Iomega at http://www.iomega.com/ipartner.</p>

<p>NOTE: The statements contained in this release regarding development, production and distribution of the Iomega(R) Media Xporter Drive USB 2.0, 160GB, anticipated product pricing and availability, expected product performance and specifications, future applications for the new product and all other statements that are not purely historical, are forward-looking statements within the meaning of the Private Securities Litigation Reform Act of 1995. All such forward-looking statements are based upon information available to Iomega as of the date hereof, and Iomega disclaims any intention or obligation to update any such forward-looking statements. Actual results could differ materially from current expectations. Factors that could cause or contribute to such differences include, but are not limited to, the successful completion of product development and testing, market acceptance of, and demand for, the Iomega product, any difficulties encountered in ramping up production or other manufacturing issues, including component availability and pricing, co-development, production, and distribution issues, product pricing and conformity to specifications, dependence upon third party suppliers, competition, intellectual property rights and other risks and uncertainties identified in the reports filed from time to time by Iomega with the U.S. Securities and Exchange Commission, including Iomega's Annual Report on Form 10-K for the year ended December 31, 2007, and its most recent Quarterly Report on Form 10-Q.</p>

<p> * 1 GB = 1,000,000,000 bytes.<br />
 ** 4 photos/MB - highly compressed 3-megapixel JPG photos; 1.1 min/MB -<br />
 128 kbps MP3 audio; 11 MB/Min - DVD MPEG 2 (720 x 480).</p>

<p><br />
Copyright(C) 2008 Iomega Corporation. All rights reserved. Iomega, Zip, REV, OfficeScreen, StorCenter, and Media Xporter, are either registered trademarks or trademarks of Iomega Corporation in the United States and/or other countries. All other trademarks, trade names, service marks, and logos referenced herein belong to their respective companies.</p>

<p> Media please contact:<br />
 Chris Romoser, Iomega Corporation, (858) 314-7148 romoser@iomega.com</p>

<p> Analyst/Investors, please contact:<br />
 Preston Romm, Iomega Corporation, (858) 314-7188</p>

<p>Source: Iomega Corporation</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>April 17, 2008  6:52 AM</b>
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
			<?=getComments(1348)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1348)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/04/iomega-announces-new-media-xporter-drive-for-use-with-todays-popular-game-consoles.php" type="text/javascript" charset="utf-8"></script>
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