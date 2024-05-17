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
		AND e.entry_id = 4621";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4621 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4621 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4621";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/panasonics-2012-smart-viera-plasma-hdtv-model-lineup-builds-upon-its-award-winning-tradition.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4621";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic\'s 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic\'s 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Panasonic\'s 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition" />
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
	<title>HDTV Magazine - Panasonic's 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition</title>
	<meta name="keywords" content="measured diagonally, inch class, inches measured, class inches, viera connect, viera, panasonic, inches, inch, measured, series, diagonally, consumer, class, new, full, technology, connect, plasma, panel, link, black, models, company, design" />
	<meta name="description" content="Continuing its history and tradition of producing award winning Plasma HDTVs, Panasonic Corporation of North America (NYSE: PC), the industry and technology leader in High Definition Plasma televisions, introduced the company's 2012 Smart VIERA line of HDTV Plasmas defining the core of a new IPTV lifestyle at the Consumer Electronics Show. The advent of Smart VIERA HDTV centers on five main points: Networking, Easy Operation, Picture Quality, Eco and Design elements. With 17 new models, ranging in screen sizes from 42-inches to 65 inches, Panasonic expanded its 3D line-up for 2012.

Also new for 2012 is..." />
	<meta name="title" content="Panasonic's 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Panasonic's 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/panasonics-2012-smart-viera-plasma-hdtv-model-lineup-builds-upon-its-award-winning-tradition.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Continuing its history and tradition of producing award winning Plasma HDTVs, Panasonic Corporation of North America (NYSE: PC), the industry and technology leader in High Definition Plasma televisions, introduced the company's 2012 Smart VIERA line of HDTV Plasmas defining the core of a new IPTV lifestyle at the Consumer Electronics Show. The advent of Smart VIERA HDTV centers on five main points: Networking, Easy Operation, Picture Quality, Eco and Design elements. With 17 new models, ranging in screen sizes from 42-inches to 65 inches, Panasonic expanded its 3D line-up for 2012.

Also new for 2012 is..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4621', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/panasonics-2012-smart-viera-plasma-hdtv-model-lineup-builds-upon-its-award-winning-tradition.php">Panasonic's 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=504&category=Plasma HDTVs">Plasma HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">Panasonic's 2012 Smart VIERA Plasma HDTV Model Line-Up Builds Upon Its Award Winning Tradition</p>

<center><i>2012 Models Focus on Enhancing the User's TV Experience With Expanded VIERA Connect&trade; Internet Accessibility, Targeted Web Browser Support, Superior FULL HD 3D, Pristine Picture Quality and Eco Advances</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 9, 2012 /PRNewswire/ --</strong> Continuing its history and tradition of producing award winning Plasma HDTVs, Panasonic Corporation of North America (NYSE: PC), the industry and technology leader in High Definition Plasma televisions, introduced the company's 2012 Smart VIERA line of HDTV Plasmas defining the core of a new IPTV lifestyle at the Consumer Electronics Show. The advent of Smart VIERA HDTV centers on five main points: Networking, Easy Operation, Picture Quality, Eco and Design elements. With 17 new models, ranging in screen sizes from 42-inches to 65 inches, Panasonic expanded its 3D line-up for 2012.</p>

<p>Also new for 2012 is a cloud-based architecture to increase the VIERA Connect IPTV platform to an unlimited number of apps, thereby cementing its reputation for creating innovative and cutting edge products and focusing on providing the consumer with the ultimate in home entertainment. New to select VIERA TVs is the inclusion of a browser, further enhancing the internet platform, as well as the addition of "3D Real Sound" with 8-Train Speakers to further enhance sound quality. In addition, Panasonic continues its commitment to the environment by improving the panel luminance efficiency, as well as producing mercury and lead free panels. To further improve the in-home 3D viewing experience, Panasonic introduced its latest generation of lightweight 3D glasses. Weighing only 27 grams, the latest generation 3D glasses utilize Bluetooth technology and feature a rechargeable battery.</p>

