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
		AND e.entry_id = 4623";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4623 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4623 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4623";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/panasonic-expands-and-enhances-2012-ledlcd-model-lineup.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4623";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Expands and Enhances 2012 LED/LCD Model Line-up" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Expands and Enhances 2012 LED/LCD Model Line-up" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Expands and Enhances 2012 LED/LCD Model Line-up" />
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
	<title>HDTV Magazine - Panasonic Expands and Enhances 2012 LED/LCD Model Line-up</title>
	<meta name="keywords" content="measured diagonally, inches measured, class inches, inch class, led lcd, panasonic, led, inches, series, inch, measured, diagonally, class, lcd, viera, panel, consumer, ips, hdmi, usb, resolution, high, terminals, new, hdtvs" />
	<meta name="description" content="Panasonic Corporation of North America (NYSE:PC), the industry and technology leader in High Definition Televisions, introduced an increased 2012 Smart VIERA line-up of LED LCD HDTVs defining the core of a new IPTV lifestyle at the Consumer Electronics Show, confirming Panasonic's commitment to provide the highest quality home entertainment experience and viewing options to the consumer. The advent of Smart VIERA HDTV centers on five main points: Networking, Easy Operation, Picture Quality, Eco and Design elements. The 2012 series focuses on larger screen sizes and LED based HDTVs, with  14 of the 16 models incorporating LED technology. Adhering to its long standing focus on providing products that consumers ask for, Panasonic added 47 inch and 55 inch screen sizes to its LED/LCD family and its 3D line up.

The 2012 line-up continues to benefit from the addition of..." />
	<meta name="title" content="Panasonic Expands and Enhances 2012 LED/LCD Model Line-up" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Expands and Enhances 2012 LED/LCD Model Line-up" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/panasonic-expands-and-enhances-2012-ledlcd-model-lineup.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic Corporation of North America (NYSE:PC), the industry and technology leader in High Definition Televisions, introduced an increased 2012 Smart VIERA line-up of LED LCD HDTVs defining the core of a new IPTV lifestyle at the Consumer Electronics Show, confirming Panasonic's commitment to provide the highest quality home entertainment experience and viewing options to the consumer. The advent of Smart VIERA HDTV centers on five main points: Networking, Easy Operation, Picture Quality, Eco and Design elements. The 2012 series focuses on larger screen sizes and LED based HDTVs, with  14 of the 16 models incorporating LED technology. Adhering to its long standing focus on providing products that consumers ask for, Panasonic added 47 inch and 55 inch screen sizes to its LED/LCD family and its 3D line up.

The 2012 line-up continues to benefit from the addition of..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4623', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/panasonic-expands-and-enhances-2012-ledlcd-model-lineup.php">Panasonic Expands and Enhances 2012 LED/LCD Model Line-up</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">Panasonic Expands and Enhances 2012 LED/LCD Model Line-up</p>

<center><i>New Larger Screen Sizes, Focus on LED, Augmented VIERA Connect&trade; and Continual Technology Improvements Cement Panasonic's Total Commitment to Smart VIERA LED LCD HDTVs</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 9, 2012 /PRNewswire/ --</strong> Panasonic Corporation of North America (NYSE:PC), the industry and technology leader in High Definition Televisions, introduced an increased 2012 Smart VIERA line-up of LED LCD HDTVs defining the core of a new IPTV lifestyle at the Consumer Electronics Show, confirming Panasonic's commitment to provide the highest quality home entertainment experience and viewing options to the consumer. The advent of Smart VIERA HDTV centers on five main points: Networking, Easy Operation, Picture Quality, Eco and Design elements. The 2012 series focuses on larger screen sizes and LED based HDTVs, with  14 of the 16 models incorporating LED technology. Adhering to its long standing focus on providing products that consumers ask for, Panasonic added 47 inch and 55 inch screen sizes to its LED/LCD family and its 3D line up.</p>

