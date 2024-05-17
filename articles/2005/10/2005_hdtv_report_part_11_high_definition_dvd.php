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
		AND e.entry_id = 225";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 225 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 225 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 225";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-11-high-definition-dvd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 225";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 11: High Definition DVD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 11: High Definition DVD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2005 HDTV Report, Part 11: High Definition DVD" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 11: High Definition DVD</title>
	<meta name="keywords" content="blu ray, dual layer, ray disc, high definition, red laser, dvd, blu, ray, disc, discs, layer, format, players, content, player, sony, mpeg, recorder, evd, video, laser, rom, announced, dual, formats" />
	<meta name="description" content="This part summarizes the main aspects of Hi Def DVD, such as formats, studio support, types of discs, competition from China, introduced models, copy protection, audio and video codecs, etc.  The complete review of the State of the High Definition DVD Technology has been covered in an article I recently wrote for the DVDetc magazine, please consult www.hdtvetc.com for access to that information, they also have an online service.  Regarding video, both groups/formats (Blu-ray and HD-DVD) selected MPEG-2, MPEG-4 H.264, and VC-1 (originally known as Microsoft's WMV-9 and VC-9) as mandatory video codecs for players; discs would have to be encoded in at least one of them.  VC-1 is now an open standard and was voted by 19 companies from the DVD Forum steering committee as best in picture quality.  According to Microsoft, the company was to remain neutral regarding format support, and we are starting to see differently lately (4Q05) due to the networking capabilities and copy protection features of HD DVD.  Regarding audio, HD DVD and Blu-ray groups approved Dolby
Digital 5.1 and DTS 5.1 as mandatory for HD players; pre-recorded discs must include at least one of the formats, at the election of the content provider.  Later, the DVD Forum decided to include also Dolby Digital +... (more in the article).  Several companies announced enhanced HD products such as combo discs and high capacity HD discs.  With the introductions from JVC and Toshiba movie buyers will save when purchasing a combo disc, play it as SD on today's DVD players, and later play it as HD in their future HD DVD player. Production costs and manufacturing are similar to DVD; studios and distribution chains would also be benefited when not dealing with two versions of the same movie. Last year at CES I met with a Chinese manufacturer of the EVD player, the Chinese Hi Def DVD, the company was one of the nine Chinese electronics manufacturers that made an EVD industry alliance in 2003 to develop and promote EVD players.  EVD is not alone anymore; the Chinese industry has grown to four formats now.  The four formats use red laser: High-Definition Videodisc (HDV), High-Definition Versatile Disc (HVD), Enhanced Versatile Disc (EVD), and in November 2004, the Forward Versatile Disc (FVD) was introduced in Taiwan.  Are you ready for another format war?" />
	<meta name="title" content="2005 HDTV Report, Part 11: High Definition DVD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2005 HDTV Report, Part 11: High Definition DVD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-11-high-definition-dvd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This part summarizes the main aspects of Hi Def DVD, such as formats, studio support, types of discs, competition from China, introduced models, copy protection, audio and video codecs, etc.  The complete review of the State of the High Definition DVD Technology has been covered in an article I recently wrote for the DVDetc magazine, please consult www.hdtvetc.com for access to that information, they also have an online service.  Regarding video, both groups/formats (Blu-ray and HD-DVD) selected MPEG-2, MPEG-4 H.264, and VC-1 (originally known as Microsoft's WMV-9 and VC-9) as mandatory video codecs for players; discs would have to be encoded in at least one of them.  VC-1 is now an open standard and was voted by 19 companies from the DVD Forum steering committee as best in picture quality.  According to Microsoft, the company was to remain neutral regarding format support, and we are starting to see differently lately (4Q05) due to the networking capabilities and copy protection features of HD DVD.  Regarding audio, HD DVD and Blu-ray groups approved Dolby
