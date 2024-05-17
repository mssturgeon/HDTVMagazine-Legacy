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
		AND e.entry_id = 1497";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1497 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1497 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1497";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/09/hdtv-and-home-theater-podcast-314-black-friday-predictions-and-neuros-osd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1497";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD</title>
	<meta name="keywords" content="blu ray, neuros osd, usb storage, circuit city, friday predictions, osd, blu, ray, video, device, videos, old, network, predictions, usb, less, any, computer, record, mpeg, storage, vhs, neuros, player, inch" />
	<meta name="description" content="Dennis from Medina OH sent us a link to an article that ran down a list of Black Friday Predictions  at BlackFriday @ GottaDeal.com. We want to do the same thing. The only rules we had were not to read the article prior to making our predictions. The article has predictions about computers, computer peripherals, GPS, and all things electronic. Our predictions will stay in the realm of HDTV and Home Theater.

We also recently received an email asking us for a good solution to archive old family videos that are locked away on VHS tapes. Many options require you to use the hard drive of your computer for the recording process. Today we take a look at a devices that easily connects to your analog sources and records to multiple destinations. The Neuros OSD lets you convert your treasured videos to digital so they can be enjoyed for years to come." />
	<meta name="title" content="HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/09/hdtv-and-home-theater-podcast-314-black-friday-predictions-and-neuros-osd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Dennis from Medina OH sent us a link to an article that ran down a list of Black Friday Predictions  at BlackFriday @ GottaDeal.com. We want to do the same thing. The only rules we had were not to read the article prior to making our predictions. The article has predictions about computers, computer peripherals, GPS, and all things electronic. Our predictions will stay in the realm of HDTV and Home Theater.

We also recently received an email asking us for a good solution to archive old family videos that are locked away on VHS tapes. Many options require you to use the hard drive of your computer for the recording process. Today we take a look at a devices that easily connects to your analog sources and records to multiple destinations. The Neuros OSD lets you convert your treasured videos to digital so they can be enjoyed for years to come." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1497', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/09/hdtv-and-home-theater-podcast-314-black-friday-predictions-and-neuros-osd.php">HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September 25, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=429&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-09-26.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Dennis from Medina OH sent us a link to an article that ran down a list of&nbsp;<a id="l.av" href="http://blackfriday.gottadeal.com/BlackFridayPredictions" target="_blank" title="Black Friday Predictions">Black Friday Predictions</a> &nbsp;at&nbsp;<a id="uj45" href="http://blackfriday.gottadeal.com/" target="_blank" title="BlackFriday @ GottaDeal.com">BlackFriday @ GottaDeal.com</a>.
We want to do the same thing. The only rules we had were not
to read the article prior to making our predictions. The article has
predictions about computers, computer peripherals, GPS, and all things
electronic. Our predictions will stay in the realm of HDTV and Home
Theater.
<div><br>
We
also recently received an email asking us for a good solution to archive old
family videos that are locked away on VHS tapes. Many options require
you to use the hard drive of your computer for the recording process.
Today we take a look at a devices that easily connects to your analog
sources and records to multiple destinations. The Neuros OSD lets you
convert your treasured videos to digital so they can be enjoyed for
years to come.<br>
<br></div>
<div><strong>Black Friday Predictions</strong></div>
<div><strong>Ara:</strong></div>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;"><em>HDTVs <br>
</em></blockquote>

<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<ul>
<li><span style="font-style: normal;">Target and Wal*Mart will have a tier one (SONY, Panasonic, Sharp) 32 inch LCD for less than $500.</span></li>
<li><span style="font-style: normal;">BestBuy and Circuit City will have a 42 inch Plasma for $750</span></li>
<li><span style="font-style: normal;">BestBuy and Circuit City will give away a Blu-ray Player with the purchase of a 60 inch Plasma</span></li>
<li><span style="font-style: normal;">No
one will have a door buster for a rear projection TV. Costco will have
the best bang for the buck deal on one. And that deal is available
today! A&nbsp;<a id="iini" href="http://www.costco.com/Browse/Product.aspx?Prodid=11290083&amp;whse=BC&amp;Ne=4000000&amp;eCat=BC%7C79&amp;N=4001386&amp;Mo=3&amp;No=3&amp;Nr=P_CatalogName:BC&amp;cat=4848&amp;Ns=P_Price%7C1%7C%7CP_SignDesc1&amp;lang=en-US&amp;Sp=C&amp;topnav=" target="_blank" title="65 inch Mitsubishi Medallion Series DLP">65 inch Mitsubishi Medallion Series DLP</a> &nbsp;for $1900.</span></li></ul>
<div><em>Blu-ray&nbsp;</em></div></blockquote>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<div>