<p>The 2012 line-up continues to benefit from the addition of the IPS LED LCD Panel (In Plane Switching) technology. The IPS LED LCD Panel delivers a wide viewing angle with almost no picture degradation at off angle viewing, a super high speed 1920 Backlight Scanning for higher moving picture resolution during fast action scenes, reduction of afterglow and a smooth, crisp image.  The high performance, high speed, high transmittance panel materials contribute to the wide viewing angle, high contrast performance and fast response time, which in turn dramatically reduces artifacts and 3D crosstalk.</p>

<p>Panasonic is committed to producing HDTVs that are mindful of the environment. The new and improved high efficient LED LCD Panels reduce targeted power consumption up to approximately 25% over last year's models.</p>

<p>VIERA Connect(1),  Panasonic's proprietary internet functionality is integrated into 12 of the new models with a new robust platform that provides easy access to such sites as  Amazon Instant Video&trade;, Netflix&trade;, Pandora , Facebook, CinemaNow, VUDU, Hulu Plus&trade;, Skype&trade;, Ustream, sports sites – MLB, NHL, NBA, MLS(2), Fox Sports, gaming and health and wellness sites.</p>

<p>"Panasonic's priority continues to be an absolute pledge to provide the consumer with the highest quality entertainment options," said Henry Hauser, Vice President, Panasonic Marketing, the Merchandising Group. "Panasonic has always followed the philosophy of listening to the consumer and producing products that meet the consumer's needs and desires, as well as providing entertainment choices. We recognize that a consumer's decision to purchase a TV entails a myriad of factors, such as viewing environment, type of programming and price. This is precisely why Panasonic is technologically agnostic and why our 2012 line up features increased screen sizes, as well as three TVs that use polarized 3D technology."</p>

<p><br />
<strong>WT50 Series</strong></p>

<p>The state of the Art design, WT50 LED series marks the debut of the two new, larger screen sizes, the TC-L47WT50, 47 inch class (47 inches measured diagonally) and the TC-L55WT50, 55 inch class (54.5 inches measured diagonally). The WT50 HDTVs feature the  FULL HD 3D, IPS LED LCD Panel; Super High Speed 1920 Backlight Scanning for higher moving picture resolution during fast action scenes, and a smooth, crisp image; 1080p resolution; VIERA Connect with built-in WiFi and Web Browser; a new Clear Panel Pro and Super-Narrow Metal Frame with Crescent Stand; 2D --> 3D conversion; Social Networking TV function to allow users to access social network sites while simultaneously watching TV; Multitasking feature to switch between apps; 3D Real Sound with 8-Train Speakers, providing immersive sound experience; Media Player, allows one to view digital images and HD video recorded on a SD Memory Card; DLNA; VIERA Touch Pad Controller; four HDMI terminals and three USB ports. The series is Energy Star* certified.</p>

<p><br />
<strong>DT50 Series</strong></p>

<p>Continuing Panasonic's move to larger LED screen sizes, the DT50 series, which provides Smart TV experience, includes two models, the TC-L47DT50, 47 inch class (47 inches measured diagonally)and the TC-L55DT50, 55 inch class (54.5 inches measured diagonally). Featuring the FULL HD 3D, IPS LED LCD Panel: Super High Speed 1920 Backlight Scanning for higher moving picture resolution during fast action scenes and a smooth, crisp image, the DT50 series includes VIERA Connect with built-in WiFi and Web Browser; a new Narrow Metal Frame; 1080p resolution; 2D --> 3D conversion; Social Networking TV function; 3D Real Sound with 8-Train Speakers; Media Player; DLNA; four HDMI terminals; three USB ports. In addition, the two DT50 LED HDTVs are Energy Star* certified.</p>

<p><br />
<strong>ET5 Series</strong></p>

<p>The three models that comprise the ET5 series feature FULL HD polarized 3D, IPS LED LCD Panel.  The TC-L42ET5, 42 inch class (42 inches measured diagonally), TC-L47ET5, 47 inch class (47 inches measured diagonally); and TC-L55ET5, 55 inch class (54.6 inches measured diagonally) incorporate the IPS LED panel, 360 Backlight Scanning for higher moving picture resolution during fast action scenes, and a smooth, crisp image; VIERA Connect with built-in WiFi and Web Browser; 2D --> 3D conversion; Social Networking TV function; Media Player; DLNA; four HDMI and two USB terminals; a PC input. Furthermore, each of the ET5s comes with 4 pairs of polarized 3D glasses. The ET5 series is also Energy Star* certified.</p>

