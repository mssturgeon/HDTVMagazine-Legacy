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
		AND e.entry_id = 4403";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4403 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4403 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4403";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/06/samsung-expands-its-ledlcd-leadership-at-infocomm-2011-with-the-introduction-of-four-new-product-lines.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4403";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines" />
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
	<title>HDTV Magazine - Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines</title>
	<meta name="keywords" content="samsung electronics, digital signage, led lcd, electronics america, ultra slim, samsung, electronics, series, led, digital, business, displays, display, lcd, bezel, ultra, technology, content, resolution, commercial, america, designed, company, slim, inc" />
	<meta name="description" content="Samsung Electronics America Inc., a subsidiary of Samsung Electronics Corporation and the number one worldwide brand of professional commercial display products* today announced from the Information Communication Marketplace Show in Orlando four new lines of LED/LCD commercial displays. The Samsung HE, ME, UE and UD Series incorporate LED-backlight technology that offers many benefits including overall image quality and lower power consumption.

All four product lines are on display at Samsung booth #1543 during InfoComm 2011 at the Orange County Convention in Orlando, FL, June 15-17 and available through Samsung distributors and resellers in Q3 2011..." />
	<meta name="title" content="Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/06/samsung-expands-its-ledlcd-leadership-at-infocomm-2011-with-the-introduction-of-four-new-product-lines.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Samsung Electronics America Inc., a subsidiary of Samsung Electronics Corporation and the number one worldwide brand of professional commercial display products* today announced from the Information Communication Marketplace Show in Orlando four new lines of LED/LCD commercial displays. The Samsung HE, ME, UE and UD Series incorporate LED-backlight technology that offers many benefits including overall image quality and lower power consumption.

All four product lines are on display at Samsung booth #1543 during InfoComm 2011 at the Orange County Convention in Orlando, FL, June 15-17 and available through Samsung distributors and resellers in Q3 2011..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4403', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/06/samsung-expands-its-ledlcd-leadership-at-infocomm-2011-with-the-introduction-of-four-new-product-lines.php">Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 15, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<p class="prtitle">Samsung Expands Its LED/LCD Leadership at InfoComm 2011 with the Introduction of Four New Product Lines</p>

<center><i>New LED/LCD Displays Offer Engaging, Dynamic Visual Experiences for Virtually all Commercial User Needs</center></i><br />
<br />

<p><strong>ORLANDO, Fla.--(BUSINESS WIRE)--</strong>Samsung Electronics America Inc., a subsidiary of Samsung Electronics Corporation and the number one worldwide brand of professional commercial display products* today announced from the Information Communication Marketplace Show in Orlando four new lines of LED/LCD commercial displays. The Samsung HE, ME, UE and UD Series incorporate LED-backlight technology that offers many benefits including overall image quality and lower power consumption.</p>

<p>All four product lines are on display at Samsung booth #1543 during InfoComm 2011 at the Orange County Convention in Orlando, FL, June 15-17 and available through Samsung distributors and resellers in Q3 2011.</p>

<p>"Samsung is building on a category we created at InfoComm 2010 by increasing the number of LED/ LCD solutions for many commercial user scenarios," said Kevin Schroll, product manager of commercial display Samsung Enterprise Business Division. "Organizations need display solutions which not only offer superior image quality but are also flexible enough to work in almost any environment. LED/LCDs work for everyone, from the small business owner to retail spaces and mission-critical applications."</p>

<p><br />
<strong>Next Generation LED Technology</strong></p>

<p>The HE, ME, UE and UD Series all incorporate LED backlight technology, which provides many benefits over traditional Cold Cathode Fluorescent Lamp (CCFL) backlighting.</p>

<p>These displays deliver uniform brightness levels with lower power consumption. With the ME and UE Series benefitting from a high 120Hz and 240Hz refresh rate, respectively, there is smoother scrolling text and reduced motion blur in digital signage messaging. With native contrast ratios of up to 5,000:1, the LED/LCD displays produce more realistic images.</p>

<p>The technology allows for reduced energy consumption ranging from 30-50 percent compared to conventional CCFL-backlit LCD displays, depending on model. In addition, the displays deliver lower total cost of ownership by combining energy savings with reductions in carbon emissions.</p>

<p><br />
<strong>Unique Design</strong></p>

