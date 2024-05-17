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
		AND e.entry_id = 5003";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5003 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5003 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5003";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-ces-2013-from-hype-to-hohum-in-minutes-by-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5003";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &ndash; by Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &ndash; by Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &ndash; by Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &ndash; by Pete Putman</title>
	<meta name="keywords" content="lcd tvs, inch inch, inch oled, days show, oled tvs, inch, tvs, lcd, show, booth, ’s, –, ”, oled, ces, tablet, samsung, new, glass, showed, demo, tablets, back, sharp, still" />
	<meta name="description" content="4K, 3D, tablets, OLEDs, gesture and voice control, and wireless &amp;acirc;�� choose a gadget; everybody was showing it in Las Vegas. And the biggest booths showcased Chinese technological prowess." />
	<meta name="title" content="HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &amp;ndash; by Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &amp;ndash; by Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-ces-2013-from-hype-to-hohum-in-minutes-by-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="4K, 3D, tablets, OLEDs, gesture and voice control, and wireless &amp;acirc;�� choose a gadget; everybody was showing it in Las Vegas. And the biggest booths showcased Chinese technological prowess." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5003', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-ces-2013-from-hype-to-hohum-in-minutes-by-pete-putman.php">HDTV Expert - CES 2013: From Hype to Ho-Hum in Minutes &ndash; by Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January 14, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=514&category=Events & Tradeshows">Events & Tradeshows</a></b>, <b><a href="/category.php?id=442&category=Wireless HDMI/HDTV">Wireless HDMI/HDTV</a></b>
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
          <div id="attachment_2635" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2635" alt="Here we go again ! (Sigh...)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Wide-View-Samsung-Booth-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Here we go again ! (Sigh…)</p></div>
