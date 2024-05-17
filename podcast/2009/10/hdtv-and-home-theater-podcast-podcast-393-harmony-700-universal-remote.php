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
		AND e.entry_id = 3299";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3299 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3299 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3299";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3299";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote</title>
	<meta name="keywords" content="lcd hdtv, home theater, panasonic viera, hdtv panasonic, harmony universal, harmony, hdtv, panasonic, inch, remote, get, lcd, tvs, buttons, theater, top, series, samsung, home, remotes, viera, our, universal, everything, hard" />
	<meta name="description" content="Not content with what the HT Guys have declared the best universal remote on the market, Logitech continues to put out new Harmony Universal Remotes.  Of the two most recent models on the market, the Harmony 700 and the Harmony 900, we've had a chance to look at the 700.  It is the less expensive of the two, and can be found in retail stores and online for an MSRP of $150 US.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=51&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Not content with what the HT Guys have declared the best universal remote on the market, Logitech continues to put out new Harmony Universal Remotes.  Of the two most recent models on the market, the Harmony 700 and the Harmony 900, we've had a chance to look at the 700.  It is the less expensive of the two, and can be found in retail stores and online for an MSRP of $150 US.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=51&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3299', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php">HDTV and Home Theater Podcast - Podcast #393: Harmony 700 Universal Remote</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October  1, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=314&category=General Interest">General Interest</a></b>
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
<h3>Smaller HDTVs Selling like Hotcakes</h3>
<p>We came across an article at twice.com called <a id="e6td" title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.twice.com/article/355641-Ratio_Of_Small_TVs_To_Large_Shifts_To_3_2.php" target="_blank">Ratio Of Small TVs To Large Shifts To 3:2</a>.  The article points to research from Retrevo Pulse that found smaller TVs, those up to 37 inches, are selling at a 3:2 ratio compared with larger sets in the 37 to 50 inch size range.  This is up from a 1:1 ratio one year ago.</p>
<p>The research analyst cited three potential reasons for this change:</p>
<ul>
<li>The completion of the digital TV transition on June 12 was a motivating factor in a new TV purchases by a wider population segment.</li>
</ul>
<ul>
<li>More households are now adding multiple HDTV sets for various rooms in the house.</li>
</ul>
<ul>
<li>More HDTV programming through terrestrial broadcasts, cable, satellite and Internet TV is now available, stoking consumer demand for sets on which to view it.</li>
</ul>
<p>In other words (or our words):</p>
<ul>
<li>People buying TVs now will only buy digital because it&#8217;s just plain silly to buy analog and it&#8217;s tough to find a digital TV that isn&#8217;t high definition anymore.  Most TV purchases are smaller TVs, so it stands to reason that the number of smaller HDTVs sold would increase.</li>
<li>People already own their big Family Room/Home Theater HDTV.  That was the first one they bought.  Now they&#8217;re adding TVs for bedrooms, bonus rooms, offices, kitchens, etc.</li>
<li>Perhaps consumers are finally getting over the &#8220;there&#8217;s nothing on in HDTV&#8221; or &#8220;everything I like it still only available in standard definition&#8221; hurdle.  Could it be true?  We certainly hope so.</li>
</ul>
<p>So on the surface, the trend makes perfect sense, but we decided to put it to the test.  As with everything else consumer and buying related, we turn to <a id="h_jx" title="Amazon.com" href="http://www.amazon.com/?%5Fencoding=UTF8&amp;tag=hdtvandhometh-20" target="_blank">Amazon.com</a> to be our barometer.</p>
<p><strong>Top 10 TVs at Amazon.com:</strong></p>
<ol>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001UAB40E" target="_blank">Panasonic VIERA G10 Series TC-P46G10 46-Inch 1080p Plasma HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001T9N0EO" target="_blank">Sony BRAVIA V-Series KDL-46V5100 46-Inch 1080p 120Hz LCD HDTV, Black</a> by Sony</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001VKY7WU" target="_blank">Samsung LN52B750 52-Inch 1080p 240Hz LCD HDTV with Charcoal Grey Touch of Color</a> by Samsung</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001U3Y8LS" target="_blank">Samsung LN26B360 26-Inch 720p LCD HDTV</a> by Samsung</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001UE6M9S" target="_blank">Panasonic VIERA C12 Series TC-L32C12 32-Inch 720p LCD HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001U3Y8LI" target="_blank">Samsung LN22B360 22-Inch 720p LCD HDTV</a> by Samsung</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001V5J7OI" target="_blank">LG 32LH30 32-Inch 1080p LCD HDTV, Gloss Black</a> by LG</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001U3YIM2" target="_blank">Panasonic VIERA S1 Series TC-L37S1 37-Inch 1080p LCD HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001SE4YQS" target="_blank">Panasonic VIERA X1 Series TC-P42X1 42-Inch 720p Plasma HDTV</a> by Panasonic</li>
<li><a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B001UE6MA2" target="_blank">Panasonic VIERA X1 Series TC-L26X1 26-Inch 720p LCD HDTV</a> by Panasonic</li>
</ol>
<p><strong>Observations:</strong></p>
<ul>
<li>Amazon&#8217;s top 10 bestselling TVs represent the exact 3:2 found in the research, but the top 3 TVs in the list all fall in the large size range.</li>
<li>There are 8 LCDs on the list and 2 plasmas</li>
<li>Samsung used to dominate the top 10, now Panasonic is the big player with 5 of the top 10 including the #1 set.</li>
<li>The first set 60 inches or larger in the full top 100 list appears at #57.  it is a plasma: <a title="Ratio Of Small TVs To Large Shifts To 3:2" href="http://www.htguys.com/shop?id=B002IK8H0A" target="_blank">Panasonic VIERA S1 Series TC-P65S1 65-Inch 1080p Plasma HDTV, Black</a> by Panasonic</li>
<li>There are no rear projection sets in the top 100</li>
</ul>
<h3>Harmony 700 Universal Remote</h3>
<p>Not content with what the HT Guys have declared the best universal remote on the market, <a href="http://www.logitech.com/" target="_blank">Logitech</a> continues to put out new Harmony Universal Remotes.  Of the two most recent models on the market, the <a id="et85" title="Harmony 700" href="http://www.logitech.com/index.cfm/remotes/universal_remotes/devices/6063&amp;cl=us,en" target="_blank">Harmony 700</a> and the <a id="b.vz" title="Harmony 900" href="http://www.logitech.com/index.cfm/remotes/universal_remotes/devices/5874&amp;cl=us,en" target="_blank">Harmony 900</a>, we&#8217;ve had a chance to look at the 700.  It is the less expensive of the two, and can be found in retail stores and online for an MSRP of $150 US (<a title="Buy Now" href="http://www.htguys.com/shop?id=B002IC0YLS" target="_blank">Buy Now</a>).</p>
<p><strong>Setup</strong></p>
<p>The manual tells you to reserve 45 minutes to get the remote set up.  If you&#8217;re a first-timer, that might be about right.  You need to make sure you know the model numbers for all your home theater equipment and also set aside a few minutes to get familiar with the programming software.</p>
<p>For us, and likely for those who&#8217;ve owned a Harmony before, it&#8217;s much closer to 15 than 45 minutes.  As is customary, we started from scratch, but there&#8217;s really no difference in how most of the Harmony remotes are programmed.  There are only subtle differences on what buttons it has, how many soft buttons are available, etc.  It took about 15 minutes to program 700 even when you consider the time it took to include all our customizations.</p>
<p>In addition to the remote, the box includes a USB cable for programming which also doubles as a recharging cord when plugged into the included wall adapter.  So yes, this Harmony is also a rechargeable model, but it&#8217;s slightly different.  For this one Logitech chose to include 2 NiMH rechargeable AA&#8217;s.  We found the charging cord to be a bit short, but since you can get a week&#8217;s worth of use out of one charge, there&#8217;s no need to keep it constantly plugged in.</p>
<p>It would seem that one benefit of the AA form factor is that if the batteries die, you can simply swap them out for some standard AA batteries you have lying around until you can get a pair of new rechargeables.  We didn&#8217;t test this theory, though, since the manual warns of a risk of explosion should you replace the batteries with an incorrect type.  Sounds like an episode of MacGyver in the making.</p>
<p><strong>Design</strong></p>
<p>The 700 is a replacement for the trusty 880 many of us had grown to love.  But admit it, it needed a face lift and the 700 provides just that.  It will drop you to four soft buttons from eight on the 880, but it provides more hard buttons that are laid out much better and a much easier to get to.  It also adds three hard buttons for the most common activities, Watch TV, Watch a Movie and Listen to Music.  Of course you can always override those to do whatever you want.</p>
<p><strong>Use</strong></p>
<p>The Harmony line of remotes is award winning and a lock for the Home Theater no-brainer award, in our opinion.  The 700 continues in that tradition.  Setup is as simple as you can get.  Then you get one click to turn everything on and setup right to do whatever you want in your home theater.  Every button on the remote does exactly what you&#8217;d expect it to without having to switch between devices, then one click to turn everything off.</p>
<p>All the buttons light up, so if you&#8217;re watching in the dark, a simple shake of the remote lets you see everything perfectly.</p>
<p><strong>Other stuff</strong></p>
<p>So the 700 falls right in the middle of the Harmony lineup.  Entry level is the 510 for $100 MSRP.  You then step up to the 700 for rechargeable batteries, a color screen and a slightly more elegant aesthetic.  From there you can move up to the Harmony One for $250.  It gives you a color touchscreen with more soft buttons and a charging cradle.  And then up to the cream of the crop in hard button remotes, the 900 for $400.  It looks just like the One, but includes built-in RF.</p>
<p>It&#8217;s worth noting that we use Harmony remotes almost exclusively in our homes and consider the Harmony One to be the de-facto standard in how a home theater remote should be built.  Harmony also offers a complete touch screen model, the ultra-sexy 1100 for $400 MSRP.  But we tend to prefer hard button remotes for their ease of use and simplicity.</p>
<p>In our food analogy from <a id="qun-" title="Episode #372" href="http://www.htguys.com/podcasts/2009/5/7/harmony-remote-round-up-podcast-372.html" target="_blank">Episode #372</a>, the 880 came in as a nice steak dinner.  Enough to get dressed up, but a great deal at the same time.  You&#8217;ll brag about what you got, and how little you paid for it.  The 700 fits right there as well, only this time you&#8217;re going out to the newest steakhouse in town.</p>
<p><strong>Conclusion</strong></p>
<p>As with any Harmony remote, the 700 is an excellent choice to control your home theater.  It&#8217;s new, sexy and incredibly easy to use.  While overall it doesn&#8217;t represent a huge departure from the 880, it does offer some nice usability upgrades.  For the coolness factor, the One with its touchscreen is still where it&#8217;s at, but for those who want the best of both worlds, a great, easy to use remote that costs a little less, the 700 is ideal.  If you haven&#8217;t tried a Harmony universal remote yet, you owe it to yourself.</p>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-10-02.mp3">Download Episode #393</a></p>
  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October  1, 2009 11:29 PM</b>
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
			<?=getComments(3299)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3299)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/10/hdtv-and-home-theater-podcast-podcast-393-harmony-700-universal-remote.php" type="text/javascript" charset="utf-8"></script>
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