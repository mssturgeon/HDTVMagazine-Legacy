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
		AND e.entry_id = 1607";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1607 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1607 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1607";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1607";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Expands Flagship XVT Series of HDTVs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Expands Flagship XVT Series of HDTVs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Expands Flagship XVT Series of HDTVs" />
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
	<title>HDTV Magazine - VIZIO Expands Flagship XVT Series of HDTVs</title>
	<meta name="keywords" content="srs trusurround, mega dynamic, xvt series, refresh rate, hdmi inputs, xvt, new, srs, contrast, models, trusurround, truvolume, dynamic, led, mega, model, series, performance, technology, hdmi, rate, lcd, audio, sound, inputs" />
	<meta name="description" content="VIZIO, America's HDTV and Consumer Electronics Company, unveils several new models to the XVT Series expanding the performance line with five NEW models. The most dramatic technology introduction is the use of LED panels featuring 240Hz with Scanning Backlight and VIZIO's Smooth Motion II(TM) technology in their largest model the 55&quot; VF551XVT. LED backlight technology provides better picture quality with 1,000,000:1 Mega Dynamic Contrast with local dimming. LED also provides faster response times and higher color saturation so you achieve a better picture. It is also environmentally responsible because it is mercury free. The VIZIO VF551XVT also has 5 HDMI Inputs and a speaker bar designed into the TV so you achieve more robust full body sound from a flat panel TV with SRS TruSurround HD(TM), and TruVolume(TM).

They are also expanding their collection of 42&quot; and 47&quot; XVTs to include..." />
	<meta name="title" content="VIZIO Expands Flagship XVT Series of HDTVs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Expands Flagship XVT Series of HDTVs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="VIZIO, America's HDTV and Consumer Electronics Company, unveils several new models to the XVT Series expanding the performance line with five NEW models. The most dramatic technology introduction is the use of LED panels featuring 240Hz with Scanning Backlight and VIZIO's Smooth Motion II(TM) technology in their largest model the 55&quot; VF551XVT. LED backlight technology provides better picture quality with 1,000,000:1 Mega Dynamic Contrast with local dimming. LED also provides faster response times and higher color saturation so you achieve a better picture. It is also environmentally responsible because it is mercury free. The VIZIO VF551XVT also has 5 HDMI Inputs and a speaker bar designed into the TV so you achieve more robust full body sound from a flat panel TV with SRS TruSurround HD(TM), and TruVolume(TM).

They are also expanding their collection of 42&quot; and 47&quot; XVTs to include..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1607', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php">VIZIO Expands Flagship XVT Series of HDTVs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2009</b>
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
				<p class="prtitle">VIZIO Expands Flagship XVT Series of HDTVs</p>

<center><i>- New arsenal includes 240Hz models from 42" thru 55", a 55" LED HDTV and Slim 120Hz 32" and 37" LCD HDTVs

<p>- New 55" 240Hz is VIZIO's First LED LCD HDTV featuring 1,000,000:1 Mega Dynamic Contrast for exceptional contrast along with 240Hz Refresh Rate with Scanning Backlight and VIZIO Smooth Motion II(TM) technology</p>

<p>- New 42" and 47" models feature 240Hz Refresh Rate with Scanning Backlight and VIZIO Smooth Motion II(TM) technology to deliver the most incredibly smooth picture and rich detail</p>

<p>- All new models include NEW VIZIO Multi-Media feature with USB input compatible to play MPEG-2, H.264 & WMV9 Video, JPEG Photos and MP3 Music from Thumb Drive or FAT32 Hard Drive, they also come with 1080P content loaded onto a 1GB USB Drive so you can enjoy 1080P video right out of the box!</p>

