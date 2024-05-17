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
		AND e.entry_id = 4616";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4616 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4616 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4616";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/sharp-makes-bigger-better-with-a-new-largescreen-led-tv-lineup-smart-features-and-enhanced-picture-quality.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4616";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp&reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp&reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sharp&reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality" />
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
	<title>HDTV Magazine - Sharp&reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality</title>
	<meta name="keywords" content="led tvs, picture quality, sharp aquos, aquos quattron, inch class, sharp, led, aquos, series, tvs, screen, new, quattron, inch, full, models, smartcentral, class, picture, hdmi, trademark, quality, registered, panel, features" />
	<meta name="description" content="Introducing more than 20 large screen 60-inch plus class TVs, Sharp today announced its new line of AQUOS&amp;reg; LED TVs, featuring picture quality enhancements, advanced smart connectivity and new designs. Serving as its flagship model, Sharp unveiled the first 80-inch (80&quot; diagonal) screen size class Quattron&amp;trade; 3D LED TV, available in April 2012.

Sharp will expand upon its market-leading selection of large-screen LED TVs in 2012 by offering..." />
	<meta name="title" content="Sharp&amp;reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sharp&amp;reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/sharp-makes-bigger-better-with-a-new-largescreen-led-tv-lineup-smart-features-and-enhanced-picture-quality.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Introducing more than 20 large screen 60-inch plus class TVs, Sharp today announced its new line of AQUOS&amp;reg; LED TVs, featuring picture quality enhancements, advanced smart connectivity and new designs. Serving as its flagship model, Sharp unveiled the first 80-inch (80&quot; diagonal) screen size class Quattron&amp;trade; 3D LED TV, available in April 2012.

Sharp will expand upon its market-leading selection of large-screen LED TVs in 2012 by offering..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4616', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/sharp-makes-bigger-better-with-a-new-largescreen-led-tv-lineup-smart-features-and-enhanced-picture-quality.php">Sharp&reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">Sharp&reg; Makes Bigger, Better with a New Large-Screen LED TV Line-up, Smart Features and Enhanced Picture Quality</p>

<center><i>Innovative New Technologies Include SmartCentral&trade;, AQUOS Advantage LIVE℠, Quad Pixel Plus II, Full-Array LED Backlighting with Local Dimming and Sharp's First Quattron&trade; 80-inch Class 3D LED TV</center></i><br />
<br />

<p>2012 International CES<br />
Booth #10916</p>

