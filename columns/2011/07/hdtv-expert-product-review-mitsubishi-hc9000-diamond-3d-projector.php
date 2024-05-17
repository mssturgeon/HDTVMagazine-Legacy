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
		AND e.entry_id = 4431";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4431 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4431 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4431";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-product-review-mitsubishi-hc9000-diamond-3d-projector.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4431";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector</title>
	<meta name="keywords" content="color temperature, shutter glasses, active shutter, gain screen, black levels, color, projector, mode, glasses, screen, image, –, figure, ’s, brightness, temperature, while, levels, content, black, adjustments, gain, light, video, gamma" />
	<meta name="description" content="Mitsubishi&amp;acirc;��s entry into the 3D projector marketplace makes beautiful 2D images. But it needs either more horsepower for 3D, or a high-gain screen for viewing." />
	<meta name="title" content="HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-product-review-mitsubishi-hc9000-diamond-3d-projector.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Mitsubishi&amp;acirc;��s entry into the 3D projector marketplace makes beautiful 2D images. But it needs either more horsepower for 3D, or a high-gain screen for viewing." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4431', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-product-review-mitsubishi-hc9000-diamond-3d-projector.php">HDTV Expert - Product Review: Mitsubishi HC9000 Diamond 3D Projector</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>July  7, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=497&category=Front Projection">Front Projection</a></b>, <b><a href="/category.php?id=308&category=Marketplace">Marketplace</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>While 3D TVs have been available for over a year, the first crop of 3D front projectors are shipping now. The models I’m aware of use either digital light processing (DLP) or liquid crystal on silicon (LCoS) imaging technologies, and all of them are engineered to operate with active shutter glasses, with the exception of LG’s $15,000 CF3D, which works with passive eyewear.</p>
