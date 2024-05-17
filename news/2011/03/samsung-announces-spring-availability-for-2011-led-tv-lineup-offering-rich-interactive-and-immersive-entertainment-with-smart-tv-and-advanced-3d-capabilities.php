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
		AND e.entry_id = 4259";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4259 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4259 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4259";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/03/samsung-announces-spring-availability-for-2011-led-tv-lineup-offering-rich-interactive-and-immersive-entertainment-with-smart-tv-and-advanced-3d-capabilities.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4259";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities" />
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
	<title>HDTV Magazine - Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities</title>
	<meta name="keywords" content="picture quality, smart hub, active glasses, samsung electronics, electronics america, samsung, led, new, smart, march, picture, design, home, entertainment, series, tvs, consumers, experience, technology, content, models, electronics, hub, quality, glasses" />
	<meta name="description" content="Samsung Electronics America, Inc., a market leader and award-winning innovator in consumer electronics, today announced it will offer features previously available in its premium models across a broader range of its 2011 LED TV line. Products will begin shipping this month. The 21 new LED TV models, ranging in screen size from 19 to 55 inches, are packed with Smart TV and advanced 3D capabilities. Samsung's premium TVs will also incorporate a stylish ultra-thin bezel which helps images become part of the living room, achieving an overall immersive smart TV and 3D viewing experience.

In 2011, consumers can look forward to..." />
	<meta name="title" content="Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/03/samsung-announces-spring-availability-for-2011-led-tv-lineup-offering-rich-interactive-and-immersive-entertainment-with-smart-tv-and-advanced-3d-capabilities.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Samsung Electronics America, Inc., a market leader and award-winning innovator in consumer electronics, today announced it will offer features previously available in its premium models across a broader range of its 2011 LED TV line. Products will begin shipping this month. The 21 new LED TV models, ranging in screen size from 19 to 55 inches, are packed with Smart TV and advanced 3D capabilities. Samsung's premium TVs will also incorporate a stylish ultra-thin bezel which helps images become part of the living room, achieving an overall immersive smart TV and 3D viewing experience.

In 2011, consumers can look forward to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4259', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/03/samsung-announces-spring-availability-for-2011-led-tv-lineup-offering-rich-interactive-and-immersive-entertainment-with-smart-tv-and-advanced-3d-capabilities.php">Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>March 17, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">Samsung Announces Spring Availability for 2011 LED TV Lineup, Offering Rich, Interactive and Immersive Entertainment with Smart TV and Advanced 3D Capabilities</p>

<center><i>New Ultra-Thin Bezel Design, Smart Hub and 3D Features Bring Home Entertainment To The Very Edge</center></i><br />
<br />

<p><strong>NEW YORK--(BUSINESS WIRE)</strong>--Samsung Electronics America, Inc., a market leader and award-winning innovator in consumer electronics, today announced it will offer features previously available in its premium models across a broader range of its 2011 LED TV line. Products will begin shipping this month. The 21 new LED TV models, ranging in screen size from 19 to 55 inches, are packed with Smart TV and advanced 3D capabilities. Samsung's premium TVs will also incorporate a stylish ultra-thin bezel which helps images become part of the living room, achieving an overall immersive smart TV and 3D viewing experience.</p>

<p>In 2011, consumers can look forward to the following innovations:<br />
<ul><li>More than 70 percent of Samsung LED TVs will offer Samsung Smart TV&trade; capabilities. Features include Samsung Smart Hub, a brand new menu interface that enables quick search and discovery of video content with Your Video, Search and a full web browser (on select models), multimedia sharing across devices with AllShare and a hassle-free network setup with Samsung's One Foot Connection.</li><li>Samsung is also expanding the number of 3D-capable TVs so that consumers have even more choice in bringing home the most immersive entertainment experience.</li><li>Advanced picture enhancing technologies in the new TVs ensure consumers get the premium picture quality they demand, whether in 2D or 3D.</li><li>All these features are delivered without compromising efficiency – the 2011 LED TV line is compatible with the more stringent Energy Star&reg; 5.1 guidelines.</li></ul></p>

