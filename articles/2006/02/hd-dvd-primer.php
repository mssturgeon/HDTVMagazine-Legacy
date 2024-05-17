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
		AND e.entry_id = 341";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 341 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 341 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 341";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/02/hd-dvd-primer.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 341";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HD DVD Primer" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HD DVD Primer" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HD DVD Primer" />
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
	<title>HDTV Magazine - HD DVD Primer</title>
	<meta name="keywords" content="warner home, studios home, date giventhe, giventhe weinstein, givenparamount home, home, dvd, date, warner, studios, weinstein, giventhe, audio, givenparamount, yearuniversal, laser, video, digital, channel, dolby, march, mpeg, dts, entertainmentthe, available" />
	<meta name="description" content="Many of you are eagerly anticipating the arrival of HD packaged media. While HD media has been available for purchase in one form or another for many years (D-VHS/D-Theater and WMVHD), HD DVD promises to be the largest distribution to date within months of its release. This article will cover the basics of HD DVD audio and video, gives a brief overview of the two Toshiba models arriving in March, and concludes with a listing of HD DVD movies that will be available upon release (and soon thereafter)." />
	<meta name="title" content="HD DVD Primer" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HD DVD Primer" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/02/hd-dvd-primer.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Many of you are eagerly anticipating the arrival of HD packaged media. While HD media has been available for purchase in one form or another for many years (D-VHS/D-Theater and WMVHD), HD DVD promises to be the largest distribution to date within months of its release. This article will cover the basics of HD DVD audio and video, gives a brief overview of the two Toshiba models arriving in March, and concludes with a listing of HD DVD movies that will be available upon release (and soon thereafter)." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=341', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/02/hd-dvd-primer.php">HD DVD Primer</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>February 28, 2006</b>
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
				<p><img src="/images/hddvd.png" alt="HD DVD" align="left">Many of you are eagerly anticipating the arrival of HD packaged media. While HD media has been available for purchase in one form or another for many years (D-VHS/D-Theater and WMVHD), HD DVD promises to be the largest distribution to date within months of its release. This article will cover the basics of HD DVD audio and video, gives a brief overview of the two Toshiba models arriving in March, and concludes with a listing of HD DVD movies that will be available upon release (and soon thereafter).</p>

<p><br />
<h2>Video Basics</h2></p>

<p>For those of you reading this article who are already aware of what HD DVD is, you can skip to the next section. But for those who would like some clarification, read on. The basic disc structure is the same as DVD (size, layers, etc.), but the compression and laser technologies involved are completely different.</p>

<p><img src="/images/articles/spectrum.gif" alt="Laser Spectrum" align="right">Traditional DVD's utilized a red laser for reading to and writing from the disc. HD DVD utilizes a new blue-violet laser. This new blue-violet laser has a 405nm wavelength vs. the red laser's 650nm. This shorter wavelength allows for much higher data density since the blue laser can write a much narrower data track. The net effect is that HD DVD can store more than 3 times the number of bits as traditional DVD's.</p>

<p>HD DVD also utilizes more advanced compression techniques than did traditional DVD. It can employ MPEG-4 AVC and VC-1 (aka Windows Media 9), whereas traditional DVD's were strictly MPEG-2. These compression advances allow for roughly twice the data storage as traditional DVD. For a direct comparison of these two specifications, see the table below:<br clear="all"></p>

