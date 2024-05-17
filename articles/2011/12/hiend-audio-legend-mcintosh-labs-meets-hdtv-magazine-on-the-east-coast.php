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
		AND e.entry_id = 4578";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4578 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4578 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4578";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/12/hiend-audio-legend-mcintosh-labs-meets-hdtv-magazine-on-the-east-coast.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4578";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast" />
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
	<title>HDTV Magazine - Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast</title>
	<meta name="keywords" content="vacuum tubes, quality audio, mcintosh products, end audio, audio quality, mcintosh, audio, quality, end, still, good, amp, time, vacuum, theta, “, ”, products, tubes, linda, audiophiles, decades, new, system, sound" />
	<meta name="description" content="Last week McIntosh Labs stop by the Washington DC/Virginia area in Fairfax, VA to reach out to fans and to introduce their products, the event included live music as well.

McIntosh was and still is up there among the few American companies that still work very hard to continue producing quality audio products. They still have their traditional metered displays and blue-hued backlighting and unique knobs. Audio quality and elegant appearance made McIntosh so special to many for decades." />
	<meta name="title" content="Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/12/hiend-audio-legend-mcintosh-labs-meets-hdtv-magazine-on-the-east-coast.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Last week McIntosh Labs stop by the Washington DC/Virginia area in Fairfax, VA to reach out to fans and to introduce their products, the event included live music as well.

