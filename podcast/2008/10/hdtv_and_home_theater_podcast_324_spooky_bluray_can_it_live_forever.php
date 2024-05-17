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
		AND e.entry_id = 1538";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1538 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1538 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1538";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1538";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?</title>
	<meta name="keywords" content="review high, technical review, blu ray, high def, def digest, buy, high, video, def, technical, digest, blu, ray, review, surround, content, able, movies, usb, should, neville, mpeg, even, uncompressed, pcm" />
	<meta name="description" content="As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.  But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.  Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween." />
	<meta name="title" content="HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.  But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.  Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1538', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php">HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October 30, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=417&category=High Definition Production">High Definition Production</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-31.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.&nbsp; But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.&nbsp; Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween.<br>
<br><strong>Top Ten Blu-ray movies for Halloween</strong><br>
<br><strong>10. Pan's Labyrinth (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000WSLAUO" id="ehh7">Buy now</a>)</strong><br>
Following a bloody civil war, young Ofelia enters a world of
unimaginable cruelty when she moves in with her new stepfather, a
tyrannical military officer. Armed with only her imagination, Ofelia
discovers a mysterious labyrinth and meets a faun who sets her on a
path to saving herself and her ailing mother. But soon, the lines
between fantasy and reality begin to blur, and before Ofelia can turn
back, she finds herself at the center of a ferocious battle between
good and evil.<br>
<ul>

<li>1080p VC-1 video</li>
<li>DTS HD Master Audio 7.1 surround track</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1181/panslabyrinth.html" id="bn2e">review</a> at High Def Digest<br>
</li></ul><strong>9. Zodiac: Director's Cut (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001HUHBAE" id="gr-q">Buy now</a>)</strong><br>
Closer in spirit to a police procedural than a gory serial-killer flick, David Fincher's <em>Zodiac</em>
provides a sleek, armrest-gripping re-invention of the crime film. It
surveys the investigation of the Zodiac killings that terrorized the
San Francisco Bay area in the late -60-early -70s; Zodiac not only
killed people, but cultivated a Jack the Ripper aura by sending icky
letters to the newspapers and daring readers to solve coded messages.<br>

