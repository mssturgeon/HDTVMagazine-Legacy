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
		AND e.entry_id = 80";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 80 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 80 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 80";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_1998.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 80";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download An Interview with Dale E. Cripps, HDTV Pioneer - 1998" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="An Interview with Dale E. Cripps, HDTV Pioneer - 1998" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="An Interview with Dale E. Cripps, HDTV Pioneer - 1998" />
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
	<title>HDTV Magazine - An Interview with Dale E. Cripps, HDTV Pioneer - 1998</title>
	<meta name="keywords" content="goal hdtv, hdtv magazine, signal providers, original goal, hdtv every, hdtv, new, vsb, standard, goal, say, think, industry, cofdm, receivers, could, television, been, spectrum, most, things, fcc, interview, every, may" />
	<meta name="description" content="Dale Cripps:
The original goal of HDTV was to create a worldwide electronic production standard competitive to 35mm film. The goal soon became one to create a new experience for the home--the next generation of television. Test in Japan determined that a 30 degree field of view and the 5.1 audio system made for a dramatically new experience. To do that visually, and without artifacts, you would need about 2 million pixels in a wide shape (later became 16:9).

In the beginning HD was not recordable. Neither were there displays for it. Few had hopes of ever transmitting it. Through to 1984 it was driven fundamentally by this desire to have one electronic production standard that would downconvert with equal ease to the existing transmission standards. Finally NHK developed a HD satellite broadcast system in 1985, intended to be used for the launch of HDTV satellite services in Japan. That transmission system, they once thought, would be sought after around the world and their own beginnings in Japan would mean a lower cost of introduction elsewhere. Japan would dominate the information age with the ultimate information appliance.
" />
	<meta name="title" content="An Interview with Dale E. Cripps, HDTV Pioneer - 1998" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="An Interview with Dale E. Cripps, HDTV Pioneer - 1998" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_1998.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Dale Cripps:
The original goal of HDTV was to create a worldwide electronic production standard competitive to 35mm film. The goal soon became one to create a new experience for the home--the next generation of television. Test in Japan determined that a 30 degree field of view and the 5.1 audio system made for a dramatically new experience. To do that visually, and without artifacts, you would need about 2 million pixels in a wide shape (later became 16:9).

In the beginning HD was not recordable. Neither were there displays for it. Few had hopes of ever transmitting it. Through to 1984 it was driven fundamentally by this desire to have one electronic production standard that would downconvert with equal ease to the existing transmission standards. Finally NHK developed a HD satellite broadcast system in 1985, intended to be used for the launch of HDTV satellite services in Japan. That transmission system, they once thought, would be sought after around the world and their own beginnings in Japan would mean a lower cost of introduction elsewhere. Japan would dominate the information age with the ultimate information appliance.
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=80', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_1998.php">An Interview with Dale E. Cripps, HDTV Pioneer - 1998</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 15, 2005</b>
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
				<p><em>This interview was conducted in 1998. It was widely published and is brought to you now in hopes of advancing your insight into the HDTV movement.</em></p>

<p><strong>An Interview with Dale E. Cripps, HDTV Pioneer</strong></p>

<p>Mr.Cripps has been active in the HDTV arena each and every day for the past 17 years. He is one among many directly responsible for HDTV in America, and the world. He is the founder and President of Advanced Television Publishing. We recently sat down with him for this exclusive interview.</p>

<p><br />
<strong>HDTV Magazine: What was the original goal of HDTV?</strong></p>

<p>Dale Cripps: The original goal of HDTV was to create a worldwide electronic production standard competitive to 35mm film. The goal soon became one to create a new experience for the home--the next generation of television. Test in Japan determined that a 30 degree field of view and the 5.1 audio system made for a dramatically new experience. To do that visually, and without artifacts, you would need about 2 million pixels in a wide shape (later became 16:9).</p>

<p>In the beginning HD was not recordable. Neither were there displays for it. Few had hopes of ever transmitting it. Through to 1984 it was driven fundamentally by this desire to have one electronic production standard that would downconvert with equal ease to the existing transmission standards. Finally NHK developed a HD satellite broadcast system in 1985, intended to be used for the launch of HDTV satellite services in Japan. That transmission system, they once thought, would be sought after around the world and their own beginnings in Japan would mean a lower cost of introduction elsewhere. Japan would dominate the information age with the ultimate information appliance.</p>

<p>A reaction to NHK's transmission system set in around mid-1986. A fear that free-over-the-air broadcasting could be overwhelmed by the popularity of a satellite or cable based HDTV service caused the NAB and others to petition the FCC. The NAB said in the petition that free broadcasting--the backbone of democracy--was facing a new threat, and they had to react by becoming technically and spectrum-ready. HDTV was coincidentally a wonderful excuse for asking the FCC to protect broadcast spectrum--freeze it from further allocations to anyone until there was an complete answer as to how much spectrum would be needed for HDTV. To a broadcaster in the late 80s there was nothing more valuable than their spectrum. HDTV fought off the challengers who had at that time had powerfullly risen to request broadcast spectrum. Saving spectrum, of course, was not its original goal. Many sub-plots have been added to the goal-line of HDTV that have nothing to do with the real goals that must in the end lead it.</p>

