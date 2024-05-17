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
		AND e.entry_id = 905";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 905 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 905 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 905";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/displaysearch-expects-sales-of-tft-lcd-manufacturing-equipment-to-jump-40-to-116-billion-in-2008.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 905";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008" />
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
	<title>HDTV Magazine - DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008</title>
	<meta name="keywords" content="supply demand, tft lcd, capital spending, demand capital, spending report, •, displaysearch, tft, equipment, demand, capacity, supply, fab, lcd, panel, forecast, report, data, spending, new, capital, quarterly, analysis, industry, added" />
	<meta name="description" content="Austin, Texas, January 15, 2008 - DisplaySearch, the worldwide leader in display market research and consulting, reports that sales of equipment used to manufacture TFT LCDs is expected to surge 40% to more than $11.6 billion in 2008, and will likely remain at a similar level through 2009 in its Quarterly TFT LCD Supply/Demand and Capital Spending Report. 

According to DisplaySearch Vice President of Manufacturing Research Charles Annis, “2007 turned out to be an excellent year for TFT LCD panel makers. Unfortunately, it was a very challenging year for equipment companies, who generated only $8.3 billion in revenues, down 35% from 2006 levels. These dynamics of reduced investment and healthy panel maker earnings, coupled with continued strong... 
" />
	<meta name="title" content="DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/displaysearch-expects-sales-of-tft-lcd-manufacturing-equipment-to-jump-40-to-116-billion-in-2008.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Austin, Texas, January 15, 2008 - DisplaySearch, the worldwide leader in display market research and consulting, reports that sales of equipment used to manufacture TFT LCDs is expected to surge 40% to more than $11.6 billion in 2008, and will likely remain at a similar level through 2009 in its Quarterly TFT LCD Supply/Demand and Capital Spending Report. 

According to DisplaySearch Vice President of Manufacturing Research Charles Annis, “2007 turned out to be an excellent year for TFT LCD panel makers. Unfortunately, it was a very challenging year for equipment companies, who generated only $8.3 billion in revenues, down 35% from 2006 levels. These dynamics of reduced investment and healthy panel maker earnings, coupled with continued strong... 
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=905', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/displaysearch-expects-sales-of-tft-lcd-manufacturing-equipment-to-jump-40-to-116-billion-in-2008.php">DisplaySearch Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 Billion in 2008</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>January 15, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=267&category=Marketplace">Marketplace</a></b>
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

<p></p>

<p><head><br />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><br />
<title>New Page 1</title><br />
</head></p>

<p><body></p>

