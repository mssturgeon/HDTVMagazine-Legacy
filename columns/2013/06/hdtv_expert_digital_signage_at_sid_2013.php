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
		AND e.entry_id = 5114";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5114 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5114 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5114";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/06/hdtv-expert-digital-signage-at-sid-2013.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5114";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Digital Signage at SID 2013" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Digital Signage at SID 2013" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Digital Signage at SID 2013" />
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
	<title>HDTV Magazine - HDTV Expert - Digital Signage at SID 2013</title>
	<meta name="keywords" content="digital signage, ken werner, photo ken, display technology, concave sign, display, inch, displays, sid, signage, digital, resized, sign, lcd, technology, oled, showing, ink, werner, plasma, photo, tubes, ken, tannas, black" />
	<meta name="description" content="Manufacturers of large display panels don&amp;amp;#8217;t make much &amp;amp;#8212; or any &amp;amp;#8212; money on panels for television, so the rapidly growing market for digital signage is a bright spot.
SID Display Week &amp;amp;#8212; held May 19-24, 2013 in Vancouver, B.C. &amp;amp;#8212; is a display technology show that is not known for a focus on digital signage, [...]" />
	<meta name="title" content="HDTV Expert - Digital Signage at SID 2013" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Digital Signage at SID 2013" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/06/hdtv-expert-digital-signage-at-sid-2013.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Manufacturers of large display panels don&amp;amp;#8217;t make much &amp;amp;#8212; or any &amp;amp;#8212; money on panels for television, so the rapidly growing market for digital signage is a bright spot.
SID Display Week &amp;amp;#8212; held May 19-24, 2013 in Vancouver, B.C. &amp;amp;#8212; is a display technology show that is not known for a focus on digital signage, [...]" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5114', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/06/hdtv-expert-digital-signage-at-sid-2013.php">HDTV Expert - Digital Signage at SID 2013</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ken Werner</b> on <b>June  2, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=333&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>, <b><a href="/category.php?id=448&category=PC & Laptop Technology">PC & Laptop Technology</a></b>
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
				<p><span style="font-size: 13px; line-height: 19px;">Manufacturers of large display panels don&#8217;t make much &#8212; or any &#8212; money on panels for television, so the rapidly growing market for digital signage is a bright spot.</span></p>
