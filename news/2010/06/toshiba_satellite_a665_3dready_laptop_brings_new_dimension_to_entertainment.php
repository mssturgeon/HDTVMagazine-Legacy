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
		AND e.entry_id = 3793";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3793 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3793 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3793";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/06/toshiba-satellite-a665-3dready-laptop-brings-new-dimension-to-entertainment.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3793";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment" />
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
	<title>HDTV Magazine - Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment</title>
	<meta name="keywords" content="legal footnote, footnote toshiba, blu ray, may vary, toshiba america, toshiba, technology, vision, laptop, may, products, nvidia, satellite, systems, information, see, content, drive, blu, ray, display, new, footnote, legal, division" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/toshiba-satellite-a665-3d-ready-laptop.jpg&quot; alt=&quot;Toshiba Satellite A665 3D-Ready Laptop&quot; height=&quot;104&quot; width=&quot;144&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Toshiba's Digital Products Division (DPD), a division of Toshiba America Information Systems, Inc., today announced the introduction of its first 3D-ready laptop for the U.S. market.

The Satellite&amp;reg; A665 3D Edition laptop features..." />
	<meta name="title" content="Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/06/toshiba-satellite-a665-3dready-laptop-brings-new-dimension-to-entertainment.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/toshiba-satellite-a665-3d-ready-laptop.jpg&quot; alt=&quot;Toshiba Satellite A665 3D-Ready Laptop&quot; height=&quot;104&quot; width=&quot;144&quot; style=&quot;float:left;padding:0 5px 5px 0&quot;&gt;Toshiba's Digital Products Division (DPD), a division of Toshiba America Information Systems, Inc., today announced the introduction of its first 3D-ready laptop for the U.S. market.

The Satellite&amp;reg; A665 3D Edition laptop features..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3793', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/06/toshiba-satellite-a665-3dready-laptop-brings-new-dimension-to-entertainment.php">Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 15, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=477&category=HTPCs & Laptops">HTPCs & Laptops</a></b>
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
				<p class="prtitle"> Toshiba Satellite A665 3D-Ready Laptop Brings New Dimension to Entertainment</p>

<center><i>Laptop Delivers Immersive Entertainment Experience Powered by NVIDIA 3D Vision Technology</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/toshiba-satellite-a665-3d-ready-laptop.jpg" alt="Toshiba Satellite A665 3D-Ready Laptop" height="104" width="144" class="keyimg"><strong>IRVINE, Calif.--(BUSINESS WIRE)--</strong>Toshiba's Digital Products Division (DPD), a division of Toshiba America Information Systems, Inc., today announced the introduction of its first 3D-ready laptop for the U.S. market.</p>

<p>The Satellite&reg; A665 3D Edition laptop features a 15.6-inch diagonal TruBrite&reg; widescreen LCD display1 with LED backlighting and a 120Hz refresh rate. NVIDIA&reg; 3D Vision&trade; software and hardware technologies2 drive the stereoscopic 3D experience, delivering out-of-the-box compatibility with hundreds of PC games, as well as videos and photos. Equipped with a rewriteable Blu-ray Disc&trade; drive3, the A665 is also ready to support content playback in the Blu-ray 3D format as it becomes available in the marketplace4.</p>

<p>Encased in the all-new Fusion&reg; X2 finish in Charcoal, the Satellite A665 laptop delivers enthusiast-class performance and features with the processing power of the Intel&reg; Core&trade; i7 quad-core processor5 with TurboBoost technology, plus the NVIDIA&reg; GeForce&reg; GTS 350M graphics processing unit6 (GPU) with 1GB of GDDR3 video memory7. The NVIDIA 3D Vision kit, which comes standard with the Satellite A665, includes software, a pair of wireless active shutter glasses and an emitter, enables users to get in the action quickly with easy set-up and intuitive conversion of supported content for stereoscopic 3D viewing.</p>

