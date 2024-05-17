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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, viglink_suppress, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 292";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];
	# I was going to use this and discovered that I could turn off insertion separately from affiliation
	#$viglink_suppress = ($row_aux['viglink_suppress'] == 1) ? 'nolinks' : '';

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 292 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 292 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 292";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/01/buying-an-hdtv-how-to-be-prepared-to-select-the-one-for-you.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 292";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Buying an HDTV?  How to be prepared to select the one for you" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Buying an HDTV?  How to be prepared to select the one for you" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Buying an HDTV?  How to be prepared to select the one for you" />
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
	<title>HDTV Magazine - Buying an HDTV?  How to be prepared to select the one for you</title>
	<meta name="keywords" content="expansion modes, home theater, screen size, hdtv set, black bars, hdtv, viewing, image, might, digital, set, need, could, screen, analog, sets, dtv, stb, integrated, video, tuners, cable, dvi, make, tuner" />
	<meta name="description" content="You have been hearing about HDTV and decided to start looking for one.  A friend of yours reminds you that the general knowledge about buying regular TVs is not quite enough for selecting this type of product, so you quickly review what you read about widescreen, black bars, digital tuners and resolution, and hope things would clear out at the store.

You get into the store and suddenly see a dozen HDTV demo sets staring at you.  A salesperson is approaching you, the person's face is familiar; the salesperson is the one that sold you the new dishwasher two weeks ago, now the person is selling HDTVs with authority.  At that point you start feeling worried, but you hang in there." />
	<meta name="title" content="Buying an HDTV?  How to be prepared to select the one for you" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Buying an HDTV?  How to be prepared to select the one for you" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/01/buying-an-hdtv-how-to-be-prepared-to-select-the-one-for-you.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="You have been hearing about HDTV and decided to start looking for one.  A friend of yours reminds you that the general knowledge about buying regular TVs is not quite enough for selecting this type of product, so you quickly review what you read about widescreen, black bars, digital tuners and resolution, and hope things would clear out at the store.

You get into the store and suddenly see a dozen HDTV demo sets staring at you.  A salesperson is approaching you, the person's face is familiar; the salesperson is the one that sold you the new dishwasher two weeks ago, now the person is selling HDTVs with authority.  At that point you start feeling worried, but you hang in there." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=292', 400, 200);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important nolinks"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare nolinks" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/01/buying-an-hdtv-how-to-be-prepared-to-select-the-one-for-you.php">Buying an HDTV?  How to be prepared to select the one for you</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 16, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p class="editorial">The following article originally appeared in HDTVetc magazine in their August 2003 issue. This previously published article contains some product information that is dated to mid-2003, and should not be considered news when reading it today. Although the content has historical value, the primary value is the tutorial substance and my analysis to reach a forecasted vision of future market conditions (that actually happened later in time) which helped many consumers in making the right purchasing decisions. Some statements of the article could be considered time-travel to the future if you project your reading imagination to back then; the vision has now transformed itself into events and conditions that actually happened, and can certainly still guide the public in making an intelligent purchase even in today's market. Enjoy the reading.</p>

<p>You have been hearing about HDTV and decided to start looking for one. A friend of yours reminds you that the general knowledge about buying regular TVs is not quite enough for selecting this type of product, so you quickly review what you read about widescreen, black bars, digital tuners and resolution, and hope things would clear out at the store.</p>

<p>You get into the store and suddenly see a dozen HDTV demo sets staring at you. A salesperson is approaching you, the person's face is familiar; the salesperson is the one that sold you the new dishwasher two weeks ago, now the person is selling HDTVs with authority. At that point you start feeling worried, but you hang in there.</p>

<p>Then you find out by yourself that only one of those HD demo sets is actually displaying HD, the only one that has an HD-tuner; the rest are showing the same image but not in HD quality. The sales person tells you: "trust me, buy this TV, it would look much better at home once connected to an HD-tuner". Would you buy a car without test-driving it?</p>

<p>Many people have been thru similar experiences over the last 5 years, fortunately some improvement is seen in the stores recently. I wrote this article to help you make your purchase with more confidence, but first allow me to summarize the following ...</p>

