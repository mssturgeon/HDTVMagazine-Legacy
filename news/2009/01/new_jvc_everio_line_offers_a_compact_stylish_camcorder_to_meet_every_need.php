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
		AND e.entry_id = 1661";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1661 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1661 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1661";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1661";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" />
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
	<title>HDTV Magazine - New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need</title>
	<meta name="keywords" content="avg mbps, rec modes, min min, data battery, battery charger, min, jvc, everio, memory, models, recording, card, dual, camcorders, trademarks, video, battery, avg, new, youtube, mbps, definition, line, model, camcorder" />
	<meta name="description" content="JVC today announced a new line of Everio camcorders that offers innovations in video sharing, dual storage memory models and hi-def camcorders no larger than their diminutive standard definition counterparts.

For 2009, what the Everio line offers is choice - in storage medium, color and sharing. The line includes hard disk drive (HDD) models, memory camcorders, and for the first time dual memory models, including standard and high definition dual..." />
	<meta name="title" content="New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="JVC today announced a new line of Everio camcorders that offers innovations in video sharing, dual storage memory models and hi-def camcorders no larger than their diminutive standard definition counterparts.

For 2009, what the Everio line offers is choice - in storage medium, color and sharing. The line includes hard disk drive (HDD) models, memory camcorders, and for the first time dual memory models, including standard and high definition dual..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1661', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php">New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2009</b>
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
				<p class="prtitle">New JVC Everio Line Offers a Compact, Stylish Camcorder to Meet Every Need</p>

<center><i>2009 line includes palm-sized new high definition models, hard disk and memory recording, more color choices and new sharing options.</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 8 /PRNewswire/</B> -- JVC today announced a new line of Everio camcorders that offers innovations in video sharing, dual storage memory models and hi-def camcorders no larger than their diminutive standard definition counterparts.</p>

<p>For 2009, what the Everio line offers is choice - in storage medium, color and sharing. The line includes hard disk drive (HDD) models, memory camcorders, and for the first time dual memory models, including standard and high definition dual SD memory card camcorders, the world's first dual card slot models. There's also a standard definition model that combines SD card recording with internal flash memory.</p>

<p>For sharing, all 2009 JVC Everios make it easy to watch videos on an iPod(R) or iPhone(R) by exporting videos to the user's iTunes(R) library with the new One Touch Export function provided by bundled software for Windows(R). This joins the One Touch Upload function for uploading to YouTube(TM), for 2009 a feature available on all Everio camcorders, and One Touch DVD Creation function for easy archiving and sharing by disc, a long-time Everio feature.</p>

<p>All 2009 Everio camcorders are extremely compact, with the high definition models for the first time the same size as standard definition Everios that use the same media. In addition, more models offer color options for 2009, and the choice of colors has expanded to six.</p>

<p>The 2009 JVC Everio line includes three HD Everio models that all record 1920 x 1080 full HD video. The GZ-HD300, with 60GB hard drive, and the GZ-HD320, with 120GB hard drive, offer HDD storage for extra-long recording times. The GZ-HM200 offers dual SD card recording, with continuous recording from one card to the next, and is one of the smallest and lightest full HD camcorders available.</p>

<p>In standard definition, there are three new Everio G series models - the 60GB GZ-MG630, the 80GB GZ-MG670 and the 120GB GZ-MG680. The Everio S series of memory camcorders includes two models - the GZ-MS120, with dual SD card slots, and the GZ-MS130, with an SD card slot and 16GB of internal flash memory. All dual memory models offer continuous recording.</p>

<p><br />
<B>Availability and pricing:</B><br />
<pre><br />
  Model              Available                    National Ad Value<br />
  GZ-MS120           February                     $299.95<br />
  GZ-MS130           February                     $349.95<br />
  GZ-MG630           January                      $429.95<br />
  GZ-MG670           January                      $479.95<br />
  GZ-MG680           February                     $549.95<br />
  GZ-HM200           March                        $579.95<br />
  GZ-HD300           February                     $699.95<br />
  GZ-HD320           February                     $799.95</pre></p>

