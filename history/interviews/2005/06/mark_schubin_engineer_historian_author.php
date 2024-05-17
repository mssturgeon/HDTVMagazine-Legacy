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
		AND e.entry_id = 96";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 96 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 96 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 96";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_engineer_historian_author.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (4) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 96";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Mark Schubin - Engineer, Historian, Author" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Mark Schubin - Engineer, Historian, Author" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Mark Schubin - Engineer, Historian, Author" />
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
	<title>HDTV Magazine - Mark Schubin - Engineer, Historian, Author</title>
	<meta name="keywords" content="hdtv news, camera cable, monday night, night football, letter box, ntsc, hdtv, cameras, show, problem, truck, something, camera, monitor, shot, different, see, director, lenses, resolution, news, think, going, could, focus" />
	<meta name="description" content="INTERVIEW Mark Schubin Interviewed by Dale Cripps in 2001. Mark Shubin ranks among the best of the best television engineers today. He brings more than 20 years experience to every production along with a wealth of television history and lore...." />
	<meta name="title" content="Mark Schubin - Engineer, Historian, Author" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Mark Schubin - Engineer, Historian, Author" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_engineer_historian_author.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="INTERVIEW Mark Schubin Interviewed by Dale Cripps in 2001. Mark Shubin ranks among the best of the best television engineers today. He brings more than 20 years experience to every production along with a wealth of television history and lore...." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=96', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_engineer_historian_author.php">Mark Schubin - Engineer, Historian, Author</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 18, 2005</b>
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
				<p>INTERVIEW  <br />
Mark Schubin</p>

<p>Interviewed by Dale Cripps in 2001.</p>

<p>Mark Shubin ranks among the best of the best television engineers today. He brings more than 20 years experience to every production along with a wealth of television history and lore. A consultant now to many large communications companies both here and abroad he took time out from his busy schedule to talk to us about the production of an opera produced in 720p with the Panasonic production truck, the same one used for many of the ABC produced Monday Night Football games. Mark talks about some of the characteristics of HDTV which differ from the older standard.</p>

<p> <br />
<strong>HDTV NEWS: You said that HDTV used to be nearly impossible to shoot, and now it isn't; can you elaborate?</strong></p>

<p>We recently shot an opera in Washington. The first Opera I shot in HDTV, which was not the first show I did in HDTV, was Semiramide at the met. We did that completely differently from the way that we would do other operas that we shoot at the met, because the HDTV technology was so restrictive. </p>

<p>The cameras were not placed where the director wanted them; the cameras were placed where the camera cables would allow them to go. We didn't record as many cameras as we would like, because there weren't that many cameras available for HD. </p>

<p>We didn't get the shots that we wanted, because the lenses were very restricted. </p>

<p>We couldn't get very many lenses. The cameras were tremendously insensitive, so we were very restricted in what we could do with lighting, and even then, if somebody moved, and was wearing some jewelry, the jewelry would stay on the screen after the person moved, the cameras were so sticky! </p>

<p>It was an era when everybody not in HD was using solid-state cameras, and in HD you had to use tube cameras. The tape machines that we used were open reel machines, and they recorded wonderful pictures, if you could get them to work, but to get them to work, you had to do something called "burnishing the tape", which meant that you had to run the tape through the machine without recording on it, and then take the reel off, and clean the heads afterwards. </p>

<p>It was a very difficult period to do anything in HD. That period, for the most part, is over.</p>

<p><strong>HDTV NEWS: When did that give way, completely?</strong></p>

<p>It's been a gradual process, but now there is very little that you can do in NTSC </p>

<p>It's been a gradual process, but now there is very little that you can do in NTSC, that you can't also do in HD. The truck that I shot in in Washington is a real TV truck. It's got distribution amplifiers and routing switchers (when we did Semiramide we had to patch with three wire patch cords; that's all done; we just route signals around now). The cameras went exactly where the director wanted them, and there was no problem running camera cable. There's plenty of camera cable available. It's fiber optic camera cable. In fact, because we were using ABC's Monday Night Football truck, and they needed lots of camera cable, and we needed lots of camera cable, we actually got some camera cable from a different source, and the camera cables are all standardized now, so that's pretty easy to look like [??][045]. </p>

