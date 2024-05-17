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
		AND e.entry_id = 73";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 73 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 73 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 73";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_the_route_to_world_communications_dr_joseph_flaherty_1998.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (5) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 73";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998" />
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
	<title>HDTV Magazine - DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998</title>
	<meta name="keywords" content="high definition, full hdtv, production exchange, wide screen, definition television, hdtv, television, digital, standard, definition, itu, world, high, broadcasting, line, new, today, should, technical, quality, programs, production, our, full, format" />
	<meta name="description" content="Today, we are passing through momentous times in the television industry - the revolutionary transition from analog to digital techniques and HDTV throughout the World. From the camera in the studio to the home display, television is being reinvented. This change is not merely an improvement; it is truly a reinvention of television technology.
" />
	<meta name="title" content="DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_the_route_to_world_communications_dr_joseph_flaherty_1998.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Today, we are passing through momentous times in the television industry - the revolutionary transition from analog to digital techniques and HDTV throughout the World. From the camera in the studio to the home display, television is being reinvented. This change is not merely an improvement; it is truly a reinvention of television technology.
" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=73', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_the_route_to_world_communications_dr_joseph_flaherty_1998.php">DTV/HDTV Standards - The Route To World Communications - Dr. Joseph Flaherty - 1998</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 12, 2005</b>
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
				<p>We bring you another in a series of addresses given by Dr. Joseph Flaherty of CBS. He is often referred to as the father of HDTV, at least in the U.S.A. He proved to be a tough competitor in the "standards wars" which raged throughout a very long standard setting process (9 years). _Dale Cripps<br />
  <br />
<strong><em>Presented in Moscow, Russia, November 4, 1998</em></strong></p>

<p>It is a great honor to be invited to address this HAT Symposium in Moscow. Sadly, I am unable to be here in person, because I am at CBS putting our first four digital HDTV stations in New York, Philadelphia, Los Angeles, and San Francisco on-the-air this week. I am happy, however, that my good friend, Henry Yushkiavitshus, is here and able to present my paper.</p>

<p>Today, we are passing through momentous times in the television industry - the revolutionary transition from analog to digital techniques and HDTV throughout the World. From the camera in the studio to the home display, television is being reinvented. This change is not merely an improvement; it is truly a reinvention of television technology.</p>

<p>The World's scientists and engineers built this new digital TV medium on the foundations of radio, television, and color TV. It was these same scientists and engineers who made today's television the World's most important communications medium. Now they have leapt beyond analog television and created 21" century television -- digital TV and HDTV.</p>

<p>As digital techniques reinvent television, so will they also reinvent the business of broadcasting. In the first decade of the new century, digital TV, and especially HDTV, will bring an entirely new viewing experience into the home, and analog television will be doomed worldwide. Digital television and HDTV will provide a diversity of services and a technical quality as different from today's television as was the introduction of color from the early mechanical television experiments of Baird in England and the work of Popov and Eisenstein in Russia.</p>

<p>Popov, who demonstrated wireless transmission in 1895 and used the technology in a practical form in the Russian navy in 1900, was a man larger than the times in which he lived. Few who have made such outstanding contributions to science and technology, could match the culture and spirit of Popov who refused to take out patents on his wireless invention, contending that the discovery should benefit mankind the world over.</p>

<p>America, too, is indebted to other Russians and Russian descendants who made their home in America, and gave us the benefit of their extraordinary abilities. Most notably was David Sarnoff the President of RCA who introduced 2oth century television and color TV to America, and Vladimir Zworykin, the inventor of the iconoscope and the developer of the kinescope, who, thus, enabled the development of modern electronic television.on June 15, 1936, David Sarnoff was about to open the first experimental television transmitter atop the Empire State building in New York. At that time he wrote:</p>

<p>"Of the future industries now visible on the horizon, television has gripped the public imagination most firmly. To bring television to the perfection needed for public service our work proceeds under high pressure at great cost.Such experiments call for.imagination of the highest order and for the courage to follow where that imagination leads. It is in this spirit that our laboratories and our scientists are diligently and devotedly engaged in a task of the highest service to humanity."</p>

<p><br />
From such work television was born, and today, that same genius and dedication in the service of mankind gave birth to digital HDTV. Yet there is much work to be done. You and I have a major role to play in advancing this digital HDTV technology and bringing it into widespread use,</p>

<p>Much can be learned of our tasks by a backward look. It was the philosopher Santayana who observed that:<br />
This statement on the adoption of a unique standard for production and exchange of high definition programs was reaffirmed at the 1998 meeting of the WBU-TC in Krakow, Poland on April 26, 1998. </p>

<p>"Those who cannot remember the past are condemned to repeat it."</p>

<p><br />
Our television past is not unblemished! Not unblemished, but not without some reason.</p>

