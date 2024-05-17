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
		AND e.entry_id = 198";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 198 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 198 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 198";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/09/program-review-winged-migration-on-hdnet-movies.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 198";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Program Review - Winged Migration on HDNet Movies" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Program Review - Winged Migration on HDNet Movies" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Program Review - Winged Migration on HDNet Movies" />
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
	<title>HDTV Magazine - Program Review - Winged Migration on HDNet Movies</title>
	<meta name="keywords" content="winged migration, jacques perrin, long distance, hdnet movies, birds flight, birds, winged, migration, perrin, film, most, jacques, fly, long, hdtv, flight, distance, earth, time, been, species, winter, seen, nature, people" />
	<meta name="description" content="&lt;em&gt;&quot;{For eighty million years, birds have ruled the skies, seas and earth. Each spring, they fly vast distances. Each Fall, they fly the same route back. This film is the result of four years following their amazing odysseys, in the northern hemisphere and then the south, species by species, flying over seas and continents.&quot;&lt;/em&gt;- Jacques Perrin (from &quot;Winged Migration&quot;)

I was heading to bed when I decided to make one last check of my HDTV channels. &quot;Wow!&quot; I heard myself exclaim, &quot;What is that?&quot;

For the next hour and something I sat transfixed and cheered by one of the most beautiful HDTV presentations I have seen since the opening ceremonies of the Winter Olympics.
" />
	<meta name="title" content="Program Review - Winged Migration on HDNet Movies" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Program Review - Winged Migration on HDNet Movies" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/09/program-review-winged-migration-on-hdnet-movies.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;em&gt;&quot;{For eighty million years, birds have ruled the skies, seas and earth. Each spring, they fly vast distances. Each Fall, they fly the same route back. This film is the result of four years following their amazing odysseys, in the northern hemisphere and then the south, species by species, flying over seas and continents.&quot;&lt;/em&gt;- Jacques Perrin (from &quot;Winged Migration&quot;)

I was heading to bed when I decided to make one last check of my HDTV channels. &quot;Wow!&quot; I heard myself exclaim, &quot;What is that?&quot;

For the next hour and something I sat transfixed and cheered by one of the most beautiful HDTV presentations I have seen since the opening ceremonies of the Winter Olympics.
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=198', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/09/program-review-winged-migration-on-hdnet-movies.php">Program Review - Winged Migration on HDNet Movies</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>September 22, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=3&category=Programming">Programming</a></b>
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
				<p><img alt="programreview4.jpg" src="http://www.hdtvmagazine.com/articles/images/mt/programreview4.jpg" width="606" height="96" /></p>

<p><br />
<strong>Winged Migration</strong></p>

<p><em>"For eighty million years, birds have ruled the skies, seas and earth. Each spring, they fly vast distances. Each Fall, they fly the same route back. This film is the result of four years following their amazing odysseys, in the northern hemisphere and then the south, species by species, flying over seas and continents."</em>- Jacques Perrin (from "Winged Migration")</p>

<p>_____________________________________________________________________</p>

<p>I was heading to bed when I decided to make one last check of my HDTV channels. "Wow!" I heard myself exclaim, "What is that?"</p>

<p>For the next hour and something I sat transfixed and cheered by one of the most beautiful HDTV presentations I have seen since the opening ceremonies of the Winter Olympics. The later remains my benchmark from which all other HDTV programs are measured, but "Winged Migration" has equaled that and added a new dimension as well. You still have time to see this program on HDNet Movies at 1:15 PM ET / 10:15 AM PT - Sun, Sep 25th and 7:15 AM ET / 4:15 AM PT - Mon, Sep 26th.  </p>

<p>DON'T MISS IT. </p>

<p>Why?</p>

<p>This award winning 89 minute documentary from director, Jaques Cluzaud with narration by Jaques Perrrin, would have been clicked away had it not been for the stunning HDTV which carried it. No doubt in theaters, where it was first seen, it did captivate through imagery. I doubt it would have been of much interest on NTSC, the old standard we are leaving quickly, but with HDTV I was transported to the opposite poles of the world most all from a bird's eye view. The raw power of this presentation was given strong emphasis by the accompanying audio. If this production represents the potential of those that will regularly come from artists who love their subjects and the medium we mutually love we are in for one hell-of-a ride, perhaps even more than we deserve (but we'll take it anyway)! </p>

<p>Some comments I found on the web about this production: </p>

<p>"Long one of France's most respected producers (Academy Award Winners "Z" and "Black and White in Color") and actors ("Z," "Cinema Paradiso," "The Young Girls of Rochefort," "Donkey Skin" and "The Brotherhood of the Wolf"), Jacques Perrin has more recently had a highly successful career creating films about nature, including "Le Peuple Singe" (monkeys) and "Microcosmos" (insects) and set in exotic locales ("Himalaya"). </p>

<p>Now with his penultimate film "Winged Migration" Perrin takes on his greatest challenge yet: exploring the mystery of birds in flight. Five teams of people (more than 450 people, including 17 pilots and 14 cinematographers) were necessary to follow a variety of bird migrations through forty countries and each of the seven continents. The film covers landscapes that range from the Eiffel Tower and Monument Valley to the remote reaches of the Arctic and the Amazon. All manner of man-made machines were employed, including planes, gliders, helicopters, and balloons, and numerous innovative techniques and ingeniously designed cameras were utilized to allow the filmmakers to fly alongside, above, below and in front of their subjects. The result is a film of staggering beauty that opens one's eyes to the ineffable wonders of the natural world."</p>

