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
		AND e.entry_id = 381";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 381 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 381 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 381";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/05/displaysearch-reports-global-lcd-tv-shipments-rise-135-in-q1.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 381";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1" />
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
	<title>HDTV Magazine - DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1</title>
	<meta name="keywords" content="larger sizes, north america, while falling, unit share, units revenues, share, lcd, sony, units, philips, revenues, samsung, larger, unit, europe, sharp, shipments, sizes, remained, rose, japan, revenue, brands, while, market" />
	<meta name="description" content="DisplaySearch has released Q1'06 worldwide LCD TV shipments and revenues by brand, region, size and resolution for over 40 different LCD TV brands as part of its Quarterly Global TV Shipment and Forecast Report.

LCD TV shipments jumped... " />
	<meta name="title" content="DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/05/displaysearch-reports-global-lcd-tv-shipments-rise-135-in-q1.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="DisplaySearch has released Q1'06 worldwide LCD TV shipments and revenues by brand, region, size and resolution for over 40 different LCD TV brands as part of its Quarterly Global TV Shipment and Forecast Report.

LCD TV shipments jumped... " />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=381', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/05/displaysearch-reports-global-lcd-tv-shipments-rise-135-in-q1.php">DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 24, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>
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
				<p><em>The following press release is brought to you in its entirety as it states without artifice the state of the LCD industry. </em><br />
<html></p>

<p><head><br />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><br />
<title>New Page 1</title><br />
</head></p>

<p><body></p>

