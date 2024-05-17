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
		AND e.entry_id = 66";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 66 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 66 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 66";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (5) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 66";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV: It\'s Going To Be Great...Or Is It?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV: It\'s Going To Be Great...Or Is It?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV: It\'s Going To Be Great...Or Is It?" />
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
	<title>HDTV Magazine - HDTV: It's Going To Be Great...Or Is It?</title>
	<meta name="keywords" content="advisory committee, new format, land mobile, advanced television, high definition, hdtv, format, fcc, industry, cable, satellite, formats, new, spectrum, years, video, could, sets, broadcasting, fiber, committee, television, consensus, testing, digital" />
	<meta name="description" content="This article is from Dr. Jeffry Krauss and was written back in 1988. You will see the extraordinary foresight from this noted author. First published in HDTV World Review. HDTV: It's Going To Be Great...Or Is It? Dr. Jeffrey Krauss..." />
	<meta name="title" content="HDTV: It's Going To Be Great...Or Is It?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV: It's Going To Be Great...Or Is It?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This article is from Dr. Jeffry Krauss and was written back in 1988. You will see the extraordinary foresight from this noted author. First published in HDTV World Review. HDTV: It's Going To Be Great...Or Is It? Dr. Jeffrey Krauss..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=66', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php">HDTV: It's Going To Be Great...Or Is It?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June  9, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p><em>This article is from Dr. Jeffry Krauss and was written back in 1988. You will see the extraordinary foresight from this noted author. First published in HDTV World Review.</em> </p>

<p><strong>HDTV: It's Going To Be Great...Or Is It?</strong><br />
Dr. Jeffrey Krauss</p>

<p>Over the past decade, a number of electronics products have migrated from the business environment to the consumer environment. Answering machines, large screen TVs and cellular phones are examples of products that gained acceptance in the consumer environment as their prices fell.</p>

<p>In the U.S. today, almost everybody has an answering machine at home. The next product to migrate from office to home will be the fax machine. Fax machines have become popular in Japanese homes, but the price is still too high in the U.S. Now that cellular telephones cost less than $500, there are growing numbers of families that have cellular telephones in two cars. Five years ago, most 26" or 27" TV sets were used for the business or industrial environment. Today, most new TV set purchases are in the larger screen sizes, because the price of this item has fallen below $500.</p>

<p>The market penetration of large screen TVs is one of the forces driving high definition television. With the big screen, the imperfections of our current TV format become more noticeable. For the first time since the introduction of color TV in 1953, consumers are demanding better picture quality.</p>

<p>HDTV Critical Factors</p>

<p>How successful will the next generation of television be? The answer is complicated and involves consumer, economic and technical considerations.</p>

<p>Only one thing is clear. Whichever HDTV format is chosen for the U.S., it will be a system that is unique to the U.S. Other countries will be adopting advanced television formats for satellite broadcasting. Only the United States is planning to adopt the new format for terrestrial television broadcasting. Only the United States will have a television distribution system that can deliver advanced television by terrestrial broadcasting, cable TV, satellite TV and optical fiber. And only the United States will have a system that is backward-compatible, in the same way that color TV was backward-compatible with black-and-white, so that consumers can continue to use their current TV sets.</p>

<p>For the consumer, there are two major questions during the first few years. Will the new format be good enough to justify the cost of a $3000 TV receiver? And how long will it take until there is enough programming produced in the new format to justify the cost? These are the same questions that were raised when color TV was introduced in the 1950s. Even though everyone agrees that HDTV is the most important advance in TV since color, some industry executives think the shift to HDTV will be far less significant than the shift from black-and-white to color.</p>

<p>For the economists, estimating market penetration for HDTV receivers is important and has already begun. Most estimates assume a 1% penetration eight years after the 1993 or 1994 start of HDTV broadcasting. One group of economists think that HDTV will achieve market penetration far more slowly than color TV did. They estimate a 20% penetration 10 years after the 1% penetration. By contrast, VCRs had a 20% penetration eight years after reaching 1%, and color TV had a 20% penetration only six years after reaching 1%. Other economists are more optimistic, of course. It is clear, however, that market penetration rate depends strongly on the cost of the consumer TV receiver.</p>

<p>On the technical side, there are many unresolved issues. One element of the debate centers around whether the new format will be "high definition television" or merely "advanced television." True high definition displays 1125 scan lines of video. In contrast, today's TV format displays only 525 lines. In addition, HDTV has a wider screen, so the display will be shaped more like the screen in a movie theater. The goal of HDTV is to have a TV format that is technically as good as 35mm movie film, with compact disk-quality audio. Advanced television (ATV) is anything that is better than our current TV system, but less than high definition.</p>

