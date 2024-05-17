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
		AND e.entry_id = 145";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Robert Graves" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 145 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Robert Graves'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Robert Graves" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 145 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 145";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/07/digital-television-broadcasting-in-the-americas.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 145";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Digital Television Broadcasting in the Americas" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Digital Television Broadcasting in the Americas" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Digital Television Broadcasting in the Americas" />
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
	<title>HDTV Magazine - Digital Television Broadcasting in the Americas</title>
	<meta name="keywords" content="dtt broadcasting, atsc standard, atsc forum, digital television, advanced television, atsc, dtt, television, broadcasting, digital, hdtv, standard, years, graves, been, information, technology, programming, broadcasts, analog, americas, sold, tvs, forum, prior" />
	<meta name="description" content="Already a vital part of the region's communications and information infrastructure, during the next decade the national television broadcasting systems throughout the Americas are expected to be upgraded from analog to digital technology, keeping pace with the technological advances that are reshaping all types of global telecommunications. The transition to digital terrestrial television (DTT) broadcasting is a revolutionary change that will dramatically affect the future of free over-the-air television in the Americas. With digital technology, DTT allows each broadcaster to provide a huge wireless information pipeline into every home, delivering 20 million bits per second through each 6 MHz broadcast television channel. This capability not only enables the delivery of dramatically sharper images and CD-quality surround sound, it supports a much greater quantity and diversity of TV programs, plus a whole new array of information services, including interactive capabilities that will help to bring the benefits of the information age more fully and more uniformly to all citizens in the Americas." />
	<meta name="title" content="Digital Television Broadcasting in the Americas" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Digital Television Broadcasting in the Americas" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/07/digital-television-broadcasting-in-the-americas.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Already a vital part of the region's communications and information infrastructure, during the next decade the national television broadcasting systems throughout the Americas are expected to be upgraded from analog to digital technology, keeping pace with the technological advances that are reshaping all types of global telecommunications. The transition to digital terrestrial television (DTT) broadcasting is a revolutionary change that will dramatically affect the future of free over-the-air television in the Americas. With digital technology, DTT allows each broadcaster to provide a huge wireless information pipeline into every home, delivering 20 million bits per second through each 6 MHz broadcast television channel. This capability not only enables the delivery of dramatically sharper images and CD-quality surround sound, it supports a much greater quantity and diversity of TV programs, plus a whole new array of information services, including interactive capabilities that will help to bring the benefits of the information age more fully and more uniformly to all citizens in the Americas." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=145', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/07/digital-television-broadcasting-in-the-americas.php">Digital Television Broadcasting in the Americas</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Robert Graves</b> on <b>July  8, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=12&category=Global & Worldview">Global & Worldview</a></b>
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
				<p><em>Robert Graves is the Chairman, ATSC Forum and Member, U.S. Delegation to PCC.II. Prior to assuming his ATSC Forum duties Robert Chaired the ATSC (Advanced Television Systems Committee) and prior to that was the Washington representative for AT&T and their participation in the Grand Alliance. Robert joins our HDTV Magazine roster of authorities to help clarify the HDTV movement to those still finding confusion with it. The ATSC Forum was formed to promote the use of the ATSC standard beyond our domestic markets. </em> _Dale Cripps</p>

<p><br />
Already a vital part of the region's communications and information infrastructure, during the next decade the national television broadcasting systems throughout the Americas are expected to be upgraded from analog to digital technology, keeping pace with the technological advances that are reshaping all types of global telecommunications. The transition to digital terrestrial television (DTT) broadcasting is a revolutionary change that will dramatically affect the future of free over-the-air television in the Americas.  With digital technology, DTT allows each broadcaster to provide a huge wireless information pipeline into every home, delivering 20 million bits per second through each 6 MHz broadcast television channel. This capability not only enables the delivery of dramatically sharper images and CD-quality surround sound, it supports a much greater quantity and diversity of TV programs, plus a whole new array of information services, including interactive capabilities that will help to bring the benefits of the information age more fully and more uniformly to all citizens in the Americas.  </p>

