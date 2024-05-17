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
		AND e.entry_id = 3729";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3729 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3729 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3729";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-nab-2010-a-show-in-transition.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3729";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - NAB 2010: A Show in Transition" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - NAB 2010: A Show in Transition" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - NAB 2010: A Show in Transition" />
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
	<title>HDTV Magazine - HDTV Expert - NAB 2010: A Show in Transition</title>
	<meta name="keywords" content="south hall, last hours, central hall, claim world’s, memory cards, video, products, nab, monitor, format, ’s, hall, inch, booth, hdmi, lcd, show, qam, using, days, mpeg, ”, projection, –, low" />
	<meta name="description" content="This year’s National Association of Broadcasters trade show resembled CES more than it did a traditional broadcast and engineering show." />
	<meta name="title" content="HDTV Expert - NAB 2010: A Show in Transition" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - NAB 2010: A Show in Transition" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-nab-2010-a-show-in-transition.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This year’s National Association of Broadcasters trade show resembled CES more than it did a traditional broadcast and engineering show." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3729', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-nab-2010-a-show-in-transition.php">HDTV Expert - NAB 2010: A Show in Transition</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April 16, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=298&category=Broadcast">Broadcast</a></b>, <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=514&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>Some of the big questions facing attendees as their flights landed in Las Vegas were these: Can NAB survive? Will it evolve into something different? Is it even that important to attend NAB anymore?</p>
