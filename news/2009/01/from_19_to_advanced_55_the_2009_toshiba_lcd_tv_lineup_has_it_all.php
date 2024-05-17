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
		AND e.entry_id = 1620";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1620 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1620 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1620";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/from-19-to-advanced-55-the-2009-toshiba-lcd-tv-lineup-has-it-all.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1620";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All" />
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
	<title>HDTV Magazine - From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All</title>
	<meta name="keywords" content="inch inch, screen sizes, inch screen, toshiba america, sizes measured, series, toshiba, inch, reg, new, hdmi, design, available, models, sizes, screen, products, line, digital, high, technology, lcd, america, measured, video" />
	<meta name="description" content="Toshiba America Consumer Products, L.L.C. (&quot;Toshiba&quot;), a market leader in LCD TVs, today announced its full 2009 line-up of LCD televisions offering striking new cosmetics, unique technologies, more advanced features and additional screen sizes. From 19-inch to new 55-inch Cinema Series(R) models, Toshiba's line-up offers..." />
	<meta name="title" content="From 19 to Advanced 55&quot;, the 2009 Toshiba LCD TV Line-Up Has It All" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="From 19 to Advanced 55&quot;, the 2009 Toshiba LCD TV Line-Up Has It All" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/from-19-to-advanced-55-the-2009-toshiba-lcd-tv-lineup-has-it-all.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Toshiba America Consumer Products, L.L.C. (&quot;Toshiba&quot;), a market leader in LCD TVs, today announced its full 2009 line-up of LCD televisions offering striking new cosmetics, unique technologies, more advanced features and additional screen sizes. From 19-inch to new 55-inch Cinema Series(R) models, Toshiba's line-up offers..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1620', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/from-19-to-advanced-55-the-2009-toshiba-lcd-tv-lineup-has-it-all.php">From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All</p>

<center><i>High Quality, Unique Technology, Next-gen Connectivity, Industry Leading Design, and Logical Line Steps Combine to Mark a New Era for Toshiba LCD TV</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire/</B> -- CES -- Toshiba America Consumer Products, L.L.C. ("Toshiba"), a market leader in LCD TVs, today announced its full 2009 line-up of LCD televisions offering striking new cosmetics, unique technologies, more advanced features and additional screen sizes. From 19-inch to new 55-inch Cinema Series(R) models, Toshiba's line-up offers a TV to fit every room and lifestyle at every price point.</p>

<p>"The new REGZA is 'more than you expected, and everything you need,'" said Scott Ramirez, VP, Marketing. "The level of technology we have packed in at every price point is certainly more than you would expect, and with all of our new series Toshiba definitely has everything you need."</p>

<p><br />
<h2>THE 2009 TOSHIBA LCD TV SERIES - more Series, more features, more opportunities</h2></p>

<p><B>AV600 Series - 720p HD - Small Sizes, Big Features</B></p>

<p>Toshiba's AV600 series packs a powerful punch in a small package. These sets are offered in 19-inch and 22-inch screen sizes (measured diagonally) in a new high-gloss black cosmetic design, and are a great choice for consumers looking to replace old analog sets to prepare for the upcoming digital TV transition.</p>

<p>AV600 models feature DynaLight(TM) dynamic backlight control for deeper black levels and two HDMI(TM) digital inputs for simple high quality connection to cable/satellite boxes, DVD players and more. These models also include a PC Input for use as a PC monitor or gaming TV, and unlike other smaller TVs they also feature Gaming Mode to shorten the signal to screen time for faster game controller response. Plus, all Toshiba 2009 TV models will meet ENERGY STAR(R) 3.0 certifications.</p>

<p><br />
<B>AV502 - Mid-Size 720p HD</B></p>

<p>Toshiba's AV502 series mid-size 720p HD models just started shipping in September and will continue throughout 2009. Available in 26-inch, 32-inch and 37-inch screen sizes (measured diagonally), these models come fully equipped with DynaLight, Gaming Mode, 2 HDMI Digital Inputs and a PC Input. Cosmetically, these models feature their own attractive high-gloss black design.</p>

<p><br />
<B>RV525 - 1080p Full HD</B></p>

<p>Stepping to 1080p Full HD is the Toshiba RV525 Series. Also shipping now and continuing throughout 2009, these models have 3 HDMI digital inputs with REGZA-Link(R) (HDMI CEC) and a High-Res PC Input. Available in 40-inch and 46-inch screen sizes (measured diagonally), this series also adds QSound(R) for enhanced audio quality.</p>

<p><br />
<B>REGZA XV645 Series - REGZA Engine, ClearFrame(TM) 120Hz, and Bottom Deep Lagoon(TM) Design</B></p>

<p>Toshiba's REGZA line is powered by Toshiba's PixelPure(R) 5G 14 Bit internal digital video processing, the heart of the REGZA engine. All REGZA models also include Resolution+ super resolution technology, the soul of the REGZA engine, to upconvert and enhance 480i/480p and even 720p signals to create increased detail so everything will feel like HD.</p>

