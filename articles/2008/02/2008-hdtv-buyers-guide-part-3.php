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
		AND e.entry_id = 1238";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1238 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1238 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1238";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-3.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1238";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2008 HDTV Buyers Guide, Part 3" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2008 HDTV Buyers Guide, Part 3" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2008 HDTV Buyers Guide, Part 3" />
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
	<title>HDTV Magazine - 2008 HDTV Buyers Guide, Part 3</title>
	<meta name="keywords" content="expansion modes, hdtv buyers, buyers guide, guide part, black bars, image, hdtv, viewing, might, content, analog, screen, could, modes, set, ntsc, part, expansion, cable, video, side, even, view, bars, digital" />
	<meta name="description" content="The third in a four-part series of articles on buying an HDTV. The following topics are covered in this segment:

Viewing Factors 
The Aspect Ratios 
The Viewing Experience at the Store" />
	<meta name="title" content="2008 HDTV Buyers Guide, Part 3" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2008 HDTV Buyers Guide, Part 3" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-3.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The third in a four-part series of articles on buying an HDTV. The following topics are covered in this segment:

Viewing Factors 
The Aspect Ratios 
The Viewing Experience at the Store" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1238', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-3.php">2008 HDTV Buyers Guide, Part 3</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 11, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>
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
				<div class="editorial">The following article is the latest in the 2008 HDTV Buyers Guide series. Other articles in this series are as follows:    <ul>     <li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_1.php">2008 HDTV Buyers Guide, Part 1</a> </li>      <li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_2.php">2008 HDTV Buyers Guide, Part 2</a> </li>      <li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_4.php">2008 HDTV Buyers Guide, Part 4</a> </li>   </ul> </div>  <br />  <p>The following topics are covered in this segment:</p>  <ul>   <li>Viewing Factors </li>    <li>The Aspect Ratios </li>    <li>The Viewing Experience at the Store</li> </ul>  <h2>Viewing Factors</h2>  <p>If you are switching from a regular direct-view 25&quot; CRT analog tube TV to a much larger 65&quot; projection digital TV, begin to recognize the compromises you might need to make regarding image quality for the given screen size when viewing <a href="/glossary.php#NTSC" target="_blank">NTSC</a> analog content on that HDTV set. </p>  <p>An HDTV set will display an upscaled progressive version of an NTSC analog 480i image, but because the original image is not as resolved, the resultant image after video processing could still be of insufficient detail for pleasant viewing. Even more so if the screen is too large or you are viewing it from too close. The effect could be compared to over-enlarging a photograph that does not have enough picture elements. The 4x3 image could look even worse when using the expansion modes of the TV to fill a 16x9 screen and eliminate black bars/pillars.</p>  <p>There will continue to be some NTSC programming broadcast until DTV is fully implemented in February 2009. And as mentioned before, cable companies have been given until 2012 by the FCC to make the full digital switch to all subscribers, which means that for now cable companies could continue feeding to analog subscribers analog 480i channels. </p>  <p>In a way, the 2012 extension above, approved by the FCC in 2007, is a relief for cable companies and subscribers, and saves both (for now) the extra cost of digital-to-analog converter boxes and digital-tier fees that would be required upon a full switch to digital channels. </p>  <p>Many 480i sources in an HDTV, including broadcast, VHS, laserdisc, DVD recordings, etc. that are based on NTSC resolution, would unfortunately show all the imperfections of the lower resolution analog source, and many will continue to do so for several years, especially if the new TV has a larger screen than the one you are replacing. </p>  <p>Some new video processing technologies improve analog image sources considerably by interpolating additional pixels to the original image, but there is only so much that can be done to an image that lacks sufficient original resolution for large displays. Be prepared to perform some tests with NTSC analog programming sources before you buy. Do not buy a screen larger than needed based only on how good the HDTV channels look. </p>  <p>Imagine for a moment this scene, the set is already at home and you and your wife begin the wonderful HD viewing, you change the channel to a non-HDTV station, your wife holds her breath for a second, and finally turns to you and tells you &quot;honey, the older TV looked better, why pay this much for a grainy picture?&quot; </p>  <h2>The Aspect Ratios</h2>  <p>Recognize the existence and the reason of various aspect ratios and their relationship to Hollywood movies, DVDs, Hi-Def DVDs, and 16x9 HDTV, and how they differ from the regular 4x3 aspect ratio of the regular NTSC analog TV. </p>  <p>Begin to understand and accept black bars, left/right pillars or top/bottom letterbox bars, and in some extreme cases even all four at once when the content has black bars within the image and the TV adds two more on the other sides for the particular program/channel. </p>  <p>Many widescreen movies (i.e., 2.35:1) are wider than the frame of a 16x9 HDTV (1.78:1) and they would still show with top/bottom letterbox black bars to maintain correct geometry of the image. A 4x3 TV (analog or digital) would show those black bars even taller. </p>  <p>Learn to recognize that even though the top/bottom letterbox bars give you the false impression that you are missing part of the image above and below, you are actually viewing all the left/right wider content intended by the director, that in a 4x3 image you would not see. </p>  <p>Understand that if you use the expansion modes for a 4x3 image to fill the 16x9 frame of the widescreen TV you are altering image geometry and the objects within, and also cutting out the content that overflows the edges of the TV frame. </p>  <p>Test TVs for 4x3 image expansion modes and scrolling capabilities. Expansion modes are not standard across manufacturers. Some TVs do not have scrolling features. The TV you like might have been made by a manufacturer that had chosen expansion modes you dislike. </p>  <p>Some expansion modes might truncate the upper part of peoples' heads on close-ups right above their eyes on the image, or make their bodies look too wide, or cut off valuable content from the bottom of the screen, such as movie subtitles, Bloomberg stock quotes, ESPN match scores, a tennis player serving behind the baseline, or the NASCAR car race position bar shown in the upper edge of the image. </p>  <p>If a 16x9 TV does not have image scrolling capabilities to allow you to move an expanded 4x3 image up or down it would mean that in order to see the hidden content of the top/bottom edges of the image, you might be forced to view the image as plain 4x3, with the side-pillars you dislike, and with the risk of side-pillar burning on prolonged viewing on plasma panels. </p>  <p>Consider also that if you choose to view 4x3 images on a 16x9 TV with side-pillars, the actual size &quot;of the image&quot; in diagonal inches is much smaller than the diagonal size of the TV screen, and its impact might not be as satisfying as you originally expected based on the panoramic impression of 16x9 images. Consider that the diagonal size of that 4x3 image with side-pillars could be even smaller than your previous 4x3 TV set. </p>  <p>Begin to educate your family regarding these aspect ratio issues, and about some viewing adjustments they might need to perform to reduce the risk of damaging some plasmas or CRTs (if you have or can still get one of those) when playing video games with two side black-pillars, and showing fixed logos and game scores for prolonged periods of time. </p>  <p>To avoid the burn-in effect of fixed objects on the image, some clever TV sets are designed to shift the entire image a few pixels at intervals in a way that is not noticed by the viewer. But many sets do not have that feature and it is up the owner to implement some safe viewing methods, such as periodically using expansion modes, avoid high contrast settings, etc. </p>  <p>Expansion modes that are unacceptable, or the lack of scrolling features, might have the potential to eliminate certain sets from your final list, regardless how good they might look in HD. Test them well at the store. </p>  <h2>The Viewing Experience at the Store</h2>  <p>When viewing and comparing sets, verify on the TV menu that video controls are set at mid-point (contrast is usually set very high by most manufacturers when they deliver the sets), color controls could have been altered by other customers, set the <a href="/glossary.php#Color+Temperature" target="_blank">color temperature</a> as standard or 6500 Kelvin (the &quot;warm&quot; setting would make the image more to red, the &quot;cool&quot; setting more to blue), turn off edge and color enhancements, sharpness, &quot;vivid&quot; settings, etc. </p>  <p>There is no value in viewing TV comparisons unless these adjustments are made first; and even then, be aware that side-by-side in-depth comparisons are difficult to be performed properly unless the sets are well calibrated and the viewing is done in a controlled light environment, an option not possible at most stores (back when CRT RPTVs were more popular, performing convergence with the TV menu was objected by sales personnel). </p>  <p>Many HDTV demos are done with still images of colorful flowers. TV is not about projecting slides, test with fast moving images. You might notice exaggerated jagged edges in diagonals or curves, pixelation errors, macro blocking, pixel over-activity (like ants moving), etc. </p>  <p>The TV itself might not necessarily be the one responsible for all those errors but it is helpful to learn how to detect image imperfections and how to identify their possible source (if it is the HD STB for cable/satellite/OTA/DVR, the TV, the broadcast, the digital compression, the limited bandwidth allocated for that HD signal, the signal strength on the reception, etc.). </p>  <p>Knowing the source of the imperfection would also aid on equipment upgrades, such confirming the need to replace an HD STB for cable/satellite/OTA/DVR rather than replacing a more expensive HDTV. Some Internet forums could help you develop awareness to detect imaging errors, or could confirm that what you are experiencing is not related to your set. </p>  <p>View content originated from true HDTV 1080i video cameras such as the HD-Net channel content, but also view content that originated from film such as movies from HBO and Showtime, which might help you experience the way film grain appears on an HDTV, whether you like it or not. Also check out the content from ABC or ESPN HD to test the conversion from their 720p broadcast to the 1080i/p native resolution of the TV, if that is the case (the conversion is made either by the integrated TV or by the HD-STB). </p>  <p>View NTSC video sources of various horizontal resolutions (such as DVD, VHS, antenna, satellite, cable); notice the effectiveness in the conversion of 480i content to progressive (to 480p, or upscaling to 720p/1080i/p), and how the video processing could further be affected if the 4x3 image is expanded by the TV modes. Evaluate if the viewing distance for those upconverted images is far enough, or you need to sit further back to tolerate possible image deficiencies. </p>  <p>View material from regular satellite/cable. Their typical over-compression is known to worsen fast action images; a large screen HDTV could make it more obvious, test with some basketball action not a golf match. </p>  <p>If after the combination of all the factors above you need to move your sitting too far back from the screen for NTSC material, the longer distance might have ruined the widescreen panoramic effect you &quot;expected from a widescreen TV&quot;, or might cause you to consider a smaller screen, or to replace your service provider due to quality, over-compression, etc. Perhaps you would have to live with it, and this is the time to be made aware of, before you buy the set.</p>  <p>Please stay tuned for the next part in the series: 2008 HDTV Buying Guide, Part 4</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 11, 2008  9:29 AM</b>
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
			<?=getComments(1238)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1238)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-3.php" type="text/javascript" charset="utf-8"></script>
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