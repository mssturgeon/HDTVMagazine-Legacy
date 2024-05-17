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
		AND e.entry_id = 3913";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3913 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3913 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3913";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/08/denon-celebrates-its-100th-anniversary-with-debut-of-special-edition-product-collection.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3913";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection" />
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
	<title>HDTV Magazine - Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection</title>
	<meta name="keywords" content="sound quality, home entertainment, product collection, special anniversary, denon electronics, denon, anniversary, audio, products, quality, high, sound, special, company, home, performance, technology, srp, collection, product, technologies, years, first, passion, features" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/denon-100.jpg&quot; alt=&quot;100th Anniversary Denon&quot; height=&quot;70&quot; width=&quot;78&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;&lt;strong&gt;MAHWAH, N.J.--(BUSINESS WIRE)--&lt;/strong&gt;Denon Electronics, one of the world's leading manufacturers of high-quality home entertainment products celebrating 100 years of innovation and technology leadership in 2010, is proud to announce the debut of its special-edition Anniversary Product Collection (A100), seven new home entertainment components that offer discriminating consumers an opportunity to become a part of Denon's century-old legacy of innovation and craftsmanship. The limited special-edition Denon Anniversary Product Collection includes..." />
	<meta name="title" content="Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/08/denon-celebrates-its-100th-anniversary-with-debut-of-special-edition-product-collection.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/denon-100.jpg&quot; alt=&quot;100th Anniversary Denon&quot; height=&quot;70&quot; width=&quot;78&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;&lt;strong&gt;MAHWAH, N.J.--(BUSINESS WIRE)--&lt;/strong&gt;Denon Electronics, one of the world's leading manufacturers of high-quality home entertainment products celebrating 100 years of innovation and technology leadership in 2010, is proud to announce the debut of its special-edition Anniversary Product Collection (A100), seven new home entertainment components that offer discriminating consumers an opportunity to become a part of Denon's century-old legacy of innovation and craftsmanship. The limited special-edition Denon Anniversary Product Collection includes..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3913', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/08/denon-celebrates-its-100th-anniversary-with-debut-of-special-edition-product-collection.php">Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August 24, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle"> Denon Celebrates Its 100th Anniversary With Debut of Special Edition Product Collection</p>

<center><i>-- Seven Elegant New Limited Edition Home Entertainment Components Reflect the Passion, Artistry and Technology of Denon's First 100 Years --</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/denon-100.jpg" alt="100th Anniversary Denon" height="70" width="78" class="keyimg"><strong>MAHWAH, N.J.--(BUSINESS WIRE)--</strong>Denon Electronics, one of the world's leading manufacturers of high-quality home entertainment products celebrating 100 years of innovation and technology leadership in 2010, is proud to announce the debut of its special-edition Anniversary Product Collection (A100), seven new home entertainment components that offer discriminating consumers an opportunity to become a part of Denon's century-old legacy of innovation and craftsmanship. The limited special-edition Denon Anniversary Product Collection includes the PMA-A100 Integrated Amplifier (SRP: $2,499); DCD-A100 CD/SACD Player (SRP: $2,499); DP-A100 Direct-Drive Turntable (SRP: $2,499); DL-A100 Cartridge (SRP: $499); AVR-A100 9.2 Channel A/V Receiver (SRP: $2,499); DBP-A100 Universal Blu-ray Player (SRP: $2,499); and AH-A100 Over-Ear Headphones (SRP: $499). All A100 products will be available beginning in November 2010 at select Special Anniversary Denon dealers, who can be found at <a target="_blank" href="http://www.Denon100.com/">www.Denon100.com</a>.</p>

<p><br />
<strong>Three Driving Constants</strong></p>

<p>For Denon, audio and video are not a hobby. The creation of products that can deliver the highest quality experience to the consumers is a driving passion and the reason the company exists. Denon is passionate about developing and leveraging technologies, so that in any format, we can bring purity of sound quality and joy to the customers' lives. The products will reproduce at home the same air, energy and the sensitivity felt at the stage or in the studio.</p>