<p>"With this active shutter 3D technology, we are able to provide consumers with dynamic, high-quality stereoscopic images right from the laptop screen," said Carl Pinto, vice president of product development, Toshiba America Information Systems, Inc., Digital Products Division. "We designed this system to support functionality that will become available in new drivers and software enhancements, so early adopters of 3D technology will be able to take advantage of new capabilities such as the ability to play Blu-ray 3D content and more."</p>

<p>"3D Vision technology is for more than just PC gamers," said Phil Eisler, general manager of 3D Vision technology at NVIDIA. "We are working closely with our partners to ensure a wealth of 3D content, including games, photos, movies and even the Internet can be fully enjoyed on a 3D laptop."</p>

<p>The Satellite A665 is also packed with premium multimedia features. Built-in harman/kardon&reg; speakers deliver impressive sound while Dolby Advanced Audio&trade; further optimizes audio quality so games, movies and music sound more vivid and true to life. Toshiba's unique new Sleep-and-Music technology is also included, enabling users to play music from their MP3 players through the laptop's speakers – even while the laptop is powered down. For movie lovers, the laptop includes Toshiba's Resolution+&trade; DVD upconversion technology, which improves the quality of standard definition DVD content by boosting contrast, sharpness and saturation to near high-definition quality.</p>

<p>Rounding out the list of premium features is an LED backlit keyboard that provides convenience in low-light conditions, while raised-tile style keys deliver a comfortable typing experience. A wide TouchPad&trade; also offers multi-touch control for easier navigation, allowing users to pinch, swipe or zoom through files, applications and webpages.</p>

<p>"3D technology has captured a tremendous amount of mindshare in the display industry. Flat panel makers and graphics chip makers are developing next-generation technology to provide consumers with a rewarding 3D experience," said John Jacobs, director of notebook market research at DisplaySearch. "While much of the discussion has centered on the TV market, the notebook PC stands to become the next major platform for consuming 3D content. Toshiba's new Satellite puts them at the forefront of the 3D adoption curve."</p>

<p>Features available on the Toshiba Satellite A665 3D Edition include:</p>

<p>    * 15.6-inch diagonal HD TruBrite&trade; LED Backlit widescreen display with 120Hz scan rate1<br />
    * Intel&reg; Core&trade; i7-740QM quad core processor5 with TurboBoost technology<br />
    * NVIDIA&reg; GeForce&reg; GTS 350M with 1GB DDR3 discrete graphics memory6<br />
    * NVIDIA 3D Vision&trade; kit with active shutter 3D glasses and emitter<br />
    * Blu-ray Disc&trade; Rewriteable (RE) and DVD SuperMulti drive with LabelFlash3<br />
    * Premium audio with harman/kardon&reg; stereo speakers and Dolby Advanced Audio&trade;<br />
    * Windows&reg; 7 Home Premium operating system<br />
    * 4GB DDR3 RAM7<br />
    * 640GB hard drive8<br />
    * 802.11b/g/n wireless9 and 10/100/1000 Ethernet<br />
    * Bluetooth&reg; V2.1 + EDR10<br />
    * Toshiba Hard Drive Impact Sensor<br />
    * TouchPad&trade; with Multi-touch Control<br />
    * 12-cell battery<br />
    * One eSATA/USB combo port with Toshiba USB Sleep-and-Charge11 and three USB 2.0 ports<br />
    * HDMI port<br />
    * Built-in Webcam with Toshiba Face Recognition<br />
    * Microphone input port with Toshiba Sleep-and-Music<br />
    * 5-in-1 Memory Card Reader and ExpressCard slots</p>

<p><br />
<strong>Pricing and Availability</strong></p>

<p>The Toshiba Satellite A665 3D Edition laptop will be available on June 20, 2010 at select retailers, e-tailers and directly from Toshiba (<a target="_blank" href="http://www.toshibadirect.com/">www.toshibadirect.com</a>) with a starting price of $1,599.99 MSRP12.</p>

