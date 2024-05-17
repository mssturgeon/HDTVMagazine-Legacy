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
		AND e.entry_id = 1613";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1613 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1613 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1613";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/vizio-announces-new-and-exciting-connected-hdtv-platform-with-wireless-connectivity-80211n.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1613";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Announces New and Exciting \'Connected HDTV\' Platform With Wireless Connectivity (802.11n)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Announces New and Exciting \'Connected HDTV\' Platform With Wireless Connectivity (802.11n)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Announces New and Exciting \'Connected HDTV\' Platform With Wireless Connectivity (802.11n)" />
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
	<title>HDTV Magazine - VIZIO Announces New and Exciting 'Connected HDTV' Platform With Wireless Connectivity (802.11n)</title>
	<meta name="keywords" content="connected hdtv, hdtv platform, adobe flash, living room, social networking, content, platform, hdtv, connected, yahoo, television, new, video, entertainment, demand, internet, service, access, adobe, music, services, information, users, top, movies" />
	<meta name="description" content="VIZIO, America's TV and Consumer Electronics Company, announced today the unveiling of its &quot;Connected HDTV&quot; Platform, which enhances the HDTV viewing experience by bringing personalized entertainment, information and social networking content into the viewer's living room. With unprecedented choice and control of web-based and local content from a wide range of popular content providers and services, including on-demand movies and music, news, weather, sports, gaming and social networking services, this platform will be integrated as a key feature into VIZIO televisions shipping..." />
	<meta name="title" content="VIZIO Announces New and Exciting 'Connected HDTV' Platform With Wireless Connectivity (802.11n)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Announces New and Exciting 'Connected HDTV' Platform With Wireless Connectivity (802.11n)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/vizio-announces-new-and-exciting-connected-hdtv-platform-with-wireless-connectivity-80211n.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="VIZIO, America's TV and Consumer Electronics Company, announced today the unveiling of its &quot;Connected HDTV&quot; Platform, which enhances the HDTV viewing experience by bringing personalized entertainment, information and social networking content into the viewer's living room. With unprecedented choice and control of web-based and local content from a wide range of popular content providers and services, including on-demand movies and music, news, weather, sports, gaming and social networking services, this platform will be integrated as a key feature into VIZIO televisions shipping..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1613', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/vizio-announces-new-and-exciting-connected-hdtv-platform-with-wireless-connectivity-80211n.php">VIZIO Announces New and Exciting 'Connected HDTV' Platform With Wireless Connectivity (802.11n)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=342&category=Wireless HDMI/HDTV">Wireless HDMI/HDTV</a></b>
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
				<p class="prtitle">VIZIO Announces New and Exciting 'Connected HDTV' Platform With Wireless Connectivity (802.11n)</p>

<center><i>- VIZIO's "Connected HDTV" Platform gives HDTV viewers unprecedented choice and access to Internet-based content streamed directly to their VIZIO HDTV without the inconvenience and expense of a PC or set top box.

<p>- VIZIO's "Connected HDTV" Platform provides a fully customizable and dynamic entertainment experience with built-in wireless (802.11n) to access on-demand movies, music, news, weather, sports, gaming and social networking services.</p>

<p>- Content and service providers are rapidly expanding and now include Accedo Broadband, Adobe(R), Amazon(R), Blockbuster(R), Flickr(R), Netflix(R), Pandora, Rhapsody(R) and Yahoo!(R)</i></center><br /><br />
<br /></p>

<p><br />
<B>LAS VEGAS and IRVINE, Calif., Jan. 7 /PRNewswire/ -- (CES) </B>-- VIZIO, America's TV and Consumer Electronics Company, announced today the unveiling of its "Connected HDTV" Platform, which enhances the HDTV viewing experience by bringing personalized entertainment, information and social networking content into the viewer's living room. With unprecedented choice and control of web-based and local content from a wide range of popular content providers and services, including on-demand movies and music, news, weather, sports, gaming and social networking services, this platform will be integrated as a key feature into VIZIO televisions shipping to retailers nationwide this Fall 2009 in time for the busy holiday season.</p>

