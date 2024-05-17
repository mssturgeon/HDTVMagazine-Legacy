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
		AND e.entry_id = 4853";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4853 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4853 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4853";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/06/vizio-launches-the-vizio-costar-with-google-tv-giving-users-the-power-to-turn-any-hdtv-into-the-ultimate-smart-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4853";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Launches The VIZIO Co-Star&trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Launches The VIZIO Co-Star&trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Launches The VIZIO Co-Star&trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV" />
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
	<title>HDTV Magazine - VIZIO Launches The VIZIO Co-Star&trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV</title>
	<meta name="keywords" content="stream player, any hdtv, lcd hdtv, ultimate smart, best ces, star, onlive, hdtv, entertainment, users, best, google, smart, experience, easy, any, player, power, access, video, game, stream, full, company, apps" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/vizio-co-star.jpeg&quot; alt=&quot;VIZIO Co-Star&quot; height=&quot;125&quot; width=&quot;125&quot; style=&quot;float:left; padding: 0 5px 5px 0&quot;&gt;&lt;strong&gt;IRVINE, Calif., June 26, 2012 /PRNewswire/&lt;/strong&gt; -- VIZIO, America's #1 LCD HDTV Company*, announced today the launch of the highly anticipated VIZIO Co-Star&amp;trade; Stream Player. Using the latest version of Google TV&amp;trade;, the VIZIO Co-Star makes it easy to transform any HDTV into the ultimate smart TV, merging live and streaming entertainment into one intuitive and easy experience. With VIZIO's new stream player, users get instant access to thousands of apps, full-screen web browsing capabilities—via Google Chrome&amp;trade; with Adobe&amp;reg; Flash&amp;reg; Player and HTML 5 support—and the best in streaming entertainment.

Unique to the stream player market, the VIZIO Co-Star offers..." />
	<meta name="title" content="VIZIO Launches The VIZIO Co-Star&amp;trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Launches The VIZIO Co-Star&amp;trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/06/vizio-launches-the-vizio-costar-with-google-tv-giving-users-the-power-to-turn-any-hdtv-into-the-ultimate-smart-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/vizio-co-star.jpeg&quot; alt=&quot;VIZIO Co-Star&quot; height=&quot;125&quot; width=&quot;125&quot; style=&quot;float:left; padding: 0 5px 5px 0&quot;&gt;&lt;strong&gt;IRVINE, Calif., June 26, 2012 /PRNewswire/&lt;/strong&gt; -- VIZIO, America's #1 LCD HDTV Company*, announced today the launch of the highly anticipated VIZIO Co-Star&amp;trade; Stream Player. Using the latest version of Google TV&amp;trade;, the VIZIO Co-Star makes it easy to transform any HDTV into the ultimate smart TV, merging live and streaming entertainment into one intuitive and easy experience. With VIZIO's new stream player, users get instant access to thousands of apps, full-screen web browsing capabilities—via Google Chrome&amp;trade; with Adobe&amp;reg; Flash&amp;reg; Player and HTML 5 support—and the best in streaming entertainment.

Unique to the stream player market, the VIZIO Co-Star offers..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4853', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/06/vizio-launches-the-vizio-costar-with-google-tv-giving-users-the-power-to-turn-any-hdtv-into-the-ultimate-smart-tv.php">VIZIO Launches The VIZIO Co-Star&trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 26, 2012</b>
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
				<p class="prtitle">VIZIO Launches The VIZIO Co-Star&trade; With Google TV Giving Users The Power To Turn Any HDTV Into The Ultimate Smart TV</p>

<p><img src="http://www.hdtvmagazine.com/news/images/vizio-co-star.jpeg" alt="VIZIO Co-Star" height="250" width="250" class="keyimg"><strong>IRVINE, Calif., June 26, 2012 /PRNewswire/</strong> -- VIZIO, America's #1 LCD HDTV Company*, announced today the launch of the highly anticipated VIZIO Co-Star&trade; Stream Player. Using the latest version of Google TV&trade;, the VIZIO Co-Star makes it easy to transform any HDTV into the ultimate smart TV, merging live and streaming entertainment into one intuitive and easy experience. With VIZIO's new stream player, users get instant access to thousands of apps, full-screen web browsing capabilities—via Google Chrome&trade; with Adobe&reg; Flash&reg; Player and HTML 5 support—and the best in streaming entertainment.</p>

<p>Unique to the stream player market, the VIZIO Co-Star offers the distinct advantage of connecting to a cable or satellite box. This allows viewers to enjoy live TV along with the web, apps, OnLive cloud gaming, and other streaming entertainment without interrupting what they're already watching. This integrated experience along with the universal touchpad remote with keyboard eliminates the need to switch TV inputs or remotes to access alternative content and provides viewers with a truly smart TV experience.</p>

<p>"Our focus to deliver the best consumer experience continues with today's announcement of the Co-Star, which delivers a superior smart TV interface that anyone can add to their existing HDTV," said Matt McRae, VIZIO's Chief Technology Officer. "We combined the powerful features of Google TV&trade; with an intuitive and easy to use interface, giving users the power to enjoy an entire world of entertainment."</p>

