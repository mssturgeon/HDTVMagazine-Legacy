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
		AND e.entry_id = 910";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 910 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 910 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 910";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/01/hdtv-and-home-theater-podcast-243-the-death-of-rear-projection-tvs-bridging-the-gap-from-your-computer-to-your-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 910";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV</title>
	<meta name="keywords" content="rear projection, media center, home theater, popcorn hour, gap computer, media, computer, market, apple, projection, rear, new, home, great, sagetv, theater, should, while, extender, our, hdtv, center, longer, may, something" />
	<meta name="description" content="We witnessed the death of CRT a few years ago when we searched high and low all over the CES show floor for somebody showing anything resembling a tube TV and came up empty. This year, if you don't count the Texas Instruments DLP booth, we saw only three rear projection sets at the entire show. Sony has announced their exiting the market because it wasn't profitable. By not announcing anything new, JVC has all but announced that they won't be in the game for very much longer. Samsung announced a hand full of new models and Mitsubishi is is touting their new laser based unit that should be available before the end of the year. So for today's show we give you some tips that will help you decide if buying a Rear Projection TV is right for you.

Also,
Until now the concept of a media center PC, or a computer in your home theater has largely remained in the realm of the IT expert or extreme enthusiast. There hasn't really been a product yet that has bridged the gap between the IT side of your life and the consumer electronics side. We discuss our odds-on favorites to successfully mass market a computer for the Home Theater and &quot;Bridge the Gap&quot; from your computer to your TV." />
	<meta name="title" content="HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs &amp; Bridging the Gap from your Computer to your TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs &amp; Bridging the Gap from your Computer to your TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/01/hdtv-and-home-theater-podcast-243-the-death-of-rear-projection-tvs-bridging-the-gap-from-your-computer-to-your-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="We witnessed the death of CRT a few years ago when we searched high and low all over the CES show floor for somebody showing anything resembling a tube TV and came up empty. This year, if you don't count the Texas Instruments DLP booth, we saw only three rear projection sets at the entire show. Sony has announced their exiting the market because it wasn't profitable. By not announcing anything new, JVC has all but announced that they won't be in the game for very much longer. Samsung announced a hand full of new models and Mitsubishi is is touting their new laser based unit that should be available before the end of the year. So for today's show we give you some tips that will help you decide if buying a Rear Projection TV is right for you.

Also,
Until now the concept of a media center PC, or a computer in your home theater has largely remained in the realm of the IT expert or extreme enthusiast. There hasn't really been a product yet that has bridged the gap between the IT side of your life and the consumer electronics side. We discuss our odds-on favorites to successfully mass market a computer for the Home Theater and &quot;Bridge the Gap&quot; from your computer to your TV." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=910', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/01/hdtv-and-home-theater-podcast-243-the-death-of-rear-projection-tvs-bridging-the-gap-from-your-computer-to-your-tv.php">HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>January 20, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>, <b><a href="/category.php?id=478&category=HTPCs & Laptops">HTPCs & Laptops</a></b>, <b><a href="/category.php?id=417&category=High Definition Production">High Definition Production</a></b>, <b><a href="/category.php?id=520&category=Laser HDTV">Laser HDTV</a></b>
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
<strong>Today's Show:</strong><br>

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-01-22.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br></p>

<p>We witnessed the death of CRT a few years ago when we searched high and low all over the CES show floor for somebody showing anything resembling a tube TV and came up empty.  This year, if you don't count the Texas Instruments DLP booth, we saw only three rear projection sets at the entire show.  Sony has announced their exiting the market because it wasn't profitable.  By not announcing anything new, JVC has all but announced that they won't be in the game for very much longer.  Samsung announced a hand full of new models and Mitsubishi is is touting their new laser based unit that should be available before the end of the year.   So for today's show we give you some tips that will help you decide if buying a Rear Projection TV is right for you.<br />
 <br />
Also,<br />
Until now the concept of a media center PC, or a computer in your home theater has largely remained in the realm of the IT expert or extreme enthusiast.  There hasn't really been a product yet that has bridged the gap between the IT side of your life and the consumer electronics side. We discuss our odds-on favorites to successfully mass market a computer for the Home Theater and "Bridge the Gap" from your computer to your TV. <br />
<strong><br />
The Death of Rear Projection TVs</strong><br />
So it's down to two: Samsung and Mitsubishi.  Both have always been very solid selections in the micro-display market, so it's not like we're left with two bad choices.  But what is a consumer to do?  Right now, if you're looking to buy a new HDTV, do you pick up a big rear projection unit, even though you know the technology will be extinct by the end of the decade, or do you go a little smaller and buy a plasma or flat LCD to make sure you have something that will be around for a while longer.  Or perhaps, you do the unthinkable and you decide to just wait it out to let the dust settle.  Maybe if it's your second HDTV that's OK, but if you aren't enjoying HDTV, you absolutely must buy something.  We recommend:</p>

<p>1. Consider your budget.  Know how much you can spend.  Use it all.<br />
2. Consider the room.  Do you have the extra depth you need for a rear projection, or is wall amounting a big deal?  How big should the screen itself be?</p>

