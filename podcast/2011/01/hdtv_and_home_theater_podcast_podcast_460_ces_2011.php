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
		AND e.entry_id = 4162";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4162 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4162 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4162";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2011/01/hdtv-and-home-theater-podcast-podcast-460-ces-2011.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4162";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #460: CES 2011" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #460: CES 2011" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #460: CES 2011" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #460: CES 2011</title>
	<meta name="keywords" content="next generation, connected tvs, showed next, card slot, consumer electronics, ”, showed, show, pretty, android, available, specs, connected, glasses, well, see, generation, good, really, projector, tablet, next, samsung, our, live" />
	<meta name="description" content="It's hard to believe that another year has gone by and we're back on our annual trek to Las Vegas to pay homage to the consumer electronics industry.  The Consumer Electronics Show is always a fun adventure, showcasing some of both the best and the most peculiar devices imaginable.  Last year we were bombarded with 3D; this year it was all about connected TVs and tablets." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #460: CES 2011" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #460: CES 2011" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2011/01/hdtv-and-home-theater-podcast-podcast-460-ces-2011.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It's hard to believe that another year has gone by and we're back on our annual trek to Las Vegas to pay homage to the consumer electronics industry.  The Consumer Electronics Show is always a fun adventure, showcasing some of both the best and the most peculiar devices imaginable.  Last year we were bombarded with 3D; this year it was all about connected TVs and tablets." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4162', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2011/01/hdtv-and-home-theater-podcast-podcast-460-ces-2011.php">HDTV and Home Theater Podcast - Podcast #460: CES 2011</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>January  7, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=444&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>CES 2011</h3>
