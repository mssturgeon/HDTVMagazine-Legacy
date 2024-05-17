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
		AND e.entry_id = 4980";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4980 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4980 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4980";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2013/01/vizio-reveals-expanded-2013-hdtv-collection-adding-ultra-hd-and-enhanced-smart-tvs-to-already-awardwinning-allled-hdtv-lineup.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4980";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up" />
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
	<title>HDTV Magazine - VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up</title>
	<meta name="keywords" content="led smart, razor led, retail price, – retail, price razor, led, smart, series, –, razor, retail, price, theater, new, ultra, hdtv, xvt, tvs, line, consumers, experience, picture, technology, design, hdtvs" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-ultra-hd.jpeg&quot; alt=&quot;VIZIO Ultra HD&quot; height=&quot;250&quot; width=&quot;250&quot;&gt;VIZIO, America's #1 large size HDTV company1, revealed today its expanded 2013 HDTV collection, highlighted by an all-new line of M-Series Razor LED&amp;trade; Smart TVs featuring an elegant, modern ultra-slim bezel and razor-thin profile. VIZIO's new HDTV line-up continues to demonstrate the brand's leadership in HDTV technology with advanced innovations like its first Ultra High Definition TVs (4K2K), part of the brand's flagship XVT-Series, as well as the all-new, all-LED2 E-Series line which features a sleek, slim-frame design and advanced Smart TV features and Theater 3D&amp;trade; available on select models.  Ranked &quot;Highest in Customer Satisfaction with HDTVs&quot; by J.D. Power and Associates3, VIZIO will showcase its new 22&quot; to 80&quot; sized collection at the 2013 Consumer Electronics Show (CES) in Las Vegas, NV.

VIZIO's new M-Series Razor LED Smart TVs are the must-have centerpieces for today's modern families. The all-new M-Series signature design  in screen sizes of..." />
	<meta name="title" content="VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2013/01/vizio-reveals-expanded-2013-hdtv-collection-adding-ultra-hd-and-enhanced-smart-tvs-to-already-awardwinning-allled-hdtv-lineup.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-ultra-hd.jpeg&quot; alt=&quot;VIZIO Ultra HD&quot; height=&quot;250&quot; width=&quot;250&quot;&gt;VIZIO, America's #1 large size HDTV company1, revealed today its expanded 2013 HDTV collection, highlighted by an all-new line of M-Series Razor LED&amp;trade; Smart TVs featuring an elegant, modern ultra-slim bezel and razor-thin profile. VIZIO's new HDTV line-up continues to demonstrate the brand's leadership in HDTV technology with advanced innovations like its first Ultra High Definition TVs (4K2K), part of the brand's flagship XVT-Series, as well as the all-new, all-LED2 E-Series line which features a sleek, slim-frame design and advanced Smart TV features and Theater 3D&amp;trade; available on select models.  Ranked &quot;Highest in Customer Satisfaction with HDTVs&quot; by J.D. Power and Associates3, VIZIO will showcase its new 22&quot; to 80&quot; sized collection at the 2013 Consumer Electronics Show (CES) in Las Vegas, NV.

VIZIO's new M-Series Razor LED Smart TVs are the must-have centerpieces for today's modern families. The all-new M-Series signature design  in screen sizes of..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4980', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2013/01/vizio-reveals-expanded-2013-hdtv-collection-adding-ultra-hd-and-enhanced-smart-tvs-to-already-awardwinning-allled-hdtv-lineup.php">VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 10, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=355&category=4K (Ultra HD)">4K (Ultra HD)</a></b>, <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">VIZIO Reveals Expanded 2013 HDTV Collection Adding Ultra HD and Enhanced Smart TVs to Already Award-Winning All-LED HDTV Line-Up</p>

