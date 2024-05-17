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
		AND e.entry_id = 367";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 367 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 367 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 367";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/04/multichannel-audio-for-hd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 367";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Multi-channel Audio for HD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Multi-channel Audio for HD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Multi-channel Audio for HD" />
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
	<title>HDTV Magazine - Multi-channel Audio for HD</title>
	<meta name="keywords" content="dolby digital, dolby truehd, digital plus, next generation, blu ray, audio, dolby, digital, bit, dvd, player, formats, truehd, dts, receiver, channel, hdmi, lossless, players, plus, format, disc, channels, connection, pcm" />
	<meta name="description" content="We had not planned on releasing this for some time, but the recent questions about HD DVD audio prompted Rodolfo and I to get this out to the public while it was most useful.  I hope you find it so.

Topics covered include:
- Hi-bit Dolby Digital Formats - Connectivity
- Legacy Discrete Surround Audio Formats for Hi Def DVD
- Hi-bit Surround Audio Formats - Summary
- Hi-bit Audio Application to Hi-def DVD Formats
- Analysis" />
	<meta name="title" content="Multi-channel Audio for HD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Multi-channel Audio for HD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/04/multichannel-audio-for-hd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="We had not planned on releasing this for some time, but the recent questions about HD DVD audio prompted Rodolfo and I to get this out to the public while it was most useful.  I hope you find it so.

Topics covered include:
- Hi-bit Dolby Digital Formats - Connectivity
- Legacy Discrete Surround Audio Formats for Hi Def DVD
- Hi-bit Surround Audio Formats - Summary
- Hi-bit Audio Application to Hi-def DVD Formats
- Analysis" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=367', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/04/multichannel-audio-for-hd.php">Multi-channel Audio for HD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>April 21, 2006</b>
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
				<blockquote>This is an excerpt from the <b>HDTV Technology Review 2006 Report</b> by Rodolfo La Maestra. We had not planned on releasing this for some time, but the recent questions about HD DVD audio prompted Rodolfo and I to get this out to the public while it was most useful.  I hope you find it so.<br>
<br>
If you are interested in the full version of this report, it is currently available from the <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/03/hdtv_technology_review_part_1_introduction.php">HDTV Technology Review, Part 1: Introduction</a><br>
Part 2: <a href="/articles/2006/04/1080p_into_hdtv_displays.php">1080p into HDTV Displays</a><br>
</blockquote>

<p><br><br />
<br><br />
<span style="font-size:12pt"><center><strong>Multi-channel Audio for HD</strong></center></span></p>

<p><br />
<h2>Hi-bit Dolby Digital Formats - Connectivity</h2><br />
In September 05, Dolby Laboratories announced its newest lossless audio multichannel format, Dolby<sup>&reg;</sup> TrueHD, for the high-definition optical disc formats, Blu-ray and HD-DVD.  The new format claims to be equaled bit-for-bit in performance to the highest-resolution studio masters currently available.</p>

<p>As covered in detail further down, Dolby Digital Plus and Dolby TrueHD have been approved as mandatory audio codecs in the HD DVD format (all players must be able to decode it), while they are optional in the Blu-ray disc format.  Dolby Digital is mandatory in both disc formats.</p>

<p>According to Dolby: "Dolby TrueHD builds upon the proven foundation of MLP Lossless&trade; by incorporating higher bit rates, additional channels, enhanced stereo mix support, and extensive metadata functionality, including dynamic range control and dialogue normalization.  Enabling recordings that are bit-for-bit identical to studio masters, MLP Lossless was first introduced in DVD-Audio, and has since become the leading multichannel lossless audio format.  In addition, Dolby TrueHD provides support for all of the new speaker locations designated by the Society of Motion Picture and Television Engineers (SMPTE) for digital cinema applications (RP 226)."</p>

<p>New delivery formats that can support at least a 2 Mbps bit rate for audio are potential software candidates.  The first applications to adopt Dolby TrueHD are Blu-ray Disc and HD DVD, as these can support up to 18 Mbps for audio.</p>

<p>Next-generation Hi-def players are being designed to include features like interactivity and audio mixing which require the audio to be decoded in the player instead of the A/V receiver.  A Dolby TrueHD multichannel decoder in the player will be the only way to ensure that listeners will hear the full quality of the wide range of audio capabilities.</p>

