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
		AND e.entry_id = 4117";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4117 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4117 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4117";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/vizio-announces-full-line-of-theater-3d-hdtvs-in-sizes-ranging-from-22-to-71-inches-brighter-flickerfree-3d-with-superior-performance-for-everyone.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4117";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Announces Full Line of Theater 3D&trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Announces Full Line of Theater 3D&trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Announces Full Line of Theater 3D&trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone" />
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
	<title>HDTV Magazine - VIZIO Announces Full Line of Theater 3D&trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone</title>
	<meta name="keywords" content="razor led, led fhd, active shutter, fhd spsyybluetoothytbdxvt, truled fhd, theater, fhd, led, razor, tvs, active, via, free, shutter, eyewear, performance, technology, truled, hdtvs, brighter, feature, spsyybluetoothytbdxvt, experience, flicker, hdtv" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-theater-3d.jpg&quot; alt=&quot;VIZIO Theater 3D&quot; height=&quot;72&quot; width=&quot;72&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;&lt;strong&gt;IRVINE, Calif., Jan. 3, 2011 /PRNewswire/ -- &lt;/strong&gt;VIZIO, America's #1 LCD HDTV Company*, announced today a full line of Theater 3D&amp;trade; HDTVs that deliver superior 3D performance for all, with sizes ranging from 22 to 71 inches. Theater 3D HDTVs offer crystal-clear, flicker-free 3D that's up to 2x brighter and significantly reduces crosstalk compared to current Active Shutter LCD TVs. Best of all, Theater 3D eyewear is battery-free, lightweight and comfortable, works with most 3D movie theaters, and will be available in..." />
	<meta name="title" content="VIZIO Announces Full Line of Theater 3D&amp;trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Announces Full Line of Theater 3D&amp;trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/vizio-announces-full-line-of-theater-3d-hdtvs-in-sizes-ranging-from-22-to-71-inches-brighter-flickerfree-3d-with-superior-performance-for-everyone.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-theater-3d.jpg&quot; alt=&quot;VIZIO Theater 3D&quot; height=&quot;72&quot; width=&quot;72&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;&lt;strong&gt;IRVINE, Calif., Jan. 3, 2011 /PRNewswire/ -- &lt;/strong&gt;VIZIO, America's #1 LCD HDTV Company*, announced today a full line of Theater 3D&amp;trade; HDTVs that deliver superior 3D performance for all, with sizes ranging from 22 to 71 inches. Theater 3D HDTVs offer crystal-clear, flicker-free 3D that's up to 2x brighter and significantly reduces crosstalk compared to current Active Shutter LCD TVs. Best of all, Theater 3D eyewear is battery-free, lightweight and comfortable, works with most 3D movie theaters, and will be available in..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4117', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/vizio-announces-full-line-of-theater-3d-hdtvs-in-sizes-ranging-from-22-to-71-inches-brighter-flickerfree-3d-with-superior-performance-for-everyone.php">VIZIO Announces Full Line of Theater 3D&trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  3, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>
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
				<p class="prtitle">VIZIO Announces Full Line of Theater 3D&trade; HDTVs in Sizes Ranging from 22 to 71 inches - Brighter, Flicker-Free 3D with Superior Performance for Everyone</p>

<center><i>-Theater 3D&trade; revolutionizes 3D for the home with an up to 2x brighter and flicker-free picture quality and significantly reduces crosstalk compared to current Active Shutter LCD TVs<br /><br />-Theater 3D eyewear is lightweight, comfortable, and doesn't require batteries. They're also compatible with most 3D movie theaters</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/vizio-theater-3d.jpg" alt="VIZIO Theater 3D" height="144" width="144" class="keyimg"><strong>IRVINE, Calif., Jan. 3, 2011 /PRNewswire/ -- </strong>VIZIO, America's #1 LCD HDTV Company*, announced today a full line of Theater 3D&trade; HDTVs that deliver superior 3D performance for all, with sizes ranging from 22 to 71 inches. Theater 3D HDTVs offer crystal-clear, flicker-free 3D that's up to 2x brighter and significantly reduces crosstalk compared to current Active Shutter LCD TVs. Best of all, Theater 3D eyewear is battery-free, lightweight and comfortable, works with most 3D movie theaters, and will be available in a range of styles and colors from brand name designers.</p>

