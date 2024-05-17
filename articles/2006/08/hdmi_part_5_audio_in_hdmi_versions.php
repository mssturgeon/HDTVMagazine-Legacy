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
		AND e.entry_id = 414";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 414 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 414 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 414";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/08/hdmi-part-5-audio-in-hdmi-versions.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 414";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDMI Part 5 - Audio in HDMI Versions" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDMI Part 5 - Audio in HDMI Versions" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDMI Part 5 - Audio in HDMI Versions" />
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
	<title>HDTV Magazine - HDMI Part 5 - Audio in HDMI Versions</title>
	<meta name="keywords" content="multi channel, audio formats, channel audio, dolby digital, def dvd, audio, hdmi, dolby, dvd, formats, receiver, channel, bit, dts, multi, players, digital, device, player, connection, def, optional, suited, pcm, however" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/images/hdmi_200.gif&quot; alt=&quot;HDMI&quot; align=&quot;right&quot;&gt;While the multichannel audio industry keeps creating more formats for newer equipment, it also creates the need of compatibility with existing equipment. And being the digital connectivity solution that the industry says it is, HDMI has to meet the challenge of that &quot;evolution&quot;.

When HDMI 1.1 came out, it added to the spec a new packet to carry some DVD-Audio content protection-related data. All audio capabilities of DVD-Audio were part of 1.0 but the CPPM/CPRM license (used for DVD-Audio encrypted disks) required some additional data to be transmitted.

Then, when HDMI 1.2 came out..." />
	<meta name="title" content="HDMI Part 5 - Audio in HDMI Versions" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDMI Part 5 - Audio in HDMI Versions" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/08/hdmi-part-5-audio-in-hdmi-versions.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/images/hdmi_200.gif&quot; alt=&quot;HDMI&quot; align=&quot;right&quot;&gt;While the multichannel audio industry keeps creating more formats for newer equipment, it also creates the need of compatibility with existing equipment. And being the digital connectivity solution that the industry says it is, HDMI has to meet the challenge of that &quot;evolution&quot;.

When HDMI 1.1 came out, it added to the spec a new packet to carry some DVD-Audio content protection-related data. All audio capabilities of DVD-Audio were part of 1.0 but the CPPM/CPRM license (used for DVD-Audio encrypted disks) required some additional data to be transmitted.

Then, when HDMI 1.2 came out..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=414', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/08/hdmi-part-5-audio-in-hdmi-versions.php">HDMI Part 5 - Audio in HDMI Versions</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>August  8, 2006</b>
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
				<p><img src="/images/hdmi_200.gif" alt="HDMI" align="left">While the multichannel audio industry keeps creating more formats for newer equipment, it also creates the need of compatibility with existing equipment. And being the digital connectivity solution that the industry says it is, HDMI has to meet the challenge of that "evolution".</p>

<p>When HDMI 1.1 came out, it added to the spec a new packet to carry some DVD-Audio content protection-related data. All audio capabilities of DVD-Audio were part of 1.0 but the CPPM/CPRM license (used for DVD-Audio encrypted disks) required some additional data to be transmitted.</p>

<p>Then, when HDMI 1.2 came out, the primary audio feature added to the spec was "One Bit Audio" which is a generic name for DSD, the audio format used in SACD (Super Audio CD).</p>

<p>Later, HDMI 1.2a was released primarily to put in place all of the specs and test methods to allow a full implementation of CEC (Consumer Electronic Control), allowing one touch control over the entire system. Before HDMI 1.2a it was possible to do a CEC implementation, but since there was no complete test spec no vendor did.</p>

<p>According to Joseph Lee, Director of Marketing of Simplay Labs LLC, the HDMI specification requires at minimum that 2 channel PCM audio be supported over the HDMI interface, all other audio formats are optional. However, a source device supporting this bare minimum on its HDMI output would be unusual, as the video and audio capabilities that most HD devices supporting HDMI are the same or better than the capabilities available from the older analog &amp; S/PDIF outputs.</p>

<p>Since HDMI is an open industry standard, the specification does not mandate specific high-resolution formats (e.g. 720p, 1080i, etc.) to be available in all logo' d devices, but leaves it up to manufacturers to choose in order to differentiate their products. There are no technical reasons why any HDMI host devices would not support the best audio & video capabilities on the HDMI output; however, manufacturers may choose not to implement some formats in order to build lower cost devices.</p>

<p>It is possible that some devices might be able to gain the capability to support digital surround sound formats on the HDMI output with a firmware upgrade, but this depends on the hardware architecture and the manufacturer's decision to support such an upgrade.</p>

<p><br />
<h2>Multi-channel Audio does not like my HDMI connection!</h2></p>

