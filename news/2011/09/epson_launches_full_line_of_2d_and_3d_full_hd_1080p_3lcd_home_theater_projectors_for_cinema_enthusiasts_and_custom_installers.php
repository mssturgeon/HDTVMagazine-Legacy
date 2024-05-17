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
		AND e.entry_id = 4515";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4515 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4515 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4515";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/09/epson-launches-full-line-of-2d-and-3d-full-hd-1080p-3lcd-home-theater-projectors-for-cinema-enthusiasts-and-custom-installers.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4515";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers" />
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
	<title>HDTV Magazine - Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers</title>
	<meta name="keywords" content="home cinema, pro cinema, epson america, cinema home, white light, home, cinema, epson, color, projectors, pro, america, full, light, technology, viewing, lcd, lamp, quality, line, image, mode, screen, brightness, projector" />
	<meta name="description" content="Epson America today announced its first line of 2D and 3D Full HD 1080p home theater projectors with the new 3LCD&amp;trade; PowerLite&amp;reg; Pro Cinema 6010, and Home Cinema 5010/5010e and 3010/3010e. With both home cinema enthusiasts and custom installers in mind, Epson utilizes new Bright 3D Drive technology for an exceptional 3D viewing experience at home on a big screen. Epson's new line offers a range of value-add features and performance at varying price levels to accommodate virtually all home cinema projection needs, including an entry-level solution available for less than..." />
	<meta name="title" content="Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/09/epson-launches-full-line-of-2d-and-3d-full-hd-1080p-3lcd-home-theater-projectors-for-cinema-enthusiasts-and-custom-installers.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Epson America today announced its first line of 2D and 3D Full HD 1080p home theater projectors with the new 3LCD&amp;trade; PowerLite&amp;reg; Pro Cinema 6010, and Home Cinema 5010/5010e and 3010/3010e. With both home cinema enthusiasts and custom installers in mind, Epson utilizes new Bright 3D Drive technology for an exceptional 3D viewing experience at home on a big screen. Epson's new line offers a range of value-add features and performance at varying price levels to accommodate virtually all home cinema projection needs, including an entry-level solution available for less than..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4515', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/09/epson-launches-full-line-of-2d-and-3d-full-hd-1080p-3lcd-home-theater-projectors-for-cinema-enthusiasts-and-custom-installers.php">Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  8, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=495&category=Front Projection">Front Projection</a></b>, <b><a href="/category.php?id=367&category=LCD HDTVs">LCD HDTVs</a></b>
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
				<p class="prtitle">Epson Launches Full Line of 2D and 3D Full HD 1080p 3LCD Home Theater Projectors for Cinema Enthusiasts and Custom Installers</p>

<center><i>PowerLite Pro Cinema 6010, Home Cinema 5010/5010e and 3010/3010e Deliver Incredible Brightness for Exceptional 3D and 2D Performance at Home Starting Under $1,600</center></i><br />
<br />

<p><strong>INDIANAPOLIS, Sept. 8, 2011 /PRNewswire/ -- (CEDIA Expo 2011, Booth 3751) -- </strong>Epson America today announced its first line of 2D and 3D Full HD 1080p home theater projectors with the new 3LCD&trade; PowerLite&reg; Pro Cinema 6010, and Home Cinema 5010/5010e and 3010/3010e. With both home cinema enthusiasts and custom installers in mind, Epson utilizes new Bright 3D Drive technology for an exceptional 3D viewing experience at home on a big screen. Epson's new line offers a range of value-add features and performance at varying price levels to accommodate virtually all home cinema projection needs, including an entry-level solution available for less than $1,600.</p>

<p>As the number-one selling projector brand worldwide(i) Epson utilizes full HD, active shutter 3D, with 1080p resolution and Bright 3D Drive Technology, which drives the panels at 480 Hz, essentially doubling the image refresh rate of 240Hz panels, delivering ultra-bright images and reduced crosstalk for the ultimate in 3D viewing at home. These projectors offer high brightness -- up to 2,400 lumens of color and white light output(ii) -- and enable viewing on larger screens and in a range of ambient light conditions. Combined with remarkable contrast and a number of value-add features, including wireless installation options and split screen mode for watching two pictures at once or watching TV and using the Internet at the same time, the Pro Cinema 6010, and Home Cinema 5010/5010e and 3010/3010e deliver top 3D and 2D performance and image quality in each of their respective categories.</p>

<p>"A large issue with 3D projection at home is the loss of brightness in image and picture quality," said Jason Palmer, marketing manager, Epson America. "With the 480 Hz drive technology developed by Epson, we've been able to create a bright, crisp image for home users to truly enjoy the 3D experience. Coupled with exceptional 2D quality viewing, these new projectors are an outstanding choice for any home cinema."</p>

<p><br />
<strong>More About the Pro Cinema 6010 and Home Cinema 5010/5010e</strong></p>

