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
		AND e.entry_id = 643";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 643 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 643 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 643";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/07/the-new-tivo-hd-dvr-29999.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 643";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The New TiVo HD DVR - $299.99" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The New TiVo HD DVR - $299.99" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The New TiVo HD DVR - $299.99" />
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
	<title>HDTV Magazine - The New TiVo HD DVR - $299.99</title>
	<meta name="keywords" content="new tivo, tivo service, kid one, amazon unbox, tivo kid, tivo, new, dvr, home, cable, television, video, broadband, service, digital, set, search, entertainment, shows, content, amazon, consumers, features, experience, movie" />
	<meta name="description" content="TiVo Inc. (NASDAQ: TIVO), the creator of and a leader in television services for digital video recorders (DVRs), announced today the launch of a new TiVo high definition DVR, delivering a premium HD experience at an affordable price. Starting today, consumers can pre-order TiVo HD at www.tivo.com for just $299.99, down from the original TiVo Series3&amp;trade; HD Digital Media Recorder at $799.99. Product expected to arrive on retail shelves in early August. The new TiVo HD DVR is the ultimate HDTV companion, maximizing the HD cable experience by combining a new popular price with the clarity of HD programming and our Emmy&amp;reg; award-winning TiVo&amp;trade; service. The new TiVo HD is also a Digital Cable Ready set-top-box that works seamlessly with any cable provider in the U.S. Moreover, the new product also enables the latest and greatest exclusive TiVo service features such as Movie &amp; TV Downloads from Amazon.com, Home Movie Sharing and universal Swivel&amp;trade; search, delivering the best of broadband video directly to the television set." />
	<meta name="title" content="The New TiVo HD DVR - $299.99" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The New TiVo HD DVR - $299.99" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/07/the-new-tivo-hd-dvr-29999.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="TiVo Inc. (NASDAQ: TIVO), the creator of and a leader in television services for digital video recorders (DVRs), announced today the launch of a new TiVo high definition DVR, delivering a premium HD experience at an affordable price. Starting today, consumers can pre-order TiVo HD at www.tivo.com for just $299.99, down from the original TiVo Series3&amp;trade; HD Digital Media Recorder at $799.99. Product expected to arrive on retail shelves in early August. The new TiVo HD DVR is the ultimate HDTV companion, maximizing the HD cable experience by combining a new popular price with the clarity of HD programming and our Emmy&amp;reg; award-winning TiVo&amp;trade; service. The new TiVo HD is also a Digital Cable Ready set-top-box that works seamlessly with any cable provider in the U.S. Moreover, the new product also enables the latest and greatest exclusive TiVo service features such as Movie &amp; TV Downloads from Amazon.com, Home Movie Sharing and universal Swivel&amp;trade; search, delivering the best of broadband video directly to the television set." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=643', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/07/the-new-tivo-hd-dvr-29999.php">The New TiVo HD DVR - $299.99</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>July 24, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">It's Here - The New TiVo HD DVR - The Ultimate HDTV Companion at a Price Consumers Have Been Asking For</p>

<center><i>The new TiVo&reg; HD DVR seamlessly combines ease of use, new content and affordability.</i></center><br />
<br />

<p><img src="http://www.lduhtrp.net/image-1683082-10440996" width="1" height="1" border="0"/><a href="http://www.jdoqocy.com/click-1683082-10440996"><img src="/images/products/tivo-hd-dvr.jpg" alt="TiVo HD DVR" align="left" /></a><B>ALVISO, Calif.- July 24, 2007 </B>- TiVo Inc. (NASDAQ: TIVO), the creator of and a leader in television services for digital video recorders (DVRs), announced today the launch of a new TiVo high definition DVR, delivering a premium HD experience at an affordable price. Starting today, consumers can pre-order TiVo HD at www.tivo.com for just $299.99, down from the original TiVo Series3&trade; HD Digital Media Recorder at $799.99. Product expected to arrive on retail shelves in early August. The new TiVo HD DVR is the ultimate HDTV companion, maximizing the HD cable experience by combining a new popular price with the clarity of HD programming and our Emmy&reg; award-winning TiVo&trade; service. The new TiVo HD is also a Digital Cable Ready set-top-box that works seamlessly with any cable provider in the U.S. Moreover, the new product also enables the latest and greatest exclusive TiVo service features such as Movie & TV Downloads from Amazon.com, Home Movie Sharing and universal Swivel&trade; search, delivering the best of broadband video directly to the television set.</p>

<p>"TiVo HD extends the TiVo experience to an even wider audience than ever, giving sports and entertainment enthusiasts the ultimate companion to their HDTV set," said Tom Rogers, CEO and President of TiVo. "It is the ultimate media centerpiece for the living room with the broadest selection of broadband content, right alongside your favorite broadcast and cable programs, giving HDTV viewers more choice and control than they've ever had before. And it can be used in place of the customer's existing cable box."</p>

<p>The TiVo HD is designed to fit seamlessly with home entertainment centers, replacing cable boxes while complimenting other entertainment devices. It is compatible with digital cable, analog cable and digital antenna (ATSC). TiVo HD offers 20 hours of HD or up to 180 hours of standard definition content. The new TiVo HD DVR allows users to record two HD channels at the same time, while watching a third previously recorded show. With a built-in Ethernet jack, two CableCARD&trade; slots and USB ports, TiVo HD also provides advanced connectivity and easy networking, making it simple to access an additional suite of exclusive TiVo features.</p>

<p>"With close to 30% of U.S. homes now owning at least one high-definition TV, the market is ready for an HD-enabled platform that combines the best in DVR technology with the best of both traditional and web-based video entertainment," notes Michael Greeson, president of The Diffusion Group, a consumer technology think tank. "TiVo HD sets the standard in terms of non-PC Internet-enabled set-top platforms, not to mention providing the seamless interface to which TiVo users have become accustomed."</p>

