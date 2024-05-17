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
		AND e.entry_id = 5191";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5191 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5191 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5191";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2014/01/kaleidescape-cinema-one-review.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5191";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Kaleidescape Cinema One - Review" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Kaleidescape Cinema One - Review" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Kaleidescape Cinema One - Review" />
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
	<title>HDTV Magazine - Kaleidescape Cinema One - Review</title>
	<meta name="keywords" content="blu ray, per movie, ray disc, digital copy, kaleidescape commented, blu, ray, disc, kaleidescape, cinema, movie, content, digital, vault, discs, store, per, movies, system, quality, cost, dvd, server, copies, copy" />
	<meta name="description" content="Beyond the functionality and practical capabilities appreciated by most press reviewers, the primary reason of this article was to test and evaluate the storing and play back quality of Blu-ray audio/video using the Cinema One as a central/only server of content.

Kaleidescape targets the Cinema One to enthusiasts of high quality home-theaters looking to replace their existing players of physical media (CD, DVD, Blu-ray) with a product that claims to enhance the user experience by organizing and facilitating the access to a library that unifies their music and movie content.

At the end of this article I also provide a cost view for the consumer, depending on his/her style of collecting content, being physical disc, electronic, or both. In my opinion this type of investment should be evaluated not only by its upfront expenditure as a system, but also as a per-movie-cost-of-ownership when all is installed and all the movies are loaded.

Throughout the article you can also read the responses from Kaleidescape (in blue italic font).


 

What is the Cinema One?

The Cinema One is a Kaleidescape server to store and organize the digital copy of movies and music into its 4TB hard drive. It can handle up to..." />
	<meta name="title" content="Kaleidescape Cinema One - Review" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Kaleidescape Cinema One - Review" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2014/01/kaleidescape-cinema-one-review.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Beyond the functionality and practical capabilities appreciated by most press reviewers, the primary reason of this article was to test and evaluate the storing and play back quality of Blu-ray audio/video using the Cinema One as a central/only server of content.

Kaleidescape targets the Cinema One to enthusiasts of high quality home-theaters looking to replace their existing players of physical media (CD, DVD, Blu-ray) with a product that claims to enhance the user experience by organizing and facilitating the access to a library that unifies their music and movie content.

At the end of this article I also provide a cost view for the consumer, depending on his/her style of collecting content, being physical disc, electronic, or both. In my opinion this type of investment should be evaluated not only by its upfront expenditure as a system, but also as a per-movie-cost-of-ownership when all is installed and all the movies are loaded.

Throughout the article you can also read the responses from Kaleidescape (in blue italic font).


 

What is the Cinema One?

