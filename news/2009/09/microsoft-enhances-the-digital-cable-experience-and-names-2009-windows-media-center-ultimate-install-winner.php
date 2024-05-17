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
		AND e.entry_id = 3254";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3254 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3254 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3254";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2009/09/microsoft-enhances-the-digital-cable-experience-and-names-2009-windows-media-center-ultimate-install-winner.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3254";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner" />
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
	<title>HDTV Magazine - Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner</title>
	<meta name="keywords" content="media center, windows media, digital cable, cable tuner, tuner cablecard, windows, media, cable, center, digital, microsoft, customers, home, support, new, cablecard, channels, sdv, tuner, cablelabs, experience, mcia, announced, server, install" />
	<meta name="description" content="Today at CEDIA EXPO 2009, Microsoft Corp. discussed key Windows Media Center features for Windows 7 and announced a series of initiatives that enhance the digital cable experience in Windows Media Center. With the addition of native support for additional international broadcast TV standards, including QAM and ATSC, there will now be support for switched digital video (SDV), a new tool that will make it possible for end customers to add a digital cable tuner with CableCARD to their PC, and for existing digital cable tuner with CableCARD customers to enjoy more portability for digital cable TV that is marked as &quot;copy freely&quot; (CF). In addition, Microsoft and the Media Center Integrator Alliance (MCIA) announced..." />
	<meta name="title" content="Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2009/09/microsoft-enhances-the-digital-cable-experience-and-names-2009-windows-media-center-ultimate-install-winner.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Today at CEDIA EXPO 2009, Microsoft Corp. discussed key Windows Media Center features for Windows 7 and announced a series of initiatives that enhance the digital cable experience in Windows Media Center. With the addition of native support for additional international broadcast TV standards, including QAM and ATSC, there will now be support for switched digital video (SDV), a new tool that will make it possible for end customers to add a digital cable tuner with CableCARD to their PC, and for existing digital cable tuner with CableCARD customers to enjoy more portability for digital cable TV that is marked as &quot;copy freely&quot; (CF). In addition, Microsoft and the Media Center Integrator Alliance (MCIA) announced..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3254', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2009/09/microsoft-enhances-the-digital-cable-experience-and-names-2009-windows-media-center-ultimate-install-winner.php">Microsoft Enhances the Digital Cable Experience and Names 2009 Windows Media Center Ultimate Install Winner</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>September  9, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=293&category=Cable, Satellite & Fiber">Cable, Satellite & Fiber</a></b>, <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Microsoft Enhances the Digital Cable Experience</p>

<center><i>Customers get new capabilities, more options, and a better digital cable experience in Windows Media Center.</center></i><br />
<br />

<p><strong>ATLANTA, Sept. 9 /PRNewswire-FirstCall/ -- </strong>Today at CEDIA EXPO 2009, Microsoft Corp. discussed key Windows Media Center features for Windows 7 and announced a series of initiatives that enhance the digital cable experience in Windows Media Center. With the addition of native support for additional international broadcast TV standards, including QAM and ATSC, there will now be support for switched digital video (SDV), a new tool that will make it possible for end customers to add a digital cable tuner with CableCARD to their PC, and for existing digital cable tuner with CableCARD customers to enjoy more portability for digital cable TV that is marked as "copy freely" (CF). In addition, Microsoft and the Media Center Integrator Alliance (MCIA) announced the winner of the 2009 Windows Media Center Ultimate Install Contest, showcasing the many ways Windows Media Center can be used in a whole-home solution.</p>

<p>"We're continuing to work on creating opportunities for partners that will enable great entertainment experiences on the PC," said Craig Eisler, corporate vice president of entertainment client software for the TV, Video & Music Business at Microsoft. "Consumers understand that having access to content via the PC is critical when it comes to entertainment experiences, and with these announcements, we're underscoring our broader commitment to deliver a rich experience with Windows Media Center."</p>

