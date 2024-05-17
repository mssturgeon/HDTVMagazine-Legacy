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
		AND e.entry_id = 1337";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1337 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1337 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1337";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/04/canon-introduces-new-xl-h1s-and-xl-h1a-hd-camcorders-at-nab.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1337";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB" />
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
	<title>HDTV Magazine - Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB</title>
	<meta name="keywords" content="genuine canon, frame rates, oom lens, video oom, camcorders provide, canon, new, image, lens, camcorders, video, output, control, audio, settings, focus, feature, manual, production, operation, include, zoom, digital, camera, both" />
	<meta name="description" content="Building on the success of its acclaimed XL H1 High Definition (HD) camcorder, Canon U.S.A., Inc., a leader in digital imaging technology, has introduced the new shoulder mount XL H1S and XL H1A HD camcorders, which include new advanced features requested by professional users for improved operation and image control.

Canon's new XL H1S and XL H1A camcorders, which will be on display at the 2008 NAB Show (Booth #SU3020), feature an updated Canon 20x HD Video Lens with three independent manual adjustment rings (focus, zoom and iris), as well as enhanced manual focus and zoom control. Additionally, the XL H1S and XL H1A incorporate..." />
	<meta name="title" content="Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/04/canon-introduces-new-xl-h1s-and-xl-h1a-hd-camcorders-at-nab.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Building on the success of its acclaimed XL H1 High Definition (HD) camcorder, Canon U.S.A., Inc., a leader in digital imaging technology, has introduced the new shoulder mount XL H1S and XL H1A HD camcorders, which include new advanced features requested by professional users for improved operation and image control.

Canon's new XL H1S and XL H1A camcorders, which will be on display at the 2008 NAB Show (Booth #SU3020), feature an updated Canon 20x HD Video Lens with three independent manual adjustment rings (focus, zoom and iris), as well as enhanced manual focus and zoom control. Additionally, the XL H1S and XL H1A incorporate..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1337', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/04/canon-introduces-new-xl-h1s-and-xl-h1a-hd-camcorders-at-nab.php">Canon Introduces New XL H1S and XL H1A HD Camcorders at NAB</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>April 11, 2008</b>
							</td><td id="article_category">
								Categories: 
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
				<p class="prtitle">Canon's New XL H1S and XL H1A HD Camcorders Provide Advanced Features for Enhanced Control and Operation</p>

<center><i>New 20x HD Video Lens, Enhanced Image Control and Other User-Requested Refinements Serve the Needs of Event Video, Documentary Filmmaking and Pro Markets</i></center><br />
<br />

<p>NAB2008<br />
Booth #SU3020</p>

<p><B>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)</B>--Building on the success of its acclaimed XL H1 High Definition (HD) camcorder, Canon U.S.A., Inc., a leader in digital imaging technology, has introduced the new shoulder mount XL H1S and XL H1A HD camcorders, which include new advanced features requested by professional users for improved operation and image control.</p>

<p>Canon's new XL H1S and XL H1A camcorders, which will be on display at the 2008 NAB Show (Booth #SU3020), feature an updated Canon 20x HD Video Lens with three independent manual adjustment rings (focus, zoom and iris), as well as enhanced manual focus and zoom control. Additionally, the XL H1S and XL H1A incorporate an expanded array of controls for customizing the image, display, and operation to match individual needs. Utilizing the industry's most affordable digital video recording media - HDV tapes - both models are ideal for such long-form production applications as wedding and event videography, digital filmmaking, documentary production and media education.</p>

<p>Both the new XL H1S and XL H1A deliver superior HD image quality with their Genuine Canon XL interchangeable lens system, three 1/3 inch, 1.67 Megapixel CCD Image Sensors, and Canon's proprietary DIGIC DVII HD Image Processor. Both models also feature a durable six-pin IEEE 1394 terminal, providing a more robust connection to external hardware.</p>

<p>The XL H1S model provides expanded connectivity by incorporating HD-SDI (SMPTE 299M)/SD-SDI (SMPTE272M) output with embedded audio and time code, providing a raw, uncompressed 1.485 Gbps signal for demanding live production environments, SMPTE Time Code input and output terminals, and a Genlock output terminal for multi-camera shooting situations.</p>

<p>"The new XL H1S and XL H1A HD camcorders continue to meet the needs of producers, event videographers, and digital cinematographers with superior Canon optical and imaging technology, and with the most cost-effective medium for high-quality HD video capture and storage, which is HDV tape," noted Yuichi Ishizuka, senior vice president and general manager, Consumer Imaging Group, Canon U.S.A. "Canon's unsurpassed quality is seen in our broadcast HDTV lenses and the Canon EOS digital photography cameras. This know-how directly benefits the quality of Canon professional and consumer HD camcorders. Recently, our consumer camcorders claimed the number-one position in total high definition camcorder unit sales above $200 in both January and February of 2008, according to The NPD Group's Retail Tracking Service."</p>

<p><br />
<B>New Genuine Canon 20x HD Video Zoom Lens III</B></p>

