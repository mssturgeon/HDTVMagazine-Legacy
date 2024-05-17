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
		AND e.entry_id = 117";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 117 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 117 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 117";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_president_cea_2003.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 117";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Gary Shapiro, President CEA - 2003" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Gary Shapiro, President CEA - 2003" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Gary Shapiro, President CEA - 2003" />
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
	<title>HDTV Magazine - INTERVIEW - Gary Shapiro, President CEA - 2003</title>
	<meta name="keywords" content="air antenna, air broadcasting, gary shapiro, aggressive campaign, digital television, hdtv, cable, been, broadcasters, think, antenna, our, satellite, consumer, most, digital, air, time, consumers, industry, product, cea, marketplace, people, agreement" />
	<meta name="description" content="Interviewed by Dale Cripps in the fall of 2003. It is doubtful that HDTV could have a better friend than CEA president, Gary Shapiro. He is a believer that HDTV is both a good thing and that everyone who sees..." />
	<meta name="title" content="INTERVIEW - Gary Shapiro, President CEA - 2003" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Gary Shapiro, President CEA - 2003" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_president_cea_2003.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Interviewed by Dale Cripps in the fall of 2003. It is doubtful that HDTV could have a better friend than CEA president, Gary Shapiro. He is a believer that HDTV is both a good thing and that everyone who sees..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=117', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_president_cea_2003.php">INTERVIEW - Gary Shapiro, President CEA - 2003</a></td>
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
				<p><strong>Interviewed by Dale Cripps in the fall of 2003.</strong></p>

<p><em>It is doubtful that HDTV could have a better friend than CEA president, Gary Shapiro. He is a believer that HDTV is both a good thing and that everyone who sees it, wants it. He sits in a position of power to aid HDTV and while his direction may be influenced by the responsibility he must have to his manufacturing constituency, the public interest remains firm. He is not faint-of-heart and strikes out at the related industries which all must do their part to make HDTV a happening thing. Gary has been in a leadership role in consumer electronics for 20 years. He is a lawyer by training and a manager by experience. He runs one of the largest trade shows in the world--the CES--held annually in Las Vegas. He brings to that venue manufacturers, developers, distributors, retailers, cable executives, DBS executives, broadcast executives, pre-recorded executives, and computer executives to mix with those from  Washington -- the FCC members and key legislators who have their hands in consumer electronics and signal policy. He was honored this last year with the prestigious Industry Leadership Award from the Academy of Digital Pioneers, an organization formed to honor those who have made important contributions to the digital television movement.  </em></p>

<p>My talk begins with a general assessments of the industry and quickly turns to where things can be made better:</p>

<p><strong>HDTVMagazine: We are approaching the fall season and there seems to be plenty of activity with regards to HDTV. Can you tell us what the CEA's present efforts are?</strong></p>

<p>Gary Shapiro: For several years CEA has been aggressive in promoting HDTV to Americans. I am sure you have been to our HDTV web. What we do here is focus on the macro--the well defined picture. The purpose of CEA is to grow the industry. We have been advocating HDTV for a decade and been at the forefront of many of the issues. There is no question that despite all of the mass media stories about the failure of HDTVwe have, as have you, believed that HDTV was inevitable even though there would be bumps in the road. We feel validated by the fact that HDTV is achieving enormous acceptance by program providers and consumers. Our sales figures on an aggregate basis are very, very positive. This has been a tipping point year. You understand that after having rocks thrown at us for many years about this failed product our faith, like yours, remains unshakable. </p>

<p><strong>I have. I believe you also have some helpful tools for the marketers of HDTV, do you not?</strong></p>

<p>We continue to work with every segment of every industry in many different ways. Our research is something is extensive on what consumers want with HDTV. Indeed, we have seen several of the major program providers join CEA recently, including NBC, ESPN and Discovery Channel and ABC because of the amount of information we are providing on HDTV. </p>

