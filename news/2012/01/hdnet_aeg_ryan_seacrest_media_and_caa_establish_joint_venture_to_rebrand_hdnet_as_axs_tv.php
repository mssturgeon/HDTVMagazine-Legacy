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
		AND e.entry_id = 4643";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4643 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4643 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4643";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/hdnet-aeg-ryan-seacrest-media-and-caa-establish-joint-venture-to-rebrand-hdnet-as-axs-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4643";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV" />
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
	<title>HDTV Magazine - HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV</title>
	<meta name="keywords" content="ryan seacrest, seacrest media, live entertainment, los angeles, dish network, hdnet, aeg, axs, live, seacrest, dish, network, entertainment, ryan, caa, programming, new, our, world, series, company, movies, including, media, venture" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/axs-tv.jpeg&quot; alt=&quot;AXS TV&quot; height=&quot;43&quot; width=&quot;144&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;AEG, Ryan Seacrest Media, and Creative Artists Agency (CAA) today announced a joint venture that will rebrand HDNet/HDNet Canada to launch AXS TV (pronounced: access). Scheduled to debut this summer, AXS TV will leverage the global reach and portfolio of content opportunities of its partners to create live entertainment and lifestyle programming. Capitalizing on AEG's unrivaled presence in live events and affiliation with more than 100 of the industry's pre-eminent venues worldwide, AXS TV will provide viewers with exclusive behind-the-scenes access to live concerts and music festivals, red carpet premieres, award shows, parties, pop culture events, and in-depth interviews with the people and artists who make live entertainment so uniquely fascinating.

HDNet's signature programming including HDNet Fights, Inside MMA, award-winning Dan Rather Reports, Sunday concert series, and select non-scripted series will..." />
	<meta name="title" content="HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/hdnet-aeg-ryan-seacrest-media-and-caa-establish-joint-venture-to-rebrand-hdnet-as-axs-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/axs-tv.jpeg&quot; alt=&quot;AXS TV&quot; height=&quot;43&quot; width=&quot;144&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;AEG, Ryan Seacrest Media, and Creative Artists Agency (CAA) today announced a joint venture that will rebrand HDNet/HDNet Canada to launch AXS TV (pronounced: access). Scheduled to debut this summer, AXS TV will leverage the global reach and portfolio of content opportunities of its partners to create live entertainment and lifestyle programming. Capitalizing on AEG's unrivaled presence in live events and affiliation with more than 100 of the industry's pre-eminent venues worldwide, AXS TV will provide viewers with exclusive behind-the-scenes access to live concerts and music festivals, red carpet premieres, award shows, parties, pop culture events, and in-depth interviews with the people and artists who make live entertainment so uniquely fascinating.

HDNet's signature programming including HDNet Fights, Inside MMA, award-winning Dan Rather Reports, Sunday concert series, and select non-scripted series will..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4643', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/hdnet-aeg-ryan-seacrest-media-and-caa-establish-joint-venture-to-rebrand-hdnet-as-axs-tv.php">HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 18, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=270&category=Programming">Programming</a></b>, <b><a href="/category.php?id=372&category=Satellite HDTV">Satellite HDTV</a></b>
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
				<p class="prtitle">HDNet, AEG, Ryan Seacrest Media, and CAA Establish Joint Venture to Rebrand HDNet as AXS TV</p>

<center><i>Beginning Summer 2012, AXS TV Will Deliver a Full Schedule of Live Entertainment and Lifestyle Programming to More Than 35 Million Homes Across North America<br /><br />DISH Network Commits to Expanded Distribution of Rebranded Network, While Providing Video-On-Demand Concerts and Unique Ticketing Opportunities for Subscribers</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/axs-tv.jpeg" alt="AXS TV" height="43" width="144" class="keyimg"><strong>LOS ANGELES, Jan. 18, 2012 /PRNewswire/ --</strong> AEG, Ryan Seacrest Media, and Creative Artists Agency (CAA) today announced a joint venture that will rebrand HDNet/HDNet Canada to launch AXS TV (pronounced: access). Scheduled to debut this summer, AXS TV will leverage the global reach and portfolio of content opportunities of its partners to create live entertainment and lifestyle programming. Capitalizing on AEG's unrivaled presence in live events and affiliation with more than 100 of the industry's pre-eminent venues worldwide, AXS TV will provide viewers with exclusive behind-the-scenes access to live concerts and music festivals, red carpet premieres, award shows, parties, pop culture events, and in-depth interviews with the people and artists who make live entertainment so uniquely fascinating.</p>

