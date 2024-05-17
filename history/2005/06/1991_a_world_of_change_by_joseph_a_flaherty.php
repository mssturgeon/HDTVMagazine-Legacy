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
		AND e.entry_id = 129";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 129 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 129 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 129";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1991_a_world_of_change_by_joseph_a_flaherty.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (5) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 129";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1991 - A World Of Change By Joseph A. Flaherty" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1991 - A World Of Change By Joseph A. Flaherty" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1991 - A World Of Change By Joseph A. Flaherty" />
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
	<title>HDTV Magazine - 1991 - A World Of Change By Joseph A. Flaherty</title>
	<meta name="keywords" content="high definition, prime time, production program, program exchange, program production, high, definition, production, programs, television, program, hdtv, transmission, electronic, film, system, europe, worldwide, today, world, distribution, digital, time, standard, percent" />
	<meta name="description" content="By Joseph A. Flaherty CBS, Inc. High definition television is the latest major advance in world telecommunications. Changes in technology, changes in culture, changes in our perception of the world, all taking place more rapidly with every passing day, are..." />
	<meta name="title" content="1991 - A World Of Change By Joseph A. Flaherty" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1991 - A World Of Change By Joseph A. Flaherty" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1991_a_world_of_change_by_joseph_a_flaherty.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="By Joseph A. Flaherty CBS, Inc. High definition television is the latest major advance in world telecommunications. Changes in technology, changes in culture, changes in our perception of the world, all taking place more rapidly with every passing day, are..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=129', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1991_a_world_of_change_by_joseph_a_flaherty.php">1991 - A World Of Change By Joseph A. Flaherty</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 26, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p>By Joseph A. Flaherty<br />
CBS, Inc.</p>

<p>High definition television is the latest major advance in world telecommunications. Changes in technology, changes in culture, changes in our perception of the world, all taking place more rapidly with every passing day, are the hallmark of our times and make the prediction of the future almost impossible. Certainly the future will look nothing like the past—it will look nothing like the present—and it will look nothing like what we today think the future will look like!</p>

<p>Nevertheless, we find in human nature a widespread aversion to, and fear of change, the necessary precursor of progress. This led the historian Elting Morrison to suggest: "It is possible, if one sets aside the long-run social benefits, to look upon invention as a hostile act—a dislocation of existing schemes, a way of disturbing the comfortable bourgeois routines and calculations."</p>

<p>This in turn led Secretary Adams to write in Smithsonian Horizons "There are some obvious lessons in all this. Inventions, especially visionary ones that disturb established patterns, rarely diffuse rapidly and successfully of their own accord...Centralized, bureaucratic managers and industrial giants are alike in too often being obstacles to, rather than sources of, broader perspectives. Enmeshed in present constraints, higher echelons fail to notice that most future problems and solutions lie outside of their sphere of influence."</p>

<p>Today we are addressing the next major problem in television—the change to high definition television. In this, there are two different aspects: the production, or making of programs and their worldwide distribution, and the transmission, or delivery of programs to the home viewer. Work on both program production and transmission is proceeding in Europe, Japan, and North America in different ways. In this article, I will cover both HDTV program production and program transmission, emphasizing the regional differences in approach, and the real need for global commonality.</p>

<p>Managing and directing change in technology can be achieved by setting certain goals and establishing certain standards, and thus bringing to our global village the benefits of this new technology. Standards have always been important to progress and to the development of a stable marketplace. If electronic HDTV program production and program exchange is to prosper, standards must make the exchange and distribution of programs worldwide technically simple and economically viable.</p>

<p>There are two ways to accomplish this. Either we need a single worldwide standard for high definition studio production and program exchange, or we need transparent and cost-effective high definition-to-high definition standards converters. Today we have neither, and HDTV electronic productions cannot be distributed worldwide! In fact, today we do not even have a transparent standards converter for converting 625-line signals to 525-line signals and vice versa. After 30 years of work, all converters including the latest designs, still produce serious artifacts in the conversion process.</p>

<p>Today, the only worldwide standard for high definition program production and program exchange is 35mm film, notwithstanding its poor motion portrayal (the wheels still go backwards). Nevertheless we muddle through, and 35mm film dominates the world's program exchange marketplace.</p>

<p>In the U.S., up to 90 percent of all prime time evening programs for all the commercial television networks have for 40 years been produced in high definition 35 mm film. This notwithstanding, we have never delivered a single frame of high definition to the home viewer. Consider for a moment the production of prime time programs for television broadcast. By prime time, we refer to the three to four hours in the evening when the largest audience is viewing, and when the programs of greatest appeal are presented. Figure 1 shows the types of programs involved. Series drama, such as "Dallas," occupies 50 percent of prime time, followed by telefeatures and feature films. Situation comedies, for the most part shot on electronic television cameras, are of course broadcast in the electronic medium. Only the 15 percent segment of news and sports programs is broadcast live.</p>

