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
		AND e.entry_id = 4756";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4756 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4756 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4756";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/03/hdtv-and-home-theater-podcast-podcast-524-eden-amazon-and-the-ultimate-avr.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4756";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR</title>
	<meta name="keywords" content="lcd hdtv, hdtv stars, ultimate avr, our avr, black stars, avr, hdtv, inch, stars, lcd, xbmc, add, available, volume, better, support, features, both, see, system, ultimate, new, receivers, our, need" />
	<meta name="description" content="This week we are going to design the AVR of the future. This is a group effort! We will provide you with initial specifications and we would like you to add features that would make our new AVR the Ultimate AVR. Now we just need someone to build it." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/03/hdtv-and-home-theater-podcast-podcast-524-eden-amazon-and-the-ultimate-avr.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This week we are going to design the AVR of the future. This is a group effort! We will provide you with initial specifications and we would like you to add features that would make our new AVR the Ultimate AVR. Now we just need someone to build it." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4756', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/03/hdtv-and-home-theater-podcast-podcast-524-eden-amazon-and-the-ultimate-avr.php">HDTV and Home Theater Podcast - Podcast #524: Eden, Amazon and the Ultimate AVR</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>March 29, 2012</b>
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
<h3>XBMC 11.0 &#8211; Eden</h3>
<p>XBMC 11.0 “Eden” has been released and is loaded with new features and improvements. For those of you who don’t know what XBMC is:<em><br />
</em></p>
<p dir="ltr"><em><strong>XBMC</strong> is an award-winning free and open source (GPL) software media player and entertainment hub for digital media. XBMC is available for Linux, OSX, and Windows. Created in 2003 by a group of like minded programmers, XBMC is a non-profit project run and developed by volunteers located around the world. More than 50 software developers have contributed to XBMC, and 100-plus translators have worked to expand its reach, making it available in more than 30 languages.</em></p>
<p><strong>Updates:</strong></p>
<ul>
<li>Massive speed increases via features like Dirty-region rendering and the new JPEG decoder</li>
<li>Better movie scraping of your library</li>
<li>Additional protocol handling</li>
<li>Better networking support</li>
<li>Better handling of unencrypted BluRay content and structures</li>
<li>Adjustable display refresh rate in OSX (to match the already available feature in Windows and Linux)</li>
<li>AirPlay support</li>
<li>An upgraded <a href="http://www.wunderground.com/">weather service</a> with geoip lookup</li>
<li>Much, much more.</li>
</ul>
<p>Full list of updates can be found in the <a href="http://mirrors.xbmc.org/releases/11.0-Eden-changelog.txt">summarized changelog</a></p>
<p>&nbsp;</p>
<h3 id="internal-source-marker_0.6407168346518015">Top Ten TVs at Amazon</h3>
<ol>
<li><a href="http://www.amazon.com/gp/product/B004OVEUPW?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004OVEUPW&amp;ref_=zg_bs_172659_1">LG 32LK330 32-Inch 720p 60 Hz LCD HDTV $344</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004OOQ8CW?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004OOQ8CW&amp;ref_=zg_bs_172659_2">LG 42LK450 42-Inch 1080p 60 Hz LCD HDTV $498</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004M8SBCK?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004M8SBCK&amp;ref_=zg_bs_172659_3">Panasonic VIERA TC-L32C3 32-Inch 720p LCD HDTV $300</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004OVEUQQ?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004OVEUQQ&amp;ref_=zg_bs_172659_4">LG 32LK450 32-Inch 1080p 60 Hz LCD VA Panel HDTV $380</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004SP0EY0?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B004SP0EY0&amp;ref_=zg_bs_172659_5">Philips 32PFL3506/F7 32-inch 720p LCD HDTV, Black $268</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004OOS35C?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004OOS35C&amp;redirect=true&amp;ref_=zg_bs_172659_6&amp;creativeASIN=B004OOS35C">LG 42LK520 42-Inch 1080p 120 Hz LCD HDTV $470 (Refurb)</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004OOS34S?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B004OOS34S&amp;ref_=zg_bs_172659_8">LG 37LK450 37-Inch 1080p 60 Hz LCD HDTV $432</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004N866SK?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004N866SK&amp;ref_=zg_bs_172659_9">Samsung LN37D550 37-Inch 1080p 60Hz LCD HDTV (Black) $499</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004OOS38O?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004OOS38O&amp;ref_=zg_bs_172659_9">LG 55LK520 55-Inch 1080p 120 Hz LCD HDTV $998</a> 4.5 Stars</li>
<li><a href="http://www.amazon.com/gp/product/B004N866SA?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004N866SA&amp;ref_=zg_bs_172659_10">Samsung UN46D8000 46-Inch 1080p 240Hz 3D LED HDTV $1590</a> 4 Stars</li>
</ol>
<p>&nbsp;</p>
<h3 id="internal-source-marker_0.6246826663547514">The Ultimate AVR</h3>
<p>This week we are going to design the AVR of the future. This is a group effort! We will provide you with initial specifications and we would like you to add features that would make our new AVR the Ultimate AVR. Now we just need someone to build it.</p>
<p>Our AVR needs to have the basics covered: Great Sound, Dolby and DTS processing, 7.1, 6 HDMI 1.4 ports, Network connectivity, and Automatic Room Calibration are table stakes. No our AVR needs to do much more than that! Let’s look at some features we want, some of which are available today and some that we want to see available soon.</p>
<h4>Dolby Volume or Audyssey Dynamic Volume (or Both!)</h4>
<p>We have all been there. We watching something late at night and we have to raise the volume so we can hear the dialog. All is well until an action sequence starts. Then its a mad dash to the find the remote and lower the volume before the children are woken or the neighbors complain. Both Dolby Volume and Audyssey Dynamic Volume level out the sound so you don’t need to do anything. We’ve heard both solutions and find they work the best at this task. This is a must have that’s available today in many but not all receivers.</p>
<h4>Smart AVR</h4>
<p>We have all heard of smart TVs well we’d like to see a smart AVR. Why not put Netflix, Hulu, and other apps right in the AVR? This eliminates at least one box in your system. Many receivers support DLNA so why not add a better user interface and stream content from your computer through the receiver. Better yet, add Airplay support for both video and audio! While we’re add it, we’d like to recommend that this mythical AVR actually spend money on a sexy new user interface. Many of these features are available on some receivers but the interface is so bland.</p>
<h4>Digital Audio/Video Distribution</h4>
<p>Let’s make this receiver an A/V distribution hub. Our AVR will be able to output multiple video sources to different zones. For this we suggest that smaller units be connected to the main unit via Ethernet or WiFi. Each of the remote units will have amplifiers so that you can add a surround system in your bedroom or den.</p>
<h4>More Sophisticated Auto Calibration</h4>
<p>The auto calibration in today’s receivers are good but we want a better system. One that uses multiple microphones to properly dial in a room. It should show you where you dead spots are and provide an analysis that you can use to add acoustic treatments to get the best experience from your system.</p>
<h4>Odds and Ends</h4>
<ul>
<li>Dedicated Subwoofer level control &#8211; Sometimes you need more bass and sometimes you don’t!</li>
<li>On screen menus even for digital sources!</li>
<li>Support for 802.11ac &#8211; Gigabit WiFi!</li>
<li>Satellite and/or HD Radio</li>
</ul>
<p>So there you have it. The beginnings of the HT Guys ultimate AVR. Please email us your feature requests or add them to this post for everyone to see. Then we need to have a pool to see when such a beast will finally come to market.</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-03-30.mp3">Download Episode #524</a></p>
<p>&nbsp;</p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>March 29, 2012  8:54 PM</b>
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
			<?=getComments(4756)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4756)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/03/hdtv-and-home-theater-podcast-podcast-524-eden-amazon-and-the-ultimate-avr.php" type="text/javascript" charset="utf-8"></script>
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