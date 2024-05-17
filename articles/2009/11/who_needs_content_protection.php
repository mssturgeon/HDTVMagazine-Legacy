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
		AND e.entry_id = 3383";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3383 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3383 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3383";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3383";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Who Needs Content Protection?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Who Needs Content Protection?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Who Needs Content Protection?" />
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
	<title>HDTV Magazine - Who Needs Content Protection?</title>
	<meta name="keywords" content="content protection, component analog, blu ray, aacs content, analog connections, content, analog, consumers, video, protection, digital, ”, aacs, equipment, audio, may, outputs, quality, component, connections, protected, “, hdmi, ict, copy" />
	<meta name="description" content="The subject of “content protection” continues to be a complicated issue. While some firmly think that one should be able to legally make a copy of lawfully acquired content, if such content is protected to avoid exactly that and the protection is circumvented to perform such copy, how can that action be right when it is actually violating the right of the content creator? 

The content production industry needs..." />
	<meta name="title" content="Who Needs Content Protection?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Who Needs Content Protection?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The subject of “content protection” continues to be a complicated issue. While some firmly think that one should be able to legally make a copy of lawfully acquired content, if such content is protected to avoid exactly that and the protection is circumvented to perform such copy, how can that action be right when it is actually violating the right of the content creator? 

The content production industry needs..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3383', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php">Who Needs Content Protection?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>November 19, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=331&category=Digital Rights Management (DRM)">Digital Rights Management (DRM)</a></b>
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
				<p>The subject of “content protection” continues to be a complicated issue. While some firmly think that one should be able to legally make a copy of lawfully acquired content, if such content is protected to avoid exactly that and the protection is circumvented to perform such copy, how can that action be right when it is actually violating the right of the content creator?  <p>The content production industry needs the audio/video equipment industry for their content to be consumed. Equipment can only be sold if appealing content is produced. Consumers need both industries to survive so they can continue getting entertainment content, and both industries need consumers to buy their products. A delicate balance of their relationship has to be maintained to coexist harmoniously while respecting each other’s rights, especially when the rules and systems are still evolving.  <p>Consumers are not all pirates and content creators have the right to an income so they can make more content. Because of the imperfect rules and the ever-evolving standards, consumers and creators of content are tested continuously to demonstrate to each other their mutual respect, without excesses, greed, or abuses on their side of the market. Equipment manufacturers follow thru by adapting their designs to include one more connection and one more content protection mechanism to help maintain that harmony, at a very high cost to consumers.  <p>We know that, throughout the years, the approach has not worked well, but how could it be improved? With so many constraints to maintain backward compatibility to legacy hardware and software, and with self-replacing connections, cables, content protection protocols, etc. the baggage seems larger than the problem to solve. Would more transformation over the same base be more efficient and effective than starting from scratch?  <p><b></b> <h2>A Corporate Mission of Challenging Content Protection </h2> <p>A <a href="http://www.hdtvmagazine.com/columns/2009/08/hdtv_almanac_dvd_archiving_at_risk.php">recent column</a> mentioned very briefly the legal subject of content protection with companies such as Kaleidescape and RealDVD. <a href="http://www.cepro.com/article/understanding_the_kaleidescape_and_realdvd_cases/%5b">This article</a> goes deeper into the matter with those companies. Violating a contract regarding a DVDCCA license (Kaleidescape) is a different legal issue than helping others systematically violate the protection of content (RealDVD), but the spirit of the “content protection” concept links them both at the hip. Ironically, this is not even for content of HD quality.  <p>A few years ago, a number of rules were discussed at the FCC for the distribution of protected HD content over satellite and cable to allow/prohibit the equipment’s ability to copy once/never, and the viewing. The subject was further complicated with the idea of establishing the "Broadcast Flag" to protect some premium content broadcasted by terrestrial DTV to deter its illegal distribution over the Internet, but it was later rejected by the court.  <p><a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php">Here</a> is an analysis of that subject. The graph at the end gives a general overview.  <p><b></b> <h2>Reconciliation of Common Sense </h2> <p>I am primarily a consumer of content, but I also produce it.  <p>As a consumer I consider it fair having to pay for every piece of content I want to view or collect, protected or not. However, I am a 60-years-old-timer. New generations are accustomed to think all content should be free because they generally “find a way” to get it free, and many times this is not due to lack of money. For young generations, living in the digital world has evolved this way since their childhood.  <p>The same digital world made the younger generations of audio/video consumers to be satisfied with lower quality due to the convenience of MP3, YouTube, and video over-compressed on a PC/cell phone, while unable (or unwilling) to appreciate the merits of hi-end audio and high quality video, as experienced and preferred by earlier generations for decades.  <p>Part of the problem is that demonstrations of hi-end audio and video by dedicated brick-and-mortar establishments are becoming endangered species, precluding younger generations from personally experiencing what quality is about.  <p>Wearing my hat of content producer, I was able to view a different perspective upon writing my <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">fifth book</a>. It was almost a year of work written for the HDTV industry. I learned to appreciate the content-creator side of the equation when someone had the nerve to request a free PDF (of a $1000 book) with the intention to distribute free copies within the organization. A reputable publication asked for a courtesy copy of the 560-page book to mix its content with theirs, and make a profit, their profit. From those experiences, I realized that what could be considered common sense to one person is certainly not to another, and there cannot be mutual understanding and respect if content rules are not in place and enforced.  <p>The digital era had made the content protection monster larger than ever. New generations are accustomed not to realize nor care for the effort a content creator endures to make a living.  <p>Although the ripping and copying of protected content for personal use may look okay to some that see it as a harmless action, it could easily grow out of control even in a civilized and democratic society. It actually happened already in several places across the globe, I witnessed piracy personally in Europe, China, Argentina, and other countries, in an open market, in front of the police, and we know we are not immune in the US either.  <p>This does not condemn the idea of allowing granddad to innocently make a copy of his purchased Cinderella DVD to been able to play the movie at his cabin when his grandchildren visit him over the weekend. His intention is to be less worried with damage when the kids handle the disc, rather than to sell the copy to boost his 401K. However, where should the line be drawn?  <p><b></b> <h2>Piracy Complicates Consumer Lives </h2> <p>Piracy has prompted content producers not to trust anyone in the digital world. Therefore, regardless if a person is good or not concerning content creation, all consumers pay some kind of price, including granddad, and unfortunately in this case we are all suspected to be pirates before we can prove our innocence.  <p>Content that is protected comes with a two-edge blade that negatively affects law obedient consumers and creators in one way or another. If all people obeyed the various laws of humankind the police and the court systems may not need to exist, and good people would not have to contribute their taxes to pay for their salaries and infrastructure.  <p>Bad apples that violate protected content with the purpose of illegal business are notorious to have the means to invest in the necessary equipment to re-digitize an analog HD signal, and that complicates the lives of millions of consumers (more on it below).  <h2>Blu-ray, AACS, and 11 Million HDTV Owners at Risk </h2> <p>When Blu-ray was made commercially available to consumers in 2006, discussions were held to implement the Image Constraint Token (ICT). The ICT is a feature of the Advanced Access Content System (AACS) of the Blu-ray format that could disenfranchise the 11 million early adopters that have purchased HDTVs since 1998. The ICT would disable the viewing of a purchased HD movie at its full resolution because the TVs above were designed with only component analog connections (DVI and HDMI were implemented later in HDTVs).  <p>If the content creator implements the ICT Token on a Blu-ray disc it would instruct the player to take the HD 1080i/p image read from the disc and down-res it to SD quality when sent to the component analog HD output of the player.  <p>This 2007 HDTV technology Report <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">Industry Edition</a> briefly covered the subject as follows:  <p>---------------------------  <p>“<i>Six times of quality reduction (to 16%) of the 1080i HD original version, whereby a 1080ix1920 = 2,073,600 pixels of video frame are down converted to 480ix704 = 337,920 pixels, both at the same rate of 30 frames per second.”</i>  <p><i>“Having a program with 100% quality reduced to 16% is not certainly an incentive to buy high quality HDTVs, a key ingredient for the success of the DTV transition mandated by the government.“</i>  <p><i>“Likewise an HDTV and an HD-STB not having protected digital connections might also run the risk of eventually not been able to view premium content in full HD if the content provider (i.e. HBO, VOD) requests it.”</i>  <p><i>“The digital outputs (HDMI or DVI) will still carry the full resolution of the disc because the outputs are protected by HDCP. Component analog connections cannot carry that protection, reason by which the resolution would be downgraded.” </i> <p><i>“The agreement also affects a large number of PC monitors/video cards used to watch Hi-def DVD that are not DVI/HDMI HDCP compliant; the vast majority of them are connected with regular VGA analog connections.”</i>  <p><i>“However If the content provider studio sets the flag to “off” the player would supply the full resolution of the content to the analog outputs. The package of the pre-recorded movie must indicate if the flag was used on the movie, so the buyer can be made aware before the purchase.”</i>  <p><i><u>Hollywood</u></i><i><u> and the ICT Token</u></i>  <p><i>“In March 2006, Sony’s announced their position not to implement the down-res feature of "Image Constraint Token" (ICT) that is built into the AACS standard for the majority of its Blu-ray content and allow Hi Def players to playback HD as 1080i over component analog connections.”</i>  <p><i>“Other Hollywood studios declared that they were following Sony’s position as well; 20th Century Fox (NWS), Disney (DIS), Universal, and Paramount (VIA) said they initially would not use the ICT Token on their releases.”</i>  <p><i>“However, Warner Brothers said that the studio most likely would release some HD-DVD titles through April implementing the ICT.”</i>  <p><i>-------------------------</i>  <p>In other words, an unprotected analog connection is viewed as a risk because an HD analog signal can be re-digitized using an analog-to-digital converter. Pirates may use such equipment to make illegal digital versions of the content using the HD 1080i component analog outputs from Blu-ray players, a connection that cannot carry the High-bandwidth Digital Content Protection (<a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a>) embedded into DVI or HDMI (a matter known as “analog hole”).  <p>Such illegal copy may not have the bit-by-bit digital quality of the original content because it was subjected to the steps of the conversion process, but a market for such level of quality may exist if the price is right, and such distribution is viewed as a considerable revenue loss by content producers.  <p>On June 19, 2009, an update of the AACS license conditions was made:  <p><a href="http://www.aacsla.com/license/AACS_Content_Participant_Agrmt_090619.pdf"><b>http://www.aacsla.com/license/AACS_Content_Participant_Agrmt_090619.pdf</b></a><b></b>  <p>I include below an excerpt of some relevant paragraphs from pages E-16 and E-17:  <p><i>-----------------------------</i>  <p><i>“2.2 Analog Outputs. A Licensed Player shall not pass, or direct to be passed Decrypted AACS Content to an analog output except:</i>  <p><i>2.2.1 An analog output of audio, or of the audio portions of other forms of Decrypted AACS content; or</i>  <p><i>2.2.2 An analog output of video delineated in Table A1, AACS Analog Authorized Outputs, in accordance with any associated restrictions and obligations specified therein and in the Agreement, and subject to the following sunset requirements:</i>  <p><i>2.2.2.1 Analog Sunset – 2010. With the exception of Existing Models, any Licensed Player manufactured after December 31, 2010 shall limit analog video outputs for Decrypted AACS Content to SD Interlace Modes only. Existing Models may be manufactured and sold by Adopter up until December 31, 2011. Notwithstanding the foregoing, Adopter may continue to manufacture and sell an Existing Model in which the implementation of AACS Technology is a Robust Inactive Product after December 31, 2010 provided that when such Robust Inactive Product is activated through a Periodic Update, such Periodic Update results in a Licensed Player that limits analog video outputs for Decrypted AACS Content to SD Interlace Modes only. Nothing in this section shall be interpreted to override limitations or obligations stated in any other section of this Agreement.</i>  <p><i>For purposes of this section, “SD Interlace Modes” shall mean composite video, s-video, 480i component video and 576i video.</i>  <p><i>2.2.2.2 Analog Sunset – 2013. No Licensed Player that passes Decrypted AACS Content to analog video outputs may be manufactured or sold by Adopter after December 31, 2013.” </i> <p><i>------------------------- </i> <p>The “analog sunset” deadlines mentioned above confirm again that millions of HDTVs will eventually be at risk when not able to display the HD image for which their TVs were designed. Additionally, that would affect many in-wall wiring installations made by professionals for home theaters and whole-house audio/video systems to distribute HD using component analog connections, <a href="http://www.cepro.com/article/hdmi_or_component_integrators_weigh_in">viewed by most</a> as a more reliable connection than DVI and HDMI (due to HDCP, and cable length issues).  <p>I personally double up all my video installations, digital and component analog cabling in parallel. It may cost more in wiring but the labor for in-wall installations is more expensive, not to mention the drywall repairs on an already built house, and more expensive would be to perform a labor repeat if the component analog connections become disabled, or the HDMI/DVI cables become unreliable.  <p><b></b> <h2>The Collateral Damage on A/V Equipment </h2> <p>Over the past decade, consumers experienced a variety of content protection methods designed to deter the making of illegal digital copies of protected content. The industry made consumers go thru DVD/CSS, 1394/<a href="http://www.hdtvmagazine.com/glossary.php#DTCP+%28Digital+Transmission+Content+Protection%29">DTCP</a>, <a href="http://www.hdtvmagazine.com/glossary.php#DVI+%28Digital+Visual+Interface%29">DVI</a>/<a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a>, <a href="http://www.hdtvmagazine.com/glossary.php#HDMI">HDMI</a>/<a href="http://www.hdtvmagazine.com/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29">HDCP</a>, Selectable Output Controls (SOC), Broadcast Flag, Blu-ray managed copy, and soon <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_bluray_only_if_your_hdtv_permits_it.php">what Hollywood was recently lobbying for</a>.  <p>On a similar vein, consumers were subjected to the protection of 7.1 digital channels of multi-channel audio using analog outputs, and the endless battles of lossless and lossy audio codecs. Consumers were also subjected to the multi-channel competition with extra surround speakers the industry deems consumers “have to have” to hear the 5-second helicopter flight scene over their heads with a ceiling speaker, a reborn concept <a href="http://www.hdtvmagazine.com/glossary.php#Ceiling+Surround+channel%2Fspeaker">implemented by ADS in the seventies</a>. Not to mention the ever “evolving” HDMI versions (1.0 to 1.4 or is soon 1.5/.6/.7/.8?) of specification and microchips, with options within those versions implemented by manufacturers as they want, “motivating” consumers to replace expensive equipment that was just purchased, which is generally obsolete the minute it leaves the store.  <p>Audio/video receivers, DTVs, players, etc. are continuously forced to carry an old bag of backward compatibility obligations within their design due to unresolved connectivity and content protection issues. Equipment is subjected to self-inflicted obsolescence that provoke early replacement of an otherwise perfectly functional audio/video piece when is unable to be upgraded seamlessly with firmware. Equipment transformations are no longer purposed to improve their primary function, image, or sound, rendering it incapable to be a medium-lasting product to justify the investment.  <h2>Finding the Solution </h2> <p>The <a href="http://www.hdtvmagazine.com/articles/2006/02/is_hdtv_complex_enough.php">connectivity confusion</a> affects consumers but also affects content creators. Exploring each other’s reasoning can be a healthy endeavor if done constructively to reach a mutually beneficial agreement, rather than letting the FCC and Congress once again do the “reasoning” under Hollywood’s pressure, and increase everyone’s blood pressure.  <p>So what exactly is the appropriate solution for the ever-evolving analog/digital connection that seems not capable to prevent piracy? Should the industry ignore piracy and concentrate on consumers? Is the loss of revenue attributed to piracy justly estimated? Could that estimate justify so much complexity? Should the connection/protection be the solution or should another content distribution model be implemented? Should the price of content include an overhead for the product to be unprotected and still produce a reasonable ROI including an estimated revenue loss due to piracy? That would certainly facilitate the connectivity complexity of law obedient consumers and equipment manufacturers, but it would make everyone pay extra, could that be agreeable to consumers? Is it justified the complexity and the higher cost of equipment that has to be redesigned to augment the floor plan of back panels to be able to offer expanded backward compatibility with all possible connections?  <p>Frankly, it does not seem I would be able to see an amicable and balanced solution before my days are over. I recall all this started long before the consumer had access to digital. Many years ago, it was to disallow the making of analog copies from analog originals, considered a big problem even when multiple analog generations suffered cumulative degradation when cascading down from the original.  <p>The possibility of mass producing illegal bit-per-bit/ lossless digital copies that may look and sound as good as the original feeds more negative energy every time a new style of delivery of content is proposed, such as the <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_bluray_only_if_your_hdtv_permits_it.php">Hollywood proposal</a> for a new delivery of compelling content.  <p>Without solving the problem of content protection at its roots first and without implementing a well-planned vision and technology for an equal protection of consumers and creators of content, the matter only becomes increasingly complicated for the media, the electronics, and the consumers.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>November 19, 2009  2:55 PM</b>
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
			<?=getComments(3383)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 3383)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2009/11/who-needs-content-protection.php" type="text/javascript" charset="utf-8"></script>
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