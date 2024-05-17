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
		AND e.entry_id = 1236";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1236 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1236 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1236";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-1.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1236";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2008 HDTV Buyers Guide, Part 1" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2008 HDTV Buyers Guide, Part 1" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="2008 HDTV Buyers Guide, Part 1" />
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
	<title>HDTV Magazine - 2008 HDTV Buyers Guide, Part 1</title>
	<meta name="keywords" content="cable company, vertical resolution, broadcast dtv, horizontal lines, current analog, digital, dtv, analog, hdtv, cable, tuners, channels, resolution, quality, broadcast, stbs, tuner, sets, stb, most, consumers, company, satellite, might, still" />
	<meta name="description" content="I wrote this article originally for the HDTVetc magazine for the August 2003 issue, and it was later published on the HDTV Magazine in 2006. Consumers still go through the same struggle at national-chain stores today. I updated the article to include current HD equipment and technologies. Its tutorial substance and analysis are still applicable today, and are intended to help consumers in making the right purchasing decisions. Enjoy the reading. 

The following topics are covered in this segment: 

H/DTV and NTSC TV Systems, What are they? 
The First Effort of the DTV Transition 
Quality HDTV, or Quantity DTV, or Both? 
Backward Compatibility with Legacy Analog TV for Digital Broadcast 
Satellite/Cable, and the DTV Transition 
Tuner Integration 
The Effect DVD had for DTV 
The Rush for Knowledge

You have been hearing about HDTV and decided to start looking for one..." />
	<meta name="title" content="2008 HDTV Buyers Guide, Part 1" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="2008 HDTV Buyers Guide, Part 1" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-1.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I wrote this article originally for the HDTVetc magazine for the August 2003 issue, and it was later published on the HDTV Magazine in 2006. Consumers still go through the same struggle at national-chain stores today. I updated the article to include current HD equipment and technologies. Its tutorial substance and analysis are still applicable today, and are intended to help consumers in making the right purchasing decisions. Enjoy the reading. 

The following topics are covered in this segment: 

H/DTV and NTSC TV Systems, What are they? 
The First Effort of the DTV Transition 
Quality HDTV, or Quantity DTV, or Both? 
Backward Compatibility with Legacy Analog TV for Digital Broadcast 
Satellite/Cable, and the DTV Transition 
Tuner Integration 
The Effect DVD had for DTV 
The Rush for Knowledge

You have been hearing about HDTV and decided to start looking for one..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1236', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-1.php">2008 HDTV Buyers Guide, Part 1</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February  5, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>
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
				<div class="editorial">The following article is the latest in the 2008 HDTV Buyers Guide series. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_2.php">2008 HDTV Buyers Guide, Part 2</a></li>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_3.php">2008 HDTV Buyers Guide, Part 3</a></li>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_4.php">2008 HDTV Buyers Guide, Part 4</a></li>