<p>Today's TV format uses radio channels of six MHz bandwidth. HDTV would need about 30 MHz per channel, unless the signal is compressed. Each of the formats that has been proposed involves compression, squeezing information that comfortably fits a 30 MHz channel into a channel of 12 or nine or even six MHz. In achieving compression, some of the sharpness may be lost. All the proposed formats have more picture scan lines than our current 525-line standard, but they don't all have 1125 lines. Some argue that 787 lines is enough, others argue for 1050 lines.</p>

<p>At least one proponent, Zenith, has said that the wider screen will make TV sets far more expensive than necessary, and the only goal should be improved sharpness. Others, however, say that consumers place a higher value on the wider screen than the improved sharpness. How much picture improvement is necessary to satisfy consumer demand? The technical experts can determine how much radio spectrum is needed to reach certain levels of improvement, but only the marketplace can determine whether the improvement is enough to justify the higher receiver costs.</p>

<p>Road Map to a Decision</p>

<p>How will we get to the next generation of TV? The FCC, which has the legal responsibility to make the decisions, has established an industry advisory committee to evaluate and test candidate HDTV formats and recommend which one should be chosen as the U.S. standard. The committee, with its various subcommittees and working parties, includes several hundred experts. There are numerous meetings every month. The FCC staff plays a minimal role in the activities of the advisory committee. Fifteen to 20 advanced television proposals were submitted to the advisory committee in September, 1988.</p>

<p>The next important step is testing of hardware. The proponents will have to submit hardware to be tested. The tests will cover both the perceived quality of the picture and sound, and the effect of interference on quality. Eventually, when testing is completed, the advisory committee will evaluate the results. Then it will make a recommendation to the FCC on which format should be adopted. What the FCC does then depends on the degree of industry consensus that has been reached. The FCC could move quickly to adopt the recommended format, or it could hold hearings, or it could reject all the work and start the process all over again. I will return to this point, because it appears to me that the FCC is not prepared to deal with a process that does not reach a consensus. And, I believe the chance of reaching an industry consensus is quite small.</p>

<p>Proposals</p>

<p>I mentioned that there were 15 to 20 proposals submitted. Some, according to experts, submitted proposals that appear to violate the laws of physics. One of the working parties of the FCC advisory committee held a full week review of proposed ATV systems in mid-November, 1988, where 13 proponents made presentations. A follow-on session was held in May, 1989.</p>

<p>About six to eight proposals are considered to be potentially viable, in the sense that they are likely to be presented in hardware form for testing. Of these, only Zenith, Philips, Sarnoff Labs and NHK expect to have hardware for testing by the end of 1989. Other proponents may have hardware available in 1990. But none of the systems has really stabilized, and all the proponents are continuing to refine their designs.</p>

<p>Testing</p>

<p>The FCC originally hoped that hardware testing could begin in late 1988. It now appears that testing will actually start in early 1990 and last one and one-half to two years.</p>

<p>The testing of TV broadcasting formats will be done by an organization called the Advanced Television Test Center (ATTC), which is funded by the commercial TV networks and the broadcast trade associations. The cable TV industry has formed its own research organization, Cable TV Labs, which is expected to contribute financial support to the ATTC test program. The ATTC is now in the process of finding laboratory space and has begun ordering the specialized, and very expensive, test equipment that will be used.</p>

<p>There remain, however, two controversies that need to be resolved before testing can begin. One deals with the format of input source material, and the other deals with audio.</p>

<p>Input Source Material</p>

<p>The input source material is defined as the programming that will be used for the testing and evaluation of each proposed format. In order that the tests be conducted fairly, the same test programming should be displayed in all the competing formats. However, the proposed formats are technically very different from one another. For example, the numbers of scan lines differ from one format to another, as do field and frame rates.</p>

<p>This means that the test programming must either be produced in several distinct technical formats, or must be converted to different formats, in order to be tested and displayed on the competing systems. When programming is converted from one format to another--the technical term is "transcoded"--sometimes impairments are introduced. Of course, none of the proponents is willing to risk having a system downgraded in the testing because of impairments caused by transcoding.</p>

<p>Audio Formats</p>

