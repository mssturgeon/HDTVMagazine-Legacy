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
		AND e.entry_id = 3473";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3473 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3473 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3473";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2010/01/netgear-expands-family-of-awardwinning-digital-media-players-with-introduction-of-digital-entertainer-express.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3473";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express" />
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
	<title>HDTV Magazine - NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express</title>
	<meta name="keywords" content="digital entertainer, entertainer express, digital media, netgear inc, home network, netgear, digital, entertainer, express, internet, media, products, network, home, content, eva, consumers, market, connected, devices, product, usb, video, new, inc" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/2010-01-06_netgear_digital_entertainer_express.jpg&quot; alt=&quot;NETGEAR Digital Entertainer Express&quot; height=&quot;144&quot; width=&quot;192&quot; class=&quot;keyimg&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;NETGEAR® today announced the worldwide launch of the Digital Entertainer Express (EVA9100), a powerful and flexible digital media player that enables consumers to easily enjoy and seamlessly stream personal digital media collections and Internet content over home networks to high-definition televisions. Providing all of the playback performance and video reliability of the Digital Entertainer Elite (EVA9150), the Digital Entertainer Express is an ideal solution for the serious media enthusiast. It incorporates..." />
	<meta name="title" content="NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2010/01/netgear-expands-family-of-awardwinning-digital-media-players-with-introduction-of-digital-entertainer-express.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/2010-01-06_netgear_digital_entertainer_express.jpg&quot; alt=&quot;NETGEAR Digital Entertainer Express&quot; height=&quot;144&quot; width=&quot;192&quot; class=&quot;keyimg&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;NETGEAR® today announced the worldwide launch of the Digital Entertainer Express (EVA9100), a powerful and flexible digital media player that enables consumers to easily enjoy and seamlessly stream personal digital media collections and Internet content over home networks to high-definition televisions. Providing all of the playback performance and video reliability of the Digital Entertainer Elite (EVA9150), the Digital Entertainer Express is an ideal solution for the serious media enthusiast. It incorporates..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3473', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/netgear-expands-family-of-awardwinning-digital-media-players-with-introduction-of-digital-entertainer-express.php">NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  6, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>
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
				<p class="prtitle">NETGEAR Expands Family of Award-Winning Digital Media Players With Introduction of Digital Entertainer Express</p>

<center><i>Advanced Digital Media Player Optimized for full 1080p Playback of Personal Media Collections and Internet Content; Powerful Media Scanning and Search Feature Enables Easy Access to Digital, Internet and RSS Videos, Photos and MP3s on HDTVs</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.com/news/images/2010-01-06_netgear_digital_entertainer_express.jpg" alt="NETGEAR Digital Entertainer Express" height="104" width="192" class="keyimg"><strong>LAS VEGAS, Jan. 6 /PRNewswire-FirstCall/ -- </strong>NETGEAR®, Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, today announced the worldwide launch of the Digital Entertainer Express (EVA9100), a powerful and flexible digital media player that enables consumers to easily enjoy and seamlessly stream personal digital media collections and Internet content over home networks to high-definition televisions. Providing all of the playback performance and video reliability of the Digital Entertainer Elite (EVA9150), the Digital Entertainer Express is an ideal solution for the serious media enthusiast. It incorporates the latest video and audio technologies to deliver an unparalleled home theater entertainment experience.</p>

<p>The Digital Entertainer Express adds to the NETGEAR family of Internet-connected set-top boxes, which also includes the Digital Entertainer Elite (EVA9150) and Digital Entertainer Live (EVA2000). NETGEAR will highlight its family of Digital Entertainers along with its home media storage server, Stora, at two press events today in conjunction with the opening of the Consumer Electronics Show (CES) in Las Vegas. See today's press release, "NETGEAR Introduces New Solutions at Consumer Electronics Show To Enable Any Media on Any Screen, Anywhere at Anytime" at http://<a target="_blank" href="http://www.netgear.com/About/PressReleases/en-US/2010/20100105a.aspx/">www.netgear.com/About/PressReleases/en-US/2010/20100105a.aspx</a>.</p>

<p>"Consumers' digital media collections are growing every year. And, more and more, they have digital content stored on their computers, USB hard drives, network storage devices, iPods®, digital cameras, etc.," said Lionel Paris, product line manager for NETGEAR entertainment products. "The Digital Entertainer Express scans all the content connected to the player either directly via USB port or over the home network so that it is easily accessible for immediate playback -- all with the latest video and audio decoding. When combined with NETGEAR Stora(TM) and one of our routers, the Digital Entertainer Express completes the ultimate connected home entertainment solution for our customers."</p>

<p>The Digital Entertainer Express' unique technology enables consumers to seamlessly stream M2TS via pre-buffering and play Blu-ray(TM) quality digital video up to 1080p. They can also play MP3, multichannel WAV and FLAC files and high-resolution digital photos from PCs, Macs® or Network Attached Storage (NAS) devices, such as NETGEAR Stora. Consumers can also enjoy Internet content, such as Internet radio (over 250 stations), Flickr(TM), RSS feeds and videos from popular websites. Furthermore, with an included free trial and subsequent special discount of PlayOn(TM) software, consumers can view hit TV shows and movies on their TVs from a wide variety of Internet sources, such as Hulu(TM), Netflix®, Amazon Video On Demand, BBC iPlayer and CBS(TM). Additionally, the Digital Entertainer Express supports an extensive selection of digital media file formats and codecs. For a full list, visit http://<a target="_blank" href="http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA9100.aspx/">www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA9100.aspx</a> .</p>

