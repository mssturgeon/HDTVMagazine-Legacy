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
		AND e.entry_id = 290";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 290 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 290 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 290";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/02/is-hdtv-complex-enough.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 290";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Is HDTV Complex Enough?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Is HDTV Complex Enough?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Is HDTV Complex Enough?" />
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
	<title>HDTV Magazine - Is HDTV Complex Enough?</title>
	<meta name="keywords" content="copy protection, def dvd, content protection, rear projection, dvi hdmi, hdtv, could, people, image, digital, dtv, set, dvd, content, analog, because, might, protection, years, video, those, issues, still, better, crt" />
	<meta name="description" content="Some people like to know the HDTV subject in detail before making their purchase; they research an overwhelming volume of technical background and specifications, they feel as earning a PhD in HDTV.  However, if you are among the majority of people that want a modern TV and are confused when trying to understand HDTV, you could always apply the simple approach of going to the corner store, amaze your eyes, and sign the check.  Is it worth to know well what are you buying?  Let us look at both approaches." />
	<meta name="title" content="Is HDTV Complex Enough?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Is HDTV Complex Enough?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/02/is-hdtv-complex-enough.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Some people like to know the HDTV subject in detail before making their purchase; they research an overwhelming volume of technical background and specifications, they feel as earning a PhD in HDTV.  However, if you are among the majority of people that want a modern TV and are confused when trying to understand HDTV, you could always apply the simple approach of going to the corner store, amaze your eyes, and sign the check.  Is it worth to know well what are you buying?  Let us look at both approaches." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=290', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/02/is-hdtv-complex-enough.php">Is HDTV Complex Enough?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February  7, 2006</b>
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
				<blockquote>The following article originally appeared in HDTVetc magazine in their November 2004 issue.</blockquote>

<p><img src="/images/articles/confusion.jpg" align="left">Some people like to know the HDTV subject in detail before making their purchase; they research an overwhelming volume of technical background and specifications, they feel as earning a PhD in HDTV.  However, if you are among the majority of people that want a modern TV and are confused when trying to understand HDTV, you could always apply the simple approach of going to the corner store, amaze your eyes, and sign the check.  Is it worth to know well what are you buying?  Let us look at both approaches.      </p>

<p><br />
<strong>The Traditional Approach for Selecting a TV Set</strong></p>

<p>For about 50 years buying a television set was mainly a matter of deciding what size of CRT (Cathode Ray Tube) was good for your family room.  If you were looking for a screen size larger than 35 inches, it was a matter of finding a rear projection set (RPTV) rather than a direct-view tube.  Some people were interested in CRT front-projectors with a big white screen to project the image from far away in a large room, a theater style approach, but the approach was not cheap.  The mass market of TV buyers was mostly into the direct-view and rear projection equipment, all in one box solution.</p>

<p>People usually invested good time in comparing and viewing images before buying, because for many "that TV" was "the TV" for the next 10/15 years.  Image quality became very important when screen sizes grew with the arrival of RPTV.  The larger image of RPTV was not as crispy as direct-view tubes, and over the last decade cable and satellite service providers started to get interested in over-compressing channels to make space for more channels, that affected image quality; their business model was, and still is, quantity not quality. </p>

<p>On those days the task of comparing sets was much less complicated than with today's different technologies of microchip display implementations, panels, levels of DTV resolution (SDTV, EDTV, HDTV), etc.  Although price was important, selecting a set was a relatively straightforward process; people did not have to think beyond the issues of size and image.  When you think about it, why not?  The purpose of a TV was ultimately to view video images, the more realistic the better.</p>

<p><br />
<strong>The H/DTV Approach</strong></p>

<p><strong>First, a Widescreen View</strong></p>

<p>On past issues of this magazine I provided recommendations and tips to help you deal with individual subjects, such as how to buy an HDTV, how tuner-integration may affect the pocket of an uninformed buyer, especially a subscriber to satellite or other bi-directional cable STB services (like video on demand), how copy protection and connectivity could affect your selection of products and your ability to record and view HD.  Each subject was targeted to people dealing with the particular needs.</p>

<p>On this article, I am looking at a wider perspective, a forest rather than the trees of each of those issues.  I analyze how the multi-level complexity could affect the level of confusion about HDTV, especially if wanting to know sufficiently before purchasing.</p>

