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
		AND e.entry_id = 3987";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3987 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3987 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3987";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2010/09/hdtv-and-home-theater-podcast-podcast-444-cedia-2010.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3987";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010</title>
	<meta name="keywords" content="ipod touch, wireless dock, home theater, internet access, blu ray, audio, ipod, home, volume, touch, video, high, access, iphone, using, quality, both, systems, internet, dolby, digital, wireless, disney, dock, system" />
	<meta name="description" content="Each year CEDIA (Custom Electronic Design and Installation Association) holds an event where the industry gathers to show off their wares. We’ve been to a few of these and actually enjoy the show more than CES. It takes the parts of CES that we are interested in and compresses it down to a more manageable show. We didn’t go this year but that won’t stop us from talking about some of the products we think are interesting." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2010/09/hdtv-and-home-theater-podcast-podcast-444-cedia-2010.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Each year CEDIA (Custom Electronic Design and Installation Association) holds an event where the industry gathers to show off their wares. We’ve been to a few of these and actually enjoy the show more than CES. It takes the parts of CES that we are interested in and compresses it down to a more manageable show. We didn’t go this year but that won’t stop us from talking about some of the products we think are interesting." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3987', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2010/09/hdtv-and-home-theater-podcast-podcast-444-cedia-2010.php">HDTV and Home Theater Podcast - Podcast #444: CEDIA 2010</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September 23, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>CEDIA 2010</h3>
