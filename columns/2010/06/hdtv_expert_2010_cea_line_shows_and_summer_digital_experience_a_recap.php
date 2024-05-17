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
		AND e.entry_id = 3807";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3807 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3807 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3807";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-2010-cea-line-shows-and-summer-digital-experience-a-recap.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3807";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap" />
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
	<title>HDTV Magazine - HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap</title>
	<meta name="keywords" content="lcd tvs, active shutter, shutter glasses, inch inch, surround sound, inch, tvs, lcd, glasses, line, new, set, work, show, shutter, mits, flash, active, rear, dlp, system, product, mitsubishi, sound, year" />
	<meta name="description" content="The Line Shows weren’t quite as interesting as Digital Experience, but there were a few gems to be found here and there." />
	<meta name="title" content="HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-2010-cea-line-shows-and-summer-digital-experience-a-recap.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The Line Shows weren’t quite as interesting as Digital Experience, but there were a few gems to be found here and there." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3807', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-2010-cea-line-shows-and-summer-digital-experience-a-recap.php">HDTV Expert - 2010 CEA Line Shows and Summer Digital Experience: A recap</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>June 24, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=333&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>, <b><a href="/category.php?id=309&category=Politics & Policy">Politics & Policy</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>The annual CEA Line Show event in New York City seems to be gaining in popularity, although the pickings were slim again this year.  Still, this event, coupled with Mitsubishi’s press showcase on 18<sup>th</sup> Street and Pepcomm’s evening Digital Experience tabletop show, held later that evening, gave me a good excuse to make a trip to the Big Apple.</p>
