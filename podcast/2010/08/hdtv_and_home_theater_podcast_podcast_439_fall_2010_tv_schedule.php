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
		AND e.entry_id = 3908";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3908 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3908 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3908";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-439-fall-2010-tv-schedule.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3908";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule</title>
	<meta name="keywords" content="bang theory, los angeles, new shows, half men, law order, family, cbs, new, abc, fox, nbc, show, america, shows, law, big, years, life, drama, school, get, dad, half, comedy, picks" />
	<meta name="description" content="Pat yourself on the back, we did it again.  We all made it through another Summer with nothing to watch on TV.  Whether you had stored up a bunch of shows on your DVR to get you through the dry season, or relied heavily on Netflix and Hulu, you made it.  And now it’s time to reap the reward.  Football is back, and in just a few short weeks all your favorite shows will be back on, and perhaps a few new ones will make it to your favorites list." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-439-fall-2010-tv-schedule.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Pat yourself on the back, we did it again.  We all made it through another Summer with nothing to watch on TV.  Whether you had stored up a bunch of shows on your DVR to get you through the dry season, or relied heavily on Netflix and Hulu, you made it.  And now it’s time to reap the reward.  Football is back, and in just a few short weeks all your favorite shows will be back on, and perhaps a few new ones will make it to your favorites list." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3908', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-439-fall-2010-tv-schedule.php">HDTV and Home Theater Podcast - Podcast #439: Fall 2010 TV Schedule</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>August 19, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=435&category=Internet HD Video">Internet HD Video</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=381&category=Sports">Sports</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>Fall 2010 TV Schedule</h3>