<p>It&#8217;s hard to believe that another year has gone by and we&#8217;re back on  our annual trek to Las Vegas to pay homage to the consumer electronics  industry.  The Consumer Electronics Show is always a fun adventure,  showcasing some of both the best and the most peculiar devices  imaginable.  Last year we were bombarded with 3D; this year it was all  about connected TVs and tablets.</p>
<p>For pictures of everything we saw and did, check out the <a href="http://www.htguys.com/ces2011">live blog</a> Ara was updating as we walked the show:  <a href="http://www.htguys.com/ces2011">http://www.htguys.com/ces2011</a></p>
<p>Some of our favorite stuff came from these companies:</p>
<p><strong>Vivitek</strong></p>
<p>Vivitek  showed a projector capable of converting any 2D content to 3D.  Kinda  neat, but we wouldn&#8217;t run out and buy one.  They also showed a <a href="http://www.htguys.com/ces2011/2011/1/6/worlds-smallest-hd-projector.html">tiny HD projector</a> called the <a href="http://eon.businesswire.com/news/eon/20110106005103/en/Vivitek-Enters-Pico-Projector-Category-LED-Based-Pocket">Qumi</a> capable of 300 lumen output.  It will sell for under $500 and looks like a fun little device.  Here are the specs:</p>
<ul>
<li> Native WXGA (1280&#215;800) resolution</li>
<li> Up to 300 lumens of brightness and a 2500:1 contrast ratio</li>
<li> 3D-Ready (via DLP Link) and a DLP chipset from Texas Instruments</li>
<li> 1.6 pounds; Dimensions: 6.3”(w) x 1.2”(h) x 3.9”(d)</li>
<li> 30,000+ estimated LED hours of operation</li>
<li> Connectivity options include: Mini-HDMI,  Universal I/O (24-pin          connector for VGA/component input), 3.5mm  video-in, 3.5mm mini-jack          audio-out</li>
<li> USB and MicroSD slots for content and memory expansion</li>
<li> Built-in 1W speaker</li>
<li> Offered in a black or white finish</li>
<li> Available in May 2011; Estimated street price of $499.00(USD)</li>
</ul>
<p><strong>Optoma</strong></p>
<p>In addition to the HD projectors Optoma is known for, they also showed a portable <a href="http://www.htguys.com/ces2011/2011/1/6/visiting-optoma-projectors.html">projector/iPhone dock combo unit</a> that looks good.  It&#8217;s basically a pico projector with an iPhone/iPod dock and some built-in speakers.  Called the <a href="http://ces.cnet.com/8301-32254_1-20027075-283.html">Neo-i</a>, it should be available soon and sell for under $500.  And the specs:</p>
<ul>
<li>Native Resolution:	854 X 480 (16:9) WVGA DLP</li>
<li>Brightness:		50 lumens Bright mode</li>
<li>Contrast Ratio:	2000:1</li>
<li> LED / Life:		RGB LED, &gt;20,000hrs</li>
<li>Dimensions:		12.8” x 8.9” x 3.1”</li>
<li>Weight:		&lt; 2.5 lbs</li>
<li> Image Size:		5”~120” (16:9 native)</li>
<li>Projection Lens:	Fixed Focus Lens, F/1.8</li>
<li>Throw Ratio:		1.8</li>
</ul>
<p><strong>JVC</strong></p>
<p>JVC had a <a href="http://www.htguys.com/ces2011/2011/1/6/ht-guys-in-3d.html">great demo of a 3D camcorder</a> displaying live on a big 3D LCD TV.  It was one of the more fun demos  to participate in.  Reaching out at the camera actually came out of the  TV.  One of the better uses of 3D at the show, but still active glasses,  so be prepared to shell out $100-$200 per person to show your home  movies to friends and family.<br />
<strong>Panasonic</strong></p>
<p>Panasonic showed their next generation plasma panels, the <a href="http://www.htguys.com/ces2011/2011/1/6/panasonic-was-showing-off-their-new-vt30-series.html">VT30 line</a>,  at the show.  They won&#8217;t be available until summer 2011, but they  looked excellent.  No word on pricing, but still available in 55&#8243; and  65&#8243; models, nothing bigger on the horizon.  Specs:</p>
<ul>
<li>3D compatible</li>
<li>One pair of 3D glasses included</li>
<li>THX certification</li>
<li>Infinite Black Pro 2 panel</li>
<li>1080p/24 playback mode</li>
<li>Short throw phosphors</li>
<li>3D-compatible SD card slot</li>
<li>Viera Connect Internet suite</li>
</ul>
<p><strong>LG</strong></p>
<p>LG had two concepts that really grabbed our attention.  First, they  were showing a whole slew of connected TVs that are somehow based on the  Plex media server.  Although LG didn&#8217;t say the software was based on  Plex, they did say that any app written for Plex would work the the <a href="http://www.htguys.com/ces2011/2011/1/6/lg-is-all-about-smart-tvs.html">LG Smart TV</a> technology.  You can draw your own conclusions.</p>
<p>LG also showed our favorite use of 3D at the show &#8211; passive glasses!   The 3D actually looked pretty good.  It wasn&#8217;t as good as the 3D on a  Panasonic plasma, but let&#8217;s be fair, what is?  It was pretty good,  pretty smooth and very cheap glasses.  They were giving them away like  candy.  Now there&#8217;s a way to make 3D appealing to the masses.</p>
<p>LG showed a glasses-free 3D demo as well.  Standing in the right  spot, it actually did pretty well.  But off angle viewing was rough.</p>
<p><strong>Samsung</strong></p>
<p>Samsung was on the <a href="http://www.htguys.com/ces2011/2011/1/6/more-smart-tvs.html">connected TV</a> bandwagon as well.  They showed a pretty cool integration of a lot of  the existing apps out there.  The next generation of Samsung connected  TVs and Blu-ray players will allow you to search for a movie and see  every service where it&#8217;s available, and the price, across services like  Netflix, Vudu, Blockbuster, Amazon, etc.  It lets you pick what you are  willing to pay and what quality you&#8217;d like to see it in.  There&#8217;s also  some social integration aspects that allow you to get recommendations  from friends and the like.</p>
<p>Of course Samsung is also in the Android phone and tablet game, so  that was on display as well.  Braden, being the Android fan boy that he  is, thought the Galaxy Player puts the iPod touch to shame.  Of course,  Ara doesn&#8217;t see it.  It&#8217;s available in Korea with no current plas to  bring it to the US.<br />
<strong>Motorola</strong></p>
<p>Motorola has some really big Android announcements.  They announced  two next generation android phones, both with dual 1Ghz processors.  One  for AT&amp;T and one for Verizon.  And of course everyone is still  waiting for a real, honest iPad competitor, so Motorola is hoping they  found that with their <a href="http://www.htguys.com/ces2011/2011/1/6/motorola-xoom-tablet-video.html">Xoom tablet</a>.   It&#8217;s the first Android 3.0 device and boasts some pretty impressive  specs.  Should be out this quarter on the Verizon network, but only on  3G;  4G comes later this year.  Specs:</p>
<ul>
<li>Android 3.0</li>
<li>10.1 inch display</li>
<li>Dual Core 1 Ghz CPU NVIDIA Tegra 2</li>
<li>1GB RAM</li>
<li>32 GB inbuilt memory with microSD Card slot</li>
<li>1280&#215;800 resolution  display</li>
<li>5 megapixel camera with 720p video recording</li>
<li>2 megapixel webcam</li>
<li>1080p HDMI output</li>
</ul>
<p><strong>Yahoo!</strong></p>
<p>Yahoo! showed the next generation of their TV widget offerings, but  these widgets are alive.  They can interact with the content on live TV  to really meld the traditional broadcast TV format with the new,  connected TV phenomenon.  This could be exciting, or it could be really  annoying, we&#8217;ll have to wait and see.</p>
<p><strong>Intel</strong></p>
<p>Everyone is in the tablet game, and Microsoft and Intel had to get into it as well.  Check out a video of the tablet from the <a href="http://www.htguys.com/ces2011/2011/1/6/hands-on-with-the-intel-tablet.html">live blog</a>.</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2011-01-07.mp3">Download Episode #460</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>January  7, 2011  2:05 AM</b>
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
			<?=getComments(4162)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4162)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2011/01/hdtv-and-home-theater-podcast-podcast-460-ces-2011.php" type="text/javascript" charset="utf-8"></script>
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