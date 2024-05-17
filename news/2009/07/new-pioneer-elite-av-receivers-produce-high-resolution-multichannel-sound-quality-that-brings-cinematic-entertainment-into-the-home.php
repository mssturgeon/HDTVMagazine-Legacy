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
		AND e.entry_id = 1751";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1751 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1751 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1751";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/07/new-pioneer-elite-av-receivers-produce-high-resolution-multichannel-sound-quality-that-brings-cinematic-entertainment-into-the-home.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1751";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home" />
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
	<title>HDTV Magazine - New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home</title>
	<meta name="keywords" content="vsx txh, home theater, pioneer elite, multi channel, home entertainment, pioneer, home, entertainment, elite, new, receivers, audio, models, txh, vsx, performance, sound, multi, theater, digital, high, thx, video, quality, channel" />
	<meta name="description" content="Raising the bar on high power A/V Receiver performance, Pioneer Electronics (USA) Inc. today unveils four A/V Receivers designed to bring enhanced clarity, detail and dynamic &quot;quick response&quot; from uncompressed Dolby&amp;reg; True-HD and DTS-HD&amp;trade; Master Audio soundtracks used in Blu-ray Disc&amp;reg; entertainment. The new Pioneer&amp;reg; Elite&amp;reg; SC-25 and SC-27 A/V Receivers incorporate a Direct Energy HD Class D amplifier featuring ICEPower&amp;trade; technology to ensure unprecedented reproduction of high resolution multi-channel, stereo as well as compressed audio and provide premium home entertainment that rivals the movie theater experience. Pioneer pushes the limits of A/V Receiver innovation with the Elite SC-27, which leverages the full advantages of this emerging amplification technology to become the industry's first Class D amp design to achieve THX&amp;trade; Ultra2 Plus as well as AIR Studio monitor certifications. Together, these achievements undoubtedly affirm the company's position as the preeminent home entertainment manufacturer for the most critical home entertainment enthusiast.

Pioneer rounds out its new line with..." />
	<meta name="title" content="New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/07/new-pioneer-elite-av-receivers-produce-high-resolution-multichannel-sound-quality-that-brings-cinematic-entertainment-into-the-home.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Raising the bar on high power A/V Receiver performance, Pioneer Electronics (USA) Inc. today unveils four A/V Receivers designed to bring enhanced clarity, detail and dynamic &quot;quick response&quot; from uncompressed Dolby&amp;reg; True-HD and DTS-HD&amp;trade; Master Audio soundtracks used in Blu-ray Disc&amp;reg; entertainment. The new Pioneer&amp;reg; Elite&amp;reg; SC-25 and SC-27 A/V Receivers incorporate a Direct Energy HD Class D amplifier featuring ICEPower&amp;trade; technology to ensure unprecedented reproduction of high resolution multi-channel, stereo as well as compressed audio and provide premium home entertainment that rivals the movie theater experience. Pioneer pushes the limits of A/V Receiver innovation with the Elite SC-27, which leverages the full advantages of this emerging amplification technology to become the industry's first Class D amp design to achieve THX&amp;trade; Ultra2 Plus as well as AIR Studio monitor certifications. Together, these achievements undoubtedly affirm the company's position as the preeminent home entertainment manufacturer for the most critical home entertainment enthusiast.

Pioneer rounds out its new line with..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1751', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/07/new-pioneer-elite-av-receivers-produce-high-resolution-multichannel-sound-quality-that-brings-cinematic-entertainment-into-the-home.php">New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July  1, 2009</b>
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
				<p class="prtitle">New Pioneer Elite A/V Receivers Produce High Resolution Multi-Channel Sound Quality That Brings Cinematic Entertainment into the Home</p>

<center><i>Four Models Take Consumer Convenience to New Levels, Especially iPhone&trade; Owners</i></center><br />
<br />

<p><img alt="Pioneer Elite SC 27" src="http://www.hdtvmagazine.com/news/images/products/pioneer-elite-sc-27.jpg" align="left" /><B>LONG BEACH, Calif.--(BUSINESS WIRE)</B>--Raising the bar on high power A/V Receiver performance, Pioneer Electronics (USA) Inc. today unveils four A/V Receivers designed to bring enhanced clarity, detail and dynamic "quick response" from uncompressed Dolby&reg; True-HD and DTS-HD&trade; Master Audio soundtracks used in Blu-ray Disc&reg; entertainment. The new Pioneer&reg; Elite&reg; SC-25 and SC-27 A/V Receivers incorporate a Direct Energy HD Class D amplifier featuring ICEPower&trade; technology to ensure unprecedented reproduction of high resolution multi-channel, stereo as well as compressed audio and provide premium home entertainment that rivals the movie theater experience. Pioneer pushes the limits of A/V Receiver innovation with the Elite SC-27, which leverages the full advantages of this emerging amplification technology to become the industry's first Class D amp design to achieve THX&trade; Ultra2 Plus as well as AIR Studio monitor certifications. Together, these achievements undoubtedly affirm the company's position as the preeminent home entertainment manufacturer for the most critical home entertainment enthusiast.</p>