<p>"Theater 3D is a significant move forward from the conventional Active Shutter TVs introduced last year," said Matthew McRae, VIZIO Chief Technology Officer. "Users immediately experience a brighter picture, no flicker, less crosstalk, and the comfortable glasses enabling them to enjoy the content without the technology getting in the way.  And by making this next generation 3D affordable VIZIO aims to fulfill our brand promise of Entertainment Freedom for All."</p>

<p>From the essential E series, to the thoughtfully designed M series, and ultra-high performance XVT series, there will be a Theater 3D model for every movie buff, sports fan, gamer, and anyone else looking for a better, brighter 3D experience, starting this spring.</p>

<p><br />
<strong>The Best 3D Experience</strong></p>

<p>VIZIO Theater 3D&trade; offers consumers a revolutionary new technology that renders flicker-free and brighter images and significantly reduces crosstalk compared to current Active Shutter LCD TVs.</p>

<p>By utilizing a circular polarized 3D filter, the burden of 3D processing is built into the TV, allowing Theater 3D eyewear to be free of the batteries and shutter mechanisms inherent in Active Shutter 3D TVs.</p>

<p>Theater 3D offers several performance advantages over conventional, "active" 3D systems. Theater 3D is up to 2x brighter, and significantly reduces crosstalk compared to current Active Shutter LCD TVs, handles fast motion without blurring, has a wider horizontal viewing angle, and reduces flicker that may cause eye strain found in active shutter 3D solutions. In addition, Theater 3D eyewear can be used to view 3D movies in a majority of movie theaters.</p>

<p>Depending on the model up to four pairs of the lightweight and comfortable Theater 3D glasses are included with each TV. With this, VIZIO has eliminated two of the most common objections to 3D HDTV purchases: the need to wear bulky 3D glasses that require batteries or recharging and the need to invest in expensive additional 3D glasses so the entire family can enjoy it together. By incorporating all of the 3D processing into the TV instead of burdening the eyewear, as is the case with Active 3D, VIZIO Theater 3D enables users to wear comfortable, eco-friendly, battery-free eyewear instead of Active Shutter glasses that are heavy, awkward, and require recharging and other maintenance.</p>

<p><br />
<strong>Support for the Widest Array of 3D Formats</strong></p>

<p>Each Theater 3D&trade; model supports the widest selection of 3D formats to ensure compatibility across Blu-ray, broadcast, cable, satellite, and gaming.  This includes Frame Packing, Side-by-Side, Top and Bottom, SENSIO&reg; HiFi 3D and the RealD Format.</p>

<p>"DisplaySearch is forecasting that North America 3D TV shipments are forecasted to increase by more than 300% in 2011 to 7M units**, driven by a range of new 3D TV types, including circular polarizer filter systems like VIZIO's Theater 3D," stated Paul Gagnon, Director of North America TV Market Research, DisplaySearch.</p>

<p><br />
<strong>VIZIO's Leading LED Picture Quality</strong></p>

<p>Some Theater 3D models feature VIZIO's Edge-Lit Razor LED&trade; technology with Smart Dimming&trade;. Razor LED HDTVs with Smart Dimming&trade; intelligently control the array of LEDs, which are organized in 32 zones. Working frame by frame, based on the content being displayed, Smart Dimming adjusts brightness in precise steps down to pure black (where the LED is completely off). This cutting-edge technology minimizes light leakage and enables a Dynamic Contrast Ratio of 10 Million to 1, for blacker blacks and whiter whites.</p>

