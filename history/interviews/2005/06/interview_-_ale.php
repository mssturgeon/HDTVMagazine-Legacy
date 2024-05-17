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
		AND e.entry_id = 64";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 64 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 64 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 64";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_alex_wallau_president_abc_network.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 64";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Alex Wallau - President ABC Network" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Alex Wallau - President ABC Network" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Alex Wallau - President ABC Network" />
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
	<title>HDTV Magazine - INTERVIEW - Alex Wallau - President ABC Network</title>
	<meta name="keywords" content="great programs, monday night, our affiliates, revenue stream, lot money, abc, our, been, people, great, television, shows, think, hdtv, going, network, news, local, watch, pvrs, thing, world, being, format, programming" />
	<meta name="description" content="So, we know what wireless is. We can play in the wireless world. Wireless is not a threat. We can play in a broadband world. Broadband just needs more distribution. We can play in most of the distribution channels but the potential for disruption of advertising is the single greatest threat we have. 
" />
	<meta name="title" content="INTERVIEW - Alex Wallau - President ABC Network" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Alex Wallau - President ABC Network" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_alex_wallau_president_abc_network.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="So, we know what wireless is. We can play in the wireless world. Wireless is not a threat. We can play in a broadband world. Broadband just needs more distribution. We can play in most of the distribution channels but the potential for disruption of advertising is the single greatest threat we have. 
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=64', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_alex_wallau_president_abc_network.php">INTERVIEW - Alex Wallau - President ABC Network</a></td>
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
				<p><em>This interview was done in the fall of 2004.</em><br />
Interviewer was Dale Cripps</p>

<p><em>As president, ABC Network Operations and Administration, Alex Wallau has direct oversight of ABC News, Network Sales, Affiliate Relations, Broadcast Operations and Engineering, Research, as well as the integration of ABC Sports with the ABC Television Network. He reports directly to Anne Sweeney, co-chairman, Disney Media Networks Unit and president, Disney-ABC Television.</p>

<p>Mr. Wallau began his career with ABC in 1976, when he joined the network's Sports division as head of On-Air Promotion, working with the legendary Roone Arledge, then head of ABC Sports. Mr. Wallau went on to become a two-time Emmy Award-winning producer and director of ABC's sports coverage. In 1986 he moved in front of the cameras as ABC's boxing analyst, and was honored by the Boxing Writers of America as the top television boxing journalist in his first year. </p>

<p>Over the years, as network vice president (1993-96), executive vice president (1996-98), president, Network Operations and Administration (1998-2000) and president, ABC Television Network (2000-2004), Mr. Wallau has witnessed many milestones in ABC's broadcast history. He has contributed to a number of these through his own work, across virtually all divisions of the network, from Primetime Entertainment to News, Sports and Daytime. He has been a strong advocate for ABC's innovative digital ventures - including broadcasting the Primetime schedule in High Definition Television--and for its multiple new on-demand digital platforms. He has also been a leader in ABC's efforts to promote diversity across the entire network</p>

<p>Mr. Wallau serves on the Board of Directors of ESPN and the Advertising Council, and is a member of the Los Angeles Board of Governors of the Museum of Television and Radio.</p>

<p>Born in Fort McPherson, Georgia, Mr. Wallau was raised in the Bronx and Connecticut. He earned a BA degree from Williams College. He and his wife, Martha, live in Los Angeles.</em></p>

<p>I start the interview by asking...</p>

<p><strong>HDTVMagazine: ...what is the most notable changes you have seen in the HDTV movement since we last spoke a year ago?</strong></p>

<p>Wallau: There are two things: There has become more of a sense of inevitability about HDTV as a format. That is true for both consumers and broadcasters. Improvements in the 8-VSB (over-the-air transmission standard) have been a help at the broadcasting level. The amount of programming, including Monday Night Football, has helped the consumer's sense of the inevitable. You can add to this the most important thing--the (new low) price points. Prices moved faster than I expected to a level of acceptability. </p>

<p>I also think Ultra HD is stirring. In the background there is this specter of something else beyond HDTV. That is a challenge from a planning perspective. How much do you spend in your physical plant today (with that looming ahead)? </p>

<p><strong>How real do you think UltraHD is?</strong></p>

