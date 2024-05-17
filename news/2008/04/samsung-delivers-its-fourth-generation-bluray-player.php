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
		AND e.entry_id = 1363";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1363 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1363 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1363";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/04/samsung-delivers-its-fourth-generation-bluray-player.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1363";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Delivers its Fourth Generation Blu-ray Player" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Samsung Delivers its Fourth Generation Blu-ray Player" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Samsung Delivers its Fourth Generation Blu-ray Player" />
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
	<title>HDTV Magazine - Samsung Delivers its Fourth Generation Blu-ray Player</title>
	<meta name="keywords" content="blu ray, ray player, home theater, samsung electronics, fourth generation, blu, ray, samsung, digital, home, player, dolby, electronics, theater, audio, generation, hdmi, video, fourth, consumers, technology, dvd, system, true, america" />
	<meta name="description" content="Samsung Electronics, a market leader and award-winning innovator in consumer electronics, continues to expand its lineup of Blu-ray format A/V products with its fourth-generation Blu-ray player. Featuring the latest in Blu-ray technology as well as advanced connectivity for use with other digital devices, it offers consumers exceptional functionality, compact design and remarkable viewing quality.

As the first company to introduce a stand-alone Blu-ray player, Samsung reinforces its support of the Blu-ray format with the next generation, full HD, BD-P1500. An ideal player for anyone who enjoys Blu-ray, DVDs or CDs in their home, this groundbreaking model significantly..." />
	<meta name="title" content="Samsung Delivers its Fourth Generation Blu-ray Player" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Samsung Delivers its Fourth Generation Blu-ray Player" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/04/samsung-delivers-its-fourth-generation-bluray-player.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Samsung Electronics, a market leader and award-winning innovator in consumer electronics, continues to expand its lineup of Blu-ray format A/V products with its fourth-generation Blu-ray player. Featuring the latest in Blu-ray technology as well as advanced connectivity for use with other digital devices, it offers consumers exceptional functionality, compact design and remarkable viewing quality.

