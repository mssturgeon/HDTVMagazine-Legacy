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
		AND e.entry_id = 586";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 586 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 586 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 586";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2007/05/panasonic-ptae1000u-lcd-front-projector.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 586";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic PT-AE1000U LCD Front Projector" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic PT-AE1000U LCD Front Projector" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic PT-AE1000U LCD Front Projector" />
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
	<title>HDTV Magazine - Panasonic PT-AE1000U LCD Front Projector</title>
	<meta name="keywords" content="light output, color space, dynamic iris, color temperature, transmissive lcd, color, light, response, projector, screen, calibration, output, while, video, lcd, level, performance, using, pixel, normal, contrast, see, dynamic, iris, green" />
	<meta name="description" content="Starting with the PT-AE700 720p projector, Panasonic has built quite a reputation around their extremely wide installation capability and inexpensive pricing using transmissive LCD technology for the last couple of years. Panasonic continues their 720p capability with the PT-AX100 while introducing new 1080p24/60 capability in the form of the PT-AE1000U for a mere $4,000 USD street price, which is expected to get even lower as the months pass. This has certainly been the year for new 1080p front projection below $5,000 USD.

Transmissive LCD projection technology is well over a decade old using red, green and blue LCD panels. No color wheel is required and therefore no concerns over rainbows; those are a DLP issue only related to the size and expense of 3 chip capability as well as supply and demand of the devices. In the early days..." />
	<meta name="title" content="Panasonic PT-AE1000U LCD Front Projector" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic PT-AE1000U LCD Front Projector" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2007/05/panasonic-ptae1000u-lcd-front-projector.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Starting with the PT-AE700 720p projector, Panasonic has built quite a reputation around their extremely wide installation capability and inexpensive pricing using transmissive LCD technology for the last couple of years. Panasonic continues their 720p capability with the PT-AX100 while introducing new 1080p24/60 capability in the form of the PT-AE1000U for a mere $4,000 USD street price, which is expected to get even lower as the months pass. This has certainly been the year for new 1080p front projection below $5,000 USD.

Transmissive LCD projection technology is well over a decade old using red, green and blue LCD panels. No color wheel is required and therefore no concerns over rainbows; those are a DLP issue only related to the size and expense of 3 chip capability as well as supply and demand of the devices. In the early days..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=586', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic-ptae1000u-lcd-front-projector.php">Panasonic PT-AE1000U LCD Front Projector</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>May  7, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=284&category=HDTV Projectors">HDTV Projectors</a></b>
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
				<p class="editorial">As this article went to publishing it was <a target="_blank" href="/forum/viewtopic.php?p=25340#25340">reported at HD Library</a> that Panasonic is providing a $1,000 USD rebate and one member found this projector for $2,749 USD. This is nearly half the price of projectors that can do better and taking all of its performance points into perspective this price level definitely hits the bang per buck category of entry level gear representing great value!</p>
<img src="/images/products/panasonic-pt-ae1000u.jpg" alt="Panasonic PT-AE1000U" /><br />

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid">$5,999.00</td>
<td class="greygrid"><a href="/equipment/model.php?man=Panasonic&model=PTAE1000U">$3,795.00</a></td>
<td class="greygrid"><a href="http://www.amazon.com/gp/product/B000MJD43Y?ie=UTF8&tag=hdtvmagazine-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=B000MJD43Y">$3,799.00</a></td></tr>
</table>
<br />
Serial #SG6640008R<br />
Warranty: 1 year parts and labor<br />
<br />
<B>Summary: Entertaining big screen pictures at 1080p/24, with the widest installation capability for a front projector</B><br />

<p><br />
Starting with the PT-AE700 720p projector, Panasonic has built quite a reputation around their extremely wide installation capability and inexpensive pricing using transmissive LCD technology for the last couple of years. Panasonic continues their 720p capability with the PT-AX100 while introducing new 1080p24/60 capability in the form of the PT-AE1000U for a mere $4,000 USD street price, which is expected to get even lower as the months pass. This has certainly been the year for new 1080p front projection below $5,000 USD.</p>

<p>Transmissive LCD projection technology is well over a decade old using red, green and blue LCD panels. No color wheel is required and therefore no concerns over rainbows; those are a DLP issue only related to the size and expense of 3 chip capability as well as supply and demand of the devices. In the early days fill factor of the pixels was quite large, creating a screen door effect that could not be missed with less than 8 screen heights. In this area transmissive LCD has improved by leaps and bounds, becoming one of the hallmarks of the Panasonic line; no pixels to be seen at even unreasonably close viewing distances. This technology does come with one thorn though that has yet to be tamed, natural dynamic range. To produce black, transmissive LCD has to block the light trying to pass through it, which remains difficult and results in a poor black response. Transmissive LCD also takes a hit in peak light output due to inefficient pass through losses of the LCD panels. This absorption of light, regardless of image, also requires the LCD panels be constantly cooled lest they or their neighboring optical parts suffer a melt down.</p>

