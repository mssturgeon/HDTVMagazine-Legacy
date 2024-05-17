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
		AND e.entry_id = 2931";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 2931 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 2931 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 2931";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2010/03/from-the-archives-the-next-generation-of-television.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 2931";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download From the Archives - The Next Generation of Television" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="From the Archives - The Next Generation of Television" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="From the Archives - The Next Generation of Television" />
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
	<title>HDTV Magazine - From the Archives - The Next Generation of Television</title>
	<meta name="keywords" content="ntsc set, field vision, height picture, times height, grand alliance, hdtv, ntsc, television, set, system, color, sets, japan, could, picture, europe, standard, projectors, world, digital, well, those, time, high, vision" />
	<meta name="description" content="Those of you who have been long-time readers of HDTV Magazine, know that we've been around since the beginning of the movement, quite literally. I began working with HDTV back in the 80's. We had the first newsletter on HDTV (The HDTV Newsletter) and the first website devoted to HDTV (HDTV Magazine, of course). I have penned many articles in the last 25 years on the subject, many of them &quot;lost&quot; in our archives. Well, it's time to bring them back and give them a permanent home.

The article below was written in 1994. It's quite a trip to read again now. I hope you enjoy it as much as I do." />
	<meta name="title" content="From the Archives - The Next Generation of Television" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="From the Archives - The Next Generation of Television" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2010/03/from-the-archives-the-next-generation-of-television.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Those of you who have been long-time readers of HDTV Magazine, know that we've been around since the beginning of the movement, quite literally. I began working with HDTV back in the 80's. We had the first newsletter on HDTV (The HDTV Newsletter) and the first website devoted to HDTV (HDTV Magazine, of course). I have penned many articles in the last 25 years on the subject, many of them &quot;lost&quot; in our archives. Well, it's time to bring them back and give them a permanent home.

The article below was written in 1994. It's quite a trip to read again now. I hope you enjoy it as much as I do." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=2931', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2010/03/from-the-archives-the-next-generation-of-television.php">From the Archives - The Next Generation of Television</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>March 24, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=359&category=HDTV History">HDTV History</a></b>
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
				<div class="editorial">Those of you who have been long-time readers of HDTV Magazine, know that we've been around since the beginning of the movement, quite literally. I began working with HDTV back in the 80's. We had the first newsletter on HDTV (The HDTV Newsletter) and the first website devoted to HDTV (HDTV Magazine, of course). I have penned many articles in the last 25 years on the subject, many of them "lost" in our archives. Well, it's time to bring them back and give them a permanent home.<br />
	<br />
	The article below was written 16 years ago, in 1994. It's quite a trip to read again now. I hope you enjoy reading it now as much as I did writing it then.<br />
	<br />
	- Dale
</div>
The Next Generation of Television<br />
Dale Cripps, Publisher<br />
HDTV Newsletter<br />

<p>In this review I hope to give you some fundamental reasons why HDTV is certain to be a valuable asset to your future and to illustrate those things which still obstruct its commercialization. Trying to illustrate the value of HDTV without you enjoying it first--a few hour road test--is little like explaining in writing how bumble bees fly. I think I can do it, but there is a danger of getting us lost in the explanation.</p>

<p>High school drop out Thomas Edison started it with his invention of the phonograph. He ushered in the era of home entertainment appliances. That opened every nation to the idea of enjoying professionally produced and technically delivered entertainment and instructional programs in the home. Musicians all feared that Mr. Edison had ruined forever their trade. Who would hire them if platters or cylinders would substitute?</p>

<p>Another dropout from Russia, David Sarnoff, ushered in commercial radio broadcasting in the 1920s. Live programs with news, drama, and music were heard in cities and country farms. Newspapers reacted to the competition charging Sarnoff with single-handedly wanting to destroy their institutions with a contraption. They refused advertising for radio. Many refused to mention it in their news stories. But nothing stopped radio. Networks formed from regional alliances to build national services. Stars from stage and phonograph shifted to the "amazing" technology that distributed their voices to more people in one day than ever they could reach in a lifetime from the stage. Broadcasting was exciting. It was growing. It became important. It created a national community with a local lodge.</p>

<p>With Sarnoff again leading the way television struggled into spotty services following WWII. Some, then more, then more and more living rooms across the United States tuned very expensive sets to nightly experiments. Milton Berle gave the nation its last long look at the fading art of vaudeville. Radio personalities saw the future in television. William Paley (then Chairman of CBS ) ushered many to the medium. The audiences went with them. The golden age of television blossomed. Television became the greatest growth industry in the immediate post WWII era.</p>