<p>Complexity and confusion have the potential to affect the timeliness of the transition to H/DTV, a) because confused people are usually reluctant to buy a relatively expensive product that might become restricted in its abilities of viewing and recording HD content, when the primary purpose is exactly that, and for which people paid premium prices for the hardware and the content, and b) because people that actually bought could start finding things that stop working or do not work properly and could bring to the surface a lot of negative feedback, enough to discourage others.     </p>

<p>In other words, you can do a great effort at informing yourself sufficiently, read all the material, do the homework, and, as I said before, get your PhD in HDTV.  But then brick walls start to appear in between the components of your newly purchased HD system, and they do not interoperate anymore as you thought they would (downrez of paid premium content over analog connections, to mention one).  </p>

<p>Therefore, and here it is the second reason for this article, no matter how informed your purchase was there are areas out of your control that could potentially limit the functionality and capability of your HD system, and you start to have doubts about what actually works, what does not, and what might not work any more, for legacy adopters and for new adopters as well.  </p>

<p>Now that you are eager to graduate with your PhD in HDTV, get ready and gain enough strength to deal with the next levels of complexity, as follows:</p>

<p><br />
<strong>1998, the Starting of the Complexity</strong></p>

<p>In 1998, buying the first HDTV or HD-STB was quite a challenge; those were the real days of early adoption.  Even when knowing what you were doing, there was a high risk of ownership just to experiment with this groundbreaking technology.  Knowledge and expert advice at the video retailer was almost non-existent, which continued to be the case for several years after that.  Only a small selection of a few HDTVs and a handful of HD-STBs were available.</p>

<p>However, the incredible beauty of HDTV images, not seen before in a consumer product, and the trill and experience of making it work, was worth the investment to the few that took the first step into the technology.  It was common to hear from those adopters that the beauty of the image made them watch programs they never though they would, and is still happening today.      </p>

<p>Six years ago, very little information was available about the technology, equipment, connectivity, etc.  Gradually, thanks to the Internet and magazines, a lot of information became available, although it required quite a bit of good research to avoid getting confused with the contradictions and errors of certain sources.  </p>

<p><br />
<strong>Today</strong></p>

<p>Since its implementation in 1998, HDTV has experienced an incredible growth in the variety and number of technologies and products, with hundreds of TV models, dozens of manufacturers, even those that never were into TV before, like computer companies that are now associated to HDTV's software or hardware in way or another, such as Microsoft, Dell, HP, and Gateway, to mention a few.  There is a remarkable increase of consumer options, and that requires a broader and deeper knowledge from sales personnel, and from consumers wanting to make an intelligent purchase.</p>

<p>We are now in the stage of consumer awareness and interest about HDTV, a stage where every business also sees an opportunity to profit from it.  However, like with other technologies, HDTV is taking time to settle in, but some technologies never do and pass away, some catch-on with incredible acceptance like DVD, others settle within a big cloud of dust of heavy competition of some software or hardware innovation: "our format/product is better, cheaper", this is not new in consumer electronics.  Some people say that HDTV is taking too long to implement and broadcasters are dragging their feet, some other say that having now a large selection of HD programming from cable, satellite, and over the air DTV, there is enough incentive to buy a set, regardless of the complexity and the confusion.       </p>

<p><br />
<strong>First Level of Complexity</strong><br />
 <br />
Today, selecting a TV based on size and image could still be simpler as the traditional way.  Regardless if the technology is analog or digital, the purpose of a TV continues to be "to view video images the more realistic the better".  However, it could be more beneficial for you to know a bit more about how the image is created by the TV you like, because it could help you anticipate the video imperfections you could detect at the store (is it slow? is it with poor blacks? etc.).</p>

<p>It might be disappointing at home when a friend of yours makes you aware that your new TV is known to produce grayish blacks or has a screen-door effect; from that moment on you would concentrate on those imperfections rather than enjoying the program, and you might feel guilty of not having investigated enough about the subject before visiting the store.  </p>

<p>DTV is a positive evolution of television captivated with the "digital everything" world of today, and "in theory" the image should be better, but that is not always the case.  A beautiful HD picture could be ruined by over-compression; regular channels would be worst on a larger screen, their lower resolution becomes more obvious if the image is expanded to fit the screen; this is a concern of most people, they expect that image quality in a digital TV would be better not worst, and end up watching just the handful of HD channels ignoring the rest.  </p>