<p>It is absolutely going to happen just as HDTV happened. Whenever a satellite provider does a survey and asks people if they want more programs or higher quality they will always opt for more channels.. Having said that, it is apparent that picture quality has become more important than it has ever been in the American viewers' mind.. It is now part of their experience. These 4000 line formats are being talked about in Japan and being tested. I think they will become part of what we have to deal with. The improvement in picture quality is not going to stop with the ATSC standard. The technology will continue to improve. That is a challenge for us in terms of planning. I do not, of course, think that it is just around the corner.</p>

<p><strong>Does that mean you must have an extensibility in your planning?</strong></p>

<p>We can't do that right now because there is no upgradable hardware. Five years ago we made sure that every camera we bought was upgradable to HD, if it wasn't already HD. That situation doesn't exist for Ulta Hi Def. </p>

<p>Ulta Hi Def is not something that we talk about at our planning meeting. It is something we talk about after the meeting. There is brainstorming about it. We presently are planning for the near term for an increase in the amount of local HD programming. The networks are not maxed out either (with HD programming). We are certainly not doing all 21 hours of prime time in HD. We don't do reality (shows); we don't do news magazines in HD. I think that will come. The upside for the HD viewer is going to be in local TV. I hear much more talk from our affiliates about making HD investments. They are feeling the need to make it, especially in the local news area. I never thought I would hear that this early. </p>

<p><strong>Has the introduction of prosumer HD cameras, such as those from Sony and JVC, influenced some of that thinking?</strong></p>

<p>Yes. But it has not been the major driver. Broadcasters still want full broadcast quality capability. They know they can get good pictures from prosumer equipment but that is more for in the field, like in Iraq. Even the remote truck has more in it than a camcorder. There should be a natural competitiveness about it. You may be a manager who doesn't think he can monetize the investment, but if the competitor does it, he feels compelled to do it. </p>

<p><strong>Is there also a competitiveness outside active of broadcasting?</strong></p>

<p>We did it because we thought it was our responsibility as return for the granting of spectrum. We also want to give our viewers the best experience possible. Frankly, we were first in the chicken and egg  equation. We went before there was an installed base to watch it. We spent tens of millions of dollars during that time. But we had been given the spectrum to do it. It was our obligation to do it. We did it. We will continue to do it.</p>

<p><strong>There has been pressure on the Disney Company from Wall Street. Some of that has been directed at ABC. The cost associated with HDTV must have impacted the bottom line. What has Wall Street's attitude been towards ABC for doing HDTV? </strong></p>

<p>It's under the radar screen for Wall Street. First of all we were able to offset some cost with corporate sponsorships. It did not all effect the bottom line negatively. We have a great and helpful partnership with Zenith-LG. We are very grateful to them for their participation. Considering the billions of dollars in costs in broadcasting, the millions of dollars we spent on HD was not so significant. </p>

<p><strong>The last time we spoke we talked about satellite carriage. You said the affiliates were very uncomfortable with it. Does that remain true?</strong></p>

<p>The affiliates are very uncomfortable about us providing our High Def signals to satellite. That still is very true. I am aware of what CBS and NBC have done...and we may end up there. But there is significant resistance at the affiliate level. They see it is a way for people to get ABC Network television shows by some means other than the affiliate in the marketplace. They want to be the provider. There is a white area argument that says if their signals can't be received then the viewer should be able to get it from some other provider. The problem has been that the satellite companies have not held strictly to white areas and so put the onus on the station in the market to verify if the person can receive the signal. That is a costly process. It also is not a good public relations move to go through a procedure which disallows people television. </p>

<p><strong>Murdoch and DirecTV made a rather stunning announcement not long ago saying that they are going to have upwards of 1000 local HD Channels starting with 500 as soon as next year. How does that contrast with the kind of thing you just mentioned?</strong></p>

<p>Local into local is O.K. That retains the advertisers. That is just taking their signal and putting it on the satellite and playing it back into their own market. They have no problem with that. They have a problem with distant signals, such as WNBC in New York being nationally distributed. They get harmed because it is not their commercials being seen. It is not their local programming (being seen). It hurts their own viewer ship. They have an understandable concern.</p>

<p><strong>What is the relationship now with affiliates and cable? Are they finding a home for HD carriage?</strong></p>

<p>It varies. But it is still a battle. Cable and satellite operators clearly see HD as something of value. Whether individual station operators are able to extract value from MSOs is a part of the negotiations under way. Those negotiations are always difficult. </p>

<p><strong>What is the basis of cable's resistance?</strong></p>

