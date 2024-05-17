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
		AND e.entry_id = 291";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 291 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 291 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 291";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/01/hdtv-integrated-tuners-and-you.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 291";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Integrated Tuners, and You" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Integrated Tuners, and You" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Integrated Tuners, and You" />
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
	<title>HDTV Magazine - HDTV Integrated Tuners, and You</title>
	<meta name="keywords" content="atsc qam, qam unscrambled, dvi hdcp, atsc tuner, cable tuners, cable, integrated, digital, atsc, tuner, tuners, ttm, qam, dvi, new, unscrambled, stb, line, hdtv, models, hdcp, ota, dtv, sets, agreement" />
	<meta name="description" content="The cable and consumer electronics industries are moving towards integrating over-the-air (OTA) and cable HD tuners into HDTV sets.  It is certainly good news that the cable industry is finally getting on board of HDTV.

Since about 70% of TV viewers subscribe to cable, this has the potential of accelerating the adoption of HDTV in general, at a pace we have not seen over the last 5 years. The integration of tuners into TVs seems to be an attractive proposition for everyone.

This article analyzes the subject to help you decide what is best for you." />
	<meta name="title" content="HDTV Integrated Tuners, and You" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Integrated Tuners, and You" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/01/hdtv-integrated-tuners-and-you.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The cable and consumer electronics industries are moving towards integrating over-the-air (OTA) and cable HD tuners into HDTV sets.  It is certainly good news that the cable industry is finally getting on board of HDTV.

Since about 70% of TV viewers subscribe to cable, this has the potential of accelerating the adoption of HDTV in general, at a pace we have not seen over the last 5 years. The integration of tuners into TVs seems to be an attractive proposition for everyone.

This article analyzes the subject to help you decide what is best for you." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=291', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv-integrated-tuners-and-you.php">HDTV Integrated Tuners, and You</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 25, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<blockquote>The following article originally appeared in HDTVetc magazine in their October 2003 issue. This previously published article contains some product information that is dated to mid-2003, and should not be considered news when reading it today. Although the content has historical value, the primary value is the tutorial substance and my analysis to reach a forecasted vision of future market conditions (that actually happened later in time) which helped many consumers in making the right purchasing decisions.  Some statements of the article could be considered time-travel to the future if you project your reading imagination to back then; the vision has now transformed itself into events and conditions that actually happened. Enjoy the reading.</blockquote>

<p>The cable and consumer electronics industries are moving towards integrating over-the-air (OTA) and cable HD tuners into HDTV sets.  It is certainly good news that the cable industry is finally getting on board of HDTV.</p>

<p>Since about 70% of TV viewers subscribe to cable, this has the potential of accelerating the adoption of HDTV in general, at a pace we have not seen over the last 5 years. The integration of tuners into TVs seems to be an attractive proposition for everyone.</p>

<p>This article analyzes the subject to help you decide what is best for you.  Let us start with some background regarding OTA and cable tuners, mandates, agreements and the FCC:</p>

<p><b><u>DTV Over-the-Air ATSC tuners</b></u> (require an antenna)</p>

<p>In 2002, television manufacturers and retailers were asked to adhere to a phased-in schedule that would lead to terrestrial OTA DTV tuners in all television sets by Dec 31, 2006.</p>

<p>The FCC then mandated that all TV sets 13-inches and larger and other products that normally carry TV tuners -such as VCRs, personal video recorders, etc.- are to include ATSC terrestrial DTV tuners by July 1, 2007.</p>

<p>Under the five-year phased-in guidelines DTV tuners are to be added to 50 percent of sets measuring 36 inches and larger by July 1, 2004, and 100 percent by July 1, 2005.  After that, 50 percent of sets measuring 25 inches to 35 inches are to add DTV tuners by July 1, 2005, and 100 percent by July 1, 2006.  The rest are to conform by July 1, 2007. </p>

<p>At that time the Consumer Electronics Association (CEA) charged the decision would put an undue cost burden on consumers and filed a lawsuit to overturn the order in October 2002.  One main factor for such appeal is the fact that approximately 70 percent of TV viewers receive their signal from cable, and those will be switching to a DTV cable set top box, not needing the DTV over the air tuner mandated on their new TV sets.</p>

<p>According to the CEA <i>"Manufacturers will remain free to sell true monitors without a DTV tuner <u>as long as they do not have NTSC tuners included</u> (underline added), as many plasma displays and front projectors are sold today.  Should the regulations remain in place, TV makers have the option of building sets with both digital and analog tuners <u>or no tuner at all</u> (underline added).  The rules do allow companies to bundle an add-on digital tuner in a separate box, which would allow the sale of today's so-called DTV-ready sets."</i></p>

<p>The matter has been settled recently, the mandated OTA tuner integration is occurring.  Additionally, cable tuners are to be included as follows.  </p>

<p><b><u>DTV Cable-tuners</b></u></p>

<p>On December 2002, an announcement was made of an agreement between the consumer-electronics and cable television industries regarding digital cable interoperability as follows: </p>

