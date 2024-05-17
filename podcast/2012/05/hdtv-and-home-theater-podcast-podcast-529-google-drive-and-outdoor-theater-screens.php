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
		AND e.entry_id = 4795";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4795 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4795 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4795";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-529-google-drive-and-outdoor-theater-screens.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4795";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens</title>
	<meta name="keywords" content="amazon prime, price amazon, screen price, prime eligible, aspect ratio, screen, outdoor, theater, amazon, includes, movie, price, set, inch, projection, eligible, prime, inflatable, screens, summer, make, easy, frame, home, aspect" />
	<meta name="description" content="Until recently, it felt like the weather was changing here in Southern California. We clicked over to daylight savings time; we had a few days of nice, sunny BBQ weather and it felt like the season of backyard movies was upon us. Since then it&amp;acirc;��s gotten a bit gloomy again, but we always like to be prepared, so we&amp;#039;ve put together a list of screen options for you if you finally want to make the jump into outdoor cinema." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-529-google-drive-and-outdoor-theater-screens.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Until recently, it felt like the weather was changing here in Southern California. We clicked over to daylight savings time; we had a few days of nice, sunny BBQ weather and it felt like the season of backyard movies was upon us. Since then it&amp;acirc;��s gotten a bit gloomy again, but we always like to be prepared, so we&amp;#039;ve put together a list of screen options for you if you finally want to make the jump into outdoor cinema." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4795', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-529-google-drive-and-outdoor-theater-screens.php">HDTV and Home Theater Podcast - Podcast #529: Google Drive and Outdoor Theater Screens</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>May  3, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>5 Affordable Outdoor Theater Screens for Summer Movie Fun</h3>