<p>The lenses that we used are basically the lenses that the director wanted. We had six very long telephoto zoom lenses, because they are now available for HD. All of that has improved tremendously. It is now possible to do almost anything that you can do in NTSC. There digital effects and there are significant switchers available for HD. There are camcorders available for HD now--two different ones. It's no longer this really, really strange thing that you can't shoot in, and the sensitivity has been improved tremendously.</p>

<p>The one area that still seems to be lacking significantly, is monitoring, but it's not so lacking that you can't do a show. You can certainly do a show in HD.</p>

<p><strong>HDTV NEWS: What do they need to improve in monitoring?</strong></p>

<p>The one area that still seems to be lacking significantly, is monitoring </p>

<p>We need to get affordable, relatively light weight, high quality monitors for HD, and that doesn't exist. There are heavy, gigantic guys from Sony and Panasonic, which make lovely pictures, but it's very difficult to position them, and if you were going to make a monitor wall, as they're doing, say, in the ABC Monday Night Football for Panasonic, the wall (of monitors) would just weigh so much the truck would probably fall over on it's side. So instead, they went with plasma panels, and LCD displays. Those are severely lacking in quality. Even in the video area, this being a 720P truck--somewhat more restricted--they were using Barco computer monitors, rather than video monitors. </p>

<p>Monitors are good--the pictures were fine--but they lack a lot of the facilities that one would normally expect to have in a video monitor.</p>

<p>So, it was a small shortfall--not critical--we were certainly able to do a show. There's down conversion (for) all of that. That works. As I wrote, it's not impossible to do HD shows any more. The big problem is when you're doing an HD show in the NTSC universe.</p>

<p><strong>HDTV NEWS: Is there a kind of a mis-match there? What should the future user of this equipment look for? What do they need to protect themselves against?</strong></p>

<p>That's a very difficult question to answer, but I can give you lots of the problems that we run into. For example, we shot opening night at Carnegie Hall in 1998 in HDTV, and we had previously been shooting opening night at Carnegie Hall every year in NTSC. </p>

<p>Again, the facilities were fine--we had an NHK truck that we were using for that. The cameras were terrific; cables, lenses, everything was fine. We had to do some interesting out-boarding because it was an HDTV truck, but we were going to be editing off-line, so we needed tapes to be able to feed the off line editor. That was NTSC, so we actually had to pull over a second truck, just to deal with the NTSC stuff, but that's sort of an operational difficulty, that's not an insoluble problem. </p>

<p>The insoluble problem came about in this regard: The director, who was in the HDTV truck, was looking at a very good HDTV monitor, and he was seeing very good HDTV pictures. He decided on a shot that had the two featured singers, who were Brian Stokes Mitchell, and Audrey Ann McDonald, and had them in full figure--the conductor on the left side of the frame, and members of the orchestra behind. It was an absolutely glorious shot. You could see the two singers interacting with each other; you could see their faces, and the look in their eyes, and the conductor appreciating them, and the orchestra appreciating them, and it was an absolutely magnificent shot. Everyone who's seen the show in HD says "wow, what a terrific shot, what a great show. This is terrific". </p>

<p>But then we have to air the thing. There is not a great deal of HDTV airing available yet, so we have to convert the show to NTSC. We chose to use letter-box to deal with the aspect ratio problem. That meant that people watching the show on NTSC got to see only 360 lines of television. We started with 1080, so they now have one third of the vertical resolution that we started with. </p>

<p>In horizontal terms, we started with 1920 pixels per line, and a typical NTSC television station can, at best, transmit 440 pixels per line. So we've gone to something like a fifth of the resolution horizontally, a third of the resolution vertically. So now this wonderful, beautiful shot that the director had gotten, and it was such a good shot that he lingered on it, having no reason to change it, in NTSC becomes an establishing shot, and the NTSC viewers are looking at this, and going . . . "hello . . . when are we going to be able to see their faces?"</p>