<p><br />
<h2>DTV (and HDTV) facts</h2><br />
You might already know of the plan to replace our current analog TV system (NTSC) dated from the 1940s by a digital DTV system. DTV has been 15 years in the making. It started on the air in November 1998. HDTV is the quality part of DTV; I will use the term DTV only when addressing the digital system in general.</p>

<p>The DTV standard is composed of 18 digital formats grouped into two levels of quality, as approved by the ATSC (American Television Systems Committee) in 1995:</p>

<p>1) SD: Standard definition, 480i/p viewable vertical resolution lines, with up to 704 total pixels of horizontal resolution, aspect ratio in 4x3 or widescreen 16x9, and</p>

<p>2) HD: High definition, 720p and 1080i/p viewable vertical resolution lines, with respectively 1280 and 1920 total pixels of horizontal resolution, in widescreen 16x9 aspect ratio.</p>

<p>Later in 2000, the Consumer Electronics Association (CEA) created another level in between: ED (enhanced), this promoted the 480p format from SD to ED, among other changes.</p>

<p>Our current NTSC over-the-air (OTA) TV system is 480i analog (actually 525i with 480i viewable lines). Digital satellite and digital cable are equivalent to digital SD but they are also transmitting some of their channels in HDTV.</p>

<p>To facilitate the transition broadcasters were given one extra channel slot from the FCC for the simultaneous broadcasting of the analog and digital versions of their programming. It is a large investment for stations to build a DTV facility with new cameras, equipment, etc. When DTV gets fully implemented broadcasters have to return one of the two channels, analog over-the-air broadcasting will stop, and current TVs, VCRs, Tivos with analog tuners would stop tuning as well.</p>

<p>The DTV system implementation is mandatory; HDTV is optional. Many OTA stations are already broadcasting in HD widescreen and people are buying HD sets at accelerated pace this year. According to this month's CEA statistic a total of approximately 6 million DTVs (300.000 integrated) and 400.000 STBs were sold since 1999.</p>

<p>Back in 1998/9 it was not unusual for first generation HDTV monitors to cost $10,000, and HD set-top-box (STB) tuners to cost from $700 to $3,000. It was expensive for early adopters. Curiously enough the idea started as "analog" HDTV until General Instruments proposed an all-digital system in 1990.</p>

<p>We all love the incredible video quality of HD, but it could also happen that a broadcasting station decides to use the allotted channel space of HD to multicast instead several sub-channels of lower SD quality. It might also be possible that the station desires to use some of the bandwidth for data-casting interactive services. We all hope that HD will reign and HD quality will prevail over digital quantity business models.</p>

<p>There would be some backward compatibility with your current analog equipment, but there is a catch. When DTV gets fully implemented in order for you to watch DTV digital channels you would need a digital tuner. A STB digital tuner would connect to your current NTSC analog TV, which would display a downgraded version of the digital image.</p>

<p>There is no need to rush for the replacement of a TV that might be in good working condition if you just want to continue watching similar quality TV, but you would have to buy a STB digital tuner.</p>

<p>This applies to your analog VCR, DVD recorder, Tivo, etc., if you want to keep the same independent tuning flexibility. DTV STB tuners are relatively expensive now but they are expected to cost much less than the current $400-$1000 price range. Imagine buying a $400 tuner for a $50 VCR.</p>

<p>The FCC has issued a "mandatory" plan to gradually incorporate digital tuners into the manufacturing of DTV monitors and other tuning devices like VCRs, which will make them integrated.</p>

<p>Some analysts anticipate that economies of scale would bring down the price of digital tuners to the level of today's current analog tuners, others estimate they would cost approximately $50 by 2007, but the reality is that new STB models still cost similarly as 1999 models then, while HDTVs came down to under $1000.</p>

<p>Ironically, most of the 6 million people that bought HDTVs did so not to view HD, but rather to enjoy playing widescreen DVDs. Although DVDs in the U.S. are not yet of HDTV quality they certainly display much better on a digital TV as 480p progressive scanning. The same DVD played on an analog TV would only show the image as a 480i interlaced scanning.</p>

