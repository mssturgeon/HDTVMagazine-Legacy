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
		AND e.entry_id = 5066";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5066 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5066 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5066";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2013/03/living-with-4k-here-is-the-4k-content.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5066";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K - Here is the 4K Content" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K - Here is the 4K Content" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K - Here is the 4K Content" />
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
	<title>HDTV Magazine - Living with 4K - Here is the 4K Content</title>
	<meta name="keywords" content="image quality, blu ray, server panel, full length, audio video, sony, content, server, video, projector, quality, panel, image, clips, demo, audio, hdmi, camera, clip, viewing, using, servers, light, even, color" />
	<meta name="description" content="As I mentioned in this article Sony announced at CES 2013 the near future availability of a 4K player and a 4K content distribution service, expected by mid 2013.

People that have a 4K display today will have to wait for 4K content to arrive in some form to show the potential of their 4K panel/projector, or use the TimeScapes nature video and suitable computer equipment (such as RAID hard drives and 4K video cards).

There are currently two Sony 4K servers that are used for demos..." />
	<meta name="title" content="Living with 4K - Here is the 4K Content" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K - Here is the 4K Content" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2013/03/living-with-4k-here-is-the-4k-content.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As I mentioned in this article Sony announced at CES 2013 the near future availability of a 4K player and a 4K content distribution service, expected by mid 2013.

People that have a 4K display today will have to wait for 4K content to arrive in some form to show the potential of their 4K panel/projector, or use the TimeScapes nature video and suitable computer equipment (such as RAID hard drives and 4K video cards).

