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
		AND e.entry_id = 4125";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4125 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4125 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4125";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/vizio-unveils-ultrawidescreen-219-cinemawide-hdtv-led-lcds-for-the-ultimate-cinematic-experience.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4125";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience" />
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
	<title>HDTV Magazine - VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience</title>
	<meta name="keywords" content="cinemawide hdtv, internet apps, aspect ratio, smart dimming, apps via, hdtv, cinemawide, apps, internet, aspect, technology, side, led, experience, theater, ratio, full, most, top, movies, movie, content, xvt, screen, models" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-ultra-widescreen.jpg&quot; alt=&quot;VIZIO Ultra-Widescreen&quot; height=&quot;72&quot; width=&quot;72&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;VIZIO, America's #1 LCD HDTV Company*, revealed today plans to launch Cinemawide HDTV&amp;trade;, 21:9 Cinema aspect ratio models that can display native 2.35:1 (&quot;CinemaScope&quot;) movies without any black bars for a true cinematic experience. The ultra widescreen perspective displays movies as designed for the silver screen for an immersive movie experience at home. Each model also features VIZIO Internet Apps&amp;trade; (VIA) in Cinema mode, which allows users to browse apps side-by-side with 16:9 Full HD content without any compromise in resolution or size. The 50- and 58-inch class size models are Edge Lit Razor LED&amp;trade; HDTVs with Smart Dimming. VIZIO will also be demonstrating at their private CES showroom a 71-inch class size model with Full Array TruLED&amp;trade; backlighting for the ultimate in performance.

All three models feature..." />
	<meta name="title" content="VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/vizio-unveils-ultrawidescreen-219-cinemawide-hdtv-led-lcds-for-the-ultimate-cinematic-experience.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-ultra-widescreen.jpg&quot; alt=&quot;VIZIO Ultra-Widescreen&quot; height=&quot;72&quot; width=&quot;72&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;VIZIO, America's #1 LCD HDTV Company*, revealed today plans to launch Cinemawide HDTV&amp;trade;, 21:9 Cinema aspect ratio models that can display native 2.35:1 (&quot;CinemaScope&quot;) movies without any black bars for a true cinematic experience. The ultra widescreen perspective displays movies as designed for the silver screen for an immersive movie experience at home. Each model also features VIZIO Internet Apps&amp;trade; (VIA) in Cinema mode, which allows users to browse apps side-by-side with 16:9 Full HD content without any compromise in resolution or size. The 50- and 58-inch class size models are Edge Lit Razor LED&amp;trade; HDTVs with Smart Dimming. VIZIO will also be demonstrating at their private CES showroom a 71-inch class size model with Full Array TruLED&amp;trade; backlighting for the ultimate in performance.

All three models feature..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4125', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/vizio-unveils-ultrawidescreen-219-cinemawide-hdtv-led-lcds-for-the-ultimate-cinematic-experience.php">VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">VIZIO Unveils Ultra-Widescreen 21:9 Cinemawide HDTV LED LCDs for the Ultimate Cinematic Experience</p>

<center><i>- VIZIO introduces Cinemawide HDTV&trade; that supports CinemaScope-wide aspect ratio with 2,560 x 1,080 resolution for the ultimate immersive movie experience<br /><br />- Cinemawide HDTV&trade; LED LCDs will be available in two sizes: 50- and 58-inch classes Edge Lit Razor LED&trade; models with Theater 3D&trade; technology<br /><br />- VIZIO to also demonstrate 71-inch Full Array TruLED&trade; Cinemawide HDTV&trade;<br /><br />- All models feature VIZIO Internet Apps&trade; (VIA) in Cinemawide mode for full side-by-side app browsing with 16:9 Full HD TV viewing</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/vizio-ultra-widescreen.jpg" alt="VIZIO Ultra-Widescreen" height="144" width="144" class="keyimg"><strong>IRVINE, Calif., Jan. 5, 2011 /PRNewswire/ --</strong> VIZIO, America's #1 LCD HDTV Company*, revealed today plans to launch Cinemawide HDTV&trade;, 21:9 Cinema aspect ratio models that can display native 2.35:1 ("CinemaScope") movies without any black bars for a true cinematic experience. The ultra widescreen perspective displays movies as designed for the silver screen for an immersive movie experience at home. Each model also features VIZIO Internet Apps&trade; (VIA) in Cinema mode, which allows users to browse apps side-by-side with 16:9 Full HD content without any compromise in resolution or size. The 50- and 58-inch class size models are Edge Lit Razor LED&trade; HDTVs with Smart Dimming. VIZIO will also be demonstrating at their private CES showroom a 71-inch class size model with Full Array TruLED&trade; backlighting for the ultimate in performance.</p>

