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
		AND e.entry_id = 1473";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1473 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1473 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1473";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/07/smpte-to-establish-3d-home-entertainment-task-force-committee-to-define-parameters-of-stereoscopic-3d-mastering-standard-for-home-display.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1473";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display" />
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
	<title>HDTV Magazine - SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display</title>
	<meta name="keywords" content="task force, entertainment technology, society motion, motion picture, home display, smpte, home, entertainment, content, standards, glasses, technology, task, digital, force, new, etc, society, engineers, motion, display, eye, television, those, industry" />
	<meta name="description" content="&lt;p class=&quot;editorial&quot;&gt;The SMPTE is a standards setting society for the North American television and motion picture industries. While every task force formed within the SMPTE may not lead to a new industry success, a formation of one sends a very strong message that a seriousness for a topic has set in. The engineers that populate the SMPTE are, for the most part, from the manufacturing and content sectors. For those who have experienced 3D on the big screen (by way of the Christie digital projector) know that a great treat is in store for us when it comes home. That is now technically possible and some sets are being sold today as 3D-ready.

In years past every effort to commercialize 3D (something certainly not new in concept nor even technology) ended with a disappointing collapse. The failure has always been blamed on the same thing--those uncomfortable glasses a viewer must wear for essential left eye-right eye image separation. Several attempts have been made to avoid glasses by using complex rear projection screen technology, but that never worked well. A golden marketing opportunity has come to the eye wear industry. The problem for them to solve is the public rejection of the 3D glasses. One way that can be done is by engaging the genius of eyeglass frame makers (like Luxottica) and the mass marketing lens-making and mounting companies (like Benyon and LensCrafters). Together they can produce a mass public appeal to both accept and then acquire comfortable and fashionable &quot;now-essential&quot; 3D eye wear. The acquiring of these fashionable 3D glasses may be quite similar to how one gets their dark glasses--order them to your prescription with a desired frame style. It needs to be sold as another of those things we accept as part of our middle class standard of living. For those with uncorrected vision you may turn to the racks of 3D glasses at your local grocer and, as with reading glasses, choose a pair fit for your face and personality. &quot;Make the glasses friendly and fashionable&quot; is the message being given here and you bring to an end the lethal objection to 3D--uncorrected and uncomfortable glasses. __Dale Cripps&lt;/p&gt;
  

WHITE PLAINS, NY - July 21, 2008 -- The Society of Motion Picture and Television Engineers (SMPTE) is establishing a task force to define the parameters of a stereoscopic 3-D mastering standard for content viewed in the home. Called 3-D Home Display Formats Task Force, the project promises to propel the 3-D home entertainment industry forward by setting the stage for a standard that will..." />
	<meta name="title" content="SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/07/smpte-to-establish-3d-home-entertainment-task-force-committee-to-define-parameters-of-stereoscopic-3d-mastering-standard-for-home-display.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;p class=&quot;editorial&quot;&gt;The SMPTE is a standards setting society for the North American television and motion picture industries. While every task force formed within the SMPTE may not lead to a new industry success, a formation of one sends a very strong message that a seriousness for a topic has set in. The engineers that populate the SMPTE are, for the most part, from the manufacturing and content sectors. For those who have experienced 3D on the big screen (by way of the Christie digital projector) know that a great treat is in store for us when it comes home. That is now technically possible and some sets are being sold today as 3D-ready.

In years past every effort to commercialize 3D (something certainly not new in concept nor even technology) ended with a disappointing collapse. The failure has always been blamed on the same thing--those uncomfortable glasses a viewer must wear for essential left eye-right eye image separation. Several attempts have been made to avoid glasses by using complex rear projection screen technology, but that never worked well. A golden marketing opportunity has come to the eye wear industry. The problem for them to solve is the public rejection of the 3D glasses. One way that can be done is by engaging the genius of eyeglass frame makers (like Luxottica) and the mass marketing lens-making and mounting companies (like Benyon and LensCrafters). Together they can produce a mass public appeal to both accept and then acquire comfortable and fashionable &quot;now-essential&quot; 3D eye wear. The acquiring of these fashionable 3D glasses may be quite similar to how one gets their dark glasses--order them to your prescription with a desired frame style. It needs to be sold as another of those things we accept as part of our middle class standard of living. For those with uncorrected vision you may turn to the racks of 3D glasses at your local grocer and, as with reading glasses, choose a pair fit for your face and personality. &quot;Make the glasses friendly and fashionable&quot; is the message being given here and you bring to an end the lethal objection to 3D--uncorrected and uncomfortable glasses. __Dale Cripps&lt;/p&gt;
  

WHITE PLAINS, NY - July 21, 2008 -- The Society of Motion Picture and Television Engineers (SMPTE) is establishing a task force to define the parameters of a stereoscopic 3-D mastering standard for content viewed in the home. Called 3-D Home Display Formats Task Force, the project promises to propel the 3-D home entertainment industry forward by setting the stage for a standard that will..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1473', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/07/smpte-to-establish-3d-home-entertainment-task-force-committee-to-define-parameters-of-stereoscopic-3d-mastering-standard-for-home-display.php">SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>July 21, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=272&category=Technology">Technology</a></b>
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
				<p class="editorial">The SMPTE is a standards setting society for the North American television and motion picture industries. While every task force formed within the SMPTE may not lead to a new industry success, a formation of one sends a very strong message that a seriousness for a topic has set in. The engineers that populate the SMPTE are, for the most part, from the manufacturing and content sectors. For those who have experienced 3D on the big screen (by way of the Christie digital projector) know that a great treat is in store for us when it comes home. That is now technically possible and some sets are being sold today as 3D-ready.

