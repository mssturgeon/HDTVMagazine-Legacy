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
		AND e.entry_id = 3206";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3206 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3206 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3206";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3206";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" />
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
	<title>HDTV Magazine - New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September</title>
	<meta name="keywords" content="computer entertainment, sony computer, vertical stand, entertainment inc, system software, playstation, system, new, entertainment, computer, sony, vertical, users, inc, stand, software, available, cech, network, further, content, bravia, games, power, hdmi" />
	<meta name="description" content="Sony Computer Entertainment Inc. (SCE) today unveiled the new PlayStation 3 (CECH-2000A) (body color: charcoal black) computer entertainment system, featuring an extremely streamlined form factor with a 120GB Hard Disk Drive (HDD). The new PlayStation 3 (PS3 ) system will become available in stores from..." />
	<meta name="title" content="New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sony Computer Entertainment Inc. (SCE) today unveiled the new PlayStation 3 (CECH-2000A) (body color: charcoal black) computer entertainment system, featuring an extremely streamlined form factor with a 120GB Hard Disk Drive (HDD). The new PlayStation 3 (PS3 ) system will become available in stores from..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3206', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php">New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 18, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=275&category=Gaming">Gaming</a></b>
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
				<p class="prtitle">New Slimmer and Lighter PlayStation(R)3 to Hit Worldwide Market This September</p>

<center><i>Lower Price to Further Accelerate Expansion of the PlayStation(R)3 Platform Along with Extensive Software Title Line-up for Upcoming Holiday Season</center></i><br />
<br />

<p><strong>TOKYO, Aug. 18 /PRNewswire/</strong> -- Sony Computer Entertainment Inc. (SCE) today unveiled the new PlayStation 3 (CECH-2000A) (body color: charcoal black) computer entertainment system, featuring an extremely streamlined form factor with a 120GB Hard Disk Drive (HDD). The new PlayStation 3 (PS3 ) system will become available in stores from September 1, 2009, in North America, Europe/ PAL territories and Asian countries and regions at a very attractive recommended retail price (RRP) of US$299 and euro 299, respectively. The system will become available in Japan on September 3, 2009, at a RRP of 29,980 yen (including tax). With the introduction of the new PS3 system, SCE will also reduce the price of the current PS3 with 80GB HDD to a RRP of US$299 from August 18 and euro 299 from August 19. Also in North America, the price of PS3 with 160GB HDD will be reduced to a RRP of US$399 from August 18. By launching a vast library of exciting and attractive software titles for PS3 this holiday season and offering customers a line-up of hardware models and pricing to match their preference, SCE will build on the momentum and further accelerate the expansion of the PS3 platform.</p>

<p>The internal design architecture of the new PS3 system, from the main semiconductors and power supply unit to the cooling mechanism, has been completely redesigned, achieving a much slimmer and lighter body. Compared to the very first PS3 model with 60GB HDD, the internal volume as well as its thickness and weight are trimmed down to approximately two-thirds. Furthermore, power consumption is also cut to two-thirds, helping to reduce fan noise. While inheriting the sleek curved body design of the original model, the form factor of the new PS3 system features a new meticulous design with textured surface finish, giving an all new impression and a casual look. With the compact body and casual appearance, the newly introduced model will appeal to a wider audience who are looking to buy the best entertainment system for their home.</p>

<p>Concurrently with the release of the new PS3 system, SCE will modify the PS3 brand name from "PLAYSTATION 3" to "PlayStation 3", and introduce a new "PS3" logo, which is engraved on the surface of the new PS3 system. By unifying under the familiar "PlayStation " name, which represents the entire PlayStation family, PS3 together with PlayStation 2 and PSP (PlayStation Portable) will further expand the PlayStation business, and will continue to enhance the entertainment experience along with the ever-growing PlayStation Network.</p>

<p>The new PS3 continues to offer the cutting-edge features and functions of the current models, such as the ability to enjoy high-definition Blu-ray disc (BD) movies and games, as well as various content and services downloadable through the network. The new PS3's storage size has increased from 80GB to 120GB, and with the extra capacity users will be able to store more games, music, photos, videos as well as various content and services available through PlayStation Network. Having more than 27 million registered accounts around the world, PlayStation Network offers more than 15,000 pieces of digital content, ranging from game titles, trailers, and demos to more than 15,000 movies and TV shows via PlayStation Store(*1). PlayStation Network members can also download free applications, such as PlayStation Home, a ground-breaking 3D social gaming community available on PS3 that allows users to interact, communicate and share gaming experiences, as well as Life with PlayStation, which offers users various news and information on a TV monitor in the living room by connecting the PS3 to the network.</p>