<p>VIZIO's "Connected HDTV" Platform is designed to compliment TV viewing. At a single touch of the VIZIO customized remote control, viewers can access their favorite Internet content from the easy to navigate on screen display, without interrupting the TV program they are viewing. With built-in wired and wireless (802.11n) networking, VIZIO televisions with the "Connected HDTV" Platform will provide true plug-and-play connectivity right out of the box.</p>

<p>"In our recent report on the future of the television, Evolution of the Television - From Passive Consumption Device to the Entertainment Center of the Home, we noted, 'The television has evolved from a passive consumption device that received limited over-the-air programming, to the entertainment center of the home where multitudes of activities come together,'" stated Nathan Safran, Analyst at Forrester Research, Inc. "While broadband set top boxes that deliver content into the home are interesting because they establish a new way of delivering content to the television, they have had limited appeal because of the up-front equipment outlay and because content has been restricted to a single partner. However, when streaming media capability is integrated directly into the network enabled television, it becomes a game changing proposition, and is the next step in the evolution of the television, as it eliminates the equipment outlay for the set top box and gives the viewer a broad range of content to choose from."</p>

<p>"As consumer demand for content and information grows exponentially, we want to address what our customers are increasingly looking for - the ability to enjoy their favorite content and services from the comfort of their living room easy chair," said Laynie Newsome, VP Sales and Marketing Communication and Co-Founder of VIZIO.</p>

<p>Paul Gagnon, Director of North America TV Market Research at DisplaySearch also noted, "In a hyper-connected world, TVs will start integrating more completely with the various sources of available content. According to a recent study DisplaySearch conducted, 22% of respondents wanted access to web-based information and more than 31% already watch movies from on-demand services."</p>

<p>Along with the expectation of anywhere, anytime access, consumers crave variety and the freedom to choose from a wide set of entertainment and information sources. "Our 'Connected HDTV' Platform is a key element of VIZIO's Consumer Ecosystem, which places the VIZIO HDTV as a focal point, allowing viewers to seamlessly connect to information and entertainment sources that they want, when they want them," said Matthew McRae, VIZIO VP. "The platform also provides an unprecedented opportunity for Internet-based service providers to develop and deploy innovative applications to a large consumer audience."</p>

<p>VIZIO's "Connected HDTV" Platform will incorporate the Yahoo! Widget Engine, which provides an open platform enabling a new class of Internet services, called TV Widgets, to thrive in the TV environment. The platform will also include support for Adobe Flash LiteTM, which will enable developers to deliver rich applications, content, and video over the Web to the television itself.</p>

<p>Leading popular content and service providers are working with VIZIO's "Connected HDTV" Platform to bring a very wide variety of entertainment, information, social networking and other applications to consumers, including:</p>

<p>Accedo Broadband (www.accedobroadband.com) -- Accedo's popular casual gaming service, Funspot(R), allows viewers to play a variety of fun games from the comfort of their favorite armchair. Games include Texas Hold 'Em Poker, Sudoku, and QuizzMaster.</p>

<p>Adobe (www.adobe.com/flashplatform) -- Adobe Flash(R) Player and Adobe Flash Lite are on over 98 percent of connected computers and more than 800 million devices, and deliver more than 80 percent of Web videos worldwide. Both players are part of the Adobe Flash Platform and ensure that content and applications are presented consistently in the format users want.</p>

<p>Amazon Video on Demand (www.amazon.com/vod) -- Amazon Video On Demand plans to make its massive selection of over 40,000 movies and TV shows available to users of VIZIO's "Connected HDTV" Platform, providing users with the total on demand entertainment solution. Using the VIZIO television's built-in Internet connection, Amazon Video On Demand plans to give customers the ability to browse, shop and instantly watch everything from hit new release movies to classic movie favorites and individual TV episodes and seasons.</p>

<p>Blockbuster OnDemand (www.blockbuster.com) -- One of the nation's leading digital movie services, BLOCKBUSTER ONDEMAND makes thousands of titles, including the newest releases, available for rental and purchase. The service is available on an a la carte basis, no subscription necessary, and includes content from the major Hollywood studios as well as more than 30 other studios, television-content distributors and foreign and independent content providers.</p>