Digital 5.1 and DTS 5.1 as mandatory for HD players; pre-recorded discs must include at least one of the formats, at the election of the content provider.  Later, the DVD Forum decided to include also Dolby Digital +... (more in the article).  Several companies announced enhanced HD products such as combo discs and high capacity HD discs.  With the introductions from JVC and Toshiba movie buyers will save when purchasing a combo disc, play it as SD on today's DVD players, and later play it as HD in their future HD DVD player. Production costs and manufacturing are similar to DVD; studios and distribution chains would also be benefited when not dealing with two versions of the same movie. Last year at CES I met with a Chinese manufacturer of the EVD player, the Chinese Hi Def DVD, the company was one of the nine Chinese electronics manufacturers that made an EVD industry alliance in 2003 to develop and promote EVD players.  EVD is not alone anymore; the Chinese industry has grown to four formats now.  The four formats use red laser: High-Definition Videodisc (HDV), High-Definition Versatile Disc (HVD), Enhanced Versatile Disc (EVD), and in November 2004, the Forward Versatile Disc (FVD) was introduced in Taiwan.  Are you ready for another format war?" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=225', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-11-high-definition-dvd.php">2005 HDTV Report, Part 11: High Definition DVD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 20, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p>The complete review of the State of the High Definition DVD Technology has been covered in an article I recently wrote for the DVDetc magazine, please consult www.hdtvetc.com for access to that information, they also have an online service.  This section addresses the main aspects of the technology and the CES 2005 highlights.</p>

<p><br />
<h2>Formats Support</h2><br />
A variety of Hollywood Studios took sides on the war between Blu-ray and HD DVD formats, although the sides are non-exclusive; the Studios can still produce High Definition DVDs on the other format at their discretion.  </p>

<p>Studio MGM and Sony Pictures/Columbia Tri-Star were already committed to Blu-ray, and will begin releasing Blu-ray movies with their DVD releases when Blu-ray players become available within the US in late 2005/early 2006, but during 2004 several events took place to support the Blu-ray format, Twentieth Century Fox announced their support to Blu-ray to become involved in the development of the format and the copy protection features; Fox favored Blu-ray but was not ready to commit their content yet, and continued exploring HD DVD through the DVD Forum.</p>

<p>In addition, Disney became a member of the board of directors of the Blu-ray Association and decided to support Blu-ray (bringing Buena Vista Home Entertainment, Walt Disney Home Entertainment, Hollywood Pictures Home Video, Touchstone Home Entertainment, Miramax Home Entertainment, Dimension Home Video, and Disney DVD to the commitment).  TDK joined Blu-ray and announced their effort to implement a new hard-coat technology to make the disc caddy-less.  JVC also joined Blu-ray.  </p>

<p>Blu-ray is now supported by over 70 companies; the Blu-ray Disc Founders recently formed the Blu-ray Disc Association and a group to collect royalties and licensing fees.  The following 13 companies were the original Blu-ray Disc Founders group: Dell, Hewlett-Packard, Hitachi, LG Electronics, Matsushita Electric Industrial, Mitsubishi Electric, Pioneer, Royal Philips Electronics, Samsung Electronics, Sharp, Sony, TDK, and Thomson Multimedia. </p>

<p>The HD DVD format is promoted by Toshiba and NEC, and was recently joined by Sanyo.  Studios like Paramount Pictures, Universal Studios, Warner Bros., and New Line Cinema declared their support to HD DVD and will release films by the end of 2005.  Thomson/RCA announced their plans to release a HD-DVD player before December 2005 as a sign of support to those Hollywood Studios that use the disc replication services of the Thomson Technicolor unit, which Thomson intends to extend to Blu-ray discs as well. </p>

<p>Beginning 4Q05 Warner Home Video will release over 50 titles in the HD DVD format from HBO video, New Line, and WHV.  HBO will release three series, and New Line Home Entertainment four titles.  Paramount will release 20 HD DVD titles and Universal 3 titles, also beginning 4Q05.</p>

<p><br />
<h2>Formats Specs, Audio, Codecs, Content Protection, and Cameras</h2><br />
Regarding specifications, the DVD forum approved the 1.0 ROM HD DVD specification and later the specifications for the Rewritable -RW format, as well as the write-once HD DVD recordable specification version 0.9, which as planned for completion by the end of 2004.  The Blu-ray disc ROM specification was declared as ready in 2004, which would facilitate the preparations for the production of the discs.</p>

<p>The Blu-ray disc has a capacity of 50GB as dual-layer, and is constructed with a 0.1 mm optical transmittance protection layer above the 1.1 mm substrate.  Matsushita has a 50GB LM-BRM50 rewritable disc with an approximate cost of $68, and a 25GB LM-BRM25 disc for about $31.  </p>

<p>HD DVD uses the same two 0.6 millimeter bonded discs design as DVD, has a dual-layer capacity of 30GB for up to 8 hours of HD, and claims compatibility with the current infrastructure of producing regular DVDs, which would bring more efficiency and less upfront investment.  </p>

