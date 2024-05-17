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
		AND e.entry_id = 4681";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4681 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4681 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4681";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/02/hdtv-expert-useful-gadgets-channel-master-cm7400-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4681";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV</title>
	<meta name="keywords" content="channel master, program guide, cnet internet, internet speeds, digital audio, program, channel, cable, channels, –, guide, vudu, master, internet, ’s, digital, time, start, hdmi, record, product, audio, video, should, speeds" />
	<meta name="description" content="Channel Master&amp;acirc;��s CM-7400 TV product (yes, that&amp;acirc;��s what it&amp;acirc;��s called) provides both over-the-air (DTTB) reception AND Hulu streaming and apps. How well does it work?" />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/02/hdtv-expert-useful-gadgets-channel-master-cm7400-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Channel Master&amp;acirc;��s CM-7400 TV product (yes, that&amp;acirc;��s what it&amp;acirc;��s called) provides both over-the-air (DTTB) reception AND Hulu streaming and apps. How well does it work?" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4681', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/02/hdtv-expert-useful-gadgets-channel-master-cm7400-tv.php">HDTV Expert - Useful Gadgets: Channel Master CM-7400 TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>February  9, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=341&category=Internet HD Video">Internet HD Video</a></b>
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
          <p>For those readers who are either (a) tired of ever-increasing bills for cable TV, or (b) looking for a different TV experience, I’ve got a product for you: Channel Master TV.</p>