<p>Far be it from me to take cable's side, but let me express their viewpoint. They feel that it is a free over-the-air broadcast, whether analog or digital, and so they have a right to take it down and turn it around for their own customers. Retransmission consent means that they don't have this right. The question is; what value do you get for giving retransmission consent? To the cable operator the ability to extract cash runs into an attitude of religious fervor by the MSO, who feels the pain for paying for free over-the-air broadcast. It's the end of the world for them. </p>

<p><strong>What is the future for broadcasting in a world full of PVRs (TIVO-like devices)?</strong></p>

<p>If I had a good answer to that I would be making a lot more money. The answer is that it is an unknown. It is a very threatening technology. Unlike cable channels, we are totally ad supported. PVRs threaten that revenue stream in a significant way. That  technology is a bad thing for us. There are other technologies--interactivity among them--which allow us to potentially grow our revenue stream. With interactivity we can eventually give advertisers more access to customers—better access—better connections. </p>

<p>There is also an opportunity in that PVR owners watch more television. That is a good thing for us. Our shows are still the dominant shows that people want to watch. The good thing (for us) is that they watch more television. The bad thing is that a certain number of them won't watch commercials. </p>

<p>We would be idiots if we didn't recognize the threat that PVRs present to our advertiser revenue base. <br />
 <br />
<strong>Do you know what that percentage is?</strong></p>

<p>Nobody knows. The early adopters are always more technically proficient and more interactive than the later adopters, when PVRs reach a significant installed base level. Any data from people who have them right now is like testing a 14 yr old computer user vs. an adult computer users. It is an entirely different sample of people.  </p>

<p>We would be idiots if we didn’t recognize the threat that PVRs present to our advertiser revenue base. </p>

<p><strong>Perhaps a PVR that carries an ABC label and is given to consumers could be made to not skip commercials. </strong></p>

<p>As I said to you before, Dale, the one thing I am sure about is that the America viewer wants great programs. Great programming requires a lot of money to produce. Up to know advertising dollars and subsidies have annually underwritten tens of billions of dollars of  programming costs. If that money goes away because of PVRs I don't think the American public is going to watch 500 channels of the equivalent of "Garage Band". They want the great actors and actresses and the great scripts--great shows with great story telling--great news gathering and, if they don’t have the advertising dollars to subsidize, its going to be subsidized in another way. That is either going to be from transaction fees or some other form of revenue generation to underwrite the cast of production. I do believe there is a world in which people would choose to watch commercials rather than to pay 99 cents for each show they watch. </p>

<p><strong>You find this evidence on the Internet. Pay sights may be ignored while more distracting advertiser supported sites are used.</strong></p>

<p>I think that may be replicated in the PVR world. You may get to a world where rather than pay out money at the end of the month for shows they have grown use to watching for free they choose advertiser supported insttead.</p>

<p>The one thing I am sure about is that the America viewer wants great programs. </p>

<p><strong>How are the DVD sales of television programs?</strong></p>

<p>A number of serial dramas have done very well. People will sit down for an involving experience with linear story telling that feels like a long mini-series. Those sales have exceeded ten million units and have become a significant source of revenue for those few programs. Some shows equally as popular but not as involving don't do so well. It has not been the majority by any means, but a significant part of the revenue stream.</p>

<p><strong>One thing PVRs don't bring much value to is in live sports. Few like to see the game a day after the event. How is Monday Night Foot ball doing for you?</strong></p>

<p>We are up year-to-year so far. We think the games look terrific in HD. I am proud that ABC is involved in what most believe is the single greatest driver for the adoption of  high-definition. </p>

<p><strong>I talked to Bryan Burns (ESPN) the other day and will publish his interview shortly. They made the announcement for a second ESPN in HDTV. Being a board member of ESPN were you involved with that discussion and decision?</strong></p>

<p>ESPN made the decision. That is not something for which they need our support. We have had a great dialog with ESPN regarding their roll out. Their adoption of 720p, along with Fox’s decision for that format, has been a great move for what we believe is a great High-def format.  </p>

<p><strong>Were you glad to see Fox come in?</strong></p>

<p>Yes, of course, The biggest single benefit for us was when the Department of Defense made the judgment that 720p was the superior format for their purposes For a number of reasons we thought that was the better format. Interlace artifacts in the digital world didn't make sense. When the DoD agreed it was very comforting since we were at that time out on a limb by ourselves. Since then the European Broadcasting Union has decided on 720p/50, Fox has. ESPN has. So, there are a lot of good companies that have made this choice.</p>

