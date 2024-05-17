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
		AND e.entry_id = 5172";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5172 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5172 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5172";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-614-ultimate-home-theater-in-a-box.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5172";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box</title>
	<meta name="keywords" content="blu ray, home theater, ray player, surround sound, center channel, receiver, home, get, theater, sound, blu, ray, system, ’s, player, ”, need, speakers, hdtv, price, smart, great, well, inch, video" />
	<meta name="description" content="There are so many Home Theater in a Box systems out there many of which cost less than $400. But seriously, what can you get for $400. A good center channel typically costs more than most HTIB systems on the market today. Each year we try to assemble a Home Theater in many boxes that we would be proud to show off in our homes. Our system will have at a minimum a HDTV, Blu-ray Player, Receiver, and 5.1 speakers." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-614-ultimate-home-theater-in-a-box.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="There are so many Home Theater in a Box systems out there many of which cost less than $400. But seriously, what can you get for $400. A good center channel typically costs more than most HTIB systems on the market today. Each year we try to assemble a Home Theater in many boxes that we would be proud to show off in our homes. Our system will have at a minimum a HDTV, Blu-ray Player, Receiver, and 5.1 speakers." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5172', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-614-ultimate-home-theater-in-a-box.php">HDTV and Home Theater Podcast - Podcast #614: Ultimate Home Theater in a Box</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>December 19, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=478&category=HTPCs & Laptops">HTPCs & Laptops</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3>Ultimate Home Theater in a Box</h3>
