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
		AND e.entry_id = 1617";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1617 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1617 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1617";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/lg-electronics-2009-hdtv-lines-feature-smart-technologies-beautiful-viewing-experience.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1617";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics\' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics\' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics\' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience" />
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
	<title>HDTV Magazine - LG Electronics' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience</title>
	<meta name="keywords" content="inch class, class inch, inch diagonal, energy saving, class sizes, inch, class, diagonal, series, access, hdtv, full, features, energy, sizes, technologies, smart, electronics, saving, home, hdmi, lcd, color, consumers, include" />
	<meta name="description" content="LG Electronics today unveiled 12 stylish new HDTV product lines -- nine series of LCD models and three plasma series -- with screens ranging from 19- to 60-inch class sizes. Each of these HDTVs blends a refined design with smart technologies to elevate and personalize the home theater experience. The entire line is being introduced this week at the 2009 International CES(R), Booth #8214, Central Hall, Las Vegas Convention Center.

Many of LG's HDTV series incorporate advanced technologies -- such as..." />
	<meta name="title" content="LG Electronics' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/lg-electronics-2009-hdtv-lines-feature-smart-technologies-beautiful-viewing-experience.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LG Electronics today unveiled 12 stylish new HDTV product lines -- nine series of LCD models and three plasma series -- with screens ranging from 19- to 60-inch class sizes. Each of these HDTVs blends a refined design with smart technologies to elevate and personalize the home theater experience. The entire line is being introduced this week at the 2009 International CES(R), Booth #8214, Central Hall, Las Vegas Convention Center.

Many of LG's HDTV series incorporate advanced technologies -- such as..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1617', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/lg-electronics-2009-hdtv-lines-feature-smart-technologies-beautiful-viewing-experience.php">LG Electronics' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">LG Electronics' 2009 HDTV Lines Feature Smart Technologies, Beautiful Viewing Experience</p>

<center><i>LCD, Plasma HDTVs Combine Stylish Design, Crisp Image Quality, Enhanced Energy Savings</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire/</B> -- LG Electronics today unveiled 12 stylish new HDTV product lines -- nine series of LCD models and three plasma series -- with screens ranging from 19- to 60-inch class sizes. Each of these HDTVs blends a refined design with smart technologies to elevate and personalize the home theater experience. The entire line is being introduced this week at the 2009 International CES(R), Booth #8214, Central Hall, Las Vegas Convention Center.</p>

<p><br />
<B>Technological Innovation and Energy Savings</B></p>

<p>Many of LG's HDTV series incorporate advanced technologies -- such as LED Backlighting, TruMotion 240Hz and wireless capabilities. Four core technologies -- AV Mode II (Cinema, Sport, Game), Invisible Speakers, Clear Voice II and Picture Wizard -- further enhance the entertainment experience. (For more information, see separate releases.)</p>

<p>Two new series of "Broadband HDTVs" feature LG's "NetCast(TM) Entertainment Access". These plasma and LCD models offer Ethernet connectivity providing access to a wide variety of information and entertainment content directly from the Internet*. Through LG's new alliances with Yahoo!(R), Netflix and YouTube, consumers can enjoy even more content from the comfort of their home. Consumers can use the Yahoo! Widget Engine for access to up-to-the minute weather reports, stock quotes, Flickr(R) photos, and more. LG HDTVs will be the first to offer instant streaming from Netflix and will also enable access to the vast array of user-generated content on YouTube directly on the LH50 and PS80 series.</p>

<p>Two wireless HDTV solutions (LHX and LH85 series) remove unsightly wires and allow for flexible and convenient TV installation options. LG's new HDTVs also meet Energy Star 3.0 specifications and have additional energy saving features. With LG's "Smart Energy Saving" package, which consists of features such as backlight control options and video mute and some series adding Intelligent Sensor, LG is paving the way toward energy saving while also allowing consumers to customize their viewing experience.</p>

<p><br />
<B>LG LCDs: Elegant Design, Wireless Content Access, Advanced Calibration Options</B></p>

<p>Innovative LCD HDTVs reward consumers' senses with easy calibration options for video and audio as well as enhanced access to content. For a crisp, clear viewing experience, all of LG's LCD HDTVs use the Super In Plane Switching (S-IPS)** panel structure which provides faster response times and better color and contrast, even at the most extreme off-axis viewing angles. LG's overall 2009 LCD HDTV line features screen size classes ranging from 19- to 55 inches:</p>

