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
		AND e.entry_id = 3414";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3414 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3414 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3414";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3414";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2010 International Consumers Electronics Show (CES) - New York Press Preview" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2010 International Consumers Electronics Show (CES) - New York Press Preview" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2010 International Consumers Electronics Show (CES) - New York Press Preview" />
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
	<title>HDTV Magazine - 2010 International Consumers Electronics Show (CES) - New York Press Preview</title>
	<meta name="keywords" content="blu ray, new york, actual ces, ces show, took place, ces, new, show, hdtv, press, products, expect, content, cea, standard, player, electronics, million, jvc, ”, projector, article, blu, ray, technology" />
	<meta name="description" content="This event by the Consumers Electronics Association was held on November 10 in New York City to show to the press a preview of the new products and planned events that will take place at the 2010 International CES in Las Vegas on January 7- 10, 2010.

Steve Koenig, CEA’s Director of Industry Analysis, and Shawn DuBravac, CFA, CEA’s Chief Economist and Director of Research disclosed their analysis of the holiday outlook for consumer electronics sales and technology trends for CES 2010, introduced some new hot products, and announced the best of Innovation Honorees, mentioned in a link below. Several exhibitors with tabletop displays introduced new products in advance of their official debuts at the actual CES in January.

The expectation for the holidays is..." />
	<meta name="title" content="2010 International Consumers Electronics Show (CES) - New York Press Preview" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2010 International Consumers Electronics Show (CES) - New York Press Preview" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This event by the Consumers Electronics Association was held on November 10 in New York City to show to the press a preview of the new products and planned events that will take place at the 2010 International CES in Las Vegas on January 7- 10, 2010.

Steve Koenig, CEA’s Director of Industry Analysis, and Shawn DuBravac, CFA, CEA’s Chief Economist and Director of Research disclosed their analysis of the holiday outlook for consumer electronics sales and technology trends for CES 2010, introduced some new hot products, and announced the best of Innovation Honorees, mentioned in a link below. Several exhibitors with tabletop displays introduced new products in advance of their official debuts at the actual CES in January.

