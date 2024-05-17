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
		AND e.entry_id = 4615";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4615 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4615 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4615";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/sharp-unveils-new-lineup-of-multifunctional-bluray-disc-players-and-sound-systems.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4615";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp&reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&trade; Players and Sound Systems" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp&reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&trade; Players and Sound Systems" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sharp&reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&trade; Players and Sound Systems" />
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
	<title>HDTV Magazine - Sharp&reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&trade; Players and Sound Systems</title>
	<meta name="keywords" content="blu ray, sharp electronics, ray disc, electronics corporation, disc players, sharp, new, blu, ray, system, msrp, availability, audio, ipod, wma, sound, trademarks, disc, corporation, connection, features, line, systems, vertical, docking" />
	<meta name="description" content="Sharp Electronics Corporation today unveiled two new multi-function Blu-ray Disc players as well as a line of sound bars and audio systems. These new products will be available in spring of 2012. Distinguishing highlights include the evolution of Sharp's Blu-ray players to multi-functional devices for entertainment content management and new sound bar and desktop solutions for audio enthusiasts and novices alike.

As the TV has become the centerpiece to most entertainment centers, aggregating content from various sources is a key interest of consumers. Sharp will showcase two new Blu-ray Disc players, the BD-AMS10U and BD-AMS20U. Both models incorporate..." />
	<meta name="title" content="Sharp&amp;reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&amp;trade; Players and Sound Systems" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sharp&amp;reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&amp;trade; Players and Sound Systems" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/sharp-unveils-new-lineup-of-multifunctional-bluray-disc-players-and-sound-systems.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sharp Electronics Corporation today unveiled two new multi-function Blu-ray Disc players as well as a line of sound bars and audio systems. These new products will be available in spring of 2012. Distinguishing highlights include the evolution of Sharp's Blu-ray players to multi-functional devices for entertainment content management and new sound bar and desktop solutions for audio enthusiasts and novices alike.

As the TV has become the centerpiece to most entertainment centers, aggregating content from various sources is a key interest of consumers. Sharp will showcase two new Blu-ray Disc players, the BD-AMS10U and BD-AMS20U. Both models incorporate..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4615', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/sharp-unveils-new-lineup-of-multifunctional-bluray-disc-players-and-sound-systems.php">Sharp&reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&trade; Players and Sound Systems</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Sharp&reg; Unveils New Line-Up of Multi-Functional Blu-ray Disc&trade; Players and Sound Systems</p>

<center><i>New Products Incorporate New Technologies and Design Enhancements</center></i><br />
<br />

<p>2012 International CES<br />
BOOTH #10916</p>

<p><strong>LAS VEGAS--(BUSINESS WIRE)--</strong>Sharp Electronics Corporation today unveiled two new multi-function Blu-ray Disc players as well as a line of sound bars and audio systems. These new products will be available in spring of 2012. Distinguishing highlights include the evolution of Sharp's Blu-ray players to multi-functional devices for entertainment content management and new sound bar and desktop solutions for audio enthusiasts and novices alike.</p>

<p>"Consumers are looking to seamlessly integrate all their entertainment devices for the ultimate in ease and performance," said Jim Sanduski, vice president, strategic product marketing, Sharp Electronics Marketing Company of America, a division of Sharp Electronics Corporation. "Our new lineup of Blu-ray and audio products perfectly complement our large screen televisions with compelling features and performance."</p>

<p><br />
<strong>More than Just a Blu-ray Player</strong></p>

<p>As the TV has become the centerpiece to most entertainment centers, aggregating content from various sources is a key interest of consumers. Sharp will showcase two new Blu-ray Disc players, the BD-AMS10U and BD-AMS20U. Both models incorporate features from the new SmartCentral interface; the "super picture" setting which improves image resolution by integrating pixel information from lower resolution video from a DVD source or streaming content and restores missing image information while enhancing image details; DLNA&reg; interface; and AQUOS&reg; Pure mode which creates an optimized color pallet for AQUOS TVs.</p>

