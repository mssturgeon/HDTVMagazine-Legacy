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
		AND e.entry_id = 72";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 72 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 72 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 72";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/a_perspective_on_digital_tv_and_hdtv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 72";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download A Perspective on Digital TV and HDTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="A Perspective on Digital TV and HDTV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="A Perspective on Digital TV and HDTV" />
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
	<title>HDTV Magazine - A Perspective on Digital TV and HDTV</title>
	<meta name="keywords" content="advisory committee, high definition, hdtv system, definition television, wide screen, hdtv, digital, system, television, fcc, systems, service, standard, definition, quality, committee, advisory, high, technical, ntsc, best, transition, channel, time, dtv" />
	<meta name="description" content="With little question the man who wrote this article -- Dr. Joseph Flaherty -- was the most important figure in HDTV in the United States, and some say, the world. In time I will post all of his key addresses..." />
	<meta name="title" content="A Perspective on Digital TV and HDTV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="A Perspective on Digital TV and HDTV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/a_perspective_on_digital_tv_and_hdtv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="With little question the man who wrote this article -- Dr. Joseph Flaherty -- was the most important figure in HDTV in the United States, and some say, the world. In time I will post all of his key addresses..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=72', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/a_perspective_on_digital_tv_and_hdtv.php">A Perspective on Digital TV and HDTV</a></td>
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
				<p>With little question the man who wrote this article -- Dr. Joseph Flaherty -- was the most important figure in HDTV in the United States, and some say, the world. </p>

<p>In time I will post all of his key addresses made over the years mostly to technical assemblies, but not always. This address was made shortly after the ATSC standard was established and the commericialization that he had worked so hard for was clearly coming. _Dale Cripps</p>

<p></p>

<p></p>

<p><strong>A Perspective on Digital TV and HDTV </strong></p>

<p>by <br />
Dr. J. A. Flaherty<br />
Senior Vice President - Technology<br />
CBS Inc. </p>

<p>At an earlier time, on June 15, 1936, when television itself was emerging from its radio foundations, General David Sarnoff, then President of the Radio Corporation of America, presented a lecture to the FCC entitled "The Future of Radio and Public Interest, Convenience and Necessity". In that lecture the General said of the development of television:</p>

<p>"Of the future industries now visible on the horizon. Television has gripped the public imagination most firmly. To bring television to the perfection needed for public service our work proceeds under high pressure at great cost and with encouraging technical results. Such experiments call for ... imagination of the highest order and for the courage to follow where that imagination leads. It is in this spirit that our laboratories and our scientists are diligently and devotedly engaged in a task of the highest service to humanity."</p>

<p>From such work television was born, and today, the same genius and dedication in the service of mankind gave birth to the ATSC DTV and HDTV standard.</p>

<p>Television, this 20th century phenomenon, is so far advanced that today, the world over, more people watch television than are literate. Yet, along the way every significant advance in television quality since John Logic Baird's 28 line mechanical scanning system has been heralded as "High Definition". High Definition has always been the best that could be done -- the full state of the art.</p>

<p>Thus, it was in 1970, 34 years after General Sarnoff's lecture, that modern HDTV began. NHK launched the modern development of High Definition Television, and carried out extensive research and psychophysical testing as groundwork for choosing a scanning format, an aspect ratio, and an entirely new electronic imaging system. From 1970 through 1977 technical papers were published around the World covering the subjective evaluation of picture quality, the response of the human visual system to increased line scanning, the visual effects of interlace scanning, and the chromatic spatial frequency response of the human visual system, and in 1973, NHR described the original wide screen 1125 line HDTV system.</p>

<p>In 1974 the International Telecommunications Union, through its CCIR, adopted an HDTV Study Question stating:</p>

<p>"Considering. That high definition television systems will require a resolution which is approximately equivalent to that of 35mm film and corresponds to at least twice the horizontal and twice the vertical resolution of present television systems: The CCIR UNANIMOUSLY DECIDES that this question should be studied: What standards should be recommended for high definition television systems intended for broadcasting to the general public?"</p>

<p>By 1977, the SMPTE Study Group on High Definition Television was formed, and in 1980 the SMPTE Journal published that group's report on HDTV. The report stated:</p>

