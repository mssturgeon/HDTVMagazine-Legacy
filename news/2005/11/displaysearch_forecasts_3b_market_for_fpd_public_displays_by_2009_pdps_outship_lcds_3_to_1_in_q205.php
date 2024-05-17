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
		AND e.entry_id = 264";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 264 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 264 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 264";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2005/11/displaysearch-forecasts-3b-market-for-fpd-public-displays-by-2009-pdps-outship-lcds-3-to-1-in-q205.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 264";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2\'05" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2\'05" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2\'05" />
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
	<title>HDTV Magazine - DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05</title>
	<meta name="keywords" content="public display, public displays, lcd public, text decoration, npd group, public, display, displays, displaysearch, pdp, lcd, size, font, worldwide, market, total, nec, color, technology, npd, share, pdps, lcds, forecast, fpd" />
	<meta name="description" content="AUSTIN, TEXAS, December 1, 2005-DisplaySearch, an NPD Group Company and the worldwide leader in display market research, issued the first edition of its newest Quarterly Public Display Shipment and Forecast Report indicating that worldwide shipments of large flat-panel displays for non-TV applications reached a worldwide total of 87K units in Q2'05. While this number was slightly down from the Q1'05 worldwide total of 96.4K units, recent price reductions in both plasma (PDP) and large-format LCD (26&quot;+) panels are forecast to accelerate this market to over 400K units for 2005, and double-digit unit Y/Y growth is forecast through 2009 to reach 1.8M units. Conservative estimates of the worldwide market show the total Compound Annual Growth (CARG) rate for PDPs used in such applications to be as much as 10% from 2004-2009 with LCDs forecast to have an even larger CARG of 82% from 2004 to 2009! , resulting in an overall explosive 36% CARG for the flat panel public display market.

" />
	<meta name="title" content="DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2005/11/displaysearch-forecasts-3b-market-for-fpd-public-displays-by-2009-pdps-outship-lcds-3-to-1-in-q205.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="AUSTIN, TEXAS, December 1, 2005-DisplaySearch, an NPD Group Company and the worldwide leader in display market research, issued the first edition of its newest Quarterly Public Display Shipment and Forecast Report indicating that worldwide shipments of large flat-panel displays for non-TV applications reached a worldwide total of 87K units in Q2'05. While this number was slightly down from the Q1'05 worldwide total of 96.4K units, recent price reductions in both plasma (PDP) and large-format LCD (26&quot;+) panels are forecast to accelerate this market to over 400K units for 2005, and double-digit unit Y/Y growth is forecast through 2009 to reach 1.8M units. Conservative estimates of the worldwide market show the total Compound Annual Growth (CARG) rate for PDPs used in such applications to be as much as 10% from 2004-2009 with LCDs forecast to have an even larger CARG of 82% from 2004 to 2009! , resulting in an overall explosive 36% CARG for the flat panel public display market.

" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=264', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/11/displaysearch-forecasts-3b-market-for-fpd-public-displays-by-2009-pdps-outship-lcds-3-to-1-in-q205.php">DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>November 30, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p><HTML><HEAD><TITLE>DisplaySearch: Press Release</TITLE><br />
<!link rel="stylesheet" href="/displaysearch/site/scripts/fonts.css" type="text/css"><br />
<style type="text/css"><br />
	BODY			{ margin: 0px 0px; font: 12px Arial, Helvetica, Verdana, Geneva, sans-serif; }<br />
	TABLE, TD		{ font: 10px Verdana, Arial, Helvetica, Geneva, sans-serif; word-spacing: 1px; color: #606060;}	/*line-height: 25px; letter-spacing: 5px;*/<br />
	.title_head		{ font-weight: bold; font-size: 18px; text-indent: 38px; }<br />
	.title 			{ font-weight: bold; font-size: 12px;  }	/* color: #720517; */<br />
	a:link			{ color:006FA2; text-decoration:none; }<br />
	a:visited		{ color:006FA2; text-decoration:none; }<br />
	a:hover			{ color:006FA2; text-decoration:underline; /* font-weight : bold; #720517*/ }<br />
	a.news			{ font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; font-size:10px; color:606060; text-decoration:none; }/*, 006699*/<br />
	a.news:hover	{ font-family: Verdana, Arial, Helvetica, Geneva, sans-serif; font-size:10px; color:006FA2; text-decoration:underline;}<br />
.style1 {color: #FFFFFF}<br />
</style></p>

<p></HEAD><br />
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LEFTMARGIN="0" RIGHTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0"></p>

<center>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td colspan="3" BGCOLOR="#333333">&nbsp;<br><br></td></tr>
<tr><td BGCOLOR="#333333" width="50%">&nbsp;</td>
	<td><table border="0" cellpadding="0" cellspacing="0" width="718">
		<tr><td 
background="http://www.displaysearch.com/press/pr_email/v4/press-head-1.gif"
width="582" class=title_head>	
				Press Release
				&nbsp;
			</td>
			<td width="136"><a href="http://www.displaysearch.com/"><img 
	src="http://www.displaysearch.com/press/pr_email/v4/press-head-logoNPD.gif" 
			alt="DisplaySearch" width="136" height="101" border="0"></a></td>
		</tr>
		</table>
	</td>
	<td BGCOLOR="#333333" width="50%">&nbsp;</td>
	</tr>
<tr><td BGCOLOR="#333333">&nbsp;</td>
	<td bgcolor="#FFFFFF">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr><td width="100" bgcolor="#f9c252">&nbsp;</td>
			<td width="10">&nbsp;</td>
			<td><br>
<!-- ---- Begin Content ---- -->			

<p>Thursday, December 1, 2005<br />
<p><span class=title>DisplaySearch Forecasts $3B Market for FPD Public Displays by 2009; PDPs Out-Ship LCDs 3 to 1 in Q2'05 </span></p>

<p>AUSTIN, TEXAS, December 1, 2005-DisplaySearch, an NPD Group Company and the worldwide leader in display market research, issued the first edition of its newest <B><I><a href="http://www.displaysearch.com/products/?pn=pub_disp_ship">Quarterly Public Display Shipment and Forecast Report</B></I></a> indicating that worldwide shipments of large flat-panel displays for non-TV applications reached a worldwide total of 87K units in Q2'05.  While this number was slightly down from the Q1'05 worldwide total of 96.4K units, recent price reductions in both plasma (PDP) and large-format LCD (26"+) panels are forecast to accelerate this market to over 400K units for 2005, and double-digit unit Y/Y growth is forecast through 2009 to reach 1.8M units. Conservative estimates of the worldwide market show the total Compound Annual Growth (CARG) rate for PDPs used in such applications to be as much as 10% from 2004-2009 with LCDs forecast to have an even larger CARG of 82% from 2004 to 2009!
 , resulting in an overall explosive 36% CARG for the flat panel public display market.</p>

<p>With production gearing up for larger flat panel displays for the consumer TV market, the exploding Public Display market is benefiting from these economies of scale with similar displays used for non-TV applications such as Point of Sale Signage, Flight Information Displays, Electronic Menu Boards and even corporate conference room displays.  The two most popular technologies for the out-of-home Public Display environment are LCDs and PDPs, both of which continue to push the technology envelope in size, resolution and price points.  LCDs as large as 55" are now commercially available worldwide, while PDPs as large as 71" are now available for commercial applications with signs that these two technologies will both gain ground in this emerging space.</p>

<p>Q2'05 results show that the most popular size and technology for Public Display applications was the 40-43" PDPs with a 63.6% share; lower resolution ED versions of 42" PDPs had a 38.8% share alone.  The 40-43" PDP category was followed by the 50" PDP size, which had a 12.3% share of the worldwide shipments of Public Displays in Q2'05.  LCD based 40" Public Displays were the third best selling size and technology with an 8.0% share for the quarter.  While PDP products currently rule the Public Display market place, large-format LCDs (26"+) have shown the greatest Q/Q shipment growth since Q2'04 with positive Q/Q growth in all quarters surveyed with the exception of Q2'05, with explosive growth projected to be reported for Q3'05.</p>

<p><span class=title>Table 1: PDP and LCD Public Display Quarterly Growth Rates</span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF"> </font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q4'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q1'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q2'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'05*</font></strong></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">LCD Public Displays (26"+)</p></td>
    <td width="10%"><p align="center">36% </p></td>
    <td width="10%"><p align="center">17% </p></td>
    <td width="10%"><p align="center">13% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
    <td width="10%"><p align="center">45% </p></td>
  </tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">PDP Public Displays (32"+) </p></td>
    <td width="10%"><p align="center">11% </p></td>
    <td width="10%"><p align="center">-2% </p></td>
    <td width="10%"><p align="center">-9% </p></td>
    <td width="10%"><p align="center">11% </p></td>
    <td width="10%"><p align="center">14% </p></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">Total WW FPD Public Displays </p></td>
    <td width="10%"><p align="center">14% </p></td>
    <td width="10%"><p align="center">1% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
    <td width="10%"><p align="center">-10% </p></td>
    <td width="10%"><p align="center">20% </p></td>
  </tr>
</table>
<ul><i>*Projected</ul></i>

<p>Street level pricing for Public Displays in the period dropped from a total Weighted Average of $3,616 in Q2'04 to $3,425 in Q2'05 with projections that on a weighted average basis pricing will fall as low as $3,032 once the results from Q3'05 are fully posted.  These price reductions were driven by the significant decline in PDP pricing, especially in the 40-43" ED PDP category, which dropped 12.1% in Q4'04 into the sub-$2,299 range thus increasing unit volume shipments.  Additionally, price reductions in the 40-42" LCD class of Public Displays helped accelerate sales, even though this technology is 1.5x more expensive than its nearest HD PDP competitor.</p>

<p><span class=title>Table 2: PDP and LCD Public Display Historical Pricing Sequential Changes</span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF"> </font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q4'04</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q1'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q2'05</font></strong></td>
    <td width="10%" align="center" class=table_title><strong><font color="#FFFFFF">Q3'05*</font></strong></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">40-42" LCD Sequential Change</p></td>
    <td width="10%"><p align="center">-16% </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-7% </p></td>
    <td width="10%"><p align="center">-6% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
  </tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">40-43" ED PDP Sequential Change </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-12% </p></td>
    <td width="10%"><p align="center">1% </p></td>
    <td width="10%"><p align="center">8% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
  </tr>
  <tr >
    <td width="50%"><p align="center">40-43" HD PDP Sequential Change </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-16% </p></td>
    <td width="10%"><p align="center">-2% </p></td>
    <td width="10%"><p align="center">7% </p></td>
    <td width="10%"><p align="center">-26% </p></td>
  </tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">Total Public Display Weighted Average Sequential Change </p></td>
    <td width="10%"><p align="center">-2% </p></td>
    <td width="10%"><p align="center">-5% </p></td>
    <td width="10%"><p align="center">1% </p></td>
    <td width="10%"><p align="center">0% </p></td>
    <td width="10%"><p align="center">-12% </p></td>
  </tr>
</table>
<ul><i>*Projected</ul></i>

<p>In the emerging area of LCD Public Displays, NEC had been the worldwide leader (operating under NEC-Mitsubishi prior to Q2'05 then under NEC Display Solutions thereafter) from Q3'04 through Q2'05 when it lost the lead to Samsung with 33.0% to NEC's 32.4% for the quarter. NEC is projected to again top the worldwide charts in Q3'05.  NEC has consistently been the North American leader, currently enjoying the top NA position in the LCD category in Q2'05 with a 41.1% share.</p>

<p><span class=title>Table 3: Q2'05 Worldwide Major Brand LCD Public Display Shares </span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">Rank</font></strong></td>
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF">Manufacturer</font></strong></td>
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">WW Share</font></strong></td>
</tr>
  <tr >
    <td width="25%"><p align="center">1 </p></td>
    <td width="50%"><p align="center">Samsung</p></td>
    <td width="25%"><p align="center">33% </p></td> 
</tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">2 </p></td>
    <td width="50%"><p align="center">NEC Display Solutions </p></td>
    <td width="25%"><p align="center">32.4% </p></td>    
</tr>
  <tr >
    <td width="25%"><p align="center">3 </p></td>
    <td width="50%"><p align="center">LGE </p></td>
    <td width="25%"><p align="center">29.1% </p></td>    
</tr>
  <tr  bgcolor="#CCCCCC">
    <td width="25%"><p align="center">4 </p></td>
    <td width="50%"><p align="center">iiyama </p></td>
    <td width="25%"><p align="center">3.8% </p></td>   
</tr>
  <tr >
    <td width="25%"><p align="center">5 </p></td>
    <td width="50%"><p align="center">Mitsubishi </p></td>
    <td width="25%"><p align="center">1.9% </p></td>    
</tr>
  <tr bgcolor="#CCCCCC" >
    <td width="25%"><p align="center"> </p></td>
    <td width="50%"><p align="center">Total </p></td>
    <td width="25%"><p align="center">100% </p></td>    
</tr>
</table>

<p><span class=title>Table 4: Q2'05 North American Major Brand LCD Public Display Shares </span></p>
<table width="80%" cellpadding="0" cellspacing="0" align="center"  bordercolor="#999999" rules="all" frame="below">
  <tr  bgcolor="#333333">
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">Rank</font></strong></td>
    <td width="50%" align="center" class=table_title><strong><font color="#FFFFFF">Manufacturer</font></strong></td>
    <td width="25%" align="center" class=table_title><strong><font color="#FFFFFF">NA Share</font></strong></td>
</tr>
  <tr >
    <td width="25%"><p align="center">1 </p></td>
    <td width="50%"><p align="center">NEC Display Solutions</p></td>
    <td width="25%"><p align="center">41.1% </p></td> 
</tr>
  <tr  bgcolor="#CCCCCC">
    <td><p align="center">2 </p></td>
    <td width="50%"><p align="center">Samsung </p></td>
    <td width="25%"><p align="center">31.0% </p></td>    
</tr>
  <tr >
    <td width="25%"><p align="center">3 </p></td>
    <td width="50%"><p align="center">LGE </p></td>
    <td width="25%"><p align="center">21.9% </p></td>    
</tr>
  <tr  bgcolor="#CCCCCC">
    <td width="25%"><p align="center">4 </p></td>
    <td width="50%"><p align="center">iiyama </p></td>
    <td width="25%"><p align="center">6.0% </p></td>   
</tr>
  <tr >
    <td width="25%"><p align="center">5 </p></td>
    <td width="50%"><p align="center">Mitsubishi </p></td>
    <td width="25%"><p align="center">0.0% </p></td>    
</tr>
  <tr bgcolor="#CCCCCC" >
    <td width="25%"><p align="center"> </p></td>
    <td width="50%"><p align="center">Total </p></td>
    <td width="25%"><p align="center">100% </p></td>    
</tr>
</table>

<p>DisplaySearch's newest <B><I><a href="http://www.displaysearch.com/products/?pn=pub_disp_ship">Quarterly Public Display Shipment and Forecast Report</B></I></a> includes shipment and forecast data for both LCD and PDP-based FPD Public Displays quarterly through 2009 with historical shipments by brand, by size, by region for LCD Public Displays and historical PDP total WW shipments by size from Q2'04 through the present. This new report also provides cost forecasts of LCD and PDP modules used in Public Display environments as well as projected average street prices by size and by technology through 2009. This report is delivered in PowerPoint and includes enhanced Excel data tables.  Please contact Arie Braun at DisplaySearch at 512-459-3126 or  <a href="mailto:arie@displaysearch.com">arie@displaysearch.com</a> for subscription information.

<p><B>Register for US FPD by December 31 and save!</B>  DisplaySearch will also hold its <B><I><a href="http://www.displaysearch.com/usfpd2006">8th Annual DisplaySearch US FPD Conference</a></I></B> at the beautiful Loews Coronado in San Diego on March 21-23, 2006.  Save $200 by signing up as an attendee by December 31,2005.  Sponsorships and exhibits are also available now!  Please visit the official conference website at <a href="http://www.displaysearch.com/usfpd2006">www.displaysearch.com/usfpd2006</a> or contact Kendra Smith at 512-459-3126 x107 or <a href="mailto:kendra@displaysearch.com">kendra@displaysearch.com.</a></p>

<p><B>About DisplaySearch</B><br />
<p>DisplaySearch, an NPD Group company, has a core team of 33 employees located in North America and Asia who produce a valued suite of market forecasts, technology assessments, surveys, studies and analyses. The company also organizes influential events worldwide. Headquartered in Austin, Texas, DisplaySearch has regional operations in Chicago, Hong Kong, Houston, Kyoto, San Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is on the web at <a href="http://www.displaysearch.com">www.displaysearch.com.</a></p></p>

<p><B>About The NPD Group Inc.</B><br />
<p>Since 1967, The NPD Group has provided reliable and comprehensive consumer and retail information for a wide range of industries. Today more than 1,400 manufacturers and retailers rely on NPD to help them better understand their customers, product categories, distribution channels and competition in order to help guide their business. Information from The NPD Group is available for the following major vertical sectors: apparel, appliances, automotive, beauty, consumer electronics, food and beverage, foodservice, footwear, home improvement, housewares, imaging, information technology, music, software, travel, toys, video games, and wireless. For more information, visit <a href="http://www.npd.com">www.npd.com.</a></p></p>

<p></p>

<p>			<br />
<!-- ---- End Content ---- -->			<br />
				<br><br><br />
			</td><br />
			<td width="70">&nbsp;</td><br />
		</tr><br />
		</table><br />
	</td><td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
<tr><td BGCOLOR="#333333">&nbsp;</td><br />
	<td><a href="http://www.displasearch.com/"><img <br />
	src="http://www.displaysearch.com/press/pr_email/press-foot.gif" <br />
	alt="" width="718" height="48" border="0"></a></td><br />
	<td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
<tr><td BGCOLOR="#333333">&nbsp;</td><br />
	<td BGCOLOR="#333333" align="right"><br />
		<font size="-2" color="#666666"><br />
		<a href="mailto:listserve@displaysearch.com?subject=unsubscribe">unsubscribe</a><br><br />
		&copy; 2005 <a href="http://www.displaysearch.com/"><font color="#777777">DisplaySearch</font></a><br />
		&bull; Site by <a href="http://www.gogocreative.com/"<br />
		onMouseOver="self.status='Gogo Creative'; return true"><font color="#777777">GoGo Creative</font></a><br />
		</font><br />
		<BR><BR><BR><BR><BR><BR><br />
	</td><br />
	<td BGCOLOR="#333333">&nbsp;</td><br />
</tr><br />
</table><br />
</center><br />
<br><br><br></p>

<p></BODY><br />
</HTML></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>November 30, 2005  7:04 PM</b>
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
			<?=getComments(264)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 264)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/11/displaysearch-forecasts-3b-market-for-fpd-public-displays-by-2009-pdps-outship-lcds-3-to-1-in-q205.php" type="text/javascript" charset="utf-8"></script>
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