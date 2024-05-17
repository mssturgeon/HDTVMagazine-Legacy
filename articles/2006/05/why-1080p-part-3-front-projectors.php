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
		AND e.entry_id = 382";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 382 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 382 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 382";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/05/why-1080p-part-3-front-projectors.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 382";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Why 1080p? - Part 3 - Front Projectors" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Why 1080p? - Part 3 - Front Projectors" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Why 1080p? - Part 3 - Front Projectors" />
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
	<title>HDTV Magazine - Why 1080p? - Part 3 - Front Projectors</title>
	<meta name="keywords" content="video processing, front projectors, equipment chain, dvd players, frame rate, projector, fps, video, player, processing, film, resolution, chip, equipment, might, dvd, content, progressive, frame, could, dlp, output, front, sony, scaler" />
	<meta name="description" content="In this article, I will introduce some of new 1080p front projectors with full 1920x1080p resolution, one from JVC D-ILA, another from Sony using similar LCoS technology (named SXRD) breaking the ground at $10,000 with high reviews (Ruby), and to wrap up, I will introduce four DLP front projectors implementing Texas Instruments' new true 1080p DLP DMD with 2 million plus mirrors (not &quot;wobulated&quot; as the earlier 1080p solutions). Some of these products are not yet available.

Affordable 1080p front projection is finally here, the time might be right to start that Home Theater HD project of your dreams. Projectors with 1080p inputs can now be paired with Blu-ray players with 1080p outputs, which transport the 1080p content of Blu-ray discs. Hopefully, that feature would also be implemented in 2nd generation HD DVD players, the other format, in the near future, according with Toshiba." />
	<meta name="title" content="Why 1080p? - Part 3 - Front Projectors" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Why 1080p? - Part 3 - Front Projectors" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/05/why-1080p-part-3-front-projectors.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In this article, I will introduce some of new 1080p front projectors with full 1920x1080p resolution, one from JVC D-ILA, another from Sony using similar LCoS technology (named SXRD) breaking the ground at $10,000 with high reviews (Ruby), and to wrap up, I will introduce four DLP front projectors implementing Texas Instruments' new true 1080p DLP DMD with 2 million plus mirrors (not &quot;wobulated&quot; as the earlier 1080p solutions). Some of these products are not yet available.

Affordable 1080p front projection is finally here, the time might be right to start that Home Theater HD project of your dreams. Projectors with 1080p inputs can now be paired with Blu-ray players with 1080p outputs, which transport the 1080p content of Blu-ray discs. Hopefully, that feature would also be implemented in 2nd generation HD DVD players, the other format, in the near future, according with Toshiba." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=382', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/05/why-1080p-part-3-front-projectors.php">Why 1080p? - Part 3 - Front Projectors</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>May 25, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<blockquote>This article is the third in a series.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/01/why_1080p.php">Why 1080p?</a><br>
Part 2: <a href="/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php">Why 1080p? - Part 2 - A Brillian(t) Case</a></blockquote>
<br>
<br>
In this article, I will introduce some of new 1080p front projectors with full 1920x1080p resolution, one from JVC D-ILA, another from Sony using similar LCoS technology (named SXRD) breaking the ground at $10,000 with high reviews (Ruby), and to wrap up, I will introduce four DLP front projectors implementing Texas Instruments' new true 1080p DLP DMD with 2 million plus mirrors (not "wobulated" as the earlier 1080p solutions). Some of these products are not yet available.

<p>Affordable 1080p front projection is finally here, the time might be right to start that Home Theater HD project of your dreams. Projectors with 1080p inputs can now be paired with Blu-ray players with 1080p outputs, which transport the 1080p content of Blu-ray discs. Hopefully, that feature would also be implemented in 2nd generation HD DVD players, the other format, in the near future, according with Toshiba.</p>

<p>Because Hi-def players have the potential to supply a hi-end 1080p signal to a 1080p projector I will cover a bit of the Hi-def DVD subject; although this article is dedicated to 1080p front projectors, most of the discussion of 1080p connectivity and video processing would apply to other 1080p displays as well.</p>

<p>For those readers that do not know, there are two competing formats in the Hi-def DVD market, one is Blu-ray, the other is HD DVD. Both formats use blue laser, discs with film based content of both formats have progressive 1080p/24Fps capabilities, and both types of players are backward compatible to play regular DVDs upconverted to 1080i/p resolution over permitted connections, such as HDMI (although HD DVD players are upconverting DVD to just 1080i for the moment).</p>

