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
		AND e.entry_id = 3962";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3962 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3962 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3962";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/09/toshibas-full-line-of-bluraytm-players-bring-a-new-dimension-to-home-theater-entertainment.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3962";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba\'s Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba\'s Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba\'s Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment" />
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
	<title>HDTV Magazine - Toshiba's Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment</title>
	<meta name="keywords" content="blu ray, toshiba america, ray disc, audio essential, america information, toshiba, bdx, blu, ray, products, systems, may, content, new, audio, registered, inc, hdmi, available, trademarks, playback, disc, information, america, dts" />
	<meta name="description" content="Toshiba America Information Systems, Inc. today expanded its collection of Blu-ray Disc players with the new BDX3000. Joining the currently available BDX2700 and BDX2500 models, the BDX3000 is the company's first 3D-capable Blu-ray Disc player. Combining Full HD Blu-ray 3D(TM) playback with a suite of streaming content, ** comprehensive audio support, and complementary design, the BDX3000 is a cornerstone for a home entertainment system.

The BDX3000, combined with Toshiba's all-new WX800 Cinema Series 3D LED HDTV and a pair of Toshiba 3D glasses opens the door for..." />
	<meta name="title" content="Toshiba's Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba's Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/09/toshibas-full-line-of-bluraytm-players-bring-a-new-dimension-to-home-theater-entertainment.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Toshiba America Information Systems, Inc. today expanded its collection of Blu-ray Disc players with the new BDX3000. Joining the currently available BDX2700 and BDX2500 models, the BDX3000 is the company's first 3D-capable Blu-ray Disc player. Combining Full HD Blu-ray 3D(TM) playback with a suite of streaming content, ** comprehensive audio support, and complementary design, the BDX3000 is a cornerstone for a home entertainment system.

The BDX3000, combined with Toshiba's all-new WX800 Cinema Series 3D LED HDTV and a pair of Toshiba 3D glasses opens the door for..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3962', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/09/toshibas-full-line-of-bluraytm-players-bring-a-new-dimension-to-home-theater-entertainment.php">Toshiba's Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 16, 2010</b>
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
				<p class="prtitle">Toshiba's Full Line of Blu-ray(TM) Players Bring a New Dimension to Home Theater Entertainment</p>

<center><i>New BDX3000 Combines the Excitement of Blu-ray 3D(TM) with an Array of Streaming Content</center></i><br />
<br />

<p><strong>IRVINE, Calif., Sept. 15 /PRNewswire/ -- </strong>Toshiba America Information Systems, Inc. today expanded its collection of Blu-ray Disc players with the new BDX3000. Joining the currently available BDX2700 and BDX2500 models, the BDX3000 is the company's first 3D-capable Blu-ray Disc player. Combining Full HD Blu-ray 3D(TM) playback with a suite of streaming content, ** comprehensive audio support, and complementary design, the BDX3000 is a cornerstone for a home entertainment system.</p>

<p>The BDX3000, combined with Toshiba's all-new WX800 Cinema Series 3D LED HDTV and a pair of Toshiba 3D glasses opens the door for a new way to enjoy select movies, shows, video games, and more*. With LED edge-lighting, Toshiba picture enhancements such as DynaLight(TM) control and ClearFrame(TM) 240Hz technology coupled with a thin design, the WX800 showcases the latest in three-dimensional digital entertainment at the highest quality.</p>

<p>"3D is changing the movie industry by creating new creative opportunities and consumer experiences. The new Toshiba BDX3000 allows everyone to bring 3D home, and create that immersive experience in the comfort of the living room," said Scott Ramirez, Vice President of Product Marketing and Development, Television and Digital A/V, Toshiba America Information Systems. "The BDX3000 is the perfect complement to the new WX800 Cinema Series 3D HDTV, and just the start of Toshiba's foray into the world of 3D."</p>

<p>The BDX3000's ability to play Blu-ray 3D(TM) Discs augments its broad range of features. Like Toshiba's other Blu-ray players, the BDX3000 includes Wi-Fi&reg; capability for wireless access to streaming movies, television shows, music, and more. At the push of a button, content from BLOCKBUSTER On Demand&reg;, Netflix&reg; , VUDU(TM), and Pandora&reg; can be accessed instantly.** All three Toshiba models also include a standard Ethernet connection.***</p>

<p>All of Toshiba's Blu-ray players support Full HD 1080p resolution at a smooth 24fps, upconvert standard DVDs to near HD quality, and can playback video, photos, and music from a USB or SD/SDHC card on a connected HDTV. With internet connectivity on Toshiba's models, bonus features (BD-Live(TM) and Bonus View(TM)) can be accessed, such as social networking and chat features, interactive websites and games, unique trailers, or commentary from directors, the cast, and more, as available on select Blu-ray discs.</p>

<p>Built-in decoding of the latest HD audio formats, including Dolby&reg; TrueHD and DTS-HD Master Audio / Essential(TM), ensure that movies, music and more sound as great as they look when played on the BDX3000, BDX2700, or BDX2500. Similarly, the players' 7.1 Channel audio outputs and HDMI&reg;-CEC compatible HDMI connections offer easy home theater integration and functionality.</p>

<p><br />
<strong>  Product Feature Highlights:(1)</strong></p>

