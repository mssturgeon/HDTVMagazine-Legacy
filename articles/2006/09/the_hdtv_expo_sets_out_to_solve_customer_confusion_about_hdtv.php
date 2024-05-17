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
		AND e.entry_id = 450";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 450 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 450 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 450";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/09/the-hdtv-expo-sets-out-to-solve-customer-confusion-about-hdtv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 450";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The HDTV Expo Sets Out To Solve Customer Confusion About HDTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The HDTV Expo Sets Out To Solve Customer Confusion About HDTV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The HDTV Expo Sets Out To Solve Customer Confusion About HDTV" />
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
	<title>HDTV Magazine - The HDTV Expo Sets Out To Solve Customer Confusion About HDTV</title>
	<meta name="keywords" content="hdtv magazine, affinity marketing, hdtv expo, dale cripps, ron bruce, hdtv, bruce, marketing, magazine, dale, cripps, affinity, consumer, expo, digital, television, consumers, satellite, ron, transition, industry, our, thehdtvexpo, electronic, path" />
	<meta name="description" content="Dear Readers: 

As most know, we have been at the forefront of the HDTV movement for more than 22 years. From the inception it was recognized by me and a slew of others that the end of the transition to digital television would be more difficult than its start. My hope is that our long-standing dedication and attention to this view can now pay dividends. The press release below is being distributed widely. The ambition of the release is to initiate a spirit of unity in a sustained cross-industry supported educational campaign of American consumers about HDTV and the digital transition. I look forward to any comments you may have. By no means have we concluded just how this educational campaign will be fully fleshed out, nor should we be so set at this stage. For now any preconceived notions that go beyond the essential concept should be set aside in order to make room for the most advanced thinking that can be applied to the mission. - Dale Cripps
 

------------------------------------

&lt;strong&gt;THE HDTV EXPO SETS OUT TO SOLVE CUSTOMER CONFUSION ABOUT HDTV&lt;/strong&gt;

&lt;em&gt;HDTV Magazine and Affinity Marketing Team-up to Launch a Nationwide Series of Seminars to Educate Consumers about HDTV and the Digital Transition&lt;/em&gt;

CEDIA, DENVER, CO -- September 17, 2006 -- HDTV Magazine and Affinity Marketing, LLC have announced today at the CEDIA Trade Show in Denver, CO that their companies have joined forces to host a continuing series of events around the U.S. called  ... The HDTV Expo. These events are to be totally consumer oriented and focused on the digital transition and new television technology.

Dale Cripps, founder and co-publisher of HDTV Magazine, said..." />
	<meta name="title" content="The HDTV Expo Sets Out To Solve Customer Confusion About HDTV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The HDTV Expo Sets Out To Solve Customer Confusion About HDTV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/09/the-hdtv-expo-sets-out-to-solve-customer-confusion-about-hdtv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Dear Readers: 

As most know, we have been at the forefront of the HDTV movement for more than 22 years. From the inception it was recognized by me and a slew of others that the end of the transition to digital television would be more difficult than its start. My hope is that our long-standing dedication and attention to this view can now pay dividends. The press release below is being distributed widely. The ambition of the release is to initiate a spirit of unity in a sustained cross-industry supported educational campaign of American consumers about HDTV and the digital transition. I look forward to any comments you may have. By no means have we concluded just how this educational campaign will be fully fleshed out, nor should we be so set at this stage. For now any preconceived notions that go beyond the essential concept should be set aside in order to make room for the most advanced thinking that can be applied to the mission. - Dale Cripps
 

------------------------------------

&lt;strong&gt;THE HDTV EXPO SETS OUT TO SOLVE CUSTOMER CONFUSION ABOUT HDTV&lt;/strong&gt;

&lt;em&gt;HDTV Magazine and Affinity Marketing Team-up to Launch a Nationwide Series of Seminars to Educate Consumers about HDTV and the Digital Transition&lt;/em&gt;

CEDIA, DENVER, CO -- September 17, 2006 -- HDTV Magazine and Affinity Marketing, LLC have announced today at the CEDIA Trade Show in Denver, CO that their companies have joined forces to host a continuing series of events around the U.S. called  ... The HDTV Expo. These events are to be totally consumer oriented and focused on the digital transition and new television technology.

