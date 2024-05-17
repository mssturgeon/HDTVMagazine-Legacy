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
		AND e.entry_id = 883";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 883 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 883 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 883";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/canon-introduces-the-vixia-family-of-highdefinition-camcorders.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 883";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Canon Introduces the VIXIA Family of High-Definition Camcorders" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Canon Introduces the VIXIA Family of High-Definition Camcorders" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Canon Introduces the VIXIA Family of High-Definition Camcorders" />
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
	<title>HDTV Magazine - Canon Introduces the VIXIA Family of High-Definition Camcorders</title>
	<meta name="keywords" content="flash memory, dual flash, memory camcorders, memory card, high definition, canon, memory, flash, vixia, camcorder, camcorders, dual, video, image, consumers, dvd, card, high, consumer, definition, family, new, full, burner, sdhc" />
	<meta name="description" content="Canon U.S.A., Inc. proudly announces the VIXIA family - a new lineup of consumer High-Definition camcorders embracing Canon optical and imaging technologies for superior image quality and flexibility - at the 2008 International Consumer Electronics Show in Las Vegas (Booth #12606).

The new HD camcorder family - the Canon VIXIA HF10 Dual Flash Memory camcorder, VIXIA HF100 Flash Memory camcorder and VIXIA HV30 HD camcorder - reflects Canon's commitment to High-Definition imaging excellence. In addition..." />
	<meta name="title" content="Canon Introduces the VIXIA Family of High-Definition Camcorders" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Canon Introduces the VIXIA Family of High-Definition Camcorders" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/canon-introduces-the-vixia-family-of-highdefinition-camcorders.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Canon U.S.A., Inc. proudly announces the VIXIA family - a new lineup of consumer High-Definition camcorders embracing Canon optical and imaging technologies for superior image quality and flexibility - at the 2008 International Consumer Electronics Show in Las Vegas (Booth #12606).

The new HD camcorder family - the Canon VIXIA HF10 Dual Flash Memory camcorder, VIXIA HF100 Flash Memory camcorder and VIXIA HV30 HD camcorder - reflects Canon's commitment to High-Definition imaging excellence. In addition..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=883', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/canon-introduces-the-vixia-family-of-highdefinition-camcorders.php">Canon Introduces the VIXIA Family of High-Definition Camcorders</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2008</b>
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
				<p class="prtitle">Canon U.S.A. Introduces the VIXIA Family of High-Definition Camcorders for the Ultimate Consumer Experience</p>

<center><i>Models Include Breakthrough Use of Dual Flash Memory, Genuine Canon Optics, And Other Proprietary Technologies, Expanding Consumers' Recording Options</i></center><br />
<br />

<p>2008 International CES</p>

