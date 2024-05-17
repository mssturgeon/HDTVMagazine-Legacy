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
		AND e.entry_id = 3727";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3727 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3727 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3727";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/05/hdtv-expert-classic-pete-contrast-shmontrast-2003.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3727";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)" />
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
	<title>HDTV Magazine - HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)</title>
	<meta name="keywords" content="dlp lcos, luminance values, luminance levels, plasma lcd, dynamic range, grayscale, contrast, lcd, projectors, color, levels, plasma, crt, black, light, ’s, luminance, monitor, images, high, gray, measured, projector, display, white" />
	<meta name="description" content="High contrast ratios don't make the display - grayscales do!" />
	<meta name="title" content="HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/05/hdtv-expert-classic-pete-contrast-shmontrast-2003.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="High contrast ratios don't make the display - grayscales do!" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3727', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/05/hdtv-expert-classic-pete-contrast-shmontrast-2003.php">HDTV Expert - Classic Pete: Contrast, Shmontrast! (2003)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>May  5, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=304&category=General Interest">General Interest</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p><strong><em>Editor’s note:  The performance of both projectors and direct-view plasma and LCD TVs and monitors has improved by leaps and bounds since this article was written. But the fundamental concept is still important – sequential contrast ratios may look impressive, don’t tell you anything about a TV, monitor, or projector’s grayscale performance.  Enjoy!</em></strong></p>
