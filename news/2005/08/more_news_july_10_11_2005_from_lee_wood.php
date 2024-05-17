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
		AND e.entry_id = 179";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 179 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 179 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 179";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2005/08/more-news-july-10-11-2005-from-lee-wood.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 179";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download More News July 10 - 11, 2005 from Lee Wood" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="More News July 10 - 11, 2005 from Lee Wood" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="More News July 10 - 11, 2005 from Lee Wood" />
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
	<title>HDTV Magazine - More News July 10 - 11, 2005 from Lee Wood</title>
	<meta name="keywords" content="yahoo news, high definition, biz yahoo, articles viewarticle, business wire, news, digital, yahoo, new, hdtv, television, articles, film, high, article, biz, technology, index, definition, home, world, business, viewarticle, event, broadcasting" />
	<meta name="description" content="Kagan Forecasts 50% of TV Households Will Have Digital TV by 2007 (Business Wire / Yahoo News) http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&amp;newsId=20050809006064&amp;newsLang=en http://biz.yahoo.com/bw/050809/96064.html?.v=1 Date Extended for Filing Conflict Decision Form 383 (TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=961 According to In-Stat Digital Terrestrial TV and Free-to-Air Satellite Services..." />
	<meta name="title" content="More News July 10 - 11, 2005 from Lee Wood" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="More News July 10 - 11, 2005 from Lee Wood" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2005/08/more-news-july-10-11-2005-from-lee-wood.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Kagan Forecasts 50% of TV Households Will Have Digital TV by 2007 (Business Wire / Yahoo News) http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&amp;newsId=20050809006064&amp;newsLang=en http://biz.yahoo.com/bw/050809/96064.html?.v=1 Date Extended for Filing Conflict Decision Form 383 (TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=961 According to In-Stat Digital Terrestrial TV and Free-to-Air Satellite Services..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=179', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/08/more-news-july-10-11-2005-from-lee-wood.php">More News July 10 - 11, 2005 from Lee Wood</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>August 11, 2005</b>
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
				<p><strong>Kagan Forecasts 50% of TV Households Will Have Digital TV by 2007</strong><br />
(Business Wire / Yahoo News)</p>

<p><a href="http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050809006064&newsLang=en">http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050809006064&newsLang=en</a><br />
<a href="http://biz.yahoo.com/bw/050809/96064.html?.v=1">http://biz.yahoo.com/bw/050809/96064.html?.v=1</a><br />
 </p>

<p><strong>Date Extended for Filing Conflict Decision Form 383</strong><br />
(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/dlrf/one.php?id=961">http://www.tvtechnology.com/dlrf/one.php?id=961</a><br />
 </p>

<p><strong>According to In-Stat Digital Terrestrial TV and Free-to-Air Satellite Services to Drive PC-TV Tuners</strong><br />
(Business Wire via Yahoo News)</p>

<p><a href="http://biz.yahoo.com/bw/050810/105081.html?.v=1">http://biz.yahoo.com/bw/050810/105081.html?.v=1</a><br />
 </p>

<p><strong>Gamers Watch Less TV -- But More HDTV</strong><br />
Ziff Davis study of video game players finds that they are spending increasingly little time in front of the tube.</p>

<p>(TVPredictions.com)</p>

<p><a href="http://www.tvpredictions.com/gamershdtv080905.htm">http://www.tvpredictions.com/gamershdtv080905.htm</a><br />
 </p>

<p><strong>Prices for flat-panel televisions drop sharply in 2Q05</strong><br />
PMA sell-through research finds flat-panel TV prices plummet as DLP&trade; and 3LCD RPTVs strike out for market share</p>

<p>(Infocomm.org)</p>

<p><a href="http://www.infocomm.org/index.cfm?objectID=AE80DD84-DA4F-4C77-AC2BB015DB80C9BB">http://www.infocomm.org/index.cfm?objectID=AE80DD84-DA4F-4C77-AC2BB015DB80C9BB</a><br />
  <br />
<strong>MSTV Receives 'Impressive Response' to Terrestrial D-A Converter Box RFQ</strong><br />
(TV Technology)</p>

<p><a href="http://www.tvtechnology.com/dlrf/one.php?id=962">http://www.tvtechnology.com/dlrf/one.php?id=962</a><br />
 </p>

<p><strong>New HD Site Launched By DMN</strong><br />
HDIssues.com addresses the rapid developments in HD technology and content production</p>

<p>(Broadcast Newsroom / HDTV Buyer)</p>

<p><a href="http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34002">http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34002</a><br />
<a href="http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=34002">http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=34002</a><br />
 </p>

<p> </p>

<p><strong>Experience colour like no other at home with Sony  [UK]</strong><br />
(Digital TV Group)</p>

<p><a href="http://griffin.dtg.org.uk/news/news.php?class=PR&subclass=&id=1068">http://griffin.dtg.org.uk/news/news.php?class=PR&subclass=&id=1068</a><br />
 </p>

<p><strong>UK Gov Looking To Subsidise Digital TV Transition via BBC?  [UK]</strong><br />
(Digital Lifestyles)</p>

<p><a href="http://www.digital-lifestyles.info/display_page.asp?section=platforms&id=2467">http://www.digital-lifestyles.info/display_page.asp?section=platforms&id=2467</a><br />
 </p>

<p><strong>Spain allocates new DTT licences  [Spain]</strong><br />
(Advanced Television)</p>

<p><a href="http://www.advanced-television.com/2005/news_archive_2005/Aug8_Aug12.htm#spainallo">http://www.advanced-television.com/2005/news_archive_2005/Aug8_Aug12.htm#spainallo</a></p>

<p><strong>August 11, 2005</strong></p>

<p><strong>NAB Wants Sooner DTV-Tuner Mandate</strong><br />
The NAB is urging the FCC to accelerate mandates for inclusion of over-the-air digital tuners in new TV sets. </p>

<p>(Multichannel News)</p>

<p><a href="http://www.multichannel.com/article/CA634076.html?display=Breaking+News&referral=SUPP">http://www.multichannel.com/article/CA634076.html?display=Breaking+News&referral=SUPP</a><br />
 </p>

<p><strong>Wolfsson's Wednesday Words (Mark's Monday Memo)</strong><br />
(Digital Television)</p>

