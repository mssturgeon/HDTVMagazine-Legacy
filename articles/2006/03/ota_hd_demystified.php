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
		AND e.entry_id = 343";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ben Drawbaugh" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 343 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ben Drawbaugh'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ben Drawbaugh" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 343 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 343";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/03/ota-hd-demystified.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 343";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download OTA HD Demystified" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="OTA HD Demystified" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="OTA HD Demystified" />
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
	<title>HDTV Magazine - OTA HD Demystified</title>
	<meta name="keywords" content="signal strength, want receive, uhf channel, channel channel, join tenna, channel, antenna, channels, want, hdtv, check, ota, antennas, see, direction, signal, uhf, area, receive, used, dtv, buy, stations, choose, antennaweb" />
	<meta name="description" content="Everyone knows that it's possible to watch TV with an antenna, but most people today don't understand why anyone would want to. We have all read the horror stories about how difficult it can be to receive a good OTA (Over the Air) signal, especially with DTV. There are a few benefits to OTA today that we didn't have before the US started the DTV transition. Some of the best picture quality possible can be obtained with an antenna, at least until High Definition DVDs are released. It's FREE, it's recordable on some computers like Windows Media Center Edition and it works sometimes when cable and Satellite doesn't." />
	<meta name="title" content="OTA HD Demystified" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="OTA HD Demystified" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/03/ota-hd-demystified.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Everyone knows that it's possible to watch TV with an antenna, but most people today don't understand why anyone would want to. We have all read the horror stories about how difficult it can be to receive a good OTA (Over the Air) signal, especially with DTV. There are a few benefits to OTA today that we didn't have before the US started the DTV transition. Some of the best picture quality possible can be obtained with an antenna, at least until High Definition DVDs are released. It's FREE, it's recordable on some computers like Windows Media Center Edition and it works sometimes when cable and Satellite doesn't." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=343', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/03/ota-hd-demystified.php">OTA HD Demystified</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ben Drawbaugh</b> on <b>March  1, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=3&category=Programming">Programming</a></b>
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
				<div align="center">
	<a href="http://www.hdbeat.com"><img src="/images/hdbeat.gif" alt="HDBeat.com"></a><br />
	<b>Re-published courtesy of HDBeat.com</b><br />
	<a href="http://www.hdbeat.com/2006/01/30/ota-hd-demystified/">http://www.hdbeat.com/2006/01/30/ota-hd-demystified/</a>
</div>
<br />

<p><img vspace="4" hspace="16" border="0" align="right" src="http://www.hdbeat.com/media/2006/01/SR15_large.jpg" alt="SR15UHF only antenna" />Everyone knows that it's possible to <a href="http://hdbeat.com/2005/07/19/hdtv-antenna-help/">watch TV with an antenna</a>, but most people today don't understand why anyone would want to. We have all read the <a href="http://www.hdbeat.com/2006/01/17/atsc-is-great-when-it-works/">horror stories</a> about how difficult it can be to receive a good OTA (Over the Air) signal, especially with DTV. There are a few benefits to OTA today that we didn't have before the US started the <a href="http://www.tvtechnology.com/features/regulatory-review/f-bu-dtv.shtml">DTV transition</a>. Some of the best picture quality possible can be obtained with an antenna, at least until High Definition DVDs are released. It's FREE, it's recordable on some computers like <a href="http://hdbeat.com/category/windows-media-center/">Windows Media Center Edition</a> and it works sometimes when cable and Satellite doesn't.</p>

<p><a href="http://www.hdbeat.com/2006/01/28/ota-hd-demystified/"><br />
</a><a href="http://www.hdbeat.com/media/2006/01/antennaweb_big.jpg"><img vspace="4" hspace="16" border="0" align="right" src="http://www.hdbeat.com/media/2006/01/antennaweb_small.jpg" alt="Street Level Map" /></a><br />
The first step to OTA nirvana is to research your area. Find out which stations are transmitting DTV and where the towers are located by using <a href="http://antennaweb.org/aw/Address.aspx">AntennaWeb</a>,&nbsp; the foremost authority for OTA information. You simply submit your zip code and the website will return a list of all the channels in your area. It will also list where the towers are and how far you are from them. In addition, they also provide a recommended antenna type to help you choose the correct antenna. Your success will depend on where you live and your surroundings, so this is an important step. You can also see a street level map that will help you get an idea of the tower's location, for the directionally challenged. For those who need even more help, you can head over to <a href="http://www.hdtvmagazine.com/programming/broadcast.php">HDTV Magazine</a> and use their Google maps version which provides even more information. Plus - it's fun! </p>

<p>Not all the DTV stations are HDTV. Some of the stations don't choose to broadcast HDTV. Yeah I know it is crazy, but true. They might choose to multi-cast SD digital channels.One station in my area actually broadcasts 4 channels which are listed on my TV as 16.1 16.2 16.3 16.4. When the station chooses to do both HDTV and multicast it usually causes degradation in picture quality, but that is another discussion.<br />
<a href="http://www.hdbeat.com/media/2006/01/titan_big.jpg"><img vspace="4" hspace="16" border="0" align="right" src="http://www.hdbeat.com/media/2006/01/titan_small.jpg" alt="TitanTV channel guide" /></a><br />
If you want to see what HDTV programming is available in your area you can check out <a href="http://ww1.titantv.com/">TitanTV</a>. They have a program guide that makes it easy to see what OTA HDTV is available . You have to sign up to use it but it's worth it. Once you login, you can go to the TV Listings section and choose &quot;Digital Guide (Antenna)&quot; to seethe local line up. All the HDTV programs are clearly marked, but not always 100% accurate. Check in the evening since that is when most HDTV shows are on. You can also check out local <a href="http://www.avsforum.com/avs-vb/forumdisplay.php?f=45">HDTV forums</a> for your area and see what other people are doing. </p>