<p>Leveraging Canon's expertise as a worldwide leader in optics, the XL H1S and XL H1A feature a redesigned Genuine Canon 20x HD Video Zoom Lens equipped with Canon's XL lens mount. The XL lens mount enables users to interchange lenses, such as Canon's optional 6x wide-angle lens, Canon EF photographic lenses, and many other lenses for specialized image capture. The XL H1S and XL H1A HD camcorders' new Genuine Canon 20x HD Video Zoom Lens III offers independent manual zoom, focus and iris rings, which can operate simultaneously. These rings have been redesigned for easy access and comfortable operation. Other innovations of the lens include: three sensitivity settings for the manual focus ring; fast and slow zoom speeds; smooth movement when starting or stopping zooms; and selectable rotational angles between the tele and wide ends of the lens zoom. Simultaneous zoom and focus control are also possible in either Manual or Auto Focus mode. The new Genuine Canon 20x HD Video Zoom Lens III incorporates aspherical lenses to reduce flare and fluorite lens elements to minimize chromatic aberration. Additionally, the lens features Canon's SuperRange Optical Image Stabilization (OIS) system, which corrects camera shake instantly on hand-held shots at full telephoto, shots taken from a moving vehicle, or other challenging situations.</p>

<p><br />
<B>User-Definable Operation</B></p>

<p>The new camcorders provide users with an unprecedented range of image/color settings, camera-control functions, and viewfinder display choices. Video can be captured and output in multiple frame rates to meet the demands of diverse production assignments. These frame rates include: 60i for mainstream production; 30F for perfect frame grabs or Web delivery; and 24F for emulating the look of motion-picture film. Canon's Factory Service Center can also provide an optional upgrade to add 50i and 25F frame rates. The camcorders can be switched in all frame rates between 1080-line 16:9 aspect ratio HD video and 480-line 4:3 aspect ratio SD video (or 576 lines in 50i or 25F mode).</p>

<p>Both the XL H1S and XL H1A HD camcorders provide Total Image Control for customizing image and color settings, with a total of 23 custom presets available for image-quality adjustment, with the range of image color presets extended from +/-9 to +/-50 steps for fine-tuning the picture (an important feature for achieving the visual subtleties favored by cinematographers). Custom image pre-set files can be stored to an onboard SD/SDHC Memory Card and shared with another Canon XL H1S, XL H1A, or with Canon XH G1 and XH A1 HD camcorders for seamless multi-camera production work.</p>

<p>Custom camera functions and operation settings total 21 items and 33 sub-items for achieving creative "looks" for footage. Among these new functions are: "shockless" white balance for smooth, color-accurate dissolves between two pre-set white balance values (adjustable from 2000K to 15000K); the ability to increase gain in 0.5dB increments from 0dB up to +18dB, and there is also additional -3dB and +36dB settings. A selective noise-reduction function is available for reducing the noise in targeted color areas, which is important for blue/green chroma-key applications in special effects work and for shooting insufficiently illuminated backgrounds.</p>

<p>Using the custom display settings feature, a camera operator can tailor the display to their shooting style. These include 22 items and 40 sub-items for selection of status information that can be shown in the XL H1S and XL H1A HD camcorders' 2.4" combination EVF/LCD monitor.</p>

<p><br />
<B>Audio Capture and A/V Connectivity</B></p>

<p>User-requested refinements in the XL H1S and XL H1A audio capabilities include: two-channel audio performance; two built-in XLR terminals with separate audio-input sensitivity settings; the ability to simultaneously record sound from an external microphone and the supplied internal microphone; line output level switching; an audio (manual) limiter; a new metal headphone jack for reliable connectivity. Another important new feature is embedded audio (and SMPTE time code) in the XL H1S HD camcorder's uncompressed HD-SDI/SD-SDI digital output via an industry-standard BNC connector.</p>

<p>Other improved connections on the XL H1S and XL H1A HD camcorders include an external LCD monitor output for critical focus confirmation on an optional larger monitor simultaneous RCA and BNC video output for added monitoring and dubbing convenience, and a photo-flash accessory shoe to support use of Canon EX Series Speedlites.</p>

<p>The new Canon XL H1S HD camcorder is scheduled to be available in early June for an estimated retail price of $8,999, and the XL H1A, which will have an estimated retail price of $5,999, is scheduled to be available in mid-July.</p>

<p><br />
<B>About Canon U.S.A., Inc.</B></p>

<p>Canon U.S.A., Inc. delivers consumer, business-to-business, and industrial imaging solutions. Its parent company, Canon Inc. (NYSE:CAJ), a top patent holder of technology, ranking third overall in the U.S. in 2007*, with global revenues of $39.3 billion, is listed as one of Fortune's Most Admired Companies in America and is on the 2007 BusinessWeek list of "Top 100 Brands." To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting www.usa.canon.com/pressroom.</p>

<p>*IFI Patent Intelligence Press Release, January 2008.</p>

<p>All referenced product names, and other marks, are trademarks or registered trademarks of their respective owners.</p>

<p>Availability, prices, and specifications of all products are subject to change without notice. Actual prices are set by individual dealers and may vary.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>April 11, 2008  6:20 AM</b>
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
			<?=getComments(1337)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1337)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/04/canon-introduces-new-xl-h1s-and-xl-h1a-hd-camcorders-at-nab.php" type="text/javascript" charset="utf-8"></script>
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