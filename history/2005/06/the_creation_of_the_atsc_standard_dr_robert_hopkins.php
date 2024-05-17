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
		AND e.entry_id = 112";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 112 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 112 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 112";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_dr_robert_hopkins.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 112";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The Creation of The ATSC Standard - Dr. Robert Hopkins" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The Creation of The ATSC Standard - Dr. Robert Hopkins" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The Creation of The ATSC Standard - Dr. Robert Hopkins" />
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
	<title>HDTV Magazine - The Creation of The ATSC Standard - Dr. Robert Hopkins</title>
	<meta name="keywords" content="working party, digital systems, dsc hdtv, — —, special panel, systems, system, atv, hdtv, digital, ntsc, television, channel, working, interference, proposed, committee, —, test, digicipher, ccdc, party, dsc, special, panel" />
	<meta name="description" content="In early 1987 the Federal Communications Commission (FCC) was considering the reassignment of portions of the UHF spectrum from broadcasting to land mobile communications (&quot;two-way&quot;). Terrestrial broadcasters, concerned that the loss of spectrum would preclude their participation in high definition television (HDTV), sponsored a demonstration of terrestrial HDTV broadcasting in Washington, DC. The only operating system at that time was the MUSE (Multiple Sub-Nyquist Sampling Encoding) system which had been designed for satellite broadcasting by NHK, the Japan Broadcasting Corporation. The RF bandwidth requirement for the terrestrial demonstration was about 9 MHz - the television channel in the United States is 6 MHz - so two adjacent channels, 58 and 59, were used. The demonstration was very successful.
" />
	<meta name="title" content="The Creation of The ATSC Standard - Dr. Robert Hopkins" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The Creation of The ATSC Standard - Dr. Robert Hopkins" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_dr_robert_hopkins.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In early 1987 the Federal Communications Commission (FCC) was considering the reassignment of portions of the UHF spectrum from broadcasting to land mobile communications (&quot;two-way&quot;). Terrestrial broadcasters, concerned that the loss of spectrum would preclude their participation in high definition television (HDTV), sponsored a demonstration of terrestrial HDTV broadcasting in Washington, DC. The only operating system at that time was the MUSE (Multiple Sub-Nyquist Sampling Encoding) system which had been designed for satellite broadcasting by NHK, the Japan Broadcasting Corporation. The RF bandwidth requirement for the terrestrial demonstration was about 9 MHz - the television channel in the United States is 6 MHz - so two adjacent channels, 58 and 59, were used. The demonstration was very successful.
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=112', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_dr_robert_hopkins.php">The Creation of The ATSC Standard - Dr. Robert Hopkins</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 23, 2005</b>
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
				<p>Dr. Robert Hopkins<br />
April 1994</p>

<p>Robert Hopkins (Senior Member, IEEE) <em>joined the Advanced Television Systems Committee (ATSC) in January 1985 as the Executive Director. He is responsible for both the technical and administrative guidance of the ATSC. The ATSC is a standards organization sponsored by more than 50 companies involved in HDTV. The ATSC addresses issues concerning advanced television in order to provide private sector input to the Department of State for international conference preparation and to the Federal Communications Commission to assist in their HDTV decision-making process.</p>

<p>Previously, Dr. Hopkins was employed by RCA for twenty years. After several years at the RCA David Sarnoff Research Center, Princeton, NJ, where he was involved in television research, Dr. Hopkins transferred to the RCA Broadcast Systems Division, Gibbsboro, NJ where he held a number of positions including manager of strategic planning and manager, field camera products and engineering.  His most recent position at RCA was managing director of RCA Jersey Limited, Jersey Channel Islands, Great Britain, an overseas subsidiary that manufactured professional television equipment for a worldwide market.</p>

<p>While at RCA, Dr. Hopkins received two Outstanding Achievement Awards from RCA Laboratories.  He is a past chairman of SMPTE's Standards Committee and the Committee on New Technology. He was appointed the first chairman of the SMPTE Working Group on Digital Video Standards in 1977. He is a Fellow of SMPTE and a Senior Member of the IEEE. He serves as the United States representative on HDTV to the CCIR.</p>

<p>He received his Bachelor of Science degree in Electrical Engineering from Purdue University in West Lafayette, Indiana; Master of Science and PhD degrees from Rutgers University, New Brunswick, New Jersey; and is a graduate of the Harvard Business School Program for Management Development.</em> </p>

<p>____________________________________________</p>

<p>A public process has been in place in the United States for six years to establish an HDTV terrestrial broadcasting standard. The process, having moved through a planning phase, a competition phase, and an examination phase, has now entered a cooperation phase. Remarkable progress has been made - a testament to the process. During the next -few months, the American digital HDTV terrestrial broadcasting system will be tested, fully documented, and recommended to the FCC for adoption. </p>

<p><strong>I. INTRODUCTION</strong><br />
In early 1987 the Federal Communications Commission (FCC) was considering the reassignment of portions of the UHF spectrum from broadcasting to land mobile communications ("two-way"). Terrestrial broadcasters, concerned that the loss of spectrum would preclude their participation in high definition television (HDTV), sponsored a demonstration of terrestrial HDTV broadcasting in Washington, DC. The only operating system at that time was the MUSE (Multiple Sub-Nyquist Sampling Encoding) system which had been designed for satellite broadcasting by NHK, the Japan Broadcasting Corporation. The RF bandwidth requirement for the terrestrial demonstration was about 9 MHz - the television channel in the United States is 6 MHz - so two adjacent channels, 58 and 59, were used. The demonstration was very successful.</p>

<p>Shortly thereafter, a number of terrestrial broadcasters jointly petitioned the FCC to initiate a proceeding to explore the issues arising from the introduction of advanced television (ATV) technologies and their possible impact on the television broadcasting service {Note 2}. The broadcasters were concerned that alternative media (cable, satellite broadcasting, etc.) would be able to deliver HDTV to the viewing public while they could not without additional spectrum.</p>

