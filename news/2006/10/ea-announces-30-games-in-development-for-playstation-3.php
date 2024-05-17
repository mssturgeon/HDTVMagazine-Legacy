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
		AND e.entry_id = 461";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 461 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 461 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 461";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 461";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download EA Announces 30 Games in Development for PLAYSTATION 3" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="EA Announces 30 Games in Development for PLAYSTATION 3" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="EA Announces 30 Games in Development for PLAYSTATION 3" />
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
	<title>HDTV Magazine - EA Announces 30 Games in Development for PLAYSTATION 3</title>
	<meta name="keywords" content="pga tour, need speed, power playstation, medal honor, electronic arts, playstation, new, game, games, power, speed, gameplay, experience, world, gamers, unique, pga, need, tour, system, def, electronic, arts, next, jam" />
	<meta name="description" content="Electronic Arts (NASDAQ:ERTS) today announced more than 30 games in development for the PLAYSTATION&amp;reg;3 computer entertainment system. When the system launches in November, EA will deliver some of the world's most popular game franchises including Madden NFL 07, Tiger Woods PGA TOUR&amp;reg; 07 and Need for Speed&amp;trade; Carbon. EA will release eight to ten games on the PLAYSTATION&amp;reg;3 by late March including EA SPORTS&amp;trade; Fight Night Round 3 and Def Jam: ICON&amp;trade;.

Paul Lee, President of EA Studios commented on the launch of the PLAYSTATION&amp;reg;3..." />
	<meta name="title" content="EA Announces 30 Games in Development for PLAYSTATION 3" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="EA Announces 30 Games in Development for PLAYSTATION 3" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Electronic Arts (NASDAQ:ERTS) today announced more than 30 games in development for the PLAYSTATION&amp;reg;3 computer entertainment system. When the system launches in November, EA will deliver some of the world's most popular game franchises including Madden NFL 07, Tiger Woods PGA TOUR&amp;reg; 07 and Need for Speed&amp;trade; Carbon. EA will release eight to ten games on the PLAYSTATION&amp;reg;3 by late March including EA SPORTS&amp;trade; Fight Night Round 3 and Def Jam: ICON&amp;trade;.

Paul Lee, President of EA Studios commented on the launch of the PLAYSTATION&amp;reg;3..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=461', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php">EA Announces 30 Games in Development for PLAYSTATION 3</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 20, 2006</b>
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
				<p class="prtitle">EA Announces 30 Games in Development for PLAYSTATION 3</p>

<p><em><center>Launch Titles Include Madden NFL 07, Need for Speed Carbon and Tiger Woods PGA TOUR 07</center></em></p>

<p><img src="/images/logos/ea.jpg" alt="EA" align="left"><b>REDWOOD CITY, Calif.--(BUSINESS WIRE)--</b> Electronic Arts (NASDAQ:ERTS) today announced more than 30 games in development for the PLAYSTATION&reg;3 computer entertainment system. When the system launches in November, EA will deliver some of the world's most popular game franchises including Madden NFL 07, Tiger Woods PGA TOUR&reg; 07 and Need for Speed&trade; Carbon. EA will release eight to ten games on the PLAYSTATION&reg;3 by late March including EA SPORTS&trade; Fight Night Round 3 and Def Jam: ICON&trade;.</p>

<p>Paul Lee, President of EA Studios commented on the launch of the PLAYSTATION&reg;3, "Each game has been custom designed to leverage the hardware power of the PlayStation 3 and serve as a launch pad for EA's next generation of HD gaming. This is only the beginning. In the months and years to come, developers will take greater advantage of the PlayStation 3's cell processors and blu-ray storage capacity to create games of stunning depth and texture."</p>

<p>Frank Gibeau, EA Executive Vice President of North America Publishing noted, "This is a very exciting time for gamers. EA's games on the PlayStation 3 will help propel HD forward. Over the course of the next 18 months, EA will roll out groundbreaking new original properties and spectacular new versions of perennial hits that will further maximize the power of the PlayStation 3's unique cell processor and outstanding blu-ray disk capacity."</p>

<p>To date, the complete list of EA games for PLAYSTATION&reg;3 includes: (listed alphabetically)</p>

<p><b>ARMY OF TWO&trade;</b></p>

<p>Delivering a groundbreaking 3rd person co-op shooter unparalleled in the action genre, EA Montreal's ARMY OF TWO focuses on gameplay centered around TWO man missions, TWO man strategies, TWO man tactics and a TWO man advantage. Taking advantage of the PLAYSTATION&reg;3 cell processor and multi-threading technology, as well as the SIXAXIS&trade; wireless controller, the EA Montreal team is creating an entirely new next-gen gameplay experience offering gamers shooting and play mechanics never before possible on the current generation of consoles. ARMY OF TWO will throw gamers into hot spots ripped from current day headlines where they will utilize unique TWO man strategies and tactics while seamlessly transitioning between playing with intelligent Partner AI and a live player.</p>