<p>SID Display Week &#8212; held May 19-24, 2013 in Vancouver, B.C. &#8212; is a display technology show that is not known for a focus on digital signage, so when I decided to look for digital signs there, I did not expect it to be an overwhelming task.  Still, the signage industry has the energy and resources to fund some interesting variations of display technology and some of those could be seen at SID.  And, I thought, the relatively modest scope of signage at SID might permit a more leisurely and philosophical view of the relationship between developments in display technology and advances in digital signage.  Let&#8217;s see how that worked out.</p>
<div id="attachment_3207" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3207" rel="attachment wp-att-3207"><img class="size-full wp-image-3207" alt="Sharp's 13.5-inch, 4Kx2K OLED profesional monitor.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xSharp_13.5_4K_1.jpg" width="600" height="344" /></a><p class="wp-caption-text">Sharp&#8217;s 13.5-inch, 4Kx2K OLED profesional monitor. (Photo: Ken Werner)</p></div>
<p>Sharp impressed everybody at SID 2012 with its 31.5-inch QFHD professional LCD monitor with IGZO backplane. This year, the company showed a 13.5-inch QFHD OLED display, in addition to the 31.5-inch LCD. There is no model number on the OLED, indicating it is a technology demonstrator and not a product. In fact, Sharp doesn&#8217;t say much about the place of OLED displays in its future, but the company keeps showing OLED technology demonstrations. Sharp did make note of its 60-, 70, and 80-inch class e-signage LCD modules, but did not emphasize them for SID. At DSE in Las Vegas in late February, though, Sharp was definitely emphasizing the big stuff up to 90 inches diagonal. One message Sharp displayed on a 90-inch at DSE was &#8220;Go Big or Go Home.&#8221;</p>
<p>QD Vision showed its Color IQ quantum-dot element &#8212; which won the Display Component of the Year Gold Award &#8212; in a commercially available Triluminous Sharp Bravia TV set. (For more information on quantum dots and LCDs, see http://www.hdtvexpert.com/?p=2741.) Clearly, the enlarged color gamut produced by quantum-dot enhancement would add substantially to the impact of digital signage. So would large-screen OLED, but large-screen OLED is a lot more expensive.</p>
<div id="attachment_3209" class="wp-caption alignleft" style="width: 174px"><a href="http://www.hdtvexpert.com/?attachment_id=3209" rel="attachment wp-att-3209"><img class="size-full wp-image-3209" alt="BSI's resized and convex 20-inch digital sign.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xBSI_convex.jpg" width="164" height="320" /></a><p class="wp-caption-text">BSI&#8217;s resized and convex 20-inch digital sign. (Photo: Ken Werner)</p></div>
<p>BiSearch International (BSI) showed resized, convex signs at DSE, and added a concave sign for SID. The resized convex signs were originally designed as game-toppers for casinos, but have been enthusiastically received by beverage companies. At SID, the concave sign was shown in a game application. The signs are resized under the Tannas patents. Except for the resizing, the curved signs are absolutely standard LCDs. The 20-inch convex sign was resized and curved by Tovis Co., Ltd (Incheon, Korea). Tovis warms the LCD gently before bending. I assume the concave sign was also fabricated by Tovis, but I don&#8217;t know that for sure. Bi-Search also showed a transparent 47-inch LCD sign. Such displays are no longer novelties, but BSI&#8217;s colors appeared quite saturated and true, and the clear parts of the display appeared quite transparent. That is probably the result of a very brightly lit box behind the display since saturated colors and transparency are conflicting qualities for transparent LCDs.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div id="attachment_3210" class="wp-caption aligncenter" style="width: 546px"><a href="http://www.hdtvexpert.com/?attachment_id=3210" rel="attachment wp-att-3210"><img class="wp-image-3210 " alt="BSI's concave sign was new for SID.  The 40-inch panel was resized to 1880x1056.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xBSI_concave.jpg" width="536" height="540" /></a><p class="wp-caption-text">BSI&#8217;s concave sign was new for SID. The 40-inch panel was resized to 1880&#215;1056. (Photo: Ken Werner)</p></div>
<p>&nbsp;</p>
<div id="attachment_3212" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3212" rel="attachment wp-att-3212"><img class="size-full wp-image-3212" alt="BSI's transparent 47-inch sign was an admirable example of the genre, with saturated colors and apparently high transparency.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xBSI_47_xparent.jpg" width="600" height="402" /></a><p class="wp-caption-text">BSI&#8217;s transparent 47-inch sign was an admirable example of the genre, with saturated colors and apparently high transparency. (Photo: Ken Werner)</p></div>
<p>&nbsp;</p>
<p>E Ink introduced its Spectra display, which adds red particles to the traditional black and white ones, and won the Display Week Best in Show Award. The technology was under development at SiPix before it was bought by E Ink, so the display uses the SiPix Microcup structure instead of the E Ink Microcapsule structure. At SID, E Ink had a poster showing the how the red, white, and black particles are arranged inside the Microcup for each of the three basic states (showing red, black, or white at a particular pixel site). What was not at all clear was how the display&#8217;s addressing mechanism could distinguish between the negatively charged black particles and the negatively charged red particles. Product Management Director Giovanni Mancini refused to tell me, and seemed to enjoy being secretive. But he did say that red and black are distinguished through a characteristic that is &#8220;something other than charge.&#8221;</p>
<p>E Ink was showing Spectra-based modules made by Pervasive Displays in sizes ranging from 1.44&#8243; (for small shelf-edge labels) to 7.2-inches.  Pervasive was also showing tiled black-and-white active-matrix E Ink Displays with Mpico timing controller.  Each of the six tiles in the demo had 800&#215;480 pixels  Can three colors be far behind?  Also in the E Ink booth were large, passively addressed, segmented, alphanumeric signs that exhibited the high contrast ratio we have come to expect from this technology.</p>
<div id="attachment_3213" class="wp-caption alignright" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=3213" rel="attachment wp-att-3213"><img class="size-medium wp-image-3213" alt="A 7.4-inch E Ink Spectra panel, which incorporates red particles, as well as white and black ones.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/x7.4-in_Spectra-300x228.jpg" width="300" height="228" /></a><p class="wp-caption-text">A 7.4-inch E Ink Spectra panel, which incorporates red particles, as well as white and black ones. (Photo: Ken Werner)</p></div>
<p>Fraunhofer&#8217;s Heinrich Hertz Institute (HHI) was showing a large autostereoscopic display with &#8220;viewpoint adaptation.&#8221; Although AS-3D displays often have several viewing zones for different angles of view, they are all best viewed from a specific distance predetermined by the display&#8217;s design. HHI uses an AFX plug-in suite to generate multiple sets of views from the standard stereoscopic inputs. These multiview images are compatible with many existing AS-3D displays, said HHI Research Associate Bernd Duckstein.</p>
<p>For the demo at SID, there were three fixed viewing distances that Duckstein selected manually, but it is possible to automate the process by detecting the viewer&#8217;s distance from the display. HHI did not focus on signage in their demonstration, but signage is an obvious application.</p>
<p>LiteMax (New Taipei City, Taiwan and Fremont, California) was showing a variety of displays in its SpanPixel line, which are based on resized LCDs. The resized units on display included a 38-inch-diagonal, 1920&#215;502 (16:4.2) with 2000 nits luminance and local area dimming; a 49.5-inch, 1920&#215;538 (16:4.5); an 8-inch (16:5); and a 10.5-inch.</p>
<div id="attachment_3214" class="wp-caption alignleft" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=3214" rel="attachment wp-att-3214"><img class="size-medium wp-image-3214" alt="LiteMax Spanpixel digital sign based on resized LCD panel with final size of 49.5 inches and 1920x538 pixels.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xLIteMax1920x538-300x100.jpg" width="300" height="100" /></a><p class="wp-caption-text">LiteMax Spanpixel digital sign based on resized LCD panel with final size of 49.5 inches and 1920&#215;538 pixels. (Photo: Ken Werner)</p></div>
<p>Prominently located on the display counter was the framed license certificate from Tannas Electronic Displays (TED) naming LiteMax the exclusive Taiwan licensee for manufacturing re-sized LCDs. And that brings us to Tannas Electronic Displays itself, which was showing what had been a Coby 40-inch TV set just two weeks before the SID show. At SID, the functioning resized display sat in the orignal Coby bezel, clearly indicating how much of the original display had been removed. Tannas also announced that GSD (Gumi-city, Korea) had just become TED&#8217;s ninth new licensee in the last 12 months for digital signage and commercial displays. Company president Larry Tannas said there were more licensees to come.</p>
<p>In the E-Ink-sponsored Innovation Zone, Shinoda Plalsma (SHIPLA) was showing a new version of its tube-based plasma display. Previous versions had the thin plasma-filmed tubes running vertically, so the display was vertically flat but could be bent into convex and concave curves horizontally. In this new version, the tubes ran horizontally so the display could be rolled up somewhat like a window shade.</p>
<p><a href="http://www.hdtvexpert.com/?attachment_id=3216" rel="attachment wp-att-3216"><img class="aligncenter size-full wp-image-3216" alt="xShinoda_1" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xShinoda_1.jpg" width="600" height="373" /></a>A little history. Tsutae Shinoda began his work on plasma displays at Fujitsu Laboratories in the early 1970s. He is responsible for many of the essential development in plasma display panels; developed the first practical VGA color panel in 1992, which measured 21 inches; and developed the first 42-inch color PDP in 1995. Very early in the development of plasma displays it was realized that the displays could be based on glass-plate sandwiches, glass spheres, or glass tubes. Obviously, glass plates won, but glass-plate PDPs are relatively heavy.</p>
<div id="attachment_3217" class="wp-caption alignright" style="width: 310px"><a href="http://www.hdtvexpert.com/?attachment_id=3217" rel="attachment wp-att-3217"><img class="size-medium wp-image-3217" alt="Close-up of the SHIPLA plasma-tube display.  The plasma tubes run horizontally.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xShinoda_1_01-300x195.jpg" width="300" height="195" /></a><p class="wp-caption-text">Close-up of the SHIPLA plasma-tube display. The plasma tubes run horizontally. (Photo: Ken Werner)</p></div>
<p>Over the last ten years or so, Shinoda has been working on PDPs in which both the plasma and the phosphors are contained in thin glass tubes. Using this technology, he has build flexible plasma displays that are very light in weight &#8212; light enough to be easily hung as banners. The downside is that the tubes are 1mm in diameter, which results in a display resolution that is too coarse for close-up viewing. However, Shinoda said at SID, he is working on tubes of 0.5mm diameter, which will multiply the number of pixels per unit area by a factor of 4.</p>
<p>A limited number of SHIPLA flexible panel displays have been installed in Asia, but horizontal tubes with half the diameter could allow the company to find a significantly larger digital-signage market.</p>
<p>Finally, two not-quite-new technologies that are being promoted for television actually have a more logical application to digital signage.</p>
<p>First, although Samsung was emphasizing small OLED displays in its booth and in its CEO&#8217;s keynote address , the company did show its dazzling $40,000, 85-inch, 4Kx2K, LCD-TV. This set &#8212; which uses a full-matrix backlight, local-area dimming, and (seemingly) every video-processing trick known to man &#8212; is the most beautiful LCD-TV I have ever seen. It is startling, it is magnetic, and &#8212; at a price that is much the same as a small BMW &#8212; it is a sign.</p>
<div id="attachment_3218" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3218" rel="attachment wp-att-3218"><img class="size-full wp-image-3218" alt="Samsung's 85-inch, 4Kx2K LCD-TV is the most impressive LCD the author has ever seen.  At an initial MSRP of $40,000, it should be.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/06/xSamsung_4K_1.jpg" width="600" height="362" /></a><p class="wp-caption-text">Samsung&#8217;s 85-inch, 4Kx2K LCD-TV is the most impressive LCD the author has ever seen. At an initial MSRP of $40,000, it should be. (Photo: Ken Werner)</p></div>
<p>Second was LG&#8217;s curved 55-inch OLED, which was showing 3D content at LGD&#8217;s SID booth. With very limited availability, a price north of $10,000, and the benefit of a curved screen for TVs in this size range being very much in doubt, this display is more a promotional device than a serious home-entertainment product. But novelty is the soul of advertising, and LG&#8217;s curved OLED is certainly an attention-grabber.  (Actually, repetition is the soul of advertising, but perhaps you will allow me some poetic license.)</p>
<p>In conclusion, I stand corrected. When all was said and done, there was quite a bit of signage-related technology at SID 2013 &#8212; if you were looking for it.</p>
<p>[Disclosure: Tannas Electronics Displays in one of the author's clients. LiteMax is a licensee of Tannas Electronic Displays. BSI purchases resized panels and displays from Tannas licensees.]</p>
<p><em> Ken Werner is Principal of Nutmeg Consultants, specializing in the display industry, display manufacturing, display technology, and display applications. You can reach him at kwerner@nutmegconsultants.com.</em></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ken Werner</b>, <b>June  2, 2013  4:58 PM</b>
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
			<?=getComments(5114)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Ken Werner', 5114)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/06/hdtv-expert-digital-signage-at-sid-2013.php" type="text/javascript" charset="utf-8"></script>
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