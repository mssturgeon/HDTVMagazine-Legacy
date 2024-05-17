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
		AND e.entry_id = 4798";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4798 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4798 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4798";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-530-harman-kardon-avr-2650.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4798";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650</title>
	<meta name="keywords" content="dolby volume, harman kardon, bridge iiip, music home, network connectivity, dolby, volume, audio, receiver, music, better, network, remote, harman, appletv, much, sound, processing, set, kardon, buy, mode, sounded, connectivity, want" />
	<meta name="description" content="Ever since we saw (or better yet heard) our first demo of Dolby Volume we couldn&amp;acirc;Äôt wait to find a reason to upgrade one of our receivers with this capability. That&amp;acirc;Äôs going on four years now! So when Ara&amp;acirc;Äôs Yamaha &amp;acirc;ÄúBroke&amp;acirc;Äù, he thought what better excuse to buy a receiver with Dolby Volume. The receiver he settled on was the Harman Kardon 2650." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-530-harman-kardon-avr-2650.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Ever since we saw (or better yet heard) our first demo of Dolby Volume we couldn&amp;acirc;Äôt wait to find a reason to upgrade one of our receivers with this capability. That&amp;acirc;Äôs going on four years now! So when Ara&amp;acirc;Äôs Yamaha &amp;acirc;ÄúBroke&amp;acirc;Äù, he thought what better excuse to buy a receiver with Dolby Volume. The receiver he settled on was the Harman Kardon 2650." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4798', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-530-harman-kardon-avr-2650.php">HDTV and Home Theater Podcast - Podcast #530: Harman Kardon AVR 2650</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>May 10, 2012</b>
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
<h3 id="internal-source-marker_0.40546959507661584">Harman/Kardon AVR 2650</h3>
<p>Ever since we saw (or better yet heard) our first demo of Dolby Volume we couldn‚Äôt wait to find a reason to upgrade one of our receivers with this capability. That‚Äôs going on four years now! So when Ara‚Äôs Yamaha ‚ÄúBroke‚Äù, he thought what better excuse to buy a receiver with Dolby Volume. The receiver he settled on was the Harman Kardon 2650 (<a href="http://www.amazon.com/gp/product/B0054N9RQQ?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B0054N9RQQ&amp;qid=1336267175&amp;ref_=sr_1_1&amp;sr=8-1">Buy Now $650</a>).</p>
<h4>Features:</h4>
<ul>
<li>HDMI¬Æ v.1.4a with 3-D, Deep Color and Audio Return Channel</li>
<li>95-watt, 7-channel amplifier</li>
<li>Network connectivity for accessing music through your home network</li>
<li>Dolby¬Æ Volume</li>
<li>Dolby TrueHD and DTS-HD Master Audio‚Ñ¢ decoding</li>
<li>Logic 7¬Æ processing</li>
<li>The Bridge IIIP connectivity (The Bridge IIIP sold separately)</li>
<li>EZSet/EQ‚Ñ¢ equalization</li>
<li>Multizone functionality</li>
<li>Five HDMI inputs</li>
<li>Connection for a SIRIUS Connect‚Ñ¢ home tuner (sold separately)</li>
<li>Multidevice, programmable/learning remote</li>
<li>Headphone jack with HARMAN headphone surround mode</li>
<li>Upgradable firmware via USB port</li>
<li>Detachable IEC power cord</li>
</ul>
<h4>Setup</h4>
<p>Setup was typical for any modern AVR. Plug in your video sources, connect speakers, and power and you are ready to calibrate. The first thing we did was rename the sources to something that made sense. The AppleTV was connected to the Game input so that was renamed to AppleTV. Cable/Sat was renamed to DirecTV and Blu-ray was left alone. The menus are simple to navigate and simple to use. Being on screen makes life so much easier to do your setup. When you change volume, inputs, or modes the 2650 superimposes the information on the screen. No more squinting to see how loud or what processing mode you are in.</p>
<p>Next up was to run the automatic calibration function. Like other AVRs you are given a microphone that you plug into the front of the receiver and place where you want the audio to be tuned for. The whole process takes about five minutes and then you are up and running!</p>
<h4>Performance</h4>
<p>The performance of this receiver is outstanding but with caveats. The calibration sets the EQ as well as levels. We found the level setting to be fine. But the audio processing really muddied the sound. The dialog got lost in the effects and music. Once we turned off audio enhancements and set the EQ to flat everything sounded much much better! We left the audio processing in auto and the 2650 was able to find the right mode, Dolby Digital, DTS Master Audio, Pro Logic and so on.</p>
<p><em><strong>Movies</strong></em><br />
Watching movies is a lot fun with this receiver! Audio sounds fantastic. The 2650 gave dialog more texture and effects more pop. The room seemed more alive. Ara‚Äôs new go to movie sequence for testing audio is now the last fight in Real Steal. The receiver made his Klipsh speakers sound new. This sequence was tested with both Dolby Volume on and off. Dolby volume did a great job of keeping surges in audio at bay. We‚Äôll talk a bit more about Dolby Volume later. The 2650 will upscale your sources to 1080p but it does not offer a ton in the way of video processing. This machine is all about audio.</p>
<p><em><strong>Music</strong></em><br />
We really enjoyed listening to music on the 2650. Harman Kardon has a technology called Logic 7. It takes regular two channel audio and turns it into 7.1 audio. We were very pleased with what it did to our music. It opened it up and surrounded you with sound. It did very well with classical music and acoustic performances. Rock sounded good too but the effect was less pronounced. With that said, we felt Dolby‚Äôs Pro Logic IIX Music sounded better.</p>
<p><em><strong>Dolby Volume</strong></em><br />
As we said at the beginning of the review we couldn‚Äôt wait for a receiver with Dolby volume! And this receiver performed exactly as advertised with it. There are three settings for this feature, low, medium, and high. For TV viewing we went with medium and for Blu-rays we went with low. Dolby Volume worked exactly as advertised. It kept the audio at a level that was consistent across all viewing material.</p>
<p>A nice feature of the 2650 is that it allows you to have a different setting for Dolby Volume per input. This is a good thing. The Derderian‚Äôs don‚Äôt have any sleeping babies in their house and most of their viewing is done at a time when the neighbors wouldn‚Äôt be bothered by loud noises. So their default mode on movie inputs (AppleTV and Blu-ray) are Dolby Volume set to off. For TV it was left on because it does a great job dealing with super loud commercials. If you have a Harmony Remote we recommend putting the Audio Effects button into your soft keys. This way if you do watch a movie late in the evening its very simple to turn on Dolby Volume. For Ara, Dolby Volume is the best feature on the $650 receiver.</p>
<h4>Odds and Ends</h4>
<p>The 2650 has a bunch of features that you may or may not find you need:</p>
<ul>
<li><strong>The Bridge IIIP</strong> &#8211; allows you to connect your iOS device with a cable. Since we have an AppleTV connected to receiver we found the missing functionality to be a non-issue. With the AppleTV or other set top box, like the NeoTV, you can play your library from somewhere on your network and that‚Äôs a better way to go than physically tethering your device to the receiver.</li>
<li><strong>Network connectivity for accessing music through your home network</strong> &#8211; Even if you don‚Äôt have a set top box connected to the AVR you will have access to music over the internet of on a computer on your local network. There is a substantial list of radio stations available to listen to. The quality varies and while some stations sounded good others were slightly better than AM radio. But if you have a radio station you want to hear on the other side of the Earth, chances are the 2650 can stream it to you.</li>
<li><strong>Menus</strong> &#8211; The 2650 has a basic GUI that is viewable through your TV. It‚Äôs straightforward to navigate and simple to use.</li>
<li><strong>Remote Control</strong> &#8211; The remote is nicely laid out and is multi functional. We didn‚Äôt spend much time with the remote since we immediately programmed our Harmony One to control the receiver.</li>
</ul>
<h4>Should I Buy the 3650 for $200 more?</h4>
<p>The additional $200 get you 15 more watts (which is imperceptible to the human ear), an additional HDMI input on the front panel and the iPod dock is included. As far as we are concerned there isn‚Äôt enough there to warrant paying the additional $200. If you want iOS connectivity buy an AppleTV for $99 and ¬†connect wirelessly through Airplay. You‚Äôll get way more for your money.</p>
<h4>Conclusion</h4>
<p>The Harman Kardon 2650 is an excellent sounding receiver that will fill your room with sound. If you want a receiver that supports HDMI1.4 for 3D and Dolby Volume to avoid riding the remote $650 is not much to ask, There are other receivers out there that will do more for the same price. but none of them will sound any better.</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-05-11.mp3">Download Episode #530</a></p>
<p>&nbsp;</p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>May 10, 2012 10:40 PM</b>
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
			<?=getComments(4798)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4798)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-530-harman-kardon-avr-2650.php" type="text/javascript" charset="utf-8"></script>
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