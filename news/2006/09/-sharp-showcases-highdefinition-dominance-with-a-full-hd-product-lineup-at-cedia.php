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
		AND e.entry_id = 447";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 447 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 447 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 447";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 447";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download  Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content=" Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content=" Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA" />
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
	<title>HDTV Magazine -  Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA</title>
	<meta name="keywords" content="home theater, high definition, front projector, contrast ratio, currently available, sharp, home, high, aquos, available, theater, models, projector, definition, screen, system, inch, room, model, front, lcd, dlp, full, contrast, technology" />
	<meta name="description" content="&lt;img src=&quot;/images/bulletins/sharp-aquos-lc-52d62u.jpg&quot; alt=&quot;Sharp AQUOS LC-52D62U&quot; align=&quot;left&quot;&gt;Sharp is rounding out the company's line of &quot;full HD&quot; home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's &quot;full HD&quot; product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature..." />
	<meta name="title" content=" Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content=" Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;/images/bulletins/sharp-aquos-lc-52d62u.jpg&quot; alt=&quot;Sharp AQUOS LC-52D62U&quot; align=&quot;left&quot;&gt;Sharp is rounding out the company's line of &quot;full HD&quot; home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's &quot;full HD&quot; product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=447', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php"> Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September 14, 2006</b>
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
				<p class="prtitle">Sharp Showcases High-Definition Dominance with a "Full HD" Product Lineup at CEDIA</p>

<p><b>DENVER--(BUSINESS WIRE) - Sept. 14, 2006</b> - New High-Definition AQUOS LCD TVs With Advanced Specifications and Competitive Pricing Plus a 1080p DLP Front Projector Headline Sharp's Elegant HD Home Entertainment Product Line 	</p>

<p><img src="/images/bulletins/sharp-aquos-lc-52d62u.jpg" alt="Sharp AQUOS LC-52D62U" align="left">Sharp is rounding out the company's line of "full HD" home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's "full HD" product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature 1080p resolution, dramatically enhanced contrast ratios and pixel response times that are among the fastest in the industry.</p>

<p>"High-definition is the future of entertainment, and we are proud to be bringing to market a complete line of 'full HD' display products that will enhance consumers' home entertainment experience," said Bob Scaglione, senior vice president and group manager, product and marketing Group, Sharp. "Sharp is committed to leading the high-definition products industry, and our CEDIA booth reflects that commitment."</p>

<p>With the addition of the AQUOS D62U line, Sharp offers full HD 1080p models in six screen sizes (37", 42", 46", 52", 57" and 65") - more than any other manufacturer. The company has unsurpassed LCD screen manufacturing capability, highlighted by its recently-opened state-of-the-art Generation 8 factory, Kameyama No. 2, which focuses solely on the creation of large-screen units. Kameyama No. 2 is the world's first and only 8th-generation LCD facility and will enable Sharp to build the most advanced flat-panel televisions in the world, and also help to meet the growing demand for competitively-priced large-screen high-definition LCD TVs in the U.S.</p>

<p>In addition to LCD TV, Sharp is further broadening its television line with an extensive line of high-definition front projectors that feature the award-winning Texas Instruments DLP technology. "Sharp's 'full HD' expertise extends beyond the flat-panel display category, and we are especially excited about the availability of our new 1080p flagship product, the XV-Z20000, which will change the front projection landscape," Scaglione continued.</p>

<p>Demonstrating the company's complete home theater prowess, Sharp's CEDIA 2006 booth will also feature a technology demonstration of the BD-MPC10, a multi-faceted Home Theater Studio System with Blu-Ray functionality and Time Domain Speaker Technology. The system consists of an AV receiver powered by Sharp's 1-Bit digital amplification operating at 11.2MHz sampling rate, a special pair of tower speakers with built-in Time Domain Speaker Technology, and a Blu-Ray high-definition player. Time Domain Speaker Technology produces a coherent and low distortion sound wave that results in true, crystal-clear sound. The BD-MPC10 also features a listener correction EQ system by Audyssey that adjusts sound for inconsistent room acoustics. Through an interface with the AQUOS Familink system, viewers can control the complete system with the push of a single button on an AQUOS TV's remote control.</p>

<p>For detailed information on Sharp's new products, please see the individual product announcements and fact sheets available for media.</p>

<p>AQUOS Widescreen 1080p Models</p>

<p>Sharp is expanding its wide range of AQUOS models with the introduction of three large-screen 1080p HDTV AQUOS Liquid Crystal Televisions, available in 42-, 46- and 52-inch screen sizes. The models produced at the new 8th-generation Kameyama plant (46- and 52-inch) will feature the latest version of Sharp's proprietary Advanced Super View panel for extraordinary LCD performance. This panel technology enables an incredible contrast ratio for deep blacks and crisp picture quality; enhanced Quick Shoot video circuitry for faster pixel response time; and wider viewing angles, so users can view the television from virtually anywhere in the room. The 46- and 52-inch models will also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, all units in this series include dual HDMI inputs, both of which are compatible with 1080p signals from Blu-ray devices. All three models will be available in October, the LC-42D62U for a Manufacturer's Suggested Retail Price (MSRP) of $2,499.99, the LC-46D62U for an MSRP of $3,499.99, and the LC-52D62U for an MSRP of $4,799.99.</p>

