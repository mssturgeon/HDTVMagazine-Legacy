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
		AND e.entry_id = 703";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 703 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 703 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 703";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/09/pioneer-showcases-new-flagship-av-receiver-outfitted-for-high-end-home-theater-performance-like-never-before.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 703";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before" />
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
	<title>HDTV Magazine - Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before</title>
	<meta name="keywords" content="home theater, multi channel, surround sound, high resolution, listening experience, pioneer, audio, sound, receiver, home, new, high, digital, performance, entertainment, theater, experience, media, surround, channel, power, technology, playback, level, users" />
	<meta name="description" content="At CEDIA Expo today, Pioneer Electronics (USA) Inc. unveils a new flagship A/V receiver; a sophisticated powerhouse that sets the benchmark for multi-channel sound in 1080p home theaters with pristine reproduction of emerging high resolution audio and high definition video formats. The Pioneer&amp;reg; Elite&amp;reg; SC-09TX A/V receiver features a reengineered &quot;direct energy high HD&quot; amplifier that takes advantage of ICEpower&amp;trade; analog class-D amplification coupled with Pioneer sound tuning technology. Reinforced with proprietary digital signal processing (DSP) capabilities, the receiver ensures maximum high resolution sound reproduction of new audio formats, Dolby&amp;reg; TrueHD, Dolby&amp;reg; Digital Plus and DTS-HD&amp;trade;. A seamless user experience is achieved with..." />
	<meta name="title" content="Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/09/pioneer-showcases-new-flagship-av-receiver-outfitted-for-high-end-home-theater-performance-like-never-before.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="At CEDIA Expo today, Pioneer Electronics (USA) Inc. unveils a new flagship A/V receiver; a sophisticated powerhouse that sets the benchmark for multi-channel sound in 1080p home theaters with pristine reproduction of emerging high resolution audio and high definition video formats. The Pioneer&amp;reg; Elite&amp;reg; SC-09TX A/V receiver features a reengineered &quot;direct energy high HD&quot; amplifier that takes advantage of ICEpower&amp;trade; analog class-D amplification coupled with Pioneer sound tuning technology. Reinforced with proprietary digital signal processing (DSP) capabilities, the receiver ensures maximum high resolution sound reproduction of new audio formats, Dolby&amp;reg; TrueHD, Dolby&amp;reg; Digital Plus and DTS-HD&amp;trade;. A seamless user experience is achieved with..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=703', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/09/pioneer-showcases-new-flagship-av-receiver-outfitted-for-high-end-home-theater-performance-like-never-before.php">Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  6, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Pioneer Showcases New Flagship A/V Receiver Outfitted for High End Home Theater Performance Like Never Before</p>

<p>CEDIA EXPO 2007<br />
Booth #740</p>

<p><B>DENVER--(BUSINESS WIRE)</B>--At CEDIA Expo today, Pioneer Electronics (USA) Inc. unveils a new flagship A/V receiver; a sophisticated powerhouse that sets the benchmark for multi-channel sound in 1080p home theaters with pristine reproduction of emerging high resolution audio and high definition video formats. The Pioneer&reg; Elite&reg; SC-09TX A/V receiver features a reengineered "direct energy high HD" amplifier that takes advantage of ICEpower&trade; analog class-D amplification coupled with Pioneer sound tuning technology. Reinforced with proprietary digital signal processing (DSP) capabilities, the receiver ensures maximum high resolution sound reproduction of new audio formats, Dolby&reg; TrueHD, Dolby&reg; Digital Plus and DTS-HD&trade;. A seamless user experience is achieved with a sizeable LCD screen on the receiver's front panel allowing users to navigate and control all connected devices as well as manage digital media playback.</p>

<p>The receiver's realistic sound quality is a result of a new professional build with premium, hand selected parts that harnesses the amplification power. The result is an impressive chassis construction, which truly realizes the audio and video engineering prowess of the new Pioneer reference A/V receiver. Pioneer outfitted the new receiver with enough connectivity options for an array of components as well as its industry-leading room tuning function for realistic surround sound performance in any room layout. It is one of the first A/V receivers to take advantage of new THX Loudness Plus&trade;, which preserves an original sound mix for an accurate listening experience at any volume level.</p>

<p>"The evolution of digital home entertainment and rapidly growing number of high definition devices necessitate a component such as our new reference A/V receiver. The SC-09TX represents the pinnacle of absolute high end design and engineering in home theater equipment," said David Bales, marketing manager of audio products for the home entertainment division of Pioneer Electronics (USA) Inc. "Home audiences will see and hear entertainment that they never realized was possible - this is truly a new generation of home theater performance."</p>

