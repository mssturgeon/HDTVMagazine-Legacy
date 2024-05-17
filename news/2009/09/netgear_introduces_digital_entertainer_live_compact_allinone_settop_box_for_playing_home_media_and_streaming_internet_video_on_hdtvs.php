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
		AND e.entry_id = 3241";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3241 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3241 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3241";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3241";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" />
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
	<title>HDTV Magazine - NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</title>
	<meta name="keywords" content="digital entertainer, entertainer live, digital media, internet video, netgear inc, digital, netgear, internet, live, entertainer, home, videos, video, media, network, products, movies, product, content, usb, storage, service, consumers, may, devices" />
	<meta name="description" content="NETGEAR launched the newest Internet-connected set-top box in its popular &quot;Digital Entertainer&quot; product family. The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.

In addition to personal media collections and YouTube, consumers can now..." />
	<meta name="title" content="NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="NETGEAR launched the newest Internet-connected set-top box in its popular &quot;Digital Entertainer&quot; product family. The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.

In addition to personal media collections and YouTube, consumers can now..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3241', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php">NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>
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
				<p class="prtitle">NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</p>

<center><i>Plays Movies, Videos, Music and Photos from USB Drives, Computers and Network Attached Storage; Accesses YouTube, Roxio CinemaNow On-demand Movies, Internet Videos, Hulu, Netflix and More</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/2009-09-08_netgear_digital_entertainer_live.jpg" alt="Netgear Digital Entertainer Live" class="keyimg" /><strong>SAN JOSE, Calif., Sept. 8 /PRNewswire-FirstCall/ -- </strong>NETGEAR , Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, today launched the newest Internet-connected set-top box in its popular "Digital Entertainer" product family. The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.</p>

<p>In addition to personal media collections and YouTube, consumers can now easily browse, download and play newly released pay-per-view movies from Roxio CinemaNow(TM). They can also view videos on their TV from a wide variety of Internet sources, such as Hulu, Netflix and CBS, through free software trials and optional subscriptions. The Digital Entertainer Live incorporates all of these functions into a single compact player, an advantage for cluttered home entertainment cabinets.</p>

<p>"People are amassing a huge amount of their own downloaded and personal digital music, photos, and videos, as well as consuming more and more Internet video," said Phillip Pyo, NETGEAR's director of product marketing for connected home entertainment products. "According to comScore's Video Metrix service, between January 2007 and July 2009, there was a 331 percent jump in the number of minutes of video watched per average viewer per month. It went from 2 hours, 31 minutes to 8 hours, 20 minutes."</p>

<p>He added, "The vast majority of people are still watching these videos on small computer screens, so it's logical to assume that the amount of time spent will continue to increase as devices such as the Digital Entertainer Live make it even easier to watch online video on HDTVs. NETGEAR is thrilled to offer an affordable, content-rich, and easy-to-set-up and -use solution that bridges this gap and enables people to fully enjoy their home media collections and online video from popular Internet sites on the best screen in their home -- their big-screen TVs."</p>

<p><br />
<strong>The Digital Entertainer Live -- Product Features</strong></p>

<p>The Digital Entertainer Live is a compact, "plug in and go" home media player with a simple remote control that enables consumers to easily access their digital movies, videos, music and photos directly from their USB storage devices and watch them on their TV. Users need only plug a USB hard drive containing digital media content into one of two USB 2.0 ports on the Digital Entertainer Live and connect the Digital Entertainer Live to their HDTV using an HDMI or composite cable. The Digital Entertainer Live also features regular RCA jacks for connecting to older analog TVs.</p>

<p>Furthermore, with its integrated network port, the Digital Entertainer Live easily makes an Ethernet wired connection to the Internet and the home network, enabling access to digital media content stored on computers and network storage devices in the home network, as well as Internet content over the web. If consumers do not have an Ethernet connection available near their TV, they can use the optional Digital Entertainer Live Wireless USB Adapter (EVAW111) that connects the Digital Entertainer Live to the Internet and the home network via Wi-Fi . Alternatively, they can use existing electrical power outlets and a powerline device, such as NETGEAR's Home Theater Internet Connection Kit (XAVB1004), to connect the Digital Entertainer Live to the Internet and the home network.</p>

<p>By connecting the Digital Entertainer Live to a broadband Internet connection, consumers enjoy the full YouTube experience -- searching, browsing and watching millions of videos with access to subscriptions, playlists, country selections, categories and channels -- all without the need for a computer. Instead of huddling around a small computer screen to watch the latest funny video, consumers can now show it on their TV for everyone to enjoy.</p>

<p>To widen the search for Internet video content, the Digital Entertainer Live is shipped with a built-in Internet video search engine that can locate videos on the entire worldwide web. The Digital Entertainer Live performs dynamic keyword searches of more than a hundred thousand websites for Internet videos without needing a computer. The search feature yields dynamic results with each letter inputted and automatically categorizes popular subjects into easy-to-find folders.</p>

