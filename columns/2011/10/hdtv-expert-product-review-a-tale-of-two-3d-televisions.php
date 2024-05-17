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
		AND e.entry_id = 4546";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4546 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4546 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4546";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/10/hdtv-expert-product-review-a-tale-of-two-3d-televisions.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4546";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: A Tale of Two (3D) Televisions" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: A Tale of Two (3D) Televisions" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: A Tale of Two (3D) Televisions" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: A Tale of Two (3D) Televisions</title>
	<meta name="keywords" content="both tvs, blu ray, viewing angles, screen diagonal, ambient light, tvs, mode, figure, both, ’s, toshiba, color, passive, here, active, –, viewing, lcd, screen, images, gamma, set, see, get, much" />
	<meta name="description" content="LG and Toshiba are aggressively pushing passive 3D technology. Here&amp;#039;s how their 47-inch offerings compare to each other." />
	<meta name="title" content="HDTV Expert - Product Review: A Tale of Two (3D) Televisions" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: A Tale of Two (3D) Televisions" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/10/hdtv-expert-product-review-a-tale-of-two-3d-televisions.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LG and Toshiba are aggressively pushing passive 3D technology. Here&amp;#039;s how their 47-inch offerings compare to each other." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4546', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/10/hdtv-expert-product-review-a-tale-of-two-3d-televisions.php">HDTV Expert - Product Review: A Tale of Two (3D) Televisions</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>October 14, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=448&category=PC & Laptop Technology">PC & Laptop Technology</a></b>
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
          <p>The great 3D TV debates continue as 2011 winds down. “Active 3D is best!” cries one group. “No, passive 3D is better!” replies another. “Don’t jump in yet, wait for autostereo TVs!” warns yet another group.</p>
