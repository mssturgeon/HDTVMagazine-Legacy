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
		AND e.entry_id = 3963";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3963 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3963 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3963";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/09/toshiba-drives-the-next-generation-of-home-entertainment-releasing-its-first-ever-3d-hdtv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3963";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV" />
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
	<title>HDTV Magazine - Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV</title>
	<meta name="keywords" content="cinema series, registered trademark, blu ray, toshiba america, hdmi cec, toshiba, series, cinema, registered, inc, trademark, hdmi, led, products, home, systems, may, new, hdtv, high, blu, ray, content, america, available" />
	<meta name="description" content="Toshiba America Information Systems, Inc., an innovator in consumer electronics and digital home entertainment, today announced the availability of its premier Cinema Series&amp;reg; line for 2010, featuring the VX700 Series, and the WX800 3D Series, LED HDTVs. Leading Toshiba's full line of LED and LCD HDTVs, Cinema Series presents the ultimate in picture quality and multimedia connectivity with luxuriously sleek, premium designs that can elevate any room's look and feel.

As the pinnacle of Toshiba's 2010 HDTV offering, the Cinema Series line boasts..." />
	<meta name="title" content="Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/09/toshiba-drives-the-next-generation-of-home-entertainment-releasing-its-first-ever-3d-hdtv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Toshiba America Information Systems, Inc., an innovator in consumer electronics and digital home entertainment, today announced the availability of its premier Cinema Series&amp;reg; line for 2010, featuring the VX700 Series, and the WX800 3D Series, LED HDTVs. Leading Toshiba's full line of LED and LCD HDTVs, Cinema Series presents the ultimate in picture quality and multimedia connectivity with luxuriously sleek, premium designs that can elevate any room's look and feel.

As the pinnacle of Toshiba's 2010 HDTV offering, the Cinema Series line boasts..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3963', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/09/toshiba-drives-the-next-generation-of-home-entertainment-releasing-its-first-ever-3d-hdtv.php">Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 16, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>
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
				<p class="prtitle">Toshiba Drives the Next Generation of Home Entertainment Releasing Its First Ever 3D HDTV</p>

<center><i>New Cinema Series&reg; LED HDTVs combine immersive picture quality, fun connectivity, and new high-end, ultra-thin cosmetic designs</center></i><br />
<br />

<p><strong>IRVINE, Calif., Sept. 15 /PRNewswire/ -- </strong>Toshiba America Information Systems, Inc., an innovator in consumer electronics and digital home entertainment, today announced the availability of its premier Cinema Series&reg; line for 2010, featuring the VX700 Series, and the WX800 3D Series, LED HDTVs. Leading Toshiba's full line of LED and LCD HDTVs, Cinema Series presents the ultimate in picture quality and multimedia connectivity with luxuriously sleek, premium designs that can elevate any room's look and feel.</p>

<p>As the pinnacle of Toshiba's 2010 HDTV offering, the Cinema Series line boasts ultimate picture, beautiful cosmetics, and state-of-the-art features. The WX800 is an exceptional choice for today's most demanding home theater connoisseurs, as well as consumers seeking a stylish entertainment powerhouse for their home. With true 3D capability(1), the WX800 presents a three-dimensional experience from 3D movies, TV programs, video games and more when using a pair of optional Toshiba 3D glasses, Toshiba's BDX3000 3D Blu-ray Disc(TM) player and a 3D capable HDMI&reg; cable.(2)</p>

<p>The BDX3000 is an integral part of Toshiba's 3D product family, with the ability to play the latest 3D Blu-ray titles(3), standard Blu-ray Discs and DVDs at the highest quality possible. It also features HD audio and a variety of streaming content(4), for a complete, cutting-edge home entertainment system.</p>

<p>The VX700 is formidable in its own right employing many of the same feature benefits of the Cinema Series brand, but targeting consumers not looking for 3D. This set also boasts advanced LED backlighting driven by Toshiba's latest PixelPure&reg; 5G video engine for exceptional colors, contrast, and clarity with both HD and SD content, and ClearFrame(TM) 120Hz processing that reduces blurring from swift action.</p>

<p>"With the new Toshiba Cinema Series models, we have created a brand of televisions that provides consumers with luxurious design, exciting features, high quality, and pride of ownership, without sacrificing true value and ease of use," said Scott Ramirez, Vice President of Product Marketing and Development, Television and Digital A/V, Toshiba America Information Systems. "Toshiba's new Cinema Series models are clearly positioned to exceed consumers' performance and design expectations. By adding our new enhanced 3D experience, the WX800 provides a home theater experience which truly distinguishes itself from all others."</p>

<p><br />
<strong>WX800 Cinema Series</strong></p>