<p>There are so many Home Theater in a Box systems out there many of which cost less than $400. But seriously, what can you get for $400. A good center channel typically costs more than most HTIB systems on the market today. Each year we try to assemble a Home Theater in many boxes that we would be proud to show off in our homes. Our system will have at a minimum a HDTV, Blu-ray Player, Receiver, and 5.1 speakers.</p>
<p>For this feature we choose components that we either have direct experience with or have experience with a similar model made by the same manufacturer. In years past we would set a maximum price but this year we are not doing that. We are defining a system that can had by anyone who is serious about home theater. These system will look and sound great by anyone’s definition!</p>
<p>&nbsp;</p>
<h4>Braden</h4>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00BWLJLD8/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00BWLJLD8&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Sharp LC-80LE757 80-inch Aquos Quattron 1080p 240Hz Smart LED 3D HDTV ($3688)</a></p>
<p dir="ltr">It’s hard to pass up an 80” TV when you can have it for less than $4000. I was trying to keep the budget to under $6000 or so, but when you consider that an 80” TV is nearly front projection size, and it doesn’t suffer from ambient light issues or degrade at all during the day, the $3688 price is a great deal.  It has the exclusive Quattron color technology that delivers a billion more colors, so you get a more powerful picture with brighter yellows, deeper blues, and richer golds. And it has a 240Hz Refresh Rate with AquoMotion 480 that doubles the effective refresh rate so you can see sharper fast-action movies and sports. And it’s a smart TV, so need need to add an external streaming player for apps. For those who want to save a little, scale down to a 70” TV, the <a href="http://www.amazon.com/gp/product/B009SJNTIY/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B009SJNTIY&amp;linkCode=as2&amp;tag=hdtvandhometh-20">VIZIO E701i-A3 70-inch 1080p 120Hz Razor LED Smart HDTV</a> is just as smart, but only costs $1498.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B00CALM12W/?tag=hdtvandhometh-20">Denon AVR-X3000 7.2-Channel 4K Ultra HD Networking Receiver with AirPlay ($899)</a></p>
<p dir="ltr">It’s no secret Denon is my favorite receiver brand, and the X3000 made our Receiver Buying Guide for a reason. It packs a ton of features, quality and big sound into a very reasonable price. It has 7 HDMI inputs and two HDMI outputs for multiple zone viewing. The X3000 comes with Audyssey’s Gold package so you can get your sound exactly the way you want it. The unit is Airplay and Windows 8 compatible and comes with Denon Remote app for mobile devices. The receiver&#8217;s network functionality supports Internet radio services such as SiriusXM, Pandora, and Spotify. Like we said in the Buying Guide, there are many more features than we can’t list. It’s a very capable receiver and will last you for years.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00GK5409G/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00GK5409G&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Klipsch RF-42 II Reference Series 7.1 Home Theater System ($1494)</a></p>
<p dir="ltr">Going back to the trusty favorites, Klipsch has never let me down. They make incredibly high quality, supremely efficient speakers that sound great and are easy to listen to. The provide the detail you need for subtle soundtracks and effects in movies and the power you need for all the big explosions. Movies and music will sound just as they were intended to &#8211; no matter the room type or size &#8211; with the power, detail and emotion of Klipsch Reference II sound. This system includes: 1 RC-42 II Center Channel Speaker, 2 RF-42 II Tower Speakers, 2 RS-41 II Surround Speakers, 2 RB-41 II Bookshelf Speakers and 1 Klipsch SW-110 Subwoofer. This system gets you into 7.1 sound at a great price. There’s nothing keeping you from upgrading each of the parts over time, like the subwoofer or maybe a bigger center channel.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/gp/product/B00BFDHVAS/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B00BFDHVAS&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Samsung BD-F5900 3D Wi-Fi Blu-ray Disc Player ($98)</a></p>
<p dir="ltr">Unless you’re buying the OPPO, a Blu-ray player is a Blu-ray player. This Samsung unit ticks all the boxes for what you need in a solid performer, and it adds a couple uniques features as well. For example, S-Recommendation helps you find new things to watch, and Samsung Apps offer you new ways to entertain. It has a built-in web browser and Wi-Fi and allows you to stream from your other devices with AllShare. It supports 3D if that’s what you’re into and can up-convert your favorite DVDs to near Blu-ray quality. Of course it has all the standard apps as well, like Netflix, Hulu Plus, Vudu and Pandora, so there’s no need for a secondary streaming player.</p>
<p dir="ltr">
<h4>Miscellaneous</h4>
<p dir="ltr">You can’t have a good home theater without a good universal remote, so throw in a <a href="http://www.amazon.com/gp/product/B004OVECU0/ref=as_li_ss_tl?ie=UTF8&amp;camp=1789&amp;creative=390957&amp;creativeASIN=B004OVECU0&amp;linkCode=as2&amp;tag=hdtvandhometh-20">Logitech Harmony 650 Remote Control</a> for $60 to round out the package. Sure it isn’t the best in the Harmony line, but it controls everything in the package: TV, Receiver, Blu-ray player, Set Top (for Cable or Satellite), with one slot left for a device to be added later. You have to provide your own batteries, but pick up a few rechargeables and you’re all set.</p>
<p dir="ltr">Figure around $200 for miscellaneous cables and connectors, and the package is complete.</p>
<p dir="ltr">
<h4>Summary</h4>
<p dir="ltr">Total cost, not including shipping or tax, for a complete home theater with an 80” television and 7.1 surround sound comes out to $6439. I was shooting for a $6000 budget, and came pretty close. It’s difficult to fathom that a budget of $6000 can get you an 80” HDTV. If you need to scale back a bit, you can get the same system, but with a 70” HDTV for only $4249. That’s unreal. A 70” TV is gigantic, and the 7.1 surround sound will completely immerse you in the experience. Not a bad way to get into HDTV and Surround sound all at once without breaking the bank.</p>
<p>&nbsp;</p>
<h4>Ara</h4>
<p dir="ltr"><a href="http://www.amazon.com/dp/B00BC4SNMK/?tag=hdtvandhometh-20">Panasonic TC-P65ZT60 65-Inch 1080p 600Hz 3D Smart Plasma TV $3200</a></p>
<p dir="ltr">Well this should be no surprise to anyone. I picked a plasma TV. I still think plasmas produce the best picture out there and for the price its hard to beat. My first HDTV cost me $4000 for a 50 inch DLP that was 720p. For $800 less I get 15 more inches on the diagonal and weighs the same. We were blown away by the picture when we saw it in a light controlled room. And that’s the Key, while it will produce a great picture during the daylight it really excels in darker rooms. So make sure you have window covering when watching movies during the day. If you are watching normal daytime fare it won’t be an issue. The TV is a Smart TV with all the apps, controls, and gimmicks that come with it. You can connected it to your network so your iRule or Roomie remotes can control it via Ethernet. But the main sale for me is the glorious picture! Get these before they are gone forever.</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B00D1VX2U0/?tag=hdtvandhometh-20">Yamaha RX-A2030 9.2-Channel Network Aventage Audio Video Receiver $1600</a></p>
<p dir="ltr">I was going to go with a receiver that was on our <a href="http://www.htguys.com/podcasts/2013/12/13/podcast-613-hdtv-buying-guide-2013.html">Receiver Buying Guide</a> but then I figured I want to try everything. Having owned three Yamaha receivers and recommending hundreds more I decided to go with the Aventage line. Every aspect of this receiver is about sound. From the parts used to the vibration dampening mechanics this receiver is for those who want a high quality audio experience. It too has all the niceties of the other receivers on our list: 4K, iOS/Android app, Airplay, Auto calibration and a ton more. Being a 9.2 receiver you will be prepared for the future what ever that ends up being!</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.aperionaudio.com/speakers/intimus-home-theater-speakers">Aperion Audio Intimus</a> speakers and <a href="http://www.hsuresearch.com/products/vtf-3mk4.html">Hsu VTF-3 MK4</a> subwoofer 7.1 Speaker System (Total Cost $1970)</p>
<p dir="ltr">For this system I am pairing the Intimus speakers with a HSU Research subwoofer. We’ll start with two <a href="http://www.aperionaudio.com/speakers/intimus-home-theater-speakers/intimus-4t-tower-speaker">Intimus 4T Tower</a> speakers ($320 each), one <a href="http://www.aperionaudio.com/speakers/intimus-home-theater-speakers/intimus-4c-center-channel-speaker">Intimus 4C center</a> channel ($160), and four Intimus 4B bookshelf speakers ($260/pair). Aperion is a fantastic speaker company out of Portland Oregon. They offer a free 30 day in home audition at no risk to you. The Intimus line sounds great and will match quite nicely with the Yamaha receiver. On the subwoofer side you know I am going to select the one that rocks my home theater, the <a href="http://www.hsuresearch.com/products/vtf-3mk4.html">VTF-3 MK4</a> ($650). Its a good sized subwoofer that packs a wallop!</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B009LRR5AQ/?tag=hdtvandhometh-20">OPPO BDP-103 Universal Disc Player (SACD / DVD-Audio / 3D Blu-ray) $500</a></p>
<p dir="ltr">There are so many blu-ray players out there for less than $100 so why go with the $500 oppo. The main reason is video processing. The Oppo has Marvell&#8217;s Kyoto-G2H video processor with the latest generation Qdeo technology will mean all the content whether blu-ray, DVD, or streamed video will look its best. Plus you can connect a cable or set top box to the Oppo and scale and process its video with the Kyoto-G2H. And that makes this the best choice out there for Blu-ray!</p>
<p>&nbsp;</p>
<p dir="ltr"><a href="http://www.amazon.com/dp/B007I5JT4S/?tag=hdtvandhometh-20">AppleTV $90</a></p>
<p dir="ltr">I know what you are asking, Why get an AppleTV when your Blu-ray player and TV already support the same content. For me its about flexibility. Airplay makes it easy to stream content from my iOS devices to my TV plus I have a lot of purchases in iTunes so my music, TV, and Movies are in Apple’s cloud and this device makes it easy to get to. If you are not into Apple you can swap out a Chromcast or Roku 3 for the same price. Well for the Chromcast you’ll get a rebate <img src="http://s0.wp.com/wp-includes/images/smilies/icon_smile.gif" alt=":-)" class="wp-smiley" /> </p>
<p dir="ltr">
<h4>Miscellaneous</h4>
<p dir="ltr">This is where we throw in everything else. But in this case there really isn’t much else left. Well maybe a cool remote control. A <a href="http://www.logitech.com/en-us/universal-remotes">Harmony</a> Remote would tie it all together nicely but if you have a tablet or smartphone you may want to consider a <a href="http://www.roomieremote.com/">Roomie</a> or <a href="http://www.iruleathome.com/">iRule</a> remote. Which ever route you choose we are allocating $200. this assume you already have a tablet or phone if you go that route.</p>
<p dir="ltr">The last thing to consider is cables, power strips, and connectors. We will allocate an additional $250 for these items as well.</p>
<p dir="ltr">
<h4>Summary</h4>
<p>In years past we would limit ourselves to a specific dollar amount but that would also limit our choices. This system, while not cheap, won’t break the bank and will be considered outstanding by anyone in the industry. Sure you can do better, but the additional cost may not produce a noticeably better experience. So without further ado… Ara’s ultimate home theater Christmas gift comes in at $7810 plus taxes. That is pretty insane for what you get. Consider Braden’s first 42 inch enhanced definition (DVD quality) plasma was $5000 and as stated earlier Ara’s 720P DLP was $4000. Enjoy!</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2013-12-20.mp3">Download Episode #614</a></p><br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>December 19, 2013 10:02 PM</b>
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
			<?=getComments(5172)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5172)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2013/12/hdtv-and-home-theater-podcast-podcast-614-ultimate-home-theater-in-a-box.php" type="text/javascript" charset="utf-8"></script>
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