<p>Toshiba just released two HD DVD players that are capable to read the 1080p resolution of the HD-DVD disc but output the HD signal as interlaced 1080i60 fields x second, which means the projector can not receive the 1080p content from a HD DVD disc due to a limitation of the player.</p>

<p>Since the primary objective of most Home Theaters is to watch movies, and most movies are sourced from 24fps film and telecined into 1080p video, it is preferable that the equipment that plays the video is able to communicate with the projector in 1080p with minimum conversions.</p>

<p>A 1080p24Fps movie displayed at that speed would show objectionable flicker. To avoid that flicker, your local Movie Theatre displays the 24 celluloid frames at double the speed by opening the film-projector's shooter twice for each frame, to total 48 frames per second. In progressive video, a projector would have to do something similar although not necessarily at that speed, usually at 60.</p>

<p>Ideally, between the disc and the image displayed by the projector the equipment chain should avoid unnecessary interlacing conversions and video processing when processing 1080p film-based content. This requires that all connections of the equipment chain maintain the 1080p progressive format and resolution, within the player, at its output, on the cables, on the input stage of the projector, etc.</p>

<p>In other words, not having 1080p outputs on the player would mean that film originated content is degraded from its original progressive form to an interlaced 1080i version using 2:3 pulldown (more below).</p>

<p>However, having 1080p outputs on a player not necessarily means problem solved. The frame rate output of the player might not be compatible with the frame rate the projector accepts; the player might output a 60Fps up-framed version processed internally while the projector expects the original 24Fps; the reverse could be true.</p>

<p>However, if both pieces offer multi frame rate capability it allows for testing to determine which one does what best, but it could also mean an impact of the expected overall 1080p performance if a handshake is reached by altering settings and not necessarily obtaining the best of their capabilities, individually and/or as a system.</p>

<p>When selecting players and projectors (and scalers) check the features, frame rates, resolutions, input/outputs, etc. before assuming the 1080p player would provide the perfect conditions you expect for your 1080p projector. For example, Sony announced their player to output everything as 1080p 60Fps, but Pioneer indicated 1080p 24Fps (which matches well with their Elite plasmas), while HD DVD players do not output 1080p at all.</p>

<p>If using scalers in between player and projector it would be ideal for the 1080p signal to pass-thru the scaler to avoid interlace processing, some scalers have pass-thru, some do not, in such case a direct connection to the projector bypassing the scaler could be the best choice for film content. However, the 1080p progressive signal could end up handled by an undefeatable interlace conversion before the projector displays it as 60p. Additionally, a scaler might help resolve a frame matching problem better than the other pieces if they multi-frame capable.</p>

<p>In other words, the conversion performs 2:3 pulldown processing to convert the 24Fps progressive source into 30 frames of 60i interlaced fields (adding the 6 missing frames as 12 fields), and then converts those 60 interlaced fields to 60p full frames, a displaying speed typically used for 1080p front and rear projection (and 1080p panels). Unfortunately, this might be not the best processing choice available in the equipment chain in order to preserve the quality of 24Fps film progressive content.</p>

<p>It could be better to maintain the progressive cadence of the film from the disc to the projector, and let the projector multiply the frame rate in the progressive domain. As mentioned above, some Pioneer Elite plasmas are known to be able to perform such functionality, the plasma panel accepts 24Fps but displays it as 72Fps, doing what they call 3:3 pull-down or Pure Cinema. The Brillian LCoS RPTV recently reviewed on the part II of this series displays at 120Fps, 5 times the 24Fps speed of the original film content.</p>

<p>"Best and ideal" equipment is not easy to find and to match because although the selection of equipment with 1080p capabilities is growing, it is still limited. There might be no other choice than to accept some connectivity limitations on the player, the projector, and/or the scaler in between, as well as accepting undefeatable interlaced conversions when handling 24fps film based material.</p>

<p>Regarding content originated as 1080i interlaced video, it would not need to go thru such 2:3 pull-down processing because it already has the 60i fields. However, to display 60i on 1080p projectors the 60i fields need to be deinterlaced and doubled as 60p frames. For performing that task, the assigned piece of equipment might/not be suited with certain functionality, such as motion-adaptive deinterlacing, pixel by pixel, motion calculation with less or more fields in advance, video processing chip used, etc.</p>

