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
		AND e.entry_id = 5202";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5202 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5202 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5202";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2014/02/living-with-4k-bought-an-uhdtv-wait-is-it-upgradeable.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5202";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K &ndash; Bought an UHDTV? wait, is it Upgradeable?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K &ndash; Bought an UHDTV? wait, is it Upgradeable?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K &ndash; Bought an UHDTV? wait, is it Upgradeable?" />
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
	<title>HDTV Magazine - Living with 4K &ndash; Bought an UHDTV? wait, is it Upgradeable?</title>
	<meta name="keywords" content="blu ray, hdmi chips, component analog, content protection, bit color, uhdtv, hdmi, content, new, upgrade, color, hdcp, blu, ray, may, product, sony, hdtv, most, player, buy, implemented, connectivity, consumers, includes" />
	<meta name="description" content="Your next question then would be “an upgrade to what and why?”

Unfortunately in most cases the upgrade may actually be a replacement of a short lived TV, depending who manufactured the UHDTV.

Over the past couple of years 4K displays and projectors were made available to consumers and, although market introduction is better than expected and prices are rapidly coming down, many journalists continuously discourage consumers with negativism, such as..." />
	<meta name="title" content="Living with 4K &amp;ndash; Bought an UHDTV? wait, is it Upgradeable?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K &amp;ndash; Bought an UHDTV? wait, is it Upgradeable?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2014/02/living-with-4k-bought-an-uhdtv-wait-is-it-upgradeable.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Your next question then would be “an upgrade to what and why?”

Unfortunately in most cases the upgrade may actually be a replacement of a short lived TV, depending who manufactured the UHDTV.

