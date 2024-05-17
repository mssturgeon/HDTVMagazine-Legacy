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
		AND e.entry_id = 4613";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4613 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4613 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4613";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/samsung-smart-led-and-plasma-tvs-usher-in-a-new-era-of-connectivity-and-control.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4613";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control" />
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
	<title>HDTV Magazine - Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control</title>
	<meta name="keywords" content="samsung smart, smart tvs, samsung electronics, smart interaction, smart led, samsung, smart, tvs, content, new, led, users, apps, plasma, technology, control, entertainment, slim, design, experience, unes, –, industry, bezel, enjoy" />
	<meta name="description" content="Samsung Electronics Co., Ltd. today unveiled new innovations in its latest LED and Plasma TV series that will redefine the way consumers manage their home entertainment experience, interact with their smart TVs and other devices and access the industry's broadest range of content.
&lt;ul&gt;&lt;li&gt;&lt;strong&gt;Smart Interaction&lt;/strong&gt; - Launch and use apps more easily through Motion Control, Voice Control and Face Recognition.&lt;/li&gt;&lt;li&gt;&lt;strong&gt;Smart Content&lt;/strong&gt; - Share photos, memories and memos with family from the TV to the cloud to smart devices, manage your own health directly from the TV, and access educational content for kids. The industry's broadest offering of movies, TV shows and streaming videos can be enjoyed via Samsung Apps, the industry's preferred TV apps platform, and Samsung Media Hub. Samsung Smart Hub offers true multi-tasking – surf the web while using or downloading multiple apps simultaneously.&lt;/li&gt;&lt;li&gt;&lt;strong&gt;Smart Evolution&lt;/strong&gt; - Select Samsung TVs in 2012 will allow consumers to enjoy new experiences as their TV is 'reborn' each year with the latest smart technology, simply by installing kits.&lt;/li&gt;&lt;/ul&gt;

Samsung's flagship LED ES8000 and Plasma E8000 series TVs, along with the ES7500 and ES6800 series of LED TVs..." />
	<meta name="title" content="Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/samsung-smart-led-and-plasma-tvs-usher-in-a-new-era-of-connectivity-and-control.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Samsung Electronics Co., Ltd. today unveiled new innovations in its latest LED and Plasma TV series that will redefine the way consumers manage their home entertainment experience, interact with their smart TVs and other devices and access the industry's broadest range of content.
&lt;ul&gt;&lt;li&gt;&lt;strong&gt;Smart Interaction&lt;/strong&gt; - Launch and use apps more easily through Motion Control, Voice Control and Face Recognition.&lt;/li&gt;&lt;li&gt;&lt;strong&gt;Smart Content&lt;/strong&gt; - Share photos, memories and memos with family from the TV to the cloud to smart devices, manage your own health directly from the TV, and access educational content for kids. The industry's broadest offering of movies, TV shows and streaming videos can be enjoyed via Samsung Apps, the industry's preferred TV apps platform, and Samsung Media Hub. Samsung Smart Hub offers true multi-tasking – surf the web while using or downloading multiple apps simultaneously.&lt;/li&gt;&lt;li&gt;&lt;strong&gt;Smart Evolution&lt;/strong&gt; - Select Samsung TVs in 2012 will allow consumers to enjoy new experiences as their TV is 'reborn' each year with the latest smart technology, simply by installing kits.&lt;/li&gt;&lt;/ul&gt;

Samsung's flagship LED ES8000 and Plasma E8000 series TVs, along with the ES7500 and ES6800 series of LED TVs..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4613', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/samsung-smart-led-and-plasma-tvs-usher-in-a-new-era-of-connectivity-and-control.php">Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  9, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=368&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=504&category=Plasma HDTVs">Plasma HDTVs</a></b>, <b><a href="/category.php?id=529&category=Smart HDTVs">Smart HDTVs</a></b>
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
				<p class="prtitle">Samsung Smart LED and Plasma TVs Usher in a New Era of Connectivity and Control</p>

<center><i>Smart Interaction, Smart Content and Smart Evolution Will Change the Way Consumers Experience Entertainment</center></i><br />
<br />

<p>2012 International CES<br />
booth #12004</p>

<p><strong>LAS VEGAS--(BUSINESS WIRE)--</strong>Samsung Electronics Co., Ltd. today unveiled new innovations in its latest LED and Plasma TV series that will redefine the way consumers manage their home entertainment experience, interact with their smart TVs and other devices and access the industry's broadest range of content.<br />
<ul><li><strong>Smart Interaction</strong> - Launch and use apps more easily through Motion Control, Voice Control and Face Recognition.</li><li><strong>Smart Content</strong> - Share photos, memories and memos with family from the TV to the cloud to smart devices, manage your own health directly from the TV, and access educational content for kids. The industry's broadest offering of movies, TV shows and streaming videos can be enjoyed via Samsung Apps, the industry's preferred TV apps platform, and Samsung Media Hub. Samsung Smart Hub offers true multi-tasking – surf the web while using or downloading multiple apps simultaneously.</li><li><strong>Smart Evolution</strong> - Select Samsung TVs in 2012 will allow consumers to enjoy new experiences as their TV is 'reborn' each year with the latest smart technology, simply by installing kits.</li></ul></p>

