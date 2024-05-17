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
		AND e.entry_id = 3735";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3735 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3735 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3735";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-pioneer-elite-bdp09fd-bluray-player-may-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3735";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)</title>
	<meta name="keywords" content="blu ray, ray player, analog audio, linear pcm, hdmi cable, audio, hdmi, player, video, output, cable, –, bdp, analog, ’s, dts, digital, blu, ray, pioneer, high, formats, dvd, dolby, playback" />
	<meta name="description" content="Pioneer’s top-line BD player is built like a tank, has whisper-quiet operation, and delivers top-notch audio and video." />
	<meta name="title" content="HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-pioneer-elite-bdp09fd-bluray-player-may-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Pioneer’s top-line BD player is built like a tank, has whisper-quiet operation, and delivers top-notch audio and video." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3735', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-pioneer-elite-bdp09fd-bluray-player-may-2009.php">HDTV Expert - Product Review: Pioneer Elite BDP-09FD Blu-Ray Player (May 2009)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April  8, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=366&category=Blu-ray">Blu-ray</a></b>
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
          <p>Back at CES, Pioneer unveiled their latest optical disc player masterpiece, the BDP-09FD. This player has all the bells and whistles a home theater buff could hope for, from dual HDMI outputs to 7.1 discrete analog audio connections, 4 GB of internal flash memory, and 16-bit video processing, not to mention eight Wolfson digital-to-analog (DAC) converters to drive the audio outputs.</p>
