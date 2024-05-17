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
		AND e.entry_id = 4314";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4314 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4314 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4314";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/04/hdtv-expert-nab-2011-its-all-about-streaming-displays-and-connectivity.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4314";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - NAB 2011: It&rsquo;s All About Streaming, Displays, and Connectivity" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - NAB 2011: It&rsquo;s All About Streaming, Displays, and Connectivity" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - NAB 2011: It&rsquo;s All About Streaming, Displays, and Connectivity" />
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
	<title>HDTV Magazine - HDTV Expert - NAB 2011: It&rsquo;s All About Streaming, Displays, and Connectivity</title>
	<meta name="keywords" content="right eye, color anaglyph, eye images, left eye, side side, eye, monitor, color, show, –, video, nab, ’s, lcd, showed, side, resolution, right, used, top, version, left, ″, monitors, production" />
	<meta name="description" content="The recent National Association of Broadcasters show in Las Vegas was loaded with new media demonstrations, and there were some cool broadcast and professional monitors to check out, too." />
	<meta name="title" content="HDTV Expert - NAB 2011: It&amp;rsquo;s All About Streaming, Displays, and Connectivity" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - NAB 2011: It&amp;rsquo;s All About Streaming, Displays, and Connectivity" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/04/hdtv-expert-nab-2011-its-all-about-streaming-displays-and-connectivity.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The recent National Association of Broadcasters show in Las Vegas was loaded with new media demonstrations, and there were some cool broadcast and professional monitors to check out, too." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4314', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/04/hdtv-expert-nab-2011-its-all-about-streaming-displays-and-connectivity.php">HDTV Expert - NAB 2011: It&rsquo;s All About Streaming, Displays, and Connectivity</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April 20, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=298&category=Broadcast">Broadcast</a></b>, <b><a href="/category.php?id=479&category=HTPCs & Laptops">HTPCs & Laptops</a></b>, <b><a href="/category.php?id=341&category=Internet HD Video">Internet HD Video</a></b>
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
          <p>With each passing year, NAB looks less and less like a broadcaster’s show and more like a cross between CES and InfoComm. It’s a three-ring circus of product demos, panel discussions, conferences, and media events that all points to the future of ‘broadcasting’ as being very different than what it was at the end of the 20<sup>th</sup> century.</p>