<p>With this massive base, the Hollywood film industry now provides 85 percent of the world's exports of cinema and television programs, for television, cable, cinema, DBS, and home video. In fact, program exports account for 36 percent of the revenues returned to the U.S. motion picture and television program producers, or $5 billion annually. These revenues are the second largest export of the United States, after defense products (Figure 2).</p>

<p>Figure 3 shows that the revenues from motion picture cinema distribution have remained nearly flat, in constant dollars, for some years, while the income from pay cable and home video, taken together, equalled the cinema revenues in 1985, and continues to grow. Today, distribution to the electronic media returns more revenue to the production studios than does the cinema box office. The trend continues and is expected to continue with the growth of DBS, cable, and private television worldwide.</p>

<p>Further, emphasizing the importance of this fact, Figure 4 analyzes all the sources of revenue for U.S. studios. The decline in the percentage of total revenues derived from the cinema is shown in the left hand column for each year. In the right hand column, home video alone now provides the biggest single source of revenue, followed by domestic television, foreign syndication television, and pay cable. Here note that the right hand column consists entirely of electronic distribution media, compared with the 24 percent of revenue provided by the last mechanical distribution medium—the cinema.</p>

<p>Figure 5 shows that the steady growth in exports derives entirely from the electronic distribution media. Over the last five years, these electronic program exports have grown at a rate of 55 percent annually.</p>

<p>The engine that produces these programs is large (Figure 6). Today Hollywood alone produces over 6000 programs annually, comprising 8000 hours of program time. Production time for this output is 36,000 days per year and occupies 210 stages, almost all of it in high definition. In Europe, the situation is entirely different. No large and viable indigenous film industry exists for the production of television programs. Thus, if 35mm film continues as the world's only standard for production and program exchange, it will assure the continued dominance of Hollywood in programs worldwide.</p>

<p>To compete in Europe, a massive investment in new high definition studios would be required before all the distribution channels could be filled with indigenous product. I believe in 1991 it is too late, and ill-advised, to try to duplicate such a monster 35mm television film production operation in Europe. Europe's strength, on the other hand, has been in electronic production for television, and its future strength will be in high definition electronic production—not film for television.</p>

<p>Moreover, as shown in Figure 7, the high and escalating cost of program production, where each episode of a serial drama, like "Dynasty," costs $1 million to $1.5 million, and a season's production of prime time programs can cost each network $400 to $600 million, demands international co-productions and worldwide marketing, to achieve a return on the production investment. This worldwide market has been the formula for Hollywood's success. For international co-production to succeed, programs must be saleable worldwide, and once again in electronic HDTV this can only be achieved by a single standard for production and program exchange, or by a transparent and cost-efficient standards converter.</p>

<p>Little, if any, progress is being made in either of these issues today. Lacking a solution, the creative community in electronic HDTV will be disadvantaged and its work relegated to a ghetto. On the other hand, high definition 35mm film programs occupy no such ghetto, and the U.S. situation is unique, because of the preponderance of 35mm film in prime time. Europe's stake in an HDTV production and distribution standard is greater than America's.</p>

<p>Thus, unlike other regions of the world, which must begin high definition program production with a large investment in electronic production equipment, the U.S. networks can convert the 75 percent of their prime time programming shot on high definition 35mm film, to widescreen high definition by merely deciding to do so.</p>

<p>This gives the U.S. an advantageous position, and enables it to make a rapid and efficient change to high definition, simply by arranging to shoot the film with a 16:9 wide screen aspect ratio.</p>

<p>No expensive conversion of existing studios is required initially. Programs may be shot on the same film using the same cameras as used today to shoot normal television programs. Thus a large proportion of the program schedule can be broadcast without an initial investment in electronic high definition studio equipment. Of course, the HDTV investment will involve network playback and distribution equipment.</p>

<p>Naturally, ultimate conversion will require a sizeable investment in high definition production equipment for the studios, but initial conversion to HDTV can be accomplished in the U.S. more quickly and inexpensively than in other regions of the world.</p>

<p><strong>Transmission and Delivery</strong></p>

<p>As to transmission and delivery to the home, Europe, North America, and Japan have all opted for a simulcast solution. That is, current normal signals will continue on today's channels, and high-definition will be delivered on a separate channel.</p>