<p>Of course, that all comes at a cost – about $2,200 at full retail. And the BDP-09FD isn’t for everyone. The question is, does the player’s performance justify the price tag?</p>
<h1>
</h1><p></p><div id="attachment_409" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-13.jpg"><img class="size-full wp-image-409" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-13.jpg" alt="" width="600" height="321" /></a><p class="wp-caption-text">Figure 1. Pioneer’s BDP-09FD is a solid, no-nonsense Blu-ray player with stealth design.</p></div>
<p>OUT OF THE BOX</p>
<p>This is not everyman’s BD player. It’s quite large, measuring 16.5” W x 14.4” D x 5.7” H, and tips the scales at 31.5 lbs. (You read that last part correctly, almost 32 pounds!) What you gain is a rock-steady chassis with a more precise drive mechanism – a slight bump against the player won’t cause the optical reader to skip tracks.</p>
<p>The exterior housing is finished in a glass black – very high-tech – while the alphanumeric display uses orange-yellow LEDs. Directly below the display (and separated by a blue power-on LED) is the disc drawer. An oversized power button on the lower left is complemented by an equally oversized “play” button on the lower right front of the player.</p>
<p>There aren’t a lot of controls besides those, aside from two small buttons marker “Pure Audio” and “Resolution” to the left of the display, and the drawer open/close, chapter advance/reverse, pause, and stop buttons to the right. Two small red indicators show when the Pure Audio mode is switched on, and when the HDMI output is active.</p>
<p>The rear panel is loaded with connectors. In addition to a standard HDMI 1.3 output, there’s a second HDMI connection, plus YPbPr BNC jacks for analog HD playback. You’ll also find optical and coaxial SPDIF audio connectors for 5.1 channel playback.</p>
<p>Pioneer has also provided eight discrete RCA jacks for multi-channel analog audio output directly to your 5.1 or 7.1 AV receiver. This is handy if your receiver doesn’t decode the latest HDMI audio formats, such as Dolby True HD, DTS Master Audio, and DTS High Resolution Audio.</p>
<p>Now, I have to pause here and point out one absurdity of Pioneer’s thinking. Packed within the shipping carton of this $2,200 Blu-ray player are two cables. One is an Ethernet cable for connecting the BDP-09FD BD-Live function, along with getting firmware updates for the player. It’s a nice thought, but too short at six feet – my house has a wireless router in the basement, and I’d need at least a 50-footer to hook things up.</p>
<p>As for the other cable, take a guess. How about a six-foot HDMI cable? (Nope.) A three-foot HDMI cable? (Wrong!) OK, how about a six-foot component video cable? (Not even close.)</p>
<p>No, the extra cable that Pioneer has so graciously included with your $2,200 Blu-ray player is a composite video cable with analog stereo audio…the old, familiar “AV” cable, colored red, white, and yellow.</p>
<p>YOU’VE GOT TO BE KIDDING ME!!! Who the heck is going to use a composite video connection with a Blu-ray player? Would it kill Pioneer to toss in a nice HDMI cable? (6’ is OK; 12’ is better) Or, just leave out the composite video cable altogether – it’s almost a slap in the face. Someone <span style="text-decoration: underline;">really</span> dropped the ball on this at the factory.</p>
<h1>
</h1><p></p><div id="attachment_410" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-23.jpg"><img class="size-full wp-image-410" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-23.jpg" alt="" width="600" height="237" /></a><p class="wp-caption-text">Figure 2. The rear panel has every AV connection you’ll need, and then some!</p></div>
<p>MENUS AND FEATURES</p>
<p>This player is loaded for bear. You name it; the function is in there someplace.  The latest firmware version is 2.46, which lets the player convert the DTS-HD format to linear PCM output through the HDMI connector, or to 7.1 channels of analog audio. In addition, the player supports Dolby TrueHD, Digital and Digital Plus, DTS Master Audio, MPEG2 AAC, and Linear PCM formats.</p>
<p>As far as video is concerned, the BDP-09FD is compliant with HDMI v1.3 and can play back Deep Color content at 1080p/60 frame rates. According to the owner’s manual, you should use a High Speed HDMI cable when outputting video in this mode.</p>
<p>Presumably, High Speed HDMI cables have lower tilt or waveform distortion than regular cables, but I don’t see that you’d have much of a problem either way if your cable runs are short – say, less than six to eight feet. Both the Main and Sub HDMI jacks can be enabled for high-speed operation.</p>
<p>Initial setup goes quickly with this player. The HDMI connection automatically communicates with your TV, monitor, or projector’s EDID (Electronic Display Interface Data) to determine the optimum output resolution and frame rate, which will usually be 1080p/60 or 1080p/24.</p>
<p>You can also manually set the resolution and frame rate. Just make sure you use the main HDMI output – the “sub” HDMI jack only carries 2-channel linear PCM audio. I should also mention that the KURO Link function for control of all devices through HDMI interconnects only works through the Main jack.</p>
<p>If your AV receiver is not quite up-to-date, you’ll want to have the BDP-09FD do the Dolby/DTS decoding and pass the audio as analog signals to the rear panel. This can be selected quickly in the Setup menu. Note that digital audio output through the HDMI and SPDIF connectors is disabled in this mode.</p>
<p>Other selections you’ll need to make are the output resolution and aspect ratio (default setting is 16:9). The player can output video at 480i, 4880p, 720p, 1080i, and 1080p resolutions, but only Blu-ray discs will work with all of them. Red laser DVDs will most likely limit your choices to 480i or 480p output, thanks to copy protection bits encoded on the DVD.</p>
<p>The Ethernet interface is conventional, with an option to have your wireless router or hub assign an IP address using Dynamic Host Control Protocol (DHCP). I suggest using this option unless you are fairly IP-savvy and can assign all of the required addresses, including the DNS addresses of your Internet service provider.</p>
<p>If you are facing a cable connection logistics problem (as I did), you may want to investigate using a wireless bridge – these gadgets emulate an Ethernet port tied to a wireless adapter, and are popular for hooking up printers to wireless networks. You’ll need to connect the bridge directly to your router or hub to configure it. Once that’s done, the BDP-09FD can sit anywhere in your house and still remain connected to the Internet.</p>
<p>IN OPERATION</p>
<p>I took the BDP-09FD for a test drive using both my Mitsubishi HC6000 3LCD 1080p front projector and a Pioneer PRO-111FD 50-inch Kuro plasma TV. Test DVDs included Iron Man, The Dark Knight, and BBC’s Planet Earth.</p>
<p>Let me mention once again the silky-smooth operation of the disc tray. It glides in and out effortlessly with no wobble, which would indicate the presence of quite a few ball bearings in its tracks. It takes the player about 30 seconds to boot up before it’s ready for a disc, and another 15 to 17 seconds before that disc is ready to play. (This holds true even for red laser DVDs.)</p>
<p>Since my AV receiver (Denon’s AVR-788) wouldn’t support the advanced Dolby and DTS BD audio formats, I opted to use the player’s analog audio outputs and let Pioneer do the decoding. It’s a great way to go, although my home system only supports 5.1-channel playback at present.</p>
<p>Picture quality from all three discs was as good as anything I’ve seen from my Reon-equipped Samsung BD-P1200 (the HC6000 also has Reon processing onboard) – excellent detail and dynamic range, with no evidence of false contouring. Unfortunately, the BD standard only calls for 8-bit video, and you can see the result in scenes that show deep blue skies – visible contour lines.</p>
<p>The BDP-09FD took care of that nicely, particularly in <em>Iron Man</em> where Tony Stark first attacks the terrorists in what’s supposed to be Afghanistan. Watch as he sails through the skies, pursued by a pair of F-22 Raptors. The blue sky gradient changes frequently from scene to scene, but you shouldn’t see any contouring along the way.</p>
<p><em>The Dark Knight</em> shows off the player’s ability to pull out shadow detail in dark scenes, of which there are plenty in this film. I looked carefully for low-level noise and didn’t see much of it, especially around objects with green and blue coloring.</p>
<p>To top things off, I spun up <em>Ice Worlds</em> from <em>Planet Earth</em>. If you don’t own this boxed set on Blu-ray, go out right now and buy a copy – these are reference-grade HD discs. <em>Ice Worlds</em> has lots of high-contrast subject matter, along with the aforementioned deep blue sky gradients and underwater photography. All of it showed up beautifully, free of noise and other digital artifacts that I’ve seen on lower-cost players.</p>
<p>As for the audio, it came through with plenty of dynamic range, and no audible sampling artifacts. (Both <em>Iron Man</em> and <em>The Dark Knight</em> have plenty of explosions that task even the best audio systems.) The sound playback was as good as I’ve experienced in the best movie theaters, with great presence and spatial separation in the surround channels. (Dang, now I have to go find two more speakers and upgrade to 7.1 playback!)</p>
<p>CONCLUSION</p>
<p>If you really want a superlative Blu-ray player, the BDP-09FD is for you. It oozes high quality all around and delivers excellent image and audio quality. My guess is, it will hold up for a long time, probably longer than your flatscreen TV. The video quality wasn’t substantially better than lower-cost players with high-end video processing, but the build quality is.</p>
<p>Where you’ll really notice the difference is in the internal audio processing, particularly if you opt to go analog to your existing receiver. The improvement in dynamic range over conventional SPDIF connections, even with 5.1 movies, is one you can hear – there’s just more audio to play with, from the subtlest sounds to swelling music and explosive special effects.</p>
<p><strong>Pioneer Elite BDP-09FD Blu-ray Player</strong><br /><strong>MSRP: $2,199</strong></p>
<p><strong>Specifications:</strong><br />
Dimensions: 16.5” (W) x 5.6” (H) x 14.2” (D)<br />
Weight: 31 pounds</p>
<p>Analog video output formats: composite, S-video, BNC YPbPr (480i/29.97, 480p/59.94, 720p/59.94, 1080i/29.97)<br />
Digital video output formats: 2x HDMI 1.3 (480p/59.94, 720p/59.94, 1080i/29.97, 1080p/59.94, 1080p/23.97)<br />
Analog audio output: 1x RCA (Stereo)<br />
Digital audio output: Toslink, HDMI (bitstream or PCM), Optical/Coaxial SPDIF<br />
Supported playback formats: BD-ROM, BD-RE, BD-R, DVD VIDEO, AUDIO CD, DVD-RW, DVD-R, DVD-R DL, DVD+R/RW, CD-R, CD-RW, CD ROM</p>
<p>Supported audio formats: Dolby TrueHD, Dolby Digital/Plus, DTS-HD Master Audio, DTS-HD HR Audio, DTS Digital Surround, MPEG, MPEG2 AAC, Linear PCM</p>
<p>LAN Interface: 100BaseT Ethernet</p>
<p><strong>Pioneer Electronics USA</strong><br /><strong>2265 E. 220th Street</strong><br /><strong>Long Beach, CA 90810</strong><br /><strong>(213) 746-6337 </strong></p>
<p><strong> </strong></p>
<p><a href="http://tinyurl.com/ophcl5" onclick="javascript:pageTracker._trackPageview('/outbound/article/tinyurl.com');">http://tinyurl.com/ophcl5</a> <strong> </strong></p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April  8, 2010 12:42 PM</b>
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
			<?=getComments(3735)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3735)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-pioneer-elite-bdp09fd-bluray-player-may-2009.php" type="text/javascript" charset="utf-8"></script>
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