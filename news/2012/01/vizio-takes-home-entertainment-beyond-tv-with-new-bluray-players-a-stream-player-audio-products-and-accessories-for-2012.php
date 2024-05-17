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
		AND e.entry_id = 4631";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4631 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4631 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4631";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2012/01/vizio-takes-home-entertainment-beyond-tv-with-new-bluray-players-a-stream-player-audio-products-and-accessories-for-2012.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4631";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012" />
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
	<title>HDTV Magazine - VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012</title>
	<meta name="keywords" content="blu ray, stream player, internet apps, high definition, sound bar, sound, player, plus, audio, video, blu, ray, apps, high, skype, products, vht, entertainment, new, any, market, web, internet, stream, experience" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-blu-ray.jpeg&quot; alt=&quot;VIZIO Blu-ray&quot; height=&quot;75&quot; width=&quot;125&quot; style=&quot;float:left; padding:0 5px 5px 0;&quot;&gt;(CES) VIZIO, America's #1 HDTV Company*, today announced its entry into several new product categories and the expansion of key product lines. The new additions to VIZIO's Beyond TV line will provide consumers with better performance and optimized entertainment experiences never seen before.

Key new products include..." />
	<meta name="title" content="VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2012/01/vizio-takes-home-entertainment-beyond-tv-with-new-bluray-players-a-stream-player-audio-products-and-accessories-for-2012.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-blu-ray.jpeg&quot; alt=&quot;VIZIO Blu-ray&quot; height=&quot;75&quot; width=&quot;125&quot; style=&quot;float:left; padding:0 5px 5px 0;&quot;&gt;(CES) VIZIO, America's #1 HDTV Company*, today announced its entry into several new product categories and the expansion of key product lines. The new additions to VIZIO's Beyond TV line will provide consumers with better performance and optimized entertainment experiences never seen before.

Key new products include..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4631', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2012/01/vizio-takes-home-entertainment-beyond-tv-with-new-bluray-players-a-stream-player-audio-products-and-accessories-for-2012.php">VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January 10, 2012</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=273&category=Blu-ray">Blu-ray</a></b>
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
				<p class="prtitle">VIZIO Takes Home Entertainment Beyond TV With New Blu-ray Players, a Stream Player, Audio Products and Accessories for 2012</p>

<center><i><ul><li>-Introductions include an advanced VIZIO Internet Apps Plus&reg; (V.I.A. Plus) enabled 3D Blu-ray player, one that incorporates the latest Google TV experience and DLNA; a V.I.A. Plus powered Stream Player that incorporates the latest Google TV experience and DLNA, and a Skype camera for use with VIZIO Internet-connected TVs</li><li>-New audio products include an HDMI-equipped slim-profile 2.1 sound bar and a five-speaker, high-performance iPod&reg;, iPhone&reg; and iPad&reg; compatible audio dock</li></ul></center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/vizio-blu-ray.jpeg" alt="VIZIO Blu-ray" height="150" width="250" class="keyimg"><strong>IRVINE, Calif. and LAS VEGAS, Jan. 10, 2012 /PRNewswire/ --</strong> (CES) VIZIO, America's #1 HDTV Company*, today announced its entry into several new product categories and the expansion of key product lines. The new additions to VIZIO's Beyond TV line will provide consumers with better performance and optimized entertainment experiences never seen before.</p>

<p>Key new products include the VBR370 3D Blu-ray player, the VAP430 VIZIO Internet Apps Plus (V.I.A. Plus) powered Stream Player, the VHT215 2.1 Sound Bar with wireless subwoofer, the VSD210 High Definition Audio Dock compatible with the iPod, iPhone and iPad, and the XCV100 VIZIO Internet Apps TV Video Camera compatible with Skype.</p>

<p>VIZIO's Beyond TV line has shown strong growth in the last three years. The company now holds the #1 market position in sound bars, with four of its SKUs taking the top four slots in recent rankings from a major consumer review publication. Its recent entries in the tablet, Blu-ray player and cable categories have been well received by consumers and dealers alike.</p>

