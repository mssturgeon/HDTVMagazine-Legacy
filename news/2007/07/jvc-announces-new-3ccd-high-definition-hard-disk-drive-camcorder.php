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
		AND e.entry_id = 642";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 642 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 642 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 642";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/07/jvc-announces-new-3ccd-high-definition-hard-disk-drive-camcorder.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 642";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder" />
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
	<title>HDTV Magazine - JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder</title>
	<meta name="keywords" content="high definition, hard disk, jvc company, company america, recording time, jvc, recording, definition, high, new, everio, camcorder, trademarks, hard, company, dvd, lens, ccd, mode, built, image, footage, using, features, disk" />
	<meta name="description" content="JVC Company of America today announced the launch of a new consumer high definition camcorder that combines a 3-CCD image sensor, high definition 1440 x 1080 recording and embedded 60GB hard disk storage in a compact size that fits in the palm of the hand.

The new HD Everio GZ-HD3 follows the launch earlier this year of the high definition HD Everio GZ-HD7, which combined the pristine image quality of high definition with the convenience and high capacity of recording to a built-in hard drive. With the new GZ-HD3, JVC brings these same benefits to a wider audience in a palm-sized camcorder that's easy to carry and offers point-and-shoot simplicity." />
	<meta name="title" content="JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/07/jvc-announces-new-3ccd-high-definition-hard-disk-drive-camcorder.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="JVC Company of America today announced the launch of a new consumer high definition camcorder that combines a 3-CCD image sensor, high definition 1440 x 1080 recording and embedded 60GB hard disk storage in a compact size that fits in the palm of the hand.

The new HD Everio GZ-HD3 follows the launch earlier this year of the high definition HD Everio GZ-HD7, which combined the pristine image quality of high definition with the convenience and high capacity of recording to a built-in hard drive. With the new GZ-HD3, JVC brings these same benefits to a wider audience in a palm-sized camcorder that's easy to carry and offers point-and-shoot simplicity." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=642', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/07/jvc-announces-new-3ccd-high-definition-hard-disk-drive-camcorder.php">JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 19, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">JVC Announces New 3CCD High Definition Hard Disk Drive Camcorder</p>

<center><i>New palm-sized HD Everio GZ-HD3 delivers hi-def performance to a wider market</i></center><br />
<br />

<p><img src="/images/products/jvc-gz-hd3.jpg" alt="JVC HD Everio GZ-HD3" align="left" /><B>WAYNE, N.J., July 19 /PRNewswire/ </B>-- JVC Company of America today announced the launch of a new consumer high definition camcorder that combines a 3-CCD image sensor, high definition 1440 x 1080 recording and embedded 60GB hard disk storage in a compact size that fits in the palm of the hand.</p>

<p>The new HD Everio GZ-HD3 follows the launch earlier this year of the high definition HD Everio GZ-HD7, which combined the pristine image quality of high definition with the convenience and high capacity of recording to a built-in hard drive. With the new GZ-HD3, JVC brings these same benefits to a wider audience in a palm-sized camcorder that's easy to carry and offers point-and-shoot simplicity.</p>

<p><br />
<B>Main Features of the HD Everio GZ-HD3</B></p>

<p>1. 3-CCD Camera System for 1440x1080 HD Recording<br />
-- By using three 16:9 progressive scan CCDs, each dedicated to one of the three primary colors - red, green, blue - the image sensor can capture truly lifelike images and deliver vivid and accurate color reproduction. Since the CCDs themselves are 16:9, they record a pixel array with native 16:9 dimensions for viewing on an HDTV and authoring to DVD or Blu-ray disc. The lens block also uses CCD pixel shift technology to increase horizontal and vertical resolution.</p>

