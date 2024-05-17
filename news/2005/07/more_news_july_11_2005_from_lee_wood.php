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
		AND e.entry_id = 151";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Lee Wood" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 151 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Lee Wood'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Lee Wood" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 151 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 151";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2005/07/more-news-july-11-2005-from-lee-wood.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 151";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download More News July 11, 2005 from Lee Wood" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="More News July 11, 2005 from Lee Wood" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="More News July 11, 2005 from Lee Wood" />
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
	<title>HDTV Magazine - More News July 11, 2005 from Lee Wood</title>
	<meta name="keywords" content="digital television, senate gov, commerce senate, president ceo, information display, display, digital, senate, sid, commerce, september, television, news, displays, edinburgh, eurodisplay, president, tuesday, international, association, committee, july, novel, scotland, conference" />
	<meta name="description" content="538 Days Until the Scheduled End of Analog Television Broadcasting TV STATIONS IN OPERATION - 1508 Stations in 211 Markets Delivering in Digital http://www.nab.org/newsroom/issues/digitaltv/dtvstations.asp Digital Television Transition -- Hearing I -- Tuesday, July 12 2005 - 10:00 AM (EDT) Senate..." />
	<meta name="title" content="More News July 11, 2005 from Lee Wood" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="More News July 11, 2005 from Lee Wood" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2005/07/more-news-july-11-2005-from-lee-wood.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="538 Days Until the Scheduled End of Analog Television Broadcasting TV STATIONS IN OPERATION - 1508 Stations in 211 Markets Delivering in Digital http://www.nab.org/newsroom/issues/digitaltv/dtvstations.asp Digital Television Transition -- Hearing I -- Tuesday, July 12 2005 - 10:00 AM (EDT) Senate..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=151', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/07/more-news-july-11-2005-from-lee-wood.php">More News July 11, 2005 from Lee Wood</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Lee Wood</b> on <b>July 11, 2005</b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><strong>538 Days Until the Scheduled End of Analog Television Broadcasting</strong><br />
 <br />
TV STATIONS IN OPERATION - 1508 Stations in 211 Markets Delivering in Digital<br />
<a href="http://www.nab.org/newsroom/issues/digitaltv/dtvstations.asp">http://www.nab.org/newsroom/issues/digitaltv/dtvstations.asp</a><br />
 <br />
<strong>Digital Television Transition -- Hearing I -- Tuesday, July 12 2005 - 10:00 AM (EDT)</strong><br />
Senate Commerce Committee Chairman Ted Stevens (R-Alaska) and Dan Inouye (D-Hawaii) have announced two Full Committee hearings on Tuesday, July 12, at 10:00 a.m. and 2:30 p.m in room 253 of the Russell Building to examine issues concerning Digital Television (DTV) transition. Witnesses are: Mr. Edward Fritts, President and CEO, National Association of Broadcasters; Mr. Manuel Abud, Vice President & General Manager, KVEA-TV in Los Angeles (Telemundo); Mr. Kyle McSlarrow; President & CEO, National Cable & Telecommunications Association; Mr. Patrick Knorr; Vice Chairman, American Cable Association; Mr. Richard Slenker, Executive Vice President, DirecTV; Mr. John M. Lawson, President and CEO, Association of Public Television Stations</p>

<p>(U. S. Senate Committee on Commerce, Science, and Transportation)<br />
<a href="http://commerce.senate.gov/hearings/witnesslist.cfm?id=1567">http://commerce.senate.gov/hearings/witnesslist.cfm?id=1567</a><br />
 <br />
<strong>Live Webcast  [Requires RealAudio]</strong><br />
Digital Television Transition -- Hearing I -- Tuesday, July 12 2005 - 10:00 AM (EDT)</p>

<p>(U. S. Senate Committee on Commerce, Science, and Transportation)<br />
<a href="http://commerce.senate.gov/live.ram">http://commerce.senate.gov/live.ram">http://commerce.senate.gov/live.ram">http://commerce.senate.gov/live.ram</a></p>

