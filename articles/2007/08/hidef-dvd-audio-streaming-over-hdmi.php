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
		AND e.entry_id = 654";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 654 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 654 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 654";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/08/hidef-dvd-audio-streaming-over-hdmi.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 654";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Hi-Def DVD - Audio Streaming Over HDMI" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Hi-Def DVD - Audio Streaming Over HDMI" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Hi-Def DVD - Audio Streaming Over HDMI" />
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
	<title>HDTV Magazine - Hi-Def DVD - Audio Streaming Over HDMI</title>
	<meta name="keywords" content="dolby digital, digital plus, blu ray, pass feature, according dolby, dolby, digital, hdmi, audio, dvd, player, plus, pass, feature, players, blu, ray, soundtrack, format, dts, advanced, streaming, legacy, because, kbps" />
	<meta name="description" content="In April 2007, as part of my analysis about Hi-Def DVD and Multi-channel audio in my annual HDTV Technology Review, I discussed the subject with Craig Eggers and Roger Dressler, Dolby executives. Some of the items discussed were: soundtrack streaming pass-through feature over HDMI in near future players, streamed Dolby Digital Plus not supported by HDMI versions 1.1 and 1.2 (while DTS HD is), audio-mix encoders for legacy connectivity, Dolby Digital at 640 kbps, etc. This article summarizes those conversations as follows:" />
	<meta name="title" content="Hi-Def DVD - Audio Streaming Over HDMI" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Hi-Def DVD - Audio Streaming Over HDMI" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/08/hidef-dvd-audio-streaming-over-hdmi.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In April 2007, as part of my analysis about Hi-Def DVD and Multi-channel audio in my annual HDTV Technology Review, I discussed the subject with Craig Eggers and Roger Dressler, Dolby executives. Some of the items discussed were: soundtrack streaming pass-through feature over HDMI in near future players, streamed Dolby Digital Plus not supported by HDMI versions 1.1 and 1.2 (while DTS HD is), audio-mix encoders for legacy connectivity, Dolby Digital at 640 kbps, etc. This article summarizes those conversations as follows:" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=654', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/08/hidef-dvd-audio-streaming-over-hdmi.php">Hi-Def DVD - Audio Streaming Over HDMI</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>August  1, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=280&category=Blu-ray">Blu-ray</a></b>
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
				<p>In April 2007, as part of my analysis about Hi-Def DVD and Multi-channel audio in my annual HDTV Technology Review, I discussed the subject with Craig Eggers and Roger Dressler, Dolby executives. Some of the items discussed were: soundtrack streaming pass-through feature over HDMI in near future players, streamed Dolby Digital Plus not supported by HDMI versions 1.1 and 1.2 (while DTS HD is), audio-mix encoders for legacy connectivity, Dolby Digital at 640 kbps, etc. This article summarizes those conversations as follows:</p>

<p><br />
<B>Streaming Pass-through on HD DVD Players</B></p>

<p>All this started upon my invitation to visit Dolby Labs in NY for their HDMI 1.3 tour in December 2006. One of the slides of the presentation by Dolby made reference to an "audiophile feature" that would allow the Hi-Def DVD player's soundtrack stream read from the disc to bypass all the internal mixing of the player and be outputted undisturbed using the HDMI output of the player.</p>

<p>The stream would certainly have to be decoded externally by an A/V receiver or Pre/pro enabled with the decoders for the soundtrack format of the stream.</p>

<div align="center"><img src="/images/articles/hi-def-dvd-decoding-mixing.jpg" alt="Hi Def DVD Decoding & Mixing" /></div>

<p>One immediate value I saw with this feature was the potential for improved sound quality because the soundtrack would not be submitted to any mixing or audio processing for advanced interactivity features one might not want to have, if the audio would be better without them.</p>

