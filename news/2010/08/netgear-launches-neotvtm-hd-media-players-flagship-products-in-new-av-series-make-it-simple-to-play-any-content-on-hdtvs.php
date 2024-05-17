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
		AND e.entry_id = 3931";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3931 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3931 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3931";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/08/netgear-launches-neotvtm-hd-media-players-flagship-products-in-new-av-series-make-it-simple-to-play-any-content-on-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3931";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NETGEAR&reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NETGEAR&reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="NETGEAR&reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs" />
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
	<title>HDTV Magazine - NETGEAR&reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs</title>
	<meta name="keywords" content="media players, blu ray, netgear inc, home theater, game consoles, netgear, neotv, internet, products, players, home, media, hdtvs, series, blu, ray, network, devices, wireless, connect, users, new, play, content, theater" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/netgear-neotv.jpg&quot; alt=&quot;NETGEAR NeoTV HD Media Player&quot; height=&quot;24&quot; width=&quot;72&quot; style=&quot;padding:0 5px 5px 0; float:left&quot;&gt;NETGEAR&amp;reg;, Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative networking solutions for homes, businesses and service providers, today announced the NeoTV 350 HD and NeoTV 550 Ultimate HD Media Players as the flagship products in the NETGEAR AV Series. The groundbreaking NeoTV HD Media Players enable users to play their digital videos, photos, or music directly on their HDTVs whether the media is stored locally, on the home network, or the Internet. The AV Series is made up of easy-to-use products that help consumers easily connect their Internet-ready devices such as HDTVs, Blu-ray(TM) players, IPTV set-top boxes, media players and game consoles to the Internet and the home network.

The NeoTV 350 HD and NeoTV 550 Ultimate HD Media Players, available in..." />
	<meta name="title" content="NETGEAR&amp;reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="NETGEAR&amp;reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/08/netgear-launches-neotvtm-hd-media-players-flagship-products-in-new-av-series-make-it-simple-to-play-any-content-on-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/netgear-neotv.jpg&quot; alt=&quot;NETGEAR NeoTV HD Media Player&quot; height=&quot;24&quot; width=&quot;72&quot; style=&quot;padding:0 5px 5px 0; float:left&quot;&gt;NETGEAR&amp;reg;, Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative networking solutions for homes, businesses and service providers, today announced the NeoTV 350 HD and NeoTV 550 Ultimate HD Media Players as the flagship products in the NETGEAR AV Series. The groundbreaking NeoTV HD Media Players enable users to play their digital videos, photos, or music directly on their HDTVs whether the media is stored locally, on the home network, or the Internet. The AV Series is made up of easy-to-use products that help consumers easily connect their Internet-ready devices such as HDTVs, Blu-ray(TM) players, IPTV set-top boxes, media players and game consoles to the Internet and the home network.

The NeoTV 350 HD and NeoTV 550 Ultimate HD Media Players, available in..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3931', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/08/netgear-launches-neotvtm-hd-media-players-flagship-products-in-new-av-series-make-it-simple-to-play-any-content-on-hdtvs.php">NETGEAR&reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 31, 2010</b>
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
				<p class="prtitle">NETGEAR&reg; Launches NeoTV(TM) HD Media Players - Flagship Products in New AV Series Make It Simple to Play Any Content on HDTVs</p>

<p><img src="http://www.hdtvmagazine.us/news/images/netgear-neotv.jpg" alt="NETGEAR NeoTV HD Media Player" height="48" width="144" class="keyimg"><strong>SAN JOSE, Calif., Aug. 31 /PRNewswire-FirstCall/ -- </strong>NETGEAR&reg;, Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative networking solutions for homes, businesses and service providers, today announced the NeoTV 350 HD and NeoTV 550 Ultimate HD Media Players as the flagship products in the NETGEAR AV Series. The groundbreaking NeoTV HD Media Players enable users to play their digital videos, photos, or music directly on their HDTVs whether the media is stored locally, on the home network, or the Internet. The AV Series is made up of easy-to-use products that help consumers easily connect their Internet-ready devices such as HDTVs, Blu-ray(TM) players, IPTV set-top boxes, media players and game consoles to the Internet and the home network.</p>

<p>"Our research has found that the biggest catalyst driving Web-connected consumer electronics products is access to premium content such as online video and music as well as personal content such as photos," said Kurt Scherf, vice president and principal analyst with Parks Associates. "NETGEAR's latest offerings capitalize on this demand, and offer a flexible approach in how consumers choose to create a Web-enabled living room by providing either a stand-alone option, such as the NeoTV(TM) Media Players, or options to enable Web-capable consumer electronics such as televisions and Blu-ray players to be easily broadband-connected."</p>

<p><br />
<strong>New NETGEAR NeoTV HD Media Players</strong></p>

<p>The NeoTV 350 HD and NeoTV 550 Ultimate HD Media Players, available in October, enable users to play their digital videos, photos, or music directly on their HDTVs and offer the most complete access to media collections, whether stored on USB storage devices, hard drives, memory cards, home media servers, home networks or on the Internet. Both models support HD 1080p, Dolby Digital and DTS surround sound. The NeoTV 550 offers an additional E-SATA port for faster transfer speeds, Blu-ray disc support via external drives and advanced metadata tagging that lets users browse cover art.</p>

<p>Other NeoTV features include a built-in memory card slot for instant photo slideshows on the TV, DLNA/UPnP compatibility for access to media servers, network share connectivity and an optional wireless connection with the AV Series NETGEAR Universal Wi-Fi Internet Adapter (WNCE2001).</p>

