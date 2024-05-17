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
		AND e.entry_id = 5182";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5182 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5182 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5182";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2014/01/hdtv-expert-ces-2014-in-the-rearview-mirror.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5182";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - CES 2014 In The Rear-View Mirror" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - CES 2014 In The Rear-View Mirror" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - CES 2014 In The Rear-View Mirror" />
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
	<title>HDTV Magazine - HDTV Expert - CES 2014 In The Rear-View Mirror</title>
	<meta name="keywords" content="oled tvs, bit rate, inch oled, red green, blue emitters, –, inch, ’s, lcd, tvs, oled, show, products, wireless, rate, color, curved, bit, hdmi, televisions, ces, most, booth, screen, blue" />
	<meta name="description" content="It&amp;acirc;��s been almost two weeks since CES. The booths and signs have long been taken down; the long lines and long hours are a distant memory. Time for a thoughtful retrospective on the show&amp;acirc;�&amp;brvbar;" />
	<meta name="title" content="HDTV Expert - CES 2014 In The Rear-View Mirror" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - CES 2014 In The Rear-View Mirror" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2014/01/hdtv-expert-ces-2014-in-the-rearview-mirror.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It&amp;acirc;��s been almost two weeks since CES. The booths and signs have long been taken down; the long lines and long hours are a distant memory. Time for a thoughtful retrospective on the show&amp;acirc;�&amp;brvbar;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5182', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2014/01/hdtv-expert-ces-2014-in-the-rearview-mirror.php">HDTV Expert - CES 2014 In The Rear-View Mirror</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>January 21, 2014</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=457&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=514&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<p>Once again, CES has come and gone. It sneaks up on us right after a relaxing Christmas / New Year holiday. We’re jolted out of a quiet reverie and it’s back to the rush to board at the airport gate, walking the serpentine lines for taxis at McCarran Airport, and “late to bed, early to rise” as we scramble to make our booth and off-site appointments in Las Vegas.</p>
