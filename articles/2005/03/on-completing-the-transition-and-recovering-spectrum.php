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
		AND e.entry_id = 21";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 21 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 21 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 21";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/03/on-completing-the-transition-and-recovering-spectrum.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 21";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download On Completing the Transition and Recovering Spectrum" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="On Completing the Transition and Recovering Spectrum" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="On Completing the Transition and Recovering Spectrum" />
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
	<title>HDTV Magazine - On Completing the Transition and Recovering Spectrum</title>
	<meta name="keywords" content="completing transition, hdtv signal, digital tuner, day spring, hdtv summit, hdtv, spectrum, digital, transition, demand, those, cost, analog, day, washington, should, new, consumer, box, any, first, being, market, free, jnd" />
	<meta name="description" content="The most hotly contested DTV topioc in Washington today is over the shut-off date for the analog spectrum.

The arguments for and against a &quot;date certain&quot; cut off are uppermost though not far behind is the approach using the 85% rule.

The most compelling argument for a &quot;date certain&quot; (2006 still most favored) is that it focuses the mind like nothing else can. The 85% rule is in and of itself not clear. Many take it to mean that when 85% of the TV households in a market can decode any digital signal the rule is satisfied. Others say that it should be satisfied only when 85% of the households can decode a digital over-the-air broadcast. Under either circumstance the life of an old analog receiver is extended while spectrum can b e returned to the FCC for auction.
" />
	<meta name="title" content="On Completing the Transition and Recovering Spectrum" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="On Completing the Transition and Recovering Spectrum" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/03/on-completing-the-transition-and-recovering-spectrum.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The most hotly contested DTV topioc in Washington today is over the shut-off date for the analog spectrum.

The arguments for and against a &quot;date certain&quot; cut off are uppermost though not far behind is the approach using the 85% rule.

The most compelling argument for a &quot;date certain&quot; (2006 still most favored) is that it focuses the mind like nothing else can. The 85% rule is in and of itself not clear. Many take it to mean that when 85% of the TV households in a market can decode any digital signal the rule is satisfied. Others say that it should be satisfied only when 85% of the households can decode a digital over-the-air broadcast. Under either circumstance the life of an old analog receiver is extended while spectrum can b e returned to the FCC for auction.
" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=21', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/03/on-completing-the-transition-and-recovering-spectrum.php">On Completing the Transition and Recovering Spectrum</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>March 18, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p>The most hotly contested DTV topioc in Washington today is over the shut-off date for the analog spectrum.</p>

<p>The arguments for and against a "date certain" cut off are uppermost though not far behind is the approach using the 85% rule.</p>

<p>The most compelling argument for a "date certain" (2006 still most favored) is that it focuses the mind like nothing else can. The 85% rule is in and of itself not clear. Many take it to mean that when 85% of the TV households in a market can decode any digital signal the rule is satisfied. Others say that it should be satisfied only when 85% of the households can decode a digital over-the-air broadcast. Under either circumstance the life of an old analog receiver is extended while spectrum can b e returned to the FCC for auction.</p>

<p>The Tenth <strong>HDTV Summit</strong> hosted by the Consumer Electronics Association was held at the Washington Convention Center on March 15th. The annual event drew 400 top executives. They gathered to discuss various analog shut off proposals as well as divine what the returned frequencies may do to bolster homeland security and American job opportunities by new digital services. Another unresolved issue is whether the spectrum to be reassigned will be managed or unmanaged. This relates to the devices themselves and how they comply with channel interferences.</p>

<p>Spectrum is valued by how much information it can carry and how far it can carry it. The spectrum that has been assigned to broadcasters is by far the best (propagation characteristics) for wide scale distribution of large amounts of data. It is coveted by those who want additional internet-like services for mobile devices as well as those who think homeland security needs to beef up its communications by using these frequencies.</p>

