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

	# Get author information
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4801 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4801
		AND a.topic_id = t.topic_id";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/05/bluray-review-back-to-the-future-trilogy.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4801";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Blu-ray Review: Back to the Future Trilogy" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Blu-ray Review: Back to the Future Trilogy" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Blu-ray Review: Back to the Future Trilogy" />
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
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

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
	<title>HDTV Magazine - Blu-ray Review: Back to the Future Trilogy</title>
	<meta name="keywords" content="– stars, stars ●, back future, ● designing, blu ray, ●, –, back, stars, future, time, film, films, trilogy, ○, series, production, original, ’s, dts, blu, ray, feature, lloyd, emeckis" />
	<meta name="description" content="In this classic sci-fi adventure from director Robert Zemeckis, eccentric inventor Doc Brown (Christopher Lloyd) turns a DeLorean into a time machine that inadvertently sends his young friend, Marty McFly (Michael J. Fox), 30 years into the past. While stuck in the 1950s, Marty disrupts his parents' destiny and risks throwing the time-space continuum completely out of whack. The only way back to 1985 now is to get mom and dad to pucker up.

The Back to the Future Trilogy is a cultural milestone in American film. It captured what the 1980’s were, what we thought the past was, and what we thought the future could bring. Most people would agree that the first movie is the best, and the series degrades as it goes along. I’m not saying..." />
	<meta name="title" content="Blu-ray Review: Back to the Future Trilogy" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Blu-ray Review: Back to the Future Trilogy" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/05/bluray-review-back-to-the-future-trilogy.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="In this classic sci-fi adventure from director Robert Zemeckis, eccentric inventor Doc Brown (Christopher Lloyd) turns a DeLorean into a time machine that inadvertently sends his young friend, Marty McFly (Michael J. Fox), 30 years into the past. While stuck in the 1950s, Marty disrupts his parents' destiny and risks throwing the time-space continuum completely out of whack. The only way back to 1985 now is to get mom and dad to pucker up.

