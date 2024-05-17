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
		AND e.entry_id = 4992";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4992 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4992 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4992";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2013/01/brace-yourself-sharp-unveils-bigger-more-beautiful-aquos-led-tv-lineup-at-ces-2013.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4992";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Brace Yourself: Sharp&reg; Unveils Bigger, More Beautiful AQUOS&reg; LED TV Lineup at CES 2013" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Brace Yourself: Sharp&reg; Unveils Bigger, More Beautiful AQUOS&reg; LED TV Lineup at CES 2013" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Brace Yourself: Sharp&reg; Unveils Bigger, More Beautiful AQUOS&reg; LED TV Lineup at CES 2013" />
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
	<title>HDTV Magazine - Brace Yourself: Sharp&reg; Unveils Bigger, More Beautiful AQUOS&reg; LED TV Lineup at CES 2013</title>
	<meta name="keywords" content="inch class, class –, msrp inch, led tvs, aquos led, aquos, sharp, led, –, class, inch, msrp, screen, new, series, tvs, technology, quattron, picture, feature, models, lineup, slim, web, march" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/Sharp-LC-90LE745U.jpeg&quot; alt=&quot;Sharp LC-90le745u&quot; height=&quot;123&quot; width=&quot;200&quot;&gt;Get ready for an all out visual thrill ride. Sharp today unveiled nearly twenty 60&quot; plus class TV models as part of its new line of AQUOS LED TVs, with more models that feature Quattron technology, more choices in the fast growing 60&quot;, 70&quot; and 80&quot; screen classes, striking new slim designs and the world's largest LED TV, the 90&quot; AQUOS.

For the first time..." />
	<meta name="title" content="Brace Yourself: Sharp&amp;reg; Unveils Bigger, More Beautiful AQUOS&amp;reg; LED TV Lineup at CES 2013" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Brace Yourself: Sharp&amp;reg; Unveils Bigger, More Beautiful AQUOS&amp;reg; LED TV Lineup at CES 2013" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2013/01/brace-yourself-sharp-unveils-bigger-more-beautiful-aquos-led-tv-lineup-at-ces-2013.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/Sharp-LC-90LE745U.jpeg&quot; alt=&quot;Sharp LC-90le745u&quot; height=&quot;123&quot; width=&quot;200&quot;&gt;Get ready for an all out visual thrill ride. Sharp today unveiled nearly twenty 60&quot; plus class TV models as part of its new line of AQUOS LED TVs, with more models that feature Quattron technology, more choices in the fast growing 60&quot;, 70&quot; and 80&quot; screen classes, striking new slim designs and the world's largest LED TV, the 90&quot; AQUOS.

For the first time..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4992', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2013/01/brace-yourself-sharp-unveils-bigger-more-beautiful-aquos-led-tv-lineup-at-ces-2013.php">Brace Yourself: Sharp&reg; Unveils Bigger, More Beautiful AQUOS&reg; LED TV Lineup at CES 2013</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 10, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">Brace Yourself: Sharp&reg; Unveils Bigger, More Beautiful AQUOS&reg; LED TV Lineup at CES 2013</p>

<center><i>Large Screen LED TV Leader Showcases World's Largest LED TV, More Models with Quattron&trade; Technology, More Choices in 60", 70" and 80" (diagonal) Screen Size Classes, and Striking Slim Designs</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/Sharp-LC-90LE745U.jpeg" alt="Sharp LC-90le745u" height="247" width="400"><strong>LAS VEGAS--(BUSINESS WIRE)</strong>--Get ready for an all out visual thrill ride. Sharp today unveiled nearly twenty 60" plus class TV models as part of its new line of AQUOS LED TVs, with more models that feature Quattron technology, more choices in the fast growing 60", 70" and 80" screen classes, striking new slim designs and the world's largest LED TV, the 90" AQUOS.</p>

<p>"It's simple – consumers want big screens with excellent picture quality, something that Sharp is uniquely able to deliver," said John Herrington, President of Sharp Electronics Marketing Company of America, a division of Sharp Electronics Corporation. "We're gearing up to extend our leadership in the large screen LED TV market with our finest AQUOS lineup ever, including more choices in large screen LED TVs, more Quattron models and iconic designs."</p>

<p><br />
<strong>Picture Quality</strong></p>

