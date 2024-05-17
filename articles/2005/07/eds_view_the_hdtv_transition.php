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
		AND e.entry_id = 170";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ed Milbourn" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 170 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ed Milbourn" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 170 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 170";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/07/eds-view-the-hdtv-transition.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 170";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Ed\'s View - The (H)DTV Transition" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Ed\'s View - The (H)DTV Transition" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Ed\'s View - The (H)DTV Transition" />
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
	<title>HDTV Magazine - Ed's View - The (H)DTV Transition</title>
	<meta name="keywords" content="dtv transition, consumer electronics, hdtv programming, compelling hdtv, penetration level, cable, hdtv, dtv, broadcasters, digital, analog, programming, stb, ota, transition, want, most, fact, security, programmers, allow, television, consumer, sets, however" />
	<meta name="description" content="Talk about herding cats!  That's an apt analogy to the task the FCC has, and continues having, in an attempt to reconcile the positions of all the various entities with a vested interest in the DTV transition - and its star, HDTV.  These DTV &quot;stakeholders&quot; are, indeed, just like a bunch of cats - hissing, growling and pawing at each other. But this is understandable, for a lot is at stake as the most fundamental change in the history of US broadcasting takes place. So, let's take a look at the salient issues surrounding these stakeholders to get a better understanding of the various positions and their impact on the growth of HDTV.  " />
	<meta name="title" content="Ed's View - The (H)DTV Transition" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Ed's View - The (H)DTV Transition" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/07/eds-view-the-hdtv-transition.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Talk about herding cats!  That's an apt analogy to the task the FCC has, and continues having, in an attempt to reconcile the positions of all the various entities with a vested interest in the DTV transition - and its star, HDTV.  These DTV &quot;stakeholders&quot; are, indeed, just like a bunch of cats - hissing, growling and pawing at each other. But this is understandable, for a lot is at stake as the most fundamental change in the history of US broadcasting takes place. So, let's take a look at the salient issues surrounding these stakeholders to get a better understanding of the various positions and their impact on the growth of HDTV.  " />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=170', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/07/eds-view-the-hdtv-transition.php">Ed's View - The (H)DTV Transition</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ed Milbourn</b> on <b>July 31, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>, <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>, <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p>Talk about herding cats! That's an apt analogy to the task the FCC has, and continues having, in an attempt to reconcile the positions of all the various entities with a vested interest in the DTV transition - and its star, HDTV. These DTV "stakeholders" are, indeed, just like a bunch of cats - hissing, growling and pawing at each other. But this is understandable, for a lot is at stake as the most fundamental change in the history of US broadcasting takes place. So, let's take a look at the salient issues surrounding these stakeholders to get a better understanding of the various positions and their impact on the growth of HDTV.  </p>

<p>Their numbers are numerous. They include:  Broadcasters, Cable Operations, Satellite System Operations, the US Government, Manufacturers, Retailers, Program Providers, and, the most important - us, the Ultimate Consumers.  Of these, let's focus on the "big five" who have the most active lobbying position in Washington, DC, and to some extent, represent the interests of all of the "stakeholders."  The "big five" are:  the Broadcasters, represented by the National Association of Broadcasters (NAB); the Cable industry, represented by the National Cable Television Association (NCTA); the Consumer Electronics Manufactures and Retailers, represented by the Consumer Electronics Association (CEA); the Programmers, represented by the Motion Picture Association of America (MPAA); and the US Government represented by the Federal Communications Commission (FCC) and the power of legislation (that's a big stick). </p>

<p><strong>Broadcasters (NAB)</strong><br />
The broadcasters have been ordered to switch to digital transmission and return their analog channel spectrum back to the Government for auctioning. They are trying everything they can to delay this. They want to hang on to their analog spectrum as long as possible.  Broadcasters argue that giving up their analog spectrum in the foreseeable future will disenfranchise a significant number of viewers who depend only on over-the-air (OTA) analog reception, and are too economically disadvantaged to buy a DTV set.  It is hard to justify this position in light of the fact that over 85% of viewers' receive signals from Cable, DBS or other providers, and this percentage is growing.  In fact, most of the "poor" viewers are connected to Cable - at least Basic Cable. </p>

