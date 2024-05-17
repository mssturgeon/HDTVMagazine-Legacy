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
		AND e.entry_id = 38";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 38 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 38 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 38";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/05/cables-view-on-the-transition.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 38";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Cable\'s View On The Transition" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Cable\'s View On The Transition" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Cable\'s View On The Transition" />
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
	<title>HDTV Magazine - Cable's View On The Transition</title>
	<meta name="keywords" content="must carry, cable operators, digital signals, high definition, set top, digital, cable, signals, analog, carry, operators, must, sets, customers, television, transition, programming, broadcasters, converted, stations, definition, high, those, boxes, million" />
	<meta name="description" content="To round out things at the Subcommittee On Telecommunications hearings today the cable industry weighed in saying they could provide the least pain to the public (some think that is a new policy for cable) and that Congress could accomplish the goal of hastening the transition with a date certain cut off by &quot;giving cable operators the flexibility to down-convert digital must carry signals to analog format at the headend and to carry some of those down-converted signals in lieu..." />
	<meta name="title" content="Cable's View On The Transition" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Cable's View On The Transition" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/05/cables-view-on-the-transition.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="To round out things at the Subcommittee On Telecommunications hearings today the cable industry weighed in saying they could provide the least pain to the public (some think that is a new policy for cable) and that Congress could accomplish the goal of hastening the transition with a date certain cut off by &quot;giving cable operators the flexibility to down-convert digital must carry signals to analog format at the headend and to carry some of those down-converted signals in lieu..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=38', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/05/cables-view-on-the-transition.php">Cable's View On The Transition</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 26, 2005</b>
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
				<p>To round out things at the Subcommittee On Telecommunications hearings today the cable industry weighed in saying they could provide the least pain to the public (some think that is a new policy for cable) and that Congress could accomplish the goal of hastening the transition with a date certain cut off by "giving cable operators the flexibility to down-convert digital must carry signals to analog format at the headend and to carry some of those down-converted signals in lieu of the digital versions." That will enfranchise, or at least avoid the disenfranchisement of the 134 million TV sets cable says you have laying around. It's a more convoluted response than that, of course, and so the entire testimony from the NCTA's CEO Kyle McSlarrow is presented here in its entirety. Again, your comments are very welcome. </p>

<p>Related story:</p>

<p><a href="http://www.marketwatch.com/news/story.asp?guid=%7BCC3E6C94%2D1CEE%2D44A0%2DAE87%2DEDC264A9DC07%7D&dist=rss&siteid=mktw ">http://www.marketwatch.com/news/story.asp?guid=%7BCC3E6C94%2D1CEE%2D44A0%2DAE87%2DEDC264A9DC07%7D&dist=rss&siteid=mktw </a></p>

<p>TESTIMONY OF KYLE McSLARROW<br />
PRESIDENT AND CEO<br />
NATIONAL CABLE & TELECOMMUNICATIONS ASSOCIATION </p>

<p>on </p>

<p>The Digital Television Transition Act of 2005 </p>

<p>[Staff Draft] </p>

<p>before the </p>

<p>SUBCOMMITTEE ON TELECOMMUNICATIONS AND THE INTERNET<br />
COMMITTEE ON ENERGY AND COMMERCE<br />
U.S. HOUSE OF REPRESENTATIVES<br />
WASHINGTON, D.C.<br />
MAY 26, 2005</p>

<p>INTRODUCTION</p>

<p>Mr. Chairman, Congressman Markey, members of the subcommittee, my name is Kyle McSlarrow. I am the President and CEO of the National Cable & Telecommunications Association and it is a privilege to appear before you today. NCTA is the principal trade association for the cable television industry in the United States. It represents cable operators serving more than 90 percent of the nation's 66 million cable television households and more than 200 cable program networks, as well as equipment suppliers and providers of ancillary services to the cable industry. </p>

<p>I appreciate your invitation to testify today about the staff's draft bill to expedite the transition to digital broadcasting. As our industry has explained in prior testimony, the cable industry has not taken a position on establishing a "hard date" by which broadcasters must relinquish their analog spectrum. Nonetheless, we recognize the importance that you and other members of this committee place on making this spectrum available for public safety and homeland security purposes, as well as for new commercial uses. The cable industry is ready, willing, and able to work with you to achieve these important policy goals, and we applaud your leadership and hard work to make them happen.</p>

<p>OVERVIEW OF THE STAFF DRAFT </p>

