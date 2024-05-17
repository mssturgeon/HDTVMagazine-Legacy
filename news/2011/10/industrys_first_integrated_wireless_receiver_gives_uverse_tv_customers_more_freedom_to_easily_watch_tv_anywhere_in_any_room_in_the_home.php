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
		AND e.entry_id = 4559";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4559 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4559 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4559";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/10/industrys-first-integrated-wireless-receiver-gives-uverse-tv-customers-more-freedom-to-easily-watch-tv-anywhere-in-any-room-in-the-home.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4559";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Industry\'s First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Industry\'s First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Industry\'s First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home" />
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
	<title>HDTV Magazine - Industry's First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home</title>
	<meta name="keywords" content="wireless receiver, verse wireless, television service, integrated wireless, north central, verse, wireless, receiver, customers, service, available, outlet, room, home, any, time, television, move, services, first, fee, watch, providers, att, based" />
	<meta name="description" content="You've probably always arranged your living room furniture around one fixture, without even thinking twice about it: the TV and TV outlet. For the first time ever, U-verse TV customers have the flexibility to set up their TV virtually wherever they want it — anywhere, in any room, on any wall.

The  AT&amp;amp;T* U-verse&amp;reg; TV Wireless Receiver is the first fully-integrated wireless receiver available from any TV provider, and gives U-verse TV customers even more freedom to watch TV when and where they want it, including rooms without an existing U-verse outlet. The Wireless Receiver will be available for order on..." />
	<meta name="title" content="Industry's First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Industry's First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/10/industrys-first-integrated-wireless-receiver-gives-uverse-tv-customers-more-freedom-to-easily-watch-tv-anywhere-in-any-room-in-the-home.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="You've probably always arranged your living room furniture around one fixture, without even thinking twice about it: the TV and TV outlet. For the first time ever, U-verse TV customers have the flexibility to set up their TV virtually wherever they want it — anywhere, in any room, on any wall.

The  AT&amp;amp;T* U-verse&amp;reg; TV Wireless Receiver is the first fully-integrated wireless receiver available from any TV provider, and gives U-verse TV customers even more freedom to watch TV when and where they want it, including rooms without an existing U-verse outlet. The Wireless Receiver will be available for order on..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4559', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/10/industrys-first-integrated-wireless-receiver-gives-uverse-tv-customers-more-freedom-to-easily-watch-tv-anywhere-in-any-room-in-the-home.php">Industry's First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 25, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=461&category=Fiber/IPTV HDTV">Fiber/IPTV HDTV</a></b>, <b><a href="/category.php?id=342&category=Wireless HDMI/HDTV">Wireless HDMI/HDTV</a></b>
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
				<p class="prtitle">Industry's First Integrated Wireless Receiver Gives U-verse TV Customers More Freedom to Easily Watch TV Anywhere, in Any Room in the Home</p>

<p><strong>DALLAS, Oct. 25, 2011 /PRNewswire/ -- </strong>You've probably always arranged your living room furniture around one fixture, without even thinking twice about it: the TV and TV outlet. For the first time ever, U-verse TV customers have the flexibility to set up their TV virtually wherever they want it — anywhere, in any room, on any wall.</p>

<p>The  AT&amp;T* U-verse&reg; TV Wireless Receiver is the first fully-integrated wireless receiver available from any TV provider, and gives U-verse TV customers even more freedom to watch TV when and where they want it, including rooms without an existing U-verse outlet. The Wireless Receiver will be available for order on Monday, Oct. 31. With the U-verse Wireless Receiver, you can enjoy:</p>

<ul><li>The flexibility to design your room and arrange your furniture to suit your style, since the TV is no longer tied to the TV outlet. In a customer survey conducted by AT&amp;T, more than two-thirds of respondents said it was very or somewhat important to have the flexibility to move the TV from one location to another without needing a TV outlet.</li><li>The ability to have a TV in rooms not typically wired for TV, including an outdoor covered patio. More than 80 percent of customers said an important reason for having a Wireless Receiver was so they could place a TV in a room where there's not an existing TV outlet, such as a kitchen or outdoors. More than half of survey respondents said they would most likely use a Wireless Receiver to move a TV to their porch or patio.</li><li>The ability to easily move your TV for special events. Thirty percent of customers surveyed said they would relocate a TV so they could have multiple TVs in a room to watch a sporting or pay-per-view event.</li><li>The ability to move your TV to the guest room or basement for house guests. More than 59 percent of survey respondents said they would relocate a TV to entertain visitors or guests.</li><li>Extra value of one receiver serving multiple rooms. With the flexibility and ease to move your TV around your home, the U-verse Wireless Receiver eliminates the need for dedicated U-verse receivers in less-frequented rooms.</li><li>A faster and simpler set-up process for customers and U-verse technicians. The U-verse Wireless Receiver simply connects to the TV and plugs into a power outlet. A wireless signal strength indicator helps you find the best place for your U-verse Wireless Receiver.</li><li>The same advanced U-verse TV experience and features available today, including the ability to watch live Standard Definition or High Definition TV and access interactive TV apps, your program guide, the U-verse Movies library and more. The Wireless Receiver also has full Total Home DVR capabilities to manage and play back recordings and pause and rewind live TV from any TV in the home.</li></ul>

