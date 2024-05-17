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
		AND e.entry_id = 1249";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1249 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1249 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1249";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-249-how-to-paint-your-home-theater-and-insight-medias-ces-2008-best-buzz-awards.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1249";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media\'s CES 2008 Best Buzz Awards" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media\'s CES 2008 Best Buzz Awards" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media\'s CES 2008 Best Buzz Awards" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media's CES 2008 Best Buzz Awards</title>
	<meta name="keywords" content="home theater, best buzz, insight media, shutter glasses, buzz awards, inch, best, display, pdp, image, ces, technology, walls, theater, samsung, glasses, home, buzz, panasonic, good, oled, same, colors, media, room" />
	<meta name="description" content="Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version Electronic House magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.
 
And yes we went to CES, but no we didn't see everything.  Insight Media just released their &quot;Best Buzz&quot; awards for CES 2008 and they mentioned a few products we either didn't see or didn't talk much about, so we thought it would be good to go over some of them.  The Best Buzz awards are given by Insight Media at CES, and other trade shows each year.  You can't petition to win - you win by showing a product or technology that gets people talking - something that creates buzz because of its uniqueness, innovation, styling, boldness or is just plain cool.
  
Finally a listener put together a A/V room with components that cost less than $5,000. This is something that you would be proud to show in your home. Take a look for yourself. Listener Joe's $5K Theater" />
	<meta name="title" content="HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media's CES 2008 Best Buzz Awards" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media's CES 2008 Best Buzz Awards" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-249-how-to-paint-your-home-theater-and-insight-medias-ces-2008-best-buzz-awards.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version Electronic House magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.
 
And yes we went to CES, but no we didn't see everything.  Insight Media just released their &quot;Best Buzz&quot; awards for CES 2008 and they mentioned a few products we either didn't see or didn't talk much about, so we thought it would be good to go over some of them.  The Best Buzz awards are given by Insight Media at CES, and other trade shows each year.  You can't petition to win - you win by showing a product or technology that gets people talking - something that creates buzz because of its uniqueness, innovation, styling, boldness or is just plain cool.
  
Finally a listener put together a A/V room with components that cost less than $5,000. This is something that you would be proud to show in your home. Take a look for yourself. Listener Joe's $5K Theater" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1249', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-249-how-to-paint-your-home-theater-and-insight-medias-ces-2008-best-buzz-awards.php">HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media's CES 2008 Best Buzz Awards</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>February  9, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=513&category=Events & Tradeshows">Events & Tradeshows</a></b>, <b><a href="/category.php?id=478&category=HTPCs & Laptops">HTPCs & Laptops</a></b>, <b><a href="/category.php?id=447&category=PC & Laptop Technology">PC & Laptop Technology</a></b>
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
				<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<strong>Today's Show:</strong><br>

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-12.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br><br />
Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version <a href="http://www.electronichouse.com/">Electronic House</a> magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.<br />
 <br />
And yes we went to CES, but no we didn't see everything.  Insight Media just released their "Best Buzz" awards for CES 2008 and they mentioned a few products we either didn't see or didn't talk much about, so we thought it would be good to go over some of them.  The Best Buzz awards are given by <a href="http://www.insightmedia.info/">Insight Media</a> at CES, and other trade shows each year.  You can't petition to win - you win by showing a product or technology that gets people talking - something that creates buzz because of its uniqueness, innovation, styling, boldness or is just plain cool.<br />
  <br />
Finally a listener put together a A/V room with components that cost less than $5,000. This is something that you would be proud to show in your home. Take a look for yourself. <a href="http://www.htguys.com/archive/2008/February12_5K_Theater.html">Listener Joe's $5K Theater</a></p>

<p><strong>Insight Media's CES 2008 Best Buzz Awards</strong><br />
 <br />
<strong>Best Image of the Show</strong><br />
<u>Samsung 14-inch FHD OLED-TV</u></p>