<p>"The living room is the activity center for many people and the TV is an ideal media hub for the home. Our products make it easy for anyone to instantly access and share the content they want, when they want it," said John Revie, senior vice president of Home Entertainment, Samsung Electronics America, Inc. "Together with new breakthrough designs, Samsung is encouraging people to take entertainment to the edge with our TVs."</p>

<p><br />
<strong>New ONE Design creates virtually borderless entertainment</strong></p>

<p>For Samsung, innovative TV design is an increasingly important and differentiating factor. The company led the industry when it launched its Touch of Color&trade; design and is again raising the bar with its new ONE Design ultra-thin bezel. The ONE Design concept reduces the size of the bezel on the LED D8000, D7000 and D6400 Series so consumers can enjoy a virtually edge-less TV viewing experience. The TV also blends in with and complements its surroundings with a touch of class.</p>

<p>The ONE design also addresses consumer demand for larger screen sizes. With the new ultra-slim bezel design, consumers can enjoy a larger screen without a physically larger TV; in fact, consumers will be able to put a larger TV screen in the same space previously occupied by a smaller model.</p>

<p>Samsung continues its tradition of design excellence beyond the bezel. The Quad Stand introduced in 2010, is slimmer in 2011, yet its petite profile keeps the LED D8000 and D7000 Series stable and sturdy with an air of elegance.</p>

<p><br />
<strong>Smart Hub Creates a Gateway to Content Made Easy</strong></p>

<p>Samsung expects 2011 to be a watershed year for smart TV. Leveraging insights gleaned from consumer interaction with Samsung Apps in 2010, Samsung developed Smart Hub, a simple menu system that empowers people to connect, discover and enjoy a wide range of content.</p>

<p>Key features of Smart Hub include:<br />
<ul><li><strong>Search</strong>, which allows users to easily search for content on the TV and via Samsung Apps or other online services.</li><li><strong>Your Video</strong>, which delivers recommendations based on a user's viewing history.</li><li><strong>Samsung Apps</strong>, the world's first HDTV-based application store, now with more than 200 apps to download in the U.S., Samsung Apps offers a range of paid and free apps that help people connect to their passions – whether in sports, entertainment, information, games or social networking.</li><li><strong>Web Browser</strong> is available on select models, including the D8000, D7000 and D6300, offering full web browsing directly from the TV.</li></ul></p>

<p>Smart Hub is available on almost all of Samsung's new 40-inch or larger LED TVs, including the LED D8000, D7000, D6400, D6300 and D6000 Series models.</p>

<p>For those seeking the ultimate Smart TV experience, the D8000 and D7000 Series include a QWERTY keyboard remote in the box, making it easy to search and discover new content with the push of a button.</p>

<p><br />
<strong>Expanded 3D Options for Any Home</strong></p>

<p>This year Samsung is bringing 3D to even more models and screen sizes. This gives consumers an immersive 3D home entertainment experience at a range of prices to help make it more cost-effective to own a 3D TV. The 2011 3D LED TV models deliver a complete 3D experience at home, with better 3D picture, the world's first 3D sound and new 3D active glasses.</p>

<p>To achieve vivid and crisp 3D picture quality, Samsung has made several technology improvements in its 2011 line. Its proprietary 3D Auto Contrast technology delivers premium picture quality without eye fatigue by maximizing the contrast between objects in the foreground and in the background. Samsung also improved its 2D to 3D up-conversion feature, making it easy to customize the 3D viewing experience by adjusting the depth of the 3D images.</p>

<p>The LED D8000, D7000 and D6400 Series feature Samsung's proprietary 3D peak algorithm, which enhances peak brightness by 20 percent while reducing power consumption by 15 percent in 3D scenes. The technology automatically adjusts brightness levels in dark scenes to ensure every 3D scene is bright and life-like.</p>

<p>At CES, Samsung introduced 3D sound and the company is building Samsung 3D Sound into all 2011 3D LED TVs. This new feature leverages the power of Samsung's SMART algorithm, a technology that builds on conventional wide stereo sound. With Samsung 3D Sound, consumers can enjoy the thrill of both audio and visual "pop out" based on the synchronization of 3D video and audio.</p>

