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
		AND e.entry_id = 906";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 906 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 906 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 906";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 906";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CES 2008: New HDTV Products and Technology Overview" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CES 2008: New HDTV Products and Technology Overview" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="CES 2008: New HDTV Products and Technology Overview" />
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
	<title>HDTV Magazine - CES 2008: New HDTV Products and Technology Overview</title>
	<meta name="keywords" content="blu ray, bonus view, hdmi cec, lcd models, announced new, new, announced, lineup, series, year, blu, ray, lcd, models, technology, plasma, hdmi, ces, dvd, sets, products, sony, wireless, feature, bonus" />
	<meta name="description" content="For those of you who have been receiving the &lt;a href=&quot;http://www.hdtvmagazine.com/news/bulletins.php&quot; target=&quot;_blank&quot;&gt;bulletins&lt;/a&gt; from CES over the past two weeks, you will recognize many of the topics below. I've picked some of the highlights and popular themes from this year's show and included a brief comment or two on each. This is not a comprehensive overview, but rather a &quot;highlight reel&quot; from the event. Here is a list of topics covered in this article. These are in no particular order:
&lt;ul&gt;&lt;li&gt;Warner Brothers Chooses to go Blu-ray Exclusive&lt;/li&gt;&lt;li&gt;Blu-ray Getting &quot;Bonus View&quot;&lt;/li&gt;&lt;li&gt;LaserTV&lt;/li&gt;&lt;li&gt;The Shift to Wireless HD&lt;/li&gt;&lt;li&gt;The Rise of Video Download Services&lt;/li&gt;&lt;li&gt;Netflix and LG to Partner on Streaming Video&lt;/li&gt;&lt;li&gt;Dish Network Commits to 100 HD Channels in 2008&lt;/li&gt;&lt;li&gt;Slingbox Pro-HD&lt;li&gt;Microsoft Mediaroom Getting Traction&lt;/li&gt;&lt;li&gt;JVC Lineup&lt;/li&gt;&lt;li&gt;LG Lineup&lt;/li&gt;&lt;li&gt;Panasonic Lineup&lt;/li&gt;&lt;li&gt;Pioneer Lineup&lt;/li&gt;&lt;li&gt;Sharp Lineup&lt;/li&gt;&lt;li&gt;Sony Lineup&lt;/li&gt;&lt;li&gt;Toshiba Lineup&lt;/li&gt;&lt;/ul&gt;" />
	<meta name="title" content="CES 2008: New HDTV Products and Technology Overview" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="CES 2008: New HDTV Products and Technology Overview" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="For those of you who have been receiving the &lt;a href=&quot;http://www.hdtvmagazine.com/news/bulletins.php&quot; target=&quot;_blank&quot;&gt;bulletins&lt;/a&gt; from CES over the past two weeks, you will recognize many of the topics below. I've picked some of the highlights and popular themes from this year's show and included a brief comment or two on each. This is not a comprehensive overview, but rather a &quot;highlight reel&quot; from the event. Here is a list of topics covered in this article. These are in no particular order:
