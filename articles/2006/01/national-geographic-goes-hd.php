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
		AND e.entry_id = 300";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 300 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 300 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 300";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/01/national-geographic-goes-hd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 300";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download National Geographic Goes HD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="National Geographic Goes HD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="National Geographic Goes HD" />
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
	<title>HDTV Magazine - National Geographic Goes HD</title>
	<meta name="keywords" content="hdtv magazine, john ford, national geographic, standard def, ford yes, hdtv, john, magazine, ford, our, say, national, geographic, get, much, ngc, programming, standard, def, people, bandwidth, time, know, network, those" />
	<meta name="description" content="The month marks both the fifth anniversary of the National Geographic Channel as well as the birth of NGC-HD, the much anticipated high-definition version. You can now receive all NGC in HDTV... well, you can if you do your part in calling both local cable and satellite operators with a demand that they carry it. (Call 1-877-77-NGCHD for more information).

It's been our tradition to interview the network brass who have had the courage to launch a HD channel. For the second time in our history we called upon Mr. John Ford, Executive Vice President NGC-HD programming. John shouldered much of the responsibilities as he engineered the launch of NGC-HD. I say &quot;for the second time&quot; because he is in the rarefied class of people who have launched more than one HDTV network, his first being the Discovery HD Theater back in 2001." />
	<meta name="title" content="National Geographic Goes HD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="National Geographic Goes HD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/01/national-geographic-goes-hd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The month marks both the fifth anniversary of the National Geographic Channel as well as the birth of NGC-HD, the much anticipated high-definition version. You can now receive all NGC in HDTV... well, you can if you do your part in calling both local cable and satellite operators with a demand that they carry it. (Call 1-877-77-NGCHD for more information).

It's been our tradition to interview the network brass who have had the courage to launch a HD channel. For the second time in our history we called upon Mr. John Ford, Executive Vice President NGC-HD programming. John shouldered much of the responsibilities as he engineered the launch of NGC-HD. I say &quot;for the second time&quot; because he is in the rarefied class of people who have launched more than one HDTV network, his first being the Discovery HD Theater back in 2001." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=300', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/01/national-geographic-goes-hd.php">National Geographic Goes HD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>January 22, 2006</b>
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
				<p><strong>"This of all brands should be in HD." </strong></p>

<p>National Geographic<br />
Interview: Mr. John Ford, Executive Vice President of Programming</p>

<p>The month marks both the fifth anniversary of the National Geographic Channel as well as the birth of NGC-HD, the much anticipated high-definition version. You can now receive all NGC in HDTV... well, you can if you do your part in calling both local cable and satellite operators with a demand that they carry it. (Call 1-877-77-NGCHD for more information).</p>

<p>It's been our tradition to interview the network brass who have had the courage to launch a HD channel. For the second time in our history we called upon Mr. John Ford, Executive Vice President NGC-HD programming. John shouldered much of the responsibilities as he engineered the launch of NGC-HD. I say "for the second time" because he is in the rarefied class of people who have launched more than one HDTV network, his first being the Discovery HD Theater back in 2001. </p>

<p>HDTV Magazine has always believed that the payoff for the expensive HDTV investment comes in the form of a cultural enrichment springing out of a clearer view and understanding of the real world. Certainly, penetrating programming is the chief requisite in fulfilling this promise. Scholars say that a successful world depends upon new growth in mutual understanding. Others emphasize the incalculable benefits produced by the introduction of beauty. "Creating understanding" while revealing intrinsic beauty has been a mission of National Geographic from its inception and now with the addition of HDTV the growth of both of these sympathetic vibrations is certain to impact our lives far into the future. We can and should be proud-professionals and consumers alike-to have played a part in this supra human challenge of giving birth to HDTV and, perhaps, a whole new era in which life once again seems to flourish. _ Dale Cripps</p>

<p><br />
NOW OUR INTERVIEW ... </p>

<p><strong>HDTV Magazine</strong>: When did HDTV appear on your radar?</p>

<p><strong>John Ford</strong>: It was clearly on my radar when I was launching Discovery HD Theater for Discovery. It was hovering in the background here when I joined National Geographic. Around the middle-to-the end of 2004 I was coming around to the fact that NGC needed to be an HDTV channel. We laid the ground work in February 2005, pulled the trigger and said to our producers that baring some compelling reason to do otherwise new projects from that date on must be in HDTV.  </p>