<p><b>Burnout&trade; 5</b></p>

<p>Burnout 5 harnesses the power of the PLAYSTATION&reg;3 to give players license to wreak havoc in Paradise City, the ultimate seamless racing battleground. Every inch of the world in Burnout 5 is built to deliver heart-stopping Burnout-style crashes and spectacular gameplay</p>

<p><b>Battlefield: Bad Company&trade;</b></p>

<p>Built from the ground-up using the bleeding-edge Frostbite&trade; game engine, Battlefield: Bad Company drops PLAYSTATION&reg;3 gamers behind enemy lines with a squad of renegade soldiers who risk it all on a personal quest for gold and revenge. Featuring a deep, cinematic single-player experience loaded with adventure and dark humor, the game delivers the series' trademark sandbox gameplay in a universe where nearly everything is destructible. Battlefield: Bad Company also will feature a full suite of the franchise's trademark multiplayer options with deep gameplay designed to take full advantage of the game's massively destructible environments.</p>

<p><b>Def Jam: ICON&trade;</b></p>

<p>Infusing hip-hop music, culture and lifestyle into the gameplay, EA Chicago and urban lifestyle powerhouse Def Jam Interactive, continue to push the boundaries of game development bringing unique and innovative content to the next generation of gaming. In Def Jam: ICON, EA Chicago is changing the way fighting games are played. With the power of the PLAYSTATION&reg;3 Cell Processor, Def Jam: ICON features the most lifelike characters seen on any platform as well as a living breathing environment that animates and pulsates to the beat of the music. Not only is the environment reaching the next generation of art but each piece of the environment moves individually to the music being played during the fight. As the environment gets destroyed, the characters and the pieces of environment are animated with real world physics driven by the cell processor. The level of interaction with the environment, the smooth and fluid character animations, and the environmental detail and physics are all possible with the power of the PLAYSTATION&reg;3.</p>

<p><img src="/images/bulletins/fight-night-round-3.jpg" alt="Fight Night Round 3" align="left"><b>Fight Night Round 3</b></p>

<p>EA SPORTS Fight Night Round 3 for the PLAYSTATION&reg;3 will be shipping on December 12 to retail stores nationwide. Featuring exclusive content including a comprehensive ESPN Integration package and a new first person mode called Get in the Ring, the PLAYSTATION&reg;3 version continues to innovate on the hit franchise. Get in the Ring mode allows gamers to experience the fight through the eyes of the boxer. For the first time ever, gamers will truly experience the sensation of the sport with visual and audio effects like ear ringing, restricted vision, flashes of bright light, color shifts and blur effects that simulate the sense and feeling of getting punched. Imagine trying to recover from Ali's lighting fast jabs when you can barely make out his glove through a blinding barrage of flashes and blur. Furthermore, the boxers are more lifelike than ever with everything from the boxer's sweat and skin to the appearance of their muscles and veins all adding to the realistic gameplay experience. Tapping into the power of the PLAYSTATION&reg;3, the EA Canada development team has come up with new ways to make the boxers look more photo realistic than ever before, like seeing the reflection of the venue walls in the sweat sheen.<br clear="all" /></p>

<p><b>Madden NFL 07</b></p>

<p>With unparalleled next-generation power, new gang-tackling physics, and jaw-dropping graphics, Madden NFL 07 for the PLAYSTATION&reg;3 delivers a previously unimaginable experience that blurs the line between gaming and reality. The brand new SIXAXIS motion sensor controller puts complete command of your players at your fingertips like never before by allowing you to throw the perfect block or deliver punishing defensive hits.</p>

<p><b>Medal of Honor Airborne&trade;</b></p>

<p>Medal of Honor Airborne is the newest installment from EA's critically-acclaimed Medal of Honor&trade; franchise which was credited with pioneering the First-Person Shooter (FPS) WWII genre when it debuted in 1999. While building on the key tenets of the franchise including historical accuracy and authenticity, Medal of Honor Airborne is set to redefine the series by introducing players to an entirely new way of experiencing a WWII FPS - namely the fully interactive Airborne experience. Players will step into the boots of Boyd Travers, Private First Class of the 82nd Airborne Division and engage in battles throughout Europe. From rocky beginnings in Sicily to war-winning triumphs in Germany, each mission begins with an intense and fully interactive airdrop which leverages the unique SIXAXIS PLAYSTATION&reg;3 controller to give players complete and precise control of how, where, and when they land behind enemy lines. In this free roaming FPS environment, the path a player chooses will dramatically change the way each mission plays out. Medal of Honor Airborne will also feature exceptionally photo-realistic characters, adding to the intensity of the cinematic, story-driven game.</p>

<p><b>NBA STREET</b></p>