<p> </p>
<p>Officially, slightly less than 90,000 folks showed up to walk the floors of the Las Vegas Convention Center, and it was elbow-to-elbow in some exhibits. But there was another trend of smaller booths for the ‘big name’ exhibitors like Panasonic and JVC.</p>
<p> </p>
<p>That reflects the reality of selling products that have mostly three and four zeros in their price tags. At my first NAB in 1995, it wasn’t unusual to see $50,000 cameras and $80,000 recorders. Now, you can buy some pretty impressive production cameras for about $5,000.</p>
<p> </p>
<p>Streaming and over-the-top video was big this year. Ironically, NAB featured an enormous streaming media pavilion back in 1999, but it vanished the next year. The reason? A lack of broadband services across the country that could support streaming at reasonable bit rates.</p>
<p> </p>
<p>Obviously, that’s all changed now, what with Netflix at 21 million subscribers and climbing, and MSOs deploying multi-platform delivery of video and audio to a plethora of handheld devices. Concurrently, the broadcast world is trying to roll out a new mobile handheld (MH) digital TV service to stand-along portable receivers and specially-equipped phones.</p>
<p> </p>
<p>And behind all of this, the FCC continues to make noise that it wants to grab an additional 100 – 120 MHz of UHF TV spectrum to be repurposed for wireless broadband, a service you’ll have to pay for. Attendees had mixed thoughts on whether the Commission will actually be able to pull this off – there is some opposition in Congress – but there appeared to be a high level of opposition to the plan, considering there is plenty of other spectrum available for repurposing, much of it already used exclusively for government and military purposes.</p>
<p> </p>
<p>Like last year, there were lots of 3D demos, but the buzz wasn’t really there. 3D still has a ways to go with its roll-out and it simply can’t compete with the interest in content delivery to smart phones, tablets, and other media players. Still, there were some cool 3D products to be found here and there.</p>
<p> </p>
<p>Here are some of the highlights from the show.</p>
<div id="attachment_1215" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1215" href="http://www.hdtvexpert.com/?attachment_id=1215"><img class="size-full wp-image-1215" title="RCA Pocket MH Receivers MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/RCA-Pocket-MH-Receivers-MR.jpg" alt="" width="600" height="391" /></a><p class="wp-caption-text">Is that an MH receiver in your pocket, or are you just glad to watch DTV?</p></div>
<p><strong>ATSC MH Pavilion</strong> – several companies exhibited a range of receivers for the MH services being transmitted during the show from Las Vegas TV stations and low-power rigs in the convention center. <strong>LG</strong> and <strong>RCA</strong> both showed some snazzy portable MH receivers, with LG’s exhibit putting the spotlight on autostereo 3D MH (as seen at CES) and a service call ‘Tweet TV’ which would allow viewers to comment on shows they’re watching and have those tweets appear on their MH receiver.</p>
<p> </p>
<p>Another demo had CBS affiliate KLAS-DT transmitting electronic coupons for local retailers and restaurants during the show. These showed up on a prototype full-touch CDMA smart phone with a 3.2” HVGA screen.</p>
<p> </p>
<p>In a nearby booth, RCA unveiled a lineup of hybrid portable DTV receivers. There are two 3.5” models (DMT335R, $119, and DMT336R, $159), a 7” version (DMT270R, $179), and a pocket car tuner/receiver that connects to an existing car entertainment center. It will sell for $129.</p>
<div id="attachment_1216" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1216" href="http://www.hdtvexpert.com/?attachment_id=1216"><img class="size-full wp-image-1216" title="Motorola frame-packed 3D Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Motorola-frame-packed-3D-Demo-MR.jpg" alt="" width="600" height="692" /></a><p class="wp-caption-text">Believe it or not, this was a commercial for Coca-Cola.</p></div>
<p><strong>Motorola</strong> had two intriguing demonstrations. The first showed full-bandwidth 3D content distribution, using the full 38.8 Mb/s bandwidth of a 256 QAM channel to transport frame-packed 1080p video with full 1920×1080 left eye and right eye images, encoded in the MPEG4 H.264 format and sequenced through active shutter glasses.</p>
<p> </p>
<p>Nearby, an HD video stream was encoded for four different displays, with all four signals carried simultaneously in the same bit stream. First up was a 1080p/60 broadcast; next to that a 720p/60 version, followed by a standard definition version (480i) and a version sized for a laptop computer or tablet. Both MPEG2 and MPEG4 codecs were used.</p>
<p> </p>
<p><strong> </strong></p>
<p><strong>Red Rover</strong> attracted quite a crowd with their 28″ 4K (3840×2160) 3D video monitor which uses two 4K LCD panels arranged at 90-degree angles to each other (one on top, facing down). A half-mirror with linear polarization is used to combine the left and right eye images for passive viewing. Both LCD panels are Samsung vertically-aligned models, and the whole works will sell for (ready for this?) $120,000.</p>
<div id="attachment_1217" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1217" href="http://www.hdtvexpert.com/?attachment_id=1217"><img class="size-full wp-image-1217" title="Red Rover 4K Prototype Monitor MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Red-Rover-4K-Prototype-Monitor-MR.jpg" alt="" width="600" height="690" /></a><p class="wp-caption-text">Only $120K? That's a steal!</p></div>
<p><strong>Volfoni</strong> showed dual-purpose 3D glasses at NAB. When powered on, they function as active shutter eyewear. Powered off, they are usable as passive 3D glasses. The whole shebang is controlled by an external power pack the size of an iPod nano that clips to your pocket or shirt, and this ‘pod’ can ‘learn’ any IR code from active shutter TVs.</p>
<p> </p>
<p>The pod controller can step through several neutral density filters and there are several levels of color correction possible from the remote power pack. (Electronic sunglasses – imagine that!) The glasses use 2.4 GHz RF signaling technology to synchronize with any active shutter monitor or TV. And despite all of the bells and whistles, they weigh just over an ounce.</p>
<p> </p>
<p><strong>Sony’s </strong>17″ and 25″ BVM-series OLED monitors that were first shown at the 2011 HPA Technology Retreat now have siblings. The PVM-E250 Trimaster OLED display is structurally the same as its more-costly BVM cousin, but has fewer adjustments and operating features. And it’s going to sell for quite a discount over the BVM version – just $6,100. There’s also a 17-inch version which wasn’t operating at the show, and it is expected to retail for $4,100.</p>
<p> </p>
<p>Up at the front of the Central Hall, <strong>Panasonic</strong> was showing the TH-42BT300U, their first plasma reference-grade monitor. It’s not all that different from the exiting 20-series industrial plasma monitors in appearance, but there’s a big difference in operating features. Black levels have dropped and low-level noise has been minimized with a half-luminance PWM step. This results in more shades of gray and a smoother transition out of black.</p>
<p> </p>
<p>In addition, the TH-42BT300U supports 3D playback for side-by-side and top + bottom color and exposure correction. Panasonic has also added automatic ’snap-to’ color space menu options, along with a user-definable color gamut option. When calibrated, it was an eye-catcher. There’s a 50-inch version also in the works, and both monitors will go on sale this fall.</p>
<div id="attachment_1218" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1218" href="http://www.hdtvexpert.com/?attachment_id=1218"><img class="size-full wp-image-1218" title="Sony PVM-E2541 OLED Monitor MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Sony-PVM-E2541-OLED-Monitor-MR.jpg" alt="" width="600" height="620" /></a><p class="wp-caption-text">Sony knows OLEDs. Make. Believe. (Nah, it was real...)</p></div>
<div id="attachment_1219" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1219" href="http://www.hdtvexpert.com/?attachment_id=1219"><img class="size-full wp-image-1219" title="Panasonic TH-42BT300U and TH-PF20U MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Panasonic-TH-42BT300U-and-TH-PF20U-MR.jpg" alt="" width="600" height="333" /></a><p class="wp-caption-text">Panasonic's TH-42BT300U (left) maps color accurately to the BT.709 color space, unlike its sibling the TH-42PF20U (right).</p></div>
<p><strong>Hyundai</strong> unveiled the B240X, a new 24″ passive stereo LCD monitor. It sports a 1920×1200 display with circularly-polarized film-patterned retarders and supports 3D side-by-side and top + bottom viewing formats. The pixel pitch is about .27 mm and brightness is rated at 300 nits. Hyundai also created an eye-catching 138″ (diagonal) 3×3 3D video wall for NAB, using its flagship S465D 46″ LCD monitor.</p>
<p> </p>
<p><strong>Sisivel</strong> has come up with a unique way to deliver higher-resolution 3D TV in the frame-compatible format. Instead of throwing away half the horizontal resolution for 1080i side-by-side 3D transmissions, Sisivel breaks the left eye and right eye images into two 1280×720 frames. The left eye frame is carried intact in a 1920×1080 transmission, while the right eye is broken up into three pieces – the top 50% of the frame, and two half-frames that make up the bottom.</p>
<p> </p>
<p>All of this gets packed in a rather unusual manner (see photo), but some simple video processing and tiling software re-assembles the right eye fragments into one image after decoding. Then, it’s a simple matter to sequence the lefty eye, right eye images as is normally done. The advantage of this format is that it has higher resolution than ESPN’s top+bottom 3D standard (two 1280×360 frames).</p>
<div id="attachment_1221" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1221" href="http://www.hdtvexpert.com/?attachment_id=1221"><img class="size-full wp-image-1221" title="Sisivel 3D frame-compatible demo WR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Sisivel-3D-frame-compatible-demo-WR.jpg" alt="" width="600" height="378" /></a><p class="wp-caption-text">So THAT's how you pack two 1280x720 3D frames into a 1920x1080 broadcast. Clever, eh?</p></div>
<p> </p>
<p><strong>JVC</strong> announced two LCD production monitors at NAB. The DT-V24G11Z is a 24-inch broadcast and production LCD monitor that uses 10-bit processing and has a native resolution of 920×1200 pixels. The extra resolution provides area above and below a 1080p image for metering, embedded captions, and signal status. The incoming signal can also be enlarged slightly to fill the entire screen.</p>
<p> </p>
<p>The DT-3D24G1Z is a 24-inch passive 3D monitor with circular polarization patterned films. It has 1920×1080 pixel resolution, 3G HD-SDI and dual-link inputs, a built-in dual waveform monitor and vectorscope, left eye and right eye measurement markers, and side-by-side split-screen display for post production work including gamma, exposure, and color/white balance correction.</p>
<p> </p>
<p>Nearby, crowds gathered to see two new 4K cameras that use a custom LSI for high bitrate HD signal processing. The demo used a Sharp 4K LCD monitor, and the cameras were running at 3840×2160 resolution. They have no model numbers or price tags yet.</p>
<p> </p>
<p><strong>Ikegami’s</strong> field emission display (FED) monitor that attracted so much attention a few NABs ago, but was written off when Sony pulled out its investment from the manufacturer, is now back. Its image quality compared favorably with Sony’s E-series BVM OLED monitors, and the images displayed with a wide H&amp;V viewing angle and plenty of contrast pop. It was being used to show images from a Vinten robotic camera mount at NAB, and no pricing has been announced.</p>
<div id="attachment_1222" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1222" href="http://www.hdtvexpert.com/?attachment_id=1222"><img class="size-full wp-image-1222" title="Ikegami FED Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Ikegami-FED-Demo-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Forget the Canon SED, Ikegami's got an FED! (A 'what?')</p></div>
<p><strong>Dolby</strong> showed their PRM-4200 42-inch HDR LCD reference monitor at NAB. While this product is not new, there was a substantial price cut announced at the show to $39,000.  Initial comments from the post production community have indicated the price is too high for today’s economic environment. As a result, Dolby has apparently sold a few to video equipment rental houses for location and studio production work.</p>
<p> </p>
<p>Digital SLRs are being used to shoot TV productions such as “House” and independent films, and they could use a couple of good monitors with hot shoe mounts. <strong>Nebtek</strong> had a 5.6” model at the show, as did <strong>TV Logic.</strong> Both models sport 1280×800 (WXGA) resolution, compatibility with HD-SDI and HDMI inputs, and have on-screen display of waveform/vectorscope details, focus assist, and chroma/luma signal warnings. Embedded audio from the cameras’ HDMI output can be displayed on screen, and there are several scan and pixel mapping modes.</p>
<p> </p>
<p>One of the more significant announcements at the show – at least, at first reading – was <strong>Verizon’s</strong> Digital Media Services. The idea is to serve as an electronic warehouse for everyone from content producers to digital media retailers – in effect, an Amazon e-commerce model, except that Verizon wouldn’t sell anything; merely ‘warehouse’ the assets and distribute them as need to whomever needs them.</p>
<p> </p>
<p>Numerous companies showed real-time MPEG encoders, among them <strong>Z3 Technology, Visionary Systems, Haivision, Vbrick, Adtec, Black Magic Designs</strong>, and (of all people) <strong>Rovi</strong>, otherwise known for their electronic program guide software. Many of these encoder boxes can accept analog video (composite and component) as well as HDMI and DVI inputs. The general idea appears to be ‘plug-and-play’ encoding for IPTV streaming across a broad range of markets. The Black Magic encoder was the cheapest I’ve seen to date at $500, while price ranges on other models ranged as high as $9,000.</p>
<div id="attachment_1224" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1224" href="http://www.hdtvexpert.com/?attachment_id=1224"><img class="size-full wp-image-1224" title="Tektronix WFM300 Anaglyph demo 1 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Tektronix-WFM300-Anaglyph-demo-1-MR.jpg" alt="" width="600" height="407" /></a><p class="wp-caption-text">A Tektronix monitor for color anaglyph 3D? REALLY?</p></div>
<div id="attachment_1225" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1225" href="http://www.hdtvexpert.com/?attachment_id=1225"><img class="size-full wp-image-1225" title="Sony HXR-NX70U WetCam Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/04/Sony-HXR-NX70U-WetCam-Demo-MR.jpg" alt="" width="600" height="537" /></a><p class="wp-caption-text">Do NOT let your children get any ideas from this photo...</p></div>
<p><strong>Tektronix</strong> had one of the funnier (unintentionally) demonstrations of test and monitoring gear. A new combination monitor, the WFM300, has a color anaglyph mode where you can see the interocular distance for red and cyan color anaglyph program material. Never mind the fact that color anaglyph isn’t being used for much of anything except printed 3D these days, so what were the folks at ‘Tek’ thinking?</p>
<p> </p>
<p>Finally, <strong>Sony </strong>showed they can be all wet but still on top of things with their demonstration of an HXR-NX70U 1080p camcorder operating normally while getting a pretty good hosing. The camera is completely water-sealed and dust-sealed for use in hostile environments, and records to internal hard disc drives and memory cards. The shower ran continuously during the show and the camera never even hiccupped. Fun stuff!</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April 20, 2011 10:25 AM</b>
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
			<?=getComments(4314)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4314)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/04/hdtv-expert-nab-2011-its-all-about-streaming-displays-and-connectivity.php" type="text/javascript" charset="utf-8"></script>
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