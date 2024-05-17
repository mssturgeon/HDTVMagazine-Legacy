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
		AND e.entry_id = 2925";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 2925 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 2925 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 2925";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 2925";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics Showcases New Products with \'Unparalleled Performance by Design\' for Holiday Season" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics Showcases New Products with \'Unparalleled Performance by Design\' for Holiday Season" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics Showcases New Products with \'Unparalleled Performance by Design\' for Holiday Season" />
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
	<title>HDTV Magazine - LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season</title>
	<meta name="keywords" content="class inch, inch class, inch diagonal, home entertainment, blu ray, inch, home, consumers, access, new, features, performance, entertainment, led, offers, class, electronics, full, hdtv, lcd, technology, design, mobile, picture, system" />
	<meta name="description" content="Led by the new SL80 and SL90 HDTVs with their seamless design and ultra-thin bezel, LG Electronics highlighted its latest collection of home entertainment, home appliance and mobile communications products at the company's 2009 holiday preview event.

During its annual Summer Line Show today LG showcased..." />
	<meta name="title" content="LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Led by the new SL80 and SL90 HDTVs with their seamless design and ultra-thin bezel, LG Electronics highlighted its latest collection of home entertainment, home appliance and mobile communications products at the company's 2009 holiday preview event.

During its annual Summer Line Show today LG showcased..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=2925', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php">LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 30, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">LG Electronics Showcases New Products with 'Unparalleled Performance by Design' for Holiday Season</p>

<center><i>First 'Seamless' LCD HDTVs Unveiled</center></i><br />
<br />

<p><strong>NEW YORK, July 30 /PRNewswire/ -- </strong>Led by the new SL80 and SL90 HDTVs with their seamless design and ultra-thin bezel, LG Electronics highlighted its latest collection of home entertainment, home appliance and mobile communications products at the company's 2009 holiday preview event.</p>

<p>During its annual Summer Line Show today - at New York City's newly renovated Alice Tully Hall in Lincoln Center for the Performing Arts - LG showcased its products' "unparalleled performance by design" and latest technological innovations.</p>

<p>"Across our brand portfolio, LG products combine innovative features, intuitive functionality, exceptional performance and stylish design to provide consumers with something better than the ordinary home entertainment experience," said Michael Ahn, president and CEO, LG Electronics North America. "Our featured Summer Line Show products, from our 4-Door French-Door Refrigerator with automatic open/close drawers, to our mobile phone with touch screens, and our advanced new SL80/90 HDTVs, demonstrate how together innovation and design produce exceptional performance."</p>

<p>LG Electronics continues to expand the content-on-demand options for consumers by announcing a new alliance with VUDU, building on its collaborations with Netflix, CinemaNow, Yahoo! and YouTube. Through this new alliance, LG is giving consumers access to an extensive online library of high-definition movie titles. (See separate release for additional details)</p>

<p><br />
<strong>Home Entertainment</strong></p>

<p>Leading the charge with HDTV innovations, such as extensive broadband capabilities and enhanced picture quality, LG Electronics is further broadening its home entertainment product family with the unveiling of the SL80 and SL90 series of LCD HDTVs.</p>

<p>Now, there's an LCD HDTV that will truly turn heads whether the screen is on or off. The SL80's advanced technology and seamless edge-to-edge panel - over an ultra-slim bezel - establish a new benchmark in the possibilities of LCD technology. Available beginning in August, the SL80 is designed to deliver exceptional picture quality, sporting a 150,000:1 contrast ratio for greater color detail and deeper blacks. It also offers TruMotion 240Hz to handle fast-moving images at lightning speed.</p>

<p>Expanding its stylish line of seamless HDTVs, LG also unveiled LED versions, the SL90 series, which will be available later this year. At only 1.15 inches thick the ultra-slim models add LED technology for the ultimate in picture quality and further energy savings.</p>

<p>LG home entertainment products featured at the summer line show include:</p>