<p>Connect with Toshiba on Twitter at twitter.com/ToshibaLaptops and on Facebook at <a target="_blank" href="http://www.facebook.com/ToshibaLaptopsUS/">www.facebook.com/ToshibaLaptopsUS</a>.</p>

<p><br />
<strong>About Toshiba America Information Systems, Inc. (TAIS)</strong></p>

<p>Headquartered in Irvine, Calif., TAIS is comprised of four business units: Digital Products Division, Imaging Systems Division, Storage Device Division, and Telecommunication Systems Division. Together, these divisions provide mobile products and solutions, including industry leading portable computers; imaging products for the security, medical and manufacturing markets; storage products for automotive, computer and consumer electronics applications; and telephony equipment and associated applications.</p>

<p>TAIS provides sales, marketing and services for its wide range of information products in the United States and Latin America. TAIS is an independent operating company owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation. Toshiba Corporation is a world leader and innovator in high technology, a diversified manufacturer and marketer of advanced electronic and electrical products. These products span from information &amp; communications systems; digital consumer products; electronic devices and components; as well as power systems including nuclear energy; industrial and social infrastructure systems; and home appliances. Toshiba was founded in 1875, and today operates a global network of more than 740 companies, with 204,000 employees worldwide and annual sales surpassing $68 billion (6.3 Trillion Yen). For more information on Toshiba visit <a target="_blank" href="http://www.toshiba.com/">www.toshiba.com</a>.</p>

<p>&copy; 2010 Toshiba America Information Systems, Inc. All product, service and company names are trademarks, registered trademarks or service marks of their respective owners. Information including without limitation product prices, specifications, availability, content of services, and contact information is subject to change without notice. All rights reserved.</p>

<p>1 Display. Any small bright dots that may appear on your display are an intrinsic characteristic of the thin film transistors manufacturing technology. See Display Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
2 NVIDIA&reg; 3D Vision&trade;. Some individuals may experience health-related complications when exposed to certain 3D images. Parents should monitor and/or supervise their children's use of 3D Vision. If you or your child should experience any problem, immediately stop using 3D Vision and consult a physician. Carefully read safety instructions included with the NVIDIA 3D Vision kit and take the "User Vision Test" in the setup wizard before you or your child use 3D Vision. If you see excessive flicker in your peripheral vision, change the refresh rate of your display.<br />
3 Blu-ray Disc&trade; Player Technology. Compatibility and/or performance issues are possible. See Blu-ray Disc Player Technology Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
4 Playback of 3D Blu-ray content will require future driver and software upgrades from NVIDIA and Toshiba, which can be downloaded via the web at http://support.toshiba.com.<br />
5 Processor. CPU performance may vary. See Processor Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
6 Graphics. GPU performance may vary. See Graphics Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
7 Memory. Memory size may vary. See Memory Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
8 Hard Disk Drive Capacity. Hard drive capacity may vary. 1 Gigabyte (GB) means 109 = 1,000,000,000 bytes using powers of 10. See Hard Disk Drive Capacity Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
9 Wireless-N. The wireless adapter is based on a draft release version of the IEEE 802.11n specification and may not be compatible with, or support all features (e.g., security) of, certain Wi-Fi&reg; equipment.<br />
10 Wireless. May require purchase of additional software, external hardware, or services. Transmission speeds may vary. See Wireless Legal Footnote at <a target="_blank" href="http://www.info.toshiba.com/">www.info.toshiba.com</a><br />
11 The "USB Sleep &amp; Charge function" may not work with certain external devices even if they are compliant with the USB specification. In those cases, turn the power of the computer ON to charge the device.<br />
12 Price. Reseller prices may vary. MSRP means "Manufacturer's Suggested Retail Price."</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 15, 2010  6:27 AM</b>
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
			<?=getComments(3793)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3793)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/06/toshiba-satellite-a665-3dready-laptop-brings-new-dimension-to-entertainment.php" type="text/javascript" charset="utf-8"></script>
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