The Cinema One is a Kaleidescape server to store and organize the digital copy of movies and music into its 4TB hard drive. It can handle up to..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5191', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2014/01/kaleidescape-cinema-one-review.php">Kaleidescape Cinema One - Review</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>January 30, 2014</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=25&category=Entertainment">Entertainment</a></b>, <b><a href="/category.php?id=291&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b>, <b><a href="/category.php?id=30&category=Products & Equipment">Products & Equipment</a></b>
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
				<p>Beyond the functionality and practical capabilities appreciated by most press reviewers, the primary reason of this article was to test and evaluate the storing and play back quality of Blu-ray audio/video using the Cinema One as a central/only server of content.  <p>Kaleidescape targets the Cinema One to enthusiasts of high quality home-theaters looking to replace their existing players of physical media (CD, DVD, Blu-ray) with a product that claims to enhance the user experience by organizing and facilitating the access to a library that unifies their music and movie content.  <p>At the end of this article I also provide a cost view for the consumer, depending on his/her style of collecting content, being physical disc, electronic, or both. In my opinion this type of investment should be evaluated not only by its upfront expenditure as a system, but also as a per-movie-cost-of-ownership when all is installed and all the movies are loaded.  <p>Throughout the article you can also read the responses from Kaleidescape (<font color="#0000ff" size="2"><em>in blue italic font</em></font>).  <p><b></b> <p><b></b>&nbsp; <p><b>What is the Cinema One?</b>  <p>The <a href="https://store.kaleidescape.com/hardware/details/limited-edition-cinema-one" target="_blank">Cinema One</a> is a Kaleidescape server to store and organize the digital copy of movies and music into its 4TB hard drive. It can handle up to 100 Blu-ray-quality movies or 600 DVD-quality movies.  <p><img title="Cinema One" border="0" alt="Cinema One" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb_1.png" width="286" height="124">  <p>The server can play the digital copy of media, and can also play BDs, DVDs, and CDs as a typical Blu-ray player does, but as mentioned above, this review concentrates in using the Cinema One for Blu-ray quality content.  <p>It can easily import Blu-rays, DVDs and CDs into its hard drive, and play them back from it, but to comply with AACS content protection licensing in order to play back the digital copy of an imported Blu-ray disc the player requires the BR disc to be inserted (not required for DVD and CDs).  <p>The Cinema One is a more-price-friendly server ($3,995) relative to the Premier line of Kaleidescape servers typically installed in whole house systems, yachts, and resorts, with prices commonly in the range of $20,000 and up depending on the complexity of the installation.  <p>&nbsp; <p>They both have similar user interfaces, features, and access to <a href="https://store.kaleidescape.com/movies" target="_blank">the Kaleidescape Store</a><b></b> to download HD movies and TV shows at the claimed video and audio “bit-for-bit quality”, which include the extra features, languages, subtitles, etc.  <p>Up to two Cinema One players can be networked to double up capacity, and each player can access the full library (of the then 200 digital copies of Blu-ray quality movies, in total).  <p>A free iOS control app for iPad is offered with all the remote control functionality, including the ability to browse the collections without interfering with the content being played by the TV/projector.<img title="Cinema One with Kaleidescape Store of movies" border="0" alt="Cinema One with Kaleidescape Store of movies" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb.png" width="399" height="193">  <p>The audio capabilities include pass-through Dolby TrueHD and DTS-HD Master Audio over HDMI (to be decoded by an external A/V receiver or pre/pro), and decoding of Dolby Digital and DTS (up to 5.1 channels though). In addition to its HDMI connection the Cinema One has digital Coaxial and stereo analog outputs for backward compatibility (more details on the specs at the end).  <p>The video can be output as high as 1080p (also as 24fps), 720p, or as pass-through resolution, and as 16:9 or CinemaScape 2.35:1 letterbox/anamorphic/native 2.35:1, to the display device.  <p>The main strength of a Kaleidescape system is its very user friendly interface and content organization, with movies and music libraries presented as lists or grids of colorful art covers. The user clicks here and there and gets lots of well presented information about the content, the artist, the director, etc., which is quickly displayed in the screen or the iPad app, and can be (re)grouped instantly to make the user experience very unique, very Kaleidescape.  <p><b></b> <p><b></b>&nbsp; <p><b>Testing Environment</b>  <p>The testing environment I used is my own home-theater, which may as well be Kaleidescape’s target audience for this product.  <p>A Sony 4K projector, Theta Casablanca high-end pre-pro, 15 speakers, 130-inch 2.35:1 Stewart Firehawk Cinemascope screen, electrically controlled masking, motorized curtains, controlled lighting, sound treated room, Theta Dreadnaught and other amps, Oppo Blu-ray player/s, Darblet video processor, high quality wiring, touch screen remote, etc.  <p>From the point of view of content, since the 1960s I pursue Kaleidescape’s idea of collecting media but I rather own my physical discs movies, concerts, and CDs, because I appreciate the uncompromised visual and sound quality of an original recording at its best, the feeling of owning, handling, and experiencing a physical product that I expect will be able to enjoy for a long time under my personal control.  <p>But I also recognize the practicality of digital storage, of instant streaming, and of downloading Internet content, because at a given moment I may have interest in content not available in my collection, and to consume it I may have to accept some quality compromises. I also recognize that some people only want downloaded or streamed content, regardless of quality. Fortunately we have industries and markets for both.  <p><b></b> <p><b></b>&nbsp; <p><b>First Impressions with the Cinema One</b>  <p>The Cinema One is a sophisticated movie and music server that is very simple to setup, organize and use compared to other alternatives such as Do-It-Yourself solutions which may be complicated to many people if having to make copies of discs, illegally or legally, and having to deal with hardware, software, and computers in general for an entertainment purpose.  <p>There are also other “Kaleidescape-like” solutions that may allow play back of Blu-ray disc digital copies without having the BR disc in the player, and some that expect the customer to obtain and use software to rip Blu-rays into their system, shifting liabilities to the consumer on doing it, and managing the content from that point on.  <p>Although a Cinema One pair is limited to two rooms, Kaleidescape has other whole house solutions, please find complete details and cost evaluation at the end of this article.  <p>As an option, to actually store the physical discs of a collection the <a href="http://kaleidescape.com/products/premiere/disc-vaults/dv700/" target="_blank">DV700 Disc Vault</a> has a carousel to hold up to 320 discs, and can automatically import the contents of up to 100 BR discs into the Cinema One’s 4TB hard drive, and eliminate the requirement of having to insert the Blu-ray disc into the Cinema One when playing the same movie from its hard drive (that requirement does not apply for imported CDs and DVDs).  <p><img title="DV700 Disc Vault" border="0" alt="DV700 Disc Vault" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/clip_image006_thumb.gif" width="240" height="122"></a>  <p>Movies stored into the Cinema One have the same special features, audio and subtitle tracks of the disc. One feature a viewer will welcome with pleasure is that the content starts playing almost immediately, skipping previews, disc menu, warning images, etc. It also offers direct access to preferred "scenes" of the stored movie, and remembers the play position of movies so a viewer can return to the same point later on.  <p>The system offers the ability to “upgrade” DVD movies imported into the Cinema One to HD digital copy downloads for $5.99, if the HD version is available to download. The system also offers to download the digital copy/Ultraviolet version of Blu-ray discs that were imported into the Cinema One for $1.99, if they are available.  <p>According to Kaleidescape’s press release: <i><font color="#0000ff" size="2">“For only $1.99, movie-lovers can now go disc-free with digital copies of the Blu-ray discs they already own… includ(ing) UltraViolet rights for viewing on TVs, PCs, or mobile devices.” </font></i> <p>This option may interest those wanting to store the discs away in a cabinet and not spend in a vault, “if” the Kaleidescape Store has the movie available for the upgrade. <i></i> <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> The movies imported to a Kaleidescape System that are eligible for upgrades are conveniently listed on the Store under “Your Library”. Today, DVDs can be upgraded to Blu-ray quality downloads for $5.99, and Blu-ray disc imports can be upgraded to Blu-ray quality downloads for $1.99. As described earlier, this eliminates the need for the disc to be present at the time of playback.</i><i></i></font></font>  <p>Although the specs are listed at the end of this article I highlight some connectivity features as follows: USB Wi-Fi adapter, HDMI and Ethernet cables included, USB port, Ethernet port for wired network connection, free iOS control app for iPad, child remote allows for children content access only, etc.  <p><b></b> <p><b></b>&nbsp; <p><b>Audio Experience</b>  <p>The playback of CDs and stored copies of CDs had good sonic dynamic range (i.e. Tchaikovsky’s 1812), and although the sound quality of some of the demo CDs I tested and imported was reasonably clear, I detected some mild roughness in some complex music harmonics, hi-frequency notes, and vocals.  <p>For other than casual listening I preferred the Oppo reference player I used for the comparisons, which provided slightly better spatial openness of the performance and clear separation and localization of instruments and vocals left-to-right and back-to-front, especially on audiophile recordings that originated from higher resolution/sampling sources.  <p>However, the difference was barely noticeable after repeated A/B comparisons, trying to identify minor sonic quality differences, which is unusual during casual listening. They would have been difficult to identify by untrained ears unless someone tips the listener as to what exactly to pay attention to, and it may have been impossible to listen with low quality audio equipment and cabling, which typically mask the quality of a good recording (or the lack of it).  <p>In other words, the audio quality was acceptable enough for the primary purpose of the Cinema One.  <p><b></b> <p><b></b>&nbsp; <p><b>Video Experience</b>  <p>Other than the experiences noticed below, the display of HD (Blu-ray quality) movies imported or downloaded was mostly similar to the Blu-ray disc quality viewed on the large screen at the standard per-picture-height distances recommended for HD and 4K, and even closer.  <p>I missed some features available in the Oppo Blu-ray player, such as subtitle positioning “with up/down steps” to position subtitles A) within the image on a Cinemascope projection/screen as high as the viewer prefers, or B) lower within the bottom back bar for 16:9 TV panels/screens below the Cinemascope image as low as the viewer prefers.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> You can find subtitle repositioning settings in the “Language &amp; Subtitles” page of the Settings menu. To access this, press the <b>Menu</b> button on the Kaleidescape remote, then select <b>System</b>. Select <b>Settings</b>, and scroll down to the <b>Language &amp; Subtitles</b> page.&nbsp; </i></font></font> <p>I agree that there is a subtitle setting as Kaleidescape commented above, but the setting does not position in up/down gradual steps nor it allows for it to be moved down to the black bar if needed, it only offers the choice of “always position the subtitle within the image” or “to work with screen masks”.  <p>The system settings are shown always as full screen rather than allowing some settings to be shown superimposed to the viewed image to change the audio or subtitles on the fly without having to stop the playback of the content. The viewer has to switch from viewing the movie, to go to the system settings screen, make the desired changes, and then to come back to the movie, granted at the point it was left (as option), but those minor adjustments disrupt the viewing.  <p>I also noticed that several movies appeared to have an image “enhancement” while playing a BR disc or its digital copy stored version of the Cinema One, which prompted me to disable the Darblet video processor because the cumulative effect was unacceptable. The Darblet is typically “on” almost all the time, in-line between the content source and the 4K projector.  <p>For those that are not familiar with the Darblet, it is a high quality video processor broadly revered by the video industry to improve the image quality of most content; I reviewed the product a few months ago and I concur with that.  <p>On the other hand, when I played the original Blu-ray version of the same movie using the reference Oppo player it showed very well, and its image quality improved with the Darblet on. It was then obvious that the Cinema One was adding something to the original image, more noticeable in skin close ups, increasing the appearance of skin imperfections.  <p>The effect was similar to what the Darblet does with film grain in old movies, making grain more noticeable by treating it as individual picture elements to enhance, together with the rest of the image. For that reason, to my taste, the image on old grainy movies looks better with NO Darblet processing (details of the effect can be found in <a href="http://www.hdtvmagazine.com/reviews/2012/12/review-darbeevision-visual-presence-dvp-5000-darblet.php" target="_blank">this review</a>). However, the Cinema One viewing experience above was not about film grain.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> You can find the option to turn on/off detail enhancement from the Advanced page in Video Settings. To access this, press the <b>Menu </b>button on the Kaleidescape remote, then select <b>System</b>. Select <b>Settings</b>, and scroll down to <b>Video</b>. Press <b>OK </b>to enter the video settings, and then scroll down to the <b>Advanced</b> page to enable/disable the detail enhancement setting.</i></font></font>  <p>After Kaleidescape’s comment I found the setting for the enhancement, it was “on”, but I was surprised that it was set to “on” by default by the Cinema One. I tested again with the enhancement “off” and then I was able to use the Darblet as I normally do, and the image appeared similar to Blu-ray quality.  <p>My suggestions are: make its default to be “off”, like the “Blacker than black” setting (enhancements should be “off” by default). Another suggestion, replace the circuitry used for the “enhancement” feature and use instead the Darblet circuitry as standard feature in the next version of the Cinema One. The Darblet is now included in high quality products such as the newer Oppo Blu-ray players and Lumagen video processors and the Cinema One deserves such quality.  <p>4K upscaling was missing, as well as 3D, calibration settings, etc.  <p><b></b> <p><b></b>&nbsp; <p><b>Functionality </b> <p>In general the Cinema One was very functional, very “laid back and enjoy” type of user experience. With the tablet paired with Wi-Fi there is no need to be close to the Cinema One, useful as whole house music control. It felt like a luxury item (and because of the high price it may actually be a luxury for most people). One can get in love fast with the power of having all the content collection under the finger tips, jumping from a track of a CD to the starting point of a movie in a matter of seconds. It certainly appealed as a practical and efficient product, with well designed user friendliness.  <p>It seems minor but I was surprised not finding an “Eject” button on the remote or the tablet app, which means that the user has to go to the Cinema One unit to operate the eject, which can be inconvenient if the unit is into an equipment rack/closet for the whole house/home theater, an scenario that is to be expected for the target audience of this product.  <p>Granted, if a user wants the disc to be ejected it is usually to also stand up and take/replace the disc anyway, but I do not remember seeing any disc player remote that lacks the open/close/eject button.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Discs in a vault can be ejected from the onscreen details for that movie. For instance, if you are looking at the movie details page for the movie Argo which you imported to your system via disc, the details page will show you that you have the disc in the Disc vault named ‘&lt;name of disc vault&gt;’. This can be ‘Basement Vault’ or some other name of your choosing to give you an indication of where the disc vault is. For discs that are in any Kaleidescape disc vault, there is an Eject disc option in the details sheet for that movie. </i></font></font> <p>My comment to that comment is that I was reviewing the Cinema One not the Vault. The Cinema One is a server but also a disc player with a drawer to insert a disc, and should have an eject button on its remote and the app used to control it.  <p>Regarding the feature of importing, there should be a way to import just the “individual” tracks of interest from a CD, DVD, and Blu-ray into the Cinema One, or to been able to delete some tracks after they are all imported, so storage space can be maximized.  <p>Importing/downloading only the content of interest makes room for more content of interest, reduces the time the Cinema One takes to store it, makes the content available to be played sooner, and uses less network time/bandwidth, especially important if having ISP cap restrictions that could increase the cost of the Internet service (it took over 8 hours to download each of the 3 Blu-ray movies I tested, at about 10/15+ Mbps on my 100Mbps Fiber line).  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Thanks for this suggestion. As you have discovered, we do not support trackbased import.</i></font></font>  <p><b><font color="#0000ff" size="2"></font></b> <p><b><font color="#0000ff" size="2"></font></b>&nbsp; <p><b>Content</b>  <p>Although the pricing of content may not be of concern to a home-theater enthusiast that can invest $3,995 on this type of equipment, I noticed that the pricing of most old and current HD movies is higher than their physical disc versions, sometimes considerably higher.  <p>For example, while I was reviewing the unit the HD movie “Man of Steel” was offered by the Kaleidescape Store for $26 when Amazon sold the Blu-ray for $19 including the BD, DVD and Ultraviolet; that is 37% extra and no disc. It should be exactly the opposite. But the shocker was when I bought the Blu-ray disc (with DVD and Ultraviolet) the same week for 9$+ at Best Buy, while the Kaleidescape Store still had it at $26 (90% extra).  <p>It is to be expected that prices fluctuate with time and specials on the various markets, but parallelisms of such large discrepancies do not help.  <p>The way I see is simple: the primary value of functionality, practicality, and user experience of the Cinema One should have been well paid already by the high $3,995 selling price, and the Kaleidescape Store content should “always” cost less than physical media that requires retail space, distribution, transporting, manual handling, etc. and plays on a $100 BD player with perhaps the same video/audio quality.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Pricing across formats and channels of distribution cannot be directly compared due to differences in licensing terms, costs, and channel dynamics.&nbsp; That said, we believe we have competitive prices and we do monitor and adjust our pricing regularly.&nbsp; For example, Man of Steel is on our Store for $16.99 (This demonstrates how often we are reviewing and adjusting our pricing model). </i></font></font> <p><i><font color="#0000ff" size="2">Also note that we offer special collections where titles only cost $6.99 for HD and $4.99 for SD content. See this week’s example of our special Tour de Force collection: https://store.kaleidescape.com/movies/collections/tour_de_force</font></i>  <p>There are no musical concerts on the available media for download from the Kaleidescape Store, while Amazon offers hundreds of concerts. About a third of my disc collection is made of concerts, which is also a main demonstration tool for what a home theater can offer.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> We currently have licensing deals with Warner Bros. and Lionsgate and their catalog of content is available for sale on the Kaleidescape Store. We are in active negotiations with many other studios and we expect to expand content available for sale in the course of 2014.</i></font></font>  <p>None of the 20+ Blu-ray new release action movies I purchased over the “past couple of years” was available for download. It appears that is due to a smaller studio selection or content restriction from certain studios, but the system would then require me to import my discs into the server and still require the Blu-ray disc to be present in the Cinema One while viewing, which defeats the practicality of having a whole house/home theater server to avoid handling physical media, unless one invests considerably more on the vault for Blu-ray discs.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Kaleidescape has license rights for more than 5,250 movies and 8,000 episodic titles and plans to expand that significantly over the next year. We are in direct negotiations with all remaining major studios. Most of these titles will support our attractive Disc-to-Digital program which allows our customers to take their Blu-ray discs out of the vault by upgrading to a digital copy. The </i><i>Kaleidescape </i><i>Store is the </i><i>first online store that enables consumers to download movies and TV series with video and audio quality equal to Blu-ray Disc and DVD</i><i>. But, our customers are still heavily dependent on physical discs and the vault has proven to be an effective solution for dealing with Blu-ray discs especially in the high-end home theater market. </i></font></font> <p><b></b> <p><b></b>&nbsp; <p><b>Tablet / Downloading Features</b>  <p>There should be a way for the tablet app to play the trailer of a movie before ordering.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> We are absolutely committed to making the shopping experience as entertaining and as delightful as possible. We fully expect to provide rich shopping experiences across all interfaces to the Kaleidescape Store.</i></font></font>  <p>As with the <img title="iPad Tablet app to control the Cinema One" border="0" alt="iPad Tablet app to control the Cinema One" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb_2.png" width="240" height="227"></a> Cinema One unit itself, the app should have the option to list titles rather than only showing art covers of content, which occupy more space in the small tablet screen.  <p>As mentioned above, because there is no way to selectively ignore individual tracks of imported/downloaded content to maximize storage usage, an alternative maybe to have the option of deleting such material after a full import, especially for already owned discs whereby such bonus/supplement could be a waste of HDD space considering it could be selectively re-imported if eventually needed.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Supplement discs can be deleted after import (or just not imported) but if the main “feature” disc contains supplement content, then that cannot be deleted. (From the details sheet of a movie, go to <b>More Options</b>, and then <b>Delete</b>”). The files downloaded from the Kaleidescape Store are bit-for-bit replicas of the source discs. They provide the exact same playback quality as is available from the DVD and Blu-ray Disc masters. </i></font></font> <p>The number of titles available for download on each genre should be shown in the tablet in the left column, and that number and genre should continue showing on each screen as the user scrolls down, so the user can see that the titles still belong to the chosen genre without having to return to the first screen to find that out.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u> </i><i>Like most sites, the Kaleidescape Store is constantly being improved. Since the launch, in April of 2013, we’ve had five major Store releases. </i></font></font> <p>When using the tablet app (iPad) the System Browser Interface button returned a red message informing that because Flash Player was not available it could not properly present the screen/data as intended.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> The app does not require Flash, but there is a web interface (System Browser) that was built prior to the iPad app that uses a Flash-based audio file to sound a notification tone. The app provides a link to the web interface as a convenience for those who would like to monitor content being added to their system, or to edit metadata about the content being import or downloaded (i.e. to add a title and list of “actors” for a DVD containing home movies).</i></font></font>  <p>Unless the Wi-Fi was “on” in the tablet and was paired to the Cinema One the meta/data about the content was not available, no data was available for music either. If the central system knows what is stored into the Cinema One of a customer there should be a way for the unpaired tablet app to show meta/data details of the stored content via account recognition regardless how is connected to the Internet.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Like other mobile apps, our app will continue to evolve.</i></font></font> <p><b><font color="#0000ff" size="2"></font></b> <p><b><font color="#0000ff" size="2"></font></b>&nbsp; <p><b>Basic Questions of Product Direction</b>  <p>In the process of evaluating this product I asked the manufacturer the following questions (their response is included):  <p>I asked the company when would the Cinema One be able to handle 3D, playback of SACD/DVD-audio, output 7.1 channels decoded by the player (not just available as streaming), have image adjustment/audio/speaker setup features, to actually eliminate the need of having to keep parallel players that perform such functions. Many &lt;$100 Blu-ray players play 3D.  <p><font size="2"><font color="#0000ff"><u><i>Kaleidescape commented</i><b><i>:</i></b></u><i> We cannot comment on future products, but we are aware of the trends in the industry.</i></font></font>  <p>When the Cinema One would be able to play imported Blu-rays without the requirement of having the disc present, and without investing on an additional vault?  <p>Although the requirement is not Kaleidescape’s but rather to comply with AACS content protection licensing conditions, it defeats the user experience claimed by the product when playing imported Blu-Ray discs, unless the user invests on a $3,995 <a href="https://store.kaleidescape.com/hardware/details/dv700-disc-vault" target="_blank">vault</a> to store up to 320 discs (of any kind), or buys the downloaded digital copy of every Blu-ray.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> The requirement to have the Blu-ray disc present at the time of playback is a contractual requirement by the AACS, and not a requirement that Kaleidescape imposes without reason. We do not expect this to change, but purchasing Blu-ray quality movies from the Kaleidescape Store gives you all the quality you would get from a disc, without requiring the disc. Also, the Kaleidescape Store offers convenient Disc-to-Digital upgrades for just $1.99. Besides delivering all of the benefits of UltraViolet for casual viewing on the go, Kaleidescape’s Disc-to-Digital program eliminates the need for disc presence and permits the upgraded title to be downloaded to up to five (5) Kaleidescape Systems.</i></font></font>  <p>Considering the market arrival of 4K for consumers over the past couple of years, and of 4K media players for downloading and streaming 4K content, I asked if there are any plans/efforts on implementing 4K using the same server concept (the response was “none” several months ago when I requested the review unit).  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented</u></i><i><u>:</u> We cannot comment on future products, but we are aware of the trends in the industry.&nbsp; </i></font></font> <p><b><font color="#0000ff" size="2"></font></b> <p><b><font color="#0000ff" size="2"></font></b>&nbsp; <p><b>Per-Movie and Total-Cost-of-Ownership of the Cinema One System (and Premiere)</b>  <p>As with the rest of the review, the cost evaluation below is based on using the Cinema One exclusively for <b>Blu-ray quality content. </b> <p>Although the system can hold more digital copies of DVDs and CDs than Blu-ray content I concentrate in Blu-ray because is the best quality of movie content Kaleidescape supports, and also because mixing the content types adds complexity to the calculations, which the buyer can do using the data below.  <p>Those that do not see cost as an issue may ignore the cost-benefit analysis in this chapter.  <p>The cost of the <a href="https://store.kaleidescape.com/hardware/details/cinema-one" target="_blank">Cinema One</a> ($3,995) is relatively high compared to similar systems and DIY solutions, and is also high for just “storing content” (4TB for 100 Blu-ray movies is about $40 each to store its digital copy into the Cinema One).  <p>That is in addition to paying $20 for a movie download, or for the price of a Blu-ray to a disc collector. Although many older movies are lower in price, there are many new releases that are higher than $20, either in Blu-ray or in downloaded digital copy. The per-movie-cost-of-ownership would then be $60 per Blu-ray quality movie for the Cinema One to store it, and the viewer has to insert the BR disc to play the digital copy.  <p>The cost could even be higher if higher ISP fees or Internet caps are applied depending on the downloading activity and the ISP provider/plan.  <p>A disc vault (<a href="https://store.kaleidescape.com/hardware/details/dv700-disc-vault" target="_blank">DV700</a>, $3,995) can physically store up to 320 of Blu-rays, and import them into the Cinema One so the Blu-ray disc will not be required to be present on its front drawer at playing time. The vault price per Blu-ray disc would be $12 per disc ($3,995/320) just to store the disc into it; however the Cinema One pair system can only store 200 digital copies of those 320.  <p>On a phone call to Kaleidescape I was told that the company is working in some kind of Cinema One expansion to match the full 320 disc capacity of the vault and been able to store the digital copies of the 120 vault discs beyond the 200 BR stored in the dual Cinema.  <p>Therefore, my evaluation of per-movie-cost-of-ownership of the vault should be in tune with the current capacity of the Cinema One pair linked to the vault, which is 200. The $3,995 vault price should actually be divided by the 200 BR movies the Cinema One can play with the vault ($20 per BR movie).  <p>The resultant $20 per BR movie for the vault is in addition to the cost of storing the media digitally ($40) in the Cinema One server, and to the price of the BR disc ($20) to a collector, as mentioned above.  <p>In summary, to an owner that prefers to have a Blu-ray collection and put the discs in a vault to avoid having to insert every disc when a movie needs to be viewed, the cost of ownership per movie raises from the $20 Blu-ray disc purchased by a collector, to <b>$80</b> per BR movie as follows:  <ul> <li> <div align="left">$40 for Cinema One server storage cost per BR movie</div> <li> <div align="left">$20 for DV700 Vault per BR movie stored in the Cinema One adjusted per today’s capacity limitations</div> <li> <div align="left">-------------------------- </div> <li> <div align="left">$60 per movie of Kaleidescape components (3 times the cost of the BR movie if using a vault)</div> <li> <div align="left">+</div> <li> <div align="left">$20 for the price of the purchased Blu-ray disc</div> <li> <div align="left">--------------------------</div> <li> <div align="left"><b>$80 per-movie-cost-of-ownership for Blu-ray collectors </b></div></li></ul> <p>&nbsp; <p>If the whole storage of the Cinema One and the vault slots are not fully used, the cost per movie increases proportionally because the total price of the equipment must be divided into the fewer number of movies the system is used for, for example if only 130 Blu-rays are held at the vault and imported in the Cinema One the cost per movie should be divided by 130 rather than 200 or 320.  <p>However, those not interested on a BR disc collection and want to download the whole library (at $20 per new release movie to facilitate calculations) do not need to have a vault. Granted, there are many movies at lower prices (and higher), and that applies to Blu-rays as well, and you can adjust the number to your case if you only collect old movies for example, or do your own average.  <p>The vault is also not necessary for those that own BR discs and prefer to download the digital copy upgrade for $1.99 each and store the discs away (if the movie is available for upgrading, otherwise this alternative is not applicable to a whole collection).  <p>&nbsp; <p>In both cases the per-movie-cost-of ownership can drop $20 per movie due to the unneeded vault as all content will be stored in the hard drive of the Cinema Ones, as follows:  <p><b>1) </b><b>BR owners</b> interested on the $2 ($1.99) digital copy upgrade and no vault: $40+$2 = $42 in Kaleidescape products + $20 of Blu-ray cost even when it will be put away = <b>$62 per movie</b>  <p><b>2) </b><b>Download only owners</b> interested on not having discs: $20DC+$40 = <b>$60 per movie</b>  <p>&nbsp; <p>In summary: Regardless if you prefer “digital copies only” or a “vault based BD collection with digital copies”, a range of $60, $62, and $80 per movie is to be expected to enjoy the better user experience of Kaleidescape.  <p>What happens when you need more storage than 2 Cinema Ones (200 digital BR copies) and a vault (320 discs)?  <p><b></b> <p><b></b>&nbsp; <p><b>There are other Kaleidescape system options in the Premiere line</b>  <p>As alternative to the Cinema One system, depending how large the system is needed for the whole house, Kaleidescape has the <a href="http://kaleidescape.com/products/premiere/" target="_blank">Premiere line</a> with servers, players, and vault as follows:  <p>· <a href="http://kaleidescape.com/products/premiere/players/" target="_blank">Players for any room</a> (M300 and M500)<img title="M300 and M500 Premiere Players" border="0" alt="M300 and M500 Premiere Players" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb_3.png" width="240" height="73">  <p>The M300 only plays content from a server and cannot import discs,  <p>The M500 can import, and can also play the discs on the unit),  <p>MSRP pricing of the players: M300 $2,995, M500 $4,495.  <p>&nbsp; <p>&nbsp; <p>· <a href="http://kaleidescape.com/products/premiere/servers/" target="_blank">Servers for the whole house</a> (1U and 3U)  <p><img title="1U Premiere Server" border="0" alt="1U Premiere Server" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb_5.png" width="240" height="111">  <p>The 1U with 8TB, includes four 2TB disk cartridges that would store up to 150 digital copies of Blu-ray discs (or digital copies of 900 DVDs) for $9,495 (/150=$63 x BR movie),  <p>The 1U with 16TB, includes four 4TB disk cartridges that would store up to 325 digital copies of Blu-ray discs (or 1,800 digital copies of DVDs) for $11,495 (/325=$35 x BR movie),&nbsp; <p>The 3U with 32TB, includes eight 4TB disk cartridges (and six expansion slots) that would store up to 650 digital copies of Blu-ray discs (or 3,600 digital copies of DV<img title="3U Premiere Server" border="0" alt="3U Premiere Server" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb_6.png" width="240" height="110">Ds) for $24,195 (/650=$37 x BR movie),</p> <p>The 3U with 56TB, includes fourteen 4TB disk cartridges that would store up to 1,300 digital copies of Blu-ray discs (or 7,200 digital copies of DVDs) for $30,165 (/1300=$23 x BR movie), </p> <p>The 5U server (KSERVER-2000 or KSERVER-2500) is mentioned in one of the cartridges below (<a href="https://store.kaleidescape.com/hardware/details/disk-1000" target="_blank">1TB unit</a>) but there is no information in Kaleidescape’s website and local dealers do not know about this server, but on a phone conversation with Kaleidescape’s CS I was told that is an old server.  <p>&nbsp; <p>· <a href="https://store.kaleidescape.com/hardware" target="_blank">Disc Vaults</a>  <p>A disc vault <a href="http://kaleidescape.com/products/premiere/disc-vaults/m700/" target="_blank">M700</a> for $6,995 (which includes a player) or  <p>A disc vault <a href="https://store.kaleidescape.com/hardware/details/dv700-disc-vault" target="_blank">DV700</a> for $3,995 without a player,  <p>The vault must be integrated with the servers and players above to hold up to 320 physical discs, so the players would not require the Blu-ray disc to be present in them for the movie to be played. I will use the lower priced DV700 for the calculations below because I am already including the necessary individual players for the rooms in the example below.  <p>If the whole house Premiere line system would have 4 rooms with one M300 player in each room (total of $2,995 x 4 = $11,980), that cost should also be added to the per-movie-cost-of-ownership depending how many BR movies the system has been designed for, for example, let us assume we have a system for exactly 320 movies (BR discs and digital copies) to play in 4 rooms:  <ul> <li>One DV700 vault to store 320 BR discs ($3,995/ 320 = <b>$12</b> per movie)  <li>One 16TB 1U server to store the 320 digital copies (max 325) of the 320 BR discs above ($11,495/ 320 = <b>$36</b> per movie when using it for the 320 max of the vault)  <li>Four M300 players to play the digital copy movies stored in servers/vault in 4 rooms ($2,995 x 4 / 320 movies in the vault/system = <b>$37</b> per movie)  <li>_________________  <li>The total cost of the Premiere system for 320 BR movies and 4 rooms would be $3,995 + $11,495 + $11,980 = $27,470</li></ul> <blockquote> <p>The per-movie-cost-of-ownership of Kaleidescape products would be: </p> <p><b>$85</b> ($27,470/320), equal to <b>($12 + $36 + $37 = $85)</b></p></blockquote> <p>That is in addition to the $20 of owning the original Blu-ray or downloading the Kaleidescape Store digital copy version, both may include an Ultraviolet digital copy/additional features, and the Blu-ray purchase may also include an extra DVD in the jewel case.  <p>When adding that it brings the total per movie to <b>$105</b> per BR movie to a collector of discs, or a downloader, and 3D movies cannot be played in the system.  <p>_________________________________________________________________&nbsp;&nbsp; <p><img title="4TB Premiere Cartrigde" border="0" alt="4TB Premiere Cartrigde" align="left" src="http://www.hdtvmagazine.us/articles/images/KaleidescapeCinemaOneReview_D8BF/image_thumb_7.png" width="240" height="107">&nbsp;</p> <p>According to Kaleidescape’s web site additional terabytes of storage can be added to the 1U or 3U server (KSERVER-1500 or KSERVER-5000) system in separate cartridges, for example:  <p>&nbsp;<a href="https://store.kaleidescape.com/hardware/details/disk-2000L" target="_blank">2TB unit</a> to store the digital copy of up 55 Blu-rays cost $695 ($13 per Blu-ray), or </p> <p><a href="https://store.kaleidescape.com/hardware/details/disk-4000L" target="_blank">4TB unit</a> to store the digital copy of up 110 Blu-rays cost $995 ($9 per Blu-ray), or  <p><a href="https://store.kaleidescape.com/hardware/details/disk-1000" target="_blank">1TB unit</a> to store the digital copy of up 25 Blu-rays cost $995 ($40 per Blu-ray) to fit an available slot of a 5U Server (KSERVER-2000 or KSERVER-2500).  <p>However, the price per digital copy BR above is not as relevant for the per-movie-cost-of-ownership evaluation because it has to be first incorporated into the cost of the server where they will be installed.  <p><u></u>&nbsp; <p><u>The investment for the two systems for Blu-ray disc collectors is:</u>  <p>A) <strong>$11,985</strong> ($3,995 + 3,995 + $3,995) for two non-expandable Cinema Ones for two rooms and a DV700 vault, to store and play up to 200 digital copies of the 320 BR discs the vault can hold, equivalent to $60 per movie of Kaleidescape hardware components, plus the $20 Blu-ray disc, <b>total of $80 per movie</b>,  <p>B) <strong>$27,470</strong> ($3,995 + $11,495 + $11,980) for the Premiere line expandable system to store and play up to 320 digital copies of the 320 BR discs the vault can hold, and four players for four rooms, equivalent to $85 per movie of Kaleidescape hardware components, plus the $20 Blu-ray disc, <b>total of $105 per movie. </b></p> <p><strong></strong>&nbsp;</p> <p><strong>Pricing as provided by Kaleidescape (US$)</strong></p> <p> <table border="3" cellspacing="0" cellpadding="0" width="591"> <tbody> <tr> <td valign="top" width="99"> <p>KSERVER-5000-1300</p></td> <td valign="top" width="421"> <p>3U Server (Capacity 1300 Blu-ray Discs or 7200 DVDs; includes fourteen 4 TB Disk Cartridges, all slots full)</p></td> <td valign="top" width="65"> <p>30,165.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KSERVER-5000-0650</p></td> <td valign="top" width="418"> <p>3U Server (Capacity 650 Blu-ray Discs or 3600 DVDs; includes eight 4 TB Disk Cartridges, six slots for expansion)</p></td> <td valign="top" width="67"> <p>24,195.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KSERVER-1500-0325</p></td> <td valign="top" width="417"> <p>1U Server (Capacity 325 Blu-ray Discs or 1800 DVDs; includes four 4 TB Disk Cartridges, all slots full)</p></td> <td valign="top" width="69"> <p>11,495.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KSERVER-1500-0150</p></td> <td valign="top" width="415"> <p>1U Server (Capacity 150 Blu-ray Discs or 900 DVDs; includes four 2 TB Disk Cartridges, all slots full)</p></td> <td valign="top" width="71"> <p>9,495.00</p></td></tr> <tr> <td valign="top" width="100"> <p>K0503-0100-PL-0010</p></td> <td valign="top" width="413"> <p>Cinema One Limited Edition Bundle (Incl. K0503-0100 and 50 preloaded titles)</p></td> <td valign="top" width="72"> <p>3,995.00</p></td></tr> <tr> <td valign="top" width="100"> <p>K0503-0100</p></td> <td valign="top" width="413"> <p>Cinema One (Capacity 600 DVDs or 100 Blu-ray Discs; incl. remote, Wi-Fi adapter, HDMI and network cables)</p></td> <td valign="top" width="73"> <p>3,995.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KVAULT-M700</p></td> <td valign="top" width="412"> <p>M700 Disc Vault (Disc vault with 320 slots and integrated M-Class player)</p></td> <td valign="top" width="74"> <p>6,995.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KVAULT-DV700</p></td> <td valign="top" width="411"> <p>DV700 Disc Vault (Disc vault with 320 slots)</p></td> <td valign="top" width="75"> <p>3,995.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KPLAYER-M500</p></td> <td valign="top" width="410"> <p>M500 Player</p></td> <td valign="top" width="76"> <p>4,495.00</p></td></tr> <tr> <td valign="top" width="100"> <p>KPLAYER-M300</p></td> <td valign="top" width="409"> <p>M300 Player</p></td> <td valign="top" width="77"> <p>2,995.00</p></td></tr></tbody></table></p> <p><b></b>&nbsp; <p><b>Final Thoughts and Questions</b>  <p>I understand that if the Cinema One unit becomes disabled and a new/repaired unit is reinstalled the downloaded content can be reloaded from the central system and the content sourced from imported discs requires a manual re-importing by the user.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> Cinema One comes with a three year warranty, which can be extended by another 2 years to a maximum of five years. While the unit is under warranty, the Cinema One will be fully replaced if needed. If the hard drive inside Cinema One fails, content from the Store can be downloaded again at no charge. Content imported from disc will need to be reimported. If there is a vault connected, the vault acts as an importing device making the loading of discs more convenient. And content that has been purchased from the Kaleidescape Store with UV rights will remain accessible via the cloud.</i></font></font>  <p>Considering that the Cinema One is linked to the central system/account I wonder what reassurance Kaleidescape offers to customers regarding purchased content and central system features that require a system account if the company ceases to exist? I would like to see company statements regarding the topic.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape provided no comment</u> in written</i><i> but on a phone call to CS I was told that all the content imported or downloaded into the Cinema One is not affected if the Kaleidescape Store, the company, or the customer account cease to exist.</i></font></font>  <p>While owning physical media may be unsophisticated it insulates a consumer from the risk of having an orphan system, and facilitates the lending of the BD or the DVD to a family member or a friend, and for the kids to play the DVD in their room while you play the Blu-ray in your home-theater with no scratches, and for playing your disc (or the free Ultraviolet included with the disc) on other players/beach house/car, which the Cinema One may have no physical access to.  <p><font size="2"><font color="#0000ff"><i><u>Kaleidescape commented:</u></i><i> We certainly do not consider owning physical media as unsophisticated. 90% of content added to Kaleidescape systems in the last 2.5 months was DVD co<a name="_GoBack"></a>ntent. There are a lot of benefits to owning physical media, and we have built our products around customers who have both physical media and those who would like to move towards acquiring titles in digital formats.&nbsp; While digital sales have nearly doubled in the last year, physical disc ownership is still going strong with Kaleidescape customers who own an average of 500 movies —that’s nearly 5 times more than the average US household. </i></font></font><i><font color="#0000ff" size="2">Kaleidescape is delivering both quality and convenience for watching physical DVD and Blu-ray discs and is laying the groundwork for a digital-only future with the Kaleidescape Store.</font></i>  <p><b></b>&nbsp; <p><b>Specifications as per manufacturer:</b><b></b>  <ul> <li><b>Size and Weight: </b> <ul> <li>17.0 in. (W) × 2.8 in. (H) × 10.0 in. (D) <br>(43.2 cm × 7.1 cm × 25.4 cm)  <li>10.2 lb (4.62 kg)<b> </b></li></ul> <li><b>Power:</b> </li> <ul> <li>High Power Standby consumption: 20.0 Watts <li>Low Power Standby consumption: 0.35 Watts <li>Max consumption: 60 Watts *Typical: 35W <li>External power adapter, 100-240VAC to 12VDC @ 5A, 60 Watts with detachable line cord.</li></ul> <li><b>Environmental:</b>  <ul> <li>Operating temp: 5 to 35°C</li></ul> <ul> <li>Storage temp: -20 to 60°C</li></ul> <ul> <li>Relative humidity: 20% to 80% (operation) 5% to 90% (storage)</li></ul> <ul> <li>Maximum operating altitude: 10,000 ft</li></ul> <ul> <li>Heat output: 135 BTU/hour (40 Watts)</li></ul> <ul> <li>Airflow: 10 CFM</li></ul> <li><b>Rack Mount:</b>  <ul> <li>Middle Atlantic rack shelf available for mounting into a 2U 19" rack space.</li></ul> <li><b>Storage:</b>  <ul> <li>Integrated storage for up to 100 Blu-ray or 600 DVD-quality movies</li></ul> <li><b>Ventilation:</b>  <ul> <li>Minimum ventilation space (front): 1 inch (2.5cm),  <li>Minimum ventilation space (rear): 2 inch (5cm)</li></ul> <li><b>Media:</b>  <ul> <li>Downloads from the Kaleidescape Store, precisely matching the quality of Blu-ray disc and DVD</li></ul> <ul> <li>Blu-ray Disc, BD-R, BD-RE  <li>DVD, DVD-R, DVD-RW, DVD+R, DVD+RW  <li>CD Audio, CD-R, CD-RW</li></ul> <li><b>Blu-ray Disc:</b>  <ul> <li>BD-Live; Profile 2.0</li></ul> <li><b>Regional Playback Control:</b>  <ul> <li>The DVD region code of the Cinema One can be changed up to four times.</li></ul> <ul> <li>The Blu-ray Disc region code of the Cinema One is set at time of purchase and cannot be changed by the customer.  <li>Any Cinema One can import any Blu-ray Disc.  <li>An imported Blu-ray movie will play if a component of the system matches the region code specified by the disc.</li></ul> <li><b>Network:</b>  <ul> <li>100Base-TX/1000Base-T Ethernet (RJ45 connector)</li></ul> <ul> <li>802.11n Wi-Fi USB adapter included</li></ul> <li><b>Audio Outputs:</b>  <ul> <li>HDMI, Digital coaxial (RCA connector), Analog stereo (RCA connectors)</li></ul> <li><b>Audio Formats:</b>  <ul> <li>Bitstream pass-through of Dolby TrueHD and DTS-HD Master Audio</li></ul> <ul> <li>Dolby Digital, DTS Digital Surround, MPEG Audio</li></ul> <li><b>Control:</b>  <ul> <li>Ethernet control from Crestron, AMX, Control4, Savant, the Kaleidescape App for iPad, and other apps and control systems</li></ul> <ul> <li>Kaleidescape Remote included</li></ul> <ul> <li>Front-panel IR receiver window</li></ul> <ul> <li>IR input (1/8 in. mini-plug)</li></ul> <li><b>Warranty</b>:  <ul> <li>3 years (extendable)</li></ul> <li><b>Certifications:</b>  <ul> <li>CSA (IEC 60950-1:2005, CSA 60950-1-07)</li></ul> <ul> <li>FCC Class B, CE, GOST R, RoHS compliant</li></ul> <li><b>Video Modes:</b>  <ul> <li>1080p60/50/24, 1080i60/50, 720p60/50, 576p, 576i, 480p, 480i</li></ul> <ul> <li>Video processing and user interface selectable to 2.35 (CinemaScape) or 1.78:1</li></ul> <li><b>Video Output:</b>  <ul> <li>HDMI</li></ul></li></ul>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>January 30, 2014  2:47 PM</b>
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
			<?=getComments(5191)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 5191)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2014/01/kaleidescape-cinema-one-review.php" type="text/javascript" charset="utf-8"></script>
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