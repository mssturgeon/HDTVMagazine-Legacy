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
		AND e.entry_id = 800";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 800 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 800 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 800";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 800";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Which is More Consumer Friendly: HD DVD or Blu-ray?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Which is More Consumer Friendly: HD DVD or Blu-ray?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Which is More Consumer Friendly: HD DVD or Blu-ray?" />
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
	<title>HDTV Magazine - Which is More Consumer Friendly: HD DVD or Blu-ray?</title>
	<meta name="keywords" content="blu ray, dvd blu, high definition, dvd players, standard dvd, dvd, blu, ray, players, support, standard, consumer, buy, format, high, player, features, both, definition, any, formats, titles, those, mbit, movie" />
	<meta name="description" content="No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &amp;quot;winner&amp;quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.

This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &amp;quot;on the fence&amp;quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence." />
	<meta name="title" content="Which is More Consumer Friendly: HD DVD or Blu-ray?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Which is More Consumer Friendly: HD DVD or Blu-ray?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &amp;quot;winner&amp;quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.

This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &amp;quot;on the fence&amp;quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=800', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php">Which is More Consumer Friendly: HD DVD or Blu-ray?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>November 26, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=280&category=Blu-ray">Blu-ray</a></b>
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
				<p>No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &quot;winner&quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.</p>  <p>This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &quot;on the fence&quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence.</p>  <h2>Why Choose Either Format?</h2>  <p>First let's take a look at the benefits that these formats have over standard DVD and even HDTV.</p>  <ul>   <li><strong>Increased resolution</strong>. Both HD DVD and Blu-ray support video at 1080 lines of <a href="/glossary.php#Vertical+Resolution" target="_blank">vertical resolution</a>, compared to standard DVDs' 480. The <a href="/glossary.php#Horizontal+Resolution" target="_blank">horizontal resolution</a> is also greater at 1920 lines vs standard DVDs' 720. In total, high definition DVD will display 2 million pixels on the screen at any given time, compared to about 350,000 with standard DVD. That's 6x the resolution in the same area. </li>    <li><strong>Higher bitrate.</strong> Resolution is the easy one to put your finger on, but the secret to better picture quality is in the bitrate, or amount of information sent to your TV each second. Standard DVD is limited to about 11Mbit/s (Megabits per second) while cable, satellite and broadcast (over-the-air) can be delivered at up to 19Mbit/s (although 12-13Mbit/s is more common). Both HD DVD and Blu-ray can support bitrates in excess of 36Mbit/s. The result is a much more detailed picture, even during fast motion scenes that can wreak havoc on the over-compressed signals of cable &amp; satellite. </li>    <li><strong>Better audio.</strong> Next on the list has to be audio. Both HD DVD and Blu-ray support more advanced audio codecs than standard DVD, including the <a href="/glossary.php#Lossless" target="_blank">lossless</a> <a href="/glossary.php#Dolby+TrueHD" target="_blank">Dolby TrueHD</a> and <a href="/glossary.php#DTS-HD+%28%2B%2B%2C+and+Master+Audio%29" target="_blank">DTS-HD Master Audio</a>. Lossless codecs provide sound exactly as the content creator intended, with nothing lost due to compression. </li>    <li><strong>Features and Interactivity (Extras)</strong>. Standard DVD has some basic interactivity. I've seen some of my kids' DVDs that include rudimentary games, etc. But with high definition DVD, a whole new world opens up. I'll gloss over the gory details and just say that with these next generation formats it will be more like browsing the web than just clicking the down arrow twice and Play. Another big difference is that these next generation players have secondary video processors. This in essence gives you the ability to toggle on a picture-in-picture display while watching the movie. This secondary video stream can include any number of features like director's commentary, out-takes, unedited footage ... the possibilities are nearly endless. </li>    <li><strong>What about download?</strong> Most download services available today don't support high definition video. Those that do have HD available don't &quot;sell&quot; the content, they &quot;rent&quot; it. And until you are able to &quot;buy&quot; a digital copy to store on your computer and play back to any of your TVs at your leisure, I can't recommend it as an adequate next step for home movie viewing. Another thing to consider is that the hard drive space required to store these downloadable movies in the same quality as HD DVD and Blu-ray would cost between $10 and $15 per movie. When all is said and done, it would cost you almost twice current HD DVD and Blu-ray prices to buy movies via download.       <p></p>      <p>In my searching, I did find one high definition movie download service that allowed you to <strong>buy </strong>movies. It's called <a href="http://www.vudu.com" target="_blank">Vudu</a>, and they have a selection similar to most movie stores. With Vudu, you first buy a set top box for $399.99. This set top box can store up to 100 hours of purchased movies, which can be purchased for $20 - $25 (for new releases). Also, in order to have instant viewing of movies, they recommend an internet connection speed of 2-3 Mbit/s. This is a definite step in the right direction, but quite a bit more expensive than HD DVD and Blu-ray, and not very practical if you plan on having a large collection of movies.</p>   </li>    <li><strong>What about combination players?</strong> A good universal option. LG has one out this year that is fully compliant with both specifications and Samsung is supposed to have one out this year as well. The problem is that they are much more expensive ($999 MSRP), which is more than you would pay if you bought both HD DVD and Blu-ray players. I therefore cannot recommend dual format players to consumers quite yet. </li> </ul>  <p>Now that I've given you a few reasons to consider investing in these formats, let's hear what consumers have to say who have already made the leap to high definition DVD:</p>  <ul>   <li>According to our <a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank">Fall 2007 HDTV Study</a>, more than 42% of respondents have made the investment<sup>1</sup> </li>    <li>90% of consumers who have invested are &quot;highly satisfied&quot; with their purchase<sup>2</sup> </li>    <li>Those that have a high definition player plan to replace 25% of their DVD library with their high definition counterparts<sup>2</sup> </li>    <li>Another figure from our <a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank">Fall Study</a> shows that for respondents that are upconverting their standard DVD content, only about 15% think it's &quot;Good enough.&quot; </li> </ul>  <p>If none of that convinces you that you need to have one of these formats in your living room, you can stop reading. If you want to know why I think HD DVD is the best option, read on dear friend.</p>  <h2>Why HD DVD?</h2>  <p>My reasoning below is not based on which format has higher bitrate or more capacity, nor is it based on which one has more studios in its pocket, or more titles on the shelves ... as all those are about equal when you're looking at the screen. The reason I am recommending HD DVD is for the benefit to the consumer ... you.</p>  <ul>   <li><strong>Standardization</strong> - No matter what player you buy, it will play all HD DVD titles with full features. Since HD DVD players began shipping, they have had one <strong>standard</strong> set of requirements for their players. 100% of the HD DVD players on the market today must support a minimum set of features. I've listed below some of the features that are guaranteed to be on <strong>all</strong> HD DVD players, but might not be on all Blu-ray players:       <ul>       <li>Support of <a href="/glossary.php#Dolby+Digital+Plus" target="_blank">Dolby Digital Plus</a> </li>        <li>Support of Dolby TrueHD </li>        <li>2nd video decoder </li>        <li>2nd audio decoder </li>        <li>Internet support (network connection) </li>        <li>Region Free </li>     </ul>   </li>    <li><strong>Less Copy Protection</strong> - The HD DVD specification requires no copyright enforcement. The Advanced Access Content System (AACS) is mandatory for Blu-ray and optional for HD DVD, although many studios are using it. Blu-ray also includes additional content protection schemes such as BD+ and ROM-Mark watermarking. Each of these layers of protection add a level of complexity to the players and increased production and licensing costs of both players and media. It has also been <a href="http://www.highdefdigest.com/news/show/1035" target="_blank">reported by High-Def Digest</a> that additional copy protection may result in more lengthy load times. </li>    <li><strong>Features &amp; Interactivity</strong> - I've never been one to make use of the &quot;Extras&quot; on standard DVDs. Well, maybe the deleted scenes and out-takes, but that's it. With HD DVD, I find myself actually looking at these features before I buy a movie to see if there's anything original. This is a highly subjective point, but my argument here is that the consumer benefits by having these features and interactivity available to them, should they happen to enjoy them. </li>    <li><strong>Internet Updating</strong> - To date, the <strong>only</strong> Blu-ray player that can update its software/firmware via network connection is the Sony Playstation 3.<p class="editorial"><b>Editorial Note:</b> It was pointed out to me that the Samsung models BDP-1400 and BDP-1200 can also update via network connection. I apologize for the oversight (11/27, 12:01am EST)</p> Other Blu-ray players require you to either order a DVD with the update or download and burn your own update DVD. Sony's BDP-S300 recently had a firmware release and I was <a href="/forum/viewtopic.php?t=8693" target="_blank">attempting to help</a> someone on our <a href="/forum/index.php" target="_blank">forums</a> download and install it. I checked the page and there were about 25 steps to follow to get it updated, along with another dozen or so &quot;Important Notes&quot; of things to make sure you do (or not do) when updating ... not very consumer friendly. </li>    <li><strong>Better Price</strong> - I mention this last because I want to stress that there are a lot of other reasons to choose HD DVD than just the price, but it can't be ignored. With street prices of Blu-ray players around <a href="/equipment/model.php?man=Sony&amp;model=BDPS300" target="_blank">$357</a> ($499 MSRP) and street prices of HD DVD players around <a href="/equipment/model.php?man=Toshiba&amp;model=HDA2" target="_blank">$169</a> ($299 MSRP) ... it's just icing on the cake. HD DVD players have even sold <a href="/news/2007/11/toshiba_hd-a2_hd_dvd_player_drops_below_100.php" target="_blank">as low as $99</a> this month in various sales at retailers like Wal-mart and Best Buy. </li> </ul>  <h2>From the That's-Not-Quite-True Department</h2>  <p>There are a lot of &quot;facts&quot; and figures that get thrown around whenever someone sticks their neck out in favor of one format or another. In this case, those that have already invested in Blu-ray may throw up some strongly-worded arguments to my recommendation. Let me attempt to disarm some of them by stating below some things you're likely to hear/read, and why they're &quot;Not Quite True&quot;:</p>  <ul>   <li><strong>Blu-ray has more studio support than HD DVD</strong> - Of the six big movie studios in North America, three of them are Blu-ray exclusive, two of them are HD DVD exclusive, and one (Warner Bros) is producing in both formats. But what we're really talking about here is the number of titles available, not the number of studios supporting it.&#160; According to Wikipedia, as of October 31st, 2007, 332 titles are available in the US on Blu-ray and 328 on HD DVD<sup>3</sup>. And as of November 6th, 2007, Netflix has 378 Blu-ray titles and 345 HD DVD titles. Sounds about even. That being said, you also must take into account whether there are titles available from only one format that you must have. That alone can make all other advantages of one format over the other irrelevant.</li>    <li><strong>Blu-ray has more manufacturer support than HD DVD</strong> - This one is true, but I include it for what it means. Usually, more manufacturers mean more competition, which leads to lower prices. HD DVD is far less expensive than Blu-ray, so what good are all those manufacturers doing for the Blu-ray format? </li>    <li><strong>Blu-ray has higher capacity/bitrate than HD</strong> <strong>DVD </strong>- I'll give you that. Blu-ray players currently support discs with a capacity of up to 50GB while HD DVD is limited to 30GB (although 51GB HD DVDs were recently approved). Also, Blu-ray bitrates can run to 54Mbit/s while HD DVD is limited to 36Mbit/s. That being said, show me how that makes a difference with a side-by-side comparison of picture quality. I doubt it's $200 better from any consumer's point of view, and that is the guiding principle of my recommendation. </li>    <li><strong>Blu-ray can do all that added feature and interactivity stuff too</strong> - Yes, but only certain players can support it, and only certain disks have it. It should not be up to the consumer to keep track of whether a player can take advantage of a specific feature they see on the back of the package ... they should <strong>know</strong> it's supported regardless of their player. </li>    <li><strong>Target went Blu-ray exclusive, the end is near</strong> - Actually, Target just bought an end-cap. A quick check in their online store shows that they are selling both the <a href="http://www.target.com/Toshiba-HD-DVD-Player-HDA30/dp/B000U6AHYS/sr=1-2/qid=1195622207/ref=sr_1_2/601-5215395-5382501?ie=UTF8&amp;index=target&amp;rh=k%3Ahd%20dvd&amp;page=1" target="_blank">Toshiba HDA30</a> and the new <a href="http://www.target.com/Venturer-HD-DVD-Player-SHD7000/dp/B000W7O43U/sr=1-3/qid=1195622207/ref=sr_1_3/601-5215395-5382501?ie=UTF8&amp;index=target&amp;rh=k%3Ahd%20dvd&amp;page=1" target="_blank">Venturer HD DVD player</a>. Also, since when is Target a bellwether in retail consumer electronics? </li>    <li><strong>Blockbuster went Blu-ray exclusive, the end is near</strong> - Again, Blockbuster's announcement was not quite that far reaching. The Blu-ray exclusivity is limited to about 87% of their stores, and they are still making HD DVD available via online rental. Also, Blockbuster later issued a <a href="http://blockbuster.mediaroom.com/index.php?s=press_releases&amp;item=727" target="_blank">press release</a> that indicated that they would continue to stock more HD DVDs in their stores as demand increases. </li>    <li><strong>Paramount got paid $150 million for HD DVD support</strong> - True, but let's not pretend money is not changing hands all over the place in this contest. It's business, and that's how business is done. I hardly think this is a reason to dislike HD DVD. </li>    <li><strong>HD DVDs scratch more easily because they don't have the hard coating that Blu-ray has</strong> - Blu-ray does utilize a hard coating on the surface of their media that resists scratches. This had to be done because the data layer in a Blu-ray disc is so much closer to the surface than in HD DVD. Regardless, this does not mean that HD DVD's are more susceptible to scratching and damage. I contacted a popular online rental company and asked them about damage reports and disc durability of the two formats. According to them, there is no appreciable difference in the number of returns for either format. </li> </ul>  <h2>Conclusion</h2>  <p>I'll restate what I've said above, but without all the detail. Here is why I believe HD DVD is the best choice for the consumer this holiday season:</p>  <ul>   <li>All HD DVD players are standard, and you can feel confident that you will not have any issues playing back any HD DVD title on any HD DVD player. </li>    <li>Since all HD DVD players are internet-capable, any updates that you may have to do to your player can be done without complicated downloads, DVD burns and upgrade routines. </li>    <li>HD DVD is region-free, meaning that no matter in which country you buy your HD DVD, it will play in your player. </li>    <li>HD DVD media has less copy protection. Less copy protection means faster disc load times. </li>    <li>Sale prices for HD DVD players this holiday are around $100-$200, much more consumer- (and wallet-) friendly than sale prices for Blu-ray players, which are around $400. </li> </ul>  <p>I expect (dare I say hope) that this will generate a lot of conversation. It remains to be seen how much of it will be in opposition to the recommendation I'm making. I will close this article with a recent quote that I came across that seems to be quite apropos:</p>  <blockquote>   <p>Human beings are perhaps never more frightening than when they are convinced beyond doubt that they are right.      <br />      <br />- Laurens van der Post, explorer and writer (1906-1996)</p> </blockquote>  <p>With that said, I welcome your comments.</p>  <p>&#160;</p>  <p><font size="1"><sup>1</sup> Source: HDTV Magazine's </font><a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank"><font size="1">Fall 2007 HDTV Study</font></a><font size="1">. The Study is still in-progress, but the data above is based on 1600+ respondents.</font></p>  <p><font size="1"><sup>2</sup> Source: The NPD Group, a leading retail market research firm</font></p>  <p><font size="1"><sup>3</sup> Source: Wikipedia article: </font><a href="http://en.wikipedia.org/wiki/Comparison_of_high_definition_optical_disc_formats" target="_blank"><font size="1">Comparison of high definition optical disc formats</font></a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>November 26, 2007  5:58 AM</b>
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
			<?=getComments(800)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 800)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php" type="text/javascript" charset="utf-8"></script>
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