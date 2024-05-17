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
		AND e.entry_id = 1710";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Robert A. Fowkes" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1710 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Robert A. Fowkes'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Robert A. Fowkes" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1710 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1710";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1710";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market" />
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
	<title>HDTV Magazine - Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market</title>
	<meta name="keywords" content="pre pro, home theater, voltage threshold, threshold error, component approach, hdmi, devices, video, component, components, threshold, approach, pre, voltage, might, signal, pro, box, receivers, theater, home, using, boxes, audio, years" />
	<meta name="description" content="Over two years ago I wrote an article titled &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2007/12/a_new_approach_to_components_in_a_digital_audiovideo_world.php&quot;&gt;&lt;b&gt;&lt;i&gt;A New Approach to Components in a Digital Audio/Video World&lt;/i&gt;&lt;/b&gt;&lt;/a&gt; to share my thoughts regarding equipment options in Home Theater and related electronics. At that time I outlined the reasons that components might provide a better overall solution to an all-in-one box in terms of upgradability, performance and sometimes even price. Basically, the upside focused on the ability to upgrade as needed without replacing perfectly adequate and functional parts and the downside involved more boxes and wires than the alternative. While that's a gross simplification of the whole hypothesis you can review the entire argument by referring to the original article.

The basic principles of that paper still apply but a lot has changed over the past two years..." />
	<meta name="title" content="Revisiting the Component Approach to Audio/Video in Today&amp;rsquo;s Home Theater Market" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Revisiting the Component Approach to Audio/Video in Today&amp;rsquo;s Home Theater Market" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Over two years ago I wrote an article titled &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2007/12/a_new_approach_to_components_in_a_digital_audiovideo_world.php&quot;&gt;&lt;b&gt;&lt;i&gt;A New Approach to Components in a Digital Audio/Video World&lt;/i&gt;&lt;/b&gt;&lt;/a&gt; to share my thoughts regarding equipment options in Home Theater and related electronics. At that time I outlined the reasons that components might provide a better overall solution to an all-in-one box in terms of upgradability, performance and sometimes even price. Basically, the upside focused on the ability to upgrade as needed without replacing perfectly adequate and functional parts and the downside involved more boxes and wires than the alternative. While that's a gross simplification of the whole hypothesis you can review the entire argument by referring to the original article.

