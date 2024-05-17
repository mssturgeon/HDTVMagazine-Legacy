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
		AND e.entry_id = 1412";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1412 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1412 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1412";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/05/hdtv-and-home-theater-podcast-279-home-theater-memory-lane.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1412";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #279 - Home Theater Memory Lane" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #279 - Home Theater Memory Lane" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #279 - Home Theater Memory Lane" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #279 - Home Theater Memory Lane</title>
	<meta name="keywords" content="home theater, been replaced, flat panel, convergence device, video rental, home, theater, video, internet, technology, dvi, movie, could, sed, vhs, format, digital, crt, still, inch, market, television, been, early, hdmi" />
	<meta name="description" content="Today we've decided to take a little walk down Home Theater memory lane.  The last couple years have seen some significant changes in home theater technology, and we've seen a few devices go extinct.  A few are still on the endangered species list, but will probably be gone very soon.  We'd like to bid farewell to some technological advances that changed the way we enjoy entertainment in our homes, or were supposed to, but maybe it didn't quite pan out." />
	<meta name="title" content="HDTV and Home Theater Podcast #279 - Home Theater Memory Lane" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #279 - Home Theater Memory Lane" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/05/hdtv-and-home-theater-podcast-279-home-theater-memory-lane.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Today we've decided to take a little walk down Home Theater memory lane.  The last couple years have seen some significant changes in home theater technology, and we've seen a few devices go extinct.  A few are still on the endangered species list, but will probably be gone very soon.  We'd like to bid farewell to some technological advances that changed the way we enjoy entertainment in our homes, or were supposed to, but maybe it didn't quite pan out." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1412', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/05/hdtv-and-home-theater-podcast-279-home-theater-memory-lane.php">HDTV and Home Theater Podcast #279 - Home Theater Memory Lane</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>May 27, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=387&category=Entertainment">Entertainment</a></b>, <b><a href="/category.php?id=447&category=PC & Laptop Technology">PC & Laptop Technology</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-27.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
Today we've decided to take a little walk down Home Theater memory lane.  The last couple years have seen some significant changes in home theater technology, and we've seen a few devices go extinct.  A few are still on the endangered species list, but will probably be gone very soon.  We'd like to bid farewell to some technological advances that changed the way we enjoy entertainment in our homes, or were supposed to, but maybe it didn't quite pan out.</p>

<p><strong><em>Aside: Is the Internet the ultimate convergence device?</em></strong></p>

<p>Panasonic recently announced a new Plasma TV line, the the <a target="_blank" href="http://www.i4u.com/article17468.html">PZ850 series</a>.  The new sets will feature a technology they're calling "VIERA CAST" which will give you direct access to web content like YouTube, Google’s Picasa online photo albums and Bloomberg, all directly on the HDTV without a set-top box.  They'll be available in mid-June in four screen sizes: 46-inch (TH-46PZ850), 50-inch class (TH-50PZ850), 58-inch (TH-58PZ850) and 65-inch (TH-65PZ850).  All will be 1080p and will support native 24p playback.  They'll range in price from $3000 to $8000 US.  We couldn't find any information on whether or not they will support wireless network connections, or will require a hardwired input.<br />
 <br />
Sure, Panasonic isn't the first to have a network connect TV, HP has been there for a while, but this whole "Internet connected" TV thing got us thinking.  With all the talk over the last few year about "convergence" of home theater devices, could the Internet be that ultimate convergence device?  You could presumably get to <a target="_blank" href="http://www.hulu.com/">Hulu.com</a> from a TV like this and watch all the shows and movies available there.  Add on sites like NetFlix "Watch Now" and you're starting to build quite a library of content, without needing Cable or Satellite service.  What if the drive that currently sits in your Vudu box was actually on the Internet somewhere.  You could buy or rent movies and watch them on any Internet connected TV in your home.  Later, when high speed mobile Internet (4G) becomes reality, you could even watch them from the car or your cell phone.</p>

<p>The only thing we'd still need to solve is access to live content.  It should be simple enough for each network to stream a feed on the Internet that anyone could get to.  Then sites could aggregate those feeds into "channels" to make them easy to find and use.  Why couldn't CNN and ESPN simply stream out a high quality feed on the Internet?  Make it subscription based or even add supported to cover bandwidth costs and what else is there?</p>

<p>The Internet may become the only convergence device you'll ever need.  Just ad a screen wherever you need it, or wherever you happen to be, and something to render the audio, and all of the rest of your home theater devices become obsolete. <br />
 <br />
<strong>Looking Back on Home Theater</strong></p>

<p><strong>CRT Televisions</strong><br />
The Home Theater revolution really began in 1946 after the end of World War II.  During the war, manufacture of televisions was halted, but when that restriction was lifted, the technology came into its own.  Of course those early CRTs were nothing like the ones that recently disappeared from store shelves, but they ushered in the era of in-home entertainment.  The earliest production sets sold hit the market even before the war.  In 1938 a 3-inch CRT television cost $125 US, the equivalent of $1863 in 2007 dollars.  The luxury 12-inch model cost $445 US, or $6633 in 2007.  While the adjusted-for-inflation prices haven't changed that much over the years, the definition of family time has been forever altered.  Most of us have fond memories of sitting around the TV with our parents, brothers and sisters enjoying a prime time show.</p>

