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
		AND e.entry_id = 4";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/05/broadcastings-challenge-or-is-it-too-late.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Broadcasting\'s Challenge, Or Is It Too Late?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Broadcasting\'s Challenge, Or Is It Too Late?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Broadcasting\'s Challenge, Or Is It Too Late?" />
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
	<title>HDTV Magazine - Broadcasting's Challenge, Or Is It Too Late?</title>
	<meta name="keywords" content="air broadcasting, cable satellite, broadcast industry, first amendment, market share, broadcasters, broadcasting, consumers, broadcast, hdtv, air, digital, even, industry, cable, share, market, free, satellite, new, public, get, signal, been, years" />
	<meta name="description" content="To continue with our series of high wire observations I bring you this speech from CEA president Gary Shapiro given April 18th to members of the broadcast community gathered in Las Vegas for the National Association of broadcasters convention. These remarks are brought to you unedited in order that the strong influences acting on the HDTV movement become clearly seen by the general public. All of the actions being asked for, however, are more in the hands of the consumers than of those agencies and institutions being called to action. The HDTV movement, and who survives and thrives, is entirely in the hands of the public and those closest to the public--the retailers and signal providers. Without the public doing their part in this transition--acting quite like partners with the manufacturers and signal providers--the movement will take on a surreal sense and spin out of control as pushing then replaces salesmanship. There is simply too much money at stake now to have it otherwise. _Dale Cripps


Gary Shapiro
President and CEO Consumer Electronics Association
Keynote Before GSB
NAB 2005
April 18, 2005
Las Vegas Country Club

Good morning. I'm honored to be here this morning to share my views and the consumer electronics industry perspective on over-the-air broadcasting and the nation's shift to digital.
" />
	<meta name="title" content="Broadcasting's Challenge, Or Is It Too Late?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Broadcasting's Challenge, Or Is It Too Late?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/05/broadcastings-challenge-or-is-it-too-late.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="To continue with our series of high wire observations I bring you this speech from CEA president Gary Shapiro given April 18th to members of the broadcast community gathered in Las Vegas for the National Association of broadcasters convention. These remarks are brought to you unedited in order that the strong influences acting on the HDTV movement become clearly seen by the general public. All of the actions being asked for, however, are more in the hands of the consumers than of those agencies and institutions being called to action. The HDTV movement, and who survives and thrives, is entirely in the hands of the public and those closest to the public--the retailers and signal providers. Without the public doing their part in this transition--acting quite like partners with the manufacturers and signal providers--the movement will take on a surreal sense and spin out of control as pushing then replaces salesmanship. There is simply too much money at stake now to have it otherwise. _Dale Cripps


Gary Shapiro
President and CEO Consumer Electronics Association
Keynote Before GSB
NAB 2005
April 18, 2005
Las Vegas Country Club

Good morning. I'm honored to be here this morning to share my views and the consumer electronics industry perspective on over-the-air broadcasting and the nation's shift to digital.
" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/05/broadcastings-challenge-or-is-it-too-late.php">Broadcasting's Challenge, Or Is It Too Late?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 12, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p>To continue with our series of high wire observations I bring you this speech from CEA president Gary Shapiro given April 18th to members of the broadcast community gathered in Las Vegas for the National Association of broadcasters convention. These remarks are brought to you unedited in order that the strong influences acting on the HDTV movement become clearly seen by the general public. All of the actions being asked for, however, are more in the hands of the consumers than of those agencies and institutions being called to action. The HDTV movement, and who survives and thrives, is entirely in the hands of the public and those closest to the public--the retailers and signal providers. Without the public doing their part in this transition--acting quite like partners with the manufacturers and signal providers--the movement will take on a surreal sense and spin out of control as pushing then replaces salesmanship. There is simply too much money at stake now to have it otherwise. _Dale Cripps</p>

<p><br />
Gary Shapiro<br />
President and CEO Consumer Electronics Association<br />
Keynote Before GSB<br />
NAB 2005<br />
April 18, 2005<br />
Las Vegas Country Club</p>

