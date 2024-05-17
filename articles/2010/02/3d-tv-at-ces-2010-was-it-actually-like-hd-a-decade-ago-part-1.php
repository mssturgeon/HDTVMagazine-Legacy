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
		AND e.entry_id = 3539";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3539 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3539 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3539";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3539";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)" />
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
	<title>HDTV Magazine - 3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)</title>
	<meta name="keywords" content="decade ago, blu ray, firmware upgrades, frame compatible, set top, hdmi, may, ces, bandwidth, content, resolution, firmware, decade, implemented, ago, industry, top, even, digital, could, blu, ray, using, actually, full" />
	<meta name="description" content="The 2010 International Consumer Electronics Show (CES) took place in Las Vegas between January 5 and 10 (the first two days were for the press). The show received approximately 120,000 attendees and 2500 exhibitors, according to preliminary estimates.

I visited all the exhibits relevant to audio and video, including the high-end audio exhibits at the Venetian and THE Show, and the usual non-CES high-end audio event, this year held at the Flamingo, in addition to 62 meetings I planned with companies, 90% related to 3D this year. If you wonder why, you might be the only one who did not see Avatar.

Although it was an exhausting effort, it was worth every minute of it. CES was a great opportunity to see ..." />
	<meta name="title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The 2010 International Consumer Electronics Show (CES) took place in Las Vegas between January 5 and 10 (the first two days were for the press). The show received approximately 120,000 attendees and 2500 exhibitors, according to preliminary estimates.

I visited all the exhibits relevant to audio and video, including the high-end audio exhibits at the Venetian and THE Show, and the usual non-CES high-end audio event, this year held at the Flamingo, in addition to 62 meetings I planned with companies, 90% related to 3D this year. If you wonder why, you might be the only one who did not see Avatar.

