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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 3562";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3562 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3562 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3562";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3562";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2" />
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
	<title>HDTV Magazine - Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2</title>
	<meta name="keywords" content="toy story, blu ray, combo pack, pre book, walt disney, toy, story, pixar, disney, dvd, feature, combo, english, blu, ray, dolby, buzz, pack, spanish, french, pre, entertainment, book, time, may" />
	<meta name="description" content="This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.

For the first time ever, these two groundbreaking films will be presented..." />
	<meta name="title" content="Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp;amp; Toy Story 2" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp;amp; Toy Story 2" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.

For the first time ever, these two groundbreaking films will be presented..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3562', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php">Two Disney-Pixar Animated Classics Come to Blu-ray: Toy Story &amp; Toy Story 2</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>February 23, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p class="prtitle">Two Disney-Pixar Animated Classics Come to Life as They've Never Been Seen Before</p>

<center><i>TOY STORY &amp; TOY STORY 2 Blasting Off For the First Time Ever in High Definition On Blu-ray(TM) + DVD Combo Pack March 23, 2010 Special Edition DVDs Available May 11, 2010</center></i><br />
<br />

<p><strong>BURBANK, Calif., Feb. 23 /PRNewswire/ -- </strong>This Spring, Walt Disney Studios Home Entertainment (WDSHE) proudly presents the eagerly awaited high definition debuts of Disney-Pixar's original animated classics Toy Story and Toy Story 2. In 1995, Toy Story made history as the first feature-length computer animated film and, together with its beloved sequel Toy Story 2, helped establish Disney-Pixar as creators of unrivaled quality family entertainment. Now, viewers can rediscover these wondrous tales of what happens when humans leave the room -- and toys come to life -- as Toy Story and Toy Story 2 debut on Disney Blu-ray(TM) + DVD Combo Pack, followed seven weeks later by the Special Edition DVDs.</p>

<p>For the first time ever, these two groundbreaking films will be presented with eye-popping 1080p high definition picture and 5.1 DTS-HD Master Audio sound, along with hours of innovative new bonus features. Also included are exclusive sneak peeks at Disney-Pixar's Toy Story 3, hitting U.S. theaters on June 18, 2010.</p>

