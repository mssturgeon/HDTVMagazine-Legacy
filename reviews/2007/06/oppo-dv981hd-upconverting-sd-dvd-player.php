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
		AND e.entry_id = 613";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 613 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 613 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 613";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2007/06/oppo-dv981hd-upconverting-sd-dvd-player.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 613";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download OPPO DV-981HD Upconverting SD DVD Player" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="OPPO DV-981HD Upconverting SD DVD Player" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="OPPO DV-981HD Upconverting SD DVD Player" />
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
	<title>HDTV Magazine - OPPO DV-981HD Upconverting SD DVD Player</title>
	<meta name="keywords" content="dvd audio, frequency response, oppo players, sacd dvd, dvd player, dvd, oppo, audio, output, video, players, disc, player, multichannel, response, analog, hdmi, sacd, receiver, performance, pcm, frequency, price, while, does" />
	<meta name="description" content="In the summer of 2005 I came across a well regarded DVI upconverting DVD player from a new kid on the block by the name of OPPO and decided to give it a whirl. This culminated in August of 2005 into a Review in Progress report for the OPDV-971HD in which I gave the product a very high rating. I have been recommending OPPO ever since. 

Since then, OPPO has released another model, the DV-970HD. The DV-970HD added HDMI output, SACD and DVD Audio, analog audio with the ability to turn the video stages off for better audio reproduction, and a unique 480i output directly from the disc for the scaling videophile." />
	<meta name="title" content="OPPO DV-981HD Upconverting SD DVD Player" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="OPPO DV-981HD Upconverting SD DVD Player" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2007/06/oppo-dv981hd-upconverting-sd-dvd-player.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In the summer of 2005 I came across a well regarded DVI upconverting DVD player from a new kid on the block by the name of OPPO and decided to give it a whirl. This culminated in August of 2005 into a Review in Progress report for the OPDV-971HD in which I gave the product a very high rating. I have been recommending OPPO ever since. 

Since then, OPPO has released another model, the DV-970HD. The DV-970HD added HDMI output, SACD and DVD Audio, analog audio with the ability to turn the video stages off for better audio reproduction, and a unique 480i output directly from the disc for the scaling videophile." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=613', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/06/oppo-dv981hd-upconverting-sd-dvd-player.php">OPPO DV-981HD Upconverting SD DVD Player</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>June 14, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=279&category=Upconverting DVD Players">Upconverting DVD Players</a></b>
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
				<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/?partner=826"><img src="/images/products/oppo-dv-981hd.jpg" alt="OPPO DV-981HD" /></a><br /></p>

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/?partner=826">$229.00</a></td>
<td class="greygrid"><a target="_blank" href="/equipment/model.php?man=OPPO%20Digital&model=DV981HD">$229.00</a></td>
<td class="greygrid"><a target="_blank" href=" http://www.amazon.com/gp/product/B000LU8A7E?ie=UTF8&tag=hdtvmagazine-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=B000LU8A7E">$229.99</a></td></tr>
</table>
<br />
Serial #VD0644601484<br />
Warranty: 1 year parts and labor<br />
<br />
<B>Summary: OPPO has an SD DVD upconverting player for both videophiles and casual viewers at the right price</B><br />
<br />
In the summer of 2005 I came across a well regarded DVI upconverting DVD player from a new kid on the block by the name of OPPO and decided to give it a whirl. This culminated in August of 2005 into a <a target="_blank" href=" /forum/viewtopic.php?t=5548">Review in Progress report for the OPDV-971HD</a> in which I gave the product a very high rating. I have been recommending OPPO ever since. 

<p>Since then, OPPO has released another model, the DV-970HD. The DV-970HD added HDMI output, SACD and DVD Audio, analog audio with the ability to turn the video stages off for better audio reproduction, and a unique 480i output directly from the disc for the scaling videophile.</p>

