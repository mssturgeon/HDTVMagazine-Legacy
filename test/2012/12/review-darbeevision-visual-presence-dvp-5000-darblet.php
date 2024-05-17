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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4954 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4954
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/12/review-darbeevision-visual-presence-dvp-5000-darblet.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4954";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Review: DarbeeVision Visual Presence DVP 5000 (Darblet)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Review: DarbeeVision Visual Presence DVP 5000 (Darblet)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Review: DarbeeVision Visual Presence DVP 5000 (Darblet)" />
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
	<title>HDTV Magazine - Review: DarbeeVision Visual Presence DVP 5000 (Darblet)</title>
	<meta name="keywords" content="film grain, glasses free, video processing, reality creation, depth enhancement, image, darblet, video, content, depth, effect, even, film, projector, should, quality, look, setting, say, grain, processing, level, rgb, appearance, original" />
	<meta name="description" content="I know what many of you would say: “if you are a purist, do not alter the original image; it should be viewed that way”.

However, you should give this little box a chance and see the results by yourself. Although I am warning you, when you do that it is almost guaranteed that you would never turn this processor off. You can adjust the effect to your preferred level of image realism and depth, and play with the settings for every content, and once you do that, there will not be a comeback to using your system without this box, you will be hooked." />
	<meta name="title" content="Review: DarbeeVision Visual Presence DVP 5000 (Darblet)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Review: DarbeeVision Visual Presence DVP 5000 (Darblet)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/12/review-darbeevision-visual-presence-dvp-5000-darblet.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I know what many of you would say: “if you are a purist, do not alter the original image; it should be viewed that way”.