<p>No matter how much you manage to be informed to select your DTV, there are many elements outside your set that could affect negatively the image you view, and is good to know about those elements as well.  I will not cover these here; this is a subject for a full article, unfortunately.       </p>

<p>Many people discover after their purchase that "some things do not work as we thought", and "no one told us of this and that, otherwise we would not have bought this particular set".  They wish they could have spent more time informing and educating themselves before the TV arrives at home.  Actually, in most cases, they realize that what was more important was to "find a knowledgeable person" that expertly and impartially help them look across the market and explain in simple terms what needs to be known for their application, rather than getting the biased advice of the store sales person that wants to sell "that" TV because they have too many of those in the storage.  </p>

<p>Since most people are casual buyers they probably are getting the advice from a store that sells X and Y products only.  Things start to get more complicated when rationalizing the setup and connectivity conditions to view, record, distribute, and connect digital TV signals, in other words "a HD system", not just a HDTV.  If you follow the traditional TV selection approach, you will soon realize that the many technologies and types of displays would make your selection process much more complex and time consuming. </p>

<p>For example, you could view at the store display technologies such as Digital Light Processing (DLP), Liquid Crystal Display (LCD), Cathode Ray Tube (CRT), Liquid Crystal on Silicon (LCoS), and the proprietary versions of if like Sony's SXRD and JVC's D-ILA, and Plasma panels (PDP).  Each of them has generic and proprietary characteristics (and imperfections) that create and affect the image differently: the rendering of color, depth of black, washed/strong whites, visible pixel structure, speed of reaction to fast images, etc.  You will begin to notice that is not as simple as comparing the images of the old CRT sets, you are not comparing apples to apples; and that is only one group of variables.  <br />
 <br />
Additionally, the same technology can produce the image as rear projection (RPTV), but also as front projection (FPTV), and as a direct-view panel, and they will look differently, a RPTV set could be appealing while the FPTV set be unattractive, which makes the understanding of the alternatives more difficult.  For example, an LCD rear projection set could not show the same quality of image as an LCD TV panel of the same size; a 40 inches rear projection CRT set will not show an image as crisp as a 40 inches direct-view CRT tube.</p>

<p>Combine all those factors across technologies, and you are in for a few weeks of viewing, questioning, reading, and understanding why a set could be the wrong choice, or could be unsuitable to your application due to your lighting environment, the distance between viewer and image considering your space, etc.  Some of them double up in price but not giving in return a double up of the image quality or screen size, but they might be very enchanting because they hang from the wall.  Some people might find very disenchanting to know that their new "HDTV" that is already hanging on the wall is just an EDTV (enhanced definition, progressive DVD quality); no one mentioned a word at the store, and the specs were very misleading; too late now. </p>

<p>Interestingly enough the old CRT is still the best bang for the buck, however, the large cabinet of a CRT rear projection set might not impress your wife/husband as much as its image impresses your eyes.  However, if you want one of those anyway, you might be out of luck if you wait for too long, Pioneer already announced in January of this year at CES 2004 their shift to plasma only, discontinuing their beautiful CRT Elite RPTVs, and Panasonic announced in November 2004 their abandonment of CRT RPTVs manufacturing as well.  Other manufacturers continue their production of new CRT sets, but are gradually moving their resources to alternate technologies, such as DLP, LCD, and plasma.</p>

<p><br />
<strong>Second Level of Complexity</strong><br><br>Although the above is already complex enough, it is a "less intellectually involving" way to select a set, and most consumers still purchase that way, but a growing group of consumers want to know much more before spending thousands of dollars on a TV, because they feel that an HDTV is not just another TV for the house.  The knowledge they gather facilitates their decision, specially when finding that the information from some retailers contradicts other retailers, or mismatches their researched material, or find in some Internet forums that a large number of owners are disappointed with X technology or piece of equipment, the one the retailer was recommending.  </p>

<p>On the other hand, people might find information that sometimes produces more harm when misinterpreted or let it become focal points of concern.  While reading about a TV, they might become too worry with X and Y artifacts, even when they cannot notice them in the actual viewing with the help of an expert.  One example is the rainbow effect of the color-wheel in one-chip DLP implementations; the artifact might bother some people that are sensitive to it, but not others.   </p>