<p>Academy Award&reg;-winner John Lasseter (1996, Special Achievement Award, Toy Story), directs these two action-packed, laugh-filled animated favorites featuring the superstar voice talents of Tom Hanks (Angels &amp; Demons) and Tim Allen (The Santa Clause), along with Don Rickles (Casino), Annie Potts (Ghostbusters), Wallace Shawn (The Princess Bride) and, of course, Pixar favorite John Ratzenberger (TV's "Cheers"). Together, Toy Story and Toy Story 2 launched the Disney-Pixar label and sent audiences to the infinity of family entertainment fun and beyond...as they're sure to do once again on Blu-ray(TM).</p>

<p>TOY STORY Blu-ray(TM) + DVD Combo Pack All-New Bonus Features<br />
<ul><li>The Story: An Exclusive Sneak Peek at Toy Story 3</li><li>Buzz Lightyear Mission Logs:</li><li>Episode One: Blast Off - Buzz reports back about his adventure to the International Space Station.</li><li>Paths to Pixar: Artists - In this series of shorts, Pixar artists talk about their career path and share advice to aspiring filmmakers and animators.</li><li>Studio Stories: A series of amusing anecdotal shorts about life at Pixar.</li><li>"John's Car" recounts how Toy Story director John Lasseter refused to stop driving his beat-up car even after the film's success.</li><li>"Baby AJ" tells the hilarious story of how important Halloween is to the Pixar team and how one Pixar employee dressed up as the oversized baby from the short Tin Toy to win a prize.</li><li>"Scooter Races" takes the audience on a rousing scooter race around the studio with John Lasseter and other employees.</li><li>Buzz Takes Manhattan - Spectacular footage from Buzz Lightyear's premiere as a Macy's Thanksgiving Day Parade balloon.</li><li>Black Friday: The Toy Story You Never Saw - The Toy Story filmmakers discuss the early cut of the film that very nearly shut down production entirely.</li></ul></p>

<p>TOY STORY 2 Blu-ray(TM) + DVD Combo Pack All-New Bonus Features<br />
<ul><li>Characters: An Exclusive Sneak Peek At Toy Story 3</li><li>Director Commentary</li><li>Buzz Lightyear Mission Logs:</li><li>Episode Two: International Space Station - Buzz reports back about his adventure to the International Space Station.</li><li>Paths to Pixar: Technical Artists - In this series of shorts, Pixar artists talk about their career path and share advice to aspiring filmmakers and animators.</li><li>Studio Stories: A series of amusing anecdotal shorts about life at Pixar.</li><li>"Toy Story 2 Sleep Deprivation Lab" gives audiences an inside peek into the intense time pressures under which the Toy Story 2 editors found themselves.</li><li>"Studio Stories: Pinocchio" shows an impromptu competition between the animators to decorate their workspace by throwing toys into the ceiling.</li><li>"Studio Stories: The Movie Vanishes" tells how a mischievous technical error almost erased all of Toy Story 2 from the computer system.</li><li>Pixar's Zoetrope - A look at the creation of the live-action zoetrope that Pixar created to capture the principles of animation in a live sculpture.</li><li>Celebrating our Friend Joe Ranft - A tribute to beloved Disney and Pixar story man Joe Ranft, regarded as one of the industry's most gifted story artists.</li></ul></p>

<p><br />
<strong>Toy Story Synopsis:</strong></p>

<p>Toy Story, the first full-length computer-animated feature film, is "a wonder to behold." - People Magazine. Experience a hilarious fantasy about the lives toys lead when they're left alone. Woody (voiced by Tom Hanks), an old-fashioned cowboy doll, is Andy's favorite. But when Andy gets Buzz Lightyear (voiced by Tim Allen) for his birthday, the flashy new space hero takes Andy's room by storm! Their rivalry leaves them lost with a toy's worst nightmare -- Sid, the toy-torturing boy next door. Woody and Buzz must work together to escape, realizing along the way that they've got a friend ... in each other!</p>

<p><br />
<strong>Toy Story 2 Synopsis:</strong></p>

<p>A Golden Globe&reg; Award winner for Best Motion Picture - Comedy Or Musical, Toy Story 2 has become a favorite all across the world, garnering praise from fans and critics everywhere. It's "an instant classic," raved New York Magazine. While Andy is away at summer camp, Woody is toynapped by Al McWhiggin, a greedy collector who needs Andy's favorite toy to complete his "Woody's Roundup" collection. Together with Jessie (voiced by Joan Cusack), Bullseye, and the Prospector, Woody is on his way to a museum where he'll spend the rest of his life behind glass. It's up to Buzz, Mr. Potato Head, Hamm, Rex, and Slinky Dog to rescue their friend and remind him what being a toy is all about.</p>

<p>The Toy Story and Toy Story 2 Blu-ray(TM) + DVD Combo Packs are priced at an SRP of $39.99. The Special Edition DVDs are priced at an SRP of $29.99.</p>

<pre>
  TOY STORY BD &amp; DVD COMBO PACK PRODUCT DETAILS:

<p>  STREET DATE:     March 23, 2010<br />
  ---------------------------------------<br />
  Direct pre-book:     1/26/10<br />
  Distributor pre-book:    2/09/10<br />
  Suggested Retail Price:  $39.99<br />
  Feature run time:    Approximately 81 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1.78:1<br />
  Video:       1080p, Widescreen<br />
  Sound:       5.1 DTS-HD ES, English 2.0 DTS-HD,       English DVS 2.0 Dolby; French 5.1 Dolby       EX; Spanish 5:1 Dolby EX<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY 2 BD &amp; DVD COMBO PACK PRODUCT DETAILS:<br />
  STREET DATE:     March 23, 2010<br />
  ---------------------------------------<br />
  Direct pre-book:     1/26/10<br />
  Distributor pre-book:    2/09/10<br />
  Suggested Retail Price:  $39.99<br />
  Feature run time:    Approximately 92 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    2.35:1<br />
  Video:       1080p, Widescreen<br />
  Sound:       5.1 DTS-HD ES, English 2.0 DTS-HD,       English DVS 2.0 Dolby; French 5.1 Dolby       EX; Spanish 5:1 Dolby EX<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY SPECIAL EDITION DVD PRODUCT DETAILS:<br />
  STREET DATE:     May 11, 2010<br />
  -------------------------------------<br />
  Direct pre-book:     3/16/10<br />
  Distributor pre-book:    3/30/10<br />
  Suggested Retail Price:  $29.99<br />
  Feature run time:    Approximately 81 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1:78:1<br />
  Sound:       5.1 EX and 2.0 Dolby Digital English,       French (Canadian only), Dedicated       Spanish Language SKU<br />
  Subtitles:       English SDH, French, Spanish</p>

<p></p>

<p>  TOY STORY 2 SPECIAL EDITION DVD PRODUCT DETAILS:<br />
  STREET DATE:     May 11, 2010<br />
  -------------------------------------<br />
  Direct pre-book:     3/16/10<br />
  Distributor pre-book:    3/30/10<br />
  Suggested Retail Price:  $29.99<br />
  Feature run time:    Approximately 92 minutes<br />
  Rated:  USA:     G<br />
  The following technical specifications may only apply to feature:<br />
  Aspect ratio:    1:78:1<br />
  Sound:       5.1 EX and 2.0 Dolby Digital English,       French (Canadian only), Dedicated       Spanish Language SKU<br />
  Subtitles:       English SDH, French, Spanish<br />
</pre></p>

<p><br />
<strong>About Disney's Combo Pack:</strong></p>

<p>To provide consumers with unprecedented quality, value and portability of their favorite Disney movies, in 2008 WDSHE pioneered the Combo Pack - a Blu-ray Disc(TM) plus a DVD and a DisneyFile Digital Copy of the film in a single package. Current Disney-branded titles available as Combo Packs include High School Musical 3, Beverly Hills Chihuahua, Bolt, Bedtime Stories, Race To Witch Mountain, Jonas Brothers and Hannah Montana The Movie.</p>

<p>Walt Disney Studios Home Entertainment, a recognized leader in the home entertainment industry, is the marketing, sales and distribution company for Walt Disney, Touchstone, Hollywood Pictures, Miramax and Buena Vista product, which includes DVD, Blu-ray Disc(TM) and electronic distribution. Walt Disney Studios Home Entertainment is a division of The Walt Disney Studios</p>

<p>These press materials are available in electronic form at <a target="_blank" href="http://www.WDSHEpublicity.com/">www.WDSHEpublicity.com</a>.</p>

<p>&copy;Walt Disney Studios Home Entertainment, Inc.</p>

<p>Source: Walt Disney Studios Home Entertainment(C)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>February 23, 2010  6:11 PM</b>
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
			<?=getComments(3562)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3562)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/02/two-disneypixar-animated-classics-come-to-bluray-toy-story-toy-story-2.php" type="text/javascript" charset="utf-8"></script>
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