<p>The overall result of the system could be impacted if the job is assigned to a player that lacks the features, and the scaler/projector has them but they are bypassed. In other words, one piece of the equipment chain might perform that function better than the rest; take your time in choosing it correctly.</p>

<p>If the projector is assigned to perform the deinterlacing, the connection of player/projector would then be as 1080i, therefore, there is no gain in looking for absolute perfect 1080p connectivity for that particular application.</p>

<p>If there is a scaler in between player and projector and is assigned to perform the deinterlacing job, the connection between player and scaler would be 1080i, the scaler deinterlaces and doubles to obtain 1080p/60Fps, outputs it that way, and the projector accepts it as 1080p/60Fps and maps the image to its chip to display it usually at the same speed of 60; those two pieces should connect as 1080p (and assumes the projector accepts 1080p/60Fps).</p>

<p>As you see there might be several possibilities in the task of improving the overall 1080p picture, you might want to test each piece of the equipment chain to find the best combination of video processing, and that would only be possible if those pieces offer a variety of video processing capabilities and connectivity (1080i/p, various frame rates, etc).</p>

<p>Although I did not mention 720p as alternative to the lack of 1080p inputs/outputs it should be noted that some equipment outputs and inputs offer 720p capability.</p>

<p>Upscaling 720p to 1080p is viewed by some as a better choice than deinterlacing 1080i to 1080p. I particularly feel that reducing the spatial original resolution from 1920 of the disc to 1280 of the 720p transport format, to later upconvert it back to 1920 with interpolated pixels (the original pixels were already lost) for the final 1080p display, is a higher price to pay than the benefit of maintaining the 60fps temporal resolution of the 1080p/720p/1080p vertical resolution conversions of the p formats, specially considering that film content was the primary purpose for 1080p HT viewing, in other words: movies, no rapid sport videos.</p>

<p>Let us make the introductions:</p>

<p><img src="/images/articles/dla-hd10k.jpg" alt="DLA-HD10K" align="right"><b>JVC</b><br />
Introduced Feb 06<br />
<u>New Flagship</u><br />
DLA-HD10K $25,000, TTM now, 3-chip D-ILA 'non-moving' mirror reflective technology, 1920x1080p, <u>accepts 1080p 48/50/60 fps over DVI-D,</u> high-resolution lenses with motorized zoom and focus with a 0-60% vertical offset, two models: a long throw with a lens throw distance of 2-3.8:1 (placement of the projector at the back of the theater), for a 10-foot screen, the projector could be placed anywhere from 20 to 38 feet from the screen, and a short throw model with lens throw distance of 1.5 - 2.0:1 to facilitate projector to be used for CRT replacement or rear screen applications, 2500:1 CR, 27db quiet fan noise, user-replaceable lamp with 2000 hrs life at $500. Faroudja, Silicon Optix, and Anchor Bay Technologies are planned to supply three different external digital signal processor packages with this model.<br clear=all><a href="http://pro.jvc.com/prof/Attributes/features.jsp?tree=&amp;model_id=MDL101568&amp;itempath=&amp;feature_id=01">http://pro.jvc.com/prof/Attributes/features.jsp?tree=&amp;model_id=MDL101568&amp;itempath=&amp;feature_id=01</a></p>

<p><img src="/images/articles/vpl-vw100.jpg" alt="VPL-VW100" align="right"><b>Sony</b><br />
VPL-VW100 (Ruby)<br />
Shown again but not demo at CES was this Sony's projector using SXRD technology, TTM Nov 05, $10,000, 1080p 3x0.61" panels, little brother of Qualia 004, 15,000:1 CR with Advanced Iris Function on, 400-watt Pure Xenon Lamp ($1,000), <u>accepts 1080p/60fps over DVI and HDMI</u>, low fan noise 22dB, 1.8X Zoom, Lens Shift, DRC-MFv2, vertical keystone, auto input search, projection picture size 40 to 300 inches diagonally, although Sony recommended not larger than 120" (as below), projector shown at right.</p>

