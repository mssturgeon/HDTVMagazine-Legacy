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
		AND e.entry_id = 5084";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ken Werner" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5084 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ken Werner'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ken Werner" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5084 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5084";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-digital-signage-expo-grows-in-size-and-energy.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5084";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Digital Signage Expo Grows in Size and Energy" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Digital Signage Expo Grows in Size and Energy" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Digital Signage Expo Grows in Size and Energy" />
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
	<title>HDTV Magazine - HDTV Expert - Digital Signage Expo Grows in Size and Energy</title>
	<meta name="keywords" content="ken werner, photo ken, digital signage, gorilla glass, show floor, display, signage, displays, inch, lcd, sign, signs, ken, christie, large, showed, werner, photo, panel, digital, year, transparent, kiosk, glass, curved" />
	<meta name="description" content="Digital Signage Expo (DSE), running from February 26 to 28 this year in Las Vegas, had 22% more exhibitors this year than last, but the growth felt larger than that and the high energy level was palpable. Exhibitors I questioned were pleased with their booth traffic, with some &amp;amp;#8220;complaining&amp;amp;#8221; of being too busy. One exhibitor [...]" />
	<meta name="title" content="HDTV Expert - Digital Signage Expo Grows in Size and Energy" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Digital Signage Expo Grows in Size and Energy" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-digital-signage-expo-grows-in-size-and-energy.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Digital Signage Expo (DSE), running from February 26 to 28 this year in Las Vegas, had 22% more exhibitors this year than last, but the growth felt larger than that and the high energy level was palpable. Exhibitors I questioned were pleased with their booth traffic, with some &amp;amp;#8220;complaining&amp;amp;#8221; of being too busy. One exhibitor [...]" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5084', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-digital-signage-expo-grows-in-size-and-energy.php">HDTV Expert - Digital Signage Expo Grows in Size and Energy</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ken Werner</b> on <b>March  4, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=333&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>, <b><a href="/category.php?id=451&category=Environment / Green">Environment / Green</a></b>
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
				<p>Digital Signage Expo (DSE), running from February 26 to 28 this year in Las Vegas, had 22% more exhibitors this year than last, but the growth felt larger than that and the high energy level was palpable. Exhibitors I questioned were pleased with their booth traffic, with some &#8220;complaining&#8221; of being too busy. One exhibitor said he didn&#8217;t have enough staff to handle the flow. He had six people.</p>
