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
		AND e.entry_id = 444";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 444 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 444 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 444";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/09/tivo-debuts-series3-high-definition-digital-media-recorder.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 444";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download TiVo Debuts Series3 High Definition Digital Media Recorder" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="TiVo Debuts Series3 High Definition Digital Media Recorder" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="TiVo Debuts Series3 High Definition Digital Media Recorder" />
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
	<title>HDTV Magazine - TiVo Debuts Series3 High Definition Digital Media Recorder</title>
	<meta name="keywords" content="tivo series, high definition, home entertainment, digital cable, tivo inc, tivo, series, box, thx, digital, cable, features, video, new, high, television, definition, entertainment, programming, advanced, home, product, hes, available, experience" />
	<meta name="description" content="TiVo Inc., the creator of and a leader in television services for digital video recorders (DVRs), announced today the launch of the high end TiVo&amp;reg; Series3&amp;trade; HD Digital Media Recorder - the first stand-alone TiVo product that is HD compatible. Available beginning mid-September, the TiVo Series3 HD box is the world's first THX&amp;reg;-certified, digital video recorder, delivering audio and video that truly maintains the fidelity of the original broadcast. Once again, TiVo is" />
	<meta name="title" content="TiVo Debuts Series3 High Definition Digital Media Recorder" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="TiVo Debuts Series3 High Definition Digital Media Recorder" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/09/tivo-debuts-series3-high-definition-digital-media-recorder.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="TiVo Inc., the creator of and a leader in television services for digital video recorders (DVRs), announced today the launch of the high end TiVo&amp;reg; Series3&amp;trade; HD Digital Media Recorder - the first stand-alone TiVo product that is HD compatible. Available beginning mid-September, the TiVo Series3 HD box is the world's first THX&amp;reg;-certified, digital video recorder, delivering audio and video that truly maintains the fidelity of the original broadcast. Once again, TiVo is" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=444', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/09/tivo-debuts-series3-high-definition-digital-media-recorder.php">TiVo Debuts Series3 High Definition Digital Media Recorder</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 12, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">TiVo Debuts Revolutionary Series3 High Definition Digital Media Recorder</p>

<p><i><div align="center">The TiVo Series3 HD Box is the World's first THX Certified DVR and Delivers the Best Home Theater Experience with Advanced Broadband Features and Two CableCARD Slots for Digital Cable Integration</div></i><br />
<img src="http://www.awltovhc.com/image-1683082-10440996" width="1" height="1" border="0"/><br />
<b>Alviso, CA -- September 12, 2006</b> - TiVo Inc., the creator of and a leader in television services for digital video recorders (DVRs), announced today the launch of the high end <a href="http://www.kqzyfj.com/click-1683082-10440996">TiVo&reg; Series3&trade; HD Digital Media Recorder</a> - the first stand-alone TiVo product that is HD compatible. Available beginning mid-September, the TiVo Series3 HD box is the world's first THX&reg;-certified, digital video recorder, delivering audio and video that truly maintains the fidelity of the original broadcast. Once again, TiVo is setting a new standard for digital cable users with its Emmy&reg;-awarding service and exclusive feature set...now in high definition!</p>

<p>"TiVo continues to be the best way to watch television and we are very proud to extend the TiVo experience into the world of high definition with the release of the TiVo Series3 HD box," said Tom Rogers, CEO and President of TiVo.</p>

<p><a href="http://www.kqzyfj.com/click-1683082-10440996"><img src="/images/bulletins/tivo-series3-HD-DVR.jpg" alt="TiVo&reg; Series3&trade; HD Digital Media Recorder" align="left"></a>The TiVo Series3 HD box delivers the ultimate in high definition entertainment, allowing the consumer to experience TiVo's acclaimed service features, such as Season Pass&trade; recordings and WishList&reg; searches, in sharp, vivid high-definition images. Extensive video analysis and performance testing by THX ensures content will always playback at the maximum quality and resolution. The advanced chipset in the TiVo Series3 HD box also lays the groundwork for support for more advanced download features in the future.<br clear="all" /></p>

<p><em><a href="http://www.kqzyfj.com/click-1683082-10440996"><img src="/images/bulletins/tivo-series3-HD-DVR-remote.jpg" alt="TiVo&reg; Series3&trade; Remote" align="right"></a></em>Dual tuners allow subscribers to record two different shows in HD at the same time, while watching a third pre-recorded show. With the high quality OLED front-panel display, the TiVo Series3 HD box will show what is recording, even when the television is off. The TiVo Series3 HD box enables the user to record up to 32 hours of HD programming, or up to 300 hours of recording capacity in standard definition. The TiVo Series3 HD box also features a new, sleek, backlit remote control.</p>

<p>The TiVo Series3 is designed to fit seamlessly into the most discerning home theater systems. It is compatible with digital cable*, analog cable and is the first TiVo to support over-the-air digital HD (ATSC). Later this year, it will also support the newly released TiVoCast service feature which allows users to access content directly through their broadband connection, introducing the next revolution of Internet and cable delivery. Future software releases can even enable advanced MPEG-4 based download features.</p>

<p><a href="http://www.kqzyfj.com/click-1683082-10440996"><img src="/images/bulletins/tivo-series3-HD-DVR-back.jpg" alt="TiVo&reg; Series3&trade; DVR (Back)" align="left"></a>With a built-in Ethernet jack and USB ports, the TiVo Series3 HD box also provides advanced connectivity and easy networking, making it simple to access an additional suite of exclusive TiVo features.</p>

