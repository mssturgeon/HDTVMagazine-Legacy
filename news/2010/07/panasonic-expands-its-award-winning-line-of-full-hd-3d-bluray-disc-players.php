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
		AND e.entry_id = 3866";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3866 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3866 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3866";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/07/panasonic-expands-its-award-winning-line-of-full-hd-3d-bluray-disc-players.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3866";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players" />
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
	<title>HDTV Magazine - Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players</title>
	<meta name="keywords" content="dmp bdt, blu ray, viera cast, ray disc, wireless lan, panasonic, bdt, viera, full, blu, ray, dmp, quality, disc, video, lan, cast, technology, wireless, power, content, high, picture, players, image" />
	<meta name="description" content="Panasonic, an industry leader in Full HD 3D technology, announced today the expansion of its line of Full HD 3D Blu-ray(TM) Disc players to include the DMP-BDT100. The DMP-BDT100, available in August, joins the DMP-BDT300 and DMP-BDT350 in providing superior 2D and 3D picture quality, as well as such innovative features as VIERA CAST(TM) internet functionality. Pricing will be announced at a later date.

The BDT100 continues the commitment to excellence established by..." />
	<meta name="title" content="Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/07/panasonic-expands-its-award-winning-line-of-full-hd-3d-bluray-disc-players.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic, an industry leader in Full HD 3D technology, announced today the expansion of its line of Full HD 3D Blu-ray(TM) Disc players to include the DMP-BDT100. The DMP-BDT100, available in August, joins the DMP-BDT300 and DMP-BDT350 in providing superior 2D and 3D picture quality, as well as such innovative features as VIERA CAST(TM) internet functionality. Pricing will be announced at a later date.

The BDT100 continues the commitment to excellence established by..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3866', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/07/panasonic-expands-its-award-winning-line-of-full-hd-3d-bluray-disc-players.php">Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 27, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Panasonic Expands Its Award Winning Line of Full HD 3D Blu-ray Disc Players</p>

<center><i>DMP-BDT100 Joins Panasonic's Family of Full HD 3D Home Entertainment</center></i><br />
<br />

<p><strong>SECAUCUS, N.J., July 27 /PRNewswire-FirstCall/ -- </strong>Panasonic, an industry leader in Full HD 3D technology, announced today the expansion of its line of Full HD 3D Blu-ray(TM) Disc players to include the DMP-BDT100. The DMP-BDT100, available in August, joins the DMP-BDT300 and DMP-BDT350 in providing superior 2D and 3D picture quality, as well as such innovative features as VIERA CAST(TM) internet functionality. Pricing will be announced at a later date.</p>

<p>The BDT100 continues the commitment to excellence established by the BDT350 which made its US debut at the 2010 Consumer Electronics Show, where it received the esteemed Innovations Award for 3D technology.</p>

<p>"From the beginning, Panasonic has led the way into the 3D era with end-to-end solutions ranging from our critically-acclaimed Full HD 3D VIERA Plasma TVs and 3D Blu-ray Disc Players, to professional 3D camcorders and our work with Hollywood studios on the authoring of Full HD Blu-ray 3D video content," said Richard Simone, Vice President, Panasonic Networking Group. "The addition of the BDT100 3D Blu-ray Disc Player to our line-up marks one of many planned expansions of our 3D offerings designed to give consumers more options and greater access to the immersive world of 3D entertainment for the home."</p>

<p>The BDT100 outputs stunning and immersive Full HD 3D imagery to compatible HD displays. The player also handles a wide variety of audio standards, and upconverts all standard definition video formats to 1080p, the highest-possible video resolution. For nearly 20 years, Panasonic has maintained a base in Hollywood, via Panasonic Hollywood Laboratory, where it has actively studied and created high quality image reproduction technologies in tandem with a number of leading movie studios. Many of the advanced image technologies originally cultivated by Panasonic through this experience are incorporated in the DMP-BDT100. Based on these technologies, the DMP-BDT100 reproduces the ultimate in 3D images, rendering them extremely faithful to the original movies.</p>

