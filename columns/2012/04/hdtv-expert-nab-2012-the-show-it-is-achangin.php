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
		AND e.entry_id = 4783";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4783 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4783 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4783";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-nab-2012-the-show-it-is-achangin.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4783";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - NAB 2012: The Show It Is A-Changin&rsquo;&hellip;" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - NAB 2012: The Show It Is A-Changin&rsquo;&hellip;" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - NAB 2012: The Show It Is A-Changin&rsquo;&hellip;" />
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
	<title>HDTV Magazine - HDTV Expert - NAB 2012: The Show It Is A-Changin&rsquo;&hellip;</title>
	<meta name="keywords" content="dynamic range, black magic, magic design, lcd monitor, high dynamic, video, thunderbolt, back, projector, digital, –, monitor, black, resolution, ’s, hall, mpeg, hdmi, show, even, products, camera, interface, see, nab" />
	<meta name="description" content="This year&amp;acirc;��s NAB looked more like a hybrid of CES and InfoComm than anything else." />
	<meta name="title" content="HDTV Expert - NAB 2012: The Show It Is A-Changin&amp;rsquo;&amp;hellip;" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - NAB 2012: The Show It Is A-Changin&amp;rsquo;&amp;hellip;" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-nab-2012-the-show-it-is-achangin.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This year&amp;acirc;��s NAB looked more like a hybrid of CES and InfoComm than anything else." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4783', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-nab-2012-the-show-it-is-achangin.php">HDTV Expert - NAB 2012: The Show It Is A-Changin&rsquo;&hellip;</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April 20, 2012</b>
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
          <p>This was my 17<sup>th</sup> trip to Las Vegas to see what once was one of the world’s largest trade shows. Back in 1995, NAB was clearly focused on broadcasting, mostly the digital kind. The ATSC Grand Alliance had a major presence back in ’95 as the United States began its tentative steps towards an all-digital broadcasting system, and there was no question that terrestrial (over-the-air) television was the king of the hill.</p>
