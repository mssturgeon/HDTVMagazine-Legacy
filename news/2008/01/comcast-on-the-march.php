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
		AND e.entry_id = 892";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 892 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 892 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 892";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/comcast-on-the-march.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 892";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Comcast On The March" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Comcast On The March" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Comcast On The March" />
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
	<title>HDTV Magazine - Comcast On The March</title>
	<meta name="keywords" content="comcast digital, project infinity, digital voice, phone service, home phone, comcast, content, fancast, consumers, available, watch, ces, movies, entertainment, phone, users, digital, movie, service, information, demand, online, customers, find, project" />
	<meta name="description" content="Comcast distributed three powerhouse press releases(see below)that will punctuate Comcast Chairman, Brian Roberts key note speech scheduled for 9:00 am this morning at the 2009 CES in Las Vegas. For a webcast of Robert's address go to www.comcast.com/ces. While most newsreleases are felt as little quakes at best these announcements from ferociously shake the ground and raise a Tsunami. _Dale
" />
	<meta name="title" content="Comcast On The March" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Comcast On The March" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/comcast-on-the-march.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Comcast distributed three powerhouse press releases(see below)that will punctuate Comcast Chairman, Brian Roberts key note speech scheduled for 9:00 am this morning at the 2009 CES in Las Vegas. For a webcast of Robert's address go to www.comcast.com/ces. While most newsreleases are felt as little quakes at best these announcements from ferociously shake the ground and raise a Tsunami. _Dale
" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=892', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/comcast-on-the-march.php">Comcast On The March</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>January  8, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=293&category=Cable, Satellite & Fiber">Cable, Satellite & Fiber</a></b>
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
				<p>Comcast distributed three powerhouse press releases(see below)that will punctuate Comcast Chairman, Brian Roberts key note speech scheduled for 9:00 am this morning at the 2009 CES in Las Vegas. For a webcast of Robert's address go to www.comcast.com/ces. While most newsreleases are felt as little quakes at best these announcements ferociously shake the ground and raise a Tsunami. _Dale</p>

<p><br />
*******************************************************************</p>

<p>MOVE OVER BELLS: COMCAST CORPORATION BECOMES THE </p>

<p>FOURTH-LARGEST PHONE SERVICE PROVIDER IN THE U.S.</p>

<p>PHILADELPHIA, PA - January 8, 2008 - Comcast Corporation (Nasdaq: CMCSK, CMCSA), the nation's leading provider of entertainment, information and communications, today announced it has become the fourth-largest residential phone service provider in the United States.</p>

<p>The announcement comes less than three years after having officially launched Comcast Digital Voice®, an innovative alternative to the plain old telephone service traditionally offered by the Bells.  Comcast Digital Voice is the first true home phone replacement service to deliver a seamless and converged communications experience across all of Comcast's services (including cable and high-speed Internet) and customers' devices.</p>

<p>"Comcast Digital Voice is the fastest-growing home phone service in the country and we're reinventing the way consumers think of home phone service in the process," said Brian L. Roberts, Chairman and CEO of Comcast Corporation.  "Our rapid rollout of advanced services like Comcast Digital Voice has enabled us to transform Comcast into the only company in the U.S. to offer integrated video, high-speed Internet and phone services to more than 40 million households."</p>

<p>This year Comcast will roll out a number of major enhancements to Comcast Digital Voice service that will be integrated with Comcast's video and high-speed Internet services, including:</p>

<p>Universal Caller ID to the TV and PC - customers can view caller information while watching TV or using their computer. <br />
SmartZone&trade; Communications Center - a central online location where Comcast customers can use "viewable voicemail" to listen to calls or forward them like email, send and receive email and, in the future, remotely program their DVRs. </p>

<p>Enhanced Cordless Telephone - that will enable home phone users to check email and voicemail, send instant messages and access a "universal" personal address book as well as a yellow pages directory. <br />
 <br />
In addition, Comcast Business Class Digital Voice is now widely available to small businesses.</p>