<p>"The appropriate standard of comparison (for HDTV) is the current and prospective optimum performance of the 35mm release print as projected on a wide screen."</p>

<p>The SMPTE HDTV Study Group concluded:</p>

<p>"The appropriate line rate for HDTV is approximately 1100 lines-per-frame, and the frame rate should be 30 frames-per-second, interlaced 2-to-1...".</p>

<p>Indead, rather than being better than necessary, HDTV was to finally put television on a par with cinema quality! We're just too good, we're just catching up -- catching up to a quality widely accepted by the creative community and by the American public. Through full HDTV, television will achieve its technical maturity.</p>

<p>The first glimpse of this maturity came in 1981 when CBS and NHK presented the first HDTV demonstration in America at the SMPTE Winter Television Conference in San Francisco. Two weeks later an HDTV demonstration was presented in Washington, D.C. for the FCC and other government entities. These demonstrations were quickly followed by packed demonstrations in New York and Hollywood, and viewers were captivated.</p>

<p>There was no turning back. Television would never again be the same!</p>

<p>On November 2lst, 1985, with apologies to Arthur C. Clarke for plagiarizing his rifle, I delivered a lecture entitled, "2OO1, A Broadcasting Odyssey". In that lecture I said;</p>

<p>"As we evaluate tomorrow's TV and HDTV and plan for its implementation, we must bear in mind that today's 'standard of service' enjoyed by the viewer will not be his 'level of expectation' tomorrow. 'Good enough' is no longer 'perfect', and may become wholly unsatisfactory. Quality is a moving target, both in programs and in technology. Our judgements as by the future must not be based on today's performance, nor on minor improvements thereto."</p>

<p>Television's quality target continued to move, and in 1987 the FCC sought private sector advice and formed the Advisory Committee on Advanced Television Service, or ACATS, under the chairmanship of Richard E. Wiley, and charged it to study the problems of the terrestrial broadcasting of advanced television, to test proposed systems, and to make a recommendation to the FCC by the second quarter of 1993 for selecting a single terrestrial HDTV transmission standard for America.</p>

<p>In the ACATS process ATV system proposals peaked at twenty one, but by l99O they had shrunk to only nine. Only two of these were HDTV simulcast systems, and all were analog designs.</p>

<p>In April of 1990, then FCC Chairman Sikes had announced:</p>

<p>''...the Commission's intent is to select a simulcast high definition television standard that is compatible with the current 6 MHz channelization plan but employing new design principles independent of NTSC technology. We do not envision ... that the Commission would adopt an enhanced definition standard, if at all, prior to reaching a final decision on an HDTV standard."</p>

<p>America had a goal - full quality terrestrial HDTV!</p>

<p>Two years and eight months into the U.S. FCC Advisory Committee Advanced TV process, on June 1, 1990, General Instrument proposed an all digital terrestrial HDTV system, and television was forever changed. The digital era had begun and analog television was doomed worldwide! Television was to make its most fundamental technological change since its invention and its subsequent colorization.</p>

<p>By 1991, only five HDTV systems remained, and, of these, only one was a hybrid analog/digital system - NHK's Narrow MUSE. The other four were all-digital systems, and the digital changeover had extended the Advisory Committee schedule by about six months.</p>

<p>The systems were:</p>

<p>NHK's hybrid analog/digital "Narrow MUSE" system employing frequency split pulse amplitude modulation. <br />
General Instrument's "DigiCipher" system employing digital POT compression algorithms and 16 or 32 state quadrature amplitude modulation or 16/32 QAM. <br />
AT&T and Zenith's "Digital Spectrum Compatible HDTV" or "DSCHDTV " employing progressive scanning, digital DCT compression algorithms, and four level vestigial sideband modulation or 4VSB/2-VSB. <br />
Thomson, Philips, Sarnoff Labs, and NBC's ÒAdvanced Digital HDTVÓ. or "AD-HDTV" employing digital DOT compression algorithms and spectrally shaped quadrature amplitude modulation. <br />
General Instrument and MIT's "Channel Compatible DigiCipher" or "CC-DigiCipher" employing progressive scan, digital DOT compression algorithms, and 16 or 32 state quadrature amplitude modulation or 16/32 QAM. <br />
In September of 1990, in its First Report and Order, the FCC decided:</p>

