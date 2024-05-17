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
		AND e.entry_id = 3368";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3368 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3368 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3368";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-398-hdtv-buying-guide-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3368";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009</title>
	<meta name="keywords" content="lcd hdtv, last year, size category, panasonic viera, inch plasma, inch, hdtv, year, lcd, plasma, picture, last, model, size, price, great, category, samsung, inches, our, large, panasonic, support, get, hdmi" />
	<meta name="description" content="It's time for our annual HDTV buying guide. Just like last year, we're listing them by size instead of technology. Prices have dropped quite a bit in just 12 months, and there are some really exciting deals available right now. This year the prices for tier one manufacturers are even more affordable. Without further ado, here is the list for 2009.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=71&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-398-hdtv-buying-guide-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It's time for our annual HDTV buying guide. Just like last year, we're listing them by size instead of technology. Prices have dropped quite a bit in just 12 months, and there are some really exciting deals available right now. This year the prices for tier one manufacturers are even more affordable. Without further ado, here is the list for 2009.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=71&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3368', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-398-hdtv-buying-guide-2009.php">HDTV and Home Theater Podcast - Podcast #398: HDTV Buying Guide 2009</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>November  5, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=438&category=Deals & Discounts">Deals & Discounts</a></b>, <b><a href="/category.php?id=447&category=PC & Laptop Technology">PC & Laptop Technology</a></b>
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
<h3>HDTV Buying Guide 2009</h3>
<div>It&#8217;s time for our annual HDTV buying guide. Just like last year, we&#8217;re listing them by size instead of technology. Prices have dropped quite a bit in just 12 months, and there are some really exciting deals available right now. This year the prices for tier one manufacturers are even more affordable. Without further ado, here is the list for 2009:</div>
<p><strong>Less than 32&#8243;</strong></p>
<div>In this size category we mostly are looking for value. At 20 inches you really can&#8217;t see the benefits of 1080p so all of our selections are 720p. Last year we featured a 19 inch Samsung TV for $317. This year the most expensive TV in the category is about $100 less.</div>
<ul>
<li><a title="Samsung LN19B360 19-Inch 720p LCD HDTV" href="http://www.htguys.com/shop?id=B001U3Y8L8" target="_blank">Samsung LN19B360 19-Inch 720p LCD HDTV</a> &#8211; This is your basic 19&#8243; LCD TV. Great for an office or bedroom. Don&#8217;t expect a home theater experience with this one. But at $220 you can&#8217;t go wrong!</li>
<li><a title="ViewSonic N1630w 16-Inch 720p LCD HDTV" href="http://www.htguys.com/shop?id=B00170V91G" target="_blank">ViewSonic N1630w 16-Inch 720p LCD HDTV</a> &#8211; Another basic model. This one is only 16 inches so it will fit in tighter spots in the kitchen. This one is goes for $185.</li>
<li><a title="VIZIO 19&quot; Class LCD HD 720P HDTV" href="http://www.htguys.com/shop?id=B001QW1ONI" target="_blank">VIZIO 19&#8243; Class LCD HD 720P HDTV</a> &#8211; We couldn&#8217;t have a budget TV selection without a model from Vizio ($190).</li>
</ul>
<div>
<div><strong>32&#8243; to 40&#8243;</strong></div>
<div>In this size category we stayed with value. We figured for most people this size category is either a second TV for a bedroom or perhaps a primary one for an apartment or condo. Here on the low end price did not go down but you can get more for the same amount. Last year we had a 40 inch model going for $915. This year we select two 37 inch models for about $600.</div>
<ul>
<li><a title="Panasonic Viera TC-37LZ800 37-Inch 1080p LCD HDTV" href="http://www.htguys.com/shop?id=B0014CTAM2" target="_blank">Panasonic Viera TC-37LZ800 37-Inch 1080p LCD HDTV</a> &#8211; Crisp picture, good blacks, accurate colors. Great off angle viewing, the TV has IPS. Price $725.</li>
<li><a id="s41b" title="LG 37LG50 37-Inch 1080p LCD HDTV" href="http://www.htguys.com/shop?id=B0016Z526U" target="_blank">LG 37LG50 37-Inch 1080p LCD HDTV</a> &#8211; Good picture with deep blacks. Three HDMI inputs. Nice looking TV for $640.</li>
<li><a id="wggl" title="Samsung LN32B530 32-Inch 1080p LCD HDTV" href="http://www.htguys.com/shop?id=B001U3Y8N6" target="_blank">Samsung LN32B530 32-Inch 1080p LCD HDTV</a> &#8211; Great TV for the bedroom or den. Accurate color and sharp picture for $600.</li>
</ul>
<div><strong>42&#8243; to 50&#8243;</strong></div>
<div>This year pricing was Similar to last year but you get a lot more. Last year we had a 42 inch Panasonic plasma that was priced at 1059. This year you get 46 inches for less money. Our 120 Hz model cost $400 more than this year. We did not recommend any 240Hz TVs this year. We feel the premium paid for the feature is not worth it at this time.</div>
<ul>
<li><a id="zt7s" title="Samsung LN46A650 46-Inch 1080p 120 Hz LCD HDTV with Red Touch of Color" href="http://www.htguys.com/shop?id=B001413D94" target="_blank">Samsung LN46A650 46-Inch 1080p 120 Hz LCD HDTV with Red Touch of Color</a> &#8211; Along with a fantastic picture you also get 120Hz, DLNA, and Infolink. Info link give you access to News, Weather, Sports, and Stocks right on your TV. Connection to the Internet is required. If you are a sports junkie or love watching movies this TV has something for you. Price about $1260.</li>
<li><a id="llp4" title="Panasonic Viera TH-46PZ80U 46-Inch 1080p Plasma HDTV" href="http://www.htguys.com/shop?id=B00142JKRM" target="_blank">Panasonic Viera TH-46PZ80U 46-Inch 1080p Plasma HDTV</a> &#8211; If you are in the market for a mid-sized plasma this the one for you! Great picture at any angle, instant response for smooth sharp picture and three HDMI inputs. Makes a great main room TV and costs less than $1000.</li>
<li><a id="e48s" title="Sony Bravia W-Series KDL-40W3000 40-Inch 1080p LCD HDTV" href="http://www.htguys.com/shop?id=B000SB7XMY" target="_blank">Sony Bravia W-Series KDL-40W3000 40-Inch 1080p LCD HDTV</a> &#8211; Support for deep color and 24 frames a second make this a movie buff&#8217;s dream TV. Ideal for medium sized rooms and priced to go easy on your pocket book $999.99</li>
</ul>
<div><strong>Greater than 50&#8243;</strong></div>
<div>This size category is for TVs that go in large rooms. Last year our largest model was a 65 inch DLP that sold for $1440. This year we are only recommending flat panel displays. We do list a 65 inch plasma that was last years ultimate Christmas present. At that time the TV cost more than twice what it goes for today and this year&#8217;s model is better. We also include a value priced 55 inch LCD so you can save a little for new receiver or a Blu Ray player.</div>
<ul>
<li><a id="hb1a" title="Samsung UN55B7000 55-Inch 1080p 120 Hz LED HDTV" href="http://www.htguys.com/shop?id=B001UHMVR2" target="_blank">Samsung UN55B7000 55-Inch 1080p 120 Hz LED HDTV</a> &#8211; Very thin (1.2 inch) TV. Nice vivid colors, support for YAHOO widgets. Alternative to plasma but at a much higher price. Buy this if you want a large format TV but do not want Plasma and you are willing to spend $300 more than our 65 inch Plasma recommendation. Price $2750</li>
<li><a id="ypy-" title="Panasonic VIERA S1 Series TC-P65S1 65-Inch 1080p Plasma HDTV" href="http://www.htguys.com/shop?id=B002IK8H0A" target="_blank">Panasonic VIERA S1 Series TC-P65S1 65-Inch 1080p Plasma HDTV</a> &#8211; Essentially we can say the same thing about this 65 inch as we did for the 46 inch plasma. Great picture at any angle, instant response for smooth sharp picture. Native Contrast Ratio of 40,000:1. It only weighs 90 lbs so finding a stand is not an issue. Price $2450</li>
<li><a id="yvc9" title="LG 55LH40 55-Inch 1080p 120Hz LCD HDTV" href="http://www.htguys.com/shop?id=B001VKYAK4" target="_blank">LG 55LH40 55-Inch 1080p 120Hz LCD HDTV</a> &#8211; Clear picture, four HDMI inputs, 24 fps support and support for ISFccc calibration. Priced at $1490</li>
</ul>
<div><strong>HT Guy&#8217;s Ultimate Christmas Present:</strong></div>
<div>This year&#8217;s ultimate Christmas gift is not for everyone. Its a very large rear projection DLP. At 82 inches this TV can rival some projector setups in screen size without requiring any of the complicated installation of a projector. So if you have a large basement or bonus room this TV may be the ticket for large screen bliss. Of course we are talking about the <a id="jzli" title="Mitsubishi WD-82837 82-Inch 1080p 120Hz Home Theater DLP HDTV" href="http://www.htguys.com/shop?id=B001XUR5H2" target="_blank">Mitsubishi WD-82837 82-Inch 1080p 120Hz Home Theater DLP HDTV</a>. This TV measures 22.7 x 73.2 x 48.5 inches and weighs in at 154 pounds. Still a fraction of the rear projection TVs of yesterday. The TV supports four HDMI connections, deep color, and is energy star compliant. At $4500 it only costs $500 more than Ara&#8217;s first 50 inch DLP.</div>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-11-06.mp3">Download Episode #398</a></p>
</div>
  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>November  5, 2009 10:51 PM</b>
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
			<?=getComments(3368)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3368)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-398-hdtv-buying-guide-2009.php" type="text/javascript" charset="utf-8"></script>
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