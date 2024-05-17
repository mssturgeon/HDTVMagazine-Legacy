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
		AND e.entry_id = 1381";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1381 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1381 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1381";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/05/new-pioneer-elite-av-receivers-deliver-unprecedented-sound-experiences-to-the-home-theater.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1381";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater" />
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
	<title>HDTV Magazine - New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater</title>
	<meta name="keywords" content="vsx txh, surround sound, home theater, blu ray, elite receivers, pioneer, home, sound, receivers, elite, new, audio, digital, high, full, txh, theater, video, vsx, surround, blu, ray, resolution, receiver, experience" />
	<meta name="description" content="Pioneer Electronics (USA) Inc. today introduces four new A/V receivers delivering high-definition (HD) audio and video experiences for emerging home theaters - the Elite&amp;reg; SC-07, SC-05, VSX-03TXH and VSX-01TXH. Acting as the HD control center, Pioneer&amp;reg;'s full line of A/V receivers work seamlessly with Pioneer's 2008 KURO displays and Blu-ray Disc&amp;reg; players to deliver the ultimate HD picture and sound performance for a truly emotional response in the living room.

Pioneer's flagship A/V receiver, code named &quot;Susano,&quot; has set the industry standard for home theater performance and its 2008 top-of-the-line receivers, the SC-07 and SC-05, continue to incorporate..." />
	<meta name="title" content="New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/05/new-pioneer-elite-av-receivers-deliver-unprecedented-sound-experiences-to-the-home-theater.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Pioneer Electronics (USA) Inc. today introduces four new A/V receivers delivering high-definition (HD) audio and video experiences for emerging home theaters - the Elite&amp;reg; SC-07, SC-05, VSX-03TXH and VSX-01TXH. Acting as the HD control center, Pioneer&amp;reg;'s full line of A/V receivers work seamlessly with Pioneer's 2008 KURO displays and Blu-ray Disc&amp;reg; players to deliver the ultimate HD picture and sound performance for a truly emotional response in the living room.

Pioneer's flagship A/V receiver, code named &quot;Susano,&quot; has set the industry standard for home theater performance and its 2008 top-of-the-line receivers, the SC-07 and SC-05, continue to incorporate..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1381', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/05/new-pioneer-elite-av-receivers-deliver-unprecedented-sound-experiences-to-the-home-theater.php">New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>May  7, 2008</b>
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
				<p class="prtitle">New Pioneer Elite A/V Receivers Deliver Unprecedented Sound Experiences to the Home Theater</p>

<center><i>Designed Specifically to Complement 2008 KURO Displays and Blu-ray Disc Players, Pioneer Elite Receivers Boast Revolutionary Amplifier Technology</i></center><br />
<br />

<p><B>NEW YORK--(BUSINESS WIRE)</B>--Pioneer Electronics (USA) Inc. today introduces four new A/V receivers delivering high-definition (HD) audio and video experiences for emerging home theaters - the Elite&reg; SC-07, SC-05, VSX-03TXH and VSX-01TXH. Acting as the HD control center, Pioneer&reg;'s full line of A/V receivers work seamlessly with Pioneer's 2008 KURO displays and Blu-ray Disc&reg; players to deliver the ultimate HD picture and sound performance for a truly emotional response in the living room.</p>

<p>Pioneer's flagship A/V receiver, code named "Susano," has set the industry standard for home theater performance and its 2008 top-of-the-line receivers, the SC-07 and SC-05, continue to incorporate the Direct Energy HD Amplifier with ICEpower&trade; analog class D amplification technology to produce a level of multi-channel power output, fidelity, and efficiency never before seen or heard in a home A/V receiver. Supported by the Company's legacy sound-tuning technology, the SC-07 and SC-05 were designed from the ground up to produce a 3-dimensional aural experience that will take listeners to the next level of high resolution multichannel surround sound in the entertainment room.</p>

