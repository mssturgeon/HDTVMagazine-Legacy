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
		AND e.entry_id = 774";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 774 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 774 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 774";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 774";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" />
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
	<title>HDTV Magazine - Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel</title>
	<meta name="keywords" content="unit share, north american, flat panel, lcd tvs, table preliminary, growth, lcd, share, sharp, volume, unit, panel, north, samsung, tvs, led, total, plasma, rose, panasonic, sony, market, table, vizio, american" />
	<meta name="description" content="AUSTIN, TEXAS, November 1, 2007--The North American TV brand sell-in rankings were shaken up again in Q3'07 with Sharp on top in LCD TVs for the first time since Q1'05. As shown in Table 1, Sharp led the North American LCD TV market with an 11.3% share, rising from #3 in Q2'07, on 65% Q/Q and 88% Y/Y growth. Its strong growth can be attributed to:" />
	<meta name="title" content="Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="AUSTIN, TEXAS, November 1, 2007--The North American TV brand sell-in rankings were shaken up again in Q3'07 with Sharp on top in LCD TVs for the first time since Q1'05. As shown in Table 1, Sharp led the North American LCD TV market with an 11.3% share, rising from #3 in Q2'07, on 65% Q/Q and 88% Y/Y growth. Its strong growth can be attributed to:" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=774', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php">Sharp and Samsung Reclaim Top Positions in LCD and Flat Panel</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>November  1, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=371&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=367&category=LCD HDTVs">LCD HDTVs</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p><html></p>

<p><head><br />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><br />
<title>New Page 1</title><br />
</head></p>

<p><body></p>

<div>
	<strong><font color="#000000" size="2">Sharp and Samsung Reclaim Top 
	Positions in LCD and Flat Panel TVs According to DisplaySearch; Vizio Falls 
	to #2 in Each Category</font></strong></div>
<div>
	&nbsp;</div>