<p>The FCC did initiate a proceeding (MM Docket No. 87-268) to consider the technical and public policy issues of ATV in July 1987. Late in 1987 the FCC formed the Advisory Committee on Advanced Television Service. The objective given to the Advisory Committee was:</p>

<p>The Committee will advise the Federal Communications Commission on the facts and circumstances regarding advanced television systems for Commission consideration of technical and public policy issues. In the event that the Commission decides that adoption of some form of advanced broadcast television is in the public interest, the Committee would also recommend policies, standards and regulations that would facilitate the orderly and timely introduction of advanced television services in the United States.</p>

<p>That proceeding, now six years in duration, has produced startling results. Six years ago it was conventional wisdom that more than 6 MHz would be required to broadcast HDTV. In Europe and Japan early decisions were made to obtain needed spectrum by using satellite broadcasting. That was not an acceptable solution in the United States where there are some 1,400 terrestrial broadcasters. Not only are they a very powerful force in our private sector, but they also provide a local public information service that is very important to our citizens. A different solution was required, and it has been found - digital compression. In 1987 virtually nobody believed that digital broadcasting would be possible for many years in the future. Now we know that the United States HDTV standard will be digital and it will fit into the 6 MHz channel.</p>

<p>In 1986 there were five proposed broadcasting systems that were being discussed in the United States. Two were satellite systems, two were augmentation systems {Note 3}, and one was a simulcast system {Note 4} that would have required a channel bandwidth of at least 9 MHz for the ATV signal.</p>

<p>One year later, there were ten systems - two more satellite systems, one more augmentation system, and two NTSC receiver-compatible systems.</p>

<p>In 1989 there were 21 systems - three analog 6 MHz simulcast proposals, five augmentation proposals, and ten NTSC receiver-compatible systems.</p>

<p>One year later only six systems remained in competition. Four were 6 MHz analog simulcast systems. The other two were NTSC receiver-compatible systems.</p>

<p>Consensus was forming. Satellite systems were out. So were augmentation systems. The public process was working well. Participants in the process put forth their ideas. The best survived peer review. Then, in June 1990, an even better idea was put forward - digital. A few months later there were three more digital simulcast proposals. When testing began in 1991, there was one NTSC receiver-compatible system, one analog simulcast system, and four digital simulcast systems. After the FCC made it clear that an HDTV simulcast system was preferred over an EDTV system {Note 5}, the NTSC receiver-compatible system was withdrawn from consideration.</p>

<p>The tests were completed in late 1992. Early in 1993, under critical examination, the analog simulcast system was eliminated from further consideration during a week-long meeting of a "Special Panel" established to compare the tested systems. The Special Panel recommended further testing of the remaining four digital systems.</p>

<p>Another significant event occurred in May 1993. The proponents of the four digital systems agreed to form a "Grand Alliance" and to merge their individual systems into a single system by incorporating the best features of each. At the time this paper was written, the Advisory Committee was examining the proposal of the Grand Alliance.</p>

<p>This paper will review the steps that have already been taken in this proceeding and those that are expected to occur in the future. The paper is organized to follow the phases of the public process. Those phases are: Planning, Competition, Examination, Cooperation, and Adoption.</p>

<p><strong>II. PLANNING PHASE</strong><br />
The planning phase began with the formation of the Advisory Committee in November 1987. The Advisory Committee was organized into three subcommittees and a number of working parties in each subcommittee Committee on Advanced Television Service.</p>

<p>-Planning Subcommittee <br />
-Technology Attributes and Assessments <br />
-Testing and Evaluation Specifications <br />
-Spectrum Utilization and Alternatives <br />
-Alternative Media and Broadcast Interface <br />
-Economic Factors and Market Penetration <br />
-ATV Systems Subjective Assessment <br />
-Audience Research <br />
-Creative Issues <br />
-Consumer/Trade Issues <br />
-Systems Subcommittee <br />
-Systems Analysis <br />
-Systems Evaluation and Testing <br />
-Systems Economic Assessment <br />
-Systems Standard <br />
-Implementation Subcommittee <br />
-Policy and Regulation <br />
-Transition Scenarios </p>

<p>A description of the objective of each of the Subcommittees and Working Parties can be found in [Ref. 2]. As may be expected, the Planning Subcommittee played the major role in the planning phase. This phase of the effort lasted about one year.</p>

<p>All Planning Subcommittee subgroups were quite active during that period. The different subgroups were defining the attributes of, and test specifications for, terrestrial ATV transmission systems, carrying out studies on possible spectrum schemes for ATV, examining the interfaces between broadcast and alternative media, examining the economic factors from a macro perspective, planning subjective assessment tests and test materials and market research programs, and examining the creative, consumer, and trade issues that might affect ATV broadcasting.</p>

<p>Much of the work of the Planning Subcommittee was completed during this phase. Two of the subgroups have continued their work throughout the entire process, however. Those two subgroups are the Working Party on Spectrum Utilization and Alternatives and the Working Party on ATV Systems Subjective Assessment. The work of the Working Party on Alternative Media Technology and Broadcast Interface continued through the competition and examination phases.</p>

<p><strong>III. COMPETITION PHASE</strong><br />
The beginning of the competition phase was marked by a week long meeting in November 1988, often referred to as "Hell Week" because of the long hours, the intensity of the meeting, and the strong technical challenges which had to be addressed "on the spot." This meeting was conducted by the Working Party on Systems Analysis to examine the system proposals that had been submitted by a number of proponents. Each proponent was given half a day to make a presentation on the proposed system at a block diagram level and respond to questions from a large number of technical experts. The primary purpose of the meeting was to "pre-certify" those systems that passed this first round of critical examination. A proposal received "pre-certification" if the technical experts believed that the proposed system could work and if the proponent appeared to have the wherewithal to construct the system and present it for testing.</p>

<p>Those systems that received "pre-certification" were examined in greater detail in a subsequent meeting. The timing of the second examination was about three months before testing was to begin on each particular system. A task force met with the proponent many times before the scheduled meeting of the working party to gain a detailed understanding of how the system worked and to propose tests specific to that system. Many more details of the system had to be divulged by the proponent during this period. During the meeting of the working party, the system received "final certification" if the technical experts were satisfied that they understood how the system worked and believed it would be ready for testing as scheduled.</p>

