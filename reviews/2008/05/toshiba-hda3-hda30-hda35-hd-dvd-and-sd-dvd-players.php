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
		AND e.entry_id = 836";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 836 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 836 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 836";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2008/05/toshiba-hda3-hda30-hda35-hd-dvd-and-sd-dvd-players.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 836";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players" />
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
	<title>HDTV Magazine - Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</title>
	<meta name="keywords" content="analog video, via hdmi, scan rates, multi channel, analog component, dvd, player, hdmi, toshiba, video, audio, output, disc, performance, analog, players, response, content, those, pcm, bitstream, native, digital, scaling, back" />
	<meta name="description" content="In case you hadn't heard, the HD format war is over. The Blu-ray camp struck a huge win just before CES when Warner Brothers announced they would be moving exclusively to Blu-ray by mid year, tipping the scales majorly in Blu-ray's favor. Then, in February 2008, Toshiba announced it would discontinue the development, manufacturing and marketing of HD DVD, officially ending the format war. They did, however, confirm that they would honor the warranty of all HD DVD players.

The Toshiba HD DVD players featured in this review were the latest (and as it turns out, last) generation to be released. While it may seem an empty effort to review them now since HD DVD has removed itself from the race, these third generation players from Toshiba have a performance response at crazy low prices to be reckoned with and deserve the full story." />
	<meta name="title" content="Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2008/05/toshiba-hda3-hda30-hda35-hd-dvd-and-sd-dvd-players.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In case you hadn't heard, the HD format war is over. The Blu-ray camp struck a huge win just before CES when Warner Brothers announced they would be moving exclusively to Blu-ray by mid year, tipping the scales majorly in Blu-ray's favor. Then, in February 2008, Toshiba announced it would discontinue the development, manufacturing and marketing of HD DVD, officially ending the format war. They did, however, confirm that they would honor the warranty of all HD DVD players.

