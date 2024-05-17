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
		AND e.entry_id = 4552";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4552 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4552 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4552";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2011/10/passive-3dtv-brain-perception-an-excuse-for-technical-limitations.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4552";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Passive 3DTV Brain Perception - An Excuse for Technical Limitations?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Passive 3DTV Brain Perception - An Excuse for Technical Limitations?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Passive 3DTV Brain Perception - An Excuse for Technical Limitations?" />
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
	<title>HDTV Magazine - Passive 3DTV Brain Perception - An Excuse for Technical Limitations?</title>
	<meta name="keywords" content="active shutter, passive dtv, audio video, half resolution, per eye, quality, dtv, image, may, even, passive, people, audio, technology, images, most, video, active, resolution, shutter, half, eye, good, those, should" />
	<meta name="description" content="As you may know, LG continues their offense against active-shutter 3DTVs and claims that their passive-LCD-3DTVs are better because most people that viewed their sets during (their arranged) consumer tests in retail centers in New York, Chicago, Los Angeles, etc. preferred them over active-shutter 3DTVs.

Although the challenge seemed primarily targeted to the active-shutter technology itself, LG also launched aggressive advertising directed to the manufacturers of that technology as well, such as Samsung and Sony.
The battle of words, ads, and press releases has become..." />
	<meta name="title" content="Passive 3DTV Brain Perception - An Excuse for Technical Limitations?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Passive 3DTV Brain Perception - An Excuse for Technical Limitations?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2011/10/passive-3dtv-brain-perception-an-excuse-for-technical-limitations.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As you may know, LG continues their offense against active-shutter 3DTVs and claims that their passive-LCD-3DTVs are better because most people that viewed their sets during (their arranged) consumer tests in retail centers in New York, Chicago, Los Angeles, etc. preferred them over active-shutter 3DTVs.