<p>The focus of this review is the recently released DV-981HD. This model provides 1080p output and multichannel PCM audio via HDMI for SACD and DVD Audio. Excluded from this model is the native 480i output via HDMI external scaling. This also happens to be the only model that comes in black, the preferred color for home theater systems.</p>

<p>As expected, neither the OPDV-971HD nor the DV-970HD will allow upconversion via the analog component video outputs. This connection is limited to 480p. The DV-981HD does not even provide component video output due to the predominance of HDMI and DVI connections on most modern displays.</p>

<p><br />
<B>Common Features</B><br />
OPPO has done a great job outlining these features on their website. Please refer to the links below for the features and specifications of each model. Also available on these pages are firmware upgrades and owners manuals.</p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/opdv971h.html">OPDV-971HD</a> (1st generation, DVI output)</p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/dv970hd/dv970hd.html">DV-970HD</a> (2nd generation, HDMI output)</p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/dv981hd/dv981hd_features.html">DV-981HD</a> (3rd generation, HDMI output)</p>

<p><br />
<B>Not-So-Common Features</B><br />
<ul><li>DCDi by Faroudja video processing technology</li><li>User adjustable video controls: Sharpness, Contrast, Brightness, and Color Saturation</li><li>PAL/NTSC disc and TV compatible with automatic or manual system conversion</li><li>No Analog Component Video on the DV-981HD model; requires HDMI or DVI digital video input. It does provide s-video and composite video connections.</li><li>High-resolution multi-channel digital audio output through HDMI supporting CD, DVD-Audio, SACD, Dolby Digital and DTS sound tracks.</li><li>Optical and Coaxial digital audio output</li></ul></p>

<p><br />
<B>Opening the Box</B><br />
OPPO has changed their packaging somewhat in an effort to lend itself better to internet sales and individual shipping.  Nonetheless, I was left with the same feeling of unexpected quality at this price point. The unit still comes in a nice bag, well packaged and includes all you need for an easy start. OPPO has also changed their remote somewhat from the original OPDV-971HD, but do not expect much here. While certainly not haling from the land of cheese, it is not back lit and button layout veers more towards a tabled layout with button shape similarity, adding to the confusion. While it has glow in the dark keys, that won't help much once the glow has extinguished itself in your darkened room. I don't place too much emphasis on remotes though as most folks use a system remote for everyday use.</p>

<p><br />
<B>Out of Box Performance</B><br />
Hooking up the player to a BenQ W10000, I found it preset for 16:9. I adjusted the output for 1080p and ran the DVE test material. Looking over at the receiver it said stereo. Going into the setup menu I found down mix set for stereo and changing that to 5.1 took care of that. To get linear PCM you will have to change a number of settings, all documented on page 18 of the <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/dv981hd/download/dv981hd_manual.pdf">owners manual</a>. After about an hour of looking at DVDs, nothing appeared to be wrong, so on to objective testing.</p>

<p><br />
<B>On the Test Bench </B><br />
The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection and copy protection since the means to see it would infer a means to steal it. At this time, the Panasonic PTAE-1000U (<a target="_blank" href="/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">recently reviewed</a>) has been kept in the lab just for this purpose because it has the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video ,such as bursts, it is also the perfect tool for checking IRE levels and color decoding. It is limited to only being able to check YPbPr output. It is unable to verify the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and is noted. All tests were performed using Digital Video Essentials as the source material.</p>

<p><br />
<B>Video Levels</B><br />
Whether by visual calibration or waveform monitoring, all of the OPPO players output 0IRE and 100IRE at the correct 16/235 levels.</p>

<p><br />
<B>Below Black</B><br />
Video content is 0-100IRE. The ability to pass a below black signal (lower than 0 IRE) is required to properly use the video standard pluge pattern for setting or confirming the black level on the display. The OPPO passed this test</p>

<p><br />
<B>Color Decoding</B><br />
Whether by visual calibration or waveform monitoring, all of the OPPO players output correct color decoding at HD scan rates, 720p, 1080i, 1080p.</p>