<p>One way to improve dynamic range is to employ an auto iris and manipulate the gamma response. Most call this a "dynamic iris". Many are using this technique to enhance the viewer's perception of dynamic range while also improving spec numbers for higher contrast ratios. For a proper ISF calibration, the feature must be turned off or the numbers will never make sense; the feature changes things that much. Many transmissive LCD products use this process to create a competitive perceived dynamic image. This inspired <a href="/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php">HD Waveform 10 - Dynamic Iris and Gamma</a>, which uses the PT-AE1000 for the measurements. Please see the above article for the specifics.</p>

<p><br />
<B>Common Features</B><br />
<ul><li>2 HDMI and 2 component video via RCA inputs, 1 S-video and 1 composite video input, and 1 VGA PC input</li><li>On the top side are two adjustments for vertical and horizontal lens shift. There is a side door containing your control keyboard.</li><li>Aspect controls vary depending on input type and scan rate. All inputs offer 4:3 or 16:9. S-video and composite video add AUTO, 14:9, ZOOM1, ZOOM2 and JUST. HDMI with HD scan rates adds H-FIT, V-FIT and ZOOM. Component with HD scan rates adds H-FIT and V-FIT.</li><li>The remote is a good size, fits the hand well while providing a back light feature when you are in the dark. It is programmable for accessing the basic features of other products.</li><li>Programmable sleep mode turns the projector off automatically by pressing the SLEEP button on the remote and selecting one of 7 fixed durations from 60-240 minutes.</li><li>Horizontal and vertical position adjust is provided and is not to be confused with lens tilt features as you are moving the image on the LCD panels.</li><li>Provides two lamp power settings: NORMAL and ECO-MODE.</li><li>Dynamic Iris with on or off switching</li><li>Lamp hours can be found in the customer menu</li><li>Serial input for external control of the projector</li></ul></p>

<p><br />
<B>Not-So-Common Features</B><br />
<ul><li>14-bit digital video processing</li><li>Provides 7 preset modes for controls and features plus 5 user memories</li><li>Clock and phase adjustments are provided to reduce artifacts or obtain 1:1 pixel mapping with PC or component HD scan rates</li><li>Color management allows 8 points of adjustment, although six is all that is required, and 3 user memories for your defined color space</li><li>Full color temperature and tracking controls plus more</li><li>HDMI supports both video and PC video levels via an HDMI Signal Level adjust</li><li>"Pure Color Filter Pro" for professional-level color reproduction in select modes</li><li>Horizontal and vertical manual lens shift</li><li>Powered 2x zoom and focus</li><li>Waveform Monitor. This is a truly unique and precedent setting feature allowing you to see your signal source including digital HDMI/DVI just like the pros! It displays in frame/field or line modes including Y, G, R, and B signals in both modes. You can select which line you want to view. Adjustment of video setup controls is reflected directly on the monitor. The manual does a decent job of explaining how to use this feature and how to make some adjustments but is far from complete. This allows you to see your signal and know conclusively what is coming from your source for video setup. In fact, you should be able to do a source calibration with this tool provided you do not have the projector calibrated professionally. Video setup adjustments are reflected and a calibration is going to be changing those which change the reference points of 0 and 100IRE for your contrast and brightness settings, as an example.</li></ul></p>

<p><br />
<B>Out of Box Performance</B><br />
The PT-AE1000 offers so much latitude in placement, making it a clear winner for point-and-play big screen. With the lens shift, zoom and patterns it can't take more than 1-2 minutes to have it ready for action at some convenient spot in your room.</p>

<p>There are so many features and options related to video setup in addition to the 7 presets provided that it made my head dizzy. Out of those seven, four of the presets engaged a function making it known by a motorized sound and a significant reduction in light output. The three presets having the highest light output are CINEMA3, NORMAL and DYNAMIC. I went with NORMAL and selected a color temp that appeared correct.</p>

<p>First up was King Kong on HD DVD. While I was mesmerized by my first 1080p experience, I also found my first problem; some of those yellow taxi cabs showed signs of clipping where bright yellow was clearly white instead. Next up was my gaming PC, which brought out the second problem: loss of clarity. I adjusted focus several times and then tried turning the iris off and that helped. Nonetheless I found myself looking for an expected level of sharpness and detail at the pixel level that could not be found. Going to a PC game though, I was floored and remained floored for days due to the level of detail provided with another 1,152,000 pixels to work with, yet I also found myself frustrated when using things such as forums and web surfing. Time for a calibration to hash out this experience!</p>

<p><br />
<B>Calibration and Performance</B><br />
Calibration Reporting follows a system developed by the ISF Forum called the ISF Display Chart. Some headings provide an embedded link to the ISF Forum for a complete description of the calibration parameter. The key consideration for this part of the review is to ascertain if the necessary controls and features exist to calibrate the display to video standards. In some cases a control or feature may not be present although the product meets that particular qualification for performance.</p>