<p>Boasting an ultra-slim depth of less than 1.2 inches, and Toshiba's high-style flush front design with sleek chrome trim and eye-catching illusion stand, the WX800 Cinema Series is not only Toshiba's first-ever 3D HDTV, but also one of the company's most meticulously designed and engineered to date. The WX800 delivers an unparalleled viewing experience with technologies such as 3D Resolution+&reg; which delivers crisp, clear 3D content that can transform a room into an incredible 3D theater.(2) Versatile as it is beautiful, the WX800 processes multiple 3D input formats, including MPEG4-MVC, RealD, and select others for hassle-free enjoyment.</p>

<p>The WX800's CineSpeed&reg; LED panel includes an advanced edge lighting system and DynaLight(TM) control for continuously optimized contrast resulting in an amazing 7M:1 contrast ratio for deep black levels, bright whites, and vibrant colors. The WX800 also features Toshiba's ClearFrame(TM) 240Hz technology for clear fast motion movies and sports. Whether displaying the latest high action flick or the energy of a live football game, the WX800's picture is clear and crisp.</p>

<p>The WX800 is a diversified centerpiece for any home theater, especially with its ability to access Toshiba's Enhanced NET TV(TM) service with Yahoo! Connected TV; a vast collection of favorite and brand new streaming content options await via a simple connection to the Internet, and a touch of the remote. The WX800's built-in 802.11b/g/n Wi-Fi&reg; capability allows for a simple, clutter-free method to instantly access tens of thousands of TV episodes and movies from Netflix&reg;, enjoy over 3,000 HD movies, video podcasts, and more from VUDU(TM), catch up with friends and family on Facebook&reg; and Twitter&reg;, and explore new videos and photos on YouTube&reg;, Flickr&reg;, and more.(4)</p>

<p>Wired connectivity is also key with the WX800, which has four HDMI&reg; connections with InstaPort(TM) fast input switching, and the convenience of HDMI-CEC to control other CEC enabled devices using a single remote.(5) A ColorStream&reg; component input and high resolution PC input make it simple to connect a wide variety of video sources to the WX800.</p>

<p>The WX800 Cinema Series is available in 55-inch and 46-inch screen sizes (measured diagonally at 54.6 inches and 46.0 inches respectively).</p>

<p><br />
<strong>VX700 Cinema Series</strong></p>

<p>The Toshiba VX700 Cinema Series model includes many of the same innovations as the flagship WX800, making it an exceptional choice for home theater enthusiasts and style aficionados not looking for 3D. The VX700's LED edge lighting system achieves a high 5M:1 dynamic contrast ratio, and enables the set to show content with rich, lifelike detail, especially with the support of Toshiba's ClearFrame(TM) 120Hz technology. Videophiles will appreciate the wide range of adjustments and controls found on the VX700 (and the WX800). Expert Model and ColorMaster(TM) allow easy access to advanced color and gamma settings, for fine tuning the picture performance to individual preference and perfection. Though the VX700's speakers are "invisible" when looking at the outside of the set, the distinguished sound they can produce is clearly noticeable, with Dolby&reg; Volume for a consistent volume level when changing channels or sources, Audyssey EQ&reg; processing, dynamic bass boost, and voice enhancement.</p>

<p>Built-in Wi-Fi&reg; connectivity is also included on the VX700 for bringing the world of Enhanced NET TV(TM) with Yahoo! Connected TV into the living room. Like the WX800, other available content and entertainment includes Netflix&reg;, VUDU(TM), Pandora&reg; Internet Radio that gives people personalized radio stations full of music they love , and even up-to-the-minute news and updates.(4) The VX700 also includes two USB ports, an SD/SDHC card slot, and support for DLNA&reg; file sharing, making it easy to watch videos, view photos, and listen to music saved on cameras, portable drives, a networked PC and elsewhere, right on the big screen.</p>

<p>When it comes to appearances, whether turned on or off, the VX700 is a style-focused addition to any room. At just over an inch thick, the VX700 and its functional design looks ideal either mounted on a wall, or connected to an "Illusion(TM)" swivel stand. And with a new flush front design, the VX700 carries a truly seamless look between the screen and the outside edges of the bezel for an added touch of sophistication.</p>

<p>The VX700 Cinema Series is available in 55-inch and 46-inch screen sizes (measured diagonally at 54.6 inches and 46.0 inches respectively).</p>

<p>Pricing and Availability:<br />
  55WX800 Cinema Series 3D LED HDTV (available now, SRP $3,299.99)<br />
  46WX800 Cinema Series 3D LED HDTV (available now, SRP $2,599.99)<br />
  55VX700 Cinema Series LED HDTV (available now, SRP $2,799.99)<br />
  46VX700 Cinema Series LED HDTV (available now, SRP $2,299.99)</p>

