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
		AND e.entry_id = 92";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 92 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 92 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 92";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1994_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 92";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch" />
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
	<title>HDTV Magazine - 1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch</title>
	<meta name="keywords" content="grand alliance, multichannel ntsc, advanced television, transition channel, second channel, hdtv, broadcasters, service, spectrum, any, channel, services, fcc, digital, television, ntsc, flexible, cofdm, quality, letter, should, process, broadcasting, markey, flexibility" />
	<meta name="description" content="Broadcasters received an early St. Patrick's Day gift on March 16 when the House Telecommunications Subcommittee passed the markup of the Tauzin Amendment to H.R. 3636 with a unanimous voice vote. The amendment grants broadcasters flexible use of the transition channel for ATV. Subcommittee Chairman, Representative Edward Markey, decried &quot;Only those who created the Communications Act in 1934 have accomplished as much as we achieved here in 1994,&quot; hailing it as the most significant communications policy change in 60 years." />
	<meta name="title" content="1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1994_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Broadcasters received an early St. Patrick's Day gift on March 16 when the House Telecommunications Subcommittee passed the markup of the Tauzin Amendment to H.R. 3636 with a unanimous voice vote. The amendment grants broadcasters flexible use of the transition channel for ATV. Subcommittee Chairman, Representative Edward Markey, decried &quot;Only those who created the Communications Act in 1934 have accomplished as much as we achieved here in 1994,&quot; hailing it as the most significant communications policy change in 60 years." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=92', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1994_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php">1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 17, 2005</b>
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
				<p><em>This article was written for the HDTV Newsletter in 1994 shortly after "multicasting" was added to the list of possibilities for digital television by way of a Congressional provision. </em><br />
_______________________________________________________</p>

<p><strong>Sweet Deal</strong><br />
Broadcasters received an early St. Patrick's Day gift on March 16 when the House Telecommunications Subcommittee passed the markup of the Tauzin Amendment to H.R. 3636 with a unanimous voice vote. The amendment grants broadcasters flexible use of the transition channel for ATV. Subcommittee Chairman, Representative Edward Markey, decried "Only those who created the Communications Act in 1934 have accomplished as much as we achieved here in 1994," hailing it as the most significant communications policy change in 60 years. </p>

<p>Although many people responded to Markey's letter requesting information on the proposal of flexible use, the legislation passed appears to have ignored most of them, as broadcasters received fully flexible use of the spectrum. Additionally, the enforced usage clause, requiring the FCC to establish a minimum number of hours per day during which advanced television services will be required was intentionally written so as not to actually require broadcasting of HDTV. While advanced television services are inclusive of HDTV, it is not considered exclusive.</p>

<p>Some have speculated that referring to occupants of the transition channel as "advanced" and incorporating such improvements as digital audio merely represents avenues by way of which the restrictions of the Ashbacker ruling can be skirted. Furthermore, one could question which is the greater public service: more channels or one improved quality channel. In any case, Ashbacker is not thought to present a threat, according to Joe Flaherty, who also expects H.R. 3636 to now pass the full house with little opposition. </p>

<p>Broadcasters are now poised to move into the digital domain. Peter McCloskey and Gary Shapiro, president and vice president of the Electronic Industries Association (EIA), respectively, tell us that as long as broadcasters move toward HDTV, the EIA will have to support them. </p>

<p><strong>Wiley Letter Encourages Whisperers to Speak Up</strong><br />
Last month, Dick Wiley, Chairman of the Advisory committee on Advanced Television Services, issued a letter which resurrected three major issues commonly thought to be long dead and buried. The three issues, all of which could substantially delay the standardization process, are alternative/flexible use of the second channel, COFDM, and interoperability. We spoke with John Abel (NAB), James Quello (FCC), Bob Rast (General Instrument), Jules Cohen (ACATS PS/WP3, consultant), Julie Barnethon (consultant, former ABC engineer), William Schreiber (MIT), George Vradenburg (Fox), and Tom Stanley (FCC) to develop a comprehensive overview of these issues from a multitude of vantage points within the industry. Numerous unsuccessful attempts were also made to contact FCC Chairman Reed Hundt, with the intention of discovering the direction his leadership may take. </p>