<p><br />
<strong>Switched Digital Video (SDV) Support Added for Windows Media Center</strong></p>

<p>In response to customer requests and cable providers' deployment of SDV, Microsoft now supports SDV in Windows Media Center for Windows 7. In conjunction with a device known as a tuning adapter, supplied by a customer's cable provider, Windows Media Center and a digital cable tuner with CableCARD will be able to tune to SDV channels. Customers can enjoy SDV broadcasts on PCs running Windows Media Center in Windows 7 and a digital cable tuner with CableCARD.</p>

<p><br />
<strong>End Customers Can Now Add Digital Cable Tuners With CableCARD to Their PCs</strong></p>

<p>Microsoft and CableLabs announced that customers will now be able to add digital cable tuners with CableCARD to a Windows 7-based PC with Windows Media Center. A new tool will be provided by Microsoft that assesses the PC's ability to support the solution. This tool will analyze the customer's PC and enable digital cable support if the PC meets requirements, opening digital cable options to Windows Media Center customers across the country. Microsoft also announced that, with Windows 7, it has increased the number of TV tuners that can be connected to the PC from two to four per tuner type, thereby allowing customers to simultaneously record or watch as many as four digital cable TV channels.</p>

<p>"We are excited that digital cable customers will now be able to take advantage of this new opportunity to bring great cable TV programming to the PC," said So Vang, vice president of OpenCable at CableLabs. "We are dedicated to helping customers get the most from their cable service, and this will be a great win for both the customer and the cable operators."</p>

<p><br />
<strong>Digital Cable Customers Can Now Enjoy More TV Portability in Windows Media Center</strong></p>

<p>Microsoft and CableLabs also announced that they worked together to enable digital cable tuner with CableCARD customers to enjoy more portability for digital cable TV that is marked as "copy freely" (CF). Customers will be able to play CF-marked digital cable recordings, such as those from local channels, on other PCs, devices and portable media.</p>

<p><br />
<strong>Windows Media Center Features in Windows 7 Highlighted</strong></p>

<p>Using new Windows 7 features such as Windows Touch, HomeGroup, Remote Media Streaming and PlayTo, sharing recorded TV, videos, music and pictures throughout the home, while on the road and to remote locations has never been easier. There is also support for the AVCHD format. This allows customers to view HD video from many popular HD video cameras.</p>

<p>In addition, support for the international broadcast TV standards that was released with the Windows Media Center TV Pack 2008 will also be included in Windows Media Center in Windows 7. This includes native support for both ATSC and QAM, the ability to remap channels, and support for subchannels.</p>

<p><br />
<strong>New Firmware for ATI TV Wonder Digital Cable Tuners</strong></p>

<p>In conjunction with the Microsoft and CableLabs announcements, Advanced Micro Devices Inc. (AMD) will be providing a new firmware update that is available to all ATI TV Wonder digital cable tuners being used with Windows 7 and Windows Vista. This firmware update will allow existing digital cable tuner with CableCARD customers to enjoy more portability for digital cable TV marked as CF. Customers will be able to play CF-marked digital cable recordings, such as those from local channels, on other PCs, devices, and portable media. In addition, the firmware will contain support for SDV. When installed on a Windows 7-based PC with a digital cable tuner with CableCARD and a tuning adapter from a cable provider, it enables access to switched digital channels in locations where SDV has been deployed.</p>

<p><br />
<strong>2009 Windows Media Center Ultimate Install Contest Winner Announced</strong></p>

<p>Microsoft, in collaboration with the Media Center Integrator Alliance (MCIA), announced the winner of the 2009 Windows Media Center Ultimate Install Contest. The winning installation was submitted by Dustin Anderson, general manager at Vision Audio in Lubbock, Texas, who built a system with Windows Media Center at the core of the entertainment experience in an extensive whole-home installation for a customer in Odessa, Texas. The installation integrates six Windows Media Center-based servers, one Windows Home Server, five dedicated theater-style rooms, 12 media racks, 98 speakers, and 30 zones of distributed audio. The home includes products from key MCIA member companies such as Autonomic Controls Inc., Crestron Electronics Inc. and Niveus Media Inc.</p>