<p>Another unresolved controversy deals with testing of audio formats. The goal is to achieve compact disk-quality sound by digital processing and transmission of the audio. Two companies, Dolby and Digideck, have proposed specific audio coding formats. In addition, some of the video proposals include specific audio coding proposals as well. However, several of the video format proponents have simply set aside data channel capacity within their formats for digital audio. They are indifferent as to which digital audio format is used.</p>

<p>The unresolved questions are: 1) whether the testing of video should be done with audio signals present; 2) whether the video should be tested several times, in the presence several different audio signal formats; and 3) how to take into account the quality of the audio coding format when evaluating different video formats.</p>

<p>Reaching a consensus on these questions may be difficult, because there is the feeling that some possible decisions could give big advantages to one format proponent over another. This will be a significant test. If the industry advisory committee cannot reach a consensus on these questions, the likelihood of reaching a consensus on which format to adopt will be very slim.</p>

<p>FCC Decisions</p>

<p>Even though this process seems to have years to go before it ends, the FCC has reached some tentative policy decisions. First, the FCC has decided that the consumer's investment in current model TV sets must be preserved. Today's TV sets must not be made obsolete. TV stations must not be allowed to abandon those consumers with current TV sets. This means either that any new format must be compatible with current TV sets, or that TV stations will have to simulcast programming in both formats on separate TV channels.</p>

<p>Second, the FCC tentatively decided that TV stations could only have up to six MHz more, in addition to their current assignment of six MHz, for advanced TV broadcasting. Depending on the format that is eventually adopted, this additional six MHz might be used in two ways. For example, the TV stations might use it to augment the information that is being broadcast on their current TV channels. The augmentation channel would carry the increased resolution data, the side panels needed for the wider picture, and the digital audio. Next-generation TV receivers would receive two channels--the current TV channel plus the augmentation channel. The TV receivers would then combine them into a high definition picture. Meanwhile, the current TV channel would continue to be available for reception by old-format TV sets.</p>

<p>Or, the second six MHz could be transmitted as a separate, standalone high definition channel. New TV sets would receive only the new channels. The old channels would continue to be used to broadcast programming to old-format TV sets, at least for some interim period of time.</p>

<p>These two tentative decisions have eliminated from consideration the one system that is farthest along in development: the Japanese system known as MUSE E. This system will be used in Japan for satellite broadcasting, requires a nine MHz channel, and is incompatible with old-format TV sets. Because MUSE E has effectively been eliminated, scientists at NHK (the Japanese broadcasting administration) have had to go back to the drawing board. They have come up with designs that use less spectrum and are backward-compatible with today's TV sets.</p>

<p>A third tentative decision by the FCC is that only the VHF and UHF spectrum now allocated for TV broadcasting will be used for HDTV broadcasting. The FCC will not take away any spectrum from land mobile or microwave or satellite services and give it TV stations. All other spectrum is either too heavily used already, or technically unsuitable for TV broadcasting.</p>

<p>A final tentative decision made by the FCC deals with non-broadcast media. For satellite TV, cable TV and fiber optic distribution of video, the FCC has decided not to adopt a standard. The standard format that the FCC adopts will apply only to terrestrial TV broadcasting.</p>

<p>Cable and satellite formats will still have to be compatible in some respects with the broadcast standard, in order for a consumer to be able to use the same TV receiver to display programming from all sources. But cable and satellite systems are not as spectrum-limited as terrestrial broadcasting, so they may be able to use different formats to take advantage of their available spectrum.</p>

<p>The EIA is setting up a committee to develop a TV receiver architecture for HDTV that will be compatible both with terrestrial TV and non-broadcast media such as cable TV, satellite TV and fiber. This new receiver will probably be an evolution of today's TV receiver-monitors. It will be able to receive signals in TV broadcast format and also in what is known as baseband format from VCRs and other input sources. The EIA calls this a "multiport" receiver. This committee is controlled by the TV set manufacturers, and controversy could arise if it decides that functions now provided by cable TV converters and satellite TV receiver should be incorporated into future TV sets.</p>

<p>Future FCC Decisions</p>

<p>The FCC is expected to decide on a single format for terrestrial TV broadcasting. It is not expected to leave the decision to the marketplace, as it did with AM stereo. But the FCC is hoping for industry consensus. Without consensus, a decision will be difficult. I am concerned that the FCC will not be able to make the hard decisions in a timely fashion.</p>

<p>First, FCC staffing has decreased over the last five years. Staffing levels have fallen from 2200 to 1800, and could go below 1650 in the next year. Second, the FCC's staffing level for HDTV is distressingly slim. Only three or four staffers seem to be spending a significant part of their time on HDTV. This may pose no problem if the industry can reach a consensus on the hard issues, but it could be a disaster if there is no consensus.</p>

