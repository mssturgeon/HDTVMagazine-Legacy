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
		AND e.entry_id = 4427";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4427 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4427 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4427";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-infocomm-2011-from-the-rear-view-mirror.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4427";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - InfoComm 2011, From The Rear view Mirror" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - InfoComm 2011, From The Rear view Mirror" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - InfoComm 2011, From The Rear view Mirror" />
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
	<title>HDTV Magazine - HDTV Expert - InfoComm 2011, From The Rear view Mirror</title>
	<meta name="keywords" content="rated lumens, digital video, short throw, projection demo, trade show, ’s, video, show, digital, products, new, hdmi, –, dvi, full, projection, maximum, showed, pro, lumens, mpeg, lcd, projector, ethernet, encoder" />
	<meta name="description" content="Digital video was big at the show. So were higher brightness projectors, tiled displays, and lower prices!" />
	<meta name="title" content="HDTV Expert - InfoComm 2011, From The Rear view Mirror" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - InfoComm 2011, From The Rear view Mirror" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-infocomm-2011-from-the-rear-view-mirror.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Digital video was big at the show. So were higher brightness projectors, tiled displays, and lower prices!" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4427', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-infocomm-2011-from-the-rear-view-mirror.php">HDTV Expert - InfoComm 2011, From The Rear view Mirror</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>July  6, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <address>EDITOR’S NOTE: As some readers may already have heard, <strong><em>Pro AV</em></strong> magazine ceased publication on June 30. This column was originally scheduled to run in the July/August 2011 issue, which will not go to print. I’ve opted to run it here instead as the show was an important one and many significant trends emerged from Orlando, not the least of which is the cross-over of consumer-grade display products into the professional channel. This trend is already generating some interesting discussions on LinkedIn.</address>
