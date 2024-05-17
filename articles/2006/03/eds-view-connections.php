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
		AND e.entry_id = 347";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ed Milbourn" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 347 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ed Milbourn" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 347 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 347";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/03/eds-view-connections.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 347";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Ed\'s View  -  Connections" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Ed\'s View  -  Connections" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Ed\'s View  -  Connections" />
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
	<title>HDTV Magazine - Ed's View  -  Connections</title>
	<meta name="keywords" content="technical identification, definition purpose, identification ieee, data rates, digital audio, digital, hdtv, network, interface, ethernet, audio, technical, definition, purpose, most, comments, ieee, usb, identification, video, data, hdmi, devices, firewire, designed" />
	<meta name="description" content="I continue to be amazed at the growing number of input jacks one finds on the back (and front) of today's HDTV sets.  This all started in the mid 1980's with the advent of the first audio/video components such as VCR's and early videodisc players.  These devices gave rise to the &quot;monitor/receiver&quot; with one or two sets of composite (Right, Left, Video) RCA jacks.  With the introduction of S-Video, another jack was added along with audio output jacks for the rising audio receiver market.  The final addition to the analog complement was the &quot;component&quot; inputs (Y, Pr, Pb or YUV).  This interface allowed the coupling of the wider bandwidth video information" />
	<meta name="title" content="Ed's View  -  Connections" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Ed's View  -  Connections" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/03/eds-view-connections.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I continue to be amazed at the growing number of input jacks one finds on the back (and front) of today's HDTV sets.  This all started in the mid 1980's with the advent of the first audio/video components such as VCR's and early videodisc players.  These devices gave rise to the &quot;monitor/receiver&quot; with one or two sets of composite (Right, Left, Video) RCA jacks.  With the introduction of S-Video, another jack was added along with audio output jacks for the rising audio receiver market.  The final addition to the analog complement was the &quot;component&quot; inputs (Y, Pr, Pb or YUV).  This interface allowed the coupling of the wider bandwidth video information" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=347', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/03/eds-view-connections.php">Ed's View  -  Connections</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ed Milbourn</b> on <b>March  7, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p>I continue to be amazed at the growing number of input jacks one finds on the back (and front) of today's HDTV sets.  This all started in the mid 1980's with the advent of the first audio/video components such as VCR's and early videodisc players.  These devices gave rise to the "monitor/receiver" with one or two sets of composite (Right, Left, Video) RCA jacks.  With the introduction of S-Video, another jack was added along with audio output jacks for the rising audio receiver market.  The final addition to the analog complement was the "component" inputs (Y, Pr, Pb or YUV).  This interface allowed the coupling of the wider bandwidth video information from DVD players to pass directly to the display, resulting in sharper pictures. However, because of the possibility of being able to copy high definition video via this interface, very few, if any, external HDTV decoder boxes use component connections.  </p>

<p>With the digital revolution and the increasing convergence of both TV and computer technologies, not only are we blessed with a plethora of multiple legacy analog jacks, but a whole new collection of digital interfaces, all with their own special purpose.  This must be confusing to the HDTV retail selling force and most likely their customers.  Most customers and retail sales personnel do not have a college degree in electrical engineering and computer science to fully understand what all of these holes and their complementary hoses do.  The instruction books help and are probably the best reference, but it seems a little more in-depth knowledge is needed to make full use of the capabilities of these multiple digital interface features.</p>

<p>I am not going to dwell on the analog connections as these are now well known, but let's take a moderately hard look at the various digital interfaces that are now (or will be) used in today's HDTV sets and related equipment.  Because of the growing convergence of HDTV with computer and telephone technologies, the more salient interfaces related to both HDTV and computer equipment will also be included.</p>

<p><strong>HDMI</strong></p>

<p><strong>Technical Identification</strong> - High Definition Multimedia Interface<br />
<strong>Definition/Purpose</strong> - HDMI is a secure, high frequency, uncompressed (baseband), single cable A/V interface.  HDMI is designed specifically to couple digital HDTV signals from various sources, such as DVRs, Cable and DBS boxes, to an HDTV display system.	<br />
<strong>Comments</strong> - 5Gbp/s capability handles all 18 DTV formats including 1080p.  HDMI supports up to eight channels of digital audio information.</p>

<p><strong>DVI</strong></p>

