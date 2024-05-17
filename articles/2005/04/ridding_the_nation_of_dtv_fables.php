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
		AND e.entry_id = 12";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 12 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 12 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 12";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 12";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Ridding the Nation of DTV Fables" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Ridding the Nation of DTV Fables" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Ridding the Nation of DTV Fables" />
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
	<title>HDTV Magazine - Ridding the Nation of DTV Fables</title>
	<meta name="keywords" content="nab mstv, dtv transition, percent requirement, house representativeswashington, consumer electronics, dtv, local, television, public, sets, percent, analog, nab, broadcasters, transition, set, cea, mstv, consumers, spectrum, air, free, congress, consumer, cable" />
	<meta name="description" content="Under any scenario conceivable there will not be a successful termination of analog services as long as there are any with a dependancy upon those signals for local news (or even entertainment). If someone is deluded enough to insist that it does happen I want the pitchfork concession Washington. Nothing riles up the public more than the loss of their TV services. The industry is repleat with stories of outages where the wrath of god decended upon the service provider until things were restored. One cable company had been testing a new channel in preparation for placing one of the music services on it. To test the video an engineer pointed a camera on a fish tank and sent the signal down that newly created channel to the subscribers. The images of fish swiming around on your television set went on for several weeks. The day the music channel replaced the fish tank caused a meltdown of the cable company's customer service department as outraged viewers demanded that they get their fish back. The 911 system goes into overload everytime a cable system breaks down. You don't mess with what people have become familiar without careful preparation, which at minimum requires a complete education of the viewer.
" />
	<meta name="title" content="Ridding the Nation of DTV Fables" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Ridding the Nation of DTV Fables" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Under any scenario conceivable there will not be a successful termination of analog services as long as there are any with a dependancy upon those signals for local news (or even entertainment). If someone is deluded enough to insist that it does happen I want the pitchfork concession Washington. Nothing riles up the public more than the loss of their TV services. The industry is repleat with stories of outages where the wrath of god decended upon the service provider until things were restored. One cable company had been testing a new channel in preparation for placing one of the music services on it. To test the video an engineer pointed a camera on a fish tank and sent the signal down that newly created channel to the subscribers. The images of fish swiming around on your television set went on for several weeks. The day the music channel replaced the fish tank caused a meltdown of the cable company's customer service department as outraged viewers demanded that they get their fish back. The 911 system goes into overload everytime a cable system breaks down. You don't mess with what people have become familiar without careful preparation, which at minimum requires a complete education of the viewer.
" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=12', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php">Ridding the Nation of DTV Fables</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>April 27, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p>The following letter from the National Association of Broadcasters' CEO, <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a> is in response to the High-Tech DTV Coalition seeking to hurry a "date certain" for the shut off of the analog TV channels. The government along with the powerful voices in industry are eager to get those channels back for both new business applications and for the money they will bring to the general fund of the U.S. Government. Regardless of what is said or what rules and legislation is written and passed, the determination of when the spectrum can be be returned is entirely "public certain", i.e., established by the public through their actions. Have they, the public, done their part in this transition? Do they understand what role it is they are to play, and why? If they have not done their part, will they sit still for having their favorite TV public services discontinued because of a distant lust over the new use and revenue from the analog spectrum?</p>

<p>Under any scenario conceivable there will not be a successful termination of analog services as long as there are any with a dependancy upon those signals for local news (or even entertainment). If someone is deluded enough to insist that it does happen I want the pitchfork concession Washington. Nothing riles up the public more than the loss of their TV services. The industry is repleat with stories of outages where the wrath of god decended upon the service provider until things were restored. One cable company had been testing a new channel in preparation for placing one of the music services on it. To test the video an engineer pointed a camera on a fish tank and sent the signal down that newly created channel to the subscribers. The images of fish swiming around on your television set went on for several weeks. The day the music channel replaced the fish tank caused a meltdown of the cable company's customer service department as outraged viewers demanded that they get their fish back. The 911 system goes into overload everytime a cable system breaks down. You don't mess with what people have become familiar without careful preparation, which at minimum requires a complete education of the viewer.</p>

