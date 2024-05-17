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
		AND e.entry_id = 3384";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3384 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3384 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3384";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-400-black-friday-preview.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3384";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview</title>
	<meta name="keywords" content="regular price, blu ray, disc player, ray disc, hdtv model, price, regular, blu, ray, hdtv, model, player, lcd, samsung, disc, full, here, plasma, hdmi, friday, card, ready, sony, black, gift" />
	<meta name="description" content="On today's show we will discuss the expected Black Friday deals we can all look forward to on the day after Thanksgiving. Last year the cheapest Blu Ray player was $128 and a  50 inch 720p plasma was going for $900. This year the cheapest Blu Ray player comes in at $78 and you can pick up a 50 inch 1080p plasma with a Blu Ray player for $1000. There are so many deals out there we can't talk about all of them. We selected a few we thought were mentioning so that you can plan out your Black Friday strategy.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=85&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-400-black-friday-preview.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="On today's show we will discuss the expected Black Friday deals we can all look forward to on the day after Thanksgiving. Last year the cheapest Blu Ray player was $128 and a  50 inch 720p plasma was going for $900. This year the cheapest Blu Ray player comes in at $78 and you can pick up a 50 inch 1080p plasma with a Blu Ray player for $1000. There are so many deals out there we can't talk about all of them. We selected a few we thought were mentioning so that you can plan out your Black Friday strategy.&lt;img alt=&quot;&quot; border=&quot;0&quot; src=&quot;http://stats.wordpress.com/b.gif?host=htguys.wordpress.com&amp;blog=8935650&amp;post=85&amp;subd=htguys&amp;ref=&amp;feed=1&quot; /&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3384', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-400-black-friday-preview.php">HDTV and Home Theater Podcast - Podcast #400: Black Friday Preview</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>November 19, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=438&category=Deals & Discounts">Deals & Discounts</a></b>, <b><a href="/category.php?id=487&category=Plasma">Plasma</a></b>, <b><a href="/category.php?id=505&category=Plasma HDTVs">Plasma HDTVs</a></b>
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
<p><strong>Black Friday Roundup</strong></p>
<p>On today&#8217;s show we will discuss the expected Black Friday deals we can all look forward to on the day after Thanksgiving. Last year the cheapest Blu Ray player was $128 and a  50 inch 720p plasma was going for $900. This year the cheapest Blu Ray player comes in at $78 and you can pick up a 50 inch 1080p plasma with a Blu Ray player for $1000. There are so many deals out there we can&#8217;t talk about all of them. We selected a few we thought were mentioning so that you can plan out your Black Friday strategy. In addition to what we talk about here Apple Computer usually has some good deals in store and on the web. Amazon does as well and if you use our link you help out the show (<a id="ucbg" title="HT Guys Amazon Link" href="http://www.amazon.com/?%5Fencoding=UTF8&amp;tag=hdtvandhometh-20" target="_blank">HT Guys Amazon Link</a>).</p>
<div>Good Hunting!</div>
<div>
<div><em><strong>Target</strong> (<a id="eh:e" title="Full Ad Here" href="http://www.blackfriday.info/sales/target-black-friday-ad.html" target="_blank">Full Ad Here</a>)</em></div>
<div>
<ul>
<li>The Dark Knight Blu Ray &#8211; $12.99</li>
<li><a id="link-28191" rel="28191" href="http://www.blackfriday.info/item/28191" target="_blank">Paul Blart Mall Cop DVD</a> &#8211; $5.99 (regular price $19.99)</li>
<li><a id="link-28036" rel="28036" href="http://www.blackfriday.info/item/28036" target="_blank">Apple 32GB iPod Touch With Free $30 Gift Card</a> &#8211; $295.00 (Same price but you get the gift card)</li>
<li><a id="link-28024" rel="28024" href="http://www.blackfriday.info/item/28024" target="_blank">Westinghouse 32&#8243; LCD HDTV</a> &#8211; $246.00 (regular price $429.99) &#8211; 720p two HDMI inputs</li>
<li><a id="link-28075" rel="28075" href="http://www.blackfriday.info/item/28075" target="_blank">XBox 360 120GB Elite Game System Bundle w/$50 Gift Card</a> &#8211; $299.99 (Same price but you get the gift card)</li>
</ul>
</div>
<div><em><strong>BestBuy</strong> (<a id="x67h" title="Full Ad Here" href="http://www.blackfriday.info/sales/best-buy-black-friday-ad.html" target="_blank">Full Ad Here</a>)</em></div>
<ul>
<li><a id="link-28861" rel="28861" href="http://www.blackfriday.info/item/28861" target="_blank">Insignia Blu-ray Disc Player (Model # NS-BRDVD3) w/Superman Returns or Beetlejuice Bluray</a> &#8211; $99.99 (regular price $150) BD Live ready</li>
<li><a id="link-28859" rel="28859" href="http://www.blackfriday.info/item/28859" target="_blank">Sony BDP-S360 Blu-ray Disc Player</a> &#8211; $149.99 (regular price $200) BDLive Ready, 24FPS support</li>
<li><a id="link-28885" rel="28885" href="http://www.blackfriday.info/item/28885" target="_blank">Logitech Harmony 510 Advanced Universal Remote</a> &#8211; $39.99 (regular price $99)</li>
<li><a id="link-28966" rel="28966" href="http://www.blackfriday.info/item/28966" target="_blank">Western Digital 1.5TB My Book Home Edition External Hard Drive</a> &#8211; $119.99 (regular price $170)</li>
<li><a id="link-28813" rel="28813" href="http://www.blackfriday.info/item/28813" target="_blank">21 (DVD)</a> &#8211; $3.99 (regular price $9.99)</li>
<li><a id="link-28790" rel="28790" href="http://www.blackfriday.info/item/28790" target="_blank">The Terminator (Blu-ray)</a> &#8211; $7.99 (regular price $17.99)</li>
<li><a id="link-28798" rel="28798" href="http://www.blackfriday.info/item/28798" target="_blank">Gladiator (Blu-ray)</a> &#8211; $12.99 (regular price $30)</li>
<li><a id="link-28841" rel="28841" href="http://www.blackfriday.info/item/28841" target="_blank">Samsung 50&#8243; 1080p Plasma HDTV (Model # PN50B530S2F)</a> &#8211; $897.99 (regular price $1300) 3 HDMI inputs Energy Star Compliant</li>
<li>Panasonic Viera 50&#8243; 1080p Plasma HDTV (Model # TC-P50U1) w/Blu-ray Disc Player (Model # DMP-BD60K) &#8211; $999.98 (Blu Ray player $149.99 + $1300 regular price for TV)</li>
<li>Insignia 42&#8243; 1080p 120Hz LCD HDTV (Model # NS-L42Q120-10A) &#8211; $699.99 (Can get this at this price today online)</li>
<li><a id="link-28855" rel="28855" href="http://www.blackfriday.info/item/28855" target="_blank">Dynex 40&#8243; 1080p 60Hz LCD HDTV (Model # DX-L40-10A)</a> &#8211; $499.99 (regular price $550)</li>
</ul>
<div><em><strong>Sears</strong> (</em><a id="x0-y" title="Full Ad Here" href="http://www.blackfriday.info/sales/sears-black-friday-ad.html" target="_blank"><em>Full Ad Here</em></a><em>)</em></div>
<div>
<ul>
<li><em>Samsung Compact Full HD Camcorder &#8211; $399.99</em></li>
<li><a id="link-25585" rel="25585" href="http://www.blackfriday.info/item/25585" target="_blank">Samsung Blu-ray Disc Player (Model BD-P1600-A)</a> &#8211; $149.99 (regular price $199.99) BD Live ready, Netflix, Pandora</li>
<li><a id="link-25584" rel="25584" href="http://www.blackfriday.info/item/25584" target="_blank">Sony Blu-ray Disc Player (Model #BDPS360)</a> &#8211; $149.99 (regular price $180) BDLive Ready, 24FPS support</li>
<li><a id="link-25489" rel="25489" href="http://www.blackfriday.info/item/25489" target="_blank">Panasonic Blu-Ray Home Theater System (Model SC-BT200)</a> &#8211; $399.99 (regular price $499.99)</li>
<li><a id="link-25580" rel="25580" href="http://www.blackfriday.info/item/25580" target="_blank">Panasonic 54&#8243; Plasma HDTV (Model #TC-P54G10)</a> &#8211; $1499.99 (regular price $1699.99) THX Certified, 1080p, 24fps, VIERA Cast Support, 3 HDMI</li>
<li><a id="link-25529" rel="25529" href="http://www.blackfriday.info/item/25529" target="_blank">Sony 46&#8243; LCD HDTV (Model KDL46V5100)</a> &#8211; $1239.99 (regular price $1599.99) 1080p, 120Hz, 4 HDMI</li>
<li><a id="link-25554" rel="25554" href="http://www.blackfriday.info/item/25554" target="_blank">Toshiba 40&#8243; 1080P Class LCD HDTV (Model 40RV525U/R)</a> &#8211; $589.99 (regular price $800) 60 Hz, 3 HDMI inputs</li>
</ul>
</div>
<div><em><strong>Meijer</strong> (<a id="g1v1" title="Full Ad Here" href="http://www.blackfriday.info/sales/meijer-black-friday-ad.html" target="_blank">Full Ad Here</a></em>)</div>
<ul>
<li>Sylvania Blu-ray Disc Player &#8211; $89.00</li>
</ul>
</div>
<p>&nbsp;</p>
<div><em><strong>Radio Shack</strong> (<a id="g6op" title="Full Ad Here" href="http://www.blackfriday.info/sales/radioshack-black-friday-ad.html" target="_blank">Full Ad Here</a>)</em></div>
<div>
<ul>
<li><a id="link-29984" rel="29984" href="http://www.blackfriday.info/item/29984" target="_blank">Samsung BD-1600A Blu-ray Disc Player</a> &#8211; $149.99 (regular price $199.99) BD Live and Netflix, Pandora</li>
<li><a id="link-29908" rel="29908" href="http://www.blackfriday.info/item/29908" target="_blank">Samsung 32&#8243; LCD HDTV</a> &#8211; $399.99 (regular price $499.99) 720p</li>
<li><a id="link-29934" rel="29934" href="http://www.blackfriday.info/item/29934" target="_blank">Xbox 360 Elite System w/$60 Cash Back &amp; 2 Games (Lego Batman and Pure)</a> &#8211; $299.99 (regular price $299.99)</li>
</ul>
</div>
<p>&nbsp;</p>
<div><em><strong>Wal*Mart</strong> (<a id="ai4x" title="Full Ad Here" href="http://www.blackfriday.info/sales/wal-mart-black-friday-ad.html" target="_blank">Full Ad Here</a>)</em></div>
<div>
<div>
<ul>
<li><a id="link-32851" rel="32851" href="http://www.blackfriday.info/item/32851" target="_blank">Magnavox NB500 Blu-ray Disc Player</a> &#8211; $78.00 (regular price $129) Basic Unit no BD Live or next gen audio</li>
<li><a id="link-32849" rel="32849" href="http://www.blackfriday.info/item/32849" target="_blank">Samsung BD-P1590 Blu Ray Player (Saturday)</a> &#8211; $148.00 (regular price $160) Looks like a custom model number for Wal*Mart. Similar features Radio Shack and others.</li>
<li>Tons of movies. Some DVDs as low as $2. Blu Rays as low as $10. Movies you&#8217;d even want to watch!</li>
<li><a id="link-32768" rel="32768" href="http://www.blackfriday.info/item/32768" target="_blank">Samsung 50&#8243; PN50B400 Plasma 720P HDTV (Saturday)</a> &#8211; $698.00 (regular price $1130)</li>
<li><a id="link-32773" rel="32773" href="http://www.blackfriday.info/item/32773" target="_blank">Emerson 32&#8243; LC320EMFX LCD 720P HDTV</a> &#8211; $248.00 (regular price $350) Low end TV for a kids room or den.</li>
<li>Samsung 46&#8243; LN46B500 LCD 1080P HDTV (Saturday) &#8211; $848.00 (Couldn&#8217;t find regular Wal*Mart price but online they go for about $950)</li>
<li>Sony Bravia 46&#8243; KDL465504 LCD 1080P HDTV &#8211; $798.00 (Couldn&#8217;t find comparable TV for pricing)</li>
</ul>
</div>
</div>
<p>&nbsp;</p>
<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2009-11-20.mp3">Download Episode #400</a></p>
  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>November 19, 2009 11:16 PM</b>
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
			<?=getComments(3384)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3384)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2009/11/hdtv-and-home-theater-podcast-podcast-400-black-friday-preview.php" type="text/javascript" charset="utf-8"></script>
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