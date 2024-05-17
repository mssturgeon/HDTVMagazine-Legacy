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
		AND e.entry_id = 113";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 113 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 113 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 113";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_2001_richard_wiley_chairman_of_acats.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 113";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS" />
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
	<title>HDTV Magazine - INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS</title>
	<meta name="keywords" content="private sector, richard wiley, copy protection, going happen, congressional meetings, hdtv, going, think, see, programming, need, people, those, get, cable, being, good, fcc, big, chairman, say, digital, wiley, issues, industry" />
	<meta name="description" content="&lt;strong&gt;HDTV Magazine: What are the most important unresolved issues with respect to the H/DTV movement today at the end of 2001?&lt;/strong&gt;

Richard Wiley: There are some major impediments for the digital television transition. I would identify four. Some of them are on the way to being greatly improved. One (of those) is the equipment. We now have over 350 models in all sizes and shapes, all of the highest quality. The prices are falling faster than anyone expected. The chicken in the 'chicken and egg' dilemma is being solved as we go.  Now the egg--that being the programming--is the biggest impediment left. The compelling programming is still in short supply. CBS has clearly done a great job (with their prime time). I am pleased as punch to see ABC coming along. I would like to see Monday Night Football a part of it. I don't understand the plan for the others. When NBC went to all-color in the 60s the whole transition to color took off I would love to see that kind of leadership once more. Obviously, going to HDTV is something they must decide for themselves. I am just a K Street lawyer. I do know this: Without compelling programming we do not have a driving force.
" />
	<meta name="title" content="INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_2001_richard_wiley_chairman_of_acats.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;strong&gt;HDTV Magazine: What are the most important unresolved issues with respect to the H/DTV movement today at the end of 2001?&lt;/strong&gt;

Richard Wiley: There are some major impediments for the digital television transition. I would identify four. Some of them are on the way to being greatly improved. One (of those) is the equipment. We now have over 350 models in all sizes and shapes, all of the highest quality. The prices are falling faster than anyone expected. The chicken in the 'chicken and egg' dilemma is being solved as we go.  Now the egg--that being the programming--is the biggest impediment left. The compelling programming is still in short supply. CBS has clearly done a great job (with their prime time). I am pleased as punch to see ABC coming along. I would like to see Monday Night Football a part of it. I don't understand the plan for the others. When NBC went to all-color in the 60s the whole transition to color took off I would love to see that kind of leadership once more. Obviously, going to HDTV is something they must decide for themselves. I am just a K Street lawyer. I do know this: Without compelling programming we do not have a driving force.
" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=113', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_2001_richard_wiley_chairman_of_acats.php">INTERVIEW - 2001 - Richard Wiley, Chairman of ACATS</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 24, 2005</b>
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
				<p><strong>This interview with Richard Wiley was conducted by Dale Cripps and first published in HDTV Magazine in December of 2001</strong></p>

<p><em>No one has made more of a contribution to the HDTV movement than has Richard E Wiley. Dick Wiley is a former Chairman, Commissioner and General Counsel of the Federal Communications Commission (1970-77) and is now a senior partner in the Washington, D.C. law firm of Wiley, Rein & Fielding. During his time with the Commission Dick played a leading role in fostering new competition and less regulation. </p>

<p>Consistently recognized as one of the nation's 100 "most influential" lawyers by The National Law Journal, he has also been the subject of recent profiles in the New York Times ("Telecommunications' Ubiquitous Man of Influence"), the American Lawyer and the National Law Journal. He was the 1996 recipient of the Electronic Industries Association's Medal of Honor and was recently admitted into Broadcasting & Cable magazine's Hall of Fame. Since 1987, Mr. Wiley served as Chairman of the FCC's Advisory Committee on High Definition Television (ACATS). </p>

<p>As pro bono Chairman of ACATS he drew together a blue ribbon group of industry giants from manufacturing, signal providers, and programming to produce what has come to be known as the ATSC standard.  </p>