<p><br />
<B>Horizontal Frequency Response Luminance</B><br />
As noted, Waveform Monitoring response was useless for this test. Visually the players pass the continuous frequency burst test quite well for luminance. For the low frequency pattern there is some banding for the highest frequency burst. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly and the OPPO players are no exception. This pattern always has banding so the best I can state on this is high, medium or a low contrast response with high being the best and low being the worst. For the OPPO players a medium contrast response was the norm; a typical response compared to others.</p>

<p><br />
<B>Vertical Frequency Response Luminance</B><br />
Vertical frequency response was excellent in 1080p. 1080i showed banding and a drop in video level response. With 720p the player could not figure out which dark and white stripes it should favor with white being predominant in the top burst and black predominant in the bottom burst.</p>

<p><br />
<B>Frequency Response Color</B><br />
All OPPO players showed some banding, which is quite normal. The contrast levels remained fairly equal from low to high. I have seen slightly better definition though using scaler/player combos.</p>

<p><br />
<b>CUE, Chroma Upsampling Error</b><br />
This causes a vertical breakup of color detail in the vertical plane, typically expressed in reds, but can show up for other colors and is related to the player using only one MPEG decoding method rather than both interlace and progressive, and applying the correct version to the native source on the disc. All OPPO players pass this test.</p>

<p><br />
<B>Aspect Ratio Control</B><br />
The DV-981HD provides an auto 16:9/4:3 switching mode so the player maintains correct aspect. With special features or 4:3 movies, black side bars are used. OPPO calls this 16:9 Wide/Auto and is found in the setup menu.</p>

<p>For the DVD collector looking for great performance, the OPPO does have an Achilles Heal when it comes to letterboxed sources. While a combination of the Wide/Auto setting along with the zoom feature will properly fill out your screen, the image you get is, simply put, terrible. This is due to a bonanza of aliasing errors and others. One solution is to use the internal scaler of your display and live with those artifacts; the very reason you would buy the OPPO in the first place. Putting in perspective, most players don't have it and these days new 4:3 letterboxed titles destined for the US are basically history. These were either a byproduct of films that were mastered for the old laser disc widescreen format or are from content providers using the original master instead of producing a new one to save a buck on the DVD version. Taking all that into account, this is a very rare problem for the movie itself. </p>

<p>On the other hand if you are into special features or foreign origin DVDs, much of that content for SD DVD is still letterboxed. HD disc, Blu-ray and HD DVD do a great job at excluding, scaling or cropping this material for your 16:9 screen or display in HD so no adjustment is required. If you are a passionate videophile then your best bet is to use an external scaler/DVD player combo. The DV-970HD is designed for that application but was not tested for this review.</p>

<p><br />
<B>Scaling</B><br />
I tested the OPPO at 1080p, 1080i and 720p feeding a 1080p DLP front projector that supports 1:1 pixel mapping with other scan rates. As expected the edges were soft, which is a byproduct of the scaling process for nearly any manufacturer. Color bar patterns showed the typical dark edging where the different colors met.</p>

<p>Moving on to test images from DVE at 1080p I was greeted with a very good response. The unit is not perfect, but perfection for SD comes at a very high price. The typical line interpolation errors and aliasing showed up although far less at 1080p. At 720p the process loses 44% of the pixels that 1080p provides and naturally has more of these errors. The most interesting observation occurred with edge definition and 1080p. Comparing 720p to 1080p it became quite apparent that having 44% more pixels greatly contributes to harder and more distinct edges since the change from peak black to peak white takes place over a smaller area by comparison. This was expected technically, but it was nice to see visual verification of this theory. An increase to a 2k x 4k imaging chip would double the pixel count yet again easily providing SD DVD edges nearly as distinct as HD! Putting this in perspective, you would need a viewing distance less than 3 screen heights to perceive this benefit and that is typically reserved for front projection and large screens.</p>

