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
		AND e.entry_id = 3688";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3688 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3688 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3688";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2010/04/hdtv-and-home-theater-podcast-podcast-423-home-theater-market-trends.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3688";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends</title>
	<meta name="keywords" content="stars customer, customer reviews, reviews price, blu ray, ray disc, price, stars, customer, reviews, blu, ray, player, receiver, disc, inch, black, hdtv, channel, lcd, samsung, sony, hdmi, top, theater, list" />
	<meta name="description" content="Every so often we like to take a look at the top sellers at Amazon.com in various Home Theater categories.  We use this information as a thermometer to roughly gauge the market as a whole.  At least that's what our crack market research team tells us we can do.  There are always some interesting tidbits of information that emerge." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2010/04/hdtv-and-home-theater-podcast-podcast-423-home-theater-market-trends.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Every so often we like to take a look at the top sellers at Amazon.com in various Home Theater categories.  We use this information as a thermometer to roughly gauge the market as a whole.  At least that's what our crack market research team tells us we can do.  There are always some interesting tidbits of information that emerge." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3688', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2010/04/hdtv-and-home-theater-podcast-podcast-423-home-theater-market-trends.php">HDTV and Home Theater Podcast - Podcast #423: Home Theater Market Trends</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>April 29, 2010</b>
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
<h3>Top Sellers at Amazon.com</h3>
<p>Every so often we like to take a look at the top sellers at <a href="http://www.amazon.com/?%5Fencoding=UTF8&amp;tag=hdtvandhometh-20" target="_blank">Amazon.com</a> in various Home Theater categories.  We  use this information as a thermometer to roughly gauge the market as a whole.  At least that&#8217;s what our crack market research team tells us we  can do.  There are always some interesting tidbits of information that  emerge.</p>
<h4>TVs</h4>
<ol>
<li><a href="http://www.htguys.com/shop?id=B0028QDO0M" target="_blank">Sharp  LC19SB27UT 19-Inch 720p LCD HDTV, Black</a>
<ul>
<li>4.4 out of 5 stars (25 customer reviews)</li>
<li>Price: $167.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B001DZJVO2" target="_blank">Toshiba  15LV505 15.6-Inch Widescreen LCD TV with Built-in DVD Player (Black)</a>
<ul>
<li>4.4 out of 5 stars (282 customer reviews)</li>
<li>Price: $176.72</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0036WT3P2" target="_blank">Samsung  LN40C630 40-Inch 1080p 120 Hz LCD HDTV (Black) </a>
<ul>
<li>4.8 out of 5 stars (19 customer reviews)</li>
<li>Price: $809.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B002KLHPU0" target="_blank">Hitachi  L19D103 19-Inch 720p LCD Flat Panel HDTV/DVD Combo, Black</a>
<ul>
<li>4.6 out of 5 stars (10 customer reviews)</li>
<li>Price: $189.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0036WT3RA" target="_blank">Samsung  LN46C630 46-Inch 1080p 120 Hz LCD HDTV (Black)</a>
<ul>
<li>3.8 out of 5 stars (16 customer reviews)</li>
<li>Price: $989.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0028YB8MA" target="_blank">Samsung  UN55B8000 55-Inch 1080p 240 Hz LED HDTV</a>
<ul>
<li>4.2 out of 5 stars (134 customer reviews)</li>
<li>Price: $2,199.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B002YT8CKA" target="_blank">Proscan  32LB45Q 32-Inch 1080p LCD HDTV, Black</a>
<ul>
<li>3.5 out of 5 stars (28 customer reviews)</li>
<li>Price: $348.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B002647HF4" target="_blank">Sharp  LC22DV27UT 22-Inch LCD HDTV with Built-In DVD Player, Black</a>
<ul>
<li>4.4 out of 5 stars (29 customer reviews)</li>
<li>Price: $289.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B001KLEUOA" target="_blank">ViewSonic  VT2430 24-Inch 1080p LCD HDTV</a>
<ul>
<li>4.0 out of 5 stars (68 customer reviews)</li>
<li>Price: $248.21</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B003DZ0ECS" target="_blank">Sony  BRAVIA S-Series KDL-32S5100/9 Professional 32-Inch 1080p LCD HDTV,  Black</a>
<ul>
<li>Only 13 days in the top 100</li>
<li>Price: $479.99</li>
</ul>
</li>
</ol>
<p><strong>Interesting data points:</strong></p>
<ul>
<li>All 10 TVs are LCD
<ul>
<li>Plasma doesn&#8217;t show up on the list until #16 (and it&#8217;s a Samsung,  not a Panasonic)</li>
</ul>
</li>
<li>40% are still 720p TVs, but that&#8217;s probably because&#8230;</li>
<li>70% of the TVs are less than 40&#8243; screens. Five are less than 30  inches.</li>
<li>2 of them are 120 Hz, only 1 is a 240 Hz model</li>
<li>There are no 3D TVs on the list
<ul>
<li>The first 3D TV appears at #12, so they&#8217;ll start to make the top 10  very soon</li>
</ul>
</li>
<li>Samsung might be slipping, only 3 of the top 10 this time</li>
</ul>
<h4>Receivers</h4>
<ol>
<li><a href="http://www.htguys.com/shop?id=B001TP3CH8" target="_blank">Sony  STR-DH100 2-Channel Audio Receiver</a>
<ul>
<li>4.3 out of 5 stars (41 customer reviews)</li>
<li>Price: $127.45</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B003BIFOL8" target="_blank">Onkyo  TX-SR608 100-Watt 7.2-Channel A/V Home Theater Receiver</a>
<ul>
<li>3-D ready HDMI 1.4</li>
<li>Price: $499.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B001YQ9T8I" target="_blank">Sony  STR-DH800 7.1-Channel Audio Video Receiver</a>
<ul>
<li>4.4 out of 5 stars (77 customer reviews)</li>
<li>HDMI 1.3</li>
<li>Price: $212.23</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0038W0J0S" target="_blank">Sony  STR-DH810 7.1-channel Blu-ray Disc A/V Receiver with 7 HD Inputs</a>
<ul>
<li>5.0 out of 5 stars (2 customer reviews)</li>
<li>HDMI 1.3</li>
<li>Price: $285.35</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B001AMSPQI" target="_blank">Onkyo  TX-8255 Stereo Receiver</a>
<ul>
<li>4.7 out of 5 stars (51 customer reviews)</li>
<li>Price: $169.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B003BEDQQW" target="_blank">Onkyo  TX-SR508 7.1-Channel Home Theater Receiver</a>
<ul>
<li>3-D ready HDMI 1.4</li>
<li>Price: $349.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B003BEDQQM" target="_blank">Onkyo  TX-SR308 5.1-Channel Home Theater Receiver</a>
<ul>
<li>3-D ready HDMI 1.4</li>
<li>Price: $229.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0039XQL2G" target="_blank">Pioneer  VSX-820-K Audio Video Receiver</a>
<ul>
<li>Available for Pre-order</li>
<li>3-D ready HDMI 1.4</li>
<li>Price: $299.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B001S2RDAY" target="_blank">Yamaha  HTR-6240BL 525-Watt 5-Channel Home Theater Receiver</a>
<ul>
<li>4.0 out of 5 stars (51 customer reviews)</li>
<li>HDMI 1.3</li>
<li>Price: $237.29</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0038W0IZO" target="_blank">Sony  STR-DH510 5.1-channel High-Definition AV Receiver</a>
<ul>
<li>3.0 out of 5 stars (1 customer review)</li>
<li>Price: $189.72</li>
</ul>
</li>
</ol>
<p><strong>Interesting data points:</strong></p>
<ul>
<li>SONY and Onkyo Rule this list</li>
<li>The most expensive receiver costs $500</li>
<li>7.1 systems are slowly becoming the norm</li>
<li>All of the Onkyo multi-channel units (3 total) and the Pioneer  (pre-order) support 3-D ready HDMI 1.4</li>
<li>There are actually two 2-channel receivers on the list.</li>
</ul>
<h4>Blu-ray Players</h4>
<ol>
<li><a href="http://www.htguys.com/shop?id=B001URWAYG" target="_blank">Sony  BDP-S360 1080p Blu-ray Disc Player</a>
<ul>
<li>4.0 out of 5 stars (226 customer reviews)</li>
<li>Price: $129.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B001VZ1W5U" target="_blank">Panasonic  DMP-BD70V Blu-ray Disc/VHS Multimedia Player</a>
<ul>
<li>4.3 out of 5 stars (89 customer reviews)</li>
<li>Price: $139.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B000B60H0G" target="_blank">Samsung  BD-C5500 1080p Blu-ray Disc Player</a>
<ul>
<li>3.5 out of 5 stars (32 customer reviews)</li>
<li>Price: $161.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B002PHM0XQ" target="_blank">Sony  BDP-N460 Blu-ray Disc Player (Black)</a>
<ul>
<li>4.3 out of 5 stars (201 customer reviews)</li>
<li>Price: $159.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0038KR8WM" target="_blank">Panasonic  DMP-BD85K WiFi Enabled Blu-Ray Disc Player</a>
<ul>
<li>4.4 out of 5 stars</li>
<li>Price: $244.45</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B00365EVW4" target="_blank">Samsung  BD-C6500 1080p Blu-ray Disc Player</a>
<ul>
<li>3.5 out of 5 stars (52 customer reviews)</li>
<li>Price: $224.00</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B00365EVWO" target="_blank">Samsung  BD-C6900 1080p 3D Blu-ray Disc Player</a>
<ul>
<li>3.4 out of 5 stars (12 customer reviews)</li>
<li>Price: $399.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0038KN114" target="_blank">Panasonic  DMP-BD65 Blu-Ray Disc Player (Black)</a>
<ul>
<li>3.9 out of 5 stars (17 customer reviews)</li>
<li>Price: $150.89</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B002EEP3MK" target="_blank">OPPO  BDP-83 Blu-ray Disc Player with SACD, DVD-Audio, and VRS Technology</a>
<ul>
<li>4.8 out of 5 stars (235 customer reviews)</li>
<li>Price: $499.99</li>
</ul>
</li>
<li><a href="http://www.htguys.com/shop?id=B0036WT1WC" target="_blank">LG  BD550 Blu-Ray Disc Player</a>
<ul>
<li>4.1 out of 5 stars (14 customer reviews)</li>
<li>Price: $152.95</li>
</ul>
</li>
</ol>
<p><strong>Interesting data points:</strong></p>
<ul>
<li>Oppo BDP-83 is the most expensive unit on the list and its rated 4.8  stars by 235 users</li>
<li>The top ten consists of 5 different manufacturers</li>
<li>Samsung has the most units on the list with three</li>
<li>Eight of the top ten offer some sort of streaming service (Netflix,  Amazon, Blockbuster, etc)</li>
<li>There is, in fact, one 3D Blu-ray player on the list</li>
<li>The Sony BDP-S360 has been in the top 100 for 388 days, despite the  lack of streaming service.</li>
</ul>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2010-04-30.mp3">Download  Episode #423</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>April 29, 2010 10:55 PM</b>
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
			<?=getComments(3688)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3688)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2010/04/hdtv-and-home-theater-podcast-podcast-423-home-theater-market-trends.php" type="text/javascript" charset="utf-8"></script>
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