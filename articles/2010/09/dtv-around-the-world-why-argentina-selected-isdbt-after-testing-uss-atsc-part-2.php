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
		AND e.entry_id = 3944";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3944 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3944 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3944";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3944";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV Around the World - Why Argentina Selected ISDB-T after Testing US&rsquo;s ATSC (Part 2)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV Around the World - Why Argentina Selected ISDB-T after Testing US&rsquo;s ATSC (Part 2)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV Around the World - Why Argentina Selected ISDB-T after Testing US&rsquo;s ATSC (Part 2)" />
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
	<title>HDTV Magazine - DTV Around the World - Why Argentina Selected ISDB-T after Testing US&rsquo;s ATSC (Part 2)</title>
	<meta name="keywords" content="digital television, dtv system, system digital, new digital, japanese isdb, digital, system, standard, argentina, analog, isdb, cable, government, dtv, channels, part, implemented, technical, terrestrial, new, selected, public, television, implementation, stbs" />
	<meta name="description" content="This series of articles is about how terrestrial broadcast digital TV is being implemented around the world.

Part 1 offered an overall view. This part 2 covers an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years regarding how the standard was selected. Parts 3 and 4 will cover the technical aspects of that system, a better choice for Argentina and several other countries that use systems that differ from the US system, although the selection was not technically guided.

Mr. Victor Acuña..." />
	<meta name="title" content="DTV Around the World - Why Argentina Selected ISDB-T after Testing US&amp;rsquo;s ATSC (Part 2)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV Around the World - Why Argentina Selected ISDB-T after Testing US&amp;rsquo;s ATSC (Part 2)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This series of articles is about how terrestrial broadcast digital TV is being implemented around the world.

Part 1 offered an overall view. This part 2 covers an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years regarding how the standard was selected. Parts 3 and 4 will cover the technical aspects of that system, a better choice for Argentina and several other countries that use systems that differ from the US system, although the selection was not technically guided.

