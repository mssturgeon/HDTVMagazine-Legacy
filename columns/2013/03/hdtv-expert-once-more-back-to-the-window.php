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
		AND e.entry_id = 5085";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5085 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5085 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5085";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-once-more-back-to-the-window.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5085";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Once More, Back to the &ndash;  Window??" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Once More, Back to the &ndash;  Window??" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Once More, Back to the &ndash;  Window??" />
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
	<title>HDTV Magazine - HDTV Expert - Once More, Back to the &ndash;  Window??</title>
	<meta name="keywords" content="bow tie, clearstream micron, leaf ultimate, yes scores, web site, antenna, antennas, channels, test, leaf, indoor, pull, uhf, channel, reception, pro, amplified, tests, dtv, signal, ’s, may, window, bow, tie" />
	<meta name="description" content="HDTVexpert tests yet another box full of indoor digital TV antennas. Read on to see which ones performed best!" />
	<meta name="title" content="HDTV Expert - Once More, Back to the &amp;ndash;  Window??" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Once More, Back to the &amp;ndash;  Window??" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-once-more-back-to-the-window.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="HDTVexpert tests yet another box full of indoor digital TV antennas. Read on to see which ones performed best!" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5085', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-once-more-back-to-the-window.php">HDTV Expert - Once More, Back to the &ndash;  Window??</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March 25, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=333&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>, <b><a href="/category.php?id=503&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p>Since I launched this Web site ten years ago, I’ve conducted numerous tests of outdoor and indoor TV antennas to see which ones really performed, and which ones were just “aluminum snake oil.” The problem with these tests is that, as soon as I complete one and write it up, I hear from yet another company who missed the boat and wants their time in the sun.</p>
