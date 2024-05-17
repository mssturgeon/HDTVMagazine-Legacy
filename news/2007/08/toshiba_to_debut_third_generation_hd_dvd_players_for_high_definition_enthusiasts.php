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
		AND e.entry_id = 662";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 662 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 662 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 662";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/08/toshiba-to-debut-third-generation-hd-dvd-players-for-high-definition-enthusiasts.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 662";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts" />
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
	<title>HDTV Magazine - Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts</title>
	<meta name="keywords" content="dvd players, high definition, third generation, toshiba america, consumer products, toshiba, dvd, players, high, hdmi, new, generation, content, models, definition, audio, products, movie, may, third, rate, consumer, features, player, america" />
	<meta name="description" content="Toshiba America Consumer Products, L.L.C. (&quot;Toshiba&quot;), announced today its third generation of HD DVD players for the U.S. market. Continuing to outpace the competition in sales of dedicated high definition players and responding to increased consumer demand, Toshiba revealed three new sleek and stylish models for an ultimate HD home movie experience. With all three new models priced under $500 (MSRP), Toshiba is delivering a powerful line-up of HD DVD players designed to meet the aggressive growth in demand for high definition TVs.

&quot;With a majority market share in unit sales of next generation DVD players, consumers are speaking loud and clear, and they are adopting HD DVD as their HD movie format of choice,&quot; said Jodi Sally, Vice President of Marketing, Toshiba's Digital A/V Group. &quot;Because of the proven manufacturing efficiencies of the HD DVD format, Toshiba can bring this level of innovation in technology to a new generation of players with cutting-edge functionality at affordable prices.&quot;" />
	<meta name="title" content="Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/08/toshiba-to-debut-third-generation-hd-dvd-players-for-high-definition-enthusiasts.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Toshiba America Consumer Products, L.L.C. (&quot;Toshiba&quot;), announced today its third generation of HD DVD players for the U.S. market. Continuing to outpace the competition in sales of dedicated high definition players and responding to increased consumer demand, Toshiba revealed three new sleek and stylish models for an ultimate HD home movie experience. With all three new models priced under $500 (MSRP), Toshiba is delivering a powerful line-up of HD DVD players designed to meet the aggressive growth in demand for high definition TVs.

&quot;With a majority market share in unit sales of next generation DVD players, consumers are speaking loud and clear, and they are adopting HD DVD as their HD movie format of choice,&quot; said Jodi Sally, Vice President of Marketing, Toshiba's Digital A/V Group. &quot;Because of the proven manufacturing efficiencies of the HD DVD format, Toshiba can bring this level of innovation in technology to a new generation of players with cutting-edge functionality at affordable prices.&quot;" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=662', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/08/toshiba-to-debut-third-generation-hd-dvd-players-for-high-definition-enthusiasts.php">Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August  6, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">Toshiba to Debut Third Generation HD DVD Players for High Definition Enthusiasts</p>

<center><i>Toshiba takes the Hi Def movie experience to a whole new level with 24 frames per second support, CE-Link connectivity, High Bit Rate Audio capabilities and unmatched interactivity functionality</i></center><br />
<br />

<p><B>WAYNE, N.J., Aug. 6 /PRNewswire/</B> -- Toshiba America Consumer Products, L.L.C. ("Toshiba"), announced today its third generation of HD DVD players for the U.S. market. Continuing to outpace the competition in sales of dedicated high definition players and responding to increased consumer demand, Toshiba revealed three new sleek and stylish models for an ultimate HD home movie experience. With all three new models priced under $500 (MSRP), Toshiba is delivering a powerful line-up of HD DVD players designed to meet the aggressive growth in demand for high definition TVs.</p>

<p>"With a majority market share in unit sales of next generation DVD players, consumers are speaking loud and clear, and they are adopting HD DVD as their HD movie format of choice," said Jodi Sally, Vice President of Marketing, Toshiba's Digital A/V Group. "Because of the proven manufacturing efficiencies of the HD DVD format, Toshiba can bring this level of innovation in technology to a new generation of players with cutting-edge functionality at affordable prices."</p>

<p>Building on the success of its first and second generation players, new leading enhancements in select models in the third generation line include an improvement in video performance with 1080p/24 frames per second (24p) support. Movie films are traditionally captured at 24 frames per second and select Toshiba third generation HD DVD players will be able to maintain this frame rate allowing consumers to enjoy movies in their native frame rate.</p>

<p>Also added to the line is "CE-Link" (HDMI(TM)-CEC) connectivity which offers the capability to communicate with and control another CE device in a whole new way. For instance, using "CE-Link" with "One Touch Play" consumers will be able to turn on a CEC capable HDTV and a Toshiba HD DVD player, and start playing a movie, with a single touch of a button on the player remote.</p>

<p><br />
<B>Toshiba Third Generation HD DVD Models</B></p>

<p>Toshiba's third generation family starts with the entry level HD-A3 player featuring 1080i output capability. The other two new models, Toshiba's HD-A30 and HD-A35, will output 1080p resolution (1920 x 1080p), the highest HD signal currently available. Both models are capable of outputing signals at 1080p/24 frames per second so consumers can enjoy movies in their native frame rate. The HD-A30 and HD-A35 models also feature "CE-Link" (HDMI-CEC), allowing two- way control between the HD DVD player and a TV through an HDMI connection.</p>

<p>The top-of-the-line HD-A35 also adds support for Deep Color via HDMI allowing compatible display devices to deliver outstanding video quality - displaying millions of possible colors to billions of possible colors.</p>

