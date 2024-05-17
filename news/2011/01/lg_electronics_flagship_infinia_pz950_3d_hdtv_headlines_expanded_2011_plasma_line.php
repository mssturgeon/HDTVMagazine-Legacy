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
		AND e.entry_id = 4156";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4156 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4156 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4156";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/lg-electronics-flagship-infinia-pz950-3d-hdtv-headlines-expanded-2011-plasma-line.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4156";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG Electronics\' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG Electronics\' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LG Electronics\' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line" />
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
	<title>HDTV Magazine - LG Electronics' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line</title>
	<meta name="keywords" content="inch class, class size, size inch, inch diagonal, class sizes, inch, class, plasma, electronics, size, series, energy, features, diagonal, –, consumers, hdtv, picture, thx, saving, certification, content, sizes, home, new" />
	<meta name="description" content="LG Electronics announced its expanded family of plasma HDTVs for 2011 today at the International Consumer Electronics Show (Booth #8205), a stunning line-up of seven new plasma series headlined by the Full HD 1080p INFINIA PZ950.

The INFINIA PZ950 – with its added online content options through LG SmartTV with Magic Motion Remote for easy navigation, THX Certified 3D display* and picture-enhancing technologies..." />
	<meta name="title" content="LG Electronics' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LG Electronics' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/lg-electronics-flagship-infinia-pz950-3d-hdtv-headlines-expanded-2011-plasma-line.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LG Electronics announced its expanded family of plasma HDTVs for 2011 today at the International Consumer Electronics Show (Booth #8205), a stunning line-up of seven new plasma series headlined by the Full HD 1080p INFINIA PZ950.

The INFINIA PZ950 – with its added online content options through LG SmartTV with Magic Motion Remote for easy navigation, THX Certified 3D display* and picture-enhancing technologies..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4156', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/lg-electronics-flagship-infinia-pz950-3d-hdtv-headlines-expanded-2011-plasma-line.php">LG Electronics' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  6, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=504&category=Plasma HDTVs">Plasma HDTVs</a></b>
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
				<p class="prtitle">LG Electronics' Flagship INFINIA PZ950 3D HDTV Headlines Expanded 2011 Plasma Line</p>

<center><i>New Plasma Displays Combine Top-of-the-Line Features, Amazing Picture Quality and Stylish Design</center></i><br />
<br />

