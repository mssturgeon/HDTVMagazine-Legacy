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
		AND e.entry_id = 4935";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4935 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4935 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4935";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-557-black-friday-2012.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4935";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012</title>
	<meta name="keywords" content="samsung led, lcd hdtv, plasma hdtv, samsung plasma, blu ray, hdtv, led, samsung, lcd, plasma, mart, friday, rca, smart, panasonic, sharp, blu, ray, emerson, wal, dvd, slim, player, viera, seiki" />
	<meta name="description" content="It&amp;#039;s almost Thanksgiving here in the US, and with that comes the biggest holiday of the HT Guys&amp;#039; year: Black Friday. The key to a successful Black Friday is preparation. You need to know exactly what store you want to go to, what you want to get, and when you need to be there." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-557-black-friday-2012.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It&amp;#039;s almost Thanksgiving here in the US, and with that comes the biggest holiday of the HT Guys&amp;#039; year: Black Friday. The key to a successful Black Friday is preparation. You need to know exactly what store you want to go to, what you want to get, and when you need to be there." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4935', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-557-black-friday-2012.php">HDTV and Home Theater Podcast - Podcast #557: Black Friday 2012</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>November 16, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=314&category=General Interest">General Interest</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>Black Friday 2012</h3>
<p>It&#8217;s almost Thanksgiving here in the US, and with that comes the biggest holiday of the HT Guys&#8217; year: Black Friday. The key to a successful Black Friday is preparation. You need to know exactly what store you want to go to, what you want to get, and when you need to be there.</p>
<p>&nbsp;</p>
<h4>Best Buy:</h4>
<p><a href="http://bfads.net/Best-Buy-Black-Friday-Insignia-26-1080p-60Hz-LEDLCD-HDTV-Link">Insignia 26&#8243; LED 1080p 60Hz HDTV (NS-26E340A13)</a> $99.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Toshiba-40-1080p-60Hz-LCD-HDTV-Link">Toshiba 40&#8243; LCD 1080p 60Hz HDTV (40E220U)</a> $179.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Samsung-39-1080p-60Hz-LED-HDTV-Link">Samsung 39&#8243; LED 1080p 60Hz HDTV (UN39EH5003FXZA)</a> $397.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Toshiba-50-1080p-60Hz-LEDLCD-HDTV-Link">Toshiba 50&#8243; LED 1080p 60Hz HDTV (50L2200U)</a> $399.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Samsung-51-720p-600Hz-Plasma-HDTV-Link">Samsung 51&#8243; Plasma 720p 600Hz HDTV (PN51E450A1FXZA)</a> $477.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Insignia-55-1080p-120Hz-LCD-HDTV-Link">Insignia 55&#8243; LCD 1080p 120Hz HDTV (NS-55L260A13)</a> $599.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Samsung-55-LED-1080p-240Hz-HDTV-Link">Samsung 55&#8243; LED 1080p 240Hz HDTV</a> $799.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Panasonic-55-1080p-120Hz-Smart-LED-3D-HDTV-w-4-Pairs-of-3D-Glasses-Link">Panasonic 55&#8243; LED 1080p 120Hz Smart 3D HDTV (TC-L55ET5) w/ 4 Pairs of 3D Glasses</a>$899.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Sharp-Aquos-60-1080p-120Hz-LED-HDTV-Link">Sharp Aquos 60&#8243; LED 1080p 120Hz HDTV (LC-60LE600U)</a> $999.99</p>
<p><a href="http://bfads.net/Best-Buy-Black-Friday-Kipsch-Icon-612-2Way-Floor-Speaker-Link">Klipsch Icon 6-1/2-in. 2-Way Floor Speaker</a> $174.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Klipsch-Icon-Dual-8-2-way-floor-Speaker-Each-Link">Klipsch Icon Dual 8-in. 2-Way Floor Speaker (Each)</a> $224.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Logitech-Harmony-650-5-Device-Universal-Remote-Link">Logitech Harmony 650 5 Device Universal Remote</a> $39.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Toshiba-Smart-WiFi-Bluray-Player-Link">Toshiba Smart WiFi Blu-ray Player</a> $39.99</p>
<p><a href="http://bfads.net/Best-Buy-Black-Friday-Dell-Entry-E2311H-23-LED-LCD-Monitor-169-5-ms-Link">Dell 23&#8243; LED LCD Monitor</a> $109.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-LG-27-Widescreen-FlatPanel-LED-HD-Monitor-Link">LG 27&#8243; Widescreen Flat-Panel LED HD Monitor</a> $199.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Seagate-Backup-Plus-3TB-External-USB-30-Hard-Drive-2012-Link">Seagate Backup Plus 3TB External USB 3.0 Hard Drive</a> $99.99<br />
<a href="http://bfads.net/Best-Buy-Black-Friday-Gateway-AllInOne-Desktop-w-AMD-E-Series-APU-4GB-RAM-500GB-HDD-20-Display-ZX4250UB308-Link">Gateway One 20&#8243; All-In-One Computer w/ AMD E-Series, 4GB RAM, 500GB HD</a> $389.99</p>
<p>&nbsp;</p>
<h4>Target:</h4>
<p><a href="http://bfads.net/Target-Black-Friday-APEX-32-LCD-1080p-HDTV-Link">APEX 32&#8243; LCD 720p HDTV (LD3288M)</a> $147.00<br />
<a href="http://bfads.net/Target-Black-Friday-Samsung-32-LED-720p-60Hz-HDTV-UN32EH4003-Link">Samsung 32&#8243; LED 720p 60Hz HDTV (UN32EH4003)</a> $247.99<br />
<a href="http://bfads.net/Target-Black-Friday-Westinghouse-50-LCD-1080p-HDTV-Link">Westinghouse 50&#8243; LCD 1080p HDTV (CW50T9XW)</a> $349.00<br />
<a href="http://bfads.net/Target-Black-Friday-Samsung-50-LED-1080p-60hz-HDTV-UN50EH5000-Link">Samsung 50&#8243; LED 1080p 60hz HDTV (UN50EH5000)</a> $699.00<br />
<a href="http://bfads.net/Target-Black-Friday-Samsung-WiFi-BluRay-Player-Link">Samsung WiFi Blu-Ray Player</a> $69.99<br />
RCA 7&#8243; Dual-Screen Portable DVD Player $59.99<br />
<a href="http://bfads.net/Target-Black-Friday-RCA-9-Portable-DVD-Player-2012-Link">RCA 9&#8243; Portable DVD Player</a> $55.00<br />
<a href="http://bfads.net/Target-Black-Friday-Battleship-Board-Game-Link">Battleship Board Game</a> $7.00</p>
<p>&nbsp;</p>
<h4>Wal-Mart:</h4>
<p>Orion 24&#8243; LED 720p 60Hz HDTV (SLED2468W) $78.00<br />
Emerson 32&#8243; LCD 720p 60Hz HDTV $148.00<br />
Emerson 40&#8243; LCD 1080p 60Hz HDTV $198.00<br />
Emerson 50&#8243; LCD 1080p 60Hz HDTV (LC501EM3) $298.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-Samsung-32-720p-60Hz-LED-HDTV-Link">Samsung 32&#8243; LED 720p 60Hz HDTV (UN32EH4003)</a> $248.00<br />
Samsung 39&#8243; LED 1080p 60Hz HDTV (UN39EH5003) $398.00<br />
Samsung 43&#8243; Plasma 720p 600Hz HDTV (PN43E490) $378.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-Samsung-46-Smart-LED-180p-120Hz-HDTV-w-Wifi-Link">Samsung 46&#8243; LED 1080p 120Hz HDTV (UN46EH5300)</a> $598.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-Samsung-50-LED-1080p-60Hz-HDTV-Link">Samsung 50&#8243; LED 1080p 60Hz HDTV (UN50EH5000)</a> $698.00<br />
Samsung 51&#8243; Plasma 720p 600Hz HDTV (PN5E45P) $478.00<br />
Sharp 60&#8243; LED 1080p 120Hz HDTV (LC-60LE600U) $998.00<br />
Sharp 70&#8243; LED 1080p 120Hz HDTV (LC-70LE600U) $1,798.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-Vizio-60-LED-1080p-120Hz-Smart-TV-w-Wifi-Link">Vizio 60&#8243; LED 1080p 120Hz HDTV (E601i-A3)</a> $688.00</p>
<p>iLive 37-in. Sound Bar (IT123B) $38.00<br />
LG Blu-ray Player $38.00<br />
LG 3D Blu-Ray Player w/ Wi-Fi $68.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-Philips-3D-WiFi-BluRay-Home-Theater-Link">Philips 3D Wi-Fi Blu-Ray Home Theater</a> $128.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-RCA-Streaming-Box-Link">RCA Streaming Box</a> $29.00<br />
<a href="http://bfads.net/Walmart-Black-Friday-Samsung-Sound-Bar-HWE350-Link">Samsung Sound Bar (HW-E350)</a> $98.00<br />
Sylvania 7&#8243; Dual-Screen DVD Player $49.00<br />
Sylvania 7&#8243; Single-Screen DVD Player $38.00<br />
HP Pavilion All-In-One Desktop w/ 4GB RAM, 500GB HDD, 20&#8243; Display (120-1333W) $389.00</p>
<h4>
K-Mart:</h4>
<p>Seiki 24&#8243; LED 1080p HDTV $88.00<br />
Proscan 32&#8243; LCD HDTV (LCD3283B) $97.00<br />
<a href="http://bfads.net/Kmart-Black-Friday-Proscan-32-LCD-720p-60Hz-HDTVDVD-Combo-PLCDV3213A-Link">Proscan 32&#8243; LCD 720p 60Hz HDTV/DVD Combo (PLCDV3213A)</a> $229.99<br />
<a href="http://bfads.net/Kmart-Black-Friday-RCA-24-1080p-LED-HDTV-LED24B45RQ-Link">RCA 24&#8243; 1080p LED HDTV (LED24B45RQ)</a> $199.99<br />
<a href="http://bfads.net/Kmart-Black-Friday-Samsung-32-720p-LCD-HDTV-N32D403-Link">Samsung 32&#8243; 720p LCD HDTV (N32D403)</a> $249.99<br />
Panasonic 32&#8243; LCD HDTV $279.99<br />
<a href="http://bfads.net/Kmart-Black-Friday-RCA-39-1080p-LCD-HDTV-39LB45RQ-Link">RCA 39&#8243; 1080p LCD HDTV (39LB45RQ)</a> $329.99<br />
<a href="http://bfads.net/Kmart-Black-Friday-RCA-42-Plasma-HDTV-Link">RCA 42&#8243; Plasma HDTV (42PA30RQ)</a> $199.99<br />
Samsung 43&#8243; Plasma HDTV $379.99<br />
RCA 52&#8243; LCD 1080p HDTV $399.99<br />
LG 47&#8243; LED 1080p HDTV $599.99</p>
<h4>
Sears:</h4>
<p>LG 47&#8243; 3D Slim LED 1080p 120Hz $699.99<br />
<a href="http://bfads.net/Sears-Black-Friday-LG-55-LED-Cinema-1080p-120Hz-3D-HDTV-55LM4600-Link">LG 55&#8243; LED Cinema 1080p 120Hz 3D HDTV (55LM4600)</a> $999.99<br />
LG 55&#8243; Slim LED 1080p 120Hz HDTV $899.99<br />
Panasonic 32&#8243; LED 1080p HDTV $349.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Panasonic-50-Viera-Plasma-HDTV-TCP50X5-Friday-at-4AM-Link">Panasonic 50&#8243; Viera Plasma HDTV (TC-P50X5) (Friday at 4AM)</a> $299.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Panasonic-50-Viera-Plasma-HDTV-TCP50X5-Link">Panasonic 50&#8243; Viera Plasma HDTV TC-P50X5</a> $299.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Panasonic-Viera-50-Plasma-1080p-600Hz-HDTV-TCP50U50-Link">Panasonic Viera 50&#8243; Plasma 1080p 600Hz HDTV (TC-P50U50)</a> $699.99<br />
Samsung 39&#8221; LED 1080p HDTV (UN39EH5003) $399.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Samsung-40-1080p-120Hz-LED-HDTV-UN40EH6000-Link">Samsung 40&#8243; 1080p 120Hz LED HDTV (UN40EH6000)</a> $549.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Samsung-40-AudioBar-w-Wireless-Subwoofer-HWE450-Link">Samsung 40&#8243; AudioBar w/ Wireless Subwoofer (HW-E450)</a> $179.99<br />
Samsung 40&#8243; LED 1080p 120Hz HDTV $599.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Samsung-46-1080p-120Hz-Slim-LED-Smart-HDTV-UN46ES6100-Link">Samsung 46&#8243; 1080p 120Hz Slim LED Smart HDTV (UN46ES6100)</a> $797.99<br />
Samsung 51&#8243; Plasma 1080p 600Hz HDTV $579.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Samsung-55-1080p-240Hz-UltraSlim-LED-3D-Smart-HDTV-UN55ES7100-w-4-pairs-of-3D-Glasses-Link">Samsung 55&#8243; 1080p 240Hz Ultra-Slim LED 3D Smart HDTV (UN55ES7100) w/ 4 pairs of 3D Glasses</a> $1,597.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Samsung-60-1080p-120Hz-LED-HDTV-UN60EH6000-Link">Samsung 60&#8243; 1080p 120Hz LED HDTV (UN60EH6000)</a> $1,199.99<br />
Samsung 60&#8243; Slim LED 1080p 120Hz HDTV $1,299.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Seiki-32-LED-HDTV-SE322FS-Link">Seiki 32&#8243; LED HDTV (SE322FS)</a> $199.99<br />
Seiki 39&#8243; 1080p LCD HDTV (SC392TS) (Friday at 4AM) $199.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Sharp-52-Aquos-LED-1080p-120Hz-Smart-TV-LC52LE640U-Link">Sharp 52&#8243; Aquos LED 1080p 120Hz Smart TV (LC52LE640U)</a> $799.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Sharp-60-Aquos-LED-1080p-120Hz-Smart-TV-LC60LE640U-Link">Sharp 60&#8243; Aquos LED 1080p 120Hz Smart TV (LC60LE640U)</a> $1,199.99<br />
<a href="http://bfads.net/Sears-Black-Friday-Sharp-70-Aquos-LED-1080p-120Hz-Smart-TV-LC70LE640U-Link">Sharp 70&#8243; Aquos LED 1080p 120Hz Smart TV (LC70LE640U)</a> $1,999.99</p>
<p>&nbsp;</p>
<h4>Top Deals:</h4>
<h4>Television &lt; 32”</h4>
<ul>
<li>Wal-Mart: Orion 24&#8243; LED 720p 60Hz HDTV (SLED2468W) $78.00</li>
<li>K-Mart: Proscan 32&#8243; LCD HDTV (LCD3283B) $97.00</li>
</ul>
<h4>Television &lt; 40”</h4>
<ul>
<li>Best Buy: <a href="http://bfads.net/Best-Buy-Black-Friday-Toshiba-40-1080p-60Hz-LCD-HDTV-Link">Toshiba 40&#8243; LCD 1080p 60Hz HDTV (40E220U)</a> $179.99</li>
<li>Wal-mart: Emerson 40&#8243; LCD 1080p 60Hz HDTV $198.00</li>
<li>Sears: Seiki 39&#8243; 1080p LCD HDTV (SC392TS) (Friday at 4AM) $199.99</li>
</ul>
<h4>Television &lt; 50”</h4>
<ul>
<li>K-Mart: <a href="http://bfads.net/Kmart-Black-Friday-RCA-42-Plasma-HDTV-Link">RCA 42&#8243; Plasma HDTV (42PA30RQ)</a> $199.99</li>
<li>Wal-Mart: Emerson 50&#8243; LCD 1080p 60Hz HDTV (LC501EM3) $298.00</li>
<li>Sears: <a href="http://bfads.net/Sears-Black-Friday-Panasonic-50-Viera-Plasma-HDTV-TCP50X5-Friday-at-4AM-Link">Panasonic 50&#8243; Viera Plasma HDTV (TC-P50X5) (Friday at 4AM)</a> $299.99</li>
</ul>
<h4>Television &gt; 50”</h4>
<ul>
<li>K-Mart: RCA 52&#8243; LCD 1080p HDTV $399.99</li>
<li>Best Buy: <a href="http://bfads.net/Best-Buy-Black-Friday-Insignia-55-1080p-120Hz-LCD-HDTV-Link">Insignia 55&#8243; LCD 1080p 120Hz HDTV (NS-55L260A13)</a> $599.99</li>
<li>Wal-Mart: <a href="http://bfads.net/Walmart-Black-Friday-Vizio-60-LED-1080p-120Hz-Smart-TV-w-Wifi-Link">Vizio 60&#8243; LED 1080p 120Hz HDTV (E601i-A3)</a> $688.00</li>
</ul>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-11-16.mp3">Download Episode #557</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>November 16, 2012 12:28 AM</b>
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
			<?=getComments(4935)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4935)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-557-black-friday-2012.php" type="text/javascript" charset="utf-8"></script>
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