<p>Comcast Digital Voice is an award-winning* and reliable phone service that packs better features than traditional phone companies for a better price.  Customers can save hundreds of dollars annually on home phone service from Comcast when compared to similar offers from the RBOCs.  According to a recent study by Microeconomic Consulting and Research Associates (MiCRA), phone competition resulting from cable's entrance into residential and business marketplace will save consumers more than $100 billion over the next five years.  Consumers have already saved $23.5 billion, including $13 billion in 2007 alone.</p>

<p>Comcast Digital Voice service includes:</p>

<p>Unlimited local and domestic long distance direct-dial calling - including calls to Canada and Puerto Rico - with competitive international rates to more than 200 countries. <br />
12 popular calling features plus enhanced voicemail - includes 3-way calling, call waiting and caller ID for no additional charge. <br />
911/E911. </p>

<p>Battery backup that lasts up to 8 hours during power outages. <br />
Compatibility with a majority of home alarm systems including industry-leaders ADT and Brink's. Ability to retain existing telephone numbers when switching from another provider. <br />
Professional installation. <br />
 </p>

<p>*Winner of Frost & Sullivan's 2007 Customer Value Enhancement Award for IP Communications Services.</p>

<p> <br />
<em>For a webcast of Chairman and CEO Brian L. Roberts' CES keynote address and for additional press information on Comcast announcements at CES, please visit www.comcast.com/ces. </em><br />
 *******************************************************************</p>

<p>COMCAST INTERACTIVE MEDIA LAUNCHES FANCAST.COM</p>

<p> SITE ENABLES CONSUMERS TO CREATE A </p>

<p>PERSONALIZED ENTERTAINMENT EXPERIENCE</p>

<p>Las Vegas, NV - January 8, 2008 - Comcast Interactive Media (CIM) today launched www.Fancast.com, the first online destination that enables users to watch, manage and find entertainment content wherever it is available - on Fancast, on television, online, on DVD or in theaters.  On Fancast, users can view an expanding free library of full episodes and clips from top networks and movie partners, find the content they are looking for across multiple platforms and create a personalized entertainment experience. </p>

<p>Watch It</p>

<p>·         Want to watch a previous episode of your favorite series that you missed? On Fancast consumers have instant access to over 3,000 hours of streaming free, full-length content from networks - including CBS, NBC, Fox (provided by Hulu), MTV Networks and BET Networks - movie trailers, short videos and interviews. </p>

<p>·         Can't find what you want to watch on Fancast? The site's "Watch it" tool will tell users where they can get it, on Fancast, on television, online, On Demand, on DVD or in the theater.</p>

<p>·         Fancast shows you what is on the TV line-up in your area no matter who your provider is. </p>

<p> </p>

<p>Find It</p>

<p>·         Need to decide what to watch tonight? On Fancast users can search for video content and entertainment information they're looking for on over 11 million web pages including information on more than 50,000 television shows, 80,000 movies and 1.2 million people combining multiple sources of entertainment information. </p>

<p>·         If users don't know exactly what content they're looking for, they can visit Fancast and discover new content through the site's personalized recommendations based on what they have chosen in the past. </p>

<p>·         Want to know who your favorite movie actor married or what TV show he appeared in before he made it big?  Explore the connections between TV shows, movies, cast and crew on "Six Degrees."</p>

<p> </p>

<p>Manage It</p>

<p>·         Want to control your DVR from the road? Coming soon in 2008, Fancast will enable users to program their DVR recordings in advance, from their computers, ensuring that they will never miss a show again.</p>

<p>·         Want a more personalized viewing experience? Fancast's "Watch List," will organize and catalog upcoming programming, set a personalized play list and send reminders to users about what they should watch in the future based on previous preferences. </p>

<p>·         In 2009, users will be able to mark and add content to a folder in their video on demand menu, directly from their computers.</p>

<p>·         If a movie is playing in the theaters, Fancast will link users to Fandango one of the Web's top movie destinations to purchase tickets online. </p>

<p>"Our goal with Fancast is to make entertainment consumption amazingly simple," said Amy Banse, President of Comcast Interactive Media. "In this new age of interactive media where entertainment is available everywhere, Fancast helps consumers find what they are looking for and manage their entertainment experience across multiple platforms. For people who want their content and information immediate and easy to find, Fancast is the ultimate one-stop shop."</p>

<p>Fancast will continue to add new features and additional content throughout 2008 and beyond.</p>

