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
		AND e.entry_id = 357";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 357 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 357 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 357";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 357";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download It\'s a Big, Big Mitsubishi World" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="It\'s a Big, Big Mitsubishi World" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="It\'s a Big, Big Mitsubishi World" />
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
	<title>HDTV Magazine - It's a Big, Big Mitsubishi World</title>
	<meta name="keywords" content="big screen, mitsubishi electric, line show, consumer electronics, light source, mitsubishi, big, screen, hdtv, market, new, still, dlp, first, products, set, well, light, laser, show, business, manufacturers, models, far, color" />
	<meta name="description" content="Mitsubishi Electric Digital Television presented their annual &quot;line show&quot; for the press who cover consumer electronics. The event this year fell on the 7th of April and was held at the elegantly appointed Hyatt Huntington Beach Resort and Conference Center in Orange County, California. It was tough duty but I was there for you! The Mitsubishi dealers gathered the following day for the same presentation.

Our afternoon led off with a brief economic report: &quot;We will end this year with $31 billion in global sales,&quot; said Cayce Blanchard, VP of Corporate Communications. &quot;The company,&quot; she emphasized,&quot; is in good financial health.&quot; Indeed, they posted a nifty $819 million net profit (Gee, just inching ahead of HDTV Magazine!!) 

Following on the heals of that report a &quot;simulated&quot; &lt;em&gt;broadcast &lt;/em&gt;of the new MTV HD channel was cued up-a channel which Mitsubishi is co-sponsoring. It's clearly not your grandfather's TV any more. Nor will pops admit to watching the bare midriff programming. This set maker is out to win a younger crowd..." />
	<meta name="title" content="It's a Big, Big Mitsubishi World" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="It's a Big, Big Mitsubishi World" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Mitsubishi Electric Digital Television presented their annual &quot;line show&quot; for the press who cover consumer electronics. The event this year fell on the 7th of April and was held at the elegantly appointed Hyatt Huntington Beach Resort and Conference Center in Orange County, California. It was tough duty but I was there for you! The Mitsubishi dealers gathered the following day for the same presentation.

Our afternoon led off with a brief economic report: &quot;We will end this year with $31 billion in global sales,&quot; said Cayce Blanchard, VP of Corporate Communications. &quot;The company,&quot; she emphasized,&quot; is in good financial health.&quot; Indeed, they posted a nifty $819 million net profit (Gee, just inching ahead of HDTV Magazine!!) 

Following on the heals of that report a &quot;simulated&quot; &lt;em&gt;broadcast &lt;/em&gt;of the new MTV HD channel was cued up-a channel which Mitsubishi is co-sponsoring. It's clearly not your grandfather's TV any more. Nor will pops admit to watching the bare midriff programming. This set maker is out to win a younger crowd..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=357', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php">It's a Big, Big Mitsubishi World</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>April 10, 2006</b>
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
				<p>Mitsubishi Electric Digital Television presented their annual "line show" for the consumer electronics press corps. The event fell  on the 7th of April this year and was held at the elegantly appointed Hyatt Huntington Beach Resort and Conference Center in Orange County, California. It was tough duty but I was there for you! The Mitsubishi dealers gathered the following day for the same presentation.</p>

<p>Our afternoon led off with a brief economic report: "We will end this year with $31 billion in global sales," said Cayce Blanchard, VP of Corporate Communications. "The company," she emphasized," is in good financial health." Indeed, they posted a nifty $819 million net profit (Gee, just inching ahead of HDTV Magazine!!) </p>

<p>Following on the heals of that report a "simulated" <em>broadcast </em>of the new MTV HD channel was cued up-a channel which Mitsubishi is co-sponsoring. It's clearly not your grandfather's TV any more. Nor will old pops admit to watching the bare midriff programming. This set maker is clearly out to win a younger audience to a big screen experience. To do that they engaged "hip" stars using even "hipper" language, and, oh yes, GAMES. </p>

<p><br />
<img alt="mitsubishi1.jpg" src="http://www.hdtvmagazine.com/articles/images/mitsubishi1.jpg" width="384" height="271"align="right"/><strong>Get The big Picture</strong><br />
Size definitely counts. "BIG screen" (once a bad word in consumer electronics) was the underlying theme for this year's snazzy lineup. The average screen size is by all measure moving up. We are no longer content with anything mandated by CRT limitations and already showing dissatisfaction with the smaller HDTV models of recent history.  </p>