<p>BD-AMS10U: Includes a USB interface for external hard drives for storage and playback and easy integration with other media sources such as digital cameras. This model is wireless LAN ready for streaming content including Netflix&reg;, VUDU&trade; and YouTube&trade;.</p>

<p><strong>Availability</strong>: March<br />
<strong>MSRP</strong>: $179.99</p>

<p>BD-AMS20U: Features a wireless LAN to easily connect with the web and access online content, USB interface for external hard drive ports, and a unique smart phone interface via Mobile High-definition Link&trade; (MHL). Via MHL, Android&trade; users can access and engage their mobile apps and content (i.e. music and videos) via their home entertainment system. While connected with the system, the mobile device also receives power and is charged.</p>

<p><strong>Availability</strong>: April<br />
<strong>MSRP</strong>: $199.99</p>

<p><br />
<strong>Added Flexibility to Sound Bar Family</strong></p>

<p>The new sound bar products for 2012 build on years of success from their predecessors. These new models are the ideal audio companions to a home entertainment system, offering flexibility for any horizontal, vertical, or wall mounted set-up while providing exceptional sound at varying price ranges with a sleek and unobtrusive look.</p>

<p>HT-SL75: 2.1 system with a 1" (h) speaker bar that matches the AQUOS TV line 60" – 70" Horizontal / Vertical. It also includes an auto on/circuit, HDMI&reg; input 1.4a 3D Pass Thru and an active RMS 100W subwoofer for optimal sound. Wall mounting hardware, vertical stands and a HDMI cable are packaged with the unit.</p>

<p><strong>Availability</strong>: Current<br />
<strong>MSRP</strong>: $329.99</p>

<p>HT-SL77: 2.1 system with a 1" (h) center speaker for a low profile design that is adjustable to match the AQUOS TV line 46"/52"/60"/70" Horizontal and 60" – 70" Vertical. It also incorporates a wireless subwoofer that can be setup in any location, within a 25' range. Wall mounting hardware, vertical stands, and a HDMI cable are supplied with the model.</p>

<p><strong>Availability</strong>: April<br />
<strong>MSRP</strong>: $449.99</p>

<p><strong>Executive / Micro / Mini Shelf Systems</strong></p>

<p>Sharp rounds-out its new audio products with a number of expertly designed product lines highlighting the convergence of functionality, like new docking compatibility with iPhone&reg; and iPad&reg; devices, and form, with sleek lines and a small footprint.</p>

<p><strong>XL-HF201P / XL-HF301P / XL-HF401P – Executive Audio Systems</strong> (key features):<br />
<ul><li>Micro Receiver style with RMS 100W total (50 watts Per Channel)</li><li>iPhone &amp; iPod&reg; Dock (top docking)</li><li>iPad USB connection /playback</li><li>Portable player connection via 3.5mm audio input</li><li>CD Player with MP3 and WMA compatibility</li><li>Two way speakers with dome tweeters and removable grilles</li><li>Pro type speaker connection with wire / post connections</li><li>Volume auto fade-in</li><li>Upgraded speaker drivers w/dome tweeter (301P model only)</li><li>Airplay&trade; music streaming and network connection iPad stand (401P model only)</li></ul></p>

<p><strong>Availability</strong>: XL-HF201P May<br />
<strong>MSRP</strong>: $249.99</p>

<p><strong>Availability</strong>: XL-HF301P June<br />
<strong>MSRP</strong>: $329.99</p>

<p><strong>Availability</strong>: XL-HF401P August<br />
<strong>MSRP</strong>: $399.99</p>