<p>In addition, an HDTV has the capability to show widescreen DVDs in anamorphic format displaying all the original vertical resolution stored on the disc, while 4x3 analog TVs would show the same DVD by letterboxing the image between top/bottom bars in order to maintain the wider aspect ratio of the movie, and with less vertical resolution for the image itself.</p>

<p>Abundant HDTV information has been released about general concepts, types of TVs, technical specifications, such as resolution, scanning, etc. This and other magazines have covered good ground to help educate readers with the concepts.</p>

<p>It is hard to digest and consolidate all that information and be ready for buying a set without some practical guidelines as a copilot; this is the objective of this article.</p>

<p>Let us get started.</p>

<p><br />
<h2>Decision to go with HDTV</h2><br />
Confirm that now is your time for an HDTV, and that your family is ready for the change if the set is for everyone to use.</p>

<p>Identify the major pieces of equipment you plan to acquire. Maybe you need only the HDTV, maybe also a progressive DVD player, or an HD-STB. Would you need any audio upgrades/purchases if the HDTV set would be connected to an external audio system?</p>

<p>Set a preliminary itemized budget based on the initial target above.</p>

<p><br />
<h2>Research</h2><br />
Review HDTV concepts and exchanges from online forums and magazines and begin to be patient while learning terminology that seems confusing first (progressive, 720p, 1080i, line-doubling, DVI, etc).</p>

<p>Visit knowledgeable HDTV stores and be extra patient if you get more confused with verbal explanations that seem to contradict what you might have read already, many salespeople are still in training, take your time in reconciling the inputs.</p>

<p>Many early adopters did not have the benefit of all the HDTV information available today. You have now at your feet many sources to inform yourself and research in order to make an intelligent purchase. Do not make the mistake of using the approach you would if buying a 13'' TV for the kitchen.</p>

<p>Learn how to identify the differences in technology (DLP, Plasma, LCD, CRTs, etc). Confirm your understanding of the differences by actually viewing some sets representing each technology in various sizes, distances, angles and lighting conditions. Not all technologies would be as effective for all room conditions.</p>

<p><br />
<h2>Understand the characteristics of the type of system you need for your viewing objectives</h2><br />
Once you comprehend the differences and capabilities of DTV technologies, you would have to concentrate on how they could apply to your viewing objectives. Evaluate if you would need a 42" plasma panel suitable for a studio or a 120" front projection screen for a home-theater in the basement.</p>

<p>Does your viewing room have uncovered windows all the time? This could be too much light for a front projection TV. Do you have enough depth on the room to allow for approximately 2 feet of depth of a CRT rear projection TV cabinet and still have enough distance between the screen and yourself to adjust your viewing position?</p>

<p>Appraise your "preferred" viewing distance and screen size for your viewing objectives. Identify also the absolute minimum and maximum of the viewing range that your room would physically permit and still give you a pleasant viewing experience. You would need to use these limits while testing the viewing positions at the store.</p>

<p>Due to the higher resolution of HDTV images they would permit you to sit much closer to the screen than what you are accustomed with regular analog TV, but remember that you might have to use the same set to also view a lot of non-HDTV material, which does not have enough resolution to sit as close as an HD image would allow.</p>

<p>Identify the cable/satellite/over-the-air HD reception services available on your area. Ask for their HD plans on the near future, the adoption of HDTV by the cable industry is moving faster this year but it might not be the case for your area. Consider some factors that you might not be able to control, for example, a satellite dish's line of sight might be blocked by tree lines that can not be trimmed, UHF antennas might be a problem for your terrain/location/house/surrounding city buildings, etc.</p>

<p>Inform yourself of the FCC antenna regulations on their web site if your Home Owners Association is rejecting the installation of your exterior antenna, the FCC is on your side and has authority over the HOA neighborhood regulations, similar to the small dish regulations a few years ago.</p>

<p>Anticipate how much of non-HDTV 4x3 content you would still watch on your new HDTV set over the next few years. Only a few channels of the satellite and cable line-up of 200 + channels are in HDTV, most are, and still could be, in digital 480i 4x3.</p>

<p>Evaluate if your viewing will be primarily/uniquely for TV content or for pre-recorded movies. For example, if the system is to exclusively watch your own movies you might just need to invest in an HDTV "monitor" and a good progressive DVD player (or an HD-VHS VCR to playback HD movies on tape).</p>

