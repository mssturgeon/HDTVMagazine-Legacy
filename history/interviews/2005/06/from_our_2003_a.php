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
		AND e.entry_id = 65";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 65 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 65 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 65";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_ceo_or_cea.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 65";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Gary Shapiro, CEO or CEA" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Gary Shapiro, CEO or CEA" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Gary Shapiro, CEO or CEA" />
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
	<title>HDTV Magazine - INTERVIEW - Gary Shapiro, CEO or CEA</title>
	<meta name="keywords" content="consumer electronics, encoding rules, gary shapiro, cable compatibility, fcc tuner, hdtv, cable, agreement, fcc, people, consumer, think, any, manufacturers, believe, our, set, mandate, issues, issue, going, rules, terms, gary, question" />
	<meta name="description" content="From our 2003 archives INTERVIEW Gary Shapiro, CEO of Consumer Electronics Association by Dale Cripps Gary Shapiro is a disciplined man. He runs one of the largest trade associations in the world. His Consumer Electronics Show (CES) is one of..." />
	<meta name="title" content="INTERVIEW - Gary Shapiro, CEO or CEA" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Gary Shapiro, CEO or CEA" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_ceo_or_cea.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="From our 2003 archives INTERVIEW Gary Shapiro, CEO of Consumer Electronics Association by Dale Cripps Gary Shapiro is a disciplined man. He runs one of the largest trade associations in the world. His Consumer Electronics Show (CES) is one of..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=65', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_ceo_or_cea.php">INTERVIEW - Gary Shapiro, CEO or CEA</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June  9, 2005</b>
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
				<p><em>From our 2003 archives</em></p>

<p><br />
INTERVIEW</p>

<p>Gary Shapiro, CEO of Consumer Electronics Association<br />
by<br />
Dale Cripps</p>

<p>Gary Shapiro is a disciplined man. He runs one of the largest trade associations in the world. His Consumer Electronics Show (CES) is one of the largest in the nation. He runs in the morning. He is a lawyer by training. One rule he observes is the “20 minute interview” which he learned in a media relations class. That one can leave the interviewer hungry for more. My approach has always been to open  with some generosity and then close in on the tougher points I know we all want answered. Let’s be happy with what we have under Gary's rules, since that is all we have. Is this the year for HDTV? All indicators say it is. </p>

<p><br />
"HDTV is what we believe in."<br />
_Gary Shapiro 2003</p>

<p>DC: What does the HDTV business look like today? Or, do we call it the DTV business?</p>

<p>GS: We use the term “HDTV” around here unless someone forgets. There is a camp of people that I put you, Peter Fannon (Panasonic), and Dick Wiley (former chairman of FCC who headed the FCC Advisory Committee on Advanced Television Services) in who are, among others, the HDTV true believers...and you have been for at least five years. I think most in the DTV Pioneer's Academy would fall into this category. We (CEA) believe in HDTV in all of its glory. There are people at Circuit City and other retailers who would agree and say, “Let’s start with the best.” That was defined for us in some of the format battles going way back around the time of the multicasting issues. But HDTV is what we believe in.</p>

<p>DC: Is it living up to our beliefs?</p>

<p>GS: In many ways HDTV has exceeded our belief in terms of its popularity and in terms of its beauty. It has not come as quickly to broadcasters and cable as we would have hoped. But people love it, and satellite has provided close to enough HDTV. There are announcements showing up every day now about channels coming on. Certainly, sports programming broadcast on the networks has been excellent. In terms of the dollar volume traded at retail, it is huge. We projected 2.1 million units and wound up at 2.4 million units in year 2002. That is beyond our forecast. That is spectacular. So, in terms of consumer acceptance and dollar volume in sales, in terms of consumer appreciation, some of the beauty in the sporting events, and some of the satellite channels it has done terrific. In regards to sale of tuners for over-the-air television, locale stations going to it…it is not proceeding as we had anticipated. I think we were either naïve or optimistic in predicting how local broadcasters would embrace HDTV. In reality when someone is relying on an over-the-air antenna that means they are not a satellite or cable subscriber and suggests that they are the lower income people. That is not the group of people who are most likely to go out and buy a HDTV receiver. So, our assumptions were wrong. We also didn’t assume there would be such a strong DVD market, which would drive HDTV (monitors). Widescreen movies in DVD are clearly the biggest driver of HDTV sales.</p>

<p>DC: Do you think there will be some catch-up in the OTA tuner business and  in your estimation what has been the cause of it not meeting initial expectations?</p>

