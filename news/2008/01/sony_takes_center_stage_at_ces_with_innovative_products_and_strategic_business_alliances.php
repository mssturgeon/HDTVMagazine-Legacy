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
		AND e.entry_id = 867";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 867 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 867 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 867";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/01/sony-takes-center-stage-at-ces-with-innovative-products-and-strategic-business-alliances.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 867";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances" />
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
	<title>HDTV Magazine - Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances</title>
	<meta name="keywords" content="high definition, digital camera, ray disc, link module, blu ray, sony, new, company, digital, bravia, technology, high, camera, definition, models, device, products, content, recording, model, line, music, entertainment, television, audio" />
	<meta name="description" content="Sony rolled out new products, technologies and business relationships here on the eve of the Consumer Electronics Show. From razor-flat television technology to innovative audio, mobile and IT products, the company demonstrated its leadership across several major product categories.

Key alliances were announced at a press conference, where company executives highlighted three new exhibit areas: HDNA (high-definition), mobility and creativity.

The company took the wraps off..." />
	<meta name="title" content="Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/01/sony-takes-center-stage-at-ces-with-innovative-products-and-strategic-business-alliances.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sony rolled out new products, technologies and business relationships here on the eve of the Consumer Electronics Show. From razor-flat television technology to innovative audio, mobile and IT products, the company demonstrated its leadership across several major product categories.

Key alliances were announced at a press conference, where company executives highlighted three new exhibit areas: HDNA (high-definition), mobility and creativity.

The company took the wraps off..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=867', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/01/sony-takes-center-stage-at-ces-with-innovative-products-and-strategic-business-alliances.php">Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  6, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">Sony Takes Center Stage at CES With Innovative Products and Strategic Business Alliances</p>

<p><B>LAS VEGAS, Jan. 6 /PRNewswire/ -- CES Booth #14200</B> -- Sony rolled out new products, technologies and business relationships here on the eve of the Consumer Electronics Show. From razor-flat television technology to innovative audio, mobile and IT products, the company demonstrated its leadership across several major product categories.</p>

<p>Key alliances were announced at a press conference, where company executives highlighted three new exhibit areas: HDNA (high-definition), mobility and creativity.</p>

<p>The company took the wraps off the first Organic Light Emitting Diode television in the United States. About the thickness of three credit cards, the new OLED-TV (model XEL-1) offers picture quality with high contrast of 1,000,000:1, outstanding brightness, exceptional color reproduction, and a rapid response time.</p>

<p>Other major announcements included additions to the company's Digital Media Extender (DMeX) technology, for clip-on BRAVIA(R) TV models, a line of high-definition Handycam(R) camcorders, a new Alpha DSLR-A200 digital camera, a VAIO(R) Home Theater PC, and the innovative Rolly(TM) entertainment device with its unique combination of robotic and digital audio technologies.</p>

<p>"Sony continues to reinforce its standing as number one in the consumer electronics industry," said Sony Electronics President and Chief Operating Officer Stan Glasgow. "We are accomplishing this by offering products and technologies that show our innovation, as well as the ability to deliver the best customer experience."</p>

<p>The nation's first OLED-TV will be launched in select Sony Style retails store around the country in limited quantities beginning tomorrow.</p>

<p><br />
<B>HDNA</B></p>

<p>Coming off a strong holiday selling season, Sony added strength to its leadership position in high-definition with key product announcements.</p>

<p>The company unveiled three new modules as part of its DMeX technology, which allows consumers to add optional features to BRAVIA(R) televisions for enhanced programming and functionality.</p>

<p>First, the BRAVIA Wireless Link module, a wireless HDMI(TM) link, frees the living room of messy wires by connecting an HD transmitter to AV gear and an HD receiver behind the television for wireless 1080p/24p transmission. It supports up to four HDMI source devices at distances up to 200 feet.</p>

<p>Next, the BRAVIA DVD link module provides an easy upgrade to DVD, CD and MP3 functionality and seamlessly mounts on the back of the television.</p>

<p>And finally, the BRAVIA Input Link module extends a home theater with three additional HDMI inputs. All three will be available later this year.</p>

<p>Additionally, new broadband content providers were announced at the show for the existing BRAVIA Internet Video Link module, which streams Internet video content directly to compatible BRAVIA televisions without the need for a PC.</p>

<p>Glasgow also touted the arrival of the CBS Interactive as the latest provider to join the service, adding one-of-a-kind videos later this month encompassing primetime and daytime programming, news and sports.</p>

<p>Sony's newest offerings in its number-one selling BRAVIA flat-panel LCD HDTV line were also on display. Ranging in sizes from 19 to 52 inches (measured diagonally), the new line includes seven entry-level 720p and 10 full HD 1080p models, including the company's first 1080p 32-inch and 37-inch HDTVs.</p>

<p>New features include slim bezels on select models, a distinctive 3D graphic interface. Select models also feature compatibility with Sony's Digital Media Port, which allows users to connect a number of optional accessories like an iPod dock to access video and audio content on the television.</p>

<p>Additional advances -- such as BRAVIA(R) Sync(TM) technology for one-touch play, Motionflow(TM) 120Hz high frame rate technology for further elimination of motion artifacts and better fast-action viewing, and x.v.Color(TM) technology for a wider gamut of colors -- are all featured on a broader selection of new models.</p>

<p>The company also announced a new high-definition version of its spherical digital living room PC, the VAIO(R) TP Home Theater PC. A stylish media hub, the unit integrates a Blu-ray Disc(TM) drive, built-in DVR functionality and BRAVIA Sync technology for one-touch play. The model is available with two external CableCARD(TM) TV tuners, so you can watch and record two HD programs at the same time.</p>