However, you should give this little box a chance and see the results by yourself. Although I am warning you, when you do that it is almost guaranteed that you would never turn this processor off. You can adjust the effect to your preferred level of image realism and depth, and play with the settings for every content, and once you do that, there will not be a comeback to using your system without this box, you will be hooked." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4954', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/12/review-darbeevision-visual-presence-dvp-5000-darblet.php">Review: DarbeeVision Visual Presence DVP 5000 (Darblet)</a></td>
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
				<p>I know what many of you would say: “if you are a purist, do not alter the original image; it should be viewed that way”. <p>However, you should give this little box a chance and see the results by yourself. Although I am warning you, when you do that it is almost guaranteed that you would never turn this processor off. You can adjust the effect to your preferred level of image realism and depth, and play with the settings for every content, and once you do that, there will not be a comeback to using your system without this box, you will be hooked. <p><img style="float:none;" alt="" src="http://www.hdtvmagazine.us/reviews/images/Review_F955/clip_image002_6c53b89e-94f1-497f-8ce6-75abe78a0f70.gif" width="626" height="219"> <p>I have been testing this piece for the past few weeks, primarily with my Sony 4K projector, cable, satellite, etc. The projector’s 4K image is already 8+ million pixels (upscaling the 2+ million of 1080p/Blu-ray). Then I add the feature of Reality Creation of the projector that does exactly that, brings the 4K image to a real appearance, skin and hair details in close ups feel as being there with the person. <p>But to my surprise, I was able to even improve upon all that with the <a href="http://darbeevision.com/front">Darblet</a>, making the final image stunning to say the least, and now 4K is not enough, and the Reality Creation over 8 million pixels by the 4K projector is not enough either, “I feel that I have to have” the Darblet add its magic on top of all that, it is becoming pleasantly addictive. This box is here to stay, and maybe I will buy a few more for the panels in other rooms, one is a 60-inch Pioneer Kuro picture perfect not-yet-beaten plasma, and I am sure the Darblet would improve even that after seeing what it did with a 4K projector. <p>As I said, I did some tests with the new Dish Network Hopper whole house DVR when I was evaluating its functionality and primarily its image quality, reviewed in <a href="http://www.hdtvmagazine.com/reviews/2012/10/review-dish-network-hopper.php">this article</a>, and I had to use the Darblet to improve Dish’s typical softness. <p>Image purists such as ISF/THX calibrators are expected to criticize the Darblet image processing, because they pursue the objective of calibrating displays to the best they could be, to comply with an imaging standard that claims to let the content be seen as the director intended, no less no more, and that is a purist subject that may deserve a separate discussion (I have discussed the subject in <a href="http://www.hdtvmagazine.com/articles/2012/09/a-relative-twist-to-tv-calibration.php">this article</a>).  <p>However, that critique disregards the personal preference of the viewer about how to better view the content regardless how the director prefers it, even if that means seeing a Technicolor 50s old classic with a bit of depth by the Darbee, or with an appearance of a restored film, or even giving a bit of video look or presence and realism, if that pleases the viewer.  <p>I personally do not do that without analyzing the impact in the final image, even if I end up adding my own touches I tend to reproduce the original content as much as possible as it should look, as film or video, but I respect those that find pleasure in adding a bit more than simple touches, and believe me, the Darblet would make them salivate when they view the demos of the content with and without the Darblet effect, in real time. <p>Some history about the company and the technology could be found on these videos: <p><a href="http://www.engineeringtv.com/video/Darbee-Visual-Presence-DVP;CES-2011">http://www.engineeringtv.com/video/Darbee-Visual-Presence-DVP;CES-2011</a> <p><a href="http://www.engineeringtv.com/video/DarbeeVision-DVP-History">http://www.engineeringtv.com/video/DarbeeVision-DVP-History</a> <p><a href="http://twit.tv/show/home-theater-geeks/135">http://twit.tv/show/home-theater-geeks/135</a> (Scott Wilkinson with Mr. Darbee, Darblet covered after 20+ minutes into the video) <p>But let the images speak by themselves, just take a look of a couple of the images below: <p><img style="float:none;" alt="" src="http://www.hdtvmagazine.us/reviews/images/Review_F955/clip_image004_b9b89b5b-e216-4732-b493-64c37306d4e1.gif" width="624" height="588"> <p>And also review the following <a href="http://darbeevision.com/gallery">media gallery</a> of images and instantly see the effect by yourself. <p>Additionally, <a href="http://darbeevision.com/assets/documents/DarbeeVision%20Whitepaper%20with%20Tech%20Details%2020120415.pdf">this white paper</a> provides technical details for techies that need to know the concept beyond image appearance.  <p><img style="float:none;" alt="" src="http://www.hdtvmagazine.us/reviews/images/Review_F955/clip_image006_24402e43-a210-4022-80c3-1240c7811355.gif" width="539" height="623"> <p><b>Now, in all honesty, not everything was a love affair with the Darblet</b> <p>I noticed<b> </b>some image issues, issues that are still controllable with your preferred adjustment levels, or with almost zero effect for some content, and issues of enclosure and connections, as follows: <p>1)- Film grain appearance is augmented with Darblet levels that are more acceptable for other content such as video. The inherent grain of film sources Telecined to video seem to be detected by the Darblet as particles to be enhanced, treating them as individual objects, adding punch and depth as with any other object in the video frame, when it would be ideal not to do that with film grain, making a blue sky with film grain look like having small insects floating around the sky, and that even when setting the Darblet effect very low. <p>The demo swipe was a great tool for instantly noticing the overall effect of the depth enhancement on every part of the image as the demo bar moved from left to right and back, and the demo was very helpful to make a decision about what to do with an specific film content with excessive grain. I personally was discouraged in setting it any higher than the low teens for old classic movies showing wide camera shots of a blue sky over a desert, as in some westerns or Lawrence of Arabia for example. <p>2)- When using excessive levels of video processing beyond 60% (of a range of 0-120%), even when using the milder HD green setting, which is the lightest effect of the three (the other two are: yellow/game, and red/full-pop), it showed a level of depth enhancement that made the image less natural than what it should be in reality, so I kept the level mostly at around 35%, and sometimes even below that level, especially for film based content due to the film grain issue explained above,<img style="float:left;" alt="" src="http://www.hdtvmagazine.us/reviews/images/Review_F955/clip_image008_4d8b1598-9786-4a61-bba2-49b98b3e62de.gif" width="293" height="676"> although that is not to say the image was bad by any means, as I said before it is a matter of personal preference, and for that there are no rules other than your own. <p>3)- Instead of the “plasticky” transparent box with cables coming out of both extremes I would rather have had the option of a typical black box with connections in the back and buttons in the front. There is no need to show the electronics thru a transparent case and oblige the user to install it out of the view hanging behind the equipment, and most probably interrupting the line of sight needed by the IR remote, which then would require adding an extender/RF converter (which is what I did). By the way, Harmony already has the Darblet on their database of commands for Harmony remotes. Ironically, Mr. Darbee said he was the inventor of the pre-programmed universal remote control and one of his comments on the video was that remotes should have been RF rather than IR, but he implemented IR on the Darblet. <p><b>General Comments</b> <p>From the point of view of general image appearance after applying the Darblet depth enhancement processing I must say that overall it was pleasant to me in most circumstances, and often making look the original image without the Darblet as rather dull with no life on it, or with a lower resolution, when it was not because I was projecting a 1080p image as 4K with the Reality Creation video processing feature of the Sony 4K projector, which is exceptional as image quality. <p>In other words, the Darblet was capable to even improve such already exceptional 4K image, and that takes me to this comment: the better the quality of the display device and the original image, and the larger the image, the more obvious the Darblet improvement effect appears to be when added to the image, which is not to say that a 40-50 inches typical consumer panel would not show the improvement, it will.  <p>The power of resolving very small pixels of my 4K projector made the improvement effect of the Darblet more obvious, but conversely it was less attractive when displaying lower quality sources with Darblet levels that were exaggerated beyond 70% (available up to 120%) or when using the red setting. <p>The user is encouraged to play with the simple settings to obtain the best image possible considering the quality of the original content and the equipment, and that should not be misinterpreted as adjusting the typical sharpness control of the TV, this is way beyond what sharpness does, it is way more sophisticated and complex (read the white paper). <p><b></b> <p><b>The Darblet claims to produce an effect like 3D without glasses</b> <p>I have seen 3D applications since <a href="http://www.hdtvmagazine.com/articles/2010/02/3d-tv-at-ces-2010-was-it-actually-like-hd-a-decade-ago-part-1.php">before they arrived to market</a>, I currently project 3D in 4K on a very large screen (with 3D glasses), and have also seen many good and bad implementations of glasses-free in the US and <a href="http://www.hdtvmagazine.com/articles/2010/09/autostereoscopic-3dtv-3d-without-glasses-display-taiwan-2010-hinted-sooner-than-you-think-part-1.php">Asia</a>. <p>The best glasses-free natural image I have seen so far is from <a href="http://www.hdtvmagazine.com/articles/2011/01/glassesfree-3d-at-ces-2011-improving-but-no-cigar-except-for-the-queen-of-ces.php">3DFusion</a> auto-stereoscopic (glasses-free) 3D panels with their <a href="http://www.hdtvmagazine.com/articles/2011/06/autostereoscopic-3dtv-glassesfree-one-companys-picture-perfect-solution-how-does-it-work.php">proprietary</a> video processing engine. However, I have to admit that the depth and detail added by the Darblet is very pleasant to my eyes, and that although it is with less depth, or should I say different depth than current stereoscopic imaging, it is adding important detail that makes the image look more real without the exaggerated depth of current 3D applications. <p>Additionally, all auto-stereoscopic 3D panels have to share their total resolution among various viewing zones (each of them with lower resolution). The share occurs even if you may not actually need all those viewing zones because you may be viewing 3D alone (and not move your head). The Darblet maintains the full resolution of the image with the added appearance of depth depending on the setting level that pleases you as viewer, and always give instant access to the level with the + or – effect control in the remote. <p><b></b> <p><b>Some information provided by the manufacturer that you may find useful</b> <p>1) The Fix/Update service is free and available to correct performance issues that we have a solution for. <p>2) The Upgrades will be installed on not free basis and relate to significant improvements made to our core Visual Presence technology. <p>3) We are glad that we made the product re-programmable to solve the former. <p>4) There is nothing available for the latter right now; however as an IP development company we intend to have breakthroughs that we would like to make available to our customers. <p>5) Software/Hardware Version since first week of August: Software: 2.8.2214 and Firmware: 1.3.21 <p>6) The signal delay across the Darblet is 0.2 milliseconds for 1080p/60 (DVI) <table class="simple">