<p>Designed as the centerpiece of high end home theaters, discerning entertainment enthusiasts gain the most realistic seeing and listening experience with the SC-09TX A/V receiver. As the industry's first to have home networking functionality, the SC-09TX allows users to enjoy favorite digital video, audio and image media files direct from a home PC to any room in the house for an entertainment experience like never before.</p>

<p><br />
<B>Powerful Direct Energy HD Amplifier</B></p>

<p>Pioneer developed the SC-09TX to deliver the absolute best-in-class high resolution audio reproduction. Current amplifier designs are no longer capable of handling the high power performance required for a memorable listening experience.</p>

<p>Engineers realized that notable ICEpower analog Class-D technology when combined with Pioneer's sound tuning technologies and super DSP room tuning results in a revolutionary new level of amplification typically found only in professional studios.</p>

<p>The exclusive Direct Energy HD Amplifier found in Pioneer's reference SC-09TX A/V receiver provides a staggering drive capability that realizes true multi-channel continuous power output (200W x 7 simultaneously) to achieve a level of surround sound performance with overwhelming accuracy and heart pounding HD entertainment.</p>

<p>The ICEpower analog Class-D amplifier boasts a unique design with raw digital power in a dense enclosure. By employing Pioneer DSP technology, the SC-09TX can deliver precise sonic reproduction that goes beyond traditional audio capacity.</p>

<p>"We are very satisfied with our collaboration with Pioneer and have enjoyed developing a dedicated, customized multi-channel audio power conversion solution for them. The multi-channel module is the very latest of ICEpower's developments - it is quite revolutionary in its technology," said Peter Sommer, President and CEO of Bang & Olufsen ICEpower. "We have worked hard to deliver excellent audio performance, power density and efficiency in this solution, and hope that the Pioneer customers will be able to enjoy a new level of sound quality with the new home theater system."</p>

<p><br />
<B>Industry Leading Processing</B></p>

<p>Pioneer's Advanced Multi-Channel Acoustic Calibration (MCACC) is a precise room tuning function that ensures studio quality sound for an array of room configurations. With True One-Touch activation, the MCACC microphone immediately begins optimizing the acoustics in a room, making subtle adjustments to the connected speakers, neutralizing the sound field of the primary listening area with meticulous fine-tuning. Sonic performance is unlike anything previously possible in home theaters. With new Full Band Phase Control technology, the SC-09TX effectively eliminates "phase lag" caused by crossover networks inherent in loudspeakers ensuring audio arrives at the listening position in sync for a whole new level in accurate multi-channel sound reproduction.</p>

<p>Pioneer continues to lead sound processing innovation by incorporating the world's finest technological features to deliver smooth, natural sonic performance in consideration of the new high powered digital enhancement and ICEpower amplification. The SC-09TX is the first A/V receiver to feature Sampling Rate Converter (SRC), a high quality process traditionally used in professional audio equipment for sound studios. SRC is a key audio scaler that oversamples all audio signals to 192 kHz creating accurate time information of a sound field for jitter-free playback.</p>

<p>The SC-09TX excels in surround sound reproduction with the integration of a new volume control technology developed by THX. Consumers can take advantage of THX Loudness Plus, a new technology that ensures an audio mix is reproduced with full details and natural sounds at any volume level.</p>

<p>All movies, music and games are mixed at a reference level in-studio that is often too loud for home theater playback; however, reducing volumes can jeopardize the entertainment experience. Featured in the new Elite A/V receiver, home theater audiences can enjoy every audio nuance in movies, music and other soundtracks with THX Loudness Plus, which maintains the integrity of soundtracks when listening below the reference level giving the true impact of movies, music and games regardless of the volume setting.</p>

<p>As the first flagship A/V receiver in nearly three years, Pioneer engineers utilized hand selected, professional-grade digital components, notably the renowned Wolfson 192 kHz/24-bit digital analog converter (DAC). Approved by professional sound engineers, the addition of Wolfson DACs ensure significant prowess in Pioneer's new flagship model. Highly regarded among professional sound engineers, the SC-09TX boasts six Wolfson WM8741 D/A converters. By including the industry's most superior sounding DAC, entertainment enthusiasts, notably audiophiles, will recognize the high performance capability only found with Pioneer's new reference receiver.</p>

<p><br />
<B>High Resolution Audio Playback</B></p>