<p>The next disc up was Star Wars Episode II, a disc I have tested to no end over the last year with numerous products. I recently stepped into 1080p land and while I found HD DVD and Blu-ray a thrilling experience, I happen to run this movie through the Toshiba HD-A1 output at 1080i and was not pleased. We watched 2 more DVD movies at that time and I gave up on my 10 foot wide 1080p for SD DVD and watched those on the upstairs 720p 50" DLP system instead. Then came this player. Using the OPPO DV-981HD at 1080p output instead provided the cure! The artifacts were basically gone and I found myself involved and sucked right in very quickly! That is always a hallmark of a videophile accomplishment.</p>

<p><br />
<b>Additional Video Features</b><br />
<ul><li>True Life Enhancement<br />
This is a complex edge enhancement process. Whether or not to use this feature is debatable. In testing I found it to be quite subtle if non existent at times. It can add a bit more dynamic look with the right video content. In the end I preferred it off for accuracy.<br />
 </li><li>Motion adaptive Noise Reduction<br />
Typically turn this off. For the most part, it will only suppress film grain and that is decided by the mastering house and producers. That is the artistic part of film making. If you don't like film grain use this feature.<br />
</li><li>Cross Color Suppression<br />
This feature relates to composite video sources used for mastering to DVD. The site explains this quite well. This applies to some special features only and the rare DVD in which the main content was mastered that way (I have one). Most content these days is captured and stored as YPbPr eliminating the cause of this artifact.</li></ul></p>

<p><br />
<B>Audio Performance</B><br />
Video testing was easy and straight forward. Audio testing created a fog of unknowns and lots of research related to specifications of products stating what they can or cannot do. Both OPPO players claim SACD and DVD Audio compatibility, which by itself only infers that those formats will work, not that they will be fully implemented. The Pioneer VSX-81TXV receiver I used for testing comes with its own fog, never stating what multichannel PCM streams it will support. This creates a dilemma for those passionate about performance since these products clearly worked together yet failed to state or output the correct version for 24/192 DVD Audio or arguably SACD. OPPO's specs for all three players state the analog audio frequency response to be 20hz-20khz, clearly a CD spec and not high enough for HD audio sources. For the OPDV-971HD, which does not support HD Audio, they not only state it uses a 24/192 DA converter but the chip used as well. And for the DV-970HD, they state Optimized analog audio circuitry for great audio quality and Unique "Audio Only" mode with video processing turned off for perfect acoustic fidelity, which infers it has at least 24/96 DA converters if not 24/192 which does not agree with the frequency response spec. Back in the day of newly released DVD Audio, the players audio section had frequency response specifications for all three rates, 16/44, 24/96 and 24/192.</p>

<p>I asked OPPO about all this and the official response was that both players have 24/192 DA converters for analog audio and it will output a digital 24/192 2 channel PCM stream with 24/192 DVD Audio discs. I would like to thank HDTV Magazine Tips List member Brent Wagner for verifying that he was able to get 192khz to display on his Pioneer VSX-84TXSi receiver using HDMI and a 24/192 encoded disc.</p>

<p>All in all analog performance was average at best and for the average multichannel system it will suffice. Considering the price of either player, audiophile analog conversion of SACD and DVD Audio sources is not a reasonable expectation and none of the OPPO products provide any ground breaking surprise in that department. If you are an audiophile seeking audiophile stereo or multichannel analog performance outputs you will have to look elsewhere. Many will find the analog output satisfying and an improvement when comparing CD sources to SACD or DVD Audio on either OPPO player. That said there was little comparison to my reference Sony SCD777ES for SACD or modified JVC XLV720 for DVD Audio. The lack of transient response, clarity and neutral sonic signature was evident. It could be argued that an audiophile performance CD player is comparable in many ways to HD audio via the OPPO. In the end both players provide a good entry level audio signature that is to be credited for a smooth response while not doing anything grossly wrong or irritating causing listener fatigue. On the other hand both players provide multichannel PCM streams via HDMI for a capable receiver and this is where some form of audiophile nirvana is to be found.</p>

