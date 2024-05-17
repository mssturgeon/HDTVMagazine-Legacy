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
		AND e.entry_id = 63";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 63 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 63 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 63";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_bryan_burns_espn.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 63";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Bryan Burns - ESPN" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Bryan Burns - ESPN" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Bryan Burns - ESPN" />
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
	<title>HDTV Magazine - INTERVIEW - Bryan Burns - ESPN</title>
	<meta name="keywords" content="hdtv magazine, bryan burns, progressive scan, going hdtv, espn espn, espn, hdtv, our, going, time, bryan, year, magazine, sports, burns, right, events, every, been, cable, think, game, make, decision, league" />
	<meta name="description" content="Back in 2003 ESPN made a huge commitment to HDTV. On the day of their launch we talked to Bryan Burns, who had a key responsibility for ESPN's venture into HDTV. INTERVIEW WITH BRYAN BURNS VICE PRESIDENT, STRATEGIC BUSINESS PLANNING..." />
	<meta name="title" content="INTERVIEW - Bryan Burns - ESPN" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Bryan Burns - ESPN" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_bryan_burns_espn.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Back in 2003 ESPN made a huge commitment to HDTV. On the day of their launch we talked to Bryan Burns, who had a key responsibility for ESPN's venture into HDTV. INTERVIEW WITH BRYAN BURNS VICE PRESIDENT, STRATEGIC BUSINESS PLANNING..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=63', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_bryan_burns_espn.php">INTERVIEW - Bryan Burns - ESPN</a></td>
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
				<p><em>Back in 2003 ESPN made a huge commitment to HDTV. On the day of their launch we talked to Bryan Burns, who had a key responsibility for ESPN's venture into HDTV.</em></p>

<p>INTERVIEW </p>

<p>WITH  </p>

<p>BRYAN BURNS<br />
VICE PRESIDENT, STRATEGIC BUSINESS PLANNING AND DEVELOPMENT</p>

<p>ESPN</p>

<p><br />
Bryan Burns</p>

<p>I am very pleased to bring to you an interview with the man who is making a great deal of HDTV history, Bryan Burns, from ESPN. It was last September when we sent out an HDTV Magazine EXTRA to break the news that ESPN was going to have an HDTV channel. This was particularly rewarding to me for ESPN has been a reader of ours (when we published the HDTV Newsletter) for 18 years. I wanted to bring you the words of Bryan, which I think are some of the most exciting I have heard since being in HDTV,  Sports fans, you have to know now there is a benevolent cosmos looking out for you.</p>

<p></p>

<p>--------------------------------------------------------------------------------</p>

<p>"We are going to use every source we have, and we have a bunch of sources."<br />
__Bryan Burns, ESPN</p>

<p><br />
--------------------------------------------------------------------------------</p>

<p>Bryan Burns came to ESPN from The Paragon Alliance, a consulting firm he founded in 1992, and that after sixteen years in professional sports team management. His MLB career included seven years as Senior Vice President of Major League Baseball, where his responsibilities included handling MLB’s worldwide television operations and overseeing special events such as the World Series, League Championship Series, and the All Star Game. Burns also served as Director of Marketing and Broadcasting for the Kansas City Royals from 1974-1983. At Comsat Video Enterprises from 1990-1992, Burns oversaw negotiations for the major sporting and special events included in the company’s Satellite Cinema and On Command Video pay-per-view services. Since April of 2000 Bryan Burns has been the strategic business planning and development Vice President for ESPN.</p>

<p>He joined ESPN in 1996. He has been responsible for the expansion of ESPN’s pay per view product to include ESPN FULL COURT for college basketball, ESPN Game Plan for college football, and MLS/ESPN Shootout for Major League Soccer. He also designed and launched ESPN NOW and ESPN EXTRA, the company’s channels, which are designed for digital cable and the expanded capacity of Direct Broadcast Satellite carriers. Burns also was responsible for ESPN’s special markets efforts to non-residential distributors such as commercial establishments and hotels.</p>

<p>Now Burns enters the new world of HDTVas the head of ESPN HD, the company’s new channel for high definition television, that launches the end of next month. </p>

<p>We talked to him by cell phone yesterday as he was driving in Eastern Connecticut</p>

<p>I started the interview by asking....</p>

<p>HDTV Magazine: How long had you considered going into HDTV as a strategic move for ESPN?</p>

<p>Bryan: I first saw HDTV in 1989 when working in Major League baseball. It has been on my radar screen ever since. </p>

<p>But specifically, we have been looking at HDTV with increasingly higher levels of intensity as time has gone by over the last two years. At about a year into our study we realized it was not a matter of if, it was only a matter of when. Choosing our "when" time was going to be our toughest assignment. When do we dive into the pool. Then we had to decide if we were going to dive in the shallow end or the deep end based on all kinds of market factors which you have been following for quite some time.</p>