<p>At television's birth, it was the era of vacuum tubes and of narrow bandwidth equipment. There were no VTRS, and no electronic way to record television signals. There were no geostationery satellites and no way to flash television signals around the World. In short, there was no international television.</p>

<p>Thus, television systems evolved as national or regional services, each with different standards -- standards that were frequently incompatible with one another. And so it was when color television emerged. The chaos continued despite the development of video recorders, international satellites, and early digital television equipment.</p>

<p>This Tower of Babel would have been made worse and ever more confusing were it not for another Russian engineer and the International Telecommunications Union, or ITU. It was through the monumental efforts of your Professor Mark Krivocheev, who, during the most difficult political times, guided the technical community of the ITU through hosts of technical issues vital to world communications. would single out but two of these, the ITU Recommendation 601-4 which led to practical digital component video tape standards worldwide and the ITU Recommendation ITU BT-709-2 that created a unique HDTV Common Image Format (CIF) for the production and exchange of HDTV programs worldwide.</p>

<p>This important International Telecommunications Union (ITU) Recommendation states in part:</p>

<p><br />
"Considering:</p>

<p><br />
that parameter values for HDTV production standards should have maximum commonality;</p>

<p><br />
that an active image format of 1920 pixels by 1080 lines provides square pixel sampling, with attendant advantages for interoperab4.1ity between various applications including digital television and computer imagery;</p>

<p><br />
Recommends:</p>

<p><br />
that for new implementations, particularly where interoperability with other applications is important, systems described in Part II (of document ITU-BT-709) are preferred."</p>

<p><br />
Part II describes the CIF system as:</p>

<p><br />
1080/60/2:1; 1080/60/1:1 and 1080/50/1:1; 1090/50/2:1 with 1920 samples per active line at an aspect ratio of 16:9.</p>

<p><br />
Thus, the ITU and its Radiocommunications Study Group 11, under the direction of Professor Krivocheev, has prepared the World for its first unique television standard for HDTV program production and program exchange.</p>

<p>The Technical Committee of the World Broadcasting Unions (WBU-TC), is composed of a membership from all the World's eight Broadcasting Unions, the:</p>

<p><br />
- Asia Pacific Broadcasting Union (ABU)<br />
- Arab States Broadcasting Union (ASBU)<br />
- Caribbean Broadcasting Union (CBU)<br />
- European Broadcasting Union (EBU)<br />
- International Association of Broadcasters (IAB)<br />
- North American National Broadcasters Association (NANBA)<br />
- Organisacion de Television Ibero-Americana (OTI)<br />
- Union des Radiodiffusions et Televisions Nationales d'Afrique (URTNA)</p>

<p><br />
This WBU-TC endorsed the ITU BT-709 Recommendation in 1997. Its statement declared:</p>

<p><br />
'The World Broadcast Unions Technical Committee strongly supports the adoption of a unique standard for program production and exchange of high definition television. This will lead to easier and better exchange of HDTV programs and lower equipment costs. It will accelerate the move to hiah definition throughout the World."</p>

<p><br />
"The WBU-TC recommends that the unique standard should be the socalled HD-CIF standard which has a 1080 line by 1920 sample by 50HZ/6OHz scanning system. This standard should be used for HDTV production equipment. Studio equipment manufacturers are being encouraged to set in motion the means to provide equipment to this standard."</p>

<p><br />
"The WBU-TC warmly recognizes the achievement of the ITU Study Group 11 in including the HO-CIF standard 4.-i its Recommendation BT-709-2. This recommendation should form the universally accepted parameter set for high definition television production."</p>

<p><br />
This statement on the adoption of a unique standard for production and exchange of high definition programs was reaffirmed at the 1998 meeting of the WBU-TC in Krakow, Poland on April 26, 1998.</p>

<p>The technical and political envirorment has never been better to ;chieve this worldwide digital HDTV standard, and it will never again be so favorable. Only you, 1, and broadcasters like us, around the World can make this standard a reality, and we simply must do so</p>

<p>instant worldwide communications by radio, telephone, fax, and the Internet are a reality. In this new Information Age, where more people watch TV than are literate, television cannot wallow in its incompatible past. 21st century television must be a worldwide phenomenon, available to all mankind without technical constraints!</p>

<p>If you remember but one thought from this lecture, this .is it. Adopt HDTV and adopt the ITU BT-709-2 HF-CIF format of 1080 lines, interlace and progressively scanned, by 1920 pixels-per-line, at a 16:9 aspect ratio in both the 50 and 60 Hertz frame rates! Russia, whose work in the ITU contributed so greatly to this standard, needs to now help lead the World into this unique HDTV format for the production and exchange of HDTV programs!</p>

<p>Lest anyone think that HDTV may be too good to serve the need, another look to the past is informative.</p>

