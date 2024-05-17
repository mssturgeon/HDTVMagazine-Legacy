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
		AND e.entry_id = 4943";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4943 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4943 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4943";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-559-hdtv-buying-guide-2012.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4943";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012</title>
	<meta name="keywords" content="street price, lcd hdtv, led hdtv, hdtv street, last year, hdtv, inch, led, price, year, ”, lcd, get, full, last, street, want, plasma, picture, bit, quality, samsung, features, slim, great" />
	<meta name="description" content="As promised, we&amp;acirc;��re back for another annual edition of the HDTV buying guide and just in time for Christmas - we&amp;acirc;��d like to think it&amp;acirc;��s back by popular demand. In years past we&amp;acirc;��ve broken the categories down at times by size and at other times by price. Both are valid, sometimes as a shopper you want to maximize the budget you have to spend, other times you know the size you need and want to find the best set.  This year we&amp;acirc;��ll return to breaking them down by size." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-559-hdtv-buying-guide-2012.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As promised, we&amp;acirc;��re back for another annual edition of the HDTV buying guide and just in time for Christmas - we&amp;acirc;��d like to think it&amp;acirc;��s back by popular demand. In years past we&amp;acirc;��ve broken the categories down at times by size and at other times by price. Both are valid, sometimes as a shopper you want to maximize the budget you have to spend, other times you know the size you need and want to find the best set.  This year we&amp;acirc;��ll return to breaking them down by size." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4943', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-559-hdtv-buying-guide-2012.php">HDTV and Home Theater Podcast - Podcast #559: HDTV Buying Guide 2012</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>November 30, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