<ul>
<li><span style="font-style: normal;">Wal*Mart will have a no name Blu-ray player for $150</span></li>
<li><span style="font-style: normal;">You will be able to find a name brand Blu-ray player that supports the full Blu-ray specification for $200.&nbsp;</span></li>
<li><span style="font-style: normal;">Target will have a buy one get one free deal on Blu-ray Movies.</span></li></ul>
<div><em>Receivers</em></div></div></blockquote>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<div>
<div>
<ul>
<li><span style="font-style: normal;">The Onkyo 606 will be found for less that $300 at Circuit City</span></li></ul>
<div><span style="font-style: normal;">Remote Control</span></div></div></div></blockquote>

<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<div>
<div>
<div>
<ul>
<li><span style="font-style: normal;">The Harmony One will go for $150 (Online Only)</span></li>
<li><span style="font-style: normal;">The Harmony 880 will go for $50 after Mail in rebate (Online Only)</span></li></ul></div></div></div></blockquote>
<div><strong>Braden:</strong></div>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;"><em>HDTVs <br>
</em></blockquote>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<ul>
<li><span style="font-style: normal;">42" plasma for less than $600<br>

</span></li>
<li><span style="font-style: normal;">40" LCD for less than $600</span><span style="font-style: normal;"></span></li>
<li><span style="font-style: normal;">60" or greater DLP for less than $1300 to beat Tiger's current deal of a <a title="73&quot; DLP for $1999" target="_blank" href="http://www.tigerdirect.com/applications/SearchTools/item-details.asp?EdpNo=3871732&amp;Sku=M402-7308" id="vmh8">73" Mitsubishi DLP for $1999</a> </span></li></ul>
<div><em>Blu-ray&nbsp;</em></div>
<ul>
<li><span style="font-style: normal;">Blu-ray players for $130</span></li>
<li><span style="font-style: normal;">Blu-ray movies for less than $10<br>
</span></li></ul><em>Other</em><br>
<ul>

<li>1 TB External Hard Drive for $100</li>
<li>Brand name HTiB for under $100<br>
</li></ul></blockquote>
<p>&nbsp;</p>
<p><strong><a id="a8qf" href="http://www.neurostechnology.com/" target="_blank" title="Neuros OSD">Neuros OSD</a><span>&nbsp;($175&nbsp;</span><a id="ucvk" href="http://www.htguys.com/shop.php?id=B000HXGIHE" target="_blank" title="Buy Now">Buy Now</a><span>) </span></strong><span><br>
The OSD is a small device that measures&nbsp;14
x 14 x 3.2 cm (5.5 x 5.5 x 1.25 inches) and weighs&nbsp;230g (8oz). Its made
out of plastic and feels sturdy in the hands. The OSD comes with the
necessary cables to connect the analog source and playback equipment.
There is also an included remote which is required to navigate the
OSD's onscreen menus. &nbsp;The main features of&nbsp;the&nbsp;OSD include:</span></p>

<ul style="margin-top: 0px; margin-bottom: 0px;">
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Video Capture</strong>&nbsp;-
Record any analog video in MPEG-4 format to any USB storage device. You
can even record directly to a computer of NAS device over your network.</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Audio Recorder</strong>&nbsp;-
Record analog audio in MP3 or AAC format to any USB storage device.&nbsp;You
can even record directly to a computer of NAS device over your network.</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Video Player</strong>&nbsp;- Play video from any USB storage device and Youtube</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Music Player</strong>&nbsp;- Play music from network or USB Storage</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Photo/Image Viewer</strong>&nbsp;- View Photos from USB and Network</li></ul>