<table class="type1b" cellpadding="0" cellspacing="0">
	<tr><td>&nbsp;</td><td style="text-align:center" class="type1b_header">DVD</th><th colspan="3" class="type1b_header">HD DVD</th></tr>
	<tr><th style="text-align:center" class="type1b_header">Disc type</th><td style="text-align:center" class="grid">DVD-ROM<br>(Read-Only)</td><td style="text-align:center" class="grid">HD DVD-ROM<br>(Read-Only)</td><td style="text-align:center" class="grid">HD DVD-R<br>(Recordable) </td><td style="text-align:center" class="grid">HD DVD-Rewritable<br>(Recordable)</td></tr>
	<tr><th class="type1b_header">Disc diameter</th><td style="text-align:center" class="grid">120mm</td><td style="text-align:center" class="grid">120mm</td><td style="text-align:center" class="grid">120mm</td><td style="text-align:center" class="grid">120mm</td></tr>
	<tr><th class="type1b_header">Disc structure</th><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td></tr>
	<tr><th class="type1b_header">Capacity<br>(Single-sided,<br>single-layer)<br>(Single-sided,<br>dual-layer)</th><td style="text-align:center" class="grid">4.7GB<br>8.5GB</td><td style="text-align:center" class="grid">15GB<br>30GB</td><td style="text-align:center" class="grid">15GB</td><td style="text-align:center" class="grid">20GB<br>32GB<span class="text_hd5">(Under development)</td></tr>
	<tr><th class="type1b_header">Playback time*<br>Recording time*</th><td style="text-align:center" class="grid"><span class="text_hd5">4.7GB, SD resolution:<br>132minutes<br><span class="text_hd5">8.5GB, SD resolution:<br>238minutes</td><td style="text-align:center" class="grid"><span class="text_hd5">15GB, HD resolution:<br>over 4 hours<br><span class="text_hd5">30GB, HD resolution:<br>over 8 hours</td><td style="text-align:center" class="grid"><span class="text_hd5">15GB, HD resolution:<br>over 4 hours</td><td style="text-align:center" class="grid"><span class="text_hd5">20GB, HD resolution:<br>over 5.5 hours<br><span class="text_hd5">32GB, HD resolution:<br>over 8.5 hours </td></tr>
	<tr><th class="type1b_header">Laser Wavelength</th><td style="text-align:center" class="grid">650nm<br>(red laser)</td><td style="text-align:center" class="grid">405nm<br>(blue laser)</td><td style="text-align:center" class="grid">405nm<br>(blue laser)</td><td style="text-align:center" class="grid">405nm<br>(blue laser)</td></tr>
	<tr><th class="type1b_header">Compression<br>technology</th><td style="text-align:center" class="grid">MPEG-2</td><td style="text-align:center" class="grid">MPEG-4 AVC/<br>VC-1/MPEG-2</td><td style="text-align:center" class="grid">MPEG-4 AVC/<br>VC-1/MPEG-2</td><td style="text-align:center" class="grid">MPEG-4 AVC/<br>VC-1/MPEG-2 </td></tr>
	<tr><th class="type1b_header">User bit rate</th><td style="text-align:center" class="grid">11.08Mbps</td><td style="text-align:center" class="grid">36.55Mbps</td><td style="text-align:center" class="grid">36.55Mbps</td><td style="text-align:center" class="grid">36.55Mbps</td></tr>
	<tr><th class="type1b_header">Track pitch</th><td style="text-align:center" class="grid">0.74&micro;m</td><td style="text-align:center" class="grid">0.40&micro;m</td><td style="text-align:center" class="grid">0.40&micro;m</td><td style="text-align:center" class="grid">0.34&micro;m</td></tr>
</table>

<p><br />
<h2>Audio Basics</h2></p>

<p>In addition to AC-3 (Dolby<sup>&reg;</sup> Digital) and MPEG codecs currently supported by traditional DVD, HD DVD has added Dolby<sup>&reg;</sup> Digital Plus (lossy) and DTS<sup>&reg;</sup> (lossy) as mandatory codecs. Support for 2-channel Linear PCM and 2-channel MLP (True HD) are also mandated. The HD DVD standard also allows for DTS<sup>&reg;</sup> HD (lossless) as an optional codec. with respect to the two currently available Toshiba units, the audio capabilities are equally as impressive as their video capabilities. Their press release says it best:</p>