<p>Each  year CEDIA (Custom Electronic Design and Installation  Association)  holds an event where the industry gathers to show off  their wares. We’ve  been to a few of these and actually enjoy the show  more than CES. It  takes the parts of CES that we are interested in and  compresses it down  to a more manageable show. We didn’t go this year  but that won’t stop us  from talking about some of the products we think  are interesting.</p>
<h4>XANTECH</h4>
<p><a href="http://www.xantech.com/Audio/AVDistribution/AudioDistribution/BDXTT/">BDXTT</a> ($100) &#8211;  The new Xantech BDXTT allows you to make a wireless  connection between  any A2DP Stereo Bluetooth® enabled audio source and  any single or  multi-zone audio system. This includes connecting  products such as the  Apple® iPad®, iPhone® or iPod touch®2G, a large  number of mobile phones,  and personal computers. Integrate these audio  sources into Xantech  multi-zone audio systems using the Xantech  MX88/MRC88 controller,  Digital Delivery System (DDS), or any other  audio amplifier or amplified  speaker with RCA or 3.5mm stereo  mini-phone inputs.</p>
<h4>Gefen</h4>
<p><a href="http://www.gefen.com/kvm/dproduct.jsp?prod_id=9108">GefenTV Auto Volume Stabilizer w/ Digital Audio Decoder</a> ($229) &#8211; The GefenTV Auto Volume Stabilizer with Digital Audio Decoder  product  serves two functions: it is a high-quality volume stabilizer  and also  delivers a mixdown from a 5.1 channel to two-channel, Left  Right audio  using Dolby AC3 decoding. It actually makes the volume  adjustments in  such a subtle way that you won&#8217;t even notice the change.</p>
<p>An  additional feature includes a bypass button that allows the   pass-through of unmodified incoming audio. Using Dolby Volume   technology, the unit senses changes in volume levels, and then maintains   a steady volume level for any input sound coming from DVD, Blu-ray or   other multimedia source. Listen to movies and TV shows easily without   the inconvenience of continuously fiddling with the volume levels. er)</p>
<p><a href="http://www.gefen.com/kvm/dproduct.jsp?prod_id=5277">GefenTV Home Theater Audio Processor</a> ($899) &#8211; The GefenTV Home Theater Audio Processor provides pass-through   connection for a Hi-Def source to two mirrored HDTV displays using  HDMI  features supporting 1080p full HD, Deep Color, Lip-Sync, Dolby  TrueHD,  DTS-HD MA, and 3DTV pass-through. The audio from both the HDMI  and coax  inputs is sent to the coax and optical outputs as compressed  AC-3  digital audio and to separate six-channel audio on the RCA and  binding  post connectors. Up to 5.1 channels of LPCM, Dolby Digital, and  Dolby  Pro Logic II is decoded on the output.</p>
<p>Speakers  can be  connected to the binding posts using the built-in amplifier,  delivering  25 watts per channel RMS. The audio can also be sent to an  external  power amplifier using the RCA connectors. The front-left and   front-right channels use bi-amplification to enhance the sound quality.</p>
<h4>Disney</h4>
<p><a href="http://disneydvd.disney.go.com/disney-wow-world-of-wonder.html">Disney WOW World of Wonder</a> (DVD $30, Blu Ray $35) &#8211; The Disney WOW World of Wonder Disc is a  definitive &#8220;how to&#8221; guide for in-home High Definition (HD)  optimization  of home entertainment systems featuring the help of classic  Disney  character GOOFY and including HD demonstration clips from  popular  Disney titles including Toy Story, Up, Bolt and Pirates of the  Caribbean: At World&#8217;s End.  The easy to follow on-screen guide is  designed to help consumers get  the best quality experience from their  home theater systems by providing  everyone from beginners and  enthusiasts to experts and custom  installers alike with valuable high  quality calibration tools.<br />
Also included on the disc are the following:</p>
<ul>
<li>Pixel Flipper &#8211; Exercises all of the pixels on the screen, to eliminate &#8220;burn-in&#8221; and &#8220;stuck&#8221; pixels.</li>
<li>Viewing Angle Diagnostic -Determines viewing angles on the owner&#8217;s monitor.</li>
<li>The  Video Encoder Stress Test &#8211; Pushes the limits of a professional  video  encoding system by presenting video encoding challenges running   simultaneously on the screen, allowing viewers to compare results to  the  WOW Benchmark Reference.</li>
<li>Direct  Access &#8211; Enables experienced users to bypass the regular  menus and go  to a list of all A/V Tools where they can access the tool  of their  choice directly.</li>
</ul>
<h4>Actiontec Electronics</h4>
<p><a href="http://www.actiontec.com/products/product.php?pid=192">Ethernet over Coax MoCA Network Adapter </a> ($175)   More and more electronic devices require high speed Internet access.   From IPTV to media centers, DVRs, BlueRay players, and game consoles   etc, the biggest challenge facing consumers is how to connect these   devices to the Internet. Now with MoCA technology, existing coaxial   wires in the home can, in essence, be converted to an Ethernet network   and deliver high speed Internet access to every connected device. Plus,   installation is a breeze. Simply plug a MoCA adapter into your Router   and to the device requiring Internet access. Most do-it-yourself   consumers can complete the installation in less than 5 minutes and   installation professionals no longer need to pull cables throughout the   house.</p>
<ul>
<li>Millions of homes in North America have an Actiontec MoCA solution</li>
<li>Coexists with most broadband services</li>
<li>Uses existing coax cabling</li>
<li>Incredibly easy to install</li>
</ul>
<h4>iPort</h4>
<p><a href="http://www.iportmusic.com/products/cm/CM-IW100T">iPort CM-IW100T In-Wall Control Mount for iPod touch</a> (Check with Integrator/Dealer) -</p>
<ul>
<li>Allows Wi-Fi connection of the mounted iPod touch for downloads of Apps, audio content and certain upgrades.</li>
<li>Delivers unbalanced audio output via a Cat5 connection up to 30 feet (9m) from the iPort to the audio wall plate.</li>
<li>Compatible with iPod touch 2nd generation and 3rd generation.</li>
<li>Charges the iPod touch while mounted.</li>
</ul>
<h4>Denon Electronics</h4>
<p><a href="http://usa.denon.com/ProductDetails/5414.asp">Denon AVR-4311CI Audio/Video Receiver</a> ($2000) -</p>
<ul>
<li>HDMI 1.4a Includes 3D Blu-ray Compatibility</li>
<li>Equipped With The Latest Surround Sound Decoders</li>
<li>Network Ready For Web Audio, Photo and Multi-Media PC Connectivity</li>
<li>Easy Setup With Automatic Room Acoustic Correction</li>
<li>HD Radio</li>
<li>Quality Discrete Power Amplifier Section</li>
</ul>
<h4>Sonos</h4>
<p><a href="http://www.sonos.com/">Wireless Dock for the iPhone/iPod Touch</a> ($119) &#8211;  The Sonos Wireless Dock accesses all of the music stored or   playing on an iPhone or iPod and sends it wirelessly to Sonos   ZonePlayers all throughout your home – before converting the music to   analog – guaranteeing the best possible audio quality.</p>
<p>The   Sonos Wireless Dock is compatible with the following devices: iPod   touch (1st, 2nd, and 3rd generation), iPod classic, iPod nano (3rd, 4th,   and 5th generation), iPhone 4, iPhone 3GS, iPhone 3G and iPhone, and   has been certified by the developer to meet Apple performance standards.   The wireless dock will be available in October.</p>
<h4>Also:</h4>
<p><strong>Runco </strong>introduced two high end projectors, the SC-50D  ($88,995) and the SC-60D  ( $98,995) both shipping in the fall. Both  projectors support 3D and  use the new Runco Smart Lens System.</p>
<p><strong>Savant </strong>Home  Automation Systems built a custom remote control out of an iPod Touch.  It does not look  like an iPod Touch rather like a custom remote  control. It also makes  VOIP calls. It can play video and it supports  FaceTime video chat. It  will ship first quarter 2011 for $399.</p>
<p><strong>Integra </strong>introduced two high end receivers. The  DTR-80.2 and the DTR-70.2 are  both 9.2 systems that include Windows 7  and support DLNA. Both should be  available by the time you read this  and go for $2,800 and 2,000  respectively. Additional features include:</p>
<ul>
<li>3D compatible</li>
<li>offer 1080p upscaling utilizing Reon-VX video processing</li>
<li>ISF Calibration</li>
<li>access to internet radio streams from several providers, including Pandora and Rhapsody</li>
</ul>
<p><strong>Mitsubishi</strong> introduced their new Diamond 3D projector  which can display a 3D image  up to 100 inches. Its SXRD based with a  120,000:1 contrast ratio. We  could not find pricing or availability  information.</p>
<p><strong>JVC</strong> DLA-HD250  LCoS technology for less than $3,000. They also introduce new projectors starting at $5K that include 3D.</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2010-09-24.mp3">Download Episode #444</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September 23, 2010 11:46 PM</b>
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
			<?=getComments(3987)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 3987)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2010/09/hdtv-and-home-theater-podcast-podcast-444-cedia-2010.php" type="text/javascript" charset="utf-8"></script>
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