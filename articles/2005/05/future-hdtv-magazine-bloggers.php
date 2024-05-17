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
		AND e.entry_id = 29";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 29 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 29 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 29";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/05/future-hdtv-magazine-bloggers.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 29";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Future HDTV Magazine Bloggers" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Future HDTV Magazine Bloggers" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Future HDTV Magazine Bloggers" />
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
	<title>HDTV Magazine - Future HDTV Magazine Bloggers</title>
	<meta name="keywords" content="hdtv magazine, dale cripps, high definition, educate public, hdtv movement, hdtv, our, magazine, public, world, television, new, technical, dale, business, consumer, been, years, most, transition, must, cripps, those, movement, internet" />
	<meta name="description" content="&quot;To create a core attraction that becomes a hub to tens of thousands of visitors each day, I call upon the expert authorities in HDTV to step up and join us as a Blogger/columnist to HDTV Magazine and provide unchallengeable responses to the vexing questions still confusing HDTV consumers today.&quot; _Dale Cripps Dear Future HDTV Magazine Bloggers: I want to thank those industry experts who have expressed interest in joining our family of Bloggers/columnists. I hope you will look at..." />
	<meta name="title" content="Future HDTV Magazine Bloggers" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Future HDTV Magazine Bloggers" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/05/future-hdtv-magazine-bloggers.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&quot;To create a core attraction that becomes a hub to tens of thousands of visitors each day, I call upon the expert authorities in HDTV to step up and join us as a Blogger/columnist to HDTV Magazine and provide unchallengeable responses to the vexing questions still confusing HDTV consumers today.&quot; _Dale Cripps Dear Future HDTV Magazine Bloggers: I want to thank those industry experts who have expressed interest in joining our family of Bloggers/columnists. I hope you will look at..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=29', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/05/future-hdtv-magazine-bloggers.php">Future HDTV Magazine Bloggers</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 23, 2005</b>
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
				<p>"To create a core attraction that becomes a hub to tens of thousands of visitors each day, I call upon the expert authorities in HDTV to step up and join us as a Blogger/columnist to HDTV Magazine and provide unchallengeable responses to the vexing questions still confusing HDTV consumers today."</p>

<p>_Dale Cripps</p>

<p><br />
Dear Future HDTV Magazine Bloggers: </p>

<p>I want to thank those industry experts who have expressed interest in joining our family of Bloggers/columnists. </p>

<p>I hope you will look at any participation as an opportunity to extend your contribution to this historic social and cultural movement. </p>

<p><br />
Dr. Jeffrey Hart wrote the book, literally, on <a href="http://www.hdtvmagazine.com/store/book.php?asin=0521826241">HDTV and Politics</a> He also wrote to me the following:</p>

<p>"I wanted to tell you how much your writing and general high spirits have been appreciated for these many years. You should take pride in what you did, because there were many times when the only person convinced that HDTV would eventually succeed was you and you had the guts to say so. In my view, the creation of new industries, especially complicated ones like this, is never guaranteed."</p>

<p><strong>Mission statement:</strong><br />
HDTV Magazine is an unwavering supporter of the digital television transition via the education and encouragement that it provides to the consumer public. Using the latest in web, voice, and video tools we aid the transition and invest with all in a new world vision fitted to the 21st century and beyond.</p>

<p>Our primary mission is to educate the public in practical matters for acquiring both HDTV hardware and software and to dispel misinformation and mythologies that may arise to becloud the HDTV movement.  </p>

<p><strong>Why Is HDTV Of Any Social Significance? Does it matter?</strong><br />
Consumer Electronics Association president, Gary Shapiro said in his recent keynote speech before the FCC bar association that technological innovation is creating a better world for today's citizens and will lead to an even better future.<br />
 <br />
"It will be a better world, a connected world," Shapiro predicted, "a world where culture and shared interests (will) transform local, regional and national boundaries so the higher ideal of human potential and spirit can be realized.  And for all of us it is a world where the communications our technologies allow inspire, entertain and inform, while reducing the chance of misunderstandings and conflict."<br />
  <br />
This statement from Gary Shapiro reflects the theme embraced by the HDTV Newsletter and HDTV Magazine from their respective inceptions. The same message is reflected in our mission statement and throughout all of our publications since 1985. To hear fresh voices of stature now acknowledging the historic significance the HDTV movement has is gratifying. When properly promoted this view of its higher public calling is far and away the most powerful sales tool HDTV can possibly call upon. The issues of spectrum recovery and its reassignment, while highly desired, are minor motivations for the transition in contrast to the greater good to our cultural health that will be realized from a fully installed base of HDTV receiving and viewing equipment. It is everyone's duty to do his or her part in making this higher calling come true. Again, we hear the same call from Gary:</p>