</ul></div>
<br />
<p><i>I wrote this article originally for the HDTVetc magazine for the August 2003 issue, and it was later published on the HDTV Magazine in 2006. Consumers still go through the same struggle at national-chain stores today. I updated the article to include current HD equipment and technologies. Its tutorial substance and analysis are still applicable today, and are intended to help consumers in making the right purchasing decisions. Enjoy the reading.</i>  <p>The following topics are covered in this segment:  <ul> <li>H/DTV and NTSC TV Systems, What are they?  <li>The First Effort of the <a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29" target="_blank">DTV</a> Transition  <li>Quality HDTV, or Quantity DTV, or Both?  <li>Backward Compatibility with Legacy Analog TV for Digital Broadcast  <li>Satellite/Cable, and the DTV Transition  <li>Tuner Integration  <li>The Effect DVD had for DTV  <li>The Rush for Knowledge</li></ul> <p><b></b> <p>You have been hearing about HDTV and decided to start looking for one. A friend of yours reminds you that the general knowledge about buying regular TVs from the CRT analog era is not sufficient to select a digital product today, so you quickly review what you read about widescreen, black bars, digital tuners and resolution, and hope things would clear out at the store.  <p>You get into the typical nationwide consumer electronic store most people go to, and suddenly see several dozens of HDTV demo sets staring back at you. A salesperson is approaching you, the person's face is familiar; the salesperson is the one that sold you the new dishwasher two weeks ago; now the person is selling HDTVs with authority. At that point you start feeling worried, but you hang in there.  <p>Obviously this store is not a quality dedicated A/V retail place. Many consumers make their purchases based on the uninformed advice of untrained staff from typical nationwide consumer electronic chains.  <p>In the near past, a typical store could only have one of those HDTVs actually displaying HD, the only one that had an HD tuner; the rest were showing the same image from a video distribution loop not suitable for HD quality.  <p>Today perhaps the whole store feed is all HD, and the sets that are staring at you show the same picture, but with different colors, contrast, image enhancements, blacks, whites, etc. because no one bothered to set them correctly. So you start wondering why HDTV is not consistently perfect as is being preached, is that what HDTV is about?  <p>The sales person turns toward you and, in the middle of your consumer panic attack, tells you: "trust me, buy this TV, it would look much better at home once connected to an HD tuner". Would you buy a car without test-driving it?  <p>Millions of people went through similar experiences since HDTV was introduced in November 1998. Fortunately, some improvement is gradually seen in the stores, especially in dedicated A/V retail stores, which should take more time to help consumers understand the concepts behind each display technology, and not just quickly sell the HDTV inventory with the red tags, as most national consumer electronic chains do.  <p>Most consumers love red tag savings, and many leave the stores wallet-happy with a product they do not understand. Perhaps many of those do not actually want to understand because the HDTV technology has been introduced with a complexity level they refuse to deal with to just get a TV.  <p>To illustrate the complexity of an HDTV purchase decision you might want to read <a href="http://www.hdtvmagazine.com/articles/2006/02/is_hdtv_complex_enough.php" target="_blank">Is HDTV Complex Enough?</a>  <p>The objective of the article you are reading is to help you make your purchase with more confidence, but first allow me to cover the following basic subjects about HDTV:  <h2>H/DTV and NTSC TV Systems, What are they? </h2> <p>You might already know of the US plan to replace our current analog interlaced TV system (<a href="http://www.hdtvmagazine.com/glossary.php#NTSC" target="_blank">NTSC</a>) dated from the 1940's by a digital DTV system, by February 17, 2009. Curiously enough the idea started as "analog" HDTV until General Instruments proposed an all-digital system in 1990.  <p>The DTV standard is composed of 18 digital formats grouped into two levels of quality, as approved by the <a href="http://www.hdtvmagazine.com/glossary.php#ATSC" target="_blank">ATSC</a> (American Television Systems Committee) in 1995:  <p>1) SD: Standard Definition, with 480i/p (i:<a href="http://www.hdtvmagazine.com/glossary.php#Interlaced" target="_blank">interlaced</a>, p:<a href="http://www.hdtvmagazine.com/glossary.php#Progressive+Scan" target="_blank">progressive</a>) viewable horizontal lines of vertical resolution (rows counted from top to bottom), each line with up to 704 total pixels of <a href="/glossary.php#Horizontal+Resolution" target="_blank">horizontal resolution</a> (counted from left to right), and with an aspect ratio (relation of width to height in units) of 4x3 (as regular TV), or widescreen 16x9.  <p>2) HD: High Definition, with 720p and 1080i/p viewable horizontal lines of vertical resolution (rows counted from top to bottom), each line with respectively 1280 (for 720p) or 1920 (for 1080i/p) total pixels of horizontal resolution (counted from left to right), and only in widescreen 16x9 aspect ratio.  <p>Note that, because is not complex enough, the horizontal lines (rows) are expressed as "vertical" resolution (480, 720, 1080), and the vertical columns made of the aligned pixels on the horizontal lines are expressed as "horizontal" resolution (704, 1280, 1920).  <p>DTV was 15 years in the making before it went on the air in November 1998. HDTV is the quality part of DTV, but its implementation is not mandatory, SD is. I will use the term DTV only when addressing the digital TV system in general.  <p>Later in 2000, the Consumer Electronics Association (CEA), allegedly to help confused consumers, created another resolution level in between: ED (enhanced definition).  <p>This promoted the 480p SD format to ED level, leaving only the 480i format in the SD level. It also granted any TV the right to be labeled HDTV if capable to display only 810i lines of vertical resolution within the displayed image, rather than 1080i.  <p>One can argue how much this intervention from the CEA helped consumers more than helped manufacturers getting rid of mediocre sets. But that was back when CRT based DTV sets were the strength of the market; now most DTV sets are fixed pixel displays and their resolution is clearly specified as a pixel count in both directions.  <p>Our current NTSC over-the-air (<a href="http://www.hdtvmagazine.com/glossary.php#OTA+%28Over-The-Air+DTV+tuners%29" target="_blank">OTA</a>) TV system is 480i analog interlaced (actually 525i with 480i viewable horizontal lines of vertical resolution). The regular channels of digital satellite and digital cable could be compared to digital SD of broadcast DTV, but they are also transmitting dozens of channels in HDTV.  <p>To facilitate the transition, broadcasters were given one extra channel slot from the FCC for the simultaneous broadcasting of the analog and digital versions of their programming. It is a large investment for TV stations to build a DTV facility with new cameras, production, equipment, etc.  <p>When DTV is fully implemented, broadcasters have to return one of the two channels, analog over-the-air broadcasting will stop, and current analog TVs, VCRs, TiVos with analog tuners would stop "tuning" as well (but they will still work as display devices if fed with a 480i analog signal from a converter, VHS tape, DVD player, etc). This date was originally set for January 2007 but has been extended to February 17, 2009. Once DTV is implemented, the FCC will auction that spectrum of airwaves.  <p>Most OTA terrestrial TV stations are already broadcasting DTV in SD and HD widescreen, and consumers are buying HDTV sets at accelerated pace every year.  <h2>The First Effort of the DTV Transition </h2> <p>Just a look back at CEA's 2003 statistics, on the first 5 years of HDTV approximately 6 million DTVs (of which only 300,000 where integrated with DTV tuners) and 400,000 tuner set-top-boxes (STBs), were sold between 1999 and 2003. By the end of 2007, the HDTV count was 8 times fold, and about 50% of households have digital TV sets, according to the CEA.  <p>Back in 1998/9 it was not unusual for first generation HDTV monitors to cost $10,000, and HD STB tuners to cost from $700 to $3,000. It was expensive for early adopters.  <p>By the end of 2007, a huge variety in technologies and TV sets was available for every viewing environment. DTV sets are much better in quality, and sell for a small fraction of the price they sold back in 1998.  <h2>Quality HDTV, or Quantity DTV, or Both? </h2> <p>We all love the incredible video quality of HD, however, since HD is not mandated within the DTV plan, it allows a broadcasting station to use the allotted 6 MHz space (for the HD channel), to multicast instead several sub-channels of lower SD quality, as it is actually happening on many stations across the US.  <p>When sharing the same 6MHz total bandwidth, SD sub-channels rob about 2-3 Mbps each from the needed bandwidth of an HD channel that by itself should broadcast at 19.4 Mbps (if the station also multicasts an HD sub-channel). The parallel broadcast forces further compression of the 19.4 Mbps HD signal to a lower bit rate to make room for the SD sub-channel, compromising HD quality.  <p>In many cases, more than one SD sub-channel is multicast together with the HD sub-channel. When the reduced HD bit rate compresses the signal beyond acceptable limits, it renders a lower quality image with noticeable artifacts, especially on fast moving images in sports, which are more evident, and unacceptable, on large screens (more on it later).  <p>It might also be possible that the TV station desires to share some of the bandwidth for data-casting interactive services, or for mobile DTV applications for hand-held portable devices (because there will be no analog broadcasting to those portable devices as well). For more information, check the articles I wrote on the "<a href="http://www.hdtvmagazine.com/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php" target="_blank">Mobile DTV</a>" series, where I analyze the potential impact of mobile applications on the quality of an HD channel when robbing from its bandwidth.  <p>We all hope that HD will reign, and HD quality will prevail over the digital-quantity business models, and you have to encourage DTV broadcasters to do so, besides, most consumers bought an HDTV not a SDTV.  <h2>Backward Compatibility with Legacy Analog TV for Digital Broadcast</h2> <p>When the DTV broadcast is fully implemented in February 17, 2009, there would be backward compatibility with your current analog equipment, but there is a catch, in order for you to watch DTV terrestrial digital channels on your current analog TV you would need a digital over-the-air STB tuner connected to it. Your current analog TV would display an analog interlaced 480i version of the digital image.  <p>There is no need to rush for the replacement of an analog TV that might be in good working condition if you just want to continue watching similar quality TV, but you would have to buy a STB digital tuner for broadcast DTV.  <p>This applies also to your analog VCR, DVD recorder, TiVo, etc., if you want them to have broadcast tuning independence. A few years ago, DTV STB tuners were relatively expensive, in the $400-$1000 price range, imagine buying a $400 digital tuner for a $30 analog VCR, but they are gradually coming down in price.  <p>The US government has approved a subsidy coupon program to help people purchase DTV tuners to facilitate the analog-to-digital transition so existing analog TV sets can continue to be used for broadcast digital DTV.  <p>For that purpose, Congress approved a fund of $1.5 billion dollars, with an initial allocation of $990 million dollars to subsidize up to two $40 coupons per household. The coupons became available in January 2008 and can be requested by consumers until March 2009, to use them toward the purchase of two DTV tuners.  <p>The two coupons cannot be used together to purchase only one DTV tuner, neither they can be used to buy another type of OTA tuner/DVR STBs, satellite STBs with broadcast DTV tuners into them, or cable STBs.  <p>The tuners offered by this program are expected to cost in the $50-$70 range each; the consumer would have to pay the difference after applying the $40 coupon. According to the plan, the tuners would become available by mid February 2008 through the national chains of Best Buy, Circuit City, etc.  <p>Although the subsidized tuners are designed to tune digital SD and HD channels, they cannot output the tuned signal other than 480i analog resolution to an analog TV. In other words, the subsidized tuners would not perform as typical HD tuners passing resolutions of 480p, 720p, or 1080i to HDTV devices for HD viewing. Their functionality is just to downconvert because their purpose is backward compatibility to analog TVs, but their price is lower than typical ATSC HDTV tuners with variable output resolutions and digital outputs.  <p><b></b> <h2>Satellite/Cable, and the DTV Transition</h2> <p>If you are a satellite subscriber you already have the satellite STB you need for their digital SD/HD services. Additionally, most satellite boxes also have a terrestrial ATSC tuner if you want to get free local channels using a VHF/UHF antenna. However, DirecTV introduced a new model in late 2007 without antenna input; the local channels would have to be viewed from the satellite feed, a service they have already for most major cities.  <p>If you are a cable subscriber, when the cable company decides to disable the analog feed to your household and supply only the digital feed, you would need a digital-to-analog cable STB to view the digital channels on each analog TV in your house, similar to the approach of the coupon program for broadcast DTV above, but you would have to lease or buy the cable STB, no coupons.  <p>Cable companies were authorized by the FCC in late 2007 to continue their analog feed service for another 5 years (up to 2012) if they prefer, but they are not obliged to do so. Cable STBs do not have DTV digital terrestrial tuners into them so you cannot use their STB connected to a UHF/VHF antenna to receive free local channels.  <p>Cable companies face at least two alternatives on the analog-to-digital transition between 2007 and 2012:  <p>a) If their subscriber base is mostly digital, a cable company might have the incentive to make a large up front investment to acquire enough digital STBs to convert all the remaining analog subscribers as soon as possible to digital tier services, who would have to lease one digital STB for each analog TV. That would release the bandwidth occupied by the analog broadcast channels on the cable feed, which could be used for additional digital channels, and receive an increased revenue if those are premium, VOD, PPV, etc. paid services.  <p>b) If the subscriber's base is mostly analog, a cable company might prefer to keep the existing mix of analog and digital STBs, and maintain the analog tier as long as needed until 2012. Since the cable feed bandwidth allocation for the analog broadcast channels must continue with this alternative, the company would have to postpone the potential growth of digital channels and services, but there will not be a need for an up front large investment for expensive digital STBs because there is no forced conversion. This option seems economical for both the company and the subscriber, because a subscriber would not be forced to lease a digital STB for each analog TVs that might be currently connected to the wall coax without a STB, as many non-primary TVs are in most households.  <p>While the up front investment of a large number of digital STBs could be expensive to a cable company, there could be a partial offset with the potential revenue received from additional digital pay services such as VOD, PPV, or premium channels. Additionally, the number of digital STBs required for a full digital conversion of the cable feed might be further reduced when considering the growing base of integrated HDTVs with CableCARD tuners expected to increase in 2008 and 2009.  <p>However, since the integrated CableCARD tuners within HDTV sets are only unidirectional, there might still be a cable subscriber's base that would still require the bi-directional capabilities of cable HD-STBs for VOD, PPV, and cable supplied programming guide. Each cable company would have to balance those factors until 2012.  <h2>Tuner Integration</h2> <p>In 2002 the FCC issued a "mandatory" plan to gradually integrate digital broadcast tuners into DTV monitors and other tuning devices, such HD DVRs. The plan has been already implemented in 2007 for all the sets larger than 13", and all DTVs on sale today are mandated to include digital terrestrial tuners (except for some industrial/professional models). In most cases they also include a cable on-the-clear tuner for non-premium unscrambled channels, or even include a CableCARD tuner for premium channels and services.  <p>As mentioned above, the CableCARD tuners are unidirectional only, and lack the bi-directional features of Video-on-Demand, Impulse Pay-per-View, and cable-company supplied programming guide, for which a separate set-top-box from the cable company would still be needed until integrated TV sets are designed to have bi-directional capabilities on their integrated CableCARD tuners.  <p>Industry analysts commented for years that economies of scale would bring down the price of digital tuners to the level of today's very low price analog NTSC tuners within TVs, but the reality is that STBs for ATSC terrestrial, or for cable, satellite, DVRs, etc. (not the down-converting government-coupon STBs) still have a high price, considering that comparatively, large HDTVs came down from the $5,000-$10,000 in 98/99 to more accessible prices below $1000.  <p>More on this subject is covered further down.  <h2>The Effect DVD had for DTV</h2> <p>Most of the 6 million people that bought HDTVs on the first 5 years of the transition (98-03) did so NOT to view HD, but rather to enjoy playing widescreen DVDs at 480p. Even now in 2008, after Hi-Def DVD has been already introduced in early 2006, regular DVDs are still a favorite content for DTV, because they certainly display quite well as progressive 480p, or upscaled to 720p or 1080i/p to the native resolution of the digital set (by either the DVD player or the TV set). The same DVD played on an analog TV would only show the image as a 480i interlaced scanning.  <p>In addition, an HDTV has the capability to show widescreen DVDs in anamorphic format displaying all the original vertical resolution stored on the disc, while 4x3 analog TVs would show the same DVD letterboxing the image between larger top/bottom bars in order to maintain the wider aspect ratio of the movie, and with less vertical resolution for the image itself.  <h2>The Rush for Knowledge</h2> <p>Since 1998 abundant HDTV information has been released about general concepts, types of TVs, technical specifications, such as resolution, display speed, etc. Many web sites and magazines have covered good ground to help educate readers with the concepts.  <p>It is hard to digest and consolidate all that information and be ready for buying a set without some practical guidelines. This is the objective of this series of articles.  <p>Stay tuned for the next part in the series: 2008 HDTV Buying Guide, Part 2</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February  5, 2008  8:50 AM</b>
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
			<?=getComments(1236)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1236)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-1.php" type="text/javascript" charset="utf-8"></script>
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