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
		AND e.entry_id = 404";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 404 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 404 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 404";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/07/hdmi-a-digital-interface-solution.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 404";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDMI - A Digital Interface Solution" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDMI - A Digital Interface Solution" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDMI - A Digital Interface Solution" />
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
	<title>HDTV Magazine - HDMI - A Digital Interface Solution</title>
	<meta name="keywords" content="silicon image, content protection, digital connectivity, hdmi hdmi, component analog, hdmi, digital, could, audio, dvi, version, video, content, color, bandwidth, hdcp, image, support, standard, used, uncompressed, gbps, hdtv, bit, chip" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/images/hdmi_200.gif&quot; alt=&quot;HDMI&quot; align=&quot;right&quot;&gt;Ever wonder what HDMI specs came along with which versions? Or why HDMI came along at all when there are so many connection types already from which to choose? Get all the details in this article, the first of a 10-part series on HDMI ... and which standards &amp; devices you should have in your home theater." />
	<meta name="title" content="HDMI - A Digital Interface Solution" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDMI - A Digital Interface Solution" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/07/hdmi-a-digital-interface-solution.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/images/hdmi_200.gif&quot; alt=&quot;HDMI&quot; align=&quot;right&quot;&gt;Ever wonder what HDMI specs came along with which versions? Or why HDMI came along at all when there are so many connection types already from which to choose? Get all the details in this article, the first of a 10-part series on HDMI ... and which standards &amp; devices you should have in your home theater." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=404', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/07/hdmi-a-digital-interface-solution.php">HDMI - A Digital Interface Solution</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>July 25, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p><b>HDMI Part 1 - A Digital Interface Solution</b></p>

<p><img src="/images/hdmi_200.gif" alt="HDMI" align="left">There is a lot to talk about HDMI for one article, so I will cover the subject in 10 articles each addressing a different area of HDMI. This first article is about specs and versions. A special request made by Shane Sturgeon, our Magazine Chief Technologist & Co-Publisher, gave me the idea of covering other areas of HDMI not well covered by the press that often prompt Magazine readers to ask for help on the Tips list and the Forum.</p>

<p><br />
<h2>HDMI, What is it for you?</h2><br />
Many people talk about HDMI as a cable, or as a spec, or as a chip, as a simplification of digital connectivity, as the end of HD content protected viewing for 10 million early adopters of HDTVs with only component analog connections, as a de-facto standard adopted by hundreds of manufacturers, as a connection that sometimes causes more problems than it resolves, etc. Well, it is all of the above; and depending on a person's agenda, he/she might be very unyielding about some of that list and entirely ignoring the others.</p>

<p><br />
<h2>HDMI, How was it created?</h2><br />
<img src="/images/silicon-image.gif" alt="Silicon Image" align="right">On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI (see below). The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba.</p>

<p>The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals, all on a single cable (15 mm, 19 pin), while using less than half the available bandwidth of HDMI. HDMI was created with the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter, although that is for the video part only since DVI does not handle audio.  With the newest version of HDMI announced in June of 2006, HDMI has doubled its bandwidth capacity to 10.2 Gbps.</p>

<p>HDMI is quickly replacing DVI and is being implemented already on many products, and is becoming the de-facto standard for transporting uncompressed signals over a cable.</p>

<p><br />
<h2>HDCP (High-bandwidth Digital Content Protection)</h2><br />
This article is not intended to cover the details of HDCP.</p>

<p>The HDCP 1.0 specification was developed by Intel with contributions from Silicon Image in February 2000 to protect DVI outputs from being copied by providing a secure link between a video source and a display device.</p>

<p>HDCP offers authentication, encryption, and renewability. The Motion Picture Association of America (MPAA) endorsed HDCP as the standard for the secure transmission of HD signals over DVI, and is used on HDMI as well.</p>

<p><br />
<h2>A Quick Summary of the Ancestor (DVI)</h2><br />
The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device. It is a connection with enough bandwidth for <u>uncompressed</u> HD video signals.</p>

<p>The 1.0 DVI specification is a point-to-point solution that supports video content but not audio, HDMI does. DVI uses the Transition-Minimized Differential Signaling (TMDS) protocol developed by Silicon Image. PanelLink is the Silicon Image's proprietary implementation of TMDS.</p>

<p>More background and specifications can be found on a Digital Connectivity Tutorial I wrote for this Magazine at:</p>

<p><a href="/articles/2006/04/digital-connectivity-a-tutorial.php">http://www.hdtvmagazine.com/articles/2006/04/digital-connectivity-a-tutorial.php</a></p>

<p>This tutorial is also included as a separate section on every Yearly HDTV Technology Report, also found on this Magazine at:</p>

<p><a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">http://www.hdtvmagazine.com/reports/hdtv-technology-review.php</a></p>