<p>"We do not find it useful to give further consideration to systems that use additional spectrum to "augment" an existing 6 MHz television channel to provide NTSC compatible service. Consistent with our goal of ensuring excellence in ATV service, we intend to select a simulcast high definition television system. A simulcast system also will be spectrum efficient and facilitate the implementation of advanced television service. Such a system will transmit the increased information of an HDTV signal in the same 6 MHz channel space used in the current television channel plan."</p>

<p>Thus, as of 1990, the FCC and the private sector Advisory Committee had abandoned "enhanced" and "augmentation" systems from consideration; focused further work on incompatible HDTV simulcast systems, and ensured that complete and objective tests would be made on all proponent systems before the approval of any HDTV system.</p>

<p>This was best expressed by FCC Chairman Sikes when he said:</p>

<p>"I understand the concerns of those who believe in an incremental, step-by-step progression toward full HDTV.. ," but "pursuing Extended Definition options would tend to maximize transition costs for both industry and consumers. Stations presumably would need to make a series of sequential investments, as they inched toward full HDTV. At the same time, however, consumers almost certainly would be confused, and would probably resist buying equipment which, in relatively short order, might be rendered obsolete " It The FCC cares enough about broadcasting and the service it provides the public to want the very best - full HDTV - and not some incremental solution to this formidable challenge."</p>

<p>The private sector Advanced Television Test Center (ATTC) laboratory completed the objective laboratory tests and "expert viewer" psychophysical tests of the five systems by August, 1992, and the "nonexpert" psychophysical tests were completed at the Advanced Television Evaluation Lab (ATEL) in Canada in October, 1992. The laboratory test report’s were issued by December, 1992, in preparation for a meeting of the Special Panel of the FCC Advisory Committee in February 1993.</p>

<p>In parallel with this work, the FCC issued its second Notice of Proposed Rule Making, or NPRM. In it the FCC proposed how the HDTV service would be defined, and the time schedule for its implementation, and for the replacement of the NTSC service.</p>

<p>This second FCC NPRM stated:</p>

<p>"We envision HDTV ... will eventually replace existing NTSC. In order to make a smooth transition to this technology, we earlier decided to permit delivery of advanced television on a separate 6 MHz (simulcast) channel. In order to continue to promote spectrum efficiency, we intend to require broadcasters to "convert" entirely to HDTV -- i.e., to surrender one 6 MHz frequency and broadcast only in HDTV once HDTV becomes the prevalent medium."</p>

<p>In May of 1992, in its Second Report and Order for implementing the HDTV service, the Commission decided to make a block allotment of frequencies for HDTV, and broadcasters would have the first option on these frequencies.</p>

<p>In its third Notice of Proposed Rule Making the FCC proposed to transition broadcasting to an all HDTV service, and to require broadcasters to surrender one of their two paired channels in 15 years from the date an HDTV standard is set and a final table of HDTV channel allotments is effective. At this time the NTSC service would be abandoned, but this schedule would be reviewed in 1998. Thus, the FCC was finalizing the regulatory procedures and rules that will govern HDTV terrestrial broadcasting.</p>

<p>The ACATS recommendation of an HDTV system was to have been made early in 1993, but in November 1992, a funny thing happened on the way to the recommendation! Toward the end of the testing process, and based on the test results, each of the system proponents identified a series of "improvements" for their systems, and requested permission of the FCC Advisory Committee to implement the improvements. </p>

<p>A Technical Sub-Group of the Advisory Committee chaired by Dr. Irwin Dorros and myself was appointed to review the improvement proposals, and approve those that were considered appropriate. This Technical Sub-group met on November 18, 1993 and approved many of the proposals.</p>

<p>The Special Panel of the Advisory Committee met the week of February 8, 1993 to consider the test results and the system improvements with a view toward selecting a final HDTV system to recommend to the Advisory Committee.</p>

