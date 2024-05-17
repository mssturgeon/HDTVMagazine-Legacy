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
		AND e.entry_id = 61";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 61 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 61 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 61";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_jeanbriac_parrette.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (4) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 61";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Jean-Briac Parrette" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Jean-Briac Parrette" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Jean-Briac Parrette" />
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
	<title>HDTV Magazine - INTERVIEW - Jean-Briac Parrette</title>
	<meta name="keywords" content="hdtv magazine, nbc universal, universal cable, standard def, chief financial, cable, universal, perrette, hdtv, bravo, nbc, magazine, new, channels, our, think, content, service, role, def, channel, financial, business, development, international" />
	<meta name="description" content="This interview was conducted in early 2005. Jean-Briac (JB) Perrette Senior Vice President, New Media And Chief Financial Officer NBC Universal Cable Jean-Briac (JB) Perrette was named Senior Vice President, New Media, and Chief Financial Officer of NBC Universal Cable..." />
	<meta name="title" content="INTERVIEW - Jean-Briac Parrette" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Jean-Briac Parrette" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_jeanbriac_parrette.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This interview was conducted in early 2005. Jean-Briac (JB) Perrette Senior Vice President, New Media And Chief Financial Officer NBC Universal Cable Jean-Briac (JB) Perrette was named Senior Vice President, New Media, and Chief Financial Officer of NBC Universal Cable..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=61', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_jeanbriac_parrette.php">INTERVIEW - Jean-Briac Parrette</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June  9, 2005</b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><em>This interview was conducted in early 2005.</em></p>

<p>Jean-Briac (JB) Perrette</p>

<p>Senior Vice President, New Media<br />
And Chief Financial Officer<br />
NBC Universal Cable </p>

<p><br />
Jean-Briac (JB) Perrette was named Senior Vice President, New Media, and Chief Financial Officer of NBC Universal Cable in May 2004. In his New Media role, Perrette spearheads the division’s worldwide strategy and development of new content distribution businesses, including video-on-demand (VOD), pay-per-view (PPV) and high definition (HD).  In this role he reports to David Zaslav, President of NBC Universal Cable. As CFO of NBC Universal Cable, Perrette is also responsible for the financial operations for cable distribution of NBC Universal’s leading portfolio of cable & broadcast assets, which are: Bravo, Bravo HD+, CNBC, CNBC World, MSNBC, mun2, Olympics, Sci-Fi, Telemundo, Trio and USA. In his CFO role, Perrette reports to Lynn Calpeter, Executive Vice President and Chief Financial Officer of NBC Universal.</p>

<p>As part of NBC’s acquisition of Vivendi Universal Entertainment, Perrette led the integration of NBC and Universal’s Cable divisions, creating one of the broadest, most profitable and fastest growing television groups. He also served as Chief Financial Officer of the Bravo Cable Network since January 2003, following his lead role in NBC’s acquisition of the network from Cablevision.  In 2003, Bravo became the fastest growing Cable network in the US and home to such hit series as Queer Eye for the Straight Guy and Celebrity Poker.</p>

<p>In his prior role at NBC, Perrette was Vice President & CFO of Business Development and was instrumental in completing over $1.5BN in acquisitions, most importantly of Bravo Cable Network and San Francisco station KNTV.  He also negotiated and executed several JVs and partnerships for CNBC International as part of its global strategy.  In his finance role, Perrette was responsible for NBC's strategic investment portfolio, which included holdings in A&E, Paxson, National Geographic International, and ValueVision International. </p>

<p>Before joining NBC, Perrette worked for NBC parent, GE and GE Capital, and was an analyst with CS First Boston in London and Tokyo.  He received a BA degree in Public Policy from Hamilton College.  Perrette lives in New York City with his wife Amy.</p>

<p><br />
<strong>About NBC Universal Cable </strong><br />
NBC Universal Cable, a division of NBC Universal, one of the world's preeminent media companies, drives the company’s cable strategic development and growth including video-on-demand, pay-per-view, HDTV and retransmission consent, and oversees the cable distribution, marketing and local ad sales of twelve properties (Bravo, Bravo HD+, CNBC, CNBC World, MSNBC, mun2, Sci-Fi, ShopNBC, Telemundo, Trio, USA and the Olympics on cable).  NBC Universal Cable also directs and manages the company’s cable and new media investments including A&E, The History Channel, History Channel International, The Biography Channel, National Geographic International, the Sundance Channel and Tivo.  </p>

