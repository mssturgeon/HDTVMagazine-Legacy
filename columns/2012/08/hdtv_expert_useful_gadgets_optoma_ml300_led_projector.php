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
		AND e.entry_id = 4886";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4886 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4886 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4886";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/08/hdtv-expert-useful-gadgets-optoma-ml300-led-projector.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4886";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector</title>
	<meta name="keywords" content="color temperature, color gamut, video mode, ansi lumens, truncated color, projector, –, color, ’s, video, hdmi, mode, figure, gamut, input, optoma, power, back, gamma, ’t, menu, temperature, led, light, bit" />
	<meta name="description" content="The future of front projection depends on its ability to go lampless. And Optoma&amp;acirc;��s ML300 shows how far we&amp;acirc;��ve come in attaining that goal." />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/08/hdtv-expert-useful-gadgets-optoma-ml300-led-projector.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The future of front projection depends on its ability to go lampless. And Optoma&amp;acirc;��s ML300 shows how far we&amp;acirc;��ve come in attaining that goal." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4886', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/08/hdtv-expert-useful-gadgets-optoma-ml300-led-projector.php">HDTV Expert - Useful Gadgets: Optoma ML300 LED Projector</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>August  8, 2012</b>
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
          <p>Back in June, during my annual Display Technology Trends on Super Tuesday at InfoComm in Las Vegas, I singled out two products that showed just how far technology has advanced in the past decade. The first was Nikon’s CoolPix 8200, a $250 point-and-shoot camera with 16 megapixels of resolution, 16x optical zoom, multi-zone focus, HDMI output, ISO speeds to 3200, and an amazingly compact form factor.</p>