<p> </p>
<p>Today, that’s all changed. The digital transition has come and gone. Cable TV has supplanted traditional broadcasting on the throne, with Internet-delivered ‘over the top’ video sitting next in line. Broadcasters are under fire from (of all people) the FCC, who wants to take back more UHF TV spectrum to solve a mostly imaginary wireless broadband crisis.</p>
<div id="attachment_2082" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2082" rel="attachment wp-att-2082"><img class="size-full wp-image-2082" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-1.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Canon's enormous booth was at the center of all the Central Hall action.</p></div>
<p> </p>
<p>Gone for the most part are the NAB ‘megabooths’ once erected and staffed by Sony, Panasonic, JVC, Ikegami, Hitachi, and Toshiba. Back in 1995, Sony had exhibits both in the Las Vegas Convention Center and Bally’s Hotel and a multi-million dollar budget to support them. Most of these companies have more modest representation these days as the center of gravity in the electronics world shifts to Korea and China.</p>
<p> </p>
<p>The profound influence of the consumer electronics world can clearly be seen as you walk the aisles at NAB. Smaller, compact, and higher-resolution camcorders have replaced the $50,000 – $100,000 behemoths of 17 years ago. iPads abound, both in individual booths and attendees’ backpacks. Videotape recorders (VTRs) have all but vanished, replaced by solid-state media recorders and ultraportable hard drives.</p>
<p> </p>
<p>The south hall of the Las Vegas Convention center, which didn’t exist except on a blueprint in 1995, now dominates the action at the show. Hundreds of small, ‘who dat?’ companies are set up in small stands and hawk their storage area network products, cloud workflows, MPEG encoders, fiber optic connectivity systems, and umpteen-million Mac-based edit, color correction, and audio mixing products.</p>
<div id="attachment_2083" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2083" rel="attachment wp-att-2083"><img class="size-full wp-image-2083" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-2.jpg" alt="" width="600" height="351" /></a><p class="wp-caption-text">For some odd reason, RED's 4K LCoS projector was behind wired, breakproof glass.</p></div>
<p> </p>
<p>The old names are still there, though. Some have even beefed up their presence, like Canon. Panasonic once occupied the entire mezzanine level of the central hall, but has ceded half that space to other companies. Sony still occupies a big chunk of the rear central; hall, but is also slowly retrenching over time.</p>
<p> </p>
<p>Here’s why: Back in the middle of the ‘90s, the typical broadcast/production camcorder shot standard definition to tape and cost anywhere from $10K to $50K, depending on bells and whistles. A good reference CRT video monitor would set you back at least $25,000, and tripods, fluid heads, gyroscopic mounts, robotic platforms, and teleprompter heads were all priced accordingly.</p>
<p> </p>
<p>Today? I have a Nikon CoolPix 8200 that can shoot 1080p/30 movies, 16 megapixel stills, offers multi-zone focus and image stabilization, comes with a 10x optical zoom lens, and records everything to a 32 GB flash drive. The price? All of $220.</p>
<div id="attachment_2084" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2084" rel="attachment wp-att-2084"><img class="size-full wp-image-2084" title="Figure 3" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-3.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Yes, Black Magic Design is in the camera business now. What's next - an Ikegami MPEG4 encoder?</p></div>
<p> </p>
<p>And there you have it. Products that once cost in the tens of thousands of dollars now are available with far greater performance for hundreds of dollars, or at least a couple thousand. Want to buy a 4K JVC camera? It will set you back about $5,000. How about a reference plasma monitor? Try $4,000.</p>
<p> </p>
<p>You can do teleprompting on an iPad now, and pick up a remote-controlled helicopter rig for your Canon digital SLR (which also shoots 1080p video) for about $500 – $700. Or grab a compact cinema camera with 13 steps of dynamic range and 2.5K image resolution for $3,000.</p>
<p> </p>
<p>Of course, most of this stuff is available on the Internet. There barely was an Internet back in 1995 (remember the dial-up days?), and you had to go to a dealer to buy any of this gear. B&amp;H wasn’t the national powerhouse it is now (yes, they had a big, long booth at NAB, right behind Sony) and production companies often had to take out loans to get the newest, latest goodies.</p>
<p> </p>
<p>Nowadays, your gear can pay for itself in a few productions. And the barriers to creating and distributing content have largely disappeared, limited only by the speed of Internet connections. ‘Content management’ was a popular expression this year, as was ‘cross-platform delivery.’ Conventional TV broadcasting is still around and trying to re-invent itself, but there are clearly many ways to package and deliver video content that don’t involve traditional media distribution.</p>
<div id="attachment_2085" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2085" rel="attachment wp-att-2085"><img class="size-full wp-image-2085" title="Figure 4" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-4.jpg" alt="" width="600" height="355" /></a><p class="wp-caption-text">Yes, you can actually edit 4K content on the go now. But first, you've gotta go out and shoot it...</p></div>
<p> </p>
<p>Will NAB survive? Sure, because it has a sweet spot on the trade show calendar and has successfully changed with the times. Those incredible shrinking booths have been replaced by the likes of Ericsson, Avid, Black Magic, Grass Valley, Harris, Evertz, Ross Video, and a host of other manufacturers whose offerings are platform-agnostic. (Can’t say that about the TV and radio transmitter folks, though…)</p>
<p> </p>
<p>After wandering the floors for three days and delivering a presentation on the management and distribution of HDMI signals (wait – you can actually manage HDMI?) at the Broadcast Engineering Conference, I managed to find a few interesting products here and there. To the list!</p>
<div id="attachment_2097" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2097" rel="attachment wp-att-2097"><img class="size-full wp-image-2097" title="Samsung Figure Insert" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Samsung-Figure-Insert.jpg" alt="" width="600" height="378" /></a><p class="wp-caption-text">3D TV at home is a piece of cake! That is, as long as you have a broadband connection, and a TV antenna...</p></div>
<p> </p>
<p><strong>RED,</strong> the makers of those cool video production cameras, apparently had some extra time and money on their hands and decided to roll out a <strong>laser-powered digital cinema projector</strong>. The design is based on a 4K LCoS light engine developed by HDI (High Definition Integration) back in 2009. Apparently RED acquired the company a year or so ago, and now wants to get into the projection space. No brightness specification was given, but the signage indicated it could light up a 15’ screen and would retail for less than $10,000. The demo showed some promise, but there are lots of things that still need attention (high black levels, low contrast, color accuracy, etc).</p>
<p> </p>
<p>It sems <strong>Black Magic Design</strong> has gotten bored with developing interface boxes. That’s the only explanation I can come up with for their new <strong>digital cinema camera</strong>, which offers 13 stops of dynamic range, a 2.5K pixel sensor, SSD recording, and support for EF lenses. It’s also compatible with the Thunderbolt display/data interface, and the suggested retail price is $3,000. Quite a crowd gathered around this demo!</p>
<div id="attachment_2086" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2086" rel="attachment wp-att-2086"><img class="size-full wp-image-2086" title="Figure 5" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-5.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Be careful where you walk when wearing Epson's MOVERIO AR glasses!</p></div>
<p> </p>
<p>You had to crawl all the way to the back of the south hall lower to find them, but <strong>Epson</strong> made the trip worthwhile with a demonstration of their <strong>Moverio augmented realty (AR) eyewear</strong>. These glasses contain two small full-color LCD panels in the middle of semi-transparent goggles, allowing you to see normally and watch projected images at the same time. (Kind of a ‘Watchman’ effect.) I’ll be curious to see if these take off, given the adverse physiological reactions that have occurred with earlier attempts at AR (Google Sony’s Glasstron spectacles).</p>
<p> </p>
<p>Tired of running heavy-duty HD-SDI cables to your otherwise-lightweight 1080p camcorder? <strong>Amimon </strong>showed a better way to hook up with their demo of a <strong>wireless 5.8 GHz HD-SDI transmission system</strong>, based on their clever wireless HDMI chipsets. The latter can already move 1080p/60 video and multi-channel audio over a 40 MHz bandwidth, so adapting it to 3G HD-SDI was a piece of cake. Their tests in the South Hall pushed a signal out to at least 300 feet before dropout.</p>
<p> </p>
<p>Around the corner, <strong>Intel</strong> made up for their lack of Thunderbolt products at CES by unveiling a suite of Thunderbolt connectivity ‘solutions,’ including a <strong>4K mobile editing package</strong> that used a Lenovo notebook PC, a pair of Samsung and Apple LCD monitors, two compact Promise four-bay RAID drives, and an AJA iOXT interface box that breaks out USB, HD-SDI, and HDMI ports.</p>
<p> </p>
<p>Other companies featured in the Thunderbolt ‘goodies’ showcase included <strong>Black Magic Design</strong> (<strong>UltraStudio video I/O box</strong>, Thunderbolt to bi-directional HD-SDI and HDMI), <strong>MOTU</strong> (analog and digital video recorder with Thunderbolt interface), <strong>LaCie</strong> (compact portable hard drives), <strong>Rocstor</strong> (<strong>KROC 2M</strong> desktop RAID storage with Thunderbolt), <strong>Seagate</strong> (<strong>GoFlex</strong> portable Thunderbolt adapter), and <strong>Sumimoto</strong> (optical fiber Thunderbolt cables). Think Thunderbolt is catching on? (Duhhhh!)</p>
<div id="attachment_2087" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2087" rel="attachment wp-att-2087"><img class="size-full wp-image-2087" title="Figure 6" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-6.jpg" alt="" width="600" height="415" /></a><p class="wp-caption-text">Cables? We don't need no stinking HD-SDI cables!</p></div>
<p> </p>
<p><strong>Samsung KBS</strong> figured out a clever way to transmit <strong>3D content over ATSC digital TV channels</strong>: Send the left eye images as usual, and transmit the right eye images over a standard broadband connection, encoded as MPEG4. All that’s needed is a constant data rate of 6 Mb/s to make it work, something that may be a piece of cake in Asia but is still uncertain even with normal broadband connections on this side of the pond. But the concept does work nicely.</p>
<p> </p>
<p><strong>Panasonic</strong> has swallowed up Sanyo and their enormous projector line (now, that will give anyone indigestion), but their big news at the show – besides a 4k camera system – was a 20,000 lumens projector that weighs all of 95 pounds. By way of comparison, my old Sony 7” CRT projector could barely crank out 200 lumens and tipped the scales at 140 pounds. The <strong>PT-DZ21K</strong> uses a three-chip DLP engine and its native resolution is 1920×1200 pixels (WUXGA).</p>
<p> </p>
<p>Around the corner, <strong>Canon</strong> showed its <strong>REALiS WUX5000</strong> 5000-lumens LCoS projector. This is the brightest Canon projector yet and offers WUXGA (1920×1200) resolution. No 4K version is in the immediate future, but just around the corner, Canon showed a prototype <strong>4K 30-inch LCD monitor</strong> using IPS technology. It certainly had lots of image detail, but needed some help with black levels. Given the company’s strong commitment to full-frame CMOS video sensors and 4K cameras, neither product was surprising.</p>
<div id="attachment_2088" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2088" rel="attachment wp-att-2088"><img class="size-full wp-image-2088" title="Figure 7" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-7.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Believe it or not, he's actually 'popping' a virtual balloon. (Seriously!)</p></div>
<p> </p>
<p>It wasn’t a shipping product, but the <strong>National Institute of Communications and Technology (NICT)</strong> in Japan showed a prototype <strong>200-inch autostereo rear-projection display</strong>. This demonstration used 200 individual JVC D-ILA projection engines, each with full 2K resolution, to light up 200 different 2K resolution views in narrow vertical bands. A special Fresnel lens integrated the views and the barrier crossings weren’t as apparent as I would have expected.</p>
<p> </p>
<p>Next door was a demonstration by NICT of a ‘virtual’ balloon to show the possibilities of <strong>haptic (touchscreen) technology</strong>. Using a special stylus, you tapped an image of a balloon to enlarge it, and then stroked the balloon to make it squeak and feel the rubbery texture through the stylus. You could even pop the balloon and smell a perfume contained inside. Way cool!</p>
<p> </p>
<p><strong>ATTO </strong>was one of many companies supporting the Thunderbolt interface with SAS/SATA RAID drives, not to mention Fibre Channel and 10 Gigabit Ethernet connections. Their <strong>Desklink</strong> products are compact and provide plenty of storage capacity. The company’s <strong>ThunderStream</strong> interface can even supported embedded storage.</p>
<p> </p>
<p><strong>Adtec</strong> rolled out a few new MPEG encoders. The <strong>EN-91P</strong> is a 1080p AVC (H.264 MPEG4) encoder that can be used for 3D and has an optical fiber input, while the <strong>EN-20</strong> is a dual-input MPEG2 encoder with Dolby AC3 encode, DD5.1 passthrough, ASI output, and an up-converted QAM output for RF-modulated transmission systems.</p>
<div id="attachment_2089" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2089" rel="attachment wp-att-2089"><img class="size-full wp-image-2089" title="Figure 8" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-8.jpg" alt="" width="600" height="431" /></a><p class="wp-caption-text">3D Glasses? We don't need no stinkin' 3D glasses!</p></div>
<p> </p>
<p><strong>Dolby</strong> showed an <strong>autostereo 3D LCD monitor</strong> that uses lenticular parallax barrier and was developed jointly with Philips. This monitor was used to show clips from <em>Hugo </em>and the 3D effect was clearly visible, although not as intense for off-axis viewers and not as punchy as active shutter or even passive shutter 3D. No word on pricing or delivery.</p>
<p> </p>
<p><strong>JVC’s</strong> 4K camcorder ($5,500) may be one of the best deals out there. The <strong>GY-HMQ10</strong> uses a ½-inch CMOS sensor with 8.3 million pixels (3840×2160) at 24, 50, or 60 frames per second. It comes with a 10x zoom lens and optical image stabilization.  Believe it or not, the GY-HMQ10 comes with four HDMI output terminals, which can also be used to drive four discrete monitors at 1920x1080p resolution.</p>
<p> </p>
<p>Last but not least, <strong>goHDR</strong> demonstrated high dynamic range video on a <strong>SIM2 HDR47E</strong> LCD monitor, similarly equipped for HDR signals. (The technology is owned and licensed by Dolby Labs.) goHDR is a spin-off of the University of Warwick in England and has produced a few HDR short films using a camera manufactured by SpheronVR, a competitor to ARRI who is also shipping HDR cameras.</p>
<div id="attachment_2090" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=2090" rel="attachment wp-att-2090"><img class="size-full wp-image-2090" title="Figure 9" src="http://www.hdtvexpert.com/wp-content/uploads/2012/04/Figure-9.jpg" alt="" width="600" height="411" /></a><p class="wp-caption-text">Panasonic's PT-DZ21K projector is so bright, you can light a match just by holding it in front of the lens. (I'm KIDDING!)</p></div>
<p> </p>
<p>High dynamic range video, also demonstrated by <strong>Dolby</strong> with its <strong>PRM4200</strong> 42-inch reference monitor, is quite something to see after watching garden-variety BT.709 video on a steady basis. The range of tonal values from deep black to pure white approaches what we see in everyday life, particularly in deep shadows where detail usually vanishes on a video screen.</p>
<p> </p>
<p>It’s not practical yet to broadcast HDR – the data rates would require enormous bandwidth – but you may soon see it in movie theaters, along with high frame rate (48 Hz and up) content. Eventually, there will be a way to get it into the home, assuming HDR technology gains any traction in a world that seems otherwise obsessed with watching video on laptops, iPads, and even phones.</p>
<p> </p>
<p>Hmmm….a high dynamic range iPad. Now, there’s a concept! Listening, Apple?</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April 20, 2012  7:27 PM</b>
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
			<?=getComments(4783)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4783)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/04/hdtv-expert-nab-2012-the-show-it-is-achangin.php" type="text/javascript" charset="utf-8"></script>
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