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
		AND e.entry_id = 132";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 132 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 132 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 132";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1994_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 132";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc." height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc." />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc." />
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
	<title>HDTV Magazine - 1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc.</title>
	<meta name="keywords" content="advanced television, grand alliance, digital hdtv, transition digital, hdtv system, digital, television, hdtv, system, systems, transition, service, broadcasting, terrestrial, technology, cbs, analog, channel, today, transmission, broadcasters, cable, video, advanced, quality" />
	<meta name="description" content="Television broadcasting is technology-past, present, and future Every frame of every program and every syllable of every word broadcast is delivered through a vast complex of ever changing and ever improving technology, and it has always been thus. Today's debates..." />
	<meta name="title" content="1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc." />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc." />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1994_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Television broadcasting is technology-past, present, and future Every frame of every program and every syllable of every word broadcast is delivered through a vast complex of ever changing and ever improving technology, and it has always been thus. Today's debates..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=132', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1994_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php">1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc.</a></td>
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
				<p><strong>Television broadcasting is technology-past, present, and future</strong></p>

<p>Every frame of every program and every syllable of every word broadcast is delivered through a vast complex of ever changing and ever improving technology, and it has always been thus.</p>

<p>Today's debates on digital television and HDTV mirror those of television's earliest days. As the philosopher Santayana observed:</p>

<p>"Those who cannot remember the past are condemned to repeat it."</p>

<p>In 1929, just two years after the opening demonstration of television by the Bell Telephone laboratories on April 7, 1927, the first American book on television was published by Sheldon and Grisewood of New York University. In their concluding chapter on The Future of Television they wrote:</p>

<p>"The chief difficulty at present in that television requires a rather broad band of wave-lengths. Had television come ten years ago this would have presented no difficulty. As matters now stand, however, with a broadcast station crowded into every possible space (of spectrum), the introduction of television will of necessity crowd some of these out. In the meantime, the fact that there is no public demand for television magnifies this difficulty. If the public knew that it wanted television, then television would at least be given a hearing."</p>

<p>Further, in the Fall 1931 edition of Radio Design magazine the writer reports:</p>

<p>"As technical conditions exist now, it is comparatively easy to produce very fine television images, but exceedingly difficult to transmit them by radio. The air is simply crowded to suffocation."</p>

<p>We were at 2 MHz then, but the magazine goes on to report:</p>

<p>"The over-enthusiastic televisionists are making their big mistake in thinking that television will repeat the glamorous history of radio broadcasting, when every sign indicates that it will not and indeed cannot. Conditions now are altogether different from what they were ten years ago. Today we have a Federal Radio Commission, an aggravating patent situation, an overcrowded ether, an overabundance of radio factories, a lot of politicians with radio axes to grind, and, worst of all, a sophisticated buying element spoiled by high quality talking motion pictures. If not for the 'talkies' the present crude televisors might stand a slight chance of success. However, the 'talkies' have entirely erased this possibility. "</p>

<p>Aren't we hearing much of this today about digital HDTV?</p>

<p>You'll be interested to note that one glimmer of sense was provided in a hearing before the Federal Radio Commission by Mr. C. W. Horn, then Manager of the Westinghouse Electric and Manufacturing Company when he told the Commission that:</p>

<p>Engineers have developed all the great inventions and statements made on television other than by engineers are of little value."</p>

<p>How many of you believe that this could be said of many technologies today?</p>

<p>But television was a major changes and change inevitably finds skeptics, even among the most informed.</p>

<p>In 1865, Lord Kelvin, then President of the Royal Society, concluded:</p>

<p>"Heavier-than-air flying machines are impossible."</p>

<p>Today, the impossible doesn't take as long as it used to take, and technology sweeps across our world, daily changing the way we live, but changes involving new technologies frequently arrive to an incredulous audience. In the late 19th century, when electricity was finding its way into everyday life, the sign shown in Fig. 1, was prominently posted in public buildings to reassure a doubting public.</p>

<p>Our technological contributions to this new art notwithstanding, television broadcasting has not always been well received.</p>

<p>Frank Lloyd Wright called it:</p>

<p>"Chewing gum for the eyes."</p>

<p>And television's own humorist, Ernie Kovacs said:</p>

<p>"Television is called a medium because it's neither rare nor well done,"</p>

<p>Nevertheless, the inexorable march of technology advanced the quality, flexibility, and reliability of the television service in revolutionary steps. Mechanical scanning systems with the Nipkow disc gave way to electronic scanning at both the transmitter and receiver with the experiments of campbell-Swinton in 1911. Electronic television was born, and the 30 line image was dubbed "high definitions as has been every subsequent increase in scanning line numbers.</p>

<p>The first practical television service was the British Broadcasting Corporation's 405 line system which went into operation just before the start of World War II. America's 525 line monochrome service, developed by the RCA, was first publicly demonstrated at the 1939 New York World's Fair and went into commercial service after the war in 1947.</p>

<p>Explosive growth followed, and television replaced AM radio as the nations s prime entertainment and news medium.</p>

<p>Nationwide television distribution by coaxial cable and terrestrial microwave systems delivered network television to over 90% of the Country' s viewers.</p>

