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
		AND e.entry_id = 4827";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4827 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4827 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4827";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/05/hdtv-expert-useful-gadgets-wallmounted-dtv-antennas-revisited.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4827";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited</title>
	<meta name="keywords" content="bow tie, mohu leaf, leaf plus, radio shack, tie antenna, leaf, tie, stations, bow, micron, antenna, –, channel, flatwave, test, amplified, mohu, amplifier, uhf, antennas, here, plus, dtv, same, ’" />
	<meta name="description" content="A re-test of Winegard&amp;acirc;��s FlatWave antenna, combined with a newcomer (Antennas Direct Micron XG) = another visit to Turner Engineering!" />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/05/hdtv-expert-useful-gadgets-wallmounted-dtv-antennas-revisited.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="A re-test of Winegard&amp;acirc;��s FlatWave antenna, combined with a newcomer (Antennas Direct Micron XG) = another visit to Turner Engineering!" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4827', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/05/hdtv-expert-useful-gadgets-wallmounted-dtv-antennas-revisited.php">HDTV Expert - Useful Gadgets: Wall-Mounted DTV Antennas Revisited</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>May 29, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=298&category=Broadcast">Broadcast</a></b>, <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>Last month, <a href="http://www.hdtvexpert.com/?p=2053">I tested a pile of wall-mounted indoor digital TV antennas</a> to see if they really work as advertised.  Two of them (Mohu’s Leaf and the Walltenna) performed decently, while the amplified LeafPlus was a clear winner.</p>
