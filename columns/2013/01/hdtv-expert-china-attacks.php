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
		AND e.entry_id = 5017";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5017 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5017 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5017";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-china-attacks.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5017";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - China Attacks!" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - China Attacks!" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - China Attacks!" />
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
	<title>HDTV Magazine - HDTV Expert - China Attacks!</title>
	<meta name="keywords" content="ken werner, photo ken, interaction ”, gen fab, westinghouse digital, inch, tvs, tcl, sets, transparent, photo, westinghouse, ken, werner, showed, –, set, image, chinese, roku, ”, lcd, screen, panel, end" />
	<meta name="description" content="Chinese TV set makers were at CES in force, determined to take more of the North American market &amp;acirc;�� not only with sets sold under private labels but, increasingly, under their own. This trend has been visible for the last couple of years, but this year it was dramatic, with elaborate exhibits laid on by [...]" />
	<meta name="title" content="HDTV Expert - China Attacks!" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - China Attacks!" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-china-attacks.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Chinese TV set makers were at CES in force, determined to take more of the North American market &amp;acirc;�� not only with sets sold under private labels but, increasingly, under their own. This trend has been visible for the last couple of years, but this year it was dramatic, with elaborate exhibits laid on by [...]" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5017', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-china-attacks.php">HDTV Expert - China Attacks!</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January 28, 2013</b>
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
          <p>Chinese TV set makers were at CES in force, determined to take more of the North American market – not only with sets sold under private labels but, increasingly, under their own. This trend has been visible for the last couple of years, but this year it was dramatic, with elaborate exhibits laid on by TCL, Haier, HiSense, Konka, and Changhong. We could add Westinghouse Digital, which, as usual, showed its line-up in a suite at the Las Vegas Hotel. (Although Westinghouse isn’t a Chinese company, it’s sets come from the same set of contract manufacturers.)</p>