<p>"We all have a role as Americans to participate," Shapiro urged in his FCC speech. "<u>Industry and company advocates</u> must present their views. Journalists must not only accept advocacy, they must dig and find the truth. Policymakers must agree on the goals and figure out what's best."</p>

<p>The underscoring is mine. "Industry and company advocates" is what we are.  </p>

<p><br />
<strong>More Benefits</strong><br />
The founder of the SMPE (latter adding the T for television making it SMPTE), C. Francis Jenkins, presented in the early 1900s another irrefutable axiom:</p>

<p><strong>"Commerce, like an Army, can go no faster than its means of communications. The history of industrial advances in all ages shows that with every addition to communications facilities the volume of business has increased."</strong></p>

<p>The digital revolution crowned with the HDTV display is certainly an addition to communications facilities. Both a cultural enrichment and an increase in business volume is the promise to all regions of the world who employ digital television and all that surrounds it, include the Internet. From a macro perspective the revolution pays for itself many times over. </p>

<p><strong>The challenge</strong><br />
Professor Jeff Hart said it most clearly: "Success in such complex undertakings is never guaranteed."</p>

<p>Without any question the introduction of HDTV was one of the most difficult and courageous moves ever made in consumer communication's history. By the time digital technology had been added to the formula the challenge that lay before a small band of supporters was to disenfranchise the well-working and profitable business of standard NTSC broadcasting (PAL and SECAM in other regions) and replace it with a product that had no positive or proven track record with a technology that was unproven in practice, had legions of detractors with their reports and studies from academia, and all at a cost of hundreds of billions of dollars. At the launch the only thing sure was that if the analog spectrum could be retuned in a national block to the FCC they had a market for it at auction. Everything else was untested and full of doubt.  </p>

<p><br />
Overcoming the ever-present threats of market failure and deliberate and circumstantial obstructions is our collective challenge. Optimizing our position within this revolution is our company challenge. </p>

<p><strong>Some background:</strong><br />
Following three years of research Advanced Television Publishing (ATP) was launched in 1985. The company mission then as is now: To support the transition from NTSC/PAL/SECAM commercial television service to High-definition commercial television services here and abroad. </p>