<p>On the other hand, Winegard’s FlatWave was a disappointment, as it didn’t perform any better than a $4.00 Radio Shack bow tie antenna. That result led to a request from Winegard to return the review sample and see if it was defective.</p>
<p>It was, according to Winegard’s National Sales Manager, Grant Whipple. The culprit was (according to their email) <em>“…a screw that was stripping and then causing a loss of contact between our circuit board and the antenna element itself.”</em> Apparently this was an early production run issue.</p>
<p>Fair enough. Grant soon had a replacement back to me. Meanwhile, Scott Kolbe, who handles PR for Antennas Direct, sensed an opportunity and sent me a sample of their Micron XG indoor amplified TV antenna to test drive. The Micron XG isn’t a flexible, thin wall-mount design, but it is an indoor antenna and I decided to test it alongside the Winegard.</p>
<div id="attachment_2120" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2120" rel="attachment wp-att-2120"><img class="size-full wp-image-2120" title="Micron XG Taped to Window MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/Micron-XG-Taped-to-Window-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">I never expected this to work -- but it did.</p></div>
<p> </p>
<div id="attachment_2121" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2121" rel="attachment wp-att-2121"><img class="size-full wp-image-2121" title="Micron XG External Amplifier MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/Micron-XG-External-Amplifier-MR.jpg" alt="" width="600" height="447" /></a><p class="wp-caption-text">Here's what the Clearstream Micron XG amplifier looks like up close.</p></div>
<p>The tests, as before, were conducted at the offices of Turner Engineering in Mountain Lakes, NJ. I stopped by there after some RF interference testing in midtown New York City the Friday before Memorial Day weekend, and John Turner and I had the run of the place – everyone had gone home for the weekend.</p>
<p>To make things more interesting, I brought along the Mohu Leaf Plus, the original RS bow tie antenna, and my spectrum analyzer and digital camera.</p>
<p>THE TEST</p>
<p>John and I followed the same test procedure as we did in April. Each antenna was taped to the window with masking tape in the same position. A channel scan was performed with a DTV receiver (this time, it was Samsung’s DTB-H260F) and we verified dropout-free reception for 1 minute on each channel to qualify it as “received.” I also recorded the transport stream from each channel to check for bit error rates (BER) and recorded screen grabs of the actual waveforms for comparison among antennas.</p>
<p>Things started off again with the bow tie, which pulled in (unamplified) seven stations, all operating on UHF channels. The strongest local stations were WMBC-18 in Upper Montclair, NJ, and WNJM-51; also on the same tower. But the bow tie also snagged WNBC-28, WFUT-30, WPXN-31, and WXTV-40 from the Empire State Building, along with WFME-29 from West Orange, NJ. All seven stations were received reliably on the Samsung tuner.</p>
<p>Next up was the replacement Winegard FlatWave. After a channel scan, it also snagged seven stations including WNJB-8 (high band VHF!), WMBC-18, WNBC-28, WCBS-33, WXTV-40, and the 2<sup>nd</sup> minor channel from WNYW-44 (virtual channel 5-2). Of course, WNJM-51 also came in with no sweat.</p>
<p> </p>
<div id="attachment_2107" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2107" rel="attachment wp-att-2107"><img class="size-full wp-image-2107" title="WMBC-18 Winegard SA MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/WMBC-18-Winegard-SA-MR.jpg" alt="" width="600" height="317" /></a><p class="wp-caption-text">Here's how WMBC-18 looked on the Winegard FlatWave antenna...</p></div>
<div id="attachment_2108" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2108" rel="attachment wp-att-2108"><img class="size-full wp-image-2108" title="WMBC-18 Bow Tie SA MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/WMBC-18-Bow-Tie-SA-MR.jpg" alt="" width="600" height="317" /></a><p class="wp-caption-text">...and here's how the same station looked on the $4 Radio Shack bow tie antenna.</p></div>
<p>In essence, i was a draw between the $40 FlatWave and $4 bow tie. The FlatWave did pull in a high band VHF station, something the bow tie could not do reliably. But the bow tie snagged three UHF stations that the Flat Wave couldn’t reel in, one of which (WFME-29) was very strong on other antennas.</p>
<p>Just for kicks, I hooked up the original Mohu Leaf and let it do its thing. The result was nine reliable UHF channels, adding WNJU-36 and WWOR-38 to the previous lists. Catastrophe struck with the Leaf Plus, though – even though its power indicator LED was lit, absolutely ZERO signal passed through to the analyzer. It was cooked!</p>
<p> </p>
<div id="attachment_2116" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2116" rel="attachment wp-att-2116"><img class="size-full wp-image-2116" title="Spectral View 480 - 720 MHz Clearstream AMP SA MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/Spectral-View-480-720-MHz-Clearstream-AMP-SA-MR.jpg" alt="" width="600" height="317" /></a><p class="wp-caption-text">Here's what the UHF TV spectrum looks like with the amplified Micron XG...</p></div>
<p> </p>
<div id="attachment_2126" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2126" rel="attachment wp-att-2126"><img class="size-full wp-image-2126" title="Spectral View 480 - 720 MHz Mohu Leaf w Clearstream Amp SA MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/Spectral-View-480-720-MHz-Mohu-Leaf-w-Clearstream-Amp-SA-MR1.jpg" alt="" width="600" height="317" /></a><p class="wp-caption-text">...and here's how the same block of channels appeared with the amplified Mohu Leaf (not the Mohu Plus).</p></div>
<p>By itself, the Clear Stream Micron XG reached out and grabbed six UHF stations – WMBC-18, WNBC-28, WFME-29, WCBS-33, WXTV-40, and WNYW-44’s ’5-2’ service. WNJM-51 finished off the list. Not bad, but hardly an improvement over the bow tie. Adding the inline preamp netted three more UHF stations – WFUT-30, WPXN-31, and WCBS-33, putting the Micron XG on a par with Mohu’s basic Leaf. One caution – the in-line amplifier lets you kick in 5, 10, 15, or 20 dB of signal boost, but you need to use it sparingly – otherwise, you’ll ‘swamp’ your TV and create a lot of noise across the band.</p>
<p>Since the Micron XG preamp is a standalone product and works with its own power supply, we decided to have some fun and try it with the rest of the antennas. Hooked up to the bow tie, it delivered WNJB’s channel 8 beacon, plus WNYW-44’s ‘5-2’ service (whatever happened to 5-1?) and WWOR-38. Cool!</p>
<p>The FlatWave also benefited from additional amplification, pulling in ten different stations (WNYE-24 was the newcomer). But so did the Mohu Leaf, which snatched eleven different DTV stations, one of which was WNJB on highband VHF channel 8.  The table below summarizes the results for what are the nine strongest DTV station signals that could be received during the test. Each station’s call sign is followed by its physical channel.</p>
<p> </p>
<p><a href="http://www.hdtvexpert.com/?attachment_id=2113" rel="attachment wp-att-2113"><img class="aligncenter size-full wp-image-2113" title="Antenna Test Results 600p" src="http://www.hdtvexpert.com/wp-content/uploads/2012/05/Antenna-Test-Results-600p.jpg" alt="" width="600" height="137" /></a></p>
<p>THE RESULTS</p>
<p>A few solid conclusions came out of this re-test. First, the Mohu Leaf is still a formidable contender, amplified or otherwise.  Even though it doesn’t have much gain at highband VHF frequencies (channels 7-13), it also managed to pull in channel 8 with a boost from the Micron XG amplifier. (I’m still checking on what happened to the Leaf Plus.)</p>
<p>Second, I didn’t see much of a difference between the defective FlatWave and its replacement. True; the 2<sup>nd</sup> model fared somewhat better than its predecessor. But in terms of total stations, it didn’t do any better than the humble bow tie – it just substituted three different stations.</p>
<p>The Micron XG – which we actually wound up taping to the window for the test, using LOTs of masking tape – was a pretty weak performer without its accessory amplifier. However; with the amplifier, it was able to haul in three additional stations. But the Leaf did even better when amplified, capturing a test-high 11 stations reliably, one more than the FlatWave when it had a dance with the external amplifier.</p>
<p>Compiling the ‘yes’ and ‘no’ results into won-lost records, the Mohu Leaf finished in first place at 7-2 competing in the ‘no amplifier’ class, with a three-way tie at 6-3. In the ‘amplified’ division, the Leaf and FlatWave tied with 8-1 records, just head of the 7-2 Micron XG. (I didn’t list the amplified bow tie here, but it finished in 3<sup>rd</sup> place with a 6-3 log.)</p>
<p>How about performance vs. value? The Leaf is currently advertised on the Mohu Web site for $36, while the FlatWave is ticketed at $40. The ClearStream Micron XG will set you back $100 (the unamplified Micron A version is $60), while the humble bow tie is (gasp!) no longer listed on the Radio Shack Web site. (I guess it makes no sense to sell a $4 antenna when you can push a $20 Terk version that looks cooler.)</p>
<p>CONCLUSION</p>
<p>You don’t need to spend a ton of money to get decent DTV reception. In fact, you should be in good shape for no more than $40, based on my tests. If signal levels are really low, the amplified models will make a difference. Based on my tests, I’d suggest sticking with the Leaf Plus, as it is $25 cheaper than the Micron XG – and a lot easier to mount to a variety of surfaces, given how light and flexible it is.</p>
<p>And isn’t it amazing just how well a bare-bones antenna works? Higher cost doesn’t always equal higher performance. Caveat Emptor!</p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>May 29, 2012  4:49 PM</b>
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
			<?=getComments(4827)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4827)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/05/hdtv-expert-useful-gadgets-wallmounted-dtv-antennas-revisited.php" type="text/javascript" charset="utf-8"></script>
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