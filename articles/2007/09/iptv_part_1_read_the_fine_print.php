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
		AND e.entry_id = 722";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 722 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 722 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 722";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/09/iptv-part-1-read-the-fine-print.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 722";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download IPTV Part 1 - Read the Fine Print" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="IPTV Part 1 - Read the Fine Print" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="IPTV Part 1 - Read the Fine Print" />
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
	<title>HDTV Magazine - IPTV Part 1 - Read the Fine Print</title>
	<meta name="keywords" content="iptv part, per month, real time, cable satellite, industry experts, iptv, networks, content, video, distribution, quality, part, bandwidth, service, services, cable, channels, using, hdtv, month, fiber, broadband, pipe, same, real" />
	<meta name="description" content="IPTV is becoming a buzzword used to generally name TV distribution using IP networks. Some IP content distributors that only offer limited services making subscribers believe this is a full service with all the features of legacy digital cable are also using this term very loosely.

There are several camps on this subject. On one side some industry experts firmly believe that the infrastructure of our current IP networks is solid enough and is ready for full-blown HDTV distribution over IP. As you will see below..." />
	<meta name="title" content="IPTV Part 1 - Read the Fine Print" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="IPTV Part 1 - Read the Fine Print" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/09/iptv-part-1-read-the-fine-print.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="IPTV is becoming a buzzword used to generally name TV distribution using IP networks. Some IP content distributors that only offer limited services making subscribers believe this is a full service with all the features of legacy digital cable are also using this term very loosely.

There are several camps on this subject. On one side some industry experts firmly believe that the infrastructure of our current IP networks is solid enough and is ready for full-blown HDTV distribution over IP. As you will see below..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=722', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/09/iptv-part-1-read-the-fine-print.php">IPTV Part 1 - Read the Fine Print</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September 19, 2007</b>
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
				<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></li>
<li><a href="/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php">IPTV Part 3 - The Methods and a Working Technology</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_5_-_additional_implementations.php">IPTV Part 5 - Additional Implementations</a></li>
<li><a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></li>
</ul></div>
<br />
<B>Introduction</B>

<p>IPTV is becoming a buzzword used to generally name TV distribution using IP networks. Some IP content distributors that only offer limited services making subscribers believe this is a full service with all the features of legacy digital cable are also using this term very loosely.</p>

<p><img src="/images/products/matrixstream.jpg" alt="MatrixStream" align="right" />There are several camps on this subject. On one side some industry experts firmly believe that the infrastructure of our current IP networks is solid enough and is ready for full-blown HDTV distribution over IP. As you will see below several companies and Telcos have not only started testing IPTV, they have also successfully implemented services for thousands of subscribers.</p>

<p>Other industry experts assert that consumers customarily use more bandwidth than what current networks can support, and the current capacity would not be able to also support real time HD video in addition to the variety of digital activities consumers do every day. These experts say that HDTV distribution using IP would not replace real-time traditional broadband methods such cable and satellite.</p>

<p>Some actual implementations from manufactures and Telcos offer hardware and services that give the impression that IPTV is ready to handle several HDTVs programs in parallel to having several PCs in the same house downloading movies, music, files, photos, etc. all running over the same IP pipe.</p>

<p><br />
<B>What Does IPTV Mean to You?</B></p>

<p>IPTV manages TV signals stored as digital files that can be distributed within packets using Internet Protocol (IP) to devices such as video cell phones, iPods, and other portable receivers, in addition to the TVs at home.</p>

<p>One IPTV application is the video service distribution from telecommunications companies such as Verizon, Bell South, Qualcomm, and AT&T Wireless to deliver movies, TV shows and sports highlights to the subscriber's PCs or handsets.</p>

<p>IPTV is also the term used by Telcos to describe sending telephone, high-speed Internet and TV channels over hybrid coax or fiber-optic cable.</p>

<p>Some IPTV services supply only VOD; others include real-time HDTV as well.</p>