<p>The Working Party on Systems Evaluation and Testing began its work during this phase. Using the attributes and test specifications developed in the Planning Subcommittee, this working party began developing the specific test plans that would eventually be used in testing the surviving proposed systems. Not only was competition among the proponents evident in the design of their different systems, it was a factor in the working parties. Indeed, because the proponents had extensive technical expertise, they were welcomed as members of the working parties - in many cases they could offer the most perceptive critique of their competitors' systems. These inputs were most helpful in designing the tests.</p>

<p>The Working Party on Systems Standard developed a process that the Advisory Committee could use to recommend a single ATV system to the FCC. That process is shown in Figure 2. The process would begin with the identification of a set of "Selection Criteria," described as the key issues that must be considered in selecting a new television system. Once the Selection Criteria were identified, each proposed system would be analyzed with regard to the Selection Criteria. Systems found to satisfy the Selection Criteria would be compared with each other to determine the areas in which significant differences occur. From the process, superior systems could be identified. The process had feedback loops to ensure that the key issues were compared with sufficient sensitivity. The goal was to select the one system that would give the best balance in satisfying the Selection Criteria. </p>

<p>Ten issues were identified as being the most critical issues. They fell into three categories - Spectrum Utilization, Economics, and Technology - and are shown in Figure 3. </p>

<p>-Spectrum Utilization <br />
-Service Area <br />
-Accommodation Percentage <br />
-Economics <br />
-Cost to Broadcasters <br />
-Cost to Alternative Media <br />
-Cost to Consumers <br />
-Technology <br />
-Audio/Video Quality <br />
-Transmission Robustness <br />
-Scope of Services and Features <br />
-Extensibility <br />
-Interoperability Considerations </p>

<p><strong>IV. EXAMINATION PHASE</strong>A. <br />
<em>Analysis of Certified Systems</em><br />
During the examination phase, the certified systems were tested in three laboratories established for this specific purpose. In 1988 the Advanced Television Test Center (ATTC) was formed as a private, non-profit organization by television broadcasting organizations and other industry organizations to test the proposed systems. Cable Television Laboratories (CableLabs), a research and development consortium of cable television system operators, established a special test facility at the ATTC to conduct the cable tests. Also, the Advanced Television Evaluation Laboratory (ATEL) was established in Canada by the Department of Communications to undertake the video subjective test program using a large number of non-expert viewers.</p>

<p>Each certified system was tested for about two months at the ATTC and CableLabs facilities following the procedures developed by the Working Party on Systems Evaluation and Testing. While the system was at the ATTC, video tapes were made under various impaired and non-impaired conditions for use at ATEL in performing the subjective assessments developed by the Working Party on ATV Systems Subjective Assessment. The subjective assessments on each system also required about two months.Many of the tests conducted by ATTC and ATEL were designed to provide information for the Working Party on Spectrum Utilization and Alternatives so that an analysis could be performed to determine the spectrum utilization of each tested system.</p>

<p>As details of the certified systems became known, the Working Party on Systems Economic Assessment analyzed each system to determine approximately what the cost of the system would be to broadcasters and to consumers. Broadcasters' costs were defined as the cost to go on-air with a network feed (i.e., without studio/production equipment at the local station). Consumers' costs were defined as 2.5 times the material cost of a 34" widescreen direct view receiver or a 56" widescreen CRT type projector.</p>

<p>During this phase, the Working Party on Transition Scenarios was active examining issues which would need attention in making the transition to ATV broadcasting. With the involvement of the Working Party on Systems Economic Assessment, each certified system was examined to determine if there were any special needs posed by that system. The Working Party on Policy and Regulation examined issues related to regulations that might be required for the transition.</p>

<p>Toward the end of this phase, after most of the proposed systems had become digital systems, the Working Party on Alternative Media Technology and Broadcast Interface was given a new assignment to examine issues of interoperability with computers and other digital media. All systems were individually analyzed to determine their degree of interoperability.</p>

<p>The Working Party on Systems Standard was charged with drafting the ATV System Recommendation report for the Advisory Committee. The working party agreed to an outline for the report and drafted the portion defining the Selection Criteria before the first test data were available. Once test data became available, the Working Party on Spectrum Utilization and Alternatives and the Working Party on Systems Standard reviewed and summarized the test data for each system. Other information for the report was supplied by other working parties. All this information was edited and integrated into the report by the Working Party on Systems Standard.</p>

<p>In March 1992 the Advisory Committee agreed to appoint a Special Panel that would take the results of the analyses of the individual proponent systems in the ATV System Recommendation report and formulate recommendations for the Advisory Committee's consideration. The Special Panel membership included Advisory Committee staff leaders and other knowledgeable participants in the Advisory Committee who were not affiliated with any system proponent. The responsibility for drafting a chapter of the report comparing the proposed systems was assigned to the Special Panel.</p>

<p><strong>B. Comparison of Certified Systems</strong><br />
The ATV System Recommendation report gives the results comparing the tested systems. The report is available in [Ref. 3]. A summary of the comparative results is given in this section {Note 6}.</p>

<p>Five HDTV simulcast systems were examined in the report. Narrow-MUSE, an analog system proposed by NHK; DigiCipher, a digital system proposed by The American Television Alliance (General Instrument Corporation and the Massachusetts Institute of Technology); Digital Spectrum Compatible HDTV (DSC-HDTV), a digital system proposed by Zenith and AT&T; Advanced Digital HDTV (AD—HDTV), a digital system proposed by the Advanced Television Research Consortium (ATRC) which includes David Sarnoff Research Center, North American Philips, Thomson Consumer Electronics, NBC, and Compression Labs, Incorporated; and Channel Compatible DigiCipher (CCDC), a second digital system proposed by the American Television Alliance.</p>

<p><strong>1)  Special Panel findings and recommendations: </strong><br />
The Special Panel met on February 8 - 11, 1993. The resulting findings and recommendations follow.</p>