<p>In 1974 the International Telecommunications Union, through its CCIR, began the study of HDTV by adopting a high definition Study Question stating:</p>

<p><br />
"Considering:: That high definition television systems will require a resolution which is approximately equivalent to that of 35mm film and corresponds to at least twice the horizontal and twice the vertical resolution of present television systems:"</p>

<p><br />
"The CCIR UNANIMOUSLY DECIDES that this question should be studied: What standards should be recommended for high definition television systems intended for broadcasting to the general public?'</p>

<p><br />
By 1977, the SMPTE Study Group on High Definition Television was formed, and in 1980 the SMPTE Journal published that group's HDTV report.</p>

<p>The report stated:</p>

<p><br />
"The appropriate standard of comparison (for HDTV) is the current and prospective optimum performance of the 35mm release print as projected on a wide screen.'</p>

<p><br />
The SMPTE HDTV Study Group concluded:</p>

<p><br />
"The appropriate line rate for HDTV is approximately 1100 lines-per-frame, and the frame rate should be 60 fields per second, interlaced 2-to-l...".</p>

<p><br />
I Indeed, rather than being better than necessary, high definition was to finally put television resolution on a par with cinema quality.  <br />
ndeed, rather than being better than necessary, high definition was to finally put television resolution on a par with cinema quality. Today, this has been achieved! HDTV equals the quality of 35mm film. Thus, our HDTV is not too good, it's simply catching up -- catching up to a quality most widely accepted by the creative community and by the World's viewers. Through full HDTV, and only through full HDTV, television will finally achieve its technical maturity. </p>

<p>if HDTV is not too good, then is it good enough?</p>

<p>Today HDTV is better than the display devices. These displays are the 'Limiting quality factor. While improvements are being made by the month, as of today, no display has achieved the full quality potential of the HDTV system. Recently, Fujitsu announced a new 42-inch, 16:9 wide screen, flat panel display with 1024 pixels per line, approaching full HDTV quality. This development in displays is as it should be! The HDTV system needs to provide the headroom for improvement and the challenge for further near term development. No new standard should ever be fully encompassed by the existing state-of-the-art, nor should it be so futuristic as not to have its potential achievable in a foreseeable time. The ITU and WBU-TC HDTV standard is beyond the present quality of displays, but not beyond the scope of their rapid development.</p>

<p>In considering the importance of HDTV broadcasting, it is vital to understand that wide screen high definition is not just pretty pictures for today's small screen TV sets. Rather, it is a wholly new digital platform that will support the larger and vastly improved displays now in commercial development.</p>

<p>On November 21st, 1985, with apologies to Arthur C. Clarke for plagiarizing his title, I delivered a lecture entitled, -2001, A Broadcasting Odyssey". In that lecture 1 said:</p>

<p><br />
"As we evaluate tomorrow's TV and HDTV and plan for its implementation, we must bear in mind that today's standard of service enjoyed by the viewer will not be his level of expectation tomorrow. Good enough is no longer perfect, and may become wholly unsatisfactory."</p>

<p><br />
"Quality is a moving target, both in programs and in technology. Our judgements as to the future must not be based on today's performance, nor on minor improvements thereto."</p>

<p><br />
Today, twenty five years after NHK began its pioneering work, high definition as defined by the ITU and the WBU-TC is, and will be, a system employing at least 1000 active lines, interlace or progressively scanned. Lesser formats may be improvements over present TV, but are not high definition!</p>

<p>The digital era has begun, and every broadcaster will feel the impact of this digital revolution. Digital technology will radically change television's means of communication, its quality, its flexibility, the conduct of the business, the scope and effectiveness of the service, and every aspect of the medium. While some may still consider this historic invention unfortunate, its application is, at the same time, inevitable.</p>

<p>CBS fully supports the ITU and WBU-TC digital standard, and plans to use the 1080 line, 1920 pixel, wide screen 16:9 aspect ratio, 60 Hz format, interlaced scanned for electronically produced programs and progressively scanned for 24 and 30 frame film programs. This format is in full compliance with the ITU BT-709-2 Recommendation and the WBUTC unique HDTV production standard.</p>

<p>It is likely that HDTV will become the medium of choice by producers, programmers, the distribution media, and by the viewing public. Major cable and DBS programmers have declared their intent to provide HDTV program services, and this will provide another incentive for the public to invest in digital TV and HDTV receivers. Additionally, regardless of the transmission format used, programming to be saleable in the international market will need to be produced in full HDTV in accordance with the ITU Recommendation BT-709-2.</p>

<p>In the United States, following nine years of study, debate, design, construction, testing and rulemaking, the FCC digital TV and HDTV transmission standards and service rules were set on April 3, 1997. The ATSC - the Advanced Television Systems Committee - standard supports a hierarchy of open, non-proprietary, scanning formats with full RDTV at the highest level, and includes Standard definition Digital TV, or SDTV, multi-program compressed SDTV, a computer VGA format, and a large digital data transmission capacity.</p>