<p>Since the launch of PS3 in November 2006, the number of BD-based titles has reached more than 1,000 titles and downloadable PS3 games to 1,400(*2) titles worldwide, with the support from a broad range of third party game developers and publishers. In addition to this extensive software title line-up, exciting and attractive new titles are to be released from SCE Worldwide Studios, including Uncharted 2: Among Thieves, EyePet, Ratchet & Clank Future: A Crack in Time, Heavy Rain, God of War 3, MAG, ModNation Racer, Gran Turismo 5 and more.</p>

<pre>
Other features of the new PS3 include:

<p>  - PS3 system software update version 3.00</p>

<p>    Concurrently with the release of new PS3, system software will be<br />
    upgraded to version 3.00 on September 1.  The update adds various user-<br />
    friendly features such as the "What's New" screen, where users can<br />
    quickly browse the new items available in PlayStation Store as well as<br />
    their recently played games directly on the XMB(TM) (XrossMediaBar),<br />
    with short cuts to each piece of content.  PS3 will evolve continuously<br />
    with the system software updates, further improving the operability and<br />
    enhancing the user experience available through the network.  PS3 owners<br />
    will be able to enjoy new features by simply updating the PS3 system<br />
    software to version 3.00 via the "System Update" function on the<br />
    XMB(*3).</p>

<p>  - BRAVIA(R) Sync(TM) Feature</p>

<p>    The new PS3 system is also equipped with the BRAVIA(R) Sync(TM) feature.<br />
    By connecting the new PS3 system and a BRAVIA TV with the HDMI cable,<br />
    users are able to directly operate the XMB on PS3 using the TV remote<br />
    control.  Other functions include "System Standby" that will<br />
    automatically turn off the PS3 system when the BRAVIA TV is turned<br />
    off(*4).</p>

<p>  - "Vertical Stand" for new PS3  (CECH-2000 series)</p>

<p>    By utilizing the separately sold "Vertical Stand", users will be able to<br />
    set the new PS3 in vertical position(*5), making it easier to place the<br />
    PS3 system anywhere at home.  The vertical stand will become available<br />
    in Japan on September 3, 2009, at a RRP of 2,000 yen (including tax) and<br />
    in North America at US$24(*6).</p>

<p>  - Removal of "Install Other OS" feature</p>

<p>    The new PS3 system will focus on delivering games and other<br />
    entertainment content, and users will not be able to install other<br />
    Operating Systems to the new PS3 system.<br />
</pre></p>

<p>Along with a vast line-up of attractive and exciting entertainment content with the new PS3 system, SCE will continue to further expand the PS3 platform and create a new world of computer entertainment.<br />
<pre><br />
  *1    Number as of end July 2009.  Content within PlayStation Store will<br />
        differ by region, please refer to the official PlayStation.com site<br />
        for further details.<br />
  *2    Includes PS one(R) classics and free of charge content (downloadable<br />
        demos).<br />
  *3    Users will need to connect their PS3 to the network to use the<br />
        function.<br />
  *4    Users will need to use BRAVIA TV that supports the BRAVIA Sync<br />
        feature.  For further information about BRAVIA Sync, please refer to<br />
        the official Sony site in each region.<br />
  *5    Users will need to use the separately sold "Vertical Stand" to set<br />
        the new PS3 in vertical position.<br />
  *6    Release date of the vertical stand for North America will be<br />
        announced when available.  Release date and price of the vertical<br />
        stand for Europe/ PAL territories and Asian countries and regions<br />
        will be announced when available.</p>

<p><br />
  Product Outline<br />
  PlayStation(R)3 (CECH-2000A)</p>

<p>   Product name                    PlayStation(R)3</p>

<p><br />
   Product code                    CECH-2000A (Charcoal Black)</p>

<p><br />
   CPU                             Cell Broadband Engine(TM)</p>

<p><br />
   GPU                             RSX(R)</p>

<p><br />
   Audio output                    LPCM 7.1ch, Dolby Digital, Dolby Digital<br />
                                    Plus, Dolby TrueHD, DTS, DTS-HD, AAC.</p>

<p><br />
   Memory                          256MB XDR Main RAM, 256MB GDDR3 VRAM</p>

<p><br />
   Hard disk      2.5" Serial ATA  120GB(*1)</p>

<p><br />
   Inputs/        Hi-Speed USB<br />
   Outputs(*2)    (USB 2.0)        2</p>

<p><br />
   Networking                      Ethernet (10BASE-T, 100BASE-TX,<br />
                                    1000BASE-T) x 1</p>

<p>                                   IEEE 802.11 b/g</p>

<p>                                   Bluetooth(R) 2.0 (EDR)</p>

<p><br />
   Controller                      Wireless Controller (Bluetooth(R))</p>

<p><br />
   AV output      Resolution       1080p, 1080i, 720p, 480p, 480i (for PAL<br />
                                    576p, 576i)</p>

<p>                  HDMI OUT<br />
                   connector(*3)   1</p>

<p>                  AV MULTI OUT<br />
                   connector       1</p>

