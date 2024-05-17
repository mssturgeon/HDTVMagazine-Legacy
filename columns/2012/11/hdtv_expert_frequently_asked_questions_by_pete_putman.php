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
		AND e.entry_id = 4941";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4941 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4941 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4941";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2012/11/hdtv-expert-frequently-asked-questions-by-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4941";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Frequently Asked Questions &ndash; by Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Frequently Asked Questions &ndash; by Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Frequently Asked Questions &ndash; by Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - Frequently Asked Questions &ndash; by Pete Putman</title>
	<meta name="keywords" content="blu ray, picture quality, inch lcd, plasma tvs, lcd tvs, ’s, inch, tvs, plasma, lcd, –, antennas, buy, quality, time, people, don’t, i’ve, get, been, picture, see, blu, ray, years" />
	<meta name="description" content="You ask; I answer (as best I can)." />
	<meta name="title" content="HDTV Expert - Frequently Asked Questions &amp;ndash; by Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Frequently Asked Questions &amp;ndash; by Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2012/11/hdtv-expert-frequently-asked-questions-by-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="You ask; I answer (as best I can)." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4941', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2012/11/hdtv-expert-frequently-asked-questions-by-pete-putman.php">HDTV Expert - Frequently Asked Questions &ndash; by Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>November 26, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=304&category=General Interest">General Interest</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>I haven’t run a Letters column on HDTVexpert.com in several years. And there’s a good reason for that: With everything else on my plate these days, I keep forgetting to do it. (That was Steve Martin’s favorite excuse, as I recall: <em>“I forgot!”</em>)</p>
