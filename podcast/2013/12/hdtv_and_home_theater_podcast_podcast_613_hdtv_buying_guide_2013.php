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
		AND e.entry_id = 5170";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5170 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5170 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5170";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-613-hdtv-buying-guide-2013.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5170";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013</title>
	<meta name="keywords" content="picture quality, led hdtv, smart led, last year, frame design, led, hdtv, inch, ”, picture, get, quality, smart, year, ’s, color, last, screen, good, samsung, technology, design, images, full, value" />
	<meta name="description" content="If you happen to be lucky enough to have budget for a new HDTV this Christmas, but still aren&amp;acirc;��t sure which one to buy, we&amp;acirc;��ve got you covered. We&amp;acirc;��re back with another annual edition of the HDTV buying guide. Staying true to form, we&amp;acirc;��re going to break the sets down into categories by screen size, just like we did last year." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-613-hdtv-buying-guide-2013.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="If you happen to be lucky enough to have budget for a new HDTV this Christmas, but still aren&amp;acirc;��t sure which one to buy, we&amp;acirc;��ve got you covered. We&amp;acirc;��re back with another annual edition of the HDTV buying guide. Staying true to form, we&amp;acirc;��re going to break the sets down into categories by screen size, just like we did last year." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5170', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-613-hdtv-buying-guide-2013.php">HDTV and Home Theater Podcast - Podcast #613: HDTV Buying Guide 2013</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>December 13, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>HDTV Buying Guide 2013</h3>