<blockquote>The mandatory audio formats for HD DVD include both lossy and lossless formats from Dolby Labs and DTS<sup>&reg;</sup> - including the newly developed Dolby<sup>&reg;</sup> Digital Plus and DTS-HD.<br>
<br>
The lossless mandatory formats include Linear PCM and Dolby TrueHD (only 2 Channel support is mandatory). The TrueHD format is bit-for-bit identical to the high resolution studio masters and can support up to eight discrete full range channels of 24-bit/96k Hz audio. Another lossless format (specified as an optional format) is DTS-HD. This employs high sampling rates of up to 192kHz.<br>
<br>
Both models feature built-in multi-channel decoders for Dolby Digital, Dolby Digital Plus, Dolby TrueHD (2 channel), DTS and DTS-HD. The HD-XA1 employs the use of four high performance DSP engines to decode the multi-channel streams of the wide array of audio formats. These high performance processors will perform the required conversion process, as well as the extensive on-board Multi-Channel Signal Management including: User Selectable Crossovers, Delay Management and Channel Level Management.</blockquote>

<p><br />
<h2>Hardware Comparison</h2></p>

<p>Slated for release next month are two HD DVD players, both from Toshiba: the <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E21TY0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">HD-XA1</a> ($799.99) and the <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E1PTGK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">HD-A1</a> ($499.99). Both players are backward-compatibile, allowing playback of older CD and DVD formats. Both players also support copy-protected playback via HDCP at 720p and 1080i over HDMI, and will scale a traditional 480p DVD source to either 720p or 1080i to match your television's capabilities.</p>

<table width="100%"><tr>
<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E1PTGK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2"><img src="/images/articles/hd-a1.jpg" alt="Toshiba HD-A1"></a></td>
<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E21TY0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2"><img src="/images/articles/hd-xa1.jpg" alt="Toshiba HD-XA1"></a></td>
</tr><tr>
<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E1PTGK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Toshiba HD-A1</a></td>
<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E21TY0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Toshiba HD-XA1</a></td>
</tr></table>

<p>The differences between the two players are arguably minimal, but that is subjective.</p>

<p>On the audio side, the XA1 has four high performance DSP engines which allow not only for audio conversion, but also improved audio signal management (user selectable crossovers, delay management, and channel level management). Of course, if you're using a separate receiver or audio component to do this processing, there's no advantage.</p>

<p>The XA1 also employs several construction advancements over its A1 counterpart. It includes a motorized door which conceals the disc drawer, function buttons and USB ports. The XA1 also includes three different interfaces and a motion-activated, backlit remote control. A double chassis construction is also employed by the XA1 to add stability and strength and minimize vibrations. And to finish it off, the HD-XA1 adds insulated stabilizing feet.</p>

<p>The remainder of the specifications for these two players are essentially the same, as outlined in the table that follows:</p>