<p>They could not see any of this interaction. Instead of it being a beautiful shot, it was a boring shot, and one that was lasting for a very long time. So that's one enormous problem. It's an esthetic problem, and the question is, what do you do? Do you shoot in HD . . . go for the HD shot? Unquestionably, it was a glorious HD shot; it was a beautiful HD show. Whether you do that, and say "ok, well, I'm shooting for the best stuff, or do you instead, give the director an NTSC monitor, and say make me a nice NTSC show, and then the HD people get sort of a boring show--maybe a little better looking than NTSC does--or do you try to do some compromise in between, and dissatisfy both sides?</p>

<p>What has been happening in places like when CBS did their coverage of the US That meant that people watching the show on NTSC got to see only 360 lines of television. <br />
Tennis Open, and ABC's coverage of Monday Night Football, is they have actually been doing two completely separate feeds. So ABC has an HD director, and an HD truck, and is creating an HD feed, and they have a separate NTSC director, and an NTSC truck, creating an NTSC feed. CBS did exactly the same thing with the US Open. The HD director is just making the best HD show he or she can make, and the NTSC director is just making the best NTSC show he or she can make. </p>

<p>That's great if you can do that. We don't have the facilities in the kinds of shows that we do to do that, and so we've been suffering.</p>

<p><strong>HDTV NEWS: So there will be a suffering period as we make this transition over the years. Unless people are willing to do dual productions, we're going to have a compromise that is a little bit distracting to both sides.</strong><br />
Yes, we are in compromise territory.</p>

<p>Does that suggest to you that, as David Niles, and others have been saying since the beginning, that HDTV is entirely a different and separate business from the NTSC business, even though it looks an awful lot the same? </p>

<p>I'm not sure which of the David Niles quotes you are referring to. He has said on a number of occasions, and a bunch of other people have said, that HDTV is not film, it is not video, it is something completely different. That, I don't think, is relevant to this discussion, but you used the term "business", and if he has been saying that it's a different business, meaning that you need to deal with it separately; yes, I'd go along with that.</p>

<p><strong>HDTV NEWS: Latency versus quality?</strong></p>

<p>Ok, this is something that we ran into in Washington. This is a different side of the HDTV and NSTC universe. The stuff that I just mentioned is the problem of broadcasting to a NTSC audience. The other problem you run into is if you are going out, and you are shooting some single camera stuff, like the "Over America," "Over Canada," all those series, or you're shooting a studio show, or something like that, you're pretty much self-contained in HD, until you go into editing and have to down-convert for NTSC airing. But what if you're doing a real NTSC live show on the order of, let's say Monday Night Football, and what if you're doing it as the only truck, not with two trucks? </p>

<p>Well, now you have a bunch of situations. You've got an announcer some place, and the announcer needs to see the show, so he needs a monitor to look at. In NTSC, that's a hundred dollar monitor that you stick in front of the him, and run a line up to it. In HDTV that's maybe a $4,000 monitor, and I'm talking about something inexpensive like a consumer-type monitor. It's big, and it's heavy, and the line that you run up there...let's say that you're using the high definition serial digital interface, HDSDI, that's one and a half gigabits of information. You can't run that very far. We ran into that problem in Washington. The lighting director normally sits in an area at the opera house at the Kennedy Center called the translation booth. That's about 700 feet of cable run from the truck. You can't run HDSDI over 700 feet on a piece of coaxial. You could maybe do it with fiber, and if we have time to rig that up, and have the equipment, we could maybe do that. So that's a problem.</p>