<p><br />
INTERVIEW:</p>

<p><br />
Dale Cripps: How did you connect to the world of HDTV? </p>

<p>Perrette: I spent several years in business development with NBC prior to the merger with Universal. I spent a year in the finance role when we acquired BRAVO and then headed up the cable integrations of our entertainment cable assets over the course of the last year and then moved to this role working with David Zavlov back in June of this year. I come at it with a business development/finance background. We have taken all of the piece of business development that affect the cable and satellite players, so HD, Video on Demand, and Pay Per view, new channels launches—anything that is basically is related to the cable and satellite operators put under the “new media” heading .</p>

<p>HDTV Magazine: VOD has always had a great promise but seems to be forever disappointing. </p>

<p>Perrette  I think you have look to Comcast for breathing life into it. It has had this real promise, I agree with you, and it was the platform and technology that was always one year away from being big.  That said, you have never seen the focus and a messages so consistent and persistent coming from the biggest operator in the market (Comcast) on one platform. They have rallied around VOD as their differentiator. </p>

<p>HDTV Magazine:  Isn’t it their combating of satellite driving that initiative more than it is a business waiting to happen? </p>

<p>Certainly, in part, I think that is right. </p>

<p>HDTV Magazine: The great late Howard Miller suffered a huge loss at Westinghouse many years ago when he drove their interactive cable experiment back in the 70s. He said he learned that you can never trust a business plan that requires for its success your customers to change their behavior. HDTV doesn’t suffer from that condition you just need to change your taste a bit.</p>

<p>Perrette  I don’t think you need to change your taste but rather just have a teaser of  having seen and experience what HDTV can do. It is still a faily expensive investment for the technology upgrade to allow you to do it, but once bitten people are persuaded to do it.</p>

<p>HDTV Magazine: With you being from the financial side you know that HDTV defies any traditional financial logic. It bears a significant expense, it cost more bandwidth, and you have never been sure that the customers would embrace it.  When you saw it…how did it strike you?</p>

<p>Perrette   A lot of the credit for the  NBC or Universal’s HD strategy goes to David Zazlov, who is head of our cable When we bought BRAVO we listened to the industry and to the (cable) operators who were continuing to push HD two years ago. At that time there was such a limited amount of HD programming available. We need to increase the content available in order to push the service and develop the consumer value in it. David had the forsight to move the company to launch BRAVO HD back in July of 2003. We since then have found it to be a terrific asset. The operators responded to it. With our closing the Universal acquisition realize BRAVO was a smart and upscale brand whom you generally associate with the early adopters of technology, and so associating BRAVO and HD together was a smart play and why we did it. As the HD market has grown significantly over the last year and one half and we now had the opportunity to take more entertainment content that became available to us through the acquisition we wanted to again be responsive to the industry and the operators and develop a product that was no longer as niche but with a broader appeal that would attract many more people to the HD platform as consumer/viewers, and make it a compelling broad offering rather than the more niche offering we had in BRAVO.  So that iw why we rolled out the transformed BRAVO in UNIVERSAL HD—a broader and, we think, a more compelling service. </p>

<p>HDTV Magazine:  What are you selecting from the Universal library that will make it a compelling service?</p>

<p><br />
Perrette   We started with the US Open Tennis event, which we also broadcast on USA in standard def, and we simulcast it on BRAVO HD in high def and we will do that going forward. So, we start with the sports franchises, which we know are valuable and people enjoy watching, It is some of the most impressive material you can see in the HD environment.  We are looking to add additional sports including golf and other events. </p>

<p>HDTV Magazine: I do not receive BRAVO here so I need to ask if these are sponsored events or paid through premium subscriptions.</p>

<p>Perrette   It is similar to our traditional cable channels. Which will have a dual revenue stream of both advertiser and subscriber revenue. </p>

<p>HDTV Magazine: What is th4e carriage of UNIVERSAL HD?</p>

<p>Perrette   Right now we have carriage agreements for 25 million homes across the U.S. This includes DirecTV, COX, VOOM, and we are obviously looking to expand that in coming months?</p>

<p>HDTV Magazine: Where do you find any resistance to carriage? </p>

