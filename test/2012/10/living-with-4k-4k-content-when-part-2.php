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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4923 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4923
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/10/living-with-4k-4k-content-when-part-2.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4923";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Living with 4K: 4K Content, when? (Part 2)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Living with 4K: 4K Content, when? (Part 2)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Living with 4K: 4K Content, when? (Part 2)" />
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
	<title>HDTV Magazine - Living with 4K: 4K Content, when? (Part 2)</title>
	<meta name="keywords" content="blu ray, sony projector, recorded media, video processing, pre recorded, content, resolution, image, video, original, sony, display, blu, ray, displays, should, projector, quality, pixels, both, even, consumer, distribution, years, hdtv" />
	<meta name="description" content="On the first article of this series I discussed about getting a 4K consumer projector as an early adopter. I also introduced UHDTV and discussed 4K and 8K image resolutions. In this part 2 article I will discuss how one can start enjoying the capabilities of a 4K display, even without 4K content.

Not having 4K content available now does not render a 4K display useless, the same as HDTVs without much content in 1998/9 were questioned, with just a few HD loops and no Blu-ray until 8 years later." />
	<meta name="title" content="Living with 4K: 4K Content, when? (Part 2)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Living with 4K: 4K Content, when? (Part 2)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/10/living-with-4k-4k-content-when-part-2.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="On the first article of this series I discussed about getting a 4K consumer projector as an early adopter. I also introduced UHDTV and discussed 4K and 8K image resolutions. In this part 2 article I will discuss how one can start enjoying the capabilities of a 4K display, even without 4K content.