<p>To fulfill that mission ATP authored and published a newsletter (44 pages issued ten times per year (the HDTV Newsletter) for ten years. The advocacy publication tracked each move and event commencing with the first demonstration of terrestrially transmitted HDTV in Washington, D.C. in 1986 on through to the acceptance of the ATSC DTV standard by the Federal Communications Commission in late 1996. </p>

<p>At its height the Newsletter was distributed in 24 countries to 550 major organizations at a final subscription price of $700 annually. All recipients of the newsletter held key positions in the development of the HDTV technology. During its lifespan I authored 100 magazine articles for 20 international and domestic trade and consumer publications and was frequently quoted in the NY Times, Wall Street Journal, Daily Variety, the AP, UPI wire news services as well as made numerous public appearances on radio and television in the U.S., Japan, and Europe. </p>

<p><strong>Company Factoids</strong><br />
Over the last 22 years ATP initiated 55,000 phone calls, conducted more than 1000 hours of recorded industry leader interviews, authored 300,000 emails, and produced 150 published magazine articles all on behalf of the HDTV movement. The HDTV Magazine has electronically produced and published 6500 formatted and finished color-filled pages for six years running (approximately 40,000 pages in all).</p>

<p>In the late 80s and early 90s ATP sponsored four international conferences on HDTV with two in New York City, one in London, England, and one in Washington D.C. (120 speakers). ATP has programmed HDTV conferences for Broadcast Engineering, the NAB, and others as well as having consulted on the subject to the U.S. Government, the governments of Japan, and Taiwan, to MCA/Universal Picture (Lew Wasserman & Sid Sheinberg) and to dozens of manufacturing companies in various parts of the world. </p>

<p>Along with the Consumer Electronics Association the first two major surveys of HDTV owners were produced with the resultant data presented to the attendees of annual HDTV Summit meetings.  </p>

<p>Founder Dale Cripps served as editor-in-chief of the quarterly HDTV World Review, published by Alan Meckler of Mecklermedia from 1988 to 1992 and the HDTV Technical editor for Widescreen Review for the last 13 years (a print publication serving 40,000 discriminating audio/videophiles). He most recently served as the technical advisor on the popular book <a href="http://www.hdtvmagazine.com/store/book.php?asin=0764575864">HDTV For Dummies</a>. </p>

<p>ATP produced the first online professional news service, HDTV News Online, for three + years (1995-1998).</p>

<p>HDTV Magazine was launched in Washington, D.C. on November 16, 1998 (the official HDTV launch date). With only minor interruptions the Magazine has been electronically distributed daily since its inception. In February of 2005 the magazine paused for three weeks for reorganization and refurbishing in preparation for mass consumer markets. That work is nearing completion and with the addition of our columnist/Bloggers the next phase of HDTV Magazine will stand completed.</p>

<p>HDTV Magazine is recognized widely and accurately as the world's first consumer publication devoted to HDTV end users. Its purpose was and is to aid the launch of HDTV by way of presenting hard-to-find facts; forming an HDTV community; establishing authority in support of education, and, in our earlier days until now, the publishing of otherwise impossible-to-find HDTV program listings. It currently supports 5000 subscribers and is now prepared for mass markets. We presently serve 350,000 page views per month from our web site (with capacity for 20 times that without changes), far more than most television print publications with the exception of TV Guide and Sound and Vision. With the addition of our Columnist/Bloggers and other additions we anticipate the page view volume within the year to escalate to one million per month. </p>

<p>No publicity for our web site has yet been made. Now that we are prepared for mass markets and the HDTV boom moves closer to high gear we anticipate becoming one of, if not the most publicized and advertised services in the world. Such promotion will follow a constant stream of improvements and features being made to our online presence.</p>

<p><strong>Awards</strong><br />
HDTV Newsletter and Dale Cripps are numbered among the charter members (66) in the CEA created Academy of Digital Television Pioneers. HDTV Magazine was the recipient of the Academy's coveted Press Leadership award for 2002 (other nominees Wall Street Journal, San Jose Mercury News, USA Today. and Sound and Vision). </p>

<p><br />
<strong>Associated Activities Still To Be Activated</strong><br />
In the year 2000 Dale Cripps and Tedson Meyers (Ret) of Coudert, Bros. formed The High-Definition Television Association of America (HDTVAA). This organization has not been activated. The purpose of the HDTVAA is to organize the global promotion of HDTV where needed and to provide meaningful incentives to those technical developments which appear promising but orphaned. HDTVAA is chartered to be a Washington lobbying force for issues that are industry-wide and where no other voice adequately serves the public interest. </p>

<p>The HDTVAA is the outgrowth of an earlier concept --The First International Academy, Institute, and Foundation for High-definition Television Arts and Sciences-- formed in spirit in 1989 by Syd Cassyd, the founder of the present Academy of Television Arts And Sciences, Sam Bush (Ret), Robert Munoz, and Dale Cripps. Mr. Cassyd believed that just as many improvements as are to be found in HDTV over NTSC can be made in forming the next global television society. The forming of this international society is dedicated to the memory and works of the late Syd Cassyd, who tirelessly devoted a lifetime to the furthering television itself. While well into his 80s he accepted what proved to be his last professional assignment, the Hollywood correspondent's role for HDTV Newsletter. </p>

<p>A new society embracing the highest ideals and potential for HDTV should be a welcome addition to the global activities of this revolution.  </p>

<p><br />
<strong>The Business Structure for HDTV Magazine</strong><br />
In February 2005 the company added a key member as co-owner with Dale Cripps in the person of Mr. Shane Sturgeon. Mr. Sturgeon has a strong Internet technical background and has assumed the responsibility for all technical development of HDTV Magazine. Has built a much more robust and professional product than was that of our previous one.  </p>

<p>Mr. Sturgeon and Dale Cripps have 50/50 equity ownership in a limited liability corporation called HDTV Magazine, Ltd. Shane is responsible for technical development and Dale for the original content.  </p>

<p><strong>What Is Left To Do?</strong><br />
At each H/DTV conference I have attended over the last two years the same refrain has been sung: "We must educate the public". </p>

<p><strong>How?</strong><br />
The goal is to educate the public:</p>

<p>1)	On the values of HDTV <br />
2)	On how these values can be received in the home and business environment <br />
3)	With a consistently accurate portrayal of terms, products and services offered<br />
4)	With clarification of overarching issues and a forward watch on new developments<br />
5)	With deep political analysis<br />
6)	With a historical perspective of the movement<br />
7)	With authority that only time in service can bring</p>

