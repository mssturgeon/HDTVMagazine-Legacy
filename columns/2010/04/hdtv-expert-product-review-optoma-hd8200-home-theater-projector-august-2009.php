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
		AND e.entry_id = 3732";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Pete Putman" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3732 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Pete Putman'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Pete Putman" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3732 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 3732";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-optoma-hd8200-home-theater-projector-august-2009.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (10) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3732";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)" />
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
	<title>HDTV Magazine - HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)</title>
	<meta name="keywords" content="black levels, auto iris, chip dlp, single chip, dynamic range, ’s, color, lens, mode, optoma, –, black, gamma, projector, video, image, dlp, lcd, using, ”, screen, any, figure, levels, projectors" />
	<meta name="description" content="Here’s another sleek, piano-black 1080p home theater projector with a long throw lens, horizontal and vertical lens offset, and dynamic iris. The difference? It uses single-chip DLP technology, and is priced at $5,000. Surprised?" />
	<meta name="title" content="HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-optoma-hd8200-home-theater-projector-august-2009.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Here’s another sleek, piano-black 1080p home theater projector with a long throw lens, horizontal and vertical lens offset, and dynamic iris. The difference? It uses single-chip DLP technology, and is priced at $5,000. Surprised?" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3732', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-optoma-hd8200-home-theater-projector-august-2009.php">HDTV Expert - Product Review: Optoma HD8200 Home Theater Projector (August 2009)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>April  8, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=501&category=DLP HDTVs">DLP HDTVs</a></b>, <b><a href="/category.php?id=497&category=Front Projection">Front Projection</a></b>, <b><a href="/category.php?id=448&category=PC & Laptop Technology">PC & Laptop Technology</a></b>
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
				<div class="art-PostContent">&#13;
&#13;
          <p>It’s funny how the fortunes of competing projection technologies have swung wildly over the past decade. Back at the turn of the century, most industry analysts (including myself) figured that Texas Instruments’ DLP technology had pretty much won the hearts and minds of CEDIA dealers, and that 3LCD didn’t stand a chance. LCoS? It was certainly out there, but mostly on the fringe.</p>
