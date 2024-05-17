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
		AND e.entry_id = 4602";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4602 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4602 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4602";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-useful-gadgets-superflat-indoor-tv-antennas-do-they-really-work.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4602";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &ndash; Do They Really Work?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &ndash; Do They Really Work?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &ndash; Do They Really Work?" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &ndash; Do They Really Work?</title>
	<meta name="keywords" content="walltenna leaf, upper left, figure clockwise, clockwise upper, leaf antenna, antenna, antennas, walltenna, leaf, –, channel, ’s, uhf, stations, strong, test, wfm, both, cable, figure, much, kowatec, balun, elements, wbph" />
	<meta name="description" content="With the growing interest in &amp;acirc;��cutting the cord,&amp;acirc;�� finding an indoor TV antenna that actually works is not an easy job." />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &amp;ndash; Do They Really Work?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &amp;ndash; Do They Really Work?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-useful-gadgets-superflat-indoor-tv-antennas-do-they-really-work.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="With the growing interest in &amp;acirc;��cutting the cord,&amp;acirc;�� finding an indoor TV antenna that actually works is not an easy job." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4602', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-useful-gadgets-superflat-indoor-tv-antennas-do-they-really-work.php">HDTV Expert - Useful Gadgets: Super-Flat Indoor TV Antennas &ndash; Do They Really Work?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January  3, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=298&category=Broadcast">Broadcast</a></b>, <b><a href="/category.php?id=312&category=Sports">Sports</a></b>
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
          <p>Depending on you believe, Americans are fed up with ever-increasing cable TV bills and are bailing out by the thousands on channel bundles, opting for free, over-the-air HDTV and movies and TV shows streamed over Internet connections.</p>