<p>Unlike perceptual or lossy data reduction, lossless coding does not alter the final decoded signal in any way, but merely "packs" the audio data more efficiently into a smaller data rate for storage or transmission.  The lossless version always sounds like the source.  The lossy version may sound like the source, but this is not guaranteed.  The perceived quality of a lossy audio format depends on many factors, including the nature of the source material, the compression efficiency of the codec, the delivery bit rate chosen, the quality of the playback hardware, and the listening environment.</p>

<p>Dolby TrueHD also provides unique support for stereo playback, either via a programmable downmix or a wholly separate stereo mix, ensuring that surround content creators can deliver the companion stereo mix exactly as they intend, without compromise.  Dolby TrueHD for next-generation high-definition media delivers sampling frequencies from 48 to 192 kHz and word lengths from 16 to 24 bits.  The sample rate and word length for Dolby TrueHD content will always be the same for all channels.</p>

<p>The bit rate needed to deliver a Dolby TrueHD lossless track depends on the characteristics of the source material, bit depth, and sampling frequency.  Dolby TrueHD for next-generation high-definition media can operate at data rates up to 18 Mbps.  All new players incorporating Dolby TrueHD technology will support this maximum data rate.</p>

<p>Dolby TrueHD for next-generation high definition media supports up to eight channels of audio, and offers expandability to accommodate more channels in the future while retaining compatibility with all Dolby TrueHD decoders.  The Dolby TrueHD stream is structured so that a player only needs to decode the number of channels it needs.  This ensures that a single Dolby TrueHD stream can be used to deliver a two-, six-, or eight-channel presentation with precise control over the playback defined by the content producer.</p>

<p>Dolby TrueHD is designed to offer comprehensive metadata functionality similar to that found in Dolby Digital and Dolby Digital Plus.  This includes down-mixes that are defined by the content producer, dynamic range compression for late-night listening, and dialogue normalization to ensure consistent playback loudness between different content.  For future content featuring discrete 7.1-channel playback, Dolby TrueHD also supports multiple 7.1 configurations.</p>

<p>The following highlights the various multichannel audio connections between near future High Definition DVD players and A/V Receivers for the Dolby formats.</p>

<p><em>Disclaimer: The information of connectivity and graphs included below are provided courtesy of Dolby Laboratories with permission.</em></p>

<p>In HD disc players, the audio will be handled in much the same fashion.  Soundtracks decoded from the disc, as well as audio elements streamed or downloaded from an Internet connection or generated internally in the player will be decoded in the player as digital PCM signals.  PCM is the format players use to perform all internal audio processing operations, including mixing.  In the mixing stage, streaming commentary, button sounds, and other non-disc-audio will be mixed with the native 5.1 or 7.1 soundtrack from the disc.  The result will be the complete audio presentation as intended by the content maker.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image364.gif" alt="Next-Generation Six-Channel Optical Player with Dolby Digital Output Encoder" align=center></td></tr><tr><td align="center">Figure 1 - Next-Generation Six-Channel Optical Player with Dolby Digital Output Encoder</td></tr></table>

<p>The implications of this decoding within the player are significant.  New features can be created for a given title long after the discs have shipped.  More importantly, the fact that players will be mixing the audio internally means that it will no longer be possible (or necessary) to output raw audio bitstreams from the player as is typical with DVD-Video.  As a result, consumers can no longer assume that every player will work with every A/V receiver.</p>

<p>Two methods already exist for reproducing the high-resolution soundtracks of next generation optical formats through your A/V receiver or audio processor.</p>

<p><br />
<u>Single-Cable Digital Connection</u></p>

<p>Increasingly, A/V processors and receivers are being equipped with IEEE 1394 (FireWire<sup>&reg;</sup>) or HDMI connections, capable of transporting up to eight channels of 24-bit/96 kHz PCM audio content. If your A/V receiver is equipped with this type of next generation connection, you should look for a similarly furnished next-generation optical media player.  By this method of connection, the mixed PCM signal is transported from the HD player to your A/V receiver, where digital signal processing and bass management can be easily effected.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image366.gif" alt="Connection via Current HDMI" align=center></td></tr><tr><td align="center">Figure 2 - Connection via Current HDMI</td></tr></table>

