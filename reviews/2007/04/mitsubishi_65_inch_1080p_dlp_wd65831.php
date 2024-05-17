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
		AND e.entry_id = 578";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 578 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 578 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 578";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi-65-inch-1080p-dlp-wd65831.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 578";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Mitsubishi 65 inch 1080p DLP (WD-65831)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Mitsubishi 65 inch 1080p DLP (WD-65831)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Mitsubishi 65 inch 1080p DLP (WD-65831)" />
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
	<title>HDTV Magazine - Mitsubishi 65 inch 1080p DLP (WD-65831)</title>
	<meta name="keywords" content="noise reduction, video noise, independently separately, light engine, separate colors, mitsubishi, video, good, screen, settings, color, calibration, dlp, noise, colors, our, picture, reduction, fan, viewing, ara, contrast, digital, detail, see" />
	<meta name="description" content="If you have listened to this show for more than a couple of months you know that Ara has purchased a 65 inch Mitsubishi DLP WD-65831 $2950 online. Many will remember the journey that got him there. He looked at the SONY SXRD, JVC HD-ILA, and he even toyed with buying the Samsung LED based DLP. In the end, one Saturday afternoon while hanging out in a Magnolia store he saw the Mitsubishi and decided it was that TV that was going into the Media Room.
" />
	<meta name="title" content="Mitsubishi 65 inch 1080p DLP (WD-65831)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Mitsubishi 65 inch 1080p DLP (WD-65831)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi-65-inch-1080p-dlp-wd65831.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="If you have listened to this show for more than a couple of months you know that Ara has purchased a 65 inch Mitsubishi DLP WD-65831 $2950 online. Many will remember the journey that got him there. He looked at the SONY SXRD, JVC HD-ILA, and he even toyed with buying the Samsung LED based DLP. In the end, one Saturday afternoon while hanging out in a Magnolia store he saw the Mitsubishi and decided it was that TV that was going into the Media Room.
" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=578', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi-65-inch-1080p-dlp-wd65831.php">Mitsubishi 65 inch 1080p DLP (WD-65831)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>April 17, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=277&category=HDTV Displays">HDTV Displays</a></b>
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
				<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2007/April20.html">http://www.htguys.com/archive/2007/April20.html</a></center>
<br />

<p>If you have listened to this show for more than a couple of months you know that Ara has purchased a 65 inch <a href="http://www.mitsubishi-tv.com/j/i/18326/WD65831.html?cid=524">Mitsubishi DLP WD-65831</a> $2950 online (<a href="http://www.htguys.com/shop.php?id=B000JLCTC2">Buy Now</a>). Many will remember the journey that got him there. He looked at the SONY SXRD, JVC HD-ILA, and he even toyed with buying the Samsung LED based DLP. In the end, one Saturday afternoon while hanging out in a Magnolia store he saw the Mitsubishi and decided it was that TV that was going into the Media Room.<br />
<strong><br />
Description</strong><br />
This is a 1080p TV with two HDMI inputs that accept 1080p picture sources. In addition to the HDMI inputs the TV also supports PC DVI-I (1), Component (3), S-Video, RCA and IEEE1394 (Front and Back). The TV has 6-Color Light Engine and Mitsubishi's own TurboLight180 lamp system that is supposed to provided high detail and bright vivid colors. You can adjust the intensity and tint of each color independently and separately for each input providing ultimate calibration options. The TV is also CableCard ready so you won't need a digital set top box if your cable provider supports the technology. The TV also has a memory card reader for viewing your digital images on the screen. Finally, the TV ships with two remote controls. One that is full featured and a second simple remote that only include the most common control functions.</p>

<p>The TV is large (40 3/4 x 58 1/2 x 19 13/16  99.0 lbs.). In fact, Ara's wife was ready to send it back when it showed up. Oddly enough, after about a month and a half of use, it no longer feels so large. With that said, when you consider that the TV has a 65 inch screen and is just under 20 inches deep its not as big as you think it would be. The TV is aesthetically pleasing. It has a piano black finish with a thin bezel that barely frames the screen. The matching base completes the look and is quite functional. One note of warning. Be prepared to dust the TV often as the black will show dust. The TV fits on the stand nicely and both complement each other. The stand easily will support your electronics and center channel speaker. Cable management is easy as well.</p>