<p>We don't care if the manufacturers make 1080i or 720p. What we prefer is they do as did Panasonic with their D5 professional tape format--make it switchable between the two. We want our switchers that way too. We want our cameras that way. That is the way the world is headed. It is great that Fox came in. They are very savvy from a technological standpoint. The decision was very smart.</p>

<p><strong>Do you find additional use for the bandwidth that you don't need for HDTV when using 720p?</strong></p>

<p>Absolutely. That is the reason we chose it. There will be some benefits down the line for our affiliates because they will have more room in their digital spectrum to do other things. One of the funny things you hear in reviews about the PVRs is that people find that the capacity is 13.4 hours (for 1080i) but if you record ABC shows it's 16 hours. They are confused as to why there is a difference. </p>

<p><strong>What are the "other things" that you see being done with that spectrum?</strong></p>

<p>The primary one is that ABC's News Now which we started during the Democratic Convention. That  will go through to November 2nd. It has been a very successful experiment. It answers the question of how to use the digital spectrum in a way that viewers find compelling. It has had terrific reviews. There has not been huge distribution. We are in the middle of some research now. The final presentation of that research will be done tomorrow. But the viewers reaction to the service has been very, very positive. The combination of local and network content and the air of informality about is wholly different than what we have been able to with our traditional services. We look forward with our affiliates to making it permanent.</p>

<p><strong>Is that a 16:9 format or 4:3?</strong></p>

<p>It is a SDTV 4;3 service. It is distributed digitally with most of the news gathering done in the traditional analog format.</p>

<p><strong>What is the health of broadcasting today?</strong></p>

<p>It is a struggle. When you combine us with our station group we are profitable. It is a business that is extremely powerful but there are both challenges and opportunities in the future. If we can meet the challenges and take advantage of the opportunities then broadcasting will be a good business going forward. If we are not smart there is a chance that the challenges will continue to hurt the business. </p>

<p><strong>Which challenge just shakes you to the core?</strong></p>

<p>PVRs. In the mid-term that is the single biggest challenge. Peter Putman did an article the other day describing a wireless PVR, or media receiver. Someone raved about it at the CEDIA show and Peter said, "Well, wireless--that is real broadcasting isn't it?" It's been around for 60 years. So, we know what wireless is. We can play in the wireless world. Wireless is not a threat. We can play in a broadband world. Broadband just needs more distribution. We can play in most of the distribution channels but the potential for disruption of advertising is the single greatest threat we have. </p>

<p><strong>Do you see using the Internet for distribution in ten or more years is as being meaningful thing? </strong></p>

<p>It is but one of the things that makes broadcasting compelling is local content. Viewers want to see their local news shows, especially. So, it would have to be some kind of  broadband distribution that was in conjunction with our affiliate body to make it as strong as it is right now. One of the reasons we have such access to people's homes is because we are in combination with local affiliates and their programming. It would be important to keep that going as long as the affiliate relationship remains sound.</p>

<p><strong>What is the brightest spot providing you the most hope for overcoming all of these struggles?</strong></p>

<p>We are out in the first week of the season with a couple of shows that people have latched on to right away—"Lost" and "Wife Swap", On Monday night we hope Desperate Housewives does very well.  The excitement of what we do is great programs. That is what we are all about. We are only as important in people's lives as the shows that we produce. So, making great content, whether ABC News, ABC Sports or ABC entertainment, is the single most important focus that we have. </p>

<p><strong>Is the skill of making great programs growing? </strong></p>

<p>I think there are more great programs on then ever before. But there has been such a huge explosion in sources of content that it has not necessarily been met by an explosion of talent to meet the appetite. Today you need to be more aware of the people who can create great content then ever, whether it's David E. Kelley, Steven Bochco, or J.J. Abrams on the drama side, or those who create comedies, reality...or the sports franchises—the NBA, the NFL. We have to make sure that people like Peter Jennings, Ted Koppel, Barbara Walters, Dianne Sawyer, Charlie Gibson and the great people from ABC news have the access and means for doing the best reporting on the news stories of the day as they can. We have to have all of the people supporting them in studio and in the field which leads then to great information put out by ABC. It is a big investment, but in the end of the day networks are differentiated only by their content. You have to be smart about how you spend your money. You can't afford to cost cut your way to greatness. You have to invest your way to greatness with great content. </p>

<p><strong>We live increasingly in a global world. Do you find global themes that could be produced with international distribution in mind today?</strong></p>

