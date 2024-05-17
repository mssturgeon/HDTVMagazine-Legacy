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
		AND e.entry_id = 1757";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1757 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1757 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1757";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/07/best-buy-and-tivo-form-strategic-alliance-to-transform-the-home-entertainment-experience.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1757";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience" />
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
	<title>HDTV Magazine - Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience</title>
	<meta name="keywords" content="best buy, user interface, tivo inc, tivo best, consumer electronics, tivo, best, buy, digital, user, experience, home, entertainment, interface, content, consumers, inc, services, platform, alliance, statements, cable, marketing, future, customers" />
	<meta name="description" content="TiVo Inc. announced it has entered into a strategic alliance with Best Buy Co., Inc. designed to transform the digital home entertainment experience. The alliance seeks to drive greater distribution of TiVo(R) products and its user experience, and to provide Best Buy with a digital platform in the home to strengthen its customer relationships. For Best Buy, the alliance is a major step intended to accelerate its evolution in digital media and better serve the needs of its customers. As part of the agreement, TiVo and Best Buy plan to investigate development of a unique user interface for TiVo DVRs purchased at Best Buy which would provide Best Buy a platform to more effectively market its digital content services, to regularly offer consumers trusted advice and guidance on the digital home experience, and to provide an ongoing dialogue with customers about Best Buy's various retail offerings.

Best Buy is making a commitment to TiVo to..." />
	<meta name="title" content="Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/07/best-buy-and-tivo-form-strategic-alliance-to-transform-the-home-entertainment-experience.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="TiVo Inc. announced it has entered into a strategic alliance with Best Buy Co., Inc. designed to transform the digital home entertainment experience. The alliance seeks to drive greater distribution of TiVo(R) products and its user experience, and to provide Best Buy with a digital platform in the home to strengthen its customer relationships. For Best Buy, the alliance is a major step intended to accelerate its evolution in digital media and better serve the needs of its customers. As part of the agreement, TiVo and Best Buy plan to investigate development of a unique user interface for TiVo DVRs purchased at Best Buy which would provide Best Buy a platform to more effectively market its digital content services, to regularly offer consumers trusted advice and guidance on the digital home experience, and to provide an ongoing dialogue with customers about Best Buy's various retail offerings.

Best Buy is making a commitment to TiVo to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1757', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/07/best-buy-and-tivo-form-strategic-alliance-to-transform-the-home-entertainment-experience.php">Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July  9, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=267&category=Marketplace">Marketplace</a></b>
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
				<p class="prtitle">Best Buy and TiVo Form Strategic Alliance to Transform the Home Entertainment Experience</p>

<center><i>Best Buy Embraces TiVo as Premier Digital Platform for Developing and Growing Best Buy's Digital Relationship with Consumers</i></center><br />
<br />

<p><B>ALVISO, Calif., July 9 /PRNewswire-FirstCall/ </B>-- TiVo Inc. (NASDAQ:TIVO) , the creator of and a leader in television services and advertising solutions for digital video recorders (DVRs), today announced it has entered into a strategic alliance with Best Buy Co., Inc. designed to transform the digital home entertainment experience. The alliance seeks to drive greater distribution of TiVo(R) products and its user experience, and to provide Best Buy with a digital platform in the home to strengthen its customer relationships. For Best Buy, the alliance is a major step intended to accelerate its evolution in digital media and better serve the needs of its customers. As part of the agreement, TiVo and Best Buy plan to investigate development of a unique user interface for TiVo DVRs purchased at Best Buy which would provide Best Buy a platform to more effectively market its digital content services, to regularly offer consumers trusted advice and guidance on the digital home experience, and to provide an ongoing dialogue with customers about Best Buy's various retail offerings.</p>

<p>Best Buy is making a commitment to TiVo to substantially increase the levels of marketing and merchandising of retail TiVo(R) DVR devices, as well as other devices that may feature the TiVo user interface and platform in the future. Best Buy believes that TiVo is a superior digital solution which enables consumers to maximize enjoyment and entertainment value from their high definition television sets by enabling them to easily navigate and experience the vast array of content options available through those sets. Moreover, Best Buy believes that TiVo's ability to deliver millions of pieces of content not available through cable or satellite, while enabling consumers to find content spanning broadcast to broadband creates a user experience based on one box, one remote, one user interface, and one stop shop content availability that renders the TV experience in a way that is both easy and intuitive for consumers.</p>

<p>Chris Homeister, Senior Vice President of Entertainment at Best Buy said, "Best Buy and TiVo together will open up a variety of new ways for consumers to get the most out of their entertainment experience, have more digital content choices, and get on-demand access to Best Buy's trusted perspective in consumer electronics."</p>

<p>"We recognize that our ability to get much closer to the customer in their enjoyment of digital services in the home is one of our greatest opportunities," Homeister continued. "TiVo's award-winning user interface, and its ongoing, innovation provides a unique combination of factors for Best Buy to further build its digital home presence."</p>

<p>In addition to delivering a superior entertainment experience, Best Buy intends to utilize TiVo's robust platform to deliver educational and marketing messages directly to consumers. "Customers continue to find new ways to access information and interact, on their terms, with brands they trust," said Barry Judge, Best Buy's Chief Marketing Officer. "The TiVo alliance will open a new channel of customer interaction - we look forward to pioneering new media together."</p>