<p dir="ltr">If you happen to be lucky enough to have budget for a new HDTV this Christmas, but still aren’t sure which one to buy, we’ve got you covered. We’re back with another annual edition of the HDTV buying guide. Staying true to form, we’re going to break the sets down into categories by screen size, just like we did last year.</p>
<p dir="ltr">For those who don’t still have last years buyer’s guide handy, here are few of the sets from last year along with their prices. It turns out waiting a year to buy a new TV doesn’t always allow you to stretch your budget any further.</p>
<ul>
<li>Toshiba 19” 720p LED for $129</li>
<li>RCA 32” 1080p LCD for $229</li>
<li>Sony BRAVIA 42” 1080p LED $548</li>
<li>VIZIO 60” 1080p 120Hz Razor LED Smart HDTV $999</li>
<li>Panasonic 65” 1080p Full HD 3D Plasma $2499</li>
</ul>
<p>&nbsp;</p>
<h4>Up to 32&#8243;</h4>
<p dir="ltr">Depending on your situation, a 32” TV could work as your primary screen. But more often, these screens work out great for a secondary viewing room like your kitchen, bedroom, office or home gym.  At this size, we’re focused on value and bang for the buck.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B009IBXEE6/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B009IBXEE6&amp;linkCode=as2&amp;tag=hdtvandhometh-20">VIZIO E241-A1 24-inch 1080p 60Hz Razor LED HDTV ($178)</a><br />
The 24” 1080p TV drops by $20 this year, or roughly 10%.  This model is part of the new E-series slim frame design from VIZIO, providing what they call high-quality design and picture at the best value. Images look good and the action does well on the 1080p Full HD resolution screen. The slim frame design makes it a good match for just about any room or situation. Plus, as an LED TV, it’ll save you money when compared to same size 2012 LCD HDTVs lighted with CCFL technology.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00A7BGORU/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00A7BGORU&amp;linkCode=as2&amp;tag=hdtvandhometh-20">oCOSMO 32-Inch 1080p 60Hz LED HDTV ($215)</a><br />
Unknown value brand oCOSMO bumps off last year’s RCA for our 32” recommendation. The oCOSMO has all the same features and a better price. It also reviews very highly at Amazon based on the impressions of 47 customers.  OK, so that isn’t a ton, but it helps. With the additional feature of a USB &amp; MHL ports helps further expand the functionality of your TV, allowing users to listen to music and view digital pictures quickly and conveniently.</p>
<p>&nbsp;</p>
<h4>Up to 42&#8243;</h4>
<p dir="ltr">This continues to be the sweet spot for TV sales, which makes it a very competitive category. You don’t have to look to hard to find some really good deals on very high quality televisions.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00BB0ZTJA/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00BB0ZTJA&amp;linkCode=as2&amp;tag=hdtvandhometh-20">LG Electronics 39LN5300 39-Inch LED-lit 1080p 60Hz TV ($347)</a><br />
We picked the LG for the value and the picture quality. It will do a good job for you without breaking the bank. the video quality is very good, but the audio performance is what you’d expect out of a TV speaker. It also offers easy self-calibration with on-screen reference points for key picture quality elements such as black level, color, tint, sharpness and backlight levels. This helps take the guesswork out of picture adjustments. It’s not a full calibration, but most people aren’t doing a full calibration on 39” TVs.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00CI3BIT4/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00CI3BIT4&amp;linkCode=as2&amp;tag=hdtvandhometh-20">VIZIO E420i-A0 42-Inch 1080p 120Hz Smart LED HDTV ($478)</a><br />
Last year’s 42” Sony cost $548. This year’s VIZIO drops that all the way down to $478 and even adds a few features for you. We aren’t huge fans of paying a premium for “smart” TV features, but when you get it for this price, why not? The built-in VIZIO Internet Apps allow you to enjoy online movies, TV shows, music, and apps without the need for an external box or dongle. It has  Smart Dimming capabilities for better contrast, richer colors and more vivid details. And it carries on the slim frame design VIZIO has been promoting, to add to the aesthetics acceptance factor.</p>
<p>&nbsp;</p>
<h4>Up to 50&#8243;</h4>
<p dir="ltr">Pricing in this category doesn’t move much from year to year. Last year this category has a plasma TV. This year, though it pains us to say it, we couldn&#8217;t do it.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00CSMAYO0/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00CSMAYO0&amp;linkCode=as2&amp;tag=hdtvandhometh-20">LG Electronics 47LN5790 47-Inch 1080p 120Hz Smart LED HDTV + Free 60-Watt 2-Channel Sound Bar ($614)</a><br />
If you aren’t planning to connect your 46 or 47-inch TV to a surround sound system, this deal is a great option.  You get the stunning picture quality of the LG television, and they’re throwing in a 60 watt, 2 channel sound bar for free to eliminate the negative experience associated with the built-in TV speaker. The price is ridiculously low, significantly lower than the 46-inch Samsung on last year’s list, and you get the soundbar. It also happens to be a Smart TV, so you get access to premium content providers like Netflix, Vudu, Hulu Plus, and YouTube direct from your TV.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B0074FGLUM/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0074FGLUM&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Samsung UN50EH5300 50-Inch 1080p 60Hz LED HDTV ($649)</a><br />
A 50-inch set is the primary viewing size for many households, and Samsung consistently makes some of the best televisions on the market. If you’re buying a main TV, don’t look for the cheapest, look for the best.  It turns out this Samsung is also a really good price, but even if it carries a small premium, its worth it.  You get everything except 3D, which in many circles is still considered everything. With this Smart HDTV, Smart Content provides new ways to explore and locate your favorite shows, movies, games, and more. A full web browser with WiFi built-in and innovative apps made for TV, along with Signature Services, enhances your enjoyment. AllShare Play allows you to stream content from other devices and enjoy it on the big screen. The Wide Color Enhancer Plus provides vibrant natural-looking images and it’s all in a sleek ultra slim design.</p>
<p>&nbsp;</p>
<h4>Greater than 50&#8243;</h4>
<p dir="ltr">These are the TVs everyone wants, the big ones you walk by in the store and drool over.  Drool no longer, just click “buy it now.”</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00A9HDXOY/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00A9HDXOY&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Samsung UN60EH6003 60-Inch 1080p 120Hz HDTV ($997)</a><br />
This 60” television from Samsung represents the ultimate intersection of performance and value. The picture quality is outstanding, it lacks a few features that the higher priced televisions have, but that’s what allows you to take it home, or put it under the tree, for less than one thousand dollars. Experience sharp picture quality, even when you are watching fast-moving images like sports or action movies. The CMR of 240 takes motion-clarity to the next level. Wide Color Enhancer Plus allows you to see picture color the way the director originally intended. Witness the entire RGB spectrum brought to life on your screen to bring you exceptionally vibrant, yet natural-looking images faithful to the director’s original intent.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00ES5YZBS/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00ES5YZBS&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Sony XBR55X850A 55-Inch 4K Ultra HD 120Hz 3D Internet LED UHDTV ($2998)</a><br />
Oh yeah, we went there. Sure it&#8217;s pricey, but you get bragging rights, and that’s gotta count for something. It has everything you could ask for to be the beast of the block. Of course, it’s a 4K Ultra HD set so you get four times the clarity of Full HD 1080p.  It will upconvert and enhance everything you watch into 4K for the ultimate HD experience. As a 4k set, you have the expanded colors and Sony&#8217;s unique TRILUMINOS display technology to take advantage of it.  To make sure its future proof, you get HDMI 2.0 ports that will supported 4K video formats if you can track down any content.</p>
<p>&nbsp;</p>
<h4>HT Guy&#8217;s Ultimate Christmas Present:</h4>
<p dir="ltr">Two years ago we showcased the <a href="http://www.amazon.com/gp/product/B004NPND20?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B004NPND20&amp;ref_=sr_1_1&amp;qid=1322753977&amp;sr=8-1">Panasonic VT30 65” Plasma at $3,000</a> as one of our ultimate presents.  And we also liked the <a href="http://www.amazon.com/gp/product/B005LYRYNG?ie=UTF8&amp;tag=hdtvpodcast-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B005LYRYNG&amp;ref_=sr_1_1&amp;s=electronics&amp;qid=1322754328&amp;sr=1-1">Sharp AQUOS 80&#8243; LED for $4,430</a>. Last year we picked the<a href="http://www.amazon.com/gp/product/B005MYZXS8/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B005MYZXS8&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Elite 60&#8243; 3d LED HDTV for $4599</a> and the gigantic <a href="http://www.amazon.com/gp/product/B0074FGZYO/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B0074FGZYO&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Samsung 75-Inch 1080p 240Hz 3D Slim LED HDTV, Gold for a measly $8997</a>. At the time, we considered it to be the most excessive TV possible.  This year, we’re going big and also going exotic. But we certainly aren’t going cheap.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00BWLJLD8/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00BWLJLD8&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Sharp LC-80LE757 80-inch Aquos Quattron 1080p 240Hz Smart LED 3D HDTV ($3688)</a><br />
To be perfectly honest, all we care about is the size. An 80” TV? That’s huge! But Sharp will tell you it isn’t just huge, it’s also a great television. It has the exclusive Quattron color technology that delivers a billion more colors, so you get a more powerful picture with brighter yellows, deeper blues, and richer golds. It supposedly creates a more realistic picture with greater detail and brightness &#8211; a feat that standard TV without Quattron can&#8217;t achieve. It has a 240Hz Refresh Rate with AquoMotion 480 so you can see sharper, more electrifying action with the most advanced panel refresh rates available today. AquoMotion, Sharp&#8217;s backlight scanning technology, doubles the effective refresh rate to hit you with all the power that fast-moving sports and movies can deliver. Sharp&#8217;s Smart TV features let you quickly connect to your favorite content and instantly access apps like Facebook, Twitter, and YouTube, plus streaming movies, music, games, and websites you love.</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00E5U3YEK/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00E5U3YEK&amp;linkCode=as2&amp;tag=hdtvandhometh-20">LG Electronics 55EA9800 Cinema 3D 1080p Curved OLED TV with Smart TV ($8999)</a><br />
Maybe we still don’t get the whole curved screen thing, but it’s OLED, and we get that for sure. The best picture quality available, hands down. Stunning design at only 4.3mm thick at its thinnest point. Stunning picture quality with Infinite Contrast that ranges from the most blazing white to the darkest black. And, 4 Color Pixel that displays images so vivid you&#8217;ll forget you are watching TV. LG&#8217;s advanced 4 Color Pixel technology adds an unfiltered, white sub-pixel to the traditional red, green and blue. The result? A brighter picture with a wider range of colors and superior color accuracy for more true to life and vibrant images. LG OLED TVs have an almost infinite contrast ratio. With self-lighting pixel technology, it can range from blazing white to the darkest black. Higher is better, and &#8220;Infinite&#8221; has been impossible. Until now.</p>
<p dir="ltr">We left off the <a href="http://www.amazon.com/gp/product/B00CMEN95U/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00CMEN95U&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Samsung UN85S9 85-Inch 4K Ultra HD 120Hz 3D Smart LED TV</a> for $39,997 because that’s too crazy. Even for us.</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2013-12-13.mp3">Download Episode #613</a></p><br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>December 13, 2013 12:45 AM</b>
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
			<?=getComments(5170)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5170)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-613-hdtv-buying-guide-2013.php" type="text/javascript" charset="utf-8"></script>
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