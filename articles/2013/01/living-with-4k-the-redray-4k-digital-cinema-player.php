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
		AND e.entry_id = 5010";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5010 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5010 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5010";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5010";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K - The REDRAY 4K Digital Cinema Player" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K - The REDRAY 4K Digital Cinema Player" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K - The REDRAY 4K Digital Cinema Player" />
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
	<title>HDTV Magazine - Living with 4K - The REDRAY 4K Digital Cinema Player</title>
	<meta name="keywords" content="redray player, blu ray, next article, collecting movies, disc end, player, redray, red, hdmi, media, review, content, new, digital, blu, ray, bit, audio, sony, movies, output, living, khz, fps, consumer" />
	<meta name="description" content="Yes I said &quot;Redray&quot;, not &quot;Blu-ray&quot; 4K player. This is an update of my &quot;Living with 4K - Part 2 - 4K Content, when?&quot; article.

According to RED DIGITAL CINEMA, this 4K player is expected to be available by early 2013.  I requested a unit for review back in December so this is just an introductory article, which I will continue with a full review when RED sends me a review unit.

I also plan to review the 4K media server Sony lends to new owners of new Sony 4K panels to display some 4K movies until the consumer market can supply it (see further below).  The server is configured as a Dell computer CPU with recorded 4K movies, it comes in a big box with a bunch of accessories, including a tablet, 16-feet HDMI high-speed cable, etc.  I will compare both media players using my 4K Sony projector. 

The Redray player is not..." />
	<meta name="title" content="Living with 4K - The REDRAY 4K Digital Cinema Player" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K - The REDRAY 4K Digital Cinema Player" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Yes I said &quot;Redray&quot;, not &quot;Blu-ray&quot; 4K player. This is an update of my &quot;Living with 4K - Part 2 - 4K Content, when?&quot; article.

According to RED DIGITAL CINEMA, this 4K player is expected to be available by early 2013.  I requested a unit for review back in December so this is just an introductory article, which I will continue with a full review when RED sends me a review unit.

I also plan to review the 4K media server Sony lends to new owners of new Sony 4K panels to display some 4K movies until the consumer market can supply it (see further below).  The server is configured as a Dell computer CPU with recorded 4K movies, it comes in a big box with a bunch of accessories, including a tablet, 16-feet HDMI high-speed cable, etc.  I will compare both media players using my 4K Sony projector. 