<ul><li>LHX Slim Wireless LED Backlight HDTV (Class Size: 55-inch*): LG's LHX offers superior picture quality with an elegant ultra-slim design - less than one-inch thick at its thinnest point. The HDTV uses a full array of LED backlights, which employ local dimming techniques for precise picture control, resulting in deeper blacks, wide color gamut and smooth motion, achieving 240Hz performance for more natural picture clarity. In addition, the LHX offers the convenience and flexibility of wireless transmission, allowing source components to be placed up to 30 feet away yet still providing uncompressed, Full HD 1080p picture signal transmission.</li><li>LH90 LED Backlight HDTV (Class Sizes: 55-, 47-, and 42-inch*): As the first LCD to receive THX Display Certification in the U.S. market, LG's LH90 series of LCD HDTVs employs TruMotion 240Hz technology, allowing for a more satisfying viewing experience while delivering images that move more naturally. The LH90 LCD HDTV series also features LED backlighting, which employs local dimming techniques for precise picture control.</li><li>LH50 Full HD 1080p LCD HDTV with NetCast(TM) Entertainment Access (Class Sizes: 47-, and 42-inch*): Using LG's first-ever HDTV with Ethernet connectivity, consumers can access even more content and video on their big screen TV without the need for a computer. This connectivity offers access to Netflix Instant Streaming, Yahoo! Widgets, YouTube, VUDU**, and access to music and photos stored on a home PC, so consumers have a choice when it comes to entertainment on a single device.</li><li>PS80 Full HD 1080p Plasma HDTV with NetCast(TM) Entertainment Access (Class Sizes: 60- and 50-inch*): LG's first plasma HDTV with Ethernet connectivity allows consumers to access even more content and video on their big screen TV without the need for a computer. This connectivity offers access to Netflix Instant Streaming, Yahoo! Widgets, YouTube**, and can also access music and photos stored on a home PC.  The PS80 also features THX Display Certification.</li><li>LG BD370 Network Blu-ray Disc Player: The BD370 offers consumers broadband connectivity and advanced audio capabilities with audio format decoding, such as Dolby TrueHD/Digital Plus and DTS-HD for a crisper, clearer auditory experience. Other features include Full HD 1080p Blu-ray disc playback with BD-Live and BonusView, and NetCast(TM) Entertainment Access, which includes Netflix instant streaming, instant access to the latest movie titles from CinemaNow, and a world of entertainment options with YouTube access**.</li><li>LG BD390 Network Blu-ray Disc Player: The BD390 boasts all the same core functionalities of the BD370 model but takes performance and connectivity a step further, featuring integrated wireless home networking for easy connection to the home network, and 1GB of built-in memory, offering consumers a simpler option for enjoying BD-Live content from their favorite Blu-ray movies without the need for a flash drive. Discrete 7.1 channel audio outputs offer exceptional connectivity and performance.</li><li>LHB953 Blu-ray Home Theater System: The perfect complement to any movie enthusiast's home entertainment system, the LHB953 Network Blu-ray Home Theater System is equipped with a 5.1 surround sound system and LG's Netcast(TM) Entertainment Access, which offers consumers multiple content-on-demand options including instant streaming from Netflix, YouTube(TM) and LG's latest content provider, Pandora  Internet radio**, right out of the box. For those that enjoy pure high definition radio, the LHB953 combines Pure HD audio performance with Dolby Digital Plus, Dolby True HD and dts-HD Advanced Digital Audio Out for an uncompressed next-generation audio experience for your home theater.</li><li>LHB977 Blu-ray Home Theater System: The LHB977 includes similar features to the LHB953, including NetCast Entertainment Access, but also features two HDMI inputs for exceptional connectivity to high-def, sources such as cable boxes and game systems, two tallboy speakers, and two stylish satellite speakers that offer superior audio performance and complement any home theater.</li><li>M237WD: The M237WD offers consumers flexibility as it doubles as a monitor and a full 1080p HDTV. It delivers premium picture performance for viewing videos or using graphic-intensive applications with its 30,000:1 contrast ratio. Its two HDMI inputs make it a multimedia centerpiece, allowing consumers to easily and conveniently connect to other digital devices. A remote control completes the package.</li><li>W2286L LED Monitor: LG's newest LCD monitor features an LED backlit display for advanced performance with a stylish, color-infused design. With 'Full HD' 1080p resolution and a 2,000,000:1 digital fine contrast ratio, the W2286L sets a new standard for desktop monitors.</li><li>N2R1 Network Attached Storage: LG's N2R1 is the first-ever Network Attached Storage with a built in DVD burner. Compatible with Windows, Linux and Mac operating systems, the NAS unit also offers advanced data management. With its simple and clean design, the N2R1 is perfect for consumers looking for the one destination to access and back all their data files.</li></ul>