<table class="type1b"><tr><td class="type1b_header">Specification</td><td class="type1b_header">HD-XA1, HD-A1</td></tr><tr><td class="grid">Disc playback:</td><td class="grid">HD DVD, DVD, DVD-R/-RW/-RAM, CD, CD-R/-RW</td></tr><tr><td class="grid">HD output:</td><td class="grid">Yes, using HDMI at disc native resolution</td></tr><tr><td class="grid">Video DAC:</td><td class="grid">11-bit/216-MHz</td></tr><tr><td class="grid">Video up-conversion for DVD:</td><td class="grid">720p/1080i</td></tr><tr><td class="grid">Enhanced black level:</td><td class="grid">0IRE/7.5IRE selection</td></tr><tr><td class="grid">Letterbox and pan-and-scan support:</td><td class="grid">Yes</td></tr><tr><td class="grid">Built-in audio decoders:</td><td class="grid">Dolby Digital, Dolby Digital Plus, DTS, and DTS-HD</td></tr><tr><td class="grid">Dolby TrueHD compatible:</td><td class="grid">Yes (2 channel)</td></tr><tr><td class="grid">Signal processors:</td><td class="grid">Four 32-bit floating-point</td></tr><tr><td class="grid">Audio DACs:</td><td class="grid">Multichannel 24-bit/192-kHz</td></tr><tr><td class="grid">HDMI audio support:</td><td class="grid">Yes, up to 5.1 L-PCM</td></tr><tr><td class="grid">Dynamic range control:</td><td class="grid">Yes</td></tr><tr><td class="grid">MP3 and WMA playback:</td><td class="grid">Yes</td></tr><tr><td class="grid">On-screen display:</td><td class="grid">Yes, trilingual: English, French, Japanese</td></tr><tr><td class="grid">Bit-rate display:</td><td class="grid">Yes</td></tr><tr><td class="grid">FL dimmer:</td><td class="grid">Yes</td></tr><tr><td class="grid">Fast forward:</td><td class="grid">Yes</td></tr><tr><td class="grid">Fast reverse:</td><td class="grid">Yes</td></tr><tr><td class="grid">Slow play:</td><td class="grid">Yes</td></tr><tr><td class="grid">Step play:</td><td class="grid">Yes</td></tr><tr><td class="grid">Time search:</td><td class="grid">Yes</td></tr><tr><td class="grid">A-B repeat:</td><td class="grid">Yes</td></tr><tr><td class="grid">Screen saver:</td><td class="grid">Yes</td></tr><tr><td class="grid">Parental lock:</td><td class="grid">Yes</td></tr><tr><td class="grid">USB:</td><td class="grid">2</td></tr><tr><td class="grid">HDMI (ver. 1.1):</td><td class="grid">1</td></tr><tr><td class="grid">ColorStream Pro component video:</td><td class="grid">1</td></tr><tr><td class="grid">S-Video:</td><td class="grid">1</td></tr><tr><td class="grid">Composite video:</td><td class="grid">1</td></tr><tr><td class="grid">Stereo analog audio:</td><td class="grid">1</td></tr><tr><td class="grid">Analog audio:</td><td class="grid">5.1 multichannel</td></tr><tr><td class="grid">Coaxial digital output:</td><td class="grid">1</td></tr><tr><td class="grid">TosLink optical digital output:</td><td class="grid">1</td></tr><tr><td class="grid">Ethernet 10/100 port:</td><td class="grid">1</td></tr><tr><td class="grid">RS-232C:</td><td class="grid">1</td></tr><tr><td class="grid">Width:</td><td class="grid">17.72"</td></tr><tr><td class="grid">Height:</td><td class="grid">4.33"</td></tr><tr><td class="grid">Depth:</td><td class="grid">13.39"</td></tr></table>

<p>Other hardware planned for later this year are as follows:<br />
<table width="100%"><tr><td style="text-align:center"><img src="/images/articles/toshiba_slim_drive.gif" alt="Toshiba HD DVD Slim Drive"></td><td style="text-align:center"><img src="/images/articles/qosmio.jpg" alt="Toshiba Qosmio Notebook PC w/ HD DVD Drive"></td></tr><tr><td style="text-align:center">Toshiba HD DVD Slim Drive</td><td style="text-align:center">Toshiba Qosmio Notebook PC w/ HD DVD Drive</td></tr><tr><td style="text-align:center"><img src="/images/articles/hr1100a.gif" alt="NEC HD DVD-ROM Drive"></td><td style="text-align:center">(No Image Yet)</td></tr><tr><td style="text-align:center">NEC HD DVD-ROM Drive</td><td style="text-align:center">Microsoft HD DVD Drive for Xbox 360</td></tr></table></p>

<p><br />
<h2>Software</h2></p>

<p>HD DVD discs employ menuing and advanced navigation via "iHD." This advanced interface allows for many new features unavailable in traditional DVD's. The most notable of which is the ability to navigate through the HD DVD and associated options as an "overlay" to the content, without interrupting your viewing.</p>

<p>You may have seen stories in the past few days about some of the interactive features not being available immediately at launch. I have not been able to confirm this, but I think I found the source of this in their latest press release:</p>

<blockquote>Design specifications and dimensions are not final and subject to change. Firmware upgrade required for full interactive features.</blockquote>

<p>And while they don't define "full interactive features", it should not be a big deal for most as this will likely be done directly via the ethernet port, similar to the latest TiVo's.</p>

<p>For content protection, HD DVD utilizes the Advanced Access Content System (AACS), which is a standard for content distribution and digital rights management. For media that is AACS-enabled, these players will be required to recognize an Image Constraint Token (ICT), inserted into the movie data, and scale the analog output (over component) down to 540p.  This is still better than standard 480p DVD, but far from HD resolutions. As well as preventing illegal copying, AACS provides "Managed Copy", which essentially allows content transfer from the HD DVD to other device (like a home media server).</p>