<p>Flickr (www.flickr.com) -- Flickr, a Yahoo! company, is one of the world's leading online photo and video sharing communities where people explore, find and manage pictures and video clips of life's daily moments with friends, family and the world. The Flickr TV Widget helps users make their favorite photo content available to the people who matter to them. Consumers can view, share and organize photos with friends and family or check out what interesting new photos were recently posted by others -- all in the social setting of the living room on their large flat panel television.</p>

<p>Netflix (www.netflix.com) -- Since launching its streaming functionality in January 2007, Netflix has grown its catalog to more than 12,000 choices of movies and TV episodes to watch instantly on the TV via a number of Netflix ready devices. Netflix members pay as little as $8.99 per month for unlimited instant streaming and unlimited DVDs from a catalog of more than 100,000 DVD titles in more than 200 genres.</p>

<p>Pandora (www.pandora.com) -- Based on the Music Genome Project begun in 2000, Pandora's personalized Internet radio and music discovery service delivers personalized playlists built from a database with over 550,000 songs analyzed by more than 30 trained musicians and assessed against nearly 400 distinct musical attributes.</p>

<p>Rhapsody (www.rhapsody.com) -- Rhapsody's award-winning digital music service gives consumers access to more than six million songs on their PC, on their portable device and in the home. Now, VIZIO "Connected HDTV"-enabled users will have living room access as well to Rhapsody's extensive music catalog, professionally programmed music channels, personalized recommendations and thousands of themed playlists.</p>

<p>Yahoo! - Yahoo! provides the best of the Internet in perfect harmony with the simplicity and reliability of the television. The innovative Yahoo! Widget Engine, which will help drive VIZIO's "Connected HDTV" Platform, will offer VIZIO customers an enhanced viewing experience by bringing to market a new class of interactive applications called TV Widgets. Initial Yahoo! TV Widgets available will include Yahoo! Weather, Yahoo! News, and Yahoo! Finance, soon to be followed by Yahoo! Sports and Yahoo! Video. Yahoo! Video puts a wide range of news, sports, lifestyle, and entertainment video content in one place for users to enjoy on their TV via the Internet. Additional well-known content providers already developing TV Widgets for deployment include CBS, Showtime(R), TwitterTM, The New York Times(R) and MySpace(R).</p>

<p>By collaborating with leading content, service and technology partners to create a truly compelling and personalized user experience, VIZIO sees its new advanced platform as part of its continual evolution to deliver consumers more of what they love. "The quality of the platform partners that we are attracting validates our approach. Our partner list will continue to expand," stated Matthew McRae, VIZIO VP. "This platform offers innumerable opportunities given VIZIO's hefty HDTV market share and growth expectations for the future."</p>

<p>VIZIO will be conducting private demonstrations of their "Connected HDTV" Platform for key business partners, analysts and press during CES in the La Tache ballroom at the WYNN Las Vegas Resort. Interested parties should contact VIZIO at partners@vizio.com or call (949) 428-2525 ext. 2554. Reporters and analysts should speak with press contacts listed below.</p>

<p><br />
<B>About VIZIO</B></p>

<p>VIZIO, Inc. "Where Vision Meets Value," headquartered in Irvine, California, is America's HDTV and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead major categories in U.S. TV sales. VIZIO is committed to bringing feature-rich flat panel televisions to market at a value through practical innovation. VIZIO offers a broad range of award winning Plasma and LCD HDTVs including the new XVT series. VIZIO's products are found at Costco Wholesale, Sam's Club, Sears, Target, Walmart, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Good Housekeeping's Best Big-Screens, CNET's Top 10 Holiday Gifts, PC World's Best Buy among others. For more information, please call 888-VIZIOCE or visit on the web at</p>

<p>www.VIZIO.com.</p>

<p>The V, VIZIO, Where Vision Meets Value names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.<br />
Photo: http://www.newscom.com/cgi-bin/prnh/20060307/LATU014LOGO-b<br />
http://www.newscom.com/cgi-bin/prnh/20090107/LA56217<br />
http://photoarchive.ap.org/<br />
AP PhotoExpress Network: PRN9<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: VIZIO, Inc. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  7:54 AM</b>
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
			<?=getComments(1613)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1613)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/vizio-announces-new-and-exciting-connected-hdtv-platform-with-wireless-connectivity-80211n.php" type="text/javascript" charset="utf-8"></script>
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