<p>Is it valuable spectrum? Depending on the day, the wind direction in Washington, and the favorableness of the economy this key spectrum has been appraised at between 2 and 70 billion dollars (proceeds to the general fund) with that money coming from government auctions. This value if lessoned if any significant part of the spectrum is withheld from the auction market due to its still being occupied by a broadcaster. But for whom is it valuable? The chief among the commercial bidders will be those seeking 3G wireless spectrum. The bidders may also be the broadcasters themselves, though the ownership restrictions make that unlikely since TV stations, even group broadcasters, have little in the way of a national vision to call upon. Their way of life and mission is "local" and the spectrum being vacated is national. Big wireless internet and phone services are more likely bidders.</p>

<p><strong>How to free the spectrum? That is the issue.<br />
</strong>It is generally agreed that the last phase in the digital transition will be the most challenging. The public must continue to do their part if the transition is to be completed. The question of how to get any unwilling public, should there be such, to do that is as debated today as it was 20 years ago.</p>

<p><strong><em>The focus has to be on demand!<br />
</em></strong>What will move H/DTV to completion is consumer demand. While some believe that market manipulations by government can do some good most say that there is no loophole that Washington can close or open that will substitute for the American public wanting its H/DTV. A clear and precise focus on creating demand is what is needed today.</p>

<p>Programming content is clearly the key for any demand building. Let me suggest that VH1 be completely converted to HDTV content and transmission by way of a fully focused industry effort. I urge you to not throw the switch to the HDTV services of VH1 until all its programming is in the true HDTV format. There needs to be a clear demonstration showing the old and new services and nothing will demonstrate that more effectively than moving in one moment from all analog content and transmission to HDTV content and its distribution. I choose VH1 not to favor its present owners but to raise the awareness in a specific market sector that has yet to be properly targeted and courted. The results of this transition will be immediately seen and act as further encouragement to other channels who are contemplating their entry into HDTV. This can best be done with an industry-wide focus and should be launched on March 20, 2006, the first day of spring.</p>

<p><strong>The danger in offering too little...</strong><br />
<strong><em>Marketing schools teach that to succeed with a new product it must be at least 10 JND (Just noticeable Difference) improved over the one it is replacing.<br />
</em></strong><br />
A 2 million pixel HDTV standard was chosen by the Japanese in order to reach this 10 JND marketing benchmark. Super-VHS, which offered some visual improvements over plain Jane VHS, did not have this 10 JND difference and failed to excite the buying appetite of the general consumers. In calculating the differences between the video disks and VHS tape the 10 JND rule again was met. The more that HDTV is departed from in the digital television offerings the more difficult it is to create consumer demand. RCA recently announced their strategy to offer very low cost old standard resolution interlace scanning tube type sets that have a digital tuner. But as long as sets that don't have the added cost of a digital tuner are for sale there is no incentive (approaching 10 JND) to buy the higher priced version <em>unless</em> you are plagued with ghosts in your reception of analog. That lack of ghosts in what was otherwise an identical image was the key differentiator the digital set exhibited in Washington at the HDTV summit. There is no other obvious reason to buy a digital standard resolution set than clearing out ghosts from the old analog standard picture. Since cable and satellite have taken on that duty for nearly 90% of the households this introduction by RCA is likely to mean only confusion rather than easing the road to completion of the digital transition. "We no longer have the $75 TVs with which to compete in the marketplace," said David Kline from JVC. But those $75 TVs will continue to flood this nation unless made illegal (which is also under discussion).</p>