<p>In 1946 only 0.5% of U.S. households had a television set, by 1954 55.7% had one, and that number hit 90% as early as 1962.  Meanwhile, in 1947 in Britain, there were 15,000 households with a TV, that number climbed to 1.4 million in 1952, and shot up to 15.1 million by 1968.  In recent years the trusted CRT television has been replaced by digital microdisplay technology like DLP and LCD, and newer, sexier flat panel technologies like flat LCD and plasma.  In 2007 LCD televisions surpassed CRT televisions in total global sales, hitting 47% - pushing CRT down to 46%.  And in 2007 Best Buy, one of the largest television retailers in the US, announced that they would no longer carry any analog TV sets.  While they still sell a few digital CRTs, were quickly seeing the good old CRT become part of the good old days.</p>

<p><strong>VCR</strong><br />
Nothing enabled Home Movie entertainment more then the advent of the VCR.  Before the mass market success of the Video Cassette Recorder in the early 1980s, we were all slaves to the television programming schedule.  You had to be home to watch something when it was on, or you'd miss it, and probably never see it again.  Forget the idea of sitting down to a movie with friends and family.  The VCR also gave us the first real home theater format war, pitting Sony's Betamax format against JVC's VHS format.  By most accounts Beta was a superior format, but VHS stole the hearts and minds of the consumer and won the war due, in part, to its longer recording times.  You could fit a two hour movie on one VHS tape, but the one hour limit on a Betmax tape (until the release of Beta II and Beta III) hurt its adoption.</p>

<p>With the VCR came video rental stores, and with that came a whole new revolution of in-home movie viewing, otherwise known as the home theater.  The stores popped up on every corner and in every strip mall you could find.  And the phenomenon exploded.  Blockbuster took over for most of the smaller, mom and pop style rental stores and became a huge player in home theater.  Lately online video rental like NetFlix and downloadable movie services like Vudu have threatened to destroy Blockbuster's business model, but it's still way too early to put brick and mortar movie rental on the endangered list.  Although VHS rental is entirely gone, having been replaced by DVD for some time.  It took a decade, but DVD overtook VHS in 2003 and hasn't looked back.  On the recording side, DVRs have begun to displace VHS as the technology of choice to record television programming.  It is estimated that one in five US households has at least one DVR, with that number set to reach 50% by 2011.</p>

<p><strong>Laserdisc</strong><br />
Laserdisc was going to usher in a new era of home theater quality with an experience unlike any other.  Technologically superior to VHS for both audio and video, it was the first optical media format to make it to consumers.  On the video side, the format supported 425 lines of resolution, compared with the 240 on a VHS tape.  The discs also supported digital audio like Dolby Digital and DTS, the same formats we find on DVD discs today.  However, while it provided superior quality, it also had a few shortcomings and never really caught on with consumers.  First of all, the discs were huge, measuring almost a full foot (30 cm) in diameter and were quite heavy.  This size made them difficult to deal with, easy to damage and required more powerful (in other words louder) equipment to spin them.  In addition, each disc only held 30 to 60 minutes of video per side, requiring consumers to flip the disc to see both halves of a movie.  If a movie was too big to fit on two sides of one disc, they'd have to swap in a second disc as well.</p>

<p>In 1998, it was estimated that laserdisc player had a market penetration of approximately two million US households or about 2%.  That number never really increased, and the technology was eventually completely displaced by DVD.</p>

<p><strong>DVI</strong><br />
We know we're sure to get email on this one, but as a home theater format, DVI or Digital Video Interface cables have all but been replaced by HDMI.  In the early days of HDTV, DVI was hailed as the best option for video cabling.  It provided the only digital connection between your HDTV source, like an ATSC tuner or a set-top box, and your display.  As HDMI crept into the picture, DVI began to lose its luster.  HDMI, or High Definition Multimedia Interface, carried the equivalent video information, but also bundled digital surround sound audio on the same cable.  Since those early days of HDMI 1.0 and 1.1, version 1.3 of the HDMI spec has been released, surpassing the video capabilities of the original DVI cable.  Still alive and kicking in the IT space, DVI has seen its days of glory as the go-to cable for HDTV fade into the sunset.  There are rumors that DisplayPort may begin to push DVI out of the PC/IT market as well.  Farewell DVI, it was fun while it lasted.</p>

<p><strong>SED</strong><br />
We first talked about SED in May of 2005, on <a target="_blank" href="http://www.htguys.com/archive/2005/May122005.html">Episode #7</a>.  <em>Surface-conduction Emission Display - SED works very much like traditional CRT technology, shooting a beam of electrons to excite phosphors on a screen. Unlike, CRTs, though SED uses a thin strip of Palladium Oxide as an electron emitter, instead of a huge cathode ray tube. This allows the whole display system to be manufactured as thin as 10 millimeters thick.</em>  We believed back in 2005 that SED had a chance to replace plasma and flat LCD as the dominant flat panel display technology.  In subsequent years at shows like CES we even saw amazing demos from the two main companies behind the technology, Canon and Toshiba.  Legal issues over patent infringement hurt SED, as did the time it took to get to market.  In that time plasma and LCD worked out the kinks, improved efficiency in manufacturing and economies of scale and greatly increased the barrier to entry in the flat panel display market.  It's rumored that Canon is still working on SED displays.  But at this point, it looks like SED will go the way of the Dodo.</p>

<p><br />
<em>Researched at <a href="http://www.wikipedia.org" target="_blank">wikipedia.org</a> and several other Internet sites.</em><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>May 27, 2008  2:15 PM</b>
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
			<?=getComments(1412)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1412)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv-and-home-theater-podcast-279-home-theater-memory-lane.php" type="text/javascript" charset="utf-8"></script>
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