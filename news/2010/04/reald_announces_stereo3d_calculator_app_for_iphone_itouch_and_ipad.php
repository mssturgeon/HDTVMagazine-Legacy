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
		AND e.entry_id = 3681";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3681 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3681 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3681";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/04/reald-announces-stereo3d-calculator-app-for-iphone-itouch-and-ipad.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3681";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad" />
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
	<title>HDTV Magazine - RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad</title>
	<meta name="keywords" content="frameforge previz, previz studio, reald professional, professional stereo, calculator app, reald, stereo, calculator, frameforge, camera, professional, lens, studio, rig, previz, app, production, parallax, settings, shot, filmmakers, software, content, shooting, tool" />
	<meta name="description" content="&lt;a href=&quot;http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&amp;subid=&amp;offerid=146261.1&amp;type=10&amp;tmpid=3909&amp;RD_PARM1=http%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Freald-professional-stereo3d%2Fid362539528%3Fmt%3D8&quot;&gt;&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/reald-stereo3d-calculator-app-iphone.jpg&quot; alt=&quot;RealD Professional Stereo3D Calculator&quot; height=&quot;100&quot; width=&quot;100&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;&lt;/a&gt;RealD Inc., a leading 3D technology provider for cinema, home and professional applications, in association with FrameForge Previz Studio, a cutting-edge pre-visualization solution for professional filmmakers, announced today the availability of the &lt;a href=&quot;http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&amp;subid=&amp;offerid=146261.1&amp;type=10&amp;tmpid=3909&amp;RD_PARM1=http%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Freald-professional-stereo3d%2Fid362539528%3Fmt%3D8&quot;&gt;RealD Professional Stereo3D Calculator&lt;/a&gt; app for iPhone, iTouch and iPad, a pre-production and on-set tool for filmmakers shooting in stereoscopic 3D. Designed for both seasoned stereographers and newcomers to stereo 3D production, this app saves time and budget by calculating parallax and separation to plan stereo 3D depth, helping a filmmaker determine an optimal camera rig, its settings and lens configuration. The RealD Professional Stereo3D Calculator is now available for..." />
	<meta name="title" content="RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/04/reald-announces-stereo3d-calculator-app-for-iphone-itouch-and-ipad.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;a href=&quot;http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&amp;subid=&amp;offerid=146261.1&amp;type=10&amp;tmpid=3909&amp;RD_PARM1=http%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Freald-professional-stereo3d%2Fid362539528%3Fmt%3D8&quot;&gt;&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/reald-stereo3d-calculator-app-iphone.jpg&quot; alt=&quot;RealD Professional Stereo3D Calculator&quot; height=&quot;100&quot; width=&quot;100&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;&lt;/a&gt;RealD Inc., a leading 3D technology provider for cinema, home and professional applications, in association with FrameForge Previz Studio, a cutting-edge pre-visualization solution for professional filmmakers, announced today the availability of the &lt;a href=&quot;http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&amp;subid=&amp;offerid=146261.1&amp;type=10&amp;tmpid=3909&amp;RD_PARM1=http%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Freald-professional-stereo3d%2Fid362539528%3Fmt%3D8&quot;&gt;RealD Professional Stereo3D Calculator&lt;/a&gt; app for iPhone, iTouch and iPad, a pre-production and on-set tool for filmmakers shooting in stereoscopic 3D. Designed for both seasoned stereographers and newcomers to stereo 3D production, this app saves time and budget by calculating parallax and separation to plan stereo 3D depth, helping a filmmaker determine an optimal camera rig, its settings and lens configuration. The RealD Professional Stereo3D Calculator is now available for..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3681', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/04/reald-announces-stereo3d-calculator-app-for-iphone-itouch-and-ipad.php">RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>April 26, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=474&category=HD Video Production">HD Video Production</a></b>
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
				<p class="prtitle">RealD Announces Stereo3D Calculator App for iPhone, iTouch and iPad</p>

<center><i>Mobile 3D Production Tool Calculates Parallax and Optimal Stereo 3D Rig Settings for Camera, Lens and Rig Configurations</center></i><br />
<br />

<p><a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=3909&RD_PARM1=http%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Freald-professional-stereo3d%2Fid362539528%3Fmt%3D8"><img src="http://www.hdtvmagazine.us/news/images/reald-stereo3d-calculator-app-iphone.jpg" alt="RealD Professional Stereo3D Calculator" height="175" width="175" class="keyimg"></a><strong>LOS ANGELES, April 26 /PRNewswire/ -- </strong>RealD Inc., a leading 3D technology provider for cinema, home and professional applications, in association with FrameForge Previz Studio, a cutting-edge pre-visualization solution for professional filmmakers, announced today the availability of the <a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=3909&RD_PARM1=http%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Freald-professional-stereo3d%2Fid362539528%3Fmt%3D8">RealD Professional Stereo3D Calculator</a> app for iPhone, iTouch and iPad, a pre-production and on-set tool for filmmakers shooting in stereoscopic 3D. Designed for both seasoned stereographers and newcomers to stereo 3D production, this app saves time and budget by calculating parallax and separation to plan stereo 3D depth, helping a filmmaker determine an optimal camera rig, its settings and lens configuration. The RealD Professional Stereo3D Calculator is now available for download from iTunes for $299.99.</p>