<p>Good morning. I'm honored to be here this morning to share my views and the consumer electronics industry perspective on over-the-air broadcasting and the nation's shift to digital.</p>

<p>I must say from the outset that what I have to say may not be easy for the broadcast industry to hear. But the truth is, I've been saying the same thing for years -- and that underscores the problem. Broadcasters face significant challenges in the digital age -- challenges they have refused to tackle and opportunities they have refused to embrace. But these challenges are not insurmountable. Indeed, those who play these changes to their advantage will succeed in the digital age.</p>

<p>Each year the challenges facing broadcasters grow. I believe this is largely because of the approach broadcasters have taken to these threats. For too long, broadcasters have tried to enhance or even save themselves by pushing for regulations on other industries. Too many competitors and innovations are out there -- all competing for the same eyeballs -- for that approach to be successful in the long run.</p>

<p>Broadcasting today hangs in the balance. How did it get there?</p>

<p>Well, the broadcast monopoly that dominated the 1960s gave way to the challenge from cable in the 1970s. In the 80s we saw eyeballs drawn away to the VCR and in the 90s along came satellite, the DVD and the Internet. This decade brings the challenge of even more, new pipes - wireless broadband, telephone providing video and even power line providing broadband entertainment. The broadcaster sell of massive localized eyeballs is now even threatened by the growing ability of cable to provide location-specific advertising.</p>

<p>Broadcasters are being obliterated as the signal comes into the home from diverse sources.</p>

<p>Back in 1998, CBS's Dr. Joe Flaherty told USA Today that HDTV "is a reinvention of television. Every aspect of it will be changed. It's going to be a new world of broadcasting," he said.</p>

<p>A new world of broadcasting. I certainly agree with Dr. Flaherty that HDTV presented an opportunity for the broadcast community to put up a good fight for consumers -- attention in the digital age. Indeed, the move to digital television was designed in part to help support over-the-air broadcasts. But broadcasters haven't taken advantage of this unprecedented opportunity to revive their business. They haven't created new business models to take advantage of digital technologies.</p>

<p>Rather, the broadcast industry has reacted to new competitive entrants, in more cases than not, with aggressive lobbying, rather than aggressive business strategy. Most recently, they have sought restrictions in various forms on cable, satellite and TV set manufacturers.</p>

<p>Broadcasters make money by selling linear advertising. Yet they are selling it to people who increasingly use DVRs to fast forward through commercials or use the remote control to channel surf at the same time they are online. Broadcasters' eyeball share will continue to go down and down each year.</p>

<p>It has become worse for broadcasters as the regulations they crave will do them in. All the fairness, equal time, children's programming and other regulations make them compete with one hand tied behind their back.</p>

<p>Yet broadcasters have invited further regulation and hastened their own insignificance. They stood on the sidelines as policymakers overreacted to the Janet Jackson incident. Their silence has invited further ambiguous regulation and drenched programmers and talent in uncertainty. Like blinded deer, broadcasters have responded with tepid programming, even pulling "Saving Private Ryan" and driving the king of all media to satellite.</p>

<p>And while the sin of broadcasters has been to seek lazily to expand and preserve their market through regulation, they have abjectly refused to market broadcasting, even in the face of declining market share and aggressive marketing by every one of their growing competitors.</p>

<p>So, instead of promoting over the air broadcasting by urging people to get free TV through antennas, the broadcast community has focused on saddling cable and satellite with carriage requirements. They have pushed tuner requirements on TV set makers and are advocating to delay the digital television transition as long as possible.</p>

<p>This last tactic is particularly troubling. Seeking to block the establishment of a hard analog cut-off date is only going to exaggerate broadcasters -- bleeding and create uncertainty among consumers. Let's set a date and all get behind it.</p>

<p><strong><span style="font-size:130%;">Is it too late for broadcasters? </span></strong></p>

