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
		AND e.entry_id = 5030";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5030 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5030 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5030";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-ise-2013-oh-its-on-pete-putman.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5030";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - ISE 2013: Oh, It&rsquo;s ON! &ndash; Pete Putman" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - ISE 2013: Oh, It&rsquo;s ON! &ndash; Pete Putman" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - ISE 2013: Oh, It&rsquo;s ON! &ndash; Pete Putman" />
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
	<title>HDTV Magazine - HDTV Expert - ISE 2013: Oh, It&rsquo;s ON! &ndash; Pete Putman</title>
	<meta name="keywords" content="laser light, inch inch, light engine, lcd displays, projector manufacturers, lcd, laser, projectors, projector, light, inch, –, lasers, models, lumens, ’s, displays, color, engine, lcds, screen, laservue, projection, show, even" />
	<meta name="description" content="At the recently-concluded Integrated Systems Europe show, projector manufacturers showed concern about the impact of super-sized, inexpensive LCD displays. And they&amp;acirc;��re fighting back." />
	<meta name="title" content="HDTV Expert - ISE 2013: Oh, It&amp;rsquo;s ON! &amp;ndash; Pete Putman" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - ISE 2013: Oh, It&amp;rsquo;s ON! &amp;ndash; Pete Putman" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-ise-2013-oh-its-on-pete-putman.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="At the recently-concluded Integrated Systems Europe show, projector manufacturers showed concern about the impact of super-sized, inexpensive LCD displays. And they&amp;acirc;��re fighting back." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5030', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-ise-2013-oh-its-on-pete-putman.php">HDTV Expert - ISE 2013: Oh, It&rsquo;s ON! &ndash; Pete Putman</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>February  1, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=497&category=Front Projection">Front Projection</a></b>, <b><a href="/category.php?id=511&category=LCD HDTVs">LCD HDTVs</a></b>, <b><a href="/category.php?id=503&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>, <b><a href="/category.php?id=312&category=Sports">Sports</a></b>
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
          <div id="attachment_2760" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2760" alt="ISE is a joint venture between InfoComm and CEDIA - and drew a sizable crowd, even with cold, wet weather." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/ISE-Outdoor-Sign-WEB.jpg" width="600" height="371" /><p class="wp-caption-text">ISE is a joint venture between InfoComm and CEDIA – and drew a sizable crowd, even with cold, wet weather.</p></div>
