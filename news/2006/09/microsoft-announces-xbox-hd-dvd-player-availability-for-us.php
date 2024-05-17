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
		AND e.entry_id = 453";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 453 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 453 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 453";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 453";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Microsoft Announces Xbox HD DVD Player Availability for U.S." height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Microsoft Announces Xbox HD DVD Player Availability for U.S." />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Microsoft Announces Xbox HD DVD Player Availability for U.S." />
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
	<title>HDTV Magazine - Microsoft Announces Xbox HD DVD Player Availability for U.S.</title>
	<meta name="keywords" content="xbox live, game studios, microsoft game, interactive entertainment, dvd player, xbox, game, new, microsoft, games, studios, entertainment, dvd, live, windows, player, next, interactive, titles, available, gamers, online, world, first, title" />
	<meta name="description" content="Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &amp;#8364;199.99/&amp;#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with ..." />
	<meta name="title" content="Microsoft Announces Xbox HD DVD Player Availability for U.S." />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Microsoft Announces Xbox HD DVD Player Availability for U.S." />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &amp;#8364;199.99/&amp;#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with ..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=453', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php">Microsoft Announces Xbox HD DVD Player Availability for U.S.</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 27, 2006</b>
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
				<p class="editorial"><img src="/images/bulletins/xbox360-hd-dvd.gif" alt="Xbox 360 HD DVD" align="left">Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &#8364;199.99/&#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with both the Universal Pictures blockbuster Peter Jackson's King Kong on HD DVD (for a limited time) and the Xbox 360 Universal Media Remote.<br clear="all"></p>

<p>Here is the full Newsflash from Xbox.com:</p>

<p class="prtitle">Xbox 360 Welcomes New Worlds of Entertainment</p>

<p>Barcelona, Spain-On the shores of one of the world's most artistic and progressive cities, Microsoft Corp. today thrilled attendees at its annual X06 event by inviting everyone to experience the next generation now on the Xbox 360&trade; system. The announcements and hands-on gameplay experiences highlight how the world's greatest game creators are pushing the boundaries of what is possible in high definition and online storytelling. Highlights of the announcements included the following:</p>

<p>    * A landmark partnership between Academy Award-winning writer, director, and producer Peter Jackson, Academy Award-winning screenwriter Fran Walsh, and Microsoft Game Studios will create two new interactive entertainment series exclusively for Xbox 360 and Xbox Live&reg;. The first will be a collaborative effort with Bungie Studios to co-create the next great chapter in the Halo&reg; universe. The second will be an entirely original property targeted at bringing new audiences into the captivating world of interactive entertainment. In addition, Microsoft Game Studios will partner with Jackson and Walsh to establish Wingnut Interactive, a studio dedicated to the creation of world-class interactive entertainment.<br />
    * Halo Warsis an all-new real-time strategy game based on the legendary Halo universe and designed exclusively for Xbox 360 by Ensemble Studios, creators of the Age of Empires&reg; franchise.<br />
    * Rockstar and Take-Two will provide Xbox 360 gamers with exclusive access to two epic  downloadable episodes of Grand Theft Auto IV via Xbox Live, each with hours of new gameplay content, and available only on Xbox 360 just months after the release of the title.<br />
    * Ubisoft confirmed that the next Splinter Cell title, the installment after Tom Clancy's Splinter Cell&reg; Double Agent&trade;, will be exclusive to Xbox 360, a testament to the ability of the powerful next-gen game console to deliver experiences no other console can match.<br />
    * 2K Games confirmed that BioShock , a first person shooter that will revolutionize the genre and forever change the expectations of gamers, will be released exclusively on Xbox 360 and Microsoft&reg;Windows&reg; next spring.<br />
    * Project Gotham Racing&reg; 4, was unveiled, the latest addition to the best-selling franchise, made exclusively for Xbox 360 by Bizarre Creations. PGR4 promises to continue the series' pedigree of innovation by introducing exciting new experiences to racing fans worldwide.<br />
    * The beloved Banjo-Kazooie&reg; franchise will breathe new, high-definition life exclusively on Xbox 360, from famed developer Rare Ltd. Beloved characters Banjo, Kazooie and Gruntilda-among other fan favorites-will new next-gen visuals and presentation as well as their sharp wit and hilarious sense of humor.<br />
    * Microsoft Game Studios will release its highly-anticipated new MMO game, Marvel Universe Online for both Xbox 360 and the Windows Vista&trade; operating system. MUO was developed by industry luminaries Cryptic Studios, creators of the smash hits City of Heroes and City of Villains.<br />
    * Expect two new additions to Xbox Live Arcade: The FPS that pioneered the network-gaming era, DOOM&reg;, from acclaimed developer id Software and Activision, is available now on Xbox Live Marketplace. The game includes the original four-episode single-player game, four-player split screen action, both co-op and deathmatch, and four player co-op and deathmatch via Xbox Live. Coming soon to Xbox Live Arcade is Sensible World of Soccer from Codemasters. Based on a classic Amiga title from 1994, Sensible World of Soccer will let gamers choose between the original graphics or an updated, high-resolution look and feel-while still capturing the original game's wide world of football.<br />
    * Arriving at retailers in North America, the U.K., France, and Germany in mid-November 2006, the Xbox 360 HD DVD Player will retail for $199.99 in North America (ESRP) and &#8364;199.99/&#163;129.99 (ESRP) in the U.K., France, and Germany. The Xbox 360 HD DVD Player comes with both the Universal Pictures blockbuster Peter Jackson's King Kong on HD DVD (for a limited time) and the Xbox 360 Universal Media Remote.</p>

