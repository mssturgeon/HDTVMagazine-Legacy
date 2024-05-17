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
		AND e.entry_id = 4994";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4994 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4994 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4994";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2013/01/toshiba-unveils-2013-tv-lineup-and-pcs-at-consumer-electronics-show.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4994";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show" />
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
	<title>HDTV Magazine - Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show</title>
	<meta name="keywords" content="screen sizes, available inch, led tvs, toshiba america, inch diagonal, toshiba, series, satellite, tvs, inch, display, new, products, available, systems, may, content, screen, line, sizes, pcs, cloud, led, media, information" />
	<meta name="description" content="CES 2013, LVCC Central Hall Booth #10926 –Toshiba's Digital Products Division (DPD), a division of Toshiba America Information Systems, Inc., today announced its full line-up of 2013 HDTVs as well as new PCs on display at this year's International Consumer Electronics Show. Highlights include an UltraHD TV, new premium LED TVs with Cloud TV functionality, an all-new line of Media Box and Universal Disc Players along with a new Satellite&amp;reg; touchscreen Ultrabook&amp;trade;...." />
	<meta name="title" content="Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2013/01/toshiba-unveils-2013-tv-lineup-and-pcs-at-consumer-electronics-show.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="CES 2013, LVCC Central Hall Booth #10926 –Toshiba's Digital Products Division (DPD), a division of Toshiba America Information Systems, Inc., today announced its full line-up of 2013 HDTVs as well as new PCs on display at this year's International Consumer Electronics Show. Highlights include an UltraHD TV, new premium LED TVs with Cloud TV functionality, an all-new line of Media Box and Universal Disc Players along with a new Satellite&amp;reg; touchscreen Ultrabook&amp;trade;...." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4994', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2013/01/toshiba-unveils-2013-tv-lineup-and-pcs-at-consumer-electronics-show.php">Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 11, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=355&category=4K (Ultra HD)">4K (Ultra HD)</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">Toshiba Unveils 2013 TV Line-Up And PCS At Consumer Electronics Show</p>

<center><i>Products on Display Include 2013 Line of HD LED TVs with Cloud TV, 4K Ultra HD TV, All-New Media Box &amp; Universal Disc Player, Satellite Touchscreen Ultrabook and More</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 8, 2013 /PRNewswire/</strong> -- CES 2013, LVCC Central Hall Booth #10926 –Toshiba's Digital Products Division (DPD), a division of Toshiba America Information Systems, Inc., today announced its full line-up of 2013 HDTVs as well as new PCs on display at this year's International Consumer Electronics Show. Highlights include an UltraHD TV, new premium LED TVs with Cloud TV functionality, an all-new line of Media Box and Universal Disc Players along with a new Satellite&reg; touchscreen Ultrabook&trade;.</p>

<p>"Toshiba's 2013 line-up of premium HD TVs not only feature a stunning, new unified design, but include powerful cloud-based technology that takes the complexity out of finding and watching content no matter where it is, while also offering consumers a smarter connected experience," said Carl Pinto, vice president of marketing, Toshiba America Information Systems, Inc., Digital Products Division. "Paired with our latest tablets, laptops and All-in-Ones, consumers can finally enjoy the benefits of the digital, connected home."</p>

<p><strong>Premium LED TVs with Advanced Cloud TV</strong></p>

<p>Toshiba's 2013 line of premium LED TVs offer powerful, new Cloud TV functionality that provides a better, smarter connected experience. Cloud TV offers easier content discovery and acquisition, including news content; better mobile device interactivity; practical social interaction features, server-based upgradability and more[1]. Some of the key initial services include Family Calendar, Messaging, News, MediaShare and MediaGuide, plus built-in wireless sharing with Intel&reg; Wireless Display (WiDi)[2] and Miracast&trade;[3]. All feature 1080p full HD resolution and include a suite of technologies that enhance picture quality.</p>

<p>The AdvancedCloud TV functionality will be included on the following LED TVs:</p>

<p>L7350 Series 3D TVs will be available in 58- and 65-inch class screen sizes<br />
L7300 Series 2D TVs will be available in 50-, 58- and 65-inch class screen sizes<br />
L4300 Series 2D TVs will be available in 32-, 39- and 50-inch class screen sizes</p>

<p><strong>4K UltraHD TV</strong></p>

