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
		AND e.entry_id = 5013";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5013 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5013 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5013";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2013/01/living-with-4k-no-disc-the-end-of-collecting-movies.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5013";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K - No disc? The end of Collecting Movies?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K - No disc? The end of Collecting Movies?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K - No disc? The end of Collecting Movies?" />
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
	<title>HDTV Magazine - Living with 4K - No disc? The end of Collecting Movies?</title>
	<meta name="keywords" content="blu ray, pre recorded, recorded media, movie collectors, video quality, blu, ray, may, content, quality, disc, movie, recorded, pre, until, years, media, player, internet, sony, rather, available, message, time, players" />
	<meta name="description" content="As mentioned in the previous article of this “Living with 4K” series two companies (Red and Sony) have announced their solution for playing 4K content on the new Ultra-HDTV displays introduced in 2012 and showed at CES 2013 in much larger selection by many manufacturers, including Samsung, Sony, LG, Sharp, Hisense, Westinghouse, Vizio, and Radio Shack (just testing if you are paying attention). 

One common denominator of these two companies is that their 4K players are not disc based, such as a new Blu-ray disc with larger capacity and more efficient compression for 4K could be, the units rather download, store, and playback 4K content using an Internet connection and an internal hard disc drive, like one would do using a computer.  

Red’s 4K player is called, guess what?, Redray, with an MSRP of..." />
	<meta name="title" content="Living with 4K - No disc? The end of Collecting Movies?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K - No disc? The end of Collecting Movies?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2013/01/living-with-4k-no-disc-the-end-of-collecting-movies.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As mentioned in the previous article of this “Living with 4K” series two companies (Red and Sony) have announced their solution for playing 4K content on the new Ultra-HDTV displays introduced in 2012 and showed at CES 2013 in much larger selection by many manufacturers, including Samsung, Sony, LG, Sharp, Hisense, Westinghouse, Vizio, and Radio Shack (just testing if you are paying attention). 

One common denominator of these two companies is that their 4K players are not disc based, such as a new Blu-ray disc with larger capacity and more efficient compression for 4K could be, the units rather download, store, and playback 4K content using an Internet connection and an internal hard disc drive, like one would do using a computer.  

