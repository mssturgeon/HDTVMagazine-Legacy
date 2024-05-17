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
		AND e.entry_id = 4558";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4558 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4558 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4558";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/12/perception-of-passive-3dtv-a-nobrainer-analogy.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4558";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Perception of Passive 3DTV - A No-Brainer Analogy" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Perception of Passive 3DTV - A No-Brainer Analogy" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Perception of Passive 3DTV - A No-Brainer Analogy" />
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
	<title>HDTV Magazine - Perception of Passive 3DTV - A No-Brainer Analogy</title>
	<meta name="keywords" content="half turn, passive dtv, active shutter, engine transmission, dealer says, car, wheels, dealer, half, dtv, passive, turn, performance, full, people, our, back, porsche, system, image, may, wheel, active, says, display" />
	<meta name="description" content="In previous articles I described the differences between active-shutter and passive polarized 3D technologies, as well as the subject of “perception” endorsed by the passive polarized 3DTV camp. Graphs and descriptions may get in the way of understanding the concepts so perhaps we should describe the subject of brain perception vs. true image quality with an analogy that may be less complicated than comparing the nuts and bolts of 3DTV technologies, as I did in other articles.

Imagine spending your Saturday visiting new car dealers..." />
	<meta name="title" content="Perception of Passive 3DTV - A No-Brainer Analogy" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Perception of Passive 3DTV - A No-Brainer Analogy" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/12/perception-of-passive-3dtv-a-nobrainer-analogy.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In previous articles I described the differences between active-shutter and passive polarized 3D technologies, as well as the subject of “perception” endorsed by the passive polarized 3DTV camp. Graphs and descriptions may get in the way of understanding the concepts so perhaps we should describe the subject of brain perception vs. true image quality with an analogy that may be less complicated than comparing the nuts and bolts of 3DTV technologies, as I did in other articles.