<p>Panasonic is dedicated to bringing new picture improving technologies to HDTVs and the 2012 line-up builds upon that philosophy. The 2012 VIERA HDTVs produce black levels that have consistently been recognized as among the best in the industry, super fast response time, intuitive and therefore easy to operate controls, an infinite number and variety of internet apps, a new VIERA's, clean and simple design incorporating "Glass and Metal" Design concept to express elegant and luxurious theme.</p>

<p>VIERA's elegant design gives it a simple yet powerful presence by harmonizing materials and design. This new design maximizes the natural qualities of glass and metal to visually express the superb performance of the display and billions of colors. The 2012 models employ NeoPlasma Black 2500 provide a crisp image even when the content shows very fast motion. The 2012 panel further reduces reflections and creates sharper pictures with higher contrast in brighter environments. In addition, a new panel structure and pre-discharge control technology contribute to an increased native contrast level. Additionally, all the 3D models include DLNA connections for easy link-up to other DLNA equipped products.</p>

<p>Panasonic's company wide commitment to sustainability and producing products that are ecologically sound is evidenced in the 100,000 hour life span of the Plasma Display panel and the lack of lead or mercury in the panels. Improved power efficiency has been addressed with, among other technology advances, new and improved phosphor mixtures and more efficient electronics.</p>

<p>"Panasonic is proud to be recognized by numerous critics for its superior picture quality and for its continuing commitment to the highest technology standards possible. Never ones to stand on our laurels, the 2012 series of VIERA Plasma HDTVs fine tunes the 3D picture quality to one that rivals what is seen in the theater. Furthermore, we have expanded and enhanced the VIERA connect internet functionality by employing cloud technology, enabling users to access an infinite number of apps," said Henry Hauser, Vice President, Panasonic Marketing, the Merchandising Group "Panasonic is continually striving to surpass the previous year's commercial and critical success, as well as listening to what consumers are saying. That is precisely why Panasonic's 2012 Plasma models will once again prove to be the industry leader. Panasonic is also very proud to have been named one of the top ten global green brands of 2011."</p>

<p>The six VIERA Plasma series, VT50, GT50, ST50, UT50, XT50, U50, feature self illuminating panels with ultimate black levels, NeoPlasma technologies(VT/GT/ST) providing a black filter with a higher efficiency panel that generates the best balance of black and white under brighter environments. The new Louver filter and new high performance panel result in improved external light shading, improved clarity and improved light transmittance. The 2012 models employ the NeoPlasma Black 2500 (VT/GT/ST/UT), a 6,220,800 pixel cells FULL local dimming, 24,576 steps of gradation technology (VT/GT) (previously only available in professional monitors), a new custom driver LSI and a fast switching phosphor panel on all of Panasonic's 1080p 3D models</p>

<p>Panasonic also continues its relationship with THX with six Full HD 3D Plasma HDTVs, certified in both 2D and 3D mode by the prestigious company founded by George Lucas.  THX certification indicates to the consumer that the picture quality has been certified to meet the stringent standards of Hollywood's top film makers.</p>

<p><br />
<strong>VT50 Series</strong></p>

<p>The VT50 series is the FULL HD 3D Plasma flagship series and is available in two screen sizes- the TC-P65VT50, 65-inch class (64.7 inches measured diagonally) and the TC-P55VT50, 55-inch class (55.1 inches measured diagonally). These two top of the line TVs offers a revolutionary level of picture quality.  FULL HD 3D; 1080p FULL HD resolution; Infinite Black Ultra Panel, Deep Black hues are achieved thanks to new and advanced pre-discharge technology; VIERA Connect with Web browser, and built-in Wi-Fi; 2500 FFD (Focused Field Drive); Fast Switching Phosphors; 2D --> 3D conversion; 24,576 steps of gradation technology ;THX in both 2D and 3D modes; ISFccc Calibration Mode with Advanced Calibration. Calibrators adjust the detailed picture setting with the calibration software (CALMAN&trade;)provided by SpectraCal Inc.</p>

