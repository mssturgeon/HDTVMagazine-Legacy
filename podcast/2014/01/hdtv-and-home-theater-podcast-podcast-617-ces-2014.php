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
		AND e.entry_id = 5177";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5177 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5177 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5177";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-617-ces-2014.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5177";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #617: CES 2014" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #617: CES 2014" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #617: CES 2014" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #617: CES 2014</title>
	<meta name="keywords" content="inches kdl, home theater, ” ”, home automation, wireless hdmi, inches, home, series, wireless, tvs, new, audio, high, available, any, ultra, streaming, inch, ”, ces, samsung, smart, resolution, led, year" />
	<meta name="description" content="Early January every year, it seems the entire consumer electronics industry descends upon Las Vegas, NV for the annual pilgrimage to CES. We decided to make a virtual pilgrimage this year, but have a lot of exiting news and product announcements to share." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #617: CES 2014" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #617: CES 2014" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-617-ces-2014.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Early January every year, it seems the entire consumer electronics industry descends upon Las Vegas, NV for the annual pilgrimage to CES. We decided to make a virtual pilgrimage this year, but have a lot of exiting news and product announcements to share." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5177', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-617-ces-2014.php">HDTV and Home Theater Podcast - Podcast #617: CES 2014</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>January  9, 2014</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
