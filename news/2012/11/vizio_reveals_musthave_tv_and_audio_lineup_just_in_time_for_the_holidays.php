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
		AND e.entry_id = 4930";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4930 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4930 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4930";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/11/vizio-reveals-musthave-tv-and-audio-lineup-just-in-time-for-the-holidays.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4930";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays" />
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
	<title>HDTV Magazine - VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays</title>
	<meta name="keywords" content="sound bars, led smart, series sound, razor led, best ces, series, sound, led, bars, line, new, audio, smart, inc, quality, hdtv, best, tvs, holiday, feature, consumers, home, any, technology, razor" />
	<meta name="description" content="Just in time for the holiday season, VIZIO, Inc., having recently received J.D. Power and Associates' &quot;Highest in Customer Satisfaction with HDTVs&quot; ranking1, announced today its new TV and audio line-up featuring an all LED-lit2 E-Series line and four newly designed sound bars.  With the recently released E-Series 60&quot; class Razor LED&amp;trade; Smart TV (E601i) already being America's best-selling 60&quot; flat-panel TV3, the line-up also includes the flagship E-Series 70&quot; class Razor LED&amp;trade; Smart TV (E701i) and new 2.0 and 2.1 E-Series and M-Series sound bars, the brand's holiday products are a great wish list addition, capable of taking any room in the home from ordinary to extraordinary at an unparalleled value.

Bringing superb picture quality to the forefront of the home, VIZIO's new, sleek E-Series HDTV holiday line-up features..." />
	<meta name="title" content="VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/11/vizio-reveals-musthave-tv-and-audio-lineup-just-in-time-for-the-holidays.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Just in time for the holiday season, VIZIO, Inc., having recently received J.D. Power and Associates' &quot;Highest in Customer Satisfaction with HDTVs&quot; ranking1, announced today its new TV and audio line-up featuring an all LED-lit2 E-Series line and four newly designed sound bars.  With the recently released E-Series 60&quot; class Razor LED&amp;trade; Smart TV (E601i) already being America's best-selling 60&quot; flat-panel TV3, the line-up also includes the flagship E-Series 70&quot; class Razor LED&amp;trade; Smart TV (E701i) and new 2.0 and 2.1 E-Series and M-Series sound bars, the brand's holiday products are a great wish list addition, capable of taking any room in the home from ordinary to extraordinary at an unparalleled value.

Bringing superb picture quality to the forefront of the home, VIZIO's new, sleek E-Series HDTV holiday line-up features..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4930', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/11/vizio-reveals-musthave-tv-and-audio-lineup-just-in-time-for-the-holidays.php">VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>November  6, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">VIZIO Reveals Must-Have TV and Audio Line-Up Just in time for the Holidays</p>

<center><i>Line-up Features LED TVs from 24" to Flagship 70" LED Smart TV and Four New Sound Bars for a Superior Audio Experience</center></i><br />
<br />

<p><strong>IRVINE, Calif., Nov. 6, 2012 /PRNewswire/ -- </strong>Just in time for the holiday season, VIZIO, Inc., having recently received J.D. Power and Associates' "Highest in Customer Satisfaction with HDTVs" ranking1, announced today its new TV and audio line-up featuring an all LED-lit2 E-Series line and four newly designed sound bars.  With the recently released E-Series 60" class Razor LED&trade; Smart TV (E601i) already being America's best-selling 60" flat-panel TV3, the line-up also includes the flagship E-Series 70" class Razor LED&trade; Smart TV (E701i) and new 2.0 and 2.1 E-Series and M-Series sound bars, the brand's holiday products are a great wish list addition, capable of taking any room in the home from ordinary to extraordinary at an unparalleled value.</p>

<p>Bringing superb picture quality to the forefront of the home, VIZIO's new, sleek E-Series HDTV holiday line-up features large screen size classes of 70", 60" (E601i), 50" (E500i), and 42" (E420i) LED Smart TVs, perfect for a living room or master bedroom upgrade, and smaller screen size classes of 39" (E390), 32" (E320), 29" (E291) and 24" (E241) LED units, ideal for the kitchen, den or office. Whether mounted on the wall, on a stand or in an entertainment center, the TVs look spectacular thanks to a slim outer bezel.  A high-definition 1080p LED picture reveals richer, more vivid colors, while advanced local dimming technology delivers deeper blacks and brighter whites for overall stunning picture quality.  Select E-Series models also feature an ultra-thin profile, less than two inches, and utilize Razor LED&trade; backlighting technology.</p>

<p>As the #1 brand in the US for WiFi flat-panel TVs4, VIZIO's 70", 60", 50", 42" and 32" Smart TVs feature VIZIO Internet Apps&reg; with built-in WiFi for endless entertainment options and instant access to Netflix&reg;, Amazon Instant Video, VUDU&trade;, Hulu Plus, YouTube&reg;, Pandora&reg;, Facebook&reg;, Twitter&reg; and others, all with the push of a button on the smart remote5.  As VIZIO's flagship model, the 70" Razor LED&trade; Smart TV sets the new standard for big screens, serving as an affordable luxury for the entertainment enthusiast.</p>

