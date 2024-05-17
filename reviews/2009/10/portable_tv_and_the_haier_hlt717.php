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
		AND e.entry_id = 3312";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3312 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3312 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3312";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3312";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Portable TV and the Haier HLT717" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Portable TV and the Haier HLT717" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Portable TV and the Haier HLT717" />
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
	<title>HDTV Magazine - Portable TV and the Haier HLT717</title>
	<meta name="keywords" content="dvd audio, mmc card, usb input, dtv reception, dtv tuner, antenna, portable, dtv, cable, reception, haier, input, power, audio, products, dvd, usb, small, channel, card, video, digital, tuner, analog, ntsc" />
	<meta name="description" content="This story actually starts with a DVD Audio player! DVD Audio is a defunct HD audio format from 2001 (along with SACD) that brings the master recording to your home. Unfortunately the DVD forum and mastering houses failed in execution of this new standard making many of the titles auto play for multi-channel only, not stereo; an irritating premise for a 2 channel audiophile minimalist requiring a video monitor to navigate the menus to the stereo tracks. Indeed, DVD Audio listening time was few and far between due to this hassle.

What my DVD Audio world needed was an inexpensive, small LCD display with quick and convenient disconnects..." />
	<meta name="title" content="Portable TV and the Haier HLT717" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Portable TV and the Haier HLT717" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This story actually starts with a DVD Audio player! DVD Audio is a defunct HD audio format from 2001 (along with SACD) that brings the master recording to your home. Unfortunately the DVD forum and mastering houses failed in execution of this new standard making many of the titles auto play for multi-channel only, not stereo; an irritating premise for a 2 channel audiophile minimalist requiring a video monitor to navigate the menus to the stereo tracks. Indeed, DVD Audio listening time was few and far between due to this hassle.

What my DVD Audio world needed was an inexpensive, small LCD display with quick and convenient disconnects..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3312', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php">Portable TV and the Haier HLT717</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>October  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=277&category=HDTV Displays">HDTV Displays</a></b>
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
				<p>This story actually starts with a DVD Audio player! DVD Audio is a defunct HD audio format from 2001 (along with SACD) that brings the master recording to your home. Unfortunately the DVD forum and mastering houses failed in execution of this new standard making many of the titles auto play for multi-channel only, not stereo; an irritating premise for a 2 channel audiophile minimalist requiring a video monitor to navigate the menus to the stereo tracks. Indeed, DVD Audio listening time was few and far between due to this hassle.</p>

<p>What my DVD Audio world needed was an inexpensive, small LCD display with quick and convenient disconnects, but in 2002 that was a tough nut to crack. LCD has come a long way since then so I decided to see what a local brick and mortar store might have to offer in 2009. My natural preference was a display with full HD A/V inputs. The smallest size available was a 15" 720p Dynex over at Best Buy. This tempted me into a custom application in the form of a wall mount and holes in the wall for cabling yet also created a hassle factor of pulling the AC plug and video cable during listening. That led me to the portable TV category.</p>

<p><br />
<strong>General Features of Portable TV</strong></p>

<p>Like so many things related to marketing I can't help but wonder why this category remained portable TV rather than changed to portable DTV. This category label is bound to bite a used electronics purchaser who could unwittingly end up with an NTSC-only product.</p>

<p>There are a number of products available. The common feature set is a DTV tuner, standard RF antenna connector, remote (very small card type), mini 3.5mm jacks for A/V input and headphones, battery pack and AC wall wart power supply/charger with some including a car charging adapter. Typical battery life is 1.5 hours. Most provide a telescoping antenna attached directly to the RF jack with a handful providing a small stick antenna on a magnetic base with an RF cable to the RF jack. Some include a USB and/or card reader slot for PC pictures, music and video. The old analog NTSC standard is supported by many. Some serve double duty as fully featured digital photo frames.</p>