<p>The answer to all three questions is “yes.” Even though attendance was still down from 2008 (NAB claimed 83,000 ‘officially;’ my guesstimate was more like 55,000 to 60,000), there were plenty of companies in attendance with lots of cool products to check out.</p>
<p>That said, the show is undergoing a rapid transformation away from a traditional ‘broadcasting’ show to a mix of InfoComm and CES – hot new products for professionals. Of course, 3D was all over the place. But so was networked video, which dominated the upper and lower South Hall exhibit areas.</p>
<p>Booths were smaller this year, and that’s not going to change any time soon…not when the typical booth is showing products that have price tags in the hundreds and low thousands. Contrast that with NAB shows 15 years ago, when most of the price tags had three and four zeros in them!</p>
<div id="attachment_483" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Attendee-Registration-Booth-MR.jpg"><img class="size-full wp-image-483" title="Attendee Registration Booth MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Attendee-Registration-Booth-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">You know attendance was off when this was one of the largest booths in the Central Hall!</p></div>
<div id="attachment_484" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Rear-Central-Hall-MR.jpg"><img class="size-full wp-image-484" title="Rear Central Hall MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Rear-Central-Hall-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">On the other hand, the alternative wasn't too attractive...</p></div>
<p>The smaller booths and lower number of exhibitors resulted in wider aisles and less traffic – a plus. But it also resulted in NAB placing the main registration area smack in the middle of the Central Hall, something I’ve never seen before.  And there was plenty of wide-open space at the end of that hall, as well in the North and South Halls.</p>
<p>Can NAB be staged in three halls? Absolutely! And can you see everything you need to see in three days? Try two days. (Thursday has become ‘exhibitor bonding day,’ to quote a fellow editor.) I could have covered my beat in two days if necessary.</p>
<p>THE TRENDS</p>
<p>Not surprisingly, 3D was a big topic this year, although not to the same extent as it was at CES. The SMPTE/ETC/EBU Digital Cinema Summit focused entirely on 3D for both days, and I was fortunate enough to deliver one of the papers to a jammed room of 500+ attendees.</p>
<p>Sony, Panasonic, JVC, Canon, Grass Valley, AVID, Doremi, Harris, Evertz, and Ross Video were just some of the companies showing 3D products in Vegas. Those products ranged from 3D monitors and cameras to 3D workflow (acquisition, editing, post, effects, and playout) software and hardware.</p>
<div id="attachment_485" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Sony-LM4251TD-Monitor-MR.jpg"><img class="size-full wp-image-485" title="Sony LM4251TD Monitor MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Sony-LM4251TD-Monitor-MR.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Sony's LM4251TD 42-inch LCD monitor uses micropolarizers for passive 3D viewing.</p></div>
<p>Other specialized 3D brands were in attendance, too. TD Vision, Miracube, Mistika, and HDlogix had nice exhibits in the South Hall, down the street from Grass Valley. Smaller companies like Cine-tal occupied the 3D Pavilion nearby, while Motorola and Ericsson showcased 3D transport and format recognition products upstairs.</p>
<p>Although the consumer TV market is seeing a big push towards active-shutter 3D TVs and monitors, the emphasis at NAB was on passive 3D viewing (cheaper glasses, more expensive displays). JVC, Hyundai, and LG all manufacture them, and there were plenty of folks standing around with RealD X-pol eyewear watching the demos.</p>
<p>The projector guys were on top of things, too. projectiondesign showed a stacked pair of 3-chip 1080p lightboxes in the Mistika booth, using linear polarized glasses. HDI showed a 100-inch, 1080p LCoS rear-projection TV in the HDlogix booth, also using X-pol glasses. Christie also had suitable 3D projection systems out for inspection.</p>
<p>There were also some demos that left me scratching my head, such as Canon’s dual-projection X-pol 3D demo, using a pair of REALiS WUXGA (1920×1200) LCoS projectors. While it worked well, it requires two separate projectors and outboard 3D filter holders – too klunky! (A Canon rep told me that was because of the 60 Hz frame rate limitation on the internal video processor.)</p>
<div id="attachment_486" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Canon-Dual-WUXGA-3D-Crop-MR.jpg"><img class="size-full wp-image-486" title="Canon Dual WUXGA 3D Crop MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Canon-Dual-WUXGA-3D-Crop-MR.jpg" alt="" width="600" height="484" /></a><p class="wp-caption-text">Well, it IS 3D, but I doubt Canon will sell very many of these rigs...</p></div>
<p>Broadband video and IPTV were also big this year. This market for MPEG-4 AVC over Ethernet, fiber, or private data networks is exploding, and encoder companies such as Adtec, Vbrick, Harmonic, Ericsson, Harris, Motorola, and Digital Rapids were showing a full range of compatible products.</p>
<p>Sezmi also occupied a booth at the show. This company has a unique selling proposition – a set-top box that receives both terrestrial (read: free) digital TV and selected cable channels carried on secondary terrestrial channels. It also accesses a video-on-demand server through broadband connections (SDTV only) and has a customizable program guide for each user.</p>
<p>While not technically broadband, the nascent MH broadcast format was in abundance at NAB. MH uses MPEG-4 AVC coding in multiple streams with IP headers to send low-resolution video to handheld receivers, such as mobile phones and combo PDA/receiver products. MH is catching on in popularity with broadcasters, who see it as a more sensible alternative to simple multicasting of secondary channels that very few people may be watching.</p>
<div id="attachment_488" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Cydle-i30-Demo-MR.jpg"><img class="size-full wp-image-488" title="Cydle i30 Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Cydle-i30-Demo-MR.jpg" alt="" width="600" height="675" /></a><p class="wp-caption-text">ATSC MH on an iPhone? Brilliant! (There's an app for everything!)</p></div>
<p>MY PICKS</p>
<p>After three days of walking around, I came up with a list of “finds” that I’ll share here. These are all products that represented clever thinking, breakthrough technology, and/or new price points. Some were easy to spot; others required quite a bit of digging. But they all made the trip to Lost Wages worth it (and that’s saying a lot, considering how airlines jam you in like sardines these days!).</p>
<p><strong>TV Logic:</strong> This manufacturer of LCD broadcast monitor showed the world’s first active-matrix OLED broadcast monitor (unless you think Sony’s press announcement hit first, which it didn’t.) The LM-150 ($6,200) uses a LG Display 15-inch OLED panel with 1366×768 pixel resolution and come equipped with all the expected niceties including markers, crop marks, caption displays, over/underscan, and HD/SDI, HDMI, and analog video jacks. There’s also a 3D version in the works (TDM-150) that will sell for about $7,700.</p>
<div id="attachment_489" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/TV-Logic-TDM-150-right-and-basic-15-inch-panel-left-MR.jpg"><img class="size-full wp-image-489" title="TV Logic TDM-150 (right) and basic 15-inch panel (left) MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/TV-Logic-TDM-150-right-and-basic-15-inch-panel-left-MR.jpg" alt="" width="600" height="493" /></a><p class="wp-caption-text">This was the coolest product at the show. But will it REALLY last 30,000 hours?</p></div>
<p><strong>Ericsson:</strong> In addition to a host of MPEG-4 and IPTV encoders, the ‘big E’ also showcased an innovative, iPad-like LCD touchscreen remote control/video viewer. Dubbed the IPTV remote, this product can dial up video from broadband, cable, satellite, and even your home network. Not only that, it can monitor weather sensors and your home security system. (Sound much like a Crestron product?) The IPTV remote will not be offered for sale at retail. Rather, it’s intended to be a content provider offering.</p>
<p><strong>Christie:</strong> Have you seen their MicroTiles yet on the <em>Colbert Report</em>? These innovative ‘mini’ DLP projection cubes use LED light engines to power 800×600 DMDs (the actual working resolution is 720×540) and measure about 12” x 16.” They can be configured in just about any format you wish, including floor and ceiling projection, and up to 1024 can be driven at one time. The LED light source is specified to last over 60,000 hours. Think of LED-powered LEGOÔ blocks, and you’ve got the concept.</p>
<div id="attachment_490" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Ericsson-IPTV-Remote-Screen-1-MR.jpg"><img class="size-full wp-image-490" title="Ericsson IPTV Remote Screen 1 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Ericsson-IPTV-Remote-Screen-1-MR.jpg" alt="" width="600" height="526" /></a><p class="wp-caption-text">And YOU thought iPads were all the rage...</p></div>
<p><strong>SmallHD:</strong> It wasn’t easy finding these guys behind the Sony booth, but they’d come up with a focus assist monitor for video and still cameras that they claim is the world’s smallest HD video monitor. The actual size is about 5.6 inches and the glass is WXGA (1280×800) LCD. It comes in two flavors – one for digital SLRs ($899) and one with SDI input ($1199). The monitors are an inch thick, weigh 10 ounces, and mount to hot shoes.</p>
<p><strong>Z3 Technology:</strong> I found this booth on my last pass through the South Hall, and it was worth the stop. They showed the Z3-MVE-01 MPEG encoder, a compact box that codes HD up to 1920×1080 resolution using H.64 High Profile (up to 30Hz), with Ethernet and ASI outputs. Input compatibility includes composite, component, HDMI, DVI, and HD-SDI video…all for $5,000.</p>
<div id="attachment_491" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/JVC-GD-463D10-Monitor-MR.jpg"><img class="size-full wp-image-491" title="JVC GD-463D10 Monitor MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/JVC-GD-463D10-Monitor-MR.jpg" alt="" width="600" height="466" /></a><p class="wp-caption-text">JVC's 46-inch X-pol monitor always drew a crowd.</p></div>
<p><strong>Adtec:</strong> I didn’t expect to see an HDMI-to-QAM modulator at the show, but that’s exactly what Adtec pulled out for me. The HDMI2QAM is a dual-channel design that encodes anything from the HDMI inputs (yes, they are HDCP-compliant) to a pair of quadrature amplitude modulation (QAM) channels, using MPEG-2 encoding. The modulation format is selectable between 64-QAM (SD), 128-QAM (not widely use), and 256-QAM (HD). Bit rates are constant and optimized for each mode (i.e. 38.8 Mb/s for each HD channel).</p>
<p><strong> </strong></p>
<p><strong>Cydle:</strong> This new start-up demonstrated an app for iPods and iPhones that allows viewing of ATSC MH (A/153) video. Along with it comes the i30,  a battery-powered docking station with built-in antenna (UHF). This means that your ‘i-whatever’ has two batteries to draw from, so if you run low on talk power, simply switch to the i30 battery. Both can charge simultaneously. Cool!</p>
<div id="attachment_493" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Sezmi-EPG-Demo-MR1.jpg"><img class="size-full wp-image-493" title="Sezmi EPG Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Sezmi-EPG-Demo-MR1.jpg" alt="" width="600" height="434" /></a><p class="wp-caption-text">Sezmi's personal program guide rivals TiVo for user-friendliness.</p></div>
<p><strong>Panasonic:</strong> I’ve seen it before at CES, but it now has a model number. The company’s first production camcorder now goes by the moniker AG-3DA1 and is yours for the low, low price of just $21,000. (Well, all things are relative, I guess.) The camera weighs about 6 and a half pounds and uses a pair of 2.l07 MP sensors (full 1920×1080) to record 1080i and 720p HD content to SD memory cards. Convergence and horizontal and vertical displacement are fully adjustable.</p>
<p>Panasonic gets another mention for the AG-AF100, which they claim is the world’s first Micro 4/3-inch (1.33:1) HD camcorder. That’s a big deal because the 4/3” format matches the coverage area of 35mm film frames…which means you can use standard 35mm film camera lenses to get effects like shallow focus, soft focus, and vignettes. The camera records to SD/SDHC/SDXC memory cards using the AVCHD format and supports 1080i/p and 720p formats, including 23.98/24/25 Hz.</p>
<p><strong>Sony</strong> gets extra credit for announcing the world’s second (or first) AM-OLED professional video monitor. The PVM-750 ($3,850) is a bit smaller than TV Logic’s offering at 7.4 inches (16:9), and is not quite full HD resolution at 960×540 pixels. (Not that you’d notice on such  small screen!)  The PVM-750 has 3G HD-SDI, HDMI, and composite video inputs, the full range of adjustments from tally and markers to blue screen mode and AC/battery power operation. No word on lifespan of the display, but Sony uses small molecule (SM) OLED technology, as does LG Display.</p>
<p><strong>LP Technologies</strong> rounds out my list with one of those ‘too good to be true’ products: An LCD-based 9 kHz to 3 GHz spectrum analyzer with USB 2.0 interface, built-in preamp, and Ethernet connectivity for remote monitoring. Sorry, no internal battery pack!) The USB hook-up can be used to save data in the Excel format, while the internal memory can tore 900 different waveforms. The display is a 6.4” 640×480 (VGA) LCD type. And the cost? Just $4,500…</p>
<div id="attachment_494" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/SmallHD-SLR-monitor-crop-MR.jpg"><img class="size-full wp-image-494" title="SmallHD SLR monitor crop MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/SmallHD-SLR-monitor-crop-MR.jpg" alt="" width="600" height="454" /></a><p class="wp-caption-text">Stick one of these on a Canon 5D MK II, and you can shoot an entire episode of 'House!' (No kidding!)</p></div>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April 16, 2010  4:40 PM</b>
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
			<?=getComments(3729)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3729)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-nab-2010-a-show-in-transition.php" type="text/javascript" charset="utf-8"></script>
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