<p>************************************************************************************************************************************************************************************************</p>
<p>We love numbers. The bigger, the better. From horsepower to megabytes, from square feet to miles per hour; we use all kinds numbers to convey superiority of one product or system over another. Sometimes those numbers are based on facts and measured performance. Sometimes they are based on marketing hype.</p>
<p>It should be no surprise that the electronic display industries are subject to the same number-mongering that pervades the automobile, real estate, and computer sectors. Now that projectors are small enough, bright enough (in most cases) and have sufficient resolution for about 90% of their end-users, the latest craze is to play up the contrast. “2000:1!” we hear. “3000:1″ we read.</p>
<p>And this sort of creative number-mongering isn’t limited to projectors. For better or worse, the plasma and LCD manufacturers have gotten into the act, too. “3000:1! 4000:1″ Where will it all stop?</p>
<p>While there’s no question that contrast is certainly an important display attribute, it can be a very misleading number if used incorrectly. (Remember the ANSI lumens versus white lumens versus peak lumens debates among the projector crowd a few years back?)</p>
<p>The truth is, grayscale is the single most important attribute of any electronic display. Without shades of gray, we don’t have contrast. Without shades of gray, we can’t create wide color palettes. Grayscales are where it all begins when a projector or monitor first comes to life on the drawing board.</p>
<p><strong>YOU WANT COLOR WITH THAT?</strong></p>
<p>Those of us who evaluate and write about projectors and monitors are drawn to those displays that provide the most life-like images. That means the widest possible grayscale with a virtually unlimited number of color combinations created by an equal-energy light source, such as the sun. Anything else represents a compromise, but some of those compromises look pretty darn good!</p>
<p>Short of using a portable nuclear fusion system to power projectors, the next best thing is to employ short-arc lamps that ionize mixtures of gases and metal halides to produce blinding shafts of light. We then force these shafts through condensers and integrators, refract the primary colors out of ‘em, use those colors to create red, green, and blue images from monochrome light modulators, and finish up by precisely overlaying the RGB images to create full color pictures.</p>
<p>With flat panel monitors, we can force light from a cold-cathode light source (such as a fluorescent lamp) through a light shutter (AM LCDs) make up of pixels coated with tiny precision filters and get our color images tht way. Or, we can discharge electricity through pixels filled with a rare gas mixture (plasma) and watch as color phosphors are stimulated to produce RGB color imaging.</p>
<p>In the old days, color imaging was accomplished by tickling phosphors with an electron gun. Surprisingly, this system produced (and continues to produce) the most life-like images of all, which is why CRT front projectors are still preferred by a small number of high-end customers for home theater applications.</p>
<p>That’s because CRTs are capable of a wide grayscale and can show images with very low luminance levels (shadow detail) as well as very high luminance levels (highlights) in the same scene. More importantly, when a CRT is idling, it is essentially shut off. I mean REALLY shut off, as in black. Not a deep gray, as you’ll see with LCD, DLP, and LCoS projectors and AM LCD and plasma monitors.</p>
<p>Wile there have been tremendous advances in color imaging with flat panel displays, one stumbling block still remains. And that’s the ability (or inability) to show a grayscale with the widest possible dynamic range. In some systems, the resolution of the imaging device is limited by brightness (CRTs). In others, it’s limited by scattered or refracted light (DLP, LCoS, LCD).</p>
<p><strong>YOU MEAN THERE’S A BASEMENT?</strong></p>
<p>Black levels are also problematic. (The term “black level” is really an oxymoron, for there can only be one level of black, and that’s black or zero luminance. A better choice of words would be “shadow detail” or “low gray levels”.) When viewing content with relatively high luminance levels – say, 20% of white or higher – then we don’t see any problems with the display.</p>
<p>But movies and TV programs show with high-key lighting are a different story. If the monitor or display can’t resolve luminance values below a certain level (say, 10% of white), then any detail in the program content with luminance values at or below that level won’t be visible.</p>
<p>If we raise the black levels (sorry, low gray levels!) by adjusting the brightness control, then we also elevate the luminance levels at the high end and wind up compressing the grayscale at some point. Granted, we see more of the detail in the image, but not as the cinematographer or videographer intended.</p>
<p>And some funky things are now happening with the subtle shades of light that approach 100% gray, or white. They are beginning to blend together or “crush” into the ceiling of 100% gray. Our display no longer has wide dynamic range and we’ve also clipped our grayscale, reducing the number of shades of color that can be rendered.</p>
<p>If the grayscale capability of a CRT-based display could be likened to the number of floors in a house, that house would have a full-sized basement and a walk-around attic. LCD, DLP, and LCoS projectors will reduce that basement to a crawl space or eliminate it altogether, and the attic becomes a tight crawl space, too. We have less floors to work with and less space overall.</p>
<p><strong>MORE FUN WITH NUMBERS</strong></p>
<p>To better understand this concept, I selected a basic 16-step grayscale ramp from the DisplayMate test pattern series for illustration. All 16 steps are clearly seen in <strong>Figure 1</strong>, and this is how the grayscale would appear on a correctly-calibrated CRT display. Setting the step above black to about 6% of white results in a contrast ratio of about 440:1 on my Princeton CRT monitor. However, with a Samsung 42″ plasma, I measured only 60:1 contrast.</p>
<div id="attachment_530" class="wp-caption aligncenter" style="width: 414px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/05/Figure-1.jpg"><img class="size-full wp-image-530" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2010/05/Figure-1.jpg" alt="" width="404" height="324" /></a><p class="wp-caption-text">Figure 1. A typical CRT monitor grayscale.</p></div>
<p style="text-align: center;">
<strong><br /></strong></p>
<p>The difference? “Black” on the CRT monitor registered around .2 nits, while on the Samsung plasma “black” registered as 3.6 nits, or 18 times as bright. With a little playing around, I could expand the contrast ratio on the Samsung panel to 107:1, but “black” now measured 1.8 nits. Since my lower black level was limited by not having a ‘basement’ to speak of, the Samsung’s 16-level grayscale resembled that of <strong>Figure 2.</strong></p>
<p><strong></strong></p>
<div id="attachment_531" class="wp-caption aligncenter" style="width: 414px"><strong><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/05/Figure-2.jpg"><img class="size-full wp-image-531" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2010/05/Figure-2.jpg" alt="" width="404" height="324" /></a></strong><p class="wp-caption-text">Figure 2. A typical grayscale from a 2003-vintage 42-inch plasma monitor.</p></div>
<p>You can still see the 15<sup>th</sup> and 16<sup>th</sup> steps (barely) at the high end of the grayscale, but there’s no difference between steps 1 and 2 at the low end. This is a very typical grayscale rendering for plasma and LCD monitors. Keep in mind plasma and CRT displays have some degree of current limiting to prevent image burn-in and premature phosphor aging, and these circuits will limit contrast with images having high overall luminance values.</p>
<p>In the case of projectors that shutter or reflect light (this also includes LCD monitors), the value of white can be substantially higher than that of the combined steps 1 &amp; 2. That’s because the resolution of the projector is not affected by brightness levels, nor are the stability of the color dichroics as sensitive to luminance values. The result is high contrast levels (great for marketing) but a loss of shadow detail (not great for viewing).</p>
<p>In my November 2002 Projector Round-Up, I measured some projectors with exceptionally high contrast. Several of them exhibited peak contrast ratios much higher than the 440:1 measured on my Princeton CRT. But none could come close to the value of “black” that I measured on the Princeton set, and consequently the grayscale images they displayed didn’t have as wide a dynamic range below about 8% to 10% of white.</p>
<p><strong>Figure 3</strong> shows an approximation of the typical LCD, DLP, and LCoS projector grayscale. Of the plasma panels I have tested, only those made by Panasonic (also used in Fujitsu’s 50″ product) can produce “black” levels that approach that of a CRT, and subsequently display a grayscale with CRT-like shadow detail performance. The Panasonic panels typically produce a black level of .2 nits, equivalent to my Princeton CRT monitor.</p>
<p><strong></strong></p>
<div id="attachment_532" class="wp-caption aligncenter" style="width: 414px"><strong><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/05/Figure-3.jpg"><img class="size-full wp-image-532" title="Figure 3" src="http://www.hdtvexpert.com/wp-content/uploads/2010/05/Figure-3.jpg" alt="" width="404" height="324" /></a></strong><p class="wp-caption-text">Figure 3. A typical grayscale from a 2003-vintage LCD, LCoS, or DLP projector.</p></div>
<p>As a result, these panels create wide grayscales with nice color palettes. But they also do well in the contrast numbers game, although I’ve never measured the 3000:1 contrast that Panasonic has claimed in the past. Instead, my numbers (taken after the panel was calibrated for best grayscale) were in the 600:1 to 800:1 range.</p>
<p><strong>CONCLUSIONS</strong></p>
<p>So – just how much contrast do you need to see in an image? Empirical data suggests the human eye is limited to a dynamic range of 100:1 at any given instant. That means that if you look at a “scene” with objects of different luminance values, you won’t be able to discern more than a 100:1 difference between the darkest and lightest objects. Of course, the instant your eye moves, its built-in auto iris function raises and lowers the grayscale boundaries. That’s what allows you to perceive shadow detail and also pick out a white cat scurrying along in a field of snow.</p>
<p>If you are watching a movie on a plasma or LCD monitor, or with a front LCD/DLP/LCoS projector, you’ll probably be satisfied with the displayed images as long as there is not a preponderance of dark gray and black objects. But switch to a nighttime scene with high contrast lighting, and your eyes will strain to pick out any shadow details.</p>
<p>Obviously there’s a long way to go to improve the rendering of “low gray levels” on projectors and monitors, but there has been progress. In addition to Panasonic’s work with plasma, Texas Instruments has made enhancements to their digital micromirror devices (DMDs) to reduce light scattering and refraction. This in turn is dropping the value of “black” and improving both grayscale rendering and contrast.</p>
<p>Unfortunately, polysilicon LCD technology seems to be limited in this area. While projectors have gotten brighter and contrast has improved, black levels are still higher than those measured on DLP projectors by 100% or more. And LCoS imaging isn’t any improvement – the black levels I measured on a D-ILA projector were equivalent to several polysilicon models in the review.</p>
<p>Remember: Numbers are great for impressing people and can sustain a good argument for several hours. But peak contrast claims don’t tell you everything about performance of a projector or monitor when it comes to rendering images with life-like grayscales, only how much brighter the “whites” can be than the “blacks”. Caveat emptor…..</p>
<address>This article originally appeared in <strong><em>Video Systems</em></strong> magazine.<br /></address>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>May  5, 2010  4:49 PM</b>
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
			<?=getComments(3727)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3727)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/05/hdtv-expert-classic-pete-contrast-shmontrast-2003.php" type="text/javascript" charset="utf-8"></script>
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