<p>As is the case with the GZ-HD7, the GZ-HD3's image processing is driven by JVC's HD Gigabrid Engine, which processes images from the progressive CCDs in native progressive to maintain as much quality as possible while applying five different noise reduction technologies to improve vertical resolution by approximately 30 percent from previous JVC standard definition camcorders. Images are reproduced with the depth and richness only possible with high- definition television, and feature natural skin tones, and vivid, glossy colors, especially green.</p>

<p>-- The HD Everio GZ-HD3 uses a Konica Minolta HD lens that ensures optimum performance with JVC's 3-CCD and HD recording technologies. The zoom lens features an ultra-compact hybrid aspherical lens system using low dispersion glass that plays a pivotal role in reducing aberrations and captures HD images with vivid colors and high resolution. The lens structure also contributes to the miniaturization of the camera.</p>

<p>2. Built-in 60GB HDD for 7 Hours of HD Recording</p>

<p>In addition to an SP mode that allows up to about seven hours of HD recording, the GZ-HD3 is also equipped with an XP mode that records at a higher bit rate for those extra special scenes. There's also a 1440CBR mode, which is convenient for those who wish to edit using HDV-compatible software.</p>

<table><tr><td>Rec Mode</td><td>Resolution</td><td>Recording Rate</td><td>Rec Time (approx)</td></tr>
<tr><td>XP</td><td>1440x1080i</td><td>VBR: max. 30Mbps</td><td>5 hrs.</td></tr>
<tr><td>SP</td><td>1440x1080i</td><td>VBR: max. 22Mbps</td><td>7 hrs.</td></tr>
<tr><td>1440CBR*</td><td>1440x1080i</td><td>VBR: approx.27Mbps</td><td>5 hrs.</td></tr></table>
*for HDV compatible stream via i.LINK

<p>3. Compact Body; Simple Controls</p>

<p>To make it easy to take the GZ-HD3 anywhere, the palm-sized camcorder features a lightweight, compact body 27 percent smaller than the GZ-HD7, and weighing a mere 1.5 lbs. It also offers point-and-shoot simplicity so no crucial scenes are missed, plus manual controls for more ambitious users.</p>

<p>4. Full Complement of Interfaces</p>

<p>JVC equipped the GZ-HD3 with a wide range of interface terminals, including HDMI and component outputs so HD footage can be viewed on the latest HDTV displays. And standard AV-S terminals that output a down-converted SD signal provide connectivity to ordinary standard definition TVs. USB and i.LINK connectors are included for transferring recordings to a personal computer for editing.</p>

<p>5. PC-less Archiving Using Exclusive DVD Burner</p>

<p>By connecting the GZ-HD3 directly to the optional CU-VD40 HD Everio SHARE STATION via USB, the user can burn HD footage to 12cm DVD discs to make backups and permanent archives of selected scenes in any desired order with just a few simple steps. Approximately 55 minutes of SP mode-recorded HD footage can be stored on 8.5GB dual layer DVD disc. The CU-VD40 can also be used with the GZ-HD7, and plays back in HD via HDMI or component, and in standard definition as well.</p>

<p>6. Bundled HD Editing CyberLink BD Solution(TM) Software for Windows, Plug in for Mac Using the supplied software, it is possible to view and edit HD footage on a Windows(R) PC, as well as author that footage to DVD and Blu-ray discs. A plug-in is also provided that allows Mac users to import the HD footage into editing applications like iMovie HD and Final Cut Pro.</p>

<p><br />
<B>Other HD Everio GZ-HD3 Features</B></p>

<p>1. Various shooting assist and manual functions:<br />
- Focus Assist outlines the in-focus part of the picture in red, blue or green to make focusing easier;<br />
- Digital Image Stabilization (DIS) provides important stability for hand held shooting;<br />
- The Zebra Function puts a stripe pattern on highlighted parts of the picture to assist in setting the exposure manually;<br />
- Program AE includes Aperture- and Shutter-Priority;<br />
- Joystick control for easy selection and operation of manual settings and menu items, including manual focus and quick control of focus mode.</p>

