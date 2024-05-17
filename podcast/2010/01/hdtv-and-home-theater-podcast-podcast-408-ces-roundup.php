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
		AND e.entry_id = 3508";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3508 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3508 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3508";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2010/01/hdtv-and-home-theater-podcast-podcast-408-ces-roundup.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3508";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #408: CES Roundup" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #408: CES Roundup" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #408: CES Roundup" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #408: CES Roundup</title>
	<meta name="keywords" content="media server,  xstreamhd media, blu ray, everyone else, same everyone, info, media, really, home, server, good,  xstreamhd, movies, hdtv, ray, samsung, plasma, didn, blu, network, showed, storage, show, dolby, ces" />
	<meta name="description" content="We decided to once again make the trek out to Las Vegas to get our own impression of all the hype coming out of CES.  We didn't go last year, and we missed it a little, so we had to go this year.  By far the biggest theme of the show this year was 3D.  Some of what we saw was really good, some was really bad and of course some was just downright strange.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=114&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #408: CES Roundup" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #408: CES Roundup" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2010/01/hdtv-and-home-theater-podcast-podcast-408-ces-roundup.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="We decided to once again make the trek out to Las Vegas to get our own impression of all the hype coming out of CES.  We didn't go last year, and we missed it a little, so we had to go this year.  By far the biggest theme of the show this year was 3D.  Some of what we saw was really good, some was really bad and of course some was just downright strange.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=114&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3508', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2010/01/hdtv-and-home-theater-podcast-podcast-408-ces-roundup.php">HDTV and Home Theater Podcast - Podcast #408: CES Roundup</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>January 14, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=444&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<div class='snap_preview'><br /><h2>Today&#8217;s Show:</h2>