<p><i>"The agreement is part of a broad 'memorandum of understanding' between the two industries that is intended to lead to a 'plug-and-play' standard that was needed to link digital cable equipment and services with consumer electronics devices. Once the Federal Communications Commission approves the agreement, it is expected to help speed the adoption of HDTV.</i></p>

<p><i>The memorandum, along with a letter to FCC chairman Michael Powell, was signed by 12 consumer electronics companies and seven major cable multiple system operators (MSO) representing more than 75 percent of all cable subscribers.  The memorandum is a package of voluntary commitments, specifications and proposals for rules covering digital television (DTV) cable hardware compatibility and content protection, and the FFC is expected to approve the recommendations."</i></p>

<p>The plan includes the phased-in use of two digital interface connectors on new digital cable-ready TVs and/or cable set-top converter boxes, including a) IEEE-1394 'FireWire/iLink' connections with Digital Transmission Content Protection (DTCP) for recordable and networkable compressed video streams, and b) the non-recordable DVI/HDMI with High-bandwidth Digital Content Protection (HDCP) connections on digital televisions and cable set-top boxes.</p>

<p>The agreement prohibited cable providers who supply STBs with both FireWire and DVI/HDMI connectors to switch the outputs in order to restrict lawful recording.   The agreement also included encoding rules to copy freely, once or never depending on the content.</p>

<p>Consumers would buy TVs from a retailer, then receive a POD (Point of Deployment) authorizing card from their cable provider which would "unlock" specific cable programming services offered by the local system.  The tuner should be plug-and-play compatible <u>even if the TV moves to another location in the US</u>.</p>

<p>The two groups agreed to launch a "test suite" for the unidirectional digital-cable products that will begin on Jan 31, 2003. </p>

<p>The proposed agreement originally specified that, by Dec 31, 2003, a cable company is expected to replace any leased HD-STB that does not include a 1394 interface with a box that has one, or to provide the software that would make such an interface functional, at no cost to the consumer.   The approved agreement is now extended, more on it later. </p>

<p> The agreement was made for an integrated <u>one-way only</u> digital cable television tuner.  Under this unidirectional agreement, bi-directional features such as video-on-demand (VOD), impulse-pay-per-view, return path of the cable system, and the use of the electronic program guide services provided by the Cable Operator would not be available, and a separate STB would be needed for those integrated TVs.</p>

<p>The two industries also agreed to work together on standards for future interactive, 'two-way' digital cable TV products. Samsung announced in January 2003 at the Las Vegas CES that it has become the first consumer electronics manufacturer to sign a license with Cable-Labs for a <u>two-way</u> interactive version of the POD.</p>

<p>By implementing this interactive version of POD, digital televisions would eventually be able to directly receive interactive digital programs without the need for a digital set-top-box from their local cable provider.</p>

<p><u><strong>How this cable plan got approved in 2003</strong></u></p>

<p>In August the FCC announced the updated progress in the establishment of the two-way interactive plug-and-play cable interoperability agreement. Under this two-way interoperability agreement, sets with interactive functionality will be labeled 'Interactive Digital Cable Ready.'</p>

<p>Digital TV sets capable of displaying one-way programming services, including premium channels, would be labeled 'Digital Cable Ready', and they require smart POD cards that will be supplied by cable TV operators to unlock scrambled channels.  The POD cards are now called "CableCARDS."</p>

<p>In September the CEA announced that the FCC reached a decision on the plug-and-play cable agreement, as follows:</p>

<p><em>"Digital cable ready HDTV owners will be provided with a secure CableCARD to be inserted into the digital receiver in order to comply with varying degrees of content copy protection levels and prevent theft of cable service. For instance, at least one copy of a digital channel sold by monthly subscription (e.g. basic and HBO) may be made for private and personal use, whereas premium pay-per-view and video-on-demand programs may be marked as copy never (originally as copy once). Free over-the-air broadcast signals may be copied freely, and may not be reduced in resolution ("down-res'd") when output from unprotected high definition analog ports."</em></p>

<p><em>"Significantly, legacy DTV set owners also are protected by this agreement, which bans the use of "selectable-output-controls," which would have enabled content providers to control content delivery to households from the head end. Without the plug-and-play agreement's encoding rules, consumers who purchased introductory HDTV sets not equipped with copyprotection-designed digital outputs could be disenfranchised and altogether denied HDTV services and programming. This agreement ensures that today's DTV products will not be made obsolete in the course of a transformation to nationwide digital video delivery over cable.  <u>But selectable output controls may some day in the future be used</u>."</em> (underline added). </p>

<p>All digital-cable-ready TV sets are required to include over-the-air ATSC tuners. The satellite industry was not a party on this FCC decision and declared that it is not the end of the process.</p>

<p>Under the approved rules, and as agreed and mentioned before, HDTVs with unidirectional cable tuners would still need a set-top box for two-way services such as video on demand, some pay-per-view programming and customized electronic programming guides.  Starting April 1, 2004, cable operators must supply, upon request, HD-STBs with functional 1394 "firewire" connectors.  By July 1, 2005, all HD-STBs would also require a digital visual interface ("DVI") or a high definition multimedia interface ("HDMI"). </p>

