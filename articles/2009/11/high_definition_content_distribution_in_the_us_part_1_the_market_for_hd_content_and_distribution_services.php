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
		AND e.entry_id = 3387";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3387 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3387 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3387";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3387";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" />
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
	<title>HDTV Magazine - High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services</title>
	<meta name="keywords" content="million million, content distribution, internet hdtv, cable satellite, cumulative end, content, million, cable, distribution, internet, digital, iptv, hdtv, video, quality, service, broadcast, dtv, satellite, analog, tvs, network, channels, resolution, services" />
	<meta name="description" content="This series of three articles analyzes the current and future market for HD, the methods of distribution and the capabilities of the digital technology to distribute HD content to meet consumer expectations. This technology must also support other quantity oriented businesses and services that can potentially degrade the original HD vision and affect those that invested in HDTV equipment under the reasonable expectation of viewing uncompromised HD quality, not just digital. This is a dilemma of quantity vs. quality when DTV permits the implementation of both, sharing the same bandwidth. 

HD content is defined in this article as..." />
	<meta name="title" content="High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This series of three articles analyzes the current and future market for HD, the methods of distribution and the capabilities of the digital technology to distribute HD content to meet consumer expectations. This technology must also support other quantity oriented businesses and services that can potentially degrade the original HD vision and affect those that invested in HDTV equipment under the reasonable expectation of viewing uncompromised HD quality, not just digital. This is a dilemma of quantity vs. quality when DTV permits the implementation of both, sharing the same bandwidth. 