<p>At the SONY booth at CES 2006 the rep seemed to know this projector very well, he indicated that, in his view, the projector performs better scaling and video processing than most external scalers, he was not sure if the projector would disable the internal down conversion to 1080i when feeding 1080p to its input. It uses pixel-by-pixel motion adaptation deinterlacing. He recommended a Stewart white/gray screen not larger than 120" and 1.3 of gain; the Firehawk and DaLite screens were said to work well, with a minimum distance of 9" for maximum brightness. I viewed this projector several times in various environments and 90-110" screens, I consistently noticed the great resolution, but accompanied of a deficient light output for my taste. I suppose that the low light output could certainly please HT fans that love film Movie Theater environments, but I particularly prefer more lumens, and would rather choose a brighter projector such as the Optoma HD81, as long as the resolution and video quality could be equal or higher than the Sony, which looked that way at CES.</p>

<p><b>Texas Instruments</b> has recently released a consumer DMD DLP chip with 2+ million mirrors, one per pixel for the full 1920x1080p HDTV resolution. The chip is targeted initially to the front projector market. Check all the new products in the DLP section. Most 1080p DLP implementations use a 960x1080 chip to produce a 1920x1080 image, the chip that has half the mirrors of the image pixel count. The DLP engine uses a mirror tilting technique at double the speed to complete the full 2 million-image pixels in two horizontal image shifts of 1 million mirror reflections each ("wobulation").</p>

<p><img src="/images/articles/dlp-booth.jpg" alt="DLP Booth" align="left">According to TI, the human eye would see the two images as one at that speed. The technique was criticized by the competition because it did not use a chip with the two million-pixel mirrors, as the other technologies do, such as LCoS (Sony's SXRD, JVC's D-ILA, eLCOS, etc). TI did not disclose any plans to supply a similar chip for RPTVs, and commented that it was a market/manufacturer decision to request to TI 1080p chips if they are demanded for RPTVs, likewise, no announcements were made by any DLP set manufacturer of RPTVs regarding new lines using this new chip.<br />
<br clear=all><br />
Some CES demos of front projectors using the new two million-mirrors-1080p-chip were stunningly good, like the <b>Optoma HD81 1080p</b> ($10,000, TTM 3Q06, below) on a 135" screen, probably the <u>best 1080p FP in the price range</u>.<br />
<br clear=all><br />
<img src="/images/articles/hd81.jpg" alt="Optoma HD81 1080p" align="left"><br />
<br clear=all><br />
<img src="/images/articles/xv-z20000.jpg" alt="Sharp's XV-Z20000 DLP 1080p" align="left"><b>Sharp's XV-Z20000 DLP 1080p</b>, near future flagship model.<br />
$TBA (rumored at $12,000), TTM 3Q06, 1920x1080p resolution, Sharp's CV-IC III Video Scaling Circuitry, DVI/HDCP and HDMI inputs, 1000 ANSI, 10000:1 CR. Excellent demo with Blu-ray at CES (below), will <u>accept 1080p</u> when released, (left).<br />
<br clear=all><br />
<img src="/images/articles/vp-11s1.jpg" alt="VP-11S1" align="right"><b>Marantz DLP 1080p</b> new projector VP-11S1, TTM TBA, $ TBA, shown as prototype, 700 ANSI, 5000:1 CR, 2 HDMI, 2 component, Gennum video processing (right).<br />
<br clear=all></p>

<p><b>Projection Design</b><br />
<img width=312 height=173 src="/images/articles/action-model-3.jpg" alt="Action model 3 1080" align="left"><br />
Action model 3 1080<br />
True 1080p single DC3 DMD 0.95", Crystalio II (according to them the world's most technologically advanced video processor) with 4th generation broadcast quality algorithms for superior SD and HD video image quality, dual 7 segment color wheels and light formatters, DuArch illumination architecture featuring dual lamps, TI's BrilliantColor SLR technology, 24/7 operation warranty, Gennum's VXP Visual Excellence Processing, adjustable output brightness from 550 to 2500 ANSI lumens.</p>

<p>Stay tuned to the part IV of this "Why 1080p?" series, we will go deeper into the soon to be available Optoma HD81, a star in the CES 2006 show.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>May 25, 2006  9:01 AM</b>
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
			<?=getComments(382)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 382)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/why-1080p-part-3-front-projectors.php" type="text/javascript" charset="utf-8"></script>
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