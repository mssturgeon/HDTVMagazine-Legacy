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
		AND e.entry_id = 5180";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5180 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5180 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5180";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2014/01/nuvola-4k-player-np1.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5180";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Nuvola 4K Player (NP-1)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Nuvola 4K Player (NP-1)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Nuvola 4K Player (NP-1)" />
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
	<title>HDTV Magazine - Nuvola 4K Player (NP-1)</title>
	<meta name="keywords" content="media player, second generation, – uhd, playback device, blu ray, player, hdmi, content, audio, nuvola, video, sony, available, –, output, digital, unit, generation, second, device, capable, playback, announced, streaming, hevc" />
	<meta name="description" content="NanoTech Entertainment has just announced the introduction of their Nuvola NP-1 4K player that claims to be compatible with all UHDTVs regardless of brand, as opposed to the proprietary player/TV pairing setup Sony has done with their current 4K media..." />
	<meta name="title" content="Nuvola 4K Player (NP-1)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Nuvola 4K Player (NP-1)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2014/01/nuvola-4k-player-np1.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="NanoTech Entertainment has just announced the introduction of their Nuvola NP-1 4K player that claims to be compatible with all UHDTVs regardless of brand, as opposed to the proprietary player/TV pairing setup Sony has done with their current 4K media..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5180', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2014/01/nuvola-4k-player-np1.php">Nuvola 4K Player (NP-1)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 15, 2014</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=30&category=Products & Equipment">Products & Equipment</a></b>
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
				<p><a href="http://www.nanotechent.com/">NanoTech Entertainmen</a>t has just announced the introduction of their <a href="http://nuvola4k.com/">Nuvola NP-1</a> 4K player that claims to be compatible with all UHDTVs regardless of brand, as opposed to the proprietary player/TV pairing setup Sony has done with their current 4K media player (<a href="http://store.sony.com/gsi/webstore/WFS/SNYNA-SNYUS-Site/en_US/-/USD/ViewProduct-Start;pgid=Cdp8WvMNFHNSRpCmsPAkSpqA0000bER0x0Sr;sid=HQmdr7qE33mhr-hTdyflqyKO1DLTanO_U8y5y8aY?SKU=27-FMPX1">FMP-X1</a>) and their UHDTVs/<a href="mailto:http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-getting-the-beautiful-monster-part-1.php">4K Projectors</a>, which limits their 4K media player connectivity to only Sony UHDTVs/4K Projectors.</p>

<p><br />
The other 4K player competitor, the $1750 <a href="http://www.hdtvmagazine.com/articles/2013/01/living-with-4k-the-redray-4k-digital-cinema-player.php">Red Ray 4K player</a>, has been unavailable for over a year after their “reserve your unit” announcement in late 2012 with an original price of $1400+.  According to an email I received from the manufacturer last week: <em>“The unit <a href="http://www.red.com/store/products/redray-player">is available on our website</a> with an estimated ship date of 2 – 3 weeks… and we are not distributing units for review at this time”.</em>  I still hope they are going to deliver a player as promised and that it will be successful, as well as <a href="mailto:http://odemax.com/odemax/about">Odemax</a>, their 4K content provider partner, because the 4K market needs <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-4k-content-when-part-2.php">more content</a> and players to play it, not just UHDTVs. </p>

<p>When considering the three 4K player options the Nuvola NP-1 appears to be the best deal in price and compatibility, for now, and it <a href="http://www.businesswire.com/news/home/20140106005986/en/NanoTech-Entertainments-Nuvola-NP-1-Awarded-Storage-Visions#.UtdQSNJDu3I">was honored</a> recently with a Storage Visions® 2014 Visionary Product Award in the Home Entertainment category.</p>

<p><br />
 <br />
 <br />
 </p>

<p>  <br />
I am expecting a Nuvola NP-1 review unit to arrive in February 2014 as the manufacturer promised this week, so I can test the quality of the player and the 4K content with a <a href="http://www.hdtvmagazine.com/articles/2012/10/living-with-4k-getting-the-beautiful-monster-part-1.php">Sony 4K projector</a>.  Meanwhile, I thought the readership would appreciate this heads up article that compiles my exchanges with Mr. David R. Foley, Nano Tech’s founder and creator of the NP-1.</p>