<p><br />
<B>About JVC Company of America</B></p>

<p>JVC Company of America, headquartered in Wayne, New Jersey, is a division of JVC Americas Corp., a wholly-owned subsidiary of Victor Company of Japan Ltd., and a holding company for JVC companies located in North and South America. JVC distributes a complete line of video and audio equipment, including high definition displays, camcorders, DVD players and recorders, home and portable audio equipment, headphones, mobile entertainment products and recording media. For further product information, visit JVC's Web site at http://www.jvc.com/ or call 800-526-5308.</p>

<p>Trademarks</p>

<p>- YouTube and the YouTube logo are trademarks and/or registered trademarks of YouTube LLC. This product's YouTube(TM) upload functionality is included under license from YouTube LLC. The presence of YouTube(TM) upload functionality in this product is not an endorsement or recommendation of the product by YouTube LLC.</p>

<p>- Microsoft(R) and Windows(R) are either registered trademarks or trademarks of Microsoft Corporation in the United States and/or other countries.</p>

<p>- Apple, Apple logo, Macintosh, Mac OS, QuickTime iMovie, Final Cut Pro, iTunes, iPod, and iPhone are trademarks of Apple Inc. registered in the United States and other countries.</p>

<p>- "AVCHD" and the "AVCHD" logo are trademarks of Panasonic Corporation and Sony Corporation.</p>

<p>- The microSD and microSDHC logos are trademarks of the SD Card Association.</p>

<p>- All brand names are trademarks, registered trademarks, or trade names of their respective holders.</p>

<p>JVC Everio Camcorders - 2009:</p>

<p>Approx. recording times for each recording mode and number of storable still images</p>

<p>  [HD Everio] HD Hard Disk Camcorder GZ-HD320, GZ-HD300<br />
<pre><br />
  4 Rec Modes:             Built-in HDD             microSDHC<br />
  UXP, XP, SP, EP          120GB model 60GB model   8GB          4GB</p>

<p>  Video UXP  Avg. 24 Mbps  11 hr       5 hr 30 min  40 min       20 min<br />
        XP   Avg. 17 Mbps  15 hr       7 hr 30 min  1 hr         30 min<br />
        SP   Avg. 12 Mbps  21 hr       10 hr        1 hr 28 min  44 min<br />
        EP   Avg. 5  Mbps  50 hr       25 hr        3 hr 20 min  1 hr 40 min<br />
  Stills                   9999</p>

<p>  [HD Everio] HD Memory Camcorder (Dual SD Slot) GZ-HM200</p>

<p>                          Dual SDHC<br />
  Rec Modes               32GB + 32 GB 16GB + 16GB  8GB + 8GB   4GB + 4GB</p>

<p>  Video UXP Avg. 24 Mbps  5 hr 20 min  2 hr 40 min  1 hr 20 min 40 min<br />
        XP  Avg. 17 Mbps  8 hr         4 hr         2 hr        1 hr<br />
        SP  Avg. 12 Mbps  11 hr 44 min 5 hr 52 min  2 hr 56 min 1 hr 28 min<br />
        EP   Avg. 5 Mbps  26 hr 40 min 13 hr 20 min 6 hr 40 min 3 hr 20 min<br />
  Stills                  9999</p>

<p>  [Everio G] Hard Disk Camcorder GZ-MG680, GZ-MG670, GZ-MG630</p>

<p>              Built-in HDD                           microSDHC<br />
  Rec Modes   120GB model  80GB model   60GB model   8GB         4GB</p>

<p>  Video Ultra 28 hr 40 min 19 hr        14 hr 20 min 1 hr 54 min 57 min<br />
        Fine  42 hr 40 min 28 hr 20 min 21 hr 20 min 2 hr 50 min 1 hr 25 min<br />
        Norm  56 hr 20 min 37 hr 40 min 28 hr 10 min 3 hr 46 min 1 hr 53 min<br />
        Eco   150 hr       100 hr       75 hr        9 hr 56 min 4 hr 58 min<br />
  Stills      9999</p>