<p><br />
<u><strong>Analysis of HD-STB vs. integrated tuners</strong></u></p>

<p>Several manufacturers started to offer HDTV integrated versions with OTA/cable tuners on their 2003/4 lines.  The integrated TV versions cost between $300-$1300 more than their monitor-only versions ($704 extra on average).  The attached table and manufacturer specifications include a representative sample of lines and models. </p>

<p>The difference in price is justified by the cost tuner/s and related components, such as an MPEG-2 decoder so the digital signal can be uncompressed for the TV to display, 1394 outputs so the tuned compressed digital signal can be sent out for HD-recording, etc. </p>

<p>HD-STB tuners are still costing between $400 and $900 MSRP.  The retail value of tuners is expected to drop eventually.</p>

<p>Back in 1999, first generation rear projection HDTVs cost consumers between $5,000 and $10,000, most 42" plasmas started in the $12,000 range; it was expensive for early adopters.  HD-STB tuners were selling between $400 and $900 (although there were some extreme cases on the $3000 range).  At that time, the MSRP relationship between a RPTV and an HD-STB was approximately 10 to 1 on average.</p>

<p>Today, similar rear projection HDTVs cost consumers approximately $1,000/$3,000, and the 42" plasmas are now in the $4,000 range, and they are better products (better line-doublers, lenses, digital inputs, video processors and scalers, etc.).  The price of a new HD-STB today has not changed much, although one can still find some 1999 STB technologies at discounted prices.  Today, the MSRP relationship between a RPTV and an HD-STB is approximately 3 to 1 on average.  </p>

<p>In other words, the price of a HD-STB tuner today, relative to the reduced price of today's DTVs, should be much lower than it is.  The same should apply to the price of tuners within integrated TVs, as it can be seen on the attached table. </p>

<p>Over the last 5 years tuners within HD-STBs did not have a record as clean as one could expect for the product to become a component of HDTVs, but they are certainly improving.</p>

<p>A tuner needing replacement or service might become a nuisance if integrated within a 300 pounds RPTV that most probably require and in-home service call/extra cost.  Having the tuner as a separate HD-STB the problem could be solved as easily as just replacing/servicing just the STB; and if it would be a leased box the cable company should take care of the problem, which could facilitate upgrades to newer/better models.  Leasing could be a good proposition <u>during the period</u> a technology needs to mature/evolve, like this one.</p>

<p> <br />
<u><strong>Identify the HD tuning capabilities that you actually need (and how it relates to recording)</strong></u></p>

<p>A cable-integrated HDTV owner that subscribes to premium cable services would be required to use a separate cable STB (unless the HDTV has an internal cable tuner with CableCARD, which are starting to come out on the last quarter of 2003). This subscriber would be paying for two tuners, one inside their new integrated HDTV for unscrambled services, and another into the external HD-STB for premium programming/interactive services (which also performs unscrambled tuning).  If you are required to use a STB for your particular cable services anyway, you might want to consider an HD monitor rather than an integrated set.</p>

<p>DBS satellite service subscribers of HD programming have already purchased a satellite HD-STB that should have an ATSC OTA HD tuner circuitry included; they should not need the OTA tuner integrated into a HDTV, nor they need a cable tuner.  </p>

<p>An over-the-air antenna TV viewer should just need an ATSC tuner (assuming the viewer already has good DTV terrestrial reception).   An integrated HDTV with an ATSC OTA tuner could be an option; a $400 over-the air STB connected to an HDTV monitor could be another option, if you are offered the option.  </p>

<p>Current D-VHS VCRs only record in HD using the IEEE1394 (Firewire connection) input.  A tuner, any tuner, should have a 1394 output to send the tuned signal to the digital VCR's 1394 input for recording.  DirecTV decided that their STBs would not have that output, DishNetwork has been announcing that is coming with such feature soon (for two years already, and maybe the model 921 is out with the 1394 output enabled by the time you read this), some new OTA STBs have that output.</p>

<p>An integrated HDTV (having a built-in OTA/cable tuner) should also have that output (the RCA Scenium 2003 integrated line 140 was released with a 1394 connection but is only "in", not outputting the tuned HD signal).   </p>

<p>One feature not (yet) included in 2003/4 integrated HDTVs is an integrated time-shifting recording ability as the one found on some new HD-STBs with PVR hard disc drives, although those are on the $1000 range (such as the Zenith HDR-230 recently released).  One recording alternative for integrated HDTVs having two-way 1394 connections is a PVR-only (no tuner) unit, such as the new DVR10 from Thompson/RCA ($450); the TV's 1394-out is for the internal HD tuner to send the signal out for recording; the 1394-in is to playback from the PVR. <br />
    </p>

<p><u><strong>What if you cannot buy an integrated HDTV by 2007?</strong></u></p>

<p>Many consumers would eventually need an economy-level digital STB to convert DTV signals down to NTSC so they can still watch the new digital broadcast using their old analog TVs; they might not be able to afford retiring analog TVs that might still be in perfect conditions.</p>