<p>In fact, the real goal for HDTV has been articulated by only a few to date. Since there is no clear business advantage for broadcasters to pioneer HDTV signals, it has been difficult for anyone of high standing to jump up and shout the good news that HDTV has finally arrived. If you give HDTV the stature of being the next generation of television you would think that everyone associated with television would need to be out their identifying with the new product. But they are not. Indeed, Jay Leno makes jokes about it, if he mentions it at all...and he is the only person being telecast in HDTV every weekday night. </p>

<p>I think the goal of manufacturers is to get HDTV into every part of the world where it will fit in. In my view the best reason to do that is because HDTV raises the standard of living for those who acquire it. I am sure most of the readers of the HDTV Magazine will agree that is its finest pay-off.</p>

<p>We are lucky that we had a pragmatic Trojan Horses--like saving spectrum--to drive the technical side. We are lucky that tax breaks were granted to networks in New York to insure the Big Apple had a hand in rolling out the next generation of TV (rather than New Jersey). We are lucky to have a good economy during the introductory period. All of these things, plus a creative renaissance, tell me we are indeed going to improve the standard of living, and to me that is the real, and too often, understated goal of HDTV."</p>

<p><strong>What will be the outcome of the current COFDM vs. 8-VSB issue?</strong></p>

<p>I wish I could tell you definitively. But I cannot. The outcome is beginning to take shape. Sinclair has courageously focused attention on a real problem. The performance of early 8-VSB receivers did not do justice to the standard. Some say that it is the standard that does not do justice to the needs of modern broadcasters. In comparative tests--some called them demonstrations--the COFDM receivers appeared superior for indoor reception to the 8-VSB receivers. Sinclair has a lot of inner city viewers. They were concerned that any tedious rotating of an outdoor antenna would slow the transition. That could impact their negotiations for carriage on cable systems, among other things. Dynamic multipath--called ghosts in standard TV--is harder for 8-VSB to handle. The decoder can be left not knowing if it is decoding the ghost or the main signal and crashes, leaving no picture. It is also highly directional. Most who subscribe to this publication may have fiddled with their antennas when acquiring their HDTV receivers. Being early adopters and problem solvers you likely overcame most of these annoyances with ease. Indeed, most places work just fine with 8-VSB. It is only that some places where COFDM is receivable, the 8-VSB has trouble, And none of what I am saying is definitive. Many groups are working on new algorithms for new chips and fine tuning with software existing chips to greatly improve this multipath performance. The COFDM may also find use in mobile applications where the 8-VSB simply cannot today. The 8-VSB is said superior over COFDM on reach--how far a usable signals travels. It is also more "robust" in handling impulse noise. Solving either reach or impulse noise might raise the cost of power for a COFDM choice. Of course, there are two sides to all of the claims with each saying their weaknesses are not so weak.</p>

<p>The two systems do have their respective strengths. So it's always a trade off. The unknowns are those things still left to be invented, but could one day they will be invented to solve all shortcomings and even extend the virtues they have. When I first began researching HDTV in 1984 Larry Thorpe from Sony carefully explained to me that while there were cameras, and there were monitors, there simply was no way to transmit HDTV. That has certainly changed, and to think that the multipath problem 8-VSB suffers with cannot be overcome with increased technical knowledge is faithless non-sense. The question is: how long will that take? That is what Sinclair and others are asking. So far the answers have not been so clear and those things put out as answers, disappointing.</p>

<p>To get more answers the FCC has decided to oversee independent side-by-side tests using the latest 8-VSB and COFDM receivers. The reports on that will likely set the stage for a decision that will settle the industry down. Those of you who have purchased an 8-VSB receiver will not lose your investment. It has already been discussed that should a new mandate be made which in any way obsoletes your receivers, the manufacturers will trade them in for whatever new is selected. This is not the best of situations, but the public relations resulting from not doing that is too horrible to contemplate.</p>

<p>Dr. Joe Flaherty--the god-father of HDTV from CBS, said in a recent interview with me that, unlike the 50 year reign of analog, this round of digital devices and solutions may not last much more than ten or fifteen years. New things will be introduced, and their proponents will bang at the door of signal providers and the FCC as well as exciting the public about their new gadgets. The new technology will leap frog what is adopted now, using for their introduction the current analog channels being returned to the FCC by broadcasters. These always-newer upgrades--mini-revolutions--will dazzle us in an era of global interconnected commerce and electro-social articulation in such ways that are hard to forecast today. Science is not sitting still, however, and applications of new developments are not slowing. Some will be attractive enough to move us to discard the old decoder box of today into the kid's room, the spare bedroom, the kitchen, and leave room for the next latest and greatest in our media rooms. The display will likely be the most stable of the components we will be acquiring over the next ten years. But efficiency in how that picture is created and distributed will certainly continue moving forward.</p>

