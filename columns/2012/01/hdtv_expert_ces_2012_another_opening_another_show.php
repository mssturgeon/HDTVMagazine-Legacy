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
		AND e.entry_id = 4642";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4642 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4642 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4642";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-ces-2012-another-opening-another-show.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4642";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW" />
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
	<title>HDTV Magazine - HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW</title>
	<meta name="keywords" content="wireless hdmi, south hall, gesture recognition, lcd tvs, video content, tvs, inch, hdmi, wireless, ’s, booth, lcd, –, new, ces, show, content, demo, still, showed, oled, system, hall, connectivity, digital" />
	<meta name="description" content="Attendance was up, aisles were crowded, and there were plenty of goodies to see in Las Vegas." />
	<meta name="title" content="HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-ces-2012-another-opening-another-show.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Attendance was up, aisles were crowded, and there were plenty of goodies to see in Las Vegas." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4642', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-ces-2012-another-opening-another-show.php">HDTV Expert - CES 2012: ANOTHER OPENING, ANOTHER SHOW</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January 18, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=514&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <div id="attachment_1658" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1658" rel="attachment wp-att-1658"><img class="size-full wp-image-1658" title="Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-11.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Over the top? Nahhh, it's CES!</p></div>
<p> </p>
<p>There’s still a debate about whether the U.S. economy has turned the corner and is on the rebound. As far as CES 2012 attendees were concerned, that ‘corner’ is way back in the rear-view mirror! According to official CES reports, over 140,000 people flocked to the Las Vegas Convention Center for the world’s second-largest annual gadget orgy (and at least 100,000 of them were constantly waiting on the South Hall cab lines).</p>
<p> </p>
<p>The show was notable for several things. First, the expanding presence of Chinese CE brands, like TCL, Changhong, Haier, and Hisense. (Never heard of them? You’re not alone.) Second, this show was Microsoft’s curtain call, as they’ve decided to go the route of Apple and stage their own product intros in the future.</p>
<p> </p>
<p>Third, there was a decided pull-back on 3D (aside from LG, who made it the focus of their booth) and a renewed emphasis on ‘connected’ TVs in all shapes and flavors. And fourth, gesture recognition made a well-deserved comeback this year after being mostly an afterthought in 2011.</p>
<p> </p>
<p>Overall, the show had less of a <em>“let’s build it because we can”</em> feel, and more of a <em>“let’s actually make a practical gadget that people will want to buy”</em> buzz. Still, there were the usual surprises – some were telegraphed in advance, while others showed up quite unexpectedly.</p>
<div id="attachment_1661" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1661" rel="attachment wp-att-1661"><img class="size-full wp-image-1661" title="Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-21.jpg" alt="" width="600" height="410" /></a><p class="wp-caption-text">LG's 55-inch OLED was a thing of beauty.</p></div>
<div id="attachment_1662" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1662" rel="attachment wp-att-1662"><img class="size-full wp-image-1662" title="Figure 3" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-3.jpg" alt="" width="600" height="426" /></a><p class="wp-caption-text">And Samsung's 55-inch OLED wasn't too shabby, either.</p></div>
<p> </p>
<p>Here’s an example. Both <strong>LG</strong> and <strong>Samsung</strong> showed <strong>55-inch organic light-emitting diode (OLED) TVs</strong> at the show. LG’s unveiling had been common knowledge, while Samsung’s was only revealed to members of the press under embargo. But both showings attracted constant crowds, as OLEDs in this size are a rare sighting!</p>
<p> </p>
<p>LG’s 55-incher is supposedly a production model and will come from a new Gen 8 fab in Korea. It uses Kodak’s white OLED technology (purchased by LG a couple of years ago), with discrete red, green, blue, and white filters applied. Samsung’s approach is a bit trickier and employs discrete red, green, and blue OLEDs. Both panels looked terrific, and thank goodness for LG Display’s separate, quieter and far less chaotic suite at the Bellagio, where I could examine the OLED TV more closely.</p>
<p> </p>
<p>It’s hard to upstage a demo like that, but <strong>Sony</strong> almost pulled it off by showing <strong>46-inch and 55-inch inorganic LED TVs</strong>. What’s an inorganic LED? It’s the same technology that powers those outdoor LED signs you see alongside highway and inside stadiums and arenas. Only Sony figured out a way to stuff 6.2 million small-pitch RGB LEDs into a TV, using an expensive and time-consuming wire bonding process that ensures (for now) that these products won’t come to market any time soon. But these TVs still looked spectacular and livened up what was otherwise a rather sedate Sony booth, compared to 2011 (remember that 92-foot passive 3D screen and the astronaut DJ?)</p>
<div id="attachment_1663" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1663" rel="attachment wp-att-1663"><img class="size-full wp-image-1663" title="Figure 4" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-4.jpg" alt="" width="600" height="259" /></a><p class="wp-caption-text">Sony's 46-inch Crystal LED (left) and a 46-inch Bravia LCD TV (right).</p></div>
<p> </p>
<p>Just down the hall, <strong>Sharp</strong> left no doubts about its product marketing strategy for the next few years by showcasing a new <strong>80-inch professional video display with touchscreen overlay</strong>. The Aquos Touch is adapted from Sharp’s 80-inch Aquos TV that launched in the fall of 2011, and complements the 70-inch product already in the line. Given that Sharp’s market share in TVs has inexplicably dwindled to the mid-single figures, this is an interesting approach – but the playing field is wide open. And the pro AV channel is very interested in large, self-contained displays that could replace traditional two-piece projector installations.</p>
<p> </p>
<p>Sharp also tickled our fancy with several Freestyle “portable” LCD TVs, including models as large as 60 inches. These TVs have been designed to be as light as possible and use a WiFi-based solution to stream HD content, so you can pretty much pick ‘em up and move ‘em wherever there’s an AC outlet. (I guess that includes the garage if you want to watch a football game with your best buds and keep the noise level down…)</p>
<p> </p>
<p>3D was around, but clearly took a back seat to other demos. Still, <strong>Toshiba</strong> showed several examples of <strong>1080p and 4K autostereo 3D TVs</strong> in their booth. These demos once again required the viewer to stand in specific locations to receive the full autostereo effect, and Toshiba thoughtfully provided small green circles with arrows in them as visual cues – when both were seen, you were positioned in a ‘sweet spot.’ Toshiba has clearly walked away from active 3D and has a few passive 3D sets in their line, but it appears autostereo is their game plan for the near future. (And yes, the 4K TV looked spectacular.)</p>
<p> </p>
<p>Next door, <strong>Panasonic</strong> anted up big time by showing <strong>a new line of LED-backlit LCD TVs</strong> that will be available in sizes to 55 inches, immediately casting doubts as to the company’s future plans for plasma TVs.  These ET-series sets employ Panasonic’s IPS-Alpha LCD panels and I have to admit, they looked doggone good, particularly at wide viewing angles. Still, the company had plenty of plasma announcements, including faster subfield drive for improved motion rendering and even lower power consumption from the 2011 plasma lineup. For my money, plasma is still the way to go – that is, until OLED prices drop low enough.</p>
<div id="attachment_1665" class="wp-caption aligncenter" style="width: 459px"><a href="http://www.hdtvexpert.com/?attachment_id=1665" rel="attachment wp-att-1665"><img class="size-full wp-image-1665" title="Figure 5" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-5.jpg" alt="" width="449" height="600" /></a><p class="wp-caption-text">4K is a lotta pixels! Wonder where the content will come from...</p></div>
<div id="attachment_1668" class="wp-caption aligncenter" style="width: 458px"><a href="http://www.hdtvexpert.com/?attachment_id=1668" rel="attachment wp-att-1668"><img class="size-full wp-image-1668" title="Figure 6" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-61.jpg" alt="" width="448" height="600" /></a><p class="wp-caption-text">Are Panasonic's new ET-series LCD TVs the 'writing on the wall' for plasma?</p></div>
<p> </p>
<p><strong>LG is head over heels in love with 3D.</strong> That’s the only conclusion anyone could make after cruising through their booth, which featured an enormous panoramic <strong>Cinema 3D</strong> videowall (passive, of course) at the Central Hall entrance. Inside, LG’s 55-inch OLED was shown with 3D and 2D content, and a nearby exhibit showcased an <strong>84-inch 4K 3D LCD monitor</strong>. (Sorry, it’s not for sale – yet…) 3D popped up on so many LG products that I expected the ‘smart’ washer and dryers also located in the massive exhibit to be labeled ‘Cinema 3D’ as well. (Technically speaking, you <span style="text-decoration: underline;">could</span> apply film patterned retarders to the front port of the washer – oh, never mind.)</p>
<p> </p>
<p>As mentioned earlier, the overwhelming presence of numerous Chinese brands at the show clearly shows which way the wind’s blowing these days. <strong>Haier</strong> brought back their clever <strong>wireless LCD TV demo</strong> from two years ago, and this version builds the inductive coupling system into the pedestal. Yes, it is completely wireless, power and all. (Amazing what you can do with a big transformer!) Elsewhere in the Haier booth, you could find a <strong>“brain wave TV” demo</strong> that was supposed to allow you to “think” of changing channels and raising/lowering volume. (It kinda worked.)</p>
<p> </p>
<p><strong>Changhong</strong> and <strong>TCL</strong> both exhibited some really sharp-looking LCD TV designs, proving that Japan and Korea don’t have any special magic in this area. All of the companies had 3D sets out for inspection with the majority using passive 3D technology, while several of the models were ‘smart’ TVs with built-in WiFi Internet connections for streaming video. No content partnerships were announced or seen, however. It’s telling that the size of these booths is getting larger with each year, while some of the Japanese TV manufacturers are slowly shrinking.</p>
<div id="attachment_1669" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1669" rel="attachment wp-att-1669"><img class="size-full wp-image-1669" title="Figure 7" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-7.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Look Ma - no HDMI cables, no power cables, no USB cables, no cables period!</p></div>
<div id="attachment_1670" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1670" rel="attachment wp-att-1670"><img class="size-full wp-image-1670" title="Figure 8" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-8.jpg" alt="" width="600" height="191" /></a><p class="wp-caption-text">I don't know what it means, but the thought is intriguing...</p></div>
<p> </p>
<p>Speaking of <strong>‘smart’ TVs</strong>, everybody had them – Sharp, LG, Samsung, Sony, Toshiba, Panasonic, Haier, TCL, Hisense, you name it. That included connected Blu-ray players. <strong>Samsung’s Apps for TV</strong> seems to be growing by leaps and bounds, and <strong>Panasonic’s Viera Connect</strong> has also added content partners. <strong>LG’s</strong> ‘smart’ TV featured a demo of the new <strong>Google TV interface</strong>, which certainly looked a lot more user-friendly that the last implementation and presented a much more logical process for searching and finding video content on the Web.</p>
<p> </p>
<p>Many of the companies exhibiting at CES used <strong>Rovi’s Total Guide EPG</strong> (or variations of it) to search out and find Web video content, as well as more traditional sources like cable, satellite, and even broadcast TV. Rovi has ported their guide to every possible platform and in their suite at Caesar’s Palace, showed implementations on set-top boxes, tablets, and a variety of TVs. The company is also into ad insertion and content delivery management systems. In short, they find it, stream it, and monetize it.</p>
<p> </p>
<p>How about connecting all of this stuff together? <strong>Rainbow Fish</strong> had a small booth in the rear of the South Hall, but it was worth hunting down. They are selling direct HDMI-to-fiber optic connectivity kits that use multimode fiber and require only a separate USB connection at the TV to supply 5 volt phantom power to the lasers. Everything is built into the plugs, so there’s no need for separate converter boxes.</p>
<div id="attachment_1673" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1673" rel="attachment wp-att-1673"><img class="size-full wp-image-1673" title="Figure 9" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-9.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">HDMI to fiber is here. Need a 300-foot extension? No problem!</p></div>
<div id="attachment_1674" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1674" rel="attachment wp-att-1674"><img class="size-full wp-image-1674" title="Figure 10" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-10.jpg" alt="" width="600" height="442" /></a><p class="wp-caption-text">DO try this at home. At least, 3M says so.</p></div>
<p> </p>
<p>A few booths away, <strong>3M was hawking a new ‘unbreakable’ HDMI cable design</strong>. Its super-flat and you can fold it, bend it, twist it – in short, pretty much abuse it any way you want. But you won’t screw up the signal, as 3M’s presentation showed. There are two types of cables – one for consumer applications, and one for computers (notebooks, I guess) and 3M offers plenty of options for color-coding the cable ends. They won’t be sold directly, but through OEM partners. Marry these with the drop-forged HDMI plugs I saw at a nearby booth, and you’ve got a ‘super’ HDMI connection.</p>
<p> </p>
<p>Don’t want to plug anything in? <strong>Silicon Image has rejuvenated the Wireless HD standard</strong> with its acquisition of SiBeam, and was demonstrating <strong>60 GHz wireless HDMI connectivity</strong> from tablets and notebook computers to large TVs. Wireless HD is a close range HDMI connectivity standard that is not WiFi based, and the chipsets and associated connections can now be manufactured in sizes small enough to build into a tablet. So, who will be the first to add it to <span style="text-decoration: underline;">their</span> tablet? (My vote is for the next-generation iPad.)</p>
<p> </p>
<div id="attachment_1686" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1686" rel="attachment wp-att-1686"><img class="size-full wp-image-1686" title="Figure 17" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-17.jpg" alt="" width="600" height="161" /></a><p class="wp-caption-text">Some signs just can't be explained. Your guess is as good as mine...</p></div>
<p>Over in the Hilton, the <strong>WHDI Consortium had their demos of 5 GHz wireless HDMI interfaces</strong> running on professional camcorders, tablets, notebooks (including wireless DisplayPort and wireless VGA, for some unknown reason), and TVs. <strong>Asus </strong>showed a production notebook computer with WHDI connectivity built-in, and <strong>HP</strong> is now selling a WHDI connectivity kit for computers and TVs. <strong>Atlona</strong> won a Best of CES award for its WHDI-based LinkCast wireless HDMI package. Can WHDI compete with Wireless HD? We’ll see as 2012 unfolds.</p>
<div id="attachment_1677" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1677" rel="attachment wp-att-1677"><img class="size-full wp-image-1677" title="Figure 11" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-111.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">I know a few people who could use waterproofed smart phones. (No, the iTunes clip wasn't "Splish Splash!")</p></div>
<div id="attachment_1678" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1678" rel="attachment wp-att-1678"><img class="size-full wp-image-1678" title="Figure 12" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-12.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Fish need camcorders?</p></div>
<p> </p>
<p>Have you ever dropped your cell phone in a pool, or in the toilet? <strong>HzO had a demonstration of their proprietary waterproofing system</strong> for handheld CE devices that showcased an iPhone merrily playing away a selection of iTunes while dunked in a fish tank for several hours. Other phones that had been ‘treated’ also took a dive. Waterproofing was a big thing at CES, as I spotted several tanks full of phones, camcorders, and still cameras.</p>
<p> </p>
<p>I mentioned gesture recognition earlier. <strong>PrimeSense</strong>, the company behind Microsoft’s Kinect Xbox motion recognition system, had an impressive demo of gesture recognition in the South Hall, and has licensed an add-on MS package to Asus called <strong>Xtion</strong>. A dancer in the booth kept things hopping with a ‘60s psychedelic imaging sequence that triggered all kinds of ‘trippy’ graphics and was fun to watch for a few minutes.</p>
<p> </p>
<p>Over in the <strong>Samsung</strong> booth, crowds lined up for the most impressive MS demo. Samsung’s implementation also incorporates voice and facial recognition, taking and storing a picture of each user with a top-mounted camera. The command “Hi, TV!” activates a menu bar along the bottom of the screen, and the user can then command channel and volume changes on the TV as well as navigate menus and delve into Samsung’s ‘smart’ TV system. Hand gestures are also used to raise and lower volume and navigate up/down through channels.</p>
<p> </p>
<p>Variations of gesture recognition were also seen in the <strong>LG</strong> and <strong>Haier</strong> booths, as well as by specialty manufacturers. Some systems require the use of a wand to control the TV; others simply rely on broad gestures – Haier’s demo had a fellow actually boxing in sync with the video game, and I was afraid he was going to deck himself at some point!</p>
<div id="attachment_1679" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1679" rel="attachment wp-att-1679"><img class="size-full wp-image-1679" title="Figure 14" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-14.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Seriously? A USB emergency in the field? Victorinox' has 16GB to go.</p></div>
<p> </p>
<div id="attachment_1680" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1680" rel="attachment wp-att-1680"><img class="size-full wp-image-1680" title="Figure 14" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-141.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Impressive car. Now, how exactly do you drive it? From the cloud? (Don't laugh...)</p></div>
<p>Other cool products at the show included Sharp’s 8K-resolution LCD TV, Victorinox’ 16 GB USB Swiss Army Knife (I kid you not), Belkin’s four-port ScreenCast wireless HDMI transmitter/receiver, Duracell’s cordless smart phone charging system (yes, it really works), Ford’s cloud-connected EVO concept car with personal health sensor monitoring, LG Display’s Art TV concept design, JVC’s new 4K camcorder for $4,000, BenQ’s new LCD monitors for gamers with instant picture setting changes, and Silicon Images’ demo of 3D mobile high-definition link (MHL) connectivity that resulted in the first TV screen I’ve ever seen with “airplane mode” on it.</p>
<p> </p>
<p>I’d be remiss by not commenting on one legendary company’s presence at the show. As many readers know, <strong>Kodak</strong> has been in a death spiral for the past decade as its core film business fades away and digital imaging takes over. The company recently received a warning from the New York Stock Exchange that it might be delisted (last time I checked, shares were selling at about 61 cents) and it is about to file for Chapter 11 bankruptcy protection in order to auction off its patents in digital imaging.</p>
<p> </p>
<p>So what the heck was the Great Yellow Father showing in that enormous booth in the upper South Hall? Why, its line of color inkjet printers, of course! Supposedly, color inkjets will be the salvation of Kodak, or at least that’s what the current management (ex-HP) tells us. Only problem is, Kodak’s market share in inkjet printers for 2011 was less than 5%, and they’re fast running out of cash for day-to-day operations.</p>
<div id="attachment_1681" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1681" rel="attachment wp-att-1681"><img class="size-full wp-image-1681" title="Figure 16" src="http://www.hdtvexpert.com/wp-content/uploads/2012/01/Figure-16.jpg" alt="" width="600" height="412" /></a><p class="wp-caption-text">OK, what's wrong with THIS picture?</p></div>
<p> </p>
<p>Somehow, Kodak’s long-time competitor <strong>Fuji </strong>managed to support both film-based and digital imaging and not drive over a cliff. At CES, they showed a new <strong>16-megapixel digital camera system</strong> <strong>with interchangeable lenses,</strong> upgraded their line of point-and-shoots, expanded the FinePix digital camera offerings, and continue to market a clever 3D digital camera. (Maybe Kodak ought to hire some of the Fuji guys…)</p>
<p> </p>
<p>I’ll have more coverage of CES 2012 during my annual Super Tuesday Technology Trends presentation at InfoComm 2012 this coming June in Las Vegas. See you there!</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January 18, 2012  9:54 AM</b>
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
			<?=getComments(4642)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4642)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/01/hdtv-expert-ces-2012-another-opening-another-show.php" type="text/javascript" charset="utf-8"></script>
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