<p><br />
<u>Multichannel Analog Connection</u></p>

<p>A next-generation optical player may also include line-level audio outputs sourced from the multichannel mixed PCM signals passed through digital-to-analog converters.  The advent of SACD and DVD-Audio in recent years has led to the incorporation of 5.1 and even 7.1 external inputs on many A/V receivers.  If your A/V receiver is equipped with 5.1 or 7.1 external audio inputs, the selection of an optical player equipped with 5.1- or 7.1 channel line-level outputs will provide full-bandwidth reproduction of the audio signal originating from your HD player.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image368.gif" alt="Connection via Multichannel Analog Inputs" align=center></td></tr><tr><td align="center">Figure 3 - Connection via Multichannel Analog Inputs</td></tr></table>

<p>A connection through either of these existing interfaces will let you experience the full potential of the high-resolution audio delivered on next-generation optical formats.</p>

<p><br />
<u>S/PDIF Connection</u></p>

<p>If your A/V receiver or processor has neither multichannel analog or digital inputs, but is equipped with 5.1-channel Dolby Digital decoding and playback, you will still be able to enjoy 5.1-channel performance from next-generation optical players.  Included within 7.1 channel multichannel Dolby Digital Plus and Dolby TrueHD streams is a core 5.1 mix prepared by the content maker that is used when the player is set for 5.1-channel mode.</p>

<p>After playback audio signals have been mixed in the player, the PCM signal can be encoded to a Dolby Digital signal and output from the player via S/PDIF (optical or coaxial) to your connected Dolby Digital A/V receiver or processor.</p>

<p>In many instances, the audio quality you will experience from this connection may be better than what you would experience during playback of standard-definition DVD Video discs, especially if the native signal on the disc is Dolby TrueHD or high-bit-rate Dolby Digital Plus.  This is a direct result of a higher-quality source signal feeding a Dolby Digital encoder running at 640 kbps-higher than the maximum bit rate on DVD-Video.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image370.gif" alt="Connection via S/PDIF" align=center></td></tr><tr><td align="center">Figure 4 - Connection via S/PDIF</td></tr></table>

<p>Because Dolby Digital encoding (of the audio mixes over the soundtrack, RLM) support is optional in HD players, you will need to look for a next-generation player equipped with an S/PDIF output and built in Dolby Digital 5.1 channel encoding technology.</p>

<p><br />
<u>Dolby TrueHD and Dolby Digital Plus in A/V Receivers</u></p>

<p>Eventually, A/V receivers will have direct access to Dolby Digital Plus or Dolby TrueHD bitstreams.  Dolby is working with the IEC and HDMI organizations to update data protocols to enable future versions of these high-bandwidth interfaces to carry these bitstreams. </p>

<p>To decode these bitstreams, the A/V decoder will need to support the updated data protocols, as well as incorporate these new decoding algorithms.  In addition, it will be necessary to select HD discs in which the content maker has permitted the core 5.1 or 7.1 audio bitstreams to bypass the player's mixing process and be sent directly to the digital outputs of the player.  We expect that certain HD discs will permit this, but they may represent a minority of titles.  In the end, the sound quality will be essentially the same as audio that was decoded in the player as PCM and transported it through a current generation HDMI connection to the A/V receiver.</p>

<p>With six or eight channels of 24 bit/96 kHz audio transported from these new HD formats, post-processing DSP requirements for an A/V receiver more than double.  Rather than devoting the considerable DSP resources to decoding the core audio signals within the A/V processor itself, it may be more fruitful to use the A/V processor's DSP resources to perform high-resolution post-processing such as bass management, room or speaker equalization, Dolby Pro Logic<sup>&reg;</sup> IIx decoding, or other types of digital signal processing.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image372.gif" alt="Connection via Next-Generation HDMI" align=center></td></tr><tr><td align="center">Figure 5 - Connection via Next-Generation HDMI</td></tr></table>