There are currently two Sony 4K servers that are used for demos..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5066', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2013/03/living-with-4k-here-is-the-4k-content.php">Living with 4K - Here is the 4K Content</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>March 10, 2013</b>
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
				<p>As I mentioned in <a href="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-no-disc-the-end-of-collecting-movies.php" target="_blank">this</a> article Sony announced at CES 2013 the near future availability of a 4K player and a 4K content distribution service, expected by mid 2013.  <p><img alt="Near future Sony 4K media player" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image002_3.gif" width="273" height="221"></p> <p>People that have a 4K display today will have to <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-4k-content-when-part-2.php" target="_blank">wait for 4K content</a> to arrive in some form to <a href="http://www.hdtvmagazine.com/articles/2013/02/living-with-4k-bluray-association-evaluating-adding-4k-how-to-see-4k-now.php" target="_blank">show</a> the potential of their 4K panel/<a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-getting-the-beautiful-monster-part-1.php" target="_blank">projector</a>, or use the <a href="http://timescapes.org/4k/Default.aspx" target="_blank">TimeScapes</a> nature video and suitable computer equipment (such as RAID hard drives and 4K video cards).  <p>There are currently two Sony 4K servers that are used for demos, and neither is available for sale.  <p>One is used to demo the <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-getting-the-beautiful-monster-part-1.php" target="_blank">Sony 4K projector</a> on industry shows such as CES, and the other is a more consumer friendly server that Sony lends to the owners of the recently released Sony 4K panels (84" LCDs, $25,000 MSRP).  <p>Ironically, the 4K server for panels is not made available to Sony 4K projector owners, the early adopters of consumer 4K, and I hope Sony reconsiders that approach.  <p>Until now the maximum video quality I fed to the projector was 1080p Blu-ray, which was upscaled to 4K by the projector's Reality Creation video engine.  <p>The situation of limited <img alt="Travel box for CPU, keyboard, mouse, wires of Sony HP 4K server" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image006_3.jpg" width="228" height="310">availability of 4K content for consumers is expected to change hopefully soon when a) <a href="http://www.hdtvmagazine.com/articles/2013/02/living-with-4k-bluray-association-evaluating-adding-4k-how-to-see-4k-now.php" target="_blank">pre-recorded 4K physical media</a> is made available, b) near future consumer players for 4K downloads promised by <a href="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php" target="_blank">Sony and RedRay</a> be actually released later this year, and c) distribution services such as satellite <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-4k-content-when-part-2.php" target="_blank">start</a> offering 4K channels.<img alt="Travel box for monitor of Sony 4K HP server" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image008_3.gif" width="231" height="258">  <p>Recently I had the opportunity to experiment with the content of one of the 3 servers Sony uses to demo their 4K projector at trade shows, such as CES, CEDIA, etc. My purpose was to review the quality of 4K content together with my Sony's 4K projector, displaying the image that the projector was designed for.  <p>The 4K content recorded in this server has grown to almost 2hrs of 4K clips and trailers, including a 48-minute 4K nature video (<a href="http://timescapes.org/products/default.aspx" target="_blank">TimeScapes</a>).  <p>Some content shows the stunning quality I have seen and expect from 4K, but other 4K content did not look much different than 1080p Blu-ray (details at the end).  <p>I was hoping to also receive a review unit of the <a href="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php" target="_blank">RedRay 4K player</a> at around the same time of this Sony 4K server to compare image quality and different compression algorithms, but RED did not confirm the planned date yet.  <p>The Sony 4K server for the 4K projector is actually an HP CPU with a mouse, keyboard, and a monitor, all shipped into two very large and sturdy travel boxes.  <h2><b>How Sony 4K Servers Physically Compare</b></h2> <p><img alt="Sony 4K HP server for 4K Projector " align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image004_3.jpg" width="292" height="417">The 4K server loaned to 4K panel owners (not pictured in this article) is only compatible with the 4K panel it was delivered with, it does not recognize my Sony 4K projector as a compatible display device even when they are both 4K.  <p>The compatibility restriction was apparently intended to protect the full-length 4K movie content that only the panel owner is supposed to see for his/her personal demo.  <p>Dealers of the 4K panel are supplied with another demo server with short 4K clips, and can only install the full length content 4K server with the panel for a client that is required to sign a release document upon installation.  <p>The 4K server for Sony 4K panels is a Dell CPU with a Sony tablet that has a GUI application to select the content and to control the CPU via Wi-Fi, for which a Wi-Fi bridge for the CPU is provided.  <p>If wireless handshake problems are experienced between the devices an alternative maybe relocating the home's wireless router close to the CPU and connect a Cat5/6 to it, while the tablet interfaces with the router via Wi-Fi without using the Wi-Fi bridge. The CPU and the tablet are required to belong to the same network.  <p>This Wi-Fi/bridge Dell server setup may appear to be more complicated than the 4K HP server I used with my 4K projector (pictured), which was up and running in a few minutes and did not require an Internet connection or Wi-Fi, but, considering that an audio/video installer is to be involved with the 4K server and panel, the more user-friendly Sony tablet/server appears to be ideal for a panel owner to operate, rather than having to use a mouse, keyboard, and a computer monitor.  <p>Additionally, the appearance factor of a computer station adjoining the elegant 4K panel may not be appealing to most, not to mention the bulky packaging and the delivering of the HP server equipment in the very large/heavy boxes pictured above. The Dell 4K server for the 4K panel is shipped into a relative small cartoon box (for the smaller CPU, the Sony tablet, the Wi-Fi bridge, and the wiring).  <p><b></b> <h2>The Connectivity Aspects (mostly applicable to both servers)</h2> <p><img alt="Sony HP 4K server for 4K projector" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image012_3.jpg" width="247" height="442">The server for either the panel (Dell) or projector (HP) is connected via hi-speed HDMI cable to the display for the 4K video, for which a 15-feet hi-speed Sony HDMI cable is included.  <p>I did not use that HDMI wire for my projector because I already ran a second AudioQuest Cinnamon HDMI 8-meter cable rated for 4K (up to 10 meters) because I plan to buy a permanent 4K player and use that connection to the projector.  <p>Both servers transport audio within the HDMI video connection but a preamp/receiver that has 4K pass-thru capabilities (to output 4K video to the 4K display) would be necessary.  <p>An alternative is to use the Toslink connection (on either server) for 5.1 lossy multi-channel or the analog L/R stereo connection, from which the matrixed center/surround channels can be decoded by a receiver/preamp. As you see, the audio tracks are a bit retro in sound quality but the most important part is the video.  <p>I expect near future consumer 4K players to have two HDMI outputs, one for 4K video out to the 4K display, and another for lossless audio to the audio system, such as some Blu-ray players have (like Oppo).  <p>Although there are other HDMI connectors in the back of the servers they are mostly inactivated.  <p>Ironically the HDMI jack that is protected with a black plastic cap in the 4K server for the panel, giving the appearance of the one to use, is actually disabled and there is no documentation to indicate what is active and what is not, so the installer needs to contact Sony's 4K server panel support to find that out. To Sony's credit they are available until 10PM PST seven days a week, and are very helpful for the step by step installation process, and waiting on the line while troubleshooting.  <p>The 4K server for the 4K projector has the same audio connectivity limitations, and as I said the audio tracks of the content are only stereo (Pro-logic) or Dolby 5.1 in separate versions of the clips. Although the Toslink connection is appropriate for that audio quality I did most of the viewing without sound to concentrate in evaluating the quality of the image.  <h2><b>The Content in the 4K Servers</b></h2> <p><b></b> <p>The 4K server for the 4K panel has 10 full length movies and about 20 documentaries and clips which are appropriate enough for the purpose of a 4K demo on the paired panel.  <p>The 4K server for the 4K projector does not have full length movies but has the <a href="http://timescapes.org/4k/Default.aspx" target="_blank">TimeScape's</a> 48 minute 4K nature film, and several 4K and 1080p shorts and trailers.  <p>Sony permitted reviewers to evaluate their 4K panel only in their site, which restricts the reviewer's capability of lab tests and of comparing the panel with other displays, for which Sony has drawn criticism. Therefore it appears that an installer would have a better shot at calibrating, reviewing and comparing in the client's home, but the installer does not have the power of the pen. I hope that was not Sony's intention.  <p>One of my purposes was to view again the 4K Rocky Mountain Express train clip that convinced me of buying this projector after seeing it in a January 2012 demo at CES, rather than just requesting a unit for a review.  <p>After having the projector for almost an year I wanted to see the same 4K clips now in my home theater, my screen, my settings, my controlled light environment, and experiment with other settings and viewing positions, not as restricted as the Sony booth was.<p><p>According to Sony, not all content in the server is final, <i>"we use these servers to test content as well as demo it." </i> <p>The following is the list of titles included in the 4K server for the 4K projector:<img src="/articles/images/Livingwith4KHereisthe4KContent_146EC/content-in-sony-4Kserver-demo.jpg" /> <h2><b>The Viewing Environment</b></h2> <p><b></b> <p>Although my purpose was to write a review using the Sony 4K server connected to my Sony 4K projector I used the opportunity to  <p><img alt="Sony 4K projector" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image014_3.jpg" width="320" height="217"></p> <p>also demo 4K to several industry professionals and colleagues that work with hi-end audio/video equipment. Their expert feedback matched with my findings (down below) and I preferred that expert input rather than Joe-six-pack casual viewing opinion, because we also analyzed as a group the nuances of the repeated playbacks.  <p>I used a 130-inches Stewart Firehawk 1.3 G3 Cinemascope screen (104 inches for 16x9) in a dark home-theater environment. Although the seating was 14 feet away, which is one foot more than the recommended 3xPH distance for HD on that screen size and is certainly beyond the suggested 4K viewing distance (1.5xPH), viewers were free to choose their viewing position and even get their noses to the screen, which they did, only to find no visible pixels, the appearance of 35 mm film in digital form.</p> <p>But 4K is not just about resolution and I hope the format moves forward with the other features such as larger DCI color space, higher than 8-bit, faster than 24fps, etc.  <p>1080p content was upscaled to 4K by the Sony projector and video-processed with its Reality Creation engine. All viewers <img alt="Home-Theater/Testing Hardware, Sony 4K server at left of rack" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image018_3.jpg" width="433" height="597">unanimously appreciated the 1080p sourced image as stunning, including the Cinemascope clips using the 130 diagonal inches of the Cinemascope screen, which is the primary reason the seating is at 14 feet, my personal preference for a peripheral angle of view than is even wider than THX and SMPTE standards to enjoy an immersive experience of the scope movies I watch on the 10-feet wide screen, now without anamorphic lenses because the higher resolution of the 4K projector (and light output) produces a very detailed image on that aspect ratio (and no black bars).&nbsp; <p>Regarding the demoed 4K clips the general consensus was that the images were too "real". Expressions like "the best projected image I have ever seen" or "I could not come back now to our projection showroom after seeing this" were the first reaction statements.  <p>And "that" is exactly the feeling with 4K when is shown correctly: a sense of realism.  <p>Not just a great image, or the argument of seeing or not pixel structure due to eye's acuity, but rather the overall impact of realism from every object in the image, the extreme detail on skin, hair, foliage, thread of fabrics, etc.  <p>We also have to consider that this is a bare bones implementation of 4K, just using 8-bit, 4-2-0, Rec-709 HDTV color space, and 24 fps of the Blu-ray format.  <p>4K offers much higher potential in all those specs and promises to be the right path for the future of image quality, now just at UHD-1 level (4K for TV) within the <a href="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" target="_blank">U-HDTV standard</a>, but with an even higher UHD-2 level (also called 8K or Super Hi-Vision) that doubles the 4K pixel resolution in both directions (16 times HD resolution).  <h2><b>4K Content Review</b></h2> <p>In all honesty I was very demanding with the image quality I wanted to see and was not particularly impressed with some of the clips and even disappointed with some 4K trailers, such as Skyfall, and other clips/trailers that I have casually seen several times at shows. They did not show what 4K can do, except for the Rocky Mountain Express short clips, which maintained the best quality throughout most of the clip, and a couple of other F65 4K cameras shorts.  <p>One challenge is choosing the right content to demo 4K and another is for it to show a constant level of quality from beginning to end to meet the requirement and the purpose of the demo, which is to show the difference and the potential of 4K. One would not demo the capabilities of an HDTV with 480i substandard content to evaluate if the set is worth the investment.  <p>Some clips taken with the Sony's F65 4K camera were excellent but there were some scenes within them where the image quality dropped to an appearance of 1080p Blu-ray for just seconds due to insufficient illumination, white balance, contrast level, color saturation, etc.  <p>The Taylor Swift F65 4K camera video showed some of those brief instances. The video started with Taylor presenting the 4K video standing in front of a flat color background. The camera could have shown more details of her skin by closing up with the right light on her young face, but the scene was rather dull, as if it was recording an old actor hiding wrinkles.  <p>Then it jumped to a super bright exterior scene and then went indoors with a variety of depths, close-ups, and light changes that may have been OK for her music video clip, but 4K could have been shown better. This video clip was shown at CES for the new smaller Sony 4K panels, and unfortunately the video was very jumpy at CES, it was not in my review.  <p>A similar experience was on the F65 4K camera shots beyond the first part of "The Arrival" clip. The first camera shots of the glossy ceramic walls and floor tiles showed extreme detail and realism, a "being there" image quality, it was a clear representation of what the 4K camera and projector can do, but after that point there were several scenes with the appearance of Blu-ray 1080p quality, especially the ones with lower light in the detective's office.  <p>In the "El Dorado" F65 4K clip there was 8-bit color banding type of effect (an example of the effect is shown on the left image,  <p><img alt="8-bit color banding (left image)" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/image_3.png" width="584" height="184"></p> <p>sourced from my 2006 <a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi-part-3-hdmi-version-13-digital-connectivity-at-its-best.php" target="_blank">HDMI article</a>). The color banding showed waves of various blue sky colors while the sun increasingly illuminated the rocky scenery, hours of single shot recording shown fast in a 15-second scene, using the camera style of TimeScapes for the fast viewing of a recording of hours of real life. This color banding scene should have been removed from the 4K video.  <p>The "El Dorado" clip also showed scenes of what 4K can do, such as at the beginning of the video, when it showed the very shiny black paint of the Cadillac rolling on the streets of Las Vegas at night, with many lights of hotels and bright signs, the quality was excellent, but then it switched to the casino interior where the image quality dropped.  <p>Which were the best clips for me? Number 1: definitely the Rocky Mountain Express clips, specially the close-ups of the steam engine and the light changes in the front, the close-ups of the turning wheels at full power, and the complex green foliage when coming out of the tunnel, showing incredible depth, detail, and the many colors of trees and leaves viewed from a block away but still in great detail, certainly a breath taken shot.  <p><img alt="Sony 4K HP server for projector" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/clip_image010_3.jpg" width="246" height="373">Another great shot was of the cabin interior at the end of the clip, which captured the right atmosphere of the end of the day, the smoke from the cigar, and all the shades and crispness of the objects in that cabin.  <p>Although not of constant 4K quality most scenes throughout the two Sony's F65 demo videos mentioned before: "The Arrival" (especially the beginning) and "El Dorado" the Cadillac ride at night and the depth and detail on the rocky scenery while driving), they were both good looking 4K clips.  <p>I also preferred many scenes in the "Timescapes" nature 4K video, especially some transitions of light and darkness, although I prefer viewing nature at its real speed rather than cramping 24 hours of still shots moving fast in a few seconds of video, is a different art that others may like more.  <p>At least half of the 4K content in the server did not honor what the format can do but a large part of the blame is for NOT selecting the right content if the purpose was displaying image quality, such as the dusty and colorless first part of the Skyfall trailer when Bond fights over the train and gets shot.  <p>Conversely, the 1080p trailers of Men in Black III, Total Recall, and Resident Evil were much more appealing, detailed, and better contrasted in dark scenes than the dull appearance of some of the 4K trailers and clips.  <p>The Total Recall 1080p night scenes at the beginning of the trailer were so good that made questionable the merits of recording other dull content in 4K for a 4K demo.  <h2><b>Final Thoughts</b></h2> <p>The 4K experience is analogous to when a hi-end audio/video system is subjected to reproduce substandard content, or using low quality wiring, unmatched components, speaker coloration or positioning, incorrect audio/video calibration, or a signal source that is not consistently recorded with the expected excellent quality of the format.  <p>Small imperfections in an otherwise high quality set of components will be immediately noticed as "something is wrong or is missing" or "this is not right, the system is better than this" degrading the whole system and its justification for its higher cost.  <p>In this case, even using the 4K F65 cameras, a minor drop of proper illumination, a bit out of focus, not the right depth, too fast pan, the wrong choice of scene or objects, etc. made those few seconds degrade an otherwise brilliant 4K clip, which no doubt can motivate negative comments of the kind of "4K is not worth, I do not see the difference, who needs so much resolution", add the price information to that and anyone can anticipate the reaction.&nbsp;&nbsp; <p><img alt="RedRay 4K Player expected for 2013" align="left" src="http://www.hdtvmagazine.us/articles/images/Livingwith4KHereisthe4KContent_146EC/image_6.png" width="311" height="157"> Additionally, this is also similar to when we started with HD in 1998 and had to rethink new approaches for make-ups, lighting, camera shots, etc. and perhaps even facelifts in a hurry due to the increased detail.  <p>In summary, proper quality in 4K content and in the whole chain all the way up to the display device is required to notice a difference with 4K. When is done well it is too obvious to ignore it. So creating a good 4K camera or a stunning 4K display are just two items of the chain, many things in between can affect the outcome, such as using excessive compression on an otherwise excellent 4K content just to make it fit in the old jar.  <p>Stay tuned for a review of the <a href="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php" target="_blank">RedRay 4K player</a> and the near future consumer Sony 4K player later in the year.  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>March 10, 2013  7:25 AM</b>
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
			<?=getComments(5066)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5066)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2013/03/living-with-4k-here-is-the-4k-content.php" type="text/javascript" charset="utf-8"></script>
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