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
		AND e.entry_id = 5004";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5004 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5004 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5004";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2013/01/oled-tv-demystified.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5004";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download OLED TV Demystified" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="OLED TV Demystified" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="OLED TV Demystified" />
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
	<title>HDTV Magazine - OLED TV Demystified</title>
	<meta name="keywords" content="sub pixels, oled lighting, white light, color filters, sub pixel, oled, light, sub, color, white, pixels, pixel, blue, lighting, colors, technology, organic, display, displays, filters, oleds, appendix, panels, although, used" />
	<meta name="description" content="As you may already know, small OLED (Organic Light Emitting Diode) panels have been introduced to consumers in several forms for several years, and at CES 2012 LG and Samsung showed their 55-inches 1080p OLED HDTV prototype panels, both displaying stunning images and with prices said to be around $10,000 USD when available toward the second part of 2012, however, is mid December 2012 and the OLED panels have not yet appeared at local stores, although they are expected soon in 2013.

As I covered in this article, LG’s WOLED HDTV uses a white OLED design and implements passive 3D technology displaying half resolution images per eye both eyes viewing simultaneously using low cost 3D glasses, while Samsung’s Super OLED HDTV uses a more classic RGB design and implements active-shutter 3D technology that renders full resolution images per eye displayed in alternate fashion. 

If you are interested to know more about the OLED technology..." />
	<meta name="title" content="OLED TV Demystified" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="OLED TV Demystified" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2013/01/oled-tv-demystified.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As you may already know, small OLED (Organic Light Emitting Diode) panels have been introduced to consumers in several forms for several years, and at CES 2012 LG and Samsung showed their 55-inches 1080p OLED HDTV prototype panels, both displaying stunning images and with prices said to be around $10,000 USD when available toward the second part of 2012, however, is mid December 2012 and the OLED panels have not yet appeared at local stores, although they are expected soon in 2013.

As I covered in this article, LG’s WOLED HDTV uses a white OLED design and implements passive 3D technology displaying half resolution images per eye both eyes viewing simultaneously using low cost 3D glasses, while Samsung’s Super OLED HDTV uses a more classic RGB design and implements active-shutter 3D technology that renders full resolution images per eye displayed in alternate fashion. 