Imagine spending your Saturday visiting new car dealers..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4558', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/12/perception-of-passive-3dtv-a-nobrainer-analogy.php">Perception of Passive 3DTV - A No-Brainer Analogy</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<p>In previous articles I described the differences between active-shutter and passive polarized 3D technologies, as well as the subject of “perception” endorsed by the passive polarized 3DTV camp. Graphs and descriptions may get in the way of understanding the concepts so perhaps we should describe the subject of brain perception vs. true image quality with an analogy that may be less complicated than comparing the nuts and bolts of 3DTV technologies, as I did in other articles. <p>Imagine spending your Saturday visiting new car dealers. Regardless if you could afford them or not you always admired the quality and performance of Porsches, Ferraris, and BMWs, and the elegance of Cadillac, Lincoln, Lexus, and Infinity, but have not decided which car to buy yet, so you are open to suggestions and may even want to experiment with new technologies and features. <p>In a way the experience is similar to <a href="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-1.php">looking to buy a new HDTV</a>, a TV with a quality image and great performance, which you want to bring home to start your enjoyment. However, for most people, evaluating 3DTV technology is more complicated and taboo than evaluating a car ride or interior, or I should rather say “people could get easily fooled when evaluating a 3DTV”. <p>An ideal 3D system would be able to take the original 1080p pair or images as they were recorded by the 3D cameras and display them simultaneously to both eyes, without diminishing per-eye image resolution (consumer passive system), or alternating images (active system, panels and projectors), or displaying a second image with inverted pixels (LG’s passive method). Other than some hi-end projection systems (including 4K, dual projectors, etc.) there is no panel or projector that is capable to do that at a reasonable consumer price found at Best Buy over the weekend.  <p>One could imagine that high level of quality to be the Lamborghini’s of the cars industry (or your dream choice of exuberant performance cars).  Realizing that you would not come back home with a Lamborghini, you still entertain and hope for the possibility of a reasonable priced Porsche, a performance level that is lower than the dream 3D above, perhaps equated to active shutter by those which vision does not get affected by the way active-shutter operates, otherwise the performance level is irrelevant, the decision becomes subjected to what your vision (or pocket) can tolerate, and passive can be regarded as the savior on the 3D hope.  Luckily both technologies coexist “and should coexist”, like the Yugos, Porsches and Lamborghinis.<p>You have arrived to the car dealers’ street and a big magnet pushes you toward the Porsche dealer, but close to it you spot a new dealer with an ad banner that catches your attention. The ad says: “our car will make you perceive the ride like the Porsche next door. There is no need to pay attention to the performance of the engine, the wheels, or the transmission if the ride feels good enough for you”. <p>You wonder how is that possible, and notice that the wheels on this wonder-car work in an odd way, they seem not to do full turns, they only do a half-turn on each side of the car at the same time, then they stop, then they turn half-way again.  The dealer says: due to engine and transmission efficiency (rather limitations) we made the car so it “is perceived” to be in constant forward movement even when the partial rotation of the wheels need each other to complete a full turn.  <p>The dealer adds: One may think that sharing the half-turn among two wheels may result in a degraded performance, but many people that drove the car for a few minutes in the parking lot do not notice that the wheels work half-way, they perceive the ride as if the wheels are doing full turns all the time, and that is what it counts, the dealer said, “your perception”, what your brain thinks the car is doing, not the mechanical performance measured by industry’s tools and standards. <p>[Back to 3DTV] One may equate the half-rotation per wheel to the typical half-resolution-per-eye of passive 3DTVs, whereby half of the video lines are displayed per eye and both eyes are needed to see the full image, not just the depth of the image. LG claims that the 3D effect is perceived as full resolution by people’s brains. <p>[Back to cars]  However, since you are traditionally interested in performance and quality, you rather have a car with wheels that perform full turns.  You consider the visit to this dealer useful as an exercise in researching alternative technology but decide to move on toward the Porsche dealer next door. <p>But when about to leave, the corner of your eye grasps another version of the same car on the dealer’s floor, you get closer and its window displays a graph, it shows other style of operation, as follows: during the time a wheel is not doing the half-turn forward it uses the engine and transmission to do something else.  Wait, you think.  Wasn’t the concept of two half-wheel-turns good enough for a perception of a smooth ride (you ask the dealer)? <p><div class="caption right" style="width:397px;"><img alt="LG Display's Passive FPR mode of operation to claim it displays the whole resolution of 3D Blu-ray - Source LG Display R&D Department" src="http://www.hdtvmagazine.us/articles/images/eeb6bfba649a_13B/clip_image003_5434b1ec-255a-46eb-bb30-71543f06a6a1.jpg" width="395" height="298"><br />LG Display's Passive FPR mode of operation to claim it displays the whole resolution of 3D Blu-ray - Source LG Display R&D Department</div>The dealer says: we thought about people objecting to the half-wheel-turn operation (regular passive 540 video lines per eye) and because of that we designed this other system so we can claim our car powers the wheels to turn more like our competitors do (full resolution active-shutter 3DTVs).  We are very proud of our system because we found a way to maximize the limited capability of the engine and transmission designed to only power half-turn cycles for the left and right wheels (typical passive 3DTV), this model applies additional bursts of power to the same two wheels after they stop their half-turn (LG’s FPR passive 3DTV on the second 120Hz cycle). <p>You see (the dealer says), on the first half-turn of the wheels (first 120Hz cycle of LG’s FPR passive 3DTV) the engine and transmission rotates the two wheels forward half-turn each, then an additional burst of power (second 120 Hz cycle of LG’s FPR passive 3DTV) makes the wheels rotate half-way again.  We are proud of this design because it can claim all the wheels are constantly moving (displaying the full resolution of 3D Blu-ray, as claimed by LG Display), however, between you and me, due to system limitations the wheels can only turn <a href="http://www.hdtvmagazine.com/articles/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php">in reverse on the second cycle</a>. Turn in reverse (you said)? How can a car be perceived to move constantly forward when the wheels are turning in reverse half of the time? <p>Shhhh! Says the dealer. Nobody notices and we do not explain unless we are asked. The dealer explains that the two reverse half-turns on each wheel are so brief that the movement “is still perceived” as a constant forward rotation when the driver sits on the back seat stretching the arms toward the front to reach the steering wheel to avoid noticing the 540 horizontal defroster lines across the whole windshield.  The black lines are needed for the system to work and since they are thicker than regular rear-window defrosters they block the viewing of the road when sitting too close from the windshield, the dealer says. <p>[Back to 3DTV]  This is similar to viewing a 3DTV from far away to avoid noticing image imperfections and the Frame Patterned Retarder (FPR) of LCD passive 3DTVs. <a href="http://www.lg.com/us/tv-audio-video/televisions/educational-browsing/index.jsp">LG recommends</a> a viewing distance of 15-feet from their 60” 3DTV, when the recommended viewing distance is traditionally 3 times the image’s height (or 7-8 feet for that 60” 3DTV). <p>[Back to cars] The dealer adds, the technology is so smart and we made it so complicated that people think the wheels are turning in the same direction, and again “perception is what counts” and this way we can claim the full wheel turning of Porsche, moreover, with this (3D passive in two 120Hz cycles) technology we (LG Display) are taking the whole car (3DTV) industry by storm.  You can even use the same sunglasses you use in a Lamborghini (local theater). <p>You have to test drive this car (you say to yourself), and you do, is not as bad as you thought, it rides different, and it may be perceived by your grandmother as moving constantly forward when driving from the back seat as the dealer said, but not to you, besides, who wants to drive from the back seat and have obstructed view of the road just to perceive the car’s movement as constantly forward? <p>Additionally, regardless of the “perception”, the fact that you know the wheels are moving forward and then moving backward all the time makes you skeptical about actual performance, safety, and braking during snow and rainy days. <p><div class="caption left" style="width:170px;"><img alt="USA Today June 30, 2011" src="http://www.hdtvmagazine.us/articles/images/eeb6bfba649a_13B/clip_image005_98a9669a-4859-43de-9222-39db9de659b6.jpg" width="168" height="289"><br />USA Today June 30, 2011</div>Then you looked at the sticker price, and your eyebrows did the talk. And you asked: I was expecting a lower price if the engine and transmission have design limitations to just work with half-turning wheels; why is it that the price of your car is similar to the Porsche next door? (Active-shutter 3DTV). And the dealer says “have you seen the low-cost sunglasses that come with our car for extra passengers?” <p>The dealer said that the manufacturer (LG Display) decided to build this car because their experts determined that a minority of people felt nausea or discomfort while driving Porsches and some even noticed visual flickering while seeing the street scenery moving thru the windows, the dealer said. [<i>And I add, however, many of those same people actually felt similar discomfort issues when riding in buses (3D local movie theater)</i>]. <p>Furthermore, the dealer said, we believe everyone must stop buying Porsches and Ferraris and buy our better car, even those people that do not suffer nausea or visual flickering on the windows should do that. So we promote our models by aggressively advertising that Porsche and Ferrari (i.e. Sony and Samsung) should <a href="http://hdguru.com/lg-fires-another-round-in-the-3d-format-war-hd-guru-analysis/5020/">better stick</a> to only make slow speed cars (2D) for neighborhood driving, and let us have the whole auto market (3DTV) for ourselves, regardless if the majority of people actually have no issues with Porsches turning the wheels with full rotation, or looking thru the windows.  <p>Additionally, you should know that Porsche includes only one high-priced designer-performance sunglasses (active-shutter 3D glasses) for the driver, while our car (passive 3DTV) comes with four sets of low-cost generic sunglasses (polarized 3D glasses), many people did not even come to the dealer to test-drive our car, they bought it in the Internet just because it comes with low cost sunglasses and buyers think about the savings for extra passengers, besides, our glasses are also compatible with the windows of public buses (local 3D theater projection systems). <p>Welcome to the world of “good enough perception”, are you in? Your call. 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December  5, 2011  8:09 AM</b>
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
			<?=getComments(4558)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4558)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/12/perception-of-passive-3dtv-a-nobrainer-analogy.php" type="text/javascript" charset="utf-8"></script>
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