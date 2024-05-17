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
		AND e.entry_id = 4257";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4257 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4257 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4257";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-samsungs-2011-line-show-the-edge-of-wonder.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4257";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Samsung&rsquo;s 2011 Line Show: &ldquo;The Edge Of Wonder&rdquo;" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Samsung&rsquo;s 2011 Line Show: &ldquo;The Edge Of Wonder&rdquo;" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Samsung&rsquo;s 2011 Line Show: &ldquo;The Edge Of Wonder&rdquo;" />
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
	<title>HDTV Magazine - HDTV Expert - Samsung&rsquo;s 2011 Line Show: &ldquo;The Edge Of Wonder&rdquo;</title>
	<meta name="keywords" content="blu ray, smart phones, ray players, lcd tvs, tablets smart, new, tvs, samsung, models, plasma, smart, blu, ray, players, lcd, –, line, glasses, inch, samsung’s, ’s, ”, tablets, phones, still" />
	<meta name="description" content="I’m not really sure what that catchphrase means. But Samsung is totally plugged in and turned on this year." />
	<meta name="title" content="HDTV Expert - Samsung&amp;rsquo;s 2011 Line Show: &amp;ldquo;The Edge Of Wonder&amp;rdquo;" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Samsung&amp;rsquo;s 2011 Line Show: &amp;ldquo;The Edge Of Wonder&amp;rdquo;" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-samsungs-2011-line-show-the-edge-of-wonder.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I’m not really sure what that catchphrase means. But Samsung is totally plugged in and turned on this year." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4257', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-samsungs-2011-line-show-the-edge-of-wonder.php">HDTV Expert - Samsung&rsquo;s 2011 Line Show: &ldquo;The Edge Of Wonder&rdquo;</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March 17, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=503&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
          <p>On March 16, it was Samsung’s turn to show everyone just how clever their engineers are by filling the Samsung Experience at New York’s Time Warner building with TVs, tablets, smart phones, laptops, cameras, and major appliances.</p>