<p><br />
<strong>NeoTV Pricing and Availability</strong></p>

<p>The NeoTV 350 HD Media Player and NeoTV 550 Ultimate HD Media Player will be available in the fall 2010. The NeoTV 350 will be available in Europe with an MSRP of Euro 129.99 and Australia with an MSRP of AUD $189.99. The NeoTV 550 will be available in North America with an MSRP of $219.99, Europe for Euro 199.99 and Australia for AUD $299.99.</p>

<p><br />
<strong>The NETGEAR AV Series Connected Entertainment Line</strong></p>

<p>The new NETGEAR AV Series Line, which includes solutions for powerline, wired and wireless environments, is the first line of home networking products specifically designed to meet the user needs, performance, and reliability requirements for connecting disparate parts of the home theater and other digital entertainment devices with the Internet. The line enables consumers to easily activate the Internet-ready features that come with today's HDTVs, Blu-ray players, media players and game consoles.</p>

<p>"Home theater devices like Blu-ray players, HDTVs, and game consoles now have the ability to connect to the Internet with the intention of giving consumers a richer entertainment experience," said Vivek Pathela, VP and GM for Home/Consumer Products at NETGEAR. "The problem is that most people don't know how to connect them and those who do often experience frustrating set-up and connection issues, video jitters, frame drops, screen freezes and audio/video sync issues. NETGEAR has been addressing these problems for many years, and our AV Series products are designed to deliver superior results for consumers and their connected entertainment needs."</p>

<p>Easy to find online and in worldwide retail stores with the NETGEAR AV Series logo, consumers can now:<br />
<ul><li>Play any content on HDTVs with the NETGEAR NeoTV 350 HD and NeoTV 550Ultimate HD Media Players (NTV350 and NTV550). Users can play digitalvideos, photos, or music directly on their HDTVs whether the media isstored locally, on the home network, or the Internet.</li><li>Project a laptop screen to an HDTV with the NETGEAR Push2TV(TM)Adapter for Intel&reg; Wireless Display (PTV1000). Everything on anotebook PC, such as Internet video, can be viewed on a big screen TVwirelessly. Intel recently announced this technology is available with25 new computer models. To learn more, visit <a target="_blank" href="http://www.netgear.com/ptv/">www.netgear.com/ptv</a></li><li>Connect up to four home theater devices to the Internet wirelesslywith the NETGEAR 3DHD Wireless Home Theater Networking Kit (WNHDB3004)and via powerline with the NETGEAR Home Theater Internet ConnectionKit (XAVB1004). Users can easily connect and stream 3DHD and HDcontent onto their HDTVs, Blu-ray(TM) players, DVRs, game consoles,TiVo&reg;, Slingbox(TM), or computers throughout the house.</li><li>Connect Internet-Ready HDTVs and Blu-ray players to the Internet withthe NETGEAR Universal WiFi Internet Adapter (WNCE2001). It wirelesslyconnects networked home theater devices to home networks and isuniversal so it works with any connected entertainment device.</li><li>Connect the Xbox360 to the Internet with the NETGEAR Xbox 360 InternetConnection Kit (XETB10GM) and play online games on Xbox&reg; Live&reg; or PS2,PS3 and PCs.</li><li>Create a wireless network for video and gaming with the NETGEARWireless Router for Video and Gaming (WNDR37AV). It creates a wirelessInternet connection for gaming consoles, Blu-ray(TM) players, HDTVs,and TiVo&reg;/DVRs as well as wireless connectivity for other homenetworked devices like laptops and wireless printers.</li><li>Add a switch to connect multiple devices with the NETGEAR Home Theaterand Gaming Network Switch (GS605AV) and easily connect networked TV,TiVo&reg; DVR, Blu-ray(TM) player, game console and cable/satelliteset-top boxes to home networks and the Internet.</li></ul></p>

<p>For more product details, pricing, availability and how-to tutorials for the AV Series products, please visit: <a target="_blank" href="http://www.netgear.com/avseries/">www.netgear.com/avseries</a></p>

<p>For more information on NETGEAR's new NeoTV products, visit <a target="_blank" href="http://www.netgear.com/avseries/">www.netgear.com/avseries</a> or <a target="_blank" href="http://www.netgear.com/NeoTV/">www.netgear.com/NeoTV</a></p>

<p><br />
<strong>About NETGEAR, Inc.</strong></p>

<p>NETGEAR (NASDAQ:NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of Small- to Medium-sized Businesses (SMBs) and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in over 27,000 retail locations around the globe, and via more than 36,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR&reg; partner. More information is available at http://www.netgear.com/ or by calling (408) 907-8000. Connect with NETGEAR at http://twitter.com/NETGEAR and http://www.facebook.com/NETGEAR.</p>

<p>&copy;2010 NETGEAR, Inc. NETGEAR, the NETGEAR logo, NeoTV and Push2TV are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Wi-Fi is a trademark of the Wi-Fi Alliance. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Maximum wireless signal rate derived from IEEE Standard 802.11 specifications. Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning NETGEAR's business and the expected performance characteristics, specifications, reliability, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 36 through 52, in the Company's quarterly report on Form 10-Q for the fiscal second quarter ended June 27, 2010, filed with the Securities and Exchange Commission on August 5, 2010. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>

<p>Source: NETGEAR, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 31, 2010  5:05 PM</b>
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
			<?=getComments(3931)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3931)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/08/netgear-launches-neotvtm-hd-media-players-flagship-products-in-new-av-series-make-it-simple-to-play-any-content-on-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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