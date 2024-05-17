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
		AND e.entry_id = 4545";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4545 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4545 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4545";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2011/10/hdtv-and-home-theater-podcast-podcast-500-a-look-back-at-500-episodes-of-hdtv-talk.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4545";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk</title>
	<meta name="keywords" content="blu ray, high definition, ray player, episodes hdtv, flat panel, blu, ray, hdtv, predictions, ”, podcast, inch, dvd, lcd, home, high, tvs, tube, hdtvs, make, players, channels, market, new, flat" />
	<meta name="description" content="It is hard to believe we&amp;#039;ve done 500 episodes of the HDTV and Home Theater Podcast.  So much has changed in the almost 7 years we&amp;#039;ve been doing this, and so much has stayed the same.  It&amp;#039;s been a great time; we&amp;#039;ve enjoyed sharing it with our listeners and are looking forward to 500 more." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2011/10/hdtv-and-home-theater-podcast-podcast-500-a-look-back-at-500-episodes-of-hdtv-talk.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It is hard to believe we&amp;#039;ve done 500 episodes of the HDTV and Home Theater Podcast.  So much has changed in the almost 7 years we&amp;#039;ve been doing this, and so much has stayed the same.  It&amp;#039;s been a great time; we&amp;#039;ve enjoyed sharing it with our listeners and are looking forward to 500 more." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4545', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2011/10/hdtv-and-home-theater-podcast-podcast-500-a-look-back-at-500-episodes-of-hdtv-talk.php">HDTV and Home Theater Podcast - Podcast #500: A Look Back at 500 Episodes of HDTV Talk</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October 13, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