<p>We talked this week by phone and covered a wide range of HDTV-related issues including the government's increasing support for the transition. </em><br />
_________________________________________<br />
 <br />
<strong>HDTV Magazine: What are the most important unresolved issues with respect to the H/DTV movement today at the end of 2001?</strong></p>

<p>Richard Wiley: There are some major impediments for the digital television transition. I would identify four. Some of them are on the way to being greatly improved. One (of those) is the equipment. We now have over 350 models in all sizes and shapes, all of the highest quality. The prices are falling faster than anyone expected. The chicken in the 'chicken and egg' dilemma is being solved as we go.  Now the egg--that being the programming--is the biggest impediment left. The compelling programming is still in short supply. CBS has clearly done a great job (with their prime time). I am pleased as punch to see ABC coming along. I would like to see Monday Night Football a part of it. I don't understand the plan for the others. When NBC went to all-color in the 60s the whole transition to color took off I would love to see that kind of leadership once more. Obviously, going to HDTV is something they must decide for themselves. I am just a K Street lawyer. I do know this: Without compelling programming we do not have a driving force.</p>

<p>You and I tend to be nuts about HDTV. I will say this about it: It IS something different; It IS a different viewing experience. We have a lot of channels here in the United States. I can understand why the Europeans are not focusing upon HDTV as they still seek more channels. But just more channels here in the United States is not going to get it for me in my home. </p>

<p>What I want is a different level of entertainment. Clearly, HDTV offers that. I have a HDTV set. I can "feel it" in my own eyes and ears. The sound and the picture are just fabulous. But we need (more) sports. We need (more) movies, which goes to the third impediment. </p>

<p>We need a solution to the copy protection issue. I know everyone is working on it. But they need a solution that covers broadcasting too (in addition to satellite and cable). I am not an expert in this field, but they need something (a watermark, perhaps) that will allow for it. I have some sympathy for Hollywood on this (problem of unauthorized copying). The fact is that with HDTV you can make perfect copies at home. Content providers have a right to be concerned over that, but we need to find a solution.</p>

<p><strong>Do you think a solution is on the horizon?</strong><br />
 <br />
Progress is being made. People understand the problems better. There is still one more (unresolved) major impediment--"cable interoperability" and/or compatibility. It is not an inconsequential purchase for most of us when we spend $2 or $3 thousand on a new set. We should expect to plug that into a wall just as you can now with the analog TV. </p>

<p>Cable has to also step-up. HBO is giving us nearly 14 hours a day of HDTV programming. In most markets, however, you need to get that wonderful programming via satellite dish. In my estimation that is unfortunate </p>

<p><strong>Why is that unfortunate?</strong><br />
 <br />
Nearly 70% of the population are already hooked to the cable. It is a big impediment to the buyer of that wonderful HD equipment when the salesman responds to his/her questions about cable compatibility with "well, not in most markets." That has to be an inhibition for people who then think that they must install either a satellite or terrestrial antenna. </p>

<p><strong>We find that people are willing to make those installations once they are aware of the great benefits to come with HD.</strong><br />
 <br />
Yes, true, but it is a little "retro." We have moved beyond the day of antennas. Yes, I have an antenna and was one of those willing to do it. But I would like to see more programming and be able to get it in an all-transmission media.</p>

<p>Gary Shapiro (president of the Consumer Electronics Association) is quite correct in saying that digital sales are going up every month. I think that is exciting. It still pales in comparison to the sales of legacy analog sets and boxes that don't pass through HDTV programming. That is why I would love to see the industry ON ITS OWN put digital tuners in a growing number of the big sets.</p>

<p><strong>Do you embrace the National Association of Broadcaster's position that a digital tuner should be mandated by the FCC into every new set sold?</strong><br />
 <br />
The question of being forced to do it is another matter. I would like to see the industry phase it in first in the big sets. That would be a very good thing.</p>