<p>As a result of the quality and capabilities that the new digital interfaces provide, hardware manufacturers can offer more highly optimized system designs that attain the ultimate in performance while providing the greatest flexibility and efficiency for the consumer.<br />
---------------------------------------  <br />
<em>Disclaimer: This concludes the connectivity information sourced from Dolby Laboratories.</em></p>

<p><br />
<h2>Legacy Discrete Surround Audio Formats for Hi Def DVD</h2><br />
In October 2004, the DVD Forum and the Blu-ray Disc Association approved mandatory and optional audio formats for both Hi Def DVD standards.  </p>

<p>Both groups approved the legacy Dolby Digital 5.1 and DTS 5.1 discrete audio surround formats as mandatory for HD players on both formats, which also ensure the audio playability of 5.1 multi-channel DVDs when played on HD players.</p>

<p>However, both High Def DVD formats declared optional the player's ability of decoding 6.1 DTS channels.  Regarding the disc itself, at least one of the legacy formats must be included on the pre-recorded discs, at the choice of the content provider.  </p>

<p><br />
<h2>Hi-bit Surround Audio Formats - Summary</h2></p>

<p><u>Dolby Digital Plus</u><br />
Dolby Digital Plus is a flexible codec based upon core Dolby Digital technologies.  For broadcasters, it provides higher efficiency coding at lower bit rates.  For the new blue laser formats, it provides more channels, extended bit rates and higher quality. </p>

<p>The Dolby Digital Plus format was announced in April 2004 at NAB.  Dolby Digital Plus enables broadcasters to transmit 5.1 at an efficient 50% (192Kbps) data rate of regular Dolby Digital (384Kbps).  Compatibility with all existing Dolby Digital consumer decoders is ensured, as the Dolby Digital Plus signal will be upconverted to a standard 640Kbps Dolby Digital Plus output in the set top box (but the set top box that performs the upconversion would be needed).  </p>

<p>The format supports multiple languages in a single bit-stream, and was selected by the Advanced Television Systems Committee (ATSC) as the standard for future robust broadcast applications, and as an option for multichannel audio delivered by the Digital Video Broadcasting (DVB) Project for satellite and cable TV.</p>

<p>Dolby Digital Plus was also announced as capable of a higher-bit rate enhancement to Dolby's existing AC-3 (Dolby Digital) lossy audio compression format.  Dolby Digital Plus format supports new levels of quality data rates as high as 6 Mbps on 7.1 channels, with a bit-rate performance of at least 3 Mbps on HD-DVD and up to 4.7 Mbps on Blu-ray Disc.  Dolby Digital Plus has no ability to carry uncompressed audio nor can it be operated in a lossless way.</p>

<p><u>DTS-HD (and ++, Master Audio)</u><br />
DTS announced that their new lossless DTS++ (as named originally) would be capable of higher bit rates.  In October 2004, the DTS++ name was changed to DTS-HD.  In December 2005, DTS announced their demonstration of a 24 Mbps extension of the same lossless format under a new name, DTS-HD Master Audio, 100% lossless and bit-for-bit identical to the studio master, as claimed by DTS.</p>

<p>DTS-HD uses a set of extensions to the coherent acoustics audio coding system, comprised of DTS Digital Surround, DTS-ES, and DTS 96/24, which allows the format to down-mix to 5.1 and two-channel, while delivering audio quality at bit rates extending from legacy DTS Digital Surround to 7.1 DTS-HD channels, using a single stream up to 18Mbps.</p>

<p><u>Dolby TrueHD</u><br />
Dolby TrueHD can support up to 14 discrete of lossless 24-bit/96 kHz audio channels at bit rates as high as 18Mbps, although HD DVD and Blu-ray Disc standards currently limit their maximum number of audio channels to eight.  Dolby TrueHD is 100% lossless audio, delivering audio playback performances in the home that are bit-for-bit identical to studio masters, designed for next generation HD DVD and Blu-ray formats. </p>

<p></p>

<p><br />
<h2>Hi-bit Audio Application to Hi-def DVD Formats</h2></p>

<p>In September 23, 2004, Dolby Laboratories announced that the DVD Forum decided to include Dolby Digital Plus and MLP Lossless, the core audio technology behind multichannel DVD-Audio, as mandatory audio standards for HD DVD.  Later, Dolby TrueHD was also selected as mandatory audio format for HD DVD.  However, both Dolby formats were selected as optional audio formats for Blu-ray players.</p>

