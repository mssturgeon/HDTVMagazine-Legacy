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
		AND e.entry_id = 5080";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5080 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5080 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5080";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-in-the-wake-of-ces-2013-thoughts-and-afterthoughts.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5080";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts" />
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
	<title>HDTV Magazine - HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts</title>
	<meta name="keywords" content="cheap anyone, anyone make, hardware cheap, inch inch, per diagonal, inch, ’s, tvs, make, lcd, –, even, less, prices, plasma, cheap, buy, price, hardware, anyone, shows, flash, per, time, phone" />
	<meta name="description" content="Sometimes you need a breather to appreciate how topsy-turvy the world of consumer electronics has become." />
	<meta name="title" content="HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-in-the-wake-of-ces-2013-thoughts-and-afterthoughts.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sometimes you need a breather to appreciate how topsy-turvy the world of consumer electronics has become." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5080', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-in-the-wake-of-ces-2013-thoughts-and-afterthoughts.php">HDTV Expert - In The Wake of CES 2013: Thoughts and Afterthoughts</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>February 14, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=514&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<p>It’s just over a month since the International CES, otherwise known as the world’s largest orgy of consumer electronics. Some folks are even jokingly calling it the “Chinese Electronics Show,” after the strong showing by mainland Chinese manufacturers.</p>