<div>
	<span class="style1"><font color="#000000" size="2"><strong>AUSTIN, TEXAS, 
	November 1, 2007--</strong>The North American TV brand sell-in rankings were 
	shaken up again in Q3'07 with Sharp on top in LCD TVs for the first time 
	since Q1'05. As shown in Table 1, Sharp led the North American LCD TV market 
	with an 11.3% share, rising from #3 in Q2'07, on 65% Q/Q and 88% Y/Y growth. 
	Its strong growth can be attributed to </font></span>
	<ul class="style1">
		<li><font color="#000000" size="2">Rapidly growing internal panel 
		capacity--Sharp had the fastest sequential TFT LCD supply growth of any 
		panel supplier, up 36% Q/Q, as it continues to ramp its 8G fab. </font>
		</li>
		<li><font color="#000000" size="2">Growing LCD TV focus--Sharp's 
		worldwide LCD TV panel shipments rose from 56% to 64% of its total 
		large-area TFT LCD volume, taking share from notebook PCs. </font></li>
		<li><font color="#000000" size="2">Significant emphasis on smaller sizes 
		where demand is strong and supply is tight--Sharp's &lt;32&quot; volume rose 77% 
		Q/Q, rising to 40% of its Q3'07 volume. Sharp was #1 in 19&quot; and 26&quot; and 
		also led at 52&quot;. </font></li>
		<li><font color="#000000" size="2">Increased emphasis on the North 
		American LCD TV market--North America rose from 24% to 34% of Sharp's 
		worldwide Q3'07 LCD TV volume. </font></li>
	</ul>
	<p class="style1"><strong><font color="#000000" size="2">Table 1: 
	Preliminary Q2'07 - Q3'07 North American LCD TV Unit Share and Growth</font></strong></p>
	<table border="1" cellpadding="0" cellspacing="0" width="403" id="table1">
		<tr align="center" bgcolor="#333333">
			<td class="style1" valign="bottom" width="73"><b>
			<font color="#000000" size="2">
			<p align="center">Rank</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73"><b>
			<font color="#000000" size="2">Brand</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q2'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q3'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q/Q<br>
			Growth</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Y/Y<br>
			Growth</font></b></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">1</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Sharp</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">65%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">88%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">2</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Vizio</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">12.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.9%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">19%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">334%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">3</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Samsung</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">33%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">79%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">4</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Sony</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">6.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">108%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">84%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center"><font color="#000000" size="2">5</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Funai</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.9%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">8.1%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">39%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">20%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="73">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Other</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">53.5%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">49.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">24%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">73%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="73">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="73">
			<font color="#000000" size="2">Total</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">34%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">82%</font></td>
		</tr>
	</table>
	<p class="style1"><font color="#000000" size="2">Other LCD TV highlights 
	include</font></p>
	<ul class="style1">
		<li><font color="#000000" size="2">Vizio fell to #2 despite 334% Y/Y 
		growth; it had the slowest Q/Q growth of the top five. Its slower Q3'07 
		sequential growth can be explained by the less seasonal nature of its 
		primary sales channel: the warehouse club channel. Nonetheless, it was 
		#1 in 32&quot; and larger volume and in LCD HDTV volume. It also led the 32&quot;, 
		37&quot; and 42&quot; markets. </font></li>
		<li><font color="#000000" size="2">Samsung fell from #2 to #3 despite 
		33% Q/Q and 79% Y/Y growth. It was #2 in 40&quot;+ volume. </font></li>
		<li><font color="#000000" size="2">Sony had the fastest Q/Q growth of 
		the top five brands up 108% Q/Q and 84% Y/Y, as it rolled out a number 
		of compelling new products later this year than last year, while also 
		targeting mass merchants like Wal-Mart through the new M series. As a 
		result, Sony's unit ranking rose from #7 to #4, and it jumped from #3 to 
		#1 in revenues. Sony had the highest focus on 40&quot; and larger LCD TVs 
		which accounted for 67% of its volume. It led at 40- 42&quot; and 46-47&quot;.
		</font></li>
		<li><font color="#000000" size="2">Funai fell from #4 to #5 in units and 
		led at 15&quot; and 20&quot;. </font></li>
		<li><font color="#000000" size="2">Preliminary totals for LCD TVs rose 
		34% Q/Q and 82% Y/Y to a new record high of 6.6M units. LCD TVs rose to 
		88% of flat panel TV volume vs. 78% in Q3'06. </font></li>
	</ul>
	<p class="style1"><font color="#000000" size="2">In plasma TVs, Panasonic 
	continued to lead, as shown in Table 2. Panasonic earned a 30% share on 11% 
	Q/Q growth. However, on a Y/Y basis, Panasonic's volume was down 28% after 
	it shipped an excessive number of plasma TVs into North America a year ago 
	when the European market stalled after the World Cup. With Panasonic down, 
	the total plasma TV market was down 17% Y/Y to a preliminary total of 866K 
	units. Other highlights include</font></p>
	<ul class="style1">
		<li><font color="#000000" size="2">Samsung and LGE each gained share on 
		significant growth at larger sizes and also enjoyed success with new 
		1080p products.&nbsp; </font></li>
		<li><font color="#000000" size="2">Vizio fell from #4 to #7 as it exited 
		the 42&quot; plasma market to focus on 42&quot; LCD. </font></li>
		<li><font color="#000000" size="2">Hitachi enjoyed the fastest Q/Q 
		growth on more than a 100% increase in 50&quot;, rising from #6 to #4. </font>
		</li>
		<li><font color="#000000" size="2">Panasonic led at 42&quot;, 50&quot; and 55-59&quot; 
		and in all 1080p products. LGE led at 60&quot;+. </font></li>
	</ul>
	<p class="style1"><strong><font color="#000000" size="2">Table 2: 
	Preliminary Q2'07 - Q3'07 North American Plasma TV Unit Share and Growth</font></strong></p>
	<table border="1" cellpadding="0" cellspacing="0" width="409" id="table2">
		<tr align="center" bgcolor="#333333">
			<td class="style1" valign="bottom" width="77"><b>
			<font color="#000000" size="2">
			<p align="center">Rank</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77"><b>
			<font color="#000000" size="2">Brand</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q2'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><b><font color="#000000" size="2">Q3'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q/Q<br>
			Growth</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Y/Y<br>
			Growth</font></b></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">1</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Panasonic</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">32.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">30.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-28%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">2</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Samsung</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">15.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">19.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">53%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">5%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">3</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">LGE</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.6%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">13.7%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">56%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">23%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">4</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Hitachi </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.1% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.6% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">64% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">28% </font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center"><font color="#000000" size="2">5</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Philips </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">9.3% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.4% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-3% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-41% </font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="77">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Other </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">24.5% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">19.5% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-4% </font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-33% </font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="77">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="77">
			<font color="#000000" size="2">Total</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">21%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">-17%</font></td>
		</tr>
	</table>
	<p class="style1"><font color="#000000" size="2">Based on the total LCD and 
	plasma volume, the flat panel TV rankings are shown in Table 3. As 
	indicated, Samsung overtook Vizio to earn the #1 position due to strong 
	growth in both LCD and plasma TVs. Sharp, Sony and Funai remained at #3 - #5 
	with each gaining share. </font></p>
	<p class="style1"><strong><font color="#000000" size="2">Table 3: 
	Preliminary Q2'07 - Q3'07 North American Flat Panel TV Unit Share and Growth</font></strong></p>
	<table border="1" cellpadding="0" cellspacing="0" width="397" id="table3">
		<tr align="center" bgcolor="#333333">
			<td class="style1" valign="bottom" width="71"><b>
			<font color="#000000" size="2">
			<p align="center">Rank</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71"><b>
			<font color="#000000" size="2">Brand</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q2'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q3'07<br>
			Unit Share</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Q/Q<br>
			Growth</font></b></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64"><b>
			<font color="#000000" size="2">
			<p align="center">Y/Y<br>
			Growth</font></b></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">1</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Samsung</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11.4%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">11.8%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">37%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">57%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">2</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Vizio</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">12.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">12%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">297%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">3</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Sharp</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">8.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">10.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">65%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">88%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">4</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Sony</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">5.5%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">8.6%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">108%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">84%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center"><font color="#000000" size="2">5</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Funai</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">6.9%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">7.2%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">39%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">16%</font></td>
		</tr>
		<tr bgcolor="#999999">
			<td class="style1" valign="top" width="71">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Other</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">56.1%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">52.3%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">24%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">44%</font></td>
		</tr>
		<tr bgcolor="#ffffff">
			<td class="style1" valign="top" width="71">
			<p align="center">&nbsp;</td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="71">
			<font color="#000000" size="2">Total</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">100.0%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">33%</font></td>
			<td class="style1" nowrap="nowrap" valign="bottom" width="64">
			<p align="center"><font color="#000000" size="2">60%</font></td>
		</tr>
	</table>
	<p class="style1"><font color="#000000" size="2">DisplaySearch's TV market 
	intelligence including panel and TV shipments, TV shipments by region by 
	brand by size for nearly 60 brands, rolling 16-quarter forecasts, TV 
	cost/price forecasts and design wins can be found in its </font>
	<a rel="nofollow" target="_blank" href="http://mail.hdtvmagazine.com/Redirect/www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-2318CD58/displaysearch/hs.xsl/quarterly_global_tv_shipment_and_forecast_report.asp">
	<em><font color="#000000" size="2">Quarterly Global TV Shipment and Forecast 
	Report</font></em></a><font color="#000000" size="2">. For more information 
	on this report, please contact Arie Braun at (512) 687-1505 or </font>
	<a rel="nofollow" target="_blank" href="mailto:arie@displaysearch.com">
	<font color="#000000" size="2">arie@displaysearch.com</font></a><font color="#000000" size="2">.
	</font></p>
	<p class="style1"><font color="#000000" size="2">DisplaySearch is also 
	holding a webinar on the Black Friday retail results. For more information, 
	please </font>
	<a rel="nofollow" target="_blank" href="http://mail.hdtvmagazine.com/Redirect/guest.cvent.com/EVENTS/Info/Summary.aspx?e=1d7b8086-6323-48dc-9656-b04f8d6348d3">
	<font color="#000000" size="2">follow this link</font></a><font color="#000000" size="2">.
	</font></div>

<p></body></p>

<p></html><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>November  1, 2007 11:42 AM</b>
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
			<?=getComments(774)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 774)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/11/sharp-and-samsung-reclaim-top-positions-in-lcd-and-flat-panel.php" type="text/javascript" charset="utf-8"></script>
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