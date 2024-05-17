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
		AND e.entry_id = 3267";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3267 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3267 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3267";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-390-cedia-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3267";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009</title>
	<meta name="keywords" content="pro cinema, digital entertainer, entertainer live, dolby volume, home theater, video, lcd, cinema, digital, home, technology, dolby, internet, pro, audio, hdmi, epson, content, volume, full, entertainer, available, live, media, show" />
	<meta name="description" content="CEDIA stands for &quot;Custom Electronics Design and Installation Association&quot;. Each year they have an expo where manufacturers show off their wares. This year the show is in Atlanta. While the HT Guys did not go we have friends who did attend. Listen to the show for some first hand reports from the event. In the mean time we run down some of the announcements that came out of the show.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=39&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-390-cedia-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="CEDIA stands for &quot;Custom Electronics Design and Installation Association&quot;. Each year they have an expo where manufacturers show off their wares. This year the show is in Atlanta. While the HT Guys did not go we have friends who did attend. Listen to the show for some first hand reports from the event. In the mean time we run down some of the announcements that came out of the show.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=39&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3267', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-390-cedia-2009.php">HDTV and Home Theater Podcast - Podcast #390: CEDIA 2009</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September 10, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<div class='snap_preview'><br /><h2>Today’s Show:</h2>
<h3>CEDIA 2009</h3>
<div>CEDIA stands for &#8220;<a title="Custom Electronics Design and Installation Association" href="http://www.cedia.net/" target="_blank">Custom Electronics Design and Installation Association</a>&#8220;. Each year they have an expo where manufacturers show off their wares. This year the show is in Atlanta. While the HT Guys did not go we have friends who did attend. Listen to the show for some first hand reports from the event. In the mean time we run down some of the announcements that came out of the show.</div>
<div>
<h4>Epson America</h4>
<p>Announced two native 1080p 3LCD(TM) home theater projectors designed for custom installers and home theater buffs, the PowerLite Pro Cinema 9100 and 9500 UB. These projectors feature the latest 3LCD chips with D7 technology for amazing color and detail, and significantly higher contrast ratios &#8211; the Pro Cinema 9100 achieves a 36,000:1 dynamic contrast ratio and the Pro Cinema 9500 UB attains an unprecedented 200,000:1 in its class (i). With professional-level color tools including ISF calibration and color isolation, the Pro Cinema 9100 and 9500 UB offer professional installers full-featured solutions.</p>
<p>Available for $2,599 and sub-$4,000 respectively, the Pro Cinema 9100 and 9500 UB offer state-of-the-art image quality and performance in each of their respective categories with enhanced color reproduction capabilities and 3LCD technology to deliver bright and natural color, crisp image detail and reliability. As Epson&#8217;s flagship home theater model, the Pro Cinema 9500 UB brings several technology enhancements to the market, including a new dual-layered auto-iris to control light reduction rates, Super-resolution(TM) technology for enhanced picture quality and improved FineFrame(TM) technology.</p>
<p>Available in October and November respectively, the Pro Cinema 9100 and 9500 UB can be purchased through authorized Epson projector dealers and select retail outlets. Both models come with Epson&#8217;s industry leading service and support, including a three-year limited warranty with toll-free access to Epson&#8217;s PrivateLine(SM) priority technical support, 90-day limited lamp warranty, and free two-business day exchange with Extra Care(SM) Home Service.</p>
<p>Epson also announced The Home Cinema 8100 is designed for home entertainment and AV enthusiasts with a budget in mind. With contrast ratio of up to 36,000:1 and brightness of 1,800 lumens, this projector delivers outstanding image quality in either a dark or lighted room – at the affordable price of $1,599.</p></div>
<h4>Gefen Uncompressed 1080p/60 Wireless HDMI</h4>
<div>Gefen is set to release its third wireless solution for transmitting HDMI video and audio across the room without cables. The GefenTV Wireless for HDMI 60Ghz Extender uses innovative WirelessHD technology.</p>
<p>This sender/receiver system extends HDMI up to 30 feet (10m) using a radio based on SiBeam technology. A line of sight placement is recommended but not mandatory. The sender is<br />
connected to the audio/video source, and supports all HDMI devices including gaming systems, Blu-ray players and set-top boxes. The receiver is connected to the extended display. Multichannel audio is delivered alongside 1080p full HD video in the HDMI format.</p>
<p>This uncompressed method of 1080p/60 full HD video transmission with multi-channel 5.1 surround sound audio makes it ideal for today’s most demanding systems, particularly gaming,<br />
due to its low latency.</p>
<h4>NETGEAR Introduces Digital Entertainer Live, Compact All-in-One Set-top Box for Playing Home Media and Streaming Internet Video on HDTVs</h4>
<p>The new Digital Entertainer Live (EVA2000) is an easy-to-use and affordable Internet set-top box that enables viewers to play their digital media collections, YouTube videos and a wide range of other Internet content on big-screen TVs. Rather than having to watch downloaded movies and online videos on small computer screens, families can now enjoy media collections stored on USB storage devices, computers and network storage directly on their HDTVs, from the comfort of their couch.</p>
<div>In addition to personal media collections and YouTube, consumers can now easily browse, download and play newly released pay-per-view movies from Roxio CinemaNow(TM). They can also view videos on their TV from a wide variety of Internet sources, such as Hulu, Netflix and CBS, through free software trials and optional subscriptions. The Digital Entertainer Live incorporates all of these functions into a single compact player, an advantage for cluttered home entertainment cabinets.</p>
<p>Backed by a one-year warranty and 24/7 technical support, the NETGEAR Digital Entertainer Live (EVA2000) is available in the U.S. through leading retailers, e-commerce sites and value-added resellers at an MSRP of $149.99. The Digital Entertainer Live Wireless USB Adapter (EVAW111) has an MSRP of $39.99. Worldwide availability of the Digital Entertainer Live is planned for the coming months.</p>
<h4>SHERWOOD INTRODUCES THE NETBOXX R-904N HIGH-PERFORMANCE INTERNET A/V RECEIVER</h4>
<ul>
<li>Connects to the Internet without a PC for video, audio and media streaming</li>
<li>Content available from You Tube, Cinema Now, on-line TV channels, Shoutcast &#8220;Internet Radio&#8221;, etc.</li>
<li>Supports PlayOn to stream Hulu, Netflix, CBS, CNN, ESPN, Amazon Video On Demand and more from a local network</li>
<li>Streams audio, video and photo media files from a local network direct to the receiver</li>
<li>High-performance audio/video in a new, highly compact form factor</li>
<li>HDMI 1.3, Dolby and DTS Lossless Audio for the highest quality sound</li>
<li>Dolby Volume eliminates volume fluctuations between channels, commercials and between Internet sites</li>
<li>High-efficiency &#8220;Green&#8221; Ti digital amplifiers</li>
</ul>
<div><em>Suggested retail is $649.95 and available in September 2009.</em></div>
<h4>SunBriteTV Introduces New 55-inch All-Weather Outdoor LCD TV</h4>
<p>Model 5500HD is a 55-inch full-HD 1080p LCD TV with 120 Hz refresh rate that is engineered for permanent outdoor installation. The corrosion-resistant, powder-coated aluminum exterior protects the internal TV components from rain, dirt, insects and extreme weather conditions.</p>
<p>With the company&#8217;s proprietary Hex-Fan Airflow System, Model 5500HD remains cool and safe in temperatures up to 122ºdegrees. In extremely cold climates, the internal thermostatically-controlled heater activates automatically when the TV&#8217;s internal temperature dips to 32º F, and safely remains outdoors in temperatures as low as -24º F. With variable fan-speed, controlled by the TVs internal thermostat, this system provides increased cooling as needed, therefore keeping fan noise to a minimum.</p>
<p>The 55-inch full-HD 1080p LCD screen displays a bright 1920 x 1080 pixel image with a 120 Hz refresh rate and delivers a 4000:1 contrast ratio. The anti-reflective, impact- and scratch-resistant window built into the exterior protects the LCD screen while reducing glare and improving picture quality</p>
<p>Model 5500HD includes two choices to simplify integration with control systems. RS232 serial is a full-featured command set with discrete on/off input select and volume control, and discrete IR control, provides codes for power on/off and input select.</p>
<p>Furthermore, the TV uses a fiber optic light path that makes it possible to install an IR emitter behind the water-tight cable door to conceal it from view. And, it is equipped with a water-resistant detachable speaker module that can to be removed when the TV is integrated with an external sound system.</p>
<p>The 5500HD will be available Oct. 1 with a MSRP of $6995</p>
<h4>Dolby Labs</h4>
<div>
<ul>
<li>Dolby Volume starting to roll out. 18 products will be announced at CEDIA with Dolby Volume from companies like:
<ul>
<li>Anthem, ARCAM, AudioControl, Bryston, Emotiva, Harman-Kardon, Integra, Onkyo, Sherwood, and Parasound</li>
<li>Toshiba will introduce TVs with Dolby Volume as well</li>
</ul>
</li>
<li>Dolby Pro Logic IIz &#8211; Technology is compatible with existing content. It works with content that has a spatial mix. You&#8217;ll get nothing out of dialog but scenes with outdoor elements will benefit. No need for a special mix. The processor does all the work. Of course content can be encoded with IIz in mind. Denon, Marantz, and Onkyo. Video games will really benefit from this technology.</li>
<li>Vudu &#8211; 2000 movies with Dolby Digital +. Dolby is really getting into online delivery of content.</li>
</ul>
</div>
<h4>Samsung Electronics America, Inc. announced availability for its latest CCFL-backlit LCD TV– the 65&#8243; LCD 650 Series (LN65B650) with 1080p resolution</h4>
<div>The latest entry in the LCD 650 Series TVs will deliver Auto Motion Plus™ 120Hz refresh rate, fast 4ms response time for motion clarity and high dynamic contrast ratios for deeper, more natural blacks. The LN65B650 will also include expansive networking and connectivity capabilities including Samsung&#8217;s Medi@2.0 Suite, the unique Touch of Color™ design, and it will meet the latest Energy Star® qualifications.</div>
<div>Estimated Selling Price: $5,999.99<br />
Availability:  September 2009</div>
</div>
</div>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-09-11.mp3">Download Episode #390</a></p>
  <a rel="nofollow" href="http://feeds.wordpress.com/1.0/gocomments/htguys.wordpress.com/39/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/comments/htguys.wordpress.com/39/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/godelicious/htguys.wordpress.com/39/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/delicious/htguys.wordpress.com/39/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/gostumble/htguys.wordpress.com/39/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/stumble/htguys.wordpress.com/39/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/godigg/htguys.wordpress.com/39/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/digg/htguys.wordpress.com/39/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/goreddit/htguys.wordpress.com/39/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/reddit/htguys.wordpress.com/39/" /></a> <img alt="" border="0" src="http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&blog=8935650&post=39&subd=htguys&ref=&feed=1" /></div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September 10, 2009 11:03 PM</b>
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
			<?=getComments(3267)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3267)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-390-cedia-2009.php" type="text/javascript" charset="utf-8"></script>
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