<p><br />
<strong>E50 Series</strong></p>

<p>The E50 series features the IPS LED LCD Panel, a slim design and brilliant picture and is available in three screen sizes, TC-L42E50, 42 inch class (42 inches measured diagonally); TC-L47E50, 47 inch class (47 inches measured diagonally); TC-L55E50, 55 inch class (54.6 inches measured diagonally). Featuring 360 Backlight Scanning for higher moving picture resolution during fast action scenes, and a crisp image the E50 models also feature1080p resolution; VIERA Connect; Social Networking TV function; DLNA; a PC input; four HDMI terminals; two USB ports. The series is ready and Energy Star* certified.</p>

<p><br />
<strong>E5 Series</strong></p>

<p>The four LED HDTVs in the E5 series include the IPS LED LCD Panel, feature 1080p resolution; Online Movies  a service that provides select Panasonic's IPTV functionality by adding five of the most popular movies to the TV's internet functionality; DLNA; four HDMI terminals; two USB ports and a PC input. The TC-L32E5, 32 inch class (31.5 inches measured diagonally), TC-L37E5, 37 inch class (36.5 inches measured diagonally), TC-L42E5 inches class (42 inches measured diagonally) and TC-L47E5 inches class (47 inches measured diagonally) are ready and both are Energy Star* certified.</p>

<p><br />
<strong>X5 Series</strong></p>

<p>The TC-L32X5, 32 inch class (31.5 inches measured diagonally) is a720p IPS LED LCD Panel. The TV features Media Player; three HDMI terminals; one USB port and a PC input. The X5 series also offers the TC-L24X5, 1080p LED LCD Panel, 24 inch class (23.6 inches measured diagonally) featuring one HDMI terminal; one USB, a PC input and Game Mode. The L32X5 is Eco efficient and is Energy Star* certified.</p>

<p><br />
<strong>U5 Series</strong></p>

<p>The TC-L42U5, 42 inch class (42 inches measured diagonally) is one of two LCD HDTVs (cold cathode backlighting) in the 2012 collection. The TV is a 1080p model with three HDMI terminals; one USB port, a PC input and Game Mode..</p>

<p><br />
<strong>C5 Series</strong></p>

<p>The TC-L32C5, 32 inch class (31.5 inches measured diagonally) is a 720p, LCD HDTV (cold cathode backlighting) with two HDMI terminals; one USB port, a PC Input and Game Mode.</p>

<p>* Based on EPA's Energy Star Program</p>

<p><br />
<strong>About Panasonic Consumer Marketing Company of North America</strong></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Marketing Company of North America, a Division of Panasonic Corporation of North America, the principal North American Subsidiary of Panasonic Corporation (NYSE: PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations, offers a wide-range of consumer solutions in the U.S. and Canada.  The Company's portfolio of innovative consumer products ranges from VIERA Full HD 3D Televisions, Blu-ray players, LUMIX Digital Cameras, Camcorders, Home Audio, Cordless Phones, Home Appliances, Wellness and Personal Care products and more.</p>

<p>Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. Panasonic was the only Consumer Electronics company to be listed in the top ten brands on the Interbrand Best Global Green Brands 2011 ranking. Follow Panasonic on Twitter @panasonicdirect, and additional company information for media is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>(1) Access to a broadband internet connection is required to access VIERA Connect features.</p>

<p>(2) Some services such as Netflix, Amazon Instant Video and league sports sites have a separate fee structure to view movies and sports events.</p>

<p>SOURCE Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2012 10:31 PM</b>
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
			<?=getComments(4623)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4623)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/panasonic-expands-and-enhances-2012-ledlcd-model-lineup.php" type="text/javascript" charset="utf-8"></script>
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