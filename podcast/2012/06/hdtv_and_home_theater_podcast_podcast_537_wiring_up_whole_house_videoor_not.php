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
		AND e.entry_id = 4860";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4860 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4860 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4860";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-537-wiring-up-whole-house-videoor-not.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4860";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&hellip;Or Not" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&hellip;Or Not" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&hellip;Or Not" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&hellip;Or Not</title>
	<meta name="keywords" content="video server, frequency range, wireless option, home theater, powerline adapters, video, wireless, home, range, audio, sound, wave, using, want, frequency, room, well, house, much, might, get, frequencies, stream, don’t, powerline" />
	<meta name="description" content="The Mac Mini Video Server project has brought in a steady stream of interested readers over the years.  Who wouldn&amp;acirc;��t want to build their own movie jukebox for a fraction of the cost of a professionally installed movie server?  The first thing you notice about the project is how easy it is, the second thing you notice is the cost and the third thing might be the fact that you just aren&amp;acirc;��t wired up for it." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&amp;hellip;Or Not" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&amp;hellip;Or Not" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-537-wiring-up-whole-house-videoor-not.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The Mac Mini Video Server project has brought in a steady stream of interested readers over the years.  Who wouldn&amp;acirc;��t want to build their own movie jukebox for a fraction of the cost of a professionally installed movie server?  The first thing you notice about the project is how easy it is, the second thing you notice is the cost and the third thing might be the fact that you just aren&amp;acirc;��t wired up for it." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4860', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-537-wiring-up-whole-house-videoor-not.php">HDTV and Home Theater Podcast - Podcast #537: Wiring Up Whole House Video&hellip;Or Not</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>June 29, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=314&category=General Interest">General Interest</a></b>
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
<h3 id="internal-source-marker_0.3000635484327816">Home Theater Terms</h3>
<ul>
<li><strong>Crossover</strong> &#8211; An electrical filter that includes high-pass, low-pass and band pass filters to divide the audible frequency spectrum (20 Hz &#8211; 20 kHz). Most loudspeaker drivers are incapable of reproducing the entire audio spectrum, so the crossover is used to make sure the correct frequencies are sent to the drivers that are built to reproduce a particular sound range. Without a crossover every driver would be sent the entire frequency range, resulting in muddied and sub-optimal audio experience.  A tweeter is a driver designed to produce high audio frequencies (typically 2,000 Hz to 20 kHz). Midrange drivers, sometimes called “squawkers,” are designed to reproduce the frequency range from approximately 300–5000 Hz. A woofer is the driver designed to produce the lowest frequency sound, typically from 20 Hz to 1000 Hz.  A full-rangedriver is designed to reproduces as much of the audible frequency range as possible.</li>
</ul>
<ul>
<li><strong>Frequency Response</strong> &#8211; Measures how accurately an audio system reproduces sound across the entire audio spectrum. This is a good measure of how well a system will perform in total because good sound reproduction requires that all audible frequencies (20 Hz &#8211; 20 kHz) are reproduced at roughly the same volume.  Some will argue that the highest and lowest frequencies are less important because the human ear doesn’t hear them as well.  Some manufacturers will quote a full-range frequency response for their speakers without specifying the decibel boundaries.  This really isn’t valuable to you at all. Subtle sound level variations across the audio band can impact the performance of the system dramatically, especially if they&#8217;re spread over a fairly wide band of frequencies.</li>
</ul>
<ul>
<li><strong>Standing Wave</strong> &#8211; This is when you do the wave at a sporting event just using your arms, so you don’t have to stand up and sit down again each time the wave comes back around.  But in home theater terms, this is a sound wave that remains in a constant position, sometimes also referred to as a stationary wave.  It occurs between two parallel walls when the reflection from each wall actually serves to reinforce the sound wave itself.  Believe it or not, it’s actually quite common in home theaters because many of us have rectangular shaped rooms.  The standing wave will actually remain present in the room for longer than the sound should be present, producing a very muddy audio experience.  It can be solved by simply changing the location of your speakers or subwoofer (moving them further from the wall), or by adding acoustic treatments to the walls themselves.</li>
</ul>
<h3>Wiring Up Whole House Video&#8230;Or Not</h3>
<p>The <a href="http://www.htguys.com/mac-mini-video-server">Mac Mini Video Server</a> project has brought in a steady stream of interested readers over the years.  Who wouldn’t want to build their own movie jukebox for a fraction of the cost of a professionally installed movie server?  The first thing you notice about the project is how easy it is, the second thing you notice is the cost and the third thing might be the fact that you just aren’t wired up for it.</p>
<p>Many new homes are being built from the ground up with Internet and home networks in mind.  But if your home was built before this trend got rolling, you might not be so lucky.  So how do you get video from one room to another to enjoy something like the <a href="http://www.htguys.com/mac-mini-video-server">Mac Mini Video Server</a>?  The obvious choice is to have a professional out to your house to run the wiring.  Depending on how much, how far, and how many walls are in the way, this could be anywhere from inexpensive to prohibitively pricey.</p>
<h4>Wireless Options</h4>
<p>If you don’t have network connections in each room, odds are you have a wireless network installed so your laptop, tablet or smartphone can get on the Internet while you’re on the couch. We don’t tend to recommend wireless networks for video distribution because they just haven’t been the most reliable technology for us in the past &#8211; especially if you have multiple 1080p Blu-ray videos going at once.</p>
<p>If you want to give the wireless option a try, make sure you’re using 802.11n at every point in the chain: from the source, through the wireless router and all the way to the destination.  Packet loss in a wireless network can cause all kinds of annoyances in video streaming, so you want to have a strong signal everywhere as well.  If you have some dead spots, try using a Wireless Repeater like the <a href="http://www.amazon.com/gp/product/B005D5M136/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005D5M136">Diamond Multimedia 300Mbps 802.11n Wireless Range Extender (WR300N)</a>. Braden has one and it works flawlessly to eliminate dead spots that used to be too far from the wireless router.  It costs around $55 and is eligible for <a href="http://www.amazon.com/gp/prime?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;ref_=sr_tc_sc_2_0&amp;qid=1326638658&amp;sr=8-2-tc%20via">Amazon Prime</a>.</p>
<p>Another wireless option you can try is beaming just the video itself, not the network stream, to televisions around the house.  To do this you place a bank of Apple TV or equivalent devices near your server machine and wire them directly.  You then transmit the output of those players to TVs placed around your home. Some wireless AV transmitters allow you to send IR back to the playback device for control. If yours doesn’t, you may need yo find an IR transmitter/reciever as well.<br />
<strong><br />
Some options for wireless video are:</strong><br />
<a href="http://www.amazon.com/gp/product/B005L9ZZ32/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005L9ZZ32">Actiontec My Wireless TV WiFi / HDMI Multi-Room Wireless HD Video Kit</a>, $195</p>
<ul>
<li>Full 1080p60 2D &amp; 3D Hi-definition video at up to 150 ft range</li>
<li>IR and USB back-channel allows full control of source device from remote room</li>
<li>Modern HDCP 2.0 support; HDMI 1.4</li>
</ul>
<p><a href="http://www.amazon.com/gp/product/B005H3AU1Y/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005H3AU1Y">Nyrius NAVS500 HD 1080p HDMI Digital Wireless A/V System with IR Remote Extender</a>, $199</p>
<ul>
<li>Supports video resolution 1080p, 1080i, 720p, 720i, 576p, 576i, 480p, 480i</li>
<li>Powerful long range signal transmits uncompressed high definition video and audio through walls, ceilings, floors up to 100ft without latency</li>
<li>Control your HD devices from any room in your house with the included infrared remote extender</li>
<li>Easy to install and set up eliminating expensive, messy wires; Control your PC&#8217;s keyboard and mouse using the USB connection remotely on any TV in your house</li>
</ul>
<h4>Wired Options</h4>
<p>If you think wireless might be too flakey and you want to go the hard-wired route, there are still plenty of choices available.  The most obvious answer is <a href="http://www.amazon.com/mn/search/?_encoding=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=ur2&amp;camp=1789&amp;creative=390957&amp;field-keywords=homeplug%20av2&amp;url=search-alias%3Delectronics&amp;sprefix=homep%2Celectronics%2C291#/ref=nb_sb_ss_i_1_6?url=search-alias=electronics">Ethernet over Powerline</a>, sometimes called HomePlug (or HomePlug AV if you want to stream video).  There are options from <a href="http://www.amazon.com/gp/product/B005GCSZD6/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005GCSZD6">ZyXEL</a>, <a href="http://www.amazon.com/gp/product/B005O0R9UA/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005O0R9UA">Belkin</a>, <a href="http://www.amazon.com/gp/product/B007ILFFS6/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B007ILFFS6">Netgear</a> and a host of others, ranging in price from $65 to $150 that all claim to be able to stream 500 Mbps.  That much data is more than plenty to support multiple HD video streams and a bit of web surfing and email for kicks.</p>
<p>We’ve been using Powerline Ethernet adapters for years and have seen the technology come a long, long way.  In the early days, you’d be lucky to get dial-up speeds between to adapters, these days the rates are much better.  Although we’ve never seen anything close to 500 Mbps, we have seen upwards of 44 Mbps in our tests &#8211; which is still more than enough to stream an HD video.  We have found that success varies widely by the maker of the Powerline adapters and also what plugs you connect them into in your home.  Your mileage may vary.</p>
<p>Powerline isn’t your only choice.  In addition to power, most homes have Coax cable run to each room to distribute an Antenna or Cable/Satellite signal.  If you aren’t already using the coax line, you could hijack it for networking using a <a href="http://www.amazon.com/mn/search/?_encoding=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=ur2&amp;camp=1789&amp;creative=390957&amp;field-keywords=moca&amp;url=search-alias%3Daps">MoCA</a> adapter.  These are harder to come by, but work very reliably in our experience and from the emails we’ve received about them.  They tend to be a bit more expensive as well, running in the $135-$200 range.  But they might be a bit more reliable for you if the powerline adapters don’t work well in the plug closest to your TV.</p>
<p>And if all else fails, similar to the second wireless option, you could try streaming just the video output from your playback device to the TVs you have in various rooms.  The <a href="http://www.amazon.com/gp/product/B0009MCNX6/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0009MCNX6">Dynex WS-007 RF Modulator Video Converter</a> costs just $13 and sends video on either channel 3 or 4, but it won’t do high definition. <a href="http://www.amazon.com/gp/product/B001F4TF9W/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B001F4TF9W">Philips</a> makes one for $8.50.  To get a similar solution that supports high def you’ll need to step up to the <a href="http://www.amazon.com/gp/product/B004KNBGQK/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B004KNBGQK">Vanco 280557 HDMI Over Single Coaxial Cable Extender</a> for a mere $466.  At that point, though, you might want to consider running the Ethernet cables instead.</p>
<h4>Conclusion</h4>
<p>If you haven’t built a video server for your home, we can’t tell you enough how much you’re missing. If the wireless route has been flakey for you and you don’t want to take on the expense of running dedicated Ethernet wires, there are many, many ways to get the video distributed throughout your home.  Don’t give up.  Start with the simplest solution and work your way up from there.  We’re fairly confident that some skillful searches at <a href="http://www.amazon.com/?%5Fencoding=UTF8&amp;tag=hdtvandhometh-20">Amazon</a></p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-06-29.mp3">Download Episode #537</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>June 29, 2012 12:37 AM</b>
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
			<?=getComments(4860)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4860)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-537-wiring-up-whole-house-videoor-not.php" type="text/javascript" charset="utf-8"></script>
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