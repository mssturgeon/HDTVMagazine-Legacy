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
		AND e.entry_id = 4912";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4912 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4912 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4912";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2012/10/review-dish-network-hopper.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4912";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Review: Dish Network Hopper" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Review: Dish Network Hopper" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Review: Dish Network Hopper" />
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
	<title>HDTV Magazine - Review: Dish Network Hopper</title>
	<meta name="keywords" content="image quality, hopper system, cable service, sap hbo, sap showtime, hopper, image, quality, sap, cable, dish, may, satellite, channels, service, darblet, movie, content, system, encore, better, resolution, dvr, hdmi, showtime" />
	<meta name="description" content="After reading the rosy articles from other reviewers about the Hopper system this may come as a splash of cold water to Dish fans, or as a “yes, we know, it has been always that way” to some satellite service history followers, why?

I reviewed primarily the image quality side of the system, as I would do with other HD video provider service or media, and I concluded that it should be better, and it could be if Dish wants.

None of the other publications said a word about the subject of Dish’s image quality, they rather concentrated in admiring the Hopper’s impressive feature set and functionality, which, do not take me wrong, I fully concur with the reviews in that area, the features are great, but to me the image quality should be the main requirement of any video service contract/package, not just a sophisticated set-top-box or a mile-long channel lineup." />
	<meta name="title" content="Review: Dish Network Hopper" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Review: Dish Network Hopper" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2012/10/review-dish-network-hopper.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="After reading the rosy articles from other reviewers about the Hopper system this may come as a splash of cold water to Dish fans, or as a “yes, we know, it has been always that way” to some satellite service history followers, why?

I reviewed primarily the image quality side of the system, as I would do with other HD video provider service or media, and I concluded that it should be better, and it could be if Dish wants.

