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
		AND e.entry_id = 4634";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4634 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4634 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4634";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/01/hdtv-and-home-theater-podcast-podcast-513-ces-2012-part-i.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4634";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I</title>
	<meta name="keywords" content="hdmi dongle, “crystal led, super oled, digital plus, next generation, ces, hdmi, led, video, primetime, dolby, connected, devices, super, samsung, features, simple, hdtv, home, announced, dongle, ”, new, tvs, smart" />
	<meta name="description" content="It&amp;#039;s that time of the year again, time to pack up and head out to Vegas, baby, Vegas for another Consumer Electronics Show. CES always has something fun and memorable in store for us, and we&amp;#039;re sure 2012 won&amp;#039;t be any different. We haven&amp;#039;t actually left yet, so for this show we&amp;#039;re telling you about what we&amp;#039;ve heard.  Next week, on Part II, we&amp;#039;ll tell you about what we&amp;#039;ve seen." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/01/hdtv-and-home-theater-podcast-podcast-513-ces-2012-part-i.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It&amp;#039;s that time of the year again, time to pack up and head out to Vegas, baby, Vegas for another Consumer Electronics Show. CES always has something fun and memorable in store for us, and we&amp;#039;re sure 2012 won&amp;#039;t be any different. We haven&amp;#039;t actually left yet, so for this show we&amp;#039;re telling you about what we&amp;#039;ve heard.  Next week, on Part II, we&amp;#039;ll tell you about what we&amp;#039;ve seen." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4634', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/01/hdtv-and-home-theater-podcast-podcast-513-ces-2012-part-i.php">HDTV and Home Theater Podcast - Podcast #513: CES 2012: Part I</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>January 12, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
