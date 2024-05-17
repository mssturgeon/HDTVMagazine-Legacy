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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4831 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4831
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/06/does-your-lcd-image-look-the-same-from-an-angled-view-part-3-how-the-3m-solution-applies-to-your-lcd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4831";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD" />
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
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

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
	<title>HDTV Magazine - Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD</title>
	<meta name="keywords" content="local dimming, light distribution, distribution films, our film, reflective polarizer, light, our, films, polarizer, local, lcd, distribution, dimming, panel, film, angle, image, source, reflective, lit, edge, technology, used, back, product" />
	<meta name="description" content="As covered in detail in parts 1 and 2, for years LCD companies have claimed that their sets can be viewed all the way to 170+ degrees. That number would be like viewing the panel all the way to the side, almost parallel to the edge of the TV frame. Have you actually tried to do that with your set? Do you still see the same image quality if any image at all? Is the image still appealing to you, or you rather move back to the center? Try moving gradually from the center passing the 20 degrees angle to the left or right and keep increasing the angle (20 degrees is about what your companion viewer may be seeing if sitting right by your side). This report (Viewing Angles section) covers more detail about the subject.

Companies like..." />
	<meta name="title" content="Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/06/does-your-lcd-image-look-the-same-from-an-angled-view-part-3-how-the-3m-solution-applies-to-your-lcd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As covered in detail in parts 1 and 2, for years LCD companies have claimed that their sets can be viewed all the way to 170+ degrees. That number would be like viewing the panel all the way to the side, almost parallel to the edge of the TV frame. Have you actually tried to do that with your set? Do you still see the same image quality if any image at all? Is the image still appealing to you, or you rather move back to the center? Try moving gradually from the center passing the 20 degrees angle to the left or right and keep increasing the angle (20 degrees is about what your companion viewer may be seeing if sitting right by your side). This report (Viewing Angles section) covers more detail about the subject.