<p>GS: Looking at the history of it the manufacturers first thought that integrated TV sets were the way to go. Then we had this format question that was raised by Sinclair, and the manufacturers hesitated. They said that they believe in HDTV but "we will sell monitors" until this issue is resolved. Sinclair single-handedly set back the transition to over-the-air HDTV not by one year, as several people believe, but by several years. Manufacturers discovered that people wanted monitors and, for the most part, didn’t want to buy integrated sets. Because they were not buying integrated sets broadcasters held back even after the standards issues was resolved. So, Sinclair was the one that hurt the broadcast transition more than any other entity in the United States. Quite frankly at that time the FCC hesitated and was not very strong in putting down Sinclair. Sinclair caused some real problems and a lot of the blame goes to them for trying to switch the standard to COFDM. It was wrong at the time because Sinclair had an interest, and still does, in delaying the transition to digital. Their public filing discloses the fact that they will benefit as long as analog is around because of their other manufacturing interests. </p>

<p>DC: I understand that the manufacturing business you are referring to is all but out of business and insignificant.</p>

<p>GS: Yes, but at the time Nat Ostroff, their chief technical officer, was out to change the standard. </p>

<p>DC: Where are we standing now in integration vs. non-integration of ATSC tuners into monitors?</p>

<p>GS: The FCC has issued a mandate for tuner integration and manufacturers intend to follow the law. </p>

<p>DC: You are on record for standing in opposition to this FCC tuner inclusion mandate. Some folks went so far as to say that you went over to the dark side. What was that all about?</p>

<p>GS: We opposed the mandate. We continue to oppose it in a law suit because we don’t like mandates in the first place, and in the second place we thought the FCC exceeded their authority. Thirdly, we thought the tuner mandate was the incorrect approach. We think the issue has to do with cable and programming and the marketplace would follow. We have come a long ways towards solving the cable issue. But a mandate which affects 15% of American homes (CEA claims only 15% are dependant on OTA) and have everyone pay, especially with the large patent royalties involved, we continue to think that is improper. Having said that it is the intention of every manufacturer to follow the law.</p>

<p>DC: You mention large royalty figures. Are those numbers outside of the norm?</p>

<p>GS: I don’t know what the norm is. I heard that Zenith is asking $14 or $15 for their royalties and Thomson also has patents. There were five Grand Alliance members who each have patents involved.</p>

<p>DC: So, when stacked up there is significant money involved?</p>

<p>GS: There is a disagreement between the broadcasters and set manufacturers about the cost. Broadcasters talk about the cost of raw materials. That is like pricing a restaurant meal based on the cost of the raw materials of the food.</p>

<p>DC: Where does this suit stand today?</p>

<p>GS: It is in the Federal court of appeals. I don’t believe oral arguments have been scheduled yet. I assume they will be in a few months.</p>

<p>DC: Let’s assume that your appeal is unheard and tuners are included and the cost is burdensome. What does that do to the over-all market pace? </p>

<p>GS: We have not gone that far since that involves a lot of highly complex factors and individual manufacturer pricing and competitive decisions. There is no question that the more a product costs the fewer consumers buy it. What keeps consumer electronics prices low is intense competition among all sorts of features.</p>

<p>"What has made the FCC tuner mandate and law suit less important is the cable compatibility agreement."<br />
 <br />
What has made the FCC tuner mandate and law suit less important is the cable compatibility agreement (where all manufacturers have signed the PHILA agreement with CableLabs). Virtually every manufacturer has indicated that including OTA tuners is not so burdensome as long as they are putting the cable compatible features into the set anyway. Manufacturers will be rushing to meet the cable compatibility standard once there is a clear indication that it is going to be accepted fully by the FCC.</p>

<p>DC: Is there any question?</p>

<p>GS: Not that I am aware of. It was just filed a week or two ago and people are studying it. But in every meeting we have had there is every indication that the policy makers are very happy that the two industries came together on this. There is no question that others have the right to comment on it.</p>

<p>DC: In explaining this agreement to the consumer, how do you sum it up?</p>

<p>GS: In a couple of years you will be able to buy a cable-compatible HDTV set that will work with virtually any cable company in the nation. To wherever you move you can be assured your set will be plug and play.</p>

<p>DC: The only feature that seems to be out of that agreement is the interactive part of it. Where was the bone of contention on that?</p>

<p>GS: It is not a matter of there being a bone of contention. We agreed that it is a complex issue. We said, "Let’s take the biggest bite and develop some of the main principles for one way." We resolved tough issues like the encoding rules and what they would be. All of those would easily apply to 'interactive' as well. 'Interactive' raises other significant issues, such as where the intelligence lay--the cable system or the TV set--and what are the rules when there is intelligence in both places? It’s the question of who is king of the road? What does the set have to respond to? It is a very complex and will take some serious discussions with the cable industry. It is going to take the will on behalf of both industries to resolve these tougher issues and a desire to provide a truly interactive product. That is what is ultimately in the consumer's interest.</p>

<p>DC: It took a while to get to this agreement. What were the sticking points along the way?</p>

<p>If someone boycotts HDTV in this country they do so at their own peril. <br />
 <br />