<p>The added benefit of Google TV's powerful search feature lets viewers easily browse websites, hunt for streaming movies or locate their favorite TV shows; and with picture-in-picture technology, it can all be done while simultaneously watching live TV action.</p>

<p>In addition to favorite apps such as Netflix&reg;, Amazon Instant Video, M-GO, YouTube&reg; and iHeartRadio, the VIZIO Co-Star is the first stream player to offer video games on demand through the OnLive&reg; Game Service.  OnLive allows users to demo, watch and play hundreds of top-tier video games directly from 'the cloud' via their VIZIO Co-Star, eliminating the need for users to own an independent game console. With easy access to thousands of apps through Google Play, Co-Star offers the most comprehensive stream player available today.</p>

<p>Complementing the overall experience, the VIZIO Co-Star includes a universal Bluetooth&reg; remote with an innovative touchpad that lets you touch, tap, scroll and drag. A full QWERTY keyboard helps users search for their favorite entertainment.  Plus, its universal feature makes it easy to control home entertainment devices with one remote.</p>

<p>The VIZIO Co-Star incorporates a number of power user features intended to satisfy the variety of user demands, starting with support for 1080p Full HD and 3D entertainment. Built-in 802.11n Wi-Fi provides easy wireless Internet access without unsightly wires. Integrated USB port connects to hard drives, keyboards and other peripherals. The VIZIO Co-Star even enables viewers to enjoy photos, music, and movies from any DLNA-enabled phone, tablet, or computer on the TV screen.</p>

<p>The VIZIO Co-Star exceeds expectations by combining a definitive user experience with a powerful smart TV platform, resulting in an all-in-one solution that brings the ultimate smart TV experience to any HDTV.</p>

<p>The VIZIO CO-Star will be available for pre-orders in July 2012 only on VIZIO.com for $99.99, with an introductory free shipping offer, while supplies last.</p>

<p>*IHS iSuppli Corporation Research Q2 2012 Market Tracker Report of Q1 2012.</p>

<p>**High-speed/broadband Internet service and access equipment are required and not provided by VIZIO.  Additional fees and/or subscriptions may be required for certain content and services.  VIZIO makes no warranties, representations, or assurances of any kind as to the content, availability, or functionality of third party content or services.</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc., headquartered in Irvine, California, is America's #1 LCD HDTV Company*.  In Q2 2007, VIZIO skyrocketed to the top by becoming the #1 shipping brand of flat panel HDTVs in North America and in Q3 2007 became the first American brand in over a decade to lead in U.S. LCD HDTV shipments. Since 2007 VIZIO LCD HDTV shipments remain in the top ranks in the U.S. and were #1 for the total year in 2009 and 2010.  VIZIO is committed to bringing feature-rich consumer electronics to market at a value through practical innovation. VIZIO offers a broad range of award winning consumer electronics.  VIZIO's products are found at Costco Wholesale, Sam's Club, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners.  VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Fast Company's 6th Most Innovative CE Company of 2009, and made the lists of Ad Age's Hottest Brands, CNET's Editor's Choice, CNET Best of CES 2011 - Television, IGN Best of CES - Television, Bluetooth.org Best of CES, Good Housekeeping's Best Big-Screens, PC World's Best Buy, Popular Mechanics Editor's Choice and OC Metro's 10 Most Trustworthy Brands among many other prestigious honors.  For more information, please call 888-VIZIOCE or visit on the web at <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>The V, VIZIO, VIZIO Internet Apps, Theater 3D, CinemaWide HDTV, Full Array TruLED, Edge Lit Razor LED, 240Hz SPS, 480Hz SPS, Entertainment Freedom and Entertainment Freedom for All names, logos and phrase are registered or unregistered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p><br />
<strong>About OnLive</strong></p>

<p>OnLive is the pioneer of on-demand, instant-action cloud computing and instant-play video game services, delivering real-time interactive experiences and rich media through the Internet. With groundbreaking video compression technology, OnLive harnesses cloud computing to provide the power and intelligence needed to instantly deliver full-featured, media-rich applications and the latest, premium game titles to tablets, smartphones, PCs, Macs and HDTVs via the OnLive&reg; Game System or connected TVs. OnLive is available in North America and the UK and will continue expanding into Europe and Asia. OnLive's technology is backed by hundreds of patents and patents pending. The company is headquartered in Palo Alto, California. OnLive investors include Warner Bros., Autodesk, Maverick Capital, AT&T, British Telecommunications (BT), The Belgacom Group, HTC and Juniper Networks. More information is available at <a target="_blank" href="http://www.onlive.com/">www.onlive.com</a> and <a target="_blank" href="http://www.onlive.co.uk/">www.onlive.co.uk</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 26, 2012  7:45 PM</b>
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
			<?=getComments(4853)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4853)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/06/vizio-launches-the-vizio-costar-with-google-tv-giving-users-the-power-to-turn-any-hdtv-into-the-ultimate-smart-tv.php" type="text/javascript" charset="utf-8"></script>
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