<p>In years past every effort to commercialize 3D (something certainly not new in concept nor even technology) ended with a disappointing collapse. The failure has always been blamed on the same thing--those uncomfortable glasses a viewer must wear for essential left eye-right eye image separation. Several attempts have been made to avoid glasses by using complex rear projection screen technology, but that never worked well. A golden marketing opportunity has come to the eye wear industry. The problem for them to solve is the public rejection of the 3D glasses. One way that can be done is by engaging the genius of eyeglass frame makers (like Luxottica) and the mass marketing lens-making and mounting companies (like Benyon and LensCrafters). Together they can produce a mass public appeal to both accept and then acquire comfortable and fashionable "now-essential" 3D eye wear. The acquiring of these fashionable 3D glasses may be quite similar to how one gets their dark glasses--order them to your prescription with a desired frame style. It needs to be sold as another of those things we accept as part of our middle class standard of living. For those with uncorrected vision you may turn to the racks of 3D glasses at your local grocer and, as with reading glasses, choose a pair fit for your face and personality. "Make the glasses friendly and fashionable" is the message being given here and you bring to an end the lethal objection to 3D--uncorrected and uncomfortable glasses. __Dale Cripps</p></p>

<p>  </p>

<p>WHITE PLAINS, NY - July 21, 2008 -- The Society of Motion Picture and Television Engineers (SMPTE) is establishing a task force to define the parameters of a stereoscopic 3-D mastering standard for content viewed in the home. Called 3-D Home Display Formats Task Force, the project promises to propel the 3-D home entertainment industry forward by setting the stage for a standard that will enable 3-D feature films and other programming to be played on all fixed devices in the home, no matter the delivery channel.  The inaugural meeting of the Task Force is open to entertainment technology professionals interested in participating in the effort, subject to available space (SMPTE membership not required). It takes place on August 19, 2008 and will be hosted by the Entertainment Technology Center (ETC) at the University of Southern California, near downtown Los Angeles.</p>

<p>"Digital technologies have not only paved the way for high quality 3-D in the theaters, they have also opened the door to 3-D in the home," explained SMPTE Engineering Vice President Wendy Aylsworth. "In order to take advantage of this new opportunity, we need to guarantee consumers that they will be able to view the 3-D content they purchase and provide them with 3-D home solutions for all pocketbooks."<br />
 <br />
The 3-D Home Display Formats Task Force will explore the standards that need to be set for 3-D content distributed via broadcast, cable, satellite, packaged media and the Internet and played-out on televisions, computer screens and other tethered displays. After six months, the committee will produce a report that defines the issues and challenges, minimum standards, evaluation criteria and more, which will serve as a working document for SMPTE 3-D standards efforts to follow. </p>

<p><br />
The first 3-D Home Display Formats Task Force gathering will feature demonstrations of 3-D technologies. All technology professionals in content creation and distribution, consumer electronics and entertainment tools and services who are considering joining the group are welcome to attend. Non-members will be asked to pay a small fee for the initial meeting, and ongoing participation in the work requires membership in the SMPTE Standards Community. Register at: www.smpte.org</p>

<p>About the Society of Motion Picture and Television Engineers<br />
The Society of Motion Picture and Television Engineers (SMPTE) is the leading international technical society for the motion imaging industry. As an internationally recognized and accredited standards-setting body, SMPTE develops standards, recommended practices and guidelines and spearheads educational activities to advance engineering and moving imagery. Since its founding in 1916, the Society has established more than 600 standards including the physical dimensions of 35mm film and the SMPTE time code. More recently, it codified the MXF file format to support the exchange of professional AV content and crafted the Digital Cinema Standards, which paved the way for digital movie theaters. Headquartered in New York, SMPTE is comprised of engineers and other technical specialists, IT and new media professionals, filmmakers, manufacturers, educators and consultants in more than 65 countries. They are joined at SMPTE by more than 200 sponsoring corporations, principal players in content creation, production and delivery for all platforms and in entertainment hardware and software. www.smpte.org <http://www.smpte.org> .<br />
 <br />
About the Entertainment Technology Center @ USC<br />
The Entertainment Technology Center @ USC is a non-profit organization within USC's School of Cinematic Arts which brings together the top entertainment, technology and consumer electronic companies to discuss how to understand what next-generation consumers want and then to work towards new entertainment products and services for the future. ETC's Executive Sponsors are Disney, Sony Pictures Entertainment, Twentieth Century Fox, Viacom/Paramount, Warner Bros., along with Alcatel-Lucent, Cisco, Deluxe Entertainment Services Group, Inc., Lucasfilm Ltd, Sharp, TATA Consultancy Services, Thomson and Volkswagen of America.  Additionally, ETC's Anytime/Anywhere Content Lab (AACL) is sponsored by Dolby, LG Electronics and Sandisk.<br />
One of ETC's current initiatives is to map 3-D and identify gaps and opportunities for improvement in acquisition, production, to distribution in theaters, the home and digital devices.  ETC is now conceptualizing the Digital 3-D Lab, as a part of the Anytime/Anywhere Content Lab, which will build on the success of ETC's world-renowned Digital Cinema Laboratory. For more information, email: info@etcenter.org </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>July 21, 2008  9:15 AM</b>
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
			<?=getComments(1473)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 1473)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/07/smpte-to-establish-3d-home-entertainment-task-force-committee-to-define-parameters-of-stereoscopic-3d-mastering-standard-for-home-display.php" type="text/javascript" charset="utf-8"></script>
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