<p><strong>This is hard data your are pulling up from surveys and direct contact with consumers?</strong></p>

<p>We are active in several areas. We do original research on consumer's needs and expectations and opinions. We also do advocacy work on the Hill and with the FCC promoting HDTV as a concept. Our major focus with the government right now is the "plug and play." agreement with the FCC. That is the most critical thing we are working on in terms of advocacy right now. We also have our HDTV Guide, which lists all 450 models of DTV products. </p>

<p>We are also very active with www.Antennaweb.org, which is the web site that lays out the antenna map that is active in retail stores. We think that is an important part of what we do. Obviously our definitions is something we continue to push. We think they have generally been accepted in terms of what HDTV is vs. digital television. We are preparing for the fourth annual Academy of Digital Television Pioneer Awards for recognizing those who are instrumental in digital television. </p>

<p><strong>You and I share awards from that organization for last year, didn't we?</strong></p>

<p>Yes. It is important to recognize people in a positive way in every different category and segment for what they have done to help HDTV happen. As you know the Academy members themselves are the people who have made a difference in H/DTV. They are the ones who vote on the awards, although we did add a People's Choice Award this last time.</p>

<p><strong>Did you see the interview in our past Pagae 2 edition?</strong></p>

<p>Yes, the one with Best Buy.</p>

<p><strong>Bill Cody (BEST BUY) said that the most important thing left  to be accomplished is getting cable fully on board. That leads me back to your comment on the cable "Plug and Play" issue. What opponents do you find in the "Plug and Play" agreement and how are you overcoming them?</strong></p>

<p>Right now the major challenge is getting something out of the FCC. We are very hopeful that we will get something in September. If not it certainly will be tragic. </p>

<p>Our opposition to the mandatory tuner stems from the logic that most of Americans are getting most of their programming by satellite and cable. So, we thought the cable solution was much more critical. Even a government mandate cannot force Americans to put up antennas. We have been very aggressive in promoting antenna usage for several years. We continue to promote it. We would like to see the broadcasters step-up and also promote antenna usage. We are totally puzzled as to how they could focus on mandating TV tuners but in all of their efforts (with the exception of just a few like WRAL) I have yet to see a TV advertisement for over-the-air antenna usage.</p>

<p><strong>Do you have any data that suggests that antenna usage is being adopted by the consumers?</strong></p>

<p>No, I don't. We don't have that data because it is not being adopted by consumers, at least to my knowledge. It is sticking to around 10 to 15% (of TV households.) </p>

<p><strong>Is this due to the view that an antenna is a retro or old fashioned technology as opposed to something more modern?</strong></p>

<p>Seventy plus percent of Americans are relying on cable and fifteen to twenty percent are are relying upon satellite. For services that most of us are paying for it doesn't make sense for consumers to think about antennas unless there is an aggressive campaign to get them to think about it.</p>

<p><strong>There is one thing I find very interesting with our audience. They are quite devoted to watching HDTV and not the lower standard. Most of the HDTV that they are watching, with some obvious exceptions, is from over-the-air services. They have retreated from the "200" old standard cable channels and adopted viewing patterns which are more in line with what the networks are locally providing to them. That would suggest that for many an antenna is sufficient and, perhaps, the only thing they want for their HDTV viewing. </strong></p>

<p>I certainly find that to be the case in my home. I tend to exclusively watch HDTV programs. I do have an over-the-air antenna. </p>

<p>History will review HDTV as the most successful product launch of a major product category. </p>

<p><strong>We do see more and more positive, or, at least, less negative press coverage now occurring where the story ends up favoring HDTV if you have the money. Certainly, the quantity of programs is not considered in short supply, though we can always look forward to more and we see coming online all of the time, so there is no sense that the trend is against you...</strong></p>

