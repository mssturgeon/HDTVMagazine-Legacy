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
		AND e.entry_id = 4845";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4845 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4845 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4845";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/06/hdtv-expert-infocomm-2012-growth-and-reinvention-by-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4845";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman</title>
	<meta name="keywords" content="las vegas, trade show, cheaper lcd, lcd displays, conference room, –, show, lcd, inch, infocomm, year, lumens, projector, wireless, both, walls, projection, samsung, i’m, see, technology, control, panasonic, attendees, ”" />
	<meta name="description" content="The commercial AV industries&amp;acirc;�� largest trade show shows no signs of slowing down. But the pro AV landscape is definitely evolving." />
	<meta name="title" content="HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/06/hdtv-expert-infocomm-2012-growth-and-reinvention-by-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The commercial AV industries&amp;acirc;�� largest trade show shows no signs of slowing down. But the pro AV landscape is definitely evolving." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4845', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/06/hdtv-expert-infocomm-2012-growth-and-reinvention-by-pete-putman.php">HDTV Expert - InfoComm 2012: Growth and Re-Invention, by Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>June 20, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=304&category=General Interest">General Interest</a></b>
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
          <p>As InfoComm 2012 recedes into the rear-view mirror (along with Las Vegas, thankfully), I’ve had a chance to think about some of the more significant trends I spotted at the show. Some have been picking up speed for almost a year, while the others are still moving in fits and starts.</p>