<p><em>Spectrum utilization findings:</em><br />
The analysis conducted by the Advisory Committee clearly demonstrates that a substantial difference exists in spectrum utilization performance between the analog Narrow-MUSE system and the four all-digital systems. The differences among the four digital systems generally are far less pronounced, however. Based on this analysis, it would appear that Narrow-MUSE will not prove to be a suitable terrestrial broadcasting ATV system for the United States. <br />
The Special Panel notes that many system proponents have proposed improvements to their systems in the area of spectrum utilization. The Special Panel finds that the system improvements, primarily those identified by its Technical Subgroup as ready for implementation in time for testing, may lead to improvements in spectrum utilization and should be subjected to testing as soon as possible. <br />
The Special Panel finds that the degree of interference from ATV-into-NTSC is recognized as an area of concern in certain markets. The Special Panel finds that the issue of ATV-into-NTSC interference, including interference to BTSC audio, should be addressed in the remaining stages of the system selection process, including the examination of refined allotment/assignment techniques, the study of possible beneficial effects of system improvements, and the consideration of any mitigations which might be achieved by transitional implementation policies. </p>

<p><strong>Economics findings:</strong><br />
No significant cost differences among the five proponent systems, either in costs to consumers or to broadcasters, are evident. Thus, based on cost alone, there is no basis to discriminate among systems. However, the additional benefits offered to broadcasters and others by the digital systems were noted as significant. </p>

<p><strong>Technology findings:</strong></p>

<p>As a result of the testing process, the Advisory Committee is confident that a digital terrestrial advanced television system can provide excellent picture and sound quality. All of the system proponents have proposed refinements that are likely to enhance the audio and video quality beyond that measured in the testing process. <br />
A variety of transmission formats was evaluated. The transmission robustness analysis conducted by the Advisory Committee clearly reveals that an all-digital approach is both feasible and desirable. All of the system proponents have proposed refinements that are likely to enhance robustness beyond that measured in the testing process. </p>

<p>An all-digital system approach is important to the scope of ATV services and features and in the areas of extensibility and interoperability. All four digital proponents have committed to a flexible packetized data transport structure and universal headers/descriptors. Progressive-scan/square-pixel transmission is considered beneficial to creating synergy between terrestrial ATV and national information initiatives. As well, scalability at the transmission data stream would permit trade-offs in "bandwidth on demand" network environments. <br />
Recommendations:</p>

<p>While all the proponents produced advanced television systems, the Special Panel notes that there are major advantages in the performance of digital HDTV systems in the United States environment and recommends that no further consideration be given to analog-based systems. The proponents of all four digital HDTV systems - DigiCipher, DSC-HDTV, AD-HDTV, and CCDC - have provided practical digital HDTV systems that lead the world in this technology. Because all four systems would benefit significantly from further development, the Special Panel does not recommend any one of these systems for adoption as a United States terrestrial ATV transmission standard at this time. Rather, the Special Panel recommends that these four finalist proponents be authorized to implement their improvements as submitted to the Advisory Committee and approved by the Special Panel's Technical Subgroup. <br />
The Special Panel further recommends that the approved system improvements be ready for testing not later than March 15, 1993, and that these improvements be laboratory and field tested as expeditiously as possible. The results of the supplemental tests, along with the already planned field tests, would provide the necessary additional data needed to select a single digital system for recommendation as a United States terrestrial ATV transmission standard. </p>

<p><strong>2)  Spectrum utilization comparisons:   </strong></p>

<p>Two spectrum utilization selection criteria were compared: accommodation percentage and service area. "Accommodation percentage" specifies the fraction of existing NTSC television stations that could be assigned an ATV channel. "Service area" refers to the interference-limited coverage area of new ATV stations.</p>

<p>The analysis of spectrum usage of the proposed systems employed an allotment approach developed by the FCC staff and a service and interference model developed by the Working Party on Spectrum Utilization and Alternatives. Combining the two in a computer program permitted development of approximate allotment/assignment plans and a comparison of the service expected to be provided by each system, if implemented, with the service provided by the NTSC system currently in use.</p>

<p>The computer program seeks, station-by-station, to match or exceed the current interference-limited NTSC service area with future companion ATV service area. The analysis includes consideration of vacant noncommercial allotments as well as authorized stations and pending applications {Note 7}. Station locations and antenna heights above average terrain are assumed to be the same for both the NTSC and ATV services. Other input parameters to the program are the planning factors applicable to all ATV systems (see Figure 4) and factors specific to each ATV system (see Figure 5) as determined by the test programs at the ATTC and ATEL. </p>

<p>_________________________________<br />
 <br />
Figure 4. Receiver planning factors applicable to all ATV systems.</p>

<p> Low VHF High VHF UHF <br />
Antenna Impedance (ohms) 75.0 75.0 75.0 <br />
Bandwidth (MHz) 6.0 6.0 6.0 <br />
Thermal Noise (dBm) -106.2 -106.2 -106.2 <br />
Noise Figure (dB) 10.0 10.0 10.0 <br />
Frequency (MHz) 69 194 615 <br />
Antenna Factor (dBm/dBu) -111.7 -120.7 -130.7 <br />
Line Loss (dB) 1.0 2.0 4.0 <br />
Antenna Gain (dB) 4.0 6.0 10.0 <br />
Antenna F/B Ratio (dB)* 10 12 14 </p>

<p><br />
* In addition to F/B ratio, a formula is employed for the forward lobe simulating an actual receiving antenna pattern. </p>

<p><br />
_______________________________________</p>

<p>Figure 5. System-specific planning factors (D/U in dB).</p>

<p>CARRIER-TO-NOISE N-MUSE DigiCipher DSC-HDTV AD-HDTV CCDC <br />
 +38 +16.0 +16.0 +18.4 +15.4 <br />
      <br />