<p>"We could not be more gratified with this important relationship with Best Buy, which is the world's leader in retail consumer electronics," said Tom Rogers, president and CEO of TiVo Inc. "We also could not be more gratified by Best Buy's recognition that TiVo is not simply another consumer electronics offering, but an ongoing service experience that can ultimately deliver on the goal of giving viewers anything they want, whenever they want it through their television sets. By virtue of TiVo having become not only a DVR, but also a media center combined with the only universal cable box that can plug into any cable system in the U.S., Best Buy will have the opportunity to both greatly expand the digital options and features available to its TV customers while also simplifying them at the same time. Moreover, we look forward to Best Buy significantly contributing to the vast array of content that is available through TiVo which can offer innovative ways to market it."</p>

<p>The key elements of the TiVo-Best Buy strategic alliance are:<br />
  <br />
<ul><li>Best Buy will seek to drive increased TiVo DVR sales through significantly increased levels of marketing and merchandising.</li><li>TiVo and Best Buy plan to investigate development of user interface features for TiVo DVRs sold by Best Buy that will provide a means of ongoing dialogue with consumers and enable easy access to Best Buy's growing assortment of digital services.</li><li>Best Buy will have access to TiVo's full suite of interface solutions including consistent presence in the TiVo Showcase area of the TiVo platform, providing it with a powerful communication tool for marketing innovative products to the TiVo subscriber base.</li><li>TiVo intends to work with Best Buy's Exclusive Brands group (e.g., Insignia, Dynex, etc.) to explore integration of its user interface, search, and other TiVo benefits to help further grow that consumer electronics line.</li><li>Best Buy and TiVo will investigate development of a series of consumer tips and insights that can be easily accessed for all kinds of digital home experiences, and Best Buy expects to explore opportunities with TiVo to provide unique Best Buy solutions that enable viewers to take greater advantage of transactional opportunities through the television set.</li><li>Best Buy expects to explore opportunities with TiVo to develop new features and offerings to ensure that the TiVo-Best Buy platform is at the forefront of new digital home entertainment offerings.</li></ul></p>

<p><br />
<B>About TiVo Inc.</B></p>

<p>Founded in 1997, TiVo Inc. (NASDAQ:TIVO) developed the first commercially available digital video recorder (DVR). TiVo offers the TiVo service and TiVo DVRs directly to consumers online at www.tivo.com and through third-party retailers. TiVo also distributes its technology and services through solutions tailored for cable, satellite, and broadcasting companies. Since its founding, TiVo has evolved into the ultimate single solution media center by combining its patented DVR technologies and universal cable box capabilities with the ability to aggregate, search, and deliver millions of pieces of broadband, cable, and broadcast content directly to the television. An economical, one-stop-shop for in-home entertainment, TiVo's intuitive functionality and ease of use puts viewers in control by enabling them to effortlessly navigate the best digital entertainment content available through one box, with one remote, and one user interface, delivering the most dynamic user experience on the market today. TiVo also continues to weave itself into the fabric of the media industry by providing interactive advertising solutions and audience research and measurement ratings services to the television industry. www.tivo.com</p>

<p>TiVo, 'TiVo, TV your way.', Season Pass, WishList, TiVoToGo, Stop||Watch, Power||Watch, and the TiVo Logo are trademarks or registered trademarks of TiVo Inc. or its subsidiaries worldwide. (C) 2009 TiVo Inc. All rights reserved. All other trademarks are the property of their respective owners.</p>

<p><br />
<B>About Best Buy Co., Inc.</B></p>

<p>With operations in the United States, Canada, Europe, China and Mexico, Best Buy is a multinational retailer of technology and entertainment products and services with a commitment to growth and innovation. The Best Buy family of brands and partnerships collectively generates more than $45 billion in annual revenue and includes brands such as Best Buy; Audiovisions; The Carphone Warehouse; Future Shop; Geek Squad, Jiangsu Five Star; Magnolia Audio Video; Napster; Pacific Sales; The Phone House; and Speakeasy. Approximately 155,000 employees apply their talents to help bring the benefits of these brands to life for customers through retail locations, multiple call centers and Web sites, in-home solutions, product delivery and activities in our communities. Community partnership is central to the way we do business at Best Buy. In fiscal 2009, we donated a combined $33.4 million to improve the vitality of the communities where our employees and customers live and work. For more information about Best Buy, visit www.bestbuy.com.</p>

<p>This release contains forward-looking statements within the meaning of the Private Securities Litigation Reform Act of 1995. These statements relate to, among other things, the future business and growth strategies of TiVo and Best Buy, future digital services from Best Buy and TiVo, future marketing and promotion activities of TiVo and Best Buy, enhanced user interface features on TiVo products sold by Best Buy, and future user interface that incorporates TiVo features for deployment on other consumer electronics devices sold by Best Buy. Forward-looking statements generally can be identified by the use of forward-looking terminology such as, "believe," "expect," "may," "will," "intend," "estimate," "continue," "plan," "seek", or similar expressions or the negative of those terms or expressions. Such statements involve risks and uncertainties, which could cause actual results to vary materially from those expressed in or indicated by the forward-looking statements. Factors that may cause actual results to differ materially include delays in development, competitive service offerings and lack of market acceptance, as well as the other potential factors described under "Risk Factors" in each company's public reports filed with the Securities and Exchange Commission, including each company's Annual Report on Form 10-K, Quarterly Reports on From 10-Q and Current Reports on Form 8-K. Each company cautions you not to place undue reliance on forward-looking statements, which reflect an analysis only and speak only as of the date hereof. Each company disclaims any obligation to update these forward-looking statements.</p>

<p>Source: TiVo Inc. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July  9, 2009  6:27 AM</b>
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
			<?=getComments(1757)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1757)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/07/best-buy-and-tivo-form-strategic-alliance-to-transform-the-home-entertainment-experience.php" type="text/javascript" charset="utf-8"></script>
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