<p>The Pro Cinema 6010 and Home Cinema 5010 and 5010e (wireless) feature an outstanding contrast ratio of up to 200,000:1 and up to 2,400 lumens of color and white light output for incredible black levels with a clear, clean picture. With Epson's Super Resolution technology and FineFrame&trade; technology for smoother frame interpolation and sharper video quality (2D Mode), cinema filter feature for larger color space and improved color fidelity and a Fujinon&reg; OptiCinema&trade; lens (2.1 zoom ratio), these projectors deliver top-of-the-line performance and quality. And to further enhance the 3D experience, they also include 2D to 3D conversion. The flagship Pro Cinema 6010 also adds two anamorphic lens modes, two pairs of 3D glasses, ISF calibration, color isolation, and a ceiling mount, cable cover and extra lamp for installation flexibility.</p>

<p><br />
<strong>More About the Home Cinema 3010/3010e</strong></p>

<p>Offering big screen home 3D viewing, the Home Cinema 3010 and 3010e (wireless) deliver up to 2,200 lumens of color and white light output along with a superb contrast ratio of up to 40,000:1 for outstanding black levels. In addition, both models feature two built-in 10W speakers for great home cinema sound. The Home Cinema 3010 also includes two pairs of 3D glasses. These affordably priced projectors offer outstanding 3D and 2D big screen image quality for a reasonable price and are ideal for home cinema enthusiasts who don't require installation or professional-level color tools.</p>

<p><br />
<strong>Additional Features of Epson's 2D and 3D Full HD 1080p Home Theater Projector Line-up</strong></p>

<p>Further augmenting performance, value and total cost of 3LCD projector ownership, each model also shares the following features:</p>

<p>Split screen capabilities in 2D mode for watching two pictures at once, or watching TV and using the Internet at the same time<br />
Epson 3LCD technology for amazing color and detail<br />
Five color modes in 2D and two color modes in 3D to easily adapt to different viewing environments<br />
Epson's exclusive 230W E-TORL&reg; (Epson Twin Optics Reflection Lamp) offering high brightness and up to 6,000 hours of lamp life(iii)<br />
HDMI (two), component video, composite video, VGA, RCA audio, and USB connections for flexibility<br />
Wireless transmitter included with Home Cinema 3010e and 5010e for ultimate installation flexibility without wires, using the WirelessHD standard to send uncompressed high-definition video over short distances to the projector<br />
Two pairs of 3D glasses included with Home Cinema 3010 and Pro Home Cinema 6010; 3D glasses sold separately for Home Cinema 3010e, 5010 and 5010e</p>

<p><br />
<strong>Availability and Support</strong></p>

<p>The PowerLite Pro Cinema 6010 will be available for less than $4,000, while the Home Cinema 5010 and 5010e are listed at under $3,000 and $3,500, respectively; all will be available through authorized Epson projector dealers in November. The Home Cinema 3010 and 3010e will be available online and through dealers in October for $1,599.99 and $1,799.99. These projectors will also be on display at CEDIA Expo 2011.</p>

<p>Each model comes with Epson's industry leading service and support, including toll-free access to Epson's PrivateLine&reg; priority technical support, 90-day limited lamp warranty, and free two-business day exchange with Extra Care(SM) Home Service. The Home Cinema 5010/5010e and 3010/3010e include a two-year limited warranty, while the Pro Cinema 6010 includes a three-year limited warranty. For more information, please see the detailed spec sheets.</p>

<p><br />
<strong>About Epson America Inc.</strong></p>

<p>Epson is a global imaging and innovation leader dedicated to exceeding the vision of customers worldwide through its compact, energy-saving, high-precision technologies, with a wide lineup ranging from printers and 3LCD projectors for business and the home, to electronic and crystal devices. Led by the Japan-based Seiko Epson Corporation, the Epson Group comprises nearly 75,000 employees in 100 countries around the world. Epson is proud of its ongoing contributions to the global environment and the communities in which it operates and has been named to the Dow Jones Sustainability World Index, an indicator for leading companies in economic, environmental and social criteria, for the third year in a row. Epson America, Inc. based in Long Beach, Calif. is Epson's regional headquarters for the U.S., Canada, and Latin America. To learn more about Epson, please visit: <a target="_blank" href="http://www.Epson.com/">www.Epson.com</a> You may also connect with Epson America on Facebook (http://www.facebook.com/EpsonAmerica), Twitter (http://twitter.com/EpsonAmerica) and (http://twitter.com/EpsonEducation) and YouTube (http://www.youtube.com/EpsonTV).</p>

<p>Note: Epson and E-TORL are registered trademarks of Seiko Epson Corporation. PowerLite and PrivateLine are registered trademarks, FineFrame is a trademark, and Extra Care is a service mark of Epson America, Inc.  All other product and brand names are trademarks and/or registered trademarks of their respective companies. Epson disclaims any and all rights in these marks.</p>

<p>(i) Based upon Q2 2010 worldwide front projection market share estimates from Pacific Media Associates</p>

<p>(ii) Color and white light output will vary depending on mode selected. White light output measured using ISO 21118 standard.</p>

<p>(iii) Lamp life will vary depending upon mode selected, environmental conditions and usage.  Lamp brightness decreases over time.</p>

<p>SOURCE Epson America</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  8, 2011  8:02 PM</b>
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
			<?=getComments(4515)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4515)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/09/epson-launches-full-line-of-2d-and-3d-full-hd-1080p-3lcd-home-theater-projectors-for-cinema-enthusiasts-and-custom-installers.php" type="text/javascript" charset="utf-8"></script>
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