<p>However, there is some argument that a very small percentage of those who must depend on OTA reception may not be able to receive digital OTA signals with satisfactory reliability - if at all. Even though theoretical calculation of DTV OTA coverage assures Broadcasters equivalency with analog coverage, in fact that is not always going to be true. Most viewers can tolerate a fairy degraded analog TV picture in spite of the snow and/or ghosts.  Such equivalent degraded DTV signals may result in no signal at all or intermittent loss, blocking or freezing of the picture along with intermittent or loss of sound. This will be true particularly with indoor antenna reception. There will be some improvement in this as DTV tuners become more sophisticated, but the OTA DTV problems will not be totally eliminated.</p>

<p>Congress is in the process of establishing a "hard" cutoff date for analog service, probably around January 1, 2009. However, the NAB is a very powerfully lobby, and Broadcasters do not want to alter their analog business models even with an ever increasing deteriorating OTA share. Therefore, they will continue to try to delay the analog "cut-off" as long as possible in spite of their increasing irrelevancy   In fact, we may be seeing the start of the demise of OTA TV broadcasting as we have known it.</p>

<p><strong>Cable (NCTA)</strong><br />
Cable is presently in the "catbird seat."  Approximately 70% of the US households are connected to cable. Well over 50% of these viewers watch cable programming compared to network and local programming (also carried by cable). Cable is rapidly converting to digital transmission and offering digital premium packages.  These packages (or tiers) provide not only HDTV programs but also video-on-demand (VOD), broadband IP, and telephone (VoIP) services. In addition, an increasing number of HDTV manufacturers are including in many models fully "cable ready" capability with digital cable tuners and the cable security card interface ("CableCard").   </p>

<p>However, in spite of the CableCard adoption, Cable companies do not want to relinquish their integrated security set-top-box (STB) business model and allow an "open" design.  Such a design would allow any CE manufacturer to offer an STB.  With some justification, Cable is concerned about the security costs and feature capability of an open STB design. The FCC has granted Cable a delay of the date that manufacturers are permitted to make and market the open STB design.   Supposedly, Cable is also working on a design that will allow security algorithms to be downloaded to the STB.  Certainly, Cable does not want to lose control over their "gateway" mechanism, the STB. But again, like Broadcasters, the DTV transition will force them to change their business model.</p>

<p>Cable is also fighting hard to prevent a ruling that would force them to carry the local broadcasters' full digital multiplex, claiming insufficient bandwidth. They also want the flexibility to convert the broadcasters' HDTV signal to SDTV for the same reasons. However, that argument is week because most cable systems are offering HDTV as part of their digital tiers. They simply do not want any ruling that will interfere with their ability to offer a premium HDTV tier without carrying non-premium SDTV channels with the same programming.  In fact, these two issues are somewhat vacuous because retransmission agreements between local broadcasters and the local cable companies are rectifying these issues on a market-to-market basis. </p>

<p><strong>Consumer Electronics Manufacturers and Retailers (CEA)</strong><br />
The Consumer Electronics (CE) group would like the DTV transition to proceed as quickly as possible - but not so fast as to not allow an expeditious reduction in their present analog inventories.  CE has always seen a fantastic opportunity with DTV, HDTV in particular. The larger screen sizes required to optimize the HDTV viewing experience offer price-premium opportunities for CE.  Add to that the developing market for HDTV peripherals such as HDTV DVD's and Digital Video Recorders (DVR's) and the opportunities exponentially increase.</p>

<p>The FCC has ordered CE to provide digital tuners in 50% of all 25-36" (diag.) sets manufactured since July 1, 2005 and in 100% of these sets manufactured after March 1, 2006.  CE accepted this in spite of the inventory problem, because they do not want to be accused of delaying the transition.</p>