HD content is defined in this article as..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3387', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php">High Definition Content Distribution in the US (Part 1) - The Market for HD Content and Distribution Services</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November 23, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<h2>Introduction</h2> <p>This series of three articles analyzes the current and future market for HD, the methods of distribution and the capabilities of the digital technology to distribute HD content to meet consumer expectations. This technology must also support other quantity-oriented businesses and services that can potentially degrade the original HD vision and affect those that invested in <a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HDTV</a> equipment under the reasonable expectation of viewing uncompromised HD quality, not just digital. This is a dilemma of quantity vs. quality when DTV permits the implementation of both, sharing the same bandwidth.  <p><a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HD</a> content is defined in this article as video originally recorded by HD video cameras or transferred from a film source at <a href="http://www.hdtvmagazine.com/glossary.php#1080i">1080i/p</a> or <a href="http://www.hdtvmagazine.com/glossary.php#720p">720p</a> resolution. The distribution of HD is subjected to the demand for that level of quality, the preservation of quality throughout the distribution channels, the competition among HD content providers and a reasonable cost/benefit to consumers, among many other factors.  <p> <h2>Market for HD Content</h2> <p>To view HD content at its full resolution a DTV needs to be capable of displaying 1080i/p or 720p without downgrading the original resolution of the image.  <p>According to Consumer Electronics Association (CEA)’s 2008 estimates, a third of the 346 million TVs in the US are DTVs that were sold between 1998 and 2008 (<a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">113.7 million)</a>. Most of those sets are HD capable, but many are of only SD/ED quality (480i/p).  <p>The other two thirds of TVs (about 230 million) are analog TVs that would require a set-top-box from a cable/satellite/Telco service provider or a broadcast DTV tuner to convert digital broadcast to analog due to the analog broadcast <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">discontinuation of June 12, 2009</a>.  <p>The 230 million TVs should not be considered a market for HD content until they <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">are replaced by HDTVs</a>. At a <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">selling pace of 35 million</a> DTVs per year a full replacement could not happen earlier than 2014. However, most consumers replace TVs when and if becomes necessary so it could take much longer for all TVs to be replaced, which affects the HD content distribution market.  <p>According to the CEA and the <a href="http://i.ncta.com/ncta_com/PDFs/NCTA_Annual_Report_05.16.08.pdf">NCTA</a> (2008 data), the 346 million analog/digital TVs are installed in 112.8 million US households, 65 million households <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">subscribe</a> to <a href="http://i.ncta.com/ncta_com/PDFs/NCTA_Annual_Report_05.16.08.pdf">cable</a>, 32.8 million to satellite and Telco, and the remaining 15 million receive broadcast with an over-the-air antenna.  <p>HD content is being distributed by all of the methods above. The Federal Communications Commission (FCC) allowed cable companies to offer subscribers an analog feed or to offer set-top-boxes for analog subscribers to tune to digital tier channels if the cable company prefers to fully switch to digital. Satellite services (small dish DirecTV/Dish Network) have been digital since they started, and offered HD content since 1999.  <p>Only those cable/satellite/Telco subscribers with an HDTV and an HDTV set-top-box can view HD at its full resolution. Viewing HD content at lower resolution should not be part of the HD content distribution market.  <p>A Nielsen research estimated that 23% of households <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">“have an HDTV and view HD</a>” while the <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">CEA estimated </a>months earlier that 50% of households are “able to experience the reality of digital television", which is not the same.  <p>Based on my analysis of the DTV industry since the transition started in 1998 I estimate that approximately 50 million households (44% of 112.8 million) may have at least one HDTV set, potentially qualifying for receiving distributed HD content even when not “viewing” HD today.  <p>Lower resolution TV channels gradually migrate to an HD format and motivate subscribers to upgrade analog/digital services to HD tiers and buy/lease HD-DVR equipment. Table 1 below shows a yearly summary of DTVs sold (and to be sold) to dealers since the beginning of the DTV transition (source: CEA, July 31, 2008):  <table border="1" cellspacing="0" cellpadding="0"> <tbody> <tr> <td width="59"> <h5>Year</h5></td> <td width="514"> <h5>DTV sets sold to dealers</h5></td></tr> <tr> <td valign="top" width="59"> <p>2011 </p></td> <td valign="top" width="514"> <p>40.8 million (cumulative end of year 2011, 228.7 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2010 </p></td> <td valign="top" width="514"> <p>38.4 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2009 </p></td> <td valign="top" width="514"> <p>35.8 projected million</p></td></tr> <tr> <td valign="top" width="59"> <p>2008 </p></td> <td valign="top" width="514"> <p>32.6 estimated million (cumulative end of year 2008, 113.7 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2007 </p></td> <td valign="top" width="514"> <p>26.4 million (cumulative end of year 2007, 81.1 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2006 </p></td> <td valign="top" width="514"> <p>23.5 million (cumulative end of year 2006, 54.7 million)</p></td></tr> <tr> <td valign="top" width="59"> <p>2005 </p></td> <td valign="top" width="514"> <p>11.4 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2004 </p></td> <td valign="top" width="514"> <p>8 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2003 </p></td> <td valign="top" width="514"> <p>5.5 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2002 </p></td> <td valign="top" width="514"> <p>4.1 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2001 </p></td> <td valign="top" width="514"> <p>1.5 million</p></td></tr> <tr> <td valign="top" width="59"> <p>2000 </p></td> <td valign="top" width="514"> <p>0.6 million</p></td></tr> <tr> <td valign="top" width="59"> <p>1999 </p></td> <td valign="top" width="514"> <p>0.1 million</p></td></tr> <tr> <td valign="top" width="59"> <p>1998 </p></td> <td valign="top" width="514"> <p>0.0 million (the DTV transition started in November 1998)</p></td></tr></tbody></table> <h4></h4> <h2>HDTV Distribution Services</h2> <p><b></b> <p><u>Broadcast, Cable and Satellite</u></p> <p>Since its inception in the mid 1900s, TV traditionally performed a tune-and-display role in a world of broadcast-only tuning. In time, cable and satellite offered an alternative to broadcast content distribution.  <p>When premium content (e.g. HBO) arrived, in order to protect the investment and the effort of creating the content, service providers implemented security controls under a pay distribution model, which required the use of a STB to unscramble premium content, even if the analog TV might have been cable-ready.  <p>When turning the page from analog to digital, the video content distribution model grew with more features but also with <a href="http://www.hdtvmagazine.com/articles/2006/02/is_hdtv_complex_enough.php">more complexity</a> for equipment and connectivity, with CableCARDs, digital/analog conversions, image resolution controls, integrated digital DVRs, selectable output controls for <a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">content protection</a>, <a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php">digital audio</a> and <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php">video connections</a>, etc.  <p>While HDTV is part of that digital distribution model, HD was declared optional in the broadcast DTV system mandated by the US Government. That can potentially affect the market for HD content distribution.  <p><a href="http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">Integrated DTVs</a> have been gradually manufactured with internal over-the-air (OTA) tuners to comply with the FCC’s mandate proposed in 2002. Also in 2002 an agreement was made with the cable industry for DTVs to also integrate a QAM digital cable tuner for in-the-clear unscrambled programming.  <p>Some DTVs also included a CableCARD slot for the integrated <a href="http://www.hdtvmagazine.com/glossary.php#QAM+%28digital+cable+tuners%29">QAM</a> cable tuner to unscramble premium programming (e.g. HBO) without an STB. While digital cable STBs are bi-directional and support Video-on-Demand (VoD), Impulse Pay-Per-View (PPV), and cable supplied Electronic Program Guide (EPG), the cable tuners <a href="http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">integrated within</a> DTVs since 2003 are only uni-directional and cannot perform that STB functionality.  <p>Bi-directional integrated HDTVs with cable tuners (<a href="http://www.tru2way.com/">Tru2way</a>) were introduced in 2009 to support VoD and Impulse PPV without an STB, in addition to other two-way features.  <p><b></b> <p>Since 1998 DirecTV and Dish Network distributed satellite HD content with <a href="http://www.hdtvmagazine.com/glossary.php#MPEG-2">MPEG-2 </a>compression, including HD networks for their local markets. With the launching of additional satellites and a migration to more efficient MPEG-4 compression (which was claimed to be about 50% more efficient than MPEG-2) the satellite services <a href="http://www.hdtvmagazine.com/articles/2007/06/directv_-_the_march_to_100_national_high_definition_channels.php">increased the number</a> of HD channels (100+).  <p>Digital cable companies have also experienced considerable HD content growth over the past few years. However, the number of HD channels traditional cable companies offer has been generally lower than satellite due to the limited bandwidth of their coaxial network compared to adding dozens of transponders on new <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2007-satellite-cable-broadcast.pdf">satellite birds</a> sent to orbit, and implementing more efficient compression algorithms.  <p>Some service providers advertised their HD content as “1000+ program choices” (i.e. VoD movies) to fool subscribers as having more (24hr) HD channels than the competition. <p><u>Internet TV</u>  <p>Although Internet TV and IPTV share the basic concept of using Internet Protocol as a method of transmission for delivery of content, they should not be mixed as many casual journalists do.  <p>Shane kindly offered his contribution about this type of delivery:  <p>The term "Internet TV", or in this case "Internet HDTV", has become the commonly accepted term for referring to video content delivered via the internet. While the more technical readers may be aware that the internet is an IP network, Internet HDTV is not the same as IPTV.  <p>The primary differentiator between the two is that IPTV is delivered to the home over a "private" network. The programming provider controls delivery directly to the home (e.g. AT&amp;T's U-Verse). By comparison, "Internet HDTV" is delivered to the home over a "public" network, namely The Internet.  <p>Within the Internet HDTV category, video can be delivered in a number of ways:<br>- Streaming: YouTube, Hulu, Netflix<br>- Download (Progressive download): Apple TV, Microsoft Xbox Live, Video podcasts<br>- Peer-to-peer (P2P): VUDU  <p>For the consumer, the primary benefit of IPTV over Internet HDTV is quality. Since the content provider controls the signal and the network all the way to the home, it has complete control over the quality of the image.  <p>Cable or Satellite service, as well as closed-IPTV-networks, are services that are much more expensive to implement and maintain than using a basic Internet connection to view IP video from the open Internet, live or download.&nbsp; <p>On either service, if the content quality exceeds the transmission capacity (bandwidth) of the ISP service for live TV viewing, the option could be to wait for a download to be ready for later viewing, and that could be possible even with a dial-up connection over standard telephone wiring. <p><u>Internet Protocol TV (IPTV) </u> <p><a href="http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV </a>manages TV signals stored as digital files that can be distributed within packets using Internet Protocol (IP) within dedicated networks to the TVs at home.  <p>As mentioned above, although it uses a similar concept of Internet Protocol data packets, IPTV should not be confused with the transmission of video over the open internet, which in some cases is also (advertised as) HD quality, such as Hulu. <p>As opposed to unidirectional terrestrial DTV broadcasters, IPTV maintains a two-way communication with subscribers, which can generate new revenues from the same content by expanding the distribution to destinations other than regular antennas. <p>With new handheld/mobile devices implementing the recently approved DTV mobile broadcast standard it could be possible to broadcast/datacast unidirectional content to a portable device while it maintains a line-back communication with the broadcast provider using its non-DTV capabilities, such as the internet access offered by the cell phone service, closing the loop. <p>Some IPTV video companies deliver movies, TV shows and sports to subscriber’s PCs or handsets using the true IPTV concept. Others (miss)use the term IPTV to describe delivery service of triple-way telephone, high-speed Internet, and TV channels over a private hybrid network made of coax and fiber-optic cable, offering <a href="http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">most of the video</a> service over coax QAM cable, rather than as IP packets. Such service certainly does not follow the “pull” model mentioned below. <p>Some IPTV services supply only over-compressed live VoD, others only allow downloads for later viewing due to limited bandwidth for live HD, or offer real-time HDTV but with content that is severely re-compressed to fit bandwidth limitations, which is typically restricted and unable to meet the requirements of a live feed of “quality” HD. <p>Some service providers limit the number of simultaneous HD programs that can be viewed in a multi-TV household, or <a href="http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php">reduce the resolution</a> of simultaneous HD streams for secondary TVs when delivered in parallel to one active HD stream that is viewed on the main HDTV. Some systems delay the start of additional VOD movie requests when one is already active in the household. <p>A benefit of IPTV is that the infrastructure and system needs just enough bandwidth to deliver the selected (“pull”) content chosen by the viewer, as opposed to the traditional content distribution systems, where dozens of parallel HD channels are simultaneously delivered for the viewer to select one at the <a href="http://www.hdtvmagazine.com/glossary.php#STB">HD-STB</a> point. <p>In such case, an IPTV Telco may not need to upgrade the distribution infrastructure to add broader content variety to subscribers because an IPTV channel line-up could flexibly grow at the head-end independently of the distribution model. <p>In a similar manner, some cable companies are implementing Switched Digital Video (SDV) to deliver only the channel selected by the viewer (“pull concept”), facilitating channel line up growth at the head-end. <p>Although some companies <a href="http://www.hdtvmagazine.com/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">had trouble</a> on their implementations, IPTV is growing in Europe, Asia, <a href="http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">and the US</a>. <p>IPTV performance, such as image quality and channel change speed, should be monitored using QoS (Quality of Service) and QoE (Quality of Experience) techniques; the latter requires the viewer’s participation.  <p>Other IP methods of HD content distribution are the P2P (peer-to-peer) file sharing, and the BitTorrent protocol, capable of distributing HD content among a large number of viewers which PCs share the effort of parallel delivery without overloading the distribution capacity of the hardware/software infrastructure at the source. <p>Part 2 of this series analyzes the quality factors of HDTV content distribution.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November 23, 2009  9:27 AM</b>
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
			<?=getComments(3387)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3387)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/11/high-definition-content-distribution-in-the-us-part-1-the-market-for-hd-content-and-distribution-services.php" type="text/javascript" charset="utf-8"></script>
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