<p>Many would have several analog tuners (TV, VCR, Tivo, etc.) on the house, and would then require several "low cost" down-conversion STBs.  In order for that to happen the price of STBs needs to be reduced considerably.  New OTA STB models can down-convert but are still in the range of $400. </p>

<p>People should be able to continue using their non-HD TVs for as long as their budget dictates, regardless of the DTV implementation schedule.  With the cable agreement and OTA tuner mandate on could expect that a large mass of HD tuners would be produced, hopefully that would bring prices down as needed. <br />
   </p>

<p><u><strong>Verify the upgrade capabilities of cable tuner/s (and integrated HDTV)</strong></u></p>

<p>Inform yourself to been able to anticipate how the future cable bi-directional features (that are still in the works by the industry) would eventually be applied to the integrated set you might want to buy in the 2003/4 period (with only unidirectional features, or with no CableCARD at all).  <br />
    <br />
In other words, when an agreement is reached about how to implement the bi-directional features, possibly next year, one would hope that it would protect the consumer that helped the implementation of the OTA mandate and Cable agreement with his/her early-integrated purchase. </p>

<p>Otherwise, to been able to have the VOD, impulse PPV, and cable guide features of the bi-directional system, the cable subscriber might be facing a) the early replacement of the cable-integrated TV or HD-STB, or b) the addition of a bi-directional cable HD-STB (read as: pay for another tuner).</p>

<p>One Mitsubishi dealer indicated that Mitsubishi was committed to make their HDTVs future proof, and that included cable tuners.  It is not clear what exactly that would mean, but reference was made to what Mitsubishi did with their "Promise Module" which provided earlier generation sets with 1394 digital connectivity and HD tuning capabilities.  Their 2004 cable-integrated lines are not CableCARD suited.</p>

<p>According to the specific (underlined) wording of the "Promise", it seems that an upgrade path to CableCARD unidirectional or bi-directional might not actually be on their plans:  "We will engineer and manufacture the upgrades necessary so the television you purchase today can be made compatible with near-future advances in digital television and digital interconnectivity. Specifically, we promise that you will be able to have your television upgraded, at a reasonable cost, to include an off-air HDTV tuner, a cable TV tuner (<u>for unscrambled programming</u>) ...  (Underline added)", </p>

<p>However, while Mitsubishi might not satisfy all of the consumer-upgrade dreams to perfection, it is certainly a company that at least offers some comfort by announcing their upgrade plans in written and executing them the best they can for their customers.  Most manufacturers of integrated sets/cable tuners are not committed to any future upgrade plan (we have seen this with DVI digital connectivity before).</p>

<p><br />
<u><strong>Summary</strong></u></p>

<p>For your convenience the following table shows a comparison of integrated TVs vs. monitors of 2003/4. Quoted prices are MSRP when the product was/will be introduced to the market.  Quoted price differences are based on MSRP.  Some HDTV sets introduced early in 2003 might now be publicly listed at reduced MSRPs (or further reduced as sale items on the street); although in some cases that situation is highlighted this report does not intend to do that consistently.    </p>

<p>When comparing the differences of a given line/manufacturer with the current price of a separate HD-STB (as an alternative to integration), take into consideration that some new integrated TVs incorporate two tuners at once, OTA and cable, a feature that usually is not available in a separate cable-HD-STB (DirecTV and DishNetwork satellite STBs include OTA tuners).   Some integrated TVs have only one RF input for DTV reception even when having two internal tuners, in such case a choice would need to made between OTA and Cable services for the use of the plug-and-play connection.  Such limitation does not exist on satellite HD-STBs.</p>

<p>Cable ready sets suited with integrated basic cable tuners for unscrambled non-premium service, or with unidirectional CableCARD/POD tuners, might eventually disappoint uninformed buyers if bi-directional features could not be incorporated transparently to their sets.  </p>

<p>Consumers that dislike STBs sitting on top of their TV monitors should find integration very appealing.  Connectivity will be simpler for them.</p>

<p>Consumers that do not mind STBs might want to investigate lease options that might be offered by the cable company and rent a box until bi-directional features are implemented.  Later, they might want to purchase a cable HD-STB or an integrated set with matured software, hardware, and bi-directional CableCARD features. </p>

<p>The ideas expressed above should not be interpreted as against integration but rather as an eye opener for 2003/4 potential buyers, since tuner prices might not come down significantly until after that period.  Those buyers might also be misled at untrained stores with the plug-and-play appearance, believing that their new cable-integrated HDTV covers all the known cable-features.<br />
	<br />
Our industry leaders showed optimism about integration.  Let us all hope the best by sharing their optimism, and by looking at the bright side of the agreements and mandates.  After all, without any agreements, the progress of HDTV could become much more difficult, slow and expensive for everyone, more expensive than a duplicated integrated tuner.</p>

