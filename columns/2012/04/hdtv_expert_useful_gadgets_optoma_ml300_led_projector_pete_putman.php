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
		AND e.entry_id = 4763";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4763 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4763 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4763";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-useful-gadgets-optoma-ml300-led-projector-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4763";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &ndash; Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &ndash; Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &ndash; Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &ndash; Pete Putman</title>
	<meta name="keywords" content="color gamut, mini hdmi, hdmi input, ansi lumens, image quality, projector, color, mode, input, gamut, hdmi, could, ’s, –, small, led, full, standard, ’ll, image, brightness, menu, lumens, ”, video" />
	<meta name="description" content="It has Wide XGA resolution, weighs all of 1.4 pounds, and cranks out 200+ lumens. What else is there to say? (Oh yeah, it also has a Mini HDMI connection.)" />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &amp;ndash; Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &amp;ndash; Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-useful-gadgets-optoma-ml300-led-projector-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It has Wide XGA resolution, weighs all of 1.4 pounds, and cranks out 200+ lumens. What else is there to say? (Oh yeah, it also has a Mini HDMI connection.)" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4763', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-useful-gadgets-optoma-ml300-led-projector-pete-putman.php">HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector &ndash; Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April  3, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=497&category=Front Projection">Front Projection</a></b>, <b><a href="/category.php?id=503&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
          <p>Optoma’s ML300 is but one of many LED-powered projectors that have come to market in the past few years. I haven’t paid a lot of attention to this category, because I think that any projectors rated at 100 lumens or lower will be killed off by the increasing use of tablets for small group presentations.</p>
