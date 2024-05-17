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
		AND e.entry_id = 20";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 20 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 20 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 20";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/04/new-sales-figures-just-released.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 20";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download New Sales Figures Just Released" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="New Sales Figures Just Released" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="New Sales Figures Just Released" />
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
	<title>HDTV Magazine - New Sales Figures Just Released</title>
	<meta name="keywords" content="consumer electronics, digital cable, sales figures, dtv products, figures released, cable, sales, dtv, digital, hdtv, cea, television, transition, industry, while, our, products, nation, year, public, shapiro, million, percent, february, new" />
	<meta name="description" content="With both celebration and caution the February DTV sales figures were released on April 15th, 2005. While the numbers for February were worthy a glass of champagne a new downward revision in the sales forecast for 2005 from the Consumer Electronic Association sounded a new and cautionary note. The revised numbers, while still handsome, are less so than the original projections. No one can say the numbers are anything but still encouraging. HDTV remains the fastest growing sector in consumer electronics. The cautionary note arises from the challenges that lay ahead for the DTV transition (more accurately stated as the HDTV component of the DTV transition). &quot;Why?&quot; you ask. &quot;Things are looking so positive?&quot;
" />
	<meta name="title" content="New Sales Figures Just Released" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="New Sales Figures Just Released" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/04/new-sales-figures-just-released.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="With both celebration and caution the February DTV sales figures were released on April 15th, 2005. While the numbers for February were worthy a glass of champagne a new downward revision in the sales forecast for 2005 from the Consumer Electronic Association sounded a new and cautionary note. The revised numbers, while still handsome, are less so than the original projections. No one can say the numbers are anything but still encouraging. HDTV remains the fastest growing sector in consumer electronics. The cautionary note arises from the challenges that lay ahead for the DTV transition (more accurately stated as the HDTV component of the DTV transition). &quot;Why?&quot; you ask. &quot;Things are looking so positive?&quot;
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=20', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/04/new-sales-figures-just-released.php">New Sales Figures Just Released</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>April 16, 2005</b>
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
				<p>New Sales Figures Released</p>

<p><span style="font-size:130%;">"Since the launch of HDTV our nation has not been properly sold on its value."<br />
</span><br />
by Dale Cripps</p>

<p><br />
With both celebration and caution the February DTV sales figures were released on April 15th, 2005. While the numbers for February were worthy a glass of champagne a new downward revision in the sales forecast for 2005 from the Consumer Electronic Association sounded a new and cautionary note. The revised numbers, while still handsome, are less so than the original projections. No one can say the numbers are anything but still encouraging. HDTV remains the fastest growing sector in consumer electronics. The cautionary note arises from the challenges that lay ahead for the DTV transition (more accurately stated as the HDTV component of the DTV transition). "Why?" you ask. "Things are looking so positive?"</p>

<p>True they are, but illusionary. The distribution of income in the United states is such that those with disposable income can readily make a choice for HDTV while those living from payday to payday are deeply challenged by its costs. This is not a good situation for the marketers nor the nation as a whole. "Where there is a will, there is a way" is an adage we might consider engaging in our strategic thinking. By that I mean the time has come to 'create universal demand'.</p>

<p>While simplistic this concept is often set aside for more competitive reasons. Lip service is given to it, of course, with industry spokesman offering wise sounding speeches about "educating the public." What that means is educating on the specific products and features while leaving the basic reason to engage it to endless speculation. What is needed is an unwavering commitment to sell America on why each member should have it. That case has not been made at effective levels. The typical reasons promoted for having HDTV pick at the low hanging fruit. They all cater to self-indulgence and instant gratification without a consideration for the general welfare<br />
Without a case for its social good being made the end of the road for self-gratification comes when money for its indulgence is no longer found.</p>

<p>Anyone who has lived in a third world nation knows that television (and more recently color television) was and is the "must have"above all else. It is recognized that universal communications to a public with an empahsis on "community" is a chief and irreplacable benefit. When a service means enough to its citizenary money is always found to install it. While it may be in a slightly economized form--cell phones instead of wires for example--the task is completed because it is seen as an affordable furthering of the general welfare of the state.</p>