<center><i>Three Product Lines Offer Consumers HDTVs Sized from 22" to 80", Featuring New Modern Industrial Design with Slim to Nearly Borderless Frames</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/vizio-ultra-hd.jpeg" alt="VIZIO Ultra HD" height="400" width="400"><strong>IRVINE, Calif. and LAS VEGAS, Jan. 7, 2013 /PRNewswire/</strong> -- VIZIO, America's #1 large size HDTV company1, revealed today its expanded 2013 HDTV collection, highlighted by an all-new line of M-Series Razor LED&trade; Smart TVs featuring an elegant, modern ultra-slim bezel and razor-thin profile. VIZIO's new HDTV line-up continues to demonstrate the brand's leadership in HDTV technology with advanced innovations like its first Ultra High Definition TVs (4K2K), part of the brand's flagship XVT-Series, as well as the all-new, all-LED2 E-Series line which features a sleek, slim-frame design and advanced Smart TV features and Theater 3D&trade; available on select models.  Ranked "Highest in Customer Satisfaction with HDTVs" by J.D. Power and Associates3, VIZIO will showcase its new 22" to 80" sized collection at the 2013 Consumer Electronics Show (CES) in Las Vegas, NV.</p>

<p>VIZIO's new M-Series Razor LED Smart TVs are the must-have centerpieces for today's modern families. The all-new M-Series signature design  in screen sizes of 32" (M321i), 40" (M401i), 47" (M471i), 50" (M501d), 55" (M551d), 60" (M601d), 65" (M651d), 70" (M701d) and 80" (M801d),pair superior picture quality with an ultra-slim frame for a more immersive viewing experience.  The ultra-thin bezels of the M-Series virtually vanish next to the near edge-to-edge glass screen for a signature look that updates any room's appearance.  Unique interior metal construction in the base and neck adds stability while a beautifully finished exterior complements the design and helps set the new standard for affordable HDTV luxury.</p>

<p>"The M-Series family utilizes a blend of thoughtful design detailing, minimalist construction and premium materials to create a line of HDTVs consumers will be proud to showcase in their homes," said Scott McManigal, VIZIO VP of Design.  "The sleek, near bezel-free design reduces distractions and enhances the rich picture quality allowing for a superior all-picture viewing experience."</p>

<p>Building on its leadership as the #1 brand in the U.S. for WiFi flat-panel TVs4, VIZIO's new M-Series Razor LED Smart TVs feature a best-in-class connected TV experience with the latest picture quality technologies.  Features like advanced local dimming, 240Hz refresh rate with Smooth Motion for the 50" and larger models and a brighter, flicker-free Theater 3D&trade; experience with lightweight, battery-free glasses, build on the M-Series franchise.  All of the new M-Series units offer VIZIO Internet Apps&reg; (V.I.A.) allowing consumers to enjoy their favorite apps like Amazon Instant Video, Crackle, Facebook&reg;, HuluPlus, M-Go, Netflix&reg;, Pandora&reg;, VUDU, YouTube&reg; and more.  The award-winning Smart TV platform offers the industry's easiest out-of-box experience, designed to allow users of all experience levels to quickly breeze through set-up and start watching TV or browse their favorite apps.  A new convenience feature for 2013, VIZIO's M-Series models will include Wi-Fi Direct universal remote controls with backlit keys, providing an intuitive way to control the entire home theater without nagging codes and programming.</p>

<p>"The new VIZIO M-Series line-up offers consumers a better entertainment experience with a clean, industrial design that now matches the superior picture quality and great Smart TV experience we are known for," said Matt McRae, VIZIO Chief Technology Officer. "The M-Series line is the perfect combination of leading technologies, highlighted by the easiest out-of-the-box experience, most popular TV apps on the market and an immersive, all picture viewing experience."</p>

<p>In addition to an enhanced line of M-Series Razor LED Smart HDTVs, VIZIO will also be unveiling its first Ultra HDTV offerings this year at CES. With four-times the resolution of Full HD 1080p, the premium XVT-Series Ultra HD Razor LED Smart TVs with Theater 3D offer the ultimate in resolution and picture quality. VIZIO will launch the 55" (XVT551d), 65" (XVT651d) and 70" (XVT701d) Ultra HD units later this year, making it one of the first technology innovators to bring the expensive new technology to mainstream consumers.</p>