<p><strong>From our field investigations it looks like big-screen SDTV sets are losing ground and buyers are increasingly turning to the HDTV big-screen sets.</strong><br />
 <br />
That is good to hear. I think sales are beginning to pick up, but we do need more programming, cable operability, and the copy protection matters settled. Those are the big three (impediments).</p>

<p><strong>Can you give us some background on the closed Congressional meetings that have been held recently in Washington, D.C.?</strong><br />
 <br />
To his credit Congressman Billy Tauzin (R-LA), joined by Congressman Fred Upton (R-MI), John Dingell (R-MI)., and Ed Markey (D-MA), and Cliff Stearns (R-FL)--the five most important people in the communications arena--have been having round table discussions with leading members of the industry. They have asked a series of questions like,"What's the problem? How can we solve those problems?" </p>

<p>They are not brow-beating anyone. They are constructive in the extreme. These guys should get all the credit in the world for showing the interest they have. At the last meeting they sent for FCC Chairman Michael Powell. The Chairman heard two hours of questions and all of the discussion. To his credit he brought along Rick Chesson (who heads the FCC DTV Task Force.) You get the feeling that government, be that the Commission, the Commerce Department, and Congress all want this transition to get going.</p>

<p><strong>What are the current goals of these Congressional meetings?</strong></p>

<p>Clearly to find out what the problems are. Once those are identified they are then rying to encourage private sector solutions, or, as Rep. Tauzin has publicly put it, "If they can't get a private sector agreement, Congress will consider if some kind of legislation will be necessary."</p>

<p>The point is, Dale, that we have committed the country to a digital television future.</p>

<p><strong>Can we raise this (transition) to the level of a national agenda, formal or not?</strong><br />
 <br />
I don't think it needs be quite so dramatic an action but I do think it has got to move. We have had five years with stops and starts. We had the big concern over progressive and interlace scanning. That is gone. We had the big concern about COFDM and 8-VSB. That is gone. The industry is together. There doesn't seem to be any technical or philosophical dispute. It is only a question of getting these remaining issues, which everyone agrees need to be resolved, to actually get done. I think the FCC is committed (to that). I commend Chairman Powell for his leadership. I think the Congress is committed to it. I praise them. I am more encouraged now than I have been in a long time. I can be accused of being an optimist in these things. You can read a lot of negatives such as, "There is no market there. It is not going to happen." You have seen it all.</p>

<p> <strong>I don't see how that point-of-view can prevail with any kind of correct investigation. </strong><br />
 <br />
It is going to happen! Who we kidding? We are not going back to analog. You have these 225 stations on the air. They may be doing upconversions now, but they are out there. They put a lot of money into it. All of these people who are now producing programming and the public is beginning to see it. It is going to take off. </p>

<p>Then there is a payoff down the road which we have not talked about yet. The tie-in of the Internet and the TV set--the tremendous profusion of data services that will be out there...</p>

<p>I am a unreconstructed HDTV fan, but I want to say that data will be a killer app of the future as well. </p>

<p><strong>As well? But not in the replacement of HD?</strong><br />
 <br />
The thing that moves the market is high-definition. It is something the public has not seen before in their living room. Everyone I show my 64 inch HD set want to get it.</p>

<p>Then they ask, "How much of this programming is there?" You have to say, "well, CBS has done a heck-of-a job of giving us sports every weekend until Christmas." We need to do that at the other networks as well. I certainly don't make the business plans for them but they have to step up to the plate. I was pleased to see NBC announce the Olympics in conjunction with HDNet. I would like to see Fox do the SuperBowl in HDTV and not just in 480 digital. That (480P) is good, but not good enough, frankly. The 720P, if one wants progressive scanning for sports, is what they have to do. </p>

<p><strong>How can we effectively influence Fox in this direction?</strong><br />
 <br />