<p><strong>Completing the transition--some hardware solutions:<br />
</strong><br />
Another approach to completing the transition is to offer free or subsidized (thus low cost) converter boxes (tuners). Any OTA decoder must decode the entire ATSC signal and could and should port out an HDTV signal as well as one usable by existing analog sets (similar to the present crop of satellite boxes that port out NTSC. Various concepts have surfaced over how to subsidize the box so that consumers on fixed incomes can afford to do their part in completing the transition. One idea raised in Washington recently is that subsidy money can be borrowed from the future proceeds of the spectrum auction and applied to tuners and their distribution. A means test would be applied for eligibility of the consumer seeking such a no or little cost subsidized box. This could be administered through some form of a rebate system where the consumer buys the box for a small amount and recovers that same amount by submitting their claim to the government (or NGO agency) assigned the task of making rebates. The test and rebate procedure would in itself be a market obstruction unless made very simple and convenient.</p>

<p>All plans fail to take into account the millions of TV households that suffer from visual or other impairments, such as macular degeneration. What should induce these people with or without money in hand to buy a digital box in order to clear up a picture they can't see in the first place? You cannot dismiss them as not counting. Let's also take into account those people who no longer prize TV as a centerpiece. Rather than grapple with the other reasons why people may not be interested in supporting this transition let us merely suggest that those reasons do exist and the obstructions are not easily overcome as long as they require people to buy and do something. So, how do we find a way to give these hold outs these boxes without the added complexity of means testing and rebates?</p>

<p><strong>Advertising is the supporter of all free-over-the air broadcasting.</strong> <em>It is free over-the-air broadcasting and its valued spectrum which is under discussion here more so than is anything about cable or DBS.<br />
</em><br />
<strong><em>An idea to ponder:</em></strong><br />
It is generally conceded that the raw cost of an external digital tuner will be $50 when produced in high quantities. That is the cost that is either paid for by the consumers or is absorbed by someone other than the consumer. Advertising pays the cost for several billion dollars in programming annually. Advertisers do that in order to reach consumers attracted to that programming with the hope of selling them their products. Let's take that model and apply it to hardware. Ask yourself why advertising cannot pay for the digital boxes to then be given away?</p>

<p>Advertising messages can be embedded in the chip set so when the box is first turned on a message from the advertiser is briefly visible. You might refine that idea by making a system that acts like a filter and relays a message to the opening screen (when first turned on) from any advertiser who pays their way. This idea has been suggested to those assembled in Washington with a high degree of interest being shown.</p>

<p>This box could then be made available 'free for the asking' at a time when demand for TVs with digital tuners ebbs to a point of threatening the technical and hardware completion of the transition.</p>

<p><strong>Raising demand<br />
</strong>Another approach suggested is to re-launch HDTV. It has been my published opinion that HDTV never got the kind of launch it always deserved. If you believe that it will do anything towards transforming your life and all lives to follow you might ask why it merely seeped into the American consciousness rather than burst in upon the scene as some bright new revelation? I often wrote about the opening ceremonies for the HDTV era as being like the Fourth of July ceremonies that occur once a year. While perhaps exaggerated let me suggest that we have an official HDTV '<strong><em>new launch'</em></strong> day. This could be a day in which all present HDTV channels and stations would broadcast the same highly entertaining and educational program on every channel that is capable of delivering an HDTV signal. This would be the telethon of telethons that heralds the coming good HDTV is promising to deliver and for building mouth-watering demand in those who for the first time are attracted to a HDTV presentation. Above all it focus everything from set makers to retailers to this new medium and is designed to scuttle the remaining power left in the NTSC universe (the biggest competitor to HDTV is still NTSC). If we do believe in this transition as being important beyond spectrum recovery we should "shout its cultural values from the rooftops!" To do this I propose that a two hour celebration of HDTV be held on the first day of spring 2006 with every station capable of sending an HDTV signal delivering this exact same program at the same time. No one watching television that day can escape this program and the publicity for it is without peer. While this sounds initially expensive I urge you to think of how it can be underwriten by advertisers. In addition to that the acceleration of the cut off day is going to have a positive economic impact upon all of broadcasting and, if we believe the industrial sages, the US economy. Why? The sooner the power for the "second" transmitter can be shut off and the spectrum returned the better it is financially for both the broadcasters and the spectrum-seeking nation.</p>

<p>This is a cornerstone event that will also awaken the rest of the world that the HDTV era is upon us.</p>

<p>Dale Cripps</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>March 18, 2005  7:41 AM</b>
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
			<?=getComments(21)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 21)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/03/on-completing-the-transition-and-recovering-spectrum.php" type="text/javascript" charset="utf-8"></script>
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