<p>"In the last two years, VIZIO has proven we can extend our high value/high-performance philosophy to categories beyond TV," says John Schindler, VIZIO VP of Product Management. "Our latest Beyond TV products are not only competitive, they exceed the performance of similar products on the market, and they offer consumers easy access to more entertainment options than ever before."</p>

<p><br />
<strong>3D Blu-ray Players That Deliver Far More Than Just Blu-ray</strong></p>

<p>The VIZIO VBR430 is one of the most advanced 3D Blu-ray players on the market for many reasons. It offers the incomparable entertainment power of the latest Google TV experience through V.I.A. Plus. The player can run thousands of Android apps and includes a full-featured Chrome web browser with flash capability. It also gives consumers the power to search for the content they want to watch, just as they'd search for a video through a web browser. A touchpad universal remote with QWERTY keyboard makes it easy to control the V.I.A. Plus interface and other functions.</p>

<p>The VBR430 is also the first 3D Blu-ray player to feature V.I.A. Plus with DLNA. DLNA, or Digital Living Network Alliance, allows the player to access personal photos, music and videos stored on DLNA-enabled computers, mobile devices, and network drives connected to your home network – and display them on the TV. Built-in Wi-Fi makes network connection easy, and Bluetooth capability provides yet another conduit for streaming media from cell phones and computers.</p>

<p>The VBR430 and VBR370 and will be available in 2012, with specific ship dates and prices TBA. For more information, please visit <a target="_blank" href="http://www.vizio.com/ces/bluray/overview/">www.vizio.com/ces/bluray/overview</a>.</p>

<p><br />
<strong>The Most Advanced Streaming Player on the Market Today</strong></p>

<p>With VIZIO's VAP430 Stream Player, any TV becomes the world's most advanced Smart TV. This stream player incorporates the latest Google TV experience with a rich V.I.A. Plus user interface, so you have access to thousands of Android apps and any web page through its Flash capable Chrome web browser. Consumers can access countless video and audio streaming services, such as Netflix&reg;, YouTube&reg;, Hulu Plus&trade;, Amazon Instant Video&trade;, HBO GO, Wall Street Journal&reg;, Pandora&reg;, iHeartRadio&reg;, Slacker Radio&reg;plus many more including the just announced M-GO&trade; video-on-demand services. (Some services may require subscription fees.) And its seamless multi-tasking capability makes it easy to jump from one experience to another.</p>

<p>With the VAP430's included touchpad universal remote with QWERTY keyboard, consumers can easily navigate through apps, surf the web, and search their streaming services and the web for any content they want. This innovative upgrade also includes HDMI-pass-through, which lets users connect cable or satellite set-top boxes through the VAP430's built-in HDMI ports, for a smooth V.I.A Plus experience that overlays live TV so no one misses a minute of their favorite programs while multi-tasking.</p>

<p>Thanks to DLNA, the VAP430 can access personal photos, music and videos stored on DLNA-enabled computers, mobile devices, and hard drives connected to your home network, then display the images on the TV and play the audio through the TV or any connected sound system..  Built-in WiFi makes connecting to the network a breeze.  Bluetooth capability and USB input provide even more ways to enjoy content from cell phones, computers, and USB drives.</p>

<p>The VAP430 Stream Player will be available in 2012, with specific ship date and pricing TBA. For more information please visit <a target="_blank" href="http://www.vizio.com/streamplayer/">www.vizio.com/streamplayer</a>.</p>

<p><br />
<strong>A Super-Slim 2.1 Sound Bar With Wireless Subwoofer for True Home Theater Sound and Advanced Features</strong></p>

<p>VIZIO created the new VHT215 sound bar to complement the latest slim-profile TVs. At less than 2 inches thick, the VHT215 looks great mounted on a wall under a flat-panel TV, or placed below the TV on a stand or table. With two HDMI inputs plus an HDMI output with ARC (Audio Return Channel), the VHT215 requires just a single cable to connect to a TV set. Dolby Digital decoding allows compatibility with all digital TV sets. Optical digital, coaxial digital and analog inputs connect the VHT215 to multiple entertainment components.</p>

