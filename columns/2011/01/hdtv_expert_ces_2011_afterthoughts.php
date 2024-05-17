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
		AND e.entry_id = 4167";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4167 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4167 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4167";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-afterthoughts.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4167";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - CES 2011: Afterthoughts" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - CES 2011: Afterthoughts" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - CES 2011: Afterthoughts" />
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
	<title>HDTV Magazine - HDTV Expert - CES 2011: Afterthoughts</title>
	<meta name="keywords" content="blu ray, active shutter, gesture recognition, lcd tvs, – name, lcd, tvs, ces, big, booth, year, –, passive, while, sony, ’s, inch, screen, tablet, display, oled, jvc, still, every, may" />
	<meta name="description" content="There’s always more to the story. Here are some additional parting thoughts from this year’s gadget fest." />
	<meta name="title" content="HDTV Expert - CES 2011: Afterthoughts" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - CES 2011: Afterthoughts" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-afterthoughts.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="There’s always more to the story. Here are some additional parting thoughts from this year’s gadget fest." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4167', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-afterthoughts.php">HDTV Expert - CES 2011: Afterthoughts</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January 13, 2011</b>
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
          <p>CES is a strange show. It’s so big and has so many exhibitors that you keep thinking about what you’ve seen for weeks afterwards – kinda like mental ‘aftershocks’ and flashbacks. And I’ve had a few of those since returning home almost a week ago.</p>