CO-CHANNEL N-MUSE DigiCipher DSC-HDTV AD-HDTV CCDC <br />
ATV-into-NTSC +16.8 +35 +35 +34 +36 <br />
NTSC-into-ATV +21 +7.6 +3.5 +0.50 +8.1 <br />
ATV-into-ATV +31 +16.4 +18.2 +19.1 +16.6 <br />
     <br />
ADJACENT-CHANNEL N-MUSE DigiCipher DSC-HDTV AD-HDTV CCDC <br />
Lower ATV-into-NTSC —31 —13.5 —17.2 —16.0 —17.8 <br />
Upper ATV-into-NTSC —12.0 —21 —7.5 —8.9 —17.0 <br />
Lower NTSC-into-ATV +28 —30 —43 —38 —37 <br />
Upper NTSC-into-ATV —11.8 —24 —42 —36 —37 <br />
Lower ATV-into-ATV —15.5 —23 —35 —33 —32 <br />
Upper ATV-into-ATV +16.6 —23 —36 —16.8 —32 </p>

<p><br />
___________________________</p>

<p> <br />
An initial program run using NTSC provided the reference for each of the ATV systems tested. The program output includes Grade B coverage area and interference-limited service area for each of the 1,657 authorized and applied-for television facilities in the August 1, 1992 FCC data base.</p>

<p>The analysis was conducted under two allotment scenarios (using both VHF and UHF channels for ATV stations, and using only UHF channels) and two sets of interference constraints (considering only co-channel interference, and considering both co-channel and adjacent-channel interference). In addition, the impact of taboos {Note 8} was assessed by recalculating coverage and interference for each scenario assuming the taboo performance measured in the laboratory. The Working Party on Spectrum Utilization and Alternatives determined that the analysis should be considered in the following priority order: 1) co-channel and adjacent-channel interference, 2) only co-channel interference, and 3) co-channel, adjacent-channel and taboo interference.</p>

<p>With the exception of one system - Narrow-MUSE - allotment/assignment schemes could be created to accommodate 100% of existing NTSC broadcast stations. Narrow-MUSE allotment/assignment plans accommodated 77.2% or 73.7% under the VHF/UHF and UHF-only channel availability options, respectively. Tradeoffs exist in the process of allotting ATV channels. While attempts were made to match the ATV coverage with that of companion NTSC stations, the provision of ATV allotments was accomplished by reducing ATV coverage areas for some stations and by introducing some new interference to the coverage areas of a portion of the set of existing NTSC stations.</p>

<p>Figure 6 depicts the interference-limited service area of each ATV station, during the transition period, relative to the interference-limited service area of its companion NTSC station under the VHF/UHF Scenario, taking into account both co-channel and adjacent-channel constraints. In this graph, the 1,657 current, and planned, NTSC stations are placed in order of decreasing ATV to NTSC service area ratio. Examination of the graphs reveals that about 1200 of the ATV stations would have an ATV service area equal to or greater than the size of their companion NTSC service area with any one of the four digital ATV systems. </p>

<p><br />
________________________________________</p>

<p>Figure 6. Interference-limited service area of each ATV station relative to the interference-limited service area of its companion NTSC station (co-channel and adjacent-channel constraints).</p>

<p> <br />
______________________________________</p>

<p><br />
Examination of the ATV coverage during and after the transition revealed that the performance of the DSC-HDTV and CCDC systems was slightly better than that of the DigiCipher and AD-HDTV systems. The performance of the Narrow-MUSE system in this category was significantly worse than that of the four all-digital systems.</p>

<p><strong>3)  Economic comparisons: </strong> <br />
There were some nominal cost differences among the systems in the estimated costs to both consumers and broadcasters. However, these differences in costs were of a minor magnitude and were judged to be indistinguishable.</p>

<p>Broadcasters costs were developed for each item on a station block diagram for each of the proposed ATV systems. It was assumed that the station's existing tower has sufficient capacity for installation of the new ATV antenna and transmission line and that the station's equipment space has room for additional gear. Additionally, the analysis was based on the use of a compressed NTSC signal multiplexed into the same STL with the ATV signal. The equipment cost for a station ranged from $1,700,500 to $1,785,500 for the five proposed systems.</p>

<p>Cost to consumers was based on technology predictions for 1998 using a manufacturing quantity of 1 million receivers. The proponents provided block diagrams, gate counts, and pin counts for suggested chip sets for their systems. It was generally recognized that the cost of the display would have a major impact on the cost of the receiver. The estimated total material cost for a 34" widescreen direct view receiver ranged from $978 to $1,048 for the five proposed systems. The estimated total material cost for a 56" widescreen projection receiver ranged from $1,494 to $1,564. Note that the estimated cost for the display was 70% of the total for both the 34" receiver and the 56" receiver. Note also that it was estimated that if selling price were 2.5 times the material cost, the 34" direct view receiver selling price would be about $2,530 and the 56" projection receiver selling price would be about $3,830.</p>

<p><strong>4)  Technology comparisons:  </strong><br />
The Special Panel examined five selection criteria under the heading Technology: Quality, Transmission, Scope of Services and Features, Extensibility, and Interoperability Considerations. Of the five, the first two - quality and transmission - were based on actual system testing. The other three were primarily the subject of detailed analyses of the systems as certified.</p>

<p>The Special Panel concluded that four excellent digital HDTV systems were developed as the result of this process. Digital ATV transmission is completely viable for over-the-air broadcasting and for transmission by the alternative media of cable and satellite. The overall picture quality of two systems came remarkably close to the quality of the 1125-line high-definition studio reference.</p>

<p>The extensive measured data and subjective assessments of the systems, however, also revealed the magnitude of the challenges associated with achievement of high overall picture and sound quality while simultaneously ensuring adequate coverage, transmission robustness, and acceptably low interference in a simulcast environment - all within the bounds of a reasonable average effective radiated power.</p>

<p>The Special Panel's examination further revealed that there are likely to be pragmatic tradeoffs required between the fundamental ATV requirements (under the quality and transmission criteria) and the sometimes conflicting but desirable capabilities described under the criteria of scope of services and features, extensibility and interoperability.</p>

