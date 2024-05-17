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
		AND e.entry_id = 3731";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3731 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3731 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3731";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-three-for-dtvreception-february-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3731";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Three For DTV&hellip;Reception (February 2009)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Three For DTV&hellip;Reception (February 2009)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Three For DTV&hellip;Reception (February 2009)" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Three For DTV&hellip;Reception (February 2009)</title>
	<meta name="keywords" content="vhf uhf, antenna msrp, uhf indoor, indoor antenna, tune pro, antenna, vhf, figure, uhf, channel, ant, ’s, indoor, –, hdtva, dtv, signals, location, channels, stations, rca, test, msrp, reception, hdtv" />
	<meta name="description" content="Here’s a tale of three indoor digital TV antennas – low-priced, mid-priced, and high-priced. See how they fared in a side-by-side test." />
	<meta name="title" content="HDTV Expert - Product Review: Three For DTV&amp;hellip;Reception (February 2009)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Three For DTV&amp;hellip;Reception (February 2009)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-three-for-dtvreception-february-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Here’s a tale of three indoor digital TV antennas – low-priced, mid-priced, and high-priced. See how they fared in a side-by-side test." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3731', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-three-for-dtvreception-february-2009.php">HDTV Expert - Product Review: Three For DTV&hellip;Reception (February 2009)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April  8, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>
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
          <p>I recently had an opportunity to test indoor DTV reception at a potentially “tough” location in New York City. This particular apartment requires an indoor TV antenna and sits about 3.5 miles from the Empire State Building, alongside Central Park.</p>