<p>  [Everio S] Memory Camcorder (Dual SD Slot) GZ-MS120</p>

<p>  Rec Modes     When using 2 SDHC Cards<br />
                32GB + 32GB     16GB + 16GB     8GB + 8GB     4GB + 4GB</p>

<p>  Video Ultra   15 hr           7 hr 30 min     3 hr 40 min   2 hr<br />
        Fine    22 hr 40 min    11 hr 20 min    5 hr 40 min   2 hr 40 min<br />
        Norm    30 hr           15 hr           7 hr 30 min   3 hr 40 min<br />
        Eco     80 hr           40 hr           19 hr 50 min  10 hr<br />
  Stills        9999</p>

<p>  [Everio S] Memory Camcorder (Single SD Slot + Internal Memory) GZ-MS130</p>

<p>               When using an SDHC Card and internal 16GB memory<br />
  Rec Modes    32GB + 16GB    16GB + 16GB   8GB + 16GB    4GB + 16GB</p>

<p>  Video  Ultra 11 hr 15 min   7 hr 30 min   5 hr 35 min   4 hr 45 min<br />
         Fine  17 hr          11 hr 20 min  8 hr 30 min   7 hr<br />
         Norm  22 hr 30 min   15 hr         11 hr 15 min  9 hr 20 min<br />
         Eco   60 hr          40 hr         29 hr 55 min  25 hr<br />
  Stills Fine  9999<br />
</pre><br />
  Note:</p>

<p>Recording time figures are estimations. There may be cases in which actual recording time becomes shorter depending on the type of content being recorded.</p>

<p>For all models, SD/microSD card is not supplied.</p>

<p>To record video, an SDHC/microSDHC card with Class 4 or higher performance is required. For UXP mode, please use class 6 or higher. SD/microSD memory cards (256MB to 2GB) and SDHC memory cards (4GB to 32GB)/microSDHC memory cards (4GB and 8GB) have been tested for the following brands: Panasonic, Toshiba, SanDisk, and ATP. Note that using other media may result in recording failure or data loss.</p>

<p>  Key optional accessories for Everio<br />
  DVD Burner<br />
  --  CU-VD50 SHARE STATION Direct DVD Burner/Player<br />
  --  CU-VD3 SHARE STATION Direct DVD Burner<br />
  Data Battery and Charger<br />
  --  BN-VF808 Data Battery (730mAh)<br />
  --  BN-VF815 Data Battery (1460mAh)<br />
  --  BN-VF823 Data Battery (2190mAh)<br />
  --  AA-VF8 Battery Charger<br />
  --  VU-VT8K Battery Charger Kit (incl. BN-VF808 Data Battery and AA-VF8<br />
      Battery Charger)<br />
  Conversion Lens and Filter<br />
  --  GL-V0730 Wide-Angle Conversion Lens<br />
  --  GL-AT30 Telephoto Conversion Lens<br />
  --  GL-A30CPK Filter Kit</p>

<p><br />
<pre><br />
  Contacts:<br />
  Chelsea Vander Groef                          JVC News Hotline<br />
  JVC Company of America                        1-877-NEWS-JVC<br />
  973-317-5000, X5312                           JVCPR@PlannedTVArts.com<br />
  cvandergroef@jvc.com<br />
</pre></p>

<p>NOTE TO EDITORS: An electronic version of this release and high-resolution<br />
images are available at: http://www.jvc.com/ces. For images, please click<br />
here.</p>

<p>First Call Analyst:<br />
FCMN Contact:<br />
http://www.jvc.com/press/Vegas09/index.jsp</p>

<p>Source: JVC Company of America </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2009  8:28 AM</b>
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
			<?=getComments(1661)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1661)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/new-jvc-everio-line-offers-a-compact-stylish-camcorder-to-meet-every-need.php" type="text/javascript" charset="utf-8"></script>
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