<table class="type1b" cellspacing=0 cellpadding=0>
<tr><td class="type1b_header">Manufacturer</td><td class="type1b_header">Models/Lines</td><td class="type1b_header">Size</td><td class="type1b_header">Original MSRP  $ Difference</td><td class="type1b_header">Main Feature Difference</td></tr>
<tr><td class="grid">Hitachi</td><td class="grid">2003 SWX Monitor vs. XWXIntegrated lines</td><td class="grid">51"/57"/65"</td><td class="grid">$1300</td><td class="grid">ATSC tuner w/1394</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">2004 S500 Monitor vs.S700 Integrated lines</td><td class="grid">65" &amp; 57"</td><td class="grid">$400</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">2004 S500 Monitor vs.T750 Integrated lines</td><td class="grid">65" &amp; 57"</td><td class="grid">$700</td><td class="grid">ATSC/QAM w/CableCARD</td></tr>
<tr><td class="grid">JVC</td><td class="grid">2003 84 Monitor vs. 94Integrated lines </td><td class="grid">56"</td><td class="grid">$400</td><td class="grid">ATSC tuner with 1394 </td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">65"</td><td class="grid">$300</td><td class="grid">ATSC tuner with 1394</td></tr>
<tr><td class="grid">Mitsubishi</td><td class="grid">2003 current models</td><td class="grid">48"</td><td class="grid">$1100</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">55" &amp; 65"</td><td class="grid">$900</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">2004 models (4Q03, 1Q04)</td><td class="grid">48" &amp; 65"</td><td class="grid">$800 </td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">55"</td><td class="grid">$900</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">Panasonic</td><td class="grid">Integrated line for 4Q03vs. WX 2003 Monitor line</td><td class="grid">47" &amp; 56"</td><td class="grid">$700</td><td class="grid">ATSC/QAM w/CableCARD, DVI(monitors) vs. HDMI (integrated)</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">53"</td><td class="grid">$800</td><td class="grid">Same</td></tr>
<tr><td class="grid">Samsung</td><td class="grid">DLP line</td><td class="grid">61"</td><td class="grid">$500</td><td class="grid">ATSC/QAM unscrambled,without 1394 (unavailable)</td></tr>
<tr style='height:25.5pt'><td>Sony</td><td class="grid">2003 line</td><td class="grid">3 models</td><td class="grid">$700</td><td class="grid">ATSC tuner</td></tr>
<tr><td class="grid">Thomson/RCA</td><td class="grid">2003 Scenium line</td><td class="grid">52" &amp; 61"</td><td class="grid">$700</td><td class="grid">ATSC tuner, 1394 "inonly" </td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">New models announced June03, Scenium line </td><td class="grid">52"</td><td class="grid">$700</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">56"</td><td class="grid">$600</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">61"</td><td class="grid">$400</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">New models announced June03, RCA line</td><td class="grid">52"</td><td class="grid">$700</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">56"</td><td class="grid">$600</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">Toshiba</td><td class="grid">Theater Wide and CinemaSeries current RPTVs</td><td class="grid">3 models</td><td class="grid">$600</td><td class="grid">ATSC/QAM unscrambled</td></tr>
<tr><td class="grid">Zenith/LG</td><td class="grid">New Plasma for Oct//Nov03, Monitor vs. OTA Integrated</td><td class="grid">50"</td><td class="grid">$1000</td><td class="grid">ATSC tuner (1394 infoN/A)</td></tr>
</table>

<p>Note: this table above and the following list are not intended to include all the manufacturers and lines.  Prices are MSRP at product release time.<br />
 </p>

<p><u><strong>Detailed list of manufacturer/line/model/size</strong></u></p>

<p><u><strong>2003 Hitachi RPTVs</strong></u></p>

<p><u>XWX Director's series integrated projectors </u><br />
With ATSC OTA tuner, DVI/HDCP, two 1394/DTCP bi-directional connections to network, D-VHS VCR or Echostar HD-STB, 0.52 mm lenticular screen, auto convergence and manual, 480i to 540p or 1080i, ISF calibration mode, 2 HD component inputs, 5 element lens, AV Network with simple remote. <br />
51" 	51XWX20B	$4300<br />
57" 	57XWX20B	$4800<br />
65" 	65XWX20B	$5300 </p>

<p><u>SWX monitors series</u><br />
No 1394, DVI/HDCP, almost similar to XWX series.<br />
51" 	51SWX20B	$2999<br />
57" 	57SWX20B	$3499<br />
65"	65SWX20B	$3999</p>

<p>$1,300 difference for ATSC tuner and 1394</p>

<p><br />
<strong><u>New Hitachi models for 3Q/4Q 2003</u></strong></p>

<p><u>S500 Monitor RPTV series</u><br />
TTM 3Q03, requires an optional PCMCIA bridge, DVI-HDTV input, two wideband component video inputs.<br />
65"	65S500	$3,800, two-piece cabinet<br />
57"	57S500	$3,300, two-piece cabinet<br />
51"	51S500	$3,000 ($2,500 street)</p>

<p><u>S700 Integrated RPTV Series (without CableCARD)</u><br />
TTM 3Q03, include two IEEE 1394/5C, come equipped with a universal memory card slot, integrated ATSC/QAM unscrambled cable tuners (lacking the CableCARD slot for scrambled channels). Anti-reflective high-contrast shield and first surface mirror, two IEEE 1394/5C interface input/outputs, DVI-HDCP, two wideband component video inputs, optical digital audio output, universal memory card slot input for digital photo display, auto digital convergence with timer and 117-point manual digital convergence<br />
65"	65S700	$4,200<br />
57"	57S700 	$3,700<br />
51"	51S700 	$3,300</p>

