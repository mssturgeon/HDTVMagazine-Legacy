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
		AND e.entry_id = 5090";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Ken Werner" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5090 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Ken Werner'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Ken Werner" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5090 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5090";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2013/04/hdtv-expert-samsung-features-smart-tv-and-kate-upton-both-impressive-at-new-york-line-show.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5090";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Samsung features Smart TV and Kate Upton &mdash; both impressive &mdash; at New York Line Show" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Samsung features Smart TV and Kate Upton &mdash; both impressive &mdash; at New York Line Show" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Samsung features Smart TV and Kate Upton &mdash; both impressive &mdash; at New York Line Show" />
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
	<title>HDTV Magazine - HDTV Expert - Samsung features Smart TV and Kate Upton &mdash; both impressive &mdash; at New York Line Show</title>
	<meta name="keywords" content="ken werner, photo ken, video processing, new york, inch uhd, samsung, set, smart, photo, ken, werner, uhd, system, inch, new, processing, cells, video, local, sets, line, plasma, dimming, watt, technology" />
	<meta name="description" content="On March 20, Samsung held its New York line show at the Museum of American Finance on Wall&amp;Acirc;&amp;nbsp;Street. The event featured Samsung&amp;amp;#8217;s new line of smart TVs and three celebrities &amp;amp;#8212; model Kate&amp;Acirc;&amp;nbsp;Upton, Eli Manning, and rapper Flo Rida &amp;amp;#8212; and it was hard to tell whether many of the media&amp;Acirc;&amp;nbsp;types were more interested in [...]" />
	<meta name="title" content="HDTV Expert - Samsung features Smart TV and Kate Upton &amp;mdash; both impressive &amp;mdash; at New York Line Show" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Samsung features Smart TV and Kate Upton &amp;mdash; both impressive &amp;mdash; at New York Line Show" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2013/04/hdtv-expert-samsung-features-smart-tv-and-kate-upton-both-impressive-at-new-york-line-show.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="On March 20, Samsung held its New York line show at the Museum of American Finance on Wall&amp;Acirc;&amp;nbsp;Street. The event featured Samsung&amp;amp;#8217;s new line of smart TVs and three celebrities &amp;amp;#8212; model Kate&amp;Acirc;&amp;nbsp;Upton, Eli Manning, and rapper Flo Rida &amp;amp;#8212; and it was hard to tell whether many of the media&amp;Acirc;&amp;nbsp;types were more interested in [...]" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5090', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2013/04/hdtv-expert-samsung-features-smart-tv-and-kate-upton-both-impressive-at-new-york-line-show.php">HDTV Expert - Samsung features Smart TV and Kate Upton &mdash; both impressive &mdash; at New York Line Show</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Ken Werner</b> on <b>April  1, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=479&category=HTPCs & Laptops">HTPCs & Laptops</a></b>
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
				<p><span style="font-size: 13px; line-height: 19px;">On March 20, Samsung held its New York line show at the Museum of American Finance on Wall </span>Street. The event featured Samsung&#8217;s new line of smart TVs and three celebrities &#8212; model Kate <span style="font-size: 13px; line-height: 19px;">Upton, Eli Manning, and rapper Flo Rida &#8212; and it was hard to tell whether many of the media </span>types were more interested in the TV sets or the celebs. Of the celebrities, who drew the most attention? Clearly, it was the comely Miss Upton, especially for the guys carrying pro-level cameras.</p>
