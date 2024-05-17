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
		AND e.entry_id = 173";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 173 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 173 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 173";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/08/2001_thoughts_on_hdtv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (5) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 173";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2001 - Thoughts on HDTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2001 - Thoughts on HDTV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2001 - Thoughts on HDTV" />
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
	<title>HDTV Magazine - 2001 - Thoughts on HDTV</title>
	<meta name="keywords" content="copy protection, pay price, hdtv programming, hdtv today, high value, hdtv, our, market, new, any, still, even, upon, protection, good, world, being, copy, going, doing, everyone, last, make, everything, hollywood" />
	<meta name="description" content="Last time on these pages I said HDTV was a love affair between a box of lights and wires and we humans. Replacing NTSC television with a completely incompatible HDTV standard is like changing the side of the road upon which we drive for the sake of the view. This nation is doing that at a cost of hundreds of billions of dollars. Not before a leisurely evening of enjoyment with HDTV in their own home do people know why this transition is fully under way, and why it is needed.
" />
	<meta name="title" content="2001 - Thoughts on HDTV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2001 - Thoughts on HDTV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/08/2001_thoughts_on_hdtv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Last time on these pages I said HDTV was a love affair between a box of lights and wires and we humans. Replacing NTSC television with a completely incompatible HDTV standard is like changing the side of the road upon which we drive for the sake of the view. This nation is doing that at a cost of hundreds of billions of dollars. Not before a leisurely evening of enjoyment with HDTV in their own home do people know why this transition is fully under way, and why it is needed.
" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=173', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/08/2001_thoughts_on_hdtv.php">2001 - Thoughts on HDTV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>August  4, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p><em>Take the time to read this thought provoking article. It gained a large following on the forums shortly after being posted in our daily outbound HDTV Magazine. Its message is contemporary, even timeless.</em></p>

<p>__________________________________</p>

<p>Last time on these pages I said HDTV was a love affair between a box of lights and wires and we humans. Replacing NTSC television with a completely incompatible HDTV standard is like changing the side of the road upon which we drive for the sake of the view. This nation is doing that at a cost of hundreds of billions of dollars. Not before a leisurely evening of enjoyment with HDTV in their own home do people know why this transition is fully under way, and why it is needed.</p>

<p>Most executives from broadcast, cable, satellite, programming, manufacturing, and especially Congress still don't own or have use of a HDTV set in their home. As a result they wrestle with issues before them using theory and logic rather than an impassioned understanding that defies words. Indeed, the progress of HDTV, now in its third year of commercialization, has not been as brisk as its core of supporters had hoped. It has been given the amber light in most press reports, some even the red light. That has released naysayers to gloat about being right about their dire predictions of HDTV's ultimate casualty.</p>

<p>But HDTV is not a market failure. The latest figures drawn from the Consumer Electronics Association this last week show sales of DTV products for this May rose 110% over the same period last year. It is a defiant survivor who has withstood all the forces of Hell unleashed against it (daily for the last 20 years in which I have covered the story). Ghastly and discouraging retail demonstrations have done the business no good and giving it the duty of paying off the national debt has decidedly added a burden upon its broad shoulders. True, no part of the television market is enjoying lush profits from HDTV yet, though manufacturers are doing well enough..sort of. I just laid down today's Wall Street Journal after I read of the deep losses within the Japanese electronics manufacturers. Is that good news? I don't think so. With every failure in Japan you will loose something from the HDTV world. I am not saying it will not be replaced by emerging countries, like China, but what's the point? I was told by the head of the NHK Laboratories at this year's National Association of Broadcasters convention in Las Vegas, who was traveling with an an old friend of mine, Morio Kumabe, who for ten years headed the HDTV broadcast services from satellite under the flag of the High-Definition Promotion Association--a group of about 100 major Japanese companies first brought together by the former head of Mitsui. Kumabe said that a "huge success" for HDTV in this country would save Japan. I had heard from colleagues of mine earlier that these Japanese companies were in deep trouble and this WWJ article today rather confrims it. If Japan comes to believe that we have any kind of value in the marketing of their salvation--a sucessful market for HDTV--what can't we ask of them to help us? It would seem to me it is becoming in everyone's best interest to capitalize on the demand that this product produces in everyone who sees it and focus everyone involved to make it THE product to seek out and buy. As the consumer sector of the market we in this community of interest are the ONLY strength in which they can rally around.</p>