The Toshiba HD DVD players featured in this review were the latest (and as it turns out, last) generation to be released. While it may seem an empty effort to review them now since HD DVD has removed itself from the race, these third generation players from Toshiba have a performance response at crazy low prices to be reckoned with and deserve the full story." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=836', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2008/05/toshiba-hda3-hda30-hda35-hd-dvd-and-sd-dvd-players.php">Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>May  1, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=360&category=HD DVD Players">HD DVD Players</a></b>
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
				<p><img height="86" alt="HD-A35" src="http://www.hdtvmagazine.com/images/mt/ToshibaHDA3HDA30HDA35HDDVDandSDDVDplayer_8C8A/image.png" width="355" border="0">  <table class="greygrid"> <tbody> <tr> <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td></tr> <tr> <td class="greygrid"><b>&nbsp;</b></td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Street</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>HD-A3</b></td> <td class="greygrid">$299.00 (2007)</td> <td class="greygrid"><a href="/equipment/model.php?man=Toshiba&amp;model=HDA3" target="_blank">$89.99</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000U62N1S?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000U62N1S" target="_blank">$132.50</a></td></tr> <tr> <td class="greygrid"><b>HD-A30</b></td> <td class="greygrid">$399.00 (2007)</td> <td class="greygrid"><a href="/equipment/model.php?man=Toshiba&amp;model=HDA30" target="_blank">$129.99</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000U6AHYS?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000U6AHYS" target="_blank">$354.99</a></td></tr> <tr> <td class="greygrid"><b>HD-A35</b></td> <td class="greygrid">$499.00 (2007)</td> <td class="greygrid"><a href="/equipment/model.php?man=Toshiba&amp;model=HDA35" target="_blank">$499.00</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000U6AHZW?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000U6AHZW" target="_blank">$699.99</a></td></tr></tbody></table><br>HD-A3 Serial #: N/A<br>HD-A30 Serial #: N/A<br>HD-A35 Serial #: PL27Z00402<br>Warranty for all models: 1 year parts and labor<br><br><b>Summary: Videophile performance at mass market prices with one 1080p24 bug</b><br><br> <p>In case you hadn't heard, the HD format war is over. The Blu-ray camp struck a huge win just before CES when Warner Brothers announced they would be moving exclusively to Blu-ray by mid year, tipping the scales majorly in Blu-ray's favor. Then, in February 2008, Toshiba announced it would discontinue the development, manufacturing and marketing of HD DVD, officially ending the format war. They did, however, confirm that they would honor the warranty of all HD DVD players.  <p>The Toshiba HD DVD players featured in this review were the latest (and as it turns out, last) generation to be released. While it may seem an empty effort to review them now since HD DVD has removed itself from the race, these third generation players from Toshiba have a performance response at crazy low prices to be reckoned with and deserve the full story. For the movie buff that wants it all, HD DVD is still necessary since it could be some time before the movies that were released exclusively in that format are released again on Blu-ray. And for some titles, it may not be worth the effort re-releasing them on Blu-ray, leaving the HD DVD release as the only HD version available.  <p>The third generation players clearly show a cookie cutter style of manufacturing and marketing, offering three general retail models with differing features (HD-A3, HD-A30 and HD-A35) along with a warehouse retailer version (HD-D3). Looking at these players you would be hard pressed to see any difference from the front or back.  <p>The HD-A3 was purchased by a friend and tested at his house. The HD-A30 was purchased through a Best Buy outlet and then exchanged at a later date for the HD-A35 because, to the surprise of Best Buy staff and yours truly, only that model supports native bit stream for HD audio codecs. Those were tested here at the lab. In the end, I was unable to find any performance difference between these players when comparing similar capabilities. Indeed, the only difference appears to be the feature set.  <h2>Common Features for All Models </h2> <ul> <li>Component analog video up to 1080i for HD DVD and 480p for SD DVD  <li>HDMI digital video output up to 1080i  <li>HDMI supports Deep Color  <li>Composite analog video output  <li>Digital audio HDMI 1.3 PCM output supporting all sound track codecs  <li>SD Optical digital audio output  <li>Ethernet port for web enabled special features along with firmware upgrades  <li>HDMI-CEC CE-Link allows the player to interact with other CE-Link devices in your system for automated commands and functions </li></ul> <p>HD-A30 Adds  <ul> <li>HDMI digital video output up to 1080p, 60 and 24 frames  <li>HDMI 1080p 24 frame output for SD DVD; performance bug? (more on this later) </li></ul> <p>HD-A35 Adds  <ul> <li>Digital audio HDMI 1.3 bit stream output supporting all HD audio codecs </li></ul> <h2>Missing features you might have expected, all models </h2> <ul> <li>There is no multi-channel analog output for any model, only stereo. You can use the SD optical digital audio output but all HD audio codecs will be down converted to that standard. </li></ul> <h2>Opening the Boxes </h2> <p>The HD-A30 was well packed, yet cheap by comparison to previous models; not a big deal as much as an observation. I actually wondered if the product was a repack but looking at the factory seal and other things it didn't appear to be. The HD-A35 seemed packed better than the HD-A30, making me wonder about it being a repack. Those concerned about rack space and the larger cabinet styles of 1st generation product from either camp will rejoice over Toshiba's 2nd and 3rd generation players, which come in a smaller cabinet, cutting previous cabinet height by about half, to 2.25 inches. For the 3rd generation products the drawer is all the way to the right and everything else to the left and the same goes for the back panel of connections that are now on the right rather than the left. I did find it odd to have the front panel reversed from most player designs where the drawer is either on the left side or middle. It was a curiosity each time I had to access the drawer! In terms of fit and finish it does not have the high end impressionable look of the 1st generation players. Nope, this is a humble product at a humble price. If there was any sense of cheapness it was clearly found with the black remote included with all models up to the HD-A30 which had the look and feel of having been paired with a sub $100 cheap and inexpensive product for the masses. You won't feel like you are controlling your product in style. That said, it has one thing that fancy 1st generation remote didn't: functionality and ease of use. The cursor buttons work and you can read the labels, which trumps style any day! The remote for the HD-A35 is identical to what came with most of the 2nd generation players, being longer and skinnier and adding direct access simplistic TV controls, but overall the same fit and finish of the HD-A3 and HD-A30 remotes.  <h2>Out of Box Performance </h2> <p>Hooking up either player to a <a href="http://www.hdtvmagazine.com/reviews/2007/07/benq_w10000_1080p_dlp_front_projector.php">BenQ W10000 DLP Front projector</a>, I found it preset for 16:9 1080p. Going into the setup menu I switched the player to 1080p24 and ran the DVE HD DVD test material. Looking over at the receiver it showed a PCM multi-channel input with the HD-A30 and bit stream labels with the HD-A35 after changing some audio settings in the player menu. Everything seemed to look and sound great. On to objective testing...  <h2>On the Test Bench </h2> <p>This will be the first HD disc player review where all aspects of video performance can be bench tested using the Digital Video Essentials HD DVD/DVD combo test and calibration disc. What follows is objective testing for both SD DVD and HD DVD content via HDMI at 720p, 1080i and 1080p along with component analog video at 480p for SD DVD and component analog at 720p and 1080i for HD DVD. All video testing was performed on the HD-A30 in the lab with a follow up confirmation of the HD-A35. The HD-A3 was tested in the owner's home.  <p>Our current SD DVD reference player and benchmark is the fully reviewed <a href="http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php">OPPO DV-981HD</a>.  <p>The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection and copy protection since the means to see it would infer a means to steal it. At this time the <a href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000U</a> has been kept in the stable just for this purpose using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, making me unable to verify the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal, as noted. All tests were performed using Digital Video Essentials test patterns as the source material.  <h2>Video Levels </h2> <p>With waveform monitoring, the Toshiba players output 0IRE and 100IRE at the correct 16/235 levels via HDMI for both disc formats at all HD scan rates.  <p>Via analog component, the Toshiba had a peak white output about 2 IRE above 100 IRE for SD DVD at 480p as well as HD DVD at 720p and 1080i. Considering the nature of video content, this is a marginal error that will only show up on occasion if perceived at all. For a digital display this may cause crushing of peak white especially if the display was calibrated without any headroom for exactly this type of condition. For analog CRT displays this will likely be negligible. Nonetheless, it is an error.  <h2>Color Decoding </h2> <p>With waveform monitoring the Toshiba players output correct color decoding at all HD scan rates with all formats and connections.  <p>Via HDMI or analog video, the Toshiba players had the usual amount of scaling artifacts observed for color bar patterns where two colors meet. Having artifacts in this area of response is unfortunately common. All the Toshiba players and the OPPO DV981HD generated about the same level of error over the same number pixels.  <h2>Horizontal Frequency Response Luminance </h2> <p>With HD DVD via HDMI, all scan rates reproduced a flawless response, as expected. Note that for 720p and 1080p testing the source content used was native to that scan rate as well as pixel mapped, a great feature of the DVE HD DVD disc providing a fair comparison for both HD scan rates.  <p>SD DVD is not pixel mapped. As noted, Waveform monitoring response was useless for this test. Visually the Toshiba passed the continuous frequency burst test at 1080p HDMI quite well for luminance, bettering the reference OPPO. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly ... and the Toshiba was no exception. This pattern always has banding as well. The best I can state on this is a high, medium or a low contrast response with high being the best and low being the worst. The Toshiba provided a high contrast response bettering the OPPO. 720p HDMI was similar in response but with a bit more banding which is to be expected when having less pixels to scale with.  <p>Analog component video had a similar response at 480p.  <h2>Vertical Frequency Response Luminance </h2> <p>With HD DVD via HDMI, all scan rates reproduced a flawless response, as expected. Note that for 720p and 1080p testing the source content used was native to that scan rate as well as pixel mapped.  <p>Via HDMI all scan rates reproduced a great response. Vertical frequency response was excellent in 1080p. Typically 720p scaling of SD DVD cannot figure out which dark and white stripes it should favor with white being predominant in the top or bottom burst and black predominant in the other. The HD-A30 sets a benchmark by getting this right for both areas at 720p.  <p>Analog component video had a great response at 480p.  <h2>Frequency Response Color </h2> <p>The Toshiba provided the best response so far providing a smooth even response across the screen for both red and blue channels for all formats at all scan rates and connections bettering the OPPO for SD DVD.  <p>Analog component video responded quite well at 480p.  <h2>CUE, Chroma Upsampling Error - SD DVD only </h2> <p>This causes a vertical breakup of color detail in the vertical plane, typically expressed in reds but can show up for other colors as well. It is related to the player using only one MPEG decoding method rather than both interlace and progressive and applying the correct version to the native source on the disc. The Toshiba failed this test but normal failure of this test is clear vertical tearing/combing of red. These artifacts appeared in a very subtle manner and videophiles are likely to pick up on it. CUE errors are much rarer these days, and with the error being subtle it is difficult to make a huge issue out of this.  <h2>Aspect Ratio Control </h2> <p>The Toshiba provides an auto 16:9/4:3 switching mode allowing the player to maintain correct aspect if the content is properly flagged with special features or 4:3 movies adding black side bars. The Toshiba provides another first for the 4:3 mode using no overscan at all; a welcomed surprise and yet again, better than the OPPO.  <p>For the DVD collector looking for great performance with all DVD mastering from 4:3 letter boxed sources to special features, the Toshiba has nothing to offer. This capability and level of performance still resides in the external scaling market.  <h2>SD DVD Scaling Analog Component Video </h2> <p>The Toshiba was tested at 480p HDMI feeding a 1080p DLP front projector with pixel mapped centered output, along with an adjusted viewing distance to compensate, using the DVE chapter 17 A/V Demonstration material.  <p>The Toshiba passed with flying colors providing the same common level of performance I would expect from most any 480p analog component video output.  <h2>SD DVD Scaling HDMI </h2> <p>The Toshiba was tested at 1080p60, 1080p24 and 720p HDMI feeding a 1080p DLP front projector with pixel mapped centered output using the DVE A/V Demonstration material.  <p>Wow! 1080p24 scaling with SD DVD? Don't get excited because the player does not use intelligent scaling and the raw source to actually do that. Instead this is an operational error that could be corrected via software. But until that happens, this is a problem area for both formats if using the 1080p24 output setting! SD DVD is encoded from the original 24 (film) or 30 (video) frame interlaced source for the format along with progressive flags that allows inexpensive dumb scaling within the player. The player then uses those flags to reconstruct the interlaced source as a progressive image as it is told. If the source is properly mastered with these progressive flags, you get excellent 480p 30 frame scaled material designed for the standard 60 hertz vertical refresh of all displays. So far, all players reviewed have used this dumb scaling system for SD DVD to generate quality 480p content and then scale it to 720p60, 1080i60 or 1080p60. The Toshiba works the same way except that it does not automatically switch back and forth between 1080p24 and 1080p60 to reflect the disc format you are watching unlike other players that have been reviewed; it stays in 1080p24 mode with SD DVD. This naturally generates intermittent artifacts with SD DVD directly related to image content, causing aliasing of edges and a vertical combing or tearing of color resolution. The only solution is to manually switch the output for the disc type you are viewing, which is inconvenient and bound to trip up the viewer at some point when they forget to check/switch. Adding insult to injury, if you happen to check after you booted an HD DVD disc and need to change scan rates, you get to wait and then find your place on the disc since this forces a reboot of the disc; SD DVD allows you to return to where you were. The lack of auto switching is a sad over sight for performance enthusiasts with 1080p24 capable displays trying to reduce the amount of boxes in our systems for convenience sake.  <p>Back to the correct 1080p60 setting for SD DVD...  <p>During testing, the Toshiba excelled, with the DVE test patterns outperforming our reference OPPO DV-981HD for the SD DVD format. With that kind of response, it was no surprise that the demonstration material was rendered just as well. A few more great discs were tested making it clear that with properly mastered and flagged content, the Toshiba is one fine SD DVD player, period! There is a catch though. If the material is not properly mastered and flagged, it has no intelligent scaling to correct source errors as was the case with the CUE test which it did technically fail. I also have a very nasty improperly mastered DVD for testing such things and the OPPO smoked the Toshiba on such content; thank goodness that is rare. With typical mastering errors, the OPPO faired a little better than the Toshiba and in all fairness all mass market manufacturers have depended on the flagged dumb scaling system for years because it works as well, if not arguably better than external scaling, provided the rest of the player processing was as accurate. To be clear this is a case of absolutes and picking nits. The difference will vary with your display, viewing environment and viewing distance. My setup requires the best or artifacts will be plain. With that said I would live with the Toshiba and the occasional poor DVD on this system rather than having to deal with yet another box and connection for those rare occurrences. For me, those rare titles can also be viewed on the far more forgiving casual system upstairs!  <h2>Scaling HD DVD via HDMI and Analog Video </h2> <p>For 1080i analog video testing I used the Panasonic PTAE1000 instead due to excellent deinterlacing of 1080i content. Switching back to the BenQ via HDMI and 1080p60 it still looked great although in some of the DVE material such as the CG graphics of plant life, there were visible aliasing problems. This was intermittent and directly related to the detail content of the material. No real surprise here since the content is 1080p24 and 1080p60. 1080i60 and 720p60 requires 2/3 pull-down processing of that content.  <p>Via HDMI and 1080p24 or 720p24 content the Toshiba delivered video perfection within the capability of the format with all test materials.  <h2>Audio Performance </h2> <p>The application of HD audio to either of the HD disc formats is quite complex, with potential land mines along the way. This is not an article on that topic, and for complete info I refer you to <a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php" target="_blank">Multi-channel Audio for HD</a>. <p>With the HD-A35 providing full bitstream support of all HD audio codecs, all you need is a compatible A/V receiver of your preference. For those with transitional PCM multi-channel HDMI supported A/V receivers, you are set to go with HD audio converted to multi-channel PCM and SD DVD outputting the native bitstream. Both methods require you use a digital video connection such as HDMI or DVI with HDCP. To be clear, the HD-A3 and HD-A30 DO NOT support bit stream from HD DVD, only PCM, still a huge step forward from using analog multi channel output and input connections.  <p>The HD-A3 and HD-A30 convert the native codec of either format to PCM multichannel when using the HDMI output for both audio and video. You will need to enter the setup menu and change the settings for SD DVD to PCM or it will be only 2-channel. If you want the native codec from SD DVD you will have to connect the SD digital output to your receiver and switch to that input for the audio along with the settings for SD DVD to bitstream.  <h2>Bitstream versus PCM</h2> <p>The HD-A35 provided my first opportunity to directly compare the native bitstream to converted PCM. While PCM decoding should be identical in theory, the reality is upon entering your A/V receiver it will be sliced and diced yet again so it can be applied to your room correction and speaker setup which is part and parcel of any HT sound system. One of the annoying attributes of analog multi-channel inputs was the fact that you could not typically perform those functions at the receiver and the player lacked the in depth adjustments as a substitute. Those receivers that could perform those functions had to convert the analog back into digital for the slicing and dicing adding another process to degrade the sound. By being able to pass on the native bitstream from the disc to the receiver, some of these processing steps get bypassed providing the potential for superior performance. Another possibility is that the chip sets and codecs used by the receiver are superior to those used in the player. Based on testing, one of the two (or a combination of both) is what my ears experienced. The difference was not dramatic but it did exist. The best description is slightly more of everything as if a slight veil had been removed between my ears and the speakers. Like SD DVD, if you are looking for the best in sound, native bitstream decoding by your A/V receiver is the future!  <p>Special features on the disc may add another layer of complexity for bitstream users depending on their application. Features such as directors comments or the new streaming of special features as a PIP function while watching the movie requires one or more additional tracks be added to the original soundtrack of the movie or allow switching back and forth, better known as advanced audio mix. Bitstream applications do not support that capability. To avoid end user confusion and possible complaints when the special features of a movie are missing their audio tracks, some discs may be flagged preventing your ability to ever process the original bitstream. Disc authors themselves may desire this to insure that you will fully benefit from the experience they have created (none of the HD DVDs used in this review had that flag). Under these circumstances, the player will only support such features as PCM or SD bit stream. When the disc is flagged this is automated and your HDMI bitstream will be switched to PCM so there is nothing for you to do. When optional, you will have to switch the player output to PCM for HDMI or use the SD digital connection for these special features. This application was not tested. <h2>Mixing Analog Component Video with HDMI Audio </h2> <p>Only those with a display that does not support HDCP HDMI or DVI need to take note of this. For those upgrading a legacy home theater system using analog video connections and starting on the audio side with HDMI audio you are going to have some problems just like other HD disc players. If you intend to use the HDMI connection for audio and HD DVD, you will be pleased to know that you will get full HD audio support via bitstream or multi-channel. Unfortunately with SD DVD you will get sound but will be left out in the cold with no image at all. The fix for that is to use the optical SD digital audio output and setup another input on your receiver for that digital input. That should release the HDMI handshake from turning on the analog video again. As I was checking out how this would work for you I also found another operational bug; to get the bitstream from SD DVD I had to go into the setup menu and change the SPDIF output to bitstream, yet when I switched back to HD DVD the receiver would only indicate PCM rather than PCM 96khz requiring me to go back into the setup menu and change the SPDIF back to PCM to get PCM 96khz on the receiver. While SD DVD allows you to stop, make changes and return back to where you were HD DVD always forces a reboot if you press the stop key. The following is a frustrated general statement based on this and other reviews. This is yet another firmware or design flaw that could be overcome by allowing the product to automatically switch to the proper settings depending on the end users application. There is no reason these players cannot output HD audio via HDMI using bitstream or PCM with an HD disc at 1080i or 720p analog video and switch to SD bitstream or PCM at 480p analog video with SD DVD allowing those in transition to get the maximum benefits from both formats on the same player without additional setup effort, A/V switching or another player.  <p>Putting this in perspective, those performance enthusiasts with legacy multiscan displays supporting native 480p probably have a good legacy 480p DVD player to go along with it. You could just keep using that for DVD if an additional box in the rack is of no concern. I get hit with folks wanting to upgrade to an upconverting player often and if you have a native 480p scan rate there is little to gain by upconverting to your HD scan rate and it could be argued you are creating more artifacts rather than less.  <h2>Ethernet Port </h2> <p>For most users you need to enter the setup menu and turn DHCP on and within that menu set DNS for auto. There are other settings to insure network compatibility. This port provides support for extra features using web based content along with firmware upgrades. Checking firmware, the player reported I had 1.0 and that 1.1 was available. The player failed on the first attempt to upgrade showing an error code on screen which locked up the player requiring an AC reboot, unplugging the AC cord. This qualifies as yet another performance bug because for most installations the product will likely end up buried with other equipment making access to the AC cord very inconvenient. The second attempt ended in success along with the fact that it was updated to 1.3, the current version as posted by Toshiba on their website and <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=8722">here at HD Library</a>.  <h2>Problems </h2> <p>During testing one frustrating operational element reared its head over and over; While SD DVD allows you to stop, make changes and return back to where you were HD DVD always forces a reboot if you press the stop key. While mentioned already, it bears mentioning again.  <p>Like the HD-A1, I continued to have an intermittent HDMI problem where the picture turned into a pixilated all red version of what it should be, related to the continual HDMI handshaking that occurs with either HD format as it goes from one bit of disc content to another on it's way to the main feature. The good news is this player finally supports hot plug and play of HDMI, unlike the HD-A1, and the fix was as simple as changing to another input and back to HDMI. For the HD-A1 I had to stop the disc and as noted that forces a reboot of the disc along with a test of my patience for that player. I don't know if this is the player or my system and cables. This problem has not occurred with any other HDMI source so naturally I am inclined to blame it on the player/Toshiba. That said, a subsequent change in the lengthy HDMI cable to the projector appears to have put a stop to this.  <p>The HD-A30 skipped a beat in one of those crucial climax scenes during the end of Blood Diamond, a Netflix rental. Fortunately it did not destroy the entertainment but we were all cringing for a moment in anticipation of a complete lock up that never happened.  <p>The HD-A30 and HD-A35 had problems with a Netflix rental of The Shooter during the last five minutes of the disc, which also happened to be one of the climaxes. To add insult to injury it locked up the player requiring removal of AC power to make it operational and for those with the product buried in a rack, a painful proposition! After numerous lengthy attempts I gave up and put the disc in an Xbox 360 setup with HD DVD drive and it played flawlessly. Physically the disc had scratches but in my experience I have seen discs in far worse condition.  <p>During Serenity, a virgin HD DVD disc purchase, the HD-A35 direct bitstream HD audio simply stopped from one scene to the next and if it weren't for the fact that I had seen the movie before we might have watched for minutes as the lack of sound appeared to flow with the scene. Rewinding back to the error brought the sound back to life. Towards the end of the movie the picture started breaking up into blocks, the sound went haywire for a moment and when everything returned to normal there was a huge lip sync error. Simply pressing pause and then play cured the problem.  <p>I can't tell you why I seem to be plagued with these kinds of HD DVD problems, but integrity requires I report this. That same integrity requires I remind you that there are plenty of others who have not had a problem but there has been no research to clear the air on whether this is a format problem or a hardware problem. Consider the Xbox 360 HD DVD drive experience as one hardware example that saved the day for The Shooter.  <h2>Service </h2> <p>Like nearly all DVD players these days, there is no field support for service and repair. Contact Toshiba for the nearest repair depot for repair or exchange.  <h2>Conclusion </h2> <p>For 1.5 years the performance community has been waiting on a statement product for the HD DVD format and the Toshiba HD-A35 delivers that reference point for the best HD DVD can be providing native 1080p24 for the video and native HD audio codec bitstreams for your preferred compatible A/V receiver!  <p>SD DVD scaling was exemplary and amongst the finest with good material. While it may not be the best with all content on DVD for the most part the main feature, the movie, is covered. If you want the best scaling for all content then an external scaler with an SDI equipped DVD player remains your only choice although an expensive one.  <p>Unfortunately, there is a bug in the system. If you are running a 1080p24 display then the best response will only come from manually changing the output to the appropriate scan rate for your content. Let your ability to always remember to check be your guide.  <p>It was evident from testing that as you go down from the HD-A35 to the other models there is no penalty in delivered performance, only in features. For analog component video applications the player receives high marks in scaling for both HD DVD at 1080i and SD DVD at 480p! Remember the video level error though for peak white, especially if you have a digital display.  <h2>Putting It in Perspective </h2> <p>The Toshiba provides reference quality for both SD and HD DVD formats. In that regard all models were a winner! Unfortunately for the technology challenged performance mass market and performance HT installers, the 1080p24 bug of the HD-A35 and HD-A30 with SD DVD is frustrating to no end. These clients are looking for auto pilot; not performance baby sitting nor multiple keystrokes on a remote in a menu they likely care less to understand. For most of these installations I can't see anything else to do but take a hit with one of the formats; artifacts with HD DVD at 1080p60 or artifacts with SD DVD at 1080p24. For the hands on user it is bound to become a nagging frustration to remember to switch scan rates all the time. Upgrading the firmware to 1.3 changed nothing on this front. The only good news is this has to be a simple firmware fix. The bad news is based on past history, if it even happens; it will likely happen in the long term rather than short. When the 2nd generation players were released the performance community wailed over the <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=6873">lack of native 1080p24 support</a> for the HD DVD format and some folks at Toshiba said they would deliver while others refused to answer. Many retailers made promises to their customers! The fact is it took Toshiba about a year to provide that. With the demise of the format it is questionable that any further effort would be expended over this. Toshiba, please get this fixed!  <p>Compared to other players I have reviewed, it is on top purely for delivering reference performance with both formats. I don't really need an external scaler or separate DVD player for top notch performance and that is a first so far.  <p>If you are looking for an HD DVD player, any of the models provide a performance to cost ratio that currently is simply out of this world; choose the feature set you want and enjoy! On top of that, HD DVD discs are going to become very cheap in the coming months and some current owners see nothing but future opportunity.  <p>If you are looking for an upconverting player for SD DVD that does 720p, 1080i or 1080p the HD-A30 at $199 MSRP is 100% competitive as well as a huge videophile bargain considering the reference response! If you don't need or want 1080p support then the HD-A3 is $149 MSRP and would do quite well with a native 720p display. For an SD DVD upconverting performance comparison our reference OPPO DV-981HD is $249!  <p>If you were looking for a long term HD disc player and couldn't decide which one that contest is over. Skip HD DVD and buy a Blu-ray player.  <p>Four recent articles and feedback on the format war:  <ul> <li><a href="http://www.hdtvmagazine.com/articles/2007/11/is_it_my_choice.php">Is It My Choice, or Is It Yours?</a>  <li><a href="http://www.hdtvmagazine.com/articles/2007/11/hd_dvd_or_blu-r.php">HD DVD or Blu-ray: My Choice is...?</a>  <li><a href="http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php">Which is More Consumer Friendly: HD DVD or Blu-ray?</a>  <li><a href="http://www.hdtvmagazine.com/news/2008/01/hd_dvd_rallies_consumer_audience_in_2007_driving_nearly_one_million_dedicated_player_sales_in_north_america.php">HD DVD Rallies Consumer Audience in 2007 Driving Nearly One Million Dedicated Player Sales in North America</a> </li></ul> <h2>Final Conclusion </h2> <p>The Toshiba HD DVD players are a great spring board into HD disc for multiscan 480p/1080i CRT legacy displays. While there are other displays with more resolving power, those old CRT products beat them hands down in other areas and Toshiba has a great player to add new HD life to your experience.  <p>For those with new displays supporting HDMI, any of the players provide quality audio and video performance and for those with 1080p24 capable displays the HD-A30 or HD-A35 provide a reference video performance envelope. The HD-A35 adds the HD audio native bit stream finale for the best HD DVD can be putting you directly in touch with the studio!</p></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>May  1, 2008  9:55 AM</b>
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
			<?=getComments(836)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 836)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/05/toshiba-hda3-hda30-hda35-hd-dvd-and-sd-dvd-players.php" type="text/javascript" charset="utf-8"></script>
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