<p>Featuring the same sleek, industrial design of VIZIO's new M-Series line, the new XVT-Series screen size classes of 55", 65" and 70" Ultra HDTVs also boast an ultra-slim frame and Razor LED technology, packing brilliant clarity, color and richer detail into the most immersive, all-picture viewing experience possible. VIZIO's Theater 3D technology included in the new XVT-Series takes movies, sports and gaming into a new dimension while an effective refresh rate of 240 Hz keeps even the most fast-moving pictures smooth and stable.</p>

<p>"As content creators continue to push the limits to produce ever-increasingly rich and intricate worlds for their stories, we're excited to be one of the first major brands to bring premium, Ultra HD resolution into the home," said Matt McRae, VIZIO Chief Technology Officer. "Ultra HD is the latest must-have upgrade for the home theater, giving discerning fans of our flagship XVT-Series access to cutting-edge technologies and an unprecedented high definition picture."</p>

<p>While the M- and XVT-Series will be unveiled at CES and available later in the year, VIZIO's popular entry-level E-Series line-up is available now and offers consumers an all-LED2 collection made affordable for every household.  Bringing superb picture quality to consumers at a great value, the E-Series HDTVs have already seen great success at retail with large screen sizes of 70" (E701i), 65" (E650i), 60" (E601i) and 55" (E551i), as well as smaller units in 50" (E500i and E500d), 42" (E420d), 39" (E390i), 29" (E291i), 24" (E241i) and 22" (E221) available to consumers. The sleek, slim-frame LED product line offers advanced local dimming technology on 42" screens and above, SRS StudioSound HD&trade;, a DTS technology, for clear audio and more HDMI inputs when compared to competitive brands, allowing for expanded entertainment options.</p>

<p>VIZIO's new 2013 HDTV collection will be on display at 2013 CES at the Wynn Hotel's Mouton Ballroom by appointment only.  The M-Series units will be available later this year and additional details on the XVT-Series Ultra HD Razor LED Smart TVs with Theater 3D to be announced closer to commercial availability.  Consumers can find the new all-LED E-Series line both in-store and online through key retailers such as Amazon, Best Buy, Costco, Sam's Club, Target, VIZIO.com and Walmart.   More information on VIZIO's CES announcements can be found at VIZIO.com/CES.</p>

<p><strong>VIZIO M-Series Razor LED Smart TVs</strong><br />
32" Razor LED Smart TV (M321i) – $399.99 Retail Price<br />
40" Razor LED Smart TV (M401i) – $529.99 Retail Price          <br />
47" Razor LED Smart TV (M471i) – $699.99 Retail Price                                                                  </p>

<p><strong>VIZIO M-Series Razor LED Smart TVs w/Theater 3D</strong><br />
50" Razor LED Smart TV w/Theater 3D (M501d) – $849.99 Retail Price<br />
55" Razor LED Smart TV w/Theater 3D (M551d) – $1,199.99 Retail Price<br />
60" Razor LED Smart TV w/Theater 3D (M601d) – $1,599.99 Retail Price<br />
65" Razor LED Smart TV w/Theater 3D (M651d) – $1,999.99 Retail Price<br />
70" Razor LED Smart TV w/Theater 3D (M701d) – $2,499.99 Retail Price<br />
80" Razor LED Smart TV w/Theater 3D (M801d) – $4,499.99 Retail Price      </p>

<p><strong>VIZIO XVT-Series Ultra HD Razor LED Smart TVs w/Theater 3D</strong><br />
55" Ultra HD Razor LED Smart TV w/Theater 3D (XVT551d) – To be announced closer to product availability<br />
65" Ultra HD Razor LED Smart TV w/Theater 3D (XVT651d) – To be announced closer to product availability<br />
70" Ultra HD Razor LED Smart TV w/Theater 3D (XVT701d) – To be announced closer to product availability</p>

<p><strong>VIZIO E-Series Razor LED TV</strong><br />
22" Razor LED TV (E221) – $159.99 Retail Price</p>

<p><strong>VIZIO E-Series Razor LED Smart TVs</strong><br />
24" Razor LED Smart TV (E241i) – $199.99 Retail Price<br />
29" Razor LED Smart TV (E291i) – $259.99 Retail Price           <br />
40" Razor LED Smart TV (E401i) – $529.99 Retail Price           <br />
55" Razor LED Smart TV (E551i) – $879.99 Retail Price<br />
60" Razor LED Smart TV (E601i) – $999.99 Retail Price<br />
70" Razor LED Smart TV (E701i) – $1,999.99 Retail Price</p>