<p>Pioneer rounds out its new line with the Elite&reg; VSX-21TXH and VSX-23TXH models designed to serve as the quintessential HD control center for the newest home theater systems. For consumers seeking to transform their entertainment experience, the two A/V Receivers offer superior digital connectivity, stellar convenience features as well as custom installation-friendly options that represent the company's continued dedication to ultimate HD performance. As with the company's new lineup of Pioneer-branded iPhone / iPod certified AV receivers, all four models offer seamless connectivity to iPhone mobile devices.</p>

<p>"Our new Elite models deliver remarkable sound quality directly resulting from our collaboration with AIR Studios, which has allowed us to better understand the artistic vision of the studio professional and deliver the emotional impact they imagined for cinema and home audiences alike," said David Bales, manager of audio marketing and product planning for Pioneer Electronics (USA) Inc. "By working with THX, we are able to ensure these A/V receivers meet the performance requirements needed to recreate the movie theater experience in the home with faithful reproduction of multi-channel soundtracks. Pioneer believes the A/V receiver is the heart of the home theater experience, and our longstanding relationships with these experts reaffirm our passion to bring studio-quality sound with power and precision to the home entertainment experience."</p>

<p>"With this latest line of A/V receivers, Pioneer is the first and only consumer electronics manufacturer to reach THX Select2 and Ultra2 Plus certification requirements using a Class D amplifier solution," said Warren Mansfield, director of consumer technology at THX. "Integrating Class D amplification into a THX Certified A/V receiver is no simple task. Our testing requirements are incredibly strict for noise, distortion, frequency response and output. Pioneer has done a remarkable job of blending this new technology with the raw power and performance needed to accurately present cinematic soundtracks in the home."</p>

<p><br />
<B>Audiophile Performance Finally Realized</B></p>

<p>Pioneer's top two models, the Elite SC-25 and SC-27, fulfill highly rigorous audio specification standards for home entertainment products as determined by the AIR Studios and THX sound tuning and certification programs. To successfully recreate studio-quality sound within a home theater, Pioneer engineers worked in tandem with the renowned AIR and THX creative and technology experts to develop a highly efficient Class D amplification design that offers dynamic amplification capabilities and a bevy of proprietary processing technologies.</p>

<p>To the delight of entertainment purists, the SC-25 and SC-27 utilize Burr-Brown analog to digital converters (ADC) and renowned Wolfson digital to analog converters (DAC) that work to maintain high resolution performance throughout the entire digital signal processing chain from input to output. The SC-27 takes DAC performance further with a professional-grade Burr-Brown 192kHz / 24-Bit sampling rate converter (SRC) for ultra-wide dynamic range and advanced jitter reduction. Once integrated into the new generation entertainment systems, these two superior A/V receivers provide advanced processing for a new era in high resolution audio playback.</p>

<p><br />
<B>Connectivity for the Digital Future</B></p>

<p>The Pioneer Elite VSX-21TXH and VSX-23TXH serve to compliment Pioneer's top-of-the-line Elite A/V receiver models. Utilizing Direct Energy Class A/B amplification with hand-selected DSP circuitry, these models provide dynamic home theater experiences. Outfitted with sophisticated video signal conversion, 1080p video scaling as well as interlaced to progressive conversion technologies, users can be assured of smooth and accurate picture reproduction on their HDTV's from any connected video device. As consumers continue to seek out the latest entertainment technologies, the VSX-21TXH and VSX-23TXH provide an abundance of connectivity solutions for current and new high definition as well as standard definition entertainment sources including Blu-ray Disc players, DVD players, game consoles, satellite boxes and more. Beginning with the VSX-23TXH, consumers can take advantage of additional front panel HDMI and USB inputs for immediate connection to digital camcorders, game consoles and emerging portable audio video entertainment devices.</p>

<p><br />
<B>Maximum User Convenience and Performance</B></p>

<p>Enhancing the overall user experience, the new Elite A/V receivers represent an all-digital entertainment solution for today's media devices. All four models are equipped with an attractive, graphic user interface (GUI) that helps consumers and integrators streamline initial installation, calibration and control of multimedia content. For iPhone and iPod owners, the new Elite models are packaged with a customized USB / composite video cable for plug and play connectivity and control of one's device. Users can navigate content using a central remote control option as well as deliver iPhone / iPod content to 2nd and 3rd zones using the receivers multi-zone capability and dynamic on-screen GUI. Most importantly, the new receivers take advantage of two proprietary digital audio enhancement functions, Advanced Sound Retriever (ASR) and Auto Level Control (ALC), specially developed to provide multi-channel sound performance of digital audio tracks and other input sources by improving playback quality and volume consistency when heard through home theater speakers. The new A/V Receivers continue to offer Pioneer's rich convenience features including:</p>