Dale Cripps, founder and co-publisher of HDTV Magazine, said..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=450', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/09/the-hdtv-expo-sets-out-to-solve-customer-confusion-about-hdtv.php">The HDTV Expo Sets Out To Solve Customer Confusion About HDTV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>September 16, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=8&category=Business & Investment">Business & Investment</a></b>
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
				<p class="editorial">Dear Readers:<br />
<br />
As most know, we have been at the forefront of the HDTV movement for more than 22 years. From the inception it was recognized by me and a slew of others that the end of the transition to digital television would be more difficult than its start. My hope is that our long-standing dedication and attention to this view can now pay dividends. The press release below is being distributed widely. The ambition of the release is to initiate a spirit of unity in a sustained cross-industry supported educational campaign of American consumers about HDTV and the digital transition. I look forward to any comments you may have. By no means have we concluded just how this educational campaign will be fully fleshed out, nor should we be so set at this stage. For now any preconceived notions that go beyond the essential concept should be set aside in order to make room for the most advanced thinking that can be applied to the mission. - Dale Cripps</p>

<p><br />
<p class="prtitle">THE HDTV EXPO SETS OUT TO SOLVE CUSTOMER CONFUSION ABOUT HDTV</p></p>

<center><em>HDTV Magazine and Affinity Marketing Team-up to Launch a Nationwide Series of Seminars to Educate Consumers about HDTV and the Digital Transition</em></center>

<p><b>CEDIA, DENVER, CO -- September 17, 2006</b> -- HDTV Magazine and Affinity Marketing, LLC have announced today at the CEDIA Trade Show in Denver, CO that their companies have joined forces to host a continuing series of events around the U.S. called  ... The HDTV Expo. These events are to be totally consumer oriented and focused on the digital transition and new television technology.</p>

<p>Dale Cripps, founder and co-publisher of HDTV Magazine, said, "These are the first events of their kind dedicated to comprehensive consumer education about HDTV, compatible products and the total switch to all digital TV broadcasting (by law) in February 2009. Exhibits and classes will be open to the public for free. Government agencies, the Consumer Electronics Association (CEA), major manufacturers, and others have all called for a unified effort to smooth the path to this 'watershed event' in U.S. television history. Three HDTV Expo's are being planned to coincide with the 2006 holiday selling season, and there will be others held in up to 50 television markets in 2007 and 2008."</p>

<p>Ron Bruce, CEO and President of Affinity Marketing, LLC, said, "We are pleased to be involved with Dale Cripps and HDTV Magazine. Dale is considered a leading pioneer in the HDTV movement.  If there is anyone who can get everyone to rally around The HDTV Expo, he certainly can." Bruce added, "There is no question that there is an urgent need for consumer education about HDTV and the digital television transition, and that is what we are about."</p>

<p>Bruce continued, "At the fourth annual Display Search HDTV Conference in Beverly Hills, CA (Aug. 15-16, 2006), Sony Electronics President Stan Glasgow told the audience at the Beverly Hilton, 'Some of us have confused ourselves as to what's going on (regarding HDTV). You can imagine how consumers are feeling'. Panasonic Chief Technology Officer, Paul Liao said, 'Consumers routinely misunderstand the benefits and features of HDTV, such as improved resolution, color and brightness'. According to a July 2006 Panasonic survey, 25% of respondents thought that an HDTV automatically included high-definition pictures on all channels, and 30% said that they had no idea what to do with a new HDTV after opening the packaging. They, 'really do need a lot of help', Liao said." And both executives say they would like to help. Both Bruce and Cripps said, "With the costs for serving a confused and bewildered market about to skyrocket we must call upon the entire spectrum of those involved in the digital TV transition to support The HDTV Expo. Broadcasters; HDTV manufacturers; and, the producers of HDTV programming content all need to get involved."</p>

<p>In addition, Brad Anderson, Vice-Chairman/CEO of Best Buy said, "there will be explosive sales (in HDTVs) as we approach 2009 ... it is important to have a path - a graceful path - to get everyone there."</p>

<p>The HDTV Expo event organizer and VP of Business Development at Affinity Marketing, Chris Walczak said, "We are that 'graceful path' to which Mr. Anderson at Best Buy refers. We are excited about providing the best forum for consumers to learn more about HDTV choices; available HDTV content; connection options; and, compatible components. Our creatively planned advertising, promotion and strong collaboration with industry leaders will guarantee success at all of our venues."</p>

