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
		AND e.entry_id = 4144";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4144 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4144 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4144";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/sony-redefines-television-with-new-bravia-lcd-hdtv-line.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4144";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony Redefines Television With New BRAVIA LCD HDTV Line" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony Redefines Television With New BRAVIA LCD HDTV Line" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony Redefines Television With New BRAVIA LCD HDTV Line" />
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
	<title>HDTV Magazine - Sony Redefines Television With New BRAVIA LCD HDTV Line</title>
	<meta name="keywords" content="inch kdl, picture quality, high definition, internet video, screen sizes, bravia, sony, inch, video, kdl, models, picture, new, internet, quality, led, full, hdtv, engine, screen, backlight, reality, include, skype, series" />
	<meta name="description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- (CES BOOTH #14200) --&lt;/strong&gt; Sony's new BRAVIA&amp;reg; HDTV lineup is redefining the television category again. Building on its broad line of innovative 3D and Internet connected TVs, Sony's 2011 BRAVIA LCD HDTV line includes 16 new 3D capable models and 22 Internet connected models. In all, the line features 27 new models ranging in screen size from 22 inches to 65 inches (measured diagonally)...." />
	<meta name="title" content="Sony Redefines Television With New BRAVIA LCD HDTV Line" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony Redefines Television With New BRAVIA LCD HDTV Line" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/sony-redefines-television-with-new-bravia-lcd-hdtv-line.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;strong&gt;LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- (CES BOOTH #14200) --&lt;/strong&gt; Sony's new BRAVIA&amp;reg; HDTV lineup is redefining the television category again. Building on its broad line of innovative 3D and Internet connected TVs, Sony's 2011 BRAVIA LCD HDTV line includes 16 new 3D capable models and 22 Internet connected models. In all, the line features 27 new models ranging in screen size from 22 inches to 65 inches (measured diagonally)...." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4144', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/sony-redefines-television-with-new-bravia-lcd-hdtv-line.php">Sony Redefines Television With New BRAVIA LCD HDTV Line</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=367&category=LCD HDTVs">LCD HDTVs</a></b>
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
				<p class="prtitle">Sony Redefines Television With New BRAVIA LCD HDTV Line</p>

