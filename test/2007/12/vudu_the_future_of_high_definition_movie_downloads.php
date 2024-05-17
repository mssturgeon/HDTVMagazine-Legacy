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
		AND e.entry_id = 831";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 831 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 831 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 831";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2007/12/vudu-the-future-of-high-definition-movie-downloads.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 831";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VUDU: The Future of High Definition Movie Downloads" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VUDU: The Future of High Definition Movie Downloads" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VUDU: The Future of High Definition Movie Downloads" />
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
	<title>HDTV Magazine - VUDU: The Future of High Definition Movie Downloads</title>
	<meta name="keywords" content="high definition, vudu box, dolby digital, video download, scroll wheel, vudu, content, movies, available, video, movie, box, digital, dvd, download, service, definition, dolby, titles, quality, could, high, watch, hours, audio" />
	<meta name="description" content=" I am writing a series of small reviews for the 2007 Holiday Gadget Guide, hosted by our advertising agency, Federated Media. One of the &quot;gadgets&quot; I reviewed recently looked quite interesting and so I thought it should have a..." />
	<meta name="title" content="VUDU: The Future of High Definition Movie Downloads" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VUDU: The Future of High Definition Movie Downloads" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2007/12/vudu-the-future-of-high-definition-movie-downloads.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content=" I am writing a series of small reviews for the 2007 Holiday Gadget Guide, hosted by our advertising agency, Federated Media. One of the &quot;gadgets&quot; I reviewed recently looked quite interesting and so I thought it should have a..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=831', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2007/12/vudu-the-future-of-high-definition-movie-downloads.php">VUDU: The Future of High Definition Movie Downloads</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>December 18, 2007</b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p></p> <p></p> <p class="editorial">I am writing a series of small reviews for the <a href="http://holidaygadgetguide.federatedmedia.net/" target="_blank">2007 Holiday Gadget Guide</a>, hosted by our advertising agency, Federated Media. One of the "gadgets" I reviewed recently looked quite interesting and so I thought it should have a more in-depth review. I have taken that <a href="http://holidaygadgetguide.federatedmedia.net/241" target="_blank">original review</a>, and gone into more detail in the review that follows.</p> <table class="greygrid"> <tbody> <tr> <td>&nbsp;</td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>Pricing at publication</b></td> <td class="greygrid">$399</td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000VEMJFY?ie=UTF8&amp;tag=hdtvmagazine-20&amp;link_code=as3&amp;camp=211189&amp;creative=373489&amp;creativeASIN=B000VEMJFY" target="_blank">399.99</a></td></tr></tbody></table><br>Serial #: 050732000534<br>Software version: 1.1.1 (rev 18056)<br>Database version: 14:1422<br>Warranty: Limited 1 year parts and labor<br><br><b>Summary: Excellent video quality in an easy to use package</b><br><br> <p>Unless you've been under a rock for the past year or so, you are undoubtedly aware of the HD DVD vs. Blu-ray "Format War" going on. The vast majority of you are choosing not to partake in this controversy because you are either waiting for one side to win, or are waiting for video download to be a viable alternative. The video download market has grown by leaps and bounds since last fall when both Amazon and Microsoft announced video download services.</p> <h2>The Video Download Market</h2> <p>Just in the past two years, we have seen a number of video download services hit the market. Each has their own strengths and weaknesses and carries with it varied cost, selection and video quality. I have included a brief list of the major players in the video download market below along with a summary of their current offerings:</p> <ul> <li><strong>Microsoft Xbox 360</strong> - Through the Microsoft Xbox 360 console, you can connect to the Xbox Live Marketplace and purchase TV shows or rent movies and watch them on whatever TV you have your console connected to. This service launched in November 2006 and since then they have accumulated a library of almost 300 TV series across 26 different networks and over 300 movies. All of their movie content is available in 480p, and a quick spot check shows that approximately half of the movie content is available in 720p HD as well. Movie prices range from $3 - $6 for rental only. They do not currently have movies available for purchase.  <li><strong>Apple TV</strong> - Apple launched it's Apple TV product and service in January of this year. The hardware itself can support HD output at up to 1280 x 720 at 24 fps, but the only content available currently is in standard definition. The hardware does hook directly to the TV, which is nice, but you still need a home computer somewhere in the picture because it can only stream from, or sync with, a PC with iTunes installed. Also, all your purchasing is done through iTunes on that computer, not the TV ... so it's not as simple as just plopping down on the couch and clicking on something to watch. Their library is a little larger than Xbox Live Marketplace with about 500 movies available and about 600 TV shows ... but none of it is in HD. And on the SD content they do have the picture quality was not good, not even DVD quality. I tested both TV series and movies and each were barely watchable. And for the $299 - $399 price tag, you'd definitely be better off with just about anything else. The content itself ranges from $1.99 for TV episodes to between $9.99 and $14.99 for newly released movies. No rentals are available yet, but Apple and Fox have <a href="http://www.ft.com/cms/s/0/91d21b3c-b3ee-11dc-a6df-0000779fd2ac.html?nclick_check=1" target="_blank">recently announced a deal</a> to rent the latest Fox DVD releases from Apple's iTunes. One would assume these would be compatible with the Apple TV device, but this has not been confirmed.  <li><strong>Amazon Unbox</strong> - Amazon unbox launched in September 2006. It has more content that Xbox Live Marketplace, but unless you have a PC or TiVo Series 2 or 3 already hooked up to your home theater, you will not be able to easily watch it on your main TV because it requires a software downloadable video player to view the content. A quick search on Amazon's site shows that they have just approximately 500 TV series available and just under 5,000 movie titles available. Unlike Xbox Live Marketplace, you <strong>can</strong> purchase movies, as opposed to just renting them. Movie rentals are $1 - $4 and purchases are $10 - $15 for most titles. None of it is available in high definition, however.  <li><strong>Netflix Watch Instantly</strong> - Launched in January 2007, Netflix's Watch Instantly service provides access to over 6,000 movies and TV episodes. This service is streaming only, so there is no persistent storage of your purchases locally. However, there is no cost for renting because it is included as part of your Netflix membership, which ranges from $5 - $24. The amount of "Watch Instantly" time you get per day is determined by your membership level. At the $5 level you only get 5 hours of viewing, while at the $24 level you get 24 hours of viewing. Netflix Watch Instantly does not have any sort of hardware compatibility yet, so you will have to have a PC connected to your home entertainment system if you want to enjoy these movies on your main TV. As with Amazon unbox, there is not any high definition content as yet.  <li><strong>Moviebeam</strong> - This service recently announced that is was closing down. I am mentioning it here so that you know what the differences are between this failed venture and what VUDU is trying to do. The Moviebeam service was similar to VUDU in that it was also a hardware solution to video downloading. The set top box was available for $200-$250 and movies could be rented for $2 - $4. Where it differed from VUDU is that it used traditional broadcast to distribute its movies. Because of this, it could not guarantee delivery into every home. It was also limited by the fact that it could only offer about 200 movies at a time and there was no movie ownership, only rental.  <li><strong>Pay-per-view (PPV) and Video-on-demand (VOD)</strong> - These services have been around for quite a while from various cable and satellite providers. They typically have several selections available in high definition and the quality is good. However, these services are limited by bandwidth. You will typically only have the option to rent 100-200 titles at most at any given time. If you are only interested in new releases and don't have any need to purchase content, then these may be viable options for you. PPV and VOD rental pricing is typically $3 - $4.  <li><strong>Sony Playstation Store</strong> - Sony has been hinting that video downloads are coming for the Playstation 3 for over a year. In February, Phil Harrison of Sony indicated that it would be here "very shortly", but we have yet to see any details on when that will be. Perhaps there will be an announcement at CES next week?</li></ul> <p>So in just over a year, we've seen a tremendous amount of online video flood the marketplace with varying degrees of quality and pricing, but we have yet to see one that has the ultimate combination of content choice, quality (high definition) video and usability.</p> <h2>Enter VUDU</h2> <p><img style="margin: 0px 5px 0px 0px" height="119" alt="image" src="http://holidaygadgetguide.federatedmedia.net/wp-content/themes/hgg/images/content/image-thumb.png" width="200" align="left" border="0">In September of this year, there was a new entry into the movie download market: <a href="http://www.vudu.com/">VUDU</a>. Unlike the Amazon and Netflix services mentioned above, VUDU is implemented as a hardware device that you connect directly to your television, rather than a software program you install on a PC or laptop. You simply connect the VUDU player set-top box to your television just like you would a cable, satellite or digital video recorder. This way you can easily enjoy movies on your larger living room television without the need for a PC or laptop hooked up to it. Another advantage that VUDU may have over its competitors is that you can <strong>buy</strong> high definition movies, rather than just rent them like you do on the Xbox Live Marketplace.</p> <p>With VUDU, there is no monthly subscription fee and no contract to sign. Rentals are reasonably priced from $0.99 - $3.99 and you can purchase movies to own for $4.99 - $24.99. Whether you rent or buy, you may be able to watch the movie immediately if your internet connection is fast enough. If your connection is between 2 and 3 Mbit/s (advertised speed), you can quite likely watch immediately. For slower connections, there will be a delay (30 minutes to an hour) before you can start watching. It will still work with slower connections if you don't mind waiting for your movie to download, but at some point &lt; 1Mbit/s, it will refuse to download. If you would like to test your speed to see how your service stacks up, VUDU has a <a href="http://speedtest.vudu.com/" target="_blank">speed test page</a> set up.</p> <h2>The Hardware</h2> <p>Your movie purchases are stored on the Vudu player, which has capacity for 100 hours of owned movies and unlimited rentals. The box has a full compliment of audio and video outputs as well as two USB ports. These USB ports are currently inactive, but are intended to provide the ability to expand the storage capacity in the near future. Rounding out the connections on the back<a href="http://www.hdtvmagazine.com/images/test/VuduPlayer_129CB/image.png"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="126" alt="image" src="http://www.hdtvmagazine.com/images/test/VuduPlayer_129CB/image_thumb.png" width="200" align="right" border="0"></a> of the box are an ethernet port, power connection and RF antenna connection (antenna included).</p> <table cellspacing="0" cellpadding="2" width="602" border="0"> <tbody> <tr> <td width="136">Dimensions</td> <td valign="top" width="464">2.4" H x 8.9" W x 7.3" D, Weight: 4.2 lbs </td></tr> <tr> <td valign="top" width="139">Storage</td> <td valign="top" width="464">Unlimited rentals, 100 hours (SD) of owned movies (50 hours HD)</td></tr> <tr> <td valign="top" width="141">Audio Outputs</td> <td valign="top" width="464">HDMI v1.1, Digital Optical, Digital Co-ax, RCA</td></tr> <tr> <td valign="top" width="143">Audio Format</td> <td valign="top" width="464">Source: Dolby<sup>&reg;</sup> Digital Plus, Output: Dolby Digital 5.1</td></tr> <tr> <td valign="top" width="144">Video Outputs</td> <td valign="top" width="464">HDMI v1.1, Component, S-Video, Composite</td></tr> <tr> <td valign="top" width="145">Video Resolution</td> <td valign="top" width="464">1080p/24, 1080i, 720p, 480p, 480i</td></tr> <tr> <td valign="top" width="146">Video Encoding</td> <td valign="top" width="464">Source: MPEG-4, SD content encoded at 480p, HD at 1080p</td></tr> <tr> <td valign="top" width="146">Connectivity</td> <td valign="top" width="464">Ethernet, 2 USB ports</td></tr> <tr> <td valign="top" width="147">Remote control</td> <td valign="top" width="464">RF, 22ft. range, unobstructed</td></tr></tbody></table> <p>As with most internet connected consumer electronics these days, it can receive firmware updates via the internet. These updates are targeted to occur every 4 to 6 weeks to provide new features, correct issues and update the interface. When I fired this one up, its first task was to update itself, which only took about 5 minutes total from start to reboot to ready-to-use.</p> <p>A few things you should note from the specs above:</p> <ul> <li>All source content is encoded using Dolby<sup>&reg;</sup> Digital Plus, but the audio output on the box is limited to either 2-ch. Stereo or Dolby<sup>&reg;</sup> Digital 5.1.  <li>All source material is encoded at either 1080p or 480p. The VUDU box will then upscale or downscale the video output to match what you have selected in the system settings (if different from the source, of course).  <li>The VUDU box <strong>will not</strong> upscale over any output other than HDMI. So if you are connecting via component connection, you will not get the benefit of the upscaled standard definition content.</li></ul> <p><a href="http://www.hdtvmagazine.com/images/test/VuduPlayer_129CB/image_4.png"><img height="240" alt="image" src="http://www.hdtvmagazine.com/images/test/VuduPlayer_129CB/image_thumb_4.png" width="91" align="right" border="0"></a>The VUDU box comes with a radio frequency (RF) remote. It may seem a bit weird in appearance, but you will be pleasantly surprised how well it feels in-hand. After just a short time, I never even had to look to see if I was pressing the right button. The remote control has a clickable scroll wheel for navigation as well as 5 buttons: Power, Back, Play/Pause, VUDU and More. The first three should be fairly intuitive. The fourth button, VUDU, always takes you back to the main page no matter how deep you happen to be navigating menu's and submenu's. The last button, the "More" button, is not yet being used but provides an opportunity for future enhancements. Last but not least, the clickable scroll wheel makes it very easy to not only navigate through the interface, but also makes it quick and easy to fast-forward or rewind a movie. And a click of the scroll wheel during playback will bring up the movie progress bar showing a time count of how far you are through the movie as well as a blue-colored progress "fill" indicating how much of the movie has been downloaded (if viewing instantly).</p> <p>The cost of the box is $399, although you can get one for less. VUDU has <a href="http://www.vudu.com/sharp.html" target="_blank">partnered with Sharp</a> to offer a free VUDU box to anyone who purchases a <a href="http://www.sharp-cart.com/ecom/partner_home.htm?view=vudu" target="_blank">42" or larger 1080p AQUOS<sup>&reg;</sup> LCD TV</a>. You must act quickly though, as the box must be activated by 1/5/08 to qualify.</p><br clear="all"> <h2>The Quality</h2> <p>The VUDU service provides their movies in both standard (480p) and high (1080p) definition. As mentioned in the specs above, the VUDU player itself will output video in a number of formats to match your television: 1080p/24, 1080i, 720p, 480p and 480i. Yes, that's right, 1080p/24! With respect to standard definition content, I have not done any empirical analysis of the scaling components within the player, but the video quality of most of the content I viewed appeared to be better than that of DVD. They have only just begun to make HD available. In fact, as of this writing, the only titles available in HD are the three "Bourne" flicks: Bourne Identity, Bourne Supremacy and Bourne Ultimatum. For those three titles, the HD content was near perfect. I compared Bourne Identity with the HD DVD version and could not tell a difference. Note that this was not a side-by-side test, but rather back to back viewing.</p> <p>All video content is encoded as MPEG-4 using variable bitrate encoding. With SD content, the average bitrate hovers around 2Mbit/s. This equates to DVD quality, but with the upscaling done by the player, it appears slightly better. The quality of the source material varies depending on the studio and how well they manage their digital library. Some content is the same as that on the DVD, while other content is from digital masters. As digital download advances, let's hope the studios digital libraries do as well so that we continue to see an improvement in digital download quality. Since all HD content on the VUDU system is encoded from 1080p source, it stands above all of the download services mentioned above. Xbox Live Marketplace is the only other download service that offers high definition content and to date they don't encode any higher than 720p.</p> <p>All audio content is encoded as Dolby<sup>&reg;</sup> Digital Plus, but the VUDU box can only output Dolby<sup>&reg;</sup> Digital 5.1 or 2-ch. stereo. This provides an equivalent audio experience to DVD, but falls short of the Dolby TrueHD and DTS-HD/Master Audio offered on HD DVD and Blu-ray media.</p> <h2>The Selection</h2> <p>Their title selection is quite impressive. Perhaps the largest digital download library available, depending on how you count Amazon unbox titles. As of this review, VUDU has partnerships with every major studio and 22 independent studios and distributors. In total, there are over 5,000 movies available, almost double most "brick-and-mortar" video stores. This doesn't yet come close to the vast libraries of Netflix and Blockbuster, which have 90,000 and 75,000 titles, respectively, but it is a good start. Additional titles are added at a rate of about 5-20 per week. Many of these new titles being added are available for purchase on the same day as the DVD. They become available for rental later, anywhere from 15 to 45 days after release, depending on the studio.</p> <p>It should also be noted that not all content is available to purchase, and not all content is available for rental. It is up to the studios to determine whether their content can be rented, purchased or both. Roughly 80% of the VUDU library is available for rent, and roughly 80% can be owned. Nearly</p> <p>As mentioned above, the only titles available in HD are the three "Bourne" flicks: Bourne Identity, Bourne Supremacy and Bourne Ultimatum. But more HD content is coming in early 2008 from Universal, Paramount and Lion's Gate.</p> <p>With VUDU, there is no monthly fee, and no service contract to sign. The movies cost about what you would expect. You can purchase (own) movies from $4.99 - $24.99. Rentals range from $0.99 - $3.99. When you rent a movie, it will be available and stored in "My Movies" for up to 30 days. Once you start watching it, you will have 24 hours in which to watch it as many times as you like. Depending on the studio, some titles are available for 48 hours once you start watching.</p> <p>VUDU also carries about 1000 TV titles, although these are still in "beta" and not available yet. I only found about 25 seasons of various series on my box. All of the TV content to this point is SD only. Their current selection of TV shows includes:</p> <ul> <li>24 (seasons 1-6)  <li>Arrested Development (seasons 1-3)  <li>Buffy the Vampire Slayer (seasons 1 &amp; 2)  <li>Family Guy (seasons 1-5)  <li>Firefly (season 1)  <li>Lost in Space (season 1)  <li>My Name is Earl (season 1)  <li>NYPD Blue (seasons 1 &amp; 2)  <li>Prison Break (seasons 1 &amp; 2)  <li>Remington Steele (season 1)  <li>The Riches (season 1)  <li>Shark (season 1)  <li>The Time Tunnel (season 1)</li></ul> <h2>The Interface</h2> <p><a href="http://www.hdtvmagazine.com/images/test/VuduPlayer_129CB/image_3.png"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; margin: 0px 0px 5px 5px; border-right-width: 0px" height="142" alt="image" src="http://www.hdtvmagazine.com/images/test/VuduPlayer_129CB/image_thumb_3.png" width="240" align="right" border="0"></a>The user interface is quite intuitive. In my opinion it is much easier to use than similar devices like the Apple TV and the Xbox 360. The remote control has a clickable scroll wheel, quite like most computer mice, which makes navigation a breeze. There are 5 buttons on the main screen: Find Movies, New on VUDU, My Movies, My Wish List and Info &amp; Settings. Each of those does about what you'd expect. Additionally, under Find Movies, you can search by genre, title, actor and director, or you can see the most watched movies as well as what's coming soon.</p> <p>It is also quite easy to find other content you might be interested in related to a particular movie. On the Movie Details screen, in addition the the standard rating, runtime, etc., you can also select "Similar Movies" to bring up a list of other movies that have similar genres. Or you can scroll through a list of actors and directors and select them to see other movies in which they appear that are available on the VUDU system.</p> <p>To round out the user interface:</p> <ul> <li>Controlled via simple 5-button remote with scroll wheel  <li>Easy fast-forward and rewind via the remote scroll wheel  <li>HD content is indicated by a little "HD" bug in the upper right hand corner of the title banners  <li>Full parental controls are available based on rating, along with passcode restriction  <li>Three options are available for display of 4:3 content: Stretched, Zoomed or Boxed  <li>You can to set your preferred HDMI resolution via the Info &amp; Settings screen  <li>Ability to set remote control sensitivity  <li>Displays "days left" and "hours left" on your rentals </li></ul> <h2>The Technology</h2> <p>There is quite a bit of proprietary new technology used in the making of the VUDU box. VUDU has applied for a total of 42 different patents for various parts of the system.</p> <p>One of the key elements that allows VUDU to handle such a large library of content and stream it instantly is Peer-to-Peer technology (P2P). P2P is a method of file sharing that allows files to be exchanged directly between end-user boxes instead of from a centralized server. So when you download a movie, you are actually connecting directly to other users VUDU boxes. Roughly 10% of the hard drive of each VUDU box is used to pre-position content that might be popular for upload to other VUDU users. This allows the VUDU system to move massive amounts of traffic and help ensure an instant-on experience for their customers. It also allows VUDU to "share" some of the distribution work to the players instead of footing the bill for a large server farm for movie distribution.</p> <h2>Room for Improvement</h2> <p>While this is the closest product we've seen to date that could possibly replace standard (or eventually high definition) DVD, there are still several areas where there could be some improvement, or where this might not quite stack up to physical media:</p> <ul> <li>You may think the $399 price of the unit itself is a hindrance. At $399 it is a little pricey, but from all appearances it seems to be a well-engineered, and well-featured, piece of equipment. Also, this price can be offset by the time and mileage to your local video store. Depending on your usage, it will pay for itself in time.  <li>As of this writing, there are only three HD movie titles available, and none of the TV series are available in HD as yet. Expect this to change in early 2008.  <li>Even though all content is encoded using Dolby<sup>&reg;</sup> Digital Plus, the VUDU box is currently limited to Dolby<sup>&reg;</sup> Digital 5.1. Dolby<sup>&reg;</sup> Digital Plus would be nice to have, but in order to effectively compete with HD DVD &amp; Blu-ray, it must someday support the same advanced audio formats like Dolby TrueHD and DTS-HD/Master Audio.  <li>The fact that most studios only allow you 24 hours to finish a rented movie once started is a real set-back. This needs to change if digitally delivered rentals are to survive.  <li>One shortcoming of the box today is the lack of ability to delete content from the box. This feature will be added as part of the next software release, currently scheduled for February deployment.  <li>The remote control's batteries load on one side toward the back, which makes it a little side-heavy, and hard to set down properly without it toppling over ... but that is a small issue, almost not worth mentioning.  <li>The remote is also RF-only, and as such I could not "train" my Harmony to work with the VUDU box. This is not as much of an issue as I thought simply because the remote is so nice to use ... in my opinion.  <li>Lastly, the VUDU system is currently only available in the U.S.</li></ul> <p>Also, here are some things to bear in mind when trying to determine if this is a good replacement for your physical DVD or high definition DVD library:</p> <ul> <li>Do you like the convenience of having your media stored electronically, or do you prefer the feel of physical media?  <li>Do you enjoy the "extras" that come with packaged media: interactive features, out-takes, deleted scenes, commentary, etc.?  <li>Do you desire other language options? Currently, the only language available is English. There are no audio tracks in different languages, nor are there any subtitles available except for English subtitles on specific foreign films.  <li>Do you enjoy the advanced audio available on HD DVD &amp; Blu-ray? Since VUDU is currently limited to Dolby Digital 5.1, this will be a big factor in your decision.  <li>Do you tend to loan your DVDs out to family and friends, give them as gifts or play in other locations?  <li>Are you a serious movie watcher/collector? If so, you will definitely want to take advantage of the expanded storage via USB when it becomes available since the box itself will only hold 50 hours of HD. But for those that have more than a couple hundred HD movies, even that may not be enough.</li></ul> <h2>The Future</h2> <p>What's in store for the VUDU box and service? With a new product like this, it's hard to guess about what they might be working on next. So I've included below just a few possibilities based on the design of the box and the service:</p> <ul> <li>The USB ports don't just have to be used for external storage. Why not allow customers to attach a DVD burner and burn purchased movies to media for use in other devices, in other locations or on other TV sets?  <li>For those that use universal remotes, it would be nice to have an IR option to the box, perhaps utilizing one of the USB ports.  <li>How about a Media Center Extender? If the VUDU box could connect with a networked PC or Media Server, you could then play video that resides on that PC ... your <strike>ripped</strike> backed-up DVD collection perhaps?  <li>Related to that, how about a VUDU Extender? A disc-less device that you could put on another TV and connect back to the main VUDU unit to stream content around the house.  <li>I'd like to see a subscription service. Something like Netflix where I can pay $30 or so per month and rent as many movies as I want.  <li>I would also like to see longer rental durations. Having 30 days to start watching a rental is great! But only allowing 24 hours to finish a rented movie once started could be an inconvenience. I realize this is a studio decision, but it would be much better to have at least 2 days to finish once you've started.  <li>For the newly released TV series', I'd like to ability to subscribe to a whole season and have it ready to watch in "My Movies" as each new episode is posted.  <li>And last, but not least: If I rent a movie for $3 then decide to buy, I'd like to be able to apply some or all of that rental to the purchase price. This decision also lies with the studios, so it's not likely to happen, but it doesn't hurt to ask.</li></ul> <h2>Conclusion</h2> <p>All in all, this is a nice service. It is the most complete video download service I've seen to date. But is it enough to replace your DVD or high definition DVD collection, or just supplement it? I think VUDU can certainly replace the current DVD library in many homes today. With respect to high definition DVD, it's not there yet, but depending on how quickly they can expand their HD offerings, it could be a viable replacement at some point next year. And even if you are a serious collector, or just prefer owning physical as opposed to digital media, this is still an excellent, although somewhat expensive, way to rent movies.</p> <p>&nbsp;</p> <p><font size="1">* Dolby Digital Plus is a registered trademark of Dolby Laboratories. <br>* HDMI is a trademark of HDMI Licensing LLC.</font></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>December 18, 2007 10:55 PM</b>
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
			<?=getComments(831)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 831)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2007/12/vudu-the-future-of-high-definition-movie-downloads.php" type="text/javascript" charset="utf-8"></script>
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