<p>A sub-$200 internal BD-ROM drive that can upgrade an existing desktop computer into a high-definition Blu-ray Disc player, as well as a DVD and a CD player was also introduced today. The new BDU-X10S model drive comes with CyberLink's PowerDVD BD Edition software for playback of commercial movie titles, recorded Blu-ray Disc home videos, DVD-ROMs and CD-ROMs.</p>

<p><br />
<B>Creativity</B></p>

<p>Knowledge transfer of Sony's HD expertise was evident by the company's newest digital imaging lineup for personal content creation.</p>

<p>Sony is leading the emerging high-definition camcorder category with the introduction of full 1920 x 1080 high-definition Handycam(R) camcorders with face detection technology. This technology can identify up to eight faces in the camcorder's LCD frame and automatically adjust focus, exposure, color control and, when photos are taken, flash control. Originally developed for Sony's digital camera line, face detection is one of several new shared technologies in digital imaging aimed to improve picture quality and make video recording more enjoyable.</p>

<p>Other features on new models include 10-megapixel photo capture, as well as hybrid and hybrid-plus movie recording. Now there are two to three recording media options all in one device to lengthen recording times and provide greater convenience when shooting on-the-go.</p>

<p>Sony debuted 16 camcorders in high-definition and standard definition to meet the video needs of virtually every consumer.</p>

<p>In the digital still camera category, the company previewed its new Alpha DSLR-A200 digital camera aimed at mainstream photo buffs. This model will replace the DSLR-A100 camera and join the Alpha system with its 23 professional-grade lenses, a full array of accessories, and the enthusiast-class DSLR-A700 camera body.</p>

<p>Designed to be faster, lighter, and easier to use, this model incorporates Sony's signature Super SteadyShot(R) image stabilization system inside the camera body. It is one of many features designed to improve camera performance, as well as help ease the transition for many users from point-and-shoot to DSLR cameras.</p>

<p><br />
<B>Mobility</B></p>

<p>To meet the changing ways people want to access their entertainment, Sony introduced a completely new portable entertainment concept. The Rolly(TM) entertainment player is an audio device that moves to the beat of your music, while delivering exceptional sound quality.</p>

<p>The player's distinctive egg shape should appeal to tech-savvy, design-conscious music enthusiasts. Personal music can be downloaded from a PC or streamed to the player with any compatible Bluetooth(R) enabled stereo device. The device incorporates two gigabytes of flash memory and built-in speakers.</p>

<p>For the ultimate in amusement, the device's artificial intelligence allows it to dance along and light up to the beat of the music. The Rolly(TM) player can be personalized by programming your own motion data along with music using the bundled software.</p>

<p>Attention was also drawn to in-car entertainment with an important alliance with Ford Motor Company. Building on a successful relationship in Europe, the global automotive manufacturer has chosen Sony to be the exclusive in-car audio supplier for Ford and Mercury vehicles in North America. This phase of the two companies' collaboration will begin with the launch of the 2009 Ford Flex.</p>

<p>In addition, Sony previewed its latest line of car audio products, including nav-u(TM) portable navigation systems with POSITION Plus(TM). The premium model comes with Bluetooth(R) wireless hands-free calling. The company also unveiled its full lineup of Xplod(R) car stereo products, including six receivers made for connecting to your favorite devices.</p>

<p>Then there was the debut of the latest mylo(TM) personal communicator. The new device allows users to cruise, upload and download content on the web; chat via AOL Instant Messenger(TM); download and listen to music; and make voice calls in one stylish device. It also has a built-in digital camera with an easy-to-use interface to easily upload photos to popular web sharing sites.</p>

<p>Sony also announced a partnership with Wayport(TM) that gives all COM-2 mylo owners free Wi-Fi access at thousands of hotspots across the United States, including more than 9,000 participating McDonald's(R) Restaurants.</p>

<p>On-the-go entertainment was accentuated by the debut of Sony's latest noise cancelling headphones, which utilize an exclusive digital signal processing to reduce 99.7 percent of all ambient noise. The noise cancelling function is enhanced by artificial intelligence technology, which selects the optical noise cancelling mode based on analysis of ambient environment noise.</p>

<p>Sony Ericsson unveiled three new Walkman(R) phones as part of its total line of 21 models. With its diamond-inspired design with a bejeweled keypad, the Z555 will be the company's first 3G phone in the United States. The elegant, stylish phone also integrates gesture control so you can silence a call or snooze the alarm by simply waving your hand over the phone.</p>

<p>Other models included the W760 phone that lets you listen to tracks depending on your mood using a unique SensMe application, as well as the ultra-stylish W350 phone with its ultra-thin size at only 10 mm and a distinctive matchbook design.</p>

<p>To further accentuate Sony's unified presence at the show, there will be live performances during the week from Epic recording artists Natasha Bedingfield and Sean Kingston, Columbia recording artist and Train lead singer Pat Monahan, and Arista Nashville recording artist Jypsi.</p>

<p>Hollywood producer Dean Devlin will demonstrate Blu-ray Disc interactivity, and director Robert Luketic will share his experience shooting Sony Pictures' upcoming "21" movie release. Movie previews and insightful product and content demonstrations are planned for the duration of the show.</p>

<p>And in the rotunda in the center of the booth is an exhibit by Sony Pictures Television, featuring new programming content and distribution systems.</p>

<p>Source: Sony Electronics Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  6, 2008 10:52 PM</b>
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
			<?=getComments(867)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 867)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/sony-takes-center-stage-at-ces-with-innovative-products-and-strategic-business-alliances.php" type="text/javascript" charset="utf-8"></script>
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