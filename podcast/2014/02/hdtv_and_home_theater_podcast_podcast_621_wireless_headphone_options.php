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
		AND e.entry_id = 5203";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5203 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5203 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5203";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2014/02/hdtv-and-home-theater-podcast-podcast-621-wireless-headphone-options.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5203";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options</title>
	<meta name="keywords" content="surround sound, home theater, wireless headphone, wireless headphones, pair headphones, headphones, sound, wireless, sony, ’re, theater, surround, buy, home, dolby, audio, headphone, may, sennheiser, pair, feature, mhz, batteries, dts, bass" />
	<meta name="description" content="You&amp;acirc;��ve probably heard us stress over and over again how important surround sound is to your HDTV and home theater experience. Without surround sound, it&amp;acirc;��s really just surveillance, not home theater. But we are regular guys too, and we leave in the real world. We know that sometimes you have to make sacrifices and compromises. You aren&amp;acirc;��t the only one in the house, or the building or the neighborhood." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2014/02/hdtv-and-home-theater-podcast-podcast-621-wireless-headphone-options.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="You&amp;acirc;��ve probably heard us stress over and over again how important surround sound is to your HDTV and home theater experience. Without surround sound, it&amp;acirc;��s really just surveillance, not home theater. But we are regular guys too, and we leave in the real world. We know that sometimes you have to make sacrifices and compromises. You aren&amp;acirc;��t the only one in the house, or the building or the neighborhood." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5203', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2014/02/hdtv-and-home-theater-podcast-podcast-621-wireless-headphone-options.php">HDTV and Home Theater Podcast - Podcast #621: Wireless Headphone Options</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>February  6, 2014</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=441&category=Wireless HDMI/HDTV">Wireless HDMI/HDTV</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>Wireless Headphones</h3>
