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
		AND e.entry_id = 5076";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5076 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5076 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5076";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2013/03/hdtv-and-home-theater-podcast-podcast-575-tis-the-season-for-receivers-yamaha-denon-onkyo-and-pioneer.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5076";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer</title>
	<meta name="keywords" content="home theater, announced new, high definition, portable device, ios device, receivers, vsx, new, receiver, device, support, video, theater, models, home, denon, avr, audio, yamaha, onkyo, series, should, want, pioneer, announced" />
	<meta name="description" content="In an effort not to be outdone by their peers, many Consumer Electronics companies try to announce their new products at the same time everyone else does. This makes it very easy for us the consumer to know what we want to buy. We don&amp;acirc;��t have to wait for that last straggler to tell us what&amp;acirc;��s coming before we plunk down our hard-earned cash. Right now it&amp;acirc;��s AV receiver season. If you&amp;acirc;��re in the market for a new receiver for your home theater, have we got a show for you." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2013/03/hdtv-and-home-theater-podcast-podcast-575-tis-the-season-for-receivers-yamaha-denon-onkyo-and-pioneer.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In an effort not to be outdone by their peers, many Consumer Electronics companies try to announce their new products at the same time everyone else does. This makes it very easy for us the consumer to know what we want to buy. We don&amp;acirc;��t have to wait for that last straggler to tell us what&amp;acirc;��s coming before we plunk down our hard-earned cash. Right now it&amp;acirc;��s AV receiver season. If you&amp;acirc;��re in the market for a new receiver for your home theater, have we got a show for you." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5076', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2013/03/hdtv-and-home-theater-podcast-podcast-575-tis-the-season-for-receivers-yamaha-denon-onkyo-and-pioneer.php">HDTV and Home Theater Podcast - Podcast #575: Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>March 22, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>Tis the Season for Receivers: Yamaha, Denon, Onkyo, and Pioneer</h3>