<p>Samsung continues to offer the total 3D home entertainment solution with the introduction of new four new pairs of 3D active glasses. The Samsung SSG-3700CR is the world's lightest pair of active 3D glasses, weighing about one ounce. Ergonomically designed with flexible 'legs' and nose pad, the SSG-3700CR delivers a comfortable experience for everyone. All four of the 3D active glasses models are also Bluetooth-enabled, offering a stable signal and crisp, true-to-life 3D imagery for an optimal viewing experience. This year, the D8000 Series will include two sets of 3D active glasses in the box. The new 2011 3D active glasses are compatible only with 2011 3D TVs.</p>

<p>As part of the company's ongoing commitment to bring premium content to consumers, Samsung announced that its new 3D Starter Kit, which includes of two pairs of 3D active glasses, would also include Blu-ray 3D versions of DreamWorks Animation SKG's Megamind and the complete Shrek collection at no additional cost.</p>

<p><br />
<strong>True-to-Life Picture Quality Enhancements</strong></p>

<p>Samsung is building on its reputation of delivering industry-leading picture quality with the introduction of new picture enhancing technologies across its line of premium LED TVs.</p>

<p>With a 240Hz refresh rate, 2 millisecond motion picture response time (MPRT) and improved LED backlight scanning, the LED D8000 offers true-to-life 2D, 3D and HD images with outstanding clarity. In addition to the Ultra Clear Panel, this CES Innovation Award honoree TV is the first to offer Micro Dimming Plus technology to provide the richest, most lifelike pictures with deep blacks and pure whites.</p>

<p>The LED D7000 also incorporates improved backlight scanning technology and Auto Motion Plus, Samsung's proprietary frame interpolation algorithm, to create sharp 2D and 3D pictures and smooth frame transitions without blurring, even for video moving at top speeds.</p>

<p>For the consumer looking for superior picture quality at an affordable price, the LED D6400 Series TV is ideal. The LED D6400 Series is a 120Hz set combined with Samsung's unique double-rate frame transition technology – the world's fastest – to further reduce the picture frame transition time by 50 percent to enhance the viewing quality of 3D content.</p>

<p><br />
<strong>Pricing and Availability</strong></p>

<pre>
Series/Model	Estimated Selling Price		Screen Size (inches)	Availability
UN55D8000	$3,599.99			54.6			February 2011
UN46D8000	$2,799.99			45.9			February 2011
UN55D7000	$3,099.99			54.6			March 2011
UN46D7000	$2,299.99			45.9			March 2011
UN55D6400	$2,399.99			54.6			May 2011
UN46D6400	$1,599.99			45.9			March 2011
UN40D6400	$1,299.99			40.0			March 2011
UN55D6300	$2,299.99			54.6			March 2011
UN46D6300	$1,499.99			45.9			March 2011
UN40D6300	$1,199.99			40.0			March 2011
UN55D6000	$2,099.99			54.6			February 2011
UN46D6000	$1,299.99			45.9			February 2011
UN40D6000	$1,099.99			40.0			February 2011
UN32D6000	$899.99				31.5			February 2011
UN40D5500	$899.99				40.0			March 2011
UN32D5500	$699.99				31.5			March 2011
UN22D5010	$299.99				21.5			March 2011
UN22D5000	$299.99				21.5			March 2011
UN32D4000	$529.99				31.5			March 2011
UN19D4000	$249.99				18.5			March 2011
</pre>	
For more information on the 2011 LED TV line, please visit http://www.samsungusanews.com/

<p><br />
<strong>About Samsung Electronics America, Inc.</strong></p>

<p>Headquartered in Ridgefield Park, NJ, Samsung Electronics America, Inc. (SEA), a wholly owned subsidiary of Samsung Electronics Co., Ltd., markets a broad range of award-winning, digital consumer electronics and home appliance products, including HDTVs, home theater systems, MP3 players, digital imaging products, refrigerators and washing machines. A recognized innovation leader in consumer electronics design and technology, Samsung is the HDTV market leader in the U.S. Please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a> for more information.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>March 17, 2011  8:42 PM</b>
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
			<?=getComments(4259)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4259)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/03/samsung-announces-spring-availability-for-2011-led-tv-lineup-offering-rich-interactive-and-immersive-entertainment-with-smart-tv-and-advanced-3d-capabilities.php" type="text/javascript" charset="utf-8"></script>
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