Not having 4K content available now does not render a 4K display useless, the same as HDTVs without much content in 1998/9 were questioned, with just a few HD loops and no Blu-ray until 8 years later." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4923', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/10/living-with-4k-4k-content-when-part-2.php">Living with 4K: 4K Content, when? (Part 2)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October 22, 2012</b>
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
				<p>On the first article of this series I discussed about getting a 4K consumer projector as an early adopter. I also introduced UHDTV and discussed 4K and 8K image resolutions. In this part 2 article I will discuss how one can start enjoying the capabilities of a 4K display, even without 4K content.  <p><b></b> <h2>Video Processing for the Wait</h2> <p>Not having 4K content available now does not render a 4K display useless, the same as HDTVs without much content in 1998/9 were questioned, with just a few HD loops and no Blu-ray until 8 years later. <p>The internal video processing of the 4K display still does a good job in showing an interpolated 4K version of a lower resolution source, such as 1080p Blu-ray, and even DVD quality. <p>Since digital TV was invented we know that no matter how good the video processing could be in interpolating millions of pixels that are smartly added by a TV in every video frame, and also motion-adapted to the previous and following video frames, there is no replacement for the loss of original resolution due to compression or format conversions. The original resolution cannot be restored as it was, the TV is just filling pixels to the best it can. <p>And if the resolution was not there to start with, the interpolated 4K image would not be of the same quality as original 4K content would, regardless how acceptable the image may appear to non discerning eyes. <p>The Sony projector does an excellent job with its Reality Creation feature, a proprietary intelligent algorithm that incorporates a huge database of known objects and associated signals to produce a very pleasant image (I will discuss more in the next articles). <p>When the first HDTVs appeared in 1998/9 many early adopter videophiles used them to upscale DVDs to HD (from 720x480 to 1920x1080), six times the jump in resolution per each video frame. <p>In the case of a 4KTV displaying a 1080p Blu-ray it would upscale mathematically twice in both directions, doubling the pixel count evenly (1920x1080 to 3840x2160). The Reality Creation feature of the Sony projector takes it from there and uses the created pixels to make the image even more stunning. <p>Sony’s SXRD chip actually has 4096x2160 pixels (17x9 aspect ratio) and is a bit wider than 16x9 HD, so it uses the 3840 center pixels of the 4096 line to display the 16x9 image, in other words it does not upscale unevenly from 1920 to 4096, which can complicate the quality of the image rather than just doubling to 3840 and use the center of the chip, showing two slim pillar black bars on the sides with the unused pixels (the example of the bottom in the graph below - HDTV/GAME). <p><img src="http://www.hdtvmagazine.us/articles/images/7834fc348f07_135AA/clip_image002_1de6beb9-61c1-43c7-b974-1ffb2d88d13d.gif" width="624" height="351"style="float:none;"> <br /><i>Graph courtesy of Sony</i><br /> <p>The other 4K displays (84" LCD panels announced by LG and <a href="http://www.youtube.com/watch?v=repTqk1kYOk&amp;feature=related">Sony</a> for $20,000 - $25,000, respectively) are actually 3840x2160 and 16x9 aspect ratio, and are 4K in Quad HD, not 4096x2160 Digital Cinema as the Sony projector, so their upscaling to 4K should fill the screen edge to edge, and the Sony 4K LCD accepts 4K via HDMI in both inputs.  <h2>What is you excuse for avoiding 4K?</h2> <p>Many get hung up with the idea that, unless the screen is very large, a TV at that resolution is not needed based on the human’s eye acuity and pixel size, or is not needed because people in practice would not view from closer to use the virtue of a smaller pixel size, so why investing in increased resolution then?, or with the idea of how difficult it is (today) to transmit, or to store in a disc, 4 to 16 times the data, or about the higher cost of the environment needed to handle a 4K load to produce content, etc. <p>Current limitations of distribution and consumer format storage should not stop a director using a high quality 4K camera to capture the best detail possible of an event that will not occur again, because although the high quality content may not be used to its full potential today future technologies may benefit from a highly resolved original, similarly to when we appreciate a restored version of “Casablanca” today. <p>Additionally, it is always possible to downscale from a better original for other practical uses, such as today’s streaming over limited bandwidth for portable devices or smaller screens, without having to compromise the quality of the original. <p>Similarly, as mentioned before, until 4K pre-recorded media and methods of 4K distribution eventually become available, a 4K projector/panel should be capable to show an improved version of an HD image, as HDTV was used to view DVD better over the past 15 years.  <p>In other words, currently the 4K market has a) original 4K content from movies and b) a couple of 4K consumer displays and growing, both at the extremes of the content path. We need the CE industry to fill the gap in between with 4K consumer media and methods of 4K distribution, and we know from the past that most implementations of consumer technology typically did not implement all the pieces simultaneously and in harmony, and that should not preclude the early adoption of 4K displays.  <p>When DVD was created in 1996 based on NTSC 480i it was released while HDTV was about to be released with 1080i digital 2 years later, but even when HDTV was introduced in 1998, HD pre-recorded media was not available until 8 years later, in 2006 with Blu-ray (in battle with HD DVD). <p>In other words, over the past 15 years there was a chicken and egg situation between digital displays and content, and 4K should not be considered different. We just have to be patient and 4K content will arrive. <p><b></b> <h2>4K Content when?</h2> <p>DirecTV is already <a href="http://advanced-television.com/2012/03/15/directv-planning-for-u-hdtv/">planning for 4K</a> and has committed to <a href="http://advanced-television.com/2012/10/01/37560/">start in 2016</a>, and 135 UHDTV channels by end of 2017 <a href="http://advanced-television.com/2012/10/04/ultra-hd-135-channels-by-2017/">were just announced</a> separately: “<i>Credit Suisse says that broadcasters in the US and Europe would be starting their roll-out of U-HD in 2015-16. “We forecast 5 Ultra HD (4k) channels in 2013 (estimated) growing to 135 by the end of 2017 (estimated).</i> <p>And it will repeat again with 8K. <p>One primary ingredient needed to fill the gap between original 4K content and displays is the availability of an efficient compression algorithm that would facilitate 4K distribution and pre-recorded media. A draft of the new HEVC (High Efficiency Video Coding) H.265 compression standard <a href="http://www.tvtechnology.com/news/0086/mpeg-h-draft-standard-disclosed-/214981">has been submitted</a> in August 2012 for approval. H.265 is known to be 50% more efficient than the current H.264 MPEG-4, and even claims to offer a better image. The standard is expected to be approved early in 2013, and that may open the door for near future 4K Blu-ray media and other distribution methods. <p>But higher resolution and better compression is not all of what UHDTV can bring, a larger color space in tune with the human vision capabilities much wider than the Rec. 709 color space of HDTV, the use of higher bit (12), and faster frame rates (than 24 for film sources and 30 for video sources) are promising features, although most probably 4K for home would be limited initially to the current 8-bit color, and be no faster than the current 24 frames-per-second for film sources (like Blu-ray 1080p). <p><b></b> <h2>Are we actually moving forward?</h2> <p>Should that be a deterrent for you to enjoy what a 4K display can bring you today? For many the answer is yes, mainly driven by the high price and the unavailability of 4K content. <p>I look at it differently. I evaluate the investment based on a) the upgradability and readiness of the display and b) the proven willingness of the manufacturer to keep up with the product as technology advances. <p>The Sony 4K projector:  <p>1) Can already accept 4K (as opposed to JVC’s 4K “precision” e-shift projectors using a 1080p chip and not accepting 4K),  <p>2) Can accept DCI; and 12-bit (although not in 4K),  <p>3) Displays 3D as 4K resolution “per eye” with active-shutter glasses, as opposed to the announced 84” LCD 4K panels that display 3D as 1080 lines per eye using 3D passive technology, sharing the 2160 rows of the vertical resolution for both eyes, in addition to have a FPR grid to split the left and right views, and <p>4) If future versions of HDMI would support higher 4K frame rates, such as 60 fps, Sony declared that they will offer both hardware and software updates to their customers.&nbsp; <p>Stay tuned for part 3.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October 22, 2012  7:15 PM</b>
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
			<?=getComments(4923)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4923)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/10/living-with-4k-4k-content-when-part-2.php" type="text/javascript" charset="utf-8"></script>
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