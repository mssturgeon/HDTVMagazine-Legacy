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
		AND e.entry_id = 352";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 352 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 352 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 352";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/03/looking-ahead.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 352";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Looking Ahead" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Looking Ahead" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Looking Ahead" />
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
	<title>HDTV Magazine - Looking Ahead</title>
	<meta name="keywords" content="young producers, shelf space, web cams, production values, hdtv magazine, hdtv, world, youtube, web, new, people, production, internet, hollywood, any, film, movie, movies, today, every, may, bandwidth, time, young, kids" />
	<meta name="description" content="&lt;strong&gt;THAT'S WHEN I DISCOVERED WWW.YOUTUBE.COM&lt;/strong&gt;

Without notice my HDTV died. What is there to do but turn misfortune into good so I decided that I would bite the bullet and 'YIPPY YAHOOO!' get a new HDTV with all those new goodies, like HDMI (and a bigger screen)and 1080p. I'm excited again!

So, I went shopping ... on the net. I soon tired from the confusions that all consumers now face and for relief punched up the news. After the usual disheartening reports about Iraq I sought refuge and went to &lt;a href=&quot;http://www.movies.com&quot;&gt;www.movies.com&lt;/a&gt; to see what was showing locally. Nothing tempted me so I extended my search for some light entertainment on the net. 

That's when I discovered &lt;a href=&quot;http://www.Youtube.com&quot;&gt;www.Youtube.com&lt;/a&gt;.  

Now we are not talking HDTV here, but the future for HDTV programming is more than likely incubating there. So, it's more than a worthy side trip that I hope you will take with me in this piece. " />
	<meta name="title" content="Looking Ahead" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Looking Ahead" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/03/looking-ahead.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;strong&gt;THAT'S WHEN I DISCOVERED WWW.YOUTUBE.COM&lt;/strong&gt;

Without notice my HDTV died. What is there to do but turn misfortune into good so I decided that I would bite the bullet and 'YIPPY YAHOOO!' get a new HDTV with all those new goodies, like HDMI (and a bigger screen)and 1080p. I'm excited again!

So, I went shopping ... on the net. I soon tired from the confusions that all consumers now face and for relief punched up the news. After the usual disheartening reports about Iraq I sought refuge and went to &lt;a href=&quot;http://www.movies.com&quot;&gt;www.movies.com&lt;/a&gt; to see what was showing locally. Nothing tempted me so I extended my search for some light entertainment on the net. 

That's when I discovered &lt;a href=&quot;http://www.Youtube.com&quot;&gt;www.Youtube.com&lt;/a&gt;.  

Now we are not talking HDTV here, but the future for HDTV programming is more than likely incubating there. So, it's more than a worthy side trip that I hope you will take with me in this piece. " />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=352', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/03/looking-ahead.php">Looking Ahead</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>March 24, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=9&category=Entertainment">Entertainment</a></b>
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
				<p><strong>...THAT'S WHEN I DISCOVERED WWW.YOUTUBE.COM</strong></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/bKeT4LH9MH0"></param><embed src="http://www.youtube.com/v/bKeT4LH9MH0" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><br />
Without any notice my HDTV died. What is there to do in a time of technical grief but turn misfortune into good, so, I decided that I would bite the bullet and 'YIPPY, YAHOOO!'! get a new HDTV with all those new goodies, like HDMI (and a bigger screen)and 1080p. I'm excited again!</p>

<p>So, I went shopping ... on the net. I soon tired from the confusions that all consumers now face and for relief punched up the news. After the usual disheartening reports about Iraq I sought refuge and went to <a href="http://www.movies.com">www.movies.com</a> to see what was showing locally. Nothing tempted me so I extended my search for some light entertainment on the net. </p>

<p>That's when I discovered <a href="http://www.YouTube.com">www.YouTube.com</a>.  </p>

<p>Now we are not talking HDTV here, but the future for HDTV programming is <em>more </em>than likely to be incubating there. So, it's more than a worthy side trip that I hope you will take with me in this piece. </p>