The basic principles of that paper still apply but a lot has changed over the past two years..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1710', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php">Revisiting the Component Approach to Audio/Video in Today&rsquo;s Home Theater Market</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Robert A. Fowkes</b> on <b>April 16, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<h2>Introduction</h2> <p>Over two years ago I wrote an article titled <a href="http://www.hdtvmagazine.com/articles/2007/12/a_new_approach_to_components_in_a_digital_audiovideo_world.php"><b><i>A New Approach to Components in a Digital Audio/Video World</i></b></a> to share my thoughts regarding equipment options in Home Theater and related electronics. At that time I outlined the reasons that components might provide a better overall solution to an all-in-one box in terms of upgradability, performance and sometimes even price. Basically, the upside focused on the ability to upgrade as needed without replacing perfectly adequate and functional parts and the downside involved more boxes and wires than the alternative. While that's a gross simplification of the whole hypothesis you can review the entire argument by referring to the original article. <h2>So what's different today?</h2> <p>The basic principles of that paper still apply but a lot has changed over the past two years which allows me to re-address my original hypothesis to present some viable alternatives. In fact, my recent audio/video purchases (more about this later) reflect this new information. So what are some of the factors that have changed? <ul> <li>HDMI has matured. With HDMI 1.3a now firmly established some of the problems of earlier versions have just about disappeared. And the industry has taken steps to see that all parties follow the standards as written.</li> <li>More manufacturers are "playing by the rules" concerning HDMI, partly due to standards monitoring but just as importantly because trouble free devices have a much smaller need for customer support. And providing support costs money.</li> <li>Prices for many very capable A/V devices have fallen to the point where the one-box approach is no longer as restrictive as it once was. While I still advocate separate amplifiers for several reasons including their longevity, relative freedom from obsolescence and the fact that internal amps in receivers tend to stifle performance in those units it has become almost as cheap to replace a good A/V receiver (even just used as a preamp-processor - or "pre-pro") as it once was to replace an individual component.</li> <li>The performance of the audio decoders and the video processors in receivers/pre-pros now rivals the quality found in standalone devices for the same task.</li> <li>The processing ability (video scaling and audio processing) of many source devices like DVD and Blu-ray (HD) players has gotten surprisingly good as more chips are available to be incorporated into these units at low cost.</li> <li>More inputs (and outputs) are now available on a wide range of Home Theater equipment including pre/pros and displays. This gives additional installation flexibility with fewer components.</li></ul> <p>On the component side of things prices have dropped dramatically as well. A standalone video processor which might have cost in excess of $2000 a very short time ago can now be had for under $600 (as of this writing). One such example is the <a href="http://www.anchorbaytech.com/dvdo_edge/product.php">DVDO Edge</a>. While more expensive units offer greater video flexibility the performance of these new cost-effective units is remarkable and would meet the needs of most home theater enthusiasts. So the original component approach still applies if you want total flexibility at a lower cost than ever. <h2>An Important Consideration</h2> <p>But there's one cloud looming over the component approach and HDMI that must be taken into consideration and carefully monitored if going this route. Yes, more boxes mean more connectors and the increased shelf space required might limit this method in some installations but that's not the real problem. In 2007 I wrote an article called <a href="http://www.hdtvmagazine.com/articles/2008/02/the_wonderful_and_sometimes_confusing_world_of_hdmi_connections.php"><b><i>The Wonderful and Sometimes Confusing World of HDMI Connections</i></b></a> where I provided a comprehensive overview of HDMI and some of the potential pitfalls. That article should bring you up to speed on HDMI and point you in the right direction regarding this now maturing technology. We are closer than ever to the "one-wire solution" now that most of the industry is complying with the standards. There is still the occasional "blue screen" as an HDMI device or two temporarily has to undergo a handshake sequence to stay connected (most noticeably in some Blu-ray devices which, I'm told, is due to some copy protection issues within that format's specifications) but in almost every case it's not the annoying delays of years past. <p>However, there is one potential problem with HDMI that might surface if using a multi-box (component) approach in one's home theater. My friend Jano Banks (co-inventor of HDMI) pointed out to me that the more devices you add to the HDMI chain the greater your chance of experiencing handshaking delays of some magnitude. He cautioned that you might encounter this problem even if the individual components each comply with the HDMI specifications separately. The reason can be explained as follows (in simplified terms). The digital HDMI signal involves voltage thresholds which determine the difference between the "0" (off) and "1" (on) state. Suppose, for argument's sake, that the HDMI spec allows a compliant component to have no more than a 30% voltage threshold error in order to be certified. This would assume that 0% voltage threshold error indicates digital perfection in signal transfer and 100% voltage threshold error indicates a failed digital signal transfer. Remember, a digital signal transfer is an on/off proposition so that if the signal is below the threshold a "0" is the result and if above the threshold a "1" is the result. (Analog analogies do not apply here - no pun intended.) The problem is that these threshold errors are cumulative so that the more HDMI devices in the chain, the better the chance that you may experience some signal problems - at least momentarily as connectivity is established. In other words if you connect four HDMI devices in series then the total voltage threshold error could actually approach 120%! (30% times 4). In actual experience you might get away with four HDMI components connected in series (I did for quite some time) because the 30% voltage threshold specification is a maximum and many components fall way below that. Also, there are ways to reinforce the HDMI signal using devices such as Repeaters that lower the threshold error even more. Jano told me that you can definitely connect three HDMI compliant devices with no problems but once you reach four or more devices you might run into some problems. As mentioned previously, the scenario I just presented does not represent the actual voltage specifications but is intended to give the reader an idea of what we are up against here. The more HDMI boxes involved, the greater the chance for some unpredictable behavior. There's another matter of wire length with HDMI devices which also involves voltage thresholds but well designed HDMI cables usually allow for that with the proper electronics. (<i>Remember, "well designed" doesn't necessarily mean "expensive!")</i> In general you have no problem with a simple well constructed HDMI cable up to 20 feet or so - sometimes even longer. But that's another subject for another time. <h2>Alternatives and My Personal Solution</h2> <p>With all this in mind, one can currently choose either the modular (component) or the integrated (pre-pro/receiver) approach using HDMI connections and come up with a reliable and great performing system. My previous articles (which see) listed the components that I was using and after viewing the latest offerings at CEDIA I made the decision to re-do the electronics of my home theater to drastically reduce the number of boxes involved. Instead of a four box (source, pre-pro, video processor, HDMI switch) path I now have a two box path (source, pre-pro) to my displays. But before anyone thinks that I have compromised on quality and flexibility let me discuss the new components and the reasons that I made the switch. In recent years I have been using Denon receivers (first the 3806 and then the 3808ci) in "pre-pro" mode because I liked the feature set on these devices. I still used my Marantz MA-700 monoblock amplifiers and my 5 channel Outlaw 755 amp (all spec'ed at 200W into 8 ohms) for power and bypassed the internal amps of the receiver. I also used a DVDO VP-50 video processor in the chain (after the receiver, with the receiver's incoming video set to pass-through) and then took the single HDMI output of the VP-50 (video only at this point) into a quality HDMI repeater/switch (Radiient Repeat-6) to feed two display devices - a JVC RS-1 1080p LCoS front projector and an HP MD5880n 58" 1080p DLP rear projection monitor. My earlier articles explain my choices of this equipment and their features in more detail. <p>Then came the <a href="http://www.usa.denon.com/ProductDetails/3922.asp">Denon AVP-A1HDCI</a> pre-pro! This was Denon's first entry into the separates field this century. Many years ago they introduced the AVP-8000 which was legendary at the time and this new unit is a state of the art AV preamplifier-processor with just about every imaginable feature included. One may gag at the $7500 list price and I realize that this puts it out of the price range of a lot of HT fans but when considering the list prices of all the components I replaced (and nobody except my mom ever paid list price) it's not completely crazy. Remember, a lot of what this behemoth contains is state of the art with some great upgradable features to ward off obsolescence. For one thing, it has Ethernet connectivity which allows for firmware upgrades which occur on a regular basis. There have already been several major upgrades since I purchased this unit (including Audyssey Dynamic Volume) and shortly DenonlinkIV will be added. And the fact that the AVP-A1HDCI is an Internet device allows me to back up the many, many configuration settings on my PC in case of an electronic disaster. Add to that the remarkable Audyssey Equalization and the Silicon Realta Video processing chips along with all the latest audio codecs and more inputs and outputs than you can shake a stick at and I have more functionality than I had in my previous three boxes combined. And the immediate bonuses for me were <ul> <li>No more "redundant" amplifiers. I have amplifiers so I'm not paying for something I'm not using as I have been recently with receivers. (But that was actually a blessing because now my former Denon 3808ci now resides upstairs where the seven channel amp is finally being used.)</li> <li>More importantly, far fewer wires by a long shot so HDMI is now much more stable than it has ever been.</li></ul> <h2>Wrapping it All Up</h2> <p>It is not my intention to provide a review of the AVP-A1HDCI here (I think you can tell what my sentiments are) as there are many well written articles on that score. Let Google be your friend in this matter. Nor am I advocating that you run out and spend your child's college fund on HT equipment. While the Denon AVP represents a unit on stereo steroids, you can find many other receivers that are incorporating the most important features of separate AV components. The point that I am trying to make is that the recent rash of high quality, multi-featured, affordable receivers from many manufacturers has made it possible to provide yourself with a fully functional HDMI A/V system without having to resort to a lot of boxes. This was not the case just a few years ago. <p>So, in conclusion, the industry has responded to the early HDMI problems with a series of stable products - whether you wish to go the component route or the single box approach. With components you have a bit more flexibility and with receivers the reliability of the HDMI circuitry (when properly implemented - read the reviews!) plays in its favor. And the modern AV Pre-pro is a hybrid: one box for all the processing and separate amps for the component enthusiast. In some ways I've come full cycle back to my Dynaco days in the '50s as described in my original component article.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Robert A. Fowkes</b>, <b>April 16, 2009  9:24 AM</b>
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
			<?=getComments(1710)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Robert A. Fowkes', 1710)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Robert A. Fowkes</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/04/revisiting-the-component-approach-to-audiovideo-in-todays-home-theater-market.php" type="text/javascript" charset="utf-8"></script>
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