<p> </p>
<p>Amazingly, the show has managed to glide smoothly over every potential speed bump it has hit in the past 15 years (the demise of the Projection Shoot-Out, the 2007-2009 recession, collapsing retail prices and dealer margins on hardware, consolidation of brand names, and infiltration of consumer electronics into the professional space).</p>
<div id="attachment_2155" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2155" rel="attachment wp-att-2155"><img class="size-full wp-image-2155" title="Prysm LPD Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Prysm-LPD-Demo-MR1.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Prysm's laser-phosphor displays didn't generate quite as much 'buzz' this year. Maybe the large LCDs lurking nearby had something to do with it?</p></div>
<p> </p>
<p>InfoComm absorbed its nearest competitor (the National Systems Contractor Association’s trade show) a few years back. It has expanded to Asia and Europe. Its education and certification program is second to none, with over 8,000 holders of Certified Technology Specialist (CTS) certificates out there – I’m one of ‘em – and ISO certification of their education process.</p>
<p> </p>
<p>I started attending InfoComm in 1994 as a journalist. Over the years, I’ve become more intimate with the education side of things, and now about 60% – 70% of my time at the show is taken up with teaching classes. This year alone, I had nine hours of individual instruction to offer to a total of over 750 students during a three-day period. (And I once swore I would never be a teacher. Ha!)</p>
<p> </p>
<p>In fact, class attendance this year was the highest I’ve ever seen it, and the attendees were predominantly end-users – colleges, hospitals, institutions, corporations, non-profits, churches, and government agencies. The transition from analog to digital has swept everyone up in its wake, and InfoComm attendees don’t want to be left behind.</p>
<p> </p>
<p>As a result, I didn’t have a lot of time to walk the trade show floor. But the significant products were out there, if you knew where to look. I even managed to feature a few of them in my classes – I’m VERY big on ‘show and tell,’ rather than ‘death by Powerpoint’ – so that attendees could get more information on the hardware and software than they’d find in the average booth tour.</p>
<div id="attachment_2156" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2156" rel="attachment wp-att-2156"><img class="size-full wp-image-2156" title="Sharp 90-inch LCD SQ MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Sharp-90-inch-LCD-SQ-MR1.jpg" alt="" width="600" height="728" /></a><p class="wp-caption-text">Sorry - there's just no way to fit this thing into a horizontal photo, it's just too darn big!</p></div>
<p> </p>
<p>The first trend is <strong>ever-larger and cheaper LCD displays</strong>. You may have heard that Sharp unveiled a 90-inch professional LCD monitor in Las Vegas (1920×1080, no price yet, but probably under $10K) and followed that up with the announcement of the TV version (LC-90LE745U, $10,999) on June 19.</p>
<p> </p>
<p><span style="text-decoration: underline;">Don’t</span> underestimate the significance of this product. Since its introduction last fall, Sharp’s $5K 80-inch LCD TV product has proven to wildly successful, but not necessarily in the home: No, AV dealers are installing them by the truckload in commercial AV projects, with a special emphasis on financial institutions and corporations who don’t want a two-piece projector/screen ‘solution’ that requires frequent lamp changes, filter maintenance, and ambient light control.</p>
<div id="attachment_2157" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2157" rel="attachment wp-att-2157"><img class="size-full wp-image-2157" title="Samsung 75-inch E-LED Display MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Samsung-75-inch-E-LED-Display-MR1.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Samsung is ready to play as well with their 75-inch edge-lit LCD monitor.</p></div>
<div id="attachment_2160" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2160" rel="attachment wp-att-2160"><img class="size-full wp-image-2160" title="Panasonic Waterproof LCD Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Panasonic-Waterproof-LCD-Demo-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">And for those of you who like watching TV underwater, Panasonic's got the solution!</p></div>
<p> </p>
<p>If the 80-inch is a projector ‘threat,’ then the 90-inch is a projector ‘killer.’ Maybe not at $10K, but you know that price will come down quickly as market demand rises – and it will rise – so expect it to be selling for $7,000 – $8,000 before very long.</p>
<p> </p>
<p>You’ll know this trend has <span style="text-decoration: underline;">really</span> picked up speed when Sharp’s nearest competitors (Samsung and LG) start pushing their big LCD screens aggressively. Samsung showed a 75-inch edge-lit LCD display at the show with the ominous caption: “Time to Replace Projector in Your Conference Room.”</p>
<p> </p>
<p>Another trend is <strong>‘ergonomic’ control systems</strong>. At CES, there were numerous demonstrations of gesture and voice control, and Samsung has already brought a TV to market (ES7500 series) that combines both with facial recognition. I didn’t see too many demos of either in Las Vegas, but Panasonic had an interesting demo that combined body recognition with gesture control to navigate a series of maps and locate yourself on a virtual campus.</p>
<div id="attachment_2161" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2161" rel="attachment wp-att-2161"><img class="size-full wp-image-2161" title="Panasonic Gesture Recognition Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Panasonic-Gesture-Recognition-Demo-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Look, Ma - both hands!</p></div>
<p> </p>
<p>The challenges to design such systems are clearly outweighed by the advantages. A conference room or classroom that can recognize a user, power itself up, and load and operate any preferences in hardware and software operation is a very attractive proposition. No doubt we’ll see some more stabs at this built around the Leap platform in the near future (Leap can detect hand motion as slight as .1 millimeters).</p>
<p> </p>
<p><strong>Wireless connectivity</strong> goes hand-in-hand with gesture and voice commands, and I’m not talking about WiFi-based solutions – they are generally the most unreliable choice, although abundant. No, I’m referring to a slew of proprietary technologies that run on separate but parallel highways to WiFi, free of bandwidth-hogging TCP/IP traffic.</p>
<div id="attachment_2164" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2164" rel="attachment wp-att-2164"><img class="size-full wp-image-2164" title="Hitachi WHDI Video Switch and Document Camera MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Hitachi-WHDI-Video-Switch-and-Document-Camera-MR.jpg" alt="" width="600" height="364" /></a><p class="wp-caption-text">Here's a plug for getting un-plugged...from Hitachi.</p></div>
<p> </p>
<p>Right now, the most promising of these is the Wireless High-Definition Interface (WHDI), which operates at 5.8 GHz, has a range of several hundred feet, and can support dozens of discrete channels that carry 1920x1080p/60 video, multichannel audio, and data. Hitachi showed a six-port (two HDMI &amp; two VGA) wireless projector switch at InfoComm, along with a super-tiny document camera that also has WHDI built-in.</p>
<p> </p>
<p>During my Wireless AV class, we treated attendees to the first public demonstration of WiSA – a multi-channel (7.2) wireless audio system that requires nothing more than AC power for each speaker. The room size was 50’ wide, and the technology is scalable to larger rooms. Combined with a WHDI connection to the Blu-ray player and my Toshiba computer, we were able to cut just about every cord (except for power).</p>
<p> </p>
<p>Projector manufacturers are well aware of the challenges posed by ever-larger and cheaper LCD displays. One way to fight back is to move away from traditional short-arc mercury vapor lamps to <strong>lampless projection engines</strong> employing LEDs, lasers, or both.</p>
<div id="attachment_2165" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2165" rel="attachment wp-att-2165"><img class="size-full wp-image-2165" title="BenQ Laser Projector MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/BenQ-Laser-Projector-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">At BenQ, it's all done with lasers.</p></div>
<div id="attachment_2166" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2166" rel="attachment wp-att-2166"><img class="size-full wp-image-2166" title="Casio Projector Lineup MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Casio-Projector-Lineup-MR.jpg" alt="" width="600" height="800" /></a><p class="wp-caption-text">And at Casio, some of it is done with lasers.</p></div>
<p> </p>
<p>Casio took a substantial lead in this market a few years back with its laser/LED hybrids, and finally plugged a hole in its line with the XJ-H2650, a wide XGA (1280×800) design with 3500 ANSI lumens brightness that made its debut at InfoComm. Now, BenQ has joined the fray with a pair of laser-only single-chip DLP projectors, both rated at 2,000 lumens (LX60ST, XGA, and LW61ST, WXGA).</p>
<p> </p>
<p>But the bigger news came from Panasonic, who not only embraced hybrid technology but jumped all the way to 1920×1080 resolution while doing it. They’re rolling out two different versions – one for education, and one for commercial applications – and the PT-RZ470 is claimed to develop in excess of 3,000 lumens. There is a wide XGA version as well, known as the PT-RW430, and it’s also rated over 3,000 lumens. Both BenQ and Panasonic claim you’ll see about 20,000 hours of operation from the laser/LED light engine before it poops out.</p>
<p> </p>
<p>Other companies showed ‘lampless’ projection technology at the show, including Optoma. But most of these demos were small, pocket-sized projectors that are good for a few hundred lumens at most. Digital Projection and projectiondesign also showcased LED-only offerings that can hit the 1,000 lumens barrier, but we still haven’t seen a ‘pure’ LED design that can beat the 2,000 lumens benchmark…for now.</p>
<p> </p>
<p><strong>Haptic control technology</strong> – i.e. touchscreen LCDs – was in abundance at the show. Samsung showed a demonstration of a large LCD touchscreen table that can be used to display images of retail merchandise. These images can then be ‘dragged’ onto a Windows 8-equipped smart phone and create a shopping cart, or even a checklist. Whatever is dragged into the smart phone is automatically mirrored to a nearby sales associate tablet, supposedly simplifying the shopping process for both parties.</p>
<p> </p>
<div id="attachment_2167" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2167" rel="attachment wp-att-2167"><img class="size-full wp-image-2167" title="Samsung Microsoft Haptic Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Samsung-Microsoft-Haptic-Demo-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">There's probably a cool table hockey demo lurking somewhere in here...</p></div>
<div id="attachment_2171" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2171" rel="attachment wp-att-2171"><img class="size-full wp-image-2171" title="Wide View of Stage MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Wide-View-of-Stage-MR1.jpg" alt="" width="600" height="342" /></a><p class="wp-caption-text">136 60-inch monitors in five walls. You can count 'em.</p></div>
<div id="attachment_2177" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2177" rel="attachment wp-att-2177"><img class="size-full wp-image-2177" title="Lead Actors against Desert Image MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Lead-Actors-against-Desert-Image-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">And here's the videowall in action during the musical.</p></div>
<p> </p>
<p>One of the more impressive demonstrations took place at Planet Hollywood, where a new musical was finishing up rehearsals. Based on songs by the Beach Boys (who are celebrating their 50<sup>th</sup> year with a nationwide tour) , ‘<em>Surf: The Musical’</em> uses five walls of 60-inch Sharp LCD monitors for all of its scenic backdrops. The walls were designed and built by Adaptive Technologies and can slide in and out and raise/lower during the performance as needed to accommodate some real 3D constructed sets.</p>
<p> </p>
<p>Each wall weighs about 8,000 pounds and it took some experimentation to figure out an adequate damping system to raise and lower the walls without any bouncing. Dynamic video processing keeps the displayed images static as the walls move up and down, creating the illusion of a curtain. If you get a chance to see the show, you will be impressed with the Ferris Wheel sequence – it felt real to me.</p>
<p> </p>
<p>I can’t wrap up this piece by mentioning the absence of one of InfoComm’s largest members and long-time exhibitors, Extron Electronics. You’ve probably heard numerous reasons why they opted to skip the show (none of which made any sense to me, particularly since Extron did participate at NAB in April). Extron is a nearly 30-year-old bellweather interfacing company and without them, the Projection Shoot-Out wouldn’t have been possible.  (Neither would the annual Extron Bash party, now R.I.P.)</p>
<div id="attachment_2172" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2172" rel="attachment wp-att-2172"><img class="size-full wp-image-2172" title="Kramer Booth MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/06/Kramer-Booth-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Kramer erected a new booth to showcase their CORE digital products.</p></div>
<p> </p>
<p>Suffice it to say that there was plenty of chatter and speculation in my classes about Extron’s absence, and apparently more than a few delighted competitors who stayed the course and reported strong booth attendance on the show floor. The enormous turnout for any classes that had the words “EDID,” “HDCP,” “HDMI,” or “digital video” in their titles and/or descriptions apparently also meant a tide of visitors to booths showing those products. Missed opportunity? Definitely.</p>
<p> </p>
<p>So, there you have it – a quick fly-by of InfoComm. Next year, I’m going to try more ambitious wireless demos (including some products I just found out about at the show) and will expand my digital video curriculum with Web-connected TVs, if everything works out. Try and make it, we’ll be in Orlando a year from now. Should be fun!</p>
<p> </p>
<p>See you there?</p>
<p> </p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>June 20, 2012  2:02 PM</b>
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
			<?=getComments(4845)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4845)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/06/hdtv-expert-infocomm-2012-growth-and-reinvention-by-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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