<p>LHX Slim Wireless LED Backlight HDTV (Class Size: 55-inch*) A CES 2009 Innovations honoree, LG's LHX offers superior picture quality with an elegant ultra-slim design -- less than one-inch thick at its thinnest point. Unlike other slim LED HDTVs that use "edge" lighting, LG's LHX uses a full array of LED backlights which employ local dimming techniques for precise picture control, resulting in deeper blacks, wide color gamut and smooth motion, achieving 240Hz performance for more natural picture clarity. Key features include:</p>

<p>  *  Full HD 1080p via Uncompressed Wireless Transmission from media box<br />
  *  TruMotion 240 Hz<br />
  *  LED backlighting with Local Dimming<br />
  *  2,000,000:1 Dynamic Contrast Ratio<br />
  *  24p Real Cinema (5:5 Pulldown)<br />
  *  Intelligent Sensor<br />
  *  ISFccc Ready<br />
  *  Four (4) HDMI V. 1.3 with Deep Color<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  LG SimpLink(TM) (HDMI-CEC)<br />
  *  Smart Energy Saving Plus<br />
  *  LG Core Technologies</p>

<p><br />
LH90 LED Backlight HDTV (Class Sizes: 55-, 47-, and 42-inch*): The LH90 series boasts all the same functionalities of the LHX model in a self- contained, clean modern silhouette (no separate set top box).</p>

<p>LH85 Wireless Full HD 1080p HDTV (Class Sizes: 55-, and 47-inch*): With an uncompressed wireless system and Full HD 1080p transmission from a separate media box, this series eliminates messy wires and cables and allows for the freedom to place the HDTV anywhere in the room. Key features include:</p>

<p>  *  Full HD 1080p via Uncompressed Wireless Transmission from media box.<br />
  *  TruMotion 120Hz<br />
  *  Intelligent Sensor<br />
  *  24p Real Cinema<br />
  *  Four (4) HDMI V.1.3 with Deep Color<br />
  *  ISFccc Ready<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  LG SimpLink(TM) (HDMI-CEC)<br />
  *  Smart Energy Saving Plus<br />
  *  LG Core Technologies</p>

<p><br />
LH55 Full HD 1080p LCD HDTV with Scanning Backlight (Class Sizes: 55-, 47-,42-, 37-inch*): For action junkies, TruMotion 240Hz uses a unique scanning backlight technique to improve motion picture response time with a more natural picture. Key features include:</p>

<p>  *  Full HD 1080p<br />
  *  TruMotion 240Hz<br />
  *  Intelligent Sensor<br />
  *  24p Real Cinema<br />
  *  Four (4) HDMI V.1.3 with Deep Color<br />
  *  ISFccc Ready<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  Smart Energy Saving Plus<br />
  *  LG Core Technologies</p>

<p><br />
LH50 Full HD 1080p LCD HDTV with NetCast(TM) Entertainment Access (Class Sizes: 47-, and 42-inch*): Using LG's first ever HDTV with Ethernet connectivity, consumers can access even more content and video on their big screen TV without the need for a computer. This connectivity offers access to Netflix Instant Streaming, Yahoo! Widgets, YouTube, and can also access music and photos stored on a home PC - so consumers have a choice when it comes to entertainment. Key features include:</p>

<p>  *  Full HD 1080p<br />
  *  TruMotion 120Hz<br />
  *  ISFccc Ready<br />
  *  Intelligent Sensor<br />
  *  24p Real Cinema (5:5 Pulldown)<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  Four (4) HDMI V.1.3 With Deep Color<br />
  *  Smart Energy Saving Plus<br />
  *  LG Core Technologies</p>

<p><br />
LU55 Stylish LCD HDTV Series (Class Sizes: 26-, 22-, and 19-inch*): LG's LU55 series offers stylish accents for the home office, kitchen, or bedroom with small screen sizes and transparent and modern designs. Key features include:</p>

<p>  *  26- and 22-inch sizes are Full HD 1080p, 19-inch has 720p HD Resolution<br />
  *  Transparent design</p>

<p><br />
LH40 Full HD 1080p LCD HDTV Series (Class Sizes: 55-, 47-, 42-, 37-, 32- inch*): The LH40 series offers consumers an exceptional viewing and listening experience with a sleek exterior and unique Invisible Speaker System. With TruMotion 120 Hz and LG's S-IPS technology motion blur is reduced for a more natural viewing experience at almost any angle. Key features include:</p>

