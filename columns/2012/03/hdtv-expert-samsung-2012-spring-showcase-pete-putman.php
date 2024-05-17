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
		AND e.entry_id = 4726";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4726 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4726 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4726";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-samsung-2012-spring-showcase-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4726";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Samsung 2012 Spring Showcase &ndash; Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Samsung 2012 Spring Showcase &ndash; Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Samsung 2012 Spring Showcase &ndash; Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - Samsung 2012 Spring Showcase &ndash; Pete Putman</title>
	<meta name="keywords" content="plasma tvs, voice gesture, gesture control, blu ray, smart evolution, tvs, samsung, smart, line, inch, –, new, web, control, ’s, time, gesture, voice, plasma, connected, content, led, share, samsung’s, blu" />
	<meta name="description" content="Samsung&amp;acirc;��s spring 2012 TV showcase was a markedly lower-key affair than previous events, and placed a heavy emphasis on &amp;acirc;��smart&amp;acirc;�� TV connectivity and control." />
	<meta name="title" content="HDTV Expert - Samsung 2012 Spring Showcase &amp;ndash; Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Samsung 2012 Spring Showcase &amp;ndash; Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-samsung-2012-spring-showcase-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Samsung&amp;acirc;��s spring 2012 TV showcase was a markedly lower-key affair than previous events, and placed a heavy emphasis on &amp;acirc;��smart&amp;acirc;�� TV connectivity and control." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4726', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-samsung-2012-spring-showcase-pete-putman.php">HDTV Expert - Samsung 2012 Spring Showcase &ndash; Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March  8, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=304&category=General Interest">General Interest</a></b>
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
          <p><strong><em>Tempus fugit!</em></strong> The Time Warner Center in New York City will soon shed that moniker, as TW sells off its former ‘prime’ real estate holdings in the city to save money.</p>
