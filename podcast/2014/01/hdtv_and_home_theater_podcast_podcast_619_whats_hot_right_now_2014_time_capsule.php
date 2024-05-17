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
		AND e.entry_id = 5187";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5187 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5187 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5187";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-619-whats-hot-right-now-2014-time-capsule.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5187";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #619: What&rsquo;s Hot Right Now (2014 Time Capsule)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #619: What&rsquo;s Hot Right Now (2014 Time Capsule)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #619: What&rsquo;s Hot Right Now (2014 Time Capsule)" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #619: What&rsquo;s Hot Right Now (2014 Time Capsule)</title>
	<meta name="keywords" content="streaming player, led hdtv, time capsule, media player, streaming media, list, inch, samsung, hdtv, ”, led, tvs, streaming, player, time, top, capsule, plasma, roku, lcd, oled, smart, our, media, sets" />
	<meta name="description" content="Today&amp;amp;#8217;s Show: What&amp;acirc;��s Hot Right Now (2014 Time Capsule) We didn&amp;acirc;��t update it in 2013, but back in January of 2012, we put together a snapshot of the then-current HDTV landscape we could refer back to in the future. Since we often look back at the technologies and prices of years gone by with shock [&amp;amp;#8230;]" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #619: What&amp;rsquo;s Hot Right Now (2014 Time Capsule)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #619: What&amp;rsquo;s Hot Right Now (2014 Time Capsule)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-619-whats-hot-right-now-2014-time-capsule.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Today&amp;amp;#8217;s Show: What&amp;acirc;��s Hot Right Now (2014 Time Capsule) We didn&amp;acirc;��t update it in 2013, but back in January of 2012, we put together a snapshot of the then-current HDTV landscape we could refer back to in the future. Since we often look back at the technologies and prices of years gone by with shock [&amp;amp;#8230;]" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5187', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-619-whats-hot-right-now-2014-time-capsule.php">HDTV and Home Theater Podcast - Podcast #619: What&rsquo;s Hot Right Now (2014 Time Capsule)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>January 23, 2014</b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<h2>Today&#8217;s Show:</h2>