<p>The decision to ICT-enable content is up to each studio, and each studio is likely to go their own way. From Video Business Online:</p>

<blockquote>No studio would comment on whether it plans to take advantage of the Image Constraint option.<br>
<br>
Within the AACS consortium, however, Warner Home Video was consistently the strongest proponent of the idea, according to sources familiar with the negotiations.<br>
<br>
20th Century Fox Home Entertainment is not a member of AACS, but has argued against the idea in other forums.<br>
<br>
AACS-member Disney, as well as non-members NBC Universal and Paramount, are likely to take advantage of the option, according to sources with knowledge of the studios' thinking.<br>
<br>
Although Sony is a member of AACS, where it sometimes clashed with Warner on the issue, sources said it is still unclear whether Sony Pictures Home Entertainment will take advantage of the ICT option now that it is in place.
</blockquote>

<p><br />
<h2>Titles</h2></p>

<p>Below is a list of titles expected to be available this year, with the expected date listed for those that had it available. Also, Netflix has recently announced that they will be making these titles available in their library for rental as soon as they are available. Those films listed below as links are <strong>available for pre-order</strong>.</p>

<table class="type1b"><tr>
	<td class="type1b_header">Title</td><td class="type1b_header">Date</td><td class="type1b_header">Studio</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTY0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Batman Begins</a></td><td class="grid">3/28/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJCK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Constantine</a></td><td class="grid">3/28/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJCU/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Million Dollar Baby</a></td><td class="grid">3/28/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJD4/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Phantom of the Opera</a></td><td class="grid">3/28/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJDE/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Twister</a></td><td class="grid">3/28/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK5K/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Apollo 13</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK4G/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Cinderella Man</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK3C/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Doom</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK3M/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Jarhead</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK3W/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Serenity</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTUY/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Sky Captain and the World of Tomorrow</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Sleepy Hollow</td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK46/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The 40 Year-Old Virgin</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTXQ/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Bourne Supremacy</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK4Q/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Chronicles of Riddick</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTV8/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Italian Job</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTVI/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Manchurian Candidate</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK5A/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">U-571</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1ZK50/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Van Helsing</a></td><td class="grid">March</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTWC/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">We Were Soldiers</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTU4/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Four Brothers</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTUO/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Sahara</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTTK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Aeon Flux</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTUE/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Lara Croft: Tomb Raider</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTW2/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">U2: Rattle and Hum</a></td><td class="grid">March</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTYK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Harry Potter and the Goblet of Fire</a></td><td class="grid">4/11/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJDY/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Lethal Weapon</a></td><td class="grid">4/11/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJDO/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Last Samurai</a></td><td class="grid">4/11/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJE8/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Training Day</a></td><td class="grid">4/11/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJEI/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Unforgiven</a></td><td class="grid">4/11/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJES/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Goodfellas</a></td><td class="grid">4/25/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJF2/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Kiss Kiss Bang Bang</a></td><td class="grid">4/25/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJFC/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Swordfish</a></td><td class="grid">4/25/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJFM/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Syriana</a></td><td class="grid">4/25/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTZE/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Matrix Trilogy</a></td><td class="grid">4/25/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJFW/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Blazing Saddles</a></td><td class="grid">5/9/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJGG/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Dukes of Hazzard</a></td><td class="grid">5/9/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJGQ/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Full Metal Jacket</a></td><td class="grid">5/9/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJH0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Rumor Has It</a></td><td class="grid">5/9/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTYU/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Terminator 3: Rise of the Machines</a></td><td class="grid">5/9/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJHA/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Charlie and the Chocolate Factory</a></td><td class="grid">5/16/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJHU/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Ocean's Twelve</a></td><td class="grid">5/16/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJHK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Green Mile (Special Edition)</a></td><td class="grid">5/16/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E5KJI4/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Troy</a></td><td class="grid">5/16/2006</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid">12 Monkeys</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Backdraft</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Conan the Barbarian</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Dante's Peak</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Dune</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">End of Days</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Mission Impossible Trilogy</td><td class="grid">Later this year</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Pitch Black</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Spy Game</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">The Bone Collector</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">The Thing</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid">Waterworld</td><td class="grid">Later this year</td><td class="grid">Universal Studios Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTWW/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Seven</a></td><td class="grid">No Date Given</td><td class="grid">New Line Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTX6/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Lord of the Rings Trilogy</a></td><td class="grid">No Date Given</td><td class="grid">New Line Home Entertainment</td></tr><tr><td class="grid">Black Rain</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTTU/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Braveheart</a></td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Coach Carter</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Elizabethtown</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Forrest Gump</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Ghost</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Grease</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Save the Last Dance</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">School of Rock</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">SpongeBob SquarePants</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Star Trek: First Contact</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTVS/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Ultimate Star Trek Movie Collection</a></td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Vanilla Sky</td><td class="grid">No Date Given</td><td class="grid">Paramount Home Entertainment</td></tr><tr><td class="grid">Derailed</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">The Libertine</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Transamerica</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Mrs. Henderson Presents</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Hoodwinked</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Wolf Creek</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">The Matador</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Lucky Number Sleven</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Breaking and Entering</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Decameron</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Last Legion</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Young Hannibal</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Scary Movie 4</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Sin City 2</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Pulse</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Passion of the Clerks</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Killshot</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Awake</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">School for Scoundrels</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid">Grind House</td><td class="grid">No Date Given</td><td class="grid">The Weinstein Company</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTWM/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Band of Brothers</a></td><td class="grid">No Date Given</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTYA/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Friends</a></td><td class="grid">No Date Given</td><td class="grid">Warner Home Video</td></tr><tr><td class="grid"><a href="http://www.amazon.com/exec/obidos/ASIN/B000E1MTZ4/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">The Aviator</a></td><td class="grid">No Date Given</td><td class="grid">Warner Home Video</td></tr></table>

