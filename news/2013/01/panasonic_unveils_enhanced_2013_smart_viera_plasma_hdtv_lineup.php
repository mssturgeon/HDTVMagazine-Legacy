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
		AND e.entry_id = 4987";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4987 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4987 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4987";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-enhanced-2013-smart-viera-plasma-hdtv-lineup.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4987";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup" />
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
	<title>HDTV Magazine - Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup</title>
	<meta name="keywords" content="inch class, picture quality, series includes, smart viera, viera plasma, series, viera, panasonic, inch, class, screen, panel, plasma, picture, models, quality, new, smart, design, features, home, built, touch, full, glass" />
	<meta name="description" content="Panasonic, an industry and technology leader in High Definition and Smart TV technology, introduces its 2013 line of Smart VIERA&amp;reg; Plasma HDTVs.  Continuing its tradition of developing award winning Plasma HDTVs, Panasonic has introduced an enhanced 2013 lineup built around user personalization and advanced networking features, increased connectivity, re-mastered picture quality, and stunning design elements.  Ranging from 42-inch class to 65-inch class, the 16 new models provide the highest quality picture and breadth of viewing options directly to the consumer.

Building on its reputation for creating innovative products, Panasonic's 2013 VIERA televisions have become more than a screen, now functioning as an extension of all lifestyle and mobile devices.

The 2013 Smart VIERA Plasma line-up features..." />
	<meta name="title" content="Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-enhanced-2013-smart-viera-plasma-hdtv-lineup.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic, an industry and technology leader in High Definition and Smart TV technology, introduces its 2013 line of Smart VIERA&amp;reg; Plasma HDTVs.  Continuing its tradition of developing award winning Plasma HDTVs, Panasonic has introduced an enhanced 2013 lineup built around user personalization and advanced networking features, increased connectivity, re-mastered picture quality, and stunning design elements.  Ranging from 42-inch class to 65-inch class, the 16 new models provide the highest quality picture and breadth of viewing options directly to the consumer.

Building on its reputation for creating innovative products, Panasonic's 2013 VIERA televisions have become more than a screen, now functioning as an extension of all lifestyle and mobile devices.

The 2013 Smart VIERA Plasma line-up features..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4987', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-enhanced-2013-smart-viera-plasma-hdtv-lineup.php">Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 10, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=504&category=Plasma HDTVs">Plasma HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">Panasonic Unveils Enhanced 2013 Smart VIERA Plasma HDTV Lineup</p>

<center><i>2013 Models Deliver Increased Connectivity and Personalization Options, Expanded VIERA Connect&trade;, Superior Full HD 3D, "Beyond the Reference" Picture Quality and Pristine Design</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 7, 2013 /PRNewswire/</strong> -- Panasonic, an industry and technology leader in High Definition and Smart TV technology, introduces its 2013 line of Smart VIERA&reg; Plasma HDTVs.  Continuing its tradition of developing award winning Plasma HDTVs, Panasonic has introduced an enhanced 2013 lineup built around user personalization and advanced networking features, increased connectivity, re-mastered picture quality, and stunning design elements.  Ranging from 42-inch class to 65-inch class, the 16 new models provide the highest quality picture and breadth of viewing options directly to the consumer.</p>

<p>Building on its reputation for creating innovative products, Panasonic's 2013 VIERA televisions have become more than a screen, now functioning as an extension of all lifestyle and mobile devices.</p>

<p>The 2013 Smart VIERA Plasma line-up features:</p>

<p><strong>My Home Screen</strong> – a personalization function that allows each user in the home to create their own personal home screen giving them quick access to their favorite content. (2013 VIERA ZT60, VT60, and ST60 Series)</p>

<p><strong>Swipe &amp; Share 2.0</strong> – a  connectivity enhancement that transforms the TV into a hub for streaming and sharing photo and video content seamlessly with Smartphone and Tablet devices.  Through Panasonic's proprietary VIERA ConnectTM platform, users can transfer personal photos and videos from their Android or iOS devices directly to the large screen with a simple swipe of the finger and transfer them back to their smart devices the same way.  Swipe &amp; Share also enables sharing of user-generated photos and videos that are on the large screen with other Android or iOS devices. (2013 VIERA&reg; ZT60, VT60, and ST60 Series)</p>

<p><strong>Touch Pen</strong> – a design feature which allows users to add their own writing to their photos on screen and transfer them back to their smart devices using the optional Electronic Touch Pen (TY-TP10U) accessory. (2013 VIERA ZT60, VT60, ST60 , and S60 Series)</p>

<p><strong>Voice Guidance</strong> – an accessibility function that uses text to speech functions to verbalize text content as it appears on your TV.  (2013 VIERA ZT60 and VT60)</p>

