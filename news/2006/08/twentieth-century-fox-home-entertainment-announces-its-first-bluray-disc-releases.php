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
		AND e.entry_id = 436";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 436 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 436 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 436";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/08/twentieth-century-fox-home-entertainment-announces-its-first-bluray-disc-releases.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 436";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases" />
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
	<title>HDTV Magazine - Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases</title>
	<meta name="keywords" content="blu ray, ray disc, master audio, lossless master, home entertainment, blu, ray, disc, releases, fox, first, lossless, audio, dts, master, director, home, dvd, features, entertainment, high, century, age, twentieth, includes" />
	<meta name="description" content="ICE AGE: THE MELTDOWN Is First Day-and-Date Release on Blu-ray and DVD in North America, Australia and Europe.

Continuing its unwavering and exclusive support for the Blu-ray Disc format, Twentieth Century Fox Home Entertainment President Worldwide Mike Dunn announced today the Studio's first wave of highly-anticipated motion pictures to debut on Blu-ray Disc (BD), which is the only high-definition packaged media platform broadly supported by the film, music, gaming, computing and consumer electronics industries. Representing more than $2 billion in box office and 90 million DVD units sold worldwide, the studio's first eight BD releases are right on target with the BD early adopter and Playstation 3 purchaser. Taking full advantage of the next generation format's high definition technology ..." />
	<meta name="title" content="Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/08/twentieth-century-fox-home-entertainment-announces-its-first-bluray-disc-releases.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="ICE AGE: THE MELTDOWN Is First Day-and-Date Release on Blu-ray and DVD in North America, Australia and Europe.

Continuing its unwavering and exclusive support for the Blu-ray Disc format, Twentieth Century Fox Home Entertainment President Worldwide Mike Dunn announced today the Studio's first wave of highly-anticipated motion pictures to debut on Blu-ray Disc (BD), which is the only high-definition packaged media platform broadly supported by the film, music, gaming, computing and consumer electronics industries. Representing more than $2 billion in box office and 90 million DVD units sold worldwide, the studio's first eight BD releases are right on target with the BD early adopter and Playstation 3 purchaser. Taking full advantage of the next generation format's high definition technology ..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=436', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/08/twentieth-century-fox-home-entertainment-announces-its-first-bluray-disc-releases.php">Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 31, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases; Available Worldwide In November, Initial Slate Targets The Early Adopter</p>

<p>IFA 2006 - World of Consumer Electronics</p>

<p>BERLIN & LOS ANGELES--(BUSINESS WIRE)--Aug. 31, 2006--</p>

<p>ICE AGE: THE MELTDOWN Is First Day-and-Date Release on Blu-ray and DVD in North America, Australia and Europe 	</p>

<p>Continuing its unwavering and exclusive support for the Blu-ray Disc format, Twentieth Century Fox Home Entertainment President Worldwide Mike Dunn announced today the Studio's first wave of highly-anticipated motion pictures to debut on Blu-ray Disc (BD), which is the only high-definition packaged media platform broadly supported by the film, music, gaming, computing and consumer electronics industries. Representing more than $2 billion in box office and 90 million DVD units sold worldwide, the studio's first eight BD releases are right on target with the BD early adopter and Playstation 3 purchaser. Taking full advantage of the next generation format's high definition technology and advanced Java-based functionality, titles will be presented with the highest quality audiovisual elements including AVC (MPEG4 Compression) and HD "Lossless" Audio (select tracks) and many have unique interactive special features.</p>

<p>Fox's stellar, action-packed line-up of initial BD releases includes: BEHIND ENEMY LINES, FANTASTIC FOUR, KINGDOM OF HEAVEN (Director's Cut), KISS OF THE DRAGON, THE OMEN (666), THE LEAGUE OF EXTRAORDINARY GENTLEMEN, SPEED and THE TRANSPORTER. This first wave of BD titles will arrive at retail outlets worldwide in November. Japan launches November 10 and product hits North American (SRP US$39.98/CAN$49.98), Australian and European stores on November 14.</p>

<p>"Blu-ray is the superior high definition format and come this holiday season it will be evident that it is really the only choice for consumers who want to enjoy pre-recorded high-definition content at home. It'll be that simple," noted Dunn. "It fully delivers on the promise of a next generation format and Blu-ray represents the bright future of the $50 billion global home entertainment industry and the inevitable successor to the incredibly popular DVD."</p>

<p>Added Dunn, "For our consumers, this first wave of titles from Fox and the incredible special features on them is an early glimpse of what they can expect from us as we assume a leadership position in the high-definition packaged media market."</p>

<p>Of Fox's Blu-ray announcement, three-time Academy Award nominated Director Ridley Scott commented, "I reviewed my Director's Cut of KINGDOM OF HEAVEN, which is 3 hrs and 8 minutes thereabouts, on Blu-ray Disc and I was astounded. It was like looking through a window of clarity. It was the most impressive thing I've ever seen."</p>

<p>The Studio's first day-and-date release with DVD is the concurrent BD release of ICE AGE: THE MELTDOWN (known as ICE AGE 2 Internationally) on November 21 in North America and the week of November 13 in Australia and select European territories. This inaugural day-and-date release will benefit from the comprehensive, multi-million dollar marketing campaigns already in place for ICE AGE: THE MELTDOWN, which will tout the availability of the title on both DVD and BD.</p>