<p>"Xbox 360 is changing the way developers are telling stories today-from the industry's most beloved franchises to exciting new properties," said Peter Moore, corporate vice president of the Interactive Entertainment Business in the Entertainment and Devices Division at Microsoft. "We are inspiring the imaginations of the entertainment industry's best and most creative talent to take their franchises in exciting directions, while also spinning new tales for everyone."</p>

<p>"We are incredibly pleased to be here in Barcelona to talk about the next chapter of Xbox&reg; and how Xbox 360 continues to deliver on the promise and potential of the next generation," said Chris Lewis, regional vice president of the Home and Entertainment Division, Europe, Middle East, and Africa (EMEA), at Microsoft. "As we prepare to launch some of our biggest global titles and best regional content, both right now and well into the future, consumers will have much to choose from with a system designed for high-definition and online entertainment. We are the only next-generation experience that seamlessly connects players to their games, friends, and entertainment content."</p>

<p><b>Xbox 360 Expands Popular Franchises</b><br />
Xbox 360 continues to expand the interactive entertainment landscape, enabling industry superstars to take beloved franchises in exciting new directions. Microsoft Game Studios announced its partnership with famed movie director Peter Jackson to herald in a new age of interactive entertainment that can only be realized through Xbox 360 and Xbox Live. The goal of this partnership is to not only create new stories, but to redefine the way they are told. The first part of this long-term relationship is for Jackson, his partner Fran Walsh, and their team to bring to life two new interactive entertainment experiences exclusively for Xbox 360 and Xbox Live. The first project is to co-write, co-design, and co-produce a completely new and original chapter in the Halo universe in collaboration with Bungie Studios. The second project is an entirely original property from the team in New Zealand that will not only bring a whole new interactive story to life, but will also captivate new audiences that have yet to discover the power of interactive entertainment.</p>

<p>Additionally, in collaboration with Microsoft Game Studios, Jackson and Walsh are creating Wingnut Interactive. The two companies will create a world-class interactive entertainment studio that fuses the strength of Microsoft's technology and interactive entertainment experience with the creative and imaginative excellence of the Wingnut team.</p>

<p>"Microsoft has built an amazing living canvas with Xbox 360 and Xbox Live, which allows the storytellers of our time to express themselves in a new medium. They have fundamentally changed how people think about games," Jackson said. "My vision, together with Microsoft Game Studios, is to push the boundaries of game development and the future of interactive entertainment. From a movie-maker's point of view, it is clear to me that the Xbox 360 platform is the stage where storytellers can work their craft in the same way they do today with movies and books, but taking it further with interactivity."</p>

<p>In addition to demonstrating its leadership in the next-generation of games through exclusive alliances, Microsoft Game Studios announced Halo Wars, an all-new RTS game based on the legendary Halo universe and built exclusively for Xbox 360 by Ensemble Studios, the creators of the Age of Empires franchise. Halo Wars places the player in command of human UNSC armies as they deploy for mankind's first deadly encounter with the enemy forces of the Covenant.</p>

