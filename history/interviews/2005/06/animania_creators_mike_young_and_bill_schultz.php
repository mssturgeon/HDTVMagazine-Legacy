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
		AND e.entry_id = 101";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 101 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 101 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 101";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/animania_creators_mike_young_and_bill_schultz.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 101";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download ANIMANIA Creators Mike Young and Bill Schultz" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="ANIMANIA Creators Mike Young and Bill Schultz" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="ANIMANIA Creators Mike Young and Bill Schultz" />
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
	<title>HDTV Magazine - ANIMANIA Creators Mike Young and Bill Schultz</title>
	<meta name="keywords" content="hdtv magazine, bill schultz, high def, high definition, mike young, hdtv, high, animation, bill, def, going, magazine, young, schultz, mike, people, new, definition, animania, get, lot, screen, better, our, time" />
	<meta name="description" content="People at Mike Young Productions, (MYP) say it’s hard to tell who is having the most fun: the studio’s young fans numbering in the millions around the world or the studio’s &quot;allegedly&quot; grown-up creators and producers which now number over..." />
	<meta name="title" content="ANIMANIA Creators Mike Young and Bill Schultz" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="ANIMANIA Creators Mike Young and Bill Schultz" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/animania_creators_mike_young_and_bill_schultz.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="People at Mike Young Productions, (MYP) say it’s hard to tell who is having the most fun: the studio’s young fans numbering in the millions around the world or the studio’s &quot;allegedly&quot; grown-up creators and producers which now number over..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=101', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/animania_creators_mike_young_and_bill_schultz.php">ANIMANIA Creators Mike Young and Bill Schultz</a></td>
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
				<p><em>People at Mike Young Productions, (MYP) say it’s hard to tell who is having the most fun: the studio’s  young fans numbering in the millions around the world or the studio’s "allegedly" grown-up creators and producers which now number over 75. New technology now makes them "forever" Young Productions</p>

<p>The talented pros who rev up this super-creative animation company admit with no head-hanging that they share many qualities with their young viewers, not the least being, "Unbridled energy, intelligence, a keen eye for original material and a quirky sense of humor." And like the kids, college students and adults who love their shows, everyone at the studio has a passion for all things animated, whether it’s the traditional 2D or the cutting-edge, 3D, CGI animation. In recent times they have entered into HDTV and we asked them why? What is the future for their business and how does HDTV fit into that?</em></p>

<p>This interview was prompted by a press release on a new animated series ( Pet Alien) that Mike Young had sold to the HDTV VOOM channel, ANIMANIA HD. We talked on Tuesday, the same day the Cablevision board met in New York, so no one was sure of the future for ANIMANIA HD. </p>

<p>Two were on the phone, Mike Young and William (Bill) Schultz. I was not sure about how this interview would go. Here were two seasoned veterans in animation at the top of their game but not early dashing pioneers of HDTV. How would these people see it and would it carry as much enthusiasm for them as we found in the early entrants.  <br />
And now the interview...</p>

<p><strong>HDTV Magazine: Since it is our chief focus here let me ask you about your involvement in high-definition. What was your entry into high-definition, and why?</strong></p>

<p>Bill Schultz: There are a lot of different ways to produce animation. The issue of resolution is always something you are discussing. With that in mind and in convergence of where the broadcast industry was going it became a natural conclusion. One of my early touches with HDTV was when we were producing some shows for Cartoon Network.  </p>

<p>We were trying to figure out what would be a good storage medium. Historically animation had been shot on 35 mm negative film. The storage method was that you put your work print and negatives in a vault and you would then have a pristine way of going back to the original. In television the post production has gone to electronics. You no longer have what would be considered a finished film master. So, we started running test...how can we archive digi-beta? Then we took it back to film. We sent the digital files to a place in Canada and ran a piece of negative right off of the digi-beta. At that time it was for Turner and they are very advanced in this regard. We started talking then about producing in HDTV. We realized it was not feasible because of the additional processing time to record all of the extra information. </p>

<p>At the same time everyone was very clear that if we did have an HD master we would have a great storage medium that was better than recording the film. So fast forward to a few years ago where we are producing Pet Alien in CGI (computer generated graphics). We do a lot of CGI stuff. We came across the folks at VOOM, most particularly Animania, They were looking for animation programming in high definition.  We ended up doing a couple of high-def master and we sold to Animania Pet Alien as our first new production. To date we have not yet produce fully in true high def in terms of rendering it all out. </p>