<p><br />
<strong>Mobile Communications</strong></p>

<p>With the latest in touch screen technology, advanced music, Bluetooth 2.0, solar power technology, and full line of QWERTY devices for every carrier, LG Mobile Phones continue to bring style, function, and performance to the wireless industry, meeting the demands of today's mobile consumer.</p>

<p>Mobile phones and Bluetooth accessories featured at the summer line show include:</p>

<ul><li>XENON(TM): The LG XENON(TM) is the model of mobile innovation with a large touch screen, enhanced flash user interface that makes menus, shortcuts, and contacts available right at your fingertips.  The two mega pixel camera and slide-out QWERTY keyboard allows users to quickly send text messages and emails.</li><li>NEON(TM): The LG NEON(TM) is a compact and colorful phone that lets users easily stay connected to their social circle with its external touch screen, slide-out full QWERTY keyboard and two mega-pixel camera.</li><li>Glance(TM) : Style-oriented consumers will respond to the sophisticated design and ultra-slim profile of the new LG Glance, which boasts an elegant, woven metal back plate, easy-to-read two-inch display, enhanced Bluetooth capability, one-touch speakerphone, speaker-independent voice command and a 1.3 mega pixel camera with picture messaging.</li><li>enV3: Text messaging fanatics can't wait to get their thumbs on the new LG enV3, a slim and feature-packed handset with a compact QWERTY keyboard, 2.6-inch internal screen, three mega-pixel camera and camcorder, Bluetooth stereo capability, a music player, and favorites key that enables users to connect with their ten most frequent contacts in a flash.</li><li>enV TOUCH: The new LG enV TOUCH combines undeniable style with an unrivaled multimedia experience by offering features such as its three-inch external touch screen, full-size QWERTY keyboard and 3.2 mega pixel camera with built-in flash, Dolby  Mobile sound, and document reader.</li><li>LX370: LG LX370 is an easy-to-use vertical slider, providing users with a sleek powerful device including a 2.0 mega pixel camera, Stereo Bluetooth, MP3 player with microSD memory card slot and more.</li><li>Rumor2 (new colors): Hot on the heels of the fast-selling LG Rumor, LG proudly presents the LG Rumor2, boasting features to delight both tech-oriented and image-conscious consumers, such as the 4-line QWERTY keypad, eye-popping screen resolution (QVGA 240x320), MP3 music player, 1.3 mega pixel camera and removable backplates for customization.</li></ul>

<p><br />
<strong>Home Appliances</strong></p>

<p>With industry-leading features, powerful performance and sleek, contemporary styling, LG offers a full suite of premium kitchen and laundry appliances, providing consumers something better throughout the home, helping make everyday household tasks such as cooking and laundry a more enjoyable experience.</p>

<p>LG home appliance products featured at the Summer Line Show, include:</p>