<p>  --  Blu-ray 3D(TM) Disc playback with the BDX3000<br />
  --  Full HD 1080p / 24fps Blu-ray Disc playback, with standard DVD<br />
      upconversion<br />
  --  Access to online content from Netflix&reg;, BLOCKBUSTER&reg; On Demand,<br />
      VUDU(TM), and Pandora&reg; internet Radio, using either wired Ethernet or<br />
      Wi-Fi&reg; connection<br />
  --  HD audio support, including Dolby&reg; True HD and DTS-HD Master Audio |<br />
      Essential(TM)<br />
  --  BD-Live(TM) (BD Profile 2.0) and Bonus View(TM) compatible<br />
  --  AVCHD video, HD JPEG/JPEG photo, and MP3/WMA audio file playback<br />
  --  HDMI connection with HDMI&reg;-CEC control<br />
  --  7.1-Channel analog audio outputs<br />
  --  USB port and SD/SDHC card slot<br />
  --  Energy Star&reg; 2.0 with the BDX3000</p>

<p><br />
<strong>  Pricing and Availability:</strong></p>

<p>  BDX3000 (available September, SRP $249.99)<br />
  BDX2700 (available now, SRP $199.99)<br />
  BDX2500 (available now, SRP $149.99)</p>

<p><br />
<strong>  About Toshiba America Information Systems, Inc. (TAIS)</strong></p>

<p>Headquartered in Irvine, Calif., TAIS is comprised of four business units: Digital Products Division, Imaging Systems Division, Storage Device Division, and Telecommunication Systems Division. Together, these divisions provide mobile products and solutions, including industry-leading portable computers; televisions, TV/DVD Combination products, Blu-ray Disc and DVD products, and portable devices; imaging products for the security, medical and manufacturing markets; storage products for automotive, computer and consumer electronics applications; and IP business telephone systems with unified communications, collaboration and mobility applications. TAIS provides sales, marketing and services for its wide range of products in the United States and Latin America.</p>

<p>TAIS is an independent operating company owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation. Toshiba is a world leader and innovator in pioneering high technology, a diversified manufacturer and marketer of advanced electronic and electrical products spanning information &amp; communications systems; digital consumer products; electronic devices and components; power systems, including nuclear energy; industrial and social infrastructure systems; and home appliances. Toshiba was founded in 1875, and today operates a global network of more than 740 companies, with 204,000 employees worldwide and annual sales surpassing 6.3 trillion yen (US$68 billion). For more information on Toshiba's leading innovations, visit the company's Web site at <a target="_blank" href="http://www.toshiba.com/">www.toshiba.com</a>.</p>

<p>More information about Toshiba's consumer electronic products available at <a target="_blank" href="http://www.tacp.toshiba.com/">www.tacp.toshiba.com</a> or <a target="_blank" href="http://www.toshibatv.com/">www.toshibatv.com</a>.</p>

<p>  (1) Important Notes:<br />
  * 3D glasses and WX800 Cinema Series 3D LED HDTV (sold separately).</p>

<p>** Third party internet services are available only in the U.S., are not provided by Toshiba, may change or be discontinued at any time and may be subject to third party restrictions. Toshiba makes no representations or warranties about these services, which may require the creation of a user account through a computer with internet access and the payment of one-time and/or recurring charges.</p>

<p>  *** BDX2500 requires a wireless adapter (sold separately).</p>

<p>  --  BDX3000 is designed to playback Blu-ray discs that comply with the 3D<br />
      specifications of Blu-ray Disc Association. It is not compatible with<br />
      other 3D specifications.<br />
  --  3D Capable display, 3D eyewear and High Speed HDMI cable (all sold<br />
      separately) required for playback and viewing 3D content.<br />
  --  Up-conversion of DVD content may result in near HD picture quality.<br />
      Results may vary depending on content, display and settings.  1080p<br />
      capable display required for viewing in 1080p. 1080p/24 fps encoded<br />
      content and an HD display capable of accepting a 1080p/24Hz signal<br />
      required for viewing 1080p/24 fps output.<br />
  --  Feature performance may vary and may also require an always-on<br />
      broadband internet connection.  Firmware update and additional<br />
      bandwidth may be required.<br />
  --  BD-Live(TM) may require an SD or SDHC card or USB memory with<br />
      available storage capacity of at least 1GB (sold separately).<br />
  --  For Dolby&reg; TrueHD and DTS-HD Master Audio | Essential(TM) playback,<br />
      content encoded in Dolby&reg; TrueHD and DTS-HD Master Audio |<br />
      Essential(TM) format required.<br />
  --  Supported file types: JPEG, MP3, WMA, and AVCHD.  Some recordable<br />
      media, cards or files may not be supported.<br />
  --  Because the Blu-ray format and 3D specifications use new technologies,<br />
      certain disc, content, connection and other compatibility and/or<br />
      performance issues are possible. Product specifications, information<br />
      and availability are all subject to change without notice.</p>

<p></p>

<p>ClearFrame and DynaLight are trademarks of Toshiba America Consumer Products, L.L.C. BLOCKBUSTER name, design and related marks are trademarks of Blockbuster Inc. "Blu-ray", "Blu-ray 3D", BD-Live" and "BONUS VIEW" are trademarks of Blu-ray Disc Association. Dolby is a registered trademark of Dolby Laboratories. DTS and the Symbol are registered trademarks &amp; the DTS logos are trademarks of DTS, Inc. ENERGY STAR is a registered mark owned by the U.S. Government. HDMI, the HDMI Logo, and High-Definition Multimedia Interface are registered trademarks of HDMI Licensing LLC in the United States and other countries. Netflix is a registered trademark of Netflix, Inc. Pandora is a registered trademark of Pandora Media, Inc. VUDU is a registered trademark of VUDU, Inc. Wi-Fi is a registered mark of the Wi-Fi Alliance. All others are trademarks or registered trademarks of their respective owners.</p>

<p>Source: Toshiba America Information Systems, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 16, 2010  7:44 PM</b>
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
			<?=getComments(3962)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3962)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/09/toshibas-full-line-of-bluraytm-players-bring-a-new-dimension-to-home-theater-entertainment.php" type="text/javascript" charset="utf-8"></script>
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