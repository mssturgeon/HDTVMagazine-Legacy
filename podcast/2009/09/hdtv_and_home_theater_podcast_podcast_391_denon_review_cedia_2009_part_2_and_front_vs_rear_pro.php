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
		AND e.entry_id = 3283";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3283 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3283 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3283";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-391-denon-review-cedia-2009-part-2-and-front-vs-rear-pro.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3283";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro</title>
	<meta name="keywords" content="blu ray, home theater, denon avr, cedia part, surround sound, home, sound, denon, movies, buy, support, theater, receiver, blu, ray, time, front, web, disc, high, going, remote, hdmi, pretty, cedia" />
	<meta name="description" content="We received a great listener review of the Denon AVR-3310CI Home Theater Receiver, so we'll share that with you. We also found a slightly different take on this year's CEDIA show with a few more products to talk about. Then we dive into the ageless debate, what to buy: a Front or Rear Projector for your home theater.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=45&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-391-denon-review-cedia-2009-part-2-and-front-vs-rear-pro.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="We received a great listener review of the Denon AVR-3310CI Home Theater Receiver, so we'll share that with you. We also found a slightly different take on this year's CEDIA show with a few more products to talk about. Then we dive into the ageless debate, what to buy: a Front or Rear Projector for your home theater.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=45&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3283', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-391-denon-review-cedia-2009-part-2-and-front-vs-rear-pro.php">HDTV and Home Theater Podcast - Podcast #391: Denon Review, CEDIA 2009 Part 2 and Front vs. Rear Pro</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September 17, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>, <b><a href="/category.php?id=496&category=Front Projection">Front Projection</a></b>
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
				<div class='snap_preview'><br /><h2>Today&#8217;s Show:</h2>
