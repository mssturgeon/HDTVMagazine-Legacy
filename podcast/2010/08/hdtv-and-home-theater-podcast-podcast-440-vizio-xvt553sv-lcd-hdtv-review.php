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
		AND e.entry_id = 3924";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3924 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3924 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3924";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-440-vizio-xvt553sv-lcd-hdtv-review.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3924";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review</title>
	<meta name="keywords" content="vizio xvt, blu ray, remote control, local dimming, lcd hdtv, vizio, internet, hdtv, picture, see, remote, apps, watching, good, lit, well, plasma, any, dimming, blu, ray, control, xvt, netflix, setup" />
	<meta name="description" content="With football season just around the corner you may be in the market for a new HDTV to watch your favorite team try and make it to the Superbowl. We’ve seen a few technologies come and go and each of them try to produce a picture that’s as good as plasma. The latest is LED. The Vizio XVT553SV will run you about $1900 for the 55 inch version and its loaded with all kinds of goodies that we can finally say that Plasma may have finally met its match." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-440-vizio-xvt553sv-lcd-hdtv-review.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="With football season just around the corner you may be in the market for a new HDTV to watch your favorite team try and make it to the Superbowl. We’ve seen a few technologies come and go and each of them try to produce a picture that’s as good as plasma. The latest is LED. The Vizio XVT553SV will run you about $1900 for the 55 inch version and its loaded with all kinds of goodies that we can finally say that Plasma may have finally met its match." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3924', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-440-vizio-xvt553sv-lcd-hdtv-review.php">HDTV and Home Theater Podcast - Podcast #440: Vizio XVT553SV LCD HDTV Review</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>August 26, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=493&category=LCD">LCD</a></b>, <b><a href="/category.php?id=510&category=LCD HDTVs">LCD HDTVs</a></b>, <b><a href="/category.php?id=484&category=LED (LCD)">LED (LCD)</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=487&category=Plasma">Plasma</a></b>, <b><a href="/category.php?id=505&category=Plasma HDTVs">Plasma HDTVs</a></b>, <b><a href="/category.php?id=381&category=Sports">Sports</a></b>
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
<h3>Vizio XVT553SV LCD HDTV Review</h3>
<p><a href="http://www.htguys.com/shop?id=B003GDHI12">Order Now, $1900</a><br />
With   football season just around the corner you may be in the market  for a   new HDTV to watch your favorite team try and make it to the   Superbowl.  We’ve seen a few technologies come and go and each of them   try to  produce a picture that’s as good as plasma. The latest is LED.   The Vizio  XVT553SV will run you about $1900 for the 55 inch version and   its  loaded with all kinds of goodies that we can finally say that   Plasma may  have finally met its match.</p>
<h4>Features:</h4>
<ul>
<li>VIA – VIZIO Internet Apps™</li>
<li>Built-in Wi-Fi 802.11n Simultaneous Dual-Band Wireless</li>
<li>Customized Bluetooth Universal Remote Control</li>
<li>TRULED™ Full Array LCD HDTV with Smart Dimming</li>
<li>240Hz Refresh Rate</li>
<li>1080p Full HD</li>
<li>SRS TruVolume</li>
<li>EcoHD</li>
</ul>
<p>The  Vizio XVT553SV is a beautiful TV. Fit and finish is quite good.  Vizio  has come a long way in this area. Its gloss black finish will  look good  in any room that its placed in. Its a direct back lit LED so  its a bit  thicker than some of the slim edge lit TVs you can get on the  market.  Don’t let that affect your decision, the TV is just under 3  inches (~7.6  CM) thick.  Because the TV is direct lit it can use local  dimming to  increase contrast ratio and create deeper blacks. The LED  array is  comprised of 120 zones.  Vizio has done a great job with the   connectivity aspect of the TV as well. More on that later.</p>
<h4>Setup</h4>
<p>HDTVs  are more complicated to setup when compared to their old  analog  counterparts but Vizio has made setting up the 553 simple. The  first  time your TV fires up as setup application is automatically  launched  takes you through the setup procedure for the Bluetooth remote  control,  how to connect to the Internet, add channels (if applicable)  through its  built-in tuner, and connect source components and program  the remote  control to operate those components. All told plan on an  hour here. No  so much for actually performing the tasks but to play  around once you  get it up and running. By the way, having a full QWERTY  keyboard makes  tasks like joing a wifi network much easier!</p>
<p>Our   TV was connected to a switching receiver for Blu Ray, Sat TV, and a  Mac  Mini. With the Internet connection the 553 had access to Vudu,  Netflix,  and web based videos as well.</p>
<h4>Performance</h4>
<p>This  was another area where we were pleasantly surprised. Out of the  box the  TV produced a beautiful picture when on standard or movie  modes. But  when we calibrated it (see settings at the end of the  review) it really  shined. Colors were vibrant and daylight scenes were  exceptional. It was  like looking through a window to the world. If you  like sports this TV  will produce a clear sharp picture that will bring  out all the subtle  details of anything you are watching.</p>
<p>Dark   scenes that usually lose detail were no problem for this TV either.   ViZio use the useless Dynamic Contrast Ratio in their specs so we won’t   quote that here. What we found was that the TV could reproduce subtle   differences in the picture that our DLPs couldn&#8217;t. The screen is less   reflective than glass we still found it necessary to limit the light in   the room to enhance the darker scene detail. You will want to make sure   to use local dimming. Local dimming is what allows the TV to produce   deep blacks. We wonder why the option to turn it off even exists.</p>
<p>Blu   Ray movies looked spectacular. Vibrant colors sharp deatil and natural   looking skin tones made the experience better than going to a theater.    We watched in both 24 fps and 60 fps modes and found video to be  smooth  regardless. It could have been a function of the content we were   watching (Band Of Brothers, Spider Man, and Close Encounters) but we   didn’t see any real difference between the two modes. If you can see   judder and you have a Blu Ray player that outputs 24 fps you will be   pleased with watching Blu Ray movies on this TV.</p>
<p>The  TV  produces a uniform brightness across the screen. This is something  that  edge lit LEDs have trouble with. With edge lit screens the  luminance  is brighter at the edges. Because of the direct LED array the  Vizio can  produce better contrast and thus more detail especially in the  darker  scenes. We did not see any blooming while watching regular HDTV   programming however, while watching letter boxed content there we a   minor amount of light bleeding into the black at the top and bottom of   the screen. Not really an issue for normal viewing. Off angle viewing is   good and close to plasma performance.</p>
<h4>VIZIO Internet Apps (VIA)</h4>
<p>We’ve  talked about Internet enabled TVs for about a year. We’ve not  been too  keen on any implementations however. That has changed with  Vizio  Internet Apps. The main reason for the change is the remote  control.  With the slide out full QWERTY keyboard, entering data becomes  straight  forward. The keyboard connects with the TV via Bluetooth and  takes a  little getting used to but it works well. In fact this remote  is the  first one that we recommend over the Harmony. I think we see a  new model  in Harmony’s future. Until Harmony come out with a compatible  remote  you’ll need to keep this around.</p>
<p>The  apps are where  this TV really adds value. Netflix works well and we  found the picture  to be as good as the Xbox 360, which we thought had  the best Netflix  picture. Amazon HD looks and sounds great as well. Same  for Vudu. But  what we thought was incredible was that all this content  was coming to  us via wifi. Many look at Internet connected TVs and say  big deal, I  don’t have a network connection near my TV. With the Vizio  its not a  problem. We had a solid connection and did not see any  skipping due to  network congestion.</p>
<p>Apps  loaded quickly and were usable. The  Facebook and Twitter implementation  looked like it belonged on the  screen, not just an afterthought. If you  have a computer phobic loved  one in your family this might be the  easiest way to get them on  Facebook. If you like looking at pictures of  friends and family this TV  is a great way to view them in a family  format. The apps include:</p>
<ul>
<li>Netflix</li>
<li>NBA Game Time</li>
<li>Amazon Video On Demand</li>
<li>WikiTV Encyclopedia</li>
<li>Ebay</li>
<li>Flickr</li>
<li>Pandora</li>
<li>Rhapsody</li>
<li>Twitter</li>
<li>Vudu</li>
<li>Yahoo Widgets</li>
</ul>
<h4>Bottom Line</h4>
<p>Vizio  has come a long way since the early days of HDTV. Ara was set  on buying  a Plasma before he spent some time with 553. The performance  is almost  what you would expect from a plasma without the issues of  degrading  black levels. Throw in functional and more importantly usable  Internet  Apps in a 55 inch package for less than $2,000, we feel  buying this TV  is a no brainer!</p>
<p><strong>Calibration Settings:</strong><br />
Picture Mode &#8211; Move<br />
Backlight  - 15<br />
Brightness &#8211; 50<br />
Contrast &#8211; 49<br />
Color &#8211; 47<br />
Tint &#8211; 0<br />
Sharpness &#8211; 3<br />
Color Temperature &#8211; Normal<br />
Red Gain &#8211; 125<br />
Green Gain &#8211; 128<br />
Blue Gain &#8211; 120<br />
Red Offset &#8211; 123<br />
Green Offset &#8211; 128<br />
Blue Offset &#8211; 125<br />
Smooth Motion Effect &#8211; off<br />
Real Cinema &#8211; off<br />
Noise Reduction &#8211; off<br />
Color Enhancement &#8211; off<br />
Adaptive Luma &#8211; off<br />
Film Mode &#8211; Auto<br />
Smart Dimming On<br />
Ambient Light Sensor &#8211; off</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2010-08-27.mp3">Download Episode #440</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>August 26, 2010 11:22 PM</b>
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
			<?=getComments(3924)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3924)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2010/08/hdtv-and-home-theater-podcast-podcast-440-vizio-xvt553sv-lcd-hdtv-review.php" type="text/javascript" charset="utf-8"></script>
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