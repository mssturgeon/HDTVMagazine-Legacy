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
		AND e.entry_id = 4089";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4089 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4089 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4089";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2010/12/hdtv-and-home-theater-podcast-podcast-456-hdtv-buying-guide-2010-and-dolby-contest-winner-announced.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4089";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced</title>
	<meta name="keywords" content="lcd hdtv, last year, dynamic contrast, contrast ratio, panasonic plasma, inch, hdtv, year, plasma, panasonic, lcd, last, led, ”, ’s, price, samsung, contrast, category, might, get, video, size, viera, buying" />
	<meta name="description" content="Our HDTV buying guide comes back by popular demand, just in time for Christmas. We’ll break them down by size again, which gets pretty close to breaking them down by price as well.  The 2010 edition shows that same falling price trend as years past, so you don’t have to feel guilty when you drop hints on what you “deserve” to see under the tree this year." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2010/12/hdtv-and-home-theater-podcast-podcast-456-hdtv-buying-guide-2010-and-dolby-contest-winner-announced.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Our HDTV buying guide comes back by popular demand, just in time for Christmas. We’ll break them down by size again, which gets pretty close to breaking them down by price as well.  The 2010 edition shows that same falling price trend as years past, so you don’t have to feel guilty when you drop hints on what you “deserve” to see under the tree this year." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4089', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2010/12/hdtv-and-home-theater-podcast-podcast-456-hdtv-buying-guide-2010-and-dolby-contest-winner-announced.php">HDTV and Home Theater Podcast - Podcast #456: HDTV Buying Guide 2010 and Dolby Contest Winner Announced</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>December  9, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