<p>HDNet's signature programming including HDNet Fights, Inside MMA, award-winning Dan Rather Reports, Sunday concert series, and select non-scripted series will continue on AXS TV.</p>

<p>As part of their agreement with the joint venture, DISH will offer a variety of unique music services to their subscribers.</p>

<p>"I'm incredibly excited to be in business with AEG, Ryan Seacrest, and CAA," said Mark Cuban , Co-Founder and Chairman, HDNet.  "This is a major step on our way to offering more live programming than any other entertainment and lifestyle network.  And our partnership with DISH to offer unique subscriber services will allow AXS TV to change the value proposition between networks, distributors, and subscribers.  AXS TV will unquestionably be able to leverage our unique assets to do things no other network will be able to replicate."</p>

<p>With production facilities at AEG's owned and operated L.A. LIVE (which includes venues such as STAPLES Center, Nokia Theatre L.A. LIVE, Club Nokia and The GRAMMY Museum's Clive Davis Theater), as well as their access to their worldwide network of the busiest venues of all sizes, AXS TV will have the inside track to programming that will connect audiences with the complete live experience. From show creation and development to rehearsals, sound-check and performance and right thru the after-party, viewers will get an immersive look into their favorite acts touring today, AXS TV will span the globe to bring viewers the best live entertainment events.</p>

<p>AEG is very pleased to be involved with Mark Cuban and HDNet and equally as proud to have partners like Ryan Seacrest Media and CAA," said Timothy J. Leiweke , President &amp; CEO, AEG.  "The live business continues to grow and expand and the ability to give fans the opportunity to experience live in a different way is something we have been looking for a way to do for years."</p>

<p>"As part of a point of emanation for content, we are excited to provide the resources of L.A. LIVE, the O2 and our London campus among the many assets we are bringing into the partnership.  In addition, we are excited that AXS TV will have a host studio at L.A. LIVE.  This is a natural tie-in for our ticketing platform, axs Ticketing, as well as our facilities around the world.</p>

<p>This is a platform for all agents, all promoters and all managers.  It is not the creation of an AEG channel.  It is about the fans and bringing the live experience to them," Leiweke added.</p>

<p>Representatives of the joint venture also announced that DISH, the nation's third largest pay-TV provider, has agreed to expand its carriage of the rebranded HDNet by offering it in the America's Top 120 programming package, bringing millions more DISH customers access to the rebranded channel and resulting in AXS TV reaching well over 35 million North American households.</p>

<p>In addition to the increased distribution of the re-branded network, DISH will begin offering a large selection of AXS-branded Video On Demand concerts starting on March 15, 2012.  Capitalizing on AEG's unrivaled presence in the promotion of live events and nationwide network of clubs, DISH will work with AEG to offer unique ticketing opportunities for DISH viewers of AXS TV. Amenities include special ticketing opportunities, premium seating, private viewing areas and food &amp; beverage specials. Starting August 1, 2012, DISH and AXS TV will launch AXS Headliner Club, an online audition site providing local bands and musicians the opportunity to audition to perform at an AEG-affiliated club or theater.</p>

<p>"AXS TV brings DISH subscribers a premier TV destination for concert-goers to watch the most popular concert acts and provide opportunities for unique ticket sales at venues near them," said Joe Clayton, CEO, DISH. "The new DISH is all about 'more music, more movies, and more magic.'  So, we're pleased to provide a majority of our subscribers front row seats to this innovative channel."</p>

<p>AXS TV will continue to be available on HDNet's existing distributors including DIRECTV, Comcast, Verizon, AT&T, Charter, NCTC-member systems, Suddenlink and Shaw (in Canada).</p>

<p>Ryan Seacrest Media holds an ownership stake in AXS TV, and will provide access to multi-media platforms, and celebrity and brand relationships, as well as assist in the corporate brand development for AXS TV. As part of the deal, Ryan Seacrest Productions, an independent entertainment production company, will also develop and produce programming for AXS TV.</p>

<p>"Over the last year, we've been diligently working with our partners AEG and CAA to strategize and explore how we could deliver quality live entertainment and lifestyle programming to a large audience. HDNet is the perfect partner to help us translate our vision into a reality, giving us an instant reach into 35 million homes when we rebrand the channel and launch AXS TV," said Ryan Seacrest .  "Mark Cuban and Tim Leiweke bring a tremendous amount of entrepreneurial experience and spirit to this venture, and I'm confident our collective collaboration will make AXS a success," he added.</p>