<p>The apartment is on a lower floor and next to several tall buildings that contain lots of steel and glass in their outer structures. The challenge was to come up with a model that would provide reasonably strong signals with minimal multipath, looking through or positioned just below a couple of small windows that face west, looking out over the northern section of the park.</p>
<p>Seeing as how RCA had just sent me their <a href="http://66.77.167.109/rcaaccessories/RcaaccessoriesProductDetail.do?ACTION_TYPE_ID=ACTION_TYPE_PRODUCT_DETAIL&amp;ACTION_CATEGORY_IDSTR_CATEGORY_INDOOR_ANTENNAS&amp;ACTION_PRODUCT_ID=ANT1450B" onclick="javascript:pageTracker._trackPageview('/outbound/article/66.77.167.109');">ANT1450B amplified VHF/UHF panel antenna</a> (MSRP: $49.95), this seemed like a perfect location to give it a test drive. For more fun, I also packed up <a href="http://www.audiovox.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10001&amp;storeId=10001&amp;productId=14370&amp;langId=-1" onclick="javascript:pageTracker._trackPageview('/outbound/article/www.audiovox.com');">Terk’s HDTVa VHF/UHF indoor antenna</a> (MSRP: $59.95) and <a href="http://www.radioshack.com/product/index.jsp?productId=2103077" onclick="javascript:pageTracker._trackPageview('/outbound/article/www.radioshack.com');">Radio Shack’s “bare bones” 15-1874 VHF/UHF indoor antenna</a> (MSRP: $11.99), along with a spectrum analyzer to accurately see how each antenna was working.</p>
<p>For test receivers, I packed up the <a href="http://www.autumnwave.com/index.php/products/tv-tuners/onair-gt" onclick="javascript:pageTracker._trackPageview('/outbound/article/www.autumnwave.com');">AutumnWave OnAir Solution HDTV-GT</a> receiver (5<sup>th</sup> gen) and my Acer notebook PC, plus a new entrant to the set-top box field – <a href="http://auroramultimedia.com/" onclick="javascript:pageTracker._trackPageview('/outbound/article/auroramultimedia.com');">Aurora Multimedia’s V-Tune Pro HD</a> ATSC/NTSC/QAM/IPTV receiver (MSRP $1,299). This box has RS232 controls and supports both component video and HDMI outputs – plus, it’s LAN-ready for streaming video and updating software and hardware.</p>
<p>THE LOCATION</p>
<p>The test apartment is currently undergoing interior re-decorating, so I simply placed each antenna near one of the two small living room windows and peaked it for best analog TV reception on as many channels as possible. The quality of each channel varied considerably, as you can imagine – multipath was so bad on some channels that it was difficult to get any reliable NTSC signals.</p>
<p>I then did channel scans with both the V-Tune Pro HD and the HDTV-GT, to see how many signals locked up both receivers. MPEG stream analysis was also done with the HDTV-GT and TSReader Pro, so I could check modulation errors. The results were surprising, to say the least.</p>
<p>The active DTV stations I was trying to receive included WNYE-24, WNBC-28, WPXN-30, WPIX-33, WNJU-36, WWOR-38, WXTV-40, WNYW-44, WABC-45, WNJM-51, WCBS-56, and WNET-61. Some of these stations have very strong signals, and I can pick ‘em up at home, 65 miles away in eastern Pennsylvania. Others aren’t quite as loud.</p>
<p><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-1a.jpg"><img class="aligncenter size-full wp-image-450" title="Figure 1a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-1a.jpg" alt="" width="345" height="235" /></a></p>
<div id="attachment_451" class="wp-caption aligncenter" style="width: 609px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-1b.jpg"><img class="size-full wp-image-451" title="Figure 1b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-1b.jpg" alt="" width="599" height="560" /></a><p class="wp-caption-text">Figures 1a-b. Radio Shack’s 15-1874 “budget” VHF/UHF indoor antenna in a formal pose (top) and in action (bottom).</p></div>
<p>RADIO SHACK 15-1874</p>
<p>This antenna is about as simple as it gets. It consists of a small plastic base with a metal bottom, a thin-wire UHF loop that snaps into place, and a pair of thread-on, telescoping VHF rabbit ears. The 15-1874 is the kind of antenna many folks might use with NTIA DTV converter boxes, to replace their old, broken rabbit ears.</p>
<p>After peaking for best analog reception, I did a channel scan and was able to pull in 7 of 13 stations currently broadcasting digital TV signals from the Empire State Building, 4 Times Square, or other locations. For what it’s worth, two of the stations that didn’t make the grade (WNJU-36 and WNJM-51) currently broadcast from towers in New Jersey, and were just too weak to be picked up even though I spotted ‘em on the analyzer.</p>
<div id="attachment_453" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-2a1.jpg"><img class="size-medium wp-image-453 " title="Figure 2a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-2a1-300x241.jpg" alt="" width="300" height="241" /></a><p class="wp-caption-text">Figure 2a. Qualcomm’s MediaFLO service on UHF channel 55 (left waveform) and WCBS-DT on channel 56 (right waveform), as received by the 15-1874.</p></div>
<p style="text-align: center;">
</p><div id="attachment_456" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-2b1.jpg"><img class="size-medium wp-image-456" title="Figure 2b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-2b1-300x240.jpg" alt="" width="300" height="240" /></a><p class="wp-caption-text">Figure 2b. DTV waveforms from WNYW-44 (left) and WABC-45 (right), as grabbed by the Radio Shack antenna. Note the strong tilt on WABC’s signal.</p></div>
<div id="attachment_457" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-2c1.jpg"><img class="size-medium wp-image-457" title="Figure 2c" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-2c1-300x238.jpg" alt="" width="300" height="238" /></a><p class="wp-caption-text">Figure 2c. WWOR’s digital signal on channel 38 was problematic, and that big notch in the middle of the 8VSB waveform was the reason – it kept fluctuating up and down.</p></div>
<p>Of the remaining stations, one (WNET-61) is operating with very low power and is beaming its signal west towards Newark, NJ – its city of license. I could see it on the analyzer, but it was just too weak to pull in. (WNET will go back to VHF channel 13 after the analog shutdown, and should be plenty strong in the metro NY area, based on tests conducted in early January.)</p>
<p>The other two stations (WPXN-30 and WWOR-38) just had tricky multipath that the RS-1874 couldn’t do anything about. After all, it’s basically a dipole antenna on UHF with little directivity. I don’t expect the rabbit ears to make that much difference with high-band VHF channels, either. Still, for $12, this antenna did a fine job and is a low-cost solution for city dwellers that live 10 or fewer miles from the transmitter site(s).</p>
<p style="text-align: center;"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-3a1.jpg"><img class="aligncenter size-full wp-image-461" title="Figure 3a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-3a1.jpg" alt="" width="600" height="585" /></a></p>
<div id="attachment_462" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-3b1.jpg"><img class="size-full wp-image-462" title="Figure 3b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-3b1.jpg" alt="" width="600" height="589" /></a><p class="wp-caption-text">Figure 3a-b. RCA’s ANT1450B in a beauty shot (top) and on the front line (bottom).</p></div>
<p>RCA ANT1450B</p>
<p>I’d tested the non-amplified version of this antenna <a href="../pages_c/RCA_ANT1500.html">(ANT1500)</a> back in the late summer, and found it wanting for indoor reception at my location. The ANT1450B also uses a similar etched strip-line VHF/UHF antenna design, but included an in-line amplifier module to boost overall signals levels.</p>
<p>Given that my home location is 23 miles and over a hill to the Philadelphia antenna farm, I figured the New York location would be a kinder test of the RCA’s abilities. Once again, I positioned it near one of the windows and peaked it for best NTSC reception, and then did a channel scan.</p>
<div id="attachment_464" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4a.jpg"><img class="size-medium wp-image-464" title="Figure 4a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4a-300x246.jpg" alt="" width="300" height="246" /></a><p class="wp-caption-text">Figure 4a. WCBS’ digital signal on channel 56 was a real challenge for the ANT1450B.</p></div>
<div id="attachment_465" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4b.jpg"><img class="size-medium wp-image-465" title="Figure 4b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4b-300x246.jpg" alt="" width="300" height="246" /></a><p class="wp-caption-text">Figure 4b. WNYW-44 (left) and WABC-45 (right) looked a bit better through the RCA antenna.</p></div>
<div id="attachment_466" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4c.jpg"><img class="size-medium wp-image-466" title="Figure 4c" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4c-300x242.jpg" alt="" width="300" height="242" /></a><p class="wp-caption-text">Figure 4c. WNYE-24 had a booming signal at the reception location.</p></div>
<p>The results? Without the companion amplifier, the ANT1450B pulled in 6 of the 13 available DTV stations, once again skipping WNET-61. It also missed WPXN-30, WNJU-36, WWOR-38, WFUT-53, and WCBS-56. This antenna is just as non-directional as the 15-1874, and equally susceptible to multipath. With re-positioning, I was able to pull in WCBS-56, but dropped WABC-45 and WPIX-33.</p>
<p>Adding the amplifier accomplished two things. First, I was now able to add WFUT-53 and WCBS-56 to my original list, although the latter channel showed “hits” now and then. Second (and unfortunately), the noise floor on VHF channels 7 through 13 was elevated by 20 dB! That’s not a good development, and one that spells trouble for WABC, WPIX, and WNET when they go back to their original high-band VHF channels 7, 11, and 13, respectively.</p>
<p><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-5a.jpg"><img class="aligncenter size-full wp-image-467" title="Figure 5a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-5a.jpg" alt="" width="560" height="426" /></a></p>
<div id="attachment_468" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-5b.jpg"><img class="size-full wp-image-468" title="Figure 5b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-5b.jpg" alt="" width="600" height="558" /></a><p class="wp-caption-text">Figure 5a-b. Terk’s HDTVa antenna looks aerodynamic just sitting still (top) and like it’s ready for takeoff when in use (bottom).</p></div>
<p>TERK HDTVa</p>
<p>This antenna continues to impress me, although its UHF section isn’t much of a mystery – it’s the Antiference Silver Sensor, coupled to an internal amplifier. The VHF element is a bit more pedestrian, with a pair of telescoping rabbit ears. They are robustly built, though.</p>
<p>After waiting for the usual channel scan, I discovered both the Aurora and OnAir receivers had logged 12 of 13 DTV stations (nope, still no sign of WNET-61). More importantly, only two (WPIX-33 and WPXN-30) showed any signs of “hits” from time to time. Impressively, I could now watch WNJU-36 and WNJM-51, previously missing in action.</p>
<div id="attachment_469" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-6a.jpg"><img class="size-medium wp-image-469" title="Figure 6a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-6a-300x228.jpg" alt="" width="300" height="228" /></a><p class="wp-caption-text">Figure 6a. WWOR-38 came in beautifully through the HDTVa.</p></div>
<div id="attachment_470" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-6b.jpg"><img class="size-medium wp-image-470" title="Figure 6b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-6b-300x227.jpg" alt="" width="300" height="227" /></a><p class="wp-caption-text">Figure 6b. WNBC-28’s 8VSB waveform, although ragged, was rock-steady with the Terk.</p></div>
<div id="attachment_471" class="wp-caption aligncenter" style="width: 310px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-6c.jpg"><img class="size-medium wp-image-471" title="Figure 6c" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-6c-300x248.jpg" alt="" width="300" height="248" /></a><p class="wp-caption-text">Figure 6c. WNYW-44 and WABC-45 looked best with the HDTVa.</p></div>
<p>Although the HDTVa is vastly more directional than either the Radio Shack or RCA designs, its performance could be even better if it had a reflector behind its rear element. WPIX’ channel 33 waveform showed some pretty funky notches, and WPXN could have used a bit more signal overall. I also noticed hits on other channels that seemed to be tied to the passage of busses and trucks in the street below, but these primarily affected upper UHF channels (53, 56) that won’t be in use after June 12.</p>
<p>As well as the HDTVa performed, it also raised the high-band VHF noise floor by 20 dB or so, indicating the presence of some type of broadband RF emitter nearby. Perhaps that was a computer, or a security system sensor. (I’ve even seen high-band VHF RF emissions from a hand-held HD camcorder, believe it or not!)</p>
<div id="attachment_472" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-7a.jpg"><img class="size-full wp-image-472" title="Figure 7a" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-7a.jpg" alt="" width="600" height="267" /></a><p class="wp-caption-text">Figure 7a. Here’s what the normal nose floor looked like underneath VHF channels 7, 9, 11, and 13.</p></div>
<div id="attachment_473" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-7b.jpg"><img class="size-full wp-image-473" title="Figure 7b" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-7b.jpg" alt="" width="600" height="258" /></a><p class="wp-caption-text">Figure 7b. And here’s what the RCA and Terk amplifiers did to it – raise it up by 20 dB!</p></div>
<h2>
</h2><p></p><div id="attachment_474" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-8.jpg"><img class="size-full wp-image-474" title="Figure 8" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-8.jpg" alt="" width="600" height="285" /></a><p class="wp-caption-text">Figure 8. Aurora Multimedia’s V-Tune Pro HD did a creditable job pulling in the test DTV signals. </p></div>
<p>CONCLUSIONS</p>
<p>My tests at this site aren’t yet complete, and another round of testing will include antennas with improved directivity to help minimize multipath. But if I had to go with one of the test antennas, I’d pick the Terk HDTVa. It did the best overall job on UHF DTV and analog VHF signals, and the internal amplifier (although not a low-noise design) does make a difference – plus, it works a lot better than the in-line amp module RCA ships with their ANT1450B.</p>
<p>I was very impressed at how well the RS 15-1874 worked, but given its traditional design, a lot of the credit must go to the OnAir HDTV-GT and Aurora’s V-Tune Pro. Stand-along HDTV set-top boxes are getting harder to find these days, and one that’s integrator-ready like the V-Tune Pro are rare. It works very well, and its receiver is even a bit better with tricky signals than the Gen 5 HDTV, now two years old.</p>
<p>As for RCA’s ANT1450B, it would appear to work best in a location where it has a clear shot towards a transmitting antenna. Handling multipath is not its strong suit, but what can you expect from what amounts to a pair of folded loop antennas, mounted inside of each other’s radius? I’d skip the in-line amplifier unless you live in a less congested area – too much garbage gets pulled in and winds up degrading the noise figure of the receiver.</p>
<p><strong>Radio Shack 15-1874 </strong></p>
<p><strong>Budget VHF/UHF Indoor Antenna</strong></p>
<p><strong>MSRP: $11.99</strong></p>
<p><strong><a href="http://tinyurl.com/2ml5re" onclick="javascript:pageTracker._trackPageview('/outbound/article/tinyurl.com');">http://tinyurl.com/2ml5re</a></strong></p>
<p><strong> </strong></p>
<p><strong>RCA ANT1450B</strong></p>
<p><strong>Amplified VHF/UHF Indoor Antenna</strong></p>
<p><strong>MSRP: $49.95</strong></p>
<p><strong><a href="http://tinyurl.com/b7ksnr" onclick="javascript:pageTracker._trackPageview('/outbound/article/tinyurl.com');">http://tinyurl.com/b7ksnr</a></strong></p>
<p><strong> </strong></p>
<p><strong>Terk HDTVa</strong></p>
<p><strong>Amplified VHF/UHF Indoor Antenna</strong></p>
<p><strong>MSRP: $59.95</strong></p>
<p><strong><a href="http://tinyurl.com/arntk" onclick="javascript:pageTracker._trackPageview('/outbound/article/tinyurl.com');">http://tinyurl.com/arntk</a></strong></p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April  8, 2010  1:31 PM</b>
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
			<?=getComments(3731)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3731)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-three-for-dtvreception-february-2009.php" type="text/javascript" charset="utf-8"></script>
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