<p>DTS-HD was declared as optional for the players of both Hi-def DVD formats. </p>

<p>In other words, Blu-ray approved as optional the 3 hi-bit audio formats (Dolby Digital Plus, Dolby TrueHD, and DTS-HD), the only mandatory codecs for Blu-ray are the legacy 5.1 DD and DTS.   </p>

<p>According to Silicon Image, the HDMI transport is able to handle 24Mbps of audio speed, suitable for any of the proposed audio formats from either disc format, including DTS HD Master Audio.  However, the version 1.3 HDMI specification would enable the players to output those audio formats over HDMI, those protocols and specifications are expected to be finalized on the first half of 2006.  Dolby is working closely with Silicon Image to ensure transmission of Dolby Digital Plus and TrueHD signals on HDMI v. 1.3. </p>

<p>As an alternative, HD players with internal hi-bit-rate decoders are expected to also have 6.1 or 7.1 analog outputs that could support the hi-bit-rate and be connected to receivers with 6.1 or 7.1 channel analog inputs.</p>

<p><br />
<h2>Analysis</h2></p>

<p>My first human reaction:  How many more multi-channel audio formats a consumer needs?  How many more connections, A/V receivers, and audio-processors a consumer needs to continuously upgrade in this multi-channel matrix/discrete, lossy/lossless, low-bit/hi-bit, 5/6/7.1 marathon?  How many consumers actually invest beyond 5.1 low-bit lossy because they find a difference their ears and pockets justify as considerable benefit?  Where is the significant content with more channels to justify more decoding/amps/speakers at a legacy 5.1 consumer's home?  </p>

<p>The large capacity of Hi-def DVD brought with it an invitation to use part of the vast space to pursue cleaner multi-channel audio for the soundtrack of a movie, but the lossless hi-bit audio space requirements to allow a maximum/approved speed of operation of 18Mbps are reaching levels that could even exceed the space requirements of the HD video itself in the same disc (using VC1 for example), not to mention using DTS HD Master Audio maxing at 24 Mbps, the risk of exceeding the capacity of a dual layer 30GB HD DVD disc, or even a 50GB Blu-ray disc depending the combination of video and audio codecs used, and that is storing just one hi-bit lossless codec in the disc, about trying to fit two, such as the typical Dolby/DTS pair, now in hi-bit? Make the math with just one.</p>

<p>In other words even when using a dual layer approach the discs might be limited to include only one of the hi-bit multichannel audio codecs, and possibly be very close to the speed capacity of the format when a non-aggressive compression HD video codec and the hi-bit lossless audio are played simultaneously, as usually is the case.   </p>

<p>In September 2005, Dolby announced that A/V receivers capable of processing PCM over their HDMI 1.1 inputs should also be able to have sufficient bandwidth to accept the HD video and the PCM multi-channel audio decoded by the Hi-def DVD player.  Any HDMI suited receiver should be capable to input the PCM and reproduce the higher bandwidth of the soundtracks.  Initially, it was believed that those HDMI 1.1 suited A/V receivers would have to use analog cables from the multi-channel audio connectors (as with DVD-Audio), and wait until specification version 1.3 of HDMI be completed (and eventually change to a 1.3 compliant A/V receiver).  </p>

<p>According to Dolby, there should be no need to replace an HDMI 1.1 suited receiver to get the benefit of the higher-bit audio formats.  However, when using the latest HDMI version 1.3 from player to receiver, the decoding would not have to happen in the player, the connection would stream the native mandatory and optional audio formats to the HDMI 1.3 suited A/V receiver, which would perform the decoding job.  Reportedly, DTS intends to suit players as well as receivers with their decoders, Dolby was quoted as concentrating initially on players.</p>