<p>In Basic Received Quality {Note 9}, DigiCipher and AD-HDTV were judged, on average, only about 0.3 CCIR grades lower in quality than the 1125-line studio reference for most segments of test material; the other systems exhibited lower performance (see Figure 7). All systems, however, exhibited visible weaknesses in one or more tests designed to address other matters relating to quality (e.g., noisy source material, multiple encode/decode operations, etc.). </p>

<p><br />
_______________________________________</p>

<p>Figure 7. Average differences between quality judgments for the 1125-line studio quality reference and for each of the proposed ATV systems.</p>

<p> Color <br />
__________________________________</p>

<p>For still material, the ATV systems did not differ significantly overall. For live video and for film, however, the DigiCipher and AD-HDTV systems exhibited significantly better performance than the other systems. For a graphic sequence that stressed vertical and temporal performance, the DSC-HDTV and CCDC systems performed best.</p>

<p>For noisy source material, the DigiCipher and AD-HDTV systems performed significantly better than the other systems. For scene cuts, the AD-HDTV system performed best. For material subjected to concatenated encode/decode operations, the DigiCipher system performed best. For material designed to stress the source-coding algorithms of the four all-digital systems, the DigiCipher and CCDC systems performed best. And, finally, examinations of quality achieved under extended coverage conditions (made only for Narrow-MUSE, DSC-HDTV, and AD-HDTV) revealed a clear superiority for the Narrow-MUSE system.</p>

<p>Overall, these results show a clear advantage for the DigiCipher and AD-HDTV systems in terms of video quality. However, they also point to the necessity for improvement, even in the two leading systems.</p>

<p>In interpreting the results, three mitigating factors should be considered. First, the video and film material used in tests of the progressively scanned ATV systems (i.e., DSC-HDTV and CCDC) exhibited high levels of random noise, as well as horizontally coherent noise. Although this may have affected adversely the performance of these two systems, it is not possible to quantify the extent to which their performance would have been affected. Second, it is likely that all systems suffered from deficiencies in the prototype hardware brought to test. And, finally, since the time of test, all system proponents claim to have made improvements in image quality.</p>

<p>A number of tests related to transmission robustness were conducted. Ability to tolerate discrete, static echoes showed an advantage of about 20 dB to the digital systems. Among the digital systems, AD-HDTV was judged significantly superior for this attribute.</p>

<p>Flutter is time-varying multipath. DigiCipher and CCDC exhibited significantly superior tolerance of this impairment.</p>

<p>With regard to impulse noise, DSC-HDTV was significantly better than the other systems.</p>

<p>CCDC performed best for in-band discrete frequency rejection. DSC-HDTV performed best for out of band discrete frequency rejection.</p>

<p>The DigiCipher and CCDC systems each exhibited resistance to composite second order intermodulation distortion that was significantly greater than that of the other systems. The DSC-HDTV and AD-HDTV systems revealed significantly greater immunity to composite triple beat products than did the remaining systems.</p>

<p>All of the digital systems exhibited substantially greater immunity to phase noise than did the Narrow-MUSE system.</p>

<p>The DigiCipher and CCDC systems tolerated considerably greater residual frequency modulation than did the remaining systems.</p>

<p>The DigiCipher, DSC-HDTV, and CCDC systems demonstrated a substantially wider local oscillator pull-in range than the other systems. The DSC-HDTV system range exceeded ± 100 kHz, the maximum value prescribed in the formal test procedure.</p>

<p>DigiCipher and CCDC were most robust to co-channel interference from ATV. AD-HDTV was best at rejecting co-channel interference from NTSC. Narrow-MUSE performed significantly better than the digital systems for ATV-into-NTSC co-channel interference. All digital systems required about the same signal level to cause co—channel interference into NTSC. (See Figure 5.)</p>

<p>Narrow-MUSE performed significantly better than the digital systems on lower adjacent-channel ATV-into-NTSC interference by causing the least interference. Among the digital systems, DSC-HDTV performed best in rejecting ATV-into-ATV and NTSC-into-ATV adjacent-channel interference. DigiCipher and CCDC caused the least upper adjacent-channel ATV-into-NTSC interference. DSC-HDTV, AD-HDTV and CCDC caused the least lower adjacent-channel ATV-into-NTSC interference. (See Figure 5.)</p>

<p>Narrow-MUSE performed significantly better than the digital systems for ATV taboo interference into NTSC. Among the digital systems, DSC-HDTV had the best all-around ability to reject taboo interference on the nine channels tested; however, the performance of all digital systems was close.</p>

<p>The DigiCipher, DSC-HDTV, and CCDC systems completed a channel change under unimpaired conditions in approximately one second, versus substantially longer times recorded for Narrow-MUSE and for AD-HDTV.</p>

<p>The channel acquisition test measured the time required to acquire the signal and display a recognizable picture under a variety of impairment conditions; signal conditions were always above the threshold of visibility (TOV). The performance of DigiCipher, DSC-HDTV, and CCDC was judged superior to the other systems. The three cited systems were able to deliver a recognizable image within about one second under conditions of moderate impairment.</p>

<p>To determine failure and recovery picture appearance, signal strength was reduced below threshold level and then increased above threshold; the resulting image behavior was observed. This test simulated signal fading in fringe areas for digital systems. In general, all systems "froze" the image as the signal fell below threshold. Typically, the image became "blocky" and dissolved into other characteristic artifacts. Recovery was most rapid for AD-HDTV (much less than one second). DigiCipher recovered with characteristic panel wiping, lasting about 1/3 second. CCDC recovery generally consumed about 1/2 second but could last longer than one second. DSC-HDTV required the longest recovery period, generally 2-5 seconds. The speed and subjective appearance of AD-HDTV's recovery were judged significantly superior to the other systems.</p>