GS: You have no idea what "a while" it did take. This agreement has occupied 20 years of my life in the consumer electronics industry. In the early 1980s we tried to define a compatible analog set. We had a draft pamphlet which we had agreed upon. We even had a standard in which RCA had invested a considerable amount called multiport. But cable companies didn't follow. There is also a mandate (now) from Congress on this. There were a lot of efforts (leading to the agreement). There is no question that we have here two industries who felt burnt by each other. There were a lot of missteps along the way. The credit goes to a lot of companies involved, especially from Comcast and Mitsubishi, who worked  very hard. They led a team facilitated by CEA and the National Cable Telecommunications Association. They worked through a whole range of highly complex issues. In the end everybody gave a little bit. </p>

<p>What is not part of it (the agreement) is the ability of the cable company to turn off a consumer electronics' product (called selectable output control). This was a very grievous concern to manufacturers. It was part of the licensing agreement that CableLabs was insisting upon. We felt it was potentially very harmful to our consumers. That is now off of the table and will not be a part of any of these agreements in the future. We had to swallow hard and accept the content copyright restrictions which the Motion Picture Association had sought in the 5C license. That was very difficult for us.</p>

<p>DC: What were the most difficult issues?</p>

<p>GS: The issue of content protection, selectable output control were extraordinarily difficult to resolve.</p>

<p>DC: Is the position of the consumer electronics industry that "we don't like anyone but our customers turning off anything for any reason?"</p>

<p>GS: We managed to have an agreement without selectable output control. They have agreed to it.</p>

<p>DC: Were the content people involved with that decision?</p>

<p>GS: The MPAA had sought and obtained things in the 5C license, so they will have to speak for themselves. My guess is that they were pleasantly surprised? Jack Valenti said that he was "pleased" that the CE industry is calling for the FCC to adopt encoding rules. The MPAA has not responded negatively, just that they were pleased that we are calling upon the FCC to adopt encoding rules.</p>

<p>DC: For those who are disciplined on following the rules of "fair use" copying how are the copy protection issues shaping up?</p>

<p>GS: The way it is shaping up is that you will be able to shift content around your home but you can't ship it out over the Internet to other people. </p>

<p>DC: I think our people would be in complete agreement.</p>

<p>GS: There may be an exception in the case of a pay-per-view event where you are paying to see a live boxing match or other value event. (In this case) you are paying to view it once and not to own it. That is what those copyright restrictions, which we have agreed to in the encoding rules, cover. It depends upon what you are watching. If you are watching free over-the-air broadcast, you certainly have the right to record it and view it as many times as you like. With pay-per-view you just have the right to watch it. </p>

<p>DC: How soon do you expect to see a HD-DVD on the market? </p>

<p>GS: My guess is 2004, if not earlier. Joe Flaherty (the father of HD in this nation) is very high on one of these companies.</p>

<p>DC: Are the war jitters causing any contingent plans in manufacturing or marketing to react to any shifting attitudes in this country?</p>

<p>GS: In any business in the United States today the issue of war is a factor--an unknown one. There is probably a greater focus on the economy than on war. We are in a tough time. Everyone is hoping that 2003 will be better. But no one is looking at major growth in any industry. We are hoping for rather modest growth. </p>

<p>With HDTV, though, we expect double digit growth. HDTV is one of the few bright spots in the entire US economy.</p>

<p>DC: There are some of us who believe that HDTV has enough demand going for it that it will sweep other tangential businesses, and those made popular through it, up   and make at least some measurable contribution to the economic recovery of the nation. Does anyone there see it that way?</p>

<p>GS: I think it is a positive factor. I would not say that it is going to lift up the whole country alone. HDTV is certainly a bright spot.</p>

<p>DC: What can government do to advance the cause of HDTV?</p>

<p>GS: Right now we are hoping that the FCC will move forward rapidly with the cable compatibility agreement. That is the biggest thing in the short term. In the long term I think we have to hope there will be something worked out between the broadcasters and the cable companies on 'must carry.' If not, that is something the government may have to step in on. Other than that I am not sure of the appropriate role for government right now. The broadcast flag issue has to be resolved.</p>

<p>DC: CBS has threatened to withdraw a year from HDTV delivery if no agreement is reached on a "broadcast flag." What is your comment upon that filing from Viacom which we reported on a few weeks ago?</p>

<p>GS: We are very disappointed that CBS has taken that approach. You know, for us CBS has taken such a phenomenal lead in HDTV...and we don't think it is wise to blackmail the government. It is a questionable strategy in terms of its effectiveness. It is also of great concern among us who have worked closely with CBS all of these years as a great leader in HDTV. </p>

<p>Of course, we value CBS's contribution on HDTV but we don't believe that one network is going to single-handedly make or break HDTV. The fact that cable and satellite programmers are rushing to HDTV, and with the success of pre-recorded formats (namely the DVD) indicates that consumers are rushing to the highest quality video formats. If someone boycotts HDTV in this country they do so at their own peril. </p>

<p>DC: Thank you Gary.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June  9, 2005  9:16 PM</b>
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
			<?=getComments(65)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 65)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_gary_shapiro_ceo_or_cea.php" type="text/javascript" charset="utf-8"></script>
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