<p>Even so, I find that there are certain questions that keep popping up after my classes and presentations, not to mention after some of my more controversial articles. And there’s no better time to address some of them with the holiday shopping season now upon us.</p>
<p>So let’s get started!</p>
<p>*****************************************************************************************************************************************************************************************************</p>
<p><em>Q. What (kind of) (brand) (size) TV should I buy?</em></p>
<p>A. Not surprisingly, I get this question a lot. But you may be surprised at my answer: Whatever you like.</p>
<p>The fact is; TV prices have never been lower. I spotted numerous Black Friday specials where TVs were selling for less than $10 per diagonal inch. Imagine that! You can pick up major brand 42-inch LCD TVs for less than $400 now. $900 will buy you a major brand 60-inch plasma TV (that price was $1000 a year ago). Heck, you can score a 70-inch LCD TV for $2,000!</p>
<p>Frankly, it’s hard to go wrong these days. Prices are so low that even if you grow disenchanted with your purchase after a year or two, you can just recycle it and buy a new one. To put things into perspective, add up what you pay for mobile phone service annually, plus the cost of a smart phone that you’ll get rid of in two years.</p>
<p>Is that number on the high side of $1,200? For about the same amount of money, you could buy a pair of 47-inch LED-backlit LCD TVs. Or a fully-loaded “smart” 3D LED LCD TV with Web browser. For what my 42-inch Panasonic TH-42PZ80U cost me in September 2008 ($1,099), I can now buy two 42-inch 1080p plasma TVs and get more HDMI inputs with reduced power consumption. Amazing!</p>
<p>Here’s a tip: No need to rush out and grab a TV before Christmas. The best deals are typically in the weeks leading up to the Super Bowl, so if you can wait that long, you’ll see some huge savings. But even if you just gotta watch your favorite college or pro team on a new TV, you’ll still find some great prices through the next four weeks.</p>
<p> </p>
<p><em>Q. You’ve always been a big advocate for plasma. Now you’re telling me that plasma is going away. How can that happen? Don’t people care about picture quality?</em></p>
<p>A. It’s simply a matter of economics. The TV-buying public has voted and voted overwhelmingly for LCD technology. One of the major consumer preference studies commissioned earlier in 2012 revealed that residents of the United States generally prefer big, cheap TVs, and don’t care much about the display technology, or Web browsers and 3D. They just want more screen for the buck – and they’re getting it, judging by current retail prices.</p>
<p>Does plasma still have an edge over LCD in terms of picture quality? Well, if you prefer deeper blacks, wider viewing angles without color shifts, and colors similar to what the best CRT TVs and projectors could produce back in the day, then plasma is the way to go.</p>
<p>But LCD TVs are often sold on form factors – how thin they are, how light they are, and how cool they look when turned off and sitting in your living room or family room. Some folks like ‘em because they’re so bright and are largely unaffected by high ambient light levels. And you can’t buy smaller plasma TVs (&lt;40 inches) these days. Last time I looked, the 2nd-largest screen size category in terms of TV sales was 30 to 39 inches. That’s 100% LCD territory.</p>
<p>Plasma TVs are only made by a handful of companies (Panasonic, Samsung, and LG). Plasma TV shipments have been steadily declining over the past five years, aside from a little bump a couple of years ago. Plasma’s share of all TV shipments in Q2 2012 was about 5.5%, which means that more CRT TVs were shipped worldwide than plasma models. (You could look it up.) Simply put; people just aren’t buying it.</p>
<p>Pioneer got out of the plasma TV business almost 5 years ago because they could not compete on price and volume. Panasonic once predicted it would be shipping 11 million plasma TVs a year – that number is now less than half, and Panasonic has been forced to idle a good portion of its plasma fabs as a result of declining demand. (That’s also why Panasonic is now pushing 42-inch, 47-inch, 55-inch, and eventually 60-inch LCD TV screen sizes.)</p>
<p>It’s hard to argue with the numbers.</p>
<p> </p>
<p><em>Q. Now that TV prices are so low, should I still have my TV calibrated?</em></p>
<p>A. Not really. Just about every TV I’ve tested has at least one preset picture mode called “cinema” or “movie” or something like that. If you switch your TV into that mode (or one of the ISF Day or Night modes if present), your TV will be “close enough for government work” when all done.</p>
<p>To be sure, go into your picture menu and check to see that (a) brightness is around 45-50, (b) contrast is about 75-80, (c) sharpness is set to zero, (d) color temperature is set to “mid” or “warm,” (d) and any “auto” gamma, black level, contrast, or brightness modes are disabled or also set to zero.</p>
<p>I’ll wager that you’d be quite happy with your TV’s picture quality after all that. And you will have saved yourself quite a few dollars that can be put to better use, like your monthly pay TV subscription. Or a sound bar to overcome the acoustical limitations of super-thin TVs.</p>
<p>I should add that I still see some value in calibrating home theater projectors, even though some of them also come with “cinema” and “movie” picture presets. Getting the best projected image quality in a darkened room is a very different and more complex process than getting acceptable TV image quality in a fully-lit room.</p>
<p> </p>
<p><em>Q. You seem to have it in for Blu-ray and 3D sometimes. Why?</em></p>
<p>A. I don’t have any particular bias against the Blu-ray format. I own five Blu-ray players and have a sizable stack of movies (as well as a Toshiba HD-DVD player and a stack of HD-DVD discs. Any takers?). And there’s really nothing else out there that compares in image quality to movies on Blu-ray.</p>
<p>What I have taken other analysts, reporters, and public relations companies to task for is ignoring the shifting sands of public opinion, which now clearly favor electronic delivery of movies and TV shows via streaming (Amazon, Netflix, Vudu, and Hulu) over physical discs; the sales and rentals of which are in a steady decline.</p>
<p>I have long maintained that the average consumer doesn’t really care if they own a physical copy of a movie – they just want to be able to watch on their schedule. And for better or worse, streaming services satisfy that desire. Never mind that the picture quality isn’t always that great, or that the stream locks up from time to time. People value convenience and price over quality most every time (it’s an old axiom of economics), and Netflix and Amazon give it to them.</p>
<p>If and when Internet speeds get fast enough on a consistent basis, I’d bet that most consumers would be happy to stream HD movies from a ‘cloud’ server and drop the discs altogether. Or load them onto flash memory for viewing on multiple platforms, like tablets. Why do you think so many WiFi-enabled Blu-ray players have been sold in the past couple of years? It’s for the access to Netflix, YouTube, and Hulu.</p>
<p>Be honest now. How many movies do you have sitting unwatched on shelves in your house, still in their original shrink wrap? Birthday presents? Holiday gifts? Impulse purchases? Who knows from where they came. Unfortunately, it’s hard to get rid of used DVDs these days – even the local libraries don’t want them. Times are changing.</p>
<p>As for 3D, which seems to come along every other sunspot cycle, it was just too expensive and too confusing to the average consumer, who (as I stated earlier) just wants a big, cheap television. The early lack of 3D movie content (caused by exclusive Blu-ray “bundles”), competing presentation formats (active vs. passive vs. autostereo), and scarcity of 3D TV channels (DirecTV’s 3D channel has all but been shut down) just added to the problem.</p>
<p>3D has its place, and right now it’s better suited to larger screens in controlled viewing environments, such as movie theaters and theme parks. TV manufacturers don’t spend much time promoting 3D anymore – they’re just trying to figure out how to get you to buy a new TV these days; any TV.</p>
<p>So 3D will just become a another bell and whistle that you can embrace or ignore on your $800, 55-inch super-thin LED “smart TV” next January.</p>
<p> </p>
<p><em>Q. Is there really that much difference between indoor TV antennas? You’ve tested a bunch of them – isn’t it more about marketing hype than anything else?</em></p>
<p>A. There are many folks out there that are trying to “huckster” people out of their hard-earned cash with “enhanced” or “high-performance” indoor antennas that are little more than a variation on the 60+-year-old bow tie design.</p>
<p>A good example would be the Clear Cast X1, which is little more than a bow tie in a solid plastic housing, connected through a l-o-n-g piece of small diameter, lossy coaxial cable.  This antenna doesn’t work substantially different than a $5 bow tie that Radio Shack used to sell.</p>
<p>Yet, Clear Cast got quite a few people to shell out $70 for it (including me, but that was for testing purposes, not because of a condition of temporary insanity in my part!).</p>
<p>I’ve seen expensive antennas made out of old satellite dishes and UHF yagis. I’ve seen loop antennas, “placemat” antennas, and cylindrical antennas. (Remember the $400 Terk “tanning lamp” HDTV antenna from the late 1990s?)</p>
<p>The physics of TV antennas haven’t changed much since the 1940s and 1950s. Most antenna designs you see now are similar to patented designs from back then, only with some tweaks or enhancements. That said; there are some clever “placemat” antennas available for sale now, and the best models I’ve tested so far are made by Mohu. (The Walltenna isn’t too shabby, either, and Winegard’s FlatWave is a decent performer.)</p>
<p>I’ve gotten a few more models in recently for reviews and will probably just re-test the entire batch soon to establish a new baseline. From experience, I’d say that you don’t need to spend much more than $50 for a good performer, unless you want an amplified version. That will run you another $20 – $30.</p>
<p>But you should be cautious about indoor antennas that sell for three figures – you may be buying more marketing hype than anything else. <em>Caveat emptor!</em></p>
<p> </p>
<p><em>Q. You’ve been predicting recently that projectors are on the way out, and that large LCD screens are going to replace them. Yet, I continue to see market forecasts that projector sales will increase substantially each year. How do you explain that discrepancy?</em></p>
<p>A. What I’ve stated on more than one occasion is that the availability of large and inexpensive LCD screens (TVs and monitors) will have an impact on projector sales for small to mid-size rooms. That would be conference rooms, meeting rooms, boardrooms, and classrooms that seat anywhere from half a dozen to 50 people.</p>
<p>And I am not making this up. As I travel across the country teaching classes for clients, presenting at major trade shows, and just informally talking to AV consultants, designers, dealers, and systems integrators; I hear again and again that this is actually happening, and not on a small scale.</p>
<p>Apparently, the major push for dumping projectors and moving to a one-piece high-resolution display that doesn’t care about ambient lighting is coming from clients, who see Sharp’s 80-inch LCD TV for $3,999 at Costco and Best Buy and wonder why they can’t put one (or two) in their company offices.</p>
<p>From what I’ve heard, this is a strong trend at financial institutions. Based on early responses to threads I’m running on several LinkedIn groups, it’s also happening in classrooms. Shipments of large LCD and plasma monitor supports are running far ahead of rear-projection frame and supports.</p>
<p>The math behind it is easy to figure out. A two-piece projection screen (usually motorized) and ceiling-mounted projector wind up costing far more than the 80-inch TV (yes, dealers are installing those and getting a multi-year warranty on them). And there are no lights to dim, and no lamps to replace. I don’t have any empirical data on mean time between failures (MTBF) for these large TVs. But so far, people seem very happy with them.</p>
<p>Keep in mind this old saw: A one-piece display solution is always preferable to a two-piece display solution. That’s what’s driving this trend.</p>
<p> </p>
<p><em>Enough!</em> Time to close up the mail bag and enjoy the rest of the month before CES hits. <em>Happy holidays!</em></p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>November 26, 2012  2:16 PM</b>
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
			<?=getComments(4941)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4941)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2012/11/hdtv-expert-frequently-asked-questions-by-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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