<p> </p>
<p>Projectors in the 100 – 500 lumens category are perhaps a bit more secure, provided they are compact enough and idiot-proof. This category, which Pacific Media Associates has labeled ‘new era’ projectors, is distinguished by small, lightweight form factors and solid-state (LED) light engines.</p>
<div id="attachment_2032" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2032" rel="attachment wp-att-2032"><img class="size-full wp-image-2032" title="ML300 3-4 View Master MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/ML300-3-4-View-Master-MR.jpg" alt="" width="600" height="399" /></a><p class="wp-caption-text">The ML300 is a 'looker' for certain.</p></div>
<p> </p>
<p>Optoma’s ML300 falls right into the middle of that group. The factory brightness rating is 300 lumens in ‘bright’ mode, and the projector barely tips the scales at 1.4 pounds.</p>
<p> </p>
<p>Think about that for a moment: back in 1994, Hitachi introduced a 500-lumen LCD projector that weighed 30 pounds, or twenty times as much as the ML300. My old 160-pound Sony CRT projector, also vintage mid-1990s and which used 7” tubes, could barely hit 200 lumens when calibrated.</p>
<p> </p>
<p>Along comes this little bugger, which has about the same resolution (1280×800 pixels native on a single DLP chip), doesn’t require any convergence, and supports both analog and digital input signals. Given how closely I follow the world of display technology, not much really impresses me these days – but the ML300 does. (Along with my Nikon CoolPix 8200 16MP camera, but that’s another story!)</p>
<div id="attachment_2033" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2033" rel="attachment wp-att-2033"><img class="size-full wp-image-2033" title="Projector in Hand - Best View CROP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Projector-in-Hand-Best-View-CROP-MR.jpg" alt="" width="600" height="368" /></a><p class="wp-caption-text">Small enough for you?</p></div>
<p> </p>
<p>OUT OF THE BOX</p>
<p> </p>
<p>There isn’t much to the ML300. It has a fixed-focal length lens (1.5:1 projection ratio), which (coincidentally) was the projection ratio of my old Sony projector. That means you can light up an 80”’ diagonal screen with a projection throw of 101 inches, or about eight and a half feet. A small elevating leg is all you’ll have to tilt the projector, which has a positive image offset and auto keystone correction.</p>
<p> </p>
<p>In addition to being lightweight, the projector is also quite small, measuring 7.2” wide by 4.4” deep and 18” tall. The light engine is a 3-LED design that presents red, green, and blue sequentially to the WXGA imaging chip. It uses a bit of power – 90 watts peak, when the LEDs are run at full brightness – but those same LEDs should be good for 20,000 hours of operation before half-brightness.</p>
<p> </p>
<p>Input connections are limited, but should encompass what you’ll need. On the side panel, you’ll find a full-size VGA connector (really an anachronism in 2012), a mini HDMI jack, an AV connector for composite video and stereo audio input (yes, there is a built-in 2 watt speaker), and a micro USB port for DisplayLink operation (display over USB).</p>
<p> </p>
<p>On the rear panel, next to the power on/off button, Optoma has provided a microSD card slot for direct playback of files from memory cards, an analog audio output connector that has a headphone icon next to it, and a full-sized USB connector for flash cards. Use this port to view JPEGs or play back a Powerpoint show file. Not the usual connector complement you are used to, but hey – it’s 2012! (Get with the program!)</p>
<div id="attachment_2034" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2034" rel="attachment wp-att-2034"><img class="size-full wp-image-2034" title="CU of Input Board MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/CU-of-Input-Board-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Here are the side input connectors.</p></div>
<div id="attachment_2035" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2035" rel="attachment wp-att-2035"><img class="size-full wp-image-2035" title="Rear View USB and SD Card Ports CROP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Rear-View-USB-and-SD-Card-Ports-CROP-MR.jpg" alt="" width="600" height="304" /></a><p class="wp-caption-text">And here's what the back side looks like.</p></div>
<p> </p>
<p>MENUS AND OPERATION</p>
<p> </p>
<p>A projector this small can’t have room for a power supply, so you’ll need to hook up the laptop-style external ‘brick’ supply. Push that rear-panel power button to get things started, and look for the credit card-sized remote control to change inputs and cycle through menu settings.</p>
<p> </p>
<p>The HOME menu brings up five choices – Video, Audio, Photo, Input, Office Viewer, and Settings. In Video mode, you’ll see a list of any available video clips from memory cards. (Did I mention that the projector has 1.4 GB of internal storage available?)  The projector will cycle through an external USB flash drive, external microSD card, and then internal memory to search for and play back files.</p>
<p> </p>
<p>Video files will be listed with a small thumbnail, while audio files are identified by filename only. JPEG photos show up with thumbnails for easier selection. You can also sequence your photos automatically in a slide show.</p>
<p> </p>
<p>The INPUT menu lets you directly access any of the connections and slots. A small picture of each appears to ensure you don’t get mixed up. Wanna go wireless? Optoma offers a WiFi dongle for the ML300 as an accessory and you can access it too from the INPUT menu. As far as playback formats, the ML300 natively supports Powerpoint, Word, Excel, Acrobat (PDF) and bitmap (.BMP) in addition to JPEG.</p>
<p> </p>
<p>The SETTING menu breaks down into Video Setting, Audio Setting, Display Setting, Slideshow, and System sub menus. In Display Setting mode, you can adjust LED brightness four ways (Bright, Cinema, Photo, and PC), select between Extended (full) color gamut and Standard (close to NTSC/BT.709), cycle between four different aspect ratios (4:3, 16:9, 16:10, Auto), choose one of four gamma presets (Presentation, Movie, Bright, and Standard), and select the projected image orientation (normal/inverted/ceiling/table).</p>
<p> </p>
<p>I should add that in my tests, I could not get my color gamut choices to stick once they were selected. And you won’t find any ‘save’ button or prompt after you make your image adjustments. Every time I selected Standard color gamut (and you’ll see why momentarily) and cycled back to the HDMI input, the projector defaulted to Extended color mode. I could only force the Standard mode by using the analog VGA input.</p>
<p> </p>
<p>In fact, the entire menu is a bit slow to use and the IR remote isn’t very responsive. You’ll have better results using the manual buttons to make your selections, but you may get confused (as I did) entering and backing out of sub-menus. I’d like to see Optoma put some more thought into making the menu more logical to navigate, along with improving the response of the IR remote.</p>
<p> </p>
<p>Believe it or now, the ML300 also supports 3D playback using DLP Link, but the input signal must be in the 1024×768 (XGA) format @ 120 Hz refresh rate only – no 720p 0r 1080p 3D formats will be recognized.</p>
<p> </p>
<p>ON THE TEST BENCH</p>
<p> </p>
<p>Right now, you’re probably thinking, “How good can the image quality possibly be from that little pipsqueak?”  The answer: Better than you and I could have imagined. In fact, the ML300 produces images that are every bit as good as my old tuned-up Sony CRT, and I got these images with about 1/100<sup>th</sup> of the effort.</p>
<p> </p>
<p>Is the color perfect? No, but it’s very close. How about gamma? Impressive for a projector in this price and size class. Black levels? Eh, they could be lower. Contrast? Not bad; could be a little higher.</p>
<div id="attachment_2037" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2037" rel="attachment wp-att-2037"><img class="size-full wp-image-2037" title="Optoma ML300 Luminance Histogram HDMI INPUT MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Optoma-ML300-Luminance-Histogram-HDMI-INPUT-MR.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Here's the gamma curve for the HDMI input in Movie mode - about 2.2.</p></div>
<p> </p>
<p>Let’s start with brightness. After what limited calibration I could do (almost none), I measured brightness at 152 ANSI lumens in Movie mode. That number increased to 173 ANSI lumens in Photo mode, then jumped again to 198 ANSI lumens in PC mode. Flat-out brightness was measured at 232 ANSI lumens in Bright mode, a number my 7” CRT would be hard-pressed to equal.</p>
<p> </p>
<p>Brightness uniformity was excellent at 85% to the average corner and 70% to the worst corner. I’ve tested conventional DLP projectors that can’t match those numbers, nor can they match the maximum color temperature shift across the ML300’s full white screen (314K).</p>
<p> </p>
<p>Contrast measurements were decent, clocking in at 244:1 ANSI in Movie mode with peak intra-scene contrast at 342:1. A 50/50 contrast window yielded a 313:1 reading, while sequential (full black to full white) contrast was logged in the books at 373:1. Again, all numbers that my old CRT projector would be hard-pressed to match.</p>
<p> </p>
<p>As far as gamma performance goes, the ML300 comes out of black a little too steeply in each preset image mode and starts to flatline between 60 – 70 IRE. But it doesn’t go into an S-curve response, nor does it clip at the high end. In Movie mode, I measured a 2.24 gamma, while the HDMI input showing video came in at 2.13.</p>
<div id="attachment_2038" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2038" rel="attachment wp-att-2038"><img class="size-full wp-image-2038" title="Optoma ML300 TK2 Temperature Histogram 720p RGB MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Optoma-ML300-TK2-Temperature-Histogram-720p-RGB-MR.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Here's the color temperature track with a 720p HD signal. A little off at the beginning, but very consistent above 30 IRE.</p></div>
<div id="attachment_2039" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2039" rel="attachment wp-att-2039"><img class="size-full wp-image-2039" title="Optoma ML300 TK2 RGB Levels Histogram 720p RGB MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Optoma-ML300-TK2-RGB-Levels-Histogram-720p-RGB-MR.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">And this RGB histogram shows why the color temperature track is so consistent.</p></div>
<p> </p>
<p>Now for those color gamut plots: You’ll notice right away that the Extended color gamut is ENORMOUS, and big enough to take in all of the digital cinema P3 gamut, sRGB, the original NTSC, and even some laser-powered projector color spaces. Only the green coordinate is out of whack, although the projector’s response is similar to the CIE 1931 observer diagram. For P3, less cyan and more yellow is required.</p>
<p> </p>
<p>The Standard gamut is a lot more subdued, and comes very close to the reference BT.709 HDTV color space. There’s just a little too much red and a little too much green, otherwise the colors would be right on the money. But again, it was impossible for me to force the projector into this truncated gamut when watching a Blu-ray movie through the mini HDMI connection – it kept defaulting back to the Extended setting, which made for some very interesting and over-saturated colors.</p>
<p> </p>
<p>IMAGE QUALITY</p>
<div id="attachment_2040" class="wp-caption aligncenter" style="width: 549px"><a href="http://www.hdtvexpert.com/?attachment_id=2040" rel="attachment wp-att-2040"><img class="size-full wp-image-2040" title="Optoma ML300 CIE  WIDE GAMUT" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Optoma-ML300-CIE-WIDE-GAMUT.jpg" alt="" width="539" height="606" /></a><p class="wp-caption-text">Here's the flat-out, full-bore Extended color gamut of the ML300, compared to the BT.709 HDTV color space (white outline).</p></div>
<div id="attachment_2041" class="wp-caption aligncenter" style="width: 549px"><a href="http://www.hdtvexpert.com/?attachment_id=2041" rel="attachment wp-att-2041"><img class="size-full wp-image-2041" title="Optoma ML300 TK2 CIE Chart 720p RGB" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Optoma-ML300-TK2-CIE-Chart-720p-RGB.jpg" alt="" width="539" height="606" /></a><p class="wp-caption-text">And here is the Standard gamut, again compared to the BT.709 color space. This is looking a lot more reasonable!</p></div>
<p> </p>
<p>LED color appears differently to the eye than dichroic color derived by refracting white light. My test instruments say the two modes are the same, but they still look different to my eyes, just as LCD and DLP color appear differently from CRT color.</p>
<p> </p>
<p>Even so, if you can tame the Extended gamut, watching a Blu-ray disc in Standard mode is just as impressive as a standard widescreen business/classroom projector and not all that far behind some of the low-priced home theater projector models with full calibration features. I picked <em>How To Train Your Dragon</em> in 2D as a test disc, along with <em>Planet Earth</em> on Blu-ray for my test videos.</p>
<p> </p>
<p>Both had excellent sharpness and detail. They could have been brighter, but I was filling a 92-inch diagonal Da-Lite Affinity screen, which doesn’t exactly make for the brightest images. LEDs cycle as fast as you could want, so there were no motion blur or color wheel artifacts to distract me, even when viewing part of an NCAA basketball tournament game and a prime-time sitcom from NBC.</p>
<p> </p>
<p>The ML300 does a passable job of processing 1080i sources to 1080p. It’s much happier with 1080p or 720p content, though. The frequency response is good all the way to 37.5 MHz with 720p multiburst test patterns, and there is some filling with 1080i and 1080p bursts. For computer presentations, try and match the native resolution for optimum sharpness and detail.</p>
<p> </p>
<p>Granted, all of these tests are pushing this projector far beyond what it was intended to do, which is to sit on a conference room table and show Powerpoints. But Optoma clearly put some time and effort into the image quality, and you could be quite happy with the ML300 for those <em>“let’s hang a sheet on the wall!”</em> movie nights. God knows it’s easy enough to set up and knock down!</p>
<p> </p>
<p>THE WRAP-UP</p>
<p> </p>
<p>Optoma’s ML300 mobile LED projector is not a toy, nor is it just a garden-variety business projector. There’s a lot more going on here than meets the eye, and you can actually use it for viewing movies as easily as holding court in a small-group presentation.</p>
<p> </p>
<p>The IR remote needs to be more responsive and the menu navigation is slow and sometimes confusing. A zoom lens would be nice, but you’ll get used to the 1.5:1 ratio quickly enough. The projector is pretty quiet (36 dB fan noise) and needs a low to mid-range gain screen, say 1.3 to 1.5.</p>
<p> </p>
<p>But the ML300’s image quality will surprise you, especially if you remember how crazy the earliest LED projectors looked like a few years ago.</p>
<p> </p>
<p><strong><em>One note:</em></strong> The mini HDMI input connector is a bit unusual and you may not be able to find it easily at your local Radio Shack. I suggest looking on Amazon.com, where I found a pair of ten-foot regular HDMI to mini HDMI cables and two standard/mini HDMI adapters, all for $11.50 and free shipping. Your new point-and-shoot camera probably has mini HDMI connections, too, so these cables are very handy to have.</p>
<p> </p>
<p><strong>Optoma ML300 Mobile LED Projector</strong></p>
<p><strong>MSRP: $499.99</strong></p>
<p> </p>
<p><strong>Available from:</strong></p>
<p> </p>
<p>Optoma</p>
<p>3178 Laurelview Court</p>
<p>Fremont, CA 94538</p>
<p>888-289-6786</p>
<p><a href="http://www.optomausa.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.optomausa.com']);">www.optomausa.com</a></p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April  3, 2012  1:24 PM</b>
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
			<?=getComments(4763)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4763)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-useful-gadgets-optoma-ml300-led-projector-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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