<p>                  Digital out<br />
                   (optical)<br />
                   connector       1</p>

<p><br />
   BD/DVD/CD      Maximum read     BD x 2 (BD-ROM)<br />
    drive (read    rate            DVD x 8 (DVD-ROM)<br />
    only)                          CD x 24 (CD-ROM)</p>

<p><br />
   Power                           AC 220 - 240, 50/60Hz(*4)</p>

<p><br />
   Power consumption               Approx. 250W</p>

<p><br />
   External dimensions             Approx. 290 x 65 x 290 mm (width x height<br />
   (excluding maximum projecting    x length)<br />
    part)</p>

<p><br />
   Mass                            Approx. 3.2kg</p>

<p><br />
   Included (*5)                   PlayStation(R)3 system x 1<br />
                                   Wireless Controller (DUALSHOCK(R)3) x 1<br />
                                   AC power cord x 1<br />
                                   AV cable x 1<br />
                                   USB cable x 1</p>

<p><br />
  *1    Hard disk capacity calculated using base 10 mathematics (1 GB =<br />
        1,000,000,000 bytes). System software versions 1.10 and later<br />
        calculate capacity using binary mathematics (1 GB = 1,073,741,824<br />
        bytes), which will display lower capacity and free space. A portion<br />
        of hard disk capacity is reserved for system administration, which<br />
        varies depending upon system software version, and is not available<br />
        for use.<br />
  *2    Usability of all connected devices is not guaranteed.<br />
  *3    "Deep Colour" and "x.v.Colour (xvYCC)" defined by HDMI ver.1.3a are<br />
        supported.<br />
  *4    Power changes depending on countries or regions.<br />
  *5    For certain regions, Euro-AV cable will be included.<br />
  Note: This product is not compatible with PlayStation(R)2 games.</p>

<p>  New Logo<br />
  "PS3"(TM)<br />
  PlayStation 3</p>

<p><br />
  Vertical Stand (CECH-ZS1)</p>

<p>  Product name              Vertical Stand</p>

<p>  Product code              CECH-ZS1</p>

<p>  Included                  Vertical Stand (CECH-ZS1) x 1</p>

<p>  External dimension        Approx. 88 mm x 18 mm x 260 mm (width x height<br />
                             x length)</p>

<p>  Mass                      Approx. 115g</p>

<p>  Supports                  CECH-2000 series</p>

<p>  *    The "Vertical Stand" is for the new PS3 system (CECH-2000 series) and<br />
       cannot be used on the current model.<br />
</pre></p>

<p><br />
<strong>About Sony Computer Entertainment Inc.</strong></p>

<p>Recognized as the global leader and company responsible for the progression of consumer-based computer entertainment, Sony Computer Entertainment Inc. (SCEI) manufacturers, distributes and markets the PlayStation game console, the PlayStation 2 computer entertainment system, the PSP (PlayStation Portable) handheld entertainment system and the PlayStation 3 (PS3 ) system. PlayStation has revolutionized home entertainment by introducing advanced 3D graphic processing, and PlayStation 2 further enhances the PlayStation legacy as the core of home networked entertainment. PSP is an innovative handheld entertainment system that allows users to enjoy 3D games, with high-quality full-motion video, and high-fidelity stereo audio. PS3 is an advanced computer system, incorporating the state-of-the-art Cell processor with super computer like power. SCEI, along with its subsidiary divisions Sony Computer Entertainment America Inc., Sony Computer Entertainment Europe Ltd., and Sony Computer Entertainment Korea Inc. develops, publishes, markets and distributes software, and manages the third party licensing programs for these platforms in the respective markets worldwide. Headquartered in Tokyo, Japan, Sony Computer Entertainment Inc. is an independent business unit of the Sony Group.</p>

<p>  Dolby is a trademark of Dolby Laboratories.<br />
  DTS is a trademark of Digital Theater Systems, Inc.<br />
  HDMI, HDMI logo and High Definition Multimedia Interface are trademarks of<br />
  HDMI Licensing LLC.<br />
  Blu-ray Disc is a trademark.<br />
  The Bluetooth word mark is a registered trademark owned by Bluetooth SIG,<br />
  Inc. and any use of such marks by Sony Computer Entertainment Inc. is<br />
  under license.<br />
  PlayStation, PLAYSTATION, PS3, RSX, DUALSHOCK and GRAN TURISMO are<br />
  registered trademarks of Sony Computer Entertainment Inc.  Cell Broadband<br />
  Engine is a trademark of Sony Computer Entertainment Inc. All other<br />
  trademarks are property of their respective owners.</p>

<p>Source: Sony Computer Entertainment America</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 18, 2009  8:15 PM</b>
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
			<?=getComments(3206)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3206)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/08/new-slimmer-and-lighter-playstationr3-to-hit-worldwide-market-this-september.php" type="text/javascript" charset="utf-8"></script>
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