<p><strong>DK-KP80P / DK-KP95P – Slim Micro Audio Systems</strong> (key features):<br />
<ul><li>Slim Micro System with RMS 50W total power 25W per channel</li><li>iPad stand supplied</li><li>iPhone &amp; iPod Docking (top docking), iPad connection via USB</li><li>Powered open/close vertical CD Door</li><li>USB connection /playback</li><li>Portable player connection via 3.5mm audio input</li><li>Vertical CD player with MP3 and WMA compatibility</li><li>Text navigation for MP3 and WMA playback</li><li>Headphone output jack</li><li>Airplay / DLNA WiFi streaming for MP3, WMA playback (DK-KP95P model only)</li></ul></p>

<p><strong>Availability</strong>: Current<br />
<strong>MSRP</strong>: $199.99 (DK-KP80P) $329.99 (DK-KP95P)</p>

<p><strong>CD-DH950P – Mini Component System</strong> (key features):<br />
<ul><li>iPod / iPhone docking slot with menu control and charging</li><li>CD-R/RW, MP3, WMA playable formats</li><li>Two-line text navigation for MP3 and WMA playback</li><li>Five-disc multi-play CD changer</li><li>RMS 240W total output</li><li>Full logic cassette deck</li><li>Two-way speaker system</li><li>AM/FM tuner with 40 Presets</li><li>Full function remote</li><li>Bass / treble settings</li><li>X Bass (Extra Bass System)</li><li>Built in clock/sleep timer</li></ul></p>

<p><strong>Availability</strong>: Current<br />
<strong>MSRP</strong>: $249.99</p>

<p><strong>CD-DHS1050P – Mini Component System</strong> (key features)<br />
<ul><li>iPod / iPhone docking slot with menu control and charging</li><li>Separate subwoofer (150W)</li><li>CD-R/RW, MP3, WMA playable formats</li><li>Two-line text navigation for MP3 and WMA playback.</li><li>Five-disc multi-play CD changer</li><li>RMS 350W total output</li><li>Full logic cassette deck</li><li>Two-way speaker system</li><li>AM/FM tuner with 40 Presets</li><li>Full function remote</li><li>Bass / treble settings</li><li>X Bass (Extra Bass System)</li><li>Built in clock/sleep timer</li></ul></p>

<p><strong>Availability</strong>: Current<br />
<strong>MSRP</strong>: $269.99</p>

<p>For more information on what Sharp announced at CES, please visit <a target="_blank" href="http://www.SharpUSANews.com/">www.SharpUSANews.com</a>. For more information visit Sharp Electronics Corporation at <a target="_blank" href="http://www.sharpusa.com/">www.sharpusa.com</a>. Find us on Facebook, follow us on Twitter and watch us on YouTube.</p>

<p><br />
<strong>About Sharp Electronics Corporation:</strong></p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions, LED lighting and mobile communication and information tools. Leading brands include AQUOS&reg; Quattron&trade; LCD televisions and 3DTVs, SharpVision&reg; projectors, Insight&reg; Microwave Drawer&reg; ovens, Notevision&reg; multimedia projectors and Plasmacluster&reg; air purifiers.</p>

<p>Sharp, and all related trademarks are trademarks or registered trademarks of Sharp Corporation and/or its affiliated entities.<br />
Blu-Ray Disc&trade;, Blu-ray&trade; and Blu-ray 3D&trade; are the logos and trademarks of the Blu-Ray Disc Association.<br />
Facebook is a registered trademark of Facebook Inc.<br />
Airplay, iPod, iPod classic, iPod nano, iPod touch, iPhone and iPad are trademarks of Apple Inc., registered in the U.S. and other countries.<br />
Mobile High Definition Link is a trademark of MHL, LLC.<br />
The Twitter name is a trademark of Twitter Inc.<br />
Android and YouTube are trademarks of Google.<br />
Netflix is a registered trademark of Netflix, Inc.<br />
VUDU is a trademark of VUDU, Inc.<br />
HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing LLC in the United State and other countries.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2012  9:52 PM</b>
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
			<?=getComments(4615)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4615)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/sharp-unveils-new-lineup-of-multifunctional-bluray-disc-players-and-sound-systems.php" type="text/javascript" charset="utf-8"></script>
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