<p>We are more market driven than anything else. We have a couple of projects now, one is in production and will be rendered out in high-definition.  And a few more projects...still with VOOM,,,about producing in true High-Def.</p>

<p>One thing we are doing that I think is quite unique in the business is that we are producing all of our shows in both 4:3 and 16:9 aspect ratios. Although the resolution is not always high def the format is. Since I now have a high-def set at home I am acuity aware as I sat and watched American Idol in high-def I appreciated not having everyone "squoushed." </p>

<p>Mike Young: You may tell by my accent that I am British. The European countries are a lot smaller and they switch over to things,  like cell phones, very quickly. It seems that you cannot even buy a 4:3 set in Europe today, all having gone now to 16:9. Yet, many of the networks here, especially the kids shows, still run their programs in 4:3. All of the European networks want everything in 16:9 and high definition. We are really trying now to switch everything to that. </p>

<p>We are doing a new show called Choose Your Own Adventure. It is based on the books that are vastly popular where kids get to a certain page of the book and they choose if they go up to the Himalayas or to the dangerous cave, or down into the jungle. They turn to the appropriate page once they have made the choice. We do it with animation. Interactive DVD is almost the wrong word to use, but on the DVD it allows the kid to make that choice. There are 15 story options, 12 different endings with some of them ending in death and destruction and others look back to the beginning of the show again. All of that is going to now be in high-def. <br />
 </p>

<p><strong>HDTV Magazine: It sounds as if you are on the runway of the video game business.</strong></p>

<p>Bill and Mike: Oh no! That is one of the issues in marketing this is to differentiate what they in video games have over what we have. This is a story. We could produce 18 or 19 minutes of animation for a 22 minute story in high-definition. It is one of our challenges to explain the difference between a video game and this story telling.</p>

<p><strong>HDTV Magazine: Do you find there is an added cost still to doing programs in high-definition? </strong></p>

<p>Mike Young: Right now it is at break even for us. Thank goodness for Animania.</p>

<p>Bill: The license fees that we project from Animania, and hopefully there will be additional buyers and Animania will stay as a buyer, but there is about 5 to 10% of the budget cost attributable to HD in terms of the additional rendering that has to be done.</p>

<p>I think there is a wonderful advantage in our creating a real library going forward that for the next decade or so is going to hang in there while all of our other libraries are going to be fairly obsolete. </p>

<p><strong>HDTV Magazine: What is the life of animation products?</strong></p>

<p>Mike Young: Well, the film ones will translate wonderfully to high-def. They will get a new lease on life. But I am afraid that all of the NTSC 425 line libraries are going to have a mighty struggle. </p>

<p>Bill Schultz: It is going to be driven by the consumer. All of in the industry who are focused on it we have to always remember that it is all driven by the consumer. If it wasn't for pornography the VHS format would never have taken off. I am sort of on both sides of this in terms of the industry, but then I am also one who likes to sit at home and watch a nice picture. As long as we find compelling entertainment opportunities with this new format...</p>

<p>Mike Young: And we saw a small screen offered to us for a trade show exhibition that was just $850. </p>

<p>Bill Schultz: I think there is still a lot of confusion. There is high-def ready; there is the TIVO and DVR technologies--are they HD or not? Just going to 16:9 is a better format which is an obvious thing that people will quickly understand. Although, I can sit ten minutes trying to explain to my friends while switching back and forth between a 4:3 standard feed and a true HD feed of a football game and they don't quite get what I am talking about. </p>

<p><strong>HDTV Magazine: Yes, there is a big concern of those who are long time advocates that while the early adopters and their followers spend a lot of time becoming educated on the subject there are a lot of people who just don't put that much stock in television in the first place, nor do they care. </strong></p>

<p>At this juncture we broke cadence on the interview and talked about VOOM, it's likelihood of surviving, and then talked about our respective views on the consumer, of which we are all a part. Perception is reality and so each perception of those in a leading position is important to hear and understand. </p>