<p>I can tell you that, after sorting through over 1,200 photos and videos, I’m still discovering things I photographed in the Las Vegas Convention Center. And there have been plenty of product announcements since the show, not to mention some shifts in power among CE manufacturers.</p>
<p>Each year, I present on future trends in technology at InfoComm. I also travel around and offer a condensed version of this talk for dealer and distributor line shows, professional society meetings, and even for a local amateur radio club.</p>
<p>As you might imagine, the content of the talk is updated frequently. What I present in two weeks at the local chapter meeting of SCTE will look and sound quite a bit different by the time I get to Orlando in mid-June. But that’s the nature of the beast – there is nothing so constant in the world of electronics as change.</p>
<p>Even so, there are a few clear trends that aren’t likely to change in the near future. And the most important trend, one which underlies everything else, is this: <i>Hardware is cheap, and anyone can make it.</i></p>
<p>&nbsp;</p>
<div id="attachment_2865" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2865" alt="This ad (and the gentleman sitting below it) pretty much sum up where CES is headed: A plethora of super-cheap products from companies you never heard of." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Gadmei-Cheap-Tablet-Ad-PPT.jpg" width="600" height="727" /><p class="wp-caption-text">This ad (and the gentleman sitting below it) pretty much sum up the future of CES: Walking through endless aisles of generic, super-cheap products from companies you never heard of until your legs give out from exhaustion&#8230;</p></div>
<p>&nbsp;</p>
<p>Think about it – you can buy a 60-inch plasma TV for less than $1,000, and that’s an everyday price. Want a nice Android tablet? You can pick them up for under $300. Blu-ray players with WiFi connectivity are now available for $70. And Roku’s XD Internet video set-top box (HD playback) is also ticketed at the same price.</p>
<p>Heck, you can buy an 80-inch LCD TV for less than $4,000. And that size and price combination has put a good portion of the front projector market in jeopardy. I won’t rehash previous columns here; suffice it to say that consultants, dealers, and systems integrators are putting these big screens in everywhere, and tearing out a lot of perfectly-good projector/screen combinations along the way.</p>
<p>But the low prices on the 80-inch Sharp TV are due to (a) excess fab capacity at Sharp’s Gen 10 Sakai LCD plant in Japan, and (b) the fact that Sharp is teetering on the verge of bankruptcy. Hence; the company is pushing the daylights out of large LCD TV and monitor sales at unbelievably low prices (less than $50 per diagonal inch).</p>
<p>Sharp also has a 90-inch product in their line, and anecdotal evidence shows that dealers are buying them for about $8,000 a pop. The 80-inch and 90-inch products are quite popular in two-up, side-by-side installations for videoconferencing and graphics display. And now China is getting into the game, showing 110-inch glass cuts made in Shenzen and resold by (among other brands) Samsung and Westinghouse. No one could have forseen nor desired this rapid drop in prices for LCD displays, particularly when the worldwide market for TVs is in decline.</p>
<p><i>Hardware is cheap, and anyone can make it.</i></p>
<p>&nbsp;</p>
<div id="attachment_2866" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2866" alt="Even at $269 (16 GB version), Barnes &amp; Noble isn't selling anywhere near as many of these as they expected." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Nook-HD+-PPT.jpg" width="600" height="474" /><p class="wp-caption-text">Even at $269 (16 GB version), Barnes &amp; Noble isn&#8217;t selling anywhere near as many of these as they expected.</p></div>
<p>&nbsp;</p>
<p>The other day, I was shopping in Best Buy and came across a special on USB flash drives (also known as “thumb” drives). SanDisk, celebrating its 25<sup>th</sup> year in business, was offering 8 gigabyte (GB) flash drives for $6 a pop – no coupons or rebates necessary. 16 GB models had a price tag of $10, and 32 GB drives could be scooped up for $20 apiece.</p>
<p>Believe it or now, flash drive capacity has blown past actual demand. With more and more people storing photos and documents “in the cloud,” there’s less of a need for portable flash memory.</p>
<p>Even so, it will take a long time to fill up a 32 GB flash drive. My 1,200+ photos and videos from CES needed about 3 GB of space on the 32 GB SD card installed in my Nikon CoolPix 8200 camera.</p>
<p>I bought a Barnes &amp; Noble Nook HD+ tablet in December, and fitted it with a 32 GB Micro SD card.  That is a LONG way from filling up – the only files that take up any sizable room are HD movies I download for rentals (about 6 – 7 GB per movie).</p>
<p>You can buy 64 GB and even 128 GB flash drives now at reasonable prices. For those crazy enough to want one, you can pick up a 500 GB thumb drive for about $300 now. Of course, you can also purchase a 1 TB Western Digital MyBook for backups at a cost of just $129.95, or a Toshiba 2 TB portable HDD for less than $200.</p>
<p><i>Hardware is cheap, and anyone can make it.</i></p>
<p>&nbsp;</p>
<div id="attachment_2867" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2867" alt="Dock an Asus smart phone in the back of this tablet, and you have an 8-inch phone/tablet, or &quot;phablet&quot; - a great example of a market-disrupting, multifunction CE product." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Asus-Phablet-Demo-PPT.jpg" width="600" height="437" /><p class="wp-caption-text">Plug an Asus smart phone in the back of this tablet, and you have an 8-inch HD phone/tablet, or &#8220;phablet&#8221; &#8211; a great example of a market-disrupting, multifunction CE product.</p></div>
<p>&nbsp;</p>
<p>The trend towards multifunction CE devices has also put a few product categories on the endangered species list. Shipments of point-and-shoot and DSLR camera declined markedly in 2011 when compared to 2010, a trend that is expected to repeat when 2012’s numbers are tallied.</p>
<p>The culprit? Mobile phones and tablets. Sure, they don’t have optical zoom lenses. And their image resolution still isn’t on a par with the best DSLRs and point-and-shoots. But that makes no difference to the average consumer, who is often pleasantly surprised to see just how well his or her smart phone takes HD-resolution pictures.</p>
<p>Last year, Canon and Nikon even introduced several models of DSLRs and pocket cameras with built-in WiFi and the Android operating system, just so people could take photos and instantly share them with friends. As far as I can tell, these products aren’t doing much to stem the decline in camera sales. After all, you can’t make phone calls or send texts with these cameras.</p>
<p>Nonetheless, prices for cameras have dropped to all-time lows. A nice compact point-and-shoot can be yours for less than $100, while a 16 megapixel model with 14x optical zoom and the ability to shoot 1080p/30 videos will run about $200. (As a point of reference, Canon’s first 5D-series DSLRs could shoot 3 frames per second in 2005 and cost $3,300.)</p>
<p><i>Hardware is cheap, and anyone can make it.</i></p>
<p>&nbsp;</p>
<div id="attachment_2869" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2869" alt="Who needs a dedicated game controller? Just use your smart phone, through its MHL connector, to do the job." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/SI-Phone-Controller-Demo-MHL-PPT.jpg" width="600" height="340" /><p class="wp-caption-text">Who needs a dedicated game controller? Just use your smart phone, through its MHL connector, to do the job.</p></div>
<p>&nbsp;</p>
<p>Even though consumers haven’t swarmed to “smart” TV functions, they do like their streaming – and Netflix is now the largest pay TV system operator in the United States, with over 25 million subscribers (yes, more than Comcast). With an ever-increasing number of viewers watching video on tablets, notebooks, and through Internet connectivity boxes like Apple TV, Boxee, and Roku, we’re seeing the leading edge of a shift in how TV shows and movies are accessed.</p>
<p>The phenomenon of “cord-cutting” is not new – mainstream publications have been following it for some time. But there’s evidence that the trend is accelerating, driven by ever-higher costs for pay TV subscriptions that are running above the annual rate of inflation.</p>
<p>And it’s Generation Y that is taking the lead here, preferring to watch episodes of popular TV shows after they become available for download or streaming at Amazon, Hulu, Vudu, Netflix, and on network Web sites. That is carrying time-shifting to an extreme, but it’s all in the name of economy.</p>
<p>Now, the traditional pay TV systems will tell you that cord-cutting is an aberration; a short-lived phenomenon that will run its course once younger people get married, form households, have children, and change to more traditional cable or satellite service.</p>
<p>Except that doesn’t appear to be happening. Just as Generation X and Y have all but pushed traditional landline telephone service into oblivion in favor of 24/7 mobile phone use, so too will they force the Comcasts, Time Warners, and DirecTVs of the world to finally offer some type of <i>a la carte</i> programming at lower prices.</p>
<p>And Gen X and Y will succeed because they’re already watching <i>a la carte</i>, streaming or downloading selected shows and movies at $2 &#8211; $5 a pop when it suits them. Many are supplementing Internet TV viewing with free, over-the-air broadcast HDTV services to hold the line on their entertainment budgets.</p>
<p>Many people buy WiFi-enabled Blu-ray players solely for the purpose of streaming. Yes, they can pop in a BD or DVD now and then, but the majority of their viewing is through that streaming port. And that is one reason why Blu-ray player prices have dropped so far and so fast.</p>
<p><i>Hardware is cheap, and anyone can make it.</i></p>
<p>&nbsp;</p>
<div id="attachment_2870" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2870" alt="Panasonic's NEO plasma TVs make drop-dead beautiful pictures. So how come most people still buy LCD TVs?" src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Panasonic-Wall-of-Neo-Plasma-PPT.jpg" width="600" height="478" /><p class="wp-caption-text">Panasonic&#8217;s NEO plasma TVs make drop-dead beautiful pictures at reasonable prices. So how come nearly 9 out of 10 people still buy LCD TVs?</p></div>
<p>&nbsp;</p>
<p>When you stop and think about it, the cost of consumer electronic devices compared to the power and functionality they offer is simply mind-boggling. With a $40 Bluetooth keyboard and $60 micro mouse, my Nook HD+ is transformed into a super-compact notebook computer. I can surf the Web, watch movies and TV shows, send and receive emails, and even make a PowerPoint presentation. And all of that cost me less than $400.</p>
<p>Televisions with screens smaller than 50 inches can often be purchased for less than $10 per diagonal inch. For that matter, I’ve seen 26-inch and 32-inch LCD TVs for about $8 per diagonal inch, a price point at which virtually no one is making any money. This means your next TV purchase is basically amortized in less than a year, and if it breaks, you simply recycle it and buy a new one.</p>
<p>The glut of LCD TVs in all sizes and the resulting TV price wars are claiming one casualty – plasma. Plasma TVs were once the Rolls-Royce of TVs and commanded comparable pricing. They still have the advantage in image quality all over LCDs, particularly at wide viewing angles. Maybe they aren’t quite as bright, but they do have excellent dynamic range and deep blacks.</p>
<p>So what? In the third quarter of 2012, 88% of all TV shipments worldwide were LCDs. 5.5% were plasma. In fact, more CRT TVs were shipped worldwide in Q3 2012 than plasma TVs! (You could look it up, as Casey Stengel used to say.)</p>
<p>Clearly, price and convenience are trumping quality, adding plasma to the endangered species list. Samsung, Panasonic, and LG will continue to manufacture plasma TVs as long as there is reasonable demand, but have been shuttering factories and fabs along the way as demand drops.</p>
<p>More importantly, they’re not investing any more capital in upgrading or enhancing plasma technology – not while TV prices are hovering in the range of $8 &#8211; $12 per diagonal inches, LCDs account for nearly 9 out of every 10 TVs sold currently, and the Chinese are breathing down the necks of Korean and Japanese TV brands with even lower-priced models.</p>
<p><i>Hardware is cheap, and anyone can make it.</i></p>
<p>&nbsp;</p>
<div id="attachment_2872" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2872" alt="Haier'as 84-inch 4K LCD TV looked as good as anything in the LG and Sony booths." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Haier-Ultra-HD-Demo-MR-PPT.jpg" width="600" height="581" /><p class="wp-caption-text">Haier&#8217;s 84-inch 4K LCD TV looked as good as anything I saw in the LG and Sony booths.</p></div>
<p>&nbsp;</p>
<p>I’ll close this essay with a look to the future of TV – specifically, 4K TV. You can shrug your shoulders, smirk, or make fun of 4K. But there’s no denying that it’s coming whether or not there is enough 4K content to watch.</p>
<p>4K went from being highly-anticipated at CES to “ho hum” in a single day. That’s because so many companies had 4K TVs on display, and many of those were located in China. Brands like Hisense, TCL, Skyworth, and Haier showed fully-loaded 4K TV products that were every bit as impressive as the latest “smart” TV offerings from Samsung, LG, Sony, Panasonic, and Sharp.</p>
<p>Not only that, the Chinese brands had multiple models of 4K TVs. While Sony and LG got some “oohs!” and “aahs!” for their 84-inch LCD offerings, Hisense had 50-inch, 55-inch, 65-inch, 84-inch, and 100-inch models flickering away in the aisles. Westinghouse Digital showed a similar portfolio in their LVH suite. Skyworth’s small booth was dominated by an 84-inch 4K set, while TCL pulled off a sensational marketing and PR coup; getting the producers of the upcoming <i>Iron Man 3</i> release (May) to showcase their 110-inch 4K set in the movie. (Guess Samsung and Sharp were asleep when that happened?)</p>
<p>The fact is, most TV manufacturing is inexorably moving to China. Some will remain in Korea, but it’s hard to see how the Japanese can hang on, seeing as they are getting clobbered by an unfavorable exchange rate on the yen and the emergence of large LCD fabs in Taiwan and China that can make big sheets of inexpensive, good-quality LCD glass – glass that can be used in everything from tablets and phones to televisions. It’s just not a fair fight.</p>
<p><i>Hardware is cheap, and anyone can make it…</i></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>February 14, 2013  3:39 PM</b>
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
			<?=getComments(5080)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5080)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-in-the-wake-of-ces-2013-thoughts-and-afterthoughts.php" type="text/javascript" charset="utf-8"></script>
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