<p><em>Samsung takes the Best Buzz for Best Image at CES with its Full HD (1920 x 1080) display from a 14-inch AM OLED and persistent crowds in the massive Samsung booth agreed.  The whopping 1920 x 1080 pixels in a super slim 14-inch OLED display rendered images in a photograph like quality as yet unmatched by any other.</p>

<p>Pixels were virtually nonexistent on the super thin (2cm) screen and the emissive pedigree of this OLED image gives the soft subtle hues and crisp bright tones that rival a mirror image of reality.  The image quality question, "are we there yet?", gets a resounding YES - now all Samsung has to do is find a way to replicate it in mass quantities - and oh yes...at an affordable price.</em></p>

<p><strong>Best PDP Display</strong><br />
<u>Panasonic 150" PDP TV</u></p>

<p><em>It's almost too easy but we can't avoid it.  The PDP Best Buzz goes to Panasonic's good-looking, crowd-pleasing 150-inch Plasma Display.  Introduced at CES, the 150-inch is now the largest unitary (no tiling) flat-screen display in the world, taking the title from Sharp's 108-inch LCD-TV.</p>

<p>The Panasonic's image quality, as well as size, was impressive.  Full HD on a screen this size wouldn't have been quite good enough, so Panasonic built a panel with 4000x2000 pixels - that's 8 million pixels instead of the approximately 2 million pixels in a Full HD display.</p>

<p>The 150-inch panel is made on a full sheet of glass from Panasonic's current fab, the same-sized glass that Panasonic normally uses to make eight 50-inch PDPs.  Volume production is scheduled for 2009 from the new Amagasaki manufacturing line.</em><br />
 <br />
<strong>Best PDP Technology Demo</strong><br />
<u>Panasonic High Efficiency PDP</u></p>

<p><em>Panasonic wins for its demonstration of a 42-inch prototype PDP with double the efficiency of current products.  Panasonic developed new phosphors and cell design technology for improved discharge, along with a new circuit and drive technology to significantly reduce power consumption.  As a result, the 42-inch prototype has twice the luminance efficiency and provides the same brightness as the existing 42-inch 1080p full HD PDP, while cutting the power consumption by half.  That's impressive, and got the show buzzing.</p>

<p>The double-efficiency technology forms the basis for next-generation PDPs, enabling even thinner profiles, larger screens, brighter images, higher definition and lower power consumption.</em></p>

<p><u>Pioneer's Super Black and Super Thin PDP Demos</u></p>

<p><em>Pioneer has shown there is plenty of life in the old dog with an amazing demo of low black levels on a next-gen KURO plasma monitor.  During the demo in a darkened room, you could see the faint glow of two Plasma monitors - and when the video came on, you realized there were three monitors in the room.   The blacks on this new KURO were so good that objects on the screen appeared to be floating in mid-air, while the colors had plenty of pop.  If SED technology wasn't officially buried yet, this demo did the trick.</p>

<p>Outside the booth, Pioneer also showed a 9mm thick 50-inch 1080p plasma monitor.  That's about 1/3 of an inch!  It was so thin we had trouble getting a clean photo of it.  Image quality was as good as any current-model KURO display, and the carpet around this demo was soaked from all the drooling over this Best Buzz winning display.</em></p>

<p><strong>Best 3D Displays</strong></p>

<p><em>CES created a new awareness of the possibilities for 3D TVs.  Long thought to be many years off, the possibility of creating a real 3D TV market soon, has now dawned on many players.  Of significance at CES was the demonstration of 3D TVs using projection, PDP and LCD technology.  Our congratulations go out to all three of these pioneering trendsetters.</em></p>

<p><u>3D Enabled Laser TV - Mitsubishi</u></p>

<p><em>We choose Mitsubishi for their demonstration of a Laser TV that can operate in 3D mode.  It is based upon DLP technology and active shutter glasses and was demonstrated for the media at a special event for the unveiling of the Laser TV.  Image quality was superb - perhaps the best we have seen, period.</p>