<p>CAA will access its extensive experience, expertise, and relationships in entertainment to help create valuable content relationships for the network and provide strategic advisory services in the areas of corporate development, marketing, technology, and brand integration and sponsorship, among others. Seacrest is represented by CAA.</p>

<p>"We have worked closely with AEG and Ryan Seacrest to conceptualize a new network that could benefit from our combined resources and create a new distribution opportunity for artists and content creators and their high-quality entertainment and lifestyle programming," said David O'Connor, Managing Partner, CAA.  "Mark Cuban and his HDNet make a terrific partner for this venture, and we look forward to joining forces to help build AXS into a premier cable destination for entertainment enthusiasts."</p>

<p>Financial terms of the new venture were not disclosed.</p>

<p><br />
<strong>ABOUT HDNet</strong></p>

<p>HDNet (<a target="_blank" href="http://www.hd.net/">www.hd.net</a>) is the independent network with unique and thought-provoking content that appeals to men of all ages and is delivered in true high definition.</p>

<p>HDNet is the exclusive, high definition home for innovative, original programming, including the network's Emmy Award winning HD news feature programs, "HDNet World Report," and "Dan Rather Reports," featuring legendary journalist Dan Rather. HDNet is also the exclusive high definition home to critically acclaimed and award winning documentaries.</p>

<p>HDNet is your home for MMA, featuring the best of Mixed Martial Arts with its Friday night series, "HDNet Fights" (<a target="_blank" href="http://www.hdnetfights.com/">www.hdnetfights.com</a>). HDNet's "Inside MMA" is the hottest Mixed Martial Arts program on television, giving fans their weekly fix for everything MMA.</p>

<p>HDNet also delivers the world's largest and most diverse concert line-up through the HDNet Concert Series. The HDNet Concert Series features leading artists and bands including Paul McCartney, Mariah Carey, John Mayer, Nickelback and more. HDNet also distributes HDNet Movies (<a target="_blank" href="http://www.hdnetmovies.com/">www.hdnetmovies.com</a>) featuring exclusive Sneak Previews of new movies before they hit theaters. The HDNet Movies Sneak Preview series features top Hollywood stars in critically-acclaimed performances including Gwyneth Paltrow, Joaquin Phoenix, Demi Moore, Michael Caine, Tom Hanks, Vera Farmiga, Parker Posey, Brian Cox, Matthew Broderick, Kirsten Dunst, Ryan Gosling, John Malkovich, Emily Blunt, Robin Williams, Charlize Theron and Kim Basinger.</p>

<p>In addition to being the exclusive home of Sneak Previews, HDNet Movies viewers enjoy the best films from the classics of the 1950s-1970s, to favorite films from the 1980s and 1990s, to recently released theatrical films. During the daytime, HDNet Movies kidScene is a special, daily block of kid-friendly programming, running from 6:00 a.m. to 3:00 p.m. ET, dedicated to entertaining and enlightening kids from the ages of 5 to 10 years old without commercial interruption. From animation to adventure, HDNet Movies kidScene brings you all the best movies with one thing in mind: KIDS.</p>

<p>Launched in 2001 by Mark Cuban and General Manager Philip Garvin, the HDNet networks are available in the U.S. via AT&T U-verse, Charter, Comcast, DIRECTV, DISH Network, Insight, Suddenlink and Verizon FiOS and in Canada via Access Communications, Cogeco, Shaw Cable and Shaw Direct.</p>

<p><br />
<strong>About AEG:</strong></p>