<p>Valuable knowledge should be used when making the HDTV decision, but every technology will always have some kind of weakness, imperfection, and lower performance factor.  It would be almost impossible to find "the" set that is the absolute perfection for all the lighting and viewing conditions, resolutions, scaling, etc.  </p>

<p>Likewise, there will always be some factor that makes any technology better when compared to another technology "on that factor", like the rendering of black for example, but that other technology that is poor in blacks could be perfect in another factor, like great whites, or fast response time.  What is important to you?  You need to assign some weight to the factors that are important to you, and guide your decision by your common sense, not the pressure from the sales person.</p>

<p>People find the difference between the DTV levels (standard, enhanced, and H(igh)DTV), and then they start counting pixels, and measuring resolution vertically and horizontally.  Then they learn about the properties of interlaced and progressive scanning, what that means for their HDTV images and DVDs, and how the interlaced format affects fast images, and how movies transferred from films are shown in video. </p>

<p>They move on to a higher complexity when they learn that an HDTV was designed to display an image at a native rate (i.e. 720p or 1080i).  They learn that it is better to show the image at its original rate, but there are HD channels in 720p (ESPN, ABC), while the rest are in 1080i.  They learn that it is very unusual to find sets capable to sync to both 720p and 1080i scan rates, and conclude that some channels would need to be converted/scaled down or up to been able to be matched and displayed at the native rate of the set; they also learn that unnecessary conversions/scaling should be avoided because they generally degrade the quality of the image and add artifacts.</p>

<p>While researching information about the eternal battle of 720p vs. 1080i, people learn that a fast basketball match recorded in progressive 720p (well suited to fast action) could end up showing interlaced artifacts when converted to a 1080i interlaced set.  On the other hand, a fixed pixel display such as DLP, LCD and plasma, should show the 720p basketball match very well, but another image with beautiful scenery captured in detail with the higher horizontal resolution of 1080i, would have to be scaled down to map the pixel count of at type of display.</p>

<p>Some fixed pixel displays are now starting to appear with the full horizontal resolution of 1080x1920, but this is not the case with the vast majority of that market.  Additionally, they learn that TVs that are not fixed pixel displays, like CRT, are not actually capable to show the full 1920 pixels of a 1080i HD image (except for some expensive projectors). </p>

<p>Then people learn that the format conversion is most probably done by a HD set-top-box (or an integrated tuner within a TV), and that box is one of the mentioned on Internet forums, where owners report that it freezes, needs to be rebooted more often than Windows 3.1, produces blurry images of regular non-HD material, its digital outputs are disabled, the CableCARD has taken several months of cable-engineers visits to make it work and still does not, etc.  </p>

<p>Then they try to rationalize: all these conversions, limitations, complexity, and confusion, and we are still not viewing HD at its full potential?</p>

<p>Some people say "is OK, I bought the HDTV primarily to watch my widescreen DVDs".  Then find to their surprise that their "widescreen" DVDs still appear with black bars when shown on their new "widescreen" HDTV (the DVD movie image is more panoramic than the 16:9 aspect ratio of HDTV).  Actually, they are watching "all of the image" of the movie, in the cinematic aspect ratio intended by the director, regardless how large the black bars might be.</p>

<p>Although the black bars are thinner than on their previous 4:3 set, they still feel cheated because they bought their "widescreen" HDTV so they could finally get rid of all the black bars.  Their first reaction is to doubt the merit of switching from the old 4:3 TV, especially when most of the hundreds of cable and satellite channels still are (and would be for long) in 4:3, and they will now show on the new HDTV with side pillars unless they are expanded to fit the 16:9 frame of the TV.  Some people would never accept it, adding one more factor to the their confusion, the aspect ratio adaptation.<br />
  <br />
Unfortunately, many people learn about these things after they made their selection, and find the confusion more painful.  As you see, some things could not be solved 100% by choosing another DTV.  Well-informed consumers could do something to help the confusion, share their knowledge to train the sales person; hopefully another consumer would get the benefit indirectly.   </p>

<p>DTV was intended to improve image quality and to provide a digital future of interactivity and convergence with digital computing and networking.  It takes time to understand how to put together the necessary components to view and record HD depending how you receive DTV signals (satellite, cable, antenna). </p>