<p>Both groups/formats selected MPEG-2, MPEG-4 H.264, and VC-1 (originally known as Microsoft's WMV-9 and VC-9) as mandatory video codecs for players; discs would have to be encoded in at least one of them.  VC-1 is now an open standard and was voted by 19 companies from the DVD Forum steering committee as best in picture quality.  According to Microsoft, the company will remain neutral regarding format support.</p>

<p>Regarding content protection, an alliance of Consumer Electronics (Sony, Toshiba, Panasonic, etc.), IT companies  (IBM, Microsoft, Intel, etc.), and Hollywood Studios (Disney, Warner, etc), was working in the development of AACS (Advanced Access Content System) expected for release by the end of 2004.  AACS employs a key required by the hardware and software to unlock the content.</p>

<p>HD DVD and Blu-ray groups approved Dolby Digital 5.1 and DTS 5.1 as mandatory for HD players; pre-recorded discs must include at least one of the formats, at the election of the content provider.  Later, the DVD Forum decided to include also Dolby Digital +  (a higher bit-rate enhancement of lossy AC-3) and MLP lossless, both as mandatory for HD DVD.  The groups declared optional the player's ability of decoding 6.1 channels DTS ++ (DTS HD, capable of higher bit rates).  Both types of discs could support up to 18Mbps, which HDMI could pass through, according to Silicon Image.  HD players suited with internal hi-bit-rate decoders could also use the 6.1 or 7.1 analog outputs connected to receivers with 6.1 or 7.1 channel analog inputs.</p>

<p>As to how this industry affected video cameras, Sony, Sharp, and Panasonic announced their plans for Blu-ray camcorders that will use 3" 15GB discs; the units are expected for early 2005. </p>

<p><br />
<h2>Enhanced Discs and Drives Technologies</h2><br />
Several companies announced enhanced HD products such as combo discs and high capacity HD discs.  NEC just announced a computer disc drive for HD-DVDs also compatible with current DVDs and CDs.  </p>

<p>New Medium Enterprises announced their 4-layer Versatile Multilayer Disc (VMD) drive/disc of 20GB using red laser technology to show HD films in MPEG-2 1080i/p formats; in fall 2005 other 15GB, 20GB, 25GB and 30GB discs and drives options will be available, and 50GB later in 2006.  VMD players are estimated that will be $250 and are DVD/CD compatible, and although VMD discs are scientifically different they can be produced with the existing DVD facilities at a similar cost.  Recordable capabilities require minor modifications of the technology.  A blue-laser version for one-Terabyte is in the works.  </p>

<p>As you might recall in my CES 2003 report, two years ago Philips announced HD-DVD backward compatibility implementing two video streams on the disc: a) SD video with MPEG-2 to be played back in regular DVD players, and b) a 'difference' stream encoded as MPEG-4 and carrying the difference between the original HD picture signal and the base SD signal that newer HD players with MPEG-4/10 decoding would read; using the two streams they can reconstruct the HD signal.  </p>

<p>Later Pixonics introduced a similar concept on their pHD format using red laser for HD DVD, which was also targeted to more efficient broadcasting, and delivery systems.  The disc capacity of 3.5 hours of combined streams, SD at 6Mbps with MPEG-2 and the additional 1.5 Mbps HD content, was reachable using a 9GB dual layer DVD disc, which would enable the disc to deliver up to 1080p.  pHD SD MPEG-2 signals would use the current CSS content protection scheme, although more Digital Rights Management methods were considered for the enhanced HD stream.</p>

<p><img src="/articles/images/mt/image089.gif" alt="" align="left">By the end of 2004, JVC announced the first Blu-ray/DVD ROM triple-layer disc to hold HD in the outer layer and SD content in the inner two layers, with 33.5 GB capacity in a single side (25 GB HD + 8.5 GB DVD); a blue laser reads the outer BD layer; a red laser reads the SD layer.  Movies can be released in both formats on a single disc.  The reflective film technology uses a double-faced substrate molding able to reflect blue laser and be sufficiently transparent so the red laser could read the inner layers.  A higher capacity version of 58.5 GB (50GB for the Blu-ray "dual" layer, 8.5GB DVD dual layer) is on the works.<img src="/articles/images/mt/image088.gif" alt="" align="right"></p>

<p>On a similar approach, Toshiba and disc maker Memory-Tech recently introduced a dual-layer disc capable of 4.7 GB DVD content in an upper layer and 15GD HD DVD content in the lower layer, intended to help the transition from DVD to HD DVD since it will contain both versions of a movie.  </p>