<p><strong>Senate turns eye to digital TV, music licensing</strong><br />
(Hollywood Reporter via Reuters / Washington, DC Post)<br />
<a href="http://today.reuters.com/news/newsarticle.aspx?type=entertainmentNews&storyid=2005-07-11T093904Z_01_N11455760_RTRIDST_0_ENTERTAINMENT-MEDIA-CONGRESS-DC.XML">http://today.reuters.com/news/newsarticle.aspx?type=entertainmentNews&storyid=2005-07-11T093904Z_01_N11455760_RTRIDST_0_ENTERTAINMENT-MEDIA-CONGRESS-DC.XML</a></p>

<p><a href="http://www.washingtonpost.com/wp-dyn/content/article/2005/07/11/AR2005071100169.html"><br />
http://www.washingtonpost.com/wp-dyn/content/article/2005/07/11/AR2005071100169.html</a><br />
 <br />
<strong>Congressional Push for DTV</strong><br />
Gary Shapiro, president and CEO of the Consumer Electronics Association testifies before the Senate Commerce Committee next week.<br />
(DesignTechnica)<br />
<a href="http://news.designtechnica.com/article7811.html">http://news.designtechnica.com/article7811.html</a><br />
 <br />
<strong>20050702 Schubin's Saturday Stuff (Mark's Monday Memo)</strong><br />
(Digital Television)</p>

<p><a href="http://www.digitaltelevision.com/mondaymemo/mlist/frm02190.html">http://www.digitaltelevision.com/mondaymemo/mlist/frm02190.html</a></p>

<p><strong>Terrestrial boxes set for lift-off</strong><br />
The retail value of Digital Terrestrial TV (DTT) set-tops will reach beyond $10 billion by 2009, according to a new study from In-Stat.</p>

<p>(CED Magazine)</p>

<p><a href="http://www.cedmagazine.com/cedailydirect/2005/0705/cedaily050708.htm#5">http://www.cedmagazine.com/cedailydirect/2005/0705/cedaily050708.htm#5</a><br />
 <br />
<strong>Time to get ready, set for digital television?</strong><br />
(Washington, DC Post via Seattle, WA Times)</p>

<p><a href="http://seattletimes.nwsource.com/html/businesstechnology/2002370091_ptdigitaltv09.html?syndication=rss">http://seattletimes.nwsource.com/html/businesstechnology/2002370091_ptdigitaltv09.html?syndication=rss</a><br />
<a href="http://seattletimes.nwsource.com/html/businesstechnology/2002370091_ptdigitaltv09.html?syndication=rss">http://seattletimes.nwsource.com/html/businesstechnology/2002370091_ptdigitaltv09.html?syndication=rss</a> </p>

<p><strong>Rear-projection advance is hot TV ticket</strong></p>

<p>The Digital Light Processor rockets to the lead in picture clarity and detail, luring TV makers and buyers<br />
(Portland, Or Oregonian)<br />
<a href="http://www.oregonlive.com/living/oregonian/index.ssf?/base/living/1120750776124940.xml&coll=7">http://www.oregonlive.com/living/oregonian/index.ssf?/base/living/1120750776124940.xml&coll=7</a><br />
 <br />
<strong>Optoma Introduces Its Latest Home Theater Projector Featuring The DarkChip 3&trade; DLP Chipset</strong><br />
(Widescreen Review)<br />
<a href="http://www.widescreenreview.com/news_detail.php?recid=10089">http://www.widescreenreview.com/news_detail.php?recid=10089</a><br />
 <br />
<strong>'European complaints could delay DTT'  [UK]</strong><br />
(Digital TV Group)</p>

<p><a href="http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=1000">http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=1000</a><br />
 <br />