<p><strong>VIZIO E-Series LED TVs</strong><br />
32" LED TV (E320) – $269.99 Retail Price<br />
37" LED TV (E370) – $349.99 Retail Price<br />
42" LED TV (E420) – $449.99 Retail Price<br />
47" LED TV (E470) – $549.99 Retail Price</p>

<p><strong>VIZIO E-Series LED Smart TV</strong><br />
32" LED Smart TV (E320i) – $299.99 Retail Price<br />
39" LED Smart TV (E390i) – $399.99 Retail Price<br />
42" LED Smart TV (E420i) – $499.99 Retail Price<br />
47" LED Smart TV (E470i) – $599.99 Retail Price<br />
50" LED Smart TV (E500i) – $699.99 Retail Price<br />
65" LED Smart TV (E650i) – $1,299.99 Retail Price</p>

<p><strong>VIZIO E-Series LED Smart TV w/Theater 3D</strong><br />
42" LED Smart TV w/Theater 3D (E420d) – $529.99 Retail Price<br />
50" LED Smart TV w/Theater 3D (E500d) – $729.99 Retail Price</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc. is headquartered in Irvine, California. In Q2 2007, VIZIO skyrocketed to the top by becoming the #1 shipping brand of flat panel HDTVs in North America and in Q3 2007 became the first American brand in over a decade to lead in U.S. LCD HDTV shipments. Since 2007 VIZIO LCD HDTV shipments remain in the top ranks in the U.S. and were #1 for the total year in 2009 and 2010. In Q4 2012, VIZIO became America's #1 60+ inch HDTV company based on units sold. VIZIO is the #1 Sound Bar Company and is a top rated sound bar brand 2-years running by leading consumer publications. As VIZIO celebrates its 10-Year Anniversary, it remains committed to what it does best, focus on the consumer to deliver visionary products at a great value. VIZIO offers a broad range of award winning consumer electronics that now include PCs and mobility products.  VIZIO's products are found at Amazon, Best Buy, BJ's Wholesale, Costco Wholesale, Sam's Club, Target, Walmart and other retailers nationwide. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics and made the lists of Ad Age's Hottest Brands, CNET's Editor's Choice, CNET Best of CES 2011 - Television, Home Theater Magazine's Top Picks, Good Housekeeping's Best Big-Screens, PC World's Best Buy and Popular Mechanics Editor's Choice among many other prestigious honors. For more information, please call 888-VIZIOCE or visit <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>&copy; 2013 VIZIO,Inc.  The V, VIZIO, Thin + Light and all names, logos and phrases are registered or unregistered trademarks of VIZIO, Inc.  All other trademarks are the property of their respective owners.  All rights reserved.</p>

<p>1Source: The NPD Group's Retail Tracking Service based on units sold November 2012 – 60"+ Flat Panel HDTVs.</p>

<p>2 VIZIO's new E-Series LED backlit LCD TV line features edge-lit and direct-lit LED technologies.</p>

<p>3 J.D. Power and Associates 2012 High Definition Television (HDTV) Satisfaction Report. Report based on responses from 1,009 consumers measuring 8 brands and measures opinions of consumers who purchased an HDTV in the last 12 months. Proprietary study results are based on experiences and perceptions of consumers surveyed August 2012. Your experiences may vary. Visit <a target="_blank" href="http://www.jdpower.com/">www.jdpower.com</a>.</p>

<p>4 The NPD Group's Retail Tracking Service based on units sold January – Nov 2012 – Total WiFi Flat Panel HDTVs</p>

<p>SOURCE VIZIO</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 10, 2013  1:29 PM</b>
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
			<?=getComments(4980)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4980)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2013/01/vizio-reveals-expanded-2013-hdtv-collection-adding-ultra-hd-and-enhanced-smart-tvs-to-already-awardwinning-allled-hdtv-lineup.php" type="text/javascript" charset="utf-8"></script>
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