<p>For the first time, more than half of the AQUOS 2013 large screen LED TV lineup will feature Sharp's exclusive Quattron technology in the 7- and 8-Series. Quattron color technology adds a yellow pixel to the standard red-green-blue sub-pixel structure, delivering more than a billion colors. This offers greater detail, smoother lines, and brighter yellows, deeper blues and richer golds. Screen brightness is also enhanced without compromising color accuracy.</p>

<p>In the new AQUOS 8-Series, Sharp takes brightness to an entirely new level with the introduction of Super Bright which combines an intelligent contrast engine with a 50 percent higher brightness panel to create Sharp's brightest picture with more contrast. The intelligent contrast engine constantly analyzes the signal and enhances the brightness of the bright objects on the screen while maintaining the black levels on the rest of the screen. The result is Sharp's most brilliant, most contrasted picture ever.</p>

<p><br />
<strong>The World's Largest LED TV</strong></p>

<p>Introduced in June of 2012, and standing majestically at nearly 4 feet tall and spanning 6 feet and 7 inches wide, the 90" (diagonal) AQUOS is the world's largest LED TV and continues as part of the 2013 AQUOS lineup. Its picture quality is as stunning as its size, with crisp and clear content at the highest HD resolution of 1080p. Despite its size, the TV is relatively thin and light. Weighing only 141 pounds and less than 5 inches deep, the unit is easy to mount on a wall. And with LED technology, it is so energy efficient, it costs only $28 per year to operate.</p>

<p><br />
<strong>Smart TV</strong></p>

<p>All three large screen series (6-, 7-, 8-Series) in the 2013 AQUOS lineup will be smart, with a dual-core processor, Web browser and built-in Wi-Fi. Sharp's SmartCentral platform provides an easy way for consumers to access virtually unlimited Web based content.</p>

<p>In the 2013 AQUOS lineup, SmartCentral will include an expanded selection of apps, Flash and HTML5 supported Web browsing and Android and iOS remote control operation. It will also feature Sharp Beam, a free app that enables users to send content from an iOS and Android smartphone or tablet to the big screen with a flick. A new SmartCentral feature gives consumers the ability to split screens for simultaneous TV viewing and Web browsing. Finally, Netflix subscribers will be able to search for content on their smartphone or tablet and then select to watch it on their AQUOS TV with Netflix "Second Screen" capability.</p>

<p><br />
<strong>Striking Design</strong></p>

<p>Sharp also brings a new innovative slim design to the 2013 AQUOS line of LED TVs. The screen bezel is astonishingly slim, so users can fit more TV in less space. The sides of the TV amazingly appear to fall away. And the whole display is sleek and thin, so customers can place or mount it practically anywhere.</p>

<p>High quality brushed aluminum frames adorn the 7- and 8-Series; the latter featuring distinctive diamond cut edges and a stunning silver finish. They also include new rounded-edge stands – the new 8-Series also includes a new silver plated O-shaped stand, giving the appearance of a floating TV.</p>

<p>Since the beautiful new designs are the perfect frames to turn a blank screen into a work of art, the new Wallpaper Mode will allow pre-installed artwork images or home photos from a USB drive on the AQUOS LED screen at a reduced light level that mirrors museum conditions, so the screen blends beautifully into a room's décor with extremely low power consumption.</p>

<p><br />
<strong>3D</strong></p>

<p>Eleven of Sharp's large screen AQUOS models feature full HD 1080p active 3D technology, delivering twice the resolution of passive 3D. New for 2013 is the use of Bluetooth 3D glasses that are not subject to interference.</p>

<p><br />
<strong>Sound</strong></p>

<p>6-Series models feature powerful 20W audio for high fidelity with clear voice, producing clean and crisp audio. The 7- and 8-Series models add Yamaha DSP technology and a 15W subwoofer, for a total of 35W, adding big, deep sound for richer audio.</p>

<p><br />
<strong>Energy Efficiency</strong></p>

<p>Most AQUOS LED TVs are ENERGY STAR&reg; 6.0 qualified. In addition, the Optical Picture (OPC) feature helps save power by using a sensor on the front of the TV to detect ambient room light, and automatically adjusts TV brightness for the best picture.</p>

<p><br />
<strong>AQUOS Advantage LiveSM</strong></p>

