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
		AND e.entry_id = 1399";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1399 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1399 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1399";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2008/05/sony-kdl46xbr4-46-inch-lcd-hdtv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1399";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony KDL-46XBR4 46 inch LCD HDTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony KDL-46XBR4 46 inch LCD HDTV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony KDL-46XBR4 46 inch LCD HDTV" />
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
	<title>HDTV Magazine - Sony KDL-46XBR4 46 inch LCD HDTV</title>
	<meta name="keywords" content="blu ray, kdl xbr, cox cable, sony kdl, our friend, picture, looked, settings, our, blu, ray, good, color, sony, enhancer, better, cox, calibration, friend, see, cinema, cable, bit, ota, improved" />
	<meta name="description" content="The TV looks visually appealing and has a high build quality. There is a clear plastic frame that goes around the TV. The TV weighs 84 pounds (38 Kg) with the pedestal and measures 49.7&quot; (126 cm) wide by 31.3&quot; (79.5 cm) high by 17.7&quot; (32 cm) deep (4.8&quot; (12 cm) without the pedestal). We immediately took the TV off the default setting and changed it to cinema. Cox cable looked very bad. So bad that we recommended switching to satellite. Braden has Cox as one of his sources so his impression was that Cox does not look that bad on his TVs. It could be that this TV does not do well with compressed sources. OTA HD looked much better. In general OTA digital channels looked better than the SD coming from the Cox cable box.

We then popped in a Blu-ray disc..." />
	<meta name="title" content="Sony KDL-46XBR4 46 inch LCD HDTV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony KDL-46XBR4 46 inch LCD HDTV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2008/05/sony-kdl46xbr4-46-inch-lcd-hdtv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The TV looks visually appealing and has a high build quality. There is a clear plastic frame that goes around the TV. The TV weighs 84 pounds (38 Kg) with the pedestal and measures 49.7&quot; (126 cm) wide by 31.3&quot; (79.5 cm) high by 17.7&quot; (32 cm) deep (4.8&quot; (12 cm) without the pedestal). We immediately took the TV off the default setting and changed it to cinema. Cox cable looked very bad. So bad that we recommended switching to satellite. Braden has Cox as one of his sources so his impression was that Cox does not look that bad on his TVs. It could be that this TV does not do well with compressed sources. OTA HD looked much better. In general OTA digital channels looked better than the SD coming from the Cox cable box.