<p>Flagship DLP 1080p Home Theater Front Projector (model XV-Z20000):</p>

<p>Sharp brings home theater to the forefront with the new XV-Z20000, utilizing a single .95" DMD chip from Texas Instruments. This groundbreaking 1080p projector has a native resolution of "full HD" 1920 x 1080 for true 16:9 widescreen movie viewing, producing clear vivid images. The XV-Z20000 transforms any room into a high-tech home theater, using Sharp's CV-IC III Video Scaling Circuitry. DVI/HDCP (High Bandwidth Digital Content Protection) and two HDMI terminals ensure a secure digital connection with all high definition set top boxes. The XV-Z20000 will be available in October for an MSRP of $11,999.99.</p>

<p>AQUOS Widescreen HDTV Series (models LC-37D90U and LC-32D50U)</p>

<p>The D90U and D50U Widescreen Series of HDTV AQUOS Liquid Crystal Televisions, available in 37- and 32-inch screen sizes, feature Sharp's proprietary multi-pixel technology for extraordinary LCD performance. This technology enables contrast ratio of 1200:1, enhanced Quick Shoot video circuitry for 6 ms pixel response time and wide viewing angles (176 degrees), so users can view the television from virtually anywhere in the room. The 37-inch model features full 1080p (1920 x 1080) HDTV resolution, providing consumers with an unparalleled high-definition experience, and also includes Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than previously possible. The LC-32D50U has stellar 1366 x 768 resolution for viewing HD programming. Additionally, both units in this series include dual HDMI, HD component, DVI-I for PC compatibility and the DTVLink advanced digital interface. These elegantly-styled models are available in a titanium finish with detachable bottom speakers (model LC-37D90U) or fixed bottom speakers (model LC-32D50U). The LC-37D90U and LC-32D50U are currently available for MSRPs of $2,999.99 and $1,799.99, respectively.</p>

<p>AQUOS D40U Series (models LC-37D40U, LC-32D40U and LC-26D40U)</p>

<p>Widescreen 37-, 32- and 26-inch HDTV AQUOS D40U Liquid Crystal Televisions further bolster Sharp's unmatched selection of sophisticated designs and its superior-performing LCD TVs. This series features a contrast ratio of 1200:1, enhanced Quick Shoot video circuitry for 6 ms pixel response time and wide viewing angles (176 degrees), so users can view the television from almost anywhere in the room. This series features an elegant piano black finish with fixed bottom speakers and a detachable table stand for wall-mounting flexibility. With 1366 x 768 resolution and built-in ATSC/QAM/NTSC tuners, consumers can enjoy the latest HDTV programming. Models LC-37D40U, LC-32D40U and LC-26D40U are currently available for MSRPs of $2,299.99, $1,599.99 and $1,099.99, respectively.</p>

<p>AQUOS LC-20D30U</p>

<p>This 20-inch AQUOS is the only model in Sharp's extensive LCD TV lineup to offer true 16:9 widescreen aspect ratio and 1366 x 768 resolution for 720p HDTV compatibility in a 20-inch screen size. Perfect for a smaller or secondary television-watching space, the widescreen format allows movies to be seen in the aspect ratio originally intended by the director. The LC-20D30U provides outstanding picture quality with 800:1 contrast ratio, and multiple placement options with 170-degree viewing angles. In addition, the LC-20D30U includes HD compatibility, HDMI and PC compatibility, and features a black matte finish with black side accents, bottom speakers and a removable table stand, for a variety of placement options. Model LC-20D30U is currently available for an MSRP of $899.99.</p>

<p>AQUOS S5U Series (models LC-20S5U and LC-15S5U)</p>

<p>These 15- and 20-inch 4:3 AQUOS models are Enhanced Definition LCD TVs that feature 480p compatibility for viewing progressive DVDs. These models have fixed bottom speakers and a silver finish with black side trim that complements any decor. The S5U series includes Optical Picture Control (OPC) for automatic brightness adjustment to accommodate a room's lighting conditions; NTSC, PAL and SECAM video playback capability; HD compatible input; and a high brightness level. These models are currently available for MSRPs of $749.99 and $549.99, respectively.</p>

<p>Home Theater DLP Front Projector (model XV-Z3000):</p>

<p>Sharp's portable DLP front projector, the SharpVision XV-Z3000, is a 720p high-definition home entertainment solution that instantly transforms any room into a high-tech home theater. This widescreen, portable projector can be carried throughout a home or to a friend's home to create an instant home theater for watching TV, viewing DVDs or playing computer games on a big screen. The XV-Z3000 features brightness (1200 ANSI Lumens) and contrast levels (6500:1) superior to those available in current front projectors, so consumers can enjoy excellent picture quality in almost any lighting conditions. Additionally, a dual-iris system adjusts image brightness to show full detail and enhances contrast ratio to compensate for varied lighting environments. The low fan noise of 30 dBA (in economy mode) ensures that the viewer won't miss a minute of the film's dialogue and special effects. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost, a 12 volt trigger and an HDMI interface. The XV-Z3000 is currently available for an MSRP of $3,499.99.</p>