<p>We had a separate sound truck in Washington. The sound people are looking at a monitor to see where the singers are, and what's going on, and we fed them something called the low-latency down-converter. It's actually part of the distribution amplifier, and it essentially down-converts at the same timing that the real signal is going out. That meant that they were seeing pretty lousy down-conversion. Well, we had good down-converters available; we had the Panasonic Universal down-converter, which is terrific, and even the down-converters built into the HTD 5 machines are terrific, but they take time to do their work, which meant that if we fed that to the sound-truck, which we ultimately did do, they're looking at something that's out of sync with the audio that we're hearing.</p>

<p>Ok, you can take care of that. The Universal down-converter, and the HTD 5 have separate audio outputs that match the down-conversion, but now you're asking the audio people to be listening to something which is not what they're mixing. So you run into all these complications.</p>

<p>Here's another situation we have...again, there's sort of a paper edit that gets made before an off-line edit on a standard computer non-linear system before the on-line edit. Well, for the paper edit, we prepare a vhs tape that has a quad split in it. The quad split is the four main recordings that we're making. It's an NTSC show; you're degrading the quality already of the NTSC by making the quad split, and then you're putting it on vhs, which degrades stuff still further, so HD, if we did that with letter-box output, would be such tiny pictures , and so degraded, that the editor couldn't really tell anything. </p>

<p>He wanted us to make anamorphic outputs for the quad split. So, ok, fine, no problem, the Panasonic machines will deal with that. But meanwhile, the lighting director wanted to have a VHS of the program feed, which was not anamorphic. He wanted to see that in letter-box, so that he could see what the actual show looked like. So now we have a different mood, and we need a different down-converter. If we were feeding the press, as we've done, say, at Carnegie Hall, then we need to down-convert to something that is neither letter-box nor anamorphic, because the press doesn't want to have letter-box on their news shows, they want to have full-screen, so now we have to come up with a center cut or a pan-and-scan type of thing. </p>

<p>Now we're talking three different kinds of down-conversion...not counting the fact that we have different latencies; not counting the fact that there are different qualities that are associated with latencies, so it just becomes a bit complicated.</p>

<p><strong>HDTV NEWS: It's a bit complicated, but these are problems that are not likely to ever go away, are they?</strong></p>

<p>Not while we are dealing with two different forms of television. As long as we're dealing with both HD and NTSC, these problems are not going to go away.</p>

<p><strong>HDTV NEWS: You mentioned focus being a big issue. How does that trouble you, or not trouble you?</strong></p>

<p>Well, it's troubling! In dealing with focus, we refer to something called the circle of confusion. The circle of confusion is a small circle, anywhere within which you cannot tell whether something is in focus, or out of focus, so you can think of it as being a pixel. If something fits within one pixel, it's in focus, if it doesn't fit within one pixel, it's out of focus. That's a little over-simplified, but it's ok to think of it that way.</p>

<p>An HDTV pixel is much smaller than a NTSC pixel, and so if you work out the formulas for depth of field, and so on, that are all based on this circle of confusion, you find that things are much more difficult to focus on in HDTV than they are in NTSC. That's just straight-forward. That's assuming all else is equal, but all else is not equal, because the cameras are also a little less sensitive. So if you're dealing with a lot of light; if you are in a studio or something like that, then you just have to deal with the circle of confusion issue. If you're dealing with low light, then you also have to face the problem that the lenses for an HD camera are going to be opened up a little wider than the lenses for an NTSC camera. </p>

<p>Then you have a third problem, which is, if you're shooting with an inch and a half viewfinder, you have this little teeny tiny picture tube that you're looking at, and you need to focus in HD on something that may not be able to present you with a true HD picture.</p>

<p>C. R. Caluette gave a really terrific paper about that at the ITS Technology Retreat last year. He said you really need the largest monitor that you can take around with you on your show, because you're not going to be able to tell focus in your tiny viewfinder.</p>