<p>So it is very typical for people to cut the hassle short and ask very simply: "tell me what to buy".  They are confused with the many variables of the "modern DTV technology", it is hard for them to rationalize the choices, and even if they do, they find unreasonable that they have to learn so much to get a digital TV, D-VHS, HD-DVR, HD-STB, etc. to replace their familiar TV, VHS, and Tivo that most find so easy to select, connect, and use; although there will always be people with a blinking twelve on their VCRs.  </p>

<p>Imagine when they find out that they have to install one terrestrial UHF/VHF antenna to connect to this modern HDTV for them to have a better chance to receive the free broadcast networks with a better image than their over-compressed HD "paid services".  For some people a small indoor or attic antenna could work well, but others might have to install the retro mast / outdoor antenna, at that point they freak out.</p>

<p>Most of the early adopters were motivated to watch DVD movies in 480p from their progressive DVD players (enhanced definition DTV level, but very good image indeed).  Some new DVD players can now upconvert to HD 720p or 1080i thru their digital outputs (DVI and HDMI).  I must note at this point that although upconversion has the objective of improving an image by adding more pixels, those pixels cannot increase the "original resolution" stored in the DVD.  So another confusion is that people think they are watching their regular DVDs in Hi def DVD quality when they are not.  </p>

<p>Many people are confused and concern about new themes, such as lamp cost, life span of the lamp, or panel life, etc.</p>

<p>Does it matter that the life span of a lamp is a few thousand hours if by replacing it for $200 the $5,000 TV is renewed to a brand new condition of light intensity?  Would it be better to replace the $5,000 TV instead?  Do they have any idea of how much is the comparative cost of doing something similar with a CRT RPTV, replacing the tubes for a visual renewal?  Some are worried that plasmas are quoted to last "just" 60,000 hours, three hours of prime time HDTV every day translates to 20,000 days, or over 50 years of life.  Do they expect their CRTs to last 50 years as well?  One can argue that some concerns might be valid, others unnecessarily alarming, but unfortunately, they contribute to further levels of complexity.  </p>

<p>Most people feel overwhelmed by the complexity and prefer to know only the basics; it is common for most to make their decisions based on a few questions at the local store, be pressured by the availability of a red tag sale item from a discontinued inventory, and using the typical routine of selecting the old TV comparing price and image, but not quite understanding what they are comparing.</p>

<p><br />
<strong>Third Level of Complexity - Content Protection Layer of Confusion</strong></p>

<p>Since 1998, it was discussed that copy protection might force changes to equipment when resolved.  At that time, the general recommendation was to be on the safe side and split the HD tuner from the monitor, one typical statement was: " Buy a TV monitor and later get a separate HD-STB when you are ready, eventually, when the issues are resolved, hopefully you would just need to replace the HD-STB tuner".</p>

<p>Even in today's era of integrated HDTVs, the recommendation of a separate STB still holds true for a large group of HDTV owners that want flexibility and do not want to pay for expensive tuners within their TV monitors if they do not need them.  The approach of monitor and STB made sense at that time because a) early generations of STBs had a number of technical problems that anyone should want out of the TV cabinet, and b) the issues of copy protection and connectivity that everyone though "would get ironed out probably in a year or so".</p>

<p>We are now six years after the DTV implementation, and the content protection issues are still evolving and causing implementation problems and delays, even to sister technologies like Hi-def DVD for example (more on it later).  They also had an effect on the connectivity and operability of legacy and new equipment. </p>

<p>All these issues should have been addressed while DTV was in development for over 20 years, not wait until DTV is implemented to start looking into it, and definitely unthinkable is to keep addressing them six years after that.  The parties involved (government and otherwise) were not capable enough to resolve the matter properly, while in comparison, the most complex part, the engineering of the HDTV technology itself, was implemented successfully. </p>

<p>The detail issues of content protection are combined with some connectivity issues on the following section.</p>

<p><br />
<strong>Fourth Level of Complexity - Connectivity</strong></p>