<p>Certain XVT models utilize VIZIO's Full Array TruLED&trade; backlighting with Smart Dimming&trade; technology. With over 120 zones across the entire display, TruLED backlighting is able to control specific areas of the image, depending on what's on screen, resulting in the most incredible and life-like images that "pop" off the screen.</p>

<p><br />
<strong>VIZIO Internet Apps&trade; (VIA)</strong></p>

<p>All Theater 3D&trade; models feature VIZIO Internet Apps (VIA) Connected HDTV platform. VIA delivers unprecedented choice and control of web-based content directly to the television without the need for a PC or set-top box. Current Apps from top online content and service brands include: Netflix, Amazon Video On Demand, VUDU, Pandora, Facebook, Flickr, Rhapsody, Twitter, and Yahoo! TV Widgets. Additional Apps recently released include Fandango&reg;, iMemories, MediaBox&trade;, My-Cast&reg;, TuneIn Radio&trade;, Web Videos, Wiki TV and Yahoo Fantasy Football.</p>

<p>Navigating VIA is simple, using the included Bluetooth Universal Remote (optional on some models) that includes a QWERTY keypad. State of the art wireless Internet access is available through built-in 802.11n Wi-Fi, allowing viewers to enjoy the convenience of on-demand movies, TV shows, social networking, music, photos and more with just the push of a button.</p>

<p><br />
<strong>VIA Plus HDTVs</strong></p>

<p>The XVT3D476SV and XVT3D556SV are part of the new VIZIO VIA Plus ecosystem that combines the convenience of entertainment on demand with a consistent and seamless experience across all devices in the ecosystem, including the VIA Phone and VIA Tablet.  VIA Plus TVs, which will incorporate the Google TV platform, feature a sophisticated and intuitive user interface that allows users to access their favorite apps as well as search and browse the web using a premium Bluetooth QWERTY universal remote with touchpad and built-in dual-band 802.11n Wi-Fi.</p>

<p><br />
<strong>Advanced Audio</strong></p>

<p>To compliment the 3D video of the Theater 3D&trade; TVs, M and XVT models include the latest high performance audio technologies from SRS Labs. SRS technologies help deliver an immersive, virtual, high definition surround sound through SRS TruSurround HD&trade;. TruSurround HD creates an immersive, feature-rich surround sound experience from two speakers, complete with rich bass, high frequency detail and clearer dialog. In addition, SRS TruVolume&trade; provides a consistent and comfortable volume level while watching TV programming for a more enjoyable multimedia experience.</p>

<p>Certain VIZIO Theater 3D TVs will feature SRS StudioSound&trade; HD - the ultimate all-in-one audio suite designed specifically for Flat Panel TVs. Years of excellence in audio, practical experience, and patented technologies allow StudioSound HD to deliver the most immersive and natural surround sound ever using built-in TV speakers. The suite also delivers remarkably crisp and clear dialog, rich bass, an elevated sound stage and consistent, spike-free volume levels. StudioSound HD features optimized audio presets for movies, news, sports and music while also providing a built-in EQ toolset for peak audio performance.</p>

<p><br />
<strong>Theater 3D in Style</strong></p>

<p>In addition, Theater 3D eyewear will be available from well-known designer brands, including Oakley who has already launched one line of 3D eyewear that is also compatible with Theater 3D HDTVs. Launched recently as the world's first optically correct 3D glasses, Oakley 3D Gascan&reg; utilizes the company's proprietary HDO-3D&trade; technology for superior visual clarity and signature Oakley comfort.</p>

<p>VIZIO continues to lead the HDTV marketplace and with the added performance and value of Theater 3D in these models for 2011:</p>