<tr class="header"><td>Input Color space</td><td>Internal Processing Color space</td><td>Output Color space</td></tr>
<tr><td>YCrCb 4:2:2</td><td>RGB Video Range</td><td>YCrCb 4:2:2</td></tr>
<tr><td>YCrCb 4:4:4</td><td>RGB Video Range</td><td>YCrCb 4:4:4</td></tr>
<tr><td>RGB 16-235</td><td>RGB Video Range</td><td>RGB 16-235</td></tr>
<tr><td>RGB 0-255</td><td>RGB PC Range</td><td>RGB 0-255</td></tr>
</table><p><b>I have not tested this pattern myself yet, but I owe the reader this information </b> <p><a href="http://cdn.avsforum.com/a/a8/a845c7be_HD.jpeg"><img alt="" align="left" src="http://www.hdtvmagazine.us/reviews/images/Review_F955/clip_image010_0bb68f46-6e6a-4a91-b669-970584088ae9.jpg" width="333" height="194"></a><img style="float:right;" alt="" src="http://www.hdtvmagazine.us/reviews/images/Review_F955/clip_image012_bec34b79-d21d-417c-b025-ab25228a7bb2.jpg" width="135" height="176">It was reported by some Darblet owners in Internet Forums and by some reviews that when testing the Darblet at 75% setting with the Spears and Munsil High Definition Benchmark disc, specifically with the Luma and Chroma Zone Plate test patterns, it produced some image alterations as shown in the picture. However, the alterations did not show with actual content, nor it was indicated that with lower settings the alterations disappeared. <p>Many owners of the Sony 4K projector reported very high satisfaction with content enhanced by the Darblet, which coincides with my experience using the same high quality projector. <p>If I have the chance I intend to perform the tests myself, but I hope the manufacturer would look into the matter when reading this. <p><b>Final Thoughts</b> <p>In summary, my congratulations to Mr. Darbee for developing an excellent product, that although at $349 MSRP is not low in price, it produces considerable and unique image improvements no other product I know does this well, at any price. So, if you are looking for a simple way to improve image quality and extract maximum performance from your existing equipment you owe a Darblet to yourself, you will not be disappointed. <a name="HDBenchmark0"></a><a name="Press"></a>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>December  5, 2012  2:52 PM</b>
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
			<?=getComments(4954)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4954)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/12/review-darbeevision-visual-presence-dvp-5000-darblet.php" type="text/javascript" charset="utf-8"></script>
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