<p>I do wish I could answer your question for the readers. I do hope we end this controversy soon over the transmission standard. This limbo period of doubt is no good. Brazil just issued their report on the modulations scheme--again a negative one for 8-VSB. They said they would go with COFDM. That will likely topple other nations on the fence like dominos. So, as I talk to you right now, I would have to say that 8-VSB is on the ropes in round 5. Maybe that is what it takes to pull all of the industry together and fix the standard to the extent that it at least meets the competition in multipath handling. Only when all doubts are removed throughout the industry will there be enough strength in the HDTV movement to overcome the gravity of NTSC. Right now HDTV is a little wart on a very big and powerful industry. That wart can be pealed off by most-anyone. A recent article in Forbes has already declared the DTV launch a failure. Forbes is a little premature, though they are not the first to say that our just-born baby should already be competing in the main Olympics. I would think that diapers should at least be removed before we start judging the career of this gifted prodigy.</p>

<p><br />
<strong>Do I think HDTV will be the end-game of this digital transition? </strong></p>

<p>In the long run, yes. I am far less certain in the interim. If we suddenly did away with all NTSC manufacturing, and only offered HDTV, then we would be in a hard transition. But we may not be making a transition at all. That could be the biggest myth we have circulating in the industry today. We could be making just a new business--one that is at the top of a pyramid and does not require the other to go away. I think we would profit by a debate as to whether we are transitioning by way of a transformation from NTSC to DTV, or whether we are giving birth to a new and independent business attracting its own distinct audience somewhere at the top?"</p>

<p><strong>What is your best advice to the current owners of HDTV?</strong></p>

<p>The best advice I can give them is to stay tuned to The HDTV Magazine, to enjoy HDTV with every opportunity they can, and to share their discovery with others.</p>

<p>In effect every early adopter is a salesperson for HDTV. The only way programming will increase is to have more receivers in use. Signal providers will not serve a dead-end street." </p>

<p><strong>What would be your advice to the industry?</strong><br />
To the professionals I say, "Get courageous. Be ready for the unorthodox since no old rules are reliable." I would also urge the end of any bickering that flared up between competing factions of manufacturers and broadcasters. If we are going to change modulation schemes, something I worry over because of the time element and the cost of money and reputation to those who have invested in it, I would urge that it be done as fast as is possible with an absolute minimum of congestion at the FCC. If we are to stay the course with 8-VSB, make that utterly definitive and collaborate to find new answers for better performance and set industry standards for receiver performance.</p>

<p>I also urge a search for a new rallying point that will give everyone a reason to act upon their own HDTV initiatives with renewed confidence adn vigor. Alan Greenspan said today that it is stability that engenders growth. I think that stability and vision are essential for HDTV to move forward. Both have been grievously missing for years. We have to have stability first in the standards. We can't start and stop, and start again, and expect the public, much less the retailers, to have any confidence in this product.</p>

<p>Then we need to revisit why we should do it in the first place. It is not enough to just say, "Oh, the people will love it when and if they ever see it,...and all the stations will want to deliver wonderful HDTV programs." Most of them upconvert from standard television and have little plans for more until there is a larger installed base of HDTV receivers. That may take ten years to develop. Far too much wishful thinking has prevailed for this large of a gamble. There are billions upon billions on the table!</p>

<p>Even though there are 119 stations on the air (pumping out upconverted NTSC and a little HD), I still urge the formation of a collective enterprise made up from the whole industry, hardware, software, signal providers, retailers--a for-profit entity owned by all these stakeholders in HDTV. This is a company that has the unremitting mission to pioneer to profitably the HD services to the early markets. I will talk more on this at a later date.</p>

<p>I know many think that to even discuss HDTV in this more enlightened era of digital connectivity is somehow missing the point. They say that digital is any and everything--resolution independent--the convergence of all things. They say to even use the term HDTV means you are a throwback to earlier times which are gone forever.</p>

<p>I have no doubt about convergence--the flexible functions in the digital age are clearly approaching. What I have doubt about is whether that fact negates the act of enjoying a television program--a movie, a special, a sporting event? Am I missing something by believing that people will for generations to come sit in front of their large screen television appliances watching beautiful pictures, hearing glorious sound, and absorbing a moving story? Will instead everyone be clicking on Yahoo, AOL, or eBay? Will the HDTV experience disappear? Let me be one to say absolutely not. It is here to stay.</p>

<p><strong> Thank you Mr. Cripps.</strong><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 15, 2005 11:59 PM</b>
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
			<?=getComments(80)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 80)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_1998.php" type="text/javascript" charset="utf-8"></script>
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