<h3>Trends at this year’s CES Show:</h3>
<ul>
<li><strong>4K TVs</strong> - Actually we saw a lot of this last year as well. Perhaps Braden’s prediction of a 4K TV year may actually come true!</li>
<li><strong>OLED TVs</strong> - Again, we saw OLED last year too. But this year we saw larger sizes. LG is a big player here.</li>
<li><strong>Sound Bars</strong> - Now you may be seeing why we decided not to go to CES. Sound Bars are big because the speakers on most modern thin TVs are terrible. But its genius!! This now gives manufacturers an opportunity to sell another piece of gear.</li>
<li><strong>Wireless Speakers</strong> - Offerings from manufacturers like Sonos and some other companies that you have never heard of before as well made an impact this year.</li>
<li><strong>Smart Home/ Home Automation</strong> - Nothing new here! Wasn’t last year the year of home automation?? Even more manufacturers to get into the automation game this year.</li>
<li><strong>Higher Resolution Audio</strong> -  This is a new trend and we like seeing it. The only issue is that high quality audio typically comes a high cost.</li>
</ul>
<h4>Samsung</h4>
<ul>
<li>Samsung showed a 110” UHD TV -  We talked about the 85” model that forced a family to sell one of their daughters into slavery so they could pay the $40K pricetag. It looks like the 110 inch model won’t be produced. At least at this time. Its more of a concept TV brought to CES to show what they can do. Will TVs like this ever get down in price to the point where projectors are no longer necessary? They also demonstrated an 85” that is “Bendable”. Turn it on and the edges curve towards you. Turn it off and its flat!</li>
<li><a href="http://www.businesskorea.co.kr/article/2834/quantum-dots-samsung-unveil-secret-weapon-2014-international-ces">Samsung to Unveil Secret Weapon at 2014 International CES</a> - Samsung is reportedly planning to unveil its secret weapon, the V1 Bomb, a high-definition TV called Quantum-dot LED TV (QLED TV) at the 2014 International CES</li>
<li><a href="http://hometheaterreview.com/samsung-to-debut-wireless-streaming-speaker-home-theater-system-portable-stereo-and-two-tv-soundbars/">Samsung To Debut Wireless Streaming Speaker, Home Theater System, Portable Stereo And Two TV Soundbars</a></li>
<li><a href="http://ces.cnet.com/8301-35306_1-57616621/samsung-unveils-a-new-foundation-for-the-smart-home/">Samsung unveils a new foundation for the smart home</a></li>
</ul>
<h4>LG</h4>
<ul>
<li>LG unveiled twelve 4K televisions for 2014 - They introduced the 105-inch 4K 105UC9 that has an aspect ratio of  21:9 and is powered by the webOS Smart TV platform. If you can’t find any 4K content for these spiffy new TVs have no fear. LG has developed something called Tru-ULTRA HD Engine Pro.  Its supposed to improve overall Ultra HD picture quality. That along with a new proprietary upscaling chip will make SD and HD content look better on Ultra HD televisions. Well at least according to LG. On the Audio side of things, LG says it worked with Harman Kardon to develop premium audio systems for each TV. But seriously, with TVs like these are you really thinking about not using a receiver?</li>
</ul>
<p>The remaining lineup:</p>
<ul>
<li>LG UB9800 Series - 4K, WebOS, Tru-Ultra HD Engine Pro, IPS, passive 3D, 65, 79, 84, 98 inches</li>
<li>LG UB9500 Series - 4K, WebOS, IPS, passive 3D, 55, 65 inches</li>
<li>LG UB8500 Series - 4K, WebOS, IPS, passive 3D, 49, 55 inches</li>
<li>LG EC9800 - 4K, WebOS, OLED,  Bendable, 77 inches</li>
<li>LG EC9700 - 4K, WebOS, OLED, 55, 65 inches</li>
</ul>
<h4>Panasonic</h4>
<ul>
<li>TC-AX800U series - 4K, edge-lit local dimming, 65 and 58 inches. The 58 inch is available now. The 65 inch will be available in the spring.</li>
<li>TC-55AS680U &#8211; 1080p, 240Hz refresh rate</li>
<li>TC-AS650U series - 1080p, 3D, 60, 55, and 50. Cool touch pad remote! Available Today</li>
<li>TC-60AS660U series - 1080p, Home Theater System Bundle, Touch Pad Remote</li>
<li>TC-AS530U series - 1080p, 60, 55, 50, 39 inches Available Now</li>
<li>TC-A400U series - 1080p, 50, 39, 32 inches. Available Today &#8211; Entry Series</li>
<li>ZT80 Plasma 50, 55, 60, and 65 inches. Available in our dreams!</li>
</ul>
<h4>Sony</h4>
<p>All 4K TVs support Netflix 4K Streaming!</p>
<ul>
<li>XBR-X950B series - 4K resolution, direct LED local dimming 85, 65 inches</li>
<li>XBR-X900B series - 4K resolution, edge-lit LED local dimming, 79, 65, 55 inches</li>
<li>XBR-X850B series - 4K resolution, Triluminous display, 70, 65, 55, 49 inches</li>
<li>KDL-W950B series - 1080p resolution, edge-lit LED local dimming, 65, 55 inches</li>
<li>KDL-W850B series - 1080p resolution, Wedge design, 70, 60 inches</li>
<li>KDL-W800B series - 1080p resolution, 55, 50 inches</li>
<li>KDL-60W630B - 1080p resolution, Smart TV, 60 inches</li>
<li>KDL-W600B series - 1080p resolution, Smart TV, 48, 40 inches</li>
</ul>
<h4>Wireless Speakers</h4>
<ul>
<li>SRS-X9 -  2.1 wireless speaker with a bi-amped subwoofer, high-res audio files, Bluetooth, aptX, NFC, Wi-Fi, AirPlay, and DLNA.</li>
<li>SRS-X7 - Similar to the X9 but without high-res audio, Wi-Fi, NFC and aptX Bluetooth, Airplay and DLNA support, and compatibility with Music Unlimited, Spotify and TuneIn Radio</li>
<li>SRS-X5 - portable, built-in Wi-Fi, Bluetooth and NFC, and can double as a hands free speakerphone when paired with your smartphone.</li>
</ul>
<h4>Sharp</h4>
<ul>
<li>SHARP delivers the first WISA compliant Universal Player - The SD-WH1000U Universal Player is the first, Wireless Speaker and Audio (WiSA™) Association, compliant component to transmit uncompressed sound at 24-bit/96kHz and video at Full HD (1080p) — wirelessly. The Sharp Universal Player has already been named a 2014 International CES Innovations Design and Engineering Awards Honoree in the High Performance Home Audio category. The SD-WH1000U will have an MSRP of $3,999.99 and will begin shipping in the spring of 2014</li>
<li>Sharp Aquos Quattron Plus TV - At half the price of a 4K TV of the same screen size the Plus has 10 million more sub pixels than a regular 1080p TV. Retina Display for TVs?? The TV will accept 4K content and will scale 1080p content to make use of every one of the 10 Million sub pixels! Sharp claims they have put more TVs over 60 inches into American homes than any other manufacturer.</li>
<li>Sharp Sound Bar -  The HT-SB602, is designed to compliment 60&#8243; and larger flat panel televisions. Its a 2.1-channel, 310-watt sound bar system with a wireless subwoofer. It has built-in Bluetooth and can be paired using NFC.  This system can be set up horizontally either in front of a TV base (including an IR extender for the TV) or wall-mounted and features dual HDMI inputs and output, 3D sound support, Dolby and DTS decoding and an optical input. $500 available in the Spring.</li>
</ul>
<h4>Vizio</h4>
<ul>
<li>VIZIO announced its all-new P-Series Ultra HD Full-Array LED Smart TV collection. Featuring a backlight that consists of 64 Active LED Zones, HEVC Codec for Ultra HD streaming and VIZIO’s V6 six-core processor that combines a quad-core GPU and dual-core CPU for performance and speed. The VIZIO P-Series Ultra HD Full-Array LED Smart TV collection comes in 50”, 55”, 60”, 65”and 70” screen sizes. Prices start at $999.99 for the 50” model and go up to $2599.99 for the 70” model. Looks like these TVs will support Netflix 4K content.</li>
</ul>
<h4>JVC</h4>
<ul>
<li>JVC introduced three new Ultra HD LCD TVs. The 55-inch DM55UXA ($1,899.99), 65-inch DM65UXA ($2,499.99) and the largest JVC TV to date, the 85-inch DM85UXA ($9,999.99). These are Direct LED TVs.</li>
<li>JVC already announced their new line of projectors at CEDIA</li>
</ul>
<h4>Other Announcements</h4>
<ul>
<li><strong>Home Automation</strong>
<ul>
<li>ADT showed a package called <a href="http://www.adtpulse.com/">Pulse</a> that can be controlled by voice along with the traditional methods.</li>
<li>Belkin showed a device that controls anything than can be turned on and off via a DC switch called the <a href="http://www.belkin.com/us/pressreleases/8800549504060/">Maker Kit</a>. No pricing.</li>
<li><a href="http://www.lowes.com/iris?cm_mmc=COOP_IRIS-_-Lowe's%20Iris%20Brand-_-Branded%20General-_-lowes%20iris">Lowe’s</a> showed more devices from their Iris line of home automation</li>
<li><a href="http://www.canary.is/">The Canary</a> system uses HD video camera and safety sensors to track everything from motion, temperature and air quality to vibration, sound, and activity to help keep you, your family and your belongings safe.</li>
<li>Samsung SmartCam &#8211; Samsung&#8217;s new SmartCam HD and SmartCam HD Outdoor offer 1080p Full HD streaming and 128 degree ultra-wide angle lenses, giving consumers the ability to remotely monitor activity &#8211; both indoor and out &#8211; with full detail and clarity, via any computer or mobile device with no additional monthly video storage or monitoring fees. Outdoor model goes for $299.</li>
<li><a href="http://revolv.com/">Revolv</a> - Makers of the <a href="http://www.amazon.com/dp/B00EWJQ9PK/?tag=hdtvandhometh-20">$299 Home Automation hub</a> that unites differing protocols announced that their product will be available at Home Depot stores. Look for them by the end of January.</li>
<li><a href="http://www.netgear.com/service-providers/products/home-monitoring-automation/ip-cameras/">NETGEAR</a> - announced a couple of wireless IP Cameras that are HD. The HMNC100 and HMNC500 are 720p and support 802.11n dual band. We have requested an evaluation unit. Pricing and availability to come.</li>
</ul>
</li>
</ul>
<ul>
<li><strong>NETGEAR</strong> - NETGEAR had a few announcements some of the ones we thought were cool are: The AC750 Range Extender. Its supports 802.11b/g/n and ac and plugs into a wall outlet. You simply connect it to your network and then it creates a powerful hotspot! They also released a free Android app that analyzes your wireless network. Finally, NETGEAR showed the NeoMediacat HDMI Dongle. Its essentially an Android set top box in a USB stick. Its Miracast enabled so you can send content from your mobile devices to the connected TV.</li>
<li><strong>Polaroid</strong> - Introduced a 50-inch LED 4K Ultra HD TV (50GSR9000) for $999. They also introduced a 50-inch LED Smart TV (50GSR7100 ) that’s Roku Ready via an included Roku Streaming Stick that plugs into the sets MHL port. The Smart TV will sell for $599 US.</li>
<li><a href="http://www.mercurynews.com/business/ci_24853354/ces-2014-dolby-unveils-technology-improve-tv-brightness"><strong>Dolby unveils technology to improve TV brightness</strong></a> - On Monday, at the International CES gadget show, the company unveiled Dolby Vision, a technology that increases the brightness and contrast of TV sets. Prototype models will be on display from TV manufacturers such as Sharp and TCL. Standard TV sets emit about 100 nits &#8212; a unit of brightness roughly equivalent to one candle per square meter. As a reference, a 100-watt lightbulb emits 18,000 nits. Dolby says its prototype monitor can put out 4,000 nits.</li>
<li><a href="http://www.theverge.com/2014/1/3/5267360/intel-dual-os-pc-plus-android-windows-microsoft-objection"><strong>Intel plans a CES coup: Android and Windows in the same computer</strong></a> -  Internally known as &#8220;Dual OS,&#8221; Intel&#8217;s idea is that Android would run inside of Windows using virtualization techniques, so you could have Android and Windows apps side by side without rebooting your machine.</li>
<li><strong>Netflix</strong> -  Confirmed that it will stream House of Cards in 4K this year. This will only be available to 4K TVs that have a Netflix app embedded in them. Confirmed Samsung UHD TVs will have this capability.</li>
<li><strong>Roku</strong> - Announced a partnership with TCL and Hisense that will have the Roku player embedded into some models. The Roku will make money off of advertising that comes through the app. TV sizes will range between 32 and 55 inches.</li>
<li><strong>Channel Master DVR+</strong> - Wins Innovations 2014 Design and Engineering Award. DVR+ is a thin (1/2 inch high) device that allows consumers to receive and record local broadcast programming without a subscription or contract, utilizing a simple digital antenna. In addition, consumers have access to streaming video services and enhanced guide data with a broadband connection. For the TV Everywhere enthusiast, DVR+ is compatible with the Slingbox® 500, providing access to all live and recorded content on any connected mobile device, either around the home or around the world. DVR+, priced at $249.99, is available now from Channel Master at <a href="http://clicks.skem1.com/trkr/?c=2977&amp;g=4986&amp;p=89d1003d9f5f0122f7e4699123c6cc38&amp;u=bc841ab48f3b7a1e001b8859ecfa9c3c&amp;q=&amp;t=1">www.channelmaster.com</a></li>
<li><strong>HAL</strong>: The Next Generation of Home Entertainment System &#8211;  <a href="http://www.hal.tv/">HAL®,</a> the voice and gesture activated, remote control replacement that connects easily to a user’s TV, was demoed for the first-time ever at CES 2014. With HAL, users can change channels, set the DVR to record the latest episode of any show, browse the internet, stream movies, make video calls, play games and much more. With just the sound of a user’s voice or the wave a hand HAL, which stands for <a href="http://www.hal.tv/">Human Algorithm LTE, </a> is able to handle the following commands:
<ul>
<li>Stream movies and music on Netflix and Pandora</li>
<li>Change channels on any cable box by channel number or specific network</li>
<li>Control the volume of the TV</li>
<li>Set the DVR to record an upcoming show</li>
<li>Place video and phone calls over Skype or on a cell phone</li>
<li>Set picture-in-picture so users can answer a Skype call while watching a movie</li>
<li>Display a user’s Facebook feed and photos</li>
<li>Conduct internet searches</li>
<li>Show the latest viral videos from YouTube</li>
<li>Play Angry Birds or Fruit Ninja</li>
</ul>
</li>
<li><strong>Dish </strong>announces streaming app for PlayStation consoles &#8211; Have a PS3 or PS4 and a Hopper. Well now there is an app that turns it into a Joey. You can even use the game controller as the remote.</li>
<li><strong>Dish </strong>announces wireless Joey &#8211; An 802.11ac WiFi access point is used to create a closed network. You can connect up to two set-top boxes per access point.</li>
<li><strong>Phorus PR5 Receiver with DTS PLAY-FI</strong> - Making its debut at the 2014 International Consumer Electronics Show, the new Phorus (a DTS subsidiary) PR5 Receiver with Play-Fi will allow you to stream high-quality audio directly from a connected smartphones, tablets or PC, wirelessly to existing audio systems over a standard home Wi-Fi network, with zero loss in music quality. Compatible with nearly all AVRs, HTiBs, soundbars, and powered speakers, Play-Fi forges a seamless connection between audio systems, mobile devices and music, creating the ultimate infrastructure within the home to play your music from any device, in as many rooms as you want to listen to it. In addition to multi-room and multi-zone streaming from any device running the Play-Fi application, the Phorus PR5 Receiver with Play-Fi also supports Bluetooth(R) AptX(R), and AAC streaming, as well as direct streaming from iTunes on OSX and Windows.</li>
<li><strong>STEIGER DYNAMICS Introduces MAVEN</strong> - MAVEN, the most powerful custom-built HTPC in its segment, is setting a new standard for the modern living room. Due to its sleek design, ultra-silent operation, and easy integration with other home theater components, the systems blend perfectly into any existing setup. Combined with a large-screen Full HD or 4K TV, the MAVEN replaces numerous devices like Blu-ray players, DVRs, Desktop PCs and gaming consoles. Up to 12 TB of WD storage provide the capacity for 1,000 Full HD Blu-ray movies, 3 million MP3s, or 3,000 hours of HD TV recording. The integrated home server functionality allows the streaming of the entire media library to mobile devices. All components are designed for continuous operation and are carefully selected based on durability, performance, and quietness. MSRP $999</li>
<li><strong>Tivo demoed Network DVR Prototype</strong> - TiVo showed off a prototype of a network-based DVR. A network-based approach will also help cable operators and programmers manage complex content rights, enabling them to create catch-up TV services and other new tiers, and to splice targeted ads shows that are recorded in the cloud. TiVo has not announced any customers for its nDVR.</li>
<li><strong>Gefen Wireless HDMI Extender</strong> - The GefenTV Wireless for HDMI 60 GHz extender system sends high definition audio and video to any HDTV display up to 33 feet (10 meters). This wireless product is comprised of small table-top Sender and Receiver units. It supports resolutions up to 1080p Full HD, 3DTV, CEC, and 7.1-channels of High Bit Rate (HBR) lossless digital audio such as Dolby® TrueHD, DTS-HD Master Audio™. The Wireless for HDMI 60 GHz is specifically designed to transmit within a room. Its signal will not penetrate through walls, facilitating interference-free operation of multiple units in adjacent venues and close proximity. Line-of-sight placement of transceivers, however, is not necessary. Thanks to its small form-factor, high performance, and near-zero latency, this product is ideal for high-definition A/V extension within a conference room or home theater installation. Available now for $450.</li>
</ul>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2014-01-10.mp3">Download Episode #617</a></p><br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>January  9, 2014 10:58 PM</b>
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
			<?=getComments(5177)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5177)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2014/01/hdtv-and-home-theater-podcast-podcast-617-ces-2014.php" type="text/javascript" charset="utf-8"></script>
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