<p><br />
<h2>Where can you try it out?</h2><br />
Just a few days ago, Toshiba released the details of their "HD DVD Tour", which was announced at CES this year. Below is the Toshiba HD DVD Tour Schedule -- Press Opportunities:</p>

<p>    1) New York, NY -- Feb 22, 2006, PC Richards -- NYC and Feb 21, Electronics Expo -- Paramus, NJ<br />
    2) Boston, MA -- March 1, 2006, Tweeter -- Newton MA<br />
    3) Chicago, IL -- March 1, 2006, ABT Electronics -- Glenview, IL<br />
    4) Washington, D.C. -- March 15, 2006, Myer Emco -- Fairfax, VA<br />
    5) Miami, FL -- Best Buy, Miami, FL<br />
    6) San Francisco, CA -- March 1, 2006, Fry's Electronics -- Sunnyvale, CA<br />
    7) Seattle, WA -- March 8, 2006, Fry's Electronics -- Renton, WA and March 7, Best Buy -- Bellevue, WA<br />
    8) Los Angeles, CA -- March 15, 2006, Fry's Electronics -- City of Industry, CA<br />
    9) Dallas, TX -- March 22, 2006, Fry's Electronics -- Plano, TX<br />
    10) Atlanta, GA -- March 8, 2006, Best Buy -- Atlanta, GA</p>

<p><br />
<h2>References</h2></p>

<ul><li><a href="/cgi-bin/ntlinktrack.cgi?http://www.hddvdprg.com/">The HD DVD Promotion Group</a></li><li><a href="/cgi-bin/ntlinktrack.cgi?http://www.tacp.toshiba.com/news/newsarticle.asp?newsid=113">Toshiba Press Release</a></li><li><a href="/cgi-bin/ntlinktrack.cgi?http://www.videobusiness.com/article/CA6300812.html">Video Business Online article</a></li><li><a href="/cgi-bin/ntlinktrack.cgi?http://www.toshiba.co.jp/hddvd/eng/index.htm">Toshiba Corporation</a></li></ul>
<strong>
Editor's Note</strong>: This is intended to be a living document, so if you have anY additions, suggestions, or comments, please let me know via the "Comments" section below.
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>February 28, 2006 10:12 AM</b>
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
			<?=getComments(341)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 341)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/hd-dvd-primer.php" type="text/javascript" charset="utf-8"></script>
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