<p>This is much better than the launch of color TV. On an inflation adjusted dollar basis HDTV is actually cheaper than color was at the same point in time after its introduction. The quality is also far superior to the analogous quality of color at this point. Color. Being an analog medium it was new and the colors were not that good. Yet, color TV was one of the most successful launches in history. History will review HDTV as the most successful product launch of a major product category. Here you have a product that cost over a $1000 and yet it is rapidly achieving mass market acceptance.</p>

<p><strong>When cars cost $24,000 or more it now does not seem much to spend $1000 on something with which you will spend more time with than your car.</strong></p>

<p>That is probably a good point. When color was introduced a car cost eight to ten times what a color set did. Today it is fair to say that a car is a great deal more. Obviously, that is not the fault of the auto industry. For the most part HDTV is a digital technology with no moving parts. We don't have to go up to 100 MPH. So, comparing the cost to a car may not be the fairest thing, but you do spend as much time, or more, in front of your TV than your car.  </p>

<p><strong>Let's talk about the decoder problem. I call it a   problem because of the ratio of decoders being sold to monitors -- about 11%. What is the cause of this disparity?</strong></p>

<p>_____________________________________________________________________<br />
<em>It has been broadcasters who have been most disappointing.</em> _Gary Shapiro <br />
_____________________________________________________________________</p>

<p>I don't think there is a problem as much as a marketplace a work. As much as we like to think that HDTV is this great, wonderful product which broadcasters are pushing, the truth is that people buying HDTV's do so for one of three reasons. </p>

<p>One is DVDs.Movies look better on HDTV. We all know that it doesn't use the full capability, but DVD does look spectacular on HDTV, plus you get the surround sound. The fact that people are buying monitors to watch their DVDs is the marketplace at work. </p>

<p>The second reason is satellite programming. If you look at DirecTV you can see that the HDTV stations are identified. There are many of them. It started out with just Mark Cuban on channel 198 and HBO. Now you have many more. </p>

<p>The third reason has to do with cable and broadcast programming. Broadcasters have been modest in their offerings, though that is increasing. The local broadcasters clearly have a long way to go. Cable started out very slowly but in the last year, especially the last few months, they are rushing to get to HDTV. It is a competitive issue. They realize that Americans want HDTV and they are losing their best viewers to satellite. </p>

<p>It has been broadcasters who have been most disappointing. They don't talk enough about HDTV. They don't push it. There is not a concerted campaign. It is not aggressive. I know that the National Association of Broadcasters have made some modest efforts, but there is zero promotion of over-the-air antennas. There has been very little promotion across the broadcaster industry. </p>

<p><strong>Can you put your finger on why? </strong></p>

<p>I think it is a tough economic time. It is a competitive model and people are focusing on the last quarter's numbers. You can see the differences in the quarter numbers In the cable and satellite industries because one is losing subscribers to another  because of HDTV. For broadcasters the pain for not being economically competitive with HDTV is similar to the same pain they experienced when asleep at the switch as cable was introduced and by not paying attention to satellite. It is a slower erosion of market share. That erosion is going to continue as it has for another 20 years. It is just going to get worse while being exacerbated by their lack of promotion of HDTV. So, it is a macro long term threat that broadcasters face. It is almost the "AM-ization, " if you will. That is something they have to face. Some, like CBS, specifically, have been very aggressive. That has been helpful. Others have not paid attention or they have made wrong decisions. The biggest wrong decision was Fox's 480p policy, which has now been switched to HDTV. They didn't want to go the way of AM or become the inferior medium. I think that is a very important decision made by Fox president Peter Chermin. I applaud him for it.</p>

<p><strong>Rupert Murdoch said at the May 2003 Congressional hearings on Newscorps' acquisition of DirecTV that HDTV was the only category capable of making the digital TV transition happen</strong>.</p>

<p>The fact that they (Europe) have gone to digital and not HDTV is, I think, a consumer tragedy. </p>