<p> </p>
<p>Or maybe not.</p>
<p> </p>
<p>While there’s no question that a cord-cutting movement does exist, it’s hard to tell how big that movement really is. But the allure of dropping $50, $60, $70, or more from your monthly Kabletown bill is strong, and the recent battles between Time Warner and MSG network over rights fees only serve to highlight the inflationary spiral of pay TV services.</p>
<p> </p>
<p>If you live in a metropolitan area and have the major networks (CBS, ABC, FOX, and NBC), chances are you already have access to quite a bit of sports programming. Maybe not the 24/7 deluge from ESPN, but you do have NFL games through 2022, selected Major League Baseball games, the NBA Finals, the NCAA Final Four tournament, college football and basketball, and numerous golf and tennis tournaments. (Oh, and let’s not forget next summer’s London Olympics on NBC.)</p>
<p> </p>
<p>And if you aren’t into sports, that’s all the more reason to stop paying for programming you don’t watch. There’s still plenty of good prime time programming available for free, not to mention reruns of older cable network shows (<em>Curb Your Enthusiasm</em> was available recently on UHF channel 17 in Philadelphia).</p>
<p> </p>
<p>With that in mind, I recently tested a pair of flat TV antennas for indoor reception. The first is the MoHu Leaf antenna (<a href="http://www.gomohu.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','www.gomohu.com']);">http://www.gomohu.com/</a>) ,available direct from MoHu for $50 plus shipping, and the second is the Walltenna (<a href="http://www.walltenna.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','www.walltenna.com']);">http://www.walltenna.com/</a>) , sold by a company known as Urban Freedom LLC for $40 (also at online stores).</p>
<div id="attachment_1620" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1620" href="http://www.hdtvexpert.com/?attachment_id=1620"><img class="size-full wp-image-1620" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-1.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Figure 1. The Walltenna is transparent and flexible (and maybe not too attractive).</p></div>
<div id="attachment_1621" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1621" href="http://www.hdtvexpert.com/?attachment_id=1621"><img class="size-full wp-image-1621" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-2.jpg" alt="" width="600" height="400" /></a><p class="wp-caption-text">Figure 2. Mohu's Leaf antenna is also flexible, but opaque and a bit less inconspicious.</p></div>
<p>Both are marketed to cord-cutters. Both companies cite the trend away from pay TV services <em>“…as more and more viewers look for higher value alternatives”</em> and <em>“…and to get free from recurring monthly cable or satellite bills, high-maintenance rooftop antennas, or bulky tabletop models.”</em></p>
<p> </p>
<p>Do they work? I tested both recently for wall-mount and window DTV reception, alongside two other stalwarts – Kowatec’s UHF panel antenna  (discontinued) and Radio Shack’s model 15-1874 ‘budget’ TV antenna. Let’s see how they stack up.</p>
<p> </p>
<p>THE TEST</p>
<p> </p>
<p>My house isn’t in the best location for indoor DTV reception. Although it’s less than 25 miles from the Roxborough (Philadelphia) digital TV antenna farm, there is a slight hill and a bunch of tall trees in the way.  Only a couple of UHF stations (17, 26) and one VHF station (6) are strong enough to come through without separate amplification.</p>
<p> </p>
<p>The back side of my house looks north towards Allentown, which has DTV stations on channels 9, 39, and 46. And they’re not all that strong, either. In short, I have the perfect location to test these flat antennas – weak signals, but just strong enough to lock up a tuner.</p>
<p> </p>
<p>To quantify my tests, I looked at the received waveform for each DTV station on an AVCOM PSA-2500C spectrum analyzer. And I used Hauppauge’s WinTV Aero-M USB stick receiver to verify reception and get some screen grabs of the stations that came in reliably.</p>
<div id="attachment_1623" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1623" href="http://www.hdtvexpert.com/?attachment_id=1623"><img class="size-full wp-image-1623" title="Composite of Wall Positions MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Composite-of-Wall-Positions-MR1.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Figure 3. (clockwise from upper left) The Walltenna, Leaf, Kowatec, and RS 'budget' antennas in position.</p></div>
<p>THE CONTESTANTS</p>
<p> </p>
<p>MoHu’s Leaf antenna looks mysterious and ‘stealthy’ with opaque black and white sides, but hold the black side at an angle to a bright light and you’ll see exactly what’s going on under that “luncheonette counter menu” plastic housing: A pair of dipole antennas with X-shaped capacity hats at the ends.</p>
<p> </p>
<p>The Walltenna takes that design and makes it larger, except you can see exactly what’s embedded in the plastic – copper foil shaped much the same way as the Leaf antenna. It just doesn’t look as nice on the wall as the Leaf, but then again, some of the best antennas have little eye appeal. (In the eyes of us RF enthusiasts, however, they are things of beauty.)</p>
<p> </p>
<p>The significant difference between both antennas – and one which I figured ahead of time would give the Walltenna the edge in receiving more DTV channels – is that the elements on the Walltenna are electrically longer than the Leaf. This means the antenna should be resonant at lower frequencies.</p>
<p> </p>
<p>I should point out that neither antenna uses a traditional collinear dipole array, as many rooftop and wall-mount UHF antennas do. With a collinear design, the physical connection ‘crosses over’ from one dipole array to the next, so that each X-shaped dipole array is out of phase with the one behind and/or in front of it, creating a broadband response. In the case of the Leaf and Walltenna, the physical connection to each ‘X’ element remains on the same side of the antenna.</p>
<p> </p>
<p>Both antennas are designed to be stuck to a window or fastened to a wall. Mohu doesn’t provide mounting holes, but Walltenna does. On the other hand, Mohu has encased the coaxial cable connection to the antenna in a solid plastic block, while Walltenna simply solders a balun to the copper strips and attaches the balun to the plastic cover with a rivet.</p>
<p> </p>
<p>I do not like the latter method at all. First off, inserting a piece of metal between the balun legs at such close range de-tunes the balun lines. Secondly, the balun is stiff enough that it provides too much torque on the base of the antenna when bent – you must be careful not to put too much strain on the connector, and the supplied RG-6 cable jumper is too stiff and heavy for the balun.</p>
<p> </p>
<p>Mohu’s antenna comes with a long run of mini 75-ohm coaxial cable. This cable has higher signal losses per foot, but is much lighter and more flexible for indoor installations. Given the rough handling that such antennas are likely to receive, this is a much better approach.</p>
<p> </p>
<p>THE TEST: ROUND ONE</p>
<p> </p>
<p>My first test took place in an upstairs bedroom. I removed an oil painting and hung/clipped the antennas to the picture hooks. For comparison, I elevated the Kowatec and Radio Shack antennas and placed them in the same position. This wall position is on the part of my house closest to Roxborough.</p>
<p> </p>
<p>After scanning for channels, the Walltenna snagged a few expected stations and a few that were not. Channel 6 (WPVI) runs tons of power to overcome interference from nearby FM stations (Channel 6 is at 85 MHz, and the first strong FM channel in Philly is 88.5). So it wasn’t a surprise to lock up.</p>
<div id="attachment_1624" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1624" href="http://www.hdtvexpert.com/?attachment_id=1624"><img class="size-full wp-image-1624" title="WPVI Composite MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/WPVI-Composite-MR.jpg" alt="" width="600" height="464" /></a><p class="wp-caption-text">Figure 4. (Clockwise from upper left) Spectrum analyzer waveforms of WPVI-6 as received with the Walltenna, Leaf, RS 'budget,' and Kowatec antennas.</p></div>
<div id="attachment_1625" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1625" href="http://www.hdtvexpert.com/?attachment_id=1625"><img class="size-full wp-image-1625" title="WBPH-9 WHYY-12 Composite MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/WBPH-9-WHYY-12-Composite-MR.jpg" alt="" width="600" height="466" /></a><p class="wp-caption-text">Figure 5. (Clockwise from upper left) WBPH-9 and WHYY-12 as received using the Walltenna, Leaf, RS 'budget,' and Kowatec antennas.</p></div>
<p>Neither was WHYY-12, which also runs beacoup power now that they don’t need to protect channel 12 in Binghamton, NY. WHYY locked up just fine without dropout. WBPH-9 from Allentown was also rock steady.</p>
<p> </p>
<p>So were UHF stations WPHL-17 and KYW-26, also a couple of powerhouses. WCAU-34 was mostly reliable with the occasional ‘hit,’ as was WFMZ-46 from Allentown, another strong station. (WBPH-9 and WFMZ-46 antennas were on the wrong side of my house.)</p>
<div id="attachment_1626" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1626" href="http://www.hdtvexpert.com/?attachment_id=1626"><img class="size-full wp-image-1626" title="KYW-26 Composite MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/KYW-26-Composite-MR.jpg" alt="" width="600" height="455" /></a><p class="wp-caption-text">Figure 6. (Clockwise from upper left) KYW-26 as receivedon the Walltenna, Leaf, RS 'budget,' and Kowatec antennas.</p></div>
<div id="attachment_1627" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1627" href="http://www.hdtvexpert.com/?attachment_id=1627"><img class="size-full wp-image-1627" title="WFMZ-46 Composite MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/WFMZ-46-Composite-MR.jpg" alt="" width="600" height="455" /></a><p class="wp-caption-text">Figure 7. (Clockwise from upper left) WFMZ-46 as received on the Walltenna, Leaf, RS 'budget,' and Kowatec antennas.</p></div>
<p>I could see RF carriers from other stations, but none were strong enough to lock up the Aero-M tuner. Even so, this was impressive performance from a so-called “all band” omnidirectional antenna. What the designer got right was to make the antenna elements longer, which helps with gain at highband VHF frequencies (channels 7-13). But it can also degrade performance in the UHF spectrum – you never get something for nothing.</p>
<p> </p>
<p>By using a balanced line connection to the balun, that problem is overcome. At higher frequencies, only the dipole elements are active. At lower frequencies, part of the transmission line becomes part of the antenna. It’s a technique I’ve used for years on ham radio antennas and on my ‘ugly duckling’ UHF antenna prototypes from a decade ago.</p>
<p> </p>
<p>So, how’d the Leaf do? Not too bad, but it only pulled in channels 6, 12, 17, 26, and 46 reliably. Channel 9 was nowhere to be seen, while channel 34 suffered from constant breakup. Odd, considering the Leaf is primarily a UHF antenna design and WCAU’s signal on channel 34 is one of the stronger signals around.</p>
<p> </p>
<p>The fact that the Leaf pulled in both channels 6 and 12 is a testament to how much power both stations run.  This antenna also uses a balanced line feeder to its coaxial connection, which provides resonance over a wider range of frequencies.</p>
<p> </p>
<p>But the ‘X’ elements at the end of the balanced line are only 4.25” long, whereas the Walltenna ‘X’ elements are over 7” long.  So the Walltenna has a decided edge in reception of VHF signals.</p>
<p> </p>
<p>How about the two ‘control’ antennas? Kowatec’s panel antenna is usually a strong performer with UHF TV stations, but all it could receive reliably in the test position was WBPH-9, WCAU-34, and WFMZ-69. Radio Shack’s ‘budget’ antenna (UHF loop and rabbit ears) did marginally better, pulling in WPVI-6, WHYY-12, KYW-26, and WFMZ-46.</p>
<p> </p>
<p>THE TEST: ROUND TWO</p>
<p> </p>
<p>For the next part of the test, I hung or placed each antenna in a back bedroom window, facing north towards the Allentown and Bethlehem stations. Once again, channel scans were run using the Aero-M and screen grabs were taken of actual DTV waveforms.</p>
<p> </p>
<p>I didn’t expect to pull in much from this location, save for WBPH-9 and WFMZ-46. The Walltenna met those expectations and also pulled in KYW-26 as a bonus, off the side of the antenna. The Leaf antenna located the exact same stations with comparable reception results.</p>
<p> </p>
<p>The control antennas provided mixed results, but one did marginally better. Kowatec’s panel antenna snagged WPVI-6, WBPH-9, and KYW-26 (no sign of WFMZ-46 and its million-watt ERP signal), while the Radio Shack 15-1874 delivered WPVI-6, WBPH-9, KYW-26, and WFMZ-46.</p>
<p> </p>
<p>Obviously all of the antennas could have been placed more carefully for optimum results. But how many readers have access to a signal level meter, or a spectrum analyzer? I’m betting  not many. So my methodology of just picking an arbitrary antenna position yielded a fair set of results.</p>
<p> </p>
<p>CONCLUSION</p>
<p> </p>
<p>There’s definitely something to the Walltenna design, but it’s not black magic. Just make the elements bigger and you will approach resonance at lower frequencies. The X-shaped elements on the end act like capacity hats and do the trick! (A full wavelength @ 175 MHz – channel 7 – is 1.7 meters, while a full wavelength @ 665 MHz – channel 46 – is .45 meters.)</p>
<p> </p>
<p>The Mohu Leaf is a solid performer on UHF and will pull in the odd VHF station, if it’s strong enough. Both antennas are easily concealed, but take care in what you place them behind or near, as metallic surfaces will detune each antenna and the balanced feed line, degrading performance. (Tip: If a metallic surface is placed ¼ wavelength behind each antenna at the desired frequency, it will become more directional on the opposite side.)</p>
<p> </p>
<p>As for the control antennas, they held their own in at least one test, so I can’t say that either flat antenna had a distinct advantage over the Kowatec and Radio Shack entries. Where the flat antennas have the upper hand is in design – they’re easier to hide and to look at . (Although Walltenna should really take a page from Mohu and encase their product in an opaque plastic coating. )</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January  3, 2012  5:03 PM</b>
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
			<?=getComments(4602)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4602)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-useful-gadgets-superflat-indoor-tv-antennas-do-they-really-work.php" type="text/javascript" charset="utf-8"></script>
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