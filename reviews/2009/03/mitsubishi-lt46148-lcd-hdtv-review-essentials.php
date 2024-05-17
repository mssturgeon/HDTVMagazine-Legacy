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
		AND e.entry_id = 1689";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1689 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1689 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1689";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-review-essentials.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1689";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Mitsubishi LT-46148 LCD HDTV - Review Essentials" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Mitsubishi LT-46148 LCD HDTV - Review Essentials" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Mitsubishi LT-46148 LCD HDTV - Review Essentials" />
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
	<title>HDTV Magazine - Mitsubishi LT-46148 LCD HDTV - Review Essentials</title>
	<meta name="keywords" content="casual viewer, video standards, viewing angle, lcd hdtv, flat panel, display, video, viewing, lcd, input, color, feature, mitsubishi, performance, better, using, casual, sources, hdmi, galleryplayer, calibration, processing, response, output, smooth" />
	<meta name="description" content="LCD has come a long way since just a few years ago, taking over the flat panel market up to about 50&quot; in screen size. While Mitsubishi is far better known for rear projection DLP since the demise of CRT, market forces can't be ignored and consumers remain spellbound by the flat panel concept in their homes. Let's see what Mitsubishi has to offer and how their LCD products compare. 

Since last year Mitsubishi has offered 40&quot;, 46&quot; and 52&quot; models and the LT46148 is part of their current line of 1920x1080 displays." />
	<meta name="title" content="Mitsubishi LT-46148 LCD HDTV - Review Essentials" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Mitsubishi LT-46148 LCD HDTV - Review Essentials" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-review-essentials.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="LCD has come a long way since just a few years ago, taking over the flat panel market up to about 50&quot; in screen size. While Mitsubishi is far better known for rear projection DLP since the demise of CRT, market forces can't be ignored and consumers remain spellbound by the flat panel concept in their homes. Let's see what Mitsubishi has to offer and how their LCD products compare. 