<p> <img alt="albatross800.55.jpg" src="http://www.hdtvmagazine.com/articles/images/mt/albatross800.55.jpg" width="440" height="330" /></p>

<p><br />
"Winged Migration is a glorious celebration of birds in flight, conveying the beauty, the amazing feats of strength and the endurance of their long distance journeys. Here is one to stir your soul!"<br />
-- Frederic and Mary Ann Brussat,</p>

<p>"Nature films are assumed to be plotless, but Winged Migration is full of major and minor narratives, from the basic struggle of a snow goose making its migratory trek from the Gulf to the Yukon, to sequences of decidedly high drama."<br />
-- John Anderson, NEWSDAY</p>

<p>"Though you learn less about the various species Perrin circled the globe to document than you might from an afternoon with Animal Planet, you become intensely chummy with the process and labor of flying."<br />
-- Michael Atkinson, VILLAGE VOICE</p>

<p>   "A fascinating motion picture."<br />
-- James Berardinelli, REELVIEWS</p>

<p>   "Winged Migration is one for the birders, or for all other people who have stood still and forgotten themselves as they watch a sparrow make its way through the world."<br />
-- Ty Burr, BOSTON GLOBE</p>

<p>"Perrin's film assembles discontinuous but overlapping visual wonders into a vaguely mystical ode to the endless variety and timeless rhythms of life."<br />
-- Bob Campbell, NEWARK STAR-LEDGER</p>

<p>   "The movie offers ample amounts of power, poetry and even humor."<br />
-- Robert Denerstein, DENVER ROCKY MOUNTAIN NEWS</p>

<p>   "There are sights here I will not easily forget."<br />
-- Roger Ebert, CHICAGO SUN-TIMES</p>

<p>   "This is a movie to be seen and savored. And savored again."<br />
-- Eleanor Ringel Gillespie, ATLANTA JOURNAL-CONSTITUTION</p>

<p>   "There's not a single special effect, and yet the visuals are spectacular."<br />
-- Rick Groen, GLOBE AND MAIL</p>

<p>   "Provides such an intense vicarious experience of being a flapping airborne creature with the wind in its ears that you leave the theater feeling like an honorary member of another species."<br />
-- Stephen Holden, NEW YORK TIMES</p>

<p><br />
"Earthbound, watching the birds fly across the sky, we undertook this film. We had to go higher, nearer the birds, within striking distance of the stars. How could we manage it? Man has dreamt of birds since the beginning of time. How to imagine being among the first to transform this dream into reality? I will always treasure the memory of the first time we achieved this. The cameraman was following the movements of the geese, with one hand the assistant pushed away those who came too near the camera: the whole spool of film ran out. Radiant, tears in their eyes, they looked at me, speechless, motionless. Their mastery and the technical result were of minor importance, they had been in the confidence of the birds in flight. What if, for the space of a year, we no longer waited for the seasons, what if we embarked on the most fabulous of journeys, what if, abandoning our towns and our countryside, we went on a tour of the planet? What if we understood that our borders did not exist, that the earth is a one and only space and what if we learned to be free as birds?"<br />
-Jacques Perrin</p>

<p><img alt="crane800.55.jpg" src="http://www.hdtvmagazine.com/articles/images/mt/crane800.55.jpg" width="440" height="330" /></p>

<p><br />
See it on HDNet Movies </p>

<p>About the Birds<br />
"Winged Migration" is a film dedicated to birds and their displacements according to the seasons. For every one of us, these winged creatures are among the most fascinating, the most shrouded in mystery and poetry. Among all the vertebrates, they are the only ones to have mastered the open sky. Through a series of miracles of evolution, they have conquered all the skies by equipping themselves with remarkably adapted organs, wings covered with feathers, powerful muscles to move them, the heart of a long distance runner. They combine a minimum of weight with maximum strength and ease. They make up one of the most extraordinary successes of evolution, after having come from a reptilian ancestor crawling on the ground. Their flight gives them an accurate place in the biosphere; no other animal has ever come to contest this. Their exceptional faculties have allowed them to answer annual fluctuations in the climates by finding refuge during the winter far from their homelands where they breed. They are the undeniable champions among all the long distance migrants. The life of many of them is spent in long peregrinations between the place where they nest and the one where they live during the winter. Many change continents. Some fly around the earth in untiring turns. And this in spite of the risks which await them. In order to better face them, even the most solitary gather together in gigantic groups, one of the great shows of nature. To perform these exploits, as in anticipation of the efforts awaiting them, the birds accumulate reserves of fats before their departure. To guide themselves, they have discovered astronomical bearings, observing the sun and the stars. They perceive the magnetic field of the earth as the needle of a compass. They have an internal clock which gives them the time and the season of the year. The hereditary innate and a part of apprenticeship with their elders, informs them on the term of their voyages and the skyways to reach them. They know how to cope with weather conditions in an uninterrupted dialogue with the wind. "Winged Migration" relates the saga of these myriad of birds all along their migration routes.<br />
-Professor Jean Dorst, French Academy of Sciences</p>

<p>Filmmakers  -  JACQUES PERRIN<br />
Director  -  JACQUES CLUZAUD<br />
Co-Directors  -  MICHEL DEBATS<br />
Narrator  -  JACQUES PERRIN</p>

<p></p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>September 22, 2005  4:50 PM</b>
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
			<?=getComments(198)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 198)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/09/program-review-winged-migration-on-hdnet-movies.php" type="text/javascript" charset="utf-8"></script>
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