<p>Featuring four times the resolution of today's 1080p HDTVs, the all-new Toshiba L9300 Series UltraHD LED TVs deliver the ultimate viewing experience for movies, TV and games. Powered by Toshiba's CEVO 4K Quad+Dual Core Processor, the TVs provide the highest quality resolution restoration, color gamut and surface brilliance enhancements that bring images to life like never before and enable powerful UltraHD upscaling and 2D-to-3D conversion[4]. The L9300 Series will be available in 58-, 65- and 84-inch class screen sizes.[5]</p>

<p><strong>Affordable Yet Stylish LED TVs</strong></p>

<p>Toshiba's 2013 line also includes several affordable LED TVs. The new L2300 Series – available in 23-, 32-, 39- and 50-inch class screen sizes – features a Gun Metallic Satin Deco and base. The new L1350 Series will be available in 23-, 29-, 32-, 39- and 50-inch screen sizes.</p>

<p><strong>Media Box and Universal Disc Player</strong></p>

<p>The all-new Toshiba Media Box and Universal Disc Player line combines the power of a smart Blu-ray Disc&trade; player with the versatility of a media streaming device in a sleek form factor that matches Toshiba's 2013 TVs.</p>

<p><strong>PCs on Display</strong></p>

<p>Toshiba's booth at CES will also showcase its latest laptops, Ultrabooks and All-in-One PCs.<br />
<ul><li>Satellite U925t: The Ultrabook Convertible combines the performance and form factor of an Ultrabook with the ease-of-use of a tablet and is ideal for on-the-go consumers and professionals. The device can be used in two modes: as a tablet or as an Ultrabook made possible by its unique sliding hinge.</li><li>Satellite U 8 45t: An all-new Ultrabook with a 14-inch diagonal display[6] designed with touch for everyday productivity and entertainment.</li><li>Satellite U845W: The first Ultrabook to feature a 14.4-inch diagonal ultrawide-HD display with a cinematic 21:9 aspect ratio. The widescreen display provides an approximately 30 percent greater view of tiles on the Windows 8 Start Screen and is also ideal for snapping Windows 8 apps to the classic desktop interface.</li><li>Qosmio&reg; X875: For gamers and media creators who want an affordable, enthusiast-class laptop, the Qosmio X875 laptop features an optional 1TB hybrid hard drive[7].</li><li>Satellite P-Series: Powerful, portable and equipped to entertain, the Satellite P-Series are the perfect laptops for media enthusiasts and power users. Featuring Harman Kardon&reg; speakers and unique Toshiba conveniences such as USB Sleep &amp; Charge[8] and Sleep &amp; Music[9]. Encased in sleek, stylish aluminum in Prestige Silver, the Satellite P-Series is available with a 15.6- and 17.3-inch diagonal display or a 14-inch touchscreen display.</li><li>Satellite S-Series: The Satellite S-Series offers high performance and design for superior multitasking, HD entertainment and creativity. Encased in brushed aluminum in Ice Blue, the Satellite S-Series is available in 15.6- and 17.3-inch diagonal screen sizes.<br />
Satellite L-Series: For those looking for a versatile PC offering great style and performance, the Satellite L-Series is a great choice. The Satellite L-Series is available in 15.6- and 17.3-inch diagonal screen sizes.</li><li>Satellite C-Series: Ideal for the budget-minded consumer looking for a great value, the Satellite C-Series offers all the essentials to tackle everyday tasks. The Satellite C-Series is available in 15.6- and 17.3-inch diagonal screen sizes.</li><li>All-in-One PCs: Toshiba's 21.5-inch LX815 and 23-inch LX835 All-in-One Desktop PCs deliver high performance for effective multitasking and HD entertainment, style and affordable pricing.</li></ul></p>

<p><strong>Pricing and Availability</strong></p>

<p>Toshiba's Ultra HD TVs, 2013 HDTVs and Media Box &amp; Universal Disc Players are scheduled to begin shipping from March 2013 onward.</p>

<p>The new Satellite and Qosmio laptops as well as the All-in-One PCs are scheduled to begin shipping in February 2013.</p>

<p>Image Gallery: <a target="_blank" href="http://www.toshibapresscenter.com/">www.toshibapresscenter.com</a></p>

<p>Connect with Toshiba on Facebook at <a target="_blank" href="http://www.facebook.com/ToshibaUSA/">www.facebook.com/ToshibaUSA</a>, on Twitter at <a target="_blank" href="http://twitter.com/ToshibaUSA/">twitter.com/ToshibaUSA</a>, and on YouTube at <a target="_blank" href="http://youtube.com/ToshibaUS/">youtube.com/ToshibaUS</a>.</p>