Visibility is one means. The Congress understanding what is being done and what is not being done (will help). Rick Chesson (at the FCC) is spending full time on DTV. He is a very competent staff member. This is his full time job due to Chairman Powell's leadership. He has the resources, like Dr. Robert Pepper, Bruce Fransa, and Amy Nathan and a lot of other veteran FCC staff members who understand these issues. They are meeting with the industry and asking why these issues are not resolved.</p>

<p>I will not be naive. These issues are not going to be resolved tomorrow. But if we are in the same place a year from now you are going to see some legislation. Those people (government) are determined to make this happen. Anyone who thinks it will be drifting like it has been will be dissuaded of that notion.</p>

<p><strong>What about the White House? What should they or can they do to aid this transition?</strong><br />
 <br />
I don't think it is the White House's issue right now. But I do think it's chosen instrument--the FCC--has the message. I have been over to the Commerce Department. They have the message. The State Department also has the message. So, we have executive branch agencies that are working in harmony with the FCC to find solutions. The bottom line is this. It is not a government problem. It is a private sector problem. The government has set up the frame work. The private sector has to step up.</p>

<p><strong>What can the private sector do of its own? Is it promotional?</strong></p>

<p>The latter is one thing we have not yet talked about. We need, of course, to solve the cable compatibility and copy protection issues, but we may also see this promotional campaign from the combined CEA/NAB effort. No one knows how to promote things better than the broadcast and cable operators and Hollywood. Yet nobody really promotes it. I can't pick up the evening paper and see a star (or other) indicating that this or that program is in HDTV. There is nothing really capturing the public's imagination and telling them that something better is out there for them.</p>

<p><strong>Perhaps you and I should go on a speaking tour.</strong></p>

<p>I speak at weddings, bar mitzvahs, and even funerals to tell the tale! Having put ten years of my life pro bono into it I think I have earned the right to be called a national cheer leader.</p>

<p>I read your publication every day. It is always cheery reminder of what is going on. Even though I say there needs to be more, there is more programming than people think there is. Your publication shows that. </p>

<p><strong>Yes, and when supplemented by DVD the investment is well worth while.</strong></p>

<p>DVD's are fine. I think DVD is a transitional product because it is not as good as the real thing-HDTV. But yes, why not see movies in a better format? What's wrong with that? They are very cheap now. It shows what the DTV sets are going to be. It is the genius of the free enterprise system. We have had a lot of people sell this whole thing short, but it is going to happen.</p>

<p><strong>It appears that Hollywood's biggest possible future is in the home and with HDTV.</strong> </p>

<p>Yes, and you know the digital cinema is coming too. There is no reason why HDTV cannot come to the motion picture theater. But I think you are right. Hollywood is going to get so much more bang for their buck from the fact that their movies are being seen as they were produced--brilliant widescreen 35mm cinematography--which is the HDTV equivalent. </p>

<p>Advertiser will wake up pretty soon. I can't agree with people who say that there is no more money in advertising. The (SDTV) ads look pretty puny when you are watching something like the widescreen SuperBowl. One minute you are watching the wonderful widescreen programming and then you go back to narrow screen in conventional TV. Pretty soon those advertisers are going to say, "Let's make those commercials in HDTV too." </p>

<p>This is the reverse of Gresham's Law. The good is going to drive out the bad (or even the fairly good). Television is awfully good as it is today, but it is not as good as HDTV.</p>

<p>I am not opposed to standard definition either. If broadcasters can figure out, likely at the local level, how to program additional SDTV programs without undermining their major signal, fine. </p>

<p>While there is some of that now, the name-of-the-game today is HDTV. I thought it was in 1989. I think it is today in 2001. You and I will be proved right ultimately. We have had a lot of nay-sayers who think we are crazy. You bring the public into my living room and then tell me they are crazy. The people who see it say, "I would like to have one of those."</p>

<p><strong>Thank you Dick.</strong><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 24, 2005  2:00 AM</b>
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
			<?=getComments(113)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 113)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_2001_richard_wiley_chairman_of_acats.php" type="text/javascript" charset="utf-8"></script>
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