<p>Color came in 1954 with the adoption of the NTSC color system. This breakthrough was followed in 1356 with the development of video tape, a joint Ampex/CBS development. The 1960's saw the development of electronic video tape editing systems, the CMX off-line editing system, miniature video tape machines, portable cameras, and the hand-hold "CBS Minicam" color camera.</p>

<p>In 1971 these developments gave birth to the CBS-developed Electronic News Gathering system, or ENG, and filmed news came to an end worldwide. Modern news operations, including CNN, could not exist without ENG. Filmed news simply could not support such realtime worldwide news operations.</p>

<p>Orbiting satellites followed by geostationary satellites, replaced the terrestrial distribution networks and offered greater quality at reduced costs. All the US networks are distributed via satellite today, and incoming feeds from remote pick-up sites are transmitted via satellite or fiber line to the network centers.</p>

<p>In the early 1970's digital equipment began to appear to provide functions difficult or impossible to achieve in the analog domain. Digital time base correctors, frame synchronizers, graphic quality character generators, graphic paint boxes, etc. began to form digital islands in an all analog sea.</p>

<p>With the development of digital video tape machines these digital islands began to grow into digital continents, evaporating.</p>

<p>Of all the technological advances that have affected television, none is more fundamental or more far-reaching than the transition to digital techniques for all phases of the television process, In fact, the conversion to digital terrestrial transmission is the last link in the digital chain.</p>

<p>This transition to digital techniques will impact the entire installed base of the television industry. At the consumer level, there are one billion television sets in use worldwide with 200 million in North America.</p>

<p>98% of American homes have a TV; 85% have a VCR; 65% are connected to cable, or have a DBS service; and 33% have a computer. To support this installed base of consumer equipment, 25 million TV sets are sold each year in the US alone. The value of this market is $8.5 billion.</p>

<p>Digital techniques in communications are not, of course, new. In 1623, Sir Francis Bacon, in his treatise: "The Dignity and Advancement of Learning", he proposed to encode the alphabet by a binary Communications system.</p>

<p>He suggested that:</p>

<p>"Provided only that the matter included be five times less than that which includes it, without any condition or limitation, the alphabet can be resolved into two letters only, which by repetition and transposition through five places could represent all the other letters of the alphabet</p>

<p>With his five-bit "byte", he could compress and encode 92 different characters, or the letters of the alphabet.</p>

<p>He concluded:</p>

<p>"The contrivance shows a method of signifying and expressing one's mind to any distance by objects that are either audible or visible, provided only that the objects are but capable of two differences; e.g. fireworks, bells, or cannon."</p>

<p>Thus, today, while it is not done with fireworks, bells, or cannons, the dominant technology issue for terrestrial broadcasters over the next four to eight years is the total conversion from present analog NTSC broadcasting to digital advanced television (ATV) broadcasting, including wide screen (16:9) TV and high definition (HDTV).</p>

<p>CBS recognized the competitive challenge represented by the emergence of HDTV, and has been deeply involved in the technology of advanced television and HDTV ever since. In fact, CBS introduced HDTV into the US in 1981, and since 1989 our publicly expressed ATV goals have been:</p>

<p>1. To ensure that terrestrial broadcasters will be able to deliver a fully competitive digital ATV and HDTV service;</p>

<p>2. To provide sufficient spectrum for terrestrial broadcasters to effect the transition to digital transmission, replicating their present coverage area;</p>

<p>3. To preserve the value of existing TV receivers, and thus the existing TV audience, during the transition to digital television:</p>

<p>4. To provide technical headroom to ensure future competitive parity for terrestrial broadcasting as digital technologies improve.</p>

<p>The first and fourth goals are satisfied to the extent possible by the "Grand Alliance" ATV/HDTV system as recommended to the FCC by the Advisory Committee on Advanced Television Service (ACATS). The Hon. Richard E. Wiley was chairman of the ACATS, and I was the Chairman of the Planning Subcommittee and Co-chairman of the Technical Subgroup responsible for defining the system specifications, approving the system design, and recommending the standard,</p>

<p>The second and third goals were largely assured with the FCC plan to transition the nation to an improved HDTV service via a second 6 MHz simulcast channel during an estimated fifteen-year transition period. However, the debate raised by some broadcasters over using the HDTV channels for multi program "standards TV and/or for data broadcasting caused concern over the "free" assignment of the second transitional channel to existing broadcasters. This, plus the successful PCS spectrum auctions, led to the present Congressional debate over TV spectrum auctions. Today, the assignment of the digital transition channels is in doubt. Naturally, the loss of these transition channels would spell doom for terrestrial broadcasting as we have known it.</p>

<p>The importance of this digital transition for broadcasting, and thus, the importance of the digital channels can be seen in that virtually all other communications media are already digital systems or rapidly becoming so:</p>

<p>Telecommunications, telephone, FAX, and computers are a11 digital systems providing improved quality, reliability, economies, and ever finding new applications. </p>

<p><u>Recorded audio </u>is already digital via the compact disc, totally replacing the analog record.<br />
 <br />