<p><br />
2. The SD card slot also stores stills and high definition video (SP mode only) on commonly available SDHC/SD memory cards (10MB/S hi-speed type card required for video recording; SDHC Class 6 or more is necessary, simultaneous recording to hard disk and SD card is not possible).</p>

<p>3. Automatic Video Light - "Auto Illumi.Light" The built-in light automatically turns on when shooting in low light situations.</p>

<p> 4. Convenience Features:<br />
 - Quick Power Off to prevent battery drain if the camera is left on by mistake;<br />
 - The Built-in Lens Cover protects the lens;<br />
 - Data Batteries allow the user to check remaining recording time, displaying battery level and maximum remaining recording time;<br />
 - Index Button to display remaining disk space and recording time;<br />
 - Remote Control.</p>

<p><br />
5. 2.8" 16:9 Widescreen Clear Bright LCD Monitor for viewing a bright, high-contrast image while shooting.</p>

<p>6. When pressed, the Direct Backup Button on the camcorder automatically launches the appropriate application to back-up only recordings that haven't yet been backed up to a computer.</p>

<p>7. New Dimple Pattern Grip design for solid and comfortable shooting.</p>

<p>8. The Mic Input and Accessory Shoe allow use of the optional MZ-V8 Stereo Microphone.</p>

<p>Like the entire JVC Everio line, the new GZ-HD3 offers the benefits of recording to a built-in hard drive. These include long recording time (five to seven hours on HD Everios; seven to 37 hours with SD Everios), no need to purchase and carry removable tapes or discs, direct access to desired scenes, easy scene deletion, in-camera basic editing, and no risk of mistakenly erasing a desired scene. In addition, using a built-in hard drive allows the camcorder to be more compact than designs that record to DVD or tape.</p>

<p>The JVC HD Everio GZ-HD3 will be available in early September, and will sell for about $1,300.</p>

<p><br />
<B>About JVC Company of America</B></p>

<p>JVC Company of America, headquartered in Wayne, New Jersey, is a division of JVC Americas Corp., a wholly-owned subsidiary of Victor Company of Japan Ltd., and a holding company for JVC companies located in North, Central, and South America. JVC distributes a complete line of video and audio equipment, including high definition displays, camcorders, DVD players and recorders, satellite systems, home and portable audio equipment, mobile entertainment products and recording media. For further product information, visit JVC's Web site at http://www.jvc.com/ or call 800-526-5308.</p>

<p>Notes:<br />
-- The terms and conditions above are as of the announcement date, and subject to change without notice.<br />
-- Microsoft(R) and Windows(R) are either registered trademarks or trademarks of Microsoft Corporation in the United States and/or other countries.<br />
-- Apple, Apple logo, Macintosh, Mac OS, QuickTime iMovie, and Final Cut Pro are registered trademarks of Apple Inc. in the United States.<br />
-- HDV and HDV logo are trademarks of Sony Corporation and Victor Company of Japan, Limited (JVC).<br />
-- i.LINK and i.LINK logo are trademarks of Sony Corporation.<br />
-- The SD and SDHC logos are trademarks of the SD Card Association.<br />
-- All brand names are trademarks, registered trademarks, or trade names of their respective holders.</p>

<p><br />
Note: For a flash presentation on the new JVC GZ-HD3, go to: http://camcorder.jvc.com/microsites/GZHD3/</p>

<p>Visit JVC's online press room at: www.jvc.com/press</p>

<p><br />
<B>Contacts</B></p>

<p>Terry Shea  JVC News Hotline<br />
JVC Company of America  1-877-NEWS-JVC<br />
973-317-5000, X5312  JVCPR@PlannedTVArts.com<br />
tshea@jvc.com</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 19, 2007  9:21 AM</b>
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
			<?=getComments(642)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 642)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/07/jvc-announces-new-3ccd-high-definition-hard-disk-drive-camcorder.php" type="text/javascript" charset="utf-8"></script>
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