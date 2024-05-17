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
		AND e.entry_id = 610";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 610 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 610 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 610";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/06/sony-adds-nine-models-to-braviar-flatpanel-lcd-line.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 610";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line" />
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
	<title>HDTV Magazine - Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line</title>
	<meta name="keywords" content="kdl xbr, inch kdl, internet video, high definition, flat panel, sony, xbr, kdl, models, video, bravia, new, feature, high, inch, color, internet, content, digital, technology, series, definition, lcd, panel, including" />
	<meta name="description" content="Sony today introduced nine new BRAVIA(R) flat-panel LCD high-definition televisions with advanced features.

The new models all feature 1920 x 1080 full high-definition resolution, 10-bit panels and, in select models, Motionflow 120Hz high frame rate technology, as well as x.v.Color(TM) capability. Encompassing the XBR5, XBR4 and W series, they come in screen sizes of 52, 46 and 40 inches (measured diagonally). Including the previously announced S series and V series flat- panel LCD models, the complete line of 17 models range in size from 70 to 26 inches (measured diagonally.)" />
	<meta name="title" content="Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/06/sony-adds-nine-models-to-braviar-flatpanel-lcd-line.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sony today introduced nine new BRAVIA(R) flat-panel LCD high-definition televisions with advanced features.

The new models all feature 1920 x 1080 full high-definition resolution, 10-bit panels and, in select models, Motionflow 120Hz high frame rate technology, as well as x.v.Color(TM) capability. Encompassing the XBR5, XBR4 and W series, they come in screen sizes of 52, 46 and 40 inches (measured diagonally). Including the previously announced S series and V series flat- panel LCD models, the complete line of 17 models range in size from 70 to 26 inches (measured diagonally.)" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=610', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/06/sony-adds-nine-models-to-braviar-flatpanel-lcd-line.php">Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June  6, 2007</b>
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
				<p class="prtitle">Sony Adds Nine Models to BRAVIA(R) Flat-Panel LCD Line</p>

<center><i>Models Compatible With BRAVIA Internet Video Link and Feature XMB Menu System</center></i><br />
<br />
<img src="/images/products/sony-bravia-lcd.jpg" align="left" /><B>NEW YORK, June 6 /PRNewswire/</B> -- Sony today introduced nine new BRAVIA(R) flat-panel LCD high-definition televisions with advanced features.

<p>The new models all feature 1920 x 1080 full high-definition resolution, 10-bit panels and, in select models, Motionflow 120Hz high frame rate technology, as well as x.v.Color(TM) capability. Encompassing the XBR5, XBR4 and W series, they come in screen sizes of 52, 46 and 40 inches (measured diagonally). Including the previously announced S series and V series flat- panel LCD models, the complete line of 17 models range in size from 70 to 26 inches (measured diagonally.)</p>

<p>"Our BRAVIA flat-panel LCD HDTVs have the leading market share because they deliver an outstanding level of picture quality and style that people appreciate," said Randy Waynick, senior vice president of Sony's Home Products Division. "The new line elevates our commitment to full HD1080p televisions displays, while offering many more choices."</p>

<p>All of the new models in the line feature Sony's Digital Media Extender (DMeX), offering a digital connection for the BRAVIA Internet Video Link module (sold separately), which allows users to view select Internet video, including high-definition content, from the comfort of their living room from providers like AOL, Yahoo! and Grouper, as well as Sony Pictures Entertainment and Sony BMG Music.</p>

<p>The module mounts on the back of a compatible Sony television and connects directly to the Internet via an existing broadband Ethernet connection (3 Mbps or higher) without the use of a personal computer. The feature will give users access to select Internet video, music videos, movie trailers, user generated videos and RSS feeds without additional charges.</p>

<p>Sony's Emmy(R) Award-winning Xross Media Bar(TM) (XMB) interface provides seamless access to various Internet video channels, as well as traditional broadcast, cable and satellite offerings, in addition to any user-generated content. The XMB incorporates an advanced but simple to use HD graphic user interface to maneuver through the menu systems easily and quickly.</p>

<p>Also simplifying operation is Sony's BRAVIA Theatre Sync(TM) feature with its one-button command, which integrates the operation of the television with supporting external components connected via an HDMI(TM) input (based on industry standard HDMI-CEC). Through a simple one-button click of the remote, users can easily enjoy viewing a Blu-ray Disc(TM) movie, listening to surround sound audio via an AV receiver, and controlling other components, all over just one single cable connection avoiding the hassle of powering on individual components, changing AV receiver audio input, switching TV video inputs, etc. (HDMI cable sold separately.)</p>

<p>Developed in consultation with the Sony Pictures Entertainment movie studio, the new BRAVIA HDTVs feature Theater Mode that adjusts the TV to display movies, better preserving the mood and detail that the filmmaker intended. When the Theater button on the television's remote control is selected, the TV automatically adjusts settings to one that has been specifically optimized for BRAVIA LCD and SXRD display technology. Sony's Theater Mode is the result of consultation with the people who bring Blu-ray disc and DVD for home viewing to reproduce an exceptional cinematic experience of the movie theater in your living room.</p>