<p><strong>LAS VEGAS, Jan. 6, 2011 /PRNewswire/ -- </strong>LG Electronics announced its expanded family of plasma HDTVs for 2011 today at the International Consumer Electronics Show (Booth #8205), a stunning line-up of seven new plasma series headlined by the Full HD 1080p INFINIA PZ950.</p>

<p>The INFINIA PZ950 – with its added online content options through LG SmartTV with Magic Motion Remote for easy navigation, THX Certified 3D display* and picture-enhancing technologies – represents the pinnacle among the more than a dozen new 2011 plasma models from LG Electronics.</p>

<p>"LG is dedicated to re-inventing the boundaries of Plasma HDTVs, and the 2011 line represents another step forward in the industry," said Jay Vandenbree, senior vice president, LG Electronics USA. "Including features that provide additional content, like LG SmartTV and 3D capability, add to the great picture quality and stylish designs that consumers have come to expect from an LG plasma HDTV."</p>

<p><br />
<strong>Connect, Surf, Enjoy</strong></p>

<p>Two of LG's new plasma lines, PZ950 and PZ750 series (each available in both 50- and 60-inch class sizes*), come with the new LG SmartTV function. Building on the online content platform the company first introduced in 2008, LG SmartTV provides consumers access to virtually limitless content, thousands of movies, customizable apps, videos and Web browsing, all through an organized, simple-to-use interface*. Making navigation easier is LG's "magic motion" remote control. This gesture-based remote provides a simple and intuitive way to control the TV as well as LG SmartTV apps. In addition, a free app for iPhone and Android-based smart phones will provide an additional way to control the set, including a full QWERTY keyboard.</p>

<p>The PZ550 series (available in 50- and 60-inch class sizes*) includes online media options from content partners including Netflix, CinemaNow, VUDU, Picasa and many more with LG NetCast 1.0. Providing easy options for connecting to the Internet, in addition to the wired Ethernet jack, all internet-capable sets can integrate into a wireless home network by using a USB wireless broadband adaptor (Included with PZ950, sold separately for other models).</p>

<p>All LG's internet-capable plasmas have incorporated the Digital Living Network Alliance (DLNA) technology. DLNA allows consumers to access content stored on other DLNA-certified devices within the home, such as computers, making content options almost limitless.</p>

<p><br />
<strong>Eye-Popping Picture, Eye-Catching Design</strong></p>

<p>Four of LG's new plasma series (PZ950, PZ750, PZ550 and PW350 series) incorporate 3D capability, working with the use of active shutter glasses and an RF emitter built into the television. They also incorporate 2D to 3D conversion, which is user-adjustable so consumers can watch almost anything with a 3D effect. The PZ950 and PZ750 have achieved THX 2D and 3D Display Certification – the industry standard for having the correct gamma, luminance, and color temperature. To earn THX 3D display certification, these models passed more than 400 laboratory tests evaluating left and right eye images for color accuracy, cross-talk, viewing angles and video processing performance. In addition to THX 3D Display Certification, this series had to pass THX certification for their superior picture quality in 2D, which must be achieved before passing THX 3D Display Certification. THX Certification ensures that consumers bring home an uncompromised HD experience with picture quality the way the director intended.</p>

<p>All of the new plasma series include LG's 600Hz Sub-field Driving to ensure that all the action is delivered smoothly with virtually no motion blur. The flagship PZ950 also includes the TruBlack filter for enhanced black levels even in brightly lit conditions. Further, it features LG's striking INFINIA Design, with a depth of less than 2 inches and a bezel measuring just 1.18 inches wide. Real glass is paired with a hair-line aluminum highlight to create a luxurious, virtually borderless appearance, while a transparent lower panel completes the slim even-bezel look. The overall effect is to draw greater attention to the screen, making for a truly immersive viewing experience in both 2D and 3D. Other 2011 models (PZ550, PW350, PV450, and PT350) include LG's TruSlim Frame, which cut the bezel to less than 1-inch thin.</p>

<p><br />
<strong>Energy Savings</strong></p>

<p>Understanding consumers' desire for products that reduce their household energy costs, most of LG's plasma HDTVs have a variety of energy-saving features, such as Intelligent Sensor, to automatically calibrate and optimize brightness, contrast, white balance and color, based on the ambient light in the room, saving on energy output under most circumstances. Additionally, ISFccc calibration options allow consumers to work with a professional to set "day" and "night" levels for optimal viewing and brightness levels. All of LG's 2011 plasma series also qualify for ENERGY STAR&reg; certification.</p>

<p>In total, LG unveiled 12 new plasma HDTV models for consumers, featuring advanced content options, excellent picture quality, wireless technology and diverse screen sizes. Full details on the series are below:</p>

<p><strong>INFINIA PZ950 Series</strong> (50 and 60-inch class size*) – LG's flagship plasma, Full HD 1080p 3D-enabled HDTV features uni-layer design with ultra-slim bezel, LG SmartTV with Magic Motion Remote, THX 3D and 2D Display Certification. Also includes TruBlack Filter, 600Hz max Sub-field Driving, DLNA, Wi-Fi capability (adaptor included), and Smart Energy Saving features.</p>

<p><strong>INFINIA PZ750 Series</strong> (50- and 60-inch class sizes*) – Full HD 1080p 3D-enabled HDTV features uni-layer design with ultra-slim bezel, LG SmartTV with Magic Motion Remote, THX 3D and 2D Display Certification. Also includes 600Hz max Sub-field Driving, DLNA, Wi-Fi capability (adaptor required, sold separately), and Smart Energy Saving features.</p>

<p><strong>PZ550 Series</strong> (50- and 60-inch class sizes*) – Full HD 1080p 3D-enabled HDTV features LG's TruSlim Frame, NetCast&trade; Entertainment Access, DLNA, Wi-Fi capability (adaptor required, sold separately), 600Hz max Sub-field Driving and Smart Energy Saving features.</p>

<p><strong>PW350 Series</strong> (42- and 50-inch class sizes*) – Provides mid- to large-size 3D-enabled HDTV with LG's TruSlim Frame, 600Hz Sub-field Driving and Smart Energy Saving features.</p>

<p><strong>PV450 Series</strong> (50- and 60-inch class sizes*) – Provides consumers with a Full HD 1080p experience with LG's TruSlim Frame, 600Hz max Sub-field Driving and Smart Energy Saving features.</p>

<p><strong>PT350 Series</strong> (42- and 50-inch class sizes*) – Provides mid- to large-size HDTV with LG's TruSlim Frame, 600Hz Sub-field Driving and Smart Energy Saving features.</p>

<p>With class sizes ranging from 42- to 60-inches, LG's plasma HDTV models are all built with LG's four core technologies:</p>

<ul type="disc"><li><b>Picture Wizard</b>: Provides consumers with an easy-to-use seven-step calibration process that allows them to change picture settings without hiring an expert.</li><li><b>Intelligent Sensor</b>: Automatically calibrates and optimizes brightness, contrast, white balance and color, based on the brightness and color temperature of lighting in the room – thereby saving on energy output in most circumstances.</li><li><b>Clear Voice II</b>: An enhancement to Clear Voice, this feature customizes volume settings by 12 distinct voice zoom levels, helping ensure consumers don't miss a single line of dialogue during action sequences.</li><li><b>AV Mode II</b>: Includes three AV modes preset to optimize picture and sound settings based on Cinema, Sports or Game content, which can be easily set with the remote control.</li></ul>

<p>For more information and product images, please visit LG's online press kit at <a target="_blank" href="http://www.lgnewsroom.com/CES2011/">www.lgnewsroom.com/CES2011</a>.</p>

<p>*PZ950 60-inch class size/60-inch diagonal<br />
*PZ950 50-inch class size/50-inch diagonal	<br />
*PZ750 60-inch class size/60-inch diagonal<br />
*PZ550 50-inch class size/50-inch diagonal<br />
*PZ550 60-inch class size/60-inch diagonal<br />
*PZ550 50-inch class size/50-inch diagonal<br />
*PW350 50-inch class size/50-inch diagonal<br />
*PW350 42-inch class size/42-inch diagonal<br />
*PV450 60-inch class size/60-inch diagonal<br />
*PV450 50-inch class size/50-inch diagonal<br />
*PT350 50-inch class size/50-inch diagonal<br />
*PT350 42-inch class size/42-inch diagonal<br />
	</p>

<p>* Design, features and specifications subject to change without notice.</p>

<p>*Internet connection &amp; subscriptions required and sold separately. The Magic Motion Remote does not come equipped with all LG SmartTV enabled TVs and separate purchase maybe required.</p>

<p>*For a small percentage of the population, the viewing of stereoscopic 3D video may cause discomfort such as dizziness or nausea. If you experience any of these symptoms, discontinue using the 3D functionality and contact your health care provider.  3D glasses required and sold separately.<br />
	<br />
	<br />
<strong>About LG Electronics, Inc.</strong></p>

<p>LG Electronics, Inc. (KSE: 066570.KS) is a global leader and technology innovator in consumer electronics, mobile communications and home appliances, employing more than 80,000 people working in over 115 operations around the world. With 2009 global sales of 55.5 trillion Korean won (USD 43.4 billion), LG comprises four business units – Home Entertainment, Mobile Communications, Home Appliance, and Air Conditioning &amp; Energy Solutions. LG is one of the world's leading producers of flat panel TVs, audio and video products, mobile handsets, air conditioners and washing machines. LG has signed a long-term agreement to become both a Global Partner and a Technology Partner of Formula 1&trade;. As part of this top-level association, LG acquires exclusive designations and marketing rights as the official consumer electronics, mobile phone and data processor of this global sporting event. For more information, please visit <a target="_blank" href="http://www.lg.com/">www.lg.com</a>.</p>

<p><br />
<strong>About LG Electronics USA</strong></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a global force and technology leader in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a range of stylish and innovative home entertainment products, mobile phones, home appliances, commercial displays, air conditioning systems and solar energy solutions, all under LG's "Life's Good" marketing theme. For more information, please visit <a target="_blank" href="http://www.lg.com/">www.lg.com</a>.</p>

<p>SOURCE LG Electronics USA, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2011  4:27 PM</b>
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
			<?=getComments(4156)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4156)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/lg-electronics-flagship-infinia-pz950-3d-hdtv-headlines-expanded-2011-plasma-line.php" type="text/javascript" charset="utf-8"></script>
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