<p>Mr. Walczak continued, "In each city, all around the U.S., The HDTV Expo will provide a rotating array of educational classes presented by industry experts to both new HDTV prospects and existing HDTV owners. We want consumers to see demonstrations of the various display technologies and help them make good buying decisions in a 'no-pressure' environment. We believe that The HDTV Expo will serve all the various stakeholders in the transition to all digital TV in February 2009. Studies show that consumers who talk directly to manufacturer reps are a lot more likely to be loyal to those products and purchase them above other brands." Mr. Walczak concluded by saying, "Certainly no manufacturer wants returns due to ill-informed consumers or sales people. That is why we are inviting all HDTV retail sales reps in our selected cities to attend our classes for free as well."</p>

<p>You can call Dale Cripps with questions at HDTV Magazine: 1-800-LOV-HDTV. Or, phone Ron Bruce at Affinity Marketing: (260) 760-7352. A consumer information and registration site is located at: www.thehdtvexpo.com. You can also email Chris Walczak at: chris@thehdtvexpo.com.  Sponsors and exhibitors can go to: www.thehdtvexpo.com/exhibitor for pertinent information.</p>

<p><strong>About HDTV Magazine</strong><br />
HDTV Magazine (www.hdtvmagazine.com) is "the" website for everyone who loves HDTV. In 1984, Dale Cripps founded The HDTV Newsletter, a professional publication distributed into 24 countries to those developing HDTV. In 1998, The HDTV Newsletter evolved into the first online publication dedicated to HDTV -- HDTV Magazine. The mission of this publication is: To educate the public on what HDTV is and give voice to the culture which arises from its engagement. For more than twenty years Dale Cripps has consulted with leaders around the world about the forthcoming HDTV revolution. He was editor-in-chief for HDTV World Review and he continues to serve as the technical editor for Widescreen Review. Mr. Cripps was also technical editor of the popular book HDTV for Dummies. He has written more than 80 published magazine articles on HDTV. He produced and programmed 4 International HDTV conferences (New York, London, Washington, D.C. and Los Angeles) and has been a speaker or moderator at many more. He is the winner of the coveted Best Press Leadership Award from the Academy of Digital Television Pioneers and is a charter member of the Academy. He is the founder of the High-definition Television Association. For these reasons and more, Dale Cripps and HDTV Magazine can be considered the global authority on HDTV.</p>

<p><strong>About Affinity Marketing, LLC</strong><br />
Affinity Marketing (a Bruce Group Company) was founded in 2003 by Ron Bruce, one of the pioneers in the Satellite Television Industry. Today, Affinity Marketing (www.affinitymarketing.biz) consults for and coaches a host of diverse consumer electronic and satellite TV clients, helping them find creative ways to increase sales, improve profits, and operate more efficiently. Mr. Bruce introduced dealer training to the Satellite TV Industry in the early 1980's. Since then, he has conducted hundreds of Dealer Training Seminars all around the U.S. He was elected to the Board of Directors of S.P.A.C.E. (the first satellite TV trade association). Mr. Bruce simultaneously sat on the Advisory Boards of three Multi-National Consumer Electronic Companies. He was nominated to the steering committee that formed the Satellite Broadcasting & Communications Association (S.B.C.A) in mid-1980. Mr. Bruce held Sr. Management positions for over 20 years at two leading Satellite TV and Consumer Electronic Equipment Distributors. He has had several articles published along the way and in 2006 completed a 312 page book titled, The Marketing Bible for Satellite TV and HDTV Dealers. This book is considered by many to be the definitive "how to" manual for marketing advanced consumer electronic products. Mr. Bruce is also a veteran of over sixty (60) industry trade shows. You can contact Affinity Marketing at: 260-760-7352 or e-mail Ron Bruce at: ron@thehdtvexpo.com.</p>

<p><strong>Contacts:</strong><br />
Ron Bruce<br />
Affinity Marketing, LLC<br />
260-760-7352<br />
ron@thehdtvexpo.com</p>

<p>Dale Cripps<br />
HDTV Magazine<br />
(800) LOV-HDTV (568-4388)<br />
dale@hdtvmagazine.com </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>September 16, 2006 12:20 PM</b>
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
			<?=getComments(450)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 450)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/09/the-hdtv-expo-sets-out-to-solve-customer-confusion-about-hdtv.php" type="text/javascript" charset="utf-8"></script>
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