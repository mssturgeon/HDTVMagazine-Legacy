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
		AND e.entry_id = 551";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 551 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 551 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 551";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/02/coalition-pledges-to-alert-consumers-about-transition.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 551";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Coalition Pledges to Alert Consumers About Transition" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Coalition Pledges to Alert Consumers About Transition" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Coalition Pledges to Alert Consumers About Transition" />
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
	<title>HDTV Magazine - Coalition Pledges to Alert Consumers About Transition</title>
	<meta name="keywords" content="digital television, dtv transition, consumer electronics, civil rights, transition coalition, transition, television, digital, coalition, consumers, analog, public, dtv, association, consumer, signals, information, air, national, president, february, new, sets, wiley, electronics" />
	<meta name="description" content="The process of educating the public about the shut off of analog television in 2009 has begun in earnest. All analog TV broadcasting from terrestrial towers will come to an abrupt and permanent end on February 17, 2009. At the same time each broadcaster is federally mandated to deliver at least the equivalent digital signal as a replacement for the shut off analog. As you will see from the press release below there are upward of 20 million people who remain entirely dependent upon over-the-air analog television signals. Few of those dependents know anything about the transition. The goal of the coalition is to educate every one who is wholly or partially dependent and insure that they have the physical apparatus needed to receive and decode digital signals-something congress has provisioned for in the form of a converter box subsidy costing one and one half billion dollars. 

The Fed's provision for the educational component is a paltry $5,000,000--something you can easily blow before half-time at the Super Bowl. Such a skimpy federal allowance will ..." />
	<meta name="title" content="Coalition Pledges to Alert Consumers About Transition" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Coalition Pledges to Alert Consumers About Transition" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/02/coalition-pledges-to-alert-consumers-about-transition.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The process of educating the public about the shut off of analog television in 2009 has begun in earnest. All analog TV broadcasting from terrestrial towers will come to an abrupt and permanent end on February 17, 2009. At the same time each broadcaster is federally mandated to deliver at least the equivalent digital signal as a replacement for the shut off analog. As you will see from the press release below there are upward of 20 million people who remain entirely dependent upon over-the-air analog television signals. Few of those dependents know anything about the transition. The goal of the coalition is to educate every one who is wholly or partially dependent and insure that they have the physical apparatus needed to receive and decode digital signals-something congress has provisioned for in the form of a converter box subsidy costing one and one half billion dollars. 

The Fed's provision for the educational component is a paltry $5,000,000--something you can easily blow before half-time at the Super Bowl. Such a skimpy federal allowance will ..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=551', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/02/coalition-pledges-to-alert-consumers-about-transition.php">Coalition Pledges to Alert Consumers About Transition</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>February 28, 2007</b>
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
				<p><em> The process of educating the public about the shut off of analog television in 2009 has begun in earnest. All analog TV broadcasting from terrestrial towers will come to an abrupt and permanent end on February 17, 2009. At the same time each broadcaster is federally mandated to deliver at least the equivalent digital signal as a replacement for the shut off analog. As you will see from the press release below there are upward of 20 million people who remain entirely dependent upon over-the-air analog television signals. Few of those dependents know anything about the transition. The goal of the coalition is to educate every one who is wholly or partially dependent and insure that they have the physical apparatus needed to receive and decode digital signals-something congress has provisioned for in the form of a converter box subsidy costing one and one half billion dollars. </p>

<p>The Fed's provision for the educational component is a paltry $5,000,000--something you can easily blow before half-time at the Super Bowl. Such a skimpy federal allowance will have to be very efficiently used to attract centers-of-influence who, unaided, can swing public views and opinions. </p>

<p>Once again the familiar name of Richard E. Wiley has appeared in a key leadership role.  At no cost to government or public Dick shepherded the H/ DTV standards setting process.  After nine arduous years of pro bono work (1987 to 1996) he gave to us the ATSC terrestrial HDTV transmission standard, without which HDTV would not have been launched in this or any other country (besides Japan). Wiley has demonstrated an extraordinary, if not entirely uncanny, talent for leadership throughout his illustrious career. Most every post filled by him has been that of Chairman (including a stint as Chairman of the Federal Communications Commission). It is hard to imagine any other with more prestige, related experience, political capital, and personal appeal for leading the public to digital television than you have with Dick Wiley. Such attributes will be critically important when calling upon the voluntary assistance of all the "educators" that will be needed from all walks of life. A call from Dick has always mobilized forces and set armies marching. Being from the law profession Wiley is a neutral and benevolent business force that can cross all boundaries without raising competitive reactions. While Dick may be reluctant to embrace a new post for such a challenging work (he also leads the law firm of Wiley Rein LLC in Washington DC in his "spare" time) by his own admission he has been himself led by a passion for establishing H/DTV. The patriotic component embedded in the transition (Homeland Security gets 25% of the analog frequencies being returned to the FCC for reassignment) will likely prove irresistible to him and to those whom he must call upon. It's a great day when one can do what they love and it is even richer when there is a sense of national duty being also realized.</em> _Dale Cripps</p>

<p></p>

<p><strong>Countdown to February 2009:  Digital Television Transition (DTV) Coalition Pledges to Alert Consumers About Transition <br />
From Analog to Digital TV<br />
</strong></p>

<p>New Website, <a href="http://www.DTVtransition.org">www.DTVtransition.org</a>, to Help Consumers Navigate the DTV Transition </p>

<p>Washington, D.C.- A diverse coalition including representatives from private industry, trade associations, civil rights organizations and community groups plus the National Telecommunications and Information Administration (NTIA) today announced that they will work together on a comprehensive consumer education campaign to increase awareness of the nation's transition from analog to digital television, which will be completed on February 17, 2009.</p>