Mr. Victor Acuña..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3944', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php">DTV Around the World - Why Argentina Selected ISDB-T after Testing US&rsquo;s ATSC (Part 2)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September  9, 2010</b>
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
				<div class="editorial">This article is the second in the "DTV around the World" series. Other articles in this series are:<br> <ul> <li><a href="/articles/2010/05/dtv-around-the-world-the-us-dtv-system-not-so-popular-around-the-world-part-1.php" target="_blank">Part 1: The US DTV System, Not so Popular Around the World</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-how-isdbt-is-being-implemented-as-dtv-in-argentina-part-3.php" target="_blank">Part 3: How ISDB-T is being Implemented as DTV in Argentina</a> </li><li><a href="/articles/2010/09/dtv-around-the-world-argentinas-dtv-system-technical-aspects-part-4.php" target="_blank">Part 4: Argentina&rsquo;s DTV System &ndash; Technical Aspects</a> </li></ul></div><p>This series of articles is about how terrestrial broadcast <a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29">digital TV</a> is being implemented around the world.  <p>Part 1 offered an overall view. This part 2 covers an interview with a colleague TV engineer that is currently testing the new DTV system in Argentina (ISDB-T), with whom I collaborated over the past few years regarding how the standard was selected. Parts 3 and 4 will cover the technical aspects of that system, a better choice for Argentina and several other countries that use systems that differ from the US system, although the selection was not technically guided.  <p><p><b>Mr. Victor Acuña, please indicate your role on the implementation of digital TV in Argentina.</b><p><span class="caption left"><img alt="Mr. Victor Acuña met with me in Buenos Aires" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldPart2WhyArgentinaselect_BA93/clip_image002_87f0ac3f-17e9-4e46-b14d-9097fcd8739c.jpg" width="259" height="245"><br />Mr. Victor Acuña met with me in Buenos Aires</span>I am an electronics engineer. I am working on the Committee of Electronics &amp; Telecommunications Technologies in the Argentine Centre of Engineers (CAI) as specialist in Digital Terrestrial Television. I am also a professor in the Institute of Superior Education in Broadcasting (ISER) and in the Universidad Abierta Interamericana (UAI).  <p>The challenge to go to digital TV with a very new system gives me the chance to work with state of the art technology.  <p><b>When did Argentina start thinking about implementing digital TV? </b> <p>In the 90’s, when digital television came to be a reality, the government made the decision to implement it in our country, selecting the <a href="http://www.hdtvmagazine.com/glossary.php#ATSC">ATSC</a> standard back then.<p><p><b>When is Argentina planning to do the switch from analog PAL-N to digital? </b><p><p>As common practice, it will be in ten years, and then, in 2019, we will have the analog cut-off.<p><p><b>To what extent are HDTV and Mobile DTV part of the digital TV implementation?</b><p><p>The implementation includes these formats because the selected standard ISDB-Tb, allows these three types of signals broadcasted simultaneously, a typical model for a 6 MHz TV channel is to have an HD signal, an SD sub-channel, and a mobile-portable signal. We are still studying the type of content each of those will have.<p><p><b>Please briefly describe how Argentina selected the digital TV standard. Was the standard selected solely on its technical merits as the best format for the country in 2010, rather than adopting the ATSC standard implemented in 1998 in the US?</b> <p><span class="caption right" style="width:259px"><img alt="The packaging with the liquid-cooler pipes and other parts/boxes, when they arrived to Canal 7" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldPart2WhyArgentinaselect_BA93/clip_image005_5c82da36-4d41-4051-9d34-2783b6d1f15a.jpg" width="257" height="193"><br />The packaging with the liquid-cooler pipes and other parts/boxes, when they arrived to Canal 7</span>The decision in 1998 was more political than technical, but while we selected the ATSC standard nothing else was done after the signatures where on paper. Many voices appealed the decision, and then it was left frozen. As you may know in 2002 our country suffered a great political and social collapse. Meanwhile both ATSC and DVB standards, conducted transmission tests.  <p><span class="caption left" style="width:272px"><img alt="Technicians in the transmission room installing the liquid-cooler system for the digital transmitters." src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldPart2WhyArgentinaselect_BA93/clip_image007_1528d444-b60d-4c12-b79f-746036748d94.jpg" width="270" height="202"><br />Technicians in the transmission room installing the liquid-cooler system for the digital transmitters.</span>At the end of 2008, under agreements with Brazil, the balance tipped toward the ISDB-Tb standard (The short for ISDB-T, the Brazilian version), which is based on the Japanese ISDB-T (Integrated System for Digital Broadcast, Terrestrial) standard, and is also known as SBTVD (Sistema Brasileiro de Televisão Digital - Brazilian System for Digital Television).  <p>In August of 2009, <a href="http://www.dibeg.org/seminar/0908Argentina_Adopted/Argentina_adopts_ISDB-T_0908.htm">the adoption</a> of the new digital TV standard <a href="http://www.casarosada.gov.ar/index.php?option=com_content&amp;task=view&amp;id=6341">was finally announced</a>. The technical improvements performed by the Brazilians over the original Japanese ISDB-T bring it as the most novel DTV system. Our SATVD (Argentinean System of Digital TV) is based in the SBTVD.  <p><b></b> <p><b>How will the analog-to-digital transition be executed and what steps has the Government taken to facilitate the transition, such as allocating parallel bandwidth for the digital versions of analog TV channels, and making available analog-to-digital converter set-top-boxes to the public to continue using their existing analog TVs, like the US did.</b>  <p>In Argentina the UHF channels are not free. They were used to transmit pay channels (wireless cable), but since broadcasting is their primary purpose, they are being released for the digital television transmissions.  <p>This year the government is working to fill 47 digital public stations around the country in two steps (first step with 21 transmitters and a second step with 26 transmitters). Four UHF channels were reserved (22, 23, 24 and 25) and a transponder in the AMC6 satellite will be used to transmit from Buenos Aires the generated signals to the provincially distributed retransmission stations.  <p><span class="caption right"><img alt="Other view of the digital transmitters" src="http://www.hdtvmagazine.us/articles/images/DTVaroundtheWorldPart2WhyArgentinaselect_BA93/clip_image012_8dc7383d-9c9e-40d5-b432-bc595eda5d52.jpg" width="244" height="202"><br />Other view of the digital transmitters</span>In Argentina there are 13 million of households with TV sets, with about 2 TV sets each, approximately 3 million of those households do not have cable TV or satellite TV, and those will be benefited because they can only view over-the-air channels. For low-income TV viewers more than a million digital-to-analog free STBs will be subsidized by Government.  <p>The transition for the main private stations has not started yet; they are out of the game because by not having UHF digital channels assigned, they cannot start to transmit their signals. However, this situation will change by this year.  <p><b></b> <p><b>Is the public expected to pay for set-top-boxes? </b> <p>Other than the subsidized converter boxes, yes; some are Japanese, Chinese, Taiwanese, Brazilian and national assembled STBs and Dongles. They will be available for sale and I expect them to cost about $100 USD for a basic model, and about $300 USD for a top unit. But prices are expected to drop following market introduction in the next months. Additionally, because many South American countries are implementing this standard, prices are expected to go down further.  <p><b></b> <p><b>Please describe the government subsidy for converter boxes? </b> <p>As I mentioned above, the government will implement a plan to distribute more than one million of subsidized free STBs for low-income people, so they can view digital TV content using their current analog TV during the digital transition. These STBs will come from national assemblers and some will be imported.  <p>The STBs are very complete; they are not basic STBs. These models will cost to the government about $120 USD, and they will be sold for about $180 USD to the public that does not qualify for free STB low income subsidy.  <p><b></b> <p><b>How does Argentina’s digital conversion parallel the efforts of other South America countries, such as Brazil, regarding the standard selection process and implementation plan?</b>  <p>There was no same parallel effort regarding the technical field. Other countries made public their comparative tests and reports, including the consultation to professionals councils. In Argentina the tests were done only by the government, and the technical papers utilized to select the standard were not published.  <p>By Decree # 1148 of August 31, 2009 the Argentine System of Terrestrial Digital Television (SATVD-T) was created, based in the ISDB-Tb standard, and formed the Assessor Council of the SATVD-T for its implementation. Later, the Decree #364/2010 gives a public interest to the National Platform of Terrestrial Digital Television, and determines the resources needed to implement the system nationwide.  <p>I think Brazil did the best political and strategic move for the southern hemisphere. Our national platform and implementation plan is like the Brazilian one.  <p>Argentina’s determination to implement the system was helped by foreign support without consultation with local experts. All the installations implemented in “Canal 7” were donated and help put in operation by the Japanese government. We are also working with “Canal 9” in doing only a transmission test (no digital commercial broadcast license yet).  <p>Recently the Association of Tele-broadcasting of Argentina (ATA) reiterated the request for digital TV channels licenses to private broadcasters agglutinated in the entity.  <p><b></b> <p><b>Is the Government planning to mandate new digital TVs to have integrated tuning capabilities for the new format? Is it working in retrofitting previously manufactured digital TVs to integrate such tuners?</b>  <p>This subject is left to the industry, of course the new TVs will come with the ISDB-T tuner, but meanwhile, as I said, the current digital TVs will need a STB to tune to the new digital standard. As far as I know, no government mandate is issued on this subject yet.<b></b>  <p><b></b> <p><b>How do the cable and satellite industry plan to implement their digital and HDTV services to compete with the new digital terrestrial broadcast standard? Do they plan to keep untouched the original compression and quality of the over-the-air HDTV signal?</b>  <p>The cable companies are doing a savage publicity about the “Digital Television” and “HD contents”. Although the terrestrial digital television is not officially on the streets yet, a series of discounts and offers are being offered to capitalize from the big sales of LCD TVs in the last months, including great payment plans of up to 50 months. <i>(Rodolfo comments: I can attest to that, on my last trip to Argentina this year I selected two panel purchases for others and very favorable discounts and payment plans were offered; however, after several days of research, I could not find one single plasma in the market; unfortunately, those interested in high quality imaging would never know what plasma can offer if not available, and have no other choice than to settle for LCD panel technology).</i>  <p>As I know the cable companies plan to repeat the broadcast digital signals as they are in the future, but it is uncertain if the quality of the “must-carry” image would be similar.  <p>The digital cable standard is not defined, but Cable operators use the <a href="http://www.hdtvmagazine.com/glossary.php#QAM+%28digital+cable+tuners%29">QAM</a> USA system and DOCSIS for Internet cable modems. Today, cable operators have the guide of all analog channels in PAL-N replicated in digital, and they have many HD premium channels in their digital guide as MGM, HBO, ESPN, etc. All the digital signals are encrypted as the premium channel, which means that if you have a DTV set with a QAM on-the-clear tuner you can’t tune to anything with it due to the encryption.  <p>Satellite services such as DirecTV already have HDTV within their subscription of approximately 170 channels. It is unknown how their HD image quality would compare with the quality of over-the-air HD broadcast when implemented.  <p>If you want to view analog free-to-air TV you need a VHF antenna connected to the TV set. If you want digital cable TV you need to connect a cable STB using the <a href="http://www.hdtvmagazine.com/glossary.php#Component+Video">component-analog</a> or <a href="http://www.hdtvmagazine.com/glossary.php#HDMI">HDMI</a> input. And now if you want to see digital terrestrial TV you need a second HDMI or component-analog input connected to the ISDB-T's STB and the <a href="http://www.hdtvmagazine.com/glossary.php#UHF+%28Ultra+High+Frequency%29">UHF</a> antenna.  <p><i>Rodolfo comments: the lack of integrated digital tuners forces panel purchases to meet the need for a sufficient number of component-analog or HDMI inputs, not to mention when the public starts to embrace Blu-ray with HDCP. Coincidentally, I was also recently involved in recommending to a Uruguayan friend that was visiting me in VA his first purchase of a Blu-ray player to connect to his RCA HD panel in Montevideo. As per part 1 you may remember that Uruguay adopted the European system DVB-T (implemented by Colombia). Geographically, Uruguay is neighbor with Brazil and Argentina, and both implemented the other format (Japanese ISDB-T). The analog TV story in South-America repeats itself decades later for digital, and the forces for adopting formats are not primarily technical. </i> <p>-----------------------------  <p>Thank you Victor for collaborating with this series of articles about DTV around the world. The next article (part 3) will cover how ISDB-T is being implemented as a system, stay tuned.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September  9, 2010  8:07 AM</b>
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
			<?=getComments(3944)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3944)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/09/dtv-around-the-world-why-argentina-selected-isdbt-after-testing-uss-atsc-part-2.php" type="text/javascript" charset="utf-8"></script>
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