<p>"Our subscribers have eagerly anticipated the day they can use our services in high definition," said Jim Denney, Vice President of Product Marketing at TiVo. "The TiVo Series3 HD DVR gives our customers the best way to experience HDTV in visual and audio performance, an easy, intuitive way to find and record HD programming and a broad set of multimedia and networking capabilities that greatly exceed other generic offerings available allowing subscribers the freedom to enjoy their favorite programming whenever they chose to do so."</p>

<p>"As more and more digital video recorders get connected to HDTVs, the demand for higher playback quality rises," said Dr. Michael Rudd, chief of AV architecture at THX Ltd. "With THX certification, the integrity of HD content won't be compromised. Our collaboration with TiVo has resulted in a product that promises to capture, store and playback HDTV programs with the detail and clarity that is true to the original broadcast."</p>

<p>"We are excited to bring the TiVo Series3 HD box to the lineup of HES suppliers," said Jim Ristow, Director of Home Entertainment Source (HES). "The new TiVo HD box is at the forefront of technology, as are HES dealers, making the relationship between TiVo and HES an excellent match."</p>

<p>Favorite existing TiVo features included in the TiVo Series3 HD box:</p>

<p> * <B>TiVo Online Scheduling</B>: Schedule last-minute recordings from the office or on the road from anywhere you can access the Internet.<br />
 * <B>WishList Searches</B>: Easily find programs by actor, director, keyword or topic. The TiVo service works to find and record all the programs related to that topic - like an advanced search engine for your television.<br />
 * <B>Season Pass Recordings</B>: Automatically record every episode, even if the network schedule changes. It can even skip repeat episodes.<br />
 * <B>TiVo KidZone</B>: Coming later this year to the Series3 HD box, only TiVo gives parents the power to easily create a customized area for their children, with only the programs they've pre-approved.</p>

<p>Broadband connected TiVo Series3 subscribers will have access to a variety of multimedia services, networking options and entertainment choices:</p>

<p> * <B>TiVoCast</B>: Download video programming via broadband to your TiVo box. Programming comes from a diverse selection of media brands and producers, including The New York Times, CNET, the NBA, iVillage, and many others. TiVoCast will be available on the Series3 HD later this year.</p>

<p> * <B>TiVo Online Services</B>: View both personal photo slideshows and those shared by friends with Yahoo! Photos, and check local weather and traffic from Yahoo!. Additionally, browse and buy movie tickets from Fandango, discover new music on Live365, and listen to entertaining podcasts.<br />
 * <B>TiVo Mobile</B>: Browse TV listings and schedule recordings directly from select Verizon Wireless phones. The downloadable program will be available later this year.</p>

<p>The TiVo Series3 HD box will join the existing TiVo product line, and will soon be available for $799.99 at retail stores including select Best Buy, Circuit City, Ultimate Electronics, Fry's and online at www.tivo.com.</p>

<p>This product will be shown at CEDIA in booth 378 beginning 9/13-9/17.</p>

<p>* A security card (CableCARD) provided by your cable operator is required to view encrypted digital cable programming. Two CableCARDs may be required for dual-tuner functionality. Certain advanced and interactive digital cable services such as video-on-demand, a cable operator's enhanced programming guide, and data-enhanced television services may require the use of a separate cable company-provided set-top box.</p>

<p><B>About THX Ltd.</B><br />
Born out of George Lucas' vision to improve the movie-going experience, THX is a driving force in cinema, post-production, car audio and home entertainment.</p>

<p>THX is dedicated to developing new ways to make the creation, delivery and presentation of entertainment content more efficient, more powerful and more enjoyable. Today, the world's premier commercial cinemas, post-production studios, car audio systems and home entertainment products incorporate THX technologies and have achieved the coveted THX Certified status. For more information, visit www.thx.com.</p>

<p>THX and the THX Logo are trademarks of THX Ltd., which may be registered in some jurisdictions. All rights reserved.</p>

<p><b>About HES</b><br />
Home Entertainment Source, the A/V specialty division of Brand Source, is a non-profit organization with over 450 members. With annual sales exceeding $1.2 billion, HES ranks as one of the largest buying groups in the CE industry.</p>

<p><b>About TiVo Inc.</b><br />
Founded in 1997, TiVo (NASDAQ: TIVO) pioneered a brand new category of products with the development of the first commercially available digital video recorder (DVR). Sold through leading consumer electronic retailers, TiVo has developed a brand which resonates boldly with consumers as providing a superior television experience. Through agreements with leading satellite and cable providers, TiVo also integrates its full set of DVR service features into the set-top boxes of mass distributors. TiVo's DVR functionality and ease of use, with such features as Season Pass&trade; recordings and WishList&reg; searches, has elevated its popularity among consumers and has created a whole new way for viewers to watch television. With a continued investment in its patented technologies, TiVo is revolutionizing the way consumers watch and access home entertainment. Rapidly becoming the focal point of the digital living room, TiVo's DVR is at the center of experiencing new forms of content on the TV, such as broadband delivered video, music and photos. With innovative features such as TiVoToGo&trade; transfers and online scheduling, TiVo is expanding the notion of consumers experiencing "TiVo, TV your way." TiVo is also at the forefront of providing innovative marketing solutions for the television industry, including a unique platform for advertisers and audience measurement research. The company is based in Alviso, Calif.</p>

<p>TiVo, Series3, WishList, Season Pass, TiVoCast, TiVoToGo, and the TiVo Logo are trademarks or registered trademarks of TiVo Inc.'s subsidiaries worldwide. © 2006 TiVo Inc. All rights reserved. All other company or product names mentioned may be trademarks or registered trademarks of the respective companies with which they are associated.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 12, 2006  5:52 AM</b>
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
			<?=getComments(444)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 444)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/09/tivo-debuts-series3-high-definition-digital-media-recorder.php" type="text/javascript" charset="utf-8"></script>
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