<p>Ask ten people on the street today what the national purpose for HDTV is and nine will shrug their shoulders and the rest will say it's a ploy (cool as it is) by industry. Not until suggeted do people see that the new clarity and beauty radiating into every home in every nation will mellow the mood of the world and produce a sense of progress, an essential for peace. Rather than inducing any such mellowness it was ushered in in the United States like a piece of contentious legislation and then commercially launched November 1998 within the confines of the Ronald Reagan Conference center, itself in the shadow of the nation's capital building. That launch is not entirely bereft of a good start but the "fireworks" to spark a nation and inspire the our citizens to act is still missing. True, that launch has led us to where we are today. That is a great achievement for both industry and the hard working consumers, who dragged it screaming and kicking into their homes. We can be satisfied with that, at least for awhile. But as the cost of a sale grows and the spectre of a "digital TV divide" begins to haunt the transition we might consider a new and spectacular re-launch of HDTV so that a genuine excitement once again leads the parade to a successful conclusion. We now have the tools for that launch and everyone can be made easily ready.</p>

<p>To market HDTV with enormous power a cause celeb must be attached to it and that cause itself be sold. The cause I see is no less than the enrichment of every nation that employs it. Once such a message is honed the vision as to why we are investing in HDTV becomes clear to everyone. It is an easy message to repeat as we do with the virtues of freedom. The secret to creating clarity on any topic is to find one point which in itself is entirely lucid and then go onward from that point. Instead of doing that our industry has plunged us into hopeless chaos with competitive slogans and offerings. The wise sounding speaches about "educating the public" are ploys for selling competitive features.</p>

<p>The HDTV era promises a clarification in human understanding and a building of our empathy for the world's diverse cultures while at the same time producing in us a new level of respect and reverence for the value AND marketability of beauty. That is a message I see from this perch. --Dale Cripps</p>

<p><br />
************************************************************************</p>

