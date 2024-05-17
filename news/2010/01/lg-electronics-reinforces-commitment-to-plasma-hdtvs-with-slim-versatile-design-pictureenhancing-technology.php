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
		AND e.entry_id = 3491";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3491 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3491 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3491";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/01/lg-electronics-reinforces-commitment-to-plasma-hdtvs-with-slim-versatile-design-pictureenhancing-technology.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3491";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology" />
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
	<title>HDTV Magazine - LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology</title>
	<meta name="keywords" content="inch class, size inch, inch diagonal, class size, sub field, plasma, inch, electronics, picture, consumers, technology, new, series, models, home, class, quality, entertainment, content, options, diagonal, slim, size, design, mobile" />
	<meta name="description" content="LG Electronics underscored its commitment to the plasma HDTV category with eight new models for 2010, led by the new LG INFINIA series, which represents the best in LG's design, technology and entertainment options.

Featuring new picture-enhancing technology and lightweight designs in a variety of screen sizes, four new plasma series..." />
	<meta name="title" content="LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/01/lg-electronics-reinforces-commitment-to-plasma-hdtvs-with-slim-versatile-design-pictureenhancing-technology.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LG Electronics underscored its commitment to the plasma HDTV category with eight new models for 2010, led by the new LG INFINIA series, which represents the best in LG's design, technology and entertainment options.

Featuring new picture-enhancing technology and lightweight designs in a variety of screen sizes, four new plasma series..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3491', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/lg-electronics-reinforces-commitment-to-plasma-hdtvs-with-slim-versatile-design-pictureenhancing-technology.php">LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  7, 2010</b>
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
				<p class="prtitle">LG Electronics Reinforces Commitment to Plasma HDTVs With Slim, Versatile Design, Picture-Enhancing Technology</p>

<center><i>New Plasmas Punctuated by Slim Bezel Depth, Enhanced Connectivity</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 7 /PRNewswire/ -- </strong>LG Electronics underscored its commitment to the plasma HDTV category with eight new models for 2010, led by the new LG INFINIA series, which represents the best in LG's design, technology and entertainment options.</p>