<p>In Europe and Japan, HDTV will be delivered by satellite, and by cable or fiber. In the U.S., satellite and cable will play a role, but primary emphasis is placed on terrestrial transmission of HDTV by the 1420 independent television stations in the U.S. On these new channels, Japan and the U.S. have opted to go directly from current television to HDTV .</p>

<p>Alfred Sikes, chairman of the FCC has stated earlier this year: "Although we aspire to establish a simulcast system, the Commission believes it advisable to be fully apprised of all aspects of an enhanced definition system (EDTV), including its technical attributes, its consumer acceptance and its cost effectiveness. We do not envision, however, that the Commission would adopt an EDTV standard, if at all, prior to reaching a final decision on an HDTV standard, which, as I indicated above, will be made in the second quarter of 1993."</p>

<p>On the other hand, Europe has been pursuing a multi-step program, beginning with a 4:3 625-line enhanced system—D2MAC, through a wide screen 16:9 625-line system (WIDE MAC), and finally to a full HDTV system (HD-MAC).</p>

<p>It is believed in the U.S. that the market will not bear the cost of several such steps to HDTV. On September 6, 1990, Chairman Sikes made this comment: "Pursuing EDTV options would tend to maximize transition costs for both industry and consumers. Stations would need to make a series of sequential investments, as they inched towards full high definition operations. At the same time, however, consumers would almost certainly be confused, and would probably resist buying equipment which, in relatively short order, might be rendered obsolete."</p>

<p>The commercial marketplace philosophy of the U.S. encourages all interested parties to propose systems for terrestrial broadcast of HDTV. In 1989 there were 23 proponents, today there are six. One proposal is for an enhanced television system, ACTV-1, by Thomson-Sarnoff-NBC.</p>

<p>Five proposals are for HDTV systems from General Instrument, MIT, NHK, Thomson-Philips and Zenith. General Instrument proposes an all-digital system. MIT has proposed a hybrid, analog-digital system, but it is not fully designed. NHK offers a terrestrial MUSE analog transmission system. Thomson-Philips had an analog system, but it is widely rumored that they will soon propose an all-digital approach. Zenith offers a hybrid analog-digital system. All these systems will be tested in 1991-1992.</p>

<p>While all this might look confusing to the outside, America, like Darwin, believes in the survival of the fittest. Other regions of the world tend to pick a single solution and hope that the technology can be developed to support it.</p>

<p>Naturally, developers of all-digital transmission systems will encounter a great technical challenge to fit HDTV into a terrestrial 6 MHz channel, but an all-digital transmission standard for satellite transmission appears quite practical.</p>

<p>Surely we are in the twilight zone of analog transmission, and recognizing that transmission standards cannot be changed easily once adopted, should we not reconsider the setting of any analog or digitally-assisted standard, especially for satellite transmission? If General Instrument and Thomson-Philips can perfect an all-digital terrestrial transmission system in a 6 MHz channel, should not Europe consider the same for 27 MHz satellite channels?</p>

<p><strong>To conclude I offer three messages.</strong></p>

<p>1. First, it is essential for the creative community and for the free flow of information worldwide to find a common technique for high definition production and program exchange. This means a single world standard, or a high quality transparent standards converter in the high definition domain. If neither of these things occur, programs largely produced on 35mm film in the U.S. will remain the main source of programs on the world market.</p>

<p>2. Second, the global marketplace is not only a hardware market, but more importantly a software market, i.e., programs, language, culture and ideas—fields in which France has been a leader for centuries. High definition must not be only an industrial affair, it must also be the affair of the artist, for whom we engineers are but toolmakers.</p>

<p>3. Third, it is important to re-examine the analog or digitally assisted analog approach to HDTV transmission in favor of an all-digital transmission system for satellite and cable. This not only recognizes the certainty that digital transmission will come sooner than expected, but digital transmission could catapult Europe and its industry into a position of clear leadership as we approach the 21st century.</p>

<p>Knowing full well that these are not easy matters to resolve, keep in mind the words of Machiavelli in The Prince: "There is nothing more difficult to take in hand, more perilous to conduct or more uncertain of success, than to take the lead in the introduction of a new order of things, because the innovator has for enemies all those who have done well under the old conditions, and but lukewarm defenders in those who may do well under the new."</p>

<p> _________________________________________________________</p>

<p><br />
<em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em><br />
 </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 26, 2005  1:06 PM</b>
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
			<?=getComments(129)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 129)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1991_a_world_of_change_by_joseph_a_flaherty.php" type="text/javascript" charset="utf-8"></script>
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