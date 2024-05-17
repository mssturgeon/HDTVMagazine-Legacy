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
		AND e.entry_id = 4166";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4166 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4166 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4166";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-applications-plenty-buzz-ahhh-not-so-much.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4166";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&hellip;" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&hellip;" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&hellip;" />
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
	<title>HDTV Magazine - HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&hellip;</title>
	<meta name="keywords" content="blu ray, smart phone, smart phones, hulu plus, active shutter, smart, wireless, autostereo, ”, lcd, inch, tvs, ces, sony, new, digital, passive, show, –, tablet, dlp, blu, ray, year, remote" />
	<meta name="description" content="Attendance was way up at CES, but the overall mood was more subdued and businesslike than extravagant." />
	<meta name="title" content="HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&amp;hellip;" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&amp;hellip;" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-applications-plenty-buzz-ahhh-not-so-much.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Attendance was way up at CES, but the overall mood was more subdued and businesslike than extravagant." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4166', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-applications-plenty-buzz-ahhh-not-so-much.php">HDTV Expert - CES 2011: Applications? Plenty! Buzz? Ahhh, Not So Much&hellip;</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January 11, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=514&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <div id="attachment_963" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Samsung-Lead-Off-Image-1.jpg"><img class="size-full wp-image-963" title="Samsung Lead-Off Image 1" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Samsung-Lead-Off-Image-1.jpg" alt="" width="600" height="300" /></a><p class="wp-caption-text">How do you like THIS for a videowall?</p></div>