<p><strong>Primary Differences between Sony and Nuvola 4K Players</strong></p>

<p>A key difference between the Sony 4K player and the Nuvola 4K player is that the current Sony FMP-X1 4K player has been introduced for downloads of 4K content distributed by Sony, but that may change with a soon to be available Netflix 4K partnership effort announced at CES 2014, and with a streaming prototype media player announced by Sony compatible with the “Video Unlimited 4K” premium 4K distribution service of Sony (more details further below).  </p>

<p>How the Nuvola 4K player differs from the Sony is that it has been introduced for primarily streaming 4K content sourced from several providers, and if desired it can also download the 4K content and store it into an externally connected hard drive using its USB 3.0 output, more details further down.  </p>

<p>Another difference is price, the Sony 4K player is $699 and the Nuvola 4K player is $299. </p>

<p>Another difference is regarding digital audio/video connectivity, the Sony player has two HDMI ports, one HDMI can be used to connect to the 4K display for the 4K video to be delivered undisturbed and the second HDMI can be used to output multichannel digital audio to a home theater audio setup that cannot pass-thru 4K video, in other words, a parallel HDMI connection, one for 4K video, one for multichannel digital audio.   If the user has an audio equipment that can actually pass-thru 4K video then the parallel HDMI connection would not be necessary. </p>

<p> </p>

<p><br />
 <br />
How the Nuvola player differs is that it has only one HDMI output for the video and audio, and no extra digital audio connection.  The implications of this limitation are described further below in the audio section. </p>

<p>Another difference relevant to installations of 4K projectors is that Sony’s 4K player requires the use of a new Sony Xperia tablet to control the player and to download content, which increases the total cost of ownership to about $500 on top of the $699.  </p>

<p>Conversely, the Nuvola player has a remote control and does not require a separate tablet.  However, Sony’s tablet is elegant and light and can also be used for all the other purposes a regular tablet can be used, although it would have to be returned to the 4K display room when the 4K player has to be controlled.</p>

<p>Regarding 4K content pricing, the Nuvola 4K content providers such as Netflix and Amazon Instant have not yet announced the price of their streamed/downloaded content, on the other hand Sony announced in mid 2013 their player and <a href="http://store.sony.com/gsi/static/WFS/SNYNA-SNYUS-Site/-/SNYNA/en_US/assets/pdfs/10177193_r0_Video_Unlimited_PDF_Rev2.pdf">4K content service</a>, which is <a href="http://store.sony.com/gsi/webstore/WFS/SNYNA-SNYUS-Site/en_US/-/USD/ViewProduct-Start;pgid=Cdp8WvMNFHNSRpCmsPAkSpqA0000bER0x0Sr;sid=HQmdr7qE33mhr-hTdyflqyKO1DLTanO_U8y5y8aY?SKU=27-FMPX1">already available</a> and content can be rented for 24 hrs of playback for $7.99 or purchased from $29.99, depending on the movie, TV episode purchases start at $3.99.  </p>

<p>As mentioned briefly before, Sony announced at CES 2014 their partnership with Netflix to deliver 4K UltraHD content in the first half of 2014 in all territories where Netflix is available, which implies that a new Sony’s 4K player would be capable of streaming 4K from Netflix in addition to the current 4K player download capabilities.  Sony also announced that starting January 2014 more than one hundred and forty 4K movies can be purchased from the <a href="http://store.sony.com/gsi/static/WFS/SNYNA-SNYUS-Site/-/SNYNA/en_US/assets/pdfs/10177193_r0_Video_Unlimited_PDF_Rev2.pdf">Video Unlimited 4K distribution service.</a></p>

<p>According to <a href="http://www.sony.net/SonyInfo/News/Press/201401/14-002E/index.html">Sony’s press release at CES 2014</a>:</p>