<p>Mitsubishi’s HC9000D has been in development for the better part of a year, and I had the chance to see it in the prototype stage a few times prior to this review. Those earlier versions were underpowered, making the 3D footage they projected unusually dark.</p>
<p>Now, Mitsubishi has started shipping a fully-powered chassis with some interesting bells and whistles inside. It comes with power zoom, focus, and lens shift, plus multi-step gamma correction and a two-position IR emitter for synchronizing its active shutter glasses.</p>
<div id="attachment_1327" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1327" href="http://www.hdtvexpert.com/?attachment_id=1327"><img class="size-full wp-image-1327" title="Mits Diamond 3D" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mits-Diamond-3D.jpg" alt="" width="600" height="351" /></a><p class="wp-caption-text">Figure 1 - The HC9000D is definitely a 'looker!'</p></div>
<p>OUT OF THE BOX</p>
<p>This is not a small projector, nor is it particularly light at 32 pounds. But it does have that cool gloss black finish that disappears into the darkness, plus an aerodynamic housing with all of the connectors along the left side, and not in the back.</p>
<p>The imaging engine for the HC9000D may be a surprise to you: It uses three .61” SXRD LCoS chips, just like the previously-mentioned LG CF3D and of course, both of Sony’s 3D front projector offerings. This is Mitsubishi’s first foray into reflective imaging, and LCoS offers a much lower cost than 3-chip DLP engines.</p>
<p>3D projectors need lots of light to overcome all of the polarization losses in active shutter glasses, so Mits has equipped the HC9000D with a 230-watt short-arc lamp. The supplied zoom lens has a ratio of 1.8:1, adequate for any home theater set-up as it easily lit up my Da-Lite Affinity 92” screen at a distance of 12 feet.</p>
<p>The input connectors include a pair of HDMI 1.4a inputs that also support ten different standard digital computer resolutions, and there’s also an analog VGA PC input connector for everything from 640×480 to 1080p/60. Mitsubishi has also provided a single component video (YPbPr) input, plus composite and S-video jacks. <em>(Question: Why are manufacturers still supporting composite video on high-end 1080p projectors?)</em></p>
<p>The interface panel is rounded out by a pair of 12V triggers for powered screens and anamorphic lens adapters, an RS-232 jack for remote control, and another DIN jack that connects to the EY-3D-EMT1 IR emitter through a short (1 meter) or long (15 meter) cable. The emitter can be attached to the lower front panel of the projector, or positioned under your projection screen.</p>
<p>The supplied remote control is identical in function to all previous Mits remotes (I inadvertently turned on my Mits HC6000 a few times with it), except that it has a black housing. You can directly access any input, jump to preset picture modes, operate the powered lens functions, and step through the iris settings. The only exception is that the STANDBY button now toggles between 2D and 3D display modes.</p>
<p>MENUS AND ADJUSTMENTS</p>
<p>Mitsubishi 3LCD projectors are known for high image quality and part of the reason is the detailed menus provided for in-depth calibrations. That protocol continues with the LCoS-powered HC9000D. Four different picture preset modes (Cinema, Video, 3D, Dynamic) are provided for viewing, along with three USER memory slots.</p>
<p>Gamma correction is also possible through five presets (Cinema, 2.0, 2.1, 2.2, 3D, and USER), and the USER gamma adjustments offer detailed adjustments of white, red, green, and blue at 15 grayscale steps. That is a tremendous amount of tweaking at your fingertips, if you are that fanatical about precise gamma response.</p>
<p>Color temperature and white balance adjustments are also available for each USER mode, or you can select from one of six presets, including 5800K, 6000K, and 6500K. None of these are completely accurate, but will get you into the ballpark. There are also a set of color management controls for all six primaries that I suggest you avoid playing with, as they don’t exactly work as intended in their current implementation.</p>
<p>The menu complement is rounded out with three different levels of black set-up (0, 3.75, and 7.5 IRE), a ‘cinema filter,’ 3:2 frame rate conversion or ‘true’ (native) frame rate selections, and various adjustments for noise reduction and detail enhancement. The former will soften the image to hide digital noise artifacts, while the latter may enhance edge transitions too much. I’d leave ‘em both off if possible.</p>
<p>The HC9000D also has Image Anyplace software built-in. It lets you re-map the pixels on a projected image to correct for off-axis projection, such as a severe high and wide angle. While Image Anyplace works quite well, it does impact image resolution as it decimates pixels to correct for trapezoidal distortion. (It can also fix lens distortions like barreling and pincushioning.)</p>
<p>You are much better off mounting the projector as close to the optical centerline of the screen as possible, and using the lens shift controls to move the image into position. Try to avoid any adjustments that manipulate pixels to correct for geometry!</p>
<p>The HDMI inputs have their own sets of tweaks. You can manually select the HDMI color depth (4:2:2, 4:4:4, or RGB), or let the projector configure it for you. There are also four different HDMI inputs modes – Auto, Standard, Enhanced, and Super White.</p>
<p>It’s best to leave this setting in Auto, as it will pick the correct color bit depth for each connected input. Enhanced is usually selected for PC input connections, but I have no idea what ‘Super White’ is intended to do: The manual just says, <em>“Select when solid white occurs.”</em> Any guesses?</p>
<p>There are also a few useful 3D image adjustments. The only 3D mode that is detected automatically by the HC9000D is the Blu-ray 1080p/24 frame-packing format, so called because it packs both left eye and right eye video into a single BD frame with 45 pixels of blanking for a total of 1920×2205 pixels. On the other hand, the so-called ‘frame compatible’ 3D formats (also known as ‘half-resolution’ formats) must be selected manually in the 3D menu, and include top+bottom (720p) and side-by-side (1080i).</p>
<p>You can compensate for light attenuation through polarization losses by boosting projector brightness in five steps, with 5.0 being the default setting. The sync pulse for active shutter glasses can also be reversed if needed in this menu. Normally, you should not need to play with either control (and as you’ll find out, a brighter screen will do you more good than the 3D brightness compensation settings!).</p>
<p>The last control I should point out is the ever-present Iris adjustment. Dynamic iris controls are <em>de rigueur</em> for LCD and LCoS projectors to drop black levels and improve contrast on low-level video content. I have never liked these adjustments because of the non-linear effect they have on gamma curves, and prefer to leave them off and just work with whatever dynamic range the projector manufacturer brought to the table – which isn’t as bad as you might think most of the time.</p>
<p>If you must use the iris settings, you have four different presets (Open, 3, 2, and 1), plus 18 steps of irising in the User menu. My advice? Set your black levels correctly and adjust the contrast for best dynamic range, and just live with it. In 2D mode, the black levels may be a bit higher than you’d want, but in 3D mode, you won’t see them anyway with the glasses on.</p>
<p>ON THE TEST BENCH: 2D</p>
<p>For my tests, I used a combination of SpectraCal’s CalMan V4.4 software and ColorFacts 7.5 to take all readings through Spyder 2 and Eye One Pro sensors. All of my calibrations were done in 2D mode, as I was most interested to see what the projector did to these settings when switched into 3D mode.</p>
<p>All 2D test patterns were generated by an AccuPel HDG4000, while my 3D test patterns were custom-created in Photoshop and played back @ 1280×720 resolution from a Toshiba M645 laptop computer, using the top+bottom frame compatible format. Additional 3D content came from Samsung’s Blu-ray test disc and 3D Blu-ray movie clips from <em>Avatar </em>and <em>How to Train Your Dragon, </em>played back on a Samsung BD-C6900.</p>
<p>You will be surprised at how little tweaking you’ll need to do to get a stable grayscale out of the HC9000D. After minimal calibration, I measured 2D brightness at 635 lumens with a center color temperature of 6542 degrees. That color temperature reading varied by a maximum of just 230 degrees over nine points of measurement. So far, so good!</p>
<p>Brightness uniformity was lower than I expected at 69% to the average corner from center, and 55% to the worst corner. That’s bordering on hot-spot territory, as 50% is a drop of one full <em>f</em>-stop in brightness. Contrast measurements were much better than you’d expect with the iris off, coming in at 279:1 ANSI (average) and 538:1 peak. While those numbers aren’t as impressive as what JVC’s achieved with their wire grid dichroic design, they are still respectable for any other LCoS projector.</p>
<p>I mentioned earlier that Mitsubishi always does a superb job with grayscale and color temperature performance. <strong>Figure 2</strong> shows an almost-perfect 2.3 gamma curve after calibration that’s as good as any I’ve ever seen on the best projectors. (And it was measured with the iris disabled.)</p>
<p>The secret? Very tight tracking of red, green, and blue levels at each luminance measurement. You can see just how tight those levels track in <strong>Figure 3</strong>, which is the RGB histogram for the target color temperature setting of 6500 Kelvin.</p>
<div id="attachment_1328" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1328" href="http://www.hdtvexpert.com/?attachment_id=1328"><img class="size-full wp-image-1328" title="Mitsubishi Diamond 9000 3D 6-10-11 Luminance Histogram FINAL CAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mitsubishi-Diamond-9000-3D-6-10-11-Luminance-Histogram-FINAL-CAL-MR.jpg" alt="" width="600" height="322" /></a><p class="wp-caption-text">Figure 2 - The HC9000D produces a nearly-perfect 2.3 gamma curve after calibration.</p></div>
<div id="attachment_1329" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1329" href="http://www.hdtvexpert.com/?attachment_id=1329"><img class="size-full wp-image-1329" title="Mitsubishi Diamond 9000 3D 6-10-11 RGB Levels Histogram FINAL CAL MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mitsubishi-Diamond-9000-3D-6-10-11-RGB-Levels-Histogram-FINAL-CAL-MR.jpg" alt="" width="600" height="321" /></a><p class="wp-caption-text">Figure 3 - This RGB histogram shows tight tracking of red, green, and blue across the entire grayscale.</p></div>
<p>The HC9000D has a ‘ginormous’ color gamut, which (unfortunately) cannot be dialed back accurately. That means the colors you’ll see off Blu-ray discs and other HD content will be over-saturated. The color management controls will not help you here – de-saturating a color will result in incorrect display of other secondary colors.</p>
<p>The correct approach is to set the exact color coordinates at the factory for RGB and CMY, based on the standard used to master the content being viewed, something very few projector manufacturers bother to do. <strong>Figures 4a-b</strong> shows the full color gamut of the projector compared to the BT.709 HDTV gamut and P3 digital cinema gamut.</p>
<div id="attachment_1330" class="wp-caption aligncenter" style="width: 597px"><a rel="attachment wp-att-1330" href="http://www.hdtvexpert.com/?attachment_id=1330"><img class="size-full wp-image-1330" title="Mitsubishi Diamond 9000 3D 6-10-11 CIE Chart BT709" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mitsubishi-Diamond-9000-3D-6-10-11-CIE-Chart-BT709.jpg" alt="" width="587" height="678" /></a><p class="wp-caption-text">Figure 4a - The HC9000D's mapped color gamut, compared to the BT.709 HDTV color space.</p></div>
<div id="attachment_1331" class="wp-caption aligncenter" style="width: 597px"><a rel="attachment wp-att-1331" href="http://www.hdtvexpert.com/?attachment_id=1331"><img class="size-full wp-image-1331" title="Mitsubishi Diamond 9000 3D 6-10-11 CIE Chart P3" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mitsubishi-Diamond-9000-3D-6-10-11-CIE-Chart-P3.jpg" alt="" width="587" height="678" /></a><p class="wp-caption-text">Figure 4b - And here's how the HC9000D's color gamut compares to the P3 digital cinema color space.</p></div>
<p>ON THE TEST BENCH: 3D</p>
<p>All well and good – the HC9000D is a top-notch 2D projector – but what happens in 3D mode? For starters, let’s see what happens when switching from 2D mode to 3D mode with glasses off and on.</p>
<p>To measure the changes in brightness, I placed a Minolta CL200 directly in front of my projection screen to take an incident light reading from the projector for this test. I started with a baseline (glassless) reading of 1124 lux and a measured color temperature of 6190K – a bit on the warm side. With 3D mode enabled on the projector, but no glasses in place, the readings changed to 1137 lux (3D brightness @ 5.0) and 6093K.</p>
<p>After positioning Mitsubishi’s active shutter glasses in front of the CL200’s sensor, brightness readings dropped to 419 lux with a color temperature of 6576K. Finally, I turned the glasses on, and saw brightness drop to 146 lux while the measured color temperature soared to 8529K. (Switching the lamp from its normal setting into HIGH mode increased brightness slightly to 66 lux.)</p>
<p>That’s quite a decrease! Comparing the final 3D reading with glasses to the calibrated 2D reading without glasses, the amount of light that finally makes it to your eyes has decreased by about 87%</p>
<p>So, what’s the solution? You will need a higher-gain screen to enjoy 3D images from the HC9000D, as it’s just not bright enough for viewing on low-gain screens with active shutter glasses – at least, not at the projection distance I use. I dusted off an older 82” Vutec SilverStar (6.0 gain) screen, and it made a world of difference with the HC9000D.</p>
<p>Here’s the conundrum: A high-gain screen doesn’t match up well to the projector’s 2D mode, as it will elevate black levels. Does that suggest you’ll need two screens? Maybe not, as Stewart Filmscreens just announced a combination 2D/3D screen that’s supposedly optimized for both modes. (They call it “5D” – I kid you not!)</p>
<p>IMAGE QUALITY</p>
<p>2D image quality is top-notch, as you’d expect with a projector using an HQV Reon processor. The adjustable frame rates are used to convert 24 fps filmed content to 96 Hz (quad refresh), while 60 Hz video is doubled to 120 Hz. Scaling of 720p content to 1080p is seamless and de-interlacing of 1080i channels showed absolutely no motion errors. The projector’s dynamic range is excellent (within the limits of its black levels) and my only complaint is that colors pop too much, for reasons I explained earlier.</p>
<p>You could be very happy just running this projector in 2D mode. In 3D mode, it’s a different story. Most of the content I looked at on my Affinity screen was too dark when viewed in 3D mode and exhibited desaturated colors with low contrast.</p>
<p>The Vutec gain screen helped considerably, but this projector needs to be cranking out at least 300 – 400 3D lumens after calibration to work with my screen type, size, and projection throw. If you reverse-engineer the numbers, that means almost 3000 lumens in calibrated 2D mode.</p>
<p>The best 3D scenes were observed with the daytime flying sequences in <em>Dragon</em> and the final attack sequences in <em>Avatar</em>. On the Vutec SilverStar screen, they punched up considerably with improved color saturation, and the viewing experience was quite enjoyable. The 24-96 fps frame rate conversion provides a smooth, bright image with absolutely zero flicker.</p>
<p>One problem I noticed was crosstalk in each lens. This popped up when the glasses were tilted even slightly, with the effect more pronounced in high-contrast scenes. For 3D to present correctly; crosstalk in the glasses has to be kept to a minimum. Otherwise, you will begin to feel eyestrain and may develop a headache after sustained viewing.</p>
<p>For comparison, Sony’s 3D active shutter glasses suffer from crosstalk problems because only one polarizer is used, while Samsung and Panasonic glasses use two polarizers and are much better at suppressing crosstalk. The Mitsubishi glasses also use dual polarizers, but their ‘extinction ratio’ isn’t as good as I would have expected. <strong>Figures 5a – 5d</strong> show sample 3D images where crosstalk is strongly evident and not quite as evident.</p>
<div id="attachment_1332" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1332" href="http://www.hdtvexpert.com/?attachment_id=1332"><img class="size-full wp-image-1332" title="Mits Glasses Text Chart MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mits-Glasses-Text-Chart-MR.jpg" alt="" width="600" height="349" /></a><p class="wp-caption-text">Figure 5a - This 3D text chart shows crosstalk (ghost images) around the letters and vertical lines.</p></div>
<div id="attachment_1333" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1333" href="http://www.hdtvexpert.com/?attachment_id=1333"><img class="size-full wp-image-1333" title="Mits Glasses Level MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Mits-Glasses-Level-MR.jpg" alt="" width="600" height="355" /></a><p class="wp-caption-text">Figure 5b - A ghost image of the center circle can be seen clearly in this photo.</p></div>
<div id="attachment_1334" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1334" href="http://www.hdtvexpert.com/?attachment_id=1334"><img class="size-full wp-image-1334" title="Dragon Wide View 1 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Dragon-Wide-View-1-MR.jpg" alt="" width="600" height="267" /></a><p class="wp-caption-text">Figure 5c - Crosstalk isn't as evident when watching 3D movies, although I noticed it in this scene from How to Train Your Dragon.  (Image © 2010 Dreamworks Animation)</p></div>
<p><em> </em></p>
<div id="attachment_1336" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1336" href="http://www.hdtvexpert.com/?attachment_id=1336"><img class="size-full wp-image-1336" title="Dragon CU View 2 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/07/Dragon-CU-View-2-MR1.jpg" alt="" width="600" height="342" /></a><p class="wp-caption-text">Figure 5d - Subtle ghost images were seen along the edges of the mountains and the dragon's wings.  (Image © 2010 Dreamworks Animation)</p></div>
<p>You will clearly see double images in the test patterns, but the ghosting isn’t quite as apparent with the stills from <em>Dragon</em>. But it is there, along the jagged rocky cliffs and other background objects. It all depends on the angle of your head – if you tilt your head to either side, the effect becomes more pronounced. Ghosting is readily apparent with credits and other high-contrast text and symbols.</p>
<p>CONCLUSIONS</p>
<p>Mitsubishi’s HC9000D is a top-notch 2D projector, but underpowered for 3D with low-gain screens. It calibrates quickly and performs nicely, but those calibrations will shift noticeably when viewing with 3D glasses. You’ll definitely need a gain screen with this projector for 3D content, and it might be a good idea to choose one that has a slightly warm color temperature that will offset the higher color temperature in 3D mode.</p>
<p>More horsepower under the hood would help. As I mentioned earlier, something in the neighborhood of 3000 lumens would be required to (a) perform a full 2D calibration and (b) provide enough illumination in 3D mode to low-gain (1.0 to 1.3) screens in the 82-inch to 102-inch range, assuming  a projection distance of 10 – 12 feet.</p>
<p>However, if you are sitting closer to a smaller screen, then you will be in better shape: The HC9000’s measured light output after calibration should be adequate for 3D viewing on a 72-inch screen at a distance of 6 to 8 feet, as you will wind up with 3x to 4x brighter images. And you DO want to sit closer to 3D screens to get the maximum impact: My recommended seating distance is 1x to 1.3x the screen diagonal measurement. That will make the 3D images fill 50% or more of your field of view, and give you that theater-like immersive experience!</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>July  7, 2011 10:00 AM</b>
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
			<?=getComments(4431)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4431)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/07/hdtv-expert-product-review-mitsubishi-hc9000-diamond-3d-projector.php" type="text/javascript" charset="utf-8"></script>
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