Companies like..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4831', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/06/does-your-lcd-image-look-the-same-from-an-angled-view-part-3-how-the-3m-solution-applies-to-your-lcd.php">Does Your LCD Image Look the Same from an Angled View? (Part 3) - How the 3M Solution Applies to Your LCD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June  6, 2012</b>
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
				<p>As covered in detail in parts 1 and 2, for years LCD companies have claimed that their sets can be viewed all the way to 170+ degrees. That number would be like viewing the panel all the way to the side, almost parallel to the edge of the TV frame. Have you actually tried to do that with your set? Do you still see the same image quality if any image at all? Is the image still appealing to you, or you rather move back to the center? Try moving gradually from the center passing the 20 degrees angle to the left or right and keep increasing the angle (20 degrees is about what your companion viewer may be seeing if sitting right by your side). <a href="http://www.displaymate.com/LCD_Plasma_ShootOut.htm">This report</a> (Viewing Angles section) covers more detail about the subject. <p>Companies like Panasonic, Sharp and others have implemented new technologies that claim view-angle improvement on their sets, but one company have created a product that could be used by all manufacturers that would like to adopt it, that company is 3M.  <p>How this technology can be applied to the type of LCD/LED you like? <p><img style="background-image: none; border-bottom: 0px; border-left: 0px; padding-left: 0px; padding-right: 0px; display: inline; float: left; border-top: 0px; border-right: 0px; padding-top: 0px" title="clip_image002" border="0" alt="clip_image002" align="left" src="http://www.hdtvmagazine.us/articles/images/a4f4deaf7870_14C5E/clip_image002_5d87e92b-7a21-4727-b82d-17cf4c4fc8a7.jpg" width="485" height="324"> <p>As mentioned in part 2, the 3M product adds two components to the typical light source and LCD panel components. <p><i>Light Distribution Films.&nbsp; This box can represent the various combinations of diffuser, prism, and microlens films that are used to shape and make uniform the output of the light source.&nbsp; The output from the Light Distribution Films is spatially uniform, and it is typically collimated to some degree, meaning the light is contained within a cone.&nbsp; The shape or size of the cone is entirely defined by the combination of Light Distribution Films. <br></i> <p><i>Reflective Polarizer.&nbsp; This box can represent 3M's DBEF and APF families of products.&nbsp; I am trying to represent several things with this film.&nbsp; First, some of the light from the Light Distribution Films is transmitted directly through the Reflective Polarizer (blue arrows).&nbsp; These blue arrows are within the same cone as that which comes from the Light Distribution Films.&nbsp; Second, some light of the wrong polarization is reflected by the Reflective Polarizer and interacts with one or more of the Light Distribution Films before being reflect back toward the Reflective Polarizer (thick green arrows).&nbsp; This is the light that has a chance to emerge from the Reflective Polarizer at broader angles than the cone defined by the Light Distribution Films.&nbsp; Third, there is a portion of light reflected by the Reflective Polarizer which makes it all the way back to the light source before being reflected back toward the panel (thick red arrows).&nbsp; This light emerges from the reflective polarizer within the cone defined by Light Distribution Films.</i> <p>Based on the description Dr. David Lamb detailed in Part 2 of this series and excerpted above I asked him the following questions (in bold), and his responses are below (<i>in italic</i>): <p><b></b> <p><b>1) </b><b>Although you specified that the light source of your example could be LED arrays (local dimming) or edge-lit source, it appears to me that there may be a difference/preference in the results when you apply your product, expecting an increase of light even on local dimming designs.&nbsp; I am not sure your graph was already representing local dimming, and perhaps top/edge light LCD design should show a graph with less light reaching the distributor layer.</b> <p><b>Even if more light can be obtained from the light source layer, local dimming typically helps in accurately directing the light to small areas of the image while keeping surrounding areas dark if the content is such, increasing contrast ratio (CR).&nbsp; Wouldn't your distributor/polarizer be counteracting with the needed directionality of a local dimming design and actually reduce CR? If so, would your product fit better for top/edge lit designs? And should rather not be used with local dimming designs because it may reduce CR? </b> <p><i>Our products work similarly in all types of panels, whether edge-lit or direct lit.&nbsp; You make a good point about local dimming and dynamic contrast.&nbsp; We have not quantified a reduction in contrast for locally dimmed sets, but I would expect it to be a second order effect.&nbsp; From a market acceptance standpoint, our products are used in the highest end locally dimmed LCDs on the market, so any reduction in contrast is at least accepted by our customers and</i> <i>all major brands and panel manufacturers use our films in some portion of their product portfolios.</i> <p><i>We simply have not quantified what I would call "cross-talk effects" in locally-dimmed sets.&nbsp; Given that our products are used in TVs which are locally-dimmed, I think that the cross-talk effects (which I acknowledge will be real) either is not large enough to be noticed or that manufacturers using our film can design the local dimming algorithm to minimize the effects of cross talk.&nbsp; I have never been asked about this by our customers, but I agree that it is an interesting subject.</i> <p><i></i> <p><b>2) </b><b>Other than showing non-uniformity, what is the reason your graph shows a perfect 90 degree angle in one side of the light but a spread of light on the other side? In theory if using local dimming the light output should be spread evenly 180 degrees on each LED, and your distributor/polarizer would amplify the effect on the final image.</b> <p><i>The cartoon that I drew was supposed to be representative of many different types of lighting.&nbsp; What I have shown is more consistent with what is seen in edge lit displays.&nbsp; You are correct:&nbsp; In direct-lit displays, the output from the light source is angularly much more uniform (though not spatially).&nbsp; Please be careful in drawing too many conclusions about specific ray paths in my cartoon.</i> <p><b>3) </b><b>What is the estimated light reduction of having two extra layers (distribution and polarizer) vs. the gaining of light output they provide? for example: 60% light gaining due to the technology spreading light, minus 20% light blocking due to the two extra layers (green and red arrows) would net a 40% light increase? (of course in addition to the benefit of spreading the light evenly) Do you have such numbers?</b> <p><i>There is not a big optical penalty for having additional layers in the backlight.&nbsp; The layers have very little absorption -- otherwise the performance would suffer tremendously.</i> <p><b>4) </b><b>When I casually saw the demo the panel appeared to maintain color/contrast/brightness uniformity at least up to 60% view-angle (120 degrees L-to-R), not to the level of plasma but quite an improvement compared to the typical 15% of many LCDs before they start to drop image quality.&nbsp; Although 3M may not want to publish a number in angle improvement due to legal considerations, would my gross estimate of 60% be consistent with your lab tests? </b> <p><i>I think your assessment of our demo and the viewing angle benefit is fair.</i> <p><b>5) </b><b>How much more would a $500 LCD panel would cost if using this technology?&nbsp; Would the technology be so costly to be non-attractive until a panel price hits $1000 or so? (You fill the blank).&nbsp; Would the technology cost more as panel size increases? Not recommended for small panels?</b> <p><i>The price of our products is, of course, based on volume, so it's difficult to give you exact details.&nbsp; Also, the application of our film enables the removal of other components from the system (such as light sources), so it is possible (in some cases) for our film to reduce the overall system cost.&nbsp; On an order of magnitude, though, our solution costs $10 (not $1 and not $100).&nbsp; Anecdotally, we can find TVs in the market with our film that has a lower selling price than similarly-featured TVs without our film.&nbsp; Because we are so far back in the supply chain, we don't think our film significantly affects the final price of the TV to consumers.</i> <p><i>With "enables the removal of other components from the system (such as light sources)," I meant additional light sources.&nbsp; This is true as long as the design is not uniformity-limited.&nbsp; In cases where uniformity limits the number of light sources, the addition of our films can still result in savings.&nbsp; For a fixed number of LEDs, adding our film enables those LEDs to be run at a lower power to achieve the same brightness.&nbsp; This reduces requirements for the electrical power supply and can result in a cost reduction somewhere else in the system.</i> <p><i></i> <p>This is the last part of this LCD article series about angle of view, and I hope the reader found it useful. LCD is also known to be subjected to blurriness because of the sample-and-hold mode of operation and <a href="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php">this article</a> covers the subject. <p>I thank again Dr. David Lamb of 3M for his participation in this article.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June  6, 2012  8:55 PM</b>
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
			<?=getComments(4831)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4831)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/06/does-your-lcd-image-look-the-same-from-an-angled-view-part-3-how-the-3m-solution-applies-to-your-lcd.php" type="text/javascript" charset="utf-8"></script>
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