<p><strong>HDTV Magazine</strong>: Did I read correctly that you have 90% of the content for this year already in HD?</p>

<p><strong>John Ford</strong>: Well, 90% of our prime time in the first quarter of 2006 is in HD. But that is not all. Some of our daytime programming has been produced anamorphically in Digibeta (a standard def digital format).  Most of what we run in daytime, of course, is a repeat of prime time programming. There will be some early morning shows that were produced in January of last year (prior to the HD directive). They will be mostly in Digibeta anamorphic standard def. Being anamorphic the wide screen (without stretching) is filled.  Interestingly enough we have more HDTV content (as a percentage) than does ESPN. </p>

<p><strong>HDTV Magazine</strong>: It would seem pretty obvious that the content from National Geographic is perfect for HDTV.</p>

<p><strong>John Ford</strong>: We are shooting authentic things in the real world, be that in nature, science, history or venture programs. The upgrade in the experience to high-definition from SDTV is greater for our type of programming than that for a movie or "how to" shows. The picture quality is now a part of the appeal of programming in our genre more than it might be for some others. Whether people or places or animals ... you can see more and see it better. It is far more like "being there" than with standard def. I think that is a big benefit.</p>

<p><img alt="nghdimages.jpg" src="http://www.hdtvmagazine.com/images/articles/nghdimages.jpg" width="530" height="301" /></p>

<p><strong>HDTV Magazine</strong>: Do you have any special programs on your schedule that are going to be "knock outs"?</p>

<p><strong>John Ford</strong>:  The soonest is "Relentless Enemies". That is a two hour special shot on a little island near Botswana, Africa. You have trapped together there on this island several herds of buffalo and a pride of lions. Because the lions are always chasing down buffalo they get a great deal of exercise. Because they are eating buffalo they get a great deal of nutrition. They look like lions on steroids! It's an incredible show produced cinematically by a team who previously had only shot on film. They were reluctant to be brought into the HD world but I persuaded them. Now they love it. We may be able to get you an interview with them before that show airs. They will tell you how beautiful HD is and how wonderful it is for them. They are based in South Africa. </p>

<p>There is also an outstanding co-production on the Galapagos Island that we are doing with a British company.  To your point earlier about the social benefit of HD, while we are not getting much look here at other human societies, we are getting a fantastic look at rare animal behaviors and life. A lot of this never-before-seen animal behavior comes only from the scope and clarity you get from HDTV. </p>

<p><strong>HDTV Magazine</strong>: What format are you favoring for distribution and capture?</p>

<p><strong>John Ford</strong>: We transmit in 720p. Those who shoot for us can use 720p or 1080i production equipment.</p>

<p><strong>HDTV Magazine</strong>: Are you encouraging the use of those small prosumer HDTV DV cameras (Sony, Cannon, Ikegami)?</p>

<p><strong>John Ford</strong>: We are not encouraging it. We are saying to producers that if you have a reason not to hang a $120,000 HD camera out the window of a speeding car holding it next to the wheels or over a cliff shooting a bird's next, you need a $3500 to $5000 camera. We will let you do that. But don't think of using it for other than those extreme occasions. We have one show that uses at one point 14 cameras to capture a collision between two cars. We wanted to see every angle. The rest of the show is in HD and we said it was OK to use those prosumer HD cameras much as you might with a home video. We could not justify the renting of 14 professional HD cameras to shoot that one segment. But the primary and secondary cameras are HDTV, even for this special scene.</p>

<p><strong>HDTV Magazine</strong>: Was the choice of 720p transmission influenced by Fox Network Group (in joint-venture with National Geographic television)? </p>

<p><strong>John Ford</strong>: Fox handles the uplink for us and they transmit in 720p, so it only made sense that our choice be 720p.</p>

<p><strong>HDTV Magazine</strong>: Are you aware of the manufacturer's competitive drive to produce and market 1080p displays?</p>

<p><strong>John Ford</strong>: Yes, we are aware that people are moving into 1080p. Certainly, we think more lines in progressive scan is a positive move. The only issue (remaining) is bandwidth.</p>