<p>Another value I saw was that a player having this pass-through feature, but not having all the multi-channel advance decoders, could offer a consumer the flexibility to compensate for the lack of decoders by doing the decoding in a newer A/V receiver (if suited with those decoders), without having to replace the player, specially considering that some Blu-ray players cost above $1000.</p>

<p>Even when the new Toshiba player has an HDMI 1.3 output it does not pass-through any native (undecoded) soundtrack streams from the disc over HDMI in Advanced Content mode, regardless of the audio coding format. This is because the interactive advanced content mix is considered a key element of the HD DVD format, which would not be able to be enjoyed to its fullest if the pass-through streaming function is implemented.</p>

<p>In the HD DVD format, the Advanced Content flag in the disc impedes the pass-through function and the player mixing cannot be avoided. It is not a manufacturer's or a consumer's choice.</p>

<p>According to Dolby, in the future, the DVD forum may consider the subject of allowing the pass-through function in HD DVD players, but no action has been taken at this time.</p>

<p>However, there may be opposition from the content makers for such pass-through feature. They may not want to compromise the interactive advance content feature of the format because they invested a lot of time defining that format the way they wanted it.</p>

<p>The software companies, who are also members of the DVD Forum, asked for this capability not to be optional in the HD DVD format, so that they can insure that consumers would not turn it off, and always obtain the full interactive experience of the format.</p>

<div align="center"><img src="/images/articles/hi-def-dvd-bitstream.jpg" alt="Hi Def DVD Bitstream High Resolution Audio" /></div>

<p>If the DVD Forum approves the pass-though feature, its implementation would most probably be done only in future players, and it is not known at this time if a revision to current HD DVD players could be made with a firmware upgrade. A similar implementation scenario could be applicable to Blu-ray as well, more on that below.</p>

<p>According to Dolby, this subject was better to be discussed with the HD DVD Promotion Group. I exchanged emails with them, but since no comments were made I assumed that the statements from Dolby about HD DVD and the DVD Forum were correct.</p>

<p><br />
<B>Streaming Pass-through on Blu-ray Players</B></p>

<p>Dolby stated that technically it is possible to pass-through advanced audio streams thru HDMI 1.3, provided the right protocols are implemented at both ends of the connection.</p>

<p>Dolby commented that it believed that the HDMI 1.3 suited Sony Play Station 3 most likely did not implement the stream-out pass-through feature for its next generation high definition codecs because the needed HDMI 1.3 protocols were still in development at product introduction.</p>

<p>The first HDMI 1.3 enabled Blu-ray players do not have at the present the stream-out functionality implemented. However, later players are expected to support the feature.</p>

<p>As with HD DVD players, Blu-ray players can only have the full interactive experience if they decode the advanced multi-channel audio soundtrack and mix it with the added audio interactive features within the player.</p>

<p>Another key reason for all the Hi-Def DVD players to do the decoding within the player is to assure playback compatibility with current and legacy AV receivers in the marketplace.</p>

<p>When HD DVD and BD players were first introduced, the HDMI protocols to deliver high-resolution audio bit-streams were not completed, nor did very many A/V receivers include an HDMI input. It is only with the advent of HDMI 1.3 that both next generation audio codecs from Dolby could be transported over HDMI.</p>

<p>Unlike HD DVD, the Blu-ray format left it up to the player manufacturer (and to the consumer) whether or not to use the interactivity feature and permit the streaming of the undecoded soundtrack using a pass-through feature.</p>

<p>In summary, if the pass-through feature is eventually used for the advanced audio streaming of only the soundtrack, it will not carry the audio add-on mixes, regardless of the audio format of the soundtrack.</p>

<p><br />
<B>Encoder for Legacy Audio Mix</B></p>

<p>An optional encoder in the player would allow the addition of the audio mixing over the soundtrack to output the legacy Dolby Digital 5.1 using the SPDIF or HDMI outputs streaming at 640Kbps.</p>

<p>However, the manufacturer of the player has the right to build the player without such legacy encoder. If a consumer is interested in the feature of audio mix over legacy audio connections it is recommended for the consumer to verify that such feature has been actually implemented in the player.</p>