<p>In a recent survey of over-the-air viewers conducted by the National Association of Broadcasters (NAB), 56 percent of respondents reported that they have "seen, read, or heard nothing" about the transition to digital television, and only 10 percent were able to guess that the transition would occur in 2009.</p>

<p>Nearly 20 million households that rely solely on over-the-air television signals will be affected by the end of analog broadcasting on February 17, 2009. Millions more households that receive over-the-air signals on secondary TV sets will also be affected.  About 96 million consumers subscribe to a cable or satellite service and should continue to receive the broadcast signals through their subscription service. </p>

<p>The mission of the DTV Transition Coalition is to ensure no consumer is left without broadcast television due to a lack of information about the transition.  The privately-funded campaign will use basic marketing and public education strategies to help television viewers better understand the nature of the transition, become educated about the changes that will occur before February 2009, and provide information about steps consumers may need to take to maintain their over-the-air television signals.  </p>

<p>One of the first components of the DTV Transition campaign is the launch of a website - <a href="http://www.DTVtransition.org">www.DTVtransition.org</a> - to help consumers learn about options they have to navigate the transition to digital television. The site provides basic information about the transition and offers links to a wide variety of additional industry resources to help answer basic questions.</p>

<p>The DTV Transition Coalition's founding members include: </p>

<p>-Association for Maximum Service Television (MSTV)<br />
-Association of Public Television Stations (APTS)<br />
-Consumer Electronics Association (CEA)<br />
-Consumer Electronics Retailers Coalition (CERC)<br />
-Leadership Conference on Civil Rights (LCCR)<br />
-LG Electronics<br />
-National Association of Broadcasters (NAB)<br />
-National Cable & Telecommunications Association (NCTA)</p>

<p>"The work of the DTV Transition Coalition will be critical to ensuring that Americans have the information they need to make the transition to digital television "said Richard E. Wiley, former Chairman of the FCC and its DTV advisory committee, who led the establishment of HDTV in the U.S. "This coalition has the breadth and scope to reach all consumers who will be affected by the DTV transition, including under-served communities." </p>

<p><br />
Quotes from speakers at today's press conference are below:</p>

<p> <br />
"NTIA looks forward to working with the Digital Television Transition Coalition to bring the benefits of the transition to all Americans. We encourage others to join the Digital Television Transition Coalition and to reach their members for a successful national digital transition."-- Meredith Baker, deputy assistant secretary, NTIA  </p>

<p>"We welcome the opportunity to inform consumers about the benefits of the digital television transition. The added value of superior picture quality and more programming choice will soon become apparent to consumers. Right now consumers can watch their favorite High Definition shows -- for free -- with the purchase of an antenna and a new digital set. Newsweek called this "one of the most promising high-tech services of the digital age." Consumers wishing to keep their current analog TV sets may do so by using a low cost, government subsidized digital to analog converter box.  We look forward to collaborating with all the industries to get the message out."-David Donovan, president, Association for Maximum Service Television</p>

<p> "Public television's goal is the preservation of over-the-air television. Rather than the dinosaur some perceive it to be, we believe broadcast television is poised for a big come back.  Consumers will rediscover it as 'wireless television' and make it cool again."--- John Lawson, president and CEO, Association of Public Television Stations</p>

<p>"The transition to digital television will revolutionize the way we communicate and create countless benefits for all Americans. This coalition unites a remarkable group of technology and public interest leaders to ensure that no consumer lacks information about this vital transformation.  The Consumer Electronics Association (CEA) is committed to this effort to educate consumers, retailers, manufacturers and policymakers about the transition to digital television.  In conjunction with this new coalition, CEA will continue its tireless efforts, initiated in 1994, to move the nation into the digital television era.  We are also assisting consumers with an important corollary to the DTV transition -- recycling of TV sets -- with our new myGreenElectronics.org environmental initiative."<br />
--Jason Oxman, vice president of communications, Consumer Electronics Association</p>

<p>"Retailers play a pivotal role in helping consumers understand what products or services they need (or already possess) that will bring the reality of digital technology and content into their homes, and we look forward to working with this important coalition."-- Marc Pearl, executive director, Consumer Electronics Retailers Coalition</p>

<p>"The switch from analog to digital television in 2009 means that some 70 million sets will go dark-and the viewers of those sets, many disproportionately elderly, lower-income, or disadvantaged-may find themselves in the dark as well. That's why the Leadership Conference on Civil Rights, a coalition of nearly 200 diverse civil rights organizations, has joined industry, broadcasters, manufacturers, and federal officials to make sure that this audience knows before the switch that they are eligible for federal vouchers.  The vouchers can be used to purchase a converter box that will let their current analog TV receive the new digital signals."--Nancy Zirkin, vice president and director of public policy, Leadership Conference on Civil Rights</p>

<p>"Over 90 percent of local broadcasters have completed the transition and are already broadcasting in digital, but the public has a longer way to go. As broadcasters, we are 100 percent committed to ensuring that no consumer is left unprepared, by lack of information, for the transition from analog to digital TV."--  Jonathan Collegio, vice president, digital television transition team, NAB</p>

<p></p>

<p><br />
"NCTA shares the goal of this coalition to educate American consumers about the February 2009 digital TV transition so that not one viewer loses their signal because of a lack of information about the transition.  While this is a formidable goal, by utilizing the combined resources of the organizations represented here today, we are confident this campaign will reach far and wide.  We look forward to working with the coalition to develop a series of simple messages that will alert American consumers about this transition and provide specific directions to ensure that their TV sets continue to receive over-the-air signals." -- Rob Stoddard, senior vice president for communications & public affairs, NCTA</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>February 28, 2007  3:27 PM</b>
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
			<?=getComments(551)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 551)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/02/coalition-pledges-to-alert-consumers-about-transition.php" type="text/javascript" charset="utf-8"></script>
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