<u>Home receivers</u>, home digital video discs, and home VCR' 9 will be digital in a few years. </p>

<p><u>Direct broadcasting satellites </u>in America were launched as digital services to take advantage of digital compression techniques to multiply their channel count and to enable wide screen HDTV transmission. The Group-W satellite operation in Singapore is an all-digital service.<br />
 <br />
Cable operators in America have completed their digital compression studies, and larger Cable systems are already converting to digital transmission to increase their channel count and to enable wide screen HDTV programming, </p>

<p>Fiber-hated television systems now being developed will also be able to deliver multi-channel high quality digital TV and HDTV programs to a cable-like customer base. </p>

<p>With the potential of over 2O0 digital channels with a wide screen HDTV capability, DBS, cable, and broad-band fiber distribution media have an important economic incentive to become digital delivery systems.</p>

<p>With increased cable, fiber, DBS and home video competition, traditional broadcasters will be under enormous pressure to maintain their competitive position in the landscape of 21st century television and to secure a place on the National Information Infrastructure (NII-) where there are no analog channels.</p>

<p>Only digital technology will provide competitive parity for broadcasters, and only the same digital technology will provide broadcasters the essential interoperability with the digital systems of the NII.</p>

<p>Analog NTSC television, as we know it today, will disappear as higher quality digital TV and HDTV capture a larger-and-larger share of the consumer market, and capture the market they will-cable, DBS, fiber, and home video will see to that. "DirecTV" has already made a substantial start.</p>

<p>In short, terrestrial broadcasters simply must make the transition to digital television, and the only way they can make this transition, with full quality TV and HDTV transmission potential, is to have a second 6 MHz television channel on which to operate the digital TV and HDTV service in parallel with the NTSC service during the analog-to-digital transition period.</p>

<p>To devise an HDTV standard for the US the FCC sought private sector advice and formed the FCC Advisory Committee on Advanced Television Service, or ACATS, in 1987 and charged it to study the problems of the terrestrial broadcasting of HDTV, to test proposed systems, and to make recommendations for a single terrestrial HDTV transmission standard.</p>

<p>System proposals peaked at twenty one, but by 1990 they had shrunk to only nine. Two of these were HDTV simulcast system", and they were both analog designs.</p>

<p>The FCC adopted a simulcast transition plan wherein each existing television station would be assigned a second 6 MHz channel for the digital TV and HDTV service. Following a transition period the NTSC service would be abandoned and the channel returned to the government for reuse.</p>

<p>Work on analog systems was in process, when, in 1990 the major change took place. On June 1, 1990 General Instrument proposed an all-digital HDTV system just four weeks before the ACUTE system submission deadline, and television would forever change, The digital era had begun and analog broadcasting was doomed.</p>

<p>Within nine months four digital HDTV systems had been proposed, and these systems were designed, built, and tested at the Advanced Television Test Center CATTY) in Alexandria, Virginia and at the Advanced Television Evaluation Laboratory in Canada.</p>

<p>While all the systems produced good HDTV pictures in a 6 MHz channel, none of the systems were judged to have performed Sufficiently well to be selected as the single standard at that time. The four digital system proponents began to examine the possibility of combining their systems into a single HDTV system proposal in what has come to be known as the Grand Alliance",</p>

<p>The "Grand Alliances was formed and announced on May 24, 1993 by the four digital HDTV system proponents - AT&T/Zenith, General Instrument, DSRC/Thomson/Philips, and MIT. The initial Grand Alliance technical proposal combined various parts of their previous four separate systems into a single all-digital HDTV transmission system.</p>

<p>A Technical subgroup chaired by Dr. Dorros of Bellcore and myself reviewed, modified, and approved the Grand Alliance system for construction and test.</p>

<p>The "Grand Alliance" system has the following parameters:</p>

<p>- The system supports dual scanning rates of 1080 active lines with 1920 pixels per-line interlace scanned at 59.94 and 60 fields/second and 720 active lines with 1280 pixels-per-line progressively scanned at 59.94 and 60 frames/second. Both scanning formats also operate in the progressive scanning mode at 30 and 24 frames/second.</p>

<p>- The system employs MPEG-2 video compression and transport systems.</p>

<p>- The system uses the Dolby AC-3, 384 Kb/8 audio system.</p>

<p>- The system uses the 8-VSB transmission system, originally developed by Zenith.</p>

<p>The system will support a hierarchy of scanning formats, as shown in Fig. 2, with full HDTV at the highest level and includes "standard TV" and multi- program compressed TV transmission as lower orders of the hierarchy. Based on studies by the consumer equipment industry, it is estimated that this additional flexibility will increase the cost of consumer HDTV receivers and VCRs by only 2% to 5%.</p>

<p>The private sector Advanced Television systems Committee (AC) has documented the full HDTV standard based on the "Grand Alliance" specifications as approved by the ACATS Technical Subgroup, and, as of this April 11, the standard was approved by the ATSC membership by an overwhelming majority. <br />
_______________________________________<br />
About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em></p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 26, 2005  1:45 PM</b>
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
			<?=getComments(132)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 132)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1994_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php" type="text/javascript" charset="utf-8"></script>
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