<p>One could foresee the day and hour when some experimentation of the shut off takes place. For the sake of example lets say that Redding, California with their 6 analog channels gets the order that on December 31, the day before the Rose Bowl, all channels are shut down. Whatever reaction that comes from that market will be a legend which all others will see in their mind's eye from that moment on. While the law may force the channel dark the last telephone number on the analog screen will be that of the Congressman of the district and the Senators of the state.<br />
To do my part in helping this transition become the success it can I have invited both <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>, CEO of the National Association of Broadcasters, and Gary Shapiro, CEO of the Consumer Electronics Association, to produce a BLOG for this web site. They are the first of many who have been or will be extended the same invitation<br />
<strong></strong><br />
<strong>The purpose of the invitation is three fold:</strong><br />
<strong></strong><br />
<strong>1. The arguments for the issues need to be articaulated clearly in full public view. </strong><br />
<span style="color:#666666;"><em><strong>(No remaining issue in the DTV transition is free of public impact and the leaders now must address an informed public in a candid manner.)</strong><br />
<strong></strong></em><br />
</span><strong>2. The resultant attention from both press and television to this web site will cause greater public participation and thus a greater promotion for H/DTV is made possible <span style="color:#666666;"><em>(With the clarifications given in these BLOGs an accelleration of the DTV transition can be expected.)</em></span></strong><span style="color:#666666;"><em><br />
<strong></strong></em></span><br />
<strong>3. The presence of these two national leaders will encourage leaders from other industries to participate with their own BLOGSs with the aim to rid the nation of DTV myths and fables. </strong><br />
<strong></strong><br />
<strong></strong><br />
The following is a letter from <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>, president of the National Association of Broadcasters in Washinton, D.C. to the Congressman most responsible for crafting legislation for this thorny issue. In coming days I will present all of the influences reaching Congress on this matter.</p>

<p>Here is that letter...</p>

<p>April 27, 2005</p>

<p><strong>The Honorable Joe BartonChairmanHouse Committee on Energy and CommerceU.S. House of RepresentativesWashington, DC 20515</strong></p>

<p>The Honorable John DingellRanking MemberHouse Committee on Energy and CommerceU.S. House of RepresentativesWashington, DC 20515</p>

<p>The Honorable Fred UptonChairmanHouse Subcommittee on Telecommunications &the InternetU.S. House of RepresentativesWashington, DC 20515</p>

<p>The Honorable Ed MarkeyRanking MemberHouse Subcommittee on Telecommunications &amp;the InternetU.S. House of RepresentativesWashington, DC 20515</p>

<p>Dear Congressmen:</p>

<p>The Computer Systems Policy Project (CSPP) has written to ask that you pass legislation aimed at "completing the DTV transition as soon as possible." Local broadcasters are strongly supportive of efforts to bring this transition to a timely conclusion, and NAB stands ready to work with this Committee to accomplish that goal.</p>

<p>However, we also agree with the many members of Congress who have expressed concern that a premature end to analog television would be terribly disruptive to millions of Americans. Our viewers are your constituents, and we believe that an overriding priority in ending this transition must be the protection of consumers against losing access to local television.</p>

<p>To date, broadcasters have invested billions of dollars and risked the most to complete the DTV transition. According to the FCC, there are now 1,497 local stations on-air in digital operating in all 211 television markets. In addition, 87.54 percent of the more than 106 million U.S. TV households are in markets with five or more broadcasters airing DTV; another 69.23 percent of all homes are in markets with eight or more broadcasters sending digital signals. Moreover, the amount of high definition television offered by broadcast networks and local TV stations has soared. Clearly, local broadcasters have upheld our commitment to make digital television a reality.</p>

<p>As these hundreds of local broadcasters are transmitting in both analog and digital signals, they are paying dual operating costs without any additional revenue source. Clearly, we have every incentive to see the transition ended and the analog spectrum freed for other uses.</p>