<h3>CES 2010 Roundup</h3>
<p>We decided to once again make the trek out to Las Vegas to get our own impression of all the hype coming out of CES.  We didn&#8217;t go last year, and we missed it a little, so we had to go this year.  By far the biggest theme of the show this year was 3D.  Some of what we saw was really good, some was really bad and of course some was just downright strange.</p>
<p>All of the pictures are available at <a href="http://www.htguys.com/podcasts/2010/1/15/podcast-408-ces-roundup.html">HTGuys.com </a></p>
<h3>Iomega ScreenPlay Director HD Media Player <a title="http://go.iomega.com/en-us/products/multimedia-drive/screenplay153-multimedia-drives/screenplay-director/?partner=4760#overviewItem_tab" href="http://go.iomega.com/en-us/products/multimedia-drive/screenplay153-multimedia-drives/screenplay-director/?partner=4760#overviewItem_tab" target="_blank">(More Info)</a></h3>
<ol>
<li>Watch HD movies stored on internal/external drives or on your local network</li>
<li>Buy or rent movies from Cinema now</li>
<li>MPEG-1; MPEG-2 (AVI/VOB); MPEG-4 (AVI/DivX® /XViD); H.264; WMV; ACVCHD</li>
<li>No built in Wi-Fi. Adapter required</li>
<li>HDMI</li>
<li>One TB model goes for $250</li>
</ol>
<h3>XstreamHD <a title="http://www.xstreamhd.com/" href="http://www.xstreamhd.com/" target="_blank">(More Info)</a></h3>
<div>
<p>The <em>XStreamHD</em> whole home entertainment solution is powered by one <em>XStreamHD</em> Media Server connected directly to your home network. The <em>XStreamHD</em> Media Server collects, stores and organizes your pre-fetched movies, music and games in your Virtual Personal Library.</p>
<p>As a whole-home solution, the <em>XStreamHD</em> Media Server can deliver multiple streams of content throughout your home to any television equipped with a compact <em>XStreamHD</em> Media Receiver or any DLNA Certified™ device – from computers to next-generation television and game consoles.</p>
<p>The HD Media Server is loaded with up to 4 TB of removable internal storage with additional access to external storage via an eSata connection. Further, the <em>XStreamHD</em> Media Server includes a built-in Network Video Recorder that uses three HDTV (ATSC) tuners to capture all of your favorite broadcast HDTV programs from start-to-finish.</p>
</div>
<div>
<ol>
<li>XStreamHD Fast Start  MSRP $399 XStreamHD Pro Start $499</li>
<li>Using a small outdoor antenna, multiple streams of studio master quality HD entertainment are delivered direct-to-home via satellite to your <em>XStreamHD</em> Media Server</li>
<li>Simultaneously access multiple streams of Full HD content throughout your home from one HD Media Server</li>
<li>Three HDTV (ATSC) tuners and a Network Video Recorder allow you to simultaneously record up to three broadcast HDTV programs.</li>
<li>Up to 4TB of removable internal storage with additional access to external storage via an eSATA connection.</li>
</ol>
</div>
<h3>Boxee <a title="http://www.boxee.tv/" href="http://www.boxee.tv/" target="_blank">(More Info)</a></h3>
<div>
<ol>
<li>Watch thousands of popular TV shows and movies for Free</li>
<li>Very cool RF remote with QWERTY keyboard on back side</li>
<li>SD card Slot</li>
<li>Wi-Fi built in</li>
<li>Facebook and Twitter support</li>
</ol>
</div>
<h3>JVC <a title="http://www.jvc.com/" href="http://www.jvc.com/" target="_blank">(More Info)</a></h3>
<ol>
<li>Real-Time 2D &#8211; 3D Conversion
<ul>
<li>Interesting concept, and it kinda worked, but not perfect</li>
</ul>
</li>
<li>Thin LEDs</li>
<li>3D LCoS projectors
<ul>
<li>We opted not to wait in line, but were told that the demo was amazing</li>
</ul>
<ul>
<li>JVC makes great projectors, from a 2D perspective we know they&#8217;re great</li>
</ul>
<ul>
<li>We&#8217;d assume their 3D is the same as everyone else&#8217;s</li>
</ul>
</li>
</ol>
<h3>LG <a title="http://www.lge.com/us/index.jsp" href="http://www.lge.com/us/index.jsp" target="_blank">(More Info)</a></h3>
<div>
<ol>
<li>In years past, LG has pushed the envelope with really big screens, this year they had really thin ones</li>
<li>They showed an &#8220;Ultra Slim LCD&#8221; &#8211; very nice
<ul>
<li>6.9mm</li>
</ul>
<ul>
<li>local dimming</li>
</ul>
<ul>
<li>LED backlit</li>
</ul>
</li>
<li>Magic TV remote
<ul>
<li>Uses gyros and accelerometers like a Wii remote</li>
</ul>
<ul>
<li>Cool idea, but a little more of a gimmick than a mind blower</li>
</ul>
</li>
<li>3D
<ul>
<li>LCD was the same as everyone else.  Not good, not bad</li>
</ul>
<ul>
<li>Ara really liked the 3D plasma.  It gave Braden headaches.</li>
</ul>
</li>
</ol>
</div>
<h3>Panasonic <a title="http://www.panasonic.com/" href="http://www.panasonic.com/" target="_blank">(More Info)</a></h3>
<div>
<ol>
<li>152-inch 4K x 2K Quad HD 3D plasma display
<ul>
<li>Thing was huge</li>
</ul>
<ul>
<li>It looked good, but the content didn&#8217;t really show it off</li>
</ul>
<ul>
<li>We didn&#8217;t see any 3D on it, but supposedly it could do it</li>
</ul>
<ul>
<li>No pricing or availability &#8230; but they can get you a 103&#8243; unit right away</li>
</ul>
</li>
<li>Panasonic&#8217;s 3D plasma won best of CES 2010
<ul>
<li>Ara really liked how plasma looked in 3D</li>
</ul>
<ul>
<li>Braden thought it was the most natural looking, but it gave him headaches</li>
</ul>
<ul>
<li>Used active glasses, which we all know are expensive</li>
</ul>
</li>
<li>Showed what they claimed was the world&#8217;s first Full HD 3D Blu-ray player with WiFi</li>
<li>Showed a portable Blu-ray player</li>
</ol>
</div>
<h3>Samsung <a title="http://www.samsung.com/us/" href="http://www.samsung.com/us/" target="_blank">(More Info)</a></h3>
<ol>
<li>Were not allowed to take pictures. These two were taken before we were admonished</li>
<li>Samsung Plasma:
<ul>
<li>3D compatible</li>
</ul>
<ul>
<li>2D-to-3D conversion system</li>
</ul>
<ul>
<li>slim 1.4-inch deep panel</li>
</ul>
<ul>
<li>Interactive capability with Samsung Internet @ TV and Samsung Apps</li>
</ul>
</li>
<li>OLED 3D &#8211; Did not look very good</li>
</ol>
<h3>Toshiba <a title="http://www.tacp.toshiba.com/televisions/" href="http://www.tacp.toshiba.com/televisions/" target="_blank">(More Info)</a></h3>
<ol>
<li>Cell TV &#8211; supposed to be the most powerful TV ever
<ul>
<li>Cell processor can upconvert 2D to 3D, we didn&#8217;t see it</li>
</ul>
<ul>
<li>TV can rip Blu-ray and DVD movies &#8211; pretty cool</li>
</ul>
<ul>
<li>Supports IPTV &#8211; not Skype</li>
</ul>
<ul>
<li>Showed a split screen of HD and HD upconverted to 4K &#8211; we weren&#8217;t blown away</li>
</ul>
<ul>
<li>It could eventually become self away</li>
</ul>
</li>
<li>Big focus on &#8220;green&#8221; technology</li>
<li>No focus on Bu-ray</li>
</ol>
<h3>Sharp <a title="http://www.sharpusa.com/" href="http://www.sharpusa.com/" target="_blank">(More Info)</a></h3>
<ol>
<li>Quad pixel technology
<ul>
<li>Big theme was the color yellow &#8211; Coldplay did not perform as far as we know</li>
</ul>
<ul>
<li>TVs looked good, but not mindblowing</li>
</ul>
<ul>
<li>Unlike most things at the show, they should ship in 2010</li>
</ul>
</li>
<li>Had a bunch of &#8220;connected&#8221; Blu-ray players
<ul>
<li>Netflix (of course)</li>
</ul>
<ul>
<li>Vudu</li>
</ul>
<ul>
<li>Pandora</li>
</ul>
<ul>
<li>Special Aquos Pure mode knows the player is connected to an Aquos HDTV and makes movies look better (uses deep color)</li>
</ul>
</li>
</ol>
<h3>Dolby <a title="http://www.dolby.com" href="http://www.dolby.com/" target="_blank">(More Info)</a></h3>
<ol>
<li>Dolby Volume</li>
<li>Dolby Mobile</li>
<li>Dolby Pro Logic IIz</li>
</ol>
<h3>SONY <a title="http://www.sony.com" href="http://www.sony.com/" target="_blank">(More Info)</a></h3>
<ol>
<li>3D OLED
<ul>
<li>Not good, but not as bad as Samsung</li>
</ul>
</li>
<li>3D LCD
<ul>
<li>Pretty much the same as everyone else&#8217;s</li>
</ul>
</li>
</ol>
<h3>The only analog set we saw &#8230;</h3>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2010-01-15.mp3">Download Episode #408</a></p>
  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>January 14, 2010 11:37 PM</b>
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
			<?=getComments(3508)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3508)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2010/01/hdtv-and-home-theater-podcast-podcast-408-ces-roundup.php" type="text/javascript" charset="utf-8"></script>
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