<div id="attachment_2924" class="wp-caption alignleft" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=2924" rel="attachment wp-att-2924"><img class="size-full wp-image-2924 " alt="Gorilla cropped lorres" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Gorilla-cropped-lorres.jpg" width="300" height="212" /></a><p class="wp-caption-text">Corning was promoting its Gorilla Glass in large sizes to protect signs located in demanding environments. The displays are Christie 55-inch LCDs with Gorilla Glass directly bonded to the display. (Photo: Ken Werner)</p></div>
<p>Peter Bocko of Corning Glass was promoting the benefits of Gorilla Glass in large sizes for digital signs. Bocko said that the signage community has the imagination, energy and resources to try new applications and new approaches, while makers of displays for monitors and TVs are too strapped for cash to boldly go where no display has gone before. (Note: I am freely paraphrasing Bocko&#8217;s remarks.)</p>
<p>Conventional display people have trouble getting used to the idea that successful digital signage installations are not primarily about the displays. Media creation and delivery, and distribution networks receive more attention. Network software must allow managers to schedule and conveniently reschedule what ads and other media appear on which sign at what time, and must verify that the ads actually appear on the screens they are scheduled to appear on.</p>
<p>Interactive signage, which senses that a person is actually viewing the sign when an ad appears, is already technically well developed, although not yet widely deployed. More advanced versions sense the age and gender of the viewer, opening the possibility of the network operator only getting paid for an exposure when the viewer fits the advertiser&#8217;s target demographic. Intel is a major technology developer in this area. There are also digital signs that are interactive in the more conventional sense of touch and gesture interaction.</p>
<p>Still, there is no digital signage without the sign, and the signage divisions of most of the major panel makers had major presences on the show floor. These included Samsung, LG, Sharp, and Panasonic. Significant players who do not make their own panels include NEC, Sony, Viewsonic, DynaScan, Planar, Mitsubishi, BrightSign, StrataCache, 3M, Philips, and Christie. Yes, that Christie. The projection Christie.</p>
<p>Although Christie was not at all bashful about promoting its DLP/LED rear-projection MicroTiles, the bulk of the booth was devoted to flat-panel solutions. Christie is espousing the approach that they are here to serve their customers with whatever technology is most appropriate, and they are stressing a vertical approach that includes sign manufacturing, installation, and network operation, as is appropriate for each  customer. Somebody at Christie has been listening to Corning&#8217;s Peter Bocko. A 55-inch Christie LCD sign was prominently identified as being protected with directly bonded Gorilla Glass, and Gorilla-ized Christie LCDs were prominently displayed in Corning&#8217;s booth.</p>
<div class="wp-caption alignright" style="width: 290px"><a href="http://www.hdtvexpert.com/?attachment_id=2930" rel="attachment wp-att-2930"><img alt="Nanolumen's right-angle LED sign was an attention-getter.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/nanolumen-lores.jpg" width="280" height="531" /></a><p class="wp-caption-text">Nanolumen&#8217;s right-angle LED sign was an attention-getter. (Photo: Ken Werner)</p></div>
<p>Although LCD signs dominated, there was a significant scattering of LED signs on the floor. Nanolumens had a large booth to show off various signs using the company’s flexible LED technology, but the most striking of their signs consisted of two vertical surfaces intersecting at 90 degrees, like the corner of a square post. The images of models wrapping around the corner were strange, and therefore attention-getting.</p>
<p><span style="font-size: 13px; line-height: 19px;">At Sharp, I spoke with Gary Bailer, the director of product planning and marketing for Pro AV products, which includes both flat-panel signage and projection. He said there is no doubt that larger flat panels are making serious inroads on the projector business in the broad mid-range of image sizes. Inexpensive projectors for elementary education are holding their own, as are powerful projectors for very large images, but the value proposition posed by ever-less-expensive large flat panels is proving impossible for competing projectors to resist.</span></p>
<p>Panasonic showed an impressive 85-inch plasma touch screen with sophisticated communication capabilities for distance collaboration. A line or signature drawn on the screen tracked the stylus precisely and without lag.</p>
<div id="attachment_2932" class="wp-caption alignleft" style="width: 410px"><a href="http://www.hdtvexpert.com/?attachment_id=2932" rel="attachment wp-att-2932"><img class=" wp-image-2932 " alt="Panasonic lo-res" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Panasonic-lo-res.jpg" width="400" height="236" /></a><p class="wp-caption-text">Panasonic&#8217;s 85-inch plasma &#8220;white&#8221; board with sophisticated communications capabilities and SLOT2.0 for convenient embedding of PC functions. (Photo: Ken Werner)</p></div>
<div id="attachment_2934" class="wp-caption alignleft" style="width: 410px"><a href="http://www.hdtvexpert.com/?attachment_id=2934" rel="attachment wp-att-2934"><img class="size-full wp-image-2934 " alt="E Ink lores" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/E-Ink-lores.jpg" width="400" height="265" /></a><p class="wp-caption-text">E Ink was energetically promoting passive electrophoretic displays for sunlight-readable, very-low-power signage applications. And the company really was serving coffee to DSE attendees. (Photo: Ken Werner)</p></div>
<p>Also among the exhibitors showing non-LCD signs was E Ink, with a variety of tiled, very-low-power, monochrome examples. E Ink customer Toppan was presented with a Product of the Year Award from Signage Solutions Magazine for an updated version of its Machikomi (&#8220;city communication&#8221;) E Ink signs in the Sendai subway system that survived the March 2011 tsunami, and provided one of the few ways that emergency information could be communicated to the population following the disaster.</p>
<p>Several companies were applying 4K panels to the demanding signage environment. LG showed a 4K 84-inch touch sign, which it labeled the world&#8217;s first. ViewSonic also showed a 4K 84-inch interactive sign based on AUO, not LG, glass. ViewSonic&#8217;s Gene Ornstead said the company is receiving interest from the DoD for interactive mapping apps in command and control centers.</p>
<div id="attachment_2936" class="wp-caption aligncenter" style="width: 410px"><a href="http://www.hdtvexpert.com/?attachment_id=2936" rel="attachment wp-att-2936"><img class="size-full wp-image-2936 " alt="Sharp90 lores" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Sharp90-lores.jpg" width="400" height="830" /></a><p class="wp-caption-text">Sharp&#8217;s Gen 10 fab, once an albatross around the company&#8217;s neck, now gives the company a big advantage in big LCDs for signage and television. This is the 90-inch, and Sharp wasn&#8217;t being modest about its size. (Photo: Ken Werner)</p></div>
<p>Sharp showed the impressive 4K 32-inch with IGZO backplane it has been showing at least since last year&#8217;s SID show. Sharp also featured its very large LCD panels &#8212; up to 90 inches &#8212; engineered into displays for the signage market.</p>
<div id="attachment_2937" class="wp-caption alignleft" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=2937" rel="attachment wp-att-2937"><img class="size-full wp-image-2937 " alt="BSI curved resized" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/BSI-curved-resized.jpg" width="300" height="582" /></a><p class="wp-caption-text">BSI&#8217;s 21.5-inch resized and curved LCD. The LCD starts out as a standard panel from LG Display, and is then cut down to a custom size, heated, and curved by Tovis of Incheon, Korea. BSI makes the curved panel into a complete sign. (Photo: Ken Werner)</p></div>
<p>Resized or bar-type displays &#8211; displays that are cut down to a custom size from standard-size panels &#8211;  were scattered over the show floor, including at the booths of Viewsonic, NEC, and G-Vision, among others.</p>
<p>The most interesting, though,  was from Bi-Search International (BSI), which showed a 26-inch LCD that was not only resized to 1366&#215;384 pixels, but was also curved!  Lots of interest from beverage companies, said BSI Account Sales Manager Jason Lee.  The LCD panel came from LG Display, and was then resized by Tovis (Korea) under the Tannas patent.  (Tannas Electronic Displays, which was also exhibiting, distributed a press release announcing that Tannas&#8217;s lawyers had filed a new complaint against Luxell Technologies for breach of a previous agreement that had settled a patent infringement suit.)  After resizing the LGD display, Tovis heated the LCD prior to bending it and placing it in its curved bezel, said Tovis&#8217;s In Ho Cho.  BSI also showed a 47-inch half-cut and curved display with 1920&#215;480 pixels.  (Disclosure:  Tannas Electronic Displays is one of the author&#8217;s clients.)</p>
<p><span style="font-size: 13px; line-height: 19px;">Finally (at least as far as this column is concerned), transparent displays are very much alive. Planar showed the very intelligently designed refrigerator door it introduced last year, but it is not yet on the market. MRI was promoting its large transparent refrigerator doors with resized displays that fill the entire door, but &#8212; unlike last year &#8212; did not have a unit on display. Stratacache did have its transparent-display refrigerator door on display. Smaller retail-window and showcase solutions could also be seen over the show floor.  </span></p>
<p>Perhaps most interesting in this segment was a Best Buy kiosk/vending machine for electronic gadgets. The kiosk uses a large, transparent LG panel that showed subjectively good color gamut. Bill Beaton, Senior Director of Product Marketing for ZoomSystems, which manages Best Buy’s 200-kiosk network, said the first of the Kiosks with transparent displays would be deployed in the next couple of months. If the new kiosk shows sufficiently increased sell-through compared to the conventional kiosk it replaces, Beaton is hopeful that more of the transparent-display units will follow.</p>
<p><span style="font-size: 13px; line-height: 19px;">Although digital signs seem ubiquitous, penetration is still quite low and the current double-digit annual growth can be sustained for years to come. That energy and optimism was reflected in this year&#8217;s DSE.</span></p>
<p><em>Ken Werner is Principal of Nutmeg Consultants, specializing in the display industry, display manufacturing, display technology, and display applications. You can reach him at ken@hdtvexpert.com.</em></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div id="attachment_2939" class="wp-caption aligncenter" style="width: 410px"><a href="http://www.hdtvexpert.com/?attachment_id=2939" rel="attachment wp-att-2939"><img class="size-full wp-image-2939 " alt="Best Buy lores" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Best-Buy-lores.jpg" width="400" height="604" /></a><p class="wp-caption-text">Best Buy will field-test this kiosk/vending machine with transparent LCD in the door to see if it produces better sell-through than its 200 conventional kiosks. (Photo: Ken Werner)</p></div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ken Werner</b>, <b>March  4, 2013  7:39 PM</b>
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
			<?=getComments(5084)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Ken Werner', 5084)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ken Werner</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-digital-signage-expo-grows-in-size-and-energy.php" type="text/javascript" charset="utf-8"></script>
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