<p><strong>Voice Interaction</strong> -- by simply saying a key word into the Smart VIERA Touch Pad Controller or Smartphone (VIERA Remote 2 App must be installed), the search result is displayed on the VIERA HDTV screen and also verbally read out. (2013 VIERA ZT60 and VT60 Series)</p>

<p>The Smart VIERA ZT60, VT60, ST60 Series models also include a web browser  with built-in Wireless LAN and the VT60 will also feature Panasonic's first-ever built-in camera  to enhance the home entertainment experience.  In addition, each new Smart VIERA HDTV offers an enhanced VIERA ConnectTM platform including more options for streaming video content, unique social networking features, fully-integrated apps, and intuitive search features designed to make the user experience fast and easy.  </p>

<p>"Panasonic continually strives to surpass the previous year's successes, and the new 2013 series of VIERA Plasma HDTVs does just that," said Henry Hauser, Vice President, Merchandising Group, Panasonic Consumer Marketing Company of North America.  "The series' enhanced VIERA Connect IPTV functionality, superior picture and sound quality, and advanced connectivity capabilities have completely transformed the TV viewing experience.  With the 16 new models, Panasonic has set the bar for years to come."</p>

<p>The new VIERA&reg; ZT60 Plasma Series is equipped with a master studio panel, producing deep black levels for stunning picture quality. The new design maximizes the natural properties of glass and metal to visually express the superb performance of display and color.  With the addition of a new Louver filter, the ZT Series line delivers improved external light shading, clarity and light transmittance.  The new panel reduces reflections to create sharper pictures with higher contrast in bright environments.</p>

<p>Building on last year's "Glass and Metal" design concept, the 2013 VIERA models continue to focus on sleek designs while delivering pristine picture display and superior sound quality. The VT60 Series includes built-in side speakers, and a thin metal pedestal with one sheet of glass featured on the ZT and VT Series models delivers a minimalist aesthetics and design. The ZT60 and VT60 Series models are also equipped with a Dual Core with Hexa Processing Engine.</p>

<p><br />
<strong>ZT60 Series</strong></p>

<p>The ZT60 series is Panasonic's Full HD 3D Plasma flagship series and is available in two screen sizes - the TC-P65ZT60, 65-inch class and the TC-P60ZT60, 60-inch class.</p>

<p>With 1080p resolution, the ZT60 series comes with advanced features for connectivity including My Home Screen, Swipe and Share 2.0, Touch Pad Controller, three HDMI and three USB connectors, along with VIERA ConnectTM, allowing consumers to connect to applications through the internet via built in Wireless LAN and Web Browser.  2D-3D conversion transforms 2D content to 3D images in real-time.  The ZT Series also includes two pairs of Active Shutter 3D Eyewear.</p>

<p>The VIERA ZT60 Series offers a "Beyond the Reference" level of picture quality. Ultimate black color, contrast and crystal crisp picture are achieved thanks to the new Studio Master Panel with newly developed panel driving method and panel structure with direct glass layer. The front glass is directly attached to the Plasma panel itself by Panasonic's exclusive crafting methods. As a result of the improvement, dim and double images are cut off by eliminating the air gap layer between the front glass and Plasma panel. The ZT60 series enhances compatibility between ultimate picture quality and stylish design. The quality of the ZT60's 3D images is greatly enhanced, with clear, natural Full HD 3D images because of reduced crosstalk. 3000 Focused-field Drive also produces image resolution of 1080 lines for 2D images, to provide crisp, clear image motion. A wider color gamut (DCI 98% Color Space) is achieved thanks to a newly developed pure red phosphor.  The ZT60 features THX in both 2D and 3D modes *1 as well as ISFccc Calibration Mode with Advanced Calibration. Calibrators adjust the detailed picture setting with calibration software (CALMAN&trade; ) provided by SpectraCal Inc.</p>

<p>In addition to picture quality, operation and usability of the VIERA&reg; ZT60 Series is also refined  with Voice control and easy web search are achieved based on voice recognition technology in combination with new touch pad remote controller with microphone. Also, text to speech function offers voice guidance for convenient usage of the web browser. In addition, the painting and retouching function based on Panasonic's newly developed Touch Pen technology offers a fun and new experience for using your TV. (Touch pen accessory is optional) </p>

<p>The ZT60 Series offers advanced network functions via VIERA ConnectTM -- Panasonic's exclusive cloud-based IPTV function.  Multi-tasking allows simultaneous use of apps from seven categories, including YouTube, Skype and Facebook. Web browser capability makes it easy to use the Internet from the TV. DLNA, Wireless LAN and Bluetooth are also supported for a high level of convenience.</p>

<p>The ZT60 Series display panel is free of mercury and lead. A newly designed phosphor process and rear panel process give the Plasma panel longevity of up to 100,000 hours before the brightness of the panel decreases to half. This is more than 30 years of viewing when watched 8 hours a day.  The stylish design of the ZT60 Series is based on the concept of "one sheet of glass", which accentuates the superiority of the panel's picture quality and the unique pedestal structure with a thin metal plate gives the TV an elegant glass and metal aesthetics.</p>