<p>Color was another dream of engineers from the beginning. It didn't have quite the excitement within its development as did radio and black and white television. Those were, after all, brand new services creating whole new ways of looking at society. They also took very gutsy and exciting pioneers to get the services started. There were no sets to receive expensively created and transmitted signals. Signals had to be invested in until a pay-back could be realized many years hence--not months hence. Color was viewed certainly as a worthy aim and again, David Sarnoff, now a trusted Chairman of RCA (which owned NBC), headed the drive. In the process he gambled nearly all of his company's resources to achieve color television, nearly failing. Not only did he see it the glorious culmination of television engineering, he wanted to expand RCA's presence and dominate both the domestic market and foreign markets in Europe and Japan. To insure order in the development process a committee was established from within the industry. Their mission--create a color standard to be accepted by the FCC. The committee was called the National Television Systems Committee (NTSC). After lengthy struggles and one major misstep (where a quickly aborted standard using a color wheel occurred), the Committee agreed upon the compatible color system we have today. It is often called NTSC. Japan bought it and Europe rejected it. But both Japan and Europe entered the color television transmission business and began making sets for sale within their own regions, and later to all regions of the world. Sarnoff did not dominate color television around the world, but many of RCA's patents were finally used in every color system.</p>

<h2>Fast Forward to 1964</h2>
<strong>Japan</strong>
Japan had one national broadcaster after the war. The revenue to operate the network--Japan Broadcasting Corporation, or NHK-- comes from a monthly levy assessed every television set in use in Japan. Huge revenues are the result with a small percentage (about .5%) of that revenue is dedicated to technical research. Most innovation for professional or consumer television is developed in a cooperative between the Japanese electronic manufacturers and

<p>Dale Cr NHK's laboratories. Some of the most significant innovation in television has come from this cooperative of broadcast laboratory and private industry.</p>

<h2>The Next Generation of Television--HDTV</h2>
NHK began work on HDTV in 1964, the same year the Olympics were held in Japan. HDTV is not just about clearer pictures. It is about involvement in the program. That comes when certain image and audio conditions are met. This provides the viewer a near three dimensional effect. Meeting that challenge defined the technical parameters for HDTV production systems and HD receivers.

<h2>Defining It</h2>
HDTV, the definition went, will have five times the information of standard television. HDTV would have at least one thousand scanning lines interlace (where every other line is painted on the tube face in each of two fields, each field being 1/60th of a second in duration), have no visible picture artifacts, nor visible scanning lines from a proscribed viewing distance of three times the height of the picture. Bingo! The most important thing to remember when evaluating HDTV is if it meets this criteria--no lines or artifacts at three times the height of the picture.

<p>A tremendous international effort to standardize on a set of detailed parameters that had been selected mostly by the Japanese, i.e. 1125 scanning lines interlace, and 60 fields per second with an aspect ratio of 5:3 (changed later to 16;9) went into high gear following Algiers and raised the issue internationally. That effort failed to deliver one single production standard. Why is detailed later in this article.</p>

<h2>"30 Degree TV"</h2>
In addition to the viewing distance function is added a new aspect ratio (height of the picture to the width). The width is changed from the traditional 4:3 ratio to 16:9. All this means to say the proscribed viewing distance is much closer to the screen (e.g, if the screen is 1 ft tall the proscribed viewing distance is 3 ft from the screen). The precise reason for widening the screen is to provide a 30 degree field of vision for the viewer. This occurs when viewing exactly at the proscribed distance. A 30 degree field of vision is dramatically more "real" for the viewer compared to a ten degree field customarily with NTSC (standard) at the 8 to 10 picture heights distance. The key to understanding the difference between HDTV and standard TV is that clarity is a matter only of distance. Distance relates directly to how great your field of vision is. A 30 degree field of vision for the viewer is the goal of HDTV.