<p>All the digital TV and HDTV receivers to be built in America will decode all of the ATSC transmission formats including full HDTV. Thus, American broadcasters will be able to use any, or all, of the ATSC scanning formats. Their digital bouquet will extend from SDTV to full HDTV.</p>

<p>As it applies to the digital decisions that you have to make, I would suggest that those who believe, or want to believe, that the viewers will never want wide screen HDTV when it is offered, are taking a 'bet-the-business" gamble. More of the same standard definition 525 or 625 line digital transmissions will not capture the market, will not sell digital receivers in quantity, and will give way to HDTV worldwide. Standard definition and multiplexed standard definition services alone will not be sufficient to compete effectively for tomorrow's viewers. European broadcasters, like their American counterparts, must have the ability to deliver full HDTV to their viewers, and those viewers must be able to receive HDTV programs as readily as they will receive digital 625 line programs.</p>

<p>Both the American and European technical communities, under the leadership of the ITU, the ATSC, and the DVB, deserve the highest praise for achieving a single worldwide common image format of 1080 lines and 1920 pixels-per-line with an aspect ratio of 16:9, and for devising two, and only two, digital TV transmission systems able to deliver standard TV and HDTV 4 n the present restricted bandwidth terrestrial TV channels.</p>

<p>While it was a torturous and time consuming process, plagued with politics and inept bureaucracies with their visions of years of evolution, sneaking up on digital TV and HDTV, our technical communities prevailed. Digital TV and HDTV are realities today, and tne World is the better for it.</p>

<p>Europe survived the MAC era; abandoned the HD-MAC proposal; launched the digital DVB project; came to the 1080 line, 1920 pixel-per-line, 16:9 aspect ratio, HD-CIF standard for program production and exchange; and Europe devised the DVB terrestrial digital standard to support HDTV as well as standard 625 line television.</p>

<p>I would suggest that those who believe, or want to believe, that the viewers will never want wide screen HDTV when it is offered, are taking a 'bet-the-business" gamble. <br />
At the present time, however, the Western European consumer equipment industry is still ignoring HDTV in its digital receiver plans. This, in my opinion, is a major mistake. European broadcasters, with the ability to broadcast HDTV through the DVB or ATSC systems, will be prevented from doing so by the inability of European digital receivers to decode the HDTV signal. Yet in America, it is the same European consumer electronics companies, Thomson and Philips, who are building all-mode digital receivers, able to decode and display all formats, including HDTV.</p>

<p>European digital receivers sold without the ability to decode and display HDTV, will either go "black" or will require new, and expensive converters, when, and not if, HDTV broadcasting becomes a reality in Europe. Programs of international interest, especially important sporting events, such as the Olympics and the World Cup, are already produced in HDTV, and they have produced a most enthusiastic reaction from viewers.</p>

<p>The DVB and ATSC transmission systems accommodate HDTV. Terrestrial broadcasters in Russia must not be denied the ability to broadcast HDTV to their viewers just because the receivers fail to include HDTV decoders.</p>

<p>In short, receivers for the new digital HDTV program service cannot be planned and implemented solely by consumer equipment manufacturers who believe that ',-here will be no consumer demand for high definition.</p>

<p>Before adopting any DTV transmission system-DVB or ATSC, Russia and its broadcasters need to insist that receivers with HDTV decoders be readily available at practical prices from the outset.</p>

<p>Meanwhile, the digital TV and HDTV service is rolling out in America, with stations in the top ten television markets beginning their digital TV and HDTV broadcast service this week. Other markets will begin DTV services in May, 1999. By 2003, the FCC requires that all 1650 U.S. television stations shall have made the transition to digital broadcasting. Further, it is planned that all analog NTSC broadcasting will cease in 2006.</p>

<p>As I said at the beginning of this lecture, the digital revolution is a momentous event in the history of our industry. As the English writer H.G. Wells put it:</p>

<p><br />
"The past is but the beginning of a beginning, and all that is and has been, is but the twilight of the dawn."</p>

<p><br />
But change is the nature of life and often irresistible. As Victor Hugo observed:</p>

<p><br />
"An invasion of armies can be resisted; but not an idea whose time has come."</p>

<p><br />
It's time that, together, we lead the World into digital TV and HDTV.</p>

<p>_____________________________________________<br />
About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em><br />
 <br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 12, 2005 10:36 PM</b>
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
			<?=getComments(73)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 73)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/dtvhdtv_standards_the_route_to_world_communications_dr_joseph_flaherty_1998.php" type="text/javascript" charset="utf-8"></script>
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