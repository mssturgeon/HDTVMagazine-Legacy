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
		AND e.entry_id = 3841";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3841 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3841 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3841";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/07/lg-electronics-brings-content-to-life-with-suite-of-3d-technology.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3841";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics Brings Content to Life With Suite of 3D Technology" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics Brings Content to Life With Suite of 3D Technology" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics Brings Content to Life With Suite of 3D Technology" />
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
	<title>HDTV Magazine - LG Electronics Brings Content to Life With Suite of 3D Technology</title>
	<meta name="keywords" content="blu ray, inch class, disc player, ray disc, class inch, consumers, content, entertainment, led, technology, inch, home, electronics, blu, ray, access, netcast, energy, provides, online, full, experience, network, series, options" />
	<meta name="description" content="LG Electronics takes entertainment to the next dimension by introducing a full suite of 3D technology products to help bring content to life for consumers. By pairing LG's new top-of-the-line &quot;Infinia&quot; LED HDTVs with its first-ever 3D-capable Network Blu-ray Disc player, consumers can now enjoy superior picture quality and the immersive 3D experience of their favorite sporting events and Hollywood mega-hits in the comfort of their home.

LG Infinia LX9500 and LX6500 series HDTVs, paired with the company's BX580 Network Blu-ray Disc player and custom 3D eyewear, now enable consumers to experience..." />
	<meta name="title" content="LG Electronics Brings Content to Life With Suite of 3D Technology" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics Brings Content to Life With Suite of 3D Technology" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/07/lg-electronics-brings-content-to-life-with-suite-of-3d-technology.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LG Electronics takes entertainment to the next dimension by introducing a full suite of 3D technology products to help bring content to life for consumers. By pairing LG's new top-of-the-line &quot;Infinia&quot; LED HDTVs with its first-ever 3D-capable Network Blu-ray Disc player, consumers can now enjoy superior picture quality and the immersive 3D experience of their favorite sporting events and Hollywood mega-hits in the comfort of their home.

LG Infinia LX9500 and LX6500 series HDTVs, paired with the company's BX580 Network Blu-ray Disc player and custom 3D eyewear, now enable consumers to experience..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3841', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/07/lg-electronics-brings-content-to-life-with-suite-of-3d-technology.php">LG Electronics Brings Content to Life With Suite of 3D Technology</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 13, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">LG Electronics Brings Content to Life With Suite of 3D Technology</p>

<center><i>LG's New LED HDTV and Blu-ray Disc Player Technologies Bring 3D Content Home*, Taking Entertainment to the Next Dimension</center></i><br />
<br />

<p><strong>ENGLEWOOD CLIFFS, N.J., July 12 /PRNewswire/ -- </strong>LG Electronics takes entertainment to the next dimension by introducing a full suite of 3D technology products to help bring content to life for consumers. By pairing LG's new top-of-the-line "Infinia" LED HDTVs with its first-ever 3D-capable Network Blu-ray Disc player, consumers can now enjoy superior picture quality and the immersive 3D experience of their favorite sporting events and Hollywood mega-hits in the comfort of their home.</p>

<p>LG Infinia LX9500 and LX6500 series HDTVs, paired with the company's BX580 Network Blu-ray Disc player and custom 3D eyewear, now enable consumers to experience 3D TV, colors and contrast like never before. The LX9500 provides the ultimate home entertainment experience with THX certified picture quality, Full LED Slim technology, virtually unlimited content possibilities from NetCast(TM) Entertainment Access, an ultra-high refresh rate and all of LG's latest innovations.</p>

<p>"While consumers are still seeking the ultimate 2D HDTV home entertainment experience, demand for 3D technology is growing by the day, and as more content becomes available in the 3D format, this demand will continue to increase," said Peter Reiner, senior vice president, marketing, LG Electronics USA. "LG is now providing consumers with 'something better' in 3D - sleek design, entertainment and online content options, and the extraordinary picture and sound quality that people have come to expect from LG."</p>

<p>LG will be offering an exclusive bundle at retail for the launch of its 3D technology suite, making 3D more accessible to consumers. With the 1-2-3D bundle, consumers who purchase any LG LX9500 or LX6500 LED HDTV paired with the LG 3D Blu-ray Disc Player from participating retailers, will receive two free pairs of the 3D active shutter glasses, a $100 instant rebate and a bonus redemption certificate to receive Warner Home Video's Blu-ray 3D(TM) title IMAX&reg; Under the Sea 3D by mail while supplies last. Under the Sea transports viewers to some of the most exotic undersea locations on Earth, including Southern Australia, New Guinea and others in the Indo-Pacific region, allowing them to experience face-to-face encounters with some of the most mysterious and stunning creatures of the sea. Visit LGusa.com/3Dpromo or visit participating retailers for full details.</p>

<p><br />
<strong>3D Experience Hits the Small Screen</strong></p>

<p>LG's 55- and 47-inch class** LX9500, the world's first Full LED 3D HDTV, provides consumers with the latest 3D-capable LED display technology, works with the use of active shutter glasses and an emitter built directly into the television to provide 3D content in Full HD 1080p resolution to each eye.</p>

<p>LG's proprietary Full LED Slim technology elevates picture quality with LED backlighting that supports detailed local dimming for improved contrast and detail, while also allowing for a sleek frame with an ultra-slim .92-inch wide bezel for a virtually borderless look. The set itself measures less than an inch thick at its thinnest point. With up to 240 addressable LED segments (on the 55LX9500), this state-of-the-art HDTV provides deeper black levels, sharper colors and an overall uniform picture quality that typically could not be achieved on an ultra-thin set. With an astonishing 480Hz refresh rate, LED local dimming, 2D THX Certification and its eye-catching super-slim frame, the LX9500 makes for a stunning display whether the power is on or off.</p>