<p>Accelerating the digital transition means that broadcasters will be transmitting only in digital format even though the vast majority of televisions still have analog-only tuners. There are currently 110 million television households in the United States, and the FCC estimates that at least 15 percent of them (~17 million) rely exclusively on analog, over-the-air broadcast transmissions to get their TV. Moreover, NCTA estimates that - just in the homes of cable subscribers - there are 134 million analog television sets that are not equipped to receive digital transmissions or are not connected to a digital-to-analog set-top box (including digital subscribers who have one or more analog sets without a digital box). </p>

<p>With such a large number of analog sets still in use, the cable industry's first priority is ensuring that our customers suffer the least amount of disruption to their television service. We believe the cable industry can best assist the digital transition by guaranteeing that our customers can - on the first day of digital-only broadcasts - continue to watch their favorite stations on their existing televisions without having to buy any new equipment or subscribe to any new service. Congress can accomplish this goal by giving cable operators the flexibility to down-convert digital must carry signals to analog format at the headend and to carry some of those down-converted signals in lieu of the digital versions. </p>

<p>We recognize this is not a perfect solution: some customers with digital video equipment on the first day of the digital-only era might not be able to watch a very few must carry stations in digital format - as is the case today. But once a decision has been made to switch exclusively to digital broadcast signals at a time when most households are analog, there are no perfect solutions . We believe our proposal minimizes costs and inconvenience to consumers, and allows you as policy-makers not to have to worry about disrupting anyone who is a cable customer - and to do so at no cost to the government. </p>

<p>What does this mean for cable customers? It means their viewing choices and preferences will not be disrupted. Today, we offer analog to our analog customers, analog and digital to our digital customers, and increasingly the opportunity for customers with high definition sets to watch a growing number of channels in high definition. In addition, many of our operators have already announced their own plans to simulcast analog channels in digital. Consequently, what is true today will still be true the day after the transition under our proposal. The bottom line, Mr. Chairman, is that cable customers will notice no change from the day before the transition to the day after. In the meantime, increasing numbers of our subscribers will continue to switch to digital services, and many of them will become high definition subscribers. </p>

<p>The alternatives to our proposal are far less palatable. </p>

<p>In particular, the staff draft that has been circulated for discussion today fails to strike an appropriate balance. Instead of allowing cable operators to carry down-converted analog signals in lieu of the digital signals transmitted by must carry broadcasters, the staff draft would require operators to carry every must carry broadcaster's digital signal in digital format. While the staff draft would allow cable operators to carry down-converted analog signals in addition to the digital signals, an operator who chose to carry one must carry broadcast station's down-converted signal would be required to carry the down-converted signals of all must carry broadcasters as well as all their digital signals. </p>

<p>This provision would provide cable operators with two options, each of which is worse for consumers than the status quo and worse than the option of allowing carriage of down-converted signals in lieu of digital signals. Cable operators would have to choose between (1) carrying all must carry stations only in the digital format in which they are broadcast, or (2) "dual carriage" of all must carry stations in both digital and down-converted analog format. </p>

<p>If cable operators were to carry must carry stations only in digital format, those stations would disappear from the viewable channel lineups of the majority of cable subscribers. Only those with digital sets or digital set-top boxes would be able to view the signals. Indeed, even the majority of customers who subscribe to digital tiers (and therefore have digital set-top boxes) would be unable to view the digital signals of must carry broadcasters if those broadcasters were broadcasting in high-definition, since the set-top boxes of most digital tier subscribers who do not have high definition television sets are not capable of converting high definition signals for viewing on analog sets. </p>

<p>It is hard to imagine any reason for requiring operators to carry broadcasters' digital standard definition signals instead of down-converted analog versions of those signals. Any difference in the picture quality between a standard definition digital signal and a down-converted analog version of that signal would surely be outweighed by the fact that only customers with digital sets and set-top boxes could view the digital signals. It is equally hard to imagine any reason for requiring operators to carry must carry broadcasters' high definition signals instead of down-converted versions of those signals, since an even smaller number of customers - only those with high definition sets and high definition set-top boxes - could watch the broadcasters' programming. </p>

<p>Yet this last scenario - with the fewest number of consumers able to watch digital programming - is the likely outcome if the only other option for cable operators is to dual carry a broadcaster's signal in digital and down-converted analog format - or, even worse, to dual carry every broadcaster's signal as outlined in the staff draft. Dual carriage of every must carry broadcaster's high definition signal and its down-converted analog signal would impose an untenable burden on cable operators and programmers. By preempting an excessive amount of capacity on cable systems, it would interfere with the ability of cable operators to offer the broadest array of programming as well as new and innovative digital services to consumers. It would be especially unfair to non-broadcast program networks, which have no guarantee of carriage of their programming in analog or digital format - much less in both . </p>