<p>In addition to producing superb picture and audio quality, the DMP-BDT100, like the BDT350, also features VIERA CAST, Panasonic's proprietary internet functionality, which brings a variety of streaming services including NETFLIX(TM), Amazon VOD(TM), Pandora&reg;, Twitter and YouTube(TM) Videos into the living room. Wireless LAN capability(*1) eliminates the need to connect a LAN cable, so the DMP-BDT100 can be used without having to worry about the length of the cable.</p>

<p>Users can also watch 2D images (JPEG) and movies (AVCHD(*2)/MPEG2(*2)) from digital cameras and camcorders via the integrated SD card and USB slot, as well as 3D Content shot by a Panasonic HDC-SDT750 Camcorder.</p>

<p>Ease of use remains a major consumer concern and the BDT100 addresses the issue of boot up time by reducing the time to 0.5-second.</p>

<p>As a green innovation company, Panasonic also focuses on important environmental issues in its production system. For example, the single chip LSI, UniPhier, is used for image signal processing. Unification of this chip helps to lower power consumption and achieve a more compact design (reducing the DMP-BDT100 size by 1-17/32 inches(*3)) while also decreasing the burden on the environment by using limited natural resources more efficiently.</p>

<p>For more information on Panasonic's Full HD 3D technology, visit <a target="_blank" href="http://www.panasonic.com/3D/">www.panasonic.com/3D</a>.</p>

<p><br />
<strong>About Panasonic Consumer Electronics Company</strong></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), a market and technology leader in High Definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Panasonic Corporation (NYSE:PC) and the hub of Panasonic's U.S. marketing, sales, service and R&amp;D operations. Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. Information about Panasonic products is available at <a target="_blank" href="http://www.panasonic.com/">www.panasonic.com</a>. Additional company information for journalists is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>

<p>(*1) For this function, the purchase of the Wireless LAN Adaptor (DY-WL10) is necessary. This function is not compatible with public Wireless LAN.<br />
(*2)  SD Memory Card only<br />
(*3)  Dimension size. Compared with the DMP-BDT350.</p>

<p>DMP-BDT100<br />
Technical Specifications</p>

<p>[High Quality Picture and Sound]<br />
True to Cinema Picture Technology</p>

<p>FULL HD 3D Blu-ray Disc(TM) Playback (All Models)</p>

<p>Advanced 2D image technology accumulated over the years at the Panasonic Hollywood Laboratory (PHL) has also been applied to 3D image playback, allowing high-quality images to approach original film quality. Lifelike 3D images with enhanced depth, deep color and texture are beautifully reproduced.</p>

<p>PHL Reference Chroma Processor Plus</p>

<p>PHL Reference Chroma Processor Plus is a high quality image-processing technology developed to precisely process each pixel of the Blu-ray Disc(TM) video signal in the vertical direction. It reproduces color data with twice the accuracy of conventional systems to keep colors faithful and sharp.</p>

<p>New P4HD Enhanced Full HD Upsampling</p>

<p>With the new P4HD enhanced Full HD upsampling, the standard definition movies in DVD also approaches Full HD quality. The picture is intelligently analyzed to have the best matching process done on each pixel.</p>

<p>96-kHz Re-master</p>

<p>The 96-kHz Surround Re-master function enhances the sound quality of CDs and other sources, and even raises the quality of the multi-channel audio data on Blu-ray Discs and DVDs. It increases the amount of sound information, to produce highly realistic sounds.</p>

<p>*CD sampling is done at 88.2kHz. DivX&reg; can be re-mastered both on DVD and CD whereas MP3 data can only be re-mastered on CD.</p>

<p>  [Easy to Use]<br />
  0.5 Sec Ultra Fast Booting</p>

<p>The boot time from standby to power-on has been greatly reduced from prior models.</p>

<p>* Your TV Set must be 'ON'. Ready in 0.5 sec from Power Off (with Quick Start Mode).</p>

