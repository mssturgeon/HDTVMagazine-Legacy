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
		AND e.entry_id = 3734";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3734 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3734 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3734";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-hdtv-tech-talk-ive-got-the-lowband-dtv-blues-june-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3734";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - HDTV Tech Talk: I&rsquo;ve Got The Low-Band DTV Blues (June 2009)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - HDTV Tech Talk: I&rsquo;ve Got The Low-Band DTV Blues (June 2009)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - HDTV Tech Talk: I&rsquo;ve Got The Low-Band DTV Blues (June 2009)" />
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
	<title>HDTV Magazine - HDTV Expert - HDTV Tech Talk: I&rsquo;ve Got The Low-Band DTV Blues (June 2009)</title>
	<meta name="keywords" content="rabbit ears, full wave, wave loop, vhf channel, low band, channel, antenna, signal, ’s, signals, noise, figure, stations, vhf, problem, wpvi’s, reception, channels, band, loop, –, uhf, station, dtv, mhz" />
	<meta name="description" content="Did DTV channel 6 disappear on your converter box or digital TV after June 12? Here’s why it may be “MIA”…and what you may be able to do about it." />
	<meta name="title" content="HDTV Expert - HDTV Tech Talk: I&amp;rsquo;ve Got The Low-Band DTV Blues (June 2009)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - HDTV Tech Talk: I&amp;rsquo;ve Got The Low-Band DTV Blues (June 2009)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-hdtv-tech-talk-ive-got-the-lowband-dtv-blues-june-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Did DTV channel 6 disappear on your converter box or digital TV after June 12? Here’s why it may be “MIA”…and what you may be able to do about it." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3734', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-hdtv-tech-talk-ive-got-the-lowband-dtv-blues-june-2009.php">HDTV Expert - HDTV Tech Talk: I&rsquo;ve Got The Low-Band DTV Blues (June 2009)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April  8, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
          <p>One of the more interesting stories that has developed following D-Day (June 12) is the trouble that viewers are having in several large markets with low-band TV channels – specifically, channel 6, which is now digital in Albany, NY; Philadelphia, PA, New Haven, CT, and five other TV markets.</p>