<ul><li>Precision Quartz Lock System (PQLS) Multi-Channel: Developed to take advantage of the unique product synergy between certain Pioneer audio video devices, when the VSX-23TXH, SC-25 or SC-27 models are connected to the Pioneer Elite BDP-23FD Blu-ray Disc player via HDMI&trade;, users can take advantage of PQLS Multi-Channel that "speed synchronizes" the digital audio data between these products to deliver jitter-free audio transmission of advanced audio formats including Dolby TrueHD and DTS-HD Master</li><li>MCACC Room Tuning: The industry's ground-breaking room calibration feature, Advanced Multi-Channel AcoustiC Calibration (MCACC) works to significantly upgrade sound playback quality in any room configuration. Working hand-in-hand with AIR Studios professional sound engineers, MCACC is directly infused with the audio reproduction philosophies of these studio experts. A quick one-touch command begins an equalization process that optimizes a room's acoustics by making subtle adjustments to a connected speaker system, neutralizes the sound field of the listening area and meticulously fine tunes for exceptional sonic performance. Utilizing phase control technology, the new receivers can eliminate signal distortion that are sometimes caused by low frequency sound sources and ensure audio arrives at the listening position in sync</li><li>Network Entertainment: The newest generation of Pioneer's sophisticated streaming function now offers access to the Sirius Satellite Radio and Rhapsody&reg; subscription music service. With Rhapsody, SC-25 and SC-27 owners have full on-demand access to a library of more than seven million songs and over 160 professionally programmed music channels right from their A/V receiver. Users can continue to stream and immediately enjoy their personal digital video, music and photos from a networked computer's hard drive through the receiver to a connected home theater system</li></ul>

<p>"Rhapsody is excited to gain an exceptional consumer electronics brand like Pioneer as a partner," says Drew Denbo, general manager of business development for Rhapsody. "This partnership is another step toward our vision of allowing users to access Rhapsody wherever and whenever they want to listen to music."</p>

<p><br />
<B>Robust Build Quality</B></p>

<p>Taking a cue from the Elite SC-09 flagship model, the newest models utilize unique Separated Component and Direct Construction techniques that have come to define Pioneer's expertise, featuring improved isolation between the pre-amplifier and amplifier sections for precise, high powered reproduction of today's demanding high resolution audio formats at extremely efficient power consumption rates. With great efforts to align and take advantage of the shared passion for cinematic performance in the home theater arena, Pioneer is excited to offer new products that recreate the entertainment experience intended by the original artists.</p>

<p><br />
<B>Ready for Custom Installation Applications</B></p>

<p>Pioneer outfits all four A/V receivers with a series of home integration specifications, a direct outcome of in-depth surveys and conversations to discover then provide mandatory requirements for a truly custom-install-ready product line that features:</p>

<ul><li>Multi-room, multi-source capability: All four models serve as the entertainment centerpiece of a minimum of 2 zone applications, and consumers can even step-up to third zone AV with the VSX-23TXH. For ultimate HD home theater performance, the SC-27 provides a 2nd Zone Component video output for multi-zone HD integration.
</li><li>Dedicated custom installation website support for immediate access to additional control codes
Dual HDMI outputs for two video devices simultaneously (VSX-23TXH and above)
</li><li>Detachable power cord: All four Elite models now offer installers increased flexibility to fit the receivers in an array of room, rack, and cabinet spaces.
</li><li>RS232 port for PC and third-party control protocols and modules</li></ul>

<p>The Pioneer Elite VSX-21TXH and VSX-23TXH will begin shipping in July 2009 for the suggested retail prices of $700 and $900, respectively. Pioneer's Elite SC-25TXH and SC-27 are shipping in August 2009 with the suggested retail prices of $1700 and $2000, respectively.</p>

<p>Pioneer's Home Entertainment and Business Solutions Group develops high definition home theater equipment for discerning entertainment enthusiasts. Its Blu-ray Disc players, A/V receivers and loudspeakers bring a new level of emotion to the HD experience. The company brands include Pioneer and Elite&reg;. When purchased from an authorized retailer, consumers receive a limited warranty for one year with Pioneer products and two years with Pioneer Elite products. More details can be located at <a target="_blank" href="http://www.pioneerelectronics.com">www.pioneerelectronics.com</a>.</p>

<p>PIONEER, the PIONEER logo and the ELITE logo are registered trademarks of the Pioneer Corporation.</p>

<p>BLU-RAY DISC is a registered trademark of Sony Corporation.</p>

<p>THX is a trademark of THX Ltd. which may be registered in some jurisdictions. All rights reserved.</p>

<p>DOLBY and the double-D symbol are registered trademarks of Dolby Laboratories.</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing LLC.</p>

<p>AIR STUDIOS and the AIR STUDIOS logo are registered trademarks of Air Sudios, Inc.</p>

<p>iPod is a trademark of Apple Computer, Inc., registered in the U.S. and other countries</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July  1, 2009  5:53 PM</b>
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
			<?=getComments(1751)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1751)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/07/new-pioneer-elite-av-receivers-produce-high-resolution-multichannel-sound-quality-that-brings-cinematic-entertainment-into-the-home.php" type="text/javascript" charset="utf-8"></script>
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