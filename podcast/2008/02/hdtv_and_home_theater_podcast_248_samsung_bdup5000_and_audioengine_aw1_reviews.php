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
		AND e.entry_id = 1248";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1248 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1248 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1248";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-248-samsung-bdup5000-and-audioengine-aw1-reviews.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1248";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews</title>
	<meta name="keywords" content="blu ray, usb port, high def, power adapter, mini jack, audio, wireless, player, receiver, ray, dvd, usb, sender, adapter, audioengine, blu, speakers, seen, good, both, high, great, power, been, discs" />
	<meta name="description" content="Listener Mike sent in a review of the Samsung BD-UP5000 combo Blu-ray and HD-DVD player.  We review the Audioengine AW1 Wireless Audio Adapter.  And the top 10 things we really wish could have been in high definition." />
	<meta name="title" content="HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-248-samsung-bdup5000-and-audioengine-aw1-reviews.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Listener Mike sent in a review of the Samsung BD-UP5000 combo Blu-ray and HD-DVD player.  We review the Audioengine AW1 Wireless Audio Adapter.  And the top 10 things we really wish could have been in high definition." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1248', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-248-samsung-bdup5000-and-audioengine-aw1-reviews.php">HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>February  8, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=517&category=HD DVD">HD DVD</a></b>, <b><a href="/category.php?id=417&category=High Definition Production">High Definition Production</a></b>, <b><a href="/category.php?id=441&category=Wireless HDMI/HDTV">Wireless HDMI/HDTV</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<strong>Today's Show:</strong><br>

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-08.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br></p>

<p><strong>Samsung <a target="blank" href="http://www.samsung.com/us/consumer/detail/detail.do?group=mp3audiovideo&type=blu_ray&subtype=duohdplayer&model_cd=BD-UP5000/XAA">BD-UP5000</a></strong></p>

<p>Listener Mike provided an excellent review of the Samsung BD-UP5000 combo Bl-ray and HD-DVD player.  Here is his conclusion on the unit.</p>

<p>The Samsung BD-UP5000 is one of the few high definition disc players that can play both Blu-Ray and HD-DVD discs.  Its MSRP is $999 but is usually available for $799 or somewhat less.  I have owned one now for almost a month.  Ironically I got it about two hours before the Warner Blu-Ray exclusive decision became public.<br />
The BD-UP5000 has some very good things going for it and a few caveats.<br />
 <br />
<strong>The pluses:&nbsp;</strong><br><ul><li>It does play HD-DVD and Blu-Ray discs very well. &nbsp;You only need one player to play both formats plus all your standard DVDs. &nbsp;I have played a number of both types and they all looked great. &nbsp;It supports all of the HD-DVD features and all of the pre-Profile 1.1 features of Blu-Ray.</li><li>Great picture quality for HD-DVD, Blu-Ray and standard DVD. &nbsp;The unit has the Reon processor for video processing and looks as good as any player on the market, better than most of them. &nbsp;Even when fed to component, all discs types look great, even without the Reon operating. &nbsp;The standard DVD performance is as good as any I have seen.</li><li>It is one of the few players with 7.1 analog audio out connections which is important to someone with an older surround processor, like me.</li><li>It loads high-def discs reasonably quickly, at least compared to other players.</li><li>It has a very simple, easy-to-understand user interface</li><li>It has a built-in Ethernet connection and is Blu-Ray Profile 1.1 ready (but not yet enabled)</li><li>The unit is very sleek looking with its piano black finish and blue lighting</li><li>The remote is good and sleek looking, though not illuminated.<br></li></ul><strong>The minuses:</strong><br><br />
<ul><li>There are a handful of high-def discs that don't play or don't play correctly, even after one firmware update. &nbsp;More firmware updates are promised.</li><li>Some people have reported video and audio dropouts with standard and high-def DVDs. &nbsp;I haven't seen this problem but it might indicate unit-to-unit variations.</li><li>It only handles Dolby TrueHD in stereo and DTS-MA not at all at this time. &nbsp;Future updates are supposed to come that will address this for both internal and external decoding but they are rumored as not happening until May.</li><li>Profile 1.1 has not yet been enabled. &nbsp;Profile 2.0 cannot be done with this player because there is not enough onboard memory and no way to add external memory</li><li>Response to the remote can be very slow sometimes</li><li>4:3 standard DVDs are always stretched if you are upscaling them. &nbsp;To watch 4:3 properly you have to switch to 480i or 480p.</li><li>Some might consider the price a little steep though it is pretty comparable to buying one each of a high-end HD-DVD and Blu-Ray player.<br><br />
</li></ul>Finally, there are a huge number of rumors floating around about this player and Samsung's commitment to it.  There are many who doubt that the promised audio updates will come or that there may be chip problems that prevent the updates.  If you're a doubter or worrier, this is probably not the player for you.  This doesn't bother me since I am happy with what it does right now and the future updates will only be pluses.</p>

<p>Overall I am glad I bought this player as a one-box solution to watching all currently available discs, without having to wait for Blu-Ray replacements for HD-DVD exclusive titles.  The video performance is spectacular and the audio issues are minor for my purposes.</p>

<p><strong>Ten things we wish we could have seen in HD</strong></p>

<p>Now that almost everything is being shot in HD, great moments will forever be captured with great detail. For this feature we thought it would be good to go back in time an identify 10 events that would have been great to see in HD. The only caveat is that the event had to have been seen on TV or captured on film. Please feel free to send us your moments.</p>