<p>CE's biggest threat is an obsolete Cable decoding standard. In order to accommodate an increasing number of HDTV programs, Cable is converting to the MPEG-4 codec standard, allowing for an up to 4:1 improvement in bandwidth conservation. Since the built-in Cable Ready decoders in CE sets will only decode MPEG-2, it is not clear how viable DTV sets will be in a future cable environment, except as monitors.</p>

<p>Cable and CE are also struggling with a second-generation cable interface standard that would allow advanced cable service offerings such as VOD, to be downloaded directly to the TV set or an open standard STB. The technical challenges of this are daunting, but progress is slowing being made.</p>

<p><strong>Programmers (MPAA)</strong><br />
The network programmers have been chastised by the Government for not providing sufficient, compelling HDTV programming to advance the DTV transition. CEA has been especially vocal relative to this issue, stating that without compelling HDTV programming, customers will not be interested in purchasing HDTV sets.  The programmers' position has been that there are not enough HDTV sets in the market to justify the additional HDTV production costs.  So, we have had a classic "chicken/egg" marketing block. However, the HDTV programming situation is now rapidly changing for the better. More and more cable programmers are providing HDTV offerings, forcing the traditional broadcast network to step-up to the challenge. More than 50% of prime-time network programming is now offered in HDTV with more and more non-prime-time and sports offerings on the way. This should rapidly drive HDTV set sales to the magical 20% household penetration level that defines the boundary between the "early adopter" and the "commodity" consumers. That penetration level should occur next year as the projected percentage of household receiving HDTV in 2005 is already at approximately 13%.  At the 20% penetration level HDTV sales will increase exponentially, giving programmers increased economic justification to product HDTV programming.</p>

<p>However, one significant obstacle looms in the way of providing increased compelling HDTV programming - the issue of adequate content protection. MPAA has a very high stake in the DTV transition relative to assuring against real-time theft of high quality productions. The full potential of HDTV cannot be realized until this issue is satisfactorily resolved for broadcast, cable, DVD, games and other HDTV sources. It will be necessary to implement recording rules and adequate security for all HDTV program sources in order for HDTV to move to the next level. The HDMI interface goes a long way in providing content protection between the STB and the HDTV display monitor, but much remains to be done. </p>

<p><strong>U.S. Government</strong><br />
The "big stick" wants the broadcasters' analog spectrum back so they can auction in off for several billion dollars. One of the primary reasons to switch to digital transmission is that the lower DTV power levels for a given coverage area allow the channels to be located ("packed") closer to each other. This frees-up a block of bandwidth that can be auctioned. In addition, this available extra spectrum is in demand for homeland security purposes. A tertiary reason is the public expectation of HDTV. The whole national advanced television program was sold on this premise, and after 15 years, the public is getting somewhat impatient.  </p>

<p>That leads us to the final, unofficial, but most important stakeholder - that's us, the consumer and ultimate beneficiary of HDTV. The potential for high definition television to advance our entertainment pleasure and information level cannot be overestimated. Other than the obvious commercial and scientific value of high quality video, the fact that a near perfect view of the world can be had in our homes is a very powerful thing indeed. </p>

<p>Ed  </p>

<p><br />
___________________<br />
<strong>About Ed Milbourn</strong><br />
After graduating from Purdue University with degrees in Electrical Engineering and Industrial Education in 1961 and 1963 respectively, Ed Milbourn joined the RCA Home Entertainment Division in 1963. During his thirty-eight year career with RCA (later GE and Thomson multimedia), Mr. Milbourn held the positions of Field Service Engineer, Manager of Technical Training and Manager of Sales Training. In 1987, he joined Thomson's Product Management group as Manager of Advanced Television Systems Planning, with responsibilities including Digital Television and High Definition Television Product Management. Mr. Milbourn retired from Thomson multimedia in December 2001, and is now a Consumer Electronics Industry consultant.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ed Milbourn</b>, <b>July 31, 2005  4:43 PM</b>
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
			<?=getComments(170)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Ed Milbourn', 170)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/07/eds-view-the-hdtv-transition.php" type="text/javascript" charset="utf-8"></script>
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