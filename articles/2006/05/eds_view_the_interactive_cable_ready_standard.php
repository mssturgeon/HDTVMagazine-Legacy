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
		AND e.entry_id = 368";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ed Milbourn" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 368 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ed Milbourn" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 368 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 368";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/05/eds-view-the-interactive-cable-ready-standard.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 368";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Ed\'s view - The Interactive "Cable Ready" Standard" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Ed\'s view - The Interactive "Cable Ready" Standard" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Ed\'s view - The Interactive "Cable Ready" Standard" />
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
	<title>HDTV Magazine - Ed's view - The Interactive "Cable Ready" Standard</title>
	<meta name="keywords" content="cable ready, cable applications, set top, digital cable, interactive cable, cable, products, ocap, services, fcc, issues, applications, consumers, want, any, ready, standard, bks, should, device, brian, content, consumer, interactive, may" />
	<meta name="description" content="&lt;em&gt;An interview with Brian Smith on the status of the Cable/CE negotiations to establish a fully open interactive digital Cable Ready standard&lt;/em&gt;


Brian Smith is a both good friend and a former business colleague of mine at RCA/Thomson.  Brian presently is VP of Technology Policy and Standards for Philips N.A. and, of special significance to us, is the Chairman of the Consumer Electronics Association (CEA) Video Division Board of Directors.  The CEA Video Board addresses many CE common issues, chief among them being that of representing the CE industry in the ongoing &quot;Interactive Cable Ready&quot; standard negotiations.  The objective of this standard is to ultimately replace the unidirectional CableCARD cable interface with a system that downloads a plethora of interactive services for digital Cable subscribers.

But developing this standard, based on Cable's OCAP (Open Cable Applications Protocol) system, is proving to be one of the most daunting tasks, both technically and commercially, to have been tackled by both industries.  Brian took time from his busy schedule to give us a comprehensive update relative to several salient aspects of the negotiations:



&lt;strong&gt;ED:  Generally, what is the present state of the negotiations?&lt;/strong&gt;

BKS:  Slow going. There are fundamental business issues on each side which conflict with each other and" />
	<meta name="title" content="Ed's view - The Interactive &quot;Cable Ready&quot; Standard" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Ed's view - The Interactive &quot;Cable Ready&quot; Standard" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/05/eds-view-the-interactive-cable-ready-standard.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;em&gt;An interview with Brian Smith on the status of the Cable/CE negotiations to establish a fully open interactive digital Cable Ready standard&lt;/em&gt;


Brian Smith is a both good friend and a former business colleague of mine at RCA/Thomson.  Brian presently is VP of Technology Policy and Standards for Philips N.A. and, of special significance to us, is the Chairman of the Consumer Electronics Association (CEA) Video Division Board of Directors.  The CEA Video Board addresses many CE common issues, chief among them being that of representing the CE industry in the ongoing &quot;Interactive Cable Ready&quot; standard negotiations.  The objective of this standard is to ultimately replace the unidirectional CableCARD cable interface with a system that downloads a plethora of interactive services for digital Cable subscribers.

But developing this standard, based on Cable's OCAP (Open Cable Applications Protocol) system, is proving to be one of the most daunting tasks, both technically and commercially, to have been tackled by both industries.  Brian took time from his busy schedule to give us a comprehensive update relative to several salient aspects of the negotiations:



&lt;strong&gt;ED:  Generally, what is the present state of the negotiations?&lt;/strong&gt;

BKS:  Slow going. There are fundamental business issues on each side which conflict with each other and" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=368', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/05/eds-view-the-interactive-cable-ready-standard.php">Ed's view - The Interactive "Cable Ready" Standard</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ed Milbourn</b> on <b>May  1, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p><em>This is an interview with Brian Smith on the status of the Cable/CE negotiations to establish a fully <a href="http://www.opencable.com/">open interactive digital Cable Ready standard</a></em></p>

<p><br />
Brian Smith is both a good friend and a former business colleague of mine at RCA/Thomson.  Brian presently is VP of Technology Policy and Standards for Philips N.A. and, of special significance to us, is the Chairman of the Consumer Electronics Association (CEA) Video Division Board of Directors.  The CEA Video Board addresses many CE common issues, chief among them being that of representing the CE industry in the ongoing "Interactive Cable Ready" standard negotiations.  The objective of this standard is to ultimately replace the unidirectional CableCARD cable interface with a system that downloads a plethora of interactive services for digital Cable subscribers.</p>

<p>But developing this standard, based on Cable's OCAP (Open Cable Applications Protocol) system, is proving to be one of the most daunting tasks, both technically and commercially, to have been tackled by both industries.  Brian took time from his busy schedule to give us a comprehensive update relative to several salient aspects of the negotiations:</p>

<p><br />
<strong>ED:  Generally, what is the present state of the negotiations?</strong></p>