<table cellSpacing="0" cellPadding="0" width="550" align="left" id="table1">
	<tr>
		<td align="left"><font face="Arial"><font size="2"><strong>DisplaySearch 
		Expects Sales of TFT LCD Manufacturing Equipment to Jump 40% to $11.6 
		Billion in 2008</strong></font> </font>
		<p>&nbsp;</td>
	</tr>
	<tr>
		<td class="maincontent" align="left"><font face="Arial" size="1">
		<strong><span class="yshortcuts" id="lw_1200426943_11"></span></strong>
		</font>
		<p><font face="Arial" size="1"><strong><span class="yshortcuts">Austin, 
		Texas</span>, January 15, 2008 - </strong>DisplaySearch, the worldwide 
		leader in display market research and consulting, reports that sales of 
		equipment used to manufacture TFT LCDs is expected to surge 40% to more 
		than $11.6 billion in 2008, and will likely remain at a similar level 
		through 2009 in its Quarterly TFT LCD Supply/Demand and Capital Spending 
		Report. </font></p>
		<p><font face="Arial" size="1">According to DisplaySearch Vice President 
		of Manufacturing Research Charles Annis, "2007 turned out to be an 
		excellent year for TFT LCD panel makers. Unfortunately, it was a very 
		challenging year for equipment companies, who generated only $8.3 
		billion in revenues, down 35% from 2006 levels. These dynamics of 
		reduced investment and healthy panel maker earnings, coupled with 
		continued strong demand, are now setting 2008 up to be a bumper year for 
		almost all segments of the LCD industry and a reversal of fortune for 
		equipment companies." </font></p>
		<p><font face="Arial" size="1">"New investments in 2008 and 2009 will 
		increase capacity, help keep prices on a declining curve, and likely 
		push demand further than even previously expected," Annis added. 
		"However, the entire TFT LCD supply chain needs to be careful, if all 
		panel makers rush to dramatically increase capacity in the next two 
		years, the industry could once again set itself up for a significant 
		over-supply and a repeat of the crystal cycle in 2010." </font></p>
		<p><font face="Arial" size="1">Additional highlights from the 
		DisplaySearch Q4'07 Quarterly Supply/Demand and Capital Spending Report 
		include </font></p>
		<p><font face="Arial" size="1">• Compared to the previous estimates, new 
		investments and capacity expansions have been increased and pulled in so 
		now 46 individual fab investments are forecast between the start of 
		Q3'07 to the end of Q3'10, eight of which will be for new LTPS lines, 
		conversions of a-Si to p-Si or expansions of current capacity.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• Counting a new IPS Alpha fab, AMLCD TFT 
		array capacity is expected to rise at a compound annual growth rate of 
		34% from 4.5 million m2 in 2000 to over 209 million m2 in 2013. Color 
		Filter capacity is on a similar growth curve, expanding from 5.3 million 
		m2 in 2000 to over 216 million m2 in 2013.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• Multiple older PDP lines are being shut 
		down as more productive lines come on stream, especially those not able 
		to meet 1080p precision requirements. And even despite the recently 
		announced Matsushita, Hitachi and Canon alliance, Matsushita is still 
		expected to forge ahead and build a 12-up, 42" equivalent PDP mega fab 
		that should focus on 50" and larger TV production.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• The top five LCD makers-Samsung, AUO, 
		LPL, CMO and Sharp-are forecast to continue to invest at the highest 
		rates increasing their total share of capacity from 79.6% in Q3'07 to 
		82.9% in Q3'09.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• In terms of TFT LCD capacity, 
		supply/demand was extremely tight from Q2-Q4'07. Supply/Demand is now 
		closely linked to seasonality, and is expected to be loser in Q1'08, but 
		then grow tight through the end of the year, helping to keep panel 
		pricing firm. Based on the current outlook, 2009 and 2010 supply/demand 
		should grow progressively looser.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• Supply/Demand of key components such as 
		CCFLs, glass substrates, CF, TAC film and PVA film also affect panel 
		maker's ability to utilize capacity. In Q3'07 and Q4'07, color filters 
		and glass substrates were in shortage, restricting the amount of panels 
		that could be produced. Key component supply will remain a gating factor 
		for panel production in 2008 and then should loosen in 2009.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• TFT fab utilization as an industry 
		average continuously increased from a very low 85.4% in Q1'07 to a very 
		high 95.7% in Q3/Q4'07, which is the highest level recorded since 
		DisplaySearch started tracking this segment in 2000.<br>