<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;"><strong>Setup</strong></div>
<div style="margin-top: 0px; margin-bottom: 0px;">Setup
took five minutes. Connect the inputs, outputs and power. For our tests
we used an old VCR that was laying around the house. It was kept so one
day Ara could convert his old family videos to digital. So for this
review even Ara's wife was excited about the potential.&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">Once
the connections were made you set the date and time. Since we also
wanted to record to a network drive we tried to connected to a shared
drive on Ara's video server however, the OSD could not find shared
drives on any of&nbsp;the&nbsp;computers on the network. It may work better with
a Windows box or NAS device but we had no way to know for sure.</div><br>
<div style="margin-top: 0px; margin-bottom: 0px;"><strong>Performance</strong></div>
<div style="margin-top: 0px; margin-bottom: 0px;">We
converted a bunch of old videos that were shot on VHS and VHS-C. The
results we quite good. Well, as good as 10 year old VHS tapes can be.
So many precious memories starting to degrade away! The digital version
of the video looked every bit as good as the tape version. &nbsp;A thirty
minute tape was reduced to a 565 MPEG-4 video clip. The actual details
are 2.6Mbps, 640x480, 30 FPS, AAC Stereo.&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">The
conversion obviously happens in real time so it will take
a&nbsp;commitment&nbsp;to convert your family's video memories. At least it is a
plug and play proposition.&nbsp;</div>

<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">The
Neuros OSD also plays back files from your computer or your attached
USB storage. It can play back a wide range of formats including:&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<ul style="margin-top: 0px; margin-bottom: 0px;">
<li style="margin-top: 0px; margin-bottom: 0px;">MPEG-4 SP&nbsp;with&nbsp;MP3&nbsp;audio,&nbsp;30fps&nbsp;up&nbsp;to&nbsp;D1&nbsp;resolution&nbsp;(720x480)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">Quicktime&nbsp;6</li>

<li style="margin-top: 0px; margin-bottom: 0px;">MPEG-4&nbsp;AAC-LC&nbsp;stereo</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MP4&nbsp;format&nbsp;at&nbsp;up&nbsp;to&nbsp;D1&nbsp;resolution</li>
<li style="margin-top: 0px; margin-bottom: 0px;">H.263&nbsp;with&nbsp;MP3&nbsp;audio</li>

<li style="margin-top: 0px; margin-bottom: 0px;">FLV&nbsp;(for&nbsp;Playback&nbsp;of&nbsp;YouTube&nbsp;videos)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">ASF</li>
<li style="margin-top: 0px; margin-bottom: 0px;">AVI&nbsp;(including&nbsp;Divx&nbsp;and&nbsp;Xvid)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MP4</li>

<li style="margin-top: 0px; margin-bottom: 0px;">WMV&nbsp;(up&nbsp;to&nbsp;QVGA)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MOV</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MPG&nbsp;MPEG</li></ul>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">It had no issues playing back any videos we threw at it.&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">Other
than the device not recognizing the shared drives on the Mac the only
other complaint we had was the GUI. While we didn't expect Apple like
flare, it did leave us wanting a better experience.</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>

<div style="margin-top: 0px; margin-bottom: 0px;"><strong>Conclusion</strong></div>
<div style="margin-top: 0px; margin-bottom: 0px;">The
Neuros OSD is a simple and&nbsp;convenient&nbsp;way to convert your old VHS tapes
to&nbsp;digital. It was so much fun to see videos of my children growing up
and of my late father. The OSD is the kick in the pants we all need to
go out and digitize our family memories! It's HT Guys tested and wife
approved!</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September 25, 2008 11:47 PM</b>
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
			<?=getComments(1497)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1497)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/09/hdtv-and-home-theater-podcast-314-black-friday-predictions-and-neuros-osd.php" type="text/javascript" charset="utf-8"></script>
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