<p><em><strong>Next Generation 4K media player prototype</strong><br />
•	New media player compatible with Sony's premium 4K distribution service "Video Unlimited 4K." Capable of playing back content at a maximum bit rate of 100 Mbps, it allows customers to enjoy high picture and sound with the stability that content downloads offer. <br />
•	In addition to H.264/MPEG-4 AVC (Advanced Video Coding), this new 4K media player incorporates a decoder compatible with the advanced HEVC compression format. This decoder is designed to provide customers with the ability to enjoy 4K/60p content and anticipated new 4K streaming services. <br />
•	Also includes a XAVC S decoder. This decoder will allow customers to save 4K video taken using "FDR-AX100" and "FDR-AX1" 4K Handycam®s on the 4K media player's internal HDD and replay these videos on a 4K TV.</em></p>

<p><br />
<strong>Audio</strong><br />
 <br />
Nuvola’s NP-1 player connectivity limitation of one HDMI complicates the options for the playback of multi-channel digital audio (or for any audio) if the single HDMI output cannot be connected to the display through an A/V receiver/pre-pro that does not accept/pass-thru 4K video, which is the case of most audio equipment in the market that typically cannot be upgraded for 4K pass-thru (it should accept 4K not only output 4K, for upscaling for example).<br />
 <br />
To accommodate for that limitation the HDMI cable from the 4K player has to be then connected to a) the 4K panel directly and listen to the typically compromised audio quality of its audio speakers/amplification, or b) an HDMI splitter (“one HDMI in/two out”) that simultaneously connects to the audio equipment and to the 4K display for the video (typical if the display is a 4K projector). </p>

<p>Consideration should be given regarding the HDMI splitter current and future compatibility with HDCP 1.0/2.2, and other HDMI 2.0 functionality features such as 60fps 4K, which may entail a future splitter replacement if not upgradeable (more further below). </p>

<p>Alternatively, there is the commercial Nuvola NP-H1 player available for $699 ($400 above the NP-1) that has additional audio outputs beyond the single HDMI (it has 5-channels analog connectors and a digital optical audio), which, although costly, the NP-H1 would avoid running the risk of compromising the quality of the HDMI 4K video output of the player when the signal goes thru the extra HDMI splitter before reaching the 4K display.</p>

<p>If the NP-1 Nuvola player would have had a separate Digital Coaxial/Optical output in addition to its HDMI it could have output legacy lossy DTS or Dolby Digital 5.1/7.1 discrete digital multi-channel to an A/V system.  For lossless DTS Master Audio or Dolby True HD codecs the HDMI output would be needed and the HDMI splitter maybe the only viable solution if the A/V equipment cannot handle 4K.</p>

<p>The player specs are not clear regarding the multichannel audio outputted via HDMI other than the NP-H1 commercial player spec mentioning “5.1” without indicating if that means a) legacy lossy DTS/Dolby Digital 5.1, or b) also lossless DTS Master Audio/Dolby True HD in 5.1, or c) if player outputs the soundtrack unaltered as included in the content, or d) is converted to lossy 5.1 regardless if the source is of superior audio quality.   </p>

<p>In summary, it would have been ideal if the Nuvola players have two HDMI outputs, as the Sony 4K player and as several other Blu-ray players have for backward compatibility reasons.  Not having them means that the buyer of the player most probably have to spend extra to address the audio connectivity limitation, unless the player is connected directly to an UHDTV panel and the user would not mind its TV audio quality.  </p>

<p><strong>Video</strong></p>

<p>The Nuvola player is limited to output 4:2:0 chroma sub-sampling, 8-bit depth, Rec.709 HDTV color space, 24/30 frames per second (for film and video based sources respectively), and is suited with an HDMI 1.4 chip.<br />
 <br />
In other words, just an HD/Blu-ray type of spec that certainly limits the potential of what 4K content could provide thru the player as per the BT. 2020 Ultra HDTV standard.  In fairness, the Sony player shows similar spec limitations with no upgradeability mentioned, and “that” unfortunately may also be the case with the 4K content the public is expected to consume from now on, including a near future 4K Blu-ray which spec is in the works and was said will be ready before year end, according to a Samsung <a href="http://www.technologytell.com/hometech/103366/samsung-leaks-blu-ray-4k/">statement</a> (and I expect their 4K Blu-ray player prototype by CES 2015 and possibly a finish product available shortly after in 2015).<br />
  <br />