<p>As a Samsung standard, all models are stunningly designed to offer not only long-lasting and reliable solutions, but long-lasting impressions as well. The displays feature ultra-thin bezels, making displays easier to hang in more locations than ever before. The bezels integrate into the overall design and feature more advanced cooling technologies, designed for long hours of continuous use.</p>

<p>Samsung MagicInfo Lite software comes included on select models. Simply plug in a USB thumb drive at the I/O panel and users can schedule presentations, images and video content in just a few minutes.</p>

<p>In addition, the ultra-slim 1.2-inch chassis depth provides a significant weight savings of up to 35-40 percent compared to traditional CCFL backlit displays making them easier to install. This removes the need for customers to get their walls reinforced in order to sustain the weight of the screen making it possible to place screens in more locations than were previously possible.</p>

<p><br />
<strong>Key Product Highlights</strong></p>

<p><u>HE Series</u></p>

<p>The HE Series (HE40A, HE46A) is designed for moderate-use commercial applications such as lobbies or small business locations like medical/dental offices. Both the HE40A and HE46A feature a built-in TV tuner in an easy-to-install, attractively designed, high-resolution display. They also feature commercial-level reliability and a three-year warranty.</p>

<p>40 and 46-inch size classes<br />
1920 x 1080 resolution<br />
5000:1 contrast ratio<br />
Integrated TV tuner (requires cable/satellite feed)<br />
178 - degree horizontal and vertical viewing angles<br />
HDMI connectivity<br />
Built-in 10W x 2 stereo speakers<br />
Thin side bezel of 13.7mm (0.53") on HE40A, 14.6mm (0.57") on HE46A<br />
MagicInfo Lite for easy plug-and-play digital signage content playback through USB<br />
Street price<br />
HE40A - $1,051<br />
HE46A - $1,402</p>

<p><u>ME Series</u></p>

<p>The ME Series (ME40A, ME46A and ME55A) is designed for the digital signage market, focusing on business customers looking to reduce their energy spend and total cost of ownership as well as reduced time/cost of installations. It delivers a high-resolution display and integrated TV tuner, with easy content control through RS232C and RJ45 ports. DisplayPort makes connectivity simple, and the ultra-slim chassis with slim bezel design will look attractive anywhere you want your message to be seen.</p>