<p>How do we educate the public when competing interests produce misleading statements?   </p>

<p><strong>With Authority  </strong><br />
The CEA, NAB, NCTA, SBCA, and the FCC all have done well in leading the education of the public ... so far. Many sources from this list, however, offer differing versions of what HDTV is and what it means. Many seek publicity less so for educating the public generically then for furthering their own competitive agendas. As a result confusion has plagued the retail markets. Public distrust of institutions has made it difficult for misconceptions to be repaired even by those sources most accurately portraying them.  </p>

<p>"I'm overwhelmed," admitted Robert Ferguson, 46, as he stood surrounded by big-screen televisions at Visions electronics on Highland Avenue yesterday. "I haven't made a decision to go high-definition." __The Canadian press</p>

<p>A Canadian Movie Network study found that 14 per cent of Canadian homes have high definition-ready sets but not the set-top box required to receive hi-def signals. </p>

<p>Forty-one per cent who don't have the box cited lack of HD programming as the reason. Some 16 per cent weren't aware a box was needed. </p>

<p>In a technically innovative society the work of consumer education is never done. As one technical version fades another is introduced. Rapid acceptance of the new, essential for momentum, comes when authority leads the educational process. The confidence needed for establishing a belief is crystallized best through a connection to authority. </p>

<p>A confused public will not buy. No one relies upon amateurs for decision-making advice where large expenses are concerned. HDTV is not an impulse item except to a few. A more deliberated and thoughtful process goes into a decision as large as the one for the primary viewing gear and programming in one's home. That is doubly true when quality and reliability are uppermost in mind. HDTV has no feature other than quality to sell. People seek answers to their price/performance questions. Authority is what we need now to answer those questions in a way that has the added benefit of being widely distributed.</p>

<p></p>

<p><strong>Dick Wiley Wrote:</strong> "Dale, you are the ultimate hero for HDTV."</p>

<p>While I thank Dick for his generous comment I have reported on many other noble men and women (certainly Dick is included) who, at great personal expense, fought for HDTV with every resource they had at their disposal. The public is the chief beneficiary of the HDTV movement -- not the governments or factories here or elsewhere.  As today's factories and institutions fade into distant memory the image serving tomorrow will reflect the pioneering you have done today. </p>

<p>For the public to act positively on this technical departure a clear understanding of the benefits and why the chief parameters were chosen is required. Authority from your sector can be felt by our readers and thus free them to do their part, as Gary Shapiro has urged, in completing this transition.  </p>

<p>Change is invariably met with a resistance. This resistance is best overcome with enthusiasm and the instilled desire to leave the old and pick up the new. Salesmanship is another force we can call upon in overcoming this resistance. We call this salesmanship in order to meet the December 31, 2006 deadline hoped for by Congress.</p>

<p><br />
Why is this transition still languishing in the eyes of some?</p>

<p>Any lag you may perceive in the transition must be linked to the weakness of the initial launch of HDTV. This nation knows how to effect change. Within fourteen months after the declaration of war with Japan the entire infrastructure of the country was mobilized to the war effort. Everyone in the nation was educated by then to act effectively in one way or the other the war effort, being that buying bonds, saving their bacon fat, or building airplanes. It paid off with a win over a formidable enemy.</p>

<p>When the will of a nation is mobilized by a captivating idea, that nation saturates itself with the message and absorbs it contextually in to its cultural consciousness. From there the individuals can act instinctively.</p>

<p>While the business of HDTV is in no way seen a failure the launch of it was a disappointment. The start of the commercial life for HDTV did not have the fanfare or star driven excitement reserved for great things and yet it is the greatest eye we have ever opened upon the world. I say this as an owner and enthusiastic consumer of HDTV programming for the last four years. Nothing has been more illuminating on the conditions of life than has come from the clear moving images and real sounds streaming into my home from every corner of the world. No technical device has brought more enjoyment, wonder, and appreciation for cultural diversity and beauty into my home than has this technical miracle.</p>

<p>Why was the launch of it treated only little better than for any product on the shelf? Why was it not heralded from the rooftops as a new and significant 'good' coming to mankind? There was a tremendous fear that it was headed for a market failure. None of the big Japanese firms had any confidences that broadcasters would step up with signals. If there were to be signals, would anyone buy the expensive hardware and take the pains to have it installed? No one was confident enough on these issues to risk more than what was already at stake. </p>