<p>I agree with Mr. Murdoch that HDTV is an incredibly important part of the digital transition.That was not been the broadcaster's view for a long time. There was a lot of talk about these various schemes to make money and monetize the spectrum. But when you read about how Europe...Europe is behind us in that they don't have HDTV. They do have digital. They are transitioning  quickly. I read recently that Berlin, Germany has already returned the spectrum. They are getting spectrum back quicker. From a Washington point-of-view that is very important. The fact that they have gone to digital and not HDTV is, however, a consumer tragedy. The US approach is right for the US and, perhaps, the European approach is best for the Europeans considering all of their different languages, etc.</p>

<p>I think CEA could be persuaded to withdraw that lawsuit if the broadcasters stepped forward with an aggressive campaign aimed at over-the-air antenna promotion. </p>

<p><strong>Going back to the discussion on decoders: The OTA/cable and satellite decoders for the US market are not being proportionally acquired. Are we using all of the promotional tools at our disposal?</strong></p>

<p>No, we are not. There is this lawsuit now in the courts where we (CEA) have challenged the mandatory tuner and the FCC's ability to mandate it. That lawsuit is working its way to an oral argument. I think CEA could be persuaded to withdraw that lawsuit if the broadcasters stepped forward with an aggressive campaign aimed at over-the-air antenna promotion. They have been quiet on promoting over-the-air broadcasting. If they would spend less time trying to put mandates on us and more time on promoting over-the-air antenna I think the marketplace would help out.</p>

<p><strong>We have heard broadcasters say that CEA and its members have given up on over-the-air broadcasting and you're not paying much attention to it.</strong></p>

<p>I can't imagine their saying that considering our aggressive campaign with antennaweb.org and the millions of dollars we have spent promoting it without any broadcaster assistance. I think it defies credulity. Manufacturers are themselves promoting over-the-air broadcasting while broadcasters are, instead, focusing their efforts in Washington (on mandates). </p>

<p>Our industry (CE) has a history of avoiding mandates in Washington. We focus on the marketplace. We know you can't force consumers to buy what they don't want. But you can do cleaver marketing to encourage wants into needs. You can create your product categories. You can create things that were once only an engineer's imagination and turn them into a consumer product whether that is  computer, a personal digital assistant, a wireless internet device, or even HDTV. None of these products came out by way of government. They came through people experimenting and introducing products at the CES, seeing what retailers, consumers, and the press would respond to. A few succeed from the long list of products introduced.</p>

<p>HDTV happens to be one of those products with a tremendous belief by Dick Wiley, Gary Shapiro, Joe Flaherty, Peter Fannon, Dale Cripps, and quite a few others. This was a product that captured our imaginations and we felt it would capture the imagination of the American Consumer, We have been rigid in that belief for ten years. Along the way we fought claims that it was a foreign invasion; that there was a better transmission system, or, that there is nothing in it for broadcasters or anyone else. We fought every step of the way and history has proven that the Flaherty's, the Cripps, the Wileys, and all other believers were absolutely right in their steadfast determination for HDTV. When you look back in history and ask, "What are you proud of?" I am pretty proud of what we did for HDTV. </p>

<p><strong>Getting back to antenna and consumer acceptance: The term "wireless" has grown popular and glamorous. Why is the TV  antenna not made more glamorous today? </strong></p>

<p>There are different flavors of wireless. There is the telephone that has an almost imperceptible antenna compared to the antenna you must put on your roof or in your house for TV. It is much more visible.</p>

<p>If somehow broadcasters can be organized to get together to use that powerful tool to help themselves promote over-the-air broadcasting they will unleash a torrent of potential AND marketplace acceptance of antennas. </p>

<p><strong>Is there no way to camouflage these devices or put them into an artistic form so they become aesthetically pleasing?</strong></p>