<p>  [Networking]<br />
  VIERA CAST(TM) with Wireless LAN System</p>

<p>With optional wireless LAN adaptor (DY-WL10) plugged into the USB terminal, VIERA CAST(TM) can be enjoyed wirelessly by accessing to your Wireless LAN router. You can access NETFLIX, Amazon Video on Demand, YouTube(TM) and other Internet sites from the special VIERA CAST(TM) Home screen.</p>

<p>*For this function, the purchase of the Wireless LAN Adaptor (DY-WL10) is necessary. This function is not compatible with public Wireless LAN.</p>

<p>*VIERA CAST(TM) home screen is subject to change without notice.</p>

<p>*The services through VIERA CAST(TM) are operated by their respective service providers, and service may be discontinued either temporarily or permanently without notice. Therefore, Panasonic will make no warranty for the content or the continuity of the services.</p>

<p>  *All features of websites or content of the service may not be available.<br />
  *Some content may be inappropriate for some viewers.</p>

<p>*Some content may only be available for specific countries and may be presented in specific languages.</p>

<p>*Google, Picasa and YouTube are trademarks of Google Inc. in the United States and/or other countries.</p>

<p>*Amazon, Amazon Video On Demand, and the Amazon Video On Demand logo are trademarks of Amazon.com, Inc. or its affiliates.</p>

<p>  *Requires broadband Internet service.<br />
  *Other trademarks and trade names are those of their respective owners.</p>

<p>*Be sure to update the firmware when a firmware update notice is displayed on the screen. If the firmware is not updated, you will not be able to use the VIERA CAST(TM) function.</p>

<p>  [Environmentally Friendly]<br />
  VIERA Link(TM) Reduces Power Consumption</p>

<p>With Energy Saving Mode, power consumption is automatically minimized when VIERA TV's power is turned off with remote controller. The Blu-ray Disc(TM) players feature compatible Unselected Device Save(*1) which puts Blu-ray Disc(TM) players into standby mode(*2) when the input switches back to TV after movie finishes.</p>

<p>  (*1) Available for 2009 and later VIERA.<br />
  (*2) With Quick Start on.</p>

<p>  Features<br />
  [High Quality Picture and Sound]<br />
  --  FULL HD 3D Blu-ray Disc(TM) Playback<br />
  --  True to Cinema Picture Technology (PHL Reference Chroma Processor Plus &amp; Adaptive High Precision 4:4:4)<br />
  --  Enhanced Full HD Upsampling: New P4HD (Pixel Precision Progressive Processing for HD)<br />
  --  96-kHz Re-master</p>

<p>  [Easy to Use]<br />
  --  VIERA Link(TM)(*1)<br />
  --  Ultra Fast Booting with Quick Start Mode</p>

<p>  [Networking]<br />
  --  VIERA CAST(TM) (NETFLIX, Pandora, YouTube(TM) Videos, Picasa(TM), etc)<br />
  --  3D Video content shot by Panasonic camcorder HDC-SDT750, Movie (AVCHD / MPEG2) &amp; JPEG View with SD Memory Card Slot<br />
  --  Music (MP3), Photos (JPEG), and Video (DivX&reg;(*2) and DivX HD(*2)) Playback on USB (Front1)</p>

<p>  [Environmentally Friendly]<br />
  --  Low Power Consumption 0.1W in Standby Mode with Quick Start Off<br />
  --  Compact Body (W16-15/16 x H1-3/8 x D8-5/32 inch)</p>

<p>(*1) Not all VIERA Link(TM) features are usable with earlier VIERA Link(TM) compatible products.</p>

<p>(*2) DivX&reg; is a registered trademark of DivX, Inc., and is used under license.</p>

<p>Source: Panasonic</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 27, 2010  8:08 PM</b>
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
			<?=getComments(3866)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3866)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/07/panasonic-expands-its-award-winning-line-of-full-hd-3d-bluray-disc-players.php" type="text/javascript" charset="utf-8"></script>
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