<p>The broadcast portion of the multiple impairment test determined the point of acquisition (POA) - which needed only to be a "recognizable" image, not a "watchable" one - under different conditions of random noise and co-channel impairments. The test results show that DSC-HDTV could acquire signal under the worst combination of these impairments, with AD-HDTV very close in performance. DigiCipher and CCDC required a significantly more favorable combination of conditions for signal acquisition. The cable portion of the multiple impairment test measured TOV under different combinations of random noise and composite triple beat. The test results show that DigiCipher, DSC-HDTV, and AD-HDTV exhibited better performance than CCDC. All of the digital ATV systems, however, are expected to operate with adequate margins of signal-to-noise and for composite triple beat on existing cable systems designed for carriage of NTSC signals using the nominal ATV power levels tested.</p>

<p>Narrow-MUSE, as expected from its analog signal format, exhibited gradual degradation of image quality with decreasing C/N. All of the digital systems had sharp thresholds, with image quality degrading from an unimpaired picture (TOV) to an unusable picture (POU) over less than a 2 dB change in C/N. Based on certification documents, this performance was expected for DigiCipher and CCDC. The claimed gradual thresholds of DSC-HDTV and AD-HDTV were judged to have utility only for short, temporary, and infrequent signal fading. The Special Panel found that no video performance advantages were found in the forms of gradual signal degradation tested, although it is desirable to maintain audio service during momentary disruptions in the picture.</p>

<p>The peak-to-average power ratios of DigiCipher and CCDC were judged significantly superior among the digital systems. It was noted that AD-HDTV required significantly more average ERP than the other systems.</p>

<p>Scope of Services and Features considered the need of an ATV system to support features and capabilities beyond those explicit in other selection criteria. All systems provided for data transmission. With respect to data, the AD-HDTV system was judged superior because it used a packetized data structure with headers and descriptors that, in general, is important for providing system flexibility. With respect to addressing, the AD-HDTV system was considered better than the other digital systems due to its ability to reassign its entire 18.5 Mbits/sec to addressing keys.</p>

<p>It was concluded that the use of a packetized data structure with universal headers and descriptors provides important flexibility for extensibility. For example, if a higher data rate channel is used to distribute programming to television stations, additional packets (with appropriate headers and descriptors) could provide higher quality images for post-production processing. Overall, the digital systems ranked better than the Narrow-MUSE system; however, there were no significant differences among the digital systems.</p>

<p>Interoperability considered delivery over alternative media (cable, satellite, packet networks), transcoding (with NTSC, film, and format conversion to other video standards), integration with computers and digital technology, interactive systems, the use of headers/descriptors, and scalability. Progressive scanning and square pixels are important for computer and other image applications. For interoperability with computers, DSC-HDTV and CCDC ranked better than the other systems. Only AD-HDTV had its final proposal for a packetized data structure and headers and descriptors implemented at the time the system was tested by ATTC, and it received the highest rating in the paper analysis on these characteristics. All digital system proponents now recognize the importance of a packetized data structure combined with headers and descriptors as a critical enabling concept for ATV flexibility. With respect to format conversion, Narrow-MUSE does not require conversion to 1125/60, and AD-HDTV's use of MPEG-1 provides the possibility of interoperability with MPEG (Moving Picture Experts Group) applications. The four digital systems were judged better than Narrow-MUSE for interoperability with digital technology, NTSC, film, still images, and interactive systems. Note that latency and acquisition time are important for interactive systems, but have not been completely determined. All five systems were judged suitably interoperable with satellite and cable.</p>

<p><strong>V. COOPERATION PHASE</strong>On <br />
May 24, 1993 the four digital system proponents announced that they had formed a "Grand Alliance" which would make a single system proposal to the Advisory Committee combining the best features of each of the individual proposals. The proposed system supported two scanning formats. The first proposed format had 720 active lines, 1280 pels (picture elements) per active line, and 60 frames per second scanned progressively. The second proposed format used interlaced scanning with 960 active lines and 1408 or 1728 pels per active line. The proposed ultimate target was 960 active lines with 1728 pels per active line scanned progressively at 60 frames per second. The proposed compression algorithm was similar to MPEG-2 with enhancements from each of the original individual systems. A single audio system was not proposed, but was to be selected from among the original individual systems after performing a comparative test. The proposed transport mechanism was packetized and similar to MPEG-2. A single modulation technique was not proposed, but was to be selected from among the original individual systems after performing a comparative test.</p>

<p>The Advisory Committee's Technical Subgroup examined the Grand Alliance proposal. At the request of the Advanced Television Systems Committee (ATSC), the Technical Subgroup suggested that the 960 active line format should be replaced with a 1080 active line format containing 1440 or 1920 pels per active line. The Technical Subgroup suggested also that the video compression algorithm and the transport mechanism should be compatible with MPEG-2. Finally, the Technical Subgroup decided to conduct a paper study of the COFDM (coded orthogonal frequency division multiplex) modulation technique.</p>

<p>At a meeting of the Technical Subgroup on October 21, 1993 the Grand Alliance announced that the 960 active line format would be replaced with a 1080 active line format, that both 60 Hz and 59.94 Hz vertical rates would be supported, that the video compression algorithm would be MPEG-2 (main profile, high level), that MPEG-2 transport mechanism would be used, and that the Dolby AC-3 audio system would be used. The Grand Alliance also announced that a test of three modulation techniques - 4 level VSB (vestigial-sideband), 6 level VSB, and 32 QAM (quadrature amplitude modulation) - would be conducted in January 1994. With the choice of a modulation technique, the Grand Alliance system will be fully specified. Key specifications now include:</p>

<p>Video compression: MPEG-2 main profile, high level <br />
Scanning formats supported: 720 lines x 1280 pels, 60 Hz, progressive scanning </p>

<p>1080 lines x 1920 pels, 60 Hz, interlaced scanning <br />
Film modes: 720 x 1280 at 30 Hz and 24 Hz, progressive scanning <br />
1080 x 1920 at 30 Hz and 24 Hz, progressive scanning <br />
Audio compression: Dolby AC-3 <br />
Transport technique: MPEG-2 </p>

<p><br />
Construction of the prototype is beginning and will continue through the summer of 1994. Laboratory tests are expected to begin in October 1994. Field tests are expected to be conducted in Charlotte, North Carolina in early 1995.</p>