<p>Well, we sure got that one wrong. Three years ago, Mitsubishi pulled the rug out from under the DLP crowd with its eye-popping 3LCD HC5000, priced at $4,495 and completely upstaging new LCoS projector announcements from JVC and Sony. Epson and Panasonic also unveiled lower-price 3LCD chassis’ with great color, deep blacks, and plenty of contrast for similarly low prices.</p>
<p>Since then, 3LCD technology has taken mighty leaps forward, incorporating manual lens offset, dynamic irising, and improved black levels to become a can’t-miss value proposition. On the LCoS side of things, JVC’s DLA-series projectors are now the favorite of many prominent home theater enthusiasts and reviewers. So what’s happened to the DLP crowd?</p>
<p>One of the limitations with using single-chip DLP light engines is the difficulty in adding mechanical lens offset. Many early DLP lightboxes had a fixed lens offset and were intended for ceiling installation. But that severely constricted the installer’s choices when adding a projection system to an existing room, something the 3LCD and D-ILA camps were quick to point out.</p>
<p>Optoma, the US branding arm of Coretronics, is a leader in sales of DLP projectors for both consumer and professional use. They’re had a few previous entries into the CEDIA channel that have done well, but the long-throw zoom lens issue had to be sticking in their craw.</p>
<p>So they did the smart thing by not getting mad, but trying to get even. And the HD8200 is all about “getting even,” leveling the playing field with 3LCD and LCoS projectors in design, functionality, and hopefully, performance.</p>
<h1>
<div id="attachment_441" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/HD8200-3-4-Figure-1.jpg"><img class="size-full wp-image-441" title="HD8200 3-4 Figure 1" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/HD8200-3-4-Figure-1.jpg" alt="" width="600" height="341" /></a><p class="wp-caption-text">Figure 1. Now, here’s a different look for an Optoma projector!</p></div></h1>
<p>OUT OF THE BOX</p>
<p>The first thing that strikes you about the HD8200 is how much it looks like JVC’s DLA-series projectors, from the long, rectangular cabinet with smooth curves to the rich, gloss black finish, the lack of nomenclature around the housing, and the minimalist video input panel. It’s all about the quality of images, and not appearances.</p>
<p>As supplied, the HD8200 is fitted with a 1.5 – 2:1 manual zoom lens, and veteran projectionists know that longer lenses usually mean less problems with pincushioning, barreling, and other optical distortions. That in turn makes aligning the projected image to a screen a much easier task. And the longer lens provides more mounting distance options.</p>
<p>Of course, longer lenses also mean optically smaller lens apertures and dimmer images, unless a lamp with more horsepower is included. So Optoma has included a hefty 220W UHP lamp that can run in two modes – standard and bright. They’ve rated lamp life to half-brightness at 3000 hours in the first mode, and 2000 in the second.</p>
<p>The imaging engine uses a DarkChip3 DMD, combined with a Pixelworks PW9800 co-processor with DNX MotionEngine. Optoma claims the HD8200 uses 10-bit signal processing to correct for both motion judder and when deinterlacing and compensating 480i and 1080i content.</p>
<p>When it comes to input connections, you basically get one of everything – one composite, one S-video, and one analog component (YPbPr) input, plus one 15-pin RGB/SCART connector, and one DVI-D jack. The exception? Optoma has provided a pair of HDMI v1.3 input jacks and labeled them as being compatible with Deep Color spaces, a color gamut that no one currently uses for HD TV shows and movies.</p>
<p><strong><em> </em></strong></p>
<p><strong><em></em></strong></p>
<p></p><div id="attachment_442" class="wp-caption aligncenter" style="width: 610px"><strong><em><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/HD8200-Rear-Figure-2.jpg"><img class="size-full wp-image-442" title="HD8200 Rear Figure 2" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/HD8200-Rear-Figure-2.jpg" alt="" width="600" height="315" /></a></em></strong><p class="wp-caption-text">Figure 2. You don’t get many inputs – and you won’t need them. (But there’s still a composite video jack!)</p></div>
<p></p>
<p>REMOTE AND MENUS</p>
<p>The supplied remote control is also a departure from previous Optoma designs. It’s not all that large, but is very user-friendly with large, backlit buttons. Optoma has thoughtfully provided direct access to many menu adjustments, including brightness, contrast, lamp bright mode, digital image shift, aspect ratios, overscan, and edge masking.</p>
<p>You’ll also have direct access to any input, and you can set up the HD8200 to automatically detect active inputs or skip inactive ones. A pair of 12VDC screen triggers is yours for the asking on the IO panel, and you can operate a motorized screen directly from the remote with Screen Up and Down keys.</p>
<p>The operation and image adjust menus aren’t overly detailed, but get you to the critical adjustments quickly. Optoma has provided four factory image presets, labeled as Cinema, Bright, Photo, and Reference. There’s also a User selection, although you can recalibrate any of the settings for any preset.</p>
<p>In addition to basic image tweaks, you’ll find an Advanced menu that really lets you get to the nitty-gritty adjustments. There’s a ten-step motion adaptive noise reduction setting that’s intended to be use with interlaced content – separating noise from interlaced artifacts in 480i and 1080i content is a tough job, and you may find this control helpful in doing so.</p>
<p>Gamma is selectable over four presets – Film, Video, Graphics, and Standard. Note that these are all factory presets, which means you can’t go into a multi-step gamma adjustment menu and fine-tune RGB response as you can on JVC’s DLA-series projectors.</p>
<p>You’ll also find a black/white extension setting that’s ostensibly used to enhance contrast. Be careful – these settings usually play with gamma curves, often resulting in an unwanted S-shaped response (I’d suggest leaving this switched off).</p>
<p>There are three factory color temperature settings (Cold, Medium and Warm) that you can readjust, using the supplied red, green, and blue contrast (high) and brightness (low) controls. You’ll also spot a Dynamic Black mode in this menu, and it’s used to enhance deep shadow detail in low-level scenes. Again, caution is in order, as dynamic black enhancements will have an adverse effect on the projector’s gamma response.</p>
<p>In the press releases for the HD8200, Optoma made a lot of noise about its PureEngine imaging technology. (Shades of Pioneer plasma TVs!) The “pure” part has a few components to it, specifically PureDetail (multi-level selectable edge enhancement), PureColor (a color-enhancement mode that stretches the projector’s gamut), and PureMotion (affects 24p content transferred 3:2 to 480i, 720p, and 1080i formats).</p>
<p>Edge enhancement can make a difference with lower-resolution analog content, although it could also enhance unwanted compression artifacts from digital SD video sources. I’d avoid using this control at all with 720p, 1080i, and 1080p sources. I’d also leave PureColor off and stick to matching the color space in which the TV show or movie was encoded. (As you’ll see shortly, the HD8200 does a good job already matching up to the ITU BT.709 HD color space.)</p>
<p>PureMotion may be the most useful gadget of the three, particularly when correcting for 24p “judder.”  If you’ve never seen a judder-correction processor at work, it can be a revelation as the “film look” gives way to a live video feel. Is this right or wrong? Well, some folks like it, and some purists don’t. You’ll have to experiment on your own to see which settings work for you.</p>
<p>As far as aspect ratios go, the HD8200 lets you select among 4:3, 16:9, Native (no image scaling at all), or LBX – short for “letterbox.” LBX mode lets you watch CinemaScope movies on a 2.35:1 screen with a companion anamorphic lens. According to the owner’s manual, LBX mode is also suitable for a <em>“…non-16×9 letterbox source.”</em></p>
<p>Additional image tweaks include Overscan (eliminates noise and digital sync from appearing on certain TV channels), Edge Mask (basically a digital zoom function and not a left/right/top/bottom masking system), Vertical Image Shift (digital), and digital keystone correction.</p>
<p>My advice is to stay away from any digital image shift functions and instead use the H and V offset controls, large thumbwheels that are mounted under the lens along with the manual zoom adjustment. You’ll be able to shift images horizontally by ±15% and vertically by ±50%, which is quite a wide range for a single-chip DLP projector.</p>
<p>One last image adjustment bears mention. It’s called SuperWide, and requires the use of a 2.0:1 aspect ratio projection screen. With SuperWide on, both 16:9 and 2.35:1 programs will be displayed without any black bars. Of course, there is a slight amount of anamorphic stretching and compression in effect to pull this off, and that may go against your “purist” instincts.</p>
<p>There are a couple of useful tools in the operations menu. Not much mention is made of it, but the HD8200 has a two-position auto irising system to lower black levels, based on the average brightness of individual scenes. If you are familiar with auto iris systems, you know that they reduce brightness as well as deepen black levels, so I’d experiment with this setting to see if you can live with the results.</p>
<p>The other useful tool is Screen Trigger B, which can be configured to activate an external anamorphic lens assembly when 2.35:1 movies are being displayed. It can also be set to activate in 4:3, 16:9, Native, or LBX modes, although the utility of those selections isn’t as obvious to me as the anamorphic lens trigger.</p>
<div id="attachment_443" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-3-Review.jpg"><img class="size-full wp-image-443" title="Figure 3 Review" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-3-Review.jpg" alt="" width="600" height="321" /></a><p class="wp-caption-text">Figure 3. The HD8200’s gamma performance was most consistent in Standard gamma mode.</p></div>
<div id="attachment_444" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4-Review.jpg"><img class="size-full wp-image-444" title="Figure 4 Review" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-4-Review.jpg" alt="" width="600" height="321" /></a><p class="wp-caption-text">Figure 4. Once above 20 IRE, the HD8200 tracked an incredibly tight grayscale.</p></div>
<p>ON THE TEST BENCH</p>
<p>So much for menus and features! How did the HD8200 do under fire? Not, bad, although there are a few areas where this projector could use further improvement.</p>
<p>I calibrated the HD8200 to light up a new, 92-inch Da-Lite JKP Affinity front screen (gain = .9) at a distance of 12 feet. After going through the menu to make sure all contrast, white level, and black level enhancements were switched off and that the auto iris was disabled, I adjusted the projector for best dynamic range and most accurate color rendering, using an AccuPel HDG4000 pattern generator and ColorFacts 7.5 software, plus a Minolta CL-200 colorimeter.</p>
<p>After calibration, I measured brightness at 364 ANSI lumens in Cinema mode. Readings in Bright, Photo, and Reference modes were 478, 468, and 449 ANSI lumens, respectively. Note that these were all taken with the projector’s lamp operating in standard mode – switching to bright mode results in a boost in lumens of about 15%.</p>
<p>Brightness uniformity calculated to 91% to the average corner, and 76% to the worst corner. These are excellent numbers for any single-chip DLP projector, some models of which have exhibited a 50% fall-off to the worst corner and noticeable hot spots in my tests.</p>
<p>Contrast measurements were comparable to some of the better 3LCD long-throw projectors I’ve tested, clocking at 559:1 ANSI (average) and 873:1 peak in Cinema mode. Black levels on this projector are higher than the best 3LCD and LCoS models – not substantially, but you can see a difference with low-light program material. The auto iris, disabled for this test, does improve blacks when active but also brings down white levels a corresponding amount.</p>
<p>Using the factory settings, I measured gamma response in Video mode at 1.82. That’s too shallow for video, and in fact the upper end of the grayscale was starting to flatten out at 80 IRE. Ironically, the projector’s Graphics gamma (measured at 2.21) was closer to ideal for video, except that this setting was also starting to flatline at 80 IRE.</p>
<p>Using a calibrated setting, I found the best gamma response (2.29) using the Standard gamma setting, resulting in a consistent climb out of black and not clipping at the high end. I also found this gamma curve provided me with the most consistent grayscale track, as seen in Figure 3.</p>
<p>Figure 4 shows the resulting grayscale track from 20 to 100 IRE. Maintaining a stable, consistent color of gray is a consistent attribute of the best DLP projectors, since the imaging devices have no inherent color bias. As you can see, the measured color temperature was consistent, varying by just 140 degrees in User mode and by 229 degrees in Cinema mode. That’s reference-grade performance!</p>
<p>I mentioned the HD8200’s color gamut earlier. As seen in Figure 5, it’s enough to cover 100% of the BT.709 standard, although the green and red pints are oversaturated and the cyan and magenta coordinates are shifted towards blue. Color management tools would help clean these up – the percentage of coordinate shift required isn’t enormous.</p>
<div id="attachment_445" class="wp-caption aligncenter" style="width: 581px"><a href="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-5-Review.jpg"><img class="size-full wp-image-445" title="Figure 5 Review" src="http://www.hdtvexpert.com/wp-content/uploads/2010/04/Figure-5-Review.jpg" alt="" width="571" height="642" /></a><p class="wp-caption-text">Figure 5. The projector’s color gamut is large enough to cover BT.709. Color management tools would lock it in even closer.</p></div>
<p>IMAGE QUALITY</p>
<p>For this part of the test, I cued up a few Blu-ray discs on OPPO’s new BDP-83 player. The BBC’s <strong><em>Planet Earth</em></strong> has some great scenes for evaluating dynamic range, specifically <strong><em>Ice Worlds</em></strong> and <strong><em>Oceans Deep</em></strong>. Ice Worlds has clips with lots of different shades of “white,” something that will reveal subtle changes in color temperature and whether any white clipping is going on.</p>
<p>Image contrast and detail was excellent with these clips, although it appeared that blacks and low grays could have been deeper. Color saturation appeared normal, particularly with close-ups of monkeys, leopards, and eagles that were captured with the sun at a low angle. That could have resulted in exaggerated reds and warm tones, but it didn’t.</p>
<p>My next test was with the director’s cut of <strong><em>Ghost Rider</em></strong>, an exceptionally detailed and contrasty transfer on Blu-ray. This is a great BD to test out dynamic range performance, particularly with the nighttime confrontation between the police and the Rider as he roars up and down the Longhorn Insurance Company skyscraper, spewing orange flames in his wake. (Come to think of it, there’s a lot of blue and orange shading in this film…wonder if the director or DP was a Syracuse or Florida graduate?)</p>
<p>The earlier scene where Johnny Blaze leaps over six helicopters on his motorcycle has some great punchy reds, oranges, and yellows. Flesh tones in these scenes could have easily been overpowered, but weren’t. At times, I thought I saw an ever-so-slight slight magenta tint to flesh tones, but that may just have been the transfer as I also observed this watching the same clip on a 50” Panasonic plasma monitor.</p>
<p>Once again, it seemed like the blacks weren’t quite deep enough, particularly in the final confrontation in the abandoned church between Wes Bentley and Nicolas Cage. Turning on the auto iris circuit pushed blacks down a lot more, but didn’t help shadow detail. I could have enhanced black levels to recover the detail, but would have lost the clean gamma curve I originally plotted.</p>
<p>The HD8200’s PureMotion processor sure does work! You can apply a high level of processing and basically eliminate all 24p film judder from any movie, making it look more like live 60 Hz video. So I repeat – is that good, or bad? Some viewers will no doubt love it; others will surely rail against it. As for myself, a little bit of judder reduction is nice, but I don’t go for the “video look” when watching a movie.</p>
<p>That Pixelworks processor does an excellent job with interlaced content. The HD8200 had no trouble whatsoever with the video and film resolution loss tests from the Realta Blu-ray disc. However, I should mention that a quick test of frequency response, using a 1080p luminance multiburst pattern, showed some filling at 37.5 MHz. That would result in the loss of very fine picture detail, and it’s another thing Optoma may want to look at.</p>
<p>CONCLUSIONS</p>
<p>Optoma’s HD8200 does indeed break new ground and should help single-chip DLP technology recover much of the ground it has lost to 3LCD and LCoS projectors. The projector delivers sharp, contrasty images with good color saturation and great dynamic range, albeit with slightly higher black levels than the best LCoS/LCD designs.</p>
<p>Improving black levels could simply be a matter of refining the optical path to cut down on refracted light, and also using a projection lens with improved coatings. The auto iris is certainly fast, but not fast enough on some scenes – you’re better off leaving it disengaged more often than not. I do recommend using a gray screen with the HD8200 for best results, particularly if there is light reflecting around your theater environment.</p>
<p>But my hat’s off to Optoma for building in mechanical lens shift and a longer zoom lens at this price point. I would have a hard time justifying spending more money for any other single-chip DLP projector after seeing the HD8200 in action. Down the road, how about adding multi-level RGBW gamma correction and color management tools to the menu? Now, that would be a hot product!</p>
<p><strong>Optoma HD8200 Home Theater Projector<br />
MSRP: $4,999</strong></p>
<p><strong>Specifications:</strong></p>
<p><strong><br /></strong>Dimensions: 14.6” W x 7.6” H x 19.2” D (projector)<br />
Weight: 18.5 lbs. (projector)<br />
Imaging Device: 1x .65” DarkChip3 1920×1080 DMD<br />
Lamp: 220W UHP<br />
Lens: 1.49 – 2.0:1 manual zoom/focus<br />
Inputs: 1x each composite/S-video, 1x RCA YPbPr, 15p VGA, 2x HDMI 1.3</p>
<p>Signal compatibility: 480i/p, 720p, 1080i, 1080p24/60, VGA-SXGA+, WXGA, HD</p>
<p>Available from:</p>
<p><strong>Optoma Technology Inc.</strong><strong><br /></strong>715 Sycamore Drive<br />
Milpitas, CA 95035<br />
408-383-3700<br /><a href="http://www.optomausa.com/" onclick="javascript:pageTracker._trackPageview('/outbound/article/www.optomausa.com');"><strong>http://www.optomausa.com/</strong></a></p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>April  8, 2010  1:11 PM</b>
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
			<?=getComments(3732)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 3732)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Pete Putman</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2010/04/hdtv-expert-product-review-optoma-hd8200-home-theater-projector-august-2009.php" type="text/javascript" charset="utf-8"></script>
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