<p>"The VIZIO TV and audio holiday line-up provides access to leading edge innovations and modern design at an unbeatable total value," said Matt McRae, VIZIO Chief Technology Officer. "The must-have features and stunning picture quality in the 70" LED Smart TV deliver an unparalleled combination of top technology made affordable without compromising quality or customer support."</p>

<p>Further reinforcing VIZIO's position as America's #1 Sound Bar Company6, the new E-Series and M-Series Sound Bars amplify the TV-watching experience by offering advanced audio quality in a compact design to fit any space.  The latest SRS technologies featured in the 2.0 and 2.1 models limit unwanted volume fluctuations and allow for a more immersive surround sound experience.  </p>

<p>The new line-up of sound bars pumps out 95 – 101dB of sound with less than 1% total harmonic distortion7, providing audiophiles with a substantial quality and clarity upgrade when compared to standard HDTV speakers.  The E-Series (SB4021E) and M-Series (SB4021M) 2.1 Home Theater Sound Bars feature wireless subwoofers, which generate deep bass from up to 60 feet away without the use of additional cables.  All sound bars connect easily to most HDTVs with just one cable.</p>

<p>"The E-Series and M-Series 2.0 and 2.1 Sound Bars are the perfect audio upgrade solution, allowing consumers to hear their content the way it was intended to be heard, with rich, accurate audio quality that raises home theater audio expectations to another level," said John Schindler, VIZIO Vice President of Product Management. "All four sound bars feature a slim and sleek design to complement any size TV, creating a rich home theater experience."</p>

<p>Appealing to the eyes as well as the ears this holiday season, VIZIO's LED TVs and sound bars offer consumers great options for any room in the house at an outstanding value. All E-Series LED TV units as well as the 2.0 and 2.1 E-Series and M-Series Sound Bars will be available both in-store and online through key retailers such as Walmart, Costco, Sam's Club, Target, Amazon and VIZIO.com.  A summary of VIZIO's new holiday line-up can be found below with additional information available at VIZIO.com.</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc. is headquartered in Irvine, California. In Q2 2007, VIZIO skyrocketed to the top by becoming the #1 shipping brand of flat panel HDTVs in North America and in Q3 2007 became the first American brand in over a decade to lead in U.S. LCD HDTV shipments. Since 2007 VIZIO LCD HDTV shipments remain in the top ranks in the U.S. and were #1 for the total year in 2009 and 2010.  VIZIO is committed to bringing feature-rich consumer electronics to market at a value through practical innovation. VIZIO offers a broad range of award winning consumer electronics.  VIZIO's products are found at Costco Wholesale, Sam's Club, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners.  VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Fast Company's 6th Most Innovative CE Company of 2009, and made the lists of Ad Age's Hottest Brands, CNET's Editor's Choice, CNET Best of CES 2011 - Television, IGN Best of CES - Television, Bluetooth.org Best of CES, Good Housekeeping's Best Big-Screens, PC World's Best Buy, Popular Mechanics Editor's Choice and OC Metro's 10 Most Trustworthy Brands among many other prestigious honors.  For more information, please call 888-VIZIOCE or visit <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>&copy; 2012 VIZIO, Inc.  The V, VIZIO, Thin + Light and all names, logos and phrases are registered or unregistered trademarks of VIZIO, Inc.  All other trademarks are the property of their respective owners.  All rights reserved.</p>

<p>1J.D. Power and Associates 2012 High Definition Television (HDTV) Satisfaction Report(SM). Report based on responses from 1,009 consumers measuring 8 brands and measures opinions of consumers who purchased an HDTV in the last 12 months. Proprietary study results are based on experiences and perceptions of consumers surveyed August 2012. Your experiences may vary. Visit <a target="_blank" href="http://www.jdpower.com/">www.jdpower.com</a>.</p>

<p>2 VIZIO's new E-series HDTV line features edge-lit and direct-lit LED technologies</p>

<p>3 Source: The NPD Group Weekly Retail Tracking Service, October 14-20, 2012, based on units sold</p>

<p>4 Source: The NPD Group Retail Tracking Service, January-September 2012, based on units sold</p>

<p>5 High-speed/broadband Internet service and access equipment are required and not provided by VIZIO, Inc.  Additional fees and/or subscriptions may be required for certain content and services.  VIZIO, Inc. makes no warranties, representations, or assurances of any kind as to the content, availability, or functionality of third party content or services.</p>

<p>6 Source: Quixel Research&reg;, USA Sound Bar Report Q3 2011 – Q2 2012</p>

<p>7 SB4021M and SB4021E sound bars rated at 101dB, SB4020M and SB4020E rated at 95dB. Sound Pressure Level measured using pink noise at 1 meter, C-weighted. Total Harmonic Distortion calculated as electrical measurement of amplifier distortion.</p>

<p>SOURCE VIZIO, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>November  6, 2012  7:12 PM</b>
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
			<?=getComments(4930)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4930)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/11/vizio-reveals-musthave-tv-and-audio-lineup-just-in-time-for-the-holidays.php" type="text/javascript" charset="utf-8"></script>
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