<p>Denon engineers and employees are musicians and music lovers. They dream, imagine and work across a broad canvas of tools and technologies to design the most innovative new products. They also strive to create products of beauty and desire to reflect the taste and sensibilities of the future owners, to perfectly add to their living rooms and lifestyles. Everybody at Denon is dedicated to the artistry and the creativity of the products.</p>

<p>From the beginnings of compact disc technology, to the rise of surround sound, and now to mobility, streaming, and networking, Denon has not only evolved with technologies, but it has been the leader and innovator that has developed and introduced these technologies to the world and made it accessible to consumers. Denon is proud of their products and wants their customers to be proud as well.</p>

<p>All Denon A100 anniversary products come in a special carton with, for the first time in Denon's history, a five-year warranty and signed certificate of authenticity from the chief Denon production engineer who handcrafted the product. Also included is a richly detailed Denon "brand book" that explores the company's long and storied history. Each A100 component is finely tuned and reflects the commitment to passion, artistry and technology that has defined Denon in its first 100 years.</p>

<p>Noted Jeff Talmadge, Director, Product Development &amp; Systems Integration, Denon Electronics: "The launch of our A100 collection is a tribute to Denon's century long mission: to faithfully reproduce sound and images exactly as the original artist intended, with products that reflect 100 years of passion, artistry and technology. In 100 years of significant and innovative 'firsts,' Denon has continuously brought the best of technologies to discriminating customers around the world, always providing them with a superior home entertainment experience. With these new Anniversary Collection products, we are giving people who appreciate what Denon is all about a unique opportunity to share in our passion and enjoy true high-performance home entertainment at the same time."</p>

<p><br />
<strong>Where Past and Future Meet</strong></p>

<p>The Denon PMA-A100 is an integrated amplifier that echoes the company's many years of audio technology development and innovation. To enhance sound quality, the amplifier inherits Denon's UHC-MOS Single Push-Pull output circuit and features newly engineered construction, forming a solid foundation for pure, clean sound quality. This high-performance component includes special anniversary tuning, superior construction and parts, including a larger, upgraded speaker terminal, cast iron footing for less vibration and higher sound quality.</p>

<p>The PMA-A100 is designed to perform seamlessly in an integrated home entertainment system with its anniversary partner, the DCD-A100 CD/SACD player. Featuring Advanced AL32 Processing, a highly accurate master clock and the latest 32Bit/192 kHz DA converters to dramatically boost the quantity of digital audio information and ensure audio accuracy, the DCD-A100 also includes a new advanced S.V.H. drive mechanism that guarantees accurate and high quality playback of SACDs and CDs. It is equipped with a full complement of digital input ports, including a USB port for connecting an iPod or USB memory. Like the PMA-A100, the DCD features a 100th anniversary signature badge affixed to its black high-gloss front panel.</p>

<p><br />
<strong>A Unique History</strong></p>

<p>The history of Denon turntables began in 1910 with the production of Japan's first gramophone and peaked in 1939 when Denon developed a disc recorder for NHK radio station. In 1970, Denon developed its own high-torque AC motor for low speeds where speed was controlled by highly precise detection of magnetic pulses recorded around the perimeter of the platter. Denon incorporated this leading-edge servo technology in its development of the direct-drive turntable, whose high performance and reliability represented a clear break from conventional idler and belt drive products.</p>

<p>Denon's DP-A100 turntable features the same high-performance Denon direct-drive turntable technology that has delivered high-precision rotation for 40 years. Equipped with the DL-A100 cartridge, the DP-A100 is capable of masterfully reproducing the energy and beauty of analog records.</p>

<p>In 1961, Denon jointly produced the now iconic DL-103 cartridge with NHK for use at FM stations. The DL-103 was able to play stereo LP records with high fidelity and reliability (providing easy handling and stability). The basic design of this model continues to achieve first-rate performance and plays an active part in today's world of Hi-Fi audio enthusiasts. Reflecting this engineering expertise and innovation, the new DL-A100 cartridge represents the standard model that has been in production for a little less than a half century - now reborn with the latest tuning for pure enjoyment.</p>

<p><br />
<strong>First Name in Digital Audio</strong></p>