<center>
<table cellSpacing="0" cellPadding="0" width="100%" border="0" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table1">
	<tr>
		<td bgColor="#ffffff" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
		<table cellSpacing="0" cellPadding="0" width="100%" border="0" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table2">
			<tr>
				<td bgColor="#ffffff" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
				<h1><font face="Arial" size="4">DisplaySearch Reports Global LCD 
				TV Shipments Rise 135% in Q1: Sony Remains #1 in Revenues, 
				Philips Earns Top Unit Share</font></h1>
				<p>AUSTIN, TEXAS, May 24, 2006 - DisplaySearch, the worldwide 
				leader in display market research and consulting and part of The 
				NPD Group, has released Q1'06 worldwide LCD TV shipments and 
				revenues by brand, region, size and resolution for over 40 
				different LCD TV brands as part of its
				<a title="http://www.displaysearch.com/products/?pn=gtv" style="color: 006FA2; text-decoration: none" href="http://www.displaysearch.com/products/?pn=gtv">
				<i><b>Quarterly Global TV Shipment and Forecast Report.</b></i></a></p>
				<p>LCD TV shipments jumped 135% year-over-year (Y/Y) while 
				falling 14% quarter-over-quarter (Q/Q) to 7.4M units. LCDs had 
				the fastest Y/Y growth and smallest sequential decline of any TV 
				technology in Q1'06, taking share in each region and were the 
				only technology to gain share sequentially rising from a 15% 
				share in Q4'05 to a 17% share in Q1'06. Due to gains by larger 
				sizes, LCD TV revenues grew nearly as fast as LCD TV unit 
				shipments, rising 114% Y/Y while falling 12% Q/Q to $8.8B. The 
				average diagonal rose 19% Y/Y and 2% Q/Q to 27.0&quot; as larger 
				sizes continue to become increasingly affordable. ASPs increased 
				2% Q/Q while falling just 9% Y/Y to $1195. 37&quot;, 40&quot;-42&quot;, 20&quot;-21&quot; 
				and 45&quot;+ were the only size categories to gain share with 
				22&quot;-23&quot;, 26&quot;-27&quot; and 30&quot;-32&quot; flat and other categories losing 
				share. The 37&quot; and larger share rose from 12% to 14% on a unit 
				basis and from 29% to 32% on a revenue basis.</p>
				<p>Relative to other technologies, LCD TVs overtook CRT TVs at 
				30&quot;-34&quot; for the first time on a unit basis in Q1'06 and rose 
				from 17% to 23% of the 40&quot;-44&quot; market with PDPs and MD RPTVs 
				losing share.</p>
				<p>All regions enjoyed at least 116% Y/Y growth except Japan 
				which grew just 35%. Europe and China continued to take share 
				from North America and Japan with Europe's share rising from 44% 
				to 46% on pre-World Cup demand.</p>
				<p>Four brands dominate the LCD TV market accounting for a 50% 
				share of units and a 54% share of revenues. The rankings of 
				these four depend on whether units or revenues are examined with 
				the rankings reversed due to Sony and Samsung's focus on larger 
				sizes supporting their revenue share and Philips and Sharp's 
				focus at all sizes supporting their unit share. As indicated in 
				Tables 1 and 2, Philips was #1 in units but #4 in revenues while 
				Sony was #4 in units and #1 in revenues. Sony had the greatest 
				focus on larger sizes of the top nine brands with 63% of its 
				shipments at 30&quot; and larger compared to Samsung at 51%, Sharp at 
				41% and Philips at 38%. Philips was #1 in units in Europe and 
				North America and led the 15&quot;-19&quot; market worldwide. Sharp rose 
				from #3 to #2 in units worldwide, remained #1 in Japan and 
				maintained the top position at 10&quot;-14&quot;, 20&quot;-21&quot;, 37&quot; and 45&quot;+ 
				size categories. Samsung had the slowest sequential decline of 
				any of the top four brands and overtook Sony at 2! 2&quot;-23&quot;, 
				26&quot;-27&quot; and 30&quot;-32&quot;. It led in rest of world (ROW) and was a 
				close second to Philips in Europe. Sony remained #1 at 40&quot;-42&quot;. 
				On a revenue basis, Sony remained #1 in North America, Samsung 
				led in Europe and ROW, Sharp remained #1 in Japan and Hisense 
				remained #1 in China.</p>
				<p><b></p>
				<center>Table 1: LCD TV Unit Share</center></b>
				<p>&nbsp;</p>
				<table borderColor="#999999" cellSpacing="0" cellPadding="0" rules="cols" width="80%" align="center" frame="below" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table3">
					<tr class="table_title" bgColor="#333333">
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Ranking
						</strong></font></td>
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Brand
						</strong></font></td>
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Q4'05 
						Share </strong></font></td>
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Q1'06 
						Share </strong></font></td>
					</tr>
					<tr>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">1</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Philips/Magnavox </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">14.2% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">13.9% </td>
					</tr>
					<tr bgColor="#cccccc">
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">2 </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Sharp</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">13.6% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">13.1% </td>
					</tr>
					<tr>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">3 </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Samsung </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">11.6% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">12.5% </td>
					</tr>
					<tr bgColor="#cccccc">
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">4 </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Sony </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">14.6% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">10.9% </td>
					</tr>
					<tr>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">5 </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">LGE </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">6.4% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">6.9% </td>
					</tr>
					<tr bgColor="#cccccc">
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">&nbsp;</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Others </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">39.6% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">42.7% </td>
					</tr>
					<tr>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">&nbsp;</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Total </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">100.0% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">100.0% </td>
					</tr>
				</table>
				<p><b></p>
				<center>Table 2: LCD TV Revenue Share</center></b>
				<p>&nbsp;</p>
				<table borderColor="#999999" cellSpacing="0" cellPadding="0" rules="cols" width="80%" align="center" frame="below" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060" id="table4">
					<tr class="table_title" bgColor="#333333">
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Ranking
						</strong></font></td>
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Brand
						</strong></font></td>
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Q4'05 
						Share </strong></font></td>
						<td width="25%" style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center"><font color="#ffffff"><strong>Q1'06 
						Share </strong></font></td>
					</tr>
					<tr>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">1</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Sony</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">19.1% </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">15.0% </td>
					</tr>
					<tr bgColor="#cccccc">
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">2 </td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060">
						<p align="center">Samsung</td>
						<td style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 10px; font-f
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 24, 2006  5:25 PM</b>
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
			<?=getComments(381)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 381)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/displaysearch-reports-global-lcd-tv-shipments-rise-135-in-q1.php" type="text/javascript" charset="utf-8"></script>
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