<p>Third, FCC has not committed the resources needed to make an independent decision. It does not have the test equipment or laboratory space to independently test the hardware. And, it is not prepared to independently analyze the test data that the advisory committee will produce.</p>

<p>Finally, the FCC has no contingency plan. Suppose the advisory committee, at the end of several years of deliberations, reaches the following conclusions: "Of the twenty or so systems tested, there are four that are superior. The four are equally good. We cannot reach a consensus on which should be the U.S. standard." Maybe this won't happen. And, if it does, maybe the four leading proponents will get together and devise a new format that is a synthesis of the best elements of each. But if no consensus can be reached, there are no procedures for forcing a synthesis.</p>

<p>In this scenario, the result could be years of delay, coming from a combination of administrative indecision and court appeals. Manufacturers would not commit to produce any TV sets in any new format while this uncertainty lasted. Even if this disaster does not come to pass, there is another serious problem lurking--limited spectrum. There might not be quite enough spectrum to satisfy all current TV stations in all markets.</p>

<p>The kind of problem that might then face the FCC can be illustrated by the following scenario: Suppose the advisory committee comes up with two formats that could be adopted. One requires a modest amount of additional spectrum, and it can be implemented at every existing TV station throughout the country. The second format provides better quality and a sharper picture, but requires more spectrum. This format can be implemented at every existing TV station throughout the country except in New York, Los Angeles and Chicago. In these three markets, there is only enough spectrum so that 75% of the TV stations can implement the new format.</p>

<p>Which format should the FCC choose? Should they pick the lower quality format, and penalize viewers throughout most of the country, for the sake of a few TV stations in three cities? Or should they pick the better quality format, and then decide which TV stations in the three big cities will be deprived of the opportunity to convert to the new format?</p>

<p>It seems likely that this kind of issue will arise. Spectrum is very scarce around the top five or ten cities, but more available everywhere else. That is a hard decision, and the FCC is not known for making hard decisions like this. Once again, the result could be years of delays caused by court appeals, particularly by any TV stations that think they will get a less desirable allocation than their competitors.</p>

<p>What the FCC won't decide is the question of standards for satellite TV, cable TV or fiber distribution of video. These industry segments will have the freedom and flexibility to choose other formats. This is really not much different than today, where satellite TV uses FM modulation rather than AM and uses digital sound in the horizontal blanking interval rather than analog sound as a subcarrier on the video. However, it is important that all these video distribution services are able to use the same display device. That means that whatever formats are eventually adopted by the cable TV and satellite TV industries need to be "friendly" and interoperable with the terrestrial standard.</p>

<p>HDTV Timetable</p>

<p>It appears that sometime in 1992 is the earliest date when the FCC could make a decision on a new TV standard. Hardware testing starts in early 1990, and could last up to two years. The advisory committee will make a recommendation to the FCC in late 1991 or early 1992; an FCC decision might appear six months later, sometime in 1992. The first HDTV transmitters and receivers might be available in 1994.</p>

<p>The Role of Congress</p>

<p>The Congress has grabbed onto HDTV because it is very sexy--it gets great press coverage. But Congressmen have only the faintest understanding of the technical issues in the debate. They understand what a TV display is, because they can touch it. They do not understand the problems of broadcast transmission. The issue that they can handle is "competitiveness." How will U.S. companies be able to compete against foreign companies? And what impact will this have on U.S. jobs?</p>

<p>In several Congressional hearings, witnesses have testified that participation in HDTV is crucial for the U.S. semiconductor industry. The semiconductor content of TV receivers will be higher in the future than today. And there will be spinoff products for the military, medical applications, air traffic control, and other industrial uses for HDTV.</p>

<p>The Congress wants to help U.S. companies avoid the fate of the VCR. That technology was invented in the U.S., but is now dominated by Japanese and other Pacific rim countries. There are Congressional proposals in the talking stage calling for Federal funding of research and development, for tax incentives, and for changes to antitrust laws that would allow competitors to join together to manufacture TV sets.</p>

<p>This is not another case of the U.S. vs. Japan, however. The largest market share for color TVs belongs to Thomson, the French company that bought the General Electric and RCA brands; Thomson has 22% of the market. Zenith is second with 12%. Philips, the European company that owns the Magnavox and Sylvania brands, is third with 10%, and Sony is fourth with 6%. All these companies manufacture in the U.S.</p>

