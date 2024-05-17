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
		AND e.entry_id = 1257";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1257 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1257 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1257";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2008/02/showdown-apple-tv-vs-vudu.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1257";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Showdown: Apple TV vs. VUDU" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Showdown: Apple TV vs. VUDU" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Showdown: Apple TV vs. VUDU" />
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
	<title>HDTV Magazine - Showdown: Apple TV vs. VUDU</title>
	<meta name="keywords" content="high definition, movie rentals, video quality, able watch, peer peer, apple, vudu, movie, content, movies, high, video, box, definition, top, see, quite, purchase, titles, new, rentals, quality, well, time, source" />
	<meta name="description" content="So you've probably read that Apple is getting into the movie rental business, and with high definition even. With the recent upgrades to their Apple TV unit and addition of movie rentals, Apple is now positioned toward the top of the heap when it comes to online high definition movie rentals. Another company that is at the top of that heap is VUDU, who also has a hardware-based player that connects directly to the TV just like the Apple TV unit. I thought it would be appropriate to pit these two against each other and see which comes out on top." />
	<meta name="title" content="Showdown: Apple TV vs. VUDU" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Showdown: Apple TV vs. VUDU" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2008/02/showdown-apple-tv-vs-vudu.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="So you've probably read that Apple is getting into the movie rental business, and with high definition even. With the recent upgrades to their Apple TV unit and addition of movie rentals, Apple is now positioned toward the top of the heap when it comes to online high definition movie rentals. Another company that is at the top of that heap is VUDU, who also has a hardware-based player that connects directly to the TV just like the Apple TV unit. I thought it would be appropriate to pit these two against each other and see which comes out on top." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1257', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2008/02/showdown-apple-tv-vs-vudu.php">Showdown: Apple TV vs. VUDU</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>February 21, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=310&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="editorial">Welcome to a new feature here at HDTV Magazine. The "Showdown" column will pick two competing products or technologies and pit them against each other head-to-head to see which one comes out on top. If you have ideas for this new column, or products you'd like to see compared, please <a href="/help/feedback.php" target="_blank">let us know</a>.</p> <p style="vertical-align: middle" align="center"><a href="http://www.apple.com/appletv" target="_blank"><img style="margin: 0px 0px 5px 5px" height="40" alt="image13" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image13.png" width="133" border="0"></a><strong><font size="5">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; vs&nbsp;&nbsp;&nbsp; </font></strong> <a href="http://www.vudu.com/" target="_blank"><img style="margin: 0px 0px 5px 5px" height="40" alt="VUDU" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_8.png" width="80" border="0"></a> </p> <p>So you've probably read that <a href="http://www.apple.com/pr/library/2008/01/15itunes.html" target="_blank">Apple is getting into the movie rental business</a>, and with high definition even. With the <a href="http://www.apple.com/pr/library/2008/01/15appletv.html" target="_blank">recent upgrades to their Apple TV unit</a> and addition of movie rentals, Apple is now positioned toward the top of the heap when it comes to online high definition movie rentals. Another company that is at the top of that heap is VUDU, who also has a hardware-based player that connects directly to the TV just like the Apple TV unit. I thought it would be appropriate to pit these two against each other and see which comes out on top.</p> <p>Please note that this Showdown is comparing these two services for the purposes of movie viewing alone. I realize that there are other features available with the Apple TV like synchronizing music, photos, etc., but we will not cover those. If those additional features are important to you then you may not find as much value in this comparison as those just looking for a movie player.</p> <p>My viewing environment for this comparison was as follows:</p> <ul> <li>TV: 46" Samsung 1080p LCD  <li>Internet connection: DSL @ 4 Mbps actual (6 Mbps advertised)  <li>Viewing distance: Approximately 8 ft.</li></ul> <p>Let the Showdown begin...</p> <h2>Apple TV</h2> <p><a href="http://www.apple.com/appletv" target="_blank"><img style="margin: 0px 0px 5px 5px" height="96" alt="Apple TV Image" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_3.png" width="200" align="right" border="0"></a> I looked at Apple TV 6 months ago for the purposes of evaluating it as a movie player, and was fairly disappointed. At the time, there was no high definition content and you had to use iTunes on a PC or Mac to purchase and download your content, then either stream it or sync it to the Apple TV. Also, their user interface was rather unimpressive, quite unlike Apple, and did not lend itself well to video storage and content discovery.</p> <p>Apple announced at Macworld in January that an update would be coming for the Apple TV product and for iTunes that would allow for movie rentals from all major studios as well as support for high definition. Well, the Apple TV update has finally arrived and I must say: I am impressed. The changes made in this latest update are monumental.</p> <p>The Apple TV unit is a set top box that connects to your TV via HDMI or component video. It can output high definition at up to 1080p/24 fps, but the <a href="http://www.apple.com/appletv/specs.html" target="_blank">tech specs according to Apple</a> limit the source video resolution to 720p/24. With this latest update from Apple, you can now rent movies directly from the Apple TV itself ... no syncing with a computer is required. In fact, it doesn't appear as though you can rent HD via iTunes at all, it's only available through the Apple TV unit.</p> <p>The interface is much-improved, allowing you to now select from among several relevant top-level categories that contain exactly what you might expect: "My Movies", "Rented Movies", "All HD", "Search", etc. </p> <p><img style="margin: 0px 0px 5px 5px" height="281" alt="image" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_4.png" width="500" border="0"> </p> <p>Included on each movie detail page is also a list of what other users rented who also rented that particular title, but missing is the ability to see other titles in the library by diving into the actor or director from each title. So the content discovery features could be expanded greatly.</p> <p><img style="margin: 0px 0px 5px 5px" height="281" alt="image" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_5.png" width="500" border="0"> </p> <p>The Apple TV library will consist of over 1,000 titles by the end of February including over 100 in high definition. The content quality was quite mixed. I tested out some TV episodes of "The Unit" in SD, and they were sub-DVD quality. For SD movies, I tested out "Shooter", which had quite good video quality, equivalent to DVD. For HD content, I tested out "Transformers", and it was excellent. It did take quite a while to download though. Once it reached 7% downloaded (about 15 minutes in), it let me start playing the film. The video quality was much better than DVD, although not quite as good as HD DVD and/or Blu-ray.</p> <p>Apple recently reduced the price of its Apple TV boxes to $229 for the 40 GB model and $329 for the 160 GB model. The content for purchase (own) ranges from $1.99 for TV episodes (in SD) to between $9.99 and $14.99 for newly released movies. Content for rent is $2.99 for library titles and $3.99 for new releases. For high definition, it's $3.99 for library titles and $4.99 for new releases.</p> <h2>VUDU</h2> <p><a href="http://www.vudu.com/" target="_blank"><img style="margin: 0px 0px 5px 5px" height="108" alt="VUDU Image" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_6.png" width="200" align="right" border="0"></a> In September 2007, there was a new entry into the movie download market: <a href="http://www.vudu.com/" target="_blank">VUDU</a>. Like the Apple TV service, VUDU is implemented as a hardware device that you connect directly to your television. VUDU has movies available for both purchase and rental. The set-top box is capable of outputting video at up to 1080p/24, and they now have about 100 movies available in high definition out of their library of over 5,000 movies. The set top box is $295, which allows you to store roughly 50 HD movies and unlimited rentals. Rentals are reasonably priced from $0.99 up to $5.99 for high definition new releases. Content for purchase (own) ranges from $4.99 up to $24.99 for high definition. </p> <p>Whether you rent or buy, you may be able to watch the movie immediately if your internet connection is fast enough because VUDU utilizes peer-to-peer (P2P) technology to distribute movie and TV content. With P2P, instead of downloading from a central server, the box downloads segments of the movie from other VUDU boxes connected to the internet.</p> <p>P2P can be difficult to explain, but let's use a "water glass" analogy: Let's imagine you have an empty glass which you want to fill up with water. Getting a complete glass of water from a single faucet works fine, taking maybe 5 seconds to fill to the top. Not too bad, but what if 100 people wanted to fill their glass? With peer to peer, you would have hundreds, even thousands, of faucets. You could not only use more than one faucet to fill your glass, but you also wouldn't necessarily have to wait for other people to fill theirs first. </p> <p>As you can see, the main benefit of P2P distribution is in its distributed delivery of your movie. You are not relying on a single server (or group of servers) to have enough horsepower to deliver your movie along with hundreds or thousands of other simultaneous customers. This means that VUDU can scale quite well to much larger audiences without significant investments on the delivery side (servers, bandwidth, etc.), making it theoretically more future-proof than a client-server system.</p> <p>Also with VUDU, every single movie on their "system" has a starter stub stored on your box already, roughly the first 30 seconds of every movie. When you buy, or rent, a movie that starter stub begins playing while the box connects to dozens of other VUDU boxes on the internet to download the subsequent segments of the movie.</p> <p>The video quality was excellent, even for SD fare. All of their content is encoded at 24 fps. SD video is 480p/24 and encoded with H.264 Main Profile while all HD content is 1080p/24 encoded with H.264 High Profile. In all honesty, when I first began testing the unit with SD programming back in November, it was not obvious to me that what I was watching <strong>wasn't</strong> HD. I've looked at all the major players in the movie download market, and the quality they are getting with their SD video is unsurpassed. And the quality of their HD content rivals that of packaged media, although I'm sure it would not hold up to a side-by-side test.</p> <p>Their user interface is flawless. This system is so easy to use, I can put the remote in just about anyone's hand and they won't have a single question about what to do next. As advertised, their content begins playing immediately. Even for HD content, all that is needed is a 4 Mbps connection to be able to watch HD content instantly. If you'd like to give your ISP a test drive and see if you'd be able to watch instantly, VUDU has a <a href="http://speedtest.vudu.com/cdn1/" target="_blank">speed test</a> that will rate your connection throughput.</p> <p><img style="margin: 0px 0px 5px 5px" height="296" alt="image" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_7.png" width="500" border="0"> </p> <p>The other thing VUDU does very well is discovery. Their interface allows for you to navigate through and dig deeper into movies from lists of actors and directors associated with each movie, as well as "Similar Movies" by genre. VUDU almost makes it too easy to find something to watch or add to your Wish List.</p> <h2>Comparisons</h2> <p>So how to the tech spec tables stack up? I've listed the pertinent information below, and highlighted where these boxes differ significantly:</p> <table class="type1b" cellspacing="0" cellpadding="2" width="617" border="0"> <tbody> <tr> <td class="type1b_header" width="226">&nbsp;</td> <td class="type1b_header" width="156">Apple TV <sup>1</sup></td> <td class="type1b_header" width="233">VUDU</td></tr> <tr> <td class="grid" width="226">Hardware Price (entry level)</td> <td class="grid" width="156"><strong>$229</strong></td> <td class="grid" width="233">$295 <sup>2</sup></td></tr> <tr> <td class="grid" width="226">Capacity</td> <td class="grid" width="156">40 GB</td> <td class="grid" width="232"><strong>250 GB</strong></td></tr> <tr> <td class="grid" width="226">Buy-to-watch time</td> <td class="grid" width="156">~15 minutes</td> <td class="grid" width="232"><strong>instant</strong></td></tr> <tr> <td class="grid" width="226">HD Movie Rentals (New Release)</td> <td class="grid" width="156"><strong>$4.99</strong></td> <td class="grid" width="231">$5.99</td></tr> <tr> <td class="grid" width="226">HD Movie Rentals (Catalog)</td> <td class="grid" width="156">$3.99</td> <td class="grid" width="231">$3.99</td></tr> <tr> <td class="grid" width="226">HD Movie Purchase (New Release)</td> <td class="grid" width="156">N/A</td> <td class="grid" width="231">$24.99 <sup>3</sup></td></tr> <tr> <td class="grid" width="226">HD Movie Purchase (Catalog)</td> <td class="grid" width="156">N/A</td> <td class="grid" width="231">N/A</td></tr> <tr> <td class="grid" width="226">Major Studios Supported</td> <td class="grid" width="156">All</td> <td class="grid" width="231">All</td></tr> <tr> <td class="grid" width="226">Rental window (once purchased)</td> <td class="grid" width="156">30 days</td> <td class="grid" width="231">30 days</td></tr> <tr> <td class="grid" width="226">Rental window (once started)</td> <td class="grid" width="156">24 hours</td> <td class="grid" width="231">24 hours</td></tr> <tr> <td class="grid" width="226">Source Resolution</td> <td class="grid" width="156">480p, 720p</td> <td class="grid" width="231"><strong>480p, 1080p</strong></td></tr> <tr> <td class="grid" width="226">Source Frame Rate</td> <td class="grid" width="156">24 fps</td> <td class="grid" width="231">24 fps</td></tr> <tr> <td class="grid" width="226">Source Video Encoding</td> <td class="grid" width="156">H.264</td> <td class="grid" width="231">H.264</td></tr> <tr> <td class="grid" width="226">Encoding Profile</td> <td class="grid" width="156">Main Profile</td> <td class="grid" width="231">Main Profile (SD), <strong>High Profile (HD)</strong></td></tr> <tr> <td class="grid" width="226">Source Audio Formats</td> <td class="grid" width="156">DD5.1</td> <td class="grid" width="231"><strong>DD+, DD5.1</strong></td></tr> <tr> <td class="grid" width="226">Max Audio Output</td> <td class="grid" width="156">DD5.1 (pass-through)</td> <td class="grid" width="231">DD5.1 (pass-through)</td></tr> <tr> <td class="grid" width="226">HD Selection</td> <td class="grid" width="156">~100 titles</td> <td class="grid" width="231">~100 titles</td></tr> <tr> <td class="grid" width="226">SD Selection</td> <td class="grid" width="156">~1,000 titles</td> <td class="grid" width="231"><strong>~5,000 titles</strong></td></tr> <tr> <td class="grid" width="226">Download technology</td> <td class="grid" width="156">Client/Server</td> <td class="grid" width="231"><strong>Peer-to-peer</strong></td></tr> <tr> <td class="grid" width="226">USB Ports</td> <td class="grid" width="156">1</td> <td class="grid" width="231"><strong>2</strong></td></tr> <tr> <td class="grid" width="226">Remote Type</td> <td class="grid" width="156">IR</td> <td class="grid" width="231">RF</td></tr></tbody></table> <p><font size="1">1 - Source: <a href="http://www.apple.com/appletv/specs.html" target="_blank">Apple TV Tech Specs</a><br></font><font size="1">2 - VUDU is currently offering <em>The Bourne Identity</em> and <em>The Bourne Supremacy</em>, both in HD, free with hardware purchase<br>3 - The only HD movie that VUDU had for purchase at the time of publication was <em>The Bourne Ultimatum</em>.</font></p> <h2>Conclusions</h2> <p>Apple has done quite well across the board with this latest update. I would like to see native 1080p transfers as well as enhanced audio support. Their HD selection is quite good, but their SD selection is a bit less than VUDU. The usability is as one would expect from Apple, very refined. It could use a few improvements in the area of content discovery, like selecting actors or directors to see other movies they've done. Lastly, while a 15 minute wait time is probably acceptable to most, it would be better if it was a bit quicker.</p> <p>VUDU slightly edges out Apple TV in every category except for price. The video quality is better with VUDU supporting 1080p vs. Apple TVs 720p. The buy-to-watch time for Apple TV is about 15 minutes, whereas with VUDU it is instant. Lastly, VUDU's selection of 5,000+ movies dwarfs Apple TVs 1,000, although they both have about the same number of HD offerings at 100-ish. Given time, this will level out. The one area where Apple TV has an advantage is price: HD movies from Apple are $4.99 vs. $5.99 from VUDU. The low-end Apple TV box is $229, which is less than the low-end VUDU box at $295. So the entry point is lower for Apple TV, but you only get 40 GB of storage in the Apple TV box vs. 250 GB in the VUDU box.</p> <p><strong>Why choose Apple TV?:</strong> Less expensive for both hardware and HD rentals.</p> <p><strong>Why choose VUDU?:</strong> Better resolution, faster access to movies and a more scalable platform. Also a better choice if you prefer to own, rather than rent, digital movies.</p> <p>&nbsp;</p> <p align="center"><strong><font size="5">Winner:</font></strong></p> <p align="center"><a href="http://www.vudu.com/" target="_blank"><img style="margin: 0px 0px 5px 5px" height="40" alt="VUDU" src="http://www.hdtvmagazine.com/images/test/ShowdownVUDUvs.AppleTV_FF4E/image_9.png" width="80" border="0"></a> </p> <p align="center">&nbsp;</p></tbody></table>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>February 21, 2008  9:09 AM</b>
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
			<?=getComments(1257)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1257)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/02/showdown-apple-tv-vs-vudu.php" type="text/javascript" charset="utf-8"></script>
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