<p><strong>VI. ADOPTION PHASE</strong><br />
In June 1992, the ATSC offered to document the selected ATV system for the FCC. In its filing with the FCC, ATSC noted a number of standards efforts needed and suggested the appropriate bodies to perform the various standardization functions. Those bodies included the founders and Charter Members of the ATSC - Institute of Electrical and Electronics Engineers, Electronic Industries Association, National Association of Broadcasters, National Cable Television Association, and Society of Motion Picture and Television Engineers. ATSC was founded to coordinate the development of voluntary national technical standards for advanced television systems. ATSC's fifty plus members, in addition to its Charter Members, are manufacturers of professional and consumer equipment, broadcasters, cable operators, satellite operators, motion picture companies, and universities.</p>

<p>With the formation of the Grand Alliance, ATSC plans to begin the documentation as the full specifications become available. This action will be concurrent with prototype construction and laboratory and field testing of the system.</p>

<p>Adoption of the system by the FCC could happen the first half of 1995.</p>

<p><strong>ACKNOWLEDGMENT</strong><br />
Much of this paper (section IV - B) was extracted from the ATV System Recommendation report, often quoting directly from the report. As such, it represents the work of a large number of dedicated people. Note that the author of this paper served as the chairman of the working party of the Advisory Committee that wrote the first draft of the report and as the chairman of the "Special Panel" which completed the report.</p>

<p><em>REFERENCES</em><br />
[1] B. Crutchfield, "Broadcasting High Definition Television," 15th International Montreux Television Symposium - Broadcast Sessions Record, pp. 158-165, June 1987.</p>

<p>[2] R. Hopkins and K. P. Davies, "HDTV Emission Systems Approach in North America," ITU Telecommunication Journal, vol. 57, pp. 330-336, May 1990.</p>

<p>[3] "ATV System Recommendation," IEEE Transactions on Broadcasting, volume 39, number 1, pp. 2-245, March 1993.</p>

<p>NOTES<br />
{1} NHK also demonstrated a terrestrial microwave delivery system at the same time.</p>

<p>{2} In this paper, the term ATV includes HDTV. The FCC has defined ATV to include advanced television features ranging from improvements to the current NTSC system to HDTV. Note that only HDTV systems are under consideration by the FCC at this time.</p>

<p>{3} The NTSC signal would be broadcast in its current channel while the additional information needed to complete the ATV signal would be broadcast in a different channel.</p>

<p>{4} The NTSC signal would be broadcast in its current channel while the same program in the ATV signal format would be broadcast in a different channel.</p>

<p>{5} EDTV systems have spatial resolution higher than NTSC but lower than HDTV. EDTV systems may have a wide aspect ratio. HDTV is generally assumed to have twice the spatial resolution of NTSC (horizontal and vertical) and a wide aspect ratio.</p>

<p>{6} The information presented in this section does not necessarily represent the view of the author. The information is extracted from the ATV System Recommendation report, often quoting directly from the report.</p>

<p>{7} In Puerto Rico, the large number of television stations assigned within the limited area of the island precludes the development of a plan providing 100% accommodation by the methodology employed herein. As a result, those stations are not included in the analysis. The comparative analysis attempted to protect all existing noncommercial vacant allotments; however, it did not attempt to assign them an ATV channel.</p>

<p>{8} The UHF taboo channels arise because of interference mechanisms in receivers. For each UHF channel assigned to a given geographic location, 14 prohibited assignments result at that location in addition to the adjacent channels which are prohibited at both UHF and VHF.</p>

<p>{9} The video quality subjective judgments were made using the CCIR Five-Point (five-interval) Continuous Quality Scale with the terms "Excellent", "Good", "Fair", "Poor" and "Bad". This method uses double presentations of reference and test signals in blind, pseudo-random orders. The responses were graded from 0 to 100, where 0-20 corresponds to "Bad", 20-40 to "Poor", 40-60 to "Fair", 60-80 to "Good" and 80-100 to "Excellent". The twenty-three video selections were compared using a t-test with an individual error rate of 5%. Emphasis is placed on describing the size of the differences between the 1125-line reference and the test signal using averages and ranges, rather than on statistical significance.</p>

<p>BIOGRAPHY<br />
Robert Hopkins (Senior Member, IEEE) joined the Advanced Television Systems Committee (ATSC) in January 1985 as the Executive Director. He is responsible for both the technical and administrative guidance of the ATSC. The ATSC is a standards organization sponsored by more than 50 companies involved in HDTV. The ATSC addresses issues concerning advanced television in order to provide private sector input to the Department of State for international conference preparation and to the Federal Communications Commission to assist in their HDTV decision-making process.</p>

<p>Previously, Dr. Hopkins was employed by RCA for twenty years. After several years at the RCA David Sarnoff Research Center, Princeton, NJ, where he was involved in television research, Dr. Hopkins transferred to the RCA Broadcast Systems Division, Gibbsboro, NJ where he held a number of positions including manager of strategic planning and manager, field camera products and engineering.  His most recent position at RCA was managing director of RCA Jersey Limited, Jersey Channel Islands, Great Britain, an overseas subsidiary that manufactured professional television equipment for a worldwide market.</p>

<p>While at RCA, Dr. Hopkins received two Outstanding Achievement Awards from RCA Laboratories.  He is a past chairman of SMPTE's Standards Committee and the Committee on New Technology. He was appointed the first chairman of the SMPTE Working Group on Digital Video Standards in 1977. He is a Fellow of SMPTE and a Senior Member of the IEEE. He serves as the United States representative on HDTV to the CCIR.</p>

<p>He received his Bachelor of Science degree in Electrical Engineering from Purdue University in West Lafayette, Indiana; Master of Science and PhD degrees from Rutgers University, New Brunswick, New Jersey; and is a graduate of the Harvard Business School Program for Management Development.</p>

<p> </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 23, 2005  9:21 PM</b>
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
			<?=getComments(112)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 112)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_dr_robert_hopkins.php" type="text/javascript" charset="utf-8"></script>
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