<h3 id="internal-source-marker_0.8118190915561351">CES 2012: Part I</h3>
<p><a id="internal-source-marker_0.034750774092508485" href="http://www.htguys.com/news/2012/1/10/ces-2012-panasonic-hdtv-lineup-for-2012-announced.html">CES 2012: Panasonic HDTV Lineup for 2012 Announced</a></p>
<ul>
<li>17 new models, ranging in screen sizes from 42-inches to 65 inches, Panasonic expanded its 3D line-up for 2012 (including 3 passive 3D LED LCDs 42”/47”/55”)</li>
<li>Also new for 2012 is a cloud-based architecture to increase the VIERA Connect IPTV platform to an unlimited number of apps</li>
<li>The six VIERA Plasma series, VT50, GT50, ST50, UT50, XT50, U50, feature self illuminating panels with ultimate black levels, NeoPlasma technologies(VT/GT/ST) providing a black filter with a higher efficiency panel that generates the best balance of black and white under brighter environments.</li>
<li>Infinite Black Ultra Panel</li>
</ul>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-dolby-digital-plus-on-more-devices-for-2012.html">CES 2012: Dolby Digital Plus on more devices for 2012</a></p>
<ul>
<li>Dolby is announcing that HBO Go® will adopt Dolby Digital Plus to deliver content to connected TVs and Blu-ray™ players.</li>
<li>Samsung™ will use Dolby Digital Plus to support its Acetrax™ application for Smart Blu-ray players and Smart Blu-ray home theater systems in Europe.</li>
<li>Portable devices with Dolby technologies—including tablets from Acer®, Samsung, and ZTE®, and Ultrabook™ devices from Acer, HP®, and Toshiba®—are shipping into major markets. For a higher-quality movie experience on iPad®, iPhone®, and iPod® devices, Dolby is showing the first mobile application with Dolby Digital Plus, the CineXPlayer video player from NXP®.</li>
</ul>
<p>&nbsp;<br />
<a href="http://www.htguys.com/news/2012/1/10/ces-2012-sony-develops-next-generation-crystal-led-display.html">CES 2012: Sony Develops Next-generation “Crystal LED Display”</a></p>
<ul>
<li>Sony announced that it has developed the next-generation self-emitting display, “Crystal LED Display,”</li>
<li>The “Crystal LED Display” is a self-emitting display that uses Sony’s unique methods to mount ultrafine LEDs in each of the Red-Green-Blue (RGB) colors, equivalent to the number of pixels (approximately six-million LEDs for Full HD).</li>
<li>Compared to existing LCD displays, the 55-inch prototype exhibited at CES is boasting approximately 3.5 times*1 higher contrast in light environment, approximately 1.4 times wider color gamut, and approximately 10 times faster video image response time (all values based on current Sony models). Sony envisages a wide range of applications for its “Crystal LED Display”, ranging from professional to consumer use.</li>
</ul>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-homeplug-powerline-alliance-announces-av2-specifica.html">CES 2012: HomePlug Powerline Alliance Announces AV2 Specification for Next-Generation Broadband Speeds over Powerline Wires</a></p>
<ul>
<li>The HomePlug® Powerline Alliance announced the availability of the HomePlug AV2 specification. This new HomePlug technology enables significant performance and coverage for broadband networking over powerline wires while remaining fully interoperable with existing HomePlug AV / IEEE 1901 compliant products used in millions of consumers&#8217; homes and widely available worldwide from service providers and retail stores.</li>
<li>Gigabit-class PHY Rate (physical interface rate)</li>
<li>Whole home coverage with inherent repeater functionality</li>
</ul>
<p>&nbsp;<br />
<a href="http://www.htguys.com/news/2012/1/10/ces-2012-iogear-announces-4-port-hdmi-switcher-that-converts.html">CES 2012: IOGEAR Announces 4-Port HDMI Switcher that converts 2D to 3D</a></p>
<ul>
<li>IOGEAR announced the 4-Port Super Switcher with Advanced 2D/3D Video Processor (GHDSSW4), which allows the connection of four HDMI source devices to the HDMI input of a 3D HDTV or 3D-ready HD video projector.</li>
<li>The GHDSSW4 also converts all of your flat DVDs, HD videos, and photos to 3D, with user controls that allow you to adjust the pop-out and depth effects for the optimal three-dimensional experience.</li>
<li>The IOGEAR 4-Port Super Switcher with Advanced 2D/3D Video Processor (GHDSSW4) will be available April 2012 for a suggested retail price of $229.95.</li>
</ul>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-always-innovating-innovates-again-with-the-hdmi-don.html">CES 2012: Always Innovating Innovates again with the HDMI Dongle. </a></p>
<ul>
<li>Always Innovating announced the HDMI Dongle, a portable set-top box. The HDMI Dongle is a device of the size of a USB stick that can be plugged into any HDMI port to transform a dumb TV into a smart Internet-connected screen. The HDMI Dongle enables Internet browsing, movie watching and games.</li>
<li>The HDMI Dongle can stream and decode from the Internet 1080p H.264 video.</li>
<li>The device is compatible with popular services such as Netflix, Hulu or Amazon video-on-demand.</li>
<li>The user interface is controlled with a 9-button remote control for easy navigation, and voice recognition for text input. The accelerometer located in the remote control enables a set of gravity-based games. The remote control also features a NFC chip to offer a tap-to-share experience.</li>
</ul>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-dish-introduces-next-generation-whole-home-dvr-ente.html">CES 2012: Dish Introduces Next Generation Whole Home DVR Entertainment System</a></p>
<ul>
<li>Features two-terabyte hard drive for up to 2,000 hours of entertainment</li>
<li>Never miss a primetime network show with PrimeTime Anytime. PrimeTime Anytime™ allows customers, with one click, to record using a single tuner all of the primetime TV programming from ABC, CBS, FOX and NBC – the networks that deliver the most popular shows during primetime. Once activated by a customer, PrimeTime Anytime records network programming in high definition, where available, every night and stores them for eight days after they have aired.</li>
<li>Records up to six programs in HD simultaneously, including PrimeTime Anytime</li>
<li>Hopper and three small Joeys let viewers watch HD and control DVR in four rooms</li>
</ul>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-sharp-2012-led-lineup.html">CES 2012: Sharp 2012 LED Lineup</a></p>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-lg-2012-hdtv-line-up-announced.html">CES 2012: LG 2012 HDTV Line Up Announced</a></p>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-toshiba-2012-led-hdtv-lineup.html">CES 2012: Toshiba 2012 LED HDTV Lineup</a><br />
(“Toshiba is at CES showing off a TV line that adds more new Internet TV features, including full Web browsing and support for Vudu apps, and it’s dropping active 3D by moving all its 3D models to passive technology.”)<br />
<a href="http://www.htguys.com/news/2012/1/10/ces-2012-samsung-unveils-the-super-oled-tv-the-ultimate-in-p.html">CES 2012: Samsung Unveils the Super OLED TV – The Ultimate in Picture Quality</a></p>
<ul>
<li>It features unmatched vivid and true-to-life picture quality in both 2D and 3D, with significantly improved color accuracy compared to conventional LED TVs. Since light output on the Super OLED is controlled on a pixel-to-pixel basis, the truest blacks and purest whites can be achieved.</li>
<li>Further, the Samsung Super OLED offers faster response times than LED, virtually eliminating motion blur even in the fastest-moving scenes.</li>
<li>Because Super OLED technology features self-emitting RGB sub-pixels which do not require a backlight, the TV weighs significantly less than a standard LED TV.</li>
<li>When powered on, a bright, vibrant picture illuminates the screen from edge to edge.</li>
</ul>
<p>&nbsp;<br />
<a href="http://www.htguys.com/news/2012/1/11/ces-2012-simpletv-launches-dvr-for-the-connected-tv-world.html">CES 2012: Simple.TV Launches DVR for the Connected TV World</a></p>
<ul>
<li>The Simple.TV DVR ($149) consists of a high-definition TV tuner that converts broadcast television into streaming MPEG-4 for the most popular connected devices. With the addition of a USB 2.0 hard drive or network-attached storage, Simple.TV users can store thousands of hours of their favorite TV shows and HDTV videos and watch them on their mobile devices or connected TVs.</li>
<li>Unlike most DVRs, Simple.TV has no traditional analog or HDMI video outputs. Once connected to a home network, Simple.TV streams content directly to a web browser or to dedicated applications that users launch on their favorite connected platforms. For users who want expanded features, Simple.TV offers a Premier Service subscription ($4.99 per month) that adds an electronic program guide, automatic TV series recording, in-depth information on content, and unlimited remote streaming for up to five users.</li>
</ul>
<p>&nbsp;<br />
<a href="http://www.htguys.com/news/2012/1/11/ces-2012-vizio-takes-home-entertainment-beyond-tv-with-new-b.html">CES 2012: VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories</a></p>
<p><a href="http://www.htguys.com/news/2012/1/10/ces-2012-samsung-2012-smart-tvs-get-boxless-support-for-dire.html">CES 2012: Samsung 2012 Smart TVs get Boxless support for DirecTV</a></p>
<ul>
<li>DIRECTV and Samsung Electronics Co, Ltd. unveiled their plans to offer DIRECTV&#8217;s more than 19.7 million customers the ability to watch live broadcast and stored content from a compatible DIRECTV DVR on Samsung&#8217;s 2012 line of Smart TVs without the need for additional set-top boxes.</li>
</ul>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-01-13.mp3">Download Episode #513</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>January 12, 2012 10:49 PM</b>
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
			<?=getComments(4634)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4634)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/01/hdtv-and-home-theater-podcast-podcast-513-ces-2012-part-i.php" type="text/javascript" charset="utf-8"></script>
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