&lt;ul&gt;&lt;li&gt;Warner Brothers Chooses to go Blu-ray Exclusive&lt;/li&gt;&lt;li&gt;Blu-ray Getting &quot;Bonus View&quot;&lt;/li&gt;&lt;li&gt;LaserTV&lt;/li&gt;&lt;li&gt;The Shift to Wireless HD&lt;/li&gt;&lt;li&gt;The Rise of Video Download Services&lt;/li&gt;&lt;li&gt;Netflix and LG to Partner on Streaming Video&lt;/li&gt;&lt;li&gt;Dish Network Commits to 100 HD Channels in 2008&lt;/li&gt;&lt;li&gt;Slingbox Pro-HD&lt;li&gt;Microsoft Mediaroom Getting Traction&lt;/li&gt;&lt;li&gt;JVC Lineup&lt;/li&gt;&lt;li&gt;LG Lineup&lt;/li&gt;&lt;li&gt;Panasonic Lineup&lt;/li&gt;&lt;li&gt;Pioneer Lineup&lt;/li&gt;&lt;li&gt;Sharp Lineup&lt;/li&gt;&lt;li&gt;Sony Lineup&lt;/li&gt;&lt;li&gt;Toshiba Lineup&lt;/li&gt;&lt;/ul&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=906', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php">CES 2008: New HDTV Products and Technology Overview</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 17, 2008</b>
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
				<p>There was a fairly significant shift at this years CES in that most of the major manufacturers were beginning to focus on the design or art of the HDTV as much as the technology. Thin is in, as is glossy, curved corners, invisible/detachable speakers, splashes of color, etc. In all, this years sets are gorgeous, both on and off. Also new this year is a focus on products and services &quot;around&quot; the TV. Wireless options are being explored, additional HD services are launching, and peripherals capable of handling HD are becoming more and more common.</p>  <p>For those of you who have been receiving the <a href="http://www.hdtvmagazine.com/news/bulletins.php" target="_blank">bulletins</a> from CES over the past two weeks, you will recognize many of the topics below. I've picked some of the highlights and popular themes from this year's show and included a brief comment or two on each. This is not a comprehensive overview, but rather a &quot;highlight reel&quot; from the event. Here is a list of topics covered in this article. These are in no particular order: </p>  <ul>   <li>Warner Brothers Chooses to go Blu-ray Exclusive</li>    <li>Blu-ray Getting &quot;Bonus View&quot;</li>    <li>LaserTV</li>    <li>The Shift to Wireless HD</li>    <li>The Rise of Video Download Services</li>    <li>Netflix and LG to Partner on Streaming Video</li>    <li>Dish Network Commits to 100 HD Channels in 2008</li>    <li>Slingbox Pro-HD</li>    <li>Microsoft Mediaroom Getting Traction</li>    <li>JVC Lineup</li>    <li>LG Lineup</li>    <li>Panasonic Lineup</li>    <li>Philips Lineup</li>    <li>Pioneer Lineup</li>    <li>Sharp Lineup</li>    <li>Sony Lineup</li>    <li>Toshiba Lineup</li> </ul>  <h2>Warner Brothers Chooses to go Blu-ray Exclusive</h2>  <p>This announcement actually came out on Friday, but I wanted to include it here because there was a lot of talk about it at the show, and on various panels and in a few press conferences. This was apparently a complete surprise to the HD DVD group, who ended up canceling all one-on-one interviews as well as the HD DVD press event at the show so that they could &quot;re-group&quot; with the HD DVD partner companies and assess the situation. </p>  <p>The reason given for Warner Brothers announcement is that it is responding to consumers demand for a single format, and that their decision was for the consumers ultimate benefit. Essentially, Blu-ray was outselling HD DVD for most of the year by a factor of 2-to-1, and the general consensus among the studios was that there was a large segment of the population not buying <strong>any</strong> movies (HD or Standard DVD) as they were waiting to see which would be the &quot;next&quot; format. So in order to spur this non-consuming public to start consuming, WB chose Blu-ray in hopes that HD DVD would then go away and the would-be-consumer's conscience would be clear to make those purchases they've been sitting on for the past year. Only time will tell if that strategy pays off.</p>  <h2>Blu-ray Getting &quot;Bonus View&quot;</h2>  <p>Blu-ray players are now starting to come out with features to compete better with the HD DVD players. <strong>Bonus View</strong> is what is otherwise known as Profile 1.1. The Blu-ray association said they wanted to have a better marketing name for profile 1.1, so they are calling their picture-in-picture functionality Bonus View to make it easier for consumers to identify players and titles with this feature. </p>  <p>Philips have announced their first &quot;Bonus View&quot; player, the BDP7200, at the show. Samsung has also announced their second generation HD Duo dual format HD DVD/Blu-ray player which will be &quot;Bonus View&quot; enabled. The Samsung player is expected to ship in May for $599 suggested retail.</p>  <p><strong>BD Live</strong> is what is also known as Profile 2.0, and is the evolution of the Blu-ray standard that requires ethernet connections on all players. Along with the PS3, the Philips unit also has a built-in ethernet port and will be upgradeable to &quot;BD Live&quot; via firmware when the time comes.</p>  <h2>LaserTV</h2>  <p>Mitsubishi announced this year at CES a new class of televisions: LaserTV. The advantages of LaserTV are in color reproduction and power consumption. From their press release: &quot;Today's HDTVs display less than 40 percent of the color spectrum that the eye can see. Now, for the first time ever, laser produces twice the color. Laser beams provide the widest range of rich, complex colors, along with the most clarity and depth of field.&quot; LaserTV also consumes less power that comparably sized flat panel displays and can be wall-mounted just like plasma and LCD sets. Mitsubishi expects to ship LaserTVs to retailers later this year.</p>  <h2>The Shift to Wireless HD</h2>  <p>Several TV manufacturers have joined together to form the WirelessHD Consortium (WiHD), who's purpose appears to be a replacement for HDMI. WirelessHD delivers copy protected, uncompressed, no-loss, full HD content. The companies that make up this consortium are LG, Panasonic, NEC, Sibeam, Samsung, Sony, Toshiba and Intel. WiHD is able to transmit at data rates of 4Gbit/s at 33 feet which would sustain a full 1080p signal as well as multiple signals at lower resolutions. It can also support DTS HD Master Audio and Dolby TrueHD, making it quite a promising candidate for replacing HDMI someday. They expect the program to be finalized in June or July, so you should expect products this Christmas emblazoned with the WiHD logo.</p>  <h2>The Rise of Video Download Services</h2>  <p>I have an article coming up soon that will outline these in greater detail, but in short there are several new big players in the HD movie download space. VUDU, which was unveiled in September of last year, announced at CES that they will increase their library of HD 1080p content to over 70 titles by the end of January. A newcomer to the download space is XStreamHD. I have an article coming up about them soon too, but in short they plan to offer (starting in October) full HD movies at up to 100Mbit/s and are capable of handling 7.1 DTS HD Master Audio. Lastly, and most recently, Apple announced this week at Macworld that they have signed deals with all the major studios to rent and buy movies via iTunes and the Apple TV. The jury is still out on whether these will be available in 720p or 1080i.</p>  <h2>Netflix and LG to Partner on Streaming Video</h2>  <p>Not much to report on this yet. A deal was announced where LG will be building a box that will supposedly interface with Netflix's streaming movie service. The planned ship date for this box is June/July, but no pricing information is available at this time. It is possible that it may be built in to LG TVs down the road, but there are no current plans to do so. Netflix also announced recently that all but their lowest rental plan now come with unlimited streaming of movies via their &quot;Watch Instantly&quot; service. It remains to be seen what the quality will be like on this content.</p>  <h2>Dish Network Commits to 100 HD Channels in 2008</h2>  <p>Dish Network announced that they will be expanding their lineup of HD channels from 76 to 100 by the end of 2008. To accomplish this, they will be launching three new satellites to handle the load. In addition to the increase in national channels, Dish is making headway into providing local HD channels via satellite by announcing 11 new markets to receive local HD coverage.</p>  <h2>Slingbox Pro-HD</h2>  <p>Sling Media/Dish have announced a new product to their lineup of Slingbox's: The Slingbox Pro-HD. Prior to this, they have had products that would accept an HD signal, but it was still transmitted in SD. With the Pro-HD,&#160; you can now &quot;sling&quot; HD content as well.</p>  <h2>Microsoft Mediaroom Getting Traction</h2>  <p>Microsoft announced a deal with British Telecom (BT) for IPTV delivery via their Mediaroom platform in the UK. They have also announced that they are working with a number of partners here in the US and North America, but have not officially announced any signed partnerships. The Mediaroom platform runs on top of IPTV service from your provider and can reside either on a dedicated set top box or on an Xbox 360 console, should you have one.</p>  <h2>JVC Lineup</h2>  <p>JVC announced 10 new LCD models across 3 model series (P, SL and J), including a new size: 52&quot;. The P series will feature an iPod dock and a USB connection for photo viewing. All but one of these new sets are 1080p sets.</p>  <h2>LG Lineup</h2>  <p>LG has announced 24 new LCD models and 8 new plasma models. 17 of the 24 LCD models will be Full HD and 6 of the 8 plasma models will have 1080p. On their upper end sets (the LG71 series), they feature 120Hz technology which LG calls TruMotion, 802.11n wireless connectivity, and they are support HDMI-CEC, which LG is calling SimpleLink. Step down to the LG70 series and get all the same features except wireless connectivity. There are four other series announced (LG60, LG50, LG40 and LG30) that successively offer less functionality as you go down the line.</p>  <p>Their plasma series are the PG20, PG30, PG60 and PG70. The PG70 series is &quot;wireless ready&quot;, able to support wireless connectivity in the future when an add-on module is available. The PG30, 60 and 70 series are all 1080p sets while the PG20 entry level series is 720p.</p>  <h2>Panasonic Lineup</h2>  <p>Panasonic announced a new line of Full HD 1080p plasmas, but the crowd at the booth was much larger around their new 150&quot; Plasma. In total there are 10 new models, all featuring increased contrast ratio, HDMI-CEC compliance (Viera Link) and longer lasting panels. Their upper end also features THX Certification and 24p input.</p>  <h2>Philips Lineup</h2>  <p>One of the several companies focused on design this year at CES was Philips. They feature a minimalist design and curved bezel resulting in a less &quot;boxy&quot; TV. New this year are their 7400 and 7600 LCD series. Both series are available in 42&quot;, 47&quot; and 52&quot; sizes and feature 120Hz frame rate which they're calling &quot;ClearLCD&quot;. They also both feature four HDMI 1.3 inputs and HDMI-CEC. The 7600 series adds Ambilight back-lighting and features better sound options.</p>  <h2>Pioneer Lineup</h2>  <p>Although there were no new Pioneer Kuro plasma TVs announced at CES, they did debut some new Kuro technology that appears to take contrast ratios to the extreme. This new plasma technology is being marketed as the first plasma that is absolute black, with no measurable light emitting from the screen. No word on when this will be commercially available.</p>  <h2>Sharp Lineup</h2>  <p>Sharp announced 14 new LCD sets across 4 new lines, two new DLP projectors, and a second generation Blu-ray player. Among the new LCDs is a new &quot;Special edition&quot; (SE94) series, featuring full HD, high contrast, HDMI 1.3 and 120Hz frame rate which they're calling &quot;Fine Motion Advanced&quot;. Response time for the special edition series is less than 4ms and the viewing angle is 176 degrees. These SE94 models also include a built-in ethernet port for internet access using a built-in browser.</p>  <h2>Sony Lineup</h2>  <p>Like Toshiba, Sony had also announced previously that they were exiting the rear projection market. Their lineup this year consisted of mainly Bravia LCD models, all of which are HDMI-CEC (Bravia Sync) capable. Sony is equipping several of these new models with their 120Hz technology: MotionFlow. New for this year is a technology Sony calls DMeX (Digital Media eXtender). This is a plug-in technology for sets so equipped that will allow modules to be added on later as additional technologies come out. </p>  <p>The one notable exception to their LCD exclusivity is the first OLED (Organic Light Emitting Diode) TV for the US market. Their entry into the OLED market is an 11&quot; display measuring a mere 3mm in thickness. It has a resolution of 960x540 and boasts of a contrast ratio of 1,000,000:1. The set is available for purchase through Sony Style stores for about $2,500 USD.</p>  <h2>Toshiba Lineup</h2>  <p>In addition to the new TVs announced, Toshiba also announced that they will now be introducing new lines twice a year. Products announced at CES will be shipping in February/March, and products announced at CEDIA will ship in August/September. Since Toshiba announced last year that they will be dropping their plasma and rear projection products, all TVs announced this year were in their LCD line. Like most other LCD manufacturers, they also are incorporating 120Hz technology they are calling ClearFrame. With their ClearFrame feature they are offering both &quot;interpolated&quot; and 5:5 pull-down modes.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 17, 2008 10:28 PM</b>
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
			<?=getComments(906)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 906)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/01/ces-2008-new-hdtv-products-and-technology-overview.php" type="text/javascript" charset="utf-8"></script>
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