<p>Hi-definition DVD disc players are expected to support Internet-streamed audio content (such as director's comments) while playing the movie, and they have to internally mix the various audio components (soundtrack, Internet, PCM sounds, etc) before converting the final audio mix to individual PCM channels to be output over the HDMI connection.<br />
 <br />
The newer hi-bit formats, Dolby Digital Plus lossy, Dolby TrueHD lossless, and DTS HD lossless (previously named DTS++ lossless, and now extended to Master Audio 24 Mbps), are much faster than the supported speed of typical digital coaxial connections (S/PDIF) used for the current legacy Dolby Digital and DTS multichannel audio formats, however, those legacy connections would still transport the down-converted legacy versions (derived from the hi-bit) produced by Hi-def DVD players.</p>

<p>As mentioned above, if an existing receiver does not have HDMI inputs it can still use the multichannel analog connections (6 to 8 RCA type of connections) until is time for the upgrade; remember the convenient DVD-Audio mess of wires?  </p>

<p>In selecting a Hi-def DVD player of any format, one factor of choosing one model over the other could be the implementation of the Hi-bit multichannel codecs that are optional (Dolby TrueHD, Dolby Digital Plus, DTS HD, depending on the format).  </p>

<p>Even when not having the latest A/V receiver that could decode the Hi-bit formats itself using the HDMI 1.3 connection, if a consumer is interested in a system to reproduce the optional DTS-HD for example, that consumer would be making a better investment by choosing a Hi-def player that decodes DTS-HD by itself.  The existing receiver, using the alternative connections above, would be spared from an unneeded upgrade just for that purpose.  </p>

<p>There are players that were announced with various combinations of optional codecs, one Blu-ray player was announced to support DTS HD but not Dolby TrueHD, another player supported True HD but just as a 2 channel feature.  A manufacturer is not obliged to suit the player with optional codecs, so a closer look at the specs would help on the selecting decision of the player, or even the format.   </p>

<p>Logically, the multi-cable analog connection alternative above assumes the receiver "has" those analog connections typically used for DVD-Audio, not all do.  In which case the Hi-def DVD player would do the multi-channel decoding and the D/A conversions for each of the 8 channels, 8 cables would carry them to the analog inputs of the receiver, which would convert them back to digital to perform any digital processing the receiver needs to do before the amplification stage of each channel to reach the speakers.  Quite a few conversions for that solution, not as clean as a direct digital connection but still a way to get the benefit of new codecs. </p>

<p>Since a player is also expected to make available the lower bandwidth compressed lossy versions (DD at 640kbps, and DTS at 1.5 Mbps) of those hi-bit formats over the typical S/PDIF digital connections, a consumer has another fallback plan of connectivity to older receivers, other than the analog connections, until equipment could be upgraded.  Audio mixes over the soundtrack would be missing though.</p>

<p>Although I could not verify the following in detail myself, Dolby was quoted stating that certain formats would not be able to be down-compatible 100% using the digital coax legacy connections, such as:</p>

<p>7.1 channels of 96/24 PCM at 18.4Mbps (in both Hi-def DVD formats)<br />
HD DVD's two channels of 192/24 PCM at 9.2Mbps<br />
Blu-ray's optional 6-channel uncompressed 192kHz/24-bit PCM at 27.6Mbps</p>

<p>In summary, there will be a variety of backward compatibility connectivity options to allow consumers to still be able to use the existing audio equipment at their current multi-channel audio capabilities when playing back the new audio formats of High Definition DVD discs/players, but there will be enough incentive for upgrades.  </p>

<p>Upgrading to an A/V receiver suited with HDMI 1.1 would bring the full benefit of the lossless audio formats transporting the channels digitally as PCM, and if the upgrade could be done to HDMI 1.3 connectivity it would open the possibility to do hi-bit decoding on the receiver, giving the consumer the option of doing the audio decoding in the A/V receiver or the player, which ever sounds best for the consumer, and perhaps been able to decode a hi-bit codec missing in the player but the receiver has.</p>

<p>In that scenario, the player would just stream out of HDMI the undecoded hi-bit multichannel signal for the A/V receiver to decode, expanding the flexibility of the audio part of the system.  However, check the specific conditions on the charts above, it seems that using the HDMI connection for streamed audio (not PCM) might disallow the mixing of the additional audio features of Hi-def DVD over the soundtrack, unless the player is suited with an optional, and probably unusual, "encoder".</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>April 21, 2006  7:35 AM</b>
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
			<?=getComments(367)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 367)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/multichannel-audio-for-hd.php" type="text/javascript" charset="utf-8"></script>
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