<p>HDTV Magazine: What was the tipping point that actually caused you to make the decision?</p>

<p>Bryan: I don't know if there was one thing. We announced this our move into High Defintion in September of last year. We had gone to our distributors at national (NCTA) show for cable in June of last year and asked them about their interests, technical specifications, etc. Wen they basically said, "Bring it on because if we are going to HDTV in sports we want it to be ESPN." That gave us the last push to go. A story I often tell is this one. I was sitting in my office in November or early December where I have three TVs used to watch our various networks. On all of them at every commercial break we were selling widescreen TV for somebody. I would see Zenith in one break, Circuit City in the next, and Sears in the next. I sat back and thought, yes, we did make this call at the right time. This IS the right time.</p>

<p>HDTV Magazine: We believe it is. </p>

<p>Byran: We did keep quiet until our announcement. But when we did a reporter called and asked about a comment that Gary Shapiro, president of CEA, had made. He said that with ESPN going to HDTV a tipping point was reached in this business. I called him the next day to thank him and added, "We totally believe in what you said." </p>

<p>We felt all along with our kind of content and this kind of brand we had the opportunity to move the needle for the entire business. HDTV has needed content in sports from someone who can use the various mediums that we have, be that ESPN 1, ESPN2, ESPN Classic, ESPN News, ESPN.COM, ESPN the Magazine, ESPN Radio networks, etc., etc., to drive this information home to the consumer. We can do that like nobody else can!</p>

<p>HDTV Magazine: We have urged the entire broadcast community--all programmers and distributors--to unleash their tremendous power of influence across the nation, but they have hardly used any of that potential for this transition. </p>

<p>Bryan: I totally agree. </p>

<p>HDTV Magazine: That can only suggest to us that not everyone is ready yet to say, "Let's through it into high gear."</p>

<p>Bryan: Just moments ago I was on a conference call with our consumer marketing folks about the production of promotional spots that are going to run on all of our networks about the early ESPN HD events such, as the Opener of Sunday Night Baseball, the Women's Final Four. The discussion was about how we were going to put into those promotions the fact that these events are also on ESPN HD. I noticed last week in the promotion of the Grammys (CBS) that there was no mention of HDTV. </p>

<p>We have a new service to launch and we are going to use all the media we have to tell the distributor, the consumers, and retailer community that it is coming. We are going to use every source we have, and we have a bunch of sources. </p>

<p>HDTV Magazine: Will retailers have a free license to tune you in and display your programming in their retail environments?</p>

<p>Bryan: There is a step for us in-between, of course, and that is the distributor. Generally speaking our distributor agreements with cable or satellite allow them to provide our programming (ESPN 1 and ESPN 2 -- anything we have) to retailers without charge for promotional purposes. We want that. We encourage that. That is a big key here. Absolutely! </p>

<p>HDTV Magazine: I note from your background that you had spent time in developing markets for bars and hotels. Is the Sports Bar going to be a part of your strategy?</p>

<p>Bryan: We have a very interesting set of constituents with whom we work. We have consumers, fans, and people on the street, like you and I, who like sports and consume an awful lot of ESPN. We have distributors -- cable and satellite. We have advertisers...such as Anheuser-Busch, Coors, Miller...You can expect that once we get this thing in the air (30 days from today) we will work very hard across all of our constituents to marry them for maximum impact. We fully recognize how that can work. I also recognize that part of our advertising community...well, we have the largest list of advertisers of any telecaster in the country. I think there are  800 active advertisers on ESPN right now across our various family of networks, and for a very simple reason: We help folks sell things.</p>

<p>That list of companies includes the companies I already mentioned as well as Zenith, Samsung, etc. Yes, I am going to find a marriage somehow, some way, and walk into that community and say, " We have an opportunity here. How are we going to do this?" We just need to figure out how to do it and make sure all of the planets are aligned in the right way to bring a turn key operation into commercial establishments. It's pretty simple. When you walk into a bar, what's on TV? ESPN. We will find a way to do that. It would be silly not to.</p>

<p>HDTV Magazine: The programming that you mentioned strike me as  being of  the highest of marquis value? Is that correct?</p>

<p>Bryan: Let me take away any belief you have that we are not doing our highest marquis productions. We had manyf ways we could have gone. We could not physically do all of our events in a year's time right out of the box. We decided to go with what we call our big events strategy. We are going to do the NFL. We are going to do Major League Baseball. We are going to do the National Hockey League. We are going to do the NBA. So, yes, the four major pro sports leagues. We are the only television entity who has ever had all four under contract at one time. We are doing them all in High Def. </p>

