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
		AND e.entry_id = 3757";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3757 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3757 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3757";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3757";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)" />
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
	<title>HDTV Magazine - DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)</title>
	<meta name="keywords" content="possible note, dtv system, yes yes, technical aspects, decade atsc, standard, isdb, atsc, dtv, world, part, argentina, broadcasting, dvb, system, mpeg, note, digital, reception, brazil, technical, countries, yes, series, dibeg" />
	<meta name="description" content="This is a series of articles about how terrestrial broadcast digital TV is being implemented around the world. In this first part in the series, I offer an overall view. Part 2 will cover an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years. Parts 3 and 4 will cover the technical aspects of that system, which is a better choice for Argentina and several other countries than the US system, although the selection was not technically guided.

Most of the rest of the world is not adopting DTV’s ATSC standard..." />
	<meta name="title" content="DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This is a series of articles about how terrestrial broadcast digital TV is being implemented around the world. In this first part in the series, I offer an overall view. Part 2 will cover an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years. Parts 3 and 4 will cover the technical aspects of that system, which is a better choice for Argentina and several other countries than the US system, although the selection was not technically guided.

Most of the rest of the world is not adopting DTV’s ATSC standard..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3757', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php">DTV Around the World - The US DTV System, Not so Popular Around the World (Part 1)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>May 26, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=328&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
				<div class="editorial">This article is the first in the "DTV around the World" series. Other articles in this series are:<br> <ul> <li><a href="/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php" target="_blank">Part 2: Why Argentina selected ISDB-T after testing US&rsquo;s ATSC</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php" target="_blank">Part 3: How ISDB-T is being Implemented as DTV in Argentina</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php" target="_blank">Part 4: Argentina&rsquo;s DTV System &ndash; Technical Aspects</a> </li></ul></div><p>This is a series of articles about how terrestrial broadcast <a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29">digital TV</a> is being implemented around the world. <p>In this first part in the series, I offer an overall view. Part 2 will cover an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years. Parts 3 and 4 will cover the technical aspects of that system, which is a better choice for Argentina and several other countries than the US system, although the selection was not technically guided. <p><b></b> <h2>No to ATSC</h2> <p>Most of the rest of the world is not adopting DTV’s <a href="http://www.hdtvmagazine.com/glossary.php#ATSC">ATSC</a> standard implemented in the US in 1998, except for Canada, Mexico and Korea, which already launched it (El Salvador and Honduras are said to have adopted it as well). <p>China is implementing their DMB-T/H terrestrial broadcasting digital TV standard and deploying it in the PRC and Hong Kong. <p>Dozens of countries in Europe, Asia, and Central and South America have launched or adopted the European (DVB-T) or the Japanese (<a href="http://www.dibeg.org/index.html">ISDB-T</a>) DTV standard, some introduced their own modifications (such as using MPEG-4 instead of <a href="http://www.hdtvmagazine.com/glossary.php#MPEG-2">MPEG-2</a>), some adopted for political and trade reasons, and some for technical reasons, such as the ability of mobile/portable broadcasting within the same standard, not like the US did in 2009 after a decade of ATSC. <p>After following our considerable effort since the 1980s for developing and implementing<b> </b>ATSC, witnessing other countries not selecting it for political reasons is one thing but not doing it for technical reasons is disappointing, however, that partly relates to newer technology developments, such as the introduction of a more efficient compression method (MPEG-4 AVC) over our choice of MPEG-2 in the nineties. <p>In the US a decade of ATSC sold almost 150 million HDTVs integrated with MPEG-2 tuners for terrestrial broadcast, changing the compression standard to MPEG-4 now would make all those HDTVs incompatible and force TV owners to obtain external MPEG-4 set-top-box tuners, certainly an unpopular decision. However, if a country is just in the process of selecting a standard and the inventory of HDTVs in the public is minimal the choice is easier to make. <p>This standard mix reminds me of the history of the various analog TV systems implemented in the world in the last decades of the 1900s, with <a href="http://www.hdtvmagazine.com/glossary.php#NTSC">NTSC</a>, SECAM, PAL-N, PAL-M, etc.  <p><b></b> <h2><a href="http://www.dibeg.org/aboutus/maine.htm">DiBEG</a> (Digital Broadcasting Experts Group)</h2> <p>This organization was founded on September 1997 to promote all over the world the ISDB-T standard (Digital Terrestrial Television Broadcasting System) launched in Japan and other countries. <p>To achieve its main task, DiBEG promotes the exchange of technical information and the international cooperation to facilitate the common understanding of the standard in the world and the smooth exchange of programs in the digital era. <p>DiBEG members are basically the main broadcasters and the manufacturers working on the broadcasting business, besides associations related to the broadcasting industry in Japan. <p>Below is a chart that highlights <a href="http://www.dibeg.org/world/world.htm">what the world</a> is doing regarding digital TV (source DiBEG): <p><a href="http://www.dibeg.org/world/world.htm"><img title="DiBEG Map" alt="DiBEG Map" src="http://www.hdtvmagazine.us/articles/images/DiBEG-map.png" width="650" height="386"></a> <p style="clear:both">Below is a brief comparison of the service aspects of the 3 broadcasting systems mentioned in the chart above (source DiBEG): <table class="simple"> <tbody> <tr class="header"> <td> <p><b>Item</b></p></td> <td> <p><b>ATSC</b></p></td> <td> <p><b>DVB-T</b></p></td> <td> <p><b>ISDB-T</b></p></td></tr> <tr> <td valign="top"> <p>HDTV / SDTV fixed reception </p></td> <td valign="top"> <p>Yes </p></td> <td valign="top"> <p>Yes </p></td> <td valign="top"> <p>Yes </p></td></tr> <tr> <td valign="top"> <p>Data Broadcasting </p></td> <td valign="top"> <p>Possible (note 1) </p></td> <td valign="top"> <p>Possible (note 1) </p></td> <td valign="top"> <p>In service </p></td></tr> <tr> <td valign="top"> <p>SFN (Single Frequency Network) </p></td> <td valign="top"> <p>No </p></td> <td valign="top"> <p>Yes </p></td> <td valign="top"> <p>Yes </p></td></tr> <tr> <td valign="top"> <p>HDTV mobile reception </p></td> <td valign="top"> <p>Impossible </p></td> <td valign="top"> <p>Impossible (note 2) </p></td> <td valign="top"> <p>Good </p></td></tr> <tr> <td valign="top"> <p>Portable reception by Cellular phone </p></td> <td valign="top"> <p>Impossible </p></td> <td valign="top"> <p>Possible (note 3) </p></td> <td valign="top"> <p>Good </p></td></tr> <tr> <td valign="top"> <p>Internet access </p></td> <td valign="top"> <p>No good </p></td> <td valign="top"> <p>Possible </p></td> <td valign="top"> <p>Good </p></td></tr></tbody></table> <p>(Note 1) For ATSC and DVB-T, actual commercial service is not popular. <br>(Note 2) For DVB-T, SDTV mobile reception is possible.<br>(Note 3) In case of DVB-T, another frequency should be required for portable reception service. <p>This <a href="http://www.dibeg.org/techp/feature/features_of_isdb-t.htm"><b>ISDB-T report</b></a> describes some of the advantages ISDB-T has over other systems, such as, "segmented OFDM Transmission" and "Time interleave".  <h2>Standard Selection - What Some of the World Experts Say?</h2> <p>(<i>Quotes sourced from “TV Technology” magazine last year</i>): <p>According to Robert Graves, Chairman of the ATSC: <i>“Fourteen years ago, the ATSC set the goal of seeing a common standard being accepted across the Americas. It hasn't worked out that way, we now have three standards in the region; four, if the Chinese succeed in getting into the market as well”</i>.  <p>And he added "<i>Back in 2006, Japan promised to build an IC plant in Brazil if they chose the ISDB-T standard. Three years later, there is no Japanese IC factory in Brazil and no plans to build one. But the country is now committed to its own version of ISDB-T—namely SBTDV-T—and is trying to sell it internationally".</i> <p>According to Guillermo Wichmann, speaker of Argentina's DVB Coalition: <i>"Each country is selecting what better suits their objectives, investment, employment, financing and cooperation commitment of the industries supporting each standard, as well as the payment of intellectual property rights royalties to the owners of each HDTV standard".</i> <p>And he added “<i>DVB in Colombia is going faster than ISDB-T in Brazil or ATSC in Mexico, transmission and set-top box costs are other major factors affecting the DTV roll-out. Other countries still have to make their standard decisions, assign new digital frequencies and plan their analog switchovers".</i> <p>Argentina finally selected ISDB-T, is doing testing in 2010, and expects to launch the system shortly this year; more on this in part-2 of this series. <p>Peter Siebert, executive director of the DVB Project, agreed with ATSC’s Graves: <i>"Political considerations often play a strong role in the decision process and sometimes outweigh the technical and commercial advantages of a particular standard".</i> <p>Regarding how political is the ISDB-T standard choice made by Brazil, Alvaro Gutierrez, an executive with the DTV equipment manufacturer SIDSA, a Madrid, Spain-based developer of DVB-based technology, said <i>"Brazil has a new standard and it is working pretty bad, but Lula Da Silva is pushing very hard politically to its neighbor countries to choose ISDB-T. So Brazil could export the technology in the region. But it's clearly a bad standard for the region; very expensive."</i> <p>Regarding the importance of low cost of receivers for selecting a standard, Raj Karamchedu, director of product marketing of Legend Silicon Corporation in California, who helped develop China’s DMB-T/H standard, commented:  <p>"<i>There are 380 million television households in China alone, and the government there has set 2015 as the analog cutoff. Add </i>[to that]<i> the ability of this standard to support mobile DTV reception in moving buses—which is very big in China— </i>[and]<i> the potential for mass producing this technology in cost-saving volumes is very high."<br></i> <p>Stay tuned for part 2, the implementation of ISDB-T in Argentina.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>May 26, 2010  8:34 AM</b>
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
			<?=getComments(3757)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3757)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php" type="text/javascript" charset="utf-8"></script>
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