<p>The U.S. has led the way in the introduction of DTT broadcasting.  After a nine-year process in which the technology was developed and competing systems were evaluated, the U.S. Federal Communications Commission adopted the ATSC Standard in 1996 and commercial DTT broadcasts began in ten cities in late 1998. Now, almost seven years later, there are 1,500 stations on the air in 211 cities reaching virtually all television households with at least one digital signal.  90% of television households have access to five digital signals and some large cities have more than 20 digital signals on the air.  </p>

<p>High-definition television (HDTV) remains the centerpiece application of DTT broadcasting in the U.S., with most prime-time programming and steadily increasing amounts of sports and movies being offered in HDTV. HDTV programming is not only available via terrestrial broadcast, but also via cable and satellite delivery, providing a critical mass of programming that is making the sales of HDTV receivers take off like a rocket. As of the end of 2004, 16 million DTV products worth $26 billion had been sold, the vast majority of which were HDTV products. Sales in 2004 were approximately equal to the cumulative sales in all prior years, and 2005 sales are projected to double those of 2004. </p>

<p>The prices of ATSC products continue to fall very rapidly, with HDTV set-top boxes as low as $200, HDTV monitors as low as $400, HDTV integrated receivers as low as $500, and integrated standard-definition (SDTV) receivers as low as $300. Indeed, the prices of HDTV receivers are rapidly converging with those for analog TVs, and large screen analog TVs have mostly disappeared from the market. My own view is that within three or four years virtually all TVs sold in the U.S. will be HD, and the prices will be no higher than those for today's analog color TVs of the same size.  </p>

<p>HDTV is a big success in the U.S., but it is not the only success.  Public broadcasters are delivering multiple simultaneous SDTV programs to help achieve their education and community service mandates, and many commercial broadcasters have added simultaneous 24-hour news or weather programs to accompany their HDTV programming.  A number of new information services have also been launched, with information accompanying regular video programming. The number and breadth of these other innovative DTT applications continue to increase.  (Interactive information services are especially well-developed in South Korea, where ATSC DTT broadcasts currently reach 80% of TV households, with nationwide coverage planned by the end of this year.)</p>

<p>Meanwhile, driven by the need to recapture and reuse valuable nationwide spectrum, the U.S. government is expanding its program to hasten the completion of the transition from analog to digital broadcasting. By 2007 virtually all TV sets sold in the U.S. are required to contain ATSC digital tuning and decoding capability, and 27 million such TVs per year are projected to be sold in the U.S. alone by that time. The tremendous economies of scale that will result from such high volumes will drive down further the prices of ATSC receivers for all countries that adopt the ATSC Standard, making DTT broadcasting affordable for all socio-economic classes throughout the hemisphere. Proposals are now before the U.S. Congress that would end analog television broadcasts by January 1, 2009, and would subsidize the purchase of an inexpensive digital-to-analog converter box for poor Americans who had not yet purchased a digital set or set-top box by that time. Such converter boxes are expected to cost $50 or less by 2008. Thus, in the U.S., the focus has now shifted to planning for the end of the transition to DTT broadcasting.</p>

<p>DTT broadcasting is also moving ahead elsewhere in the Americas. In Canada, ATSC HDTV broadcasts are now on the air in Toronto and Montreal, with substantial amounts of HD programming available via cable and satellite as well. More than 50% of TVs now sold in Canada are HD-capable, with more than 1.2 million such sets sold to date.  Following six years of experimental broadcasts, Mexico formally adopted its DTT policy in July 2004 and commercial services will begin this August. Under Mexico's policy, ATSC broadcasting is to be implemented in major cities and the U.S. border region no later than 2006.</p>