Although it was an exhausting effort, it was worth every minute of it. CES was a great opportunity to see ..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3539', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php">3D TV at CES 2010 - Was it Actually Like HD a Decade Ago? (Part 1)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 12, 2010</b>
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
				<div class="editorial">The following article is the latest in the "3D TV at CES 2010 – Was it Actually Like HD a Decade Ago?" series. Other articles in this series are as follows:<br /><ul><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_2.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 2)</a></li><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_3.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 3)</a></li><li><a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_4.php">3D TV at CES 2010 – Was it Actually Like HD a Decade Ago? (Part 4)</a></li></ul></div><p>Many thought that way. Short answer: Think deeper, beyond the 3D industry noise. <p> <h2>CES and 3D</h2> <p>The 2010 International Consumer Electronics Show (<a href="http://www.cesweb.org/">CES</a>) took place in Las Vegas between January 5 and 10 (the first two days were for the press). The show received approximately 120,000 attendees and 2500 exhibitors, according to preliminary estimates. <p>I visited all the exhibits relevant to audio and video, including the high-end audio exhibits at the Venetian and <a href="http://www.theshowlasvegas.com/">THE Show</a>, the usual non-CES high-end audio event, this year held at the Flamingo, in addition to 62 meetings I planned with companies, 90% related to 3D this year. If you wonder why, you might be the only one who did not see Avatar. <p>Although it was an exhausting effort, it was worth every minute of it. CES was a great opportunity to see and compare all 3D implementations, even those products that may not ever come to market, or those that would require a second mortgage, such as the 152” 3D plasma by Panasonic, which was outstanding indeed. <p>I spent 6 days viewing many types of 3D display devices, 3D formats, using passive, active, or no glasses, DirecTV 3D, Blu-ray 3D, etc. Make no mistake, the consumer electronic reincarnation of 3D has arrived, and 2010 will bring the first wave of products to market; although Mitsubishi 3D TVs may feel they were first already.  <p>I also attended several meetings with 3D industry experts and engineers, including a long 4hr meeting with the <a href="http://www.3dathome.org/default.aspx">3D@Home Consortium</a> joining manufacturers of 3D displays and chips, 3D content providers, HDMI, <a href="http://www.reald.com/">3D glasses providers</a>, etc. all with the common objective of accelerating the implementation of 3D for the home. <p>The consortium is a platform that helps its members share company and product information to avoid format wars, and duplication of efforts and standards, so they can have compatible products at the end.  <p>Although there are many matters of 3D that are not yet agreed upon, CES allowed me to confirm my writings about the <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">HD World Conference in NY</a> and at the <a href="http://www.hdtvmagazine.com/articles/2009/12/2010_international_consumers_electronics_show_ces_new_york_press_preview.php">pre-CES 2010 press conference in NY</a> last November 2009, regarding how 3D-for-the-home would be produced, distributed, and displayed by the consumer. <p> <h2>The Opportunity of 3D</h2> <p>3D has been a valuable opportunity for the motion pictures industry to boost their revenue, which was gradually decreasing over the years because patrons were not frequenting movie theaters as before. <p>The 3D movie Avatar has been a success at the theaters and has given new hope for the motion picture industry. The public is not only coming back to the local theaters but is also paying more for a 3D ticket. <p>3D is also beginning to motivate the consumer electronics industry. They hope to entice consumers with the good impression from the Avatar 3D experience so they purchase another digital TV, now with 3D capabilities, and another 3D service, 3D player, 3D Blu-ray discs, etc. after consumers recently invested in 2D HDTVs, HD services, HD players, and HD discs.  <p>One difference between both efforts is that 3D is not a product of a mandated digital transition like 2D-DTV was, but 3D is riding over the digital platform created by the transition.  <p>Even though the volume of sales of HDTVs over the past decade (<a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_can_you_help_part_3_tvs_vs_households.php">approximately 150 million DTVs</a>) should not be used as a measurement of expectation for a similar repeat of 3D HDTV sales, the industry dreams on the golden opportunity. <p>One main reason such expectation could not be the same is that most manufacturers plan to include the 3D capability mainly on their higher lines of TV models. Another factor is the high cost (relative to the TV) of several active-shutter 3D glasses for a typical family. <p>Even though most of the 150-million DTVs are non-high-end HDTVs, people interested in the higher-lines of products may be interested in having a 3D feature in the next product they buy. <p>However, those should not be mistaken as 3D adopters by market research bean counters; they may have chosen the higher HDTV model for its better quality, not 3D. <p>Some 3D estimates made by research firms seem based on sales projections of higher TV lines and may give the impression of a large consumer interest in 3D. For example, many misleading research reports concluded that x percentage of households view HD just because they bought an HDTV, when the equipment actually may not be setup to receive HD, intentionally or by lack of knowledge. <p>After 3D is on the market for while, I wonder how many consumers that view for the first time a well-recorded HD image on their new TVs would think they are viewing 3D without glasses. <b></b> <h2>Is 3D Actually Similar to HD a Decade ago?</h2> <p>Many experts in the HD industry, such as Mr. Gary Shapiro, president and CEO of the Consumer Electronics Association, compared the 3D CES movement of 2010 to the HDTV introduction in 1998.  <p>I admit that I felt the same on the surface when looking at the factors that make the two efforts similar, they are both digital technology, use digital distribution channels and digital displays, and they are similarly complex with multiple formats, standards, conversions, connectivity requirements, etc. <p>Nevertheless, when looking at beyond the technology demos at CES, several factors make the 3D effort very different to the HD introduction a decade ago. I mention just nine factors in this series, 5 detailed in this article (Part 1) and 4 in the next article (Part 2), coming soon after this one: <p><b>1) A variety of 3D content is expected from various sources since the introduction, </b>such as 3D Blu-ray pre-recorded media, 3D satellite, 3D cable, some 3D terrestrial broadcast talks, etc, as opposed to the very limited demo loops from PBS, Discovery, and HD-Net when HD started in 1998. However, this should not be interpreted such that 3D will have more content than HD on a permanent basis. <p><b>2) Cable, satellite, and terrestrial broadcast plan to distribute 3D, but using frame-compatible lower resolution formats. </b> <p>3D distributed content would have inferior image resolution compared to the display capabilities of most 3DTVs, such as the active-shutter 3DTVs. <p>This is the exact opposite to what HD experienced when implemented in 1998; HD content was available and distributed at its full 1080i resolution, but the displays were not capable of displaying that resolution for several years (analog HD-CRT RPTVs back in the late 90s and early 2000s). <p>In comparison to other 3D content sources, such as 3D Blu-ray, the picture resolution of the service providers would be half of the 3D standard recently approved by the Blu-ray Association for 3D pre-recorded format (using full 1080p resolution on the pair of HD images, one for each eye). <p>Many LCDs and plasmas soon to be released during this first year of 3D are capable of displaying the full 3D resolution using active shutter glasses. <p>This should not be a surprise. Service providers can make more revenue distributing additional content using the same or lower bandwidth, rather than delivering uncompromised quality using the necessary bandwidth. <p>If a satellite or cable provider would want to send two full resolution 3D images using the same compression they apply to 2D they may have to assign more bandwidth for that purpose. Bandwidth is a limited asset for their push-model implementation. <p>However, this may be an attractive model to some subscribers that may be willing to pay extra for a quality 3D VOD service, a concept that is being discussed, but is not what is happening. <p><b></b> <p><b>3) A widely adopted digital interface (</b><a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi_a_digital_interface_solution.php"><b>HDMI</b></a><b>) has been well established in the industry</b>, as opposed to the first 4 years of HD with only component analog connectivity and no content protection. <p>HDMI versions 1.3 and 1.4 have sufficient bandwidth (10.2 Gbps) to transport dual 3D images with full 1080p resolution even at 60 fps frame rate. <p>HD started with a component analog connection a decade ago, and was followed by the digitally compressed Firewire <a href="http://www.hdtvmagazine.com/glossary.php#IEEE1394">IEEE1394</a>/<a href="http://www.hdtvmagazine.com/glossary.php#DTCP+%28Digital+Transmission+Content+Protection%29">DTCP</a>, which later evolved into the digitally uncompressed <a href="http://www.hdtvmagazine.com/glossary.php#DVI+%28Digital+Visual+Interface%29">DVI</a> video-only connection, and the HDMI of today (carrying also audio), both with <a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a> content protection, and broadly implemented by the industry. <p><b></b> <p><b>4) Regarding 3D interoperability protocols, version 1.4 of HDMI</b> introduced in mid-2009 already has some 3D protocols for interoperability between 3D devices. Soon, version 1.4a will be released to include the 3D protocol for the top/bottom frame-compatible technique that several 3D service providers have interest in using, such as ESPN. <p>The new 1.4a version will make this technique mandatory for new-3D TVs to accept, together with the other formats, but a set-top-box supplied by an operator (such as satellite or cable) may only support the top/bottom technique.  <p>Additionally, a testing facility was recently announced for manufacturers to test HDMI features of version 1.4 implemented in their equipment, chips, and cables. <p><a href="http://www.hdtvmagazine.com/news/2010/01/cablelabs_develops_3d_test_support_opens_laboratory_for_3d_tv_technology.php">CableLabs has also offered a testing facility</a> (at no charge) for 3D interoperability of set-top-boxes, to make sure all equipment is compatible. <p>As mentioned in point 3, HDMI was not even available to help implement HDTV in 1998, so firmware upgrades of a non-existing product were not possible, making the HDTV implementation quite different to the flexibility 3D has in this matter. <p><b>5) Firmware upgrades could be applied to earlier HDMI versions to implement 3D protocols. </b>The firmware upgrade could be applied<b> </b>to version 1.3 hardware, which already has more than the bandwidth capacity needed to transport uncompressed 3D at full resolution for each eye (3D Blu-ray).  <p>Firmware updates of 3D protocols could also be applied to even earlier HDMI versions to enable older chips to transport frame-compatible 3D formats within the bandwidth limitations of HDMI 1.0 – 1.2 (4.95 Gbps). According to HDMI, companies can implement firmware upgrades to earlier versions without asking for authorization.  <p>A combination of hardware and software design allows a device to be upgradable for 3D language/protocols; however, many devices may require a whole system upgrade, not just the firmware applicable to the HDMI chip. If some of those upgrades are hardware-related, it could mean having to replace the device. <p>A manufacturer could have implemented a v1.3 chip/software that only met the device/functionality purpose, but may not be suitable for firmware upgrades, not only for 3D but also for any other missing functionality of the 1.3 HDMI spec. Blu-ray players are often made to receive firmware upgrades. <p>Provided an A/V receiver/switcher does not disturb the 3D signal into the HDMI stream, even without a firmware upgrade it may be capable of passing-through the 3D language received from a 3D-upgraded-set-top-box sending a frame-compatible 3D signal, to a 3DTV that is connected to an HDMI output of the receiver/switcher. <p>However, extracting the audio from the HDMI stream would also unpack the 3D language video part of the stream. When the A/V receiver repacks the video signal with the 3D language, it may have lost what the 3DTV expects to understand the 3D signal sent by the set-top-box.  <p>Additionally, a v1.2 chip installed into a device may have implemented more functionality than a device having a v1.3 chip. The HDMI version number does not tell the whole story of the implemented functionally. <p>Having 10.2 Gbps of bandwidth by specification (v1.3 or 1.4) does not mean the chip/device/software was implemented to handle that maximum bandwidth, nor does it mean that it would be capable of receiving any firmware upgrade, such as for 3D language/protocol functionality, just by version number implementation. <p>A manufacturer has a choice of making/installing a chip that supports no more than 1x1080p60 or making a chip that could go much higher to match the specification. <p>In theory, the full 10.2 Gbps of bandwidth of the HDMI specification for versions 1.3 or 1.4 is not actually required to implement 3D, not even the 3D Blu-ray full 1080p dual frames, because most of its content would be 2X1080p24 (film base) or 2X1080i60 (video source) for which the 4.95 Gbps bandwidth of v1.0 would be sufficient. Again, this is the spec; the manufacturer implementation of HDMI could have been more limited (and less costly) to meet the needs of the device. <p>This would allow cable, satellite, and broadcast service providers to reuse (covered in point 6 below) set-top-boxes to transport frame-compatible compressed 3D structures (top/bottom, side-by-side, checkerboard, etc.). Consult the 3D section of <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">the HD World conference</a> article of November 2009 for an early view on this subject. <p>None of the above flexibility was available when HDTV was implemented in 1998; the formats were set in stone (480i/p, 720p, and 1080i/p).<b></b> <p>In the <a href="/articles/2010/02/3d_tv_at_ces_2010_was_it_actually_like_hd_a_decade_ago_part_2.php">next article in the series</a>, I will mention four additional factors, highlighted in the following bullets: <p><b>6) Cable and satellite are planning to reuse existing STBs for 3D…</b> <p><b>7) All major display manufacturers have already implemented many digital technology advances in their HDTVs… </b> <p><b>8) Many 3D sets have included the ability to convert a 2D source image “on-the-fly” and displayed it as 3D…</b> <p><b>9) Viewing 3D with glasses reminds me of a decade ago, but for other reasons…</b></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 12, 2010 11:30 AM</b>
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
			<?=getComments(3539)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3539)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php" type="text/javascript" charset="utf-8"></script>
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