<p>The Windows Media Center Ultimate Install Contest, now in its third year, encourages integrators to show off their talents by presenting their most unique and creative installations that leverage Windows Media Center technologies. Vision Audio's integration of the family's music, movies, videos and pictures, as well as the integration of Windows Media Center and Windows Home Server with the Crestron home automation system, and the large scope of the installation set it apart as the winner for 2009.</p>

<p>"We're thrilled to receive this recognition from Microsoft and the MCIA. The Windows Media Center platform has enabled us to be on the cutting edge of technology, which has provided us with critical business advantages during the economic downturn," Anderson said.</p>

<p>More information on the contest and images from the install can be found online at <a target="_blank" href="http://www.microsoft.com/ultimateinstall/">http://www.microsoft.com/ultimateinstall</a>.</p>

<p>Also on Display at CEDIA EXPO 2009</p>

<p>At the Microsoft booth at CEDIA EXPO 2009, Microsoft will show additional hardware and software installations that enhance the digital cable experience. Demonstrations include these:</p>

<p>  --  The new Zune HD portable media player using the Zune HD AV dock to<br />
      display 720p content on an HDTV. The Zune HD and updated Zune PC<br />
      software will launch on Sept. 15.<br />
  --  A home server powered by Windows Home Server software. The upcoming<br />
      Windows Home Server Power Pack 3, currently in beta testing, will add<br />
      enhancements for Windows Media Center. Power Pack 3 features include<br />
      the option to move recorded TV content to the home server in a variety<br />
      of resolutions, and the ability for users to see statistics about the<br />
      home server through Windows Media Center.</p>

<p>  --  A technology preview of the new Multi-Channel Cable TV Card from Ceton<br />
      Corp., which enables PCs with Windows Media Center to play or record<br />
      multiple live channels of premium HDTV at once, and stream live HD<br />
      channels or recordings to multiple TV sets throughout the home, all<br />
      with a single CableCARD.</p>

<p><br />
<strong>About CableLabs</strong></p>

<p>Founded in 1988 by members of the cable television industry, Cable Television Laboratories is a non-profit research and development consortium that is dedicated to pursuing new cable telecommunications technologies and to helping its cable operator members integrate those advancements into their business objectives. Cable operators from around the world are members. CableLabs maintains web sites at <a target="_blank" href="http://www.cablelabs.com/">www.cablelabs.com</a>; <a target="_blank" href="http://www.packetcable.com/">www.packetcable.com</a>; <a target="_blank" href="http://www.cablemodem.com/">www.cablemodem.com</a>; <a target="_blank" href="http://www.cablenet.org/">www.cablenet.org</a>; <a target="_blank" href="http://www.opencable.com/">www.opencable.com</a>; and <a target="_blank" href="http://www.tru2way.com/">www.tru2way.com</a>.</p>

<p><br />
<strong>About the Media Center Integrator Alliance (MCIA)</strong></p>

<p>The MCIA is an open and independent non-profit consortium formed to advance and administer the support, promotion, and enrichment of the media center ecosystem. Charter members of MCIA include AMD, Crestron, HP, Intel, Life|ware, Microsoft Corp., and Niveus Media.</p>

<p><br />
<strong>About Microsoft</strong></p>

<p>Founded in 1975, Microsoft (NASDAQ:MSFT) is the worldwide leader in software, services and solutions that help people and businesses realize their full potential.</p>

<p>Source: Microsoft Corp.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>September  9, 2009  9:23 PM</b>
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
			<?=getComments(3254)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3254)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/09/microsoft-enhances-the-digital-cable-experience-and-names-2009-windows-media-center-ultimate-install-winner.php" type="text/javascript" charset="utf-8"></script>
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