<p> </p>
<p>This new product from the folks who were formerly best-known for TV antennas, amplifiers, and related products, is an ATSC receiver with dual DVRs (320 GB total capacity) and tuners, plus built-in WiFi connectivity for Vudu’s streaming HD movie service and Vudu apps. If you live in an area with plots of digital TV stations and are content to give up premium news, sports, and lifestyle channels (replacing some of them with Internet-delivered content), then you should check out this product.</p>
<p><a href="http://www.hdtvexpert.com/?attachment_id=1768" rel="attachment wp-att-1768"><img class="aligncenter size-full wp-image-1768" title="Channel-Master-TV MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-MR.jpg" alt="" width="600" height="337" /></a></p>
<p>WHAT’S IN THE BOX</p>
<p> </p>
<p>The CM7400 is a stylish, small (10” W x 7” D x 1.75” H) black box with three ‘rubber duck’ WiFi antennas attached to its rear panel. The front panel has a black gloss finish and shows only the power indicator, current time, and indicator LEDs for menu navigation. There’s also a small USB 2.0 port above the clock.</p>
<p> </p>
<p>The rear panel is loaded with jacks, including an RF loop-through (two ‘F’ connectors), component and composite analog video outputs, an HDMI output, a Toslink connector for digital audio, a second USB 2.0 port, a 100BaseT Ethernet port, and an eSATA connection, presumably for an external hard drive. Power for the CM-7400 comes from a small wall transformer – there’s no internal supply.</p>
<p><a href="http://www.hdtvexpert.com/?attachment_id=1769" rel="attachment wp-att-1769"><img class="aligncenter size-full wp-image-1769" title="Channel-Master-TV-back-panel-antennas-up MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-back-panel-antennas-up-MR.jpg" alt="" width="600" height="300" /></a></p>
<p>The supplied remote resembles those shipped by TiVo. It provides the usual secondary control of set-top boxes and other connected gadgets in your system, plus volume, channel, mousedisk, and numeric keypad functions.  It’s actually pretty hefty, compared to the box it’s controlling!</p>
<p> </p>
<p>To hook up the CM-7400, your best bet is to use the HDMI port, but if you have an older TV, the analog RCA jacks will suffice. Keep in mind you can only get 720p and 1080i resolutions through component jacks – if you want 1080p playback (24-frame or 30-frame), you’ll need to use the HDMI connector. Digital audio is accessible through the Toslink connector, or embedded in the HDMI hook-up.</p>
<div id="attachment_1770" class="wp-caption aligncenter" style="width: 181px"><a href="http://www.hdtvexpert.com/?attachment_id=1770" rel="attachment wp-att-1770"><img class="size-full wp-image-1770" title="Channel-Master-TV-Remote MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-Remote-MR.jpg" alt="" width="171" height="600" /></a><p class="wp-caption-text">Does this remote remind of you anything in particular?</p></div>
<p> </p>
<p>MENUS AND SETTINGS</p>
<p> </p>
<p>The first thing you’ll want to do is configure your channels. Go into the Settings menu and select Channels, and the CM-7400 will prompt you for your location. Scroll to the Local Broadcast option and select it (make sure your TV antenna is connected first!). The box will take a few minutes to scan for all local channels and will also start building program guide information from each station’s PSIP data.</p>
<p> </p>
<p>You’ll notice that the box can receive digital cable channels that are not scrambled (conditional access) and if you enter your zip code, will ask you for your cable provider. The problem is; most cable systems are moving to scramble all channels in the future, even over-the-air retransmissions. It appears the FCC will give in on this request (they already have with RCN), so plan on sticking to free over-the-air channels.</p>
<p> </p>
<p>The next step is to configure your wireless network. (Or, you can simply plug in a wired Ethernet cable, but wireless gives you more options.) The CM-7400 supports 802.11 b/g/n protocols and will connect quickly to your network – if there is a password, you’ll be prompted to enter it on the remarkably easy-to-read menu GUI, which uses mostly white text on a black background.</p>
<p> </p>
<p>Channel Master provides a nice Quick Start Guide to get you through these steps, so you should be up and running pretty quickly. Now, it’s time to watch TV.</p>
<div id="attachment_1771" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1771" rel="attachment wp-att-1771"><img class="size-full wp-image-1771" title="Channel-Master-TV-Menu-Bar MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-Menu-Bar-MR.jpg" alt="" width="600" height="337" /></a><p class="wp-caption-text">Here's the top level menu bar.</p></div>
<div id="attachment_1772" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1772" rel="attachment wp-att-1772"><img class="size-full wp-image-1772" title="Channel-Master-TV-Program-Guide MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-Program-Guide-MR.jpg" alt="" width="600" height="337" /></a><p class="wp-caption-text">And here's the program guide interface.</p></div>
<p> </p>
<p>As I mentioned earlier, the CM-7400 uses each station’s Program and System Information Protocol data to build an electronic program guide. That’s how the DVR knows what programs are coming up in the schedule and when to record them. As you tune through each major and minor channel, you’ll see a program synopsis appear in a black bar at the top of the screen. This bar will list the major and minor channel numbers, the program name, its duration, the rating, and a brief description.</p>
<p> </p>
<p>You can also press the GUIDE button and a complete program schedule for all receivable stations will appear, showing 30-minute increments. Scroll to a program listing and press OK, and the scheduler will appear, asking you if you want to (a) record the episode, (b) record the series (repeated scheduled recordings), (c) find other times that the program is scheduled, or (d) manually record the program.</p>
<p> </p>
<p>The manual feature is handy if your local station isn’t listing program guide information correctly, or it is simply missing, a problem I had with local station WCAU-10 (NBC) a couple of months ago. Scheduling a manual recording without the correct program guide info is not an easy task, as you have to carefully enter a start and stop time and how often you want to record this time block (One Time Only, etc). For all recordings, http://reviews.cnet.com/internet-speed-test/ you can select the record quality, how long to keep it, and if you want the program to start early or end late in one-minute increments.</p>
<p> </p>
<p>IN ACTUAL USE</p>
<p> </p>
<p>The more I used this product, the more similarities I saw to the TiVo interface, which IMHO is the best GUI around for a DVR. About the only things missing from Channel Master TV are “thumbs up and down” controls, an audible “beep” or “boop” each time you execute a keystroke or command, and the program preference and search functions that make TiVo so powerful. Well, you can’t win them all…</p>
<p> </p>
<p>As for the Vudu streaming and apps section, you will see a lot of familiar Internet TV services, including Pandora, Facebook, Picasa, Flickr, and some newbies like NBC Nightly News, New York Times, Associated Press, CNN Daily, and quite a few premium channels like Dexter, Californication, Big Love, and TrueBlood. Just select and click away to start watching.</p>
<div id="attachment_1773" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1773" rel="attachment wp-att-1773"><img class="size-full wp-image-1773" title="Channel-Master-TV-VUDU-apps MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-VUDU-apps-MR.jpg" alt="" width="600" height="337" /></a><p class="wp-caption-text">Here's what the Vudu Apps screen looks like.</p></div>
<div id="attachment_1774" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1774" rel="attachment wp-att-1774"><img class="size-full wp-image-1774" title="Channel-Master-TV-VUDU-Movie-Service MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/02/Channel-Master-TV-VUDU-Movie-Service-MR.jpg" alt="" width="600" height="337" /></a><p class="wp-caption-text">And here's the Vudu Movie selection screen. You can choose from SD, HD&lt; or HDX resolution (see text for warnings).</p></div>
<p> </p>
<p>To test out Vudu, I opened an account and purchased two movies – <em>Bridesmaids</em> (or as I like to call it, <em>The Hangover on Estrogen</em>), and <em>The Help</em>. Yeah, they are both chick flicks, but quite entertaining (in fact, Bridesmaids was flat-out hilariously gross!). Vudu gives you the choice of renting using HDX (1080p/24) quality, HD (720p) quality, and SD (480p) quality. The price difference is small, but you need to check first to see how fast your Internet speeds are.</p>
<p> </p>
<p>Channel Master TV will do that for you automatically through the Vudu interface and recommend a quality level. But be warned – Internet speeds vary widely  and typically slow down in the evening during peak viewing hours. My suggestion is to go to the CNET Internet Speed Checker Web site (<a href="http://reviews.cnet.com/internet-speed-test/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://reviews.cnet.com']);">http://reviews.cnet.com/internet-speed-test/</a>) and see what your typical download speeds are during the day and at night. You may find that SD mode works most consistently.</p>
<p> </p>
<p>My rule of thumb is – up to 2-3 megabits per second (Mb/s) is good for SD video delivery. Figure on 5-6 Mb/s to get 720p HD content reliably, and 8 Mb/s or better for 1080p video. Otherwise, you may find your movie stops abruptly and the Vudu screen will tell you it is “buffering” – something that can take a few minutes if download speeds drop.</p>
<p> </p>
<p><em>Bridesmaids</em> took four tries to start correctly, then played perfectly in HDX resolution until the past 10 minutes when it stopped and started “buffering” again. I dropped down to SD resolution to finish the movie and it didn’t look all that bad on my Panasonic 42-inch 1080p plasma. <em>The Help</em> ran smoothly except for one hiccup near the middle, but this time, I selected SD playback for the entire film. The reason? My average nighttime Internet speeds were dropping into the 2 – 4 Mb/s range.</p>
<p> </p>
<p>As for over-the-air channels, the CM-7400 has a very sensitive receiver and evidently uses sophisticated adaptive equalization. What that means in English is reliable reception of weak stations or stations off to the side of the antenna, as well as good reception during periods of signal fading, such as during a thunderstorm. I was able to lock in and watch 38 different minor channels in the Philadelphia market, which is basically a small hotel cable TV system. And they’re all free.</p>
<p> </p>
<p>Sports fans should also keep in mind that there is a growing cry to move all cable sports channels to premium tiers as cable bills continue to climb. You won’t need to pay to watch NFL games (available on CBS, NBC, and FOX through 2022), the NCAA men’s basketball tournament, selected major league baseball games and the World Series, SEC and Big Ten football, and the Olympics – not to mention the Masters golf tournament, selected tennis matches, and the Indianapolis 500. All free with an antenna!</p>
<p> </p>
<p>I should mention that the test unit seemed to run a bit warm to me, even when it was switched off. One product review on the Channel Mater Web site recommended using a laptop cooler (external heat sink) to help with heat dissipation. Also, Channel Master released an updated version of the OS on January 18, which you should install and upgrade.</p>
<p> </p>
<p>CONCLUSION</p>
<p> </p>
<p>Channel Master’s CM-7400 TV DVR is a clever product that nicely combines dual DVRs with Vudu streaming. It has a nicely-designed and executed user interface, sets up quickly, and supports 1080p playback through its HDMI connector. You can also loop your antenna connection through the CM-7400 and continue to watch on your regular TV, giving you the ability to watch three programs at once while recording two of them. Clever, eh?</p>
<p> </p>
<p>SPECIFICATIONS</p>
<p> </p>
<p>Channel Master CM-7400 TV DVR</p>
<p>SRP: $400</p>
<p>Available at: <strong><a href="http://tinyurl.com/7m6qbgk" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://tinyurl.com']);">http://tinyurl.com/7m6qbgk</a></strong></p>
<p>And other online outlets including Amazon.com</p>
<p> </p>
<p><strong>Video</strong></p>
<ul><li>480i/480p</li>
<li>720p</li>
<li>1080p/1080i</li>
</ul><p><strong>Audio</strong></p>
<ul><li>Dolby® Digital and Dolby® Digital Plus</li>
</ul><p><strong>Tuners</strong></p>
<ul><li>Dual ATSC/Clear QAM¹</li>
<li>No monthly subscription fee</li>
<li>Includes a one year manufacturer’s limited warranty</li>
</ul><p><strong>Recording Capacity</strong></p>
<ul><li>320GB Hard Disk Drive²</li>
<li>Up to 35 hours of HD recording³</li>
<li>Up to 150 hours of SD recording³</li>
</ul><p><strong>Wireless</strong></p>
<ul><li>Built-in 802.11b/g/n</li>
</ul><p><strong>Dimensions</strong></p>
<ul><li>10(w) x 7(d) x 1.75(h) inches</li>
</ul><p><strong>Rear Panel Features </strong></p>
<ul><li>RJ-45 Ethernet</li>
<li>USB 2.0</li>
<li>HDMI®</li>
<li>eSATA</li>
<li>Digital Audio (Optical)</li>
<li>RF output</li>
<li>RF antenna/cable input</li>
<li>RCA component and composite video</li>
<li>Stereo audio</li>
</ul><p><strong>Front Panel Features </strong></p>
<ul><li>Illuminated power standby button</li>
<li>Indicators for network status, HD and recording status</li>
<li>USB 2.0</li>
<li>IR receiver</li>
<li>Capacitive touchpad</li>
<li>Clock display</li>
</ul><p><strong>Contents Included</strong></p>
<ul><li>Channel Master TV Unit</li>
<li>User Guide</li>
<li>Quick Start Guide</li>
<li>IR Universal Remote Control</li>
<li>AA Batteries</li>
<li>Composite and Stero Audio Cable</li>
<li>RF Coaxial Cable</li>
<li>HDMI Cable</li>
<li>AC Adapter</li>
</ul></div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>February  9, 2012 11:45 AM</b>
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
			<?=getComments(4681)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4681)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/02/hdtv-expert-useful-gadgets-channel-master-cm7400-tv.php" type="text/javascript" charset="utf-8"></script>
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