<p>VIZIO Theater 3D Series<br />
<table class="simple"><tr class="header"><td>Model</td><td>Size/ Res.</td><td>Refresh</td><td>Smart Dimming</td><td>VIZIO Internet Apps</td><td>QWERTY Remote</td><td>SRS Studio Sound HD</td><td>MSRP</td></tr><tr><td>E3D320VX</td><td>32" FHD</td><td>60</td><td>N</td><td>Y</td><td>IR</td><td>Y</td><td>TBD</td></tr><tr><td>E3D420VX</td><td>42" FHD</td><td>120</td><td>N</td><td>Y</td><td>IR</td><td>Y</td><td>TBD</td></tr><tr><td>E3D470VX</td><td>47" FHD</td><td>120</td><td>N</td><td>Y</td><td>IR</td><td>Y</td><td>TBD</td></tr><tr><td>M3D420SV Razor LED</td><td>42" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>M3D420SR Razor LED</td><td>42" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>M3D460SR Razor LED</td><td>46" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>M3D470SV Razor LED</td><td>47" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>M3D550SV Razor LED</td><td>55" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>M3D550SR Razor LED</td><td>55" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D225KP Razor LED</td><td>22" FHD</td><td>60</td><td>N</td><td>Y</td><td>IR</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D265KP Razor LED</td><td>26" FHD</td><td>60</td><td>N</td><td>Y</td><td>IR</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D325KP Razor LED</td><td>32" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D375KP Razor LED</td><td>37" FHD</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D425SP TruLED</td><td>42" FHD</td><td>480 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D475SP TruLED</td><td>47" FHD</td><td>480 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D555SP TruLED</td><td>55" FHD</td><td>480 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D650SV Razor LED</td><td>65" FHD</td><td>120</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D476SV TruLED</td><td>47" FHD</td><td>480 SPS</td><td>Y</td><td>Y, PLUS</td><td>Bluetooth w/Touchpad</td><td>VIA Plus</td><td>TBD</td></tr><tr><td>XVT3D556SV TruLED</td><td>55" FHD</td><td>480 SPS</td><td>Y</td><td>Y, PLUS</td><td>Bluetooth w/Touchpad</td><td>VIA Plus</td><td>TBD</td></tr><tr><td>XVT3D500CM Razor LED</td><td>50" WFHD 2560 x1080</td><td>240 SPS</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr><tr><td>XVT3D580CM Razor LED</td><td>58" WFHD 2560 x1080</td><td>120</td><td>Y</td><td>Y</td><td>Bluetooth</td><td>Y</td><td>TBD</td></tr></table></p>

<p>* Sources: Q3 2010 iSuppli and DisplaySearch Reports</p>

<p>** Report source: DisplaySearch Quarterly TV Design and Feature Report</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc., "Entertainment Freedom For All," headquartered in Irvine, California, is America's HDTV and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead in U.S. TV sales. Since 2007 VIZIO HDTV shipments remain in the TOP ranks in the U.S. and was #1 for the total year in 2009.  VIZIO is committed to bringing feature-rich consumer electronics to market at a value through practical innovation. VIZIO offers a broad range of award winning consumer electronics. VIZIO's products are found at Costco Wholesale, Sam's Club, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Fast Company's 6th Most Innovative CE Company of 2009, and made the lists of Ad Age's Hottest Brands, Good Housekeeping's Best Big-Screens, CNET's Editor's Choice, PC World's Best Buy and OC Metro's 10 Most Trustworthy Brands among many other prestigious honors. For more information, please call 888-VIZIOCE or visit on the web at <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>The V, VIZIO, TruLED, Extreme VIZIO Technology XVT, VIZIO Internet Apps, VIA Plus, 480Hz SPS, 240Hz SPS, Thin Line, Smooth Motion, Razor LED, Smart Dimming, Theater 3D, Cinema HDTV, Entertainment Freedom For All names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>SOURCE VIZIO</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  3, 2011  6:53 PM</b>
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
			<?=getComments(4117)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4117)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/vizio-announces-full-line-of-theater-3d-hdtvs-in-sizes-ranging-from-22-to-71-inches-brighter-flickerfree-3d-with-superior-performance-for-everyone.php" type="text/javascript" charset="utf-8"></script>
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