<p>And the Chinese set-makers weren’t just showing generic me-too products. Among their offerings were 4Kx2K sets, very large screens, smart TVs, and 3D-TVs. Although LG got a lot of attention for its short-throw laser TV that could be positioned just 22 inches from the screen onto which it was projecting, Hisense showed one that worked with its front edge virtually in line with the plane of the screen!</p>
<p>Let’s look at what the vendors were showing in more detail.</p>
<p>TCL showed an extremely broad line-up, including a 110-inch 4Kx2K TV, a voice-control TV, and the TCL MOVO Google TV box. TCL’s Jianpeng “Conan” Jiao told Display Daily that the MOVO box will be the first TCL product to appear in the U.S. market under TCL’s own name rather than a private label. Incidentally, the MOVO box has a handsome and distinctive design. Among the TV sets TCL showed was a 55-inch 4K that is eventually headed for the U.S., but Conan had no information on when that might be. The 55-inch 4K and 110-inch 4K panels are made by China Star Optoelectronic Technology (CSOT), which is jointly owned by TCL and Samsung.</p>
<p>The 55-inch 4K looks very good. Just one thing: We will all have to get used to sitting closer to our TV sets than is typical today in order to fully appreciate the capabilities of such a small 4K screen. Another point. The increased impression of depth delivered by 4K really makes 4K 3D-TV’s worst enemy, as well as its enabler in that 4K makes several important implementations of 3D-TV look a lot better they do in FHD.</p>
<p>Also in TCL’s booth were demonstrations of TV voice control and a “Blade TV” that is strikingly thin even by today’s standards.</p>
<div>
<dl id="attachment_15684"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0287_cropped_medres1.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0287_cropped_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0287_cropped_medres1.jpg" width="600" height="1772" /></a></dt>
<dd>TCL’s very thin “Blade TV” (Photo: Ken Werner)</dd>
</dl></div>
<p>Conan said that TCL terminated its licensing deal to sell TVs under the RCA brand because TCL wants to push its own brand in the U.S. market. Technicolor, which owns and licenses the RCA brand, may have an alternative interpretation.</p>
<p>In addition to its almost-zero-throw laser projection TV, Hisense showed an 84-inch 4K, and 110-inch 4K, a Roku-ready TV, and a Google TV, among others.</p>
<div>
<dl id="attachment_15696"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0031_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0031_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0031_medres.jpg" width="600" height="397" /></a></dt>
<dd>The Hisense 110-inch 4Kx2K TV is the world’s largest in the sense that there is none larger, but several other makers showed equally large TVs, all using the LCD panel made by CSOT of Shenzhen, China. (Photo: Ken Werner)</dd>
</dl></div>
<div>
<dl id="attachment_15697"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0035_cropped_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0035_cropped_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0035_cropped_medres.jpg" width="600" height="374" /></a></dt>
<dd>Hisense’s almost-zero-throw (measuring from the front edge of the projector to the plane of the screen) laser projector was largely ignored, while LG’s 22-inch-throw model in the expensive part of the hall drew gawkers and praise. (Photo: Ken Werner)</dd>
</dl></div>
<p>Haier introduced 25 TV models. Among the demos was the “Gaze” eye-control TV and an autostereoscopic TV with an unacceptably pixelated image. Haier also joined the TV makers – which seemingly includes most of them – showing a variant of 3D technology that allows two viewers to each watch a different show on the same set. Yawn. Just because you can do something doesn’t mean you should. More interesting was the award-winning pizza maker who was flinging pizza crusts around Haier’s booth. (The company is a major manufacturer of kitchen appliances.)</p>
<div>
<dl id="attachment_15685"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0238_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0238_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0238_medres.jpg" width="600" height="397" /></a></dt>
<dd>Enhanced ways of controlling a TV in this emerging era of interactive TV and a plethora of program choices were all over the show floor. Here is Haier’s “3D Gesture Control.” (Photo: Ken Werner)</dd>
</dl></div>
<div>
<dl id="attachment_15688"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0248_cropped.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0248_cropped" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0248_cropped.jpg" width="600" height="807" /></a></dt>
<dd>Haier hired a champion pizza-crust tosser to spin dough in its booth. The intent was draw attention to the company’s major kitchen appliances. (Photo: Ken Werner)</dd>
</dl></div>
<p>Konka was showing a touch TV, its own 84-inch 4K, and a transparent TV. Transparent LCDs have obvious application in retail signage and merchandizing, but if you can figure out why anyone would want their home TV set to be transparent, please let me know. Actually, old friend Bob Raikes of MEKO’s Display Monitor has suggested a reason. Raikes says that some high-end buyers have complained about the “black hole” on their wall when their TVs are turned off. A few of them have actually shown an image of their wallpaper on the TV to make the set blend into the wall, but this consumes power. So, suggested Raikes, a transparent TV could solve this problem for those who feel it’s a problem.</p>
<div>
<dl id="attachment_15689"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0222_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0222_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0222_medres.jpg" width="600" height="951" /></a></dt>
<dd>Konka’s transparent TV. (Photo: Ken Werner)</dd>
</dl></div>
<p>Alas! The LCD in a “transparent” LCD-TV is no more transparent than the LCD in any other TV, which is approximately 6%. Some of the transparent LCDs made for retail shop windows or display cases may have pushed that number up by a few percentage points by decreasing the density of the matrix color filter and thus sacrificing color gamut. That’s a reasonable compromise for retail applications but it would not be acceptable for consumer TV. Thus, at best, Raikes’ “black hole” on the wall would become a “very dark gray hole.” Even worse, transparent TVs and monitors work by using the bright light behind them to substitute for the backlight on normal LCD displays. Again, no problem in retail applications, where you can light a display case as brightly as you like, but brightly lighting the wall behind a transparent TV – like a Philips Ambilight on steroids – is not likely to appeal to viewers.</p>
<p>Changhong was featuring its “New B Series” of TVs with “motion interaction,” “recognition interaction,” and “UHD Interaction.”  No, I’m not quite sure what those terms mean, either, but they presumably enabled a demo in which the viewer was invited to play chess with an elegant Chinese woman, both the woman and the chess pieces existing only behind the TV screen.</p>
<div>
<dl id="attachment_15700"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0227_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0227_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0227_medres.jpg" width="600" height="397" /></a></dt>
<dd>Chanhong’s demonstration of a gesturally controlled interactive TV chess game. (Photo: Ken Werner)</dd>
</dl></div>
<p>In its suite in the Las Vegas Hotel, Westinghouse Digital (WD) showed the widest range of 4K sets at the show, from 110 inches at the top to 50 inches at the bottom. And Westinghouse was the only vendor to announce prices for its 4K sets. The 110-inch 4K at the t 110-inch 4K is available on special order at the end of Q1 for about $300K. The panel is from CSOT’s new Gen 8.5 fab in Shenzhen, where it is made “one-up”; that is, each substrate that goes through the fab produces only one 110-inch panel. This is a very expensive way to make TV displays and it invites low manufacturing yields. Westinghouse Digital’s Rey Roque, speculated that making 98-inch panels two-up on the Gen 8.5-fab might be a more viable combination. TCL and Samsung also use the CSOT 110-inch panel. Sharp’s Gen 10 fab should be able to produce very large 4K panels at lower cost.</p>
<div>
<dl id="attachment_15691"><dt><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0337_cropped_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0337_cropped_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0337_cropped_medres.jpg" width="600" height="342" /></a></dt>
<dd>Image on the Westinghouse Digital 4Kx8K 110-inch TV. Since this image has been down-converted to 600 pixels wide, you will have to take our word for the fact that this image conveyed a remarkable impression of three-dimensionality although it was a 2D image. (Photo: Ken Werner)</dd>
</dl></div>
<p>In the Westinghouse suite Roque said that the 65-inch 4K panel would be available at the end of Q1 for $3999; the 55-inch 4K, end Q2, $2999; and the 50-inch 4K, end Q2, $2499. Roque said he did not know from where the smaller panels were being sourced, but it was not CSOT.</p>
<p><a href="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0335_cropped_medres.jpg" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.display-central.com']);"><img title="DSC_0335_cropped_medres" alt="" src="http://www.display-central.com/wp-content/uploads/2013/01/DSC_0335_cropped_medres.jpg" width="300" height="71" /></a>Most of WD’s larger edge-lit TVs will Mobile High-Definition Link (MHL) compliant. MHL is the industry standard audio-visual interface for connecting mobile phones and other portable devices to TVs and other displays. MHL supports 1080p video and digital audio, and also provides for powering/charging the mobile device. Westinghouse does this through what looks like an HDMI connector, although it is now an HML connector. The sets are “Roku Ready” and directly provide signal and power to a Roku Stick through the single HML connector. The Roku Stick provides the same functionality as Roku streaming box, but in a much smaller and tidier package. WD may bundle a Roku Stick with some Roku ready models.</p>
<p>WD revealed that their Direct LED (DLED) sets use a backlight design borrowed from CCFL backlights, with, typically, three or four horizontal LED channels replacing the CCFLs. The design is slightly more costly than an equivalent CCFL backlight, but the power efficiency is better – and it is much cheaper than an Edge LED (ELED) with its expensive light guide plate, WD said.</p>
<p>Mark Twain once said of a dancing bear that the wonder is not that the bear dances poorly, but that it dances at all. That was not at all the case with the Chinese TV manufacturers at CES. The Chinese dragons were dancing with energy and grace.</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January 28, 2013  5:45 PM</b>
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
			<?=getComments(5017)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5017)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/01/hdtv-expert-china-attacks.php" type="text/javascript" charset="utf-8"></script>
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