The manufacturer said the commercial version of the Nuvola NP-1 unit has similar specs, but it is rugged and is designed with software to handle digital signage installations.  Other than the extra audio connectivity mentioned above, the company said a consumer would not benefit from spending more ($699) in that commercial unit because it offers the same video quality than the $299 NP-1 unit, and the digital signage capabilities would be useless to regular consumers just looking for 4K playback. <br />
 <br />
According to the company, the second generation unit (when is available) will be fitted with a 2.0 HDMI chip and a better processor, and is expected to handle 60 fps 4K and better than 8-bit depth, but still limited to output 4:2:0 chroma sub-sampling at 4K 60 fps as per current HDMI 2.0 specs.  <br />
 <br />
The company claimed that the type of HDMI 2.0 chips required for what a 4K player must do are not available yet, although for the purpose of 4K displays the 2.0 chips may be available.  Some display manufacturers implemented/upgraded/worked around 1.4 HDMI chips into 4K UHDTVs/projectors and provided some extra “HDMI 2.0 type” of functionality, such as 60 fps for 4K, and HDCP 2.2, without actually installing an HDMI 2.0 chip.  </p>

<p>Nano Tech said that they will have to wait possibly until 2Q2014 for the HDMI 2.0 chip suitable to their 4K player to become available.  The HDMI 2.0 spec was recently approved in September 2013 and normally takes months before compliant chips become available.  <br />
 <br />
<strong>Streaming and Downloading</strong></p>

<p>Although the player is mainly designed as a streamer of 4K content (and of lower resolutions) it can also store a downloaded movie in a compatible storage device connected to its USB 3.0 output, if the content source provides the option/functionality for download, not just streaming. <br />
 <br />
According to the company, once the content has been stored the user can find the file of the recorded movie in the attached storage device, similarly to a Windows Explorer type of activity, and the movie will be played by the NP-1 player.  No details about content organization were provided but I assume there will be no other than folders with files, certainly not as fancy as a Kaleidescape server, and certainly not as pricey either.<br />
 <br />
In addition to Netflix, Amazon Instant, etc. and other 4K streaming apps that are included in the player's menu, Nano Tech hopes the player will have access to new 4K content providers that are open to the player.  Additionally, the company is also implementing their own 4K content service, the bundled <a href="http://www.nanotechent.com/media_channels.php">UltraFlix</a>™ UHD channel, which will be announced in February of this year, the company said.  <br />
 <br />
Sony’s 4K content service and ODEMAX content partner of the Red Ray 4K player were mentioned in the conversation but they are not included as 4K providers of the Nuvola player.</p>

<p><strong>What’s in the Plans</strong></p>

<p>Although Nano Tech Entertainment has just announced the introduction of their Nuvola NP-1 4K player the company is already working on a second generation of the player for which they are considering and welcoming feedback from the users of the first generation unit released just now.  <br />
 <br />
Along those lines, the company took my request of enhancing the HDMI connectivity limitations for separate audio, which as I said it only has one HDMI output and no coax/optical digital audio outputs, however, no promises were given.  </p>

<p>A second HDMI output was quoted as "not a low cost feature" and I suspect it meant “relative to the low player’s price of $299 MSRP”, however, the market has many price-reasonable Blu-ray players and A/V equipment with multiple HDMI outputs, some even supporting 4K.</p>

<p>Nano Tech preferred not to issue any statement estimating the availability of the second generation player, neither to confirm if it would be available even within this year, because, they said, it will take much effort and time to build the second generation unit and test it to make sure it works as expected with the new chips/processor.  However, Nano Tech confirmed that they committed their efforts to do so and it will be a matter of time.<br />
 <br />
There will be an upgrade program for users of 1st generation players (NP-1) to trade them in for a second generation unit when they become available.  The price of the trade in upgrade is expected to be lower than buying the second generation unit but no details were provided.  </p>

<p>It remains to be seen what it would happen to the 4K content stored in the external HDD when switching players, many HD-DVRs loose access to previously recorded content when the attached HDDs are disconnected and reconnected. There will be no upgrades to the 1st generation unit other than the trade-in program.  Although it was not said I assume they did not mean that for firmware upgrades thru its internet connection like a Blu-ray player does.</p>

<p>With the eventual upgrade/trade in of the 4K player, an HDMI splitter that was installed for the audio limitations mentioned earlier may also need replacement to be compatible with HDCP 2.2, 60 fps 4K if implemented by the content providers and if the new player still has the same HDMI limitations.</p>