<p>Thanks to meticulous engineering and careful tuning, the VHT215 delivers superior sound quality. Rugged drivers in the sound bar allow a lower subwoofer crossover point (108 Hz), so voices sound full and natural. Most competing sound bars have a crossover point around 200 Hz, which makes voices sound thin. The VHT215's wireless subwoofer delivers the impact and dimension to create a great movie or music experience. SRS&reg; TruSurround&trade; and TruVolume&trade; produce immersive sound while assuring the volume never gets too high or too low.</p>

<p>The VHT215 is available now.</p>

<p><br />
<strong>An Affordable, iPad-Compatible High Definition Audio Dock That Outperforms Docks Three Times its Price</strong></p>

<p>VIZIO's VSD210 high-definition 2.1 audio dock produces big, room-filling sound from iPhone, iPad, and most iPod models. The audio performance of the VSD210 exceeds products at multiples of its price, with separate midrange drivers and tweeters for each channel, a 4-inch subwoofer, and sound carefully tailored through the use of digital signal processing.  A 3.5mm analog input connects to computers and non-Apple mobile devices, and a remote control is included.</p>

<p>The performance of the VSD210 compares with any sound dock at any price. It was tuned specifically to beat some of the best-known docks on the market, and it does so easily—yet at only about one-third the price of some leading models. Its modern, elegant design suits any room in a home or business.</p>

<p>The VSD210 is available now.</p>

<p><br />
<strong>Make Free Skype-to-Skype Video Calls Through VIZIO Internet Apps (V.I.A.)-Equipped VIZIO TVs</strong></p>

<p>VIZIO's new XCV100 Video Camera lets owners of VIZIO TVs with VIZIO Internet Apps (V.I.A.) capability and any Skype certified devices—phones, tablets, TVs—to make Skype-to-Skype video or voice calls and instant message anywhere in the world for FREE—and all from the comfort of the couch. The XCV100 mounts atop the V.I.A. enabled TV and incorporates a high-definition** video camera and four separate microphones with a range of 16 feet. The XCV100 tilts down as far as 10 degrees, to assure that the picture is perfectly framed and every member of the family can get in on the call.</p>

<p>Installation is simple: Just plug the XCV100 into the V.I.A. enabled TV's USB port and launch Skype.  Learn more at <a target="_blank" href="http://www.vizio.com/tvchat/">www.vizio.com/tvchat</a>.</p>

<p>The XCV100 is available now.</p>

<p>* Source: IHS iSuppli Corporation Research Q4 2011 Market Tracker Report of Q4 2010 - Q3 2011.</p>

<p>**Not all HDTV models support High Definition viewing. Please visit <a target="_blank" href="http://www.vizio.com/">www.vizio.com</a> for more information</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc., "Entertainment Freedom For All," headquartered in Irvine, California, is America's HDTV and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead in U.S. TV sales. Since 2007 VIZIO HDTV shipments remain in the TOP ranks in the U.S. and was #1 for the total year in 2009.  VIZIO is committed to bringing feature-rich consumer electronics to market at a value through practical innovation. VIZIO offers a broad range of award winning consumer electronics. VIZIO's products are found at Costco Wholesale, Sam's Club, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Fast Company's 6th Most Innovative CE Company of 2009, and made the lists of Ad Age's Hottest Brands, Good Housekeeping's Best Big-Screens, CNET's Editor's Choice, PC World's Best Buy and OC Metro's 10 Most Trustworthy Brands among many other prestigious honors. For more information, please call 888-VIZIOCE or visit on the web at <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>The V, VIZIO, TruLED, Extreme VIZIO Technology XVT, VIZIO Internet Apps, 480Hz SPS, 240Hz SPS, Thin Line, Smooth Motion, Razor LED, Smart Dimming, Theater 3D, CinemaWide HDTV, Entertainment Freedom For All, names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>SOURCE VIZIO</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January 10, 2012  7:03 PM</b>
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
			<?=getComments(4631)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4631)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2012/01/vizio-takes-home-entertainment-beyond-tv-with-new-bluray-players-a-stream-player-audio-products-and-accessories-for-2012.php" type="text/javascript" charset="utf-8"></script>
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