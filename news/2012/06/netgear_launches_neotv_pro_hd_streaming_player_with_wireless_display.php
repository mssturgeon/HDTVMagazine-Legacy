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
		AND e.entry_id = 4835";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4835 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4835 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4835";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/06/netgear-launches-neotv-pro-hd-streaming-player-with-wireless-display.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4835";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display" />
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
	<title>HDTV Magazine - NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display</title>
	<meta name="keywords" content="neotv pro, streaming player, wireless display, netgear neotv, netgear inc, netgear, neotv, streaming, pro, intel, products, player, display, wireless, trademarks, widi, internet, company, available, inc, consumers, may, channels, new, performance" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/netgear-neotv-pro-hd-streaming-player.jpeg&quot; alt=&quot;NETGEAR NeoTV Pro HD Streaming Player&quot; height=&quot;67&quot; width=&quot;125&quot; style=&quot;padding:0 5px 5px 0&quot;&gt;NETGEAR&amp;reg;, Inc. (NASDAQGM: NTGR), a global networking company that delivers innovative products to consumers, businesses and service providers, today announced the NETGEAR NeoTV&amp;trade; Pro HD Streaming Player (NTV200S), the latest addition to the NETGEAR NeoTV streaming player family of products. NeoTV Pro offers high quality streaming content from a wide variety of online channels and is the world's first streaming player to offer wireless display capability using Intel&amp;reg; Wireless Display (WiDi).

With the NeoTV Pro, users can..." />
	<meta name="title" content="NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/06/netgear-launches-neotv-pro-hd-streaming-player-with-wireless-display.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/netgear-neotv-pro-hd-streaming-player.jpeg&quot; alt=&quot;NETGEAR NeoTV Pro HD Streaming Player&quot; height=&quot;67&quot; width=&quot;125&quot; style=&quot;padding:0 5px 5px 0&quot;&gt;NETGEAR&amp;reg;, Inc. (NASDAQGM: NTGR), a global networking company that delivers innovative products to consumers, businesses and service providers, today announced the NETGEAR NeoTV&amp;trade; Pro HD Streaming Player (NTV200S), the latest addition to the NETGEAR NeoTV streaming player family of products. NeoTV Pro offers high quality streaming content from a wide variety of online channels and is the world's first streaming player to offer wireless display capability using Intel&amp;reg; Wireless Display (WiDi).

With the NeoTV Pro, users can..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4835', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/06/netgear-launches-neotv-pro-hd-streaming-player-with-wireless-display.php">NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June  8, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>
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
				<p class="prtitle">NETGEAR Launches NeoTV Pro HD Streaming Player With Wireless Display</p>

<center><i>Wirelessly stream the London Olympics from your laptop to your TV using built in Intel Wireless Display (WiDi) or stream Netflix, Hulu Plus, Vudu, Pandora and hundreds of other online channels with the new NeoTV Pro</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/netgear-neotv-pro-hd-streaming-player.jpeg" alt="NETGEAR NeoTV Pro HD Streaming Player" height="135" width="250" class="keyimg"><strong>SAN JOSE, Calif., June 7, 2012 /PRNewswire/</strong> -- NETGEAR&reg;, Inc. (NASDAQGM: NTGR), a global networking company that delivers innovative products to consumers, businesses and service providers, today announced the NETGEAR NeoTV&trade; Pro HD Streaming Player (NTV200S), the latest addition to the NETGEAR NeoTV streaming player family of products. NeoTV Pro offers high quality streaming content from a wide variety of online channels and is the world's first streaming player to offer wireless display capability using Intel&reg; Wireless Display (WiDi).</p>

<p>With the NeoTV Pro, users can choose from a large selection of streaming movies, TV shows and music from streaming services like Netflix, Hulu Plus, Vudu, YouTube, Pandora and many others, and instantly stream it to their TVs in up to full 1080p HD and surround sound from the comfort of their living room. And now, for customers that are not satisfied with hundreds of streaming channels but want the worldwide web on the big screen, the NeoTV Pro enables consumers to wirelessly display virtually any type of content from an Intel WiDi laptop to a TV, including sport broadcasts that are only online, movies, music, photos and any Internet site.(1)</p>