<p>However, as a matter of public policy, the corporate financial interests of a handful of technology companies should not trump the needs of American television viewers. Make no mistake: a premature end to analog television could leave millions of Americans without access to free local TV station signals. The harm to these consumers -- a disproportionate number of whom come from poor and minority households -- must be considered against the purely parochial interests of high-tech companies hoping to profit from new uses of this spectrum. Today, 73 million television sets are in use in households that rely on free, over-the-air broadcasting as their only source for TV reception. Moreover, a recent study by the GAO found that 20.5 million TV households rely exclusively on over-the-air TV reception. The study also found that 28 percent of Hispanic households rely solely on over-the-air television, and that one-half of households where the head of the home is over 50 years of age and the annual income is less than thirty thousand dollars are over-the-air reliant. It is critically important that these Americans -- and those that may have second and third over-the-air TV sets in homes wired for cable and satellite -- not be disenfranchised from access to local television.</p>

<p>CSPP wrongly asserts that local stations' occupation of TV spectrum band is hindering the rollout of public safety communications interoperability. The fact of the matter is that in the ten cities most likely to be struck by a terrorist attack, the communications interoperability issue has been resolved. In September 2004, USA Today reported that then-Homeland Security Secretary Tom Ridge announced that in 10 of the cities considered at highest risk for a terrorist attack, firefighters, police and other emergency responders in charge during a disaster can now talk to each other to coordinate a quick response. (<a href="http://www.nab.org/xert/corpcomm/092704USAToday.asp">See attached article</a>). While expansion of public safety communications interoperability remains an important policy goal, CSPP appears to be overstating the problem for its own ulterior motives. It goes without saying that local broadcasting remains a primary "first responder" during times of crisis. Citizens know that local TV stations provide lifeline information during emergency weather situations, Amber Alerts, terrorist attacks, and other disasters. Local television stations also provide valuable services during good times, offering news and public affairs programming that citizens rely upon to be connected to their communities. We cover the local sports that communities rally around. Our partnerships with charities raise billions of dollars for non-profits that improve and strengthen communities. In short, local broadcasting has always been integral to the fabric of the American life.</p>

<p>As broadcasters, we are no strangers to technological innovation. The DTV transition represents a revolutionary milestone in broadcasting, and it will further enhance our ability to serve your constituents with compelling free local content.</p>

<p>In 1996, Congress and broadcasters entered into a public-private partnership aimed at bringing the next generation of free television to the viewing public. Congress, broadcasters and viewers are on the precipice of seeing this ambitious undertaking completed. As we near completion of this historic journey, we urge Congress to reject approaches that focus myopically upon clearance of spectrum to benefit the narrow interests of a small group of corporations. The overriding goal must be a seamless DTV transition that does NOT leave millions of Americans stranded from access to free TV.</p>

<p>NAB looks forward to working with the Committee as you fashion a solution that will end the transition, while ensuring that Americans can enjoy continued access to free local television.<br />
Sincerely,</p>

<p><br />
This article appeared in TV Technology...<br />
<strong></strong><br />
<strong><span style="font-size:130%;">NAB, MSTV Oppose DTV Tuner Mandate Delay</span><br />
</strong><br />
NAB and the Association for Maximum Service Television (MSTV) are urging the FCC to reject a proposal by the Consumer Electronics Association (CEA) to eliminate the requirement that 50 percent if all television sets shipped after July 1, 2005 have DTV tuners.<br />
NAB President/CEO <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a> accused the CEA of perpetuating fraud on the American consumer.</p>

<p>"CEA member companies continue to sell millions of analog TV sets every year, while refusing to tell consumers that these sets will soon be obsolete or need converters to work in the digital era," Fritts said.</p>

<p>"Every analog set sold to a consumer willing to purchase a new television set necessarily decreases the likelihood that a given market will soon reach the 85 percent statutory threshold. Such delay, aside from depriving consumers of the benefits of digital technology, will impede the return of analog spectrum allotted for future use by first responders and commercial wireless providers," according to NAB and MSTV.</p>

<p>Both groups also point to Congressional actions in their comments, noting, recent press reports, "House Commerce Committee Chairman Joe Barton has stated his intention to ask the Commission to accelerate the deadline for the final DTV tuner mandate (i.e., the date by which all sets sold that are 13 inches or greater in size must include a DTV tuner) to 2005 or early 2006. Against this backdrop, the Commission should not take any action that could delay consumers' acceptance of DTV technology."</p>

