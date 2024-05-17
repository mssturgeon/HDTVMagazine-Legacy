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
		AND e.entry_id = 4374";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4374 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4374 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4374";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/05/hdtv-expert-deg-cranks-up-the-3d-hype-machine.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4374";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - DEG Cranks Up The 3D Hype Machine" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - DEG Cranks Up The 3D Hype Machine" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - DEG Cranks Up The 3D Hype Machine" />
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
	<title>HDTV Magazine - HDTV Expert - DEG Cranks Up The 3D Hype Machine</title>
	<meta name="keywords" content="blu ray, quote “, picture quality, pairs glasses, don’t know, ”, respondents, “, blu, ray, glasses, owners, deg, survey, watch, watching, ’s, pairs, while, group, buy, programming, either, know, content" />
	<meta name="description" content="A recent study commissioned by the Digital Entertainment Group says consumers are “tremendously satisfied” with the home 3D experience. Did you expect to hear otherwise?" />
	<meta name="title" content="HDTV Expert - DEG Cranks Up The 3D Hype Machine" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - DEG Cranks Up The 3D Hype Machine" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/05/hdtv-expert-deg-cranks-up-the-3d-hype-machine.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="A recent study commissioned by the Digital Entertainment Group says consumers are “tremendously satisfied” with the home 3D experience. Did you expect to hear otherwise?" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4374', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/05/hdtv-expert-deg-cranks-up-the-3d-hype-machine.php">HDTV Expert - DEG Cranks Up The 3D Hype Machine</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>May 30, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=352&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=336&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b>, <b><a href="/category.php?id=302&category=Entertainment">Entertainment</a></b>
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
          <p>Last Tuesday, the Digital Entertainment Group, an advocacy group comprised of CE manufacturers and Hollywood content producers, released a study conducted by research firm SmithGeiger that claims 3D TV owners are overwhelmingly happy with their purchases.</p>