<p>The question of its failure, however, is clearly behind us. Now every fiber in our promotional being can be unleashed in the marketing of HDTV. The greatest profit ahead is for both private and public sectors and comes best from an accelerated pace of the transition. Everything is in place for that acceleration. It is only a matter of igniting the promotional power of the nation until the enthusiasm is kindled and fires fanned to new heights.</p>

<p> It has been stated by numerous authorities that we are on the cusp of some kind of global revolution - a time when cultures are set to collide. There could not be a more fitting time for clarifying what the values are in each of the competing cultures and drawing them together in a new common image format that creates in the end a new world vision. Understanding, as noted above by Gary Shapiro, is enhanced by our technical innovations. An increase in mutual understanding is what our scholars tell us is the only way for preventing armed conflict to flair up tragically among these competing cultures. If HDTV's unfolding around the world can add one scintilla to that mutual understanding its deployment should be no less crucial than having the arms we must otherwise bear for an open and deadly conflict.</p>

<p>I am not suggesting that there is a political solution that can ameliorate or otherwise tame this cultural conflict. I can only say that if an old vision is about to die a new one is about to be born. We ought to show the best we can in that new vision and it will take HDTV to best illustrate it.</p>

<p><br />
MONEY, MONEY, MONEY...</p>

<p><br />
There is never enough of it. We feel limited by the little we have. We can only gain more of it from those who also set to gain most - the public. We can earn from that public with subscriptions and advertising as long as we ease the burden of acquiring the product. We must make our drawing power irresistible here and our volume of visitors will grow large.  </p>

<p>Some interesting facts: <br />
Over 70% of the TV households (110 million in U.S.A.) have Internet access. Over 40% of those connect with wideband speeds, and the addition of wideband connections is growing exponentially. As a result there is no publishing medium like the Internet. In many ways it operates like the television model, except it transmits upon a command. Many note that the success of a TV program is dependant upon its writers. "If it's not on the page, it won't be on the screen." We are all the writers of the script for this revolution, at least where the image of it is concerned. We have to be a magnet if the money we need to fan the flames around the world is going to roar in. It is a duty to ourselves and to the movement that we become as strong a center as possible </p>

<p>Advertising on the Internet is enjoying a rebirth. Online sales of goods and services this last quarter reportedly is up 23% -- totally tens of billions of dollars. In many ways the Internet is a better deal for advertisers. We receive 300,000 page views a month (without yet starting promotions) and most audio video magazines have no more than 45,000 printed copies. How many copies are opened to the advertiser's page? Who has a counter for that? </p>

<p>Every page viewed on our site is accounted for. When someone comes to our page on the Internet, you know they see your promotion.</p>

<p>Volume is the next issue. Advertisers like Sam Runco, who serve a niche, cannot afford to advertise in Time Magazine because the volume of printed copies is so great that rates are staggering. But he can buy just as many targeted views on the Internet as he can afford. Everyone who needs to launch a new service or to brand himself or herself in the HDTV universe needs a successful web site where their ads can be placed and seen by those who are coming for a kindred interest. I ask you authors to help the advertisers realize their dreams with the HDTV community who visit the web site for only one reason - to learn more about HDTV from you. </p>

<p><br />
<strong>How We Can Do Business</strong><br />
Some of those invited to participate in this Blog family work for large companies - broadcasters, cable, and DBS companies. You are in high positions because I am seeking only the authoritative. You may not be as keen on extra income as you are market result, but there are others of unquestioned value to the movement who have not been as well taken care of by their past employers. I want to make sure that a business system works for them (and for all).  </p>

<p>In my next Blog I will lay out the business model for this group of authorities we seek. The Blog following that one will illustrate the methods for authoring your column - the nuts and bolts of our publishing services.</p>

<p>I sincerely hope you hear and respond to this calling. The potential is huge and only takes your agreement to share you knowledge. I might add that we have 1200 highly skilled consumers in another list we call the TIPS List. It is an email forum and I will ask them to bring to you the challenging questions that will be yours to respond to.</p>

<p>Sincerely,</p>

<p>Dale Cripps<br />
HDTV Magazine</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 23, 2005  7:36 PM</b>
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
			<?=getComments(29)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 29)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/future-hdtv-magazine-bloggers.php" type="text/javascript" charset="utf-8"></script>
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