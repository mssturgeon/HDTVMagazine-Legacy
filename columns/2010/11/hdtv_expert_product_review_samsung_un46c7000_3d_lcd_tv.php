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
		AND e.entry_id = 4054";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4054 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4054 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4054";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/11/hdtv-expert-product-review-samsung-un46c7000-3d-lcd-tv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4054";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV</title>
	<meta name="keywords" content="blu ray, movie mode, image quality, lcd tvs, color temperature, mode, –, color, frame, samsung, image, ’s, samsung’s, movie, gamma, contrast, content, lcd, brightness, tvs, auto, video, calibration, input, adjustments" />
	<meta name="description" content="Samsung was one of the first companies to get out of the gate with 3D TVs. Here’s a close-up look at the smallest model in their product line, the UN46C7000." />
	<meta name="title" content="HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/11/hdtv-expert-product-review-samsung-un46c7000-3d-lcd-tv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Samsung was one of the first companies to get out of the gate with 3D TVs. Here’s a close-up look at the smallest model in their product line, the UN46C7000." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4054', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/11/hdtv-expert-product-review-samsung-un46c7000-3d-lcd-tv.php">HDTV Expert - Product Review: Samsung UN46C7000 3D LCD TV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>November 10, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=511&category=LCD HDTVs">LCD HDTVs</a></b>
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
          <p>If you attended CES back in January, you couldn’t escape 3D. It was everywhere in every booth, staring down from plasma and LCD TVs, projected from hanging screens, and dazzling on super-thin OLED monitors.</p>