<strong>Belarus: Switch to Digital TV Broadcasting Set for 2015</strong>  [Belarus]</p>

<p>(Radio Netherlands via RedNova)</p>

<p><a href="http://www.rednova.com/news/display/?id=168278&source=r_technology">http://www.rednova.com/news/display/?id=168278&source=r_technology</a></p>

<p><strong>EuroDisplay 2005</strong><br />
<em>19-22 September, Edinburgh, Scotland</em></p>

<p><strong>Enlightenment on Display</strong>What better place to host the premier display research conference than Edinburgh, Scotland, long-known as the "Centre of the Enlightenment?" Join key display industry players from around the globe for this important event, covering all aspects of display-related science and technology, from fundamentals to advanced developments in established as well as novel display fields.</p>

<p>With its unique Medieval and neoclassical charms, beautiful Edinburgh, the Scottish capital and a World Heritage Site, is one of the world's top tourist destinations. Edinburgh is also within easy reach of some of the world's most famous and challenging golf courses, as well as many of Scotland's famous whisky distilleries.</p>

<p><strong>EuroDisplay 2005 </strong><br />
<a href="http://www.sid.org/conf/eurodisplay2005/eurodisplay2005.html">http://www.sid.org/conf/eurodisplay2005/eurodisplay2005.html</a>, the <strong><em>25th International Display Research Conference </em></strong>(IDRC), will take place from Monday, 19 September through Thursday, 22 September at the beautiful Edinburgh International Conference Center <a href="http://www.eicc.co.uk/content/">http://www.eicc.co.uk/content/</a> in Edinburgh, Scotland, U.K.</p>

<p>Future Display Development Workshop<br />
Monday, 19 September<br />
- Displays for Mobile Phones<br />
- OLEDs<br />
- Displays for Large-Screen TVs<br />
- Organic Electronics</p>

<p><strong>Technical Conference</strong><br />
Tuesday, 20 September to Thursday 22 September<br />
- OLEDs<br />
- 3-D Displays<br />
- Components<br />
- TFTs on Flexible Substrates<br />
- Plasma Displays<br />
- Human Factors<br />
- Novel LCDs<br />
- Projection<br />
- Paper-Like and Flexible Displays<br />
- FEDs and Addressing<br />
- Poly-Si TFTs<br />
- LCD Novel Fabrication and Alignment<br />
- Integrated Systems<br />
- Novel Technologies</p>

<p><em>Poster Session: Featuring 74 Additional Papers</em><br />
Keynote Speaker: Professor Sir Richard Friend of Cambridge University, U.K., one of the pioneers of organic-based displays.</p>

<p><strong>Exhibition</strong><br />
Tuesday, 20 September to Thursday 22 September<br />
Leading electronic information-display companies from around the world will showcase their latest products to a prestigious international audience. If you are interested in exhibiting, please contact Kate Dickie, Exhibit Sales Manager, +1-212-460-8090 ext. 215, e-mail: Kate@sid.org.</p>

<p><em>Click here </em>(http://www.sid.org/conf/eurodisplay2005/eurodisplay2005.html) for full program and registration information.</p>

<p><strong>ABOUT SID</strong>The Society for Information Display (SID) is the premier international not-for-profit society exclusively devoted to the advancement of electronic-display technology, manufacturing, and applications. Its international headquarters are located at 610 South Second Street, San Jose, CA 95112, U.S.A. Visit SID online at www.sid.org.</p>

<p>Society for Information Display<br />
610 S. 2nd Street, San Jose, CA 95112<br />
Tel: (408) 977-1013 Fax: (408) 977-1531 email: office@sid.org website: www.sid.org</p>

<p> <br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Lee Wood</b>, <b>July 11, 2005  8:41 AM</b>
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
			<?=getComments(151)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Lee Wood', 151)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Lee Wood</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/07/more-news-july-11-2005-from-lee-wood.php" type="text/javascript" charset="utf-8"></script>
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