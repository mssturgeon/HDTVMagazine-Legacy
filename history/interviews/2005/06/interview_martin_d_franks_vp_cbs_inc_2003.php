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
		AND e.entry_id = 128";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 128 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 128 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 128";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_martin_d_franks_vp_cbs_inc_2003.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 128";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003" />
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
	<title>HDTV Magazine - INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003</title>
	<meta name="keywords" content="doing nfl, copy protection, game week, production equipment, cable compatibility, our, going, transition, marketplace, nfl, think, time, hdtv, people, last, doing, been, first, college, challenge, problem, equipment, good, cbs, game" />
	<meta name="description" content="Martin Franks is in the profession he loves--television. He has the responsibility at CSB for managing the H/DTV transition. At the launch of the new television season we wanted to ask him to size up the progress made from last..." />
	<meta name="title" content="INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_martin_d_franks_vp_cbs_inc_2003.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Martin Franks is in the profession he loves--television. He has the responsibility at CSB for managing the H/DTV transition. At the launch of the new television season we wanted to ask him to size up the progress made from last..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=128', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_martin_d_franks_vp_cbs_inc_2003.php">INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 26, 2005</b>
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
				<p><em>Martin Franks is in the profession he loves--television. He has the responsibility at CSB for managing the H/DTV transition. At the launch of the new television season we wanted to ask him to size up the progress made from last year to this and to see what still needs to be done to spur on the horses.</em>  </p>

<p><strong>My first question:</strong> <br />
<strong>Marty, we are in a new season. What is the first thing that comes to  mind when you think of this new season?</strong></p>

<p> It's a competitive thought. In the last several years it was nice when we didn't have much competition in HD. But as a company who cares a lot about the success of the transition I must say that it is also nice to read my HDTV Magazine in the morning and see that there is so much on in HD.</p>

<p> <strong>It is certainly growing.</strong></p>

<p>It is striking! I saw a note on one of the Internet forums talking about the fact that at one point there were going to be five different HDTV programs on at the same time. I think that is good for the business; it is good for the transition; and I think it is showing up out in the stores, where, by all reports that we receive, people are continuing to buy the product.<br />
 <br />
<strong>Do you think that the charge leveled against broadcasting for several years that there was not enough compelling programming is now fully satisfied?</strong> </p>

<p>No, it is not fully satisfied. It is dramatically better. It helped enormously when ABC came on board. I support Alex Wallau and his colleagues for joining us. It has helped to have NBC come along, particularly with their last minute edition of ER. As a fan of West Wing I keep waiting for them to do the same there… but…our biggest challenge remains to figure out a way to do more of our sports in HD. Just as prime time was the first frontier where we learned a great deal about how to do HD, the next big breakthrough is going to be when we can "regularize" more of our sports in HD. Unfortunately that is financial challenge and a combination of logistical and technological challenge.</p>

<p><strong>How would you characterize those technological challenges, and are they being met? </strong></p>

<p>Having the core digital truck that we use for the football has been a tremendous help. It save a lot of money and improved the quality of the production at the same time. But we need four or five more of those trucks to be available to use from our vendors. From the standpoint of a truck vendor it is hard to commit the capital to that kind of enterprise without a greater assurance that it is going to be used for more than the 20 Saturdays in the fall, but rather used 52 weeks a year.<br />
 <br />
That is one challenge. There are likely two other things which I glean from reading your publication and the AVS forum, which I do regularly, I think a lot of viewers are understandably impatient, but they fail to understand that there is still a ways to go in developing the HDTV production equipment. It is only this fall with our college football that we finally have a HDTV super-slow motion that we are comfortable in using. We still don’t have a first down line that we can project. We want to make sure that our HDTV broadcast are what viewers have come to expect fram  CBS Sports production.   Some of the equipment just doesn't exist. </p>

<p>The  other is the logistical challenge All of these stadiums, including ones that have been built in the last couple of years, were built with coaxial cables. When we are doing an NFL game in a modern stadium there is a plug in the wall at a camera position. They can plug the camera into the coax and there is a drop location in the basement where we can put our truck, and we are on the air.</p>

<p>Now, with very few exceptions, we have to run fiber cables each time we are in a stadium. That is expensive and time consuming. I am in hopes that as new stadiums are built they will do them with fiber. </p>

<p>It would be great to have an NFL game of the week on, and we have hopes of being able to do that in the not-too-distant future, But, I guess what I am talking about in terms of really driving this transition home is not when we are doing one NFL game each week, but that we are doing all of the NFL games. We are not going to completely succeed with this transition until the Jet’s fan who lives in New York gets his Jets game every weekend in HD, not just occasionally have an opportunity to see them when they happen to be the Game of The Week.</p>

<p> <strong>Is the announcement from ESPN going to provide some of the solutions? </strong></p>

<p>I hope. I have not talked to my friends there since their announcement. So far its been mostly ourselves and Mark Cuban doing the stimulation of the production equipment marketplace. So, having ABC come aboard will help. We have long term contracts with truck vendors so even if ESPN's vendors build a couple more trucks they won't necessarily be available to us. It will clearly help, though. Maybe there will be three or four people who want the first down line in HD instead of one the marketplace will be stimulated to produce it more quickly.</p>

<p><strong>Are you being asked to do things by manufacturers or are you asking them?</strong></p>

