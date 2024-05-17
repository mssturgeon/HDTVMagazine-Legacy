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
		AND e.entry_id = 363";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 363 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 363 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 363";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/04/digital-connectivity-a-tutorial.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 363";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Digital Connectivity - A Tutorial" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Digital Connectivity - A Tutorial" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Digital Connectivity - A Tutorial" />
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
	<title>HDTV Magazine - Digital Connectivity - A Tutorial</title>
	<meta name="keywords" content="dual link, silicon image, single link, pin connector, second link, dvi, link, digital, hdmi, standard, pin, mhz, cable, hdcp, dual, video, single, signal, signals, mbps, connector, connections, equipment, pins, connection" />
	<meta name="description" content="The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device.  It is a connection with enough bandwidth for uncompressed HD signals.

IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called &quot;Fire Wire&quot; for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.

On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba." />
	<meta name="title" content="Digital Connectivity - A Tutorial" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Digital Connectivity - A Tutorial" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/04/digital-connectivity-a-tutorial.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device.  It is a connection with enough bandwidth for uncompressed HD signals.

IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called &quot;Fire Wire&quot; for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.

On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=363', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/04/digital-connectivity-a-tutorial.php">Digital Connectivity - A Tutorial</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>April 25, 2006</b>
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
				<blockquote>This is an excerpt from the <b>HDTV Technology Review 2006 Report</b> by Rodolfo La Maestra. If you are interested in the full version of this report, it is currently available from the <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/03/hdtv_technology_review_part_1_introduction.php">HDTV Technology Review, Part 1: Introduction</a><br>
Part 2: <a href="/articles/2006/04/1080p_into_hdtv_displays.php">1080p into HDTV Displays</a><br>

<p>This tutorial article was drafted when DVI was starting to be implemented in HDTVs and first appeared on my 2003 HDTV report.  Since then, it is customarily included in a separate section within each yearly report to provide the basic background about digital connections used in HDTV equipment.</p>

<p>Additionally, on each annual report I use other sections to provide an update of how these connections are being implemented on audio and video equipment year after year, what type of problems they have, what functionally they facilitate, what is recommended regarding technical requirements when looking for a product (such as having HDCP compliance, a big issue in the 2003/4 reports), etc.</p>

<p>In the 2006 report, HDMI is mentioned throughout the report, additionally there is a separate large section that covers the HDMI chips, the HDMI implementation, the trends of manufacturer's adoption, the specifications of the released versions, the issues surrounding incompatibility of HDMI suited products, the issues surrounding the new multi-channel audio hi-bit transported with HDMI, the implementation of content protection over the HDMI connection, etc.</p>

<p>Each yearly report adds a new layer of the year regarding HDMI, as issues, as upgrades, as implementation trends, organizations involved with, manufacturers using it for 1080p sets and blu-laser players, etc. </p>

<p>In other words, this except is just a section to get the reader familiarized with the basics of digital connectivity.  For the complete picture, including wireless digital connectivity, please consult the annual reports.<br />
</blockquote></p>

<p><br />
<h2>DVI</h2><br />
The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device.  It is a connection with enough bandwidth for uncompressed HD signals.</p>

<p>The 1.0 DVI specification is a point-to-point solution that supports video content but not audio.  DVI uses the Transition-Minimized Differential Signaling (TMDS) protocol developed by Silicon Image.  PanelLink is the Silicon Image's proprietary implementation of TMDS.</p>

<p>The HDCP (High-bandwidth Digital Content Protection) 1.0 specification was developed by Intel with contributions from Silicon Image in February 2000 to protect DVI outputs from being copied by providing a secure link between a video source and a display device.</p>

<p>HDCP offers authentication, encryption, and renewability.  The Motion Picture Association of America (MPAA) endorsed HDCP as the standard for the secure transmission of HD signals over DVI.</p>

<p>Most new DTV monitors and integrated displays have incorporated DVI or HDMI inputs, although on their first generation some panels were not HDCP compliant, now there is a large volume of H/DTV equipment that is.  However, some displays were reported to have interoperability problems regarding DVI/HDCP or HDMI/HDCP.</p>