<p>NAB and MSTV included results of a study showing that in 54 percent of U.S. homes, the largest TV set is between the 25 and 35-inch screen size covered under the July 2005 50 percent rule.<br />
I presented CEA's side of argument in the <a href="http://www.tvtechnology.com/dlrf/issue.php?w=2005-02-22">Feb. 22, 2005 RF Report</a> . One of the arguments CEA made for delaying the 50 percent date and accelerating the 100 percent date from July 2006 to March 2006 was that this would cause retailers to over-order analog sets, creating a surplus of DTV sets. In their comments, "MSTV and NAB do not disagree that phased implementation of a given size of receiver may be inefficient from an enforcement standpoint. Nevertheless, the number of sets that become available to consumers while the 50 percent requirement is in effect would certainly be greater than if there were no mandate during that time. Some is better than none, and CEA-CERC should not be allowed to make the perfect the enemy of the good. Also, turning the 50 percent requirement into a 0 percent requirement is not the only, or even most logical, option should the Commission conclude that a phased approach to 25-35 inch sets is inefficient."</p>

<p>CEA recommends that Congress enact a 100 percent requirement effective July 2005 to avoid the inefficiencies of a phased approach and avoid harm to public interest.<br />
NAB and MSTV note that the rationale for the 50 percent requirement was to give manufacturers time to "develop efficiencies in production" of DTV sets and keep prices reasonable and it no longer applies. "The innovations of some manufacturers have achieved those efficiencies ahead of schedule; thus, it is unlikely that consumers will see an appreciable 'spike' in prices of 25-35 inch receivers if manufacturers are required to produce only DTV sets in that category by July 2005. For example, RCA has announced a 27-inch set, available this summer, which will sell for less than $300. In short, the economic thesis that underlies the CEA-CERC petition is simply denied by the reality of the new RCA sets." NAB and MSTV explain the consumers expect their TV sets to be able to pick up all broadcast signals.</p>

<p>NAB and MSTV complained that retailers, with support from CEA, have not consistently explained the importance of DTV tuner functionality to their customers. As a result, "Not surprisingly, consumer confusion has resulted. In most major electronic retail outlets throughout the country, it is next to impossible to find an in-store display of off-air DTV reception and capability. As recently as January, at the 2005 International Consumer Electronics Show in Las Vegas, CEA introduced a brochure called 'The 3 Simple Steps to HDTV.' Billed on its cover as a brochure that is designed to make it easy for you to learn the simple steps to get the full high definition experience in your home, the words 'broadcast', 'antenna' or 'over-the-air' do not even appear in this brochure, as if terrestrial broadcasting of HDTV programming did not exist. Step 2 of the brochure, titled 'Get the Programming,' brazenly states, '[c]all your local cable or satellite provider to order HDTV programming - the only way to get the full HD movie theater experience in your home."</p>

<p><br />
I urge you to take a look at the <a href="http://www.mstv.org/docs/DTV%20Tuner%20-%20Joint%20Comments%20MSTV_NAB%20[FINAL%20-%20PDF].PDF">Joint Comments of MSTV and NAB </a>.<br />
This is an important issue for broadcasters, especially with Congress considering shutting down analog TV broadcasting possible as soon as Dec. 31 next year. I'd be interested in readers' comments about their experiences purchasing set-top boxes or DTV sets for OTA reception at consumer electronics stores. Was it possible to buy a DTV set or set-top box without getting a sales pitch for a satellite HDTV service? At NAB I heard of a case where an electronics store refused to sell the customer an ATSC set-top box unless they also purchased a DBS HDTV package! Somewhat contradictory to this, I also heard Wal-Mart was prevented from selling the USDTV HDTV set-top boxes without the USDTV subscription package at a higher price than those purchased with the subscription. The USDTV subscription provides cable TV programming for a fee using broadcast DTV spectrum.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>April 27, 2005  1:25 PM</b>
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
			<?=getComments(12)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 12)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php" type="text/javascript" charset="utf-8"></script>
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