<p>I tested the OPPO PCM stream using a Pioneer VSX-81TXV A/V receiver which provides 24/192 DA conversion for the outputs. In this mode I was able to duplicate all DVD Audio formats out to 24/96 yet when a 24/192 disc was put in the player the receiver stated 96khz on the front panel. When an SACD is played on the OPPO the Pioneer indicates 88.2khz. In this area things became far dicier for me as a reviewer. My 2 channel system is composed of custom and modified products designed and setup for the ultimate expression of a neutral audio signature. My multichannel setup is clearly a compromise by comparison using off the shelf stock products with a speaker arrangement that is not optimized for multichannel applications. Accordingly, I need to keep all of this in perspective in my comments.</p>

<p>The best place to start in tearing this down is the source, player and disc. The vast majority of DVD Audio is 24/96 multichannel encoded so based on that you potentially have a clear shot from the disc multichannel decoding to the DA converters on your receiver, provided the OPPO is decoding the bitstream off the disc properly. To acquire this in your system requires you setup the OPPO for the PCM stream and the OPPO audio menu for multichannel speaker setup, setting your speakers as small with subwoofer on so the decoding remains native to the source. It was in this mode with 24/96 sources that the two HD audio capable OPPO players shined the best without question. So much so that I am strongly considering revamping my multichannel application for better performance to provide a more in depth sonic experience! Beyond that things begin to get dicey due to conversion of other formats like SACD and 24/192 PCM sources. With 24/192 I can only state that like 24/96 if your receiver supports it then it will sound as good as the receiver. Whether in multichannel or stereo mode, SACD was lacking in clarity. Not only was it being down converted* to 88.2khz, but also being converted from DSD to PCM. That said there were other aspects of SACD multichannel playback compared to top notch stereo that provided pause in my evaluation. To go in depth would extend the length of this review dramatically in trying to provide perspective on those technical issues. Ultimately, getting the full potential of SACD performance is an audiophile concern and I suggest an audiophile stand alone player. I know that is not an easy or inexpensive product to find. It is unfortunate, but in the end maintaining a pure unconverted signal for SACD from source to decoded analog output whether that be stereo or multichannel analog or digital is a huge challenge for the end user on a budget.</p>

<p>This also drew comparisons between a decoded multichannel PCM stream and the raw bitstream off the disc feeding the receiver for DVD movie soundtracks. Splitting hairs, I found I preferred the bitstream for an edge in overall clarity. This brings up the current debate concerning HDMI 1.3 support for HD Audio bitstreams which would provide a straight shot from the disc to your receiver. While a digital PCM multichannel stream is typically considered superior to an analog multichannel input, there remains a process of conversion that will take place as the channels are sliced, diced and converted to the settings you have designed for your room in the receiver. The only to way to get a straight shot through the receiver is to turn all such processing off as if it was a direct analog feed and output directly to the amps. But that would defeat the purpose and improvements gleaned by room correction. With a bitstream, the decoding and processing all take place within the same domain reducing conversion steps. The flip side of this argument is digital, but any videophile with external scaling also understands conversion of anything from its native form generally creates artifacts and cross conversions only create cumulative errors. At this point, this debate has few players since HDMI 1.3 sources and receivers are rare at this time so I bring this up as a reference for my experience and a heads up to readers passionate about the new HD disc formats performing at their peak capability.</p>

<p><br />
<B>Problems</B><br />
None</p>

<p><br />
<B>Service</B><br />
This is one of those rare moments where I can report from direct experience. OPPO is great. I lost my OPDV-971HD during the warranty period. I called them up explaining I was a service center and they sent me a part! While it did not resolve the problem they deserve kudos for providing that potential convenience. I ended up having to ship it back but lo and behold they offer a prepaid service so I could simply order one and send the old one back for credit. If I was needy I could have also had them overnight one, naturally at my expense. Now that is service!</p>