<p>Those two questions should make the decision on which TV is right for you.  For example, if your budget is around $1500 you're probably looking at a <a href="http://www.htguys.com/shop.php?id=B000N53T82">61" rear projection</a> or a <a href="http://www.htguys.com/shop.php?id=B000O5TFRK">50" plasma</a> (42" if you want to step up to a <a href="http://www.htguys.com/shop.php?id=B000RQ5K2A">Kuro</a>), and <a href="http://www.htguys.com/shop.php?id=B000UN3VT4">42" LCD</a>.  If your budget is $2500 you can scale up to a <a href="http://www.htguys.com/shop.php?id=B000RYUWX4">73" Mitsubishi DLP</a>, get a <a href="http://www.htguys.com/shop.php?id=B000M2P3NU">60" plasma</a> or a <a href="http://www.htguys.com/shop.php?id=B000UN914I">52" LCD</a>.</p>

<p>In our minds, the rear projection sets are still great TVs.  Between the two of us, we own three, and will be getting really good use out of them for years - even after they stop being sold at our store.  While there may be a bunch of consumers who bought early CRT and RP LCDHDTVs who wish they would have waited a little while longer, the technology is far enough along now that you should be able to confidently purchase a micro display TV without any remorse.</p>

<p>Of course, a week or two after we say this one of the remaining companies will probably announce their intention to exit the market, but that's just how it goes some times...<br />
 <br />
 <strong><br />
Bridging the Gap from your Computer to your TV</strong><br />
We've got our eye on a few products or technologies that might be able to go mass market with this concept.  Here are our top 4, moving up in likelihood.</p>

<p>4. <a href="http://sagetv.com/hd_extender.html">SageTV HD Media Extender</a><br />
We've used SageTV in the past and found it to be a great interface for media center functionality on a PC.  The interface is well done, it has an open an active community and can really do a lot of great things.  The media extender has been around for a while, but the HD version is a recent addition.  It provides the same Sage TV front end in your Home Theater or any room in the house while allowing you to run SageTV on a PC in your office or a closet somewhere.  At $199 it's a great price and makes good sense.  It will playback just about anything the SageTV software can handle, including high definition up to 1080p.  The uphill battle for SageTV will be convincing the masses that setting up a media center PC is easy and something they want to do.  It's still largely an IT based market, so they have their work cut out for them before it goes to grandmas house.</p>

<p>3. <a href="http://www.popcornhour.com/onlinestore/">Popcorn Hour A-100</a><br />
The Popcorn Hour A-100, or Networked Media Tank, allows you to "stream or playback your digital media content from a variety of sources, such as your PC, NAS, digital camera, USB mass storage devices (Flash drive, HDD, DVD drive), internal HDD and even directly from the Internet via the Media Service Portal."  It is very similar to other devices on the market, like the stuff from <a href="http://www.mvixusa.com/">Mvix</a> that we've reviewed in the past.  The big difference by all accounts with the Popcorn Hour is that the user interface is actually fairly straight forward and may be, in fact, kitchen ready.  We've requested a demo unit but are yet to hear back.  It only costs $179 (without an internal hard drive), so it's fairly cost effective.  It works as a front end for any media you may have on your office computer and supports pretty much every format imaginable, all the way up to 1080p.  It does take some know-how to get it set up, but doesn't require any specific software on your computer, so it might just do the trick.  Since most people we talk to haven't heard of it before, we guessing they have their marketing work cut out for them.</p>

<p>2. <a href="http://www.apple.com/appletv/">Apple TV</a><br />
So if there's one thing Apple knows how to do it's put together a great user experience.  By dropping the price of the Apple TV to $229 and adding some new functionality, they may have a device that can finally find a home in your family room.  The Apple TV no longer requires a computer, but can download and stream it's own content as a stand alone device.  This is cool, but odds are all your content is somewhere on the network already, or you'll want a couple Apple TV units in various rooms, so it would need to be.  As an Apple product it isn't quite as versatile, file format wise, as the other options on the list, but the user experience is perfect, so it should be grandma approved.  If Apple can convince consumers that their TV device is a natural extension of the iPod (maybe even add an iPod dock, duh), they could sell a ton of them.  If it is perceived as an extension to the Mac product line, they'll be fighting the same uphill battle they are in selling computers.  But they certainly have the market presence and the money to make something happen.</p>

<p>1. <a href="http://www.microsoft.com/windows/products/winfamily/mediacenterextender/default.mspx">Windows Media Center Extenders</a><br />
Going on the assumption that millions of new Vista computers will be sold this year - probably on the order of 15 to 20 times more than their Mac counterparts, the Media Center Extender devices have to be the favorite to make an impression on the consumer electronics market.  Microsoft has tried to convince everyone to put a computer in the family room, but it never worked.  Now they've built that functionality into Vista for your office PC and will sell you an inexpensive extender to allow you to enjoy all the HTPC goodness in your home theater, without needing an actual computer there.  This seems like it might work.  If they pull a play out of Sony's playbook and advertise that you already have all this great functionality in your office (just like the PS3 already has a Blu-ray player), all you need is this little extender to enjoy it on your massive HDTV.  Seems pretty simple.  And to steal another similarity from a different CE war, Apple is the only company making and promoting the Apple TV.  Microsoft has their own XBox 360 and devices from the likes of HP, Samsung, D-link, and Linksys.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>January 20, 2008  8:37 AM</b>
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
			<?=getComments(910)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 910)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/01/hdtv-and-home-theater-podcast-243-the-death-of-rear-projection-tvs-bridging-the-gap-from-your-computer-to-your-tv.php" type="text/javascript" charset="utf-8"></script>
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