<p>"Shooting in 3D is an art form, one that can be challenging not only for novices but experienced professionals as well given the calculations needed to create an effective and comfortable 3D visual," said Joshua Greer, President of RealD. "This Calculator takes away the guesswork involved in shooting in 3D, helping ensure the best configured rigs are in place for each shot and maximizing on-set efficiency."</p>

<p>The RealD Professional Stereo3D Calculator is an interactive tool for planning 3D film and video capture. Designed to work with any camera and stereo rig combinations, users can select the display size upon which the content will be shown, the camera, its lens or lenses, and customize the rig and camera capabilities and parameters. The Calculator factors together these settings and reports how subjects will look in 3D to assist in determining an optimal rig set-up for the desired 3D effects.</p>

<p>"Like our FrameForge Previz Studio software package, the goal is to deliver a tool that helps filmmakers shoot better stereo 3D by handling the complicated 3D calculations for them. With this app, a filmmaker can remain focused on creating powerful and compelling 3D instead of getting lost in the angles, geometry and optics behind it," added Ken Schafer, President and Lead Program Architect at Innoventive Software (FrameForge Previz Studio).</p>

<p><br />
<strong>RealD Professional Stereo3D Calculator features include:</strong></p>

<p>  --  Parallax - know the depth of a shot by seeing the actual positive and<br />
      negative parallax values at any distance with the subject in focus<br />
  --  Separation - learn how various objects in each shot will affect the<br />
      on-screen image quality based on separation and distance from the<br />
      cameras (includes "Maximum Positive On-Screen Offset")<br />
  --  Lens - determine the appropriate lens or zoom to achieve the desired<br />
      3D effect<br />
  --  Shooting style - choose "Converged" or "Parallel" style to customize<br />
      shots and plan post-production workflow<br />
  --  Camera Setup - set camera model, lens type and interaxial range; test<br />
      real shots with interactive shot settings and quickly rearrange the<br />
      setup real-time<br />
  --  Stereo Solver(TM) Mode gives the exact settings needed to accomplish<br />
      any shot while ensuring far objects' parallax never goes over defined<br />
      maximum on-screen offset<br />
  --  S3D Calculator Mode functions as a pure 3D reference calculator with<br />
      interactive value tables</p>

<p><br />
<strong>About RealD Inc.</strong></p>

<p>RealD Inc. is a leading global licensor of stereoscopic (three-dimensional), or 3D, technologies. RealD's extensive intellectual property portfolio enables a premium 3D viewing experience in the theater, the home and elsewhere. RealD licenses its RealD Cinema Systems to motion picture exhibitors that show 3D motion pictures and alternative 3D content. RealD also provides its RealD Format, active and passive eyewear, and display and gaming technologies to consumer electronics manufacturers and content producers and distributors to enable the delivery and viewing of 3D content on high definition televisions, laptops and other displays. RealD's cutting-edge 3D technologies have been used for applications such as piloting the Mars Rover, heads-up displays for military jets and robotic medical procedures. For more information about RealD, please visit its website at <a target="_blank" href="http://www.reald.com/">www.reald.com</a></p>

<p><br />
<strong>About FrameForge Previz Studio</strong></p>

<p>FrameForge Previz Studio is the industry leader in optically accurate desktop previsualization for the film and television industries. Created by San Diego-based Innoventive Software, LLC, the award-winning FrameForge Previz Studio software is available for both Mac and Windows, and has powered the productions of thousands of filmmakers worldwide, from Academy Award winning directors and ASC Cinematographers to the next breakout indie filmmaker. FrameForge's Stereo Edition leverages the optical power and accuracy of its predecessors while offering unparalleled ease and power for stereo 3D production needs. It has been quickly adopted by some of the world's top stereographers in addition to being selected by the Sony 3D Technology Center for their Stereographic Previsualization. <a target="_blank" href="http://www.frameforge3d.com/">www.frameforge3d.com</a></p>

<p>  Media Contact:<br />
  Rick Heineman (RealD)<br />
  (310) 339-9347<br />
  rheineman@reald.com</p>

<p>  Stuart Voytilla (FrameForge)<br />
  (858) 270-7204<br />
  vpmarketing@frameforge3d.com</p>

<p>Source: RealD</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>April 26, 2010  9:48 PM</b>
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
			<?=getComments(3681)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3681)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/04/reald-announces-stereo3d-calculator-app-for-iphone-itouch-and-ipad.php" type="text/javascript" charset="utf-8"></script>
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