<p>There are things to be done before we court Japan. The big job is first convincing signal providers in the depths of their being that HDTV is good for them. They need and want to know that the public is out there doing their part to seek their signals and, more importantly, are now a conscious factor in this transition rather than an endless horde being dragged by the feet to who knows where. But the evidence in support of this view is not overly convincing...yet. High-scan monitors--over three million of them now sold--work mostly from DVDs signals. Decoders capable of making sense out of a terrestrial or cable signal have been a fraction of the unit sales, amounting to something just over 15% to that of the monitors. While a decoder for an HDTV program will deliver a kick-butt image people are frightened off from buying them. They hear old out-dated horror stories about reception or not-so-outdated rumors about impending changes in the standard to satisfy Hollywood. These issues, plus the lack of their being cable-ready, has too many consumers walking out of the retailer showrooms without buying a thing. Even worse, customers are not drawn into the stores to see the new image due to negative press accounts saying 'its still too early ."</p>

<p>Such slow decoders sale do nothing to raise the spirits for terrestrial signal providers. While relieved this year by excellent up-front advertising sales for their NTSC business, they have this involuntarily command to carry the pioneering water for H/DTV while dismantling the only business that is doing well (again). They have been badgered to provide still-more HDTV programming to fuel the interest, and still market impediments keep thwarting the efforts they do make in this direction.</p>

<p>Satellite providers have set top boxes, including the large dish folks. While they suffer from the same copy protection conundrum their reputation isn't quite as sullied as it is for over-the-air. They are capturing a good deal of the receiver market. Cable is entering with more aggressive moves than were expected, though far from convincing to many analysts. I just learned today, however, of Cox Cables aggressive plans to roll out in ten markets next fall. Time-Warner has been aggressive and doing the right moves. Once thought of as the biggest market impediment cable is turning out to be one of the better friends of HDTV.</p>

<p>But still, it is broadcasting who all look to to drive the 'real business' of HDTV. The networks still command half the audience in primet time. If you are a manager of a publicly held television enterprise today you live under unrelenting pressure from Wall Street who wants you to run your stock to the ceiling. Very unlikely that HDTV would be high on your list to do that. In an industry which lives or dies by daily ratings HDTV is nearly invisible. Only a handful of people represent the implementation of HDTV today within broadcasting.</p>

<p>Manufacturers of receiving equipment have been saying that more compelling HDTV programming is the key to fostering a faster market uptake, especially for the decoders. Once the market has attained a certain "velocity," they predict, the pressure on executives from financial institutions is lifted. Things can shift rapidly then to their opposite. New content then gets released for competitive reasons, which causes retail sales to soar even more. It then becomes obvious to everyone that a great big hit is in the making. "It should be backed," the financial managers will advise. "Anyone who is a stakeholders in television should jump in." More fuel is added by this lead to drive retail markets to the point where even the stickiest of the market impediments (used until then as a safety brake) are boldly swept away as everyone sprints forward to gain a solid foothold in the new 'success story.' At least that is my take on this phenomenal story.</p>

<p>This 'exuberant' condition has yet to show itself. We are still mired in a phase where highly paid managers are focused to squeezing out the last remaining profits from the old standard. Those who inhabit the world of HDTV today are still thought of as 'fringies', yours truly included. This 'fringe' is actually a highly dedicated group of souls who believe that HDTV means much more to civilization than just a new technical gadget. HDTV brings a new way of seeing to the world that embraces it. It can be seen as a new carrier for all other visions. As one dear friend puts it, "It lets the real world in"</p>

<p>While not all of my colleagues share this lofty view I can say without equivocation that there is a growing belief that HDTV is at least a healthy and positive contribution to our times. It is so attractive that once installed a new respect for quality must follow. That seems especially important in times dominated by coarseness in social intercourse and when degenerate institutions face deadly global conflicts. If the world is to find a way past its present meltdown it will come only with a NEW vision which, as Dr. Maya Angelou said a few years back, "can be followed with our hearts unafraid."</p>