<p><br />
<strong>About Toshiba America Information Systems, Inc. (TAIS)</strong></p>

<p>Headquartered in Irvine, Calif., TAIS is comprised of three business units: Digital Products Division, Imaging Systems Division, and Telecommunication Systems Division. Together,  these divisions provide digital products, services and solutions, including industry-leading portable computers; televisions, TV/DVD Combination products, Blu-ray Disc&trade; and DVD products, and portable devices; imaging products for the security, medical and manufacturing markets; storage products for computers; and IP business telephone systems with unified communications, collaboration and mobility applications. TAIS provides sales, marketing and services for its wide range of products in the United States and Latin America. TAIS is an independent operating company owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation. For more information on TAIS visit us.toshiba.com.</p>

<p><br />
<strong>About Toshiba Corporation</strong></p>

<p>Toshiba is a world-leading diversified manufacturer, solutions provider and marketer of advanced electronic and electrical products and systems. Toshiba Group brings innovation and imagination to a wide range of businesses: digital products, including LCD TVs, notebook PCs, retail solutions and MFPs; electronic devices, including semiconductors, storage products and materials; industrial and social infrastructure systems, including power generation systems, smart community solutions, medical systems and escalators &amp; elevators; and home appliances. Toshiba was founded in 1875, and today operates a global network of more than 550 consolidated companies, with 202,000 employees worldwide and annual sales surpassing 6.1 trillion yen (US$74 billion). Visit Toshiba's web site at www.toshiba.co.jp/index.htm.</p>

<p>&copy; 2013 Toshiba America Information Systems, Inc. All product, service and company names are trademarks, registered trademarks or service marks of their respective owners. Information including without limitation product prices, specifications, availability, content of services, and contact information is subject to change without notice. All rights reserved.</p>

<p>[1] Services available through the Cloud may be changed or removed at any time.  Some services provided through the Cloud may not be available outside of the United States and may not be available to you.</p>

<p>[2] Intel&reg; Wireless Display. Copy protection technology, if any, associated with the content may prevent or limit viewing of content.</p>

<p>[3] To display content from one device to another directly requires that both devices are certified for Miracast.</p>

<p>[4] 2D to 3D Conversion. 3D effect and image quality may vary depending upon content quality and display device capability/functionality/settings. The 3D effect function is activated and controlled by you for your personal enjoyment of 2D home video in 3D according to your personal preference. It is not intended for unauthorized use of copyrighted works. To the extent you need to obtain permission from a right holder to view copyrighted works, it is your responsibility to obtain such permission.</p>

<p>[5] Because "4K" is a new format that makes use of new technologies, certain compatibility and/or performance issues are possible. Currently, no "4K" video content or standards, including, but not limited to, broadcasting or streaming, exists for "4K" televisions in general. This TV may not be compatible with such content and/or standards, if and when developed. Certain HDMI formats or still images exceeding "4K" resolution may not be supported.   This may require purchase of additional external hardware. Toshiba makes no representation or warranties about any future "4K" content, standards or services.</p>

<p>[6] Display. Any small bright dots that may appear on your display are an intrinsic characteristic of the thin film transistors manufacturing technology. See Display Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a></p>

<p>[7] Hard Disk Drive Capacity. Hard drive capacity may vary. 1 Gigabyte (GB) means 109 = 1,000,000,000 bytes using powers of 10. See Hard Disk Drive Capacity Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a></p>

<p>[8] The "USB Sleep &amp; Charge function" may not work with certain external devices even if they are compliant with the USB specification. In those cases, turn the power of the computer ON to charge the device.</p>

<p>[9] Toshiba Sleep &amp; Music. In this mode, the headphone jack and mute functions are disabled. The volume and sound quality will diminish as the speakers not tuned in Windows&reg; mode. Use the volume control on your audio device to adjust sound. Audio line-in cable is not included.  </p>

<p>SOURCE Toshiba America Information Systems, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 11, 2013  6:21 PM</b>
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
			<?=getComments(4994)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4994)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2013/01/toshiba-unveils-2013-tv-lineup-and-pcs-at-consumer-electronics-show.php" type="text/javascript" charset="utf-8"></script>
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