<p>The Elite VSX-03TXH and VSX-01TXH join Pioneer's leading receiver from 2007 - the VSX-94TXH - to round out the entire 2008 offering. All three models flawlessly pass a 1080p picture with full reproduction capabilities of all new high resolution audio formats.</p>

<p>"Our flagship A/V receiver, the Susano, represents the pinnacle of high-end design and engineering in home theater equipment. We are now delivering that premium experience into our core line of Elite A/V receivers," said David Bales, audio marketing manager for the home entertainment division of Pioneer Electronics (USA) Inc. "The new Direct Energy HD amplifier with ICEpower technology was integral for us to provide the full impact of new uncompressed audio soundtracks now available on Blu-ray Discs. Just as HDTV and Blu-ray have brought new levels of high definition video performance to consumers, the new high resolution surround sound formats coupled with our expertise in multi-channel amplifier design truly deliver a high-definition, multi-channel A/V experience far beyond anything we have produced before."</p>

<p><br />
<B>Built for High Definition Surround Sound</B></p>

<p>The SC-07 and SC-05 in Pioneer's line of Elite receivers deliver robust, realistic sound quality as a result of the exclusive Direct Energy HD Amplifier. Pioneer combines its industry-leading amplifier design and sound tuning technologies with the most advanced digital signal processing (DSP) to produce high resolution surround sound at unprecedented efficiency levels. Now the receiver can operate at extraordinary output levels with extremely low distortion and virtually no wasted energy; all in an effort to develop the most advanced home theater amplifier to date.</p>

<p>Entertainment enthusiasts can take advantage of internal decoding of new advanced lossless surround sound formats - DTS-HD&trade; Master Audio, DTS-HD High Resolution, Dolby&reg; TrueHD and Dolby&reg; Digital Plus - to finally experience the full impact these high resolution audio codecs deliver to the viewing experience with ultra-rich surround sound that complements, and completes, on-screen imagery.</p>

<p>For accurate, natural surround sound reproduction of all connected HD and SD devices, Pioneer employs its exclusive room tuning feature in all four new Elite receivers. Advanced MCACC ensures studio quality surround sound regardless of the room configuration. This feature makes subtle adjustments to optimize the audio experience for even the most discerning ear. In addition, Pioneer's Elite SC-07, SC-05 and VSX-94TXH incorporate the exclusive Full Band Phase Control, which eliminates "phase lag" or group delay between all speakers in a home theater system. This innovative DSP maintains soundtrack synchronization, ensuring the most accurate multi-channel sound reproduction is achieved and heard. All models include new symmetric equalization (EQ) for the most precise speaker calibration.</p>

<p>Pioneer's new Elite receivers (SC-07, SC-05 and VSX-94TXH) include Neural-THX&trade; allowing for encoded content to be delivered in a two-channel stereo format and decoded for dispersion among up to 7.1 channel surround sound. In addition, the full line of Elite receivers includes dialogue enhancements, a wide range of listening modes, Mid-night Listening and Lip-Sync A/V synchronization. For the ultimate digital signal processing, all Elite receivers utilize anti-jitter technologies. The SC-07 is equipped with a professional level Burr Brown Sampling Rate Converter (SRC) to scale all digital audio signals up to 192 kHz 24-bit resolution.</p>

<p><br />
<B>HD Digital Connectivity</B></p>

<p>Pioneer brings HDMI 1.3a with full support of 12-Bit Deep Color to deliver a stellar picture from connected sources with a range of hues and shades not previously possible. In addition, for analog video sources, Pioneer's Elite receivers are engineered for 1080p video processing with a Pioneer digital video converter and Faroudja video scaler chip that ensures up to full 1080p resolutions to best match the incoming video signal to the native resolution of a connected display.</p>

<p><br />
<B>Pioneer Audio Synergy</B></p>