<p>- New Ultra Slim (2.5" depth) 32" & 37" 120Hz models provide exceptional picture and sound performance with 50,000:1 Mega Dynamic Contrast Ratio, and SRS TruVolume and TruSurround HD</i></center><br /><br />
<br /></p>

<p><br />
<B>LAS VEGAS and IRVINE, Calif., Jan. 7 /PRNewswire/ -- (CES 2009)</B> -- VIZIO, America's HDTV and Consumer Electronics Company, unveils several new models to the XVT Series expanding the performance line with five NEW models. The most dramatic technology introduction is the use of LED panels featuring 240Hz with Scanning Backlight and VIZIO's Smooth Motion II(TM) technology in their largest model the 55" VF551XVT. LED backlight technology provides better picture quality with 1,000,000:1 Mega Dynamic Contrast with local dimming. LED also provides faster response times and higher color saturation so you achieve a better picture. It is also environmentally responsible because it is mercury free. The VIZIO VF551XVT also has 5 HDMI Inputs and a speaker bar designed into the TV so you achieve more robust full body sound from a flat panel TV with SRS TruSurround HD(TM), and TruVolume(TM).</p>

<p>They are also expanding their collection of 42" and 47" XVTs to include 240Hz with scanning backlight using VIZIO's Smooth Motion II(TM) technology and Mega Dynamic Contrast ratio of 50,000:1. The 42" SV421XVT and 47" SV471XVT are capable of outstanding realism and are close to eliminating motion blur-once and for all. All new XVT models integrate the latest SRS sound technologies like SRS TruSurround HD(TM), and SRS TruVolume(TM), for a complete all-in-one home theater solution.</p>

<p>"VIZIO is committed to providing the absolute latest advanced technologies to consumers at a value. Consumers expect great performance and style from VIZIO" says Laynie Newsome, VIZIO Co-Founder and VP Sales and Marketing Communications. "You will see this demonstrated in the many new products we are introducing at CES, our prestigious XVT Series exemplifies what we believe is the BEST performing leading edge HDTV technology for consumers."</p>

<p><br />
<B>The Best in Video Performance</B></p>

<p>Launching two compelling new technologies, the expanded XVT Series offers more advanced features than any previous VIZIO product line with a complete line of Full HD 1080p performance coupled with both 120Hz and 240Hz with Scanning Backlight.</p>

<p>Using advanced LED backlight control local dimming, the VF551XVT is capable of delivering the brightest highlights and the deepest blacks. Coupled with VIZIO's Mega Dynamic Contrast Ratio(TM) these sets can provide a contrast ratio that rivals the performance of the best plasma displays. The use of local dimming to increase overall contrast ratio and picture quality, especially in brightly lit rooms, allows these LED models to exhibit deep, three-dimensional-like images.</p>

<p>VIZIO's Smooth Motion II(TM) technology steps up the game with 240Hz Refresh Rate in the SV421XVT and SV471XVT for unbelievably flawless video -- frame after frame by eliminating motion blur from fast-action movie sequences.</p>

<p>Every LED and LCD model in the series is outfitted with the Advanced Glare Polarizer (AGP) and Enhanced In-Plane Switching (IPS). AGP provides up to 60% better contrast ratio in bright room environments. Earlier Anti-Glare screens tended to create an overall haze on the TV image; however, VIZIO's solution counteracts the effects of ambient light, while keeping images bright and crystal clear. By reducing the reflections that are produced by ambient light, the overall picture contrast can be preserved. IPS produces truer colors at wider viewing angles ensuring vivid and clear pictures even when sitting off-axis.</p>

<p>VIZIO's advanced HD/SD noise reduction removes noise and artifacts caused by signal compression from cable and satellite providers. Each model is outfitted with multiple HDMI inputs for maximum connectivity with DVD/Blu-ray players, digital cable and satellite set-top boxes, gaming consoles, and HD camcorders. All XVT series sets have a Game Port on the side of the set with inputs (including HDMI) for fast, easy connection of other devices.</p>

<p><br />
<B>Sound Advancements</B></p>

<p>VIZIO's XVT Series is not just about superior video features and technologies. Every model is equipped with audio enhancement technologies from SRS Labs to ensure the best possible audio performance from built-in speakers. The 55" VF551XVT and VF550XVT1A take audio performance to a new level with the integration of a Sound Bar that delivers 30W of power (15W x2-speakers), enhanced by SRS TruSurround HD(TM) and TruVolume HD(TM). All the other new XVT models (SV320XVT, SV370XVT, SV422XVT, SV472XVT) also take advantage of SRS TruSurround and TruVolume.</p>

<p>SRS TruSurround HD(TM) makes it possible to enjoy realistic surround sound without running cables or adding additional speakers. Providing a suite of processes, TruSurround HD uses the improved SRS TruBass(TM) to optimize the low frequency performance of the VIZIO Sound Bar for cleaner bass tones. SRS Dialog Enhancement ensures crisp and intelligible vocals, while Definition Control delivers maximum high-frequency realism and clarity.</p>

<p>SRS' latest innovation, TruVolume HD(TM) allows customers to set their volume level at a pre-defined position. Regardless of the content, be it a quiet movie passage or a loud commercial, there are no annoying volume fluctuations. SRS uses Intelligent Multi-band Monitoring and Analysis to establish a consistent volume while delivering a more natural listening experience. There is no pumping, breathing, or clipping artifacts that is commonly associated with traditional Automatic Gain Controls (AGCs). The circuit works flawlessly with mono, stereo and even multi-channel sources.</p>

<p><br />
<B>Thoughtfully Earth-friendly</B></p>

<p>VIZIO is committed to making more eco-friendly products and designs. To that end, the VF551XVT use LED panels rather than CCFL panels. LED panels do not use mercury, and are highly efficient requiring significantly less power.</p>

<p>Also being introduced in these same models is Dynamic Power Control (DCP), which lowers power consumption by up to 15% more than Energy Star 3.0 requirements without sacrificing picture quality. DCP analyzes image data to optimize the amount of backlight produced, minimizing image distortion. According to CNET tests, TVs that meet Energy Star 3.0 requirements can save up to $100 a year, so it is possible to save even more using VIZIO's latest products with DCP.</p>

<p>VIZIO guarantees zero bright pixel defects for the duration of the limited one-year warranty on all their products. Additionally, they provide free one-year on-site service and lifetime technical support.</p>

<p><br />
<B>XVT Models and Features</B></p>

<p>  Model: SV320XVT *NEW*<br />
  Size: 32" 120Hz FULL HD 1080P LCD HDTV<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 120 Hz<br />
  HDMI inputs: 3<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Fall 2009<br />
  Price: $749.99</p>

<p>  Model: SV370XVT *NEW*<br />
  Size: 37" 120Hz FULL HD 1080P LCD HDTV<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 120 Hz<br />
  HDMI inputs: 3<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Fall 2009<br />
  Price: $999.99</p>

<p>  Model: SV421XVT *NEW*<br />
  Size: 42" LCD<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 240 Hz<br />
  HDMI inputs: 4<br />
  Game Port: Yes<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Summer 2009<br />
  Price: $1099.99</p>

<p>  Model: SV471XVT *NEW*<br />
  Size: 47" LCD<br />
  Contrast: Mega Dynamic 50,000:1<br />
  Refresh Rate: 240 Hz<br />
  HDMI inputs: 4<br />
  Game Port: Yes<br />
  Audio Enhancements: SRS TruSurround HD & TruVolume<br />
  Availability: Summer 2009<br />
  Price: $1399.99</p>

<p>  Model: VF551XVT1A *NEW*<br />
  Size: 55" LED<br />
  Contrast: 1,000,000:1 Mega Dynamic Contrast Ratio<br />
  Refresh Rate: 120 Hz<br />
  HDMI inputs: 5<br />
  Game Port: Yes<br />
  Audio Enhancements: Integrated Sound Bar, SRS TruSurround HD & TruVolume<br />
  Availability: Summer 2009<br />
  Price: $1999.99</p>

<p></p>

<p>The new models can be seen at VIZIO's private presentations at the WYNN Hotel's La Tache Ballrooms during the International CES in Las Vegas from January 7-10, 2009.</p>

<p><br />
<B>About VIZIO</B></p>

<p>VIZIO, Inc. "Where Vision Meets Value," headquartered in Irvine, California, is America's HDTV Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead major categories in U.S. TV sales. VIZIO is committed to bringing feature-rich flat panel televisions to market at a value through practical innovation. VIZIO offers a broad range of award winning Plasma and LCD HDTVs including the new XVT series. VIZIO's products are found at Costco Wholesale, Sam's</p>

<p>Club, Sears, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Good Housekeeping's Best Big-Screens, CNET's Top 10 Holiday Gifts, PC World's Best Buy among others. For more information, please call 888-VIZIOCE or visit on the web at www.VIZIO.com.</p>

<p>The V, VIZIO, Where Vision Meets Value names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>Source: VIZIO, Inc. </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009  7:13 AM</b>
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
			<?=getComments(1607)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1607)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/vizio-expands-flagship-xvt-series-of-hdtvs.php" type="text/javascript" charset="utf-8"></script>
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