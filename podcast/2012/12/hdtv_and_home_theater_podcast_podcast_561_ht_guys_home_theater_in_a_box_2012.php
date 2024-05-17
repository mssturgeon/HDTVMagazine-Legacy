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
		AND e.entry_id = 4962";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4962 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4962 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4962";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/12/hdtv-and-home-theater-podcast-podcast-561-ht-guys-home-theater-in-a-box-2012.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4962";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012</title>
	<meta name="keywords" content="blu ray, home theater, ray player, speaker system, receiver speakers, home, blu, ray, system, theater, get, receiver, player, budget, cost, speakers, remote, features, most, network, inch, box, quality, right, stream" />
	<meta name="description" content="There are so many Home Theater in a Box systems out there many of which cost less than $400. But seriously, what can you get for $400. A good center channel typically costs more than most HTIB systems on the market today. Each year we try to assemble a Home Theater in many boxes that we would be proud to show off in our homes. Our system will have at a minimum a HDTV, Blu-ray Player, Receiver, and 5.1 speakers, all for less than $2500." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/12/hdtv-and-home-theater-podcast-podcast-561-ht-guys-home-theater-in-a-box-2012.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="There are so many Home Theater in a Box systems out there many of which cost less than $400. But seriously, what can you get for $400. A good center channel typically costs more than most HTIB systems on the market today. Each year we try to assemble a Home Theater in many boxes that we would be proud to show off in our homes. Our system will have at a minimum a HDTV, Blu-ray Player, Receiver, and 5.1 speakers, all for less than $2500." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4962', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/12/hdtv-and-home-theater-podcast-podcast-561-ht-guys-home-theater-in-a-box-2012.php">HDTV and Home Theater Podcast - Podcast #561: HT Guys Home Theater in a Box 2012</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>December 13, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=478&category=HTPCs & Laptops">HTPCs & Laptops</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>HT Guys Home Theater in a Box</h3>