<p dir="ltr">You’ve probably heard us stress over and over again how important surround sound is to your HDTV and home theater experience. Without surround sound, it’s really just surveillance, not home theater. But we are regular guys too, and we leave in the real world. We know that sometimes you have to make sacrifices and compromises. You aren’t the only one in the house, or the building or the neighborhood.</p>
<p dir="ltr">Sometimes that compromise means using a sound bar. Other times it may mean throwing on some headphones so you can still enjoy HDTV when the volume may otherwise disrupt the delicate balance of your ecosystem. Like many of you, Braden has young children. He has many of them, in fact. Sometimes it’s nice to let them sleep a little at night and headphones can come in quite handy for that.</p>
<p dir="ltr">You have a couple major decisions to make when buying headphones for your home theater, the most important is how well they sound. But beyond that, there are some logistics questions, the main one being: wired or wireless? Wired have reliable quality, and no need to recharge batteries. But they have long cables that need to be stored, if the cable isn’t long enough, they can be uncomfortable, and so on. Wireless headphones are much more convenient and, provided you buy the right ones, can sound just as good.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B009A6CZYO/?tag=hdtvandhometh-20"><strong>Sony MDRRF985RK Wireless RF Headphone</strong></a><br />
Buy now: $86</p>
<p dir="ltr">Sony makes some of our all time favorite studio monitor headphones, the <a href="http://www.amazon.com/dp/B000AJIF4E/?tag=hdtvandhometh-20">MDR7506 Professional Large Diaphragm Headphones</a> that go for $102. They may not be the best headphones around anymore, but they are a tried and true classic, and still quite good. If you’re in the market for wired headphones, they’re worth checking out. But, for this round-up, we aren’t interested in wired, we’re going wireless. It may have been our sentimentality that pushed us to them, but the Sony MDRRF985RK are the first pair of headphones on the list.</p>
<p dir="ltr">These phones from Sony give you the freedom to travel up to 150 feet from your sound source while delivering stereo quality sound. Hopefully your couch isn’t 150 feet away from your TV, but just in case it is, you’re all set. They run on the 900MHz RF wireless band, which is fine, but not the best choice. They have a 40mm driver for decent bass performance, and claim frequency response from 20 Hz to 20 kHz. They have an auto tuning feature that conveniently scans up and down the band to automatically tune in channels.</p>
<p dir="ltr">And like most units on our list, they are rechargeable. They include Ni-MH rechargeable batteries with a max run time of 25 hours. You’ll probably pass out before they do. All-in-all a solid performer at a decent price. They won’t blow you away with their quality, but they will be reliable and that also won’t blow away your whole checking account.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B000H12VI6/?tag=hdtvandhometh-20"><strong>JVC HAW600RF 900MHZ Wireless Headphones</strong></a><br />
Buy now: $54</p>
<p dir="ltr">These headphones from JVC are feature for feature nearly identical the the prior model from Sony. They run on the same 900 MHz RF frequency, but boast a slightly larger effective distance of 164 feet. They too have the auto tuning feature to hopefully provide the best quality sound at all times. Couple that with a 40mm driver for full-bodied sound and you’ve got a pair of headphones that are tough to distinguish from the Sony pair.</p>
<p dir="ltr">The JVC HAW600RF does offer a convenient paging or voice call function that allows for voice communication from the base station to the headset user. So if someone wanders off wearing the headphones, or you simply want to play practical jokes on someone to annoy them, you’ve got that going for you. But what really got us to put these above the Sony is the price. They’re close to half the cost of the Sony model.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B0001FTVEK/?tag=hdtvandhometh-20"><strong>Sennheiser RS120 On-Ear 926MHz Wireless RF Headphones with Charging Cradle</strong></a><br />
Buy now: $80</p>
<p dir="ltr">In the bang-for-the-buck category, the RS120 may take the cake. It is a lightweight RF wireless headphone system with Open-Aire Supra-Aural design for hi-fidelity audio reproduction. They feature a transparent, well-balanced sound with solid bass reproduction, and are a great choice for both hi-fi and TV use. The transmitter has an “easy recharge” function for conveniently recharging the included headphone batteries.</p>
<p dir="ltr">The RS120 headphones run on the 926 MHz frequency, which is still in the 900 MHz band, but for some reason they don’t seem to suffer the same interference issues that others on the 900 MHz band struggle with. And somehow this allows them to claim a 328 foot reception distance, even though walls and ceilings. Unlike other units on the list, however, these Sennheisers don’t have the auto-tuning functionality, but instead provide three user-selectable channels.</p>
<p dir="ltr">If you want a pair of really good headphones without spending too much money, the Sennheiser RS120 would be our first pick in the under $100 category.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B002TLT10I/?tag=hdtvandhometh-20"><strong>Sennheiser RS 170 Digital Wireless Headphone with Dynamic Bass and Surround Sound</strong></a><br />
Buy now: $198</p>
<p dir="ltr">If you’d like to step up from a solid performer to a stand out, you’ll want to look at the Sennheiser RS170 headphones. They aren’t the top of the line Sennheiser model, but they’re close enough that you still get superior sound quality without over-paying for it. The <a href="http://www.amazon.com/dp/B002TLT10S/?tag=hdtvandhometh-20">RS180</a> for $240 or the even more expensive <a href="http://www.amazon.com/dp/B006ZNX81E/?tag=hdtvandhometh-20">RS220</a> for $495 are fighting for supremacy in the Sennheiser wireless headphone showdown. But in the real world, cost is a factor in deciding what to buy, and at under $200, the RS170 headphones are ideal.</p>
<p dir="ltr">The RS170 utilizes KLEER&#8217;s lossless digital wireless audio transmission for audiophile-grade sound and reception. They claim KLEER technology will not interfere with wireless networks or other 2.4GHz devices. Up to 4 compatible Sennheiser KLEER headphones can be paired with the same transmitter for private listening for multiple individuals. Which, at that point, begs the question &#8211; can’t all four of you just take off the headphones and use the speakers instead?</p>
<p dir="ltr">The RS170 wasn’t built for the average listener, they were built with the audiophile in mind, the transducer systems’ neodymium magnets deliver clear and detailed audio reproduction. The sealed, private earcups prevent sound leakage and make sure you can hear everything you’re supposed to. They also feature selectable Dynamic Bass Boost for deeper bass and selectable Surround Sound simulation for a more immersive pseudo-home theater experience.</p>
<p dir="ltr">This pair of headphones run on a different frequency that the others, using 2.4 &#8211; 2.8 GHz, with built-in auto selecting technology. They have a range of up to 260 feet. The wireless headphones operate on 2 AAA NiMH rechargeable batteries, which are included and should get you around 24 hours of listening time. The transmitter also acts as a charging stand for the headphones when not in use, and can fully recharge depleted headphone batteries in about 16 hours.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B005LA53D8/?tag=hdtvandhometh-20"><strong>Sony MDR-DS7500 Wireless Digital Surround Headphones System</strong></a><br />
Buy now: $429</p>
<p dir="ltr">Speaking of over-paying, the Sony MDR-DS7500 clock in at an impressive $429. But before you freak out about how you can buy an entire surround sound receiver for that, consider what you’re getting. First, and perhaps most importantly, these headphones were developed in cooperation with Sony Pictures Entertainment, a place near and dear to the HT Guys’ hearts. But beyond that, if you want to build something for the home theater, it makes sense to work with the experts in cinema sound.</p>
<p dir="ltr">What did that joint development produce? The outcome was Sony&#8217;s new &#8220;New Cinema mode&#8221; which was designed around analyzing the measured data for movie production in real movie theaters and sound stages. By combining Sony&#8217;s VPT (Virtualphones Technology) this &#8220;New Cinema mode&#8221;  is said to reproduce the sound of an ideal movie theater.</p>
<p dir="ltr">The MDR-DS7500 comes with a newly developed chipset for 3D audio as well as Dolby NR professional Logic II z decoder supporting audio signals up to 7.1Ch. DMI inputs on the processor enables the new DS7500 to decode HD audio such as Dolby TrueHD. Also supports multi-channel linear PCM as well as Bravia HDMI CEC. Three HDMI inputs with 1 line HDMI output allows you to connect to your devices freely.</p>
<p dir="ltr">The MDR-DS7500 has a dynamic reproduction range of 5 Hz to 25 kHz and the full list of codecs it supports is: Dolby NR TrueHD, Dolby Digital plus, Dolby Digital, Dolby NR professional logic II z, Dolby NR professional logic. x, DTS-HD mastering audio, DTS-HD high-resolution audio, DTS 96/24, DTS-ES, DTS, Neo:6, MPEG-2 AAC, and linear PCM 7.1ch/5.1ch. The headphones operate on the 2Ghz spectrum and the built-in rechargeable batteries provide up to 18 hours of continuous playback.</p>
<p dir="ltr">Some Amazon reviews mentioned getting a Japanese instruction manual; obviously not helpful for most of us. But luckily they reported that some strategic googling led them to an English version that got the job done. Who reads the instructions anyways <img src="http://s1.wp.com/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley" /> </p>
<p>&nbsp;</p>
<h4>Other</h4>
<p dir="ltr">There are also a handful of surround sound gaming headsets out there like the <a href="http://www.amazon.com/dp/B00C2B2Y9A/?tag=hdtvandhometh-20">Skullcandy PLYR1 7.1 Surround Sound Wireless Gaming Headset</a> for $130 or the <a href="http://www.amazon.com/dp/B006W41W3M/?tag=hdtvandhometh-20">Turtle Beach Ear Force X42 Wireless Dolby Surround Sound Gaming Headset</a> for $123. If you’re into gaming and can get a pair like this, they may be able to double for you as a solid home theater solution as well. There isn’t a huge advantage in price, and connectivity may be more challenging since they’re intended to be connected to a gaming console. But buying one pair of headphones that can serve dual purpose might make sense for some.</p>
<p>&nbsp;</p>
<h4>Conclusion</h4>
<p dir="ltr">There are plenty of approaches available today to listen to all the booming explosions and dynamic action you want from your home theater without disturbing anyone else. There are also cases where people with hearing impairments or who have experienced a loss of hearing could benefit from their own headphones, even if they’re listening to the same thing as everyone else. Whatever your requirements, there’s sure to be a set of headphones for you that will meet your needs and keep the finance committee happy.</p>
<p dir="ltr">
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2014-02-07.mp3">Download Episode #621</a></p><br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>February  6, 2014 11:37 PM</b>
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
			<?=getComments(5203)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5203)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2014/02/hdtv-and-home-theater-podcast-podcast-621-wireless-headphone-options.php" type="text/javascript" charset="utf-8"></script>
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