<p>Pat  yourself on the back, we did it again.  We all made it through  another  Summer with nothing to watch on TV.  Whether you had stored up a  bunch  of shows on your DVR to get you through the dry season, or  relied  heavily on Netflix and Hulu, you made it.  And now it’s time to  reap the  reward.  Football is back, and in just a few short weeks all  your  favorite shows will be back on, and perhaps a few new ones will  make it  to your favorites list.</p>
<p>As in years past, we visited <a href="http://www.tvguide.com/special/fall-preview/fall-schedule.aspx" target="_blank">TVGuide.com</a> for a great chart of everything you’ll want to see each night of the   week.  But since TV Guide only covers the big 5 networks (we’ll give the   CW the benefit of the doubt), we rounded out our research with <a href="http://tvdramas.about.com/od/tvshowsaz/a/fallpremiere07.htm" target="_blank">About.com</a> and <a href="http://www.thetvremote.com/2009-2010-winter-premiere-schedule/" target="_blank">The TV Remote</a>.  We’ll summarize it all here, but if you want to dive a bit deeper, those are all great resources.</p>
<p>TV Guide also has a great premier <a href="http://www.tvguide.com/special/fall-preview/calendar.aspx" target="_blank">calendar</a> with the new shows starting each day in September and October.  Some start as early as September 7.</p>
<h4>Monday</h4>
<ul>
<li><strong>ABC</strong>
<ul>
<li>Dancing with the Stars, Castle</li>
</ul>
</li>
<li><strong>CBS</strong>
<ul>
<li><strong>Mike &amp; Molly</strong> &#8211; A  comedy about a working class  Chicago couple who find love at an  Overeaters Anonymous meeting.  From  the Executive Producer of The Big  Bang Theory and Two and a Half Men,  it’s a new comedy about finding  romance in the most unusual places.</li>
<li><strong>Hawaii Five-0</strong> &#8211; A  contemporary take on the classic  series about a new elite federalized  task force whose mission is to  wipe out the crime that washes up on the  Islands&#8217; sun-drenched beaches.</li>
<li>How I Met Your Mother, Rules of Engagement, Two and a Half Men</li>
</ul>
</li>
<li><strong>The CW</strong>
<ul>
<li>90210, Gossip Girl</li>
</ul>
</li>
<li><strong>FOX</strong>
<ul>
<li><strong>Lone Star</strong> &#8211; a  provocative soap set against the  backdrop of big Texas oil.  Robert  (Bob) Allen is a charismatic and  brilliant schemer who has meticulously  constructed two lives in two  different parts of Texas. He&#8217;s juggling two  identities and two women in  two very different worlds &#8211; all under one  mountain of lies</li>
<li>House</li>
</ul>
</li>
<li><strong>NBC</strong>
<ul>
<li><strong>The Event</strong> &#8211; The  Event is an emotional, high-octane  conspiracy thriller that follows  Sean Walker, an everyman who  investigates the mysterious disappearance  of his would-be fiancée  Leila, and unwittingly begins to expose the  biggest cover-up in U.S.  history.</li>
<li><strong>Chase </strong>- U.S.  Marshals Annie Frost likes to stay  one step ahead of the outlaws. As  far as this cowboy boot-wearing girl  is concerned, they can run, but  they can&#8217;t hide from her forever. Annie  has a sharp mind, a big heart,  and an attitude to match. Throw in a  unique perspective and personal  style, and she is the reason you don&#8217;t  mess with Texas.</li>
<li>Chuck</li>
</ul>
</li>
</ul>
<h4>Tuesday</h4>
<ul>
<li><strong>ABC</strong>
<ul>
<li><strong>No Ordinary Family</strong> &#8211; The  Powells are about to go  from ordinary to extraordinary. After 16 years  of marriage, Jim and  Stephanie&#8217;s relationship lacks the spark it once  had, and their family  life now consists of balancing work and their two  children, leaving  little time for family bonding. During a family  vacation set up by Jim  in an attempt to reconnect, their plane crashes  into the Amazon River.  But this is where the fun starts for the Powells,  as they soon discover  that something&#8217;s not quite right. Each of them  now possesses unique  and distinct superpowers.</li>
<li><strong>Detroit 1-8-7</strong> &#8211; What  does it take to be a  detective on America&#8217;s streets? Get an in-depth  look at some of  Detroit&#8217;s finest and watch the crisis and revelation,  heartbreak and  heroism of the cops assigned to an inner city homicide  unit.</li>
<li>Dancing with the Stars: the Results Show</li>
</ul>
</li>
<li><strong>CBS </strong>
<ul>
<li>NCIS, NCIS: Los Angeles, The Good Wife</li>
</ul>
</li>
<li><strong>The CW</strong>
<ul>
<li>One Tree Hill, Life Unexpected</li>
</ul>
</li>
<li><strong>FOX </strong>
<ul>
<li><strong>Raising Hope</strong> &#8211; A  new single-camera family comedy  from Emmy Award winner Greg Garcia (&#8220;My  Name Is Earl&#8221;) that follows the  Chance family as they find themselves  adding an unexpected new member  into their household.</li>
<li><strong>Running Wilde</strong> &#8211; A  romantic comedy about Steve  Wilde, a filthy-rich, immature playboy  trying desperately to win (or  buy) the heart of his childhood  sweetheart, Emmy Kadubic, the  über-liberal humanitarian who got away –  all told through the  perspective of a 12-year-old girl.</li>
<li>Glee</li>
</ul>
</li>
<li><strong>NBC </strong>
<ul>
<li>Biggest Loser, Parenthood</li>
</ul>
</li>
</ul>
<h4>Wednesday</h4>
<ul>
<li><strong>ABC </strong>
<ul>
<li><strong>Better With You </strong>- A  sitcom focusing on three  couples in one family. They include older  sister Maddie and her partner  of nine years, Ben; younger sister Mia and  her  boyfriend-just-turned-fiance of two months, Casey; and their  parents,  Vicky and Joel. The comedy promises to show &#8220;real, relatable  stories&#8221;  as well as a believable family dynamic.</li>
<li><strong>The Whole Truth</strong> &#8211; This  unique legal drama  chronicles the way a case is built from the  perspective of both the  defense and prosecution. Showing each side  equally keeps the audience  guessing, shifting allegiances and opinions  on guilt or innocence until  the very final scene.</li>
<li>The Middle, Modern Family, Cougar Town</li>
</ul>
</li>
<li><strong>CBS</strong>
<ul>
<li><strong>The Defenders </strong>- A  drama about two colorful Las  Vegas defense attorneys who go all-in when  it comes to representing  their clients. Nick and Pete are the local  go-to guys with an eclectic  client list who are still looking to hit  their own jackpot.</li>
<li>Survivor, Criminal Minds</li>
</ul>
</li>
<li><strong>The CW </strong>
<ul>
<li><strong>Hellcats </strong>- Marti  Perkins&#8217; plan was to get through  Lancer University on her scholarship,  go to law school, and leave  Memphis and her hard-drinking mother behind  to start a new life as an  attorney. Instead, Marti&#8217;s scholarship gets  cancelled and her mother  &#8220;forgets&#8221; to tell her. Out of options, Marti  finds herself fighting for  a spot on the Hellcats &#8212; Lancer&#8217;s legendary  cheer squad &#8212; and for  the scholarship that comes with it.</li>
<li>America&#8217;s Next Top Model</li>
</ul>
</li>
<li><strong>FOX </strong>
<ul>
<li>Lie to Me, Hell&#8217;s Kitchen</li>
</ul>
</li>
<li><strong>NBC</strong>
<ul>
<li><strong>Undercovers</strong> &#8211; A  one-hour spy drama that proves  marriage is still the world&#8217;s most  dangerous partnership.  To put the  spark back in their marriage, some  couples take a tropical vacation.  Not Steven and Samantha. They rejoin  the CIA. Now they&#8217;re discovering  things about each other they never  knew. Like which lock-picking  technique each prefers, and who killed  who, and how well they work  together in a hostile environment.  With  their day jobs and lives in  the balance, date night is about to get a  lot more exciting.</li>
<li><strong>Law &amp; Order: Los Angeles</strong> &#8211; There&#8217;s  a place  where crime has reached celebrity status. Welcome to Los  Angeles, home  of the rich and famous, and the completely unscrupulous.  They say this  is the place where anyone with the right attorney can get  away with  murder. We say this is the place that needs some Law &amp;  Order.</li>
<li>Law &amp; Order: SVU</li>
</ul>
</li>
</ul>
<h4>Thursday</h4>
<ul>
<li><strong>ABC </strong>
<ul>
<li><strong>My Generation</strong> &#8211; Based  on a Swedish half-hour  mockumentary, which featured three guys in high  school who were  revisited by a camera crew fifteen years later. The  American version  uses the documentary format in a different way, as an  investigative  documentary, and to put the show in the context of the  wider world and  show the sea change around the globe in the last 10  years. &#8220;We&#8217;ll be  pushing these characters further than they want to go,  invading their  privacy and seeing things that they don&#8217;t want you to  see.”</li>
<li>Grey&#8217;s Anatomy, Private Practice</li>
</ul>
</li>
<li><strong>CBS </strong>
<ul>
<li><strong>$#*! My Dad Says</strong> &#8211; Stars  Emmy Award winner William  Shatner as Ed Goodson, a forthright and  opinionated dad who relishes  expressing his unsolicited and often wildly  politically incorrect  observations to anyone within earshot. Nobody is  safe from Ed&#8217;s rants,  including his sons, Henry, a struggling  writer-turned-unpaid blogger;  and Vince, the meek half of a husband/wife  real estate duo with  domineering Bonnie.</li>
<li>Big Bang Theory, CSI, The Mentalist</li>
</ul>
</li>
<li><strong>The CW </strong>
<ul>
<li><strong>Nikita </strong>- When  she was a deeply troubled teenager,  Nikita was rescued from death row  by a secret U.S. agency known only as  Division, who faked her execution  and told her she was being given a  second chance to start a new life and  serve her country. What they  didn&#8217;t tell her was that she was being  trained as a spy and assassin.  Ultimately, Nikita was betrayed and her  dreams shattered by the only  people she thought she could trust. Now,  after three years in hiding,  Nikita is seeking retribution and making it  clear to her former bosses  that she will stop at nothing to expose and  destroy their covert  operation.</li>
<li>Vampire Diaries</li>
</ul>
</li>
<li><strong>FOX </strong>
<ul>
<li>Bones, Fringe</li>
</ul>
</li>
<li><strong>NBC </strong>
<ul>
<li><strong>Outsourced </strong>- Mid  America Novelties sells products  like whoopee cushions, foam fingers,  and wallets made of bacon. Yes,  this is the stuff upon which the  American way of life is built, but try  explaining that to someone who  lives on the other side of the world.   Well, that&#8217;s exactly what Todd  Dempsy must do when he&#8217;s sent to run  the company&#8217;s call center in India.  Talk about culture shock, and not  just for Todd&#8217;s employees. While Todd  has to teach them how to make the  up-sell to the Deluxe Twin Beer  Helmet, he&#8217;s going to have to adapt as  well. Like in a country where  cows are sacred, perhaps you don&#8217;t order  a double cheeseburger.</li>
<li>Community, 30 Rock, The Office, The Apprentice</li>
</ul>
</li>
</ul>
<h4>Friday</h4>
<ul>
<li><strong>ABC</strong>
<ul>
<li><strong>Secret Millionaire</strong> &#8211; A  one-hour alternative series  that follows some of America&#8217;s wealthiest  people for one week as they  leave behind their lavish lifestyles,  sprawling mansions and luxury  jets, conceal their true identities, and  go to live and volunteer in  some of the most impoverished and dangerous  communities in America.  Surviving on welfare wages, their mission is to  discover the unsung  heroes of America—deserving individuals who  continually sacrifice  everything to help those in need.</li>
<li><strong>Body of Proof</strong> &#8211; Dr.  Megan Hunt was in a class of  her own, a brilliant neurosurgeon at the  top of her game. Her world is  turned upside down when a devastating car  accident puts an end to her  time in the operating room. Megan resumes  her career as a medical  examiner determined to solve the puzzle of who  or what killed the  victims. Megan’s instincts are sharp, but she’s  developed a reputation  for graying the lines of where her job ends and  where the police  department’s begins.</li>
<li>20/20</li>
</ul>
</li>
<li><strong>CBS </strong>
<ul>
<li><strong>Blue Bloods</strong> &#8211; A drama about a multi-generational  family of cops dedicated to New York  City law enforcement. Frank Reagan  is the New York City Police  Commissioner and heads both the police  force and the Reagan brood. He  runs his department as diplomatically as  he runs his family, even when  dealing with the politics that plagued  his unapologetically bold father,  Henry, during his stint as Chief.</li>
<li>Medium, CSI: NY</li>
</ul>
</li>
<li><strong>The CW</strong>
<ul>
<li>Smallville, Supernatural</li>
</ul>
</li>
<li><strong>FOX </strong>
<ul>
<li>Human Target, Good Guys</li>
</ul>
</li>
<li><strong>NBC</strong>
<ul>
<li><strong>School Pride</strong> &#8211; Studies  show that beautiful schools  result in higher scholastic achievement,  and we&#8217;re putting that theory  to the test. Each week, we empower an  entire community as we renovate a  rundown school and watch as its pride  grows. Every episode features  touching personal stories of children,  parents and teachers  experiencing the amazing transformations of their  school grounds and  athletic fields. Surprise celebrity appearances and  seeing it all  happen against the clock add drama to this inspirational  show.</li>
<li><strong>Outlaw </strong>- Few  jobs are guaranteed for a lifetime,  and a Supreme Court appointment is  one you just don&#8217;t quit. Unless  you&#8217;re Cyrus Garza (Smits). A playboy  and a gambler, Justice Garza  always adhered to a strict interpretation  of the law. Until he realized  the system he always believed in was  flawed. Now, he&#8217;s quit the bench  and returned to being an attorney.  Determined to represent &#8220;the little  guy,&#8221; he&#8217;s using his inside  knowledge of the justice system to take on  today&#8217;s biggest legal cases.  And making plenty of powerful people  unhappy along the way.</li>
<li>Dateline NBC</li>
</ul>
</li>
</ul>
<h4>Saturday</h4>
<ul>
<li><strong>ABC </strong>
<ul>
<li>College Football</li>
</ul>
</li>
<li><strong>CBS </strong>
<ul>
<li>Crimetime Saturday, 48 Hours Mystery</li>
</ul>
</li>
<li><strong>FOX</strong>
<ul>
<li>Cops, America&#8217;s Most Wanted</li>
</ul>
</li>
</ul>
<h4><strong>Sunday</strong></h4>
<ul>
<li><strong>ABC </strong>
<ul>
<li>America&#8217;s Funniest Home Videos, Extreme Makeover: Home Edition, Desperate Housewives, Brothers &amp; Sisters</li>
</ul>
</li>
<li><strong>CBS</strong>
<ul>
<li>60 Minutes, The Amazing Race, Undercover Boss, CSI: Miami</li>
</ul>
</li>
<li><strong>FOX</strong>
<ul>
<li>The OT, The Simpsons, The Cleveland Show, Family Guy, American Dad</li>
</ul>
</li>
<li><strong>NBC</strong>
<ul>
<li>Football Night in America, Sunday Night Football</li>
</ul>
</li>
</ul>
<h4>Braden&#8217;s Picks, New Shows</h4>
<ul>
<li>CBS: Hawaii Five-0</li>
<li>CBS: Blue Bloods &#8211; Tom Selleck, ‘nuff said</li>
<li>CBS: $#*! My Dad Says &#8211; William Shatner, ‘nuff said</li>
<li>ABC: No Ordinary Family</li>
</ul>
<h4>Ara&#8217;s Picks, New Shows</h4>
<ul>
<li>CBS: Hawaii Five-0</li>
<li>CBS: $#*! My Dad Says</li>
</ul>
<h4>Braden&#8217;s Picks, Returning Shows</h4>
<ul>
<li>ABC: Castle</li>
<li>CBS: NCIS, The Mentalist</li>
<li>FOX: Human Target, Good Guys</li>
<li>NBC: Chuck, The Office, Community, 30 Rock</li>
</ul>
<h4>Ara&#8217;s Picks, Returning Shows</h4>
<ul>
<li>ABC: Modern Family</li>
<li>CBS: How I Met Your Mother, Two and a Half Men, Big Bang Theory, NCIS, Survivor</li>
<li>FOX: House, Bones, Fringe, Hells Kitchen</li>
<li>NBC: Chuck, 30 Rock</li>
</ul>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2010-08-20.mp3">Download Episode #439</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>August 19, 2010 10:37 PM</b>
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
			<?=getComments(3908)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3908)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-439-fall-2010-tv-schedule.php" type="text/javascript" charset="utf-8"></script>
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