<p>Samsung's flagship LED ES8000 and Plasma E8000 series TVs, along with the ES7500 and ES6800 series of LED TVs, received CES 2012 Innovations Design and Engineering Awards. Samsung will showcase its new TVs at the 2012 International CES, booth #12004, which will be held at the Las Vegas Convention Center (LVCC), from January 10-13, 2012.</p>

<p>"In this era of smart entertainment, consumers are changing the way they want to be entertained and how they choose to access this content," said Hyun-suk Kim, executive vice president, Visual Display Business at Samsung Electronics. "Samsung is redefining what a TV can do so people can use more intuitive ways to control their entertainment experiences, maintain closer contact to people that are important to them, and easily manage and share content across multiple screens."</p>

<p><br />
<strong>INTUITIVE CONTROLS FOR THE MOST EASY-TO-ENJOY CONTENT EXPERIENCE</strong></p>

<p>Samsung's premium 2012 Smart TVs will feature Samsung's Smart Interaction technology – an intuitive platform that uses Motion Control, Voice Control and Face Recognition commands for the TV. Smart Interaction complements the remote and seeks to offer users greater choice and convenience in how they control their home entertainment experience.</p>

<p>For example, users can turn the TV on or off, activate selected apps or search for content in the web browser simply by speaking in any of the 20 to 30 languages that are supported by the technology. With a wave of their hand, they can browse and choose a link or content via the web browser. The built-in camera recognizes movement in the foreground and two unidirectional array microphones recognize voice at an incredibly accurate rate. Noise cancellation technology helps separate any background noise from the users' commands.</p>

<p>Samsung's 2012 line of Smart LED and Plasma TVs also support connectivity with select wireless keyboards and mice, making it more convenient than ever for users to access and navigate content.</p>

<p><br />
<strong>SAMSUNG SMART TVS GET EVEN SMARTER AND CLOSER TO THE FAMILY</strong></p>

<p>Samsung Smart TVs now bring the family back to the living room. The new TVs embrace the promise of a multi-screen world by allowing users to do more with their TV through Samsung's new suite of exclusive services, the first of its kind in the industry:</p>

<p>Family Story's album lets users organize photos and enjoy slide shows on the big screen. Family members can also share memos and reminders anywhere they are via the phone, tablet, PC or TV.<br />
Fitness allows users to connect their Samsung TV to a Samsung smartphone via a mobile Fitness app. They can also connect to a WiFi-enabled scale to manage their weight goals and use the TV's built-in camera to create a virtual mirror to monitor their exercise routines.<br />
Kids offers a wealth of infotainment and games in a kid-friendly interface that parents and control and monitor.<br />
Samsung's premium Smart TVs – including the UNES8000 LED TV and PNE8000 Plasma TV – are powered by a new dual-core processor to enable multi-tasking, so apps no longer need to be exited and relaunched.</p>

<p>Searching for movies, browsing the Internet, downloading apps via Samsung Apps, accessing Video on Demand content or chatting with friends has never been more convenient.</p>

<p>Samsung's Smart Hub user interface has also been redesigned for faster performance and improved convenience with a cleaner, more intuitive interface in Full HD. With a tabbed web browser, users can open multiple pages at a time, while the new Content Bar remembers recent activities for easy access to content.</p>

<p>Video fans will have even more reason to celebrate in 2012. With over 1,400 apps globally, Samsung Apps has established itself as the industry's leading TV apps platform, with the broadest range of video content from leading brands. In addition to popular favorites such as CNBC Real Time, ESPN ScoreCenter, Hulu Plus, MTV Music Meter, Netflix and TIME TV, new apps in 2012 include Angry Birds, Bravo, M-GO from Technicolor, The Daily and VH1's 'I Love the 80's Trivia'.</p>

<p>Also new in 2012, Samsung will extend the availability of its successful Media Hub app, currently pre-loaded on Galaxy S smartphones and Galaxy Tabs, to the TV. The app offers consumers all-star entertainment and a diverse selection of movies and TV shows the day after they air, as well as past seasons of the shows. In addition, select movies will be available the day they are released on DVD.</p>

<p>Samsung's new AllShare Play marks an evolution of its AllShare feature. AllShare Play allows users to upload and share multimedia content with AllShare Play compatible devices such as smartphones, tablets, cameras and computers.</p>