Red’s 4K player is called, guess what?, Redray, with an MSRP of..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5013', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2013/01/living-with-4k-no-disc-the-end-of-collecting-movies.php">Living with 4K - No disc? The end of Collecting Movies?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 25, 2013</b>
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
				<h2>The Players</h2> <p>As mentioned in the previous article of this “Living with 4K” series two companies (Red and Sony) have announced their solution for playing 4K content on the new Ultra-HDTV displays introduced in 2012 and showed at CES 2013 in much larger selection by many manufacturers, including Samsung, Sony, LG, Sharp, Hisense, Westinghouse, Vizio, and Radio Shack (just testing if you are paying attention). <img style="border-right-width: 0px; margin: 10px 20px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="RedRay 4K Player" border="0" alt="RedRay 4K Player" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KNodisc_DE81/image_3.png" width="464" height="234">  <p>One common denominator of these two companies is that their 4K players are not disc based, such as a new Blu-ray disc with larger capacity and more efficient compression for 4K could be, the units rather download, store, and playback 4K content using an Internet connection and an internal hard disc drive, like one would do using a computer.&nbsp;&nbsp; <p>Red’s 4K player is called, guess what?, <a href="http://www.red.com/products/redray">Redray</a>, with an MSRP of $1450, available early in 2013 the company says, check the previous article for technical details.&nbsp; The player uses a 4K service provider: <a href="http://odemax.com/information.html">Odemax</a>, that was announced to be live by March 2013.  <p>Sony’s player and 4K movie service were announced at CES 2013 as to be available by mid-2013, check also the details in the previous article. In addition, Sony already has a 4K server the company lends to the buyers of their new $25,000 4K panels so they appreciate what their panel can do with 4K quality content, rather than just up-scaling 1080p Blu-rays.&nbsp; The server has stored a few 4K movies, not necessarily the latest hits, and is configured as a Dell PC, I received that server this week and I am in the process of installing it with my 4K Sony projector and test it for a review, so stay tuned.  <p>I am also scheduled to review the Redray 4K player when the company can make one available for a review, and, when Sony makes available a review unit of their near future 4K player, I plan to do the same.&nbsp; <img style="border-right-width: 0px; margin: 10px 20px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Sony 4K player - Expected by Mid 2013" border="0" alt="Sony 4K player - Expected by Mid 2013" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KNodisc_DE81/image_6.png" width="359" height="271">  <p>But this article is not about players, is about the, arguably loud and clear, message these announcements are giving to movie collectors, those that for years have been accustomed to buy their own movies for their collection with the best audio and video quality there is, because watching a movie is just one of the pleasures of having it, the feeling of ownership of a collected item cannot be replaced by Internet content, but I know many would not understand that concept and still listening to an over-compressed MP3 version rather than buying the CD.&nbsp; <p>&nbsp; <h2>The Message</h2> <p>The message seems to be clear from Redray because their purpose is primarily to distribute content with Odemax to theaters and to those that buy their player, or their projector with the incorporated player, and Red has developed their own very efficient compression algorithm for the 4K content to require no more than what HDTV broadcast requires today.  <p>But the message is not 100% clear from Sony because their mid-year server maybe a message of a) forget about a 4K disc, ever, or b) there is no disc yet but we can offer you a way to consume 4K content for the time being, not necessarily admitting it as in lieu of future pre-recorded discs, but to rather fill a hole until there is one, or even after there is one, but the reality is: if there is one.&nbsp;&nbsp;&nbsp; <p>As I mentioned in my other article the Blu-ray Association consistently declared for over a year that they are NOT working on a 4K disc solution, but you may join me in the analysis below in that such statement may not be telling anything other than not saying “<em>we want Blu-ray to be alive for a few more years and we do not want to cause an stampede of people stopping buying Blu-ray discs if we announce that 4K ray spec standard is coming”</em>.&nbsp; <p>Regardless if a 4K disc would be available or not, as a consumer of content I like to have alternatives, rather than being imposed a format or service that follows a mass trend of streaming and downloading mediocre quality because it may be practical to most people.&nbsp; <p>Additionally, judging by the high number of LCDs people buy, rather than quality plasma imaging, and their interest of viewing over-compressed Internet content with Smart TVs, the message is loud and clear that the audience does not appreciate quality, or does not know how to appreciate quality, and unfortunately, judging by the down hill of brick and mortar good audio/video stores that can properly educate the public with quality demonstrations, the end result would be similar, besides, mediocrity with a red tag is what sells, and is here to stay.  <p>&nbsp; <h2>The Impact to Movie Collectors</h2> <p>As some of you already know, I am a movie collector since the era of laserdisc and original aspect ratio pre-recorded movies, and, until the audio/video quality of Blu-ray can be improved I will continue to love the image and sound quality of Blu-ray, which has not been matched by ANY streaming or downloading system or Internet service yet.<img style="border-right-width: 0px; margin: 10px 20px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Odemax 4K Content Service" border="0" alt="Odemax 4K Content Service" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KNodisc_DE81/image_9.png" width="467" height="286">&nbsp; <p>In addition, watching a movie is a ceremonial event for me. I enjoy pulling a movie case from the collection, look at the photos and peruse the brochures and comment with viewer friends before I dim out the lights of the home theater, then take the disc out and play it, check the additional materials in the menu, choose any of the many languages and subtitles offered, level the height of subtitles within my Cinemascope screen (try that with streamers), play the content on its original aspect ratio any time I want, even 10 years later without concern about running out of storage capacity and compression for hundreds of movies at their highest quality, play it for 5 minutes or for the whole movie or for the 20<sup>th</sup> time or when a friend comes over to my home-theater and asks me to watch what he/she prefers at that given moment (not a few hours later when the 4K download finishes or what is currently in the hard drive).&nbsp; <p>In other words, it is a pleasant ceremony like going to Cinerama theaters in the 50s/60s and witness how the huge screen curtains open, and keep opening, and opening beyond our peripheral vision, preparing you for something that transports you to another world, and it did.&nbsp;&nbsp;&nbsp;&nbsp; <p>The picture above is the 4K Media Player Sony announced at CES 2013 to be available by mid 2013, round?  <p>&nbsp; <h2>The Impact of Having or Not a 4K Disc Now</h2> <p>There may be a chance that with the concept of these downloading players the days of pre-recorded media may actually be over sooner than expected.&nbsp; The preachers of “Internet content has taken over pre-recorded media” may indeed soon prepare their party drinks while casually enjoying their beloved mediocre image and sound, typically preferred due to practicality and in most cases driven by a free ride that overtakes sound and video quality, courtesy of the Internet and the MP3 style of consuming content in the over-compressed digital world, which no question has a place that should be respected but not in the quality world.  <p>We will have to wait until these two 4K Internet players are introduced to confirm if their quality can actually be better than pre-recorded media, and how the pre-recorded media model of the past decades may be affected.&nbsp; We may have to wait longer to confirm that an actual 4K Blu-ray disc (or any “ray” disc) may not be introduced ever, or if this downloading 4K service maybe filling a hole, but one thing is Odemax delivering a 4K movie to a local theater for viewing it from the hard drive for just a couple of weeks until the next movie hit, and another thing is believing that the same approach can be welcomed by movie collectors accustomed to keep the content forever, and expect that pre-recorded media would not be needed any longer.&nbsp; <p>Looking back at the events of pre-recorded media, Blu-ray may follow the steps of DVD and 4K may never exist.&nbsp; DVD pre-recorded format was introduced in 1996/7 as 480i digital discs based in an analog NTSC television system just 2 years before the introduction of digital 1080i HDTV in 1998, a display that was capable of showing 6 times the resolution of that DVD format but had to wait until Blu-ray was introduced 8 years later in 2006 to match what the TV could do with pre-recorded media.&nbsp; <p>Some say the delay was planned because the Gods in Hollywood wanted to milk the cow of DVD sales until it totally dried out, which is actually happening for a number of reasons.&nbsp; A Blu-ray disc introduction before such time could have produced an early demise of DVD sales if consumers switched to Blu-ray earlier to enjoy the better image and sound comparable to the HDTV many early adopters already had for 8 years since 1998, and still enjoyed it watching pre-recorded letterboxed laserdiscs and anamorphic DVDs instead.  <p>In other words, regardless if Blu-ray may have been technically ready years before its actual introduction in 2006, an approach of “let us hold the Blu-ray horses until the time is right” may be the repeat destiny of 4K media until Blu-ray can be milked to the maximum, and possibly a) hold a 4K disc introduction for a similar long period of 8 years (HDTV 1998 – Blu-ray 2006 = 8), or b) cause that a 4K disc may never see the light if the downloading/streaming Gods impose it to us as the only option of modern times, and “resistance is futile”.  <p>I sincerely hope that the 4K downloaded content would not be as weak in audio/video quality as the <a href="http://www.hdtvmagazine.com/articles/2011/06/streaming-inflation.php">current Internet based content</a> is, and that the needs of movie collectors be considered in that business model, otherwise it may be better to stay in the era of 1080p Blu-ray discs, when quality was still respected as a personal choice in a world of good enough mediocrity. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 25, 2013  1:47 PM</b>
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
			<?=getComments(5013)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5013)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2013/01/living-with-4k-no-disc-the-end-of-collecting-movies.php" type="text/javascript" charset="utf-8"></script>
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