<p>A little of both. We still spend an enormous amount of time and resource cooperating with manufacturers on testing their equipment and helping to develop their equipment. Our engineers travel extensively to lend their expertise to this process. We do see that as a collaborative effort. When I refer to CBS's leadership it is also the technological developments which we stimulate, starting with Joe Flaherty and Bob Ross, and Bob Siedel, and am very proud of what CBS puts in behind the camera.</p>

<p><strong>At the recent hearings it was said in the opening remarks that the transition is not going as rapidly as many would like it to go? Where is your perception in respect to that statement?</strong> </p>

<p>I think we have made enormous progress in the last year. I testified at the same hearing a year and one half year ago. I said then that the government had to make up its mind. The original transition, while there was the 2006 deadline, was set to be a marketplace driven transition. I said then that the marketplace will sort all of this out, it is just not going to do it by 2006. Now, as we get closer to 2006 and the government has determined that it wants to keep as close to that date as it can it is more appropriate to push the marketplace.</p>

<p>In terms of surprises--I thought that with Rep Tauzin (R-LA) and the Chairman of the FCC Powell round tables--the jaw boning process--was making great progress on copy protection and the cable compatibility issues. I am extremely disappointed that that progress seems to have stalled once again. I know less about the cable compatibility because a) we are not in the cable business, and b) we don't manufacture sets. On the other hand we do like our viewers to have happy and easy viewing experience. </p>

<p>I know a great deal more about the copy protection issue and that is a growing problem for us. Because we are not going to allow our business to be "Napsterized". Again, I am surprised when I read on the web pages and forums people's violent reaction when we are seeking to protect our copyright.<br />
 <br />
<strong>We have been saying that either the problem is not as great as what has been said about it, or it is also a responsibility of the marketplace to not let it be a big problem. In other words, the consumer has a responsibility to you as well as you have some obligations to the consumer.</strong></p>

<p>When we did our deal with Echo star to put our HDTV feeds up, CBS gave a blanket waiver in all of the markets that we own. We thought that was a pretty good faith gesture. But it is pretty discouraging to go and read on the Internet forums people talking about how to steal that signal. <em><u>If you take signal theft to its ultimate extension then there is no incentive to create programming.</u></em> This is one of the impediments to doing the NFL. We have a contractual obligation to the NFL to maintain regions. If those regions can be defeated, guess what? We are not going to get to do the NFL in HDTV.</p>

<p><strong>We have been saying that the public is screwing themselves by doing this.</strong></p>

<p>We know we have an obligation to provide a product, and that is our part of the deal. Tthe broadcast flag was such an elegant solution to me because all it was intended to do was to keep people from pirating product over the Internet. <u><em>It was not meant to even remotely defeat copying at home or even copying on a home network.</em></u> It struck me as a good solution. I am troubled to see it stalled once again and at some point there will be consequences. I tried to get the HD version of a movie recently. The movie will air on the analog network but I could not negotiate an HD version and I could not get it. </p>

<p>I was hard pressed to tell that studio they were making the wrong judgment. Again, go to the message boards. There are people recording those movies and all of a sudden they have a perfect HD digital master of a copyrighted product and if they choose to engage in piracy they have the raw material with which to work. </p>

<p><strong>Isn't this a problem incredibly exacerbated by the statement of every engineer say that everything can be broken. You have no permanent solution, but rather a series of solutions like computer security patches, which seem to download endlessly?</strong> </p>

<p>You can never defeat piracy. We have learned the hard way from DVDs in China. You can, however, make it harder. Sure, eventually some kid in a garage in Cupertino is going to hack the algorithm. But if you look at Nepster, which about hacking the algorithm as much as it became an "in thing" to do on college campuses. Where are the colleges in this whole exercise? The notion that the colleges are allowing their servers and T1 lines to be witting accomplices in piracy is mind boggling. As one who is paying a rather substantial college tuition for a child at the moment it is not exactly a lesson I want him to be learning from his college education.<br />
 <br />
There is a solution to this problem. It will NOT inhibit home recording, home networking by anyone. All of a sudden some of the CE manufacturers have taken this pure position--they want to sell DVD recorders, etc., and I think it is short sided on their part. <br />
So, I am more worried about these issues related to the transition--cable compatibility and copy protection.--and the fact that I thought we were quite close on both scores several months ago. But we seem less close, and all of a sudden there is more programming.   If viewers continue to want shows , like CSI and CSI Miami, then their producers have to have an incentive to make that investment. Part of their incentive is the back-end market--the syndication marketplace. If that marketplace can be eroded via internet piracy, it is not a good thing.</p>

<p><strong>The quality of the programming could only decline.</strong> </p>

<p>Also the picture quality of it. We can meet our obligation to the law by broadcasting a 4:3 480 picture. What a travesty that would be. I like my CSI Miami in 16:9 and 1080i.</p>

<p>But we shouldn't let that cloud things. We have made tremendous progress in a relatively short period of time. As we were first discussing, there are going to be two college footballs games on tomorrow! (speaking of Saturday, Oct 12, 2002). Last night at ten o’clock you could watch Without A Trace or you could watch ER.<br />
 </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 26, 2005 12:26 PM</b>
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
			<?=getComments(128)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 128)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_martin_d_franks_vp_cbs_inc_2003.php" type="text/javascript" charset="utf-8"></script>
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