<p><a href="http://www.digitaltelevision.com/mondaymemo/mlist/frm02195.html">http://www.digitaltelevision.com/mondaymemo/mlist/frm02195.html</a><br />
 </p>

<p><strong>Nearly 90% of All US Households to Have Digital TV by 2009</strong><br />
eMarketer Report Explores the Technologies and Dynamics That Will Change Broadcasting, Communications and Advertising Forever </p>

<p>(Market Wire / Yahoo News)</p>

<p><a href="http://www.marketwire.com/mw/release_html_b1?release_id=92865">http://www.marketwire.com/mw/release_html_b1?release_id=92865</a><br />
http://biz.yahoo.com/iw/050811/092865.html</p>

<p> </p>

<p><strong>Is your TV set for digital?</strong><br />
(Canton, OH Repository)</p>

<p><a href="http://www.cantonrep.com/index.php?Category=5&ID=236869&r=0">http://www.cantonrep.com/index.php?Category=5&ID=236869&r=0</a><br />
 <br />
<strong>Television Stations</strong><br />
High definition television, with its promise of sharper, movie- theater quality pictures, has slowly been entering the Richmond area. </p>

<p>(RedNova)</p>

<p><a href="http://www.rednova.com/news/display/?id=204268&source=r_technology">http://www.rednova.com/news/display/?id=204268&source=r_technology</a><br />
 </p>

<p><strong>KARE 11 Launches First Local Twenty-Four Hour Digital Weather Service</strong></p>

<p>(PR Newswire / Yahoo News)</p>

<p><a href="http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/08-10-2005/0004086239&EDATE=">http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/08-10-2005/0004086239&EDATE=</a></p>

<p><a href="http://biz.yahoo.com/prnews/050810/cgw040.html?.v=20">http://biz.yahoo.com/prnews/050810/cgw040.html?.v=20</a><br />
 </p>

<p><strong>HDFEST 2005 World Tour to Launch</strong><br />
South Florida Film Festival event is first stop</p>

<p>(Broadcast Newsroom / HDTV Buyer / HD Issues)</p>

<p><a href="http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34030">http://www.broadcastnewsroom.com/articles/viewarticle.jsp?id=34030</a><br />
<a href="http://www.hdissues.com/articles/viewarticle.jsp?id=34030">http://www.hdissues.com/articles/viewarticle.jsp?id=34030</a><br />
http://www.hdtvbuyer.com/articles/viewarticle.jsp?id=34030</p>

<p> </p>

<p><strong>High Definition Tours World</strong><br />
HDFEST's "high-definition only" screenings will allow attendees the full experience of what high-definition and digital cinema currently have to offer audiences and will include a special selection of animation , documentaries, features and shorts.</p>

<p>(Digital Broadcasting)</p>

<p><a href="http://www.digitalbroadcasting.com/content/news/article.asp?DocID={3FA85319-778F-47DB-B998-ABED1897531C}&Bucket=Current+Headlines">http://www.digitalbroadcasting.com/content/news/article.asp?DocID={3FA85319-778F-47DB-B998-ABED1897531C}&Bucket=Current+Headlines</a><br />
 </p>