<p>An HD video connection should not be difficult to implement, is a matter of a good quality wire with standardized plugs at each end.  The problem of connectivity is not the wire, is what you can do with it, or more exactly, what content providers do not want you to do with the connection.  A number of self-replacing connecting standards have been introduced over the years, such as component analog YPbPr, and digital DVI, HDMI, and IEEE1394, and a number of copy protection methods were developed for the digital connections, such as HDCP, DTCP, and more recently, the Broadcast Flag with the blessing of the FCC.  </p>

<p>Even when having all of those connections on your equipment, and even when you complied with all the "evolving" rules of connectivity, their practical implementation might not work as you expect.  People will find that, for example, some digital connections are not working correctly, some might produce lower quality results in the HD video than the analog connections they eagerly "upgrade", some connectors are installed in equipment but are intentionally rendered non-operational until further notice, some equipment is found incompatible with other equipment that uses the same type of standard connection because they were implemented with different proprietary characteristics, component analog connections that worked since 1998 are suddenly impacted by lowering their resolution for HD images.   </p>

<p>The connectivity subject is still "evolving" because it is linked to the ever-evolving copy protection issues.  The DVI and HDMI connections for uncompressed digital HDTV signals were not necessarily pushed forward because a direct digital connection to a digital display would 'theoretically' provide a better image, or because HDMI would simplify the wiring in one cable when adding multi-channel digital audio to the video connection, or because IEEE1394 Firewire &trade; was unable to handle HD video quality.  Those are nice features if/when they work, but let us be honest.     </p>

<p>DVI or HDMI (together with HDCP copy protection) was "supported" because the MPAA wanted a sturdier solution to the risk of HD digital content being distributed and recorded with a quality similar to the original by people with the wrong motive and the available means to do it.  Certainly, they forgot about a minor factor, they would affect the very same consumers that buy tons of their content in DVDs everyday.  Additionally, under the fear that the Internet could be used as a channel for distribution of HD digital video, the FCC added another layer of complexity: the "Broadcast Flag", for which so far the FCC has approved 13 different digital output technologies and recordings methods.  And this is not over.</p>

<p>In November 2004, several newspapers reported that those involved in the downloading of movies are now targeted by the lawsuits filed in federal courts across the country by Disney, Warner Bros., MGM, Universal, Fox, Paramount and Sony.  MPAA's representative Rich Taylor declined to say exactly how many suits the studios filed or where they filed them, while Gigi Sohn, president of Public Knowledge, a Washington, D.C.-based civil liberties group, declared, "I wish they would think more about how they're going to sell movies than how they're going to sue people".  The group wants the entertainment industry to develop alternate business models to suit the needs of 21st-century technology.</p>

<p>Some organizations are still brainwashed in the middle of the content protection gridlock, slowing the process of migrating from analog to digital, making more complicated the design of products with multiple connectivity to be competitive.  Products end up over-suited with analog component YPbPr, analog RGB BNC or VGA 15 pin D-sub, DVI, HDMI, and if integrated with a DTV tuner it better have two-way IEEE1394, and of course with DTCP and HDCP copy protection; all that in addition to regular S-Video and composite connections, and of course now the "Broadcast Flag" circuitry.  </p>

<p>Audio video receivers already suffered a five-year video connectivity transformation when they were forced to support DVD and later HD bandwidths.  Receivers and pre/pros were built with multiple 3-wire YPbPr component analog connections to accommodate and switch the video devices connected to them, and in many applications, there were not enough inputs, so people needed to buy external switches.  After doing all that effort, those manufacturers were forced to rethink the strategy with the arrival of DVI, and later again with HDMI.  Just imagine how busy the back of a receiver or pre/pro would have to be in order to be competitive and support enough video jacks for several analog-component and digital DVI/HDMI cables coming from legacy and new equipment such as D-VHS, HD-STB, DVD players/recorders, HD-DVRs, HDTVs, etc.  </p>

<p>Not to mention the case of protecting DVD-Audio and Super-Audio digital multi-channel CDs by having the player converting the digital signal to analog and send it to the audio system using six separate analog wires, which requires at least six analog jacks at the back of the receiver, all in the name of content protection.</p>

<p>If the issues of digital connectivity and copy protection would have been addressed and resolved properly and timely, it could have facilitated a faster and simpler implementation, be less confusing, and be cost-efficient to consumers and manufacturers.  It could have benefited everyone by capitalizing from economies of scale that should have been fully matured by now.  Unfortunately, manufacturers and consumers were put in a bind.</p>