<ul><li>Ultra-Capacity 4-Door French-Door Refrigerator (model LMX28987ST): LG's French-door refrigeration line now features an ultra-capacity 27.5 cubic foot 4-door model with an exclusive automatic open/close bottom freezer drawer option. At the press of a button, the drawers automatically open, making it easier for consumers to access food or unload groceries. The drawer also has an assisted open/close function that allows consumers to slightly push or pull on the drawer to experience the automatic open/close feature.</li><li>Gas Cooktops (models LCG3091ST and LCG3691ST): LG's new high-performance gas cooktops, available in 30- and 36-inch sizes, offer professional-grade features, such as a range in power from 5,000 to 19,000 BTUs, and feature professional-grade knobs and three heavy-duty continuous grates, covering each of the five burners for a professional look. Its contemporary stainless steel styling package includes LED lights that indicate when a burner is in use or may still be hot to the touch. Controls are placed at the front of the cooktop for easier access when the cooktop is in use.</li><li>Slide-In Electric Range (model LSE3094): LG's first slide-in electric range features elegant styling with its stainless steel frames and its rich set of features, such as its 5.4 cubic foot capacity - among the largest available in the slide-in category, and separate baking drawer. Dual convection fans with three different settings evenly disperse heat throughout the oven for faster pre-heating and more uniform baking, and a gliding and rotating rack provides added convenience for home chefs.</li><li>Steam Dishwasher (model LDF9932ST): LG's newest Steam Dishwasher offers sleek styling with its stainless steel finish and LCD display integrated into the top of the door. Features include the highly efficient TrueSteam(TM) technology, which offers the option of adding steam to any wash cycle and a SenseClean(TM) washing system that automatically measures the turbidity of the water and adjusts wash time during the first rinse in order to optimize water usage. Consumers will also enjoy energy-saving features such as a half-load cleaning option and a hybrid condensing drying system, which allows for faster drying, reduced spotting and superior energy efficiency.</li><li>Graphite Steel Laundry System with Washing Motions (models WM2701HV and DLEX2701/DLGX2702): Until now, washers only used one cleansing motion - tumbling - to clean clothes. LG's new motion technology introduces four "dance-like" washing motions - rolling, stepping, swinging and scrubbing - using LG's Direct Drive motor to increase efficiency and reduce noise and vibration. These innovative washing motions care for clothes while saving time, water and energy. LG's TrueBalance(TM) anti-vibration system also helps offset unbalanced loads in the washer drum, allowing for quieter overall operation.</li><li>LED Steam Laundry Pair in Riviera Blue Designer Finish (model WM2801 and DLEX2801/DLGX2802): One of the largest capacity front-load steam laundry pairs in the industry, at 4.5 cubic feet, with a new eye-pleasing LED display, the Riviera Blue with LED is the latest version of the Steam Laundry Pair. It comes with a LED display, which takes the guesswork out of cycle selection, LG's TrueSteam(TM) technology, including the exclusive Allergiene(TM) cycle, designed to reduce common allergens such as dust mites and pet dander on fabrics, and like all LG steam laundry systems, it is Energy-Star rated.</li></ul>

<p><br />
For additional information and images on all of LG's product offerings, please visit: <a target="_blank" href="http://www.pimsmultimedia.com/LGSLS2009">www.pimsmultimedia.com/LGSLS2009</a></p>

<p><br />
<strong>About LG Electronics USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances and business solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.LGusa.com/">www.LGusa.com</a>.</p>

<p>  * 55LHX 55-inch class/54.6-inch diagonal<br />
  * 55LH90 55-inch class/54.6-inch diagonal<br />
  * 47LH90 47-inch class/47.0-inch diagonal<br />
  * 42LH90 42-inch class/42.0-inch diagonal<br />
  * 47LH50 47-inch class/47.0-inch diagonal<br />
  * 42LH50 42-inch class/42.0-inch diagonal<br />
  * 60PS80 60-inch class/59.5-inch diagonal<br />
  * 50PS80 50-inch class/50.0-inch diagonal</p>

<p>  ** Internet connection and subscriptions required and sold separately.</p>

<p>Source: LG Electronics USA, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 30, 2009  7:41 PM</b>
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
			<?=getComments(2925)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 2925)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/07/lg-electronics-showcases-new-products-with-unparalleled-performance-by-design-for-holiday-season.php" type="text/javascript" charset="utf-8"></script>
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