<p>Pioneer's engineers developed unique settings in its A/V receivers to harmonize audio performance like never before. Pioneer created jitter-free playback and dynamic sound quality for music CDs. When connected to a new Blu-ray Disc player via HDMI, Pioneer's SC-07 and SC-05 receivers utilize a proprietary Precision Quartz Locking System (PQLS) that synchronizes data between the Blu-ray Disc player and the receiver, providing listeners with the ultimate precision in CD playback.</p>

<p><br />
<B>Networking and Consumer Convenience Features</B></p>

<p><B>Home Media Gallery</B></p>

<p>The SC-07 and SC-05 enhance the home theater experience with the exclusive Home Media Gallery. Pioneer's home networking feature enables users to select and playback personal digital music and JPEG photo files direct from a PC hard drive or USB for playback in the living room through the main HD home theater system.</p>

<p>Through the receiver, users can easily access and stream digital media files directly from a connected home PC or laptop computer with IP networking capability. Home Media Gallery is compliant with Digital Living Network Alliance (DLNA), Windows Vista or Windows Media Connect as well as Microsoft playsforsure&trade; DRM technology.</p>

<p>Additional Connectivity Options</p>

<p>Pioneer continues to provide full-feature entertainment connectivity with Advanced Digital iPod&reg; USB for pure digital audio fidelity. It allows users to navigate and select personalized music playlists from their iPods. Users can control these digital components player with the receiver's remote control and on-screen display.</p>

<p>The proliferation of portable music devices has resulted in reduced audio file sizes that have negatively affected the reproduction quality of audio entertainment. Recognizing this, Pioneer employs its proprietary Sound Retriever DSP technology, which works by "filling in" and compensating for the audio data removed from the compressed files (WMA, MP3, MPEG-4 AAC) for music playback that is near CD quality.</p>

<p><br />
<B>Full Custom-Installation Ready</B></p>

<p>Pioneer's full of Elite A/V Receivers are ready for custom installation with the following features:</p>

<p> * Multi-zone, multi-source capabilities allow Pioneer's Elite receivers to serve as the entertainment centerpiece of up to 3 A/V zones in the home<br />
 * The SC-07 has dual HDMI video output and second zone component video output for zone 2 HD video capabilities<br />
 * RS232 Port for PC and 3rd party custom control and connectivity<br />
 * Advanced Direct Construction ensures enhanced reliability and performance<br />
 * New product cosmetics synchronizes with design of the KURO displays and Blu-ray Disc players</p>

<p>The VSX-01TXH and VSX-03TXH will be available in June for a suggested price of $750 and $1,000, respectively. The SC-05 and SC-07 will be available in August for a suggested price of $1,800 and $2,200, respectively. The VSX-94TXH is currently available for $1,600.</p>

<p>Pioneer's Home Entertainment and Business Solutions Group develops high definition home theater equipment for discerning entertainment junkies. Its flat panel televisions, Blu-ray Disc players, A/V receivers and speakers bring a new level of emotion to the HD experience. The company brands include Pioneer and Elite&reg;. When purchased from an authorized retailer, consumers receive a limited warranty for one year with Pioneer products and two years with Pioneer Elite products. More details can be located at www.pioneerelectronics.com.</p>

<p>PIONEER, the PIONEER logo and the ELITE logo are registered trademarks of the Pioneer Corporation.</p>

<p>DOLBY and the double-D symbol are registered trademarks of Dolby Laboratories.</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing LLC.</p>

<p>DTS and DTS Digital Surround are registered trademarks of Digital Theater Systems, Inc.</p>

<p>Microsoft and Windows Media are trademarks or registered trademarks of Microsoft Corporation.</p>

<p>THX is a trademark of THX Ltd. which may be registered in some jurisdictions. All rights reserved.</p>

<p>iPod is a trademark of Apple Computer, Inc., registered in the U.S. and other countries.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>May  7, 2008  2:36 PM</b>
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
			<?=getComments(1381)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1381)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/05/new-pioneer-elite-av-receivers-deliver-unprecedented-sound-experiences-to-the-home-theater.php" type="text/javascript" charset="utf-8"></script>
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