<p>YouTube.com is a web site where one can upload at no cost homemade or professionally produced "movies" (usually clips under 10 minutes length) from any internet connection. There is a size limit so no long form movie is going to show up there. This is not a copyright threatening sight expect, perhaps, for short form music videos (which are usually promotional and don't carry the same copyright concerns as movies). This is for the people themselves who are making these little movies with their web cams and digital video cameras. The movie clips are stored on the site and can be sorted by visitors using "tags" that hone you in to content categories you want. Any (or all if you have the time) of the clips can be downloaded by one click per movie ... all free! And a lot are downloaded. According to the current Time Magazine 30 million clips are downloaded every day from YouTube.com and that number is growing rapidly. As the song goes "Something's Happening Here."</p>

<p><em><strong>(You will enjoy all of our examples more with audio enabled)</strong></em><br />
<object width="425" height="350"align="right"><param name="movie" value="http://www.YouTube.com/v/QD0B12KNR0M"></param><embed src="http://www.YouTube.com/v/QD0B12KNR0M" type="application/x-shockwave-flash" width="425" height="350"></embed></object><br />
Most of the videos are produced by our kids and young adults horsing around. The two to three minute amateur "masterpieces" spare no expense (or even pocket change), but they are often spruced up with modern computer assisted editing. Maybe I'm getting old and grandfatherly but I found them endlessly amusing and more enlightening than I had bargained for (in other words, viewers beware). Others are produced by small production companies from all parts of the world seeking, I suspect, recognition. They, of course, exhibit considerably more polish, production values, and forethought. </p>

<p>There are several movie clip hubs similar to YouTube.com, not the least being Google. You can search them out using "YouTube" as your search string then clicking on "Similar Sites." Shockwave is used on the YouTube site so any web browser with that one plug-in can play all movies posted their uniformly. You don't have to watch them from the YouTube site either. You can cut and paste a URL they provide (as do the others) for each clip and embed it on your own MySpace.com page, your blog, or your professional web pages. The movies will appear where you want them and entertain your visitors at no cost to you or to them. (see example below)</p>

<p><strong>Oh, you're not familiar with <a href="http://www.MySpace.com">www.MySpace.com</a>? </strong></p>

<p>Get with it dude and lady dudettes. No less a hipster than Rupert Murdoch (no grass growing under his feet) bought <a href="http://www.MySpace.com">www.MySpace.com</a> for $500 million dollars. <a href="http://www.MySpace.com">www.MySpace.com</a> has become THE social hall of the world and kids flock to it by the millions in order to get known and show off their characters (if not a bit more!! Caution is advised). The self-created pages remind me of a high school annual but evolving year round with new pictures and commentary added incessantly by pals who visit your (MySpace) page. Parents cringe who have heard nightmarish stories about predators lurking in the MySpace halls. That is a quite-valid fear and parents should take every precaution. While no substitute for parental guidance kids today are a far savvier lot than we ever were and have concocted a "perv alert network" that puts Homeland Security to shame! <object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/ogJ76elRFSQ"></param><embed src="http://www.youtube.com/v/ogJ76elRFSQ" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p>Yes, the majority of clips are absolutely outrageously playful things but look past the adolescence and you will find among the rocks and sand a diamond or two. The significant thing to me is the raw talent--the rough diamonds-who debuts their acts on YouTube.com for all the world to see. That site, and others like it, are global audition halls without the freezing intimidation from immediate casting call judgment or from a haughty eye of a competitor. Nor is there the barrier of money. Youthful "starving artists", if they are serious enough, can unleash their creativity from within the confines of their own rooms (or even from Internet cafes) and get themselves before reviewers, or, more hopefully, an eager agent or a principal from any place in the world. Some young producers have gained an enormous following with hundreds of thousands, even millions of people having downloaded and viewed their content. When did this ever happen before?</p>

<p>Networks of people can also be formed on these sites. That will undoubtedly lead as things mature to powerful creative collaborations. All who register on YouTube.com, for example, may network with any other registrant. I have registered and started sending messages of encouragement to the more talented producers. That will pay dividends. When these youngsters see that I am from the industry they always respond with a sincere appreciation for me taking the time to review their work and to contact them. I am astounded by the level of intelligence and maturity that comes back to me in their responses. We may have underestimated this echo-boomer generation. They become buoyed with a new enthusiasm confidence when they realize that their work is being seen and reviewed (even when critically) by professionals in the field. I recommend that others from any walk do the same. Those you choose to support may blossom into HDTV program producers to entertain you and your children's children. I never fail to mention that HDTV can be a low-cost tool (today) leading them from an amateurish beginning with a webcam to one where commercial $$$ can actually be realized. They really perk up with that "insight." I can also foresee the day when there will be prize-granting contests on the web. They may start off as little amateur film fests but no reason why they can't grow into bigger and bigger world-class film festivals--huge events that are web-wide. Fox's <em>American Idol</em> could expand today on a wideband Internet to be "Global Idol", where Olympic-like national competition moves on to being international with the top four or five candidates for "Global Idol" being given enough money to mount whatever kind of production they think will win the crown. A linear progression for Youtube.com might be the establishment of a subscription tier. Production money could then be raised and distributed to those young producers who have made the grade to the pay section. </p>

<p>The YouTube-like hubs have every possibility of growing into major entertainment behemoths for both production and distribution. The fact that IPTV is on the horizon along with a national push in the U.S. for more wideband services should not escape notice of their management. Hubs will have content creators from within their own loyal ranks able to fill every increase in bandwidth this or any other nation wants to employ. I sincerely believe that the bedrooms of these budding moviemakers represent the beginning of great studios of the future. The strongest creative personality will be the center. A home prosumer or consumer HDTV camera is available today which will out-perform the Arriflex camera of the film era. These kids will have computer editing and special effects tools today with which to make much more interesting productions. </p>

<p><strong>Culture Does Matter...</strong></p>

<p><em>"As long as there has been a talking Hollywood, Hollywood has had a huge impact on the rest of the world."</em> _George Lucas, March, 2006</p>

<p>"Star Wars" film creator George Lucas, the progenitor to all of this kind of decentralized thinking, told a packed house in San Francisco Tuesday night that the United States is a provincial country with a culture that has invaded the world via Hollywood. The movie capital of the world needs to "be cautious of the kind of Imperialism we export". But the Lucas appeal comes at a time when the celluloid capital's influence is also fading. The stars are falling, the audiences are retreating and, according to report after report, contraction is occurring at just about every level.  Not only this, but production values--the once unchallengeable domain of Hollywood-have risen dramatically in other parts of the world to rival the look and feel of the best of Hollywood features. But before sounding like Chicken Little and his famous sky I should note that Hollywood has shown enormous resilience to every business challenge and have been always formidable competitors since the beginning of movies. Still, the ground beneath all of it is shifting like never before. As a third generation Hollywood person I feel it deeply. A power-shift is coming as the young creative personalities from around the world spring up like bamboo shoots and stitch themselves together at the roots in the confines of these international web behemoths. The natural aggregation of talents these sites offer could easily form the power center for future entertainment and information services around the world. If that is the case a new breed of spectacularly creative moguls will also emerge to manage these empires from within the core of the super-hubs...all from a single lap top if desired.</p>

<p>"Some people in other countries are troubled by what they see as U.S. culture squashing local art and cinema," continued Lucas. This acknowledged domination by Hollywood comes to an abrupt end when differing cultures around the world shoot up through the Internet and visibly blanket the earth. Consider how the young people the world over now perceive each other using video as a means for daily communications. How can they not find both their commonality as well as their uniqueness? Web cams and, soon enough, live full-motion HDTV instant messaging systems will connect like-minded kids from all corners of the world. All of these things will and are forming the cultural superstructure for the future and returning power 'of-the-folk' back to 'the-folk'. It may take a hundred years or more and how long they keep it is another question, but the means for improving relations in our world using these new tools is a source of genuine hope. When adulthood overtakes these young artists and communicators they will inevitably lead a cultural vision forward that will abandon the divisive barbs and snares used to protect despots of the past and become fixed on leading a society of open minds to a common point where all people may mutually discover the keys to peace. That should be the aspiration of every leading artist today. Hollywood has buried itself up-to-the neck in a celluloid tomb and if they do not fully embrace and spiritually lead the faster paced digital revolution they may never leave it. The only security Hollywood has is to become this new international hub that is open to the changes in the world and which embraces the new means for elevating and distributing the talent that expresses it. Perhaps, Mr. Murdoch has seen this potential and is the first of the seven majors to invest in a people-driven generation of entertainment. </p>

<p><strong>And what's in store for the professionals ... ?</strong></p>

<p>For those producing professional products now a slick distribution system is in the making. Again, it is an inevitable consequence of the web and the demands put upon it by HDTV. Within a few years' time a producer will be able to upload a full length interactive HD-produced feature to one or more of these huge hubs and it will be drawn down by individual viewer-requests for as long as the Internet exists. If you embed advertising in your product and make the right contractual agreements it will make no difference whether your product is stolen and redistributed to a million other web sites or stays secure on one. Advertiser will pay on the number of views (information relayed by every means from Nielson or the likes back to the mother ship) and not by which site has served up the movie. You won't sell schlock either. There is a rating system installed on YouTube (and all of its kind) which lets the audience rank/review the clips. The higher the viewer rating, the better will be the "shelf space" given to the production. The blockbusters of tomorrow will be those who have the best consumer ratings and the Internet's best "shelf space".</p>

<p><strong>Loving Diversity...</strong></p>

<p>I watched a most-interesting movie online while doing this research. The movie was from Ballywood (India) with first rate production values. It had high marks from the public and a correspondingly prominent shelf space. The flick was a finely crafted look into an obscure cultural corner of India--widowhood-- that I had never seen nor even thought about before running into Youtube. It was a refreshing experience. It was, of course, not presented in HDTV. The demand that we HDTV people have for content will do as it has always done--pull whatever technology and bandwidth required for receiving it into our homes. This is the HDTV era and the web has to become HDTV-capable to retain its standing just as TV and cable channels must to survive. Signs are already showing with IPTV HDTV boxes coming on the market that we here at HDTV Magazine are not the first to figure this out.</p>

<p><strong>Bandwidth, Is There Ever Enough?</strong></p>

<p>But perhaps you disagree that such broadband will come. You may fall in with HDNet's founder, Mark Cuban, who has been preaching that the bandwidth to do all of this stuff is utter fantasy. He claims this in hopes of uprooting copy protection measures in HDTV hardware. In his "where's the beef" message (most recently delivered at the CEA Technology Summit in Washington DC last week) he said that all of the copy protection measures are guards against things that are never going to happen. The time and cost of downloading a huge block of data making up an HDTV movie is not going to occur in our lifetimes, or perhaps the next generation. "The bandwidth just is not there and that which gets installed will be filled with more interesting things than dated movies... and what you have to-the-home in bandwidth is elastic anyway as it is being shared."  What is truly predictable is that consumer demand for bandwidth will grow and grow and when that is expressed with a corresponding willingness-to-pay for it, it will come. Right now demand is fueled by undemanding wimpy little web cams and video clips and some of the media news services (turning to streaming media), but the day when people are producing in HDTV and getting it to markets via IP (Internet Protocol)is coming. There is no turning back from that pursuit. </p>

<p>A little more evidence in support of this direction is this headline just pulled from today's news:<em><strong>"60 Minutes" coming to Web in Yahoo! deal CBS has expanded its deal with Yahoo!, agreeing to bring "60 Minutes" to the Web this fall. Under the deal, Yahoo! also will carry outtakes and extras from the news magazine.</strong></em></p>

<p>Another hint that the U.S. will take more aggressive action on establishing ubiquitous wideband comes from this just-released directive from the EC, which states: "The European Commission has a directive to bring broadband Internet to all of Europe by 2010." The Commission said that EU member states must mobilise their collective policies toward bridging the "broadband gap" in rural and less-developed areas. The U.S. is not comfortable being seen as technically second rate to Europe.</p>

<p>To summarize: The writing is on the wall. Big, BIG change is coming as bandwidth increases to the home. A rich new universe of artistic expression is destined to come from the kids growing up with their incomparable tools for collaboration, production, and distribution. We learned this in the HDTV conversion: Anything holding too firmly to the past will suffer when the new inevitably begins to supplant it. These young producers will be making marketable content and attracting audiences at an earlier age than ever before. They will have both time and perspective on their side to gather powerful teams of collaborators, to refine the arts of production, and will have gained distribution skills that simply don't exist today. Their produce will be distributed to any and all displays--huge in electronic theaters, big in HDTV homes, small in handhelds. The  consumer/beneficiary of this phenomenal era will have something well-worth seeing, hearing, and writing home about. A clear world view will come from a superior understanding led by the arts through HDTV and supported by the Internet(not the age of two dimensional film).  </p>

<p>What do I mean by this last comment about film? Film, you say, has played a major role in giving the world its identity. Why would I suggest it is not instrumental in forming a still-clearer world view? Let me take you back 20 years when a foreign film maker acquired an expensive HDTV system (camera and recorders). It was news then because the cost was in the millions of dollars for just a handful of production items. I asked the artist/buyer in an interview why he would spend so much on what was still an experiment? He shot back without hesitation that film was an already explored medium and to a creator it was pretty-much dead. There wasn't anything new he or any artist could find to do with it. But with electronic production the gates to a vast new world of multidimensional creativity were flung opening with no sign of their ever closing. He blazed a trail for the YouTube youngsters to find and follow. These kids are going to enter into the most advanced era of creative expression known (so far) to man and it is not optimism that believes that they will shake us by the lapels and captivate our hearts and minds with a truth never before known. Stay tuned, it's all going to be televised in HDTV. This reminds me; next week I will take you with me on my shopping trip for my new HDTV! I am excited because HDTVs are so much better and bigger now.  Be sure to get the brand new report by Rodolfo LaMaestra. It's a bargain and within its 207 pages is a complete picture of the state-of-the HDTV industry and all of the products being produced for the U.S. and Canadian HDTV market. http://<a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">www.hdtvmagazine.com/reports/hdtv-technology-review.php</a> _Dale Cripps</p>

<p>UPDATED 4/4/2006 - This article just in....</p>

<p>LONDON, England -- <em>A British singer has signed up by a major record label after broadcasting live performances from her living room on the Internet.</p>

<p>Sandi Thom, 24, is now on the books of RCA/SonyBMG after signing with the label at her flat on Monday night.</p>

<p>She built up a daily audience of more than 100,000 people around the world.</p>

<p>Speaking on British television, Sandi said she could not believe what had happened and that her life had "changed dramatically."</p>

<p>"I don't think I have quite realized it just yet," she told GMTV.</p>

<p>"It has obviously changed for the better. "I have managed to get massive amounts of exposure through using the Internet and that is something that people have struggled to do for years."</p>

<p>Several record labels had approached the aspiring star following her Webcasts, which were broadcast on 21 consecutive nights.</p>

<p>RCA label director Craig Logan said: "Sandi is a very talented artist with an already unique story.</p>

<p>"We're very excited that we're now going to be a part of that story as she develops into a major artist."</em></p>

<p><br />
 </p>

<p></p>

<p>And now a few non-HDTV programs for your enjoyment taken at random from <a href="http://www.Youtube.com">www.Youtube.com</a>...</p>

<p>It's a little bit of entertainment brought to you by HDTV Magazine, the first voice in High-definition.</p>

<p><br />
<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/pJvjuEDXekg"></param><embed src="http://www.youtube.com/v/pJvjuEDXekg" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/8NE5elL30w4"></param><embed src="http://www.youtube.com/v/8NE5elL30w4" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><br />
<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/dbBfosZoFZs"></param><embed src="http://www.youtube.com/v/dbBfosZoFZs" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/YSPFuftHmRk"></param><embed src="http://www.youtube.com/v/YSPFuftHmRk" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/LgUHpVQrWNQ"></param><embed src="http://www.youtube.com/v/LgUHpVQrWNQ" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p>Over two million people have downloaded this video...</p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/D2kJZOfq7zk"></param><embed src="http://www.youtube.com/v/D2kJZOfq7zk" type="application/x-shockwave-flash" width="425" height="350"></embed></object>  </p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/QvF9Pqnwm_Y"></param><embed src="http://www.youtube.com/v/QvF9Pqnwm_Y" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><strong>And this boy is a one man band...</strong></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/xrALwpkjkJc"></param><embed src="http://www.youtube.com/v/xrALwpkjkJc" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/BfZFERGAXQA"></param><embed src="http://www.youtube.com/v/BfZFERGAXQA" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><strong><em>And now a word from China...</em></strong></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/OykYZ8SM2_8"></param><embed src="http://www.youtube.com/v/OykYZ8SM2_8" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/lVPRZCWI7ZM"></param><embed src="http://www.youtube.com/v/lVPRZCWI7ZM" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/V3OjQbkntgE"></param><embed src="http://www.youtube.com/v/V3OjQbkntgE" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/KIxIZB3AYoo"></param><embed src="http://www.youtube.com/v/KIxIZB3AYoo" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>

<p><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/5GkwAKQElng"></param><embed src="http://www.youtube.com/v/5GkwAKQElng" type="application/x-shockwave-flash" width="425" height="350"></embed></object></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>March 24, 2006 10:41 PM</b>
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
			<?=getComments(352)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 352)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/03/looking-ahead.php" type="text/javascript" charset="utf-8"></script>
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