<p>In such case you would have no need for the tuner of an integrated set or for an HD-STB digital tuner, this could save you $400/$1000, and you can add the tuner later. As I mentioned before, the majority of HDTVs sold over the last 5 years were bought to watch DVD movies in progressive-scan 480p, this might be your case as well.</p>

<p>Review the equipment plan that you identified initially. Make sure the HDTV would be used either as a stand-alone set, or as the centerpiece of a home-theater audio/video multi-channel system. If so, confirm also the need for changes/upgrades of your current audio setup, such as replacing a stereo receiver by multichannel audio for movies, and their corresponding additional speakers.</p>

<p><br />
<h2>Recognize your buying/upgrade electronic habits. Are they applicable to your HDTV?</h2><br />
HDTV is still haunted by many issues that are not yet fully resolved, such as digital connectivity, copy protection, recording ability, etc., those have the potential to make your purchase and upgrade path more confusing and frustrating.</p>

<p>Some people replace TV sets only after their CRT tubes expire with their tongs out on their family room, some others like to upgrade often to been able to experience minor improvements on image, these usually have their wallets regularly dominated by minor technology changes. The cost of owing HDTV/s within a 10-year period would be different for each case, identify which is your case and how that affects your budget and/or selection.</p>

<p>The HDTV technology evolves constantly and you need to be tolerant if you get surprises of discontinued products two months after you bought the set. Some manufacturers replace their lines 2 to 3 times per year just to add some minor features.</p>

<p>If you are planning to upgrade again by getting another HDTV set in a couple of years maybe it is not that important to select a first one that has absolutely all of the bullet-proof features for long term compatibility (or waiting for those features to become available), like HDMI digital connectivity for example (a set with DVI, or even with just component inputs, could suffice). By evaluating your consumer electronics habits and long term cost of ownership you may confirm the selection and features with more clarity.</p>

<p><br />
<h2>Understand how NTSC would display on an HDTV. Understand the widescreen viewing factors</h2><br />
If you are switching from a regular direct-view 25" CRT tube TV to a much larger 65" projection TV, begin to recognize the compromises you might need to make regarding image quality and screen size when viewing NTSC analog content on that HDTV set.</p>

<p>An HDTV set will display a line-doubled version of a current NTSC analog 480i image, but the resultant image could still be of insufficient detail for pleasant viewing if the screen is too large. The effect could be compared to over-enlarging a photograph that does not have enough picture elements. The image could look even worst when using the expansion modes to fill a 16x9 screen.</p>

<p>There would still be a lot of NTSC programming broadcasted until DTV is fully implemented. Your new HDTV set would unfortunately show all the imperfections of that analog image, for several years to come.</p>

<p>Some new video processing technologies introduced in 2003 sets improve NTSC sources considerably by doubling or quadrupling the pixels, but there is so much it can be done to an image that lacks sufficient original resolution for large displays. Be prepared to perform some tests with NTSC sources before you buy. Do not buy a screen larger than needed based only on how good the HDTV channels look.</p>

<p>Imagine for a moment this scene, the set is already at home and you and your wife begin the wonderful HD viewing, you change the channel to a non-HDTV station, your wife holds her breath for a second, and finally turns to you and tells you "honey, the older TV looked better, why pay this much for a grainy picture?"</p>

<p>Begin to recognize the existence and the reason of various aspect ratios and their relationship to Hollywood movies, DVDs and 16x9 HDTV, and how they differ from the regular 4x3 aspect ratio of the regular NTSC TV.</p>

<p>Begin to understand and accept black bars, left/right or top/bottom, and in some extreme cases even all four at once. Many widescreen movies (i.e., 2.35:1) are wider than the frame of a 16x9 HDTV (1.78:1), they would still show with top/bottom black bars to maintain correct geometry of the image. A 4x3 TV (analog or digital) would show those black bars even larger.</p>

<p>Learn to recognize that even though the top/bottom bars give you the illusion that you are missing part of the image above and below, you are actually viewing all the left/right wider content intended by the director that in a 4x3 image you would not see.</p>