<p>One of the virtues of IPTV is the ability to maintain a two-way communication with subscribers, as opposed to unidirectional terrestrial broadcasters. However, with IPTV those broadcasters can generate new revenues from the same content by expanding the distribution via IPTV to destinations other than regular antennas.</p>

<p>According to Broadcast Engineering "ESPN and CNN HD content viewed by consumers at their PCs is distributed using a separate IP routing system that delivers the video over the Internet within IP packets. Tribune Broadcasting and Sinclair Broadcast Group use TeleStream's FlipFactory for IP delivery to share content and leverage technologies, such as from the Associated Press, CBS News Source, Path 1 and other video resources."</p>

<p><br />
<B>The Camp in Favor of IPTV</B></p>

<p>IPTV lowers costs, video is distributed in real-time or near real-time reliably, and can also be sent and stored for later viewing.</p>

<p><img src="/images/articles/ip-set-top-solutions.jpg" alt="IP Set-top Solutions" align="right" />Even if the receiving-end converts the video to baseband, the IP distribution system still moves and shares content easier than a typical baseband video distribution system (easier for the sender and for the receiver of the content).</p>

<p>A Telco may not need to lay down new wiring to deliver additional content to the subscriber. If the viewed channel is the only one delivered through the pipe, an IPTV channel line-up might grow independently of the distribution model. This is in contrast to the bandwidth limited services cable and satellite offer.</p>

<p>According to Broadcast Engineering: "With baseband, the assumption is that fat, proprietary pipes are available whenever you need them. IP comes from the computer data world, where bits are just bits, no matter what they describe. It's designed to accommodate limited bandwidths and high traffic networks, and employs security and error-correction algorithms that add overhead to a file and in some cases is not the most efficient (or fastest) distribution method. Connection protocols like Fiber Channel and Gigabit Ethernet help move these bits quickly and reliably. Television in its current form is becoming obsolete."</p>

<p><br />
<B>The "Not-so-Fast" Camp</B></p>

<p>Following are some excerpts from BernsteinResearch's Craig Moffett's testimony before the Senate Commerce Committee on March 17, 2006 on issues related to the pending telecommunications reform, "Net Neutrality", and IPTV:</p>

<p><br />
<br clear="all" /><blockquote>"...despite a great deal of arm waving from "visionaries," our telecommunications infrastructure is woefully unprepared for widespread delivery of advanced services, especially video, over the Internet. Downloading a single half hour TV show on the web consumes more bandwidth than does receiving 200 emails a day for a full year. Downloading a single high definition movie consumes more bandwidth than does the downloading of 35,000 web pages; it's the equivalent of downloading 2,300 songs over Apple's iTunes web site. Today's networks simply aren't scaled for that."</p>

<p>"In a series of recent research reports that I entitled "The Dumb Pipe Paradox" - which I believe provided the original impetus for the Committee's invitation to testify today - I tried to address the expectation that the telcos are rapidly rushing in to meet this need and to provide competition for cable incumbents. In fact, by their own best estimates, they'll be able to reach no more than 40% or so of American households with fiber over the next seven years. And most of that will be in the form of hybrid fiber/legacy copper networks, such as that being constructed by AT&T under the banner of "Project Lightspeed."</p>

<p>"These hybrid networks are expected to deliver 20Mbs average downstream bandwidth. After accounting for significant standard deviation around that average, that will mean many "enabled" subscribers will actually receive far less. I and many others on Wall Street harbor real doubts as whether these hybrid networks will prove technologically sufficient to meet future demands."</p>

<p>"More importantly, in 60% of the country, there are simply no new networks on the horizon, and the existing infrastructure from the telcos - DSL running at speeds of just 1.5Mbs or so - simply won't be adequate to be considered "broadband" in five years or so. That includes wireless networks, by the way. Current and planned wireless networks - including the over-hyped Wi-Max technology - offer the promise of satisfying today's definition of broadband, but simply can't feasibly support the kind of bandwidth required for the kind of dedicated point-to-point video connections that will be required to be considered broadband tomorrow. Those demands will continue to fall to terrestrial wired networks."</p>