<p>Bill Schultz: I count myself as an overage Joe consumer. I just bought my first HDTV set a few  months ago. I had to replace a big screen TV in my family room and thought I had just as well go ahead with the HDTV. A week after getting I realized I didn't have any HDTV programming. So I went and got that from Adelphia, but their HD package is still pretty meager. I don't know if I want to sit in my living room and watch three hours of beautiful photography of Europe on HD, a lot of that kind of program being out there. But like I say, I do like watching the 16:9 format. When you go back to standard def from HDTV you certainly notice the difference.</p>

<p><strong>HDTV Magazine: The questions is: is it just noticing the difference or is it a new experience? </strong></p>

<p>Bill Schultz: With what we do... I don't particularly feel that traditional animation is an HD experience that is worth anything .Quite honestly, seeing all of the imperfections in a higher resolution picture lets you know that most of the animation is not deserving of it. </p>

<p><strong>HDTV Magazine: So it is a negative in that sense?</strong></p>

<p>Bill Schultz: Yes it is. I think in 3D it is much more interesting. There you are capable of delivering higher resolution that has more information and that is appealing. The other part of it is that the HDTV people have to also deliver better sound and make the entire experience upgraded. That should not be forgotten.</p>

<p><strong>HDTV Magazine: While I cannot say there is an overwhelming number but there are people who are seriously discussing a wider screen 4 million pixel system. In regards to 3D I did see one system in Las Vegas for 3D HDTV. There were two rear projection engines in one box with each running polarized images to the same screen, one vertical, and one horizontal. With polarized glasses the 3D was stunning. I don't know how you get people to wear those glasses in their homes.</strong></p>

<p>Bill Schultz: I tend more of the Joe consumer type. People replace their TV sets slowly. HD us better than traditional as cassette was better than 8 track. Is HD better than standard def the way CDs are better than audio cassettes. </p>

<p><strong>HDTV Magazine: I think you can make a good case for it. A lot of it is in the viewing environment you select. The whole system was designed to be viewed at 3 X picture height. That gives you a 30 degree field of view, which excites the peripheral part of your vision, thus adding the sense or reality. People were wired up in tests to make these determinations and it was determined that the 30 degree was the key thing and they backed into a technical choice to support that. If you set it up right it is a great experience. I thought for some time that we would not get more than 40% of the public interested in it, but that in time the manufacturers would find it in their best interest to only make them and so conclude the revolution in that way. As far as those driving and pulling it 40% may be a generous figure.  For me its a wonderful experience and spent 20 years in supporting it.</strong></p>

<p>Bill Schultz: To us it is another sale. I think it is interesting on a technical level. I would personally like to watch all of my shows in high-definition. I hope it gets more traction. I hope VOOM is able to come up with something.</p>

<p>Unfortunately, it is not compelling enough just to offer HDTV to set up a whole new satellite service like VOOM. Ultimately all the others are going to come up with the same thing. For VOOM to be successful, and HDTV to get the kind of penetration we saw with DVDs or CDs they will have to give the consumer something that they can't get otherwise. Right now I don't know what that is.</p>

<p><strong>HDTV Magazine: I don't either but we have often talked in theory about productions that fit HDTV. If you have ever seen an awards show or some other large venue program done on HDTV you start to get the idea of how this differentiates the services. The sense of being part of an audience in the auditorium, being part of the stadium audience at a sporting event--that is really compelling. Again, this takes a large screen for the small screen doesn't do it. With the small screen you tend to not achieve the 3 X picture height and the vastness you might feel is lost. </strong></p>

<p>Let me shift gears here, where is animation going from here?</p>

<p>Bill Schultz: In animation the big thing is 3D CGI. That is compelling. When you see high-def CGIs there is a noticeable difference. The fans of animation want a higher standard. So, for us we are appropriately apportioning resources for production and development in that regard. It is not a C change for us. We are going to continue to focus upon stories, characters, art, and design. As broadcasters split out high-definition rights vs. standard def rights we are going to try to make the most out of both markets. </p>

<p><strong>HDTV Magazine: Thank you Mike and Bill.</strong><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 20, 2005 11:30 AM</b>
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
			<?=getComments(101)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 101)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/animania_creators_mike_young_and_bill_schultz.php" type="text/javascript" charset="utf-8"></script>
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