<p>TV receivers are different than VCRs. Only about 10% of the cost of TV receivers today is in the semiconductor components. Cabinets and picture tubes make up a large part of the cost of color TV sets. Shipping costs are high for these components. So foreign-owned companies have established U.S. manufacturing plants. A study done for the EIA concludes that 13 million HDTV receivers will be purchased in the United States in the year 2003, and 92 percent of them will be made in the U.S.</p>

<p>The Congress is now starting to wrestle with this question: Should federal funding, tax breaks and other benefits be available only to U.S.-owned companies, or also to foreign-owned companies that have U.S. manufacturing plants and U.S. employees?</p>

<p>Congress depends upon a consensus to make decisions, even more than the FCC. It is possible that a consensus will emerge, but there is certainly no consensus yet on what action to take. Any Congressional action will be limited to the area of display technology, not the problems of setting a transmission standard.</p>

<p>How HDTV Will Affect TIA Members</p>

<p>The manufacturers that make up the membership of the Telecommunications Industry Association come from diverse parts of the telecommunications industry. As such, some will be affected by HDTV, some not. Some will have new business opportunities, some may see opportunities pass them by.</p>

<p>Land Mobile</p>

<p>One industry already affected is the land mobile industry. Land mobile was poised to get access to unused UHF television spectrum, when the FCC put a freeze on spectrum sharing. Now, that spectrum sharing possibility is dead.</p>

<p>But, land mobile might eventually turn out to be a winner. This possibility might happen if, for example, the Zenith format is chosen as the standard. The Zenith format fits the compressed HDTV signal into a six MHz channel that is incompatible with today's TV sets. This means that TV stations would have to simultaneously broadcast the new signal, as well as the old signal that is compatible with today's TVs. This simulcasting would only be needed for some transition period, say 15 to 20 years. At the end of the transition period, the FCC could decide to take away some of the TV spectrum now being used by TV stations and give it to land mobile. But this is a scenario with a 20-year horizon, so it does little to solve today's shortage of land mobile spectrum.</p>

<p>Fiber Optics</p>

<p>Fiber manufacturers and telephone companies think they have a stake in HDTV. The telephone companies assume that they will be delivering video to the home over optical fiber.</p>

<p>Telcos have asked the FCC to require that next-generation TV receivers have a connector port for a fiber optic link. That won't happen. First, the FCC probably doesn't have legal authority in this area. Second, the consumer electronics industry objects to anything that adds unnecessary costs to its products. But most importantly, the HDTV formats that have been proposed are analog formats. They could be converted to digital signals, but they would require huge data rates, 500 Mb/s or more. That would be too expensive. A compression format would be needed for digital HDTV, but nobody has yet proposed such a format. It is premature to talk seriously about a digital fiber optic connector on TV receivers, until the industry agrees to a digital HDTV compression format.</p>

<p>Actually, compressed digital HDTV might not be needed. Analog rather than digital transmission of video over fiber seems to be coming along quickly. The cable television industry plans to aggressively install fiber as a replacement for coaxial cable over the next decade. Cable industry plans call for analog transmission of video over fiber, not digital.</p>

<p>Video is inherently an analog signal, and digital coding is not yet at the point where it is equally efficient. Even with personal computers, the latest video interface standard, known as VGA, consists of analog signals. The earlier digital interface standards have been abandoned, because they cannot reproduce video with enough colors and enough resolution in the same bandwidth.</p>

<p>In Washington, everyone has a hidden agenda. The telco agenda is not that well hidden. The Bell Operating Companies' primary goal is to use the HDTV issue to help get out from under the Line of Business restrictions from the antitrust decision. Their secondary goal is to buy up the local cable companies, and eliminate a potential future competitor for telephone service. Finally, a lower priority is to bring fiber to every home. This will happen eventually, but it will happen faster if they can use cable TV service to share the costs.</p>

<p>This is a battle between telephone and cable TV giants. Manufacturers of fiber optic systems should not allow themselves to get caught in the middle in this battle between the telephone industry and the cable TV industry.</p>

<p>Satellite and Cable TV</p>

<p>The satellite and cable industries are potentially the big winners in HDTV. They have more technical freedom and flexibility than broadcast television. They will have programming available, since pay TV programmers like HBO are likely to be among the first HDTV programmers. They don't have to wait for an FCC standard if they can reach agreement on an industry standard.</p>

<p>The major technical question mark for cable TV is the effect on HDTV of the echoes and signal reflections in the cable. Cable TV systems have "close-in" echoes that produce ghosts that are very slightly separated from the actual image on the screen. The result is a softening of the picture sharpness that is usually not objectionable. It remains to be seen whether these ghosts will be a problem with HDTV signals.</p>

