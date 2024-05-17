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

	# Get author information
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4952 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4952
		AND a.topic_id = t.topic_id";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/12/living-with-4k-part-5-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4952";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
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
	<title>HDTV Magazine - Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</title>
	<meta name="keywords" content="naming conventions, ultra hdtv, aspect ratio, hdtv standard, lower layer, cea, hdtv, resolution, naming, level, ultra, ebu, confusion, lower, standard, used, consumer, conventions, dtv, part, defined, term, itu, uhd, layer" />
	<meta name="description" content="Part 3 of this series addressed the overall issues of naming conventions defined by the various organizations (DCI, ITU, EBU and CEA) regarding 4K and Ultra-HDTV. Part 4 covered the specifics of DCI and ITU naming conventions and standards.

This part 5 covers the specifics of EBU’s (European Broadcasting Union) and CEA’s (Consumer Electronics Association) naming conventions and standards, wrap the subject, and provide an historic perspective of similar naming conventions decisions taken by the CEA in the past.


Ultra-HDTV as defined by the EBU (European Broadcasting Union)..." />
	<meta name="title" content="Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/12/living-with-4k-part-5-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Part 3 of this series addressed the overall issues of naming conventions defined by the various organizations (DCI, ITU, EBU and CEA) regarding 4K and Ultra-HDTV. Part 4 covered the specifics of DCI and ITU naming conventions and standards.

This part 5 covers the specifics of EBU’s (European Broadcasting Union) and CEA’s (Consumer Electronics Association) naming conventions and standards, wrap the subject, and provide an historic perspective of similar naming conventions decisions taken by the CEA in the past.