<p><strong>HDTV Magazine</strong>: Is that (1080p) something you would trial after if the population of those monitors grew?</p>

<p><strong>John Ford</strong>: That is hard to say. That would be a Fox corporate decision. They have a lot of sports to deal with and a lot of multi-camera shoots plus bandwidth restrictions. As we get to better bandwidth efficiency then the restrictions start to loosen up and you can more readily say, "let's go to 1080p" and not worry too much. But right now bandwidth is enough of an issue that I think 720p-given that it is progressive scan with a whole lot of lines going into it- is a very good choice.</p>

<p><strong>HDTV Magazine</strong>: There is a growing cadre of video aficionados who have not been particular about much of anything until HDTV came along. They are now out counting pixels.  They keep straining for every last bit of system performance that can be had.  I can already predict that many from that group will say, "Oh, they have gone to 720p. What a catastrophe." </p>

<p><strong>John Ford</strong>: We can certainly say that it is better than standard def. There are others who have embraced 720p without much comment. While you can switch back and forth between formats and see some differences, the 720p is such a quantum leap above standard def that I am confident people will be satisfied. Once they no longer are going back and forth trying to count the pixels, which to me defeats the purpose of enjoying your TV time,  I am sure they will enjoy what we have to offer them. When you make the Galapagos Island look like a movie, you got something going. </p>

<p>We know that people are excited about the National Geographic brand in HD. All the surveys, and certainly anecdotally, people say, "This of all brands should be in HD." Consumer demand is already there. All we need to do is to get the word out and let them know that the channel is up and running. If they don't get it, call their cable and satellite provider.</p>

<p><strong>HDTV Magazine</strong>: I note on your <a href="/cgi-bin/ntlinktrack.cgi?http://channel.nationalgeographic.com/channel/hd/">web site</a> that you have a number to call for further information on how the public can reach and petition their carriers. </p>

<p><strong>John Ford</strong>: Yes, that will get you in touch with your cable operators so you can call them and ask, "Do you carry NG-HD?" If not, then say, "I would like you to carry it." That kind of consumer interest does have an impact upon local market decisions. Bandwidth is scarce. It is always a choice as whether to use that bandwidth for telephony, other digital channels, etc. We know that the HDTV community loves their HDTV but there are just not enough channels. This is one way to stand up and say, "You know what; we want the National Geographic channel. If you are making room for whatever service it is, make sure you have room for NGC-HD.</p>

<p><strong>HDTV Magazine</strong>: What carriage do you presently have?</p>

<p><strong>John Ford</strong>: To answer this I must refer you to the person handling affiliate sales, but I do know that our affiliates are working on deals with several distributors. </p>

<p><strong>HDTV Magazine</strong>: You presently have 56 million reached in standard def. What is your target with NGC-HD?</p>

<p><strong>John Ford</strong>: We hope in one year to be in half of the HD homes. </p>

<p><strong>HDTV Magazine</strong>: As you may have read there are a great many TV households who have HDTV capable monitors but without a HDTV signal to drive them. They either don't know or care about true HDTV. Many in the industry say this is a matter of continuing education of the public. Can we count you in on the educational process to the American public?</p>

<p><strong>John Ford</strong>: On our standard network, which is a simulcast with our HD network, there will be an on-screen notification stating whenever a program is available in HDTV (which is most of them).  Then we put up that 1 877 number.</p>

<p><strong>HDTV Magazine</strong>: Is money-making important for your move to HDTV?</p>

<p><strong>John Ford</strong>: It is only if you take the long view.  Presently it is more of an expense and you can't really trace a whole lot of revenue back to it. But in the long run we believe it will pay off.  </p>

<p><strong>HDTV Magazine</strong>: The long run ... .is that five years? Ten years? Three years? </p>

<p><strong>John Ford</strong>: I think we will see tangible results in three year's time. </p>

<p><strong>HDTV Magazine</strong>: Thank you and good luck with this very significant launch.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>January 22, 2006 10:16 PM</b>
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
			<?=getComments(300)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 300)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/01/national-geographic-goes-hd.php" type="text/javascript" charset="utf-8"></script>
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