<p>All three models feature VIZIO's Theater 3D&trade; technology that delivers superior, flicker-free 3D performance that is up to 2x brighter and significantly reduces crosstalk compared to current Active Shutter LCD TVs and works in conjunction with battery-free, lightweight glasses. Theater 3D puts the burden of 3D processing into the TV, eliminating the need for cumbersome, complex, and expensive glasses. Compared to "Active Shutter" technology, VIZIO's Theater 3D offers up to 2x brighter picture quality without flickering. It also significantly reduces the crosstalk inherent in Active Shutter 3D which can cause eyestrain and headaches. Theater 3D eyewear is compatible with most 3D movie theaters.</p>

<p>"For movie-buffs, the Cinemawide HDTV series is a revelation and lets them watch movies as the filmmakers intended," stated Matthew McRae, VIZIO CTO. "The result is a significantly more immersive experience that fills your field of vision without black bars or loss of resolution. In addition, accessing favorite apps with VIZIO Internet Apps can now be a true side-by-side experience with 16:9 content for a full HD image right next to the VIA sidebar."</p>

<p><br />
<strong>Cinemawide HDTV - Ultra-Widescreen</strong></p>

<p>Most HDTVs have an aspect ratio of 16:9, or 1.78:1, and a resolution of 1,920 x 1,080 often referred to as 1080p Full HD. This aspect ratio was a compromise between the more square formatting of older televisions (4:3 or 1.33:1) and the wider 1.85:1 aspect of many movies. A 1.85:1 movie on a 16:9 HDTV will still show some black bars at the top and bottom of the screen. Big-budget Hollywood blockbusters, though, are usually filmed in the much wider 2.35:1 or 2.39:1 aspect ratio. VIZIO's new Cinemawide HDTVs slot in between these "CinemaScope" aspect ratios perfectly with a 2.37:1 (21:9) aspect ratio. So whether you're watching 2001, Lord of the Rings, Kill Bill, Toy Story 3, or countless other movies, you can watch them in their original aspect ratio and without black bars.</p>

<p><br />
<strong>VIZIO Internet Apps&trade; (VIA) Meets Cinemawide HDTV</strong></p>

<p>With Cinemawide HDTV, using VIZIO Internet Apps (VIA) is more seamless than ever. While watching a pixel-perfect 16:9 full HD image on the right side of the screen, users can simultaneously browse and use Apps on the left side of the screen. Search Wiki TV for the actor you just saw, Tweet about the movie you're watching, or even buy tickets to the new sequel through Fandango.</p>

<p>"Connected TV's are expected to account for 21% of Global TV Shipments in 2010, rising to 122 million units globally by 2014**, representing one of the most exciting areas of growth in the TV industry and ultra-wide aspect ratio TVs, such as 21:9, and will enable consumers to view their TV content and Internet content simultaneously in harmony," stated Paul Gagnon Director of North America TV Market Research, DisplaySearch.</p>

<p>With VIZIO Internet Apps (VIA), top online content and services brands are available at the touch of a button, including: Amazon Video On Demand, Facebook&reg;, Flickr&reg;, Netflix, Rhapsody&reg;, Pandora&reg;, Twitter&trade;, VUDU&reg;, and Yahoo!&reg; TV Widgets. Additional Apps recently released include Fandango&reg;, iMemories&reg;,  MediaBox&trade;, My-Cast&reg;, TuneIn Radio&trade;, Web Videos, Wiki TV and Yahoo Fantasy Football . VIZIO Internet Apps delivers unprecedented choice and control of web-based content directly to the television without the need for a PC or set-top box.</p>

<p>Navigating VIZIO Internet Apps is simple on all three Cinemawide HDTV&trade; models, using the included Bluetooth Universal Remote with a built-in QWERTY keypad. State of the art wireless Internet access is available through built-in Dual-Band 802.11n Wi-Fi, allowing viewers to enjoy the convenience of on-demand movies, TV shows, social networking, music, photos and more with just the push of a button.</p>

<p><br />
<strong>The Best 3D Experience Gets Even Better ... And Wider</strong></p>