<p>The BD release of ICE AGE: THE MELTDOWN, the global $645 million box office behemoth and sequel to one of Fox's best-selling DVDs ever, is authored in HDMV and presented with DTS HD Lossless Master Audio and explodes with all-audience friendly HD bonus materials. Chief among the added features is the never-before-seen CGI short "No Time For Nuts" -- created exclusively for the DVD and BD releases -- featuring more antics by the nut-obsessed breakout character "Scrat," voiced by ICE AGE director Chris Wedge. The disc also includes a Director's commentary, "Crash and Eddie" Stunts -- three CGI short shorts, The Animation Director's Chair, Lost Historical Films on the Ice Age Period, "Scrat's Piranha Smackdown" Sound Effects Lab, "Crash And Eddie" Blooper, and much more.</p>

<p>Fox's commitment to emerging technologies is dedicated to enhancing the consumer experience of its products and providing for backward compatibility with their existing home entertainment libraries while also aggressively protecting its intellectual property from piracy. The Blu-ray companies fully embrace the Studio's steadfast commitment to the fight against piracy and the preservation of the integrity of its properties. Twentieth Century Fox is a member of the Board of Directors of the Blu-ray Disc Association.</p>

<p>Titles and disc configurations are detailed below:</p>

<p>-- BEHIND ENEMY LINES: Marked as one of the studio's first three BD-J releases, BEHIND ENEMY LINES features DTS HD Lossless Master Audio and MPEG 4 compression. The disc also includes commentaries by Director John Moore, Editor Martin Smith, and Producers John Davis and Wyck Godfrey, as well as selectable HD trailers of upcoming BD releases.</p>

<p>-- FANTASTIC FOUR: Presented with DTS HD Lossless Master Audio, the HDMV Blu-Ray Disc of FANTASTIC FOUR boasts commentaries by Ioan Gruffud, Jessica Alba, Chris Evans, Michael Chiklis and Julian McMahon, and selectable HD trailers of upcoming BD releases.</p>

<p>-- KINGDOM OF HEAVEN (Director's Cut): To accommodate the full 3 hour and 42 minute run time of Ridley Scott's Director's cut version of his epic masterpiece, KINGDOM OF HEAVEN is one of the industry's first dual-layer BD releases and is authored in HDMV presented with DTS HD Lossless Master Audio.</p>

<p>-- KISS OF THE DRAGON: Authored in HDMV with DTS HD Lossless Master Audio, KISS OF THE DRAGON includes commentaries by Chris Nash, Bridget Fonda, and Jet Li, as well as selectable HD trailers of upcoming BD releases.</p>

<p>-- THE LEAGUE OF EXTRAORDINARY GENTLEMEN: One of the industry's most advanced BD releases, THE LEAGUE OF EXTRAORDINARY GENTLEMEN is authored in BD-J with DTS HD Lossless Master Audio and AVC (MPEG 4 compression) and includes commentaries by the cast and crew, a unique search index which allows the viewer to sort scenes from the movie into 72 categories ranging from actor (e.g., Shane West, Sean Connery) to character (e.g., Allan Quarterman, Agent Tom Sawyer) to locations (e.g., Paris, Venice), among others. Additional features include an interactive first person shooter game boasting 12 unique play modes, up to 99 bookmarks, an animated pop-up trivia track, and HD trailers of upcoming BD releases.</p>

<p>-- THE OMEN (666): Authored in HDMV with DTS HD Lossless Master Audio, THE OMEN (666) includes commentary by John Moore, Glenn Williamson and Dan Zimmerman, two featurettes and two extended scenes plus a BD-exclusive animated pop-up trivia track entitled "The Devil's Footnotes," which explores the history of the triple sixes (666).</p>

<p>-- SPEED: This BD-J release boasts DTS HD Lossless Master Audio and MPEG 4 compression. Special features include commentary tracks and commentary chapter selections by Jan De Bont, Graham Yost, and Mark Gordon, as well as an animated pop-up trivia track, up to 99 bookmarks, a 56-category search index (see description on THE LEAGUE OF EXTRAORDINARY GENTLEMEN) and a java game entitled, Speed: Take Down, touting six game play modes. The title also includes HD trailers for upcoming BD releases.</p>

<p>-- THE TRANSPORTER: Authored in HDMV and presented with DTS HD Lossless Master Audio, THE TRANSPORTER Blu-Ray Disc features commentaries by Actor Jason Statham and Producer Steven Chasman, in addition to host of selectable HD trailers of upcoming BD releases.</p>

<p>Blu-ray Disc is a next generation optical disc format developed for high-definition video and high-capacity software applications. A single-layer Blu-ray Disc holds up to 25 gigabytes of data and a dual-layer Blu-ray Disc holds up to 50 gigabytes of data. This greater storage capacity enables the Blu-ray Disc to store over six times the amount of content than is possible with current DVDs, and is particularly well-suited for high definition feature films with extended levels of additional bonus and interactive material. Blu-ray also features the most advanced copy protection, backward compatibility with the current DVD format, connectivity and advanced interactivity.</p>

<p>A recognized global industry leader, Twentieth Century Fox Home Entertainment LLC is the worldwide marketing, sales and distribution company for all Fox film and television programming on VHS and DVD as well as video acquisitions and original productions. Each year the Company introduces hundreds of new and newly enhanced products, which it services to retail outlets -- from mass merchants and warehouse clubs to specialty stores and e-commerce -- throughout the world. Twentieth Century Fox Home Entertainment LLC is a subsidiary of Twentieth Century Fox Film Corporation, a News Corporation company.</p>

<p>Contacts<br />
Fox Home Entertainment<br />
Steve Feldstein, +310-369-5369<br />
North America<br />
or<br />
Marla Rothschild, +310-369-5827 / +818-730-8393<br />
International</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 31, 2006  1:51 PM</b>
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
			<?=getComments(436)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 436)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/08/twentieth-century-fox-home-entertainment-announces-its-first-bluray-disc-releases.php" type="text/javascript" charset="utf-8"></script>
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