<p>40, 46 and 55-inch size classes<br />
Integrated TV tuner (requires cable/satellite feed)<br />
Content control through RS232C and RJ45<br />
Thin bezels of 13.7mm (0.53") on ME40A, 14.6mm (0.57") on ME46A, 14.7mm (0.57") on ME55A<br />
Ultra-slim 29.9mm (1.2") chassis<br />
1920 x 1080 HD resolution<br />
5000:1 contrast ratio<br />
178-degree horizontal and vertical viewing angles<br />
DisplayPort and HDMI connectivity<br />
MagicInfo Lite for easy plug-and-play digital signage content playback through USB<br />
Built-in 10W x 2 stereo speakers<br />
Street price<br />
ME40A - $1,227<br />
ME46A - $1,578<br />
ME55A - $2,338</p>

<p><u>UE Series</u></p>

<p>The UE Series (UE46A and UE55A) is created for customers looking to deploy an affordable videowall solution that provides a near-seamless device while taking advantage of the benefits of LED technology. Screen quality is outstanding, with 1920 x 1080 resolution, ultra-fast 240Hz panel refresh rates and Samsung picture technology. Displays are also 3D-capable (with optional 3D glasses).</p>

<p>46 and 55-inch size classes<br />
Ultra-fast 240Hz panel refresh rate and 4ms response time<br />
Thin bezels of 5.2mm (0.2") or 10.3mm (0.4") bezel-to-bezel on UE 46A, 5.1mm (0.2") or just 10.0mm (0.39") bezel-to-bezel on UE55A<br />
Ultra-slim 29.9mm (1.2") chassis<br />
3D capable display (with optional 3D glasses)<br />
1920 x 1080 HD resolution<br />
5000:1 contrast ratio<br />
450 nits brightness<br />
178-degree horizontal and vertical viewing angles<br />
DisplayPort and HDMI connectivity<br />
MagicInfo Lite for easy plug-and-play digital signage content playback through SUB<br />
Built-in 10W x 2 stereo speakers<br />
Street price<br />
UE46A - $2,455<br />
UE55A - $3,507</p>

<p><u>UD Series</u></p>

<p>The UD Series (UD55A) is designed for customers looking to deploy mission-critical videowalls that can run 24/7 with super narrow bezels. With a width of just 5.5mm total from bezel to bezel, direct-lit backlight panels and advanced cooling, the UD Series will be virtually seamless and provide amazing video walls that simply cannot be ignored.</p>

<p>55-inch size class<br />
Ultra-slim 29.9mm (1.2") chassis<br />
1920 x 1080 HD resolution<br />
5000:1 contrast ratio<br />
700 nits brightness<br />
178-degree horizontal and vertical viewing angles<br />
DisplayPort and HDMI connectivity<br />
Content control through RS232C and RJ45<br />
Built-in 10W x 2 stereo speakers<br />
24/7 run time<br />
Street price<br />
UD55A - TBD<br />
All LED/LCDs are available through Samsung resellers and distribution channels, which can be located by calling 1-866-SAM-4BIZ or by visiting <a target="_blank" href="http://www.samsung.com/business/">www.samsung.com/business</a>. Samsung Power Partners receive special promotions, lead referrals, training and technical support, as well as collateral and marketing materials.</p>

<p>To find out more about becoming a Samsung Power Partner, visit <a target="_blank" href="http://www.samsungpartner.com/">www.samsungpartner.com</a>.</p>

<p><br />
<strong>About Samsung Electronics America Enterprise Business Division</strong></p>

<p>Based in Ridgefield Park, N.J., Samsung's Enterprise Business Division (EBD) is a division of Samsung Electronics America (SEA), a U.S. subsidiary of Samsung Electronics Company, Ltd. (SEC), the world's largest technology company based on revenue. As one of the fastest growing IT companies in the world, Samsung EBD is committed to serving the needs of consumers ranging from the home user to the Fortune 500 elite and supporting the valued channel partners who serve our customers. Samsung EBD offers a complete line of award-winning color and mono-laser printing solutions, desktop monitors, laptop computers, digital signage solutions and projectors. For more information, please visit <a target="_blank" href="http://www.samsung.com/business/">www.samsung.com/business</a> or call 1-866-SAM4BIZ.</p>

<p><br />
<strong>About Samsung Electronics America, Inc.</strong></p>

<p>Samsung Electronics America, Inc. (SEA), based in Ridgefield Park, NJ, is a subsidiary of Samsung Electronics Co., Ltd. The company markets a broad range of award-winning consumer electronics, information systems, and home appliance products, as well as oversees all of Samsung's North American operations including Samsung Telecommunications America, LP, Samsung Semiconductor Inc., Samsung Electronics Canada, Inc. and Samsung Electronics Mexico, Inc. As a result of its commitment to innovation and unique design, the Samsung organization is one of the most decorated brands in the electronics industry. The company was ranked #19 in BusinessWeek/Interbrand "100 Best Global Brands," and named as one of Fast Company's "50 Most Innovative Companies of 2010." For more information, please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a>. You can also Fan Samsung on <a target="_blank" href="http://www.Facebook.com/SamsungUSA/">www.Facebook.com/SamsungUSA</a> or follow Samsung via Twitter @SamsungTweets.</p>

<p>About Samsung Electronics Co., Ltd.</p>

<p>Samsung Electronics Co., Ltd. is a global leader in semiconductor, telecommunication, digital media and digital convergence technologies with 2010 consolidated sales of US$135.8 billion. Employing approximately 190,500 people in 206 offices across 68 countries, the company consists of eight independently operated business units: Visual Display, Mobile Communications, Telecommunication Systems, Digital Appliances, IT Solutions, Digital Imaging, Semiconductor and LCD. Recognized as one of the fastest growing global brands, Samsung Electronics is a leading producer of digital TVs, semiconductor chips, mobile phones and TFT-LCDs. For more information, please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a>.</p>

<p>*Market share information provided by DisplaySearch</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 15, 2011  2:39 PM</b>
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
			<?=getComments(4403)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4403)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/06/samsung-expands-its-ledlcd-leadership-at-infocomm-2011-with-the-introduction-of-four-new-product-lines.php" type="text/javascript" charset="utf-8"></script>
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