<p>DTV has been riddled with over the air reception problems since inception and is the most glaring problem with these products per customer reviews. Based on the use and expectation of performance of portable TV products of yesteryear, these are bound to disappoint. Our old NTSC analog system was far more robust because it was far more forgiving. Multipath problems and signals buried in noise were still useful especially on little screen sizes, creating nothing but momentary visual blips of noise and even under severe conditions at least you could hear the sound. Analog beats digital hands down as an emergency service for the public. These same problems wreak havoc on digital because blips in the stream of data kill picture and sound and if reception is too poor then you get nothing at all. Like Murphy's Law, these reception blips will happen during a climatic event in your program raising your blood pressure. While much has been done on the receiver end and many local broadcasters are still updating or modifying their transmitter and/or antenna locations, vast improvement can only come with major changes in the system covered by colleague Ed Milbourn in his article <a href="/columns/2009/03/eds_view_hdtv_broadcast_wish_list.php">HDTV Broadcast Wish List</a>. Indeed, the portable TV category has a new competitor; the <a href="/forum/viewtopic.php?t=11488">ATSC Mobile/Handheld</a> standard being developed around cell phone products. A number of portable TV products noted that they are designed for stationary use only, not mobile.</p>

<p>Knowing all this I found it ironic that current portable TV products still use outdated antenna technology in the form of a multi-directional telescoping antenna or the similar stick antenna. This was one product line where I fully expected to see <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=10289">Smart Antenna</a> technology implemented, if not for the benefit of the consumer and their own brand name, then to at least reduce product returns. Bottom line is your portable TV won't seem so portable and convenient if you have to haul a separate and much larger antenna design along with it. Numerous reviews pointed out this need for a better and more directional antenna design and the reception improvements gleaned by providing one.</p>

<p>This DTV reception problem is very disconcerting related to local news and announcements during emergencies. This is not the analog TV experience of yesteryear! If buying this product for that purpose you should test your reception right away. For now and the near future, many of us will get better results with an old fashioned analog AM and FM radio that can run off of batteries for these events.</p>

<p><br />
<strong>Available Products</strong></p>

<p>The following list of portable TV products provides general information only to help get you started. All are 7-inch 16:9 480x243 screens except for one 10-inch as noted. Price ranges from $90-$140 except for that 10-inch with an MSRP of $200. Some USB inputs may be mini and/or require a cable or adapting USB mini/USB cable. Technical details, specifications and a descriptive owner's manual can be difficult to find. I highly recommend you check reviews and manufacturer websites when looking for specific features.</p>

<ul><li>CTA TV-P7 - SD/MS/MMC card reader</li><li>Eviant T7-01</li><li>Envizen EF70701 - USB input, SD/MS/MMC card reader</li><li>Envizen EF71001 10" Digital Photo Frame 800x480 - No A/V input or battery power, USB input, SD/MS/MMC card reader</li><li>Axion AXN-8701</li><li>Viore PLC7V95 - USB input, SD/MS/MMC card reader</li><li>iView 780PTV - USB input, SD/MS/MMC card reader</li><li>Tivax HiRez7 - USB input, SD/MS/MMC card reader, can use standard batteries in a pinch</li><li>Digital Prism 7" LCD TV</li></ul>

<p><br />
<h2>Review: Haier HLT717</h2></p>

<p><strong>Features</strong></p>

<ul><li>7-inch LCD screen, 480x243</li><li>ATSC DTV tuner, Analog Cable tuner, Digital Clear QAM cable tuner</li><li>ATSC DTV Electronic Programming Guide</li><li>RCA composite video and stereo audio connectors</li><li>Manual 4:3 or 16:9 aspect ratio control</li><li>3.5mm headphone jack</li><li>Remote</li><li>Tripod Mount</li><li>Main power switch</li><li>Sleep timer</li><li>Folding kick stand</li><li>Standard RF antenna connector</li><li>Telescopic antenna with snap in storage slot on TV (must be disconnected)</li><li>OEM rechargeable battery pack</li><li>AC wall wart power supply and charger</li></ul>

<p>I eventually came across this portable TV from Haier conveniently down the street at my local Target for $129. It was small enough to sit on one of my shelves, had RCA A/V inputs for composite video and ran on a battery along with a main power switch to completely kill it. Beyond my DVD Audio application it has a DTV tuner, AC power supply/charger, remote, optional antenna, standard RF coax input and headphone jack.</p>

<p>My first amusing test was checking over-the-air (OTA) DTV reception; amusing because my location is awful and the optional antenna for portable use is an omni directional telescoping antenna. I went outside on my deck and as expected the Haier failed to find anything worthy. During the scan it was able to detect the two VHF channels and none of the UHF. Next step was using my Silver Sensor UHF antenna. Unfortunately the positioning mechanism for the telescopic antenna was a tight fellow and I was unable to unscrew it by the hex nut connector alone without a wrench so I did it the way I put it on; grabbed the stiff antenna along with the nut to loosen it. This is not something you easily pop on and off. With the Silver Sensor connected the Haier detected all the DTV stations but only one UHF channel would pass muster making it into memory. Outdoors the remote sensitivity was quite poor requiring it be within about 2 feet of the display to respond reliably. Outside the Haier stayed locked to this one channel but moving the whole mess into the house on the other side of a window, a four foot difference, made reception unstable and sensitive to my physical location nearby. While a Smart Antenna design would probably do little for my location it would be far better than the telescoping omni-directional rod antenna. The product does not offer a signal analysis interface in the menu to help you with a marginal reception problem.</p>