<p>Here, in no particular order, are some afterthoughts from CES:</p>
<div id="attachment_998" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TCL-Gesture-Recognition-Demo-MR.jpg"><img class="size-full wp-image-998" title="TCL Gesture Recognition Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TCL-Gesture-Recognition-Demo-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">It looked much more impressive than it worked.</p></div>
<p><strong>Gesture Recognition – Hey, Where’d it Go?</strong> In 2007, 2008, and 2009, gesture recognition for TV operation was a BIG deal at CES. Hitachi, Toshiba, JVC, and others all showed sophisticated gesture-recognition systems at previous CES shows, and last year’s Toshiba exhibit managed to combine GR, their Cell processor, <span style="text-decoration: underline;">and</span> 3D in a most impressive demonstration.</p>
<p>This year? Hardly any GR demos at all, aside from some rather crude examples found in the Hisense and TCL booths that barely worked. The TCL demo was so insensitive that visitors to that particular exhibit looked like they were swatting at flies, while the Hisense demo consisted of someone doing a work-out while following an animated trainer on a nearby LCD TV.</p>
<p>Yawn…</p>
<div id="attachment_999" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-31-inch-OLED-TV-MR.jpg"><img class="size-full wp-image-999" title="LG 31-inch OLED TV MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/LG-31-inch-OLED-TV-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">OLED TVs are coming any day now. About the same time the Cubs win the World Series.</p></div>
<p><strong>OLEDs – We’re Still Waiting:</strong> Every year, Samsung, Sony, LG, and others tease us with demonstrations of gorgeous-looking OLED TVs in a variety of screen sizes. Yet, we continue to wait, and wait, and wait for production models to come to brick-and-mortar stores. (The XEL-1 doesn’t count.) Sony even built an autostereo screen into a 24.5-inch AM OLED display, while Samsung’s 19-inch AM OLED was 50% transparent.</p>
<p>We’d all like to replace our LCD and plasma TVs with OLEDs, but it looks like we’re going to be drooling and waiting a LONG time before that happens. Smart phones have already beaten us to the punch and it looks like tablet computers will be the next place to roll out (literally) OLED screens.</p>
<p>And yet, every year, we get our hopes up again…</p>
<div id="attachment_1000" class="wp-caption aligncenter" style="width: 458px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TI-Pocket-Projectors-Two-up-MR.jpg"><img class="size-full wp-image-1000" title="TI Pocket Projectors Two-up MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/TI-Pocket-Projectors-Two-up-MR.jpg" alt="" width="448" height="600" /></a><p class="wp-caption-text">These must be figments of my imagination.</p></div>
<p><strong>Picoprojectors: Vaporware?</strong> After reading a recent Display Daily post by colleague Matt Brennesholtz at Insight Media, I fired off an email to eight different IM analysts, asking them if they had ever seen a picoprojector in use in 2010 other than at a trade show or a display technology conference.</p>
<p>This may surprise you, but each one of them responded with a simple, “No.” None of them had spotted any at retail, either. And yet, companies like Pacific Media Associates continue to issue optimistic sales forecasts for picoprojectors, while Texas Instruments had a full suite of “picos” at CES that were built into smart phones, a tablet computer, cameras, and pocket projectors.</p>
<p>I think tablet computers may derail picoprojectors, or obsolete them completely. How about you?</p>
<div id="attachment_1001" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sharp-3D-TV-Demo-MR.jpg"><img class="size-full wp-image-1001" title="Sharp 3D TV Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Sharp-3D-TV-Demo-MR.jpg" alt="" width="600" height="489" /></a><p class="wp-caption-text">Maybe they didn't get the memo last year?</p></div>
<p><strong>Hey Sharp, 3D was SO 2010!</strong> Sharp once again had an enormous CES booth filled with big, colorful LCD TVs (70-inches was the big news this year) and finally had a few 3D Blu-ray demos to go with them. Well, a year late isn’t too bad, I guess. The only problem is; Sharp’s share of the U.S. TV market has been steadily dropping since 2005 and is below 3%, according to NPD Display Search’s 3<sup>rd</sup> quarter 2010 numbers. That’s embarrassing! Even Panasonic now ships more LCD TVs than Sharp, who pioneered the LCD TV biz a couple of decades ago.</p>
<p>The four-color Quattron technology, while intriguing, doesn’t appear to have caught on with consumers so far, and we all know how disappointing sales of active shutter 3D TVs have been to date. To add to Sharp’s problems, Sony has not fully committed to fund its share of Sharp’s new Gen 10 LCD plant. Sony was originally on the hook for a 34% stake, but according to multiple reports may cap that investment at 12% and look to China for a cheaper source of LCD panels.</p>
<p>This would be a good time for a comeback, kid…</p>
<div id="attachment_1002" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Mitsubishi-92-inch-DLP-RPTV-1-MR.jpg"><img class="size-full wp-image-1002" title="Mitsubishi 92-inch DLP RPTV 1 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Mitsubishi-92-inch-DLP-RPTV-1-MR.jpg" alt="" width="600" height="342" /></a><p class="wp-caption-text">There's a contrarian in every crowd...</p></div>
<p><strong>Mitsubishi Thumbs its Nose at the Experts:</strong> Yep, those ‘diamond’ guys are still making rear-projection DLP TVs, and apparently selling plenty of them, too. Their 92-inch roll-out at CES drew big crowds and will probably ticket around $5,000, which is less money than a decent front projector, screen, and home theater in a box will cost you. Did I say it could do 3D, too? Side-by-side, top+bottom, frame packing, checkerboard – you name it.</p>
<p>We “experts” predicted Mits would fall by the wayside as the LCD and plasma juggernauts rolled through the market. Uh, not quite. And with Mits’ new laser light engine, the issue of lamp replacement will eventually fade into the sunset. Texas Instruments is thrilled that they still have a RPTV customer, and as long as Mits can manage its bill of materials (BOM) costs, they can remain in the catbird seat for a few more years until something better comes along.</p>
<p>(<em>Sound of a big raspberry coming from Irvine…</em>)</p>
<div id="attachment_1003" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/DisplayPort-Multi-Monitor-Demo-MR.jpg"><img class="size-full wp-image-1003" title="DisplayPort Multi-Monitor Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/DisplayPort-Multi-Monitor-Demo-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Remember reading "The Tortoise and the Hare" as a kid? Here's the tortoise. </p></div>
<p><strong>DisplayPort: On Your Mark…Get Set…Get Set…Get Set:</strong> Is DisplayPort <span style="text-decoration: underline;">ever</span> going to take off? I saw several cool demos of multi-monitor support and embedded 3D notebooks through DisplayPort in the IDT suite, along with a basic booth in the lower South Hall showing wireless DisplayPort over WHDI and a multi-channel audio concept demo.  But who’s using it, aside from Apple?</p>
<p>In the meantime, HDMI (Silicon Image) showed ViaPort (multiple connections to a TV hub and one to a AVR with automatic streaming for the highest-supported audio format), MHL (Mobile content through a mini HDMI interface to TVs and other devices), and ViaPort for digital signage (Blu-ray at full resolution to eight daisy-chained TVs through single HDMI connections).</p>
<p>Maybe they misplaced the starter’s gun.</p>
<div id="attachment_1004" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Vizio-XV533-Demo-MR.jpg"><img class="size-full wp-image-1004" title="Vizio XV533 Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Vizio-XV533-Demo-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">What's next? VIZIO appliances? Cars? An Airline?</p></div>
<p><strong>VIZIO – The Next Apple?</strong> Not only has VIZIO staked a big claim in the TV marketplace, they also rolled out a tablet computer and a smart phone at CES. The VIZIO Phone has a 4-inch display, GPS, WiFi, two built-in cameras, HDMI output (MHL), 2 GB of storage and doubles as a universal remote for VIZIO products.</p>
<p>The VIZIO tablet is pretty impressive, too. It also has WiFi, GPS, and a high-rez camera for videoconferencing, HMDI output, three internal speakers, and 2Gb of internal storage plus a MicroSD card slot. And yes, it can also work as a universal remote. The guys at VIZIO also thumbed their noses at all of the active-shutter 3DTV manufacturers and opted to go with passive 3D in a 65-inch LCD set that uses inexpensive RealD (circular polarization) glasses.</p>
<p>What’s next, Mr. Wang? Brick-and-mortar ‘VIZIO Zone’ stores in selected cities and malls? (Don’t laugh, he might just try it!)</p>
<div id="attachment_1005" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/JVC-65-inch-E-LED-Passive-3D-TV-Details-MR.jpg"><img class="size-full wp-image-1005" title="JVC 65-inch E-LED Passive 3D TV Details MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/JVC-65-inch-E-LED-Passive-3D-TV-Details-MR.jpg" alt="" width="600" height="393" /></a><p class="wp-caption-text">Ghandi was into passive, too. </p></div>
<p><strong>Active Shutter 3D – Has it Peaked Already?</strong> In addition to VIZIO, LG and JVC also showed new large LCD TV products with embedded micropolarizers and inexpensive passive 3D glasses. I saw a few passive demos here and there, but these were the big three as far a product rollouts. LG even had large bins with passive glasses at the numerous entrances to their booth.</p>
<p>While passive 3D certainly solves the problems with fragile and expensive glasses, it can play funny tricks with screen resolution as every other horizontal row of pixels has micro-sized circular polarizers that work in opposite directions. That can make the screen appear to have noticeable black lines on it when viewing normal content, a problem that would be solved by moving to 4K native resolution (thereby adding to panel complexity and costs).</p>
<p>Still, passive 3D could put a crimp in 3D TV sales this year as it feeds into the average consumer’s wariness of another TV ‘format war.’</p>
<p><strong>Step Right Up and Getcha 3D Camcorder!</strong> This product category went from 0 participants in 2010 to “I lost count’ in 2011. Panasonic, Sony, ViewSonic, JVC – you name the company, they had a 3D camcorder out for inspection somewhere in their booth. And it wasn’t just the big boys, either. Ever hear of Aiptek? Didn’t think so. They showed a palm-sized 3D camcorder under their name that coincidentally appeared in the nearby ViewSonic booth.</p>
<p style="text-align: center;">
</p><div id="attachment_1006" class="wp-caption aligncenter" style="width: 583px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Panasonic-3D-Camcorder-with-Lens-Adapter-MR.jpg"><img class="size-large wp-image-1006  " title="Panasonic 3D Camcorder with Lens Adapter MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/Panasonic-3D-Camcorder-with-Lens-Adapter-MR-1024x766.jpg" alt="" width="573" height="429" /></a><p class="wp-caption-text">Coming to a home near you! Check newspapers...</p></div>
<p>The question is how many of these cameras were using conversion lenses (Panasonic) and how many were capturing video through true 3D optical assemblies (JVC, Sony).  The Aiptek model in question may also have been converting 2D on the fly, but it was hard to tell from the sketchy details in their booth. Also, Sony’s and JVC’s cameras use the full-resolution frame-packing format, similar to Blu-ray DVD.</p>
<p>OK, who wants a 3D camcorder? (And a 3D TV to go with it?)</p>
<div id="attachment_1007" class="wp-caption aligncenter" style="width: 810px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2011/01/USPS-Booth-View-2-MR.jpg"><img class="size-full wp-image-1007" title="USPS Booth View 2 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/01/USPS-Booth-View-2-MR.jpg" alt="" width="800" height="598" /></a><p class="wp-caption-text">Wonder if their booth was open on Saturday?</p></div>
<p><strong>Hey, Didn’t You Guys Just Lose $8.5B?</strong> Once again, the United States Postal Service occupied a healthy-sized booth in the upper South Hall. And once again, they were shilling for Priority and Overnight Mail, package shipping, and a new service called PremiumPostcard.com direct mail marketing.  They also featured something called the Fast and Furious Challenge, although no racecar was in sight this year.</p>
<p>Ordinarily, I’d be kinda upset that taxpayer money was spent this way…except that the USPS operates as a quasi-private agency, living entirely off revenues from mail delivery. So maybe I should instead give them props for trying to drum up more business, except that it’s hard to understand how many of the surrounding Chinese manufacturers would benefit from any USPS offerings.</p>
<p>As long as they don’t drop Saturday delivery, I guess I don’t care…</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January 13, 2011  2:38 PM</b>
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
			<?=getComments(4167)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4167)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/01/hdtv-expert-ces-2011-afterthoughts.php" type="text/javascript" charset="utf-8"></script>
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