<p>Understand that if you use the expansion modes for a 4x3 image to fill the 16x9 frame of the TV you are altering image geometry and also cutting out the content that overflowed the edges of the TV frame. Test TVs for 4x3 image expansion modes and scrolling capabilities. Expansion modes are not standard across manufacturers. Some TVs do not have scrolling features. The TV you like might have been made by a manufacturer that had chosen expansion modes you dislike.</p>

<p>Some expansion modes cut "big" heads of people right above their eyes on the image, or make people bodies look too fat, or make disappear from the bottom of the screen things like movie subtitles, Bloomberg stock quotes, ESPN match scores, or even a tennis player behind the baseline.</p>

<p>If a 16x9 TV does not have image scrolling capabilities to allow you to move an expanded 4x3 image up or down it would mean that in order to see the hidden content of the top/bottom edges of the image, you might end up viewing NTSC as plain 4x3, with the sidebars you dislike, and with the risk of side bar burning on prolonged viewing.</p>

<p>Consider also that if you choose to view 4x3 on a 16x9 TV the actual image size is much smaller than the TV screen size and its impact might not be as satisfying as you originally expected based on 16x9 images.</p>

<p>Begin to educate your family regarding these aspect ratio issues and about some viewing adjustments they might need to make to reduce the risk of damaging CRTs or plasma panels when playing video games with two side black bars and showing fixed logos and game scores for prolonged periods.</p>

<p>To avoid the burning effect of fixed objects on the image some clever sets shift the entire image at intervals in a way that is not noticed by the viewer. But many sets do not have that feature and it is up the owner to know how to use some safe viewing methods (such as periodically using expansion modes, avoid high contrast settings, etc).</p>

<p>Expansion modes that are unacceptable or the lack of scrolling features might have the potential to eliminate certain sets from the list regardless how good they might look in HD. Test them well at the store.</p>

<p><br />
<h2>Get ready for the actual viewing</h2><br />
When viewing and comparing sets verify on the TV menu that video controls are set at mid-point (contrast is usually set to the maximum by most manufacturers when they deliver the sets), many sets are left with the color controls altered by other customers, set the color temperature as standard or 6500 Kelvin (the warm setting would make the image more red, the cool setting more blue), turn off scan velocity modulation, check the CRT convergence (if you know how to do it).</p>

<p>Some sets have automatic convergence, but if not, do at least the simple convergence with the cross at the center of the screen, although the multipoint convergence is encouraged when reaching the last phase of finalists.</p>

<p>There is no value in viewing comparisons unless these adjustments are made; and even then, be aware that side-by-side in-depth comparisons are difficult to be performed properly unless the sets are well calibrated and the viewing is done in a controlled environment, an option not available at the stores (convergence itself might be questioned by sales personnel).</p>

<p>Many HDTV demos are done with still images of colorful flowers; TV is not about projecting slides. Test with fast moving images. You might notice jagged edges, pixelation errors, macro-blocking, pixel over-activity (like ants moving), etc.</p>

<p>The TV itself might not necessarily be the one responsible for all those errors but it is helpful to learn how to detect errors and how to identify their possible source (the HD-STB, the TV, the broadcast, the digital compression, the limited band-width allocated for that HD signal, the signal strength on the reception, etc.).</p>

<p>Knowing the source would also aid on equipment upgrades, like confirming the need to replace an HD-STB rather than a more expensive HDTV. Some Internet forums could help you develop awareness to detect imaging errors.</p>

<p>View content originated from true HDTV video cameras such as HD-Net, but also from film based material such as movies from HBO and Showtime. View ABC or ESPN HD programming to test the conversion from their 720p broadcast to 1080i if applicable (the conversion is made either by the integrated TV or by the HD-STB).</p>

<p>View NTSC video sources of diverse horizontal resolutions (such as DVD, VHS, antenna, satellite, cable), notice the effectiveness in line-doubling 480i material (to 480p or 540p for example) and how the doubling reacts when the 4x3 image is expanded by the TV modes. Evaluate if the viewing distance for those line-doubled images is far enough, or you need to sit further back to tolerate possible deficiencies.</p>

<p>View material from regular satellite/cable. Their typical over-compression is known to worsen fast action images; a large screen HDTV could make it more obvious, test with some basketball action not a golf match.</p>