<p>The Digital Entertainer Express incorporates two USB ports for instant access to content on USB flash drives, digital cameras, iPods or other USB storage devices. It can also search and index files directly on the device, enabling users to navigate content across multiple networked PCs and devices at the same time. In fact, the Digital Entertainer Express automatically finds all digital media files on the home network and organizes them into an easily accessible library.</p>

<p>As one of the most flexible digital media players on the market, the Digital Entertainer Express can easily be connected to the Internet and home network in a variety of ways. Its integrated network port makes an Ethernet wired connection extremely simple. However, if consumers do not have an Ethernet connection available near their TV, they can use the optional Wireless USB Adapter (EVAW111) that connects the Digital Entertainer Express to the Internet and the home network via Wi-Fi®. Alternatively, they can also use existing electrical power outlets and a powerline device to connect the Digital Entertainer Express to the Internet and the home network, such as the NETGEAR Home Theater Internet Connection Kit (XAVB1004),or either of the new powerline devices announced by NETGEAR at CES today, including the new Powerline 200 AV Adapter Kit (XAVB2001) and Powerline 200 AV+ Adapter Kit (XAVB2501) with a filtered "pass-through" power socket (http://<a target="_blank" href="http://www.netgear.com/Products/PowerlineNetworking/PowerlineEthernetAdapters.aspx/">www.netgear.com/Products/PowerlineNetworking/PowerlineEthernetAdapters.aspx</a>).</p>

<p>Like many NETGEAR consumer devices, the Digital Entertainer Express features a simple "push button" way to connect to wireless routers called Push 'N' Connect. When it is used with the Wireless USB Adapter, consumers can easily and securely connect the Digital Entertainer Express to wireless networks without having to remember or input a password. The NETGEAR Digital Entertainer Express also includes environmentally friendly features, such as an energy-efficient power supply and automatic power-saving mode, which consume as little as .01 watts.</p>

<p>With two or more NETGEAR Digital Entertainer Express units, the "Follow Me" feature lets consumers pause a video in one room and resume it in another.</p>

<p>"Exceptional growth in the availability of high quality, long tail content online is driving the growth for new media players and connected set-top boxes," said Jayant Dasari, broadband and television infrastructure and services research analyst at Parks Associates. "However, since the way consumers like to acquire and enjoy their media collections varies from consumer to consumer, it's important for vendors to offer new options that bring online content directly to HDTVs. By increasing its family of Internet-connected set-top boxes to include a feature-rich yet affordable solution, NETGEAR is poised to increase its market share in the segment and become a primary player in the Internet-connected set-top box market."</p>

<p>Backed by full 24/7 technical support, the NETGEAR Digital Entertainer Express (EVA9100) is now available worldwide through leading retailers, e-commerce sites and value-added resellers. The Digital Entertainer Express (EVA9100) has an MSRP in the U.S. of $229, a lower entry price than its sister product, the Digital Entertainer Elite (EVA9150), to reflect its streamlined capabilities and features. Photos and other product information can be found at http://<a target="_blank" href="http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA9100.aspx/">www.netgear.com/Products/Entertainment/DigitalMediaPlayers/EVA9100.aspx</a> .</p>

<p><br />
<strong>About NETGEAR, Inc.</strong></p>

<p>NETGEAR (NASDAQGM: NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of Small- to Medium-sized Businesses (SMBs) and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in over 27,000 retail locations around the globe, and via more than 37,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR® partner. More information is available at http://<a target="_blank" href="http://www.netgear.com/">www.netgear.com</a>/ or by calling (408) 907-8000. Connect with NETGEAR at http://<a target="_blank" href="http://twitter.com/NETGEAR/">twitter.com/NETGEAR</a> and http://<a target="_blank" href="http://www.facebook.com/netgear/">www.facebook.com/netgear</a>.</p>

<p>©2010 NETGEAR, Inc. NETGEAR, the NETGEAR logo and NETGEAR Stora are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Blu-ray is a trademark of the Blu-ray Disk Alliance. Wi-Fi is a trademark of the Wi-Fi Alliance. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>PlayOn service will be free for a trial period and thereafter offered with a special discount with Digital Entertainer Express purchase. The term of the trial period may be found at the Digital Entertainer Express product site located within <a target="_blank" href="http://www.netgear.com/playon/">www.netgear.com/playon</a>. Support of online sites subject to PlayOn terms and conditions.</p>

<p>Hulu and Netflix are only available in the United States. Netflix support requires an existing subscription to the Netflix service.</p>

<p>iPod Touch is not supported.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning NETGEAR's business and the expected performance characteristics, specifications, reliability, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 36 through 50, in the Company's quarterly report on Form 10-Q for the fiscal third quarter ended September 27, 2009, filed with the Securities and Exchange Commission on November 6, 2009. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>

<p>Source: NETGEAR, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2010  9:20 AM</b>
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
			<?=getComments(3473)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3473)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/netgear-expands-family-of-awardwinning-digital-media-players-with-introduction-of-digital-entertainer-express.php" type="text/javascript" charset="utf-8"></script>
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