<p>"In Part I of the "Dumb Pipe Paradox," I noted that if a telco was in the business of providing broadband connections only - that is, if phone service becomes, as many predict, simply another bit stream on top of a data connection - then the cost to provide service would be as much as $80 per month. And from a consumer's perspective, that would be the pipe only, before paying for any content over the web. And the cost, and therefore the price, would likely be much, much more."</p>

<p>"Some recent comments from BellSouth's Chief Architect, Henry Kafka, at the Optical Fiber Communication/National Fiber Optics Engineers Conference last week put this in perspective. He estimated that the average residential broadband user today consumes about two gigabytes of data per month. Heavy users who regularly download movies consume an average of 9 gigabytes of data per month. In the future, watching IPTV would consume 224 gigabytes, and would cost carriers $112 per month to deliver. And if IPTV is going to deliver High Definition, then the average user would be consuming more than one terabyte per month, at a cost to carriers of $560 per month."</blockquote></p>

<p><br />
<B>Lessons Learned from the HDTV World</B></p>

<p>After witnessing what the industry has done to image quality for the sake of squeezing in a few extra channels to generate a profit, I would say, "be cautious and read the fine print".</p>

<p>Before rushing to IPTV I now use a magnifying glass to read the smaller font on IPTV offerings, surprises most probably appear on hard-to-find footnotes.</p>

<p>Mark Cuban, the owner of probably the best quality HD feed, HDNet, was favoring the position above in one of his blogs and on the HDTV Conference sponsored by the CEA on 2006 at Washington DC.</p>

<p>Mark Cuban is a self-made millionaire and a visionary that is pro HD quality, and probably the best that could happen for HD content since 1998.</p>

<p>Mark strives to produce and distribute the best image that HD technology can provide to your home. He is certainly not about quantity: no compression, no bit-starving for more channels sacrificing the quality of the existing HD channels, no needless content protection that assumes everyone is a pirate, etc. Just one look at his HD channel will make all of this obvious.</p>

<p>I personally share the same principles. I am 100% against quantity-oriented models designed to generate a profit by offering an abundance of inferior content rather than a few well-chosen channels providing true HD quality within the bounds of the available bandwidth.</p>

<p>The few "early adopters" that have followed the evolution of HDTV since 1998 (and before) can attest that HD quality has been consistently butchered down to its knees by the "never enough" compression games implemented by satellite, cable, and broadcasters trying to send more channels over the same pipe, or by multicasting SD sub-channels while robbing from the bandwidth needed for the HD channel; and lately by the proposed A-VSB for broadcasting DTV for mobile purposes using the same 6MHz slot, which is discussed at length in the recently published series on the subject. (For more, start with <a href="/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The System</a></p>

<p>Using the opportunities brought forth by the digital world to compromise the quality of HDTV as created is certainly a step backward.</p>

<p><br />
<B>Do Your Home Work</B></p>

<p>Some IPTV implementations could be acceptable to some subscribers that match their requirements with the characteristics of the service, but some IPTV services are not an actual improvement from the classical cable or satellite, not even in cost.</p>

<p>The trick is to completely research the technical, functional, performance and quality conditions of the IPTV service and hardware before you close an account with another service.</p>

<p>A short trial period in parallel to the existing service is a good idea if offered; it would give you the chance to also compare image quality side by side, and if one of the two services freezes up entirely while tennis star Federer is smashing the championship match-point, there is no need to compare any longer.</p>

<p>In the next article, I will have comprehensive coverage about IPTV implementation, companies involved, hardware options, what some companies do not disclose to potential subscribers, some success stories, and some lessons learned from not-so-good implementation stories.</p>

<p>Next Article: <a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September 19, 2007 10:45 AM</b>
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
			<?=getComments(722)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 722)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/iptv-part-1-read-the-fine-print.php" type="text/javascript" charset="utf-8"></script>
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