<p>"For decades, the TV outlet has dictated how viewers can arrange their furniture and where they place their TV," said David Christopher, chief marketing officer, AT&amp;T Mobility and Consumer Markets. "Now, for the first time, customers have the freedom to move the TV virtually whenever and wherever they want to, without a special appointment with a service technician. It's another innovation for U-verse TV."</p>

<p>The U-verse Wireless Receiver is available for a one-time fee of $49 and a standard $7 per month receiver rental fee.</p>

<p><br />
<strong>How it Works</strong></p>

<p>TV content is delivered from the wireless access point near the residential gateway over your in-home Wi-Fi to the U-verse Wireless Receiver, made by Cisco. All U-verse Internet packages include wireless home networking at no extra cost. The easy set-up reduces the time for installation appointments because technicians won't need to rewire or add outlets, and it's also simple for existing customers to add a wireless receiver on their own. </p>

<p><br />
<strong>You may view a demo of the U-verse Wireless Receiver here. </strong></p>

<p>AT&amp;T U-verse TV is the fastest growing TV service in the country and the only 100 percent Internet Protocol-based television (IPTV) service offered by a national service provider. U-verse is built on the mission of bringing customers an innovative and better experience than cable, and the industry's first fully-integrated Wireless Receiver is the latest example in a long line of enhancements. AT&amp;T U-verse TV ranked "Highest in Residential Television Service Satisfaction in the North Central, South and West Regions," according to the J.D. Power and Associates 2011 Residential Television Service Provider Satisfaction Study(SM).</p>

<p>*AT&amp;T products and services are provided or offered by subsidiaries and affiliates of AT&amp;T Inc. under the AT&amp;T brand and not by AT&amp;T Inc.</p>

<p><br />
<strong>About AT&amp;T</strong></p>

<p>AT&amp;T Inc. (NYSE: T) is a premier communications holding company and one of the most honored companies in the world. Its subsidiaries and affiliates – AT&amp;T operating companies – are the providers of AT&amp;T services in the United States and around the world. With a powerful array of network resources that includes the nation's fastest mobile broadband network, AT&amp;T is a leading provider of wireless, Wi-Fi, high speed Internet, voice and cloud-based services. A leader in mobile broadband and emerging 4G capabilities, AT&amp;T also offers the best wireless coverage worldwide of any U.S. carrier, offering the most wireless phones that work in the most countries.  It also offers advanced TV services under the AT&amp;T U-verse&reg; and AT&amp;T | DIRECTV brands. The company's suite of IP-based business communications services is one of the most advanced in the world. In domestic markets, AT&amp;T Advertising Solutions and AT&amp;T Interactive are known for their leadership in local search and advertising. </p>

<p>Additional information about AT&amp;T Inc. and the products and services provided by AT&amp;T subsidiaries and affiliates is available at http://www.att.com.  This AT&amp;T news release and other announcements are available at http://www.att.com/newsroom and as part of an RSS feed at <a target="_blank" href="http://www.att.com/rss/">www.att.com/rss</a>. Or follow our news on Twitter at @ATT.</p>

<p>&copy; 2011 AT&amp;T Intellectual Property. All rights reserved. Mobile broadband not available in all areas. AT&amp;T, the AT&amp;T logo and all other marks contained herein are trademarks of AT&amp;T Intellectual Property and/or AT&amp;T affiliated companies. All other marks contained herein are the property of their respective owners.</p>

<p>Geographic and service restrictions apply to AT&amp;T U-verse. Call or go to <a target="_blank" href="http://www.att.com/uverse/">www.att.com/uverse</a> to see if you qualify.</p>

<p>AT&amp;T U-verse TV: Residential customers only. Prices, programming, features and offers subject to change without notice. A one-time TV service activation fee of $36 applies. A one-time fee of $49, and monthly recurring fee of $7, per wireless receiver applies; power outlet and connection of receiver to TV required; Limit 2 per household.  Technical restrictions apply; may not be available to all customers.</p>

<p>AT&amp;T U-verse received the highest numerical score among television service providers in the North Central, South and West regions in the proprietary J.D. Power and Associates 2011 Residential Television Service Satisfaction Study(SM). Study based on 23,880 total responses from measuring 12 providers in the North Central region (IL, IN, MI, OH, WI), 13 providers in the South (AL, AR, FL, GA, KS, KY, LA, MS, MO, NC, OK, SC, TN, TX), and 10 providers in the West (AZ, CA, CO, ID, IA, MN, MT, NE, NV, NM, ND, OR, SD, UT, WA, WY) and measures consumer satisfaction with television service. Proprietary study results are based on experiences and perceptions of consumers surveyed in Nov. 2010, Jan. 2011, April 2011 and July 2011. Your experiences may vary.  Visit jdpower.com.</p>

<p>SOURCE AT&amp;T Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 25, 2011  2:59 PM</b>
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
			<?=getComments(4559)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4559)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/10/industrys-first-integrated-wireless-receiver-gives-uverse-tv-customers-more-freedom-to-easily-watch-tv-anywhere-in-any-room-in-the-home.php" type="text/javascript" charset="utf-8"></script>
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