<p>There is no question that TV manufacturers put some heavy bets on 2010 being the year of 3D. And most of the heavy betting came from Samsung, who originally announced 19 different models of LCD and plasma 3D sets at their press conference.</p>
<p>As things played out, public reception to 3D TV has been mixed. Numerous surveys have been taken that show consumers think 3D is certainly cool, but not many of them plan to buy a 3D TV this year. Is it too early in the technology curve? Is the lingering recession keeping wallets shut? It’s hard to say, but the fact is that 3D is coming along slowly – perhaps more slowly than manufacturers would like.</p>
<div id="attachment_904" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/un46c7000-led-MR.jpg"><img class="size-full wp-image-904" title="un46c7000-led MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/un46c7000-led-MR.jpg" alt="" width="600" height="495" /></a><p class="wp-caption-text">No, those cute lil' monsters do NOT come with the TV.</p></div>
<p>Samsung’s UN-46C7000 ($2,599 list) is one of the smallest 3D TVs available. For this review, I purchased Samsung’s BD-C6900 3D Blu-ray player for $249 at Amazon.com, as it was difficult to procure a press sample. (You can now buy this player for $214 at several different online stores.) Of course, right after it shipped, Samsung’s PR agency sent me the new BD-C6800 player. Figures!</p>
<p><strong>OUT OF THE BOX</strong></p>
<p>The UN46C7000 is ready to rock and roll. You’ll spend a few minutes assembling the support stand and trying to figure out how to attach it to the back of the incredibly-thin TV (something Samsung’s lab folks have had to deal with, too).  The finish around the bezel and on the stand is a shiny silvery color, which I find a bit distracting. But it goes to the old saying that “televisions are furniture,” I guess.</p>
<p>Samsung has provided plenty of input connections on this TV. There are four HDMI inputs, all of them version 1.4a compatible. Input #1 also supports connections to a personal computer, while Input #2 is the audio return channel (ARC) connection for an external AV receiver.</p>
<div id="attachment_900" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Rear-Panel-Analog-CU-MR.jpg"><img class="size-full wp-image-900" title="Rear Panel Analog CU MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Rear-Panel-Analog-CU-MR.jpg" alt="" width="600" height="272" /></a><p class="wp-caption-text">Believe it or not, THOSE are the analog video connections, along with the antenna input (far left).</p></div>
<p>There’s also a single analog component video (YPbPr) connection, a sign of the times. How much longer before this connection goes away altogether?  Of course, composite video connections just WON’T go away, and there’s one of those, too. Note that all of these analog connections do not use conventional RCA jacks – there’s no room for ‘em.</p>
<p>Instead, Samsung provides special breakout cables for component and composite video, along with analog video hookups. The actual plugs are stereo mini types. The same space/size problem applies to the Antenna input – Samsung provides an adapter to go from the standard threaded F-connector to a mini slide-on coaxial connector.</p>
<p>All of the HDMI connections support CEC, so when you turn on your Blu-ray player, the TV also powers up and switches to that input automatically. Want to feed digital audio from TV programs to your AV receiver? Samsung’s gotcha covered with a Toslink output jack, but you’ll need to come up with the cable. And as I just mentioned, HDMI input #2 will provide an audio return path to your receiver.</p>
<div id="attachment_901" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/CU-HDMI-Inputs-MR.jpg"><img class="size-full wp-image-901" title="CU HDMI Inputs MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/CU-HDMI-Inputs-MR.jpg" alt="" width="600" height="523" /></a><p class="wp-caption-text">Four HDMI inputs are arrayed vertically along the left side of the rear panel.</p></div>
<p><strong>MENUS AND ADJUSTMENTS</strong></p>
<p>Samsung’s menus haven’t changed much over the years.  There are four image presets, labeled Dynamic, Standard, Natural, and Movie. Suffice it to say that you won’t want to run the TV for very long in Dynamic mode, as the pictures are extremely bright and over-enhanced. Standard, Natural, and Movie modes all work well for everyday viewing, but if you are into calibration, you’ll need to use Movie mode.</p>
<p>In addition to the Big 5 adjustments, you can also select from four different color temperature settings, five different aspect ratio settings, and a host of ‘green’ energy setting modes called Eco Solution. There are five different settings for screen brightness – including one that turns the image off, but leaves the sound on – and there’s also an ‘Eco Sensor’ that adjusts picture brightness based on ambient room lighting conditions.</p>
<p>If you think all of these settings play havoc with gamma, you are correct! And there are other image ‘enhancements’ that Samsung has included that will also result in some strange gamma curves, including three different black levels, three settings for dynamic contrast, and a shadow detail enhance/reduce adjustment. My advice is to leave them all off.</p>
<div id="attachment_902" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Remote-Control-CU-MR.jpg"><img class="size-full wp-image-902" title="Remote Control CU MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Remote-Control-CU-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Samsung's backlit remote controls have gotten pretty snazzy in recent years. </p></div>
<p>Thanks to former home theater magazine editor Mike Wood, who know runs Samsung’s test lab in Los Angeles, we’re seeing more calibrator-friendly adjustments in the image menu. There are two Expert Patterns (grayscale and color) for basic brightness, contrast, saturation, and hue calibrations. You can also select red, green, and blue-only modes, as well as Auto, Native, and Custom color spaces. The Custom mode lets you define your own x,y coordinates for primaries.</p>
<p>For color temperature calibration, Samsung provides two-point and ten-point RGB gain and offset adjustments. The theory is to do most of the calibration in two-point mode, then go back through a multi-step grayscale in ten-point mode for fine-tuning. (It almost worked for me, with one hiccup.)</p>
<p>Other adjustments include Flesh Tone enhance (leave it off), xvYCC mode (leave it off as well, no one currently supports extended color in packaged content), and the usual edge enhancement (peaking) stuff. (Remember, HDTV doesn’t need edge enhancement – it’s high-definition, savvy?)</p>
<p>There are a couple of noise filters that have some effect on image quality. The MPEG noise filter attempts to use low-pass filtering to get rid of mosquito noise and macroblock (excessive compression) artifacts. Be warned that low-pass filtering softens high-frequency image detail, so go easy on these controls. There’s only so much you can do to turn chicken turds into chicken salad, as my old college film professor used to say.</p>
<p>We’ll wrap things up with a discussion of Auto Motion Plus. This feature, which is pretty much <em>de rigueur</em> on all new LCD TVs, corrects for 24-frame judder by pulling the frame rate up to multiples of 60 Hz. In the case of the UN46C7000, the corrected frame rate is supposedly 240 Hz. What this actually does to images is to make filmed content look like it is live, or shot at video rates.</p>
<p>Whether this is esthetically a good thing to do is a matter of debate. The result is a very smooth presentation, free of flicker and judder, but it just doesn’t look the same as a movie. The motivation behind Auto Motion Plus (and every other TV manufacturers implementation of it) is to get rid of motion blur and smearing, something that all LCD TVs suffer from to various degrees. Try it – you may like it, you may hate it.</p>
<p><strong>3D MENUS</strong></p>
<p>Thought I’d forgotten about these, eh? Samsung 3D TVs are quite smart enough to recognize when 3D content is streaming through their inputs, unless it is encoded in the HDMI v1.4a <strong><em>frame packing</em></strong> format. This format, which delivers movies in the 1920×1080p @24 Hz format, is so unique that if you start playing a 3D Blu-ray disc, the UN46C7000 will automatically switch into 3D mode – no further adjustments required.</p>
<p>The two <strong><em>frame-compatible</em></strong> 3D formats (1080i side-by-side and 720p top+bottom) require some help from you to be shown correctly. Once you’ve established that you are indeed seeing the unprocessed 720p or 1080i 3D program from your content provider, go into the UN46C7000’s 3D menu and turn 3D mode ON.</p>
<p>Your will then be presented with a menu of 3D frame compatible formats to choose from, including <strong>side by side</strong> (1080i), <strong>top &amp; bottom</strong> (720p), and several esoteric formats like <strong>line by line</strong>, <strong>vertical stripe</strong>, <strong>checkerboard</strong> (also known as quincunx), and <strong>frequency</strong>. That last format alternates full-frame left and right images in a similar manner to active shutter 3D, but at slower frame rates.</p>
<p>Aside from frame packing and side-by-side/top &amp; bottom, you are most likely to run into the checkerboard format when playing back 3D games and other non-standard media. The other formats are not widely used, but you may come across them with Internet-delivered or broadcast content in the future.</p>
<p>Samsung also has a 2D to 3D conversion algorithm built-in to all of their 3D TVs. Try it – the effect is noticeable at times, but still doesn’t look quite right to me. My advice is not to try and add synthetic 3D effects to everyday TV shows and movies, but stick with content that has been specifically formatted for 3D. (Readers who saw <em>Clash of the Titans</em> in 3D know what I’m talking about.</p>
<p><strong>ON THE TEST BENCH</strong></p>
<p>Given all of the image enhancement adjustments present in this TV – and the auto-dimming circuitry that boosts black levels – it is difficult to get an accurate read on gamma performance and contrast. Nevertheless, I did run a basic set of test patterns and came up with some mostly-believable numbers, using 1920×1080p test patterns from an AccuPel HDG4000 generator and ColorFacts 7.5 calibration software.</p>
<p>After my best calibration, I measured brightness in Movie mode at 110 nits  (32 foot-Lamberts). That number ranged as high as 400 nits in Dynamic mode (tanning lamp mode), 201 nits in Standard mode, and 210 nits in Natural mode. ANSI (average) contrast was clocked at a respectable 621:1, with peak contrast from  checkerboard pattern at 722:1.</p>
<p>Because of the auto dimming feature with low-level content, peak contrast can reach amazingly high levels. In Movie mode, a sequential white/black measurement reached 20,567:1, and soared to 400,000:1 in Dynamic mode. (Not that your eye can actually see that level of contrast.)</p>
<div id="attachment_905" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-Luminance-Histogram-FINAL-CAL-MR.jpg"><img class="size-full wp-image-905" title="Samsung UN46C7000 Luminance Histogram FINAL CAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-Luminance-Histogram-FINAL-CAL-MR.jpg" alt="" width="600" height="334" /></a><p class="wp-caption-text">It's kinda wobbly-looking, but this 2.44 gamma was the best I could pull from the TV.</p></div>
<p>White balance uniformity was respectable for an LCD TV. Maximum color temperature shift across a full white screen was 388 degrees Kelvin, while maximum color shift across a nine-step grayscale was 287 degrees Kelvin. During one of my ten-point calibrations, the gray pattern at 30 IRE shifted noticeably blue-green, resulting in a bump up to 7260K. I’m not sure why it happened – going back and recalibrating in two-point mode fixed the problem.</p>
<div id="attachment_906" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-Temperature-Histogram-VIDEO-GAMMA-FINAL-MR.jpg"><img class="size-full wp-image-906" title="Samsung UN46C7000 Temperature Histogram VIDEO GAMMA FINAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-Temperature-Histogram-VIDEO-GAMMA-FINAL-MR.jpg" alt="" width="600" height="334" /></a><p class="wp-caption-text">That's a pretty impressive grayscale track!</p></div>
<div id="attachment_907" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-RGB-Levels-Histogram-VIDEO-GAMMA-FINAL-MR.jpg"><img class="size-full wp-image-907" title="Samsung UN46C7000 RGB Levels Histogram VIDEO GAMMA FINAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-RGB-Levels-Histogram-VIDEO-GAMMA-FINAL-MR.jpg" alt="" width="600" height="334" /></a><p class="wp-caption-text">And here's the reason why - look at the RGB levels, which vary little from black to 100 IRE.</p></div>
<p>I mentioned the screwy gamma curve performance earlier. You’ll tear your hair out trying to get a consistent gamma on the UN46C7000, so you’ll just have to settle for your ‘best shot.’ That’s what I did with an effective but wobbly 2.44 gamma in what I called my ‘best’ calibration out of ten. Not satisfied, I came back and tried it again with a ‘final’ calibration and didn’t see a significant difference.</p>
<p>But both curves were a lot cleaner than what I started with, which was S-curve gamma response in almost every picture mode. The culprit? That doggone auto-dimming circuit that forces deep blacks when the on-screen content has low luminance levels. Needless to say, you don’t want to be using a TV like this as a reference-grade monitor.</p>
<p>The UN46C7000 has a surprisingly accurate color gamut when compared to the BT.709 standard color space for HDTV. It just comes up a bit short on red and is oversaturated with green and blue. You can fix this to some extent using the Custom color space control, but red, yellow, and green are then undersaturated as a result. Can win ‘em all…</p>
<p style="text-align: center;">
</p><div id="attachment_914" class="wp-caption aligncenter" style="width: 480px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-CIE-Chart.jpg"><img class="size-full wp-image-914 " title="Samsung UN46C7000 CIE Chart" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-CIE-Chart.jpg" alt="" width="470" height="542" /></a><p class="wp-caption-text">Here's the UN46C7000's factory color gamut...</p></div>
<div id="attachment_916" class="wp-caption aligncenter" style="width: 480px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-CIE-Chart-MOVIE-MODE-BEST-CAL.jpg"><img class="size-full wp-image-916 " title="Samsung UN46C7000 CIE Chart MOVIE MODE BEST CAL" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/Samsung-UN46C7000-CIE-Chart-MOVIE-MODE-BEST-CAL.jpg" alt="" width="470" height="542" /></a><p class="wp-caption-text">...and here's the corrected color gamut, albeit light on green, yellow, and red.</p></div>
<p><strong>IMAGE QUALITY</strong></p>
<p>Because this TV is primarily marketed for 3D use, I decided to make most of my image quality judgments based on 3D content.  Of course, that didn’t leave me a lot of options for programming as I could only choose from 3D sports on ESPN, or the sole 3D Blu-ray disc in my possession – <em>Monsters Vs. Aliens</em>.</p>
<p>My thoughts on 3D football have already been published and <a href="../?p=777">can be found here</a>. As for image quality, I found myself switching to Natural or Standard mode to pick up the additional brightness I was losing through Samsung’s active shutter glasses – about 50%, according to the basic physics of light. Movie mode was not bright enough for viewing 3D unless I had all ambient room lighting dimmed and there was little or no outside light.</p>
<p>Of course, switching out of Movie mode when watching a 3D movie tosses all of your calibration efforts out the window. How’s that for a conundrum? Your best image quality isn’t bright enough for watching 3D movies. (I knew there was a catch to this 3D thing…)</p>
<p>Switching in and out of Auto Motion mode fixed up quite a few motion blur problems observed in ESPN’s 3D telecast of the Ohio State – Miami football game, which I also elected to watch in Standard mode so I could throw away 100 of those 200 nits, yet still have acceptable screen brightness. I didn’t have a chance to use it to watch conventional movies.</p>
<div id="attachment_918" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/11/ESPN-Top-and-Bottom-Channel-980-WR.jpg"><img class="size-full wp-image-918" title="ESPN Top and Bottom Channel 980 WR" src="http://www.hdtvexpert.com/wp-content/uploads/2010/11/ESPN-Top-and-Bottom-Channel-980-WR.jpg" alt="" width="600" height="390" /></a><p class="wp-caption-text">This is the 21st-century version of the old Indian chief test pattern.</p></div>
<p>The 3D experience using frame compatible formats isn’t quite the same as watching frame-packed 3D from a Blu-ray. The latter format has more detail, more contrast punch, and  is just a lot more satisfying to watch. Because the two frame-compatible formats are half-resolution, image detail on long and medium shots didn’t quite measure up to ‘straight’ HD as seen from ESPN’s 2D telecast of the same game on my adjacent Panasonic 42-inch 1080p plasma.</p>
<p><em>Monsters</em> in 3D was a very enjoyable experience. I did observe a slight amount of crosstalk through Samsung’s glasses, mostly when bright or near-white objects were present in the frame, such as Dr. Cockroach’s white lab coat, or white text on signs. Auto Motion was disabled and I didn’t see much in the way of objectionable judder, although animated movies tend to be ‘cleaner’ in this regard than live action films.</p>
<p>In general, it’s tough to make critical observations about 3D image quality because the images are so much dimmer. And it is discouraging that the best calibrated mode was too dark for my liking, resulting in dull colors and lower contrast. But given the screwy gamma response I saw in all modes, maybe I should have just sat back and enjoyed whatever appeared on the screen.</p>
<p>2D was a different story. In Movie mode, images had saturated, accurate color, plenty of contrast pop, and more than enough brightness for everyday viewing. Once ambient room light levels get to a certain point, you don’t really see any elevated black level issues. But you will see a flattening of contrast and a drop in brightness as you move off the center axis, something all LCD TVs have to contend with.</p>
<p><strong>CONCLUSION</strong></p>
<p>Samsung’s UN46C7000 is representative of current 3D LCD TV technology, using edge LED backlighting, auto dimming, and a super-thin design.</p>
<p>In terms of 2D performance, it is a strong performer despite those issues relating to gamma performance. In fact, it’s one of the best ultra-thin LCD sets I’ve examined in recent years, even though the patterned vertical alignment (PVA) liquid crystal layer still has some problems with color shifts when viewed off-axis.But it is bright, the colors pop, and images are detailed and crisp, especially after you go through and disable all of the so-called enhancements. And as you can see from the charts, once you calibrate it, it stays tight when tracking a specific color temperature.</p>
<p>As a 3D set, it does a workmanlike job, but could use more help with critical adjustments at higher brightness levels. You can’t calibrate anything in any mode other than Movie, so your only option is to crank up the brightness and try to recapture some of the light lost in Samsung’s active shutter glasses. That may screw up the TV’s gamma response, through.</p>
<p><strong> SAMSUNG BD-C6900/BD-C6800:</strong><strong> </strong> Samsung’s 3D Blu-ray players are very easy to set up. Plug them in, power up, and the CEC sensor will automatically turn on the TV and switch to that input. Both players are WiFi enabled, and will prompt you for a connection to your home network using manually-configured IP setup or the default automatic (DHCP) configuration. If you don ‘t know much about TCP/IP configurations and addresses, use the automatic mode to set it and forget it.</p>
<p>Both players can stream content from Netflix and also from your home media servers, so you can watch video clips, look at digital photos, and listen to MP3 music files and Internet radio from Pandora. The players will automatically configure themselves to the 1080p/24 frame-packing format when a 3D Blu-ray disc is loaded, and the default output resolution is 1080p for Samsung LCD and plasma TVs.</p>
<p>Full specifications and other product information are available here – http://www.samsung.com/us/video/tvs/UN46C7000WFXZA</p>
<p>Current Web prices on this TV range from <strong>$1,370 to $2,200</strong> as of November 10, 2010.</p>
<p>Power consumption tests – Over an <strong>8-hour</strong> period, the UN46C7000 consumed an average of <strong>106.4 watts</strong> while in Movie mode with full-screen content.</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>November 10, 2010  2:26 PM</b>
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
			<?=getComments(4054)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4054)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/11/hdtv-expert-product-review-samsung-un46c7000-3d-lcd-tv.php" type="text/javascript" charset="utf-8"></script>
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