<p>Additionally, the HD-A35 offers 5.1 channel analog output and High Bit Rate Audio (up to 7.1 channel) via HDMI. With content encoded in 7.1ch, this advanced surround sound is achieved through the HDMI connection bypassing the player's internal audio processor and sending the signal to a 7.1 capable A/V receiver. High Bit Rate Audio will allow the consumer to integrate the HD-A35 with the latest multi-channel A/V receivers and enjoy a whole new dimension of high definition home entertainment.</p>

<p>All of Toshiba's third generation HD DVD players are refined with new cosmetic designs. Rounded edges, slim chassis (only 59.5 mm - nearly half as tall as first generation players) and high gloss, black acrylic face plates create very sleek devices - a perfect complement to Toshiba's award winning REGZA(R) HD LCD televisions.</p>

<p><br />
<B>Beyond the Hi Def Movie Experience</B></p>

<p>Starting with 1st generation players, all of Toshiba's HD DVD players support the enhanced features of the HD DVD format mandated by the DVD Forum including picture-in-picture video, audio commentary and the ability to allow web-enabled network capabilities. Using the Ethernet ports found on all Toshiba HD DVD players, once connected to the network, users can access bonus features, as available, from a movie studio's server. This data is then saved in the player's persistent storage and can be accessed by the user. In addition to accessing new bonus features, some HD DVD discs may include locked prerecorded content which can be unlocked with a downloadable key from the studio's online server. Web-enabled capability is now opening the door to a new entertainment experience beyond hi def video and audio.</p>

<p>"Consumers will always have a consistent experience with HD DVD as we have an established platform to keep the players updated to ensure the ultimate HD movie experience," said Yoshi Uchiyama, Group Vice President, Toshiba's Digital A/V Group. "A mandatory Ethernet port in all HD DVD players ensures that consumers can receive updates to their units to support the latest offerings from the studios and maximizing their investment in the format."</p>

<p>All of Toshiba's HD DVD models are backward compatible allowing users to enjoy their libraries of current DVD and CD software while enhancing the look of regular DVDs by upconverting them to near high definition quality.</p>

<p>For more information on HD DVD, please visit www.toshibahddvd.com.</p>

<p>Expected Pricing and Expected Availability:<br />
HD-A3 ($299.99, October 2007)<br />
HD-A30 ($399.99, September 2007)<br />
HD-A35 ($499.99, October 2007)</p>

<p><br />
<B>About Toshiba America Consumer Products, L.L.C.</B></p>

<p>Toshiba America Consumer Products, L.L.C. is owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation, a world leader in high technology products with subsidiaries worldwide. Toshiba is a pioneer in HD DVD, DVD and DVD Recorder technology and a leading manufacturer of a full line of home entertainment products, including flat panel TV, combination products and portable devices. Toshiba America Consumer Products, L.L.C. is headquartered in Wayne, New Jersey. For additional information, please visit www.tacp.toshiba.com.</p>

<p><br />
<B>Important Notes:</B></p>

<p>HD DVD with high-definition content required for HD viewing. DVD with standard definition content will upconvert to near HD quality. Viewing high- definition content and up-converting DVD content may require an HDCP capable DVI or HDMI input on your display device. 1080p capable display required for viewing content in 1080p. Firmware update may be required for some interactive features depending on content, which may also require an always-on broadband internet connection. Some features may require additional bandwidth. To take advantage of web-enabled network content, downloading and installing the latest firmware (ver.2.3 for HD DVD player models HD-XA1, HD-A1, and HD-D1; ver.2.0 for HD DVD player models HD-XA2, HD-A2, HD-A2W, HD-D2, HD-A2C, and HD- A20; and ver.1.0 for HD-A35, HD-A30, and HD-A3) is required. Web-enabled features require an always on broadband connection along with specific movie titles that include this form of content. MP3/WMA audio files not supported. For 24p output, content that was created in 1080p/24 frames/sec is required. Viewing 24p output requires an HD display capable of accepting a 1080p/24Hz signal. Use of CE-Link, which is a feature based on HDMI-CEC, requires an HDMI-CEC compatible display device. Depending on the specifications of your TV, some or all CE-Link functions may not work even if your TV is HDMI-CEC compatible. Use of High Bit Rate Audio requires High Bit Rate Audio compatible AV receiver. Deep Color feature as specified in HDMI 1.3a requires compatible Deep Color capable HD display and/or device. Because HD DVD is a new format that makes use of new technologies, certain disc, digital connection and other compatibility and/or performance issues are possible. This may, in rare cases, include disc freezing while accessing certain disc features or functions, or certain parts of the disc not playing back or operating as fully intended. If you experience such issues, please refer to the FAQ sections of www.toshibahddvd.com or www.tacp.toshiba.com for information on possible work-around solutions or the availability of firmware updates that may resolve your problem, or contact Toshiba Customer Solutions. Some features subject to delayed availability. While Toshiba has made every effort at the time of publication to ensure the accuracy of the information provided herein, product specifications, configurations, system/component/options availability are all subject to change without notice. For additional information on Toshiba HD DVD players, please visit http://www.tacp.toshiba.com/hddvd/</p>

<p>HDMI, the HDMI logo, and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing, LLC.</p>

<p>HD DVD and DVD are trademarks of DVD Format/Logo Licensing Corporation.</p>

<p>Source: Toshiba America Consumer Products, L.L.C.</p>

<p>CONTACT: Nicole Lawler of BRODEUR, +1-617-587-2024, nlawler@brodeur.com,<br />
for Toshiba America Consumer Products, L.L.C.</p>

<p>Web site: http://www.tacp.toshiba.com/<br />
http://www.tacp.toshiba.com/hddvd<br />
http://www.toshibahddvd.com/</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August  6, 2007  5:52 AM</b>
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
			<?=getComments(662)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 662)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/toshiba-to-debut-third-generation-hd-dvd-players-for-high-definition-enthusiasts.php" type="text/javascript" charset="utf-8"></script>
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