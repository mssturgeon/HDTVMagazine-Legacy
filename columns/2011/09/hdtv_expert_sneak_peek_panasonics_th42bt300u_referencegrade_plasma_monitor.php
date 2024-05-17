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
		AND e.entry_id = 4522";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4522 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4522 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4522";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/09/hdtv-expert-sneak-peek-panasonics-th42bt300u-referencegrade-plasma-monitor.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4522";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Sneak Peek: Panasonic&rsquo;s TH-42BT300U Reference-Grade Plasma Monitor" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Sneak Peek: Panasonic&rsquo;s TH-42BT300U Reference-Grade Plasma Monitor" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Sneak Peek: Panasonic&rsquo;s TH-42BT300U Reference-Grade Plasma Monitor" />
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
	<title>HDTV Magazine - HDTV Expert - Sneak Peek: Panasonic&rsquo;s TH-42BT300U Reference-Grade Plasma Monitor</title>
	<meta name="keywords" content="color gamut, color temperature, chart shows, red green, plasma monitor, color, panasonic, gamma, series, monitor, monitors, gamut, mode, plasma, could, those, green, nits, white, much, ’s, industrial, black, performance, next" />
	<meta name="description" content="It&amp;acirc;��s about as accurate as any flat-panel display can get." />
	<meta name="title" content="HDTV Expert - Sneak Peek: Panasonic&amp;rsquo;s TH-42BT300U Reference-Grade Plasma Monitor" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Sneak Peek: Panasonic&amp;rsquo;s TH-42BT300U Reference-Grade Plasma Monitor" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/09/hdtv-expert-sneak-peek-panasonics-th42bt300u-referencegrade-plasma-monitor.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It&amp;acirc;��s about as accurate as any flat-panel display can get." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4522', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/09/hdtv-expert-sneak-peek-panasonics-th42bt300u-referencegrade-plasma-monitor.php">HDTV Expert - Sneak Peek: Panasonic&rsquo;s TH-42BT300U Reference-Grade Plasma Monitor</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>September 15, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=506&category=Plasma HDTVs">Plasma HDTVs</a></b>
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
          <p>It’s been almost six years in the making, but Panasonic has finally come out with a reference-grade plasma monitor. First announced at the 2011 HPA Technology Retreat and shown publicly at NAB 2011, the TH-42BT300U (and its 50-inch brother, the TH-50BT300U) is an industrial monitor that doubles as a reference display for post-production and a host of critical imaging applications.</p>