<p>The parties that deal with standards and decisions that affect consumer electronics, including our government and the content provider organizations, should be more sensitive and aware of the toll everyone pays for the many years of unplanned transformations and the delays caused by the endless lobbies and gridlock negotiations.  Some technologies are technically ready and are on hold for years because these organizations cannot make their minds and agree on something.</p>

<p><br />
<strong>An Analogy to Help Us Focus</strong></p>

<p>Imagine this situation from the world we live everyday: </p>

<p>You buy a new car (HDTV); you find at the gas station that the car only works properly when you use X gasoline (MPAA content over digital connections), but if the gas pump of the car detects (using HDCP, DTCP, Broadcast flag content protection) that you are using another brand of gasoline, you can not drive in the highway anymore, because the car speed is limited to a maximum of 15 miles an hour (HDTV down-rezed to a SDTV level of resolution using the analog component connections). </p>

<p>The new car (HDTV) was certainly designed to travel to any place at any speed, but the oil company (MPAA) designed the gas pump of the car so they can control it responding to their command, and "teach you how to drive your car".  Sometimes you might not even be able to move the car from the parking lot (blank screen on the HDTV), so you have to walk.    </p>

<p>The irony is that you bought your new car (like a Ferrari HDTV) without knowing that the oil company (MPAA) would have such control over your driving, and it costs several times the cost of your old car (analog NTSC TV), which no longer be manufactured.  </p>

<p>The gurus say that you can always continue driving your old car (NTSC TV), but there is a catch, it will only work if you buy a converter gas pump (HD-set top box to down-convert digital DTV to analog NTSC), which today is as expensive as the car itself (analog NTSC TV).  If you do not buy the converter gas pump, your car will stop functioning for what it was created.  </p>

<p>How is that for consumer satisfaction?   </p>

<p>Although it affects everyone, it does less harm to the high-end Porsche type of owner than to the large group of people that owns the 10 year old used car and struggle every day to survive without health insurance and without loosing the job.</p>

<p>This group of the TV viewing community would love to have an HDTV but they would be happy enough by just been able to turn on their TV after their long day of work and watch the content they viewed before; they are not counting the lines of resolution or pixels on a plasma panel they can not afford, they just want their TV to continue working until "they" are ready to replace it, not the government.  </p>

<p><br />
<strong>Is Hi-Def DVD Also Affected by the Complexity?</strong></p>

<p>The design and implementation of a solution to HD content protection had taken too long, causing the movie studios to be reluctant in releasing their content in HD quality, unless is properly protected.  This in turn caused delays to the introduction of Hi-Def DVD players/recorders/discs, which could have been released already (Japan released Blu-ray almost two years ago).  </p>

<p>Since the DVD introduction in 1996, people are buying a record number of DVDs even with the piracy market of DVDs; people obviously like to buy and collect their own movies.  If the Hi-Def DVD hardware and software could debut at a price as attractive as DVD, the HD format could experience the same overwhelming success.</p>

<p>In the other hand, some negative thinkers believe that introducing Hi-Def DVD could represent a menace to the DVD profitable market, so they have no incentive to release it now; in other words, they send us back to square one.   </p>

<p>Several companies join in partnerships to develop and promote competing formats such as HD DVD and Blu-ray in addition to VMD, WM9, and EVD, all implementing a variety of competing video compression technologies.  Computer companies are also taking sides on those partnerships.  All formats are looking for the commitment of content providers so their equipment can have interesting media to play, and as mentioned earlier the content providers are reluctant to rush in releasing their media due to copy protection issues.  </p>

<p>Is this complex enough?  This is worst than Disney vs. Sony and Beta vs. VHS combined.  It is disappointing that the consumer electronics industry, the content providers, and our government have not learned enough from past mistakes.  </p>

<p><br />
<strong>Some Light of Help at the End of the Tunnel</strong></p>

<p>Where do you stand on this panoramic view?  Are you ready to be part of it? </p>

<p>If you say: Yes, today.  Congratulations enjoy your purchase.  Inform yourself sufficiently to make an intelligent selection with the features you need for your "HD system" to work, not just the TV.     </p>

<p>If you say: Could be, but I prefer to buy later.  If it is because you rather wait until the issues are resolved, this could take long; remember the "the issues will be ironed out" statement is being commented since 1998.  If you can afford the purchase at current prices, you could start enjoying incredible HD images.  </p>

