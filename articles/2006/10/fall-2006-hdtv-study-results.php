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
		AND e.entry_id = 465";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 465 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 465 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 465";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 465";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Fall 2006 HDTV Study Results" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Fall 2006 HDTV Study Results" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Fall 2006 HDTV Study Results" />
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
	<title>HDTV Magazine - Fall 2006 HDTV Study Results</title>
	<meta name="keywords" content="next months, blu ray, intend purchase, dvd blu, home entertainment, respondents, hdtv, next, question, months, most, dvd, purchase, people, own, blu, ray, survey, intend, either, popular, surprise, home, important, programming" />
	<meta name="description" content="The results are in. From &lt;strong&gt;August 22nd, 2006&lt;/strong&gt; through &lt;strong&gt;October 1st, 2006&lt;/strong&gt;, HDTV Magazine conducted and sponsored The Fall 2006 HDTV Study. This article focuses on several key questions related to HDTV technology and how the results came out. The charts and data below reflect the preferences, buying habits, and general demographics of &lt;strong&gt;1281&lt;/strong&gt; study respondents. These respondents took the survey either as a subscriber to our services, or by navigating from a link featured on our home page. As such, the audience for this survey are primarily either owners of HDTVs or those in the immediate market (within 3 months of purchase) for an HDTV." />
	<meta name="title" content="Fall 2006 HDTV Study Results" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Fall 2006 HDTV Study Results" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The results are in. From &lt;strong&gt;August 22nd, 2006&lt;/strong&gt; through &lt;strong&gt;October 1st, 2006&lt;/strong&gt;, HDTV Magazine conducted and sponsored The Fall 2006 HDTV Study. This article focuses on several key questions related to HDTV technology and how the results came out. The charts and data below reflect the preferences, buying habits, and general demographics of &lt;strong&gt;1281&lt;/strong&gt; study respondents. These respondents took the survey either as a subscriber to our services, or by navigating from a link featured on our home page. As such, the audience for this survey are primarily either owners of HDTVs or those in the immediate market (within 3 months of purchase) for an HDTV." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=465', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php">Fall 2006 HDTV Study Results</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 27, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=20&category=General Interest">General Interest</a></b>
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
				The results are in. From <strong>August 22nd, 2006</strong> through <strong>October 1st, 2006</strong>, HDTV Magazine conducted and sponsored The Fall 2006 HDTV Study. This article focuses on several key questions related to HDTV technology and how the results came out. The charts and data below reflect the preferences, buying habits, and general demographics of <strong>1281</strong> study respondents. These respondents took the survey either as a subscriber to our services, or by navigating from a link featured on our home page. As such, the audience for this survey are primarily either owners of HDTVs or those in the immediate market (within 3 months of purchase) for an HDTV. If you would like to see specific combinations of this data, or if you have ideas for questions or topics we should cover on future studies, please <a href="/help/feedback.php">let us know</a>.

<div align="center"><div class="alertbox" style="vertical-align:middle"><img src="/images/logos/internet-podcasting_75x40.gif" alt="Podcast" align="left" style="border:1px solid #D5CA8B; padding:0">Tune in to hear us talk about the survey on <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/">The HDTV Podcast</a> with Ara Derderian and Braden Russell. [ <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/archive/2006/October272006.html">Permanent Link</a> ]<br clear="all" /></div></div>

Below is a list of the questions covered in this article:

<ul>
<li><a href="#1">How many HDTVs do you own?</a></li>
<li><a href="#2">What type(s) of HDTVs do you own?</a></li>
<li><a href="#3">What is the size of your largest HDTV?</a></li>
<li><a href="#4">What type(s) of content do you watch most?</a></li>
<li><a href="#5">How do you receive your HD programming?</a></li>
<li><a href="#6">Are you able to receive digital programming, either over-the-air or via your current cable/satellite provider?</a></li>
<li><a href="#7">Are you able to receive HDTV programming, either over-the-air or via your current cable/satellite provider?</a></li>
<li><a href="#8">HD DVD or Blu-ray?</a></li>
<li><a href="#9">In deciding between HD DVD and Blu-ray, what is the most important factor?</a></li>
<li><a href="#10">Which is more important to you?</a></li>
<li><a href="#11">As it relates to high definition, for which of the following areas do you have an interest?</a></li>
<li><a href="#12">Approximately how much do you have invested in your home entertainment system (components, cables, media, etc)? (US Dollars)</a></li>
<li><a href="#13">Which of the following home entertainment system components and services do you own and/or subscribe to?</a></li>
<li><a href="#14">Do you intend to purchase an HDTV within the next 6 months?</a></li>
<li><a href="#15">If you intend to purchase an HDTV within the next six months, which type of HDTV are you most likely to purchase?</a></li>
<li><a href="#16">Are you planning on buying a Sony Playstation 3 (PS3) within the next 6 months? (assuming they stick to the 11/17 release date)</a></li>
<li><a href="#17">Assuming neither technology pulls ahead, are you planning on purchasing a next-gen DVD player within the next 6 months?</a></li>
<li><a href="#18">Which other home entertainment components and services are you planning to purchase in the next 6 months?</a></li>
<li><a href="#19">Other Comments</a></li>
</ul>

<br />
<h2><a name="1">How many HDTVs do you own?</a></h2>
<div style="float:left"><object id="30925705" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30925705%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=200">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30925705%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>Even though the audience for this study are primarily HDTV owners, it is pleasing to see that more than one third (37%) of respondents indicated that they had more than one HDTV, and about one in eight (13%) indicated they had 3 or more. <b>(1247 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="2">What type(s) of HDTVs do you own?</a></h2>
<div style="float:left"><object id="30926348" width="400" height="200" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926348%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926348%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="200"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This was one of the surprises of the survey. I would have expected LCD and Plasma technologies to be much closer, but nearly twice as many people had LCD televisions as did Plasma. It was also very interesting to see that LCD was ahead of both rear-projection and direct-view CRT displays. One thing I would like to know: How did 7 people have SED displays? They're not even on the market yet. <b>(1138 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="3">What is the size of your largest HDTV?</a></h2>
<div style="float:left"><object id="30926420" width="400" height="200" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926420%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=200&chartWidth=450">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926420%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=200&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="200"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The choices for this questions probably could have been better arranged to more accurately isolate the popular sizes of televisions. As they are, they range from 25 to 70 inches, in 5 inch increments. The most popular sizes are in the 46" - 50" and the 30" - 35" range. <b>(1148 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="4">What type(s) of content do you watch most?</a></h2>
<div style="float:left"><object id="30928488" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928488%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928488%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>Surprise #2 on the survey: Network primetime television is watched more than Sports, which contradicts several recent surveys. Although to be fair, this is perhaps a seasonal question, and Sports may indeed be more popular at other times of the year ... we shall see.</p>
<p>This question will be expanded on future surveys. We thought we hit the highlights here, but nearly 10% selected "Other" and provided answers other than what we suggested. <b>(1110 respondents)</b><br clear="all" /></p><p>Some of the most popular "Other" answers included:
<ul>
<li>All of the above</li>
<li>Anything HD</li>
<li>Education</li>
<li>News</li>
<li>Science</li>
<li>and Xbox</li>
</ul></p>