<p>  *  Full HD 1080p<br />
  *  Three (3) HDMI(TM) (V.1.3 with Deep Color)<br />
  *  Intelligent Sensor<br />
  *  24p Real Cinema (5:5 Pulldown)<br />
  *  ISFccc Ready<br />
  *  LG SimpLink(TM)<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  Smart Energy Saving Plus<br />
  *  LG Core Technologies</p>

<p><br />
LH30 Full HD 1080p LCD HDTV Series (Class Sizes: 47-, 42-, 37-, 32-inch*): The LH30 series features LG's four core technologies in a simple and elegant form while Invisible Speakers make for a slim silhouette. Key features include:</p>

<p>  *  Full HD 1080p<br />
  *  Super IPS Panel Technology<br />
  *  ISFccc Ready<br />
  *  LG SimpLink(TM)<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  24p Real Cinema (2:2 Pulldown)<br />
  *  Three (3) HDMI(TM) (V.1.3 with Deep Color)<br />
  *  Smart Energy Saving</p>

<p><br />
LH20 LCD HDTV Series (Class Sizes: 42-, 37-, 32-, 26-, 22-, 19-inch*): LG's LH20 series provides a variety of sizes for diverse placement in consumers' homes. Key features include:</p>

<p>  *  720p HD Resolution<br />
  *  Two (2) HDMI(TM) (V.1.3 with Deep Color)<br />
  *  ISFccc Ready<br />
  *  Smart Energy Saving<br />
  *  LG SimpLink (TM) (32-inch and above)<br />
  *  LG Core Technologies (32-inch and above)</p>

<p><br />
<B>LG Plasmas: Sleek Style with Advanced Content Access</B></p>

<p>Three new plasma HDTV series use the latest generation of panel technology for improved brightness, reduced reflectivity and fast response time with 600Hz smooth motion for an incredible viewing experience. The 1080p series (PS60, PS80) also features THX(R) Display Certification providing accurate picture reproduction and a more immersive viewing experience.</p>

<p>The PS80 series comes equipped with "THX(R) Media Director," a feature designed to simplify operation, ensuring optimized settings, use of product features and deliver a realistic entertainment experience. THX Media Director allows movies, music and other digital media to communicate picture and sound settings directly to other THX Media Director-enabled consumer electronics devices, dynamically configuring them for the best playback experience.</p>

<p><br />
<B>Highlights from LG's 2009 plasma HDTV series include:</B></p>

<p>PS80 Full HD 1080p Plasma HDTV with NetCast(TM) Entertainment Access (Class Sizes: 60- and 50-inch*): LG's first plasma HDTV with Ethernet connectivity allows consumers to access even more content and video on their big screen TV without the need for a computer. This connectivity offers access to Netflix Instant Streaming, Yahoo! Widgets, YouTube and can also access music and photos stored on a home PC -- so consumers have a choice when it comes to entertainment. Key features include:</p>

<p>  *  Full HD 1080p<br />
  *  THX Display Certification and THX Cinema Mode<br />
  *  THX Media Director<br />
  *  Super Bright Panel<br />
  *  600Hz Smooth Motion<br />
  *  Four (4) HDMI (V.1.3 with Deep Color)<br />
  *  ISFccc ready<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  LG SimpLink(TM) Connectivity<br />
  *  Smart Energy Saving<br />
  *  LG Core Technologies</p>

<p><br />
PS60 Full HD 1080p Plasma HDTV Series (Class Sizes: 60- and 50-inch*): The PS60 series features a unique single-layer design that gives the appearance of a pane of glass and offers consumers a variety of bezel colors to match their home decor. Key features include:</p>

<p>  *  Full HD1080p<br />
  *  THX Display Certification and THX Cinema Mode<br />
  *  Super Bright Panel<br />
  *  600Hz Smooth Motion<br />
  *  Four (4) HDMI (V.1.3 with Deep Color)<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  Smart Energy Saving<br />
  *  LG SimpLink(TM) Connectivity<br />
  *  LG Core Technologies</p>

<p><br />
PQ30 Plasma HDTV Series (Class Sizes: 50- and 42-inch*): The PQ30 series of plasmas offer consumers crisp picture and sound quality for those looking for maximum value in a cost-effective package. Key features include:</p>