<h3>HDTV Buying Guide 2010</h3>
<p>Our HDTV buying guide comes back by popular demand, just in time for   Christmas. We’ll break them down by size again, which gets pretty close   to breaking them down by price as well.  The 2010 edition shows that   same falling price trend as years past, so you don’t have to feel guilty   when you drop hints on what you “deserve” to see under the tree this   year.</p>
<h4>Up to 32&#8243;</h4>
<p>Great  for a secondary viewing room, bedroom or office, the TVs in  this  category are all about value.  If you live in a small apartment or  dorm  room, these might even work as your primary screen.</p>
<p><a href="http://www.htguys.com/shop?id=B003DV93LU">VIZIO M190MV 19-inch Full HD 720p LED LCD HDTV</a> &#8211; Like last year, we wanted to have a TV for under $200 on the list.    Last year it was a Vizio as well.  But this year you get to upgrade to   an LED model that provides a 20,000:1 Dynamic Contrast Ratio.</p>
<p><a href="http://www.htguys.com/shop?id=B003ES5B6S">ViewSonic VT2300LED 23-Inch 1080p LED LCD HDTV with Built-in HDTV Tuner</a>.    Sure, you might not need 1080p at 23 inches, but for only $249, why   not?  It’s also a super slim edge lit LED backlight and includes SRS HD   Audio to please your ears.</p>
<p><a href="http://www.htguys.com/shop?id=B0039213XY">Panasonic TC-L32X2 32-Inch 720p LCD HDTV with iPod Dock</a> &#8211; A bit more than the basic 32” HDTV, this Panasonic includes a   Universal Dock for Apple iPod, which enables you to control and play   music and video directly from your iPod or iPhone.  Great deal for only   $368.</p>
<h4>32&#8243; to 40&#8243;</h4>
<p>The  deals in this size category are almost comical.  We expect  retailers  will have to pay you to take one off the shelves in a couple  years. Last  year we had a couple 37 inch models for about $600.  This  year you can  get a 37 inch TV for less than $400.</p>
<p><a href="http://www.htguys.com/shop?id=B002DQUAPE">VIZIO 37&#8243; Class 1080p 60Hz LCD HDTV</a> &#8211; Dynamic 50,000:1 Contrast Ratio allows for deeper blacks and brighter   whites.  It is also Version 3.0 ENERGY STAR compliant, so it’ll save   you money every month, and it only sets you back $399 to start.</p>
<p><a href="https://docs.google.com/document/d/1Mcj9THeVxiLeTw40CfgAi8HmcyRgX9xgd6U-FYeHT8E/edit?hl=en">LG 37LE5300 37-Inch 1080p 120 Hz LED LCD HDTV</a> &#8211; For just slightly more than you would have paid last year for a 37”,   only $679, you now get an LED TV that is both super slim and reports an   incredible 3,000,000:1 dynamic contrast ratio.  Throw in support for   DivX HD video files, and Dolby Digital decoding and we have a winner.</p>
<p><a href="http://www.htguys.com/shop?id=B001UV6P1Q">Samsung LN40B750 40-Inch 1080p 240 Hz LCD HDTV with Charcoal Grey Touch of Color</a> &#8211; Samsung is the dominant player in LCD right now, and this TV won’t   let you down.  It has technology like Auto Motion Plus 240Hz, 2ms   response time, and a 150,000:1 dynamic contrast ratio.  Medi@2.0 lets   you enjoy Internet@TV for content via Yahoo! and more.  Street price:   $929.</p>
<h4>42&#8243; to 50&#8243;</h4>
<p>Pricing  in this category didn’t change much, but like last year, the  features  keep getting better.  This is the first category where we can  start  recommending plasma technology, and we still highly recommend  it.  But  with LED backlighting, the differences between LCD and plasma  are  getting harder to distinguish.</p>
<p>There are actually 3 choices for 42” plasma TVs for under $500 ($499 to be exact).  The <a href="http://www.htguys.com/shop?id=B0036WT3V6">Samsung PN42C450</a>, the <a href="http://www.htguys.com/shop?id=B003924U7A">Panasonic TC-P42C2</a>, and the <a href="http://www.htguys.com/shop?id=B0038W35EK">LG 42PJ350</a>.    We’ve long been fans of Panasonic plasma, but they’re all 720p and   seem roughly equivalent in specs.  You can pick the one that has your   favorite bezel.  A 42 inch plasma for $500 isn’t bad; Braden spent about   7x more than that for his first 42” Panasonic.</p>
<p>Last year the Samsung LN46A650 made our list and set you back $1260.  This year it is replaced by the <a href="http://www.htguys.com/shop?id=B0036WT3RU">Samsung LN46C650 46-Inch 1080p 120 Hz LCD HDTV</a>.    You keep all the great features that put the set on our list last   year: fantastic picture, Red Touch of Color, 120Hz, DLNA and HDMI-CEC.    But this model also adds Internet widgets for access to BLOCKBUSTER,   Facebook, YouTube, Flickr, Amazon Video On Demand and more.  And the   price has come down to $1099.</p>
<p>At 50 inches, 1080p might be important to you, and the best deal on a 50” TV is still the <a href="http://www.htguys.com/shop?id=B003924UCK">Panasonic VIERA TC-P50G25 50-Inch 1080p Plasma</a>.    Amazing picture quality and the “Infinite Black” panel blocks ambient   light and produces deep blacks and bright, vivid images with minimal   reflection.  It also has VIERA Cast for streaming online content plus   video conferencing via Skype.  The price?  Only $1068.</p>
<h4>Greater than 50&#8243;</h4>
<p>This  size category used to be dominated by rear projection sets, but   starting last year &#8211; and continuing this year &#8211; we don’t have any on  the  list.  Once again we are only recommending flat panel displays.  At   this size, 3D might be something for you to consider, but we aren’t  just  yet.</p>
<p><a href="http://www.htguys.com/shop?id=B0038JED9E">Toshiba 55UX600U 55-Inch 1080p 120 Hz LED HDTV with Net TV</a>.    This TV is awesome; you’ll love it.  It is LED backlit and the   DynaLight backlight control adjusts for deeper black levels providing a   much higher dynamic contrast than standard LCD.  It has Net TV  on-demand  entertainment from sites like VUDU and Pandora, access to  social  networking sites and includes a Wi-Fi adapter.  Street price:  $1299.</p>
<p><a href="http://www.htguys.com/shop?id=B00252SQ0M">LG 55LH85 55-Inch 1080p 120 Hz Wireless HDMI LCD HDTV</a> &#8211; Where the Toshiba gives you LED for better contrast, this LG set   instead provides built in support for wireless HDMI, making it easy to   hang on any wall anywhere.  All you need is power.  hide all your   components in a closet for a very clean looking installation.  When you   consider the convenience, $1791 is a pretty good deal.</p>
<p><a href="http://www.htguys.com/shop?id=B003B56LFY">Sharp AQUOS LC60E88UN 60-Inch 1080p X-Gen Panel TV</a>.    Once the 800 pound gorilla in LCD TVs, Sharp has slipped in the   standings, but their TVs are still really good.  This set has all the   features you need to produce amazing video quality.  Sure it won’t do   3D, but it does everything else really, really well.  You get 60 inches   of 1080p goodness at 240hz for $1695.  Certainly a TV worth  considering.</p>
<h4>HT Guy&#8217;s Ultimate Christmas Present:</h4>
<p>Last  year we showcased Mitsubishi’s 82 inch DLP as our ultimate  present.  At  82 inches it came close to front projection theater size  and at the  time only cost $4500.  This year there’s an <a href="http://www.htguys.com/shop?id=B002RL8I0Y">85” Panasonic plasma</a> you can pick up for around $22,000, but that’s a little much even for us.  Instead we went with the <a href="http://www.htguys.com/shop?id=B001AAQTXQ">Pioneer PDP-6020FD 60-Inch Class KURO Plasma HDTV</a>.    We all know KURO plasmas are amazing.  And since they aren’t being   made anymore, so it’s almost like buying a classic vinyl.  It just feels   special to have one.  Sure, $5699 might be a bit much just to feel   special.  But you’ll tell your grandkids about owning one.  We promise.</p>
<p>Or you could save a little money and get the <a href="http://www.htguys.com/shop?id=B003N3BV90">Panasonic TC-P65VT25 65-inch 3D Ready 1080p VIERA Plasma HDTV</a>.    Sure it’s the same cost, $4500, as last year’s Mitsubishi for 17 less   inches of screen, but it’s a plasma.  And not only that, it’s 3D  ready,  so you’re all set for the future.  When you consider that the  Panasonic  65” plasmas cost around $18,000 when they first came out a  few years  ago, $4500 is a steal.</p>
<p>Here’s a little secret. Ara will be buying his family the <a href="http://www.htguys.com/shop?id=B003N3BV5O">Panasonic TC-P58VT25 58-inch 3D 1080p VIERA Plasma</a> HDTV ($2700) to finally replace his 6 year old Samsung DLP. It was a   tough decision between this and the Vizio we reviewed last summer. He   could have saved $1000 with the Vizio but in the end, with 3D in the   news everywhere we look, we felt it was important for one of us to have a   3D capable TV. The sacrifices we make for our listeners!  It also  helps  that CNET said this is the best TV they’ve ever seen! As far as  3D  goes, the best 3D we have seen was on a Panasonic plasma at last  year’s  CES show. Throw in Viera Cast and its a win, albeit an expensive  one.</p>
<p>By the way, if you’re interested, the <a href="http://www.htguys.com/shop?id=B003I4YMOK">Mitsubishi 82” DLP</a> is down to $3,200 and the non-3D <a href="http://www.htguys.com/shop?id=B00391Z89K">Panasonic 65-Inch Plasma</a> is only $1988.</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2010-12-10.mp3">Download Episode #456</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>December  9, 2010 10:43 PM</b>
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
			<?=getComments(4089)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4089)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2010/12/hdtv-and-home-theater-podcast-podcast-456-hdtv-buying-guide-2010-and-dolby-contest-winner-announced.php" type="text/javascript" charset="utf-8"></script>
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