<p dir="ltr">In an effort not to be outdone by their peers, many Consumer Electronics companies try to announce their new products at the same time everyone else does. This makes it very easy for us the consumer to know what we want to buy. We don’t have to wait for that last straggler to tell us what’s coming before we plunk down our hard-earned cash. Right now it’s AV receiver season. If you’re in the market for a new receiver for your home theater, have we got a show for you.</p>
<p dir="ltr">The Television used to be the center of the home theater or family room entertainment system. But for a few years now, the A/V receiver has taken a much more central role. It is primarily responsible for getting the audio from all your devices (Cable or Satellite tuner, Blu-ray player, Streaming box, Game System, …) and blasting it out in awesomely loud, surround sound to all the speakers you can hook up to it. Just this function alone of course makes the receiver indispensable in any home theater.  As we’ve often said, HDTV without surround sound is only half of the experience.</p>
<p dir="ltr">But over the last 7 or 8 years, receivers have moved firmly into the video device category as well. In addition to being the audio hub, the receiver can also serve as the hub for all the video signals from all your devices, so you only need to run one cable to your TV or Projector. Quite a convenience. But beyond simply switching video for you, many receivers include some fairly heavy-duty video processors in them to assist with video format conversion such as upscaling SD to HD. For some, video features in a receiver can be just as important as the audio functionality.</p>
<p dir="ltr">The recent smartphone and tablet revolution hasn’t skipped the home theater market either. Some receivers allow Internet streaming of audio content directly to the receiver, without the need for an external streaming box or computer. Some offer Bluetooth playback so you can play from a portable device without wires. Some offer dedicated wires to connect to or USB ports to plug into. Many even include a smartphone app you can use to control the unit without the need for a traditional IR remote.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.whathifi.com/news/yamahas-new-75-series-rx-v-av-receivers-starting-from-300-announced-in-usa"><strong>Yamaha&#8217;s new 75-series RX-V AV receivers, starting from $300, announced in USA</strong></a></p>
<p dir="ltr">Yamaha USA has announced five new models for their 2013 RX-V lineup. Prices start at $300 for the RX-V375, $450 for the RX-V475, $550 for the RX-V575 and $650 for the RX-V675.  All of those should be available for sale this month. The top of the line RX-V775WA should be available in April for $850.</p>
<p dir="ltr"> All models, even the $300 unit, use upgraded discrete amplification, Burr Brown digital-to-analog converters (DACs), which should improve sound quality and are compatible with 3D and ultra high definition (UHD) TVs. All models except for the entry level RX-V375 have a front-panel MHL (Mobile High Definition Link) input for HD video content from a portable device, support AirPlay and have built-in streaming support. The RX-V375 has a USB port instead.</p>
<p dir="ltr">Yamaha has also given the top three members of the lineup US$550 RX-V575, US$650 RX-V675 and US$855 RX-V775WA more powerful 7-channel amplifiers. The other two are 5.1 receivers. Among the three 7.1 models, the RX-V675 and RX-V775WA support ultra high definition upscaling. The middle three units (RX-V475, RX-V575, RX-V675) are WiFi upgradeable, while the RX-V775WA includes the Wi-Fi adapter in the box. All of the network enabled units can be controlled by a remote app for iOS or Android devices.</p>
<p dir="ltr">More info:</p>
<p dir="ltr"><a href="http://usa.yamaha.com/products/audio-visual/av-receivers-amps/rx/">The New  RX-V 75 Series</a></p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://reviews.cnet.com/av-receivers/denon-avr-e200/4505-6466_7-35627715.html"><strong>Denon&#8217;s 2013 receivers aim for simplicity</strong></a></p>
<p dir="ltr">Denon USA has announced three new models for their 2013 E-series lineup. Prices start at $250 for the entry 5.1 AVR-E200, $400 for the 5.1 AVR-E300, and $600 for the 7.1 AVR-E400.  Like everyone else, Denon has added support for 4K/UHD, MHL, AirPlay and smartphones. All of the new E-series AV receivers from Denon should be available already for purchase at authorized retailers.</p>
<p dir="ltr"> The AVR-E250 is as basic as receivers come these days, and probably not something you’d want in your home theater. It may work fine in a secondary room, but for the home theater you probably want to look into the AVR-E300, which adds an extra HDMI input (total of 5), networking support, Airplay, a set-up assistant and Audessey speaker calibration.  If you want 7.1, the AVR-E400 gets you the extra two channels, along with another HDMI input, video upconversion capability and a powered second zone option.</p>
<p dir="ltr">More info:</p>
<p dir="ltr"><a href="http://usa.denon.com/us/product/pages/productlanding.aspx?pcatid=avsolutions(denonna)&amp;catid=avreceivers(denonna)">Denon AV Receivers</a></p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.technologytell.com/hometech/94020/onkyo-new-av-receivers-and-htib/"><strong>Onkyo Intros Three New AV Receivers and One HTiB</strong></a></p>
<p dir="ltr">Onkyo’s newly announced new mid-priced receivers should be coming to authorized dealers in April, and if you’d like speakers with that, a 7.1-channel Home Theater in a Box system following in May. The TX-NR525 will sell for $500, the TX-NR626 for $600, and the TX-NR727 will top out the line at $900.  All three have networking capabilities with direct access to streaming providers like Spotify, Last.fm and TuneIn Radio.  Not to be outdone by the competition, they of course have a smartphone app, 4K pass-thru, and Audyssey speaker calibration.</p>
<p dir="ltr"> The TX-NR525 is a 5.2 channel receiver with more than just the basics.  But if you really want all the bells and whistles, the TX-NR626 and TX-NR727 are where it’s at.  Both are 7.2 channel capable and feature Qdeo 4K upscaling for great video performance, built-in WiFi and Bluetooth connectivity for your phone.  Using Wi-Fi or a wired connection, you can stream lossless audio an iPhone or Android device, or use DLNA to stream FLAC, Apple Lossless, Dolby TrueHD, LPCM, and DSD from another device on your network, like a laptop, personal computer, media server or network attached storage device.</p>
<p dir="ltr">More info:</p>
<p dir="ltr"><a href="http://onkyousa.com/prod_class.cfm?class=Receiver&amp;Source=hdrmenu">Onkyo TX Series A/V Receivers</a></p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.digitaltrends.com/home-theater/2013-pioneer-av-receivers-support-ultra-hd-4k-and-high-resolution-audio-formats/"><strong>Pioneer Debuts Four Feature-Packed AV Receivers for 2013</strong></a></p>
<p dir="ltr">Pioneer has announced four new AV receivers for 2013 in their standard or value line &#8211; essentially the receivers that don’t carry the “Elite” badge. Still great receivers, but also quite a bit easier on the pocketbook.  Prices start at $280 for the VSX-523-k, $430 for the VSX-823-k, $530 for the VSX-1023-k, and $630 for the grandaddy of the lineup, the VSX-1123-k.  All four offer discrete amplifier design, with the 523 and 823 providing 5 channels and the 1023 and 1123 stepping it up to 7 channels.</p>
<p dir="ltr"> All four models allow iOS playback, including album art and metadata on your TV if you have one connected, using the USB cable that comes with your iOS device (not included). They will also charge the iOS device when it is connected.  All models except for the entry level VSX-523-k include support for Mobile High Definition Link (MHL) so you can watch HD video content, with 7.1 surround, from a portable device, all while charging the device at the same time.</p>
<p dir="ltr">The VSX-823-k, VSX-1023-k and VSX-1123-k enable apps for both Android and iOS that allow you to control and configure the receiver more easily than the front panel or using the remote to navigate menus. The apps will also wirelessly stream music from the device through the receiver, including support for playlists. All but the entry VSX-523-k support DLNA and offer built-in Pandora and vTuner access. Either using USB or the network, the devices can playback a ton of audio formats, including DSD on the VSX-1123-k.</p>
<p dir="ltr">All four models support 3D and UHD passthru, with the VSX-1123-k stepping up to support upscaling using the Marvell Qdeo processor.</p>
<p dir="ltr">More info:</p>
<p dir="ltr"><a href="http://www.pioneerelectronics.com/PUSA/Home/AV-Receivers/Pioneer+Receivers">Introducing the New 2013 Family of A/V Receivers</a></p>
<p>&nbsp;</p>
<h4>Conclusion</h4>
<p dir="ltr">If you’re looking for value and a receiver that’s bursting with any and every feature imaginable, it looks like all the manufacturers have something for around $600 that will fit the bill. Which one to pick is entirely up to you. In the “bang for your buck” category, it really looks like a toss up between the Yamaha RX-V575 for $650, the Denon AVR-E400 for $600, the Onkyo TX-NR626 for $600, and the Pioneer VSX-1123-k for $630.  Let your ears decide.</p>
<div></div>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2013-03-22.mp3">Download Episode #575</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>March 22, 2013 12:07 AM</b>
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
			<?=getComments(5076)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5076)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2013/03/hdtv-and-home-theater-podcast-podcast-575-tis-the-season-for-receivers-yamaha-denon-onkyo-and-pioneer.php" type="text/javascript" charset="utf-8"></script>
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