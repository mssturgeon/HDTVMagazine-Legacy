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
		AND e.entry_id = 270";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 270 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 270 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 270";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/12/to-subsidize-or-not-to-subsidize-that-was-almost-the-question.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 270";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download To Subsidize or Not To Subsidize,  That Was Almost The Question" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="To Subsidize or Not To Subsidize,  That Was Almost The Question" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="To Subsidize or Not To Subsidize,  That Was Almost The Question" />
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
	<title>HDTV Magazine - To Subsidize or Not To Subsidize,  That Was Almost The Question</title>
	<meta name="keywords" content="analog spectrum, uhf channels, analog digital, unused uhf, signal providers, spectrum, government, hdtv, digital, transition, subsidy, analog, channels, money, those, pay, profit, public, auction, paid, broadcasting, fcc, least, cost, nothing" />
	<meta name="description" content="On our &quot;Tips List&quot; – an email forum for those seriously interested in HDTV - a question concerning the proposed Federal &quot;subsidy&quot; for ATSC tuners (to the needy) was hotly argued. One side said it was another example of inept government giving the store away and the other claimed that it was already paid for out of auction money from the analog spectrum, and, while still terribly odious, it was at least comprehensible. The argument was the only thing on target with responses being deeply shaded by political persuasions so, I decided to wade into the conflagration with a little historical memory and perspective. Shane, my stalwart partner in Internet affairs, said he had seen the topic hashed out on other forums with little more then hysteria and hyperbole waging war with one another and would I kindly arrangement my &quot;Tips&quot; comments into an article for wider distribution. With this introduction I hope what follows—the unedited Tips responses—will serve as that article.

The HDTV initiative of 1987 wound up freeing from non-use a large chunk of prime broadcast spectrum. Prior to that the unusable &quot;taboo&quot; or separation channels were essential to protect analog TV transmission from otherwise unavoidable adjacent channel interference. The taboo channels acted as buffers between most of the VHF channels and contained no other useful content, though they did consume a significant part of the broadcast spectrum.  

The way to make use of the buffer spectrum along with many unused UHF channels was to transition from analog to digital broadcasting by both broadcasters and the public alike. The analog spectrum could then be shut off when all were broadcasting in digital and most-all (at least 85%) of the public were equipped to receive digital signals." />
	<meta name="title" content="To Subsidize or Not To Subsidize,  That Was Almost The Question" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="To Subsidize or Not To Subsidize,  That Was Almost The Question" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/12/to-subsidize-or-not-to-subsidize-that-was-almost-the-question.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="On our &quot;Tips List&quot; – an email forum for those seriously interested in HDTV - a question concerning the proposed Federal &quot;subsidy&quot; for ATSC tuners (to the needy) was hotly argued. One side said it was another example of inept government giving the store away and the other claimed that it was already paid for out of auction money from the analog spectrum, and, while still terribly odious, it was at least comprehensible. The argument was the only thing on target with responses being deeply shaded by political persuasions so, I decided to wade into the conflagration with a little historical memory and perspective. Shane, my stalwart partner in Internet affairs, said he had seen the topic hashed out on other forums with little more then hysteria and hyperbole waging war with one another and would I kindly arrangement my &quot;Tips&quot; comments into an article for wider distribution. With this introduction I hope what follows—the unedited Tips responses—will serve as that article.

The HDTV initiative of 1987 wound up freeing from non-use a large chunk of prime broadcast spectrum. Prior to that the unusable &quot;taboo&quot; or separation channels were essential to protect analog TV transmission from otherwise unavoidable adjacent channel interference. The taboo channels acted as buffers between most of the VHF channels and contained no other useful content, though they did consume a significant part of the broadcast spectrum.  

The way to make use of the buffer spectrum along with many unused UHF channels was to transition from analog to digital broadcasting by both broadcasters and the public alike. The analog spectrum could then be shut off when all were broadcasting in digital and most-all (at least 85%) of the public were equipped to receive digital signals." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=270', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/12/to-subsidize-or-not-to-subsidize-that-was-almost-the-question.php">To Subsidize or Not To Subsidize,  That Was Almost The Question</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>December  9, 2005</b>
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
				<p><em><strong>On our "Tips List" - an email forum for those seriously interested in HDTV - a question concerning the proposed Federal "subsidy" for ATSC tuners (to the needy) was argued. One side said it was another example of inept government giving the store away and the other claimed that it was already paid for out of auction money from the analog spectrum, and, while still terribly odious, it was at least comprehensible. The argument was the only thing on target with responses being shaded by political persuasions so I decided to wade into the conflagration with a little historical memory and perspective. Shane, my stalwart partner in Internet affairs, said he had seen the topic hashed out on other forums with little more then hysteria and hyperbole waging war with one another and would I kindly arrange my "Tips" comments into an article for wider distribution. With this introduction I hope what follows-the unedited Tips responses-will serve as that article.</strong></em> _Dale</p>

<p>The HDTV initiative of 1987 wound up freeing from non-use a large chunk of prime broadcast spectrum. Prior to that the unusable "taboo" or separation channels were essential to protect analog TV transmission from otherwise unavoidable adjacent channel interference. The taboo channels acted as buffers between most of the VHF channels and contained no other useful content, though they did consume a significant part of the broadcast spectrum.  </p>

<p>The way to make use of the buffer spectrum along with many unused UHF channels was to transition from analog to digital broadcasting by both broadcasters and the public alike. The analog spectrum could then be shut off when all were broadcasting in digital and most-all (at least 85%) of the public were equipped to receive digital signals.  </p>