<p>My first stop was at DriveIn24 Studios, where Mits had set up a very nice dealer and press display of their latest LCD and DLP HDTV sets. As most readers know, Mitsubishi is the lone hold-out in rear-projection TV, a product category that’s nearly extinct thanks to super-low prices on big plasma and LCD TVs, along with the general public’s obsession with super-thin TV profiles and their disinterest in replacing lamps.</p>
<p>Still, Mits has claimed to sell in excess of half a million RPTVs every year (a number I have some doubts about) and is hanging in there as the remaining ‘niche’ player in the category. And they had a few new products to brag about, including two upgraded 82-inch DLP Rear-pro sets (WD-82738 and WD-82838), a larger, less-expensive Laser DLP product (the 75-inch L75-A91<strong>, </strong>MSRP<strong> </strong>$5,999), and a 3D Starter Pack (Model 3DC-1000, $399).</p>
<div id="attachment_608" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Mitsuibishi-WD-83838-MR.jpg"><img class="size-full wp-image-608" title="Mitsuibishi WD-83838 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Mitsuibishi-WD-83838-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Now, THAT is some serious 3D TV viewing!</p></div>
<p>This starter kit is designed to retrofit older Mitsubishi DLP TVs for active shutter 3D and contains two pairs of active shutter 3D eyewear, a 3D emitter, a 3D adapter with remote control, an HDMI cable, and also features a Disney 3D showcase Blu-ray disc with 3D trailers of <em>A Christmas Carol</em>, <em>Alice in Wonderland,</em> and <em>Toy Story 3</em>. The 3D adapter is also available separately for $99.</p>
<p>Mitsubishi also showed off a new line of six Unisen LCD TVs (40, 46, and 55-inch) with their unique 18-speaker soundbar. Paired with a separate woofer, this audio system does provide some amazingly good surround-sound effects, and it’s also found in their ten rear pro models. The soundbar can be used by itself, or set up as the center speaker in a surround sound system (I liked the audio quality better in that mode).</p>
<p>The funny thing about the Mits line-up is that they’ve managed to survive some very turbulent times in the TV industry and maintain their distinct brand identity as a high-end product. (Too bad their dealers can’t pull that trick off; 62-year-old specialty retailer Ken Crane’s in southern California just announced they’re closing their doors for good this year.)</p>
<p>And Mits does make a high-quality product – I was quite taken with the 3D demos on that 82-inch behemoth in a darkened room, the best way to watch 3D content. They were a lot more impressive than the 3D stuff Sony showed at the Line Show <a href="../?p=592">(see my related post here)</a>. As for 3D on LCD TVs, Mits doesn’t think the technology is ready for prime time yet, due to crosstalk problems between LCD TVs and active shutter glasses.</p>
<div id="attachment_609" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Max-and-Frank-with-Laser-Vue-MR.jpg"><img class="size-full wp-image-609" title="Max and Frank with Laser Vue MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Max-and-Frank-with-Laser-Vue-MR.jpg" alt="" width="600" height="459" /></a><p class="wp-caption-text">Mitsubishi execs Max Wasinger (left) and Frank DeMartin (right) are all smiles as they show off their new 75-inch Laser Vue DLP RPTV...and continue to thrive in the rear-projection TV business.</p></div>
<p>Back at the Line Shows, I found a few items of interest. Envision Peripherals is a specialty brander of 19-inch, 26-inch, 32-inch, and 42-inch LCD TVs under the AOC name, and they had a demonstration of a 32-inch set that uses a standard flash memory stick as a DVR. This feature will work with any ATSC or QAM signal and writes the program to flash memory, allowing you to fast-forward, pause, rewind or skip through programs.</p>
<p>Currently, the minimum storage supported is 512 MB, while the maximum is 2 GB. (You’ll need about 9 GB per hour to record or shift HD programs and 3 GB per hour of SD programs.) So it’s an intriguing feature, but one which needs more work if it has any value. The literature mentioned that AOC is working on making 8GB flash drives compatible with their TVs.</p>
<p>Across the way, Vizio set up their booth to show a full range of consumer electronics products, including a new broadband WiFi router (XWH100, dual-band operation) and a wireless 5.1 surround sound system with sound bar for under-TV mounting (VHT510) and separate wireless subwoofer and rear speakers. Vizio also showed a passive 3D LCD TV prototype using alternate-line circular polarization, but it was plagued with purple-tinted ghost images from crosstalk. (Back to the drawing board!)</p>
<div id="attachment_610" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Vizio-65-inch-Passive-LCD-TV-MR.jpg"><img class="size-full wp-image-610" title="Vizio 65-inch Passive LCD TV MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Vizio-65-inch-Passive-LCD-TV-MR.jpg" alt="" width="600" height="579" /></a><p class="wp-caption-text">Vizio's 65-inch passive 3D TV made for an interesting concept demo, but had serious crosstalk problems.</p></div>
<p>As mentioned in my other post, Monster Cable is in the 3D glasses business. They’ve signed a deal to OEM active shutter glasses from Florida-based Bit Cauldron, and these glasses don’t use infrared (IR) signaling. Instead, they rely on the 802.15.4 RF (wireless) ZigBee protocol, which (as a sage company representative pointed out to me last January at CES) <em>“…will still work even if you are standing in the next room!”</em> (No comment…)</p>
<p>The advantage of an RF-based connection is immunity to interference from intense lamps, fluorescent lights, and momentary obstructions of the TV’s IR emitter. Best of all, these are universal (learning) active shutter glasses that will work with ANY 3D TV. (And they work a heck of a lot better than Sony’s own glasses with Bravia LCD TVs!) They’re not cheap at $170 a pair, though.</p>
<div id="attachment_611" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Monster-3D-Glasses-MR.jpg"><img class="size-full wp-image-611" title="Monster 3D Glasses MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Monster-3D-Glasses-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Need a pair of 'works anywhere' 3D glasses? Monster's got your number.</p></div>
<p>I wrapped the day up a visit to the Digital Experience gadget fest, where Logitech was showing a prototype of the Google TV set-top box. They couldn’t tell me anything about it or what it would cost (those details will be forthcoming in the fall), but I can tell you it has HDMI input and output connections, dual USB sockets, an Ethernet port, a SPDIF connection, and an external DC power plug. So far as I can tell, it has no provision for time-shifting or recording, although (as mentioned earlier) that could easily be done with flash memory sticks, which can be bought for about $20 apiece with 8 MB of capacity.</p>
<p>Across the way, TiVo had their Premiere DVRs out for inspection (they run on a Flash operating system) but no new updates to announce. They were intrigued, though, by Google’s attempt to get into the TV Guide business and what that means long-term for manufacturers of TVs and set-tops.</p>
<div id="attachment_612" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Logitech-Google-TV-Box-Top-MR.jpg"><img class="size-full wp-image-612" title="Logitech Google TV Box - Top MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Logitech-Google-TV-Box-Top-MR.jpg" alt="" width="600" height="394" /></a><p class="wp-caption-text">Here's the Logitech prototype Google TV box. Not much to look from the top, is there?</p></div>
<div id="attachment_613" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Logitech-I-O-MR.jpg"><img class="size-full wp-image-613" title="Logitech I-O MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/06/Logitech-I-O-MR.jpg" alt="" width="600" height="258" /></a><p class="wp-caption-text">And here's a look at its connectors. Hmmm...HDMI inputs and outputs, eh?</p></div>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>June 24, 2010  9:31 AM</b>
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
			<?=getComments(3807)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3807)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/06/hdtv-expert-2010-cea-line-shows-and-summer-digital-experience-a-recap.php" type="text/javascript" charset="utf-8"></script>
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