<p>If you still needed any convincing that the U.S. economy is on the rebound, the 30-minute-long cab line at McCarran Airport did the trick. Attendance at this year’s running of the world’s ultimate gadget expo was WAY up, probably hitting 2007 levels. (CES claimed 140,000 in attendance, but my guess is that the real number was more like 90,000 – 100,000, based on cab lines and traffic.)</p>
<p>But CES was a vastly different show than in recent years. True “wowza!” product demos were few and far between. Instead, what we saw were ‘apps’ – practical, real-world applications of technologies introduced in the past couple of years. (And of course, umpteen million tablet computers.)</p>
<p>Smart phones were huge this year, and they were doing everything from shooting videos to doubling as game controllers and even talking to ovens and refrigerators. The Android OS rules this space, with Windows coming up far behind. If there was a possible use for a smart phone, someone demonstrated it in a booth (including 3D).</p>
<div id="attachment_964" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-Autostereo-MH-3D-Demo-1-Image-2.jpg"><img class="size-full wp-image-964" title="LG Autostereo MH 3D Demo 1 Image 2" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-Autostereo-MH-3D-Demo-1-Image-2.jpg" alt="" width="600" height="414" /></a><p class="wp-caption-text">Ever expect to see 3D on an MH receiver? Neither did I.</p></div>
<div id="attachment_993" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Aiptek-Booth-Sign-H-Insert-2.jpg"><img class="size-full wp-image-993" title="Aiptek Booth Sign H Insert 2" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Aiptek-Booth-Sign-H-Insert-2.jpg" alt="" width="600" height="398" /></a><p class="wp-caption-text">A wonderful moment, indeed.</p></div>
<p>Discussions of “the cloud” were heard in every hallway. For those readers who don’t know what “the cloud” is, it’s the concept of storing and accessing media files from remote servers, streaming or downloading it to view on portable and desktop displays. Netflix streaming is a good example of “the cloud,” and many industry analysts believe “cloud” delivery of content is where everything is headed – no more big hard drives or optical disc readers, just fast wireless and wired Ethernet connections.</p>
<p>Speaking or wireless, it’s all the rage. I lost track of all the wireless connectivity demos, ranging from wireless USB 3.0 docking stations to full-bandwidth 1080p video and multi-channel audio streaming to TVs from Blu-ray players, using the 6 GHz radio frequency band.</p>
<p>And those tablet computers…they were everywhere, so many that tablets suffered the ignonimous fate of moving from the most anticipated new product at the opening of the show to “so what?” products by its closing. I saw just as many off-brand and white label tablets in the lower regions of the South Hall as I did at the Blackberry, ViewSonic, Samsung, Sharp, Sony, and Panasonic booths. Can you say, “buzz kill?”</p>
<div id="attachment_965" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/WHDI-enabled-Graphics-Card-with-Antennas-Image-3.jpg"><img class="size-full wp-image-965" title="WHDI-enabled Graphics Card with Antennas Image 3" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/WHDI-enabled-Graphics-Card-with-Antennas-Image-3.jpg" alt="" width="600" height="317" /></a><p class="wp-caption-text">That's a complete nVidia workstation graphics card, connected through 6 GHz wireless links.</p></div>
<div id="attachment_994" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Samsung-Central-Station-Demo-2-Insert-3.jpg"><img class="size-full wp-image-994" title="Samsung Central Station Demo 2 Insert 3" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Samsung-Central-Station-Demo-2-Insert-3.jpg" alt="" width="600" height="381" /></a><p class="wp-caption-text">How does a Wireless USB 3.0 docking station grab ya? Samsung's got it.</p></div>
<p>3D TRENDS</p>
<p>Last year’s show was dominated by 3D. You couldn’t get away from it! This year, the 3D pickings weren’t quite as abundant, although a few companies (Sony and Panasonic) continued to place a heavy emphasis on stereoscopic TV viewing in their booths.</p>
<p>Toshiba did too, except they chose to emphasize glasses-free (autostereo) 3D exclusively in their booth. LG opted to show passive 3D products that use inexpensive circular-polarization glasses, along with a single autostereo LCD TV. Meanwhile, Sony had concept demos of a portable 3D Blu-ray player and a 24-inch autostereo organic light-emitting diode (OLED) TV.</p>
<p>The reduced emphasis on 3D might have something to do with the paltry sales of active-shutter 3D TVs in 2010. Sales numbers were nowhere near what anyone predicted, which could partly be blamed on the recession. But it could also be blamed on a perception that there is a format war brewing in the world of 3D TV (shades of the 1080i vs. 720p battles from ten years ago).</p>
<div id="attachment_966" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Toshiba-AutoStereo-Notebook-V-Image-4.jpg"><img class="size-full wp-image-966" title="Toshiba AutoStereo Notebook V Image 4" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Toshiba-AutoStereo-Notebook-V-Image-4.jpg" alt="" width="600" height="607" /></a><p class="wp-caption-text">Toshiba's 15-inch prototype autostereo notebook display uses a built-in camera to adjust the 3D viewing angle to your position.</p></div>
<div id="attachment_991" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Polaroid-Anaglyph-TV-Demo-1-Image-Insert.jpg"><img class="size-full wp-image-991" title="Polaroid Anaglyph TV Demo 1 Image Insert" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Polaroid-Anaglyph-TV-Demo-1-Image-Insert.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">HELLO, 1958! Polaroid actually showed a blue-yellow anaglyph 3D demo at CES. (CAUTION: Don't watch Avatar this way...)</p></div>
<p>Toshiba’s recent announcements of glasses-free 3D TV certainly added to that perception, and that’s all they showed at CES. Meanwhile, LG and JVC seem to be leaning towards passive 3D (embedded polarizing filters) in their LCD TVs, and in fact LG had large baskets of passive 3D glasses available to both visitors.</p>
<p>The LG autostereo LCD TV worked about as well as the Toshiba models. As you change your viewing position, patterned film retarders (PFRs) built-in to the LCD surface create a new perspective and viewpoint, blocking some pixels and revealing others. It works, but you’ve seen the same effect before with static digital signage displays in retail stores and in airports. And it’s not easy to watch 3D video this way for very long.</p>
<p>There were plenty of autostereo handheld display demos. LG’s new Optimus smart phones were shown as game controllers for 3D gaming systems, but were also displaying mobile 3D content. Nearby, LG had a demonstration of autostereo 3D as broadcast from Las Vegas DTV station KLVX, using the MH mobile digital TV standard.</p>
<p>Sony showed an autostereo media player in its booth, along with the aforementioned portable Blu-ray player with autostereo screen. (Frankly, I think the market for portable BD players is pretty miniscule, but the autostereo images looked quite nice.)</p>
<div id="attachment_967" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sony-24-inch-Autostereo-OLED-2-Image-5.jpg"><img class="size-full wp-image-967" title="Sony 24-inch Autostereo OLED 2 Image 5" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sony-24-inch-Autostereo-OLED-2-Image-5.jpg" alt="" width="600" height="364" /></a><p class="wp-caption-text">Sony's 24.5-inch autostereo AM OLED was a show-stopper.</p></div>
<div id="attachment_968" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/JVC-65-inch-Passive-E-LED-3D-TV-Image-6.jpg"><img class="size-full wp-image-968" title="JVC 65-inch Passive E-LED 3D TV Image 6" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/JVC-65-inch-Passive-E-LED-3D-TV-Image-6.jpg" alt="" width="600" height="390" /></a><p class="wp-caption-text">JVC's got some skin in the 3D game with this 65-inch passive 3D LED LCD TV. </p></div>
<p>Sharp, who last year missed the boat on 3D – and whose U.S. market share in TV sales continues to drop precipitously – rolled out the 3D bandwagon this year, with a full line of Quattron 3D TVs out for inspection, including a new 70-inch model. Hidden away in another part of their booth were demos of 3.8” and 10.6” autostereo LCD displays for handheld devices.</p>
<p>JVC, who has been concentrating more on projection products lately, unveiled their first consumer passive 3D TV. It’s a 65-inch, edge-lit LED model with embedded micropolarizers that work with RealD theater glasses. Back in the Central hall, Hisense, Konka, and TCL all showed Chinese-made 3D sets with active shutter glass technology, while VIZIO threw its hat in with the passive 3D crowd, unveiling several models that use embedded polarizing filters and passive eyewear.</p>
<p>Hmmm…maybe there IS something to this 3D format war, after all…</p>
<p>NETWORKED TVS</p>
<p>It was hard to find a TV at CES that didn’t sport some sort of Internet connection. Panasonic (VieraCast), VIZIO (VIZIO Internet Apps), Sony (Google TV), LG (Smart TV), and Samsung (Samsung Apps) all had full plates of NeTVs out for inspection, along with numerous connected Blu-ray players. By the way, the ‘connected’ part of Blu-ray players is the big reason they are finally selling so well, as consumers apparently can’t get enough of YouTube and Netflix streaming.</p>
<p>There were also plenty of demos of smart phone control of TVs, using WiFi to stream back a lower-resolution version of the content being displayed on-screen. I’m not really sure why anyone would need that functionality, especially if they are already sitting in front of the TV watching whatever program or movie is playing out. Maybe it’s just in case you need to run to the bathroom?</p>
<div id="attachment_969" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-Internet-TV-Converter-Box-Image-7.jpg"><img class="size-full wp-image-969" title="LG Internet TV Converter Box Image 7" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-Internet-TV-Converter-Box-Image-7.jpg" alt="" width="600" height="319" /></a><p class="wp-caption-text">Don't have Internet connectivity on your plasma or LCD TV? LG's got the fix.</p></div>
<div id="attachment_970" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sony-Google-TV-Remote-CU-Image-8.jpg"><img class="size-full wp-image-970" title="Sony Google TV Remote CU Image 8" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sony-Google-TV-Remote-CU-Image-8.jpg" alt="" width="600" height="352" /></a><p class="wp-caption-text">You want a TV remote? I'll show you a TV remote!</p></div>
<p>LG went everyone better with their ST600 Smart TV adapter. Remember ATSC set-top boxes from the DTV transition? Well, the ST600 is an Internet TV adapter that works with any set through its HDMI port.  It costs about $150, and gives you a Web browser, plus one-button access to popular Internet TV sites like Netflix, CinemaNow, VUDU, Hulu Plus, YouTube, MLB TV, Pandora, and others.</p>
<p>Sony prominently featured their Sony Smart TV product line, based on Google TV. This product has really stumbled out of the gate, probably because of the incredibly complex keyboard remote control (remember Web TV, anyone?) and the fact that a majority of Web video surfing can be accessed with directed one-button Hulu Plus, Netflix, and YouTube apps. Maybe we’ll see a simplified version of the product from Sony in 2011.</p>
<p>Panasonic rolled out its own tablet computer, as previously mentioned. The Viera Tablet is part of a “cloud” focused content delivery strategy (there it is, again!) that will let consumers access on-demand and VIERA Connect content. The tablet will actually be available in several different sizes, ranging from 4” to 10,” and also functions as a TV remote control.</p>
<p>Sharp also featured connected Blu-ray players, with directed apps for VUDU taking center stage. Three new models use wireless connections to access Netflix, VUDU, Pandora and YouTube content via streaming connections. They also took the wraps off a 70-inch Quattron LCD TV with built-in WiFi and a support for CinemaNow, Netflix, VUDU, and DLNA video streaming.</p>
<div id="attachment_971" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sharp-Quattron-70-inch-LCD-1-Image-10.jpg"><img class="size-full wp-image-971" title="Sharp Quattron 70-inch LCD 1 Image 10" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sharp-Quattron-70-inch-LCD-1-Image-10.jpg" alt="" width="600" height="402" /></a><p class="wp-caption-text">Sharp's got a new 70-inch LCD glass cut, and wireless Internet connectivity to go with it.</p></div>
<p>Samsung didn’t have quite as many sexy NeTV announcements, but they did have the largest LCD TV at the show (75 inches) and prominently featured their Smart Hub technology. You can access the usual suspects through wired and wireless Ethernet connections, along with Blockbuster, MLB.TV, AccuWeather, Facebook, Hulu Plus, and History Channel content, among others.</p>
<p>PROJECTION TRENDS</p>
<p>There wasn’t a lot of projector news from CES. Texas Instruments used the event to launch a new line of DLP Pico HD chipsets. These are tiny WXGA-resolution (1280×800 pixels) digital micromirror devices (DMDs) that are used in picoprojectors and pocket projectors, and there were plenty on display in the TI suite. They had picos running in GE digital cameras, Sharp smart phones, and even a prototype tablet computer.</p>
<p>Sony even showed a DLP-based picoprojector in a new digital camera at Digital Experience, an interesting development considering that both companies parted ways back in 1996 after Sony built its first and only SVGA DLP high-brightness projector.</p>
<div id="attachment_972" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TI-WXGA-Pico-Chip-with-Dime-Image-11.jpg"><img class="size-full wp-image-972" title="TI WXGA Pico Chip with Dime Image 11" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TI-WXGA-Pico-Chip-with-Dime-Image-11.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Yes, Pico DLP chips really are that small.</p></div>
<div id="attachment_973" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TI-GE-Digital-Camera-with-DMDs-Image-12.jpg"><img class="size-full wp-image-973" title="TI -GE Digital Camera with DMDs Image 12" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TI-GE-Digital-Camera-with-DMDs-Image-12.jpg" alt="" width="600" height="334" /></a><p class="wp-caption-text">Even digital cameras are equipped with picoprojectors nowadays.</p></div>
<p>Other picoprojectors were shown from LG, ViewSonic, Acer, and Optoma. The Optoma iPod docking station with built-in picoprojector was a clever product, as was the GE digital camera. But most of these projectors cast small, dim images, and you have to wonder how the explosion of tablet computers will affect this market, considering that both picos and tablets would be used for very small group presentations.</p>
<p>Several 3D projectors took a bow in Las Vegas. Mitsubishi finally has a model number for its LCoS 3D projector (HC9000), while Sharp announced the XV-Z17000 DLP 3D chassis. Samsung’s also got a new 3D box, the SP-A8000, which also uses DLP technology. Over in the JVC booth, the previously-announced DLA-X9 and DLA-X7 D-ILA (LCoS) 3D front projectors now have THX 3D certification – apparently the only models to earn that appellation so far. The general consensus is that DLP produces better blacks and higher contrast than LCoS 3D projectors, but that will remain to be seen. (I expect to have a review sample of the Mits unit in mid-March.)</p>
<p>Mitsubishi’s big screen TV division continues to hang on in the rear-projection DLP marketplace and is actually doing quite well, thank you very much. (It’s easy to capture 100% market share when you are the only player!) They launched a 92-inch DLP set with 3D compatibility, and while it doesn’t have a model number yet, expect it to sell in the mid-$5000 range, with active shutter glasses an extra.</p>
<div id="attachment_974" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Mitsubishi-92-inch-DLP-RPTV-2-Image-13.jpg"><img class="size-full wp-image-974" title="Mitsubishi 92-inch DLP RPTV 2 Image 13" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Mitsubishi-92-inch-DLP-RPTV-2-Image-13.jpg" alt="" width="600" height="370" /></a><p class="wp-caption-text">Is a 92-inch 3D screen big enough for ya?</p></div>
<div id="attachment_975" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Mitsubishi-155-inch-OLED-Wall-2-Image-14.jpg"><img class="size-full wp-image-975" title="Mitsubishi 155-inch OLED Wall 2 Image 14" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Mitsubishi-155-inch-OLED-Wall-2-Image-14.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">NO?? OK, then  how about a 155-inch OLED screen?</p></div>
<p>WIRED VS. WIRELESS NETWORKING</p>
<p>I met with most of the major networking groups at CES. Two of them (HDBaseT and DiiVA) are very close in theory and practice, with structured wire being used to distribute video and audio between connected devices. Both systems also support USB connectivity for remote gaming control, and both systems can deliver power to connected devices (100 watts for HDBaseT and 24 watts for DiiVA).</p>
<p>Many commercial interface manufacturers are incorporating HDBaseT infrastructures into their AV switching products, the latest being Crestron (Digital Media) and Gefen. AMX already uses a version of HDBaseT in their AV switchers and distribution amplifiers.</p>
<p>DiiVA is apparently gaining popularity in China, where new apartment buildings and houses all have structured wire pulls. Most of the companies that have DiiVA-compatible products are also (not surprisingly) based in China.</p>
<div id="attachment_976" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/DiiVA-Main-Demo-1-Image-15.jpg"><img class="size-full wp-image-976" title="DiiVA Main Demo 1 Image 15" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/DiiVA-Main-Demo-1-Image-15.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Keep your eye on Diiva for both consumer and commercial applications.</p></div>
<div id="attachment_977" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Summit-Semi-Zone-Detection-2-Crop-Image-16.jpg"><img class="size-full wp-image-977" title="Summit Semi Zone Detection 2 Crop Image 16" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Summit-Semi-Zone-Detection-2-Crop-Image-16.jpg" alt="" width="600" height="378" /></a><p class="wp-caption-text">A touch of the button is all it takes to get you in a surround-sound sweet spot, courtesy of Summit Semiconductor.</p></div>
<p>On the wireless side, Summit Semiconductor, Aeleron, and Amimon all showed system-on-chip solutions for high-bitrate video and audio distribution. Amimon is the founder of the Wireless High Definition Interface (WHDI) and showed wireless display connectivity to remote PCs, as well as Blu-ray 1080p playback to specially-equipped LG and Hisense wireless LCD TVs.</p>
<p>Aeleron featured Ultra WideBand (UWB) connectivity of 1080p streaming and docking systems that work with TVs, laptops, smart phones, and other media players. They also featured DLNA-compatible UWB adapters for in-room signal distribution (UWB can’t go between rooms) and driverless HDMI interfaces.</p>
<p>Summit’s demo was perhaps the most interesting. It featured uncompressed distribution of wireless multi-channel surround audio to randomly-placed powered speaker columns. A special remote activates a supersonic Doppler system that automatically adjusts the levels of all speakers so that you are sitting in t ‘sweet spot,’ no matter where you are in the room, or where the speakers happen to be placed. It takes all of ½ second for this adjustment to be made.</p>
<p>Back over in the Hilton, Sigma Designs has found a way to reduce line noise and broad spectrum interference in HomePlug systems. Turns out, all those battery chargers and AC adapters are pretty ‘dirty,’ which clips the available bit rate for moving video and audio through decoupled AC power lines. With the Sigma enhancements, the receive speed (to a media player or TV) is as much as 65% of the transmit speed (from the playout source). With normal HomePlug appliances, the receive speed can drop to as little as 20 – 25% of the transmit speed.</p>
<div id="attachment_978" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sigma-Designs-Media-over-AC-Power-Demo-Image-17.jpg"><img class="size-full wp-image-978" title="Sigma Designs Media over AC Power Demo Image 17" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sigma-Designs-Media-over-AC-Power-Demo-Image-17.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Who knew HomePlug systems were so noisy? (not to mention iPad AC power adapters...)</p></div>
<div id="attachment_979" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Kenmore-Smart-Phone-Reading-Diagnostics-2-Image-18.jpg"><img class="size-full wp-image-979" title="Kenmore Smart Phone Reading Diagnostics 2 Image 18" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Kenmore-Smart-Phone-Reading-Diagnostics-2-Image-18.jpg" alt="" width="600" height="621" /></a><p class="wp-caption-text">Guess what? Your smart phone can talk to your oven now. And your refrigerator, and washer, and dryer, and...WHAT??? No! Not the TOILET!!!</p></div>
<p>THE WRAP</p>
<p>There was so much more to report on from CES. Many of the new TVs and accessories will be featured in upcoming spring line shows, where I’ll take a closer look at each. You can also find news about specific model numbers and pricing at many other media outlets, along with each manufacturer’s specific Web sites.</p>
<p>If there was anything to take away from the show, it was that TVs were not the big news at CES this year. Instead, multi-function smart phones and connected media appliances generated all the buzz. We’re definitely in for a protracted battle between the “your TV should be the hub!” advocates and the “Connect outside the TV!” evangelists, not to mention the “go wireless!” and “use wired connections!” camps.</p>
<p>I tend to favor the “connect outside the TV” and “go wireless” arguments, although it is a tricky task to stream high-definition video in an uncompressed format between rooms in a house. (And no, the FCC taking away more UHF TV channels won’t help at all – there’s not enough spectrum space in the UHF band for 512 MHz channels!)</p>
<p>3D will continue to muddle along this year, as the economy slowly recovers and consumers sit on their hands. The confusing “glasses or no glasses” messages won’t help. Active-shutter 3D and passive 3d are clearly superior to autostereo 3D for viewing TV shows and movies, but you have to test-drive all three modes first to understand why. Look for the passive systems from LG, JVC, and VIZIO to pick up more market share as the year winds on and consumers realize they can use their freebie movie theater glasses at home.</p>
<div id="attachment_980" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-Autostereo-LCD-Monitor-4-Image-19.jpg"><img class="size-full wp-image-980" title="LG Autostereo LCD Monitor 4 Image 19" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-Autostereo-LCD-Monitor-4-Image-19.jpg" alt="" width="600" height="343" /></a><p class="wp-caption-text">LG's placing its bets on passive 3D TV.</p></div>
<div id="attachment_981" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Samsung-Flexible-AM-OLED-Bends-Image-20.jpg"><img class="size-full wp-image-981" title="Samsung Flexible AM-OLED Bends Image 20" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Samsung-Flexible-AM-OLED-Bends-Image-20.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Samsung's flexible OLED displays don't get bent out of shape.</p></div>
<p>NeTVs are here to stay and potentially a lot more popular than 3D. Sony’s Google TV approach may be too complicated for most consumers, who are likely to favor the simpler direct channel apps offered by everyone else. And if they can access Netflix, YouTube, and Hulu Plus, they may not need much else. Look for LG’s Internet converter box to be copied by other manufacturers so that older TVs can join in the fun.</p>
<p>It was nice to see a few OLED TV demos this year, but once again the technology just isn’t ready for prime time. Look for Samsung to show an OLED Galaxy tablet later this year, if for no other reason than to prove they can make one. But it will be a while before you can buy it. The rest of the tablet and smart phone crowd will stay with tried-and-true LCD technology for the time being.</p>
<p>Blu-ray disc and player prices will continue to plummet. I’ve predicted that major brands will stop making conventional DVD players altogether in 2011, moving to Blu-ray as their exclusive platform. While we didn’t see any BD players with internal hard drives like those sold in Japan, they’re not far off. Too many people are using Netflix streaming and would like to try a straight digital download for improved image quality. What better place to enable a DVR than in a BD multifunction media hub?</p>
<p>And get used to using your smart phone to do everything. Game console controls, TV remotes, autostereo displays, even diagnostic tools to use with connected major appliances – all of these smart phone applications were shown at CES.</p>
<p>So was a iPhone case with a built-in bottle opener, which might turn out to be one of the most useful smart phone “apps” of all…</p>
<div id="attachment_982" class="wp-caption aligncenter" style="width: 319px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/BeAHeadCase-iPhone-Bottle-Opener.jpg"><img class="size-full wp-image-982" title="BeAHeadCase iPhone Bottle Opener" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/BeAHeadCase-iPhone-Bottle-Opener.jpg" alt="" width="309" height="313" /></a><p class="wp-caption-text">No comment!</p></div>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January 11, 2011  5:49 PM</b>
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
			<?=getComments(4166)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4166)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-applications-plenty-buzz-ahhh-not-so-much.php" type="text/javascript" charset="utf-8"></script>
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