<p>Features (from Mitsubishi's Website):<ul><li>1080P DLP Display</li><li>Mitsubishi Exclusive 6-Color Light Engine - generates yellow, cyan and magenta directly for brighter colors, a wider range of colors and whiter whites.</li><li>TurboLight180 - Our unique, patented optical design focuses light more efficiently to produce a 10% brighter on-screen image.</li><li>High Contrast Picture</li><li>Plush1080p - Mitsubishi's video scaling technology</li><li>Tru1080p Processing</li><li>4D Video Noise Reduction - Mitsubishi’s exclusive 4D Video Noise Reduction uses advanced algorithms to better identify video noise from fine detail and correct the signal rather than distort it.</li><li>PerfectColor - ability to adjust the intensity of six separate colors independently of each other and separately for every input.</li><li>PerfecTint - ability to adjust the tint of six separate colors independently of each other and separately for every input.</li><li>DeepField Imager - constantly adjusts brightness and contrast for optimum settings in all areas of the picture.</li><li>SharpEdge - It enhances horizontal and vertical edges for stunning picture precision</li><li>Video Modes: Brilliant / Bright / Natural</li></ul></p>

<p><br />
<strong>Setup</strong><br />
Hooking the TV was straight forward. We connected the antenna to the digital tuner and and ran our digital inputs (Satellite TV and Up-converting DVD player) through our switching receiver and then into the HDMI input. Once setup we scanned the digital airwaves and we were ready to go. We used the DVE to calibrate the TV (see the settings at the end of this writeup). But that was just our starting point. We continued to tweak the picture until we had it just so. There are settings on the TV like Video Noise Reduction, Sharp Edge, and Deep Field that we just turned on and off to see if we liked the results. For the record we turned Noise Reduction and Sharp Edge off. We left Deep field on.</p>

<p><strong>Performance:</strong><br />
The first thing we have to say about this TV is that you need to be prepared to spend some time with it calibrating it. We typically say you need to take any TV you buy off its default settings  to get a good picture, and that is true about this TV as well. However, if that is all you do with this TV you will really be missing out (not to mention spending too much money for the TV). With this TV we strongly recommend doing a full calibration or hiring an ISF certified professional to do it for you. Only then will it perform to its fullest capability. For reference purposes we are including the calibration settings we used for this review at the end of this review.</p>

<p>The 831 has some of the deepest blacks we have seen on a TV that isn't a plasma, it has good color representation (after the Perfect Color and Perfect Tint adjustments), and great detail in dark scenes. Watching HD is like looking through your perfectly cleaned window. The detail is amazing. Standard Definition looks pretty good but with such a large screen its easy to see the flaws in the picture. The speakers on the TV do a good job and sound surprisingly full. But to be honest with you after the initial listen they have not been turned on since.</p>

<p>No (virtually no) rainbows! One of the issues with DLP TVs is an something  known as Rainbows. From Wikipedia: The DLP "Rainbow Effect" This visual artifact is best described as brief flashes of perceived red, blue, and green "shadows" observed most often when the projected content features bright/white objects on a mostly dark/black background (the scrolling end credits of many movies are a common example). In the month and a half Ara has had this TV he has only seen two rainbows and they were barely perceivable. In contrast, on his his second generation DLP, Ara sees rainbows all the time.</p>

<p>Another issue that affects Rear Projection TVs is something known as Silk Screen Effect (SSE). Some viewers can see the texture of the screen in front of the image. Its pretty bad on the default settings. It can be minimized and almost eliminated by properly setting you contrast and brightness. With that said it is still noticeable under certain viewing conditions. In Ara's case it is most noticeable when watching hockey and specifically when looking at the ice. But after calibration it has not detracted from the overall look of the game.</p>

<p>One thing to consider with DLPs is that their off angle viewing is not as good as plasmas and some newer LCDs. The Mitsubishi does a good job at off angle viewing and not so good with vertical angles. That is to say if you play video games standing up this TV is not for you. For typical TV and movie watching there will be no issues for almost anyone sitting in front of the TV. If you have some seats way off to the side (beyond 145 degrees) your viewing experience will start to degrade.</p>

<p>Some have complained about the fan noise of this TV. We measured it with a Sound Pressure Meter right at the source of the fan and got a reading of 65 dB. That's like putting your ear at the exhaust fan of the TV.  To put this in perspective 65 dB is right between clothes dryer (60 dB) and a Vacuum Cleaner (70 dB). At a normal distance of 14 feet, the meter read 49 dB. Which is just above a bedroom at night. So the fan is not an issue. Any audio you have in your home theater system will be well above the fan in a dead quiet room.</p>

<p>The WD-65831 is a bit pricey at just under $3000 (its still cheaper than our first HDTVs that are still in use today. If you want the size but don't want to spend the Money, Mitsubishi has a WD-65731 for $1940 (Buy Now). It has many of the same features but a slightly less capable lamp system and light engine.</p>

<p><strong>Final Thoughts:</strong><br />
This is one of the best TVs we have seen and we look at allot of them. The colors are bright and vivid. Dark scene detail is impressive and HD looks real. But to get the most our of this TV you'll need to invest in a Calibration DVD or have it professionally calibrated. The only real complaints we have are that its a bit expensive and it needs the afore mentioned calibration support.</p>

<p><strong>Calibration Settings:</strong><ul><li>Contrast: 20</li><li>Brightness: 30</li><li>Color: 36</li><li>Tint: 34</li><li>Sharpness: 24</li><li>Color Temperature: Low</li><li>Picture Mode: Natural</li><li>Video Noise Reduction: Off</li><li>Sharp Edge: Off</li><li>Deep Field: On</li></ul></p>

<p><strong>Perfect Color</strong><ul><li>Magenta: 32</li><li>Red: 24</li><li>Yellow: 31</li><li>Green: 46</li><li>Cyan: 32</li><li>Blue: 32</ul></li></p>

<p><strong>Perfect Tint</strong><ul><li>Magenta: 47</li><li>Red: 29</li><li>Yellow: 29</li><li>Green: 55</li><li>Cyan: 46</li><li>Blue: 28</ul></li></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>April 17, 2007  8:45 AM</b>
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
			<?=getComments(578)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('The HT Guys', 578)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi-65-inch-1080p-dlp-wd65831.php" type="text/javascript" charset="utf-8"></script>
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