Since last year Mitsubishi has offered 40&quot;, 46&quot; and 52&quot; models and the LT46148 is part of their current line of 1920x1080 displays." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1689', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-review-essentials.php">Mitsubishi LT-46148 LCD HDTV - Review Essentials</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>March 18, 2009</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=277&category=HDTV Displays">HDTV Displays</a></b>
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
				<table class="greygrid"><tbody>     <tr>       <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td>     </tr>      <tr>       <td class="greygrid">&#160;</td>        <td class="greygrid"><b>MSRP</b></td>        <td class="greygrid"><b>Street</b></td>        <td class="greygrid"><b>Amazon.com</b></td>     </tr>      <tr>       <td class="greygrid"><b>Mitsubishi LT-46148 LCD HDTV</b></td>        <td class="greygrid">$2,099.00</td>        <td class="greygrid"><a href="/equipment/model.php?a=B0018C7FVQ&amp;man=MITSUBISHI&amp;model=LT-46148" target="_blank">$1,339.87</a></td>        <td class="greygrid"><a href="http://www.amazon.com/gp/product/B0018C7FVQ?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B0018C7FVQ" target="_blank">$1,479.84</a></td>     </tr>   </tbody></table>  <p>Serial #: 101561    <br />Warranty: 1 year parts and labor     <br />Product Source: Manufacturer </p>  <p><strong>Summary: Better than expected for casual viewing applications </strong></p>  <p>LCD has come a long way since just a few years ago, taking over the flat panel market up to about 50&quot; in screen size. While Mitsubishi is far better known for rear projection DLP since the demise of CRT, market forces can't be ignored and consumers remain spellbound by the flat panel concept in their homes. Let's see what Mitsubishi has to offer and how their LCD products compare. </p>  <p>Since last year Mitsubishi has offered 40&quot;, 46&quot; and 52&quot; models and the LT46148 is part of their current line of 1920x1080 displays. </p>  <h2>Common Features </h2>  <ul>   <li>Black cabinet finish with swivel stand </li>    <li>Auto-sensing inputs </li>    <li>Back panel - 4 HDMI 1.3 with Deep Color, X.V. Color and Simplay HDMI certified, 2 component video (input 1 supports optional composite video and 2 is component only), 1 composite video or s-video (input 3) </li>    <li>Side Panel AUX Input - component video or composite video and USB Photo Port (JPEG files) </li>    <li>Accepts 480i, 480p, 720p, 1080i, 1080p and 1080p24 frame </li>    <li>serial RS232 port </li>    <li>All video inputs support analog left/right audio </li>    <li>HDMI L/R analog audio inputs (assignable) </li>    <li>RCA digital audio output (internal tuner and video inputs) </li>    <li>Antenna 1 main and Antenna 2 aux for NTSC, DTV, analog cable, QAM cable 64 and 256 (Clear QAM) </li>    <li>Fairly good size remote that was comfortable in the hand, easy to use and includes an orange backlighting button providing great clarity to read the buttons in the dark </li>    <li>SD aspect ratios - standard (16:9), expand, zoom, stretch plus, narrow (4:3) </li>    <li>HD aspect ratios - full native (1:1 pixel mapping), standard (16:9 with marginal over scan), wide expand, zoom </li>    <li>PC aspect ratios - Centered 1:1 pixel mapped centered output 4:3 and 16:9, zoom (fills out screen), reduce for 1080p only </li>    <li>Plush 1080p internal scaler </li>    <li>Smooth 120 Hz LCD Video Processing </li>    <li>Backlight Adjust </li>    <li>WCG-CCF back lighting, Wide Color Gamut - Cold Cathode Fluorescent for accurate color </li>    <li>10-bit digital video processing </li>    <li>CEC HDMI control </li> </ul>  <h2>Not-So-Common Features </h2>  <ul>   <li>Perfect Color </li>    <li>NetCommand IR control </li>    <li>IR NetCommand output or IR input </li>    <li>TV Guide on screen guide (DTV or digital cable only) </li>    <li>GalleryPlayer Images </li> </ul>  <h2>Missing Features </h2>  <ul>   <li>4:3 squeeze aspect ratio for proper geometry with stretched 4:3 HDTV 16:9 broadcasts </li> </ul>  <h2>Out of Box Performance</h2>  <p>In a matter of minutes the TV went from out of the box to displaying images from a TiVo Series 3 using HDMI via the auto input sensing feature. It does take a minute or two for the display to recognize the connection and perform this function for you. After setting up the input it will ask about setting up the remote for NetCommand which I skipped. As with all display products you get <em>sales mode</em> from the manufacturer which is their calibration to induce your purchase having nothing to do with performance imaging and video standards. My son joined me on this first look and both of us quickly grew weary of the artificial artifact ridden response. Having experience with Mitsubishi products I set the picture mode to Natural, color temp to Low and Video Noise to off creating a perceptually pleasing response similar to what we would expect with video standards. As we went channel surfing, first impression was intermittent jumping or skipped frames. We finally settled in on a 20 year old movie on HDNet. This led to a discussion of how this movie did not look its age as if it had just been shot with an HD video camera. My son followed up with comments of how so far it looked like computer generated images along with an artificial motion response. I went into the menu and turned off the Smooth 120 Hz LCD processing making the movie finally look like film, removing the CG motion artifact as well. We played with this feature some more and spent about 10 minutes on one particular scene using the TiVo DVR function. The Smooth 120 Hz LCD processing has three settings, off, medium and high. High provided the most artificial response and quirky motion totally un-natural. Medium was little improvement. While this feature did remove motion blur for the most part it would intermittently lose cadence lock jumping a frame or blurring for a moment. We both agreed the best setting was with this feature turned off. Some more surfing and testing of the Smooth 120 Hz feature brought us to another HDNet Movie from 1996. Ultimately we found the imaging seductive along with the movie being entertaining and involving so we turned off the feature and ended up watching the movie all the way through. </p>  <h2>Gallery Player</h2>  <p>If you were interested in this feature GalleryPlayer has closed it's doors. Per GalleryPlayers website they ceased operations July 30<sup>th</sup> 2008. CrunchGear reported that founder Scott Lipsky had sold the company August 2008. On August 27<sup>th</sup>, 2008, Mitsubishi issued a press release stating, "Mitsubishi Digital Electronics America, Inc. has been informed by GalleryPlayer, Inc. that GalleryPlayer will no longer provide the service that enables owners of certain models of televisions to download and display art and photographic imagery on their flat panel HDTVs. Accordingly, while owners can continue to view their own photographs on their televisions, that portion of the GalleryPlayer feature which was designed to allow access to the GalleryPlayer on-line library of images will not function as advertised". The loss of the GalleryPlayer feature affected models from Panasonic and Samsung as well. </p>  <p>You can still put pictures on a USB flash drive but unfortunately PC images are encoded for PC video 0-255 and this input is setup for consumer video, 16-235. That means upper whites, 235-255, and lower blacks, 0-15, are clipped delivering images with artifacts appearing overdriven with highlights washed out, blacks cut off, devoid of color or wrong color. For those who know what good video should look like it will be obvious yet those who do not know any better may be potentially satisfied.</p>  <h2>Using a PC</h2>  <p>The display does not offer a VGA PC input requiring you have a DVI or HDMI digital video output instead from your PC. According to the manual you must name the HDMI input you are using for your computer, PC, &quot;It is important to use the name PC so that the TV can process the video signal correctly&quot;. This setting is critical if you want the most out of your PC because it allows 4:4:4 color processing. This also changes your aspect ratio options adding a 1:1 pixel mapped centered output with some scan rates. </p>  <p>If you select PC for the input name the display expects PC progressive scan rates at a 60 Hz frame rate. It won't display 1080i as an example if you are using your PC for DTV outputting native 1080i. If you expect to run a PC Blu-ray player in the future it won't accept 1080p24 frame properly either telling you this scan rate doesn't work. Although it will show an image, after downsizing it with black borders all around, your 1:1 pixel map is destroyed.</p>  <h2>Noise</h2>  <p>One of the quietest displays I have had in my presence. This is to be expected of LCD in general. </p>  <h2>Maintenance</h2>  <p>None that I am aware of. </p>  <h2>Problems</h2>  <p>During the HQV Benchmark Blu-ray test I was checking how the 120 Hz processing was responding to the panning stadium test which requires bringing the menu up and multiple cursor key presses to navigate to the setting finally exiting from the menu to view the image. On one occasion the menu was stuck on the screen and all controls locked out with no way to recover without pulling the AC cord from the wall. I just let it sit and fortunately within about 1 minute the display recovered returning to normal. </p>  <h2>Viewing Angle</h2>  <p>The display offers a wide horizontal left to right viewing angle without the typical washed out look of older generation LCD panels. It does slightly change the black levels and slightly discolors at extreme angles. Vertical angles were another story and in that case the display did significantly wash out and discolor. If you are installing the display over a fireplace for example you will want to use a mount that tilts it downward to try and maintain a 90 degree viewing angle to your favored sitting position. If you are using the display at a normal screen height lined up with your viewing positions, either mounted on the wall or using the supplied stand, you should be fine. </p>  <p>If viewing angle is important in your application there are other brands that perform better in both the vertical and horizontal planes. </p>  <h2>Subjective Viewing Results</h2>  <p>As noted in the <a href="http://www.hdtvmagazine.com/test/2009/03/mitsubishi_lt46148_lcd_hdtv_on_the_test_bench.php" target="_blank">bench testing portion</a> the display is not inclined towards ISF calibration but does provide a surprisingly good response with factory settings. The only time calibration errors were clearly visible was when viewing content I am intimate with limiting such perception to test materials or menus from sources. </p>  <p>With the Smooth 120 Hz LCD Processing turned off, a variety of material and sources were viewed for over two months with not one complaint. There were rare occasions where having Smooth 120 Hz turned off revealed significant motion artifacts with 24 or 30 frame sources. Nobody in the house wanted it turned on due to the artificial CG outcome of the feature and I was the only one who ever noticed the occasional motion errors. </p>  <p>The display has ample light output, a great contrast ratio and the appearance of deep dark blacks. The unique back lighting design provides a wide range of light output to match your viewing environment. Image details were sharp and crisp. HD sources looked fantastic along with upscaled SD content from DVD or from a TiVo Series 3 DVR and cable service. If you are not going to provide sources that upscale to HD, using the internal Plush 1080p scaler of the display instead for standard definition sources such as the antenna input for analog cable, you may wish you had your old analog TV back. SD scaling is quite poor so upgrading your sources is highly recommended for the first time HDTV buyer. If you are replacing an older HDTV display, you more than likely have all this covered already. </p>  <h2>Putting it in Perspective</h2>  <p>This display was used in the upstairs casual viewing environment. While the ultimate in all performance attributes is not the main goal for that application, I prefer a display that can at least be calibrated to video standards for an accurate color response. I can do better in that regard with a different brand and it is a shame Mitsubishi does not want to provide at least that level of calibration capability. </p>  <p>With videophiles out of the loop I can concentrate on the typical viewer, but need to break that down into two groups; casual viewer with some money to spend on sources and casual viewer on a budget. </p>  <p>For the budget casual viewer who intends to just drop this display into a current standard definition setup using the same standard definition connections that you always have, then you can also do better with a different brand due to the flaws of the internal Plush 1080p scaler. </p>  <p>For the casual viewer who intends to provide HD service via an external set top box, provide an upscaling DVD player or Blu-ray / DVD combo player, an Xbox 360 or PS3 or use a PC, this display has a lot to offer mostly because of the better than average factory calibration available at your fingertips. </p>  <p>Price versus performance may be the faltering point though, even for the casual viewer that will spend money on upgrades. Even if the flaws can be overlooked one might wonder why they would do that since they can do better in some performance areas for plus or minus $200. </p>  <h2>Final Conclusion</h2>  <p>If you are a videophile intending to ISF calibrate, looking for the best performance and accurate imaging from a display, this is not the right product for that application. As noted in the &quot;<a href="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi_lt46148_lcd_hdtv_on_the_test_bench.php" target="_blank">On the Test Bench</a>&quot; follow-up piece, the response is a mixed bag of results and the product lacks the necessary controls to correct errors. </p>  <p>If you are looking for some good performance, accuracy is not required and you will not be ISF calibrating, then this display is worthy of your consideration. That represents 90% of the market who have never heard of the concept of video standards much less video calibration. Indeed, this display has much to offer because with a few control setting changes, the factory calibration is not that far off, representing one of the better factory imaging responses available. I was satisfied over the two months and never developed a need to return to our Samsung DLP display. Overall, performance results fall somewhere between &quot;casual viewing&quot; and &quot;performance viewing&quot;. <p>Stay tuned tomorrow for the remainder of this review where we put the <a href="http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi_lt46148_lcd_hdtv_on_the_test_bench.php" target="_blank">Mitsubishi LT46148 LCD HDTV "On the Test Bench"</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>March 18, 2009  9:29 AM</b>
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
			<?=getComments(1689)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 1689)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/03/mitsubishi-lt46148-lcd-hdtv-review-essentials.php" type="text/javascript" charset="utf-8"></script>
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