<p>While Wiley's own view, "pending future developments, is that we should stay on the course—that is, continue and complete our work as expeditiously as feasible, proceed to give the FCC a recommendation on a new video standard." However, he recognizes that neither he alone nor any one group collectively can make such a decision, and that because these issues continue to reappear, that more input is necessary to ensure there are no major omissions or cumbersome elements poised to kill HDTV.</p>

<p><strong>FLEXIBLE USE</strong><br />
Although Wiley's letter asked for comments from the industry, it was not until Congressman Ed Markey issued a letter on March 3, seeking information from industry groups, the FCC, and other interested parties regarding a proposal to allow broadcasters flexibility to use their spectrum for ancillary or supplementary services. Respondents were given only one week to reply, but many did, including Chairman Hundt. With the recent developments in the Telecommunications Subcommittee, this issue is now decided, but not to the satisfaction of a great many people.</p>

<p>Many people had suggested—notably Fox and the NAB—of late that the broadcasters should be allowed to use their proposed transition channel for multiple, digital, standard definition channels, data transmission, and other services in addition to or even instead of HDTV. "Dynamic scalability," or the ability to shift between HDTV and lower definition multiple channels or other digital uses, could provide broadcasters significant revenue boosts, and was touted as a way to help underwrite the considerable costs associated with implementing HDTV. Obviously, the most enthusiastic proponents of such an arrangement have been the broadcasters, whose lobbying efforts were largely responsible for the entire discussion. </p>

<p><strong>Broadcasters Answer Call</strong><br />
NAB President and CEO Eddie Fritts, representing NAB, INTV, ABC, CBS, NBC, and Fox, responded to Markey's letter with a very solid, well researched report on the importance of granting broadcasters flexible use of the second channel. The ten page response, addressing all 14 questions posed by the congressman, cited many historical and legal precedents to support their position. </p>

<p><strong>Trust Us</strong><br />
Fritts claimed "broadcasters are committed to maintaining free, universal, over-the-air service" for every assigned channel, and would continue to meet all public service obligations, operating subject to FCC regulations. They believe a policy of flexible use would not undermine the FCC's discretion or authority over ATV, but would merely enable efficiency in the use of the spectrum. Additionally, they responded to the quality vs. quantity issue by stressing "there is no likelihood that broadcasters would reduce the quality of their product," as "reduced picture quality is simply not competitively viable" due to the variety of competitive program providers offering consumers high quality video. Thus, "the marketplace can be relied upon to discipline broadcasters when it comes to video quality. Further, the establishment of any digital transmission system requires the adoption of standards for both transmission and reception." However, the bottom line of broadcasters' quality argument was the fact that they are licensed by the FCC to provide public service, and "if a broadcaster did not continue to provide a full broadcast service throughout its license term, it would face a real possibility of losing its license at renewal." </p>

<p><strong>Assignment vs. Allocation</strong><br />
Broadcasters argued that providing a second channel for transition to ATV would not require any new spectrum allocation, since the UHF band, on which ATV will presumably be provided, was allocated to television broadcasting in 1945. The FCC needs only to assign some of that broadcast spectrum to broadcasters for ATV. This semantic difference could prove quite significant if challenges are made based on the Ashbacker ruling. Ancillary use on that channel, they maintain, "might be critical to successful implementation of ATV in its early stages, when receiver penetration is low," and "if spectrum for that service is not assigned to existing broadcasters and they are not permitted to make the most effective use of that spectrum as ATV service, it is unlikely that any advanced system of over-the-air broadcasting will be implemented, and the goal of providing universal advanced data services to the public would suffer."</p>

<p><strong>Auxiliary vs. Primary</strong><br />
The primary use of broadcast spectrum, they maintained, would still be the provision of free, over-the-air broadcast service, and "as a practical matter, the types of services which could be offered will be limited." Additionally, despite the fact that some of the ancillary services broadcasters may be interested in providing would require the purchase of a converter, the service would not become a subscription service any more than "the requirement that consumers purchase a television set in order to receive current television service" would make it "anything other than a free service." Flexible use of the channel, they argued, is not much different than the current practice of using subcarriers or the vertical blanking interval to provide a variety of services. Similarly, "any non-broadcast services would be in addition to a broadcasters main program service, just as broadcasters currently provide ancillary services on subcarriers or in the VBI."</p>