<p>In addition, Microsoft Game Studios provided first details surrounding Project Gotham Racing 4, the latest addition to the premiere racing franchise from Bizarre Creations, and the reunion of Banjo, Kazooie, and Gruntilda in an all-new addition to the Banjo-Kazooie franchise from industry veterans Rare.</p>

<p>Microsoft and Ubisoft announced that the next Splinter Cell title will be created exclusively for Xbox 360. Based on the increasingly proven potential of the Xbox 360 hardware and its online promise, Ubisoft confirmed that Xbox 360 will be the exclusive platform for the next iteration of its massively popular and influential espionage franchise. Through the power of Xbox Live, the series that revolutionized online cooperative and competitive gameplay promises to transform and modernize online gaming once again.</p>

<p>New details regarding the epic, exclusive episodic content for the upcoming and highly-anticipated Grand Theft Auto IV from Rockstar and Take-Two were also revealed; Rockstar Games will offer two downloadable episodes, each with hours of new gameplay, extending the experience of what already promises to be an immense game. Both chapters will be exclusive and available only to Xbox 360 gamers via Xbox Live. Grand Theft Auto IV will be available to Xbox 360 gamers on its first day of availability: October 16, 2007, in North America and October 19, 2007, in Europe.</p>

<p><b>Xbox Live Announcements</b><br />
Xbox Live Arcade made a surprise announcement today, unveiling one of the greatest games of the 3-D era: DOOM is now available for download for only 800 Microsoft points. The game brings legendary DOOM mayhem to gamers, who for the first time ever can relive the classic demon-blasting frag fest in both single-player and two-to-four player co-op and deathmatch modes over Xbox Live. Also a new, multi-title relationship with Codemasters was announced, with the first title being the classic, fast-action soccer game, Sensible World of Soccer.</p>

<p>Xbox Live is a thriving online game community, connecting more than 3 million members across nearly 25 countries to enjoy hundreds of social games, as well as on-demand game demos, Xbox Live Arcade games, music, and movie content. With more than 10 million downloads to date and nearly 100 independent, classic, and original development titles available by next summer, Xbox Live Arcade is a fast-growing phenomenon.</p>

<p><b>Jump Into HD DVD Affordably</b><br />
At X06, exciting details about the much-anticipated Xbox 360 HD DVD Player were also revealed. Available in mid-November, 2006 in North America for $199.99 (ESRP), in the U.K., France, and Germany for &#8364;199.99 (&#163;129.99) (ESRP), and other territories in 2007, the Xbox 360 HD DVD Player comes with the Universal Pictures blockbuster film Peter Jackson's King Kong on HD DVD (for a limited time) and the Xbox 360 Universal Media Remote. Users can just add the Xbox 360 HD DVD Player to their Xbox 360 to create the ultimate home-theater experience.</p>

<p>"The Xbox 360 HD DVD Player is the best high-definition movie experience and value on the market," Moore said. "The reviews, the word of mouth, and the consumer response have all been crystal clear-HD DVD is the format of choice. We're not forcing movie technology on game players, but are instead letting them choose how to personalize their experiences. If they want HD DVD, there's no better value out there."</p>

<p>The Xbox 360 HD DVD Player offers up to six times higher resolution than DVD, and as part of the fall 2006 console update all Xbox 360 consoles will have the ability to output native resolution 1080p games and movies. Users can enjoy blockbuster HD DVD releases, with more than 150 titles available by the holidays from major movie studios including Paramount Pictures, StudioCanal, Universal Studios, New Line Entertainment, HBO, and Warner Bros. Entertainment Inc.</p>

<p><b>The Most Anticipated Titles</b><br />
In addition to taking popular franchises in new directions, developers are finding new life through Xbox 360 with exciting new content, and are delivering many of the industry's most praised and anticipated titles.</p>

<p>Arguably the most anticipated title of 2006, Gears of War&reg; from Epic Games and Microsoft Game Studios is a third-person tactical action/horror game available exclusively on Xbox 360.Gears of War will be the only game to blend a deep and disturbing story of human survival against hordes of nightmarish creatures with a next-generation tactical combat system and unsurpassed visuals and special effects. Gears of War, which will be available Nov. 12, 2006, in the U.S. and Nov. 17, 2006, in Europe, has garnered numerous industry awards and accolades including the Game Critics Awards Best Console and Best Action Game of E3 2006, IGN's E3 2005 Best Xbox 360 Game, and GameSpot's E3 2006 People's Choice Award.</p>