<p>If after the combination of all the above you need to move too far back from the screen for NTSC material, it might have ruined the widescreen impact you expected, or might cause you to consider a smaller screen, or to replace your service provider, etc. Perhaps you would have to live with it, and this is the time to be made aware of, before you buy the set.</p>

<p><br />
<h2>How the HD video experience interact with the rest of the system, sitting and room arrangements</h2><br />
While performing the viewing tests decide if your sitting arrangement would need to be movable to adapt to the quality of the viewing material (closer for HDTV, farther for NTSC), or perhaps it would be better to select a fixed viewing position at a compromise point adequate to the viewing of both.</p>

<p>Viewing distances might look well at the store but might be physically constrained by your actual room dimensions, and your planned sitting arrangements; remember to use your room's viewing distance measurements for store tests and confirm at home. You might conclude that is better to reduce the screen size for all things to fit.</p>

<p>Assuming you will use the HDTV with a multi-channel audio system (otherwise disregard all the statements relevant to this subject), evaluate how moving a couple of feet away from the screen to make an image acceptable might affect the audio sweet spot due to standing waves, listener distance from the speakers, and room boundaries. This most probably would be done independently (video at the store, audio at home using the video distances confirmed at the store).</p>

<p>You could conclude that is better to relocate the speakers so the multichannel sweet spot matches the viewing spot. Or you could start with a good sound sweet spot and adapt the screen size of the HDTV so the viewing sweet spot coincides with the sound sweet spot. If that is your case you better know all this in advance, before you commit to a screen size based only on viewing reasons.</p>

<p>If all this gets too complicated and you just want to keep it simple with some surround in the room, you should concentrate on the viewing factors of this article.</p>

<p>While we are on this topic I would like to mention that the use of TV's small speakers (and small TV amps) as alternative for a missing center channel is not recommended as a permanent home-theater setup. The dialog and much of the sound of a movie comes from the center channel, some have estimated it in the order of 60% of the movie soundtrack.</p>

<p>When using the TV's small amp/speakers in a home theater their capacity would be exceeded much earlier than the external L/R speakers/amp (assuming is larger than the TV audio, as typically is). The effect could be worst if the system does not have a subwoofer to redirect low frequencies from a small center and surrounds. The distortion on the center channel would affect the clarity of the dialog over loud passages.</p>

<p>Additionally, sounds that are panning side-to-side would have different timbre while switching among speakers (from left to center to right) accompanying the video movement in that direction. Voices of people walking side-to-side will change their tone as they enter the TV's center speaker and as they depart from it.</p>

<p>While these are home-theater considerations, if the HDTV is to become a centerpiece for your home theater then the issues would require attention sooner or later. Plan for installing front (L/C/R) speakers with matching timbre and size, plan for equal amplification for those.</p>

<p><br />
<h2>Examine your recording needs and the required digital connections</h2><br />
There are three manufacturers of HD-VHS VCRs in the market now; the VCRs record HD from their IEEE1394 inputs only. You need to be aware of how this relates to your integrated HDTV and HD-STB regarding connectivity.</p>

<p>Decide if HD recording is a feature you would need today. Perhaps it could be sufficient for now to record HD content as a SD downgraded analog version on a regular VCR or DVD recorder.</p>

<p>Sony has introduced HD-DVD recording in Japan in April of this 2003, for $3,800, with an HD satellite tuner. We will have to wait for its introduction in the US.</p>

<p>Evaluate if you just need a PVR (Personal Video Recorder, similar to Tivo) for time shifting, or an actual HD-VHS for video archival. HD-PVRs for OTA started to appear in 2003 for around $1000.</p>

<p>An integrated HDTV (or HD-STB) should have an IEEE1394 (Firewire) output to been able to send the tuned HD signal to the HD recorder, otherwise you would not be able to record HD. Some integrated sets have IEEE1394 connectors but without output capabilities.</p>

<p>Be aware that most HD prerecorded VHS tapes available use D-Theater protection, playable on JVC and Marantz compatible HD-VHS VCRs, but not on the Mitsubishi HD-VHS VCR.</p>