<p>Moving on to the right antenna for my location the Haier performed just as well as my DTV tuner and handled one fringe station better. If you select over the air you are stuck with DTV reception. When selecting cable you engage an old NTSC cable tuner along with a Cable Clear QAM tuner. Channel auto-programming went quite fast compared to other products tested and tuning was also faster than expected when surfing through all the digital QAM channels. Best news is the Haier utilizes two memory slots, one for DTV and one for cable, and you can change from one tuning system to the other by simply changing the reception mode in the menu. This is a great feature if the Haier is going to serve a dual role in your home with cable service and portable DTV outdoors. If you still have some local VHF stations transmitting in NTSC you should be able to pick them up using the cable tuning mode but there is nothing this TV can do with UHF except DTV. Another great feature is the ability to directly tune the transmitting channel number; if received and properly captured it will go into memory automatically. This means you can select that station and move your antenna around to see if you can capture the signal even if it was missed on the auto scan. It is not convenient that the antenna has to be removed and snapped in place for on board storage.</p>

<p>Portable TV is not about video performance. If you are sensitive to lip sync this TV may irritate you as it appears no audio delay was included in the design. Every channel had the problem to the same degree. Overall color balance and factory settings looked fine. At this size the pixel matrix is limited and while finding that spec is like pulling nails it appears all the 7 inch panels revolve around 480x243. You have to hit 10 inches to get an ED, Extended Definition, pixel matrix of 800x480 and that size is very rare in this category. It appeared anything that could earn a 720p rating came at a significantly larger size along with a power cord only such as that 15" Dynex at Best Buy. Nonetheless the limited 7" display had enough legibility for text from DTV or a DVD player. Text font types in DVD menus though could be troublesome at times. The resolution limit of the panel creates artifacts. With HD you can get moire artifacts depending on content. The vertical resolution of 243 lines affects both SD and HD content creating an artifact of fine horizontal lines through out the screen triggered by vertical pans somewhat similar to interlaced analog TV. Angled views had far more affect on black level and light output rather than discoloring the image, a plus.</p>

<p>While I read numerous complaints about tinny sounding audio these users clearly have unreasonable expectations; there isn't much to be done about that at this small size and I found the itty bitty speakers to sound just as I would have expected.</p>

<p><br />
<strong>Haier Conclusion</strong></p>

<p>If you are looking for a small DTV for a small application the Haier just may have you covered due to the cable system capabilities and the RCA input jacks. If on satellite you can pick up your VHF channel 3 or 4 NTSC RF feed by switching to cable mode or grab some RCA cables and use the A/V input.</p>

<p>As a portable TV, keep your expectations realistic and be prepared to try an antenna that provides some margin of directional ability to overcome multi-path. The proper antenna for your location is the key to stable DTV reception. While there are clearly distance limitations with a small antenna you could also be close to the towers yet <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=6914">swamped with multi-path</a> preventing reception. Some larger yet still plausible alternatives are the <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=3378">Silver Sensor</a> or <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=4289">DB2</a>.</p>

<p><br />
<strong>So How About that DVD Audio Player?</strong></p>

<p>The Haier worked great for this application. The RCA A/V inputs created my quick disconnect and universal convenience. The size was just right and the display fit quite nicely between the shelves in an open space in front of the PS3. LCD displays are RF noise makers and as a minimalist audiophile the main power switch allowed me to leave the video cable attached while completely killing operation of the product right down to the standby power supply and micro awaiting a power command to turn on. Font legibility was on the edge but good enough to navigate the menus.</p>

<p>I would have loved to try out that 10 inch Envizen Digital Photo Frame due to the ED resolution but it had no A/V input and required an AC power cord...</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>October  8, 2009  9:39 AM</b>
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
			<?=getComments(3312)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 3312)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/10/portable-tv-and-the-haier-hlt717.php" type="text/javascript" charset="utf-8"></script>
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