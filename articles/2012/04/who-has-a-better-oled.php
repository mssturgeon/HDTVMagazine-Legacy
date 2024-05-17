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
		AND e.entry_id = 4761";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4761 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4761 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4761";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2012/04/who-has-a-better-oled.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4761";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Who Has a Better OLED?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Who Has a Better OLED?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Who Has a Better OLED?" />
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
	<title>HDTV Magazine - Who Has a Better OLED?</title>
	<meta name="keywords" content="sub pixel, sub pixels, blue sub, rgb oled, active shutter, oled, ces, panel, lcd, image, sub, technology, rgb, samsung, color, sony, pixel, may, inch, quality, white, light, viewing, lg’s, crystal" />
	<meta name="description" content="In this previous article about CES I promised to cover in more detail OLED (Organic Light Emitting Diode) for television, and similar technologies shown at CES 2012, even those that actually do not use the classic RGB emissive OLED display method, this article covers that subject.  I will cover 4K, 3D4K, and 8K of CES 2012 in later articles.

As you may be aware OLED is not a new technology, nor are the other competitors such as..." />
	<meta name="title" content="Who Has a Better OLED?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Who Has a Better OLED?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2012/04/who-has-a-better-oled.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In this previous article about CES I promised to cover in more detail OLED (Organic Light Emitting Diode) for television, and similar technologies shown at CES 2012, even those that actually do not use the classic RGB emissive OLED display method, this article covers that subject.  I will cover 4K, 3D4K, and 8K of CES 2012 in later articles.