<p>BKS:  Slow going. There are fundamental business issues on each side which conflict with each other and have not yet yielded to mutually satisfactory compromises even after almost 2 ½ years. Furthermore, the landscape continues to change over time with Cable planning new technologies/services (e.g. switched digital) which were not anticipated at the beginning and further complicate things.</p>

<p><strong>ED:  What are the major commercial and technical issues being      addressed?</strong></p>

<p>BKS:   Cable's fundamental business position is that their service is the entire collection of individual services, presented in the way they want them presented with little or no room for CE products to provide any value-added or differentiation. In effect, Cable wants a set-top box buried within the TV.</p>

<p>CE mfrs. need the freedom to innovate and differentiate their products in order to compete with each other in the retail environment. This includes wanting a uniformity of user operation whether the viewer is watching cable, terrestrial broadcast or any other internal source. It is confusing to the consumer to have to "switch gears" in how the product remote control buttons, menus and other functions operate when they are "watching cable". Furthermore there is a history (including current unidirectional plug & play), where CE products can enhance/differentiate while viewing cable content.</p>

<p>OCAP was designed for use in a dedicated set-top box not having any other functionality but accessing cable services. It has a number of technical resource management systems that want to take total control of the device. Obviously in a multifunction product which may be used for viewing other content, modifications to the way OCAP operates are necessary. A joint technical team is working on some modifications.  A major part of the discussions is how far those modifications should go.</p>

<p>Testing is a very large and complicated issue. OCAP is a middleware software specification, but it is not a uniform piece of software code. There can be many OCAP implementations all based on the same written specification. The applications that cable downloads onto the OCAP middleware can be likened to PC applications running on Windows. The combination of many platforms, many different OCAP implementations, many differently configured Cable head-ends and a variety of applications, would make testing everything against everything mathematically daunting. Cable does not want to unduly delay commercial introduction of new applications to enable extended testing, but CE mfrs are concerned about product robustness - which can be summed up as "TVs should not crash". Finding a middleground is a tough task.</p>

<p>Related to the testing issue is "common reliance". The CE side believes that whatever technologies Cable wants CE to use in cable-ready devices, they should use for themselves in their leased products. Whether it is CableCARDS, OCAP or anything else, if Cable must also rely upon it, then any technical issues will be quickly resolved. So far, Cable has not even implemented CableCARD for its own use and has consistently requested implementation delays from the FCC.</p>

<p>There are also Content Protection issues. Although CE is friendly towards the normal array of protections as covered in the unidirectional agreement and embodied in FCC regulations, cable's content providers want to go further. They would like to have the option to totally shut off selected product outputs, selectively reduce the resolution of hi-def content, and phase out analog interfaces. The CE community is concerned about consumers becoming totally confused, disenchanted and - even worse - believing the products are suddenly "broken" if a content provider shuts-off an interface.</p>

<p>Licensing issues also abound. In order to use cable's conditional access system, several licenses are needed from CableLabs, the cable industry consortium, and potentially from some third parties. Cable's proposed licenses for devices that are fully interactive with cable systems go far beyond simple licensing of the technology and its intellectual property (with appropriate rules to protect concerns over theft of service and copyright issues).  CableLabs has drafted these licenses to compel product conformance to the business and future marketing objectives of cable operators, as well - something that many in the CE and IT industry have said is beyond the permissible scope of present FCC regulations.  These regulations protect a consumer's right to attach a lawful competitive device to a network, so long as the device does not harm the network or contribute to theft of service, and limit the imposition of other licensing constraints on the device provider.  </p>

<p>Technological evolution is another sticky area. At some point in the future, Cable will want to introduce new services which may not be able to operate on older products because they lack a certain new technology. Yet consumers will buy a fully-featured integrated bi-directional cable ready TV specifically because they want the full array of services and which they expect to operate for many, many years. How can Cable continue to evolve their services without angering consumers whose TVs are only a couple of years old? The trick for us is to protect consumers' expectations to enjoy the services they anticipated when they bought their TV, even if they may need some ancillary devices down the road. </p>

<p><strong>ED:  What, if any, are the "deal breakers" as seen by each side?</strong></p>

<p>BKS: Cable does not want their services "disaggregated," i.e. allowing the CE device to become, in their view, a filter for the way they present and market services to the consumer.  For example, they do not want their UI to be modified by the CE products, and they want everything on the UI to be available for the consumer to order and pay for.  Cable wants copy protection and output control flexibility that they say is necessary for them to compete with other service providers.  </p>

<p>CE and IT manufacturers believe that customers should have a choice in the blend of capabilities that they pay for in their products.   They don't think that the combination of OCAP middleware and future cable conditional access software should totally control the TV and its access to other services/peripheral devices, or the home network. They don't think CableLabs should be able to unilaterally establish or change the specifications for what constitutes an Integrated cable ready receiver or the test process for approving them.  CE wants all downloaded Cable applications to be thoroughly tested on CE devices for robustness.  </p>

<p><br />
<strong>ED:  Are others besides Cable and CE involved?  Do they have a   vote?</strong></p>