<p>Not too long ago, some articles claimed that HDMI was not implemented by some manufacturers as a full multi-channel connection. The confusion came from the fact that the majority of first-generation HDMI suited devices were TVs with only two-channel stereo and had no use for the full multi-channel signal.</p>

<p>However, most other equipment, from DVD players to A/V receivers, switchers, etc, should be capable to receive, process, switch, or send the full multi-channel audio content across HDMI.</p>

<p>HDMI is a two-way communication between the source device and the receiving device by which the receiving device tells the source about its multi-channel capabilities. The source device can then send a matching signal, such as two-channel stereo to a TV, or 5.1 DD channel to a 5.1 A/V receiver. In other words, the source device adapts to the receiving device when sending the signal.</p>

<p>In the case of an A/V receiver receiving the signal from a 5.1 DD DVD player, both ends of the connection recognize the need to maintain the 5.1, but the receiver might redirect the signal to a TV that needs only L/R channels, for which the output of the receiver adapts on only that output jack by down-mixing the DD stream.</p>

<p>Regarding the newer hi-bit multi-channel audio codecs from Dolby and DTS, the consumer should verify that the HDMI transmitter/receiver chip installed in both ends of the HDMI link is actually capable to transport the multi-channel audio you plan to play. For example, one legacy application could limit itself to transport the typical Dolby Digital 5.1, another consumer might require transporting SACD, a feature implemented on chips complying with the current HDMI specification version 1.2.</p>

<p>Earlier transmitter/receiver HDMI chips could have been manufactured based on specification versions not suited for newer audio formats, and one could not expect that old chips support newer functionality, such as SACD, DTS-HD, Dolby TrueHD, Dolby Digital Plus, etc. For more information about the applicability and connectivity of hi-bit audio formats , please review the following article I recently wrote about Multi-channel Audio for HD:</p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php">http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php</a></p>

<p><br />
<h2>Hi-bit Audio Application to Hi-def DVD Formats</h2></p>

<p>In September 23, 2004, Dolby Laboratories announced that the DVD Forum decided to include Dolby Digital Plus and MLP Lossless, the core audio technology behind multichannel DVD-Audio, as mandatory audio standards for HD DVD. Later, Dolby TrueHD was also selected as mandatory audio format for HD DVD. However, both Dolby formats were selected as optional audio formats for Blu-ray players. DTS-HD was declared as optional for the players of both Hi-def DVD formats.</p>

<p>In other words, Blu-ray approved as optional the 3 hi-bit audio formats (Dolby Digital Plus, Dolby TrueHD, and DTS-HD), the only mandatory codecs for Blu-ray are the legacy 5.1 DD and DTS.</p>

<p>According to Silicon Image, the HDMI transport is able to handle 24Mbps of audio speed, suitable for any of the proposed audio formats from either disc format, including DTS HD Master Audio. However, the version 1.3 HDMI specification would enable the players to output those audio formats over HDMI for them to be decoded externally, by a future A/V receiver capable to do so, for example, if one prefers so; the protocols and specifications were finalized and available in June 2006. Dolby is working closely with Silicon Image to ensure transmission of Dolby Digital Plus and TrueHD signals on HDMI v. 1.3.</p>

<p>As an alternative, HD players with internal hi-bit-rate decoders are expected to also have 6.1 or 7.1 analog outputs that could support the hi-bit-rate and be connected to receivers with 6.1 or 7.1 channel analog inputs.</p>

<table class="grid" cellspacing="0" align="center"><tr><td class="grid"><img src="/images/articles/multichannel-connect.gif" alt="Connection via Multichannel Analog Inputs (graph courtesy of Dolby Laboratories)"></td></tr><tr><td class="grid" style="text-align:center">Connection via Multichannel Analog Inputs (graph courtesy of Dolby Laboratories)</td></tr></table>

<p><br />
<h2>Hi Def DVD, Using HDMI for Audio</h2></p>

<p>In September 2005, Dolby announced that A/V receivers capable of processing PCM over their HDMI 1.1 inputs should also be able to have sufficient bandwidth to accept the HD video and the PCM multi-channel audio decoded by the Hi-def DVD player.</p>

<p>Any HDMI suited A/V receiver should be capable to input the PCM and reproduce the higher bandwidth of the soundtracks. Initially, it was believed that those HDMI 1.1 suited A/V receivers would have to use analog cables from the multi-channel audio connectors (as with DVD-Audio), and wait until specification version 1.3 of HDMI be completed (and eventually change to a 1.3 compliant A/V receiver).</p>

