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
		AND e.entry_id = 4579";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4579 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4579 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4579";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-507-hdtv-buying-guide.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4579";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide</title>
	<meta name="keywords" content="street price, hdtv street, lcd hdtv, buying guide, plasma hdtv, hdtv, inch, price, street, lcd, led, panasonic, list, buying, plasma, picture, technology, ’s, light, ”, video, usb, glasses, guide, inches" />
	<meta name="description" content="If we didn&amp;acirc;��t spend enough of your money with the Receiver Buying Guide on Episode 505 and you still have some budget left, you&amp;acirc;��re in luck.  Today we&amp;acirc;��ll give you our choices for what HDTV to buy in several different price categories.  Of course screen size goes up as prices go up, just as you&amp;acirc;��d expect. And similar to the Receiver Buying Guide, this guide isn&amp;acirc;��t about getting the latest and greatest. Its about getting a good value for your money and something you&amp;acirc;��ll enjoy for years to come." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-507-hdtv-buying-guide.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="If we didn&amp;acirc;��t spend enough of your money with the Receiver Buying Guide on Episode 505 and you still have some budget left, you&amp;acirc;��re in luck.  Today we&amp;acirc;��ll give you our choices for what HDTV to buy in several different price categories.  Of course screen size goes up as prices go up, just as you&amp;acirc;��d expect. And similar to the Receiver Buying Guide, this guide isn&amp;acirc;��t about getting the latest and greatest. Its about getting a good value for your money and something you&amp;acirc;��ll enjoy for years to come." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4579', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-507-hdtv-buying-guide.php">HDTV and Home Theater Podcast - Podcast #507: HDTV Buying Guide</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>December  1, 2011</b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>HDTV Buying Guide</h3>
