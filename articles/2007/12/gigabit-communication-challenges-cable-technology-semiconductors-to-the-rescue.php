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
		AND e.entry_id = 813";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="RedMere/Molex" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 813 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'RedMere/Molex'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="RedMere/Molex" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 813 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 813";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 813";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" />
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
	<title>HDTV Magazine - Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue</title>
	<meta name="keywords" content="pair skew, intra pair, high frequency, cable manufacturers, gbps data, cable, data, skew, figure, eye, equalization, cables, technology, redmere, gbps, meter, adaptive, pair, manufacturers, high, problems, signal, frequency, cost, end" />
	<meta name="description" content="Multi-gigabit communications present many challenges to cable manufacturers. How can bandwidths higher than 10Gbps required by new standards such as HDMI™ and DisplayPort be achieved over low cost cables? What are the core technical problems with achieving these high data rates and what technologies can be used to address them? How can manufacturers achieve solutions which are less dependent on copper pricing? How can reliability issues be resolved without the need to use thicker cables? Cable manufacturing techniques have evolved to try to meet the challenge, but semiconductor solutions are emerging as promising alternatives and can be expected to play a significant role in solving these issues.

This article explains the physical problems faced by cable manufacturers, in particular..." />
	<meta name="title" content="Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Multi-gigabit communications present many challenges to cable manufacturers. How can bandwidths higher than 10Gbps required by new standards such as HDMI™ and DisplayPort be achieved over low cost cables? What are the core technical problems with achieving these high data rates and what technologies can be used to address them? How can manufacturers achieve solutions which are less dependent on copper pricing? How can reliability issues be resolved without the need to use thicker cables? Cable manufacturing techniques have evolved to try to meet the challenge, but semiconductor solutions are emerging as promising alternatives and can be expected to play a significant role in solving these issues.