<p>There always has been a great deal of reality programming coming out of Europe, specifically the UK. Archie Bunker was based on a British show. We are sending ideas their way. "Alias" is a huge hit in Europe. The stars of "Alias" were vacationing in the south of France and greeted in the streets by their show names. We still export a lot more content overseas than is being brought in but I don’t think the globalization of content has happened yet in the television business, but it may.</p>

<p><strong>Any specials coming up that will be ideally suited to HDTV and thus exciting for the public? </strong></p>

<p>Certainly! Monday Night Football, the Academy Awards in February, and the American Music Awards in January. We are trying to do virtually all of our specials in HDTV. We did not do the Emmys in HD this year.</p>

<p><strong>Why was that?</strong> </p>

<p>It fell through the cracks. It is something we should have done. It is produced outside and nobody asked anyone else if it should be in HD. </p>

<p><strong>Do you find that High-def is influencing the way programs are conceived and produced?</strong></p>

<p>I find that people are trying to raise the bar on all of what they are doing and HD is a part of that process. Creators are excited from any increase in quality and excited about HDTV. </p>

<p><strong>Let's talk about the transition itself. The transition seems to be going along pretty well in terms of consumer sales, though I was alarmed by the last CEA report which stated that DTV sales were up only 10% in August over last August.  Did you notice that figure and did it startle you?</strong></p>

<p>I did notice it. I don’t know what to make of it. It may be some aberration. It is counter intuitive. </p>

<p><strong>There is a theory which says following the relatively easy sales to the early adopters and the sphere they influence another circle exists which is insulated from those spheres and is fully satisfied with the TV as they have always had it and not willing to shell out additional money for something they don’t perceive as a product meant for them. What would any consumer softening do to the enthusiasm for continued investment by the broadcasters?</strong></p>

<p>If the format ultimately gets rejected that would reduce our enthusiasm. But that would also call for an action from Congress. The spectrum was given to us for use for digital distribution and HD. It would require a change in Washington if the format was not being adopted and then we would all have to figure out how are we going to use the spectrum if not for HD.</p>

<p> ABC Programming includes the benefactor with Mark Cuban</p>

<p><strong>Isn't the worst possible scenario the one were we get hung up on the fence 50 % analog and 50 % digital?</strong></p>

<p>That won't happen. I just don’t believe you will get to 50/50 where it just holds. </p>

<p><strong>Were you interested in the attempts by Senator John McCain to set the hard date for spectrum return to 2009 with a provision for a billion dollar loan to subsidize decoders for those refusing to buy one or unable to buy one?</strong></p>

<p>I think it will take a lot more money than one billion dollars for a 2009 date certain cut-off. It is absurd to think that Washington is going to turn off television to tens of millions of people. It is political suicide.  </p>

<p><strong>There has been talk about people looking less attractive on HDTV due to imperfections.</strong></p>

<p>It is an issue not just for people but also for our sets. The detail on soap operas where we have to upgrade the set. We didn’t have to upgrade on news because they were already pretty high tech. There is a lot more detail there and if the detail is bad you don't want the people to see it. You have to make things better. </p>

<p><strong>Is the Disney Corporation happy with HDTV—glad you made the move?</strong></p>

<p>Everyone is very pleased with the 720p decision. That was something felt to be very important by the Disney Imagineering people. Progressive scanning was the way to go. That has been a good thing for us with other areas of the company that are producing content.</p>

<p><strong>When you go home what is your own viewing experience? What are your habits like?</strong></p>

<p>I watch more D-VHS than I would bet than anyone in the world. I have D-VHS recorded both in New York and Los Angeles because I don’t get to watch a lot of the shows live. So, I have D-VHS of all of our competition and our own shows. I have four separate JVC 400s--the professional version of the D-VHS player. I also have two HD-PVRs. I have six more PVRs. I watch a lot of television.</p>

<p><strong>And your family…do they feel as enthusiastic about all of this?</strong></p>

<p>Martha and I have been married for 37 years. We don't have children but I can tell you that when mym niece and nephews come to visit they are more impressed with fact that the X Box has a component output that makes for better graphics then they are by seeing some HDTV show. </p>

<p><strong>Did you get a chance to look at our new HD Programming Grid Guide?</strong></p>

<p>I did. I use it. I am a fan.</p>

<p><strong>Thank you Alex.</strong><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June  9, 2005  8:58 PM</b>
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
			<?=getComments(64)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 64)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_alex_wallau_president_abc_network.php" type="text/javascript" charset="utf-8"></script>
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