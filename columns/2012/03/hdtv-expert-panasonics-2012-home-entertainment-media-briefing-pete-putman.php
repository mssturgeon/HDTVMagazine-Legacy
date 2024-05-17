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
		AND e.entry_id = 4751";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4751 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4751 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4751";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-panasonics-2012-home-entertainment-media-briefing-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4751";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Panasonic&rsquo;s 2012 Home Entertainment Media Briefing &ndash; Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Panasonic&rsquo;s 2012 Home Entertainment Media Briefing &ndash; Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Panasonic&rsquo;s 2012 Home Entertainment Media Briefing &ndash; Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - Panasonic&rsquo;s 2012 Home Entertainment Media Briefing &ndash; Pete Putman</title>
	<meta name="keywords" content="blu ray, dmp bdt, lcd tvs, viera connect, active shutter, plasma, panasonic, lcd, tvs, –, dmp, inch, models, year, viera, line, inches, active, blu, ray, new, ’s, hdmi, market, series" />
	<meta name="description" content="Panasonic&amp;acirc;��s current TV lineup includes LCD, plasma, active shutter 3DTV, AND passive 3DTV in all sizes. So, what&amp;acirc;��s the 2012 marketing strategy &amp;acirc;�� something for everyone?" />
	<meta name="title" content="HDTV Expert - Panasonic&amp;rsquo;s 2012 Home Entertainment Media Briefing &amp;ndash; Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Panasonic&amp;rsquo;s 2012 Home Entertainment Media Briefing &amp;ndash; Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-panasonics-2012-home-entertainment-media-briefing-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Panasonic&amp;acirc;��s current TV lineup includes LCD, plasma, active shutter 3DTV, AND passive 3DTV in all sizes. So, what&amp;acirc;��s the 2012 marketing strategy &amp;acirc;�� something for everyone?" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4751', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-panasonics-2012-home-entertainment-media-briefing-pete-putman.php">HDTV Expert - Panasonic&rsquo;s 2012 Home Entertainment Media Briefing &ndash; Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March 28, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=302&category=Entertainment">Entertainment</a></b>, <b><a href="/category.php?id=479&category=HTPCs & Laptops">HTPCs & Laptops</a></b>, <b><a href="/category.php?id=511&category=LCD HDTVs">LCD HDTVs</a></b>, <b><a href="/category.php?id=506&category=Plasma HDTVs">Plasma HDTVs</a></b>
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
          <div id="attachment_1991" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1991" rel="attachment wp-att-1991"><img class="size-full wp-image-1991" title="Jason Gastman Shows off line MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Jason-Gastman-Shows-off-line-MR.jpg" alt="" width="600" height="435" /></a><p class="wp-caption-text">Senior product manager Jason Gastman walks us through the 2012 TV lineup.</p></div>