<p>From leading U.K. based developer Rare and Microsoft Game Studios comes Viva Piñata&trade;, an original game concept and the latest innovative gaming experience for gamers of all ages and types. Viva Piñata invites gamers to create an immersive world where living piñatas inhabit an ever-changing environment. Viva Pinata, which has won several industry awards including Best Graphics from Nick Jr. Magazine and IGN's Runner-Up for Best Strategy Game of E3 2006, is scheduled to be available this holiday.</p>

<p>Underlining the Xbox 360 platform strength, Microsoft and Ubisoft today confirmed that Assassin's Creed, the eagerly anticipated action title during the crusades, is also coming to Xbox 360 on the same day and date as the game's release on other platforms. Assassin's Creed is a next-generation action-adventure/stealth title from the highly talented and critically acclaimed team that brought gamers Prince of Persia: The Sands of Time . Assassin's Creed will place gamers in the role of a ruthless and skilled assassin as he silently stalks his victims.</p>

<p>Last week at the Tokyo Game Show in Japan, Lost Odyssey wowed audiences with its incredible graphic style and epic storyline. From famed Japanese developer Hironobu Sakaguchi, Lost Odyssey will be shipped in Japan in 2007, and in the U.S. and Europe at a later date.</p>

<p>Few games have generated more interest than BioShock in the past year, and Microsoft confirmed today that the highly anticipated first-person shooter will be exclusive to Xbox 360 and Windows when it launches in spring 2007.</p>

<p><b>More Exciting Titles</b><br />
Looking into the coming year, X06 showcased a montage video trailer demonstrating the dazzling power and versatility of the Xbox 360 platform, promising gamers a choice of titles and genres coming this spring season, including the Microsoft Game Studios titles Crackdown&trade;, Too Human, Mass Effect&trade;, and Forza Motorsport&trade; 2. In addition to these titles from Microsoft Game Studios, games from today's leading publishers round out what is already a robust library of offerings for the Xbox 360 platform, including John Woo Presents Stranglehold, Lost Planet, and Pro Evolution Soccer 6.</p>

<p>Microsoft underscored the continued momentum behind Xbox 360 with more than 5 million consoles sold since launch, the fastest console launch ever. Microsoft remains on track to deliver 10 million consoles worldwide, with a library of 160 games, by the end of the year. The Xbox Live community continues to grow and is on track to double in size to 6 million gamers by June 2007. Those numbers are supported by the most impressive number of all: Xbox 360 is now available in more than 30 countries since the console launched last November. By the end of this year, Xbox 360 will launch in even more countries, including South Africa and in Europe where plans are set to distribute the console in Slovakia, the Czech Republic, Hungary, and Poland.</p>

<p><b>Games for Windows</b><br />
Microsoft also provided attendees with an update on Games for Windows and announced several exciting new Games for Windows titles, including Bioshock and Marvel Universe Online. Starting this September with LEGO&reg; Star Wars&reg; II: The Original Trilogy from LucasArts and Company of Heroes from THQ, games will carry the Games for Windows branding after meeting a set of technical guidelines designed to provide consumers with a consistent, reliable gaming experience on Windows XP and Windows Vista. The guidelines include easier game installation, improved reliability, and support for key Windows Vista features such as the Games Explorer and Parental Controls. They will also support wide-screen gaming, launch from within Windows Media&reg; Center, be compatible with 64-bit consumer versions of Windows, and will support the Xbox 360 Controller for Windows (for games that enable gamepads).</p>

<p>Attendees were given a glimpse of the exciting upcoming Games for Windows titles including Hellgate: London, Rail Simulator, and Age of Conan: Hyborian Adventures, as well as Microsoft Game Studios titles Age of Empires III: The WarChiefs, Alan Wake, Zoo Tycoon&reg; 2: Marine Mania&reg;, Flight Simulator X, Shadowrun&trade;, Halo&reg; 2 for Windows Vista, and the newly named Marvel Universe Online, a massively multiplayer online game for Xbox 360 and Windows Vista from Cryptic Studios, creators of the hit City of Heroes franchise.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 27, 2006 11:51 AM</b>
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
			<?=getComments(453)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 453)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/09/microsoft-announces-xbox-hd-dvd-player-availability-for-us.php" type="text/javascript" charset="utf-8"></script>
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