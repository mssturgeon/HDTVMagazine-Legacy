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
		AND e.entry_id = 1619";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1619 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1619 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1619";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/netgear-unveils-two-new-internetconnected-settop-products-to-enrich-tv-entertainment-for-internet-families-and-serious-media-enthusiasts.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1619";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts" />
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
	<title>HDTV Magazine - NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts</title>
	<meta name="keywords" content="digital entertainer, entertainer elite, internet videos, digital media, serious media, netgear, internet, digital, video, products, videos, entertainer, media, elite, wireless, player, content, storage, connected, network, market, family, sites, performance, home" />
	<meta name="description" content="At the Consumer Electronics Show (CES) opening today, NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, introduced two new products that enable consumers to enjoy the world of Internet videos and digital entertainment content on their HDTV screens. Designed to enhance the connected lifestyles of Internet-generation families and serious media enthusiasts, the NETGEAR Internet TV Player (ITV2000) and the NETGEAR Digital Entertainer Elite (EVA9150) will be..." />
	<meta name="title" content="NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/netgear-unveils-two-new-internetconnected-settop-products-to-enrich-tv-entertainment-for-internet-families-and-serious-media-enthusiasts.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="At the Consumer Electronics Show (CES) opening today, NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, introduced two new products that enable consumers to enjoy the world of Internet videos and digital entertainment content on their HDTV screens. Designed to enhance the connected lifestyles of Internet-generation families and serious media enthusiasts, the NETGEAR Internet TV Player (ITV2000) and the NETGEAR Digital Entertainer Elite (EVA9150) will be..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1619', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/netgear-unveils-two-new-internetconnected-settop-products-to-enrich-tv-entertainment-for-internet-families-and-serious-media-enthusiasts.php">NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
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
				<p class="prtitle">NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts</p>

<center><i>Bringing the World of Internet Videos, Live Internet TV, YouTube, Popular Websites, HD Media Collections, Family Photos, Music and More to the TV</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/</B> -- At the Consumer Electronics Show (CES) opening today, NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, introduced two new products that enable consumers to enjoy the world of Internet videos and digital entertainment content on their HDTV screens. Designed to enhance the connected lifestyles of Internet-generation families and serious media enthusiasts, the NETGEAR Internet TV Player (ITV2000) and the NETGEAR Digital Entertainer Elite (EVA9150) will be demonstrated this week in the NETGEAR booth, 30769, in the South Hall 3 of the Las Vegas Convention Center. (See press release, "NETGEAR Delivers the Connected Lifestyle at the 2009 Consumer Electronics Show.")</p>

<p>"Internet video consumption is at an all-time high," said Vivek Pathela, NETGEAR's vice president and general manager of home/consumer products. "Just in the month of October 2008 alone, comScore(R) estimated that almost half of the total U.S. population viewed more than 13.5 billion online videos. That's a large number of Internet videos that are viewed mostly on PCs, even though many people would rather watch them on their TVs."</p>

<p>Pathela added, "Our new family of Internet-connected set-top products will, for the first time, enable users to benefit from viewing the broadest spectrum of Internet videos and digital content on their HDTVs, in the comfort of their living rooms. We're offering a diverse line-up of networked entertainment products designed to suit the needs of various connected lifestyles, from the Internet family to the serious media enthusiast."</p>

<p><br />
<B>Internet TV Player (ITV2000)</B></p>

<p>NETGEAR's Internet TV Player (ITV2000) is a compact, easy-to-use, "plug in and go" Internet set-top device with a simple remote control that enables viewers to catch up on the world of Internet videos including YouTube(TM), live Internet TV, popular Internet video websites, premium video-on-demand and online video searches retrieving billions of Internet videos from a place it was previously unavailable -- the TV in their living rooms. Rather than watching videos on PC screens in separate rooms, families can watch video from a variety of Internet sources on the TV together, in the comfort of their den or family room.</p>

<p>The Internet TV Player unlocks the value of new HDTVs as well as old analog TVs. It is ideal for the Internet families who enjoy online video, and for those who are geographically displaced from their preferred television content, such as international sporting events and Bollywood productions. It streams content from popular sites such as BBC.com, CNN.com, ESPN.com, EuroSport.com, NBC.com, PGATour and TMZ.com, as well as video powerhouses YouTube, Google Videos(TM), Yahoo Videos(TM) and MetaCafe(TM). NETGEAR's Internet TV Player supports streaming of live TV broadcasts from Internet sites around the world, and premium, paid movies on demand such as CinemaNow.com, in addition to downloaded videos from sites such as BitTorrent(R). Its superior VTap(TM) video search capabilities enable the intelligent search of Internet videos, including targeting video sites by country, topic of interest, person or popular website. Consumers are also able to play video, music, and photos from a local USB flash drive as well as from the NETGEAR ReadyNAS(R) family of storage solutions.</p>

<p>Slightly larger than a deck of cards, the compact Internet TV Player connects to the home network and the Internet via Ethernet or wireless USB adapter. It does not require a PC to play Internet video, nor does it require installing any PC software or setting up file sharing or firewall settings.</p>

<p>The NETGEAR Internet TV Player (ITV2000) is expected to be available in early Summer 2009 through leading retailers, e-commerce sites, and value-added resellers, at an MSRP in the U.S. of $199. More information can be found at (http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/itv2000.asp x).</p>