<p><strong>Described Specifications According to Nano Tech</strong></p>

<p><em>“The Nuvola NP-1 supports current 4K UltraHD video using the H.264 compression, and will be automatically updated with the latest H.265 (HEVC) codecs as they become available.”</p>

<p>“The Nuvola NP-1 is powered by the fastest mobile processor on the market today. The all-purpose media player uses the nVidia Tegra 4 processor that features a Quad Core Cortex-A15 processor with an amazing 72 GPU's for incredible graphics processing power. The system comes standard with 2GB DDR RAM, 16GB Internal Flash Storage and has external connections for USB storage devices. From decoding 4K UltraHD Videos to playing state of the art 3D Video games, the Nuvola has the power to do it all.”</p>

<p>“The Nuvola NP-1 features built in Ethernet, state of the art 2x2 Wi-Fi, Bluetooth, and Infrared communications. You can connect remotes, game controllers and many other devices wirelessly or by connecting to the USB port.”</p>

<p>“The Nuvola NP-1 runs on Android 4.2 (JellyBean) operating system, the most advanced device OS available. It comes preloaded with dozens of the best Android digital signage apps. Plus, with access to the Google Play Store, you can choose from thousands of compatible apps and download them directly onto the Nuvola NP-1”</em></p>

<p><strong>Technical Specifications</strong></p>

<p>Processor NVIDIA Tegra 4 Quad Core Mobile Processor with 2GB DDR3 RAM<br />
Graphics Processor 72 GPU<br />
Internal Storage 16GB Flash Memory<br />
RJ-45 Ethernet 10/100/1000<br />
Wireless Dual Band 802.11n 2x2 Mimo Wi-Fi<br />
Bluetooth 4.0<br />
Output HDMI 1.4 output w/Audio<br />
I/O1x USB 2.0 / 1x USB 3.0<br />
Size90mm (w) x 100mm (l) x30mm (h)<br />
Streaming Video Support4K UltraHD (3840x2160), HD (1920x1080), SD (720x480)<br />
Operating System Android 4.2 Jelly Bean OS<br />
Power DC 12V<br />
Supported Video Formats MP4, MKV, AVI, MOV, OGG (H.264)<br />
Supported Audio Formats AAC, FLAC, MP3, WAV<br />
Supported Image Formats BMP, GIF, JPG, PNG<br />
Bundled Movies Access to 10 Streaming Movies free of charge on the NanoFlix UltraHD Network</p>

<p>Additional information regarding image quality vs. adaptive bitrate to adjust to available bandwidth<br />
 <br />
<strong>Lossless Download</strong><br />
500K – SD<br />
1.5 M – HD 720p<br />
2.5 M – HD 1080p<br />
10M – UHD 2160p if HEVC H.265 capable playback device<br />
20M – UHD 2160p if HEVC H.264 capable playback device</p>

<p><strong>Visually Perfect Download</strong> (very difficult to detect any loss)<br />
400K – SD<br />
1.2 M – HD 720p<br />
1.6 M – HD 1080p<br />
6M – UHD 2160p if HEVC H.265 capable playback device<br />
10M – UHD 2160p if HEVC H.264 capable playback device</p>

<p><strong>Some Lossieness</strong> (banding, blocky gradient)<br />
300K – SD<br />
980K – HD 720p<br />
1 M – HD 1080p<br />
3M – UHD 2160p if HEVC H.265 capable playback device<br />
6M – UHD 2160p if HEVC H.264 capable playback device</p>

<p>Pending to confirm are the folling items:<br />
 <br />
1) Is the current player compliance limited to only HDCP 1.0 content protection? Or HDCP 2.2 is possible/upgradeable on the current player?  <br />
 <br />
2) I assume the second gen player will have HDCP 2.2?<br />
 <br />
3) Is the player firmware upgradeable thru its internet connection?<br />
 <br />
4) Is either generation of player capable to seamlessly add more 4K apps as more content providers become available?</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 15, 2014 10:58 AM</b>
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
			<?=getComments(5180)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5180)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2014/01/nuvola-4k-player-np1.php" type="text/javascript" charset="utf-8"></script>
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