<p>The Digital Entertainer Live also supports pay-per-view movies on-demand from Roxio CinemaNow, where users can buy or rent a range of newly released movies as soon as they are available on DVD and watch them in minutes. Consumers no longer have to wait for the mail or drive to the nearest rental store. They simply browse all the movies on their TV and download them to a USB storage device using their Digital Entertainer Live and Roxio CinemaNow account.</p>

<p>Additionally, the Digital Entertainer Live includes a free trial of VuNow, which provides access to hundreds of other Internet videos, live Internet TV and live Internet radio streamed from popular sites from around the world, such as Bloomberg, CNN Video, C-SPAN, ESPN, Germany's 2DF, Al Jazeera, BBC Worldwide, China's CCTV, Germany's DWTV, Euronews, EuroSport, France 24, France's Orange Sport, Germany's RTL, and Sky News. The Digital Entertainer Live also comes with a free trial of PlayOn software. By running this optional software on a computer also connected to the Internet and home network, users enjoy hit TV shows and movies from popular Internet video services such as Hulu, Netflix, Amazon Video On Demand, BBC iPlayer, CBS, NFL, the Australian Broadcasting Corp. and more, wherever the service is normally available via the Internet.</p>

<p>"There is an ever-increasing amount of digital media -- TV episodes, movies, photos and music -- being stored on computers and other devices throughout the home, as well as on the Internet," said Jayant Dasari, broadband and television infrastructure and services research analyst at Parks Associates. "In fact, some people have even maxed out their personal computers with media, requiring external storage, such as one that connects via USB. Due to this increase in distributed digital media content, consumers are looking for ways to enjoy their digital media and online videos in one place. Internet-connected set-top boxes are one solution that enable the entire family to benefit from viewing the broadest spectrum of digital content on their HDTVs from the comfort of their living rooms."</p>

<p><br />
<strong>Pricing and Availability</strong></p>

<p>Backed by a one-year warranty and 24/7 technical support, the NETGEAR Digital Entertainer Live (EVA2000) is available in the U.S. through leading retailers, e-commerce sites and value-added resellers at an MSRP of $149.99. The Digital Entertainer Live Wireless USB Adapter (EVAW111) has an MSRP of $39.99. Worldwide availability of the Digital Entertainer Live is planned for the coming months.</p>

<p>Product details, instructional videos and other information are at <a target="_blank" href="http://www.delive.netgear.com/">www.delive.netgear.com</a>.</p>

<p>Product photos can be downloaded from: <a target="_blank" href="http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA2000.aspx">www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA2000.aspx</a></p>

<p><br />
<strong>About NETGEAR, Inc.</strong></p>

<p>NETGEAR (NASDAQGM: NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of Small- to Medium-sized Businesses (SMBs) and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in over 29,000 retail locations around the globe, and via more than 41,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR partner. More information is available at <a target="_blank" href="http://www.netgear.com/">www.netgear.com</a> or by calling (408) 907-8000. Follow NETGEAR at <a target="_blank" href="http://twitter.com/NETGEAR/">twitter.com/NETGEAR</a> and <a target="_blank" href="http://www.facebook.com/netgear">www.facebook.com/netgear</a>.</p>

<p>2009 NETGEAR, Inc. NETGEAR and the NETGEAR logo are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>Wi-Fi is a trademark of the Wi-Fi Alliance. Any other trademarks or trade names mentioned are the property of their respective owners.</p>

<p>VuNow service will be free for trial period and thereafter subject to paid annual subscriptions. The term of the trial period may be found at the Digital Entertainer Live product site located within <a target="_blank" href="http://www.netgear.com/vunow">www.netgear.com/vunow</a>. The VuNow service will be subject to acceptance of the terms of the VuNow service and license agreement.</p>

<p>PlayOn service will be free for a trial period and thereafter offered with a special discount with Digital Entertainer Live purchase. The term of the trial period may be found at the Digital Entertainer Live product site located within <a target="_blank" href="http://www.netgear.com/playon">www.netgear.com/playon</a>. Support of online sites subject to PlayOn terms and conditions.</p>

<p>CinemaNow, Hulu and Netflix are only available in the United States. Netflix support requires an existing subscription to the Netflix service.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning NETGEAR's business and the expected performance characteristics, specifications, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 35 through 49, in the Company's quarterly report on Form 10-Q for the fiscal second quarter ended June 28, 2009, filed with the Securities and Exchange Commission on August 6, 2009. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>

<p>Source: NETGEAR, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  8, 2009  9:14 AM</b>
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
			<?=getComments(3241)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3241)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/09/netgear-introduces-digital-entertainer-live-compact-allinone-settop-box-for-playing-home-media-and-streaming-internet-video-on-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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