<p>Mitsubishi's ability to recover a once a commanding lead in the big screen HDTV market is still questioned by many industry analysts. They scratch their heads at what seems a vain attempt to stay competitively in play against the likes of Samsung, Sony, Toshiba, Philips, LG etc. Wall Street was showing no skepticism, though, and ran the Mitsubishi Electric's stock up nearly twofold over the last 12 months. The company is, of course, into much more than consumer television.  "...if you can imagine it, Mitsubishi Electric makes it!" proclaims their web site (with no apology for exaggeration). </p>

<p>Don't be confused by the name "Mitsubishi" either. Mitsubishi televisions are made under the Mitsubishi Digital Electronics America, Inc. umbrella, which for most of its U.S. business is headquartered in Irvine, California. Founded in 1870, Mitsubishi built a broadly based conglomerate and played a central role in the modernization of Japanese industry. The name "Mitsubishi" is found today on cars, trucks, power plants, banks, financial services, rubber plantations, airplanes, defense products, gas, oil, and heavy road equipment, but each of the 30 principal Mitsubishi companies (and hundreds of subsidiaries) have lived autonomously since 1946 when our post war occupation forces demanded decentralization (a breakup). The presidents of each of the 30 divisions bearing the Mitsubishi name still meet, however, on the second Friday of every month at what is known as Mitsubishi Kinyokai, or the Friday Club. So, while Mitsubishi Electric is itself big, it stands apart from all of the other like-named companies sharing only a common cultural heritage-something still important in a ruling class structure which exists today in Japan. </p>

<p>In their first venture into television Mitsubishi targeted the big screen (of that day) and delivered the largest CRT set in the world. They later abandoned the single tube to concentrate on larger three tube rear projectors. They lept into the HDTV business realizing their strength in big screen sizes would serve them well, and it did. They became the undisputed market leader in HDTV.  But they were trounced (in terms of market share) in subsequent years by their huge competitive rivals. That trouncing led to speculation over their chances of survival in television. Considering their great diversity they had no particular need to stay in the game. But for good or bad they stuck with it and now look to be a far more formidable and respected competitor than in the past and with a more broadened target market. </p>

<p><strong>Replay</strong><br />
Those who have been pleased with their first HD-Mitsubishi purchases will no doubt be back to them for the next round.  Like the rebirth of the Internet (now being called Interent-2) this next phase of the HDTV market development should be called HDTV-2 (you heard it hear first, folks). By that I mean that the second wave of early adopters are stepping out now to replace their first HD primary viewing device with one having the newer advances in color management, resolution, brightness, and digital connections like HDMI. Mitsubishi should do well if the innovations they have placed in imaging are promoted to their old audience as well as to the younger generation straying from TV into gaming. I can tell you from first hand observations that they do deliver the goods at a price that was unimaginable a few years ago. They do show heavy reliance upon rear projection and this category has weakened, especially in Japan where small living spaces demand flat screens. While the U.S. market has taken to flat screen LCDs and plasmas almost as a fashion statement the new level of performance in DLP projection is thought to successfully go swim that tide. Americans still have more room than most do in other nations.</p>

<p>If bigger is better more pixels is supreme. Mitsubishi is quickly becoming an all 1080p outfitter. No more of that wimpy 720 p stuff, which has you sitting at 4, 5, or even more picture heights so you can resolve the images clearly. In order to sell big screens within the average living space in America homes today you must sit closer to the screen-three or less picture heights without artifacts distracting or pushing you back. The best way to do that is to make sure all two million, two hundred thousand pixels (potential in the 1080 standard) are painted on the screen with progressive scanning with noise and motion anda interlace artifacts held to a minimum. That, in theory, allows, or even encourages, a closer viewing distance, which means you can buy more square inches of picture for more money! The challenge has been viewing of old NTSC grade content on these newer sets. You may have the seating arrangement suited to HDTV only to be driven back by the "ugly" NTSC presentation. As HDTV becomes the ONLY signal we are willing to watch, that issue will vanish.  </p>

