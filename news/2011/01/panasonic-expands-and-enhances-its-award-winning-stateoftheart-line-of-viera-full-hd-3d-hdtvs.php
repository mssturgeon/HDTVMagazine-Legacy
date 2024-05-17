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
		AND e.entry_id = 4140";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4140 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4140 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4140";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/panasonic-expands-and-enhances-its-award-winning-stateoftheart-line-of-viera-full-hd-3d-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4140";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs" />
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
	<title>HDTV Magazine - Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs</title>
	<meta name="keywords" content="measured diagonally, class measured, inch class, digital still, viera connect, viera, panasonic, full, inch, measured, diagonally, class, hdtvs, line, still, new, digital, connect, both, video, series, models, images, led, plasma" />
	<meta name="description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- &lt;/strong&gt;Panasonic, the industry and technology leader in Full HD 3D technology and High Definition televisions, introduced its 2011 line of VIERA Full HD 3D HDTVs at the Consumer Electronics Show. The 2011 Full HD 3D line-up features an expansion of its award wining 2010 model line from eight to 12 Plasma models, including two new screen sizes and the addition of two LCD/LED Full HD 3D models.  At last year's CES, Panasonic's TC-P50VT25 was judged &quot;Best Product of the Show&quot; and the 2011 model line-up builds upon that recognition of high quality with improved picture quality, new crosstalk reduction, faster acting phosphors, the inclusion of DLNA in all models, expanded internet functionality via the proprietary VIERA Connect IPTV platform and Neo Plasma technologies. Adhering to Panasonic's commitment to the environment, all 2011 models meet Energy Star 4.2 requirements. The 2011 VIERA line also includes VIERA Link&amp;trade; for increased networking and 3D VIERA Image Viewer&amp;trade; to allow for watching both 2D and 3D digital still and HD video on a large HDTV.

Panasonic's Plasma Full HD 3D HDTVs are now available..." />
	<meta name="title" content="Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/panasonic-expands-and-enhances-its-award-winning-stateoftheart-line-of-viera-full-hd-3d-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- &lt;/strong&gt;Panasonic, the industry and technology leader in Full HD 3D technology and High Definition televisions, introduced its 2011 line of VIERA Full HD 3D HDTVs at the Consumer Electronics Show. The 2011 Full HD 3D line-up features an expansion of its award wining 2010 model line from eight to 12 Plasma models, including two new screen sizes and the addition of two LCD/LED Full HD 3D models.  At last year's CES, Panasonic's TC-P50VT25 was judged &quot;Best Product of the Show&quot; and the 2011 model line-up builds upon that recognition of high quality with improved picture quality, new crosstalk reduction, faster acting phosphors, the inclusion of DLNA in all models, expanded internet functionality via the proprietary VIERA Connect IPTV platform and Neo Plasma technologies. Adhering to Panasonic's commitment to the environment, all 2011 models meet Energy Star 4.2 requirements. The 2011 VIERA line also includes VIERA Link&amp;trade; for increased networking and 3D VIERA Image Viewer&amp;trade; to allow for watching both 2D and 3D digital still and HD video on a large HDTV.

Panasonic's Plasma Full HD 3D HDTVs are now available..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4140', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/panasonic-expands-and-enhances-its-award-winning-stateoftheart-line-of-viera-full-hd-3d-hdtvs.php">Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">Panasonic Expands and Enhances its Award Winning State-of-the-Art Line of VIERA Full HD 3D HDTVs</p>

<center><i>2011 Line Introduces Two LCD/LED Full HD 3D HDTVs; New Screen Sizes and Focuses on Enhancing the User's TV Experience With Expanded VIERA Connect&trade; Internet Accessibility, Improved Picture Quality, Luminous Efficiency and a Continuing Commitment to the Environment</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- </strong>Panasonic, the industry and technology leader in Full HD 3D technology and High Definition televisions, introduced its 2011 line of VIERA Full HD 3D HDTVs at the Consumer Electronics Show. The 2011 Full HD 3D line-up features an expansion of its award wining 2010 model line from eight to 12 Plasma models, including two new screen sizes and the addition of two LCD/LED Full HD 3D models.  At last year's CES, Panasonic's TC-P50VT25 was judged "Best Product of the Show" and the 2011 model line-up builds upon that recognition of high quality with improved picture quality, new crosstalk reduction, faster acting phosphors, the inclusion of DLNA in all models, expanded internet functionality via the proprietary VIERA Connect IPTV platform and Neo Plasma technologies. Adhering to Panasonic's commitment to the environment, all 2011 models meet Energy Star 4.2 requirements. The 2011 VIERA line also includes VIERA Link&trade; for increased networking and 3D VIERA Image Viewer&trade; to allow for watching both 2D and 3D digital still and HD video on a large HDTV.</p>