<p>Social Networking TV function to allow users to access social network sites while simultaneously watching TV; Multitasking feature to switch between apps ; 3D Real Sound with 8-Train Speakers; a new Louver Filter; a VIERA Touch Pad Controller; Bluetooth; DLNA; VIERA Link, a technology that utilizes HDMI-CEC (Consumer Electronics Control) and allows a consumer to operate all VIERA Link compatible A/V components using only the TV's remote control; Media Player, allows one to view digital photos and HD video recorded on a SD Memory Card and USB Memory Stick;  four HDMI connections and three USB ports. The VT50 series continues the design evolution as seen in last year's VT30 series with a stunning one sheet of glass design. The Flat and lustrous, this single pane of glass is totally obstruction-free. It expresses a minimalist sense of beauty by stripping away all unnecessary elements with a newly designed gradation metal pedestal.</p>

<p><br />
<strong>GT50 Series</strong></p>

<p>The GT50 series includes four screen sizes – the TC-P50GT50, 50-inch class (49.9 inches measured diagonally); TC-P55GT50, 55-inch class (55.1 inches measured diagonally); TC-P60GT50, 60-inch class (60.1 inches measured diagonally); TC-P65GT50, 65-inch class (64.7 inches measured diagonally). The series features FULL HD 3D; Infinite Black Pro Panel; VIERA Connect with Web browser and built-in Wi-Fi; 1080p FULL HD resolution; 2500 FFD (Focused Field Drive); Fast Switching Phosphors; 2D --> 3D conversion; THX in both the 2D and 3D modes; 24,576 steps of gradation technology; Social Networking TV function; Multitasking; 3D Real Sound with 8 train speakers; Media Player; Bluetooth; DLNA; VIERA Link; four HDMI connections and three USB ports. Additionally the GT50 models incorporate the glass &amp; metal design – Stylish Metal Frame with a gradation metal pedestal.</p>

<p><br />
<strong>ST50 Series</strong></p>

<p>The TC-P50ST50, 50-inch class (49.9 inches measured diagonally); the TC-P55ST50, 55-inch class (55.1 inches measured diagonally); the TC-P60ST30, 60-inch class (60.1 inches measured diagonally) and the TC-P65ST50, 65 inch class (64.7 inches measured diagonally) comprise the ST 50 series of FULL HD 3D VIERA HDTVs. The ST30 models include Infinite Black Pro Panel; VIERA Connect with Web browser and built-in Wi-Fi; 1080p FULL HD resolution; 2500 FFD (Focused Field Drive); Fast Switching Phosphors; 2D --> 3D conversion; Social Networking TV function; 3D Real Sound with 8-Train Speakers; Media Player; Bluetooth; DLNA; VIERA Link, three HDMI connections and two USB ports.</p>

<p><br />
<strong>UT50 Series</strong></p>

<p>There are four screen sizes in the UT50 series – the TC-P42UT50, 42 inch class (41.6 inches measured diagonally); TC-P50UT50, 50 inch class (49.9 inches measured diagonally); TC-P55UT50, 55 inch class (55.1 inches measured diagonally); TC-P60UT50, 60 inch class (60.1 inches measured diagonally). All offer FULL HD 3D; 1080p FULL HD resolution; VIERA Connect (Wi-Fi ready); 2500 FFD (Focused Field Drive); Fast Switching Phosphors; 2D --> 3D conversion; Social Networking TV  function; Media Player; Bluetooth; DLNA; VIERA Link; two HDMI connections and two USB ports.</p>

<p><br />
<strong>XT50 Series</strong></p>

<p>The XT50 series provides the consumer with an affordable 3D solution with Online Movies feature, a service that provides select Panasonic's IPTV functionality by adding five of the most popular movies to the TV's internet functionality. The TC-P42XT50, 42 inch class (41.6 inches measured diagonally) and the TC-P50XT50, 50 inch class (49.9 inches measured diagonally) produce 720p resolution and features Online Movies, a service that provides select Panasonic's IPTV functionality by adding five of the most popular movies to the TV's internet functionality; 2D --> 3D conversion; 600Hz Sub-field Drive; Bluetooth; Media Player; DLNA; VIERA Link; two HDMI and two USB connections.</p>