<p><br />
<h2>Do we need HDMI?</h2><br />
Maybe our need for simplified cabling is not as much as the motivation of the Motion Pictures Association of America (MPAA) to protect their content, but today's digital world is increasingly in demand for more efficient and secured ways of accessing, distributing, and managing digital content any time any where; and HDMI has become the connection of choice by the HDTV industry for our living rooms.</p>

<p>Component analog and Firewire connections have their place as well.</p>

<p>Component analog was used as the only HD video connection on the first 5 years of HDTV adoption (1998-2003), the problem is that it is unprotected, and for that reason content providers preferred DVI or HDMI as a more secured method to transport an HD signal from a source equipment to a display device (TV, projector, panel).</p>

<p>IEEE1394 Firewire, also covered in the Digital Connectivity Tutorial, is being used for transporting HD <u>compressed</u> signals in digital form for a network or for recording devices, such as D-VHS and external DVRs. Firewire was implemented using a form of content protection called DTCP (also called 5c, for the five companies that found the standard).</p>

<p>One could efficiently record a compressed HD video signal with a bandwidth of 19.4 Mbps transported over Firewire, but it would not be practical to try to record its uncompressed form with approximately 2.2 Gbps of bandwidth, and even if someone wants to try that, DVI or HDMI with HDCP content protection would not allow it.</p>

<p>How could it take 2.2Gbps? The 1080i HD format has 1125 total lines of 2200 pixels x frame (active image 1080x1920), there are 30 frames per second on 1080i, requiring 74.25 MHz/pixels (1125 x 2200 x 30fps). Each pixel contains data for RGB and is implemented by DVI/HDMI with 30 bits (8 per each color plus another 6 for encoding). A 1080i HD 74.25 MHz/pixel signal would require 2.2 Gbps speed rate. Try to transport a 1080p/60fps signal and it could double up that bandwidth requirement.</p>

<p>Many modern HDTVs have the 3 types of connections for backward compatibility and for the different purposes they support, but there are still many earlier generation HDTV sets on the consumer hands (about 10 million) that could be resold as used equipment that only has component analog connections, or could still be used on other rooms of the house.</p>

<p>Beware; it could happen that a protected program running over such unprotected analog connection would not be able to be viewed as HD, even when you pay for the rightful viewing of the content (as PPV, VOD, or premium channel). I cover this subject in depth on this article:</p>

<p><a href="/articles/2006/02/analysis-of-dtv-content-protection-rulings-and-agreements.php">http://www.hdtvmagazine.com/articles/2006/02/analysis-of-dtv-content-protection-rulings-and-agreements.php</a></p>

<p>So hello HDMI, the path seems inevitable, for now.</p>

<p>HDMI has been able to transport the maximum HD quality of 1080p since day one in an uncompressed manner, the digital storage capacity required by a 2hr movie at 1080p quality is by itself overwhelming even to today's storage dreamers, and with the added HDCP content protection protocol (courtesy of Intel) HDMI/HDCP is considered very secured.</p>

<p>However, nothing could be absolutely secured anymore in the world, it is a matter of time and human will, many protection methods might be broken eventually. With the rapid advances of high capacity storage facilities, storing uncompressed HD video could one day be as cost effective as storing a CD is today, and some day a teenager in a corner of the world might claim that even HDCP could be circumvented.</p>

<p>He would then write the instructions of how to do it on the bottom of his skateboard, or publish it on the web as it happened already with DVD and other cases. Morals and human intelligence when used for a negative purpose are always in a constant clash to live in harmony, and HDMI/HDCP could be challenged as well.</p>

<p><br />
<h2>HDMI version 1.3, the Excuses</h2><br />
People are talking about version 1.2 and version 1.3 and why they prefer to wait to get blah-blah features. Version 1.3 has been officially out for a few weeks already, the availability of chips and audio/video equipment using those chips is another story, it could take months, some say equipment could be using 1.3 chips by Christmas, Sony's PS3 expected by November claims it would have it.</p>

<p>I recently received an invitation from Leslie Chard, President of HDMI Licensing LLC, upon their introduction of version 1.3 in June of this year. Leslie had the courtesy to give me a private presentation before the official release to the press; the information below was taken from the presentation.</p>

<p>I also used the opportunity to exchange ideas about several subjects regarding HDMI, including some issues people are having when using HDMI, which took away some of the glowing image of HDMI, some with merit some without.</p>

<p>There are some rumors running about version 1.3 as the only one linked to 1080p, and that earlier versions would not be 1080p capable. One should not condone a manufacturer that installed a non-1080p HDMI chip regardless of the version into a $39 DVD player that just needs to output 480i/p, why? It does not have any use for the 1080p capability.</p>

<p>However, what is not right to some 1080p interested consumers is that equipment that claims 1080p handling capability be suited with a non-1080p HDMI chip that bottlenecks such ability.</p>

