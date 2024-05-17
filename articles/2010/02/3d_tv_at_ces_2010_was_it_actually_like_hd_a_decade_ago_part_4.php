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
		AND e.entry_id = 3565";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3565 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3565 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3565";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-4.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3565";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)" />
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
	<title>HDTV Magazine - 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)</title>
	<meta name="keywords" content="decade ago, blu ray, local theater, ces –, ago part, home, content, viewing, glasses, consumers, experience, dtv, theater, decade, could, blu, ray, ago, quality, industry, image, may, local, new, make" />
	<meta name="description" content="My previous articles in this series have mentioned a few factors by which the 3D implementation effort is different from HD a decade ago, rather than similar, as some industry experts have expressed. 

In this fourth installment, I analyze how consumers could embrace the 3D effort, and I provide some ideas to help them evaluate the adoption of 3D for their particular application: Either as a replacement of HDTV, or as a value added feature for 3D content, whether it is sourced from satellite, cable, or Blu-ray." />
	<meta name="title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-4.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="My previous articles in this series have mentioned a few factors by which the 3D implementation effort is different from HD a decade ago, rather than similar, as some industry experts have expressed. 

In this fourth installment, I analyze how consumers could embrace the 3D effort, and I provide some ideas to help them evaluate the adoption of 3D for their particular application: Either as a replacement of HDTV, or as a value added feature for 3D content, whether it is sourced from satellite, cable, or Blu-ray." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3565', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-4.php">3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 4)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 25, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<div class="editorial">The following article is the latest in the "3D TV at CES 2010 – Was it Actually Like HD a Decade Ago?" series. Other articles in this series are as follows:<br> <ul> <li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_1.php" target="_blank">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 1)</a> <li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_2.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 2)</a> <li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_3.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 3)</a></li></ul></div> <p>My previous articles in this series have mentioned a few factors by which the 3D implementation effort is different from HD a decade ago, rather than similar, as some industry experts have expressed.  <p>In this fourth installment, I analyze how consumers could embrace the 3D effort, and I provide some ideas to help them evaluate the adoption of 3D for their particular application: Either as a replacement of HDTV, or as a value added feature for 3D content, whether it is sourced from satellite, cable, or Blu-ray. <b></b> <h2>Industry and Consumers </h2> <p>3D for the home arrived to a world of a matured digital TV technology and an established Blu-ray industry, neither of both were available upon HDTV introduction a decade ago.  <p>Would those technology advances make the 3D implementation easier than it was for HD? Perhaps, but those are only part of a possible success of 3D at home. The DTV transition has now been complete and about half of households have DTV. Those factors should also make the gradual acceptance of 3D easier, being a product based on similar digital video technology already accepted by millions of households.  <p>HDTV did not have these favorable factors in 1998, other than DVDs showing better than VHS in analog sets since 1996.  <p>However, this does not imply that consumers will rush to the stores and replace their sets with 3D sets.  <p>Other factors could make 3D less attractive to many viewers: Reduced light output, wearing dark glasses, higher delta cost, reduced resolution on content distributed by service providers, etc.  <h2>Complexity and Confusion </h2> <p>3D manufacturers, content creators, service providers, and consumers must productively leverage the decade of HD experiences to make 3D a product of quality, less confusion, and simplified installation/compatibility/connectivity, not just to make new revenue.  <p>The industry now has the opportunity to make 3D less complicated and more standardized than DTV was for consumers. With a distribution model not compromised in resolution, image quality, and bandwidth, as planned by satellite, cable, and terrestrial broadcast stations.  <p>Although what is happening is exactly the contrary, I understand it is due “mainly” to the higher signal requirements of 3D when transported over the legacy HD infrastructure that was not designed for 3D.  <p>While the industry may entice consumers with the idea of having the Avatar 3D experience of the local theater at home, it could be risky for the adoption of 3D when consumers turn from the relative darkness of the 3D content back to regular 2D viewing, and note the superior brightness they are accustomed to seeing in HD, the superior resolution (unless it’s a 3D Blu-ray), and a better overall punch in the image.  <p>Consumers cannot experience this comparison with the same content at the local theater. The “not-liked” risk of a consumer there is under $20 for a 3D ticket, the risk at home would be much more costly. Fortunately, 3D panels and 3D Blu-ray players can still be used for 2D, if the 3D honeymoon goes south.  <h2>Which Content to Watch, When? </h2> <p>Some proclaim that 3D will replace the current TV at home. I do not agree with the concept of watching everything in 3D using a TV feature that converts 2D content to 3D images.  <p>Most TV content made for 2D is not suitable for 3D viewing and would not be a pleasant experience at home viewing it on a constant basis (about 4 hours or more per day, as typical bean counters say TV is viewed by the public).  <p>Additionally, viewing all the 2D content converted to 3D by the TV could create visual fatigue and discomfort. Not to mention the effect of wearing the 3D glasses during the daily TV viewing, or having to double up and wear the 3D glasses over prescription glasses, and the sense of separation from other viewers that the dark glasses produce for what is otherwise a family event.  <p>However, the polarization of passive 3D versions may eventually be applied to prescription glasses, which would turn them into a limited-use piece and become even more expensive than active glasses.  <p>Even without headaches or irritating effects, the unnatural effect of the 3D conversions done by the 3DTV is also an issue. I experienced various conversions over the past few months that may be acceptable for very short viewing (i.e. a 30 second add, a musical video) but was unacceptable to me for longer periods, such as a whole program.  <p>Substandard 2D-to-3D conversions done on the fly by the 3DTV could potentially be a turn off for many viewers, and such rejection could transfer to true 3D content. I will discuss this subject in another article.  <p>However, some well-made content made specifically for 3D could be enjoyed at home occasionally if the conditions are right.  <p>Viewers would put their 3D glasses on, enjoy the different experience for the length of the feature, and when the content is over, they will take off the 3D glasses and resume their customary viewing of typical 2D content originally made for TV, including 2D movies, news, etc. This situation is comparable to viewing a special 3D feature in the local theater and return back home for 2D viewing.  <p>Starting this year consumers will have the chance to purchase new 3D displays, 3D Blu-ray players, 3D movies, and 3D services, to experience 3D in the privacy and convenience of the home.  <p>Could that again create a situation of patrons not frequenting the local theater because they have a better setup at home, now with 3D? Perhaps, for similar reasons home theaters were a success: No noise from adjacent patrons, no discourteous people text messaging or emailing on the distracting cell’s bright screen (not necessarily kids, like it happened to me during Avatar every 10 minutes), no stepping or sitting on bubble-gum or sticky soda, not to mention the convenience at home of being able to stop the movie to get (or get rid of) whatever your needs are, etc.  <p>In other words, it has the potential for a repeat and get the movie industry worried again, but for that to happen 3D has to establish itself at home in large volumes. I do not see that happening any time soon (or happening at all), and if that happens the movie industry has plenty of time to plan for the next method of attraction to the local theater, maybe smelling the scene, but without a nose adaptor on the 3D glasses, please.  <p><b></b> <h2>3DTV - Replacement or Addition? </h2> <p>I explore below some possible situations that consumers may face soon when considering 3DTV adoption:  <ol> <li>Would a household need <strong>more than one</strong> HDTV capable to view a true 3D program at home? Considering the limited availability of 3D content compared to 2D, and the sporadic occasion of 3D viewing, I do not see how multiple 3DTVs can be justified when the family can gather in front of the 3DTV capable for that occasion.<br><br>Other than video-gamers, 3D has been usually a movie/sport experience for larger screens, such as home-theaters. A typical home does not normally have more than one dedicated home-theater, although for many consumers the definition of home-theater extends even to a small TV, some speakers, and a beanbag.<br> <li>Would it be reasonable to convert a front <strong>projection home-theater</strong> to 3D, and buy a new 3D projector (or two), silver screen, external polarization filters, HDMI wiring rated for high bandwidth (if not already installed), 3D Blu-ray player, new 3D compatible A/V receiver, multiple 3D glasses for the family, etc. just for the viewing of an occasional 3D movie?<br><br>Some may reject the idea just by looking at the cost. I would rather qualify my response as follows:<br><br>Perhaps the conversion is reasonable, if:<br><br>a) It was time for replacing some major pieces of the HT system anyway, and <br><br>b) The Delta cost for adding 3D capabilities is reasonable (about 20% for me, maybe a different percentage for you, maybe zero after the initial period of early adoption), and <br><br>c) The 2D display quality is not compromised for the system to be 3D capable, and <br><br>d) The image quality of the new system for 2D is better than the current HDTV experience, and <br><br>e) Several reliable equipment reviews tested the 3D and 2D performance and demonstrated to be of high quality, on both. <br> <li>Would<strong> I replace a 2D</strong> panel<u> </u>for a 3D panel for the viewing of an occasional 3D movie/sport? Yes, but only if the 5 points above are met.<br> <li>Would I <strong>buy a new panel</strong> with 3D capabilities if needed?&nbsp; Yes, if the 3D panel version meets the criteria of b, c, and e above.&nbsp; Ignore e) if the purchase cannot wait for published reviews, but you are running the risk of later reading bad reviews of a set you already purchased.<br> <li>Would I accept <strong>wearing 3D glasses</strong> to view occasional 3D programs at home? Perhaps, but I have to experience 3D often at home to provide a categorical answer. </li></ol> <p>Although I am not thrilled to wear the 3D dark glasses as a videophile conscious of image quality, I may be able to concentrate in the content and enjoy the 3D experience at home such I did with Avatar at the local theater, and temporarily disregard the constraints of the 3D technology and the compromised distribution by service providers.  <p>I accept 3D better a) knowing that the 3D event trades some image advances for the experience of depth that my eyes would not see otherwise, and b) knowing that the system does not compromise the image quality of the 2D viewing I can experience when I want, perhaps even during the viewing of the 3D program to compare the same content in 2D Blu-ray. At which point my reaction will be: Magia! Se hizo la luz!  <p>Stay tuned to more coverage of 3DTV.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 25, 2010  9:04 AM</b>
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
			<?=getComments(3565)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3565)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-4.php" type="text/javascript" charset="utf-8"></script>
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