<p>A satellite dish is an antenna. There have been various devices to make them more visibly attractive for communities. With satellite radio services you are looking at an antenna on top of your car. I have one. Doesn't bother me at all. It is a question of consumer awareness. Broadcasters control the most powerful tool there is for creating consumer awareness. The whole business model for broadcasting is based on consumer awareness. That is what they are selling to others. If somehow they can be organized to get together to use that powerful tool to help themselves promote over-the-air broadcasting they will unleash a torrent of potential AND marketplace acceptance of antennas. That may help solve many of the their existing problems. I think it is fair to say that the cable industry and the satellite industry are secretly pleased that broadcasters have not figured out that they themselves have the power and ability to change their own future. </p>

<p><strong>That brings us to cable. I did talk to the NCTA which seem to be quite canned, but positive statements: "Yes, it is a good thing to do. It is our future. We are doing everything we can. Are you seeing what they are saying?</strong></p>

<p>I believe the cable industry -- for the majors --there is no question in my mind that they are very interested in HDTV as a competitive tool against satellite. It is in their business interest to be so. They are rushing to it. They don't want to be perceived as an inferior medium. I think the marketplace will be very helpful with cable in getting to HDTV providing there is a "plug and p lay" agreement, which allows consumer electronics companies to invest in a standardized technology. </p>

<p><strong>When I asked you what the major obstruction to this agreement was earlier in this interview you seemed to say that it was the FCC. Why?</strong></p>

<p>I would not say in the short term I would say the FCC is an obstruction. I didn't mean to say that. What I mean to say is that a failure to enact the plug and play agreement is the major impediment in the short term.</p>

<p><strong>What is impeding the FCC from making this decision?</strong></p>

<p>I think you will have to asked them that. They have their due process. In December 2002 we handed it over to them and Chairman Powell assured me and Robert Sachs (president of the National Cable Telecommunications Association) that he would do everything possible to move quickly. I think they did initially and got it out to comment (each rule must go through a process of comments and reply comments prior to an FCC action). Right now it is not going as fast as we want and we are very frustrated. </p>

<p><strong>I have heard that manufacturers want this done by September in order to give them time to fabricate for the next fall buying season. Is that the problem. </strong></p>

<p>Manufacturers are facing the dilemma of a manufacturing decision. While each manufacturer makes its own decision but if there is any risk of a change in that agreement they can't really go into production.</p>

<p><strong>Is there any serious risk in the agreement now?</strong></p>

<p>The agreement is very clear, helpful, and specific for almost all parties. There are those fringes of the "copy anything" people and the "copy nothing" people. Both sides have issues. That means we clearly end up in the middle. We are confident that it preserves consumer's traditional use rights and it protects the copyright interests as they have been protected with satellite. It steps up their protection. I think it is an agreement that is about as acceptable to everyone as will ever be created. It seems to be a slam dunk, but we are just counting the days.</p>

<p>H<strong>Back to broadcasting for a minute. The recent upfront markets (for advertising at the network level) enjoyed a record $9 billion in sales of advertising space. None of that is identified for HDTV or caused by HDTV. Is the problem that all bills are still being paid by the traditional business?</strong></p>

<p>Look at how cable came into the marketplace some 30 years ago. Broadcasters looked at cable and said, "Ah, what do I care about them? Ii is too small a market." In the exact same way cable and broadcasters responded to satellite, video games, VCRs, and everything else. Every time a new media arrives it starts with a small marketplace and they ignore it. Then it grows very big and takes away their marketshare, and, before you know it, they have a new competitor which they should have paid attention to in the beginning and focused on what consumers were asking for. With broadcasters vs. cable consumers were asking for choice. For cable vs satellite they were asking for choice and, I would argue, quality of programming. Now consumers are making a huge investment. The fact that are not buying tuners is not alarming to me, although they are increasingly doing so. They are investing in the largest portion of it--the monitor. They will be looking for programming that can be displayed beautifully on that monitor. </p>

<p>HDTVMag: Thank you Gary.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 24, 2005  2:39 PM</b>
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
			<?=getComments(117)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 117)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_president_cea_2003.php" type="text/javascript" charset="utf-8"></script>
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