<p>Perrette   It is the natural evolution. For a new network to be in 25 million homes over 18 months in today’s environment is a terrific accomplishment.  We are confident that in the next six to twelve months we will add on to that significantly. The response has been very positive from our announcement to transform BRAVO HD into UNIVERSAL HD and moving to a much broader service.</p>

<p>HDTV Magazine: Is there anything you are doing with UNIVERSAL HD that is making you hold your breath and cross your fingers?</p>

<p>Perrette   No. There is nothing here I think that is concerning here.  It is a terrific development. I have Time Warner here in New York and the HD offering is till limited.  People are not into the rythem yet of just going into the HD channels. There is still not quite enough there and the programming it is not consistently HD. As they continue to have more HD channels with more HD programming on those channels our view is that you will then train people into becoming just truly HD watchers as opposed to switching from standard def to High def. Our service is 100% 1080i HD content and between sports, movies (mini blockbusters like Backdraft, Apollo 13, Meet the Parent) those are all titles that will be done without commercial interruption and in some cases uncut (not generally available on linear cable services). </p>

<p>The third thing is that we will have these franchises which we are taking from the best of our cable properties, including Monk, Battle Star Gallatica, Law and Order SVU from USA. Those are huge franchises that are from the top rated cable programming that we think will be a huge draw relative to the offering that is out there today. We take somewhat more of the limited bandwidth, which some of the operators are sensitive to, but instead really offer the most compelling product in one slot. <br />
HDTV Magazine:</p>

<p>HDTV Magazine: Are you firm on not compromising image quality?</p>

<p>Perrette   Absolutely! We think if you are going to be in this space and get consumer/viewers being compelled to it then going in and out of HD and standard def is a huge disservice to the viewer and to the roll out of HD overall.</p>

<p>HDTV Magazine: We have listened  to endless complaints about one cable channel who has chosen to upconvert and stretch (to fill the screen) a large percentage of what they list with the major listing services, ours, and TV Guide, that it is all HDTV. It is a major frustration for the viewers. You will not subject the viewer to that kind of thing?</p>

<p>Perrette   Ours is 100% HDTV programming. </p>

<p>HDTV Magazine: That will be very welcome news to our readers. </p>

<p>You have a great classic library at Universal. Will we see some of the old classics, such as the Frankenstein franchise from the old black and white era?  </p>

<p>The challenge is always the same. In re-launching the service as UNIVERSAL HD what we are going to do is make is a much broader appealing service. The problem with the old black and white and other older content is that it is  terrific and very valuable but from an audience appeal in today’s age it has less of an appeal than Appolo 13 or a Backdraft.  So, while we certainly look at doing things down the road that might use more of that content for now the service will largely stick with the content that has a broader appeal. </p>

<p>HDTV Magazine: Universal Studios is one of the great factories for producing original programming for television. Do you forecast any original productions for this network?</p>

<p>Perrette  It is something we will always look as the business model evolves and the viewership evolves. </p>

<p>HDTV Magazine: Since you have global responsibilities do you see establishing the UNIVERSAL HD franchise extending itself around the world? </p>

<p>Perrette  As you know the challenge is always International. It is hard to speak about as a  broad a single discussion because unlike the U.S. market it is so dependant upon market by market activities. But in general we are actively involved in the creation of new assets and new services and we are taking them as opportunity arises. While nothing I did we do have a series of international standard def channels that exists in Europe and Latin America and Asia. (CNBC Europe and Asia, Sci Fi Channels in Europe, 13th Street in several markets in Europe, We have Universal Channel in Latin America. So, we have a series of different channels that already excit internationally. We are constantly and actively looking to see how we can create new channels and new services. </p>

<p>HDTV Magazine:  Speaking of the SC fi Channel. Any chance of seeing the SCFi channels take the same route as UNIVERSAL HD? </p>

<p>Perrette: The question is; when will all of the linear services will move from standard def to HD? I think that will happen. Is that a three year item? Is that a five year item? Everyone has their own guess, but certainly over time you can imagine that the natural progression is going to be that all services will move to the HD platform. In the interim we are excited to use the UNIVERSAL HD umbrella to be able to put things like Battle Star Gallatica (ScFi's upcoming series) and provide SCiFI content there.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June  9, 2005  8:38 PM</b>
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
			<?=getComments(61)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 61)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_jeanbriac_parrette.php" type="text/javascript" charset="utf-8"></script>
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