<p><strong>NII</strong><br />
Another point stressed in the letter was that "any bar on broadcasters offering a particular class of services would seem to be inconsistent with the overall goal of H.R. 3636 of permitting any technologically and economically viable competitive service to be offered by any class of provider." No new spectrum allocation would be needed for this purpose, and "the FCC's rules now allow television stations to provide a variety of telecommunications services in the VBI, including data transmission, teletext, paging, and distribution of computer software." Some of the wireless voice providers have expressed interest in providing video services which would not be subject to restrictions, a move supported by broadcasters who stressed "we do not believe that such restrictions going either way would advance the public interest."</p>

<p><strong>We'll Pay</strong><br />
Finally, the broadcasters "support a requirement that the FCC establish an appropriate fee for broadcasters based on the amounts paid by providers of competitive services which obtained spectrum in auctions." However, there have been no spectrum auctions as yet, and predicting the price for a particular piece of spectrum is not possible, thus the fees to be paid can not yet be determined. What is suggested is that since "the auction process will begin this year, while any new flexible uses under the amendment are probably several years away," the FCC will have ample time to determine appropriate fees. Broadcasters do warn that fees should not be so high as to stifle the beginnings of such businesses.</p>

<p><strong>Give Them The Flexibility, But . . .<br />
</strong>Most of the other respondents to the Markey letter, as well as the individuals we spoke with, advocated allowing the broadcasters the flexibility to use their spectrum in different ways, though under varying conditions and/or restrictions. Chairman Hundt's letter was somewhat ambiguous as to his personal opinion on the matter, though he did state "The digital technology under development is flexible enough to support different communications services in addition to broadcasting," which apparently signified his support.</p>

<p><strong>Early Introduction</strong><br />
Most of those calling for flexible usage of the transition channel did so with the belief that added broadcasting revenue would result in more rapid implementation of full-time HDTV service. James Carnes, President and CEO of the David Sarnoff Research Center noted that "encouraging the development of these digital services is fully consistent with the FCC's authority to set digital transmission standards and regulations," and that spectrum flexibility would incentive to deployment of HDTV, as well as allowing HDTV to become a key element of the NII. Additionally, he did not believe fees should be levied or licenses required for such services. AT&T Senior VP Gerald Lowrie concurred, advising Markey that "any legislation regarding broadcast flexibility should promote the early introduction of HDTV."</p>

<p><strong>No Interference</strong><br />
Dr. Joe Donahue, Senior VP at Thomson Consumer Electronics, responded to Markey's letter by advocating allocation of the new spectrum without a fee so as to preserve free broadcasting. Donahue also favored flexible use of both the NTSC channel and the HDTV channel without fee or license requirements, so long as the ancillary services do not interfere or detract from the television service. However, any such service that reduces the quality of or terminates HDTV service "should be subject to a full fee, if not more, since such use detracts from free over-air television and possibly jeopardizes its future." Zenith CEO Jerry Pearlman proposed that any digital service be subject to the restriction: that it "be interruptible so as not to disrupt the HDTV programming in those limited instances when all the bits are needed to make good pictures."</p>

<p><strong>HDTV Required</strong><br />
Jerry Pearlman, CEO of Zenith, commented on the success and investment of the Grand Alliance to date, and believes a rapid launch of HDTV is critical. However, he stated, "Zenith does see a possible role for flexible use of spectrum, including digital NTSC (D-NTSC), so long as it is part of a clear migration path to HDTV." Zenith believes that D-NTSC would increase broadcast revenue and fund HDTV conversion, but with substantial restrictions, including initial simulcast requirements to stimulate consumer interest, maintaining it as a free service, requiring HDTV during primetime, with established timetables for increasing HDTV airtime, and maintaining technical standards common with HDTV.</p>