<p>Today, while broadcasters are still transmitting both analog and digital signals, cable operators may fulfill their must carry obligations by carrying a broadcaster's analog signal for viewing by all customers while also allowing operators to carry - in addition - those digital signals that provide additional compelling benefits to customers with high definition sets or digital set-top boxes. This approach has provided broadcasters with incentives to offer compelling digital content in order to gain additional carriage of their digital signals. It has also enabled cable operators and program networks to deploy digital services that best meet the needs and interests of cable customers. Finally, it has ensured continued availability of a must carry broadcaster's primary video programming to the widest cable audience without requiring cable customers to purchase digital television sets or set-top boxes in order to view that primary video stream. </p>

<p>If Congress decides that the analog spectrum needs to be returned before most television viewers are equipped to receive digital signals, it can ensure that these positive developments continue without disruption or significant costs - at least to cable customers, if not to over-the-air viewers. To do so, however, we urge you to revise the approach taken in the staff draft with respect to must carry and down-conversion. Instead of permitting operators to carry down-converted signals in addition to mandated carriage of the digital signals transmitted by must carry broadcasters, one needs simply to permit carriage of down-converted signals in lieu of the digital signals, while giving operators the discretion to carry both the down-converted and digital versions of the signal. </p>

<p>CABLE IS LEADING THE BROADER NATIONAL TRANSITION TO DIGITAL </p>

<p>In the United States, the broadcasters' transition from analog to digital is only a small part of the larger digital transition that is occurring in every area of our nation's economy. Since 1996, when Congress enabled cable's investment in new technology and programming by substantially reducing regulation, cable operators have completely rebuilt their facilities. With an investment of more than $95 billion, operators have replaced coaxial cable with fiber and installed new digital equipment in homes and system headends, thus enabling the transmission of voice, video, and Internet services in digital format. As a result, cable customers are already enjoying a full complement of digital programming and advanced information services independently of the broadcasters' slower conversion to digital. </p>

<p>For example, cable customers can purchase digital programming tiers that include a diverse array of video networks and commercial-free music channels. Digital customers also have access to video-on-demand programming, digital video recording, and electronic program guides. These features allow programs to be viewed at the customer's convenience and at a time of the customer's choosing. They also allow cable subscribers to block access to programming they do not want their children or households to see. All of cable's digital services can be enjoyed by consumers with analog sets who use digital set-top boxes that convert digital signals to analog. More innovative, interactive video services are on the way, in addition to the Internet and digital telephone services that are already attracting large numbers of customers. </p>

<p>Cable customers with HDTV sets have even more options. They can receive a wide selection of programming transmitted in high definition, including 18 HD cable networks that transmit much of their programming in high definition. In addition, cable operators are now voluntarily carrying the digital channels of a substantial number of over-the-air broadcast stations in addition to those stations' analog signals (either through retransmission consent agreements with individual commercial stations or voluntary initiatives such as cable's recent carriage agreement with public television stations. ) Note that cable's contractual agreement with public television stations was reached through private negotiations - not federal legislation or FCC regulations.</p>

<p>CABLE'S CARRIAGE OF BROADCAST SIGNALS </p>

<p>The vast majority of cable customers have analog television sets, and most of those sets - as in over-the-air households - are not equipped with digital set-top boxes. Today, cable operators provide the analog signals of virtually all local television stations, which can be viewed by all customers - those with and without digital boxes, and those with and without digital television sets. In addition, operators also provide the digital signals of some, but not all, broadcast stations - in particular, those stations that provide compelling digital programming that is likely to enhance the value of cable service for the small but growing number of customers with high definition sets. </p>

<p>Cable's current carriage practices are wholly consistent with what both the marketplace and the "must carry" rules dictate. Existing law requires cable operators to carry the analog signals of all "must carry" broadcast stations during the digital transition, while making carriage of the digital signals optional and subject to "retransmission consent" agreements with broadcasters. The FCC has recognized that requiring "dual carriage" of the analog and digital signals of all must carry stations - regardless of whether the digital programming is valuable to the few cable households capable of viewing it on their sets - would do nothing to further the purposes of the must carry requirements or the digital transition while unduly burdening the First Amendment rights of cable operators and programmers.</p>

