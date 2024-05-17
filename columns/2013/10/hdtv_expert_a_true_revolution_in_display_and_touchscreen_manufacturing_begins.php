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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, viglink_suppress, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 5153";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ken Werner" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];
	# I was going to use this and discovered that I could turn off insertion separately from affiliation
	#$viglink_suppress = ($row_aux['viglink_suppress'] == 1) ? 'nolinks' : '';

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5153 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ken Werner'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ken Werner" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5153 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5153";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/10/hdtv-expert-a-true-revolution-in-display-and-touchscreen-manufacturing-begins.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5153";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins" />
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
	<title>HDTV Magazine - HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins</title>
	<meta name="keywords" content="sheet resistance, silver nanowire, touch screen, transparent conductor, touch screens, ito, touch, sheet, clearohm, transparent, resistance, price, cambrios, silver, pet, indium, nanowire, conductor, screen, glass, display, displays, flexible, good, chart" />
	<meta name="description" content="On the morniing of October 3rd, three companies announced the formation of a joint venture that will place into volume production a transparent conductor that is much more flexible, more electrically conductive, more optically transparent, and less expensive than the material that has been the standard solution for decades, and still is. That standard solution [&amp;amp;#8230;]" />
	<meta name="title" content="HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/10/hdtv-expert-a-true-revolution-in-display-and-touchscreen-manufacturing-begins.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="On the morniing of October 3rd, three companies announced the formation of a joint venture that will place into volume production a transparent conductor that is much more flexible, more electrically conductive, more optically transparent, and less expensive than the material that has been the standard solution for decades, and still is. That standard solution [&amp;amp;#8230;]" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5153', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important nolinks"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare nolinks" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/10/hdtv-expert-a-true-revolution-in-display-and-touchscreen-manufacturing-begins.php">HDTV Expert - A True Revolution in Display and Touch-screen Manufacturing Begins</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ken Werner</b> on <b>October  9, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=304&category=General Interest">General Interest</a></b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p>On the morniing of October 3rd, three companies announced the formation of a joint venture that will place into volume production a transparent conductor that is much more flexible, more electrically conductive, more optically transparent, and less expensive than the material that has been the standard solution for decades, and still is.</p>
<p>That standard solution is indium tin oxide (ITO), and it is used in most of the touch screens and electronic displays manufactured today, from LCDs and OLEDs for cell phones to the giant LCDs in the largest TV sets, digital signs, and public information displays.</p>
<div id="attachment_3428" class="wp-caption alignright" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=3428" rel="attachment wp-att-3428"><img class="size-medium wp-image-3428" alt="Concept of what an early-generation flexible phone might look like.  (Graphic:  Cambrios)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/10/Flexphone-300x297.jpg" width="300" height="297" /></a><p class="wp-caption-text">Concept of what an early-generation flexible phone might look like. (Graphic: Cambrios)</p></div>
<p>In the U.S., Cambrios (Sunnyvale, California) announced the formation of TPK Film Solutions, Ltd. (TPKF), a joint venture with TPK, the world&#8217;s largest touch solution provider, and NISSHA, a leader in film-based touch sensors. TPKF&#8217;s mission is to &#8220;produce ClearOhm silver nanowire-based film in a roll-to-roll process allowing original equipment manufacturers (OEMs) to bring to market cuttng-edge touchscreens for new products and applications worldwide,&#8221; Cambrios announced in its press release. The joint venture agreement, which expands upon an existing agreement with TPK, was formerly signed on Oct. 3rd; volume production is anticipated beginning in Q2&#8217;14.</p>
<p>All of this may not sound too exciting until you understand not only that transparent conductors are essential components of most displays and touch screens, but also that ITO has significant limitations. As a result, the industry has wanted a viable replacement for ITO for some time; now, one is finally available.</p>
<p>So, what&#8217;s wrong with ITO? First, it&#8217;s relatively rare and is found in very low concentrations in various metal ores. As a result, it can be economically extracted only as a by-product of mining these higher-volume ores, primarily zinc sulfide. Rougly half of the world&#8217;s indium supply currently comes from China.</p>
<p>Although there isn&#8217;t an overall shortage of indium reserves relative to demand, the price has been cyclical, with cycles since 1985 typified by rapid increases and gradual decreases. The last price peak, in 2005, was a high one, topping out at about $1100 per kilogram (kg). The bottom of the price cycle in 2009 was almost as much as the previous two peaks. In January 2011, the price of pure indium was about $800/kg. (Data courtesy of the Polinares Consortium, a project of the European Union. The U.S. Geological Survey normally provides similar data, but the USGS Website is currently inoperative as a result of the U.S. government shutdown.)</p>
<p>The rising price trend is due to the increase in manufacturing of flat-panel displays, touch screens, and solar cells. The 2006 spike was due in part to a downturn in the production of zinc. Bottom line: the price and supply of indium are largely outside the control of the companies in the display and touch-screen industries who buy it, and even, to a significant degree, of the companies that sell it. Thus, when a leading supplier of indium responds to the current tight supply by saying on its Website, &#8220;The Indium Corporation believes higher prices will draw forward additional supplies which will alleviate any scarcity,&#8221; the thinking sounds wishful.</p>
<p>Price and supply issues have aggravated ulcers in the display industry from time to time, but that would not be enough to motivate a transition to alternatives if ITO were a really good transparent conductor. Although it has been good enough for the most part until recently, the evolution of displays and touch screens are making its shortcomings more and more troublesome. Although ITO does have good optical transparency, it is not a particularly good electrical conductor. The electrical resistance of thin-film conductors is expressed by its sheet resistance, which is measured in &#8220;ohms/square,&#8221; a metric that describes the material itself and is independent of the area of the film or its thickness.</p>
<div id="attachment_3429" class="wp-caption alignleft" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=3429" rel="attachment wp-att-3429"><img class="size-medium wp-image-3429" alt="Silver nanowire technology is capable of considerable lower sheet resistance at high optical transmissivity than is ITO.  (Chart:  Cambrios)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/10/xmission-vs-sheet-resistance-copy-300x184.jpg" width="300" height="184" /></a><p class="wp-caption-text">Silver nanowire technology is capable of considerable lower sheet resistance at high optical transmissivity than is ITO. (Chart: Cambrios)</p></div>
<p>Optical transmission of a transparent conductor varies with its sheet resistance, so it is useful to characterize these materials by plotting their transmission vs their sheet resistance. (See chart.) The ClearOhm silver nanowire material itself has, impressively, an optical transmission of between 99% and 100% at sheet resistances from 50 ohms/sq up, and ITO is only a couple of percentage points behind at over 150 ohms/square. Note, however, that it is difficult to make ITO with low sheet resistance and still maintain acceptable transmission. Whether its ClearOhm, ITO, or an alternative, the transparent conductor has to be applied to a substrate. That substrate has often been glass, but is increasingly likely to be a flexible polymer. The most popular of these is polyethylene terephthalate or PET. As you can see on the chart, a 125 micrometer sheet of PET absorbs more light than either the silver nanowire ink or the ITO that is applied to it, but the overall transmission is still over 90%, and ClearOhm maintains that down to 25 ohms/square.</p>
<p>If you make a touch screen using a transparent conductor on a thin sheet of PET, it is thinner, lighter, and more rugged than a similar touch screen made on glass, so there is an advantage to using a PET touch screen even if it is applied to a glass display that is not designed to bend, and this is especially true for a portable device such as a cell phone or tablet. Another advantage is that PET is more amenable to inexpensive roll-to-roll processing than is glass. (Please note, however, that Corning and Asahi Glass now offer display glass that is so thin that it can be rolled.)</p>
<div id="attachment_3430" class="wp-caption alignright" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=3430" rel="attachment wp-att-3430"><img class="size-medium wp-image-3430 " alt="flexibility" src="http://www.hdtvexpert.com/wp-content/uploads/2013/10/flexibility-300x173.jpg" width="300" height="173" /></a><p class="wp-caption-text">The message here is simple: ITO is brittle; silver nanowires are flexible. (Chart: Cambrios)</p></div>
<p>But, ultimately, if you are making a touch screen on a flexible substrate, you would like to use its flexibility as well as its thinness and light weight. Here, brittle ITO falls flat. If you apply ITO to a PET substrate and wrap the PET around a rather small-diameter cylinder, cracks will appear in the ITO after only one wrap/unwrap cycle and the sheet resistance will rapidly increase by a factor of 1000 or more. The sheet resistance of silver nanowire ink, on the other hand increases only very slightly over 50 cycles. (See chart.)  ITO can survive a significantly more gentle bend, particularly in a &#8220;bend-once&#8221; application.</p>
<p>So, silver nanowire technology produces transparent conductors with lower sheet resistance, higher optical transmissivity, and much greater flexibility than ITO, but, in addition, it does so at lower cost. This may sound too good to be true, but it isn&#8217;t. Jason Heikenfeld, Director of the Novel Devices Laboratory and an Associate Professor in the School of Electronics and Computing Systems at the University of Cincinnati recently told me in a personal (but not secret) communication: &#8220;We have used ClearOhm here and validated it. It is amazing in performance and can be patterned quite well. Clear-Ohm already costs less than ITO and beats the competition in performance. It is a great product.&#8221;</p>
<p>TPK Chairman Michael Chiang explained his company&#8217;s investment in the TPKF joint venture by saying, “Silver nanowires are a major part of our strategy to address the mid- and low-end segments of the mobile devices market. We will deliver high-volume manufacturing capacity for ClearOhm films for incorporation into leading consumer electronic devices including mobile phones, tablets and large-area touchscreen applications.”</p>
<p>On October 1st, Cambrios announced a significant design win. Lenovo will use ClearOhm in its new 20-inch-class All-in-One computer, the Lenovo Flex 20. &#8220;When seeking a technology to support our Flex 20 All-in-One computer touchscreen, we actively looked for a solution that not only delivered a high-performance experience, but also lowered the price point for our end-users. Cambrios’ ClearOhm transparent conductors answer this challenge and collectively, we delivered a best-in-class product,” said Sam Dusi, Director of Worldwide Strategic Alliances for Lenovo in the press release announcing the design win.</p>
<p>Cambrios has been is semi-stealth mode for most of its ten-year history. That time is clearly over and, with it, the unquestioned hegemony of ITO for transparent conductors.  That hegemony will be further eroded by other competing technologies, but, for now, only ClearOhm is commercially available in significant volumes.</p>
<p>Expect a series of new ClearOhm design wins to be announced over the next few months, including at least one very, very big one.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ken Werner</b>, <b>October  9, 2013  2:45 PM</b>
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
			<?=getComments(5153)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Ken Werner', 5153)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ken Werner</h2>
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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/10/hdtv-expert-a-true-revolution-in-display-and-touchscreen-manufacturing-begins.php" type="text/javascript" charset="utf-8"></script>
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