<p><br />
<strong>VT60 Series</strong></p>

<p>The VT60 series includes three screen sizes – the TC-P55VT60, 55-inch class; TC-P60VT60, 60-inch class; and TC-P65VT60, 65-inch class.</p>

<p>The VT60 Series achieves the ultimate in advanced design combining built-in side speakers and a built-in camera with a Full HD 3D display delivering 1080p resolution via an Infinite Black Ultra Panel. Similar to the ZT60 series, the VT60 models are equipped with enhanced connectivity features including VIERA ConnectTM, My Home Screen, built-in Wireless LAN and Web Browser, Swipe and Share 2.0.  The VT60 Series also includes a Touch Pad Controller and voice interaction with three HDMI and three USB connectors. The VT60 Series also includes two pairs of Active Shutter 3D Eyewear.  3000 Focused-field Drive technology produces image resolution of 1080 lines for 2D images, to provide crisp, clear image motion. The VT60 also features THX in both 2D and 3D modes *1 as well as ISFccc Calibration Mode with Advanced Calibration. Calibrators adjust the detailed picture setting with the calibration software (CALMAN&trade; )provided by SpectraCal Inc.</p>

<p><br />
<strong>ST60 Series</strong></p>

<p>The TC-P50ST60, 50-inch class; TC-P55ST60, 55-inch class; TC-P60ST60, 60-inch class and TC-P65ST60, 65 inch class comprise the ST60 series of Full HD 3D VIERA&reg; HDTVs.</p>

<p>The ST60 models include Infinite Black Pro Panel with 2500 Focused Field Drive and 1080p Full HD resolution for pristine picture quality.  The ST60 Series features VIERA Connect, My Home Screen, a Web Browser, Swipe and Share 2.0, and voice guidance.  The series includes three HDMI connections, two USB ports and two pairs of Active Shutter 3D Eyewear.</p>

<p><br />
<strong>S60 Series</strong></p>

<p>The S60 Series is one of two non-3D models in this year's HDTV Plasma lineup.</p>

<p>There are five screen sizes in the S60 Series – the TC-P42S60, 42-inch class; TC-P50S60, 50-inch class; TC-P55S60, 55-inch class; TC-P60S60, 60-inch class; and TC-P65S60, 65-inch class.</p>

<p>The S60 Series includes an Online Movies feature -- a service that provides select IPTV functionality by offering access to popular applications for streaming movies and other videos.  All S60 models offer 1080p Full HD resolution, built-in Wireless LAN, 600Hz Sub-field Drive, Media Player; DLNA, VIERA&reg; Link, two HDMI connections and two USB ports.</p>

<p><br />
<strong>X60 Series</strong></p>

<p>The X60 Series is one of two non-3D models in this year's HDTV Plasma lineup.</p>

<p>There are two screen sizes in the X60 Series -- the TC-P42X60, 42-inch class and TC-P50X60, 50-inch class.  The X60 offers 720p HD resolution; Media Player; 600Hz Sub-Field Drive; Game Mode; two HDMI connections and one USB port.</p>

<p>Panasonic's line of 2013 VIERA Plasma HDTVs will be available starting in February 2013.  They will be on display at the Panasonic booth #9406 at the 2013 International CES at the Las Vegas Convention Center from January 8, 2013 through January 11, 2013.  For more information including technical specifications please visit <a target="_blank" href="http://www.Panasonic.com/">www.Panasonic.com</a>.</p>

<p><br />
<strong>About Panasonic Consumer Marketing Company of North America</strong></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Marketing Company of North America, a Division of Panasonic Corporation of North America, the principal North American Subsidiary of Panasonic Corporation (NYSE: PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations, offers a wide-range of consumer solutions in the U.S. and Canada. The Company's portfolio of innovative consumer products ranges from VIERA Full HD 3D Televisions, Blu-ray players, LUMIX Digital Cameras, Camcorders, Home Audio, Cordless Phones, Home Appliances, Wellness and Personal Care products and more.</p>

<p>Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. In the 2012 Interbrand Annual Best Global Green Brands ranking, the Panasonic brand jumped four spots to number six: http://www.interbrand.com/en/best-global-brands/Best-Global-Green-Brands/2012-Report.aspx. Follow Panasonic on Twitter @PanasonicUSA, and additional company information for media is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>Notes</p>

<p>*1 - Tentative</p>

<p>SOURCE Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 10, 2013  4:33 PM</b>
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
			<?=getComments(4987)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4987)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2013/01/panasonic-unveils-enhanced-2013-smart-viera-plasma-hdtv-lineup.php" type="text/javascript" charset="utf-8"></script>
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