<h3>What’s Hot Right Now (2014 Time Capsule)</h3>
<p dir="ltr">We didn’t update it in 2013, but back in January of 2012, we put together a snapshot of the then-current HDTV landscape we could refer back to in the future. Since we often look back at the technologies and prices of years gone by with shock and amazement, instead of simply trying to remember what things were like “back then” we built our time capsule.</p>
<p>We’re going to update the time capsule and look back at our 2012 snapshot.  As we did last time, we took some of the top sellers at Amazon across a variety of categories and got their prices.  This gives us a good indication of what is hot right now and also what their prices are.</p>
<h4>Televisions</h4>
<h4>2012: 3D TVs</h4>
<p dir="ltr">In 2012 3D TVs were all the rage. Every store and manufacturer had them, and nobody really wanted them, although the industry hadn’t really figured that out yet. We had a whole section in our 2012 capsule devoted to 3D TVs.  In 2014 we’d drop the 3D TV section and replace it with a 4K TV section instead.</p>
<p dir="ltr"><strong>LG Infinia 55LW5600 55-Inch Cinema 3D 1080p 120 Hz LED-LCD</strong>, (was: <a href="http://www.amazon.com/LG-Infinia-55LW5600-55-Inch-LED-LCD/dp/B004OOVIHW/ref=sr_1_1?ie=UTF8&amp;qid=1390458183&amp;sr=8-1&amp;keywords=B004OOVIHW">$1282</a>) Ranked #4 on the list. The 47” version of the same TV, the LG Infinia 47LW6500 47-Inch Cinema 3D 1080p 240 Hz LED-LCD HDTV, (was: <a href="http://www.amazon.com/LG-Infinia-47LW6500-47-Inch-LED-LCD/dp/B004OVEVO2/ref=sr_1_1?ie=UTF8&amp;qid=1390458235&amp;sr=8-1&amp;keywords=B004OVEVO2">$1077</a>), was a few spots down the list at #12.</p>
<p dir="ltr"><em>Equivalent TV today: LG 55LA6200 55-Inch Cinema 3D 1080p 120Hz LED-LCD HDTV with Smart TV and Four Pairs of 3D Glasses, <a href="http://www.amazon.com/dp/B00BB9OOII/?tag=hdtvandhometh-20">$999</a></em></p>
<p dir="ltr"><strong>Samsung UN55D8000 55-Inch 1080p 240Hz 3D LED HDTV</strong>, (was: <a href="http://www.amazon.com/Samsung-UN55D8000-55-Inch-1080p-Silver/dp/B004N866SU/ref=sr_1_1?ie=UTF8&amp;qid=1390458283&amp;sr=8-1&amp;keywords=B004N866SU">$2058</a>) Samsung held the next TV on the list at #11.</p>
<p dir="ltr"><em>Equivalent TV today: Samsung UN55F7100 55-Inch 1080p 240Hz 3D Ultra Slim Smart LED HDTV, <a href="http://www.amazon.com/dp/B00BCGROJG/?tag=hdtvandhometh-20">$1498</a></em></p>
<p dir="ltr">We found 34 3D TVs in the top 100 made by LG, Samsung, Sony, Toshiba, and Sharp. There were 27 Active sets and 7 Passive (LG, Toshiba).  They ranged in size from 40” to 70” and ranged in price from $699 to over $3400.</p>
<p dir="ltr">
<h4>2014: 4K TVs</h4>
<p dir="ltr"><strong>Seiki Digital SE39UY04 39-Inch 4K Ultra HD 120Hz LED TV</strong>, <a href="http://www.amazon.com/gp/product/B00DOPGO2G/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00DOPGO2G&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$599</a>, The first TV on the list, at position #42, is the 39” 3840 x 2160 Panel from Seiki that also supports 4K up-scaling.</p>
<p dir="ltr">Despite what we thought we’d be able to load into the time capsule, there was only one 4K / Ultra high def TV in the top 100. This is something we’ll probably look back on in a few years in amazement, but 1080p still dominates the list, with a few 720p sprinkled throughout.</p>
<p>&nbsp;</p>
<h4>2012: Plasma TVs</h4>
<p dir="ltr">Two years ago plasma technology was showing signs of waning, but it was still going strong and winning awards, if not the hearts and minds of the average consumer. These days plasma is all but gone, so we’d probably replace plasma in our 2014 time capsule with OLED.</p>
<p dir="ltr"><strong>Panasonic VIERA TC-P50S30 50-Inch 1080p Plasma HDTV</strong>, (was: $799) The first plasma in the top 100 came in at #22 overall. Another slightly bigger Panasonic was on the list at #30, the<strong>Panasonic VIERA TC-P60ST30 60-Inch 1080p 600 Hz 3D Plasma HDTV</strong>, (was: $1400).</p>
<p>&nbsp;</p>
<p dir="ltr"><strong>Samsung PN43D450 43-Inch 720p 600 Hz Plasma HDTV</strong>, (was: $492) Again moving down a few spots to #35 we found our first non-Panasonic plasma set.</p>
<p dir="ltr">Overall we found 14 plasma TVs in the top 100 in 2012, not a bad showing.  They were mostly made by Panasonic and Samsung, with one LG unit in the list.  They ranged in size from 43” to 65” and ranged in price from under $500 to over $2500.</p>
<p dir="ltr">In 2014 we found just 5 of them in the top 100: 1 LG, 2 Samsung and 2 Panasonic plasmas. They ranged in size from 51” to 65” and in price from $800 to $3200. The first one, the LG, was on the list at position #53.</p>
<p>&nbsp;</p>
<h4>2014: OLED TVs</h4>
<p dir="ltr">Here’s another nugget to file away in the time capsule: no OLED televisions in the top 100. Only two OLED TVs appear in the “top selling OLED” category. Odds are this will be quite different when we revisit this capsule in the future. The two OLED models Amazon actually has available are curved sets. Wonder how that format will hold with time&#8230; They are:</p>
<p dir="ltr"><strong>Samsung KN55S9C Curved Panel Smart 3D OLED HDTV</strong>, <a href="http://www.amazon.com/gp/product/B00E5GIN36/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00E5GIN36&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$8,997</a>. This model has 4 HDMI, 2 USB, 1 LAN, 2 Component, and 1 Composite input. It is a Samsung SmartTV 2.0 with built-in WiFi and Smart Interaction 2.0 with a built-in Camera. Of course its still a 1080p display, but is 3D capable. Also boast a Quad Core processor for super fast app-tivity.</p>
<p dir="ltr"><strong>LG Electronics 55EA9800 Cinema 3D 1080p Curved OLED TV with Smart TV</strong>, <a href="http://www.amazon.com/gp/product/B00E5U3YEK/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00E5U3YEK&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$8499</a>. This LG set, being an OLED, offers what LG calls “Infinite Contrast’ that ranges from the most blazing white to the darkest black. LG&#8217;s 4 Color Pixel technology adds an unfiltered, white sub-pixel to the traditional red, green and blue resulting in a brighter picture with a wider range of colors and superior color accuracy for more true to life and vibrant images.</p>
<p dir="ltr">
<h4>2012: LCD TVs</h4>
<p dir="ltr">Of course the vast majority of sets in the top 100 list were LCD.  Some of the notable sets in 2012 were:</p>
<p dir="ltr"><strong>TCL L40FHDF12TA 40-Inch 1080p 60 Hz LCD HDTV</strong>, (was: <a href="http://www.amazon.com/TCL-L40FHDF12TA-40-Inch-2-Year-Warranty/dp/B004UETB20/ref=sr_1_1?ie=UTF8&amp;qid=1390458548&amp;sr=8-1&amp;keywords=B004UETB20">$319</a>) This was the #1 TV on the list at the time we built the 2012 time capsule.</p>
<p dir="ltr"><strong>Sharp LC-70LE732U</strong>, (was: <a href="http://www.amazon.com/Sharp-LC-70LE732U-AQUOS-1080p-HDTV/dp/B004OCXGAG/ref=sr_1_1?ie=UTF8&amp;qid=1390458624&amp;sr=8-1&amp;keywords=B004OCXGAG">$2389</a>) This is the biggest set in the list, but not the most expensive by more than $1000.  It came in at #75.</p>
<p dir="ltr"><strong>Samsung UN65D8000 65-Inch 1080p 240 Hz 3D LED HDTV</strong>, (was: <a href="http://www.amazon.com/Samsung-UN65D8000-65-Inch-1080p-Silver/dp/B004Y45RXI/">$3488</a>, now: $2499) This Samsung, on the list at #88, had the distinction of being the most expensive set on the list.</p>
<p dir="ltr"><strong>Coby LEDTV2226 22-Inch 1080p HDMI LED TV/Monitor</strong>, (was: <a href="http://www.amazon.com/gp/product/B0040XJC7K/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0040XJC7K&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$179</a>) On the list at #46, this set from Coby isn’t the smallest, but it is the least expensive. The smallest set on the list was actually the Samsung UN19D4003 19-Inch 720p 60Hz LED HDTV, $181.  It ranked in at #18.</p>
<p dir="ltr">
<h4>2014: LCD TVs</h4>
<p dir="ltr">The trend continues in 2014, with the vast majority of sets in the top 100 list being LCD.  Samsung dominated our “notable” list, but that was purely a coincidence. Many other manufacturers are well represented in the full 100. Some of the notable sets in 2014 are:</p>
<p dir="ltr"><strong>Samsung UN32EH5300 32-Inch 1080p 60 Hz Smart LED HDTV</strong>, <a href="http://www.amazon.com/gp/product/B0074FGNJ6/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0074FGNJ6&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$328</a>. This was the #1 TV on the list. This little 32” LED packs a ton of features into a small price tag, which is probably what makes it so appealing.</p>
<p dir="ltr"><strong>Samsung UN19F4000 19-Inch 720p 60Hz Slim LED HDTV</strong>, <a href="http://www.amazon.com/gp/product/B00BCGRZ04/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00BCGRZ04&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$157</a>. At 19 inches this Samsung is the smallest TV on the list, but at $157 it isn’t the least expensive. It only has 720p resolution, but that shouldn’t matter because of the small screen size. It ranks #14 on the list.</p>
<p dir="ltr"><strong>VIZIO E221-A1 22-Inch 1080p 60Hz LED HDTV</strong>, <a href="http://www.amazon.com/gp/product/B00BF9MZ80/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00BF9MZ80&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$139</a>. At $139 Vizio has the distinction of the lowest cost on the list. With a whopping 22 inch screen, it’s bigger than the Samsung above. The kicker? It has 1080p full HD resolution. The appears in the top 100 at position #19.</p>
<p dir="ltr"><strong>Samsung UN75F6300 75-Inch 1080p 120Hz Slim Smart LED HDTV</strong>, <a href="http://www.amazon.com/dp/B00DZRGUAO/?tag=hdtvandhometh-20">$2658</a>. This 75” jumbo screen from Samsung is the largest TV on the list. There are a couple of 70” TVs, and quite a few 65”, but the UN75F6300 is one of only two 75 inch TVs to make the cut. It ranks at #80 on the list. And it’s about $1000 less than the 65” on our list from 2012.</p>
<p dir="ltr">
<h4>Media Streamers</h4>
<p dir="ltr">The Internet Apps and Movie Streaming services continue to shift, so we compared out 2012 snapshot of the top 10 media streamers with the 2014 list. Roku continues to dominates the top 10 with 4, just like two years ago. Apple, WD, and TiVo all with 1 each, same as last time. Google remains on the list, swapping out the Logitech Revue for the Chromecast. Sony dropped from two streamers to one with the Playstation 4. Netgear is new to the list in 2014 with the Push2TV wireless HDMI adapter.</p>
<p>&nbsp;</p>
<h4>2012</h4>
<p dir="ltr">1. Roku LT Streaming Player, $49.99</p>
<p dir="ltr">2. Apple TV MC572LL/A, $98.00</p>
<p dir="ltr">3. Roku 2 XS 1080p Streaming Player, $99.99</p>
<p dir="ltr">4. Roku 2 XD Streaming Player 1080p, $77.09</p>
<p dir="ltr">5. Western Digital WD TV Live Streaming Media Player, $89.99</p>
<p dir="ltr">6. Sony SMP-N100 Streaming Player with Wi-Fi, $49.99</p>
<p dir="ltr">7. Roku 2 HD Streaming Player, $69.99</p>
<p dir="ltr">8. Sony SMP-N200 Streaming Media Player with Wi-Fi, $60.86</p>
<p dir="ltr">9. Logitech Revue with Google TV, $140.99</p>
<p dir="ltr">10. TiVo TCD746320 Premiere DVR, $75.64</p>
<p>&nbsp;</p>
<h4>2014</h4>
<p dir="ltr">1. Google Chromecast HDMI Streaming Media Player, <a href="http://www.amazon.com/dp/B00DR0PDNE/?tag=hdtvandhometh-20">$35.00</a></p>
<p dir="ltr">2. Apple TV, <a href="http://www.amazon.com/dp/B007I5JT4S/?tag=hdtvandhometh-20">$89.99</a></p>
<p dir="ltr">3. Roku 3 Streaming Media Player, <a href="http://www.amazon.com/dp/B00BGGDVOO/?tag=hdtvandhometh-20">$98.00</a></p>
<p dir="ltr">4. PlayStation 4 Console, <a href="http://www.amazon.com/dp/B00BGA9WK2/?tag=hdtvandhometh-20">$499.99</a></p>
<p dir="ltr">5. Roku 1 Streaming Player, <a href="http://www.amazon.com/dp/B00F5NB7JK/?tag=hdtvandhometh-20">$49.97</a></p>
<p dir="ltr">6. Roku HD Streaming Player, <a href="http://www.amazon.com/dp/B00FO12XY6/?tag=hdtvandhometh-20">$39.95</a></p>
<p dir="ltr">7. Roku 2 Streaming Player, <a href="http://www.amazon.com/dp/B00F5NB7MW/?tag=hdtvandhometh-20">$77.99</a></p>
<p dir="ltr">8. NETGEAR Push2TV Wireless Display HDMI Adapter with Miracast, <a href="http://www.amazon.com/dp/B00904JILO/?tag=hdtvandhometh-20">$58.98</a></p>
<p dir="ltr">9. WD TV Live Media Player, <a href="http://www.amazon.com/dp/B005KOZNBW/?tag=hdtvandhometh-20">$89.43</a></p>
<p dir="ltr">10. TiVo Roamio HD Digital Video Recorder, <a href="http://www.amazon.com/dp/B00EEOSZK0/?tag=hdtvandhometh-20">$149.99</a></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2014-01-24.mp3">Download Episode #619</a></p><br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>January 23, 2014 11:43 PM</b>
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
			<?=getComments(5187)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5187)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-619-whats-hot-right-now-2014-time-capsule.php" type="text/javascript" charset="utf-8"></script>
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