<p><strong>HDFEST 2005 World Tour Launches with South Florida Film Festival Event</strong><br />
<a href="http://">HDFEST will be launching its 2005 World Tour next month with a film festival event in South Florida September 9-10.</a></p>

<p>(Videography)</p>

<p><a href="http://www.videography.com/articles/article_13551.shtml">http://www.videography.com/articles/article_13551.shtml</a><br />
 </p>

<p> </p>

<p><strong>A Look Ahead at HDTV, Shot by You</strong><br />
(New York, NY Times)</p>

<p><a href="http://tech2.nytimes.com/2005/08/11/technology/circuits/11pogue.html">http://tech2.nytimes.com/2005/08/11/technology/circuits/11pogue.html</a><br />
 </p>

<p><strong>Australian Film Shot on JVC's GY-HD101E HD Camcorders</strong><br />
<a href="http://">The first film in the world to be shot with two JVC GY-HD101E ProHD cameras is now in production. The film entitled "Reality Check" stars Paul Mercurio (lead actor of Australian film, Strictly Ballroom) and Dieter Brenmar (actor in Australian television drama, Home and Away) is a kind of 'Survivor gone wrong' movie.</a><br />
(Videography)</p>

<p><a href="http://www.videography.com/articles/article_13553.shtml">http://www.videography.com/articles/article_13553.shtml</a><br />
 </p>

<p><strong>HDTV: Do your worst</strong><br />
(TV Squad)</p>

<p><a href="http://www.tvsquad.com/2005/08/10/hdtv-do-your-worst/">http://www.tvsquad.com/2005/08/10/hdtv-do-your-worst/</a><br />
 </p>

<p><strong>'Software guy' builds HDTV antennas out of frustration  [How Antennas Direct got started]</strong><br />
(St. Louis, MO Post-Dispatch)</p>

<p><a href="http://www.stltoday.com/stltoday/business/stories.nsf/story/09BE6D90BF616D338625705A000CC681?OpenDocument">http://www.stltoday.com/stltoday/business/stories.nsf/story/09BE6D90BF616D338625705A000CC681?OpenDocument</a></p>

<p> </p>

<p> </p>

<p><strong>Format fight </strong><br />
The peace talks are over - a war seems inevitable between the two formats competing to replace DVD to become the high definition (HDTV) pre-recorded/recordable disc standard.</p>

<p>(Guardian Weekly)</p>

<p><a href="http://www.guardian.co.uk/online/story/0,3605,1546157,00.html">http://www.guardian.co.uk/online/story/0,3605,1546157,00.html</a></p>

<p> </p>

<p><strong>DVD format war will rage for two years</strong><br />
Experts predict long battle for supremacy between Blu-ray and HD DVD</p>

<p>(vnunet.com)</p>

<p><a href="http://www.vnunet.com/vnunet/news/2140916/dvd-format-battle-gartner-two">http://www.vnunet.com/vnunet/news/2140916/dvd-format-battle-gartner-two</a><br />
 </p>

<p><strong>Consumers Reluctant To Buy High-Definition Media </strong></p>

<p>(TechWeb News via InternetWeek)</p>

<p><a href="http://www.internetweek.com/news/168600602">http://www.internetweek.com/news/168600602</a></p>

<p> </p>

<p> </p>

<p> </p>

<p><strong>JVC Commences Volume Production of New 0.7-Inch D-ILA Full HD Liquid Crystal Device</strong><br />
(PhysOrg)</p>

<p><a href="http://www.physorg.com/news5693.html">http://www.physorg.com/news5693.html</a><br />
 </p>

<p><strong>New Pioneer PureVision Plasma Televisions Make HDTV Look Picture Perfect</strong><br />
Enhanced Contrast Ratios Offer Better Black Level Than Ever Before </p>

<p>(Business Wire / Yahoo News / eCoustics.com)</p>

<p><a href="http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005669&newsLang=en">http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005669&newsLang=en</a><br />
http://biz.yahoo.com/bw/050810/105669.html?.v=1</p>

<p><a href="http://news.ecoustics.com/bbs/messages/10381/154055.html">http://news.ecoustics.com/bbs/messages/10381/154055.html</a><br />
 </p>

<p><strong>New Plasmas Set the Mark for Excellence in HDTV with Increased Contrast Ratio and Excellent Black Levels</strong><br />
(New Age Media Concepts)</p>

<p><a href="http://press.namct.com/content/view/2599/2/">http://press.namct.com/content/view/2599/2/</a><br />
 </p>