<p><B>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)</B>--Canon U.S.A., Inc. proudly announces the VIXIA family - a new lineup of consumer High-Definition camcorders embracing Canon optical and imaging technologies for superior image quality and flexibility - at the 2008 International Consumer Electronics Show in Las Vegas (Booth #12606).</p>

<p>The new HD camcorder family - the Canon VIXIA HF10 Dual Flash Memory camcorder, VIXIA HF100 Flash Memory camcorder and VIXIA HV30 HD camcorder - reflects Canon's commitment to High-Definition imaging excellence. In addition, the previously released HG10 AVCHD Hard Disk Drive camcorder and HR10 AVCHD DVD camcorder join the VIXIA family, giving consumers a variety of formats to choose from, all of which deliver a superior High-Definition experience. Also being introduced is the DW-100 DVD Burner*.</p>

<p>"We are very excited about our new VIXIA family of camcorders, as well as being an innovator by offering Dual Flash Memory," said Yuichi Ishizuka, senior vice president and general manager, Consumer Imaging Group, Canon U.S.A. "Consumers are actively investing in HD televisions and they're discovering the value of capturing memories in HD. Whichever format consumers may prefer, including our revolutionary Dual Flash Memory, all VIXIA camcorders share Genuine Canon Optics and a host of Canon technologies allowing precious moments to be preserved with unrivaled color and clarity."</p>

<p><br />
<B>VIXIA Core Technologies</B></p>

<p>All VIXIA camcorders feature Canon core technologies to create HD video that possesses the highest level of image quality - a Genuine Canon HD Video Lens incorporates over 70 years of optics experience in professional broadcast and photography; a Canon designed and manufactured HD CMOS Image Sensor for Full HD (1920 x 1080) image capture; the Canon-developed DIGIC DV II Image Processor for superior color and clarity; Instant AutoFocus for fast and accurate auto focusing, crucial for HD; and SuperRange Optical Image Stabilization, which corrects a wide range of camcorder vibration for virtually shake-free images.</p>

<p><br />
<B>Dual Flash Memory - The Ultimate Consumer Convenience</B></p>

<p>Canon's breakthrough use of Dual Flash Memory - the ability to record to an internal Flash drive as well as a removable SDHC memory card - allows consumers to experience a new level of performance, style and flexibility. Dual Flash Memory allows consumers to record video to the camcorder's internal Flash drive even if they do not have a memory card. When the internal Flash drive becomes full, footage can be easily transferred to an SDHC memory card and when it comes time to view their video, the card is simply placed into a memory card reader in a computer or HDTV for instant viewing. Furthermore, having a SDHC memory card slot allows for expandability, since greater capacity can be added in the future by purchasing additional cards.</p>

<p>Flash Memory boasts a number of advantages and end-user benefits for maximum convenience and flexibility. Since Flash Memory is a solid-state memory format and has no moving parts, the camcorder can be smaller, more compact and lighter than ever before, allowing it to be carried anywhere. Additionally, Flash Memory is a highly stable method of storage, and as a result, accidental jolts to the camcorder are significantly less likely to result in failure or data loss. Consumers will also enjoy the camcorder's low power consumption, which leads to longer battery time. Compared with other types of storage, Flash Memory camcorders are able to read and write data faster, so users can start recording faster and have immediate access to their recorded scenes.</p>

<p><br />
<B>VIXIA HF10 Dual Flash Memory and VIXIA HF100 Flash Memory Camcorders</B></p>

<p>Despite their compact size, the VIXIA HF10 Dual Flash Memory and HF100 Flash Memory camcorders are packed with advanced technology and a wealth of features to create stunning quality video. The VIXIA HF10 Dual Flash Memory camcorder offers the flexibility of recording up to 6 hours of High-Definition video to a 16GB internal Flash drive, as well as the option of recording to an SDHC memory card. The HF100 Flash Memory camcorder features an SDHC memory card slot only. The SDHC slot provides future storage expandability with both models. These camcorders also offer other sophisticated new features, including a newly designed Genuine Canon 12x HD Video Lens, a robust Canon 3.3 Megapixel Full HD CMOS Image Sensor, and Full HD Lens-to-Screen (1920 x 1080 Full HD resolution to capture, record and output).</p>

<p>In addition to 24p Cinema Mode, which allows users to mimic the look of Hollywood-style movies, the VIXIA HF10 Dual Flash Memory and HF100 Flash Memory camcorders offer a new feature called 30p Progressive Mode. Canon's 30p Progressive Mode, once exclusive to pro-level camcorders, delivers clarity for fast action events, such as sports or news, and is the perfect frame rate for clips intended to be posted on the Web. A 2.7" Widescreen Multi-Angle Vivid LCD offers a wide viewing angle, making it visible from any direction. It also offers an expanded color range to more accurately reflect what users will see later on their HDTV. The models use an Intelligent Lithium-ion Battery, which indicate the remaining battery time down to the minute. Furthermore, the VIXIA HF10 Dual Flash Memory and HF100 Flash Memory camcorders offer a newly designed Mini Advanced Accessory Shoe, providing cable-free connectivity to an optional Canon microphone or video light. A microphone terminal with manual level control delivers additional audio flexibility and a fully functional 3.1 Megapixel digital camera is built right in, allowing consumers to capture high-quality still images with a wide selection of Advanced Photo features.</p>

<p><br />
<B>VIXIA HV30 HD Camcorder</B></p>

<p>As the successor to the highly acclaimed, award-winning Canon HV20 HD Camcorder, the VIXIA HV30 HD camcorder provides consumers with the ability to record HD quality video to MiniDV cassettes. Wrapped in a sophisticated black exterior, the VIXIA HV30 camcorder features a Genuine Canon 10X HD Video Lens, Canon 2.96 Megapixel Full HD CMOS Image Sensor, DIGIC DV II Image Processor, a 30p Progressive Mode (and 24p Cinema Mode), and a 2.7" Widescreen Multi-Angle Vivid LCD. In addition, the VIXIA HV30 camcorder is compatible with Canon's high capacity BP-2L24H Lithium-ion battery.</p>

<p><br />
<B>DW-100 DVD Burner*</B></p>

<p>The Canon DW-100 DVD Burner is the perfect companion for Canon Flash Memory camcorders and Canon Hard Disk Drive camcorders. It allows a consumer to burn all, part or previously recorded video from a compatible Canon camcorder to a DVD. In addition to burning Standard Definition DVDs, the DW-100 can also burn AVCHD DVDs which can be played in compatible Blu-Ray players. The DW-100 DVD Burner has only three buttons: power, record and eject, making operation fast and easy. Unlike other similar yet daunting devices, the DW-100 DVD Burner is designed for one-touch operation: connect via USB, set and burn. The DW-100 can also act as a player itself by connecting to an HDTV through the Canon VIXIA HF10, VIXIA HF100 or VIXIA HG10 camcorders.</p>

<p>Available in late April, the VIXIA HF10 Dual Flash Memory and VIXIA HF100 Flash Memory camcorders will have an estimated retail price of $1,099 and $899, respectively.** The Canon VIXIA HV30 is scheduled to be available in late February for the estimated retail price of $999.** The Canon DW-100 DVD Burner is scheduled to be available in late April for the estimated retail price of $269.**</p>

<p><br />
<B>About Canon U.S.A., Inc.</B></p>

<p>Canon U.S.A., Inc. delivers consumer, business-to-business, and industrial imaging solutions. Its parent company, Canon Inc. (NYSE:CAJ), a top patent holder of technology, ranking third overall in the U.S. in 2006†, with global revenues of $34.9 billion, is listed as one of Fortune's Most Admired Companies in America and is on the 2007 BusinessWeek list of "Top 100 Brands." To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting www.usa.canon.com/pressroom.</p>

<p>All referenced product names, and other marks, are trademarks of their respective owners.</p>

<p>Prices, availability and specifications are subject to change without notice.</p>

<p>* The DW-100 has not been authorized as required by the rules of the Federal Communications Commission or tested for compliance with the U.S. Federal Performance Standard For Laser Products as mandated by 21CFR; as such, this device is not, and may not be, offered for sale or lease, or sold or leased in the United States until such authorization is obtained.</p>

<p>**All prices are estimated retail prices. Actual prices are determined by individual dealers and may vary.</p>

<p>IFI Patent Intelligence, January 11, 2007</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2008  7:47 AM</b>
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
			<?=getComments(883)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 883)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/canon-introduces-the-vixia-family-of-highdefinition-camcorders.php" type="text/javascript" charset="utf-8"></script>
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