<p>Pictures are taken with a digital camera, which has limitations of its own inducing artifacts that are not there. The main purpose of the pictures is to provide a reference for the review, regardless of quality, and provide a fair impression of actual performance as compared to other pictures to which they may be compared.</p>

<p><br />
<B>Optics</B><br />
Focus uniformity was even left-to-right and top-to-bottom, with slight depth of field focus errors on the edges which will change with the zoom setting. Adjusting focus or adjusting zoom to the end points would make the picture slightly change position, and while not severe it certainly was odd.</p>

<p><br />
<B>Pixel Visibility</B><br />
The definition of pixel visibility has two forms. One is the ability to see the individual pixel response on your screen and is related only to your ability to perceive the full resolution; this is good. The other is your ability see pixels on the screen related to the fill factor provided by the technology and that is the concern for this measurement.</p>

<p>For this projector there is practically no fill factor. There is little to be seen between the pixels even when you are within a foot of screen. With LCD you are focusing on the panels and they have enough depth that when you are setting focus there are three steps that appear to be in focus. Without clearly visible fill factor at the screen it could be difficult for the novice to discern which of the three is correct as the focus pattern itself has an artifact adding to the elusiveness of this adjustment; I chose the middle focus point. If your desire is short viewing distances and big pictures without being able to see the technology, this one excels.</p>