<p><strong>Technical Identification</strong> - Digital Video Interface<br />
<strong>Definition/Purpose</strong> - DVI is designed to provide a digital video connection between computer and monitor devices.  DVI has similar digital video bandwidth characteristics as HDMI, but it is not secure, nor does it carry audio signals.<br />
<strong>Comments</strong> - DVI is physically compatible with HDMI via an adapter.  However, the audio signals must be connected via separate audio cables.</p>

<p><strong>SPDIF </strong><br />
(Digital Audio Interface)</p>

<p><strong>Technical Identification</strong> - Sony/Philips Digital Interconnect Format<br />
<strong>Definition/Purpose</strong> - SPDIF provides a serial digital output stream from a digital audio source in either un-decoded PCM (Pulse Code Modulation) or decoded Dolby Digital (5.1 channel) formats.  SPDIF is normally used to connect digital audio signals from an HDTV receiver, DVD and/or DVR to a separate multi-channel (surround sound) audio processing (home theater) system.<br />
<strong>Comments</strong> - SPDIF employs either a coaxial (RCA jack) or optical physical interface.  Most new HDTV equipment incorporates only the optical interface.  Various sampling formats and sampling rates are automatically supported.  Bit rates can approach 3Mb/s.<br />
Note:  The SPDIF interface on some HDTV models will not support all Dolby Digital 5.1 channels if the HDMI is also utilized; only two-channel stereo is output.  In this case it is necessary to connect the SPDIF cable directly between the external A/V source and the "home theater" audio system.</p>

<p><strong>FIREWIRE</strong> (iLink)</p>

<p><strong>Technical Identification</strong> - IEEE 1394<br />
<strong>Definition/Purpose</strong> - IEEE 1394 provides a simple, low-cost, high frequency, bi-directional digital interface designed to interconnect various A/V products, including computers.  FireWire is designed to allow various '1394 compatible A/V components to be connected as a network with the components being coupled to each other in a "daisy chain" configuration.<br />
<strong>Comments</strong> - The ability of FireWire to simultaneous handle a wide range of various digital data formats, including real-time high data rate A/V streams and low-speed control streams as well as the ability to self-configure, makes it a prime candidate as the format for the illusive A/V local area (A/V cluster) network standard.  IEEE 1394 supports data rates of up to 400Mbps ('1394a) and 800Mbps ('1394b), sufficient to handle several independent HDTV compressed digital streams.</p>

<p><strong>DTV LINK</strong></p>

<p><strong>Technical Identification</strong> - IEEE 1394 plus 5C content protection<br />
<strong>Definition/Purpose</strong> - DTV Link is a specialized application of FireWire whereby a content (copy) protection layer, called 5C*, as well as specific control formats, are added to the basic IEEE 1394 interface.<br />
<strong>Comments</strong> - In order for FireWire to be a serious contender as the default format for local HDTV component cluster networks, it must be protected.  DTV Link provides that capability.</p>

<p><strong>ETHERNET</strong></p>

<p><strong>Technical Identification</strong> - IEEE 802.3 or 10 -100BaseT (Mbps over Twisted Pair)<br />
<strong>Definition/Purpose</strong> - Ethernet is the oldest and most popular Local Area Network (LAN) and Wide Area Network (WAN) technology.  Originally developed as a means to network computers and printers, it has evolved as the default solution for networking most any type of digital data.  Most new houses are now wired for physically transporting Ethernet via Category Five or Six (CAT 5 or 6) twisted-pair wiring.  Further, most Cable and DSL modems employ an Ethernet interface to allow direct connection to devices with Ethernet functionality.  Some HDTV models have built-in web browser software with an Ethernet interface, allowing these units to be directly connected to the Web via an Ethernet network or Ethernet enabled modem.<br />
<strong>Comments</strong> - Ethernet's evolving bandwidth capability, now over 1Gbps, and technical flexibility place this format in position to be the technology-of-choice as the A/V network standard for in-home and multiple building WANs.  Most any other network formats, such as FireWire, can be coupled to an Ethernet network via routing devices (Routers).</p>

<p>The above descriptions are of the most salient digital connections found on today's HDTV receivers and associated components.  However, because of that aforementioned increasing convergence of traditional consumer electronics and all information technologies, i.e. computers, games et al, other emerging connection technologies are certain to be embraced by HDTV equipment.  Therefore, for completeness, I have added a summary of the most important of these emerging and expanding connection technologies.</p>

<p><strong>USB</strong></p>