As you may be aware OLED is not a new technology, nor are the other competitors such as..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4761', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2012/04/who-has-a-better-oled.php">Who Has a Better OLED?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>April 13, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=470&category=OLED HDTVs">OLED HDTVs</a></b>
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
				<p><span class="caption right"><img alt="Sony 11-inch OLED of 2008" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image002_73cbbc8e-5662-469a-a830-8d9676179e8a.jpg" width="162" height="140"><br />Sony 11-inch OLED of 2008</span>In <a href="http://www.hdtvmagazine.com/articles/2012/02/is-ces-worth-attending-anymore.php">this</a> previous article about CES I promised to cover in more detail OLED (Organic Light Emitting Diode) for television, and similar technologies shown at CES 2012, even those that actually do not use the classic RGB emissive OLED display method, this article covers that subject. I will cover 4K, 3D4K, and 8K of CES 2012 in later articles. <p>As you may be aware OLED is not a new technology, nor are the other competitors such as Toshiba/Canon’s SED 1999-2007 (p.47 of 2005 HDTV Technology <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2005.pdf">Review</a>), and NED/FED (p.39-44 of 2006 HDTV <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2006.pdf">Review</a>). All ED technologies were reviewed again on Chapter 5 (p.40-53) of 2007 HDTV <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2007.pdf">Review</a> (354 pages mammoth, be patient downloading), all free. OLED is the survivor.  <p>You may remember that back in CES 2007 Sony <a href="http://www.hdtvmagazine.com/columns/2007/01/hdtv-almanac-ces-oled-lives.php">demoed</a> a 27-inch 1080p OLED, and in CES 2008 <a href="http://www.twice.com/article/249928-Sony_Debuts_14_LCD_TVs_Its_1st_OLED.php">Sony debuted</a> their XEL-1, an 11-inch $2500 OLED that was introduced more as a technology proof-of-concept than a consumer TV. It claimed 30,000 hours of life and was later <a href="http://www.hdtvmagazine.com/columns/2010/02/hdtv-almanac-sony-pulls-plug-on-oled-tv-in-japan.php">discontinued in 2010</a>. It was reported for its RGB material to actually have a <a href="http://www.twice.com/article/236579-Sony_s_OLED_Lifespan_Rated_At_17_000_Hours.php">shorter life</a>, which changed the color of the image as the sub-pixels aged unevenly. <p>Samsung also <a href="http://www.twice.com/article/258142-Samsung_To_Show_31_OLED_TV.php">announced</a> the introduction of a 31-inch prototype at CES 2008 that claimed 35,000 hours of life and announced plans to mass-produce 14-inch screens in 2008, and also promised a 42-inch by 2010, but in December 2008 <a href="http://displaydaily.com/2008/12/09/no-oled-tvs-from-samsung-for-now-or-forever/">the company said</a> “Samsung will not bring OLED TVs to market anytime soon because the technology is currently <a href="http://www.electronista.com/articles/08/12/03/samsung.oled.displays/">considered too expensive</a>”. <p>Later in 2009, Kodak, credited to have developed OLED in the 1970s, <a href="http://www.twice.com/article/441269-Kodak_To_Sell_OLED_Business_To_LG.php">announced</a> that it was selling its OLED business to LG, who <a href="http://www.twice.com/article/388087-LG_Targets_Cheap_OLED_TVs_By_2016.php">announced</a> plans to develop OLED TVs while showing a 15-inch set, which plans? A 20-inch+ in 2010, 30-inch+ in 2011, and 40-inch+ in 2012 with the goal to be priced similarly than LCD by 2016. Judging by CES of January 2012 it appears LG and Samsung have done better than what their crystal balls said. <p>At CES 2012 two large OLED panels were demoed and announced to be available in the second half of 2012, a 55-inches RGB OLED <a href="http://www.hdtvmagazine.com/news/2012/01/samsung-unveils-the-super-oled-tv-the-ultimate-in-picture-quality.php">by Samsung</a>, and a similar size White OLED with color filters <a href="http://www.hdtvmagazine.com/news/2012/01/lg-electronics-takes-historic-step-forward-with-ultrathin-55inch-class-oled-hdtv.php">by LG</a>, with a rumored price range between $5k and $10K, although LG just announced the set will be released earlier than planned and at a price just below $8K. Is that expensive for OLED? Absolutely not, considering Sharp’s Elite LCD ranges $6-8K, for an LCD, nice picture for an LCD, but is just an LCD, so, if I justify spending that money on a TV, I prefer to sign a check for an OLED (or a plasma) rather than an LCD.  <p><b>LG’s WOLED</b> <p><span class="caption left"><img alt="LG's 55-inches &quot;White&quot; OLED" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image005_1488e40f-ae52-4a16-979e-f65fc2f164e8.jpg" width="452" height="318"><br />LG's 55-inches "White" OLED</span>LG Display is full of surprises. At CES 2011the company decided to declare an <a href="http://www.hdtvmagazine.com/articles/2011/10/passive-3dtv-brain-perception-an-excuse-for-technical-limitations.php">ad war</a> to the active-shutter 3DTV technology (and unfortunately to the companies as well) and concentrate in <a href="http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php">passive 3D</a> LCD with Film Patterned Retarder (FPR, <a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">at half resolution per eye</a>), while Sony, Panasonic, Samsung, JVC, and many others were all doing active-shutter 3D TVs and projectors at full resolution per eye (which manufacture 90% of the 3D models available in the market, according to 3D University as of Oct 31, 2011). <p>Then, at CES 2012 LG introduced their 4K version of the FPR 3D panel, which by having the double of vertical resolution (2196)now (thankfully) displays 3D as 1080p per eye, how did it look to me? That is for the next article. <p>LG also introduced a different approach of OLED, a WOLED 55-inch panel, weighting only 16.5 lbs (not a typo), is only 4mm thin (not a typo either), claims 100,000,000:1 contrast ratio (if anyone can measure that, deep in space), claims less than 0.1 microseconds refresh rate, and has a Triple XD engine that drives the picture with Dynamic Color Enhancer, a Contrast Optimizer, and a Resolution Upscaler. Not surprisingly, it displayed a stunning image, like Samsung did as well (down below). <p><span class="caption left" style="width:629px"><img alt="clip_image009" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image009_b4c1e959-ad9a-4e0e-ac94-abe138dba88f.jpg" width="627" height="353"><br />Credit: CNET – I am crushed by journalists with cameras that cost more than the TV, in front of LG’s OLED on press day, I was more interested in analyzing the image than in taking a picture, reason by which you may be reading this. How did I get to the front row, viewing straight to the set with no one in front of me? Patience, anticipation,and lots of muscles in my back to hold the avalanche, although that did not work that well when the 153,000 people arrived when CES opened the following day, the photo above was a piece of cake compared to that.</span><br clear="both" /> <p>As expected, LG’s press conference at CES provided only the highlights. The personal discussions with other colleagues were more technically productive and I found out we all got the same “I do not know” responses from LG and still left unanswered questions like: <p><span class="caption right"><img alt="clip_image011" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image011_b9d178fa-e30d-4f68-8bc6-5b1529bec05a.jpg" width="352" height="263"></span>How exactly the panel uses the white sub-pixels with the filters, and what that means for voltage, light output, picture quality, pricing, angle of view, scalability to higher sizes and resolution, manufacturing, and longevity compared to RGB OLED, especially the known aging problem of the blue sub-pixel? Does the panel also use the W sub-pixel like the Pen Tile technology in smaller LCD devices? (graph) <p><b></b> <p><b>Pen Tile? I rather have Real Marble</b> <p>LCD, as a transmissive display technology, uses the white sub-pixel PenTile technology in smaller/portable devices for a higher aperture ratio to increase the overall transmissivity of the LCD panel, saving energy while getting the brighter image needed by a portable device outdoors. <p>The AM/OLED technology for small devices rather uses RGBG PenTile, the extra G sub-pixel helps decrease current density and still obtain improved brightness without damaging the organic layers of the panel, making them last much longer than they did a few years ago, which favors cost, and panel application for prolonged viewing purposes. <p>Before CES the appearance was that LG’s implementation of WOLED did not implement Pen Tile as above, and it was confirmed when at CES LG declared: “we use all white OLED sub-pixels with an RGB color filter”. The “why” was not explained at CES, but LG provided a bit more of information after CES (further down).  <p>Let us think about this for a minute: A light source (white OLED rather than LEDs) that still uses filters at front to produce the colors? Sounds familiar? What kind of “emissive OLED” is that when the colored light is actually not emitted by a pixel at front? WOLED appears to be another “transmissive” technology with local dimming but using OLED as light source at the sub-pixel level, rather than edge, top, or multi-zone LEDs. So regardless if the WOLED may not look as emissive as RGB OLED it controls black at the pixel level and the image looks stunning, and for most consumers, that is what it counts, a great image. <p><span class="caption right"><img alt="Cannon/Toshiba SED effort shown at CES 2006 with a 37&quot; panel, planning for a 55&quot;" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image013_c8bfc920-9523-4093-9562-b25be423c918.gif" width="435" height="283"><br />Cannon/Toshiba SED effort shown at CES 2006 with a 37" panel, planning for a 55"</span>I had the chance to view the OLEDs several times in 5 days and to compare LG’s 55” W-OLED (55EM9600) with Samsung’s 55” Super OLED (no model # and no specifications, even as of March 1st), and with SONY’s 55” Crystal LED panel, although this panel actually uses miniature LEDs for the RGB sub-pixels and it is only a prototype with no availability announcements (covered below). <p>Samsung OLED emissive and LG WOLED (not so emissive) technology showed stunning images that were obviously better than plasma’s emissive technology of today and the defunct SED (Canon/Toshiba effort shown at CES 2006, photo), and I quote from my 2006 HDTV Technology <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2006.pdf">Review</a>:  <p>“<i>Canon demonstrated their SED technology (in partnership with Toshiba), on the right is the 37” prototype panel shown at CES. Canon said that the first unit will be a 55” 1080p model (which will become available in Japan next year), they do not know price, nor date of availability yet, only size. It will have a life cycle of about 30,000 hrs (similar to CRT they said). Canon and Toshiba will be selling similar models under their brand names.”</i> <p>However, stunning does not necessarily equate to a pleasant image for prolonged viewing, and I cover that subject down below as well.  <p>Tim Alessi, Director of New Product Development of LG Electronics U.S.A., Inc (his position then, he recently transferred to another CE company), claimed their “white” OLED has a natural image, ultimate lightweight design, widest color gamut, widest viewing angle, deepest black levels, and would be suited with 3D FPR’s LG Cinema (at half resolution per eye as their current LCDs). <p>At CES LG did not provide commitment assurance to any plans to manufacture a 4K OLED, although they admitted 4K appears to be a natural progression of the technology. OLED’s availability was announced to be expected by the 3<sup>rd</sup> quarter of 2012 in the US, although LG said its production did not start yet (at CES time). No pricing was announced, but earlier rumors of “between $5K and $10K” were spread again (but as mentioned earlier, LG recently announced an <$8k price). LG said there were no plans for other sizes other than the 55 inches panel shown at CES.  <p>However, after CES LG provided more insight as to why they chose the WOLED design of white OLEDs with color filters (and no mask) over Samsung’s RGB OLED technology (that typically uses a mask that complicates construction). LG said it is easier to manufacture and scale to other sizes and resolutions (like 4K), and its image quality has improved color reproduction, while the filters do not affect the viewing angle. LG added that is highly expected for the panel to have long life, and be more energy efficient than LCD and plasma. <p>When LG announced that 50% of LG’s 2012 LCD models will be suited with LG Cinema 3D (passive FPR) and Smart TV, they said they have 18,000 engineers and scientists around the world, but LG did not disclose how many of those engineers and scientists are actually working on OLED.  <p><b></b> <p><b>Sony Crystal LEDs Prototype Panel</b> <p>The panel was demoed in two environments, one (shown in the photo) standing alone and flanked by two Sony staff to protect it from people getting too close, and another Crystal demo was in a dark booth with Sony’s top-of-the-line LCD of similar size, both displaying side-by-side the same trailers simultaneously. <p>The Sony person at this booth provided a bit more information about the product, but I was surprised he did not notice (or did not want to admit) the image problems I detected on the Crystal when I described the issues to him. <p>When I saw the demo in a rush at press day I noticed the overall pop and higher color saturation, but did not notice that some colors exceeded the boundaries of the edges of objects, especially the red.  <p><span class="caption left"><img alt="clip_image016" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image016_0c57ee66-ec83-40b6-acc0-0a56dae693db.jpg" width="426" height="413"></span>The white Sony logo shown at the beginning of the demo trailer was correctly displayed in white by the LCD but was displayed in pink by the Crystal. <p>A sand-color wall on the boat clip showed correctly in the LCD but showed as a reddish/pink wall on the Crystal. <p>The reds on the braking lights of cars showed over-saturated in the Crystal exceeding the edges of the rear light-assembly of the cars, and bleeding over the car’s body. <p>A pedestrian street under the shadows of trees receiving sun light accents of green over the street’s dirt color, was correctly depicted in the LCD, but showed pink on the Crystal; yes, a pink dirt-street. <p>I am not a fan of LCD, I always considered LCD of inferior image quality compared to plasma, but this demo actually made the LCD panel show more natural colors and accurate saturation than the Crystal, and the ironic part was that this demo was supposed to show exactly the opposite to highlight the beauty of the prototype. <p>Perhaps the reason was lack of ISF calibration, but all the content in the Crystal appeared to have excessive red push. Again, this is just a prototype and I hope a production unit will be better, if ever released. <p>The following are some specs provided by Sony about this prototype: <p><i>The “Crystal LED Display” is a self-emitting display that uses Sony’s unique methods to mount ultra-fine LEDs in each of the Red-Green-Blue (RGB) colors, equivalent to the number of pixels (approximately six-million LEDs for Full HD). The RGB LED light source is mounted directly on the front of the display, dramatically improving the light use efficiency. This results in images with strikingly higher contrast (in both light and dark environments), wider color gamut, superb video image response time, and wider viewing angles when compared to existing LCD and plasma displays, with low power consumption. Furthermore, due to the display’s structure and manufacturing processes, the “Crystal LED Display” is also ideal for large screens.</i> <p><span class="caption right"><img alt="Sony's Crystal LED Display Prototype" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image018_847c29db-3048-449f-8794-e3d7e9f03d82.jpg" width="469" height="315"><br />Sony's Crystal LED Display Prototype</span><i>Compared to existing LCD displays, the 55-inch prototype exhibited at CES is boasting approximately 3.5 times higher contrast in light environment, approximately 1.4 times wider color gamut, and approximately 10 times faster video image response time (all values based on current Sony models). Sony envisages a wide range of applications for its “Crystal LED Display,” ranging from professional to consumer use. Parallel to its continued development and commercialization of organic light emitting diode (OLED) displays, Sony will work conscientiously to bring the “Crystal LED Display” to market. </i> <p><i><u>Main specifications of the prototype</u></i> <p><i>Panel size: 55-inch</i> <p><i>Number of pixels: 1,920 x 1,080 x RGB (Full HD: uses approx. 2 million each of RGB LEDs, </i> <p><i>a total of around 6 million LEDs) </i> <p><i>Display elements: RGB LEDs </i> <p><i>Frame rate: 120Hz </i> <p><i>Brightness: Approximately 400 cd/m<sup>2</sup></i> <p><i>Viewing angle: Approximately 180 degrees </i> <p><i>Contrast (dark environment): More than measurable limit values </i> <p><i>Color gamut: More than 100% compared to NTSC (xy)</i> <p><i>Power consumption (panel module): Under approximately 70W<sup>*2 </sup></i> <p>I hope Sony would eventually come to the market with this set, or with an OLED set, to compete with the quality shown by Samsung and LG at CES, and I wish Sony the best on that endeavor. <p><b></b> <p><b>Samsung Super OLED panel</b> <p><span class="caption left"><img alt="Samsung 55-inches Super OLED" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image021_8c704392-67e0-47d0-b060-31571b9d1517.jpg" width="613" height="397"><br />Samsung 55-inches Super OLED</span><br clear="both" />I met with Mr. Michael N. Wood, Senior Manager Product Quality Assurance, Samsung Electronics America, Inc. who gave me a walk-thru of the new hi-end video products Samsung was introducing at CES. I was particularly interested in technical details of their Super OLED, which I was able to view many times throughout my 5 days of exhibitors CES.  <p>In all honesty, the set was as stunning as LG’s, but had no model # and no specs other than 1080p with active-shutter 3D technology, which could be used for two viewers seeing two different HD images simultaneously with their 3D glasses using time sequential 3D, also demoed. As of yesterday, when I contacted again Michael at Samsung, additional specs were still not available but I was offered to receive as soon as they are available.  <p>Why Super? For starters, it was the only true RGB emissive OLED, and I add another super, 3D will be active-shutter with full 1080p resolution per eye with OLED quality. In my opinion having a panel of this quality would be a waste with half-resolution 3D passive FPR, as LG implements on their WOLED panel, but hey! The 3D glasses are cheap! (An important feature that may make you choose a $5/8/10K hi-end OLED panel, if you catch my drift) <p><b></b> <p><b>Out of the Blue </b> <p> It has been known that the blue sub-pixel in OLED typically ages sooner compared to the red and green sub-pixels. Thru the years the blue sub-pixel longevity (to reach half-life brightness) improved from just a few thousand hours to 25,000 hours the last time I researched the subject, while the red and green OLED sub-pixels last several times that number of hours. The effect: mixing a degraded blue with normal red/green produces unwanted color balance. <p>So my first question to Michael was: How long the blue longevity is now in your Super OLED?  <p>Michael told me that Samsung found a way to double the longevity of the blue sub-pixel to now 50,000 hours, which makes the OLED panel last much longer than traditional RGB OLEDs are known to last (which equates to 34 years of 4 hours per day of viewing, or less years if you sleep with the TV on, just to claim that its life is still too short and that is the reason you are not buying it). <p><span class="caption left"><img alt="Samsung's 55-inches OLEDs" src="http://www.hdtvmagazine.us/articles/images/808d263f49a2_144AB/clip_image024_f1b05a2e-5af6-4b3e-a764-170c4e52175b.jpg" width="627" height="263"><br />Samsung's 55-inches OLEDs</span><br clear="both" /> <p><b>And now we came to the subject title: Which OLED was better?</b> <p>Does it manner now? <p>It was not easy (or fair) to subjectively compare which image was better between Samsung’s OLED panel and LG’s WOLED, but after 5 days of repeated viewings I would say that although it was a very close call, I tend to like more Samsung’s OLED. <p>However, in all honesty, I noticed something in one of the images that raised a concern: when showing the clip with the yellow flower over the very black background the yellow of the flower was so over-saturated that exceeded the edges of the flower and spilled over the black background with a ¼ inch yellow halo around the flower, certainly making the edge less defined and the flower unnatural. <p>It was like a glowing effect that should not happen in a picture of a real flower with a black background, unless the content was actually recorded with that yellow halo to begin with, and the OLED was just displaying the image as it was.  <p>I also saw the same effect in the computer generated objects shown over the black background, although the halo effect did not look that abnormal on those, knowing that it was a CGI of an unreal object and on CGI anything can happen. <p>In all fairness, some CGI images were also displayed in LG’s OLED, but I cannot say I noticed the same because I did not see the flower clip on LG’s.  <p><b></b> <p><b>What actually drives the Choice?</b> <p>Regardless of which image I liked better, there are many other factors that may turn the tables on the choice for each person, for example:  <p>a) Scalability to higher resolutions (4K) and to larger sizes. LG claims they can, and implied it would be difficult for Samsung, but Samsung did not cover the scalability subject yet,  <p>b)3D preference of the viewer (passive/active), if it is passive that automatically points to LG, and vice verse,  <p>c) Longevity of the organic material. LG’s white OLEDs claim to make the panel last much longer than Samsung’s RGB OLEDs, but check (f). However, would someone looking for this quality be concern with longevity beyond 10 years? Maybe in 3-5 years such person would change the set anyway, or move it to a secondary room for less viewing hours, <p>d) Measured picture quality considering that LG uses filters that are known to generally affect viewing angle, color, contrast, and brightness due to the extra color filter layer. This also applies to the extra FPR film layer for 3D, which may potentially affect the 2D image quality when the film may not be removed out of the 2D viewing. Samsung does not use FPR because the 3D is active-shutter, <p>e) Great whites may be shown with a dedicated (4<sup>th</sup>) W pixel in the LG, while Samsung’s may not be as white if the mixing of the RGB OLED sub-pixels does not render perfect whites, or may be degraded with time by an unbalanced mix of RGB sub-pixels due to inconsistent aging among them, also check (f), <p>f) Samsung’s blue sub-pixel may be subjected to unnecessary aging just to produce a white image compared to LGs dedicated W sub-pixel, unless LG obtains white light the same way by activating an RGB sandwich of OLEDs per sub-pixel, as <a href="http://reviews.cnet.com/8301-33199_7-57386898-221/what-is-oled-tv/?part=rss&amp;subj=news&amp;tag=title">claimed</a> by CNET (with which I do not agree because LG clearly said they use white OLED sub-pixels with color filters). I asked LG to confirm CNET’s understanding. <p>So as you see it would not be fair for anyone to choose which OLED is better based on a subjective image evaluation of prototypes during a trade show viewing, the production models are not out until at least July and they can change by then, and reviewers from reliable publications did not yet have the chance to calibrate, evaluate picture quality, and do objective comparisons. I suspect that by the end of the third quarter we would be able to have feedback in all the areas above. <p><b></b> <p><b>What is next?</b> <p>I am looking forward to see OLED in the hands of consumers and reviewers in a few months. Regardless if it is a Samsung or an LG, I anticipate that an owner would probably feel the need to ISF calibrate the punch of the panel down to make its image less striking and more natural for prolonged viewing to avoid visual fatigue, especially in a dark environment. <p>Plasma will continue to be the highest quality image technology for the money for some time until OLED can take its place at reasonable pricing, and LCD will continue to be a lower image quality alternative that would still appeal to many consumers because of relatively low pricing and consumer and retailer inertia for LCD (and ignorance about image quality), although gradually experiencing a reduction in market share while OLED establishes itself in a variety of sizes at competitive prices, also in 4K and 4K3D.  <p>Even with LG’s and Samsung’s great demo introductions and short-term timetables, some industry spectators that closely followed SED, FED, NED and OLED steps throughout the years may still say: “I have seen so many prototype demos and claims that I would believe OLED to be ready when I see it in Best Buy with a price tag”. <p>It appears this time may be different because the announcements are not any more “we will have ZZZ vaporware technology by year XXXX”. The manufacturers are rather announcing availability by the 2<sup>nd/</sup> 3<sup>rd</sup> quarter as they customarily announce their new TV lines every year. Additionally, since two of the largest companies are competing for the privilege to be first and better we should all win in 2012. <p>Stay tuned for 4K, 3D4K and 8K, which is not just about “not seeing pixel structure”, as some 4K naysayers say. 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>April 13, 2012  7:17 AM</b>
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
			<?=getComments(4761)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4761)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2012/04/who-has-a-better-oled.php" type="text/javascript" charset="utf-8"></script>
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