<h2>Why?</h2>
Why this need for a 30 degree viewing field? This is the most critical point to understand. It was determined from scientific experiments and with specific tests at NHK that the human visual system operates in two distinct ways. One, the central portion of your vision is quite sensitive to detail. You can read or see work in front of you with great acuity. But this central portion is not very good at fast motion detection. Move something rapidly across your eyes and it is a meaningless blur. Peripheral vision just ten degrees from center begins to lose detail acuity, but is increasingly sensitive to motion. If a television program is to provide a viewer with the greater sense of reality both the acuity of the central vision and the motion detection of more peripheral vision needs to be stimulated at the same time. Past 30 degrees there is no more to be gained. Under 30 degrees and there is a dramatic fall off. I once labeled HDTV "30 degree television" to emphasize this point.

<h2>More Behind Adding Clarity?</h2>
All well made NTSC TV sets pulling in a strong well transmitted signal will look clear at some distance--usually about 8 to 10 times the height of the picture. Due to the absolute limits of NTSC the image is never clearer than what 525 scanning lines interlaced written on the face of the tube can produce. There are also numerous inherently troublesome artifacts which appear until you are at least 8 times the picture height. This means that if you had an HDTV set and an NTSC set with the same picture height you would draw back much further on the NTSC set to gain acuity. Again, using a 1 ft height example, you are at 3 ft for the HDTV and 8 ft for the NTSC set. The field of vision is 30 degrees at that distance with the HDTV, and something less than 10 degrees with the NTSC set.

<p>Looking at it another way, we simply say that for a given room we can now add a much larger screen and not suffer those bad images we have all seen on big screen sets when we are too close. HDTV is about big screens and field of vision. That of course, is the fundamental value of HDTV.</p>

<h2>Color</h2>
You can say, "well, my NTSC set is fine and gives me great color." It may to the untrained eye, but HDTV carries five times the color information over NTSC. Where color definition has failed you in the past, leaving subtle beauties only to the imagination--now they are presented in their varying intensities of reds, violets, greens, and blues with all shades in-between. This is a Kodak picture that moves.

<h2>Transmitting The Pictures--No Signal, No Sale</h2>
It is one thing to take an HDTV picture and quite another to transmit it. In a closed system with camera and tape recorders you can work in a high bandwidth domain. But once you need to send that HDTV image somewhere you are faced with grueling restrictions, i.e., lower bandwidth channels. Following a broadcast demonstration in Washington, DC of the MUSE HDTV system in 1986, broadcasters feared that they could be left out if they didn't have a way to terrestrially transmit it. Cable could, by opening up their non-regulated spectrum. DBS could also. Telephone fiber system could without strain and said it was the reason to take fiber-to-the-home. The broadcasters asked the FCC to set up a process whereby they would first determine how much spectrum they would need, and then be granted it from the FCC to insure their ability to compete technically well into the next century.

<h2>It's A Squeeze</h2>
The FCC set everything in motion. Most have read about where a competitive process generated several highly evolved transmission schemes, each then tested at the Advanced Television Test Center in Virginia. That technical selection process turned out to be the most efficient in the world, climaxing in a post-competitive conglomeration and blending of efforts from former competitor proponents. This new group is known as the Grand Alliance (GA). The Grand Alliance is made up of General Instrument, Zenith, David Sarnoff Research Laboratories, AT&T, Compression Labs, MIT, Philips and Thompson. The system is created and will be delivered with only modest delays from the original timetable. Most significantly the process has put the US in the technical lead in global television technology. That technology is already doing the road widening work on the information super highway. Such leads are fragile in a world of evolving technology, though, and both Europe and Japan slumber not, but work feverishly in the aim to leap-frog the US within a few years.

<h2>On Schedule?</h2>
But getting this far at this time was once thought impossible. No more than four years past broadcast executives in the technical know were saying publicly it wouldn't happen until we were all flying aero-cars to work. But it did. In the week of February 21st the system received its final blessing from the technical sub-group to the FCC Advisory Committee, headed by former FCC Chairman, Richard Wiley. With the last piece now in place with the 8 VSB (Vestigial Side Band) transmission system selected in late February, the final hardware will now be built for final testing of the system. That is to be done by the first quarter of next year. The FCC expects to set a standard in mid to late 1995.