<p>Things are booming in the world of consumer electronics, regardless of the state of the world’s economy. You needed no additional proof beyond the enormous turnout at last week’s International CES, which was in excess of 150,000, according to official press releases. Even if you apply the Kell factor, that’s still a huge turnout – at least 120,000.</p>
<p>I’ve used an easy rule to determine attendance: How long it takes to catch a cab at the end of the first two days of the show. 10 minutes? Light turnout. 20 minutes? Respectable turnout. 40 minutes or more? Now, that’s a crowd!</p>
<p>I spent the equivalent of three full days at the show, scrambling back and forth between strip hotels and the convention center, capturing over 1200 videos and photos along the way. After a while, it all started to blur together. I mean; how many 110-inch TVs do you have to see before the “awe” wears off? How many tablets will you run across before you swear never to touch another one?</p>
<p>This year’s edition of show was characterized by a level playing field across many technologies. No longer do the Japanese and Koreans have an exclusive right to “first to market.” Their neighbors across the sea are now just as technically competent, if not more so.</p>
<div id="attachment_2638" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2638" alt="Hisense's &quot;Big Bertha&quot; uses the same glass as TVs shown by TCL, Samsung, and Westinghouse Digital. " src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Hisense-110-inch-TV-1-WR.jpg" width="600" height="434" /><p class="wp-caption-text">Hisense’s “Big Bertha” uses the same glass as TVs shown by TCL, Samsung, and Westinghouse Digital.</p></div>
<p> </p>
<div id="attachment_2639" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2639" alt="Everybody (and their brother) had an 84-inch 4K TV at the show. (Yawn...)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Skyworth-4K-TV-Demo-WR.jpg" width="600" height="800" /><p class="wp-caption-text">Everybody (and their brother) had an 84-inch 4K TV at the show. (Yawn…)</p></div>
<p>Case in point: The 110-inch 4K LCD TVs shown at CES (I counted four of them, including one in the Samsung booth) all use glass from a Chinese LCD fab known as China Star Optoelectronics Technology, which is a three-year old joint venture between TCL, Samsung, and the local government of Shenzen.</p>
<p>Never heard of them? You will. What’s even more amazing is that their Gen 8.5 LCD fab is (according to an industry insider I spoke to) more efficiently used when cutting two 98-inch LCD panels at the same time. Those are huge cuts, and given China’s predilection for market dominance, we may see rapid price drops in 4K TVs across all sizes by the end of 2013.</p>
<p>Speaking of 4K (UHDTV); everyone had it. And I mean <span style="text-decoration: underline;">everyone</span>! Sony, Panasonic, LG, Samsung, Toshiba, Sharp, Westinghouse, Skyworth, TCL, Hisense, Haier – wait! You never heard of those last four companies? The last three had enormous booths at the show, and Hisense showed five different models of 4K TVs – 50, 58, 65, 84, and 100 inches. That’s more than anyone else had.</p>
<p>In a significant marketing and PR coup, TCL managed to get their 110-inch 4K TV featured in <i>Iron Man III</i>, which debuts in May. That’s the sort of promotional genius that Sony and Panasonic used to pull off. But there are new guys on the block now, and they’re playing for keeps. The steady decline of the Japanese TV industry and continuing financial woes of its major players are all the proof you need.</p>
<div id="attachment_2640" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2640" alt="Sony 56-inch OLED View 1 WR" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Sony-56-inch-OLED-View-1-WR.jpg" width="600" height="318" /><p class="wp-caption-text">So – who was REALLY “first” to show a 4K 56-inch OLED TV? Sony, or…</p></div>
<div id="attachment_2641" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2641" alt="...Panasonic, who also claimed they were the &quot;first?&quot; (Maybe it was a matter of minutes?)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Panasonic-56-inch-OLED-Demo-2-WR.jpg" width="600" height="450" /><p class="wp-caption-text">…Panasonic, who also claimed they were the “first?” (Maybe it was a matter of minutes?)</p></div>
<p> </p>
<p>Interestingly, Sony’s booth signs identified this display as the <i>“world’s first and largest OLED TV.”</i> Puzzling, as it clearly wasn’t the first OLED TV ever shown, and just down the hall, Panasonic was showing its 56-inch OLED TV, the <i>“world’s largest 4K OLED created by printing technology.”</i> Both companies need to get out of their booths more often!</p>
<p>Panasonic, who emphatically renewed their commitment to plasma at CES (despite a continued decline in plasma TV sales worldwide), clearly wanted to show they had a second act ready when plasma eventually bites the bullet. The company is also a major player in IPS LCD, manufacturing LCD TVs in sizes to 65 inches that are every bit as good anything LG cranks out.</p>
<p>Speaking of LG…the heavy emphasis on 3D found in last year’s booth was all but gone this year. Yes, the enormous passive 3DTV wall that greeted visitors at the entrance was still there. And there were a few passive 3D demos scattered throughout the booth. But the more impressive exhibit featured a wall of curved 55-inch OLED TVs. (Why would anyone need a curved TV? You’re probably asking. Well, why would anyone need most of the stuff you see at CES?)</p>
<p>LG also showcased a unique product – a 100” projector screen illuminated by an ultra-short-throw laser projector. LG billed it as the world’s largest wall-mount TV (for now) and it’s known as “Hecto.” The projector uses laser diodes (presumably with DLP technology; that wasn’t mentioned) to illuminate that screen at a distance of just 22 inches.</p>
<div id="attachment_2643" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2643" alt="It's bad enough that LG shows 55-inch OLED TVs we can't buy yet. Now, they have curved OLED Tvs we can't buy yet. (Drool...)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/LG-Wall-of-Curved-OLEDs-WR.jpg" width="600" height="800" /><p class="wp-caption-text">It’s bad enough that LG shows 55-inch OLED TVs we can’t buy yet. Now, they have curved OLED TVs we can’t buy yet. (Drool…)</p></div>
<p> </p>
<div id="attachment_2644" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2644" alt="Got two peole who want to watch two different 3D TV programs at the same time? No problem for Samsung!" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Samsung-Two-channel-3D-TV-demo-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Got two peole who want to watch two different 3D TV programs at the same time? No problem for Samsung!</p></div>
<p>Back down the hall, LG’s neighbor Samsung also showed a 55-inch curved OLED TV (just one) and a couple of company representatives were surprised to hear that LG had a bevy of them. (I repeat my observation about booth personnel who need to get out more.) Samsung did have a clever demo of an OLED TV showing simultaneous 2K programming – simply change a setting on the 3D glasses and you could watch one or the other show. (TI showed this same trick years ago with DLP RPTVs by switching left eye and right information.)</p>
<p>Samsung did have an 85-inch 4K LCD TV that wasn’t duplicated anywhere else on the show floor, and as far as I can tell, it’s a home-grown product. But given the company’s investment in China Star and its shifting emphasis on AM OLED production, I would not be surprised to see Samsung sourcing more of its LCD glass from China in the near future.</p>
<p>Sharp’s booth intrigued me. Here’s a company on the verge of bankruptcy that was showing a full line of new Quattron LCD TVs, along with “Moth Eye” anti-glare first surface glass. Moth Eye glass preserves high contrast and color saturation, but minimizes reflections in a similar way to a moth’s eye; hence the name. Sharp also had impressive demos of flexible OLEDs and a gorgeous 32-inch 4K LCD monitor.</p>
<p>IGZO was also heralded all around the booth. Indium Gallium Zinc Oxide is a new type of semiconductor layer for switching LCD pixels that consumes less power, passes more light, and switches at faster speeds. Many LCD manufacturers (and OLED manufacturers, too) are working on IGZO, but Sharp is closer to the finish line than anyone else – and that may be the salvation of the company, along with an almost-inevitable orderly bankruptcy.</p>
<p>IGZO is why Terry Gou, the chairman of Hon Hai Precision Industries, wants to buy a piece of Sharp – about 10%, to be exact. He’s looking for a source of VA glass for Apple’s tablets and phones (Hon Hai owns Foxconn, who manufactures these products.)  And if Sharp can’t get its financial house in order, he might wind up making a bid for the entire company. <i>(“Never happen!”</i> you say. <i>“The Japanese government wouldn’t allow it.”</i> Well, these are different times we live in, so never say “never!”)</p>
<div id="attachment_2645" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2645" alt="Sharp may not be able to balance their books, but they still know how to manufacture some beautiful displays." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Sharp-4K-IGZO-Demo-WR.jpg" width="600" height="532" /><p class="wp-caption-text">Sharp may not be able to balance their books, but they still know how to manufacture some beautiful displays.</p></div>
<div id="attachment_2646" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2646" alt="It goes without saying that Tony Stark would have a 110-inch TV, right?" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/TCL-110-inch-TV-Iron-Man-Demo-WR.jpg" width="600" height="393" /><p class="wp-caption-text">It goes without saying that Tony Stark would have a 110-inch TV, right?</p></div>
<p>On to the Chinese. They showed 4K, 84-inch and 110-inch LCD glass cuts, gesture recognition, clever LED illumination systems, 3D, smart TVs – basically, everything the Japanese and Koreans were showing. Hisense had a spectacular demo of a transparent 3D LCD TV, along with something called U-LED TV. The explanation of this by the booth representative was so ambiguous that I’ll leave it at an enhanced method of controlling the backlight for improved contrast.</p>
<p>I had heard from an industry colleague that Hisense’s XT880-series 4K TV would have rock-bottom retail prices, but couldn’t confirm this from booth personnel. (Think of $2,000 for a 50-inch 4K TV.) The company’s gesture recognition demo wasn’t nearly as impressive – it’s powered by Israel-based EyeSight – but clearly shows that Hisense is just as far along in refining this feature as anyone else.</p>
<p>TCL had demonstrations of high-contrast 4K TVs with amazingly deep blacks; as good as anything I’ve seen from LG and Samsung. They also had a demonstration of autostereo 3D at the back of their booth, very close to Toshiba (who was showing the same thing). Haier had that now-ubiquitous 4K LCD TV prominently featured in their booth, along with smart TVs and what must have been several dozen tablets. Meanwhile, Skyworth’s booth in the lower south hall showcased yet another 84-inch 4K TV.</p>
<div id="attachment_2650" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2650" alt="RCA's got the first tablet with an integrated ATSC/MH tuner, and it runs Windows 8." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/RCA-ATSC-Tablet-Demo-2-WR.jpg" width="600" height="450" /><p class="wp-caption-text">RCA’s got the first tablet with an integrated ATSC/MH tuner, and it runs Windows 8.</p></div>
<div id="attachment_2651" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2651" alt="TV antennas are passe? NOT!" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Xinxidi-Antenna-Booth-WR.jpg" width="600" height="450" /><p class="wp-caption-text">TV antennas are passe? NOT!</p></div>
<div id="attachment_2652" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2652" alt="Celluon's laser-powered virtual keyboard works on any surface. TI had a pair connected to picoprojectors in their suite." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Laser-Virtual-Keyboard-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Celluon’s laser-powered virtual keyboard works on any surface. TI had a pair connected to picoprojectors in their suite.</p></div>
<p>Vizio’s suite at the Wynn featured 80-inch, 70-inch, and 60-inch LCD TVs using the Sharp Gen 10 glass, and they looked impressive. One version of the 70-inch set is already selling below $2,000, and the 80-incher will come in (for now) at just under $4,500. Vizio also had three new 4K TVs in 55-inch, 65-inch, and 70-inch sizes, but no pricing was announced yet. (Everyone is sitting on their hands waiting for the other guy to price his 4K TVs!)</p>
<p>There was obviously a lot more to CES than televisions. Vizio has a new 11.6” tablet with 1920×1080 resolution that runs Windows 8 with a AMD Z-60 processor. Panasonic showed a prototype 20-inch 4K (3840×2560) tablet using IPS-alpha glass. It also runs Windows 8 with an Intel Corei5 CPU and has multi-touch and stylus input. And RCA had a cool 8-inch tablet (Win 8 OS) that incorporates an ATSC receiver and small antenna. It can play back both conventional 8VSB and MH broadcasts.</p>
<p>Silicon Image had a kit-bashed 7” Kindle tablet running their new UltraGig 6400 60 GHz transmitter, delivering 2K video to a bevy of LCD TVs. They also showed a new image scaling chip to convert 2K to 4K, along with the latest version of InstaPrevue. The latter technology lets you see what’s on any connected HDMI input with I-frame thumbnails of video and still images.</p>
<div id="attachment_2653" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2653" alt="Silicon Image's new UltraGig 6400 TX chip connects this full HD Kindle tablet to an HDTV at 60 GHz." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/SI-Kindle-Tablet-WiHD-Demo-PPT.jpg" width="600" height="800" /><p class="wp-caption-text">Silicon Image’s new UltraGig 6400 TX chip connects this full HD Kindle tablet to an HDTV at 60 GHz.</p></div>
<p> </p>
<div id="attachment_2654" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2654" alt="Conexant's powerful speech processing chips can filter out any background noise while you &quot;command&quot; your smart TV." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Conexant-Voice-Recognition-Enhancement-WR.jpg" width="600" height="426" /><p class="wp-caption-text">Conexant’s powerful speech processing chips can filter out any background noise while you “command” your smart TV.</p></div>
<div id="attachment_2655" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2655" alt="Omek's gesture control demo was easily the most impressive at the show." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Omak-Virtual-Record-Player-MWR.jpg" width="600" height="716" /><p class="wp-caption-text">Omek’s gesture control demo was easily the most impressive at the show.</p></div>
<p>Over in the LV Hotel, Conexant dazzled with a demonstration of adaptive background noise filtering to improve the reliability of voice control systems for televisions. The demo consisted of a nearby loudspeaker playing back an art lecture while commands for TV operation were spoken. A graphical representation showed how effectively the background noise was filtered out completely. The second demo had a Skype conversation running with a TV on in the background and the remote caller walking around the room. I never heard one peep from the TV, and the remote caller was always intelligible.</p>
<p>A few floors down, Omek (yet another Israel-based gesture recognition startup) had perhaps the best demo of gesture control at the show. Their system captures 22 points of reference along your hands, allowing complex gesture control using simple, intuitive finger and wrist movement. (No flailing of arms was necessary). I watched as an operator at a small computer monitor pulled a virtual book from a shelf and flipped through its pages, and also selected a record album, removed the record from its sleeve, and placed it on a virtual turntable. I was even treated to a small marionette show!</p>
<p>At the Renaissance, Prime Sense had numerous exhibits that all revolved around their new, ultra-compact 3D camera design. One demo by Shopperception involved boxes of cereal on a shelf. As you picked one up, the sensors would flash a coupon offer for that cereal to your tablet or phone, or suggest you buy a larger, more economical size instead of two boxes.</p>
<p>Nearby, Covii had one of those “You Are Here” shopping mall locator maps that operated with touchless sensing to expand and provide more detail about any store you were interested in, including sales and promotions. And Matterport had a nifty 3D 360-degree camera that could scan and provide a 3D representation of any room in about one minute. You could then rotate and turn the views in any direction.</p>
<div id="attachment_2658" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2658" alt="Do not - repeat, DO NOT try this at home with your tablet!" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Waterproof-Tablet-Demo-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Do not – repeat, DO NOT try this at home with your tablet!</p></div>
<div id="attachment_2659" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2659" alt="A hybrid low rider? With a 500-watt sound system? Who'd a thunk it?" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Lexus-ct200h-Low-Rider-WR.jpg" width="600" height="450" /><p class="wp-caption-text">A hybrid low rider? With a 500-watt sound system? Who’d a thunk it?</p></div>
<div id="attachment_2662" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2662" alt="Wear this Garmin GPS watch and nobody can ever tell you to &quot;get lost!&quot;" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/TCU-GPS-Watch-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Wear this Garmin GPS watch and nobody can ever tell you to “get lost!”</p></div>
<p>HzO was back with another amazing demo of their WaterBlock waterproofing system. They had a tablet computer sitting in a continuous shower, and also dunked it in a fish tank. Additional demos included dropping smart phones in a bowl of beer and other mysterious liquids. The water infiltrates all spaces but has no effect on operation – you just drip-dry the device once extracted from water. (How do you get rid of the beer smell, though?)</p>
<p>There was an HDMI pavilion at the show, but I was more interested in the goings-on at the DisplayPort exhibit. VESA representatives showed me a single-channel DP connection from a smart phone to a TV for gaming and playing back video, all over a super-thin connecting cable. The powers that be at VESA are also talking about upping the data rates for DisplayPort (currently about 18 Gb/s) to accommodate higher-resolution TVs.</p>
<p>Right now, DP uses an uncompressed data coding method. But there is now discussion of applying a light compression algorithm (tentatively called DisplayStream) that would enable data rates to go much higher – more like 25 Gb/s. (DisplayPort can currently handle 3840×2160 pixels with 10-bit color and a 60-Hz refresh rate.)</p>
<p>I was surprised at the number of devices at the show that support HDMI, and expected more support for DP given its ability to handle higher data rates and its Thunderbolt data layer overlay. It may still be early in the game – the venerable VGA connector is on its way out starting this year, and manufacturers of laptops, tablets, and phones are still debating which digital interface to hitch their horses to.</p>
<div id="attachment_2663" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2663" alt="No, this is not a typical CES attendee. But it's how all of us feel after three days at the show." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Nikon-Mad-Scientist-2-WR.jpg" width="600" height="450" /><p class="wp-caption-text">No, this is not a typical CES attendee. But it’s how all of us feel after three days at the show.</p></div>
<div id="attachment_2664" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2664" alt="Panasonic's 20-inch 4K offering is the Rolls-Royce of tablets. (So who needs a notebook!)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Panasonic-20-inch-Tablet-demo-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Panasonic’s 20-inch 4K offering is the Rolls-Royce of tablets. (So who needs a notebook!)</p></div>
<p> </p>
<div id="attachment_2665" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2665" alt="Suffice it to say that this was a VERY popular booth at CES..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Inada-Massag-Chair-Area-WR.jpg" width="600" height="450" /><p class="wp-caption-text">Suffice it to say that this was a VERY popular booth at CES…</p></div>
<div id="attachment_2667" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2667" alt="...as was this one. Sealy lets you control your mattress settings from your iPad. (Hey, it's CES!)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Sealy-iRest-Booth-WR.jpg" width="600" height="450" /><p class="wp-caption-text">…as was this one. Sealy lets you control your mattress settings from your iPad. (Hey, it’s CES!)</p></div>
<p>Let’s wrap things up with a discussion of ultrabooks. Intel’s booth prominently featured a full line of these next-gen notebooks, although several of the models on display weren’t nearly as thin as I’d expect an ultrabook to be. Shipments of “ultras” in 2012 were only about half of what was forecast.</p>
<p>The reason? Tablets. Vizio’s new tablet is one of the larger models at nearly 12 inches, but Panasonic showed you can go even larger and make it work. At that point, why would you need a notebook? I left mine at home this time and used a Nook HD+ instead. Fitted with a Bluetooth keyboard and mouse, and loaded with Office-compatible programs, it did everything I needed it to do while in Vegas.</p>
<p>Needless to say, the Intel booth representative wasn’t too happy when I pointed this out to him. But that’s the thing about CES: There’s always some other guy at the show that has the same or better product than you. There’s always a better mousetrap or waffle-maker lurking in the South Hall. Very few companies have much of an edge in technology these days (the Chinese brands proved that in spades), and so many of these “wow, gotta have it!” items become commodities in rapid order.</p>
<p>The plethora of 4K and ultra-large LCD TVs found at CES proved this conclusively, as they went from hype to ho-hum in a matter of minutes. So did tablets, smart phones, and other connectivity gadgets. What CES 2013 was really about was the shift in manufacturing prowess and power to China from Japan and Korea; a shift that will only accelerate with time. And that is definitely NOT ho-hum!</p>
<p><i>Editor’s note: Many thanks and a tip of the hat to Nikon booth personnel, who were apparently charging and swapping out batteries for journalists who (like me) inadvertently ran out of power during the show. They saved me more than once! </i></p>
<p><i> </i></p>
<div id="attachment_2669" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2669" alt="Marilyn says, &quot;Gentlemen prefer 4K 3D curved wireless multi-touch OLED IGZO cloud-based voice controlled tablets!&quot; (See you next year...)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/01/Marilyn-Look-Alike-WR.jpg" width="600" height="800" /><p class="wp-caption-text">Marilyn says, “Gentlemen prefer 4K 3D curved wireless multi-touch OLED IGZO cloud-based voice controlled tablets!” (See you next year…)</p></div>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January 14, 2013 11:36 AM</b>
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
			<?=getComments(5003)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5003)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-ces-2013-from-hype-to-hohum-in-minutes-by-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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