<p>In our minds (the combination of) ESPN and HDTV really blossom about a year from now. ESPN HD as we started is kind of an on-ramp for what it is really going to be in a year from now. Chuck Pagano, your friend for many years, has built a new digital center in Bristol, Connecticut. It is going to be 120,000 square feet. Right now it is built; it is heated; it is cooled, but it is not yet outfitted. Chuck will start to buy the electronic guts for that building at the National Association of Broadcasters convention beginning in a few weeks time (Las Vegas in April). When it is done in a year from now we think it will be the largest HDTV facility in the world because we are converting our entire operation here. What that means for you and your readers is that when we get it done we are going to start producing most of our studio programming in native High-Definition. We will add 3700 hours per year studio native High Definition Television in about a year from now!</p>

<p>What does that mean for our on ramp year? We went to the national cable show (NCTA) and asked our distributors--cable and satellite--if they want us to do an event from time-to-time, or do they want something all of the time, knowing that if it is all the time we just can't yet do it all in HDTV. They said, "We want to set it and forget it. We do not want to have to send a guy to the head end every time you guys do a basketball game and tweak the bandwidth. We just can't do that."</p>

<p>Using that as our marching orders we are going to start by taking the ESPN service and upconvert it 24 hours a day. I understand that upconversion is like chalk across the blackboard to many of the 'purist' who have been in this for awhile, but it is the only way to start and have a service going all of the time.</p>

<p>From a remote production standpoint it would take 40 remote trucks to do what ESPN does (presently) in a year's time. I just could not make that economically work, as you might guess. We have commissioned three trucks to be built. The first comes off the production line today. That will allow us to do two to three events a week in the first year. We have to do 'truck logistics'. We look at things like the NBA finals and say, O.K. we will do game 2 and we will do game 5, but we can't do game 2 and game 5 if they are further than 600 miles away because I can't get the truck there in time. We are spending a lot of time maximizing the logistics of truck operations in an attempt to get the most telecast that we can squeeze out of these three units. So, what we have in this first "on ramp" year is an upconverted 24/7 signal with as many events as we can logistically put together, with an emphasis on our big events. </p>

<p>A year from now, when the center is built, we can start making programs almost overnight for half of a year's time produced daily. In a year and a half from now we will go on. Let there be no question that we have a long term commitment here. We are spending oodles of money and oodles of time, and our people are fully engaged. We have three thousand people working at ESPN and many from a technical background. They could work for us or channel 3. I t would not make much of a difference. But (with HDTV) they really care. They have waited their whole life to work on HDTV. </p>

<p>HDTV Magagzine That is a common phenomena found in every sector but retail.</p>

<p>HDTV Magazine: Are you all 720P.</p>

<p>Bryan: I am sure you have talked to Alex Wallau (president of ABC). He feels very strongly about it, as do we. Our view is that its not about the 1080 and the 720, it's about the "p" and about the "i". If you and I are doing the news, it's not a big deal (interlace or pgoressive), but for motion in sports, for pucks, bats, balls, nintey eight mile per hour sliders...progressive scan is going to cover the motion of sports better than interlace. It was a tough call for us, in part, because we knew it would make our costs higher. There is a lot of progressive scan pieces that have not yet been built. But we felt like we were in a VHS/BETAMAX decision process. We felt that, hey, we are ESPN and we have to make the right decision for all sports. The right decision for all sports is progressive scan. We see that if we go to Best Buy and ask to see the best DVD player that they have they show you a progressive scan. It is the best. We have to do things the best way.</p>

<p>HDTV Magazine: I think the audience is growing up with respect to this issue and have come to understand the trade offs. There is an increasing awareness that 1080 i may be better for still and filmed images while 720p delivers a better result when motion is present. I doubt it is an issue.</p>

<p>Byran. We felt we were making a ten year decision, perhaps a decision for all times. We were making a progressive decision over that of interlace. I was a little surprised to go to the CES this year and not get hit on this position.  I think you are right. It is a non issue.</p>

<p>HDTV Magazine: Who will be carrying you? </p>

<p>I will leave the specific answer to another in our company but will tell you that we are in very active conversations with every one of our distributers--cable, satellite, large, small--very active discussion with all of them on both business and engineering levels. I think it is clear that we are not going to start with every operator in the country signed up. But the conversations are ongoing and intense. We think the things we are going to do on our 'air' will help stimulate interest at every level and help in the process of carriage and clearance. Hopefully, we can wrap up carriage arrangements as soon as possible.</p>

<p>HDTV Magazine: Will you have any conditional access of any kind where you will be blacking out programming?</p>

<p>Bryan: Blackouts are a way of life with us. We will have dual pathways to our distributors just as we have in our standard definition. That is another expense we have  that other programmers in HDTV don't have. It is the nature of sports. </p>

<p>HDTV Magazine: Thank you Bryan and good luck in your HDTV ventures. We applaud you.</p>

<p>***</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June  9, 2005  8:50 PM</b>
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
			<?=getComments(63)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 63)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_bryan_burns_espn.php" type="text/javascript" charset="utf-8"></script>
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