<p>Featuring new picture-enhancing technology and lightweight designs in a variety of screen sizes, four new plasma series - the INFINIA PK950 and PK750, as well as the PK550 and PJ350- were introduced today at the International Consumer Electronics Show, (Booth #8205).</p>

<p><br />
<strong>Design and Picture Enhancement</strong></p>

<p>LG has completely redesigned its plasma line for 2010, resulting in plasma sets that are slimmer and lighter than ever before. At only around two-inches in depth, the 2010 models are approximately 37 percent thinner than previous models and dramatically lighter. In the most extreme case, the weight of a 60-inch plasma HDTV was reduced by more than 40 percent to a svelte 95 pounds.</p>

<p>LG's new plasma series includes stunning new designs, such as the single-layer design of the PK750 and PK950, and the "TruSlim Frame" on the PK550 and PJ350 models, which boasts a bezel width of just 0.9-inches. Wireless capability contributes to the freedom of infinite entertainment possibilities.</p>

<p>Setting a new standard in picture quality, the PK750 and PK950 models have achieved THX Display Certification*. These models also feature 600Hz Sub-Field Driving, a protective "Skin Glass," Dual XD Engine and a TruBlack Filter to provide consumers with superior picture quality in a bright room. The 600Hz Sub-Field Driving improves moving picture response time using a 10 sub-field per frame driving process, ultimately offering consumers a clearer picture.</p>

<p>The Protective Skin Glass system reduces the gap between the outer glass and filter by 70 percent, virtually eliminating double imaging and helping to reduce glare. LG's exclusive Dual XD Engine facilitates up-conversion of standard definition content to near high-definition quality, so consumers can enjoy improved image quality with everything they watch. It also provides extremely accurate color reproduction. The incorporation of the TruBlack Filter reduces ambient light reflection, providing consumers with better black levels and improving contrast for a sharper and more detailed picture in a bright room.</p>

<p>"Plasma TV technology is still considered the gold standard of picture quality by discerning home theater enthusiasts," said Tim Alessi, director of product development, LG Electronics USA. "LG's 2010 plasma line combines new technology, such as the TruBlack Filter and superior color accuracy, with striking new designs that are slimmer, narrower and lighter, demonstrating LG's commitment to innovation in the plasma category."</p>

<p><br />
<strong>Content and Connectivity</strong></p>

<p>The PK950* and PK750* plasma series boast a connectivity package with a variety of entertainment options including NetCast Entertainment Access(TM). With this NetCast feature, consumers can access an array of online content options**:<br />
<ul><li>Skype(TM): Newly added in 2010, this allows consumers to make free video and voice calls over the Internet to family members and friends (Separate camera and other equipment needed and sold separately).</li><li>Netflix(TM): Updated with Netflix 2.0, consumers can stream thousands of movies without a PC.</li><li>VUDU(TM): Allows consumers to instantly buy or rent from an extensive library of movies and TV titles, including a catalog of more than 2,000 high-definition movies - with no monthly fees or additional hardware.</li><li>YouTube(TM): Offers the ability to instantly stream millions of Web videos directly from the Internet (without a personal computer).</li><li>Napster(TM): Napster subscribers can now enjoy unlimited on-demand streaming music from millions of songs on their NetCast TV.</li><li>Yahoo! Widget Engine(TM): Enables access to various applications called TV Widgets that allow viewers to interact with popular Internet services and online media through applications specifically tailored to the needs of the watcher, such as up-to-the minute Yahoo! News, Weather and Finance, and new widgets, including CNBC, CBS and Showtime.</li></ul></p>

<p>LG Electronics has incorporated the Digital Living Network Alliance (DLNA) technology across the full line of plasma models. This allows consumers to access content stored on other DLNA-certified devices within the home, such as computers, making content options almost limitless.</p>

<p>Providing easy options for connecting to the Internet, in addition to the wired Ethernet jack, all NetCast-enabled sets can also integrate into a wireless home network by using a USB wireless broadband adaptor (sold separately). Both PK950 and PK750 models support multi-media playback from a connected USB device including photos (JPEG), music (MP3) and video (DivX HD).</p>

<p>For greater convenience and flexibility in setup and installation, all LG Plasma series with NetCast offer wireless Full HD 1080p transmission from a "Wireless Media Hub" from up to 98 feet. Connecting source components, such as Blu-ray Disc players, cable or satellite boxes and video games to the media hub enables transmission to a compact receiver adaptor which attaches to the back of the TV, hidden from view. This eliminates the need for individual components to be connected directly to the TV, making for a clean and easy installation and removal of unsightly wires (Media Hub and receiver adaptor sold separately as a package).</p>

<p><br />
<strong>Controls</strong></p>

<p>LG's PK950 series also incorporates a unique "Magic Wand" remote system that provides an immersive interaction with the set. This "Magic" user interface brings together menus, component controls and even embedded games which can be accessed using a simple remote that combines minimal buttons and gestures to control the on-screen activity, mirroring a "Wii-like" experience.</p>

<p><br />
<strong>Plasma Expansion</strong></p>

<p>A founding member of the Plasma Display Coalition, LG also introduced two other series of plasmas this week at CES. The PK550 and PJ350 models offer consumers a 1080p and 720p plasma option respectively and do not compromise on features or design. Both series boast LG's TruSlim Frame, reducing the bezel width to only 0.9 inches, along with a slim 2-inch depth and the protective Skin Glass system for picture quality improvement. This is in addition to the many value-added core features found on virtually all LG HDTV products.</p>

<p>The PK550 Full HD 1080p series (available in 50-and 60-inch class sizes*) features a TruSlim Frame and slim profile for a clean, modern look. It also incorporates 600Hz Sub-Field Driving, Intelligent Sensor technology for energy savings and USB 2.0 for additional content viewing options.</p>

<p>The 720p PJ350 series (available in 42- and 50-inch class sizes*) also offers consumers the TruSlim Frame and slim profile for a clean, modern look, as well as 600Hz Sub-Field Driving in two display sizes. The USB 2.0 feature and three HDMI ports allow consumers to connect to a variety of entertainment devices.</p>

<p><br />
<strong>Core Plasma Features</strong></p>

<p>For easy self-calibration, all plasma lines feature LG's Picture Wizard II technology, which provides on-screen reference points for key picture quality elements, such as black level, color, tint, sharpness and backlight levels. Also included in the new plasma lines are ISFccc calibration options to deliver the level of calibration filmmakers intended viewers to see. Others include:<br />
<ul><li>Intelligent Sensor: Automatically calibrates and optimizes brightness, contrast, white balance and color, based on the brightness and color temperature of lighting in the room - thereby saving on energy output in most circumstances.</li><li>Clear Voice II: An enhancement to Clear Voice, this feature customizes volume settings by 12 distinct voice zoom levels without diminishing other surrounding sounds, helping ensure consumers don't miss a single line of dialogue during action sequences.</li><li>AV Mode II: Includes three AV modes preset to optimize picture and sound settings based on Cinema, Sports or Game content which can be easily set with the remote control.</il></ul></p>

<p>For more information and product images, please visit LG's online press kit at <a target="_blank" href="http://www.lgusa.com/cespressroom/">www.lgusa.com/cespressroom</a></p>

<p><br />
<strong>About LG Electronics USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances and business solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.LGusa.com/">www.LGusa.com</a>.</p>

<p><br />
<strong>About LG Electronics, Inc.</strong></p>

<p>LG Electronics, Inc. is a global leader and technology innovator in consumer electronics, mobile communications and home appliances, employing more than 84,000 people working in 115 operations including 84 subsidiaries around the world. With 2008 global sales of $44.7 billion, LG comprises of five business units - Home Entertainment, Mobile Communications, Home Appliance, Air Conditioning and Business Solutions. LG is one of the world's leading producers of flat panel TVs, audio and video products, mobile handsets, air conditioners and washing machines. LG has signed a long-term agreement to become both A Global Partner of Formula 1(TM) and A Technology Partner of Formula 1(TM). As part of this top-level association, LG acquires exclusive designations and marketing rights as the official consumer electronics, mobile phone and data processor of this global sporting event. For more information, please visit <a target="_blank" href="http://www.lge.com/">www.lge.com</a>.</p>

<p>  *PK950 60-inch class size/60-inch diagonal<br />
  *PK750 60-inch class size/60-inch diagonal<br />
  *PK550 60-inch class size/60-inch diagonal<br />
  *PK550 50-inch class size/50-inch diagonal<br />
  *PJ350 50-inch class size/50-inch diagonal<br />
  *PJ350 42-inch class size/42-inch diagonal</p>

<p>  * Specifications subject to change without notice.<br />
  * THX certification is pending final testing and approval by THX Ltd.</p>

<p>  ** Internet connection on subscriptions required and sold separately.</p>

<p>Source: LG Electronics USA, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  7, 2010  6:48 AM</b>
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
			<?=getComments(3491)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3491)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/lg-electronics-reinforces-commitment-to-plasma-hdtvs-with-slim-versatile-design-pictureenhancing-technology.php" type="text/javascript" charset="utf-8"></script>
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