<p>While all the systems produced good HDTV pictures in a 6 MHz channel, none of the systems were judged to have performed sufficiently well to be selected as the single standard at that time. but since the all-digital systems performed significantly better than the hybrid analog/digital Narrow Muse system, this system was dropped from further consideration The Special Panel approved making the improvements for the four all-digital systems, and recommended expeditious re-testing of the systems.</p>

<p>Meanwhile, the final four digital system proponents began to examine the possibility of combining their systems into a single best-of-the-best HDTV system through a consortium that came to be known as the "Grand Alliance". The Grand Alliance was formed on May 24, 1991 by the four digital HDTV system proponents -AT&T/Zenith, General Instrument, DSRC/Thomson/Philips, and MIT.</p>

<p>The FCC Advisory Committee assigned its Technical subgroup, including Official Observers from Canada, Mexico, the EBU, and NHK, and still under the chairmanship of Dr. Dorros and myself, the task of reviewing the Grand alliance proposal, modifying it as necessary, selecting final specifications, and approving the system for prototype construction. Following detailed system review and modification the Grand Alliance and the Technical Subgroup recommended the system parameters:</p>

<p>The system would support two, and only two, scanning rates of 1080 active lines with 1920 square pixels-per-line interlace scanned at 59.94 and 60 fields/second and 720 active lines with 1280 pixels-per-line progressively scanned at 59.94 and 60 frames/second. Both formats would also would operate in the progressive scanning mode at 30 and 24 frames/second. <br />
The system would employ MPEG-2 compatible video compression and transport systems. <br />
The system would use the Dolby AC-3, 384 Kb/s audio system. <br />
Following the subsystem transmission tests of the Vestigial Sideband or VSB system and the Quadrature Amplitude Modulation or QAM system, the VSB system was approved on February 24, 1994.</p>

<p>The Grand Alliance system was built, tested, and field tested to verify that it performed better than any of the four separate proponent HDTV systems. It was the-best-of-the-best.</p>

<p>At this late date, in the Spring of 1995, FCC Chairman Reed Hundt required the Advisory Committee to include several Standard Definition or SDTV formats in the DTV standard, and, without further SDTV tests, the SDTV formats were added to the ATSC scanning formats and the planned transition to HDTV in America became a transition to SDTV and HDTV.</p>

<p>The ACATS digital TV and HDTV standard was recommended to the FCC by the Advisory Committee on November 28, 1995. In another last minute change, the FCC promoted a series of meetings among the broadcasters, the consumer equipment manufacturers, and members of the computer industry to agree on a compromise in the scanning formats of the ATSC standard. Thus, the scanning formats were privatized and are now private sector ATSC standards. Following this compromise, the ATSC standard was finally approved by the FCC on December 24th, 1996 and was mandated for terrestrial DTV/HDTV broadcasting.</p>

<p>81 days later, on April 3, 1997, the FCC adopted-digita1 channel assignment plan and the DTV service rules.</p>

<p>The FCC fifth, and final, Report and Order in the proceeding on digital television was summarized by the FCC as follows:</p>

<p>"The overarching goa1 in this proceeding is to provide for the success of free, local digital broadcast television. To bolster DTV's chance for success, the Commission's decisions allow broadcasters to use their channels according to their best business judgement, as long as they continue to offer free programming on which the public has come to rely. Specifically, broadcasters must provide a free digital video programming service that is at least comparable in resolution to today's service and aired during the same time periods as today's analog service. Broadcasters will be able to put together whatever package of digital product they believe will best attract customers and to develop partnerships with others to help make the most productive and efficient use of their channels. Giving broadcasters the flexibility in their use of their digital channel will allow them to put together the best mix of services and programming to stimulate consumer acceptance of digital technology and the purchase of digital receivers.</p>

<p>"The Commission requires the affiliates of the top four networks (ABC, CBS, Fox, & NBC in the top 10 markets to be on-the-air with a digital signal by May 1, 1999. Affiliates of the top four networks in markets 11 - 30 must be on-the-air by November 1, 1999." "An important goal in this proceeding is the return of the analog (NTSC) spectrum at the end of the DTV transition period. The Commission has set a target of 2006 as a reasonable end-date for NTSC service. The Commission will review that date in its periodic reviews, which will be conducted every two years to allow evaluation of the progress of DTV and changes in Commission rules, if necessary."</p>