If you are interested to know more about the OLED technology..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5004', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2013/01/oled-tv-demystified.php">OLED TV Demystified</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 16, 2013</b>
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
				<p>As you may already know, small OLED (Organic Light Emitting Diode) panels have been introduced to consumers in several forms for several years, and at CES 2012 LG and Samsung showed their 55-inches 1080p OLED HDTV prototype panels, both displaying stunning images and with prices said to be around $10,000 USD when available<a target="_blank" href="https://www.e-junkie.com/ecom/gb.php?cl=170595&c=ib&aff=237814"><img alt="The OLED Handbook" style="float:right" src="http://www.hdtvmagazine.us/articles/images/5c86ed3503ee_132DD/clip_image002_5cb5ae7e-6b40-4ec3-80ae-012255ee750f.jpg" width="340" height="400"></a> toward the second part of 2012, however, the OLED panels have not yet appeared at local stores, although they are expected soon in 2013.  At least LG announced at CES 2013 panel availability for the first quarter in the US, at a higher price: $12,000. <p>As I covered in <a target="_blank" href="http://www.hdtvmagazine.com/articles/2012/04/who-has-a-better-oled.php">this article</a>, LG’s WOLED HDTV uses a white OLED design and implements passive 3D technology displaying half resolution images per eye both eyes viewing simultaneously using low cost 3D glasses, while Samsung’s Super OLED HDTV uses a more classic RGB design and implements active-shutter 3D technology that renders full resolution images per eye displayed in alternate fashion.  <p>If you are interested to know more about the OLED technology, a colleague publication has released <a target="_blank" href="https://www.e-junkie.com/ecom/gb.php?cl=170595&c=ib&aff=237814">The OLED Handbook - A Guide to OLED Technology, Industry and Market</a>.</b> <p>As you may be aware, OLEDs of various colors have been developed with sufficient efficiency using fluorescent and phosphorescent materials for the commercialization of not only OLED TV panels, but also to generate white light for general lighting, back light for LED displays, and other display applications. <p>In order to create white light the organic materials of primary colors (red, green, and blue, or alternatively blue and yellow) have to be excited simultaneously. Incorrect excitation of more than one organic species may cause color stability problems. <p>Uneven longevity between organic materials may also produce unintended colors as a panel ages, and phosphorescent blue has been known to have a shorter lifespan compared to fluorescent blue and compared to the other phosphorescent colors, although the blue’s lifespan is now much longer than a few years back. Apparently LG has used a fluorescent material for the blue and a phosphorescent material for the red and green, and if so the life span of all may have been equaled but I have no confirmation yet from LG of that, I asked LG Display in Korea last week at CES to provide me their official response. <p>LG’s OLED implements WOLED with a set 4 sub-pixels, of which 3 sub-pixels are used to create colors with color filters and 1 extra sub-pixel is used to produce white light without using a filter, the set of 4 sub-pixels conforms a single pixel of image on the screen. <p>Although not publicly confirmed by LG, all the 4 sub-pixels appear to be made of a stack of organic materials of either a) blue/yellow colors, or b) the three RGB colors. The stack of organic color materials is used to create white light by each of the 4 sub-pixels, rather than to directly create the color a sub-pixel may need to display. <p><img alt="LG 55-inch WOLED" style="float:left" src="http://www.hdtvmagazine.us/articles/images/5c86ed3503ee_132DD/clip_image004_ce94fe00-9edb-46cf-b0d7-5899e3d796e4.jpg" width="361" height="255">When a pixel in the screen needs to display a particular color the white light of each of its sub-pixels is passed thru the color filters located in front of the sub-pixels, and that produces the intended color. <p>This is an approach that is rather similar to LCD technology, whereby light is coming from the back of the panel and color is created by the filters at front (while the liquid crystals regulate the light passing thru). <p><img alt="Samsung's 55-inch Super OLED" style="float:right" src="http://www.hdtvmagazine.us/articles/images/5c86ed3503ee_132DD/clip_image007_44dd13ee-2ef6-466d-a774-85234cd6ca06.jpg" width="359" height="233">However, although WOLED still uses filters each WOLED sub-pixel is capable to generate and control its own light, which can be turned off at the pixel level to render blacker blacks and higher contrast than typical LCD panels with edge (or back) illumination designs, which are known to produce uneven light distribution across the panel, that is more noticeable when displaying a plain white image, showing the corners with a different illumination than the center of the screen (although multiple zone LED designs tend to mitigate that problem). <p>Samsung uses the classic RGB design of OLED, by which each of the 3 sub-pixels emits its own red, green or blue color, without using filters. If the pixel needs to show full white it blends the colors of the 3 sub-pixels to create white light, but if a pixel needs to show just red it should not need to use (shorting the life off) the blue and green sub-pixels. Unless black has to be displayed, LG’s WOLED uses all the color stacks of the sub-pixels constantly to produce white light regardless of the final color displayed by the pixel thru the color filters.  <p>Until both companies disclose the details of how their pixel technology is used on their OLED panels, it appears that Samsung’s RGB design uses more efficiently the blue sub-pixel organic material (known for its restricted durability relative to the other two colors) because it is only used when is actually needed to display blue, not to generate white light to show a non-blue color with a filter (WOLED).  <p>Additionally, not needing color filters at the front of the Samsung OLED panel potentially produces an image with true emissive benefits, like plasma and CRT, such as maintaining image quality even when viewed at wide angles (a <a target="_blank" href="http://www.hdtvmagazine.com/articles/2012/05/does-your-lcd-image-look-the-same-from-an-angled-view-part-1-the-concept.php">weakness of LCD</a> beyond 20 degrees off center). <p>Although the OLED Handbook does not actually provide the comparative analysis mentioned above, a quick reading persuaded me to recommend it to consumers and industry professionals that would like to get a closer look at the OLED technology.  <p>Ron Mertens, the author of “<b>The OLED Handbook Guide to OLED Technology, Industry &amp; Market, 2012 Edition</b>”, is a software engineer that in July 2004 launched <a target="_blank" href="http://www.oled-info.com/">OLED-Info</a> to offer daily news and resources for the OLED community, and has done a great effort to cover the OLED subject for manufacturers and industry professionals, while making the content easy to read for consumers,. <p>Here is the Table of Contents: <p><b>Introduction .........................................................................................5</b><br /><b>About the Author .................................................................................7</b><br /><b>Preface to the 2012 Edition ..................................................................8</b><br /><b>What is an OLED? .................................................................................9</b><br /><b>OLED: An Organic Light Emitting Device ................................................9</b><br /><b>OLED Structure ...................................................................................11</b><br /><b>Different Kinds of OLEDs ....................................................................16</b><br /><b>How an OLED is Made ........................................................................24</b><br /><b>Depositing and Patterning ..................................................................24</b><br /><b>Vacuum Evaporation with a Shadow Mask .........................................24</b><br /><b>Laser-Based Patterning .......................................................................25</b><br /><b>Inkjet Printing .....................................................................................25</b><br /><b>Nozzle Printing ....................................................................................26</b><br /><b>Other Production Methods ..................................................................26</b><br /><b>Scaling (Beyond Gen-5.5) .....................................................................28</b><br /><b>Fabricating an OLED Lighting Panel ......................................................28</b><br /><b>OLED Displays ......................................................................................30</b><br /><b>AMOLED Displays on the Market ..........................................................31</b><br /><b>Samsung AMOLED Displays ..................................................................32</b><br /><b>Nokia ClearBlack Display (CBD) .............................................................35</b><br /><b>PMOLED Displays on the Market ..........................................................36</b><br /><b>OLED TVs ..............................................................................................37</b><br /><b>OLED Microdisplays ..............................................................................38</b><br /><b>Flexible OLEDs Today ............................................................................39</b><br /><b>Transparent OLEDs Today .....................................................................42</b><br /><b>3D OLEDs ..............................................................................................43</b><br /><b>OLED Displays in Automobiles ..............................................................44</b><br /><b>Hot OLED Gadgets ................................................................................45</b><br /><b>OLED Lighting .......................................................................................48</b><br /><b>Why OLED Lighting? And Why Not? ......................................................50</b><br /><b>OLEDs vs. Other Lighting Sources ..........................................................51</b><br /><b>OLED Efficiency .....................................................................................52</b><br /><b>OLED Lighting Quality ...........................................................................53</b><br /><b>OLED Panels and Design Kits .................................................................54</b><br /><b>OLED Lamps ..........................................................................................63</b><br /><b>OLED Lighting Summary ........................................................................65</b><br /><b>OLED Lighting Roadmaps .......................................................................66</b><br /><b>The OLED Lighting Industry ....................................................................67</b><br /><b>The OLED Industry .................................................................................69</b><br /><b>The OLED Value Chain ...........................................................................69</b><br /><b>OLED Lighting ........................................................................................74</b><br /><b>Investing in OLEDs .................................................................................80</b><br /><b>A Final Word .........................................................................................81</b><br /><b>Acknowledgments ................................................................................82</b><br /><b>Appendices ...........................................................................................83</b><br /><b>Appendix A: Glossary ............................................................................83</b><br /><b>Appendix B: OLED Companies ...............................................................87</b><br /><b>Appendix C: Where to Buy OLED Modules ...........................................121</b><br /><b>Appendix D: OLED Fab Generations .....................................................124</b><br /><b>Appendix E: A Short OLED History ........................................................125</b><br /><b>Appendix F: Other Emerging Display Technologies ...............................129</b><br /><b>Appendix G: A Short Introduction to 3D Displays .................................136</b><p>The book can be ordered in hard copy or pdf version. 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 16, 2013  6:51 PM</b>
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
			<?=getComments(5004)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 5004)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2013/01/oled-tv-demystified.php" type="text/javascript" charset="utf-8"></script>
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