<center><i>New Models Drive Innovation With Unprecedented Access To Internet Content, Skype, 3D, and Striking Industrial Design</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 5, 2011 /PRNewswire/ -- (CES BOOTH #14200) --</strong> Sony's new BRAVIA&reg; HDTV lineup is redefining the television category again. Building on its broad line of innovative 3D and Internet connected TVs, Sony's 2011 BRAVIA LCD HDTV line includes 16 new 3D capable models and 22 Internet connected models. In all, the line features 27 new models ranging in screen size from 22 inches to 65 inches (measured diagonally).</p>

<p>"Sony's BRAVIA televisions deliver a complete entertainment experience – from instant access to online video content to the immersiveness of 3D theatrical features, sports, and video games, we're redefining the form and function of television," said Chris Fawcett, vice president for Sony's television business. "While innovative features are important, rest assured that Sony's hallmark picture quality and unmatched industrial design remains intact, making BRAVIA the perfect centerpiece for any home decor."</p>

<p><br />
<strong>Lean Back and Surf</strong></p>

<p>Delivering the entertainment you want, when you want it, the 2011 BRAVIA line offers 22 models that deliver instant access to online entertainment. Whether connecting to a home broadband network via Wi-Fi (802.11n) or Ethernet connection, compatible models offer access to over 40 free and premium movies, video, and music services through the BRAVIA Internet Video platform including Sony's Video On Demand and Music Unlimited powered by Qriocity&trade;, as well as Netflix&trade;, Pandora&reg;, HuluPlus&trade;, Amazon Video on Demand, YouTube&trade;, Slacker&reg; Internet Radio, Crackle, and Blip.tv.</p>

<p>Video On Demand powered by Qriocity is a premium video streaming service with instant access to Hollywood blockbusters, and Music Unlimited powered by Qriocity is a cloud-based, streaming music service which gives music lovers access at anytime to a constantly expanding catalog of over six million global music tracks from major labels.</p>

<p>A number of the new BRAVIA models will also have Skype&trade; embedded. After connecting the Sony camera and microphone (CMU-BR100, sold separately) users can enjoy free widescreen Skype-to-Skype video calls with friends and family from the comfort of their living rooms. Users can also make voice calls while simultaneously watching TV.</p>

<p>The new connected models also make certain users will never have to wonder what that song playing during their favorite movie, TV program, or commercial is again. Sony's new Track ID powered by Gracenote, analyzes any selected song playing back on the TV, identifies it, and provides artist, album, and song information.</p>

<p><br />
<strong>Expanding 3D</strong></p>

<p>The 2011 BRAVIA line includes 16 new 3D models that deliver outstanding performance with Sony's active shutter technology. Building on last year, the new line offers affordable 3D entry models all the way up to Sony's top-of-the-line flagship models that pack best-in-class picture quality with every feature for the complete entertainment experience.</p>

<p>Improvements to this year's 3D models include panel drive enhancements that deliver quicker response time helping to reduce crosstalk, 5:5 pull down for a more realistic cinematic movie experience, and built-in 3D emitters on all 3D models.</p>

<p>Expanding the 3D content users can view, the new BRAVIA 3D capable models can display 3D images taken with Sony's new Cyber-shot cameras like the DSC-WX9 via USB input.</p>

<p><br />
<strong>Driving Picture Quality</strong></p>

<p>From full HD 1080p Blu-ray Disc, to broadcast HD, to user-generated Internet Video content, processing of incoming content is essential for an excellent picture. To this end, Sony has upgraded the BRAVIA processing power with the new X-Reality PRO and X-Reality Engine digital video processors.</p>

<p>The X-Reality PRO Engine is a two-chip digital video processor that optimizes high definition content, compressed HD signals, standard definition, and other sources including low resolution Internet content. Utilizing a vast database of "ideal" signal patterns developed by analyzing and indexing and enormous library of film and video, the X-Reality PRO Engine compares incoming signals pixel-by-pixel with ideal scenes to display vivid and detailed images.</p>

<p>Additionally, the engine's multi-frame analysis helps create missing resolution and Sony's Super Bit Mapping technology incorporates 14 bit up-scaling, processing and pixel mapping to smooth gradation and improve the quality of low-tone pictures.</p>

<p>Found in select models, the single chip X-Reality Engine utilizes Sony's Intelligent Image Enhancer technology to deliver outstanding picture quality. Incoming video is separated into constituent parts (outline, texture, and color/contrast), and the appropriate image enhancement is added to each part. The engine also uses Intelligent MPEG Noise Reduction to automatically detect the incoming sources and noise level to apply the appropriate amount of noise reduction for each scene.</p>

<p>Also new for select models is Sony's Intelligent Peak LED Backlight which uses full array local dimming for deeper blacks, while also boosting brightness in lighter scenes creating incredible on-screen contrast, while Sony's OptiContrast panel elevates the picture to the front surface of the TV and creates a dark background for rich, vibrant, high contrast picture even in well-lit rooms.</p>

<p>Sony's MotionFlow&trade; XR builds on previous versions of the company's motion compensation technology and helps to reduce blur caused by quick camera movements, enhancing sharpness and creating a clearer picture.</p>

<p><br />
<strong>Simplifying User Interface and Functionality</strong></p>

<p>Select new BRAVIA models feature Sony's improved user interface that delivers uninterrupted viewing and intuitive navigation. Retaining the intuitive operation of the award-winning Xross Media Bar (XMB&trade;), the new UI provides uninterrupted viewing by disseminating the video and placing key menu functions and favorites along the bottom and side of the picture frame.</p>

<p>Additionally, the models feature an improved remote control and offer control via the Media Remote application on iPhone, iPod Touch or Android mobile device. The app, which can be downloaded from the Apple App Store or the Android Marketplace, functions as a full remote with keyboard, allows online content search, and playback.</p>

<p><br />
<strong>BRAVIA XBR-HX929 Series 3D Capable HDTV</strong></p>

<p>Available in April, Sony's new 3D capable (with the addition of Sony active shutter glasses, sold separately), XBR-HX929 series features brilliant full HD (1080p) picture quality with Sony's Intelligent Peak LED backlight for outstanding contrast.</p>

<p>The model also features Sony's MotionFlow XR 960 featuring a precise backlight control that is synchronized with the liquid crystal movement from frame to frame creating clearer, sharper moving images.</p>

<p>The beautifully designed model features Sony's improved Monolithic Design Concept that draws viewers into the picture when the TV is turned on, and blends into the room decor when turned off. The 2011 Monolithic Design BRAVIA models incorporate Corning&reg;'s Gorilla&reg; Glass allowing for thinner, lighter, and stronger screen material.</p>

<p>With integrated Wi-Fi (802.11n), the XBR-HX929 models offer instant access to thousands of Internet movies, videos and music from BRAVIA Internet Video and Qriocity. Sony's Media Remote app provides simple operation and search for internet content.</p>

<p>The XBR-HX929 models are also custom installation friendly with key features such as rear IR-input and a two-way RS232c connection for easier integration with third-party control systems.</p>

<p>Other XBR-HX929 Series features include:</p>

<p>    Brilliant Full HD (1080p) picture quality with full-array local dimming LED backlight<br />
    Sleek Monolithic Design with OptiContrast Panel Technology<br />
    Integrated Wi-Fi (802.11n)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity<br />
    X-Reality&trade; PRO Engine<br />
    MotionFlow&trade; XR 960<br />
    Rear two-way IR inputs and two-way RS232C control<br />
    Screen sizes include 65-inch (XBR-65HX292, which is available in August 2011), 55-inch (XBR-55HX929), and 46-inch (XBR-46HX929)</p>

<p><br />
Other new BRAVIA models include:</p>

<p>BRAVIA&reg; HX820-Series LED LCD 3D Capable HDTV</p>

<p>Available in May</p>

<p>    Brilliant Full HD (1080p) picture quality with Dynamic Edge LED backlight<br />
    Sleek Monolithic Design with OptiContrast Panel Technology<br />
    Integrated Wi-Fi&trade; (802.11n)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity&trade;<br />
    X-Reality&trade; PRO Engine<br />
    MotionFlow&trade; XR 480<br />
    Screen sizes include 55-inch (KDL-55HX820), and 46-inch (KDL-46HX820)</p>

<p><br />
BRAVIA&reg; NX720-Series LED LCD 3D Capable HDTV</p>

<p>Available in May</p>

<p>    Brilliant Full HD (1080p) picture quality with Dynamic Edge LED backlight<br />
    Sleek Monolithic Design with OptiContrast Panel Technology<br />
    Integrated Wi-Fi&trade; (802.11n)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity&trade;<br />
    MotionFlow&trade; XR 240<br />
    X-Reality&trade; Engine<br />
    Screen sizes include 60-inch (KDL-60NX720), 55-inch (KDL-55NX720), and 46-inch (KDL-46NX720)</p>

<p><br />
BRAVIA&reg; HX729-Series LED LCD 3D Capable HDTV</p>

<p>Available in May</p>

<p>    Brilliant Full HD (1080p) picture quality with Dynamic Edge LED backlight<br />
    X-Reality&trade; PRO Engine<br />
    MotionFlow&trade; XR 480<br />
    Integrated Wi-Fi&trade; (802.11n)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity&trade;<br />
    Rear two-way IR inputs and two-way RS232C control<br />
    Screen sizes include 65-inch (KDL-65HX729), 55-inch (KDL-55HX729), and 46-inch (KDL-46HX729)</p>

<p><br />
BRAVIA&reg; EX720-Series LED LCD 3D Capable HDTV</p>

<p>Available in February</p>

<p>    Brilliant Full HD (1080p) picture quality with Edge LED backlight<br />
    X-Reality&trade; Engine<br />
    MotionFlow&trade; XR 240<br />
    Wi-Fi&trade; ready (USB wireless LAN adapter sold separately)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity&trade;<br />
    Screen sizes include 60-inch (KDL-60EX720, which is available in April), 55-inch (KDL-55EX720), 46-inch (KDL-46EX720), 40-Inch (KDL-40EX720), and 32-inch (KDL-32EX720)</p>

<p><br />
BRAVIA&reg; EX620-Series LED LCD HDTV</p>

<p>Available in February</p>

<p>    Brilliant Full High Definition (1080p) picture quality with Edge LED backlight<br />
    X-Reality&trade; Engine<br />
    MotionFlow&trade; 120<br />
    Wi-Fi&trade; ready (USB wireless LAN adapter sold separately)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity&trade;<br />
    Screen sizes include 55-inch (KDL-55EX620), 46-inch (KDL-46EX620), and 40-Inch (KDL-40EX620)</p>

<p><br />
BRAVIA&reg; EX520-Series LED LCD HDTV</p>

<p>Available in February</p>

<p>    Brilliant Full HD (1080p) picture quality with Edge LED backlight<br />
    X-Reality&trade; Engine<br />
    Wi-Fi&trade; ready (USB wireless LAN adapter sold separately)<br />
    Skype&trade; ready in 720p high definition<br />
    BRAVIA Internet Video and Qriocity&trade;<br />
    Energy saving Presence Sensor minimize power consumption<br />
    Screen sizes include 46-inch (KDL-46EX520), 40-inch (KDL-40EX520), 32-inch (KDL-32EX520)</p>

<p><br />
BRAVIA&reg; BX420-Series HDTV</p>

<p>Available in February</p>

<p>    Brilliant Full HD (1080p) picture quality with CCFL backlight<br />
    Five HD Inputs to connect multiple HD devices<br />
    USB input for photo, music and video playback<br />
    Scene select customized picture and sound settings<br />
    Ambient Light Sensor<br />
    Screen sizes include 46-inch (KDL-46BX420), 40-inch (KDL-40BX420), and 32-inch (KDL-32BX420)</p>

<p><br />
BRAVIA&reg; BX320-Series HDTV</p>

<p>Available in February</p>

<p>    Amazing High Definition (720p) picture quality with CCFL backlight<br />
    Five HD Inputs to connect multiple HD devices<br />
    USB input for photo, music and video playback<br />
    Scene select customized picture and sound settings<br />
    Ambient Light Sensor<br />
    Screen sizes include 32-inch (KDL-32BX320), and 22-inch (KDL-22BX320)</p>

<p><br />
For further details and pre-orders, please visit <a target="_blank" href="http://www.sony.com/bravia/">www.sony.com/bravia</a> or Sony Style retail stores across the country.</p>

<p>To learn about the 3D world created by Sony, please visit <a target="_blank" href="http://www.sony.net/united/3D/">www.sony.net/united/3D</a>.</p>

<p>SOURCE Sony Electronics Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2011 10:10 PM</b>
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
			<?=getComments(4144)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4144)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/sony-redefines-television-with-new-bravia-lcd-hdtv-line.php" type="text/javascript" charset="utf-8"></script>
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