The Redray player is not..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5010', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php">Living with 4K - The REDRAY 4K Digital Cinema Player</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 27, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=369&category=4K (Ultra HD)">4K (Ultra HD)</a></b>
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
				<p>Yes I said "Redray", not "Blu-ray" 4K player. This is an update of my "Living with 4K – <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-4k-content-when-part-2.php">Part 2</a> – 4K Content, when?" article.  <p>According to <a href="http://www.redstudio.com/home">RED DIGITAL CINEMA</a>, this 4K player is expected to be available by early 2013.&nbsp; I requested a unit for review back in December so this is just an introductory article, which I will continue with a full review when RED sends me a review unit.<img style="border-right-width: 0px; margin: 10px 15px 10px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="RedRay 4K Player" border="0" alt="RedRay 4K Player" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KTheREDRAY4KDigitalCinemaPlay_1514B/image_3.png" width="339" height="216">  <p>I also plan to review the 4K media server Sony lends to new owners of new Sony 4K panels to display some 4K movies until the consumer market can supply it (see further below). The server is configured as a Dell computer CPU with recorded 4K movies, it comes in a big box with a bunch of accessories, including a tablet, 16-feet HDMI high-speed cable, etc.&nbsp; The server for the 4K projector is different, I will review it and the REDRAY media player using my 4K Sony projector.<p>The Redray player is not a disc player, but rather a 4K movie download and playback player. Toshiba used this Redray player to demo their new Ultra HDTV displays at CES 2013.&nbsp; <p>In addition, a soon to be available Sony’s 4K consumer player (pictured further down, not the computer loaner above) was announced at CES 2013, with a 4K movie service, both to be available by mid 2013, no pricing or details of the 4K content itself were provided.&nbsp; <p>Could these announcements of download players be considered enough signs of a possible end of pre-recorded media with the 4K arrival?  <p>The Blu-ray Association has been giving me that impression at their roundtables.&nbsp; Every time I asked Mr. Parsons, the president of the Association, he responded that they were NOT working on a 4K Blu-ray disc standard, although it is difficult to judge and anticipate the consumer electronics industry (some reasons at the end).&nbsp; But that is the subject of my next article "Living with 4K - No Disc? The end of Collecting Movies".<img style="border-right-width: 0px; margin: 10px 20px 0px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="RED 4K Camera" border="0" alt="RED 4K Camera" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KTheREDRAY4KDigitalCinemaPlay_1514B/image_6.png" width="333" height="211">&nbsp; <p><a href="http://www.red.com/products/redray">Here</a> is the $1,450 Redray player, which realistically its MSRP should be irrelevant to someone that spent $25,000 on a 4K display.&nbsp; I paid as much for my first DVD player back when dinosaurs were around, not to mention my turntables for vinyl records, what is interesting is that turntables are coming back to haunt all the MP3 "audiophiles" of the modern world.  <p>The RED company manufactures the famous Red 4K (actually 5K) video camera for filmmakers at a very reasonable price compared to the alternatives (pictured).&nbsp; <p>The Redray 4K player implements its own compression algorithm in .RED files requiring only 2.5 MB (20 Mbps) for transporting 4K content, which is roughly what HD requires today for broadcasting with MPEG-2 at its full resolution (19+ Mbps).  <p>The content will be supplied by a RED’s distribution platform: <a href="http://odemax.com/information.html">Odemax.com</a>, expected to be up and running by March 2013.&nbsp; The platform provides filmmakers, production companies and independent distributors, direct channel access to the new cloud enabled REDRAY 4K home players.&nbsp; </p> <p>The question is, would one be able to have access to a new movie release from any major studio as well? We will have to see how the service grows.<img style="border-right-width: 0px; margin: 10px 15px 0px 0px; display: inline; border-top-width: 0px; border-bottom-width: 0px; border-left-width: 0px" title="Sony 4K player - Expected for Mid 2013" border="0" alt="Sony 4K player - Expected for Mid 2013" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KTheREDRAY4KDigitalCinemaPlay_1514B/image_8.png" width="336" height="273"></p> <p>The 4K player from Sony (pictured) is expected to let a consumer download 4K movies from Sony studios and partners, which content selection maybe more interesting than Odemax’s independent filmmakers to some, although no doubt others may prefer the exact opposite.  <p>The Redray player outputs 4K DCI but also UltraHD, 1080p and 720p formats with an audio of 24-bit 7.1 channel at 48 kHz, comparably lower than the Blu-ray capacity of 7.1 channels at 96 kHz or 5.1 channels at 192 kHz.  <p>It connects via HDMI 1.4 to the 4K display for video, and it has a separate HDMI 1.3 output to connect to an A/V receiver or preamp for multi-channel audio, as some Blu-ray players already do, such as Oppo.&nbsp; <p>I confirmed with HDMI LLC at CES 2013 that the HDMI specification standard will introduce a new version by the first semester 2013 that as expected will include, among other features, a 60fps frame rate spec for 4K (the current HDMI version1.4 already supports 4K as 24fps and 60i, in addition to 25fps and 50i for other regions of the world).&nbsp; <p><a href="http://www.red.com/products/redray#tech-specs">Here</a> are the technical specs of the REDray player:</p> <p>&nbsp;</p> <p>&nbsp;</p> <p> <table border="1" cellspacing="0" cellpadding="0" width="638"> <tbody> <tr> <td valign="top" width="151"> <p><b>DIMENSIONS</b></p></td> <td valign="top" width="485"> <p>316 × 61mm x 260 mm ( 12.4 × 2.4 × 10.2″ )</p></td></tr> <tr> <td valign="top" width="151"> <p><b>WEIGHT</b></p></td> <td valign="top" width="485"> <p>5.9 Lbs</p></td></tr> <tr> <td valign="top" width="151"> <p><b>MATERIAL</b></p></td> <td valign="top" width="485"> <p>Aluminum</p></td></tr> <tr> <td valign="top" width="151"> <p><b>OPERATING TEMPS</b></p></td> <td valign="top" width="485"> <p>Zero to 40 C</p></td></tr> <tr> <td valign="top" width="151"> <p><b>STORAGE TEMPS</b></p></td> <td valign="top" width="485"> <p>Minus 5 to 60C</p></td></tr> <tr> <td valign="top" width="151"> <p><b>NOTABLE FEATURES</b></p></td> <td valign="top" width="485"> <p>Network based 4K 3D Playback System.</p></td></tr> <tr> <td valign="top" width="151"> <p><b>ADDITIONAL NOTES</b></p></td> <td valign="top" width="485"> <p>Unique to RED, this product is the only available 4K resolution signal source  <p>for Ultra HD flat panel displays and 4K projectors, may also be used for  <p>digital signage applications to drive up to four 1080p displays.</p></td></tr> <tr> <td valign="top" width="151"> <p><b>RESOLUTION</b></p></td> <td valign="top" width="485"> <p>Up to 4096 × 2160 pixels, 2D or 3D</p></td></tr> <tr> <td valign="top" width="151"> <p><b>BIT-DEPTH (COLOR)</b></p></td> <td valign="top" width="485"> <p>YCbCr 12-bit 4:2:2 or RGB 8-bit 4:4:4</p></td></tr> <tr> <td valign="top" width="151"> <p><b>COLORIMETRY</b></p></td> <td valign="top" width="485"> <p>ITU-R BT.709</p></td></tr> <tr> <td valign="top" width="151"> <p><b>PROGRAM OUTPUT</b></p></td> <td valign="top" width="485"> <p>4K DCI, UltraHD, 1080p, 720p</p></td></tr> <tr> <td valign="top" width="151"> <p><b>PREVIEW OUTPUT</b></p></td> <td valign="top" width="485"> <p>1080p, 720p</p></td></tr> <tr> <td valign="top" width="151"> <p><b>MEDIA SECURITY</b></p></td> <td valign="top" width="485"> <p>REDCrypt™ digital media encryption</p></td></tr> <tr> <td valign="top" width="151"> <p><b>DRM OPTIONS</b></p></td> <td valign="top" width="485"> <p>ODEMAX™ digital rights management</p></td></tr> <tr> <td valign="top" width="151"> <p><b>REMOTE CONTROL</b></p></td> <td valign="top" width="485"> <p>IR, 802.11n, Ethernet</p></td></tr> <tr> <td valign="top" width="151"> <p><b>GENLOCK</b></p></td> <td valign="top" width="485"> <p>RS170A Tri-level Sync</p></td></tr> <tr> <td valign="top" width="151"> <p><b>PLAYBACK FRAME RATES</b></p></td> <td valign="top" width="485"> <p>24, 25, 30, 48, 50, 60 fps</p></td></tr> <tr> <td valign="top" width="151"> <p><b>DIGITAL MEDIA</b></p></td> <td valign="top" width="485"> <p>Internet download, SDCard or USB-2 flash media</p></td></tr> <tr> <td valign="top" width="151"> <p><b>VIDEO FILE FORMAT</b></p></td> <td valign="top" width="485"> <p>.RED (4K), .MP4 (1080p, 720p)</p></td></tr> <tr> <td valign="top" width="151"> <p><b>AUDIO FILE FORMAT</b></p></td> <td valign="top" width="485"> <p>.RED (up to 7.1 Ch) .MP4 (Stereo)</p></td></tr> <tr> <td valign="top" width="151"> <p><b>AUDIO OUTPUT</b></p></td> <td valign="top" width="485"> <p>Up to 7.1 channel LPCM, 24-bit 48Khz</p></td></tr> <tr> <td valign="top" width="151"> <p><b>OUTPUT CONNECTORS</b></p></td> <td valign="top" width="485"> <p>4 x HDMI 1.4 (Program), 2 x HDMI 1.3 (Preview and Audio)</p></td></tr> <tr> <td valign="top" width="151"> <p><b>STORAGE CAPACITY</b></p></td> <td valign="top" width="485"> <p>1TB Internal SATA Drive</p></td></tr> <tr> <td valign="top" width="151"> <p><b>POWER</b></p></td> <td valign="top" width="485"> <p>120 – 240V 50 – 60Hz A.C.</p></td></tr></tbody></table></p> <p>&nbsp; <p>When I get the Redray player for review I will cover more of the technical details, I just wanted to give you the heads up of what is coming, especially because many naysayers keep criticizing the need for 4K and use the lack of 4K content as an additional excuse to reject 4K in general, I would not be surprised if they are still pursuing further return of the investment they made on their blinking-12:00 VCRs.  <p><b></b>Stay tuned with my next article "Living with 4K - No disc? The end of Collecting Movies".&nbsp;&nbsp; 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 27, 2013  8:53 PM</b>
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
			<?=getComments(5010)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5010)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php" type="text/javascript" charset="utf-8"></script>
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