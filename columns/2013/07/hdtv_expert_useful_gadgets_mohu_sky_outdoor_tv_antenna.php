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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, viglink_suppress, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 5128";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];
	# I was going to use this and discovered that I could turn off insertion separately from affiliation
	#$viglink_suppress = ($row_aux['viglink_suppress'] == 1) ? 'nolinks' : '';

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5128 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5128 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5128";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/07/hdtv-expert-useful-gadgets-mohu-sky-outdoor-tv-antenna.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5128";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna</title>
	<meta name="keywords" content="uhf channels, vhf uhf, sky antenna, channels received, vhf channels, antenna, uhf, channel, channels, sky, antennas, vhf, clearstream, stations, ’s, received, here, signal, test, reception, band, cable, outdoor, –, mohu" />
	<meta name="description" content="Mohu&amp;acirc;��s Leaf and Ultimate indoor TV antennas have scored highly in my tests. So how well does the Mohu Sky outdoor antenna work? Read on, and find out." />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/07/hdtv-expert-useful-gadgets-mohu-sky-outdoor-tv-antenna.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Mohu&amp;acirc;��s Leaf and Ultimate indoor TV antennas have scored highly in my tests. So how well does the Mohu Sky outdoor antenna work? Read on, and find out." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5128', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important nolinks"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare nolinks" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/07/hdtv-expert-useful-gadgets-mohu-sky-outdoor-tv-antenna.php">HDTV Expert - Useful Gadgets: Mohu Sky Outdoor TV Antenna</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>July 19, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=298&category=Broadcast">Broadcast</a></b>, <b><a href="/category.php?id=460&category=Satellite HDTV">Satellite HDTV</a></b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p>Depending on which media outlets you follow, “cutting the cord” is a fast-growing phenomenon. Or maybe it isn’t. Or maybe it&#8217;s a short-term threat to the bottom line of pay TV. Or perhaps it’s a long-term threat.</p>