<p><br />
<B>Digital Entertainer Elite (EVA9150)</B></p>

<p>An ideal solution for serious media enthusiasts, NETGEAR's Digital Entertainer Elite (EVA9150) is the most powerful and flexible digital media player available, incorporating the latest video, audio and wireless technologies to deliver an unparalleled living room experience. Its integrated 500GB hard drive, easily user-upgradeable to larger capacity disks, makes the Digital Entertainer Elite the world's most powerful home video jukebox on the market. Consumers can play on their TVs Blu-Ray quality up to 1080p digital video, high-resolution digital photos, MP3s and recorded television shows from their PCs or storage on their network. They can also enjoy Internet content, such as YouTube, Internet radio, Flickr(TM), RSS feeds, and videos from popular websites. The Digital Entertainer Elite automatically finds all digital media files on the home network and organizes them into an easily accessible library.</p>

<p>The Digital Entertainer Elite is exceptional in that it works simultaneously with Windows(R), Mac(R) and Linux computers, and Networked Attached Storage (NAS) devices, such as NETGEAR's award-winning family of ReadyNAS(R) storage solutions. It supports an unmatched list of file formats, including:</p>

<p>  --  Video formats: AVI, DivX, Xvid WMV, MOV, M4V, MP4, VOB, MPG, MP1, MP2,<br />
      MP4, ISO, IFO, MKV, TS and M2TS;<br />
  --  Audio formats: MP1, MP2, MP3, WMA, WMA-Pro, AAC, FLAC, AIFF, WAV,<br />
      LPCM, Dolby AC3 5.1 stereo downmix, Dolby AC3 5.1 passthrough, DTS 5.1<br />
      passthrough, Dolby TrueHD Downmix, Dolby+ Passthrough and DTS-HD<br />
      Master Audio passthrough;<br />
  --  Subtitle formats: SUB, SRT, SMI, SAMI, TXT and DVD Subpicture; and,<br />
  --  Video codecs: MP1, MP2, MP4, Xvid, VC-1/WMV-9, H.264 and OpenDivX.</p>

<p></p>

<p>The Digital Entertainer Elite's two USB ports also enable instant access to content on a USB flash drive, digital camera, iPod(R) or other USB storage device. Three of NETGEAR's exclusive RangeMax(TM) internal metamaterial antennas and Dual-Band wireless 802.11n give the Digital Entertainer Elite the best wireless performance of any digital media player on the market. More specifically, the Dual-Band wireless enables the Digital Entertainer Elite to pick from not only three 2.4GHz channels, crowded by 802.11g wireless networks in the neighborhood, but up to twenty clearer channels in the 5GHz band. Further, the Sigma Designs 8635 chip ensures the most powerful and flexible audio and video performance.</p>

<p>Multiple NETGEAR Digital Entertainer Elite units can work in concert throughout a house. Using "Follow Me," consumers can pause a video in one room and resume it in another. While in "Party Mode," they can synchronize music playback for whole-home listening.</p>

<p>The Digital Entertainer Elite also features NETGEAR's award-winning Push 'N' Connect to seamlessly and securely connect wireless clients based on Wi-Fi Protected Set-up (WPS), eliminating the need to remember or input password keys. Further, NETGEAR's Digital Entertainer Elite includes environmentally friendly features, such as an energy-efficient power supply and auto-sleep mode.</p>

<p>Backed by a one-year warranty and full 24/7 technical support, the NETGEAR Digital Entertainer Elite (EVA9150) is expected to be available in February through leading retailers, e-commerce sites, and value-added resellers at an MSRP in the U.S. of $399. More information can be found at (http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/eva9150.asp x).</p>

<p>"NETGEAR's product introductions take advantage of two major trends that we are seeing with consumers -- digital media and connectivity," said Kurt Scherf, vice president and principal analyst of Parks Associates, which specializes in research and analysis for digital living technologies. "First, as digital media collections grow, we anticipate that the average broadband household will require a significant amount of additional storage and media centralization capabilities. Second, our Digital Media Evolution study finds high demand for connected CE experiences that can deliver premium Web content to the living room."</p>

<p><br />
<B>About NETGEAR, Inc.</B></p>

<p>NETGEAR (NASDAQGM: NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of small- to medium-sized businesses and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in over 29,000 retail locations around the globe, and via more than 41,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR(R) partner. More information is available by visiting www.netgear.com or calling (408) 907-8000.</p>

<p>(C) 2009 NETGEAR, Inc. NETGEAR(R), the NETGEAR logo, RangeMax and ReadyNAS are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Maximum wireless signal rate derived from IEEE Standard 802.11 specifications. Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning the expected performance characteristics, specifications, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 31 through 44, in the Company's Quarterly Report on Form 10-Q for the fiscal quarter ended September 28, 2008, filed with the Securities and Exchange Commission on November 7, 2008. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 11:02 AM</b>
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
			<?=getComments(1619)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1619)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/netgear-unveils-two-new-internetconnected-settop-products-to-enrich-tv-entertainment-for-internet-families-and-serious-media-enthusiasts.php" type="text/javascript" charset="utf-8"></script>
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