We then popped in a Blu-ray disc..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1399', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2008/05/sony-kdl46xbr4-46-inch-lcd-hdtv.php">Sony KDL-46XBR4 46 inch LCD HDTV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>May 20, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=277&category=HDTV Displays">HDTV Displays</a></b>
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
				<p><a href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&amp;storeId=10151&amp;langId=-1&amp;productId=8198552921665116636" target="_blank"><img style="margin: 0px 0px 5px 5px" height="160" alt="Sony KDL-46XBR4" src="http://www.hdtvmagazine.com/images/mt/SonyKDL46XBR446inchLCDHDTV_12FF5/image.png" width="160" border="0"></a>&nbsp; <table class="greygrid"> <tbody> <tr> <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td></tr> <tr> <td class="greygrid">&nbsp;</td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Street</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>Sony KDL-46XBR4</b></td> <td class="greygrid"><a href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&amp;storeId=10151&amp;langId=-1&amp;productId=8198552921665116636" target="_blank">$3,299.99</a></td> <td class="greygrid"><a href="/equipment/model.php?man=Sony&amp;model=KDL46XBR4" target="_blank">$2,455.00</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000UN8MKM?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000UN8MKM" target="_blank">$2,799.99</a></td></tr></tbody></table> <p>Warranty: 1 Year Parts / 1 Year Labor </p> <p><b>Summary: A nice TV when a good quality HD signal is present, but a bit on the pricey side.</b></p> <p>As you would imagine, being a close friend of the HT Guys comes with some benefits. We have a friend that just bought the Sony KDL-46XBR4 46 inch LCD and a Sony Blu-ray player. He asked for some help setting the TV up and of course we said yes. Well, we said yes because it provides good material for our show. Our friend uses Cox cable, OTA and Blu-ray for his HD material.<a name="r-0p0"></a>  <p><a name="axgx0"></a><a name="qg%3Ax2"></a> <h2>Features</h2> <ul> <li>1080p  <li>10-bit Processing and 10-bit Display  <li>Motionflow&trade; 120 Hz with Full HD high frame rate capability  <li>Deep Color Support  <li>24p True Cinema (24p Input Capability)  <li>DMeX - Ready (Digital Media Extender) - <a name="agei0"></a><a name="sota0"></a><i>Sony's Digital Media Extender (DMeX) ready televisions offer a digital connection path for the addition of the optional modules like the new BRAVIA Internet Video Link. 6 With innovative DMeX expansion capabilities featuring the Emmy<sup>&reg;</sup> award winning XMB user interface, these models are not merely TVs, but powerful entertainment platforms that not only meet your needs today, but extend to add new features seamlessly.</i></li></ul> <h2>Impression</h2> <p>The TV looks visually appealing and has a high build quality. There is a clear plastic frame that goes around the TV. The TV weighs 84 pounds (38 Kg) with the pedestal and measures 49.7" (126 cm) wide by 31.3" (79.5 cm) high by 17.7" (32 cm) deep (4.8" (12 cm) without the pedestal). We immediately took the TV off the default setting and changed it to cinema. Cox cable looked very bad. So bad that we recommended switching to satellite. Braden has Cox as one of his sources so his impression was that Cox does not look that bad on his TVs. It could be that this TV does not do well with compressed sources. OTA HD looked much better. In general OTA digital channels looked better than the SD coming from the Cox cable box.<a name="hdvk0"></a><br><a name="hdvk1"></a><br>We then popped in a Blu-ray disc (Fantastic Four Rise of the Silver Surfer) and were quite impressed with the picture. We saw great detail in the picture and found that the skin tones looked very natural. Color representation was highly accurate. The TV has very good black levels for an LCD. We found that computer generated scenes looked fake for some reason. The skiing scene looked very good. There was a lot of contrast between the white snow and the blue sky. The TV has a Contrast Ratio of 2,000:1. We liked the off angle viewing of this TV. While not on par with Plasmas, it's much better than most LCDs we've seen.<a name="w1ch0"></a><br><a name="zfwt0"></a><br>Next we started playing around with the settings to dial the TV in. Our friend was happy with the pre-configured cinema settings and was getting antsy about us spending so much time with his TV. In the end, our quick calibration made the picture even better. Our calibration settings are included at the end of this review, but please use them as a starting point only. We ended up turning the all the special processing off, but you can play around with those to see if you like what you see. With HD film based material, we felt the Motion Enhancer took away from the viewing experience. With SD material it actually improved the picture. This is very subjective so if you plan on buying this TV or you already own one, experiment with these settings.<a name="w.hi0"></a><br><a name="w.hi1"></a><br>Once we had it dialed in, we went back and watched the Fantastic Four Blu-ray disc. It was clear to all that we had indeed improved the picture. Our friend was happy we came by. It was everything we said before about the clarity, color, and detail but improved. We also watched Standard Definition DVDs (Spider Man 3, 27 Dresses, and Enchanted) after calibration and were pleasantly surprised at how good the picture looked. The motion enhancer actually improved the picture. Our recommendation is to screen parts of the movie with the motion enhancer on and then turn it off, and see which one you prefer. You'll soon figure out what types of movies this will help and what types it won't help. Unfortunately we do not have a clear cut answer for its use.  <h2>Conclusion<a name="r3y30"></a></h2> <p>Overall, the Sony makes for a nice TV when a good quality HD signal is present. If you have an overly compressed signal via OTA or Cable you won't be happy with what you see. To really show off this set, you should also consider buying a Blu-ray player. Please do not leave the set in its default settings. At a minimum switch it to Cinema mode. Better yet calibrate it with any of the calibration DVDs or have a professional do it for you. This TV will really show its stuff when set up properly. The only negatives we can find are that its optical output does not do 5.1 for anything other than its ATSC tuner (but this is the case with almost all TVs), and that it's a bit on the pricey side.  <h2>Calibration Settings (Use only as a starting point)<a name="hatw1"></a></h2> <p>HDMI Settings (Blu-ray)<a name="ujn_0"></a><br><a name="ujn_1"></a><br>Picture Mode: Custom<a name="ujn_2"></a><br>Backlight: 3<a name="ujn_3"></a><br>Picture: 66<a name="ujn_4"></a><br>Brightness: 45<a name="ujn_5"></a><br>Color: 57<a name="ujn_6"></a><br>Hue: 0<a name="ujn_7"></a><br>Color Temperature: Neutral<a name="ujn_8"></a><br>Sharpness: 38<a name="ujn_9"></a><br>Noise Reduction: Off<a name="ujn_10"></a><br><a name="ujn_11"></a><br>Advanced Settings<a name="ujn_12"></a><br>White Balance:<a name="ujn_13"></a><br>R-Gain: 0<a name="ujn_14"></a><br>G-Gain: -5<a name="ujn_15"></a><br>B-Gain: -5<a name="ujn_16"></a><br>R-Bias: 0<a name="ujn_17"></a><br>G-Bias: -5<a name="ujn_18"></a><br>B-Bias: 0<a name="ujn_19"></a><br><a name="wtiu0"></a><br>Color Space: Standard<a name="ujn_20"></a><br>Detail Enhancer: off<a name="wtiu1"></a><br>Edge Enhancer: off<a name="ujn_21"></a><br>Everything else set to off<a name="ujn_22"></a><br>Motion Enhancer: off<a name="kbgg3"></a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>May 20, 2008  9:23 AM</b>
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
			<?=getComments(1399)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1399)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/05/sony-kdl46xbr4-46-inch-lcd-hdtv.php" type="text/javascript" charset="utf-8"></script>
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