<p>The AVR-A100 9.2 Channel A/V surround receiver is the ultimate home theater hub for today's digital age. In addition to DENON Link 4th, featuring HDMI Clock Control, the AVR-A100 features high-bit i/p conversion and scaling, as well as advanced connectivity, network audio/photo streaming and a special interface to enhance the enjoyment of Internet content. It also includes a unique block condenser, a higher-grade speaker terminal with a gold plated inputs/outputs, and cast iron footing to stabilize sound quality.</p>

<p>The perfectly matching DBP-A100 Universal Blu-ray Player lets users not only enjoy the maximum performance of Blu-ray discs and DVDs but also Super Audio CDs, DVD-Audio discs and CDs - all with the very best in high-quality sound and video. This level of quality is made possible through the inclusion of Denon's vibration-resistant technologies including Direct Mechanical Construction and Multi-layer Chassis Structure. Since the DBP-A100 is also equipped with Denon Link 4th to thoroughly minimize jitter during HDMI transmission, users enjoy the best possible audio performance when combined with the AVR-A100. The DBP-A100 also features an anniversary edition coupling condenser and cast iron footing for sound stabilization.</p>

<p>Rounding out the Anniversary Collection line are Denon's luxurious AH-A100 headphones. Strikingly housed in a piano mahogany finish, the AH-A100 features a high-grade driver for superior sound quality, newly designed skin-soft ear pads and headband, as well as a luxurious storage case.</p>

<p><br />
<strong>Become Part of the Denon Anniversary Celebration</strong></p>

<p>Throughout the year, Denon will celebrate its 100th anniversary with exciting initiatives. To celebrate this milestone in its history, Denon is inviting everyone to join in the celebration through a special anniversary website, <a target="_blank" href="http://www.denon100.com/">www.denon100.com</a>. Visitors can learn all about the company's "Legacy of Firsts," and most importantly, the people whose passion for audio and video perfection have always driven the Denon brand. Those who register online will be first to be notified by Denon about Special Anniversary Denon dealers in their area.</p>

<p>For further information on Denon's 100th anniversary and other Denon news, please visit:</p>

<p>http://www.denon100.com, http://usblog.denon.com and http://usa.denon.com. Like Denon on Facebook at http://www.facebook.com/denonusa or follow Denon on Twitter at http://twitter.com/denonus.</p>

<p><br />
<strong>About Denon Electronics</strong></p>

<p>Denon celebrates its 100th Year Anniversary in 2010, carrying on its tradition of excellence with a renewed commitment to the highest quality home theater, audio and software products. Denon is recognized internationally for innovative and groundbreaking products and has a long history of technical innovations, including the development and groundbreaking commercialization of PCM digital audio. Denon Electronics is owned by D&amp;M Holdings Inc.</p>

<p><br />
<strong>About D&amp;M Holdings Inc.</strong></p>

<p>D&amp;M Holdings Inc. is a global operating company providing worldwide management and distribution platforms for premium consumer, automotive, commercial and professional audio and video businesses including Denon&reg;, Marantz&reg;, McIntosh&reg; Laboratory, Boston Acoustics&reg;, Snell Acoustics, Escient&reg;, Calrec Audio, Denon DJ, Allen &amp; Heath, D&amp;M Professional and D&amp;M Premium Sound Solutions. Our technologies improve the quality of any audio and visual experience. All product and brand names with a trademark symbol are trademarks or registered trademarks of D&amp;M Holdings, Inc. or its subsidiaries. For more information visit <a target="_blank" href="http://www.dm-holdings.com/">www.dm-holdings.com</a>.</p>

<p>DISCLAIMER</p>

<p>Statements in this news release that are not statements of historical fact may include forward looking statements regarding future events or the future financial performance of the company. We wish to caution you that such statements are just predictions and that actual events or results may differ materially. Forward looking statements involve a number of risks and uncertainties surrounding competitive and industry conditions, market acceptance for the company's products, risks of litigation, ability to meet targeted launch dates, technological changes, developing industry standards and other factors related to the company's businesses. The Company reserves all of its rights.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August 24, 2010  2:37 AM</b>
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
			<?=getComments(3913)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3913)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/08/denon-celebrates-its-100th-anniversary-with-debut-of-special-edition-product-collection.php" type="text/javascript" charset="utf-8"></script>
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