<p>VIZIO's Theater 3D&trade; technology uses circular polarization, similar to what is found in most 3D movie theaters. This technology offers a brighter, flicker-free image, handles fast motion without blurring, and has a wider horizontal viewing angle compared to "Active Shutter" technology.</p>

<p>By including four pairs of the lightweight and comfortable Theater 3D glasses with these TVs, VIZIO has eliminated two of the most common objections to 3DTV purchases: the need to wear bulky 3D glasses that require batteries or recharging and the need to invest in expensive additional 3D glasses so the entire family can enjoy it together. Two of the four pairs are specially designed to accommodate prescription eyeglass wearers. By incorporating all of the 3D processing into the TV instead of burdening the eyewear, as is the case with Active 3D, VIZIO Theater 3D enables users to wear comfortable, eco-friendly, battery-free lenses instead of Active Shutter glasses that are heavy, awkward, and require recharging and other maintenance.</p>

<p><br />
<strong>Support for the Widest Array of 3D Formats</strong></p>

<p>The entire Cinemawide HDTV line supports the widest selection of 3D formats to ensure compatibility across Blu-ray, broadcast, cable, satellite, and gaming.  This includes Frame Packing, Side by Side, Top and Bottom, plus SENSIO&reg; Hi-Fi 3D and the RealD Format.</p>

<p><br />
<strong>VIZIO's Leading LED Picture Quality</strong></p>

<p>The XVT3D500CM and XVT3D580CM utilize VIZIO's Smart Dimming Edge Lit Razor LED technology. Smart Dimming intelligently controls its array of LEDs, which are organized in 32 zones. Working frame by frame, based on the content being displayed, Smart Dimming adjusts brightness in precise steps down to pure black (where the LED is completely off). This cutting-edge technology minimizes light leakage and enables a Dynamic Contrast Ratio of 10 Million to 1, for blacker blacks and whiter whites.</p>

<p>The top of the line XVT3D710CM uses VIZIO's TruLED&trade; Full Array LED backlighting with Smart Dimming technology that is able to dim specific areas of the image, depending on what's on screen, resulting in the most incredible and life-like images that "pop" off the screen.</p>

<p><br />
<strong>Advanced Audio</strong></p>

<p>VIZIO Cinemawide HDTVs will feature SRS StudioSound HD - the ultimate all-in-one audio suite designed specifically for Flat Panel TVs. Years of excellence in audio, practical experience and patented technologies allow StudioSound HD to deliver the most immersive and natural surround sound ever using built-in TV speakers. The suite also delivers remarkably crisp and clear dialog, rich bass, an elevated sound stage and consistent, spike-free volume levels. StudioSound HD features optimized audio presets for movies, news, sports and music while also providing a built-in EQ toolset for peak audio performance.</p>

<p>VIZIO continues its leadership in bringing innovative technologies that transform and enhance the HDTV experience. The Cinemawide HDTV XVT3D500CM and XVT3D580CM will be available later this year.</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc., "Entertainment Freedom For All," headquartered in Irvine, California, is America's HDTV and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead in U.S. TV sales. Since 2007 VIZIO HDTV shipments remain in the TOP ranks in the U.S. and was #1 for the total year in 2009.  VIZIO is committed to bringing feature-rich consumer electronics to market at a value through practical innovation. VIZIO offers a broad range of award winning consumer electronics. VIZIO's products are found at Costco Wholesale, Sam's Club, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Fast Company's 6th Most Innovative CE Company of 2009, and made the lists of Ad Age's Hottest Brands, Good Housekeeping's Best Big-Screens, CNET's Editor's Choice, PC World's Best Buy and OC Metro's 10 Most Trustworthy Brands among many other prestigious honors. For more information, please call 888-VIZIOCE or visit on the web at <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>The V, VIZIO, TruLED, Extreme VIZIO Technology XVT, VIZIO Internet Apps, 480Hz SPS, 240Hz SPS, Thin Line, Smooth Motion, Razor LED, Smart Dimming, Theater 3D, Cinemawide HDTV, Entertainment Freedom For All, names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>SOURCE VIZIO, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2011  8:52 PM</b>
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
			<?=getComments(4125)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4125)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/vizio-unveils-ultrawidescreen-219-cinemawide-hdtv-led-lcds-for-the-ultimate-cinematic-experience.php" type="text/javascript" charset="utf-8"></script>
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