<p>We don’t make them all on time. Some we miss completely. But there’s a serendipity angle to it all: We might find, in our haste to get from one meeting to another, some amazing new gadget we didn’t know about as we take shortcuts through booths in the North, South, and Central Halls.</p>
<p>Or a colleague sends us a text or leaves a voicemail, emphatically stating “you have to see this!” Or a chance meeting leads to an ad hoc meeting, often off-site or over a hasty lunch in the convention center.</p>
<p>My point is this: You “find” as many cool things at the show as you “lose.” For every must-see product that you don’t see, there’s another one you trip over. Granted; many “must-see” products are yawners – you&#8217;ve figured it out 30 seconds into your carefully-staged meeting with PR people and company executives, and you’re getting fidgety.</p>
<p><img class="aligncenter size-full wp-image-3529" alt="LS Samsung Booth MCU 600p" src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/LS-Samsung-Booth-MCU-600p.jpg" width="600" height="384" /></p>
<p>My best CES discoveries involve products or demos where I can observe them anonymously, without PR folks hovering at my side or staring at my badge before they pounce like hungry mountain lions.</p>
<p>Unlike most of my colleagues in the consumer electronics press, I don’t need to break stories the instant I hear about them. There are already too many people doing that. What’s missing is the filter of analysis – some time spent to digest the significance of a press release, product demo, or concept demo.</p>
<p>And that’s what I enjoy the most: Waiting a few days – or even a week – after the show to think about what I saw and ultimately explain the significance of it all. What follows is my analysis of the 2014 International CES (as we are instructed to call it) and which products and demos I thought had real significance, as opposed to those which served no apparent purpose beyond generating daily headlines and “buzz.”</p>
<p><b>Curved TV screens:</b> OK, I had to start with this one, since every TV manufacturer at the show (save Panasonic and Toshiba) exhibited one or more curved-screen OLED and LCD televisions. Is there something to the curved-screen concept? On first blush, you’d think so, given all of the PR hype that accompanied these products.</p>
<p>The truth is; really big TV screens do benefit a little from a curved surface, particularly if they are UHDTV models and you are sitting close to them. The effect is not unlike Cinerama movie screens from the 1950s and 1960s. (That’s how I saw <i>Dr. Zhivago</i> and <i>2001: A Space Odyssey</i> back in the day.)</p>
<div id="attachment_3530" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3530" alt="Toshiba described their version of the 21:9 widescreen LCD TV as having &quot;5K&quot; resolution - and mathematically, it does (I guess!)." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Toshiba-5K-Widescreen-LCD-TV-600p.jpg" width="600" height="393" /><p class="wp-caption-text"><span style="font-size: 12px;">Toshiba described their version of the 21:9 widescreen LCD TV as having &#8220;5K&#8221; resolution &#8211; and mathematically, it does (I guess!).</span></p></div>
<div id="attachment_3550" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3550" alt="This wall of 56-inch curved OLEDs greeted visitors to the Panasonic booth." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Panasonic-Curved-OLED-600p.jpg" width="600" height="299" /><p class="wp-caption-text"><span style="font-size: 12px;">This wall of 56-inch curved OLEDs greeted visitors to the Panasonic booth.</span></p></div>
<p>Bear in mind I’m talking about BIG screens here – in the range of 80 inches and up. The super-widescreen (21:9 aspect ratio) LCD TVs shown by Samsung, LG, and Toshiba used the curve to great effect. But conventional 16:9 TVs didn’t seem to benefit as much, especially in side-by-side demos.</p>
<p>The facts show that worldwide TV shipments and sales have declined for two straight years, except in China where they grew by double digits each year. TV prices are also collapsing – you can buy a first-tier 55-inch “smart” 1080p LCD TV now for $600, and 60-inch “smart” sets are well under $800 – so manufacturers will try anything to stimulate sales.</p>
<p>Is that the reason why we’re seeing so many <b>UHDTV (4K) TVs</b> all of a sudden? Partially. Unfortunately, there’s just no money in manufacturing and selling 2K TVs anymore (ask the Japanese manufacturers how that’s been working for them), and the incremental cost to crank out 4K LCD panels isn’t that much.</p>
<p>Chinese panel and TV manufacturers have already figured this out and are shifting production to 4K in large panels while simultaneously dropping prices. You can already buy a 50-inch 4K LCD TV from TCL for $999. Vizio, who is a contract buyer much like Apple, announced at the show that they’d have a 55-inch 4K LCD TV for $1299 and a 65-inch model for well under $2,000.</p>
<div id="attachment_3532" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3532" alt="Hisense is building a factory in the U.S. to assemble TVs. And you wondered if they were serious about the North American TV business?" src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Hisense-65-inch-Curved-4K-TV-2-600p.jpg" width="600" height="450" /><p class="wp-caption-text"><span style="font-size: 12px;">Hisense is building a factory in the U.S. to assemble TVs. And you wondered if they were serious about the North American TV business?</span></p></div>
<div id="attachment_3533" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3533" alt="Vizio's 65-inch high dynamic range (HDR) 4K TV was very impressive." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Vizio-HDR-65-inch-LCD-TV-600p.jpg" width="600" height="396" /><p class="wp-caption-text"><span style="font-size: 12px;">Vizio&#8217;s 65-inch high dynamic range (HDR) 4K TV was very impressive.</span></p></div>
<p>Consider that the going price for a 55-inch 4K “smart” LCD TV from Samsung, LG, and Sony is sitting at $2,999 as of this writing and you can see where the industry is heading. My prediction is that all LCD TV screens 60 inches or larger will use 4K panels exclusively within three years. (4K scaling engines work much better than you might think!)</p>
<p>And don’t make the popular mistake of conflating 4K with 3D as &#8216;failed&#8217; technologies. The latter was basically doomed from the start: Who wants to wear glasses to watch television? Not many people I know. Unfortunately, glasses-free (autostereo) TV is still not ready for prime time, so 3D (for now) is basically a freebie add-on to certain models of televisions.</p>
<p>4K, on the other hand, has legs. And those legs will get stronger and faster as the new High Efficiency Video Codec (HEVC) chips start showing up in televisions and video encoders. HEVC, or H.265 encoding, can cut the required bit rate for 2K content delivery in half. That means it can also deliver 4K at the old 2K rates, somewhere in the ballpark of 10 – 20 Mb/s.</p>
<div id="attachment_3535" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3535" alt="Toshiba (like many others) is moving quickly to adopt and integrate HEVC H.265  encoding and decoding into their products." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Toshiba-4K-HEVC-Demo-600p.jpg" width="600" height="450" /><p class="wp-caption-text">Toshiba (like many others) is moving quickly to adopt and integrate HEVC H.265 encoding and decoding into their products.</p></div>
<div id="attachment_3536" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3536" alt="Nanotech's Nuvola 4K media player costs only $300 and delivers the goods." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Nanotech-4K-Player-600p.jpg" width="600" height="607" /><p class="wp-caption-text">Nanotech&#8217;s Nuvola 4K media player costs only $300 and delivers the goods.</p></div>
<p>While consumer demand for 4K is slowly ramping up, there is plenty of interest in UHDTV from the commercial AV sector. And Panasonic focused in on that sector almost exclusively in their CES booth. I’m not sure why – there are plenty of inferences here; most significantly, it would appear that Panasonic is exiting the money-losing television business entirely. (Ditto nearby Toshiba, which had similar 4K “applications” showcased and which also did not exhibit a line of 2014 televisions.)</p>
<p>Long story short; you may be buying 4K televisions in the near future whether you want ‘em or not. It’s a manufacturing and plant utilization issue, and if commercial demand for 4K picks up as expected, that will drive the changeover even faster.</p>
<p>As for sources of 4K content; Samsung announced a partnership with Paramount and Fox to get it into the home via the M-Go platform. Comcast had an Xfinity demo for connected set-top-boxes to stream 4K, and of course Netflix plans to roll out 4K delivery this year direct to subscribers.</p>
<p>I’m not sure how they’ll pull that off. My broadband speeds vary widely, depending on time of day: I’m writing this at noontime and according to CNET’s Broadband Speed Test, my downstream bit rate is about 22 megabits per second (Mb/s). Yet, I’ve seen that drop to as low as 2 – 3 Mb/s during late evening hours, when many neighbors are no doubt streaming Netflix movies.</p>
<p>Even so, HEVC will definitely help that problem. I spoke to a couple of Comcast folks on my flights out to and back from CES, and they’re all focused on the bandwidth and bit rate challenges of 2K streaming, let alone 4K. More 4K streaming interface products are needed, such as Nanotech’s $300 Nuvola NP-H1, which is about the size of an Apple TV box and ridiculously simple to connect and operate.</p>
<div id="attachment_3538" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3538" alt="LG's got a 77-inch curved OLED TV that can also flex. (Why, I don't know...)" src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/LG-77-inch-Flex-OLED-TV-600p.jpg" width="600" height="529" /><p class="wp-caption-text"><span style="font-size: 12px;">LG&#8217;s got a 77-inch curved OLED TV that can also flex. (Why, I don&#8217;t know&#8230;)</span></p></div>
<div id="attachment_3554" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3554" alt="nVidia built an impressive 3D heads-up display into the dash of a BMW i3 electric car." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/nVidia-Heads-Up-Display-BMW-i3-600p.jpg" width="600" height="450" /><p class="wp-caption-text"><span style="font-size: 12px;">nVidia built an impressive 3D heads-up display into the dash of a BMW i3 electric car.</span></p></div>
<p>Oh, yeah. I should have mentioned <b>organic light-emitting diode (OLED)</b> displays earlier. There were lots of OLED displays at CES, ranging from the cool, curved 6-inch OLED screen used in the new LG G-Flex curved smartphone to prototype 30-inch OLED TVs and workstation monitors in the TCL booth and on to the 55-inch, 65-iunch, and even 77-inch OLED TVs seen around the floor. (LG’s 77-inch offering is current the world’s largest OLED TV, and of course, it’s curved.)</p>
<p>OLEDs are tricky beasts to manufacture. Yields are usually on the low side (less than 25% per manufacturing run) and that number goes down as screen sizes increase, which explains the high prices for these TVs.</p>
<p>And there’s the unresolved issue of differential color aging, most notably in dark blue emitters. With current OLED science, you can expect dark blue emitters to reach half-brightness at about 5,000 hours of operation with a maximum brightness of 200 nits. Samsung addresses this quandary by employing two blue emitters for every red and green pixel on their OLED TVs, while LG has the more difficult task of managing blue aging in their white OLED emitters.</p>
<p>Several studies over the past three years consistently show people hanging on to their flat screen TVs for 5 to 7 years, which is likely to be a lot longer than 5,000 hours of operation. Will differential color aging rear its ugly head as early adopters shell out close to $10K for a 55-inch OLED TV? Bet on it.</p>
<p>Turns out, there’s another way to get wide color gamuts and saturated colors: <b>Quantum dots. </b>QDs, as we call them, are inorganic compounds that exhibit piezoelectric behavior when bombarded with photons. They emit stable, narrow-bandwidth colors with no drift, and can do so for long periods of time – long enough to work in a consumer television.</p>
<div id="attachment_3540" style="width: 549px" class="wp-caption aligncenter"><img class="size-full wp-image-3540" alt="3M featured its quantum dot film (QDF) in several demos. An LCD TV equipped with it is at the top of the picture." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Nanosys-3M-QDF-Demo-1-600p.jpg" width="539" height="600" /><p class="wp-caption-text"><span style="font-size: 12px;">3M featured its quantum dot film (QDF) in several demos. An LCD TV equipped with it is at the top of the picture.</span></p></div>
<div id="attachment_3542" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3542" alt="This prototype WiHD dongle turns any smartphone or tablet equipped with MHL or Micro HDMI interfaces into a 60 GHz wireless playback system." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Compact-WiHD-Demo-1-600p.jpg" width="600" height="413" /><p class="wp-caption-text"><span style="font-size: 12px;">This prototype WiHD dongle turns any smartphone or tablet equipped with MHL or Micro HDMI interfaces into a 60 GHz wireless playback system.</span></p></div>
<p>QDs are manufactured by numerous companies, most notably Nanosys and QD Vision in the United States.  The former company has partnered with 3M to manufacture an optical film that goes on the backside of LCD panels, while the former has discrete Q-LEDs that can work in direct illuminating arrays or as part of an edge illuminating backlit with waveguide plates.</p>
<p>Sony is already selling 55-inch and 65-inch 4K LCD TVs using the Q-LED technology, and I can tell you that the difference in color is remarkable. Red – perhaps the most difficult color to reproduce accurately in any flat-screen TV – really looks like red when viewed with a QD backlight. And it’s possible to show many subtle shades of red with this technology.</p>
<p>All you need is a QD film or emitter with arrays of red and green dots, plus a backlight made up of blue LEDs. The blue passes through, while the blue photons “tickle” the red and green dots, causing them to emit their respective colors. It’s also possible to build a direct-illumination display out of quantum dots that would rival OLED TVs.</p>
<p>How about 4K display interfaces? By now, you’ve probably heard that HDMI has “upgraded” to version 2.0 and can support a maximum data rate of 18 gigabits per second (GB/s).  Practically speaking; because of the way display data is transmitted, only 16 Gb/s of that is really available for a display connection. Still, that’s fast enough to show 4K content (3840&#215;2160, or Quad HD) with a 60 Hz frame rate, using 8-bit color.</p>
<div id="attachment_3543" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3543" alt="DisplayPort can now carry USB 3.0 on its physical layer. Here's an Accell DockPort breakout box with Mini DisplayPort and USB connections." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Accell-DockPort-Breakout-Box-600p.jpg" width="600" height="450" /><p class="wp-caption-text"><span style="font-size: 12px;">DisplayPort can now carry USB 3.0 on its physical layer. Here&#8217;s an Accell DockPort breakout box with Mini DisplayPort and USB connections.</span></p></div>
<div id="attachment_3544" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3544" alt="Epson's Moverio glasses aren't as sexy as Google Glass - but then, they can do more things." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Epson-Moverio-Glasses-600p.jpg" width="600" height="376" /><p class="wp-caption-text"><span style="font-size: 12px;">Epson&#8217;s Moverio glasses aren&#8217;t as sexy as Google Glass &#8211; but then, they can do more things.</span></p></div>
<p>Over at the DisplayPort booth, I heard stories of version 1.3 looming later this spring. DisplayPort 1.2, unlike HDMI, uses a packet structure to stream display, audio, and other data across four scalable lanes, and has a maximum rate of 21.6 Gb/s – much faster than HDMI. Applying the “20 percent” rule, that leaves about 17.3 Gb/s to actually carry 4K signals. And the extra bits over HDMI means that DP can transport 3840&#215;2160 video with a frame rate of 60 Hz, but with 10-bit color.</p>
<p>Don’t underestimate the value of higher data rates: 4K could turn out to be a revolutionary shift in the way we watch TV, adding much wide color gamuts, higher frame rates, and high dynamic range (HDR) to the equation. HDMI clearly isn’t fast enough to play on that field; DP barely is. Both interfaces still have a long way to go.</p>
<p>So – why not make a wireless 4K connection? There were plenty of demos of <b>wireless connectivity</b> at the show, and I’m not just talking about Wi-Fi. Perhaps the most impressive was in the Silicon Image meeting room, all the way at the back of the lower South Hall, near the Arizona border.</p>
<p>SI, which bought out wireless manufacturer SiBEAM a few years ago, demonstrated super-compact 60 GHz wireless HDMI and MHL links using their UltraGig silicon. A variety of prototype cradles for phones and tablets were available for the demo: Simply plug in your handheld device and start streaming 1080p/60 video to a nearby 55-inch LCD TV screen.</p>
<p>Granted, the 60 GHz tech is a bit exotic. But it works quite well in small rooms and can take advantage of signal multipath “bounces” by using multiple, steerable antenna arrays built-in to each chip. And it can handle 4K, too – as long as the bit rate doesn’t exceed the HDMI 2.0 specification, the resolution, color bit depth, and frame rate are irrelevant.</p>
<p>This sort of product is a “holy grail” item for meeting rooms and education. Indeed; I field numerous questions every year during my InfoComm wireless AV classes along these lines: “Where can I buy a wireless tablet dongle?” Patience, my friends. Patience…</p>
<div id="attachment_3545" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3545" alt="LG was one of many companies showing &quot;digital health&quot; products, like these LifeBand monitors." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/LG-Lifeband-Stand-600p.jpg" width="600" height="395" /><p class="wp-caption-text"><span style="font-size: 12px;">LG was one of many companies showing &#8220;digital health&#8221; products, like these LifeBand monitors.</span></p></div>
<div id="attachment_3546" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3546" alt="You can now buy the concave-surface LG G-Flex smartphone. But I don't think you'll see any of these in the near future..." src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/LGD-Flexible-OLED-Display-1-600p.jpg" width="600" height="418" /><p class="wp-caption-text"><span style="font-size: 12px;">You can now buy the concave-surface LG G-Flex smartphone. But you won&#8217;t see any of these in the near future&#8230;</span></p></div>
<p>The decline in TV shipments and sales seems to be offset by a boom in <b>connected personal lifestyle and health gadgets</b>, most notably wristbands that monitor your pulse and workouts. There were plenty of these trinkets at the show and an entire booth in the lower South Hall devoted to “digital health.”</p>
<p>Of course, the big name brands had these products – LG’s LifeBand was a good example. But so did the Chinese and Taiwanese manufacturers. “Digital health” was like tablets a few years back – so many products were introduced at the show that they went from “wow!” to “ho-hum” in one day.</p>
<p>This boom in personal connectivity extends to appliances, beds (Sleep Number had a model that can hear snoring and elevate the head of the bed automatically), cars (BMW’s i3 connected electric car was ubiquitous), and even your home. Combine it with short-range Bluetooth or ZigBee wireless connectivity and you can control and monitor just about anything on your smartphone and tablet.</p>
<p>Granted; there isn’t the money in these small products like there used to be in televisions. But consumers do want to connect, monitor, and control everything in their lives, and their refrigerators, cars, beds, televisions, percolators, and toasters will be able to comply. (And in 4K resolution, too!)</p>
<div id="attachment_3548" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3548" alt="PointGrab can mute a TV simply by raising a finger to your lips!" src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/PointGrab-Finger-Control-Demo-600p.jpg" width="600" height="421" /><p class="wp-caption-text"><span style="font-size: 12px;">PointGrab lets you mute a TV simply by raising a finger to your lips!</span></p></div>
<div id="attachment_3549" style="width: 610px" class="wp-caption aligncenter"><img class="size-full wp-image-3549" alt="Panasonic downplayed TVs at CES, but had a functioning beauty salon in their booth (by appointment only..)" src="http://www.hdtvexpert.com/wp-content/uploads/2014/01/Panasonic-Beauty-Appliances-600p.jpg" width="600" height="450" /><p class="wp-caption-text"><span style="font-size: 12px;">Panasonic downplayed TVs at CES, but had a functioning beauty salon in their booth (by appointment only..)</span></p></div>
<p>Obviously, I didn’t visit the subjects of gesture and voice control. There were several good demos at the show of each, and two of the leading companies I showcased last year &#8211; Omek and Prime Sense &#8211; have been subsequently acquired by Intel and Apple. Hillcrest Labs, PointGrab, and other had compelling demos of gesture control in Las Vegas – a subject for a later time.</p>
<p>Summing up, let’s first revisit my mantra: Hardware is cheap, and anyone can make it. Televisions and optical disc media storage are clearly on the decline, while streaming, 4K, health monitoring, and wireless are hot. The television manufacturing business is slowly and inexorably moving to China as prices continue their free-fall.</p>
<p>The consumer is shifting his and her focus to all the devices in the home they use every days; not just television. Connectivity is everything, and the television is evolving from an entertainment device into a control center or “hub” of connectivity. The more those connections are made with wireless, the better – and that includes high-definition video from tablets and phones.</p>
<p>It’s going to be an interesting year…</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>January 21, 2014 12:21 PM</b>
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
			<?=getComments(5182)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5182)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2014/01/hdtv-expert-ces-2014-in-the-rearview-mirror.php" type="text/javascript" charset="utf-8"></script>
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