<div id="attachment_1163" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1163" href="http://www.hdtvexpert.com/?attachment_id=1163"><img class="size-full wp-image-1163" title="Tim Baxter Speaks LS MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Tim-Baxter-Speaks-LS-MR1.jpg" alt="" width="600" height="304" /></a><p class="wp-caption-text">If you can turn it on and watch it, you can connect to it (or connect it to something else).</p></div>
<p>If there was one unifying theme in this blizzard of products, it was ‘connected.’ Digital cameras streaming photos wirelessly to TVs. Laptops connecting wirelessly to docking stations. 3D active shutter glasses connecting over Bluetooth to 3D TVs. Smart phones controlling TVs and appliances. TVs streaming content in real time to tablets and smart phones. Blu-ray players streaming movies to TVs.</p>
<p> </p>
<p>Oh, wait. They already do that last one. My bad!</p>
<p> </p>
<p>For fans of the 1960s TV secret agent spoof<em> Get Smart</em>, yesterday’s event was right out of an episode with Agents 86 and 99 and all their cool gadgets. The only things missing were the Cone of Silence and the famous ‘shoe that is actually a telephone.’ (I’m sure Samsung’s working on both.)</p>
<p> </p>
<p>Now, we did see some of Samsung’s goodies at CES. But it’s so noisy, so crowded, and so confusing out there that these line shows bring back necessary clarity and allow members of the press to more leisurely peruse the offerings to see what’s really hot.</p>
<p> </p>
<p>Samsung’s president Tim Baxter has a new name for the massive cross-platform, ‘send anything anywhere’ approach that Samsung has adopted for 2011: The Nth-screen strategy.</p>
<p> </p>
<p>And what exactly does THAT mean? To quote Baxter, <em>“It means our devices work together to create new experiences, while letting people access content anywhere, anytime on any screen.”</em> So there!</p>
<div id="attachment_1164" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1164" href="http://www.hdtvexpert.com/?attachment_id=1164"><img class="size-full wp-image-1164" title="Samsung Smart Hub Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-Smart-Hub-Demo-MR1.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">THis is where all the magic happens.</p></div>
<p>Once I navigated past an impressive display of ultra-thin TVs with minimal bezels floating in mid-air with Galaxy tablets and smart phones, I was able to zero on the TV, Blu-ray, and TV accessory products.  Of course, LCD TV is Samsung’s ‘bread-and-butter’ product, and there are 21 new models with LED backlights that range from 19 to 55 inches.</p>
<p> </p>
<p>The big news in connected TVs is Smart Hub, which is a hybrid of keyword video search, Samsung Apps, and a full Web browser (apparently not Google TV). This new ‘find video wherever it is to be found’ control will be included on all premium models. It will be included on all TVs 40 inches and larger. The D8000 and D7000 LCD TVs will also have a full QWERTY keyboard for searching video.</p>
<p> </p>
<p>Selected TVs can also share media with other connected devices (read: smart phones and tablets), and there’s a more user-friendly network setup and connection wizard. From a style standpoint, the bezels keep getting thinner – at this rate, they’ll soon be transparent – and power consumption has been cut back to meet Energy Star 5.1 standards.</p>
<p> </p>
<p>Seven of the new LED LCD TVs support 3D playback (D8000, D7000, and D6400 series). The usual tweaks have been made (improved 2D to 3D, a new 3D auto contrast mode, 3D brightness peaking, and improved surround audio), but the biggest change is in the glasses. They’re still active shutter, but now use Bluetooth wireless instead of infrared to connect to the TV.</p>
<div id="attachment_1165" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1165" href="http://www.hdtvexpert.com/?attachment_id=1165"><img class="size-full wp-image-1165" title="SSG3700CR Glasses MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/SSG3700CR-Glasses-MR1.jpg" alt="" width="600" height="353" /></a><p class="wp-caption-text">Do these 3D glasses rock the house, or what? (Sorry, they're not backwards-conpatible.)</p></div>
<p>That means, of course, that older Samsung 3D glasses will not work with 2011 TVs, nor will older TVs work with the new glasses. Speaking of those, they have an all-new design for 2011, with the battery compartment at the back of the temples. The temples themselves are curved and flexible and may just hold up better under normal use.</p>
<p> </p>
<p>Prices have come WAY down on LED LCD TVs. The top-of-the-line UN55D8000, which is available now at retail, has an estimated selling price of $3,599. Except for three models, the rest of the line is priced under $2,300, with thirteen models retailing below $1,500.</p>
<p> </p>
<p>The most bang for the buck will be the UN55D6000, which is ticketed at just $2,099 and should be well under $1,500 by September if past price trends are any indication. All models offer full 1080p resolution except for the 19”, 26”, and 32” D4000 series TVs.</p>
<p> </p>
<p>Plasma is still very much a part of the story at Samsung and there are 15 new models to please you. I’m still a big fan of plasma, and Samsung has come a long way in PDP picture quality lately (see my current review of the UN50C8000 3D plasma TV).</p>
<div id="attachment_1166" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1166" href="http://www.hdtvexpert.com/?attachment_id=1166"><img class="size-full wp-image-1166" title="Samsung D8000 3D Plasma Demo MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-D8000-3D-Plasma-Demo-MR1.jpg" alt="" width="600" height="381" /></a><p class="wp-caption-text">OK, time to face facts: 3D just looks better on a plasma TV.</p></div>
<p>Like the LCD sets, the new plasma offerings have a super-thin bezel. Eight of the new models are just an inch-and-a-half thick, something that wowed us when Hitachi showed it three years ago at CES. Now, we journalists just expect it, I guess.</p>
<p> </p>
<p>Smart Hub will be present on all of the D8000, D7000, and D6500 models, and all of the 3D goodies from the LED LCD line will also be included on all but a handful of 2011 plasma TVs. That includes the new active shutter glasses with Bluetooth. Other enhancements include that new deep black panel (which also cuts down on image brightness), a local contrast enhancement circuit – I’ll reserve judgment on that until I can test-drive it – and Cinema Black APL control.</p>
<p> </p>
<p>I’m willing to bet Samsung has also been working on faster-decaying phosphors to minimize the yellow ‘smear’ sometimes seen with fast motion. Panasonic’s also been attacking this problem, and what one company does, the other invariably copies. I should mention that the new 3D starter kits for both LCD and plasma TVs include not only the <em>Shrek</em> portfolio of movies in 3D, but also a new 3D Blu-ray pressing of <em>Megamind</em>, along with two pairs of the ‘new’ glasses.</p>
<p> </p>
<p>Plasma TVs have always represented a great value for top-notch performance. Samsung’s largest plasma, the 64-inch PN-64D8000, will set you back $3,799 and ships in April. There are also 55-inch and 51-inch models in the D8000 family (you read that correctly, champ – there are now 51-inch plasmas!), along with the same screen sizes in the D7000 line.</p>
<p> </p>
<p>Ten plasma sets fall below $2,500, with four models under a kilobuck. You can get into 3D plasma pretty cheaply now, starting with the PN43D490 ($799) which happens to be a 720p HDTV. The lowest-priced 1080p plasma equipped with 3D is the PN51D550, and Samsung figures it will be advertised at $1,299 – still a bargain, and you know it will be in the $800 to $900 range before long.</p>
<div id="attachment_1167" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1167" href="http://www.hdtvexpert.com/?attachment_id=1167"><img class="size-full wp-image-1167" title="Samsung - nVidia Gaming Console MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Samsung-nVidia-Gaming-Console-MR.jpg" alt="" width="600" height="392" /></a><p class="wp-caption-text">How'd you like to play 3D games on this setup? It's made up of tiled 27-inch Samsung 3D LCD monitors.</p></div>
<p>How’s about playback hardware? Samsung has seven new Blu-ray players, four of which are 3D models. The top-line BD-D7500 carries a $350 tag (you know that will drop quickly) and is loaded for bear. Oddly, the lower-priced BD-6700 ($300) and BD-6500 (also $300) support DTS-HD High Resolution audio, which the more expensive BD-D7500 doesn’t (neither does the comparably-priced BD-D7000). The 6700 and 6500 players also have component video outputs, something that is rapidly disappearing from all DVD and Blu-ray players as we head into an ‘analog sunset.’</p>
<p> </p>
<p>For bare-bones playback, you can pick up the BD-D5300 ($150). It’s got the same network connectivity features, but no component outputs and doesn’t support as many digital audio formats (Dolby only).  This player, and its sibling the BD-D5700, do not have ‘out of the box’ WiFi connectivity, as do the other new players. You’ll have to pick up a wireless LAN adapter to make that work.</p>
<p> </p>
<p>Samsung has a new feature for all of its connected Blu-ray players. It works with a Samsung-branded router and is called One Foot. You simply place the player within one foot of the router, turn everything  on, and the router’s IP address is automatically configured (this is something Panasonic should also be doing!) and you’re good to go, no matter where you place that Blu-ray player afterwards.</p>
<div id="attachment_1170" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1170" href="http://www.hdtvexpert.com/?attachment_id=1170"><img class="size-full wp-image-1170" title="QWERTY 2 MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/QWERTY-2-MR1.jpg" alt="" width="600" height="413" /></a><p class="wp-caption-text">A keyboard for your TV. Hmm, where have we seen that before? (Oh right, Web TV...)</p></div>
<p>There are also several new Blu-ray home entertainment systems, including the company’s first offering with 7.1 channel audio. The HW-D7000 supports 3D Blu-ray playback and Internet connectivity (along with all of those Smart Hub goodies), and there is a new 3D Sound Plus spatial surround system that claims to move sound waves along a z-axis – that is, towards and away from you. The HW-D7000 is ticketed at $599, while eight other models range in price from $350 (HW-D540) to $800 (HT-D6730W).</p>
<p> </p>
<p>One interesting new app (HBO GO) lets you watch HBO programming on all new Smart TVs or through Smart Blu-ray players. If you are a current HBO subscriber, you get the app at no charge. I’m not sure what the picture quality will be like, as HBO HDTV movies and programs have high-quality production values that depend on high bit-rate speeds to look their best. Maybe better than Netflix? We’ll see.</p>
<p> </p>
<p>I’ll wrap things up with a mention of the new Galaxy media players. These cuties measure 4 and 5 inches and run on the Android Froyo 2.2 OS. Both have 802.11n connectivity, front and rear cameras (shades of the new iPad), stereo speakers, and support for Flash 10.1. (Take that, Apple!) Skype comes loaded on the 4” model, and they can function as E-book readers with Noon and Kindle apps. Additional memory  can be loaded through a MicroSD card slot (maximum of 32 GB). No prices were announced at the event.</p>
<div id="attachment_1157" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1157" href="http://www.hdtvexpert.com/?attachment_id=1157"><img class="size-full wp-image-1157" title="Galaxy Tabs and Players CU MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Galaxy-Tabs-and-Players-CU-MR.jpg" alt="" width="600" height="363" /></a><p class="wp-caption-text">Here's how the new Galaxy media players (far right) compare in size to Galaxy tablets.</p></div>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March 17, 2011  4:59 PM</b>
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
			<?=getComments(4257)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4257)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-samsungs-2011-line-show-the-edge-of-wonder.php" type="text/javascript" charset="utf-8"></script>
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