<p>The DVI standard is able to handle single or dual link connections.  A single-link connection supports up to UXGA resolution of 1600 x 1200 at 60 Hz.  Dual-link connections provide bandwidth for resolutions beyond QXGA (2048 x 1536).</p>

<p>According to DVI specs a single link has 165 MHz/pixels capacity for 3 channels, Red, Green and Blue, each channel could support up to 1.65 Gbps speed rate, or a total of 4.95 Gbps for the 3 channels (165 MHz x 30 bits x sec).  Dual-link connections double that capacity to 330 MHz, with a speed-rate capacity up to 9.9 Gbps.</p>

<p>The 1080i HD format has 1125 total lines of 2200 pixels x frame (active image 1080x1920), requiring 74.25 MHz/pixels (1125 x 2200 x 30fps).  Each pixel contains data for RGB and is implemented by DVI with 30 bits (8 per each color plus another 6 for encoding).  An HD 74.25 MHz/pixel signal would require 2.2 Gbps speed rate.</p>

<p>A link of 3 channels supporting 165 MHz is sufficient for the 74.25 MHz HD 1080i signal without requiring the use of the second link, and will also be sufficient to transport a 1080p/60 frames x second signal at 148.5 MHz without requiring the second link.</p>

<p>If the signal to be transmitted would be higher than the single link capacity of 165 MHz, it would require the use of a dual DVI link connection, each link will carry half of the signal; the second link cannot be used with just what is exceeding 165 MHz of the first link.  For example, a 200 MHz signal would be carried with both links operating at 100 MHz each.</p>

<p>HDMI uses the same 165MHz capacity per link; dual-link uses the B connector with the second link pins.</p>

<p>DVI identifies and auto-configures the connected device.  If source equipment is connected with DVI single link to a display configured as dual link DVI, the image will experience a lower resolution.  Some first generation single link DVI cables use dual link connectors.  DVI standard cables have typically a five-meter distance limitation, although with better quality wiring, such as fiber-optic, higher distances are possible.</p>

<p>There are three types of DVI connectors:</p>

<p><u>DVI-I (integrated)</u>, carries a single or dual-link digital signal, with an additional analog signal for legacy devices.  The 29-pin DVI connector uses 24 pins for the digital data stream (12 for each link) and 5 pins (1 plus-shaped blade and 4 pins) to carry analog video and ground.</p>

<table><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image352.gif" alt="DVI-I"><br>DVI-I
</td></tr></table>

<p><u>DVI-D (digital)</u> carries digital-only video data to a display.  It is designed for 12 or 24 pin connections, and single/dual link operation (notice the lack of 4 pins, 2 above/2 below the flat blade).</p>

<table><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image354.gif" als="DVI-D"><br>DVI-D
</td></tr></table>

<p><u>DVI-A (analog)</u> is available for legacy analog applications to carry analog signals to a CRT monitor or an analog HDTV (claims to be better than VGA).  The three rows of eight pins have three pins missing in the first row, five missing in the second row and four missing in the third row, and that the "flat blade" contact seen to the left has two contacts above and below it.  There is no single or dual link in analog cables.</p>

<table><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image356.jpg" alt="DVI-A"><br>DVI-A
</td></tr></table>

<p>Regarding connecting plugs to receptacles:</p>

<p>A DVI-D plug can be connected to either DVI-D or DVI-I receptacles,<br />
A DVI-A plug can be connected to either DVI-I/A or VGA (w/adapter) receptacles,<br />
A DVI-A receptacle would accept DVI-I but not DVI-D.<br />
A DVI-I plug can be connected to either DVI-I or DVI-A receptacles (the 'A' ignores 'I's digital pins)</p>

<p><br />
<h2>IEEE1394</h2><br />
IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called "Fire Wire" for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.</p>

<p>In March 2000, an updated specification was approved, the 1394a.  The "a" standard supports speeds of 100Mbps, 200Mbps, and 400Mbps over a distance of 4.5 meters, and up to 63 peer-to-peer nodes/devices.</p>

<p>In 2001, the IEEE 1394 "b" standard emerged as a network technology (rather than as serial bus); it is capable of moving data streams at faster speeds over longer distances than the original.</p>