<h2>A Grand Alliance</h2>
The GA system is both a data compression scheme, using MPEG 2 (Motion Picture Experts Group), and a channel coding scheme, using the 8 VSB method offered from Zenith. I won't go into any technical details here other than to say that the choice of the MPEG compression standard was made in order to be a part in the international effort in standardizing digital compression/decompression. The choice of 8 VSB proved in testing to be the most robust against the QAM (Quadrature Amplitude Modulation) candidate. VSB proved to have the longest reach (from a transmitter) under interference laden conditions. This means you can live a little farther from a tower, have more cars and airplanes go by, have adjacent channels closer in, and even have channels using the same frequencies not so far away, and still receive perfect HDTV pictures. Digital television has the unusual character of adding up all the transmission errors at the set, and if the errors have gone beyond the system's ability to conceal or overcome, the picture simply disappears. Analog pictures are more of the General Mac Arthur model--they just fade away. But that is analog's greatest feature, and millions of viewers in fringe areas may not be able to receive the digital signal with the same kind of antennas they use to get at least "something" on their old analog NTSC sets. This fact has worried broadcasters the most since they are paid for the viewers they attract to their sponsors. Any shortening of the viewing radius from a tower means either expensive repeaters, or a loss of ad revenue.

<h2>Audio</h2>
Leave it to an image guy to save audio for last. I am not an audio expert. I will say that the Grand Alliance has, after extensive qualifying tests at Lucas Ranch in Northern California, selected the Dolby AC 3 system. This system reportedly has no audible compression artifacts and is a five channel surround system. Until recently sound experts had not associated high-end audio with video. This new betrothal is just waiting for the wedding bells. Everyone I know in the lay world has remarked that it was the addition of audio quality that enhanced immeasurably their enjoyment of television viewing. Movies on tape or disc with high grade audio tracks do make a big difference, but wait until digital audio surround hits you coupled with "30 degree" television. Write me and tell me I was wrong. I will wait a long time for such a letter.

<h2>What Will These Sets Look Like?</h2>
Make no mistake, HDTV is a major home appliance. It will typically not fit under the kitchen cabinet. You will want to plan for where your HDTV set can go. If you are fortunate, you will have a wall which can be modified for recessing a larger rear projection unit. Tube type sets will be large and bulky. They will correspond in weight and size to the larger tube type NTSC sets, only wider by 20%. Rear projection systems will be wider also, but not necessarily deeper. Interior designers will increasingly have experts on call and be able to knowledgeably design, specify, and outfit your media room. Due to the inherently higher price of HDTV it is likely such experts will be your best high-end outlets.

<p>Both CRT sets and rear CRT projection sets will dominate the market for several years to come. Front projection systems will also have their place when extra large screens are wanted. Beautiful home theaters costing tens to even hundreds of thousands of dollars are already being built using HDTV-ready projectors. These projectors are able to synch to any number of line rates automatically. The input to these devices must be baseband RGB video, meaning the decoded picture is essentially the same as the one that was encoded (baseband). But that leaves these projectors immune to any changes in decoders, as long as the decoder used is delivering baseband out.</p>

<p>Most of the projectors on the market today use a three CRT tube configuration and deliver up to 750 lumens in prosumer models, and 1500 lumens in the professional models. In some cases, where light output is an issue, projectors can be stacked one upon the other. They converge automatically, or with minimal operator requirements, and can give the clarity of high quality film and the brightness of a well maintained theater. Oh, yes, and that smashing audio. Gee, wish I knew more about audio.</p>