<h3>Links:</h3>
<ul>
<li><a href="http://thewoodwhisperer.com/low-entertainment-center-pt-1/" target="_blank">The Wood Whisperer: Build your own Home Entertainment Center</a></li>
<li><a href="http://www.electronichouse.com/article/q_should_i_go_with_a_big_rptv_or_projection_system/C223" target="_blank">Ask a Pro: Q. Should I Go with a Big RPTV or Projection System?</a></li>
<li><a href="http://www.hometheaterblog.com/hometheater/2009/09/cedia-2009-wrapup/" target="_blank">CEDIA 2009 Wrap-Up at HomeTheaterBlog.com</a></li>
</ul>
<p>We received a great listener review of the Denon AVR-3310CI Home Theater Receiver, so we&#8217;ll share that with you.  We also found a slightly different take on this year&#8217;s CEDIA show with a few more products to talk about. Then we dive into the ageless debate, what to buy: a Front or Rear Projector for your home theater.</p>
<h3>Listener J.R. Reviews the New Denon AVR-3310C (<a href="http://www.htguys.com/shop?id=B002AKKFQ2">$1500 Buy Now</a>)</h3>
<div><em> I just bought a Denon AVR-3310CI and thought I would share my experiences with you and your listeners.</p>
<p>My impetus to buy was the release of the PS3 Slim and it&#8217;s bitstream capabilities. I knew for a long time that I wanted an HDMI-capable receiver, and was willing to live without bitstream mode from my original PS3. After hearing that the slim has bitstreaming of lossless audio, I felt is was time to move the original PS3 to the basement and buy a Slim. I then convinced myself that it was time to take the plunge and get that HDMI receiver I always wanted.</p>
<p>I first bought an Onkyo NS-TX807. After setting it up, it sounded good and did everything I wanted it to do. Then the problems started. It began emitting distortion through my speakers and my TV, and then shut down the audio output completely. I think it overheated despite being in a pretty well-ventilated shelving unit. After letting it cool down for about an hour, I tried again. This time it worked, but it started pushing a &#8220;thump&#8221; to my subwoofer every the PS3 changed audio modes. I was obviously not going to live with this, so I decided to return the Onkyo and get a Denon.</p>
<p>I was going to buy a 2310, but realized that I probably would not buy another receiver for a while. I have plans to build a dedicated theater in the next five years, and I figured this new receiver was going to move into the theater when it&#8217;s built. So&#8230;I bit the bullet and bought the 3310. It&#8217;s more future-proof.</p>
<p>This model has all the features you&#8217;d expect in an a mid-priced AVR: multiple HDMI inputs, video upscaling, DTS-MA and Dolby True HD decoding, support for the new soundfields such as front high speakers, networking capability, HD radio, iPod dock support, and multi-zone output.</p>
<p>After going through the Audyssey set-up, I first tried one of my favorite CDs. I was blown away with how much better this Denon sounds. I have had a lot of different receivers from a lot of different manufacturers. This one, by far, has the most accurate, smooth, and balanced sound reproduction. It sounds even better than my high-end (albeit 7-year old) Onkyo which is mated to superior speakers (makes me want to move the better speakers upstairs!). The other nice thing about CD playback is that the receiver automatically senses the source material and sets the correct playback mode. When playing a CD, it chose stereo mode. When I switched to a DVD, it chose the correct codec for the disc being played.</p>
<p>After trying a CD, I popped in &#8220;The Fifth Element&#8221; to experience True HD. This blew me away. Not only was the sound quality better overall, the surround channels were much more alive. I was totally enveloped in sound, which was a very cool feeling. I can&#8217;t wait for movie night this week, not to mention the Glee premiere tomorrow night on Fox.</p>
<p>I then switched over to the tuner and experimented with HD radio. For those who have no experience with HD radio, it&#8217;s CD-quality sound with additional information such as the artist and song title. This was a welcome surprise. I didn&#8217;t realize how many HD radio channels were available in my area. The Denon picked up about a half dozen while using the supplied antenna.</p>
<p>Finally, the wife acceptance factor is pretty high on this one. My wife will really enjoy the Dynamic Volume, because she can&#8217;t stand the large swings in sound during movie playback. We usually have to watch movies with closed captioning turned on because inevitably the dialog is too quiet in relation to the overall soundtrack. Dynamic Volume should fix this problem. Also, the remote is pretty straightforward. Denon placed most of the common features on the front of the remote, with big, easy to find buttons. The more arcane commands are hidden behind a panel on the back of the remote.</p>
<p>In all, I&#8217;m really pleased. The sound reproduction is fantastic, the features are great, and it&#8217;s pretty future-proof (at least until HDMI 1.4 comes out!). It cost a little more than I wanted to spend, but I think it will last me a long time.</p>
<p></em></div>
<div>
<h3>CEDIA 2009, Part 2</h3>
<p><strong><a href="http://www.crestron.com/products/show_products.asp?jump=1&amp;model=ADMS" target="_blank">Crestron ADMS:</a></strong></div>
<div>
<ul>
<li>Removes the boundaries between movies, music, TV, and the Internet</li>
<li>Delivers all the content you want from DVDs, Blu-ray Discs, CDs, MP3s, iTunes®, Windows Media®, Netflix®, Amazon.com®, YouTube® and virtually any other online source you desire</li>
<li>Organizes everything in an intuitive on-screen or touchpanel user interface</li>
<li>Allows easy searching by title, actor, artist, genre, etc.</li>
<li>WorldSearch™ instantly finds the content you want ― whether its on a hard drive, optical disc, or the Internet ― and delivers it to your home theater in full HD and 7.1 surround sound</li>
<li>Purchase or rent movies and videos right on-screen</li>
<li>Integrated Web browser enables access to all your favorite Web sites</li>
<li>Onboard Blu-ray Disc drive allows playing DVDs and importing CDs</li>
<li>Up to 1000 disc external Blu-ray Disc® storage ― adds every disc to your media library complete with cover art and metadata</li>
<li>1TB internal hard drive storage</li>
<li>RAID 1 (mirrored) data loss prevention technology</li>
<li>Expandable using NAS or Windows Home Server appliances</li>
<li>Outputs 1080p high-definition video and 7.1 surround sound, plus 2 discrete zones of stereo audio</li>
<li>Home Control screen enables onscreen control of lighting, climate, and other home automation functions</li>
<li>Affords native Crestron® control system integration via Ethernet</li>
<li>Gigabit LAN port enables ultra high-speed downloads and glitch-free streaming</li>
<li>Front panel USB, 1394, and MMC ports enable easy transfer of home movies, photos, and music files</li>
<li>Advanced HDCP support ensures compatibility with protected content</li>
<li>Ultra secure and reliable operating system delivers a superior alternative to HTPCs</li>
<li>Ultra-quiet design allows placement in the immediate listening environment</li>
<li>VNC remote access enables off-premises dealer support and upgrade</li>
<li>3-space 19&#8243; rack-mountable (rack ears included)</li>
</ul>
</div>
<div><strong><a href="http://gizmodo.com/5356596/pioneer-project-et-hands-on-the-ultimate-networked-media-box" target="_blank">Pioneer’s Project ETAP (Entertainment Tap):</a></strong></p>
<ul>
<li> Play Blu-ray movies</li>
<li><a href="http://gizmodo.com/5288454/blu+ray-managed-copy-full+res-backups-are-only-good-in-theory">Managed Copy</a> (backup) Blu-ray movies</li>
<li> Connect to video services like Netflix (though definitely not Hulu)</li>
<li> Stream stuff like MovieTrailers.com with a polished, non-web interface</li>
<li> Link you to buying related movies/products through an unobtrusive interface</li>
<li> Support Windows Media Center (possibly through DLNA)</li>
<li> Be controlled through Android phones, the Nokia N810 (demoed perfectly), and the iPod touch (limited demo through the web)</li>
<li> Load 128 USB connected drives (which it encrypts in some cases)</li>
<li> Rip music in FLAC and PCM</li>
<li> Automatically include the album art and lyrics</li>
<li> Stream LastFM, Rhapsody and support Rhapsody downloads</li>
<li> Integrate third party home automation devices</li>
<li> Update Twitter</li>
<li> And do everything listed above through a SlingBox-like, web-mirrored interface</li>
</ul>
</div>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-09-18.mp3">Download Episode #391</a></p>
  <a rel="nofollow" href="http://feeds.wordpress.com/1.0/gocomments/htguys.wordpress.com/45/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/comments/htguys.wordpress.com/45/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/godelicious/htguys.wordpress.com/45/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/delicious/htguys.wordpress.com/45/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/gostumble/htguys.wordpress.com/45/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/stumble/htguys.wordpress.com/45/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/godigg/htguys.wordpress.com/45/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/digg/htguys.wordpress.com/45/" /></a> <a rel="nofollow" href="http://feeds.wordpress.com/1.0/goreddit/htguys.wordpress.com/45/"><img alt="" border="0" src="http://feeds.wordpress.com/1.0/reddit/htguys.wordpress.com/45/" /></a> <img alt="" border="0" src="http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&blog=8935650&post=45&subd=htguys&ref=&feed=1" /></div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September 17, 2009 11:35 PM</b>
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
			<?=getComments(3283)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3283)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/09/hdtv-and-home-theater-podcast-podcast-391-denon-review-cedia-2009-part-2-and-front-vs-rear-pro.php" type="text/javascript" charset="utf-8"></script>
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