<p>Even though it will not be required, the cable TV industry will probably use the same HDTV format as terrestrial broadcasters, because it simplifies their technical system design. Initial demonstrations of some Japanese equipment seem to indicate that HDTV signals can pass down a cable system without impairment. But much more testing under controlled conditions will be needed.</p>

<p>Meanwhile, some cable operators are delaying their rebuilds until they know what performance specifications will support HDTV. This slowdown has had some effect on cable TV equipment manufacturers. At the same time, the apparent cable industry enthusiasm for analog fiber optics has created some important new marketplace opportunities for fiber optic manufacturers.</p>

<p>The satellite industry will almost certainly not use the same format as terrestrial broadcasting, because virtually all the proposed formats contain quadrature signal components that are incompatible with FM modulation. In Europe and Japan, satellite TV is the only way that HDTV will be distributed. Consequently, there are satellite formats available for use fairly quickly. But satellite is not totally free to choose any format it wants. Consumers will not be willing to buy two different TV receivers. The satellite HDTV format will have to be closely related to the terrestrial TV format, in terms of scan lines and other parameters.</p>

<p>In addition, the satellite TV industry includes some uncertainties. The recent failure of the HBO-GE partnership known as Crimson reflects this. Perhaps Hughes will launch a high power DBS satellite in the early 1990s, or perhaps the launch will be postponed. For now, however, the only satellite TV industry is the home dish market of 1-2 million subscribers. This is simply not a big enough business to become a leader in HDTV.</p>

<p>In my opinion, both cable TV and satellite TV will be followers, rather than leaders, in switching to HDTV. The leader will be either terrestrial TV, or possibly pre-recorded media such as laser disks. The role of pre-recorded media in HDTV remains to be seen.</p>

<p>Microwave</p>

<p>The microwave industry is also affected to some extent by HDTV. TV broadcast stations are major users of microwave, both for portable links used for live news coverage and fixed microwave links between the TV station studio and the transmitter tower. When they buy new HDTV transmitters, they will have to buy new microwave links. In the same way that broadcasters will need more TV spectrum for HDTV broadcasting, they will also need more microwave spectrum.</p>

<p>Unfortunately, the microwave spectrum doesn't seem to be available in most cities. The FCC has given very little thought to this aspect of HDTV. Perhaps the FCC can go to the Federal government and "borrow" some lightly-used government microwave spectrum for 20 or 30 years. Or perhaps this becomes an important new opportunity for the fiber optics industry. But it is a matter of concern that has not been given much attention.</p>

<p>Conclusion</p>

<p>The broadcasting industry used to be a stodgy, dull, mature industry. HDTV has changed all that. It has created excitement. There have probably been more advances in video technology, at least on paper, in the last two years than in the previous twenty. But for now just about everything is still on paper. The next three years will be even more exciting, as theory is translated into hardware, hardware is tested, test results are evaluated, and decisions are made.</p>

<p>My hope is that three years is the right timetable for a decision, not five years or ten years. My hope is that the decisions will be easy, not hard. My hope is that the industry will reach a consensus, in spite of all the forces lined up to make that difficult. My hope is that HDTV will create tremendous new opportunities for manufacturers and at the same time give consumers the improved quality they want.</p>

<p>Jeffrey Krauss<br />
Jeffrey Krauss holds a BS from the Illinois Institute of Technology and a PhD from Case Western Reserve University, both in physics. He is currently an independent consultant helping clients understand the impact of government regulations on new technologies.</p>

<p>Dr. Krauss has worked at Bell Laboratories as a technical staff member for network analysis and operations research. At American Satellite Corporation he was responsible for the economic modelling of satellite networks. He held the position of assistant chief at the FCC's Office of Plans and Policy, working on the policy impact of new technologies. He was vice president of corporate affairs at M/A-COM, Inc., a telecommunications equipment manufacturer, representing the company before the FCC, Congress, and other government agencies on telecommunications policy matters.</p>

<p>Dr. Krauss is a member of the FCC's Advanced Television Advisory Committee. He also is a member of the National Cable Television Association's Engineering Committee, and the Satellite Broadcast and Communication Association's Technical Committee. In addition, he is a member of the EIA's HDTV Subcommittee.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June  9, 2005  9:27 PM</b>
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
			<?=getComments(66)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 66)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php" type="text/javascript" charset="utf-8"></script>
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