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
		AND e.entry_id = 1534";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1534 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1534 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1534";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-323-a-few-amazoncom-top-ten-lists.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1534";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists</title>
	<meta name="keywords" content="stars details, lcd hdtv, blu ray, details samsung, player stars, details, stars, inch, dvd, hdtv, samsung, lcd, player, blu, ray, players, top, sony, touch, color, red, list, plasma, recorder, lists" />
	<meta name="description" content="We've compile a few lists of some of the top selling items at Amazon.com.  A few of the lists shocked us a little bit.  Of course, we have an opinion  on what they mean, and what we might see for the Holiday shopping season." />
	<meta name="title" content="HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-323-a-few-amazoncom-top-ten-lists.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="We've compile a few lists of some of the top selling items at Amazon.com.  A few of the lists shocked us a little bit.  Of course, we have an opinion  on what they mean, and what we might see for the Holiday shopping season." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1534', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-323-a-few-amazoncom-top-ten-lists.php">HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October 27, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=314&category=General Interest">General Interest</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-28.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
We've compile a few lists
of some of the top selling items at Amazon.com.&nbsp; A few of the lists
shocked us a little bit.&nbsp; Of course, we have an opinion&nbsp; on what they
mean, and what we might see for the Holiday shopping season.<br>

<br><strong>A Few Amazon.com Top Ten lists</strong><br>
<br><strong>Televisions</strong><br>
10. Toshiba 19LV505 19-Inch 720p LCD HDTV with Built In DVD Player<br>
&nbsp;&nbsp;&nbsp; 3.5 / 5 stars, $334, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001413EQ6" id="ulzp">Details</a><br>
<br>9. Samsung LN52A750 52-Inch 1080p DLNA LCD HDTV with RED Touch of Color<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $2123, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001418WF4" id="ulzp">Details</a><br>
<br>8. Samsung LN19A450 19-Inch 720p LCD HDTV<br>

&nbsp;&nbsp;&nbsp; 4 / 5 stars, $320, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001418WG8" id="ulzp">Details</a><br>
<br>7. Samsung LN22A450 22-Inch 720p LCD HDTV, Black<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $385, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00141AZCW" id="ulzp">Details</a><br>
<br>6. Samsung LN40A650 40-Inch 1080p 120Hz LCD HDTV with RED Touch of Color<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $1298, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0014175NE" id="ulzp">Details</a><br>
<br>

5. Samsung LN46A550 46-Inch 1080p LCD HDTV<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $1289, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0014175E8" id="ulzp">Details</a><br>
<br>4. Samsung LN40A550 40-Inch 1080p LCD HDTV<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $998, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001418W2C" id="ulzp">Details</a><br>
<br>3. Samsung LN32A450 32-Inch 720p LCD HDTV<br>
&nbsp;&nbsp;&nbsp; 5 / 5 stars, $623, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00141AYIC" id="ulzp">Details</a><br>

<br>2. Samsung LN46A650 46-Inch 1080p 120Hz LCD HDTV with RED Touch of Color<br>
&nbsp;&nbsp;&nbsp; 5 / 5 stars, $1570, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001413D94" id="ulzp">Details</a><br>
<br>1. Samsung LN52A650 52-Inch 1080p 120Hz LCD HDTV with Red Touch of Color<br>
&nbsp;&nbsp;&nbsp; 5 / 5 stars, $2029, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001413DF8" id="ulzp">Details</a><br>
<br>&nbsp;&nbsp;&nbsp; <br>
<strong>Of note:</strong><br>
<ul>

<li>Nine of the top 10 TVs are made by Samsung</li>
<li>All of the TVs are High Definition (60/40 split, 1080p to 720p)</li>
<li>All of the TVs are LCD</li>
<ul>
<li>The first plasma on the list is</li>
<ul>
<li>#17: Panasonic Viera TH-42PZ85U 42-Inch 1080p Plasma HDTV<br>
</li></ul>
<li>The first CRT, also the first SDTV on the list is</li>
<ul>
<li>#85: Haier HTR20 20" CRT TV<br>