<p>The "b" standard specifications were intended to support up to 3,200 Mbps depending on the cable material, and permit the use of cabling materials not supported by the "a" standard.  It supports speeds up to 100Mbps over 100 meters of Category 5 wiring, 400 Mbps over 100 meters of plastic optical fiber, and up to 3,200 Mbps (or 3.2 Gbps) over 100 meters of glass optical fiber.</p>

<p>The "b" standard is compatible with the "a" standard; if an "a" device were plugged into a "b" component, the bus would deliver a maximum speed limited by the "a" standard (400Mbps).  Each "b" device can be set up to 100 meters apart from the next in sequence, allowing the total network to be quite significant in cable length.</p>

<p>The licensing fee for the use of the patented technology is $ 0.25 per system; chipsets are less than $5 each in volume.</p>

<p>It supports hot swapping and plug-and-play, so a consumer's 1394 bus can recognize automatically a 1394 device when it is connected/disconnected, and reconfigure itself.</p>

<p>The connection is now being used by a growing number of DTV equipment manufacturers for the transmission of compressed HD signals, such as D-VHS recording and networking DTV equipment.</p>

<p>There are three types of cables used for 1394.  The 6-conductor type has two separately shielded twisted pairs for data and two power wires in an overall shielded cable with 6-pin connectors on either side.  The 4-wire cable uses two separately shielded data cables without power wires in an overall shielded cable with 4-pin connectors on either end.  The third type of cable uses either type of actual cable, with a 6-pin connector on one side, and a 4-pin connector on the other side of the cable.</p>

<p>The 4-pin connector is more common on digital video camcorders and other small external devices because of it's small size, while the 6-pin connector is more common on PC's, external hard drives due to it's durability and support for external power for 1394 peripherals.</p>

<table width="100%"><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image358.gif" alt="IEEE female connectors"><br>6-pin female connector above left<br>4-pin female connector above right
</td><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image360.jpg" alt="IEEE 6-pin male"><br>The 6-pin male<br>connector
</td><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image362.jpg" alt="IEEE 4-pin male"><br>4-pin male<br>connector
</td></tr></table>

<p>HD signals are broadcast in compressed MPEG-2 format at approximately 19 Mbps.  D-VHS VCRs are able record compressed HD signals and require a 1394 connection to receive the digital data stream.  HDTV monitors require a MPEG-2 decoder to decompress the signal for display, as oppose to DVI that is uncompressed.</p>

<p>DTCP (Digital Transmission Content Protection) has been created for the purpose of copy protection over the 1394 connection.  DTCP is also known as 5c for the five companies that participated on the standard (Sony, Toshiba, Intel, Hitachi, and Matsushita).</p>

<p>During the last two to three years, there have been many discussions (and hype) about using these types of digital connections (DVI and 1394) for DTV equipment, rather than only the analog connections (component YPbPr, RGB, RGBHV, etc), for protecting HD digital content.</p>

<p>Since 2003, most manufacturers released a large variety of products adopting these two connections to enable their equipment for digital connectivity, IEEE1394 for compressed HD video from integrated TVs with tuners, cable and OTA HD-STBs mainly for recording purposes, and DVI for uncompressed HD video for the viewing of protected content (using HDCP).</p>

<p>HDMI is quickly replacing DVI and is being implemented already on many products, and is becoming the de-facto standard for transporting uncompressed signals over a cable.</p>

<p><br />
<h2>HDMI</h2><br />
On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba.</p>

<p>The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals on a single cable (15 mm, 19 pin), while using less than half the available bandwidth.  HDMI has the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter.</p>

<p>Not included in the standard but used with DVI and HDMI is the HDCP (High-bandwidth Digital Content Protection) protocol.  HDCP is licensed by Intel, designed to protect HDMI and DVI signals from piracy, and used for authentication between A/V products.  In 2003, a license fee of five cents was applied to each product (four cents for HDMI, 1 cent for HDCP), that manufacturers had to pay to the HDMI founders and Intel.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>April 25, 2006  7:12 AM</b>
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
			<?=getComments(363)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 363)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/digital-connectivity-a-tutorial.php" type="text/javascript" charset="utf-8"></script>
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