<p>We do know this: Pay TV subscription rates have increased astronomically in the past ten years. An increasing number of subscribers are bellyaching about paying for channels they don’t watch. Some have even gone so far as to “cut the cord” and drop pay TV channel packages altogether; opting for Internet streaming and in some cases, free over-the-air TV broadcasts.</p>
<p>If you live in a major TV market, chances are there are plenty of free OTA channels you can pull in. Since every television sold since 2006 must include a digital TV tuner for these broadcasts, all you need is some sort of antenna to receive those signals.</p>
<p>And you may be surprised by how many channels there are. If you live in the Los Angeles basin, there are no less than 27 different digital TV broadcast channels carrying over 130 minor (sub) channels of programming! That’s more than I have in my cable TV package, although I’ll grant that I wouldn’t watch many of them.</p>
<p>But at least I don’t have to pay for channels I don’t watch. And that’s the appeal of free OTA TV, combined with on-demand streaming of movies and TV shows from outlets such as Hulu, Amazon, Netflix, and Vudu. All you are paying for is a fast Internet connection.</p>
<div id="attachment_3287" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3287" alt="Here's Mohu's Sky antenna, jury-rigged to a ten-foot mast and ready for testing." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/Mohu-Sky-Installed-WEB.jpg" width="600" height="349" /><p class="wp-caption-text">Here&#8217;s Mohu&#8217;s Sky antenna, jury-rigged to a ten-foot mast and ready for testing.</p></div>
<p>REACH FOR THE SKY</p>
<p>In the past, I’ve tested a raft of indoor TV antennas from Mohu, Walltenna, Winegard, Antennas Direct, and Northvu. <a href="http://www.hdtvexpert.com/?cat=5">In my most recent test</a>, I also included an indoor test of Mohu’s Sky amplified TV antenna ($169.99, available from Mohu, Amazon, and other online retailers). While it did a pretty good job, this product is intended for true outdoor use and won’t replace a flat, wall-mount antenna.</p>
<p>So, I freed up some time to set up the Sky on my rear deck and really cut it loose. The Sky resembles an “x” dipole, or a crossed dipole antenna. It’s housed in solid plastic and comes with a “J” arm support for and mounting plate for attaching to a roof or eave. The Sky measures 21” x 9” x 1” and is supplied with a 30-foot-long coaxial cable. There’s also an active amplifier inside the Sky, powered by an inline USB-style transformer that mounts at your TV.</p>
<p>You don’t have to use the supplied cable – you can use any cable you want, and I suggest sticking with a decent quality run of RG-6U cable from antenna to TV to keep signal attenuation to a minimum. The phantom power supply will work with really long cable runs (I tried it with 100’ of coax, no problem), and you can also mount the power supply in your basement or attic and split the incoming signal to feed two or more televisions.</p>
<div id="attachment_3288" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3288" alt="Antennas Direct's ClearStream 1 came out of storage for the competition..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/ClearStream-2-Against-Sky-WEB.jpg" width="600" height="403" /><p class="wp-caption-text">Antennas Direct&#8217;s ClearStream 1 came out of storage for the competition&#8230;</p></div>
<div id="attachment_3289" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3289" alt="...as did the ClearStream 2, tested on this site a few years ago." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/ClearStream-2-against-house-WEB.jpg" width="600" height="450" /><p class="wp-caption-text">&#8230;as did the ClearStream 2, tested on this site a few years ago.</p></div>
<p>&nbsp;</p>
<div id="attachment_3290" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3290" alt="Channel Master's 4221 4-bay colinear UHF antenna uses 60-year-old technology - and still works like a charm." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/CM-4221-Against-the-sky-WEB.jpg" width="600" height="376" /><p class="wp-caption-text">Channel Master&#8217;s 4221 4-bay colinear UHF antenna uses 60-year-old technology &#8211; and still works like a charm.</p></div>
<p>For comparisons, I went into my “aluminum archive” and pulled out a ClearStream 1 (single loop antenna) and ClearStream 2 (dual loop antenna), both sold by Antennas Direct, and a Channel Master 4221 four-bay “x” dipole antenna. To level the playing field, I added an external “off brand” amplifier with the ClearStream and CM antennas. This amplifier has about the same gain figure (15 dB) as the Sky model. (You can’t use the Sky antenna without its amplifier switched on.)</p>
<p>THE TEST</p>
<p>For my tests, I procured a pair of 5’ steel masts from Radio Shack and supported them with a Winegard tripod mount, held in place by cinder blocks. The actual outdoor reception test was simple. I attached each antenna to the top of the 10’ mast and rotated it to aim south-southwest toward Philadelphia (position “A” in the results).</p>
<p>I scanned for active channels using my Hauppauge Aero-M USB DTV tuner stick, and for every channel I detected, I then scanned for Program and System Information Protocol (PSIP data). If I was able to read it and identify the channel, I looked at the actual MPEG transport stream using TS Reader (indicated dropped packets and transmission errors) and finally verified that I had 60 – 90 seconds of clean video and audio with no dropout.</p>
<div id="attachment_3291" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3291" alt="Here's my test rig, with an AVCOM spectrum analyzer and Hauppauge Aero-M connected to my Toshiba latop for reception and measurements." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/Computer-and-Spectrum-Analyzer-WEB.jpg" width="600" height="420" /><p class="wp-caption-text">Here&#8217;s my test rig, with an AVCOM spectrum analyzer and Hauppauge Aero-M connected to my Toshiba latop for reception and measurements.</p></div>
<div id="attachment_3292" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3292" alt="Although this housing is lettered just like the Mohu Bolt amplifier, it's actually a phantom power supply for the Sky's internal preamp." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/Sky-Phantom-Power-WEB.jpg" width="600" height="450" /><p class="wp-caption-text">Although this housing is lettered just like the Mohu Bolt amplifier, it&#8217;s actually a phantom power supply for the Sky&#8217;s internal preamp.</p></div>
<p>&nbsp;</p>
<p>This process was repeated after I swung the antennas to the north-northwest, towards Allentown, PA. I expected that in some cases, I’d be able to receive stations from both markets regardless of the antenna position. That’s because these antennas are sold as somewhat omnidirectional or “non-directional.” The manufacturer expects you can install the antenna outdoors as high as practical, and you shouldn’t have to worry about its orientation (North? South? West?).</p>
<p>In reality, all of the antennas I tested are somewhat directional, as you’ll see from my tests. So I suggest picking up a small antenna rotor, which is easy to find at Radio Shack and other online stores. Rotors come in real handy if the TV stations in your market have towers scattered all around the city. (Pittsburgh and Atlanta come to mind here.)</p>
<p>I also took a look at the actual 8VSB carrier waveforms using an AVCOM PSA-2500C spectrum analyzer, mostly to see how much multipath “tilt” was present in the signal. I’ve included a few of those screen grabs here to show the relative signal strength of multiple TV transmitters in the UHF band as received by each antenna.</p>
<p>At my location, the pickings on VHF are slim. WPVI broadcasts a towering signal on channel 6 in Roxborough, PA, while WHYY has a potent carrier on channel 12. In Allentown, WBPH is a strong beacon on channel 9. And that’s about it – the rest of the stations are found on the UHF band.</p>
<p>Of that group, several stations usually stand out in my tests. WPHL is very strong on channel 17, as is KYW on channel 26. (I can receive KYW in my basement, and I’m 22 miles away from the transmitter!) WCAU is pretty reliable on channel 34, as is WLVT on channel 39. And WFMZ in Allentown is broadcasting with over one million watts ERP on channel 46, meaning I can usually pull them in with a paper clip.</p>
<p>I should point out here that the vast majority of indoor TV antennas work pretty well at UHF frequencies, but are electrically too small to pull in many high-band VHF channels. They just can’t approach resonance and have gain. The same thing applies to outdoor antennas – a solid performer at UHF frequencies may have little or no gain on high-band VHF channels.</p>
<div id="attachment_3293" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3293" alt="Here's a spectral view of channels 6 through 13, as received through the Sky antenna." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/Mohu-Sky-TK-II-Chs-6-13-Position-A-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">Here&#8217;s a spectral view of channels 6 through 13, as received through the Sky antenna.</p></div>
<p>&nbsp;</p>
<div id="attachment_3294" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3294" alt="And here's how channels 6 through 13 look like as received with the ClearStream 1." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/AD-SL-PMP-Chs-6-13-Position-A-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">And here&#8217;s how channels 6 through 13 look like as received with the ClearStream 1. Note that WPVI&#8217;s signal on channel 6 (about 85 MHz) is not receivable on the CS-1, but comes in like gangbusters on the Sky (above).</p></div>
<p>That doesn’t mean you won’t be able to receive any VHF channels. If the signal strength is there, your smaller antenna may couple enough energy anyway to enable reception. But keep in mind that while a quarter-wavelength antenna for UHF reception might only be five inches long, a quarter-wave antenna for pulling in channel 7 needs to be about 16 inches long to achieve resonance.</p>
<p>The moral of the story is that all of the test antennas are physically the right size for pulling in UHF channels. They may not work quite as well for high-band (175 – 216 MHz) VHF channels, and I don’t expect they’d work at all with low-band (54 – 87 MHz) VHF reception. It all depends on the distance from your reception location to the transmitter.</p>
<p>THE RESULTS</p>
<p>Table 1 shows how all of the antennas fared. In the “A” position, the Sky gave a good accounting of itself, pulling in all three of the Philly and Allentown high-band VHF broadcasts. It also snagged seven of the ten strongest UHF stations coming from both markets. While Antennas Direct’s ClearStream 1 couldn’t find WPVI on channel 6 (that resonance thing, again), it did even better by pulling in the remaining two VHF signals and all ten of the UHF stations.</p>
<p>&nbsp;</p>
<div id="attachment_3296" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3296" alt="Table 1 - Results of the outdoor reception tests. Stations received successfully are indicated in green text. " src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/TABLE-1-WEB.jpg" width="600" height="276" /><p class="wp-caption-text">Table 1 &#8211; Results of the outdoor reception tests. Stations received successfully are indicated in green text.</p></div>
<p>&nbsp;</p>
<p>Oddly, the ClearStream 2 picked up one VHF channel, but dropped the UHF signal from WYBE-35, giving it a score of 3 VHF and 9 UHF channels. And the venerable Channel Master 4221 four-bay collinear antenna nearly matched it, missing only WYBE and WPVI-6. (Again, this antenna has no gain at lower frequencies.)</p>
<p>Turning the antennas northwest to favor Allentown (position “B”) really quieted things down. The playing field was almost level across all antennas with the Sky locating 2 VHF and 2 UHF stations, the ClearStream 1 digging out one additional UHF station, the Clear Stream 2 adding one more UHF station, and the 4221 spotting one VHF and three UHF stations.</p>
<div id="attachment_3298" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3298" alt="Here's a spectral view of all UHF channels as received with the Sky antenna. Compare it to..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/Chs-14-51-Mohu-Sky-Position-A-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">Here&#8217;s a spectral view of all UHF channels as received with the Sky antenna. Compare it to&#8230;</p></div>
<div id="attachment_3299" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3299" alt="...all UHF channels received with the ClearStream 1..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/AD-SL-PMP-Chs-14-51-Position-A-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">&#8230;all UHF channels received with the ClearStream 1&#8230;</p></div>
<div id="attachment_3300" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3300" alt="...all UHF channels as received with the ClearStream 2..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/AD-DL-PMP-Chs-14-51-Position-A-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">&#8230;all UHF channels as received with the ClearStream 2&#8230;</p></div>
<div id="attachment_3302" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3302" alt="...and all UHF channels received using the Channel Master 4221. " src="http://www.hdtvexpert.com/wp-content/uploads/2013/07/Chs-14-51-4-Bay-PMP-Position-A-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">&#8230;and all UHF channels received using the Channel Master 4221. All antennas were in position &#8220;A&#8221; for these readings.</p></div>
<p>CONCLUSION</p>
<p>Mohu’s Sky antenna is a strong performer. It did surprisingly well in my earlier indoor antenna tests, but it’s much happier in free space with plenty of oxygen flowing around it. The antenna does exhibit a directional characteristic, as did the three other antennas in this test. But it was able to handle both VHF and UHF signals with aplomb, although its UHF performance wasn’t quite as good as the ClearStream 1 and 2 loop antennas with external amplifiers.</p>
<p>&nbsp;</p>
<p>Mohu Sky Outdoor VHF/UHF TV Antenna</p>
<p>MSRP: $169.99</p>
<p>Sold by Greenwave Scientific</p>
<p><a href="http://www.gomohu.com">www.gomohu.com</a></p>
<p>&nbsp;</p>
<p>Also available from other online retailers.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>July 19, 2013 11:13 AM</b>
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
			<?=getComments(5128)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5128)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/07/hdtv-expert-useful-gadgets-mohu-sky-outdoor-tv-antenna.php" type="text/javascript" charset="utf-8"></script>
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