McIntosh was and still is up there among the few American companies that still work very hard to continue producing quality audio products. They still have their traditional metered displays and blue-hued backlighting and unique knobs. Audio quality and elegant appearance made McIntosh so special to many for decades." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4578', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/12/hiend-audio-legend-mcintosh-labs-meets-hdtv-magazine-on-the-east-coast.php">Hi-End Audio Legend McIntosh Labs Meets HDTV Magazine on the East Coast</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>December  1, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=525&category=High-End Audio">High-End Audio</a></b>
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
				<p><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image002_5cf3a167-989b-4de2-89c0-46cca3eb4e3a.jpg" width="355" height="267">Last week <a target="_blank" href="http://www.mcintoshlabs.com/us/Pages/Home.aspx">McIntosh</a> Labs stop by the Washington DC/Virginia area in Fairfax, VA to reach out to fans and to introduce their products, the event included live music as well. <p>McIntosh was and still is up there among the few American companies that still work very hard to continue producing quality audio products. They still have their traditional metered displays and blue-hued backlighting and unique knobs. Audio quality and elegant appearance made McIntosh so special to many for decades. <p><img alt="" style="float:right" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image004_5cf2fde9-a8e5-4bf0-aae1-ce0878038a95.jpg" width="317" height="279">For those that like looking back in search for a perspective check <a href="http://www.mcintoshlabs.com/us/Brand/Pages/Heritage.aspx">McIntosh Heritage</a>, according to the company “<i>McIntosh was originally founded in Silver Springs, Maryland, 1949. The current Binghamton, NY factory, shown under construction in 1956, continues to grow with the company</i>.” <p>In November 4th McIntosh did a similar <a href="http://www.cepro.com/article/mcintosh_opens_experience_centers"><i>Experience Center</i></a> event in <a href="http://www.cepro.com/slideshow/image/9350/">Century Stereo, San Jose CA</a>. <i></i> <p>When I received the invitation to this east coast event a few weeks ago I was looking forward to meet them and to convey my best wishes to a company that deserves respect in their pursue for quality and in maintaining their heritage. <p>Linda Passaro, McIntosh’s VP of Sales and Marketing, and Christopher Smith Director of Sales – East, cordially greeted me when I arrived to the <a href="http://www.iq-av.com/default.aspx">IQ Home Entertainment</a> A/V store in Fairfax VA. <p>This was not an opportunity to demo their products but rather to get to know each other and sense the direction of the company. Linda explained that she enjoys introducing McIntosh products like the diamonds business she was involved for over two decades before arriving at McIntosh. To my surprise Linda said that many women showed their interest in McIntosh, especially those looking for quality and style, she said it was not unusual for them to point to the elegant McIntosh products while enjoying the quality of the audio they were listening and turn around to tell their husbands “honey I want exactly THAT”, such as if the amp would be a beautiful diamond ring in the jewelry store she wanted for their anniversary. If that trend expands, the hi-end audio may not be “his” stuff anymore, as traditionally has been for decades, which is actually good for everyone. <p><span class="caption right" style="width:375px"><img alt="Linda Passaro, McIntosh's VP of Sales and Marketing (left), myself (center), and Barry Davis, Sr. Advertising Manager of The Weekly Standard" align="right" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image007_d2631650-5f64-41c7-aeec-c11c2af7a3e1.jpg" width="373" height="249"><br />Linda Passaro, McIntosh's VP of Sales and Marketing (left), myself (center), and Barry Davis, Sr. Advertising Manager of “The Weekly Standard”</span>What was interesting was that when I arrived the rooms were filled with dozens of young men and women in their twenties and thirties, and there I was on my early sixties sharing the same respect for the famous brand. <p>In that environment I did not feel as old as I thought I would be when I was expecting a gathering of mainly old-timers that still claim to cleanly hear 20 kHz (thru their hearing aids). <p><span class="caption left" style="width:452px"><img alt="IQ Home Entertainment McIntosh event (me on the right talking to a McIntosh engineer)" align="left" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image009_104334bd-20d1-4fc0-9766-39fa36eb6dae.jpg" width="450" height="289"><br />IQ Home Entertainment McIntosh event (me on the right talking to a McIntosh engineer)</span>On a side note about old timers, one case that impressed me at a Consumers Electronics Show for audio a few years ago was when I visited the rebirth of Allison speakers. <p>At that time Allison’s VP told me that the legendary <a href="http://www.stereotimes.com/comm121501.shtml">Roy Allison</a> (founder of Allison acoustics after he left AR) still preferred to calibrate his famous Allison speakers by trusting his own ears close to the transducers. I hope the good man is still alive and enjoying the audio he loves. A few years ago I gave my dearest “Allison One” towers to my Son, <a href="http://www.danlamaestra.com/">a musician</a> and piano player of the US Navy jazz band (<a href="http://www.navyband.navy.mil/lamaestra_daniel.shtml">The Commodores</a>); I knew he knew how to enjoy them. <p>Since my audio beginnings in the 1960’s I admired the quality halo of McIntosh, at that time I was living in Argentina enjoying the quality of vacuum tubes triodes in stereo, the best quality audio back then, a triodes concept that is still used by many hi-end equipment of today, but I could not afford McIntosh products in my young years. <p>At that time, when the dinosaurs ran around, the first good amplifiers with transistors were not yet introduced. A few years later my amp manufacturer introduced their transistors version of my vacuum tube amp, I was eager to hear it, but it sounded very harsh, it was a step backwards in audio quality. <p>Vacuum-tubes-triode technology maintained its merits in the pursuit for quality and I felt blessed for having the opportunity early in life to learn what good sound quality was about, not like the MP3 youngsters of today in a world of ever increasing closings of brick and mortar A/V stores that could have demo good quality audio to them.  <p>I still remember replacing the vacuum tubes of my amp quite often so I could maintain the high quality of that clean sound, and it was worth every penny. My vinyl record turntable married with precision tone arms and sensitive moving magnet cartridges were the only source until the FM stereo tuner arrived. Don’t you dare touching my vinyl records or my stylus with your fingers! Or playing them without doing the traditional ceremony of a good cleaning first! <p>Pictured on the side is McIntosh Mc275 50<sup>th</sup> anniversary limited edition gold amp, a stunning piece of art, to be available in December 2011 (275 units only), a collector’s item (<img alt="" align="right" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image012_6d29f131-f575-4f3d-981d-9ce4b2469dee.jpg" width="304" height="233">here is the <a href="http://www.youtube.com/user/McIntoshLaboratory">celebration</a> by McIntosh Laboratory). <p>There was a good article recently published by Steven Stone in Audiophilereview.com, “<a href="http://audiophilereview.com/the-history-of-high-end-audio.html"><i>The History of High End Audio</i></a>” that refreshed my memories of 5 decades of hi-end audio, and I quote from his article: <p><i>“The other important trend in the 60's was brought on by the usurpation of the role of the </i><a href="http://audiophilereview.com/tubes.html"><b><i>vacuum tube</i></b></a><i> by </i><a href="http://nobelprize.org/educational/physics/transistor/history/"><b><i>the transistor</i></b></a><i>. Japanese hifi firms hadn't seen much success with their tube-based designs in the early 60's.But with the advent of the transistor </i><a href="http://hometheaterreview.com/new-pioneer-elite-g-clef-components/"><b><i>Pioneer</i></b></a><i>, </i><a href="http://hometheaterreview.com/yamaha/"><b><i>Yamaha</i></b></a><i>, </i><a href="http://hometheaterreview.com/sony/"><b><i>Sony</i></b></a><i>, </i><a href="http://hometheaterreview.com/sherwood-newcastle/"><b><i>Sherwood</i></b></a><i>, </i><a href="http://hometheaterreview.com/kenwood-sovereign-dv-5700-dvd-player-reviewed/"><b><i>Kenwood</i></b></a><i>, and </i><a href="http://hometheaterreview.com/sansui/"><b><i>Sansui</i></b></a><i>, all entered the US market with products whose specifications far exceeded those of US-made tube-based components. Of course 20-20 hindsight shows that these specifications used </i><a href="http://stereos.about.com/od/faqs/f/thd.htm"><b><i>THD or total harmonic distortion</i></b></a><i> figures rather than breaking down the harmonic distortion into 1st, 2nd, 3d, and 4th order harmonics. If they had, audiophiles would have seen how the distortion characteristics of early transistors were much worse at higher odd-order harmonics than tubes. Many audiophiles switched from tube electronics to solid-state electronics and discovered that the sonic results weren't a step up in quality or enjoyment.” </i> <p><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image014_97cca45f-aae7-4c03-bb7e-4189360a2ea8.jpg" width="269" height="236">According to <a href="http://www.mcintoshlabs.com/us/Brand/Pages/Legends.aspx">McIntosh Legends</a><i>: “Over the years, many famous musicians, recording artists and producers have selected </i><a target="_blank" href="http://www.mcintoshlabs.com/"><i>McIntosh audio systems</i></a><i> for the rich quality of sound, to experience music the way the artist intended. The loyalty from the music community began in the 1960s era of rock and roll. Several of our brand ambassadors have shared their experience and passion for McIntosh which has often spanned across decades. At McIntosh, we care about the satisfaction we bring and the product experience of all our customers.”</i> <p><i>“The vision of Frank McIntosh (r.) to build a better amplifier challenged what was believed possible. Gordon Gow (l.) and Frank, together designed the famous ‘Unity Coupled Circuit’:”</i> <p><i>“In 2005, McIntosh launched the Reference System and rocked the industry at the consumer electronics show. The Reference System became the pinnacle of audio excellence. The MT10 McIntosh turntable system is introduced in 2007. The first McIntosh turntable design matches the styling and performance of McIntosh stereo systems”</i> <p><img alt="" style="float:right" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image016_37a57620-b34e-46ca-8d9d-9fdb5156a460.jpg" width="304" height="268"><i></i> <p><img alt="" align="left" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image018_668bb983-d021-4d51-8010-4e0d5e123b2a.jpg" width="302" height="266"><b><i></i></b> <p>Over five decades, like many consumers, I upgraded my audio equipment quite a few times, mostly in pursue of better sound quality and power, but also to keep up with what unfortunately affects most preamps (and A/V receivers) at one time or another, such as new lossless/lossy audio codecs, HDMI versions, handling of 3D protocols, to mention a few.  <p>I respected and considered McIntosh on my choices, but my current hi-end pre/amp system is a <a href="http://www.thetadigital.com/casablanca_iiihd_controller_info.shtml">Theta Casablanca</a> preamp and a <a href="http://www.thetadigital.com/dreadnaught_iii_amplifier_info.shtml">Theta Dreadnaught</a> amp, a 100 pounds gorilla capable of cleanly handling 16 speakers for home-theater movies without a blink. At that time Theta was a better choice for me. <span class="caption left" style="width:241px"><img alt="Theta Casablanca preamp" align="left" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image020_8163ca46-cae0-43ae-b22d-f7cd81ebb627.jpg" width="239" height="159"><br />Theta Casablanca preamp</span> <p>So what a heresy! “You abandoned your treasured vacuum tubes”, some audiophiles may say, especially those that pray every night over their still warmed vacuum tubes before they go to bed. Back then I did not go that far, but close. <p>Well, I wanted to evolve into a multi-channel hi-end system with modularity and maintaining a high quality of sound, but in order to fit so many vacuum tubes to handle all the channels the industry is adding every year I would have to remove some seats in the home theater, not to mention the high heat, and the brightness created by the tubes in a room that should be a dark cave for the projector. Remember the first generation main frame computers?  <p><span class="caption right" style="width:193px"><img alt="Theta Dreadnaught amp" align="right" src="http://www.hdtvmagazine.us/articles/images/3ea630bec7f0_14CE7/clip_image023_2e8124da-2f47-4420-be8b-98b845eaa67f.jpg" width="191" height="156"><br />Theta Dreadnaught amp</span>Many audiophiles may have faced similar decisions (including divorces and pitching into retirement accounts); fortunately transistor designs improved audio quality with time. <p>As it turned out, Theta was not as modular and future proof as it preached since day one, unless you consider a $12K tag for an upgrade of a pre-amp reasonable for just adding HDMI and decoding ability of new audio codecs; and they were not alone in the industry. <p>So where is the hi-end industry heading regarding upgradeability when a manufacturer of a $300 AV receiver can implement the new features with a minor expense or by releasing the new model for $315 with all the new features? Granted they are not at the same quality of audio, but do the hi-end companies actually think they can squeeze an extra $10K+ from good customers for a similar objective? I certainly hope McIntosh would not go the Theta (and others) road.  <p>Audiophiles could always blame McIntosh, Theta and Krell for their divorces and 401k penalties for early withdrawals to pay for their love of audio, but they deserve more consideration for the trust and investment they put in hi-end companies. <p>Fortunately, thanks to Linda, women may eventually understand audiophiles much better, and that should reduce the number of divorces and home refinancing caused by new codecs, HDMI, and preamp upgrades. <p>Keep up the good work Linda and, while you help your company, help us, audiophile men, so we can give our wives a diamond that beautifully reproduces Beethoven compositions to their best and at the same time keeps them happy, just find us a diamond that shines for a reasonable amount of time before it dies. 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>December  1, 2011  8:46 AM</b>
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
			<?=getComments(4578)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4578)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/12/hiend-audio-legend-mcintosh-labs-meets-hdtv-magazine-on-the-east-coast.php" type="text/javascript" charset="utf-8"></script>
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