<p>In some cases, the TV might actually have the 1080p capable HDMI chip but might not have the proper TV design to internally handle 1080p between the chip and the final display of the image. Many first generation 1080p HDTVs recently introduced do not accept 1080p due to these reasons; cost decisions, market choices, etc. not the HDMI spec, any version.</p>

<p>It is unfair to HDMI that many manufacturers were blaming the unavailability of the HDMI 1.3 spec for their inability to accept 1080p on their 1080p TV sets, when actually even the version 1.0 spec was capable to handle such resolution, obviously the proper chip was not installed.</p>

<p>A similar situation could be mentioned for the incorrect claims that without HDMI 1.3 a new Hi-def DVD player (of either format) could not output new multichannel lossless audio formats (Dolby True-HD, DTS-HD, etc) to an A/V receiver.</p>

<p>Actually it could, the player converts the new audio format as LPCM and send it to the A/V receiver that way even thru a non-1.3 HDMI connector. For that to happen, the player must have the proper audio decoder for the lossless audio format read from the disc. Version 1.3 would permit the encoded audio to be streamed out as is read from the disc, for an external decoder to do the job (future A/V Receivers); there are some pros and cons on doing this. This subject is covered in depth in the following article:</p>

<p><a href="/articles/2006/04/multichannel-audio-for-hd.php">http://www.hdtvmagazine.com/articles/2006/04/multichannel-audio-for-hd.php</a></p>

<p><br />
<h2>The Versions</h2><br />
Let us start with some basic bullets about the HDMI specs on each version:</p>

<p><b>HDMI 1.0</b> (Dec 2002)<br />
-Max Video Performance<br />
1080p @ 60Hz refresh rate, or UXGA (PC format)<br />
24 bit RGB/36 bit YCrCb color depth</p>

<p>-Max Audio Performance<br />
8 channels uncompressed digital audio @ 192kHz, 24 bits per sample<br />
Support for all existing Dolby &amp; DTS compressed formats</p>

<p><b>Time Line:</b><table class="bare" cellspacing="0"><tr><td class="grid">December 2002</td><td class="grid">June 2004</td><td class="grid">August 2005</td><td class="grid">December 2005</td></tr><tr><td class="grid"><b>HDMI 1.0</b></td><td class="grid"><b>HDMI 1.1</b></td><td class="grid"><b>HDMI 1.2</b></td><td class="grid"><b>HDMI 1.2a</b></td></tr><tr><td class="grid">Initial specification</td><td class="grid">Added support for DVD-Audio</td><td class="grid">Added support for SACD</td><td class="grid">CEC Functionality fully specified</td></tr><tr><td class="grid">&nbsp;</td><td class="grid">Improvements to compatibility testing</td><td class="grid">Permits use of RGB color space for monitor applications</td><td class="grid">Testing required for specific cable lengths</td></tr><tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">Supports low-voltage (AC-coupled sources) in PCs</td><td class="grid">Certified Connector List -required to pass ATC testing</td></tr></table></p>

<p><b>1.3 Next Gen HDMI Performance</b><br />
-Maximum bandwidth increased from 165 MHz (4.95Gbps) to 340 MHz (10.2Gbps)</p>

<p>-Technical foundation established for future bandwidth increases</p>

<p>-Support for increased refresh rate and next generation displays<br />
e.g. 1080p @ 60Hz with 36 bit RGB, or 1080p @ 90Hz refresh rate WQXGA displays (1440p)</p>

<p>-Added support for Deep Color for increased color bit-depth 30-, 36-, and 48-bit RGB/YCbCr</p>

<p>-Current 24 bit color enables ~17 million colors. Deep color enables billions of colors. Helps eliminate on-screen effects such as color banding</p>

<p>-Added support for next generation "xvYCC" color standard, will allow the display of any viewable color (1.8X as many colors as existing standard)</p>

<p>-Version 1.3 ensures highest possible signal resolutions from next generation video sources: Blu-Ray and HD-DVD, PS3, HDMI-equipped PCs</p>

<p>-Maximum Audio Performance<br />
Version 1.3 adds support for future Dolby TrueHD &amp; DTS-HD Master audio formats (Blu-ray/HD-DVD). Lossless compression formats bring a similar digital surround experience as original theater movies.</p>

<p>-Automatic Lip Sync timing compensation to enable automatic correction for most common audio/video sync issues</p>

<p>-Mini connector for camcorders, digital-still cameras, etc.</p>

<p><br />
The latest HDMI specification can be downloaded at no cost by visiting <a href="/cgi-bin/ntlinktrack.cgi?http://www.hdmi.org/">www.hdmi.org</a></p>

<p><br />
Stay tuned for Part 2, coming soon to a HDTV theater near you, the HDTV Magazine.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>July 25, 2006  9:57 PM</b>
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
			<?=getComments(404)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 404)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/07/hdmi-a-digital-interface-solution.php" type="text/javascript" charset="utf-8"></script>
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