<p>BRAVIA TVs are not just optimized for movies, however. Increasingly, people want to view photos from their compatible digital cameras on their large-screen HDTV sets, expanding beyond just viewing them on computer monitors. The new Photo TV HD mode brings the look of actual printed photography to the set reproducing high quality digital photos by fine-tuning parameters, including sharpness, gradation and color.</p>

<p><br />
<B>W3000 Series</B></p>

<p>Featuring an elegant brushed metal picture frame bezel, the new W-series includes the 52-inch KDL-52W3000, 46-inch KDL-46W3000 and 40-inch KDL-40W3000. The full HD 1080p models feature Live Color Creation(TM) technology with WCG- CCFL backlight and 10-bit processing with a 10-bit panel, which has the capability to deliver 64 times the level of color expression than 8-bit panels. The result is a smoother transition between colors and more natural, accurate reproduction of subtle color changes.</p>

<p>Enhancing image quality further is the adoption of the new industry color standard for video, xvYCC, also referred to as x.v.Color(TM) technology. This standard expands the potential color data range of video by about 1.8 times resulting in the display of more natural and vivid colors similar to what the human eye can actually see with supporting video sources. This technology is a perfect complement to Sony's HD camcorder models, which capture color range beyond what broadcasters currently deliver.</p>

<p>Unfortunately, not all today's video content sources match the razor sharp resolution and rich colors available on a Blu-ray disc or a high-definition broadcast. Helping to enhance content that is not perfect, Sony's BRAVIA Engine(TM) EX full digital video processing system with Digital Reality Creation-Multifunction v1.0 (DRC-MF v1.0) technology delivers an exceptional picture by up converting common standard definition signals like DVDs and non- HD broadcasts to better match the television's capabilities.</p>

<p>When high-definition signals are available, however, the sets feature 1080p input capability via HDMI, component, and PC inputs (with supporting PC graphics cards) for an outstanding picture. Additionally, the HD component and HDMI inputs are compatible with both 1080/60p and 1080/24p sources (24p True Cinema).</p>

<p><br />
<B>XBR4 and XBR5 Series</B></p>

<p>Sony's new 52-inch KDL-52XBR4, 46-inch KDL-46XBR4 and 40-inch KDL-40XBR4 feature an elegant floating glass frame design with the ability to swap the standard black bezel out for any of eight other optional colors including Scarlet Red, Pacific Blue, Arctic White, Sienna Brown, Titanium Silver, Midnight Black and new for 2007, Rose Metallic and Champaign Gold. The 52- inch KDL-52XBR5, 46-inch KDL-46XBR5 and 40-inch KDL-40XBR5 televisions also feature the floating glass design with an elegant piano black-finished bezel (non interchangeable.)</p>

<p>Both XBR model lines offer Sony's Motionflow 120Hz high frame rate. The Motionflow technology creates 60 unique frames between each of the existing 60 frames, doubling the frames displayed per second in real-time, further improving images for fast action sports and other programming. Motionflow 120Hz high frame rate effectively eliminates motion artifacts ("judder") while watching content filmed at 24 frames-per-second. This means with all of your existing DVDs or broadcast movies and prime-time TV series, you can enjoy all the detail even with moving objects.</p>

<p>The XBR models also add BRAVIA Engine Pro circuitry with Digital Reality Creation-MultiFunction v2.5 which upconverts non-1080p signals, including 720p and 1080i.</p>

<p>The KDL-52XBR4, KDL-46XBR4 and KDL40XBR4 models will be available in August for about $4,800, $3,800 and $3,000, respectively. Also shipping in August, the KDL-46XBR5 and KDL-40XBR5 will be about $4,100 and $3,300. The KDL-52XBR5 model will come out in September for about $5,100. Sony's KLD- 46W3000 and KDL-40W300 models will debut in July for about $3,500 and $2,700, respectively, while the KDL-52W3000 will be available in August for about $4,300.</p>

<p>All models will be offered direct at sonystyle.com and at Sony Style stores nationwide, as well military base exchanges and authorized Sony retailers throughout the country.</p>

<p>NOTE: News releases and digital images with captions are available at<br />
http://www.sony.com/news. For information regarding the nearest Sony<br />
authorized dealer or service location, your readers can call 1-800-222-SONY.<br />
BRAVIA Internet Video Link image quality and picture size will vary and is<br />
dependent upon broadband speed and delivery by content providers. Premium<br />
Internet video content may require additional fees. High-definition content<br />
requires at least a 10 Mbps connection speed.<br />
Photo: http://www.newscom.com/cgi-bin/prnh/20070606/LAW145</p>

<p>Source: Sony Electronics, Inc.</p>

<p>CONTACT: Greg Belloni of Sony Electronics, Inc., +1-858-942-4460, or<br />
greg.belloni@am.sony.com; or Tania Scheer of PainePR, +1-212-613-4918, or<br />
tscheer@painepr.com</p>

<p>Web site: http://www.sony.com/news</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June  6, 2007  3:17 PM</b>
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
			<?=getComments(610)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 610)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/06/sony-adds-nine-models-to-braviar-flatpanel-lcd-line.php" type="text/javascript" charset="utf-8"></script>
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