As the first company to introduce a stand-alone Blu-ray player, Samsung reinforces its support of the Blu-ray format with the next generation, full HD, BD-P1500. An ideal player for anyone who enjoys Blu-ray, DVDs or CDs in their home, this groundbreaking model significantly..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1363', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/04/samsung-delivers-its-fourth-generation-bluray-player.php">Samsung Delivers its Fourth Generation Blu-ray Player</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>April 23, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<b>Samsung Delivers its Fourth Generation Blu-ray Player</b>
    </h1>
		            
		    		
		    		<div id="story_subheadline">
			        	
    <p class="bwtextaligncenter">
      <i>Samsung's BD-P1500 Blu-ray Player and HT-AS720 Home Theater Audio 
      System Provides a Home Theater Experience of Unparalleled Quality</i>

    </p>
  
		    		</div>
					
					
					
					<!-- start story body -->
					<p>RIDGEFIELD PARK, N.J.--(<a href="http://www.businesswire.com/">BUSINESS WIRE</a>)--Samsung Electronics, a market leader and award-winning innovator in 
      consumer electronics, continues to expand its lineup of Blu-ray format 
      A/V products with its fourth-generation Blu-ray player. Featuring the 
      latest in Blu-ray technology as well as advanced connectivity for use 
      with other digital devices, it offers consumers exceptional 
      functionality, compact design and remarkable viewing quality.
    </p>
    <p>
      <span id="bwanpa1">"</span>Samsung is excited to continue its support of 
      the Blu-ray format with our fourth-generation Blu-ray player," said Reid 
      Sullivan, vice president of Audio/Video &amp; Imaging at Samsung Electronics 
      America. <span id="bwanpa2">"</span>We are confident that consumers now 
      have the ultimate HD addition to complete their home theater package.<span id="bwanpa3">"</span>

    </p>
    <p>
      <span class="bwunderlinestyle"><b>BD-P1500 </b></span><b><span class="bwunderlinestyle" id="bwanpa4">-</span><span class="bwunderlinestyle"> 
      Fourth-generation Blu-ray Player</span></b>
    </p>
    <p>
      As the first company to introduce a stand-alone Blu-ray player, Samsung 
      reinforces its support of the Blu-ray format with the next generation, 
      full HD, BD-P1500. An ideal player for anyone who enjoys Blu-ray, DVDs 
      or CDs in their home, this groundbreaking model significantly improves 
      the home viewing experience and accommodates each of these formats in 
      one unit. Further, the BD-P1500 can upconvert standard DVDs to 720p, 
      1080i and 1080p resolutions and offers Full HD video playback 
      capabilities for a crystal-clear picture.
    </p>

    <p>
      Designed with fans of multimedia technology in mind, the BD-P1500 easily 
      connects to other digital devices through an HDMI 1.3&nbsp;port with CEC for 
      expanded color delivery and easy home theater control. The P1500 has BD 
      Profile 1.1 Bonus View and is BD Live Ready. With its built-in Ethernet 
      connection and USB input users can quickly upgrade their BD-P1500 with 
      the latest firmware which ensures the player remains at the cutting edge 
      of Blu-ray technology. By making these firmware upgrades simple and 
      easy, Samsung allows consumers to continually upgrade their BD-P1500 
      with the latest features.
    </p>
    <p>
      True audiophiles will love the BD-P1500 for going beyond standard Dolby 
      Digital audio playback to include Dolby Digital Plus, Dolby TruHD and 
      DTS-HD High resolution (scheduled to available later this year via 
      firmware update).
    </p>
    <p>
      The BD-P1500 will be available in June 2008.
    </p>
    <p>

      <span class="bwunderlinestyle"><b>HT-AS720 5.1 Channel Receiver / 
      Speaker System</b></span>
    </p>
    <p>
      Designed to compliment Samsung<span id="bwanpa5">'</span>s Blu-ray disc 
      players and offer consumers a full HD solution, the HT-AS720 5.1 channel 
      receiver / speaker system provides powerful, theater-like sound. With 
      its slick, deep black design and soft blue LED accents, the HT-AS720 
      seamlessly boosts both the audio presence and visual aesthetics of one's 
      home theater to a new level. A fully powered subwoofer, accentuated by 
      six satellite speakers, gives the HT-AS720 a rumbling 650W of total 
      output power to ensure the supreme sound quality matches the picture, 
      giving consumers a true cinematic experience at home.
    </p>
    <p>
      The HT-AS720 is currently available.
    </p>

    <p>
      <span class="bwunderlinestyle"><b>Specifications</b></span>
    </p>
    <table class="bwtablebottommargin" id="t5665483_1" cellspacing="0">
      <tbody><tr>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>

        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_0_5400" rowspan="1">
          <b>BD-P1500 Blu-ray Player</b>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;

        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_0_9360" rowspan="1">
          <b>HT-AS720</b>
        </td>
      </tr>
      <tr>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_1_1440" rowspan="1">
          Video
        </td>

        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_1_5400" rowspan="1">
          <span id="bwanpa6">&bull;</span> Blu-ray playback at content native HD 
          resolution of 1080p/1080i/720p

          <p class="bwcellparagraphmargin">
            <span id="bwanpa7">&bull;</span>1080p 24 Fs / 60 Fs
          </p>

          <p class="bwcellparagraphmargin">
            <span id="bwanpa8">&bull;</span>Selectable DVD upconversion 
            (720p/1080i/1080p)
          </p>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwwhitespacenowrap bwcellpaddingright0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_1_9360" rowspan="1">
          <p class="bwcellparagraphmargin">

            n/a
          </p>
        </td>
      </tr>
      <tr>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_2_1440" rowspan="1">
          Audio
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;

        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_2_5400" rowspan="1">
          <span id="bwanpa10">&bull;</span> 2.1 channel output

          <p class="bwcellparagraphmargin">
            <span id="bwanpa11">&bull;</span> Coaxial and optical digital audio 
            outputs
          </p>
          <p class="bwcellparagraphmargin">
            <span id="bwanpa12">&bull;</span> Dolby<span id="bwanpa9">&reg;</span> 
            Digital, Dolby Digital Plus, Dolby TrueHD,
          </p>

        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_2_9360" rowspan="1">
          <span id="bwanpa13">&bull;</span> Dolby Digital Plus Dolby True HD and 
          DTS HD pass through
        </td>
      </tr>

      <tr>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_3_1440" rowspan="1">
          Playable Media
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_3_5400" rowspan="1">
          <span id="bwanpa14">&bull;</span> BD-ROM, DVD-ROM, DVD-R(V mode only), 
          DVD-RW (V/VR mode), Audio CD, CD-R, CD-RW
        </td>

        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwwhitespacenowrap bwcellpaddingright0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_3_9360" rowspan="1">
          n/a
        </td>
      </tr>
      <tr>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_4_1440" rowspan="1">

          Features
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_4_5400" rowspan="1">
          <span id="bwanpa15">&bull;</span> HDMI 1.3 digital interface 
          (1080p/1080i/720p)

          <p class="bwcellparagraphmargin">
            <span id="bwanpa16">&bull;</span> MPEG2, VC-1, H.264, HD JPEG decoding
          </p>

          <p class="bwcellparagraphmargin">
            <span id="bwanpa17">&bull;</span> BD Java
          </p>
          <p class="bwcellparagraphmargin">
            <span id="bwanpa18">&bull;</span> HDMI, Component Video, S-Video outputs
          </p>
          <p class="bwcellparagraphmargin">
            <span id="bwanpa19">&bull;</span> HDMI CEC
          </p>

          <p class="bwcellparagraphmargin">
            <span id="bwanpa20">&bull;</span> Ethernet port
          </p>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_4_9360" rowspan="1">

          <span id="bwanpa21">&bull;</span> 1080p pass through

          <p class="bwcellparagraphmargin">
            <span id="bwanpa22">&bull;</span> HDMI (2) input and (1)output
          </p>
        </td>
      </tr>
      <tr>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_5_1440" rowspan="1">

          <b>Estimated Selling Price</b>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_5_5400" rowspan="1">
          <ul>
            <li>

              <b>TBD</b>
            </li>
          </ul>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwwhitespacenowrap bwcellpaddingright0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_5_9360" rowspan="1">

          <ul>
            <li>
              <b>$549</b>
            </li>
          </ul>
        </td>
      </tr>
      <tr>

        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_6_1440" rowspan="1">
          <b>Availability</b>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>
        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_6_5400" rowspan="1">
          <ul>

            <li>
              <b>June 2008</b>
            </li>
          </ul>
        </td>
        <td class="bwsinglebottomborder" colspan="1" rowspan="1">
          &nbsp;
        </td>

        <td class="bwcellpaddingleft0 bwverticalaligntop bwtextalignleft bwsinglebottomborder" colspan="1" id="t5665483_1_6_9360" rowspan="1">
          <ul>
            <li>
              <b>Now</b>
            </li>
          </ul>
        </td>
      </tr>

    </tbody></table>
    <p>
      <i>*Subject to change without notice</i>
    </p>
    <p>
      <span class="bwunderlinestyle"><b>About Samsung Electronics America, Inc.</b></span>
    </p>
    <p>

      Headquartered in Ridgefield Park, NJ, Samsung Electronics America, Inc. 
      (SEA), a wholly owned subsidiary of Samsung Electronics Co., Ltd., 
      markets a broad range of award-winning, digital consumer electronics and 
      home appliance products, including HDTVs, home theater systems, MP3 
      players, digital imaging products, refrigerators and washing machines. A 
      recognized innovation leader in consumer electronics design and 
      technology, Samsung is the HDTV market leader in the U.S. Please visit <a target="_blank" href="http://www.samsung.com" shape="rect">www.samsung.com</a> 
      for more information.
    </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>April 23, 2008  8:59 AM</b>
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
			<?=getComments(1363)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 1363)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/04/samsung-delivers-its-fourth-generation-bluray-player.php" type="text/javascript" charset="utf-8"></script>
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