<p>Considering that all media was abandoning analog for digital the view by technology developers and entrepreneurs was unalterably that broadcasting would profit by going digital. The spectrum was reorganized and the once unusable taboo frequencies, now factored back in along side unused UHF channels, provided broadcasters with two 6 MHz channels, one being the transition channel broadcasters would finally return to the FCC for sale and reassignment to other digital services. The revenue from the sale or auction will go to the government almost as a gift (estimated in value between 10 and 70 billion dollars)from HDTV. </p>

<p>While no one disputes that the transition to H/DTV has been a government managed affair public cooperation has always been needed for its completion. Since 100% public cooperation can never be expected an incentive plan for those least-likely to act (acquire digital reception devices on their own) was proposed. This plan adopts a form of subsidy as "the greater good" in service to the transition. In this view an incentive (subsidy) is arranged and paid for by borrowing from the future spectrum auction proceeds. </p>

<p>While many see the subsidy in pejorative terms (another damn giveaway!) an alternative view is that the subsidy is a "cost-of-doing-business" and not a federal screwing. The fact that abuse may occur from the hands of our wealthier citizens seeking something for nothing is a deep shame upon those citizens but not the result of a flawed policy. A recent Internal Revenue Service study reminds us that "the vast majority of Americans are honest people who pay their taxes timely and accurately". There is no reason to predict any different behavior with an open administration of this ATSC tuner subsidy. The incorporation of a "means test" for calculating who is 'qualified and who is not' is higher than the cost associated with abuse. Neither approach would compare in scale to the catastrophic cost of not seeing the transition completed. <br />
 <br />
Some 25% of the recovered analog spectrum has now been set aside for Homeland Security to provide spectrum for critical communications (especially needed during emergencies). </p>

<p>Editor's Note: <em>The recommendation for completing the transition using subsidies paid for by the auction revenue was first published by HDTV Newsletter in our April, 1996 issue. </em></p>

<p><strong><em>After posting this response above another comment came in saying that the government never knew how to profit from anything and that provoked a bit more commentary from yours truly: </em></strong></p>

<p>The government's direct investment in HDTV was and is zero dollars (all cost was born for the development, testing, and market introduction by the HD proponents, manufacturers, and the signal providers). When DARPA tried to insert the Federal government into the process and invest $3 million in a domestic HDTV projector technology the chief of DARPA, Dr. Craig Fields, was publicly humiliated and fired for the crime of advocating the government's investment in a specific technology (he was charged with trying to institute an "industrial/technology policy"). If a profit is defined as return on investment the government has not, and will not, make a profit on HDTV because they have absolutely nothing invested in it. Richard (Dick) Wiley (himself a former FCC Chairman and partner in the K Street legal firm Wiley, Rein and Fielding) did all of the coordination work that created the standard ... and all pro bono as private citizen. Come to think of it no one deserves such a windfall except the owners of the spectrum, which must be you and me, and we paid NOTHING for it. The idea that the broadcasters got a windfall with an extra 6 MHz handed to them is also laughable. What they got was a temporary mandated license to pay two power bills for the same or lesser results they had with one and to pay for the capital equipment they, and I do mean the bulk of them, had no desire to pay for nor even to get involved. The networks drove it a bit because they did not want to be the only program/signal providers who were not able to upgrade their quality easily. So, they had a lot of spectrum issues which actually fell to the large body of broadcast owners (not the nets) to pay for. The networks had some lab work, consultations, and HDTV film transfers along with some extra satellite distribution costs. (That has now grown, of course, to include all of their sports outfitting, which is considerable). The government got the first big (and unearned) guaranteed return on HDTV by inserting the spectrum auction deal into the process and EVERYONE understood the windfall that it was. That remains chief among several reasons so many continue to push things in Congress...so they could get at that "free money." I know many of the Congressmen involved and their thinking was predominantly on the "windfall gain" and this argument of saving Homeland Security via acquisition of some of the reclaimed spectrum became an afterthought (which has proved to be a convenient public issue upon which to hang hats), but it was the "free" money that they had first coveted. There are enough watchdogs in the press who know the real story and would never let-up on any legistlators who "lost" that money through reckless legislation. Everyone in Washington understood that "money to the government" was the biggest understandable reason to move the transition forward. I hope no one thinks that all of the legislation and FCC rulings moving it forward was due to some homage being paid to Sony or Panasonic or Samsung or to CBS or anyone else involved. It was to get the spectrum back, sold, and re-deployed, period. Now, not in defense nor condemnation of that fact it must be recognized that without the FCC's Congressional mandate to set (endorse what was handed to them)the national broadcasting standard we would not have one HDTV system to work with, but hundreds (as you do in computers), or more likely none at all (since the manufacturers considered it far too risky to move forward into the market without an FCC mandate). I suppose we can say the government earned a commission in their roll as "igniters" of the industry due to their unique roll in both setting and mandating the use of digital broadcasting. The underlying reason for doing it all, and no one is saying this because in this nation we only recognize money as chief motivator, is to elevate our standard of living and initiate a new visual era upon which new social values can be seen and adopted. That is what the founders of HDTV understood and they used everything else to move it forward since those values are the least comprehendible. But if only a tiny bit of this last is true, the profit to the nation (and perhaps the rest of the world) is enormous-to-phenomenally enormous. It may take many years before those more esoteric values can be quantifiable, but, as with every other advance in communications, there has been a corresponding advance in economics, if nothing else. _Dale Cripps</p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>December  9, 2005  8:38 PM</b>
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
			<?=getComments(270)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 270)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/12/to-subsidize-or-not-to-subsidize-that-was-almost-the-question.php" type="text/javascript" charset="utf-8"></script>
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