<p>Maybe. The principled position of free over-the-air broadcasting being in the public interest and thus deserving of a special status declines as audience share declines. With less than 15% of American homes relying on over the air broadcasting, policymakers have to ask why they should treat broadcasters differently. More, it's not just cable and satellite who will count in this competitive mix in the future. It's anyone who receives an Internet connection from their power company, telephone company or wirelessly. Moreover, that signal goes not only to their TV set, but to the screen on their phone or their watch or the PDA in their hand. Just what rationale will broadcasters have for their special place when more than 90 percent of Americans will be connected to the Internet or to cable or to satellite? And have no doubt that time will come this decade!</p>

<p>The only hope broadcasters have is to wake up and recognize that they must unite behind a strategy to shift the decline in their market share. Specifically, I suggest broadcasters use hard dollars and their own media to SELL the value of their service to consumers. Every day cable and satellite, wireless, telephone, and soon, other utilities market to consumers while broadcasters remain silent is another day of declining market share.</p>

<p>Here is a quick way to win back share - or at least slow the hemorrhaging:</p>

<p><strong>- Promote free over the air broadcasting. </strong><br />
For several years I have implored the NAB and MSTV to join us in promoting the use of antennas. The pleas have been ignored. CEA eventually went out on our own 4 years ago and built a website for consumers to discover the best type of antenna for their home. We independently funded and launched an online antenna selector website -- <a href="http://antennaweb.org">antennaweb.org </a>-- in order to provide consumers with information about over-the-air DTV reception. This resource has been available and promoted to consumers for years and site hits now range from 150,000 to 200,000 a month. We also have tried to promote over-the-air broadcasting in nearly all of our own HDTV promotional efforts and general messaging.</p>

<p><strong>- Shift to HDTV Quickly.</strong><br />
Broadcasting is becoming the inferior medium - and consumers know it. That perception can be altered by broadcasters if they would only embrace high-definition and actively promote free OTA programming, including unique content such as the Super Bowl. Networks are providing HDTV and local broadcasters should carry it at full power and not keep it a secret! Local broadcasters must promote HDTV -- even on their analog stations. They also should market the surround sound experience -- one of the most compelling aspects of HDTV. 6MhZ is a big pipe -- broadcasters should fill it and then let consumers know about it.</p>

<p>I still keep hearing about new plans and schemes to send data, ad inserts, multicasting, or send a smaller signal to get a wider service area. Broadcasters must ignore all these silly get rich quick schemes and multicasting. They offer no competitive advantage and it's not why Congress gave broadcasters the spectrum to begin with. Americans have spoken and they want HDTV.</p>

<p><strong>- Support a Hard Cut off Date -- </strong><br />
No other medium has the HDTV message and offering that broadcasting does. Over-the-air offers consumers free High Definition Television. FREE! I implore the broadcast industry to enlighten consumers about this offering and educate them about antenna reception. If we have a hard cut-off date, the broadcast story will be all the more compelling!</p>

<p><strong>- Defend the First Amendment --</strong><br />
The first amendment matters, not only as an important principle, but also as broadcasters --primary commercial protection. It also gives Americans a wide range of cutting edge ideas and opinions. I have been disappointed by the industry's reluctance --even refusal --to stand up to defend its First Amendment rights. The industry will see a further migration of talent and creativity to pay services and alternative formats if it does not defend itself.</p>

<p>I hope the broadcast industry does not sit idly by as broadcasting becomes wimpish, milquetoast media. The answer is not to regulate competitors, it is to stand up and fight using broadcast strengths.</p>

<p>I will conclude with the same words I delivered to the broadcast engineers at NAB last year. Unfortunately, they hold just as true today:</p>

<p>Unless broadcasters unite and stand up for those values that made you great --free over-the-air broadcasting and the first amendment; unless broadcasters vocally and visibly embrace new digital technologies; and unless broadcasters emerge from complacency, 11 years from now this room will be very small and America will be a pay service country. It would be sad to see a great industry fade into oblivion or into dependency on government regulation on other media. But that is the path you are choosing. I urge you to remove your shackles, stand up for your rights, embrace your digital future and choose a different path.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 12, 2005 11:43 AM</b>
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
			<?=getComments(4)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 4)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/broadcastings-challenge-or-is-it-too-late.php" type="text/javascript" charset="utf-8"></script>
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