<p>Much has been made of the rapid price drops in the LCD TV market; specifically, LCD TVs that measure 65 inches and up. Ever since Sharp rolled out its 70-inch and 80-inch 1080p LCD TV products in 2011, consultants and systems integrators have been switching over to these projection screen-sized displays instead of traditional front projectors and separate screens.</p>
<p>There are many reasons for this trend, not the least of which is the low prices on the 70-inch, 80-inch, and 90-inch Sharp products – about $2,000, $3700, and $8000, respectively. When compared to a ceiling-mounted projector and motorized screen, it’s just not a fair fight. Add in the additional labor and wiring of power and class 2 control and video signals, and the big LCDs come out clearly ahead.</p>
<p>There are other reasons why investment banks and universities are making the switch away from projection. One in particular is the need to replace lamps every few thousand hours (if they last that long). Another is the need with certain projectors to clean dust out and replace air filters. Neither of these maintenance issues are factors with large LCD TVs, which also come with extended warranties if installed by an authorized dealer/integrator.</p>
<p>And of course, there’s the ambient lighting issue. Clients can legitimately ask, “What is the point of a nice conference room with plenty of windows if you have to keep closing them every time you make a presentation?” With LCD displays, you don’t need to, unless you have a glare problem.</p>
<p>From my perspective, the market for 2000- to 3000-lumens projectors that are ceiling-mounted in classrooms and meeting rooms has turned irreversibly towards self-contained flat screen displays. This trend will only accelerate as these screens continue to drop in price and more competitors jostle for a share of the pie.</p>
<p>But projector manufacturers aren’t ready to fold up shop and cry, “uncle!” At ISE 2013, more than a few “lampless” projectors made their debut, and they’re aimed at stemming the tide of mongo LCDs.</p>
<div id="attachment_2757" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2757" alt="Sony Laser 3LCD Projector 3-4 View WEB" src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Sony-Laser-3LCD-Projector-3-4-View-WEB.jpg" width="600" height="491" /><p class="wp-caption-text">I can’t tell what’s more amazing: That Sony harnessed a laser light engine to a 3LCD projector, or that they started with 4000 lumens and 1920×1200 resolution.</p></div>
<p>Perhaps the most intriguing product was found in the Sony booth, where an installation-sized 3LCD chassis was up and running. This product, which doesn’t have a model number or price yet, uses a 100% laser light illumination engine to project Wide UXGA (1920×1200) images.</p>
<p>It wasn’t a static demo, either. The projector was sequencing through a series of full-color graphics and photos (no video, though) and the color was impressive. What was even more impressive was the use of WUXGA 3LCD panels (not LCoS or DLP). This is the first publicly-shown 3LCD projector to use lasers – even Epson, who is the dominant player in HTPS LCD fabrication and one of the top brands of LCD projectors – hasn’t shown one yet.</p>
<p>Sony’s prototype, which will be officially launched at InfoComm this coming June, is rated at 4000 lumens of brightness, both in white and color light output. It has interchangeable lenses and supports image warping and soft-edge blending.</p>
<p>When it came to discuss the workings of the laser light engine, “mum” was the word. I suspect the laser light engine is being used to stimulate phosphors to get red, green, and blue light. The only thing that has me wondering is the light output, which is on the high side for a laser/phosphor system. Well, all will be revealed in about five months…</p>
<div id="attachment_2759" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2759" alt="Mitsubishi's also mixing it up with three models of LaserVue projectors." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Mitsubishi-LaserVUE-Projectors-WEB.jpg" width="600" height="402" /><p class="wp-caption-text">Mitsubishi’s also mixing it up with three models of LaserVue projectors.</p></div>
<p>Not far away, Mitsubishi took the wraps off a new line of LaserVue DLP projectors. These “hybrid” models build on the same projection technology that Mits developed for its erstwhile LaserVUE rear projection TV sets; employing a red LED, numerous blue laser diodes, and a single-segment green phosphor color wheel.</p>
<p>Unlike Sony, Mits opted to go with three different models for its coming-out party. The NW31U-EST WXGA (1280 x 800 resolution, 2500 lumens) extreme short throw model will arrive in April, followed shortly by two standard throw models: the NW30U WXGA (1280 x 800, 3000 lumens) and the NF32U (1920×1080, 3000 lumens).</p>
<p>The Mits projectors are also notable in that they are part of the new “cloud” lineup – these projectors can connect quickly and easily to the Internet to download and stream files. (We’ve come a long way from those slow, tedious and unreliable “wireless projector” demos of the late 1990s!) And they can mirror any Android or iOS tablet that would be used to control that remote computer or server.</p>
<p>So – how long are the lasers supposed to last in these new projectors? The stock response is 20,000 to 30,000 hours. In reality, it’s the power supply that often craps out before the lasers, a problem that popped up more than a few times with the LaserVUE TVs. I’d assume that both Sony and Mitsubishi have since gathered much useful data on power supply lifetimes and de-rating to ensure reliable service.</p>
<div id="attachment_2762" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2762" alt="BenQ expanded their line of laser DLP projectors..." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/BenQ-Laser-Projector-CU-WEB.jpg" width="600" height="291" /><p class="wp-caption-text">BenQ expanded their line of laser DLP projectors…</p></div>
<div id="attachment_2763" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2763" alt="...while Panasonic made their hybrids the centerpiece of a nice energy conservation demo." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Panasonic-Hybrid-Projector-Demo-WEB.jpg" width="600" height="327" /><p class="wp-caption-text">…while Panasonic made their hybrids the centerpiece of a nice energy conservation demo.</p></div>
<p>BenQ also showed laser-engined DLP projectors at the show, while nearby, Casio had a full line of LED/laser hybrids. The color on most models I saw was considerably better than the first crop that came out in 2010 and 2011 – obviously, engineers are taming the excessively-saturated shades of red and blue that LEDs and lasers create. (BenQ uses lasers exclusively; Casio uses both lasers and LEDs.)</p>
<p>Although Epson didn’t show a laser 3LCD product, I’m quite sure one is in the works at the Matsumoto labs. And you can be sure that other projector manufacturers will have lampless models of their own to show in Orlando later this year.</p>
<div id="attachment_2756" class="wp-caption aligncenter" style="width: 610px"><img class="size-full wp-image-2756" alt="Samsung's got a 95-inch LCD (and a 75-inch version, too) to make the projector guys uncomfortable." src="http://www.hdtvexpert.com/wp-content/uploads/2013/02/Samsung-95-inch-E-LED-Display-2-WEB.jpg" width="600" height="705" /><p class="wp-caption-text">Samsung’s got a 95-inch LCD (and a 75-inch version, too) to make the projector guys uncomfortable.</p></div>
<p>Is the use of a laser, LED, or hybrid light engine enough to stem the tide to big LCDs? Only a handful of projector marketing guys I spoke to at the show were optimistic that the onrush of LCDs could be stopped or delayed.</p>
<p>While lasers and LEDs make replacement lamps go away, the issues with ambient light and the costs of installing a separate screen and projector mount remain. And the soon-to-be-available crop of 4K LCD displays in sizes from 50 to 100 inches will just raise the stakes even higher.</p>
<p>Still; it’s good to see that projector manufacturers are fighting back and innovating some cool designs along the way. (And if they still need motivation, all they had to do was check out the 75-inch and 95-inch edge-lit LCD displays in the Samsung booth…)</p>
<p> </p>
<p> </p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>February  1, 2013  4:11 PM</b>
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
			<?=getComments(5030)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 5030)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/02/hdtv-expert-ise-2013-oh-its-on-pete-putman.php" type="text/javascript" charset="utf-8"></script>
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