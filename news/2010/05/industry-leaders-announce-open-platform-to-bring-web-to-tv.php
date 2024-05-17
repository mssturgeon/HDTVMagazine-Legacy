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
		AND e.entry_id = 3756";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3756 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3756 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3756";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/05/industry-leaders-announce-open-platform-to-bring-web-to-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3756";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Industry Leaders Announce Open Platform to Bring Web to TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Industry Leaders Announce Open Platform to Bring Web to TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Industry Leaders Announce Open Platform to Bring Web to TV" />
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
	<title>HDTV Magazine - Industry Leaders Announce Open Platform to Bring Web to TV</title>
	<meta name="keywords" content="dish network, best buy, president ceo, network google, living room, google, web, experience, sony, content, intel, logitech, network, internet, video, platform, dish, new, experiences, world, entertainment, applications, best, open, ceo" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/google.jpg&quot; alt=&quot;Google&quot; height=&quot;47&quot; width=&quot;120&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Today at the Google I/O developer conference in San Francisco, leading industry players announced the development of Google TV — an open platform that adds the power of the web to the television viewing experience, ushering in a new category of devices for the living room. Intel, Sony, and Logitech, together with Best Buy, DISH Network and Adobe, joined Google (NASDAQ:GOOG) on stage to announce their support for Google TV.

Over the past decade, the Internet has created unprecedented opportunity for innovation and development across the world, but so far the web has largely been absent from living rooms. With Google TV, consumers will now be able to..." />
	<meta name="title" content="Industry Leaders Announce Open Platform to Bring Web to TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Industry Leaders Announce Open Platform to Bring Web to TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/05/industry-leaders-announce-open-platform-to-bring-web-to-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/google.jpg&quot; alt=&quot;Google&quot; height=&quot;47&quot; width=&quot;120&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Today at the Google I/O developer conference in San Francisco, leading industry players announced the development of Google TV — an open platform that adds the power of the web to the television viewing experience, ushering in a new category of devices for the living room. Intel, Sony, and Logitech, together with Best Buy, DISH Network and Adobe, joined Google (NASDAQ:GOOG) on stage to announce their support for Google TV.

Over the past decade, the Internet has created unprecedented opportunity for innovation and development across the world, but so far the web has largely been absent from living rooms. With Google TV, consumers will now be able to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3756', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/05/industry-leaders-announce-open-platform-to-bring-web-to-tv.php">Industry Leaders Announce Open Platform to Bring Web to TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>May 21, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>
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
				<p class="prtitle"> Industry Leaders Announce Open Platform to Bring Web to TV</p>

<center><i>Google, Intel, Logitech and Sony Join Together to Deliver Google TV Platform<br /><br />DISH Network, Best Buy, Adobe to Support Bringing Devices to Market</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/google.jpg" alt="Google" height="47" width="120" class="keyimg"><strong>SAN FRANCISCO--(BUSINESS WIRE)</strong>--Today at the Google I/O developer conference in San Francisco, leading industry players announced the development of Google TV — an open platform that adds the power of the web to the television viewing experience, ushering in a new category of devices for the living room. Intel, Sony, and Logitech, together with Best Buy, DISH Network and Adobe, joined Google (NASDAQ:GOOG) on stage to announce their support for Google TV.</p>

<p>Over the past decade, the Internet has created unprecedented opportunity for innovation and development across the world, but so far the web has largely been absent from living rooms. With Google TV, consumers will now be able to search and watch an expanded universe of content available from a variety of sources including TV providers, the web, their personal content libraries, and mobile applications.</p>

<p><br />
<strong>Search across TV, Web, and Apps</strong></p>

<p>Google TV is based on the Android platform and runs the Google Chrome web browser. Users can access all of their usual TV channels as well as a world of Internet and cloud-based information and applications, including rich Adobe&reg; Flash based content – all from the comfort of their own living room and with the same simplicity as browsing the web. When coupled with the Intel&reg; Atom&trade; processor CE4100, Intel's latest system-on-a-chip designed specifically for consumer electronics, the new platform will offer home theatre quality A/V performance. Sony and Logitech said they would be delivering products based on the new Intel Atom processor and running Google TV later this year. While Google TV is designed to work with any TV operator, at launch the user experience will be fully optimized when paired with DISH Network.</p>

