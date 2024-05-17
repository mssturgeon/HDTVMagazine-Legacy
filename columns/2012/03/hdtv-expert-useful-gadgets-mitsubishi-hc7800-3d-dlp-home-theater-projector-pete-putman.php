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
		AND e.entry_id = 4746";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4746 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4746 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4746";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-useful-gadgets-mitsubishi-hc7800-3d-dlp-home-theater-projector-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4746";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &ndash; Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &ndash; Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &ndash; Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &ndash; Pete Putman</title>
	<meta name="keywords" content="gain screen, home theater, lens offset, color space, gamma performance, mode, color, projector, gamma, –, screen, figure, black, gain, ’s, contrast, mitsubishi, brightness, lens, low, home, see, content, settings, theater" />
	<meta name="description" content="In a day and age of &amp;acirc;��me too&amp;acirc;�� projectors, Mit&amp;acirc;��s HC7800 really stands out. But &amp;acirc;��ouch,&amp;acirc;�� those glasses!" />
	<meta name="title" content="HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &amp;ndash; Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &amp;ndash; Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-useful-gadgets-mitsubishi-hc7800-3d-dlp-home-theater-projector-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In a day and age of &amp;acirc;��me too&amp;acirc;�� projectors, Mit&amp;acirc;��s HC7800 really stands out. But &amp;acirc;��ouch,&amp;acirc;�� those glasses!" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4746', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-useful-gadgets-mitsubishi-hc7800-3d-dlp-home-theater-projector-pete-putman.php">HDTV Expert - Useful Gadgets: Mitsubishi HC7800 3D DLP Home Theater Projector &ndash; Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March 26, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=501&category=DLP HDTVs">DLP HDTVs</a></b>, <b><a href="/category.php?id=497&category=Front Projection">Front Projection</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>Shopped for a home theater projector lately? With all of the attention that new, low-cost LCD and plasma displays are getting, it might be easy to write off the home theater projector market’s future.</p>