<div id="attachment_3051" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3051" rel="attachment wp-att-3051"><img class=" wp-image-3051 " alt="Samsung's New York Line Show was lavish and well produced.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/Samsung-teleprompter.jpg" width="600" height="397" /></a><p class="wp-caption-text">Samsung&#8217;s New York Line Show was lavish and well produced. (Photo: Ken Werner)</p></div>
<div id="attachment_3052" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3052" rel="attachment wp-att-3052"><img class="size-full wp-image-3052" alt="Model Kate Upton and Samsung Electronics America EVP Joe Stinziano" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/Kate-Upton-and-Joe-Stinziano.jpg" width="600" height="460" /></a><p class="wp-caption-text">Model Kate Upton and Samsung Electronics America EVP Joe Stinziano (Photo:  Ken Werner)</p></div>
<p>Despite the temptation, I kept my focus on the technology.</p>
<p>Although Samsung was emphasizing Smart TV, the biggest attention-grabber was the company&#8217;s new, very high-end, 85-inch UHD-TV. Apparently figuring that LG and Sony had underpriced their 84-inch UHD-TV offerings (at $20 thousand to $25 thousand), Samsung&#8217;s MSRP is $39,999. If you can get past the fact that this big TV costs as much as a small BMW, you will appreciate the beautiful image and the advanced technology.</p>
<div id="attachment_3045" class="wp-caption alignleft" style="width: 380px"><a href="http://www.hdtvexpert.com/?attachment_id=3045" rel="attachment wp-att-3045"><img class=" wp-image-3045" alt="Samsung UHDTV lo res" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/Samsung-UHDTV-lo-res.jpg" width="370" height="282" /></a><p class="wp-caption-text">Native 4Kx2K images on Samsung&#8217;s 85-inch Ultra High Def TV were beautiful. (Photo: Ken Werner)</p></div>
<div id="attachment_3044" class="wp-caption alignright" style="width: 392px"><a href="http://www.hdtvexpert.com/?attachment_id=3044" rel="attachment wp-att-3044"><img class=" wp-image-3044   " alt="Samsung UHDTV detail lores" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/Samsung-UHDTV-detail-lores.jpg" width="382" height="252" /></a><p class="wp-caption-text">A relatively small area of the UHD-TV screen. (Photo: Ken Werner)</p></div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>Samsung National Trainer Jesse Rowe fielded overlapping questions from several press people, and did it knowledgeably and with good humor. The LCD panel for the set is made on one of Samsung&#8217;s Gen 8 panel-manufacturing lines. The finished TV &#8220;floats in its frame,&#8221; which is Samsung-speak for the user being able to move the screen vertically in its frame-like stand, as well as being able to tilt it.</p>
<p>As with all of the company&#8217;s premium smart TVs, this 9000 Series UHD-TV has all of the Smart TV bells and whistles, including voice control, gesture control, facial recognition (so the TV will present your preferences intstead of your significant other&#8217;s) and a touch panel on the remote control. When you first turn on the TV, you see the &#8220;Smart Hub&#8221; by default, which provides access to five different file folders of content, which Samsung calls &#8220;panels.&#8221; The five panels are Social (social media), Apps (such as Netflix, Pandora, TED, Fitness, etc.), On TV (live TV shows and movies), Movies &amp; TV Shows (from streaming services), and Photos, Videos, &amp; Music (your own media stored on your network). There are different ways of exploring each panel.</p>
<p>&#8220;S-Recommendation&#8221; learns your viewing habits over time and offers customized recommendations that span all the panels and present the choices independent of their source. With voice control, you can tell the set how to narrow down the options. &#8221;Smart View&#8221; can push content to the TV from your tablet, smart phone, smart camera, etc.</p>
<p>All sets at the 7500 model level or above make use of the Evolution+ kit. Each set can be upgraded to the specifications of subsequent models for four years after manufacturing date by buying a $299 module that replaces the module on the back of the set. The module contains hardware as well as firmware updates. For instance, the module that updates 2012 sets to 2013 specifications contains the new quad-core processor. The kit also contains the latest touch remote control.</p>
<div id="attachment_3046" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3046" rel="attachment wp-att-3046"><img class="size-full wp-image-3046" alt="The 84-inch UHD-TV performs video processing in 720 different cells.  Here are the cells and the local-area-dimming backlight.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/UHDTV-processing-cells-cropped-lores.jpg" width="600" height="359" /></a><p class="wp-caption-text">The 84-inch UHD-TV performs video processing in 720 different cells. Here are the cells and the local-area-dimming backlight. (Photo: Ken Werner)</p></div>
<div id="attachment_3047" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3047" rel="attachment wp-att-3047"><img class="size-full wp-image-3047" alt="UHDTV processing" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/UHDTV-processing.jpg" width="600" height="462" /></a><p class="wp-caption-text">Even in this drastically pixel-reduced version of the original photo, you can still some of the effects of Samsung&#8217;s video processing. (Photo: Ken Werner)</p></div>
<p style="text-align: left;">Let&#8217;s return to some of the specific features of the UHD-TV set. It has 2GB of memory for app storage and storing downloads, and, significantly, has a full-array backlight with local area dimming &#8212; which Samsung calls &#8220;Precision Black Dimming.&#8221; The UHD-TV set does three stages of video processing within 720 cells based on local color, contrast, and detail, and combines it with the local area dimming. (Smaller non-UHD sets use a smaller number of cells, with the cell size being roughly the same independent of screen size. With these other sets, the number of levels of video processing depends on where the set sits in the model line-up.</p>
<p>FHD premium sets have edge backlighting (or edge-lighting) and do local dimming of the LEDs in the edge-light. The F8000 level is the only FHD LCD-TV with all three levels of dimming.  Note: Samsung was not stating the number of cells used in the video processing, but <span style="font-size: 13px; line-height: 19px;">had no objection to my taking a photo and counting. Regardless, the video processing was </span>demonstrated in detail on the UHD-TV set, and it does its job very well. Native 4K static images were beautiful, and 2K to 4K up-conversion was impressive. Pre-orders for the set are being taken now, and the set will be in 30 specially selected retail locations by the end May.  A 110-inch version will be available late this year, Rowe said.</p>
<p>Samsung is still making plasma TVs, and the premium F8500 uses a &#8220;Deep Black Algorithm&#8221; to deliver impressive black levels. The set contains a quad-core processer, native support for HEVC-encoded content, and all of the Smart TV features. The set produces excellent images.</p>
<p>I&#8217;m a great fan of plasma television technology, but the handwriting is on the wall. Plasma TV market share is in the single digits and falling. It is just a matter of time before manufacturers will not be able to sell enough units to justify the cost of running their factories. But that time is not yet, at least as far as Samsung is concerned. A company rep told me, &#8220;Samsung does not see any end to plasma at this time.&#8221; Samsung, he said, sees two robust markets for plasma: 1) videophiles, and 2) people looking for the largest screen they can get for their dollar.</p>
<div id="attachment_3049" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3049" rel="attachment wp-att-3049"><img class="size-full wp-image-3049" alt="Samsung's HT-F6500W 1000-watt, 5.1-channel home theater system with vacuum-tube pre-amp.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/Samsung-5.1-cropped-lores.jpg" width="600" height="468" /></a><p class="wp-caption-text">Samsung&#8217;s HT-F6500W 1000-watt, 5.1-channel home theater system with vacuum-tube pre-amp. (Photo: Ken Werner)</p></div>
<p>Samsung was also showing some high-end audio products. A wireless audio system and dock (for both iOS and Galaxy devices); a 2.1-channel, 310-watt soundbar; and a 5.1-channel, 1000-watt home-theater system (with access to Samsung&#8217;s smart TV system through the integrated Blu-ray player) were interesting primarily for their vacuum-tube pre-amps. The tubes were used as a prominent feature of the industrial design, as well as for their purported improvements to the audio quality. (The tube-vs.-transistor debate has been going on for decades and is not likely to stop anytime soon.)</p>
<div id="attachment_3050" class="wp-caption aligncenter" style="width: 610px"><a href="http://www.hdtvexpert.com/?attachment_id=3050" rel="attachment wp-att-3050"><img class="size-full wp-image-3050" alt="The vacuum tubes in the pre-amp are used as a prominent component of the industrial design.  (Photo:  Ken Werner)" src="http://www.hdtvexpert.com/wp-content/uploads/2013/04/Samsung-pre-amp-cropped.jpg" width="600" height="459" /></a><p class="wp-caption-text">The vacuum tubes in the pre-amp are used as a prominent component of the industrial design. (Photo: Ken Werner)</p></div>
<p>Finally, there was the MX-FS9000, 2560-watt Giga Speaker system with dual 15-inch sub-woofers and lighting effects suitable for the Starship Enterprise entering warp drive &#8212; or maybe for DJs. (Promoting the speakers was Flo Rida&#8217;s part of the program.) The system has Bluetooth connectivity. The idea of 2560 watts from a teenager&#8217;s iPhone is truly frightening.</p>
<p>&nbsp;<br />
<em>Ken Werner is Principal of Nutmeg Consultants, specializing in the display industry, display </em><em>manufacturing, display technology, and display applications. You can reach him at </em><em>ken@hdtvexpert.com.</em></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Ken Werner</b>, <b>April  1, 2013  9:37 PM</b>
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
			<?=getComments(5090)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Ken Werner', 5090)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Ken Werner</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2013/04/hdtv-expert-samsung-features-smart-tv-and-kate-upton-both-impressive-at-new-york-line-show.php" type="text/javascript" charset="utf-8"></script>
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