<p>  *  720p HD Resolution<br />
  *  600Hz Smooth Motion<br />
  *  Three (3) HDMI (V.1.3 with Deep Color)<br />
  *  USB 2.0 for access to digital music and photos (MP3, JPEG)<br />
  *  Auto Volume Leveler II<br />
  *  Smart Energy Saving<br />
  *  LG SimpLink(TM) Connectivity<br />
  *  LG Core Technologies</p>

<p><br />
<B>About LG Electronics USA</B></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances and business solutions, all under LG's "Life's Good" marketing theme. For more information, please visit http://www.lgusa.com/.</p>

<p><br />
<B>About LG Electronics, Inc.</B></p>

<p>LG Electronics, Inc. is a global leader and technology innovator in consumer electronics, home appliances and mobile communications, employing more than 82,000 people working in 114 operations including 82 subsidiaries around the world. With annual worldwide revenues exceeding $40 billion, LG Electronics comprises five business units: Home Entertainment, Home Appliance, Air Conditioning, Business Solutions and Mobile Communications. LG is one of the world's leading producers of mobile handsets, flat panel TVs, air conditioners, front-loading washing machines, optical storage products, DVD players and home theater systems. For more information, please visit http://www.lge.com/.</p>

<p>   * Product specifications subject to change without notice.<br />
   * Internet subscription required and sold separately.<br />
   * For more information, please visit<br />
     http://www.pimsmultimedia.com/LGCES2009<br />
   ** S-IPS panels on models 32-inches and above.</p>

<p>   * 55LHX 55-inch class/54.6-inch diagonal<br />
   * 55LH85 55-inch class/54.6-inch diagonal<br />
   * 55LH90 55-inch class/54.6-inch diagonal<br />
   * 47LH90 47-inch class/47.0-inch diagonal<br />
   * 42LH90 42-inch class/42.0-inch diagonal<br />
   * 47LH85 47-inch class/47.0-inch diagonal<br />
   * 55LH55 55-inch class/54.6-inch diagonal<br />
   * 47LH55 47-inch class/47.0-inch diagonal<br />
   * 42LH55 42-inch class/42.0-inch diagonal<br />
   * 37LH55 37-inch class/37.0-inch diagonal<br />
   * 47LH50 47-inch class/47.0-inch diagonal<br />
   * 42LH50 42-inch class/42.0-inch diagonal<br />
   * 26LU55 26-inch class/26.0-inch diagonal<br />
   * 22LU55 22-inch class/21.6-inch diagonal<br />
   * 19LU55 19-inch class/18.5-inch diagonal<br />
   * 55LH40 55-inch class/54.6-inch diagonal<br />
   * 47LH40 47-inch class/47.0-inch diagonal<br />
   * 42LH40 42-inch class/42.0-inch diagonal<br />
   * 37LH40 37-inch class/37.0-inch diagonal<br />
   * 32LH40 32-inch class/31.5-inch diagonal<br />
   * 47LH30 47-inch class/47.0-inch diagonal<br />
   * 42LH30 42-inch class/42.0-inch diagonal<br />
   * 37LH30 37-inch class/37.0-inch diagonal<br />
   * 32LH30 32-inch class/31.5-inch diagonal<br />
   * 42LH2042-inch class/42.0-inch diagonal<br />
   * 37LH20 37-inch class/37.0-inch diagonal<br />
   * 32LH20 32-inch class/31.5-inch diagonal<br />
   * 26LU20 26-inch class/26.0-inch diagonal<br />
   * 22LU20 22-inch class/21.6-inch diagonal<br />
   * 19LU20 19-inch class/18.5-inch diagonal<br />
   * 60PS80 60-inch class/59.5-inch diagonal<br />
   * 50PS80 50-inch class/50.0-inch diagonal<br />
   * 60PS60 60-inch class/59.5-inch diagonal<br />
   * 50PS60 50-inch class/50.0-inch diagonal<br />
   * 50PQ30 50-inch class/50.0-inch diagonal<br />
   * 42PQ30 42-inch class/41.6-inch diagonal<br />
   * 60PS80 60-inch class/59.5-inch diagonal</p>

<p>Source: LG Electronics USA, Inc. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 10:57 AM</b>
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
			<?=getComments(1617)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1617)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/lg-electronics-2009-hdtv-lines-feature-smart-technologies-beautiful-viewing-experience.php" type="text/javascript" charset="utf-8"></script>
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