<p><strong>LAS VEGAS--(BUSINESS WIRE)--</strong>Introducing more than 20 large screen 60-inch plus class TVs, Sharp today announced its new line of AQUOS&reg; LED TVs, featuring picture quality enhancements, advanced smart connectivity and new designs. Serving as its flagship model, Sharp unveiled the first 80-inch (80" diagonal) screen size class Quattron&trade; 3D LED TV, available in April 2012.</p>

<p>"As the number one market share leader in the large screen television market, we're excited to provide consumers with more choices, better designs and innovative features," said John Herrington, president of Sharp Electronics Marketing Company of America, a division of Sharp Electronics Corporation. "It's clear that consumers are looking for the biggest television experience coupled with the highest picture quality possible, which is exactly what Sharp is delivering in 2012."</p>

<p><br />
<strong>The New 2012 Line-Up</strong></p>

<p>Sharp will expand upon its market-leading selection of large-screen LED TVs in 2012 by offering a wider array of choices to consumers, including more than 20 models in screen size classes of 60-inches or larger, many featuring 3D in 120Hz or 240Hz panel options. The AQUOS Quattron models will feature the new Quad Pixel Plus II technology. All Sharp AQUOS LED TVs meet or exceed ENERGY STAR&reg; version 5.3 standards, drawing a relatively small amount of power for their screen sizes. The 80" class LC-80LE632U is the most energy-efficient flat-panel TV available today, as measured by power consumption per square inch. On average, it costs just $22 per year in electricity use to operate.</p>

<p><br />
<strong>Taking the 80-inch Class to a New Dimension</strong></p>

<p>In September 2011, Sharp launched the industry's largest LED TV with its 80-inch class LC-80LE632U, providing more than twice the screen area of a 55-inch TV. Today, Sharp is taking the 80-inch class TV to a new dimension with the launch of the new LC-80LE844U. Available in April, the full HD 1080p LC-80LE844U features a vivid 3D image, employing Sharp's Quattron technology with full array LED backlighting and a newly developed 240Hz AQUOS LCD panel to virtually eliminate blur during fast-moving video. The LC-80LE844U also boasts built-in WiFi, 4 HDMI&reg; inputs and the new SmartCentral user interface.</p>

<p><br />
<strong>Enhanced Picture Quality</strong></p>

<p>Sharp is introducing Quad Pixel Plus II, the latest innovation for its exclusive Quattron technology. Quad Pixel Plus II, available on the 60-inch (60 1/32" diagonal) and 70-inch (69 ½" diagonal) classes Sharp AQUOS Quattron 8 and 9 series LED TVs, offers even greater detail, smoother lines and color transitions. Sharp is improving picture quality even more in the 9 Series with the introduction of full array LED backlighting with local dimming, which provides deep black levels and an optimal contrast ratio, while reducing energy consumption. Local dimming allows specific groups of LEDs to be dimmed for greater control of brightness and darkness in different areas of the screen for outstanding black levels.</p>

<p><br />
<strong>Ownership Experience</strong></p>

<p>AQUOS connected TV owners have access to AQUOS Advantage LIVE℠, Sharp's unique, complimentary customer support program. AQUOS Advantage Live allows trained advisors to remotely connect via the Internet to AQUOS TVs to assist with setup, troubleshooting and picture optimization. And with LIVE answer, Sharp's experts are available to customers through a dedicated line, 1-87-SEE-AQUOS, providing instantaneous, remote access to representatives. SmartCentral will be integrated in most of Sharp's new 2012 LED TVs, including the 6, 7, 8 and 9 Series.</p>

<p><br />
<strong>The Smarter TV -- SmartCentral</strong></p>

<p>The TV is the entertainment hub of the home with consumers demanding the ability to easily access more types of content directly from their TV. Sharp is answering this demand with SmartCentral, a newly designed easy to use, intuitive and customizable user interface. Select models give consumers the ability to search for content, browse the web and access many of the most popular applications, including Netflix&reg;, Hulu&trade;, Facebook&reg;, CinemaNow&reg; and YouTube&trade; among others. Users can customize their experience with personalized settings, including selecting favorite apps, and choosing from multiple wallpapers and with select models, two viewing formats – full screen or dock.</p>

<p>Another smart feature is Sharp AQUOS Beamzit&trade;, a free media-sharing app that allows users to wirelessly send photos, music and video to a Sharp AQUOS Wi-Fi enabled television from their IOS&reg; or Android&trade; smart phone or tablet.*</p>

<p><br />
<strong>Sharp AQUOS Quattron 9 Series 3D LED TVs</strong></p>

<p>Sharp's full HD 1080p 9 Series AQUOS Quattron 3D LED TVs in screen size classes of 60- and 70-inches (LC-60LE945U and LC-70LE945U) offer advanced LED technology with Sharp's brightest Full HD Active 3D panel. They incorporate Quattron Quad Pixel Plus II and full array LED backlighting with local dimming to improve picture quality and deliver a superior viewing experience. The 240Hz panel improves picture quality further by virtually eliminating blur and artifacts from fast-moving video.</p>

<p>The 9 Series features Sharp's new SmartCentral and built-in WiFi. With five HDMI&reg; inputs, consumers will be able to connect a variety of devices – including computers, to Blu-ray Disc&trade; players, DVRs, camcorders, sound bar systems and video game consoles – directly to their TV.</p>

<p>Introduction: Summer</p>

<p><br />
<strong>Sharp AQUOS Quattron 8 Series 3D LED TVs</strong></p>

<p>Sharp's 8 Series AQUOS Quattron 3D LED TVs in screen size classes of 60-, 70- and 80-inches (LC-60LE847U, LC-70LE847U and LC-80LE844U) offer a sleeker look with a newly designed ultra-slim bezel with a black brushed aluminum finish. The 80-inch class model features full array LED, and the 60- and 70-inch class models are edge-lit LED, all with Quattron Quad Pixel Plus II technology and 240Hz.</p>

<p>Like the 9 Series, the 8 series offers full HD 1080p with built-in WiFi and SmartCentral. The 8 Series includes four HDMI inputs.</p>

<p>Introduction: April<br />
MSRP: LC-60LE847U, $3,199; LC-70LE847U, $4,199; LC-80LE844U, $6,499</p>

<p><br />
<strong>Sharp AQUOS 7 Series 3D LED TVs</strong></p>

<p>Sharp's new 7 Series AQUOS 3D TVs models in screen size classes of 60- and 70-inches (LC-60LE745U and LC-70LE745U) also feature a newly designed ultra-slim bezel design with a rich black-brushed aluminum finish. These Full HD Active 3D edge-lit LED TVs feature Sharp's high-performance AQUOS LCD Panel, full HD 1080p and 120Hz refresh rate. The 7 Series also features built-in WiFi, SmartCentral and four HDMI inputs.</p>

<p>Introduction: March<br />
MSRP: LC-60LE745U, $2,699; LC-70LE745U, $3,599</p>

<p><br />
<strong>Sharp AQUOS 6/5 Series LED TVs</strong></p>

<p>Sharp's 6/5 series comprises five different models in screen size classes of 42-, 46-, 52-, 60- and 70-inches (LC-42LE540U, LC-46LE540U, LC-52LE640U, LC-60LE640U, LC-70LE640U) and includes a high-performance AQUOS LCD Panel, edge lit LED backlighting, 120Hz Fine Motion Enhanced, and a new narrow bezel design. Like other Sharp series, these models offer built-in WiFi and access to Sharp's newly designed SmartCentral user interface (docking layout), access to the most popular apps and AQUOS Advantage Live (LE640 Series only). Four HDMI inputs offer multiple ports to connect to your favorite devices.</p>

<p>Introduction: LE640 February; LE540 March<br />
MSRP: LC-52LE640U, $1,699; LC-60LE640U, $2,699; LC-70LE640U, $3,299</p>

<p>For more information on what Sharp announced at CES, please visit <a target="_blank" href="http://www.SharpUSANews.com/">www.SharpUSANews.com</a>. For more information visit Sharp Electronics Corporation at <a target="_blank" href="http://www.sharpusa.com/">www.sharpusa.com</a>. Find us on Facebook, follow us on Twitter and watch us on YouTube.</p>

<p><br />
<strong>About Sharp Electronics Corporation:</strong></p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions, LED lighting and mobile communication and information tools. Leading brands include AQUOS&reg; Quattron&trade; LED TVs and 3DTVs, SharpVision&reg; projectors, Insight&reg; Microwave Drawer&reg; ovens, Notevision&reg; multimedia projectors and Plasmacluster&reg; air purifiers.</p>

<p>* Available on some AQUOS TV models.</p>

<p>Sharp, and all related trademarks are trademarks or registered trademarks of Sharp Corporation and/or its affiliated entities.</p>

<p>Blu-ray Disc&trade;, Blu-ray&trade; and Blu-ray 3D&trade; are the logos and trademarks of the Blu-ray Disc Association.<br />
CinemaNow is a trademark of BBY Solutions, Inc.<br />
ENERGY STAR is a registered trademark of the U.S. Environmental Protection Agency.<br />
Facebook is a registered trademark of Facebook Inc.<br />
HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing LLC in the United State and other countries.<br />
Hulu is a registered trademark of Hulu, LLC.<br />
Netflix is a registered trademark of Netflix, Inc.<br />
The Twitter name is a trademark of Twitter Inc.<br />
VUDU is a trademark of VUDU, Inc.<br />
Android and YouTube are trademarks of Google, Inc.<br />
IOS is a registered trademark of Cisco.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2012  9:57 PM</b>
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
			<?=getComments(4616)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4616)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/sharp-makes-bigger-better-with-a-new-largescreen-led-tv-lineup-smart-features-and-enhanced-picture-quality.php" type="text/javascript" charset="utf-8"></script>
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