<p>The XV645 series is available in 40-inch, 46-inch and 52-inch screen sizes (measured diagonally), and includes ClearFrame 120Hz technology for clearer fast motion video, as well as AutoView(TM), which automatically sets picture settings based on the incoming video signal and ambient room light. This series includes three HDMI digital inputs with enhanced REGZA-LINK (HDMI-CEC) and a high-resolution PC input. Cosmetically, this series features Toshiba's new Bottom Deep Lagoon design with invisible speakers. Inspired by nature, this design provides a three dimensional feeling to a clean, flat design.</p>

<p><br />
<B>REGZA ZV650 Series - An All-New Level with ClearScan 240(TM), CrystalCoat(TM), Dolby(R) Volume, and Next Gen Connectivity</B></p>

<p>The REGZA ZV650 series is available in 42-inch, 47-inch and new 55-inch screen sizes (measured respectively at 42, 47 and 54.6-inches diagonally) and adds ClearScan 240, which combines a 120Hz frame rate with new Backlight Scanning Technology to create a 240Hz effect for an all new level of picture clarity with fast motion video. For improved sound, the ZV650 series also adds Dolby Volume, which eliminates annoying, inconsistent volume levels from commercials or when changing channels. Connectivity is also taken to an all new level with four HDMI digital inputs that have enhanced REGZA-Link (HDMI-CEC), and InstaPort(TM) for faster HDMI switching. To allow playback of downloaded content, this line also adds a USB Port allowing consumers to watch their JPEG pictures, MPEG-1 & 2 video or even DivX(R) video and listen to MP3 audio. There is also an SD Card Slot that allows easy viewing of JPEG digital pictures by simply taking the memory card from the camera and placing it in the TV.</p>

<p>Cosmetically, this series features the Full Deep Lagoon Design with invisible speakers.</p>

<p><br />
<B>REGZA SV670 Series - FocaLight(TM) LED Backlight with Local Dimming and Stunning Infinity Flush Front(TM) Design</B></p>

<p>Available in 46-inch and 55-inch screen sizes (measured respectively at 46 and 54.6-inches diagonally), this state-of-the-art series adds FocaLight LED Backlight with Local Dimming. Unlike simple edge LED, FocaLight offers a full LED matrix for enhanced brightness uniformity. Even more important is the local dimming, which creates significantly higher dynamic contrast, as blacks are blacker without reducing the peak white brightness.</p>

<p>The ultimate in cosmetic design, this series features Toshiba's Deep Lagoon process behind the new Infinity Flush Front. Inspired by an infinity pool, this design uses a solid sheet of anti-reflective glass across the entire front of the TV. The stunning appearance of the SV670 will set a new standard in LCD TV design.</p>

<p><br />
<B>An Internet TV</B></p>

<p>Toshiba plans to introduce an additional series later in 2009 that will incorporate the new TV Widgets, enabled by the Widget Channel framework, which allows consumers direct and easy access to sources of personalized Internet content via their remote control. A rich array of content is available, such as local weather, top news stories, favorite sports scores, stock quotes, pictures, videos and more, and they are all just a click away. In addition, DLNA and Microsoft's Extender for Windows Media Center capabilities will also be added to allow remote control of a compatible PC. With DLNA and Microsoft's Extender for Windows Media Center, the user can access all of the multi-media content stored on their PC's hard disc drive.</p>

<p><br />
<B>Expected Models and Availability:</B></p>

<p>  AV600 Series<br />
  19AV600U (March 2009)<br />
  22AV600U (March 2009)</p>

<p>  AV502 Series<br />
  26AV502U (Available Now)<br />
  32AV502U (Available Now)<br />
  37AV502U (Available Now)</p>

<p>  RV525 Series<br />
  40RV525U (Available Now)<br />
  46RV525U (Available Now)</p>

<p>  REGZA XV645 Series<br />
  40XV645U (May 2009)<br />
  46XV645U (May 2009)<br />
  52XV645U (May 2009)</p>

<p>  REGZA ZV650 Series<br />
  42ZV650U (April 2009)<br />
  47ZV650U (April 2009)<br />
  55ZV650U (April 2009)</p>

<p>  REGZA SV670 Series<br />
  46SV670U (May 2009)<br />
  55SV670U (May 2009)</p>

<p>  Additional REGZA Series to be announced</p>

<p><br />
<B>About Toshiba America Consumer Products, L.L.C.</B></p>

<p>Toshiba America Consumer Products, L.L.C. is owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation, a world leader in high technology products with subsidiaries worldwide. Toshiba is a pioneer in DVD technology and a leading manufacturer of a full line of home entertainment products, including flat panel TVs, combination products and portable devices. Toshiba America Consumer Products, L.L.C. is headquartered in Wayne, New Jersey. For additional information please visit www.tacp.toshiba.com.</p>

<p>DivX, DivX Certified, and associated logos are trademarks of DivX, Inc. and are used under license.</p>

<p>Dolby is a registered trademark of Dolby Laboratories.<br />
ENERGY STAR is a registered mark owned by the U.S. Government.</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing, L.L.C.</p>

<p>QSound is a registered trademark of QSound Labs, Inc.</p>

<p>All others are trademarks or registered trademarks of their respective companies.</p>

<p>Source: Toshiba America Consumer Products, L.L.C. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 11:06 AM</b>
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
			<?=getComments(1620)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1620)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/from-19-to-advanced-55-the-2009-toshiba-lcd-tv-lineup-has-it-all.php" type="text/javascript" charset="utf-8"></script>
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