<p>NETGEAR NeoTV Pro easily connects to the Internet via built-in high-speed 300 Mbps WiFi, which can deliver smooth streaming at 1080p HD video resolution, or wired through an Ethernet cable. Navigation is simple with the NeoTV Streaming Player remote control app, which turns an Apple&reg; iPhone&reg;, iPad&reg;, iPod&reg; Touch or Android&reg; smartphone or tablet into a high-end touch screen remote control. Available free from Apple's App Store and Google&reg; Play, the remote control app makes navigating movies, TV shows, songs and videos on NeoTV Pro simple and intuitive. For those without a smartphone, the NeoTV Pro Streaming Player comes with a regular remote control.</p>

<p>NETGEAR also announced the addition of four new streaming channels available on all NeoTV streaming players starting in June. One of the new channels, SpiritClips, provides NeoTV users with a library of wholesome and engaging Hallmark Hall of Fame&reg; feature films, available on-demand.</p>

<p>In addition to SpiritClips, examples of new content available through NeoTV include:</p>

<ul><li>Fred - Fred Figglehorn is the biggest YouTube celebrity, reaching nearly a billion video views.</li><li>And1 Basketball - And1 is an amazing alternative street basketball league that brings everything from dunking contests to some of the most amazing basketball highlights ever captured.</li><li>Formula Drift - Features some of the greatest races syndicated today. Formula Drift specializes in "drifting" and will soon be syndicated on a national TV network.</li></ul>

<p>"All 32 events of the upcoming summer Olympics in London will be streamed live on nbcolympics.com.  With the NeoTV Pro Streaming Player, and a laptop supporting Intel WiDi, consumers can watch Michael Phelps swim or Usain Bolt sprint on a big screen TV, streamed directly from the Internet," said Damir Skripic, product line manager for connected entertainment at NETGEAR. "NeoTV Pro brings a richer Internet entertainment experience to TVs. With built-in Intel WiDi and their laptop, NeoTV Pro allows consumers to access the entire Internet on their TV."</p>

<p><br />
<strong>Pricing and Availability</strong></p>

<p>The NETGEAR NeoTV Pro Streaming Player (NTV200S) is available now in the United States at $69.99 and can be purchased at major retailers and online stores. To learn more about the NETGEAR NeoTV Pro Streaming Player, please visit <a target="_blank" href="http://www.netgear.com/ntvpro/">www.netgear.com/ntvpro</a>.</p>

<p><br />
<strong>About NETGEAR, Inc.</strong></p>

<p>NETGEAR (NASDAQGM: NTGR) is a global networking company that delivers innovative products to consumers, businesses and service providers. For consumers, the company makes high performance, dependable and easy to use home networking, storage and digital media products to connect people with the Internet and their content and devices. For businesses, NETGEAR provides networking, storage and security solutions without the cost and complexity of big IT. The company also supplies top service providers with retail proven, whole home solutions for their customers. NETGEAR products are built on a variety of proven technologies such as wireless, Ethernet and Powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in approximately 29,000 retail locations around the globe, and through approximately 41,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR&reg; partner. More information is available at http://www.NETGEAR.com or by calling (408) 907-8000. Connect with NETGEAR at http://twitter.com/NETGEAR and http://www.facebook.com/NETGEAR.</p>

<p>(1) Intel&reg; WiDi requires an Intel&reg; Wireless Display enabled PC, NeoTV Pro and TV. Blu-ray&trade; disc and DVD movie playback requiring output protection is not supported over wireless display on NTV200S. 1080p resolution is available on second-generation Intel&reg; Core&trade; processor-based PCs with built-in visuals enabled. Consult your PC manufacturer. For more information, see http://www.intel.com/go/widi.</p>

<p>&copy; 2012 NETGEAR, Inc. NETGEAR and  the NETGEAR logo, are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Intel and Intel Wireless Display (WiDi) are trademarks or registered trademarks of Intel Corporation or its subsidiaries. iPad, iPhone and iPod Touch are registered trademarks of Apple Inc. Android and Google Play are trademarks of Google Inc. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p><strong>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</strong> This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning NETGEAR's business and the expected performance characteristics, specifications, reliability, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 40 through 58, in the Company's quarterly report on Form 10-Q for the fiscal quarter ended April 1, 2012, filed with the Securities and Exchange Commission on May 8, 2012. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June  8, 2012  9:45 PM</b>
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
			<?=getComments(4835)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4835)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/06/netgear-launches-neotv-pro-hd-streaming-player-with-wireless-display.php" type="text/javascript" charset="utf-8"></script>
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