<p>"TiVo HD is a perfect complement to the HDTV sets that are quickly becoming the standard for home entertainment," said Jim Denney, Vice President of Product Marketing at TiVo. "With an affordable price and uncompromised quality, TiVo HD is an obvious choice for anyone with a passion for home entertainment and HD programming."</p>

<p>TiVo HD includes access to a number of renowned TiVo features, furthering the difference between the TiVo service and generic DVR competitors, such as:</p>

<p> * Movie & TV Downloads - In partnership with Amazon.com, TiVo brings you Amazon Unbox&trade; on TiVo&reg;, allowing you to download thousands of movies and TV shows straight to your TiVo DVR. Amazon Unbox on TiVo allows you to rent or buy movies from Amazon Unbox using your remote, download them to your TiVo box over your home network, and enjoy them right on your television set whenever you want. The movies you want are always in stock and new releases are available for purchase the same day they arrive on DVD. Best of all, order with your TiVo remote from the comfort of your living room and your rentals and purchases end up in your Now Playing list, right where you'd expect them to be. It's like having an entire video store connected to your TV.</p>

<p></p>

<p> * Universal Swivel&trade; Search - Exclusive to the TiVo service, universal Swivel search lets you quickly find everything you want in the world of broadcast and broadband television with a single, powerful search. It's the first truly TV centric onscreen search tool that allows subscribers to explore and discover broadcast, cable, and broadband content in an easy-to-use experience. TiVo subscribers can search using the way they intuitively think about television; that is, by starting with a program they currently enjoy and using elements of that program to find more of what they like. Universal Swivel search allows viewers to seamlessly link from descriptions of one program to all others that have common elements, including program name, actors, or suggestions based on other viewers' feedback.</p>

<p> </p>

<p> * TiVoCast - TiVoCast delivers original video programming directly to your TiVo box over your broadband Internet connection from a variety of media brands and producers, including the New York Times, CNET, iVillage, The Onion, and many others. The content appears in your Now Playing List, alongside regular broadcast programming as well as your Amazon Unbox rentals and purchases and even Home Movies. It's all seamlessly integrated into the entire TiVo experience.</p>

<p></p>

<p> * Home Movie Sharing - Instead of burning your home movies to DVD and mailing them to friends and family, now you can share them through a private TiVo channel of your own. Simply upload your video footage or photographs to One True Media (www.OneTrueMedia.com), get a channel code, and send the code out to your audience. Your home videos will show up right in the Now Playing list on their TiVo boxes, so they can enjoy them on their own TV. No need to huddle around a computer screen anymore, home movie sharing delivers those precious moments directly to the TV.</p>

<p></p>

<p> * Online Services -With your TiVo box connected to your broadband home network, you can access a variety of online services right on your TV, including Yahoo! Traffic and Weather, Fandango movie tickets, live radio, podcasts, games and more.</p>

<p></p>

<p> * TiVo KidZone - Only TiVo-branded DVRs give you total control over what your kids see on TV. With TiVo KidZone, you get to choose which shows your children can watch and record. It also helps you discover great new shows for them through recommendations from leading national children's organizations. TiVo KidZone provides a customized Now Playing List for your children that displays only the shows you pre-approve, keeping their shows separate from your own shows. TiVo KidZone relies on your own personal settings and password to ensure your kids only see what you want them to see, keeping TV as safe as possible.</p>

<p><br />
Pre-orders begin today with the first boxes being shipped in early August. See www.tivo.com for details on ship dates. A subscription to the TiVo service is required and sold separately. TiVo HD will be available starting early August at Best Buy, Circuit City and other retailers for $299.99.</p>

<p><br />
<B>About TiVo Inc.</B></p>

<p>Founded in 1997, TiVo (NASDAQ: TIVO) pioneered a brand new category of products with the development of the first commercially available digital video recorder (DVR). Sold through leading consumer electronic retailers, TiVo has developed a brand which resonates boldly with consumers as providing a superior television experience. Through agreements with leading satellite and cable providers, TiVo also integrates its full set of DVR service features into the set-top boxes of mass distributors. TiVo's DVR functionality and ease of use, with such features as Season Pass&trade; recordings and WishList&reg; searches and KidZone have elevated its popularity among consumers and have created a whole new way for viewers to watch television. With a continued investment in its patented technologies, TiVo is revolutionizing the way consumers watch and access home entertainment. Rapidly becoming the focal point of the digital living room, TiVo's DVR is at the center of experiencing new forms of content on the TV, such as broadband delivered video, music and photos. With innovative features, such as TiVoToGo&trade; and online scheduling, TiVo is expanding the notion of consumers experiencing "TiVo, TV your way.&reg;" The TiVo&reg; service is also at the forefront of providing innovative marketing solutions for the television industry, including a unique platform for advertisers and audience measurement research. The Company is based in Alviso, California.</p>

<p>TiVo, Season Pass, Swivel, TiVoToGo, WishList, the slogan 'TiVo, TV your way.', Series2, Series3, and the TiVo logo are trademarks of TiVo Inc. or its subsidiaries worldwide. © 2007 All rights reserved.</p>

<p>CableCARD&trade; is a trademark of the Cable Television Laboratories, Inc. All other trademarks are the property of their respective owners.</p>

<p><br />
<B>Contacts:</B></p>

<p>Krista Wierzbicki - 408.519.9438, kwierzbicki@tivocom  </p>

<p>Andrew Pray - 415.348.2732, praya@ruderfinn.com</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>July 24, 2007  9:56 AM</b>
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
			<?=getComments(643)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 643)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/07/the-new-tivo-hd-dvr-29999.php" type="text/javascript" charset="utf-8"></script>
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