<ul>
<li>1080p VC-1 video</li>
<li>Dolby Digital 5.1 surround track</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1636/zodiac_nl.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>8. Underworld</strong><strong> (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000TGJ80I" id="gr-q">Buy now</a>)<br>
</strong>In the Underworld, Vampires are a secret clan of modern aristocratic
sophisticates whose mortal enemies are the Lycans (werewolves), a
shrewd gang of street thugs who prowl the city's underbelly. Noone
knows the origin of their bitter blood feud, but the balance of power
between them turns even bloodier when a beautiful young Vampire warrior
and a newly-turned Lycan with a mysterious past fall in love. Kate
Beckinsale and Scott Speedman star in this modern-day, action-packed
tale of ruthless intrigue and forbidden passion ­ all set against the
dazzling backdrop of a timeless, Gothic metropolis.<br>

<ul>
<li>1080p AVC MPEG-4 video</li>
<li>uncompressed PCM 5.1 surround track<br>
</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/996/underworld.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>7. The Orphanage </strong><strong>(<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
In
Newline's The Orphanage a woman discovers dark secrets hidden
within her cherished childhood home.&nbsp; The supernatural drama is the
feature film debut of acclaimed young Spanish director
Juan Antonio Bayona. A superbly atmospheric and emotionally powerful
tale of love, loss and guilt.&nbsp; There are a few gory make-up effects,
but Bayona mostly preys on our fear of the unknown to craft a
first-rate fright fest.<br>

<ul>
<li>1080p VC-1 video</li>
<li>DTS HD Lossless Master Audio 7.1 (Spanish)</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/446/orphanage.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>6. I Am Legend</strong><strong> (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
Robert Neville is a brilliant scientist, but even he could not contain
the terrible virus that was unstoppable, incurable, and man-made.
Somehow immune, Neville is now the last human survivor in what is left
of New York City and maybe the world. For three years, Neville has
faithfully sent out daily radio messages, desperate to find any other
survivors who might be out there. But he is not alone. Mutant victims
of the plague, The Infected, lurk in the shadows...watching
Neville's every move...waiting for him to make a fatal mistake.
Perhaps mankind's last, best hope, Neville is driven by only one
remaining mission: to find a way to reverse the effects of the virus
using his own immune blood. But he knows he is outnumbered...and
quickly running out of time.<br>

<ul>
<li>1080p VC-1 video</li>
<li>Dolby TrueHD 5.1 Surround</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1336/iamlegend.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>5. The Descent (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
On an annual extreme outdoor adventure, six women meet in a remote part
of the Appalachians to explore a cave hidden deep in the woods. Far
below the surface of the earth, disaster strikes when a rock fall
blocks their exit and there's no way out. The women push on, praying
for another exit, but there is something else lurking under the earth.
The friends are now prey, forced to unleash their most primal instincts
in an all-out war against an unspeakable horror - one that attacks
without warning, again and again and again.<br>
<ul>
<li>1080p AVC MPEG-4 video</li>

<li>uncompressed PCM 6.1 surround mix</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/463/descent.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>4. Disturbia</strong><strong> (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000RO6K80" id="ew9t">Buy Now</a>)</strong><br>
After his father’s accidental death, Kale remains
withdrawn and troubled. When he lashes out at a well-intentioned but
insensitive teacher, he finds himself under a court-ordered house
arrest. His mother continues to cope, working extra shifts to support
herself and her son, as she tries in vain to understand the changes in
his personality. His interests turn outside the windows of his suburban
home toward those of his neighbors, including a mutual attraction to
the new girl next door. Together, they begin to suspect
that another neighbor is a serial killer. Are their suspicions merely
the product of Kale’s cabin fever and vivid imagination? Or have they
unwittingly stumbled across a crime that could cost them their lives?<br>
<ul>
<li>1080p AVC MPEG-4 video</li>

<li>DTS 6.1 Surround-ES and Dolby Digital 5.1 Surround EX</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/939/disturbia.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>3. Monster House</strong><strong>  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000IFRT38" id="ew9t">Buy Now</a>)</strong><br>
Even for a 12-year old, D.J. Walters has a particularly overactive
imagination. He is convinced that his haggard and crabby neighbor
Horace Nebbercracker, who terrorizes all the neighborhood kids, is
responsible for Mrs. Nebbercracker's mysterious disappearance. Any toy
that touches Nebbercracker's property, promptly disappears, swallowed
up by the cavernous house in which Horace lives. D.J. has seen it with
his own eyes! But no one believes him, not even his best friend,
Chowder. What everyone does not know is D.J. is not imagining things.
Everything he's seen is absolutely true and it's about to get much
worse than anything D.J could have imagined.<br>
<ul>
<li>1080p MPEG-2 video</li>

<li>uncompressed PCM 5.1 surround
  mix</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/186/monsterhouse.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>2. Sweeney Todd: The Demon Barber of Fleet Street  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B001BN1ZHW" id="ew9t">Buy Now</a>)</strong><br>
After years of rumors, it turns out that Tim Burton was the perfect visionary to film <em>Sweeney Todd: The Demon Barber of Fleet Street</em>,
Stephen Sondheim's Broadway masterpiece, and the result is a macabre
and moving musical movie as enthralling as anything Burton has ever
done. The show's mix of gothic horror, Grand Guignol, <em>very</em> dark
humor, and witty and beautiful music never was the stuff of traditional
musical comedy, but it's a powerful work, and perhaps the richest of
the late 20th century.<br>

<ul>
<li>1080p VC-1 video</li>
<li>lossless Dolby TrueHD 5.1</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1603/sweeneytodd2007.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>1. The Shining </strong><strong>  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000UJ48WC" id="ew9t">Buy Now</a>)</strong><br>
?Heeeeere?s Johnny!? In a macabre masterpiece adapted from Stephen
King?s novel, Jack Nicholson falls prey to forces haunting a snowbound
mountain resort with a macabre history. Kubrick's <em>The Shining</em> gets under your skin and chills your bones; it stays with you, inhabits you, haunts you. And there's no place to hide.

<ul>
<li>1080p VC-1 video</li>
<li>uncompressed PCM 5.1 Surround mix<br>
</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/325/shining1980.html" id="bn2e">review</a> at High Def Digest</li></ul><br>
<br><strong>Is Blu Ray a Temporary Platform?</strong>
<div><br>
<strong></strong></div>
<div> We received an email from Brad in Festus MO with a link to a CNET
article that suggests that the Playstation 4 will not have a Blu-ray
drive (<a href="http://news.cnet.com/8301-13506_3-10042820-17.html" target="_blank" title="Why the Playstation 4 won't have Blu-ray">Why the Playstation 4 won't have Blu-ray</a>).
One reason for this assertion is that technology is moving so fast that
there isn't enough time for Blu Ray to take a strong hold before a
better technology makes Blu-ray obsolete. So for today we would like to
discuss this interesting idea.</div>

<div>&nbsp;</div>
<div><strong>Reasons for Blu-ray's Demise:</strong></div>
<div>
<ol>
<li>More
convenient to download HD content then go out and buy or rent. Its safe
to say that in the future we will have more bandwidth than we have now.
Its not unrealistic that we will be able to download HDX quality movies
in less than 30 minutes. We certainly will be able to start watching
within five minutes.</li>
<li>There will be a storage breakthrough that
will give us 25 or 50GB on a USB stick. Just two years ago no one would
have believed that you can store 8 GB on a USB key. Today you can buy a <a href="http://www.htguys.com/shop.php?id=B000TXEE14" target="_blank" title="8GB USB Stick for less than $25">8GB USB Stick for less than $25</a>!
Soon we will have USB 3.0 that not only increases the capacity but also
the data rate. USB 3.0 will have a 4.8 Gbps data rate so copying files
to the drive will not take forever.</li>
<li>Portability. With HD movies
on a stick you will be able to take you movies on the go. We predict
that there will be mobile entertainment systems that will be able to
receive the USB stick and play the contents. Likewise we feel that
future iPods will store HD versions of movies and be able to down
convert on the fly so that legacy devices with A/V inputs will still be
usable.&nbsp;</li>
<li>Studio Support - This is the most pie in the sky!
Studios will realize that doing away with all the packaging will
greatly increase their profit and they will fully support downloadable
content with no restrictions. They will also have two types of content,
free with ads included, and no ads but you have to pay for it.</li>
<li>Interactive
Content - BD live can still work in this scenario. There is no reason
why computers or other players can't access the Internet and provide a
dynamic experience.</li></ol>

<div>&nbsp;</div>
<div><strong>Our hope for the future:</strong></div>
<div>We'd
like to see a HTPC that is more like a DVR. It should be able to
download content but also have a tuner built in. Recorded and
downloaded movies should be transportable to a portable device in full
HD. In effect, the portable device should act like a VHS cassette tape.
If I have rights to the content I should be able to connect it to a
friends device and play&nbsp;the&nbsp;content. For universal access the device
should be able to output AV through RCA cables for playback on older
legacy type of devices. This ends up bringing the
Video&nbsp;Cassette&nbsp;Recorder into the 21st century. DVRs are great, but its
too hard to take your recorded content with you!</div>
<div>&nbsp;</div>
<div>&nbsp;</div></div><br>
<br><br>

				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October 30, 2008 11:38 PM</b>
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
			<?=getComments(1538)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1538)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-324-spooky-bluray-can-it-live-forever.php" type="text/javascript" charset="utf-8"></script>
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