None of the other publications said a word about the subject of Dish’s image quality, they rather concentrated in admiring the Hopper’s impressive feature set and functionality, which, do not take me wrong, I fully concur with the reviews in that area, the features are great, but to me the image quality should be the main requirement of any video service contract/package, not just a sophisticated set-top-box or a mile-long channel lineup." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4912', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2012/10/review-dish-network-hopper.php">Review: Dish Network Hopper</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October  2, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=530&category=HDTV Tuners (Satellite)">HDTV Tuners (Satellite)</a></b>
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
				<p><b>Overall: Impressive System - Image Quality could be better (and the Darblet can help)</b> <p>After reading the rosy articles from other reviewers about the <a href="http://www.dish.com/technology/hopper/">Hopper system</a> this may come as a splash of cold water to Dish fans, or as a “<i>yes, we know, it has been always that way</i>” to some satellite service history followers, why? <p>I reviewed primarily the image quality side of the system, as I would do with other HD video provider service or media, and I concluded that it should be better, and it could be if Dish wants. <p><img title="" alt="" src="http://www.hdtvmagazine.us/articles/images/6a2bb23af2fd_12CD1/clip_image002_570b2d9e-ac09-485d-8a42-0bd94ded68f0.jpg" width="436" height="303">None of the other publications said a word about the subject of Dish’s image quality, they rather concentrated in admiring the Hopper’s impressive feature set and functionality, which, do not take me wrong, I fully concur with the reviews in that area, the features are great, but to me the image quality should be the main requirement of any video service contract/package, not just a sophisticated set-top-box or a mile-long channel lineup. <p>And that is when I compared it against my cable service, not to mention if I would have compared it with the typically better over-the-air image (using the full 19.4 Mbps as a single HD channel in the dedicated 6MHz slot, indeed), or even with Blu-ray, which may be considered unfair from the point of view of its higher Mbps, but it’s video quality is not the moving target seen in the broad variety of cable services around US, which may be the reason why some disappointed cable customers may consider satellite service a better choice no matter what. <p> <p><b>The Testing Objective </b> <p>I have been admiring the functionality and features of the Hopper system since it was announced first at CES 2012, and, as I intended originally (and I made Dish aware of the objective) I was interested in a review of the Hopper based on A/B image comparisons with other services, and, if the image quality was acceptable enough, I was even considering discontinuing my 5 HD-DVR cable service and adopt the feature packed Hopper system for my whole house (details at the end). <p> <p><b>The Testing Environment</b> <p>Dish installed one Hopper network DVR, one Joey client (of the Hopper), and a satellite dish using my own home as testing environment. The Hopper and Joey were installed in a parallel loop to my cable feed to allow me to perform almost instant comparisons of the same content using the same displays, wiring, HDMI switching, etc.&nbsp; <p>I installed the Hopper in my home theater and the Joey in a guest bedroom. In my home theater I connected the Hopper and the cable Cisco HD-DVR first thru a top-of-the-line Integra DHC-80.3 preamp processor I was testing, and later thru my Theta Digital Casablanca III HD pre/pro. I used the two preamps/processors at different times to verify that their HDMI pass-thru switching function was not affecting the signal quality of the Hopper. <p>Although the two pre/pros are in two different leagues of audio quality (a 10-fold cost difference to be exact, but not a 10- fold audio quality difference, if anyone can measure the incremental audio quality of a $25,000 pre/pro in numeric terms vs. a $2,600 pre-pro), their role for this testing was not relevant to audio but rather for them be as video-unobtrusive as possible when performing the fast pass-thru HD switching for the testing, which they did consistently well. The short delay of the typical HDMI/HDCP hand-shaking did not detract from the effectiveness of the comparisons in most cases.  <p>The alternative was to run another 30-feet high-quality HDMI cable to the Sony 4K projector I used for the test in my HT, and calibrate two of its inputs to the same specs, one for the Hopper another for the cable DVR, and do the switching tests at the projector. I preferred using the existing HDMI cable to the single input to the projector and rather use the pre/pros to do pass-thru HDMI switching between the cable DVR and the Hopper. <p><img title="" alt="" src="http://www.hdtvmagazine.us/articles/images/6a2bb23af2fd_12CD1/clip_image004_d72cb2a1-293a-44a3-8450-55f98a2c0380.jpg" width="629" height="270"> <p>I used a 10 feet wide CinemaScope Stewart Firehawk G3 1.3 gain screen to project 16:9 and 2.35:1 content, using the lens and aspect-ratio memories of the 4K projector, without adding anamorphic lens in the light path. <p>The second testing environment was made of the Joey (client of the Hopper DVR) connected to a 50-inch 1080p GT Panasonic plasma that did the HDMI switching between the cable DVR and the Joey with both inputs adjusted to the same THX settings. <p><b>The Subjective Testing</b> <p>My purpose was to subjectively compare perceived image quality to the best it can be done at home by most people under equal conditions and hardware environments. <p>I tested first the Hopper with the projector at 1080p and 4K to identify obvious image differences and then with the relatively smaller size plasma (50) to verify if consistent and similar image differences were also noticed on a panel many homes may have as main TV set.  <p>Upon starting I did not notice expected satellite pixelation errors or artifacts on the Hopper, although ironically the 101-learn-how-to channel of Dish, the channel a new Dish subscriber views first to be welcomed, showed pixelation and macro-blocking artifacts the minute I tuned to it (I thought to myself: quite a greeting to those new to the satellite world). <p>During the several weeks of testing I noticed softness on Dish's HD channels compared to the cable feed of the same content. Although cable services are typically not of excellent quality, my HD cable showed a relatively detailed image, perhaps at a level 7.5 on a scale where 10 would be Blu-ray video quality and 8.5 would be over-the-air HD broadcast. <p>Dish’s image softness was particularly noticeable when it was unable to exhibit the details of the cable feed, such as imperfections of skin in faces, wrinkles actors do not want to show, glow of eyes, levels of sweat and oily skin within the same face, individual threads of hair, especially gray “accents” of old people hair, texture on the fabrics, etc. <p>Whether the reason was exclusively due to the satellite feed, or the Hopper performance, or a combination of both, was not my primary concern to debug. My concern was evaluating the final image as perceived at the display device, the same image any subscriber should see at their own home with the same Dish equipment and service.  <p>The projection and the plasma viewings were done at the standard 3-times-picture-height viewing distance for HD. I viewed various channels and content types over several weeks, looking (and hoping) for a better outcome, maybe was the day or the channel, but concluded the same: Dish’s HD image was consistently softer and lacked the detail I see in my cable feed. <p>It is to be expected that a typical cable service could not be as good as over-the-air broadcast, and I saw no point in also using OTA as a comparison because it would have shown more marked differences in image quality with the Hopper. Neither I compared it to DirecTV but I suspect it would be similar, based on ten years of subscription, and I was interested in the Hopper system not DirecTV’s DVR. <p>While the image softness should be less noticeable in small screens or when viewing from farther than normal distances for HD, it would be noticeable in the large screens the market is increasingly introducing to consumers, and it may be easily detected if having my parallel setup for comparisons. <p>I asked Dish if there was something they may be able to do to improve the overall HD image quality considering the increasingly large panel market, and the response was: “<i>the quality is set. We’ve heard from several reviewers that the quality of our stream is better than their cable, but as you’re witness to, this is not always the case.&nbsp; The cable feed can vary by location. While the satellite feed is better in many areas, it is not better in all.”</i> <p> <p><b>A Hopper for me?</b> <p>Regardless of how good the Hopper system is as hardware and software, I would be slightly degrading the image quality I receive from my current cable service if I switch to Dish, but I may want to try to adapt to it (using the Darblet, further down) in order to get the great functionality of a Hopper system that is ideal for my home. Besides, other than me, my family does not easily notice subtle image differences even on their large plasmas. So the answer is probably yes, I believe I am getting a Hopper, and I still hope Dish improves image quality. <p><b>Satellite Service Memories</b> <p>Dish is not alone, since 1999 both satellite providers have been criticized for delivering a so called "<a href="http://en.wikipedia.org/wiki/HD_Lite">HD lite</a>" over-compressed image quality when taking bandwidth from a quality feed to add more channels on that space. <p>This is the eternal fight of quantity vs. quality that we are all subjected on most topics of human life. There will always be a mix of people that want hundreds of channels regardless of quality, and others that want quality in the content and/or the image but few channels. <p>Typically, over-compression /resolution limiting/bit-starving techniques make an image softer and prone to pixelation and macro-blocking when displaying fast sports content, fires, running water, rapid flashing/strobe lights, etc. <p>Since the beginning of satellite, Internet forums posted many HD comparisons by subscribers of Dish Network and DirecTV, typically complaining about similar image quality issues, especially when comparing them to broadcast HD over-the-air channels, available since November 1998, and later Blu-ray since late 2006. <p>The ideal would be that Dish’s image quality matches the impressive Hopper system features and functionality. It is unfortunate that the image quality of HD providers still is a mix and match between satellite, cable, and IPTV companies even in 2012. <p>Considering how terrible some cable companies are (judged by the complaints of their customers), most people should look forward to enjoy a Hopper. I should then consider myself lucky for receiving a good image quality from cable. <p>Would the image loss I perceived with the Hopper be like a DVD quality presentation of an HD feed? not that much, DVD is 1/6 the pixel count of HD per video frame, but, subjectively to my eyes, the image appeared to be about 80% of its “could be” quality, based on the missing detail perceived when comparing the two images. However, most viewers may never notice what exactly they are missing in the image and may accept the image quality as excellent for their screen/viewing situation.&nbsp; <p> <p><b>A Plan B to improve Perceived Image Quality from the Hopper: the Darblet</b> <p><img title="" alt="" src="http://www.hdtvmagazine.us/articles/images/6a2bb23af2fd_12CD1/clip_image006_038f34e7-bd9f-4a7e-add0-92e4caa00cec.gif" width="427" height="298">Among the equipment that I am currently reviewing I chose to connect a <a href="http://darbeevision.com/">Darblet from Darbee</a> to the Hopper, a device that claims to improve the perceived quality and depth of an image, with user adjustable settings, and a demo screen that shows the effects of its video processing in split/laterally rolling images. The Darblet claims to provide the appearance of 3D without glasses, but that is for another article. <p>I connected the Darblet to the Hopper output and dialed its video processing high at first but gradually reduced it to about 50% of its capability during the few hours I was viewing the Hopper with the Darblet. <p>I must admit that the Darblet did a noticeable improvement to the image quality, tested with just content no calibration patterns, not yet, making it close or even better than the quality of my cable service, not to mention the image quality improvement when I lifted the resolution of the image also with my 4K projector to scale up to 4K with its Reality Creation feature. <p>Did the Hopper with the Darblet set to 50% video processing show a comparable image than the Hopper upscaled to 4K by the projector (and no Darblet)? <p>It is not an even comparison, they do different things, the 4K upscale showed cleaner to my eyes but the Darblet alone showed a convincing improvement and a detailed 1080p image “if the settings were not exaggerated for the particular content”. <p>The 4K upscale of the projector helped further indeed, but those that do not have a 4K display would still notice a clear improvement on Dish’s image (or on any image) with just the Darblet. <p>I am not saying one “has” to buy the Darblet to enjoy the Hopper but the Darblet may as well be a life-saver plan B for those that “desperately want” the great functionality of the Hopper system and are concerned with the image quality of satellite services, Dish and otherwise, especially when the satellite provider does not admit the need for image quality improvement. So you can do it yourself at your home, although you may already know that although it improves the looks it does not restore the original resolution lost by an over-compressed satellite transmission.  <p>The Darblet can be connected to the HDMI output of an A/V receiver/pre/pro before going to the panel/projector to improve the image quality of any other device connected to the A/V receiver/pre/pro, which saves you by not having to install one Darblet to each source device you want to improve. I would probably start by installing the Darblet only to the Hopper. <p> <p><b>Hopper Functionality and Features</b> <p><a href="http://www.dish.com/technology/hopper/">The Hopper functionality</a> is very enticing as a whole-house system, such as starting the playback of a recorded movie in one room and continuing the viewing in another room, the Internet apps and control access, GUI, networking with small Joey clients (not requiring other DVRs), grouped recordings by user, TV anywhere technology, Internet and house content sharing features, up to 6 live HD recordings with 3 tuners (including the 4 live HD automatic Prime Time recordings), intuitive search, remote control locator, 2TB hard-drive for many hours of HD, the automatic (and very unique) simultaneous prime time recording of 4 networks with commercial-skipping during later playback, etc. <p>There are other DVR systems that have some of those features, Tivo comes to mind, and there are also attached fees that need to be considered in the overall cost comparison. On the “like-to-have features of the Hopper system” I have the following 3 suggestions:  <p><u>Component-analog connection</u> <p>The Joey only has an HDMI output for HD, perhaps Dish may consider carrying a few Joeys in inventory with a component-analog-output for customers that still have pre-HDMI HDTVs (about 11 million early adopters, the ones that paid ten-fold for the first generations of HDTVs and provided the revenue for further HDTV R&amp;D so we can all now enjoy HD at rock bottom prices). <p>The alternative: to purchase a +-$50 HDMI-to-component-analog converter box for each non-HDMI HDTV connected to a Joey, however, HD content that maybe protected with HDCP may be affected on its analog version (no image, lower resolution, etc.).  <p><u>ATSC tuner for over-the-air HD</u> <p>The Hopper does not have an over-the-air DTV tuner as old Dish (and DirecTV) set-top-boxes had. The tuner would have allowed subscribers to make image quality comparisons between the over-the-air image tuned by the Hopper (if the HDTV was not tuner-integrated) and Dish’s satellite retransmission of the same over-the-air channel, which is what I did with DirecTV set-top-boxes in 1999. I was able to witness the gradual deterioration of satellite image quality as more channels were added over the years (MPEG-2 back then). <p><u>“Native” setting for Output Resolution</u> <p>The Hopper does not have a pass-thru feature for the tuned content to be output at its original resolution to the TV. The “native” feature was present in satellite STBs since their beginning, but not now in the sophisticated Hopper. <p>By not having a pass-thru feature the user has to choose either a fixed 1080i/p or 720p for HD output in the Hopper to manually match the resolution accepted by the HD display, if there is only one you have no choice, but the TV set may accept more than one resolution and its transcoding quality may be better than the Hopper so it would be better to let the Hopper pass the content as is for the TV to do a better resolution conversion job (although the opposite maybe true as well).  One catch is that changing channels may take a bit longer when the TV has to perform the transcoding every time a channel is changed to another with different resolution, but that should be the choice of the viewer not the set-top-box. <p>This means that, if the Hopper is manually set to 1080i, a tuned 720p progressive content would have to be transcoded to interlaced 1080i60 by the Hopper (with the risk of adding artifacts in the format conversion) even if the display device could also accept 720p and display it as 1080p, maintaining the signal in progressive format for less artifacts, which a native setting would allow.&nbsp; <p>A native setting should be added to the resolution settings of the Hopper/Joeys. <p>A side note: Regarding my tests above, I took into account the transcoding/format conversion forced by the Hopper and manually handled the testing and the resolution settings in the hardware involved to match the resolution of the original content and maintain consistency among formats and conversions in the DVRs and the displays, so the tuned content did not have to go thru unnecessary conversions that may potentially affect the image quality comparisons. <p>Dish commented about the subject as follows: <i>“As far as a pass through setting, we list the 1080, 720 and 480 options for the general consumer to make it easier for them because so many do not understand the native setting. However, this setting may come in future updates, but have not made any announcements on this. As Hopper goes into more homes with high-end installations then the need for the native setting option grows.”</i> <p><b>Commercial- Auto-Skipping Feature</b> <p>Due to lack of time I could not test as I wanted the unique (and controversial) automatic commercial-auto-skipping feature when playing back network recordings after 1AM, although I tested the multiple network recordings at prime time.  <p>I am not a TV viewer, or should I said, “Not a viewer of heavily advertised low quality TV content” but I congratulate Dish for the commercial-skipping feature and I hope Dish and the Broadcasters could reconcile their positions and interests.  <p>Broadcasters have a valid point in that the investment for creating content needs commercials revenue, but the commercial-auto-skipping feature appears not to be much different than what viewers already do by pressing the fast-forward remote button on their DVR recordings to avoid seeing the same exact commercials broadcasters “think” people view when they market the idea to their advertisers. <p>When I visit back some existing audio/video installations it is not uncommon to notice the wear and tear of the fast-forward button on DVR remotes, some to the point of not clearly see the arrow logo on it, particularly the remotes operated by women (nails, no offense, my tennis player wife likes watching hours of tennis and does not even want to wait the 30 seconds between services).  <p>If people are so in love with the DVR fast-forward button why not making their viewing easier and let the great Hopper idea be?<b> </b> <p>Maybe it is time to think in other advertising methods rather than keeping viewers hostage for more decades, not to mention the interruptions to the momentum of a program plot, which is the main reason I do not watch TV content.  <p> <p><b>The Hopper may be a better Deal for Most</b> <p>My cable company bulk-billing cost almost 40% more than the Dish Hopper/service startup, primarily because the lease of my cable DVR is double the Hopper/Joey lease; and I have 5 HD DVRs. <p>My family views just a few channels of the hundreds available, and I am only a movie viewer of my collected Blu-rays in my theater due to my strict requirements for high audio/video quality, but Dish motivated me when I saw a premium HD movie package with 5 times the number of channels of my cable service, and for a lower cost ($35 for 23 HD Dish movie channels, rather than $41 for just 5 HD cable movie channels). <p>All the factors pointed in the direction of discontinuing my cable service and subscribe to Dish right away with my eyes closed. Although the slight difference in image quality affects me I still put the Hopper system high in my list of options and I probably switch to Dish, and that may be the case for most people interested on this feature loaded Hopper system. <p>Regardless of how bad certain cable companies may be regarding image quality/service in some areas of the US, I hope Dish works in improving the image quality for the premium movie package, especially now that 4K projectors and relatively large 4K UHDTV panels are beginning to appear in the market. <p>If you are interested, below is a comparison of HD movie packages between my cable service and Dish: <table border="1" cellspacing="0" style="border-collapse:collapse"><tbody><tr><td colspan="2"><b>Local Cable Premium/Movie Channels</b></td><td><b>Dish Movie Package HD</b></td><td><b>Dish American Top 250 package</b></td></tr>