<p>Under a separate voluntary agreement made at the urging of the FCC, some group owners, including CBS, agreed to have some top-ten digital stations on-the-air by November 1, l998. CBS will have four of its digital TV and HDTV stations on-the-air by this date.</p>

<p>Thus, after 9 years, 3 months and 22 days of study, debate, design, construction, testing, and rulemaking, America's terrestrial broadcasters have the ATSC digital TV and HDTV system that, if used promptly, will assure their competitive parity with other 21st century distribution media.</p>

<p>The FCC has posed an aggressive rollout, a short transition period, and outlined a difficult DTV broadcasting schedule. Moreover, this digital transition will not occur in a "free marketplace" environment.</p>

<p>The Federal Government wants to recover the present NTSC spectrum in the shortest possible time to auction and re-use it, and this will result in constant government pressure to complete the digital transition in the shortest possible time. This, in turn, will foreshorten the transition period over that which would normally occur in a fully "free marketplace".</p>

<p>In the past, all competition in the TV marketplace was based on the same general technical quality. It was 525 line NTSC from the camera on stage to the receiver in the home, and senior managers never had to make decisions on program presentation quality. Hereafter, there will be a wide range of technical qualities delivered to viewers from SDTV, through "SDTV multiplex" programming, to full quality HDTV Technical quality will become an increasing factor in the competition for viewers. Wide screen HDTV will be offered by various television media, and, thus, HDTV will always be just a "channel click" away.</p>

<p>HDTV may become the primetime medium of choice by producers, programmers, the distribution media, and by the viewing public. Some cable and DPS programmers have already declared their intent to provide HDTV program services. Naturally, some broadcast dayparts will, of necessity, be composed of SDTV programs for some time to come, and some broadcasters, cable operators, and satellite-to-home systems will employ "multiplexed'' SDTV programming.</p>

<p>However, in considering the importance of HDTV, it is vital to understand that wide screen high definition is not just pretty pictures for today's small screen TV sets. Rather, it is a wholly new digital platform which will support the larger and vastly improved displays in development for commercialization. HDTV viewed on such large wide screen displays will create an entirely new viewing experience in the home. HDTV will finally make the home theater a practical reality.</p>

<p>Digital implementation has begun, and every segment of the television business will feel the impact of this digital revolution. Moreover, digital advances are moving so quickly that their impact will be felt faster than many believe. We call this "digitization" a revolution because digital technology will radically change television's means of communication, its quality, its flexibility, the conduct of the business, the scope and effectiveness of the service, and virtually every aspect of the medium.</p>

<p>Today, twenty five years after NHK began its HDTV work, the ATSC HDTV standard employs the basic 1973, wide screen, 1125 line system with l080 active lines, interlace scanned 2-to-1. As defined by the SMPTE and CCIR studies, "High Definition" television was, is, and will always be, a system employing at least 4000 active lines, interlace or progressively scanned. Lesser formats may be "improvements'' over NTSC and PAL systems, but are not, and will never be, "High Definition"!</p>

<p>The ATSC standard is adopted, the digital channels are assigned, the service rules are in place, the equipment is beginning to roll out, digital TV and HDTV receivers are in design for marketing in 1998, and digital stations are in construction, with several already on-the-air, and with many more to come. By Autumn 1998 digital stations will be on the air in each of the top ten markets, and by May 1999 30% of American households will have access to DTV and HDTV. By November 1999 50% of households will have access to DTV and HDTV, and all TV stations will be on-the-air with a digital TV signal by May 1, 2003.</p>

<p>For America the digital TV and HDTV era is here! Join the resolution!</p>

<p>As Alexander Pope advised in his 1710 "Essay on Criticism":</p>

<p>"Be not the first by whom the new are tried, nor yet the last to lay the old aside."</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 12, 2005 10:12 PM</b>
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
			<?=getComments(72)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 72)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/a_perspective_on_digital_tv_and_hdtv.php" type="text/javascript" charset="utf-8"></script>
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