<p>In addition to the types mentioned above, there is serious work going on with solid state projectors using liquid crystals as "light valves". Some of these projectors work with light transmitting through open crystals, such as the classy Sharp 1.2 million pixel version. Others use a refractive scheme, where high-powered metal-halide light is reflected out the lens to the screen as determined by the position of an electronically addressed liquid crystal pixel. Promises of having good commercial 3500 lumen projectors of this variety are very high. Hughes (that's General Motors, friends) has spent $200 million getting us there. A scaled down home version from their partner, JVC, is being developed as we write. These are exciting developments, indeed, and the future of these projectors offers a dramatic contrast to the fuzzy low contrast ratio projectors found in too many homes today.</p>

<p>While not yet available in this country, if you venture to Japan you can buy an HDTV set with a choice of 28", 32", or 36" CRT models from Panasonic, Sharp, Sony, Toshiba, Hitachi, Mitsubishi, JVC, or Sanyo. Scaling up with rear projectors you can choose from a 52, 60, or 72 inch version from Panasonic, Hitachi, Mitsubishi, JVC, and Sony. Willing to pay the price--you can go up to 200 inch front screen projectors with Mitsubishi and Sony leading the way. More modest front projectors are available from Sony, Mitsubishi, Sharp, Ikegami, and a few others. Pioneer, Panasonic, and Sony will soon have a laserdisc on the market with a MUSE signal. If you have the MUSE decoder in your system it will decode the picture to baseband to run through your projector. If you need also to use the projectors--front and rear--for your computer images, the ports to plug it in are typically available. Many of the manufacturers will be selling MUSE/VHS compatible VCRs in the near future as well. Again, to benefit from the HDTV signal you will need the full MUSE decoder. Only those wanting HDTV before the product launches in this country, however, should consider such purchases since the US sets will come with digital decoders as described briefly above. MUSE will not decode this kind of signal anymore than an NTSC set can decode the European PAL signals.</p>

<p>Does all this mean HDTV is so expensive as to be out of range? For some it will. That is always the case with highly engineered quality items. HDTV is unavoidably in the high-end class, at least in its beginnings. Color television was also. The introductory prices for US consumers will be slightly less than was corresponding for color. The difference is that with modern manufacturing techniques and components these sets will perform out of the box with absolutely knock-out pictures and deliver blow-away audio. Early color was primitive in comparison--components drifted this way and that giving NTSC the nickname of Never Twice the Same Color. Unless severe quality impairing cost-cutting measures are taken at the beginning, HDTV will gain a sterling reputation upon its start-up. If you are an early adopter, be prepared to spend what you would on a large screen set plus 20 to 50%.</p>

<p>In Japan, the only place actually selling HDTV sets, prices range from $2,800 to $10,000 for CRT models, depending upon features and size. Projection units range all over the map from $7,500 to $65,000. Once economies of scale kicks in, those prices are going to drop to that level just marginally more than today's color. The probability of low, low cost HDTV is little since quality is the only thing HDTV has to sell and that will always come with some price. Also HDTV suggests larger screens will be sought. But the VCR is one amazing device for the price, and you will see "acceptable" price points for HDTV as well. You can still buy today a black and white standard set, but few do. We all pay a higher price because we like color, and we will again because we will certainly like HDTV.</p>

<h2>For Everyone?</h2>
Is it for everyone, then? Allow me an opinion. I have never thought any one thing was for everyone. That is why I put quotes around the word "acceptable" in the paragraph above. There are various grades of everything--even ideas, and people buy what they can afford, what they internally feel they deserve or relate to, and what is available. HDTV is a step up from today's standards. Those who are on an upgrade path to higher standards of living will definitely be the ones who are going to have HDTV. It will, for a time, be a status symbol as it was with early black and white. Whether it will be a sign of "yuppiness", or just the payoff for a well run life no one can tell. But, it is certain that some people will not feel right about having an HDTV set when there is still world hunger. Ted Turner says he is in that camp. More likely there will be those who cannot fit it in to their budget. Some won't be able to fit it into their house. But, having spent considerable time in Mexico in the 70s I can report that the poorest of families had their 25 inch color sets blazing day and night. When a family wants something for any good reason, the means to having seem materialize (unless you suffer the rations of war, or other scourges). Having said that, it would be insensitive to not add some concern for the perpetuation of what is today a universal access service. In my dog days I certainly appreciated the ability to buy used for $25 a working TV so I could have my window on the world. The concern is that if HDTV does create a class of users who pay all the bills, their interests will be served at the expense of the interest of the little guy. It is a legitimate concern, but the overriding fact is that with the advent of different quality standards, television will never again be an egalitarian device where one standard fits all. To some degree cable has disenfranchised the lower of the low income people, but they can still get their local over-the-air stations. If these traditional stations go dark in the service of a higher income group (for HDTV set owners), one will have to question any governmental regulatory policy that permits or encourages that.

<h2>Europe</h2>
Europe reacted with industrial strength horror when Japan first demonstrated a means for transmitting HDTV in 1984 (MUSE). They realized Japan was serious about dominating the world in consumer electronics and communications technology, and Europe was behind, unable to replicate immediately what Japan was demonstrating. They puffed up as a region, focused to one entity by the EC, and said, "By golly we can and we will meet this competitive threat. We will start by rejecting the standards proposed by Japan (and backed by the US) and go our own way." If Europe did go its own way, Japan would have to re-develop their HDTV, which meant time, which meant Europe could catch-up. Europe was launching their DBS industry at that same time and hooked their HDTV future to DBS. They also hooked it to what was then a new analog technology that was to be used on the satellites before all this HDTV talk got serious. They modified this analog technology, called MAC (for Multiple Analog Component) to expand itself for HDTV duty. They called that HD-MAC. But when the US went all-digital the European press trashed MAC saying it was outmoded before it could get going. Any hopes that Europe had, even with the full backing of the EC, to employ an analog HDTV system were politically dashed. The cost of scuttling it was in the billions of dollars, though much of the work in tubes and projectors and items unrelated to decoding are preserved for the next system on the drawing boards. Europe had done some of the more formative work in digital, and much of the MPEG 2 ideas are from solid European research. The sting from dropping HD-MAC in favor of digital HDTV has been salved and much of the bills paid for by European taxpayers anyway.

<h2>Rest of the World</h2>
Korea has a similar ministerial structure as Japan. They have their Minister of Industrial Trade and Industry as does Japan, and a Minister of Post and Telecommunications. Through these ministries comes both leadership and money to develop technologies that sustain Korea's economic objectives. HDTV has become high on the list and every Korean manufacturer is involved in its development in association with universities and research centers. Korea announced recently their plans to broadcast HDTV from satellite, as has Taiwan and China. Taiwan has organized a coalition of companies and research centers to develop receivers, mostly projection types. Many think Taiwan will make an all-out effort to create brand name awareness and exploit this opportunity to sell new products for a new era.

<p>China is a very interesting nation to watch. Not only do they have all the customers in the world, but what standard that nation chooses will have enormous impact upon the other regions of the world. The main question is whether they will retain a 50Hz system, or a 60Hz one? China currently uses the PAL system, operating at 50Hz (where the image is update carrying motion 50 times per second, interlaced). This was once required to synch with power-lines--50 cycles per second in China. But that requirement was done away with years ago technically and far less flicker occurs at 60 Hz. But, it is a political question. If they move to 60 Hz, the US and Japan are advantaged. If they retain 50 Hz, Europe is advantaged (also 50 Hz region). If China moves to 60 Hz, Europe could rationalize a move also to 60 Hz. Now is the time to do that, if ever, since both China and Europe are hard at work in designing, and in the case of Europe re-designing, their digital HDTV systems. Digital has no specific requirement for frame rates and, in fact, the Grand Alliance system operates at 24 fps (frames per second), 25 fps, 50 fps, 60 fps. It is the camera standard that is the big problem and if that production standard could be just one, the cost of productions would drop and the world would sooner have things like HDTV camcorders for the consumer. Those CCDs are expensive to design and make, and the more of them that are made the same way, the better off you are in terms of price.</p>

<h2>Imitation is Not Always Flattery</h2>
This article is about HDTV. But what I am about to discuss in a few paragraphs is definitely not HDTV. Initial cost of HDTV worried many manufacturers. The fact that no one has shown themselves eager to pioneer the all-important broadcast signals gives them the jitters (where is your faith manufacturers?). Tube plants for the wider 16:9 sets had to be built for making demonstration sets and for filling manufacturing pipelines in anticipation of the Japanese plans to start experimental eight hour per day satellite broadcasting in 1991. But building plants was very expensive. They needed guaranteed customers on a quota basis. They encouraged lower resolution tubes at the 16:9 aspect ratio as an alternative to the more costly HDTV tubes. Manufacturers decided that if HDTV couldn't get off the ground, or as an interim step, they could produce widescreen NTSC sets. They could do everything to squeeze the last drop from NTSC quality and maybe that would be good enough for the public. HDTV would be let to live or die on its own, but a major push to widescreen NTSC would be undertaken. It was, and you can buy widescreen NTSC resolution sets on the market in the US, Japan and Europe right now.

<p>But buyer beware. You will not want to sit at three times the height of the picture anymore than you do now with your NTSC set. If you don't sit at three times the height of the picture the widescreen does nearly nothing for you anymore than a letter box presentation does now on NTSC. You will not, at a viewing distance of 8 times the height of the picture, have the 30 degree field of vision, nor that involvement that HDTV was designed to achieve for you. It is like buying a stereo with cheap speakers and it was done not to satisfy demand, but to pay for expensive tube plants until HDTV arrives to do that. Widescreen NTSC is not HDTV and it is not going to be HDTV-ready because the tube cannot deliver the resolution that will urge you closer to the set. Cheap speakers don't get any better with better signal inputs. If you don't mind the artifacts of NTSC you can spend $5,000 on such a set, but I personally don't know why. You had just as well buy a large screen standard set at less money per square inch, and if you want letterbox format, get it from laserdisc. At least you won't scratch your head trying to figure out what to do with the 90% of 4:3 programing you will be using the set for. Do you expand it and cut off top and bottom? Do you expand it horizontally and make everyone look fat and distorted? Do you use it as 4:3 set and watch little distracting pictures being displayed on a side? Do you forget it and wait until true HDTV is on the market? I opt for the latter.</p>

<h2>What Are The Obstacles to The Success of HDTV?</h2>
The answer to that is both simple and complex. The simple to understand is that the US has the largest installed base of NTSC standard television sets in the world. The consumers watch more television per day than anyplace in the world. That means there is a very heavy dependency upon the old systems by many differing groups ranging from the consumer to advertisers to the broadcast and transmitting groups (cable) in the form that they now exist. That is the NTSC infrastructure. Now 500 or 1000, or an infinite number of channels are being proposed, still in the NTSC format. This is adding more weight to the infrastructure and making "change" all the more problematic.

<p>This is overcome only by the power of the attractiveness of the product to the end users. Some form of "pull" must be felt. Consumers demanding HDTV, however, when no signals are being offered makes little sense. Some brave souls must begin delivering at a substantial investment those HDTV signals--enough so to encourage the consumers to buy. David Sarnoff understood that and ordered NBC to move to color as quickly as feasible. Still, that did not do enough for the industry and the color line at RCA was shut down with Sarnoff himself weeping in a board meeting, saying the pain of continuing with such heavy losses was unbearable. But miracles do happen, and because of high profile color programs and a lowering of prices the color boom started and all was well again.</p>

<p>The question of whether the current crop of broadcast executives in the networks or major independents have the raw guts to bet their futures on HDTV is unclear. But they may be forced. The FCC, if it follows the plan set up by the last administration, will force broadcasters to use the new spectrum for transmitting HDTV signals. If they don't after a certain time, they will lose the use of the new spectrum and it will be re-assigned to someone else. And, by the way, that poor beleaguered broadcaster will have to give back to the FCC the old NTSC spectrum since is being retired from television assignments. Broadcasters do not uniformly like this kind of deal, though oddly enough, they understand that forcing the situation could mean the cheapest transition. All elements would presumably go in a full court press promotional assault on the public. HDTV IS HERE AND YOU WILL BUY IT! or lose in time your right to receive TV signals! Well, forcing is always a tough call.</p>

<p>Those saying "forget it, I don't need it," could hop on a non-FCC spectrum regulated cable or DBS NTSC system and blow off terrestrial broadcasting forever. Terrestrial guys don't forget such horrible thoughts and when they discovered that their Grand Alliance system could not only compress one HDTV picture into 6 MHz, but also four or even five NTSC quality pictures into a single 6MHz channel, they realized that a multiple NTSC service might be more advantageous over a single expensive HDTV service. "Let's multiplex and give the people choice rather than quality!" To say they would like to totally abandon HDTV is a slight exaggeration, but only slight.</p>

<p>This question of where the first signals will come from will be answered only when enough courage is mustered up by those willing to step into the painful experience of pioneering. It can be horrifying, as David Sarnoff unexpectedly found, but if successful could offer the ultimate reward. It could mean a Ted Turner-style rise to influence and prosperity. There are always great egos willing to take large chances in order that their goodness be felt and recognized. Look for this type of personality to deliver your first HDTV signals and to be, perhaps, the next William Paley (founder of CBS).</p>

<p>Of course, cable has a wideband outlet. But it is looking more and more as if the industry is heading to a pay-by-the-bit system. HDTV is five times the bits of a compressed NTSC signal. Will an audience be willing to pay that difference? Or, will business dictate to the cable operator to put out more instead of better? The DBS game was made possible by digital compression, which made finally one bird competitive with cable in terms of the number of programs offered. DBS has benefited, as have all the digital compression users, by the fact that HDTV drove the industry with a carrot on a stick to complete the digital effort. But it is the issue of "choice" of lower standard resolution television that has taken center stage and left HDTV--the quality entrant--at the door looking for someone with whom to dance.</p>

<p>This article was written in 1994 by Dale Cripps</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>March 24, 2010  8:34 AM</b>
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
			<?=getComments(2931)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 2931)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2010/03/from-the-archives-the-next-generation-of-television.php" type="text/javascript" charset="utf-8"></script>
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