<p>Brazil has been actively studying alternatives for DTT broadcasting for several years, and the government of Brazil has funded research into new DTT technology and enhancements that could be incorporated into whatever broadcast standard Brazil adopts. Brazil intends to make its DTT standard decision by February 2006, which may well be to adopt one of the existing DTT standards, but with several improvements and enhancements.  </p>

<p>Argentina adopted the ATSC Standard in 1998 and broadcasters there have been conducting experimental ATSC broadcasts for several years.  Broadcasters in Chile have also conducted experimental ATSC broadcasts, and have formally recommended adoption of the ATSC Standard to the government. Several other countries in the hemisphere have begun to explore their options and policies for DTT broadcasting, including Peru, Colombia, Ecuador, Venezuela, Uruguay, Bolivia, Panama, Guatemala, the Dominican Republic, and Jamaica.  </p>

<p>CITEL has been discussing DTT broadcasting for several years through its PCC.II-Radiocommunications Including Broadcasting group. In October 2003 PCC.II adopted a resolution urging the OAS members states to implement DTT broadcasting as rapidly as local conditions permit, using a common standard throughout the hemisphere. Since that time, CITEL has been preparing a DTV Implementation Guide to explain the benefits of DTT broadcasting, and to assist OAS member states in their efforts to plan for and implement DTT, by sharing the experiences of those countries that have already undertaken significant efforts in this area.</p>

<p>In summary, digital television technology offers a remarkable improvement in the technical quality of television, plus a quantum increase in the quantity of television programming available, plus a revolutionary improvement in the information infrastructure of the nations that implement it. Thus, DTT broadcasting represents an immediate and effective means of promoting social inclusion throughout the hemisphere and bridging the "digital divide," so that all socio-economic segments of society can reap the benefits of this fruitful new technology. It is vital that nations throughout the Americas take steps now to bring these tremendous benefits to their citizens. Adoption of a common DTT transmission standard throughout the hemisphere will create great economies of scale that will mean more broadcast and consumer products from more suppliers at lower prices, making DTT affordable to all and hastening the transition to DTT throughout the region.</p>

<p>_______________________________________________<br />
ROBERT K. GRAVES</p>

<p>Chairman, ATSC Forum</p>

<p>Robert Graves serves as Chairman of the ATSC Forum, a group of companies and associations working to educate governments, broadcasters, manufacturers and others around the world regarding the benefits of digital television (DTV) and the ATSC family of DTV standards.  </p>

<p>Prior to the formation of the ATSC Forum in 2002, Mr. Graves served for six years as Chairman of the Advanced Television Systems Committee (ATSC), a group of approximately 140 international corporations, associations and research and educational institutions developing standards for digital television.  </p>

<p>Mr. Graves has been heavily involved since 1991 in efforts to establish an international standard for digital television transmission, resulting in the adoption of the ATSC DTV Standard by the U.S. Federal Communications Commission (FCC) in 1996.  The ATSC Standard has also been formally adopted by Canada, Mexico, Argentina, and South Korea, and is being actively considered by many other countries.  Mr. Graves served as a member of the FCC's Advisory Committee on Advanced Television Service, and he represented the Digital HDTV Grand Alliance (seven companies who developed the system upon which the ATSC Standard is based) before the FCC and the U.S. Congress.  Along with other leaders of the Grand Alliance, in 1997 Mr. Graves received an Emmy Award for contributions to the development of high-definition television.  </p>

<p>A 22-year veteran of AT&T, Mr. Graves served as Government Affairs Vice President-Technology and Infrastructure for eight years prior to forming his own consulting firm in 1995.  </p>

<p>Mr. Graves holds Electrical Engineering degrees from the University of Utah and Stanford University, and an MBA with distinction from The Wharton School, University of Pennsylvania.  </p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Robert Graves</b>, <b>July  8, 2005  3:58 AM</b>
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
			<?=getComments(145)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Robert Graves', 145)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Robert Graves</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/07/digital-television-broadcasting-in-the-americas.php" type="text/javascript" charset="utf-8"></script>
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