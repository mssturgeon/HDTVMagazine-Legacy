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
		AND e.entry_id = 102";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 102 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 102 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 102";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_1998.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 102";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The Legendary Sam Runco - 1998" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The Legendary Sam Runco - 1998" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The Legendary Sam Runco - 1998" />
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
	<title>HDTV Magazine - The Legendary Sam Runco - 1998</title>
	<meta name="keywords" content="sam runco, line doubler, home theater, long time, could get, hdtv, going, runco, get, people, hdtvmagazine, because, good, been, could, time, years, better, line, sam, industry, home, new, things, right" />
	<meta name="description" content="Sam Runco President, Runco International With Dale E. Cripps HDTV Magazine 1998 &quot;The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here.&quot; -Sam Runco &quot;If we could just get the..." />
	<meta name="title" content="The Legendary Sam Runco - 1998" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The Legendary Sam Runco - 1998" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_1998.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sam Runco President, Runco International With Dale E. Cripps HDTV Magazine 1998 &quot;The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here.&quot; -Sam Runco &quot;If we could just get the..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=102', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_1998.php">The Legendary Sam Runco - 1998</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 20, 2005</b>
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
				<p>Sam Runco<br />
President, Runco International</p>

<p>With Dale E. Cripps</p>

<p>HDTV Magazine<br />
1998 </p>

<p>"The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here." -Sam Runco</p>

<p>"If we could just get the world to realize that television did not go up in price." -Sam Runco</p>

<p><br />
Runco International was founded by Sam and Lori Runco in 1987. Sam Runco has been an innovator in the video projection business since the early 1970's, when his projectors first appeared with the Runco name. The new company was the first to coin the term "home theater," and promptly received a trademark for it from the state of California.  </p>

<p>In 1989, Runco introduced the CinemaPro 600 video projector, replacing the CinemaBeam product line that launched the company.  </p>

<p>The CinemaPro 600 was a major success for Runco and found its way into homes, nightclubs and bars throughout America. This product helped establish Runco as a force in the video projection marketplace.</p>

<p>Early in 1991, Runco expanded its product line with the introduction of the original Super IDTV (Improved Definition Television) system. This consisted of the IDP -800 projector mated to the SC-1050 line doubler. This was a revolutionary new product for the industry.  </p>

<p>This combination also marked Runco's entrance into the high-end home theater video market, a new market segment that Runco created with the Super IDTV, a market Runco has lead. For the first time, consumers could obtain a home video projection system capable of reproducing images with film like quality. </p>

<p>In 1992, Runco pioneered yet another industry first, the ARC IV Aspect Ratio Controller, the first of its kind for use with the Super IDTV system. </p>

<p>This and other technological innovations since secured Runco's position as industry leader in state-of-the-art video reproduction for the home.  </p>

<p> <br />
 In 1997 Runco improved his latest IDP-850 by designing and building an internal line doubler card. Dubbed the DTV-852 for its digital and HDTV capabilities, this projector was the first CRT projector to reach the market with a built-in line doubler. </p>

<p><br />
Sam Runco: My position is from experience. The most I can do is hope to drive someone who is reading this into a store-to have the experience. What will they get when they go into the store? That's up to the sales people. One of the things I'm working on is trying to find sales people to train. Show them HDTV, but also show them what they're going to be watching for the next few years. Spend your time on widescreen DVD. Sit back and enjoy the 5.1 Dolby digital audio. - Sam Runco</p>

<p><strong>HDTVMagazine: If you were king engineering and controling the roll-out of HDTV, would it be as it is with broadcasters leading the way? Or, would you do something else?</strong></p>

<p>Sam Runco: Do you mind if I back into that question? I'll tell you why, Dale. I'm usually pretty impromptu when I deal with things from my experience rather than from selected events. If we talked a little bit about my experience we could decide what I would do if I were king.</p>

<p>First, I've been watching the approach of HDTV for a long time. I won't go into what is right or wrong about the system-the 18 formats, the FCC, the grand alliance, or CEMA.</p>