<p>AEG is one of the leading sports and entertainment presenters in the world. AEG, a wholly owned subsidiary of the Anschutz Company, owns or is affiliated with a collection of companies including over 100 of the world's preeminent facilities such as STAPLES Center (Los Angeles, CA), The Home Depot Center (Carson, CA), Best Buy Theater (Times Square, New York), Sprint Center, (Kansas City), Rose Garden Arena (Portland, OR), Target Center (Minneapolis, MN), Mercedes-Benz Arena (Shanghai, China), MasterCard Center (Beijing, China), O2 World Hamburg, Allphones Arena (Sydney, Australia), Ericsson Globe arena (Stockholm, Sweden), O2 World arena (Berlin, Germany) and The O2 arena and entertainment district (London, England) which are all part of the portfolio of AEG Facilities . Developed by AEG, L.A. LIVE is a 4 million square foot / $2.5 billion downtown Los Angeles sports, residential &amp; entertainment district featuring Nokia Theatre L.A. LIVE and Club Nokia, a 54-story, 1001-room convention "headquarters" destination along with entertainment, restaurant and office space that "officially" opened in 2010.In addition to overseeing privately held management shares of the Los Angeles Lakers (NBA), assets of AEG Sports include franchises and properties such as the Los Angeles Kings (NHL), Los Angeles Galaxy and Houston Dynamo (MLS), two hockey franchises in Europe, the Amgen Tour of California cycling race and Bay to Breakers foot race. AEG Live , the company's live-entertainment division, is the world's second largest concert promotion and touring companies and is comprised of touring, festival, exhibition, broadcast, merchandise and special event divisions with fifteen regional offices. AEG Global Partnerships , a division responsible for worldwide sales and servicing of sponsorships naming rights and other strategic partnerships and AEG Merchandising , a multi-faceted merchandising company are also core business units of AEG. In 2010, AEG launched its AEG 1EARTH environmental program with the announcement of 2020 environmental goals and the release of the industry's first sustainability report while in 2011, AEG introduced axs Ticketing , the first phase of its new entertainment platform serving as the company's primary consumer brand which will also feature a mobile service as well as a video content service now in development. For additional information, visit <a target="_blank" href="http://www.aegworldwide.com/">www.aegworldwide.com</a>.</p>

<p><br />
<strong>ABOUT RYAN SEACREST MEDIA/RYAN SEACREST PRODUCTIONS (RSP)</strong></p>

<p>Ryan Seacrest Media is an investment holding company. Ryan Seacrest holds preeminent positions in broadcast television, nationally syndicated radio, local radio and cable as both a producer and on-air host. He is celebrated internationally as host of the top-rated primetime talent showcase "American Idol," and hosts and produces E! News and its red carpet awards show coverage. He also serves as an executive producer and co-hosts "Dick Clark's New Year's Rockin' Eve with Ryan Seacrest." On radio, Seacrest is host of "On Air with Ryan Seacrest," his market-topping #1 nationally syndicated LA morning drive-time radio show for Clear Channel's 102.7 KIIS-FM, as well as a nationally-syndicated Top 40 radio show. In 2006, Seacrest launched Ryan Seacrest Productions (RSP), which has since become a television production powerhouse. RSP produces the hit series "Keeping Up with the Kardashians," the highest-rated show on the E! network and the spin-offs "Khloe and Lamar," "Kourtney and Khloe Take New York," "Kourtney and Khloe Take Miami," and "Kourtney and Kim Take New York." RSP also produced the Emmy Award-winning ABC reality series "Jamie Oliver's Food Revolution." RSP is currently producing two new reality series including "Melissa and Tye: A New Reality," for CMT and "Shah's of Sunset," for Bravo.</p>

<p><br />
<strong>ABOUT CREATIVE ARTISTS AGENCY (CAA)</strong></p>

<p>Creative Artists Agency (CAA) is the world's leading entertainment and sports agency, representing many of the most successful professionals working in film, television, music, video games, theatre, fashion, and the Internet, and provides a range of strategic marketing and consulting services to corporate clients.CAA is also a leader in sports, representing more than 700 of the world's top athletes in football, baseball, basketball, hockey, soccer, tennis, and golf, and works in the areas of broadcast rights, corporate marketing initiatives, licensing, and sports properties for sales/sponsorship opportunities.In addition, The Intelligence Group, a market research and trend forecasting company, is a division of CAA.</p>

<p><br />
<strong>ABOUT DISH NETWORK</strong></p>

<p>DISH Network Corporation (NASDAQ: DISH), through its subsidiary DISH Network L.L.C., provides approximately 13.945 million satellite TV customers, as of Sept. 30, 2011, with the highest quality programming and technology with the most choices at the best value, including HD Free for Life. Subscribers enjoy the largest high definition line-up with more than 200 national HD channels, the most international channels, and award-winning HD and DVR technology. DISH Network's subsidiary, Blockbuster L.L.C., delivers family entertainment to millions of customers around the world. DISH Network Corporation is a Fortune 200 company. Visit <a target="_blank" href="http://www.dish.com/">www.dish.com</a>.</p>

<p>SOURCE HDNet</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 18, 2012  8:02 PM</b>
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
			<?=getComments(4643)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4643)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/hdnet-aeg-ryan-seacrest-media-and-caa-establish-joint-venture-to-rebrand-hdnet-as-axs-tv.php" type="text/javascript" charset="utf-8"></script>
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