<p>*************************************************************************************</p>
<p>The bags are unpacked. The flash drives have been downloaded, as have the photos. The expense reports are done, and my voice has finally come back after five straight days of teaching classes, enduring loud music during even louder conversations at dinner, and engaging in robust give-and-take arguments at trade show booths.</p>
<p>Yes, InfoComm 2011 is fading rapidly into the distance.  It is my busiest trade show, as I teach at least three courses every year and sometimes present at the pre-show Projection Summit. In between setting up and taking down all of the gear in my classes, I find a little free time to walk the show floor – unscheduled, as usual.</p>
<p>Pretty much all of my classes these days revolve around digital signal technology, whether it be digital video, wireless digital displays, or digital television. The wireless AV class had over 100 attendees, which is impressive considering most of the technology I showed isn’t available for purchase yet, but remains lurking in the wings.</p>
<p>The digital video class – which can get ’dry’ at times with discussions of MPEG, bit rates, and IPTV – also drew a strong crowd. Plus, I had a strong sense this year that more attendees were ‘getting it’ about digital video.</p>
<p>And no wonder, considering all of the encoder/decoder products shown in the Orlando Convention Center. In addition to the usual suspects, some companies I’d not heard of previously were set up and showing their encoders, decoders, switchers, and distribution products. More and more of these products were using optical fiber and category wire than ever before, along with the usual mix of HDMI and DVI ports.</p>
<div id="attachment_1311" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1311" href="http://www.hdtvexpert.com/?attachment_id=1311"><img class="size-full wp-image-1311" title="Salitek Orion Wall LS View MR 600p" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Salitek-Orion-Wall-LS-View-MR-600p.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Now THAT'S the way to watch HDTV!</p></div>
<p>In no particular order, here’s a baker’s dozen of interesting products and demos I found in my travels:</p>
<p><strong>Sharp</strong> had a small booth, but got everyone’s attention right away with their announcement of the LC-70LE732U, a 70-inch 1080p LCD display for digital signage and other pro AV applications. It’s got four HDMI inputs, Ethernet connectivity, uses a full LED backlight array, and will retail for $3,700. (Yes, you read that right, $3,700!) Think that’s gonna put a crimp into the front projector market?</p>
<p><strong>Casio</strong> now has a pro installation version of their lamp-less (laser and LED) DLP projectors. The PRO XJ-H1650 is rated at 3500 lumens and has a full range of connectivity options, although (inexplicably) it comes with two 15-pin VGA ports and just one HDMI input. There’s also a short-throw version with optional interactive whiteboard, a combination I saw in at least a dozen other booths.</p>
<div id="attachment_1312" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1312" href="http://www.hdtvexpert.com/?attachment_id=1312"><img class="size-full wp-image-1312" title="Casio Pro Projector MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Casio-Pro-Projector-MR.jpg" alt="" width="600" height="380" /></a><p class="wp-caption-text">Casio's got a new lamp-less installation projector (and yes, it IS mounted upside-down!).</p></div>
<p><strong>Kramer Electronics </strong>is one of several new members of the HDBaseT Alliance and had two new transmitters and one receiver in their booth. The TP-581T/582T transmitters combine HDMI video and audio, bi-direction 100BaseT Ethernet, RS232, and IR controls into a single Cat 5e cable at distances up to 328 feet. The TP-582R receiver converts all signals back to their native format. Maximum bandwidth is 2.25 Gb/s per graphics channel.</p>
<p><strong> </strong></p>
<p><strong>Sanyo</strong> has a high-powered 3LCD projector offering for the business and education channels. The PLC-WU3800 is rated at 3800 lumens, but will be ticketed at just under $2,000. It’s a wide XGA design (1280×800) fitted with a 1.6x zoom lens and instant shut-down power cycling. Sanyo also had a panoramic 3D demo using ultra short-throw projectors that attracted a bit of a crowd.</p>
<p><strong>BenQ</strong> is in the installation projector game with their SP981, a full 1080p single-chip DLP chassis. It’s rated at 4500 lumens and has a 1.5x zoom lens, manual lens shift in both axes, and HQV image processing. It seems that 3500 – 4000 lumens is the ‘new’ 2500 lumens these days!</p>
<div id="attachment_1313" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1313" href="http://www.hdtvexpert.com/?attachment_id=1313"><img class="size-full wp-image-1313" title="Sharp LC-70LE732U Exhibit MR 600p" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Sharp-LC-70LE732U-Exhibit-MR-600p.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Sharp's new 70-inch pro AV display for $3,800 generated lots of buzz (and this wasn't even the real thing).</p></div>
<p><strong></strong></p>
<div id="attachment_1322" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1322" href="http://www.hdtvexpert.com/?attachment_id=1322"><img class="size-full wp-image-1322" title="Sanyo 3D Short-Throw MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Sanyo-3D-Short-Throw-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Sanyo showed a clever ultra short-throw 3D projection demo.</p></div>
<p>Visionary Solutions came to Orlando with a full rack of MPEG encoder products. VS is considered to be one of the best at what they do, and their new AVN441 H.264 encoder ‘blade’ is now shipping. It’s an ultra-compact plug-in card that converts DVI, HDMI, component, or (ugh!) composite video into an MPEG4 stream for digital distribution. Maximum bit rates are 5 to 20 MB/s for HD signals.</p>
<p><strong>Arrive Systems</strong> had one of the more unusual new products at the show. It’s a rack-ready, all-in-one AV switcher with support for DVI, HDMI, analog video, an Ethernet router, analog audio-follow switching, and an 8-outlet power strip, plus low-voltage control, phantom power, and an Ethernet port to monitor everything. The whole shebang is fitted to a recycled, formed aluminum housing that doubles as a heat sink, and the company claims to be ‘green’ in its manufacturing process as a result.</p>
<p><strong>Panasonic</strong> has a new, easy-to-set-up high definition videoconferencing system. It runs on the popular H.264 codec and supports a wide range of connectivity options, including a robotic wide-angle camera and a conventional consumer-grade camcorder that fits to a tiny tripod and allows close-up views of everything from schematics to circuit boards. The buy-in price is around $12,000 for a basic system – watch how fast that price comes down – and picture quality was excellent.</p>
<div id="attachment_1314" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1314" href="http://www.hdtvexpert.com/?attachment_id=1314"><img class="size-full wp-image-1314" title="Canon Parabolic Screen MR 600p" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Canon-Parabolic-Screen-MR-600p.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Talk about immersive! Canon had this parabolic projection demo in their booth.</p></div>
<div id="attachment_1315" class="wp-caption aligncenter" style="width: 458px"><a rel="attachment wp-att-1315" href="http://www.hdtvexpert.com/?attachment_id=1315"><img class="size-full wp-image-1315" title="Prysm Vertical Kiosk MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Prysm-Vertical-Kiosk-MR.jpg" alt="" width="448" height="600" /></a><p class="wp-caption-text">Prysm is pushing its laser-phosphor displays into retail signage.</p></div>
<p><strong>Ciil Technologies</strong> (pronounced <em>See-All</em>) showed waterproof, dustproof 1080p LCD monitors at the show, designed for outdoor and other rugged installations. The glass is impact-resistant and the monitors have automatic light sensing to adjust brightness for high ambient light environments. Not the first time we’ve seen this type of product, but more of them are now coming to market at full HD resolution.</p>
<p><strong>Extron</strong> showed its first home-grown MPEG-4 H.264 encoder. The SME 100 accepts analog RGB or DVI signals to a maximum resolution of 1600×1200 with embedded or discrete audio, and 1920x1080p video at a maximum frame rate of 60 Hz. It also contains a three-input switcher.</p>
<p><strong> </strong></p>
<p><strong>Salitek </strong>had a ginormous LCD videowall at the show, made up of forty Orion OPM-4260 plasma monitors. You don’t often see plasma used in videowalls, but this one was truly spectacular (as the press release claimed). The only drawback might be power consumption, which would be on the order of 12,000 watts (40x 300 watts per panel). Each OPM-4260 has a 2mm bezel for a nearly seamless presentation.</p>
<div id="attachment_1316" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1316" href="http://www.hdtvexpert.com/?attachment_id=1316"><img class="size-full wp-image-1316" title="Samsung Ultra-Thin Commercial LCD Display MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Samsung-Ultra-Thin-Commercial-LCD-Display-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">The professional AV channel is just as nuts about ultra-thin displays as the consumer marketplace is.</p></div>
<p><strong>Stewart Filmscreens</strong> is tackling the tricky task of combining an optimized 2D low-gain screen with one for 3D that has higher brightness while minimizing hot-spotting. The result is something called Silver 5D screen material (clever, eh?), and it’s available in sizes up to 40’ x 90’. Silver 5D is designed for passive 3D viewing and can also be microperforated.</p>
<p><strong>Mitsubishi</strong> showed the first integrated fiber optic interface modules I’ve ever spotted in a rear projection cube. The company’s Seventy Series DLP rear-projection cubes (1920×1080, 70”) now have a <strong>Thinklogical </strong>fiber optic input card. The complete TX/RX set encodes DVI signals to a parallel data format with a maximum data rate of 6.25 Gb/s.</p>
<p>Finally, <strong>Haivision</strong> took the wraps off their Viper streaming/recording product. The Viper is an H.264 appliance that can capture, play back, and stream SD and HD video content through either dual DVI or HD-SDI inputs at full frame rates. Everything is controlled through an intuitive touch panel, and the Viper can also function as a standalone IP video server. (Plus it’s named after a ‘super-cool aquatic animal,’ as the press release states.)</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>July  6, 2011  9:26 AM</b>
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
			<?=getComments(4427)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4427)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-infocomm-2011-from-the-rear-view-mirror.php" type="text/javascript" charset="utf-8"></script>
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