<p>There are so many Home Theater in a Box systems out there many of which cost less than $400. But seriously, what can you get for $400. A good center channel typically costs more than most HTIB systems on the market today. Each year we try to assemble a Home Theater in many boxes that we would be proud to show off in our homes. Our system will have at a minimum a HDTV, Blu-ray Player, Receiver, and 5.1 speakers, all for less than $2500.</p>
<p>For this feature we choose components that we either have direct experience with or have experience with a similar model made by the same manufacturer. When we can’t find something in the target price range for a particular component that we have experience with we use the reputation of the manufacturer and/or listener feedback.</p>
<p>&nbsp;</p>
<h4>Braden:</h4>
<p><a href="http://www.amazon.com/gp/product/B009H8JOZS/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B009H8JOZS&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>VIZIO E601i-A3 60-Inch 1080p 120Hz Razor LED Smart HDTV ($999)</strong></a><br />
This one came straight from our <a href="http://www.htguys.com/podcasts/2012/11/30/podcast-559-hdtv-buying-guide-2012.html">HDTV Buying Guide</a> from <a href="http://www.htguys.com/podcasts/2012/11/30/podcast-559-hdtv-buying-guide-2012.html">Episode 559</a>. The name of the game when you’re shopping on a budget, as most of us typically are, is bang for the buck. You can’t beat this Vizio when you factor in size, features, quality and cost. The E-Series 60” has 1080p Full HD resolution with 120Hz refresh rate with smooth motion. It features an ultra thin profile and VIZIO Internet Apps with built-in Wi-Fi, but you won’t need the apps, because we’re going to get them in the Blu-ray player as well. Bottom line here, a high quality 60” TV.  ‘Nuff said.</p>
<p><a href="http://www.amazon.com/gp/product/B004U403XQ/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B004U403XQ&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Denon AVR-1912 7.1 Channel Network Streaming A/V Home Theater Receiver $457</strong></a><br />
You can save a little bit of money by going with last year’s model on this very capable Denon receiver. You don’t really give anything up by doing so. It has all of the same essential functionality with the quality you know you’ll get from a Denon product.</p>
<ul>
<li>7.1ch Network Streaming A/V Receiver</li>
<li>HDMI 1.4a 6In/1Out 3D Ready</li>
<li>AirPlay Ready</li>
<li>Discrete Power Amps rated at 90 watts each</li>
</ul>
<p><a href="http://www.amazon.com/gp/product/B006U1YVZ8/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B006U1YVZ8&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Sony BDP-S390 Blu-ray Disc Player with Wi-Fi $88</strong></a><br />
We didn’t go for a 3D TV, so there’s no need to get a 3D Blu-ray player. This Sony model has all the features you need (Netflix, Hulu, Vudu, etc.) and a great price.</p>
<ul>
<li>Built-in Wi-Fi</li>
<li>Full HD 1080p Blu-ray Disc playback</li>
<li>Media Remote app for iPhone/iPad &amp; Android phones</li>
<li>With the Sony Entertainment Network, you can instantly stream a wide variety of online movies, videos, TV shows, and music from Netflix, YouTube, Pandora, Hulu Plus and more.</li>
<li>Stream Unlimited Prime Instant Video with This Device: <a href="http://www.amazon.com/gp/prime?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;ref_=sr_tc_sc_2_0&amp;qid=1326638658&amp;sr=8-2-tc%20via">Amazon Prime</a> members can stream over 30,000 movies and TV episodes at no additional cost</li>
</ul>
<p><a href="http://www.amazon.com/gp/product/B001AR3S8I/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B001AR3S8I&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>KEF KHT2005.3 5.1 Subwoofer/Satellite System with KUBE-2 (Gloss Black) $797</strong></a><br />
This is an amazing speaker system. In fact, if you buy it directly from <a href="http://www.htguys.com/amazon">Amazon</a>, where it happens to be <a href="http://www.amazon.com/gp/prime?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;ref_=sr_tc_sc_2_0&amp;qid=1326638658&amp;sr=8-2-tc%20via">Prime eligible</a>, you’ll pay <a href="http://www.amazon.com/gp/product/B007F1BW3G/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B007F1BW3G&amp;linkCode=as2&amp;tag=hdtvandhometh-20">$1399</a>. But right now, buying from a different seller through Amazon, you can get the whole 5.1 system for under $800. I really wanted to get the <a href="http://www.amazon.com/gp/product/B004AOZANE/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B004AOZANE&amp;linkCode=as2&amp;tag=hdtvandhometh-20">KEF T105 Home Theater System</a>, (which I use in my home), but couldn’t make it fit in my budget. If you have more than $2500 to spend, look into it.  But the KHT2005.3 is awesome.</p>
<ul>
<li>5.1 surround sound speaker system</li>
<li>Each satellite features KEF&#8217;s aluminum 100-millimeter (4-inch) Uni-Q drivers</li>
<li>Cast aluminum cabinets</li>
<li>200-watt 250-millimeter (10-inch) powered subwoofer</li>
<li>Wall mounting brackets are included</li>
</ul>
<p><a href="http://www.amazon.com/gp/product/B004OVECUA/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B004OVECUA&amp;linkCode=as2&amp;tag=hdtvandhometh-20"><strong>Logitech Harmony 700 Rechargeable Remote with Color Screen $90</strong></a><br />
We had budget to spend, so why not tie all the other items together with a great, low cost, very versatile and functional remote like the 700? I use a couple of them in various rooms in my home and couldn’t be happier with it.</p>
<ul>
<li>Rechargeable design keeps your remote charged and ready for up to a week &#8211; no batteries to buy or replace</li>
<li>Brilliant color screen displays favorite-channel icons and commands for easy selection</li>
<li>One-touch activity buttons automatically turn on all the right devices for instant access to TV, DVDs, music and more</li>
<li>Replaces multiple remotes with universal control of up to 6 different components</li>
<li>Supports virtually any home-entertainment component, including over 250,000 devices from over 5,000 brands</li>
</ul>
<p>All in, we’re pushing right up against our proposed budget at $2431. Some of these items aren’t available via <a href="http://www.amazon.com/gp/prime?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;ref_=sr_tc_sc_2_0&amp;qid=1326638658&amp;sr=8-2-tc%20via">Prime</a>, so there will be some shipping costs. Depending on where you live, you may have to pay tax on some of them as well.  We’ll have to leave the $69 cushion in the budget to cover those items, and maybe an <a href="http://www.amazon.com/b/?_encoding=UTF8&amp;ajr=0&amp;camp=1789&amp;creative=390957&amp;linkCode=ur2&amp;node=202505011&amp;tag=hdtvandhometh-20">HDMI cable</a> or two.</p>
<p>&nbsp;</p>
<h4>Ara:</h4>
<p><a href="http://www.amazon.com/dp/B00752VKFA/?tag=hdtvandhometh-20"><strong>Panasonic VIERA TC-P55GT50 55-Inch 1080p 600Hz Full HD 3D Plasma TV $1350</strong></a><br />
Ok so I blew more than half of my budget on the TV. Yes I went with plasma because dollar for dollar you won’t be able to beat the picture in this size.  The main reason I picked this TV was for the picture quality. It has deep blacks and fantastic color. It also is a smart TV if you are interested in that. Other features that make this a worthy purchase are:</p>
<ul>
<li>Built in wifi</li>
<li>THX 3D Certified</li>
<li>Skype</li>
<li>DLNA</li>
</ul>
<p><a href="http://www.amazon.com/dp/B0091VAHIO/?tag=hdtvandhometh-20"><strong>Sony BDPBX39 Blu-ray Player with Wi-Fi $135</strong></a><br />
Every home theater needs a blu-ray player and back in the day this component would have blown about half the budget as well. The good news for today is that you get a blu-ray player that turns your TV into a SmartTV wirelessly. With this unit you can stream Netflix, YouTube, Pandora, HuluPlus and more and control it from an Android or iOS app.</p>
<p><a href="http://www.amazon.com/dp/B007JF85WE/?tag=hdtvandhometh-20"><strong>Yamaha RX-V673 7.2-Channel Network AV Receiver $550</strong></a><br />
My receiver choice comes right off of our receiver buying guide. And why not you get so much for the $550. You get Airplay, Android/iOS remote control support, next generation audio, and automatic room calibration all in a great sounding receiver that won’t break the bank.</p>
<p><a href="http://www.amazon.com/dp/B000O7LEG8/?tag=hdtvandhometh-20"><strong>JBL CS6100BG High-Performance Complete 6-Piece Home Theater Speaker System with Brackets $400</strong></a><br />
This is the area that most home theater in a box systems cut corners on. While these speakers aren’t the best on the market they are considerably better than any HTIB speakers you can buy for $400. If you think about it, how much can the speakers in a $400 HTIB system actually be worth? These speakers look and sound great. But at some point down the road this would be the first thing to upgrade when you want serious room filling sound.</p>
<p>The total cost for my system is $2435. The TV, receiver and speakers are available via prime so there are no shipping costs. The Blu-ray player is showing a shipping cost of $7.99 at the time of this writing. That means the system comes in at $2443 + tax. Note: I felt no need for a Roku or AppleTV since both the Blu-ray player and the TV support most streaming functions. But if you have to have those it only adds at most $100.</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-12-14.mp3">Download Episode #561</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>December 13, 2012 10:54 PM</b>
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
			<?=getComments(4962)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4962)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/12/hdtv-and-home-theater-podcast-podcast-561-ht-guys-home-theater-in-a-box-2012.php" type="text/javascript" charset="utf-8"></script>
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