<br />
<h2><a name="5">How do you receive your HD programming?</a></h2>
<div style="float:left"><object id="30928311" width="250" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928311%26width%3D250%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30928311%26width%3D250%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The only surprise with this question was how equally weighted all three options seemed to be. This question allowed multiple answers, and each option was selected by about 47% of respondents. <b>(1112 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="6">Are you able to receive digital programming, either over-the-air or via your current cable/satellite provider?</a></h2>
<div style="float:left"><object id="31136450" width="125" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136450%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=125">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136450%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>No surprise here, given that the audience was generally already familiar with HDTV. <b>(1111 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="7">Are you able to receive HDTV programming, either over-the-air or via your current cable/satellite provider?</a></h2>
<div style="float:left"><object id="31136460" width="125" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136460%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=125">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31136460%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>No surprise here either. <b>(1111 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="8">HD DVD or Blu-ray?</a></h2>
<div style="float:left"><object id="31218392" width="400" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218392%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionText&chartHeight=125&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218392%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionText&chartHeight=125&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The first of several next-gen DVD questions. HD DVD does seem to be favored by a small margin (~4%), but one should keep in mind the timing of this survey: When this survey was conducted, Blu-ray was having significant (Samsung) player problems and the picture quality of the existing titles was questionable. Having said that, the interesting part about this question is that the vast majority are content to wait until there is a clear winner, rather than enjoying another form of high definition while they can. And given the investment each is making in their respective formats, it is quite possible that IF there is ever a clear winner ... it will probably not be apparent for at least 6 months to a year. <b>(1206 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="9">In deciding between HD DVD and Blu-ray, what is the most important factor?</a></h2>
<div style="float:left"><object id="31220019" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31220019%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31220019%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>The results to this question came out about where we expected. Picture Quality being the clear most important factor, with Price and Titles both about equal. Most could apparently care less about the Brand or Capacity of the disc, although both of those do factor into the Picture Quality to some extent.</p><p>There was also an open-ended component to this question, where respondents could specify an "Other" important factor. 131 people specified "Other", so perhaps this questions option should be expanded on future surveys as well. <b>(1170 respondents)</b><br clear="all" /></p><p>Some of the most popular "Other" answers are listed below ... my favorite is the last one, which was mentioned several times:
<ul>
<li>All of the above</li>
<li>Backward Compatibility</li>
<li>Clear Leader/Winner</li>
<li>Combo Player</li>
<li>Playstation 3</li>
<li>Recordability</li>
<li>Not Sony</li>
</ul></p>

<br />
<h2><a name="10">Which is more important to you?</a></h2>
<div style="float:left"><object id="30926686" width="200" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926686%26width%3D200%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=450">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30926686%26width%3D200%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>It should be no surprise that people preferred Quality to Quantity 3 to 1. This gap will widen considerably as more content makes the move to HD. In the next year we will see more content in the areas of Gaming, Satellite, and Packaged Media (HD DVD &amp; Blu-ray). There will also be SOME content coming available via Internet download ... but don't expect this to be a huge market for more than 12 months out. <b>(1205 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="11">As it relates to high definition, for which of the following areas do you have an interest?</a></h2>
<div style="float:left"><object id="30678608" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678608%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678608%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This question was primarily geared to help us determine which portions of our website should be improved or expanded to continue retaining the interest of our visitors. The answers here are about where I expect them to be, with the possible exception being Sports. I would have predicted that Sports would have been up there with Movies. This question will have an "Other" option on future surveys, as I'm sure there were other interest categories that would have been specified. <b>(1206 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="12">Approximately how much do you have invested in your home entertainment system (components, cables, media, etc)? (US Dollars)</a></h2>
<div style="float:left"><object id="30678643" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678643%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678643%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3DOptionID&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This is included mainly for your own comparison. There are not any juicy conclusions with this data ... so enjoy. <b>(1195 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="13">Which of the following home entertainment system components and services do you own and/or subscribe to?</a></h2>
<div style="float:left"><object id="30678656" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678656%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678656%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=250&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="250"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>It was a bit of a surprise to have an "audio" option lead on an HDTV-related study, but I suppose high-end audio goes hand-in-hand with high-end video. <b>(1187 respondents)</b><br clear="all" />Some other interesting comparisons:
<ul>
<li>CRT's are still more prevalent than flat-panel technology ... this will change</li>
<li>Cable &amp; Satellite are about even</li>
<li>More people own HDPC/Media Center's than own Xbox 360's</li>
<li>4x as many people own HD DVD players vs. Blu-ray</li>
<li>27 people own PS3's ????</li>
</ul></p>

<br />
<h2><a name="14">Do you intend to purchase an HDTV within the next 6 months?</a></h2>
<div style="float:left"><object id="31218024" width="125" height="125" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218024%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=125">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31218024%26width%3D125%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=125&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="125"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>1 in 4 intend to buy an HDTV within the next 6 months. This figure is included primarily to support the next chart. It is rather interesting that this works out to 305 respondents (24% of 1190), yet 670 answered the next question. I thought the next question was dependent upon a "Yes" answer to this one, but apparently it was not. <b>(1190 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="15">If you intend to purchase an HDTV within the next six months, which type of HDTV are you most likely to purchase?</a></h2>
<div style="float:left"><object id="31217160" width="400" height="200" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31217160%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31217160%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=200&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="200"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This is the second question on the survey that indicated LCD was more popular than Plasma. More than twice as many people intend to purchase an LCD television that do Plasma. And LCoS appears to be poised to overtake Plasma in the near future, aided no doubt by the new SXRD line from Sony. It is also interesting to note that there are still people who intend to purchase CRT televisions ... even though most manufacturs have gotten out of the CRT lines. <b>(670 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="16">Are you planning on buying a Sony Playstation 3 (PS3) within the next 6 months? (assuming they stick to the 11/17 release date)</a></h2>
<div style="float:left"><object id="31221468" width="425" height="100" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31221468%26width%3D425%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=425">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31221468%26width%3D425%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=600"
		FlashVars=""
		quality="high"
		width="600"
		height="100"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div><br clear="all" />
<p>Only one in 7 intend to purchase the PS3 when it comes out next month. Perhaps not a surprise given that the average age of respondents is not typical of the gaming generation. <b>(1183 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="17">Assuming neither technology pulls ahead, are you planning on purchasing a next-gen DVD player within the next 6 months?</a></h2>
<div style="float:left"><object id="31224566" width="175" height="100" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31224566%26width%3D175%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=175">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Pie3D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D31224566%26width%3D175%26type%3DFC_2_3_Pie3D%26order%3Dnum%20DESC&chartHeight=100&chartWidth=250"
		FlashVars=""
		quality="high"
		width="250"
		height="100"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>Like the other HD DVD/Blu-ray above, this also indicates that most people are willing to wait it out rather than make another format mistake. HD DVD does again have a slight preference over Blu-ray, but it is negligable on a respondent pool of this size. <b>(1186 respondents)</b><br clear="all" /></p>

<br />
<h2><a name="18">Which other home entertainment components and services are you planning to purchase in the next 6 months?</a></h2>
<div style="float:left"><object id="30678790" width="400" height="175" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">
	<param name="movie" value="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678790%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=400">
	<param name="FlashVars" value="">
	<param name="quality" value="high">
	<embed
		src="http://www.hdtvmagazine.com/charts/FC_2_3_Bar2D.swf?dataURL=http%3A%2F%2Fwww.hdtvmagazine.com%2Fxml%2Fstudy_xml.php%3Fqid%3D30678790%26width%3D400%26type%3DFC_2_3_Bar2D%26order%3Dnum%20DESC&chartHeight=175&chartWidth=450"
		FlashVars=""
		quality="high"
		width="450"
		height="175"
		name="$name"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer">
	</embed>
</object></div>
<p>This question clearly needs expanded to other options, given that over a third of all respondents provided an "Other" answer. Of those that selected something other than "Other", HD TiVo/DVR was the clear leader. <b>(616 respondents)</b><br clear="all" /></p><p>Some of the more popular "Other" entries:
<ul>
<li>DirecTV's new HD DVR</li>
<li>DirecTV's new MPEG-4 receiver</li>
<li>FiOS/Fiber Optic service</li>
<li>None</li>
<li>PS3</li>
</ul>
</p>


<h2><a name="19">Other Comments</a></h2>
<p>On the survey, we had an open-ended question: "What else is on your mind?". These will be collected and published in a subsequent article. We had 442 people take the time to submit their comments to us, and we'd like to take the time to digest each of these and respond to as many as possibly. So watch for that article at a later date. <b>(442 respondents)</b><br clear="all" /></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 27, 2006 11:09 AM</b>
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
			<?=getComments(465)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 465)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/10/fall-2006-hdtv-study-results.php" type="text/javascript" charset="utf-8"></script>
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