<p><strong>The Difference is Black and White with New Pioneer Elite PureVision Plasma Televisions</strong><br />
New Plasmas Set the Mark for Excellence in HDTV with Increased Contrast Ratio and Excellent Black Levels </p>

<p>(Business Wire / Yahoo News / eCoustics.com)</p>

<p><a href="http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005658&newsLang=en">http://home.businesswire.com/portal/site/google/index.jsp?ndmViewId=news_view&newsId=20050810005658&newsLang=en</a><br />
http://biz.yahoo.com/bw/050810/105658.html?.v=1</p>

<p><a href="http://news.ecoustics.com/bbs/messages/10381/154057.html">http://news.ecoustics.com/bbs/messages/10381/154057.html</a><br />
 </p>

<p><strong>Samsung's New Plasma TVs Target Specialty A/V Retailers With High-End Design</strong><br />
CES Innovations Award-Winning HP-R5072 Anchors New 72-Series, Which Offers 42", 50" and 63" Models Through Professional A/V Retail and Home Install Channels</p>

<p>(Widescreen Review)</p>

<p><a href="http://www.widescreenreview.com/news_detail.php?recid=10230">http://www.widescreenreview.com/news_detail.php?recid=10230</a><br />
 </p>

<p><br />
<strong>In-Stat: Digital TV services to drive PC-TV tuner sales</strong></p>

<p>(DigiTimes)</p>

<p><a href="http://www.digitimes.com/mobos/a20050811PR200.html">http://www.digitimes.com/mobos/a20050811PR200.html</a><br />
 </p>

<p><strong>Digital Terrestrial TV And Free-to-Air Satellite Services To Drive PC-TV Tuners</strong><br />
(Media Center PC World)</p>

<p><a href="http://www.mediacenterpcworld.com/news/479">http://www.mediacenterpcworld.com/news/479</a><br />
 </p>

<p><strong>TV on the PC Gets Real</strong></p>

<p>Time Warner launches trial in San Diego</p>

<p>(Broadcasting & Cable)</p>

<p><a href="http://www.broadcastingcable.com/article/CA632697.html?display=Feature&referral=SUPP">http://www.broadcastingcable.com/article/CA632697.html?display=Feature&referral=SUPP</a><br />
 </p>

<p><strong>DTV: Video for nothing and your flicks for free</strong><br />
(CNET Crave)</p>

<p><a href="http://crave.cnet.co.uk/software/0,39029471,39191476,00.htm">http://crave.cnet.co.uk/software/0,39029471,39191476,00.htm</a><br />
 </p>

<p> </p>

<p><strong>Live Mobile TV Broadcasts Of International Sporting Event</strong><br />
DVB-H technology allows television channels to be distributed effectively to users of mobile devices. All television channels and special event channels can be accessed by viewers as live broadcasts.</p>

<p>(Digital Broadcasting)</p>

<p><a href="http://www.digitalbroadcasting.com/content/news/article.asp?DocID={A757DB8D-D656-4F90-B750-F2246C3E64A9}&Bucket=Current+Headlines">http://www.digitalbroadcasting.com/content/news/article.asp?DocID={A757DB8D-D656-4F90-B750-F2246C3E64A9}&Bucket=Current+Headlines</a><br />
 </p>

<p> </p>

<p><strong>National Geographic plans UK HDTV channel  [UK]</strong><br />
(Digital Spy)</p>

<p><a href="http://www.digitalspy.co.uk/article/ds23356.html">http://www.digitalspy.co.uk/article/ds23356.html</a><br />
 </p>

<p><strong>Tibetan farmers and herdsmen enjoy "digital TV"  [Tibet]</strong><br />
(People's Daily)</p>

<p><a href="http://english.peopledaily.com.cn/200508/11/eng20050811_201717.html">http://english.peopledaily.com.cn/200508/11/eng20050811_201717.html</a><br />
 </p>

<p><strong>Gov't to allow 2 new BS digital HDTV stations  [Japan]</strong><br />
(Kyodo News via Yahoo News)</p>

<p><a href="http://asia.news.yahoo.com/050810/kyodo/d8bt0akg1.html">http://asia.news.yahoo.com/050810/kyodo/d8bt0akg1.html</a></p>

<p> </p>

<p><strong>Digital TV Markets  [Australia]</strong></p>

<p>When is digital television available in your area (updated 3 August 2005)</p>

<p>(Digital Broadcasting Australia)</p>

<p><a href="http://www.dba.org.au/index.asp?sectionID=22">http://www.dba.org.au/index.asp?sectionID=22</a><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>August 11, 2005  9:34 AM</b>
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
			<?=getComments(179)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 179)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/08/more-news-july-10-11-2005-from-lee-wood.php" type="text/javascript" charset="utf-8"></script>
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