<p>Panasonic's Plasma Full HD 3D HDTVs are now available in the following screen sizes- 42-inch class (41.6" measured diagonally); 46-inch class (46" measured diagonally); 50-inch class (49.9" measured diagonally); two new screen sizes-the 55-inch class (55.1" measured diagonally); and the 60-inch class (60.1" measured diagonally)- and 65-inch class (64.7" measured diagonally). The new additions to the Panasonic Full HD 3D family are two LCD/LED HDTVs, a 37-inch class (37" measured diagonally) and a 32-inch (31.5" measured diagonally).</p>

<p>"Everyone at Panasonic is extremely proud of the critical acclaim and acceptance afforded our 2010 Full HD 3D sets," said Henry Hauser, Vice President, Panasonic, Merchandising, Display Group. "We're exceptionally pleased that the VT25 series won a number of best of awards in 2010, not the least of which was the prestigious Best in Show at last years CES. Never one to stand on laurels, the 2011 line builds upon that critical success while increasing the model line-up to include two new Plasma screen sizes, and expanding the 3D experience to select LED models.</p>

<p>"Panasonic is committed to providing the consumer with the highest quality entertainment options and we firmly believe that both Plasma and LCD/LED are terrific technologies. The choice of which technology to purchase depends on a number of factors, including viewing environment and viewing choices. The consumer now has a choice of buying a Full HD 3D TV, whether Plasma or LCD/LED, from the industry leader. Furthermore, as majority of reviewers pointed out, Panasonic's 3D HDTVs also produce superior 2D pictures."</p>

<p>Panasonic's proprietary IPTV functionality is expanded and enhanced for 2011, with a new identifying name, VIERA Connect&trade;(1), reflecting the transition from the walled garden approach of VIERA Cast to the interactive and inter-connected philosophy behind the new Internet enabled platform. VIERA Cast's popular sites, including Netflix&trade;, Amazon VOD&trade;, You Tube&trade;, Pandora, Twitter, Bloomberg News, a weather channel and Skype&trade; continue on VIERA Connect and are joined by a host of exciting features and apps, all optimized for the best possible user experience. Incorporated in VIERA Connect are such apps as CinemaNow, Hulu Plus, Napster, Facebook and popular sports sites including MLB.TV, MLS Matchday Live, NBA Game Time, and NHL Game Center(2).</p>

<p>All Panasonic VIERA Full HD 3D HDTVs include 3D VIERA Image Viewer&trade; a function for easy viewing of digital still photos (both in 2D/3D) and the ability to play back AVCHD 2D video and 3D video recorded on SD card.</p>

<p>"As we enter into the new year, there is little doubt that 3D is here to stay and Panasonic is uniquely positioned to provide the ultimate home 3D experience for the consumer," added Hauser. "Panasonic provides an end-to-end 3D solution, from our highly regarded HDTVs to the introduction of the first consumer 3D camcorder, 3D capable digital still cameras, 3D Blu-ray Disc players, professional Full HD 3D displays, including the 103-inch Plasma, and Panasonic Hollywood Laboratory, a Hollywood based facility where many of the current 3D Blu-ray discs are authored."</p>

<p>.</p>

<p>PANASONIC 2011 FULL HD 3D HDTVs</p>

<p>Full HD 3D Plasma</p>

<p>For 2011 Panasonic introduced three VIERA Full HD 3D Plasma model lines, the VT30 series,GT30 and ST30 series. Panasonic's Full HD 3D televisions provide full 1080p resolution to both the right and left eye, thereby giving the viewer the definitive 3D entertainment experience.</p>

