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
		AND e.entry_id = 4882";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4882 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4882 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4882";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/07/hdtv-expert-useful-gadgets-indoor-dtv-antennas-the-third-times-the-charm.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4882";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Indoor DTV Antennas &ndash; The Third Time&rsquo;s The Charm" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Indoor DTV Antennas &ndash; The Third Time&rsquo;s The Charm" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Indoor DTV Antennas &ndash; The Third Time&rsquo;s The Charm" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Indoor DTV Antennas &ndash; The Third Time&rsquo;s The Charm</title>
	<meta name="keywords" content="yes yes, bow tie, clear cast, mohu leaf, clearstream micron, yes, antenna, test, micron, antennas, reception, bow, –, reflector, clear, tie, leaf, plus, amplifier, ’s, here, dtv, vhf, indoor, cast" />
	<meta name="description" content="Time for one more road trip to finish off an in-depth test of indoor DTV antennas. You may be surprised at the results!" />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Indoor DTV Antennas &amp;ndash; The Third Time&amp;rsquo;s The Charm" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Indoor DTV Antennas &amp;ndash; The Third Time&amp;rsquo;s The Charm" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/07/hdtv-expert-useful-gadgets-indoor-dtv-antennas-the-third-times-the-charm.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Time for one more road trip to finish off an in-depth test of indoor DTV antennas. You may be surprised at the results!" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4882', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/07/hdtv-expert-useful-gadgets-indoor-dtv-antennas-the-third-times-the-charm.php">HDTV Expert - Useful Gadgets: Indoor DTV Antennas &ndash; The Third Time&rsquo;s The Charm</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>July 31, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=457&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
          <p>Earlier this year, I posted a couple of product reviews of indoor digital TV antennas. The first test, <a href="http://www.hdtvexpert.com/?p=2053">posted on April 6</a>, concluded that there isn’t a heck of a lot of difference between a $5 bow tie and a $40 ‘flat’ antenna when it comes to VHF and UHF TV reception.</p>
