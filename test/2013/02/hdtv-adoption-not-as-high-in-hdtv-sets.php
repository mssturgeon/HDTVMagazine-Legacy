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
		AND e.entry_id = 5052";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5052 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5052 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5052";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2013/02/hdtv-adoption-not-as-high-in-hdtv-sets.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5052";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Adoption - Not as High in HDTV Sets" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Adoption - Not as High in HDTV Sets" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Adoption - Not as High in HDTV Sets" />
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
	<title>HDTV Magazine - HDTV Adoption - Not as High in HDTV Sets</title>
	<meta name="keywords" content="analog tvs, dtv transition, per household, tvs still, hdtv adoption, tvs, hdtv, analog, households, dtv, sets, per, cea, million, household, still, penetration, dtvs, may, millions, years, adoption, transition, research, projected" />
	<meta name="description" content=" HDTV was implemented within the effort of the DTV transition and since 1998 the Consumer Electronics Association (CEA) published yearly statistics of DTVs shipped. Just a few years ago approximately 113 million households with 3.1 TVs on average per..." />
	<meta name="title" content="HDTV Adoption - Not as High in HDTV Sets" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Adoption - Not as High in HDTV Sets" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2013/02/hdtv-adoption-not-as-high-in-hdtv-sets.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content=" HDTV was implemented within the effort of the DTV transition and since 1998 the Consumer Electronics Association (CEA) published yearly statistics of DTVs shipped. Just a few years ago approximately 113 million households with 3.1 TVs on average per..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5052', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2013/02/hdtv-adoption-not-as-high-in-hdtv-sets.php">HDTV Adoption - Not as High in HDTV Sets</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 15, 2013</b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><b></b> <p>HDTV was implemented within the effort of the DTV transition and since 1998 the Consumer Electronics Association (CEA) published yearly statistics of DTVs shipped. Just a few years ago approximately 113 million households with 3.1 TVs on average per household were estimated (up from 2.6 a couple of years earlier).  <p>The current data is 119 million households (Census) and 3 TVs per household ratio (CEA) on average. I am using these new published numbers for this article to analyze and measure HDTV sets penetration and analog TVs that are still in use, a different perspective from the HDTV adoption in households reported by other research and articles, which typically show larger penetration percentages than the perspective of TV sets.  <p>I followed the DTV transition implementation since 1998 and kept my own statistics throughout the years, which I reconciled with CEA's published numbers on a yearly basis. I published those numbers in the yearly <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology Review</a> and a series of "<a href="http://www.hdtvmagazine.com/articles/2008/10/dtv-transition-can-you-help-part-3-tvs-vs-households.php">DTV transition</a>" articles.&nbsp; The CEA requested as a consolidated version of them a few years ago, and this is a follow up of those articles.  <p>Research firms often make their independent surveys about TVs that consumers have at home. The <a href="http://www.leichtmanresearch.com/press/020813release.html">last research</a> from Leichtman Research Group (LRG) sampled 1252 households, the percentages of that survey applied to CEA’s 119 million/3 TV ratio per US household could be used to extrapolate TV sets per type (analog or digital) as another perspective of household penetration of HDTV.  <p>I discontinued my monitoring of the DTV adoption because the DTV transition was completed almost 4 years ago and they were many other subjects of technology that required my attention for the readership of HDTV Magazine, there was too much to cover and review in the digital TV world, such as 3DTV, LED, streaming and pre-recorded media, compression technologies, and now UHDTV and OLED.  <p>But this is a good opportunity to take a snap shot of the HDTV adoption because several publications have recently published glossy numbers on the 75% household adoption range, including the same CEA with 88% and 68% depending of the source of the research and the analysis performed. <p>According to CEA: CEA reports on household penetration rates in two separate studies: the first is in the <i>U.S Consumer Electronics Sales and Forecasts</i> (which reads as 88%) is based on sales data tracking and consumer surveys conducted by CEA.&nbsp; The 68% is from the <i>Annual CE Ownership and Market Potential Study</i> where penetration is calculated using self-reported numbers from a quantitative study that was administered via telephone interview to a random national sample of 2,028 U.S. adults between January 26 and January 30, 2012. The margin of sampling error at 95% confidence for aggregate results is +/- 2.2%.&nbsp; In this survey, weights were applied to cases based on gender, age, race and geographic region. As a result, this data can be generalized to the entire U.S. adult population.  <p> However, the reason of this article is penetration of DTV sets in the television market, rather than households adopting TVs.  <p>&nbsp; <h2><b>A bit of History of how we got here</b></h2> <p>If you started with HDTV in 1998 you may recall that first generation HDTV sets were mostly large cabinets with 1080i rear projection CRTs, 50-70 inches diagonal, from Toshiba, Pioneer Elite, JVC, Panasonic, Phillips, etc., they had no 720p inputs but could accept 480p progressive DVD, which was an attraction point.  <p>Anamorphic DVD was one of the main reasons pre-recorded movie lovers invested in these expensive first generation HDTV sets, not because we were eager to watch a few loops of HD broadcast with just a couple of hours of original content shown over and over again at prime time.  <p>I must remind those criticizing U-HD/4K TV that there is nothing wrong with a reasonable wait for the arrival of 4K content, it eventually arrives as it did with HDTV, meanwhile you can enjoy 1080p Blu-rays upscaled to four times their resolution by the U-HD TV, similarly to 480p DVDs upscaled to six times their resolution by a HDTV, but easier because the scaling is evenly doubled vertically and horizontally.&nbsp; <p>HD-Net (a channel available in satellite back then) was the best it happened to HDTV content, always striving for quality in every sense, and still does today, congratulations to Mark Cuban, its owner. Now Mark should produce 4K content on a new 4K channel, it may come eventually.  <p>Another reason for buying those early HDTV sets was also to view letterboxed original aspect ratio laserdiscs, the pre-DVD niche format of movie collectors, which still showed a widescreen image sandwiched between top/bottom black bars even on the 16x9 TV but the bars were slimmer than when shown in 4x3 digital TVs.  <p>Besides early adopter fans of quality video, the mandated DTV transition gradually made people buy their first HDTV but also set in motion a continuous shift of perfectly functional TVs to other rooms in the household.  <p>Some said attrition would make a household end up with the same total number of TVs, but actually the number of TVs per household was gradually increasing because the shifted TVs were in good working conditions and there was always a good reason for one more analog TV to be shifted to a secondary room, a den, basement, video gaming, home office, assisted living old family member, etc. for some even the garage as the end of the line before being dumped for recycling.  <p>In other words many millions of perfectly functional legacy analog TVs are still in use, especially in lower income households, nobody talks about that.  <p>In retrospective, below is my last published table before the DTV Transition was completed that appeared in the publications mentioned above:</p> <p>&nbsp;</p> <p> <table border="1" cellspacing="1" cellpadding="0" width="392"> <tbody> <tr height="60"> <td valign="top" width="45"> <p align="center"><b><font size="4">Year</font></b></p></td> <td valign="top" width="220"> <p align="center"><b><font size="4">Millions DTVs Shipped - </font></b> <p align="center"><b><font size="4">CEA as of July 31, 2008</font></b></p></td> <td valign="top" width="121"> <p align="center"><strong><font size="4">Cumulative in Millions</font></strong></p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2011</p></td> <td valign="top" width="220"> <p>&nbsp; 40.8 projected </p></td> <td valign="top" width="121"> <p align="left">&nbsp;&nbsp; 228.7</p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2010</p></td> <td valign="top" width="220"> <p>&nbsp; 38.4 projected </p></td> <td valign="top" width="121"> <p align="left">&nbsp;&nbsp; 187.9</p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2009</p></td> <td valign="top" width="220"> <p>&nbsp; 35.8 projected </p></td> <td valign="top" width="121"> <p align="left">&nbsp;&nbsp; 149.5</p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2008</p></td> <td valign="top" width="220"> <p>&nbsp; 32.6 estimated </p></td> <td valign="top" width="121"> <p>&nbsp;&nbsp; 113.7</p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2007</p></td> <td valign="top" width="220"> <p>&nbsp; 26.4 </p></td> <td valign="top" width="121"> <p align="left">&nbsp;&nbsp;&nbsp;&nbsp; 81.1</p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2006</p></td> <td valign="top" width="220"> <p>&nbsp; 23.5 </p></td> <td valign="top" width="121"> <p align="left">&nbsp;&nbsp;&nbsp;&nbsp; 54.7</p></td></tr> <tr> <td valign="top" width="45"> <p align="center">2005</p></td> <td valign="top" width="220"> <p>&nbsp; 11.4</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr> <td valign="top" width="45"> <p align="center">2004</p></td> <td valign="top" width="220"> <p>&nbsp; 8.0</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr> <td valign="top" width="45"> <p align="center">2003</p></td> <td valign="top" width="220"> <p>&nbsp; 5.5</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr> <td valign="top" width="45"> <p align="center">2002</p></td> <td valign="top" width="220"> <p>&nbsp; 4.1</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr height="20"> <td valign="top" width="45"> <p align="center">2001</p></td> <td valign="top" width="220"> <p>&nbsp; 1.5</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr> <td valign="top" width="45"> <p align="center">2000</p></td> <td valign="top" width="220"> <p>&nbsp; 0.6</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr> <td valign="top" width="45"> <p align="center">1999</p></td> <td valign="top" width="220"> <p>&nbsp; 0.1</p></td> <td valign="top" width="121">&nbsp;</td></tr> <tr> <td valign="top" width="45"> <p align="center">1998</p></td> <td valign="top" width="220"> <p>&nbsp; 0.0</p></td> <td valign="top" width="121">&nbsp;</td></tr></tbody></table></p> <p><b></b> <p>Do you think those projected millions of DTVs were actually shipped on the 2008-2011 period?  <p>&nbsp; <p><b></b> <h2><b>HDTV Adoption Today</b></h2> <p><a href="http://www.ce.org/News/News-Releases/Press-Releases/2013-Press-Releases/CE-Industry-Revenues-to-Reach-Record-High-$209-Bil.aspx">CEA</a>'s updated data below shows that a total of 244 million DTVs were shipped since 1998.&nbsp; The projected trend for the 2008-2011 period (32-35-38-40M) had rather stayed on the 33M+ per year, projected as well for 2013. <p>&nbsp; <p> <table border="1" cellspacing="1" cellpadding="0" width="386"> <tbody> <tr height="60"> <td valign="top" width="47"> <p align="center"><strong><font size="4">Year</font></strong></p></td> <td valign="top" width="218"> <p align="center"><b><font size="4">Millions DTVs Shipped - </font></b> <p align="center"><b><font size="4">CEA 2012</font></b></p></td> <td valign="top" width="115"> <p align="center"><strong><font size="4">Cumulative in Millions</font></strong></p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2013</p></td> <td valign="top" width="218"> <p>&nbsp; 33.5 projected</p></td> <td valign="top" width="115">&nbsp;</td></tr> <tr> <td valign="top" width="47"> <p align="center">2012</p></td> <td valign="top" width="218"> <p>&nbsp; 33.9</p></td> <td valign="top" width="115"> <p align="left">&nbsp; 244.2 (*)</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2011</p></td> <td valign="top" width="218"> <p>&nbsp; 33.7</p></td> <td valign="top" width="115"> <p align="left">&nbsp; 210.3</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2010</p></td> <td valign="top" width="218"> <p>&nbsp; 34.6</p></td> <td valign="top" width="115"> <p align="left">&nbsp; 176.6</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2009</p></td> <td valign="top" width="218"> <p>&nbsp; 34.8</p></td> <td valign="top" width="115"> <p align="left">&nbsp; 142</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2008</p></td> <td valign="top" width="218"> <p>&nbsp; 31.1</p></td> <td valign="top" width="115"> <p align="left">&nbsp; 107.2</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2007</p></td> <td valign="top" width="218"> <p>&nbsp; 24.9 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp; 76.1</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2006</p></td> <td valign="top" width="218"> <p>&nbsp; 22.3 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp; 51.2</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2005</p></td> <td valign="top" width="218"> <p>&nbsp; 10.7</p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp; 28.9</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2004</p></td> <td valign="top" width="218"> <p>&nbsp; 8.0 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp; 18.2</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2003</p></td> <td valign="top" width="218"> <p>&nbsp; 5.5 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp; 10.2</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2002</p></td> <td valign="top" width="218"> <p>&nbsp; 2.5 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp;&nbsp; 4.7</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2001</p></td> <td valign="top" width="218"> <p>&nbsp; 1.5 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp;&nbsp; 2.2</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">2000</p></td> <td valign="top" width="218"> <p>&nbsp; 0.6 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp;&nbsp; 0.7</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">1999</p></td> <td valign="top" width="218"> <p>&nbsp; 0.1 </p></td> <td valign="top" width="115"> <p align="left">&nbsp;&nbsp;&nbsp;&nbsp; 0.1</p></td></tr> <tr> <td valign="top" width="47"> <p align="center">1998</p></td> <td valign="top" width="218"> <p>&nbsp; 0.0 </p></td> <td valign="top" width="115">&nbsp;</td></tr></tbody></table></p> <p>(*) Out of a total of 357 million TVs in the US, 244 million DTVs is 68% of technology penetration of TV sets, irrespective of adopting households.&nbsp; The other 32% (113M) are still analog TVs, although the number could be much higher considering that many millions of the DTVs purchased within the 68% since 1998 replaced previously purchased DTVS that became damaged and irreparable (a DTV replacing another DTV rather than upgrading an analog set).</p> <p>Regardless of the percentage of household penetration one may use from the CEA (68% or 88%) there are still at least 113M analog TVs in use in the US.  <p>&nbsp;</p> <h2><strong>From another Research Perspective</strong></h2> <p>According to <a href="http://www.leichtmanresearch.com/press/020813release.html">this</a> most recent study from Leichtman Research Group (LRG), today's HDTV landscape in the US is as follows, and I quote from their press release:  <blockquote> <p><em>"75 per cent of households in the US have at least one high definition television (HDTV) set – up from 23 per cent five years ago."</em> </p></blockquote> <blockquote> <p>"<i>About 59 per cent of TV sets in HD households are HDTVs</i>." </p></blockquote> <p>Applying LRG’s percentages to Census’ 119 million US households at CEA’s 3 TV per household average ratio the following can be extrapolated:  <p>29 million households (25% of 119) do not even have one HDTV and still use <b>89 million</b> analog TVs (119 x .25 x 3 TVs per household ratio).  <p>Since 59% of the TVs used in 75% HDTV households are HDTVs, the other 41% are analog TVs, at the 3 TV average ratio per household that totals <strong>109 million</strong> ((119 x .75 x 3) x .41), making the estimate of analog TVs as high as <strong>198</strong> million or 55%, and DTV sets penetration just 45%, much lower than CEA’s 68% DTV set penetration (not households). <p>&nbsp; <p><u>Extrapolated from LRG’s research</u>&nbsp; </p> <table border="1" cellspacing="1" cellpadding="2" width="504"> <tbody> <tr> <td valign="top" width="283"> <p align="center"><strong><font size="4">Analog TVs in use </font></strong></p> <p align="center"><strong><font size="4">in the US</font></strong></p></td> <td valign="top" width="100"> <p align="center"><strong><font size="4">Millions of Analog TVs</font></strong></p></td> <td valign="top" width="115"> <p align="center"><strong><font size="4">% of 357 Million TVs in the US</font></strong></p></td></tr> <tr> <td valign="top" width="283"> <p align="left"><font size="3"><strong>&nbsp;</strong> In 29 million non-HDTV Households</font></p></td> <td valign="top" width="100"> <p align="center"><font size="3">89 </font></p></td> <td valign="top" width="115"> <p align="center"><font size="3">24 %</font></p></td></tr> <tr> <td valign="top" width="283"> <p align="left"><font size="3">&nbsp; In HDTV Households</font></p></td> <td valign="top" width="100"> <p align="center"><font size="3">&nbsp; 1<font size="2"><font size="3">09</font>&nbsp;</font>&nbsp;</font></p></td> <td valign="top" width="115"> <p align="center"><font size="3">30 %</font></p></td></tr> <tr> <td valign="top" width="283"> <p align="left"><font size="3">&nbsp; <strong>Total Analog TVs&nbsp;&nbsp; </strong></font></p></td> <td valign="top" width="100"> <p align="center"><font size="3"><strong>198</strong></font></p></td> <td valign="top" width="115"> <p align="center"><strong><font size="3">55 %</font></strong></p></td></tr></tbody></table> <p><strong></strong>&nbsp; <p><u>Comparison of LRG and CEA estimates</u>&nbsp; <p><b></b></p> <table border="1" cellspacing="1" cellpadding="2" width="505"> <tbody> <tr> <td valign="top" width="281"> <p align="left"><strong><font size="3">&nbsp;</font><font size="4"> % of TVs in the US by Type</font></strong></p></td> <td valign="top" width="104"> <p align="center"><strong><font size="4">Per LRG</font></strong></p></td> <td valign="top" width="114"> <p align="center"><strong><font size="4">Per CEA</font></strong></p></td></tr> <tr> <td valign="top" width="281"> <p align="left"><font size="3"><strong>&nbsp;</strong> HDTVs&nbsp; </font></p></td> <td valign="top" width="104"> <p align="center">&nbsp;&nbsp;&nbsp; <font size="3">45 % </font></p></td> <td valign="top" width="114"> <p align="center"><font size="3">68 %</font></p></td></tr> <tr> <td valign="top" width="281"> <p align="left"><font size="4"><font size="3">&nbsp; Analog TVs still in use</font></font></p></td> <td valign="top" width="104"> <p align="center"><font size="3">&nbsp;&nbsp; 55 % </font></p></td> <td valign="top" width="114"> <p align="center"><font size="3">32 % </font></p></td></tr></tbody></table> <p><strong><font size="4"></font></strong>&nbsp; <h2><strong><font size="4">Final Thoughts</font></strong></h2> <p><b></b> <p>Measuring the adoption of HDTV technology in TV sets provides quite a different perspective than measuring adopting households, during the DTV Transition there was a concern about the number of households having at least one tuner-integrated DTV set or a method to receive an emergency over-the-air broadcast when analog broadcasting is discontinued, which served a purpose of emergency communication to a public that was mandated to migrate to digital.  <p>Now that the DTV Transition is completed I prefer to evaluate HDTV technology adoption in number of DTV sets as they replace analog TVs rather than in number of adopting households, and, regardless which research is used as source, <b>approximately one third to half of the TVs in use in US households are still analog TVs, and that is too many, </b>especially considering that Ultra HDTV displays have already being introduced as the successor of HDTV, I expected the HDTV penetration in TV sets to be faster and in higher numbers. This is analogous to having a third/half of TVs still as B/W when HDTV was introduced in 1998 to replace analog color NTSC TVs. <p>One of the factors that could have slowed down the HDTV adoption may have been the never ending introduction of newer technologies, LED (viewed as a miracle medicine for LCD technology limitations), LCD selling more than the better image of plasma, 3DTV in all its forms, UHDTV/4K in all its conflicting definitions, OLED's continuous self-correcting availability announcements, and the list goes on.  <p>Those contribute to consumer confusion and to uncertainty when buying a new TV, motivating the delay of a purchase under the expectation and rumors that in a few months a near future model would be much better and cheaper, which is the reality in this fast evolving industry, not to mention experiencing the feeling of accelerated obsolescence when buying a TV and seeing it superseded just a few months later.&nbsp; Competitiveness and innovation in this industry always brought better products to market, but also motivated the attitude of "waiting for the next model, it should be better".  <p>&nbsp; <h2><strong>Bottom Line</strong></h2> <p>Regardless if we use CEA’s 113M or LRG’s 198M of analog TVs that are still in use, at a rate of the projected 33M x year of shipped sets it may take about 4 years to fully replace them with DTVs (6 years with LRG’s 198M estimate), but actually more years considering that many millions of the new DTVs will continue replacing older DTVs that may become beyond repair.&nbsp; <p>That may put us on a 20-25 year DTV transition since 1998, however, many millions of households that are below the poverty line may not be able, nor they may justify, to change their still functional analog TVs, if ever.</p> <p>In summary, on this number games of researchers and bloggers, what would you think if someone tells you that the 15-year construction is 88% complete but at least another 4-6 years may be needed for the remaining 12%?  <p>Maybe is time to change the ruler, or the reading material?&nbsp; Perhaps both.  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 15, 2013 10:40 PM</b>
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
			<?=getComments(5052)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5052)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2013/02/hdtv-adoption-not-as-high-in-hdtv-sets.php" type="text/javascript" charset="utf-8"></script>
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