<h3><strong>HDTV Buying Guide 2012</strong></h3>
<p>As promised, we’re back for another annual edition of the HDTV buying guide and just in time for Christmas &#8211; we’d like to think it’s back by popular demand. In years past we’ve broken the categories down at times by size and at other times by price. Both are valid, sometimes as a shopper you want to maximize the budget you have to spend, other times you know the size you need and want to find the best set.  This year we’ll return to breaking them down by size.</p>
<p>Before we get going we want to recap last year’s list.  We think we can beat the prices this year and get you more bang for your buck. A few of the sets from last year’s list were:</p>
<ul>
<li>Magnavox 19” 720p LCD for $149</li>
<li>Sony BRAVIA KDL32BX320 32-Inch 720p LCD HDTV for $348</li>
<li>LG 50” 720p Plasma for $599</li>
<li>Westinghouse 60” 1080p LCD for $1219</li>
<li>Panasonic 65-Inch 1080p 3D Plasma for $2,349</li>
</ul>
<p>&nbsp;</p>
<h4>Up to 32&#8243;</h4>
<p>These screens are great for a secondary viewing room like your kitchen, bedroom or office.  At this size, we’re focused on value and bang for the buck.  Depending on your situation, a 32” TV could even work as your primary screen.  Remember the CRT days when a 32” TV was considered huge?</p>
<p><a href="http://www.amazon.com/gp/product/B0077E48TG/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0077E48TG&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Toshiba 19L4200U 19-Inch 720p 60Hz LED TV ($129)</strong></a><br />
Say goodbye to standard-definition with 720p HD resolution.  In the kitchen, bedroom or dorm room, Toshiba&#8217;s L4200U 720p LED HDTV, offers excellent small-screen HD quality, LED backlighting for bright, sharp images, great energy efficiency, and excellent audio. The 19L4200U has dynamic backlight control for deeper blacks and more detail.  It also has a USB media port so you can easily connect to your favorite tunes and photos, create slideshows, or listen to your personal playlists.</p>
<p><a href="http://www.amazon.com/gp/product/B00752RB0I/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00752RB0I&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Panasonic VIERA TC-L24X5 24-Inch 1080p Full HD LED LCD TV ($199)</strong></a><br />
We usually don’t recommend 1080p for screens this small, but for under $200, why not?  It makes a great HDTV and could double as a computer monitor if you needed it to. The TC-L24X5 is a 1080p LED LCD panel HDTV featuring one HDMI terminal, one USB, a PC input, and game mode.  It’s an all-around performer in a stylish design</p>
<p><a href="http://www.amazon.com/gp/product/B00856XDPK/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00856XDPK&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>RCA 32LB45RQ 32-Inch Full 1080p 60Hz LCD HDTV ($229)</strong></a><br />
RCA used to be a force in the television industry, could 2013 be a comeback year?  This set made the list for value, but the quality won’t upset you either. Even if it does, at this price it’s practically disposable. The 32LB45RQ features a high-definition 31.5&#8243; display, ATSC/NTSC tuner, and HDMI connectivity. With additional component and conventional AV input, as well as a USB port to play photos and music from your USB devices, this RCA HDTV makes it easy to connect you to all your favorite media.</p>
<h4>
Up to 42&#8243;</h4>
<p>This is the sweet spot for TV sales and where you can find some incredible deals.  Last year we had a 32 inch for about $350 and a 42 inch for about $500.  This year you can get a 37 inch TV for less than $400.</p>
<p><a href="http://www.amazon.com/gp/product/B0081UUYWA/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0081UUYWA&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Sceptre X408BV-FHD 39-Inch 1080p 60HZ LCD HDTV ($332)</strong></a><br />
Another value leader, Sceptre, is pushing hard to get you the best bang for your buck. This LCD HDTV features a sleek and slim look, built-in ATSC/NTSC/QAM tuners, full 1080p resolution and 90,000:1 dynamic contrast that automatically helps balance the images to look more vivid and lifelike. This TV comes with 3 HDMI ports that lets you play all your high play any high-definition device with the eases of a single cord and the USB port that helps further expand the functionality, allowing users to listen to music and view digital pictures quickly and conveniently.</p>
<p><a href="http://www.amazon.com/gp/product/B008KECFRO/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B008KECFRO&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Sony BRAVIA KDL42EX440 42-Inch 1080p HDTV ($548)</strong></a><br />
Sony still makes good TVs, maybe not quite as good as their prime in the Trinitron era, but still quite good. Introduce your home to the true beauty of Sony HD wiith Full HD 1080p image detail and bright LED backlighting, this slim-profile TV is ideal for action movies, sports and video games. Everything moves naturally, with Motionflow XR 120 technology that delivers clear, realistic sports and movie action.</p>
<p>&nbsp;</p>
<h4>Up to 50&#8243;</h4>
<p>Pricing in this category didn’t change as much as the smaller sizes, but it did get better. And when you couple that with the new and improved features, your dollar is still going quite a bit further today than it did just a year ago. This is the first category plasma technology shows up, and we still highly recommend it.</p>
<p><a href="http://www.amazon.com/gp/product/B009VRTEPU/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B009VRTEPU&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Samsung UN46ES6003 46-Inch 1080p 120Hz Slim LED HDTV ($697)</strong></a><br />
Samsung is the gorilla in HDTV these days and this set shows why. The Full HD 1080p resolution and Clear Motion Rate of 240 combine to deliver realistic action in every frame. Clear Motion Rate (CMR) was developed to accurately measure how well a LCD or LED TV can depict fast-moving images. LED TVs with a CMR of 240 can display action-packed movement with sharp detail and deeper levels of contrast while eliminating image distortion. Wide Color Enhancer Plus allows you to witness the entire RGB spectrum brought to life on your screen to bring you exceptionally vibrant, yet natural-looking images faithful to the director’s original intent.</p>
<p><a href="http://www.amazon.com/gp/product/B007EM7NES/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B007EM7NES&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>TCL LE48FHDF3300ZTA 48-Inch 1080p 240Hz LED HDTV ($499)</strong></a><br />
So we aren’t quite sure about the brand, but we are sure about the price. We’ll admit, we’re recommending this set sight unseen based solely on price and Amazon reviews. A 48” 1080p LED HDTV for less than $500 speaks for itself. They include a 2 year limited warranty, so that should help ease the uncertainty with the brand itself. You&#8217;ll enjoy crisp, vibrant images thanks to this TCL television&#8217;s 240 HZ refresh rate, 1080p picture resolution and high brightness capability. The 3-D Y/C digital comb filter also provides enhanced color and picture detail, and the 1920 x 1080 pixel resolution supports 16.7 million colors for stunning image clarity. If you have experience with TCL, let us know!</p>
<p>&nbsp;</p>
<h4>Greater than 50&#8243;</h4>
<p>These are the TVs everyone wants, the big ones you walk by in the store and drool over.  Drool no longer. At prices like these you won’t need to feel guilty about placing one for yourself under the tree.</p>
<p><a href="http://www.amazon.com/gp/product/B009VRTEP0/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B009VRTEP0&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Samsung UN55ES6003 55-Inch 1080p 120Hz Slim LED HDTV ($1247)</strong></a><br />
You get a great HDTV without any of the other frills getting in the way, just great picture and a great price. The 6003 series allows you to immerse yourself in a full HD 1080p viewing experience. With ConnectShare Movie you can watch videos, play music, or view photos directly from a USB drive. With two HDMI ports, easily connect multiple compatible AV devices at the same time. The impressive picture quality, excellent sound technology, and slim profile design will transform how you watch TV. If you want apps, you can always add an Apple TV or Roku later.</p>
<p><a href="http://www.amazon.com/gp/product/B009H8JOZS/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B009H8JOZS&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>VIZIO E601i-A3 60-Inch 1080p 120Hz Razor LED Smart HDTV ($999)</strong></a><br />
It’s official, 60” HDTVs have dropped below $1000, and this one also has all the bells and whistles. Not the name brand of a Samsung, so you get more for your money. The E-Series 60” has 1080p Full HD resolution with 120Hz refresh rate with smooth motion. It features an ultra thin profile and VIZIO Internet Apps with built-in Wi-Fi gives you instant access to a world of streaming movies, TV shows, music, and more – all at the push of a button on the smart remote with keyboard.</p>
<p><a href="http://www.amazon.com/gp/product/B00752VKFA/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00752VKFA&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Panasonic VIERA TC-P65GT50 65-Inch 1080p 600 Hz Full HD 3D Plasma TV ($2499)</strong></a><br />
If you’re going to go with a 65” TV and can’t quite swing the price of the VT50, consider the GT50. Every bit as good to the casual viewer, the GT50 offers an excellent compromise between price and performance. This set actually went up in price a bit, sort of. It is a bit more expensive than the GT30 was last year. The GT50 series features Full HD 3D, Infinite Black Pro Panel, and VIERA Connect with web browser and built-in Wi-Fi. Other unique features include 24,576 shades of gradation, 2500 focused field drive, and it is THX 3D Certified, reproducing 2D &amp; 3D images with cinema-like quality.</p>
<p>&nbsp;</p>
<h4>HT Guy&#8217;s Ultimate Christmas Present:</h4>
<p>Last year we showcased the <a href="http://www.amazon.com/gp/product/B004NPND20?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004NPND20&amp;ref_=sr_1_1&amp;qid=1322753977&amp;sr=8-1">Panasonic VT30 65” Plasma at $3,000</a> as one of our ultimate presents. The price this year has dropped to <a href="http://www.amazon.com/gp/product/B004NPND20?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004NPND20&amp;ref_=sr_1_1&amp;qid=1322753977&amp;sr=8-1">$2,899</a>. We also liked the <a href="http://www.amazon.com/gp/product/B005LYRYNG?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B005LYRYNG&amp;ref_=sr_1_1&amp;s=electronics&amp;qid=1322754328&amp;sr=1-1">Sharp AQUOS 80&#8243; LED for $4,430</a>. If you didn’t get it last year, you can now for only <a href="http://www.amazon.com/gp/product/B005LYRYNG?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B005LYRYNG&amp;ref_=sr_1_1&amp;s=electronics&amp;qid=1322754328&amp;sr=1-1">$3,889</a>. The last TV on the ultimate list last year was the massive <a href="http://www.amazon.com/gp/product/B004ZL2O9U?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B004ZL2O9U&amp;ref_=sr_1_5&amp;s=electronics&amp;qid=1322754688&amp;sr=1-5">Mitsubishi 92” 1080p Projection TV for $4,680</a>. This TV has dropped almost half its price in just one year &#8211; you can buy it now for only <a href="http://www.amazon.com/gp/product/B004ZL2O9U?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B004ZL2O9U&amp;ref_=sr_1_5&amp;s=electronics&amp;qid=1322754688&amp;sr=1-5">$2,778</a>. But this year we’re going with the ultimate in picture quality, the Elite LED.</p>
<p><a href="http://www.amazon.com/gp/product/B005MYZXS8/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005MYZXS8&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Elite Black 60&#8243; Pro-60x5fd Flat Panel 3d LED HDTV ($4599)</strong></a><br />
Yes, we know there is a <a href="http://www.amazon.com/gp/product/B005MYZ4MS/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005MYZ4MS&amp;linkCode=as2&amp;tag=hdtvandhometh-20">70” model</a>, but at close to $8000, it was a bit excessive &#8211; even for us. This screen has the best picture we’ve ever seen. It isn’t the best picture “by a mile” nor is it the best picture “hands down,” but it is an impressive screen.  If you want a TV that rivals the quality as close as we’ve ever found for a bit less money, you want to get the <a href="http://www.amazon.com/gp/product/B00752VLSG/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00752VLSG&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Panasonic VIERA TC-P65VT50 65-Inch 3D Plasma TV for $3,699</a>.</p>
<p><a href="http://www.amazon.com/gp/product/B0074FGZYO/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0074FGZYO&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Samsung UN75ES9000 75-Inch 1080p 240Hz 3D Slim LED HDTV, Gold ($8997)</a><br />
If you want to go with the most excessive TV possible, this is it. It even comes in a gold tone bezel. So yeah, if you want a solid gold TV, this is it. But it’s not just a gold frame, it has every possible feature you could ever imagine, and a few you probably have never heard or thought of. If you get this for Christmas, rejoice that someone loves you that much. If it was us, we’d return it for a 65” Panasonic VT50, and a new receiver, and new speakers, and a couple new tablets&#8230;</p>
<p>&nbsp;</p>
<h4>Last year:</h4>
<p><strong>Less than $500</strong></p>
<ul>
<li><a href="http://www.htguys.com/shop?id=B005450YTS">Magnavox 19ME601B/F7 19-Inch 720p LCD TV (Street Price $149)</a></li>
<li><a href="http://www.amazon.com/gp/product/B004HYG9SM?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004HYG9SM&amp;ref_=sr_1_1&amp;qid=1322752970&amp;sr=8-1">Sony BRAVIA KDL32BX320 32-Inch 720p LCD HDTV (Street Price $348)</a></li>
<li><a href="http://www.htguys.com/shop?id=B005JK01GO">LG 42LV4400 42-Inch 1080p 120Hz LED-LCD HDTV (Street Price $499)</a></li>
</ul>
<p><strong>$500 &#8211; $1000</strong></p>
<ul>
<li><a href="http://www.htguys.com/shop?id=B004LACPFS">LG 50PT350 50-Inch 720p 600Hz Plasma HDTV (Street Price $599)</a></li>
<li><a href="http://www.htguys.com/shop?id=B005VOL9MI">Samsung UN46D6003 46-Inch 1080p LED HDTV &#8211; Black (Street Price $799)</a></li>
</ul>
<p><strong>Greater than $1000</strong></p>
<ul>
<li><a href="http://www.htguys.com/shop?id=B004VD2LHC">Westinghouse VR-6025Z 60-Inch 1080p 120 Hz LCD HDTV (Street Price $1219)</a></li>
<li><a href="http://www.htguys.com/shop?id=B005DRBDY2">LG 55LW5300 55-Inch 1080p 120Hz Cinema 3D LED-LCD HDTV with 3D Blu-ray Player and Four Pairs of 3D Glasses (Street Price $1369)</a></li>
<li><a href="http://www.amazon.com/gp/product/B004MME75Q?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004MME75Q&amp;ref_=sr_1_1&amp;qid=1322753211&amp;sr=8-1">Panasonic VIERA TC-P65GT30 65-Inch 1080p 3D Plasma HDTV (Street Price $2,349)</a></li>
</ul>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-11-30.mp3">Download Episode #559</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>November 30, 2012 12:40 AM</b>
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
			<?=getComments(4943)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4943)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/11/hdtv-and-home-theater-podcast-podcast-559-hdtv-buying-guide-2012.php" type="text/javascript" charset="utf-8"></script>
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