<p> </p>
<p>Here are the facts. At present, there are a handful of manufacturers of active 3D TVs, including market leaders Samsung, Panasonic, and Sony. On the other side of the street, we have passive 3D TVs available from LG, Toshiba, and Vizio.</p>
<p> </p>
<p>There are other companies playing in the 3D space to a lesser degree, including Sharp (active 3D) and JVC (passive 3D). And Toshiba is trying to be all things to all people, supporting a few active models and also announcing that they will bring a 55-inch autostereo TV to the Japanese market this fall.</p>
<p> </p>
<p>All of this back-and-forth volleying is accomplishing one thing, if nothing else: It’s confusing the heck out of potential buyers. No one wants to sink a few thousand dollars into a 3D TV system and realize belatedly that they picked the wrong horse in the race.</p>
<p> </p>
<p>Problem is; no one can say for certain which horse will win that race. Active 3D has its detractors for using expensive, battery-operated glasses that can create eye fatigue in certain individuals from flicker. However, an active 3D TV delivers all 1920×1080 pixels for every video frame in both 2D and 3D mode. And there are no patterned barriers attached to the screen surface to affect 2D viewing.</p>
<p> </p>
<p>Passive 3D has simplicity and lower cost going for it – you can use the same circularly-polarized glasses you brought home from the local Cineplex – but presents a visible artifact in the form of horizontal patterned film retarder lines when watching 3D content and sitting closer than 2x the screen diagonal. And passive 3D TVs have very narrow ‘usable’ viewing angles, compared to active 3D TVs.</p>
<p> </p>
<p>As for autostereo, let’s just say right now that it’s not really ready for prime time yet, based on what I saw at CES 2011 in the Toshiba booth. The appeal of glassless 3D is easy to understand, but it makes the design of the TV much more complex. Plus, there’s a tradeoff: The more ‘views’ you have on an autostereo TV, the lower the overall resolution of each view.</p>
<p> </p>
<p>SEEING DOUBLE</p>
<p> </p>
<p>In my tests of 3D TVs, both active and passive, I look very carefully for evidence of ghosting, or double images. Ghosting is caused by insufficient suppression of opposite-eye images, and results in double vision (and often, headaches).</p>
<p> </p>
<p>The ability of a 3D TV design and its associated eyewear to suppress ghosting is called its extinction ratio.  The laws of physics say that active LCD TVs will have a harder time suppressing ghosts than plasma TVs, and that’s because of all the polarizers used in a typical LCD TV: They interact with the polarizers used in 3D eyewear and can cancel each other out.</p>
<p> </p>
<p>There is even an inconsistency among active 3D TVs. Samsung and Panasonic use dual polarizers in their active eyewear. Sony, however, opted to go with a single polarizer for two reasons. First, the resulting images are brighter. And second, it helps to minimize flicker and eyestrain. But there’s a trade-off, and that is a lower extinction ratio and lots of ghost images with small head tilt.</p>
<p> </p>
<p>Passive 3D TVs don’t get a free pass here. One set of polarizers is mounted on the TV screen surface (those afore-mentioned film patterned retarders) to work with the other set in the ‘el cheapo’ passive glasses. At certain narrow viewing angles, their extinction ratio is quite high. But at comparatively small offset viewing angles, the double images are apparent, as I’ll demonstrate shortly.</p>
<p> </p>
<p>TWO FOR THE MONEY</p>
<p> </p>
<p>I decided to see what the fuss was all about with passive 3D TVs and lined up a pair of 47-inch models to see what they could do. LG’s 47LW6500 ($1,399) is at the top of their 3D TV line and comes with four pairs of passive glasses. It’s a 240 Hz LED-backlit LCD TV with four HDMI inputs (1.4a compatible, of course), plus a host of network functions (Smart TV) and other bells and whistles.</p>
<div id="attachment_1540" class="wp-caption aligncenter" style="width: 560px"><a rel="attachment wp-att-1540" href="http://www.hdtvexpert.com/?attachment_id=1540"><img class="size-full wp-image-1540" title="LG-Infinia-47LW6500 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/LG-Infinia-47LW6500-MR.jpg" alt="" width="550" height="427" /></a><p class="wp-caption-text">Figure 1. LG's 47LW6500 3D LCD TV</p></div>
<div id="attachment_1528" class="wp-caption aligncenter" style="width: 560px"><a rel="attachment wp-att-1528" href="http://www.hdtvexpert.com/?attachment_id=1528"><img class="size-full wp-image-1528" title="led-tv-47TL515-01" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/led-tv-47TL515-01.jpg" alt="" width="550" height="371" /></a><p class="wp-caption-text">Figure 2. Toshiba's 47TL515U 3D LCD TV</p></div>
<p>Toshiba’s 47TL515U ($1,299 SRP) just came to market and offers much the same goodies as the LG Set. (I’ve been told it even uses LG Display panels.) This is also a 240 Hz LED-backlit LCD TV with 4 HDMI inputs, Net TV, and one leg up on the LG set: It’s equipped with the new InstaPort HDMI connector. That means fast switching between HDMI sources.</p>
<p> </p>
<p>To be honest, there’s not a whole lot of difference between the two TVs. The LG scans TV channels faster; the Toshiba changes inputs faster. Both TVs have multifunction remotes, but the Toshiba remote is far more complicated and difficult to use. There are just too many small buttons, and the navigation mousedisk is mounted coaxially inside a second navigation ring, which also has four pushbuttons on it. You can’t use this remote easily even with the lights on.</p>
<p> </p>
<p>The LG remote is FAR more user-friendly, with big, white buttons, large blue volume and channel controls, and a simpler mousedisk for navigating through menus. In addition, the LG’s 3D on/off button is nestled between the volume and channel rocker switches and clearly marker “3D” in bright red. In contrast, it took me a while to find the 3D mode button on the Toshiba – it’s part of a row of four tiny black buttons near the bottom of the remote.</p>
<div id="attachment_1529" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1529" href="http://www.hdtvexpert.com/?attachment_id=1529"><img class="size-full wp-image-1529" title="Two Remotes MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Two-Remotes-MR.jpg" alt="" width="600" height="512" /></a><p class="wp-caption-text">Figure 3. Which remote would you rather use? And can you find the 3D buttons?</p></div>
<div id="attachment_1548" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1548" href="http://www.hdtvexpert.com/?attachment_id=1548"><img class="size-full wp-image-1548" title="3D Button Closeups MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/3D-Button-Closeups-MR.jpg" alt="" width="600" height="277" /></a><p class="wp-caption-text">Figure 4. Here they are!</p></div>
<p>MENUS AND ADJUSTMENTS</p>
<p> </p>
<p>The menus on both TVs leave a bit to be desired. Toshiba’s menus appear as rotary icons near the bottom of the screen. You scroll (spin, rotate) left or right to bring up the desired menu, then hit select and start making your choices or adjustments. It’s a bit different than the usual horizontal bar menus, but you’ll get used to it quickly enough.</p>
<p> </p>
<p>On the LG set, pressing the Home button brings up a master screen that shows Smart TV icons, a settings icon, and an input icon. Whatever you’re watching on screen is reduced to a small window. You then have to navigate to the ‘Settings’ button and select it to get into any menus. It’s slightly annoying, but you shouldn’t have to adjust it very much.</p>
<p> </p>
<p>I usually go into more detail about menu settings here. Suffice it to say that both TVs give you a full range of adjustments over images, with the exception of 3D. The 47LW6500 has two ISF Expert modes in addition to Intelligent (ambient light sensing), Vivid, Standard and Cinema presets, and you can get the TV’s white balance very close to the BT.709 target of 6500 degrees pretty easily. Ditto the 47TL515U, which also has an ‘expert’ mode for calibration, and offers two Movie modes, Sports, and Autoview (ambient light sensing) presets.</p>
<p> </p>
<p>As mentioned earlier, both TVs offer 240 Hz scanning and de-judder circuits that can convert a film look to live video, along with automatic contrast, adjustable gamma, and black level settings. All routine stuff and all things you should shut off if you want to calibrate either TV to work at its best. The digital noise reduction circuits are handy if you are viewing video content that has been over-compressed. That shouldn’t be much of a problem with HDTV programs, but is quite common with standard-definition programming.</p>
<p> </p>
<p>TUNING THEM UP</p>
<p> </p>
<p>What I was interested in seeing was how well each TV worked in 2D mode after calibration, and what happened to image quality when 3D mode was selected. Here’s where the biggest difference was found between the two TVs – the 47LW6500 will not let you make ANY adjustments or access any menus in 3D mode, while the 47TL515U will. And if you think that’s not such a big deal, have I got some histograms for you…</p>
<div id="attachment_1530" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1530" href="http://www.hdtvexpert.com/?attachment_id=1530"><img class="size-full wp-image-1530" title="Figure 5a" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-5a.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Figure 5a. Here's the gamma curve for the 47LW6500 after calibration - a beautiful 2.31 arc.</p></div>
<div id="attachment_1531" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1531" href="http://www.hdtvexpert.com/?attachment_id=1531"><img class="size-full wp-image-1531" title="Figure 5b" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-5b.jpg" alt="" width="600" height="279" /></a><p class="wp-caption-text">Figure 5b. And here's the corresponding after-calibration 2.44 curve on the 47TL515U.</p></div>
<p>Figure 5a shows the final gamma curve for the Toshiba, while 5b shows the LG gamma after calibration. Both curves are consistent coming out of black and mimic the performance of a CRT. Where things get dicey is when 3D mode is switched on. The LG TV switches to a much brighter image with higher black levels and an S-curve gamma, which measures approximately 1.5 – and there not a thing you can do to fix it.</p>
<p> </p>
<p>On the other hand, the Toshiba set exhibited a remarkably consistent gamma after its 3D mode was turned on, with a curve similar to 2D mode that measured 2.36. AND you can go back into the menu and tweak it if you want to.</p>
<div id="attachment_1532" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1532" href="http://www.hdtvexpert.com/?attachment_id=1532"><img class="size-full wp-image-1532" title="Figure 6a" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-6a.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Figure 6a. Yikes! What happened to the LG's gamma performance?</p></div>
<div id="attachment_1533" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1533" href="http://www.hdtvexpert.com/?attachment_id=1533"><img class="size-full wp-image-1533" title="Figure 6b" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-6b.jpg" alt="" width="600" height="279" /></a><p class="wp-caption-text">Figure 6b. Toshiba maintains its gamma settings even in 3D mode.</p></div>
<p>How about color temperature? Figures 7a and b show grayscale tracks for both sets in 2D mode, and they’re looking pretty good, eh? But switch to 3D mode, and as you can see in figures 8a and b, the 47LW6500 jumps way above 9300 degrees, while the 47LT515U doesn’t move nearly that high (about 6800 degrees) – and again, you can fix it.</p>
<p> </p>
<p>This is what you don’t hear about 3D TVs: Their 2D calibrations usually go out the window when 3D mode is selected, and most of the time, you can’t do a darn thing about it. Fortunately, Toshiba does preserve your ability to compensate for any shifts caused in 3D mode.</p>
<p> </p>
<p>Let’s talk about color accuracy. HDTV content for television and released on Blu-ray disc is supposed to conform to the ITU BT.709 color space, which produces colors that are somewhat less saturated than the full color gamut of LCD and plasma TVs. So to be ‘precise,’ any TV ought to match that color encoding as closely as possible.</p>
<p> </p>
<p>Guess what? Both TVs do just that, as seen in figures 9a and 9b. LG gets the blue ribbon for coming closest to the desired RGB and CMY coordinates and both sets provide color management software (CMS) to fine-tune the x,y locus of each coordinate. Note that the coordinates shift on both TVs when in 3D mode (why is that???) and the shift is more noticeable on the LG TV, as evidenced by the green, cyan, magenta, and yellow targets.</p>
<div id="attachment_1534" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1534" href="http://www.hdtvexpert.com/?attachment_id=1534"><img class="size-full wp-image-1534" title="Figure 7a" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-7a.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Figure 7a. The 47LW6500 tracks a stable grayscale in 2D mode...</p></div>
<div id="attachment_1535" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1535" href="http://www.hdtvexpert.com/?attachment_id=1535"><img class="size-full wp-image-1535" title="Figure 7b" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-7b.jpg" alt="" width="600" height="279" /></a><p class="wp-caption-text">Figure 7b. And so does the 47TL515U.</p></div>
<p> </p>
<div id="attachment_1551" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1551" href="http://www.hdtvexpert.com/?attachment_id=1551"><img class="size-full wp-image-1551" title="Figure 7c" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-7c.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Figure 8a. Switch to 3D mode on the LG, and all bets are off with respect to color temperature.</p></div>
<div id="attachment_1552" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1552" href="http://www.hdtvexpert.com/?attachment_id=1552"><img class="size-full wp-image-1552" title="Figure 7d" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-7d.jpg" alt="" width="600" height="279" /></a><p class="wp-caption-text">Figure 8b. Meanwhile, everything is rock-steady in 3D on the Toshiba.</p></div>
<div id="attachment_1553" class="wp-caption aligncenter" style="width: 510px"><a rel="attachment wp-att-1553" href="http://www.hdtvexpert.com/?attachment_id=1553"><img class="size-full wp-image-1553" title="Figure 8a" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-8a1.jpg" alt="" width="500" height="562" /></a><p class="wp-caption-text">Figure 9a. Here's the 47LW6500's color gamut. The dark outline is the BT.709 color space.</p></div>
<div id="attachment_1554" class="wp-caption aligncenter" style="width: 510px"><a rel="attachment wp-att-1554" href="http://www.hdtvexpert.com/?attachment_id=1554"><img class="size-full wp-image-1554" title="Figure 8b" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-8b1.jpg" alt="" width="500" height="562" /></a><p class="wp-caption-text">Figure 9b. And here's the 47TL515U's color gamut, mapped against the BT.709 color space.</p></div>
<p>VIEWING 3D</p>
<p> </p>
<p>For my viewing tests, I used a Blu-ray copy of <em>Avatar</em>, played out from a Samsung C6900 3D BD player. Since both TVs use circular polarization in their eyewear, I was able to watch with many different pairs of glasses and saw no difference in the results.</p>
<p> </p>
<p>First off, 3D images seemed to have more depth on the Toshiba. Can’t tell you why that was, but I definitely noticed it. Not to say that the LG set didn’t do a good job  – it did, but the Toshiba 3D images seemed to be more realistic, especially in the scenes with people gathered around the sacred trees, campfires, in the lab, and in the war room.</p>
<p> </p>
<p>Color quality was better on the Toshiba for the reasons enumerated in the previous section. It doesn’t jump that far out of calibration in 3D mode. The LG TV does get considerably brighter and colder in color temperature, and the overall picture quality isn’t as pleasing to the eye.</p>
<p> </p>
<p>Both TVs seem to switch on their motion de-juddering circuits in 3D mode, so you need to make sure that function is disabled completely if you want a true ‘film’ look when watching 3D Blu-ray discs. Look for a menu function that shuts down 240 Hz mode. (And make sure you’ve shut off ALL other picture enhancements like dynamic contrast, auto black levels, etc.)</p>
<p> </p>
<p>Now for my viewing distance suggestions. I generally counsel people to shoot for a seating distance equal to 1.3 – 1.5x of the screen diagonal, in order to get a more immersive 3D effect. That rule of thumb works great with active shutter TVs, and also holds true for 3D front projectors, but it doesn‘t hold up with passive 3D TVs.</p>
<p> </p>
<p>The reason? You’ll see the FPR lines, which appear as thin, horizontal black bands. Close one eye or the other, and there they are! In fact, you’ll see them with both eyes open, and it’s like the old ‘screen door’ effect with low-resolution LCD projectors from the mid-1990s. Kinda distracting, in my opinion.</p>
<p> </p>
<p>So it forces you to sit farther away from the 3D TV, which is exactly the opposite of what you want to do! The best 3D experiences come when the screen fills 50% or more of your field of view, and you’ve removed as many distracting 3D artifacts from outside that field. That’s one reason why 3D works so much better in movie theaters than at home – there is practically zero ambient light and the screens are big, so your brain locks onto the 3D illusion much more quickly.</p>
<p> </p>
<p>Practically speaking, you need to sit about 2x the screen diagonal (94”, or about eight feet) to minimize the FPR lines. I’ve tested this viewing distance with a variety of viewers, young and old, and it holds up. And that, in my mind, is the big strike against passive 3D: You want to sit closer, but you can’t because of this picture artifact.</p>
<p> </p>
<p>The other artifact you’ll notice from time to time is crosstalk. It depends on your viewing angle and the type of content, but it is most often seen with titles, high contrast fine detail, and angular objects on light backgrounds. I created a few test patterns to check for crosstalk and you can see the actual results in figure 10a. Both TVs suffer from this problem – it’s a direct consequence of using FPRs.</p>
<div id="attachment_1543" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1543" href="http://www.hdtvexpert.com/?attachment_id=1543"><img class="size-full wp-image-1543" title="LG -30V FS MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/LG-30V-FS-MR.jpg" alt="" width="600" height="336" /></a><p class="wp-caption-text">Figure 10a. Here's what crosstalk looks like on a passive 3D set.</p></div>
<div id="attachment_1544" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1544" href="http://www.hdtvexpert.com/?attachment_id=1544"><img class="size-full wp-image-1544" title="Figure 9b" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-9b.jpg" alt="" width="600" height="407" /></a><p class="wp-caption-text">Figure 10b. Here's the view through one eye of a passive 3D image. You can see the FPR lines running horizontally along the walkway in the foreground and across the railroad tracks.</p></div>
<div id="attachment_1545" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1545" href="http://www.hdtvexpert.com/?attachment_id=1545"><img class="size-full wp-image-1545" title="Figure 9c" src="http://www.hdtvexpert.com/wp-content/uploads/2011/10/Figure-9c.jpg" alt="" width="600" height="388" /></a><p class="wp-caption-text">Figure 10c. Here's a close-up view of the FPR artifact.</p></div>
<p>THE WRAP-UP</p>
<p> </p>
<p>Did I mention that both TVs make some beautiful pictures in 2D mode? No FPR artifacts are seen here; just full-resolution 1080p images with good contrast and color saturation. In fact, the 47LW6500 is one of the better 2D LCD TVs I’ve tested recently, and the 47TL515U is right up there with it.</p>
<p> </p>
<p>The devil is in the details, and in 3D mode, the 47TL515U is clearly the better performer. Just the fact that it lets you fine-tune image settings while in 3D mode is a BIG plus in my book. It is nice to know that all of your hard work in 2D mode won’t be lost, and you’ll see some really good-looking 3D as a result.</p>
<p> </p>
<p>With the 47LW6500, you are out of luck. 3D pictures will be a lot brighter, black levels will elevate, and white crush will be present… and you’ll just have to live with whatever the TV shows you. If there’s any consolation, you’ll get into 3D mode a lot faster with the LG TV ( that big fat 3D button is a great idea) and the Toshiba isn’t as user-friendly when it comes to the remote control.</p>
<p> </p>
<p>By the way, both sets support all standard 3D formats, including side-by-side and top + bottom frame compatible, and the frame-sequential Blu-ray format is recognized automatically by both TVs. (You can also horse around with converting 2D to 3D, if you have nothing better to do…)</p>
<p> </p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>October 14, 2011  3:27 PM</b>
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
			<?=getComments(4546)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4546)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/10/hdtv-expert-product-review-a-tale-of-two-3d-televisions.php" type="text/javascript" charset="utf-8"></script>
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