<p><u>T750 Integrated RPTV Series</u><br />
TTM 4Q03, integrated ATSC/QAM cable w/CableCARD tuners, Learning AV NET, two-piece cabinet design, auto digital convergence with timer and 117-point manual digital convergence, two IEEE 1394/5C interface input/outputs and a DVI-HDTV digital video, Simple Remote, anti-reflective high-contrast shield and first surface mirror, come equipped with a universal memory card slot.<br />
65"	65T750 	$4,500<br />
57"	57T750 	$4,000</p>

<p>Difference<br />
$700 difference for ATSC/QAM w/CableCARD tuner on 65" and 57" (S500 vs. T750 <br />
$400 difference for ATSC/QAM tuner without CableCARD on 57" and 65" (S500 vs. S700)</p>

<p></p>

<p><strong><u>JVC</u></strong></p>

<p><u><strong>2003 models CRT RPTVs:</strong></u><br />
<u>74 line</u> Monitors<br />
DVI, D.I.S.T at 1080i, HD DSD, 6500K-color temperature, switchable 3:2 pull-down, auto-convergence, selectable scan velocity modulation, white character correction circuitry.<br />
56"	AV-56WP74	$2,200, TTM April 03, ($1,800 street Sep03)<br />
65"	AV-65WP74	$2,900, TTM Mar 03</p>

<p><u>84 new line</u> Monitors<br />
TTM Aug 03, D.I.S.T 1500i, new 16M 10 bit 3D/Y digital comb filter, selectable SVM, new emissive light universal remote, new HD range 75 MHz digital super detail, DVI/HDCP<br />
56"	AV-56WP84	$2,700<br />
65"	AV-65WP84	$3,200</p>

<p><u>94 line</u> Integrated<br />
Upgrade of 84 line models TTM Sep 03, ATSC tuner with two-way 1394<br />
56"	AV-56WP94	$3,100 <br />
65"	AV-65WP94	$3,500 </p>

<p>Difference between 84 and 94 lines for ATSC tuner with two-way 1394<br />
$300 on 65", $400 on 56"</p>

<p><strong><u>Mitsubishi</u></strong></p>

<p><u><strong>2003 Models</strong></u><br />
16x9 RPTVS, no DVI, (TTM current 2003 line), 480i/p/1080i, horizontal resolution 1200 lines, 2 component inputs for 480i/p (which also accept 1080i on the Gold, Gold Plus and Platinum series), 1 HDTV auto-select component in (RGB or RGBHV for 480i/p/1080i).</p>

<p><u>Gold Series</u> Monitors<br />
NSTC and HD-upgradeable with promise module, removable Diamond screen shield (except on the 42"), 0.52 mm pitch lenticular screen, 4 element lens.<br />
48"	WS-48311	$2,200 <br />
55"	WS-55311	$2,600<br />
65"	WS-65311	$3,200, 5 element lens, 2-piece cabinet, 0.72 mm pitch lenticular screen</p>

<p><u>Platinum Series</u> Integrated RPTVs<br />
HAVi system control, 3 digital IEEE 1394/DTCP FireWire input/outputs, 1 digital audio bit-stream SPIF coaxial jack, 1 VGA input 640x480, removable Diamond screen shield, RF digital antenna inputs/tuners for ATSC over the air and QAM unscrambled digital cable, 7" CRTs.    <br />
48"	WS-48511	$3,300, 4 element lens, 0.52 mm pitch lenticular screen<br />
55"	WS-55511	$3,500, 5 element lens, 0.52 mm pitch lenticular screen<br />
65"	WS-65511	$4,100, 5 element lens, 2-piece cabinet, 0.72 mm pitch lenticular screen</p>

<p>Difference for ATSC/QAM cable (no CableCARD) unscrambled and 1394<br />
$1,100 on 48"<br />
$  900 on 55" and 65"</p>

<p><br />
<strong><u>Mitsubishi 2004 models announced April 03, started to appear in Sep 03</u></strong></p>

<p><u>Silver Plus</u> Monitors<br />
MonitorLink connection (DVI/HDCP and RS-232) to connect to new HD-5000 STB for like integrated capabilities, "PerfectColor System," which enables independent control of six colors, and Advanced Multimedia Video Processing, which reduces artifacts in up converted images, 3:2 pulldown, 64 point convergence, MicroFine 3 CRTs (except 42") QuadField Focus, Diamond Shield removable (by user on 48"/55"/65" models, and by a service call on the 42").</p>

<p>42"	WT-42413	$1,900 ($2,100 press release), $1,800 street Sep 03 <br />
48"	WS-48413	$2,200 ($2,000 street Sep 03)<br />
55"	WS-55413	$2,400 ($2,100 street Sep 03), EDF lenses<br />
65"	WS-65413	$3,000 ($3,400 press release), $2,600 street Sep 03, EDF lenses</p>

<p><u>Gold Plus</u> Integrated sets <br />
TSC OTA tuner and QAM unscrambled cable tuner<br />
NetCommand 3.0 on-screen home-theater control system with IR remote code learning capability and MonitorLink/DVI-HDCP inputs. AMVP video processor for pixel multiplication, 5-format memory card readers for MP3, WMA music and JPEG photo files, PerfectColor Precision  6-way, MicroFine 3 CRTs Improved focus, two 1394 rear (one 1394 front), Color Tuned Diamond Shield, 3:2 pulldown, 64 point convergence, QuadField Focus, EDF lenses.</p>

<p>48"	WS-48613	$3,000 ($3,300 press release), 2,700 street<br />
55"	WS-55613	$3,300 ($3,600 press release), 3,000 street, TTM Oct 03, two coax speakers<br />
65"	WS-65613	$3,800 ($4,100 press release), $3,500 street, TTM Oct 03, two coax speakers </p>

<p>MSRP Difference for ATSC/QAM cable unscrambled and 1394<br />
$800 on 48" <br />
$900 on 55" <br />
$800 on 65"</p>

<p><br />
<strong><u>Panasonic</u></strong></p>

<p><u>New Integrated CRT RPTVs</u><br />
TTM October/November 03, ATSC/QAM cable tuners, 1394, component in, HDMI/HDCP, POD (Point of Deployment interface card) for cable, on this first release POD only supports one-way (which means no two-way interactivity for "impulse" PPV and VOD. Panasonic expects to support two-way at a later time.<br />
    <br />
47"	PT-47TWD63	$2,200<br />
53"	PT-53TWD63	$2,500 (to order $2,350 street Sep 03) TTM Nov 03<br />
56"	PT-56TXD63	$2,700 (to order $2,500 street Sep 03) TTM Nov 03</p>

<p><u>New RPTV CRT Monitors</u><br />
<u>WX - Line</u><br />
DVI/HDCP, TTM May 03, 480p/1080i, component, 7" CRTs, 4 speakers, 30W amp, 850 lines resolution, regular screen shield, .52 mm lenticular screen, 2 component in, 3:2 pull-down.<br />
47"	PT-47WX53	$1,500 <br />
53"	PT-53WX53	$1,700 <br />
56"	PT-56WX53	$2,000, DVI/HDCP omitted on Panasonic web-site</p>

<p><u>TW - Line</u><br />
Same features of the WX line plus VIVA sound, anti-reflective shield, 6 speakers, 60-watt amp.<br />
53"	PT-53TW53	$2,000 <br />
56"	PT-56TW53	$2,200 </p>

<p>Difference for ATSC/QAM, and DVI (monitors) vs. HDMI (integrated), specific POD and 1394 2-way capabilities were unavailable at the time of this report, integrated line compared with WX monitor line:</p>

<p>$700 in 47" <br />
$800 in 53" <br />
$700 in 56" </p>

<p><br />
<strong><u>Samsung</u></strong></p>

<p><u><strong>DLP RPTVs</strong></u><br />
<u>New monitor models</u><br />
TTM April 03 to replace the HLM line, HD 2 chip, 1280x720, DNIe (Digital Natural Image Engine) video enhancer (in addition to DCDi), 3:2 pull-down, DVI/HDCP, XGA PC 15 pin input, 2 HD component, scroll and stretch functions.<br />
61"	HLN617W	$5,500 ($5,200 street Sep03), one 480p component in</p>

<p><u>New Integrated models</u><br />
TTM 4Q03, with ATSC/cable tuners (NO 1394 out as of Jan 03 data, and specs still unavailable), other specs same as above.<br />
61"	HCN691W	$6,000, same as HLN617W but with tuners </p>

<p>Difference ATSC/cable tuners $500</p>

<p><br />
<strong><u>Sony</u></strong></p>

<p>Announced on Mar 03<br />
<u>Fully integrated RPTVs</u><br />
ATSC tuners, TTM Sep 03, DVI/HDCP, Enhanced memory stick slots, 3 i.Link 1394 connections.<br />
51"	KDP-51WS550	$2,500 (price drop from $2,700 Sep 03)<br />
57"	KDP-57WS550	$2,900 (price drop from $3,000 Sep 03)<br />
65'	KDP-65WS550	$3,300 (price drop from $3,500 Sep 03)</p>

<p><u>New CRTs based Hi-Scan RPTV monitors</u><br />
DVI/HDCP (announced Jun 03) TTM July 03.<br />
51"	KP-51WS510		$1,800 (price drop from 2,000 Sep 03)<br />
57"	KP-57WS510		$2,200 <br />
65"	KP-65WS510		$2,600 </p>

<p>Difference for ATSC tuner $700 in the 3 models<br />
 </p>

<p><strong><u>Thomson/RCA</u></strong></p>

<p><u><strong>2003 RPTVs RCA Scenium</strong></u><br />
<u>Integrated w/ATSC tuner</u><br />
DVI/HDCP, two 1394 inputs "in only" (DTV Link and AVC networking), 2 HD component in, Ethernet port for Web browsing, optical digital audio output, 1080i, 480p, 3:2 pull-down.<br />
52"	HD52W140	$3,200<br />
61"	HD61W140	$3,800		</p>

<p><u>RPTV Monitors w/DVD player (optional ATSC tuner)</u><br />
TTM current, DVI/HDCP, no 1394, $200 less without DVD player, 3:2 pull-down, 2 HD component inputs.<br />
52"	D52W135D	$2,700 ($2,500 w/out DVD player), TTM current ($1,900 street Apr 15)<br />
61"	D61W135D	$3,300 ($3,100 w/out DVD player)</p>

<p><strong><u>New models announced June 03</u></strong><br />
<u>Scenium Integrated</u><br />
ATSC/QAM unscrambled cable tuners, Net-Connect Ethernet web browsers, Hi-Pix picture system, DVI/HDCP, 2 1394/DTCP 2 way interfaces,<br />
52"	HD52W151	$2,800<br />
56"	HD56W151	$3,100<br />
61"	HD61W151	$3,300</p>

<p><u>RCA line Integrated</u><br />
ATSC/QAM unscrambled cable tuners, true-Scan Digital Reality signal processing, 2 way 1394/DTCP inputs and DVI/HDCP input. <br />
52"	HD52W41	$2,500, TTM July 03<br />
56"	HD56W41	$2,800, TTM July 03</p>

<p><u>Scenium Monitors</u><br />
DVI/HDCP<br />
52"	D52W136D	$2,100, TTM June 03<br />
56"	D56W136D	$2,500, TTM July 03<br />
61"	D61W136D	$2,900, TTM June 03 </p>

<p><u>RCA line monitors</u><br />
DVI/HDCP<br />
52"	D52W20	$1,800, TTM June 03<br />
56"	D56W20	$2,200, TTM June 03</p>

<p>Differences:<br />
2003 models<br />
Scenium 52" and 61" $700 (ATSC tuner, 1394 "in only")</p>

<p>New models announced on June 03 (difference: ATSC/QAM tuners and 1394)<br />
Scenium $700 52", $600 56", $400 61"<br />
RCA line $700 52", $600 56" </p>

<p><br />
<strong><u>Toshiba</u></strong></p>

<p><u>Theater Wide Fully integrated RPTV CRTs</u><br />
Split cabinet design, ATSC/QAM unscrambled tuners, Gemstar's TV Guide, dual DTVLink (1394/DTC) inputs, DVI/HDCP, multicard slot for JPEG image viewing for SD and Smartmedia flash media formats, Power focus HD2 CRTs, CableClear DNR + analog-to-digital video processing, Theater Net IR/1394 ICON Control system.<br />
51"	51H93		$2,700, TTM Aug 03<br />
57"	57H93		$3,000, TTM Jul 03<br />
65"	65H93		$3,500, TTM Aug 03</p>

<p><u>Cinema Series Fully integrated RPTV CRTs</u><br />
TTM Aug 03, ATSC/QAM unscrambled tuners, same as Theater Wide (except for the Theater Net IR/1394 ICON Control system) plus PowerFocus HD4 CRTs, TheaterShield AR, an Accufocus automatic lens convergence system with 56 points and manual setting capability, MegaBrand super wide band video amp.<br />
51"	51HX93	$3,000<br />
57"	57HX93	$3,300<br />
65"	65HX93	$3,800</p>

<p><u>Theater Wide Monitors</u><br />
Except for the tuners, features are similar than above Theater Wide integrated models.<br />
51"	51H83		$2,100 (1,800 street Sep 03), TTM May 03, Dynamic Quadruple Focus, 2 HD component in, 3:2 pull-down,<br />
57"	57H83		$2,400($2,000 street on Sep 03), TTM May 03, 3:2 pulldown, digital auto convergence <br />
65"	65H83		$2,900, TTM Jul 03</p>

<p><u>Cinema Series Monitors</u><br />
PowerFocus HD4 CRTs, PowerFocus HCF Achromatic Lens System, MegaBrand wideband video amplifier, Theater Shield AR, Crystal Scan HDSC 1080i, Cable Clear DNR+, Touch Focus, Accufocus 56 point manual convergence, Theater Net IR ICON system.<br />
51"	51HX83	$2,400<br />
57"	57HX83	$2,700<br />
65"	65HX83	$3,200</p>

<p>Difference<br />
Theater Wide $600 on 3 models for ATSC/QAM unscrambled with 1394 <br />
Cinema Series same as above</p>

<p><br />
<strong><u>Zenith/LG</u></strong></p>

<p>New Plasmas announced Sep 03, LG-branded panels</p>

<p><u>Fully integrated</u> w/ATSC tuner (1394 information N/A)<br />
50"	DU-50PZ60	$11,000, TTM Nov 03, 1366x768, 1000:1 CR, 1000 ANSI<br />
<u>Monitor only version</u><br />
50"	MU-50PZ90V	$10,000, TTM Oct 03, has identical picture performance without tuner.	</p>

<p>Difference $1000 for the ATSC tuner</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 25, 2006  7:00 AM</b>
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
			<?=getComments(291)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 291)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/01/hdtv-integrated-tuners-and-you.php" type="text/javascript" charset="utf-8"></script>
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