<p>Such sweeping social visions only come through inspired artists. It makes some sense to say that the best of them will choose a medium that "makes everything new again" all from the (jump) start. These light bearers learn to transcend old dying conditions and offer up an alternate view of life -- like seedlings sprouting from a charred forest. Civilizations gone mad restore order in one of two ways--a passionate movement for freedom and godly abundance driven by recognizably good people, or, diving headfirst into a dark dictatorial world where we all find ourselves enslaved by paranoid characters leading all to an inescapable misery. That dark shadow has descended upon us more often than history likes to re-tell.</p>

<p>So far HDTV is the best means going for an artist to deliver the better of those two options, and to the widest possible audience. The Moher Film Market Analysis of 2001 has tuned to this theme last year when they said in their report, "It has become obvious that audiences around the world have grown weary of the violence and crime which plagues the streets--children shooting one another in schools, and the simple truth that we, as a people, have lost our way in the world. As society searches for the answers that will nourish their souls, they will be looking to the storytellers who influence and educate society through entertainment."</p>

<p>Of course lofty goals were held out for radio too, but it was a bruising Dempsy boxing match piped into the Waldorf Astoria Ballroom in New York City that finally "enlightened" and motivated the investors of the day to jump on board. So, I am not saying that HDTV and saving the world go hand in hand...but one thing I feel strongly about is the idea of not squandering any of the potential public good that HDTV can deliver, especially for any superficial reasons. I, for one, would hate to make an accounting of myself to civilization for NOT finishing what I started. This is a rare opportunity made possible from the integration of all technical and cultural knowledge to date. All of that makes some heady shoulders upon which we can stand. In a phrase--don't blow it.</p>

<p>Every market impediment is an enemy of this last idea. They do horrendous damage to the retail side of HDTV, which grievously impacts everything from engineering to content. Buyers do do their research. After all, love it as we may, HDTV is still expensive for all but a few to be an impulse item. It takes no time, however, to find those nasty blocks raised, like these copy protection circuits which have yet to be settled upon and included into the decoder/monitor package. Over the years I had learned from my consumer electronic mentors, and have so reported, that ANY confusion introduced into the marketplace causes that market apply its breaks and to stall or suffer a regression. A major retail chain executive told me during the COFDM/8-VSB debate that if the standard were to be changed they would no longer handle HDTV because the cost of re-educating and reestablishing the trust of the consumers would outweigh any other of the retailer's benefits.</p>

<p>By far the copy protection issue, which is initiated and pushed by Hollywood's financial backers, has done more damage to the HDTV retail set top box/decoder movement than any other issue. It lingers there like an old haunting memory saying over and over to buyers, "Don't waste your time and money. Everything is going to change." But those in the debate are so fiercely competitive that no agreement can be found--no idea can be declared so superior that a counter movement cannot be made equally effective. No government legislation can cause it. It is only finalized with the outburst and outrage of the public, who you remember love HDTV. Their love is spurned and they will not take it beyond reason. Once the public is for an issue, no industry can stand against them. With this understanding we have organized in Washington the High-Definition Television Association of America, which is designed to be the collective force of the public voice in Washington and any where else where there appears to an obstruction to our love affair with this remarkable "box of lights and wires."</p>