<p>Home Theater DLP Front Projector (model DT-500):</p>

<p>The DT-500 high-definition DLP front projector is a stylish, feature-packed projector that is ideal for a dedicated home theater or any viewing room. This portable unit can be moved easily from room to room, for an instant home theater anywhere. Utilizing DLP technology from Texas Instruments, and with a resolution of 1280 x 768, the DT-500 produces a 4000:1 contrast ratio and a brightness rating of 1200 ANSI lumens, delivering one of the best pictures available in consumer home theater today. Weighing just 8.6 pounds, consumers can carry the projector to any room of the house to watch TV, DVD movies or play computer games on a big screen and then store the entire system in a cabinet to save space. A powered optical iris system instantly changes brightness and contrast settings with the push of a button to allow the greatest flexibility for varying home theater environments. Home theater convenience is further enhanced with easy installation and whisper-quiet operation. A 6 Segment 5 X Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction, resulting in an uninterrupted, detailed picture. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost and an HDMI interface. The DT-500 is currently available for an MSRP of $3,299.99.</p>

<p>Portable DLP Front Projector (model DT-100):</p>

<p>Weighing just over eight and a half pounds, this portable DLP front projector can be moved easily from room to room, for an instant big-screen theater anywhere in the home. Using DLP technology from Texas Instruments, this stylish, feature-packed projector is ideal for consumers to watch TV, DVDs or play computer games on a full-size screen and then pack it all up and put it away, saving space and avoiding clutter. The DT-100 provides consumers with a compact, lightweight product that will easily fit on a shelf, cabinet or small side table. The projector is EDTV (enhanced definition television), with a resolution of 854 x 480 that is high-definition compatible. Upgraded features include an extremely high contrast ratio of 2500:1 as well as 1000 ANSI Lumen brightness for brilliant clarity and a superior image. The low fan noise of 30 dBA (in economy mode) ensures that a film's dialogue and special effects are the only sounds that movie-watching guests will hear. The projector is outfitted with a 6 Segment 5 X Speed color wheel that minimizes "color breaking" and provides high quality images with accurate color reproduction. The DT-100 is currently available for an MSRP of $1,299.99.</p>

<p>Widescreen Liquid Crystal Television/DVD Combos (models LC-26DV20U and LC-20DV20U):</p>

<p>The Widescreen LC-26DV20U and LC-20DV20U LCD TVs provide a slim, versatile, all-in-one television and video solution. Both units feature HDMI and HD component inputs for high-definition compatibility when connected to a separate set-top box. A built-in progressive-scan DVD Player loads discs into the TV from the side, keeping the sleek appearance of the unit and creating a complete home theater solution. The DV20U Series provides a high contrast ratio (800:1) and wide viewing angles (170 degrees). The 26-inch model is an HDTV and includes built-in NTSC/ATSC/QAM tuners. The 20-inch model is an HDTV Monitor offering HD compatibility and PC connectivity. These televisions are silver and feature bottom-placed speakers that will complement any decor. The LC-26DV20U and LC-20DV20U are currently available for MSRPs of $1,049.99 and $899.99 respectively.</p>

<p>For more information on Sharp's full line of Liquid Crystal Televisions, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07430, or call 800-BE-SHARP. For online product information, visit sharpusa.com.</p>

<p>Sharp Electronics Corporation is the Mahwah, N.J.-based marketing and sales subsidiary of Japan's Sharp Corporation, a worldwide developer of the core technologies that are integral to shaping the next generation of home entertainment products, appliances, networked, multifunctional office solutions, solar energy and mobile communication and information tools. Leading brands include AQUOS(R) Liquid Crystal Televisions, 1-Bit(TM) digital audio products, SharpVision(R) projection products, Carousel(R) microwaves, IMAGER(TM) digital multifunctional systems, and Notevision(R) multimedia projectors. Sharp Electronics Corporation employs approximately 2,000 people throughout the U.S. supporting more than 50 product lines.</p>

<p>*Sharp won a 2004 Technology & Engineering Emmy(R) for Award for Development of Direct View Liquid Crystal Display Screens. Use of the trademarks and service marks of the National Television Academy, including the mark Emmy(R), requires the prior express written permission of the National Television Academy.</p>

<p>**Cable system must deliver HDTV programming. Consumers should check with their local cable company to determine available HDTV channels.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September 14, 2006  7:24 AM</b>
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
			<?=getComments(447)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 447)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/09/-sharp-showcases-highdefinition-dominance-with-a-full-hd-product-lineup-at-cedia.php" type="text/javascript" charset="utf-8"></script>
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