<p>If we didn’t spend enough of your money with the Receiver Buying Guide on <a href="http://www.htguys.com/podcasts/2011/11/18/podcast-505-black-friday-2011-and-receiver-buying-guide.html">Episode 505</a> and you still have some budget left, you’re in luck.  Today we’ll give you our choices for what HDTV to buy in several different price categories.  Of course screen size goes up as prices go up, just as you’d expect. And similar to the Receiver Buying Guide, this guide isn’t about getting the latest and greatest. Its about getting a good value for your money and something you’ll enjoy for years to come.</p>
<p>Before we get going we want to recap last year’s list. In our greater than 60 inch we had a non 3D Sharp Aquos for $1695. Under our Ultimate Christmas gift we had the Panasonic 65 inch VT25 coming in at a whopping $4,500. Today that same TV can be had for <a href="http://www.htguys.com/shop?id=B003N3BV90">$2750</a>. In one year it goes from Ultimate to affordable. Likewise the 58 inch VT25 Ara bought set him back $2700 last year and now it can be had for <a href="http://www.htguys.com/shop?id=B003N3BV5O">$2500</a>. Not nearly as big a decline. Lastly we had one DLP from Mitsubishi on the list. A 82 inch that came in at $3,200. Today it goes for <a href="http://www.htguys.com/shop?id=B003I4YMOK">$2,800</a>.</p>
<h4>Less than $500</h4>
<p><a href="http://www.htguys.com/shop?id=B005450YTS">Magnavox 19ME601B/F7 19-Inch 720p LCD TV (Street Price $149)</a><br />
If you missed Black Friday but still need a small TV for your kitchen, garage or maybe a bathroom, this Magnavox is priced pretty close to the door-busters some stores were running.  You can save yourself the hassle of the crowds and avoid being pepper sprayed by just grabbing this HDTV instead.</p>
<ul>
<li>LED Backlight High Definition LCD Display</li>
<li>Convenient Side Input Panel with HDMI</li>
<li>Stereo Speakers / 5 Band Equalizer / Auto Volume Leveling</li>
<li>USB for JPEG Playback</li>
</ul>
<p><a href="http://www.amazon.com/gp/product/B004HYG9SM?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004HYG9SM&amp;ref_=sr_1_1&amp;qid=1322752970&amp;sr=8-1">Sony BRAVIA KDL32BX320 32-Inch 720p LCD HDTV (Street Price $348)</a><br />
Sure this is 720p, but at 32 inches, you really don’t need more resolution.  The TV has excellent picture quality and will do a wonderful job wherever you need a TV of this size, be it a bedroom, dorm room or bonus room.</p>
<ul>
<li>Five separate HD inputs (two HDMI, two HD component, one PC)</li>
<li>USB port for enjoying photos or MP3s from your USB devices</li>
<li>Light Sensor adjusts picture brightness based on ambient light</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B005JK01GO">LG 42LV4400 42-Inch 1080p 120Hz LED-LCD HDTV (Street Price $499)</a><br />
Can you believe a 42” 1080p, LED 120Hz TV for under $500?  Neither can we.  But it’s for real.  Sure it barely makes the list, but that’s impressive in itself.</p>
<ul>
<li>LG&#8217;s LED technology provides a slim profile and delivers amazing brightness, clarity and color detail, as well as greater energy efficiency compared to conventional LCD TVs.</li>
<li>TruMotion 120Hz technology lets you see sports, video games and high-speed action with virtually no motion blur</li>
<li>Full HD 1080p gives it superior picture quality over standard HDTV. You&#8217;ll see details and colors like never before.</li>
<li>Image Contrast Ratio: 100,000:1</li>
</ul>
<p>&nbsp;</p>
<h4>$500 &#8211; $1000</h4>
<p><a href="http://www.htguys.com/shop?id=B004LACPFS">LG 50PT350 50-Inch 720p 600Hz Plasma HDTV (Street Price $599)</a><br />
As a general rule in life, if you have the opportunity to pick up a 50” plasma for under $600, do it.  Don’t ask questions, just pull the trigger.  You’ll be glad you did.  Sure this is a 720p TV, but that’s why you can get it for such a steal. It’s going to be an amazing picture, and for the price, you simply can’t go wrong.</p>
<ul>
<li>50-inch Plasma HDTV panel with Full HD 1080p resolution and 3,000,000:1 dynamic contrast ratio</li>
<li>600Hz Sub Field Driving virtually eliminates motion blur, for crystal-clear motion video in sports, games, etc.</li>
<li>Enjoy media from connected USB devices with support for a wide range of video, music, and image files</li>
<li>Intelligent Sensor automatically optimizes the picture for the lighting and color conditions in the viewing room</li>
<li>TruSlim Frame is less than an inch wide, for viewing without the distraction of a larger frame</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B005VOL9MI">Samsung UN46D6003 46-Inch 1080p LED HDTV &#8211; Black (Street Price $799)</a><br />
We’ve been fans of Samsung HDTVs since they dominated DLP back in the day.  Now they are hitting it out of the park with LCD and this set is no different.  It has enough specs to keep you happy for a long time, as long as 3D isn’t your thing.</p>
<ul>
<li>1080p resolution</li>
<li>5.0 Energy Star rating</li>
<li>Smart TV ready</li>
<li>Contrast Ratio: 4,000,000:1</li>
</ul>
<p>&nbsp;</p>
<h4>Greater than $1000</h4>
<p><a href="http://www.htguys.com/shop?id=B004VD2LHC">Westinghouse VR-6025Z 60-Inch 1080p 120 Hz LCD HDTV (Street Price $1219)</a><br />
This set made the list because, first of all, it’s a good set.  Secondly, 60” HDTV for $1200?  Really?  It has 4 or 5 stars at Amazon (Best online review quote: “TV was as advertised, easy to hook up and looks awesome. Very clear picture and sound is good. Now if only it would make the BEARS WIN!!!!”)</p>
<ul>
<li>Ultra slim design (4-5/8&#8243; deep)</li>
</ul>
<p><a href="http://www.htguys.com/shop?id=B005DRBDY2">LG 55LW5300 55-Inch 1080p 120Hz Cinema 3D LED-LCD HDTV with 3D Blu-ray Player and Four Pairs of 3D Glasses (Street Price $1369)</a><br />
We had to throw at least one 3D TV in the list, and LG is one of the few manufacturers selling passive 3D, so we had to go with theirs.  This set not only gets you the 55” 1080p LED HDTV but also includes the Blu-ray player and 4 pairs of glasses in the bundle.  And if 4 isn’t enough, don’t worry, these glasses are really inexpensive.</p>
<ul>
<li>LG&#8217;s Cinema 3D technology delivers flicker free, wide angle viewing through use of lightweight, inexpensive, battery free glasses (included: four pairs of glasses).</li>
<li>Enjoy amazing depth along with smoother, crisper images, and a clear picture from virtually any angle.</li>
<li>Enjoy your favorite 2D movies and shows in immersive 3D with LG&#8217;s 2D to 3D conversion feature.</li>
<li>LG&#8217;s LED Plus back-light technology provides even greater control of brightness through local dimming that delivers better contrast.</li>
<li>TruMotion 120Hz technology lets you see sports, video games and high-speed action with virtually no motion blur</li>
</ul>
<p><a href="http://www.amazon.com/gp/product/B004MME75Q?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004MME75Q&amp;ref_=sr_1_1&amp;qid=1322753211&amp;sr=8-1">Panasonic VIERA TC-P65GT30 65-Inch 1080p 3D Plasma HDTV (Street Price $2,349)</a><br />
If you really want the Mac Daddy of HDTVs, Panasonic Plasma is still the way to go.  Sure the VT30 has better features and slightly better specs, but you can save $800 by buying this one and get a nearly identical viewing experience.  This one is 3D also, but let’s be honest, that’s not why you’re buying it.</p>
<ul>
<li>Infinite Black 2 Panel</li>
<li>VIERA Connect Wi-Fi Ready</li>
<li>THX Mode</li>
<li>DLNA Certified</li>
</ul>
<p>&nbsp;</p>
<h4>HT Guys Ultimate Christmas Present</h4>
<p><a href="http://www.amazon.com/gp/product/B004NPND20?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004NPND20&amp;ref_=sr_1_1&amp;qid=1322753977&amp;sr=8-1">Panasonic VIERA TC-P65VT30 65-inch 1080p 3D Plasma HDTV (Street Price $3,000)</a><br />
This TV has everything Ara loves about his plasma and more. Word we hear is that this TV uses some of the technology Panasonic bought from Pioneer to create the deepest blacks around. Panasonic calls it the Infinite Black Pro 2 Panel. If you are buying it for 3D be warned Panasonic cheaps out and only provides one pair.</p>
<ul>
<li>One Sheet of Glass Design</li>
<li>VIERA Connect WiFi Ready</li>
<li>THX 3D Certified Display</li>
</ul>
<p>&nbsp;<br />
<a href="http://www.amazon.com/gp/product/B005LYRYNG?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B005LYRYNG&amp;ref_=sr_1_1&amp;s=electronics&amp;qid=1322754328&amp;sr=1-1">Sharp AQUOS LC-80LE632U 80&#8243; LED-LCD TV &#8211; 16:9 &#8211; HDTV 1080p &#8211; 1080p &#8211; 120 Hz (Street Price $4,430)</a><br />
At 80 inches who needs a projector! The TV only weighs 121 pounds (55 Kgs) and can easily be mounted on a wall. It uses as much energy as three 100W light bulbs. It has built in Wifi and supports Netflix, Vudu, and CinemaNow streaming services. A built-in media player allows for playback of video, music, and photos via the USB port.</p>
<p><a href="http://www.amazon.com/gp/product/B004ZL2O9U?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B004ZL2O9U&amp;ref_=sr_1_5&amp;s=electronics&amp;qid=1322754688&amp;sr=1-5">Mitsubishi WD-92840 92-Inch 1080p Projection TV (Street Price $4680)</a><br />
What list would be complete without a DLP. We keep wondering if this will be the last year that a DLP makes the list. The previous TV is a great candidate to replace a projector for those with limited room. If you have a large space then this TV may be the way to go. It supports 3D and has a built in 16 channel sound bar and supports Vudo online streaming. From a size perspective the TV measures 81 inches wide by 55 inches high and 25 inches deep and it only weighs 194 pounds. It uses less energy than the 80 inch AQUOS, by one 100W light bulb</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2011-12-02.mp3">Download Episode #507</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>December  1, 2011 11:11 PM</b>
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
			<?=getComments(4579)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4579)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2011/12/hdtv-and-home-theater-podcast-podcast-507-hdtv-buying-guide.php" type="text/javascript" charset="utf-8"></script>
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