<p>The LX9500 also offers consumers virtually limitless entertainment options through NetCast Entertainment Access. This model is Wi-Fi enabled (adaptor required, sold separately), so online content can be accessed through a wireless connection to a home network. LG's LX9500 series also incorporates LG's unique "Magic Wand" remote system that provides an intuitive interaction with the set.</p>

<p>LG's LX9500, launching in July, is supported by an integrated, multifaceted marketing campaign with broadcast and online advertising tied to LG's corporate partnership with the NCAA, which began airing during the NCAA "March Madness" men's basketball tournament.</p>

<p><br />
<strong>LX6500 Series</strong></p>

<p>LG's stylish LX6500 series, available in 55- and 47-inch class screen sizes** through U.S. retailers, delivers 3D technology, contains energy-saving features, and delivers superior display performance with multiple entertainment options. It allows consumers to tap into the future of entertainment with broadband TV, and offers a host of other features that will bring the viewing experience to the next level.</p>

<p>Featuring LED Plus technology, the LX6500 combines edge lighting and local dimming for greater control of individual sections of the picture, based on the actual on-screen content which will deliver better contrast, amazing clarity and color detail, as well as greater energy efficiency compared to conventional LCD TVs. With the dynamic mega contrast ratio of 8,000,000:1, the LX6500 delivers stunning colors and deeper blacks, so concerns about seeing details in dark scenes are a thing of the past. View sports, video games and high-speed action with virtually no motion blur and in crystal clarity with LG's TruMotion(TM) 240Hz technology, which enables your TV to keep up with the fastest moving scenes. The LX6500 series also features NetCast and is WiFi capable (adaptor required, sold separately).</p>

<p><br />
<strong>Endless Entertainment</strong></p>

<p>Both the LX9500 and LX6500 keep consumers entertained at home, boasting seamless connectivity and a virtually limitless content package with a variety of entertainment options, including NetCast Entertainment Access. With NetCast, consumers can access the following content sites for an almost endless array of online entertainment options without the need for a personal computer:</p>

<ul><li>Netflix(TM): Consumers can stream thousands of movies and TV shows, including a growing number of HD titles.</li><li>VUDU(TM): Allows consumers to instantly buy or rent from the largest library of movies in Full HD 1080p resolution as well as TV titles - with no monthly fees or additional hardware.</li><li>YouTube(TM): Offers the ability to instantly stream millions of Web videos directly from the Internet (without a personal computer).</li><li>Yahoo! Widgets(TM): Enables access to TV Widgets that allow viewers to interact with popular Internet services and online media through applications specifically tailored to the needs of the watcher, such as up-to-the minute Yahoo! News, Weather and Finance, and new widgets, including CBS, Showtime and CNBC.</li><li>Picasa: Provides access to Google's photo software so consumers can view photo albums at the touch of a button.</li></ul>

<p><br />
<strong>Energy Savings</strong></p>

<p>Understanding consumers' desire for products that reduce their household energy costs, both of LG's 3D LED HDTVs have a variety of energy-saving features, such as Intelligent Sensor to automatically calibrate and optimize brightness, contrast, white balance and color, based on the ambient light in the room, thereby saving on energy output under most circumstances. Additionally, ISFccc calibration options allows for professional calibration to set "day" and "night" levels for optimal viewing and brightness levels customized to the consumers own viewing environment. All of LG's 2010 LED LCD series are ENERGY STAR&reg; rated.</p>

<p><br />
<strong>3D Hollywood Blockbusters Come to Blu-ray</strong></p>

<p>LG's BX580 Network Blu-ray Disc Player adds 3D playback capabilities in addition to 2D Blu-ray discs and DVD playback so consumers can use one machine to watch their favorite 3D and 2D content. The BX580 provides consumers with the ability to enjoy the new 3D content format, and when combined with the online content capabilities from NetCast, including Netflix(TM), CinemaNow, VUDU(TM), YouTube(TM), MLB.TV, Pandora, Picasa and AccuWeather, the BX580 provides an unrivalled entertainment package.</p>

<p>LG's BX580 3D-capable Network Blu-ray Disc player has built-in Wi-Fi capability allowing consumers to access online content through a connection to their home network, without the need for a direct Ethernet connection, offering greater flexibility during setup by eliminating wires. DLNA certification gives users access to content, like family videos, from other DLNA certified devices such as a computer.</p>

<p>For more information about LG products, please visit <a target="_blank" href="http://www.LG.com/">www.LG.com</a>.</p>

<p><br />
<strong>ABOUT LG ELECTRONICS USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances and business solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.LG.com/">www.LG.com</a>.</p>

<p>**55LX9500 55-inch class/54.6-inch diagonal<br />
**47LX9500 47-inch class/47.0-inch diagonal<br />
**55LX6500 55-inch class/54.6-inch diagonal<br />
**47LX6500 47-inch class/47.0-inch diagonal</p>

<p>*LG LED HDTVs are LCD TVs with LED Backlighting. LG TVs are THX certified in 2D only. Internet connection required to access NetCast and is sold separately. Some NetCast services require separate paid subscriptions. Viewing 3D video may cause discomfort. Visit <a target="_blank" href="http://www.lge.com/">www.lge.com</a> for details. 3D glasses required and sold separately.</p>

<p>Source: LG Electronics</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 13, 2010  8:27 PM</b>
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
			<?=getComments(3841)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3841)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/07/lg-electronics-brings-content-to-life-with-suite-of-3d-technology.php" type="text/javascript" charset="utf-8"></script>
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