<p>Arlington, Va., April 15, 2005 - The Consumer Electronics Association (CEA) today announced that February 2005 unit sales to dealers of direct view and rear projection digital television (DTV) products rose 43 percent over February 2004, showing continued strength in this key consumer electronics product category. CEA stated that February sales reached 342,060 units on dollar sales of $443 million. Year-to-date unit sales are up 14 percent over the first two months of 2004 and full year 2005 sales are expected to more than double the final 2004 total of 7.3 million units. CEA also announced that total February 2005 sales of DTV products including monitors displays and integrated sets reached 498,554 units on dollar sales of $699.4 million, marking a 29 percent increase over combined sales in February 2004. "Digital television, particularly HDTV (high-definition television) remains the fastest-growing segment of the consumer electronics industry, driven by strong consumer demand for flat-panel and rear-projection HDTV products," said CEA President and CEO Gary Shapiro. "At the same time, however, the rate of growth is slower than we originally anticipated, due to a variety of factors." CEA is revising its full-year 2005 projections for DTV product sales to 15 million units from the previously announced 20 million. CEA explained the initial projection, issued earlier this year, overstated the full-year effect of the Federal Communications Commission's (FCC) DTV Tuner Mandate and was based in part on what proved to be false assumptions that the cable and broadcast industries would support specific actions necessary to achieve aggressive sales growth in 2005. "Quite frankly, we were wrong. Our ambitious DTV sales projection for the year was based on a belief that both the broadcast and cable industries would embrace actions that would greatly accelerate our nation's transition to digital television," said Shapiro. "While broadcasters and cable providers have made strides toward increasing programming and making infrastructure investments, both industries continue to throw up roadblocks that slow the transition. Specifically, the broadcast industry is opposing the establishment of a 'date certain' end to analog television broadcasts while the cable industry refuses to support and promote Digital Cable Ready (DCR) products. We see both of these as critical and necessary steps in order to reach 20 million unit sales this year and help ensure rapid consumer adoption of DTV." Shapiro noted that CEA has supported the effort of several key lawmakers, including Senator John McCain (R-AZ) and Congressman Joe Barton (R-TX), to accelerate the DTV transition by setting a specific cut off date for analog broadcasts. "A hard cut off date would eliminate any uncertainty for consumers and all parties involved in the transition and thus more effectively advance the move to DTV," CEA has argued. Shapiro also observed that in addition to opposing a hard deadline, the broadcasters also are fighting to slow the transition by blocking CEA's petition before the FCC to accelerate the existing timetable for manufacturers to install digital tuners in all television sets offered for sale. CEA believes the accelerated deadline will expedite the transition and provide needed certainty in the marketplace. Specifically, CEA is requesting the FCC advance the deadline for manufacturers to include digital tuners in all televisions with screens sized 25- to 36-inches from July 1, 2006 to March 1, 2006. In exchange, the association is urging the Commission to eliminate the July 1, 2005 deadline that requires 50-percent of sets offered for sale in this size range include a digital tuner."The current 50 percent requirement is antithetical to the Commission's goal of building marketplace demand for broadcast DTV receivers when applied to popular, 25- to 36-inch screen sizes," said Shapiro. "Although initially conceived as a phase-in for the benefit of manufacturers and retailers, in reality it creates uncertainty in the marketplace for each group and slows the ramping-up of volume production necessary to bring costs down." CEA also based its 2005 sales forecast on the assumption that the cable industry would support and promote DCR products. These products provide access to digital HDTV programming and other premium channels without a set-top box and also include over-the-air DTV tuners. CEA believes the cable industry has not adequately promoted the availability of DCR sets, nor have they adequately supported the requisite CableCARD, the security device consumers must obtain from their cable provider in order to access content on their DCR products. "More than seventy percent of U.S. households choose to receive their primary television signal via cable. Digital Cable Ready offers cable consumers a seamless transition to DTV while also driving up the ATSC tuner penetration rate," Shapiro said. "As we have long said, cable operators must support DCR integrated television sets with adequate promotion and supplies of CableCARDS provided at a fair price in order to provide a seamless viewing experience for cable customers to access HDTV and DTV programming." Shapiro underscored that even with the reduced sales forecasts, sales projections for 2005 and beyond are extraordinarily strong. "Today, even as we concede we were overly ambitious with our original forecast, we celebrate the fact that consumers will flock to DTV in greater numbers than ever before. Our industry will sell more DTV products in this single year than we've sold in several past years combined. In fact, our revision still puts us ahead of where we thought we'd be just a year ago: In 2004, we forecast total 2005 sales would reach a mere 8.3 million sets." Shapiro will address these issues and evaluate the current DTV landscape as he attends and addresses several audiences during the National Association of Broadcasters (NAB) convention in Las Vegas next week. He will be speaking at several events during the show, including the annual CBS Affiliate Engineering breakfast in the Tower Ballroom at the Bellagio Hotel at 7:30 a.m. on Monday, April 18.</p>

<p>Some other news...<br />
MSOs, Public TV Stations Reach Accord on Digital CarriagePublic TV stations and cable multiple system owners reaching more than 80 percent of cable homes have ratified a deal that will ensure carriage of public broadcasting's digital TV signals on systems serving "the vast majority of the nation's cable subscribers," according to a joint release Thursday by the National Cable &amp; Telecommunications Association and public broadcast groups.(TelevisionWeek)<a href="http://www.blogger.com/">http://www.blogger.com/</a></p>

<p>Public Television and Cable Ratify Digital Cable Carriage AgreementPTV Stations and Cable Systems Approve Agreement Between APTS, NCTA and PBS for Digital Cable Carriage During and After the Digital TV Transition(Association of Public Television Stations)<a href="http://www.blogger.com/">http://www.blogger.com/</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>April 16, 2005 11:43 AM</b>
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
			<?=getComments(20)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 20)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/04/new-sales-figures-just-released.php" type="text/javascript" charset="utf-8"></script>
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