<p><strong>Technical Identification</strong> - Universal Serial Bus 1.1 and 2.0<br />
<strong>Definition/Purpose</strong> - USB was developed as a simple, easy means to physically connect computers with a variety of peripherals.  USB enables up to 127 separate devices to be "hot" connected to and self configured by a computer, therefore providing true "plug-n-play" capability.  USB 2.0 is a higher performance USB protocol, allowing data rates of to 480Mbps versus 12Mbps for USB 1.1.  USB 1.1 is fully back compatible with computers hosting USB 2.0.<br />
<strong>Comments</strong> - Although USB and FireWire are similar in concept, USB is designed mainly to network asynchronous peripherals such as printers, scanners and cameras, while FireWire is formatted for networking compressed, real-time audio/video multimedia devices.</p>

<p><strong>Wi-Fi </strong><br />
(Wireless Fidelity)</p>

<p><strong>Technical Identification</strong>  - IEEE 802.11b, a, g, and n<br />
<strong>Definition/Purpose</strong> - Wi-Fi is a short distance (LAN), broadband radio transceiver system designed to provide wireless digital network capability.  Wi-Fi is sometimes called "wireless Ethernet" because it uses the Ethernet protocol to drive various modulation schemes. Wi-Fi is transmitted in frequency bands centered at 2.4GHz and 5GHz.  The adoption of IEEE 1394 techniques to the basic Wi-Fi Ethernet protocol has resulted in robust multimedia data rates up to 1.6Gbps.<br />
<strong>Comments</strong> - As connections become increasingly wireless, Wi-Fi will become ubiquitous in the HDTV world.  Wi-Fi will be the basis for wirelessly connecting HDTV monitors to a variety of components located throughout the home.</p>

<p><strong>WiMax</strong><br />
(Worldwide Interoperability for Microwave Access)</p>

<p><strong>Technical Identification</strong> - IEEE 802.16a<br />
<strong>Definition/Purpose</strong> - WiMax is an extension of the basic Wi-Fi protocols designed to allow broadband data network coverage over a comparatively large area, called a Metropolitan Area Network (MAN).  WiMax is capable of 70Mbps data transfer rates over a 30-mile range.<br />
Microwave frequencies utilized are between 2 and 11GHz.<br />
<strong>Comments</strong> - WiMax is presently being deployed worldwide in many major urban and suburban areas to provide broadband access without the expense of the "last mile" premises connections.  With the present deregulation of many state and municipal telecommunications systems, WiMax networks are in a position to become a serious competitive threat to traditional Cable and Telco installations.<br />
  <br />
<strong>Bluetooth</strong></p>

<p><strong>Technical Identification</strong> - IEEE 802.15<br />
<strong>Definition/Purpose</strong> - Bluetooth is designed to be a short range (10 meter), low cost, low power, automatically self-configuring, signal/control network for personal-area networks (PANs).  <br />
<strong>Comments</strong> - Bluetooth has found its greatest commercial success in wireless earpiece extensions for cell phones; but, at this time, this technology does not have sufficient bandwidth capability for interconnecting HDTV A/V components. However, with more capacity, Bluetooth could be a contender for HDTV signal connection applications.  Bluetooth 2.0 can handle data rates up to 3 Mbps and employs spread-spectrum modulation centered at 2.45Ghz.  A local Bluetooth PAN network can manage up to eight devices.</p>

<p>At this time there are at least five different inter-industry groups working on DTV/HDTV network standards. Hopefully, there will be some convergence of thought from these groups that will result in a robust, secure HDTV A/V interconnectivity standard.  The tremendous pace of evolving technology is sometimes a nemesis to developing and establishing technical standards.  Further, the longer the network standards process takes, the harder it becomes to accommodate legacy equipment.  The best consumer strategy is to protect the investment in the display system (i.e. monitor), which should have a lifetime of at least ten years.  Separate signal delivering devices (i.e. boxes) are much less expensive to replace than the display.   HDMI is probably the most stable connection technology as we look to the future.  That's why I listed it first, and the more you have, the merrier.</p>

<p>Ed    </p>

<p>*Also known as Digital Transmission Licensing Administration (DTLA).  The "5C" refers to the five companies that developed and comprise the licensing group.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ed Milbourn</b>, <b>March  7, 2006 11:41 AM</b>
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
			<?=getComments(347)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Ed Milbourn', 347)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/03/eds-view-connections.php" type="text/javascript" charset="utf-8"></script>
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