<p>The second test, <a href="http://www.hdtvexpert.com/?p=2105">posted on May 29</a>, gave one manufacturer a ‘do-over’ as their original product didn’t perform all that well and was judged to be defective. And that test also included a newcomer who didn’t make the original cut. (Believe it or not, both tests grew out of a more impromptu test in my house of a couple of panel antennas!)</p>
<p>Since the Round 2 results were posted, three things transpired. First, I became aware of yet another indoor DTV antenna, called the Clear Cast X1 and sold through Sunday newspaper inserts, magazines, and <a href="https://www.clear-cast.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.clear-cast.com']);">even on this Web site</a>.</p>
<p>According to Clear Cast, <em>“Advanced patent pending design of the X-1 digital antenna pulls in free over-the-air digital and HDTV broadcasts in your area so you can leave behind cable-only channels &amp; expensive cable &amp; satellite bills. Receive crystal clear digital picture on any digital TV in the house with NO monthly bill, easy install and setup plus NO waiting for the cable guy.” </em>OK, I was intrigued enough to order one (they’re not cheap!)</p>
<p>Secondly, the PR firm that represents Antennas Direct – the company that shipped me a Clear Stream Micron XG for Round 2 testing – inquired why I hadn’t tested the accessory reflector with the antenna. (Simple: As Steve Martin used to say, “I forgot!”)</p>
<p>Finally, the Mohu Leaf Plus that self-destructed in Round 2 had been replaced and was ready for another go. (The amplifier failed, a problem Mohu was aware of and corrected in subsequent production.)</p>
<p>So it was clearly time for one last trek to Mountain Lakes, NJ to put all of the antennas from Round 1 and Round 2 through one more workout. I loaded up my spectrum analyzer, computer, several spools of coax, and a few splitters and headed out to put this test to bed once and for all.</p>
<p>THE TEST</p>
<p>For Rounds 1 and 2, I used the same window as the desk in front of it was unoccupied at the time. This time around, I opted for a slightly different location between two desks so that I wasn’t interfering with everyone’s work. Additionally; since the test position had now shifted by about six feet, I decided to re-test every antenna from Rounds 1 and 2 to be consistent and fair to all.</p>
<div id="attachment_2282" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2282" title="Wide View of Test Setup MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/Wide-View-of-Test-Setup-MR.jpg" alt="" width="600" height="800" /><p class="wp-caption-text">Here's what the test site looked like.</p></div>
<p> </p>
<div id="attachment_2283" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2283" title="Bow Tie in Place MS MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/Bow-Tie-in-Place-MS-MR.jpg" alt="" width="600" height="450" /><p class="wp-caption-text">And here's the 'reference' bow tie antenna taped to the window.</p></div>
<p>I was assisted in my endeavor by John Turner, the owner and president of Turner Engineering and a long-time veteran of the broadcast systems integration world. Using AVCOM’s PSA-2500C spectrum analyzer, we positioned a $4.99 Radio Shack bow tie antenna (no longer available) for best reception of WNJM-51 (now known as “NJTV”) out of Montclair, NJ.</p>
<p>I also connected a Hauppauge Aero-M USB stick DTV received to pull in each station, in tandem with the TS Reader MPEG stream analyzer program to verify reliable reception (i.e. low bit rate errors). Each antenna under test fed the spectrum analyzer and Aero-M through a two-way splitter, and each antenna was placed in exactly the same spot on the east-facing window, using four pieces of masking tape as markers for alignment.</p>
<p>For each test, I scanned for channels using the Aero-M receiver. Next, I scanned each physical TV channel that was received with TS Reader to see how clean that stations’ MPEG stream was. Finally, I captured screen shots of the actual waveforms from each station I received. And if those three steps didn’t prove which antenna works the best, I don’t know what would!</p>
<p>THE RESULTS</p>
<p>For the record, here are all of the test antennas:</p>
<p> </p>
<p>Radio Shack bow tie ($4.99, no longer offered, but you can find them on eBay)</p>
<p><a href="https://www.clear-cast.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.clear-cast.com']);">Clear Cast X1</a> ($68 plus shipping)</p>
<p><a href="http://www.walltenna.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.walltenna.com']);">Walltenna</a> ($35 plus shipping)</p>
<p><a href="http://store.gomohu.com/the-leaf-indoor-hdtv-antenna.html" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://store.gomohu.com']);">Mohu Lea</a>f ($38 plus shipping)</p>
<p><a href="http://store.gomohu.com/the-leaf-plus-amplified-indoor-hdtv-antenna.html" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://store.gomohu.com']);">Mohu Leaf Plus</a> ($75 plus shipping)*</p>
<p><a href="http://flatwave.tv/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://flatwave.tv']);">Winegard FlatWave</a> ($40, free shipping through August 31)</p>
<p><a href="http://www.antennasdirect.com/store/ClearStream-Micron-XG-Indoor-Digital-TV-Antenna.html" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.antennasdirect.com']);">Antennas Direct ClearStream Micron XG</a> ($100 plus shipping)*</p>
<p>* – <em>amplified, or comes with optional amplifier</em></p>
<p> </p>
<p>For my tests, I scanned for all New York City and New Jersey DTV stations within range of Turner Engineering. One local station (WMBC-18) was so strong that I essentially discounted it from my test results – it would have come in with a paper clip!</p>
<div id="attachment_2284" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2284" title="Clear Cast X1 Closeup MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/Clear-Cast-X1-Closeup-MR.jpg" alt="" width="600" height="450" /><p class="wp-caption-text">The Clear Cast X1 is definitely NOT worth $70. Let the buyer beware!</p></div>
<p> </p>
<p>But other stations weren’t quite as strong. WABC-7 is a good test of high band VHF reception, inasmuch as every antenna in the test is supposed to pull in both VHF and UHF signals. WNJB-8 in the Watchung Hills of New Jersey is another good test of VHF reception.</p>
<p>For UHF signals, I checked out WNYE-24 (atop the Empire State Building), WNBC-28 (also on Empire and usually strong), WFME-29 (in West Orange, NJ), WFUT-30 (on Empire), WCBS-33 (Empire), WWOR-38 (Empire), and WNJM-51 (Montclair, NJ).</p>
<p>I didn’t expect the antennas to have much luck with WABC or WNJB, as they are too small to have much gain at VHF frequencies. The amplified antennas were a different story, though. If you are aggressively marketing indoor TV antennas for ‘all band’ reception, then you’d better deliver!</p>
<p>Table 1 shows how the unamplified antennas compared to each other. Satisfactory reception is indicated by glitch-free video streams for at least one minute and a ‘clean’ reading with TS Reader, while Table 2 shows how the amplified antennas (or amplified variations) compared.</p>
<div id="attachment_2285" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2285" title="Antennas Direct in Place MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/Antennas-Direct-in-Place-MR.jpg" alt="" width="600" height="450" /><p class="wp-caption-text">Yes, you can actually attach the Micron XG to glass with masking tape! (The reflector was a tad more difficult to install...)</p></div>
<p> </p>
<p>Note that the ClearStream Micron XG was tested three different ways –‘ bare bones’ with no amplifier or reflector in Table 1; with its amplifier switched to 15 dB mode in Table 2, and with the amplifier on and the accessory reflector attached in Table 2.</p>
<p> </p>
<table border="1" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" width="64">
<p align="center"><strong>Antenna</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WABC-7</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNJB-8</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNYE-24</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNBC-28</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WFME-29</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WFUT-30</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WCBS-33</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WWOR-38</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNJB-51</strong></p>
</td>
</tr><tr><td valign="top" width="64">
<p align="center"><strong>RS Bow Tie</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr><tr><td valign="top" width="64">
<p align="center"><strong>CC X1</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><em><strong>No</strong></em></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
</tr><tr><td valign="top" width="64">
<p align="center"><strong>Walltenna</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr><tr><td valign="top" width="64">
<p align="center"><strong>Mohu Leaf</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr><tr><td valign="top" width="64">
<p align="center"><strong>FlatWave</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr><tr><td valign="top" width="64">
<p align="center"><strong>Micron XG</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr></tbody></table><p><em>Table 1 – Unamplified antenna performance</em></p>
<p> </p>
<table border="1" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" width="64">
<p align="center"><strong>Antenna</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WABC-7</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNJB-8</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNYE-24</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNBC-28</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WFME-29</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WFUT-30</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WCBS-33</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WWOR-38</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>WNJB-51</strong></p>
</td>
</tr><tr><td valign="top" width="64"><strong>Leaf Plus</strong></td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr><tr><td valign="top" width="64"><strong>Micron XG w/amp</strong></td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr><tr><td valign="top" width="64"><strong>Micron XG w/amp and refl.</strong></td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong><em>No</em></strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
<td valign="top" width="64">
<p align="center"><strong>Yes</strong></p>
</td>
</tr></tbody></table><p><em>Table 2 – Amplified antenna performance</em></p>
<p><em> </em></p>
<p>Oddly enough, the Micron XG was the only unamplified antenna to pull in WWOR-38. But it was ‘tone deaf’ when it came to the two high band VHF stations. Neither version of the Mohu Leaf could snag WWOR-38, either.</p>
<p>As for the vaunted Clear Cast X1, it was unresponsive to any VHF channels and couldn’t hear local station WNJM-51. In contrast, the late, lamented Radio Shack bow tie worked exceptionally well on just about every UHF channel.</p>
<p>Bonus reception: WNJU-36, which is a tough signal to pull in at this indoor location, was successfully reeled in by the Micron XG with amplifier and reflector. So was WXTV-40, also pulled in with and without the accessory reflector.</p>
<p>THE CHARTS</p>
<p>I’ve included a few charts to show what the actual DTV received signals looked like on the AVCOM analyzer. You may be surprised to see how small the differences are between each antenna, and you will also note that the reflector didn’t improve reception at all on the Micron XG – in fact, it actually made things worse, probably due to all of the signal reflections and multipath at the test site.</p>
<p>As a reference, the actual signal levels shown are about 12 dB stronger at the displayed resolution bandwidth (300 kHz).</p>
<div id="attachment_2286" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2286" title="UHF SPECTRUM BOW TIE MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/UHF-SPECTRUM-BOW-TIE-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">Here's what the RF spectrum looks like from channels 18 to 51, using the bow tie antenna.</p></div>
<p> </p>
<div id="attachment_2287" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2287" title="UHF SPECTRUM CC X1 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/UHF-SPECTRUM-CC-X1-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">And here's the same spectral view using the Clear Cast X1...</p></div>
<p> </p>
<div id="attachment_2288" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2288" title="UHF SPECTRUM CLEARSTREAM NO AMP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/UHF-SPECTRUM-CLEARSTREAM-NO-AMP-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">...using the ClearStream Micron XG...</p></div>
<p> </p>
<div id="attachment_2289" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2289" title="UHF SPECTRUM MOHU LEAF MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/UHF-SPECTRUM-MOHU-LEAF-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">...and using the Mohu Leaf (no amplifier).</p></div>
<p> </p>
<div id="attachment_2291" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2291" title="WNJM-51 BOW TIE MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/WNJM-51-BOW-TIE-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">Here's channel 51, the former WNJM, as received on the bow tie...</p></div>
<p> </p>
<div id="attachment_2292" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2292" title="WNJM-51 CC X1 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/WNJM-51-CC-X1-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">...and here's the same station on the Clear Cast X1. No improvement.</p></div>
<p> </p>
<div id="attachment_2293" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2293" title="WNJM-51 FLATWAVE MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/WNJM-51-FLATWAVE-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">Winegard's FlatWave pulled in channel 51 more robustly...</p></div>
<p> </p>
<div id="attachment_2294" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2294" title="WNJM-51 WALLTENNA MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/WNJM-51-WALLTENNA-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">...as did the Walltenna.</p></div>
<p> </p>
<div id="attachment_2295" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2295" title="WWOR-38 WXTV-40 CLEARSTREAM W 15 AMP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/WWOR-38-WXTV-40-CLEARSTREAM-W-15-AMP-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">Finally, here are received waveforms for WNJU-36 and WWOR-38, using the ClearStream MIcron XG with the amplifier set to 15 dB, but minus the reflector...</p></div>
<p> </p>
<div id="attachment_2296" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2296" title="WWOR-38 WXTV-40 CLEARSTREAM W REFLECTOR AND AMP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/07/WWOR-38-WXTV-40-CLEARSTREAM-W-REFLECTOR-AND-AMP-MR.jpg" alt="" width="600" height="317" /><p class="wp-caption-text">...and here's what those same waveforms looked like AFTER I installed the reflector. Reception actually worsened, something I saw on numerous other UHF channels. Indoor DTV reception can be funny that way!</p></div>
<p>CONCLUSIONS</p>
<p>It’s hard to make much or an argument for spending very much money on an indoor DTV antenna when you see how well the lowly $5 bow tie design performed! This antenna design has been around since the 1950s and is just one of those things that can’t be improved on – unless you build an array of them. (‘X’-shaped colinear UHF antennas perform the same as the bow ties.) It’s just unfortunate that no mainstream electronics retailer sells these anymore. (Hey Radio Shack, are you listening?)</p>
<p>However, it’s easy to make the argument that the Clear Cast X1 is definitely <span style="text-decoration: underline;">not</span> worth spending $70 on, especially since it was easily outperformed by the far less costly Leaf, Walltenna, and FlatWave antennas. Even the bow tie picked up six more stations than the X1 in my overall tests, two of them on VHF. I don’t know what’s inside the plastic housing, but I’d bet it is nothing more than a simple dipole, bow tie, or loop antenna (Clear Cast’s claims to having a ‘patent pending’ notwithstanding). Keep your wallets in your pockets!</p>
<p>Among the basic flat antennas, I still prefer the Leaf – it’s smaller and more esthetically pleasing than the Walltenna (which still  does a good job, better than the FlatWave) and it’s been a reliable performer everywhere I travel. The Leaf Plus is a bit pricey at $75, but the amplifier – while not as powerful as that on the ClearStream Micron XG – helps pull in marginal stations and doesn’t add much to the form factor.</p>
<p>As for the Micron XG, I had mixed feelings about it. It’s big and somewhat blocky, expensive, and based on my tests, you can’t depend on it for VHF reception in suburban locations, a chore the other ‘flat’ antennas handled without much difficulty. In its favor, the Micron XG <span style="text-decoration: underline;">did</span> pull in WWOR, something no other antenna could do. (Maybe that outcome was just a fortuitous combination of antenna position and signal level?)</p>
<p>The Micron XG amplifier makes a big improvement, but I’d suggest running it no higher than 15 dB. The 20 dB setting creates too much noise and also degrades weak signals, as observed with the spectrum analyzer. The lower-gain 10 dB setting is also very handy in fringe urban areas where you don’t need tons of signal, but just need to boost the carrier-to-noise ratio (CNR) a bit.</p>
<p>And that reflector? It’s hardly worth bothering with, as it didn’t improve reception on any of the tested channels and in some cases degraded it. Those results were puzzling, because the reflector effectively converts the antenna pattern to something resembling a two-element yagi, which should have more gain as it becomes more directional. Maybe you’d have different results over a line-of-sight (LOS) path, but that’s hard to ensure when trying to grab DTV signals indoors.</p>
<p>In any case, you should be able to get a decent indoor DTV antenna for less than $50. Stay away from the amplified versions unless you live in a fringe urban or outer suburban area, where there are less likely to be out-of-band sources of overload and interference. Always place your antenna near a window and/or closest to the direction of the TV transmitter(s) for best results.</p>
<p>Good luck!</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>July 31, 2012  5:46 PM</b>
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
			<?=getComments(4882)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4882)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/07/hdtv-expert-useful-gadgets-indoor-dtv-antennas-the-third-times-the-charm.php" type="text/javascript" charset="utf-8"></script>
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