<p>AQUOS connected TV owners have access to AQUOS Advantage Live, Sharp's unique, complimentary customer support program. AQUOS Advantage Live allows trained advisors to remotely connect via the Internet to AQUOS TVs to assist with setup, troubleshooting and picture optimization. And with Live answer, Sharp's experts are available to customers through a dedicated line, 1-87-SEE-AQUOS, and can provide instantaneous, remote access to representatives.</p>

<p><br />
<strong>Sharp AQUOS Quattron 8-Series 3D LED TVs</strong></p>

<p>Quattron color intensification<br />
Super Bright technology<br />
AQUOS 1080p LED display<br />
Smart TV with Dual-Core Processor, built-in Wi-Fi and Web browser<br />
Aquomotion960 with 240Hz panel<br />
Active 3D with two Bluetooth 3D glasses supplied<br />
35W Audio with built-in subwoofer<br />
Ultra slim silver aluminum frame with diamond-cut edges<br />
Wallpaper Mode<br />
Introduction:<br />
60 inch class - LC-60LE857 (March) MSRP: $2999.99<br />
70 inch class - LC-70LE857 (April) MSRP: $3999.99<br />
80 inch class - LC-80LE857 (April) MSRP: $6499.99</p>

<p><br />
<strong>Sharp AQUOS 7-Series 3D LED TVs</strong></p>

<p>Quattron color intensification<br />
AQUOS 1080p LED display<br />
Smart TV with Dual-Core Processor, built-in Wi-Fi and Web browser<br />
Aquomotion480 with 240Hz panel (C7500/LE757)<br />
240 Hz panel (LE755)<br />
Active 3D with two Bluetooth 3D glasses supplied (LE755/LE757)<br />
35W audio with built-in subwoofer<br />
Ultra slim black aluminum frame<br />
Wallpaper Mode<br />
Introduction:<br />
60 inch class – LC-60LE757 (April) MSRP: $2299.99<br />
70 inch class – LC-70LE757 (April) MSRP: $3499.99<br />
80 inch class – LC-80LE757 (May) MSRP: $5999.99</p>

<p>60 inch class – LC-60LE755 (February) MSRP: $2199.99<br />
70 inch class – LC-70LE755 (March) MSRP: $3399.99</p>

<p>60 inch class – LC60C7500 (March) MSRP: $2099.99<br />
70 inch class – LC70C7500 (March) MSRP: $3299.99</p>

<p><br />
<strong>Sharp AQUOS 6-Series LED TVs</strong></p>

<p>AQUOS 1080p LED display<br />
Smart TV with Dual-Core Processor, built-in Wi-Fi and Web browser<br />
Active 3D (LE657)<br />
AQUOMotion 240 with 120Hz panel (LE657)<br />
Ultra slim frame<br />
Wallpaper Mode<br />
Introduction: LE657 – active 3D<br />
60 inch class – LC-60LE657 (March) MSRP: $1799.99<br />
70 inch class – LC-70LE657 (March) MSRP: $2799.99</p>

<p>Introduction: LE650 – non-3D<br />
60 inch class – LC-60LE650 (February) MSRP: $1499.99<br />
70 inch class – LC-70LE650 (February) MSRP: $2499.99<br />
80 inch class – LC-80LE650 (May) MSRP: $4999.99</p>

<p>60 inch class – LC-60C6500 (Q1) MSRP: $1499.99<br />
70 inch class – LC-70C6500 (Q1) MSRP: $2499.99<br />
80 inch class – LC-80C6500 (Q1) MSRP: $4999.99</p>

<p>For more information on Sharp's CES announcements, please visit <a target="_blank" href="http://www.SharpUSA.com/CESnews/">www.SharpUSA.com/CESnews</a>. For more information visit Sharp Electronics Corporation at <a target="_blank" href="http://www.SharpUSA.com/">www.SharpUSA.com</a>. Find us on Facebook, follow us on Twitter and watch us on YouTube.</p>

<p><br />
<strong>About Sharp Electronics Corporation:</strong></p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions, LED lighting and mobile communication and information tools. Leading brands include AQUOS&reg; LED TVs, Insight&reg; Microwave Drawer&reg; ovens, Notevision&reg; multimedia projectors and Plasmacluster&reg; air purifiers.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 10, 2013 10:25 PM</b>
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
			<?=getComments(4992)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4992)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2013/01/brace-yourself-sharp-unveils-bigger-more-beautiful-aquos-led-tv-lineup-at-ces-2013.php" type="text/javascript" charset="utf-8"></script>
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