<p><br />
<strong>About Toshiba America Information Systems, Inc. (TAIS)</strong></p>

<p>Headquartered in Irvine, Calif., TAIS is comprised of four business units: Digital Products Division, Imaging Systems Division, Storage Device Division, and Telecommunication Systems Division. Together, these divisions provide mobile products and solutions, including industry-leading portable computers; televisions, TV/DVD Combination products, Blu-ray Disc and DVD products, and portable devices; imaging products for the security, medical and manufacturing markets; storage products for automotive, computer and consumer electronics applications; and IP business telephone systems with unified communications, collaboration and mobility applications. TAIS provides sales, marketing and services for its wide range of products in the United States and Latin America.</p>

<p>TAIS is an independent operating company owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation. Toshiba is a world leader and innovator in pioneering high technology, a diversified manufacturer and marketer of advanced electronic and electrical products spanning information &amp; communications systems; digital consumer products; electronic devices and components; power systems, including nuclear energy; industrial and social infrastructure systems; and home appliances. Toshiba was founded in 1875, and today operates a global network of more than 740 companies, with 204,000 employees worldwide and annual sales surpassing 6.3 trillion yen (US$68 billion). For more information on Toshiba's leading innovations, visit the company's Web site at <a target="_blank" href="http://www.toshiba.com/">www.toshiba.com</a>.</p>

<p>  Important Notes:<br />
  1. 3D Feature Health Precaution:  Due to the possible impact on vision<br />
     development, viewers of 3D programming should be age 6 or above.<br />
     Children may be more susceptible to health effects from viewing 3D<br />
     images and should be closely supervised.  Some viewers may experience a<br />
     seizure or blackout when exposed to certain flashing images or lights<br />
     contained in certain 3D television pictures or video games.  Anyone who<br />
     has had a seizure, loss of awareness, or other symptom linked to an<br />
     epileptic condition, or has a family history of epilepsy, should<br />
     contact a health care provider before using the 3D function of this<br />
     product.<br />
  2. Viewing 3D content with the WX800 requires Toshiba 3D glasses, BDX3000<br />
     3D Blu-ray Disc(TM) player and 3D capable HDMI cable all sold<br />
     separately.<br />
  3. BDX3000 is designed to play back Blu-ray discs that comply with the 3D<br />
     specifications of Blu-ray Disc Association.  It is not compatible with<br />
     other 3D specifications.<br />
  4. Third party internet services are not provided by Toshiba, may change<br />
     or be discontinued at any time and may be subject to third party<br />
     restrictions.  Toshiba makes no representations or warranties about<br />
     these services, which may require the creation of a user account<br />
     through a computer with internet access and one-time and/or recurring<br />
     charges.  Some features may require an always-on broadband internet<br />
     connection, firmware update and/or additional bandwidth.  Some<br />
     recordable media, cards or files may not be supported.<br />
  5. Use of HDMI&reg;-CEC feature requires an HDMI&reg;-CEC compatible peripheral<br />
     device. Depending on the specifications of your device, some or all<br />
     HDMI&reg;-CEC functions may not work even if your device is HDMI&reg;-CEC<br />
     compatible.</p>

<p><br />
  LED TV is part of the Toshiba LCD TV line.</p>

<p><br />
CinemaSeries, CineSpeed, ColorStream and PixelPure are registered trademarks and ClearFrame, ColorMaster, DynaLight, Illusion and Net TV are trademarks of Toshiba America Consumer Products, L.L.C. Audyssey EQ is a registered trademark of Audyssey Laboratories, Inc. Dolby is a registered trademark of Dolby Laboratories. DLNA is a registered trademark of the Digital Living Network Alliance. Facebook is a registered trademark of Facebook Inc. Flickr and Yahoo! are registered trademarks of Yahoo! Inc. HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing, LLC in the United States and other countries. InstaPort is a trademark or registered trademark of Silicon Image, Inc. in the United States and other countries. Netflix is a registered trademark of Netflix, Inc. Pandora is a registered trademark of Pandora Media, Inc. Picasa is a trademark of Google, Inc. Resolution+ is a registered trademark of Toshiba Corporation. Twitter is a registered trademark of Twitter, Inc. VUDU is a registered trademark of VUDU, Inc. Wi-Fi is a registered mark of the Wi-Fi Alliance. YouTube is a registered trademark of Google Inc. All others are trademarks or registered trademarks of their respective owners.</p>

<p>Source: Toshiba America Information Systems, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 16, 2010  7:48 PM</b>
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
			<?=getComments(3963)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3963)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/09/toshiba-drives-the-next-generation-of-home-entertainment-releasing-its-first-ever-3d-hdtv.php" type="text/javascript" charset="utf-8"></script>
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