The expectation for the holidays is..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3414', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php">2010 International Consumers Electronics Show (CES) - New York Press Preview</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December  8, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p>This event by the Consumers Electronics Association was held on November 10 in New York City to show to the press a preview of the new products and planned events that will take place at the 2010 International CES in Las Vegas on January 7- 10, 2010. <p>Although most of the products shown at this show were not relevant to HDTV, it was interesting to see that some excellent products that were relevant, such as the LCoS JVC projector (top of the line model DLA-HD990, $10K MSRP), got an Innovation Honoree award. <p>Steve Koenig, CEA’s Director of Industry Analysis, and Shawn DuBravac, CFA, CEA’s Chief Economist and Director of Research disclosed their analysis of the holiday outlook for consumer electronics sales and technology trends for CES 2010, introduced some new hot products, and announced the best of Innovation Honorees, mentioned in a link below. Several exhibitors with tabletop displays introduced new products in advance of their official debuts at the actual CES in January. <p>The expectation for the holidays is that based on the pooled consumers (n=344 US adults) they will cut back in expenditures in 2009 compared to 2008 under the reason of earning less money, with a -3% change of total holiday dollar spending compared to 2008. 60% of CE insiders believe that the # 1 challenge is “<i>People not wanting to spend as much as they have in the past”</i> in electronics retailers this holiday season, while the 2<sup>nd</sup> biggest challenge is “<i>Less people coming into the store.</i>” <p>Based on a random national sample of 1,002 U.S. adults: A +8% of allocation of money has been projected for 2009 CE gifts compared to 2008. The notebook/laptop PC item still is the # 1 product on the “CE gift wish list for adults” (same as 2008). The TV was 2<sup>nd</sup> on the list in 2008 but the portable MP3/digital media player took its place in 2009, and a flat panel TVs is 3<sup>rd</sup>in 2009. Kindle/E-reader, iPhone, and Blu-Ray players are new in the “CE gift wish list for adults” in 2009, appearing as sixth, seventh, and eight places. <p>Bundling of products is growing for the 2009 holiday such as TVs bundled with a home theater system or a Blu-ray player. A # 1 trend to watch at CES 2010 is “<i>Beyond HD: Tomorrow’s TV Experience Connected displays deliver a new interactive environment with access to content, movies, music, widgets and more. 3-D TV goes mainstream.”</i> according to the CEA. <p>Over 4 million 3D TVs were forecasted to be shipped in 2010, increasing 1 million per year on the forecast up to 2013 (with 7+ million units), while 2009 registered 2+ million, and 2008 0.5 million. Seventeen percent of adults reported to have seen 3D at the theaters in the last 12 months. <p>CEA said, “The first CES took place in New York City in June of 1967, with 250 exhibitors and 17,500 attendees. Since then, the International CES has grown more than eight-fold.” CEA added at the show, “The 2010 CES continues to gain momentum, with strong sales and a record number of more than 330 new exhibitors. We are updating our projections for the 2010 show based on momentum in exhibit sales and pre-registration numbers. We expect the 2010 CES to draw more than 110,000 attendees from around the world and to feature more than 2,500 exhibitors.” <p><u>Product Debut at CES (source CEA)</u> <ul> <li>1970 Videocassette Recorder (VCR)  <li>1974 Laserdisc Player  <li>1981 Camcorder  <li>1981 Compact Disc Player  <li>1990 Digital Audio Technology  <li>1991 Compact Disc - Interactive  <li>1994 Digital Satellite System (DSS)  <li>1996 Digital Versatile Disc (DVD)  <li>1998 High Definition Television (HDTV)  <li>1999 Hard-disc VCR (PVR)  <li>2000 Satellite Radio  <li>2001 Microsoft Xbox  <li>2001 Plasma TV  <li>2002 Home Media Server  <li>2003 Blu-ray DVD  <li>2003 HDTV DVR  <li>2004 HD Radio  <li>2005 IPTV  <li>2007 New convergence of content and technology 2008 OLED TV  <li>2009 3D HDTV </li></ul> <p>This Pre-show event in NY of the actual 2010 CES in Vegas was a 22hr long day for me. The train from DC relaxed the effort and facilitated time for writing. When I was drafting this article in the train back to DC, I was also drafting four other articles that were published before this one. A few days later, I returned to this draft and noticed that other publications covered this event quite well already. Therefore, I decided to redraft, offer the links to those sources, and rather complete this article with my view about the interesting things I expect to see at CES 2010 regarding HD and 3D, not mentioned in detail on other publications.  <p>Some of the articles that covered the CES 2010 press show are as follows: <p><a href="http://www.displaydaily.com/">http://www.displaydaily.com/</a> <p><a href="http://www.dealerscope.com/article/a-roundup-news-announced-ces-press-preview-event-new-york-tuesday/1?sponsor=newsletter/today">http://www.dealerscope.com/article/a-roundup-news-announced-ces-press-preview-event-new-york-tuesday/1?sponsor=newsletter/today</a> <p><a href="http://cesweb.org/news/upToTheMinute/111109.asp?edm=uttm111009#3530">http://cesweb.org/news/upToTheMinute/111109.asp?edm=uttm111009#3530</a> <p><a href="http://www.twice.com/article/388448-CEA_Unveils_Best_Of_Innovations_Award_Winners.php?nid=2402&amp;source=link&amp;rid=5380669">http://www.twice.com/article/388448-CEA_Unveils_Best_Of_Innovations_Award_Winners.php?nid=2402&amp;source=link&amp;rid=5380669</a> <p><a href="http://www.twice.com/article/388428-CEA_Highlights_CES_10_Features_Changes.php?nid=2402&amp;source=title&amp;rid=5380669">http://www.twice.com/article/388428-CEA_Highlights_CES_10_Features_Changes.php?nid=2402&amp;source=title&amp;rid=5380669</a> <p><a href="http://www.dealerscope.com/slideshow/highlights-from-ces-unveiled?sponsor=newsletter/today#0">http://www.dealerscope.com/slideshow/highlights-from-ces-unveiled?sponsor=newsletter/today#0</a> <p>CES Innovations Honorees: <p><a href="http://www.cesweb.org/awards/innovations/2010honorees.asp?category=931350">http://www.cesweb.org/awards/innovations/2010honorees.asp?category=931350</a> <p>Although the actual CES show is from the seventh to the 10<sup>th</sup>of January, the <a href="http://www.cesweb.org/press/events/default.asp">fifth and the sixth of January are reserved for pre-show events for the press</a>, which I usually attend as well. The sixth is a day when many important companies such a Panasonic, LG, Sharp, Pioneer, etc. each offer a consolidated hour to the press to unveil the products they will introduce during the following four days when CES opens. Although it is a busy full day for the press with back-to-back meetings, the primer usually helps me to be more efficient at the booths and meetings during the rest of the show. <p>As I mentioned above, the press pre-CES show of November 10 announced the top-of-the-line JVC projector to receive the Innovations Honoree award in the video products group, here are some details on the projector: <p><a href="http://admin.virtualpressoffice.com/Presenter?urlId=1&amp;deliveryid=1258031094453">http://admin.virtualpressoffice.com/Presenter?urlId=1&amp;deliveryid=1258031094453</a> <p>Company representatives at the show said JVC would also demo their 4K projector at CES in tandem with another 4K projector for a 3D presentation. JVC declared no plans for 3D 1080p consumer projectors, a statement issued also at the recent <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">HD World Conference</a> I attended on October 15 in NY City. Perhaps CES 2010 will give a surprise announcement.  <p>Although neither Panasonic nor Sony made announcements at this press-pre-CES show, I expect that Sony will demo their 4K projectors, as they did before. Many LCD/plasma panel manufacturers are also expected to have their 3D demos with stereoscopic passive glasses such as JVC and Hyundai, with active shutter glasses such as Panasonic and several others, and with auto-stereoscopic capabilities with no glasses. I hope that I may be able to be lucky enough to hold the correct viewer sweet spot among the large CES crowd trying to do the same long enough so I can analyze the picture, a sweet spot that is usually required by that technology to obtain the full 3D effect.  <p>In other words, I expect that this CES could be similar to what 1998/9 was for HDTV when I purchased my first HDTV, but now for 3D HD. Considering that a broadly-adopted standard has not been established yet, I expect more confusion about the introduction of 3D than when HDTV was introduced in 1998, which had the ATSC standard of 1995 as a base.  A 3D standard for media, distribution and display is needed before 3D consumer electronics equipment and content are introduced in volume to the public.<p>I hope that the standard expected by the end of this year will soon enough align manufacturers, content distributors, and content creators, so consumers will not have to suffer another format war struggle, and pay for a wrong choice, again. <p>This time a format war in 3D may mean much more that choosing the correct player or selecting the correct 3D media/service provider. This time adopting wrong too early could be as bad as paying high dollar for a 3D-HDTV that implements <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">a 3D display standard</a> with limited 3D capabilities.  <p>Considering that the standard completion for 3D Blu-ray is so imminent, I expect that Panasonic would introduce a ready-for-retail 3D Blu-ray player with full 1080p dual HD images together with their new 3D plasma panel, as promised to be available in 2010. <p>I also expect to see demos of 8K and 16K by some manufacturers. Some market research companies recently estimated that millions of households would embrace the Ultra-HDTV format within the next few years, with a rather aggressive adoption over the next ten years. In my opinion, we just came out of the DTV transition in June 2009, about 50% of households are estimated to have HDTV, and the industry expects consumers to switch again to another technology, including 3D, when their new HDTVs are still smelling of brand new electronics in their homes?  <p>I estimate exactly 15,356,798 homes in 5 years with 16K 3D and a growth of 53.73% by 2025, if you know what I mean. What a crystal ball some people have indeed. My position is that it is too early to issue such defined projections when we are just getting a handle on HDTV after a recent DTV transition that “motivated” many people to invest in a new DTV, and many did even when not needing to replace their perfectly functional analog televisions.  <p>It will be interesting to witness any 3D announcements from the content distribution providers, such as satellite, cable, FiOS, Internet, and broadcast, as well as their short-term plans for 3D content, set-top-boxes, etc., and <a href="http://www.hdtvmagazine.com/articles/2009/10/hd_world_conference_in_ny_3d_ip_online_video_and_mobile_dtv.php">using which transmission methods and image resolution for stereoscopic 3D HDTV to the home. </a> <p>We shall meet again soon, hopefully right after CES 2010 a few weeks from now. Stay tuned.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December  8, 2009  9:22 AM</b>
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
			<?=getComments(3414)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3414)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/12/2010-international-consumers-electronics-show-ces-new-york-press-preview.php" type="text/javascript" charset="utf-8"></script>
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