<p>The top-of-the-line VIERA VT30 series consists of two models- the 65-inch class (64.7" measured diagonally) TC-P65VT30 and the 55-inch class ( 55.1" measured diagonally) TC-P55VT30. The VT30 models are distinguished by a one sheet glass design, giving the TVs a beautiful sleek new design element. In addition to providing 3D viewing, the VT30 series of VIERA HDTVs features the VIERA Connect service; Skype video calling; Wi-Fi ready including LAN (through USB port); 3D VIERA Image Viewer&trade; to view JPEG 2D digital still images, 3D digital still images and both 2D &amp;  3D HD video recorded on an SD Memory Card; VIERA Link; a PC input; three USB Ports; THX certification(3); 24p playback; 600Hz subfield (produces 1080 lines of moving picture resolution); four HDMI connections; DLNA connector; fast switching phosphors; a RS232C/ISF connection and an Infinite Black Pro 2 panel. The new and improved Infinite Black Pro 2 Panel raises the luminous efficiency while minimizing pre-discharge, resulting in even more subtle, delicate blacks, in both dark and bright environments than last year's Infinite Black Pro Panel provided. One pair of Full HD 3D eyewear is included with the VT30 series.</p>

<p>The VIERA GT30 series features four models, the TC-P65GT30, a 65-inch class (64.7" measured diagonally); the TC-P60GT30, a 60-inch class (60.1" measured diagonally); the TC-P55GT30, a 55-inch class ( 55.1" measured diagonally ) and the TC-P50GT30, a 50-inch class (49.9" measured diagonally). The GT30 series features an Infinite Black 2 Panel; VIERA Connect, Wi-Fi ready with included LAN (through USB port); Skype Video calling; 3D VIERA Image Viewer&trade; to view JPEG 2D digital still images, 3D digital still images and both 2D &amp;  3D HD video recorded on an SD Memory card; DLNA; VIERA link; PC input; 4 HDMI connections; 3 USB ports; THX certification(3); fast switching phosphors and a 600Hz Sub-field Drive.</p>

<p>The VIERA ST30 series introduces six screen sizes, from 42-inches to 65-inches, to the Panasonic family of Full HD 3D HDTVs. Featured in this series are the TC-P65ST30, a 65-inch class (64.7" measured diagonally); TC-P60ST30, a 60-inch class ( 60.1" measured diagonally); TC-P55ST30, a 55-inch class (55.1" measured diagonally); TC-P50ST30, a 50-inch class (49.9" measured diagonally); TC-P46ST30, a 46-inch class (46" measured diagonally) and the TC-P42ST30, a 42" class (41.6" measured diagonally). The ST30 Full HD 3D HDTVs present a feature package that includes the Infinite Black 2 Panel; VIERA Connect, Wi-Fi Ready with LAN (through USB port); Skype Video calling; 3D VIERA Image Viewer&trade; to view JPEG 2D digital still images, 3D digital still images and both 2D &amp;  3D HD video recorded on an SD Memory card; DLNA; VIERA link; 3 HDMI connections; 2 USB ports; fast Switching Phosphor; 600Hz Sub-field drive.</p>

<p>Full HD 3D LCD-LED</p>

<p>For the 2011 Full HD 3D line-up, Panasonic has extended the immersive 3D technology to its LED line, with two models, the TC-L37DT30, a 37-inch class (37" measured diagonally) and the TC-L32DT30, a 32-inch class (31.5" measured diagonally). Both models employ an IPS Alpha LED panel – assuring a wide viewing angle with almost no picture degradation,  and improved motion response; VIERA Connect, Wi-Fi ready (includes LAN Adaptor); 3D VIERA Image Viewer&trade; to view JPEG 2D digital still images, 3D digital still images and both 2D &amp;  3D HD video recorded on an SD Memory card; DLNA; VIERA link; 4 HDMI connections; 3 USB ports; a PC input; ISF Pro Setting Menu; 240Hz with Motion Picture Pro 5, providing fast motion picture response necessary to produce crisp, cross-talk free 3D images and a lower power consumption.</p>

<p>About Panasonic Consumer Electronics Company</p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), a market and technology leader in High Definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Panasonic Corporation (NYSE: PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations.  Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs.  Information about Panasonic products is available at <a target="_blank" href="http://www.panasonic.com/">www.panasonic.com</a>. Additional company information for journalists is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>(1) Some 2011 VIERA Connect applications will only be compatible with 2011 VIERA Connect-enabled Panasonic VIERA HDTVs due to specific technology requirements required to activate certain applications.</p>

<p>(2) Available to subscribers of the individual sites</p>

<p>(3) Tentative</p>

<p>SOURCE Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2011  9:59 PM</b>
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
			<?=getComments(4140)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4140)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/panasonic-expands-and-enhances-its-award-winning-stateoftheart-line-of-viera-full-hd-3d-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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