<p>For a webcast of Chairman and CEO Brian L. Roberts' CES keynote address and for additional press information on Comcast announcements at CES, please visit www.comcast.com/ces.  </p>

<p><br />
*********************************************************************</p>

<p><br />
Comcast CEO Brian L. Roberts ANNOUNCES Project Infinity: </p>

<p>Strategy to Deliver Exponentially more Content Choice on TV </p>

<p> Comcast To Give Consumers More than 1,000 HD Choices in 2008</p>

<p>Announces Roadmap to Add 6,000 Movies with 3,000 in HD </p>

<p>LAS VEGAS, NV - January 8, 2008 -Comcast Corporation (Nasdaq: CMCSK, CMCSA), the nation's leading provider of entertainment, information and communications, today announced three major content initiatives at the 2008 Consumer Electronics Show.  Comcast CEO Brian L. Roberts unveiled the Company's plan to give consumers more than 1,000 HD choices in 2008, its strategy to begin adding additional  HD movies, and announced Project Infinity - its vision to give consumers the ability to watch any movie, television show, user generated content or other video that a producer wants to make available On Demand.  </p>

<p> </p>

<p>"Project Infinity plans to give consumers the best and most content they will find On Demand anywhere - more HD, more sports, more movies, kids' programs and network TV," said Brian L. Roberts, Chairman and Chief Executive Officer of Comcast Corporation.  "Project Infinity builds on our commitment to bring more content to people across all platforms at home and on the go, and we'll work with our partners, programmers and video producers to deliver on this vision."</p>

<p>More Choice - Project Infinity</p>

<p>Project Infinity envisions ever-increasing customer choice that continues the evolution of time-shifted viewing that began with the huge success of Comcast On Demand.  Comcast's vision is to give customers exponentially more content choices - all available to consumers at the click of the remote without having to buy any additional equipment.  </p>

<p> Project Infinity is a logical extension of Comcast's television and online content strategy, which has fundamentally changed the way people watch video. As Comcast's On Demand library has expanded to offer more than 10,000 selections each month, viewership has grown dramatically, surpassing six billion views since 2003.  Comcast customers now are selecting On Demand 100 times per second, with 275 million views monthly.  </p>

<p> </p>

<p>Comcast will support its plan for Project Infinity using its existing fiber network and national IP backbone. The Company plans to create a system of library servers that will efficiently serve VOD content to consumers from several key locations across the country. This system would enable Comcast to offer exponentially more VOD content. </p>

<p>More HD</p>

<p>Comcast plans to expand its current HD lineup beyond the hundreds of HD choices available today, which is already more than any other provider offers.  By the end of the year, Comcast will make available more than 1,000 HD movies and TV shows every month, as well as the most popular television networks in HD as they debut.  HD content is the fastest-growing category in Comcast's On Demand library.</p>

<p>More Movies</p>

<p>With 1,300 movie titles available each month, Comcast Digital Cable customers already have access to more movies On Demand than they can find anywhere else.  Beginning next year, Comcast plans to offer more than 6,000 movies a month, and more than 3,000 of them will be available in HD.  Today, Comcast Digital Cable customers can choose from new releases as well as hundreds of free movies from Sony, MGM, FEARNet and Encore as well as movies from premium networks like Starz, HBO, Cinemax, Showtime and The Movie Channel - all available at their fingertips with no additional equipment.</p>

<p>More Content Online</p>

<p>Today the company also launched Fancast.com, the first online destination that will enable customers to find, manage and watch television and movie content wherever it is available - on Fancast, on television, online, on DVDs or in movie theaters.  Fancast will provide consumers with an easy way to manage their entertainment experience as the number of viewing choices that are available across platforms continues to grow rapidly.  In addition, Comcast currently makes more than 90,000 videos available at any time on Comcast.net.</p>

<p> For a webcast of Chairman and CEO Brian L. Roberts' CES keynote address and for additional press information on Comcast announcements at CES, please visit www.comcast.com/ces.  </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>January  8, 2008  8:40 AM</b>
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
			<?=getComments(892)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 892)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/comcast-on-the-march.php" type="text/javascript" charset="utf-8"></script>
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