Ultra-HDTV as defined by the EBU (European Broadcasting Union)..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4952', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/12/living-with-4k-part-5-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php">Living with 4K (Part 5) - Which 4K ... DCI 4K, Ultra-HDTV, Ultra-HD, Quad-Full-HD?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>December  5, 2012</b>
							</td><td id="article_category">
								Categories: 
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
				<p>Part 3 of this series addressed the overall issues of naming conventions defined by the various organizations (DCI, ITU, EBU and CEA) regarding 4K and Ultra-HDTV. Part 4 covered the specifics of DCI and ITU naming conventions and standards. <p>This part 5 covers the specifics of EBU’s (European Broadcasting Union) and CEA’s (Consumer Electronics Association) naming conventions and standards, wrap the subject, and provide an historic perspective of similar naming conventions decisions taken by the CEA in the past. <p><b><u></u></b> <p><b>Ultra-HDTV as defined by the EBU (European Broadcasting Union):</b> <p><a href="http://www.youtube.com/watch?v=LAVTX4S7vCw&amp;feature=youtu.be">EBU defines</a> Ultra-HDTV with two levels, the 3840x2160 resolution as 4K level (UHD-1 lower layer), and the 7680x4320 resolution as 8K level (UHD-2 upper layer). <p><a href="http://tech.ebu.ch/news/hoffmann-on-a-uhd-future-26oct12">The lower layer </a>UHD-1 (in EBU terms) coincides with the now broadly called Ultra-HD by the CEA (Consumer Electronics Association, more below). The upper layer UHD-2 (in EBU terms) is called Super Hi-Vision by NHK, the Japanese broadcaster. <p><a href="http://tech.ebu.ch/webdav/site/tech/shared/factsheets/ebu_fs_beyond-hd_web.pdf">Per EBU document </a>the following statement illustrates the naming confusion (page 2): <p>"<i>For the specific case of UHDTV Level 1, there are several flavours with small differences, leading to some confusion over nomenclature. For example, we have the actual ITU UHDTV Level 1, with 3840x2160 pixels; the Digital Cinema 4k format, with 4096x2160 pixels; and then several undefined terms such as ‘Quad HD’, often used for marketing purposes. The industry needs to agree on common terminology to avoid confusion and ensure interoperability."</i> <p><i></i> <p><b>Ultra HD defined by the CEA (October 2012):</b> <p><a href="http://www.twice.com/articletype/news/ultra-hd-now-4k&rsquo;s-official-ce-industry-name/103664">The specification</a> was apparently created to clearly identify newer 3840x2160 sets that were introduced as 4K when they were actually not in terms of horizontal pixel count (3840 rather than 4096) and of aspect ratio (16:9 rather than 17:9 of the 4K DCI standard). <p>The CEA announcement provided no details or minimum specifications for bit depth or frame rates for the U-HD display or signal acceptance (other than its resolution). <p>Likewise, no comments were provided regarding manufacturer agreements to upgrade U-HD sets when the HDMI 1.4 specification is revised to handle 4K 60fps by the end of 2012, as expected. The Sony 4K consumer projector offers that agreement, beyond the 4K 24/30fps of the current HDMI version 1.4.  <p>Display resolution was defined as to have a minimum of 3840x2160, similar to the UHD-1 lower layer of the U-HDTV standard. The U-HD display must at least have 16:9 aspect ratio, and accept 3840x2160 resolution from at least one input, in addition to be capable of upscaling lower resolution signals to its native 3840x2160.  <p>Although the CEA apparently intended to agree to a common terminology for the new sets it selected a nomenclature (U-HD) that could be misinterpreted, and even sounds as a renaming/duplication of the broader U-HDTV standard defined by the EBU, ITU, and SMPTE, which goes beyond the 3840x2160 format and also includes the 7680x4320 format (named as 8K and Super Hi-Vision by Japanese broadcasting).<i> </i> <p>The CEA did not comment if an adjustment would be pursued for the established U-HDTV formats with ITU, EBU and SMPTE standards, or for discontinuing the use of 4K and 8K in those specifications. <p><b></b> <p><b>Final Thoughts</b> <p>In my opinion the term 4K should have never been used outside the Digital Cinema compliant products such as the recently introduced Sony 4K projector or the CinemaQuattro 4K DLP 3-Chip Home Cinema Projector, but once the 4K term has been broadly used by the video electronics industry, by the standard organizations, and by the press the matter requires much more than the CEA to reconcile the confusion. <p>Add to the confusion the different bit rates, color spaces, frame rates, audio channels, and aspect ratios, and it becomes obvious that the video electronics should abandon the reference to 4K, in the same manner the TV industry never used the digital cinema term “2K” for 1080p HD products even when both have the same 1080 vertical resolution.&nbsp; <p>Whether there was a need to avoid possible law suits for misrepresentation of product capabilities that are not actually 4K, or for a need to select a consumer-friendly name for 2160p TV, better than just calling it “4K”, I agree with the need to avoid the use of the 4K name in U-HDTV panels, but the choice of the U-HD name considering that there is already an existing term “UHD-1 lower layer” within the U-HDTV standard only brings more confusion to consumers and the industry. There should be an effort to reconcile the naming conventions among the various organizations not to just unilaterally create new ones.  <p><b></b> <p><b>An Historic Perspective of DTV Naming Conventions by the CEA</b> <p>Early adopters of <a href="http://www.hdtvmagazine.com/glossary.php#HDTV+%28High+Definition+TV%29">HDTVs</a> may remember the <a href="http://www.hdtvmagazine.com/glossary.php#DTV+%28Digital+Television%29">DTV standard</a> highlighted by the <a href="http://www.hdtvmagazine.com/glossary.php#ATSC">ATSC</a> Table 3 of DTV formats (page 4 on <a href="http://www.hdtvmagazine.com/downloads/hdtv-technology-review-2003.pdf">this document</a>) approved in 1996 after decades of television engineering work to create the new digital television standard with two levels of DTV: HD (<a href="http://www.hdtvmagazine.com/glossary.php#720p">720p</a>/<a href="http://www.hdtvmagazine.com/glossary.php#1080i">1080i</a>/<a href="http://www.hdtvmagazine.com/glossary.php#1080p">1080p</a>) and SD (<a href="http://www.hdtvmagazine.com/glossary.php#480i">480i</a>/<a href="http://www.hdtvmagazine.com/glossary.php#480p">480p</a>). <p>When the first 16:9 HDTVs started to appear in the market between 1998 and 2000 there were some manufacturers that implemented 1080i resolution but still using squarish 4:3 televisions. <p>Since a 1080i HDTV program had a rectangular 16:9 image those squarish televisions displayed it as a small rectangle between top/bottom black bars. The smaller image was displayed with <a href="http://www.hdtvmagazine.com/glossary.php#810i">only 810</a> video lines, the remaining 270 video lines where used for the black bars, and in theory the lower resolution should have disqualified the TV from using the HDTV naming convention. <p>As a response, to “help avoid consumer confusion”, the CEA created in late 2000 a new level named <a href="http://www.hdtvmagazine.com/glossary.php#EDTV+%28Enhanced+Definition+TV%29">EDTV</a> (Enhanced DTV) between HD and SD which I initially thought was to be reserved for those 4:3 <a href="http://www.hdtvmagazine.com/glossary.php#Aspect+ratio">aspect ratio</a> TVs to clearly identify them for consumers that may still prefer a 4:3 TV to view plenty of legacy NTSC content in the correct aspect ratio and do not mind viewing HD in a lower resolution. <p>However, the ED level was actually used to promote the <a href="http://www.hdtvmagazine.com/glossary.php#480p">480p</a> format from its original SD level and the CEA promoted the squarish 810i DTVS to the HD level, allowing them to be advertised as HDTVs together with 720p and 1080i/p true HDTVs. This action facilitated the sale of those inferior quality DTVs at a time consumers had difficulty understanding just what HDTV was. <p>In other words, although the CEA said that the decisions were made to reduce consumer confusion the renaming actually disenfranchised unsuspected consumers. <p>It appears a similar situation has been created again with the use of the 4K term and the renaming of Ultra-HD television. This reminds me of automobile dealers renaming “used cars” for “pre-owned vehicles” to make buyers feel they were getting a better product. <p>Welcome to the 4K world, or it would be just “U-HD” for you?  <p>Stay tuned for “Living with 4K” Part 6 – Sony’s last word.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>December  5, 2012  2:34 PM</b>
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
			<?=getComments(4952)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4952)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
					<?=stripslashes($author['bio_short'])?>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/12/living-with-4k-part-5-which-4k-dci-4k-ultrahdtv-ultrahd-quadfullhd.php" type="text/javascript" charset="utf-8"></script>
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