<p>Panasonic’s 2012 TV and home entertainment line show took on extra importance this year, what with the company closing in on a $9.7 B (as in “billion”) loss for the fiscal year that will end on Friday, March 30. To be accurate, a substantial portion of that red ink is due to a goodwill accounting write-down on the 2009 acquisition of Sanyo, which will cease to exist as a corporate entity after Friday.</p>
<p> </p>
<p>But the remainder is largely attributable to consumer electronics operations; specifically, the television business. Think about it: Just five years ago, a 42-inch plasma TV with 1080p resolution retailed for over $2,000. Now, the price is about 1/3 of that, meaning the cost per diagonal inch for that TV has dropped from about $47 to $15. (Real-world example: I paid $1,100 for a TH-42PX80U 42-inch 1080p Panasonic plasma in September of 2008.)</p>
<p> </p>
<p>Frankly, Japanese TV manufacturers can’t be profitable at that price point, which is why Panasonic (along with Sony, Sharp, and other TV brands) are having such a miserable year financially.</p>
<p> </p>
<p>But Panasonic was ‘different’ from the other guys in that it promoted plasma display technology as a differentiator. And Panasonic did (and still does) plasma better than anyone else, now that the late, lamented Pioneer plasma lineup has faded into history.</p>
<p> </p>
<p>The focus on plasma meant that for years, there was a ‘green line’ between plasma screen sizes and LCD TV sizes that Panasonic simply would not cross. That line – 42 inches – was breached slightly in 2011 with the introduction of a couple of LCD TVs that used the company’s IPS-Alpha LCD alignment layer. <em>(IPS stands for ‘in-plane switching’ and was originally developed by Hitachi. LG also uses a variant of IPS extensively in their LCD TV product line.)</em></p>
<div id="attachment_1992" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1992" rel="attachment wp-att-1992"><img class="size-full wp-image-1992" title="VIERA CONNECT TV Screen TC-L55WT50 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/VIERA-CONNECT-TV-Screen-TC-L55WT50-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">The VIERA CONNECT menu is different for 2012, and adds some exclusive content partnerships.</p></div>
<p> </p>
<p>This year, all bets were off as Panasonic blew by the ‘green line’ with 42-inch, 47-inch, and even 55-inch LCD TVs. And except for a couple of bargain-basement 42-inch 720p models and one 3D iteration, smaller Panasonic plasma TVs are now becoming history. The TC-P42X5 (720p) is tagged at $429.99 (meaning it will be the first 42-inch plasma to sell for less than $400 at an everyday price), while the TC-P42XT50 will retail at $650. The 50-inch XT50 also supports 3D playback.</p>
<p> </p>
<p>The LCD usurpers all fall into the VIERA E50 series, which includes the TC-L42E50 ($900), TC-L47E50 ($1,100), and TC-L55E50 (price TBA). All three models use LED backlights and have 1080p resolution; VIERA Connect; social networking TV function; DLNA; a PC input; four HDMI terminals and two USB ports. And all models are ‘WiFi-ready’ (you have to buy a separate USB dongle and plug it in).</p>
<p> </p>
<p>Back to plasma: The ‘top of the line’ models for 2012 are in the ST50 series, and include (quoting from the press release) <em>“…Infinite Black Pro Panel; Full HD 3D; VIERA Connect™ with a web browser and built-in WiFi; 1080p Full HD resolution; 2500 FFD (Focused Field Drive); fast switching phosphors; 2D ? 3D conversion; Social Networking TV which allows the user to simultaneously view a program on the TV and connect with their Twitter and/or Facebook account on the same screen ; 3D Real Sound with 8-train speakers –eight dome type micro speakers with reflectors that deliver wide ranging, high quality sound; a new louver filter; Media Player;  Bluetooth; DLNA; VIERA Link™; three HDMI connections and two USB ports.”</em> (Wow, let me catch my breath for a moment…)</p>
<div id="attachment_1999" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1999" rel="attachment wp-att-1999"><img class="size-full wp-image-1999" title="TC-P50ST50 Side View 2 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/TC-P50ST50-Side-View-2-MR.jpg" alt="" width="600" height="467" /></a><p class="wp-caption-text">The TC-P50ST50 plasma line is loaded for bear. So why does it offer only three HDMI inputs?</p></div>
<p> </p>
<p>Does that sound like the feature set of a TV, or of a computer? The ST50 plasma sets actually have a dual-core processor, and with all of the listed input and output ports – plus all of the apps, streaming capabilities, WiFi, and other features – they basically ARE computers, albeit fitted with very large plasma monitors. You can get ‘em in sizes ranging from 50 inches (TC-P50ST50, $1,400) to 65 inches (TC-P65ST50, price TBA).</p>
<p> </p>
<p>For ‘Full HD 3D’ plasma viewing (their wording), Panasonic offers the UT50 series, which starts at 42 inches (TC-P42UT50, $800) and goes all the way to 60 inches (TC-P60UT50, ($1800 – and no, I don’t know why there isn’t a 65-inch SKU in this lineup.) UT50 plasma TVs are all 1080p resolution, with VIERA Connect (you need to buy the WiFi dongle separately), media player, faster switching phosphors, Bluetooth connectivity, DLNA operation, two HDMI connections (Why only two? There are three on the ST50 series!), and dual USB ports.</p>
<p> </p>
<p>Now, here’s the weird part. Panasonic, along with Samsung and Sony, launched the Full HD 3D initiative (<a href="http://www.fullhd3dglasses.com/index.php/press/august-30-2011/" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','http://www.fullhd3dglasses.com']);">read the press release here</a>) in 2011, and at CES 2012, demonstrated interoperability between different models of active shutter 3D glasses. The goal was to educate and inform consumers that active shutter 3D TV is a very different (and better) animal than the passive 3D TVs that employ circularly-polarized eyewear and deliver half the vertical picture resolution. (LG is the biggest proponent of passive 3D, which is similar to the process used in 3D movie theaters.)</p>
<p> </p>
<p>So – you’d think Panasonic would be firmly behind active shutter? Guess again. The new line of ET5-series LCD TVs uses film-patterned retarder (FPR) LCD panels and have most of the bells and whistles of the VIERA line, including built-in Wifi, 2D to 3D conversion, the internal media player, DLNA compatibility, and the social networking TV functions.</p>
<div id="attachment_1993" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1993" rel="attachment wp-att-1993"><img class="size-full wp-image-1993" title="TC-L47ET5 Passive 3D TV CROP MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/TC-L47ET5-Passive-3D-TV-CROP-MR.jpg" alt="" width="600" height="450" /></a><p class="wp-caption-text">Full HD 3D is the only way to go! (Except when it isn't.)</p></div>
<p> </p>
<p>Oddly enough, the ET5 TVs come with four HDMI inputs, which is more than any other model range. And of course, you get four pairs of passive 3D glasses with each TV, starting with the TC-L42ET5 ($$1,100) and continuing with the TC-L47ET5 ($1,300) and TC-L55ET5 ($1,900).</p>
<p> </p>
<p>When I asked Panasonic representatives why they continue to support both plasma and LCD in the same screen size, even though plasma TV sales accounted for only 13.5% of the worldwide market last year, they replied that there was still enough demand for the product through ‘niche’ dealers, especially in the larger sizes. That’s probably true for the high-end VXT products, but I don’t see how any 42-inch plasma will be in the line next year – and 50-inch sizes may also be heading towards the endangered species list if those market share numbers keep dropping.</p>
<p> </p>
<p>I got a similar answer when I asked Panasonic to reconcile its emphatic support for active shutter 3D with the launch of several passive 3D TV models. The reply was something to the extent that these models didn’t have all of the goodies of the UT50 series (but they do have more HDMI inputs!) and that the company was simply responding to consumer demand.</p>
<div id="attachment_1994" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1994" rel="attachment wp-att-1994"><img class="size-full wp-image-1994" title="Blu-ray Player Exhibit MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/Blu-ray-Player-Exhibit-MR.jpg" alt="" width="600" height="326" /></a><p class="wp-caption-text">Jonesing for connected Blu-ray players? Panasonic's got six of 'em.</p></div>
<p> </p>
<p>OK, let’s take a closer look at what’s <span style="text-decoration: underline;">really</span> happening. First, Panasonic sells a lot of LCD TVs. (In fact, they sold more of them back in 2010 than Sharp did!) But for all of 2011, the market leader in combined LCD and plasma TV sales was Samsung, capturing 26% of the business in the fourth quarter. Panasonic was way back in fourth place with 6.9% of the market. According to NPD DisplaySearch, this was the first time that someone other than Panasonic led in worldwide plasma TV shipments.</p>
<p> </p>
<p>Remember about six years ago when Panasonic announced it was building new plasma fabs that would ultimately give it the capacity to roll out 11 million plasma TVs a year? The ENTIRE plasma TV market for 2011 was 5.2 million units, a decline year-to-year of 8%. Overall, plasma TV shipments accounted for just 13.5% of the worldwide total.</p>
<p> </p>
<p>As a result, Panasonic has idled a good portion of its plasma manufacturing capacity, along with a lot of its LCD capacity. Across the board, Panasonic’s TV revenue share declined 19% from 2010, which is a big contributor to all the red ink I mentioned at the start of this article. So the company’s 2012 TV marketing strategy may be more along the lines of <em>“Let’s throw everything at the wall and see if anything sticks!”</em></p>
<p> </p>
<p>Truth be told, we are probably looking at the demise of plasma as a consumer TV display technology in the not-too-distant future. Panasonic will eventually run into the same buzz saw that sliced up Pioneer – too much fab capacity and not enough market demand. It’s a great idea on paper to say you’ll continue to support plasma in the high-end and niche markets, but there comes a point where it just doesn’t make sense economically to stay in the business – and Panasonic is already staring at unprecedented losses for the year.</p>
<p> </p>
<p>As for 3D, the DisplaySearch numbers show that TV purchases that were specifically tied to 3D capability amounted to about 7% of all TVs sold in North America in the third quarter of 2011 (the latest quarter for which I could find numbers). Active shutter or not, 3D TV just isn’t selling well on this part of the planet, but Panasonic’s support for passive 3D makes no sense at all – it’s not like the numbers are going to change as a result.</p>
<div id="attachment_1995" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=1995" rel="attachment wp-att-1995"><img class="size-full wp-image-1995" title="New Blu-Ray Remote MR" src="http://www.hdtvexpert.com/wp-content/uploads/2012/03/New-Blu-Ray-Remote-MR.jpg" alt="" width="600" height="447" /></a><p class="wp-caption-text">Now, there's a remote control you don't see every day. (Notice the button dedicated to Netflix streaming?)</p></div>
<p> </p>
<p>In another portion of the demo room, Panasonic showed just how good its black levels are on 2012 plasma TV models, compared to 2011. Excuse me, but I recall seeing this same demo for the past six years, and the black levels on my 2008 model are already excellent – measuring below .1 nits on average. The 2011 VIERA ‘before’ plasma I observed had black levels resembling a 2006-vintage LCD TV, and didn’t look right to me. It’s time to retire this demonstration!</p>
<p> </p>
<p>Oh, I almost forgot: There will be six new Blu-ray players in the line this year, three more than are really necessary. Four of them fall into the Smart Network 3D Blu-ray category, starting with the top-line DMP-BDT500 ($350) and stepping down through the DMP-BDT320 ($200) to the DMP-BDT220 ($150). There’s also the very compact and stylish DMP-BBT01 ($270), which can operate horizontally and vertically.</p>
<p> </p>
<p>All four models offer (and I quote from the press release again) <em>“…an improved UniPhier chip processor, 24p output for VOD, an expanded VIERA Connect functionality, and FLAC (Free Lossless Audio Codec),192Hz/32bit Audio DAC (not available on the DMP-BBT01), Smartphone remote control capability, a new touchpad remote control (available on DMP-BBT01, DMP-BDT500, DMP-BDT320),  2D-to-3D up-conversion2, which can convert 2D images from VIERA Connect1,  DVDs and Blu-ray discs into 3D with natural depth perception, a new slim design and a unique slot-in drive that is found in two of the models (the DMP-BBT01 and DMP-BDT320).”</em></p>
<p> </p>
<p>Two non-3D players also make their debut. The DMP-BD87 will retail for $120, while the DMP-BD77 is the entry-level model, priced at $90. The difference? Built-in WiFi on the DMP-BD87, while you’ll need the accessory USB dongle for the DMP-BD77. Both models (and the four 3D versions) are also ‘Smart VIERA’ enabled and support the most popular Internet TV sources, including Netflix, YouTube, CinemaNow, Vudu, and Hulu Plus.</p>
<p> </p>
<p>The reality of most Blu-ray player purchases is that people are buying them primarily to get inexpensive access to Netflix, YouTube, and Hulu. These three services account for something like 80% of all video streaming these days, and a connected Blu-ray player is a great way to add streaming to an older (but not THAT old) LCD or plasma TV – like mine.</p>
<p> </p>
<p>Panasonic also has some new, more ergonomic remote controls for its TVs and Blu-ray players. One of them has just a few buttons and a touch pad, similar to those found on notebook computers. (Oh wait, I forgot – TVs are basically computers nowadays…)</p>
<p> </p>
<p>So there you have it – plasma TVs to 65 inches, LCD TVs with LED backlights to 55 inches (and very likely to 60 inches in short order), and both active and passive 3D TVs. Something for everybody in 2012!</p>
<p> </p>
<p>Come to think about it, this roster reads a lot like the LG TV lineup from 2009, and we all know what eventually happened to their active 3D TV line…</p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March 28, 2012  9:08 AM</b>
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
			<?=getComments(4751)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4751)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/03/hdtv-expert-panasonics-2012-home-entertainment-media-briefing-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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