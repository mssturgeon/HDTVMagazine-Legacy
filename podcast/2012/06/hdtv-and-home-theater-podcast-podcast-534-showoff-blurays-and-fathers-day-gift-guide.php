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
		AND e.entry_id = 4833";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4833 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4833 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4833";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-534-showoff-blurays-and-fathers-day-gift-guide.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4833";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&rsquo;s Day Gift Guide" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&rsquo;s Day Gift Guide" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&rsquo;s Day Gift Guide" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&rsquo;s Day Gift Guide</title>
	<meta name="keywords" content="blu ray, home theater, home automation, power strip, dirty power, home, power, blu, theater, ray, colors, sound, film, dad, audio, show, even, make, something, lost, action, light, sounds, ’s, video" />
	<meta name="description" content="It&amp;#039;s that time of year again where Dads get to to have the spotlight for a whole day! If you are like us, the love of your family is all you need to make your day worth it! But if the family insists on getting you something it may as well be something that you will enjoy. After all, who needs another tie!  Print out this page and leave it someplace where the family can find it. Our goal was to create a list that didn&amp;acirc;��t break the bank so even your teenagers could find something that would make you smile!" />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&amp;rsquo;s Day Gift Guide" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&amp;rsquo;s Day Gift Guide" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-534-showoff-blurays-and-fathers-day-gift-guide.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It&amp;#039;s that time of year again where Dads get to to have the spotlight for a whole day! If you are like us, the love of your family is all you need to make your day worth it! But if the family insists on getting you something it may as well be something that you will enjoy. After all, who needs another tie!  Print out this page and leave it someplace where the family can find it. Our goal was to create a list that didn&amp;acirc;��t break the bank so even your teenagers could find something that would make you smile!" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4833', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-534-showoff-blurays-and-fathers-day-gift-guide.php">HDTV and Home Theater Podcast - Podcast #534: Show-off Blu-rays and Father&rsquo;s Day Gift Guide</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>June  7, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=411&category=Blu-ray">Blu-ray</a></b>, <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>
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
				<h2>Today&#8217;s Show:</h2>