<p>The other was Optoma’s ML300 LED projector, which I compared in performance to my late, lamented Sony VPH-D70 CRT projector. The latter – which was the centerpiece of my home theater until 2006 – could crank out about 170 – 200 lumens, had three 7” CRTs, weighed about 140 pounds, had a maximum resolution of 1280×720, and zero support for digital connections. (Oh, and it cost $12,000 new.)</p>
<div id="attachment_2333" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2333" title="ML300 3-4 View Master MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/ML300-3-4-View-Master-MR.jpg" alt="" width="600" height="399" /><p class="wp-caption-text">Hard to believe this pipsqueak replaced a 140-pound CRT projector!</p></div>
<p>The ML300 was a perfect benchmark against the VPH-D70. It has a native resolution of 1280×800 pixels, using a single DLP imaging device. Like the Sony CRT projector, it is ‘lampless,’ relying on discrete red, green, and blue light-emitting diode chips to provide illumination.</p>
<p>But it weighs considerably less – 1.4 pounds, about the same as the Remote Commander keyboard remote that came with the VPH-D70. And it offers ‘instant on’ operation, with an estimated LED life of 20,000 hours to half-brightness. There’s no convergence required; no keystone correction (it’s automatic) to fool with, and the ML300 supports all the standard HD and SD video formats, plus a host of computer resolutions.</p>
<p>Significantly, it will set you back all of $499. I’m not sure I could have replaced the Remote Commander for that price!</p>
<div id="attachment_2334" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2334" title="Projector in Hand - Best View CROP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Projector-in-Hand-Best-View-CROP-MR.jpg" alt="" width="600" height="368" /><p class="wp-caption-text">Remember when cell phones were bigger than this?</p></div>
<p> </p>
<p>With more projector manufacturers jumping on the ‘lamp free’ bandwagon at InfoComm, it’s a good time to take a closer look at one of these marvels. Right now, projectors are under assault by large, inexpensive LCD monitors and TVs, and one reason is the need to replace lamps – they’re not cheap, and stockpiled lamps can turn out to be defective months after their warranty runs out when you actually need them.</p>
<p>There are no such worries with LED (and laser) light engines. Yes, they eventually will croak – all electronics do. But the probability of them not lighting up after sitting idle for several months is very low. And, they’re more friendly to the environment (projector lamps contain salts of mercury, and that’s something we don’t need more of in our water and air!).</p>
<p>OUT OF THE BOX</p>
<p>Did I mention that the LM300 was tiny? You can hold it in the palm of your hand. (Actually, you can hold it for quite a while in the palm of your hand – it’s that light!) The housing measures all of 7.2” long by 4.4” deep and sits 1.8” tall. That would slip very nicely into my computer bag.</p>
<p>The lens is mounted off-center and is a varifocal type with a zoom ratio of 1.5:1. That means you need to place it about 15 feet away from a 10’ wide screen to fill the width. Projected images have a 100% plus offset, meaning they will sit above the top of the lens. The projector also has automatic digital keystone correction that you can override.</p>
<p> </p>
<div id="attachment_2335" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2335" title="CU of Input Board MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/CU-of-Input-Board-MR.jpg" alt="" width="600" height="450" /><p class="wp-caption-text">Here are the main inputs – VGA, HDMI, and composite video.</p></div>
<p> </p>
<p>As far as connectivity goes, the ML300 comes with a 15-pin VGA input jack (just can’t kill off analog, can we?) that is compatible with resolutions from VGA to WXGA, a mini HDMI input for standard video resolutions to a maximum of 1920x1080p/60, and a micro USB connector for playing back JPEG images from a flash drive. There’s also a full-size USB port on the real panel.</p>
<p>You have to look real hard to find it, but yes, there <span style="text-decoration: underline;">is</span> a composite video connection (can’t kill that off, either) through a micro 2.5mm breakout plug that also provides analog audio to RCA jacks. A mini (3.5mm) stereo audio jack is included to loop out audio from a PC or from the connected HDMI source.</p>
<p>One thing you will realize in short order is that normal VGA and HDMI cables will pull this projector all over the table. In fact, a VGA connection looks kind of ridiculous into the ML300 – the plug is enormous, compared to the I/O side panel. The Mini HDMI connection is more reasonable, but you may have some trouble finding this cable. (I bought a few through Amazon.com for the sum of $11.)</p>
<p>The supplied remote control is so small that you need to keep it in a secure place – it would be easy to lose. These remotes are commonly referred to as ‘credit card’ remotes, but in reality, they are about 2/3 the width.</p>
<p> </p>
<div id="attachment_2336" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2336" title="Rear View USB and SD Card Ports CROP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Rear-View-USB-and-SD-Card-Ports-CROP-MR.jpg" alt="" width="600" height="304" /><p class="wp-caption-text">And here’s what the rear panel looks like. The power switch is in the upper left corner.</p></div>
<p><strong><em><br /></em></strong></p>
<p>REMOTE AND MENUS</p>
<p>Speaking of remotes…there aren’t a lot of buttons to play with on the ML300. Aside from the power button, you’ll find navigation buttons, direct links to the video, HDMI, and VGA inputs, a high-low power operation selector, a mute button, and a home button to take you to the top menu.</p>
<p>Here, you can select inputs, play video directly from a micro SD memory card, view JPEG photos, connect to an external laptop or PC for display through the USB connections, and select whether you want a wide or truncated color space. (LEDs can output tremendously saturated colors!)</p>
<p>The projector also has 2 GB of internal memory, on which you will find stored (ready for this?) the owner’s manual. Cool, eh? Between that memory and the USB and micro SD ports, you can load and immediately view JPEG and BMP files, plus Powerpoint, Word, Excel, and Acrobat documents. You can even connect via WiFi with an $30 accessory dongle to make a presentation.</p>
<p>You can also connect an iPhone, iPod, and iPad to the ML300 with an optional connectivity kit for really high-tech presentations. Again, you simply choose the appropriate input (WiFi or micro SD) and start presenting. I can’t imagine any input option that Optoma has forgotten.</p>
<p>I found the menu navigation a bit tricky. The remote has to be pointing at the right part of the projector, or it won’t respond. The projector’s top menu buttons are backlit, but don’t light up until you press one of them. And when you’ve made a selection, you have to confirm it with the ‘O’ button, or back out of a menu with the ‘X” button.</p>
<p>One continual problem I had was setting the truncated color gamut and having that setting stick. To do this, I had to hit the Home button (a little house) and go into the Display settings menu. It was easy enough to toggle to the smaller gamut, but the setting wouldn’t keep when I switched back to HDMI input.</p>
<p>I suspect that was because the extended display identification data (EDID) my computer was transmitting to the ML300 identified that it was operating in 32-bit more. That probably triggered the projector to use the extended gamut, which of course makes colors over-saturated when viewing video. But it is annoying that I couldn’t override the setting.</p>
<p>The only other image adjustment you can make is to gamma. By playing with this setting and the color gamut, you can achieve a more accurate representation of colors when playing back video. I should add that you can’t make any image adjustments when viewing an input.</p>
<p>PERFORMANCE</p>
<p>The ML300 is really a set it and forget it, ‘plug and play’ product that will generally give you great pictures. Just connect your source, turn it on, and present (or watch). But I thought it would be useful to measure some key parameters, such as gamma and color temperature.</p>
<p>But first, the brightness and contrast readings. I set the projector up in my theater and lit up a 92” Da-Lite Affinity screen, measuring 152 ANSI lumens in Film mode with the LEDs running at reduced power. That number jumped to 173 ANSI lumens in video mode and 198 ANSI lumens in Photo mode.</p>
<p>Cranking the LEDs to full power raised my brightness measurement to 232 ANSI lumens. That’s about 22% less than the Optoma specification. Contrast numbers were pretty good – not great – at 244:1 ANSI in low-brightness mode, with a peak reading of 342:1. 50/50 (white/black) contrast was logged at 313:1, and sequential black/white contrast measured 373:1.</p>
<div id="attachment_2337" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2337" title="Optoma ML300 Luminance Histogram HDMI INPUT MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Optoma-ML300-Luminance-Histogram-HDMI-INPUT-MR.jpg" alt="" width="600" height="286" /><p class="wp-caption-text">Figure 1: Here’s the gamma curve for the HDMI input in film/video mode. It averages 2.13, which is a bit on the shallow side, and flattens out above 60 IRE.</p></div>
<p> </p>
<div id="attachment_2338" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2338" title="Optoma ML300 Temperature Histogram HDMI INPUT MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Optoma-ML300-Temperature-Histogram-HDMI-INPUT-MR.jpg" alt="" width="600" height="286" /><p class="wp-caption-text">Figure 2: The ML300′s color temperature is too high, but at least it’s consistent.</p></div>
<p>Figure 1 shows the standard gamma setting in film and video mode compared to PC mode. There’s not much of a difference, and the gamma is in the vicinity of 2.0 – 2.2 below 50 IRE. However, it becomes a straight line above 70 IRE and in PC mode, shows the slightest inclination to roll over and clip highlights.</p>
<p>Color temperature performance is a bit erratic, as seen in Figure 2.  You can’t set the color temperature manually, and it averages 7200 Kelvin to 7700 Kelvin in all input modes, depending on the gray level being shown. It would be nice if Optoma dialed the color temperature down about 100 degrees – it shouldn’t be hard to do with the LED light engine.</p>
<p>I will give this projector credit for being consistent. Figure 3 shows the RGB histogram film/video mode, and it is rock-steady. That means if Optoma could rebalance the color temperature to a more-palatable 6500K, it should stay right there from 0 to 100 IRE.</p>
<div id="attachment_2339" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2339" title="Optoma ML300 RGB Levels Histogram HDMI INPUT MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Optoma-ML300-RGB-Levels-Histogram-HDMI-INPUT-MR.jpg" alt="" width="600" height="286" /><p class="wp-caption-text">Figure 3: That’s a remarkably steady RGB histogram, even at low gray levels.</p></div>
<p> </p>
<p>Now, about the wide color gamut: Figure 4 shows just how big it is, and that’s what LEDs deliver- saturated, intense colors that go far beyond the limited shades that can be shown in the BT.709 HDTV standard.</p>
<p>Some folks love these ‘deep’ colors. Well, they certainly do ‘pop’ off the screen, but flesh tones are exaggerated as a consequence and some colors are not accurate (greens in particular can shift in hue). While you can select the smaller gamut as seen in Figure 5, it seems to switch back to a wide gamut when you select your signal source, particularly if that source supports extended color bit depths. A manual override would be nice!</p>
<div id="attachment_2340" class="wp-caption aligncenter" style="width: 549px"><img class="size-full wp-image-2340" title="Optoma ML300 CIE  WIDE GAMUT" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Optoma-ML300-CIE-WIDE-GAMUT.jpg" alt="" width="539" height="606" /><p class="wp-caption-text">Figure 4: Got color? You betcha!</p></div>
<p> </p>
<div id="attachment_2341" class="wp-caption aligncenter" style="width: 549px"><img class="size-full wp-image-2341" title="Optoma ML300 TK2 CIE Chart 720p RGB" src="http://www.hdtvexpert.com/wp-content/uploads/2012/08/Optoma-ML300-TK2-CIE-Chart-720p-RGB.jpg" alt="" width="539" height="606" /><p class="wp-caption-text">Figure 5: The ML300′s truncated color gamut is quite a bit closer to the BT.709 HDTV standard. Good luck selecting it, though.</p></div>
<p>PERFORMANCE</p>
<p>For Viewing PC content, the ML300 more than carries its weight. You’ll get the best image quality if you drive it at its native 1280×800 resolution, which just happens to be the native/preferred timing stored in the projector’s EDID. It’s also quite happy with 1280×720 in RGB mode. Otherwise, the remaining PC formats it supports are all 4:3.</p>
<p>The projector takes a few seconds to recognize, poll, and lock up to an HDMI signal. That’s because it’s reading EDID first and then looking for copy protection keys if your source is a Blu-ray player, DVD player, or set-top box. If you have a computer with a Blu-ray drive (like my Toshiba Satellite), it will look for keys there, too. In fact, the projector takes longer to establish an HDMI connection than it does to power up. Weird…</p>
<p>Video quality isn’t up to that of a home theater projector, but what can you expect for $500? A handful of projector manufacturers are dabbling in LED light engines and the ones I’ve seen that are accurate in terms of gamut, color temperature, and gamma are many times more expensive than the ML300.</p>
<p>Still, the video quality you get is serviceable, especially if you are playing back progressive-scan material. And let’s face it; you’re not likely to use this projector in a home theater, particularly since you can’t really calibrate it.</p>
<p>CONCLUSION</p>
<p>In terms of ease of use and connectivity options, the ML300 rocks the house. I can’t see any faster way to get a presentation up and running, and the doggone thing is so lightweight that you can place it just about anywhere. (Watch you don’t trip on the power block cable, though!) And with a maximum power draw of 90 watts in high output mode, it doesn’t get all that hot. (Nor does it get all that noisy at 36 dB!)</p>
<p>I’d like to see Optoma re-work the menu to speed up navigation and allow changes to gamma and color gamut without exiting the input menu. As far as the accessory cables go, come on guys – I found a ten-foot Mini HDMI cable on Amazon for about $11.  Be a pal and throw one in the box, will ya?</p>
<p> </p>
<p><strong>Optoma ML300 LED portable projector</strong></p>
<p><strong>SRP: $499</strong></p>
<p> </p>
<p><strong>Available from:</strong></p>
<p><strong>Optoma USA</strong><strong> </strong></p>
<p>3178 Laurelview Ct.<br />
Fremont, CA 94538<br /><strong>Tel</strong><strong>:</strong> (510) 897-8600<br /><strong>Fax</strong><strong>:</strong> (510) 897-8601</p>
<p><a href="http://www.optomausa.com" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.optomausa.com']);">www.optomausa.com</a></p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>August  8, 2012  5:33 PM</b>
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
			<?=getComments(4886)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4886)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/08/hdtv-expert-useful-gadgets-optoma-ml300-led-projector.php" type="text/javascript" charset="utf-8"></script>
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