The Back to the Future Trilogy is a cultural milestone in American film. It captured what the 1980’s were, what we thought the past was, and what we thought the future could bring. Most people would agree that the first movie is the best, and the series degrades as it goes along. I’m not saying..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4801', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/05/bluray-review-back-to-the-future-trilogy.php">Blu-ray Review: Back to the Future Trilogy</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>May 15, 2012</b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<h2>4.4 Stars (out of 5) - Rated PG</h2> <p>Synopsis</p> <p>In this classic sci-fi adventure from director Robert Zemeckis, eccentric inventor Doc Brown (Christopher Lloyd) turns a DeLorean into a time machine that inadvertently sends his young friend, Marty McFly (Michael J. Fox), 30 years into the past. While stuck in the 1950s, Marty disrupts his parents' destiny and risks throwing the time-space continuum completely out of whack. The only way back to 1985 now is to get mom and dad to pucker up.</p> <p><b>Starring:</b></p> <p>Michael J. Fox, Christopher Lloyd, Lea Thompson, Crispin Glover, Thomas F. Wilson, Claudia Wells, Marc McClure, Wendie Jo Sperber, George DiCenzo, Frances Lee McCain, James Tolkan, J.J. Cohen, Casey Siemaszko, Billy Zane, Harry Waters Jr.</p> <p><b>Director:</b></p> <p>Robert Zemeckis</p> <p><b>Blu-ray Release Date:</b></p> <p>October 26, 2010</p> <p><b>Subtitles:</b></p> <p>English SDH, French, Spanish</p> <p>Rating</p> <p>Overall rating weighted as follows:</p> <p><b>Audio 40%, Video 40%, Special Features 20%</b>, <b>Movie - its just our opinion so take it with a grain of salt</b></p> <h2>Audio 3.9 Stars (out of 5)</h2> <p><i>Dolby and DTS Demo Discs used as basis for comparison</i></p> <p>● Subwoofer – <b>2.5 Stars</b></p> <p>● Dialog – <b>5.0 Stars</b></p> <p>● Surround Effects – <b>4.0 Stars</b></p> <p>● Dynamic Range – <b>4.0 Stars</b></p><b></b> <p><b>English: </b>DTS-HD Master Audio 5.1, <b>French:</b> DTS 5.1, <b>Spanish:</b> DTS 5.1<b></b></p> <p>Upgrading audio from a 25 year old film can be a difficult task, and Universal did a satisfactory job of bringing this series into the future. Dialog is handled with care and doesn’t allow any lines from the script to be lost to time traveling adventure. Surround effects are often used throughout the series, and feature sounds of birds, ticking clocks, helicopters, flying cars, horses, and rolling thunder. At times it seems like the rear channels were forced to produce sound, and nothing really flowed naturally to the back. The subwoofer was the least impressive aspect of this DTS-HD mix, it can be heard but it’s weak and slight. You do notice it during gunshots, galloping horses, some music, a few explosions, the Delorean’s engine, and the sonic boom sound of time travel. High pitch sounds like Marty’s guitar sounded great but overall the dynamic range was a little flat.</p> <h2>Video 4.8 Stars (out of 5)</h2> <p><i>Spears &amp; Munsil Benchmark Blu-ray Edition used as basis for comparison</i></p> <p>● Color Accuracy - <b>5.0 Stars</b></p> <p>● Shadow detail – <b>4.5 Stars</b></p> <p>● Clarity – <b>4.5 Stars</b></p> <p>● Skin tones – <b>5.0 Stars</b></p> <p>● Compression – <b>5.0 Stars</b></p> <p><b>Codec:</b> VC-1, <b>Resolution:</b> 1080p, <b>Aspect Ratio:</b> 1.85:1, <b>Original Aspect Ratio:</b> 1.85:1<b></b></p> <p>This 27 year old trilogy looks just as good as many of the new Blu-ray releases. Colors are natural and full of life, and draw attention to lush green trees, Marty’s pink cowboy shirt, neon and florescent lights in the future, and Marty’s puffy red vest jacket. Film grain is kept at a medium level, and helps keep the cinematic feel of the films. The clarity is usually crisp and clean and brings out the details in loose hairs, beard whiskers, brinks on the clock tower, stripes on clothing, and zebra striped carpets. Skin tones look great when actor’s faces aren’t covered with bad makeup that tries to age them 30 years.</p> <h2>Bonus Features 4.5 Stars (out of 5)</h2> <p>● U Control - Universal's "exclusive signature feature" allows you to access "Setups and Payoffs," that show you how plot points are prepared. |"Storyboard Comparison," which shows the original conception of several sequences; and "Trivia Track," a host of factoids about various aspects of all three films.</p> <p>● Tales From The Future - A six part documentary about the film, from development through filming and release. These are:</p> <p>○ "In the Beginning" (27:24) - Covers pre-production and the original casting.</p> <p>○ "Time to Go" (29:54) - Production of the original film.</p> <p>○ "Keeping Time" (5:43) – Features Alan Silvestri's score.</p> <p>○ "Time Flies" (28:37) – Discusses the special effects and shooting the 2nd and 3rd film back to back.</p> <p>○ "Third Time's the Charm" (17:07) - Focuses on the production design of the 3rd film, as well as Christopher Lloyd's Doc Brown as a romantic character.</p> <p>○ "The Test of Time" (17:00) – Discusses the cultural impact of the movies.</p> <p>● The Physics of 'Back to the Future' (HD; 8:25) – A discussion with physicist Michio Kaku about the science of the films.</p> <p>● Nuclear Test Site Ending Storyboard Sequence (HD;4:12) – A look at the original ending of the film.</p> <p>● Back to the Future Night (SD; 27:10) – Footage of the pre-show of the first aired television broadcast of the first film.</p> <p>● Deleted Scenes (HD; 17:57 over three discs) - 16 deleted or extended scenes.</p> <p>● Michael J. Fox Q &amp; A (SD; 10:20)</p> <p>● Q &amp; A Commentaries with Zemeckis and Gale.</p> <p>● Feature Commentaries with Gale and co-producer Neil Canton.</p> <p>● Archival Featurettes, a compendium of older documentaries on the films, which includes:</p> <p>● Making of the Trilogy: Chapters One (SD; 15:30), Two (SD; 15:30) and Three (SD; 16:30), a 2002 documentary released with the DVD version of the films.</p> <p>● The Making of 'Back to the Future' Parts I (SD; 14:28), II (SD; 6:40) and III (SD; 7:32)</p> <p>● The Secrets of the 'Back to the Future' Trilogy (SD; 20:41) a Kirk Cameron hosted tv special which answers fan questions about the series.</p> <p>● Behind The Scenes, a series of archival material which includes:</p> <p>● Original Make-up Tests (SD; 2:17), where you can see Lloyd before his "Einstein-Stokowski" transformation;</p> <p>● Outtakes (SD; 5:23 over three discs), with gags and on set mishaps.</p> <p>● Production Design (SD; 2:55)</p> <p>● Storyboarding (SD; 1:29)</p> <p>● Designing the DeLorean (SD; 3:31)</p> <p>● Designing Time Travel (SD; 2:41)</p> <p>● Hoverboard Test (SD; :58)</p> <p>● Evolution of the Visual Effects Sequences (SD; 5:42)</p> <p>● Designing Hill Valley (SD; 1:08), more production design info.</p> <p>● Designing the Campaign (SD; 1:18), marketing info.</p> <p>● FAQs, text only questions about the series, with answers by Zemeckis and Gale.</p> <p>● Back to the Future: The Ride (SD; 31:06) – Feature about the ride from Universal Studios the theme park.</p> <p>● Music Videos of Huey Lewis and the News performing "Power of Love" (SD; 6:27) and ZZ Top performing "DoubleBack" (SD; 4:09).</p> <p>● Photo Galleries, which include production art, storyboards, photos, marketing materials and character portraits.</p> <p>● Theatrical Trailers and Teasers for all of the films.</p> <h2>Movie – 4.5 Stars (out of 5)</h2> <p><b>Review</b></p> <p>The Back to the Future Trilogy is a cultural milestone in American film. It captured what the 1980’s were, what we thought the past was, and what we thought the future could bring. Most people would agree that the first movie is the best, and the series degrades as it goes along. I’m not saying the 2nd and 3rd movies weren’t good, but when you match them up to a movie that’s as near to perfect, it’s hard to compete. However, watching all the films back to back is a real treat, similar plot points become apparent, certain character traits are easily noticeable, and the overall trilogy flows as one strong storyline. Back to the Future has become a family favorite in my household, and it’s a testament to the film because it stands up to the test of time. The films are thoughtful, smart, funny, and silly when they need to be. As a whole, the trilogy has several moral lessons, but at its core it tells us that the future is not written.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>May 15, 2012  9:54 PM</b>
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
			<?=getComments(4801)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4801)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
					<?=stripslashes($author['bio_short'])?>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/05/bluray-review-back-to-the-future-trilogy.php" type="text/javascript" charset="utf-8"></script>
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