<p>Google TV expands video choice from the hundreds of channels available today through a pay TV provider to the vast storehouse of video content available through the web and streaming videos. The Google TV experience is complemented by the ability to watch streaming video from leading content platforms, including Netflix, Amazon Video On Demand, and YouTube. Google TV will also have the capability to run apps from the Android Market.</p>

<p>To navigate the array of content that will now be available through a single device and on a single screen, Google TV introduces an integrated search experience to help viewers easily find relevant content across over-the-air and pay-TV channel listings, DVR, and the Internet, as well as a picture-in-picture layout to access multiple windows simultaneously. Google TV also features an innovative home screen to help viewers quickly organize their favorite content and personalize their TV viewing experience. Some of these features are only available with advanced integration from DISH Network.</p>

<p><br />
<strong>Broad Alliance of Industry Leaders</strong></p>

<p>Eric Schmidt, Google Chairman and CEO said, "We are very proud to be working with this distinguished set of partners, all of whom have decades of experience in hardware, design and retail."</p>

<p>Sony announced plans to introduce "Sony Internet TV," the World's first TV lineup incorporating the Google TV platform. The first models are planned to be introduced in the U.S. market in the Fall of 2010 with the lineup featuring both a standalone TV model and set top box-type unit incorporating a Blu-ray Disc drive.</p>

<p>Howard Stringer, Chairman, President and CEO, Sony Corporation said, "I am delighted to announce the unique alignment of Google's rapidly growing, open source Android platform with Sony's unparalleled expertise in the field of TV design and technology. The addition of 'Sony Internet TV' will further bolster Sony's comprehensive TV lineup and will fuse new levels of enjoyment and interactivity into the TV experience."</p>

<p>Logitech will introduce a companion box that brings Google TV to existing HDTV home entertainment systems, easily integrating with any brand of HDTV and set-top box. The companion box will incorporate Logitech's Harmony&reg; remote control technology, and will include a controller that combines keyboard and remote control capabilities. The company also has plans to introduce an HDTV camera and video chat for Google TV, along with additional choices for navigation and control, including apps to turn a smart phone into an advanced controller for Google TV and home-entertainment systems.</p>

<p>Gerald Quindlen, President and CEO, Logitech said, "We committed to Google TV early on because it aligns with our strategy to support open platforms that enable new immersive experiences in the digital living room. While Google TV enables seamless discovery of all your content, Logitech enables seamless control over how you experience that content. We look forward to continued collaboration with Google and the developer community to create new Google TV experiences that have yet to be imagined."</p>

<p>The Intel Atom CE4100 processor will power both the Logitech and Sony devices. Paul Otellini, Intel President and CEO praised the collaborative effort and said TV as we know it was being "reinvented." "Today marks the next step in the evolution of TV to Smart TV. TV's are becoming smarter as a result of the microprocessor and the Internet. Traditional TV programming will be merged seamlessly with the infinite amount of content on the Internet to enable every viewer to determine what they want to watch, when they want it. This is Moore's Law transforming television, powered by the performance of Intel microprocessors."</p>

<p>DISH Network has been a key partner with Google on advanced integration development for Google TV. The two partners began a joint trial over a year ago with more than 400 DISH Network and Google beta users. Based on the continuous feedback from the trial, Google and DISH Network have built the optimized Google TV experience that seamlessly integrates traditional TV, DVR and web content.</p>

<p>Charlie Ergen, Chairman, President and CEO of DISH Network, said, "Google TV marks the next evolution in television, and we are excited to be the first to partner with Google to bring this experience to our customers. Only DISH Network Google TV customers will be able to enjoy a unified search across TV, DVR and web; easily find related content; and manage their entire TV viewing experience. Additionally, the advanced integration will allow developers to create new and exciting applications to enrich the TV viewing experience."</p>