<p><br />
<strong>FUTURE PROOFING YOUR TV WITH SMART EVOLUTION</strong></p>

<p>2012 Samsung Smart TVs will be future-proofed for years to come. Thanks to its proprietary system-on-chip technology, Samsung is the only company that can deliver an evolving TV that allows you to easily enjoy the benefits of the latest TV technology year after year without purchasing a brand new set. With a simple slot-in at the back of TV, Samsung's Evolution Kit will bring the latest and greatest TV technology to life.</p>

<p><br />
<strong>SAMSUNG LED TVS DELIVER CRYSTAL CLEAR PICTURE QUALITY WITH INNOVATIVE DESIGN</strong></p>

<p>Samsung's 2012 line of Full HD LED TVs continue to raise the bar in picture quality. Whether users are viewing a detailed shot from a camera or seeing incredible 3D depth, images are breathtakingly real with detailed contrast.</p>

<p>Both the Samsung UNES8000 Smart LED TV and UNES7500 Smart LED TV series offer cutting-edge technologies that deliver clear images in both 2D and 3D.The UNES8000's Micro Dimming Ultimate analyzes the picture in hundreds of pieces to optimize the LED backlight and video signal for each piece in real time, by increasing peak whites in areas of lower gradation. This leads to an overall 20 percent increase in brightness and allow home entertainment enthusiasts to enjoy richer colors, brighter pictures and higher contrast ratios. The technology also eliminates the "halo" effect and image distortion associated with diffused light.</p>

<p>Samsung is taking its minimalist and luxurious design identity further with this year's LED line. The 0.2 inch ultra-slim bezel design, first introduced in 2011, returns for premium models while the rest of Samsung's entire LED line will feature a slim 0.5 inch slim bezel. In all cases, consumers enjoy a larger screen size without increasing the overall size of the TV when compared to conventional models.</p>

<p>The UNES8000 also sports a new metallic U-shape stand that adds to the sophistication this TV brings to any room. The stand's unique, minimalistic design belies its sturdiness and stability. Together, the TV and stand form a harmonious composition of straight lines and curves, appearing as a timeless objet d'art.</p>

<p>Samsung also offers an optional ultra slim wall-mount so that users can show off the TV's slim form factor by hanging it like a picture frame on the wall.</p>

<p><br />
<strong>SAMSUNG'S BEST PLASMA YET – FORM MEETS FUNCTION WITHOUT COMPROMISE</strong></p>

<p>Samsung's PNE80000 Plasma TVs combine a new black bezel design with enhanced display technologies for the most exceptional Plasma TV Samsung has ever created.</p>

<p>The PNE8000 sports a slim 1.5-inch profile and Samsung's Plasma +1 ultra-slim bezel design, which reduces the gap between the bezel and screen content , yielding more screen real estate without increasing the size of the TV. The bezel is a new titan black metal color with a transparent crystal-like border, resulting in a more immersive entertainment experience than ever before.</p>

<p>Users will experience some of the deepest black levels ever achieved on a Samsung TV, thanks to the PNE8000's innovative Real Black Pro Panel. The new panel combines advancements in the structure of the plasma panel with software improvements, yielding blacker blacks, bolder color, higher contrast, and exceptional picture quality. This technological improvement improves black levels by 10%, creating an incredible cinematic viewing experience.</p>

<p>As with the UNES8000 LED TV, the PNE8000 includes Smart TV features, 3D, Smart Interaction and a dual-core processor for multitasking – truly the ultimate in Plasma TVs.</p>

<p>All Samsung 2012 TVs are compliant with the Energy Star&reg; 5.1 guidelines.</p>

<p>CES-specific information may be found at <a target="_blank" href="http://www.samsungces.com/">www.samsungces.com</a>. For more information about Samsung, including press releases, video content and product images, please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a></p>

<p><br />
<strong>About Samsung Electronics Co., Ltd.</strong></p>

<p>Samsung Electronics Co., Ltd. is a global leader in semiconductor, telecommunication, digital media and digital convergence technologies with 2010 consolidated sales of US$135.8 billion. Employing approximately 190,500 people in 206 offices across 68 countries, the company operates two separate organizations to coordinate its nine independent business units: Digital Media &amp; Communications, comprising Visual Display, Mobile Communications, Telecommunication Systems, Digital Appliances, IT Solutions, and Digital Imaging; and Device Solutions, consisting of Memory, System LSI and LCD. Recognized for its industry-leading performance across a range of economic, environmental and social criteria, Samsung Electronics was named the world's most sustainable technology company in the 2011 Dow Jones Sustainability Index. For more information, please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  9, 2012  9:39 PM</b>
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
			<?=getComments(4613)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4613)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/samsung-smart-led-and-plasma-tvs-usher-in-a-new-era-of-connectivity-and-control.php" type="text/javascript" charset="utf-8"></script>
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