<p>That’s the motivation for this round of tests, which included some previously-tested models and a few newcomers. It’s taken me a few months to schedule this test and round up all of the review models, but the good news is that every one of these antennas is currently offered for sale; some from multiple online retail outlets.</p>
<p>WHY INDOOR TV?</p>
<p>If you subscribe to pay TV services (as I do), you’ve surely noticed two things. (1) The monthly cost of your channel services has gone up over the past decade at a rate far in excess of ordinary inflation, and (2) you probably don’t watch more than 10 to 15 channels anyway on a regular basis.</p>
<p>Now, couple those observations with the expanding universe of Web-based (“over the top”) video channels, including the ever-popular YouTube, Hulu and Hulu Plus, Netflix, Vudu, Amazon Prime, and assorted network-based streaming sites. Add a Roku box, Apple TV, Boxee, or any of a number of OTT receiving solutions; drop the TV channel bundle from your pay TV subscription, and you’ve probably cut your monthly cost by 50%. (This assumes you’re keeping broadband service.)</p>
<div id="attachment_2997" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2997" alt="Good thing I don't do this on a regular basis. They'd never get any work done!" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Wide-View-of-Master-Test-Setup-WEB.jpg" width="600" height="800" /><p class="wp-caption-text">Good thing for the gang at Turner Engineering that I don&#8217;t test indoor antennas on a regular basis. They&#8217;d never get any work done!</p></div>
<p>&nbsp;</p>
<p>All well and good, except that streaming video services are very much dependent on available bandwidth. Watching <i>Modern Family</i> or <i>The Avengers</i> at 2 PM when Internet traffic is light is a completely different experience at 10 PM, when it seems that everyone and their brother is hogging bandwidth.</p>
<p>While there’s not much you or I can do about that problem (except perhaps subscribe to FiOS), you can watch HD broadcast network channels for free all over the U.S.A. And if you live near an urban area, you may have multiple channels you can pull in, using that little “F” connector on the back of your LCD or plasma flat screen.</p>
<p>&nbsp;</p>
<div id="attachment_2998" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2998" alt="The &quot;mighty mite&quot; - a Radio Shack $4 UHF bow tie." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/CU-of-Bow-Tie-Antenna-MR.jpg" width="600" height="325" /><p class="wp-caption-text">The &#8220;mighty mite&#8221; &#8211; a Radio Shack $4 UHF bow tie.</p></div>
<div id="attachment_2999" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2999" alt="NorthVu's NV20 Pro firmly attached (we hoped) to the window. Don't try this at home..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Northview-NV20-CU-on-Window-WEB.jpg" width="600" height="390" /><p class="wp-caption-text">NorthVu&#8217;s NV20 Pro, firmly attached (we hoped) to the window. <span style="text-decoration: underline;">Don&#8217;t</span> try this at home&#8230;</p></div>
<p>&nbsp;</p>
<p>All you need to watch these channels is some sort of antenna. While outdoor antennas always work best, you may live in an apartment or condo where going that route is problematic for cosmetic or legal reasons (even though you do have the right to install an outdoor antenna on property that is yours exclusively, but I won’t get into that now).</p>
<p>The fact is; indoor TV reception has actually gotten easier and better. Yes, I remember the early days of digital TV reception, which involved more luck and prayer than anything else. But we’ve come way past those trial-and-error exercises, and it’s now much easier to pull in local digital TV signals indoors.</p>
<p>All you need is a TV antenna that meets the following criteria: It is resonant or close to resonant at the desired frequencies of reception; can be installed easily on a wall, window, or some other surface suitable for mounting, and is a true plug-and-play design. You just screw on the antenna cable to your TV, go into the appropriate set-up and channel menus, and scan for active channels.</p>
<p>&nbsp;</p>
<div id="attachment_3000" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3000" alt="It's a little bit easier to attach Winegard's FlatWave with masking tape..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Winegard-Flatwave-on-Window-WEB.jpg" width="600" height="419" /><p class="wp-caption-text">It&#8217;s a little bit easier to attach Winegard&#8217;s FlatWave with masking tape&#8230;</p></div>
<div id="attachment_3001" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3001" alt="...as it is to attach the Mohu Leaf. Maybe transparent tape would look nicer?" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Leaf-Ltd-on-Window-WEB.jpg" width="600" height="450" /><p class="wp-caption-text">&#8230;as it is to attach the Mohu Leaf. Maybe transparent tape would look nicer?</p></div>
<p>&nbsp;</p>
<p>ANYTHING GOOD ON TONIGHT?</p>
<p>If you haven’t tried indoor TV reception yet, you may be surprised just how many channels you can pull in. For many folks living in the Los Angeles basin who have a clear shot toward Mt. Wilson, that could mean as many as 27 major DTV channels with over 130 total sub-channels of programming. Heck, that’s a mini cable system into itself!</p>
<p>I live in the Philadelphia metro market, and can consistently receive 15 major DTV channels with over 30 sub-channels of programming. That’s using a modest dual-band yagi mounted at the base of my chimney, along with a similar antenna installed in my attic. And my dual-band UHF/VHF yagi antennas that sit atop a rotor and 5’ of mast on my roof can pull in another 8-10 DTV stations from New York City, which is about 65 miles distant.</p>
<p>These antenna systems supplement my Comcast cable service, which was cut off during Hurricane Sandy for the better part of a week by a 100-year-old oak tree that chopped the cable and telephone lines in half. Using an inverter (since replaced by a generator), I could still watch local news and weather from all of the locations just mentioned.</p>
<p>I’m a little too far away from the Philly TV towers in Roxborough to depend on indoor antennas, which is why I went the rooftop/attic route. But your location may be closer; in which case one of the models tested in this review could be right for you.</p>
<div id="attachment_3002" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3002" alt="Here's the Leaf Ultimate with inline preamp (near bottom of photo) percolating nicely." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Leaf-Ultimate-Wide-View-with-Amplifier-WEB.jpg" width="600" height="800" /><p class="wp-caption-text">Here&#8217;s the Leaf Ultimate with inline preamp (near bottom of photo) percolating nicely.</p></div>
<div id="attachment_3003" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3003" alt="Yes, we actually got a ClearStream Micron XG to stay attached to the test window! (Special formulation for the masking tape?)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/AD-ClearStream-Antenna-with-Amplifier-WEB.jpg" width="600" height="725" /><p class="wp-caption-text">Yes, we actually got a ClearStream Micron XG to stay attached to the test window! (Special formulation for the masking tape?)</p></div>
<p>&nbsp;</p>
<p>As a general rule of thumb, homes and apartments as far away as ten miles from a TV station should be able to pull in the signal with an unamplified antenna. If the TV tower is located at a high altitude, as is the case in Los Angeles, Phoenix, Las Vegas, and Portland (mountains) and New York City and Chicago (skyscrapers), that indoor reception distance can increase by 50% or more.</p>
<p>However, there are locations where indoor DTV reception is borderline reliable or problematic. In those cases, an amplified antenna may be a better choice, as digital signals require a minimum threshold above background noise to be received correctly. For the ATSC system used in this country, the “laboratory” threshold is about 15 dB. In real life with signal echoes and fading, it’s more like 20 dB.</p>
<p>There are caveats with amplified antennas. First, not all amplifiers are created equal! Your particular amplifier may have lots of gain, but strong, nearby out-of-band signals can overload it and create more problems than it is fixing.</p>
<p>Second, amplifiers are noisy, and some more noisy than others. It does you no good to add an amplifier if it increases background noise (or as some call it, the noise floor) along with the signal. So a poorly-designed amplifier can actually make difficult TV reception worse.</p>
<div id="attachment_3004" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3004" alt="Here's what the UHF TV spectrum looks like on the bow tie antenna..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/RS-Bow-Tie-Antenna-Chs-14-51-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">Here&#8217;s what the UHF TV spectrum looks like on the bow tie antenna&#8230;</p></div>
<div id="attachment_3005" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3005" alt="...and here's what it looks like on the NorthVu NV20 Pro." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Chs-14-51-View-300-kHz-RBW-NV20-PRO-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">&#8230;and here&#8217;s what it looks like on the NorthVu NV20 Pro. RF carriers from channels 18, 29, and 51 are anywhere from 3 dB to 9 dB weaker than on the bow tie, while channels 33 and 40 are barely there.</p></div>
<p>&nbsp;</p>
<p>THE COMPETITORS – PASSIVE DIVISION</p>
<p>I selected nine different antennas for this latest round. Five were unamplified, and four had some sort of internal or external amplification. One of the amplified antennas (Mohu’s Sky) is actually intended for outdoor use, but I figured I’d see just how well it performed by a window anyway. (The Sky will be part of an outdoor antenna test soon.)</p>
<p>To kick things off, I needed a reference indoor antenna. What better choice than the classic UHF bow tie, which Radio Shack used to sell for all of $4.00? Although The Shack has since dropped this antenna from its catalog, you can still find them online. <a href="http://www.summitsource.com/steren-antenna-indoor-outline-hdtv-only-enhances-inside-reception-chrome-plated-brass-flat-cable-with-spade-connectors-balun-part-petra-p-9077.html?ref=1&amp;gclid=COHns4vAmLYCFVGf4Aodrh4ABw">Summit Source has one made by Steren for all of $2.49</a>.</p>
<p>Next up is the <a href="http://www.northvu.com/Nv20ProProduct#1">NorthVu NV20 Pro</a>, a VHF/UHF panel antenna that claims to use a fractal-based design to improve resonance and performance. NorthVu is a Canadian company and its Web site promotes the use of free digital TV to cut costs of cable. A number of retailers carry it (including Amazon) and it will set you back about $60, plus shipping.</p>
<p>Batting in the #3 spot is the WallTenna, which I’ve tested previously. This flexible, super-flat antenna is intended for UHF reception only, although it might pull in VHF stations if the transmitter is close by.  At present, <a href="http://www.walltenna.com/index.html">WallTenna is sold direct through the company’s Web site for $35</a>.</p>
<p><a href="http://www.winegard.com/flatwave/costco.php">Winegard’s FlatWave</a> flexible panel antenna was another solid performer from previous tests, so it deserved another go-around. You can find it at numerous online sites and also in Costco, but prices are all over the place, ranging from $20 to $36. Shop carefully!</p>
<p>No test of indoor antennas would be complete without <a href="http://www.gomohu.com/">Greenwave Scientific’s Mohu Leaf</a>, a strong performer in previous antenna tests. You can find it at numerous online and brick-and-mortar retailers (Sears, B&amp;H, Amazon, Sam’s Club, J&amp;R) for$40. You can also buy it direct from Greenwave.</p>
<p>&nbsp;</p>
<div id="attachment_3006" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3006" alt="Here's what WMBC-18 looks like with the WallTenna." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/WMBC-18-300-kHz-RBW-Walltenna-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">Here&#8217;s what WMBC-18 looks like with the WallTenna.</p></div>
<div id="attachment_3007" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3007" alt="And here's what WMBC-18 looks like as received by the bow tie." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/WMBC-18-300-kHz-RBW-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">And here&#8217;s what WMBC-18 looks like as received by the bow tie. Not much difference!</p></div>
<p>&nbsp;</p>
<div id="attachment_3008" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3008" alt="WNJM-51, as received on the FlatWave antenna..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/WNJM-51-300-kHz-RBW-Flatwave-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">WNJM-51, as received on the FlatWave antenna&#8230;</p></div>
<div id="attachment_3009" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3009" alt="...and the same station, as received by the NorthVu NV20 Pro. " src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/WNJM-51-300-kHz-RBW-NV20-PRO-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">&#8230;and the same station, as received by the NorthVu NV20 Pro.</p></div>
<p>&nbsp;</p>
<p>THE COMPETITORS – AMPLIFIED DIVISION</p>
<p>Four more antennas rounded out the test, and all of them use active electronics to boost signal levels. NorthVu sent along the <a href="http://www.northvu.com/Nv20ProAmplifiedProduct#.UVCb5jfQjng">NV20 Pro Amplified</a>, which looks exactly like the NV20 Pro except that it has a built-in power supply with AC cord. It’s currently selling for about $90, and Amazon has it.</p>
<p><a href="http://www.antennasdirect.com/store/ClearStream-Micron-Indoor-Antennas.html">Antennas Direct’s ClearStream Micron XG antenna</a> is a panel design that comes in several flavors – (1) bare bones, (2) with a variable multi-step inline amplifier, (3) with a separate reflector panel, and (4) with both options together. Figure $80 for the basic panel with amplifier and $130 for the loaded system (which I tested). Oddly, the AT Web site currently lists a lower price for the basic panel antenna and amplifier ($79.99) than for the antenna alone ($89.99)!</p>
<p>The <a href="http://store.gomohu.com/the-leaf-ultimate-hdtv-antenna.html">Mohu Leaf Ultimate</a> is basically a Leaf antenna with an outboard preamplifier. Otherwise, it looks identical to the passive Leaf antennas, and you can find it at the same retail outlets for $90. (Sam’s Club had it for $55 at the time I wrote this.)</p>
<p>Finally, <a href="http://store.gomohu.com/sky-hdtv-outdoor-antenna.html">Mohu’s Leaf Sky antenna</a> isn’t really an indoor design, but it’s small enough that I thought it would be fun to include it in this test. You may recall some of the bar-style VHF/UHF antennas that were popular a number of years back at the start of the digital TV transition: These could be installed on a roof or mounted on inside or outside walls. I figured it was worth seeing how well the Sky did on a very large window with minimal amounts of metal nearby to de-tune its pattern.</p>
<div id="attachment_3010" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3010" alt="I think we reached the practical load limit for 1&quot;-wide masking tape during this test!" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Mohu-Sky-on-Window-WEB.jpg" width="600" height="450" /><p class="wp-caption-text">I think we reached the practical load limit for 1&#8243;-wide masking tape during this test!</p></div>
<p>&nbsp;</p>
<div id="attachment_3011" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3011" alt="They may be hard to see, but there are two 8VSB carriers in there - WABC-7 (left) and WNJB-8 (right). There's just too much noise and not enough carrier-to-noise separation to pull in these signals with the ClearStream Micron XG." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/WABC-7-WNJB-8-50-dBm-REF-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">They may be hard to see, but there are two 8VSB carriers in there &#8211; WABC-7 (left) and WNJB-8 (right). There&#8217;s just too much noise and not enough carrier-to-noise separation to pull in these signals with the ClearStream Micron.</p></div>
<div id="attachment_3012" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3012" alt="The Leaf Ultimate couldn't do anything to help WABC's signal, but it did pull in WNJB-8 nicely (that hill just to the right of screen center)." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/WABC-7-WJNB-8-50-dBm-REF-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">The Leaf Ultimate couldn&#8217;t do anything to help WABC&#8217;s signal, but it did pull in WNJB-8 nicely (that hill just to the right of screen center).</p></div>
<p>THE TEST</p>
<p>For consistency, I decided to head back to the scene of my early DTV converter box and antenna tests – Turner Engineering, in Mountain Lakes, NJ. The Turner building is located on a bit of a rise with a decent view to the east, northeast, and southeast; good enough to pull in numerous DTV stations from the Empire State Building in New York City, as well as various DTV stations in northern New Jersey.</p>
<p>John Turner, president of the company and a life-long &#8220;tinkerer,&#8221; has always been a willing and eager accomplice in these tests, so we set up an area in his front office where we could attach each antenna to a window using copious amounts of masking tape (non-inductive!).</p>
<p>I was also able to find some space to set up the test gear, which included an AVCOM PSA-2500C spectrum analyzer, my Toshiba laptop, Hauppauge’s <a href="http://hauppauge.com/site/products/data_aero-m.html">Aero-M USB stick DTV receiver</a>, and Turner’s in-house DTV receiver system (a Samsung DTB-H260F ATSC set-top box, no longer available, and the legendary Princeton AF3.0HD 28-inch HD CRT monitor that was quite popular in the late 1990s.</p>
<p>The test was simple. After each antenna was attached to the window (not an easy task with some of the heavier models), I recorded the spectral views of various DTV channels from 7 (WABC-DT) through 51 (WNJM-DT). I also recorded wide views of the UHF TV spectrum from channels 14 through 51, and selected views of other high-band VHF DTV stations.</p>
<p>The final part of the test involved verifying reception without any dropouts or “hits” for at least 30 a minute. I also recorded MPEG transport streams from various stations to verify the bit error rate (BER) was indeed low.</p>
<p>If I didn’t see any hits and recorded a clean MPEG stream, the test antenna was rated OK for that channel. If the signal locked up even briefly or I saw too many dropped bits in the MPEG stream, it received an INT grade. If the station’s PSIP (Program and System Information Protocol) was detected by the Samsung and Hauppauge receivers, but the receiver couldn&#8217;t tune it in, the antenna received a NO grade for that channel.</p>
<p>&nbsp;</p>
<div id="attachment_3013" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3013" alt="Here's a view of the UHF TV spectrum as &quot;seen&quot; by the NorthVu NV20 Pro with amplifier." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Chs-14-51-300-kHz-RBW-NV20-AMP-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">Here&#8217;s a view of the UHF TV spectrum as &#8220;seen&#8221; by the NorthVu NV20 Pro with amplifier.</p></div>
<div id="attachment_3014" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3014" alt="Here's a view of the same channels from the Leaf Ultimate." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Chs-14-51-300-kHz-Leaf-Ultimate-WEB.jpg" width="600" height="317" /><p class="wp-caption-text">Here&#8217;s a view of the same channels from the Leaf Ultimate. WNJM-51 (far right) is quite a bit stronger through the NV20 Pro, but the Leaf Ultimate is grabbing a much stronger signal from WMBC-18 (left).</p></div>
<p>&nbsp;</p>
<p>THE RESULTS</p>
<p>Table 1 shows how each antenna fared for 11 different channels. One (WNJB) was on channel 8 in the Warren Hills of New Jersey, while the remaining ten channels  were all UHF and came from Empire and selected locations in New Jersey. The two strongest were WMBC-18 (Montclair NJ) and WNJM-51 (also Montclair), less than 11 miles away.</p>
<p>In addition to the channels listed, I also scanned for WABC-7 (previously received in tests at this location), WPIX-11, WNET-13, WNYE-25, and WNJU-36. However, none of the antennas were able to successfully pull in these stations aside from an intermittent signal here and there, so I dropped them from the test results.</p>
<p>The “No Amplifier” tests were surprisingly competitive, although I didn’t expect the cheapest antenna to be the best performer. But that’s how it played out as the UHF bow tie earned nine YES scores, one INT, and one NO. It was the only antenna to pull in WNYW’s signal on channel 44, a notoriously tough catch at this indoor location.</p>
<p>The WallTenna, Winegard’s FlatWave, and the Mohu Leaf all tied for second place with seven YES tallies, but the WallTenna and Leaf edged ahead by pulling in WNBC’s signal on channel 28 somewhat cleanly whereas the FlatWave couldn’t lock it up.</p>
<p>NorthVu’s NV20 Pro was the biggest disappointment in this test. It only garnered four YES scores against seven NO tallies. I would have expected a lot better, based on the preliminary specifications and information I received from NorthVu’s product management folks.</p>
<div id="attachment_3025" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3025" alt="Table 1 - comparison of passive (top) and amplified (bottom) indoor antenna performance." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Test-Results-Chart-With-Color-WEB.jpg" width="600" height="186" /><p class="wp-caption-text">Table 1 &#8211; comparison of passive (top) and amplified (bottom) indoor antenna performance.</p></div>
<p>&nbsp;</p>
<p>Intriguingly, the NV20 Pro is also about the same size as the late, lamented Kowatec CS102; one of the best indoor UHF antennas I’ve ever tested. (Hey, antenna manufacturers! Maybe one of you can scoop up the rights to the CS-102 and resurrect it?)</p>
<p>Things were a bit more exciting in the amplified antenna competition. Mohu clearly had the upper hand here with their Leaf Ultimate product, as it gathered up ten solid YES scores and a solitary INT (for WNYW, of course!) The new Sky product acquitted itself well as an indoor antenna, also bagging ten YES scores and a single NO (from guess who?).</p>
<p>The ClearStream Micron XG (without the reflector, which no other antenna offered or used) came in behind these two with seven YES and three NO tallies, plus a single INT from our friends on channel 44. Once again, NorthVu brought up the rear with their NV20 Pro Amplified, which fared only slightly better than the basic NV20. It scored five YES, three INT, and three NO tallies.</p>
<p>&nbsp;</p>
<div id="attachment_3015" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3015" alt="We checked for reception through all antennas using this vintage Princeton AF3.0HD CRT monitor. Remember CRT monitors?" src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/Verifying-Reception-on-Princeton-Monitor-WEB.jpg" width="600" height="399" /><p class="wp-caption-text">We checked for reception through all antennas using this vintage Princeton AF3.0HD CRT monitor. Remember CRT monitors?</p></div>
<div id="attachment_3016" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-3016" alt="This is what the ClearStream Micron XG preamp looks like. Notice the four operating modes, selectable with a small tactile pushbutton." src="http://www.hdtvexpert.com/wp-content/uploads/2013/03/ClearStream-Cartridge-Amplifier-TCU-CROP-WEB.jpg" width="600" height="493" /><p class="wp-caption-text">This is what the ClearStream Micron XG preamp looks like. Notice the four operating modes, selectable with a small tactile pushbutton.</p></div>
<p>&nbsp;</p>
<p>CONCLUSIONS</p>
<p>It says a lot that the least-expensive and simplest unamplified antenna design took on all comers and won. It also implies that the particular location where the antennas were mounted just seemed to favor the bow tie this time around (we didn’t test it with an amplifier). These tests were conducted in March with no foliage on nearby trees, whereas my last test was in late July of last year with trees fully leafed out. Even so, the bow tie did pull in WNYW-44 solid as a rock for as long as we chose to watch, something no other passive or amplified antenna could do.</p>
<p>All of the antennas performed equally well at the low end of the UHF band (channel 18) as they did at the high end (channel 51). Five of them were able to haul in channel 8 (about 180 MHz) reliably, which is an impressive feat for such small antennas that expect to work a lot better at UHF frequencies.</p>
<p>Ironically, only two amplified antennas could pull in WWOR on channel 38, something the bow tie did with relative ease. On channel 30 (WFUT), the NorthVu NV20 Pro was the only antenna that couldn’t hook up to the signal. A similar situation occurred with ION-31, not receivable on any of the passive antennas, but plenty strong with the Leaf Sky, Leaf Ultimate, and ClearStream Micron XG. Once again, the NV20 Pro Amplified just couldn’t pull it off.</p>
<p>I should mention that the ClearStream Micron XG’s preamplifier was set to a maximum of 15. Any higher, and the noise floor was degraded, something I could easily see on the spectrum analyzer. In general, I like to keep amplifiers at about 10 dB maximum to guard against this problem – too much gain creates all kinds of reception issues, and you only need to boost the signals up high enough to maintain the required carrier-to-noise ratio (CNR) for reliable digital TV reception.</p>
<p>The separate preamp supplied with Leaf’s Sky and Ultimate antennas is a good design, adding minimal noise while providing sufficient gain to pull signals out of the mud. I can’t say anything about the quality of the NV20 Pro’s amplifier as it is mounted internally, but in my tests it did not appear to add much noise to any of the received signals.</p>
<p>Based on these and previous tests, I’d give the WallTenna, FlatWave, and Leaf a thumbs-up. If you can find one, the bow tie is cheap enough to play around with and may fit the bill. (Hey, Starbucks coffee costs more and the thrill doesn’t last as long). I can’t recommend the NV20 Pro, though.</p>
<p>In the amplified crowd, the Leaf Ultimate and Sky both deliver solid performance. It is a testament to the design of the Sky that it worked so well indoors, but if you opt to use it this way, make sure you have a large window and keep it at least 2-3 feet away from any metal objects.</p>
<p>Antenna Direct’s ClearStream Micron XG is a decent performer, but expensive. I can tell you from a previous test that the reflector made little difference, but if that&#8217;s your cup of tea, position the antenna on a non-metallic surface (bookshelf, window ledge, etc.), aim it towards the TV transmitters when using the reflector assembly, and don’t run the preamp higher than the ‘15’ setting.</p>
<p>Most importantly, keep in mind that you don&#8217;t need to spend a lot of money to get reliable indoor TV reception. My best performers in the passive category were all under $50, and some were under $40. Check TV reception sites first (TVFool.com is one of the best) to get an idea of how strong signals may be at your location before you buy.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March 25, 2013  4:27 PM</b>
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
			<?=getComments(5085)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5085)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/03/hdtv-expert-once-more-back-to-the-window.php" type="text/javascript" charset="utf-8"></script>
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