<p>Mitsubishi has not only created a very compelling 3D TV, but it is also trying to create a new TV category - Laser TV.  We think this summer the company will come to market with a 65-inch model that will have an impressive color gamut and great contrast.  For the 3D mode, it uses the same "SmoothPicture" technology on Mitsubishi's other DLP-TVs, which can be easily adapted to display stereoscopic images - once the content is properly formatted over an HDMI input.</em><br />
 <br />
<u>3D PDP-TV - Samsung</u></p>

<p><em>In an effort to differentiate their PDP-TV products from those offered by other companies, Samsung has turned to stereoscopic 3D.  Most of Samsungs' DLP-RPTVs are already 3D enabled, but now, it has extending 3D to PDPs.  This is the first time a major CE company has said it would commercialize a glasses-based stereoscopic PDP-TV.</p>

<p>To produce the 3D effect, Samsung borrows the same checkerboard pattern it uses on DLP-TVs and runs the PDP at 120 frames/sec.  For the left eye image, a checkerboard-sampled version of the image is displayed on the PDP.  This is synchronized with the shutter glasses to allow this image to be seen by the user.  The same is done for the right eye image in the second half of the frame.  Samsung undoubtedly modified the phosphors somewhat to speed up their response, especially in the green so as to lower crosstalk or ghosting between the two images.  This crosstalk is still not as good as its RPTV sets, but acceptable.  Users can buy a $150 3D kit when the sets go on sale in March.</em><br />
 <br />
<u>3D LCD-TV - SpectronIQ 3D</u></p>

<p><em>There was also big news and lots of buzz around the SpectronIQ 3D demonstration of its 3D LCD-TV product - a 46-inch model that will ship this summer.  This is the first time we expect to see a 3D LCD-TV sold in the US through major Big Box stores, which is why this is a big deal.  In addition, it is the first set to include a decoding chip that will allow the display of 3D content from an ordinary DVD, HD DVD or Blu-ray player.  The only rub is that studios will need to press special disks with this encoded 3D version, but it is a big step in creating an easy-to-use consumer 3D TV.</p>

<p>Spectron IQ will use a 3D technology called micro-pol.  It is a line interlaced technique whereby alternate lines contain the left and right eye images that can be seen in each eye using passive polarized glasses (cheaper than active glasses).  Sensio Technologies Inc., of Montreal, Canada, will provide the 3D codec.</em></p>

<p> <br />
<strong>Best OLED Display</strong><br />
<u>31-inch OLED TV from Samsung</u></p>