<p><br />
<B>1:1 Pixel Mapping (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=82">Definition</a>)</B><br />
The luminance, black and white signal, and chrominance, color signal, frequency response pattern goes out to the full 1920 lines, but it should be noted that 960 lines is the limit of chroma or color detail for the current HDTV system. The only source that can provide 1920 chroma detail is a PC.</p>

<p><br />
<B>HDMI 1080i / Component 1080i / Reference</B><br />
<img alt="HDMI 1080i / Component 1080i / Reference" src="/images/reviews/PT-AE1000/allBurst.jpg" /><br />
For HDMI the 960 line response is nice for red and possibly oversaturated, lacking for blue and at 1920 there are two errors. Looking at the luminance 1920 you see it lacks the contrast of the 960. There is a graying out of the response as the difference between peak white and black is reduced. Another artifact is that the white lines are fatter than the black ones. If you were here with me you would note that the light from the white pixels are actually leaking into the black ones reducing what is called intra-field contrast ratio. This is not the full story behind this observed response and I cannot tell you if it is a limitation of the technology or the circuitry feeding the LCD panels.</p>

<p>At 1920 you will also note the highly saturated red and blue chroma response bleeding the primary of red or blue in the 1920 pattern removing any trace of the secondary cyan or yellow. This was an odd response and during the review it was not as if this artifact reared its ugly head with video or PC graphics. As noted earlier, video does not support a 1920 chroma response so it would seem safe to say that this would not cause a problem with a video source. On the other hand, if PC graphics are going to be a primary source this could show up with the right image.</p>

<p>From experience this type of response is related to filtering of a multiple line response also representing a specific reoccurring frequency. I have calibrated and tested many displays that could not pass the multiple alternating black and white single pixel burst at all providing only a grayed out field in it's place, yet could pass (not a correct response mind you - but pass) a single pixel white or black line from a pin pattern or the Sencore focus pattern of alternating E's formed out of single pixels. This is yet another example of how just the right image may be required to show the error.</p>

<p>The component 1080i response does not have the color bleed error at 1920. At 960 it does not have the clear pattern of red and cyan or even the ghostly blue and yellow response of HDMI. The 1920 luminance response remains consistent with HDMI. Key point for this test is that there are projectors on the market that pass these patterns correctly via either connection type.</p>

<p>Looking at the reference response you can clearly see each and every line and pixel of either luma or chroma information. This particular reference also shows the difference in pixel visibility between a full 1920x1080 chip DLP and this LCD. On the component 1080i image I happened to catch a clear shot of the LCD pixels; as you can see the fill factor is overwhelming good. While one might suspect that the differences in how these images were captured could easily account for what you are seeing, for the record, they are a fair facsimile of what you would perceive with the naked eye. The lack of detail and soft response is inherent of transmissive LCD technology.</p>

<p>While the projector clearly does 1:1 pixel mapping, this test not only shows response errors but also provides clues as to why a full chip DLP, as an example, would outperform it for detail and sharpness. At the viewing position, you cannot clearly make out pixels or fill factor of the DLP any more than the LCD, yet the perceived difference in response for these patterns would be strikingly clear. With real images you would note a difference in sharpness and detail.</p>

<p><br />
<b>Overscan</b><br />
Via HDMI, 720p, 1080i, and 1080p all chopped off a few pixels on the right side. This also revealed an interesting observation; the projector remembers vertical and horizontal centering positions by scan rate. Via component analog video, 720p and 1080i had the same result with only a one pixel loss on the left side for 720p. </p>

<p><B>Calibration Notes</B><br />
The settings constantly interacted with each other from brightness and contrast to the color temp adjustments; just one single click or numerical change could slightly throw off the whole cart for another round of adjustments. I started with NORMAL and being dissatisfied with the overall results tried CINEMA3, but that was off even more and could only be drawn in using the coarse color temp adjust in the video setup menu. In COLOR1 I found a response very close to standards and this one also used the motorized light reducing gizmo. While not documented, I set this one up achieving a similar response as I had with NORMAL but with an even lower light output. This particular setting did not require a color space adjustment having been preset by the factory on target. For most of the settings the green and blue primaries were increased for additional (and clearly beneficial) light output to sell the product, but pushing the color temperature towards cyan prevents 100% accurate color and fidelity. Another concern was the unexpected lack of fine adjustment making it difficult to get precisely on target for color temperature and tracking. These controls were no different than others I have used in the past, yet they had a coarse response for this projector. I was unable to get an overall delta C response below one, although my 30 and 80 IRE targets were correct.</p>

<p><br />
<B>Gamma (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Definition</a>)</B><br />
The gamma response charts consist of a green line representing the target gamma of 2.2 and a red line representing the response of the display. The average gamma figure only has value when the lines match; otherwise your calibrator will look at the individual steps to identify and correct the problem.</p>

<p>NORMAL Gamma Pre-calibration<br />
<img alt="NORMAL Gamma Pre-calibration" src="/images/reviews/PT-AE1000/normalgammapre.jpg" /></p>

<p>NORMAL Gamma Post-calibration<br />
<img alt="NORMAL Gamma Post-calibration" src="/images/reviews/PT-AE1000/normalgammapost.jpg" /></p>

<p>With the iris turned off the gamma was actually not bad for pre-calibration. The contrast had to be turned down significantly to even-out the steps and open up the peak white from 90 to 100IRE. This compression of gamma at the top in most cases infers a large color temperature error as well; yet another reason to try and obtain a proper response.</p>

<p><br />
<B>Color Temperature and Tracking (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">Definition</a>)</B><br />
A raw 6500 Kelvin response chart may look nice but it does not reflect a specific color. Delta C is provided instead which shows how far off from D65 the response is. The target is less than 1. Less than .5 error is considered quite good approaching a reference response. RGB response charts are included providing a much better understanding of response errors. In a perfect D65 world all three colors would be flat creating a single line response at 100% for a flawless color temperature and tracking response.</p>

<p>NORMAL Delta C Pre-calibration<br />
<img alt="NORMAL Delta C Pre-calibration" src="/images/reviews/PT-AE1000/normaldeltaCpre.jpg" /></p>

<p>NORMAL D65 RGB Chart Pre-calibration<br />
<img alt="NORMAL D65 RGB Chart Pre-calibration" src="/images/reviews/PT-AE1000/normalRGBpre.jpg" /></p>

<p>NORMAL Delta C Post-calibration<br />
<img alt="NORMAL Delta C Post-calibration" src="/images/reviews/PT-AE1000/normalDeltaCpost.jpg" /></p>

<p>NORMAL D65 RGB Chart Post-calibration<br />
<img alt="NORMAL D65 RGB Chart Post-calibration" src="/images/reviews/PT-AE1000/normalRGBpost.jpg" /></p>

<p>While not provided when I looked at the data charts, I could see the light output dropped by half creating a significant loss; so significant that I looked up a few front projection displays for comparison and indeed the PT-AE1000 was rather unique. It is commonly known that calibration will cause a loss in light output and a 25% drop is not unusual when bringing the two stronger primaries back into spec. Looking at the RGB chart you can see that both green and blue output were dramatically increased over red. Red is the Achilles heel of all arc lamp based displays as it is the primary with the least amount of light output. All display products have a weak primary so no big deal. What this means for an arc lamp source is to increase light output for sales and marketing you turn up green and blue since they have more light output to offer which ends up pushing the color temperature towards cyan. This creates a fairly common marginal error and correcting it has marginal impact on light output. For the Panasonic LCD this was not the case. What we see here is a heavy handed boost of green and blue creating horrendous cyan errors to provide a competitive bright image in the market. While I did not measure the results I did return the contrast back to pre-calibration which helped boost light output quite a bit while keeping things under some control. I tried returning the color temp to preset creating a huge boost in light output but that made white clearly cyan shifted.</p>

<p>Delta C errors typically come in two peaks for many consumer displays. As mentioned earlier, the response was difficult to tame but I gave it another go and managed to squeak out the final shown here below with two strong peaks and a fairly good valley below 1, which also happens to be in the prime area of most video levels for an image. In the world of consumer displays this is an average response.</p>

<p>NORMAL Delta C Post-calibration (second attempt)<br />
<img alt="NORMAL Delta C Post-calibration (second attempt)" src="/images/reviews/PT-AE1000/normalDeltaCpost2.jpg" /></p>

<p>NORMAL D65 RGB Chart Post-calibration (second attempt)<br />
<img alt="NORMAL D65 RGB Chart Post-calibration (second attempt)" src="/images/reviews/PT-AE1000/normalRGBpost2.jpg" /></p>

<p>Look at all three RGB graphs and you will see red never flattens out and remains a humped response regardless. I will not say this was the last word on a proper color temperature calibration but looking at the forest rather than this one tree the meadow has more problems than this one.</p>

<p><br />
<B>Color Decoding (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=78">Definition</a>)</B><br />
Color encoding and decoding for real images creates a complex array of phase angles which can interact. It is possible to have correct color space and incorrect color decoding. Decoding is tested using patterns that provide complex phase angles. For this test I use the Sencore VP403 Color Decode, SMPTE Color Bars and the Accupel 100% and 75% Color Decoder patterns.</p>

<p>The product does not provide any means to isolate the red, green and blue color channels to professionally check color decoding.</p>

<p>The PT-AE1000 appeared to pass within the limitations and errors created when using color filters for this test.</p>

<p><br />
<B>Color Space (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=81">Definition</a>)</B><br />
Once color decoding is established then comes color space. There are various types of color space in the world with the American SMPTE C and European EBU being very similar and specified for standard definition mastering applications and broadcast studio monitoring. The new kid on the block is BT709 for HDTV which slightly expands the color space from standard definition. As to which one you should use or that I as a reviewer should reference has been debated heavily. For reviews I will be using HDTV BT709 color space. If the product provides color space management this also infers that you can calibrate for SMPTE-C or EBU if you desire unless stated otherwise.</p>

<p>HDTV BT709 Color Space Pre-calibration<br />
<img alt="HDTV BT709 Color Space Pre-calibration" src="/images/reviews/PT-AE1000/normalCSpre.jpg" /></p>

<p>HDTV BT709 Color Space Post-calibration<br />
<img alt="HDTV BT709 Color Space Post-calibration" src="/images/reviews/PT-AE1000/normalCSpost.jpg" /></p>

<p>For NORMAL using out of the box settings all three primaries fall way outside the HDTV color space. Currently this is being used as a sales marketing tool with tag lines of deep color, rich color, lifelike color, etc. While Deep Color is a new and valid technology, there are currently no sources available. This projector does not support the standard nor meet that vastly larger color space specification. In this case, and for a while to come, this is nothing but an artificial expansion of standard color space and while not accurate more or deeper color claims are perceptually valid.</p>

<p>This was by far the easiest alignment and it still wasn't all too easy. It provides this neat cursor that makes you think what you are adjusting is what you will get once you press enter but the projector goes through a processing sequence for your new target and you find that the target moved. Ultimately this became a song and dance of making a small adjustment and pressing enter to see where the target would end up. In the end I was able to achieve an overall satisfactory response but was unable to get the red on target for HDTV or EBU. If SMPTE-C is your target the red does line up quite well for that specification.</p>

<p><br />
<B>Y/C and RGB Color Timing (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=84">Definition</a>)</B><br />
It can be difficult to define the source of errors related to this element of performance. What we are looking for is a precision alignment of luminance and the color signal as well the individual red, green and blue channels within that color signal. When this is not set properly, edges form between color blocks reducing color definition as well as creating an artifact with the right images. For this test I use the Accupel color decoding pattern. Images do not reflect accurate color but you can still identify the primary or secondary colors.</p>

<p>Magenta and Green Error<br />
<img alt="Magenta and Green Error" src="/images/reviews/PT-AE1000/YCcolor.jpg" /></p>

<p>Reference Response for All Colors<br />
<img alt="Reference Response for All Colors" src="/images/reviews/PT-AE1000/YCcolorREF.jpg" /></p>

<p>The Panasonic shows a very clear and visible error with magenta and green in the 2-3 pixel range of size. The rest were fine. Note the error between magenta and green only occurs when magenta follows green (to the left of green) as well a difference in error between magenta and cyan depending on if it is on the right or left of green. These differences are not uncommon when an error shows itself. In the reference image you can slightly detect the same type of error between magenta and green, yet this was only 1 pixel in size with a marginal error in color allowing it to blend well, making it difficult to see from the viewing position.</p>

<p><br />
<B>Edge Enhancement (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=79">Definition</a>)</B><br />
Out of the box, the display had the notorious outlining of edges, but this was eliminated using the customer controls. This also brought the lack of detail to light. Without nice sharp edges coming from the pixels themselves, a correct setting created a soft look. I did find just a glimmer of edge enhancement helped with this perception as well as improved legibility with PC text.</p>

<p><br />
<B>Multi-source Ready (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=83">Definition</a>)</B><br />
As pointed out in the beginning the product has numerous settings, features and memories plus a number of inputs duplicated. This provides myriad ways to calibrate the product for different scan rates and input types, which is a good plus for the new user. What it does not provide are individual memories related to scan rate only, which would allow a single connection to the display and memory slots that automatically get loaded as you change the scan rate you are feeding it. For performance users, this should not create a problem provided you are feeding the display sources that meet video standards output at 1080i or 1080p and many performance users already have an external scaler to address those concerns.</p>

<p><br />
<B>Contrast Ratio</B><br />
This measurement is provided for the purpose of comparison only to other reviews of front projectors to illustrate true contrast ratios using a D65 calibrated color temperature using a 100IRE and 0IRE window pattern. With the dynamic iris feature, the Panasonic potentially comes with two measurements. When a projector provides that I measure the response with the dynamic iris turned off, this is a simple measurement of a 0IRE raster and 100IRE window after calibration. The probe is pointed towards the projector and moved towards it until .5fl has been obtained with a 0 IRE raster. Being able to obtain this contrast ratio on your screen will depend on how much of the light reflected off the screen gets reflected back to the screen by your room. This simple measurement does not account for the light in bright areas contaminating the black areas due to projector design and/or technology, which is called intrafield contrast ratio.</p>

<p>With the Dynamic Iris OFF, and using a calibrated D65 light output at 96 lamp hours, I obtained 367fl at 100IRE and .522fl for 0IRE yielding a contrast ratio of 703:1.</p>

<p>On the surface that is a good number. In reality it is somewhat poor because in a real system the perceived contrast is directly related to how black your blacks are. A CRT can easily have a lower contrast ratio but perceptually beat the pants off of a higher one because it can do inky jet blacks. In the world of performance front projection and micro display technology this is an average response.</p>

<p>Please refer to the article, <a href="/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php">HD Waveform 10 - Dynamic Iris and Gamma</a>, for an in depth look at this feature.</p>

<p><br />
<B>Light Output</B><br />
A light meter was not available to provide an accurate or meaningful number. Based on experience, this projector when calibrated is quite dim. 16:9 on my native 2.35 screen works out to 102" using a 1.4 gain. Using zoom to reduce the image to the smallest setting yielded 77" 16:9 and that was looking about right, but a bit more brightness would have been preferred. The problem here is the lamp only has about 75 hours on it and the light output will drop another 25% or more within the next 400 hours so. Being light challenged at this early stage in lamp life is not a good sign. Reverting back to manufacturer presets and some of the other setups, the screen became bright again with DYNAMIC providing plenty of punch as expected but visually that setting was error ridden which is also expected. 100" 16:9 is a commonly expected size in the home theater installation world so I would suggest a 2.0-3.0 gain screen in calibrated mode. Bear in mind screens in the 85-110" range typically vary from .8 to 1.3 based on the projector used for a committed room for a multitude of reasons; higher gains more often than not point to a unique situation. The out of box normal preset for color temperature and contrast did drive my 102" 1.4 gain to a pleasing level, which changes the perspective on lamp life and output.</p>

<p>Due to the extensive zoom range you have some variation in light output there as well. When set for the widest setting, the light path through the lens is spread out across the surface improving pass through efficiency. To take advantage of this will require the projector be placed quite close to the viewing screen. A disadvantage to that is that you take a hit in focus uniformity which means the edges will have a different focal point than the middle.</p>

<p><br />
<B>Lens Shift (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=87">Definition</a>)</B><br />
An installer will want to know where center is to reduce optical artifacts if you don't need the feature. Both lens shift controls provide a center detent position. There is play in these controls and they do not have a precision feel in your hand or on screen, but this is far better than the joystick approach Panasonic used in the past and with minimal tinkering you can get it very close if not right.</p>

<p><br />
<B>Noise</B><br />
This was an extremely quiet projector. Increasing <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=87">lamp power</a> will also increase fan speed but even that remained at an acceptable level. Throughout testing the lamp was set for normal.</p>

<p><br />
<B>Light Leakage / Stray Light</B><br />
This projector did not exhibit any light leakage.</p>

<p><br />
<B>Maintenance</B><br />
While all transmissive LCD projectors have filters to keep dust from getting into the optical cooling path, not all allow easy maintenance or replacement. The Panasonic does and recommends you do so every 100 hours. Due to this attribute of the system, it does not work well for the long term in some applications such as a bar or restaurant which could easily have tobacco smoke pumped through the optical cooling path. This may even be a worthy consideration in your home if you use tobacco or burn candles. An alternative to burning candles is to use a warmer if the scent is your intent. If your intent is for ambient lighting, purchase soot-free candles. Transmissive LCD is far from impervious to the environment in which it is used, and some have felt the sting of an expensive light engine to get the clean bright image back they once had. Remember my comment about LCD melt down? It is very important you keep this filter maintained! In the extreme, if the filter gets too much material in it, then the fan will begin to suck the finest debris through the filter, which can easily end up in your optics and appear on the screen as dots and smudges.</p>

<p>Lamp replacement will come. The great thing is the projector will tell you at 1800 hours that you ought to order one with a 30 second message when you turn it on. After 2000 hours the message remains until you make it go away by pressing any button. If mounted on the ceiling you will be pleased to hear the lamp cover is on the top (or in this case the bottom), which is the part you see when looking up! The customer menu allows you to reset the lamp timer.</p>

<p><br />
<B>Problems</B><br />
None</p>

<p><br />
<B>Subjective Viewing Results NORMAL</B><br />
Returning to King Kong, the calibration helped a lot in obtaining a response I am used to seeing. The color seemed overly saturated yet after hours profiling the product I just turned the color down a little. Blacks were slightly blue as expected from the RGB chart results. That same calibration reduced light output dramatically as discussed earlier. When using the PC, the iris had to be turned off lest it upset legibility. Even then there was some effort to read the screen. It was in this area of 1:1 pixel mapping that the Panasonic suffered. While I had little to complain about while gaming, using the internet was always a disappointment. Throughout it was noticeably flat in delivery rather than dynamic. Calibrated, it simply could not compete on any level with any of the calibrated DLPs in my stable. As for 1080p, it did look sharper and more detailed than my 720p DLP, yet compared to the 1080p DLP review projector it was incredibly soft, lacking in detail and dim. In the end I could not escape the loss in clarity, detail, dynamics and light output that my eye is accustomed to. While reducing screen size helped with the light output problem the projector could not escape its inherent limitations. Those same limitations had me turn on the dynamic iris feature to gain the perception of more light in exchange of other errors.</p>

<p>As a calibrator there is a point where you begin to realize that if science is not the products strength then you mix science and art for a better perceptual picture, which is exactly what I ended up doing for my large screen. I tried returning to the out-of-box setup conditions, which greatly improved things overall on a perceptual level, and is why insiders call that the "sales mode"! Knowing how wacky things were though I decided to keep the color temp settings and simply boost the contrast back up to 0 which definitely pointed things in the right direction without cyan whites. This did create a dramatic perceptual and real world improvement. Even in this mode my experience of better performing real world contrast ratios still left me disappointed with any scene that veered towards darkness. I was perceptually fooled only with bright images, yet even those retained a subtle flat look to them. Staying in this mode I watched The Devil Wears Prada on Blu-ray, Superman Returns on HD DVD and the first hour of West Side Story via HDNet and cable. All were entertaining. One strong characteristic came out though and that was a lack of color detail, creating a smeary poorly-focused look, very pronounced in saturated primary colors.</p>

<p><br />
<B>Subjective Viewing Results CINEMA 1</B><br />
As noted earlier, this mode and a few others engage a motorized function on the projector that dramatically reduces light output. While this may have the appearance and sound of yet another iris, I find nothing from the manual or website to substantiate that. Notably, nothing can be found about this noise or the visible result.</p>

<p>This was found on the website:<br />
Cinema 1 - Soft, smooth picture ideal for movies. Created under the supervision of David Bernstein, a leading Hollywood colorist.</p>

<p>In the brochure I found the following:<br />
<blockquote>Pure Color Filter Pro" for Professional-Level Color Reproduction. We equipped the PT-AE1000U with a specially developed optical filter that optimizes the light from the UHM projector lamp, helping to achieve deeper blacks while improving purity levels in the three primary colors (red, green and blue) that compose the image. It combines with our multilayered vacuum plating technology to create what we call the Pure Color Filter Pro. This advanced filter system improves color purity to such an extent that the color gamut is expanded nearly to the level specified in the Digital Cinema Initiatives (DCI)*4. To viewers at home, this means you see the kind of bright, vibrant colors and deep, rich blacks that make for great entertainment.</blockquote></p>

<p>The Color Filter Pro appears as the most logical candidate and seeing a picture of this optical device in the brochure it is also quite dark inferring a loss of light. Ultimately I could not test this setting at its full potential due to the enormous loss of light. That by itself will prevent it from looking comparable to any of the other displays I have in the house, regardless of technology or form. I decided to set up the product for a 77" screen and disregard the light problem. Images improved on many subtle levels, especially the dark scenes where instead of a blue black I had some black blacks and a better sense of color rendition. Based on what I was seeing, this mode appears to represent the product at its best. To take advantage of this mode will require a higher than normal gain screen with a small size and easily over 2.0 for larger screen sizes.</p>

<p><br />
<B>Day and Night Settings</B><br />
Over the last couple of years this has become a new feature in the control menu or on the remote for some displays that changes calibration settings based on ambient room light at the push of a button. Unfortunately many displays cannot do that and maintain relevant accuracy. The Panasonic is a surprising exception due to the Pure Color Filter Pro because not only is it a filter but it also acts like a manual iris reducing light output with the difference being you do not have a variable range. The difference in light output is significant enough that this projector could be implemented in a dark/medium room or medium/bright room. If this is an application you are seeking for this product I highly recommend you work with a professional to select the proper screen size and gain for optimal results. There are only a handful of products that can do dark/bright applications accurately; contact a professional.</p>

<p></p>

<p><B>Conclusion</B><br />
Based on features alone, the Panasonic excels at many things yet that bears little relation to final overall performance and that was the rub for this reviewer. I loved the motorized zoom and focus along with the vertical centering control for my zoom 2.35 application, which made changing back and forth nearly effortless. I am playing a PC game at the moment limited to 1024x768, and with the zoom I was able to fill out my screen while still retaining a native pixel map rather than using aspect controls, which destroy that very important performance attribute. I am going to miss that capability a lot. It gets most of the numbers right with the iris turned off and has good color management but it can't fix one native number and that is the inherent contrast ratio of transmissive LCD technology and the loss in light output. With video I preferred the errors of having the dynamic iris on. While it will pixel map there is clearly a loss in clarity at the single pixel level, another inherent trait of the technology and that artifact came out powerfully in my 1080p PC application. If you are looking for imaging science for the performance enthusiast, or reference imaging for mastering this not the right product for those applications. Having said that I must put CINEMA 1 in perspective; this is where imaging science is to be found, but you are going to have to use the oddball high gain screen and depending on your selection and viewing position that may cause sparklies and uniformity errors potentially taking a hit in detail which other products can fully deliver as well as good light output for common screens, so it does not make sense to this reviewer to use this projector for such an application. I would love to see Panasonic apply their engineering know-how to DLP technology providing a performance and entry level line of gear for front projection like they recently did for rear projection.</p>

<p>One potential confusing element in this story is the benefit of calibrating. It is clear that a full calibration correcting all aspects of the product is not going to be in your best interest unless you are shooting for the CINEMA 1 mode. That said I did find value by mixing some experience and art into the process. An ISF calibration does not stop at the display either covering your sources as well. In essence we are calibrating the complete video system from source to screen confirming you are getting the best response possible.</p>

<p><br />
<B>Putting It in Perspective</B><br />
If you want the better part of the 1080p wow factor, save some money and be able to put the projector where you want or need to then the PT-AE1000 should be towards the top of your list. The lens shift and zoom provides an ultra wide scope for placement and installation while maintaining image quality. The picture control feature set is inspiring. Most end users are likely to be first timers or casual viewers of the point-and-play variety. While perceptually great with video PC showed its limitations and only you can decide what is acceptable. It was severe enough that even casual viewers and first timers can easily pick up on it so this needs to be stated again.</p>

<p>If you are a performance enthusiast willing to go through the hassle of a correct installation and screen while keeping your viewing distance at 3.2 screen heights or more, why not consider imaging science 720p instead. Due to the rampage for 1080p those prices have dropped. A 1080p response is just one aspect of quality imaging and a close viewing distance is required for the payoff. You can still get the Samsung SPH710AE from $2,200-2,400 USD on line and that is a reference projector designed by one of the best known names in the business synonymous with performance; Joe Kane Productions! This is top notch performance on a budget. Bear in mind that for Samsung, this projector is considered an old model and out of stock. All that remains is what A stock (new factory sealed) is left in the distribution system or is being sold as B stock (refurbished).</p>

<p>If you are a 1080p performance enthusiast willing to go through the hassle of a correct installation and screen then why not spend $1-2K more and go with clearly better performance as well. Check the upcoming BenQ W10000 review or the Optoma HD81 which one of our authors recently purchased.</p>

<p>I have yet to review reflective LCD technology. Greg Rogers did a review on the Sony Pearl for Widescreen Review and there were some response similarities in the technical portion. Its reputation is also based on the dynamic iris technique.</p>

<p><br />
<B>Final Conclusion</B><br />
In the end I found this to be an entry level projector designed for an entry level installation. For most first time users and casual viewers this big screen 1080p experience is bound to have them grinning ear to ear and wondering why they waited. On that point, as a first timer or casual viewer, don't overlook another cost savings for the big screen experience in the form of the PT-AX100U at 720p, running anywhere from $1,300 - 2,200 USD. While not specifically reviewed, I suspect it will have a very similar response to its big 1080p brother.</p>

<p>Note: As this article went to publishing it was <a target="_blank" href="/forum/viewtopic.php?p=25340#25340">reported at HD Library</a> that Panasonic is providing a $1,000 USD rebate and one member found this projector for $2,749 USD. This is nearly half the price of projectors that can do better and taking all of its performance points into perspective this price level definitely hits the bang per buck category of entry level gear representing great value!<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>May  7, 2007  8:32 AM</b>
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
			<?=getComments(586)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 586)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/05/panasonic-ptae1000u-lcd-front-projector.php" type="text/javascript" charset="utf-8"></script>
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