<p>As the popularity of 1080p home theater devices including, flat panel televisions and Blu-ray Disc players grows, the Pioneer SC-09TX A/V receiver delivers the full impact of lossless DTS-HD Master Audio, Dolby TrueHD and Dolby Digital Plus multi-channel audio formats via HDMI. Internal decoding of these advanced audio formats provides audiophiles robust, vivid surround sound performance that has long been desired. Video enthusiasts will appreciate the full impact these new audio codecs deliver to the viewing experience with rich, realistic sound that illustrate on-screen imagery. Sports fans will be able to feel the impact of hard-hitting tackles when watching sports movies and musicians can distinctly hear the acoustic guitar when enjoying concert discs.</p>

<p><br />
<B>High Performance Build Quality</B></p>

<p>For uncompromised high resolution audio playback performance, Pioneer engineers set out to develop a strong, rugged chassis build that could handle the powerful amplification without hindering sound quality. The revolutionary chassis construction rivals professional A/V products with its state-of-the-art modular build. The unique Separated Power Block Design isolates digital processing and amplification blocks and prevents internal interference between each block while improving the receiver's overall operation.</p>

<p>The SC-09TX has an ultra rigid, separated construction for digital, analog, audio, and video sections and further insulates hand selected premium components with a dual chassis internal construction. Unlike any receiver before it, the SC-09TX delivers the subtle nuances of delicate sounds with accuracy that audiophiles have been seeking. The ultra-rigid separation brings the true excitement of HD film for videophiles that ensures dynamic, smooth playback for high resolution products. While featuring maximum connectivity and a higher power output, the receiver's depth remains unchanged allowing it to easily fit on traditional A/V racks.</p>

<p><br />
<B>Home Media Gallery</B></p>

<p>The Pioneer SC-09TX delivers the most impressive home theater experience with the integration of Home Media Gallery, Pioneer's exclusive home networking functionality that allows users to select and playback personal digital media files direct from a PC hard drive for enjoyment in stunning high definition. Through the receiver, users can easily access and stream digital media files directly from their home PC or laptop computer with Home Media Gallery's IP networking capability. It is also compliant with Digital Living Network Alliance (DLNA), Window and Apple computers video, Windows Vista or Windows Media Connect as well as Microsoft playsforsure&trade; DRM technology.</p>

<p><br />
<B>Enhanced Digital Connectivity</B></p>

<p>Pioneer continues to provide the most robust entertainment connectivity with the SC-09TX:</p>

<p>* Sirius&reg; and XM&reg; Satellite Radio: The new receiver continues to lead digital entertainment options allowing users to enjoy both SIRIUS and XM Satellite Radio crystal clear, programming via dedicated connectivity to both a SiriusConnect&trade; SC-H1 tune and XM Connect & Play&trade; antenna (both sold separately; monthly subscription needed). Users can access and control each satellite radio devices with the unit's remote and large on-screen display capability. HD music can be enjoyed with XM HD Surround broadcasts powered by Neural Audio&reg; in 5.1 surround sound<br />
* Advanced iPod Operation: Users can navigate and select personalized music and video playlists from their iPod&reg; with the SC-09TX. The receiver supports LPCM audio transmission, via an included cable, for a more vivid and natural sound performance. Owners can control their personal digital player with the receiver's remote control and on-screen display.</p>

<p>Taking advantage of its audio heritage, Pioneer incorporated an improved Front Stage Surround Advance 2.1 channel surround sound feature as well as Advanced Sound Retriever to process and improve the playback of compressed audio files including MP3, WMA and iPod&reg; song lists to ensure a premium listening experience from the wide variety of new digital audio formats common to PC and internet entertainment sources.</p>

<p>The SC-09TX will begin shipping this winter for a suggested price of $7000.</p>

<p>Pioneer's Home Entertainment and Business Solutions Group develops high definition home theater equipment for sports and entertainment junkies. Its flat panel televisions, Blu-ray Disc players, A/V receivers and speakers heighten the emotions created by great HD content. The company brands include Pioneer&reg; and Elite&reg;. When purchased from an authorized retailer, consumers receive a limited warranty for one year with Pioneer products and two years with Elite products. More details can be located at www.pioneerelectronics.com.</p>

<p>Pioneer and Elite are registered trademarks of Pioneer Corporation.</p>

<p>HDMI is a registered trademark of HDMI Licensing, LLC.</p>

<p>BLU-RAY DISC is a registered trademark of Sony Corporation.</p>

<p>Dolby is a registered trademark of Dolby Laboratories.</p>

<p>Microsoft and Windows Media are trademarks or registered trademarks of Microsoft Corporation.</p>

<p>THX is a trademark of THX Ltd. which may be registered in some jurisdictions. All rights reserved.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  6, 2007 11:38 PM</b>
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
			<?=getComments(703)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 703)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/pioneer-showcases-new-flagship-av-receiver-outfitted-for-high-end-home-theater-performance-like-never-before.php" type="text/javascript" charset="utf-8"></script>
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