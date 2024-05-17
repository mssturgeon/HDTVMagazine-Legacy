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
		AND e.entry_id = 5063";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5063 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5063 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5063";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2013/02/smartstick-makes-any-tv-smart.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5063";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download SmartStick Makes "Any" TV Smart" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="SmartStick Makes "Any" TV Smart" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="SmartStick Makes "Any" TV Smart" />
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
	<title>HDTV Magazine - SmartStick Makes "Any" TV Smart</title>
	<meta name="keywords" content="firmware upgrade, browsing email, digital analog, online keyboard, smart stick, smartstick, tvs, smart, may, analog, any, hdmi, device, unit, keyboard, those, make, could, upgrade, email, million, browsing, resolution, digital, product" />
	<meta name="description" content="This small device promises to make &quot;any TV&quot; Smart, giving you access to Internet streaming services such as Netflix, YouTube, etc., network content, and even browsing capabilities and email, all using your own TV as display device.

The idea is great, the device is relatively low in price but I should add: when it works and when you have the compatible TV. 

The first question I asked myself is: which kind of TV can actually be made &quot;Smart&quot; based on the connectivity and specifications of the SmartStick?" />
	<meta name="title" content="SmartStick Makes &quot;Any&quot; TV Smart" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="SmartStick Makes &quot;Any&quot; TV Smart" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2013/02/smartstick-makes-any-tv-smart.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This small device promises to make &quot;any TV&quot; Smart, giving you access to Internet streaming services such as Netflix, YouTube, etc., network content, and even browsing capabilities and email, all using your own TV as display device.

The idea is great, the device is relatively low in price but I should add: when it works and when you have the compatible TV. 