<p> </p>
<p>It should be no surprise then that the Samsung Experience pavilion on the 3<sup>rd</sup> floor is also history. This electronic ‘toy store’ once showcased the latest in Samsung TVs, phones, Blu-ray players, tablets, and even appliances, and it also served as the venue for Samsung’s annual spring line shows.</p>
<p> </p>
<p>No more. The 2012 spring show took place March 6 at the Metropolitan Pavilion on 18<sup>th</sup> Street, the site of the rapidly-growing CEA Summer Line Shows. And it was a relatively sedate affair, choosing to focus on ‘connectivity’ – connected Smart TVs, connected digital cameras and tablets, and connected humans. That is, humans using more intuitive methods to ‘connect’ to their TVs and control them.</p>
<div id="attachment_1903" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1903" rel="attachment wp-att-1903"><img class="size-full wp-image-1903" title="Joe Stinziano Shows Off Smart Upgrade  MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Joe-Stinziano-Shows-Off-Smart-Upgrade-MR.jpg" alt="" width="600" height="516" /></a><p class="wp-caption-text">Samsung VP Joe Stinziano touts the new Smart Evolution upgrade module.</p></div>
<p> </p>
<p>The big news for 2012 is the ES-line of LED (LCD) TVs, which take full advantage of voice and gesture recognition for control. The TV comes with a built-in camera and takes a picture of each user, which is then used to store your preferences. The camera can even pick you out of a crowd.</p>
<p> </p>
<p>Voice controls include basic volume up/down and channel up/down operation, or direct channel numbers. You can also change inputs and launch a Web browser, at which point the gesture control takes over. This was demonstrated at CES to a long line of attendees and will probably be a popular item for ‘geeks.’ (I’m not sure yet if I want my TV to watch <span style="text-decoration: underline;">me</span> while I’m watching it!)</p>
<p> </p>
<p>Voice and gesture control will be standard on the ES7500 46-inch, 50-inch, and 55-inch LED TVs, ES8000 46, 55, 60, and 65-inch LED TVs, and 51, 60, and 65-inch ES8000 plasma TVs. Prices start at about $2,200 for the line, and all models are shipping now.</p>
<p> </p>
<p>One big question that keeps coming up as NeTVs evolve into full-blown Web browsers with powerful CPUs is this: Is there any way to make them future-proof? After all, Apple and Microsoft update their operating systems on a frequent basis, so why should anyone worry about their TV becoming obsolete?</p>
<div id="attachment_1904" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1904" rel="attachment wp-att-1904"><img class="size-full wp-image-1904" title="Smart Evolution Module CU MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Smart-Evolution-Module-CU-MR.jpg" alt="" width="600" height="512" /></a><p class="wp-caption-text">And here's what the Smart Evolution module looks like in action.</p></div>
<p> </p>
<p>This problem is solved nicely (from Samsung’s perspective) with Smart Evolution, which is basically a chassis that mounts on the back of the TV and contains all the latest firmware and hardware updates. Readers who’ve been following the HDTV market for the last decade may recall that Mitsubishi came out with a similar product over 10 years ago – an expansion module they called “The Promise” that fit into their line of rear-projection TVs. (And how well did THAT idea work out?)</p>
<p> </p>
<p>In addition to built-in cameras and noise-canceling microphones for using Skype and voice/gesture control, Samsung also unveiled a new, super-simple remote control that is remarkably free of buttons. It’s actually a touch pad, with volume and channel buttons mounted on either side. It does double duty as a microphone for voice commands, and also ships with the ES7500, ES8000 LED, and ES8000 plasma TVs.</p>
<p> </p>
<p>You are probably not surprised that Samsung also unveiled a full-sized Bluetooth keyboard to work with the same ES line of TVs. That’s because the keyboards on most remotes are too small for Western fingers (certainly for me!) and you may be on a Web page where you need to enter strings of text.</p>
<div id="attachment_1905" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1905" rel="attachment wp-att-1905"><img class="size-full wp-image-1905" title="AllShare Play Screen MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/AllShare-Play-Screen-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Now, be nice and share with your brothers and sisters!</p></div>
<p> </p>
<p>Hold on there, pardner! Have we gone back in time to the days of Web TV? Historically, TV viewers have clearly shown their disdain for using a keyboard to watch television, and there’s no reason to expect that will change any time soon. Fortunately, the new Smart Touch Remote can also activate an on-screen keyboard which can then be ‘swiped’ to enter text or numbers for Web pages.</p>
<p> </p>
<p>Other enhancements to the TV line include Micro Dimming to achieve more precise local area dimming on LED TVs and improve contrast uniformity, and the availability of Real Black Filter across all of the plasma TVs in the 2012 line.  The purpose is to minimize reflections and light scattering that lowers contrast and elevates black levels – Panasonic uses a similar technique on its plasma TVs.</p>
<p> </p>
<p>AllShare is a new concept from Samsung. According to the press release, All Share lets viewers share content to a variety of connected devices, such as tablets, laptops, and smart phones. The content is stored on 5 GB of ‘cloud’ server space. In addition, any Web site that’s being browsed on a mobile device can be re-directed and launched from a compatible Samsung Smart TV.</p>
<p> </p>
<p>At least one reporter asked if AllShare competes with Ultraviolet, the movie industry’s ‘cloud’ system for cross-platform viewing of content. Actually, all Ultraviolet does is to store keys on its ‘cloud’ servers, and those keys are then used to unlock and watch copies of movies previously purchased on a wide range of platforms. In contrast, AllShare stores the content, not keys.</p>
<div id="attachment_1906" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1906" rel="attachment wp-att-1906"><img class="size-full wp-image-1906" title="Smart Hub Screen MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Smart-Hub-Screen-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Smart Hub is still here. In fact, everything about Samsung TVs is 'smart' these days.</p></div>
<p> </p>
<p>To keep up with all of this content and GUI juggling, the ES7000, ES7500, and ES8000 TVs now have dual-core processors for high processing speeds. OK, computer! (Sorry, Radiohead fans…)</p>
<p> </p>
<p>In the Blu-Ray department, there are five new models ranging from the entry-level BD-ES300 ($99.99) to the loaded-for-bear BD-E6500 ($229.99). Depending on the model, you’ll have built-in WiFi, an internal Web browser, access to All Share, Smart Hub, and Disc to Digital, a new service that lets you ‘rip’ a DVD or Blu-ray file to a digital file accessible to connected (mobile) devices. (Hmmm, sounds a lot like Ultraviolet to me!)</p>
<p> </p>
<p>It’s interesting to stop and consider that just five years ago, a ‘bare bones’ Blu-ray player would set you back nearly $1200, with some models approaching two grand. Now, you can have every option you want or need – including Internet connectivity – for less than $200 after online retailers slash their advertised pricing.</p>
<div id="attachment_1909" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1909" rel="attachment wp-att-1909"><img class="size-full wp-image-1909" title="Samsung 2012 Plasmas MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Samsung-2012-Plasmas-MR.jpg" alt="" width="600" height="632" /></a><p class="wp-caption-text">Samsung's 2012 plasma TVs may have the thinnest bezels ever.</p></div>
<p> </p>
<p>And what about 3D? There was almost no discussion of it this year, quite a change from the hullaballo of 2010. 3D is largely a standard feature now in higher-priced TVs (like that ES7000-7500-8000 lineup again) because consumers just haven’t bitten on the concept.</p>
<p> </p>
<p>One thing Samsung has done for 2012 is to cut the price of replacement active 3D glasses to (ready for this?) $20 a pair; a price that should <span style="text-decoration: underline;">really</span> tick off early 3D adopters who had to fork over $100 or more to replace their active glasses each time Junior inadvertently sat on and broke them. The lower price point isn’t likely to stimulate 3D TV sales – nothing really has, not even passive or autostereo – but it’s still a nice gesture to the small group who grooves on the third dimension.</p>
<p> </p>
<p>And now for the 800-pound gorilla in the room: No, Samsung did NOT show an OLED TV in New York. BUT – there apparently will be an OLED TV in the line, most likely using the 55-inch cut. And of course, it will be loaded to the top with all of the Samsung add-ons (Smart Hub, AllShare, voice/gesture control, etc.) We’ll probably see it late in the year.</p>
<p> </p>
<p>My (educated) guess is that the pricing will be about $8K – $10K, or where LG has hinted <span style="text-decoration: underline;">its </span>55-inch product will be tagged when it gets to market sometime late summer or early fall. Given Samsung’s desire to sell off its money-losing LCD fab business and place more emphasis on OLED technology through its Samsung Mobile Displays division, it might be the perfect time to launch OLEDs. (Or maybe not, if yields aren’t high enough…)</p>
<p> </p>
<p><span style="text-decoration: underline;">Trivia time:</span> Remember when a 42-inch plasma cost $10,000? That was over ten years ago, and you can now buy Samsung’s 43-inch entry-level 720p PN43E450 for less than $550.</p>
<p> </p>
<p>Amazing…</p>
<p> </p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March  8, 2012  1:42 PM</b>
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
			<?=getComments(4726)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4726)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-samsung-2012-spring-showcase-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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