<p>As added inducements all manufacturers have invented new features which a thinking HDTVite just can't stand being without. I mean, how can a thoughtful person buy a DLP the size of mount Everest that doesn't have a "6 Primary Color System, TurboLight 150TM, Plus 1080pTM, Tru1080p Processing, 4D Video Noise Reduction, PerfectColor TM , ClearThought-R, Easy Connect and enough interfaces to satisfy ... well ... anyone? And ... due to increased light output efficiencies you may need dark glasses to avoid retina damage! I mean...when is it too bright? That threshold seems crossed to me. Of course, this intense brightness is not for us at home but rather for us at the store so we can see that this or that set is far superior by the amount of light shinning in your eyes. The good news is that these over-bright displays moving into your local TV store are fully adjustable to a more pleasing level...like we movie aficionados (in particular) appreciate.</p>

<p><img alt="Mits2a.jpg" src="http://www.hdtvmagazine.com/articles/images/Mits2a.jpg" width="370" height="316"align="left"/><br />
It's a Laser in your future ... <br />
The BIG headline that preceded the event appeared in the April 3rd edition of the New York Times in the Business Day section. It announced Mitsubishi's plan for a new 3 color (red, green, blue) laser addressed DLP projector. The promise of this development is a smaller footprint, still more brightness, a light source that will last for the lifetime of the set, lower power consumption (becoming a huge deal in California), a wider color gamut, and it can slip into just about any space in your house.  </p>

<p>The announcement caused more than a few eyebrows to lift not to mention expectations. I went there with the dreamy eyed idea of buying one of these laser devices to replace my dearly departed Toshiba, which bit the dust a few weeks ago. But alas, all what was available to me and you was a handsome mock up and a standing promise from the technical team to fill the box by Christmas of 2007. </p>

<p>Why announce something so far in advance? That question produced several nonsensical answers. One, for instance, said that it would set up an anticipation in potential plasma buyers (Mitsubishi is only in LCD and DLP) so they would set aside their plasma purchase (and dollars) for this hot new thing when it finally arrives. The dealers, I am told, have disagreed and fear that the announcement will not only hold off a decision on plasmas but a decision on constant light source DLP models as well. Another more plausible answer was that they (Mitsubishi) are not the only ones working on laser addressing and wanted to get the first PR jump in the market. According to the company's newly installed president, Masaharu Abe, they have their hands full in making the production model. Large investments must still be made. Mr. Abe's strength lies in bringing highly complex developments to market quickly. I would speculate he was brought in to hasten the laser to its market.</p>

<p>As I walked about the exhibition pushing my ample nose up against the new LCD and DLP 1080p products it struck me how much more dependant these manufacturers have become upon the quality of the signals reaching their products. The old complaint one incessantly hears about DirecTV, DISH, and cable companies concerning "over compressing" signals (and, thus, producing visual artifacts) will not help big screen 1080p quality perception nor sales. It is precisely this point which makes the high-definition DVD an important component in the future of big screen 1080p success. Mitsubishi remains neutral on DVD formats and will have, I overheard, a high-def DVD product in the marketplace when the format war blood is mopped up. </p>

<p>Another concern expressed to me by top management is the retailing of 1080p displays. Vic Murty, who says his job intensifies after this line show, notes that one of the weak ends is still how retail exhibits their products. While things are better than when HDTV first hit the marketplace it is still a big stone's throw away from being perfect. Not only do manufacturers have to make their products artificially bright to attract showroom attention in overly light-filled spaces, but the signals used to show off their displays are far too often not uniform and seldom, if ever, 1080p native. One might wish that the retailers in the future would just lease out space to the manufacturers and let them install their own well-trained sales staff to present their products in a controlled environment that best shows the goods. But that would take a major restructuring of the retail business. Anyone going to the Chicago Mart knows, however, that this has been a means for doing high-end business for half a century or longer. In fact, the ancient bazaars around the world were often hundreds of individual shops renting space under one roof. </p>

<p>That's all for today. Tomorrow I will walk you through all of the models as they were presented at the line show and we will learn the meaning of the new acronyms swelling up the HDTV "dictionary".  I will post technical details along with the aesthetics of Mitsubishi's four "fully featured" 1080p LCDs, now in 37 and 46 inch sizes, and seven new 1080p DLP models ranging in size from 52, 57, 65 on up to a whopping 73 incher. <br />
Until then__Dale</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>April 10, 2006  6:30 PM</b>
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
			<?=getComments(357)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 357)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php" type="text/javascript" charset="utf-8"></script>
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