This article explains the physical problems faced by cable manufacturers, in particular..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=813', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php">Gigabit Communication Challenges Cable Technology: Semiconductors to the Rescue</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>RedMere/Molex</b> on <b>December 19, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p>Multi-gigabit communications present many challenges to cable manufacturers. How can bandwidths higher than 10Gbps required by new standards such as HDMI&trade; and DisplayPort be achieved over low cost cables? What are the core technical problems with achieving these high data rates and what technologies can be used to address them? How can manufacturers achieve solutions which are less dependent on copper pricing? How can reliability issues be resolved without the need to use thicker cables? Cable manufacturing techniques have evolved to try to meet the challenge, but semiconductor solutions are emerging as promising alternatives and can be expected to play a significant role in solving these issues.  <p>This article explains the physical problems faced by cable manufacturers, in particular <i>differential skew</i> and <i>limited bandwidth</i>. The origins and implications for both problems are explained using eye diagrams. The article presents a silicon solution for these problems, discussing the challenges associated with circuits which automatically de-skew and the requirement for equalization to address the limited bandwidth problem. Data recovery is shown to be dramatically improved when adaptive de-skew and equalization is applied.  <p>Cost-effective silicon embedded in a cable bulk-head, combined with low-cost manufacturing techniques (high AWG cables, etc), provide cable manufacturers with a competitive solution in terms of performance and cost.  <p>Cable Manufacturers need to embrace semiconductor technology. Embedded silicon can answer the commercial and technical issues facing the industry today.  <p><b>The Cable Problem</b>  <p><b></b> <p>The main challenge for cable manufacturers is to solve the issue of propagation delay difference and high frequency suppression problems associated with the eternal need for increased data-rates. Propagation delay difference varies with cable length and dielectric constant and causes increased common-mode noise (cross-talk, EMI) and reduced transmission margin. High frequency suppression is a function of conductive loss (skin-effect and shield current) and causes increased rise times and reduced amplitude on the transmitted signal.  <p>To combat these effects, manufacturers need to optimize the selection of cable type (moving away from STP to TWINAX or SCTC), dielectric performance (use mechanical foaming) and conductive material (use solid and not stranded material and move to low AWGs). These solutions are expensive and bring other challenges to the cable (bulkiness, weight, rigidness, solder-cracking in connector, etc). An alternative approach is to consider cost-effective embedded semiconductor solutions such as RedMere's MagnifEye&trade; Repeater, MagnifEye&trade; Switch and Cable MagnifEye&trade; solutions which solve these <i>intra-pair skew</i> and <i>high frequency attenuation</i> problems for different cable applications<i>,</i> allowing cable manufacturers to work with thin low-cost cables such as 36 AWG.  <h4><br>Intra-pair Skew</h4> <p>Intra-pair skew exists in all systems where differential signals are transmitted. It is caused by differences in transit times or electrical path lengths for the positive and negative parts of a differential signal. These transit time differences ("skews") or electrical path length differences are caused by tolerances in the cable manufacturing process. The phenomenon is not well known because twisted pair have only recently been used for Gigabit data rates. At these data rates cables of three meters and beyond have differential skew times that are significant portions of the data bit times.  <p>To see where this "skew" time might come from we first note that signal propagation velocity along a twisted pair is approximately 0.71 times the speed of light which translates to approximately 47ps per cm. Thus for a ten meter cable the total delay is 47ns. Therefore a path length difference of just 1% within a ten meter cable causes an intra-pair skew of 470ps. We will show that this level of skew is disastrous in the context of 300-600ps bit-times. Figure 1 illustrates how a tiny change in the cable wrapping leads to a change in cable length, which then results in intra-pair skew.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image002.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="78" alt="clip_image002" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image002_thumb.jpg" width="556" border="0"></a></p> <p>Figure 1. Manufacturing quality affects path length in a twisted pair.  <p>It is also important to note that even if the manufacturing process produces perfectly matched lengths, this does not guarantee zero skew. Cables and PCB materials can have non-uniform dielectric constants due to variation in thickness and material properties. This results in variation in propagation velocity, which also changes the effective path length. The skew problems discussed can be exacerbated by bending or compression of the cable, effects which are almost guaranteed in many application environments.  <p>We have characterized hundreds of cables and found a wide variation in intra-pair skew ranging from 40ps on shorter cables up to 520ps on some 20 meter examples. Samples of cable measurements we have made are shown in Figure 2.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image004.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="276" alt="clip_image004" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image004_thumb.gif" width="390" border="0"></a><br>Figure 2. Measured intra-pair skew</p> <p>The impact of skew for a TV receiver is to directly reduce the timing budget available to the data recovery circuit to extract the data. Figure 3 below shows the impact of 155ps of skew on a 3.4Gbps data eye through three meters of Twinax cable. This is the HDMI&trade; specification limit for the skew at the receiver end of a cable.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image006.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="261" alt="clip_image006" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image006_thumb.jpg" width="557" border="0"></a></p> <p>Figure 3. Eye diagram at 3.4Gbps showing zero skew (top plot) and eye diagram with 155ps of intra-pair skew (lower plot).  <p>The top plot corresponds to a cable with zero skew and shows significant eye opening. This opening allows the data recovery block in the receiver sample the data over a 300ps window to decide whether a '1' or '0' is present. The lower plot shows that the addition of the 155ps of skew has reduced this valid data window to approximately 150ps thus making it virtually impossible for the data recovery block in the receiver.  <h5>Solution for Intra-pair Skew Problem</h5> <p>Redmere has patented a unique <i>adaptive de-skewing</i> technology to tackle the skew problem. This technology sits at the front end of the receiver chip and re-aligns the positive and negative portions of the differential signals. The re-alignment is done automatically using a combination of deep oversampling of the data bits and custom DSP. The result of this de-skewing block is seen below in Figure 4 where the top poor eye is reopened resulting in the data eye shown in the lower plot.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image008.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="274" alt="clip_image008" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image008_thumb.jpg" width="557" border="0"></a></p> <p>Figure 4. Eye diagram at 3.4Gbps showing degradation due to 155ps of skew (top plot) and improved eye diagram after processing by REDMERE's active de-skew circuitry (lower plot).  <h4>Inter Symbol Interference (ISI) or High frequency Attenuation</h4> <p>Because cables have multiple parallel lines there will invariably be some series inductance and parallel capacitance. These parasitic elements will filter the high frequency components of the signal. A measure of this effect is seen below in Figure 5. Here we see signal attenuation versus frequency for three and six meter cables.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image010.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="315" alt="clip_image010" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image010_thumb.gif" width="494" border="0"></a></p> <p>Figure 5. Signal attenuation versus frequency for three and six meter Twinax cables.  <p>When a signal is filtered by the cable, the data pulses of different lengths are shortened or lengthened and this degrades the data eye. This time domain degradation is seen in the eye diagrams of Figure 6 where we see the impact of passing data through a three meter Twinax cable (Top graph) and then further degradation when data is passed through six meters of Twinax cable.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image012.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="200" alt="clip_image012" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image012_thumb.jpg" width="557" border="0"></a></p> <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image014.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="204" alt="clip_image014" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image014_thumb.jpg" width="557" border="0"></a></p> <p>Figure 6. 3.4Gbps eye diagram at the end of three and six meters of Twinax cable.  <p>Cable equalizers compensate for the high frequency loss by applying gain to the high frequency components of the received signal. This process is seen in Figure 7 where the cascade of the cable transfer function and the equalizer transfer function produce a unity gain or all pass transfer function.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/image.png"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="159" alt="image" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/image_thumb.png" width="577" border="0"></a> </p> <p>Figure 7. Cascading of cable transfer function with an equalizer transfer function produces unity gain over all frequencies.  <p>If appropriate equalization is applied to the three meter data eye seen in Figure 6 then the resultant improved eye diagram is as can be seen in Figure 8.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image029.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="216" alt="clip_image029" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image029_thumb.jpg" width="558" border="0"></a></p> <p>Figure 8. 3.4Gbps data with appropriate equalization showing improved eye.  <p>If the same <i>fixed equalization</i> is applied at the end of a six meter cable then the result is a poor eye as shown in Figure 9. Thus the six meter cable is under-equalized and the level of eye closure here may well cause bit errors.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image031.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="217" alt="clip_image031" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image031_thumb.jpg" width="558" border="0"></a></p> <p>Figure 9. Poor 3.4Gbps data eye resulting from <i>fixed equalization</i> scheme applied to six meter cable.  <p>For this reason, <i>fixed equalization</i> is not generally a quality solution and there is a requirement for tuning the equalization as the cable length changes. A simple implementation of this tuning process is referred to as <i>programmable equalization</i>. In this case, tuning of the equalizer chip is achieved by setting or resetting external pins. These pins enable the selection of different transfer functions for the equalizer. This process may work in certain situations with external test equipment selecting the correct settings on the chip, but the ideal solution is where the chip tunes the equalizer parameters itself. This is referred to as <i>adaptive equalization</i>. <i>Adaptive equalization</i> changes the transfer function of the equalizer to automatically cancel the attenuation caused by the cable.  <p>It is also worth noting that some receiver chips with fixed equalization claim that they a suited for particular length cables. This claim, while partially true, ignores the variation associated with different cable technologies. This variation is clear from Figure 10 which shows different levels of attenuation found in 11 different five meter cables from different manufacturers.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image033.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="398" alt="clip_image033" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image033_thumb.gif" width="579" border="0"></a></p> <p>Figure 10 Attenuation versus frequency measured in 11 cables from different manufacturers.  <p><b>MagnifEye</b><b><sup>TM</sup></b><b> Technology from Redmere </b> <p>Our solution to the cable limitations is to do both <i>adaptive de-skewing </i>and<i> adaptive equalization </i>in tandem, i.e. using our patented MagnifEye<sup>TM</sup> technology. This block sits at the front end of our HDMI products. MagnifEye<sup>TM</sup> technology very effectively tunes the receiver to the specific cable connected. This gives the optimal reception of HDMI signals across longer cables and improves operating margins on shorter ones.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image035.gif"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="187" alt="clip_image035" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image035_thumb.gif" width="383" border="0"></a></p> <p>Figure 11 MagnifEye<sup>TM</sup> Technology Block Diagram  <h4></h4> <p>The following section shows the impact of using both <i>adaptive de-skewing </i>and<i> adaptive equalization</i>. It is also clear from the following sequence that both are necessary.  <h4>Importance of Equalization and De-skew</h4> <p>The first scope shot (Figure 12) shows a closed eye when 2.275Gbps data is passed though a 15 meter cable with 300ps of skew.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image037.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="247" alt="clip_image037" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image037_thumb.jpg" width="352" border="0"></a></p> <p>Figure 12 2.275Gbps Data at the end of 15 meters of cable with 300ps of skew.  <p>Clearly there is no chance of recovering this data in this raw state. A standard analog front end would add equalization at this stage to improve the eye. The result of this can be seen below in Figure 13.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image039.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="247" alt="clip_image039" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image039_thumb.jpg" width="352" border="0"></a></p> <p>Figure 13 2.275Gbps Data at the end of 15 meters of cable with 300ps of skew with adaptive equalization applied  <p>This signal has a wider eye opening but it is clear from the relative eye closure that the subsequent data recovery system would result in many bit errors. Standard front-ends available today are doomed to failure when required to deal with 300ps of skew as equalization is the only tool on offer. Fortunately MagnifEye<sup>TM</sup> technology has another weapon in its arsenal; it also applies <i>adaptive de-skewing </i>to the same data, the result of which is shown in Figure 14.  <p align="center"><a href="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image041.jpg"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="247" alt="clip_image041" src="http://www.hdtvmagazine.com/images/test/GigabitCommunicationchallengesCableTechn_BEDD/clip_image041_thumb.jpg" width="352" border="0"></a></p> <p>Figure 14 2.275Gbps Data at the end of 15 meters of cable with 300ps of skew with <i>adaptive equalization</i> and Magnifye<sup>TM </sup>'s <i>adaptive de-skewing.</i>  <p>Now the data has clear open eyes and is perfectly conditioned for the data recovery block.  <h4>Summary</h4> <p>This article has shown the challenges in sending up to 3.4Gbps through several meters of cable. Two of the key cable challenges, namely "differential skew" and "Inter symbol Interference" have been introduced and their impact on cable performance demonstrated. Both these problems reduce the valid data eye, but on cheaper cables the eye is completely closed and data is rendered unrecoverable. One solution to these problems is to use more expensive cable technology which will typically result in a thicker and less flexible cable. An alternative is to consider embedded silicon combined with lower cost bulk cable.  <p>Redmere's patented MagnifEye<sup>TM</sup> technology is such a solution, combining circuit solutions for each of the problems into one elegant core applicable to a variety of cable applications. Whether the application is a multi-port cable repeater, externally powered cable or a cable powered off internal power, MagnifEye&trade; provides optimal signal integrity for lowest cost cables. With MagnifEye<sup>TM </sup>technology, cable manufacturers can deliver cable assemblies which meet the data requirements of today's market in a cost-effective manner.  <p><b>About the authors</b>:  <p>Dr. John Horan (<a href="mailto:john.horan@redmere.com">john.horan@redmere.com</a>), Chief Technology Officer and a Co-Founder of Redmere Technology. Previously he was an IC Architect with the Wireline Communications Division of Ceva Inc., based in Cork, Ireland. He has authored numerous technical papers and has had 6 US patents issued, with others pending.  <p>Atsuhito Noda (<a href="mailto:Atsuhito.Noda@molex.co.jp">Atsuhito.Noda@molex.co.jp</a>) is Director of New Technology Development for Molex's Global Micro Product Division and is based in Japan. He previously worked in Connector Engineering for 27 years and has 45 patents.  <p>Deirdre Mathelin (<a href="mailto:deirdre.mathelin@redmere.com">deirdre.mathelin@redmere.com</a>) is Product Manager for RedMere's HDMI&trade; semiconductor products and is based in Paris, Francea. She has 21 years semiconductor experience, having previously worked in semiconductor design for Infineon, ST Microelectronics and Ceva.  <p>David McGowan (<a href="mailto:david.mcgowan@redmere.com">david.mcgowan@redmere.com</a>), Applications Manager, based in Cork, Ireland, is responsible for RedMere's high-speed interface semiconductor application development. Prior to RedMere, David worked for Panasonic TV Group, Apple Computer and Ceva.  <p><b>About RedMere Technology</b>  <p>Headquartered in Balbriggan, Ireland, RedMere is an innovator in driving architecture and semiconductor solutions for high speed multimedia interconnect applications for consumer electronics and personal computing markets. For more information, please visit <a href="http://www.redmere.com/">www.redmere.com</a>.  <p><b>About Molex Incorporated</b>  <p>Molex Incorporated is a 69-year-old global manufacturer of electronic, electrical and fiber optic interconnection systems. Based in Lisle, Illinois, USA, the company operates 54 manufacturing facilities in 19 countries. The Molex website is <a href="http://www.molex.com">www.molex.com</a>.  <p><b></b> <p><b></b> <p><b>Contact Details:</b></p>
RedMere Technology Ltd.,<br />
2B Fingal Bay Business Park,<br />
Balbriggan,<br />
Co. Dublin,<br />
Ireland<br />
Tel: +353 1 841 0920<br />
Fax: +353 1 690 4196<br />
<a href="http://www.redmere.com">www.redmere.com</a><br />
</p><p>
Molex Japan Co. Ltd.,<br />
1-5-4 Fukamihigashi,<br />
Yamato,<br />
Kanagawa,<br />
242-8585 Japan<br />
Tel: +81 46 261 4500<br />
Fax: +81 46 264 1470<br />
<a href="http://www.molex.com">www.molex.com</a><br />
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>RedMere/Molex</b>, <b>December 19, 2007  7:20 AM</b>
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
			<?=getComments(813)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('RedMere/Molex', 813)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About RedMere/Molex</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/12/gigabit-communication-challenges-cable-technology-semiconductors-to-the-rescue.php" type="text/javascript" charset="utf-8"></script>
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