<p>This sensible balance, which serves the interests of must carry broadcasters, cable operators, cable programmers, and cable customers, can be preserved even after broadcasters stop transmitting analog channels. To do so, Congress should allow cable operators to "down-convert" the digital signals of must carry broadcasters to analog at the headend and provide the primary video programming stream of those down-converted signals to cable homes in lieu of the primary digital video stream. This will ensure that all cable households can receive the programming provided by those must carry broadcasters without having to purchase digital television sets or digital set-top boxes. </p>

<p>Households with HDTV sets would, of course, continue to watch the increasing number of HD channels that exist now, but in some instances would watch a small number of must carry channels in analog even if the broadcaster were transmitting in high definition. I would note that cable operators could still choose to provide the digital signal in addition to the down-converted analog signal if the digital version were uniquely compelling and attractive to customers with digital and HDTV equipment. </p>

<p>Current law requires cable operators to carry must carry signals without "material degradation." The FCC has interpreted this to mean that - after the transition when broadcasters are transmitting only a digital signal - " a broadcast signal delivered in HDTV must be carried in HDTV." This "no material degradation" requirement makes sense if - as is the case under current law - the transition to digital-only broadcasting does not occur until most households are equipped to receive digital signals on their television sets. If Congress is going to impose a "hard date" that occurs before most consumers have digital sets or set-top boxes, however, then it should also permit carriage of down-converted must carry signals in lieu of the digital signals in order to ensure a seamless transition for consumers. </p>

<p>CONCLUSION </p>

<p>Mr. Chairman, there is much to commend in the staff draft, and there is obviously much more that needs to be done with regard to the issue of providing converters for analog TV sets after the transition. We are grateful for the opportunities we have had to discuss these issues with you, and we want to continue working with you and other members of this committee on our shared goal of ensuring that the maximum number of consumers continue to have access to the same digital programming after the transition that they currently enjoy during the transition.</p>

<p>I would be pleased to answer any questions you might have. </p>

<p>Eleventh Annual Report to Congress on the Status of Competition in the Market for the Delivery of Video Programming, FCC-05-13, released February 2, 2005. </p>

<p>In return for deregulation, the cable industry promised Congress and American consumers that it would provide: (1) facilities-based competition to the telephone companies, and (2) a whole new generation of advanced information and video services - both of which we have done.</p>

<p>The cable industry is rapidly rolling out high definition programming. As of January 1, 2005, cable companies had launched high definition television service on systems passing 92 million homes. At least one cable operator in all of the top 100 markets now offers HDTV, and HD over cable is available in 184 of the 210 U.S. television markets.</p>

<p>Cinemax HDTV, Comcast SportsNet HD, Discovery HD Theater, ESPN HD, ESPN2 HD, HBO HDTV, HDNet, HDNet Movies, INHD, INHD2, MSG Networks in HD, NBA TV, NFL HD, Showtime HD, STARZ! HD, The Movie Channel HD, TNT HD, and Universal HD.</p>

<p>In 2002, the cable industry was the first to embrace FCC Chairman Powell's call for voluntary industry action to speed the digital television transition. As of January 1, 2005, cable operators voluntarily carried 504 digital broadcast signals - a 66 percent increase over the 304 stations carried in December 2003.</p>

<p>On January 31, 2005, NCTA reached agreement with the Association of Public Television Stations (APTS) to ensure that the digital programming offered by local public TV stations is carried on cable systems serving the vast majority of cable subscribers across the nation. The boards of NCTA, APTS, and PBS ratified the agreement on February 4, 2005.</p>

<p>There are approximately 172 million television sets in the 66 million cable households across the country. 26 million cable homes subscribe to digital service, but not all digital households have digital boxes on all their TVs. This means that there are approximately 28 million analog TVs in digital homes that will require boxes after the transition. If one adds these 28 million sets to the approximately 106 million analog TVs in homes with only analog cable service (41 million), there are a total of around 134 million analog TV sets in cable homes that will require digital boxes in order to get digital service. The cost of deploying 134 million set-top boxes is $9 billion for a simple $67 digital-to-analog box and $29 billion for a $200 interactive digital cable box.</p>

<p>In re Carriage of Digital Television Broadcast Signals, First Report and Order and Further Notice of Proposed Rulemaking, 16 FCC Rcd. 2598, 2629 (2001) (emphasis added).</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 26, 2005  5:07 PM</b>
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
			<?=getComments(38)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 38)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/cables-view-on-the-transition.php" type="text/javascript" charset="utf-8"></script>
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