The first question I asked myself is: which kind of TV can actually be made &quot;Smart&quot; based on the connectivity and specifications of the SmartStick?" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5063', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2013/02/smartstick-makes-any-tv-smart.php">SmartStick Makes "Any" TV Smart</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 26, 2013</b>
							</td><td id="article_category">
								Categories: 
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
				<p>&nbsp; <h2><strong>The Catch: Only for an HDTV that accepts 720p or 1080p via HDMI</strong></h2> <p><a href="http://www.favientertainment.com/" target="_blank">This small device</a> promises to make "any TV" Smart, giving you access to Internet streaming services such as Netflix, YouTube, etc., network content, and even browsing capabilities and email, all using your own TV as display device.  <p>The idea is great, the device is relatively low in price but I should add: when it works and when you have the compatible TV. <a href="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/image_2.png"><img alt="" src="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/image_thumb.png" width="583" height="231"></a> <h2><b>Initial Examination</b></h2> <p>The first question I asked myself is: which kind of TV can actually be made "Smart" based on the connectivity and specifications of the SmartStick?  <p>The second question I asked myself is: which of your TVs would actually benefit more from a Smart upgrade?  <p><b>To answer the first question,</b> the web-published specs and User Guides contradict each other. A non-technically inclined consumer may probably buy it because of the general <i>"any TV"</i> claim, but the devil is in the details, here are just two specs, and I quote:  <ul> <li><i>Make any TV a Smart TV</i>  <li><i>100% Compatible with any HDTV</i></li></ul> <p>In theory the generalized concept of <i>"any TV"</i> as advertised should also include the black-and-white TV your grandfather used to watch "I love Lucy" 60 years ago, but to be reasonable, where do we draw the line of backward compatibility for the <i>"any TV"</i> claim?  <p>The statements of "<i>any TV</i>" and of "<i>100% compatible with any HDTV</i>" are not technically true considering that at least 124 million TVs in the US (35% of the whole US 357 million TV inventory, details below) do not have HDMI inputs, which is how the Smart Stick connects to the TV, additionally, most of those TVs (if not all) do not accept the 720p or 1080p output resolutions of the SmartStick.  <p>But the promising news is that I talked to Chad Stayton, Director of Product Development of Smart Stick and he was receptive of my recommendations and ideas for backward connectivity and resolution, he said they may be considered as future improvements/products (more below).  <p>However, there could be a higher cost for a "one-for-all" device if we require the device to include components and connectors to make it backward compatible to <i>"any TV"</i>. Chad and I discussed the alternatives, which I cover further down.  <p><b>Regarding the second question of </b>"which of your TVs would benefit more from a Smart upgrade?"  <p>Would you like your perfectly functional color 480i analog TV you still have in the basement to be able to access Netflix, so your grand kids would not destroy your new fancy plasma? No luck.  <p>Or your early adopter 1080i HDTV you bought early in the DTV Transition (about 11 million HDTVs sold in 1998-2003 had only component analog connections) to be able to access Google and email? No luck, even when buying an additional digital-to-analog converter due to limited resolutions (more below).  <p>About the new HDTV you bought last year for the family room? would you like for it to be a bit smarter and have flexibility of content selection for your group gatherings? The SmartStick could make it smarter.  <p>Why not making Smart all of the above TVs if the idea is to make "<i>any TV"</i> smarter?  <p>Because as I said above, making a one-for-all super connected Smart Stick will make all buyers pay for features they may not need. I recommended to Chad to make a couple of models dedicated to their connectivity purpose to lower the cost of each unit.  <h2><b>TVs in the US</b></h2> <p><a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php" target="_blank">When I was fully involved</a> with the DTV transition over the past decade the Consumers Electronics Association (CEA) published yearly statistics that kept adjusting since 1998 as millions of early adopters started buying more and more HDTVs. According to the CEA's data they use now 119 million households (Census), and 3 TVs in each household on average: 357 million TVs in total.  <p>On <a href="http://www.hdtvmagazine.com/articles/2013/02/hdtv-adoption-not-as-high-in-number-of-hdtv-sets.php" target="_blank">this recent article</a> I analyzed the DTV penetration; from 1998 to 2012 the CEA reported 244 million DTVs shipped, which means that at least other 113 million are analog TVs that are still in use in analog and digital homes.  <p>Add to those the estimated 11 million of HDTVs sold before HDMI was implemented and that would make a total of 124 million TVs that could benefit with modern Smart features, but they cannot due to connectivity/compatibility limitations of the SmartStick. That is 35% of the TV market that is ignored by the SmartStick, and in my opinion the market share that will see a great jump in the smart capabilities of their legacy TVs.  <p>Therefore if one company like Favi Entertainment announces to you that they have a $50 SmartStick that can make <i>"any"</i> of your TVs smart, which TV in your home would you think could get more benefit?  <h2><b>The Tests</b></h2> <p>I tested the SmartStick with several TV technologies all the way to a 4K projector, and my review found various factors of appreciation for what the unit claims to do, and also found factors that I would like the unit to do better, but unfortunately I experienced a high number of performance problems.<a href="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/clip_image005_2.jpg"><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/clip_image005_thumb.jpg" width="265" height="341"></a>  <p>Let me clarify that this review is not the typical introductory blog to advertise the device; that was already (and loosely) done by most other publications and bloggers.  <p>I analyze the device's applicability, performance, or compatibility, and to do that I also wear the imaginary shoes of owners of millions of varied TVs out there, not just the TVs I use for my tests.  <p>The <a href="http://www.amazon.com/FAVI-SmartStick-Android-Apps-Built-/dp/B009JBGR80/ref=sr_1_1?ie=UTF8&amp;qid=1360388079&amp;sr=8-1&amp;keywords=smartstick" target="_blank">SmartStick</a> features are theoretically positive for their potential, although in my case it failed to work on most of my tests for no apparent reason during 3 weeks of testing. It was weak on features that may not affect me personally but may affect others, like the online keyboard, remote, the design choices made regarding connectivity and resolution, etc.  <p>For starters, there is a discrepancy in <a href="https://docs.google.com/file/d/0BwXbgEaIfimIN3VmYmdqMmxBWTQ/edit?usp=sharing&amp;pli=1" target="_blank">Page 14 of the User's Guide</a> that specifies that the "display ratio output mode" can be set to either720p, 1080i, or 1080p, but page 20 of the same Guide (and the online menu) shows a spec that omits 1080i.  <p>The reality is that the SmartStick as delivered today cannot output 1080i, but as I said, Chad thought the unit was designed to allow for a 1080i addition in the resolution list with a firmware upgrade.  <p><a href="http://www.hdtvmagazine.com/testWindowsLiveWriter/SmartStickMakesAnyTVSmart_134AD/clip_image007_2.gif"></a>&nbsp; <h2><b>Wi-Fi, Streaming</b></h2> <p>The streaming experience was fair in a few instances, but erratic and unpredictable in most others, rejecting the server connection, loading delays and then disconnecting, disconnecting every few seconds, returning Netflix errors, giving looping security certificate error messages when browsing at every site including Google, etc.  <p>I decided to give another chance to the unit for a few more days to repeat the same tests in the hope to determine if there was a pattern for the problems, such as in a particular content or application, how busy the Wi-Fi line was, the time of day, etc. but I could not make the unit work consistently well at any particular session to conclude anything, so I ran out of patience.  <p>My ISP service is 100Mbps over fiber and the SmarStick menu clocked 57Mbps from my Wi-Fi router, I tested some days at 3am when nobody was in the house line, but usually the Wi-Fi connection was dropped right after seeing the speed.  <p>In one case<a href="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/clip_image009_2.jpg"><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/clip_image009_thumb.jpg" width="373" height="357"></a> the Wi-Fi router was just a couple of feet away from the SmartStick but still failed to maintain a connection.  <p>As I did many times on these tests I rebooted to see if that would restore it to a more stable performance, but after reconnecting it dropped the line again.  <p>After rebooting several times within a few minutes I reached a point that it was not possible to test features that require an active Ethernet connection. Other Wi-Fi devices in the room streamed normally.&nbsp; <p>I thought that it may have been practical if the unit had a standard RJ45 connector as a backup plan if a user has Wi-Fi difficulty and does not mind using the wired Ethernet connection if close to the TV and the SmartStick.  <p>I typically do not allow myself to try more than one review unit (or manufacturer tweaked units) because my theory is that I have to imitate exactly what a regular buyer would face and expect from a purchased product at the local store, a properly working unit out of the box, so I did not request a second media review unit.  <p>I thought about buying one unit myself at Amazon and retest but quite frankly I did not have the time to do that and my interest was decaying rapidly with the disappointing performance of my particular unit. However, I may consider doing that if/when the connectivity/compatibility issues I mentioned are incorporated and the problems I experienced corrected.  <h2><b>Keyboard</b></h2> <p>I tested SmartStick's <a href="http://www.favientertainment.com/SmartStick-Keyboard-p/fe02rf-bl.htm" target="_blank">Mini-wireless keyboard</a> with laser pointer mouse (a $39.99 product) and the Touch pad within it, it has very small keys which Smart phone users may find easy to use, the overall appearance and size was attractive.  <p>I suggest for the on-the-screen keyboard to have the ability to allow the cursor shift outside any given row/column of letters/numbers, so one can quickly go to the first letter of a row, for example, by just going over the last letter of the same row if it is closer to get to the target letter that way, in other words, looping around rows and columns, rather than going to the end and back only.&nbsp; <p>The above would facilitate typing at a bit more of speed with the online keyboard. One thing is just the one-time effort of entering an account ID and password to set up a service, a different thing is having to use the same online keyboard for abundant typing for browsing and email, which is absolutely impractical.  <p>It would be ideal that any remaining improvement be made to the online keyboard so active keyboard users would not have to recur to a wireless keyboard and mouse, adding to the total cost of ownership.  <p>However, this is purely the preference of the user, which may end up using an additional keyboard/mouse even if the online keyboard is improved to a satisfactory level.  <h2><b>Menu</b></h2> <p><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/SmartStickMakesAnyTVSmart_134AD/clip_image007_3.gif" width="522" height="289"></p> <p>The menu and GUI were acceptable to me as a Smart basic product. I installed and used some Smart apps but I cannot comment on the whole Android app inventory, besides, the dysfunctional unit I tested would not have allowed me to go too far in testing other apps.</p> <h2><b>Remote Control</b></h2> <p>I could not make the small remote control to work for days, and then it suddenly started working; the battery was fine, it tested well at 3 volts. I read reviews of users that claimed to have the same problem, to which Favi responded that the remote is used for backing up? (I could not remember the term used in the online review exchanges) </p> <p>Not having the remote working was actually secondary to me because I was using the Mini keyboard and Mouse for speed typing and could not dedicate more time debugging the issue, but I have to mention the remote issue because it may be important to those not buying the keyboard (or those using their own).  <h2><b>Browsing, Email</b></h2> <p>What I liked about the Smart Stick is the capability of browsing and email for those that cannot separate their online activity (social network, email, browsing, etc.) from their entertainment TV/movie activity using the same display, I should say that was the primary reason I was interested in reviewing it, but not for me.  <p>Typically I do exactly the opposite, there are no interruptions when I watch a movie, and I do not use my home theater to browse or email on a large screen, but I respect others preferring the multitasking during entertainment, and SmartStick provides unique value in that regard.  <p>The browsing facility also allows for a connection to video sources from sites and countries for which there may be no application available and a laptop would have been required instead.  <h2><strong>Specs</strong></h2> <p>The SmartStick comes in a <a href="http://www.favientertainment.com/SmartStick-p/ss-4gb.htm" target="_blank">4GB</a> ($49.99) and <a href="http://www.favientertainment.com/SmartStick-p/ss-8gb.htm" target="_blank">8GB</a> ($79.99) versions, and accepts a micro SD card as well.  <p>The SmartStick has an HDMI male output plug to directly connect to an empty HDMI port of the TV eliminating the need of an HDMI cable.  <p>If your installation already has an HDMI wire plugged to an empty port of your TV panel because it was prewired that way when installed if the panel cannot be easily moved out of its installation/enclosure, I suggest the use of a female-female HDMI adaptor to facilitate the connection to the SmartStick's HDMI male plug. This was the case in one of the HDTVs I used to test the SmartStick.  <p>Here are the specs:  <ul> <li>OS: Android (4.0)  <li>Resolution: 720P, 1080P (HDMI)  <li>Microprocessor: ARM Cortex-A9 @ 1.0 GHz  <li>RAM: 1GB DDR3 SDRam  <li>Built-in Wi-Fi: 802.11 b/g/n  <li>Storage: 4GB or 8GB  <li>USB Port: 2.0  <li>Mini USB: DC Input,  <li>Micro SD: Up to 32GB  <li>Remote Control: Infrared  <li>Dimensions: 3.6" x 1.3" x 0.6"  <li>Weight: 0.08 lb.  <li><a href="https://docs.google.com/file/d/0BwXbgEaIfimIOFl3YU5tcTBkYVk/edit?usp=sharing" target="_blank">Download Quick Connect</a> | <a href="https://docs.google.com/file/d/0BwXbgEaIfimIN3VmYmdqMmxBWTQ/edit?usp=sharing" target="_blank">Download User Guide</a></li></ul> <h2><b>The case for Backward Compatibility</b></h2> <p>As I mentioned before, the HDMI output was not made capable to deliver 1080i/480i/p. I would not expect such limitation from a product that claims to add modern (Smart) functionality to "any TV".&nbsp; <p>Chad took my suggestion of adding more resolutions to the device and it appears to be possible with a firmware upgrade but there could be an impact on menus, presentation, and Android app selection for 480i resolution for NTSC analog TVs.  <p>I mentioned to Chad that in order to be compliant with the HDMI specification my understanding is that a device must be capable of a minimum of 480p.  <p>By adding 480i and 1080i resolutions the SmartStick would cover the whole range of backward compatibility regarding resolution with old televisions, analog or digital.  <p>But even when expanding the resolution selection of the SmartStick to 480i/p/720p/1080i/p a digital-to-analog converter to convert the HDMI output of the SmartStick to analog would still be needed, including for HDTVs that only have component analog inputs.  <p>Such converter would have to be purchased separately (about $35) increasing the cost of "Smart modernizing" those TVs. But hopefully Favi may consider my suggestion of eventually make a separate SmarStick model with an integrated converter and connectors; a one stop solution for at least 124 million televisions to become Smart regardless how old they are.  <p>Interesting enough Samsung introduced a similar small smart box a few years ago at CES and I questioned them about the same loose advertising of "any TV" because the device had the same connectivity limitations. I have not seen the product since then.  <h2><b>Would the SmartStick be worth $50 for you?</b></h2> <p>Provided that it works, it depends of the requirements of your case:  <p>a) It may be worth for some HDTVs accepting only 720p/1080p if the Wi-Fi experience is acceptable and that is all you need.  <p>b) It is not suitable for many TVs that do not accept digital signals (early H/DTVs not having HDMI, and NTSC analog TVs), because the SmartStick lacks 480i/p/1080i outputs. These TVs would require both features: matched resolution and digital to analog conversion, neither are available.&nbsp; <p>c) It may be worth to owners that absolutely have to have browsing and/or email capabilities on the one-and-only display device that is also used for the TV functionality (i.e. a student in a dorm, a small room in a shared apartment, assisted living person, etc.), provided that the display is compatible with the SmartStick specs.  <p>d) It may not be worth to those without Wi-Fi and not willing to spend on a Wi-Fi router because the house already has a CaT-5/6 network with higher speed and the customer is happy enough using Cat-5/6 wires to the devices.  <p>However, with the proliferation of tablets and mobile devices it is hard to imagine a modern house without a Wi-Fi router, unless the user always uses cell services for even that purpose.  <p>Regardless, I suggest the manufacturer to consider adding a RJ45 connector to the device.  <p>e) It would be worth for those having a portable projector with compatible resolution and HDMI input. Or those having a home-theater projector but not a Smart Blu-ray player, or a player that is smart enough to provide the basic access to Netflix and other streaming services but it lacks browsing and/or email capabilities, if that is required.  <h2><b>Final thoughts</b></h2> <p>A recent firmware upgrade for the SmartStick was released, which I applied before testing the device; several customers complained about product issues in forums and product reviews and SmartStick's customer service responded by highlighting the items covered in the firmware upgrade.  <p>However, some of what I commented above is not solved by firmware but require hardware modifications/redesign, such as analog connections if the converter can be included, an RJ45 jack, etc.  <p>The SmartStick claims features that makes it practical and provides a new life to many non-smart TVs. It may be very useful and attractive to those that were not adversely affected by some of the factors mentioned above, so my recommendation is, if you actually need a device like this, try it in "your" environment and TV, but buy it from a dealer that meets the returning policy you require for that purpose, maybe you would have better luck than I had with the review unit.  <p>However, if you have problems, before returning the unit, check with Favi's customer service, they may have aligned a firmware upgrade/fix for your situation that could be timely applied.  <p>The device appears to have a good market, but I was surprised that the chosen connectivity and resolutions ignored a very large market (35%) of not-that-old H/DTVs and legacy analog NTSC TVs that would actually be benefited with the functionality and capabilities of this product.  <p>As I said, after talking to Chad, there is a possibility that a firmware upgrade may be able to expand the choices of output resolutions, and by purchasing a reasonable priced digital-to-analog converter the SmartStick can then actually claim to upgrade "any TV" with the existing model. I quote Chad on his response:  <p>" <i>FAVI’s development team will be attempting to enable support to a wider range of resolution options, including 480i, in upcoming firmware revisions."</i>  <p>Hopefully, another model with an internal converter could eventually be offered for those wanting a single box solution to connect with TVs having only analog inputs (analog or digital TVs).  <p>It seems there is no much choice to owners of those TVs to been able to upgrade them with the features offered by the SmartStick, which include browsing and email. The total cost of the upgrade, even with purchasing an additional digital-to-analog converter, would still be relatively low compared to the alternative of replacing an otherwise perfectly functional TV.  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 26, 2013  6:56 PM</b>
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
			<?=getComments(5063)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5063)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2013/02/smartstick-makes-any-tv-smart.php" type="text/javascript" charset="utf-8"></script>
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