<p>The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here. Whether it's 1080i, or 720p, or even a couple of added new ones in the next few years. Without a doubt  this is an evolutionary process. While many people will deny that. both the computer and the motion picture companies are strong and will flex their muscle over the next year or two. There will be some added formats. </p>

<p>Will they make the picture better? That's questionable. HDTV is such a jump over NTSC that when someone argues over 720p and 1080i it's a moot point. They're both so damn good in reference to the NTSC system that it just doesn't matter. </p>

<p>It may matter, however, after we get used to watching it for a year or two. Then we can get a little tweaky on it, adjusting it a little bit here and there. Some people who pick on it now-both the 1080i and the 720p. There are people who claim you're not going to be able to get good sports because of the motion, and so on. Again, I am just happy to be here-happy that it's around.  </p>

<p>The presentations that have been done so (for HDTV) get nothing but negative press. A couple of things are bringing that about. </p>

<p>First, the experience of HDTV cannot be experienced from a written article. It can't come from broadcast news on an NTSC set. All you can do is talk about what it looked like in print (like what we're doing now). You could see HDTV on a news broadcast, but the quality is only as good as is the particular device it's being displayed on.</p>

<p><strong>HDTVMagazine: It's trying to explain sex, right?</strong></p>

<p>Exactly the same thing. The most we can sell through the media that is available to us is an appointment-get the individual into a store and into a position where they can experience HDTV, along with the big sound. (Fortunately for us the 5.1 digital audio is one of those things that's part of the system.) Then they can have that experience. From that point on their level of entertainment, their level of quality is going to drastically change. Once they've done that, they are never going to want to watch things the way they were before.</p>

<p><strong>HDTVMagazine: That's what happened to me! </strong></p>

<p>It is an experience, and like most experiences, it has to be experienced. It can't be synthesized. As an industry we're coming in and trying to drop a bomb on top of them now. The media is reacting to that bomb. Out here in San Francisco there was an article called "Who Cares TV", and "HDTV is Finally Here, and Nobody Gives a Damn." These are the headings for the articles that are coming out in Silicon Valley, of all places! </p>

<p><strong>HDTVMagazine: The Japanese have been asking me from NHK, "What is going on over here? People seem to be reacting negatively."</strong></p>

<p>The whole industry can profit from the experiences of a few companies. One of those companies is Runco. What I'm going to tell you, Dale, is going to sound like a sales pitch  </p>

<p><strong>HDTVMagazine: Sell it, Sam!</strong></p>

<p>Don't take it as that. Let's excerpt the information from the sales pitch, because there's no other way to explain it. </p>

<p>We introduced to the home theater industry in 1989 the line-doubler. Faroudja came out in 1991 with the LD100. He built it for the broadcast industry then found that, "Gee, it's starting to sell into the home theater industry." It was 98% of Faroudja's business last year-these kinds of products. Runco is still the premier company, and that's maybe bragging, but it happens to be so.</p>

<p><strong>HDTVMagazine: Keep bragging!</strong></p>

<p>Because we are, we have a pool of information. The information is that in 1991 we came out with a controller. From that time until now we've been selling widescreen television. We have a lot of widescreens in place. We have been selling the improved definition television to people for a long time-almost a decade. As a matter of fact, without the widescreen, it's long than that.</p>

<p><strong>HDTVMagazine: Could you get any of those customers to go back to whatever they came from?</strong></p>

<p>Those customers are spoiled. In many cases, Dale. In the last 8 or 9 years a Runco customer may have, and in many cases definitely has,  purchased 2, 3, or 4 models of our products. It is sometimes to replace the last one. I find that unbelievable, but they do. </p>

<p>Once they get used to watching a football game using a good, data-grade quality projector-one that can do the proper spot-size and has a processor...and we all know the processors have gotten better and better and better...they don't go back. When Faroudja introduced the LD100, it was the reference standard because of the decoder and the 3:2 pull down feature. Well, in the last year the scalers with 3:2 pull down in component input have made an advancement over that particular piece. In those days we didn't think it could get any better. Now that old one looks like it's broken. It's amazing.</p>

<p>You look at a good processed picture-a good picture on an 8 or a 9 inch tube using full-scale tripling with an anamorphic 16 by 9 picture (and assuming that the software is as good as it can be), it's terrific.</p>

<p>We have been creating really good pictures for a long long time. People have been enjoying them. They enjoy them to the point that the don't enjoy themselves when they have to watch something else. </p>

<p>Now comes the HDTV experience. It looks like it's going to be a while before this stuff hits (unless we handle it right). By the way, I have to preface this with this: I'm really a proponent. I'm high on HDTV. . That's important for me to say. Some of my (following) statements are going to sound like I'm not. They're (made) only because I'm dealing with what I believe is the reality of the moment, which is pushing me in the direction I'm going. </p>

<p>I think in order to sell HDTV-to get it accepted-the first thing is to get high-definition devices into the home. What I'm noticing is a reluctance on the buyer's part to purchase the set because he's afraid it's going to be obsolete by the time the signals hit it. That is because the industry really can't decide what the hell it wants.</p>

<p><strong>HDTVMagazine: It's a big problem, Sam.</strong></p>

<p>It's a monster problem. The companies that may look like they're progressive-the companies building the receivers-decoders into the sets-are the ones that are going to come to the biggest harm. Why?  They are not going to be upgradable or expandable. There are companies that are using component, or RGB inputs that have capabilities of a minimum of 1080i. They stand to be in good shape, assuming that they can deliver good process video right now. That will cause someone to purchase the set based on the quality of what that picture is right NOW. "Oh, by the way," you say to them, "when HDTV finally broadcast en masse, this thing is going to be able to do it. Don't worry about the thing being out-dated." </p>

<p>The way of getting people to move off the dime is to have them realize that the thing is worth the $5 or $10 thousand NOW.  HDTV is free later. That is a better approach than trying to sell them HDTV (now). </p>

<p><strong>HDTVMagazine: In other words, the improvements in an HDTV monitor over existing sets for existing signals is worth it alone. </strong></p>

<p>It is worth it alone. </p>

<p><strong>HDTVMagazine: And you say that with ample evidence.</strong></p>

<p>I stand here with all the evidence in the world that it is true! The argument that could come from outside Runco might be, "Yeah, well all your buyers are people that have so much money they don't really care." That's not the way it works. </p>

<p>When I started selling expensive equipment -I can't call it high-end because I never believed I was in the high-end business. I believed that the stuff I was selling just cost a lot of money. In other words, I never paired a Runco projector, for instance, with Wilson speakers. I always felt that Wilson can do what they're doing for a long, long time. Krell can do what they're doing for a long, long time. But the day that someone comes up with better picture than mine for $5,000, I'm in trouble. There's no fancy box or container-no slick gizmo that I could produce to allow me to sell mine for $50, 000 if the picture is better (from someone else) for $5,000. </p>

<p>Visuals are different than audios. There is a lot of subjectivity in audio. You can install a brand new surround-sound system into a home. The center channel could have two mid-ranges-one of them could be out. One of the two sub-woofers could be out. The tweeter in the surround could be out. No one would give a damn, or able to tell. Yet, if you see a phosphor burn on the TV in the right hand corner, I get a call the next day. You don't have to be a genius. The salesman cannot convince you that the spot is not there! But I have seen that happen in audio. </p>

<p><strong>HDTVMagazine: Some of the early pay-per-view were in letter-box which resulted in a lot of complaints.</strong></p>

<p>Oh, boy, I'll bet. "Where's the rest of my picture!"</p>

<p>My position is this: "Hey, this stuff I'm selling is really high technology. In order to get these kind of pictures right now, they cost a lot of money."</p>

<p>When I first started I put a line doubler along with a projector for the home theater industry. My reason for doing that, Dale, was because I thought I was going to sell a lot of $5,000 projectors by having a flag-ship line. What happened? I ended up selling more $15,000 projectors than $5,000 projectors.</p>

<p><strong>HDTVMagazine: How did that happen?</strong></p>

<p>Whatever it was, I'm glad it happened! And, it keeps on going up. When Faroudja came out with his processor I had a couple of competitors. Up until that time, I didn't. For two years it was a free market for me. Then the competitors came out. What happened? Because the Faroudja processor was so expensive nobody worried about paying $20, $25, or even $30,000 for a projector. The bar raised again. </p>

<p>I found myself dealing with very, very wealthy people. The misnomer is that rich people have so much money they don't care what they do with it. They throw it around. "They're stupid." Someone who says that doesn't realize that the words "rich" and "stupid" just don't go together. </p>

<p>For years I've heard people say, "This guy is such a good customer. He just signs a blank check." Well, I have never seen anybody do that. It just doesn't happen. What I've observed, and it certainly is the rule rather than the exception, is that when a person is wealthy they'll pay more. Why? Because they know they're buying your soul. The type of people buying Runco projectors are buying cutting-edge technology. They are also buying the dealer. The dealer may think, "Wow, am I going to make a fortune from this guy." But they find out that, "Wow, this guy owns me." The people who have been successful in the business are the ones who realize, "Yes, I'm for sale". </p>

<p>My position as a manufacturer has always been-my soul belongs to the dealers-the dealers' souls belong to the end users. Our job is  service. We're absolute service. But while we were dealing with them we learned they pay for technology and enjoy it. Their feedback was the most objective that you could get. Now, that's an arguable statement, because someone would say, "Just because a guy is richer doesn't mean that he's smarter. It may not mean that he's smarter, but he's probably more objective, because to get where he is...</p>

<p><strong>HDTVMagazine: ...you don't make uneducated and emotional decisions.</strong></p>

<p>Yes. He had all the pieces to his puzzle together. </p>

<p>The feedback we got  was very, very good. Now these guys (our customers) are elated, because more than anything nobody likes to get screwed. The fact that they bought something in 1991 that they now can plug an HDTV receiver/decoder into (even if they don't particularly like the projector since there are better ones), makes them feel really good. But the big thing is that these people have been watching good pictures for a lot of years, and now HDTV comes along as a bonus. </p>

<p>If we could just get the world to realize that television did not go up in price. What happened is that improved definition television came down in price. It finally came down to meet the needs of the upper end of the main-stream buyer.  </p>

<p>When I'm saying television, I'm referring to the Mitsubishis, Toshibas, Sharps, Panasonic, Sonys-the companies that are out there who have real HDTV. The sets are worth a lot more money than the price tag just for the improved definition (they deliver). I can now watch a football game, and it really looks. It feels like I'm on the field. Better yet, I can put a movie on and watch it in widescreen and have my whole family enjoy it. I am finally able to afford something that, up-until-now only Bill Gates and Paul Allens could afford.</p>

<p>I don't think we're getting that message across. What we're getting across, is: "Here's HDTV. Oh, by-the-way, there is anything on (in HDTV formats). Oh, by-the-way, there are fights over standards. This may end-up being nothing. If you dish out $6,000 or $8,000, this thing is going to become obsolete.: </p>

<p>That is the message we are (the industry and press) sending out today. The customer says, "I'm screwed." </p>

<p><strong>HDTVMagazine: The low-cost way for entering into what has heretofore been a very expensive market is with today's HDTV?. </strong></p>

<p>Yes. I wanted to let you know my experience so that you could pull from that. You're the writer. What I feel that I've given you is some strong, hard experiential information</p>

<p><strong>HDTVMagazine: Now another big question: HDTV was designed when realizable improvements to NTSC were modest. What everyone (in the 70s and 80s) said we needed to succeed in a new product category (HDTV) was a 10 JND (a scale of Just Noticeable Difference units) improvement. That is what led to the twice vertical, twice horizontal resolution conclusions. Considering we have DvD, satellite and digital cable, the gap perceptual seems to have closed to less than 10 JND. Do we have enough improvements between today's state-of-the-art and the state-of-the-art in HDTV to go forward successfully?</strong></p>

<p>We had the Runco line with 1050 (line doubled) from 1989 to 1991. It wasn't the best line doubler in the world. It was the only line doubler in the world! Faroudja came out in 1991 with the LD100. By comparison it just wowed you. Now let's compare the LD100 to the Snell&Wilcox (pixel) interpolator. All of a sudden there's another jump-wow. Look at the difference in quality.  </p>

<p>There is still more to be squeezed out of the NTSC system from the display devices and processors. We're getting pretty close to perfect. What we're not perfect on doing yet, and this will tally into HDTV too-we're not pulling the full quality out of the film-to-video transfers. There are some good colorists putting out great transfers, but up until now I ask you, what has the main market been for a transfer from film to video? It's been VHS. Why should they go through all the trouble of making sure that they color it correct, and that the focus is correct on each one of those frames when it's going to be performing at 220 lines? Now that the medium's getting higher they're starting to pay attention. The DVD's are getting better, and better.</p>

<p><strong>HDTVMagazine:</strong> Bob Hopkins (Vice-president of Sony High-definition Facility in Hollywood) said that everything that's ending up  up as a DVD from Sony Pictures was first transferred as HDTV. Is that helping?</p>

<p>That's fabulous. Now, in answer to your question: There's nothing that can replace true resolution. We can fabricate. We can manufacture, interpolate, and do all the other things to bring out the picture. We've come to the point where we do pixel-by-pixel interpolation instead of just line by line. This stuff has gotten really sophisticated. But it's a manufactured pixels . It's not a real one. When next to the best IDTV, HDTV is still going to show noticeable improvement. Television has 50 years with the NTSC system. It wasn't designed to do any of the stuff we're doing with it right now. Considering what we've squeezed out of it, and you mentioned Yves-what Yves has done to squeeze out of it is phenomenal! </p>

<p>It's time for a change. I'd probably sit here and say exactly  the same thing about the film industry. A hundred years! How many things in a hundred years? Wouldn't the computer industry love to get just one tenth of that! A hundred years of film... It's time for a change. I think that HDTV is very, very welcome right now. When things start to get broadcast in HDTV-when people begin to see.... </p>

<p>Let's back up a bit. Digital TV. You know, you've heard the statement that "HDTV is digital. But digital is not necessarily HDTV." Digital TV along  with HDTV is going to bring a whole new world from the television side simply because there's no bad signals. Now in some cases there may be NO signals, but in the stream it's going to be perfect. There's going to be no bad ones anymore! That's going to bring a whole new world. Yeah, I think it's necessary, I think it's time for a change.</p>

<p><strong>HDTVMagazine: When I entered this field I read one of these classic overviews of manufacturing. I learned that whenever there is a new product on the horizon that is ready to supplant the position of an old one, the old suddenly goes into a vigorous animation in a vain attempt to look like the new and strive to reach the potential of the new. They always fall short and fail. The new takes off and takes over.</strong></p>

<p>Yes, and that is exactly what is happening. There are a couple of things that are going on. Right now, the broadcasters are doubting the time lines. I sat on a panel the other day in San Antonio. I was with a number of people, including Charles Pantusa (HD Vision, Dallas, Texas), whom you know.</p>

<p><strong>HDTVMagazine: Yes, for years.</strong></p>

<p>We listened to one of the speakers from the local television stations. The next one was from Paragon Cable (the cable network in San Antonio). Then we heard from a representative from Unity Motion. It was a great panel. One thing I got out of it is that they all believe the move to HDTV isn't really going to take off for a lot of years. I don't agree. Why do they think that? They're all looking at it as being systematic, like it's going to take so long to sell so many of these (HDTV receiver) this way, and then we're going to get this into the market at this time, and so on, and so forth. </p>

<p>It doesn't work that way. Supply and demand suggest that if we get the hardware in the homes, and the broadcasters have someone to broadcast to, they will start to broadcast. It's the thing that's going to make them money. It's the chicken and the egg, of course. The whole idea is to make sure that we can get that wheel spinning. If the customers started to demand it, the broadcasters would have this stuff up and running in six months!</p>

<p><strong>HDTVMagazine: This is part of the background of my question: If you were king would you continue urging broadcasters to pioneer the business? Or, is there another way to pioneer the business?  </strong></p>

<p>Of the many things I've been called over the years (and I've been called a few nasty ones), I certainly have been called a pioneer. We at Runco have done the pioneering for this kind of thing (Sam coined the term 'Home Theater'-Ed). I see that the solution is to get the display devices into homes. How do we get them into homes? Well, that's the whole story I just told.</p>

<p><strong>HDTVMagazine: Let me sum it up, Sam. You said, "Hey, you can buy a new breed of television. It's a terrific value because it makes the existing signals look great. AND, you have the bonus that when HDTV signals come, you get that too." That's the right story?</strong><br />
Yes. You don't have to wait for anything.</p>

<p><strong>HDTVMagazine: And so, there's the motive. Everybody's got a signal already. You don't have wait for any signals, it's already better. Did you make that point in the panel?</strong></p>

<p>I made that point. We had about a hundred and twenty five in each session. You can always count on there being a bunch of baby-boomers there. Baby-boomers are a big part of my business. Since I'm one, I can pick on them. The way I approached it was to talk about the baby-boomers after World War II. Our parents were different than we were. For one thing, they were totally unselfish,. All they cared about was us. They worked and lived for us. To do right by them we carried on the tradition by being selfish and taking care of ourselves, just the way they took care of us. We were all liberals in the 60's-hippies, and using all kinds of drugs, and we never thought we'd have two nickels to rub together. All of a sudden we find that we turned 30, 40, and now 50, and we actually have some money. What do you know, we turn Republican. It's a shock. A lot of these people in the audience were shocked because they knew it was true! You know the guys that were yelling and screaming, "Ban the bomb; free sex; blah blah blah..." Well, you're Republicans! What can I tell you, I said, that's the local customer.</p>

<p>What I was leading to is this: If you're waiting for HDTV, you got one reason not to wait. You ain't got much years left. You better buy something now, because you're not going to be able to see soon. I use that approach to lead into the. "Hey guys, this is a faster way. If you can get good pictures now, take a look at what's out there-go see what they're doing", I mean the Mitsubishi processor (in their HDTV) looks real good. The processors that are out there are not perfect-they're not Faroudja quality-but they are on the market already. You can go into one of these stores today and buy one of these things and go home with something that's really nice for a few dollars. I think that's the point that we have to drive home. That's what I was driving home at that conference.</p>

<p>My position is from experience. The most I can do is hope to drive someone who is reading this into a store-to have the experience. What will they get when they go into the store? That's up to the sales people. One of the things I'm working on is trying to find sales people to train. Show them HDTV, but also show them what they're going to be watching for the next few years. Spend your time on widescreen DVD. Sit back and enjoy the 5.1 Dolby digital audio. If you really want to trick them, throw an HDTV picture in the middle of the demo and see if they can pick out which part was in HDTV. In many cases they are not going to do that. </p>

<p>Mitsubishi has come up with the Sencor unit. (the Sencor hard drive system is used for providing demo material where there are no other signals available-ed). The dealers are starting to use these systems, and they're doing HDTV demos. Now that's great, but if they limit their demos to just that bit of HDTV footage the people are going to walk out with the same attitude that we just talked about here. Show them the TV in the way it's supposed to be used, and then cap it with an HDTV demo.</p>

<p><strong>HDTVMagazine: Thank you Sam.</strong></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 20, 2005 12:56 PM</b>
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
			<?=getComments(102)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 102)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_1998.php" type="text/javascript" charset="utf-8"></script>
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