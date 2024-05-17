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
		AND e.entry_id = 4781";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4781 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4781 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4781";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/04/hdtv-and-home-theater-podcast-podcast-527-the-hopper-by-dish.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4781";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish</title>
	<meta name="keywords" content="blockbuster home, hopper joeys, pretty cool, hopper joey, recorded content, hopper, dish, get, home, joey, remote, dvr, system, content, joeys, don’t, using, pretty, playback, ’s, shows, need, connect, blockbuster, want" />
	<meta name="description" content="Sometimes it&amp;acirc;��s OK to arrive fashionably late. It doesn&amp;acirc;��t always matter when you arrive to the party, what matters is what you bring with you when you get there. Dish Network has proven that waiting can pay off. Their recent entry into whole home DVR, the Hopper, brings some pretty cool features to the party." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/04/hdtv-and-home-theater-podcast-podcast-527-the-hopper-by-dish.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sometimes it&amp;acirc;��s OK to arrive fashionably late. It doesn&amp;acirc;��t always matter when you arrive to the party, what matters is what you bring with you when you get there. Dish Network has proven that waiting can pay off. Their recent entry into whole home DVR, the Hopper, brings some pretty cool features to the party." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4781', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/04/hdtv-and-home-theater-podcast-podcast-527-the-hopper-by-dish.php">HDTV and Home Theater Podcast - Podcast #527: The Hopper by Dish</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>April 19, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=459&category=Satellite HDTV">Satellite HDTV</a></b>
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
<h3>The Hopper by Dish</h3>
<p>Sometimes it’s OK to arrive fashionably late. It doesn’t always matter when you arrive to the party, what matters is what you bring with you when you get there. Dish Network has proven that waiting can pay off. Their recent entry into whole home DVR, the <a href="http://www.mydish.com/upgrades/products/hopper/">Hopper</a>, brings some pretty cool features to the party.</p>
<h4>The System</h4>
<p>The new whole home DVR system from Dish Network is comprised of a main DVR unit, called the Hopper, and additional playback units for other rooms called Joeys.  You can connect up to three Joeys to one Hopper, providing High Def viewing in 4 rooms.  If you need more, you can add another Hopper and three more Joeys with it.</p>
<p>Physically, the Hopper is a bit smaller than Dish’s other DVR units, like the ViP 722, but what’s inside is actually much, much bigger.  It has three high def tuners and is capable of recording up to six high definition programs simultaneously. Yes, all six are recorded in high definition. More on how that works later. To accommodate that capability, Dish has included a 2 TB drive that can store 2000 hours of programming, or 500 hours of HD programming (as of today’s update, it was 250 hours yesterday). It also has one eSATA and two USB ports to allow for more storage.</p>
<p>The Hopper isn’t just a Dish receiver, though. It supports DLNA, so it can stream your local music or movie collection without the need for another player in your home theater. Now that Dish owns Blockbuster, it also has a built in connection to <a href="http://www.mydish.com/upgrades/blockbuster/">Blockbuster@Home</a> movies, so you can watch just about anything you want. New releases are available for rental like Vudu and many catalog titles are free, like Netflix. It even has a few Internet apps built right in.</p>
<p>The Joey is even smaller than the Hopper, a little smaller than a VHS cassette (remember those?), but it boasts all the same features, except for the tuners and hard drive of course. But using the Joey is no different than using the Hopper itself, you get direct access to the tuners and all recorded content. You can create recordings, delete recordings, see what others are watching on the other tuners, anything you want.</p>
<h4>Installation and Setup</h4>
<p>The Hopper and Joeys use MoCA to communicate and playback video over the existing coax lines in your house. To get the system installed, you need to connect a MoCA box where the satellite feed enters the house and use it to connect the Hopper and its Joeys. It takes each box about 15-20 minutes  and a reboot or two to get initialized and connected. If you ignore the time it takes for the guide to fully populate, it took about two hours for us to get a full system, Hopper and three Joeys, up and running. You’ll probably be working with an installer, so installations times can vary quite a bit.</p>
<p>In addition to the MoCA connection, if you want to access local content from your network, stream movies using Blockbuster @Home or take advantage of the built-in Internet apps, you need to connect the Hopper to your network and the Internet using one of the Ethernet ports in the back. It has two, but evidently the second one is for future functionality. Once connected, the Joeys can get to almost everything over the existing MoCA connection &#8211; everything except DLNA. To get that you have to connect the Ethernet port on the Joeys as well.</p>
<p>If you don’t have Ethernet available at all your Hopper and Joey locations Dish has a device called the Hopper Internet Connector (HIC) that can act as a bridge between your home network and the Hopper/Joey MoCA system. We don’t have one, but we’re trying to track one down. We’ll update the review when we’ve been able to play around with it.</p>
<p>Supposedly you can also use the HIC to bypass the MoCA connection entirely and stream live and recorded content to the Joey using just an Ethernet connection. This would add tremendously to the versatility of installation. If you want to run the Joey to a location that has Ethernet but no coax, you should be just fine. According to dish, this is an unsupported and untested feature. We’ll try to make it work when we get our hands on an HIC.</p>
<p>One of the early setup steps is pairing a remote with each box. The remotes use IR and ZigBee to communicate, so they have to be paired up. The front of each box has a Remote Locator button that you can use to find the remote should it fall behind the couch cushions or something. You can also use the remote as a Universal, using the actual onscreen UI to program it. The system has remote codes for just about any device you can imagine.</p>
<h4>Using the System</h4>
<p>The user interface on the Hopper is excellent. It is very easy to use and very snappy. Lag times we’re used to seeing with other DVRs for loading or scrolling through a guide, doing a search, or sometimes even just changing a channel, and greatly reduced on the Hopper. The interface is very intuitive. Anyone familiar with a DVR will have no problem navigating the Hopper. The Joey interface is exactly the same as the Hopper. Anything you can do on the Hopper, you can do on the Joey.</p>
<p>The search feature is pretty cool. It performs a live search as you type, narrowing down the results with each letter.  You don’t have to type in the whole title or description, hit enter and let it work for a few minutes to hopefully come back with something.  You’ll usually be able to see what you’re looking for within the first few characters. The search even includes content from Blockbuster@Home, so it’s easy to find movies from a wide variety of sources.</p>
<p>Recordings can be organized into folders so you can provide your own filtering to easily get to the shows you want, without wading through everyone else’s recorded content. The shows themselves are displayed in a layout similar to a movie server with cover art instead of a simple text based list of recordings. It makes the interface a little better to look at and you don’t really lose anything. 15 shows at a time are visible on screen at once, which is the same or more than you’d get from a list style display.</p>
<p>By far the best feature of the Hopper, the one that really sets it apart, is PrimeTime Anytime. Once enabled, the Hopper will use one of its three tuners to record all prime time programming from the big four networks, ABC, CBS, NBC and FOX in high definition. That leaves two tuners for live content or to record other shows on other networks &#8211; that’s how you can get the six shows at once. If you have two shows you record at the same time on two different networks, like Hawaii Five-0 on CBS and Castle on ABC, it still uses just one tuner.</p>
<p>How many times have you missed a pilot or premiere of a new show because you forget to set the DVR in time? With PrimeTime Anytime, you get it automatically. If you like it, you can add it to your list of shows to save on the DVR. If not, it will automatically be deleted in a few days along with the rest of the PrimeTime Anytime recordings you don’t care about. The recording themselves are segregated into their own DVR area so you don’t have to worry about them cluttering up what you actually want to record and save.</p>
<p>Blockbuster@Home is pretty cool as well. You get a whole library of movies available for rent, just like Vudu, built right into the DVR. The rentals cost a little more, up to $6.99 for 1080p. We didn’t pay for any rentals, but the selection of free titles was pretty good, so we sampled Thor and Transformers: Dark of the Moon, both in HD. We were impressed with the video quality, as good as anything we’ve seen other than Vudu HDX. Unfortunately the sound was only in stereo.</p>
<p>DLNA playback was a little more hit and miss. The Hopper and Joey didn’t always see all our DLNA servers, in fact sometimes they didn’t even see the same servers. When they did connect to a server, file playback wasn’t 100%. If a file format was supported, playback looked great, but there were a lot of files that we couldn’t play. Dish only claims support for MP4 and MKV containers, we didn’t have enough time to figure out exactly what codecs work within those containers. Based on what we were able to playback, though, once you determine what DLNA servers they like and what file formats they support, we’re confident you could build a pretty decent video server system using just the Hopper and Joeys as players.</p>
<h4>Other bits and pieces</h4>
<p>Because of the ZigBee support, programming the remotes is actually pretty cool. You pull up the correct option in the menu directly on your TV, then you tell the system what kind of TV, DVD player or third device, typically a receiver, you have and it programs the correct IR codes for you automatically. It’s even easier than programming a Harmony remote. Although it doesn’t have macro support, it will punch buttons like volume through to whatever device you tell it to control volume on (TV or Aux) regardless of which device you’re controlling on the remote. If you don’t have a universal in a secondary room, the included remote does pretty much everything you need.</p>
<p>Along the lines of Universal remotes, the IR codes are mostly the same between the Hopper and the older Dish DVRs, so your universal remote will be pretty close to fully functional without any modifications. They did add a few buttons on the new remote, so it makes sense to update your universal, but right out of the box you’ll probably be able to do most of what you need without having to reprogram.</p>
<p>Each Joey comes with a bracket and screws for mounting it to the wall.  Because of the ZigBee support, you can mount it behind a flat panel and not worry at all about line of sight for the IR commands.   In secondary rooms that may not have surround sound or a Blu-ray player, you can get access to just about any content you&#8217;re interested in with just a simple Joey mounted behind your plasma or LCD TV.</p>
<p>You can also connect a <a href="http://www.dish.com/technology/tv-everywhere/">Sling Adapter</a> to the Hopper to add built-in Slingbox technology. It allows you to control the DVR and stream content natively, without having to hijack video outputs or proxy IR commands. What’s more, the Dish app for remote playback on a smartphone or tablet is free, unlike the somewhat high cost you pay for the standard Slingbox app.</p>
<p>When we first wrote up the review on the system, we were going to tell you that the Internet apps were as pointless as any other Internet apps.  Of course they don’t have the ones you really want, like Netflix and Vudu because of Blockbuster@Home.  And beyond that, there really wasn’t anything in there that mattered&#8230;until today. Dish sent out a press release about the availability of Pandora playback. We got the update on our Hopper and it works really well. The update won’t be available for the Joey until June-ish.</p>
<p>The update also expanded the storage space reserved for user recorded content from 500 GB to 1 TB</p>
<h4>Conclusion</h4>
<p>The whole home DVR space is getting a bit crowded, but Dish has found a way to crash the party and not look like an also-ran. The addition of PrimeTime Anytime really sets it apart. That’s an idea we’re sure others will try to copy, if Dish doesn’t have it protected in some way. The inclusion of Blockbuster@Home, especially the integration into the search functionality, also eliminates the need for more apps like Netflix, Vudu, Amazon or iTunes. Dish has found a way to breathe new life into the party, and we’re diggin’ it.</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-04-20.mp3">Download Episode #527</a></p>
<p>&nbsp;</p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>April 19, 2012 10:27 PM</b>
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
			<?=getComments(4781)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4781)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/04/hdtv-and-home-theater-podcast-podcast-527-the-hopper-by-dish.php" type="text/javascript" charset="utf-8"></script>
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