&nbsp;</font></p>
		<p><font face="Arial" size="1">• As shown in the following table below, 
		with Gen 10, lots of Gen 8 and other expansions, including a second IPS 
		Alpha fab, going forward TFT equipment makers should see significantly 
		higher revenues than 2007.</font></p>
		<div align="center">
			<table cellSpacing="0" cellPadding="0" width="100%" border="1" id="table2">
				<tr bgColor="#437088">
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">Year</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">1999</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2000</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2001</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2002</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2003</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2004</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2005</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2006</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2007</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2008</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2009</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" color="#fdf6fd" size="1">2010</font></td>
					<td vAlign="bottom" noWrap>
					<p class="style1" align="center">
					<font face="Arial" size="1"><font color="#fdf6fd">201</font><font color="#fffcff">1</font></font></td>
				</tr>
				<tr>
					<td vAlign="bottom" noWrap><font face="Arial" size="1">
					Revenues</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$3.6</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$6.7</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$3.8</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$5.2</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$6.3</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$13.3</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$10.5</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$12.7</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$8.3</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$11.6</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$11.3</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$10.5</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">$9.6</font></td>
				</tr>
				<tr bgColor="#c6e3f3">
					<td vAlign="bottom" noWrap><font face="Arial" size="1">
					Growth</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">0%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">85%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">-43%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">36%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">22%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">109%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">-21%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">22%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">-35%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">40%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">-3%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">-8%</font></td>
					<td vAlign="bottom" noWrap>
					<p align="center"><font face="Arial" size="1">-8%</font></td>
				</tr>
			</table>
		</div>
		<p><font face="Arial" size="1">• TFT LCD makers achieved pre-tax profit 
		margins of 17% on average in Q3'07, the best performance since Q2'04 and 
		exceeding the prior peak. Large area TFT LCD panel margins are forecast 
		to be even higher in Q4'07 and push into the 20% level during 2008. Most 
		industry participants are quite optimistic about 2008. </font></p>
		<p><font face="Arial" size="1">The metrics behind these trends and 
		breaking industry news are explained and analyzed in great detail, and 
		further backed up by expanded Excel data tables in the recently 
		published Q4'07 Quarterly Supply/Demand and Capital Spending Report.
		</font></p>
		<p><font face="Arial" size="1">The Quarterly TFT LCD Supply/Demand and 
		Capital Spending Report is one of DisplaySearch's most comprehensive 
		publications, which offers detailed insights on actual and forecast 
		AMLCD fab activity, panel supply/demand, fab utilization and the capital 
		equipment market. Furthermore, the report provides a broader prospective 
		on the FPD industry through discussion of module, PDP, OLED and color 
		filter fab activity, total announced capital spending, panel maker 
		financial information and a variety of other relevant topics. The 
		280-slide PowerPoint report covers a wide 13-quarter rolling forecast 
		range, with this edition evaluating Q3'07 - Q3'10. Annual forecasts 
		extend from 1999 through 2011 or 2013. The PowerPoint file is 
		accompanied by DisplaySearch's proprietary database of TFT array fab 
		install and substrates per month and square meters capacity. The easy to 
		filter, sort and sum Excel data includes fields for fab region, 
		manufacturer, fab name, glass size, generation, technology, expansion 
		phase, equipment maximum capacity, purchase order, install, mass 
		production, end dates and probability factor. The data tables have been 
		expanded to include all equipment total data for easy reference. </font>
		</p>
		<p><font face="Arial" size="1">The report continues to increase content 
		and depth of analysis with every edition, since Q1'07 the following new 
		features have been added:</font></p>
		<p><font size="1"><br>
		<font face="Arial">• PDP equipment forecast</font></font></p>
		<p><font face="Arial" size="1">• Expanded panel maker coverage-For 
		example, <span class="yshortcuts" id="lw_1200426943_12">Samsung 
		Electronics</span> (SEC) was added to the CapEx historical numbers and 
		forecast analysis. Innolux, TPO and Wintek are now included in the list 
		of companies covered in the Taiwanese fund raising activities section. 
		Innolux was added to panel maker debt-to-equity and liability analysis.</font></p>
		<p><font face="Arial" size="1">• A new chapter on quarterly Investor 
		Conferences and Financial summaries</font></p>
		<p><font size="1"><br>
		<font face="Arial">•</font></font><font face="Arial" size="1">&nbsp;Enhanced 
		data file with sum totals for capacity by individual fab, equipment data 
		in Excel, including array equipment spending by region and generation</font></p>
		<p><font face="Arial" size="1">• Added "tier" definitions for 
		small/medium producers</font></p>
		<p><font face="Arial" size="1">• Capacity conversion analysis</font></p>
		<p><font face="Arial" size="1">• Substantially increased inside 
		information and DisplaySearch analysis of relevant trends through a 
		subsection of "TFT LCD News &amp; Information Analysis" and "Implications" 
		slides<br>
		• Unit size demand forecast breakout by application</font></p>
		<font size="1">
		<p><br>
		<font face="Arial">• Explanation and data on depreciation schedules by 
		region</font></p>
		<p><br>
		<font face="Arial">• Install date range details added to the detailed 
		equipment investment timing tables to better represent how equipment 
		moves into a fab over time </font></font></p>
		<p><font face="Arial" size="1">For more information on the DisplaySearch 
		Q4'07 Quarterly Supply Demand and Capital Spending Report, please 
		contact <span class="yshortcuts" id="lw_1200426943_13">arie@displaysearch.com</span>, 
		or contact your regional DisplaySearch offices in Japan, Korea,
		<span class="yshortcuts" id="lw_1200426943_14">Taiwan</span> and
		<span class="yshortcuts" id="lw_1200426943_15">China</span>. </font></p>
		<p><font face="Arial" size="1"><u>Company Contact:<br>
		</u>Arie Braun<br>
		DisplaySearch <br>
		<span class="yshortcuts" id="lw_1200426943_16">512.687.1505</span> (ph)
		<br>
		<span class="yshortcuts" id="lw_1200426943_17">512.628.3484</span> (fax)
		<br>
		E-mail: </font>
		<a target="_blank" rel="nofollow" href="mailto:arie@displaysearch.com">
		<font face="Arial" color="#269ad0" size="1">
		<span class="yshortcuts" id="lw_1200426943_18">arie@displaysearch.com</span></font></a><font face="Arial" size="1">
		</font></p>
		<p><font size="1" face="Arial"><u>Media Contact:</u><br>
		Stacey Voorhees<br>
		Public Relations<br>
		<span class="yshortcuts" id="lw_1200426943_19">925.336.9592</span> (ph)<br>
		E-mail:
		<a target="_blank" rel="nofollow" href="mailto:stacey@savvypublicrelations.net">
		<span class="yshortcuts" id="lw_1200426943_20"><font color="#269ad0">
		stacey@savvypublicrelations.net</font></span></a></font></p>
		<p><font size="1"><font face="Arial"><strong>About DisplaySearch
		</strong><br>
		DisplaySearch, an NPD Group company, has a core team of 57 employees 
		located in <span class="yshortcuts" id="lw_1200426943_21">Europe</span>,
		<span class="yshortcuts" id="lw_1200426943_22">North America</span> and 
		Asia who produce a valued suite of FPD-related market forecasts, 
		technology assessments, surveys, studies and analyses. The company also 
		organizes influential events worldwide. Headquartered in
		<span class="yshortcuts" id="lw_1200426943_23">Austin, Texas</span>, 
		DisplaySearch has regional operations in
		<span class="yshortcuts" id="lw_1200426943_24">Chicago</span>,
		<span class="yshortcuts" id="lw_1200426943_25">Houston</span>,
		<span class="yshortcuts" id="lw_1200426943_26">Kyoto</span>,
		<span class="yshortcuts" id="lw_1200426943_27">London</span>, San Diego,
		<span class="yshortcuts" id="lw_1200426943_28">San Jose</span>,
		<span class="yshortcuts" id="lw_1200426943_29">Seoul</span>,
		<span class="yshortcuts" id="lw_1200426943_30">Shenzhen</span>,
		<span class="yshortcuts" id="lw_1200426943_31">Taipei</span> and
		<span class="yshortcuts" id="lw_1200426943_32">Tokyo</span>, and the 
		company is on the web at </font>
		<a target="_blank" href="http://www.displaysearch.com/">
		<span class="yshortcuts" id="lw_1200426943_33">
		<font face="Arial" color="#269ad0">http://www.displaysearch.com/</font></span></a><font face="Arial">.</font></font></td>
	</tr>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
	<tr>
		<td>
		<p align="center">
		<img height="24" alt="The Worldwide Leader in Display Market Research and Consulting" src="http://img.en25.com/eloquaimages/clients/DisplaySearch/{27105a4d-5899-4163-8181-7bfe3b9e23a1}_footer.gif" width="550"></td>
	</tr>
</table>

<p></body></p>

<p></html></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>January 15, 2008  1:27 PM</b>
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
			<?=getComments(905)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 905)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/displaysearch-expects-sales-of-tft-lcd-manufacturing-equipment-to-jump-40-to-116-billion-in-2008.php" type="text/javascript" charset="utf-8"></script>
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