<p>Now that you know where your stations are and what you can watch, it's time to find the right antenna. As you might guess, outdoor antennas are better and should be used if possible. As a compromise you can mount an antenna in your attic. There are a few indoor antennas that work well, but to obtain the perfect reception we want, we are going to use an outdoor antenna. Now if you study the information we obtained from AntennaWeb you will see that in my area not all the channels' towers are in the same direction. The channels I want to receive are 3, 8, 10, 13, 28,38 and 44. These are the channels with HD content that I want to watch. Lucky for me all of the channels except channel 10, are 14 miles away and in the same direction. Channel 10 is in the opposite direction and 25 miles away. That will be more difficult but we can address that with some additional equipment. The other important thing to notice about the information from AntennaWeb is that the digital channels are actually on different channels than they say they are. For example channel 10 is really channel 24. Thanks to <a href="http://www.highdefinitionblog.com/?p=126">PSIP</a> it will show up on your TV as 10 but in reality it's channel 24. This is important because a different antenna is needed for channel 10 than for channel 24. This is because channel 10 is a <a href="http://en.wikipedia.org/wiki/VHF">VHF</a> channel and channel 24 is a <a href="http://en.wikipedia.org/wiki/Ultra_high_frequency">UHF</a> channel. <br />
<a href="http://www.hdbeat.com/media/2006/01/antennas_big.jpg"><img vspace="4" hspace="16" border="0" align="right" src="http://www.hdbeat.com/media/2006/01/antennas_small.jpg" alt="Antennas" /></a><br />
Since I want to receive 2 VHF channels and 4 UHF channel from the southeast I will use a <a href="http://antennasdirect.com/V15_vhf_antenna.html">VHF/UHF combo antenna</a> pointed in that direction. I also want to receive one UHF channel from the northwest so I will buy a separate <a href="http://antennasdirect.com/SR15_HDTV_Antenna.html">UHF only antenna</a> and point it northwest. I originally tried it with just one antenna but my reception of channel 10 was not perfect so I added the second one. I want to combine the two antennas so I only have to run one coax cable to my TV. If I used a regular combiner it could cause <a href="http://users.ece.gatech.edu/%7Emai/tutorial_multipath.htm">multi-path</a>, which would hinder my reception. I could use a rotor or an A/B switch but those don't work well with TiVos since it can't control the switch or rotor. So I am going to use a <a href="http://www.channelmaster.com/pages/TVS/Passives.htm">JOIN-TENNA</a> from Channel Master.They make them for every channel so you have to order the right one. I bought one for channel 24.</p>

<p>You can buy an antenna locally or if you are like me and like to buy things on-line you can check out <a href="http://antennasdirect.com">Antennas Direct</a>. They have great products and service, they will even help you pick out an antenna. Just email them and let them know your surroundings. You might also want to buy some miscellaneous supplies like a pole and wire to connect everything. You can pay someone to do this too but what fun would that be? <br/><a href="http://www.hdbeat.com/media/2006/01/pole_big.jpg"><img vspace="4" hspace="16" border="0" align="right" alt="Pole with two antennas" src="http://www.hdbeat.com/media/2006/01/pole.jpg" /></a><br />
I mounted both my antennas on the same pole along with my JOIN-TENNA. I connected it all and used a compass to point the antennas in the appropriate direction. The exact channel direction of the tower is available on AntennaWeb. I adjusted my UHF only antenna by angling it upwards a little to improve the signal strength. After you point the antennas it is a good idea to check your signal strength with a meter in order to fine tune them. Most DTV tuners include a signal strength meter, but not all. So if yours doesn't have this feature then it will be more work. You have to check each channel to see if you can receive it and then make adjustments as needed. After each adjustment, check the other channels to make sure you don't mess the other ones up.</p>

<p>With my setup I receive all the channels absolutely perfect. No monthly fees, no drop outs! My total cost was about $200, plus my time. It was well worth it! I have this signal split 5 ways without a problem. Amplifiers can be used if you experience signal loss from splitting or excessively long coax runs.But remember the amplifier is used to overcome attenuation from passive devices and the length of the cable, not antenna problems. </p>

<p>Feel free to post any questions in the comments and good luck!</p>

<p><a href="http://www.hdbeat.com/media/2006/01/sr15_big.jpg"><img vspace="4" hspace="16" border="0" align="right" src="http://www.hdbeat.com/media/2006/01/sr15_small.jpg" alt="SR15 UHF Only antenna." /></a><a href="http://www.hdbeat.com/media/2006/01/jointenna_big.jpg"><img vspace="4" hspace="16" border="0" align="bottom" src="http://www.hdbeat.com/media/2006/01/jointenna_small.jpg" alt="Jointenna" /></a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ben Drawbaugh</b>, <b>March  1, 2006  8:12 AM</b>
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
			<?=getComments(343)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Ben Drawbaugh', 343)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ben Drawbaugh</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/03/ota-hd-demystified.php" type="text/javascript" charset="utf-8"></script>
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