<p>With the introductions from JVC and Toshiba movie buyers will save when purchasing a combo disc, play it as SD on today's DVD players, and later play it as HD in their future HD DVD player.  Production costs and manufacturing are similar to DVD; studios and distribution chains would also be benefited when not dealing with two versions of the same movie.</p>

<p>     <br />
<h2>High Definition DVD at CES 2005</h2><br />
<img src="/articles/images/mt/image090.jpg" alt="" align="right">JVC submitted for approval to the Blu-ray Disc Association the adoption of the combo system above and has shown the unit below at CES:<br />
<table><tr><td align="center"><br />
<img src="/articles/images/mt/image091.jpg" alt="JVC Blu-ray DVD Combo ROM"><br>JVC Blu-ray DVD Combo ROM<br />
</td></tr></table></p>

<p><img src="/articles/images/mt/image092.jpg" alt="LG BH-6900" align="left">LG showed the BH-6900 (left), currently available in Korea for $4000, a Blu-ray recorder with ATSC/NTSC tuners, 160GB HDD, 16 hours of HD, 156 of SD, BD to HDD two-way dubbing, 23 GB Blu-ray disc for up to 2.5 hours of HD programming, multiple disc playback for DVD, DVD-R, DVD+R, Audio CD, PSIP, IEEE1394 (DVi iLink for camcorders), component and DVI output, TTM after 2005.<br clear="all"></p>

<p><img src="/articles/images/mt/image093.jpg" alt="Panasonic Blu-ray BD-ROM" align="right">Panasonic showed a Blu-ray BD-ROM player prototype (right) and a BD-ROM player concept,<br />
<img src="/articles/images/mt/image094.jpg" alt="Panasonic DMR-E700BD" align="left">$N/A, TTM N/A; the company also showed the DMR-E700BD (left), a BD/DVR recorder released in Japan in July 2004, $2800, digital OTA tuner, 4.5 hours of HD with 50GB dual layer open-cartridge discs, DVD-RAM and DVD-R discs recording for analog TV, dual-drives manufacturing plans of 2000 units x month.<br clear="all"><br />
		                 <br />
<img src="/articles/images/mt/image095.jpg" alt="Philips Blu-ray Recorder" align="left">Philips showed a Blu-ray recorder (left) and a PC drive recorder unit that will also play ROM BD discs, TTM 2005, $450.  No decisions were made yet regarding 1080p playing/recording, neither on 1080i over component analog video for the US.<br clear="all"></p>

<p><img src="/articles/images/mt/image096.jpg" alt="Pioneer PC Blu-ray" align="right">Pioneer will introduce a PC Blu-ray (2H05), and later a Blu-ray recorder (right) that was demo at the show (DVD/BRD), both able to record HDTV, SDTV, DVD-R/-RW, and DVD+R/+RW discs.<br clear="all"></p>

<p><img src="/articles/images/mt/image098.jpg" alt="Samsung BD-P1000" align="left">Samsung also showed model BD-P1000 Blu-ray ROM player (left), $1,000, TTM end of 2005, and (below) recorder player model BD-R1000, TTM N/A, $ N/A, Samsung did not confirm if ROM BD discs will play in the recorder US<br />
<img src="/articles/images/mt/image097.jpg" alt="Samsung Blu-ray BD-R1000 Recorder/Player" align="right">unit; first demo last year with ATSC/NTSC tuners, HDMI, component out, digital audio coax/optical, selectable outputs switchable to 1080i/720p/480p, estimated originally at $2000 but not released.<br clear="all"></p>

<p><img src="/articles/images/mt/image099.jpg" alt="Sanyo HD DVD ROM Player" align="left">Sanyo joined the HD DVD format and showed a prototype HD DVD ROM player, TTM end of 2005, $1000.<br clear="all"></p>

<p><img src="/articles/images/mt/image100.jpg" alt="Sharp BD-HD100 Blu-ray recorder" align="right">Sharp showed Japan's Blu-ray recorder BD-HD100 (right), DVD-RW/R recorder, 160 GB HDD for 19 hours of HD or 218 hrs of SD, twin-tray for Blu-ray and DVD allowing copying of unprotected content between the discs and the HDD, 6-way digital dubbing (Blu-ray, HDD, DVD) DVD multi format playback (DVD-RW/-R and +RW/+R and RAM), HDMI, i.Link, triple digital tuners (terrestrial and satellite), TTM December 2004, $3,000, records/playbacks 25GB single layer discs but reportedly not dual layer 50GB discs, playback DVD, DVD+/-R, DVD+/-RW, DVD-RAM, and CDs, production plans for 3000 units x month.<br clear="all"></p>

