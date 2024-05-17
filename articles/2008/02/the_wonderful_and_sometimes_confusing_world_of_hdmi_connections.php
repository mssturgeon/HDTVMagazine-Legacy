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
		AND e.entry_id = 1241";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Robert A. Fowkes" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1241 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Robert A. Fowkes'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Robert A. Fowkes" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1241 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1241";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/02/the-wonderful-and-sometimes-confusing-world-of-hdmi-connections.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1241";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The Wonderful and Sometimes Confusing World of HDMI Connections" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The Wonderful and Sometimes Confusing World of HDMI Connections" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The Wonderful and Sometimes Confusing World of HDMI Connections" />
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
	<title>HDTV Magazine - The Wonderful and Sometimes Confusing World of HDMI Connections</title>
	<meta name="keywords" content="hdmi cables, hdmi equipped, hdmi signal, hdmi cable, digital signals, hdmi, cable, switch, work, system, time, cables, unit, power, display, signal, world, well, people, equipment, digital, important, signals, own, might" />
	<meta name="description" content="HDMI (which stands for High Definition Multimedia Interface) was developed to accommodate the emerging HD digital technologies which have now entered the mainstream of home entertainment. It was planned as a &quot;one wire&quot; digital solution to both audio and video requirements. It is also one of the most confusing connection technologies available to the general public - especially when people hdmi(3)are used to more traditional interconnectivity.  As HDMI attempts to do what it was designed to do, part of the process involves the pieces of the puzzle communicating with each other (sometimes referred to as &quot;handshaking&quot;). This two way communication causes..." />
	<meta name="title" content="The Wonderful and Sometimes Confusing World of HDMI Connections" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The Wonderful and Sometimes Confusing World of HDMI Connections" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/02/the-wonderful-and-sometimes-confusing-world-of-hdmi-connections.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="HDMI (which stands for High Definition Multimedia Interface) was developed to accommodate the emerging HD digital technologies which have now entered the mainstream of home entertainment. It was planned as a &quot;one wire&quot; digital solution to both audio and video requirements. It is also one of the most confusing connection technologies available to the general public - especially when people hdmi(3)are used to more traditional interconnectivity.  As HDMI attempts to do what it was designed to do, part of the process involves the pieces of the puzzle communicating with each other (sometimes referred to as &quot;handshaking&quot;). This two way communication causes..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1241', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/02/the-wonderful-and-sometimes-confusing-world-of-hdmi-connections.php">The Wonderful and Sometimes Confusing World of HDMI Connections</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Robert A. Fowkes</b> on <b>February 15, 2008</b>
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
				<p><b><i>Some Suggestions From One Who's Been There</i></b>  <h2>Introduction</h2> <p><b>HDMI </b>(which stands for <b>H</b>igh <b>D</b>efinition <b>M</b>ultimedia <b>I</b>nterface) was developed to accommodate the emerging HD digital technologies which have now entered the mainstream of home entertainment. It was planned as a "one wire" digital solution to both audio and video requirements. It is also one of the most confusing connection technologies available to the general public - especially when people <img title="hdmi(3)" height="199" alt="hdmi(3)" hspace="8" src="http://www.rfowkes.com/assets/images/hdmi_3_.jpg" width="200" align="right" vspace="10" border="0">are used to more traditional interconnectivity.&nbsp; As HDMI attempts to do what it was designed to do, part of the process involves the pieces of the puzzle communicating with each other (sometimes referred to as "handshaking"). This two way communication causes some unfamiliar, and therefore unsettling, things to happen as each component tries to find out what it's connected to while concurrently announcing itself to the rest of the system.&nbsp; People used to the instant-on nature of most analog and non-HDMI digital connections are disturbed when they see momentary flickering, "blue screens" and other indications that something is going on.&nbsp; While this isn't your father's audio/video system it's also not an indication that anything is wrong - as long as everybody is following the HDMI guidelines. The purpose of this article is to provide you with some insight into what to expect when incorporating HDMI into your A/V system, whether it involves a simple connection between a television and a High Definition set top box or a much more complex system that includes multiple sources including HD television content, standard and HD players (both formats), gaming systems and many other peripherals.&nbsp; It goes without saying that the more complex your particular setup is, the better the chance that at least one of the components will not play completely by the HDMI rules.</p> <p>I don't intend to go into great detail about HDMI itself (its specifications, various forms, history, future direction, etc.) except how it applies to helping you adjust to and use the technology.&nbsp; There are many, many excellent resources already out there that go into detail about HDMI and you can easily access them on the internet.&nbsp; A Google-type search on the term "HDMI" will produce a wealth of background information and I refer you to that. There's everything from Wikipedia to vendor sites offering some information (take what you see with a grain of salt, keeping in mind the authors and the agendas).&nbsp; Most of it is very informative. My personal favorite writer on this topic is my friend, Rodolfo La Maestra, who wrote an excellent series of articles for HDTV Magazine (web-based) in his role as Senior Technical for that publication. You can find these articles located at: </p> <p><a href="http://www.hdtvmagazine.com/articles/articles-author.php?author=Rodolfo+La+Maestra&amp;id=16">http://www.hdtvmagazine.com/articles/articles-author.php?author=Rodolfo+La+Maestra&amp;id=16</a>  <p>They come highly recommended and will provide you with a strong foundation in HDMI in a lucid and comprehensive fashion. It's a ten part series beginning with the July 25, 2006 article and well worth perusing.<br>It is my intention to focus on connecting all the parts of your particular HDMI-equipped system by providing some of the basics and then turning to a couple of possible solutions from a few selected manufacturers. This is by no means a complete summary of what's available out there. The landscape is changing too fast to provide such information without it quickly becoming dated.</p> <p>Let's get started.</p> <h2>Part One: Sometimes it works, sometimes it doesn't?!? </h2> <p>As mentioned before, chances are that there are a lot of people out there who own some HDMI equipment (you need at least two pieces for a connection) and they have experienced absolutely no problems at all. In fact, they probably wonder what people with HDMI problems were talking about since it is foreign to their own experiences.&nbsp; For the purposes of illustration, let's assume that these simple connections involve an HDMI equipped TV with an HDMI equipped DVD player (it doesn't even have to be an HD player at this point - more about that later). Ideally, you connect the HDMI cable from the player to the display and you are all set.&nbsp; Both picture and sound flow seamlessly to the TV over one wire.&nbsp; Probably the most challenging thing about this type of connection is making sure that the input selection on the display is set to "HDMI" (or whatever terminology the TV manufacturer chooses to use) and that the HDMI output port on the player is activated (usually from a set-up menu) and that the audio is directed through it.&nbsp; Unless there is an incompatibility between the player and the display everything should work as advertised.&nbsp; It should be noted that in the early days of HDMI some of the manufacturers didn't adhere to all the standards (La Maestra goes into what those standards are in his series of articles for those interested). Certain HD cable boxes, for example, would not connect properly to certain televisions even though both contained HDMI capabilities.&nbsp; Most of those early problems have been resolved (but not all) as more and more manufacturers are learning the ins and outs of HDMI connectivity.</p> <p>But as people began to add additional HDMI components to their systems a series of issues cropped up.&nbsp; It is not unusual in this day and age for a home entertainment system to contain not only an HDMI television content source (probably HD), at least one HDMI-equipped DVD player (most likely HD - with one or both formats: Blu-ray and/or HD-DVD) and possibly an HDMI equipped gaming device (like a PS3 or one of the newer model Xbox 360s). Add to that the fact that there are some people who have all this connected to more than one display for a number of applications and the complexity of the connectivity escalates quickly.&nbsp; All of a sudden the TV with "only" one HDMI input is considered inadequate and the problem with connecting everything together and controlling it is a major issue. And even if a solution is worked out where all the parts fit together, sometimes strange things happen, like unpredictable performance and dropped picture and sound. Luckily there's light (and sound) at the end of the tunnel.</p> <p>It must be kept in mind that what was true for pre-HDMI connections (plug them in correctly and they work) is not necessarily true for HDMI. The new situation is "plug them in correctly to HDMI equipment <i><u>which plays by the rules</u></i> and everything works."&nbsp; One of the biggest problems with some early implementations of HDMI (and it could happen in any form, 1.1, 1.2 and 1.3) was that some manufacturers took shortcuts with the specifications and while this might have tested o.k. with a simple installation, once multiple components with slight HDMI variations were linked together the effect was cumulative.&nbsp; That's why a more complex set up had a greater chance of not working with 100% reliability. This would be a good place to mention that the recently introduced HDMI 1.3a standard has started to address this issue.&nbsp; The "a" refers to the fact that it contains a "compliance test specification." In other words - it plays by the HDMI rules. This is not to suggest that if your HDMI equipment isn't version 1.3a that it won't work. There are a lot of products out there, going all the way back to HDMI 1.0 that work just fine within the parameters of each version of HDMI (see the reference materials for details). It's just that monitoring the HDMI situation was not a major focus in the early days so it was a hit and miss situation.</p> <h2>Part Two: The most important advice in this entire article </h2> <p>Pay attention to what I'm about to say.&nbsp; If you take away anything from these pages it should be the following:  <p><b><i>As you put together your HDMI equipped system, make sure that any switches, repeaters and other connectors that you purchase come with a money back guarantee. This is an example of the old "try and buy" philosophy.</i></b>  <p>I can't stress how important this is (unless you have money to burn.)&nbsp; All the theory in the world doesn't replace real world experience. You can't tell whether a piece of equipment will work with another piece (or pieces) just by reading about it and examining the specifications.&nbsp; Variations among equipment, as well as the cumulative effects mentioned earlier, require that the only real test is trying it out.&nbsp; You can increase the odds of success by sticking with "reliable" brands - but that's a somewhat moving target and often hard to gauge.&nbsp; Bring it home, hook it up and try it out. If it works, great! If not, you've lost nothing but a bit of your time.&nbsp; There are so many variables in the HDMI world that this is the only sure way to get an answer. Certification can help in the process but can't guarantee it in all installations.</p> <p>Read that second paragraph of this section again.</p> <h2>Part Three:&nbsp; So what makes me such a know-it-all?</h2> <p>Well, in the first place - I'm not.&nbsp; But I do have a lot of experience (over 50 years working with and building electronics equipment) and I've lived through complex HDMI scenarios.&nbsp; My current equipment includes four HDMI sources (Digital HD Satellite, HD players and gaming devices) distributed to two displays (a front projector and a rear projection monitor).&nbsp; The brands aren't important at this point but you can probably find details at <a href="http://www.rfowkes.com/">my Home Theater web site</a> if you're interested - and if the descriptions are current.&nbsp; What is important is that my system is a true HDMI obstacle course. I've lived through all manner of incompatibilities, loss of video, "poltergeist" screens, and eventual success in navigating the sometimes hazardous waters of HDMI.&nbsp; But the sea has now calmed and I want to share some of my experiences.</p> <p>In the second place - I have some friends in high places.&nbsp; In this case it comes in the form of <b>Jano Banks.&nbsp; </b>Jano is a true HDMI expert as well as he should be since his name is the second one listed on the HDMI patent.&nbsp; I had the good fortune to make his acquaintance several years ago at CEDIA and we have been in touch ever since.&nbsp; If I have a question about HDMI I literally go to the source.&nbsp; So what you are reading here is a combination of informed personal experience (mine) and some expertise via good people like Jano.</p> <p>So, with that in mind let's get down to some specifics.</p> <h2>A. HDMI Cables </h2> <p><strong><u><img title="121777-2309p111-2b" height="250" alt="121777-2309p111-2b" src="http://www.rfowkes.com/assets/images/121777-2309p111-2b.jpg" width="238" border="0"> </u></strong> <p>Tread carefully here, gang.&nbsp; There are sharks in these waters! One of the advantage of digital signals is that they resolve themselves into a series of zeros and ones (0 &amp; 1).&nbsp; Granted, that's probably a slightly watered down version of the real story but it does point out that a lot of the real (and the mythical) factors involving analog signals (magnetic fields, shielding, interference, etc.) really don't apply&nbsp; to digital signals - or at least not to the extent that they affect their continuous brethren. Yes, there might be some cable length issues and even some bandwidth issues as the digital signals get faster and pack the data more closely but essentially - for 99+% of A/V users these are theoretical limitations rather than real issues. In layman's terms: As long as the cable is capable of distinguishing between the HIGH ("On") state and the LOW ("Off") state of a digital transmission and passing that information through it has done its job.&nbsp; Or to put it another way: If you are living in a world of mostly 3' to 10-15' cables then any well constructed HDMI cable will do a fine job.&nbsp; That's heresy to sellers of $100+ HDMI cables when a $10 cable will perform as well in real world applications.&nbsp; But it's also a fact. The next time you purchase a new display and the salesman tries to sell you a $100 (or more!) HDMI cable when you can order a perfectly good (well made, good connectors) cable for about a tenth the cost ask him if he'll take it back and refund your money if you're not satisfied. Better still - don't waste your money in the first place. If I had a dollar for every time a sales person has claimed that the more expensive cable "will definitely make a noticeable difference" I'd have all my HT gear paid for - and I own a lot!&nbsp; Don't believe me? Try it out for yourself.&nbsp; Some cables have a 90% mark-up so they are important in an industry where margins on displays are very slim thanks to the competition.</p> <p>The bottom line with HDMI cables is that as long as you are talking about normal lengths (under 15') then don't waste your money on the expensive spread. More important is that the connectors are secure so that the cable won't work its way loose.&nbsp; That said, if you are going to traverse long distances (like from an A/V Receiver or pre/pro to a front projector) then distance might play a small role. The problem is that even digital signals will weaken over distances and if the threshold between the "On" and "Off" state gets too close the equipment might no longer be able to distinguish between the "ones" and the "zeros."&nbsp; Not to worry. There's a little device known as a <b>repeater </b>that comes to the rescue. By definition,<b> <i>a repeater is an </i>electronic<i> device that receives a </i>signal<i> and </i>retransmits<i> it at a higher level or higher power, or onto the other side of an obstruction, so that the signal can cover longer distances without degradation.</i></b> That says it all.&nbsp; If you a dealing with front projection length HDMI cables be sure there is a repeater involved (usually it's built into the jack at one end.) Works like a champ.&nbsp; I currently use a 60 foot Ultrarun cable from ACCELL with absolutely no problems (feeding a 1080p signal from a DVDO VP-50 video processor to the 1080p input of a JVC DLA-RS1 FP for those who want specifics.) This won't apply for the majority of home installations.</p> <p>At CEDIA this year some of the cable industry folks (who will remain nameless) fought back with claims of "speed ratings" for HDMI cables. The argument goes like this:&nbsp; As higher and higher data rates for digital signals are introduced to deliver deeper color and higher resolution sound a point will be reached in the future where today's HDMI cables are not up to the task.&nbsp; One should always look to the future and not buy "inferior" cables with low data transmission rates because you might miss some of the audio and video nuances. They leave out some important considerations.&nbsp; In the first place no test standards have yet been agreed on and a lot of those that are being proposed have been created by the cable industry itself. Is this a case of the fox minding the hen house?&nbsp; Time will tell. Secondly, while data rates will undoubtedly go up in the future I've yet to see any valid results that prove that today's "regular" HDMI cables aren't up to the task with today's signals. Even artificially produced high rate signals will be passed by any well constructed HDMI cable (the low two figure priced kind) and one's ears can't tell the difference. Any differences that have been claimed by the industry only show up on some instrumentation traces and the last time I looked most of us watch our content on TV displays and not oscilloscopes.&nbsp; Seriously, until some of these claims can be converted into real world experiences I wouldn't concern myself too much about the "speed rating" of an HDMI cable. And by the time that the sources have advanced to the point where data transmission speeds become a meaningful factor your ten dollar cable will have provided you with years of faithful service.</p> <p>One last thing about HDMI cables and HDMI versions.&nbsp; All quality cables will handle HDMI 1.1, 1.2, 1.2a. 1.3, 1.3a, etc. You don't need to buy an HDMI 1.3a cable to transmit a 1.3a signal.&nbsp; In fact there's no such animal according to HDMI specifications.&nbsp; The cables are passive (except for those with built-in repeaters). HDMI cables are much simpler to deal with than a lot of people think.&nbsp; Don't overspend and make sure to look for ones that connect as snuggly as possible.&nbsp; All the rest is smoke and mirrors as far as I'm concerned.<br></p> <h2>B. Magic Boxes</h2> <p>We now turn our attention to the HDMI boxes that many people require to make all their connections.&nbsp; In an ideal world everyone would own an A/V Receiver or pre/pro that has all the HDMI inputs needed for all HDMI sources and one or two extra for good measure.&nbsp; All the inputs would be of the HDMI 1.3a (or beyond) variety so they could handle undecoded HD audio signals, deep color and all the other advanced technologies. (Refer to the earlier references I cited if you want to learn more about all these parameters). But this is the real world and a lot of people have equipment that needs additional HDMI inputs to serve all their needs.&nbsp; Or, perhaps, they want to feed the HDMI output of their receiver or pre/pro to more than one display. And that's where connection boxes enter the picture - as they have for years in the A/V world.&nbsp; But this time it's a little different.</p> <p>For one thing, remember that HDMI is a two-way street with each component in the chain talking in both directions to keep the HDMI connection viable. Therefore it becomes important that the connection devices play by the HDMI rules or you are inviting trouble.&nbsp; One of the first signs that there might be a problem occurs if the box you are considering does not have its own power supply.&nbsp; One of the HDMI specifications clearly states that no devices should get their power from the HDMI signal itself because that leads to the possibility of a compromised signal. So here are a couple of observations from my experience.</p> <p><b>Observation #1:</b> If a "box" you are considering doesn't have its own power supply then avoid it. Not necessarily like the plague because some of these units actually work from time to time but because you may recall that I said that effects in the HDMI chain are cumulative.&nbsp; If each device contributes a little bit of non-standard performance then the end result might be a big problem.</p> <p><b>Observation #2:</b> Start with the least expensive solution and work your way up. This assumes that you already understand the "try before you buy" concept and will work with that in mind. In many cases a simple (inexpensive) solution will do the trick. But in other cases (more complex installations) you may have to go for a product that costs a little more money because it has some features that will make everything work properly. A little planning in this area will keep you from overspending unless you expect to expand your system. And at the same time, spending too little might result in equipment that doesn't function properly in the HDMI world.</p> <p><b>Observation #3:&nbsp; </b>When you are considering HDMI "boxes" make sure that the specifications allow for audio as well as video pass through if you intend to use the speakers in your display. If you are feeding the audio into a receiver or a pre/pro prior to the switch then this is not a consideration.</p> <p><b>Observation #4:&nbsp; </b>Make sure the HDMI device is HDCP compliant. Many components will not perform property if the copy protection protocol isn't handled properly.&nbsp; Better safe than sorry.</p> <p><b>Observation #5:</b> If you are going to "cheat" a bit you are probably safer when going from multiple HDMI inputs to a single output rather than the other way around. 2x1 units (two inputs, one output) are more forgiving than 1x2 units (one input, two outputs) in my experience.&nbsp; Specifically, I've generally had some luck with an unpowered 2x1 switch but no luck at all with an unpowered 1x2 switch.&nbsp; See the specific details below.</p> <p>Over the past year or so I've had the opportunity to try out a variety of HDMI connection boxes at both ends of the price spectrum. What follows are some specific examples of my experiences. I hope that they offer you a bit of insight into the task of connecting a rather comprehensive HDMI system together.</p> <p>My first foray into HDMI connectivity was to purchase a 2x2 HDMI/HDCP switch from Gefen. My main purpose was to take the HDMI output from my DVDO Video Processor and feed one of two monitors in my home theater - either my (at the time) 720p Runco CL-710 DLP FP or my 1080p HP MD5880n DLP RPM. The reason I purchased a 2x2 switch rather than just a 1x2 unit was because I wanted to have some flexibility for possible future expansion.&nbsp; The switch was not inexpensive (about $350). However this task was not as easy as I thought. For one thing the Gefen switch was unreliable. Sometimes I would get an image and other times I would not.&nbsp; After several calls to Gefen I was supplied with a replacement unit which didn't really work any better than the first unit. At the time CEDIA 2006 was approaching so I thought I might get some answers there.&nbsp; Fortunately I ran into the aforementioned Jano Banks and he pointed out to me that a lot of the early HDMI devices didn't work because there were some inconsistencies in the implementation regarding adhering to HDMI specifications.&nbsp; And after some time at the Gefen booth I discovered that there had been several firmware upgrades to my 2x2 unit and I was given a name to call when I returned home to work the problem out. Unfortunately, many calls later the issue was never resolved and I have to feel that my unit was built at a time when very little was known (or followed) regarding HDMI.</p> <p>As it happens, back at CEDIA 2006 I stopped by the booth of a company called <b>ACCELL </b>where I saw a 2x1 switch.&nbsp; When I mentioned that I was really looking for something that would give me a 1x2 configuration so that I could drive one of two displays I was assured by representatives from that company that this was possible by merely turning the switch around. I was given a sample of their $99 "UltraAV HDMI Audio/Video Switch" to try out at home. The only thing that really concerned me from the outset was that this switch did not supply its own power and drew power from the HDMI line itself. Jano warned me that this could be a problem because the switch was not adhering to HDMI guidelines.&nbsp; He suggested that while it might work in a 2x1 configuration it would run into problems if used in a 1x2 configuration.&nbsp; And he was right.&nbsp; The ACCELL switch was able to switch between two incoming signals (as pictured below) when output to a single monitor but turning it around was a disaster. </p> <p><img title="switch_appstory" height="205" alt="switch_appstory" src="http://www.rfowkes.com/assets/images/switch_appstory.jpg" width="515" border="0">  <p>Over an extended period of time I discovered that if I used the above switch to provide a signal to two displays it was extremely unpredictable. If I managed to get a picture to TV1 the video would disappear when I switched to TV2. In fact the only way to get a signal to TV2 would be to physically unplug power from the source component (the DVDO VP) and then cycle it back on. And if I then tried to switch back to TV1 I had to repeat the entire process again.&nbsp; Clearly not a solution but an impediment.&nbsp; Jano explained to me that when dealing with multiple display outputs it was important that the box constantly refresh each of the outputs to look for any changes in the HDMI signal.&nbsp; Without this process in place it was inevitable that the HDMI signal would be dropped.&nbsp; What I was doing, in effect, by unplugging power from the source was forcing the ACCELL unit to re-poll the inputs manually.&nbsp; He suggested that a proper unit for multiple HDMI outputs (a) had to have its own power supply and (b) should really be a <b>repeater </b>(as mentioned earlier) constantly retransmitting a reconstructed HDMI signal while monitoring which outputs contains a live display.&nbsp; The 2x1 ACCELL unit clearly was not in that category and that's why it could not function as a 1x2 device.&nbsp; As a 2x1 device it was adequate although I still question the lack of its own power source in the long run. It worked, but it wasn't being asked to provide multiple outputs.</p> <p>As a postscript to the above scenario, at CEDIA 2007 ACCELL introduced their brand new "UltraAV HDMI 1-2 Splitter ($129). And lo and behold, it contains its own source of power! Here's what it looks like (power brick not shown) </p> <p><img title="anglesplitter" height="225" alt="anglesplitter" hspace="0" src="http://www.rfowkes.com/assets/images/anglesplitter.jpg" width="225" align="top" border="0">  <p>And here's what it looks like in a connected state:  <p><img title="splitter_appstory" height="313" alt="splitter_appstory" hspace="0" src="http://www.rfowkes.com/assets/images/splitter_appstory.jpg" width="557" align="top" border="0">  <p>It's actually quite a compact unit, weighing only 2 ounces and measuring 2.75" square x a little over .5" high. I ran it through a series of torture tests, feeding it a variety of HDMI signals and turning the various sources and displays on and off multiple times each. This little unit was able to feed both displays simultaneously without dropping a signal. One thing that should be noted (and which is true of simultaneous output from any repeater) is that if more than one resolution display is present the output will default to the lower resolution. i.e. if one display is 720p and the other is 1080p then the limitation for simultaneous display will be 720p.&nbsp; This did not affect me because I recently upgraded my 720p Runco FP to a 1080p JVC DLA-RS1 FP. I suspect that the circuitry of this new 1x2 ACCELL repeater conforms much more closely to HDMI guidelines and the results bear this out.&nbsp; A very nice solution if you have a dual display HDMI requirement.</p> <p>If you are looking for multiple input HDMI switchers I would still stick with powered units as I don't trust ones that draw power from the HDMI line itself. Even ACCELL seems to acknowledge the situation as they have recently introduced a 4x1 <i>powered HDMI switch as pictured below.</i> </p> <p><img title="4X1SwitchFeatures" height="226" alt="4X1SwitchFeatures" hspace="0" src="http://www.rfowkes.com/assets/images/4X1SwitchFeatures.jpg" width="496" align="top" border="0">  <p>And ACCELL is by no means the only provider of multiple input HDMI devices.&nbsp; You can shop around at various web site providers and come up with a wide variety of such switches in all price ranges. Just make sure you specify "powered" and can return the unit if it doesn't do the job for you.</p> <p>At the high end of the scale are switches and repeaters offered by <b>Radiient Technologies </b>- a company that Jano Banks co-founded.&nbsp; They offer a range of products from 4x1 switches/repeaters (both a consumer and a pro model) to a multiple output repeater which can handle up to 6 simultaneous outputs. </p> <p><img title="mediaselect4ce" height="212" alt="mediaselect4ce" hspace="0" src="http://www.rfowkes.com/assets/images/mediaselect4ce.jpg" width="523" align="top" border="0">  <p><b>Radiient Select-4ce&nbsp; 4x1&nbsp;&nbsp;&nbsp; $149 (consumer edition)</b>  <p><img title="mediaselect4" height="247" alt="mediaselect4" hspace="0" src="http://www.rfowkes.com/assets/images/mediaselect4.jpg" width="518" align="top" border="0">  <p><b>Radiient Select-4&nbsp;&nbsp;&nbsp; 4x1&nbsp;&nbsp;&nbsp;&nbsp; $249</b>  <p><img title="media" height="189" alt="media" hspace="0" src="http://www.rfowkes.com/assets/images/media.jpg" width="555" align="top" border="0">  <p><b>Radiient Repeat-6&nbsp; 1x6&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $699</b>  <p>I had the privilege of beta-testing the Repeat-6 and lived through the long process of upgrading the firmware and the hardware until Radiient finalized the product.&nbsp; This process gave me a new appreciation for all the details that have to be ironed out to assure consistency in the HDMI environment.&nbsp; There's a lot going on behind the scenes, electronically, to produce the pictures and sounds via this relatively new delivery system.</p> <p>All Radiient products are well designed and well made and it shows in the final products and their performance.&nbsp; I'm not suggesting that everyone needs a repeating unit that's capable of six simultaneous HDMI signal outputs and the flexibility of the Repeat-6, while extensive, is probably beyond the requirements of most home systems. However, the Radiient Select-4 series offer a variety of options and prices for all switching needs. And the design is such that these devices can handle just about every HDMI situation that might occur with such a wide range of HDMI components out there - with new ones arriving every day. Considering the genesis of this company I wouldn't expect any less.</p> <p>The above experiences merely accentuate the need to try out HDMI solutions in your own system to see if all the pieces work together nicely.&nbsp; I've provided a glimpse at both "budget" and a "affordable luxury" options and I don't mean to suggest that these are the only two alternatives. Shop around a bit, examine the specifications and, most importantly, try the unit of your choice out with your equipment in your location.&nbsp; There is no substitute for that. </p> <p>As a starting point here are the URLs for the products mentioned above:  <p><b>ACCELL: </b><a href="http://www.accellcables.com/index.html">http://www.accellcables.com/index.html</a>  <p><b>RADIIENT:&nbsp; </b><a href="http://www.radiient.com/">http://www.radiient.com/</a>  <p>You may find that an inexpensive, but well designed unit will satisfy your needs.&nbsp; Or, if you have a slightly more complex system (or a maverick component or two) you may require a slightly more sophisticated HDMI option.<br> <h2>Some Final Thoughts</h2> <p>Clearly, in the era of HDMI it's clearly not your father's A/V system. In fact, it isn't even <i>my </i>earlier systems. Screens will flash. Components will take a second or so to communicate with each other (handshake). The important thing is that they eventually do.&nbsp; It's a slightly different connection paradigm than in the analog days but there's so much more going on.&nbsp; As you add and subtract HDMI products to your system the goal is for each part to understand what it's connected to and what is expected of it. It seems a bit more complicated at first, but the objective is to make everything simpler in the end.&nbsp; Once people get used to this new connectivity and the way it interacts it will be easier to accept. In the computer world "Plug and Play" has become the norm and HDMI is really, to look at it one way, an extension of that idea.&nbsp; After all, the majority of home theater components are actually computers themselves and in a digital world where things are taken literally, it's important that standards be adhered to if everything is going to work in concert.  <p><b>Happy Handshaking!</b></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Robert A. Fowkes</b>, <b>February 15, 2008  9:03 AM</b>
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
			<?=getComments(1241)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Robert A. Fowkes', 1241)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Robert A. Fowkes</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/02/the-wonderful-and-sometimes-confusing-world-of-hdmi-connections.php" type="text/javascript" charset="utf-8"></script>
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