<p><br />
<h2>Analyze if you need a separate HD-STB or an integrated TV. Connectivity issues</h2><br />
HDTV monitors have analog component video inputs to which HD-STBs could connect to; in the future such connection "might" be subjected to copy protection viewing restrictions. DVI has been adopted to deter unlawful digital copying (by using HDCP copy protection). HDMI is targeted as the successor of DVI.</p>

<p>Make sure the HDTV (integrated or monitor) has DVI inputs to connect HD-STBs with DVI outputs, the more inputs the better (some new DVD players have DVI outputs). Satellite HD tuners come only as separate HD-STBs, and they use DVI and component outputs to connect to DTVs. Confirm that the DVI connections are HDCP compliant; some plasma panels have DVI to connect to PCs but are not HDCP compliant.</p>

<p>Check that the HD-STB can simultaneously send out the HD and SD signals to facilitate HD viewing while recording the down-converted SD version on a regular VCR or while distributing it for home-networking; some STBs can not do this simultaneously.</p>

<p>The FCC has mandated an implementation plan for all manufacturers to gradually integrate HD tuners into the TVs and recording devices; likewise, there is a recent plug-and-play agreement with the cable industry to also include HD-cable tuners with a POD (Point Of Deployment) card named "CableCARD". It remains to be seen how both will be implemented. Some people believe that integrated HDTVs are practical and would accelerate DTV adoption since 70% of the U.S. population subscribes to cable.</p>

<p>An integrated set cost about the same as the monitor TV version plus a separate HD-STB tuner, and in some cases more. Cable-subscribers would be paying extra for an integrated TV that has an over-the-air tuner (that would not actually be used), and vice versa. Neither person would have the choice to just pay for the tuner they need (or for no tuner at all) if integrated within all sets. Current tuners cost $400/$1000.</p>

<p>If you were a satellite customer you would have no use for the OTA/cable tuners within integrated HDTVs. During the first 5 years of HDTV, early adopters criticized the operation and reliability of many HD tuners. Having those tuners within separate STBs would facilitate their service or replacement.</p>

<p>Many new 2003 HDTVs have DVI/HDCP connections, but no HDMI. Only OTA STBs are being introduced with IEEE1394 for recording; the cable agreement is gradually incorporating this feature into cable-tuners. DirecTV favors DVI-only STBs, meaning no HD recording. For almost 2 years Dish Network is being announcing the release of their (921) PVR STB with DVI/component/IEEE1394, finally out, but without 1394, and later discontinued without such connection.</p>

<p>In summary, be aware of some of the pros and cons of integrated vs. monitor sets, the connectivity issues with DVI and its future replacement by HDMI (and both using HDCP for the viewing of future copy protected content, if implemented), and connectivity issues for recording HD-VHS using IEEE1394, especially if you buy an integrated set.</p>

<p><br />
<h2>Other items to consider (controls, cables, screen shields, etc)</h2><br />
Most OTA HD reception is UHF, if you are to receive HDTV OTA, plan for having one antenna installed; even on the attic an antenna could receive good HDTV signals. Budget for good quality wiring, especially the HD video cable.</p>

<p>Think about children around delicate lenticular screens lacking shield protection. Some sets have non-removable shields that reflect back room light that you might not notice at the store.</p>

<p>Sometimes replacing a regular TV with an HDTV/home theater set-up becomes a nuisance for some family members due to the complexity of the system controls. Separate HDTV wiring for stand-alone use could help.</p>

<p>Consider performing an ISF (Imaging Science Foundation) calibration after 300 hours of viewing, until then use a calibration-DVD. Most TVs improve considerably after ISF calibration. Check your manufacturer's warranty about ISF.</p>

<p><br />
<h2>Purchase the HDTV</h2><br />
Check for store policies regarding delivery, installation, extended warranties, and problem resolution policies.</p>

<p>Buy from a reputable store that could protect you when a heavy HDTV has problems, particularly in the case of the delivery and installation of delicate plasma panels.</p>

<p>Enjoy HDTV.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 16, 2006  1:09 PM</b>
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
			<?=getComments(292)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 292)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/01/buying-an-hdtv-how-to-be-prepared-to-select-the-one-for-you.php" type="text/javascript" charset="utf-8"></script>
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