Over the past couple of years 4K displays and projectors were made available to consumers and, although market introduction is better than expected and prices are rapidly coming down, many journalists continuously discourage consumers with negativism, such as..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5202', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2014/02/living-with-4k-bought-an-uhdtv-wait-is-it-upgradeable.php">Living with 4K &ndash; Bought an UHDTV? wait, is it Upgradeable?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February  1, 2014</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=33&category=Technology">Technology</a></b>
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
				<p>&nbsp; <p>Your next question then would be “an upgrade to what and why?”  <p>Unfortunately in most cases the upgrade may actually be a replacement of a short lived TV, depending who manufactured the UHDTV.  <p>Over the past couple of years 4K displays and projectors were made available to consumers and, although market introduction is better than expected and prices are rapidly coming down, many journalists continuously discourage consumers with negativism, such as:  <p>“You will see no difference from your typical viewing distance”,  <p>“Your eyes are not capable to notice, UHDTV is stupid”,  <p>“Why UHDTV if many still think they are viewing HD when tuning to a SD channel on their HDTVs”,  <p>“Prices are too high”, and one of the latest denials is,  <p>“Current UHDTVs may be obsolete soon when standards are implemented”,  <p>Unfortunately, it may be right, and that depends on the very few manufacturers that offer some upgrade capabilities, and the take out of this article is: choose right, or wait.  <p>On the “<a href="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-3-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" target="_blank">Living with 4K</a>” series of articles I described the differences between 4K and UHDTV, and also mentioned the ITU <a href="http://www.itu.int/dms_pubrec/itu-r/rec/bt/R-REC-BT.2020-0-201208-I!!PDF-E.pdf" target="_blank">REC. 2020</a> UHDTV <a href="http://www.itu.int/net/pressoffice/press_releases/2012/31.aspx#.Uu2j7D1dW3J" target="_blank">standard</a>, which includes <a href="http://www.youtube.com/watch?v=LAVTX4S7vCw&amp;feature=youtu.be" target="_blank">4K UHD-1 and 8K UHD-2</a>, with 3840 (x2160) and 7680 (x4320) of horizontal resolution and 4 times/16 times the image resolution of HDTV, respectively.  <p>However, the new Ultra HD standard as specified by the Rec. 2020 <a href="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-4-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" target="_blank">includes other video features (and audio)</a> for image quality besides higher pixel resolution. It includes 10 and 12-bit color depth (rather than the current 8-bit of HD and Blu-ray, and all current consumer HD content), it also includes 4:2:2 and 4:4:4 chroma sub-sampling for a less aggressive color compression (rather than the current 4:2:0 used for consumer HD content, and Blu-ray, which discards 75% of the color information of the full color/luminance data of 4:4:4), and includes more colors than the current <a href="http://en.wikipedia.org/wiki/Rec._709" target="_blank">Rec. 709 of HDTV</a>/Blu-ray with a larger color space, to mention a few.  <p>Do you know if your UHDTV would be able to accept and display what it should? Or is just limited to display 4 times the pixels of the legacy HDTV image it can handle. On a recent <a href="http://conferences.smpte.org/content/current" target="_blank">SMPTE</a> conference we were discussing exactly that: more pixels or better pixels?  <p>Such detail is not usually addressed in the blogs and articles consumers typically read because to understand them requires the right audience and certainly more knowledge that just turn on the TV, sit, and enjoy, which is what most consumers care for, not to mention the knowledge the writer should have.  <p>However, early adopters that are actually interested on that detail, and bought or are about to buy an UHDTV or new 4K player, are beginning to get worried about the rumors of early obsolescence, and about investing so dearly on one of the new 4K UHDTVs to shortly find out that the UHDTV or the 4K player maybe obsolete because it cannot display the full feature set of what “may” be coming when the dust settles.  <p>One such item is 4K Blu-ray, with specs expected by year-end and possible players hopefully next year but with unknown technical features. Another item is content protection standards such as HDCP 2.2 (used by Sony’s 4K downloading player which was introduced as only compatible with new Sony’s 4K displays) rather than the commonly used HDCP 1.0. Another is the ability to accept and display 4K resolution at 60 frames per second (currently 24 and 30 fps). Another is the compatibility with HDMI 2.0 connectivity/spec, which features of 2.0, and which HDMI chips were implemented in the product, plain 1.4, 1.4 with certain 2.0 capabilities, or 2.0?, etc.  <p>Along those lines, due to the HDMI chips implemented, most A/V receivers and pre-pros are not capable to accept and output 4K untouched pass-thru video, or handle the higher frame rates of 4K, and most are not upgradeable, or the upgradeability may be costly.  <p>So the question to be asked by someone looking to buy an UHDTV now should be: “if the current model of UHDTV does not do what may be coming shortly depending on how the standard is implemented for content and equipment, which manufacturer can commit to an upgrade path?  <p>The answer to that is: unfortunately just a few of them. Samsung is one, Sony is another. Most do not say.  <p>Samsung offers a connectivity box that maybe upgraded/replaced so the consumer would not have to replace the UHDTV for issues of connectivity, such as the new HDMI 2.0 or HDCP 2.2, or future versions.  <p>Sony is performing an effort to upgrade the hardware/software of earlier models of 4K TVs and projectors to bring them up to the new features as they become available from the industry and standards, many of those not controlled by the manufacturer, such as HDMI 2.0, HDCP 2.2, etc.  <p>Incidentally, the HDMI 2.0 spec that allows for 4K 60fps was just introduced in September 2013 and HDMI chips take months to become available, so what a manufacturer is supposed to install on a UHDTV that was just released?  <p>What happens if 4K Blu-ray, or a downloading/streaming service, are implemented with 60 fps (a feature of HDMI 2.0, and Rec. 2020 standard) and the expensive UHDTV you just purchased has a 1.4 HDMI chip that cannot handle the new format?  <p>What happens if the 4K content carries the new HDCP 2.2 content protection with water marking, etc. and you recently purchased an UHDTV, or buy next month’s UHDTV from X manufacturer, which has an HDMI chip that cannot yet handle other than legacy HDCP 1.0 content protection?  <p>What happens if we are lucky enough to eventually have access to 4K content that comes with 12-bit color depth and larger color space, with more colors in the original image, and your recently purchased UHDTV can only handle 8-bit color depth and the smaller Rec. 709 color space of HDTV and Blu-ray content? Who and how that conversion be done?&nbsp; Could it possibly be done correctly?&nbsp; <p>Most 4K manufacturers sell you expensive UHDTVs and have no upgrade path. Sony has its own 4K player, own 4K content, own 4K cameras, installed base of thousands of 4K projectors on local theaters, a <a href="http://www.hdtvmagazine.com/articles/2012/12/living-with-4k-part-6-which-4k-sony-dci-4k-and-ultrahd-capable.php" target="_blank">whole coverage of the 4K ecosystem</a> no other industry player can claim.  <p>Regarding the cost of upgrading <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-getting-the-beautiful-monster-part-1.php" target="_blank">Sony’s first generation 4K consumer projector</a>, it has been estimated at $2,500 but that depends on the dealer's additional job on the client’s installation. That is only 10% of the cost of the $25,000 4K projector (VW-1000). Is that better than declaring it obsolete because HDMI 2.0 and 60 fps 4K have arrived?  <p>The upgrade is done by a Sony technician at the client’s home and includes a new lamp (+-$700), <a href="http://www.hdtvmagazine.com/articles/2013/03/living-with-4k-here-is-the-4k-content.php" target="_blank">Sony's 4K content downloader/player</a> ($700), Sony's new tablet to control the player and for 4K downloading services ($500+, also useful as a tablet as any other tablet), 4K 60fps capabilities of the HDMI 2.0 spec with new HDMI chips and main boards, compatibility with HDCP 2.2 for the protection of Sony’s 4K content, a feature other manufacturers do not even mention, ask them what are their plans for their customers if HDCP 2.2 is used to protect 4K content you want to view, etc.  <p>Again, critics should look at the broad picture and realize that although most manufacturers may expect customers to buy again, others like Samsung and Sony should rather be commended for their commitment to their 4K clients by adapting their products as new features, products and standards arrive to market.  <p>Companies that do not offer upgradeability may pose a big question mark for consumers looking for reassurance when selecting a new UHDTV, no matter how good the TV image may look today.  <p>I had a similar experience back in 1998 when HDTV was just on the air and HDTVs were introduced, the expensive HDTVs only had component analog inputs for the HD connectivity to HD-STBs to tune over-the-air or satellite and that was fine back then.  <p>Later in 2003 all HDTV and equipment manufacturers implemented DVI and HDMI digital connectivity and gradually switched to HDMI. Blu-ray players also went thru an “analog sunset” period to stop installing component analog connections, making obsolete early adopter HDTVs by disrupting connectivity with Blu-ray players and other source equipment that does not have component analog connections, such as <a href="http://www.bestbuy.com/site/searchpage.jsp?_dyncharset=UTF-8&amp;_dynSessConf=&amp;id=pcat17071&amp;type=page&amp;sc=Global&amp;cp=1&amp;nrp=15&amp;sp=&amp;qp=&amp;list=n&amp;iht=y&amp;usc=All+Categories&amp;ks=960&amp;fs=saas&amp;saas=saas&amp;keys=keys&amp;st=roku" target="_blank">Roku</a>, <a href="http://www.bestbuy.com/site/searchpage.jsp?_dyncharset=UTF-8&amp;_dynSessConf=&amp;id=pcat17071&amp;type=page&amp;sc=Global&amp;cp=1&amp;nrp=15&amp;sp=&amp;qp=&amp;list=n&amp;iht=y&amp;usc=All+Categories&amp;ks=960&amp;fs=saas&amp;saas=saas&amp;keys=keys&amp;st=apple+tv" target="_blank">Apple TV</a>, <a href="http://www.bestbuy.com/site/chromecast-hdmi-streaming-media-player/9071056.p;jsessionid=2CB7E433B50FF6D6828140C6A6C5E147.bbolsp-app03-195?id=1219013308425&amp;skuId=9071056&amp;st=chromecast&amp;cp=1&amp;lp=1" target="_blank">ChromeCast</a>, etc.  <p>About 11 million early adopters that purchased their HDTVs between 1998 and 2003 were disenfranchised and although an HDMI-to-component-analog converter could have helped, if the content carries content protection (HDCP) over HDMI, it would have not.  <p>The point is: No upgrade path to HDMI was offered by ANY TV manufacturer to the majority of HDTV owners at that moment, and those consumers were the ones that foot the bill of R&amp;D for those companies to grow by paying high prices for those first generations of TVs.  <p>In summary: As an early adopter in the middle of this evolving 4K implementation, rather than choosing a wonderful product with no assured upgrade plans, I prefer to choose a product from a manufacturer that releases ground-breaking technology as early as possible but commits to upgrade the product later to incorporate expected advances in the overall effort of implementing a new standard like UHDTV, without having to buy the product again for that reason.  <p>Additionally, due to the broad number of transforming factors of how 4K is being implemented, installers and dealers must do an effort to be fully knowledgeable of much more than the product at hand, to properly advice clients of all the variables, for the client to make the informed decision of when and which 4K product to buy, if they pursue more than just being first on the block. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February  1, 2014  8:38 PM</b>
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
			<?=getComments(5202)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5202)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2014/02/living-with-4k-bought-an-uhdtv-wait-is-it-upgradeable.php" type="text/javascript" charset="utf-8"></script>
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