<h3 id="internal-source-marker_0.07052331887927132">Top Blu-ray Discs to show off your Home Theater</h3>
<p>We are often asked about what blu-ray discs we use to show off our system. Here are the HT Guys recommended discs based on our Blu-ray reviews to show off your home theater. If you have a go to disc that you always use please post it to the comments section or send us an email.</p>
<p><strong><a href="http://www.htguys.com/news/2011/11/8/super-8-blu-ray-review.html">Super 8 </a></strong><br />
Wow, Super 8&#8242;s audio performance is far beyond super, it&#8217;s spectacular. There&#8217;s always something interesting happening in the sound space. Sometimes children can be heard playing behind you, typewriters and phone rings seem as if they come from another room of your house, and cars race by from all directions. Rear speakers also get a workout from helicopters that fly by and sharp chirping crickets in the distance. Low end sounds get lots of attention with heavy punches, deep resonating footsteps, gunshots, and of course lots of wide sounding explosions. However, the train crash scene is without a doubt the best sounding spectacle I have ever heard on my home theater system. That scene is worth the price of this Blu-ray alone. I found myself ducking from the sound of the train careening out of control from behind me. The explosions were so deep and loud I thought my neighbors were going to complain, and it utilized the speakers so well it seemed like my room was bigger than it was. I have to tip my hat to Dolby for doing such an awesome job on this TrueHD 7.1 mix.</p>
<p>Super 8 looks luscious and cinematic. The warm colors pop on screen and help feature the stylish clothing of the 70&#8242;s with including crazy multi-colored shirts and all of their plentiful patterns. The film grain is light, but it does not impend on the clarity, you&#8217;re able to see every loose hair, pre-teen pimple, and even the shine on braces. I was quite impressed by the shadow detail. Even though a good amount of this film happens in the dark, very little details are lost to the night.</p>
<p><strong><a href="http://www.htguys.com/news/2011/10/4/transformers-dark-of-the-moon-blu-ray-review.html">Transformers: Dark of the Moon </a></strong><br />
What a threat. This Blu-ray features a rare Dolby TrueHD mix. I wish we saw more of them, because the audio on Dark of the Moon is flawless. I was amazed at the heft and surround usage even when watching the Paramount logo. Bullets and missiles whizz by your head, explosions and bombs fill the room with expanding rumbles, transforming robots hum and clang with crystal clarity, and helicopters fly by with heavy wind churning blades. The rear speaker mix seemed a little hot, but that&#8217;s how I like it. Even with all of the insane action, dialog was never lost in the audio chaos.</p>
<p>The clarity was astounding, I was able to see loose hairs, pores, complex patterns on fancy suits, winkles, and  tiny beads of sweat. I&#8217;ve never seen a movie with this much fast moving CGI appear this clean and free of any compression problems. Shadows and colors were handled with such skill it seemed as if the giant robots from outer space were real. Their was hardly any film grain, and it made the robots look hyper-realistic. Landscapes of a crumbling downtown Chicago were impressive, and brought home the scale of the battle. Colors were a little warm an made colors more vibrant.</p>
<p><strong><a href="http://www.htguys.com/news/2012/4/3/mission-impossible-ghost-protocol-blu-ray-review.html">Mission Impossible &#8211; Ghost Protocol</a> </strong><br />
This is what action movies should sound like. Full of depth, power, and subtlety Ghost Protocol has all bases covered. Gunshots resonate, trains rumble, explosions are deep and wide, and punches land with heavy thuds, and give your subwoofer a decent workout. Rear channels also get plenty of action with whizzing bullets, whipping winds, crashing cars, sounds of heavy traffic, and even crickets. Dialog isn’t ignored and never gets lost in the sounds of spy action. It’s easy to see that Dolby put lots of time and care into creating a top notch audio presentation for this Blu-ray.</p>
<p>Mission Impossible has impressive video performance and presents a smooth detailed picture throughout the film. With clean clarity it’s easy to see tiny wrinkles on faces, fine details in clothing, and textures in bricks and cobblestone. Colors seem a bit cold in a few scenes, but still remain to look natural and help feature the stunning colors like the red walls of the Kremlin, and the beautiful orange colored sand of the Dubai desert. Film grain is light, and very little details are lost to the darkness.<br />
<strong><br />
<a href="http://www.htguys.com/news/2012/1/5/kung-fu-panda-2-blu-ray-review.html">Kung Fu Panda 2 </a></strong><br />
I can’t think of another family film with this much rumbling subwoofer action. At one point my daughter looked at me and asked if I just felt the couch shake. This Dolby True HD mix pops on all cylinders. Every silly line of dialog is easily heard, and is never lost in the mix. The frequency range is wide enough to hear the high pitched ringing of clashing swords and bass filled heavy footsteps. Surround speakers are put to good use making the audio feel spacious while filling the room with sounds of fireworks, rowdy crowds, and fierce kung fu action.</p>
<p>It’s almost unfair to judge the video quality of a computer animated film of this caliber. Hundreds of people are paid to make sure the colors are vivid and the details are stunning, and it shows. Kung Fu Panda 2 looks fantastic on Blu-ray. The individual black and white hairs on Po are easily seen, due to the stunning clarity. Detail on peacock feathers, individual drops of water, and fireworks all shine with clean crispness. Colors are vivid and help bring the beautiful colorful landscapes of China to life. No details are ever lost in the shadows and show no sign of compression issues.</p>
<h4>Audio Only</h4>
<ul>
<li><a href="http://www.htguys.com/news/2012/1/24/real-steel-blu-ray-review.html">Real Steel</a></li>
<li><a href="http://www.htguys.com/news/2011/12/20/cowboys-aliens-blu-ray-review.html">Cowboys &amp; Aliens</a></li>
<li><a href="http://www.htguys.com/news/2011/11/15/harry-potter-and-the-deathly-hallows-part-2-blu-ray-review.html">Harry Potter and the Deathly Hallows: Part 1&amp;2</a></li>
<li><a href="http://www.htguys.com/news/2011/10/11/fast-five-blu-ray-review.html">Fast Five</a></li>
<li><a href="http://www.htguys.com/blu-ray/2012/4/10/war-horse-blu-ray-review-25.html">War Horse</a></li>
</ul>
<h3 id="internal-source-marker_0.6476335153165586">Home Theater Terms</h3>
<ul>
<li><strong>Lumens </strong>- a measure of the total &#8220;amount&#8221; of visible light emitted by a source. As a reference a 100 watt incandescent light bulb outputs 1750 lumens. By the way, The bigger the image you&#8217;re projecting, the less concentrated your projector&#8217;s brightness is going to be on each square inch of the screen. So if you have a lot of ambient light coming into the room and you have a large screen you’ll want to go with a projector with a high lumen rating. Most high end projectors are not very bright. It is pretty much assumed that they are going into dedicated screening rooms.</li>
<li><strong>Decibel </strong>- a measurement of Sound level. Our ears detect changes in volume in a non-linear fashion. A decibel is a logarithmic scale of loudness. A difference of 1 decibel is an almost imperceptible change in volume. It takes about 3 db for most humans to hear a difference and 10 decibels is perceived by the listener as a doubling of volume. On your AVR when you go from -15db to -5db the sound volume hitting your ears is doubled. As a side note, it takes a doubling of wattage in your AVR for an increase of 3db. That’s why paying an extra $200 for the next model up AVR just because it is 125W instead of 100W is a waste of money. Provided that’s the only additional feature.</li>
<li><strong>Dynamic Range</strong> &#8211; in audio DR is the difference between the loudest possible undistortedsound and thesoftest sound that is still audible. Fun fact: A 100db sound difference between say a rocket engine and a whisper results in the rocket engine being one billion times louder than the whisper. Powerful receivers with low distortion produce sounds with the greatest dynamic range. The dynamic range of human hearing is roughly 140 dB.</li>
</ul>
<h3 id="internal-source-marker_0.5182999870653835">Father&#8217;s Day Gift Guide for the Home Theater Lovin Dad!</h3>
<p>It&#8217;s that time of year again where Dads get to to have the spotlight for a whole day! If you are like us, the love of your family is all you need to make your day worth it! But if the family insists on getting you something it may as well be something that you will enjoy. After all, who needs another tie!  Print out this page and leave it someplace where the family can find it. Our goal was to create a list that didn’t break the bank so even your teenagers could find something that would make you smile!<br />
<strong></strong></p>
<p><strong>Logitech Squeezebox Radio Music Player with Color Screen</strong> (<a href="http://www.amazon.com/gp/product/B002LARRDK?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B002LARRDK&amp;qid=1338581693&amp;ref_=sr_1_21&amp;s=aht&amp;sr=1-21">Buy Now $150</a>) &#8211; If your dad likes to listen to music while he works, then why not make it possible for him to listen to his entire library and then some! The Logitech Squeezebox Radio is a network device that brings a world of music to the garage or workshop. Listen to Internet radio, subscription services, or your personal digital music collection anywhere in your home, all over your Wi-Fi network.<br />
<strong></strong></p>
<p><strong>Video Streamer</strong> &#8211; There are so many options to chose from here. If your dad is into the iTunes ecosystem an <a href="http://www.amazon.com/gp/product/B007I5JT4S?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B007I5JT4S&amp;qid=1338581693&amp;ref_=sr_1_1&amp;s=aht&amp;sr=1-1">AppleTV</a> is the perfect choice. It goes for $99. Perhaps you want the flexibility of a <a href="http://www.amazon.com/mn/search/?_encoding=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;field-keywords=Roku&amp;url=search-alias%3Delectronics">Roku box</a>. They have models ranging in price from $59 to $89. Maybe you want a versatile streamer for less than $50? Then the <a href="http://www.amazon.com/gp/product/B005MJWGJC?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B005MJWGJC&amp;qid=1338583345&amp;ref_=sr_1_1&amp;s=electronics&amp;sr=1-1">Netgear NeoTV</a> is perfect gift! Either way dad will always have something to watch.</p>
<p><strong>5 Pack 1-Foot Extension Power Cable</strong> &#8211; If your dad is into home theater he probably has a bunch of components plugged into a power strip or conditioner. Since switches, video streamers, or slingboxes all have different sized plugs it&#8217;s hard to make them all fit in a typical power strip. These little cables make it possible to use every space on your power strip regardless of the plug shape! They are well worth the $10 (<a href="http://www.amazon.com/gp/product/B000CRFOMK?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B000CRFOMK&amp;ref_=pd_sim_e_4">Buy Now</a>) they cost so you may want to buy two sets!!</p>
<p><strong>Belkin PureAV® PF30 Home Theater Power Console</strong> &#8211; Now that you have the one foot extension cables you need something to plug them into. The <a href="http://www.amazon.com/gp/product/B000T9DHZW?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B000T9DHZW&amp;qid=1338584334&amp;ref_=sr_1_3&amp;sr=8-3">Belkin PF30</a> will protect dad’s equipment as well as clean up dirty power. If you have dirty power you’ll hear and see an improvement. If you don’t have dirty power the PF30 will protect dad’s equipment from power surges! At $90 its a small price to pay for piece of mind!<br />
<strong></strong></p>
<p><strong>Home Automation</strong> &#8211; We have talk quite a bit about home automation. This is a gift where the whole family benefits. Why not have a smart home? There are plenty of gadgets and protocols available. Start out small and work your way up to a full system. Here is a list of a home automation products starting at about $30 (<a href="http://www.amazon.com/mn/search/?_encoding=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;field-keywords=home%20automation&amp;url=search-alias%3Daps">Home Automation Products</a>).</p>
<p>If none of the above speak to you or your dad. You can always go with a few <a href="http://www.amazon.com/b?_encoding=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393193&amp;node=2901953011&amp;ref_=sa_menu_blu8">Blu-ray Movies</a>. You can find a great selection of movies starting at $5. Or maybe an HD video camera for less than $100. You can pick up the <a href="http://www.amazon.com/gp/product/B004FLL5BI?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393185&amp;creativeASIN=B004FLL5BI&amp;qid=1338839929&amp;ref_=sr_1_3&amp;s=photo&amp;sr=1-3">Kodak ZX5</a> for $87.90. Its 1080p and waterproof so you can take it to the beach this summer. And of course, if you have to, you can still buy dad a <a href="http://www.amazon.com/gp/product/B0073EZL28?ie=UTF8&amp;tag=hdtvandhometh-20&amp;linkCode=shr&amp;camp=213733&amp;creative=393177&amp;creativeASIN=B0073EZL28&amp;redirect=true&amp;ref_=s9_al_bw_g193_ir04">tie</a>!</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2012-06-08.mp3">Download Episode #534</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>June  7, 2012 10:42 PM</b>
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
			<?=getComments(4833)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4833)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2012/06/hdtv-and-home-theater-podcast-podcast-534-showoff-blurays-and-fathers-day-gift-guide.php" type="text/javascript" charset="utf-8"></script>
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