<p>   <strong>1. The Moon Landing</strong> - I remember watching this as a child at my uncle's house. They had a 26 inch RCA color TV. The reason we went there was that we didn't have a color TV in 1969. Imagine how wonderful it would have looked in HD! The contrast in the moon lends itself to high def!<br />
   <strong>2. The Falling of the Berlin Wall</strong> - It was like a party. HD would have captured the expressions on everyone's face and help us all experience what the Berliners were experiencing.<br />
   <strong>3. The Babe Ruth Call Shot</strong> - Legend has it that Babe Ruth called a Home run in the fifth inning of game 3 of the World Series against the Chicago Cubs. On film you can see the Babe pointing but its not clear what he was pointing at. HD would have ended this debate once and for all.<br />
   <strong>4. The last episode of M*A*S*H</strong> - Still the most watched television show in history. Ara watched it on a 19 inch TV in his college dorm room. An episode that big needed to be seen HD!<br />
   <strong>5. The Ali Frasier fight</strong> - The thrilla in Manila. Back then there was no pay per view. You had to go someplace that had closed circuit TV. I am sure the quality was no where near as good as boxing on HBO!<br />
   <strong>6. The Wright Brothers first flight</strong> - We've all seen the film a thousand times but did we really experience what was going on? Not really.<br />
   <strong>7. Elvis Presley's Ed Sullivan appearance</strong> - Of course we would want to see him filmed without censorship.<br />
   <strong>8. The US Olympic Team beating the USSR in the Olympics</strong> - It was 1980 and Ara and five of his buddies were huddled around a TV at a buddy's house. Twenty six inches and no surround. How did we ever live back then?<br />
   <strong>9. Hindenburg explodes above Lakehurst New Jersey</strong> - Not to be gruesome but we have all seen this one a few times in our life.<br />
  <strong>10. Woodstock</strong> - How amazing would that concert have been on MHD?  Think about it, Jimi Hendrix, Janis Joplin, Crosby, Stills, Nash & Young, Creedence Clearwater Revival, The Who, Santana, Grateful Dead, Joe Cocker, Jefferson Airplane...</p>

<p><br />
<strong>Audioengine <a target="_blank" href="http://audioengineusa.com/store/product_info.php?manufacturers_id=&products_id=82&osCsid=d3c93326469e27713475477989f4590a"> AW1</a> Wireless Audio Adapter</strong></p>

<p>If you own an iPod you have probably wanted to send the music to a remote set of speakers. There are various products on the market that make that possible. Today we review what we feel is the easiest to use wireless adapter on the market. The Audioengine AW1 Wireless Adapter consists of two pieces, a Sender and a Receiver. The Sender will transmit audio from any mp3 device via a 3.5" mini jack or a computer's USB port. It works with both Macs and PCs! The device is small, about the size of a USB key drive. Both the sender and the receiver have a 3.5 inch connection, one for the device with the audio and the other to connect to the speakers. All cables are included, even an adapter to accept RCA outputs from your receiver. More on this later. </p>

<p>The range of the adapter is 100 feet. We did not go the entire 100 feet in our testing but were able to roam comfortably around our palatial estate with no dropouts. That was about thirty feet in a straight line through multiple floors and walls. The audio was crystal clear and we did not hear any interference. The engineers at Audioengine developed a proprietary audio protocol running over 802.11, which is how they get such high interference tolerance against WLAN, Bluetooth, cordless phones, microwave ovens, etc.</p>

<p>Both Sender and Receiver get power from the USB bus. If you wish to use the device with an mp3 player you need to use the included USB AC Power Adapter. This ties you to a wall socket, which is OK if you control your music from one spot. The only issue we have is that the sender is not battery powered. They are working on a battery powered sender but no time frame has been established for the product.</p>

<p>When using the Sender on a computer it gets power and audio from the USB Port. When using the Sender with an mp3 player you will need to connect the included mini jack cable to the mp3 player's headphone output. If you use the receiver with the Audioengine A5 (<a target="blank" href="http://www.htguys.com/archive/2007/September04.html">See our Review</a>), the connection is as easy as 1 2 3. First connect the mini jack cable to the A5 input on the top of the speaker, then connect the other end of the cable to the output of the receiver, and finally plug the receiver into the USB port on the top of the A5. If you want to use a speaker set other than the A5s you will need to buy an additional power adapter. Audioengine will sell you a USB charger for $20, but any USB charger will work (iPod, phone, etc.).</p>

<p>The entire installation took us about 15 minutes. It would have been shorter but we had to read the instructions to figure out how to get audio out of the Mac USB port. Took us two minutes to find in the manual and 15 seconds to do. There is also the ability to have one sender transmit to multiple receivers so you can do whole house audio quite easily.</p>

<p><strong>Main Use Configurations:</strong><ul><li>PC to Remote Speakers - More than likely you will use this with a laptop. Wireless speakers are really not needed in most computer situations.</li><li>mp3 to Remote Speakers/Receiver - Very typical situation. Just be aware that if you are not using Audioengine A5 speakers or if you are sending the audio to a receiver you will need an additional power adapter</li><li>Receiver to A5s as wireless surrounds/subwoofer - This is an easy way to turn your Audio Engine A5s into wireless surround speakers. If you don't have A5s you can use any other pair of powered speakers as long as you add an additional power adapter. Going the A5 route will run you about $475 for an very good wireless surround system. May seem like a lot but what will it cost to wire the room?</li></ul></p>

<p>We have reviewed a few wireless systems on the show. They all worked pretty well but every now and then interference from a cordless phone or wireless router would ruin the experience. The Audioengine AW1 Wireless Adapter is the best wireless audio we have heard to date. Its a snap to install and use. Combined with the A5s, its combo that's hard to beat!<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>February  8, 2008  5:28 AM</b>
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
			<?=getComments(1248)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1248)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-248-samsung-bdup5000-and-audioengine-aw1-reviews.php" type="text/javascript" charset="utf-8"></script>
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