Although the challenge seemed primarily targeted to the active-shutter technology itself, LG also launched aggressive advertising directed to the manufacturers of that technology as well, such as Samsung and Sony.
The battle of words, ads, and press releases has become..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4552', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2011/10/passive-3dtv-brain-perception-an-excuse-for-technical-limitations.php">Passive 3DTV Brain Perception - An Excuse for Technical Limitations?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 26, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=349&category=3D HDTV">3D HDTV</a></b>
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
				<p><div class="caption right" style="width:388px;"><img alt="LG's Consumer Tests in Retail Centers" src="http://www.hdtvmagazine.us/articles/images/cb0588d8bc38_13F2B/clip_image002_bb530f01-1b8a-4590-bd36-6be93564816d.jpg" width="386" height="219"><br />LG's Consumer Tests in Retail Centers</div>As you may know, LG continues their <a href="http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php">offense against</a> active-shutter 3DTVs and claims that their passive-LCD-3DTVs are better because most people that viewed their sets during (their arranged) consumer tests in retail centers in New York, Chicago, Los Angeles, etc. <a href="http://www.lg.com/us/press-release/article/lg-cinema-3d-hdtv-beats-competition-in-live-consumer-challenge.jsp">preferred them</a> over active-shutter 3DTVs. <p><div class="caption left" style="width:213px;"><img alt="USA Today - June 30, 2011" src="http://www.hdtvmagazine.us/articles/images/cb0588d8bc38_13F2B/clip_image004_0d900370-b018-44cf-80bd-922307839097.jpg" width="211" height="364"><br />USA Today - June 30, 2011</div>Although <a href="http://www.twice.com/article/471101-3D_Glasses_War_Rages_On_As_Vizio_And_LG_Defend_Passive_Tech.php">the challenge</a> seemed primarily targeted to the active-shutter technology itself, LG also launched <a href="http://hdguru.com/lg-fires-another-round-in-the-3d-format-war-hd-guru-analysis/5020/">aggressive advertising</a> directed to the manufacturers of that technology as well, such as <a href="http://www.hdguru3d.com/index.php?option=com_content&amp;view=article&amp;id=1136:let-the-battle-begin-between-samsung-and-lg-that-is&amp;catid=35:hdguru3d-news&amp;Itemid=59">Samsung</a> and <a href="http://www.hdguru3d.com/index.php?option=com_content&amp;view=article&amp;id=1444:lg-and-sony-argue-over-who-consumers-like-more&amp;catid=35:hdguru3d-news&amp;Itemid=59">Sony</a>. <p>The <a href="http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php">battle</a> of words, ads, and press releases has become rather ridiculous, and consumers and retailers are at a loss when things like this happen. <a href="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-1.php">Buying a 3DTV</a> should be a joyful and rewarding experience, but is rather turning into a take-a-side undertaking, throwing bananas in the zoo’s monkey house. <p>LG may have limited itself to offer <a href="http://www.hdtvmagazine.com/articles/2011/03/3dtv-technologies-which-one-for-you.php">an alternate technology</a> to those that may have issues with the active-shutter technology such as flicker and nausea (although that minority could also have issues accepting 3D in theaters as well), <a href="http://www.hdtvmagazine.com/articles/2011/03/3dtv-are-competing-technologies-necessary-including-autostereoscopic.php">or to offer a low cost 3D glasses alternative</a> to the relatively higher cost of the 3D active-shutter glasses, but the company appears to believe they can take over the whole 3DTV market (with their mediocre 3DTV, yes, I said that). <p>I cannot imagine Porsche attacking Ferrari in USA Today’s ads and tell the company to better stick to economy commuter cars or Dolby attacking DTS and tell the company to better stick with stereo. I frankly do not remember anything like this even in the Beta vs. VHS or the HD DVD vs. Blu-ray competitions, and those were actual battles for format survival not just a display device in a jungle of TVs, but even then the exchanges were handled professionally and with respect. <p>Ironically in this 3DTV case, the one with the lower image quality 3DTV, the one with 540 black-lines across an image that is half the resolution of 3D Blu-ray (or quarter the resolution if displaying cable/satellite side-by-side 3DTV content), has the guts to denigrate manufacturers implementing a higher quality 3DTV technology. <p><div class="caption right" style="width:319px;"><img alt="T2’s brain processing power is 3D8K per eye, but he 'perceived' it as just 480i with 3D-passive-glasses, I am lucky he spared my life after the test" src="http://www.hdtvmagazine.us/articles/images/cb0588d8bc38_13F2B/clip_image008_e2f20172-6a4d-4110-97bb-669323d5d9e8.jpg" width="317" height="423"><br />T2’s brain processing power is 3D8K per eye, but he 'perceived' it as just 480i with 3D-passive-glasses, I am lucky he spared my life after the test</div>On previous <a href="http://www.hdtvmagazine.com/articles/2011/07/displaying-3dtv-images-what-is-wrong-with-this-picture.php">articles</a> I covered the imaging subject of both technologies and how their images are actually displayed, but it seems passive-3DTV preachers prefer to disregard standards of TV Imaging Science traditionally used to evaluate HDTV image quality, to rather give importance only to the visual perception by the viewer’s brain on demos that can be easily manipulated with untrained viewers. <p>In other words, LG put emphasis on how a viewer “perceives” a 3DTV passive image rather than the quality of the actual 3D image as displayed by the TV, emphasizing that perception should be what it counts, even if the images are just at half-resolution per eye, as the passive 3D method is.  <p>If a TV produces a good quality image it is expected for a viewer to perceive it as such, whereas a low quality TV image would inevitably be perceived as a degraded image by the viewer. <p>For years the Imaging Science has pursued the effort of educating the public about the importance of calibrating a TV <a href="http://www.hdtvmagazine.com/articles/2010/10/autostereoscopic-3dtv-3d-without-glasses-what-else-to-lose-for-stereoscopic-3d-part-3.php">to produce the best image</a> it can display, following strict parameters of imaging standards, so a viewer may perceive a natural image that resembles as much as possible the original source. <p>If 3D can be displayed and viewed as a pair of two well calibrated good quality fully resolved HDTV images, why denigrate its quality when introducing an alternative product that may fit better a relatively small audience that may have viewing issues even in the local 3D theater. <p>The approach LG takes in concentrating only in how 3DTV images are perceived was supported by one recent report that “coincidentally and timely” defended the “perception” factor regardless of the quality of the displayed image, openly contradicting what most experts indicated over the past years, <a href="http://www.hdtvmagazine.com/articles/2011/07/typical-passive-3dtvs-displaying-and-perceiving-3d-images.php">including myself</a>, which is that passive 3DTV technology offers only half-resolution imaging per eye and each eye receives considerably less information than with active-shutter technology, regardless of how capable the brain may be to compensate, half of the original image has been lost. Should that technology exist? Yes, for those needing it to enjoy 3D. In other words: parallel technologies. <p><b></b> <h2>Cut, cut, cut. I thought they would stop when people start “noticing”</h2> <p>For decades, the audio and video industry has been introducing standards and codecs that in one way or another compressed signals humans hear or view, to fit restrictions of transmission, recording space, or equipment limitations. <p><div class="caption left" style="width:286px;"><img alt="To 'cut' corners T2 removes the 3D glasses" src="http://www.hdtvmagazine.us/articles/images/cb0588d8bc38_13F2B/clip_image011_916d992a-0d94-481c-83e0-cdf218fec04d.jpg" width="284" height="408"><br />To “cut” corners T2 removes the 3D glasses</div>From analog interlaced 480i NTSC TV to fit in 6 MHz channel space, to the 16-bit 44 kHz CD sampling of a continuous analog wave of real sounds, to DTS and Dolby multi-channel audio formats with perceptual encoding and masking, to H/DTV MPEG-2 with 50-100 to 1 compression, to DVD and Blu-ray MPEG-2/MPEG-4/VC1 compression, to luminance and color sampling/compression on component analog signals, the list goes on and on, humans have been forced to accept curtailed signals that were “carefully studied” so sounds or video information that arguably may not be heard or viewed when (dis)played simultaneously with other sounds/pixels/colors can be identified and removed from the content. <p>Like masking very soft passages of music played with very loud ones, or filtering out sounds believed to be beyond human’s hearing threshold, although some studies determined that humans can still perceive many very high frequency sounds at various harmonic levels thru bone structure rather than timpani sensation and therefore be able to differentiate the particular timbre of instruments playing the same base note. Likewise, information can be reduced about some RGB colors the eye is more sensitive to, so they are not encoded at full bit resolution as others do.  <p>Cutting corners in audio and video is not a new concept, one common denominator (or should I say “the typical excuse”) is: “human perception may not notice”. In other words, why bother encoding, displaying, transferring, etc. signals that humans may not notice and may not even be aware those signals existed within the original content? <p>Although the readers of this publication, like me, may want to defend quality eternally I honestly believe it may be a bit too late to fight a battle of quality when all the odds are against it, starting by the sad reality that a majority of people is not even educated or trained to appreciate differences in audio/video quality, and even if they do, most would only pay for the “good enough” choices.  <p>Knowing that an original 3D image-pair was created and recorded at the source as two 1080p full resolution images, but is displayed by a passive 3DTV at half-resolution per eye using the same “good enough perception” excuse, goes against the idea of pursuing for image quality at the source to begin with, so why bother Mr. James Cameron and the others? <p>The passive 3D display industry seems not minded about going backwards in quality under the excuse that our brains will compensate, so a “good-enough half-resolution” Avatar version should be OK next time Mr. Cameron. However, Cameron is actually thinking in the other direction, he is entertaining the idea of increasing the 24 fps frame rate to 48 or even 60 fps to smooth out the 3D presentation, and also increasing the 3D depth of future content compared to his conservative Avatar 3D depth. <p>Traditionally, the subject of audio/video quality has been very abstract for most people. For many it is hard to think beyond the simple face value of the content they see and hear so they can analyze what they have “lost in the transmission” and understand what they could have seen or heard otherwise, unless actual side-by-side comparisons can be made, not by casual 3DTV images on a shopping center or central station, but with specific 3DTV patterns designed to isolate and clearly show the bad from the good, and then demonstrate how that affects real images. When that is done people would notice, then tell me what they prefer. <p>That type of testing is missing at most 3DTV magazine reviews perhaps because many reviewers are still waiting for 3D tools and patterns to help evaluate 3DTVs properly, as a good quality dual 2D image pair. Due to the lack of 3D tools and patterns to perform solid comparisons among 3DTVs subjective evaluations could range from “that is a nice looking image” to “I was able to view it for 3 hours with no headaches” to “my 3-year-old daughter just came into the room and said she liked this 3DTV better, besides, the frame of the 3D glasses was pink, her favorite color”. <p>An interesting column (“3D-What should consumers know?”) was written by industry’s renowned Imaging Science Joe Kane <a href="http://www.widescreenreview.com/wsr_issuedetail.php?recid=162&issuenumber=159">in a recent issue</a> of the Wide-Screen Review Magazine, in my opinion one of the most prestigious publications regarding quality of video and audio. The column addresses exactly the same 3DTV issues I have been writing for the past 3 years. Professionally we both recommend active-shutter glasses for quality reasons, and both share the same concerns with passive 3DTV, especially how is being introduced to viewers. <p>On another front, brick and mortar specialized audio/video stores are disappearing rapidly, they were unique in that they took the time to teach and demonstrate what quality is about. Unfortunately, consumers would not have that opportunity anymore. <p>Now many consumers become DIY 3DTV experts under the umbrella of the Internet and Wikipedia, buying 3DTVs in the Web without even seeing the image, but happy about an unbeatable price. Good price deals always give purchasers the power of finding appreciation for even the worst images and most probably never admit they made a bad choice (don’t you dare saying their baby is ugly). <p>MP3 found a way to please a lot of people. Would you think those would be as pleased if they could have experienced a good demo of hi-end audio? 480i NTSC TV pleased many people for over half century, until 480p DVD and 720p/1080i HDTV produced the no turning back WOWs!! <p>Monaural pleased many people until stereo and multi-channel audio immersed them into the music and soundtracks of concerts and movies. 4:3 B&amp;W aspect ratio movies pleased many people until color <a href="http://www.hdtvmagazine.com/articles/2007/01/cinemascope-hdht-part-i-the-concept.php">CinemaScope came to stimulate their peripheral vision</a> and brought viewers into a movie. <p>A common denominator on all those is that, if economically possible, most would find difficult to go back to lower quality once the better quality is experienced.  <p>But what happens when quality gives up in front of you before you even tasted it? What happens when the 3DTV you just purchased cannot be upgraded to ever show the original 3D quality of the source? <p>Next article: “The Perception of Passive 3DTV – a No-brainer Analogy”. Stay tuned 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 26, 2011  8:06 AM</b>
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
			<?=getComments(4552)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 4552)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2011/10/passive-3dtv-brain-perception-an-excuse-for-technical-limitations.php" type="text/javascript" charset="utf-8"></script>
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