<p><br />
<B>Putting It in Perspective</B><br />
I think the review says it all; you can't go wrong with this product at this price. To do significantly better will require far more money, well over $1000. What you would gain is better imaging for maybe 10-20% of the time and that kind of concern puts you in the videophile 5-6 figure home theater money league. For general everyday audio performance using an HDMI equipped receiver accepting linear PCM or analog multichannel inputs you have access to thousands of HD audio titles. If you are an audiophile though you can do far better and this is not the right product for such a demanding application.</p>

<p>With HD DVD and Blu-ray players hitting the market do you really need yet another box, remote and available connection to deal with? The first generation Toshiba HD DVD players did OK with upscaling but the OPPO is better. Blu-ray on the other hand has just hit the market from a variety of well known manufacturers who have their own history of decent performance as well as new HD DVD models from Toshiba supporting 1080p for both HD DVD and SD DVD and the difference in performance may not be enough to justify. For my system I have a first generation Toshiba HD DVD player, Xbox 360 and a Sony PS3. As pointed out, the Toshiba is not as good. The Xbox 360 is stuck at 480p and won't upconvert SD DVD for the standard analog component connection. Until recently it had an SD DVD black level error as well but that has been fixed. As this article was published the Sony PS3 was upgraded to support 1080p upconversion of SD DVD and based on a preliminary evaluation it directly competes with the OPPO's level of performance. For my application and passion for quality, the DV-981HD fits the requirement at the right price even though SD DVD is going by the wayside in my HD system.</p>

<p>What about the other models? It depends on the application. The DV-970HD is $149 and you can certainly save some dough if your display is not 1080p or does not support a 1080p input. That said, if 1080p is in your near future I would suggest the DV-981HD. If you are running an external scaler the DV-970HD does provide the raw 480i output from the disc for the best possible processing, costing far less than modified DVD players enabled for SDI. It is a natural for that application and also the intent of providing the feature! To be clear, this feature was not tested for this review. The OPDV-971HD is $199 and the main purpose to buy it is for a legacy display that uses DVI inputs. While DVI is compatible with HDMI there are various reasons as to why one might want DVI exclusively and such reasons will have been brought to your attention by your calibrator or HT installer due to your unique situation. An explanation here would be lengthy and apply to very few users.</p>

<p>Finally we come to the sad reality; your application and either one of the HD disc players may make the OPPO unnecessary. Starting at the bottom price range a Sony PS3 is $600 and a Toshiba HD-A20 about $450. Take the cost of an OPPO out of those prices and you can see the dilemma you have with price versus overall capability. In my opinion OPPO needs to get involved with the HD disc formats or they will be left with great SD DVD players that no longer fill a need if either or both HD disc formats blast off. As of today, due to the Sony firmware update for the PS3, I have no real need for the DV-981HD either for SD DVD.</p>

<p><br />
<B>Conclusion</B><br />
OPPO continues to give other far better known manufacturers a great deal of competition, not only at this price point but even higher ones hovering at $1k plus. While certainly not perfect nor to be expected at this price point it excels in what it does right and for what it does not do wrong providing very clean imaging for SD DVD videophile applications and decent sound for SACD and DVD Audio. Ultimately this product comes highly recommended for videophiles and most users!</p>

<p><br />
<b>Other Reviews</b><br />
<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.hometheaterhifi.com/cgi-bin/shootout.cgi?function=search&articles=124#OPPO%20DigitalOPDV971H%20(DVI)">OPDV-971HD Home Theater Secrets Review</a></p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.hometheaterhifi.com/cgi-bin/shootout.cgi?function=search&articles=130#OPPO%20DigitalDV-970HD%20(HDMI)">DV-970HD Home Theater Secrets Review</a></p>

<p><br />
<b>References</b><br />
*<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.smr-home-theatre.org/surround2002/technology/page_07.shtml">Poking a round hole in a square wave</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>June 14, 2007  6:54 AM</b>
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
			<?=getComments(613)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 613)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/06/oppo-dv981hd-upconverting-sd-dvd-player.php" type="text/javascript" charset="utf-8"></script>
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