<p><em>At the massive Samsung CES booth, the company validated the OLED-TV category with a (now you're talking) 31-inch AM OLED display.  The crowds came in droves to see the future of emissive TV with a bright, colorful image that rivals any flat screen TV currently being shipped.</p>

<p>Samsung did a wonderful job of showcasing both the 31-inch and it's smaller 14-inch cousin for the CES crowds.  It was one of the "must-see" exhibits at CES and the reason why we give it the OLED Best Buzz of the show award.</em></p>

<p><strong>Best Innovations</strong><br />
<u>Texas Instruments' DualView Mode</u></p>

<p><em>Texas Instruments' demonstration of the DualView mode on 3D enabled RPTV sets was truly innovative and captivating.  The idea is to create two independent views on the same TV.</p>

<p>The idea leverages the active shutter glasses used in normal 3D mode, but instead of flashing the left and right sides of the glasses to see stereoscopic images, both sides of the glasses open and shut at the same time.  The TV updates at 120 frames per second, in alternate frames running at 60Hz are synchronized to one set of glasses - and one image on the TV, while alternate frames can be viewed with the other set of glasses.  And these images can be different.  This means gamers can get two different views while playing the same game.  This is pretty cool and another novel and innovative use of the 3D display technology.  The quality of the active shutter glasses needs to improve before commercialization can begin,  nonetheless we choose Texas Instruments for their DualView display concept.</em></p>

<p></u>Vudu's HDTV Set Top Box</u></p>

<p><em>VUDU used CES to launch a $399 set-top box that can download HD movies and TV shows over the Internet on a purchase or rental basis.  There is no annual subscription fee, and it will play back in the 1080p/24 format. Expansion storage is also available.  If you can get FHD movies from sources on the Internet, why do you need a Blu-ray or HD DVD player where you pay a lot more to buy the movie?  Food for thought.</em></p>

<p>About Insight Media<br />
Insight Media (www.insightmedia.info) is a leading publishing and consulting firm focused on the display industry. With its core team of world-class display experts, Insight Media tracks the technology, components, products, markets, applications, manufacturing and business aspects of consumer and professional display markets. The company publishes daily and monthly news and analysis as well as in-depth annual technology/market reports. It also hosts industry conferences, provides strategic and tactical consulting services and offers industry education via webinars and on-site seminars. </p>

<p><strong>How to Paint Your Home Theater</strong><br />
Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version Electronic House magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.</p>

<p>Since the point of a home theater is to enjoy movies and HDTV, you need an environment that supports that, not one that distracts from it.  Every display device out there works by beaming colored light at your eyeballs.  Whether it's a TV or a projection screen, it becomes one giant lamp in the front of the room.  But the light doesn't just hit your eyes, it shines on every surface in the room.  If the walls in the room are very reflective, that light will bounce right back at the screen and wash out the picture.  For this reason, dark colors are the best for your home theater.  We all know from studying the color spectrum in grade school that black can loosely be thought of as zero light reflection and white represents complete light reflection.  So obviously the darker your walls, the better your theater will perform.</p>

<p>But it isn't just the color of the walls that matters.  Paint manufacturers have created ways for even black paint to be somewhat reflective by introducing a sheen, or gloss, to the finish.  So ideally you'd have a dark color with no gloss at all.  Those with small children or animals know that the truly matte finishes are very difficult to clean, to a semi-gloss or satin finish is probably a good compromise.  The rule to remember is that your walls should be as dark and as muted as possible.  If the aesthetics committee is asking for white or beige high gloss paint, you may need to step in and offer an opinion.</p>

<p>Don't forget about the ceiling.  It can be just as reflective as the walls themselves.  Having painted a few ceilings myself, this is not a job anyone relishes, but sometimes you have to sacrifice for your passions.  some believe that if you decide to paint the ceiling and the walls different colors, the ceiling should always be the darker of the two colors.  In most cases this is the best rule of thumb.  If the ceiling is lighter than the room, it gives the illusion of being a light source when your watching a movie.  It almost feels like there are lights on even when they aren't.</p>

<p>Of course there's more you can do to the walls to help with audio.  You can rough them up a bit by adding textured finishes.  Smooth walls will reflect sound more than a textured wall.  But if you're really concerned about audio reflection, you'll want to add some sort of fabric to the walls.  The fabric will absorb the sound, while the texture wall will just make it bounce off funny, and thus reduce the amount of reflection that hits your ear.  For fabric options you can mount sound panels, hang tapestries or use thick curtains.  You can even use thick curtains where there aren't windows by painting a faux window, or hanging them over a large mirror.  This give the sound absorption you need and even adds depth and size to the room.  You simply close the when your watching something to eliminate light reflecting from your imaginary window.</p>

<p>Read the full Electronic House article <a href="http://www.electronichouse.com/article/home_theater_colors_choose_carefully/">here</a>.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>February  9, 2008 12:24 PM</b>
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
			<?=getComments(1249)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 1249)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv-and-home-theater-podcast-249-how-to-paint-your-home-theater-and-insight-medias-ces-2008-best-buzz-awards.php" type="text/javascript" charset="utf-8"></script>
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