<p>These rights owners of high-value content (and let's call them new movies, which is what they are) believe they have to protect their assets against a hoard of miscreant citizens--you and me, by the way--whom they believe in the depths of their deluded hearts are bent upon mishandling their product as well as undermining their/our own welfare. This accusation from your chief of all entertainment sources has manifested itself in tricky hardware and software measures which simply leave the average consumer doing what they have always done--turning on and off their TV set. The average American has a VCR, but who really uses it for time shifting? Who uses it to make copies of even PBS programs, for example. I offer this example because people who own VCRs purchase these tapes right after their showing! PBS makes a handsome return selling what they give away un-encrypted every day.</p>

<p>We should, of course, have sympathy, along with a good deal of respect, for anyone who takes a risk that, in the final analysis, is to our benefit. We can bitch to high heaven about talent being over-paid, but our lives would only be poorer if they didn't take the chances they do and deliver the products to us that they do. We are not denied their mansions or their so-called glamorous life because of the few dollars a year we spend with them. No mansion of theirs becomes mine if they should make less reward or more. All of their activity is, bottom line (even the titillating scandals that have replaced the court intrigues of old) are to my enrichment, and I want to honor that.</p>

<p>One in five movies (at least it used to be this ratio) make money enough to pay for the rest, which either break-even or lose their shirt. Many movies would never recoup their investment without non-theatrical sales. Our respect for this fact is the only thing that is going to keep the flow of such productions coming our way.</p>

<p>I was advised by a former council to the MPAA that it is the financial institutions, upon which they all urgently depend, who most-rule the copy protection mission. Those institutions know less about the entire field of digital and entertainment than any others in the program supply chain, and they live in a perpetual state of conservative paranoia. No analyst I am aware of has looked favorably upon HDTV as a real business. No analyst I know has ever sent out a glowing report on the economic benefits of HDTV to ANYONE, much less the movie business, where a hit is pure gold. This gold is not transported now in an armored car, but rather down phone lines, the air, over cable wires, and raining down from satellites. I don't want to steal anyone's gold, and I am having a little trouble with being accused of doing that kind of thing, of condoning it in the very least. I would be horrified if anyone in my family were to steal their neighbors goods. Yet, I am asked to pay the price for locking-up Hollywood's gold. Why don't they think of me as a responsible custodian of their goods? Why am I thrown in with some other lot, and asked to pay the price?</p>

<p>There is talk that legislation will be needed to put to rest this copy protection issue, at least technically speaking. But critics of such a proposal far out weigh and outclass those seeking that remedy. No one sees any legislation that would provide the kind of flexibility that fast moving technical innovations must have to remain competitive. The NTSC standard is more than 50 years old without barely a revision. That is a clear enough of an example of what happens to innovation when nailed down by government decree. And the NTSC standard was not that securely nailed down at that, but what was, nailed the stanard to the wall. There is some kind of legislation thougths going on proposed from Senator Hollings office and, correctly if I am wrong, Billy Tauzin's office. And, I suppose others, have legislative ideas and agendas for copy protection solutions. It's not that government is bad, but rather it is the time it takes to resolve matters where many interests are given space to jockey for advantage, just as it should. This freedom to win the prize in a competitive culture is to me one of greats payoffs we havewith a Democracy. Democracy supports all directions in life with conscious limits set for those who otherwise smash into the walls. You only want to anchor by government decree that which will not morph into something else quickly on its own.</p>

<p><br />
Even in their most optimistic forecasts the Consumer Electronics Association shows HDTV at only a 12% penetration of US households by the year 2006 (a theoretical date for when the old analog services would be shut down and that spectrum recovered for FCC/Government auction). That is still an insignificant percentage in the consumer realms. We need to exceed that forecast if we are going to be beneficiary of a well-propelled growth in new, high value content aimed at this medium.</p>

<p>While there are some in the major television networks who believe that the FCC can light a torch to the feet of broadcasters and make them toe the FCC deadlines for installing DTV transmitters, no executive I know believes that any legislation from Congress can do any good in spurring the consumer market. That is a voluntary market which may be led a bit by some action of the Commission, but in cities where every TV station is DTV operational market take-up rates are not significantly greater than areas where satellite is available. I hope I am wrong on that observation. Anyone have hard numbers?</p>

<p>Fears over the copying of original material has been with us since the printing press. Scribes sought its annihilation, seeing in it the finish of their careers. Zerox suffered countless attacks from publishers and audio recordings and video recordings each drew their fair share of blood in rights management battles. Many will remember Jack Valenti, president of the MPAA, predicting a disaster for the movie making industry from all the unchecked copying that VHS would do. The outcome we all know is different. It is likely that some of Mr. Valenti's sustenance comes from his seven constituents amply afforded by the enormous pot of gold the VHS introduction has provided to Hollywood. No dire consequence occurred.</p>

<p>Since digital makes perfect copies generation after generation the fear is greater than with the VHS case (where 3 generations deep made it unwatchable). Digital could be endlessly propagated. More copies are then copyable, and on and on it goes down the line. It's 'word-of-mouth,' only instead of sharing words, you share movies and that denies Hollywood the unfilled marketplace where they can sell their movie I gave away. That, Hollywood wants to protect against becoming an established habit among millions of otherwise decent citizens in our nation. This fear of our being bad has fathered the copy protection initiative. If you are not bad, you should be a bit offended by what you are being asked to do, as well as quite understanding that not all of our fellow citizens are all that good. You got to do something to either protect against or really stop theft. So, until someone else comes up with a better solution, the guy risking his/her money builds a vault.</p>

<p>The first thing everyone needs to understand is that no copy protection scheme--no vault--is going to outlast the hardware in which it is implemented.</p>

<p>Let me take you somewhere: Last night I had one of the strangest dreams in my entire life. In this dream all structure in life was made void. Everything from mathematical formulas to axioms in science to dogma and laws of social order were entirely dissolved. We were completely free agents in a new form of social and material stuff that operated complimentarily, leaving everyone free to navigate throughout it at will and without restraint. At first I thought there were no consequences to anything one might do in this new found freedom. But as the dream unfolded I could see that the consequences for doing wrong, while not immediately generating personal opprobrium, were just as devastating as ever upon the whole.</p>

<p>I began to learn .from the dream's premise that regardless of the freedoms we gain or whether law and governance are absent to enforce a decision, we still have to make that same right choice as if it were the death penalty not to. Even though our free will had grown in this dream beyond any material order-making, fences, barriers, or restraints our lives were dependant upon our acting as if they were still there.</p>

<p>Frankly, it was a disturbing dream. I began to realize that I had entered into the pure digital age where everything is possible with nothing being stoppable. Any and everything was doable at my will. I could run through an intersection. I could take from a shelf what I wanted. No rules forced compliance to any kind of behavior. But there were consequences to be seen. If I failed to stop at a corner I might cause a wreck. If I took goods from the shelf without compensation, I undermined my own economy. In my dream I had gone to an era where only personal self-governance was the backbone and the salvation rather than laws and locks. No barriers, no walls, no restraints outside of my own mind would work to keep order in this strange new, and infinitely free land.</p>

<p>What we are arguing over, fundamentally, in copy protection is the method for preserving a viable economic system. As in my dream there are no more effective walls, barriers, nor restraints in the high-speed digital era hard on the threshold. What I realized with this dream is that we have no choice but to construct a behavioral order which is geared to live in such a super-free world. While this is easier said than done, it is never done until said. Societies do right themselves once they understand that the payoff for doing so is far superior to the spoils of thieves. It's as obvious as the value of clean water. We stop polluting it and everyone can drink long and satisfyingly from the well again.</p>

<p>Going back to today where locks and laws prevail we have to realize that no lock is going to stay latched long in this digital universe. There is always a work around, especially when speaking about the Internet. It was designed, after all, to survive and to keep on delivering the 'goods' even in all out war. No copy protection system is ever going to be left uncompromised. Bernie Lechner, the honoree of this year's NAB lifetime of distinguished service to broadcast and television engineering said to me that if Hollywood doesn't want its movies copied they will have to keep them in a vault until a showing, frisk all who enter the theater for a camera, and after the showing return it under armed guard to the vault. That, said this distinguished American fixture of television science, was the ONLY way it could be protected. Everything else is destined to fail. I say everything but a deep social realization that we should act as the agent for our content providers and protect the value of their property by encouraging the economic engine that produces these pieces we covet. And besides, who wants a gaggle of entertainment dependents showing up at your door every Friday night to partake of your largess? I don't. Invite them in for a drink. Show them HDTV. Spread good cheer and go to bed with a clean conscious. Otherwise you will be treated by Hollywood like criminals, frisked at the store and watched as in a George Orwell nightmare...and it cost you a bundle and they go to the bank with an entirely negative view of their best customers--all seen as thieves in the night protected by temporary locks we must change periodically at our own expense just to receive the voice of the arts we will need to pull us past 9|11. Get real. Get sensible. Pledge to support the economy rather than steal from it.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>August  4, 2005  5:45 AM</b>
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
			<?=getComments(173)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 173)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/08/2001_thoughts_on_hdtv.php" type="text/javascript" charset="utf-8"></script>
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