<p> </p>
<p>Panasonic is one of three companies still manufacturing plasma displays in quantity, the others being Samsung and LG. But Panasonic has made significant investment in PDP fabrication and also branched out into larger sizes (85”, 103”, and 151”).</p>
<div id="attachment_1488" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1488" href="http://www.hdtvexpert.com/?attachment_id=1488"><img class="size-full wp-image-1488" title="Panasonic TH-42BT300U and TH-PF20U MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH-42BT300U-and-TH-PF20U-MR.jpg" alt="" width="600" height="333" /></a><p class="wp-caption-text">Panasonic's TH-42BT300U (left) blew away the TH-42PF20U (right) at Panasonic's NAB suite in the 'crow's nest'</p></div>
<p>AN INDUSTRY IN TRANSITION</p>
<p> </p>
<p>As the days of the time-tested Sony BVM-series and PVM-series CRT monitors ran short, many studios and post houses started to experiment with using LCD and plasma TVs and monitors to replace them. Problem was, these consumer TVs didn’t offer anywhere near the consistency and accuracy of the BVMs. They were often way too bright, had S-curve gamma performance, high black levels, and wildly inaccurate color coordinates and gamuts.</p>
<p> </p>
<p>Sony even tried to replace its BVMs and PVMs with a line of LCD monitors, known as Trimaster. These expensive displays ($25K for a 23-inch monitor) were certainly accurate, but could not overcome many of the physical and optical limitations of LCD technology.</p>
<p> </p>
<p>Their biggest drawback was price. Customers just could not rationalize spending that much money for a small LCD monitor, particularly when they could buy a 50-inch industrial plasma for less than $5,000. And the pictures they got out of the industrial plasma monitors looked pretty darned good! (But not accurate.)</p>
<p> </p>
<p>I started testing Panasonic’s 42-inch industrial monitors back in 2008 to see if they could be ‘tuned up’ for improved performance, using the existing set of controls. The answer was, they could indeed be calibrated, as long as they were not running in ‘dynamic’ picture mode, and all ‘enhancements’ to images were shut off.</p>
<p> </p>
<p>In fact, the model I tested – the TH-42PF11U – performed so well that many post houses started putting them everywhere in their facilities. The price point (less than $2,000) couldn’t be beat, and Panasonic offered both SDI and HD-SDI modular input cards, along with dual HDMI inputs. After a seminar at B&amp;H photo in 2009 to explain how to tune up the TH-42PF11U, I recall a Panasonic area rep as saying the company was back-ordered on this particular monitor to the tune of almost 800 units.</p>
<p> </p>
<p>YOU CAN ALWAYS MAKE IT BETTER</p>
<p> </p>
<p>As well as the TH-42PF11U performed, it lacked some critical settings and adjustments. First of all, the monitor’s color gamut never changed in any mode. It covered most of the digital cinema P3 gamut, but far exceeded the BT.709 HDTV gamut. And you couldn’t adjust it.</p>
<p> </p>
<p>Second, there were only a handful of pre-set gamma curves, like 2.0, 2.2, and 2.6. While these did turn out to be close to ideal, there were problems getting the blue channel to track correctly from black to white and produce a stable value of gray at any brightness level.</p>
<p> </p>
<p>Finally, there were no memory settings available to save a bunch of different calibrations. Depending on the source material and the final master file, a post house could be working with EBU or HDTV color spaces, or even a digital intermediate that might emulate the P3 DCI space. How could all of those settings be saved?</p>
<p> </p>
<p>The TH-42PF11U was replaced by the TH-42PF20U, which was more of a digital sign display and had major black level issues. So that wasn’t the solution. It took one more year of waiting for the 300-series to make their debut, and the wait was well worth it.</p>
<div id="attachment_1489" class="wp-caption aligncenter" style="width: 410px"><a rel="attachment wp-att-1489" href="http://www.hdtvexpert.com/?attachment_id=1489"><img class="size-full wp-image-1489" title="TH-42BT300" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/TH-42BT300.jpg" alt="" width="400" height="324" /></a><p class="wp-caption-text">Yeah, you're right - these new monitors don't look very 'sexy.' But it's what's inside the counts.</p></div>
<p>THE HIGHLIGHTS…</p>
<p> </p>
<p>Those of us who had been nagging Panasonic to step up their game got almost everything we wanted in the 300-series. No, we didn’t get 10 steps of individual red, green, blue, and white gamma adjustments, although you almost don’t need them now. But we did get multiple memories with custom labeling, and we finally got a color gamut adjustment, with one custom setting for fine-tuning x,y coordinates for primaries.</p>
<p> </p>
<p>The TH-42BT300U isn’t quite as bright as its predecessors. That’s because it has additional first surface filtering, a trick stolen from Pioneer’s Kuro playbook. As a result, these monitors are not ‘blazingly’ bright, unless you operate them in Dynamic mode. But that’s OK, as Panasonic found that many editors and colorists were operating the 11-series monitors at much lower brightness levels than the 120-130 nits (over 30 ft-L) I was calibrating at.</p>
<p> </p>
<p>Now, you’ll see about 75-80 nits out of the TH-42BT300U in Cinema mode, and 85-95 nits in Standard mode. Both modes can be calibrated nicely to a specific color temperature, but gamma performance is most accurate in Cinema mode.</p>
<p> </p>
<p>Speaking of gamma; you’ll find numerous presets including 1.8, 2.0, 2.2, 2.35, 2.5, and 2.6. And they’re all right on the money – in my calibration tests, a 2.35 gamma resulted in an actual curve measuring 2.35. Close enough for government work!</p>
<p> </p>
<p>Better yet, the TH-42BT300U tracks a tighter grayscale than its grandfather, and the TH-4211PFU was no slouch in that department. Out of the box, the BT.709 color gamut setting was pretty close to ideal, but I was able to get it even closer using the Custom adjustments for red, green, and blue offsets.</p>
<p> </p>
<p>…AND THE PROOF</p>
<p> </p>
<p>Here are some performance charts I generated with ColorFacts 7.5 for your edification. First off is a gamma curve, plotted with the gamma set to 2.35 and peak white at about 75 nits (about 22 foot-Lamberts, for those of you who prefer your measurements in the olde English style).</p>
<p><a rel="attachment wp-att-1490" href="http://www.hdtvexpert.com/?attachment_id=1490"><img class="aligncenter size-full wp-image-1490" title="Panasonic TH42BT300 9-13-11 Luminance Histogram CINEMA 2.35 GAMMA FINAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH42BT300-9-13-11-Luminance-Histogram-CINEMA-2.35-GAMMA-FINAL-MR.jpg" alt="" width="600" height="334" /></a></p>
<p>Next up is a color temperature histogram plot. The dashed line is the target of 6500 Kelvin. Note that the accuracy of my sensor head (an X-rite Eye One Pro) is a little off below 10 IRE).</p>
<p><a rel="attachment wp-att-1491" href="http://www.hdtvexpert.com/?attachment_id=1491"><img class="aligncenter size-full wp-image-1491" title="Panasonic TH42BT300 9-13-11 Temperature Histogram CINEMA 2.35 GAMMA FINAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH42BT300-9-13-11-Temperature-Histogram-CINEMA-2.35-GAMMA-FINAL-MR.jpg" alt="" width="600" height="334" /></a></p>
<p>How does that line track so cleanly? The next chart shows you why. It’s an RGB levels histogram, plotted from 0 to 100 IRE. The mix of red, green, and blue is pretty consistent – not perfect, but adequate for most critical work.</p>
<p><a rel="attachment wp-att-1492" href="http://www.hdtvexpert.com/?attachment_id=1492"><img class="aligncenter size-full wp-image-1492" title="Panasonic TH42BT300 9-13-11 RGB Levels Histogram CINEMA 2.35 GAMMA FINAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH42BT300-9-13-11-RGB-Levels-Histogram-CINEMA-2.35-GAMMA-FINAL-MR.jpg" alt="" width="600" height="334" /></a></p>
<p>And our next chart shows just how closely the color gamut of the TH-42BT300U tracks the BT.709 color space. Out of the box, the green coordinate was slightly over-saturated and shifted toward yellow. Some quick adjustments in the Edit menu fixed that in a hurry.</p>
<p><a rel="attachment wp-att-1493" href="http://www.hdtvexpert.com/?attachment_id=1493"><img class="aligncenter size-full wp-image-1493" title="Panasonic TH42BT300 9-13-11 CIE Chart STANDARD 2.35 GAMMA BT.709 FINAL" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH42BT300-9-13-11-CIE-Chart-STANDARD-2.35-GAMMA-BT.709-FINAL.jpg" alt="" width="587" height="678" /></a></p>
<p>For those readers who want to know how much of the P3 color space is covered by the TH-42BT300U, look at the next chart. Note that the green saturation isn’t quite enough to do the trick, but you can calibrate a nice grayscale track for the DCI target color temperature (all those small white dots show where the plotted temperatures feel for each grayscale value).</p>
<p><a rel="attachment wp-att-1494" href="http://www.hdtvexpert.com/?attachment_id=1494"><img class="aligncenter size-full wp-image-1494" title="Panasonic TH42BT300 9-13-11 CIE Chart CINEMA P3 GAMMA 2.6" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH42BT300-9-13-11-CIE-Chart-CINEMA-P3-GAMMA-2.6.jpg" alt="" width="587" height="678" /></a></p>
<p>The last CIE chart shows the native full-gamut color capabilities of the TH-42BT300U (white outline), compared to the BT.709 HDTV color space (black outline).</p>
<p><a rel="attachment wp-att-1495" href="http://www.hdtvexpert.com/?attachment_id=1495"><img class="aligncenter size-full wp-image-1495" title="Panasonic TH42BT300 9-13-11 CIE Chart NATIVE VS BT.709" src="http://www.hdtvexpert.com/wp-content/uploads/2011/09/Panasonic-TH42BT300-9-13-11-CIE-Chart-NATIVE-VS-BT.709.jpg" alt="" width="587" height="678" /></a></p>
<p>And to wrap things up, here are some quickie contrast measurements:</p>
<p><em>After calibration, in Cinema Warm BT.709 mode with 2.35 gamma:</em></p>
<p>ANSI contrast = 771:1<br />
Peak contrast = 1527:1</p>
<p>Average white level, checkerboard pattern: 68.85 nits<br />
Average black level, checkerboard pattern: .089 nits</p>
<p>Maximum color temperature shift across a 100 IRE screen, measured at nine points: 76 degrees Kelvin </p>
<p>(That last number is mind-boggling for an industrial display monitor of any type!)</p>
<p> </p>
<p>CONCLUSIONS</p>
<p> </p>
<p>My hat’s off to Panasonic, although I am one of those who has been nagging them for years to come out with a reference plasma monitor. I’d like to think a lot of the testing I did on the 11-series and 12-series provided much of the momentum that led to the 300-series displays. Even so, the company figured out it had a diamond in the rough and started polishing.</p>
<p> </p>
<p>What’s up in the next generation? Let’s hope multi-point gamma correction finally makes it into the menu, along with improved color saturation in the green channel so it can cover more of the P3 space. Regardless, the TH-42BT300U delivers a level of performance that is an absolute bargain for the asking price, which as I understand it will be less than $5,000. (Much less, in fact!)</p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>September 15, 2011 12:00 PM</b>
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
			<?=getComments(4522)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4522)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/09/hdtv-expert-sneak-peek-panasonics-th42bt300u-referencegrade-plasma-monitor.php" type="text/javascript" charset="utf-8"></script>
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