<p>Sony showed two Blu-ray players using MPEG-2 and VC-1 codecs (below), and the recorder/player available in Japan since April 2003:<br />
<table><tr><td align="center"><br />
	<img src="/articles/images/mt/image101.jpg" alt="SONY Blu-ray Player MPEG-2"><br />
	SONY Blu-ray Player MPEG-2<br />
</td><td align="center"><br />
	<img src="/articles/images/mt/image102.jpg" alt="SONY Blu-ray Player VC1"><br />
	SONY Blu-ray Player VC1<br />
</td></tr></table></p>

<table align="left"><tr><td align="center">
<img src="/articles/images/mt/image103.jpg" alt="SONY BDZ-S77"><br>
SONY Blu-ray Recorder BDZ-S77
</td></tr></table>

<p>Sony also showed the model # BDZ-S77 available in Japan since April 2003 (left), originally $4,000 now around $3,000, disc with caddy, single-layer 23 GB capacity discs, Sony discs playable/recordable in the Matsushita unit, Panasonic 25GB discs play after 90 seconds recognition, 50GB Matsushita discs cannot be used, single drive w/ two laser heads (red/blue).<br clear=all></p>

<p><img src="/articles/images/mt/image104.jpg" alt="Toshiba Blu-ray Player" align="left">Toshiba announced blue-laser players ($1000), recorders, and discs that will appear in late 2005, including the notebook built-in drives.  They are capable to play regular DVDs and CDs, with HDMI/HDCP and IEEE1394/DTCP outputs, Ethernet interactive connection, support video<br />
<img src="/articles/images/mt/image105.jpg" alt="Toshiba HDD + HD DVD" align="right">resolutions up to 1920x1080p, if discs are recorded that way, selectable to match the native resolution of the display; 1080p output resolution is not yet confirmed, nor if 1080i HD playback would be allowed over analog component outputs.  Like most DVD players with that feature, DVD 1080i upconversion of protected content is over HDMI only. </p>

<p>Meetings were held at CES to discuss the possibility of reaching an agreement in avoiding a format war, no agreement was reached, but Sony was quoted as looking into having their Blu-ray unit capable to play HD-DVD as well, no official announcements were made.              </p>

<p>In addition to the format war above, there is the Chinese EVD format.</p>

<p><br clear="all"><br />
<h2>Chinese Hi Def DVD</h2><br />
The full coverage of this subject can be obtained from the DVDetc magazine article I recently wrote about the subject, please consult www.hdtvetc.com.  Last year at CES I met with a Chinese manufacturer of the EVD player, the Chinese Hi Def DVD, the company was one of the nine Chinese electronics manufacturers that made an EVD industry alliance in 2003 to develop and promote EVD players.  EVD is not alone anymore; the Chinese industry has grown to four formats now.  </p>

<p><img src="/articles/images/mt/image106.jpg" alt="EVD Fact Sheet" align="right">EVD decoder products have been manufactured by Beijing Homaa Microelectronics Technology and Beijing E-world Technology, in cooperation with United States-based LSI Logic.  Below is the EVD player introduced last year, over the right, the specs for the EVD format:<br />
<img src="/articles/images/mt/image107.jpg" alt="EVD 500 Chinese High Def DVD Player"></p>

<p>The four formats use red laser: High-Definition Videodisc (HDV), High-Definition Versatile Disc (HVD), Enhanced Versatile Disc (EVD), and in November 2004, the Forward Versatile Disc (FVD) was introduced in Taiwan.</p>

<p>EVD and HVD use MPEG-2, and FVD uses WM9.  Studios such Paramount, MGM, and Miramax have released some WM9 titles in Germany; Disney has released some titles in Italy.  Universal has some HVD movies in China, and Sony and Miramax has some EVD in China.  According to Beijing K-City, who sold 6000 HDV players to France, there were 400 HDV titles available in China.  </p>

<p>Please review the subject with more detail and the conclusions on the mentioned DVDetc magazine article.</p>

<p>Be sure that you read the next article in the series: HDTV Recorders (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 20, 2005  5:35 AM</b>
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
			<?=getComments(225)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 225)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-11-high-definition-dvd.php" type="text/javascript" charset="utf-8"></script>
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