<h3>A Look Back at 500 Episodes of HDTV Talk and Topics</h3>
<p>It is hard to believe we&#8217;ve done 500 episodes of the HDTV and Home Theater Podcast.  So much has changed in the almost 7 years we&#8217;ve been doing this, and so much has stayed the same.  It&#8217;s been a great time; we&#8217;ve enjoyed sharing it with our listeners and are looking forward to 500 more.</p>
<h3>2005:</h3>
<p>March 30, 2005 &#8211; Inaugural Podcast &#8211; Introductions: “In this weeks podcast we define and discuss basic HDTV terms and what it all means. During the primer section we will provide you with all the information you need to make informed decisions and not buy into the sales hype.”</p>
<p>Voom, the all-HD satellite service, shows some great stuff at CES, then quickly gets sold off to Dish/Echostar.  Dish eventually killed the Voom channels in 2008.</p>
<p>Anyone remember Brillian TVs?  They were a high end LCOS TV company that merged with LCD maker Syntax in Nov 2005, sold TVs under the Olevia name and eventually folded.</p>
<p>September 19, 2005 &#8211; Podcast #28 &#8211; The most awesome home theater you have ever seen!  We visited one of the most amazing Home Theaters we’ve ever been in, and have never been the same since.</p>
<h4>Televisions:</h4>
<ul>
<li>Tube TVs are a dominant force
<ul>
<li>Philips 30&#8243; Widescreen Flat-Tube HDTV $799.99</li>
<li>Sony FD Trinitron WEGA 30&#8243; Widescreen HDTV Tube $999.99</li>
<li>Toshiba 30&#8243; Widescreen HD-Ready Flat-Tube TV $799.99</li>
</ul>
</li>
<li>1080p TVs start to come to market
<ul>
<li>Samsung 50” 1080p DLP $5000</li>
</ul>
</li>
</ul>
<h3>2006:</h3>
<p>March 2006 &#8211; News Corp. executive says his company can get away with charging $25-30 to watch a single HDTV movie.</p>
<p>Canon and Toshiba show SED televisions at CES to rave reviews.  The technology was stifled by legal and manufacturing issues and never materialized.</p>
<p>With the introduction of HD-DVD and Blu-ray, we’re also introduced to new high definition audio formats from Dolby and DTS.</p>
<p>Dish Network and DirecTV closed out the year fighting over who has more HD channels, with the grand total being in the 35-40 channel range.</p>
<h4>Predictions:</h4>
<ul>
<li>BluRay and HD DVDs will finally hit the market with much fanfare, but at a very steep price. While the early adopters will all grab on to a format, most consumers will ignore this until there is more HD content available.</li>
</ul>
<h3>2007:</h3>
<p>March 9, 2007 &#8211; Podcast #152 &#8211; Setting up a do-it-yourself video Server and home entertainment network based around the Mac Mini.</p>
<h4>Predictions:</h4>
<ul>
<li>DIRECTV Will Become the HDTV Leader.</li>
<li>Several Basic Cable Channels Will Go HDTV In Mid-Year</li>
</ul>
<h4>HT Guys Predictions:</h4>
<ol>
<li>HDTV adoption will skyrocket.</li>
<li>HDTV content will boom (We will see at least 15 new national HDTV channels by the end of 2007)</li>
<li>50&#8243; HDTVs will be under $1000</li>
<li>The Next generation format war will drag on for one more year.</li>
<li>HD downloads and VOD will take off</li>
<li>Wireless technology will start to make its way into the living room.</li>
<li>Apple computer will enter the world of HD</li>
</ol>
<h4>Cost of HDTVs</h4>
<ul>
<li>Tube (CRT): $500 &#8211; $1000 for 30” &#8211; 34”</li>
<li>Flat Panel: $1500 &#8211; $2500 for 37” &#8211; 42”</li>
<li>Projection: $1000 &#8211; $2000 for 40” &#8211; 50”</li>
</ul>
<h4>Cost of Blu-ray</h4>
<ul>
<li>The newest high definition DVD players can cost more than $1000. High definition DVDs cost $25 and up.</li>
</ul>
<p>HD Guru writes a story about <a href="http://hdguru.com/your-new-disposable-flat-panel-hdtv/107/">Your New Disposable Flat Panel TV</a></p>
<p dir="ltr">Polaroid’s HDTVs, which sell mainly through Circuit City, would seem to be bargain-priced compared to the “name brand” competitors. Polaroid’s 1080p 42” LCD Model number PLD 4241TLXB, for example, sells for $1149.99 at Circuit City, while Toshiba’s 42HL167 goes for $1699.99.</p>
<p dir="ltr">The Polaroid set seems like a good deal until you find out what the Circuit City and Polaroid websites fail to disclose, which is that Polaroid HDTVs cannot be repaired after the warranty period expires!</p>
<h3>2008:</h3>
<p>January 22, 2008 &#8211; Podcast #243 &#8211; The Death of Rear Projection TVs: We witnessed the death of CRT a few years ago when we searched high and low all over the CES show floor for somebody showing anything resembling a tube TV and came up empty. This year, if you don&#8217;t count the Texas Instruments DLP booth, we saw only three rear projection sets at the entire show.</p>
<p>August 5, 2008 &#8211; Podcast #299 &#8211; Good news for Blu-ray early adopters, so far BD-Live is pointless &#8212; Guess what? BD-Live is still pointless in 2011!</p>
<h4>Predictions:</h4>
<ul>
<li>HDTV &#8216;Arms Race&#8217; Between Cable &amp; Satellite Will Escalate</li>
<li>Local HD News Becomes Ratings Weapon</li>
<li>Peace Comes to the Blu-ray vs. HD DVD War</li>
</ul>
<h4>HT Guys Predictions</h4>
<ol>
<li>HD-DVD and Blu-ray will not unify.</li>
<li>Microsoft will make a huge push into the living room.</li>
<li>Stand-alone Blu-ray player prices fall.</li>
<li>VUDU will gain momentum and make big strides in 2008.</li>
<li>Portable HD-DVD players hit the market.</li>
<li>Apple will redo the Apple TV as a true media center device.</li>
<li>Wireless HDMI will come to market.</li>
<li>LCD HDTV sales will skyrocket.</li>
<li>Reality shows will go high def.</li>
<li>HDMI-CEC will take hold.</li>
</ol>
<h4>Black Friday Predictions:</h4>
<ul>
<li>Blu-ray player for the low, low price of $149</li>
<li>Blu-ray player and movies bundle for $199</li>
<li>Blu-ray movies for $9.99</li>
<li>46 inch to 47 inch 1080p LCD for $799</li>
<li>52 inch 1080p LCD for $1,199</li>
<li>50 inch 720p plasma for $699</li>
</ul>
<h3>2009:</h3>
<ul>
<li>Sharp AQUOS DX first and probably last LCD TV with built in Blu-ray player</li>
<li>CNET calls Pioneer Kuro Elite Best flat panel TV ever!</li>
</ul>
<p>The “biggest change to television broadcasting since the advent of color” was set to occur on Tuesday, February 17, when all broadcasters would start sending a digital signal and cut off their analog transmissions.  This date was postponed to June 12 and became the biggest non-event since Y2K.</p>
<h4>Ara&#8217;s Predictions</h4>
<ol>
<li>Netbooks will become popular as portable A/V devices</li>
<li>Movie Download services will Blossom</li>
<li>Blu Ray Movie Prices will be on par with their DVD counterparts</li>
<li>Digital Transition will go smoothly</li>
<li>DirecTV and/or Dish Network will start transmitting Dolby Digital Plus Audio Tracks on some channels</li>
</ol>
<h4>Braden&#8217;s Predictions</h4>
<ol>
<li>New release downloads will go &#8220;all you can eat&#8221;</li>
<li>Portable Blu-ray players will hit the shelves</li>
<li>A true iPhone alternative will emerge</li>
<li>Blu-ray prices will fall to be the same as DVD</li>
<li>Something really exciting will happen in TV technology</li>
</ol>
<h3>2010:</h3>
<ul>
<li>Fifty-inch HDTVs for as little as $550? 32-inch LCD HDTVs for under $200? Blu-ray players for $70 or less?</li>
<li>3D TVs start rolling out.</li>
<li>Between 8 and 9 million Blu-ray players are sold in 2010</li>
<li>42 inch Panasonic Plasma was available for $300 during Black Friday Sales</li>
</ul>
<p>The HT Guys and Dolby “Ultimate Home Entertainment” contest wrapped up in December.  The video submissions were awesome and the <a href="http://www.htguys.com/ultimate">winner</a> stills makes us laugh.</p>
<h4>Ara&#8217;s Predictions</h4>
<ol>
<li>Apple will change the TV industry</li>
<li>3D Will be Forced Upon All of Us</li>
<li>OLED will break the 11 inch barrier for a commercially available model</li>
<li>Plasma will hold on to market share if not get a little stronger</li>
</ol>
<h4>Braden&#8217;s Predictions</h4>
<ol>
<li>Netflix will make some new releases available via streaming</li>
<li>Blockbuster will make the transition to kiosk</li>
<li>High Definition streaming will become a reality</li>
<li>3D in the home will flop</li>
<li>4K TV will emerge as the &#8220;next big thing&#8221;</li>
</ol>
<h3>2011:</h3>
<ul>
<li>stay tuned!</li>
</ul>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2011-10-14.mp3">Download Episode #500</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October 13, 2011 10:30 PM</b>
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
			<?=getComments(4545)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4545)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2011/10/hdtv-and-home-theater-podcast-podcast-500-a-look-back-at-500-episodes-of-hdtv-talk.php" type="text/javascript" charset="utf-8"></script>
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