<tr><td></td><td style="background-color:#FFFF00">HD in yellow</td><td style="background-color:#FFFF00"><b><i>HD in yellow/bold</i></b></td><td style="background-color:#FFFF00">HD in yellow</td></tr>
<tr><td>Action Max</td><td>754</td><td></td><td></td></tr>
<tr><td><b><i>Cinemax</i></b></td><td>750</td><td>310 SD sap</td><td></td></tr>
<tr><td><b><i>Cinemax (W)</i></b></td><td>751</td><td style="background-color:#FFFF00"><b><i>311 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>Cinemax HD</i></b></td><td style="background-color:#FFFF00">760</td><td style="background-color:#FFFF00"><b><i>310 HD sap</i></b></td><td></td></tr>
<tr><td><b><i>Encore</i></b></td><td>650</td><td style="background-color:#FFFF00"><b><i>340 HD/SD sap</i></b></td><td style="background-color:#FFFF00">340 HD only</td></tr>
<tr><td>Encore (W)</td><td>651</td><td></td><td>341 SD sap</td></tr>
<tr><td>Encore Action</td><td>652</td><td></td><td>343 SD</td></tr>
<tr><td>Encore Action (W)</td><td>653</td><td></td><td></td></tr>
<tr><td>Encore Drama</td><td>658</td><td></td><td>345 SD</td></tr>
<tr><td>Encore Drama (W)</td><td>659</td><td></td><td></td></tr>
<tr><td>Encore Love</td><td>654</td><td></td><td>346 SD</td></tr>
<tr><td>Encore Mystery</td><td>656</td><td></td><td></td></tr>
<tr><td>Encore WAM</td><td>662</td><td></td><td></td></tr>
<tr><td>Encore Western</td><td>660</td><td></td><td>342 SD</td></tr>
<tr><td>FLiX</td><td>311</td><td>333 SD</td><td></td></tr>
<tr><td><b><i>HBO Comedy</i></b></td><td>708</td><td style="background-color:#FFFF00"><b><i>307 HD/SD sap</i></b></td><td></td></tr>
<tr><td>HBO E</td><td>700</td><td>300 SD sap</td><td></td></tr>
<tr><td><b><i>HBO Family</i></b></td><td>706</td><td style="background-color:#FFFF00"><b><i>305 HD/SD sap</i></b></td><td></td></tr>
<tr><td>HBO Family (W)</td><td>707</td><td></td><td></td></tr>
<tr><td><b><i>HBO HD</i></b></td><td style="background-color:#FFFF00">710</td><td style="background-color:#FFFF00"><b><i>300 HD sap</i></b></td><td></td></tr>
<tr><td>HBO Plus</td><td>702</td><td></td><td></td></tr>
<tr><td><b><i>HBO Signature</i></b></td><td>704</td><td style="background-color:#FFFF00"><b><i>302 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>HBO W</i></b></td><td>701</td><td style="background-color:#FFFF00"><b><i>303 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>HBO Zone</i></b></td><td>709</td><td style="background-color:#FFFF00"><b><i>308 HD only</i></b></td><td></td></tr>
<tr><td>More Max</td><td>752</td><td></td><td></td></tr>
<tr><td>More Max (W)</td><td>753</td><td></td><td></td></tr>
<tr><td>Showtime</td><td>500</td><td></td><td></td></tr>
<tr><td>Showtime (W)</td><td>501</td><td>318 SD sap</td><td></td></tr>
<tr><td>Showtime Extr. (W)</td><td>503</td><td></td><td></td></tr>
<tr><td>Showtime Extreme</td><td>502</td><td>322 SD sap</td><td></td></tr>
<tr><td>Showtime Family</td><td>509</td><td></td><td></td></tr>
<tr><td><b><i>Showtime HD</i></b></td><td style="background-color:#FFFF00">515</td><td style="background-color:#FFFF00"><b><i>318 HD sap</i></b></td><td></td></tr>
<tr><td>Showtime Next</td><td>508</td><td></td><td></td></tr>
<tr><td><b><i>Showtime Show. (W)</i></b></td><td>507</td><td style="background-color:#FFFF00"><b><i>319 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>Showtime Showcase</i></b></td><td>506</td><td style="background-color:#FFFF00"><b><i>321 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>Showtime Too</i></b></td><td>504</td><td style="background-color:#FFFF00"><b><i>320 HD/SD sap</i></b></td><td></td></tr>
<tr><td>Showtime Women</td><td>510</td><td></td><td></td></tr>
<tr><td>Starz</td><td>600</td><td>350 SD sap</td><td></td></tr>
<tr><td>Starz Cinema(x?)</td><td>605</td><td>353 SD sap</td><td></td></tr>
<tr><td><b><i>Starz Edge</i></b></td><td>602</td><td style="background-color:#FFFF00"><b><i>352 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>Starz HD</i></b></td><td style="background-color:#FFFF00">610</td><td style="background-color:#FFFF00"><b><i>350 HD sap</i></b></td><td></td></tr>
<tr><td>Starz inBlack</td><td>604</td><td>365 SD sap</td><td></td></tr>
<tr><td><b><i>Starz Kids/Family</i></b></td><td>606</td><td style="background-color:#FFFF00"><b><i>356 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>Starz W</i></b></td><td>601</td><td style="background-color:#FFFF00"><b><i>351 HD/SD sap</i></b></td><td></td></tr>
<tr><td>Thriller Max</td><td>755</td><td></td><td></td></tr>
<tr><td>TMC</td><td>550</td><td>327 SD sap</td><td></td></tr>
<tr><td><b><i>TMC HD</i></b></td><td style="background-color:#FFFF00">555</td><td style="background-color:#FFFF00"><b><i>327 HD sap</i></b></td><td></td></tr>
<tr><td>TMC Xtra</td><td>552</td><td>328 SD sap</td><td></td></tr>
<tr><td>TMC Xtra (W)</td><td>553</td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td></tr>
<tr><td colspan="2"><b>Dish Only Movie Channels</b></td><td></td><td></td></tr>
<tr><td>Epix Drive In</td><td></td><td></td><td>292 SD sap</td></tr>
<tr><td>Fox Movie Channel</td><td></td><td></td><td>133 SD</td></tr>
<tr><td><b><i>HBO2 (E)</i></b></td><td></td><td style="background-color:#FFFF00"><b><i>301 HD/SD sap</i></b></td><td></td></tr>
<tr><td>HBO 2 (W)</td><td></td><td>304 SD only sap</td><td></td></tr>
<tr><td><b><i>HBO latino</i></b></td><td></td><td style="background-color:#FFFF00"><b><i>309 HD/SD</i></b></td><td></td></tr>
<tr><td>MoreMax</td><td></td><td>312 SD sap</td><td></td></tr>
<tr><td><b><i>Action Max</i></b></td><td></td><td style="background-color:#FFFF00"><b><i>313 HD/SD sap</i></b></td><td></td></tr>
<tr><td><b><i>5StarMax</i></b></td><td></td><td style="background-color:#FFFF00"><b><i>314 HD/SD sap</i></b></td><td></td></tr>
<tr><td>Showtime Beyond</td><td></td><td>323 SD sap</td><td></td></tr>
<tr><td><b><i>Starz Comedy</i></b></td><td></td><td style="background-color:#FFFF00"><b><i>354 HD/SD</i></b></td><td></td></tr>
<tr><td>The Movie Channel (W)</td><td></td><td></td><td>329 SD sap</td></tr>
<tr><td>Encore Family</td><td></td><td></td><td>347 SD</td></tr>
<tr><td>Encore Suspense</td><td></td><td></td><td>344 SD</td></tr>
<tr><td>Encore MoviePlex</td><td></td><td></td><td></td></tr>
<tr><td><b>HD Movie channels comparison: 5 cable vs. 23 Dish</b></td> <td style="background-color:#FFFF00"> <p><b>Cable Service: (only) 5 HD Movie channels for additional $41</b></td><td style="background-color:#FFFF00"><b>Dish: 23 HD Movie channels for $35 (w/Amer 250), or $44 (w/Amer 120, or Amer 200, or Family packages)</b></td><td><b>Dish SD Movie channels Included in American 250</b></td></tr>
</tbody></table>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October  2, 2012  7:43 AM</b>
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
			<?=getComments(4912)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4912)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2012/10/review-dish-network-hopper.php" type="text/javascript" charset="utf-8"></script>
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