<p><br />
BKS: Following completion of the unidirectional P&P process, when the groups embarked on this next phase, the FCC asked us to include input from other affected industries. There have been a variety of meetings on content protection with the MPAA, individual studios, TV networks, broadcasters, etc.  These have included other MPVDs (e.g. satellite and telcos) and programming networks, which are also affected by the FCC "encoding" rules that protect consumers from excessive application of copy protection, selectable output control, and "downres" technologies, as well as Cable's existing hardware suppliers, component makers, etc.  Under the FCC guidelines, the official "deal" is between CE and Cable, so these other groups do not get a vote in any proposed bilateral "framework" proposal for new regulations - - however when the agreement is put into the FCC open process, then everyone gets an opportunity to comment on it and the FCC may elect to modify it.</p>

<p>It should also be mentioned, that the CE group consists not only of typical CE companies, but there are also important members of the PC community as well.</p>

<p><br />
<strong>ED:  Is there any thought is making the negotiated version of OCAP (or whatever it is now called) an "open" ANSI standard?</strong></p>

<p><br />
BKS:   We expect that whatever the final jointly agreed specification is, it will go through an ANSI open standards organization such as CEA or SCTE. From the CE perspective, we are on record with the FCC as wanting to see the regulations reference very specific versions ("snapshots") of such standards. If there is still disagreement on certain elements of those specific standards, the FCC could elect (and did in the uni-agreement) to specify in the regulations certain changes to the written specs.</p>

<p><br />
<strong>ED:  Is there really any commercial advantage for CE to embrace OCAP?  (i.e. can CE in general make any money on it from the standpoint of a standard retail marketing model?)</strong> </p>

<p>BKS:  Consumers seem to like the services they get from Cable. Many dislike having a separate set-top box in order to get them. This is now becoming even more the case when many TVs don't have "tops" to put STBs on! Multiple remotes, "dueling" volume controls, hugely different UI schemes, and other user control confusion are tremendously frustrating to consumers.  Consumers embraced the very limited degree of cable compatibility that was achieved in the analog world, which allowed them to tune all unscrambled channels with their TV and VCR remote controls.  We believe there is still great potential for unidirectional "CableCARD" products that first came to market in 2004, and allow consumers to do the same for scrambled digital channels, as well.  Over 2 million have entered consumer homes in under 2 years but we are still struggling with CableCARD support issues in the field.  If these can be resolved, and CableCARD installations become just as routine as set-top boxes, I believe that the consumer preference for well-integrated solutions will become apparent.  This is both the promise and the challenge of taking the next step, and building highly reliable products that integrate the OCAP software.  If we can, I think consumers and retailers will love them.  We want to keep giving consumers a choice in what they buy, and in how it works.</p>

<p><br />
<strong>ED:  Concerning the present state of the negotiations, when do you anticipate an agreement (if any).</strong></p>

<p>BKS:  I don't know. There are still many issues unresolved (as described above), however the groups continue to meet to try to work through them.</p>

<p>Here is what we jointly told the FCC in a March 31 written status report that we filed in FCC Docket No. 97-80:  </p>

<p>"The parties continue to meet with and work with each other, and both sides share the belief that this process is valuable and necessary for the successful design and deployment of integrated Digital Cable Ready products.  Since the date of the last status reports, the parties' joint engineering team has continued its work and has made significant progress in how to define how resources in interactive Digital Cable Ready Products (IDCPs) using the OpenCable Application Platform (OCAP) can be shared between cable applications and other applications of the IDCP, in particular how to avoid conflicts in the use of resources within IDCPs by cable applications and other applications.  There remain technical issues to resolve, and consideration of all solutions by the larger group.  There is also an expectation that a joint team will address defining a workable conformance testing program for interactive products and software applications designed to run on them, based upon the framework previously described in earlier status reports.  Other issues that the parties have agreed to discuss include possible updates to future unidirectional products, and means of conveying firmware updates to bi-directional products."</p>

<p><strong>Thanks, Brian!</strong></p>

<p><em>Having dealt for years with Cable/CE issues in a similar position as Brian's, I can certainly empathize and sympathize with Brian and his team as they struggle with this task.  As Cable continues to protect its private business model, which, of course, they have right to do (in spite of the FCC), Cable may be advised to check its backside.  Coming on strong as a viable competitor to Cable are powerful members of the traditional telecommunications industry  - AT&T, BellSouth, and Verizon.  Last month, these entities, along with CEA, announced the start of negotiations to develop device interoperability standards based on IP (Internet Protocol).  Because of the increasing deregulation of telecommunications services and the growing ubiquity of broadband Internet, the adoption of such an IP network/device standard may very well outpace and eventually "trump" Cable.  Stay tuned.</em></p>

<p>Ed</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ed Milbourn</b>, <b>May  1, 2006 12:03 PM</b>
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
			<?=getComments(368)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Ed Milbourn', 368)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/eds-view-the-interactive-cable-ready-standard.php" type="text/javascript" charset="utf-8"></script>
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