<p>There have also been reports of difficulty with stations on channel 7, most notably WLS in Chicago and WABC in New York City. The situation there is quite different, but we’ll take a quick look at it at the end of this article.</p>
<p>THE OP-ED SECTION</p>
<p>First off, let it be said that the FCC’s decision to retain channels 2 through 6 in the DTV channel core was ill advised. These are some of the oldest TV channels in existence and used to be the prime spots for a TV station, since they were the lowest channel numbers on tuners.</p>
<p>But the frequencies in which these channels are located – specifically, from 55 MHz to about 88 MHz, give or take several kilohertz – have long been plagued with impulse noise, such as you’d get from noisy fluorescent lamp ballast, brush motors, or any electronic equipment that creates inductive voltage spikes.</p>
<p>To make matters worse, seasonal signal propagation enhancement, caused by sporadic ionization of the ionosphere’s E-layer, can cause signals on these frequencies to hop across the country and create co-channel interference many thousands of miles away. Ham radio operators like myself refer to this summertime phenomenon as “E-skip,” for short.</p>
<p>Here’s another reason why channels 2 through 6 should have been retired: They require very large antennas for efficient reception. A full-wave loop antenna for channel 2 (56 MHz) would measure 5.4 meters in length, or about 17.5 feet! (Contrast that with a full-wave loop for UHF channel 42, which would be about 18 inches around.)</p>
<p>This makes it problematic to design an indoor antenna with any kind of gain, short of adding an internal amplifier. Unless that amplifier’s design is bullet-proof (and for normal Radio Shack prices, it usually isn’t), the antenna system will be overwhelmed with noise and interference from other nearby RF signals, such as FM radio stations.</p>
<p>THE CHANNEL SIX CONUNDRUM</p>
<p>But that’s water under the bridge now, and 40 stations have decided to stay put on this not-so-valuable real estate. As a result, I’m getting quite a few emails about some bizarre low-band VHF reception issues.</p>
<p>My favorite so far is from a television station monitoring service, whose rooftop channel 5 antenna in West Virginia is being routinely wiped out every day by fluorescent lights in the Ace Hardware below, during normal store hours. (Not impossible to fix, but it will take some detective work.)</p>
<p>Getting back to my home market of Philadelphia, there are plenty of problems with reception of WPVI’s digital signal on channel 6. And it became evident pretty quickly that WPVI was having these problems just 24 hours after shutting down their analog signal on channel 6.</p>
<p>Subsequently, WPVI and CBS affiliate WRGB in Schenectady, NY (also on channel 6, and also experiencing reception issues) applied to the FCC for an emergency authorization to go to higher power.</p>
<p>According to  a news story in the June 22 issue of <em>Broadcasting and Cable</em> magazine, <em>“…The FCC granted the station (WPVI) a special temporary authority (STA) to boost its transmission power on Ch. 6 from the relatively low 7.5 kilowatts (kW) to 30.6 kW, the maximum power for the northeastern “Zone 1” region of the U.S.”</em></p>
<h1>
<div id="attachment_424" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-14.jpg"><img class="size-full wp-image-424" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-14.jpg" alt="" width="600" height="489" /></a><p class="wp-caption-text">Figure 1. WPVI’s DTV signal on VHF channel 6, seen at 1:00 PM on June 12. Each of the sharp, rounded signals to its immediate right are FM radio stations.</p></div></h1>
<p>WPVI’s original digital signal on June 12 at 1 PM, as seen in Figure 1, wasn’t too shabby to begin with, and I could receive it quite easily on both my rooftop and attic antenna systems. It also came in nicely near the southwest wall of my house, on both floors, while using Eviant’s T7 Card portable digital TV set.</p>
<p>But there are always devils in the details, and you can see them quite clearly immediately to the right of WPVI’s flat-topped 8VSB carrier. Those numerous rounded peaks are FM broadcast stations, the closest of which is on 88.5 MHz (WXPN). Almost immediately adjacent is WRTI’s FM operation on 90.1, followed by WHYY on 90.9, etc.</p>
<p>So, what’s the problem? Those FM stations are co-located at the Roxborough TV tower farm, NW of Center City. And they present very strong signals that can slip through the filters in NITA converter boxes, resulting in interference to the channel 6 signal. What’s more, FM and TV signals mixing in converter box receivers will produce sum and difference frequencies that wind up right in a portion of the channel 6 spectrum.</p>
<p>So what’s likely happening is that closer-in TV viewers, who probably don’t have really long rabbit ears (a full-wave loop @ 85 MHz measures 3.53 meters, or 11.6 feet) are trying to pull in a signal that’s competing with strong, adjacent-channel signals from FM  broadcasters. Toss in the usual elevated noise floor from arc lamps, power transformers, air conditioning compressors, and refrigerator motors, and you have a sticky wicket indeed!</p>
<p></p><div id="attachment_425" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-24.jpg"><img class="size-full wp-image-425" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-24.jpg" alt="" width="600" height="482" /></a><p class="wp-caption-text">Figure 2. WPVI’s “boosted” DTV signal, as seen at 9:45 AM on June 22. It’s about 6 dB stronger than before.</p></div>
<div id="attachment_426" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-32.jpg"><img class="size-full wp-image-426" title="Figure 3" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-32.jpg" alt="" width="600" height="467" /></a><p class="wp-caption-text">Figure 3. This wide view of the TV spectrum from channel 2 to channel 13 shows how strong WPVI’s new signal is, compared to WBPH-9 and WHYY-12 (far right).</p></div>
<p>WPVI’s Special Temporary Authorization (STA) from the FCC definitely resulted in a stronger signal, as seen in Figure 2. And Figure 3, which shows a wider view of all low-band and high-band VHF channels, plus the FM band, reveals that WPVI’s broadcast is now the strongest TV signal coming out of Philadelphia. (Notice the comparatively weaker signal from WHYY-12, the 8VSB carrier to the far right.) But is WPVI even strong enough now?</p>
<p>In both of my spectrum analyzer screen grabs, you may notice that the FM radio station carriers get progressively weaker as the frequency increases. That’s because I’m using an FM trap to try and attenuate them. But that filter simply isn’t sharp enough to subdue WXPN, WRTY, and WHYY without also affecting the strength of WPVI’s signal.</p>
<p>Only precision signal filters with multiple poles and what we call “Hi-Q” sharp filter skirts can solve this problem. Except that filters like that are VERY expensive to manufacture, and not something you’d put into a $59 converter box or a $500 TV set.</p>
<p>The adjacent channel overload problem is compounded by the use of circular signal polarization from FM stations. This is done among other reasons so that their broadcast signals remain moderately stable in as your drive around in your car. But that’s no help to the home TV viewer, who may try to no avail to weaken the FM signals by positioning their TV antenna horizontally or vertically.</p>
<h1>
<div id="attachment_427" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-42.jpg"><img class="size-full wp-image-427" title="Figure 4" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-42.jpg" alt="" width="600" height="486" /></a><p class="wp-caption-text">Figure 4. A spectral view of WRGB-6 in Schenectady, NY, also “up against it” with multiple strong FM stations in close proximity.</p></div></h1>
<p>In case you think this is just a “big city” problem, look at Figure 4, which shows the FM carrier immediately upstream from WRGB-6 in Schenectady. Same problem – multiple strong FM stations that can play havoc with converter boxes and integrated TV sets are located immediately adjacent to WRGB’s 8VSB carrier. And similar complaints about lost reception are coming into the chief engineer’s office up there.</p>
<p>OK, SO WHAT DO WE DO NOW?</p>
<p>Unfortunately, there isn’t any “one size fits all” fix to this problem. But there are some things that may work.</p>
<p><strong>Inline signal attenuators:</strong> First of all, ATSC signals will come through at very low carrier-to-noise ratios, where analog NTSC signals won’t. It stands to reason that viewers close to the TV antenna farms have more than enough signal to begin with, so the counter-intuitive approach is to add attenuators inline with the antenna leads.</p>
<p>This will result in a weaker signal on channel 6, but will also drag down the levels of FM stations, too. Toss in an inexpensive FM notch filter, and at some point the TV receiver or converter box may be able to make better sense of the differences between the FM and channel 6 8VSB signals.</p>
<p>Of course, for this to work correctly, the attenuator should only be in the VHF antenna line, because it’s also going to clip signals from every TV station upstream from the filter, including high-band VHF and UHF. The VHF antenna should also be horizontally polarized, and not vertically polarized. That means flattening out those rabbit ears, or using a bar antenna or folded dipole on the roof, or in the attic.</p>
<p><strong>Eliminating noise:</strong> Another possible problem is broadband noise, as I mentioned earlier. It’s worth checking out DTV reception problems with as many of your home appliances and lights disconnected as possible, to see if some “hash” isn’t getting into your system and creating interference problems.</p>
<p>Such interference would manifest itself on the FM band (Surprise! FM isn’t completely noise-free) as well. Any offending appliances should be replaced or repaired, because they’re likely creating bigger interference problems with other electronic devices in and nearby your home.</p>
<p><strong>Using the wrong antenna:</strong> Of course, in more than a few cases, the problem seems to be one of trying to receive VHF channel 6 with a UHF antenna, which of course is akin to trolling for marlin with a Pocket Fisherman.</p>
<p>Many folks don’t realize that WPVI is now relocated a long ways away from its former position on UHF channel 64 (about 771 MHz), and that the small UHF loop antenna that used to work so well to pick up Jim Gardner and Action News is little more than a piece of decorative aluminum when it comes to watching VHF TV channels.</p>
<p>So what’s needed is a pair of longer rabbit ears, or even better yet, a folded dipole antenna that can be mounted on the side of a house, or in the attic – or even on the roof. The size would be ½ the length of a full-wave loop, or about 5 feet 9 inches. (5 feet is close enough for government work.)</p>
<p>This folded loop can be made out of copper tape, aluminum, or stiff wire – anything conductive. Even refrigerator drain hose (also copper) also works. Simply solder the leads of a 300-ohm coaxial balun to the open ends of the loop and run a piece of RG-6 to it, and you’re in business. <a href="http://www.wfu.edu/%7Ematthews/misc/dipole.html" onclick="javascript:pageTracker._trackPageview('/outbound/article/www.wfu.edu');">Here’s a link to a simple folded dipole design</a>, made from TV ribbon wire (twin lead). It’s scalable to any VHF channel.</p>
<p>Of course, you can also try a pair of conventional rabbit ears, but if you’re close in to the TV station (10 miles or less), stay away from amplified designs. They’ll only make the problem worse. On the other hand, WRGB’s chief engineer reported at least one viewer had complained about losing the signal on his rabbit ears antenna…30+ miles away. In that case, the amplifier is a good idea, but a rooftop or attic antenna is a lot more sensible.</p>
<p>MEANWHILE, BACK AT THE RANCH…</p>
<p>The problems that have been reported with reception of VHF channel 7 in New York City and Chicago appear to be arising from either improper antenna selection, or elevated noise floors, a common problem in cities. VHF signals have a tough time penetrating tall buildings, a task that UHF signal seem to handle with more aplomb.</p>
<p>But once again, a UHF antenna is not even close to resonance at 180 MHz (Channel 7). That’s about 1.67 meters, or 5.5 feet for a full-wave loop antenna. The good news is, everyday rabbit ears will usually do the trick here, but you’ll need to experiment with their polarization to see what works best. Fortunately, there aren’t any pesky FM radio station carriers lurking nearby.</p>
<p>What there IS, however, is lots of broadband noise. Figure 5 shows a spectral view of analog channels 7 through 13 in New York City, about 3.5 miles northeast of the Empire State Building, inside a 3<sup>rd</sup>-floor apartment where I’ve been researching an indoor TV antenna design.</p>
<p></p><div id="attachment_428" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-51.jpg"><img class="size-full wp-image-428" title="Figure 5" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-51.jpg" alt="" width="600" height="267" /></a><p class="wp-caption-text">Figure 5. Here’s a view of the TV spectrum from channel 7 through 13, as seen from the upper reaches of Fifth Avenue in New York City.</p></div>
<div id="attachment_429" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-61.jpg"><img class="size-full wp-image-429" title="Figure 6" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-61.jpg" alt="" width="600" height="258" /></a><p class="wp-caption-text">Figure 6. Whoops! Adding a preamplifier didn’t make matters better; it made them worse by elevating the noise floor. </p></div>
<p>So far, so good! But I wanted a little bit more separation between TV carriers and noise for more reliable DTV reception and to feed multiple TVs. So, I tested an inline preamplifier – with disastrous results. Figure 6 shows that the amplifier boosted channels 7 through 13 by almost 20 dB, but also kicked up the noise floor by the same amount – basically accomplishing nothing.</p>
<p>Lesson learned? I’ll have to come up with most of the gain in the antenna system, and try with different combinations of attenuators and preamps to see how I can add some “active” gain to the system without adding more noise and creating a new set of headaches.</p>
<p>I’ll be conducting more tests on channel 6 reception and also high-band VHF stations during the summer to see what practical solutions myself and others can come up with. Look for more coverage of this issue later in the summer. In the meantime, email any questions and observations you may have about “difficult” DTV stations, so we can share them with other readers.</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April  8, 2010 12:55 PM</b>
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
			<?=getComments(3734)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3734)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-hdtv-tech-talk-ive-got-the-lowband-dtv-blues-june-2009.php" type="text/javascript" charset="utf-8"></script>
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