<p>What's interesting is, in the previous generation of cameras, the Sony HDC500, there was a remote focus control, so that the video operator could help out and do fine focus. The latest cameras don't have that. Also, the latest cameras using two thirds inch imaging devices don't have lenses for them that match the quality of the lenses that were available for the one inch cameras. So, in a way, we've come an additional generation forward...there are now HD camcorders, for example, which there weren't before, but we've taken a step backward in terms of picture quality, I think. The latest cameras are not up to the snuff of what the previous possibility of camera and lens were. I'm not saying it's just the camera. It's the camera/lens combination.</p>

<p><strong>HDTV NEWS: As an engineer, are there any serious shortcomings today that you just wish the camera designers would have addressed? Can you help give them a little engineering feedback? </strong></p>

<p>I would like monitoring to be addressed, more than cameras. On the camera issue, I think that the bigger problem is the lens. I think that the cameras are pretty good. There are some minor things that need to be worked out. There were some problems that we had in Washington, but those were prototype cameras of the 720p mode. I think the cameras are pretty good, but having gone through the two thirds inch chip, there is a big problem in terms of detail resolution. It is not as good as the previous generation of cameras was. </p>

<p><strong>HDTV NEWS: Are these lenses that are being used the same lenses that were used for NTSC? </strong></p>

<p>Some people do that. I wouldn't. I think that's a bad idea, but I'm told that that's one of the reasons that the shift in the cameras to two thirds inch was done; so that people could use their NTSC lenses. There's a big difference between an HD lens and an NTSC lens, but again, it depends on what you're looking at it on. A lot of people go out in the field, and will shoot something, and will look at it on some monitor that doesn't really have true HD resolution, and they'll say "oh...I can't see any difference with this lens at all...this looks fine", and then they will air it, or show it someplace where there is a really high quality HD projector, and you're looking at it, and you go "boy, this looks awfully soft, compared to the other stuff you shot", so I think that's a big problem. I don't think that was properly done.</p>

<p><strong>HDTV NEWS: Getting back to the monitor story, some of the producers have said "you know, you really need to produce this on a monitor that offers a 30 degree field of view that you expect the end-viewer to be experiencing. That would suggest a larger monitor.Is that called for? </strong></p>

<p>I have mixed feelings about that. If you look at human vision, and where it pops out in resolution, you find that you actually can't see HD resolution without having a large monitor, or being closer to it. If you were to scale up...In an NTSC truck, the director typically looks at a 20 inch monitor...in an HD facility you'd probably want something on the order of a 50 inch monitor, and nobody's really doing that yet.    <br />
    <br />
  <em>On the camera issue, I think that the bigger problem is the lens  </em></p>

<p><br />
But, there is a second human visual phenomenon, which is known as sharpness. Sharpness is different from resolution. Resolution is being able to distinguish that two lines in a pair are two lines and not one line. Sharpness is a psycho-visual sensation that says "this is very crisp", or something like that. Otto Shade, the researcher at RCA laboratories many years ago, said that the psycho-visual sensation of sharpness is proportional to the square of the area under a curve that plots resolution versus contrast. Even if you cannot see the maximum resolution of HD, you may be influenced by it, if this sharpness is improved for you. </p>

<p>What I found, in sitting in the truck at the Washington Opera, was that the little 17 inch STI LTD panels looked noticeably sharper to me than did the 42 inch platinum panels. Even from considerably farther away, (too far, in my opinion, to see the HD resolution), one of two things was happening. Either the platinum panels were so awful, in the resolution that they were providing, that I would notice it from the STI's, or there was some contribution sharpness being made. So, I'm not prepared to give an absolute answer to your question, at this point, but I think that there are certainly reasons to believe that what you said might be true--that you may have to go for larger monitoring.</p>

<p><strong>HDTV NEWS: In the different cameras on the market today...are they functionally about the same? In other words, a man trained on one could easily move to the other?</strong></p>

<p>Yes, sure. There are slight differences, but nothing special.</p>

<p><br />
<strong>Thank you Mark.</strong></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 18, 2005 11:36 AM</b>
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
			<?=getComments(96)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 96)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/mark_schubin_engineer_historian_author.php" type="text/javascript" charset="utf-8"></script>
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