<p>Until recently, it felt like the weather was changing here in Southern California. We clicked over to daylight savings time; we had a few days of nice, sunny BBQ weather and it felt like the season of backyard movies was upon us. Since then it’s gotten a bit gloomy again, but we always like to be prepared, so we&#8217;ve put together a list of screen options for you if you finally want to make the jump into outdoor cinema.</p>
<p><strong><a href="http://www.amazon.com/gp/product/B0033SC2SE/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0033SC2SE">Camp Chef 120-Inch Portable Outdoor Movie Theater Screen</a></strong><br />
Price: $153, Amazon Prime eligible<br />
This screen has a 4.5 star rating from Amazon users and looks like a sure bet. It isn&#8217;t an inflatable screen, so there&#8217;s no need to worry about fan noise, and it collapses down to quite a reasonable size for storage in the winter.  It even includes storage bags to make sure everything is protected and intact when you pull it out again the next summer.</p>
<ul>
<li>120-inch screen for projecting movies outdoors or indoors</li>
<li>600D by 600D Oxford nylon screen; 16:9 aspect ratio</li>
<li>Kit includes four guy ropes with stakes for stability; easy setup</li>
<li>Includes separate heavy duty storage bags for both screen and frame</li>
<li>Steel tube frame measures 1-1/2-inches in diameter; unit weighs 44 pounds</li>
</ul>
<p><strong><br />
<a href="http://www.amazon.com/gp/product/B006CT46DU/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B006CT46DU">Outdoor Entertainment Gear OS92 Indoor/Outdoor Movie Theater Screen</a></strong><br />
Price: 92&#8243; $153, 115&#8243; $199, Amazon Prime eligible<br />
This screen has a 5 star rating, granted only from 3 reviewers, but all 3 love it.  The metal frame appears to be quite sturdy and it appears to pack up a little smaller than the Camp Chef screen. However, it doesn&#8217;t come with legs. So if you have a patio cover or deck you can hook it to, you’ll be all set.  If not, you might need to add the <a href="http://www.amazon.com/gp/product/B006CUBCYK/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B006CUBCYK">Outdoor Entertainment Gear OSKIT Indoor/Outdoor Big Screen Leg Kit</a> for an additional $62.</p>
<ul>
<li>Light weight aluminum frame big screen</li>
<li>Oxford nylon sivler screen for great viewing quality</li>
<li>Easy to assemble and set up</li>
<li>Designed for front and rear projection</li>
<li>Hanging straps and hardware included</li>
</ul>
<p><strong><a href="http://www.amazon.com/gp/product/B001615V9M/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B001615V9M">Sima XL-72 72&#8243; Inflatable Home Theater Kit</a></strong><br />
Price: $153, Amazon Prime eligible<br />
It is the smallest screen on the list, but it isn’t the least expensive. As an inflatable screen, it will be very easy to collapse and store, but you&#8217;ll also have to find a way to get it inflated each time you want to use it. This model doesn&#8217;t have a dedicated blower, so at least you won’t have to contend with fan noise during the movie. Amazon users rate this one at 3.5 stars. It does include 2 screen options, both White and Gray, so you can choose the one that works best for your situation. We recommend a small <a href="http://www.amazon.com/gp/product/B0009PURX6/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0009PURX6">compressor/pump</a> to save your lungs.</p>
<ul>
<li>16:9 Hdtv Format</li>
<li>Includes 2 Screens: Bright White For Vivid Picture Even In Partially Lit Rooms &amp; Dove Gray For High Contrast Picture In Darker Rooms</li>
<li>Cinema-Quality Fabric</li>
<li>Fixed Screen Tension For Perfectly Flat Surface</li>
<li>High Effective Scattered Screen Angle</li>
</ul>
<p><strong><a href="http://www.amazon.com/gp/product/B000HRYV38/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B000HRYV38">Epson Duet 80-Inch Dual Aspect Ratio Projection Screen</a></strong><br />
Price: $118, Amazon Prime eligible<br />
This portable screen can be adjusted to show either 4:3 or 16:9 aspect ratios. It is a portable screen and includes a tripod, so it should set up fairly quickly in your yard. But it isn&#8217;t necessarily intended for outdoor use, so it doesn&#8217;t include any form of anchors to hold it in place. Depending on what kind of weather you&#8217;re used to in the summer time, specifically how strong a summer breeze might be, this screen could be problematic.  But it is very affordable and received 4.5 stars from over 100 customer reviews.</p>
<ul>
<li>Use with any home theater or business projector</li>
<li>Enjoy fast, easy setup with the innovative, patent-pending design</li>
<li>Expand Screen to the size that best meets your needs &#8211; Standard 4:3 or widescreen 16:9</li>
<li>Mount on the included floor stand or on a wall with the included wall bracket</li>
<li>When closed, Duet forms its own carrying case (just 43&#8243; long)</li>
</ul>
<p><strong><a href="http://www.amazon.com/gp/product/B000FGD2SQ/ref=as_li_ss_tl?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=as2&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B000FGD2SQ">Open Air Outdoor Home Projector Screen 16&#215;9</a></strong><br />
Price: $1226<br />
Perhaps our list should have said &#8220;4 affordable screens and 1 gigantic screen that costs quite a bit&#8221; but this screen is a giant. It is a 220&#8243; screen that will make your backyard feel like a drive in theater. The only way to easily set-up, tear-down and store a screen that big is to make it inflatable, which this one is. It also includes a blower to keep it perfectly inflated for your entire movie, but that could impact your audio experience as well.</p>
<ul>
<li>Wide screen, 16:9 aspect ratio.</li>
<li>Easier to set up than a pup tent</li>
<li>220-inch diagonal projection surface (about 18&#8242;)</li>
<li>Weighs only 17 pounds and fits inside a stuff sack.</li>
<li>Inflates in less than 20 seconds! No need to attach projection surface because it is permanently attached for hassle free setup.</li>
</ul>
<p>&nbsp;</p>
<h4>Also on today&#8217;s show:</h4>
<ul>
<li><a href="http://www.htguys.com/audioengine"><strong>Win a pair of Audioengine A5+ speakers</strong></a></li>
<li><a href="http://www.pcmag.com/article2/0,2817,2402535,00.asp">HDTV Alternatives: 5 High-End TV Projectors</a></li>
<li><a href="http://www.marketwatch.com/story/software-lets-movie-theaters-play-71-and-111-surround-sound-without-adding-speakers-accomplished-through-partnership-between-dms-and-genaudio-2012-04-23">Software Lets Movie Theaters Play 7.1 and 11.1 Surround Sound Without Adding Speakers</a></li>
<li><a href="http://drive.google.com/">Google Drive</a></li>
<li><a href="http://www.amazon.com/mn/search/?encoding=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;field-keywords=IR%20Repeater&amp;url=search-alias%3Daps">IR Repeaters at Amazon</a></li>
<li>Pioneer introduces new Elite AVRs</li>
<li>20% US Households Have an Internet-Connected TV</li>
<li>Six new Epson PowerLite projectors unveiled</li>
</ul>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-05-04.mp3">Download Episode #529</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>May  3, 2012 10:06 PM</b>
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
			<?=getComments(4795)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4795)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/05/hdtv-and-home-theater-podcast-podcast-529-google-drive-and-outdoor-theater-screens.php" type="text/javascript" charset="utf-8"></script>
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