<p>According to Dolby, there should be no need to replace a receiver suited with HDMI 1.1 to get the benefit of the higher-bit audio formats. However, when using the latest HDMI version 1.3 from player to receiver, the decoding would not have to happen in the player, the connection would stream the native mandatory and optional audio formats to the HDMI 1.3 suited A/V receiver, which would perform the decoding job.</p>

<table class="grid" cellspacing="0" align="center"><tr><td class="grid"><img src="/images/articles/pcm-connect.gif" alt="Connection via Next-Generation HDMI (graph courtesy of Dolby Laboratories)"></td></tr><tr><td class="grid" style="text-align:center">Connection via Next-Generation HDMI (graph courtesy of Dolby Laboratories)</td></tr></table>

<p><br />
Reportedly, DTS intends to suit players as well as receivers with their decoders, Dolby was quoted as concentrating initially on players.</p>

<p>Hi-definition DVD disc players are expected to support Internet-streamed audio content (such as director's comments) while playing the movie, and they have to internally mix the various audio components (soundtrack, Internet, PCM sounds, etc) before converting the final audio mix to individual PCM channels to be output over the HDMI connection.</p>

<p>The newer hi-bit formats, Dolby Digital Plus lossy, Dolby TrueHD lossless, and DTS HD lossless (previously named DTS++ lossless, and now extended to Master Audio 24 Mbps), are much faster than the supported speed of typical digital coaxial connections (S/PDIF) used for the current legacy Dolby Digital and DTS multichannel audio formats, however, those legacy connections would still transport the down-converted legacy versions (derived from the hi-bit) produced by Hi-def DVD players.</p>

<p><br />
<h2>Lip Sync Feature of 1.3, how would be implemented?</h2></p>

<p>There was a question recently by one of the Magazine readers interested to know if both pieces of equipment, the source and the receiving device, would need to be 1.3 capable in order for the "Lip Sync" feature to work. I contacted Leslie Chard, President of HDMI Licensing LLC, he had the courtesy to provide details as follows, and I quote:</p>

<p>"The 1.3 lip sync correction functionality is required on the device that creates the lip sync problem (typically a display - which has a latency between audio/video processing because of the more demanding requirements of video processing), and a device that can correct the lip sync delay (the initial implementations of this will be in a receiver, but in the future this functionality will be in DVD players, and most other CE devices.) The reports that we are getting from manufacturers indicate that this function is very popular and will be widely implemented."</p>

<p><br />
<h2>Upgrade? For Audio? Again?</h2></p>

<p>We have said that if an existing receiver does not have HDMI inputs it can still use the multichannel analog connections (6 to 8 RCA type of connections) until is time for the upgrade; remember the convenient DVD-Audio mess of wires?</p>

<p>In selecting a Hi-def DVD player of any format, one factor of choosing one model over the other could be the implementation of the Hi-bit multichannel codecs that are optional (Dolby TrueHD, Dolby Digital Plus, DTS HD, depending on the format).</p>

<p>Even when not having the latest A/V receiver that could decode the Hi-bit formats itself using the HDMI 1.3 connection, if a consumer is interested in a system to reproduce the optional DTS-HD for example, that consumer would be making a better investment by choosing a Hi-def player that decodes DTS-HD by itself. The existing receiver, using the alternative connections above, would be spared from an unneeded upgrade just for that purpose.</p>

<p>In summary, there will be a variety of backward compatibility connectivity options to allow consumers to still be able to use the existing audio equipment at their current multi-channel audio capabilities when playing back the new audio formats of High Definition DVD discs/players, but there will be enough incentive for upgrades.</p>

<p>Upgrading to an A/V receiver suited with HDMI 1.1 would bring the full benefit of the lossless audio formats transporting the channels digitally as PCM, and if the upgrade could be done to HDMI 1.3 connectivity it would open the possibility to do hi-bit decoding on the receiver, giving the consumer the option of doing the audio decoding in the A/V receiver or the player, which ever sounds best for the consumer, and perhaps been able to decode a hi-bit codec missing in the player but available in the receiver.</p>

<p>In that scenario, the player would just stream out over HDMI the un-decoded hi-bit multichannel signal for the A/V receiver to decode, expanding the flexibility of the audio part of the system. However, in some specific conditions, using the HDMI 1.3 connection for streamed audio (not PCM) might disallow the mixing of the additional audio features of Hi-def DVD over the soundtrack, unless the player is suited with an optional "encoder" of such signals over that output.</p>

<p><br />
Stay tuned for Part 6 "1080p Support"</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>August  8, 2006  5:19 AM</b>
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
			<?=getComments(414)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 414)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/08/hdmi-part-5-audio-in-hdmi-versions.php" type="text/javascript" charset="utf-8"></script>
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