<p>This is hardly earth-shaking news, considering the source. The DEG’s job is to promote things like 3D and the Blu-ray optical disc format. Both are key parts of the revenue stream for TV manufacturers and movie studios.</p>
<p><a rel="attachment wp-att-1291" href="http://www.hdtvexpert.com/?attachment_id=1291"><img class="aligncenter size-full wp-image-1291" title="DEG Logo" src="http://www.hdtvexpert.com/wp-content/uploads/2011/05/DEG-Logo2.jpg" alt="" width="293" height="203" /></a></p>
<p>The survey, <a href="http://www.degonline.org/pressreleases%5C2011%5Cf_3DTV%20Research.pdf" onclick="javascript:_gaq.push(['_trackEvent','outbound-article','www.degonline.org']);">which you can read here</a>, does reveal many interesting ‘a-has!’ if you read carefully between the lines. Let’s take them in order.</p>
<p><strong>Quote: <em>“Of those who view programming in 3D, an overwhelming 88 percent rated the 3D picture quality positively, compared to 91 percent for their 2D picture quality.”</em></strong> Really? Why didn’t 3D picture quality rate as high as or higher than 2D picture quality? Wasn’t that a key consideration in buying a 3D TV in the first place?</p>
<p><strong>Quote: <em>“And, 24 percent of those who view 3D at home reported watching more television – in 2D and 3D – since purchasing their new 3D TV.”</em></strong> OK, can we break that down a bit further? How much more TV were they watching, on average? 10% more? 50%? 75%? We don’t know. And what’s the breakdown between increased 3D and 2D viewing? Again, we don’t know.</p>
<p><span style="text-decoration: underline;">Here’s what I found much more interesting:</span> 75% of the people in the DEG study who bought a new 3D TV did NOT report watching more 2D or 3D programming after their purchase, while 1% are actually watching less TV. Why? Because there wasn’t enough 3D programming to watch?</p>
<p>Does ‘watching more television’ include DVDs and Blu-ray movies? We just don’t have enough details here, so the <em>‘24% reported watching more TV’</em> claim is statistically meaningless without context. (And what about that 1% who are now watching less TV? Interesting…)</p>
<p><strong>Quote: <em>“Also, 85 percent of 3D TV owners surveyed would prefer to watch half, most, or all of their programs in 3D.”</em></strong> Looking at the tables actually provided by DEG, 14% said they’d watch most programs in 2D. But the group that said <em>“it would be an even split”</em> (using the report’s own wording) came to 23%, and a group that is stuck at 50-50 clearly does not favor either side – even though the DEG counted this group in the 85%.</p>
<p>I read the results this way: 62% of respondents clearly would watch everything or most programming in 3D, while 23% don’t lean either way and 14% prefer 2D. If you are trying to make a case that there is a clear preference for 3D, the numbers presented say that 37% of the sample group <span style="text-decoration: underline;">does not</span> prefer to ‘watch most or all programming in 3D.’ While that still presents a 2:1 ratio favorable to 3D viewing, it is quite different from the 85% figure claimed by the DEG.</p>
<p><strong>Quote: <em>“Of the 3,100 3D TV owners surveyed, only a handful experienced any discomfort when using active shutter 3D glasses.”</em></strong> All right, I’m intrigued – what is <em>“a handful?”</em> Read further into the report and you will see that (a) 18% of respondents <em>“never feel like I fully adjust to the glasses”</em> while an additional 8% state that, <em>“it takes several minutes for me to adjust to the glasses.”</em> That is a total of 26% respondents who either have on-going problems with 3D glasses or take a long time to get used to 3D eyewear.</p>
<p>And the DEG survey numbers are in line with research done in human vision response by several universities and the American Optometrists Association. At the ADA/3D@Home conference in New York City a couple of months ago, the estimates I heard were that as much as 25% of the general population cannot see 3D correctly.</p>
<p>If the DEG thinks 26% is <em>“a handful,”</em> they are delusional.</p>
<p><strong>Quote: <em>“With an average of 2.38 pairs of glasses at home, it is clear that 3D TV owners are actively using their 3D TVs for viewing 3D.”</em></strong> If I had drawn that conclusion from the statistics presented in this survey, I would have gotten a big, fat “F” from my statistics professor at Syracuse University, not to mention my logic professor at Seton Hall!</p>
<p>Here’s what he would have said to me: Make sure you have all of the facts before you draw any conclusions! Facts such as: Anyone who bought a Samsung 3DTV in the past year got 2 pairs of glasses with it as part of a 3D starter kit. Did you buy an LG Infinia 3D TV bundle last fall? You got four pairs of glasses with it.</p>
<p>In fact, so many promotions bundled two or more pairs of glasses with the purchases of a 3D TV that the fact that the average home had 2.38 pairs doesn’t mean very much at all. Nor does it allow us to draw any definitive conclusions about how often viewers are using their TVs to watch 3D. All it means is that the average 3D TV owner has about 2 pairs of 3D glasses.</p>
<p><strong>Quote: <em>“More than 7 out of 10 of those surveyed use a Blu-ray 3D or 3D-capable player.”</em></strong> For what purpose, exactly? The survey question is incomplete, as it doesn’t ask specifically whether respondents <em>“use a Blu-ray 3D or 3D-capable player”</em> to watch 3D, a mix of 3D and 2D content, or mostly 2D content?</p>
<p>Here’s my question: How many of those Blu-ray players are mostly being used to watch Netflix streaming, and how often?</p>
<p>The accompanying chart shows that 87% use a cable or satellite set-top box, while 71% use a Blu-ray or other 3D-capable player (not a PlayStation 3), and 61% use a DVR or TiVo.</p>
<p>But the chart also says that 28% of respondents use a standard-definition DVD player. Why include that number, as it’s not relevant to 3D content playback? 34% of respondents have a Nintendo Wii (as I do), and it’s not a 3D delivery platform, either.</p>
<p>The survey goes on to mention that that <em>“44 percent of 3D TV owners purchased their Blu-ray player bundle with their TV.”</em> If these purchases really were 3D TV bundle deals, then 44% of 3D TV owners actually got a <span style="text-decoration: underline;">free</span> Blu-ray player as part of their TV bundle. That was made quite clear in the advertising and marketing for various 3D TV bundle packages. Maybe the DEG isn’t quite clear on the meaning of the words “free” or “bundle?”</p>
<p>At the May 24 Connected TV and 3D event in New York City, DEG president Ron Sanders (also president of Warner Home Video) stated,  <em>“The results of this landmark study clearly show that 3D TV owners are overwhelmingly happy with their 3D experience…this bodes well for the future of the Home 3D category.” </em></p>
<p>Really? My statistics professor would have been ROFL at hearing that. Here’s what my conclusions are.</p>
<p><strong>(1)</strong> <strong>75% of the survey respondents who bought a new 3D TV aren’t watching any more TV as a result of that purchase.</strong> That could mean they aren’t that enthusiastic about 3D, or that they just bought the TV as an upgrade and made sure it had 3D capability in it that they may or may not use. We don’t know enough to say – SmithGeiger didn’t ask.</p>
<p><strong>(2) About two-thirds of the respondents want to watch most if not all of their programming in 3D.</strong> That is an interesting number and one which should be re-sampled a year from now.</p>
<p><strong>(3) 26% of the respondents either cannot use 3D glasses at all or have measurable difficulty in adapting to 3D eyewear. </strong>That’s right in line with educated estimates and is a substantial impediment to widespread 3D TV adoption.</p>
<p><strong>(4) The average number of pairs of 3D glasses in survey households is not substantially higher than the number of free glasses given away in 3D TV bundles.</strong> And we have NO idea how often they are being used, as SmithGeiger never bothered to ask.</p>
<p><strong>(5) We know that 7 out of 10 respondents have Blu-ray players. We also know that many respondents have cable and satellite boxes.</strong> <strong>There are more of the latter than of the former.</strong> <strong>(Stop the presses!)</strong> What we DON’T know is how often those Blu-ray players and set-top boxes are being used to watch 3D content.</p>
<p>In fact, it’s mind-boggling that SmithGeiger didn’t ask any questions respondents about the number of hours per day, week, and month they actually spend watching 3D content!</p>
<p>Other fun tidbits:</p>
<p><strong>(6) 78% of PlayStation 3 owners have upgraded their consoles for viewing 3D Blu-ray movies, and 76% of PS3 owners upgraded to play 3D games.</strong> Yet the following chart in the DEG study shows that only 7% of PS3 owners play 75 to 100% of their games in 3D, while 59% (by far the largest group) said that 25% or less of their game-playing is in 3D. There’s a disconnect here.</p>
<p><strong>(7) 55% of 3D TV owners “would definitely” buy a 3D TV again. </strong>What – only half? I thought 88% of them loved their 3D TV picture quality! 25% of respondents said they “would probably” buy another 3D TV, while 14% said they “might or might not.” 7% said they “probably would not or definitely would not” buy a 3D TV again.</p>
<p>I interpret those numbers to mean that roughly half of the survey respondents are either (a) lukewarm about, (b) indifferent to, or (c) opposed to buying a 3D TV again.</p>
<p>That hardly constitutes a ringing endorsement for 3D TV, so it’s surprising that SmithGeiger didn’t ask the logical follow-up question: <em>“Please list the reasons why you would buy or not buy a 3D TV again?”</em></p>
<p>Given the DEG’s position as industry cheerleader for 3D and Blu-ray, I’m not at all surprised in the way the survey results were stated. There is clearly a need for objective, in-depth analysis of why people have purchased 3D TVs, how they use them, and what their like and dislikes about 3D TV are.</p>
<p>But this survey and report doesn’t do the job. It’s clearly presented as more ‘spin’ that fact. There are too many holes in its methodology and flaws in its results  to be taken seriously as an objective analysis of the trends in 3D TV adoption rates and the factors that drive them.</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>May 30, 2011 12:16 PM</b>
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
			<?=getComments(4374)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4374)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/05/hdtv-expert-deg-cranks-up-the-3d-hype-machine.php" type="text/javascript" charset="utf-8"></script>
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