<p><br />
<strong>U50 Series</strong></p>

<p>The TC-P50U50, 50 inch class (49.9 inches measured diagonally) is the lone non-3D in this year's HDTV Plasma model line-up. The model features 1080p FULL HD resolution; Fast Switching Phosphors, Media Player; 600Hz Sub-Field Drive ;Game Mode ;two HDMI connections and one USB port.</p>

<p>Panasonic first introduced its concept of connected HDTVs five years ago and in 2012 VIERA Connect(1), Panasonic's proprietary IPTV platform, continues to improve its functionality by employing a cloud based technology and philosophy, allowing for an unlimited number of apps being available to the consumer. VIERA Connect provides access to apps in such categories as video and music, social networking, games, news and lifestyle, sports, health and fitness and kids education adhering to the philosophy that the consumer should be able to personalize the internet experience, VIERA Connect features a market site, where the consumer can select specific apps or accessories from a limitless selection.</p>

<p>Popular sites, including Netflix&trade;, Amazon Instant Video&trade;, YouTube&trade;, Pandora&reg;, Twitter, Facebook, Bloomberg News, AP, Wall Street Journal, Accuweather&reg; , Skype&trade;, Wealth TV, CinemaNow, Hulu Plus&trade;, sports sites Fox Sports, MLB, NBA, NHL and MLS(2) ,BodyMedia and Withings continue on VIERA Connect and are joined by a host of exciting features and apps, all optimized for the best possible user experience. On November 1, 2011, Panasonic introduced an app, Social Networking TV that allows one to access social network sites such as Twitter and Facebook, while simultaneously enjoying programming on their VIERA HDTV. At the same time, Panasonic also announced the addition of a new gaming application from PlayJam and a movie/video channel dedicated to Bollywood, called BigFlix. BigFlix will allow users to access Bollywood Video on Demand, while PlayJam is a game channel with multiple game apps</p>

<p>With ease of use as a major focus, VIERA Connect's interface implements quick keyword input (like a cell phone) to help retrieve favorite content faster and easier. An optional Skype Communication camera (TY-CC20W) allows the consumer to communicate with friends and family via a large TV, rather than on a small computer or smart phone screen. There is also an app that allows one to use their iPhone/iPad as a remote.</p>

<p>Panasonic HDTVs also employ VIERA Link&trade; - a technology that utilizes HDMI-CEC (Consumer Electronics Control) and allows a consumer to operate all VIERA Link compatible A/V components using only the TV's remote control and helpful on-screen menus. In addition to operating a VIERA HDTV, video source (Blu-ray Disc&trade; and DVD player) and home theater receiver, A VIERA Link capable Network Camera can be connected to a VIERA HDTV and controlled via VIERA remote. Users can then watch their child, pet and/or property by installing the camera and networking to the VIERA HDTV via Ethernet cable</p>

<p><br />
<strong>About Panasonic Consumer Marketing Company of North America</strong></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Marketing Company of North America, a Division of Panasonic Corporation of North America, the principal North American Subsidiary of Panasonic Corporation (NYSE: PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations, offers a wide-range of consumer solutions in the U.S. and Canada.  The Company's portfolio of innovative consumer products ranges from VIERA Full HD 3D Televisions, Blu-ray players, LUMIX Digital Cameras, Camcorders, Home Audio, Cordless Phones, Home Appliances, Wellness and Personal Care products and more.</p>

<p>Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. Panasonic was the only Consumer Electronics company to be listed in the top ten brands on the Interbrand Best Global Green Brands 2011 ranking. Follow Panasonic on Twitter @panasonicdirect, and additional company information for media is available at <a target="_blank" href="http://www.panasonic.com/pressroom/">www.panasonic.com/pressroom</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2012 10:22 PM</b>
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
			<?=getComments(4621)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4621)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/panasonics-2012-smart-viera-plasma-hdtv-model-lineup-builds-upon-its-award-winning-tradition.php" type="text/javascript" charset="utf-8"></script>
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