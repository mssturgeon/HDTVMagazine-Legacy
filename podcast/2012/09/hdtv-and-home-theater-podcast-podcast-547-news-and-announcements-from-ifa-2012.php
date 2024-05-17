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
		AND e.entry_id = 4893";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4893 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4893 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4893";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/09/hdtv-and-home-theater-podcast-podcast-547-news-and-announcements-from-ifa-2012.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4893";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012</title>
	<meta name="keywords" content="consumer electronics, hybrid tablets, show berlin, dual core, inch model, ifa, inch, windows, show, tablets, new, sony, —, ’s, tvs, year, most, model, berlin, samsung, microsoft, world, even, company, display" />
	<meta name="description" content="The annual IFA show in Berlin, dubbed &amp;acirc;��Consumer Electronics Unlimited,&amp;acirc;�� is the second largest consumer tech show in the world - second only to CES in Las Vegas. They are expecting to set new attendance records this year, surpassing the more than 1,400 exhibitors and 239,000 attendees that showed up last year.  Berlin is a bit too far for us to drive, so we have to cover the show virtually.  IFA, or Internationale Funkausstellung, has been around since 1924 and was originally an international radio exhibition." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/09/hdtv-and-home-theater-podcast-podcast-547-news-and-announcements-from-ifa-2012.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The annual IFA show in Berlin, dubbed &amp;acirc;��Consumer Electronics Unlimited,&amp;acirc;�� is the second largest consumer tech show in the world - second only to CES in Las Vegas. They are expecting to set new attendance records this year, surpassing the more than 1,400 exhibitors and 239,000 attendees that showed up last year.  Berlin is a bit too far for us to drive, so we have to cover the show virtually.  IFA, or Internationale Funkausstellung, has been around since 1924 and was originally an international radio exhibition." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4893', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/09/hdtv-and-home-theater-podcast-podcast-547-news-and-announcements-from-ifa-2012.php">HDTV and Home Theater Podcast - Podcast #547: News and Announcements from IFA 2012</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September  7, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>, <b><a href="/category.php?id=396&category=Global & Worldview">Global & Worldview</a></b>
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
<h3>News and Announcements from IFA 2012</h3>
<p>The annual <a href="http://b2b.ifa-berlin.com/en/">IFA show</a> in Berlin, dubbed “Consumer Electronics Unlimited,” is the second largest consumer tech show in the world &#8211; second only to CES in Las Vegas. They are expecting to set new attendance records this year, surpassing the more than 1,400 exhibitors and 239,000 attendees that showed up last year.  Berlin is a bit too far for us to drive, so we have to cover the show virtually.  IFA, or Internationale Funkausstellung, has been around since 1924 and was originally an international radio exhibition.</p>
<p>As we weren’t at the show, we have to report what others are reporting.  The following is a set of excerpts from various articles and posts from around the world wide web.  They are not our writing nor our words, all credit goes to the original authors.  Each article is linked to if you’d like to read more or find out more about the author.</p>
<p><a href="http://www.pcworld.com/article/261826/4k_tvs_hybrid_tablets_top_trends_at_ifa_in_berlin.html"><strong>4K TVs, Hybrid Tablets Top Trends at IFA in Berlin</strong></a><br />
<em>This year at the IFA consumer electronics show in Berlin vendors introduced and demonstrated a plethora of ultra-high resolution TVs, hybrid tablets based on Microsoft&#8217;s upcoming operating systems, as well as the first device based on Windows Phone 8.</em></p>
<p><em>Here are some of most interesting trends at IFA 2012 and the products they have spawned:</em></p>
<p><em><strong>Hybrid Tablets</strong></em><br />
<em>IFA 2012 was definitely the coming out party for tablets based on Microsoft&#8217;s upcoming Windows 8 and Windows RT operating systems, which have been developed with tablets in mind. The Windows 8 tablets use Intel processors and the Windows RT devices use processors from ARM.</em></p>
<p><em><strong>OLED</strong></em><br />
<em>At IFA, LG and Samsung showed based on the technology. Both are capable of reproducing 3D content. Using a feature called Multi View, as well as glasses with integrated earphones, two people can watch two different shows at the same time on Samsung&#8217;s ES9500.</em></p>
<p><em>On the show floor both sets produced great images, but as has been the case since OLED TVs first arrived the improvements come at a steep cost. LG&#8217;s new OLED model costs around 9000 euros (US$11,290) in Europe.</em></p>
<p><em><strong>NFC</strong></em><br />
<em>Integrated NFC (Near-Field Communications) is showing up in an increasing number of products including, at IFA, Samsung&#8217;s ATIV Tablet, Sony&#8217;s Xperia T smartphone and the Vaio Duo 11 hybrid tablet and Asus&#8217; Vivo Tab.</em></p>
<p><em>But the technology hasn&#8217;t necessarily been included for making payments, which is the area that has received the most attention. Sony users will be able to touch their phone to new NFC-equipped speakers and headphones so that music jumps from playing on the device to the speakers or headphones. </em><a href="http://www.pcworld.com/article/261826/4k_tvs_hybrid_tablets_top_trends_at_ifa_in_berlin.html">more&#8230;</a></p>
<p><a href="http://www.extremetech.com/electronics/135327-ifa-move-over-3d-its-time-for-4k-uhdtv"><strong>IFA: Move over 3D, it’s time for 4K UHDTV</strong></a><br />
<em>After five years of trying to convince us that 3D TVs are the future, it seems TV makers are finally ready to move on — to 4K UHDTV. At the IFA consumer electronics show in Berlin, Sony, Toshiba, and LG are all showing off 84-inch 4K (3840×2160) TVs. These aren’t just vaporware, either: LG’s TV is on sale now in Korea (and later this month in the US), Sony’s is due later this year, and Toshiba will follow in the new year.</em></p>
<p><em><strong>LG</strong></em><br />
<em>LG actually debuted its 4K TV back at CES in January, but it’s back at IFA with a launch date (September), a price ($22,000), a model number (84LM9600), and this time the company is actually letting people play with the set. Beyond its size and resolution, there’s plenty of connectivity down the side (HDMI and USB ports up the wazoo), passive 3D (and 2D-to-3D conversion), built-in WiFi, and a slew of other top-end features.</em></p>
<p><em>In general, consumers and reporters at IFA all seem to say the same thing about LG’s 84-inch TV: It only really comes into its own when you get really close — close enough that all you can see is the TV (about five feet). Remember, despite having 3840×2160 (8.2 million pixels) — four times the resolution of 1920×1080 — the pixel density is still very low (54 PPI, vs. the 200-300 PPI found on modern mobile displays). An 84-inch 4K TV only has a slightly higher pixel density than a 50-inch 1080p TV (44 PPI).</em></p>
<p><em>Curiously, a few people are reporting that the TV seems to have very poor horizontal viewing angles (and the LG site doesn’t even list the viewing angles, which is usually a bad sign).</em></p>
<p><em><strong>Sony</strong></em><br />
<em>Sony, never one to be out done on features, has decided that its 84-inch 4K UHDTV will debut with a built-in 10-speaker 50-watt sound system, built-in WiFi, and Sony’s Entertainment Network, which provides access to Netflix, Pandora, YouTube, Skype, and other web services. The whole thing weighs a mind-blowing 176 pounds (80 kilos).</em></p>
<p><em>Like LG, Sony’s XBR-84X900 (Sony sure loves its memorable model numbers) supports passive 3D at 4K resolutions, and for PlayStation 3 owners there’s SimulView, which allows two gamers to play a game at 1080p without split screen (using polarized glasses).</em></p>
<p><em>There’s no word on pricing — but it’ll probably be at least $25,000 when it launches “some time this year.”</em></p>
<p><em><strong>Toshiba</strong></em><br />
<em>The Toshiba 84-inch 4K TV, with an iPhone next to it for scale (Credit: The Verge)</em><br />
<em>We don’t know much about Toshiba’s 84-inch 4K display, other than the fact that it’s coming some time in 2013. Judging by the photos, Toshiba’s unit is sleeker than Sony’s TV, but not quite as svelte as LG’s. There aren’t any built-in speakers — but really, if you’re going to spend $20k on a TV, does Sony really think that you won’t also have a proper cinema-grade surround sound setup?</em></p>
<p><em>Our best bet is assume that the 84-inch model has the same features as Toshiba’s smaller, already-launched 55-inch 4K TV. The 55ZL2 supports glasses-free 3D through lenticular lenses, which direct redirect 3D imagery to different locations (i.e. different seats on the sofa). The 55ZL2 also has the ability to play video from online sources, but most reviews suggest that Toshiba’s offering pales in comparison to Sony’s, or indeed a $99 media streamer.</em></p>
<p><em>Perhaps most worryingly, the 55ZL2 only accepts 4K video input through Toshiba’s proprietary “digital serial port” — and the only device that outputs to a digital serial port is Toshiba’s own professional, very expensive media servers. Hopefully the 84-inch model will accept 4K over HDMI, like the Sony and LG UHDTVs.</em></p>
<p><em><strong>84 inches? How about 145?</strong></em><br />
<em>IFA has produced two other TVs that are very interesting, both from Panasonic: a 145-inch 8K UHDTV, and a 20-inch 4K monitor. Created in collaboration with NHK, the Japanese broadcaster that originated the 8K UHDTV (Super Hi-Vision) transmission standard, the 145-inch Panasonic plasma TV is by far the largest high-resolution display in the world. There is no word on price or availability.</em></p>
<p><em>Perhaps the most interesting monitor on display at IFA is Panasonic’s 20-inch 4K, which clocks in at an amazing 216 PPI. All reports suggest that these could be the most beautiful desktop displays ever, but again we don’t have a price or availability. Generally, these high-res displays are targeted at specialist applications, though, like medicine — so expect them to start at $5,000.</em></p>
<p><em>Finally, a friendly reminder: While a 4K monitor or TV sounds like a good idea, bear in mind that there’s almost zero 4K content on the market — and short of spending a thousand bucks on a monstrous video card setup, nothing that will even come close to rendering a game at 3840×2160. There isn’t a 4K Blu-ray standard, and 4K broadcast TV transmission is still very much in its infancy.</em></p>
<p><em>As always, though, it’s a case of build it and he will come — so if you have $20,000 kicking around, please blaze the trail for us mere mortals who have been stuck at 1080p for a decade.</em><a href="http://www.extremetech.com/electronics/135327-ifa-move-over-3d-its-time-for-4k-uhdtv">more&#8230;</a></p>
<p><a href="http://www.pcmag.com/slideshow/story/302202/the-top-products-at-ifa-2012"><strong>The Top Products at IFA 2012</strong></a><br />
<em>Formerly a low-key, European-focused home appliance-centric show, this year&#8217;s IFA was a nonstop riot of Windows 8 tablets, hot smartphones and huge, gorgeous TVs.</em></p>
<p><em>We found a few trends on the floor in Berlin. Convertible Windows 8 tablets that double as laptops were everywhere. We also saw several Windows 8 all-in-one desktop PCs with touch screens. Windows 8&#8242;s new user interface is designed for touch screens, and Microsoft&#8217;s partners got the message: They want you to manhandle your PC.</em></p>
<p><em>Windows 8 manufacturers are also experimenting like mad with touch-screen shapes and sizes. Sony&#8217;s gigantic VAIO Tap 20, for instance, is basically a desktop PC that detaches to become a home version of a Microsoft Surface table. Will this work? We don&#8217;t know, but we&#8217;re happy to try and find out.</em></p>
<p><em>For TVs, 4K is the new HD. Ultra high definition 4K TV doubles the standard 1080p resolution both vertically and horizontally, making the pixels practically invisible even on relatively large panels at relatively short distances. 4K really comes into its own on huge screens, so Sony, LG and Toshiba all debuted 84-inch models. While they&#8217;re completely unaffordable at $20,000 and up, this is the future—even if there isn&#8217;t any 4K content yet.</em></p>
<p><em>Samsung, the world&#8217;s largest consumer electronics company, is a true innovator, with some wild products that define new markets. The company debuted the world&#8217;s first Windows 8 phone and a large-screen, Android-powered camera, and extended the success of its Galaxy Note &#8220;phablet&#8221; with the 5.5-inch Galaxy Note II.</em></p>
<p><em>We did find two trends at the show that we&#8217;re not fans of. There aren&#8217;t any Windows RT tablets in this list, even though there were several announced at IFA. We think there are just too many questions around Microsoft&#8217;s mysterious ARM-based OS, starting with whether any third-party apps will be available for it. Microsoft has remained disturbingly silent about RT even while it&#8217;s been promoting Windows 8. There also aren&#8217;t any laptops with 21-by-9 screens, an awkward layout that makes most video content look pretty bad.</em><br />
<em>Check out the slideshow for our favorite finds at IFA. </em><a href="http://www.pcmag.com/slideshow/story/302202/the-top-products-at-ifa-2012">more&#8230;</a></p>
<p><a href="http://www.digitalspy.com/tech/news/a403478/eye-controlled-television-unveiled-at-ifa-2012.html"><strong>Eye-controlled television unveiled at IFA 2012</strong></a><br />
<em>Chinese electronics firm Haier is launching a line of futuristic television sets controlled by users&#8217; eye movements.</em></p>
<p><em>The Gaze TV devices, on show at the IFA electronics trade show, are powered by eye-tracking technology developed by Swedish company Tobii.</em></p>
<p><em>During the Berlin event, it was demonstrated how users will be able to call up menus by staring at specific parts of the screen, select icons by gazing directly at them, and alter the volume by looking up and down. </em><a href="http://www.digitalspy.com/tech/news/a403478/eye-controlled-television-unveiled-at-ifa-2012.html">more&#8230;</a></p>
<p><a href="http://www.cbsnews.com/8301-501465_162-57502788-501465/samsung-ifa-2012-first-windows-8-smartphone-slate-tablet-laptop-hybrid/"><strong>Samsung IFA 2012: First Windows 8 smartphone, Slate tablet-laptop hybrid</strong></a><br />
<em>Samsung announced a bevy of new devices Wednesday at the Internationale Funkausstellung (IFA 2012) trade show in Berlin. Along with the Galaxy Note II, the company unveiled the ATIV S smartphone, ATIV Tab, Series 7 Slate PC and Series 5 Ultra touch.</em></p>
<p><em>Samsung&#8217;s ATIV S is the very first Microsoft Windows 8 device. The smartphone features a 4.8-inch HD display, 1.5 gigahertz dual-core processor, HSPA+ support, 1.9-megapixel front-facing camera and a Windows 8 mobile operating system. </em><a href="http://www.cbsnews.com/8301-501465_162-57502788-501465/samsung-ifa-2012-first-windows-8-smartphone-slate-tablet-laptop-hybrid/">more&#8230;</a></p>
<p><a href="http://hd.broadcastnewsroom.com/article/Dune-HD-Announces-Worlds-Smallest-Full-HD-Media-Player-at-IFA--2175651"><strong>Dune HD Announces World&#8217;s Smallest Full HD Media Player at IFA</strong></a><br />
<em>As part of its continuing innovation, Dune HD today announced the Dune HD Connect, the world&#8217;s smallest Full HD media player. Designed to convert existing dumb screens into Smart TVs, the Dune HD Connect comes in a compact stick format that simply plugs into your TV&#8217;s HDMI socket to enable movies, TV and other content to be streamed from home networks or the internet through its built-in Wi-Fi. The options of cable or terrestrial digital TV tuners further increase the content choices. </em><a href="http://hd.broadcastnewsroom.com/article/Dune-HD-Announces-Worlds-Smallest-Full-HD-Media-Player-at-IFA--2175651">more&#8230;</a></p>
<p><a href="http://www.mobilemag.com/2012/09/04/ifa-2012-maxell-shows-off-two-new-budget-tablets/"><strong>IFA 2012: Maxell Shows Off Two New Budget Tablets</strong></a><br />
<em>Maxell is often a name that conjures up thoughts of old-school VHS tapes and CD-Rs for burning. The truth is that the company does a whole lot more than that, as was shown at IFA 2012 this year. The most exciting items shown off were two budget tablets, both under $300.</em></p>
<p><em>The models are known as the MaxTab H10 and MaxTab H8, and are rather similar, besides screen size and a few extra tweaks. The 9.7-inch model (H10) features a 1280×768 display, runs Android 4.0.4, and has a dual-core ARM Cortex A9 1.5 processor with a dual-core Mali 400 GPU. There’s also 4GB of storage with microSD for expansion, 1GB of DDR3 RAM, mini-USB, HDMI out, a 2MP front-facing cam and a 5MP on the back. The H10 also has Wifi and bluetooth and is compatible with a 3G dongles. </em><a href="http://www.mobilemag.com/2012/09/04/ifa-2012-maxell-shows-off-two-new-budget-tablets/">more&#8230;</a></p>
<p><a href="http://connecteddigitalworld.com/2012/09/04/ifa-2012-elgato-announces-updates-to-eye-tv/"><strong>IFA 2012: Elgato Announces Updates to Eye-TV</strong></a><br />
<em>Elgato today unveiled the new EyeTV Mobile, a redesigned, even smaller model of its highly acclaimed DVB-T TV Tuner designed to fit the iPad’s and iPhone’s dock connector. With the IFA 2012 launch of the new EyeTV Mobile comes the announcement of EyeTV Micro, an ultra-compact DVB-T TV Tuner for Android smartphones and tablets. Also on display at this years IFA is the Game Capture HD, Elgato’s solution for recording and sharing PlayStation or Xbox gameplay. </em><a href="http://www.pcworld.com/article/261826/4k_tvs_hybrid_tablets_top_trends_at_ifa_in_berlin.html">more&#8230;</a></p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-09-07.mp3">Download Episode #547</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September  7, 2012 12:03 AM</b>
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
			<?=getComments(4893)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4893)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/09/hdtv-and-home-theater-podcast-podcast-547-news-and-announcements-from-ifa-2012.php" type="text/javascript" charset="utf-8"></script>
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