<p>Best Buy will bring their retail experience and consumer expertise to the project, with Google TV devices being sold at Best Buy locations nationwide later this year. "Every day, our 180,000 Blue Shirt store employees and Geek Squad Agents work with our customers to get them the best home theater experience possible," said Brian Dunn, CEO Best Buy. "We are thrilled about the new and exciting experiences smart TVs, like Google TV, provide to our customers - and we are looking forward to showcasing those experiences in our store and ensuring customers get connected to all the products and services that bring those experiences to life."</p>

<p>Finally, Adobe Flash Player 10.1 will be integrated directly into the Google Chrome browser on Google TV, enabling viewers to experience tens of millions of web pages with rich Flash content including games, animations, applications, videos, audio and more. Shantanu Narayen, President and CEO, Adobe said, "An open web ecosystem offers endless opportunities for creativity and innovation. Flash Player 10.1 extends the advantages of full web browsing and consistent, rich experiences to smartphones, tablets, netbooks and Internet-connected TVs. We're thrilled to be part of the Google TV initiative with other industry leaders who share a common vision of enabling access to the best web experiences possible."</p>

<p><br />
<strong>Opportunity for Developers</strong></p>

<p>The demonstration at I/O highlighted the unique opportunity developers have to help shape the future of Google TV. Today Google announced that they would soon release a set of TV specific APIs for web applications, encouraging web developers to begin building unique web applications for use on television sets. Later this year Google will also release an updated Android SDK, which will support applications built for Google TV.</p>

<p>Google also plans to open source the Google TV platform to help spur innovation in the industry and so that other developers can benefit from the project. The long term goal is to collaborate with the entire developer community to help drive entertainment in the living room forward and to introduce the next generation of TV-watching experience.</p>

<p>For more information about the project visit <a target="_blank" href="http://google.com/tv/">google.com/tv</a>.</p>

<p><br />
<strong>About Google</strong></p>

<p>Founded in 1998 by Stanford Ph.D. students Larry Page and Sergey Brin, Google's innovative search technologies connect millions of people around the world with information every day. Google's targeted advertising program provides businesses of all sizes with measurable results, while enhancing the overall web experience for users. Google is headquartered in Silicon Valley with offices throughout the Americas, Europe and Asia.</p>

<p><br />
<strong>About Intel</strong></p>

<p>Intel (NASDAQ:INTC) is a world leader in computing innovation. The company designs and builds the essential technologies that serve as the foundation for the world's computing devices. Additional information about Intel is available at <a target="_blank" href="http://www.intel.com/pressroom/">www.intel.com/pressroom</a> and <a target="_blank" href="http://blogs.intel.com/">blogs.intel.com</a>.</p>

<p><br />
<strong>About Logitech</strong></p>

<p>Logitech is a world leader in products that connect people to the digital experiences they care about. Spanning multiple computing, communication and entertainment platforms, Logitech's combined hardware and software enable or enhance digital navigation, music and video entertainment, gaming, social networking, audio and video communication over the Internet, video security and home-entertainment control. Founded in 1981, Logitech International is a Swiss public company listed on the SIX Swiss Exchange (LOGN) and on the Nasdaq Global Select Market (LOGI).</p>

<p><br />
<strong>About Sony</strong></p>

<p>Sony Corporation is a leading manufacturer of audio, video, game, communications, key device and information technology products for the consumer and professional markets. With its music, pictures, computer entertainment and on-line businesses, Sony is uniquely positioned to be the leading electronics and entertainment company in the world. Sony recorded consolidated annual sales of approximately $78 billion for the fiscal year ended March 31, 2010. Sony Global Web Site: http://www.sony.net/</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>May 21, 2010  7:24 AM</b>
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
			<?=getComments(3756)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3756)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/05/industry-leaders-announce-open-platform-to-bring-web-to-tv.php" type="text/javascript" charset="utf-8"></script>
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