<p><br />
<B>Streamed Dolby Digital Plus Not Carried Over HDMI 1.1/1.2 (While DTS HD is)</B></p>

<p>HDMI 1.3 is required to transport streamed Dolby Digital Plus. DTS-HD could be streamed thru HDMI 1.3 as well, but it could be streamed also thru earlier versions 1.1 and 1.2. The reason for the DTS-HD capability of the HDMI 1.1 and 1.2 specifications is because they were revised to carry up to 6 Mbps and to handle the protocols of DTS-HD, but not to support Dolby Digital Plus.</p>

<p>However, even when an A/V receiver might have HDMI 1.1 or 1.2 connections, it would not be able to decode DTS-HD unless it is a new model that is also suited with a new DTS-HD decoder. Otherwise, it will only decode legacy DTS.</p>

<p>Although Dolby Digital Plus has a lower bit rate requirement that the 6Mbps (3Mbps for HD DVD and 4.7 Mbps for current Blu-ray), the format was not included in the revision of the specification per Dolby's choice. To avoid confusing the market, the company preferred to maintain both Dolby advanced audio formats (Dolby Digital Plus and TrueHD) together and only within the same 1.3 specification.</p>

<p>According to Dolby, while it may have been possible to transport Dolby Digital Plus bit-streams over traditional optical or coaxial digital audio outputs, the company made a conscious decision not to license Dolby Digital Plus equipped A/V receivers until the HDMI 1.3 spec was fully implemented.</p>

<p>Dolby's position was that it made more sense ---and the enthusiast would be better served--- to introduce new technologies as a "package", as opposed to releasing Dolby Digital Plus and Dolby TrueHD technologies incrementally into the marketplace in different generations of A/V receivers.</p>

<div align="center"><img src="/images/articles/hi-def-dvd-truehd-bitrate-range.jpg" alt="Dolby TrueHD Bitrate Range" /></div>

<p>The 4.7 Mbps for Dolby Digital Plus for Blu-ray is the maximum data rate needed should the Blu-ray group authorize more than 7.1 channels, in the future. The current maximum data rate for 7.1 channel Dolby Digital Plus in Blu-ray is 1.7 Mbps.</p>

<p>However, Dolby recommends that consumers avoid only using data rate as a measurement of quality. A significant contributor to quality in lossy codecs such as Dolby Digital and Dolby Digital Plus is the efficiency of the technology as well as the density of data.</p>

<p>According to Dolby, not implementing Dolby Digital Plus streaming over commonly used HDMI 1.1/1.2 connections, as DTS does with DTS HD, is actually not an issue, because the existing HD DVD movies carry the Advanced Content flag that would not permit the player to avoid the audio mix over the soundtrack anyway.</p>

<p>On the Blu-ray side, even when the discs do not use such flag, in order to use Dolby Digital Plus, according to Dolby, the soundtrack must have 6.1 or 7.1 discrete signals, and because the few hundred movies available on Blu-ray do not have encoded more than 5.1 channels, there is no multi-channel signal capable to reach the threshold into the Dolby Digital Plus territory.</p>

<p><br />
<B>Dolby Digital Improved at 640kbps</B></p>

<p>As you might have been aware already, a higher bit-rate of 640 kbps for Dolby Digital 5.1 is obtained by a Hi-Def player from the disc, and is outputted using the legacy optical/digital audio connections (and HDMI).</p>

<p>Every Dolby Digital A/V receiver manufactured is capable of decoding the 640kbps Dolby Digital bit-stream, and there is an audible increase in audio quality with 640kbps Dolby Digital, compared to lower bit rate implemented in Standard Definition DVD discs.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>August  1, 2007  5:44 AM</b>
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
			<?=getComments(654)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 654)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/08/hidef-dvd-audio-streaming-over-hdmi.php" type="text/javascript" charset="utf-8"></script>
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