<p> </p>
<p>Yet, front projection is still the cheapest way to get a big image – for the immediate future, at least. And there are some really good deals out there to be found, particularly in multi-function (2D / 3D) projectors.</p>
<p> </p>
<p>Mitsubishi has been turning out some really impressive and affordable home theater projectors for the past six years, starting with the ground-breaking HC5000 and continuing with the high-end 3D HC9000. At last year’s Cedia Expo, the HC7800 made its debut, and I finally got ahold of one to play with. I wasn’t disappointed.</p>
<p> </p>
<div id="attachment_1965" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1965" rel="attachment wp-att-1965"><img class="size-full wp-image-1965" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-1.jpg" alt="" width="600" height="313" /></a><p class="wp-caption-text">Figure 1. Mitsubishi's HC7800 shares a lot in common with the HC9000 3D projector.</p></div>
<p> </p>
<p>OUT OF THE BOX</p>
<p> </p>
<p>The HC7800 resembles its bigger brother HC9000 in more than one way. The cabinet has that same high-gloss black finish with an aerodynamic appearance, and a silvery-gray trim around the top panel controls, as well as around the front of the lens, which is offset slightly to the left of center.</p>
<p> </p>
<p>Directly behind the lens is a pop-up cover that reveals a knob adjustment for vertical lens offset. As it comes from the factory, the lens offset is pretty high, putting the bottom of the image at the optical centerline. The theory behind this decision is that the projector would most likely be ceiling mounted. However, you can dial the image down quite a bit, although you may see some degradation of brightness uniformity at the extremes.</p>
<p> </p>
<div id="attachment_1966" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1966" rel="attachment wp-att-1966"><img class="size-full wp-image-1966" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-2.jpg" alt="" width="600" height="359" /></a><p class="wp-caption-text">Figure 2. The vertical lens offset knob is hidden behind this door.</p></div>
<p><strong><em><br /></em></strong></p>
<p><strong><em> </em></strong></p>
<div id="attachment_1967" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1967" rel="attachment wp-att-1967"><img class="size-full wp-image-1967" title="Figure 3" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-3.jpg" alt="" width="600" height="239" /></a><p class="wp-caption-text">Figure 3. Here's the connector line-up for the HC7800.</p></div>
<p><strong><em><br /></em></strong></p>
<p> </p>
<p>The standard connector complement includes a single component video input, a 15-pin VGA connector for computers, and a pair of HDMI v1.4a jacks, compatible with frame-packed 3D program formats. Mits has also included an RS-232 port and Ethernet jack for remote control, a pair of 12V triggers for electric screens and anamorphic lens adapters, and a DIN connector that drives the infrared 3D sync emitter.</p>
<p> </p>
<p>The supplied remote control should be familiar to Mitsubishi projector users – it’s been standard for several years and provides direct access to inputs, three picture memories, and a bunch of useful tools including color management, frame rate conversion, three iris settings, and the usual brightness / contrast / sharpness / color settings. Brilliant Color mode is also supported.</p>
<p> </p>
<div id="attachment_1968" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1968" rel="attachment wp-att-1968"><img class="size-full wp-image-1968" title="Figure 4" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-4.jpg" alt="" width="600" height="328" /></a><p class="wp-caption-text">Figure 4. Same old remote, but some new buttons!</p></div>
<p><strong><em><br /></em></strong></p>
<p> </p>
<p>INSIDE THE CHASSIS</p>
<p> </p>
<p>The HC7800 is a single-chip DLP design that uses the latest .65” 1920×1080 DMD imager harnessed to a six-segment color wheel. You may be surprised to see mechanical lens offset married to a single chip DLP light engine, but it has become easier to achieve and essentially <em>de rigueur</em> for home theater projectors – especially when the preferred imaging systems make extensive use of lens shift.</p>
<p> </p>
<p>The illumination system revolves around a 240 watt short-arc lamp that can be throttled back to 190 watts in low power mode. In theory, this should provide a pretty bright image – Mitsubishi’s spec for full-throttle operation with no image correction is 1500 lumens – but in practice, you’ll see a much dimmer image after calibration, and may require a gain screen to watch 3D content as a result.</p>
<p> </p>
<p>Like other Mits projectors, the HC7800 is equipped with an irising system. It should provide an almost infinite black when activated, but also does some screwy things to gamma performance. My preference is to leave it off and use a low-gain screen to take care of low gray levels. However, that approach doesn’t work so well with 3D content as you will see shortly.</p>
<p> </p>
<p>MENUS AND ADJUSTMENTS</p>
<p> </p>
<p>Menu adjustments abound. Mits provides three User presets to store your settings, and you can tweak everything from brightness and contrast to color temperature (six presets plus RGB high and low), gamma (five settings from 2.0 to 2.4 and 3D, plus two user-defined gamma menus), and five picture modes including ISD Day and Night.</p>
<p> </p>
<p>The frame rate conversion menu works on multiples of 24 Hz, so when switched off, you are viewing movies at 96 Hz. Want to clean up all the judder and blurred motion while (and I quote) <em>“…retaining the clicking sensation unique to film?”</em> Select True Film mode. There’s also a True Video mode for 30 Hz / 60 Hz content that ups the rate to 120Hz.</p>
<p> </p>
<p>Mits has also given you five steps of motion interpolation to minimize 24 Hz blur and make film look more like video. Play with it; you’ll probably find a setting you like. And all of this stuff also works with 3D movies and video, too.</p>
<p> </p>
<p>The iris mentioned earlier has four speed settings, plus OFF. That last one is my preference! As I said earlier, variable iris settings can dive deeper into black than James Cameron in the Marianas Trench, but the display gamma is subsequently compromised and inconsistent. Better to use a lower-gain screen and stick with a fixed gamma curve to get the best results.</p>
<p> </p>
<p>The HDMI inputs can also be configured for different color modes and black levels settings. In RGB mode, black will be deeper than in video mode, and whatever HDMI output mode your DVD or Blu-ray player is set to should be matched on the HC7800. In theory, the projector should make this adjustment on its own, based on the signal detected from the player. You can also change video setup for every input on the projector, again with black at 0, 3.5, or 7.5 IRE.</p>
<p> </p>
<p>The color management tools are intriguing and should only be used with some sort of colorimeter to either read out the x,y coordinates for each color adjustment, or a graphical display of where the red, green, blue, cyan, magenta, and yellow wind up as you change saturation and hue. Don’t try this adjustment unless you can measure the results accurately!</p>
<p> </p>
<p>The HC7800 also has a color space adjustment. In Wide mode, the full gamut of the projector is used, regardless of the signal source. In Normal, the color gamut is truncated and closer to that of the Adobe sRGB color space (also closer to ITU REC.709 HDTV). Between this setting and the color management tools, you’ll get well within the ballpark.</p>
<p> </p>
<div id="attachment_1969" class="wp-caption aligncenter" style="width: 490px"><a href="http://www.hdtvexpert.com/?attachment_id=1969" rel="attachment wp-att-1969"><img class="size-full wp-image-1969" title="Figure 5a" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-5a.jpg" alt="" width="480" height="540" /></a><p class="wp-caption-text">Figures 5a-b: The HC7800's 'full' color gamut is so wide...</p></div>
<p><strong><em><br /></em></strong></p>
<div id="attachment_1970" class="wp-caption aligncenter" style="width: 549px"><a href="http://www.hdtvexpert.com/?attachment_id=1970" rel="attachment wp-att-1970"><img class="size-full wp-image-1970" title="Figure 5b" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-5b.jpg" alt="" width="539" height="606" /></a><p class="wp-caption-text">...that it covers most of the DCI P3 digital cinema color space.</p></div>
<p> </p>
<p>There are so many aspect ratios supported by the HC7800 that I can’t even list them. The owner’s manual shows 38 different possibilities, including anamorphic (two settings), 4:3, 16:9, two zoom modes, and a stretch mode. Leave this control set to Auto and it will generally figure things out on its own! The dual anamorphic modes are used with an accessory lens, with Mode 1 for video playback and mode 2 for sync with computers.</p>
<p> </p>
<p>Yep, I almost forgot – this is a 3D projector, too. The HC7800 is compatible with all 3D formats, with frame-packing detected automatically. However, for side-by-side and top + bottom frame-compatible 3D viewing, you’ll have to change the 3D menu setting manually as there is no way for the projector to know what kind of frame it is showing.</p>
<p> </p>
<p>The 3D menu lets you reverse the sync on the 3D glasses if the images aren’t rendering correctly. I’ve never had this happen to me, but it’s nice to know you can reverse the problem. There is also a 2D-to-3D processor which results in ‘fake’ 3D imagery by interpolating relative distances of objects in a scene and creating parallax information on the fly. I have never felt any need to watch 2D content in 3D, but I can tell you that the process works – sort of. Stick to native 3D content and you’ll be happier with the results.</p>
<p> </p>
<p>The 3D IR emitter is a compact little gadget with a swivel base that you can mount near the projector, or on top of it. The supplied 3D sync cable isn’t very long, and a super-long 3D sync cable like the one supplied with the HC9000 wasn’t included. But this emitter supposedly has a line-of-sight range of about 30 feet.</p>
<p> </p>
<p>ON THE TEST BENCH</p>
<p> </p>
<p>One thing I like about the Mitsubishi home theater projectors is that they come from the factory requiring little in the way of calibration. The HC7800 was no exception; all I had to do was switch to a deeper gamma setting for Blu-ray discs and fiddle a little bit with RGB contrast and gain.</p>
<p> </p>
<p>Brightness after calibration was measured at 388 ANSI lumens in Low lamp mode, jumping to 466 ANSI lumens in High (normal?) lamp mode. That is a lot lower than 1500 lumens, but in general, you’ll see at least a 50% reduction in brightness when calibrating a projector, and maybe more if you use a steep gamma. With the lights off and my Da-Lite Affinity 92” screen, I was quite satisfied with the results.</p>
<p> </p>
<p>Brightness uniformity is a challenge for DLP projectors and the HC7800 measured about 80% to the average corner, with the worst corner coming in at around 55%. Color temperature uniformity was within 515 degrees across the screen – not quite as ‘tight’ as I’d like to see, but for a $3,000 projector, better than average.</p>
<p> </p>
<p>Contrast numbers were pretty good, but reveal why Mitsubishi wanted to use an irising system. Using a sixteen-square checkerboard, I calculated ANSI (average) contrast at 477:1 and peak (highest/lowest) contrast at 772:1 – nothing to sneeze at! Sequential white/black contrast registered 1048:1, while a 50/50 white/black test pattern yielded a figure of 663:1.</p>
<div id="attachment_1971" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1971" rel="attachment wp-att-1971"><img class="size-full wp-image-1971" title="Figure 6a" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-6a.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Figure 6a. Here's the gamma curve for 2.4 Cinema mode. Sweet!</p></div>
<p> </p>
<div id="attachment_1972" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1972" rel="attachment wp-att-1972"><img class="size-full wp-image-1972" title="Figure 6b" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-6b.jpg" alt="" width="600" height="286" /></a><p class="wp-caption-text">Figure 6b. And here's the gamma curve in 3D mode - consistent, but shallow.</p></div>
<p> </p>
<p>The HC7800’s gamma curves are seen in figures 6a and 6b. 6a shows the final gamma for 2D mode with a 2.4 curve selected, while 6b shows the projector after being switched into 3D mode. Many 3D TVs I’ve tested do very strange things to gamma performance when running in 3D mode, and that’s because the brightness and contrast are pumped up to overcome light lost in the glasses.</p>
<p> </p>
<p>Fortunately, the HC7800 is a bit more disciplined and doesn’t jump too far off the tracks, resulting in a 1.94 gamma when showing 3D content. That’s not as steep as I’d like, but at least the curve doesn’t clip or flat-top at the high end, and the grayscale ramp out of black looks a lot like the 2D 2.4 gamma when you are wearing active shutter glasses.</p>
<p> </p>
<p>After trying to match up the projector’s color gamut to the REC.709 color space, I came up with the plot shown in figure 7. The user controls can get you very close with red and blue, but the green hue adjustment either wasn’t working or doesn’t have enough range – I couldn’t add enough yellow to the mix to line up with the desired 709 locus. But it was close.</p>
<div id="attachment_1973" class="wp-caption aligncenter" style="width: 490px"><a href="http://www.hdtvexpert.com/?attachment_id=1973" rel="attachment wp-att-1973"><img class="size-full wp-image-1973" title="Figure 7" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-7.jpg" alt="" width="480" height="540" /></a><p class="wp-caption-text">Figure 76. I got oh-so-close to matching the REC.709 color space. Oh well, still an improvement...</p></div>
<p> </p>
<p>IMAGE QUALITY TESTS</p>
<p> </p>
<p>For my viewing tests, I cued up 2D and 3D versions of <em>How to Train Your Dragon</em>, one of the better 3D movies I’ve seen. And of course, I pulled out my 3D copy of <em>Avatar</em> to see how it showed. For screens, I used the Affinity for both 2D and 3D viewing, and for some extra ‘punch’ set up a Vutec Silver Star 6.0 gain screen to help overcome the losses in the 3D glasses.</p>
<p> </p>
<p>As a 2D projector, the HC7800 is a peach. I’m not a big fan of DLP for the home, preferring full-time RGB imaging found in 3LCD and LCoS projectors. But this box performed much better than I expected, and in fact comes close to the performance of the discontinued $12K Samsung SP-A900B in many ways. Its color gamut may not be as accurate, but the HC7800’s color temperature tracking is exceptionally tight and gamma performance is remarkably consistent in any mode.</p>
<p> </p>
<p>After spending as much time as I needed on color management and getting the gamma right (between 2.3 – 2.4), I leaned back and enjoyed <em>Dragon</em> in good ol’ flat 2D. I also watched a few CBS and NBC prime time TV shows, caught some NCAA men’s basketball, and also a few cartoons; all the while looking for problems with black levels and color saturation. Didn’t see ‘em!</p>
<p> </p>
<p>Viewing 3D required me to put on the ‘newly designed’ Mits eyewear, and wow – were they heavy and uncomfortable! I kinda felt like a Navy Seal on a night ops mission wearing these glasses, which supposedly have faster switching times and reduced crosstalk. But they are big and bulky, and not what I expected after using the latest lightweight 3D specs from Samsung and Panasonic. Even the HC9000 specs aren’t as clunky.</p>
<p> </p>
<div id="attachment_1974" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1974" rel="attachment wp-att-1974"><img class="size-full wp-image-1974" title="Figure 8" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Figure-8.jpg" alt="" width="600" height="345" /></a><p class="wp-caption-text">Figure 8. No, they're not night vision goggles. But they feel like it.</p></div>
<p><strong><em><br /></em></strong></p>
<p> </p>
<p>Despite their weight and discomfort, the glasses worked very well. Indeed; I saw very little crosstalk as I tilted my head back and forth. But I definitely needed to use the gain screen during the nighttime scenes in Dragon and Avatar – 400 lumens just doesn’t cut it with a low-gain or even zero-gain screen. I could have used 2x or even 3x that level of brightness!</p>
<p> </p>
<p>So there’s your puzzler: The HC 7800 is a great all-around projector in 2D mode, but challenged to put enough photons on a low-gain screen in 3D mode after calibration. Aside from using two different screens to watch 3D – or a dual-mode screen, like Stewart now offers – you may want to just crank up the brightness and contrast when watching 3D content and not obsess over the gamma performance, or even the color temperature.</p>
<p> </p>
<p>CONCLUSION</p>
<p> </p>
<p>Amazing what $2,999 (or less) buys you these days. I couldn’t help but compare the HC7800’s 2D performance to the Samsung SP-A900B as I was calibrating it…such a deal! Even if you <span style="text-decoration: underline;">never</span> watch a single minute of 3D content on this projector, you’d be very happy with it matched to a .85 – .9 gain screen. But 3D mode will require some help from the screen, or a lot brighter lamp setting. And I’m sorry, but Mitsubishi has to re-think the glasses – they are just too bulky and uncomfortable for my taste.</p>
<p> </p>
<p><strong>Mitsubishi HC7800 3D DLP Home Theater Projector</strong></p>
<p><strong>SRP: $2,999</strong></p>
<p> </p>
<p><span style="text-decoration: underline;">Available from:</span></p>
<p> </p>
<p>Mitsubishi Electric Visual Solutions America, Inc.</p>
<p>9351 Jeronimo Road</p>
<p>Irvine, California 92618</p>
<p>Phone: (949) 465-6000</p>
<p>Fax:      (949) 465-6013</p>
<p><a href="http://www.mevsa.com/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.mevsa.com']);">www.mevsa.com</a></p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March 26, 2012  6:05 PM</b>
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
			<?=getComments(4746)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4746)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-useful-gadgets-mitsubishi-hc7800-3d-dlp-home-theater-projector-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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