<p>If you say: No, too risky now.  Wait until you feel that the issues of your concern have been resolved for a safe purchase of all the components you need.  Perhaps even wait until Hi-Def DVD players/recorders are implemented in the US, hopefully in 2005/6, and see how the connectivity/copy protection is implemented on them.  However, the promise of "next year" has been expressed for 3 years already by manufacturers that demo Hi-Def DVD products at CES every year.  </p>

<p>If you say: No, I cannot afford HDTV now.  Equipment prices are lower each week.  You do not need to budget for an expensive set, the RPTV CRT solution is still the best bang for the buck, and if you have enough floor space for their larger cabinet, you can buy large screens for between $1000 and $2000.  </p>

<p>If you say: No, I want to continue with my current set.  Some proposals were discussed by government to help over-the-air TV viewers by subsidizing the cost of a digital STB tuner to view DTV down converted to the resolution of their analog TV (480i).  Another alternative is to get that STB yourself, if you are a cable or satellite subscriber, you might have it already.  If you use an over-the-air antenna, the STB cost about $350, but prices are expected to come down faster now.</p>

<p>Over 70% of TV viewers are cable subscribers; there is another proposal that would have many of those cable subscribers recognized and counted as DTV recipients (this is a simplified statement).  If both proposals are approved, the government could accelerate considerably the completion of the DTV transition, which requires that at least 85% of the population receive DTV to stop NTSC analog broadcasting, which would permit the auction (billions of dollars) of the airwave spectrum returned by the broadcasters upon releasing the analog channels.  </p>

<p><br />
<strong>Does Anyone Care if there is Confusion?</strong></p>

<p>What is better for the consumer?  Is it better for YOU to know?  </p>

<p>Maybe the consumer does not feel confused, or does not care, if so, why making the effort of working on a cure?  Who needs to have HDTV knowledge to invest in HDTV?  Perhaps your kids have already obtained some of that knowledge from their daily Instant Messenger while doing their homework in front of the computer; remember they grew up on a digital world.  </p>

<p>If it is better to know, there is a large task ahead to quickly educate the public to reduce the confusion.  Do the situation require only an education effort?  Perhaps a simplification of HDTV is needed before the education effort.  Perhaps we should continue with the current complexity and wait for more years of constant exposure to the technology.</p>

<p><br />
<strong>There is Always a Positive Side</strong></p>

<p>We have to admit that we could be worst without having this new digital future, and without the current availability of so many incredible DTV products, thanks to 20 years of wonderful engineering effort and 6 years of early adopters.  We could have had an "analog" HDTV, as some proposals originally recommended, and we could have been without the efforts of the ATSC and the Grand-Alliance.</p>

<p>We could have had Cable HD still dragging their feet, rather than the fast support experienced over the last year, which opens their 70% TV market to the world of DTV, and help reach most of the 85% threshold required to complete the migration to DTV.  We could have continued with a market focused in CRT RPTVs other than the variety of display technologies of today (CRT, plasma, LCD RP and TV, DLP, LCoS, XRD, D-ILA, etc).</p>

<p>Even though complexity and confusion make difficult the DTV transition, let us recognize and be thankful of the growth, maturity, variety of H/DTV technologies, sizes, and styles of products that makes us today much, much better than when we started in 1998.  In fact, it is a miracle that we experienced that growth considering all the other pending issues that are haunting the effort for so many years.</p>

<p><br />
<strong>Build from it and Move on</strong></p>

<p>The industry and the related organizations should a) capitalize from the errors, the delays, and failed negotiations, and should move forward fast and steady to simplify and help adopt HDTV sooner, and b) reconcile all the issues of connectivity and copy protection, while protecting backward compatibility and respecting the investment of millions of HDTV early adopters since 1998.  </p>

<p>The standards and rules should not continue to duplicate/self-replace/overlay themselves in a trial an error approach, they should be simplified and implemented following a well-planned path, and protecting the consumer rights of HD viewing, networking, and recording, as the rights of content providers regarding their media.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February  7, 2006  7:00 AM</b>
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
			<?=getComments(290)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 290)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/is-hdtv-complex-enough.php" type="text/javascript" charset="utf-8"></script>
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