<p>The 4th chapter of the NBA STREET series returns with a brand new game engine only possible with the power of the PLAYSTATION&reg;3. The multi-platinum franchise is once again raising the bar with cutting edge graphics and innovative gameplay that puts the ball directly in your hands. With a new animation engine and control system, NBA STREET allows users to create tricks-on-the-fly for the first time in a basketball game. The best of the best in the NBA are rendered with meticulous detail, making true athlete fidelity a reality. NBA Stars will play in new authentic environments that are equally detailed with 360 degrees of view, making it possible to get up close and personal with every move and moment.</p>

<p><b>Need for Speed&trade; Carbon</b></p>

<p>Need for Speed Carbon and the PLAYSTATION&reg;3 introduce the world to a whole new way to play Need for Speed. The battle for Palmont City starts in the streets, but is ultimately won in the canyons as Need for Speed Carbon immerses you in the world's most dangerous and adrenaline-filled forms of street racing. The combination of the classic Need for Speed controls with the new, unique motion sensitive controller of the PLAYSTATION&reg;3 takes the gameplay to a level previously not possible. The player will instantly recognize and feel the physics differences between the 50 plus Muscle, Exotic, and Tuner cars, as they use their crew to win Canyon races, customize their cars using Autosculpt&trade;, and battle to take control of the streets of Palmont. EA is leveraging the high capacity and throughput of the Blu-ray disk to store and stream our highly complex world; something that was becoming increasingly difficult to do on other forms of media.</p>

<p><b>SKATE</b></p>

<p>With innovative controls that take advantage of the PLAYSTATION&reg;3 hardware and the dual analog sticks, SKATE offers a unique and authentic next-gen skateboard videogame experience unparalleled in the skate videogame genre. Featuring physics driven animations made only possible by the power of the PLAYSTATION&reg;3, gamers will have a unique experience every time they pick up the controller since no two tricks will ever be the same. The amount of information the game is able to take from the unique flickit analog controls and interpret it through the physics engine could never be done on a current generation console system. SKATE on PLAYSTATION&reg;3 has the ability to simulate real world physics versus canned animations. From a fully procedural trick engine to the way cloth moves on the skaters, all movement is physically simulated and dynamic offering gamers a skating game that is the closest thing to skateboarding without actually putting their feet on a board.</p>

<p><b>Tiger Woods PGA TOUR&reg; 07</b></p>

<p>Tiger Woods PGA TOUR 07 allows you to compete for The FedExCup, the new PGA TOUR&reg; championship playoff system, against some of the world's best golfers. New golfers in the game include Michael Campbell, Ian Poulter, and Annika Sorenstam. With the new True Aiming system, survey the course layout and weigh the risks of each shot before swinging away using the refined dual analog stick swing system. Develop your drive, chip shots, and putting skills in the new Practice Facility or take on a friend in new mini games including Capture the Flag, Twenty One or Target-to-Target, before unleashing yourself on the PGA TOUR. Tiger Woods PGA TOUR 07 on PLAYSTATION&reg;3 takes full advantage of the new motion-sensor controller for a greater degree of ball spin direction and speed control. Gamers tilt the controller in the direction they wish the ball to spin, the longer the tilt in the direction, the faster the ball will turn for more action on the course and around the green.</p>

<p>To download screenshots from any of these games, please visit info.ea.com.</p>

<p><b>About Electronic Arts</b></p>

<p>Electronic Arts Inc. (EA), headquartered in Redwood City, California, is the world's leading interactive entertainment software company. Founded in 1982, the company develops, publishes, and distributes interactive software worldwide for videogame systems, personal computers and the Internet. Electronic Arts markets its products under four brand names: EA SPORTSTM, EATM, EA SPORTS BIGTM and POGOTM. In fiscal 2006, EA posted revenue of $2.95 billion and had 27 titles that sold more than one million copies. EA's homepage and online game site is www.ea.com. More information about EA's products and full text of press releases can be found on the Internet at http://info.ea.com.</p>

<p>Electronic Arts, EA, EA SPORTS, EA SPORTS BIG, POGO, Need for Speed, AutoSculpt, Army of TWO, Burnout, Medal of Honor Airborne and Battlefield: Bad Company are trademarks or registered trademarks of Electronic Arts Inc. in the U.S and/or other countries. Medal of Honor is a trademark or registered trademark of Electronic Arts Inc. in the U.S. and/or other countries for computer and video game products. Def Jam&reg;, Def Jam Icon&trade;, and all associated trademarks and logos are used under license from DJR Holdings, LLC and Simcoh, LLC. John Madden, NFL, Tiger Woods, PGA TOUR and NBA are trademarks of their respective owners and used with permission. "PLAYSTATION" is a registered trademark of Sony Computer Entertainment Inc. All other trademarks are the property of their respective owners.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 20, 2006  5:46 AM</b>
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
			<?=getComments(461)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 461)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/10/ea-announces-30-games-in-development-for-playstation-3.php" type="text/javascript" charset="utf-8"></script>
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