<p>Joe Donahue also rejected any consideration of freedom to transmit single or multiple programs of lower quality full time, proposing a minimum requirement of six hours per day of HDTV programming [editor's note:  Current regulations only require two hours per day of NTSC broadcasting]. Finally, Dr. Joe claimed that any amendment considered by Congress must expedite the completion of the ATV process.</p>

<p><strong>Reclamation of Reversion Channel</strong><br />
Lowrie emphasized AT&T's support for the FCC plan to assign the conversion channel temporarily, and that "the FCC should reclaim the original reversion channel for other purposes at the end of a brief (15 year) conversion process," reinforcing the idea that the second channel is "warranted only as a transitional means for upgrading the quality of television service to HDTV," and that "no broadcaster should automatically receive an additional channel except in transition to implement HDTV," with all spectrum for other purposes assigned by auction.</p>

<p><strong>HDTV Not a Priority</strong><br />
Some advocates of flexibility actually favor abandoning HDTV completely, opting instead to multiplex 6 digital channels, each of lower resolution than current analog NTSC. Perhaps most notable of such people is Fox boss Rupert Murdoch, who claims that developments in NTSC have made the difference between it and HDTV minimal. However, Wiley pointed out that if HDTV is not ultimately pursued, the question arises as to whether the FCC would—or legally could under the Ashbacker principle—still grant broadcasters additional broadcast channels of spectrum, or instead offer it to other parties interested in providing services.</p>

<p><strong>Flexibility May Kill HDTV</strong>Several individuals expressed their opposition to broadcasting flexibility, either to us or to Ed Markey. Bob Rast, VP HDTV Development at General Instrument, in his letter to Markey, very forcefully stated that "spectrum should not be made available for any purpose other than high-definition television except on the basis of comparative licensing or auctions." He claimed that the only practical, immediate alternate use is multichannel NTSC, and that allowing it would subsidize broadcasters and create an unfair competitive advantage. Additionally, because digital multichannel NTSC can not coexist with HDTV in a new channel, Rast believes that the entire transition would be jeopardized and that "the spectrum recapture would be delayed, and likely threatened." </p>

<p>Dr. Jae Lim, Professor at MIT, also opposed allowing multichannel NTSC broadcasting, citing potential delays in the introduction of HDTV service, as broadcasters would have little incentive to invest in it, due to the fact that "they can send more programs within a single channel and there is a very large installed based of NTSC receivers that can be adapted to receive multichannel NTSC at a relatively small cost." Furthermore, Lim predicted that any introduction of multichannel NTSC would make interlaced scanning a permanent fixture in the new service, a result contrary to Grand Alliance intentions. </p>

<p><strong>COFDM</strong><br />
Although the VSB transmission subsystem was chosen over the QAM technique by the Grand Alliance, following extensive testing at the Advanced Television Test Center, the feline transmission technique known as coded orthogonal frequency multiplexing (COFDM) has demonstrated yet another of its proverbial nine lives. In a notable about-face from his earlier attempts to squelch any and all discussions of COFDM by the likes of PBS's Howard Miller, Wiley now says he "would welcome any views that you may have on this subject and its possible impact on our work." </p>

<p>Europe is involved in the development of COFDM, and the Advisory Committee sent over a technical study group to review their progress. However, because European channelization is mostly 8 MHz, there is no work being done on 6 MHz systems. Initiating such development would take an estimated 9-15 months at a cost of $7-8 million. The Broadcasters Caucus of the Advanced Television Systems Committee (ATSC) has now expressed interest in such development, as they believe COFDM could have advantages over both VSB and QAM, and plan to formulate technical specifications. The potential problem which accompanies such a plan is that development would not be likely to coincide with the Advisory Committee's timetable for testing the completed Grand Alliance prototype hardware, presenting the possibility that the Committee might be asked to substantially delay its process. The concerns which arise include how the testing facilities would be maintained in the interim, and how COFDM would affect cable interests, since developing a standard acceptable to both broadcasting and cable has been a primary concern since the outset in 1987. Although Wiley notes that no decision is immediately necessary, he suggests the importance of determining COFDM's potential merits and drawbacks before disrupting the current schedule. </p>

<p>Dr. Tom Stanley, Chief Engineer at the FCC, marvels at the success of the ACATS process to date, describing it as rational and orderly, and opposes straying from decisions which have already been made. He believes "the hard part is to stop talking and start to putting together the hardware. . . I hope that [final decision process] is not delayed or complicated by the COFDM or the issues of multiplexing. I think those are great questions that must be explored, but I think we should at least continue finishing this "A" (HDTV) quality signal in 6 MHz, which is computer compatible and can be impressed on a digital carrier that blankets a city in some fashion, yet-to-be-established. Retired MIT professor William Schreiber takes an opposite stance on this issue, noting "I gave up long ago the idea that this is a rational process. It isn't. It is a crazy process. Most of the statements that people make are not technical arguments. They are self serving statements which try to make you believe that their company's own perceived interests are the interests of the country at large or even the television industry at large." Schreiber recalls a 1992 submission he made to the FCC regarding the merits of COFDM. "My submission was really attacked by many of the other commentors," but "in this report, John Henderson's committee fully substantiates every single thing I said in 1992." When asked if those merits justify delays in development of a year and a half, he replied "there is no grass roots demand for HDTV, and I don't know what the hurry is. The experience of the inquiries of 1987 clearly shows that undue haste simply delays the process. In the last four years, the dates of making a decision has slipped about three years, and that is primarily because not nearly enough time was allowed."</p>

<p>Fox's George Vradenburg, serving as Rupert Murdoch's spokesman, offered us their company's line while winging his way west. "I think there is some promise there [with COFDM] and I think it has to be pursued. There's certainly some promise coming out of Europe, Japan and Canada. All of those areas of the world are studying it with some intensity and vigor, and I think it would be irresponsible of us to not fully exhaust that. I'd hate to be in the position in two or three years that Japan finds itself in today with its transmission system—that we've overlooked something, and find that we've adopted a VSB system that's obsolete." However, he also claimed "We can't waste any time here. ACATS has got a schedule for the completion of this work, and I think the study of COFDM has got to fit within that schedule. If we continually delay the ACATS schedule to look at the newest girl that comes down the block, we'd never finish. So, I think we've got to look at COFDM inside the ACATS schedule." </p>

<p><strong>INTEROPERABILITY</strong><br />
The testing of the four HDTV systems which were combined in the Grand Alliance system, according to Wiley, "revealed that a high-line number interlaced scanning system produced better television pictures than lower-line number progressive scanning systems." As we have monitored previously, a number of critics have charged that the Grand Alliance inclusion of interlaced scanning impedes interoperability with other imaging formats like computers, while others contend that "HDTV represents the initial introduction of a digital bit stream into the American home to which other advanced (National Information Infrastructure-type) applications could be added." </p>

<p>Of those we spoke to, only George Vradenburg commented on interoperability, and only then after being specifically questioned. Vradenburg believes that anyone who is uncomfortable with the inclusion of interlace simply does not trust the marketplace to make the best choice. He notes that both the transport and the transmission system inside the Grand Alliance will support either format, but that in the long run the marketplace is going to pick progressive. Asked how a company should deal with such distrust, he responds "If you are a computer maker, you need to go out there with good, inexpensive, progressive-line systems, and demonstrate their superiority and quality and price. Then, people are going to buy them."</p>

<p>Interestingly, in arguing against allowing flexible use of broadcasting spectrum above in his letter to Markey, Dr. Jae Lim made an applicable comment. Noting the Grand Alliance's intention to ultimately phase out interlace once a 1000+ line progressive system is developed, he worries that it will be a permanent fixture due to the presence of multiplexed digital NTSC service.</p>

<p>The old guard of such "progressive-only" warriors as Gary Demos, et al, are conspicuously absent from current discussions, causing one to recall their promise to press their point throughout the entire process. Considering the fact that there are still three or four more notices and reports to be issued by the FCC before the matter is concluded, chances are that they will return to fight another day. Thus the entire issue appears to be somewhat on hold, perhaps simply overshadowed by more popular issues such as flexibility. _Dale Cripps, 1994</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 17, 2005  7:49 PM</b>
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
			<?=getComments(92)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 92)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1994_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php" type="text/javascript" charset="utf-8"></script>
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