</li></ul>
<li>The first rear projection on the list is</li>
<ul>
<li>#86: Samsung HL56A650 56-Inch 1080p Slim DLP HDTV</li></ul></ul></ul><br>
<strong>Our conclusions:</strong><br>
<ul>
<li>Watch for significant price drops in plasma and rear projection this Holiday season as they try to keep a toe hold in the market</li>
<li>While the LCD market looks very competitive, prices should remain more or less stable with demand being so high</li>
<li>CRT is dead</li>
<li>Rear project is just about dead</li>

<li>Plasma needs ultra-contrast, fast! (and affordable!)<br>
</li></ul><br>
<br><strong>DVD players<br>
</strong>10. Toshiba DVR610 1080p Upconverting Tunerless VHS DVD Recorder<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $158, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001415ERS" id="ulzp">Details</a><br>
<br>9. Philips DVP5140 Multiformat DVD Player with DivX, MP3, Windows Media Support<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $48, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000F2KUK8" id="p6gd">Details</a><br>

<br>
8. Philips Hard Disk/DVD Recorder 160 GB (DVDR3576H/37)<br>
&nbsp;&nbsp;&nbsp; 4 / 5 starts, $295, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0013WM0BQ" id="mln2">Details</a> <br>
<br>
7. Sony DVP-NS700H/B 1080p Upscaling DVD Player<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $73, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0015VW3BM" id="szbw">Details</a><br>
<br>
6. Panasonic DMP-BD35K 1080p Blu-Ray Player<br>

&nbsp;&nbsp;&nbsp; Not rated, $289, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001GAOYCS" id="urt9">Details</a><br>
<br>
5. Coby DVD-224 Compact DVD Player<br>
&nbsp;&nbsp;&nbsp; 3 / 5 stars, $28, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000261N6M" id="omg1">Details</a><br>
<br>
4. Sony DVP-FX820 8-Inch Portable DVD Player<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $154, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00139R1TA" id="omg1">Details</a><br>

<br>
3. Sony BDP-S550 1080p Blu-Ray Player<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $329, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001FN3ZRQ" id="omg1">Buy now</a><br>
<br>
2. Samsung BD-P1500 1080p Blu-ray Player<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $215, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0014H16V0" id="omg1">Details</a><br>
<br>
1. Sony BDP-S350 1080p Blu-ray Disc Player<br>

&nbsp;&nbsp; &nbsp;4.5 / 5 stars, $264, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001A4LVYY" id="omg1">Details</a><br>
<br>&nbsp;&nbsp; <br>
<strong>Of note:</strong><br>
<ul>
<li>The top 3 DVD players are Blu-ray players, 4 of the top 10 are Blu-ray</li>
<li>Sony has 4 players on the list</li>
<li>Of the non-Blu-ray players, none is just a "standard DVD" player</li>
<ul>
<li>One is a portable player</li>

<li>One is ultra compact, and ultra inexpensive</li>
<li>Two are upscaling/upconverting models</li>
<li>One is a DVR and recorder</li>
<li>One is a VHS and recorder (also an upconverter)<br>
</li>
<li>One supports digital media playback (DivX, MP3, etc.)<br>
</li></ul></ul><br>
<strong>Our conculsions:</strong><br>
<ul>
<li>Blu-ray
is gaining momentum.&nbsp; We still think Blu-ray player prices have some
room to drop, so look for them to be aggressive in "unseating" DVD
players this Holiday season</li>

<li>At around $75, upconverting players
still make sense to a lot of people.&nbsp; When Blu-ray hits $150 it will
take off, when it gets below $100 it will be pretty much the the only
format available.&nbsp; DVD will be the next VHS at that point.<br>
<br></li></ul><br>

				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October 27, 2008 10:17 PM</b>
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
			<?=getComments(1534)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1534)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv-and-home-theater-podcast-323-a-few-amazoncom-top-ten-lists.php" type="text/javascript" charset="utf-8"></script>
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